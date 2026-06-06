<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class XenditService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        // API key dibaca secara dinamis dari Settings setiap kali service diinstansiasi
        $this->secretKey = Setting::get('xendit.secret_key', '');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->timeout(30);
    }

    public function isConfigured(): bool
    {
        return Setting::getBool('xendit.enabled') && filled($this->secretKey);
    }

    // ── Connection Test ───────────────────────────────────────────────────────

    /**
     * Tes koneksi menggunakan key yang diberikan langsung (dari form, sebelum disimpan).
     * Memanggil GET /balance — jika 401/INVALID_API_KEY = key salah,
     * jika 403/REQUEST_FORBIDDEN_ERROR = key VALID (dikenali Xendit, hanya kurang izin endpoint ini).
     *
     * @return array{success: bool, message: string, data: array|null}
     */
    public function testWithKey(string $secretKey): array
    {
        if (! filled($secretKey)) {
            return ['success' => false, 'message' => 'Secret Key tidak boleh kosong.', 'data' => null];
        }

        // Pastikan tidak ada whitespace di awal/akhir key
        $secretKey = trim($secretKey);

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->acceptJson()
                ->timeout(10)
                ->get('https://api.xendit.co/balance');

            // ── 200 OK: key valid & punya izin baca balance ───────────────────
            if ($response->successful()) {
                $balance = number_format($response->json('balance', 0), 0, ',', '.');
                return [
                    'success' => true,
                    'message' => "API Key valid ✓ — Saldo akun: Rp {$balance}",
                    'data'    => $response->json(),
                ];
            }

            $errorCode = $response->json('error_code', '');
            $httpStatus = $response->status();

            // ── 403 REQUEST_FORBIDDEN_ERROR: key VALID, hanya kurang izin ─────
            // Xendit mengenali key-nya → autentikasi berhasil
            if ($httpStatus === 403 || $errorCode === 'REQUEST_FORBIDDEN_ERROR') {
                return [
                    'success' => true,
                    'message' => 'API Key valid ✓ — Key dikenali Xendit. (Izin "Balance Read" belum aktif, tidak berpengaruh ke fungsi pembayaran.)',
                    'data'    => null,
                ];
            }

            // ── 401: key benar-benar tidak valid ─────────────────────────────
            if ($httpStatus === 401 || in_array($errorCode, ['INVALID_API_KEY', 'AUTHENTICATION_ERROR', 'UNAUTHORIZED_REQUEST'])) {
                return [
                    'success' => false,
                    'message' => 'API Key tidak valid. Pastikan Secret Key (bukan Public Key) yang dimasukkan, dan sesuaikan environment (Sandbox/Production).',
                    'data'    => null,
                ];
            }

            // ── Error lainnya ─────────────────────────────────────────────────
            $errorMsg = $response->json('message', 'Kesalahan tidak diketahui.');
            return [
                'success' => false,
                'message' => "Error [{$errorCode}]: {$errorMsg}",
                'data'    => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException) {
            return ['success' => false, 'message' => 'Koneksi ke Xendit gagal. Periksa koneksi internet.', 'data' => null];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'data' => null];
        }
    }

    // ── Dispatcher ────────────────────────────────────────────────────────────

    /**
     * Buat pembayaran berdasarkan channel_type pada PaymentMethod.
     * Mengembalikan array berisi data pembayaran Xendit.
     */
    public function createPayment(Order $order, PaymentMethod $pm): array
    {
        return match ($pm->xendit_channel_type) {
            'QR_CODE'         => $this->createQRCode($order, $pm),
            'VIRTUAL_ACCOUNT' => $this->createVirtualAccount($order, $pm),
            'EWALLET'         => $this->createEWallet($order, $pm),
            default           => throw new \RuntimeException(
                "Channel type [{$pm->xendit_channel_type}] tidak didukung."
            ),
        };
    }

    // ── Channel: QR Code (QRIS) ───────────────────────────────────────────────

    public function createQRCode(Order $order, PaymentMethod $pm): array
    {
        // Bersihkan order_number dari karakter non-URL-safe (/ → -) agar aman di URL path simulate
        $externalId = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $order->order_number)
            . '-' . now()->timestamp;

        $response = $this->http()->post("{$this->baseUrl}/qr_codes", [
            'external_id'  => $externalId,
            'type'         => 'DYNAMIC',
            'channel_code' => $pm->xendit_channel_code ?? 'ID_DANA',
            'currency'     => 'IDR',
            'amount'       => (int) $order->total_payment,
            'expires_at'   => now()->addMinutes(30)->toIso8601String(),
            'callback_url' => url('/api/webhooks/xendit'),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal membuat QR Code Xendit: ' . $response->body());
        }

        $data = $response->json();

        return [
            'type'        => 'qr_code',
            'external_id' => $data['external_id'] ?? $externalId,  // response Xendit pakai field 'external_id'
            'invoice_id'  => $data['id'] ?? null,
            'qr_string'   => $data['qr_string'] ?? null,
            'expires_at'  => $data['expires_at'] ?? null,
        ];
    }

    // ── Channel: Virtual Account ──────────────────────────────────────────────

    public function createVirtualAccount(Order $order, PaymentMethod $pm): array
    {
        $externalId = $order->order_number . '-' . now()->timestamp;

        $response = $this->http()->post("{$this->baseUrl}/callback_virtual_accounts", [
            'external_id'     => $externalId,
            'bank_code'       => $pm->xendit_channel_code,
            'name'            => $order->customer?->name ?? 'Customer',
            'expected_amount' => (int) $order->total_payment,
            'is_single_use'   => true,
            'is_closed'       => true,
            'expiration_date' => now()->addHours(24)->toIso8601String(),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal membuat Virtual Account Xendit: ' . $response->body());
        }

        $data = $response->json();

        return [
            'type'        => 'virtual_account',
            'external_id' => $externalId,
            'invoice_id'  => $data['id'] ?? null,
            'va_number'   => $data['account_number'] ?? null,
            'va_bank'     => $data['bank_code'] ?? $pm->xendit_channel_code,
            'expires_at'  => $data['expiration_date'] ?? null,
        ];
    }

    // ── Channel: E-Wallet ─────────────────────────────────────────────────────

    public function createEWallet(Order $order, PaymentMethod $pm): array
    {
        $externalId = $order->order_number . '-' . now()->timestamp;
        $properties = $pm->xendit_channel_properties ?? [];

        $response = $this->http()->post("{$this->baseUrl}/ewallets/charges", [
            'reference_id'       => $externalId,
            'currency'           => 'IDR',
            'amount'             => (int) $order->total_payment,
            'checkout_method'    => 'ONE_TIME_PAYMENT',
            'channel_code'       => $pm->xendit_channel_code,
            'channel_properties' => array_merge([
                'success_redirect_url' => url('/admin/pos?cashier_id=' . ($order->cashier_id ?? 1)),
                'failure_redirect_url' => url('/admin/pos?cashier_id=' . ($order->cashier_id ?? 1)),
            ], $properties),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal membuat E-Wallet Xendit: ' . $response->body());
        }

        $data = $response->json();

        $checkoutUrl = $data['actions']['desktop_web_checkout_url']
            ?? $data['actions']['mobile_web_checkout_url']
            ?? $data['checkout_url']
            ?? null;

        return [
            'type'         => 'ewallet',
            'external_id'  => $externalId,
            'invoice_id'   => $data['id'] ?? null,
            'checkout_url' => $checkoutUrl,
            'expires_at'   => $data['charge_expiry_at'] ?? null,
        ];
    }

    // ── Status check (polling) ────────────────────────────────────────────────

    /**
     * Cek status pembayaran berdasarkan xendit_invoice_id di order.
     * Return: 'paid' | 'unpaid' | 'failed'
     */
    public function checkPaymentStatus(Order $order): string
    {
        if (! $order->xendit_invoice_id) {
            return 'unpaid';
        }

        $type = $order->paymentMethod?->xendit_channel_type;

        try {
            // QR Code: cek via Transactions API — jauh lebih akurat dari QR Code status.
            // QR Code bisa INACTIVE/EXPIRED setelah dibayar, bukan berarti gagal.
            if ($type === 'QR_CODE') {
                return $this->checkQrPaymentViaTransactions($order);
            }

            $response = match ($type) {
                'VIRTUAL_ACCOUNT' => $this->http()->get("{$this->baseUrl}/callback_virtual_accounts/{$order->xendit_invoice_id}"),
                'EWALLET'         => $this->http()->get("{$this->baseUrl}/ewallets/charges/{$order->xendit_invoice_id}"),
                default           => null,
            };

            if (! $response?->successful()) {
                return 'unpaid';
            }

            $status = strtoupper($response->json('status', ''));

            // ACTIVE artinya instrumen baru dibuat & menunggu pembayaran — BUKAN lunas
            if ($status === 'ACTIVE') {
                return 'unpaid';
            }

            if (in_array($status, ['PAID', 'SUCCEEDED', 'COMPLETED', 'SETTLED'])) {
                return 'paid';
            }

            // INACTIVE pada Virtual Account single-use bisa berarti sudah dibayar;
            // biarkan webhook yang mengonfirmasi agar polling tidak salah baca.
            if ($type === 'VIRTUAL_ACCOUNT' && $status === 'INACTIVE') {
                return 'unpaid';
            }

            if (in_array($status, ['FAILED', 'VOIDED', 'EXPIRED', 'INACTIVE'])) {
                return 'failed';
            }

            return 'unpaid';
        } catch (\Throwable) {
            return 'unpaid';
        }
    }

    /**
     * Cek status pembayaran QR Code via Xendit Transactions API.
     * Lebih akurat karena melihat transaksi yang terjadi, bukan status QR Code-nya.
     */
    private function checkQrPaymentViaTransactions(Order $order): string
    {
        $referenceId = $order->xendit_external_id;
        if (! $referenceId) {
            return 'unpaid';
        }

        $response = $this->http()->get("{$this->baseUrl}/transactions", [
            'reference_id'     => $referenceId,
            'channel_category' => 'QR_CODE',
        ]);

        if (! $response->successful()) {
            return 'unpaid';
        }

        foreach ($response->json('data', []) as $txn) {
            $status = strtoupper($txn['status'] ?? '');

            if (in_array($status, ['SUCCESS', 'SUCCEEDED', 'PAID', 'COMPLETED', 'SETTLED'])) {
                return 'paid';
            }

            if (in_array($status, ['FAILED', 'VOIDED', 'EXPIRED'])) {
                return 'failed';
            }
        }

        // Belum ada transaksi → masih menunggu pembayaran
        return 'unpaid';
    }

    // ── Simulate Payment (Sandbox only) ──────────────────────────────────────

    /**
     * Trigger Xendit Sandbox "Simulate Payment".
     * Hanya tersedia di Sandbox — tidak berpengaruh di Production.
     *
     * @throws \RuntimeException jika channel tidak didukung atau API gagal.
     */
    public function simulatePayment(Order $order): void
    {
        $type        = $order->paymentMethod?->xendit_channel_type;
        $channelCode = $order->paymentMethod?->xendit_channel_code;
        $invoiceId   = $order->xendit_invoice_id;
        $externalId  = $order->xendit_external_id;
        $amount      = (int) $order->total_payment;

        // Xendit sandbox hanya mendukung simulate untuk QR Code channel ID_DANA.
        // ID_QRIS tidak bisa disimulate via API — harus pakai channel ID_DANA.
        if ($type === 'QR_CODE' && $channelCode !== 'ID_DANA') {
            throw new \RuntimeException(
                "Simulate payment QR Code hanya didukung untuk channel ID_DANA di Xendit Sandbox. " .
                    "Channel aktif: {$channelCode}. " .
                    "Buat payment method baru dengan Xendit Channel Code = ID_DANA untuk testing."
            );
        }

        $response = match ($type) {
            'QR_CODE' => $this->http()->post(
                "{$this->baseUrl}/qr_codes/{$externalId}/payments/simulate",
                ['amount' => $amount]
            ),
            'VIRTUAL_ACCOUNT' => $this->http()->post(
                "{$this->baseUrl}/callback_virtual_accounts/external_id={$externalId}/simulate_payment",
                ['amount' => $amount]
            ),
            default => throw new \RuntimeException(
                "Simulate payment tidak didukung untuk channel [{$type}]. Gunakan checkout URL untuk E-Wallet."
            ),
        };

        if (! $response->successful()) {
            $errCode = $response->json('error_code', 'UNKNOWN_ERROR');
            $msg     = $response->json('message', 'Gagal menjalankan simulate payment.');
            throw new \RuntimeException("[{$errCode}] {$msg}");
        }
    }

    // ── Webhook verification ──────────────────────────────────────────────────

    public function verifyWebhookToken(string $token): bool
    {
        $expected = trim(Setting::get('xendit.webhook_token', ''));

        // Token wajib dikonfigurasi — tolak jika kosong di DB
        if (! filled($expected)) {
            return false;
        }

        return hash_equals($expected, trim($token));
    }
}

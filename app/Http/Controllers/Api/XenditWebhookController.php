<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\XenditService;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $xendit = app(XenditService::class);

        // ── Verifikasi token ──────────────────────────────────────────────────
        $incomingToken = $request->header('x-callback-token', '');
        if (! $xendit->verifyWebhookToken($incomingToken)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $raw = $request->all();

        // ── Ekstrak external_id & status sesuai struktur webhook Xendit ────────
        // QR Code  : { "status": "COMPLETED", "qr_code": { "external_id": "..." } }
        // VA       : { "status": "PAID",       "external_id": "..." }
        // eWallet  : { "status": "SUCCEEDED",  "reference_id": "..." }
        $event = $raw['event'] ?? '';

        $externalId = match (true) {
            // QR Code payment — external_id ada di dalam qr_code object
            str_starts_with($event, 'qr.')         => $raw['qr_code']['external_id'] ?? null,
            // VA payment — external_id di root
            str_starts_with($event, 'fva_')        => $raw['external_id'] ?? null,
            // eWallet / generic
            default                                => $raw['reference_id']
                ?? $raw['external_id']
                ?? $raw['qr_code']['external_id']
                ?? null,
        };

        $status = strtoupper($raw['status'] ?? '');

        if (! $externalId) {
            return response()->json(['message' => 'ok']);
        }

        // Temukan order berdasarkan xendit_external_id
        $order = Order::where('xendit_external_id', $externalId)->first();

        if (! $order) {
            return response()->json(['message' => 'ok']);
        }

        // Jangan proses ulang order yang sudah lunas
        // Order 'failed' tetap bisa di-recover via resend webhook
        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'ok']);
        }

        // ── Pembayaran berhasil ───────────────────────────────────────────────
        if (in_array($status, ['PAID', 'SUCCEEDED', 'COMPLETED', 'SETTLED'])) {
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'completed',
            ]);

            return response()->json(['message' => 'ok']);
        }

        // ── Pembayaran gagal/expired ──────────────────────────────────────────
        if (in_array($status, ['FAILED', 'VOIDED', 'EXPIRED', 'INACTIVE'])) {
            $order->update([
                'payment_status' => 'failed',
                'status'         => 'cancelled',
            ]);

            return response()->json(['message' => 'ok']);
        }

        return response()->json(['message' => 'ok']);
    }
}

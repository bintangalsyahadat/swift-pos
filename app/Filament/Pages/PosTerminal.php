<?php

namespace App\Filament\Pages;

use App\Models\Cashier;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\StockMove;
use App\Services\XenditService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class PosTerminal extends Page
{
    protected static ?string $slug = 'pos';

    // Use Filament base layout (no sidebar/topbar)
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.pages.pos-terminal';

    protected static bool $shouldRegisterNavigation = false;

    // ─── URL parameter ────────────────────────────────────────────────────────
    #[Url]
    public int $cashier_id = 0;

    // ─── Phase: no_session | operational | close_session ──────────────────────
    public string $phase = 'no_session';

    // ─── Cashier / Session ────────────────────────────────────────────────────
    public ?Cashier $cashier      = null;
    public ?int     $sessionId    = null;   // store ID only, not the model

    // ─── Open-session form ────────────────────────────────────────────────────
    public int $openingBalance = 0;

    // ─── Close-session form ───────────────────────────────────────────────────
    public int $actualBalance   = 0;
    public string $closingNotes = '';
    public bool $notesRequired  = false;

    // ─── Cart: [product_id => [name, sku, price, qty, subtotal]] ─────────────
    public array $cart = [];

    // ─── Transaction form ─────────────────────────────────────────────────────
    public ?int   $customerId      = null;
    public int    $discount        = 0;
    public ?int   $paymentMethodId = null; // FK to payment_methods
    public string $search          = '';
    public int    $cashPaid        = 0;    // nominal uang yang diberikan customer (cash only)

    // ─── Checkout modal ───────────────────────────────────────────────────────
    public bool $showCheckoutModal = false;

    // ─── Receipt modal ────────────────────────────────────────────────────────
    public bool $showReceiptModal = false;
    public ?int $receiptOrderId   = null;

    // ─── Xendit payment modal ─────────────────────────────────────────────────
    public bool    $showXenditModal     = false;
    public ?int    $xenditOrderId       = null;
    public string  $xenditType         = '';   // qr_code | virtual_account
    public string  $xenditQrString     = '';
    public string  $xenditVaNumber     = '';
    public string  $xenditVaBank       = '';
    public ?string $xenditExpiresAt    = null;
    public bool    $xenditPaymentFailed = false;
    public string  $xenditFailureNote  = '';

    // ─── Customer picker modal ────────────────────────────────────────────────
    public bool   $showCustomerModal  = false;
    public string $customerSearch     = '';

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        abort_if(! $this->cashier_id, 404);

        $this->cashier = Cashier::findOrFail($this->cashier_id);
        abort_if(! $this->cashier->is_active, 403, 'Terminal kasir ini tidak aktif.');

        // Check for an existing open session on this terminal
        $open = PosSession::openSessionForTerminal($this->cashier_id);

        if ($open) {
            if ((int) $open->user_id !== (int) Auth::id()) {
                abort(403, 'Terminal "' . $this->cashier->name . '" sedang digunakan oleh kasir lain.');
            }
            // Resume own session — store only the ID to avoid model hydration policy checks
            $this->sessionId = $open->id;
            $this->phase     = 'operational';
        }

        // Default payment method = first active one (usually Cash)
        $this->paymentMethodId = PaymentMethod::active()->value('id');
    }

    public function getTitle(): string
    {
        return 'POS — ' . ($this->cashier?->name ?? '');
    }

    // ─── Computed properties ──────────────────────────────────────────────────

    /** Lazily load the PosSession from DB — never stored as model property to avoid Shield policy checks on hydration. */
    #[Computed]
    public function posSession(): ?PosSession
    {
        return $this->sessionId ? PosSession::find($this->sessionId) : null;
    }

    #[Computed]
    public function products()
    {
        $search = $this->search;
        return Product::query()
            ->where('is_active', true)
            ->when($search, fn($q) => $q->where(function ($query) use ($search) {
                $query->where('name',    'like', '%' . $search . '%')
                    ->orWhere('sku',     'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            }))
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function customers()
    {
        return Customer::orderBy('name')->get();
    }

    #[Computed]
    public function filteredCustomers()
    {
        $search = $this->customerSearch;
        return Customer::query()
            ->when($search, fn($q) => $q->where(function ($query) use ($search) {
                $query->where('name',  'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            }))
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        $id = $this->customerId;
        return $id ? Customer::find($id) : null;
    }

    public function openCustomerModal(): void
    {
        $this->customerSearch = '';
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal(): void
    {
        $this->showCustomerModal = false;
        $this->customerSearch   = '';
    }

    public function selectCustomer(int $id): void
    {
        $this->customerId       = $id;
        $this->showCustomerModal = false;
        $this->customerSearch   = '';
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
    }

    #[Computed]
    public function paymentMethods()
    {
        return PaymentMethod::active()->get();
    }

    #[Computed]
    public function hasCustomer(): bool
    {
        $customerId = $this->customerId;
        return (bool) ($customerId
            ?? ((int) \App\Models\Setting::get('general.default_customer_id') ?: null));
    }

    #[Computed]
    public function currencySymbol(): string
    {
        return \App\Models\Setting::currencySymbol();
    }

    #[Computed]
    public function totalPrice(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    #[Computed]
    public function discountAmount(): float
    {
        $cart     = $this->cart;
        $discount = $this->discount;
        $total    = collect($cart)->sum('subtotal');
        return round($total * ($discount / 100), 2);
    }

    #[Computed]
    public function totalPayment(): float
    {
        $cart     = $this->cart;
        $discount = $this->discount;
        $total    = collect($cart)->sum('subtotal');
        $discAmt  = round($total * ($discount / 100), 2);
        return $total - $discAmt;
    }

    #[Computed]
    public function isCashPayment(): bool
    {
        $id = $this->paymentMethodId;
        $pm = $id ? PaymentMethod::find($id) : null;
        return $pm && $pm->type === 'cash';
    }

    #[Computed]
    public function changeAmount(): int
    {
        $cart       = $this->cart;
        $discount   = $this->discount;
        $cashPaid   = $this->cashPaid;
        $total      = collect($cart)->sum('subtotal');
        $discAmt    = round($total * ($discount / 100), 2);
        $totalPay   = (int) ceil($total - $discAmt);
        $change     = $cashPaid - $totalPay;
        return max(0, $change);
    }

    // ─── Session: Open ────────────────────────────────────────────────────────

    public function openSession(): void
    {
        $this->validate([
            'openingBalance' => 'required|integer|min:0',
        ]);

        // Enforce single-terminal constraint
        $existing = PosSession::openSessionForTerminal($this->cashier_id);
        if ($existing) {
            Notification::make()
                ->title('Terminal sudah dibuka')
                ->body('Kasir lain sudah membuka terminal ini.')
                ->danger()
                ->send();
            return;
        }

        $newSession = PosSession::create([
            'terminal_id'     => $this->cashier_id,
            'user_id'         => Auth::id(),
            'opened_at'       => now(),
            'opening_balance' => $this->openingBalance,
            'state'           => 'open',
        ]);

        $this->sessionId = $newSession->id;
        $this->phase     = 'operational';
    }

    // ─── Session: Show close form ─────────────────────────────────────────────

    public function showCloseSession(): void
    {
        $this->actualBalance = 0;
        $this->closingNotes  = '';
        $this->notesRequired = false;
        $this->phase         = 'close_session';
    }

    public function cancelCloseSession(): void
    {
        $this->phase = 'operational';
    }

    public function updatedActualBalance($value): void
    {
        if ($this->posSession) {
            $expected            = $this->posSession->computeExpectedBalance();
            $this->notesRequired = ((int) $value !== $expected);
        }
    }

    // ─── Session: Close ───────────────────────────────────────────────────────

    public function closeSession(): void
    {
        $session  = $this->posSession;
        abort_if(! $session, 403);
        $expected = $session->computeExpectedBalance();
        $diff     = (int) $this->actualBalance - $expected;

        $rules = ['actualBalance' => 'required|integer|min:0'];
        if ($diff !== 0) {
            $rules['closingNotes'] = 'required|string|min:3';
        }

        $this->validate($rules, [
            'closingNotes.required' => 'Catatan penutup diperlukan saat ada selisih.',
        ]);

        $session->update([
            'closed_at'         => now(),
            'expected_balance'  => $expected,
            'actual_balance'    => $this->actualBalance,
            'difference_amount' => $diff,
            'closing_notes'     => $diff !== 0 ? $this->closingNotes : null,
            'state'             => 'closed',
        ]);

        $this->sessionId = null;

        Notification::make()
            ->title('Sesi berhasil ditutup')
            ->success()
            ->send();

        $this->redirect(
            \App\Filament\Resources\Cashiers\CashierResource::getUrl('index')
        );
    }

    // ─── Cart ─────────────────────────────────────────────────────────────────

    // ─── Barcode scan ─────────────────────────────────────────────────────────

    public function scanBarcode(string $code): void
    {
        $code = trim($code);
        if (! $code) return;

        $product = Product::where('barcode', $code)
            ->orWhere('sku', $code)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            Notification::make()
                ->title('Produk tidak ditemukan')
                ->body("Tidak ada produk dengan barcode / SKU: \"{$code}\"")
                ->warning()
                ->send();
            return;
        }

        if ($product->currentStock() <= 0) {
            Notification::make()
                ->title('Stok habis')
                ->body("{$product->name} tidak memiliki stok tersedia.")
                ->danger()
                ->send();
            return;
        }

        $this->addToCart($product->id);

        Notification::make()
            ->title('Ditambahkan ke cart')
            ->body($product->name)
            ->success()
            ->duration(1500)
            ->send();

        $this->dispatch('cart-updated');
    }

    // ─── Cart ─────────────────────────────────────────────────────────────────

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product || ! $product->is_active) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'sku'        => $product->sku,
                'price'      => (float) $product->price,
                'qty'        => 1,
                'subtotal'   => (float) $product->price,
            ];
        }

        $this->recalculateSubtotal($productId);
    }

    public function incrementQty(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
            $this->recalculateSubtotal($productId);
        }
    }

    public function decrementQty(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['qty'] > 1) {
                $this->cart[$productId]['qty']--;
                $this->recalculateSubtotal($productId);
            } else {
                $this->removeFromCart($productId);
            }
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function clearCart(): void
    {
        $this->cart            = [];
        $this->customerId      = null;
        $this->discount        = 0;
        $this->paymentMethodId = null;
        $this->cashPaid        = 0;
    }

    private function recalculateSubtotal(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['subtotal'] =
                $this->cart[$productId]['price'] * $this->cart[$productId]['qty'];
        }
    }

    // ─── Checkout ─────────────────────────────────────────────────────────────

    public function openCheckoutModal(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong')->warning()->send();
            return;
        }

        // Default payment method jika belum dipilih
        if (! $this->paymentMethodId) {
            $this->paymentMethodId = PaymentMethod::active()->value('id');
        }

        $this->cashPaid = 0;
        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->showCheckoutModal = false;
    }

    public function pay(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong')->warning()->send();
            return;
        }

        if (! $this->paymentMethodId) {
            Notification::make()->title('Silakan pilih metode pembayaran')->warning()->send();
            return;
        }

        // Validasi cash: nominal harus >= total
        if ($this->isCashPayment && $this->cashPaid < (int) ceil($this->totalPayment)) {
            Notification::make()
                ->title('Nominal uang kurang')
                ->body('Nominal yang dibayarkan harus ≥ total tagihan.')
                ->warning()
                ->send();
            return;
        }

        // Gunakan default customer dari Setting jika tidak dipilih
        $customerId = $this->customerId
            ?? ((int) \App\Models\Setting::get('general.default_customer_id') ?: null);

        // Jika masih null, tampilkan pesan & stop
        if (! $customerId) {
            $this->showCheckoutModal = false;
            Notification::make()
                ->title('Pelanggan belum dipilih')
                ->body('Pilih pelanggan di form, atau atur Pelanggan Default di halaman Pengaturan terlebih dahulu.')
                ->warning()
                ->persistent()
                ->send();
            return;
        }

        $pm       = PaymentMethod::find($this->paymentMethodId);
        $isOnline = $pm?->is_online && \App\Models\Setting::getBool('xendit.enabled');

        // ── Pembayaran Online via Xendit ──────────────────────────────────────
        if ($isOnline) {
            $this->payViaXendit($pm, $customerId);
            return;
        }

        // ── Pembayaran Offline (tunai / kartu / QRIS statis) ──────────────────
        $isCash         = $this->isCashPayment;
        $cashPaid       = $isCash ? $this->cashPaid : null;
        $changeAmount   = $isCash ? $this->changeAmount : null;
        $createdOrderId = null;

        DB::transaction(function () use ($customerId, $cashPaid, $changeAmount, &$createdOrderId) {
            $userId = Auth::id();

            $order = Order::create([
                'customer_id'        => $customerId,
                'cashier_id'         => $this->cashier_id,
                'pos_session_id'     => $this->sessionId,
                'order_date'         => now(),
                'total_price'        => $this->totalPrice,
                'discount'           => $this->discount,
                'discount_amount'    => $this->discountAmount,
                'total_payment'      => $this->totalPayment,
                'cash_paid'          => $cashPaid,
                'change_amount'      => $changeAmount,
                'payment_method'     => PaymentMethod::find($this->paymentMethodId)?->code ?? 'cash',
                'payment_method_id'  => $this->paymentMethodId,
                'payment_status'     => 'paid',
                'status'             => 'completed',
            ]);

            foreach ($this->cart as $item) {
                $detail = OrderDetail::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $item['subtotal'],
                ]);

                StockMove::create([
                    'product_id'      => $item['product_id'],
                    'user_id'         => $userId,
                    'quantity'        => $item['qty'],
                    'type'            => 'out',
                    'order_detail_id' => $detail->id,
                    'reference'       => $order->order_number,
                    'state'           => 'done',
                ]);
            }

            $createdOrderId = $order->id;
        });

        $this->showCheckoutModal = false;
        $this->receiptOrderId    = $createdOrderId;
        $this->showReceiptModal  = true;
        $this->clearCart();

        Notification::make()
            ->title('Pembayaran berhasil')
            ->body('Pesanan telah dicatat.')
            ->success()
            ->send();

        $this->dispatch('open-receipt');
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->receiptOrderId   = null;
    }

    // ─── Xendit: buat pembayaran & tampilkan modal ────────────────────────────

    private function payViaXendit(PaymentMethod $pm, int $customerId): void
    {
        $createdOrderId = null;

        DB::transaction(function () use ($customerId, &$createdOrderId) {
            $userId = Auth::id();

            $order = Order::create([
                'customer_id'       => $customerId,
                'cashier_id'        => $this->cashier_id,
                'pos_session_id'    => $this->sessionId,
                'order_date'        => now(),
                'total_price'       => $this->totalPrice,
                'discount'          => $this->discount,
                'discount_amount'   => $this->discountAmount,
                'total_payment'     => $this->totalPayment,
                'payment_method'    => PaymentMethod::find($this->paymentMethodId)?->code ?? 'online',
                'payment_method_id' => $this->paymentMethodId,
                'payment_status'    => 'unpaid',
                'status'            => 'new',
            ]);

            foreach ($this->cart as $item) {
                OrderDetail::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $item['subtotal'],
                ]);
                // StockMove dibuat saat status berubah ke 'processing' / 'completed'
            }

            $createdOrderId = $order->id;
        });

        // Panggil Xendit API
        try {
            $order  = Order::with(['customer', 'paymentMethod'])->findOrFail($createdOrderId);
            $xendit = app(XenditService::class);
            $result = $xendit->createPayment($order, $pm);

            // Simpan data Xendit ke order
            $order->update([
                'xendit_invoice_id'  => $result['invoice_id'] ?? null,
                'xendit_external_id' => $result['external_id'] ?? null,
                'xendit_qr_string'   => $result['qr_string'] ?? null,
                'xendit_va_number'   => $result['va_number'] ?? null,
                'xendit_va_bank'     => $result['va_bank'] ?? null,
                'xendit_expires_at'  => isset($result['expires_at'])
                    ? \Carbon\Carbon::parse($result['expires_at'])
                    : null,
            ]);

            // Tampilkan Xendit payment modal
            $this->xenditOrderId      = $order->id;
            $this->xenditType         = $result['type'] ?? '';
            $this->xenditQrString     = $result['qr_string'] ?? '';
            $this->xenditVaNumber     = $result['va_number'] ?? '';
            $this->xenditVaBank       = $result['va_bank'] ?? '';
            $this->xenditExpiresAt    = $result['expires_at'] ?? null;

            // Reset state error dari transaksi sebelumnya
            $this->xenditPaymentFailed = false;
            $this->xenditFailureNote   = '';

            $this->showCheckoutModal = false;
            $this->showXenditModal   = true;
            $this->clearCart();
        } catch (\Throwable $e) {
            // Batalkan order jika Xendit gagal
            if ($createdOrderId) {
                Order::find($createdOrderId)?->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            }

            Notification::make()
                ->title('Gagal membuat pembayaran Xendit')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function checkXenditStatus(): void
    {
        if (! $this->xenditOrderId) {
            return;
        }

        $order = Order::with('paymentMethod')->find($this->xenditOrderId);

        if (! $order) {
            return;
        }

        // Jika sudah lunas via webhook sebelumnya
        if ($order->payment_status === 'paid') {
            $this->xenditPaymentConfirmed($order);
            return;
        }

        if ($order->payment_status === 'failed') {
            $this->xenditPaymentFailed = true;
            $this->xenditFailureNote   = 'Pembayaran gagal atau kedaluwarsa. Order ini telah otomatis dibatalkan.';
            return;
        }

        // Poll ke Xendit API
        $xendit = app(XenditService::class);
        $status = $xendit->checkPaymentStatus($order);

        if ($status === 'paid') {
            $order->update(['payment_status' => 'paid', 'status' => 'completed']);
            $this->xenditPaymentConfirmed($order);
        } elseif ($status === 'failed') {
            $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
            $this->xenditPaymentFailed = true;
            $this->xenditFailureNote   = 'Pembayaran gagal atau kedaluwarsa. Order ini telah otomatis dibatalkan.';
        }
    }

    private function xenditPaymentConfirmed(Order $order): void
    {
        $this->showXenditModal  = false;
        $this->receiptOrderId   = $order->id;
        $this->showReceiptModal = true;

        Notification::make()
            ->title('Pembayaran berhasil')
            ->body("Pesanan #{$order->order_number} telah lunas.")
            ->success()
            ->send();

        $this->dispatch('open-receipt');
    }

    public function closeXenditModal(): void
    {
        $this->showXenditModal     = false;
        $this->xenditPaymentFailed = false;
        $this->xenditFailureNote   = '';
    }

    /** Hanya untuk Sandbox / Dev: trigger simulate payment langsung dari modal. */
    public function simulateXenditPayment(): void
    {
        if (! $this->xenditOrderId) return;

        $order = Order::with('paymentMethod')->find($this->xenditOrderId);
        if (! $order) return;

        try {
            app(XenditService::class)->simulatePayment($order);

            Notification::make()
                ->title('Simulate payment dikirim ✓')
                ->body('Menunggu konfirmasi dari Xendit — status akan diperbarui otomatis.')
                ->success()
                ->duration(4000)
                ->send();

            // Auto-check status setelah 3 detik agar webhook sempat diterima
            $this->js('setTimeout(() => $wire.checkXenditStatus(), 3000)');
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal simulate payment')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    // ─── Computed: receipt order ───────────────────────────────────────────────
    #[Computed]
    public function receiptOrder(): ?Order
    {
        $id = $this->receiptOrderId;
        if (! $id) {
            return null;
        }

        return Order::with(['orderDetails.product', 'customer', 'paymentMethod'])
            ->find($id);
    }

    // Order yang sedang menunggu pembayaran Xendit (berbeda dengan receiptOrder)
    #[Computed]
    public function xenditOrder(): ?Order
    {
        $id = $this->xenditOrderId;
        return $id ? Order::find($id) : null;
    }
}

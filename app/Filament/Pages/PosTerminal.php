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

    // ─── Customer picker modal ────────────────────────────────────────────────
    public bool   $showCustomerModal  = false;
    public string $customerSearch     = '';

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        abort_if(! $this->cashier_id, 404);

        $this->cashier = Cashier::findOrFail($this->cashier_id);
        abort_if(! $this->cashier->is_active, 403, 'This cashier terminal is inactive.');

        // Check for an existing open session on this terminal
        $open = PosSession::openSessionForTerminal($this->cashier_id);

        if ($open) {
            if ((int) $open->user_id !== (int) Auth::id()) {
                abort(403, 'Terminal "' . $this->cashier->name . '" is already in use by another cashier.');
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
                ->title('Terminal already open')
                ->body('Another cashier has already opened this terminal.')
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
            'closingNotes.required' => 'Closing notes are required when there is a discrepancy.',
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
            ->title('Session closed successfully')
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
            Notification::make()->title('Cart is empty')->warning()->send();
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
            Notification::make()->title('Cart is empty')->warning()->send();
            return;
        }

        if (! $this->paymentMethodId) {
            Notification::make()->title('Please select a payment method')->warning()->send();
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
                ->title('Customer belum dipilih')
                ->body('Pilih customer di form, atau atur Default Customer di halaman Settings terlebih dahulu.')
                ->warning()
                ->persistent()
                ->send();
            return;
        }

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
            ->title('Payment successful')
            ->body('Order has been recorded.')
            ->success()
            ->send();

        $this->dispatch('open-receipt');
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->receiptOrderId   = null;
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
}

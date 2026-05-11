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
    public ?PosSession $session   = null;

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

    // ─── Checkout modal ───────────────────────────────────────────────────────
    public bool $showCheckoutModal = false;

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
            if ($open->user_id !== Auth::id()) {
                abort(403, 'Terminal "' . $this->cashier->name . '" is already in use by another cashier.');
            }
            // Resume own session
            $this->session = $open;
            $this->phase   = 'operational';
        }

        // Default payment method = first active one (usually Cash)
        $this->paymentMethodId = PaymentMethod::active()->value('id');
    }

    public function getTitle(): string
    {
        return 'POS — ' . ($this->cashier?->name ?? '');
    }

    // ─── Computed properties ──────────────────────────────────────────────────

    public function getProductsProperty()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search, fn($q) => $q->where(function ($query) {
                $query->where('name',    'like', '%' . $this->search . '%')
                    ->orWhere('sku',     'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            }))
            ->limit(50)
            ->get();
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get();
    }

    public function getFilteredCustomersProperty()
    {
        return Customer::query()
            ->when($this->customerSearch, fn($q) => $q->where(function ($query) {
                $query->where('name',  'like', '%' . $this->customerSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->customerSearch . '%');
            }))
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
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

    public function getPaymentMethodsProperty()
    {
        return PaymentMethod::active()->get();
    }

    public function getHasCustomerProperty(): bool
    {
        return (bool) ($this->customerId
            ?? ((int) \App\Models\Setting::get('general.default_customer_id') ?: null));
    }

    public function getTotalPriceProperty(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function getDiscountAmountProperty(): float
    {
        return round($this->totalPrice * ($this->discount / 100), 2);
    }

    public function getTotalPaymentProperty(): float
    {
        return $this->totalPrice - $this->discountAmount;
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

        $this->session = PosSession::create([
            'terminal_id'     => $this->cashier_id,
            'user_id'         => Auth::id(),
            'opened_at'       => now(),
            'opening_balance' => $this->openingBalance,
            'state'           => 'open',
        ]);

        $this->phase = 'operational';
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
        if ($this->session) {
            $expected            = $this->session->computeExpectedBalance();
            $this->notesRequired = ((int) $value !== $expected);
        }
    }

    // ─── Session: Close ───────────────────────────────────────────────────────

    public function closeSession(): void
    {
        $session  = $this->session;
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

        Notification::make()
            ->title('Session closed successfully')
            ->success()
            ->send();

        $this->redirect(
            \App\Filament\Resources\Cashiers\CashierResource::getUrl('index')
        );
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
        $this->cart          = [];
        $this->customerId    = null;
        $this->discount      = 0;
        $this->paymentMethodId = null;
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

        DB::transaction(function () use ($customerId) {
            $userId = Auth::id();

            $order = Order::create([
                'customer_id'        => $customerId,
                'cashier_id'         => $this->cashier_id,
                'pos_session_id'     => $this->session->id,
                'order_date'         => now(),
                'total_price'        => $this->totalPrice,
                'discount'           => $this->discount,
                'discount_amount'    => $this->discountAmount,
                'total_payment'      => $this->totalPayment,
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
        });

        $this->showCheckoutModal = false;
        $this->clearCart();

        Notification::make()
            ->title('Payment successful')
            ->body('Order has been recorded.')
            ->success()
            ->send();
    }
}

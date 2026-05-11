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
    public ?int   $customerId       = null;
    public int    $discount         = 0;
    public ?int   $paymentMethodId  = null; // FK to payment_methods
    public string $search           = '';

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

    public function getPaymentMethodsProperty()
    {
        return PaymentMethod::active()->get();
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

    public function checkout(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Cart is empty')->warning()->send();
            return;
        }

        if (! $this->customerId) {
            Notification::make()->title('Please select a customer')->warning()->send();
            return;
        }

        DB::transaction(function () {
            $userId = Auth::id();

            $year  = now()->format('Y');
            $month = now()->format('m');
            $last  = Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotNull('order_number')
                ->orderByDesc('id')
                ->value('order_number');
            $lastNumber  = $last ? (int) substr($last, -4) : 0;
            $orderNumber = 'O/' . $year . '/' . $month . '/' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number'    => $orderNumber,
                'customer_id'     => $this->customerId,
                'cashier_id'      => $this->cashier_id,
                'pos_session_id'  => $this->session->id,
                'order_date'      => now(),
                'total_price'     => $this->totalPrice,
                'discount'        => $this->discount,
                'discount_amount' => $this->discountAmount,
                'total_payment'      => $this->totalPayment,
                'payment_method'     => $this->paymentMethodId
                    ? (\App\Models\PaymentMethod::find($this->paymentMethodId)?->code ?? 'cash')
                    : 'cash',
                'payment_method_id'  => $this->paymentMethodId,
                'payment_status'  => 'paid',
                'status'          => 'processing',
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

        Notification::make()->title('Transaction completed')->success()->send();

        $this->clearCart();
    }
}

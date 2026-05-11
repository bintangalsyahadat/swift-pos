<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(Cashier::class);
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    protected $fillable = [
        'customer_id',
        'cashier_id',
        'pos_session_id',
        'payment_method_id',
        'order_number',
        'order_date',
        'total_price',
        'status',
        'discount',
        'discount_amount',
        'total_payment',
        'payment_method',
        'payment_status',
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (Order $order) {
            if (! $order->isDirty('status')) {
                return;
            }

            $newStatus = $order->status;
            $userId = Auth::id();

            if ($newStatus === 'processing') {
                $order->orderDetails()->with('product')->get()->each(function ($detail) use ($order, $userId) {
                    StockMove::create([
                        'product_id'      => $detail->product_id,
                        'user_id'         => $userId,
                        'quantity'        => $detail->quantity,
                        'type'            => 'out',
                        'order_detail_id' => $detail->id,
                        'reference'       => $order->order_number,
                        'state'           => 'draft',
                    ]);
                });
            } elseif ($newStatus === 'completed') {
                StockMove::whereHas('orderDetail', fn($q) => $q->where('order_id', $order->id))
                    ->update(['state' => 'done']);
            } elseif ($newStatus === 'cancelled') {
                StockMove::whereHas('orderDetail', fn($q) => $q->where('order_id', $order->id))
                    ->update(['state' => 'cancelled']);
            }
        });

        static::creating(function (Order $order) {
            $year = now()->format('Y');
            $month = now()->format('m');

            $last = static::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotNull('order_number')
                ->orderByDesc('id')
                ->value('order_number');

            $lastNumber = $last ? (int) substr($last, -4) : 0;
            $nextNumber = $lastNumber + 1;

            $order->order_number = 'O/' . $year . '/' . $month . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}

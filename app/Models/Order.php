<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected $fillable = [
        'customer_id',
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

    protected static function boot(): void
    {
        parent::boot();

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

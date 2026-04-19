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
        'order_date',
        'total_price',
        'status',
        'discount',
        'discount_amount',
        'total_payment',
        'payment_method',
        'payment_status',
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}

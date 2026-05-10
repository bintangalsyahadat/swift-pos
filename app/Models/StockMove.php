<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMove extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'type',
        'quantity',
        'order_detail_id',
        'reference',
        'state',
        'notes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }
}

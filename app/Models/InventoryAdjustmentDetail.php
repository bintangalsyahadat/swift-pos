<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentDetail extends Model
{
    protected $fillable = [
        'inventory_adjustment_id',
        'product_id',
        'type',
        'quantity',
        'notes',
    ];

    public function inventoryAdjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

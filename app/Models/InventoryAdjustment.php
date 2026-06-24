<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'name',
        'reference',
        'status',
        'user_id',
        'notes',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (InventoryAdjustment $adjustment) {
            if (! $adjustment->isDirty('status')) {
                return;
            }

            if ($adjustment->status === 'done') {
                $adjustment->details()->with('product')->get()->each(function ($detail) use ($adjustment) {
                    if ($detail->product && $detail->product->isService()) {
                        return;
                    }

                    StockMove::create([
                        'product_id' => $detail->product_id,
                        'user_id'    => $adjustment->user_id,
                        'type'       => $detail->type,
                        'quantity'   => $detail->quantity,
                        'state'      => 'done',
                        'reference'  => $adjustment->name,
                        'notes'      => $detail->notes ?? $adjustment->notes,
                    ]);
                });
            }
        });
    }

    public function details()
    {
        return $this->hasMany(InventoryAdjustmentDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'brand_id',
        'category_id',
        'sub_category_id',
        'is_active',
        'in_stock',
        'sku',
        'barcode',
        'base_price',
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function stockMoves()
    {
        return $this->hasMany(StockMove::class);
    }

    /**
     * Hitung stok aktual dari akumulasi stock move yang sudah done.
     * Tidak bergantung pada kolom stock — murni dari stock move.
     */
    public function currentStock(): int
    {
        $in  = $this->stockMoves()->where('state', 'done')->where('type', 'in')->sum('quantity');
        $out = $this->stockMoves()->where('state', 'done')->where('type', 'out')->sum('quantity');

        return (int) ($in - $out);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}

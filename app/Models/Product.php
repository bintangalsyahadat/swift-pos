<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \App\Models\Concerns\HasApiId;

    protected $fillable = [
        'api_id',
        'name',
        'description',
        'price',
        'image',
        'brand_id',
        'category_id',
        'sub_category_id',
        'is_active',
        'type',
        'sku',
        'barcode',
        'base_price',
    ];

    /**
     * Apakah produk bertipe storable (berbasis stok).
     */
    public function isStorable(): bool
    {
        return $this->type === 'storable';
    }

    /**
     * Apakah produk bertipe service (jasa, tidak berbasis stok).
     */
    public function isService(): bool
    {
        return $this->type === 'service';
    }

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
    /**
     * Stok hanya relevan untuk produk storable. Service product selalu null.
     */
    public function currentStock(): ?int
    {
        if ($this->isService()) {
            return null;
        }

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

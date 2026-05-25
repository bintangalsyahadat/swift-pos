<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use \App\Models\Concerns\HasApiId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'api_id',
        'name',
        'description',
        'image',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

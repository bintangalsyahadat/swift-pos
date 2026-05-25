<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    use \App\Models\Concerns\HasApiId;

    protected $fillable = [
        'api_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

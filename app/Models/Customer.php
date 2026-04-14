<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];
}

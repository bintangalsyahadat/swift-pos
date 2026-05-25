<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Otomatis mengisi kolom `api_id` dengan UUID saat model pertama kali dibuat.
 * Gunakan trait ini pada semua model master yang memiliki kolom `api_id`.
 *
 * @mixin Model
 */
trait HasApiId
{
    public static function bootHasApiId(): void
    {
        static::creating(function ($model) {
            if (empty($model->api_id)) {
                $model->api_id = (string) Str::uuid();
            }
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Indomie',  'description' => 'Produk mie instan Indomie',        'is_active' => true],
            ['name' => 'Aqua',     'description' => 'Air mineral Aqua',                 'is_active' => true],
            ['name' => 'Nestle',   'description' => 'Produk makanan & minuman Nestle',  'is_active' => true],
            ['name' => 'Unilever', 'description' => 'Produk kebutuhan rumah tangga',    'is_active' => true],
            ['name' => 'Wings',    'description' => 'Produk Wings Food & Wings Care',   'is_active' => true],
            ['name' => 'Mayora',   'description' => 'Produk snack & minuman Mayora',    'is_active' => true],
            ['name' => 'Indofood', 'description' => 'Produk Indofood',                  'is_active' => true],
            ['name' => 'P&G',      'description' => 'Produk kebutuhan pribadi P&G',     'is_active' => true],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand['name']], $brand);
        }
    }
}

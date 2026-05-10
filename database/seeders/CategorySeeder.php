<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name'        => 'Makanan',
                'description' => 'Produk makanan & camilan',
                'is_active'   => true,
                'subs'        => [
                    ['name' => 'Mie & Pasta',   'description' => 'Mie instan, pasta, bihun'],
                    ['name' => 'Snack & Kripik', 'description' => 'Camilan, kripik, biskuit'],
                    ['name' => 'Roti & Kue',    'description' => 'Roti tawar, roti manis, kue'],
                ],
            ],
            [
                'name'        => 'Minuman',
                'description' => 'Produk minuman kemasan',
                'is_active'   => true,
                'subs'        => [
                    ['name' => 'Air Mineral',       'description' => 'Air mineral kemasan botol & galon'],
                    ['name' => 'Minuman Teh & Kopi', 'description' => 'Teh kemasan, kopi kemasan'],
                    ['name' => 'Minuman Energi',    'description' => 'Minuman isotonik & energi'],
                ],
            ],
            [
                'name'        => 'Kebutuhan Rumah Tangga',
                'description' => 'Produk kebersihan & perawatan rumah',
                'is_active'   => true,
                'subs'        => [
                    ['name' => 'Sabun & Deterjen', 'description' => 'Sabun mandi, deterjen, pewangi'],
                    ['name' => 'Sampo & Perawatan Rambut', 'description' => 'Sampo, kondisioner'],
                    ['name' => 'Pembersih Lantai', 'description' => 'Cairan pembersih lantai & toilet'],
                ],
            ],
            [
                'name'        => 'Perawatan Diri',
                'description' => 'Produk perawatan tubuh & kecantikan',
                'is_active'   => true,
                'subs'        => [
                    ['name' => 'Pasta Gigi & Sikat Gigi', 'description' => 'Pasta gigi, sikat gigi, obat kumur'],
                    ['name' => 'Deodorant & Parfum',      'description' => 'Deodorant, body spray'],
                    ['name' => 'Perawatan Kulit',         'description' => 'Lotion, pelembab, sunscreen'],
                ],
            ],
        ];

        foreach ($data as $item) {
            $subs = $item['subs'];
            unset($item['subs']);

            $category = Category::create($item);

            foreach ($subs as $sub) {
                SubCategory::create([
                    'category_id' => $category->id,
                    'name'        => $sub['name'],
                    'description' => $sub['description'],
                    'is_active'   => true,
                ]);
            }
        }
    }
}

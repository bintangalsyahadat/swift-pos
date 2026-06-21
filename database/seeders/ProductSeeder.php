<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $indomie  = Brand::where('name', 'Indomie')->first();
        $aqua     = Brand::where('name', 'Aqua')->first();
        $nestle   = Brand::where('name', 'Nestle')->first();
        $unilever = Brand::where('name', 'Unilever')->first();
        $wings    = Brand::where('name', 'Wings')->first();
        $mayora   = Brand::where('name', 'Mayora')->first();
        $indofood = Brand::where('name', 'Indofood')->first();
        $pg       = Brand::where('name', 'P&G')->first();

        $makanan  = Category::where('name', 'Makanan')->first();
        $minuman  = Category::where('name', 'Minuman')->first();
        $rmt      = Category::where('name', 'Kebutuhan Rumah Tangga')->first();
        $perawatan = Category::where('name', 'Perawatan Diri')->first();

        $miePasta    = SubCategory::where('name', 'Mie & Pasta')->first();
        $snack       = SubCategory::where('name', 'Snack & Kripik')->first();
        $roti        = SubCategory::where('name', 'Roti & Kue')->first();
        $airMineral  = SubCategory::where('name', 'Air Mineral')->first();
        $tehKopi     = SubCategory::where('name', 'Minuman Teh & Kopi')->first();
        $energi      = SubCategory::where('name', 'Minuman Energi')->first();
        $sabun       = SubCategory::where('name', 'Sabun & Deterjen')->first();
        $sampo       = SubCategory::where('name', 'Sampo & Perawatan Rambut')->first();
        $pembersih   = SubCategory::where('name', 'Pembersih Lantai')->first();
        $gigiSikat   = SubCategory::where('name', 'Pasta Gigi & Sikat Gigi')->first();
        $deodorant   = SubCategory::where('name', 'Deodorant & Parfum')->first();
        $kulitSkin   = SubCategory::where('name', 'Perawatan Kulit')->first();

        $products = [
            // Mie & Pasta
            ['name' => 'Indomie Goreng',            'description' => 'Mie instan rasa goreng', 'price' => 3500,  'base_price' => 2800,  'brand_id' => $indomie->id,  'category_id' => $makanan->id, 'sub_category_id' => $miePasta->id,   'sku' => 'IND-GRG',   'is_active' => true],
            ['name' => 'Indomie Soto',              'description' => 'Mie instan rasa soto',   'price' => 3500,  'base_price' => 2800,  'brand_id' => $indomie->id,  'category_id' => $makanan->id, 'sub_category_id' => $miePasta->id,   'sku' => 'IND-STO',   'is_active' => true],
            ['name' => 'Indomie Kari Ayam',         'description' => 'Mie instan rasa kari',   'price' => 3500,  'base_price' => 2800,  'brand_id' => $indomie->id,  'category_id' => $makanan->id, 'sub_category_id' => $miePasta->id,   'sku' => 'IND-KAR',   'is_active' => true],

            // Snack & Kripik
            ['name' => 'Chitato Sapi Panggang',     'description' => 'Kripik kentang rasa sapi panggang', 'price' => 11000, 'base_price' => 8500,  'brand_id' => $indofood->id, 'category_id' => $makanan->id, 'sub_category_id' => $snack->id, 'sku' => 'CHT-SPI', 'is_active' => true],
            ['name' => 'Oreo Original',             'description' => 'Biskuit Oreo rasa original',        'price' => 8500,  'base_price' => 6500,  'brand_id' => $mayora->id,   'category_id' => $makanan->id, 'sub_category_id' => $snack->id, 'sku' => 'ORE-ORI', 'is_active' => true],
            ['name' => 'Roma Marie Susu',           'description' => 'Biskuit Roma Marie Susu',           'price' => 9000,  'base_price' => 7000,  'brand_id' => $mayora->id,   'category_id' => $makanan->id, 'sub_category_id' => $snack->id, 'sku' => 'ROM-MAR', 'is_active' => true],

            // Roti & Kue
            ['name' => 'Roti Tawar Sari Roti',      'description' => 'Roti tawar 400g', 'price' => 16000, 'base_price' => 13000, 'brand_id' => $indofood->id, 'category_id' => $makanan->id, 'sub_category_id' => $roti->id, 'sku' => 'SRI-TAW', 'is_active' => true],

            // Air Mineral
            ['name' => 'Aqua 600ml',                'description' => 'Air mineral Aqua botol 600ml',  'price' => 4000,  'base_price' => 3000,  'brand_id' => $aqua->id, 'category_id' => $minuman->id, 'sub_category_id' => $airMineral->id, 'sku' => 'AQU-600',  'is_active' => true],
            ['name' => 'Aqua 1500ml',               'description' => 'Air mineral Aqua botol 1.5L',  'price' => 7000,  'base_price' => 5500,  'brand_id' => $aqua->id, 'category_id' => $minuman->id, 'sub_category_id' => $airMineral->id, 'sku' => 'AQU-1500', 'is_active' => true],
            ['name' => 'Le Minerale 600ml',         'description' => 'Air mineral Le Minerale 600ml', 'price' => 4000,  'base_price' => 3000,  'brand_id' => $mayora->id, 'category_id' => $minuman->id, 'sub_category_id' => $airMineral->id, 'sku' => 'LMN-600', 'is_active' => true],

            // Minuman Teh & Kopi
            ['name' => 'Teh Botol Sosro 450ml',     'description' => 'Teh manis Sosro kemasan botol', 'price' => 6000,  'base_price' => 4500, 'brand_id' => $indofood->id, 'category_id' => $minuman->id, 'sub_category_id' => $tehKopi->id, 'sku' => 'SOS-450', 'is_active' => true],
            ['name' => 'Nescafe RTD 250ml',         'description' => 'Kopi Nescafe ready to drink',  'price' => 8000,  'base_price' => 6000, 'brand_id' => $nestle->id,   'category_id' => $minuman->id, 'sub_category_id' => $tehKopi->id, 'sku' => 'NES-250', 'is_active' => true],

            // Minuman Energi
            ['name' => 'Pocari Sweat 500ml',        'description' => 'Minuman isotonik Pocari Sweat', 'price' => 9000,  'base_price' => 7000, 'brand_id' => $wings->id, 'category_id' => $minuman->id, 'sub_category_id' => $energi->id, 'sku' => 'POC-500', 'is_active' => true],
            ['name' => 'Mizone 500ml',              'description' => 'Minuman aktif Mizone',           'price' => 7500,  'base_price' => 5500, 'brand_id' => $unilever->id, 'category_id' => $minuman->id, 'sub_category_id' => $energi->id, 'sku' => 'MIZ-500', 'is_active' => true],

            // Sabun & Deterjen
            ['name' => 'Rinso Anti Noda 800g',      'description' => 'Deterjen bubuk Rinso 800g',    'price' => 25000, 'base_price' => 20000, 'brand_id' => $unilever->id, 'category_id' => $rmt->id, 'sub_category_id' => $sabun->id, 'sku' => 'RNS-800', 'is_active' => true],
            ['name' => 'Lifebuoy Sabun Mandi 110g', 'description' => 'Sabun mandi Lifebuoy',         'price' => 5500,  'base_price' => 4000,  'brand_id' => $unilever->id, 'category_id' => $rmt->id, 'sub_category_id' => $sabun->id, 'sku' => 'LFB-110', 'is_active' => true],
            ['name' => 'So Klin Pewangi 900ml',     'description' => 'Pewangi pakaian So Klin',      'price' => 22000, 'base_price' => 17000, 'brand_id' => $wings->id,    'category_id' => $rmt->id, 'sub_category_id' => $sabun->id, 'sku' => 'SOK-900', 'is_active' => true],

            // Sampo
            ['name' => 'Pantene Sampo 170ml',       'description' => 'Sampo Pantene anti rontok',    'price' => 17000, 'base_price' => 13000, 'brand_id' => $pg->id,       'category_id' => $rmt->id, 'sub_category_id' => $sampo->id, 'sku' => 'PAN-170', 'is_active' => true],
            ['name' => 'Sunsilk Hitam Berkilau 170ml', 'description' => 'Sampo Sunsilk hitam berkilau', 'price' => 16500, 'base_price' => 12500, 'brand_id' => $unilever->id, 'category_id' => $rmt->id, 'sub_category_id' => $sampo->id, 'sku' => 'SUN-170', 'is_active' => true],

            // Pembersih Lantai
            ['name' => 'Wipol Cemara 800ml',        'description' => 'Pembersih lantai Wipol',       'price' => 14000, 'base_price' => 10500, 'brand_id' => $wings->id,    'category_id' => $rmt->id, 'sub_category_id' => $pembersih->id, 'sku' => 'WIP-800', 'is_active' => true],

            // Pasta Gigi
            ['name' => 'Pepsodent Action 190g',     'description' => 'Pasta gigi Pepsodent',         'price' => 13000, 'base_price' => 10000, 'brand_id' => $unilever->id,  'category_id' => $perawatan->id, 'sub_category_id' => $gigiSikat->id, 'sku' => 'PEP-190', 'is_active' => true],
            ['name' => 'Oral-B Sikat Gigi',         'description' => 'Sikat gigi Oral-B',            'price' => 15000, 'base_price' => 11500, 'brand_id' => $pg->id,        'category_id' => $perawatan->id, 'sub_category_id' => $gigiSikat->id, 'sku' => 'ORB-SIK', 'is_active' => true],

            // Deodorant
            ['name' => 'Rexona Men 150ml',          'description' => 'Deodorant Rexona Men spray',   'price' => 25000, 'base_price' => 19000, 'brand_id' => $unilever->id,  'category_id' => $perawatan->id, 'sub_category_id' => $deodorant->id, 'sku' => 'REX-150', 'is_active' => true],

            // Perawatan Kulit
            ['name' => 'Vaseline Lotion 200ml',     'description' => 'Lotion Vaseline pelembab',     'price' => 22000, 'base_price' => 17000, 'brand_id' => $unilever->id,  'category_id' => $perawatan->id, 'sub_category_id' => $kulitSkin->id, 'sku' => 'VAS-200', 'is_active' => true],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['sku' => $product['sku']], $product);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitialStockSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $initialStocks = [
            'IND-GRG' => 200,
            'IND-STO' => 180,
            'IND-KAR' => 150,
            'CHT-SPI' => 100,
            'ORE-ORI' => 120,
            'ROM-MAR' => 90,
            'SRI-TAW' => 50,
            'AQU-600' => 300,
            'AQU-1500' => 200,
            'LMN-600' => 250,
            'SOS-450' => 150,
            'NES-250' => 100,
            'POC-500' => 120,
            'MIZ-500' => 100,
            'RNS-800' => 80,
            'LFB-110' => 150,
            'SOK-900' => 70,
            'PAN-170' => 90,
            'SUN-170' => 80,
            'WIP-800' => 60,
            'PEP-190' => 100,
            'ORB-SIK' => 80,
            'REX-150' => 60,
            'VAS-200' => 70,
        ];

        // Buat satu InventoryAdjustment untuk stok awal
        $adjustment = InventoryAdjustment::create([
            'name'      => 'Stok Awal Sistem',
            'reference' => 'INIT/STOCK/2026',
            'status'    => 'draft',
            'user_id'   => $user->id,
            'notes'     => 'Pengisian stok awal saat sistem pertama kali digunakan.',
        ]);

        foreach ($initialStocks as $sku => $qty) {
            $product = Product::where('sku', $sku)->first();
            if (! $product) continue;

            InventoryAdjustmentDetail::create([
                'inventory_adjustment_id' => $adjustment->id,
                'product_id'              => $product->id,
                'type'                    => 'in',
                'quantity'                => $qty,
            ]);
        }

        // Konfirmasi → trigger boot() → buat StockMove done untuk setiap detail
        $adjustment->update(['status' => 'done']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            // ── Offline ──────────────────────────────────────────────────────
            [
                'name'       => 'Cash',
                'code'       => 'cash',
                'type'       => 'cash',
                'is_online'  => false,
                'is_active'  => true,
                'sort_order' => 1,
                'description' => 'Pembayaran tunai di kasir.',
            ],
            [
                'name'       => 'Credit / Debit Card (EDC)',
                'code'       => 'card_edc',
                'type'       => 'card',
                'is_online'  => false,
                'is_active'  => true,
                'sort_order' => 2,
                'description' => 'Kartu kredit/debit via mesin EDC.',
            ],

            // ── QRIS (Xendit) ─────────────────────────────────────────────────
            [
                'name'                  => 'QRIS',
                'code'                  => 'qris',
                'type'                  => 'qr_code',
                'is_online'             => false,
                'is_active'             => true,
                'sort_order'            => 3,
                'fee_type'              => 'percentage',
                'fee_value'             => 0.70,
                'description'           => 'Bayar via QRIS (GoPay, OVO, DANA, ShopeePay, dll).',
            ],
        ];

        foreach ($methods as $data) {
            PaymentMethod::updateOrCreate(
                ['code' => $data['code']],
                $data,
            );
        }
    }
}

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
                'is_online'             => true,
                'xendit_channel_type'   => 'QR_CODE',
                'xendit_channel_code'   => 'ID_QRIS',
                'is_active'             => true,
                'sort_order'            => 3,
                'fee_type'              => 'percentage',
                'fee_value'             => 0.70,
                'description'           => 'Bayar via QRIS (GoPay, OVO, DANA, ShopeePay, dll).',
            ],

            // ── Virtual Account (Xendit) ──────────────────────────────────────
            [
                'name'                 => 'Virtual Account BCA',
                'code'                 => 'va_bca',
                'type'                 => 'virtual_account',
                'is_online'            => true,
                'xendit_channel_type'  => 'VIRTUAL_ACCOUNT',
                'xendit_channel_code'  => 'BCA',
                'is_active'            => true,
                'sort_order'           => 4,
                'description'          => 'Transfer via Virtual Account BCA.',
            ],
            [
                'name'                 => 'Virtual Account Mandiri',
                'code'                 => 'va_mandiri',
                'type'                 => 'virtual_account',
                'is_online'            => true,
                'xendit_channel_type'  => 'VIRTUAL_ACCOUNT',
                'xendit_channel_code'  => 'MANDIRI',
                'is_active'            => true,
                'sort_order'           => 5,
            ],
            [
                'name'                 => 'Virtual Account BNI',
                'code'                 => 'va_bni',
                'type'                 => 'virtual_account',
                'is_online'            => true,
                'xendit_channel_type'  => 'VIRTUAL_ACCOUNT',
                'xendit_channel_code'  => 'BNI',
                'is_active'            => true,
                'sort_order'           => 6,
            ],
            [
                'name'                 => 'Virtual Account BRI',
                'code'                 => 'va_bri',
                'type'                 => 'virtual_account',
                'is_online'            => true,
                'xendit_channel_type'  => 'VIRTUAL_ACCOUNT',
                'xendit_channel_code'  => 'BRI',
                'is_active'            => true,
                'sort_order'           => 7,
            ],
            [
                'name'                 => 'Virtual Account Permata',
                'code'                 => 'va_permata',
                'type'                 => 'virtual_account',
                'is_online'            => true,
                'xendit_channel_type'  => 'VIRTUAL_ACCOUNT',
                'xendit_channel_code'  => 'PERMATA',
                'is_active'            => false,
                'sort_order'           => 8,
            ],

            // ── E-Wallet (Xendit) ─────────────────────────────────────────────
            [
                'name'                 => 'GoPay',
                'code'                 => 'gopay',
                'type'                 => 'ewallet',
                'is_online'            => true,
                'xendit_channel_type'  => 'EWALLET',
                'xendit_channel_code'  => 'ID_GOPAY',
                'is_active'            => true,
                'sort_order'           => 9,
            ],
            [
                'name'                 => 'OVO',
                'code'                 => 'ovo',
                'type'                 => 'ewallet',
                'is_online'            => true,
                'xendit_channel_type'  => 'EWALLET',
                'xendit_channel_code'  => 'ID_OVO',
                'is_active'            => true,
                'sort_order'           => 10,
            ],
            [
                'name'                 => 'DANA',
                'code'                 => 'dana',
                'type'                 => 'ewallet',
                'is_online'            => true,
                'xendit_channel_type'  => 'EWALLET',
                'xendit_channel_code'  => 'ID_DANA',
                'is_active'            => true,
                'sort_order'           => 11,
            ],
            [
                'name'                 => 'ShopeePay',
                'code'                 => 'shopeepay',
                'type'                 => 'ewallet',
                'is_online'            => true,
                'xendit_channel_type'  => 'EWALLET',
                'xendit_channel_code'  => 'ID_SHOPEEPAY',
                'is_active'            => true,
                'sort_order'           => 12,
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

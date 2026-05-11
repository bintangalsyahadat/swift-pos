<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // General
            ['group' => 'general', 'key' => 'general.store_name',           'value' => 'SwiftPOS'],
            ['group' => 'general', 'key' => 'general.currency',             'value' => 'IDR'],
            ['group' => 'general', 'key' => 'general.timezone',             'value' => 'Asia/Jakarta'],
            ['group' => 'general', 'key' => 'general.receipt_footer',       'value' => 'Thank you for shopping with us!'],
            ['group' => 'general', 'key' => 'general.default_customer_id',  'value' => null],

            // Xendit
            ['group' => 'xendit', 'key' => 'xendit.enabled',       'value' => '0'],
            ['group' => 'xendit', 'key' => 'xendit.secret_key',    'value' => ''],
            ['group' => 'xendit', 'key' => 'xendit.public_key',    'value' => ''],
            ['group' => 'xendit', 'key' => 'xendit.webhook_token', 'value' => ''],
            ['group' => 'xendit', 'key' => 'xendit.environment',   'value' => 'sandbox'],
        ];

        foreach ($defaults as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}

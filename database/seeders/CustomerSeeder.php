<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Budi Santoso',    'email' => 'budi@example.com',    'phone' => '081234567890', 'address' => 'Jl. Mawar No. 10, Jakarta'],
            ['name' => 'Siti Rahayu',     'email' => 'siti@example.com',    'phone' => '082345678901', 'address' => 'Jl. Melati No. 5, Bandung'],
            ['name' => 'Agus Wijaya',     'email' => 'agus@example.com',    'phone' => '083456789012', 'address' => 'Jl. Anggrek No. 3, Surabaya'],
            ['name' => 'Dewi Permata',    'email' => 'dewi@example.com',    'phone' => '084567890123', 'address' => 'Jl. Kenanga No. 8, Yogyakarta'],
            ['name' => 'Riko Pratama',    'email' => 'riko@example.com',    'phone' => '085678901234', 'address' => 'Jl. Dahlia No. 2, Medan'],
            ['name' => 'Rina Kusuma',     'email' => 'rina@example.com',    'phone' => '086789012345', 'address' => 'Jl. Cempaka No. 7, Semarang'],
            ['name' => 'Hendra Saputra',  'email' => 'hendra@example.com',  'phone' => '087890123456', 'address' => 'Jl. Bougenville No. 1, Makassar'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri@example.com',   'phone' => '088901234567', 'address' => 'Jl. Lavender No. 14, Bali'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}

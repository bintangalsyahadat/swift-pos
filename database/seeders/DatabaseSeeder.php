<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@swiftpos.com',
            'password' => Hash::make('12345678'),
        ]);

        $this->command->call('shield:super-admin', ['--user' => $user->id, '--panel' => 'admin']);

        $this->call([
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            InitialStockSeeder::class,
            OrderSeeder::class,
            PaymentMethodSeeder::class,
            SettingSeeder::class,
        ]);
    }
}

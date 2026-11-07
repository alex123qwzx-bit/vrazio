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
        // 1. Create admin user
        User::create([
            'name' => 'VRAZIO Admin',
            'email' => 'admin@vrazio.com',
            'phone' => '+919999999999',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        // 2. Create 10 patron users via factory
        User::factory()->count(10)->create();

        // 3. Create 24 products (detailed, realistic jewellery data)
        $this->call(ProductSeeder::class);

        // 4. Seed site theme settings with default values
        $this->call(SiteThemeSettingSeeder::class);
    }
}

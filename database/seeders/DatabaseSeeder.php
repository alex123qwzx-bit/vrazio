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
        User::firstOrCreate(
            ['email' => 'admin@vrazio.com'],
            [
                'name' => 'VRAZIO Admin',
                'phone' => '+919999999999',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        // 2. Create sample patron users without Faker
        $samplePatrons = [
            ['name' => 'Aarav Sharma', 'email' => 'aarav.sharma@example.com', 'phone' => '+919876543210'],
            ['name' => 'Ananya Iyer', 'email' => 'ananya.iyer@example.com', 'phone' => '+919876543211'],
            ['name' => 'Rohan Mehta', 'email' => 'rohan.mehta@example.com', 'phone' => '+919876543212'],
        ];

        foreach ($samplePatrons as $patron) {
            User::firstOrCreate(
                ['email' => $patron['email']],
                [
                    'name' => $patron['name'],
                    'phone' => $patron['phone'],
                    'role' => 'patron',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ]
            );
        }

        // 3. Create 24 products (detailed, realistic jewellery data)
        $this->call(ProductSeeder::class);

        // 4. Seed site theme settings with default values
        $this->call(SiteThemeSettingSeeder::class);
    }
}

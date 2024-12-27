<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'full_name' => 'Test User',
            'mobile' => '09001000000',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'full_name' => 'Test User',
            'mobile' => '09123553854',
            'email' => 'm.pouretemadi@digikala.com',
        ]);

        $this->call([
            AdminSeeder::class,
            LocationSeeder::class,
            BrandSeeder::class,
            TagSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ShippingMethodSeeder::class,
            BannerSeeder::class,
            DiscountSeeder::class,
//            OrderSeeder::class,
//            TicketSeeder::class
        ]);
    }
}

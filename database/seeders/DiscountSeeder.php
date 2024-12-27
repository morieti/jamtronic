<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        Discount::query()->create([
            'code' => 'FIRST',
            'type' => 'percentage',
            'value' => '100',
            'min_order_value' => '10000',
            'usage_limit' => '10000',
            'per_user_limit' => '1',
            'starts_at' => '2024-12-25 00:00:00',
            'expires_at' => '2025-01-30 23:59:59',
            'is_active' => true,
            'is_free_shipping' => true
        ]);
    }
}

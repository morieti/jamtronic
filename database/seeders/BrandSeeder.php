<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run()
    {
        Brand::create([
            'name' => 'HP',
            'slug' => 'HP'
        ]);

        Brand::create([
            'name' => 'Kento ژاپنی',
            'slug' => 'KENTO'
        ]);
    }
}

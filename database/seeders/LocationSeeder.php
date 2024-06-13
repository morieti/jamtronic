<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run()
    {
        Region::query()->create([
            'name' => 'تهران',
            'slug' => 'tehran',
        ]);

        Region::query()->create([
            'name' => 'مازندران',
            'slug' => 'mazandaran',
        ]);

        City::query()->create([
            'region_id' => 1,
            'name' => 'تهران',
            'slug' => 'tehran'
        ]);

        City::query()->create([
            'region_id' => 2,
            'name' => 'ساری',
            'slug' => 'sari'
        ]);
    }
}

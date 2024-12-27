<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            'alfa',
            'amtech',
            'DTEC',
            'goot',
            'lotfett',
            'rdier',
            'st',
            'welsolo',
            'آساهی',
            'آسران',
            'اسکلاره',
            'امگا',
            'باکو',
            'پروسکیت',
            'تروساردی',
            'روبیکن',
            'ریلایف',
            'سان شاین',
            'سومو',
            'فلورمار',
            'کامان',
            'کملیون',
            'کویک',
            'لوداستار',
            'مای',
            'مکانیک',
            'هاکو',
            'یاکسون'
        ];

        foreach ($brands as $brand) {
            Brand::create([
                'name' => $brand,
                'slug' => $brand
            ]);
        }
    }
}

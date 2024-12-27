<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run()
    {
        Banner::query()->create([
            'slug' => 'FirstOrderFreeShipping',
            'title' => 'ارسال رایگان اولین سفارش',
            'subtitle' => 'با کد: FIRST',
            'image' => 'freeshipping.png',
            'link' => 'https://jamtronic.com',
            'type' => 'top'
        ]);
    }
}

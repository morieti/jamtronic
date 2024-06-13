<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run()
    {
        ShippingMethod::create([
            'type' => 'express',
            'title' => 'ارسال اکسپرس تهران',
            'subtitle' => '',
            'price_caption' => 'رایگان (سفارشات بالای ۲ میلیون تومان)',
            'parent_id' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);

        ShippingMethod::create([
            'type' => 'post',
            'title' => 'پست پیشتاز',
            'subtitle' => '۱ تا ۳ روز کاری پس از تحویل به پست',
            'price_caption' => 'نرخ ثابت ۳۵.۰۰۰ تومان',
            'price' => 35000,
            'parent_id' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);

        ShippingMethod::create([
            'type' => 'in_person',
            'title' => 'تحویل حضوری (در محل فروشگاه)',
            'subtitle' => '',
            'price_caption' => '',
            'parent_id' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run()
    {
        ShippingMethod::create([
            'type' => 'post',
            'title' => 'ارسال با پست',
            'subtitle' => '۱ تا ۳ روز کاری پس از تحویل به پست',
            'price_caption' => 'نرخ ثابت ۶۵.۰۰۰ تومان',
            'price' => 65000,
            'parent_id' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);

        ShippingMethod::create([
            'type' => 'express',
            'title' => 'ارسال با پیک شهر تهران',
            'subtitle' => 'پس کرایه',
            'price_caption' => 'متوسط هزینه ارسال 200 هزار تومان به عهده مشتری است',
            'parent_id' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);

        ShippingMethod::create([
            'type' => 'express',
            'title' => 'ارسال با تیپاکس',
            'subtitle' => 'پس کرایه',
            'price_caption' => 'متوسط هزینه ارسال 90 هزار تومان بر عهده مشتری است',
            'parent_id' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);

        ShippingMethod::create([
            'type' => 'in_person',
            'title' => 'تحویل حضوری',
            'subtitle' => 'در محل فروشگاه',
            'price_caption' => '',
            'parent_id' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);
    }
}

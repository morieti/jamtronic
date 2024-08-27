<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TicketSubject;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run()
    {
        TicketSubject::create([
            'subject' => 'Technical Issue',
            'sort' => 1
        ]);

        TicketSubject::create([
            'subject' => 'Sales',
            'sort' => 2
        ]);


        TicketSubject::create([
            'subject' => 'Order Tracking',
            'sort' => 3
        ]);
    }
}

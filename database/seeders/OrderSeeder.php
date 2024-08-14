<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        UserAddress::create([
            'user_id' => 2,
            'region_id' => 1,
            'city_id' => 1,
            'receiver_name' => 'Morteza',
            'receiver_mobile' => '09123553854',
            'address' => 'My Address',
            'postal_code' => '1234567890',
            'description' => 'My Address Description',
            'lat' => '12.34',
            'lng' => '12.34',
        ]);

        Order::create([
            'user_id' => 2,
            'user_address_id' => 1,
            'shipping_method_id' => 1,
            'total_price' => 50000,
            'status' => Order::STATUS_PAYMENT_FAILED,
            'short_address' => 'My short address',
            'short_shipping_data' => 'Express Tehran',
            'payment_gateway' => 'saman',
            'use_wallet' => false,
            'wallet_price_used' => 0
        ]);

        Order::create([
            'user_id' => 2,
            'user_address_id' => 1,
            'shipping_method_id' => 1,
            'total_price' => 50000,
            'status' => Order::STATUS_PAYMENT_SUCCESS,
            'short_address' => 'My short address',
            'short_shipping_data' => 'Express Tehran',
            'payment_gateway' => 'saman',
            'use_wallet' => false,
            'wallet_price_used' => 0
        ]);

        Order::create([
            'user_id' => 2,
            'user_address_id' => 1,
            'shipping_method_id' => 1,
            'total_price' => 52000,
            'status' => Order::STATUS_USER_CANCELED,
            'short_address' => 'My short address',
            'short_shipping_data' => 'Express Tehran',
            'payment_gateway' => 'saman',
            'use_wallet' => false,
            'wallet_price_used' => 0
        ]);

        OrderItem::create([
            'order_id' => 2,
            'payable_id' => 1,
            'payable_type' => Payment::class,
            'quantity' => 1,
            'price' => 48100,
        ]);

        Payment::create([
            'order_id' => 1,
            'amount' => 50000,
            'payment_method' => 'saman',
            'payment_status' => Payment::STATUS_FAILED,
            'transaction_id' => '1000-2000-3000',
        ]);

        Payment::create([
            'order_id' => 2,
            'amount' => 50000,
            'payment_method' => 'saman',
            'payment_status' => Payment::STATUS_VERIFIED,
            'transaction_id' => '3000-2000-1000',
        ]);
    }
}

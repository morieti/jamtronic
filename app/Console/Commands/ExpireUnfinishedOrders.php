<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUnfinishedOrders extends Command
{
    protected $signature = 'schedule:expire-unfinished-orders';
    protected $description = 'Expire Unfinished Orders Older than 45 minutes';

    public function handle()
    {
        try {
            DB::beginTransaction();

            /** @var Order[] $orders */
            $orders = Order::query()
                ->with('items', 'items.payable')
                ->where('updated_at', '<', now()->subMinutes(45))
                ->whereIn('status', [Order::STATUS_CHECKOUT, Order::STATUS_PENDING_PAYMENT])
                ->get();

            foreach ($orders as $order) {
                $order->transitionTo(Order::STATUS_EXPIRED);
                $order->getBackInventories();
                $order->save();
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            logger($exception->getMessage());
        }

        $this->info('Unfinished Orders expired successfully.');
    }
}

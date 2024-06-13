<?php

namespace App\Listeners;

use App\Events\WalletBalanceUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WalletBalanceListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param WalletBalanceUpdated $event
     * @return void
     */
    public function handle(WalletBalanceUpdated $event)
    {
        if ($event->order) {
            $event->user->walletHistory()->create([
                'title' => __('Order ID: :id', ['id' => $event->order->id]),
                'subtitle' => __('Online Payment :gateway', ['gateway' => __($event->order->payment_gateway)]),
                'amount' => $event->order->total_price
            ]);

            if ($event->order->wallet_price_used > 0) {
                $event->user->walletHistory()->create([
                    'title' => __('Order ID: :id', ['id' => $event->order->id]),
                    'subtitle' => __('Wallet Use'),
                    'amount' => -((int)$event->order->wallet_price_used)
                ]);
            }
        } else {
            $event->user->walletHistory()->create([
                'title' => $event->title,
                'subtitle' => $event->subtitle,
                'amount' => $event->amount
            ]);
        }
    }
}

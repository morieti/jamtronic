<?php

namespace App\Providers;

use App\Events\WalletBalanceUpdated;
use App\Listeners\WalletBalanceListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        WalletBalanceUpdated::class => [
            WalletBalanceListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }
}

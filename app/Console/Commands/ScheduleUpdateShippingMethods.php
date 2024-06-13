<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateShippingMethods;

class ScheduleUpdateShippingMethods extends Command
{
    protected $signature = 'schedule:update-shipping-methods';
    protected $description = 'Dispatch the UpdateShippingMethods job';

    public function handle()
    {
        UpdateShippingMethods::dispatch();
        $this->info('UpdateShippingMethods job dispatched successfully.');
    }
}

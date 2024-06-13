<?php

use App\Console\Commands\ExpireUnfinishedOrders;
use App\Console\Commands\ExpireUnfinishedPayments;
use App\Console\Commands\ScheduleUpdateShippingMethods;
use App\Console\Commands\DeleteExpiredFavorites;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\MarkTicketsAsDone;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->everyMinute();

Schedule::command(ScheduleUpdateShippingMethods::class)->hourly();

Schedule::command(ExpireUnfinishedPayments::class)->everyMinute();
Schedule::command(ExpireUnfinishedOrders::class)->everyMinute();

Schedule::command(DeleteExpiredFavorites::class)->daily();

Schedule::command(MarkTicketsAsDone::class)->everySixHours();

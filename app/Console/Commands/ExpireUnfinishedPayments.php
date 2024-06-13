<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class ExpireUnfinishedPayments extends Command
{
    protected $signature = 'schedule:expire-unfinished-payments';
    protected $description = 'Expire Unfinished Payments Older than 16 minutes';

    public function handle()
    {
        Payment::query()
            ->where('created_at', '<', now()->subMinutes(16))
            ->where('payment_status', Payment::STATUS_PENDING)
            ->update(['payment_status' => Payment::STATUS_EXPIRED]);

        $this->info('Unfinished Payments expired successfully.');
    }
}

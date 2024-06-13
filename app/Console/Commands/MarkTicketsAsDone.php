<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class MarkTicketsAsDone extends Command
{
    protected $signature = 'schedule:mark-tickets-as-done';
    protected $description = 'Mark tickets as done';

    public function handle()
    {
        Ticket::query()
            ->where('status', Ticket::STATUS_RESPONDED)
            ->whereDate('updated_at', '<', now()->subDays(2))
            ->update(['status' => Ticket::STATUS_CLOSED]);

        $this->info('Tickets marked as done');
    }
}

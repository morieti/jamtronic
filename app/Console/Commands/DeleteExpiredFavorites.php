<?php

namespace App\Console\Commands;

use App\Models\UserFavorite;
use Illuminate\Console\Command;

class DeleteExpiredFavorites extends Command
{
    protected $signature = 'schedule:delete-expired-favorites';
    protected $description = 'Delete expired favorites older than 1 month';

    public function handle()
    {
        UserFavorite::query()
            ->whereDate('expired_at', '<', now())
            ->delete();

        $this->info('Expired favorites deleted');
    }
}

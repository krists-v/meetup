<?php

namespace App\Console\Commands;

use App\Support\JoinRequestExpiry;
use Illuminate\Console\Command;

class ExpireJoinRequests extends Command
{
    protected $signature = 'join-requests:expire';

    protected $description = 'Expire join requests the host has not answered';

    public function handle(): int
    {
        $expired = JoinRequestExpiry::expire();

        $this->info('Expired '.$expired.' join '.($expired === 1 ? 'request' : 'requests').'.');

        return self::SUCCESS;
    }
}

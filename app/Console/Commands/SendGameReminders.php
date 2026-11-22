<?php

namespace App\Console\Commands;

use App\Support\GameReminders;
use Illuminate\Console\Command;

class SendGameReminders extends Command
{
    protected $signature = 'games:remind';

    protected $description = 'Remind people about a game about three hours before it starts';

    public function handle(): int
    {
        $sent = GameReminders::send();

        $this->info('Sent '.$sent.' game '.($sent === 1 ? 'reminder' : 'reminders').'.');

        return self::SUCCESS;
    }
}

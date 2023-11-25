<?php

namespace Seatplus\BroadcastHub\Commands;

use Illuminate\Console\Command;
use Seatplus\BroadcastHub\Commands\CorporationTracking\CorporationTrackingCommand;

class BroadcastAllCommand extends Command
{
    protected $signature = 'broadcast-hub:all';

    protected $description = 'Broadcast all messages';

    public function handle()
    {
        $this->info('Broadcasting all messages');

        $this->call(CorporationTrackingCommand::class);

        return self::SUCCESS;
    }
}

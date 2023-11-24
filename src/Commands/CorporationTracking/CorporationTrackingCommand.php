<?php

namespace Seatplus\BroadcastHub\Commands\CorporationTracking;

use Illuminate\Console\Command;
use Seatplus\Eveapi\Models\Corporation\CorporationMemberTracking;

class CorporationTrackingCommand extends Command
{
    protected $signature = 'broadcast-hub:corporation-tracking {corporation_id?}';

    protected $description = 'Notify when a corporation member joins or leaves';

    public function handle(): void
    {
        $corporation_id = $this->argument('corporation_id');

        $corporation_id ? $this->handleSingleEntityInvocation($corporation_id) : $this->handleSubscriptions();

    }

    private function handleSingleEntityInvocation(string $corporation_id): void
    {
        $this->info("Notifying for corporation {$corporation_id}");

        $this->call(NewCorporationMemberCommand::class, ['corporation_id', (int) $corporation_id]);
    }

    private function handleSubscriptions(): void
    {
        CorporationMemberTracking::query()
            ->select('corporation_id')
            // get distinct values
            ->distinct()
            ->pluck('corporation_id')
            ->each(fn (int $corporation_id) => $this->handleSingleEntityInvocation($corporation_id));
    }
}

<?php

namespace Seatplus\BroadcastHub\Commands\CorporationTracking;

use Illuminate\Support\Collection;
use Seatplus\BroadcastHub\Commands\NotificationCommand;
use Seatplus\BroadcastHub\Notifications\NewCorporationMember;
use Seatplus\Eveapi\Models\Corporation\CorporationInfo;
use Seatplus\Eveapi\Models\Corporation\CorporationMemberTracking;

class NewCorporationMemberCommand extends NotificationCommand
{
    protected $signature = 'broadcast-hub:corporation-tracking:new-member {corporation_id?}';

    protected $description = 'Notify when a new corporation member joins';

    protected string $argument_key = 'corporation_id';

    protected string $notification_class = NewCorporationMember::class;

    protected string $entity_type = CorporationInfo::class;

    protected function getGroupedModels(Collection $subscribable_ids): Collection
    {
        // then we query the corporation tracking table for all corporation_ids
        $corporation_members = CorporationMemberTracking::query() // @phpstan-ignore-linerm
            ->with('character')
            ->whereIn('corporation_id', $subscribable_ids)
            ->select(['character_id', 'start_date', 'corporation_id'])
            ->get();

        // then we group the corporation members by corporation_id
        return $corporation_members->groupBy('corporation_id');
    }

    protected function buildNotificationArguments(string $entity_id, Collection $original_payload, Collection $new_payload): array
    {
        // get new entries in new_corporation_members that are not in old_corporation_members
        $new_corporation_members = $new_payload->diff($original_payload);

        // get corporation
        $corporation = CorporationInfo::find($entity_id);

        return [$new_corporation_members->toArray(), $corporation];
    }
}

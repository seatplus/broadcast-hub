<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Seatplus\BroadcastHub\Commands\CorporationTracking\CorporationTrackingCommand;
use Seatplus\Eveapi\Models\Corporation\CorporationMemberTracking;

it('runs the command without error', function () {

    // prevent notifications from being sent
    Notification::fake();

    // prevent events from being fired
    Event::fake();

    // prevent any http requests from being made
    \Illuminate\Support\Facades\Http::fake();

    // create CorporationMemberTracking model
    $corporation_member_tracking = CorporationMemberTracking::factory()->create([
        'corporation_id' => \Seatplus\Eveapi\Models\Corporation\CorporationInfo::factory(),
    ]);

    // run the command
    $this->artisan(CorporationTrackingCommand::class)
        ->assertExitCode(0);

});

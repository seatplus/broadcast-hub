<?php

use Illuminate\Support\Facades\Event;
use Seatplus\Auth\Models\User;
use Seatplus\BroadcastHub\Http\Actions\GetAvailableNotifications;
use Seatplus\BroadcastHub\Notifications\NewCorporationMember;
use Seatplus\Eveapi\Models\Character\CharacterInfo;
use Seatplus\Eveapi\Models\Corporation\CorporationInfo;
use Seatplus\Eveapi\Models\Corporation\CorporationMemberTracking;

use function Pest\Laravel\actingAs;

it('returns owned notification from action', function () {

    Event::fake();

    [$user, $character, $notification] = prepareNotificationTest();

    // give user corporation roles
    updateCharacterRoles($character, ['Director']);

    // give user permission
    assignPermissionToUser($user, 'view member tracking');

    // act as user
    actingAs($user);

    // run action
    $available_notifications = (new GetAvailableNotifications)->execute(
        notification: get_class($notification),
        recipient_id: $user->id,
    );

    expect($available_notifications)->toHaveCount(1)
        // expect first notification to have title
        ->and(data_get($available_notifications, '0.title'))->toBe($notification::getTitle())
        // expect first notification to have owned
        ->and(data_get($available_notifications, '0.owned'))->toBeTrue()
        // expect first notification to have subscribed
        ->and(data_get($available_notifications, '0.subscribed'))->toBeFalse();

});

it('returns not owned notifications from assets for superusers', function () {
    [, , $notification] = prepareNotificationTest();

    // create user
    $user = Event::fakeFor(fn () => User::factory()->create());

    // give user superuser
    assignPermissionToUser($user, 'superuser');

    actingAs($user);

    // run action
    $available_notifications = (new GetAvailableNotifications)->execute(
        notification: get_class($notification),
        recipient_id: $user->id,
    );

    expect($available_notifications)->toHaveCount(1)
        // expect first notification to have title
        ->and(data_get($available_notifications, '0.title'))->toBe(NewCorporationMember::getTitle())
        // expect first notification to have owned
        ->and(data_get($available_notifications, '0.owned'))->toBeFalse()
        // expect first notification to have subscribed
        ->and(data_get($available_notifications, '0.subscribed'))->toBeFalse();

});

it('throws exception if validation fails', function () {
    $new_corporation_members = [
        [
            'start_date' => now(),
        ],
    ];

    $corporation_info = CorporationInfo::factory()->make();

    // create NewCorporationNotification
    new class($new_corporation_members, $corporation_info) extends NewCorporationMember
    {
        public function via(): string
        {
            return 'broadcasthub';
        }

        public function toBroadcaster(): object
        {
            return new class
            {
                public function broadcastOn(): string
                {
                    return 'test';
                }
            };
        }
    };
})->throws(\Exception::class, 'The 0.character field is required.');

function prepareNotificationTest()
{

    $new_corporation_members = [
        [
            'start_date' => now(),
            'character' => [
                'character_id' => 1,
                'name' => 'test',
            ],
        ],
    ];

    $corporation_info = CorporationInfo::factory()->make();

    // create NewCorporationNotification
    $notification = new class($new_corporation_members, $corporation_info) extends NewCorporationMember
    {
        public function via(): string
        {
            return 'broadcasthub';
        }

        public function toBroadcaster(): object
        {
            return new class
            {
                public function broadcastOn(): string
                {
                    return 'test';
                }
            };
        }
    };

    // prevent any event from being fired
    Event::fake();

    // create user
    $user = Event::fakeFor(fn () => User::factory()->create());

    $character = $user->characters->first();

    // expect user to have character
    expect($character)->toBeInstanceOf(CharacterInfo::class)
        // expect main_character to have corporation
        ->and($character->corporation)->toBeInstanceOf(CorporationInfo::class);

    // create corporation member tracking
    CorporationMemberTracking::factory()->create([
        'corporation_id' => $character->corporation->corporation_id,
    ]);

    return [$user, $character, $notification];
}

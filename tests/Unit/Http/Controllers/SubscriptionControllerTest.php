<?php

use Seatplus\BroadcastHub\Subscription;

it('returns available notifications for broadcaster', function () {

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub']);

    $broadcaster_mock = mock(\Seatplus\BroadcastHub\Contracts\Broadcaster::class, function ($mock) {
        $mock->shouldReceive('getImplementedNotificationClasses')
            ->once()
            ->andReturn([
                'Seatplus\BroadcastHub\Notifications\Broadcasts\BroadcastNotification',
                'Seatplus\BroadcastHub\Notifications\Broadcasts\BroadcastNotification2',
            ]);
    });

    $this->mock(\Seatplus\BroadcastHub\BroadcastRepository::class)
        ->shouldReceive('getBroadcaster')
        ->once()
        ->andReturn($broadcaster_mock);

    $response = test()->actingAs($user)->get(route('subscriptions.index', 'eveonline'));

    $response->assertOk();

    // expect response  to be an array of the base_64 encoded notification classes
    expect($response->json())->toHaveCount(2)
        ->and($response->json())->toMatchArray([
            base64_encode('Seatplus\BroadcastHub\Notifications\Broadcasts\BroadcastNotification'),
            base64_encode('Seatplus\BroadcastHub\Notifications\Broadcasts\BroadcastNotification2'),
        ]);

});

it('returns available notifications to subscribe to. There will be one notification per entity to subscribe to', function () {

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub']);

    $this->mock(\Seatplus\BroadcastHub\Http\Actions\GetAvailableNotifications::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn([
            [
                'id' => 1,
                'name' => 'channel1',
            ],
            [
                'id' => 2,
                'name' => 'channel2',
            ],
        ]);

    $response = test()
        ->actingAs($user)
        ->get(route('subscriptions.show', ['notification class', 'recipient']));

    $response->assertOk();

    // expect response to be json
    expect($response->json())->toHaveCount(2)
        ->and($response->json())->toMatchArray([
            [
                'id' => 1,
                'name' => 'channel1',
            ],
            [
                'id' => 2,
                'name' => 'channel2',
            ],
        ]);

});

it('stores subscripiton', function () {

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub']);

    // as entity we chose the first character of the user
    $character = $user->characters->first();

    // create notification class
    $notification = new class implements \Seatplus\BroadcastHub\Contracts\Notification
    {
        public function via(): array
        {
            return ['broadcast'];
        }

        public static function getPermissions(): array
        {
            return [
                'view broadcasts hub',
            ];
        }

        public static function getCorporationRoles(): ?array
        {
            return null;
        }

        public static function getTitle(): string
        {
            return 'title';
        }

        public static function getDescription(): string
        {
            return 'description';
        }

        public static function getModel(): string
        {
            return \Seatplus\Auth\Models\Character::class;
        }

        public static function getIdentifier(): string
        {
            return 'character_id';
        }

        public static function getEntityType(): string
        {
            return 'character';
        }

        public function toBroadcaster(): object
        {
            return new class
            {
                public function getIdentifier()
                {
                    return 1;
                }
            };
        }
    };

    $notification_class = get_class($notification);

    // expect Subscription to be empty
    expect(Subscription::all())->toHaveCount(0);

    // because recipient_id is a foreign key, we need to create a recipient first
    $recipient = \Seatplus\BroadcastHub\Recipient::factory()->create();

    // create request

    $response = test()
        ->actingAs($user)
        ->post(route('subscriptions.store'), [
            'entity_id' => $character->character_id,
            'entity_type' => \Seatplus\Eveapi\Models\Character\CharacterInfo::class,
            'notification' => $notification_class,
            'recipient_id' => $recipient->id,
        ]);

    $response->assertOk();

    // expect Subscription to be created
    expect(Subscription::all())->toHaveCount(1)
        ->and(Subscription::first())->toMatchArray([
            'subscribable_id' => $character->character_id,
            'subscribable_type' => \Seatplus\Eveapi\Models\Character\CharacterInfo::class,
            'notification' => $notification_class,
            'recipient_id' => $recipient->id,
        ]);

});

it('fails store request if notification is not subclass of \Seatplus\BroadcastHub\Contracts\Notification::class', function () {
    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub']);

    // as entity we chose the first character of the user
    $character = $user->characters->first();

    // expect Subscription to be empty
    expect(Subscription::all())->toHaveCount(0);

    // because recipient_id is a foreign key, we need to create a recipient first
    $recipient = \Seatplus\BroadcastHub\Recipient::factory()->create();

    // create notification class

    $response = test()
        ->actingAs($user)
        ->post(route('subscriptions.store'), [
            'entity_id' => $character->character_id,
            'entity_type' => \Seatplus\Eveapi\Models\Character\CharacterInfo::class,
            'notification' => \Seatplus\Eveapi\Models\Character\CharacterInfo::class,
            'recipient_id' => $recipient->id,
        ]);

    // the validation should fail
    $response->assertStatus(302);

    // exception message should be test is not a subclass of \Seatplus\BroadcastHub\Contracts\Notification::class
    expect($response->exception->getMessage())->toEqual('Seatplus\Eveapi\Models\Character\CharacterInfo is not a subclass of Seatplus\BroadcastHub\Contracts\Notification or Seatplus\BroadcastHub\Notifications\GlobalNotification');

});

it('destroys the subscription by subscription_id', function () {
    $subscription = Subscription::factory()->create();

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub']);

    $response = test()
        ->actingAs($user)
        ->delete(route('subscriptions.destroy', $subscription->id));

    $response->assertOk();

    expect(Subscription::all())->toHaveCount(0);
});

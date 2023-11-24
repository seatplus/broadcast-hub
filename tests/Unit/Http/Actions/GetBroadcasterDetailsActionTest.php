<?php

use Illuminate\Support\Facades\Event;
use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Contracts\Broadcaster;
use Seatplus\BroadcastHub\Http\Actions\GetBroadcasterDetailsAction;
use Seatplus\Connector\Http\Actions\AddConnectorDetailsAction;

it('returns broadcaster details', function () {

    $broadcaster_implementation = mock(Broadcaster::class, function ($mock) {
        $mock->shouldReceive('getSettings->getValue')
            ->once()
            ->with('broadcaster.enabled', false)
            ->andReturn(true);
    });

    $broadcast_repository_mock = $this->mock(BroadcastRepository::class, function ($mock) use ($broadcaster_implementation) {
        $mock->shouldReceive('getBroadcaster')
            ->once()
            ->with('twitch')
            ->andReturn($broadcaster_implementation);
    });

    $add_connector_details__action_mock = $this->mock(AddConnectorDetailsAction::class, function ($mock) {
        $mock->shouldReceive('setAdminPermission->setIsDisabled->execute')
            ->once()
            ->andReturn([
                'status' => 'Registered',
                'name' => 'Twitch',
                'description' => 'Twitch.tv',
                'logo' => 'https://static-cdn.jtvnw.net/jtv_user_pictures/twitch-profile_image-1a1a1a1a1a1a1a1a-300x300.png',
                'url' => 'https://www.twitch.tv',
                'color' => '#6441a5',
            ]);
    });

    // create user
    $user = Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    // acting as user
    \Pest\Laravel\actingAs($user);

    // run the action
    $result = (new GetBroadcasterDetailsAction($broadcast_repository_mock, $add_connector_details__action_mock))->execute('twitch');

    // assert the result
    expect($result)->toHaveKeys([
        'status',
        'name',
        'description',
        'logo',
        'url',
        'color',
        'can_manage',
    ]);

});

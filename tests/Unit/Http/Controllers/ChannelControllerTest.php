<?php

use Seatplus\BroadcastHub\Http\Actions\SynchronizeChannelsAction;

it('gets channel index', function () {

    // given
    $broadcaster_string = 'eveonline';

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub', 'manage broadcasts channel']);

    $broadcaster_mock = mock(\Seatplus\BroadcastHub\Contracts\Broadcaster::class)
        ->shouldReceive('getChannels')
        ->andReturn([
            [
                'id' => 1,
                'name' => 'channel1',
            ],
            [
                'id' => 2,
                'name' => 'channel2',
            ],
        ])
        ->getMock();

    $this->mock(\Seatplus\BroadcastHub\BroadcastRepository::class)
        ->shouldReceive('getBroadcaster')
        ->with($broadcaster_string)
        ->andReturn($broadcaster_mock);

    // run
    $response = test()
        ->actingAs($user)
        ->get(route('channels.index', $broadcaster_string));

    // expect
    $response->assertOk();
});

it('synchronizes channels', function () {

    // given
    $broadcaster_string = 'eveonline';
    $encoded_broadcaster = base64_encode($broadcaster_string);

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub', 'manage broadcasts channel']);

    $this->mock(SynchronizeChannelsAction::class)
        ->shouldReceive('execute')
        ->once();

    // run
    $response = test()
        ->actingAs($user)
        ->put(route('channels.store', $encoded_broadcaster), [
            'checkedChannels' => [
                [
                    'id' => 1,
                    'name' => 'channel1',
                ],
                [
                    'id' => 2,
                    'name' => 'channel2',
                ],
            ],
        ]);

    // expect
    $response->assertOk();
});

it('throws error if id is not string or int', function () {

    // given
    $broadcaster_string = 'eveonline';
    $encoded_broadcaster = base64_encode($broadcaster_string);

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub', 'manage broadcasts channel']);

    // run
    $response = test()
        ->actingAs($user)
        ->put(route('channels.store', $encoded_broadcaster), [
            'checkedChannels' => [
                [
                    'id' => new class
                    {
                    },
                    'name' => 'channel1',
                ],
                [
                    'id' => 1,
                    'name' => 'channel2',
                ],
            ],
        ]);

    // expect response to be 302
    $response->assertStatus(302);

    $response->assertInvalid([
        'checkedChannels.0.id' => 'checkedChannels.0.id is not a valid id. It must be integer or string.',
    ]);

});

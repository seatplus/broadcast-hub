<?php

it('gets available recipients', function () {

    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    assignPermissionToUser($user, ['view broadcasts hub']);

    $this->mock(\Seatplus\BroadcastHub\Http\Actions\GetRecipientsAction::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(collect([
            [
                'id' => 1,
                'name' => 'channel1',
            ],
            [
                'id' => 2,
                'name' => 'channel2',
            ],
        ]));

    $response = test()->actingAs($user)->get(route('recipients.show', 'eveonline'));

    $response->assertOk();
});

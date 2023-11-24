<?php

use Illuminate\Support\Facades\Event;
use Seatplus\Auth\Models\User;

beforeEach(function () {
    $this->user = Event::fakeFor(fn () => User::factory()->create());

    // add permission to user
    assignPermissionToUser($this->user, 'view broadcasts hub');
});

it('returns index view', function () {

    test()->actingAs($this->user)
        ->get(route('broadcasts.index'))
        ->assertStatus(200)
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('BroadcastHub/BroadcastHubIndex')
            ->has('broadcasters')
        );
});

it('returns show view', function () {

    $this->mock(\Seatplus\BroadcastHub\Http\Actions\GetBroadcasterDetailsAction::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn([]);

    test()->actingAs($this->user)
        ->get(route('broadcasts.show', ['broadcaster' => 'test']))
        ->assertStatus(200)
        ->assertJson([
            'can_manage' => false,
        ]);

});

it('stores settings', function ($enabled) {

    assignPermissionToUser($this->user, ['view broadcasts hub', 'can enable broadcasts channel']);

    $this->mock(\Seatplus\BroadcastHub\Http\Actions\StoreBroadcastSettingsAction::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn([
            'enabled' => $enabled,
        ]);

    test()->actingAs($this->user)
        ->post(route('broadcasts.store'), [
            'id' => 'test',
            'enabled' => false,
        ])
        ->assertStatus(302);

})->with([
    'with broadcaster enabled' => [true],
    'with broadcaster disabled' => [false],
]);

it('disables broadcaster', function () {

    assignPermissionToUser($this->user, ['view broadcasts hub', 'can enable broadcasts channel']);

    $this->mock(\Seatplus\BroadcastHub\Http\Actions\DisableBroadcasterAction::class)
        ->shouldReceive('execute')
        ->once();

    test()->actingAs($this->user)
        ->delete(route('broadcasts.destroy', ['broadcaster' => 'test']))
        ->assertStatus(302);

});

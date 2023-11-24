<?php

describe('test global index', function () {
    beforeEach(function () {
        $this->broadcaster_id = faker()->word;
        $this->recipient_id = faker()->word;

        $this->user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

        $this->broadcaster_mock = mock(\Seatplus\BroadcastHub\Contracts\Broadcaster::class)
            ->shouldReceive('getImplementedNotificationClasses')
            ->andReturn([
                \Seatplus\BroadcastHub\Tests\Stubs\ErrorNotificationStub::class,
            ])
            ->getMock();

        $this->mock(\Seatplus\BroadcastHub\BroadcastRepository::class)
            ->shouldReceive('getBroadcaster')
            ->with($this->broadcaster_id)
            ->andReturn($this->broadcaster_mock);
    });

    it('gets global index', function () {

        assignPermissionToUser($this->user, ['view broadcasts hub', 'manage broadcasts channel']);

        // run
        $response = test()
            ->actingAs($this->user)
            ->get(route('global-subscriptions.index', [$this->broadcaster_id, $this->recipient_id]));

        // expect
        $response->assertOk();
    });

    it('gets global index with empty array if user does not have manage broadcasts channel permission', function () {

        assignPermissionToUser($this->user, ['view broadcasts hub']);

        // run
        $response = test()
            ->actingAs($this->user)
            ->get(route('global-subscriptions.index', [$this->broadcaster_id, $this->recipient_id]));

        // expect
        $response->assertOk();
    });

});

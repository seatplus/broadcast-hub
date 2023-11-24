<?php

it('can disable broadcaster', function () {

    $broadcaster_mock = mock(\Seatplus\BroadcastHub\Contracts\Broadcaster::class, function ($mock) {
        $mock->shouldReceive('getSettings->getValue')
            ->once()
            ->with('broadcaster', [])
            ->andReturn([
                'enabled' => true,
                'foo' => 'bar',
            ]);

        $mock->shouldReceive('storeBroadcasterSettings')
            ->once()
            ->with([
                'enabled' => false,
                'foo' => 'bar',
            ]);
    });

    $repository_mock = mock(\Seatplus\BroadcastHub\BroadcastRepository::class, function ($mock) use ($broadcaster_mock) {
        $mock->shouldReceive('getBroadcaster')
            ->once()
            ->with('d634a9c9-4521-4ee7-8c21-ecb43c4bb0af')
            ->andReturn($broadcaster_mock);
    });

    // run action
    $action = new \Seatplus\BroadcastHub\Http\Actions\DisableBroadcasterAction($repository_mock);
    $action->execute('d634a9c9-4521-4ee7-8c21-ecb43c4bb0af');

});

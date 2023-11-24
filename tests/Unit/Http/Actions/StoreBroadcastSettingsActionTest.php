<?php

use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Http\Requests\StoreBroadcastRequest;

it('can store settings', function () {

    $implementation = mock(\Seatplus\BroadcastHub\Contracts\Broadcaster::class, function ($mock) {
        $mock->shouldReceive('getSettings->getValue')
            ->once()
            ->with('broadcaster', [])
            ->andReturn([
                'enabled' => false,
                'foo' => 'bar',
            ]);

        $mock->shouldReceive('storeBroadcasterSettings')
            ->once()
            ->with([
                'enabled' => true,
                'foo' => 'bar',
            ]);
    });

    $request = mock(StoreBroadcastRequest::class, function ($mock) {
        $mock->shouldReceive('validated')
            ->once()
            ->andReturn([
                'id' => 'd634a9c9-4521-4ee7-8c21-ecb43c4bb0af',
                'enabled' => true,
            ]);
    });

    $repository = mock(BroadcastRepository::class, function ($mock) use ($implementation) {
        $mock->shouldReceive('getBroadcaster')
            ->once()
            ->with('d634a9c9-4521-4ee7-8c21-ecb43c4bb0af')
            ->andReturn($implementation);
    });

    // run action
    $action = new \Seatplus\BroadcastHub\Http\Actions\StoreBroadcastSettingsAction($repository);
    $settings = $action->execute($request);

    // assert settings
    expect($settings)->toHaveCount(2);
    expect($settings['enabled'])->toBeTrue();
    expect($settings['foo'])->toBe('bar');
});

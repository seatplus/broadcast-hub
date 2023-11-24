<?php

use Seatplus\BroadcastHub\BroadcastRepository;

beforeEach(function () {

    $this->broadcastRepository = app(BroadcastRepository::class);

    $this->broadcaster = $this->mock(\Seatplus\BroadcastHub\Contracts\Broadcaster::class, function ($mock) {
        $mock->shouldReceive('getName')
            ->andReturn('testBroadcaster');
        $mock->shouldReceive('getImg')
            ->andReturn('testImg');
    });

});

it('throws exception if broadcaster not found', function () {
    $this->broadcastRepository->getBroadcaster('testBroadcaster');
})->throws(\Exception::class, 'Broadcaster not found');

it('returns all broadcasters', function () {
    $this->broadcastRepository->addBroadcaster($this->broadcaster);

    $results = $this->broadcastRepository->getBroadcasters();

    expect($results)->toHaveCount(1);
});

it('get Broadcaster', function () {
    $this->broadcastRepository->addBroadcaster($this->broadcaster);

    $result = $this->broadcastRepository->getBroadcaster(base64_encode(get_class($this->broadcaster)));

    expect($result)->toBeInstanceOf(\Seatplus\BroadcastHub\Contracts\Broadcaster::class);
});

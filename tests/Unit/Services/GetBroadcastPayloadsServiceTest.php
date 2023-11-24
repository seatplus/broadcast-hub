<?php

it('throws exception if notification is not subclass of Notification', function () {
    $service = new \Seatplus\BroadcastHub\Services\GetBroadcastPayloadsService();

    $service->execute('test', 1, 'test', collect([]));
})->throws(\Exception::class, 'test string is not a subclass of '.\Seatplus\BroadcastHub\Contracts\Notification::class);

it('returns original payload if payload_hash is same', function () {
    $service = new \Seatplus\BroadcastHub\Services\GetBroadcastPayloadsService();

    $payload = collect([
        'test' => 'test',
    ]);

    [$original_payload, $new_payload] = $service->execute(
        \Seatplus\BroadcastHub\Tests\Stubs\ErrorNotificationStub::class,
        1,
        'test',
        $payload
    );

    expect($original_payload)->toBe($new_payload);
    expect($original_payload)->toBe($payload);
});

it('returns original payload and new payload if payload_hash is different', function () {
    $service = new \Seatplus\BroadcastHub\Services\GetBroadcastPayloadsService();

    $payload = collect([
        'test' => 'test',
    ]);

    $payload_hash = hash('sha256', $payload->toJson());

    // create original broadcast
    $test = \Seatplus\BroadcastHub\Broadcast::query()->create([
        'notification' => \Seatplus\BroadcastHub\Notifications\Notification::class,
        'entity_id' => 1,
        'entity_type' => 'test',
        'payload' => $payload,
        'payload_hash' => $payload_hash,
    ]);

    // change payload
    $payload->put('test', 'test2');

    [$original_payload, $new_payload] = $service->execute(
        \Seatplus\BroadcastHub\Notifications\Notification::class,
        1,
        'test',
        $payload
    );

    expect($original_payload)->not()->toBe($payload);
    expect($new_payload)->not()->toBe($original_payload);
});

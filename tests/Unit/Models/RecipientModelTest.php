<?php

use Seatplus\BroadcastHub\Recipient;
use Seatplus\BroadcastHub\Subscription;

beforeEach(fn () => \Illuminate\Support\Facades\Event::fake());

it('can create a subscriber', function () {

    // expect no subscriber to exist
    expect(Recipient::count())->toBe(0);

    $subscriber = Recipient::factory()->create();

    expect($subscriber::count())->toBeOne();
});

it('can create subscriber with many subscriptions', function () {

    $subscriber = Recipient::factory()
        ->has(Subscription::factory()->count(3))
        ->create();

    expect($subscriber->subscriptions->count())->toBe(3);
});

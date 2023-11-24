<?php

use Seatplus\BroadcastHub\Recipient;
use Seatplus\BroadcastHub\Subscription;

it('can create a subscription', function () {

    $subscription = Subscription::factory()->create();

    expect($subscription::count())->toBeOne();
});

it('can create a subscription with a subscriber', function () {

    $subscription = Subscription::factory()->create();

    expect(Recipient::count())->toBeOne();
    expect($subscription->recipient_id)->toBe(Recipient::first()->id);

    //dd(\Seatplus\BroadcastHub\Recipient::first()->id, $subscription->recipient_id);

    expect($subscription->recipient)->toBeInstanceOf(Recipient::class);
});

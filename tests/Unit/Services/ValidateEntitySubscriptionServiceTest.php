<?php

use Seatplus\BroadcastHub\Services\ValidateEntitySubscriptionService;
use Seatplus\BroadcastHub\Subscription;
use Seatplus\BroadcastHub\Tests\Stubs\AbstractNotificationStub;

it('returns only subscriptions for given notification', function () {

    $parent_notification = AbstractNotificationStub::class;

    $child_notification = new class extends AbstractNotificationStub
    {
        public function via(): string
        {
            return 'test';
        }

        public function toBroadcaster(): object
        {
            return new \stdClass();
        }
    };

    $subscription = Subscription::factory()->create([
        'notification' => get_class($child_notification),
    ]);

    Subscription::query()->first();

    $subscription2 = Subscription::factory()->create([
        'notification' => get_class($child_notification),
    ]);

    $subscription3 = Subscription::factory()->create([
        'notification' => 'test2',
    ]);

    // expect 3 subscriptions to be created
    expect(Subscription::count())->toBe(3);

    $validate_entity_subscription_service = new ValidateEntitySubscriptionService();

    $subscriptions = $validate_entity_subscription_service($parent_notification);

    expect($subscriptions)->toHaveCount(2)
        ->and($subscriptions->first()->id)->toBe($subscription->id)
        ->and($subscriptions->last()->id)->toBe($subscription2->id);

});

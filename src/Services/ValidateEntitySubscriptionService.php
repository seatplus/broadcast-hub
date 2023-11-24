<?php

namespace Seatplus\BroadcastHub\Services;

use Illuminate\Support\Collection;
use Seatplus\BroadcastHub\Notifications\Notification;
use Seatplus\BroadcastHub\Subscription;

class ValidateEntitySubscriptionService
{
    /**
     * @throws \Throwable
     */
    public function __invoke(string $notification_string, int $entity_id = null): Collection
    {

        // check if notification_string is subclass of Notification containing getEntityType method
        throw_unless(is_subclass_of($notification_string, Notification::class), new \Exception("${notification_string} string is not a subclass of ".Notification::class));

        $entity_type = $notification_string::getEntityType();

        $subscriptions = Subscription::query()
            ->with(['subscribable', 'recipient'])
            ->where('subscribable_type', $entity_type)
            // when no entity_id is given, return all subscriptions for given notification
            ->when($entity_id, fn ($query) => $query->where('subscribable_id', $entity_id))
            ->get();

        // check if any of the subscriptions has notification that is subclass of $notification
        return $subscriptions->filter(fn ($subscription) => is_subclass_of($subscription->notification, $notification_string));
    }
}

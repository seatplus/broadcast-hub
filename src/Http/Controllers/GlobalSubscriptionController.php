<?php

namespace Seatplus\BroadcastHub\Http\Controllers;

use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Subscription;

class GlobalSubscriptionController
{
    const GLOBAL_NOTIFICATIONS = [
        'Seatplus\BroadcastHub\Notifications\ErrorNotification',
        //'Seatplus\BroadcastHub\Notifications\NewBroadcastNotification',
        //'Seatplus\BroadcastHub\Notifications\NewSubscriptionNotification',
        //'Seatplus\BroadcastHub\Notifications\SubscriptionDeletedNotification',
    ];

    /**
     * @throws \Throwable
     */
    public function index(string $broadcaster_id, string $recipient_id, BroadcastRepository $broadcastRepository)
    {

        // if user does not have manage broadcasts channel permission, return empty array
        if (! auth()->user()->can('manage broadcasts channel')) {
            return [];
        }

        $broadcaster = $broadcastRepository->getBroadcaster($broadcaster_id);

        $notifications = $broadcaster::getImplementedNotificationClasses();

        return collect(self::GLOBAL_NOTIFICATIONS)
            ->map(fn (string $notification) => $this->getNotification($notifications, $notification))
            ->filter()
            ->map(fn (string $notification) => $this->buildNotification($notification, $recipient_id));
    }

    private function getNotification(array $notifications, string $parent_class): ?string
    {
        return collect($notifications)
            // filter out error notifications
            ->first(fn (string $notification) => is_subclass_of($notification, $parent_class));
    }

    private function buildNotification(string $notification, string $recipient_id)
    {
        $subscription_id = Subscription::query()
            ->where('recipient_id', $recipient_id)
            ->where('notification', $notification)
            ->value('id');

        $is_subscribed = (bool) $subscription_id;

        return [
            'title' => $notification::getTitle(),
            'description' => $notification::getDescription(),
            'recipient_id' => $recipient_id,
            'notification' => $notification,
            'entity_id' => 0, // subscribable_id
            'entity_type' => $notification, // subscribable_type
            'is_subscribed' => $is_subscribed,
            'subscription_id' => $subscription_id,
        ];
    }
}

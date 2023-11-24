<?php

namespace Seatplus\BroadcastHub\Http\Controllers;

use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Http\Actions\GetAvailableNotifications;
use Seatplus\BroadcastHub\Http\Requests\StoreSubscriptionRequest;
use Seatplus\BroadcastHub\Notifications\ErrorNotification;
use Seatplus\BroadcastHub\Subscription;

class SubscriptionController
{
    /**
     * @throws \Throwable
     */
    public function index(string $broadcaster_id, BroadcastRepository $broadcastRepository)
    {
        $broadcaster = $broadcastRepository->getBroadcaster($broadcaster_id);

        $notifications = $broadcaster::getImplementedNotificationClasses();

        return collect($notifications)
            // filter out error notifications
            ->filter(fn (string $notification) => ! is_subclass_of($notification, ErrorNotification::class))
            // map notifications to urlencoded class name
            ->map(fn (string $notification) => base64_encode($notification));

    }

    /**
     * @throws \Throwable
     */
    public function show(string $notification_class, string $recipient_id, GetAvailableNotifications $getAvailableNotifications)
    {
        $notification_class = base64_decode($notification_class);

        $result = $getAvailableNotifications->execute($notification_class, $recipient_id);

        return response()->json($result);

    }

    public function store(StoreSubscriptionRequest $request)
    {
        $validated = $request->validated();

        $subscription = Subscription::firstOrCreate([
            'recipient_id' => $validated['recipient_id'],
            'notification' => $validated['notification'],
            'subscribable_id' => $validated['entity_id'],
            'subscribable_type' => $validated['entity_type'],
        ]);

        session()->flash('success', 'Successfully subscribed');

        return response()->json([
            'subscription_id' => $subscription->id,
        ]);
    }

    public function destroy(string $subscription_id)
    {
        $subscription = Subscription::find($subscription_id);

        $subscription->delete();

        session()->flash('success', 'Successfully unsubscribed');

        return response()->json([
            'subscription_id' => $subscription->id,
        ]);
    }
}

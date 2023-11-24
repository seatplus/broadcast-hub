<?php

namespace Seatplus\BroadcastHub\Services;

use Illuminate\Support\Collection;
use Seatplus\BroadcastHub\Broadcast;
use Seatplus\BroadcastHub\Contracts\Notification;

class GetBroadcastPayloadsService
{
    /**
     * @throws \Throwable
     */
    public function execute(
        string $notification,
        int $entity_id,
        string $entity_type,
        Collection $payload
    ): array {

        // check if notification_string is subclass of Notification
        throw_unless(is_subclass_of($notification, Notification::class), new \Exception("${notification} string is not a subclass of ".Notification::class));

        $payload_hash = hash('sha256', $payload->toJson());

        $broadcast = Broadcast::query()
            ->firstOrCreate([
                'notification' => $notification,
                'entity_id' => $entity_id,
                'entity_type' => $entity_type,
            ], [
                'payload' => $payload,
                'payload_hash' => $payload_hash,
            ]);

        // if payload_hash is same, return
        if ($broadcast->payload_hash === $payload_hash) {
            return [$payload, $payload];
        }

        $original_payload = $broadcast->payload;

        // if payload_hash is different, update payload_hash and payload
        $broadcast->update([
            'payload' => $payload,
            'payload_hash' => $payload_hash,
        ]);

        // return original broadcast and new broadcast
        return [$original_payload, $broadcast->payload];

    }
}

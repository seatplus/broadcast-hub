<?php

namespace Seatplus\BroadcastHub\Http\Actions;

use Seatplus\BroadcastHub\Recipient;

// This action is called from the ChannelController@store method
// This action creates, removes and updates recipients for a broadcaster
// The recipients without a name are individual users, the ones with a name are channels
class SynchronizeChannelsAction
{
    // This action is called from the ChannelController@store method
    // The request is validated in the ChannelController
    public function execute(string $encoded_broadcaster, array $checked_channels): void
    {
        $broadcaster = base64_decode($encoded_broadcaster);

        // get all recipients without a name, as they belong to individual users
        $user_recipients = Recipient::query()
            ->where('connector_type', $broadcaster)
            ->whereNull('name')
            ->get(['connector_id', 'connector_type', 'name'])
            ->toArray();

        // build recipients array from request
        $recipients = collect($checked_channels)
            ->map(fn ($channel) => [
                'connector_id' => $channel['id'],
                'connector_type' => $broadcaster,
                'name' => $channel['name'],
            ])
            ->merge($user_recipients)
            ->toArray();

        // upsert the merged recipients
        Recipient::upsert(
            $recipients,
            ['connector_id', 'connector_type'],
            ['name']
        );

        // delete all recipients that are not in the request
        Recipient::query()
            ->where('connector_type', $broadcaster)
            ->whereNotIn('connector_id', collect($recipients)->pluck('connector_id'))
            ->delete();
    }
}

<?php

namespace Seatplus\BroadcastHub\Http\Actions;

use Seatplus\BroadcastHub\Recipient;
use Seatplus\Connector\Models\User;

class GetRecipientsAction
{
    private \Seatplus\Auth\Models\User $user;

    private bool $can_manage;

    private array $response;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->can_manage = $this->user->can('manage broadcasts channel');
        $this->response = [
            'user_recipient' => null,
            'recipients' => [],
        ];
    }

    public function execute(string $broadcast)
    {
        $connector_type = base64_decode($broadcast);

        // get connector user
        $connector_user = User::query()
            ->where('user_id', $this->user->getAuthIdentifier())
            ->where('connector_type', $connector_type)
            ->first();

        $user_recipient = Recipient::firstOrCreate([
            'connector_id' => $connector_user->connector_id,
            'connector_type' => $connector_type,
        ]);

        // add name of main character to user recipient
        $user_recipient->name = $this->user->main_character->name; // @phpstan-ignore-line

        $this->response['user_recipient'] = $user_recipient;

        // if user is not allowed to manage broadcast channels, return only user recipient
        if (! $this->can_manage) {
            return $this->response;
        }

        // get all recipients for the given connector type
        $recipients = Recipient::query()
            ->where('connector_type', $connector_type)
            ->whereNotNull('name')
            ->get();

        // add recipients to response
        $this->response['recipients'] = $recipients;

        // return response
        return $this->response;
    }
}

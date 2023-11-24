<?php

namespace Seatplus\BroadcastHub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Seatplus\BroadcastHub\Contracts\Notification;
use Seatplus\BroadcastHub\Notifications\GlobalNotification;

class StoreSubscriptionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'entity_id' => ['required', 'integer'],
            'entity_type' => ['required', 'string'],
            'notification' => ['required', function ($attribute, $value, $fail) {

                // fail if value is neither subclass of Notification nor subclass of ErrorNotification
                if (! is_subclass_of($value, Notification::class) && ! is_subclass_of($value, GlobalNotification::class)) {
                    $fail($value.' is not a subclass of '.Notification::class.' or '.GlobalNotification::class);
                }
            }],
            'recipient_id' => ['required', 'string'],
        ];
    }
}

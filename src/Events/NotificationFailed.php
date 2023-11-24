<?php

namespace Seatplus\BroadcastHub\Events;

use Seatplus\BroadcastHub\Contracts\Notification;
use Seatplus\BroadcastHub\Recipient;

class NotificationFailed
{
    public function __construct(
        public Recipient $recipient,
        public Notification $message,
        public \Throwable $exception
    ) {
    }
}

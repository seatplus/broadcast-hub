<?php

namespace Seatplus\BroadcastHub\Contracts;

use Seatplus\BroadcastHub\Recipient;

interface Channel
{
    public function send(Recipient $recipient, Notification $notification): void;
}

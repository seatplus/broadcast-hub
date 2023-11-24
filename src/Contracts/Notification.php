<?php

namespace Seatplus\BroadcastHub\Contracts;

interface Notification
{
    public function via(): string;

    public function toBroadcaster(): object;
}

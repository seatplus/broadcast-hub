<?php

namespace Seatplus\BroadcastHub\Contracts;

interface Notification
{
    public function via(): array;

    public function toBroadcaster(): object;
}

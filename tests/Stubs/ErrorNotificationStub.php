<?php

namespace Seatplus\BroadcastHub\Tests\Stubs;

use Seatplus\BroadcastHub\Notifications\ErrorNotification;

class ErrorNotificationStub extends ErrorNotification
{
    public function via(): string
    {
        return 'test';
    }

    public function toBroadcaster(): object
    {
        return new \stdClass();
    }
}

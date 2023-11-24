<?php

namespace Seatplus\BroadcastHub\Tests\Stubs;

use Seatplus\BroadcastHub\Notifications\Notification;
use Seatplus\Eveapi\Models\Character\CharacterInfo;

abstract class AbstractNotificationStub extends Notification
{
    public static function getEntityType(): string
    {
        return CharacterInfo::class;
    }
}

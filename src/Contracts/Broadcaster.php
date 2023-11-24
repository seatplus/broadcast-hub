<?php

namespace Seatplus\BroadcastHub\Contracts;

use Seatplus\BroadcastHub\Recipient;
use Seatplus\Connector\Contracts\Connector;

interface Broadcaster extends Connector
{
    public static function getNotifiableId(Recipient $subscriber): ?string;

    public static function getImplementedNotificationClasses(): array;

    public static function findImplementedNotificationClass(string $notification_class): string;

    public static function isBroadcasterEnabled(): bool;

    public static function getEnablePermission(): string;

    public static function userCanEnableBroadcaster(): bool;

    public static function storeBroadcasterSettings(array $settings): void;

    public static function getChannels(): array;
}

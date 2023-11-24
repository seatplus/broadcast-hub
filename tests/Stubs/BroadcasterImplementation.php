<?php

namespace Seatplus\BroadcastHub\Tests\Stubs;

use Illuminate\Support\Collection;
use Seatplus\BroadcastHub\Recipient;
use Seatplus\Connector\Models\Settings;
use Seatplus\Connector\Models\User;

class BroadcasterImplementation implements \Seatplus\BroadcastHub\Contracts\Broadcaster
{
    public static function getNotifiableId(Recipient $subscriber): ?string
    {
        return null;
    }

    public static function getImplementedNotificationClasses(): array
    {
        return [];
    }

    public static function findImplementedNotificationClass(string $notification_class): string
    {
        return '';
    }

    public static function isBroadcasterEnabled(): bool
    {
        return true;
    }

    public static function getEnablePermission(): string
    {
        return 'test';
    }

    public static function userCanEnableBroadcaster(): bool
    {
        return true;
    }

    public static function storeBroadcasterSettings(array $settings): void
    {

    }

    public static function getChannels(): array
    {
        return [];
    }

    public static function getImg(): string
    {
        return '';
    }

    public static function getName(): string
    {
        return '';
    }

    public static function isConnectorConfigured(): bool
    {
        return true;
    }

    public static function isConnectorSetup(): bool
    {
        return true;
    }

    public static function getConnectorConfigUrl(): string
    {
        return '';
    }

    public static function getRegistrationUrl(): string
    {
        return '';
    }

    public static function users(): Collection
    {
        return collect();
    }

    public static function findUser(int $user_id): ?User
    {
        return null;
    }

    public static function getSettings(): Settings
    {
        return new Settings();
    }
}

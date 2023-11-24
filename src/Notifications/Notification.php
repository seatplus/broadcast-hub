<?php

namespace Seatplus\BroadcastHub\Notifications;

use Illuminate\Notifications\Notification as LaravelNotification;
use Seatplus\BroadcastHub\Contracts\Notification as NotificationContract;

abstract class Notification extends LaravelNotification implements NotificationContract
{
    protected static ?string $title = null;

    protected static ?string $description = null;

    protected static array $permissions = [];

    protected static ?array $corporation_roles = null;

    protected static string $model;

    protected static string $identifier;

    protected static string $entity_type;

    public static function getTitle(): string
    {
        return static::$title ?? class_basename(static::class);
    }

    public static function getDescription(): string
    {
        return static::$description ?? '';
    }

    public static function getPermissions(): array
    {
        return static::$permissions;
    }

    public static function getCorporationRoles(): ?array
    {
        return static::$corporation_roles;
    }

    public static function getModel(): string
    {
        return static::$model;
    }

    public static function getIdentifier(): string
    {
        return static::$identifier;
    }

    public static function getEntityType(): string
    {
        return static::$entity_type;
    }
}

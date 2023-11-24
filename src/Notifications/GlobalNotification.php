<?php

namespace Seatplus\BroadcastHub\Notifications;

use Illuminate\Notifications\Notification as LaravelNotification;
use Seatplus\BroadcastHub\Contracts\Notification as NotificationContract;

abstract class GlobalNotification extends LaravelNotification implements NotificationContract
{
    protected static ?string $title = null;

    protected static ?string $description = null;

    public static function getTitle(): string
    {
        return static::$title ?? class_basename(static::class);
    }

    public static function getDescription(): string
    {
        return static::$description ?? '';
    }

    abstract public function via(): string;

    abstract public function toBroadcaster(): object;
}

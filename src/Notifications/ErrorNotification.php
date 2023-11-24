<?php

namespace Seatplus\BroadcastHub\Notifications;

use Seatplus\BroadcastHub\Contracts\Notification as NotificationContract;
use Seatplus\BroadcastHub\Recipient;

abstract class ErrorNotification extends GlobalNotification
{
    protected $recipient_name;

    protected int $code;

    protected string $error_message;

    protected string $notification_class;

    protected static ?string $description = 'This notification is used to notify the user what error had occurred.';

    public function __construct(
        protected Recipient $recipient,
        protected NotificationContract $message,
        protected \Throwable $exception
    ) {
        $this->recipient_name = $recipient->name ??= $recipient->user->main_character->name; // @phpstan-ignore-line
        $this->notification_class = $message::class;
        $this->code = $exception->getCode();
        $this->error_message = $exception->getMessage();

    }
}

<?php

namespace Seatplus\BroadcastHub\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Seatplus\BroadcastHub\Events\NotificationFailed;
use Seatplus\BroadcastHub\Notifications\ErrorNotification;
use Seatplus\BroadcastHub\Recipient;

class NotificationFailedListener
{
    public function handle(NotificationFailed $notificationFailed)
    {

        // get connector type from recipient
        $connector_type = $notificationFailed->recipient->connector_type;

        //dd($connector_type, $connector_type::class);

        // find the notification class that is implemented by the connector
        try {
            $error_notification = $connector_type::findImplementedNotificationClass(ErrorNotification::class);
        } catch (\Exception $e) {

            Log::warning('Could not find implemented notification class for connector type '.$connector_type.' with error '.$e->getMessage());

            return;
        }

        // construct error notification
        $error_notification = new $error_notification($notificationFailed->recipient, $notificationFailed->message, $notificationFailed->exception);

        // get enable permission from connector
        $enable_permission = $connector_type::getEnablePermission();

        $admin_permissions = ['superuser', $enable_permission];

        // get user with enable permission from recipient
        $admin_recipients = Recipient::query()
            ->where('connector_type', $connector_type)
            ->whereHas('user.roles.permissions', fn ($query) => $query->whereIn('name', $admin_permissions))
            ->orWhereHas('user.permissions', fn ($query) => $query->whereIn('name', $admin_permissions))
            ->get();

        // send error notification to user with enable permission
        Notification::send($admin_recipients, $error_notification);
    }
}

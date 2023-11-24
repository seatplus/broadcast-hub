<?php

use Illuminate\Support\Facades\Notification;
use Seatplus\BroadcastHub\Notifications\ErrorNotification;
use Seatplus\BroadcastHub\Recipient;
use Seatplus\BroadcastHub\Tests\Stubs\BroadcasterImplementation;
use Seatplus\BroadcastHub\Tests\Stubs\ErrorNotificationStub;

describe('does not send error notification if implemented notification class is not found', function () {

    beforeEach(function () {
        Notification::fake();

        $notification = $this->mock(ErrorNotification::class);

        $connector = new class
        {
            public static function findImplementedNotificationClass($class)
            {
                throw new \Exception('test');
            }
        };

        $this->notification_failed = new \Seatplus\BroadcastHub\Events\NotificationFailed(
            recipient: Recipient::factory()->make([
                'connector_type' => $connector::class,
            ]),
            message: $notification,
            exception: new \Exception('test'),
        );
    });

    it('via Listener class', function () {

        $notification_failed_listener = new \Seatplus\BroadcastHub\Listeners\NotificationFailedListener();

        $notification_failed_listener->handle($this->notification_failed);

        Notification::assertNothingSent();
    });

    it('via Event class', function () {
        event($this->notification_failed);

        Notification::assertNothingSent();
    });

});

it('send error notification to user with enable permission', function () {

    Notification::fake();

    $connector_mock = mock(BroadcasterImplementation::class, function ($mock) {

        $mock->shouldReceive('findImplementedNotificationClass')
            ->andReturn(ErrorNotificationStub::class);
        $mock->shouldReceive('getEnablePermission')
            ->andReturn('test');
    });

    $recipient = Recipient::factory()->make([
        'connector_type' => $connector_mock::class,
    ]);

    $failed_notification = new ErrorNotificationStub(
        recipient: $recipient,
        message: mock(\Seatplus\BroadcastHub\Notifications\Notification::class),
        exception: new \Exception('test'),
    );

    $notification_failed_event = new \Seatplus\BroadcastHub\Events\NotificationFailed(
        recipient: $recipient,
        message: $failed_notification,
        exception: new \Exception('test'),
    );

    // create admin user
    $admin_user = \Seatplus\Auth\Models\User::factory()->create();
    // give admin user superuser permission
    assignPermissionToUser($admin_user, 'superuser');

    $notification_failed_listener = new \Seatplus\BroadcastHub\Listeners\NotificationFailedListener();

    $notification_failed_listener->handle($notification_failed_event);

    // assert that notification was sent
    Notification::assertCount(0);

});

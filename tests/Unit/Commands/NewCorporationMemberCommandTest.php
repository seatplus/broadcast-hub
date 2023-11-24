<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Seatplus\BroadcastHub\Commands\CorporationTracking\NewCorporationMemberCommand;
use Seatplus\BroadcastHub\Services\GetBroadcastPayloadsService;
use Seatplus\BroadcastHub\Services\ValidateEntitySubscriptionService;
use Seatplus\Eveapi\Models\Corporation\CorporationMemberTracking;

describe('zero subscriptions', function () {

    beforeEach(function () {
        // Mock the dependencies and define their behavior
        $this->mock(GetBroadcastPayloadsService::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->never();
        });

        $this->mock(ValidateEntitySubscriptionService::class, function (MockInterface $mock) {
            $mock->shouldReceive('__invoke')
                ->once()
                ->andReturn(new Collection());
        });

    });

    it('tests NewCorporationMemberCommand without arguments', function () {

        // Asserts that the command is executed without errors
        $this->artisan(NewCorporationMemberCommand::class)->assertExitCode(0);
    });

    it('throws Exception for if called with arguments', function () {

        // Expect Exception ' No active subscriptions found for given corporation_id' to be thrown
        $this->expectExceptionMessage('No active subscriptions found for given corporation_id');

        $this->artisan(NewCorporationMemberCommand::class, ['corporation_id' => 1])->assertExitCode(0);

    });

});

describe('with subscription', function () {
    beforeEach(function () {

        Notification::fake();
        Event::fake();

        // Mock the dependencies and define their behavior
        $this->mock(ValidateEntitySubscriptionService::class, function (MockInterface $mock) {

            // create CorporationMemberTracking
            $corporation_member_tracking = CorporationMemberTracking::factory()->create([
                'corporation_id' => \Seatplus\Eveapi\Models\Corporation\CorporationInfo::factory(),
            ]);

            // create recipient
            $this->user = \Seatplus\Auth\Models\User::factory()->create();

            // create anonymous class with attributes
            $notification_class = new class
            {
                public int $id = 1;

                public function __construct(...$args)
                {
                }

                public function via($notifiable)
                {
                    return ['broadcast'];
                }
            };

            $mock->shouldReceive('__invoke')
                ->once()
                ->andReturn(new Collection([
                    (object) [
                        'subscribable_id' => $corporation_member_tracking->corporation_id,
                        'subscribable' => (object) [
                            'name' => 'test',
                        ],
                        'recipient' => $this->user,
                        'notification_class' => $notification_class,
                    ],
                ]));
        });

    });

    it('has does not send notification if payload is the same', function () {
        $this->mock(GetBroadcastPayloadsService::class, function (MockInterface $mock) {

            $payload = collect([
                'test' => 'test',
            ]);

            $mock->shouldReceive('execute')
                ->once()
                ->andReturn([
                    $payload,
                    $payload,
                ]);
        });

        $this->artisan(NewCorporationMemberCommand::class, ['corporation_id' => 1])->assertExitCode(0);

        // assert that no notification was sent
        Notification::assertNothingSent();

        // assert that no event was fired
        Event::assertNotDispatched(\Seatplus\BroadcastHub\Events\NotificationFailed::class);
    });

    it('sends notification if payload is different', function () {
        $this->mock(GetBroadcastPayloadsService::class, function (MockInterface $mock) {

            $payload = collect([
                'test' => 'test',
            ]);

            $mock->shouldReceive('execute')
                ->once()
                ->andReturn([
                    collect(['foo', 'bar']),
                    collect(['foo2', 'bar2']),
                ]);
        });

        $this->artisan(NewCorporationMemberCommand::class)->assertExitCode(0);

        // assert that no notification was sent
        Notification::assertCount(1);

        // assert that no event was fired
        Event::assertNotDispatched(\Seatplus\BroadcastHub\Events\NotificationFailed::class);
    });

});

describe('exceptions', function () {
    beforeEach(function () {

        Notification::fake();
        Event::fake();

        // Mock the dependencies and define their behavior
        $this->mock(ValidateEntitySubscriptionService::class, function (MockInterface $mock) {

            // create CorporationMemberTracking
            $corporation_member_tracking = CorporationMemberTracking::factory()->create([
                'corporation_id' => \Seatplus\Eveapi\Models\Corporation\CorporationInfo::factory(),
            ]);

            // create recipient
            $recipient = \Seatplus\BroadcastHub\Recipient::factory()->create();

            $notification_class = $this->mock(\Seatplus\BroadcastHub\Contracts\Notification::class, function (MockInterface $mock) {
                $mock->shouldReceive('via')
                    ->andThrow(new \Exception('test'));
            });

            $mock->shouldReceive('__invoke')
                ->once()
                ->andReturn(new Collection([
                    (object) [
                        'subscribable_id' => $corporation_member_tracking->corporation_id,
                        'subscribable' => [
                            'name' => 'test',
                        ],
                        'recipient' => $recipient,
                        'notification_class' => $notification_class,
                    ],
                ]));
        });

    });

    it('throws exception without Error Notification nor Event if Notification cannot be constructed', function () {
        $this->mock(GetBroadcastPayloadsService::class, function (MockInterface $mock) {

            $payload = collect([
                'test' => 'test',
            ]);

            // if payload is not collection or array, an exception is thrown
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn([
                    $payload,
                    'string',
                ]);
        });

        // assert that error was thrown
        $this->expectExceptionMessage('must be of type Illuminate\Support\Collection, string given, called in');

        $this->artisan(NewCorporationMemberCommand::class)->assertExitCode(0);

        // assert that no notification was sent
        Notification::assertNothingSent();

        // assert that no event was fired
        Event::assertNotDispatched(\Seatplus\BroadcastHub\Events\NotificationFailed::class);

    });

    it('sends NotificationFailed event if notification fails', function () {
        $this->mock(GetBroadcastPayloadsService::class, function (MockInterface $mock) {

            $payload = collect([
                'test' => 'test',
            ]);

            $mock->shouldReceive('execute')
                ->once()
                ->andReturn([
                    collect(['foo', 'bar']),
                    collect(['foo2', 'bar2']),
                ]);
        });

        $this->artisan(NewCorporationMemberCommand::class)->assertExitCode(0);

        // assert that no event was fired
        Event::assertDispatched(\Seatplus\BroadcastHub\Events\NotificationFailed::class);
    });

});

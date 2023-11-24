<?php

namespace Seatplus\BroadcastHub\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Seatplus\BroadcastHub\Events\NotificationFailed;
use Seatplus\BroadcastHub\Services\GetBroadcastPayloadsService;
use Seatplus\BroadcastHub\Services\ValidateEntitySubscriptionService;
use Throwable;

abstract class NotificationCommand extends Command
{
    protected string $argument_key = '';

    protected string $notification_class = '';

    protected string $entity_type = '';

    protected Collection $subscriptions;

    public function __construct(
        protected ?GetBroadcastPayloadsService $payload_service = null,
        protected ?ValidateEntitySubscriptionService $validate_entity_subscription_service = null
    ) {

        parent::__construct();

        // if no payload service is given, use default
        $this->payload_service ??= new GetBroadcastPayloadsService;

        // if no validate entity subscription service is given, use default
        $this->validate_entity_subscription_service ??= new ValidateEntitySubscriptionService();

    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            $this->prepareSubscriptions();

            // if an argument is given, we only want to notify for that argument
            if ($this->getArgument()) {
                $this->handleSingleEntityInvocation();
            }

            $this->handleSubscriptions();

        } catch (Throwable $e) {

            // if run in console, show error
            if (app()->runningInConsole()) {
                $this->error($e->getMessage());
            }

            throw $e;
        }

    }

    /**
     * @throws Throwable
     */
    private function prepareSubscriptions(): void
    {

        $this->subscriptions = ($this->validate_entity_subscription_service)(
            $this->notification_class,
            $this->getArgument()
        );
    }

    private function getArgument(): ?string
    {
        return $this->argument($this->argument_key);
    }

    /**
     * @throws Throwable
     */
    private function handleSingleEntityInvocation(): void
    {
        // if no subscriptions, throw exception
        throw_if($this->subscriptions->isEmpty(), new Exception("No active subscriptions found for given {$this->argument_key}"));

        // get name of entity
        $entity_name = $this->subscriptions->first()->subscribable->name;

        // use description to notify user
        $this->info("{$this->description} ({$entity_name})");
    }

    /**
     * @throws Throwable
     */
    private function handleSubscriptions(): void
    {

        $subscribable_ids = $this->subscriptions->pluck('subscribable_id')->unique();

        $grouped_models = $this->getGroupedModels($subscribable_ids);

        $grouped_models->each(function ($models, $entity_id) {
            [$original_payload, $new_payload] = $this->getPayloads($models, $entity_id);

            // if payload is same, return
            if ($original_payload->diff($new_payload)->isEmpty()) {
                return;
            }

            // build notification constructor arguments
            $notification_arguments = $this->buildNotificationArguments($entity_id, $original_payload, $new_payload);

            // get the subscriptions for the given key
            $subscriptions = $this->subscriptions->where('subscribable_id', $entity_id);

            // iterate over the subscriptions
            $subscriptions->each(function ($subscription) use ($notification_arguments) {
                // get the recipient
                $recipient = $subscription->recipient;

                // get the notification
                $notification = new $subscription->notification_class(...$notification_arguments);

                // send the notification
                try {
                    Notification::send($recipient, $notification);
                } catch (Exception $e) {
                    report($e);

                    event(new NotificationFailed($recipient, $notification, $e));
                }

            });
        });
    }

    abstract protected function getGroupedModels(Collection $subscribable_ids): Collection;

    /**
     * @throws Throwable
     */
    private function getPayloads($models, $key): array
    {

        return $this->payload_service->execute(
            $this->notification_class,
            $key,
            $this->entity_type,
            $models
        );
    }

    abstract protected function buildNotificationArguments(string $entity_id, Collection $original_payload, Collection $new_payload): array;
}

<?php

namespace Seatplus\BroadcastHub\Http\Actions;

use Seatplus\Auth\Services\Affiliations\GetAffiliatedIdsService;
use Seatplus\Auth\Services\Affiliations\GetOwnedAffiliatedIdsService;
use Seatplus\Auth\Services\Dtos\AffiliationsDto;
use Seatplus\BroadcastHub\Notifications\Notification;
use Seatplus\BroadcastHub\Subscription;

class GetAvailableNotifications
{
    private string $title;

    private string $description;

    private array $permissions;

    private ?array $corporation_roles;

    private string $model;

    private string $identifier;

    private string $entity_type;

    private AffiliationsDto $affiliations_dto;

    private \Illuminate\Database\Eloquent\Collection $subscriptions;

    private array $owned_ids;

    private \Illuminate\Support\Collection $affiliated_ids;

    private string $notification;

    private string $recipient_id;

    /**
     * @throws \Throwable
     */
    public function execute(string $notification, string $recipient_id): array
    {
        $this->prepareAction($notification, $recipient_id);

        return $this->runAction();
    }

    private function getAffiliatedIds()
    {
        $base_query = $this->model::query()
            ->select($this->identifier)
            // select unique identifier
            ->distinct();

        if (auth()->user()->can('superuser')) {
            return $base_query->pluck($this->identifier);
        }

        $affiliated_ids = GetAffiliatedIdsService::make($this->affiliations_dto)
            ->getQuery()
            ->pluck('affiliated_id');

        // merge owned ids with affiliated ids
        $affiliated_ids = $affiliated_ids->merge($this->owned_ids);

        return $base_query->whereIn($this->identifier, $affiliated_ids)->pluck($this->identifier);
    }

    private function getOwnedIds(): array
    {
        return GetOwnedAffiliatedIdsService::make($this->affiliations_dto)
            ->getQuery()
            ->pluck('affiliated_id')
            ->toArray();
    }

    private function getSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::query()
            ->where('subscribable_type', $this->entity_type)
            ->where('notification', $this->notification)
            ->where('recipient_id', $this->recipient_id)
            ->select(['id', 'subscribable_id'])
            ->get();
    }

    /**
     * @throws \Throwable
     */
    private function prepareAction(string $notification, string $recipient_id): void
    {
        $this->notification = $notification;
        $this->recipient_id = $recipient_id;

        // throw exception if not instance of Notification
        throw_unless(is_subclass_of($notification, Notification::class), new \Exception('Notification string is not a subclass of '.Notification::class));

        $this->title = $notification::getTitle();
        $this->description = $notification::getDescription();
        $this->permissions = $notification::getPermissions();
        $this->corporation_roles = $notification::getCorporationRoles();
        $this->model = $notification::getModel();
        $this->identifier = $notification::getIdentifier();
        $this->entity_type = $notification::getEntityType();

        $this->affiliations_dto = new AffiliationsDto(
            permissions: $this->permissions,
            user: auth()->user(),
            corporation_roles: $this->corporation_roles,
        );

        // get subscriptions for the given notification
        $this->subscriptions = $this->getSubscriptions();

        // get owned ids
        $this->owned_ids = $this->getOwnedIds();

        // get affiliated ids, this needs to be after owned ids
        $this->affiliated_ids = $this->getAffiliatedIds();
    }

    private function runAction(): array
    {
        return $this->affiliated_ids->map(function ($entity_id) {

            $subscription = $this->subscriptions->firstWhere('subscribable_id', $entity_id);
            $is_subscribed = (bool) $subscription;

            return [
                'title' => $this->title,
                'description' => $this->description,
                'notification' => $this->notification,
                'entity_id' => $entity_id,
                'entity_type' => $this->entity_type,
                $this->identifier => $entity_id,
                'owned' => in_array($entity_id, $this->owned_ids),
                'subscribed' => $is_subscribed,
                'subscription_id' => $is_subscribed ? $subscription->id : null, // @phpstan-ignore-line
            ];
        })
            // sort by owned first
            ->sortByDesc(fn ($item) => $item['owned'])
            ->values()
            ->toArray();
    }
}

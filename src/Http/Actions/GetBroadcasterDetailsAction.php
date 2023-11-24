<?php

namespace Seatplus\BroadcastHub\Http\Actions;

use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Contracts\Broadcaster;
use Seatplus\Connector\Http\Actions\AddConnectorDetailsAction;

class GetBroadcasterDetailsAction
{
    public function __construct(
        private BroadcastRepository $broadcastRepository,
        private AddConnectorDetailsAction $addConnectorDetailsAction
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function execute(string $broadcaster)
    {
        $implementation = $this->broadcastRepository->getBroadcaster($broadcaster);

        $is_disabled = ! $implementation->getSettings()->getValue('broadcaster.enabled', false);

        $base_details = $this->addConnectorDetailsAction
            ->setAdminPermission('superuser')
            ->setIsDisabled($is_disabled)
            ->execute($implementation);

        // if broadcaster status is Registered add base details with can_manage
        if ($base_details['status'] === 'Registered') {
            $base_details['can_manage'] = auth()->user()->can('manage broadcasts channel');
        }

        return $base_details;
    }
}

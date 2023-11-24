<?php

namespace Seatplus\BroadcastHub\Http\Controllers;

use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Http\Actions\SynchronizeChannelsAction;
use Seatplus\BroadcastHub\Http\Requests\StoreChannelsRequest;

class ChannelController
{
    public function index(string $broadcaster, BroadcastRepository $repository): array
    {
        $broadcaster = $repository->getBroadcaster($broadcaster);

        return $broadcaster::getChannels();
    }

    public function store(string $broadcaster, StoreChannelsRequest $storeChannelsRequest, SynchronizeChannelsAction $syncChannelsAction): void
    {
        $validated = $storeChannelsRequest->validated();

        $syncChannelsAction->execute($broadcaster, $validated['checkedChannels']);

    }
}

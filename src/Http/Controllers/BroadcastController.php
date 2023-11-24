<?php

namespace Seatplus\BroadcastHub\Http\Controllers;

use Inertia\Inertia;
use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Http\Actions\DisableBroadcasterAction;
use Seatplus\BroadcastHub\Http\Actions\GetBroadcasterDetailsAction;
use Seatplus\BroadcastHub\Http\Actions\StoreBroadcastSettingsAction;
use Seatplus\BroadcastHub\Http\Requests\StoreBroadcastRequest;

class BroadcastController
{
    public function index(BroadcastRepository $broadcastRepository)
    {
        return inertia('BroadcastHub/BroadcastHubIndex', [
            'broadcasters' => $broadcastRepository->getBroadcasters(),
        ]);
    }

    public function show(string $broadcaster, GetBroadcasterDetailsAction $getBroadcasterDetailsAction)
    {
        return [
            ...$getBroadcasterDetailsAction->execute($broadcaster),
            'can_manage' => auth()->user()->can('manage broadcasts channel'),
        ];
    }

    /**
     * @throws \Throwable
     */
    public function store(StoreBroadcastSettingsAction $storeBroadcastSettingsAction, StoreBroadcastRequest $request)
    {

        $settings = $storeBroadcastSettingsAction->execute($request);

        $enabled = array_key_exists('enabled', $settings) && $settings['enabled'];

        return $enabled ? Inertia::location(route('broadcasts.index')) : to_route('broadcasts.index');
    }

    public function destroy(string $broadcaster, DisableBroadcasterAction $disableBroadcasterAction)
    {

        $disableBroadcasterAction->execute($broadcaster);

        // return to index
        return Inertia::location(route('broadcasts.index'));
    }
}

<?php

namespace Seatplus\BroadcastHub\Http\Actions;

use Seatplus\BroadcastHub\BroadcastRepository;
use Seatplus\BroadcastHub\Http\Requests\StoreBroadcastRequest;

class StoreBroadcastSettingsAction
{
    public function __construct(
        protected BroadcastRepository $repository
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function execute(StoreBroadcastRequest $request): array
    {

        // get all data from the request
        $validated = $request->validated();

        $broadcaster = $this->repository->getBroadcaster($validated['id']);

        // get current settings
        $current_settings = $broadcaster->getSettings()->getValue('broadcaster', []);

        // remove id from validated array
        unset($validated['id']);

        $settings = array_merge($current_settings, $validated);

        // save merged settings
        $broadcaster->storeBroadcasterSettings($settings);

        return $settings;

    }
}

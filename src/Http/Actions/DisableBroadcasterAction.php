<?php

namespace Seatplus\BroadcastHub\Http\Actions;

use Seatplus\BroadcastHub\BroadcastRepository;

class DisableBroadcasterAction
{
    public function __construct(
        protected BroadcastRepository $repository
    ) {
    }

    public function execute(string $id): void
    {
        $broadcaster = $this->repository->getBroadcaster($id);

        // get current settings
        $settings = $broadcaster->getSettings()->getValue('broadcaster', []);

        // set enabled to false
        $settings['enabled'] = false;

        // save settings
        $broadcaster->storeBroadcasterSettings($settings);
    }
}

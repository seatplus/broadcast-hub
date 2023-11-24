<?php

namespace Seatplus\BroadcastHub;

use Seatplus\BroadcastHub\Contracts\Broadcaster;

class BroadcastRepository
{
    private array $broadcasters = [];

    public function addBroadcaster(Broadcaster $broadcaster)
    {

        $name = $broadcaster::getName();
        $img = $broadcaster::getImg();

        $broadcaster_implementation_class = get_class($broadcaster);
        $broadcaster_id = base64_encode($broadcaster_implementation_class);

        $this->broadcasters[$broadcaster_id] = [
            'name' => $name,
            'img' => $img,
            'id' => $broadcaster_id,
            'implementation' => $broadcaster_implementation_class,
        ];

    }

    public function getBroadcasters(): array
    {
        return $this->broadcasters;
    }

    /**
     * @throws \Throwable
     */
    public function getBroadcaster(string $broadcaster): Broadcaster
    {
        throw_unless(array_key_exists($broadcaster, $this->broadcasters), new \Exception('Broadcaster not found'));

        $result = $this->broadcasters[$broadcaster];

        return app($result['implementation']);

    }
}

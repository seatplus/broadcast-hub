<?php

namespace Seatplus\BroadcastHub\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Seatplus\BroadcastHub\Recipient;
use Seatplus\BroadcastHub\Subscription;
use Seatplus\Eveapi\Models\Character\CharacterInfo;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {

        return [
            'recipient_id' => Recipient::factory(),
            'notification' => $this->faker->word,
            'subscribable_type' => CharacterInfo::class,
            'subscribable_id' => CharacterInfo::factory(),
        ];
    }
}

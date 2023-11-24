<?php

namespace Seatplus\BroadcastHub\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Seatplus\BroadcastHub\Recipient;

class RecipientFactory extends Factory
{
    protected $model = Recipient::class;

    public function definition()
    {
        return [
            'connector_id' => $this->faker->uuid,
            'connector_type' => $this->faker->word,
            'name' => $this->faker->word,
        ];
    }
}

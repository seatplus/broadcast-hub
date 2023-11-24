<?php

namespace Seatplus\BroadcastHub\Http\Controllers;

use Seatplus\BroadcastHub\Http\Actions\GetRecipientsAction;

class RecipientController
{
    public function __invoke(string $broadcaster, GetRecipientsAction $getRecipientsAction): \Illuminate\Database\Eloquent\Collection|array|\Illuminate\Support\Collection
    {
        return $getRecipientsAction->execute($broadcaster);
    }
}

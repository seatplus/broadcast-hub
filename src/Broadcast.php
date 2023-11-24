<?php

namespace Seatplus\BroadcastHub;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = [
        'notification',
        'entity_id',
        'entity_type',
        'payload_hash',
        'payload',
    ];

    protected $casts = [
        'payload' => 'collection',
    ];
}

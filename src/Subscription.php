<?php

namespace Seatplus\BroadcastHub;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Ramsey\Uuid\Uuid;

/** @property Uuid $id */
class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'recipient_id',
        'notification',
        'subscribable_id',
        'subscribable_type',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }
}

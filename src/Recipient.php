<?php

namespace Seatplus\BroadcastHub;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Seatplus\Connector\Models\User;

class Recipient extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'connector_id',
        'connector_type',
        'name',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            \Seatplus\Auth\Models\User::class,
            User::class,
            'connector_id',
            'id',
            'connector_id',
            'user_id'
        );
    }
}

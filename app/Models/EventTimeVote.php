<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTimeVote extends Model
{
    protected $fillable = [
        'event_time_option_id',
        'user_id',
        'vote',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(EventTimeOption::class, 'event_time_option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

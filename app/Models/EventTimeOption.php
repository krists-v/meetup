<?php

namespace App\Models;

use Database\Factories\EventTimeOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTimeOption extends Model
{
    /** @use HasFactory<EventTimeOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'starts_at',
        'position',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'position' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(EventTimeVote::class);
    }

    public function voteCount(string $vote): int
    {
        return $this->votes->where('vote', $vote)->count();
    }
}

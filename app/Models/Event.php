<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'sport_type',
        'event_date',
        'location',
        'max_participants',
        'description',
        'visibility',
        'invite_token',
        'schedule_type',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'max_participants' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->invite_token)) {
                $event->invite_token = Str::random(32);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function goingParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'going');
    }

    public function maybeParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'maybe');
    }

    public function notGoingParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'not_going');
    }

    public function isCreator(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function hasReachedCapacity(): bool
    {
        if ($this->max_participants === null) {
            return false;
        }

        return $this->goingParticipants()->count() >= $this->max_participants;
    }

    public function getUserStatus(User $user): ?string
    {
        $participant = $this->participants()->where('users.id', $user->id)->first();

        return $participant?->pivot->status;
    }

    public function timeOptions(): HasMany
    {
        return $this->hasMany(EventTimeOption::class)->orderBy('position')->orderBy('starts_at');
    }

    public function isPoll(): bool
    {
        return $this->schedule_type === 'poll';
    }

    public function bestTimeOption(): ?EventTimeOption
    {
        $options = $this->relationLoaded('timeOptions')
            ? $this->timeOptions
            : $this->timeOptions()->with('votes')->get();

        return $options->sort(function (EventTimeOption $a, EventTimeOption $b): int {
            $yes = $b->voteCount('yes') <=> $a->voteCount('yes');

            if ($yes !== 0) {
                return $yes;
            }

            $maybe = $b->voteCount('maybe') <=> $a->voteCount('maybe');

            if ($maybe !== 0) {
                return $maybe;
            }

            return $a->starts_at <=> $b->starts_at;
        })->first();
    }

    public function sportLabel(): string
    {
        return match ($this->sport_type) {
            'football' => 'Football',
            'tennis' => 'Tennis',
            'basketball' => 'Basketball',
            default => 'Other',
        };
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now());
    }
}

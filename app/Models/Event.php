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
        'sport',
        'custom_sport',
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

        static::saving(function (Event $event) {
            if (blank($event->sport)) {
                return;
            }

            $legacy = ['football', 'tennis', 'basketball', 'other'];
            $event->sport_type = in_array($event->sport, $legacy, true) ? $event->sport : 'other';

            if ($event->sport !== 'other') {
                $event->custom_sport = null;
            }
        });
    }

    public const SPORTS = [
        'padel' => 'Padel',
        'basketball' => 'Basketball',
        'football' => 'Football',
        'tennis' => 'Tennis',
        'hockey' => 'Hockey',
        'volleyball' => 'Volleyball',
        'other' => 'Other',
    ];

    public function sportSelection(): string
    {
        $sport = $this->sport ?: $this->sport_type;

        return array_key_exists((string) $sport, self::SPORTS) ? (string) $sport : 'other';
    }

    public function customSportInput(): string
    {
        if (filled($this->custom_sport)) {
            return $this->custom_sport;
        }

        $sport = $this->sport ?: $this->sport_type;

        if (! array_key_exists((string) $sport, self::SPORTS)) {
            return (string) $sport;
        }

        return '';
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

        return $options
            ->reject(fn (EventTimeOption $option) => $option->hasPassed())
            ->sort(function (EventTimeOption $a, EventTimeOption $b): int {
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
        if ($this->sport === 'other' && filled($this->custom_sport)) {
            return $this->custom_sport;
        }

        $sport = $this->sport ?: $this->sport_type;

        return self::SPORTS[$sport] ?? (string) $sport;
    }

    public function sportTone(): string
    {
        return match ($this->sport ?: $this->sport_type) {
            'padel' => 'bg-teal-100 text-teal-800',
            'basketball' => 'bg-orange-100 text-orange-800',
            'football' => 'bg-emerald-100 text-emerald-800',
            'tennis' => 'bg-lime-100 text-lime-800',
            'hockey' => 'bg-sky-100 text-sky-800',
            'volleyball' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
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

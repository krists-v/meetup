<?php

namespace App\Models;

use App\Notifications\PromotedFromWaitlist;
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
        'setting',
        'level',
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
            if (filled($event->sport)) {
                $legacy = ['football', 'tennis', 'basketball', 'other'];
                $event->sport_type = in_array($event->sport, $legacy, true) ? $event->sport : 'other';

                if ($event->sport !== 'other') {
                    $event->custom_sport = null;
                }
            }

            if (! in_array($event->setting, ['indoor', 'outdoor'], true)) {
                $event->setting = self::defaultSetting($event->sportSelection());
            }
        });
    }

    public const CATEGORIES = [
        'racket' => 'Racket',
        'team' => 'Team',
        'indoor' => 'Indoor',
        'outdoor' => 'Outdoor',
        'social' => 'Social',
    ];

    public const LEVELS = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'pro' => 'Pro',
    ];

    /**
     * Label, category, indoor or outdoor default, and Lucide icon.
     * Null category or setting means a custom activity.
     *
     * @var array<string, array{label: string, category: ?string, setting: ?string, icon: string}>
     */
    public const ACTIVITIES = [
        'padel' => ['label' => 'Padel', 'category' => 'racket', 'setting' => 'indoor', 'icon' => 'grid-2x2'],
        'tennis' => ['label' => 'Tennis', 'category' => 'racket', 'setting' => 'outdoor', 'icon' => 'circle'],
        'table-tennis' => ['label' => 'Table tennis', 'category' => 'racket', 'setting' => 'indoor', 'icon' => 'disc'],
        'basketball' => ['label' => 'Basketball', 'category' => 'team', 'setting' => 'indoor', 'icon' => 'circle-dot'],
        'football' => ['label' => 'Football', 'category' => 'team', 'setting' => 'outdoor', 'icon' => 'goal'],
        'hockey' => ['label' => 'Hockey', 'category' => 'team', 'setting' => 'indoor', 'icon' => 'snowflake'],
        'volleyball' => ['label' => 'Volleyball', 'category' => 'team', 'setting' => 'indoor', 'icon' => 'volleyball'],
        'bowling' => ['label' => 'Bowling', 'category' => 'social', 'setting' => 'indoor', 'icon' => 'disc-3'],
        'climbing' => ['label' => 'Climbing', 'category' => 'indoor', 'setting' => 'indoor', 'icon' => 'brick-wall'],
        'running' => ['label' => 'Running', 'category' => 'outdoor', 'setting' => 'outdoor', 'icon' => 'footprints'],
        'cycling' => ['label' => 'Cycling', 'category' => 'outdoor', 'setting' => 'outdoor', 'icon' => 'bike'],
        'hiking' => ['label' => 'Hiking', 'category' => 'outdoor', 'setting' => 'outdoor', 'icon' => 'trees'],
        'other' => ['label' => 'Other', 'category' => null, 'setting' => null, 'icon' => 'sparkles'],
    ];

    public const SPORTS = [
        'padel' => 'Padel',
        'tennis' => 'Tennis',
        'table-tennis' => 'Table tennis',
        'basketball' => 'Basketball',
        'football' => 'Football',
        'hockey' => 'Hockey',
        'volleyball' => 'Volleyball',
        'bowling' => 'Bowling',
        'climbing' => 'Climbing',
        'running' => 'Running',
        'cycling' => 'Cycling',
        'hiking' => 'Hiking',
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

    public function hasUnspecifiedSport(): bool
    {
        return $this->sportSelection() === 'other' && $this->customSportInput() === '';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot('id', 'status')
            ->withTimestamps();
    }

    public function goingParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'going');
    }

    public function waitlistParticipants(): BelongsToMany
    {
        return $this->participants()
            ->wherePivot('status', 'waitlist')
            ->orderByPivot('created_at')
            ->orderByPivot('id');
    }

    public function maybeParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'maybe');
    }

    public function notGoingParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'not_going');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at')->orderBy('id');
    }

    public function shareUrl(): string
    {
        if ($this->visibility === 'private') {
            return route('events.invite', $this->invite_token);
        }

        return route('events.show', $this);
    }

    public function shareText(): string
    {
        $when = $this->isPoll()
            ? 'Time still to be chosen'
            : $this->event_date->timezone(config('app.timezone'))->format('D j M, H:i');

        return $this->title.'. '.$this->sportLabel().', '.$when.', '.$this->location.'. '.$this->shareUrl();
    }

    public function whatsAppUrl(): string
    {
        return 'https://wa.me/?text='.rawurlencode($this->shareText());
    }

    public function isCreator(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function hasReachedCapacity(): bool
    {
        return $this->isFull();
    }

    public function goingCount(): int
    {
        if (isset($this->going_participants_count)) {
            return (int) $this->going_participants_count;
        }

        if ($this->relationLoaded('participants')) {
            return $this->participants->where('pivot.status', 'going')->count();
        }

        return $this->goingParticipants()->count();
    }

    public function isFull(): bool
    {
        return ! $this->isPoll()
            && $this->max_participants !== null
            && $this->goingCount() >= $this->max_participants;
    }

    public function attendanceLabel(): string
    {
        if ($this->isPoll()) {
            return $this->max_participants
                ? 'max '.$this->max_participants.' once a time is chosen'
                : '';
        }

        $going = $this->goingCount();

        if ($this->max_participants === null) {
            return $going.' going';
        }

        $left = max(0, $this->max_participants - $going);
        $spots = $left === 1 ? '1 spot left' : $left.' spots left';

        return $going.' of '.$this->max_participants.' going · '.$spots;
    }

    public function mapUrl(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->location);
    }

    public function matchesInviteToken(mixed $token): bool
    {
        if (! is_string($token) || $token === '' || strlen($token) !== strlen((string) $this->invite_token)) {
            return false;
        }

        return hash_equals((string) $this->invite_token, $token);
    }

    public function getUserStatus(User $user): ?string
    {
        $participant = $this->participants()->where('users.id', $user->id)->first();

        return $participant?->pivot->status;
    }

    public function waitlistPosition(User $user): ?int
    {
        $index = $this->waitlistParticipants()->pluck('users.id')->search($user->id);

        return $index === false ? null : $index + 1;
    }

    public function promoteWaitlist(): void
    {
        $this->unsetRelation('participants');
        $this->unsetRelation('goingParticipants');

        $open = $this->max_participants === null
            ? PHP_INT_MAX
            : max(0, $this->max_participants - $this->goingParticipants()->count());

        if ($open < 1) {
            return;
        }

        foreach ($this->waitlistParticipants()->get() as $waiting) {
            if ($open < 1) {
                break;
            }

            $this->participants()->updateExistingPivot($waiting->id, [
                'status' => 'going',
            ]);
            $waiting->notify(new PromotedFromWaitlist($this));
            $open--;
        }
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

    public static function defaultSetting(?string $sport): string
    {
        $sport = array_key_exists((string) $sport, self::SPORTS) ? (string) $sport : 'other';

        return self::ACTIVITIES[$sport]['setting'] === 'indoor' ? 'indoor' : 'outdoor';
    }

    public static function activityIcon(?string $sport): string
    {
        $sport = array_key_exists((string) $sport, self::SPORTS) ? (string) $sport : 'other';

        return self::ACTIVITIES[$sport]['icon'];
    }

    /**
     * @return list<string>
     */
    public static function slugsInCategory(string $category): array
    {
        return array_keys(array_filter(
            self::ACTIVITIES,
            fn (array $activity): bool => $activity['category'] === $category,
        ));
    }

    public function isIndoor(): bool
    {
        return $this->setting === 'indoor';
    }

    public function welcomesBeginners(): bool
    {
        return $this->level === null || $this->level === 'beginner';
    }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->level] ?? 'All levels';
    }

    public static function accentInk(?string $sport): string
    {
        if (preg_match('/text-activity-[\w-]+/', self::accentClasses($sport), $match) === 1) {
            return $match[0];
        }

        return 'text-pitch-700';
    }

    public static function accentClasses(?string $sport): string
    {
        $sport = array_key_exists((string) $sport, self::SPORTS) ? (string) $sport : 'other';

        return match ($sport) {
            'padel' => 'bg-activity-padel-soft text-activity-padel',
            'tennis' => 'bg-activity-tennis-soft text-activity-tennis',
            'table-tennis' => 'bg-activity-table-tennis-soft text-activity-table-tennis',
            'basketball' => 'bg-activity-basketball-soft text-activity-basketball',
            'football' => 'bg-activity-football-soft text-activity-football',
            'hockey' => 'bg-activity-hockey-soft text-activity-hockey',
            'volleyball' => 'bg-activity-volleyball-soft text-activity-volleyball',
            'bowling' => 'bg-activity-bowling-soft text-activity-bowling',
            'climbing' => 'bg-activity-climbing-soft text-activity-climbing',
            'running' => 'bg-activity-running-soft text-activity-running',
            'cycling' => 'bg-activity-cycling-soft text-activity-cycling',
            'hiking' => 'bg-activity-hiking-soft text-activity-hiking',
            default => 'bg-activity-other-soft text-activity-other',
        };
    }

    public function sportTone(): string
    {
        return self::accentClasses($this->sportSelection());
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

<?php

namespace App\Models;

use App\Notifications\PromotedFromWaitlist;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
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
        'photo_path',
        'price',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'max_participants' => 'integer',
        'price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->invite_token)) {
                $event->invite_token = Str::random(32);
            }
        });

        static::deleting(function (Event $event) {
            $event->deletePhotoFile();
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
        $date = $this->event_date->timezone(config('app.timezone'));
        $when = $this->isPoll()
            ? 'time still open'
            : $date->format('l').' at '.$date->format('H:i');

        return $this->title.', '.$when.', '.$this->location.'. Come play. '.$this->shareUrl();
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
                ? 'Up to '.$this->max_participants.' can play'
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

    public function spotsLeftLabel(): ?string
    {
        if ($this->isPoll()) {
            return 'Pick a time';
        }

        if ($this->max_participants === null) {
            return null;
        }

        if ($this->isFull()) {
            return 'Full';
        }

        $left = max(0, $this->max_participants - $this->goingCount());

        return $left === 1 ? '1 spot left' : $left.' spots left';
    }

    public function playersLabel(): string
    {
        $going = $this->goingCount();

        if ($this->max_participants === null) {
            return $going === 1 ? '1 player' : $going.' players';
        }

        return $going.' / '.$this->max_participants.' players';
    }

    public function whenLabel(): string
    {
        if ($this->isPoll()) {
            $count = (int) ($this->time_options_count ?? $this->timeOptions()->count());

            return $count === 1 ? '1 time to pick' : $count.' times to pick';
        }

        return $this->event_date->timezone(config('app.timezone'))->format('l, j F · H:i');
    }

    public function areaLabel(): string
    {
        $location = trim((string) $this->location);

        foreach ([' · ', '·', ','] as $separator) {
            if (! str_contains($location, $separator)) {
                continue;
            }

            $parts = array_values(array_filter(array_map('trim', explode($separator, $location)), fn (string $part) => $part !== ''));

            return $parts[count($parts) - 1] ?? $location;
        }

        return $location;
    }

    public function priceLabel(): ?string
    {
        if ($this->price === null) {
            return null;
        }

        if ((float) $this->price === 0.0) {
            return 'Free';
        }

        if ($this->max_participants === null || $this->max_participants < 1) {
            return '€'.$this->money((float) $this->price).' total';
        }

        $each = round((float) $this->price / $this->max_participants, 2);

        return '€'.$this->money($each).' / person';
    }

    public function photoUrl(): string
    {
        if (filled($this->photo_path)) {
            return route('events.photo', $this).'?v='.$this->updated_at?->getTimestamp();
        }

        return asset('images/sports/'.$this->defaultPhotoSlug().'.svg');
    }

    public function defaultPhotoSlug(): string
    {
        $sport = $this->sportSelection();

        return is_file(public_path('images/sports/'.$sport.'.svg')) ? $sport : 'generic';
    }

    public function deletePhotoFile(): void
    {
        if (blank($this->photo_path)) {
            return;
        }

        $shared = static::query()
            ->where('photo_path', $this->photo_path)
            ->where('id', '!=', $this->id)
            ->exists();

        if (! $shared) {
            Storage::disk('photos')->delete($this->photo_path);
        }
    }

    public function money(float $amount): string
    {
        $rounded = round($amount, 2);

        if (abs($rounded - floor($rounded)) < 0.001) {
            return (string) (int) round($rounded);
        }

        return number_format($rounded, 2, '.', '');
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
        return 'text-success';
    }

    public static function accentClasses(?string $sport): string
    {
        return 'bg-mist text-ink';
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

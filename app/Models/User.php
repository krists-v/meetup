<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'favorite_sports',
        'sport_levels',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'favorite_sports' => 'array',
            'sport_levels' => 'array',
        ];
    }

    public function sportLevel(?string $sport): ?string
    {
        if ($sport === null || ! array_key_exists($sport, Event::SPORTS)) {
            return null;
        }

        $level = ($this->sport_levels ?? [])[$sport] ?? null;

        return is_string($level) && array_key_exists($level, Event::LEVELS) ? $level : null;
    }

    public function sportLevelLabel(?string $sport): ?string
    {
        $level = $this->sportLevel($sport);

        return $level === null ? null : Event::LEVELS[$level];
    }

    public function avatarUrl(): ?string
    {
        if (blank($this->avatar_path)) {
            return null;
        }

        return route('avatars.show', $this).'?v='.$this->updated_at?->getTimestamp();
    }

    public function deleteAvatarFile(): void
    {
        if (filled($this->avatar_path)) {
            Storage::disk('avatars')->delete($this->avatar_path);
        }
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->using(EventUser::class)
            ->withPivot('id', 'status', 'attended')
            ->withTimestamps();
    }

    /**
     * @var array{showed: int, total: int}|null
     */
    protected ?array $attendanceCache = null;

    /**
     * Past games this person was going to, and how many of those they showed up for.
     * A blank flag counts as showed up. False is a no-show. Fewer than three past games is a new player.
     *
     * @return array{showed: int, total: int, label: string}
     */
    public function attendance(): array
    {
        if ($this->attendanceCache === null) {
            self::loadAttendance([$this]);
        }

        $showed = $this->attendanceCache['showed'];
        $total = $this->attendanceCache['total'];

        return [
            'showed' => $showed,
            'total' => $total,
            'label' => $total < 3 ? 'New player' : 'Shows up '.$showed.'/'.$total,
        ];
    }

    public function attendanceLabel(): string
    {
        return $this->attendance()['label'];
    }

    /**
     * @param  iterable<int, User>  $users
     */
    public static function loadAttendance(iterable $users): void
    {
        $users = $users instanceof Collection ? $users : collect($users);
        $users = $users->filter(fn ($user) => $user instanceof self);

        if ($users->isEmpty()) {
            return;
        }

        $rows = DB::table('event_user')
            ->join('events', 'events.id', '=', 'event_user.event_id')
            ->whereIn('event_user.user_id', $users->pluck('id')->unique()->all())
            ->where('event_user.status', 'going')
            ->where('events.event_date', '<', now())
            ->get(['event_user.user_id', 'event_user.attended']);

        $grouped = $rows->groupBy('user_id');

        foreach ($users as $user) {
            $mine = $grouped->get($user->id, collect());
            $total = $mine->count();
            $showed = $mine->reject(fn (object $row) => self::markedNoShow($row->attended))->count();
            $user->attendanceCache = [
                'showed' => $showed,
                'total' => $total,
            ];
        }
    }

    private static function markedNoShow(mixed $attended): bool
    {
        return $attended === false || $attended === 0 || $attended === '0';
    }
}

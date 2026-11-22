<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
            ->withPivot('status')
            ->withTimestamps();
    }
}

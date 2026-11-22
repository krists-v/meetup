<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    public static function separates(int $one, int $other): bool
    {
        if ($one === $other) {
            return false;
        }

        return self::query()
            ->where(function ($query) use ($one, $other): void {
                $query->where('blocker_id', $one)->where('blocked_id', $other);
            })
            ->orWhere(function ($query) use ($one, $other): void {
                $query->where('blocker_id', $other)->where('blocked_id', $one);
            })
            ->exists();
    }
}

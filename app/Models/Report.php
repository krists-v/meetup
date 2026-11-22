<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const OPEN = 'open';

    /**
     * @var array<string, string>
     */
    public const REASONS = [
        'no_show' => 'No-show',
        'rude' => 'Rude or abusive',
        'spam' => 'Spam',
        'fake_profile' => 'Fake profile',
        'other' => 'Other',
    ];

    protected $fillable = [
        'reporter_id',
        'reported_id',
        'reason',
        'note',
        'status',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reported(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}

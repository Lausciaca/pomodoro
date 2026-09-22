<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroSession extends Model
{
    use HasFactory;

    public const PHASE_FOCUS = 'focus';

    public const PHASE_SHORT_BREAK = 'short_break';

    public const PHASE_LONG_BREAK = 'long_break';

    public const SOURCE_TIMER = 'timer';

    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'user_id',
        'phase',
        'duration_minutes',
        'started_at',
        'completed_at',
        'status',
        'source',
        'batch_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('completed_at', now()->toDateString());
    }

    public function scopeFocus(Builder $query): Builder
    {
        return $query->where('phase', self::PHASE_FOCUS);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_MANUAL);
    }

    public function scopeFromTimer(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_TIMER);
    }
}

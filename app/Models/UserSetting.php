<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'study_minutes',
        'short_break_minutes',
        'long_break_minutes',
        'cycles_before_long_break',
        'auto_start_breaks',
        'auto_start_pomodoros',
        'notifications_enabled',
        'sound_enabled',
    ];

    protected function casts(): array
    {
        return [
            'study_minutes' => 'integer',
            'short_break_minutes' => 'integer',
            'long_break_minutes' => 'integer',
            'cycles_before_long_break' => 'integer',
            'auto_start_breaks' => 'boolean',
            'auto_start_pomodoros' => 'boolean',
            'notifications_enabled' => 'boolean',
            'sound_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return the settings row for a user, creating it with defaults if missing.
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'study_minutes' => 25,
                'short_break_minutes' => 5,
                'long_break_minutes' => 15,
                'cycles_before_long_break' => 4,
                'auto_start_breaks' => true,
                'auto_start_pomodoros' => false,
                'notifications_enabled' => true,
                'sound_enabled' => true,
            ]
        );
    }
}

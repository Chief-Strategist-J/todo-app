<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PomodoroTimer extends Model
{
    use HasFactory;
    protected $table = 'pomodoro_timers';

    protected $fillable = [
        'pomodoro_id',
        'started_at',
        'completed_at',
        'stopped_at',
        'resumed_at',
        'segment_duration_seconds',
        'status',
        'user_action',
        'duration_in_seconds',
        'pause_duration_seconds',
        'active_duration_seconds',
        'is_interrupted',
        'number_of_pauses',
        'device_used',
        'user_feedback',
        'additional_metadata',
        'performance_score',
    ];

    protected $casts = [
        'additional_metadata' => 'array',
        'is_interrupted' => 'boolean',
    ];

    public function pomodoro()
    {
        return $this->belongsTo(Pomodoro::class);
    }
}

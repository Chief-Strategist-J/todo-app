<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PomodoroUser extends Model
{
    use HasFactory;

    protected $table = 'pomodoro_users';

    protected $fillable = [
        'pomodoro_id',
        'user_id',
        'todo_id',
        'assigned_duration',
        'completed_at',
        'role',
        'is_active',
        'is_completed'
    ];

    protected $casts = [
        'assigned_duration' => 'integer',
        'completed_at' => 'datetime',
        'role' => 'string',
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
    ];
}

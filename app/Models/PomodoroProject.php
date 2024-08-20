<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PomodoroProject extends Model
{
    use HasFactory;
    
    protected $table = 'pomodoro_projects';

    protected $fillable = [
        'pomodoro_id',
        'project_id',
        'association_type',
        'is_mandatory',
    ];

    protected $casts = [
        'association_type' => 'string',
        'is_mandatory' => 'boolean',
    ];
}

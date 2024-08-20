<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PomodoroTag extends Model
{
    use HasFactory;

    protected $table = 'pomodoro_tags';

    protected $fillable = [
        'pomodoro_id',
        'tag_id',
        'tag_type',
    ];

    protected $casts = [
        'tag_type' => 'string',
    ];
}

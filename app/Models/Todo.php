<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Todo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'is_completed',
        'due_date',
        'priority',
        'assigned_to',
        'tags',
        'created_by',
        'updated_by',
        'status',
        'reminder',
        'attachment',
        'category',
        'estimated_time',
        'actual_time',
        'location',
        'recurring',
        'recurring_frequency',
        'notes',
        'completed_at',
        'color_code',
        'is_archived',
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'is_completed' => 'boolean',
        'due_date' => 'datetime',
        'priority' => 'string',
        'assigned_to' => 'integer',
        'tags' => 'string',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'status' => 'string',
        'reminder' => 'datetime',
        'attachment' => 'string',
        'category' => 'string',
        'estimated_time' => 'integer',
        'actual_time' => 'integer',
        'location' => 'string',
        'recurring' => 'boolean',
        'recurring_frequency' => 'string',
        'notes' => 'string',
        'completed_at' => 'datetime',
        'color_code' => 'string',
        'is_archived' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}

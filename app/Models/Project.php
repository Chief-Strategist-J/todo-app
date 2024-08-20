<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    // Table name
    protected $table = 'projects';

    // Fillable fields
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'currency',
        'progress_percentage',
        'priority',
        'is_public',
        'client_name',
        'project_manager',
        'estimated_hours',
        'actual_hours',
        'repository_url',
        'documentation_url',
        'category',
        'is_archived',
        'task_count',
        'completed_task_count',
        'team_size',
        'last_activity_at',
        'project_code',
        'risk_score',
        'status_color',
        'comment_count',
        'attachment_count',
        'completion_percentage',
        'main_language',
        'is_featured',
        'customer_satisfaction_score',
        'revision_count',
        'project_type',
        'roi',
        'stakeholder_count',
        'budget_utilization',
        'project_phase',
        'lessons_learned',
        'created_by',
        'updated_by',
        'department_id',
    ];

    // Cast attributes to their appropriate types
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'customer_satisfaction_score' => 'decimal:2',
        'roi' => 'decimal:2',
        'budget_utilization' => 'decimal:2',
        'is_public' => 'boolean',
        'is_archived' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array', // Assuming metadata is a JSON field
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

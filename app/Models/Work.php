<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'firebase_work_id' => 'string',
        'project_name' => 'string',
        'description' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'role' => 'string',
        'technologies_used' => 'string',
        'is_featured' => 'boolean',
        'url' => 'string',
        'client_name' => 'string',
        'client_contact' => 'string',
        'project_type' => 'string',
        'project_status' => 'string',
        'responsibilities' => 'string',
        'challenges' => 'string',
    ];

    protected $fillable = [
        'firebase_work_id',
        'user_id',
        'user_work_detail_id',
        'project_name',
        'description',
        'start_date',
        'end_date',
        'role',
        'technologies_used',
        'is_featured',
        'url',
        'client_name',
        'client_contact',
        'project_type',
        'project_status',
        'responsibilities',
        'challenges',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userWorkDetail(): BelongsTo
    {
        return $this->belongsTo(UserWork::class, 'user_work_detail_id');
    }
}

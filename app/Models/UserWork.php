<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWork extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'firebase_user_works_id',
        'user_id',
        'user_detail_id',
        'company_name',
        'position',
        'start_date',
        'end_date',
        'current_job',
        'responsibilities',
        'location',
        'department',
        'is_remote',
        'supervisor_name',
        'supervisor_contact',
        'salary',
        'employment_type',
        'reason_for_leaving',
        'achievements',
        'is_verified',
    ];
    protected $casts = [
        'firebase_user_works_id' => 'string',
        'company_name' => 'string',
        'position' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'current_job' => 'boolean',
        'responsibilities' => 'string',
        'location' => 'string',
        'department' => 'string',
        'is_remote' => 'boolean',
        'supervisor_name' => 'string',
        'supervisor_contact' => 'string',
        'salary' => 'string',
        'employment_type' => 'string',
        'reason_for_leaving' => 'string',
        'achievements' => 'string',
        'is_verified' => 'boolean',
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

    public function userDetail(): BelongsTo
    {
        return $this->belongsTo(UserDetail::class);
    }

    public function works(): HasMany
    {
        return $this->hasMany(Work::class, 'user_work_detail_id');
    }
}

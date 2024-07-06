<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Education extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'firebase_education_id',
        'user_education_detail_id',
        'user_id',
        'course_name',
        'description',
        'start_date',
        'end_date',
        'instructor',
        'grade',
        'is_featured',
        'certificate_url',
        'institution_name',
        'course_type',
        'status',
        'credits',
        'course_code',
        'department',
        'semester',
        'is_mandatory',
        'is_elective',
        'syllabus',
        'prerequisites',
        'is_online',
        'feedback',
        'resources',
    ];
    protected $casts = [
        'firebase_education_id' => 'string',
        'course_name' => 'string',
        'description' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'instructor' => 'string',
        'grade' => 'string',
        'is_featured' => 'boolean',
        'certificate_url' => 'string',
        'institution_name' => 'string',
        'course_type' => 'string',
        'status' => 'string',
        'credits' => 'string',
        'course_code' => 'string',
        'department' => 'string',
        'semester' => 'string',
        'is_mandatory' => 'boolean',
        'is_elective' => 'boolean',
        'syllabus' => 'string',
        'prerequisites' => 'string',
        'is_online' => 'boolean',
        'feedback' => 'string',
        'resources' => 'string',
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

    public function userEducationDetail(): BelongsTo
    {
        return $this->belongsTo(UserEducation::class, 'user_education_detail_id');
    }
}

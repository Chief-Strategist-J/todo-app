<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEducation extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'firebase_user_education_id', 'user_detail_id', 'user_id', 'institution_name', 'degree', 'field_of_study', 'start_date', 'end_date',
        'grade', 'currently_studying', 'location', 'activities', 'achievements', 'is_verified', 'major', 'minor', 'advisor', 'thesis_title',
        'honors', 'study_mode', 'description', 'transcript_url', 'certification', 'is_completed', 'institution_website', 'notes'
    ];

    protected $casts = [
        'currently_studying' => 'boolean',
        'is_verified' => 'boolean',
        'honors' => 'boolean',
        'is_completed' => 'boolean',
        'firebase_user_education_id' => 'string',
        'institution_name' => 'string',
        'degree' => 'string',
        'field_of_study' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'grade' => 'string',
        'location' => 'string',
        'activities' => 'string',
        'achievements' => 'string',
        'major' => 'string',
        'minor' => 'string',
        'advisor' => 'string',
        'thesis_title' => 'string',
        'study_mode' => 'string',
        'description' => 'string',
        'transcript_url' => 'string',
        'certification' => 'string',
        'institution_website' => 'string',
        'notes' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'user_education_detail_id');
    }

    public function userDetail(): BelongsTo
    {
        return $this->belongsTo(UserDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Preference extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'firebase_preferences_id' => 'string',
        'user_preferences_detail_id' => 'integer',
        'user_id' => 'integer',
        'category' => 'string',
        'name' => 'string',
        'value' => 'string',
    ];

    protected $fillable = [
        'firebase_preferences_id',
        'user_preferences_detail_id',
        'user_id',
        'category',
        'name',
        'value',
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

    public function userPreferences(): BelongsTo
    {
        return $this->belongsTo(UserPreference::class, 'user_preferences_detail_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSocialAccountDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'firebase_account_details_id', 'user_detail_id', 'user_id', 'provider', 'social_id', 'avatar', 'profile_url',
        'profile_data', 'access_token', 'refresh_token', 'token_expires_at', 'is_active'
    ];

    protected $casts = [
        'firebase_account_details_id' => 'string',
        'provider' => 'string',
        'social_id' => 'string',
        'avatar' => 'string',
        'profile_url' => 'string',
        'profile_data' => 'json',
        'access_token' => 'string',
        'refresh_token' => 'string',
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function socialAccountDetails(): HasMany
    {
        return $this->hasMany(SocialAccountDetail::class, 'user_social_account_id');
    }
}

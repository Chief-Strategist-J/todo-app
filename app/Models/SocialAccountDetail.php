<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialAccountDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'firebase_social_account_detail_id' => 'string',
        'user_social_account_id' => 'integer',
        'user_id' => 'integer',
        'social_provider' => 'string',
        'social_username' => 'string',
        'social_email' => 'string',
        'social_avatar' => 'string',
        'social_profile_data' => 'json',
        'social_access_token' => 'string',
        'social_refresh_token' => 'string',
        'social_token_expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    protected $fillable = [
        'firebase_social_account_detail_id',
        'user_social_account_id',
        'user_id',
        'social_provider',
        'social_username',
        'social_email',
        'social_avatar',
        'social_profile_data',
        'social_access_token',
        'social_refresh_token',
        'social_token_expires_at',
        'is_verified',
    ];

    public function userDetail(): BelongsTo
    {
        return $this->belongsTo(UserDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userSocialAccount(): BelongsTo
    {
        return $this->belongsTo(UserSocialAccountDetail::class, 'user_social_account_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name','email', 'phone','birthdate','address','city','state',
        'country','zipcode','avatar','bio','is_active','last_login_at'
    ];

    protected $casts = [
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'birthdate' => 'date',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'country' => 'string',
        'zipcode' => 'string',
        'avatar' => 'string',
        'bio' => 'string',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'user_settings' => 'json',
        'additional_details' => 'json',
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

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function userSocialAccountDetails(): HasMany
    {
        return $this->hasMany(UserSocialAccountDetail::class);
    }
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }
    public function userEducations(): HasMany
    {
        return $this->hasMany(UserEducation::class);
    }
    public function userWorks(): HasMany
    {
        return $this->hasMany(UserWork::class);
    }


    
}

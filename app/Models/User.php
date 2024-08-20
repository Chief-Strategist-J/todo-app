<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, CanResetPassword, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at',
        'deleted_at',
    ];
   
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userDetails(): HasOne
    {
        return $this->hasOne(UserDetail::class);
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
    public function preferences(): HasMany
    {
        return $this->hasMany(Preference::class);
    }
    public function socialAccountDetails(): HasMany
    {
        return $this->hasMany(SocialAccountDetail::class);
    }
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
    public function userWorks(): HasMany
    {
        return $this->hasMany(UserWork::class);
    }
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function pomodoros(): BelongsToMany
    {
        return $this->belongsToMany(Pomodoro::class, 'pomodoro_user')
            ->withPivot(['todo_id', 'assigned_duration', 'completed_at', 'role', 'is_active', 'is_completed'])
            ->withTimestamps();
    }
}

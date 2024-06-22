<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPreference extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'firebase_user_preferences_id', 'user_detail_id', 'user_id', 'receive_email_notifications', 'dark_mode', 'language', 'timezone',
        'dashboard_settings', 'additional_details', 'enable_two_factor_auth', 'enable_automatic_updates', 'enable_push_notifications',
        'theme_color', 'currency', 'notification_sound', 'show_online_status', 'default_view_mode', 'items_per_page'
    ];

    protected $casts = [
        'firebase_user_preferences_id' => 'string',
        'receive_email_notifications' => 'boolean',
        'dark_mode' => 'boolean',
        'language' => 'string',
        'timezone' => 'string',
        'dashboard_settings' => 'json',
        'additional_details' => 'json',
        'enable_two_factor_auth' => 'boolean',
        'enable_automatic_updates' => 'boolean',
        'enable_push_notifications' => 'boolean',
        'theme_color' => 'string',
        'currency' => 'string',
        'notification_sound' => 'string',
        'show_online_status' => 'boolean',
        'default_view_mode' => 'string',
        'items_per_page' => 'integer',
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

    public function preferences(): HasMany
    {
        return $this->hasMany(Preference::class, 'user_preferences_detail_id');
    }
}

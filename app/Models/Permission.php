<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;
    protected $casts = [
        'firebase_permissions_id' => 'string',
        'name' => 'string',
        'description' => 'string',
        'resource' => 'string',
        'action' => 'string',
        'is_active' => 'boolean',
        'settings' => 'json',
        'permissions_data' => 'json',
        'grant_type' => 'string',
        'level' => 'integer',
    ];

    protected $fillable = [
        'firebase_permissions_id',
        'user_permissions_detail_id',
        'user_id',
        'name',
        'description',
        'resource',
        'action',
        'is_active',
        'settings',
        'permissions_data',
        'grant_type',
        'level',
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
}

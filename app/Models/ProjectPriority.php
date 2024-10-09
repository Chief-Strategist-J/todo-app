<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPriority extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'settings','created_by'];

    protected $casts = [
        'settings' => 'array',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}

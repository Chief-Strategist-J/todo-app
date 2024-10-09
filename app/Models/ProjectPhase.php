<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPhase extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'details','created_by'];

    protected $casts = [
        'details' => 'array',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}

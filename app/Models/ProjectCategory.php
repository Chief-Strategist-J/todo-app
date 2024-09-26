<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Define the relationship with the projects
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

}

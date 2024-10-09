<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function createPriorityIfNotExists(string $priorityName, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($priorityName, $userId) {
                // Check if the priority already exists
                $exists = DB::table('project_priorities')
                    ->where('name', $priorityName)
                    ->where('created_by', $userId)
                    ->exists();

                if (!$exists) {
                    // Insert the new priority
                    DB::table('project_priorities')->insert([
                        'name' => $priorityName,
                        'created_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    return true;
                }

                return false;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create project priority: ' . $e->getMessage());
            return false;
        }
    }
}

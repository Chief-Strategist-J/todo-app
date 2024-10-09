<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'indicators','created_by'];

    protected $casts = [
        'indicators' => 'array',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function createStatusIfNotExists(string $statusName, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($statusName, $userId) {
                // Check if the status already exists
                $exists = DB::table('project_statuses')
                    ->where('name', $statusName)
                    ->where('created_by', $userId)
                    ->exists();

                if (!$exists) {
                    // Insert the new status
                    DB::table('project_statuses')->insert([
                        'name' => $statusName,
                        'created_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    return true;
                }

                return false;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create project status: ' . $e->getMessage());
            return false;
        }
    }
}

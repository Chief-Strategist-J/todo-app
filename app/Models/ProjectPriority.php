<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectPriority extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'settings', 'created_by'];

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

    public function getPaginatedPrioritiesForUser(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return Cache::flexible("priorities_for_user_{$userId}_page_{$page}", [60, 120], function () use ($userId, $page, $perPage): LengthAwarePaginator {
            try {
                return DB::transaction(function () use ($userId, $page, $perPage) {
                    $query = DB::table('project_priorities')
                        ->where('created_by', $userId)
                        ->select('id', 'name', 'settings');

                    return $query->paginate($perPage, ['*'], 'page', $page);
                });
            } catch (\Exception $e) {
                Log::error('Failed to retrieve paginated priorities for user: ' . $e->getMessage());
                return new LengthAwarePaginator([], 0, $perPage, $page); // Return empty paginator in case of error
            }
        });
    }

    public function updatePriority(int $id, int $userId, array $data): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId, $data) {
                return DB::table('project_priorities')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->update([
                        'name' => $data['name'],
                        'settings' => json_encode($data['settings']), // Assuming settings need to be stored as JSON
                        'updated_at' => now(),
                    ]) > 0; // Return true if update was successful
            });
        } catch (\Exception $e) {
            Log::error('Failed to update project priority: ' . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function deletePriority(int $id, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId) {
                return DB::table('project_priorities')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->delete() > 0; // Return true if deletion was successful
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete project priority: ' . $e->getMessage());
            return false; // Return false on error
        }
    }

}

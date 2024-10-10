<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'indicators', 'created_by'];

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

    public function getPaginatedStatusesForUser(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return Cache::flexible("statuses_for_user_{$userId}_page_{$page}", [60, 120], function () use ($userId, $page, $perPage): LengthAwarePaginator {
            try {
                return DB::transaction(function () use ($userId, $page, $perPage) {
                    $query = DB::table('project_statuses')
                        ->where('created_by', $userId)
                        ->select('id', 'name', 'indicators');

                    return $query->paginate($perPage, ['*'], 'page', $page);
                });
            } catch (\Exception $e) {
                Log::error('Failed to retrieve paginated statuses for user: ' . $e->getMessage());
                return new LengthAwarePaginator([], 0, $perPage, $page); // Return empty paginator in case of error
            }
        });
    }

    public function updateStatus(int $id, int $userId, array $data): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId, $data) {
                return DB::table('project_statuses')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->update([
                        'name' => $data['name'],
                        'indicators' => json_encode($data['indicators']), // Assuming indicators need to be stored as JSON
                        'updated_at' => now(),
                    ]) > 0; // Return true if update was successful
            });
        } catch (\Exception $e) {
            Log::error('Failed to update project status: ' . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function deleteStatus(int $id, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId) {
                return DB::table('project_statuses')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->delete() > 0; // Return true if deletion was successful
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete project status: ' . $e->getMessage());
            return false; // Return false on error
        }
    }
}

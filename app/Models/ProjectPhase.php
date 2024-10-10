<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectPhase extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'details', 'created_by'];

    protected $casts = [
        'details' => 'array',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function createPhaseIfNotExists(string $phaseName, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($phaseName, $userId) {
                // Check if the phase already exists
                $exists = DB::table('project_phases')
                    ->where('name', $phaseName)
                    ->where('created_by', $userId)
                    ->exists();

                if (!$exists) {
                    DB::table('project_phases')->insert([
                        'name' => $phaseName,
                        'created_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    return true;
                }

                return false;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create project phase: ' . $e->getMessage());
            return false;
        }
    }

    public function getPaginatedPhasesForUser(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return Cache::flexible("phases_for_user_{$userId}_page_{$page}", [60, 120], function () use ($userId, $page, $perPage): LengthAwarePaginator {
            try {
                return DB::transaction(function () use ($userId, $page, $perPage) {
                    $query = DB::table('project_phases')
                        ->where('created_by', $userId)
                        ->select('id', 'name', 'details');

                    return $query->paginate($perPage, ['*'], 'page', $page);
                });
            } catch (\Exception $e) {
                Log::error('Failed to retrieve paginated phases for user: ' . $e->getMessage());
                return new LengthAwarePaginator([], 0, $perPage, $page); // Return empty paginator in case of error
            }
        });
    }

    public function updatePhase(int $id, int $userId, array $data): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId, $data) {
                return DB::table('project_phases')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->update([
                        'name' => $data['name'],
                        'details' => json_encode($data['details']),
                        'updated_at' => now(),
                    ]) > 0;
            });
        } catch (\Exception $e) {
            Log::error('Failed to update project phase: ' . $e->getMessage());
            return false;
        }
    }

    public function deletePhase(int $id, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId) {
                return DB::table('project_phases')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->delete() > 0;
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete project phase: ' . $e->getMessage());
            return false; // Return false on error
        }
    }

}

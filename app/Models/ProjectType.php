<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'attributes', 'created_by'];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function createTypeIfNotExists(string $typeName, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($typeName, $userId) {
                // Check if the type already exists
                $exists = DB::table('project_types')
                    ->where('name', $typeName)
                    ->where('created_by', $userId)
                    ->exists();

                if (!$exists) {
                    // Insert the new type
                    DB::table('project_types')->insert([
                        'name' => $typeName,
                        'created_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    return true;
                }

                return false;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create project type: ' . $e->getMessage());
            return false;
        }
    }

    public function getPaginatedTypesForUser(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return Cache::flexible("types_for_user_{$userId}_page_{$page}", [60, 120], function () use ($userId, $page, $perPage): LengthAwarePaginator {
            try {
                return DB::transaction(function () use ($userId, $page, $perPage) {
                    $query = DB::table('project_types')
                        ->where('created_by', $userId)
                        ->select('id', 'name', 'attributes');

                    return $query->paginate($perPage, ['*'], 'page', $page);
                });
            } catch (\Exception $e) {
                Log::error('Failed to retrieve paginated types for user: ' . $e->getMessage());
                return new LengthAwarePaginator([], 0, $perPage, $page); // Return empty paginator in case of error
            }
        });
    }

    public function updateType(int $id, int $userId, array $data): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId, $data) {
                return DB::table('project_types')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->update([
                        'name' => $data['name'],
                        'attributes' => json_encode($data['attributes']), // Assuming attributes need to be stored as JSON
                        'updated_at' => now(),
                    ]) > 0; // Return true if update was successful
            });
        } catch (\Exception $e) {
            Log::error('Failed to update project type: ' . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function deleteType(int $id, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId) {
                return DB::table('project_types')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->delete() > 0; // Return true if deletion was successful
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete project type: ' . $e->getMessage());
            return false; // Return false on error
        }
    }
}
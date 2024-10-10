<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'metadata', 'created_by'];

    protected $casts = [
        'metadata' => 'array',
    ];


    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function createCategoryIfNotExists(string $categoryName, int $userId, ?array $metadata = null): bool
    {
        try {

            return DB::transaction(function () use ($categoryName, $userId, $metadata) {

                $exists = DB::table('project_categories')
                    ->where('name', $categoryName)
                    ->where('created_by', $userId)
                    ->exists();

                if (!$exists) {
                    DB::table('project_categories')->insert([
                        'name' => $categoryName,
                        'metadata' => json_encode($metadata),
                        'created_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    return true;
                }

                return false;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage());
            return false;
        }
    }

    public function getPaginatedCategoriesForUser(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return Cache::flexible("categories_for_user_{$userId}_page_{$page}", [60, 120], function () use ($userId, $page, $perPage): LengthAwarePaginator {
            try {
                return DB::transaction(function () use ($userId, $page, $perPage) {
                    $query = DB::table('project_categories')
                        ->where('created_by', $userId)
                        ->select('id', 'name');

                    return $query->paginate($perPage, ['*'], 'page', $page);
                });
            } catch (\Exception $e) {
                Log::error('Failed to retrieve paginated categories for user: ' . $e->getMessage());
                return new LengthAwarePaginator([], 0, $perPage, $page); // Return empty paginator in case of error
            }
        });
    }

    public function updateCategory(int $id, int $userId, array $data): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId, $data) {
                return DB::table('project_categories')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->update([
                        'name' => $data['name'],
                        'updated_at' => now(),
                    ]) > 0;
            });
        } catch (\Exception $e) {
            Log::error('Failed to update category: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory(int $id, int $userId): bool
    {
        try {
            return DB::transaction(function () use ($id, $userId) {
                return DB::table('project_categories')
                    ->where('id', $id)
                    ->where('created_by', $userId)
                    ->delete() > 0;
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete category: ' . $e->getMessage());
            return false;
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

}

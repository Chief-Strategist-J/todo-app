<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'attributes','created_by'];

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
}

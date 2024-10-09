<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectPhase extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'details','created_by'];

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
                    // Insert the new phase
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
}

<?php

namespace Database\Seeders;

use App\Models\ProjectPhase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectPhaseSeeder extends Seeder
{
    public function run()
    {
        $phases = [
            ['name' => 'Initiation'],
            ['name' => 'Planning'],
            ['name' => 'Execution'],
            ['name' => 'Monitoring & Controlling'],
            ['name' => 'Closure'],
        ];
        
        foreach ($phases as $phase) {
            ProjectPhase::updateOrCreate(
                ['name' => $phase['name']], // Condition to find the record
                $phase                      // Attributes to update or create
            );
        }
        
    }
}

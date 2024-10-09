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
            ['name' => 'Initiation','created_by' => 1],
            ['name' => 'Planning','created_by' => 1],
            ['name' => 'Execution','created_by' => 1],
            ['name' => 'Monitoring & Controlling','created_by' => 1],
            ['name' => 'Closure','created_by' => 1],
        ];
        
        foreach ($phases as $phase) {
            ProjectPhase::updateOrCreate(
                ['name' => $phase['name']], // Condition to find the record
                $phase                      // Attributes to update or create
            );
        }
        
    }
}

<?php

namespace Database\Seeders;

use App\Models\ProjectPriority;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectPrioritySeeder extends Seeder
{
    public function run()
    {
        $priorities = [
            ['name' => 'Low','created_by' => 1],
            ['name' => 'Medium','created_by' => 1],
            ['name' => 'High','created_by' => 1],
            ['name' => 'Urgent','created_by' => 1],
        ];
        
        foreach ($priorities as $priority) {
            ProjectPriority::updateOrCreate(
                ['name' => $priority['name']], // Condition to find the record
                $priority                      // Attributes to update or create
            );
        }
        
    }
}

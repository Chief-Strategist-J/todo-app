<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'Pending','created_by' => 1],
            ['name' => 'In Progress','created_by' => 1],
            ['name' => 'On Hold','created_by' => 1],
            ['name' => 'Completed','created_by' => 1],
            ['name' => 'Cancelled','created_by' => 1],
        ];
        
        foreach ($statuses as $status) {
            ProjectStatus::updateOrCreate(
                ['name' => $status['name']], // Condition to find the record
                $status                      // Attributes to update or create
            );
        }
        
    }
}

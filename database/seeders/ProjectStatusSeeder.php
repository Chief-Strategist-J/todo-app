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
            ['name' => 'Pending'],
            ['name' => 'In Progress'],
            ['name' => 'On Hold'],
            ['name' => 'Completed'],
            ['name' => 'Cancelled'],
        ];
        
        foreach ($statuses as $status) {
            ProjectStatus::updateOrCreate(
                ['name' => $status['name']], // Condition to find the record
                $status                      // Attributes to update or create
            );
        }
        
    }
}

<?php

namespace Database\Seeders;

use App\Models\ProjectType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Internal','created_by' => 1],
            ['name' => 'External','created_by' => 1],
            ['name' => 'Client-based','created_by' => 1],
            ['name' => 'Research','created_by' => 1],
        ];
        
        foreach ($types as $type) {
            ProjectType::updateOrCreate(
                ['name' => $type['name']], // Condition to find the record
                $type                      // Attributes to update or create
            );
        }
        
    }
}

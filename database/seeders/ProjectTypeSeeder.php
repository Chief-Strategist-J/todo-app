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
            ['name' => 'Internal'],
            ['name' => 'External'],
            ['name' => 'Client-based'],
            ['name' => 'Research'],
        ];
        
        foreach ($types as $type) {
            ProjectType::updateOrCreate(
                ['name' => $type['name']], // Condition to find the record
                $type                      // Attributes to update or create
            );
        }
        
    }
}

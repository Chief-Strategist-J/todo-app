<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Software Development'],
            ['name' => 'Marketing Campaign'],
            ['name' => 'Design'],
            ['name' => 'Research'],
            ['name' => 'Consulting'],
            ['name' => 'Product Launch'],
            ['name' => 'Event Planning'],
            ['name' => 'Financial Audit'],
            ['name' => 'Business Strategy'],
            ['name' => 'Customer Support Improvement'],
            ['name' => 'Data Analysis'],
            ['name' => 'Quality Assurance'],
            ['name' => 'Training & Development'],
            ['name' => 'IT Infrastructure'],
            ['name' => 'Digital Transformation'],
            ['name' => 'Brand Development'],
            ['name' => 'Customer Relationship Management'],
            ['name' => 'Human Resources'],
            ['name' => 'Procurement & Supply Chain'],
            ['name' => 'Sustainability & Compliance'],
        ];

        foreach ($categories as $category) {
            ProjectCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

    }

}

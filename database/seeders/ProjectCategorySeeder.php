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
            ['name' => 'Software Development', 'created_by' => 1],
            ['name' => 'Marketing Campaign', 'created_by' => 1],
            ['name' => 'Design', 'created_by' => 1],
            ['name' => 'Research', 'created_by' => 1],
            ['name' => 'Consulting', 'created_by' => 1],
            ['name' => 'Product Launch', 'created_by' => 1],
            ['name' => 'Event Planning', 'created_by' => 1],
            ['name' => 'Financial Audit', 'created_by' => 1],
            ['name' => 'Business Strategy', 'created_by' => 1],
            ['name' => 'Customer Support Improvement', 'created_by' => 1],
            ['name' => 'Data Analysis', 'created_by' => 1],
            ['name' => 'Quality Assurance', 'created_by' => 1],
            ['name' => 'Training & Development', 'created_by' => 1],
            ['name' => 'IT Infrastructure', 'created_by' => 1],
            ['name' => 'Digital Transformation', 'created_by' => 1],
            ['name' => 'Brand Development', 'created_by' => 1],
            ['name' => 'Customer Relationship Management', 'created_by' => 1],
            ['name' => 'Human Resources', 'created_by' => 1],
            ['name' => 'Procurement & Supply Chain', 'created_by' => 1],
            ['name' => 'Sustainability & Compliance', 'created_by' => 1],
        ];

        foreach ($categories as $category) {
            ProjectCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

    }

}

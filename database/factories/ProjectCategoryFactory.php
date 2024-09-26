<?php

namespace Database\Factories;

use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProjectCategoryFactory extends Factory
{
    protected $model = ProjectCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Software Development', 'Marketing Campaign', 'Design', 'Research', 'Consulting']),
            'metadata' => [
                'description' => $this->faker->sentence(),
                'related_tags' => $this->faker->words(3, true), // Example additional metadata
            ],
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\ProjectPriority;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProjectPriorityFactory extends Factory
{
    protected $model = ProjectPriority::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Low', 'Medium', 'High', 'Urgent']),
            'settings' => [
                'color' => $this->faker->hexColor(), // Example of a setting
                'urgency_level' => $this->faker->numberBetween(1, 10), // Additional priority setting
            ],
        ];
    }
}

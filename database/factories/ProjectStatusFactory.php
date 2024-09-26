<?php

namespace Database\Factories;

use App\Models\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProjectStatusFactory extends Factory
{
    protected $model = ProjectStatus::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Pending', 'In Progress', 'On Hold', 'Completed', 'Cancelled']),
            'indicators' => [
                'progress_color' => $this->faker->hexColor(), // Color representation of the status
                'completion_threshold' => $this->faker->numberBetween(0, 100),
            ],
        ];
    }
}

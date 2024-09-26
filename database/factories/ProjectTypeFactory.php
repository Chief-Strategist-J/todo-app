<?php

namespace Database\Factories;

use App\Models\ProjectType;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProjectTypeFactory extends Factory
{
    protected $model = ProjectType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Internal', 'External', 'Client-based', 'Research']),
            'attributes' => [
                'is_billable' => $this->faker->boolean(),
                'time_tracking' => $this->faker->boolean(),
            ],
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\ProjectPhase;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProjectPhaseFactory extends Factory
{
    protected $model = ProjectPhase::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Initiation', 'Planning', 'Execution', 'Monitoring & Controlling', 'Closure']),
            'details' => [
                'phase_goal' => $this->faker->sentence(),
                'expected_duration_days' => $this->faker->numberBetween(5, 100),
            ],
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Pomodoro;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pomodoro>
 */
class PomodoroFactory extends Factory
{
    protected $model = Pomodoro::class;

    public function definition()
    {
        return [
            'uuid' => Str::uuid(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'duration' => $this->faker->numberBetween(1, 60),
            'status' => 'pending',
            'start_time' => null,
            'end_time' => null,
            'metadata' => json_encode(['priority' => $this->faker->word]),
            'priority' => $this->faker->word,
            'tags' => $this->faker->word,
            'is_completed' => false,
            'is_archived' => false,
            'todo_id' => Todo::factory(),
            'user_id' => User::factory(),
            'project_id' => null,
        ];
    }
}

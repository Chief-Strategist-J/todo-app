<?php

namespace Database\Factories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3, true), // Title can be a longer sentence
            'description' => $this->faker->text(200), // Limit description to 200 characters
            'is_completed' => $this->faker->boolean(),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'start_time' => $this->faker->dateTimeBetween('now', '+1 week'),
            'end_time' => $this->faker->dateTimeBetween('now', '+1 month'),
            'date' => $this->faker->dateTime(),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'tags' => $this->faker->words(3, true),
            'status' => $this->faker->randomElement(['pending', 'in progress', 'completed']),
            'reminder' => $this->faker->dateTimeBetween('now', '+1 month'),
            'attachment' => null, // You can add logic to generate a filename if needed
            'category' => $this->faker->word(),
            'estimated_time' => $this->faker->numberBetween(30, 120),
            'actual_time' => $this->faker->numberBetween(0, 120),
            'location' => $this->faker->address(),
            'recurring' => $this->faker->boolean(),
            'recurring_frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'notes' => $this->faker->text(200), // Limit notes to 200 characters
            'completed_at' => $this->faker->boolean() ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'color_code' => $this->faker->hexColor(),
            'is_archived' => $this->faker->boolean(),
            'firebase_todo_id' => $this->faker->uuid(),
            'created_by' => null, // Assuming you will set these relations later
            'updated_by' => null,
            'assigned_to' => null,
            'parent_id' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'is_active' => $this->faker->boolean(),
            'order' => $this->faker->numberBetween(0, 100),
            'version' => $this->faker->numberBetween(1, 10),
            'follower_count' => $this->faker->numberBetween(0, 1000),
            'usage_count' => $this->faker->numberBetween(0, 500),
            'related_posts_count' => $this->faker->numberBetween(0, 300),
            'user_interaction_count' => $this->faker->numberBetween(0, 200),
            'popularity_score' => $this->faker->randomFloat(2, 0, 100),
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
            'meta_title' => $this->faker->sentence(3),
            'color' => $this->faker->hexColor(),
            'image_url' => $this->faker->imageUrl(),
            'tag_type' => $this->faker->word(),
            'content_type' => $this->faker->word(),
            'description_vector' => null,
            'meta_description' => $this->faker->sentence(10),
            'description' => $this->faker->sentence(15),
            'geolocation_data' => null,
            'meta_data' => null,
            'created_by' => User::factory(), // Ensure that created_by is populated with a User
            'parent_id' => null,
            'last_trend_update' => $this->faker->optional()->dateTime(),
            'last_used_at' => $this->faker->optional()->dateTime(),
        ];
    }
}

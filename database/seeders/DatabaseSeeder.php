<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::updateOrCreate(
            ['id' => 1], // Condition to check if the user with ID 1 exists
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'), // Use a hashed password
            ]
        );

        $this->call([
            TodoSeeder::class,
            TagSeeder::class,
            ProjectCategorySeeder::class,
            ProjectPrioritySeeder::class,
            ProjectTypeSeeder::class,
            ProjectPhaseSeeder::class,
            ProjectStatusSeeder::class,
        ]);
    }
}

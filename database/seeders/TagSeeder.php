<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::updateOrCreate(
            ['id' => 1], // Condition to check if the user with ID 1 exists
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'), // Use a hashed password
            ]
        );
        
        $tags = [
            ['name' => 'Urgent', 'slug' => Str::slug('Urgent')],
            ['name' => 'Personal', 'slug' => Str::slug('Personal')],
            ['name' => 'Work', 'slug' => Str::slug('Work')],
            ['name' => 'Home', 'slug' => Str::slug('Home')],
            ['name' => 'Important', 'slug' => Str::slug('Important')],
            ['name' => 'Design', 'slug' => Str::slug('Design')],
            ['name' => 'Research', 'slug' => Str::slug('Research')],
            ['name' => 'Productive', 'slug' => Str::slug('Productive')],
        ];

        foreach ($tags as $tag) {
            DB::table('tags')->updateOrInsert(
                ['name' => $tag['name']], // Condition to check if the tag already exists
                [
                    'uuid' => Str::uuid(),
                    'slug' => $tag['slug'],
                    'created_by' => 1, // Assuming admin user with ID 1 is creating these tags
                    'is_active' => true,
                    'order' => 0,
                    'version' => 1,
                    'follower_count' => 0,
                    'usage_count' => 0,
                    'related_posts_count' => 0,
                    'user_interaction_count' => 0,
                    'popularity_score' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

    }

}

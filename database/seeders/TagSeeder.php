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
            ['name' => 'Urgent', 'slug' => Str::slug('Urgent'),'color'=>'0xFFFF0000'],
            ['name' => 'Personal', 'slug' => Str::slug('Personal'),'color'=>'0xFF98FB98'],
            ['name' => 'Work', 'slug' => Str::slug('Work'),'color'=>'0xFF40E0D0'],
            ['name' => 'Home', 'slug' => Str::slug('Home'),'color'=>'0xFF87CEFA'],
            ['name' => 'Important', 'slug' => Str::slug('Important'),'color'=>'0xFFFF7F50'],
            ['name' => 'Design', 'slug' => Str::slug('Design'),'color'=>'0xFF00FA9A'],
            ['name' => 'Research', 'slug' => Str::slug('Research'),'color'=>'0xFF800000'],
            ['name' => 'Productive', 'slug' => Str::slug('Productive'),'color'=>'0xFF9400D3'],
        ];

        foreach ($tags as $tag) {
            DB::table('tags')->updateOrInsert(
                ['name' => $tag['name']], // Condition to check if the tag already exists
                [
                    'uuid' => Str::uuid(),
                    'slug' => $tag['slug'],
                    'created_by' => 1, // Assuming admin user with ID 1 is creating these tags
                    'color' => $tag['color'], // Assuming admin user with ID 1 is creating these tags
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

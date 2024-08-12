<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->index(); // UUID for tracking
                
                $table->boolean('is_active')->default(true)->index(); // Is the tag active
                $table->integer('order')->default(0)->index(); // Display order
                $table->integer('version')->default(1)->index(); // Version for auditing
                $table->integer('follower_count')->default(0)->index(); // Number of followers for the tag
                $table->integer('usage_count')->default(0)->index(); // Number of times the tag has been used
                $table->integer('related_posts_count')->default(0)->index(); // Count of related posts
                $table->integer('user_interaction_count')->default(0)->index(); // User interactions count
                
                $table->decimal('popularity_score', 8, 2)->default(0)->index(); // Popularity score
                
                $table->string('name')->unique()->index(); // Tag name
                $table->string('slug')->unique()->index(); // Slug for URL
                $table->string('meta_title')->nullable()->index(); // SEO meta title
                $table->string('color')->nullable()->index(); // Optional color code
                $table->string('image_url')->nullable()->index(); // URL for an image associated with the tag
                $table->string('tag_type')->nullable()->index(); // Type/category of tag
                $table->string('content_type')->nullable()->index(); // Type of content
                
                $table->string('description_vector')->nullable()->index(); // For ML representation
                $table->string('meta_description')->nullable()->index(); // SEO meta description
                $table->string('description')->nullable()->index(); // Optional description
                
                $table->json('geolocation_data')->nullable(); // Geolocation data
                $table->json('meta_data')->nullable(); // Metadata
                
                $table->softDeletes(); // Soft delete timestamp
                
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->index(); // Foreign key for user
                
                $table->foreignId('parent_id')->nullable()
                ->constrained('tags', 'id') // Specify the reference table and column
                ->name('fk_tags_parent_id') // Explicitly name the foreign key constraint
                ->onDelete('cascade') // Cascade delete on parent
                ->index(); // Index for faster lookups

                $table->foreignId('todo_id')->nullable()
                ->constrained('todos')
                ->onDelete('cascade')
                ->name('fk_tags_todo_id')
                ->index();


                $table->timestamp('last_trend_update')->nullable()->index(); // Last trend update
                $table->timestamp('last_used_at')->nullable()->index(); // Last time the tag was used
                $table->timestamps(); // Created at and updated at

                // Additional composite indexes for common queries
                $table->index(['created_by', 'is_active']); // Composite index for queries on created_by and is_active
                $table->index(['parent_id', 'is_active']); // Composite index for queries on parent_id and is_active
            });
        }   

        
        public function down(): void
        {
            Schema::dropIfExists('tags');
        }
    };

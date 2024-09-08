<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            
            $table->string('title')->index('idx_post_title');
            $table->string('content')->nullable()->index('idx_post_content');
            $table->string('status')->default('draft')->index('idx_post_status'); // Status: draft, published, etc.
            $table->string('slug')->unique()->index('idx_post_slug');
            
            // Additional details
            $table->json('metadata')->nullable(); // Additional metadata in JSON format
            
            // Generated columns from JSON fields
            $table->string('metadata_author')->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.author"))')->index('idx_post_metadata_author');
            $table->string('metadata_category')->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.category"))')->index('idx_post_metadata_category');
            
            // Timestamps
            $table->dateTime('published_at')->nullable()->index('idx_post_published_at');
            $table->timestamps();
            $table->softDeletes();
        
            // Main fields
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index('idx_post_user');
        
            // Composite indexes
            $table->index(['status', 'published_at'], 'idx_status_published_at'); // Composite index for status and publication date
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

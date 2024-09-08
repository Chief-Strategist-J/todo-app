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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            
            
            $table->string('content')->index('idx_comment_content');
            $table->string('status')->default('pending')->index('idx_comment_status'); // Status: pending, approved, etc.
            
            // Additional details
            $table->json('metadata')->nullable(); // Additional metadata in JSON format
        
            // Generated columns from JSON fields
            $table->string('metadata_approved_by')->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.approved_by"))')->index('idx_comment_metadata_approved_by');
            
            // Timestamps
            $table->dateTime('approved_at')->nullable()->index('idx_comment_approved_at');
            $table->timestamps();
            $table->softDeletes();
            
            // Main fields
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->index('idx_comment_post');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index('idx_comment_user');
        
            // Composite indexes
            $table->index(['post_id', 'status'], 'idx_post_status'); // Composite index for post and status
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};

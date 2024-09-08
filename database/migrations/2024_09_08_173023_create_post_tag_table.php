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
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->index('idx_post_tag_post');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade')->index('idx_post_tag_tag');
        
            // Timestamps
            $table->timestamps();
        
            // Unique constraint
            $table->unique(['post_id', 'tag_id'], 'post_tag_unique');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};

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
        Schema::create('comment_tag', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade')->index('idx_comment_tag_comment');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade')->index('idx_comment_tag_tag');
        
            // Timestamps
            $table->timestamps();
        
            // Unique constraint
            $table->unique(['comment_id', 'tag_id'], 'comment_tag_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_tag');
    }
};

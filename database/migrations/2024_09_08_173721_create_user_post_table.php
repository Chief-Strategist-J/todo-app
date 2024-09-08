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
        Schema::create('user_post', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index('idx_user_post_user');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->index('idx_user_post_post');
        
            // Additional fields
            $table->string('interaction')->default('liked')->index('idx_user_post_interaction'); // e.g., liked, followed
            $table->dateTime('interacted_at')->nullable()->index('idx_user_post_interacted_at');
        
            // Timestamps
            $table->timestamps();
        
            // Unique constraint
            $table->unique(['user_id', 'post_id'], 'user_post_unique');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_post');
    }
};

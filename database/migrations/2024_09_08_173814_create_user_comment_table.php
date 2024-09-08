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
        Schema::create('user_comment', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index('idx_user_comment_user');
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade')->index('idx_user_comment_comment');
        
            // Additional fields
            $table->string('interaction')->default('liked')->index('idx_user_comment_interaction'); // e.g., liked, flagged
            $table->dateTime('interacted_at')->nullable()->index('idx_user_comment_interacted_at');
        
            // Timestamps
            $table->timestamps();
        
            // Unique constraint
            $table->unique(['user_id', 'comment_id'], 'user_comment_unique');
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_comment');
    }
};

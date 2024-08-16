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
        Schema::create('project_feedback', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_feedback_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_feedback_user_id'); // Unique name for the foreign key constraint
        
            $table->string('feedback_text'); // Changed from text to string
            $table->integer('rating')->nullable()->index();
            $table->string('feedback_type')->default('general')->index(); // e.g., 'general', 'milestone', 'deliverable'
            $table->foreignId('related_item_id')->nullable()->index(); // Index related item for quick searches
            $table->string('related_item_type')->nullable()->index(); // Index related item type for quick searches
            $table->boolean('is_anonymous')->default(false)->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'user_id', 'feedback_text'], 'uniq_feedback_per_user_project');
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'feedback_type', 'rating'], 'idx_proj_feedback_type_rating');
            $table->index(['project_id', 'user_id', 'rating'], 'idx_proj_user_rating');
            $table->index(['user_id', 'feedback_type', 'rating'], 'idx_user_feedback_type_rating');
            $table->index(['project_id', 'related_item_id', 'feedback_type'], 'idx_proj_related_item_feedback_type');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'user_id', 'feedback_type'], 'idx_feedback_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_feedback');
    }
};

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
        Schema::create('project_knowledge_transfers', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('from_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_knowledge_transfer_from_project_id');
        
            $table->foreignId('to_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_knowledge_transfer_to_project_id');
        
            $table->string('knowledge_area')->index();
            $table->string('description'); // Changed to string from text
            $table->string('transfer_method')->index(); // e.g., 'documentation', 'training', 'mentoring'
            $table->date('transfer_date')->index();
            $table->string('status')->default('planned')->index(); // planned, in_progress, completed
            $table->foreignId('responsible_user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->name('fk_knowledge_transfer_responsible_user_id');
            $table->json('resources')->nullable();
            $table->string('feedback')->nullable(); // Changed to string from text
            $table->integer('effectiveness_rating')->nullable()->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['from_project_id', 'to_project_id', 'knowledge_area', 'transfer_date'], 'uniq_knowledge_transfer_proj_area_date');
        
            // Composite Indexes with Shortened Names
            $table->index(['from_project_id', 'knowledge_area', 'status', 'transfer_date'], 'idx_knowledge_transfer_from_proj_area_status_date');
            $table->index(['to_project_id', 'knowledge_area', 'status', 'transfer_date'], 'idx_knowledge_transfer_to_proj_area_status_date');
            $table->index(['transfer_method', 'status', 'transfer_date'], 'idx_knowledge_transfer_method_status_date');
            $table->index(['effectiveness_rating', 'status'], 'idx_knowledge_transfer_rating_status');
            $table->index(['from_project_id', 'to_project_id', 'transfer_date'], 'idx_knowledge_transfer_proj_pair_date');
            $table->index(['knowledge_area', 'transfer_date'], 'idx_knowledge_transfer_area_date');
        
            // Generated Columns and Indexes for JSON Fields
            // Example: Extracting the first resource link from 'resources'
            $table->string('resource_link')->nullable(); // Example generated column for a key in JSON
        
            $table->index('resource_link', 'idx_knowledge_transfer_resource_link');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_knowledge_transfers');
    }
};

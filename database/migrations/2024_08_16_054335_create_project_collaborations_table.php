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
        Schema::create('project_collaborations', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('primary_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_collaborations_primary_project_id');
        
            $table->foreignId('secondary_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_collaborations_secondary_project_id');
        
            $table->string('collaboration_type')->index(); // Index for quick searches on type
            $table->string('description')->nullable(); // Changed to string from text
            $table->date('start_date')->index();
            $table->date('end_date')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->json('shared_resources')->nullable();
            $table->json('goals')->nullable();
            $table->foreignId('primary_contact_id')->nullable()->constrained('users')->onDelete('set null')->name('fk_collaborations_primary_contact_id');
            $table->foreignId('secondary_contact_id')->nullable()->constrained('users')->onDelete('set null')->name('fk_collaborations_secondary_contact_id');
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['primary_project_id', 'secondary_project_id', 'collaboration_type'], 'uniq_collaborations_proj_type');
        
            // Composite Indexes with Shortened Names
            $table->index(['primary_project_id', 'status'], 'idx_collaborations_primary_proj_status');
            $table->index(['secondary_project_id', 'status'], 'idx_collaborations_secondary_proj_status');
            $table->index(['collaboration_type', 'start_date'], 'idx_collaborations_type_start_date');
            $table->index(['status', 'start_date'], 'idx_collaborations_status_start_date');
            $table->index(['primary_project_id', 'secondary_project_id', 'start_date'], 'idx_collaborations_proj_pair_start_date');
            $table->index(['primary_project_id', 'end_date'], 'idx_collaborations_primary_proj_end_date');
            $table->index(['secondary_project_id', 'end_date'], 'idx_collaborations_secondary_proj_end_date');
            $table->index(['start_date', 'end_date'], 'idx_collaborations_date_range');
        
            // Generated Columns and Indexes for JSON Fields
            $table->string('shared_resources_key')->nullable(); // Example generated column for key in JSON
            $table->string('goals_key')->nullable(); // Example generated column for key in JSON
        
            $table->index('shared_resources_key', 'idx_collaborations_shared_resources_key');
            $table->index('goals_key', 'idx_collaborations_goals_key');
        });
        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_collaborations');
    }
};

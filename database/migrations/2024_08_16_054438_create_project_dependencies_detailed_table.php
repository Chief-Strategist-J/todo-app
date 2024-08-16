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
        Schema::create('project_dependencies_detailed', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key Constraints with Unique Names
            $table->foreignId('dependent_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_dep_dtl_dependent_project'); // Unique name for the foreign key constraint
            
            $table->foreignId('dependency_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_dep_dtl_dependency_project'); // Unique name for the foreign key constraint
            
            $table->string('dependency_type')->index(); // Index name for quick searches
            $table->integer('lag_time')->default(0); // in days
            $table->string('description')->nullable(); // Changed from text to string
            $table->string('status')->default('active')->index(); // Index name for quick searches
            
            // JSON Fields
            $table->json('affected_milestones')->nullable();
            $table->json('risk_assessment')->nullable();
            
            // Foreign Key with Unique Name
            $table->foreignId('responsible_user_id')->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->name('fk_dep_dtl_responsible_user'); // Unique name for the foreign key constraint
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique Constraint
            $table->unique(['dependent_project_id', 'dependency_project_id', 'dependency_type'], 'uniq_dep_proj_type'); // Ensure unique dependency type
            
            // Composite Indexes with Shortened Names
            $table->index(['dependent_project_id', 'status'], 'idx_dep_proj_status_dep');
            $table->index(['dependency_project_id', 'status'], 'idx_dep_proj_status_dep_proj');
            $table->index(['dependency_type', 'lag_time'], 'idx_dep_type_lag_time');
            $table->index(['status', 'lag_time'], 'idx_status_lag_time');
            $table->index(['dependent_project_id', 'dependency_project_id', 'lag_time'], 'idx_dep_dep_lag_time');
            $table->index(['dependent_project_id', 'status', 'lag_time'], 'idx_dep_status_lag_time');
            $table->index(['dependency_project_id', 'status', 'lag_time'], 'idx_dep_proj_status_lag_time');
            $table->index(['lag_time', 'status'], 'idx_lag_time_status');
            
            // Generated Columns for JSON Fields
            $table->string('affected_milestones_0')->nullable()->storedAs('JSON_UNQUOTE(JSON_EXTRACT(affected_milestones, "$[0]"))')->index();
            $table->string('risk_assessment_0')->nullable()->storedAs('JSON_UNQUOTE(JSON_EXTRACT(risk_assessment, "$[0]"))')->index();
            // Add more generated columns as needed based on your JSON structure
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_dependencies_detailed');
    }
};

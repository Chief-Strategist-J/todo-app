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
        Schema::create('project_dependencies', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_dependencies_project_id'); // Unique name for the foreign key constraint
            
            $table->foreignId('dependent_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_dependencies_dependent_project_id'); // Unique name for the foreign key constraint
            
            $table->string('dependency_type')->default('finish_to_start')->index();
            $table->integer('lag_time')->default(0)->index(); // in days
            
            // Change 'description' from text to string
            $table->string('description')->nullable();
            
            $table->string('status')->default('active')->index();
            
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'dependent_project_id'], 'unique_project_dependent');
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'dependency_type', 'status'], 'idx_proj_dep_type_status');
            $table->index(['dependent_project_id', 'status'], 'idx_dep_proj_status');
            $table->index(['project_id', 'lag_time', 'status'], 'idx_proj_lag_status');
            $table->index(['dependency_type', 'lag_time'], 'idx_dep_type_lag');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'dependent_project_id', 'dependency_type'], 'idx_dependencies_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_dependencies');
    }
};

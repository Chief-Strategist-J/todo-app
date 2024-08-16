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
        Schema::create('project_deliverable_dependencies', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('dependent_deliverable_id')
                  ->constrained('project_deliverables')
                  ->onDelete('cascade')
                  ->name('fk_dependent_deliverable_id');
            
            $table->foreignId('dependency_deliverable_id')
                  ->constrained('project_deliverables')
                  ->onDelete('cascade')
                  ->name('fk_dependency_deliverable_id');
            
            $table->string('dependency_type')->index();
            $table->integer('lag_time')->default(0)->index(); // in days
            $table->string('description')->nullable(); // Changed to string
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['dependent_deliverable_id', 'dependency_deliverable_id'], 'uniq_dependent_dependency');
        
            // Composite Indexes with Shortened Names
            $table->index(['dependent_deliverable_id', 'dependency_type', 'lag_time'], 'idx_dependent_type_lag');
            $table->index(['dependency_deliverable_id', 'dependency_type', 'lag_time'], 'idx_dependency_type_lag');
            $table->index(['dependency_type', 'lag_time'], 'idx_type_lag');
            $table->index(['dependent_deliverable_id', 'lag_time'], 'idx_dependent_lag');
            $table->index(['dependency_deliverable_id', 'lag_time'], 'idx_dependency_lag');
            $table->index(['dependency_type', 'dependent_deliverable_id'], 'idx_type_dependent');
            $table->index(['lag_time', 'description'], 'idx_lag_description');
        });
        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_deliverable_dependencies');
    }
};

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
        Schema::create('project_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_risk_assessments_project_id'); // Unique name for the foreign key constraint
        
            $table->string('risk_type')->index();
            $table->string('description'); // Changed from text to string
            $table->string('probability')->index();
            $table->string('impact')->index();
            $table->decimal('risk_score', 5, 2)->index();
            $table->string('mitigation_strategy')->nullable(); // Changed from text to string
            $table->string('status')->default('open')->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'risk_type', 'risk_score'], 'idx_project_risk_type_score');
            $table->index(['project_id', 'status'], 'idx_project_status');
            $table->index(['probability', 'impact'], 'idx_probability_impact');
            $table->index(['project_id', 'risk_type', 'probability', 'impact'], 'idx_project_risk_type_prob_impact');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'risk_type', 'risk_score'], 'idx_risk_assessments_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_risk_assessments');
    }
};

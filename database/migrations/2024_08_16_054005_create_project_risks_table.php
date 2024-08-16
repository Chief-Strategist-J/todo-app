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
        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_risks_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->name('fk_risks_assigned_to'); // Unique name for the foreign key constraint
        
            $table->string('title')->index(); // Risk title
            $table->string('description'); // Changed from text to string
            $table->string('category')->index(); // Risk category
            $table->string('probability')->index(); // Risk probability
            $table->string('impact')->index(); // Risk impact
            $table->decimal('risk_score', 5, 2)->index(); // Risk score
            $table->string('mitigation_strategy')->nullable(); // Changed from text to string
            $table->string('status')->default('identified')->index(); // Risk status
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'title'], 'uniq_risks_project_title'); // Ensure unique risk titles within a project
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'category', 'risk_score'], 'idx_risks_proj_cat_score');
            $table->index(['project_id', 'status'], 'idx_risks_proj_status');
            $table->index(['risk_score', 'probability', 'impact'], 'idx_risks_score_prob_impact');
            $table->index(['category', 'status'], 'idx_risks_cat_status');
            $table->index(['probability', 'impact'], 'idx_risks_prob_impact');
            $table->index(['risk_score', 'status'], 'idx_risks_score_status');
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_risks');
    }
};

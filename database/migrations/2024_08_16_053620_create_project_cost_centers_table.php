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
        Schema::create('project_cost_centers', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_cost_centers_project_id'); // Unique name for the foreign key constraint
        
            $table->string('name')->index(); // Cost center name
        
            $table->string('description')->nullable(); // Changed from text to string
            $table->decimal('budget', 15, 2)->default(0.00)->index(); // Budget
            $table->decimal('actual_cost', 15, 2)->default(0.00)->index(); // Actual cost
            $table->string('status')->default('active')->index(); // Status
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'name'], 'uniq_cost_centers_name'); // Unique cost center name per project
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'status', 'budget', 'actual_cost'], 'idx_cost_centers_proj_status_budget_cost');
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_cost_centers');
    }
};

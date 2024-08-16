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
        Schema::create('project_workflows', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_workflows_project_id'); // Unique name for the foreign key constraint
            
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->name('fk_workflows_created_by'); // Unique name for the foreign key constraint
            
            $table->string('name')->index(); // Index name for quick searches
            $table->string('description')->nullable(); // Changed from text to string
            $table->json('steps')->nullable(); // JSON field
            $table->boolean('is_active')->default(true)->index();
            $table->integer('usage_count')->default(0)->index();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique Constraint
            $table->unique(['project_id', 'name'], 'uniq_project_workflow_name'); // Ensure unique workflow names per project
            
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'is_active', 'usage_count'], 'idx_proj_active_usage_count');
            $table->index(['project_id', 'created_by', 'is_active'], 'idx_proj_created_active');
            $table->index(['created_by', 'name', 'is_active'], 'idx_created_name_active');
            
            // Hash Indexing for Composite Fields (Note: MySQL does not support hash indexes, so you might not need this)
            // $table->index(['project_id', 'name', 'is_active'], 'idx_workflow_hash')->algorithm('hash');
            
            // Generated Columns for JSON Fields
            $table->string('step_0')->nullable()->storedAs('JSON_UNQUOTE(JSON_EXTRACT(steps, "$[0]"))')->index();
            $table->string('step_1')->nullable()->storedAs('JSON_UNQUOTE(JSON_EXTRACT(steps, "$[1]"))')->index();
            // Add more generated columns as needed based on your JSON structure
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_workflows');
    }
};

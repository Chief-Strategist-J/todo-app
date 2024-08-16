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
        Schema::create('project_sprints', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_sprints_project_id'); // Unique name for the foreign key constraint
        
            $table->string('name')->index(); // Index name for quick searches
            $table->string('goal')->nullable(); // Changed from text to string
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->string('status')->default('planned')->index();
            $table->json('tasks')->nullable(); // JSON field
            $table->decimal('velocity', 8, 2)->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'name'], 'uniq_project_sprint_name'); // Unique name per project
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'status', 'start_date'], 'idx_sprints_proj_status_start');
            $table->index(['project_id', 'end_date'], 'idx_sprints_proj_end_date');
        
            // Hash Indexing for Composite Fields (if needed)
            $table->index(['project_id', 'name'], 'idx_sprints_proj_name')->algorithm('hash');
        
            // Generated Columns for JSON Fields
            $table->string('task_0')->nullable()->storedAs('tasks->>"$[0]"')->index();
            $table->string('task_1')->nullable()->storedAs('tasks->>"$[1]"')->index();
            // Add more generated columns as needed based on your JSON structure
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_sprints');
    }
};

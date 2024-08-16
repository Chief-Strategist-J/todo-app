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
        Schema::create('project_issues', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_issues_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('reported_by')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_issues_reported_by'); // Unique name for the foreign key constraint
        
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->index()
                  ->name('fk_issues_assigned_to'); // Unique name for the foreign key constraint
        
            $table->string('title')->index();
            
            // Changed from text to string
            $table->string('description');
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('open')->index();
            
            $table->date('due_date')->nullable()->index();
            
            // JSON Field with Generated Columns
            $table->json('related_tasks')->nullable();
            $table->string('related_tasks_summary')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(related_tasks, "$.summary"))')
                  ->index();
            $table->string('related_tasks_details')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(related_tasks, "$.details"))')
                  ->index();
        
            // Changed from text to string
            $table->string('resolution')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'priority', 'status'], 'idx_proj_priority_status');
            $table->index(['reported_by', 'assigned_to', 'due_date'], 'idx_reported_assigned_due_date');
            $table->index(['project_id', 'status', 'due_date'], 'idx_proj_status_due_date');
            $table->index(['priority', 'due_date'], 'idx_priority_due_date');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'status', 'priority'], 'idx_issues_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_issues');
    }
};

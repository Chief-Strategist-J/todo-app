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
        Schema::create('project_deliverables', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_project_id');
                  
            $table->string('name')->index();
            $table->string('description')->nullable(); // Changed to string
            $table->date('due_date')->index();
            $table->string('status')->default('pending')->index(); // pending, in_progress, completed, accepted
            $table->foreignId('responsible_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->name('fk_responsible_user_id');
            $table->json('acceptance_criteria')->nullable();
            $table->foreignId('reviewer_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->name('fk_reviewer_id');
            $table->string('feedback')->nullable(); // Changed to string
            $table->date('accepted_date')->nullable()->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'name', 'due_date'], 'uniq_project_name_due_date');
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'status', 'due_date'], 'idx_project_status_due_date');
            $table->index(['status', 'due_date', 'accepted_date'], 'idx_status_due_date_accepted_date');
            $table->index(['responsible_user_id', 'status'], 'idx_responsible_user_status');
            $table->index(['reviewer_id', 'accepted_date'], 'idx_reviewer_accepted_date');
            $table->index(['name', 'status'], 'idx_name_status');
            $table->index(['accepted_date', 'project_id'], 'idx_accepted_date_project_id');
        
            // Generated Columns for JSON Fields
            // Add generated columns if specific JSON values need to be extracted and indexed. Example:
            // $table->string('acceptance_criteria_value')->storedAs('JSON_EXTRACT(acceptance_criteria, "$.value")')->index();
            // $table->string('feedback_value')->storedAs('JSON_EXTRACT(feedback, "$.value")')->index();
        });
        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_deliverables');
    }
};

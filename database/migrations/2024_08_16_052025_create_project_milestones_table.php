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
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_milestones_project_id'); // Unique name for the foreign key constraint
        
            $table->string('name')->index();
            
            // Change 'description' from text to string
            $table->string('description')->nullable();
            
            $table->date('due_date')->index();
            $table->string('status')->default('pending')->index();
            $table->decimal('completion_percentage', 5, 2)->default(0.00)->index();
            
            $table->foreignId('responsible_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->index()
                  ->name('fk_milestones_responsible_user_id'); // Unique name for the foreign key constraint
            
            $table->boolean('is_critical')->default(false)->index();
            
            // Add JSON column
            $table->json('deliverables')->nullable();
            
            // Generated column for indexing JSON deliverables
            $table->string('deliverable_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(deliverables, "$.key"))') // Adjust JSON path according to your structure
                  ->index()
                  ->name('idx_deliverable_key'); // Custom name for the index on generated column
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'due_date', 'status'], 'idx_project_due_date_status');
            $table->index(['project_id', 'completion_percentage'], 'idx_project_completion_percentage');
            $table->index(['status', 'due_date'], 'idx_status_due_date');
            $table->index(['responsible_user_id', 'due_date'], 'idx_responsible_user_due_date');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'name', 'due_date'], 'idx_milestones_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};

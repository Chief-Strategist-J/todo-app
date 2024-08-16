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
        Schema::create('project_todo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_todo_project_id'); // Unique name for the foreign key constraint
            $table->foreignId('todo_id')
                  ->constrained('todos')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_todo_todo_id'); // Unique name for the foreign key constraint
            $table->integer('order')->default(0)->index();
            $table->timestamp('added_at')->useCurrent()->index();
            $table->foreignId('added_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->index()
                  ->name('fk_project_todo_added_by'); // Unique name for the foreign key constraint
            $table->boolean('is_critical_path')->default(false)->index();
        
            // Composite Unique Constraint
            $table->unique(['project_id', 'todo_id'], 'unique_project_todo');
        
            // Additional Indexes with Shortened Names
            $table->index(['project_id', 'order', 'is_critical_path'], 'idx_project_order_critical');
            $table->index(['added_by', 'is_critical_path'], 'idx_added_by_critical');
            $table->index(['todo_id', 'order'], 'idx_todo_order');
        
            // Hash Indexes (if supported)
            $table->index(['project_id', 'todo_id', 'order'], 'idx_project_todo_order_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_todo');
    }
};

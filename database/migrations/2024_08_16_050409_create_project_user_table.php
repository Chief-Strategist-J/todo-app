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
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_user_project_id'); // Unique name for the foreign key constraint
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_user_user_id'); // Unique name for the foreign key constraint
            $table->string('role')->default('member')->index();
            
            // Add JSON columns
            $table->json('permissions')->nullable();
            $table->json('skills_utilized')->nullable();
            
            // Generated column for indexing JSON permissions
            $table->string('permissions_type')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(permissions, "$.type"))')
                  ->index();
            
            // Generated column for indexing JSON skills_utilized
            $table->string('skills_utilized_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(skills_utilized, "$.key"))') // Adjust JSON path according to your structure
                  ->index();
        
            $table->timestamp('joined_at')->useCurrent()->index();
            $table->decimal('contribution_percentage', 5, 2)->default(0.00)->index();
            $table->integer('assigned_tasks_count')->default(0)->index();
            $table->integer('completed_tasks_count')->default(0)->index();
            $table->decimal('performance_score', 5, 2)->default(0.00)->index();
        
            // Composite Unique Constraint
            $table->unique(['project_id', 'user_id']);
        
            // Additional Indexes
            $table->index(['project_id', 'role', 'performance_score']);
        });
               
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};

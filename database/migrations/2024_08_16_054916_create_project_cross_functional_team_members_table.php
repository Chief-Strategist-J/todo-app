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
        Schema::create('project_cross_functional_team_members', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('team_id')
                  ->constrained('project_cross_functional_teams')
                  ->onDelete('cascade')
                  ->name('fk_team_id');
                  
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_project_id');
                  
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->name('fk_user_id');
                  
            $table->string('role')->index();
            $table->decimal('time_allocation', 5, 2)->default(100.00)->index(); // percentage
            $table->date('joined_date')->index();
            $table->date('left_date')->nullable()->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['team_id', 'project_id', 'user_id'], 'uniq_team_project_user');
        
            // Composite Indexes with Shortened Names
            $table->index(['team_id', 'role', 'time_allocation'], 'idx_team_role_time_allocation');
            $table->index(['project_id', 'user_id', 'role'], 'idx_project_user_role');
            $table->index(['time_allocation', 'joined_date'], 'idx_time_allocation_joined_date');
            $table->index(['joined_date', 'left_date'], 'idx_joined_left_dates');
            $table->index(['team_id', 'joined_date'], 'idx_team_joined_date');
            $table->index(['project_id', 'left_date'], 'idx_project_left_date');
            $table->index(['role', 'time_allocation'], 'idx_role_time_allocation');
        });
        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_cross_functional_team_members');
    }
};

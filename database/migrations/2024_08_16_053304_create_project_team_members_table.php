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
        Schema::create('project_team_members', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_team_members_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_team_members_user_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('role_id')
                  ->constrained('project_team_roles')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_team_members_role_id'); // Unique name for the foreign key constraint
        
            $table->date('joined_date')->index();
            $table->date('left_date')->nullable()->index();
            $table->decimal('allocation_percentage', 5, 2)->default(100.00)->index();
            $table->json('skills')->nullable(); // JSON field
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'user_id'], 'uniq_team_member'); // Unique member per project
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'role_id', 'joined_date'], 'idx_team_proj_role_joined');
            $table->index(['project_id', 'allocation_percentage'], 'idx_team_proj_allocation');
        
            // Generated Columns for JSON Fields
            $table->string('skill_0')->nullable()->storedAs('skills->>"$[0]"')->index()->name('idx_skill_0');
            $table->string('skill_1')->nullable()->storedAs('skills->>"$[1]"')->index()->name('idx_skill_1');
            // Add more generated columns as needed based on your JSON structure
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_team_members');
    }
};

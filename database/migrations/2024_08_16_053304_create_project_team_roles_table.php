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
        Schema::create('project_team_roles', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_team_roles_project_id'); // Unique name for the foreign key constraint
        
            $table->string('name')->index(); // Role name
        
            $table->string('description')->nullable(); // Changed from text to string
            $table->json('permissions')->nullable(); // JSON field
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'name'], 'uniq_team_roles_name'); // Unique role name per project
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'description'], 'idx_team_roles_proj_desc');
        
            // Generated Columns for JSON Fields
            $table->string('permission_0')->nullable()->storedAs('permissions->>"$[0]"')->index()->name('idx_permission_0');
            $table->string('permission_1')->nullable()->storedAs('permissions->>"$[1]"')->index()->name('idx_permission_1');
            // Add more generated columns as needed based on your JSON structure
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_team_roles');
    }
};

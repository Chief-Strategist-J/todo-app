<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_stakeholders_project_id'); // Unique name for the foreign key constraint
        
            $table->string('name')->index(); // Considered unique if necessary
            $table->string('role')->index();
            $table->string('influence_level')->index();
            $table->string('interest_level')->index();
            
            // Change 'expectations' from text to string
            $table->string('expectations')->nullable();
            
            // Add JSON column
            $table->json('communication_preferences')->nullable();
        
            // Generated column for indexing JSON communication_preferences
            $table->string('communication_pref_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(communication_preferences, "$.key"))') // Adjust JSON path according to your structure
                  ->index()
                  ->name('idx_communication_pref_key'); // Custom name for the index on generated column
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'role'], 'idx_project_role');
            $table->index(['project_id', 'influence_level', 'interest_level'], 'idx_project_influence_interest');
            $table->index(['project_id', 'name', 'role'], 'idx_project_name_role');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'influence_level', 'interest_level'], 'idx_project_stakeholders_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_stakeholders');
    }
};

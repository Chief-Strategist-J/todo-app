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
        Schema::create('project_cross_functional_teams', function (Blueprint $table) {
            $table->id();
        
            $table->string('team_name')->index(); // Index team name
            $table->string('purpose'); // Changed to string from text
            $table->date('formation_date')->index();
            $table->date('dissolution_date')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->foreignId('team_lead_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->name('fk_team_lead_id');
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['team_name', 'formation_date'], 'uniq_team_name_formation_date');
        
            // Composite Indexes with Shortened Names
            $table->index(['status', 'formation_date'], 'idx_status_formation_date');
            $table->index(['team_lead_id', 'status'], 'idx_team_lead_status');
            $table->index(['team_name', 'status'], 'idx_team_name_status');
            $table->index(['formation_date', 'dissolution_date'], 'idx_formation_dissolution_date');
            $table->index(['status', 'team_lead_id'], 'idx_status_team_lead_id');
        });
        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_cross_functional_teams');
    }
};

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
        Schema::create('project_phase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->index('proj_fk');  // Unique and short foreign key name for project_id
            
            $table->foreignId('phase_id')
                ->constrained('project_phases')
                ->onDelete('cascade')
                ->index('ph_fk');  // Unique and short foreign key name for phase_id

            $table->timestamps();

            // Add unique constraint to prevent duplicate phase assignments to the same project
            $table->unique(['project_id', 'phase_id'], 'proj_ph_unique')->index();  // Unique constraint name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_phase');
    }
};

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
        Schema::create('pomodoro_projects', function (Blueprint $table) {
            $table->id();

            // Foreign keys with unique names
            $table->foreignId('pomodoro_id')
                ->constrained('pomodoros')
                ->onDelete('cascade')
                ->index('idx_pp_pomodoro_id')
                ->name('fk_pp_pomodoro_id');

            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->index('idx_pp_project_id')
                ->name('fk_pp_project_id');

            // Additional fields
            $table->string('association_type')->default('primary')->index('idx_pp_association_type'); // Type of association
            $table->boolean('is_mandatory')->default(false)->index('idx_pp_is_mandatory'); // Indicates if the project association is mandatory

            $table->timestamps();

            // Unique constraint to avoid duplicate associations
            $table->unique(['pomodoro_id', 'project_id'], 'uq_pp_pomodoro_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_projects');
    }
};

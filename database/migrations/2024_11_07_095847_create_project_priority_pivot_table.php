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
        Schema::create('project_priority', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->index()
                ->name('project_id_pk');
            
            $table->foreignId('priority_id')
                ->constrained('project_priorities')
                ->onDelete('cascade')
                ->index()
                ->name('priority_id_pk');
            
            $table->timestamps();

            // Add a unique index to avoid duplicate entries for the same project and priority
            $table->unique(['project_id', 'priority_id'], 'pp_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_priority');
    }
};

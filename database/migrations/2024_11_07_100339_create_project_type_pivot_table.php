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
        Schema::create('project_project_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->index()
                ->name('ppkid');
            
            $table->foreignId('type_id')
                ->constrained('project_types')
                ->onDelete('cascade')
                ->index()
                ->name('pptkid');
            
            $table->timestamps();

            // Add a unique index to avoid duplicate entries for the same project and type
            $table->unique(['project_id', 'type_id'], 'project_type_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_type');
    }
};

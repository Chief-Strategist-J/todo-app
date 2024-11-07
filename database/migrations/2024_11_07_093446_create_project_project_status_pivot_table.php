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
        Schema::create('project_project_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->index('proj_sk');

            $table->foreignId('status_id')
                ->constrained('project_statuses')
                ->onDelete('cascade')
                ->index('status_sk');

            $table->timestamps();


            $table->unique(['project_id', 'status_id'], 'proj_status_unique')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_project_status');
    }
};

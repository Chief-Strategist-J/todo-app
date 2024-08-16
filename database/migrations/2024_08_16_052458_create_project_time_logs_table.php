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
        Schema::create('project_time_logs', function (Blueprint $table) {
            $table->id();

            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->index()
                ->name('fk_time_logs_project_id'); // Unique name for the foreign key constraint

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->index()
                ->name('fk_time_logs_user_id'); // Unique name for the foreign key constraint

            $table->foreignId('task_id')
                ->nullable()
                ->constrained('todos')
                ->onDelete('set null')
                ->index()
                ->name('fk_time_logs_task_id'); // Unique name for the foreign key constraint

            $table->dateTime('start_time')->index();
            $table->dateTime('end_time')->nullable()->index();
            $table->integer('duration')->default(0)->index(); // in minutes

            // Change 'description' from text to string
            $table->string('description')->nullable();

            $table->boolean('is_billable')->default(true)->index();
            $table->string('activity_type')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'user_id', 'start_time'], 'idx_proj_user_start');
            $table->index(['project_id', 'end_time'], 'idx_proj_end_time');
            $table->index(['user_id', 'start_time', 'end_time'], 'idx_user_start_end_time');
            $table->index(['task_id', 'start_time'], 'idx_task_start_time');
            $table->index(['is_billable', 'activity_type'], 'idx_billable_activity');

            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'user_id', 'duration'], 'idx_time_logs_hash')->algorithm('hash');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_time_logs');
    }
};

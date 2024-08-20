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
        Schema::create('pomodoro_users', function (Blueprint $table) {
            $table->id();

            // Foreign keys with unique names
            $table->foreignId('pomodoro_id')
                ->constrained('pomodoros')
                ->onDelete('cascade')
                ->index('idx_pu_pomodoro_id')
                ->name('fk_pu_pomodoro_id');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->index('idx_pu_user_id')
                ->name('fk_pu_user_id');

            $table->foreignId('todo_id')
                ->constrained('todos')
                ->onDelete('cascade')
                ->index('idx_pu_todo_id')
                ->name('fk_pu_todo_id');

            // Additional fields
            $table->integer('assigned_duration')->nullable()->index('idx_pu_assigned_duration'); // Duration assigned to user
            $table->timestamp('completed_at')->nullable()->index('idx_pu_completed_at'); // Timestamp when the Pomodoro was completed
            $table->string('role')->default('participant')->index('idx_pu_role'); // Role of the user in the Pomodoro (e.g., participant, observer)
            $table->boolean('is_active')->default(true)->index('idx_pu_is_active'); // Whether the Pomodoro session is currently active
            $table->boolean('is_completed')->default(false)->index('idx_pu_is_completed'); // Status if the Pomodoro session is completed

            $table->timestamps();

            // Unique constraint to avoid duplicate assignments
            $table->unique(['pomodoro_id', 'user_id', 'todo_id'], 'uq_pu_pomodoro_user_todo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_users');
    }
};

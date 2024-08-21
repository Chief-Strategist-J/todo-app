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
        Schema::create('pomodoro_timers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('pomodoro_id')
                ->constrained('pomodoros')
                ->onDelete('cascade')
                ->index('pomodoro_id_idx');
            
            $table->timestamp('started_at')->nullable()->index('started_at_idx');
            $table->timestamp('completed_at')->nullable()->index('completed_at_idx');
            $table->timestamp('stopped_at')->nullable()->index('stopped_at_idx');
            $table->timestamp('resumed_at')->nullable()->index('resumed_at_idx');
            
            // Duration and status
            $table->integer('segment_duration_seconds')->nullable()->index('segment_duration_seconds_idx');
            $table->string('status')->default('started')->index('status_idx');
            $table->string('user_action')->nullable()->index('user_action_idx');
            
            // Additional fields for machine learning
            $table->float('duration_in_seconds')->nullable()->index('duration_in_seconds_idx'); // Duration in seconds
            $table->float('pause_duration_seconds')->nullable()->index('pause_duration_seconds_idx'); // Total pause time in seconds
            $table->float('active_duration_seconds')->nullable()->index('active_duration_seconds_idx'); // Total active time in seconds
            $table->boolean('is_interrupted')->default(false)->index('is_interrupted_idx'); // Flag to check if interrupted
            $table->integer('number_of_pauses')->default(0)->index('number_of_pauses_idx'); // Number of pauses during the pomodoro
            $table->string('device_used')->nullable()->index('device_used_idx'); // Device used to track the pomodoro
            $table->string('user_feedback')->nullable()->index('user_feedback_idx'); // User feedback about the session
            $table->json('additional_metadata')->nullable(); // JSON field for additional metadata
            $table->float('performance_score')->nullable()->index('performance_score_idx'); // Performance score for analysis
            
            // Default timestamps for Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_timers');
    }
};

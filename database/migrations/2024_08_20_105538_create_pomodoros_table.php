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
        Schema::create('pomodoros', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index('pomodoros_uuid_index'); // UUID for tracking
            $table->string('title')->index('pomodoros_title_index');
            $table->string('description')->nullable()->index('pomodoros_description_index');
            $table->integer('duration')->default(25)->index('pomodoros_duration_index'); // Duration of pomodoro in minutes
            $table->string('status')->default('pending')->index('pomodoros_status_index'); // Status: pending, in progress, completed
            $table->timestamp('start_time')->nullable()->index('pomodoros_start_time_index');
            $table->timestamp('end_time')->nullable()->index('pomodoros_end_time_index');
            $table->json('metadata')->nullable(); // Metadata related to the pomodoro
            $table->string('priority')->default('medium')->index('pomodoros_priority_index');
            $table->string('tags')->nullable()->index('pomodoros_tags_index'); // Tags for categorization
            $table->boolean('is_completed')->default(false)->index('pomodoros_is_completed_index');
            $table->boolean('is_archived')->default(false)->index('pomodoros_is_archived_index');

            // Generated columns for JSON fields
            $table->string('metadata_priority')->virtualAs('json_unquote(json_extract(metadata, "$.priority"))')->index('pomodoros_metadata_priority_index');
            $table->string('metadata_category')->virtualAs('json_unquote(json_extract(metadata, "$.category"))')->index('pomodoros_metadata_category_index');

            // Foreign keys
            $table->unsignedBigInteger('todo_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            
            $table->foreign('todo_id', 'fk_pomodoros_todo_id')
                ->references('id')->on('todos')
                ->onDelete('cascade')
                ->index('pomodoros_todo_id_index');

            $table->foreign('user_id', 'fk_pomodoros_user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->index('pomodoros_user_id_index');

            $table->foreign('project_id', 'fk_pomodoros_project_id')
                ->references('id')->on('projects')
                ->onDelete('cascade')
                ->index('pomodoros_project_id_index');

            $table->softDeletes();
            $table->timestamps();

            // Composite indexes with custom names
            $table->index(['status', 'priority', 'is_archived'], 'status_priority_archived_index');
            $table->index(['start_time', 'end_time', 'duration'], 'start_end_duration_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoros');
    }
};

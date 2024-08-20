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
        Schema::create('pomodoro_tags', function (Blueprint $table) {
            $table->id();

            // Foreign keys with unique names
            $table->foreignId('pomodoro_id')
                ->constrained('pomodoros')
                ->onDelete('cascade')
                ->index('idx_pt_pomodoro_id')
                ->name('fk_pt_pomodoro_id');

            $table->foreignId('tag_id')
                ->constrained('tags')
                ->onDelete('cascade')
                ->index('idx_pt_tag_id')
                ->name('fk_pt_tag_id');

            // Additional fields
            $table->string('tag_type')->default('general')->index('idx_pt_tag_type'); // Type of tag

            $table->timestamps();

            // Unique constraint to avoid duplicate associations
            $table->unique(['pomodoro_id', 'tag_id'], 'uq_pt_pomodoro_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_tags');
    }
};

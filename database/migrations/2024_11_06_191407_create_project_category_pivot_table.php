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
        Schema::create('project_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade')->index('proj_id');
            $table->foreignId('category_id')->constrained('project_categories')->onDelete('cascade')->index('cat_id');
            $table->timestamps();

            // Add a unique constraint to prevent duplicate assignments
            $table->unique(['project_id', 'category_id'], 'proj_cat_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_category');
    }
};

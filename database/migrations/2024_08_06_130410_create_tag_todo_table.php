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
        Schema::create('tag_todo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade'); // Foreign key to tags
            $table->foreignId('todo_id')->constrained('todos')->onDelete('cascade'); // Foreign key to todos
            
            $table->timestamps();

            $table->unique(['tag_id', 'todo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_todo');
    }
};

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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->boolean('is_completed')->default(false);    
            $table->dateTime('due_date')->nullable();
            
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->dateTime('date')->nullable();
            
            $table->string('priority')->default('medium'); // priority: low, medium, high
            $table->string('tags')->nullable(); // tags for categorization
            $table->string('status')->default('pending'); // status: pending, in progress, completed
            $table->dateTime('reminder')->nullable(); // reminder date and time
            $table->string('attachment')->nullable(); // path to an attachment file
            $table->string('category')->nullable(); // category of the task
            $table->integer('estimated_time')->nullable(); // estimated time in minutes
            $table->integer('actual_time')->nullable(); // actual time in minutes
            $table->string('location')->nullable(); // location related to the task
            $table->boolean('recurring')->default(false); // indicates if the task is recurring
            $table->string('recurring_frequency')->nullable(); // frequency of recurrence: daily, weekly, monthly
            $table->string('notes')->nullable(); // additional notes
            $table->dateTime('completed_at')->nullable(); // timestamp when the task was completed
            $table->string('color_code')->nullable(); // color code for the task.
            $table->boolean('is_archived')->default(false); // indicates if the task is archived
            
            // Keys
            $table->string('firebase_todo_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('todos')->onDelete('cascade');

            // Indexes
            $table->index('firebase_todo_id');
            $table->index('parent_id');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('assigned_to');

            $table->index('title');
            $table->index('description');
            $table->index('is_completed');
            $table->index('due_date');
            $table->index('priority');
            $table->index('tags');
            $table->index('status');
            $table->index('reminder');
            $table->index('attachment');
            $table->index('category');
            $table->index('estimated_time');
            $table->index('actual_time');
            $table->index('location');
            $table->index('recurring');
            $table->index('recurring_frequency');
            $table->index('notes');
            $table->index('completed_at');
            $table->index('color_code');
            $table->index('is_archived');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
 
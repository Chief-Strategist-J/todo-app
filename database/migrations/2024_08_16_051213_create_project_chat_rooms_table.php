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
        Schema::create('project_chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_chat_rooms_project_id'); // Unique name for the foreign key constraint
        
            $table->string('name')->unique()->index(); // Unique constraint
            $table->string('type')->default('general')->index();
            $table->boolean('is_private')->default(false)->index();
            $table->integer('member_count')->default(0)->index();
            $table->timestamp('last_message_at')->nullable()->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'type', 'is_private'], 'idx_project_type_private');
            $table->index(['project_id', 'name'], 'idx_project_name');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'type', 'is_private'], 'idx_project_type_private_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_chat_rooms');
    }
};

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
        Schema::create('project_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')
                  ->constrained('project_chat_rooms')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_chat_room_id'); // Unique name for the foreign key constraint
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_user_id'); // Unique name for the foreign key constraint
            
            // Change 'content' from text to string
            $table->string('content');
            
            $table->string('message_type')->default('text')->index();
            $table->boolean('is_pinned')->default(false)->index();
            $table->integer('read_by_count')->default(0)->index();
        
            // Add JSON columns
            $table->json('attachments')->nullable();
        
            // Generated column for indexing JSON attachments
            $table->string('attachments_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(attachments, "$.key"))') // Adjust JSON path according to your structure
                  ->index()
                  ->name('idx_attachments_key'); // Custom name for the index on generated column
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['chat_room_id', 'created_at'], 'idx_chat_room_created_at');
            $table->index(['user_id', 'created_at'], 'idx_user_created_at');
            $table->index(['message_type', 'is_pinned'], 'idx_message_type_pinned');
            $table->index(['chat_room_id', 'user_id'], 'idx_chat_room_user_id');
        
            // Hash Indexing for Composite Fields
            $table->index(['chat_room_id', 'message_type', 'created_at'], 'idx_chat_room_message_type_created_at_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_chat_messages');
    }
};

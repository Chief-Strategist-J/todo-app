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
        Schema::create('notification_comment', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade')->index('idx_notif_comment_notification');
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade')->index('idx_notif_comment_comment');
        
            // Additional fields
            $table->string('action')->default('commented')->index('idx_notif_comment_action');
            $table->dateTime('triggered_at')->nullable()->index('idx_notif_comment_triggered_at');
        
            // Timestamps
            $table->timestamps();
        
            // Unique constraint
            $table->unique(['notification_id', 'comment_id'], 'notif_comment_unique');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_comment');
    }
};

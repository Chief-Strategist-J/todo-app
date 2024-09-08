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
        Schema::create('notification_post', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade')->index('idx_notif_post_notification');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->index('idx_notif_post_post');
        
            // Additional fields
            $table->string('action')->default('created')->index('idx_notif_post_action');
            $table->dateTime('triggered_at')->nullable()->index('idx_notif_post_triggered_at');
        
            // Timestamps
            $table->timestamps();
        
            // Unique constraint
            $table->unique(['notification_id', 'post_id'], 'notif_post_unique');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_post');
    }
};

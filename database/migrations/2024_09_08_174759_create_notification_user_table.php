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
        Schema::create('notification_user', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade')->index('idx_notif_user_notification');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index('idx_notif_user_user');
        
            // Additional fields
            $table->string('status')->default('unread')->index('idx_notif_user_status'); 
            $table->dateTime('read_at')->nullable()->index('idx_notif_user_read_at');
            $table->boolean('is_archived')->default(false)->index('idx_notif_user_archived');
        
            // Timestamps
            $table->timestamps();
        
            // Unique constraint
            $table->unique(['notification_id', 'user_id'], 'notif_user_unique');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_user');
    }
};

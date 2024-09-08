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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Main fields for the notification
            $table->unsignedBigInteger('user_id')->index('idx_notif_user'); // User to whom the notification is related
            $table->string('title')->default('New Notification')->index('idx_notif_title'); // Title of the notification
            $table->string('message')->nullable()->index('idx_notif_message'); // Notification message
            $table->string('type')->default('general')->index('idx_notif_type'); // Type of notification (e.g., alert, message)
            $table->string('status')->default('unread')->index('idx_notif_status'); // Status (unread, read)
            $table->boolean('is_seen')->default(false)->index('idx_notif_seen'); // Boolean to track if the notification was seen
            $table->boolean('is_archived')->default(false)->index('idx_notif_archived'); // Boolean to archive the notification
            $table->string('priority')->default('medium')->index('idx_notif_priority'); // Priority of notification (low, medium, high)
            
            // Timestamps related to notification
            $table->dateTime('sent_at')->nullable()->index('idx_notif_sent_at'); // When the notification was sent
            $table->dateTime('read_at')->nullable()->index('idx_notif_read_at'); // When the notification was read
            $table->dateTime('archived_at')->nullable()->index('idx_notif_archived_at'); // When the notification was archived
        
            // Additional details
            $table->string('url')->nullable()->index('idx_notif_url'); // Optional URL related to notification
            $table->string('icon')->nullable()->index('idx_notif_icon'); // Icon for the notification
            $table->json('metadata')->nullable(); // Additional metadata in JSON format
        
            // Generated columns from JSON fields
            $table->string('metadata_action')->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.action"))')->index('idx_notif_metadata_action'); // Extract 'action' from metadata
            $table->string('metadata_target')->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.target"))')->index('idx_notif_metadata_target'); // Extract 'target' from metadata
        
            // Notification settings
            $table->boolean('is_pushed')->default(false)->index('idx_notif_is_pushed'); // Was the notification sent as a push notification
            $table->boolean('is_emailed')->default(false)->index('idx_notif_is_emailed'); // Was the notification sent via email
            $table->boolean('is_sms_sent')->default(false)->index('idx_notif_is_sms_sent'); // Was the notification sent via SMS
            $table->string('platform')->default('web')->index('idx_notif_platform'); // Platform (web, mobile)
        
            // Notification tracking
            $table->integer('click_count')->default(0)->index('idx_notif_click_count'); // How many times the notification was clicked
            $table->integer('retry_count')->default(0)->index('idx_notif_retry_count'); // How many times the notification sending was retried
            $table->decimal('delivery_time', 8, 2)->nullable()->index('idx_notif_delivery_time'); // Time taken to deliver the notification in seconds
            $table->integer('response_code')->nullable()->index('idx_notif_response_code'); // Response code from notification system
        
            // Unique constraints
            $table->uuid('uuid')->unique()->index('idx_notif_uuid'); // Unique identifier for tracking the notification
        
            // Foreign key constraints with unique names
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->name('fk_notifications_user_id');
        
            // Composite indexes
            $table->index(['user_id', 'status'], 'idx_user_status'); // Composite index for user and status
            $table->index(['is_seen', 'is_archived'], 'idx_seen_archived'); // Composite index for seen and archived status
        
            $table->timestamps();
            $table->softDeletes(); // For soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

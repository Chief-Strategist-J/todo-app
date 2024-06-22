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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('firebase_user_preferences_id')->nullable();

            $table->foreignId('user_detail_id')->nullable() ->constrained('user_details')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 
            
            $table->boolean('receive_email_notifications')->default(true); // Flag for receiving email notifications
            $table->boolean('dark_mode')->default(false); // Flag for enabling dark mode
            $table->string('language')->nullable(); // Preferred language
            $table->string('timezone')->nullable(); // Preferred timezone
            $table->json('dashboard_settings')->nullable();
            $table->json('additional_details')->nullable(); // JSON field for storing customized dashboard settings
            $table->boolean('enable_two_factor_auth')->default(false); // Flag for enabling two-factor authentication
            $table->boolean('enable_automatic_updates')->default(true); // Flag for enabling automatic updates
            $table->boolean('enable_push_notifications')->default(true); // Flag for enabling push notifications
            $table->string('theme_color')->nullable(); // Preferred theme color
            $table->string('currency')->nullable(); // Preferred currency
            $table->string('notification_sound')->nullable(); // Notification sound preference
            $table->boolean('show_online_status')->default(true); // Flag for displaying online status
            $table->string('default_view_mode')->nullable(); // Default view mode preference
            $table->integer('items_per_page')->default(10);


            $table->index('user_detail_id');
            $table->index('user_id');
            $table->index('firebase_user_preferences_id');
            $table->index('receive_email_notifications'); 
            $table->index('dark_mode');
            $table->index('language');
            $table->index('timezone');
            $table->index('enable_two_factor_auth');
            $table->index('enable_automatic_updates');
            $table->index('enable_push_notifications');
            $table->index('theme_color');
            $table->index('currency');
            $table->index('notification_sound');
            $table->index('show_online_status');
            $table->index('default_view_mode');
            $table->index('items_per_page');


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

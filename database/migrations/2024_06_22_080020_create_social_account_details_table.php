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
        Schema::create('social_account_details', function (Blueprint $table) {
            $table->id();

            $table->uuid('firebase_social_account_detail_id')->nullable();
            
            $table->index('user_id');
            $table->index('user_social_account_id');
            $table->index('firebase_social_account_detail_id');

            $table->foreignId('user_social_account_id')->nullable()->constrained('user_social_account_details')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 

            $table->string('social_provider'); // Provider of the social account (e.g., Facebook, Google)
            $table->string('social_username')->nullable(); // Username associated with the social account
            $table->string('social_email')->nullable(); // Email associated with the social account
            $table->string('social_avatar')->nullable(); // Avatar URL of the social account
            $table->json('social_profile_data')->nullable(); // Additional data related to the social profile
            $table->string('social_access_token')->nullable(); // Access token for the social account
            $table->string('social_refresh_token')->nullable(); // Refresh token for the social account
            $table->dateTime('social_token_expires_at')->nullable(); // Expiry date/time of the access token
            $table->boolean('is_verified')->default(false);

            $table->index('social_provider');
            $table->index('social_username');
            $table->index('social_email');
            $table->index('social_avatar');
            $table->index('social_access_token');
            $table->index('social_refresh_token');
            $table->index('social_token_expires_at');
            $table->index('is_verified')->default(false);

            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_account_details');
    }
};

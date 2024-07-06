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
        Schema::create('user_social_account_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('firebase_account_details_id')->nullable();

            $table->foreignId('user_detail_id')->nullable()->constrained('user_details')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 

            $table->string('provider'); // Provider of the social account (e.g., Facebook, Google)
            $table->string('social_id')->unique(); // Social ID associated with the account, unique per provider
            $table->string('avatar')->nullable(); // Avatar URL of the social account
            $table->string('profile_url')->nullable(); // Profile URL of the social account
            $table->json('profile_data')->nullable(); // Additional data retrieved from the social profile
            $table->string('access_token')->nullable(); // Access token for the social account
            $table->string('refresh_token')->nullable(); // Refresh token for the social account
            $table->dateTime('token_expires_at')->nullable(); // Expiry date/time of the access token
            $table->boolean('is_active')->default(true);

            $table->index('user_detail_id');
            $table->index('user_id');
            $table->index('firebase_account_details_id');
            $table->index('provider'); 
            $table->index('social_id');
            $table->index('avatar'); 
            $table->index('profile_url'); 
            $table->index('access_token');
            $table->index('refresh_token'); 
            $table->index('token_expires_at');
            $table->index('is_active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_social_account_details');
    }
};

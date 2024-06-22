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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('firebase_user_details_id')->nullable();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 
           
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('avatar')->nullable();
            $table->string('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            
            $table->string('temp_otp')->nullable();
            
            $table->json('user_settings')->nullable();
            $table->json('additional_details')->nullable();
            
            $table->index('first_name');
            $table->index('last_name');
            $table->index('email');
            $table->index('phone');
            $table->index('birthdate');
            $table->index('address');
            $table->index('city');
            $table->index('state');
            $table->index('country');
            $table->index('zipcode');
            $table->index('avatar');
            $table->index('bio');
            $table->index('is_active');
            $table->index('last_login_at');
            $table->index('user_id');
            $table->index('firebase_user_details_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};

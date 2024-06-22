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
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();

            $table->uuid('firebase_preferences_id')->nullable();

            $table->foreignId('user_preferences_detail_id')->nullable()->constrained('user_preferences')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 

            $table->string('category');
            $table->string('name'); 
            $table->string('value')->nullable(); 

            $table->index('user_id');
            $table->index('user_preferences_detail_id');
            $table->index('firebase_preferences_id')->nullable();
            $table->index('category');
            $table->index('name');
            $table->index('value');
            
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};

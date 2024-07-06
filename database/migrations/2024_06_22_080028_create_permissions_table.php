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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->uuid('firebase_permissions_id')->nullable();

            $table->index('user_id');
            $table->index('firebase_permissions_id')->nullable();
            $table->index('user_permissions_detail_id');

            $table->foreignId('user_permissions_detail_id')->nullable()->constrained('user_permissions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 

            $table->string('name');
            $table->string('description')->nullable();
            $table->string('resource');
            $table->string('action');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->json('permissions_data')->nullable();
            $table->string('grant_type')->nullable();
            $table->unsignedInteger('level')->default(1);

            $table->index('name');
            $table->index('description');
            $table->index('resource');
            $table->index('action');
            $table->index('is_active');
            $table->index('grant_type');
            $table->index('level');
            
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

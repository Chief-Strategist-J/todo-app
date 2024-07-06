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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            
            
            $table->uuid('firebase_user_permissions_id')->nullable();
            $table->index('firebase_user_permissions_id');

            $table->foreignId('user_detail_id')->nullable() ->constrained('user_details')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 
            
            $table->boolean('is_allowed')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->string('note')->nullable();
            $table->unsignedInteger('priority')->default(1); // Priority of the permission
            $table->json('metadata')->nullable(); // Additional metadata related to the permission
            $table->string('scope')->nullable(); // Scope of the permission
            $table->string('constraint')->nullable(); // Constraint for the permission
            $table->boolean('is_revocable')->default(true); // Whether the permission can be revoked
            $table->timestamp('revoked_at')->nullable();

            $table->index('user_detail_id');
            $table->index('user_id');
            $table->index('is_allowed');
            $table->index('expires_at');
            $table->index('note');
            $table->index('priority');
            $table->index('scope'); 
            $table->index('constraint');
            $table->index('is_revocable'); 
            $table->index('revoked_at');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};

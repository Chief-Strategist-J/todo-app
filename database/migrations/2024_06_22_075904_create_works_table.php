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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            
            $table->uuid('firebase_work_id')->nullable();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('user_work_detail_id')->nullable()->constrained('user_works')->onDelete('cascade'); 


            $table->string('project_name')->nullable(); // Name of the project
            $table->string('description')->nullable(); // Description of the project
            $table->date('start_date')->nullable(); // Start date of the project
            $table->date('end_date')->nullable(); // End date of the project
            $table->string('role')->nullable(); // Role in the project
            $table->string('technologies_used')->nullable(); // Technologies used in the project
            $table->boolean('is_featured')->default(false); // Flag indicating if the project is featured
            $table->string('url')->nullable(); // URL related to the project
            $table->string('client_name')->nullable(); // Name of the client or organization
            $table->string('client_contact')->nullable(); // Contact details of the client
            $table->string('project_type')->nullable(); // Type of the project (e.g., web application, mobile app)
            $table->string('project_status')->nullable(); // Status of the project (e.g., ongoing, completed)
            $table->string('responsibilities')->nullable(); // Specific responsibilities in the project
            $table->string('challenges')->nullable();


            $table->index('user_id');
            $table->index('user_work_detail_id');
            $table->index('firebase_work_id');
            $table->index('project_name');
            $table->index('description');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('role');
            $table->index('technologies_used');
            $table->index('is_featured');
            $table->index('url');
            $table->index('client_name');
            $table->index('client_contact');
            $table->index('project_type');
            $table->index('project_status');
            $table->index('responsibilities');
            $table->index('challenges');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};

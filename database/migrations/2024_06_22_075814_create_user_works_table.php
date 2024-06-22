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
        Schema::create('user_works', function (Blueprint $table) {
            $table->id();


            $table->uuid('firebase_user_works_id')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('user_detail_id')->nullable()->constrained('user_details')->onDelete('cascade'); 
            
            $table->string('company_name')->nullable(); // Name of the company
            $table->string('position')->nullable(); // Position held
            $table->date('start_date')->nullable(); // Start date of employment
            $table->date('end_date')->nullable(); // End date of employment
            $table->boolean('current_job')->default(false); // Flag indicating if this is the current job
            $table->string('responsibilities')->nullable(); // Responsibilities in the job
            $table->string('location')->nullable(); // Location of the job
            $table->string('department')->nullable(); // Department within the company
            $table->boolean('is_remote')->default(false); // Flag indicating if the job was remote
            $table->string('supervisor_name')->nullable(); // Name of the supervisor
            $table->string('supervisor_contact')->nullable(); // Contact details of the supervisor
            $table->string('salary')->nullable(); // Salary details
            $table->string('employment_type')->nullable(); // Full-time, part-time, contract, etc.
            $table->string('reason_for_leaving')->nullable(); // Reason for leaving the job
            $table->string('achievements')->nullable(); // Achievements in the job
            $table->boolean('is_verified')->default(false); 


            $table->index('user_id');
            $table->index('user_detail_id');
            $table->index('firebase_user_works_id');
            $table->index('company_name');
            $table->index('position');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('current_job');
            $table->index('responsibilities');
            $table->index('location');
            $table->index('department');
            $table->index('is_remote');
            $table->index('supervisor_name');
            $table->index('supervisor_contact');
            $table->index('salary');
            $table->index('employment_type');
            $table->index('reason_for_leaving');
            $table->index('achievements');
            $table->index('is_verified'); 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_works');
    }
};

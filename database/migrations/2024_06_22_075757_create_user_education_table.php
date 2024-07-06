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
        Schema::create('user_education', function (Blueprint $table) {
            $table->id();

            $table->uuid('firebase_user_education_id')->nullable();

            $table->foreignId('user_detail_id')->nullable()->constrained('user_details')->onDelete('cascade'); 
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 

            $table->string('institution_name')->nullable(); // Name of the educational institution
            $table->string('degree')->nullable(); // Degree obtained
            $table->string('field_of_study')->nullable(); // Field of study
            $table->date('start_date')->nullable(); // Start date of education
            $table->date('end_date')->nullable(); // End date of education
            $table->string('grade')->nullable(); // Grade or GPA
            $table->boolean('currently_studying')->default(false); // Flag indicating if currently studying
            $table->string('location')->nullable(); // Location of the institution
            $table->string('activities')->nullable(); // Activities and societies involved in
            $table->string('achievements')->nullable(); // Achievements and awards
            $table->boolean('is_verified')->default(false); // Verification status of education details
            $table->string('major')->nullable(); // Major subject
            $table->string('minor')->nullable(); // Minor subject
            $table->string('advisor')->nullable(); // Advisor name
            $table->string('thesis_title')->nullable(); // Thesis title
            $table->boolean('honors')->default(false); // Honors received
            $table->string('study_mode')->nullable(); // Mode of study (online, in-person, etc.)
            $table->string('description')->nullable(); // Description of the education program
            $table->string('transcript_url')->nullable(); // URL to the transcript
            $table->string('certification')->nullable(); // Certification obtained
            $table->boolean('is_completed')->default(false); // Flag indicating if the education is completed
            $table->string('institution_website')->nullable(); // Institution website
            $table->string('notes')->nullable(); 

            $table->index('user_detail_id');
            $table->index('user_id');
            $table->index('firebase_user_education_id');
            $table->index('institution_name'); 
            $table->index('degree'); 
            $table->index('field_of_study');
            $table->index('start_date'); 
            $table->index('end_date'); 
            $table->index('grade');
            $table->index('currently_studying');
            $table->index('location'); 
            $table->index('activities'); 
            $table->index('achievements'); 
            $table->index('is_verified'); 
            $table->index('major');
            $table->index('minor'); 
            $table->index('advisor'); 
            $table->index('thesis_title');
            $table->index('honors'); 
            $table->index('study_mode');
            $table->index('description'); 
            $table->index('transcript_url');
            $table->index('certification');
            $table->index('is_completed');
            $table->index('institution_website');
            $table->index('notes'); 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_education');
    }
};

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
        Schema::create('education', function (Blueprint $table) {
            $table->id();

            $table->uuid('firebase_education_id')->nullable();

            $table->foreignId('user_education_detail_id')->nullable()->constrained('user_education')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 
            
            $table->string('course_name')->nullable(); // Name of the course or subject
            $table->string('description')->nullable(); // Description of the course
            $table->date('start_date')->nullable(); // Start date of the course
            $table->date('end_date')->nullable(); // End date of the course
            $table->string('instructor')->nullable(); // Instructor or professor
            $table->string('grade')->nullable(); // Grade obtained in the course
            $table->boolean('is_featured')->default(false); // Flag indicating if the course is featured
            $table->string('certificate_url')->nullable(); // URL to the certificate if available
            $table->string('institution_name')->nullable(); // Name of the institution offering the course
            $table->string('course_type')->nullable(); // Type of the course (e.g., online, in-person)
            $table->string('status')->nullable(); // Status of the course (e.g., completed, ongoing)
            $table->string('credits')->nullable(); // Credits earned
            $table->string('course_code')->nullable(); // Course code
            $table->string('department')->nullable(); // Department offering the course
            $table->string('semester')->nullable(); // Semester of the course
            $table->boolean('is_mandatory')->default(false); // Mandatory course flag
            $table->boolean('is_elective')->default(false); // Elective course flag
            $table->string('syllabus')->nullable(); // Course syllabus
            $table->string('prerequisites')->nullable(); // Prerequisites for the course
            $table->boolean('is_online')->default(false); // Flag indicating if the course is online
            $table->string('feedback')->nullable(); // Feedback received for the course
            $table->string('resources')->nullable();


            $table->index('user_id');
            $table->index('user_education_detail_id');
            $table->index('firebase_education_id');
            $table->index('course_name'); 
            $table->index('description');
            $table->index('start_date'); 
            $table->index('end_date');
            $table->index('instructor'); 
            $table->index('grade'); 
            $table->index('is_featured'); 
            $table->index('certificate_url'); 
            $table->index('institution_name'); 
            $table->index('course_type'); 
            $table->index('status');
            $table->index('credits');
            $table->index('course_code');
            $table->index('department'); 
            $table->index('semester');
            $table->index('is_mandatory'); 
            $table->index('is_elective');
            $table->index('syllabus');
            $table->index('prerequisites');
            $table->index('is_online');
            $table->index('feedback');
            $table->index('resources');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};

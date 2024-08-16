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
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_reports_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_reports_created_by'); // Unique name for the foreign key constraint
        
            $table->string('report_type')->index();
            $table->string('name')->index();
            
            // JSON Field with Generated Columns
            $table->json('report_data')->nullable();
            $table->string('report_data_summary')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(report_data, "$.summary"))')
                  ->index();
            $table->string('report_data_details')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(report_data, "$.details"))')
                  ->index();
            
            $table->dateTime('report_date')->index();
            $table->string('status')->default('draft')->index();
            $table->string('summary')->nullable(); // Changed from text to string
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'report_type', 'report_date'], 'idx_proj_type_date');
            $table->index(['created_by', 'status'], 'idx_created_by_status');
            $table->index(['project_id', 'name', 'status'], 'idx_proj_name_status');
            $table->index(['report_date', 'report_type', 'created_by'], 'idx_date_type_created_by');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'report_type', 'name'], 'idx_reports_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_reports');
    }
};

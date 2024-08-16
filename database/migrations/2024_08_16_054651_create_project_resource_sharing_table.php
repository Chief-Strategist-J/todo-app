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
        Schema::create('project_resource_sharing', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('provider_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_resource_sharing_provider_project_id');
        
            $table->foreignId('receiver_project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->name('fk_resource_sharing_receiver_project_id');
        
            $table->string('resource_type')->index(); // Index for quick searches on resource type
            $table->string('description'); // Changed to string from text
            $table->integer('quantity')->index();
            $table->string('unit')->nullable();
            $table->date('start_date')->index();
            $table->date('end_date')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->decimal('cost', 15, 2)->nullable()->index();
            $table->string('currency', 3)->default('USD');
            $table->json('terms_and_conditions')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->name('fk_resource_sharing_approved_by');
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['provider_project_id', 'receiver_project_id', 'resource_type', 'start_date'], 'uniq_resource_sharing_proj_type_start_date');
        
            // Composite Indexes with Shortened Names
            $table->index(['provider_project_id', 'status', 'start_date'], 'idx_resource_sharing_provider_proj_status_start_date');
            $table->index(['receiver_project_id', 'status', 'start_date'], 'idx_resource_sharing_receiver_proj_status_start_date');
            $table->index(['resource_type', 'status', 'quantity'], 'idx_resource_sharing_type_status_quantity');
            $table->index(['cost', 'currency'], 'idx_resource_sharing_cost_currency');
            $table->index(['provider_project_id', 'receiver_project_id', 'end_date'], 'idx_resource_sharing_proj_pair_end_date');
            $table->index(['start_date', 'end_date'], 'idx_resource_sharing_date_range');
        
            // Generated Columns and Indexes for JSON Fields
            // Assuming terms_and_conditions contains a key 'agreement' that you want to extract
            $table->string('terms_and_conditions_agreement')->nullable(); // Example generated column for key in JSON
        
            $table->index('terms_and_conditions_agreement', 'idx_resource_sharing_terms_agreement');
        });
        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_resource_sharing');
    }
};

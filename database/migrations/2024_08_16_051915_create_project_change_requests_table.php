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
        Schema::create('project_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_change_requests_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_change_requests_requested_by'); // Unique name for the foreign key constraint
        
            $table->string('change_type')->index();
            
            // Change 'description' from text to string
            $table->string('description');
            
            $table->string('status')->default('pending')->index();
            
            // Add JSON column
            $table->json('impact_analysis')->nullable();
            
            // Generated column for indexing JSON impact_analysis
            $table->string('impact_analysis_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(impact_analysis, "$.key"))') // Adjust JSON path according to your structure
                  ->index()
                  ->name('idx_impact_analysis_key'); // Custom name for the index on generated column
        
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->index()
                  ->name('fk_change_requests_approved_by'); // Unique name for the foreign key constraint
        
            $table->timestamp('approved_at')->nullable()->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'change_type', 'status'], 'idx_project_change_status');
            $table->index(['requested_by', 'status'], 'idx_requested_by_status');
            $table->index(['project_id', 'approved_by', 'approved_at'], 'idx_project_approved_by_at');
            $table->index(['status', 'approved_at'], 'idx_status_approved_at');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'change_type', 'status'], 'idx_change_requests_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_change_requests');
    }
};

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
        Schema::create('project_integrations', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_integrations_project_id'); // Unique name for the foreign key constraint
        
            $table->string('integration_type')->index(); // Index integration type for quick searches
        
            // JSON Field with Generated Columns
            $table->json('configuration')->nullable();
            $table->string('config_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(configuration, "$.key"))')
                  ->index();
            $table->string('config_value')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(configuration, "$.value"))')
                  ->index();
        
            $table->boolean('is_active')->default(true)->index();
            $table->dateTime('last_sync_at')->nullable()->index();
        
            // Changed from text to string
            $table->string('sync_status')->nullable()->index(); // Index sync status for quick lookup
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'integration_type'], 'uniq_project_integration_type');
            
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'integration_type', 'is_active'], 'idx_proj_integration_active');
            $table->index(['project_id', 'last_sync_at', 'is_active'], 'idx_proj_last_sync_active');
            $table->index(['integration_type', 'last_sync_at'], 'idx_integration_sync');
            $table->index(['project_id', 'sync_status'], 'idx_proj_sync_status');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'integration_type', 'is_active'], 'idx_integrations_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_integrations');
    }
};

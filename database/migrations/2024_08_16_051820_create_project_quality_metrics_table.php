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
        Schema::create('project_quality_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_quality_metrics_project_id'); // Unique name for the foreign key constraint
        
            $table->string('metric_name')->index();
            $table->decimal('target_value', 10, 2)->index();
            $table->decimal('actual_value', 10, 2)->nullable()->index();
            
            // Change 'description' from text to string
            $table->string('description')->nullable();
            
            $table->string('unit_of_measure')->nullable()->index();
            $table->timestamp('measured_at')->nullable()->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'metric_name', 'measured_at'], 'idx_project_metric_measured_at');
            $table->index(['project_id', 'target_value'], 'idx_project_target_value');
            $table->index(['project_id', 'actual_value'], 'idx_project_actual_value');
            $table->index(['metric_name', 'unit_of_measure'], 'idx_metric_unit_of_measure');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'metric_name', 'target_value'], 'idx_quality_metrics_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_quality_metrics');
    }
};

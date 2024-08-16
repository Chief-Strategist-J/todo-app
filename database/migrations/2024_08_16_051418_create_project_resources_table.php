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
        Schema::create('project_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_resources_project_id'); // Unique name for the foreign key constraint
            
            $table->string('name')->index(); // Considered unique if necessary
            $table->string('type')->index();
            $table->decimal('quantity', 10, 2)->default(0.00)->index();
            $table->string('unit')->nullable()->index();
            $table->decimal('cost_per_unit', 15, 2)->nullable()->index();
            
            // Add JSON column
            $table->json('availability_schedule')->nullable();
            
            // Change 'description' from text to string
            $table->string('description')->nullable();
        
            // Generated column for indexing JSON availability_schedule
            $table->string('availability_schedule_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(availability_schedule, "$.key"))') // Adjust JSON path according to your structure
                  ->index()
                  ->name('idx_availability_schedule_key'); // Custom name for the index on generated column
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'type', 'quantity'], 'idx_project_type_quantity');
            $table->index(['project_id', 'name'], 'idx_project_name');
            $table->index(['project_id', 'cost_per_unit'], 'idx_project_cost_per_unit');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'type', 'quantity'], 'idx_project_resources_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_resources');
    }
};

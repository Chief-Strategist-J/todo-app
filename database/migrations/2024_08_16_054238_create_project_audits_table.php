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
        Schema::create('project_audits', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_audits_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->name('fk_audits_user_id'); // Unique name for the foreign key constraint
        
            $table->string('action')->index();
            $table->string('auditable_type')->index();
            $table->unsignedBigInteger('auditable_id')->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent')->nullable()->index();
        
            $table->timestamps();
        
            // Unique Constraint
            $table->unique(['project_id', 'user_id', 'auditable_type', 'auditable_id', 'created_at'], 'uniq_audits_project_user_type_id_date');
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'action', 'auditable_type'], 'idx_audits_proj_action_type');
            $table->index(['user_id', 'action'], 'idx_audits_user_action');
            $table->index(['auditable_type', 'auditable_id'], 'idx_audits_type_id');
            $table->index(['ip_address', 'user_agent'], 'idx_audits_ip_user_agent');
            $table->index(['created_at'], 'idx_audits_created_at');
            $table->index(['project_id', 'created_at'], 'idx_audits_proj_created_at');
        
            // Generated Columns and Indexes for JSON Fields
            $table->string('old_values_key')->nullable(); // Example generated column for key in JSON
            $table->string('new_values_key')->nullable(); // Example generated column for key in JSON
        
            $table->index('old_values_key', 'idx_audits_old_values_key');
            $table->index('new_values_key', 'idx_audits_new_values_key');
        });
        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_audits');
    }
};

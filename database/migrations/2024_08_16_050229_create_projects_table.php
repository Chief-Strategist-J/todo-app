<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name')->index();
            $table->string('slug')->unique()->index(); // Unique constraint
            $table->string('description')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->dateTime('start_date')->nullable()->index();
            $table->dateTime('end_date')->nullable()->index();
            $table->decimal('budget', 15, 2)->nullable()->default(0.00)->index();
            $table->string('currency', 3)->default('USD')->index();
            $table->integer('progress_percentage')->default(0)->index();
            $table->string('priority')->default('medium')->index();
            $table->boolean('is_public')->default(false)->index();
            $table->string('client_name')->nullable()->index();
            $table->string('project_manager')->nullable()->index();
            $table->integer('estimated_hours')->nullable()->default(0)->index();
            $table->integer('actual_hours')->default(0)->index();
            $table->string('repository_url')->nullable()->index();
            $table->string('documentation_url')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->boolean('is_archived')->default(false)->index();
            $table->integer('task_count')->default(0)->index();
            $table->integer('completed_task_count')->default(0)->index();
            $table->integer('team_size')->default(0)->index();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->string('project_code')->unique()->index(); // Unique constraint
            $table->decimal('risk_score', 5, 2)->default(0.00)->index();
            $table->string('status_color')->default('#808080')->index();
            $table->integer('comment_count')->default(0)->index();
            $table->integer('attachment_count')->default(0)->index();
            $table->decimal('completion_percentage', 5, 2)->default(0.00)->index();
            $table->string('main_language')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->decimal('customer_satisfaction_score', 3, 2)->nullable()->default(0.00)->index();
            $table->integer('revision_count')->default(0)->index();
            $table->string('project_type')->nullable()->index();
            $table->decimal('roi', 8, 2)->nullable()->default(0.00)->index();
            $table->integer('stakeholder_count')->default(0)->index();
            $table->decimal('budget_utilization', 5, 2)->default(0.00)->index();
            $table->string('project_phase')->nullable()->index();
            $table->string('lessons_learned')->nullable()->index();

            $table->foreignId('created_by')->constrained('users', 'id')->onDelete('cascade')->index()->name('projects_created_by_foreign');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id')->onDelete('set null')->index()->name('projects_updated_by_foreign');
            $table->foreignId('department_id')->nullable()->constrained('departments', 'id')->onDelete('set null')->index()->name('projects_department_id_foreign');

            $table->softDeletes();
            $table->timestamps();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('project_categories')
                ->onDelete('set null')
                ->index()
                ->name('projects_category_id_fk');

            $table->foreignId('priority_id')
                ->nullable()
                ->constrained('project_priorities')
                ->onDelete('set null')
                ->index()
                ->name('projects_priority_id_fk');

            $table->foreignId('type_id')
                ->nullable()
                ->constrained('project_types')
                ->onDelete('set null')
                ->index()
                ->name('projects_type_id_fk');

            $table->foreignId('phase_id')
                ->nullable()
                ->constrained('project_phases')
                ->onDelete('set null')
                ->index()
                ->name('projects_phase_id_fk');

            $table->foreignId('status_id')
                ->nullable()
                ->constrained('project_statuses')
                ->onDelete('set null')
                ->index()
                ->name('projects_status_id_fk');

            // Create composite index
            $table->index(['category_id', 'priority_id', 'status_id'], 'category_priority_status_index');

            // Composite indexes with shortened names
            $table->index(['status', 'priority', 'project_type'], 'status_priority_type_index');
            $table->index(['start_date', 'end_date', 'project_phase'], 'dates_phase_index');
            $table->index(['is_public', 'is_archived', 'is_featured'], 'public_archived_featured_index');
            $table->index(['category', 'status', 'priority'], 'category_status_priority_index');
            $table->index(['risk_score', 'completion_percentage', 'budget_utilization'], 'risk_completion_budget_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

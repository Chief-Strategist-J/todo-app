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
        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
        
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_expenses_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('cost_center_id')
                  ->nullable()
                  ->constrained('project_cost_centers')
                  ->onDelete('set null')
                  ->name('fk_expenses_cost_center_id'); // Unique name for the foreign key constraint
        
            $table->string('description')->index(); // Expense description
            $table->decimal('amount', 15, 2)->index(); // Amount
            $table->date('date')->index(); // Expense date
            $table->string('category')->index(); // Expense category
            $table->foreignId('recorded_by')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->name('fk_expenses_recorded_by'); // Unique name for the foreign key constraint
            $table->string('status')->default('pending')->index(); // Expense status
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->name('fk_expenses_approved_by'); // Unique name for the foreign key constraint
        
            $table->timestamps();
            $table->softDeletes();
        
            // Unique Constraint
            $table->unique(['project_id', 'date', 'description'], 'uniq_expenses_project_date_desc'); // Unique entries for each expense
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'category', 'date'], 'idx_expenses_proj_cat_date');
            $table->index(['amount', 'status'], 'idx_expenses_amount_status'); // Index for amount and status
            $table->index(['project_id', 'amount'], 'idx_expenses_proj_amount'); // Additional index for project_id and amount
            $table->index(['cost_center_id', 'status'], 'idx_expenses_cost_center_status'); // Additional index for cost_center_id and status
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_expenses');
    }
};

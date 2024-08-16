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
        Schema::create('project_budgets', function (Blueprint $table) {
            $table->id();

            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->index()
                ->name('fk_budgets_project_id'); // Unique name for the foreign key constraint

            $table->decimal('total_budget', 15, 2)->index();
            $table->decimal('spent_amount', 15, 2)->default(0.00)->index();
            $table->string('currency', 3)->default('USD')->index();

            // JSON Field with Generated Column for Indexing
            $table->json('budget_breakdown')->nullable();
            $table->decimal('budget_breakdown_amount')->generatedAs('CAST(budget_breakdown->"$.amount" AS DECIMAL(15,2))')->index();

            $table->decimal('contingency_percentage', 5, 2)->default(10.00)->index();
            $table->string('budget_status')->default('within_budget')->index();

            // Change 'notes' from text to string
            $table->string('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'total_budget', 'spent_amount'], 'idx_proj_total_spent');
            $table->index(['currency', 'budget_status'], 'idx_currency_status');
            $table->index(['contingency_percentage', 'total_budget'], 'idx_contingency_total');
            $table->index(['spent_amount', 'budget_status'], 'idx_spent_status');

            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'total_budget', 'contingency_percentage'], 'idx_budgets_hash')->algorithm('hash');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_budgets');
    }
};

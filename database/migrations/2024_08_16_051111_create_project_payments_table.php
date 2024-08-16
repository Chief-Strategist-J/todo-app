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
        Schema::create('project_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_project_payments_project_id'); // Unique name for the foreign key constraint
        
            $table->decimal('amount', 15, 2)->default(0.00)->index();
            $table->string('currency', 3)->default('USD')->index();
            $table->string('status')->default('pending')->index();
            $table->string('payment_method')->nullable()->index();
            $table->string('transaction_id')->nullable()->unique()->index(); // Unique constraint
            $table->timestamp('paid_at')->nullable()->index();
            
            // Change 'notes' from text to string
            $table->string('notes')->nullable()->index();
        
            // Add JSON columns
            $table->json('metadata')->nullable();
            
            // Generated column for indexing JSON metadata
            $table->string('metadata_key')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.key"))') // Adjust JSON path according to your structure
                  ->index()
                  ->name('idx_metadata_key'); // Custom name for the index on generated column
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'status', 'paid_at'], 'idx_project_status_paid_at');
            $table->index(['project_id', 'amount', 'status'], 'idx_project_amount_status');
        
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'currency', 'status'], 'idx_project_currency_status_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_payments');
    }
};

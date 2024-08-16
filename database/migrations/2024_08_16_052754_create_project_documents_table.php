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
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key Constraints with Unique Names
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_documents_project_id'); // Unique name for the foreign key constraint
        
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index()
                  ->name('fk_documents_uploaded_by'); // Unique name for the foreign key constraint
        
            $table->string('title')->index();
            $table->string('file_path')->unique()->index(); // Ensure unique file paths
            $table->string('file_type')->index();
            $table->integer('file_size')->nullable()->index(); // Index file size for queries
            $table->string('version')->default('1.0')->index();
            
            // Changed from text to string
            $table->string('description')->nullable();
            
            // JSON Field with Generated Columns
            $table->json('tags')->nullable();
            $table->string('tags_primary')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(tags, "$.primary"))')
                  ->index();
            $table->string('tags_secondary')
                  ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(tags, "$.secondary"))')
                  ->index();
            
            $table->boolean('is_public')->default(false)->index();
        
            $table->timestamps();
            $table->softDeletes();
        
            // Composite Indexes with Shortened Names
            $table->index(['project_id', 'file_type', 'version'], 'idx_proj_file_type_version');
            $table->index(['project_id', 'file_size', 'is_public'], 'idx_proj_file_size_public');
            $table->index(['uploaded_by', 'file_type', 'version'], 'idx_uploaded_file_type_version');
            $table->index(['title', 'file_type'], 'idx_title_file_type');
            
            // Hash Indexing for Composite Fields
            $table->index(['project_id', 'file_path'], 'idx_documents_hash')->algorithm('hash');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};

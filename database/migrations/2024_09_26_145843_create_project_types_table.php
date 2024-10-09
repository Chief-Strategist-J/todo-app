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
        Schema::create('project_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->json('attributes')->nullable();
        
            $table->string('attributes_type')
                ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.type"))')
                ->index('attributes_type_index');
        
            // Adding created_by foreign key
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->index('idx_created_by_types');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_types');
    }
};

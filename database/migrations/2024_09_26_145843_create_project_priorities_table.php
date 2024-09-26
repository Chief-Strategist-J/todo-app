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
        Schema::create('project_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->json('settings')->nullable();

            $table->string('settings_level')
                ->virtualAs('JSON_UNQUOTE(JSON_EXTRACT(settings, "$.level"))')
                ->index('settings_level_index');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_priorities');
    }
};

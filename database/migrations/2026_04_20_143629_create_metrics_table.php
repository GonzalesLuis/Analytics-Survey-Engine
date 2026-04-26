<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the metrics table.
     */
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id('metric_id');

            // Stable metric identifier used by `SurveyService` (e.g. srlg, tmes, perceived_learning_gain).
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};

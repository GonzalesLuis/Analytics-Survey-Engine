<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the dimensions table.
     */
    public function up(): void
    {
        Schema::create('dimensions', function (Blueprint $table) {
            $table->id('dimension_id');

            // Owning survey (surveys.survey_id).
            $table->foreignId('survey_id')->constrained('surveys', 'survey_id');

            // Optional label used to group dimensions in the UI (e.g. guidance_level, language).
            $table->string('category')->nullable();

            // Stable key used by compute logic (e.g. prior_understanding, confidence).
            $table->string('name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dimensions');
    }
};

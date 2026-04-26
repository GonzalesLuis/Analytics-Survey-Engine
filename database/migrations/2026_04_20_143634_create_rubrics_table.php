<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the rubrics table.
     */
    public function up(): void
    {
        Schema::create('rubrics', function (Blueprint $table) {
            $table->id('rubric_id');

            // Owning metric (metrics.metric_id).
            $table->foreignId('metric_id')->constrained('metrics', 'metric_id');

            // Inclusive numeric score range for this rubric row.
            $table->decimal('min_score', 5, 4);
            $table->decimal('max_score', 5, 4);

            // Human-readable categorization (e.g. low/medium/high).
            $table->string('status_level');

            // Text shown to users; "algorithm interpretation" can be more technical.
            $table->text('interpretation');
            $table->text('algo_interpretation')->nullable();
            $table->text('recommended_action')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubrics');
    }
};

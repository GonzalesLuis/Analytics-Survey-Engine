<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the rubric results table.
     */
    public function up(): void
    {
        Schema::create('rubric_results', function (Blueprint $table) {
            $table->id('rubric_results_id');

            // Owning tutoring session (tutoring_sessions.tutoring_session_id).
            $table->foreignId('tutoring_session_id')->constrained('tutoring_sessions', 'tutoring_session_id');

            // User the interpretation belongs to.
            $table->unsignedBigInteger('user_id');

            // The metric result being interpreted (metric_results.metric_result_id).
            $table->foreignId('metric_result_id')->constrained('metric_results', 'metric_result_id');

            // Denormalized rubric fields for direct rendering on the results page.
            $table->string('status_level');
            $table->text('interpretation');
            $table->text('algorithm_interpretation')->nullable();
            $table->text('recommended_action')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_results');
    }
};

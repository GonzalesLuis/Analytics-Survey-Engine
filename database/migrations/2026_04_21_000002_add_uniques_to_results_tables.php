<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add unique constraints to result/answer tables.
     */
    public function up(): void
    {
        Schema::table('metric_results', function (Blueprint $table) {
            // Ensures recomputing metrics updates the same row instead of inserting duplicates.
            $table->unique(
                ['tutoring_session_id', 'user_id', 'metric_id'],
                'metric_results_session_user_metric_unique'
            );
        });

        Schema::table('dimension_scores', function (Blueprint $table) {
            // Ensures recalculating dimension scores replaces existing values for the same session/user/dimension.
            $table->unique(
                ['tutoring_session_id', 'user_id', 'dimension_id'],
                'dimension_scores_session_user_dimension_unique'
            );
        });

        Schema::table('response_answers', function (Blueprint $table) {
            // Prevents the same question being stored twice within a single response.
            $table->unique(
                ['response_id', 'question_id'],
                'response_answers_response_question_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('response_answers', function (Blueprint $table) {
            $table->dropUnique('response_answers_response_question_unique');
        });

        Schema::table('dimension_scores', function (Blueprint $table) {
            $table->dropUnique('dimension_scores_session_user_dimension_unique');
        });

        Schema::table('metric_results', function (Blueprint $table) {
            $table->dropUnique('metric_results_session_user_metric_unique');
        });
    }
};

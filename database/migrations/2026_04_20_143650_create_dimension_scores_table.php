<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the dimension scores table.
     */
    public function up(): void
    {
        Schema::create('dimension_scores', function (Blueprint $table) {
            $table->id('dimension_score_id');

            // Owning tutoring session (tutoring_sessions.tutoring_session_id).
            $table->foreignId('tutoring_session_id')->constrained('tutoring_sessions', 'tutoring_session_id');

            // Which dimension the score represents (dimensions.dimension_id).
            $table->foreignId('dimension_id')->constrained('dimensions', 'dimension_id');

            // Raw average (1-5 scale) and its normalized representation (0-1).
            $table->decimal('avg_score', 5, 4);
            $table->decimal('normalized_score', 5, 4);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dimension_scores');
    }
};

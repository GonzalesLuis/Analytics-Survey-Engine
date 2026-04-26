<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the metric results table.
     */
    public function up(): void
    {
        Schema::create('metric_results', function (Blueprint $table) {
            $table->id('metric_result_id');

            // Owning tutoring session (tutoring_sessions.tutoring_session_id).
            $table->foreignId('tutoring_session_id')->constrained('tutoring_sessions', 'tutoring_session_id');

            // User the metrics belong to (the workflow supports multi-user sessions if needed).
            $table->unsignedBigInteger('user_id');

            // Which metric this score represents (metrics.metric_id).
            $table->foreignId('metric_id')->constrained('metrics', 'metric_id');

            // Final computed score (0-1).
            $table->decimal('metric_score', 5, 4);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_results');
    }
};

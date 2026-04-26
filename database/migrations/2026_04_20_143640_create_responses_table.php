<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the responses table.
     */
    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id('response_id');

            // Owning tutoring session (tutoring_sessions.tutoring_session_id).
            $table->foreignId('tutoring_session_id')->constrained('tutoring_sessions', 'tutoring_session_id');

            // The user who submitted the survey (kept as an integer for flexibility).
            $table->unsignedBigInteger('user_id');

            // Which survey this response belongs to (surveys.survey_id).
            $table->foreignId('survey_id')->constrained('surveys', 'survey_id');

            // Explicit created timestamp; this model disables automatic timestamps.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};

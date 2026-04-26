<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the response answers table.
     */
    public function up(): void
    {
        Schema::create('response_answers', function (Blueprint $table) {
            $table->id('response_answer_id');

            // Owning response (responses.response_id).
            $table->foreignId('response_id')->constrained('responses', 'response_id');

            // The question being answered (questions.question_id).
            $table->foreignId('question_id')->constrained('questions', 'question_id');

            // Likert score stored as a small integer (1..5).
            $table->unsignedTinyInteger('score');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_answers');
    }
};

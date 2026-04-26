<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the questions table.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id');

            // Owning dimension (dimensions.dimension_id).
            $table->foreignId('dimension_id')->constrained('dimensions', 'dimension_id');

            // The text displayed to the respondent.
            $table->text('question_text');

            // When true, the selected Likert score is reversed before aggregation.
            $table->boolean('is_reverse')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

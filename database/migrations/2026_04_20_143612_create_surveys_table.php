<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the surveys table.
     */
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            // Primary key uses a custom column name to match the rest of the schema.
            $table->id('survey_id');

            // Stable identifier used by the application (e.g. "pre_session", "post_session").
            $table->string('name');

            // Standard Laravel timestamps (created_at/updated_at) for auditability.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};

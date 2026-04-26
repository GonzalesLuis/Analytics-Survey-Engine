<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add user_id to dimension_scores.
     */
    public function up(): void
    {
        Schema::table('dimension_scores', function (Blueprint $table) {
            // Insert next to tutoring_session_id for readability and composite uniqueness later.
            $table->unsignedBigInteger('user_id')->after('tutoring_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dimension_scores', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};

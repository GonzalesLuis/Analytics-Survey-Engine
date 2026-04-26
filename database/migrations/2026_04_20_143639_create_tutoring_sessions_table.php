<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the tutoring sessions table.
     */
    public function up(): void
    {
        Schema::create('tutoring_sessions', function (Blueprint $table) {
            $table->id('tutoring_session_id');

            // Optional linkage to an assignment/task being tutored
            $table->unsignedBigInteger('assignment_id')->nullable();

            // Session lifecycle timestamps.
            $table->timestamp('session_start')->nullable();
            $table->timestamp('session_end')->nullable();
            $table->timestamp('evaluated_at')->nullable();

            // Simple state machine managed by the application (ongoing -> completed).
            $table->string('status')->default('ongoing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutoring_sessions');
    }
};

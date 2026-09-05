<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Semakan permohonan (Urus Setia / Kewangan / Teknikal) — append-only.
 * BERASINGAN daripada application_approvals (kelulusan formal).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('review_type', 20);   // secretariat | finance | technical
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 30);      // recommend | return_for_revision | not_recommended
            $table->text('comments')->nullable();
            $table->unsignedInteger('revision_number')->default(0);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['application_id', 'review_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_reviews');
    }
};

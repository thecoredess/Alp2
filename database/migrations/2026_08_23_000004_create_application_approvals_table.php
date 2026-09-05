<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rekod kelulusan formal per aras — append-only.
 * Unique(application_id, approval_level_id) menghalang aras yang sama
 * diluluskan dua kali (idempotensi / klik berganda).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('approval_level_id')->nullable()->constrained('approval_levels')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 30);   // approved | rejected | return_for_revision
            $table->text('comments')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->unsignedInteger('revision_number')->default(0);
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('application_id');
            // Satu aras hanya boleh diluluskan sekali bagi satu pusingan (revisi) —
            // menghalang kelulusan berganda / klik berganda dalam pusingan yang sama.
            $table->unique(['application_id', 'approval_level_id', 'revision_number'], 'app_level_rev_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_approvals');
    }
};

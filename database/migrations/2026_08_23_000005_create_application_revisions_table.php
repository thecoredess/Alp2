<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rekod revisi — mengekalkan bukti keadaan permohonan yang dihantar sebelum
 * ia dikembalikan untuk pembetulan (snapshot), serta sebab & peringkat pemulangan.
 * Append-only untuk tujuan audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->unsignedInteger('revision_number');
            $table->decimal('previous_requested_amount', 15, 2)->default(0);
            $table->json('snapshot')->nullable();          // maklumat projek + item bajet + senarai dokumen
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('return_stage', 30)->nullable(); // secretariat | finance | technical | approval
            $table->text('reason')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('resubmitted_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_revisions');
    }
};

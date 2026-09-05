<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dokumen projek — bukti perbelanjaan & laporan akhir. Storan peribadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_expense_id')->nullable()->constrained('project_expenses')->cascadeOnDelete();
            $table->string('category', 30);      // expense_evidence | report_evidence
            $table->string('document_type', 40);
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('sha256', 64)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'category']);
            $table->index('project_expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};

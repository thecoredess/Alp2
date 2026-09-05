<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kewajipan dokumen penutupan projek — boleh dikonfig mengikut jenis projek.
 * Ini DATA PEMBANGUNAN, bukan polisi tetap DBKL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('project_type', 20);
            $table->string('category', 30);       // closure_evidence
            $table->string('document_type', 40);
            $table->boolean('is_required')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['project_type', 'category', 'document_type'], 'pdr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_document_requirements');
    }
};

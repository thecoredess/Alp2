<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tetapan aliran kerja mengikut jenis permohonan — boleh dikonfig.
 * Contoh: CSR tidak perlu semakan teknikal; DEVELOPMENT perlu.
 * Ini DATA PEMBANGUNAN, bukan polisi tetap DBKL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_workflow_settings', function (Blueprint $table) {
            $table->id();
            $table->string('application_type', 20)->unique();
            $table->boolean('requires_technical_review')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_workflow_settings');
    }
};

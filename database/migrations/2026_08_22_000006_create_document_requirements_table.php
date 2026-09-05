<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kewajipan dokumen mengikut jenis permohonan — boleh dikonfigur.
 * Bukan polisi tetap DBKL; nilai lalai diseed dan boleh diubah kemudian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('application_type', 20);
            $table->string('document_type', 40);
            $table->boolean('is_required')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['application_type', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};

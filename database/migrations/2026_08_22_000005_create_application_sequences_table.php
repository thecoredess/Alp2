<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jadual pembilang untuk nombor permohonan — selamat-serentak.
 * Baris dikunci (lockForUpdate) semasa penjanaan; TIDAK guna COUNT(*)+1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->string('type', 20);
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['year', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_sequences');
    }
};

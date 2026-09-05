<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pembilang nombor projek — selamat-serentak (lockForUpdate), bukan COUNT(*)+1. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_sequences', function (Blueprint $table) {
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
        Schema::dropIfExists('project_sequences');
    }
};

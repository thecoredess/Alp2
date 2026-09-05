<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** UR-M04-001 — senarai semak item Pegawai JP (JSON append-only pada rekod semakan). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_reviews', function (Blueprint $table) {
            $table->json('checklist')->nullable()->after('comments');
        });
    }

    public function down(): void
    {
        Schema::table('application_reviews', function (Blueprint $table) {
            $table->dropColumn('checklist');
        });
    }
};

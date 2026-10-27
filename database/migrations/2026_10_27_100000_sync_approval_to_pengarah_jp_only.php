<?php

use App\Models\ApprovalLevel;
use Database\Seeders\ApprovalLevelSeeder;
use Illuminate\Database\Migrations\Migration;

/** Kelulusan akhir Pengarah JP; PEPU makluman sahaja (tiada aras kelulusan). */
return new class extends Migration
{
    public function up(): void
    {
        (new ApprovalLevelSeeder)->run();
    }

    public function down(): void
    {
        // Tiada rollback — kekal pada model Pengarah JP sahaja.
    }
};

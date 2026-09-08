<?php

use App\Models\ApprovalLevel;
use Database\Seeders\ApprovalLevelSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Selaraskan matriks kelulusan lama (Pegawai/Pengarah/Jawatankuasa) ke URS v1.2:
 * Aras 1 Peraku (≤ RM3,000) → Aras 2 PEPU (> RM3,000).
 */
return new class extends Migration
{
    public function up(): void
    {
        (new ApprovalLevelSeeder)->run();

        ApprovalLevel::query()
            ->whereNull('financial_year_id')
            ->where('sequence', '>', 2)
            ->update(['active' => false]);

        // Nyahaktif baris lama yang masih aktif dengan nama/model terdahulu.
        ApprovalLevel::query()
            ->whereNull('financial_year_id')
            ->where('active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%Pegawai%')
                    ->orWhere('name', 'like', '%Jawatankuasa%')
                    ->orWhere(function ($q2) {
                        $q2->where('name', 'like', '%Pengarah%')
                            ->where('name', 'not like', '%TP/Pengarah JP%');
                    });
            })
            ->update(['active' => false]);
    }

    public function down(): void
    {
        // Tiada rollback — konfigurasi lama tidak disokong.
    }
};

<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\ApprovalLevel;
use Illuminate\Database\Seeder;

/**
 * ┌───────────────────────────────────────────────────────────────┐
 * │  DEVELOPMENT CONFIGURATION — NOT OFFICIAL DBKL POLICY          │
 * │  Ambang kelulusan contoh untuk pembangunan/ujian sahaja.      │
 * │  Had sebenar mesti ditetapkan oleh DBKL melalui UI matriks.   │
 * └───────────────────────────────────────────────────────────────┘
 *
 * Model berbilang-aras: jumlah menentukan band; kelulusan berurutan
 * diperlukan dari aras 1 hingga band tersebut.
 */
class ApprovalLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['Aras 1 — Peraku (TP/Pengarah JP)', '0.00', '3000.00', RoleName::PELULUS->value, 1],
            ['Aras 2 — PEPU / Pengurusan Tertinggi', '3000.01', null, RoleName::PENGURUSAN->value, 2],
        ];

        foreach ($levels as [$name, $min, $max, $role, $seq]) {
            ApprovalLevel::updateOrCreate(
                ['financial_year_id' => null, 'sequence' => $seq],
                [
                    'name' => $name,
                    'min_amount' => $min,
                    'max_amount' => $max,
                    'required_role' => $role,
                    'active' => true,
                ],
            );
        }

        // Nyahaktif aras lama di luar model URS v1.2 (Peraku → PEPU).
        ApprovalLevel::query()
            ->whereNull('financial_year_id')
            ->where('sequence', '>', 2)
            ->update(['active' => false]);
    }
}

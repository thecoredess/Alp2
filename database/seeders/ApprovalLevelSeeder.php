<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\ApprovalLevel;
use Illuminate\Database\Seeder;

/**
 * Kelulusan sumbangan ALP: Pengarah JP (pelulus) — satu aras untuk semua jumlah.
 * PEPU menerima makluman e-mel selepas kelulusan; tiada tindakan kelulusan dalam sistem.
 */
class ApprovalLevelSeeder extends Seeder
{
    public function run(): void
    {
        ApprovalLevel::updateOrCreate(
            ['financial_year_id' => null, 'sequence' => 1],
            [
                'name' => 'Kelulusan Pengarah JP',
                'min_amount' => '0.00',
                'max_amount' => null,
                'required_role' => RoleName::PELULUS->value,
                'active' => true,
            ],
        );

        ApprovalLevel::query()
            ->whereNull('financial_year_id')
            ->where('sequence', '>=', 2)
            ->update(['active' => false]);
    }
}

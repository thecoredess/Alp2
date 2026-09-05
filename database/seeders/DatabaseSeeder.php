<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,             // Peranan & kebenaran (WAJIB, production-safe)
            DocumentRequirementSeeder::class,        // Kewajipan dokumen permohonan (production-safe)
            ProjectDocumentRequirementSeeder::class, // Kewajipan dokumen penutupan projek (production-safe)
            WorkflowSettingsSeeder::class,           // Tetapan semakan teknikal (production-safe)
            SystemSettingSeeder::class,              // Polisi URS lalai ON (URS v1.2)
            DevSeeder::class,                 // Data pembangunan sahaja (DEV DATA)
            OfficialAlp2026Seeder::class,     // 15 ALP rasmi DBKL (Jun 2026) + akaun alp01–alp15
        ]);
    }
}

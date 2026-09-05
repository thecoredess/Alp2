<?php

namespace App\Services\Project;

use App\Enums\ApplicationType;
use Illuminate\Support\Facades\DB;

/**
 * Penjana nombor projek selamat-serentak (lockForUpdate; bukan COUNT(*)+1).
 * Mesti dipanggil dalam transaksi. Format: PRJ/CSR/2026/0001.
 */
class ProjectNumberGenerator
{
    public function next(ApplicationType $type, int $year): string
    {
        $code = $type->code();

        DB::table('project_sequences')->insertOrIgnore([
            'year' => $year, 'type' => $code, 'last_number' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $row = DB::table('project_sequences')
            ->where('year', $year)->where('type', $code)->lockForUpdate()->first();

        $next = ((int) $row->last_number) + 1;
        DB::table('project_sequences')->where('id', $row->id)->update(['last_number' => $next, 'updated_at' => now()]);

        return sprintf('PRJ/%s/%d/%04d', $code, $year, $next);
    }
}

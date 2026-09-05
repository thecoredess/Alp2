<?php

namespace App\Services\Application;

use App\Enums\ApplicationType;
use Illuminate\Support\Facades\DB;

/**
 * Penjana nombor permohonan yang selamat-serentak.
 *
 * Menggunakan jadual pembilang `application_sequences` dengan kunci baris
 * (lockForUpdate). TIDAK menggunakan COUNT(*)+1. Mesti dipanggil dalam
 * transaksi pangkalan data yang sedang berjalan.
 */
class ApplicationNumberGenerator
{
    /** Format: ALP/CSR/2026/0001 */
    public function next(ApplicationType $type, int $year): string
    {
        $code = $type->code();

        // Pastikan baris pembilang wujud (atomik melalui unique key).
        DB::table('application_sequences')->insertOrIgnore([
            'year' => $year,
            'type' => $code,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kunci baris & tambah.
        $row = DB::table('application_sequences')
            ->where('year', $year)
            ->where('type', $code)
            ->lockForUpdate()
            ->first();

        $next = ((int) $row->last_number) + 1;

        DB::table('application_sequences')
            ->where('id', $row->id)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return sprintf('ALP/%s/%d/%04d', $code, $year, $next);
    }
}

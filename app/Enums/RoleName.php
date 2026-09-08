<?php

namespace App\Enums;

/**
 * Senarai peranan (role) sistem ALP DBKL.
 * Nilai enum digunakan sebagai nama role dalam Spatie Permission.
 */
enum RoleName: string
{
    case SUPER_ADMIN = 'super_admin';
    case SYSTEM_ADMIN = 'system_admin';
    case ALP = 'alp';
    case URUSSETIA_ALP = 'urussetia_alp';
    case PEGAWAI_URUSSETIA = 'pegawai_urussetia';
    case PEGAWAI_KEWANGAN = 'pegawai_kewangan';
    case PEGAWAI_JKEW = 'pegawai_jkew';
    case PEGAWAI_TEKNIKAL = 'pegawai_teknikal';
    case PELULUS = 'pelulus';
    case PENGURUSAN = 'pengurusan';

    /**
     * Label paparan selaras URS v1.2 TBL-03 (padanan role sistem sedia ada).
     * Nilai Spatie (`value`) tidak diubah supaya data/permission kekal.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin (teknikal)',
            self::SYSTEM_ADMIN => 'Pentadbir Sistem / Admin JP',
            self::ALP => 'ALP (Pemohon)',
            self::URUSSETIA_ALP => 'Urus Setia ALP',
            self::PEGAWAI_URUSSETIA => 'Pegawai JP',
            self::PEGAWAI_KEWANGAN => 'Kerani Kewangan JP',
            self::PEGAWAI_JKEW => 'JKEW',
            self::PEGAWAI_TEKNIKAL => 'Pegawai Teknikal (legacy)',
            self::PELULUS => 'TP / Pengarah JP (Peraku)',
            self::PENGURUSAN => 'PEPU / Pengurusan Tertinggi',
        };
    }

    /** Semua nilai role sebagai array string. */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}

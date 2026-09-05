<?php

namespace App\Enums;

/**
 * Kitaran hayat tahun kewangan.
 * DRAFT  → baru dicipta, belum dibuka.
 * OPEN   → dibuka, boleh terima peruntukan/permohonan (tetapi bukan tahun semasa lalai).
 * ACTIVE → tahun kewangan semasa (hanya satu boleh aktif pada satu masa).
 * CLOSED → ditutup, data dilindungi daripada perubahan.
 */
enum FinancialYearStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case ACTIVE = 'active';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::OPEN => 'Dibuka',
            self::ACTIVE => 'Aktif',
            self::CLOSED => 'Ditutup',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-700',
            self::OPEN => 'bg-blue-100 text-blue-800',
            self::ACTIVE => 'bg-green-100 text-green-800',
            self::CLOSED => 'bg-navy-100 text-navy-800',
        };
    }

    /** Adakah tahun ini boleh diubah (bukan ditutup)? */
    public function isEditable(): bool
    {
        return $this !== self::CLOSED;
    }
}

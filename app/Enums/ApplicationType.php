<?php

namespace App\Enums;

/** Jenis permohonan utama (dikawal ketat — bukan teks bebas). */
enum ApplicationType: string
{
    case CSR = 'csr';
    case DEVELOPMENT = 'development';

    public function label(): string
    {
        return match ($this) {
            self::CSR => 'CSR',
            self::DEVELOPMENT => 'Pembangunan',
        };
    }

    /** Kod ringkas untuk nombor permohonan (cth ALP/CSR/2026/0001). */
    public function code(): string
    {
        return match ($this) {
            self::CSR => 'CSR',
            self::DEVELOPMENT => 'DEV',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::CSR => 'bg-royal-100 text-royal-800',
            self::DEVELOPMENT => 'bg-navy-100 text-navy-800',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}

<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
        };
    }

    /** Warna badge (kelas Tailwind). */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-green-100 text-green-800',
            self::INACTIVE => 'bg-gray-100 text-gray-700',
        };
    }
}

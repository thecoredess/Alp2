<?php

namespace App\Enums;

enum ApprovalDecision: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case RETURN_FOR_REVISION = 'return_for_revision';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => 'Diluluskan',
            self::REJECTED => 'Ditolak',
            self::RETURN_FOR_REVISION => 'Kembalikan Untuk Pembetulan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::APPROVED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::RETURN_FOR_REVISION => 'bg-orange-100 text-orange-800',
        };
    }
}

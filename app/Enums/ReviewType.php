<?php

namespace App\Enums;

enum ReviewType: string
{
    case SECRETARIAT = 'secretariat';
    case FINANCE = 'finance';
    case TECHNICAL = 'technical';

    public function label(): string
    {
        return match ($this) {
            self::SECRETARIAT => 'Semakan Urusetia JP',
            self::FINANCE => 'Semakan Kewangan (legacy)',
            self::TECHNICAL => 'Semakan Teknikal (legacy)',
        };
    }

    /** Permission yang diperlukan untuk membuat semakan jenis ini. */
    public function permission(): string
    {
        return match ($this) {
            self::SECRETARIAT => 'applications.review.secretariat',
            self::FINANCE => 'applications.review.finance',
            self::TECHNICAL => 'applications.review.technical',
        };
    }
}

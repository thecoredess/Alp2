<?php

namespace App\Enums;

enum ReviewDecision: string
{
    case RECOMMEND = 'recommend';
    case RETURN_FOR_REVISION = 'return_for_revision';
    case NOT_RECOMMENDED = 'not_recommended';

    public function label(): string
    {
        return match ($this) {
            self::RECOMMEND => 'Disyorkan',
            self::RETURN_FOR_REVISION => 'Kembalikan Untuk Pembetulan',
            self::NOT_RECOMMENDED => 'Tidak Disyorkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::RECOMMEND => 'bg-green-100 text-green-800',
            self::RETURN_FOR_REVISION => 'bg-orange-100 text-orange-800',
            self::NOT_RECOMMENDED => 'bg-red-100 text-red-800',
        };
    }

    /** Semakan yang memajukan aliran kerja (RECOMMEND & NOT_RECOMMENDED bersifat nasihat). */
    public function advancesWorkflow(): bool
    {
        return $this !== self::RETURN_FOR_REVISION;
    }
}

<?php

namespace App\Enums;

/** Jenis program sumbangan URS v1.2 (BR-011). */
enum ProgramCategory: string
{
    case KOMUNITI = 'komuniti';
    case SUKAN = 'sukan';
    case PENDIDIKAN = 'pendidikan';
    case KEMASYARAKATAN = 'kemasyarakatan';

    public function label(): string
    {
        return match ($this) {
            self::KOMUNITI => 'Program komuniti',
            self::SUKAN => 'Sukan',
            self::PENDIDIKAN => 'Pendidikan',
            self::KEMASYARAKATAN => 'Aktiviti kemasyarakatan',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}

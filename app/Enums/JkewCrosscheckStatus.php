<?php

namespace App\Enums;

/** Hasil semakan silang JKEW (BR-013 / UR-M06-003). */
enum JkewCrosscheckStatus: string
{
    case PENDING = 'pending';
    case CLEAR = 'clear';
    case FLAGGED = 'flagged';
    case NOT_REQUIRED = 'not_required';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu semakan silang',
            self::CLEAR => 'Tiada rekod sumbangan lain',
            self::FLAGGED => 'Terdapat rekod — perlu perhatian',
            self::NOT_REQUIRED => 'Tidak berkenaan',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}

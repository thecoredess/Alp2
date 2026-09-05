<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case DELAYED = 'delayed';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'Belum Bermula',
            self::IN_PROGRESS => 'Dalam Pelaksanaan',
            self::DELAYED => 'Lewat',
            self::COMPLETED => 'Selesai',
            self::CLOSED => 'Ditutup',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'bg-gray-100 text-gray-700',
            self::IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::DELAYED => 'bg-orange-100 text-orange-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::CLOSED => 'bg-navy-100 text-navy-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
        };
    }

    /** Boleh dikemas kini secara operasi (bukan ditutup/dibatalkan). */
    public function isOperationallyEditable(): bool
    {
        return ! in_array($this, [self::CLOSED, self::CANCELLED], true);
    }

    /** Status di mana kerja sedang berjalan. */
    public function isActive(): bool
    {
        return in_array($this, [self::IN_PROGRESS, self::DELAYED], true);
    }
}

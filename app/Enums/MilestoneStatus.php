<?php

namespace App\Enums;

enum MilestoneStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case DELAYED = 'delayed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Belum Mula',
            self::IN_PROGRESS => 'Sedang Berjalan',
            self::COMPLETED => 'Selesai',
            self::DELAYED => 'Lewat',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-gray-100 text-gray-700',
            self::IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::DELAYED => 'bg-orange-100 text-orange-800',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}

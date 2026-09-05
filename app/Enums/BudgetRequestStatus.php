<?php

namespace App\Enums;

enum BudgetRequestStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case REVISION_REQUIRED = 'revision_required';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::PENDING_APPROVAL => 'Menunggu Kelulusan',
            self::REVISION_REQUIRED => 'Perlu Pembetulan',
            self::APPROVED => 'Diluluskan',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-700',
            self::PENDING_APPROVAL => 'bg-amber-100 text-amber-800',
            self::REVISION_REQUIRED => 'bg-orange-100 text-orange-800',
            self::APPROVED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::CANCELLED => 'bg-gray-200 text-gray-700',
        };
    }

    /** Boleh disunting oleh maker (draf atau perlu pembetulan). */
    public function isEditableByMaker(): bool
    {
        return $this === self::DRAFT || $this === self::REVISION_REQUIRED;
    }

    /** Cadangan aktif yang belum diselesaikan (untuk semakan duplikasi/pending). */
    public static function activeValues(): array
    {
        return [self::DRAFT->value, self::PENDING_APPROVAL->value, self::REVISION_REQUIRED->value];
    }
}

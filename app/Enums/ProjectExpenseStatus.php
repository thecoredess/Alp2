<?php

namespace App\Enums;

enum ProjectExpenseStatus: string
{
    case DRAFT = 'draft';
    case PENDING_VERIFICATION = 'pending_verification';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case REVISION_REQUIRED = 'revision_required';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::PENDING_VERIFICATION => 'Menunggu Pengesahan',
            self::VERIFIED => 'Disahkan',
            self::REJECTED => 'Ditolak',
            self::REVISION_REQUIRED => 'Perlu Pembetulan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-700',
            self::PENDING_VERIFICATION => 'bg-amber-100 text-amber-800',
            self::VERIFIED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::REVISION_REQUIRED => 'bg-orange-100 text-orange-800',
        };
    }

    public function isEditableByMaker(): bool
    {
        return $this === self::DRAFT || $this === self::REVISION_REQUIRED;
    }

    /** Status yang belum diselesaikan (untuk halangan penutupan projek). */
    public static function unresolvedValues(): array
    {
        return [self::DRAFT->value, self::PENDING_VERIFICATION->value, self::REVISION_REQUIRED->value];
    }
}

<?php

namespace App\Enums;

enum RefundStatus: string
{
    case DRAFT = 'draft';
    case PENDING_VERIFICATION = 'pending_verification';
    case REVISION_REQUIRED = 'revision_required';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::PENDING_VERIFICATION => 'Menunggu Pengesahan',
            self::REVISION_REQUIRED => 'Perlu Pembetulan',
            self::VERIFIED => 'Disahkan',
            self::REJECTED => 'Ditolak',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-700',
            self::PENDING_VERIFICATION => 'bg-amber-100 text-amber-800',
            self::REVISION_REQUIRED => 'bg-orange-100 text-orange-800',
            self::VERIFIED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
        };
    }

    public function isEditableByMaker(): bool
    {
        return $this === self::DRAFT || $this === self::REVISION_REQUIRED;
    }
}

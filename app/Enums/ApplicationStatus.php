<?php

namespace App\Enums;

/**
 * Kitaran hayat permohonan.
 *
 * Fasa 3 hanya melaksanakan DRAFT dan SUBMITTED secara aktif. Nilai lain
 * ditakrifkan untuk peta jalan (Fasa 4–5) supaya pengiraan "Pending Request"
 * kekal betul apabila status semakan/kelulusan ditambah kelak — TETAPI logik
 * peralihan untuknya belum dilaksanakan.
 */
enum ApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    // Ditakrifkan untuk masa depan (belum dilaksanakan pada Fasa 3):
    case UNDER_SECRETARIAT_REVIEW = 'under_secretariat_review';
    case UNDER_FINANCE_REVIEW = 'under_finance_review';
    case UNDER_TECHNICAL_REVIEW = 'under_technical_review';
    case PENDING_APPROVAL = 'pending_approval';
    case REVISION_REQUIRED = 'revision_required';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::SUBMITTED => 'Menunggu Semakan Admin JP',
            self::UNDER_SECRETARIAT_REVIEW => 'Menunggu Perakuan Pegawai JP',
            self::UNDER_FINANCE_REVIEW => 'Semakan Kewangan (legacy)',
            self::UNDER_TECHNICAL_REVIEW => 'Semakan Teknikal (legacy)',
            self::PENDING_APPROVAL => 'Menunggu Peraku PEPU',
            self::REVISION_REQUIRED => 'Perlu Pembetulan',
            self::APPROVED => 'Diluluskan',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::IN_PROGRESS => 'Dalam Pelaksanaan',
            self::COMPLETED => 'Selesai',
            self::CLOSED => 'Ditutup',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-700',
            self::SUBMITTED => 'bg-blue-100 text-blue-800',
            self::UNDER_SECRETARIAT_REVIEW,
            self::UNDER_FINANCE_REVIEW,
            self::UNDER_TECHNICAL_REVIEW => 'bg-blue-100 text-blue-800',
            self::PENDING_APPROVAL => 'bg-amber-100 text-amber-800',
            self::REVISION_REQUIRED => 'bg-orange-100 text-orange-800',
            self::APPROVED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::CANCELLED => 'bg-gray-200 text-gray-700',
            self::IN_PROGRESS => 'bg-purple-100 text-purple-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::CLOSED => 'bg-navy-100 text-navy-800',
        };
    }

    /** Boleh disunting oleh pemilik semasa DRAFT atau REVISION_REQUIRED. */
    public function isEditableByOwner(): bool
    {
        return $this === self::DRAFT || $this === self::REVISION_REQUIRED;
    }

    /**
     * Status yang dikira sebagai "Pending Request" (permohonan aktif yang belum
     * diluluskan/ditolak/dibatalkan). Fasa 3: hanya SUBMITTED wujud dalam data;
     * status semakan disertakan supaya pengiraan kekal betul pada fasa akan datang.
     *
     * @return array<int, string>
     */
    public static function pendingRequestValues(): array
    {
        return [
            self::SUBMITTED->value,
            self::UNDER_SECRETARIAT_REVIEW->value,
            self::UNDER_FINANCE_REVIEW->value,
            self::UNDER_TECHNICAL_REVIEW->value,
            self::PENDING_APPROVAL->value,
            self::REVISION_REQUIRED->value,
        ];
    }
}

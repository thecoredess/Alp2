<?php

namespace App\Enums;

/** Status semakan laporan aktiviti (M07) — selepas ALP muat naik. */
enum ReportCardStatus: string
{
    case DRAFT = 'draft';

    case AWAITING_ADMIN_JP = 'awaiting_admin_jp';
    case AWAITING_PEGAWAI_JP = 'awaiting_pegawai_jp';
    case APPROVED = 'approved';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf — belum dihantar',
            self::AWAITING_ADMIN_JP => 'Menunggu semakan Admin JP',
            self::AWAITING_PEGAWAI_JP => 'Menunggu pengesahan Pegawai JP',
            self::APPROVED => 'Disahkan',
            self::RETURNED => 'Dikembalikan untuk pembetulan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-amber-100 text-amber-800',
            self::AWAITING_ADMIN_JP => 'bg-blue-100 text-blue-800',
            self::AWAITING_PEGAWAI_JP => 'bg-indigo-100 text-indigo-800',
            self::APPROVED => 'bg-green-100 text-green-800',
            self::RETURNED => 'bg-orange-100 text-orange-800',
        };
    }
}

<?php

namespace App\Enums;

/**
 * Status pembayaran/baucar URS bagi permohonan yang telah diluluskan.
 * Metadata operasi sahaja — TIDAK mempos ke ledger (komitmen kekal; belanja projek berasingan).
 */
enum ApplicationPaymentStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case VOUCHER_PREPARED = 'voucher_prepared';
    case SENT_TO_JKEW = 'sent_to_jkew';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::VOUCHER_PREPARED => 'Baucar Disedia',
            self::SENT_TO_JKEW => 'Dihantar ke JKEW',
            self::PAID => 'Telah Dibayar',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'bg-amber-100 text-amber-800',
            self::VOUCHER_PREPARED => 'bg-blue-100 text-blue-800',
            self::SENT_TO_JKEW => 'bg-indigo-100 text-indigo-800',
            self::PAID => 'bg-green-100 text-green-800',
            self::CANCELLED => 'bg-gray-200 text-gray-700',
        };
    }

    /** @return list<string> */
    public static function openValues(): array
    {
        return [
            self::PENDING_PAYMENT->value,
            self::VOUCHER_PREPARED->value,
            self::SENT_TO_JKEW->value,
        ];
    }
}

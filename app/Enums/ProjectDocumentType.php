<?php

namespace App\Enums;

/**
 * Jenis dokumen projek (bukti perbelanjaan, penutupan, dan refund).
 * Dikawal ketat — bukan teks bebas.
 */
enum ProjectDocumentType: string
{
    // Bukti perbelanjaan
    case INVOICE = 'invoice';
    case RECEIPT = 'receipt';
    case PAYMENT_VOUCHER = 'payment_voucher';
    case PURCHASE_ORDER = 'purchase_order';
    case DELIVERY_ORDER = 'delivery_order';
    case APPROVAL_REFERENCE = 'approval_reference';
    // Bukti penutupan
    case FINAL_REPORT = 'final_report';
    case BEFORE_PHOTO = 'before_photo';
    case AFTER_PHOTO = 'after_photo';
    case COMPLETION_PHOTO = 'completion_photo';
    case COMPLETION_CERTIFICATE = 'completion_certificate';
    case COMPLETION_EVIDENCE = 'completion_evidence';
    case PROGRAM_ATTENDANCE = 'program_attendance';
    case IMPACT_EVIDENCE = 'impact_evidence';
    case TECHNICAL_COMPLETION = 'technical_completion';
    // Bukti refund
    case BANK_SLIP = 'bank_slip';
    case CREDIT_NOTE = 'credit_note';
    case REFUND_RECEIPT = 'refund_receipt';
    case OFFICIAL_LETTER = 'official_letter';
    // Umum
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::INVOICE => 'Invois',
            self::RECEIPT => 'Resit',
            self::PAYMENT_VOUCHER => 'Baucar Bayaran',
            self::PURCHASE_ORDER => 'Pesanan Belian',
            self::DELIVERY_ORDER => 'Nota Serahan',
            self::APPROVAL_REFERENCE => 'Rujukan Kelulusan',
            self::FINAL_REPORT => 'Laporan Akhir',
            self::BEFORE_PHOTO => 'Gambar Sebelum',
            self::AFTER_PHOTO => 'Gambar Selepas',
            self::COMPLETION_PHOTO => 'Gambar Penyiapan',
            self::COMPLETION_CERTIFICATE => 'Sijil Penyiapan',
            self::COMPLETION_EVIDENCE => 'Bukti Penyiapan',
            self::PROGRAM_ATTENDANCE => 'Kehadiran Program',
            self::IMPACT_EVIDENCE => 'Bukti Impak',
            self::TECHNICAL_COMPLETION => 'Dokumen Teknikal Penyiapan',
            self::BANK_SLIP => 'Slip Bank',
            self::CREDIT_NOTE => 'Nota Kredit',
            self::REFUND_RECEIPT => 'Resit Refund',
            self::OFFICIAL_LETTER => 'Surat Rasmi',
            self::OTHER => 'Lain-lain',
        };
    }

    /** Pilihan jenis dokumen untuk sesuatu kategori. */
    public static function forCategory(string $category): array
    {
        $map = [
            'expense_evidence' => [self::INVOICE, self::RECEIPT, self::PAYMENT_VOUCHER, self::PURCHASE_ORDER, self::DELIVERY_ORDER, self::APPROVAL_REFERENCE, self::OTHER],
            'closure_evidence' => [self::FINAL_REPORT, self::COMPLETION_PHOTO, self::COMPLETION_CERTIFICATE, self::COMPLETION_EVIDENCE, self::BEFORE_PHOTO, self::AFTER_PHOTO, self::PROGRAM_ATTENDANCE, self::IMPACT_EVIDENCE, self::TECHNICAL_COMPLETION, self::OTHER],
            'refund_evidence' => [self::BANK_SLIP, self::CREDIT_NOTE, self::REFUND_RECEIPT, self::OFFICIAL_LETTER, self::OTHER],
        ];

        return collect($map[$category] ?? [])->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}

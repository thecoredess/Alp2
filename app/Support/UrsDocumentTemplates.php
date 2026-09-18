<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Templat dokumen URS M10 (UR-M10-002) — diselenggara Admin JP melalui tetapan.
 */
final class UrsDocumentTemplates
{
    public const KEY_LETTER_HEADER = 'template.letter_header';

    public const KEY_LETTER_BODY = 'template.letter_body';

    public const KEY_LETTER_FOOTER = 'template.letter_footer';

    public const KEY_LETTER_REFERENCE = 'template.letter_reference';

    public const KEY_LETTER_SIGNATORY_NAME = 'template.letter_signatory_name';

    public const KEY_LETTER_SIGNATORY_TITLE = 'template.letter_signatory_title';

    public const KEY_LETTER_SIGNATORY_ON_BEHALF = 'template.letter_signatory_on_behalf';

    public const KEY_LETTER_CONTACT_PHONE = 'template.letter_contact_phone';

    public const KEY_LETTER_DBAYAR_URL = 'template.letter_dbayar_url';

    public const KEY_BORANG_HEADER = 'template.borang_header';

    public const KEY_BORANG_FOOTER = 'template.borang_footer';

    public static function letterHeader(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_HEADER, self::defaults()[self::KEY_LETTER_HEADER]);
    }

    public static function letterBody(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_BODY, self::defaults()[self::KEY_LETTER_BODY]);
    }

    public static function letterFooter(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_FOOTER, self::defaults()[self::KEY_LETTER_FOOTER]);
    }

    public static function letterReferenceBase(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_REFERENCE, self::defaults()[self::KEY_LETTER_REFERENCE]);
    }

    public static function letterSignatoryName(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_SIGNATORY_NAME, self::defaults()[self::KEY_LETTER_SIGNATORY_NAME]);
    }

    public static function letterSignatoryTitle(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_SIGNATORY_TITLE, self::defaults()[self::KEY_LETTER_SIGNATORY_TITLE]);
    }

    public static function letterSignatoryOnBehalf(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_SIGNATORY_ON_BEHALF, self::defaults()[self::KEY_LETTER_SIGNATORY_ON_BEHALF]);
    }

    public static function letterContactPhone(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_CONTACT_PHONE, self::defaults()[self::KEY_LETTER_CONTACT_PHONE]);
    }

    public static function letterDbayarUrl(): string
    {
        return (string) SystemSetting::get(self::KEY_LETTER_DBAYAR_URL, self::defaults()[self::KEY_LETTER_DBAYAR_URL]);
    }

    public static function borangHeader(): string
    {
        return (string) SystemSetting::get(self::KEY_BORANG_HEADER, self::defaults()[self::KEY_BORANG_HEADER]);
    }

    public static function borangFooter(): string
    {
        return (string) SystemSetting::get(self::KEY_BORANG_FOOTER, self::defaults()[self::KEY_BORANG_FOOTER]);
    }

    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            self::KEY_LETTER_HEADER => 'Dewan Bandaraya Kuala Lumpur',
            self::KEY_LETTER_BODY => 'Permohonan di atas telah DILULUSKAN melalui aliran kelulusan sistem ALP DBKL. Jumlah diluluskan ialah snapshot pada masa kelulusan dan direkod dalam lejar bajet sebagai komitmen.',
            self::KEY_LETTER_FOOTER => 'Dokumen dijana oleh Sistem ALP DBKL. Sila rujuk rekod sistem untuk pengesahan rasmi.',
            self::KEY_LETTER_REFERENCE => 'DBKL.JP.100-19/1/3 Jld. 4',
            self::KEY_LETTER_SIGNATORY_NAME => 'NORHASLINDA BINTI NORDIN',
            self::KEY_LETTER_SIGNATORY_TITLE => 'Pengarah',
            self::KEY_LETTER_SIGNATORY_ON_BEHALF => 'Jabatan Pentadbiran b.p. Datuk Bandar Kuala Lumpur',
            self::KEY_LETTER_CONTACT_PHONE => '03-2617 9852/03-2617 9853',
            self::KEY_LETTER_DBAYAR_URL => 'https://dbayar.dbkl.gov.my/',
            self::KEY_BORANG_HEADER => 'Dewan Bandaraya Kuala Lumpur',
            self::KEY_BORANG_FOOTER => 'Borang Penyaluran Sumbangan — Sistem ALP DBKL',
        ];
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return [
            self::KEY_LETTER_HEADER => self::letterHeader(),
            self::KEY_LETTER_BODY => self::letterBody(),
            self::KEY_LETTER_FOOTER => self::letterFooter(),
            self::KEY_LETTER_REFERENCE => self::letterReferenceBase(),
            self::KEY_LETTER_SIGNATORY_NAME => self::letterSignatoryName(),
            self::KEY_LETTER_SIGNATORY_TITLE => self::letterSignatoryTitle(),
            self::KEY_LETTER_SIGNATORY_ON_BEHALF => self::letterSignatoryOnBehalf(),
            self::KEY_LETTER_CONTACT_PHONE => self::letterContactPhone(),
            self::KEY_LETTER_DBAYAR_URL => self::letterDbayarUrl(),
            self::KEY_BORANG_HEADER => self::borangHeader(),
            self::KEY_BORANG_FOOTER => self::borangFooter(),
        ];
    }
}

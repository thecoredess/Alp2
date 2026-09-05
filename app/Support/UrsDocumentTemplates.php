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
            self::KEY_LETTER_BODY => 'Permohonan di atas telah DILULUSKAN melalui aliran kelulusan sistem ALP DBKL. Jumlah diluluskan ialah snapshot pada masa kelulusan dan direkod dalam ledger bajet sebagai komitmen.',
            self::KEY_LETTER_FOOTER => 'Dokumen dijana oleh Sistem ALP DBKL. Sila rujuk rekod sistem untuk pengesahan rasmi.',
            self::KEY_BORANG_HEADER => 'Dewan Bandaraya Kuala Lumpur',
            self::KEY_BORANG_FOOTER => 'Borang Penyaluran Sumbangan (BR-020 / TBL-10) — Sistem ALP DBKL',
        ];
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return [
            self::KEY_LETTER_HEADER => self::letterHeader(),
            self::KEY_LETTER_BODY => self::letterBody(),
            self::KEY_LETTER_FOOTER => self::letterFooter(),
            self::KEY_BORANG_HEADER => self::borangHeader(),
            self::KEY_BORANG_FOOTER => self::borangFooter(),
        ];
    }
}

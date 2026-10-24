<?php

namespace App\Support;

use App\Models\Application;

/** Kertas Kerja — medan borang dalam talian & panduan ALP. */
final class KertasKerjaGuide
{
    public const MIN_CHARS = 30;

    public const MAX_CHARS = 8000;

    public static function intro(): string
    {
        return 'Isi maklumat program di bawah (Kertas Kerja dalam talian). Medan g) — tiada perlu muat naik fail Kertas Kerja berasingan melainkan JP meminta salinan PDF.';
    }

    /**
     * @return list<array{key: string, label: string, hint: string, placeholder: string, rows: int}>
     */
    public static function formFields(): array
    {
        return [
            [
                'key' => 'program_background',
                'label' => 'Latar belakang & masalah / keperluan komuniti',
                'hint' => 'Terangkan situasi setempat dan kenapa program diperlukan.',
                'placeholder' => 'Contoh: Peningkatan kos sara hidup di PPR X; asnaf memerlukan bantuan berkala…',
                'rows' => 4,
            ],
            [
                'key' => 'program_objectives',
                'label' => 'Objektif program',
                'hint' => 'Nyatakan matlamat utama program secara jelas.',
                'placeholder' => 'Contoh: Mengurangkan bebanan asnaf melalui bantuan barang dapur…',
                'rows' => 3,
            ],
            [
                'key' => 'program_beneficiaries',
                'label' => 'Sasaran penerima manfaat',
                'hint' => 'Siapa benefisiari (kumpulan, anggaran bilangan)?',
                'placeholder' => 'Contoh: 80 keluarga asnaf & warga emas dalam kawasan PPR…',
                'rows' => 3,
            ],
            [
                'key' => 'program_schedule',
                'label' => 'Jadual tarikh aktiviti & lokasi (WP Kuala Lumpur)',
                'hint' => 'Senaraikan tarikh/masa aktiviti dan alamat lokasi program (mesti WP KL).',
                'placeholder' => "Contoh:\n15/12/2026 — Taklimat & agihan — Dewan PPR Sentul\n20/12/2026 — Gotong-royong — Blok A PPR Sentul",
                'rows' => 4,
            ],
            [
                'key' => 'program_budget_breakdown',
                'label' => 'Pecahan perbelanjaan',
                'hint' => 'Item kos dan jumlah (RM); jumlah keseluruhan selari dengan medan f).',
                'placeholder' => "Contoh:\nBahan makanan — RM 1,500\nPencuci mulut & minuman — RM 800\nPengangkutan sukarelawan — RM 200\nJumlah — RM 2,500",
                'rows' => 5,
            ],
            [
                'key' => 'program_impact',
                'label' => 'Impak program',
                'hint' => 'Kesan jangka pendek/panjang kepada komuniti.',
                'placeholder' => 'Contoh: Membantu asnaf mendapat bantuan berkala dan mengukuhkan jalinan komuniti…',
                'rows' => 3,
            ],
        ];
    }

    /** @return list<string> */
    public static function fieldKeys(): array
    {
        return array_column(self::formFields(), 'key');
    }

    /** @return array<string, list<string|object>> */
    public static function validationRules(): array
    {
        $rules = [];
        foreach (self::fieldKeys() as $key) {
            $rules[$key] = ['required', 'string', 'min:'.self::MIN_CHARS, 'max:'.self::MAX_CHARS];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public static function validationAttributes(): array
    {
        $attrs = [];
        foreach (self::formFields() as $field) {
            $attrs[$field['key']] = strtolower($field['label']);
        }

        return $attrs;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public static function normalizedAttributes(array $data): array
    {
        $out = [];
        foreach (self::fieldKeys() as $key) {
            $out[$key] = trim((string) ($data[$key] ?? ''));
        }
        $out['purpose'] = self::summarizePurpose($out);

        return $out;
    }

    /**
     * @param  array<string, string>  $kertasKerja  keyed by fieldKeys
     */
    public static function summarizePurpose(array $kertasKerja): string
    {
        $objectives = trim($kertasKerja['program_objectives'] ?? '');
        if ($objectives !== '') {
            return mb_strlen($objectives) > 1000 ? mb_substr($objectives, 0, 997).'…' : $objectives;
        }

        $background = trim($kertasKerja['program_background'] ?? '');

        return mb_strlen($background) > 1000 ? mb_substr($background, 0, 997).'…' : $background;
    }

    public static function isComplete(Application $application): bool
    {
        foreach (self::fieldKeys() as $key) {
            $value = trim((string) ($application->{$key} ?? ''));
            if (mb_strlen($value) < self::MIN_CHARS) {
                return false;
            }
        }

        return true;
    }

    /** @return list<string> */
    public static function themes(): array
    {
        return array_column(self::formFields(), 'label');
    }

    /** @return list<string> */
    public static function minimumSections(): array
    {
        return [
            'Latar belakang dan masalah / keperluan komuniti',
            'Objektif dan sasaran penerima manfaat',
            'Jadual tarikh aktiviti dan lokasi (Wilayah Persekutuan Kuala Lumpur)',
            'Anggaran / pecahan perbelanjaan selari dengan jumlah dimohon (medan f)',
        ];
    }

    public static function notesForAssociationGuide(): string
    {
        return 'Diisi dalam borang permohonan (Langkah 1) — '.implode(', ', self::themes()).'.';
    }

    /** @return array<string, string> */
    public static function samplePayload(): array
    {
        $payload = [];
        foreach (self::formFields() as $field) {
            $payload[$field['key']] = 'Ujian '.$field['label'].' — '.str_repeat('x', 35);
        }

        return $payload;
    }
}

<?php

namespace App\Support;

/**
 * Penjana PDF satu muka surat yang sah untuk data simulasi/demo.
 *
 * Bukan untuk dokumen sebenar — hanya supaya pratonton lampiran data
 * simulasi boleh dipaparkan seperti fail muat naik biasa.
 */
class PlaceholderPdf
{
    /** @param  list<string>  $lines */
    public static function make(string $title, array $lines = []): string
    {
        $stream = "BT\n/F1 18 Tf\n60 780 Td\n(".self::escape($title).") Tj\nET\n";

        $y = 745;
        foreach ($lines as $line) {
            $stream .= "BT\n/F1 11 Tf\n60 {$y} Td\n(".self::escape($line).") Tj\nET\n";
            $y -= 18;
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                .'/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            5 => '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "{$num} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $size = count($objects) + 1;

        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach (array_keys($objects) as $num) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$num]);
        }

        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    /** Helvetica hanya menyokong ASCII; aksara lain diganti. */
    private static function escape(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? '';

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}

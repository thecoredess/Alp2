<?php

namespace App\Support\Export;

use Illuminate\Http\Response;

/**
 * Penjana PDF ringkas tulen — tanpa pakej luar. Menghasilkan bait PDF sah
 * (application/pdf) untuk laporan bertabular: kepala surat DBKL, tajuk, meta
 * penapis, dan jadual dengan penomboran halaman. Ringkas (bukan pixel-perfect).
 */
class PdfWriter
{
    private array $objects = [];
    private float $pageW;
    private float $pageH;
    private float $margin = 40.0;
    private array $pages = [];   // setiap: content stream string
    private string $content = '';
    private float $y;

    public function __construct(private readonly bool $landscape = false)
    {
        // A4 (points): 595.28 x 841.89.
        $this->pageW = $landscape ? 841.89 : 595.28;
        $this->pageH = $landscape ? 595.28 : 841.89;
    }

    public static function response(ReportData $data, string $filename, bool $landscape = false): Response
    {
        $binary = (new self($landscape))->render($data);

        return new Response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
            'Content-Length' => (string) strlen($binary),
        ]);
    }

    public function render(ReportData $data): string
    {
        $this->y = $this->pageH - $this->margin;

        // Kepala surat.
        $this->text('DBKL — Dewan Bandaraya Kuala Lumpur', $this->margin, $this->y, 12, true);
        $this->y -= 15;
        $this->text('Sistem Ahli Lembaga Penasihat', $this->margin, $this->y, 9, false);
        $this->y -= 20;
        $this->text($data->title, $this->margin, $this->y, 13, true);
        $this->y -= 16;

        foreach ($data->meta as $k => $v) {
            $this->text($k.': '.$v, $this->margin, $this->y, 8, false);
            $this->y -= 11;
        }
        $this->y -= 8;

        // Lebar lajur berkadar.
        $usable = $this->pageW - 2 * $this->margin;
        $weights = array_map(fn ($c) => max(1, $c['width'] ?? 3), $data->columns);
        $totalW = array_sum($weights);
        $x = [];
        $cx = $this->margin;
        foreach ($weights as $i => $w) {
            $x[$i] = $cx;
            $cx += $usable * $w / $totalW;
        }
        $colW = fn ($i) => $usable * $weights[$i] / $totalW;

        $this->drawHeader($data, $x, $colW);

        foreach ($data->rows as $row) {
            if ($this->y < $this->margin + 24) {
                $this->newPage();
                $this->drawHeader($data, $x, $colW);
            }
            foreach ($row as $i => $value) {
                if (! isset($x[$i])) {
                    continue;
                }
                $text = $this->cellText($data, $i, $value);
                $this->cell($text, $x[$i], $colW($i), $this->y, 8, false, $data->columnAlign($i));
            }
            $this->y -= 13;
        }

        return $this->assemble();
    }

    private function drawHeader(ReportData $data, array $x, callable $colW): void
    {
        foreach ($data->columns as $i => $col) {
            $this->cell($col['label'], $x[$i], $colW($i), $this->y, 8, true, $data->columnAlign($i));
        }
        $this->y -= 4;
        $this->line($this->margin, $this->y, $this->pageW - $this->margin, $this->y);
        $this->y -= 12;
    }

    private function cellText(ReportData $data, int $i, $value): string
    {
        $type = $data->columnType($i);
        if (($type === 'money') && $value !== null && $value !== '' && is_numeric((string) $value)) {
            return 'RM '.number_format((float) $value, 2, '.', ',');
        }

        return (string) ($value ?? '');
    }

    // ── Primitif lukisan ────────────────────────────────────────

    private function cell(string $text, float $x, float $w, float $y, float $size, bool $bold, string $align): void
    {
        $text = $this->truncate($text, $w, $size);
        if ($align === 'right') {
            $tw = $this->textWidth($text, $size);
            $this->text($text, $x + $w - $tw - 2, $y, $size, $bold);
        } elseif ($align === 'center') {
            $tw = $this->textWidth($text, $size);
            $this->text($text, $x + ($w - $tw) / 2, $y, $size, $bold);
        } else {
            $this->text($text, $x + 2, $y, $size, $bold);
        }
    }

    private function text(string $text, float $x, float $y, float $size, bool $bold): void
    {
        $font = $bold ? '/F2' : '/F1';
        $this->content .= "BT {$font} {$size} Tf ".round($x, 2).' '.round($y, 2).' Td ('.$this->escape($text).") Tj ET\n";
    }

    private function line(float $x1, float $y1, float $x2, float $y2): void
    {
        $this->content .= '0.7 w '.round($x1, 2).' '.round($y1, 2).' m '.round($x2, 2).' '.round($y2, 2)." l S\n";
    }

    private function newPage(): void
    {
        $this->pages[] = $this->content;
        $this->content = '';
        $this->y = $this->pageH - $this->margin;
    }

    private function textWidth(string $text, float $size): float
    {
        return strlen($text) * $size * 0.5;
    }

    private function truncate(string $text, float $w, float $size): string
    {
        $max = (int) floor(($w - 4) / ($size * 0.5));
        if ($max > 0 && strlen($text) > $max) {
            return substr($text, 0, max(1, $max - 1)).'…';
        }

        return $text;
    }

    private function escape(string $text): string
    {
        // Latin-1 untuk WinAnsiEncoding; ganti '…' & buang bukan-ASCII selamat.
        $text = str_replace('…', '...', $text);
        $text = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text) ?: $text;

        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $text);
    }

    private function assemble(): string
    {
        $this->pages[] = $this->content;
        $n = count($this->pages);

        // Objek: 1 Catalog, 2 Pages, kemudian bagi setiap halaman: Page + Content.
        // Font: dua objek terakhir.
        $pageObjIds = [];
        $contentObjIds = [];
        $objId = 3;
        foreach ($this->pages as $i => $c) {
            $pageObjIds[$i] = $objId++;
            $contentObjIds[$i] = $objId++;
        }
        $fontRegular = $objId++;
        $fontBold = $objId++;

        // 1: Catalog
        $this->objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        // 2: Pages
        $kids = implode(' ', array_map(fn ($id) => "{$id} 0 R", $pageObjIds));
        $this->objects[2] = "<< /Type /Pages /Count {$n} /Kids [{$kids}] >>";

        foreach ($this->pages as $i => $c) {
            $mediabox = '0 0 '.round($this->pageW, 2).' '.round($this->pageH, 2);
            $this->objects[$pageObjIds[$i]] = "<< /Type /Page /Parent 2 0 R /MediaBox [{$mediabox}] "
                ."/Resources << /Font << /F1 {$fontRegular} 0 R /F2 {$fontBold} 0 R >> >> "
                ."/Contents {$contentObjIds[$i]} 0 R >>";
            $stream = $c;
            $this->objects[$contentObjIds[$i]] = "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        $this->objects[$fontRegular] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $this->objects[$fontBold] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        // Bina fail dengan jadual xref.
        ksort($this->objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($this->objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $count = count($this->objects) + 1;
        $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
        for ($id = 1; $id < $count; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }
}

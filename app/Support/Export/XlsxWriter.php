<?php

namespace App\Support\Export;

use Illuminate\Http\Response;
use ZipArchive;

/**
 * Penulis XLSX (Office Open XML) tulen — tanpa pakej luar. Menghasilkan fail
 * .xlsx sah menggunakan ZipArchive. Nilai wang ditulis sebagai nombor dengan
 * nilai literal string DECIMAL kanonik (tiada aritmetik float dalam kod kita).
 */
class XlsxWriter
{
    public static function response(ReportData $data, string $filename): Response
    {
        $binary = self::build($data);

        return new Response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.xlsx"',
            'Content-Length' => (string) strlen($binary),
        ]);
    }

    public static function build(ReportData $data): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rootRels());
        $zip->addFromString('xl/workbook.xml', self::workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($data));

        $zip->close();
        $binary = file_get_contents($tmp);
        @unlink($tmp);

        return $binary;
    }

    private static function sheet(ReportData $data): string
    {
        $rowsXml = '';
        $r = 1;

        // Tajuk.
        $rowsXml .= self::row($r++, [self::inlineStr($data->title, 2)]);
        // Meta.
        foreach ($data->meta as $k => $v) {
            $rowsXml .= self::row($r++, [self::inlineStr($k.': '.$v, 0)]);
        }
        $r++; // baris kosong

        // Header.
        $headerCells = [];
        foreach ($data->columns as $col) {
            $headerCells[] = self::inlineStr($col['label'], 1);
        }
        $rowsXml .= self::row($r++, $headerCells);

        // Data.
        foreach ($data->rows as $row) {
            $cells = [];
            foreach ($row as $i => $value) {
                $type = $data->columnType($i);
                if (($type === 'money' || $type === 'number') && $value !== null && $value !== '' && is_numeric((string) $value)) {
                    $cells[] = self::numberCell((string) $value, $type === 'money' ? 3 : 0);
                } else {
                    $cells[] = self::inlineStr((string) ($value ?? ''), 0);
                }
            }
            $rowsXml .= self::row($r++, $cells);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.$rowsXml.'</sheetData></worksheet>';
    }

    private static function row(int $r, array $cells): string
    {
        return '<row r="'.$r.'">'.implode('', $cells).'</row>';
    }

    /** Sel teks (inlineStr). $style: 0 normal, 1 header bold, 2 title bold big. */
    private static function inlineStr(string $text, int $style): string
    {
        $s = $style > 0 ? ' s="'.$style.'"' : '';

        return '<c'.$s.' t="inlineStr"><is><t xml:space="preserve">'.self::esc(self::guardFormula($text)).'</t></is></c>';
    }

    /**
     * Perlindungan CSV/formula-injection: teks yang bermula dengan = + - @ (atau
     * tab/CR/LF) diawali apostrof supaya tidak ditafsir sebagai formula oleh
     * pembaca hamparan. Sel wang adalah numerik — tidak terjejas.
     */
    private static function guardFormula(string $text): string
    {
        if ($text !== '' && in_array($text[0], ['=', '+', '-', '@', "\t", "\r", "\n"], true)) {
            return "'".$text;
        }

        return $text;
    }

    /** Sel nombor; $style 3 = format wang 2dp. */
    private static function numberCell(string $value, int $style): string
    {
        $s = $style > 0 ? ' s="'.$style.'"' : '';

        return '<c'.$s.'><v>'.self::esc($value).'</v></c>';
    }

    private static function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    /** Gaya: 0 normal, 1 header bold, 2 tajuk bold besar, 3 wang (#,##0.00). */
    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>'
            .'<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><name val="Calibri"/></font></fonts>'
            .'<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            .'<borders count="1"><border/></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="4">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'          // 0 normal
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' // 1 header bold
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>' // 2 title
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>' // 3 money
            .'</cellXfs></styleSheet>';
    }
}

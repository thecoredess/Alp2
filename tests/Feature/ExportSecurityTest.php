<?php

namespace Tests\Feature;

use App\Support\Export\ReportData;
use App\Support\Export\XlsxWriter;
use Tests\TestCase;
use ZipArchive;

class ExportSecurityTest extends TestCase
{
    private function sheetXml(ReportData $data): string
    {
        $binary = XlsxWriter::build($data);
        $tmp = tempnam(sys_get_temp_dir(), 'xlsxtest').'.xlsx';
        file_put_contents($tmp, $binary);
        $zip = new ZipArchive();
        $zip->open($tmp);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tmp);

        return $xml;
    }

    public function test_xlsx_guards_text_formula_injection(): void
    {
        // Payee berniat jahat yang bermula dengan '=' (dan varian + - @).
        $data = new ReportData('Ujian', [
            ['label' => 'Penerima', 'type' => 'text'],
            ['label' => 'Amaun', 'type' => 'money'],
        ], [
            ['=SUM(A1:A9)', '1000.00'],
            ['+CMD()', '2000.00'],
            ['-2+3', '3000.00'],
            ['@SUM', '4000.00'],
        ], []);

        $xml = $this->sheetXml($data);

        // Setiap payload teks mesti diawali apostrof (dineutralkan; apostrof di-escape XML sebagai &apos;).
        $this->assertStringContainsString('&apos;=SUM(A1:A9)', $xml);
        $this->assertStringContainsString('&apos;+CMD()', $xml);
        $this->assertStringContainsString('&apos;-2+3', $xml);
        $this->assertStringContainsString('&apos;@SUM', $xml);
        // Tiada sel teks bermula terus dengan '=' tanpa neutralisasi.
        $this->assertStringNotContainsString('<t xml:space="preserve">=SUM', $xml);
    }

    public function test_xlsx_money_stays_numeric_without_quote(): void
    {
        $data = new ReportData('Ujian', [
            ['label' => 'ALP', 'type' => 'text'],
            ['label' => 'Amaun', 'type' => 'money'],
        ], [['ALP-01', '12345.67']], []);

        $xml = $this->sheetXml($data);

        // Nilai wang ditulis sebagai nombor tepat (bukan teks berapostrof, tiada ralat float).
        $this->assertStringContainsString('<v>12345.67</v>', $xml);
        $this->assertStringNotContainsString("'12345.67", $xml);
    }

    public function test_normal_text_is_not_altered(): void
    {
        $data = new ReportData('Ujian', [['label' => 'ALP', 'type' => 'text']], [['ALP-01'], ['Dato Ahmad']], []);
        $xml = $this->sheetXml($data);
        $this->assertStringContainsString('ALP-01', $xml);
        $this->assertStringNotContainsString("'ALP-01", $xml);
    }
}

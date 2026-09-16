<?php

namespace App\Services\Documents;

use App\Enums\DocumentType;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

/** Jana PDF panduan dokumen persatuan + lampiran Borang EFT rasmi. */
class AssociationDocumentGuideService
{
    public const DOWNLOAD_FILENAME = 'Panduan-Dokumen-Persatuan-ALP-DBKL.pdf';

    public const REPORT_TEMPLATE_FILENAME_PREFIX = 'Format-Laporan-Program-ALP-DBKL';

    public const GUIDE_VERSION = 5;

    public function downloadUrl(): string
    {
        return route('association-guide.download', ['v' => self::GUIDE_VERSION]);
    }

    public function reportCardTemplatePath(): string
    {
        return resource_path('documents/laporan-program-alp-template.docx');
    }

    public function reportCardTemplateExists(): bool
    {
        return is_readable($this->reportCardTemplatePath());
    }

    public function reportCardTemplateFilename(int $year): string
    {
        return self::REPORT_TEMPLATE_FILENAME_PREFIX.'-'.$year.'.pdf';
    }

    public function generateReportCardTemplate(?int $year = null): string
    {
        $year ??= now()->year;

        return Pdf::loadView('pdf.laporan-program-alp', [
            'year' => $year,
        ])->setPaper('a4')->output();
    }

    public function eftFormPath(): string
    {
        return resource_path('pdf/borang-eft-2026.pdf');
    }

    public function eftFormExists(): bool
    {
        return is_readable($this->eftFormPath());
    }

    /** @return list<array{no: int, type: DocumentType, label: string, hint: string, notes: string}> */
    public function checklistItems(): array
    {
        return [
            [
                'no' => 1,
                'type' => DocumentType::PENDAFTARAN_PERTUBUHAN,
                'label' => DocumentType::PENDAFTARAN_PERTUBUHAN->label(),
                'hint' => DocumentType::PENDAFTARAN_PERTUBUHAN->simpleHint(),
                'notes' => 'Salinan mesti jelas, menunjukkan nama persatuan dan nombor pendaftaran. Pastikan maklumat selari dengan borang permohonan.',
            ],
            [
                'no' => 2,
                'type' => DocumentType::BORANG_EFT,
                'label' => DocumentType::BORANG_EFT->label(),
                'hint' => DocumentType::BORANG_EFT->simpleHint(),
                'notes' => 'Gunakan borang EFT rasmi DBKL (disertakan di akhir dokumen ini). Lengkapkan maklumat bank, tandatangan dan cop persatuan.',
            ],
            [
                'no' => 3,
                'type' => DocumentType::PENYATA_BANK,
                'label' => DocumentType::PENYATA_BANK->label(),
                'hint' => DocumentType::PENYATA_BANK->simpleHint(),
                'notes' => 'Muat naik salinan muka depan penyata bank atau buku simpanan yang disahkan syarikat. Nama pemegang akaun mesti selari dengan persatuan penerima.',
            ],
            [
                'no' => 4,
                'type' => DocumentType::KERTAS_KERJA,
                'label' => DocumentType::KERTAS_KERJA->label(),
                'hint' => DocumentType::KERTAS_KERJA->simpleHint(),
                'notes' => 'Terangkan latar belakang, objektif, sasaran penerima manfaat, jadual aktiviti, pecahan perbelanjaan dan impak program.',
            ],
            [
                'no' => 5,
                'type' => DocumentType::SIJIL_ROS,
                'label' => DocumentType::SIJIL_ROS->label(),
                'hint' => DocumentType::SIJIL_ROS->simpleHint(),
                'notes' => 'Sijil pendaftaran ROS (Pendaftar Pertubuhan) yang masih sah. Satu persatuan (nombor ROS) hanya dibenarkan satu permohonan setahun.',
            ],
        ];
    }

    public function generate(): string
    {
        if (! $this->eftFormExists()) {
            throw new RuntimeException('Borang EFT rasmi tidak dijumpai dalam sistem.');
        }

        $guidePdf = Pdf::loadView('pdf.panduan-dokumen-persatuan', [
            'items' => $this->checklistItems(),
            'year' => now()->year,
        ])->setPaper('a4')->output();

        $reportPdf = $this->generateReportCardTemplate(now()->year);

        $merged = new Fpdi;

        $this->appendPdf($merged, StreamReader::createByString($guidePdf));
        $this->appendPdf($merged, StreamReader::createByString($reportPdf));
        $this->appendPdf($merged, $this->eftFormPath());

        return $merged->Output('S');
    }

    private function appendPdf(Fpdi $merged, StreamReader|string $source): void
    {
        $pageCount = $merged->setSourceFile($source);

        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $merged->importPage($page);
            $size = $merged->getTemplateSize($template);
            $merged->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $merged->useTemplate($template);
        }
    }
}

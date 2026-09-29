<?php

namespace App\Services\Documents;

use App\Enums\DocumentType;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Support\Tbl10BudgetSnapshot;
use App\Support\UrsDocumentTemplates;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Throwable;

/** Borang Penyaluran (TBL-10) + lampiran permohonan dalam satu PDF. */
class BorangPenyaluranPdfService
{
    private const DISK = 'local';

    /**
     * @return array{application: Application, tbl10: Tbl10BudgetSnapshot, templates: array<string, string>, logoSrc: string}
     */
    public function viewData(Application $application, Tbl10BudgetSnapshot $tbl10): array
    {
        $logoPath = public_path('images/logo-alp-dbkl.png');
        $logoSrc = is_readable($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : '';

        return [
            'application' => $application,
            'tbl10' => $tbl10,
            'templates' => UrsDocumentTemplates::all(),
            'logoSrc' => $logoSrc,
        ];
    }

    public function filename(Application $application): string
    {
        $safe = str_replace(['/', '\\', ' '], '-', $application->application_number);

        return 'Borang-Penyaluran-'.$safe.'.pdf';
    }

    public function generate(Application $application, Tbl10BudgetSnapshot $tbl10): string
    {
        $application->loadMissing(['documents', 'alp', 'reviews.reviewer', 'approvals.approver', 'approvals.approvalLevel']);

        $mainPdf = Pdf::loadView('applications.borang-pdf', $this->viewData($application, $tbl10))
            ->setPaper('a4')
            ->output();

        $merged = new Fpdi;
        $this->appendPdf($merged, StreamReader::createByString($mainPdf));

        foreach ($this->orderedDocuments($application) as $document) {
            $this->appendDocument($merged, $document);
        }

        return $merged->Output('S');
    }

    /** @return \Illuminate\Support\Collection<int, ApplicationDocument> */
    private function orderedDocuments(Application $application)
    {
        $order = collect(DocumentType::contributionAttachments())
            ->values()
            ->map(fn (DocumentType $type, int $index) => [$type->value, $index])
            ->flatMap(fn (array $pair) => [$pair[0] => $pair[1]]);

        return $application->documents
            ->sortBy(fn (ApplicationDocument $doc) => $order[$doc->document_type->value] ?? 99)
            ->values();
    }

    private function appendDocument(Fpdi $merged, ApplicationDocument $document): void
    {
        if (! Storage::disk(self::DISK)->exists($document->stored_path)) {
            $this->appendCoverPage($merged, $document, 'Fail lampiran tidak dijumpai dalam storan sistem.');

            return;
        }

        $bytes = Storage::disk(self::DISK)->get($document->stored_path);
        $mime = (string) ($document->mime_type ?: Storage::disk(self::DISK)->mimeType($document->stored_path));

        if ($this->isPdf($mime, $document->original_filename)) {
            try {
                $this->appendCoverPage($merged, $document);
                $this->appendPdf($merged, StreamReader::createByString($bytes));

                return;
            } catch (Throwable) {
                $this->appendCoverPage($merged, $document, 'PDF lampiran tidak dapat dibaca — sila rujuk fail asal dalam sistem.');

                return;
            }
        }

        if ($this->isImage($mime, $document->original_filename)) {
            $this->appendImagePages($merged, $document, $bytes, $mime);

            return;
        }

        $this->appendCoverPage(
            $merged,
            $document,
            'Format fail (Word/Excel) tidak disertakan dalam PDF gabungan — muat turun fail asal dari modul permohonan.',
        );
    }

    private function appendCoverPage(Fpdi $merged, ApplicationDocument $document, ?string $note = null): void
    {
        $pdf = Pdf::loadView('pdf.borang-attachment-cover', [
            'label' => $document->document_type->label(),
            'filename' => $document->original_filename,
            'note' => $note,
        ])->setPaper('a4')->output();

        $this->appendPdf($merged, StreamReader::createByString($pdf));
    }

    private function appendImagePages(Fpdi $merged, ApplicationDocument $document, string $bytes, string $mime): void
    {
        $normalized = $this->normalizeImageMime($mime, $document->original_filename);
        if ($normalized === null) {
            $this->appendCoverPage($merged, $document, 'Format imej tidak disokong.');

            return;
        }

        $pdf = Pdf::loadView('pdf.borang-attachment-image', [
            'label' => $document->document_type->label(),
            'filename' => $document->original_filename,
            'mime' => $normalized,
            'data' => base64_encode($bytes),
        ])->setPaper('a4')->output();

        $this->appendPdf($merged, StreamReader::createByString($pdf));
    }

    private function isPdf(string $mime, string $filename): bool
    {
        return str_contains($mime, 'pdf') || str_ends_with(strtolower($filename), '.pdf');
    }

    private function isImage(string $mime, string $filename): bool
    {
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $lower = strtolower($filename);

        return str_ends_with($lower, '.jpg')
            || str_ends_with($lower, '.jpeg')
            || str_ends_with($lower, '.png');
    }

    private function normalizeImageMime(string $mime, string $filename): ?string
    {
        if (in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
            return $mime === 'image/jpg' ? 'image/jpeg' : $mime;
        }

        $lower = strtolower($filename);
        if (str_ends_with($lower, '.png')) {
            return 'image/png';
        }
        if (str_ends_with($lower, '.jpg') || str_ends_with($lower, '.jpeg')) {
            return 'image/jpeg';
        }

        return null;
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

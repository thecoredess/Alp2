<?php

namespace App\Services\Documents;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Support\HijriDate;
use App\Support\UrsDocumentTemplates;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\View\View;

/** Surat pemakluman keputusan permohonan sumbangan ALP (template DBKL). */
class ApprovalLetterService
{
    public const TEMPLATE_PATH = 'documents/surat-pemakluman-alp-template.docx';

    public function templatePath(): string
    {
        return resource_path(self::TEMPLATE_PATH);
    }

    public function templateExists(): bool
    {
        return is_readable($this->templatePath());
    }

    /** @return array<string, mixed> */
    public function viewData(Application $application, ?Carbon $at = null): array
    {
        $application->loadMissing(['financialYear', 'alp']);

        $at ??= now();
        $approved = $application->status === ApplicationStatus::APPROVED;

        return [
            'application' => $application,
            'approved' => $approved,
            'referenceNo' => $this->referenceNo($application),
            'letterDate' => $at,
            'letterDateMalay' => $at->locale('ms')->translatedFormat('d F Y'),
            'letterDateHijri' => HijriDate::malayLabel($at),
            'alpName' => $application->alp->name,
            'alpAddress' => $application->alp->address ?: '—',
            'recipientName' => $application->recipient_name ?: '—',
            'signatoryName' => UrsDocumentTemplates::letterSignatoryName(),
            'signatoryTitle' => UrsDocumentTemplates::letterSignatoryTitle(),
            'signatoryOnBehalf' => UrsDocumentTemplates::letterSignatoryOnBehalf(),
            'contactPhone' => UrsDocumentTemplates::letterContactPhone(),
            'dbayarUrl' => UrsDocumentTemplates::letterDbayarUrl(),
        ];
    }

    public function html(Application $application): View
    {
        return view('applications.letter', $this->viewData($application));
    }

    public function pdf(Application $application): Response
    {
        $pdf = Pdf::loadView('applications.letter-pdf', $this->viewData($application))
            ->setPaper('a4');

        $suffix = $application->status === ApplicationStatus::APPROVED ? 'Lulus' : 'Tidak-Lulus';
        $filename = 'Surat-Pemakluman-'.str_replace(['/', '\\'], '-', $application->application_number).'-'.$suffix.'.pdf';

        return $pdf->stream($filename);
    }

    public function referenceNo(Application $application): string
    {
        return UrsDocumentTemplates::letterReferenceBase();
    }

}

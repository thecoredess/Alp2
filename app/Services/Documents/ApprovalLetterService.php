<?php

namespace App\Services\Documents;

use App\Enums\ApplicationStatus;
use App\Models\Application;
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
            'letterDateHijri' => $this->hijriLabel($at),
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

    private function hijriLabel(Carbon $date): string
    {
        if (! class_exists(\IntlCalendar::class)) {
            return '—';
        }

        try {
            $cal = \IntlCalendar::createInstance(null, 'ms_MY@calendar=islamic');
            if (! $cal) {
                return '—';
            }
            $cal->setTime($date->getTimestamp() * 1000);
            $day = $cal->get(\IntlCalendar::FIELD_DAY_OF_MONTH);
            $month = $cal->get(\IntlCalendar::FIELD_MONTH) + 1;
            $months = [
                1 => 'Muharram', 2 => 'Safar', 3 => 'Rabiulawal', 4 => 'Rabiulakhir',
                5 => 'Jamadilawal', 6 => 'Jamadilakhir', 7 => 'Rejab', 8 => 'Syaaban',
                9 => 'Ramadan', 10 => 'Syawal', 11 => 'Zulkaedah', 12 => 'Zulhijjah',
            ];
            $year = $cal->get(\IntlCalendar::FIELD_YEAR);

            return sprintf('%d %s %dH', $day, $months[$month] ?? '', $year);
        } catch (\Throwable) {
            return '—';
        }
    }
}

<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\ReportCardStatus;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Notification\ApplicationNotifier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Laporan aktiviti / report card (URS M07, BR-018/019).
 */
class ApplicationReportCardService
{
    private const DISK = 'local';

    public function __construct(
        private readonly AuditService $audit,
        private readonly ApplicationNotifier $notifier,
    ) {}

    public function canUpload(Application $application): bool
    {
        if ($application->status !== ApplicationStatus::APPROVED || ! $application->hasVoucherPrepared()) {
            return false;
        }

        $status = $application->report_card_status;

        return $status === null
            || $status === ReportCardStatus::RETURNED;
    }

    /**
     * Tarikh akhir dikemukakan: 1 bulan selepas baucar disedia.
     * Tiada tarikh akhir sebelum baucar wujud — jangan tandakan tertunggak.
     */
    public function dueDate(Application $application): ?\Carbon\Carbon
    {
        if (! $application->hasVoucherPrepared()) {
            return null;
        }

        $start = $application->payment_voucher_date
            ?? $application->payment_updated_at
            ?? $application->updated_at;

        return $start?->copy()->startOfDay()->addMonthNoOverflow()->endOfDay();
    }

    public function isOverdue(Application $application): bool
    {
        if ($application->report_card_submitted_at || $application->report_card_status !== null) {
            return false;
        }

        $due = $this->dueDate($application);

        return $due !== null && now()->greaterThan($due);
    }

    public function hasReportCard(Application $application): bool
    {
        return $application->report_card_status === ReportCardStatus::APPROVED;
    }

    /** Ada fail dimuat naik (belum semestinya disahkan JP). */
    public function hasUploadedReportDocument(Application $application): bool
    {
        if ($application->report_card_submitted_at) {
            return true;
        }

        return $application->documents()
            ->whereIn('document_type', [
                DocumentType::REPORT_CARD->value,
                DocumentType::LAPORAN_AKTIVITI->value,
            ])
            ->exists();
    }

    public function upload(
        Application $application,
        User $actor,
        UploadedFile $file,
        DocumentType $type = DocumentType::REPORT_CARD,
        ?string $remarks = null,
    ): ApplicationDocument {
        if (! $this->canUpload($application)) {
            throw new ApplicationException(
                'Laporan aktiviti hanya boleh dimuat naik selepas baucar disedia oleh Kewangan JP.'
            );
        }

        if (! in_array($type, [DocumentType::REPORT_CARD, DocumentType::LAPORAN_AKTIVITI], true)) {
            throw new ApplicationException('Jenis dokumen report card tidak sah.');
        }

        return DB::transaction(function () use ($application, $actor, $file, $type, $remarks) {
            $storedPath = $file->store("applications/{$application->id}/report-card", self::DISK);
            $sha256 = hash_file('sha256', $file->getRealPath());

            $doc = $application->documents()->create([
                'document_type' => $type,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'sha256' => $sha256,
                'uploaded_by' => $actor->id,
            ]);

            $application->report_card_submitted_at = now();
            $application->report_card_status = ReportCardStatus::AWAITING_ADMIN_JP;
            if ($remarks !== null) {
                $application->report_card_remarks = $remarks;
            }
            $application->updated_by = $actor->id;
            $application->save();

            $this->audit->log('APPLICATION_REPORT_CARD_UPLOADED', $application, null, [
                'document_type' => $type->value,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            $this->notifier->reportCardSubmitted($application->fresh());

            return $doc;
        });
    }

    /**
     * Permohonan diluluskan tanpa report card, program sudah tamat (atau hampir).
     *
     * @return Collection<int, Application>
     */
    public function awaitingSubmission(?int $financialYearId = null): Collection
    {
        return Application::query()
            ->with(['alp.users', 'financialYear'])
            ->where('status', ApplicationStatus::APPROVED->value)
            ->whereNull('report_card_submitted_at')
            ->when($financialYearId, fn ($q) => $q->where('financial_year_id', $financialYearId))
            ->orderBy('id')
            ->get()
            ->filter(fn (Application $app) => ! $this->hasReportCard($app));
    }

    /** Hantar peringatan NT-007 untuk yang sudah melebihi / menghampiri due date. */
    public function sendReminders(bool $onlyOverdue = true): int
    {
        $sent = 0;

        foreach ($this->awaitingSubmission() as $app) {
            $due = $this->dueDate($app);
            if (! $due) {
                continue;
            }

            $shouldRemind = $onlyOverdue
                ? now()->greaterThan($due)
                : now()->greaterThanOrEqualTo($due->copy()->subDays(7));

            if (! $shouldRemind) {
                continue;
            }

            // Elak spam: sekali sehari.
            if ($app->report_card_reminder_sent_at && $app->report_card_reminder_sent_at->isToday()) {
                continue;
            }

            $this->notifier->reportCardReminder($app);
            $app->forceFill(['report_card_reminder_sent_at' => now()])->save();
            $sent++;
        }

        return $sent;
    }
}

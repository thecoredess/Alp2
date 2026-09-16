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

    /** @var list<DocumentType> */
    private const REPORT_TYPES = [
        DocumentType::REPORT_CARD,
        DocumentType::LAPORAN_AKTIVITI,
    ];

    public const REMINDER_DAYS_BEFORE = 7;

    public const REMINDER_DAYS_AFTER = 7;

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

        return $status === null || $status === ReportCardStatus::RETURNED;
    }

    public function canSubmit(Application $application): bool
    {
        return $application->status === ApplicationStatus::APPROVED
            && $application->report_card_status === ReportCardStatus::DRAFT
            && $this->draftDocument($application) !== null;
    }

    public function canDiscardDraft(Application $application): bool
    {
        return $this->canSubmit($application);
    }

    /**
     * Tarikh akhir dikemukakan: 1 bulan selepas tarikh program.
     */
    public function dueDate(Application $application): ?\Carbon\Carbon
    {
        if ($application->status !== ApplicationStatus::APPROVED || ! $application->program_date) {
            return null;
        }

        return $application->program_date->copy()->startOfDay()->addMonthNoOverflow()->endOfDay();
    }

    public function isOverdue(Application $application): bool
    {
        if ($application->report_card_status === ReportCardStatus::APPROVED) {
            return false;
        }

        if (in_array($application->report_card_status, [
            ReportCardStatus::AWAITING_ADMIN_JP,
            ReportCardStatus::AWAITING_PEGAWAI_JP,
        ], true)) {
            return false;
        }

        if ($application->report_card_submitted_at) {
            return false;
        }

        $due = $this->dueDate($application);

        return $due !== null && now()->greaterThan($due);
    }

    public function hasReportCard(Application $application): bool
    {
        return $application->report_card_status === ReportCardStatus::APPROVED;
    }

    public function hasDraft(Application $application): bool
    {
        return $application->report_card_status === ReportCardStatus::DRAFT;
    }

    /** Fail draf semasa menunggu pengesahan ALP. */
    public function draftDocument(Application $application): ?ApplicationDocument
    {
        if (! $this->hasDraft($application)) {
            return null;
        }

        return $this->reportDocumentsQuery($application)->latest('id')->first();
    }

    /** Ada fail dimuat naik dan dihantar ke JP. */
    public function hasUploadedReportDocument(Application $application): bool
    {
        return $application->report_card_submitted_at !== null;
    }

    /** Simpan fail sebagai draf — belum dihantar ke Admin JP. */
    public function upload(
        Application $application,
        User $actor,
        UploadedFile $file,
        DocumentType $type = DocumentType::LAPORAN_AKTIVITI,
        ?string $remarks = null,
    ): ApplicationDocument {
        if (! $this->canUpload($application) && ! $this->hasDraft($application)) {
            throw new ApplicationException(
                'Laporan aktiviti hanya boleh dimuat naik selepas baucar disedia oleh Kewangan JP.'
            );
        }

        if (! in_array($type, self::REPORT_TYPES, true)) {
            throw new ApplicationException('Jenis dokumen report card tidak sah.');
        }

        return DB::transaction(function () use ($application, $actor, $file, $type, $remarks) {
            $this->removeReportDocuments($application);

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

            $application->report_card_submitted_at = null;
            $application->report_card_status = ReportCardStatus::DRAFT;
            if ($remarks !== null) {
                $application->report_card_remarks = $remarks;
            }
            $application->updated_by = $actor->id;
            $application->save();

            $this->audit->log('APPLICATION_REPORT_CARD_DRAFT_SAVED', $application, null, [
                'document_type' => $type->value,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            return $doc;
        });
    }

    /** Hantar draf laporan aktiviti ke Admin JP. */
    public function submit(Application $application, User $actor): Application
    {
        if (! $this->canSubmit($application)) {
            throw new ApplicationException('Tiada draf laporan aktiviti untuk dihantar.');
        }

        return DB::transaction(function () use ($application, $actor) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            if (! $this->canSubmit($application)) {
                throw new ApplicationException('Tiada draf laporan aktiviti untuk dihantar.');
            }

            $application->report_card_submitted_at = now();
            $application->report_card_status = ReportCardStatus::AWAITING_ADMIN_JP;
            $application->updated_by = $actor->id;
            $application->save();

            $doc = $this->draftDocument($application);

            $this->audit->log('APPLICATION_REPORT_CARD_SUBMITTED', $application, null, [
                'document_type' => $doc?->document_type->value,
                'original_filename' => $doc?->original_filename,
            ]);

            $this->notifier->reportCardSubmitted($application->fresh());

            return $application;
        });
    }

    public function discardDraft(Application $application, User $actor): Application
    {
        if (! $this->canDiscardDraft($application)) {
            throw new ApplicationException('Tiada draf laporan aktiviti untuk dibatalkan.');
        }

        return DB::transaction(function () use ($application, $actor) {
            $this->removeReportDocuments($application);

            $application->report_card_status = null;
            $application->report_card_submitted_at = null;
            $application->report_card_remarks = null;
            $application->updated_by = $actor->id;
            $application->save();

            $this->audit->log('APPLICATION_REPORT_CARD_DRAFT_DISCARDED', $application);

            return $application;
        });
    }

    /**
     * Permohonan diluluskan tanpa report card dihantar.
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

    /**
     * NT-007 — peringatan 7 hari sebelum & 7 hari selepas tarikh akhir (jika belum dihantar).
     */
    public function sendReminders(): int
    {
        $sent = 0;

        foreach ($this->awaitingSubmission() as $app) {
            if (! $this->needsReportReminder($app)) {
                continue;
            }

            $due = $this->dueDate($app);
            if (! $due) {
                continue;
            }

            $today = now()->startOfDay();
            $dueDay = $due->copy()->startOfDay();
            $upcomingDay = $dueDay->copy()->subDays(self::REMINDER_DAYS_BEFORE);
            $overdueFollowUpDay = $dueDay->copy()->addDays(self::REMINDER_DAYS_AFTER);

            if ($today->equalTo($upcomingDay) && ! $app->report_card_upcoming_reminder_sent_at) {
                $this->notifier->reportCardUpcomingReminder($app, $due);
                $app->forceFill(['report_card_upcoming_reminder_sent_at' => now()])->save();
                $sent++;

                continue;
            }

            if ($today->equalTo($overdueFollowUpDay) && ! $app->report_card_overdue_reminder_sent_at) {
                $this->notifier->reportCardOverdueReminder($app, $due);
                $app->forceFill(['report_card_overdue_reminder_sent_at' => now()])->save();
                $sent++;
            }
        }

        return $sent;
    }

    private function needsReportReminder(Application $application): bool
    {
        if ($application->status !== ApplicationStatus::APPROVED) {
            return false;
        }

        if ($application->report_card_status === ReportCardStatus::APPROVED) {
            return false;
        }

        if (in_array($application->report_card_status, [
            ReportCardStatus::AWAITING_ADMIN_JP,
            ReportCardStatus::AWAITING_PEGAWAI_JP,
        ], true)) {
            return false;
        }

        return ! $this->hasUploadedReportDocument($application);
    }

    private function reportDocumentsQuery(Application $application)
    {
        return $application->documents()->whereIn('document_type', array_map(
            fn (DocumentType $t) => $t->value,
            self::REPORT_TYPES,
        ));
    }

    private function removeReportDocuments(Application $application): void
    {
        foreach ($this->reportDocumentsQuery($application)->get() as $doc) {
            Storage::disk(self::DISK)->delete($doc->stored_path);
            $doc->delete();
        }
    }
}

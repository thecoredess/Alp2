<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
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
        return $application->status === ApplicationStatus::APPROVED;
    }

    /** Tarikh akhir dikemukakan: 1 bulan selepas tamat program (BR-018). */
    public function dueDate(Application $application): ?\Carbon\Carbon
    {
        $end = $application->proposed_end_date ?? $application->proposed_start_date;

        return $end ? $end->copy()->addMonthNoOverflow()->endOfDay() : null;
    }

    public function isOverdue(Application $application): bool
    {
        if ($application->report_card_submitted_at) {
            return false;
        }

        $due = $this->dueDate($application);

        return $due !== null && now()->greaterThan($due);
    }

    public function hasReportCard(Application $application): bool
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
            throw new ApplicationException('Report card hanya untuk permohonan yang telah diluluskan.');
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
            if ($remarks !== null) {
                $application->report_card_remarks = $remarks;
            }
            $application->updated_by = $actor->id;
            $application->save();

            $this->audit->log('APPLICATION_REPORT_CARD_UPLOADED', $application, null, [
                'document_type' => $type->value,
                'original_filename' => $file->getClientOriginalName(),
            ]);

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
            ->where(function ($q) {
                $q->whereNotNull('proposed_end_date')
                    ->orWhereNotNull('proposed_start_date');
            })
            ->orderBy('proposed_end_date')
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

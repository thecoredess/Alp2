<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Models\Application;
use App\Models\ApplicationReview;
use App\Models\ApplicationRevision;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Notification\ApplicationNotifier;
use App\Support\JpReviewChecklist;
use Illuminate\Support\Facades\DB;

/**
 * Aliran semakan JP (URS):
 * Admin JP (checklist + keputusan) → Pegawai JP (perakuan/syor) → PENDING_APPROVAL (Pengarah/PEPU).
 * Semakan Kewangan/Teknikal pra-kelulusan diasingkan ke legacy.
 * Rekod semakan bersifat append-only.
 */
class ApplicationReviewService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly ApplicationNotifier $notifier,
    ) {}

    /** Jenis semakan yang dijangka bagi status semasa (atau null jika tiada). */
    public function expectedReviewType(Application $application): ?ReviewType
    {
        return match ($application->status) {
            ApplicationStatus::SUBMITTED,
            ApplicationStatus::UNDER_SECRETARIAT_REVIEW => ReviewType::SECRETARIAT,
            ApplicationStatus::UNDER_FINANCE_REVIEW => ReviewType::FINANCE,
            ApplicationStatus::UNDER_TECHNICAL_REVIEW => ReviewType::TECHNICAL,
            default => null,
        };
    }

    /**
     * Rekod satu semakan dan majukan aliran kerja (atau kembalikan untuk pembetulan).
     * Atomik.
     */
    public function review(Application $application, ReviewType $type, User $user, ReviewDecision $decision, ?string $comments, ?array $checklist = null): Application
    {
        return DB::transaction(function () use ($application, $type, $user, $decision, $comments, $checklist) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            $expected = $this->expectedReviewType($application);
            if ($expected !== $type) {
                throw new ApplicationException('Permohonan ini tiada pada peringkat semakan '.$type->label().'.');
            }

            $isAdminJp = $user->canMakeFullJpReviewDecision();

            if ($type === ReviewType::SECRETARIAT) {
                $this->assertSecretariatActor($application, $isAdminJp);

                if ($decision === ReviewDecision::RECOMMEND && $isAdminJp) {
                    if (! is_array($checklist) || ! JpReviewChecklist::allLengkap($checklist)) {
                        throw new ApplicationException(
                            'Semua item senarai semak mesti lengkap sebelum hantar kepada Pegawai JP (UR-M04-001).'
                        );
                    }
                }
            }

            // Rekod semakan (append-only).
            ApplicationReview::create([
                'application_id' => $application->id,
                'review_type' => $type,
                'reviewer_id' => $user->id,
                'decision' => $decision,
                'comments' => $comments,
                'checklist' => $checklist,
                'revision_number' => $application->revision_number,
                'reviewed_at' => now(),
                'created_at' => now(),
            ]);

            if ($decision === ReviewDecision::RETURN_FOR_REVISION) {
                $this->returnForRevision($application, $user, $type->value, $comments);

                return $application;
            }

            $next = $this->nextStatusAfterReview($type, $isAdminJp);
            $remarks = $isAdminJp && $type === ReviewType::SECRETARIAT
                ? 'Keputusan Admin JP — menunggu perakuan Pegawai JP'
                : $type->label().' selesai';

            $this->transition($application, $next, $user, $remarks);

            $this->audit->log(strtoupper($type->value).'_REVIEW_COMPLETED', $application, null, [
                'decision' => $decision->value,
                'revision_number' => $application->revision_number,
                'actor' => $isAdminJp ? 'admin_jp' : 'pegawai_jp',
            ]);

            $fresh = $application->fresh();
            if ($next === ApplicationStatus::UNDER_SECRETARIAT_REVIEW) {
                $this->notifier->awaitingPegawaiJp($fresh);
            } elseif ($next === ApplicationStatus::PENDING_APPROVAL) {
                $this->notifier->awaitingNextApprover($fresh);
            }

            return $application;
        });
    }

    /** Admin JP hanya pada SUBMITTED; Pegawai JP hanya selepas keputusan Admin JP. */
    private function assertSecretariatActor(Application $application, bool $isAdminJp): void
    {
        if ($isAdminJp) {
            if ($application->status !== ApplicationStatus::SUBMITTED) {
                throw new ApplicationException(
                    'Keputusan Admin JP hanya untuk permohonan yang menunggu semakan Admin JP.'
                );
            }

            return;
        }

        if ($application->status !== ApplicationStatus::UNDER_SECRETARIAT_REVIEW) {
            throw new ApplicationException(
                'Pegawai JP hanya boleh membuat pengesyoran selepas keputusan Admin JP.'
            );
        }
    }

    /** Status seterusnya selepas semakan diluluskan/dimajukan. */
    private function nextStatusAfterReview(ReviewType $type, bool $isAdminJp): ApplicationStatus
    {
        return match ($type) {
            ReviewType::SECRETARIAT => $isAdminJp
                ? ApplicationStatus::UNDER_SECRETARIAT_REVIEW
                : ApplicationStatus::PENDING_APPROVAL,
            // Laluan legacy (route dinyahaktif) — kekal selamat jika dipanggil ujian lama.
            ReviewType::FINANCE, ReviewType::TECHNICAL => ApplicationStatus::PENDING_APPROVAL,
        };
    }

    /** Kembalikan permohonan untuk pembetulan (dari semakan). */
    public function returnForRevision(Application $application, User $user, string $stage, ?string $reason): void
    {
        $from = $application->status;

        // Simpan snapshot bukti keadaan semasa (sebelum pembetulan).
        ApplicationRevision::create([
            'application_id' => $application->id,
            'revision_number' => $application->revision_number,
            'previous_requested_amount' => $application->requested_amount,
            'snapshot' => $this->snapshot($application),
            'returned_by' => $user->id,
            'return_stage' => $stage,
            'reason' => $reason,
            'returned_at' => now(),
            'created_at' => now(),
        ]);

        $this->transition($application, ApplicationStatus::REVISION_REQUIRED, $user, 'Dikembalikan untuk pembetulan ('.$stage.')');

        $this->audit->log('APPLICATION_RETURNED_FOR_REVISION', $application, null, [
            'stage' => $stage,
            'reason' => $reason,
            'revision_number' => $application->revision_number,
        ]);

        $this->notifier->revisionRequired($application, (string) $reason);
    }

    /** Peralihan status terkawal + sejarah (append-only). */
    private function transition(Application $application, ApplicationStatus $to, User $user, ?string $remarks): void
    {
        $from = $application->status;
        $application->status = $to;
        $application->save();

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by' => $user->id,
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }

    /** Snapshot ringkas keadaan permohonan untuk rekod revisi. */
    private function snapshot(Application $application): array
    {
        $application->loadMissing(['documents']);

        return [
            'purpose' => $application->purpose,
            'recipient_name' => $application->recipient_name,
            'recipient_bank_account' => $application->recipient_bank_account,
            'requested_amount' => $application->requested_amount,
            'documents' => $application->documents->map(fn ($d) => [
                'document_type' => $d->document_type->value,
                'original_filename' => $d->original_filename,
            ])->all(),
        ];
    }
}

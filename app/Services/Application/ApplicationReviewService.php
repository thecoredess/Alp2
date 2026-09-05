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
use Illuminate\Support\Facades\DB;

/**
 * Semakan Pegawai JP (Urus Setia) kemudian terus ke kelulusan (URS v1.2 M04→M05).
 * Semakan Kewangan/Teknikal pra-kelulusan diasingkan ke legacy — tidak lagi dalam aliran aktif.
 * Pemulangan untuk pembetulan kekal. Rekod semakan bersifat append-only.
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

            if ($decision === ReviewDecision::RECOMMEND && $type === ReviewType::SECRETARIAT) {
                if (! is_array($checklist) || ! \App\Support\JpReviewChecklist::allLengkap($checklist)) {
                    throw new ApplicationException(
                        'Semua item senarai semak mesti lengkap sebelum hantar ke perakuan (UR-M04-001).'
                    );
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

            // Maju ke peringkat seterusnya.
            $next = $this->nextStatusAfterReview($application, $type);
            $this->transition($application, $next, $user, $type->label().' selesai');

            $this->audit->log(strtoupper($type->value).'_REVIEW_COMPLETED', $application, null, [
                'decision' => $decision->value,
                'revision_number' => $application->revision_number,
            ]);

            if ($next === ApplicationStatus::PENDING_APPROVAL) {
                $this->notifier->awaitingNextApprover($application->fresh());
            }

            return $application;
        });
    }

    /** Status seterusnya selepas sesuatu semakan diluluskan/dimajukan. */
    private function nextStatusAfterReview(Application $application, ReviewType $type): ApplicationStatus
    {
        return match ($type) {
            // URS v1.2: Pegawai JP → terus Peraku/Pelulus (tiada semakan kewangan/teknikal pra-kelulusan).
            ReviewType::SECRETARIAT => ApplicationStatus::PENDING_APPROVAL,
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
        $application->loadMissing(['budgetItems', 'documents']);

        return [
            'project_title' => $application->project_title,
            'project_summary' => $application->project_summary,
            'objectives' => $application->objectives,
            'scope' => $application->scope,
            'target_group' => $application->target_group,
            'location' => $application->location,
            'requested_amount' => $application->requested_amount,
            'budget_items' => $application->budgetItems->map(fn ($i) => [
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit' => $i->unit,
                'unit_cost' => $i->unit_cost,
                'total' => $i->total,
            ])->all(),
            'documents' => $application->documents->map(fn ($d) => [
                'document_type' => $d->document_type->value,
                'original_filename' => $d->original_filename,
            ])->all(),
        ];
    }
}

<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApprovalDecision;
use App\Enums\RoleName;
use App\Models\Allocation;
use App\Models\Application;
use App\Models\ApplicationApproval;
use App\Models\ApplicationStatusHistory;
use App\Models\ApprovalLevel;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use App\Services\Notification\ApplicationNotifier;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * Kelulusan formal berbilang-aras + penciptaan komitmen bajet.
 *
 * KESELAMATAN KEWANGAN:
 * - Semua operasi atomik (DB transaction); kunci baris application + allocation.
 * - Komitmen dicipta HANYA selepas kelulusan aras terakhir yang diperlukan.
 * - Idempotensi: unique(application_id, type=COMMITMENT) + unique(application_id,
 *   approval_level_id, revision_number) + semakan status berkunci.
 * - Semakan bajet guna Ledger Available sebenar (bukan tolak pending sendiri).
 */
class ApprovalService
{
    public function __construct(
        private readonly ApprovalMatrixService $matrix,
        private readonly BudgetService $budget,
        private readonly ApplicationReviewService $reviews,
        private readonly AuditService $audit,
        private readonly ApplicationNotifier $notifier,
    ) {}

    /**
     * Kemajuan kelulusan untuk paparan/logik.
     *
     * @return array{required: \Illuminate\Support\Collection<int, ApprovalLevel>, approvedCount: int, nextLevel: ?ApprovalLevel, finalLevel: ?ApprovalLevel}
     */
    public function progress(Application $application): array
    {
        $required = $this->matrix->requiredLevels($application->requestedAmountMoney(), $application->financial_year_id);
        $approvedCount = $application->approvals()
            ->where('decision', ApprovalDecision::APPROVED->value)
            ->where('revision_number', $application->revision_number)
            ->count();

        return [
            'required' => $required,
            'approvedCount' => $approvedCount,
            'nextLevel' => $required->get($approvedCount),
            'finalLevel' => $required->last(),
        ];
    }

    /** Luluskan aras semasa; cipta komitmen jika ini aras terakhir. Atomik. */
    public function approve(Application $application, User $approver, ?string $comments): Application
    {
        return DB::transaction(function () use ($application, $approver, $comments) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            if ($application->status !== ApplicationStatus::PENDING_APPROVAL) {
                throw new ApplicationException('Permohonan tidak berada pada peringkat kelulusan.');
            }

            $amount = $application->requestedAmountMoney();
            $required = $this->matrix->requiredLevels($amount, $application->financial_year_id);
            $approvedCount = $application->approvals()
                ->where('decision', ApprovalDecision::APPROVED->value)
                ->where('revision_number', $application->revision_number)
                ->count();

            /** @var ApprovalLevel|null $nextLevel */
            $nextLevel = $required->get($approvedCount);
            if (! $nextLevel) {
                throw new ApplicationException('Tiada aras kelulusan berbaki.');
            }

            $this->assertApproverAuthorised($approver, $nextLevel);

            $isFinal = ($approvedCount + 1) === $required->count();

            // Rekod kelulusan aras (unique menghalang duplikasi dalam pusingan sama).
            ApplicationApproval::create([
                'application_id' => $application->id,
                'approval_level_id' => $nextLevel->id,
                'approver_id' => $approver->id,
                'decision' => ApprovalDecision::APPROVED,
                'comments' => $comments,
                'sequence' => $nextLevel->sequence,
                'revision_number' => $application->revision_number,
                'decided_at' => now(),
                'created_at' => now(),
            ]);

            $this->audit->log('APPROVAL_LEVEL_APPROVED', $application, null, [
                'level' => $nextLevel->name,
                'sequence' => $nextLevel->sequence,
                'is_final' => $isFinal,
            ]);

            if (! $isFinal) {
                // Kekal PENDING_APPROVAL; catat sejarah.
                $this->history($application, $application->status, $application->status, $approver,
                    'Kelulusan aras '.$nextLevel->sequence.' ('.$nextLevel->name.') diberikan');

                $this->notifier->awaitingNextApprover($application->fresh());

                return $application;
            }

            // ── Aras terakhir → cipta komitmen ──
            $this->commitAndApprove($application, $amount, $approver);

            return $application;
        });
    }

    /** Cipta komitmen bajet (selepas semakan baki) & tukar status ke APPROVED. */
    private function commitAndApprove(Application $application, Money $amount, User $approver): void
    {
        // Kunci akaun peruntukan untuk keselamatan serentak.
        $allocation = Allocation::query()
            ->where('alp_id', $application->alp_id)
            ->where('financial_year_id', $application->financial_year_id)
            ->lockForUpdate()
            ->first();

        if (! $allocation) {
            throw new ApplicationException('Tiada peruntukan untuk ALP ini bagi tahun kewangan berkenaan.');
        }

        // Idempotensi: komitmen tidak boleh wujud lebih daripada sekali.
        if ($application->commitmentTransaction()->exists()) {
            throw new ApplicationException('Komitmen telah wujud untuk permohonan ini.');
        }

        // Semakan baki menggunakan Ledger Available SEBENAR (bukan tolak pending sendiri).
        $ledgerAvailable = $this->budget->summaryFor($application->alp_id, $application->financial_year_id)->available();
        if ($amount->greaterThan($ledgerAvailable)) {
            throw new ApplicationException(sprintf(
                'Baki peruntukan tidak mencukupi untuk komitmen. Ledger Available: RM%s; jumlah: RM%s.',
                $ledgerAvailable->format(),
                $amount->format(),
            ));
        }

        // URS v1.2: komitmen pada permohonan sahaja — modul projek diasingkan ke legacy.
        // unique(application_id,type) jaga idempotensi.
        $this->budget->recordCommitment($allocation, $amount, $application);

        $from = $application->status;
        $application->status = ApplicationStatus::APPROVED;
        $application->payment_status = ApplicationPaymentStatus::PENDING_PAYMENT;
        $application->save();

        $this->history($application, $from, ApplicationStatus::APPROVED, $approver, 'Permohonan diluluskan');

        $this->audit->log('APPLICATION_APPROVED', $application, null, [
            'amount' => $amount->value(),
            'application_number' => $application->application_number,
        ]);

        $this->notifier->approved($application);
        $this->notifier->awaitingPayment($application);
    }

    /** Tolak permohonan pada peringkat kelulusan. Tiada komitmen dicipta. */
    public function reject(Application $application, User $approver, ?string $comments): Application
    {
        return DB::transaction(function () use ($application, $approver, $comments) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            if ($application->status !== ApplicationStatus::PENDING_APPROVAL) {
                throw new ApplicationException('Permohonan tidak berada pada peringkat kelulusan.');
            }

            $progress = $this->progress($application);
            $level = $progress['nextLevel'];

            ApplicationApproval::create([
                'application_id' => $application->id,
                'approval_level_id' => $level?->id,
                'approver_id' => $approver->id,
                'decision' => ApprovalDecision::REJECTED,
                'comments' => $comments,
                'sequence' => $level?->sequence ?? 0,
                'revision_number' => $application->revision_number,
                'decided_at' => now(),
                'created_at' => now(),
            ]);

            $from = $application->status;
            $application->status = ApplicationStatus::REJECTED;
            $application->save();

            $this->history($application, $from, ApplicationStatus::REJECTED, $approver, 'Permohonan ditolak');
            $this->audit->log('APPLICATION_REJECTED', $application, null, ['reason' => $comments]);

            $this->notifier->rejected($application, (string) $comments);

            return $application;
        });
    }

    /** Kembalikan untuk pembetulan dari peringkat kelulusan. */
    public function returnForRevision(Application $application, User $approver, ?string $comments): Application
    {
        return DB::transaction(function () use ($application, $approver, $comments) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            if ($application->status !== ApplicationStatus::PENDING_APPROVAL) {
                throw new ApplicationException('Permohonan tidak berada pada peringkat kelulusan.');
            }

            $progress = $this->progress($application);
            ApplicationApproval::create([
                'application_id' => $application->id,
                'approval_level_id' => $progress['nextLevel']?->id,
                'approver_id' => $approver->id,
                'decision' => ApprovalDecision::RETURN_FOR_REVISION,
                'comments' => $comments,
                'sequence' => $progress['nextLevel']?->sequence ?? 0,
                'revision_number' => $application->revision_number,
                'decided_at' => now(),
                'created_at' => now(),
            ]);

            // Guna logik pemulangan yang sama (snapshot + transition + audit).
            $this->reviews->returnForRevision($application, $approver, 'approval', $comments);

            return $application;
        });
    }

    private function assertApproverAuthorised(User $approver, ApprovalLevel $level): void
    {
        if ($approver->hasRole(RoleName::SUPER_ADMIN->value)) {
            return;
        }

        if (! $approver->can('applications.approve') || ! $approver->hasRole($level->required_role)) {
            throw new ApplicationException('Anda tiada kuasa meluluskan pada aras ini ('.$level->name.').');
        }
    }

    private function history(Application $application, ApplicationStatus $from, ApplicationStatus $to, User $user, string $remarks): void
    {
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by' => $user->id,
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }
}

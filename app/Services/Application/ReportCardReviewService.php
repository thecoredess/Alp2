<?php

namespace App\Services\Application;

use App\Enums\ReportCardStatus;
use App\Enums\ReviewDecision;
use App\Models\Application;
use App\Models\ReportCardReview;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Notification\ApplicationNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Semakan laporan aktiviti: Admin JP → Pengesahan Pegawai JP.
 */
class ReportCardReviewService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly ApplicationNotifier $notifier,
    ) {}

    public function queueStatusFor(User $user): ReportCardStatus
    {
        return $user->canMakeFullJpReviewDecision()
            ? ReportCardStatus::AWAITING_ADMIN_JP
            : ReportCardStatus::AWAITING_PEGAWAI_JP;
    }

    public function canReview(Application $application, User $user): bool
    {
        if (! $user->can('applications.review.secretariat')) {
            return false;
        }

        $expected = $this->queueStatusFor($user);

        return $application->report_card_status === $expected;
    }

    public function review(Application $application, User $user, ReviewDecision $decision, ?string $comments): Application
    {
        return DB::transaction(function () use ($application, $user, $decision, $comments) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            if (! $this->canReview($application, $user)) {
                throw new ApplicationException('Laporan aktiviti ini tiada pada giliran semakan anda.');
            }

            $isAdmin = $user->canMakeFullJpReviewDecision();
            $stage = $isAdmin ? 'admin_jp' : 'pegawai_jp';

            ReportCardReview::create([
                'application_id' => $application->id,
                'reviewer_id' => $user->id,
                'stage' => $stage,
                'decision' => $decision,
                'comments' => $comments,
                'reviewed_at' => now(),
                'created_at' => now(),
            ]);

            if ($decision === ReviewDecision::RETURN_FOR_REVISION) {
                $application->report_card_status = ReportCardStatus::RETURNED;
                $application->report_card_submitted_at = null;
                $application->save();

                $this->audit->log('REPORT_CARD_RETURNED', $application, null, [
                    'stage' => $stage,
                    'comments' => $comments,
                ]);
                $this->notifier->reportCardReturned($application->fresh(), $comments ?? '');

                return $application;
            }

            if ($isAdmin) {
                $application->report_card_status = ReportCardStatus::AWAITING_PEGAWAI_JP;
                $application->save();

                $this->audit->log('REPORT_CARD_ADMIN_REVIEWED', $application, null, [
                    'decision' => $decision->value,
                ]);
                $this->notifier->reportCardAwaitingPegawaiJp($application->fresh());
            } else {
                $application->report_card_status = ReportCardStatus::APPROVED;
                $application->save();

                $this->audit->log('REPORT_CARD_APPROVED', $application, null, [
                    'decision' => $decision->value,
                ]);
                $this->notifier->reportCardApproved($application->fresh());
            }

            return $application;
        });
    }
}

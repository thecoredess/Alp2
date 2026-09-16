<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Enums\ReviewType;
use App\Models\Application;
use App\Models\User;
use App\Services\Application\ReportCardReviewService;

/**
 * Petakan satu notifikasi kepada tugasan sebenar penerimanya.
 *
 * Sasaran dikira semasa pengguna mengklik (bukan semasa notifikasi dijana)
 * supaya notifikasi lama turut mendarat pada halaman betul, dan supaya
 * kebenaran serta status semasa permohonan sentiasa dihormati.
 */
final class NotificationTarget
{
    private function __construct(
        public readonly string $url,
        public readonly string $action,
    ) {}

    /** @param array<string, mixed> $data */
    public static function for(array $data, User $user): self
    {
        $application = isset($data['application_id'])
            ? Application::find($data['application_id'])
            : null;

        if (! $application || $user->cannot('view', $application)) {
            return new self(
                (string) ($data['url'] ?? route('notifications.index')),
                'Buka',
            );
        }

        return match ((string) ($data['event'] ?? '')) {
            'submitted', 'awaiting_pegawai_jp' => self::review($application, $user),
            'awaiting_peraku', 'awaiting_pepu' => self::approval($application, $user),
            'revision_required' => self::revision($application, $user),
            'awaiting_payment' => self::payment($application, $user, 'Sediakan baucar'),
            'payment_voucher', 'report_card_reminder', 'report_card_reminder_upcoming', 'report_card_reminder_overdue' => self::reportCard($application),
            'report_card_submitted', 'report_card_awaiting_pegawai_jp' => self::reportCardReview($application, $user),
            'report_card_approved', 'report_card_returned' => self::reportCard($application, 'Buka laporan aktiviti'),
            'payment_paid' => self::payment($application, $user),
            default => self::application($application),
        };
    }

    /** Semakan JP — hanya jika permohonan masih pada giliran pengguna ini. */
    private static function review(Application $application, User $user): self
    {
        $expected = match ($application->status) {
            ApplicationStatus::SUBMITTED,
            ApplicationStatus::UNDER_SECRETARIAT_REVIEW => ReviewType::SECRETARIAT,
            default => null,
        };

        $ownStage = $user->canMakeFullJpReviewDecision()
            ? ApplicationStatus::SUBMITTED
            : ApplicationStatus::UNDER_SECRETARIAT_REVIEW;

        if ($expected === ReviewType::SECRETARIAT
            && $application->status === $ownStage
            && $user->can('applications.review.secretariat')
        ) {
            return new self(
                route('reviews.show', [$application, ReviewType::SECRETARIAT->value]),
                'Buka borang semakan',
            );
        }

        return self::application($application);
    }

    /** Kelulusan Peraku / PEPU — approvals.show menolak status selain PENDING_APPROVAL. */
    private static function approval(Application $application, User $user): self
    {
        if ($application->status === ApplicationStatus::PENDING_APPROVAL
            && $user->can('applications.approve')
        ) {
            return new self(
                route('approvals.show', $application),
                'Buka borang kelulusan',
            );
        }

        return self::application($application);
    }

    /** Pembetulan — terus ke langkah 1 borang jika pengguna boleh menyunting. */
    private static function revision(Application $application, User $user): self
    {
        if ($user->can('update', $application)) {
            return new self(
                route('applications.wizard.maklumat', $application),
                'Buat pembetulan',
            );
        }

        return self::application($application);
    }

    private static function payment(Application $application, User $user, string $action = 'Buka rekod pembayaran'): self
    {
        if ($user->can('payments.view')) {
            return new self(
                route('payments.index', ['cari' => $application->application_number]),
                $action,
            );
        }

        return self::application($application);
    }

    private static function reportCard(Application $application, string $action = 'Muat naik laporan aktiviti'): self
    {
        return new self(
            route('applications.show', [$application, 'tab' => 'report']),
            $action,
        );
    }

    /** Semakan laporan aktiviti — Admin JP atau Pegawai JP mengikut giliran. */
    private static function reportCardReview(Application $application, User $user): self
    {
        $reviews = app(ReportCardReviewService::class);

        if ($user->can('applications.review.secretariat')
            && $reviews->canReview($application, $user)
        ) {
            return new self(
                route('report-cards.review.show', $application),
                'Semak laporan aktiviti',
            );
        }

        return self::application($application);
    }

    private static function application(Application $application): self
    {
        return new self(route('applications.show', $application), 'Buka permohonan');
    }
}

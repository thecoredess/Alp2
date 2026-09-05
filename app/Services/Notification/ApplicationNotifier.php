<?php

namespace App\Services\Notification;

use App\Enums\ApprovalDecision;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use App\Services\Application\ApprovalMatrixService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class ApplicationNotifier
{
    public function __construct(
        private readonly ApprovalMatrixService $matrix,
    ) {}

    public function submitted(Application $application): void
    {
        $application->loadMissing('alp.users');

        $recipients = $this->usersWithPermission('applications.review.secretariat');

        Notification::send(
            $recipients,
            new ApplicationWorkflowNotification(
                $application,
                'submitted',
                'Permohonan baharu telah dihantar dan menunggu semakan Urus Setia.',
            ),
        );
    }

    /**
     * NT-003 / NT-004 — maklumkan pelulus aras seterusnya (Peraku atau PEPU).
     * Dipanggil selepas semakan JP → PENDING_APPROVAL, atau selepas kelulusan aras bukan akhir.
     */
    public function awaitingNextApprover(Application $application): void
    {
        $required = $this->matrix->requiredLevels(
            $application->requestedAmountMoney(),
            $application->financial_year_id,
        );

        $approvedCount = $application->approvals()
            ->where('decision', ApprovalDecision::APPROVED->value)
            ->where('revision_number', $application->revision_number)
            ->count();

        $next = $required->get($approvedCount);
        if (! $next) {
            return;
        }

        $recipients = User::role($next->required_role)
            ->where('status', UserStatus::ACTIVE)
            ->get()
            ->filter(fn (User $u) => $u->can('applications.approve'))
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $isFirst = $approvedCount === 0;
        $event = $isFirst ? 'awaiting_peraku' : 'awaiting_pepu';
        $msg = $isFirst
            ? 'Permohonan menunggu perakuan (NT-003). Aras: '.$next->name.'.'
            : 'Permohonan menunggu kelulusan PEPU / aras seterusnya (NT-004). Aras: '.$next->name.'.';

        Notification::send(
            $recipients,
            new ApplicationWorkflowNotification($application, $event, $msg),
        );
    }

    public function revisionRequired(Application $application, string $reason = ''): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        $msg = 'Permohonan anda dikembalikan untuk pembetulan.'.($reason !== '' ? ' Sebab: '.$reason : '');

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification($application, 'revision_required', $msg),
        );
    }

    public function approved(Application $application): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'approved',
                'Permohonan anda telah diluluskan. Anda boleh mencetak surat/ringkasan kelulusan.',
            ),
        );
    }

    public function rejected(Application $application, string $reason = ''): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        $msg = 'Permohonan anda telah ditolak.'.($reason !== '' ? ' Sebab: '.$reason : '');

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification($application, 'rejected', $msg),
        );
    }

    public function paymentVoucherPrepared(Application $application): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'payment_voucher',
                'Baucar pembayaran untuk permohonan anda sedang disediakan / telah direkod.',
            ),
        );
    }

    public function paymentPaid(Application $application): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        $voucher = $application->payment_voucher_no
            ? ' No. baucar: '.$application->payment_voucher_no.'.'
            : '';

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'payment_paid',
                'Pembayaran bagi permohonan anda telah direkod sebagai dibayar.'.$voucher,
            ),
        );
    }

    /** NT-007 — peringatan report card / laporan aktiviti. */
    public function reportCardReminder(Application $application): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'report_card_reminder',
                'Peringatan: sila muat naik laporan aktiviti / report card untuk permohonan ini (BR-018 / NT-007). Tempoh rujukan: 1 bulan selepas aktiviti.',
            ),
        );
    }

    /** @return Collection<int, User> */
    private function usersWithPermission(string $permission): Collection
    {
        return User::permission($permission)
            ->where('status', UserStatus::ACTIVE)
            ->get();
    }
}

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

        // Giliran pertama: Admin JP (checklist + keputusan).
        $recipients = $this->usersWithPermission('applications.review.secretariat')
            ->filter(fn (User $u) => $u->canMakeFullJpReviewDecision())
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ApplicationWorkflowNotification(
                $application,
                'submitted',
                'Permohonan baharu telah dihantar dan menunggu semakan Admin JP.',
            ),
        );

        $this->submissionAcknowledged($application);
    }

    /** Pengesahan e-mel kepada ALP selepas hantar / hantar semula permohonan. */
    public function submissionAcknowledged(Application $application): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        if ($owners->isEmpty()) {
            return;
        }

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'submission_acknowledged',
                'Permohonan anda '.$application->application_number.' telah berjaya dihantar dan akan diproses oleh Jabatan Pentadbiran.',
            ),
        );
    }

    /** Selepas keputusan Admin JP — giliran Pegawai JP membuat pengesyoran ke Pengarah. */
    public function awaitingPegawaiJp(Application $application): void
    {
        $recipients = $this->usersWithPermission('applications.review.secretariat')
            ->reject(fn (User $u) => $u->canMakeFullJpReviewDecision())
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ApplicationWorkflowNotification(
                $application,
                'awaiting_pegawai_jp',
                'Permohonan telah disemak Admin JP dan menunggu pengesyoran Pegawai JP kepada Pengarah JP.',
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
            ? 'Permohonan menunggu perakuan. Aras: '.$next->name.'.'
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

    /**
     * Selepas kelulusan aras akhir (PEPU) — giliran Kewangan JP menyediakan baucar.
     * Penerima ialah pemegang `payments.manage` (Kerani baucar), bukan skop JKEW.
     */
    public function awaitingPayment(Application $application): void
    {
        $recipients = $this->usersWithPermission('payments.manage');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ApplicationWorkflowNotification(
                $application,
                'awaiting_payment',
                'Permohonan telah diluluskan PEPU dan sedia untuk proses bayaran. Sila sediakan baucar (jumlah: RM'
                    .$application->requestedAmountMoney()->format().').',
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

        $due = $application->program_date?->copy()->startOfDay()->addMonthNoOverflow();

        $dueLabel = $due ? $due->format('d/m/Y') : '—';

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'payment_voucher',
                'Baucar telah disedia. Sila muat naik laporan aktiviti dalam 1 bulan selepas tarikh program (akhir: '.$dueLabel.').',
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

    /** NT-007 — 7 hari sebelum tarikh akhir laporan aktiviti. */
    public function reportCardUpcomingReminder(Application $application, \Carbon\Carbon $due): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'report_card_reminder_upcoming',
                'Peringatan: sila muat naik laporan aktiviti sebelum '.$due->format('d/m/Y').' (7 hari lagi). Tarikh akhir dikira 1 bulan selepas tarikh program.',
            ),
        );
    }

    /** NT-007 — 7 hari selepas tarikh akhir jika laporan masih belum dimuat naik. */
    public function reportCardOverdueReminder(Application $application, \Carbon\Carbon $due): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'report_card_reminder_overdue',
                'Peringatan: laporan aktiviti masih belum dimuat naik. Tarikh akhir ('.$due->format('d/m/Y').') telah luput 7 hari. Sila muat naik segera.',
            ),
        );
    }

    /** Selepas ALP muat naik — giliran Admin JP semak. */
    public function reportCardSubmitted(Application $application): void
    {
        $recipients = $this->usersWithPermission('applications.review.secretariat')
            ->filter(fn (User $u) => $u->canMakeFullJpReviewDecision())
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ApplicationWorkflowNotification(
                $application,
                'report_card_submitted',
                'Laporan aktiviti telah dimuat naik oleh ALP dan menunggu semakan Admin JP.',
            ),
        );
    }

    /** Selepas Admin JP semak — giliran Pegawai JP sahkan. */
    public function reportCardAwaitingPegawaiJp(Application $application): void
    {
        $recipients = $this->usersWithPermission('applications.review.secretariat')
            ->reject(fn (User $u) => $u->canMakeFullJpReviewDecision())
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ApplicationWorkflowNotification(
                $application,
                'report_card_awaiting_pegawai_jp',
                'Laporan aktiviti telah disemak Admin JP dan menunggu pengesahan Pegawai JP.',
            ),
        );
    }

    public function reportCardApproved(Application $application): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'report_card_approved',
                'Laporan aktiviti anda telah disahkan Pegawai JP.',
            ),
        );
    }

    public function reportCardReturned(Application $application, string $reason = ''): void
    {
        $application->loadMissing('alp.users');
        $owners = $application->alp?->users ?? collect();

        $msg = 'Laporan aktiviti dikembalikan untuk pembetulan.'.($reason !== '' ? ' Sebab: '.$reason : '');

        Notification::send(
            $owners,
            new ApplicationWorkflowNotification(
                $application,
                'report_card_returned',
                $msg,
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

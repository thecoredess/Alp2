<?php

namespace App\Services\Application;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Notification\ApplicationNotifier;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Jejak status pembayaran/baucar URS (UR-M06) — metadata operasi.
 * Tidak mencipta transaksi ledger; belanja projek kekal melalui ProjectExpense.
 */
class ApplicationPaymentService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly ApplicationNotifier $notifier,
    ) {}

    /**
     * @param  array{
     *   payment_status: string,
     *   payment_voucher_no?: ?string,
     *   payment_supplier_no?: ?string,
     *   payment_voucher_date?: ?string,
     *   payment_reference?: ?string,
     *   payment_remarks?: ?string,
     *   paid_at?: ?string,
     *   sent_to_jkew_at?: ?string,
     *   jkew_crosscheck_status?: ?string,
     *   jkew_crosscheck_remarks?: ?string
     * }  $data
     */
    public function update(Application $application, User $actor, array $data): Application
    {
        return DB::transaction(function () use ($application, $actor, $data) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            if ($application->status !== ApplicationStatus::APPROVED) {
                throw new InvalidArgumentException('Status pembayaran hanya untuk permohonan yang telah diluluskan.');
            }

            $status = ApplicationPaymentStatus::from($data['payment_status']);
            $from = $application->payment_status;

            if ($status === ApplicationPaymentStatus::VOUCHER_PREPARED) {
                if (blank($data['payment_supplier_no'] ?? null) && blank($application->payment_supplier_no)) {
                    throw new InvalidArgumentException('No. pembekal diperlukan untuk Baucar Disedia.');
                }
                if (blank($data['payment_voucher_no'] ?? null) && blank($application->payment_voucher_no)) {
                    throw new InvalidArgumentException('No. baucar diperlukan untuk Baucar Disedia.');
                }
                if (blank($data['payment_voucher_date'] ?? null) && blank($application->payment_voucher_date)) {
                    throw new InvalidArgumentException('Tarikh baucar diperlukan untuk Baucar Disedia.');
                }
            }

            if ($status === ApplicationPaymentStatus::PAID) {
                if (blank($data['payment_voucher_no'] ?? null) && blank($application->payment_voucher_no)) {
                    throw new InvalidArgumentException('No. baucar diperlukan sebelum menanda sebagai Dibayar.');
                }
            }

            if ($status === ApplicationPaymentStatus::SENT_TO_JKEW) {
                if (blank($data['sent_to_jkew_at'] ?? null) && blank($application->sent_to_jkew_at)) {
                    $data['sent_to_jkew_at'] = now()->toDateTimeString();
                }
            }

            $application->payment_status = $status;
            $application->payment_voucher_no = $data['payment_voucher_no'] ?? $application->payment_voucher_no;
            $application->payment_supplier_no = $data['payment_supplier_no'] ?? $application->payment_supplier_no;
            $application->payment_voucher_date = array_key_exists('payment_voucher_date', $data)
                ? $data['payment_voucher_date']
                : $application->payment_voucher_date;
            $application->payment_reference = $data['payment_reference'] ?? $application->payment_reference;
            $application->payment_remarks = $data['payment_remarks'] ?? $application->payment_remarks;
            $application->payment_updated_by = $actor->id;
            $application->payment_updated_at = now();

            if (array_key_exists('sent_to_jkew_at', $data) && filled($data['sent_to_jkew_at'])) {
                $application->sent_to_jkew_at = $data['sent_to_jkew_at'];
            } elseif ($status === ApplicationPaymentStatus::SENT_TO_JKEW && blank($application->sent_to_jkew_at)) {
                $application->sent_to_jkew_at = now();
            }

            if (array_key_exists('jkew_crosscheck_status', $data) && filled($data['jkew_crosscheck_status'])) {
                $application->jkew_crosscheck_status = $data['jkew_crosscheck_status'];
            }
            if (array_key_exists('jkew_crosscheck_remarks', $data)) {
                $application->jkew_crosscheck_remarks = $data['jkew_crosscheck_remarks'];
            }

            if ($status === ApplicationPaymentStatus::PAID) {
                $application->paid_at = ! empty($data['paid_at'])
                    ? $data['paid_at']
                    : ($application->paid_at ?? now());
            } elseif ($status === ApplicationPaymentStatus::CANCELLED) {
                // kekalkan paid_at jika pernah dibayar; biasanya null
            } else {
                $application->paid_at = null;
            }

            $application->save();

            $this->audit->log('APPLICATION_PAYMENT_UPDATED', $application, null, [
                'from' => $from?->value,
                'to' => $status->value,
                'voucher_no' => $application->payment_voucher_no,
                'supplier_no' => $application->payment_supplier_no,
                'voucher_date' => $application->payment_voucher_date?->format('Y-m-d'),
                'reference' => $application->payment_reference,
                'sent_to_jkew_at' => $application->sent_to_jkew_at?->toDateTimeString(),
                'jkew_crosscheck' => $application->jkew_crosscheck_status?->value,
            ]);

            if ($status === ApplicationPaymentStatus::VOUCHER_PREPARED) {
                $this->notifier->paymentVoucherPrepared($application);
            } elseif ($status === ApplicationPaymentStatus::PAID) {
                $this->notifier->paymentPaid($application);
            }

            return $application;
        });
    }
}

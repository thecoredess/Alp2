<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\FinancialYearStatus;
use App\Models\Allocation;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\DocumentRequirement;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Notification\ApplicationNotifier;
use App\Support\Money;
use App\Support\UrsContributionPolicy;
use Illuminate\Support\Facades\DB;

/**
 * Tindakan penghantaran permohonan — atomik & selamat-serentak.
 *
 * TIADA transaksi ledger dicipta di sini: penghantaran BUKAN komitmen kewangan.
 * Kunci baris allocation (lockForUpdate) menyerikan penghantaran serentak bagi
 * ALP+tahun yang sama supaya dua penghantaran tidak boleh melebihi baki yang sama.
 */
class ApplicationSubmissionService
{
    public function __construct(
        private readonly ApplicationBudgetService $appBudget,
        private readonly AuditService $audit,
        private readonly ApplicationNotifier $notifier,
        private readonly RecipientRegistry $recipients,
    ) {}

    public function submit(Application $application, User $user): Application
    {
        return DB::transaction(function () use ($application, $user) {
            // (2) Kunci akaun peruntukan ALP+tahun untuk menyerikan penghantaran serentak.
            Allocation::query()
                ->where('alp_id', $application->alp_id)
                ->where('financial_year_id', $application->financial_year_id)
                ->lockForUpdate()
                ->first();

            // Muat semula keadaan terkini dalam transaksi.
            $application->refresh();
            $application->load(['financialYear', 'budgetItems']);

            // (3) Mesti DRAFT.
            if (! $application->isDraft()) {
                throw new ApplicationException('Hanya permohonan berstatus Draf boleh dihantar.');
            }

            // (4) Tahun kewangan mesti Aktif/Dibuka (bukan Ditutup).
            $year = $application->financialYear;
            if ($year->isClosed() || ! in_array($year->status, [FinancialYearStatus::ACTIVE, FinancialYearStatus::OPEN], true)) {
                throw new ApplicationException('Tahun kewangan tidak sah atau telah ditutup — permohonan tidak boleh dihantar.');
            }

            // (5) Kira semula jumlah dipohon dengan tepat dari item bajet.
            $total = Money::zero();
            foreach ($application->budgetItems as $item) {
                // Sahkan semula setiap item (kuantiti × harga seunit).
                $expected = Money::of((string) $item->unit_cost)->times((int) $item->quantity);
                $total = $total->plus($expected);
            }

            // (6) Jumlah > 0.
            if (! $total->isPositive()) {
                throw new ApplicationException('Jumlah dipohon mesti melebihi RM0.00. Sila tambah item bajet.');
            }

            // (7) Dokumen wajib mesti lengkap.
            $missing = $this->missingRequiredDocuments($application);
            if ($missing->isNotEmpty()) {
                $labels = $missing->map(fn ($t) => $t->label())->implode(', ');
                throw new ApplicationException("Dokumen wajib belum lengkap: {$labels}.");
            }

            // (8-10) Semakan baki: tidak boleh melebihi (Ledger Available − Pending lain).
            $availableForNew = $this->appBudget->availableForNewRequest(
                $application->alp_id,
                $application->financial_year_id,
                $application->id,
            );

            if ($total->greaterThan($availableForNew)) {
                throw new ApplicationException(sprintf(
                    'Baki peruntukan tidak mencukupi. Tersedia untuk permohonan baharu: RM%s; jumlah dipohon: RM%s.',
                    $availableForNew->format(),
                    $total->format(),
                ));
            }

            foreach (UrsContributionPolicy::validateApplicationAmount($total) as $error) {
                throw new ApplicationException($error);
            }

            foreach (UrsContributionPolicy::validatePeriodQuota(
                $total,
                $application->alp_id,
                $application->financial_year_id,
                (int) $year->year,
                $application->id,
            ) as $error) {
                throw new ApplicationException($error);
            }

            $this->assertUrsRecipientRules($application);

            // (11-13) Snapshot jumlah & tukar status.
            $from = $application->status;
            $application->requested_amount = $total->value();
            $application->is_short_notice = UrsContributionPolicy::isShortNotice($application->proposed_start_date);
            $application->status = ApplicationStatus::SUBMITTED;
            $application->submitted_at = now();
            $application->updated_by = $user->id;
            $application->save();

            // (14) Sejarah status (append-only).
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $from->value,
                'to_status' => ApplicationStatus::SUBMITTED->value,
                'changed_by' => $user->id,
                'remarks' => 'Permohonan dihantar',
                'created_at' => now(),
            ]);

            // (15) Audit.
            $this->audit->log('APPLICATION_SUBMITTED', $application, null, [
                'requested_amount' => $total->value(),
                'financial_year' => $year->year,
                'application_number' => $application->application_number,
            ]);

            $this->notifier->submitted($application);

            return $application;
        });
    }

    /**
     * Hantar semula permohonan selepas pembetulan (REVISION_REQUIRED → SUBMITTED).
     * Menambah revision_number, mengesahkan semula bajet & dokumen, dan memulakan
     * semula aliran semakan dari peringkat Urus Setia. Rekod pusingan sebelum
     * dikekalkan sebagai sejarah. Atomik.
     */
    public function resubmit(Application $application, User $user): Application
    {
        return DB::transaction(function () use ($application, $user) {
            Allocation::query()
                ->where('alp_id', $application->alp_id)
                ->where('financial_year_id', $application->financial_year_id)
                ->lockForUpdate()
                ->first();

            $application->refresh();
            $application->load(['financialYear', 'budgetItems']);

            if ($application->status !== ApplicationStatus::REVISION_REQUIRED) {
                throw new ApplicationException('Hanya permohonan berstatus Perlu Pembetulan boleh dihantar semula.');
            }

            $year = $application->financialYear;
            if ($year->isClosed() || ! in_array($year->status, [FinancialYearStatus::ACTIVE, FinancialYearStatus::OPEN], true)) {
                throw new ApplicationException('Tahun kewangan tidak sah atau telah ditutup.');
            }

            // Kira semula & sahkan.
            $total = Money::zero();
            foreach ($application->budgetItems as $item) {
                $total = $total->plus(Money::of((string) $item->unit_cost)->times((int) $item->quantity));
            }
            if (! $total->isPositive()) {
                throw new ApplicationException('Jumlah dipohon mesti melebihi RM0.00.');
            }

            $missing = $this->missingRequiredDocuments($application);
            if ($missing->isNotEmpty()) {
                throw new ApplicationException('Dokumen wajib belum lengkap: '.$missing->map(fn ($t) => $t->label())->implode(', ').'.');
            }

            $availableForNew = $this->appBudget->availableForNewRequest(
                $application->alp_id, $application->financial_year_id, $application->id,
            );
            if ($total->greaterThan($availableForNew)) {
                throw new ApplicationException(sprintf(
                    'Baki peruntukan tidak mencukupi. Tersedia: RM%s; jumlah: RM%s.',
                    $availableForNew->format(), $total->format(),
                ));
            }

            foreach (UrsContributionPolicy::validateApplicationAmount($total) as $error) {
                throw new ApplicationException($error);
            }

            foreach (UrsContributionPolicy::validatePeriodQuota(
                $total,
                $application->alp_id,
                $application->financial_year_id,
                (int) $year->year,
                $application->id,
            ) as $error) {
                throw new ApplicationException($error);
            }

            $this->assertUrsRecipientRules($application);

            // Tandakan rekod revisi pusingan semasa sebagai dihantar semula.
            $application->revisions()
                ->where('revision_number', $application->revision_number)
                ->whereNull('resubmitted_at')
                ->update(['resubmitted_at' => now()]);

            $from = $application->status;
            $application->requested_amount = $total->value();
            $application->is_short_notice = UrsContributionPolicy::isShortNotice($application->proposed_start_date);
            $application->revision_number = $application->revision_number + 1; // pusingan baharu
            $application->status = ApplicationStatus::SUBMITTED;
            $application->submitted_at = now();
            $application->updated_by = $user->id;
            $application->save();

            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $from->value,
                'to_status' => ApplicationStatus::SUBMITTED->value,
                'changed_by' => $user->id,
                'remarks' => 'Permohonan dihantar semula (pembetulan #'.$application->revision_number.')',
                'created_at' => now(),
            ]);

            $this->audit->log('APPLICATION_RESUBMITTED', $application, null, [
                'requested_amount' => $total->value(),
                'revision_number' => $application->revision_number,
                'application_number' => $application->application_number,
            ]);

            $this->notifier->submitted($application);

            return $application;
        });
    }

    /**
     * Dokumen wajib yang belum dimuat naik untuk permohonan ini.
     *
     * @return \Illuminate\Support\Collection<int, \App\Enums\DocumentType>
     */
    public function missingRequiredDocuments(Application $application): \Illuminate\Support\Collection
    {
        $required = DocumentRequirement::requiredFor($application->application_type);

        // Normalkan kepada nilai string (pluck mungkin memulangkan enum atau string).
        $uploadedValues = $application->documents()->pluck('document_type')
            ->map(fn ($t) => $t instanceof \App\Enums\DocumentType ? $t->value : $t)
            ->all();

        return $required->reject(fn ($type) => in_array($type->value, $uploadedValues, true))->values();
    }

    /**
     * BR-008 / BR-009 / BR-010 / BR-012 / BR-023.
     */
    private function assertUrsRecipientRules(Application $application): void
    {
        if (blank($application->recipient_name) || blank($application->recipient_ros_number)) {
            throw new ApplicationException('Maklumat penerima sumbangan (nama & no. ROS) wajib diisi sebelum hantar.');
        }

        if (blank($application->program_category)) {
            throw new ApplicationException('Jenis program URS wajib dipilih sebelum hantar.');
        }

        if (blank($application->compliance_declared_at)) {
            throw new ApplicationException(
                'Sila sahkan deklarasi pematuhan (bukan kos pentadbiran/politik/perayaan/keagamaan — BR-012) sebelum hantar.'
            );
        }

        if (! UrsContributionPolicy::isKualaLumpurAddress(
            $application->recipient_address,
            $application->location,
        )) {
            throw new ApplicationException(
                'Alamat persatuan / lokasi program mesti di Kuala Lumpur (BR-010).'
            );
        }

        $ros = trim((string) $application->recipient_ros_number);
        $this->recipients->syncFromApplication($application);
        $application->refresh();

        $duplicateQuery = Application::query()
            ->where('financial_year_id', $application->financial_year_id)
            ->where('id', '!=', $application->id)
            ->whereIn('status', [
                ApplicationStatus::SUBMITTED->value,
                ApplicationStatus::UNDER_SECRETARIAT_REVIEW->value,
                ApplicationStatus::UNDER_FINANCE_REVIEW->value,
                ApplicationStatus::UNDER_TECHNICAL_REVIEW->value,
                ApplicationStatus::PENDING_APPROVAL->value,
                ApplicationStatus::REVISION_REQUIRED->value,
                ApplicationStatus::APPROVED->value,
                ApplicationStatus::IN_PROGRESS->value,
                ApplicationStatus::COMPLETED->value,
                ApplicationStatus::CLOSED->value,
            ]);

        if ($application->recipient_id) {
            $duplicateQuery->where(function ($q) use ($application, $ros) {
                $q->where('recipient_id', $application->recipient_id)
                    ->orWhereRaw('LOWER(TRIM(recipient_ros_number)) = ?', [mb_strtolower($ros)]);
            });
        } else {
            $duplicateQuery->whereRaw('LOWER(TRIM(recipient_ros_number)) = ?', [mb_strtolower($ros)]);
        }

        if ($duplicateQuery->exists()) {
            throw new ApplicationException(
                'Persatuan dengan no. ROS ini telah mempunyai permohonan dalam tahun kewangan semasa (BR-009 / BR-023).'
            );
        }
    }
}

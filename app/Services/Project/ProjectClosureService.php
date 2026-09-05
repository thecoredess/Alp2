<?php

namespace App\Services\Project;

use App\Enums\ProjectExpenseStatus;
use App\Enums\ProjectStatus;
use App\Models\Allocation;
use App\Models\Project;
use App\Models\ProjectStatusHistory;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use Illuminate\Support\Facades\DB;

/**
 * Penutupan projek — atomik. Melepaskan baki komitmen tidak diguna melalui
 * COMMITMENT_RELEASE. Tiada projek CLOSED dengan komitmen tidak direkonsiliasi.
 */
class ProjectClosureService
{
    public function __construct(
        private readonly BudgetService $budget,
        private readonly ProjectFinancialService $financial,
        private readonly AuditService $audit,
    ) {}

    public function close(Project $project, User $user): Project
    {
        return DB::transaction(function () use ($project, $user) {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

            // (4) Status: mesti COMPLETED.
            if ($project->status !== ProjectStatus::COMPLETED) {
                throw new ProjectException('Projek mesti berstatus Selesai sebelum ditutup.');
            }
            // (5) Laporan akhir dihantar.
            $report = $project->report;
            if (! $report || ! $report->submitted_at) {
                throw new ProjectException('Laporan akhir mesti dihantar sebelum penutupan.');
            }
            // (6) Tiada perbelanjaan belum selesai.
            if ($project->expenses()->whereIn('status', ProjectExpenseStatus::unresolvedValues())->exists()) {
                throw new ProjectException('Terdapat perbelanjaan yang belum diselesaikan (draf/menunggu/pembetulan).');
            }
            // (6b) Semua dokumen penutupan wajib mesti dimuat naik.
            $missing = $this->missingClosureDocuments($project);
            if ($missing->isNotEmpty()) {
                throw new ProjectException('Dokumen penutupan wajib belum lengkap: '.$missing->map->label()->implode(', ').'.');
            }

            // (7) Kira baki komitmen tidak diguna dari ledger.
            $unused = $this->financial->outstandingCommitment($project);

            // (8) Lepaskan jika > 0 (Seksyen 33: jangan cipta transaksi bernilai sifar).
            if ($unused->isPositive()) {
                $allocation = Allocation::where('alp_id', $project->alp_id)
                    ->where('financial_year_id', $project->financial_year_id)
                    ->lockForUpdate()->first();
                if (! $allocation) {
                    throw new ProjectException('Tiada peruntukan berkaitan untuk pelepasan komitmen.');
                }
                $this->budget->recordCommitmentRelease($allocation, $unused, $project);
            }

            // (9) Tukar status CLOSED + tarikh.
            $this->transition($project, ProjectStatus::CLOSED, $user, 'Projek ditutup');

            // Rekonsiliasi: baki komitmen mesti 0 selepas penutupan.
            $remaining = $this->financial->outstandingCommitment($project->fresh());
            if ($remaining->isPositive()) {
                throw new ProjectException('Rekonsiliasi gagal: baki komitmen masih wujud selepas penutupan.');
            }

            $this->audit->log('PROJECT_CLOSED', $project, null, [
                'project_number' => $project->project_number,
                'released' => $unused->value(),
            ]);

            return $project;
        });
    }

    /**
     * Pembatalan projek. Default Fasa 5: dihalang jika ada perbelanjaan disahkan (> 0);
     * jika tiada perbelanjaan, lepaskan komitmen penuh & tandakan CANCELLED.
     */
    public function cancel(Project $project, User $user, string $reason): Project
    {
        return DB::transaction(function () use ($project, $user, $reason) {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if (in_array($project->status, [ProjectStatus::CLOSED, ProjectStatus::CANCELLED], true)) {
                throw new ProjectException('Projek telah ditutup/dibatalkan.');
            }
            if ($this->financial->verifiedSpent($project)->isPositive()) {
                throw new ProjectException('Projek dengan perbelanjaan disahkan tidak boleh dibatalkan secara mudah (perlu penutupan terkawal).');
            }
            if ($project->expenses()->whereIn('status', ProjectExpenseStatus::unresolvedValues())->exists()) {
                throw new ProjectException('Selesaikan perbelanjaan belum selesai dahulu.');
            }

            $outstanding = $this->financial->outstandingCommitment($project);
            if ($outstanding->isPositive()) {
                $allocation = Allocation::where('alp_id', $project->alp_id)
                    ->where('financial_year_id', $project->financial_year_id)
                    ->lockForUpdate()->first();
                if ($allocation) {
                    $this->budget->recordCommitmentRelease($allocation, $outstanding, $project);
                }
            }

            $this->transition($project, ProjectStatus::CANCELLED, $user, 'Projek dibatalkan: '.$reason);
            $this->audit->log('PROJECT_CANCELLED', $project, null, ['reason' => $reason, 'released' => $outstanding->value()]);

            return $project;
        });
    }

    /**
     * Jenis dokumen penutupan wajib yang belum dimuat naik.
     *
     * @return \Illuminate\Support\Collection<int, \App\Enums\ProjectDocumentType>
     */
    public function missingClosureDocuments(Project $project): \Illuminate\Support\Collection
    {
        $required = \App\Models\ProjectDocumentRequirement::requiredFor($project->project_type, 'closure_evidence');
        if ($required->isEmpty()) {
            return collect();
        }

        $present = $project->documents()
            ->where('category', 'closure_evidence')
            ->pluck('document_type')
            ->map(fn ($t) => $t instanceof \App\Enums\ProjectDocumentType ? $t->value : $t)
            ->all();

        return $required->reject(fn (\App\Enums\ProjectDocumentType $t) => in_array($t->value, $present, true))->values();
    }

    private function transition(Project $project, ProjectStatus $to, User $user, string $remarks): void
    {
        $from = $project->status;
        $project->status = $to;
        if ($to === ProjectStatus::CLOSED && ! $project->actual_completion_date) {
            $project->actual_completion_date = now();
        }
        $project->save();

        ProjectStatusHistory::create([
            'project_id' => $project->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by' => $user->id,
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }
}

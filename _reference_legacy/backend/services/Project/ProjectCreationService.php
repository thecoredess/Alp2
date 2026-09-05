<?php

namespace App\Services\Project;

use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Project;
use App\Models\ProjectStatusHistory;
use App\Models\User;
use App\Services\Audit\AuditService;

/**
 * Mencipta projek daripada permohonan yang diluluskan.
 * IDEMPOTEN: satu permohonan diluluskan = tepat satu projek (unique application_id).
 * Dipanggil dalam transaksi kelulusan akhir (Fasa 4) supaya atomik.
 */
class ProjectCreationService
{
    public function __construct(
        private readonly ProjectNumberGenerator $numbers,
        private readonly AuditService $audit,
    ) {}

    public function createFromApproved(Application $application, ?User $actor = null): Project
    {
        // Idempoten: jika projek sudah wujud, pulangkan sedia ada.
        $existing = Project::where('application_id', $application->id)->first();
        if ($existing) {
            return $existing;
        }

        $number = $this->numbers->next($application->application_type, $application->financialYear->year);

        $project = Project::create([
            'project_number' => $number,
            'application_id' => $application->id,
            'alp_id' => $application->alp_id,
            'financial_year_id' => $application->financial_year_id,
            'project_name' => $application->project_title,
            'project_type' => $application->application_type,
            // Snapshot tidak boleh ubah daripada jumlah diluluskan.
            'approved_amount' => $application->requested_amount,
            'start_date' => $application->proposed_start_date,
            'end_date' => $application->proposed_end_date,
            'status' => ProjectStatus::NOT_STARTED,
            'progress_percent' => 0,
            'created_by' => $actor?->id ?? auth()->id(),
        ]);

        ProjectStatusHistory::create([
            'project_id' => $project->id,
            'from_status' => null,
            'to_status' => ProjectStatus::NOT_STARTED->value,
            'changed_by' => $actor?->id ?? auth()->id(),
            'remarks' => 'Projek dicipta daripada permohonan diluluskan',
            'created_at' => now(),
        ]);

        $this->audit->log('PROJECT_CREATED', $project, null, [
            'application_id' => $application->id,
            'project_number' => $number,
            'approved_amount' => (string) $project->approved_amount,
        ]);

        return $project;
    }
}

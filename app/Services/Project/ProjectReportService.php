<?php

namespace App\Services\Project;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\User;
use App\Services\Audit\AuditService;

/**
 * Laporan akhir projek. Dihantar selepas projek COMPLETED, sebelum penutupan.
 */
class ProjectReportService
{
    public function __construct(private readonly AuditService $audit) {}

    public function submit(Project $project, User $user, array $data): ProjectReport
    {
        if ($project->status !== ProjectStatus::COMPLETED) {
            throw new ProjectException('Laporan akhir hanya boleh dihantar selepas projek Selesai.');
        }

        $report = ProjectReport::updateOrCreate(
            ['project_id' => $project->id],
            [
                'summary' => $data['summary'] ?? null,
                'outcome' => $data['outcome'] ?? null,
                'beneficiary_count' => $data['beneficiary_count'] ?? null,
                'impact_summary' => $data['impact_summary'] ?? null,
                'completion_summary' => $data['completion_summary'] ?? null,
                'issues' => $data['issues'] ?? null,
                'lessons_learned' => $data['lessons_learned'] ?? null,
                'final_remarks' => $data['final_remarks'] ?? null,
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ],
        );

        $this->audit->log('PROJECT_REPORT_SUBMITTED', $project, null, ['report_id' => $report->id]);

        return $report;
    }
}

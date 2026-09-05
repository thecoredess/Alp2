<?php

namespace App\Services\Project;

use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectProgressHistory;
use App\Models\ProjectStatusHistory;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

/**
 * Kitaran hayat operasi projek: start, kemajuan, milestone, selesai.
 * Peralihan status dikawal; sejarah append-only; diaudit.
 * Tindakan kewangan (perbelanjaan/penutupan) berada dalam servis berasingan.
 */
class ProjectService
{
    public function __construct(
        private readonly ProjectFinancialService $financial,
        private readonly AuditService $audit,
    ) {}

    /** NOT_STARTED → IN_PROGRESS. */
    public function start(Project $project, User $user): Project
    {
        return DB::transaction(function () use ($project, $user) {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if ($project->status !== ProjectStatus::NOT_STARTED) {
                throw new ProjectException('Hanya projek yang belum bermula boleh dimulakan.');
            }
            // Mesti ada komitmen bajet.
            if (! $this->financial->outstandingCommitment($project)->isPositive()) {
                throw new ProjectException('Tiada komitmen bajet untuk projek ini.');
            }
            $project->loadMissing('financialYear');
            if ($project->financialYear->isClosed()) {
                throw new ProjectException('Tahun kewangan telah ditutup.');
            }

            $this->transition($project, ProjectStatus::IN_PROGRESS, $user, 'Projek dimulakan');
            $project->update(['actual_start_date' => now()]);

            $this->audit->log('PROJECT_STARTED', $project, null, ['project_number' => $project->project_number]);

            return $project;
        });
    }

    /** Kemas kini kemajuan (0–100) + sejarah. Boleh set DELAYED / kembali IN_PROGRESS. */
    public function updateProgress(Project $project, User $user, int $percent, ?string $remarks, ?ProjectStatus $status = null): Project
    {
        if ($percent < 0 || $percent > 100) {
            throw new ProjectException('Kemajuan mesti antara 0 dan 100.');
        }
        if (! $project->status->isActive() && $project->status !== ProjectStatus::NOT_STARTED) {
            throw new ProjectException('Kemajuan hanya boleh dikemas kini semasa projek aktif.');
        }

        return DB::transaction(function () use ($project, $user, $percent, $remarks, $status) {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

            // Tukar status (delayed/in_progress) jika diminta & sah.
            if ($status && in_array($status, [ProjectStatus::IN_PROGRESS, ProjectStatus::DELAYED], true) && $status !== $project->status) {
                $this->transition($project, $status, $user, 'Status projek dikemas kini');
            }

            $project->update(['progress_percent' => $percent]);

            ProjectProgressHistory::create([
                'project_id' => $project->id,
                'progress_percent' => $percent,
                'status' => $project->status->value,
                'remarks' => $remarks,
                'updated_by' => $user->id,
                'created_at' => now(),
            ]);

            $this->audit->log('PROJECT_PROGRESS_UPDATED', $project, null, ['progress' => $percent]);

            return $project;
        });
    }

    /** IN_PROGRESS/DELAYED → COMPLETED (kerja siap; belum ditutup dari segi kewangan). */
    public function complete(Project $project, User $user): Project
    {
        return DB::transaction(function () use ($project, $user) {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if (! $project->status->isActive()) {
                throw new ProjectException('Hanya projek aktif boleh ditandakan selesai.');
            }
            if ($project->progress_percent < 100) {
                throw new ProjectException('Kemajuan mesti 100% sebelum projek boleh diselesaikan.');
            }
            // Semua milestone (jika ada) mesti selesai.
            $incomplete = $project->milestones()->where('status', '!=', MilestoneStatus::COMPLETED->value)->count();
            if ($incomplete > 0) {
                throw new ProjectException('Semua milestone mesti diselesaikan dahulu.');
            }

            $this->transition($project, ProjectStatus::COMPLETED, $user, 'Projek selesai');
            $project->update(['actual_completion_date' => now()]);

            $this->audit->log('PROJECT_COMPLETED', $project, null, ['project_number' => $project->project_number]);

            return $project;
        });
    }

    // ── Milestone ───────────────────────────────────────────────

    public function addMilestone(Project $project, User $user, array $data): ProjectMilestone
    {
        $this->assertOperational($project);

        $milestone = $project->milestones()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'target_date' => $data['target_date'] ?? null,
            'status' => MilestoneStatus::PENDING,
            'sort_order' => (int) $project->milestones()->max('sort_order') + 1,
            'remarks' => $data['remarks'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->audit->log('PROJECT_MILESTONE_CREATED', $project, null, ['milestone_id' => $milestone->id]);

        return $milestone;
    }

    public function updateMilestone(ProjectMilestone $milestone, User $user, array $data): ProjectMilestone
    {
        $this->assertOperational($milestone->project);

        $status = isset($data['status']) ? MilestoneStatus::from($data['status']) : $milestone->status;

        $milestone->update([
            'name' => $data['name'] ?? $milestone->name,
            'description' => $data['description'] ?? $milestone->description,
            'target_date' => $data['target_date'] ?? $milestone->target_date,
            'status' => $status,
            'completed_at' => $status === MilestoneStatus::COMPLETED ? ($milestone->completed_at ?? now()) : null,
            'remarks' => $data['remarks'] ?? $milestone->remarks,
            'updated_by' => $user->id,
        ]);

        $this->audit->log('PROJECT_MILESTONE_UPDATED', $milestone->project, null, ['milestone_id' => $milestone->id, 'status' => $status->value]);

        return $milestone;
    }

    // ── Bantuan ─────────────────────────────────────────────────

    private function assertOperational(Project $project): void
    {
        if (! $project->status->isOperationallyEditable()) {
            throw new ProjectException('Projek yang telah ditutup/dibatalkan tidak boleh diubah.');
        }
    }

    private function transition(Project $project, ProjectStatus $to, User $user, string $remarks): void
    {
        $from = $project->status;
        $project->status = $to;
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

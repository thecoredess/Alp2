<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('projects.view_all') || $user->alp_id !== null;
    }

    /** Papar projek: staf berkuasa atau pemilik ALP. */
    public function view(User $user, Project $project): bool
    {
        if ($user->can('projects.view_all')) {
            return true;
        }

        return $user->alp_id !== null && $user->alp_id === $project->alp_id;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.update') && $project->status->isOperationallyEditable();
    }

    public function progress(User $user, Project $project): bool
    {
        return $user->can('projects.progress') && $project->status->isOperationallyEditable();
    }

    public function milestones(User $user, Project $project): bool
    {
        return $user->can('projects.milestones') && $project->status->isOperationallyEditable();
    }

    public function complete(User $user, Project $project): bool
    {
        return $user->can('projects.complete');
    }

    public function close(User $user, Project $project): bool
    {
        return $user->can('projects.close');
    }

    /** Bina/hantar laporan akhir. */
    public function report(User $user, Project $project): bool
    {
        return $user->can('project-reports.create');
    }

    /** Muat naik/buang dokumen penutupan: projek belum ditutup. */
    public function manageClosureDocuments(User $user, Project $project): bool
    {
        return $user->can('projects.closure-documents.manage') && ! $project->isClosed();
    }

    public function viewClosureDocuments(User $user, Project $project): bool
    {
        return $user->can('projects.closure-documents.view') && $this->view($user, $project);
    }
}

<?php

namespace App\Policies;

use App\Models\ProjectExpense;
use App\Models\User;

class ProjectExpensePolicy
{
    public function view(User $user, ProjectExpense $expense): bool
    {
        if ($user->canAny(['expenses.view', 'projects.view_all'])) {
            return true;
        }
        $expense->loadMissing('project');

        return $user->alp_id !== null && $user->alp_id === $expense->project->alp_id;
    }

    /** Sunting/hantar: maker sendiri & status boleh disunting. */
    public function update(User $user, ProjectExpense $expense): bool
    {
        return $user->can('expenses.update')
            && $user->id === $expense->created_by
            && $expense->isEditableByMaker();
    }

    public function submit(User $user, ProjectExpense $expense): bool
    {
        return $user->can('expenses.submit')
            && $user->id === $expense->created_by
            && $expense->isEditableByMaker();
    }

    /** Sahkan: kebenaran + bukan maker/submitter (maker ≠ checker). */
    public function verify(User $user, ProjectExpense $expense): bool
    {
        return $user->can('expenses.verify')
            && $user->id !== $expense->created_by
            && $user->id !== $expense->submitted_by;
    }

    public function reject(User $user, ProjectExpense $expense): bool
    {
        return $user->can('expenses.reject')
            && $user->id !== $expense->created_by
            && $user->id !== $expense->submitted_by;
    }

    public function returnForRevision(User $user, ProjectExpense $expense): bool
    {
        return $user->can('expenses.return')
            && $user->id !== $expense->created_by
            && $user->id !== $expense->submitted_by;
    }

    /** Muat naik/buang dokumen bukti perbelanjaan: maker sendiri & masih boleh disunting. */
    public function manageDocuments(User $user, ProjectExpense $expense): bool
    {
        return $user->can('expenses.documents.manage')
            && $user->id === $expense->created_by
            && $expense->isEditableByMaker();
    }
}

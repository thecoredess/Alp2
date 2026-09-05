<?php

namespace App\Policies;

use App\Models\ProjectExpenseRefund;
use App\Models\User;

class ProjectExpenseRefundPolicy
{
    public function view(User $user, ProjectExpenseRefund $refund): bool
    {
        if ($user->canAny(['refunds.view', 'projects.view_all'])) {
            return true;
        }
        $refund->loadMissing('project');

        return $user->alp_id !== null && $user->alp_id === $refund->project->alp_id;
    }

    /** Sunting/hantar: maker sendiri & status boleh disunting. */
    public function update(User $user, ProjectExpenseRefund $refund): bool
    {
        return $user->can('refunds.update')
            && $user->id === $refund->created_by
            && $refund->isEditableByMaker();
    }

    public function submit(User $user, ProjectExpenseRefund $refund): bool
    {
        return $user->can('refunds.submit')
            && $user->id === $refund->created_by
            && $refund->isEditableByMaker();
    }

    public function verify(User $user, ProjectExpenseRefund $refund): bool
    {
        return $user->can('refunds.verify')
            && $user->id !== $refund->created_by
            && $user->id !== $refund->submitted_by;
    }

    public function reject(User $user, ProjectExpenseRefund $refund): bool
    {
        return $user->can('refunds.reject')
            && $user->id !== $refund->created_by
            && $user->id !== $refund->submitted_by;
    }

    public function returnForRevision(User $user, ProjectExpenseRefund $refund): bool
    {
        return $user->can('refunds.return')
            && $user->id !== $refund->created_by
            && $user->id !== $refund->submitted_by;
    }

    /** Muat naik/buang dokumen bukti refund: maker sendiri & masih boleh disunting. */
    public function manageDocuments(User $user, ProjectExpenseRefund $refund): bool
    {
        return $user->can('refunds.update')
            && $user->id === $refund->created_by
            && $refund->isEditableByMaker();
    }
}

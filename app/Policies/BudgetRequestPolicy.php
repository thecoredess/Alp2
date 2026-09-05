<?php

namespace App\Policies;

use App\Models\BudgetRequest;
use App\Models\User;

class BudgetRequestPolicy
{
    /** Boleh melihat cadangan: maker sendiri atau pihak berkuasa/checker. */
    public function view(User $user, BudgetRequest $request): bool
    {
        if ($user->id === $request->created_by) {
            return true;
        }

        return $user->canAny([
            'allocations.view_all', 'budget.view_all',
            'allocations.approve', 'adjustments.approve',
        ]);
    }

    /** Sunting/hantar: maker sendiri, status boleh disunting, dan ada kebenaran. */
    public function update(User $user, BudgetRequest $request): bool
    {
        return $user->id === $request->created_by
            && $request->isEditableByMaker()
            && $user->can($request->request_type->permission('request.update'));
    }

    public function submit(User $user, BudgetRequest $request): bool
    {
        return $user->id === $request->created_by
            && $request->isEditableByMaker()
            && $user->can($request->request_type->permission('request.submit'));
    }

    /** Kelulusan: mesti ada kebenaran approve DAN bukan maker/submitter (maker≠checker). */
    public function approve(User $user, BudgetRequest $request): bool
    {
        return $user->can($request->request_type->permission('approve'))
            && $user->id !== $request->created_by
            && $user->id !== $request->submitted_by;
    }

    public function reject(User $user, BudgetRequest $request): bool
    {
        return $user->can($request->request_type->permission('reject'))
            && $user->id !== $request->created_by
            && $user->id !== $request->submitted_by;
    }

    public function returnForRevision(User $user, BudgetRequest $request): bool
    {
        return $user->can($request->request_type->permission('return'))
            && $user->id !== $request->created_by
            && $user->id !== $request->submitted_by;
    }
}

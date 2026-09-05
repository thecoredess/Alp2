<?php

namespace App\Policies;

use App\Models\Allocation;
use App\Models\User;

class AllocationPolicy
{
    /** Paparan senarai/overview peruntukan (pengurusan/kewangan/admin). */
    public function viewAny(User $user): bool
    {
        return $user->canAny(['allocations.view', 'budget.view_all']);
    }

    /**
     * Papar satu peruntukan: pegawai berkuasa ATAU pemilik ALP
     * (pengguna ALP / Urus Setia yang dikaitkan dengan ALP itu).
     */
    public function view(User $user, Allocation $allocation): bool
    {
        if ($user->canAny(['allocations.view', 'budget.view_all'])) {
            return true;
        }

        return $user->alp_id !== null && $user->alp_id === $allocation->alp_id;
    }

    /** Set peruntukan awal terus (Admin JP — URS Fasa 7.12). */
    public function create(User $user): bool
    {
        return $user->can('allocations.manage');
    }

    /** Laras peruntukan terus (Admin JP). */
    public function adjust(User $user, Allocation $allocation): bool
    {
        return $user->can('allocations.manage');
    }
}

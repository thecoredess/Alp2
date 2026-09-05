<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /** Boleh melihat sebarang senarai permohonan (sendiri atau semua). */
    public function viewAny(User $user): bool
    {
        return $user->can('applications.view_all') || $user->alp_id !== null;
    }

    /** Boleh melihat senarai "Semua Permohonan" (staf DBKL berkuasa). */
    public function viewAll(User $user): bool
    {
        return $user->can('applications.view_all');
    }

    /** Papar satu permohonan: pemilik ALP atau staf berkuasa. */
    public function view(User $user, Application $application): bool
    {
        if ($user->can('applications.view_all')) {
            return true;
        }

        return $user->alp_id !== null && $user->alp_id === $application->alp_id;
    }

    /** Cipta permohonan: mesti dikaitkan dengan ALP (ALP / Urus Setia ALP). */
    public function create(User $user): bool
    {
        return $user->can('applications.create') && $user->alp_id !== null;
    }

    /** Sunting draf: pemilik sahaja & masih DRAFT. */
    public function update(User $user, Application $application): bool
    {
        return $user->can('applications.create')
            && $user->alp_id !== null
            && $user->alp_id === $application->alp_id
            && $application->isEditableByOwner();
    }

    /** Hantar permohonan: sama seperti sunting draf. */
    public function submit(User $user, Application $application): bool
    {
        return $this->update($user, $application);
    }

    /** Muat turun / lihat dokumen: sama seperti lihat permohonan. */
    public function downloadDocument(User $user, Application $application): bool
    {
        return $this->view($user, $application);
    }

    /** Muat naik report card / laporan aktiviti (M07) selepas diluluskan. */
    public function uploadReportCard(User $user, Application $application): bool
    {
        if ($application->status !== \App\Enums\ApplicationStatus::APPROVED) {
            return false;
        }

        if ($user->can('applications.view_all')) {
            return true;
        }

        return $user->can('applications.create')
            && $user->alp_id !== null
            && $user->alp_id === $application->alp_id;
    }
}

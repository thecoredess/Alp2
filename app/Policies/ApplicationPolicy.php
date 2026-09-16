<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use App\Services\Application\ApplicationReportCardService;

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

        if ($user->alp_id !== null && $user->alp_id === $application->alp_id) {
            return true;
        }

        return $this->viewCrosscheckReference($user, $application);
    }

    /** Cipta permohonan: ALP sendiri, atau Admin JP bagi pihak ALP. */
    public function create(User $user): bool
    {
        if ($user->canCreateApplicationOnBehalf()) {
            return true;
        }

        return $user->can('applications.create') && $user->alp_id !== null;
    }

    /** Sunting draf: pemilik ALP, atau Admin JP (bagi pihak). */
    public function update(User $user, Application $application): bool
    {
        if (! $application->isEditableByOwner()) {
            return false;
        }

        if ($user->canCreateApplicationOnBehalf()) {
            return true;
        }

        return $user->can('applications.create')
            && $user->alp_id !== null
            && $user->alp_id === $application->alp_id;
    }

    /** Hantar permohonan: sama seperti sunting draf. */
    public function submit(User $user, Application $application): bool
    {
        return $this->update($user, $application);
    }

    /** Admin JP kemaskini Borang Penyaluran semasa semakan (SUBMITTED). */
    public function updateBorangDuringReview(User $user, Application $application): bool
    {
        if (! $user->canMakeFullJpReviewDecision()) {
            return false;
        }

        if (! $user->can('applications.review.secretariat')) {
            return false;
        }

        return $application->status === ApplicationStatus::SUBMITTED;
    }

    /** Muat turun / lihat dokumen: sama seperti lihat permohonan. */
    public function downloadDocument(User $user, Application $application): bool
    {
        return $this->view($user, $application);
    }

    /** Muat turun borang memo semakan silang (siap isi). */
    public function downloadCrosscheckMemo(User $user, Application $application): bool
    {
        return $user->can('applications.review.secretariat');
    }

    /** Muat naik borang ulasan JKEW selepas semakan silang. */
    public function uploadCrosscheckMemo(User $user, Application $application): bool
    {
        if ($user->can('payments.jkew_scope') || $user->can('payments.manage')) {
            return true;
        }

        return $user->can('applications.review.secretariat');
    }

    /** Rujukan semakan silang — staf DBKL sahaja (bukan ALP). */
    public function viewCrosscheckReference(User $user, Application $application): bool
    {
        if ($user->alp_id !== null && $user->alp_id === $application->alp_id && ! $user->can('applications.view_all')) {
            return false;
        }

        return $user->can('applications.view_all')
            || $user->can('applications.review.secretariat')
            || $user->can('payments.view')
            || $user->can('applications.approve');
    }

    /** Muat naik draf laporan aktiviti (M07) selepas diluluskan. */
    public function uploadReportCard(User $user, Application $application): bool
    {
        $service = app(ApplicationReportCardService::class);

        if (! $service->canUpload($application) && ! $service->hasDraft($application)) {
            return false;
        }

        return $this->ownsApplicationForReportCard($user, $application);
    }

    /** Hantar draf laporan aktiviti ke Admin JP. */
    public function submitReportCard(User $user, Application $application): bool
    {
        if (! app(ApplicationReportCardService::class)->canSubmit($application)) {
            return false;
        }

        return $this->ownsApplicationForReportCard($user, $application);
    }

    /** Batalkan draf laporan aktiviti. */
    public function discardReportCardDraft(User $user, Application $application): bool
    {
        if (! app(ApplicationReportCardService::class)->canDiscardDraft($application)) {
            return false;
        }

        return $this->ownsApplicationForReportCard($user, $application);
    }

    private function ownsApplicationForReportCard(User $user, Application $application): bool
    {
        if ($user->can('applications.view_all')) {
            return true;
        }

        return $user->can('applications.create')
            && $user->alp_id !== null
            && $user->alp_id === $application->alp_id;
    }
}

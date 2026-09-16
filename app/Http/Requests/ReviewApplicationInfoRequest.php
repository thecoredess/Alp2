<?php

namespace App\Http\Requests;

/** Kemaskini Borang Penyaluran semasa semakan Admin JP. */
class ReviewApplicationInfoRequest extends ApplicationInfoRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');

        return $application
            ? ($this->user()?->can('updateBorangDuringReview', $application) ?? false)
            : false;
    }
}

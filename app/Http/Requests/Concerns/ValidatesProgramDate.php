<?php

namespace App\Http\Requests\Concerns;

use App\Support\UrsContributionPolicy;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesProgramDate
{
    protected function validateProgramDate(Validator $validator, ?CarbonInterface $appliedAt = null): void
    {
        if ($validator->errors()->has('program_date')) {
            return;
        }

        // Admin JP: notis pendek dibenarkan (< 2 bulan).
        if ($this->user()?->canWaiveProgramLeadTime()) {
            return;
        }

        $raw = $this->input('program_date');
        if (blank($raw)) {
            return;
        }

        foreach (UrsContributionPolicy::validateProgramLeadTime(
            \Carbon\Carbon::parse($raw),
            $appliedAt,
        ) as $error) {
            $validator->errors()->add('program_date', $error);
        }
    }
}

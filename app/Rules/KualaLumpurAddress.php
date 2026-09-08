<?php

namespace App\Rules;

use App\Support\UrsContributionPolicy;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** BR-010: alamat persatuan mesti dalam Wilayah Persekutuan Kuala Lumpur. */
class KualaLumpurAddress implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! UrsContributionPolicy::isKualaLumpurAddress(is_string($value) ? $value : null)) {
            $fail('Alamat persatuan mesti berada dalam Wilayah Persekutuan Kuala Lumpur.');
        }
    }
}

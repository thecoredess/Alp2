<?php

namespace App\Http\Requests\Concerns;

use App\Support\KertasKerjaGuide;

trait ValidatesKertasKerja
{
    /** @return array<string, list<string|object>> */
    protected function kertasKerjaRules(): array
    {
        return KertasKerjaGuide::validationRules();
    }

    /** @return array<string, string> */
    protected function kertasKerjaAttributes(): array
    {
        return KertasKerjaGuide::validationAttributes();
    }
}

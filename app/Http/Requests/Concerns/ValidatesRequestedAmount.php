<?php

namespace App\Http\Requests\Concerns;

use App\Models\Application;
use App\Models\FinancialYear;
use App\Support\ApplicationAmountValidator;
use App\Support\Money;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesRequestedAmount
{
    protected function validateRequestedAmount(Validator $validator, int $alpId, int $financialYearId, int $calendarYear, ?int $excludeApplicationId = null): void
    {
        if ($validator->errors()->has('requested_amount')) {
            return;
        }

        $raw = $this->input('requested_amount');
        if ($raw === null || $raw === '') {
            return;
        }

        $amount = Money::of(number_format((float) $raw, 2, '.', ''));

        foreach (ApplicationAmountValidator::errorsFor(
            $amount,
            $alpId,
            $financialYearId,
            $calendarYear,
            $excludeApplicationId,
        ) as $error) {
            $validator->errors()->add('requested_amount', $error);
            break;
        }
    }

    protected function amountContextFromApplication(?Application $application): ?array
    {
        if (! $application) {
            return null;
        }

        $application->loadMissing(['alp', 'financialYear']);

        return [
            'alp_id' => $application->alp_id,
            'financial_year_id' => $application->financial_year_id,
            'calendar_year' => (int) $application->financialYear->year,
            'exclude_application_id' => $application->id,
        ];
    }

    protected function amountContextFromUser(): ?array
    {
        $user = $this->user();
        $year = FinancialYear::active();

        if ($user?->alp_id === null || $year === null) {
            return null;
        }

        return [
            'alp_id' => $user->alp_id,
            'financial_year_id' => $year->id,
            'calendar_year' => (int) $year->year,
            'exclude_application_id' => null,
        ];
    }
}

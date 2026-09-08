<?php

namespace App\Http\Requests;

use App\Enums\ProgramCategory;
use App\Http\Requests\Concerns\ValidatesProgramDate;
use App\Http\Requests\Concerns\ValidatesRequestedAmount;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Rules\KualaLumpurAddress;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Borang Penyaluran Sumbangan ALP — medan a–i (nama ALP dari akaun atau pilihan Admin JP). */
class ApplicationCreateRequest extends FormRequest
{
    use ValidatesProgramDate;
    use ValidatesRequestedAmount;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $onBehalf = $this->user()?->canCreateApplicationOnBehalf() ?? false;

        return [
            'alp_id' => [$onBehalf ? 'required' : 'nullable', 'integer', Rule::exists('alps', 'id')],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_ros_number' => ['required', 'string', 'max:100'],
            'program_date' => ['required', 'date'],
            'program_category' => ['required', Rule::enum(ProgramCategory::class)],
            'requested_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'purpose' => ['required', 'string', 'max:1000'],
            'recipient_bank_account' => ['required', 'string', 'max:64'],
            'recipient_address' => ['required', 'string', 'max:500', new KualaLumpurAddress],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $this->validateProgramDate($v, now());

            $ctx = $this->amountContextForCreate();
            if ($ctx === null) {
                return;
            }

            $this->validateRequestedAmount(
                $v,
                $ctx['alp_id'],
                $ctx['financial_year_id'],
                $ctx['calendar_year'],
                $ctx['exclude_application_id'],
            );
        });
    }

    /** @return array{alp_id: int, financial_year_id: int, calendar_year: int, exclude_application_id: null}|null */
    protected function amountContextForCreate(): ?array
    {
        $year = FinancialYear::active();
        if ($year === null) {
            return null;
        }

        $user = $this->user();
        $alpId = $user?->canCreateApplicationOnBehalf()
            ? (int) $this->input('alp_id')
            : (int) ($user?->alp_id ?? 0);

        if ($alpId <= 0 || ! Alp::whereKey($alpId)->exists()) {
            return null;
        }

        return [
            'alp_id' => $alpId,
            'financial_year_id' => $year->id,
            'calendar_year' => (int) $year->year,
            'exclude_application_id' => null,
        ];
    }

    public function attributes(): array
    {
        return [
            'alp_id' => 'ALP',
            'recipient_name' => 'nama persatuan',
            'recipient_ros_number' => 'no. ROS',
            'program_date' => 'tarikh program',
            'program_category' => 'jenis/kategori program',
            'requested_amount' => 'jumlah sumbangan',
            'purpose' => 'tujuan sumbangan',
            'recipient_bank_account' => 'no. akaun penerima sumbangan',
            'recipient_address' => 'alamat persatuan',
        ];
    }
}

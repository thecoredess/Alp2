<?php

namespace App\Http\Requests;

use App\Enums\ProgramCategory;
use App\Http\Requests\Concerns\ValidatesProgramDate;
use App\Http\Requests\Concerns\ValidatesRequestedAmount;
use App\Rules\KualaLumpurAddress;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Kemas kini draf Borang Penyaluran (medan a–i). */
class ApplicationInfoRequest extends FormRequest
{
    use ValidatesProgramDate;
    use ValidatesRequestedAmount;

    public function authorize(): bool
    {
        $application = $this->route('application');

        return $application
            ? ($this->user()?->can('update', $application) ?? false)
            : true;
    }

    public function rules(): array
    {
        return [
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
            $application = $this->route('application');
            $this->validateProgramDate($v, now());

            $ctx = $this->amountContextFromApplication($application);
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

    public function attributes(): array
    {
        return [
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

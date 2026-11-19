<?php

namespace App\Http\Requests;

use App\Enums\ProgramCategory;
use App\Http\Requests\Concerns\ValidatesKertasKerja;
use App\Http\Requests\Concerns\ValidatesProgramDate;
use App\Http\Requests\Concerns\ValidatesRequestedAmount;
use App\Models\Application;
use App\Rules\KualaLumpurAddress;
use App\Services\Application\ApplicationAssociationInviteService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Simpan draf borang persatuan (tanpa PDPA) — semasa muat naik lampiran. */
class AssociationAutosaveRequest extends FormRequest
{
    use ValidatesKertasKerja;
    use ValidatesProgramDate;
    use ValidatesRequestedAmount;

    public function authorize(): bool
    {
        return $this->inviteApplication() !== null;
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_ros_number' => ['required', 'string', 'max:100'],
            'program_date' => ['required', 'date'],
            'program_category' => ['required', Rule::enum(ProgramCategory::class)],
            'requested_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            ...$this->kertasKerjaRules(),
            'recipient_bank_account' => ['required', 'string', 'max:64'],
            'recipient_address' => ['required', 'string', 'max:500', new KualaLumpurAddress],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $application = $this->inviteApplication();
            if ($application === null) {
                return;
            }

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
            ...$this->kertasKerjaAttributes(),
            'recipient_bank_account' => 'no. akaun penerima sumbangan',
            'recipient_address' => 'alamat persatuan',
        ];
    }

    public function inviteApplication(): ?Application
    {
        $token = (string) $this->route('token');
        $application = app(ApplicationAssociationInviteService::class)->findActiveByPlainToken($token);

        if ($application) {
            app()->instance('associationInviteApplication', $application);
        }

        return $application;
    }
}

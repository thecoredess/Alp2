<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesKertasKerja;
use App\Http\Requests\Concerns\ValidatesProgramDate;
use App\Http\Requests\Concerns\ValidatesRequestedAmount;
use App\Enums\ProgramCategory;
use App\Models\Application;
use App\Services\Application\ApplicationAssociationInviteService;
use App\Rules\KualaLumpurAddress;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Borang persatuan (pautan awam) — medan sama seperti Langkah 1 ALP. */
class AssociationFillRequest extends FormRequest
{
    use ValidatesKertasKerja;
    use ValidatesProgramDate;
    use ValidatesRequestedAmount;

    public function authorize(): bool
    {
        $token = (string) $this->route('token');
        $application = app(ApplicationAssociationInviteService::class)->findActiveByPlainToken($token);

        if ($application) {
            app()->instance('associationInviteApplication', $application);

            return true;
        }

        return false;
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
            'pdpa_consent' => ['accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $application = $this->inviteApplication();
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
            'pdpa_consent' => 'persetujuan Notis Perlindungan Data Peribadi',
        ];
    }

    public function messages(): array
    {
        return [
            'pdpa_consent.accepted' => 'Sila bersetuju dengan Notis Perlindungan Data Peribadi DBKL sebelum menghantar borang.',
        ];
    }

    public function inviteApplication(): ?Application
    {
        $bound = app()->bound('associationInviteApplication')
            ? app('associationInviteApplication')
            : null;

        return $bound instanceof Application ? $bound : null;
    }
}

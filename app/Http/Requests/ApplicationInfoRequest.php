<?php

namespace App\Http\Requests;

use App\Enums\ApplicationType;
use App\Enums\ProgramCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Langkah 1 — Maklumat permohonan + penerima (URS v1.2 TBL-10). */
class ApplicationInfoRequest extends FormRequest
{
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
            'application_type' => ['required', Rule::enum(ApplicationType::class)],
            'project_title' => ['required', 'string', 'max:255'],
            'project_summary' => ['nullable', 'string', 'max:5000'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_ros_number' => ['required', 'string', 'max:64'],
            'recipient_bank_account' => ['required', 'string', 'max:64'],
            'recipient_address' => ['required', 'string', 'max:500'],
            'program_category' => ['required', Rule::enum(ProgramCategory::class)],
            'location' => ['required', 'string', 'max:255'],
            'proposed_start_date' => ['required', 'date'],
            'proposed_end_date' => ['nullable', 'date', 'after_or_equal:proposed_start_date'],
            'compliance_declaration' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'application_type' => 'jenis permohonan',
            'project_title' => 'tujuan / nama program',
            'project_summary' => 'ringkasan',
            'recipient_name' => 'nama penerima sumbangan',
            'recipient_ros_number' => 'nombor pendaftaran ROS',
            'recipient_bank_account' => 'no. akaun penerima',
            'recipient_address' => 'alamat persatuan',
            'program_category' => 'jenis program',
            'location' => 'lokasi program',
            'proposed_start_date' => 'tarikh program mula',
            'proposed_end_date' => 'tarikh program tamat',
            'compliance_declaration' => 'deklarasi pematuhan',
        ];
    }

    public function messages(): array
    {
        return [
            'proposed_end_date.after_or_equal' => 'Tarikh tamat mesti selepas atau sama dengan tarikh mula.',
            'compliance_declaration.accepted' => 'Anda mesti mengesahkan deklarasi pematuhan BR-012.',
        ];
    }
}

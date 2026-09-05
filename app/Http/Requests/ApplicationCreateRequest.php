<?php

namespace App\Http\Requests;

use App\Enums\ApplicationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Cipta draf awal — medan penuh dilengkapkan di wizard maklumat (TBL-10). */
class ApplicationCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_type' => ['required', Rule::enum(ApplicationType::class)],
            'project_title' => ['required', 'string', 'max:255'],
            'project_summary' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'proposed_start_date' => ['nullable', 'date'],
            'proposed_end_date' => ['nullable', 'date', 'after_or_equal:proposed_start_date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'application_type' => 'jenis permohonan',
            'project_title' => 'nama projek',
            'project_summary' => 'ringkasan',
            'location' => 'lokasi',
            'proposed_start_date' => 'tarikh mula',
            'proposed_end_date' => 'tarikh tamat',
        ];
    }
}

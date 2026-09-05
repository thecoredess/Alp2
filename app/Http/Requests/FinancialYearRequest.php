<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Kebenaran dikawal di peringkat controller/route.
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('financialYear')?->id;

        return [
            'year' => [
                'required', 'integer', 'min:2000', 'max:2100',
                Rule::unique('financial_years', 'year')->ignore($id),
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'year' => 'tahun',
            'label' => 'label',
            'remarks' => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'year.unique' => 'Tahun kewangan ini telah wujud.',
            'year.required' => 'Sila masukkan tahun kewangan.',
        ];
    }
}

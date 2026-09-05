<?php

namespace App\Http\Requests;

use App\Enums\BudgetRequestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetRequestFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Kebenaran dikawal di controller (permission + Policy).
    }

    public function rules(): array
    {
        return [
            'request_type' => ['required', Rule::enum(BudgetRequestType::class)],
            'alp_id' => ['required', Rule::exists('alps', 'id')],
            'financial_year_id' => ['required', Rule::exists('financial_years', 'id')],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99', 'decimal:0,2'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            // Sebab wajib untuk pelarasan (tambah/kurang); pilihan untuk peruntukan awal.
            'reason' => [
                Rule::requiredIf(fn () => $this->input('request_type') !== BudgetRequestType::INITIAL_ALLOCATION->value),
                'nullable', 'string', 'max:2000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'request_type' => 'jenis cadangan',
            'alp_id' => 'ALP',
            'financial_year_id' => 'tahun kewangan',
            'amount' => 'jumlah',
            'reference_number' => 'nombor rujukan',
            'reason' => 'sebab',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt' => 'Jumlah mesti lebih daripada sifar.',
            'reason.required' => 'Sebab wajib diisi untuk pelarasan.',
        ];
    }
}

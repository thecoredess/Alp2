<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprovalLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Kebenaran dikawal di controller.
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'financial_year_id' => ['nullable', Rule::exists('financial_years', 'id')],
            'min_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2', 'gte:min_amount'],
            'required_role' => ['required', Rule::in(RoleName::values())],
            'sequence' => ['required', 'integer', 'min:1', 'max:100'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama aras',
            'min_amount' => 'jumlah minimum',
            'max_amount' => 'jumlah maksimum',
            'required_role' => 'peranan diperlukan',
            'sequence' => 'urutan',
        ];
    }

    public function messages(): array
    {
        return ['max_amount.gte' => 'Jumlah maksimum mesti >= jumlah minimum.'];
    }
}

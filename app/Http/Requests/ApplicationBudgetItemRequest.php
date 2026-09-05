<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Item bajet — kuantiti integer (Fasa 3), harga seunit DECIMAL(15,2). */
class ApplicationBudgetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'description' => 'keterangan',
            'quantity' => 'kuantiti',
            'unit' => 'unit',
            'unit_cost' => 'harga seunit',
            'remarks' => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'Kuantiti mesti sekurang-kurangnya 1.',
            'unit_cost.min' => 'Harga seunit tidak boleh negatif.',
            'unit_cost.decimal' => 'Harga seunit mesti maksimum 2 titik perpuluhan.',
        ];
    }
}

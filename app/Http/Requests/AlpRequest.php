<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Kebenaran dikawal di peringkat controller.
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('alp')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'ref_code' => [
                'required', 'string', 'max:50',
                Rule::unique('alps', 'ref_code')->ignore($id),
            ],
            'portfolio_zone' => ['nullable', 'string', 'max:255'],
            'appointment_start' => ['nullable', 'date'],
            'appointment_end' => ['nullable', 'date', 'after_or_equal:appointment_start'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'ref_code' => 'kod rujukan',
            'portfolio_zone' => 'portfolio / zon',
            'appointment_start' => 'tarikh mula lantikan',
            'appointment_end' => 'tarikh tamat lantikan',
            'phone' => 'telefon',
            'email' => 'e-mel',
            'address' => 'alamat',
            'remarks' => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'ref_code.unique' => 'Kod rujukan ini telah digunakan.',
            'appointment_end.after_or_equal' => 'Tarikh tamat lantikan mesti selepas tarikh mula.',
        ];
    }
}

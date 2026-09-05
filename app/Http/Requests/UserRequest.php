<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $id = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'unit' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(RoleName::values())],
            // ALP wajib jika peranan berkaitan ALP.
            'alp_id' => [
                Rule::requiredIf(fn () => in_array($this->input('role'), [
                    RoleName::ALP->value,
                    RoleName::URUSSETIA_ALP->value,
                ], true)),
                'nullable',
                Rule::exists('alps', 'id'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'e-mel',
            'unit' => 'unit / jabatan',
            'role' => 'peranan',
            'alp_id' => 'ALP',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'E-mel ini telah digunakan.',
            'alp_id.required' => 'Sila pilih ALP untuk peranan ini.',
            'role.in' => 'Peranan tidak sah.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Langkah 2 — Objektif & Skop. */
class ApplicationScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objectives' => ['nullable', 'string', 'max:5000'],
            'scope' => ['nullable', 'string', 'max:5000'],
            'target_group' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'objectives' => 'objektif',
            'scope' => 'skop',
            'target_group' => 'kumpulan sasaran',
        ];
    }
}

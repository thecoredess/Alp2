<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Cipta draf permohonan kosong — borang diisi ALP atau persatuan (pautan) kemudian. */
class ApplicationShellDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Application::class) ?? false;
    }

    public function rules(): array
    {
        $onBehalf = $this->user()?->canCreateApplicationOnBehalf() ?? false;

        return [
            'alp_id' => [$onBehalf ? 'required' : 'nullable', 'integer', Rule::exists('alps', 'id')],
        ];
    }

    public function attributes(): array
    {
        return [
            'alp_id' => 'ALP',
        ];
    }
}

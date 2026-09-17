<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedTypes = array_map(
            fn (DocumentType $type) => $type->value,
            DocumentType::contributionAttachments(),
        );

        return [
            'document_type' => ['required', Rule::in($allowedTypes)],
            'file' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                // Larang skrip/boleh-laksana secara eksplisit.
                'extensions:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'document_type' => 'jenis dokumen',
            'file' => 'fail',
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Jenis fail tidak dibenarkan. Hanya PDF, imej, Word dan Excel diterima.',
            'file.max' => 'Saiz fail tidak boleh melebihi 10 MB.',
        ];
    }
}

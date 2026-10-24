<?php

namespace App\Http\Requests\Concerns;

use App\Enums\DocumentType;
use Illuminate\Validation\Rule;

trait ValidatesContributionDocumentUpload
{
    /** @return array<string, mixed> */
    public function contributionDocumentRules(): array
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
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'extensions:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            ],
        ];
    }

    public function rules(): array
    {
        return $this->contributionDocumentRules();
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

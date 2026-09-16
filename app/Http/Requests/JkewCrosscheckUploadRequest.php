<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JkewCrosscheckUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx',
                'extensions:pdf,doc,docx',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'borang ulasan JKEW',
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Hanya fail PDF atau Word (.doc/.docx) diterima.',
        ];
    }
}

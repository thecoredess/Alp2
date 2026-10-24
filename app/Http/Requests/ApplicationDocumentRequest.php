<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationDocumentRequest extends FormRequest
{
    use Concerns\ValidatesContributionDocumentUpload;

    public function authorize(): bool
    {
        return true;
    }
}

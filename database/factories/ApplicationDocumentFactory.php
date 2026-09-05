<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationDocument>
 */
class ApplicationDocumentFactory extends Factory
{
    protected $model = ApplicationDocument::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'document_type' => DocumentType::KERTAS_KERJA,
            'original_filename' => 'dokumen.pdf',
            'stored_path' => 'applications/test/dokumen.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'sha256' => str_repeat('a', 64),
        ];
    }

    public function type(DocumentType $type): static
    {
        return $this->state(fn () => ['document_type' => $type]);
    }
}

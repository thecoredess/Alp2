<?php

namespace App\Services\Documents;

use App\Enums\DocumentType;
use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Simpan / buang lampiran permohonan pada cakera peribadi. */
final class ApplicationDocumentStorage
{
    private const DISK = 'local';

    public function store(Application $application, UploadedFile $file, DocumentType $type, ?int $uploadedBy): ApplicationDocument
    {
        $storedPath = $file->store("applications/{$application->id}", self::DISK);
        $sha256 = hash_file('sha256', $file->getRealPath());

        return $application->documents()->create([
            'document_type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'sha256' => $sha256,
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function delete(ApplicationDocument $document): void
    {
        if (Storage::disk(self::DISK)->exists($document->stored_path)) {
            Storage::disk(self::DISK)->delete($document->stored_path);
        }

        $document->delete();
    }
}

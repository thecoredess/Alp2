<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\ApplicationDocumentRequest;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationDocumentController extends Controller
{
    /** Cakera storan peribadi (storage/app/private). */
    private const DISK = 'local';

    public function __construct(private readonly AuditService $audit) {}

    public function store(ApplicationDocumentRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $file = $request->file('file');
        $type = DocumentType::from($request->validated('document_type'));

        // Simpan pada storan peribadi; nama fail dijana (bukan nama asal) untuk keselamatan.
        $storedPath = $file->store("applications/{$application->id}", self::DISK);
        $sha256 = hash_file('sha256', $file->getRealPath());

        $application->documents()->create([
            'document_type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'sha256' => $sha256,
            'uploaded_by' => $request->user()->id,
        ]);

        // Jangan log kandungan dokumen — hanya metadata.
        $this->audit->log('APPLICATION_DOCUMENT_UPLOADED', $application, null, [
            'document_type' => $type->value,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Dokumen dimuat naik.');
    }

    public function download(Application $application, ApplicationDocument $document): StreamedResponse
    {
        $this->authorize('downloadDocument', $application);
        abort_unless($document->application_id === $application->id, 404);
        abort_unless(Storage::disk(self::DISK)->exists($document->stored_path), 404);

        return Storage::disk(self::DISK)->download($document->stored_path, $document->original_filename);
    }

    public function view(Application $application, ApplicationDocument $document): StreamedResponse
    {
        $this->authorize('downloadDocument', $application);
        abort_unless($document->application_id === $application->id, 404);
        abort_unless(Storage::disk(self::DISK)->exists($document->stored_path), 404);

        return Storage::disk(self::DISK)->response(
            $document->stored_path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type ?? 'application/octet-stream'],
        );
    }

    public function destroy(Application $application, ApplicationDocument $document): RedirectResponse
    {
        // Hanya semasa DRAFT & pemilik (authorize 'update').
        $this->authorize('update', $application);
        abort_unless($document->application_id === $application->id, 404);

        Storage::disk(self::DISK)->delete($document->stored_path);
        $type = $document->document_type->value;
        $filename = $document->original_filename;
        $document->delete();

        $this->audit->log('APPLICATION_DOCUMENT_REMOVED', $application, null, [
            'document_type' => $type,
            'original_filename' => $filename,
        ]);

        return back()->with('status', 'Dokumen dibuang.');
    }
}

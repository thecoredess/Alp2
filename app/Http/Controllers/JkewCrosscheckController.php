<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\JkewCrosscheckUploadRequest;
use App\Models\Application;
use App\Services\Audit\AuditService;
use App\Services\Documents\JkewCrosscheckMemoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class JkewCrosscheckController extends Controller
{
    private const DISK = 'local';

    public function __construct(
        private readonly JkewCrosscheckMemoService $memos,
        private readonly AuditService $audit,
    ) {}

    public function downloadDoc(Application $application): Response
    {
        $this->authorize('downloadCrosscheckMemo', $application);

        $content = $this->memos->filledDocx($application);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$this->memos->downloadFilename($application, 'docx').'"',
        ]);
    }

    public function store(JkewCrosscheckUploadRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('uploadCrosscheckMemo', $application);

        $file = $request->file('file');
        $storedPath = $file->store("applications/{$application->id}", self::DISK);
        $sha256 = hash_file('sha256', $file->getRealPath());

        $existing = $application->documents()
            ->where('document_type', DocumentType::SEMAKAN_SILANG_JKEW)
            ->get();

        foreach ($existing as $doc) {
            Storage::disk(self::DISK)->delete($doc->stored_path);
            $doc->delete();
        }

        $application->documents()->create([
            'document_type' => DocumentType::SEMAKAN_SILANG_JKEW,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'sha256' => $sha256,
            'uploaded_by' => $request->user()->id,
        ]);

        $this->audit->log('APPLICATION_CROSSCHECK_UPLOADED', $application, null, [
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Borang ulasan JKEW dimuat naik.');
    }
}

<?php

namespace App\Services\Project;

use App\Enums\ProjectDocumentType;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Pengurusan dokumen bukti projek (perbelanjaan, penutupan, refund).
 * Storan peribadi; nama fail dijana; SHA-256 direkod; immutable selepas
 * tindakan kewangan dimuktamadkan.
 */
class ProjectDocumentService
{
    private const DISK = 'local';

    public function __construct(private readonly AuditService $audit) {}

    public function uploadExpenseDocument(ProjectExpense $expense, User $user, ProjectDocumentType $type, UploadedFile $file): ProjectDocument
    {
        if (! $expense->isEditableByMaker()) {
            throw new ProjectException('Bukti perbelanjaan hanya boleh ditambah semasa Draf/Perlu Pembetulan.');
        }

        $doc = $this->store($expense->project_id, $type, 'expense_evidence', $file, $user, [
            'project_expense_id' => $expense->id,
        ]);
        $this->audit->log('EXPENSE_DOCUMENT_UPLOADED', $expense, null, ['document_type' => $type->value, 'filename' => $file->getClientOriginalName()]);

        return $doc;
    }

    public function uploadClosureDocument(Project $project, User $user, ProjectDocumentType $type, UploadedFile $file): ProjectDocument
    {
        if ($project->isClosed()) {
            throw new ProjectException('Projek telah ditutup — bukti tidak boleh ditambah.');
        }

        $doc = $this->store($project->id, $type, 'closure_evidence', $file, $user);
        $this->audit->log('PROJECT_REPORT_DOCUMENT_UPLOADED', $project, null, ['document_type' => $type->value, 'filename' => $file->getClientOriginalName()]);

        return $doc;
    }

    public function uploadRefundDocument(ProjectExpenseRefund $refund, User $user, ProjectDocumentType $type, UploadedFile $file): ProjectDocument
    {
        if (! $refund->isEditableByMaker()) {
            throw new ProjectException('Bukti refund hanya boleh ditambah semasa Draf/Perlu Pembetulan.');
        }

        $doc = $this->store($refund->project_id, $type, 'refund_evidence', $file, $user, [
            'project_expense_refund_id' => $refund->id,
        ]);
        $this->audit->log('REFUND_DOCUMENT_UPLOADED', $refund, null, ['document_type' => $type->value, 'filename' => $file->getClientOriginalName()]);

        return $doc;
    }

    public function remove(ProjectDocument $document, User $user): void
    {
        $this->assertRemovable($document);

        Storage::disk(self::DISK)->delete($document->stored_path);
        $filename = $document->original_filename;
        $category = $document->category;
        $document->delete();

        $action = $category === 'closure_evidence' ? 'PROJECT_REPORT_DOCUMENT_REMOVED'
            : ($category === 'refund_evidence' ? 'REFUND_DOCUMENT_REMOVED' : 'EXPENSE_DOCUMENT_REMOVED');
        $this->audit->log($action, $document->project, null, ['filename' => $filename]);
    }

    /** Adakah dokumen ini masih boleh dibuang (bukan selepas dimuktamadkan)? */
    public function assertRemovable(ProjectDocument $document): void
    {
        $document->loadMissing(['project']);
        $editable = match ($document->category) {
            'expense_evidence' => ProjectExpense::find($document->project_expense_id)?->isEditableByMaker() ?? false,
            'refund_evidence' => ProjectExpenseRefund::find($document->project_expense_refund_id)?->isEditableByMaker() ?? false,
            'closure_evidence' => ! $document->project->isClosed(),
            default => false,
        };

        if (! $editable) {
            throw new ProjectException('Dokumen ini telah dimuktamadkan dan tidak boleh dibuang.');
        }
    }

    private function store(int $projectId, ProjectDocumentType $type, string $category, UploadedFile $file, User $user, array $extra = []): ProjectDocument
    {
        $storedPath = $file->store("projects/{$projectId}/{$category}", self::DISK);
        $sha256 = hash_file('sha256', $file->getRealPath());

        return ProjectDocument::create([
            'project_id' => $projectId,
            'category' => $category,
            'document_type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'sha256' => $sha256,
            'uploaded_by' => $user->id,
            ...$extra,
        ]);
    }
}

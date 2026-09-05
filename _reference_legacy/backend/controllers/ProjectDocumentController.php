<?php

namespace App\Http\Controllers;

use App\Enums\ProjectDocumentType;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use App\Services\Project\ProjectDocumentService;
use App\Services\Project\ProjectException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentController extends Controller
{
    private const DISK = 'local';

    public function __construct(private readonly ProjectDocumentService $documents) {}

    public function storeExpense(Request $request, ProjectExpense $expense): RedirectResponse
    {
        $this->authorize('manageDocuments', $expense);
        $type = $this->validateDocument($request, 'expense_evidence');

        try {
            $this->documents->uploadExpenseDocument($expense, $request->user(), $type, $request->file('file'));
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Dokumen bukti perbelanjaan dimuat naik.');
    }

    public function storeClosure(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('manageClosureDocuments', $project);
        $type = $this->validateDocument($request, 'closure_evidence');

        try {
            $this->documents->uploadClosureDocument($project, $request->user(), $type, $request->file('file'));
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Dokumen penutupan dimuat naik.');
    }

    public function storeRefund(Request $request, ProjectExpenseRefund $refund): RedirectResponse
    {
        $this->authorize('manageDocuments', $refund);
        $type = $this->validateDocument($request, 'refund_evidence');

        try {
            $this->documents->uploadRefundDocument($refund, $request->user(), $type, $request->file('file'));
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Dokumen bukti refund dimuat naik.');
    }

    public function download(Request $request, ProjectDocument $document): StreamedResponse
    {
        $this->authorizeView($request, $document);
        abort_unless(Storage::disk(self::DISK)->exists($document->stored_path), 404);

        return Storage::disk(self::DISK)->download($document->stored_path, $document->original_filename);
    }

    public function destroy(Request $request, ProjectDocument $document): RedirectResponse
    {
        $this->authorizeManage($request, $document);

        try {
            $this->documents->remove($document, $request->user());
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Dokumen dibuang.');
    }

    private function authorizeView(Request $request, ProjectDocument $document): void
    {
        match ($document->category) {
            'expense_evidence' => $this->authorize('view', ProjectExpense::findOrFail($document->project_expense_id)),
            'refund_evidence' => $this->authorize('view', ProjectExpenseRefund::findOrFail($document->project_expense_refund_id)),
            'closure_evidence' => $this->authorize('viewClosureDocuments', $document->project),
            default => abort(404),
        };
    }

    private function authorizeManage(Request $request, ProjectDocument $document): void
    {
        match ($document->category) {
            'expense_evidence' => $this->authorize('manageDocuments', ProjectExpense::findOrFail($document->project_expense_id)),
            'refund_evidence' => $this->authorize('manageDocuments', ProjectExpenseRefund::findOrFail($document->project_expense_refund_id)),
            'closure_evidence' => $this->authorize('manageClosureDocuments', $document->project),
            default => abort(404),
        };
    }

    private function validateDocument(Request $request, string $category): ProjectDocumentType
    {
        $allowed = array_keys(ProjectDocumentType::forCategory($category));
        $validated = $request->validate([
            'document_type' => ['required', Rule::in($allowed)],
            'file' => [
                'required', 'file', 'max:10240',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'extensions:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            ],
        ], [
            'file.mimes' => 'Jenis fail tidak dibenarkan. Hanya PDF, imej, Word dan Excel diterima.',
            'file.max' => 'Saiz fail tidak boleh melebihi 10 MB.',
            'document_type.in' => 'Jenis dokumen tidak sah untuk kategori ini.',
        ], [
            'document_type' => 'jenis dokumen',
            'file' => 'fail',
        ]);

        return ProjectDocumentType::from($validated['document_type']);
    }
}

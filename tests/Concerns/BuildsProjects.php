<?php

namespace Tests\Concerns;

use App\Models\Alp;
use App\Models\FinancialYear;
use App\Models\Project;
use App\Models\User;
use App\Services\Application\ApprovalMatrixService;
use App\Services\Application\ApprovalService;
use App\Support\Money;

trait BuildsProjects
{
    use BuildsWorkflow;

    /**
     * Hasilkan projek DILULUSKAN (melalui aliran sebenar: submit → semakan →
     * kelulusan berbilang-aras → projek + komitmen dicipta).
     */
    protected function approvedProject(Alp $alp, FinancialYear $year, string $amount): Project
    {
        $app = $this->toPendingApproval($this->submitted($alp, $year, $amount));

        $required = app(ApprovalMatrixService::class)->requiredLevels(Money::of($amount), $year->id);
        foreach ($required as $level) {
            $approver = User::factory()->create()->assignRole($level->required_role);
            $approver->givePermissionTo('applications.approve');
            app(ApprovalService::class)->approve($app->fresh(), $approver, null);
        }

        return $app->fresh()->project;
    }

    /** Finance maker (kewangan) untuk perbelanjaan. */
    protected function financeMaker(): User
    {
        return $this->userWithRole(\App\Enums\RoleName::PEGAWAI_KEWANGAN->value);
    }

    /** Finance checker (pelulus) untuk pengesahan perbelanjaan. */
    protected function financeChecker(): User
    {
        return $this->userWithRole(\App\Enums\RoleName::PELULUS->value);
    }

    /** Project operator (urus setia DBKL). */
    protected function projectOperator(): User
    {
        return $this->userWithRole(\App\Enums\RoleName::PEGAWAI_URUSSETIA->value);
    }

    /** Projek DILULUSKAN yang telah dimulakan (IN_PROGRESS) dengan komitmen. */
    protected function startedProject(string $amount = '85000.00'): Project
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '1000000.00');
        $project = $this->approvedProject($alp, $year, $amount);
        app(\App\Services\Project\ProjectService::class)->start($project, $this->projectOperator());

        return $project->fresh();
    }

    /** Cipta + hantar + sahkan satu perbelanjaan (dengan bukti). */
    protected function verifyExpense(Project $project, string $amount): \App\Models\ProjectExpense
    {
        $maker = $this->financeMaker();
        $expense = app(\App\Services\Project\ProjectExpenseService::class)->createDraft($project, $maker, [
            'expense_date' => now()->toDateString(),
            'reference_number' => 'INV/'.fake()->unique()->numerify('#####'),
            'description' => 'Bayaran', 'amount' => $amount,
        ]);
        $this->attachEvidence($expense);
        app(\App\Services\Project\ProjectExpenseService::class)->submit($expense, $maker);
        app(\App\Services\Project\ProjectExpenseVerificationService::class)->verify($expense->fresh(), $this->financeChecker());

        return $expense->fresh();
    }

    /** Lampirkan satu dokumen bukti (tanpa storan sebenar) pada perbelanjaan/refund. */
    protected function attachEvidence(\Illuminate\Database\Eloquent\Model $owner): \App\Models\ProjectDocument
    {
        $isRefund = $owner instanceof \App\Models\ProjectExpenseRefund;

        return \App\Models\ProjectDocument::create([
            'project_id' => $owner->project_id,
            'project_expense_id' => $isRefund ? null : $owner->id,
            'project_expense_refund_id' => $isRefund ? $owner->id : null,
            'category' => $isRefund ? 'refund_evidence' : 'expense_evidence',
            'document_type' => $isRefund ? \App\Enums\ProjectDocumentType::BANK_SLIP : \App\Enums\ProjectDocumentType::INVOICE,
            'original_filename' => 'bukti.pdf',
            'stored_path' => 'test/'.fake()->unique()->numerify('########').'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'sha256' => hash('sha256', (string) fake()->unique()->numerify('##########')),
            'uploaded_by' => $owner->created_by,
        ]);
    }
}

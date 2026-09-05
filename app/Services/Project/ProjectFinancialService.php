<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Services\Budget\BudgetService;
use App\Support\Money;

/**
 * Ringkasan kewangan projek yang DIKIRA DARI LEDGER (sumber kebenaran rasmi).
 * Rekod project_expenses hanyalah bukti operasi yang dikaitkan ledger.
 *
 * Model (baldi committed & spent projek dari budget_transactions):
 *   Approved Commitment  = project.approved_amount (snapshot)
 *   Outstanding          = baldi committed  (= approved − spent − released)
 *   Verified Spent       = baldi spent
 *   Released             = approved − spent − outstanding
 *   Unused (releasable)  = outstanding
 *
 * Rekonsiliasi: Approved = Outstanding + Verified Spent + Released.
 */
class ProjectFinancialService
{
    public function __construct(private readonly BudgetService $budget) {}

    /**
     * @return array{approved: Money, gross: Money, refunded: Money, spent: Money, outstanding: Money, released: Money, unused: Money}
     *
     * spent = Net Spent (= Gross Expenditure − Verified Refund).
     * Rekonsiliasi: Approved = Outstanding + Net Spent + Released.
     */
    public function summary(Project $project): array
    {
        $s = $this->budget->summaryForProject($project->id);
        $approved = $project->approvedAmountMoney();
        $netSpent = $s->spent;               // baldi spent = gross − refund
        $outstanding = $s->committed;
        $released = $approved->minus($netSpent)->minus($outstanding);

        $gross = $this->typeTotal($project->id, 'expenditure');
        $refunded = $this->typeTotal($project->id, 'refund');

        return [
            'approved' => $approved,
            'gross' => $gross,
            'refunded' => $refunded,
            'spent' => $netSpent,
            'outstanding' => $outstanding,
            'released' => $released,
            'unused' => $outstanding,
        ];
    }

    private function typeTotal(int $projectId, string $type): Money
    {
        $sum = \App\Models\BudgetTransaction::where('project_id', $projectId)
            ->where('type', $type)->sum('amount');

        return Money::of($sum === null ? '0' : (string) $sum);
    }

    public function outstandingCommitment(Project $project): Money
    {
        return $this->budget->summaryForProject($project->id)->committed;
    }

    public function verifiedSpent(Project $project): Money
    {
        return $this->budget->summaryForProject($project->id)->spent;
    }

    /**
     * Sahkan rekonsiliasi: Approved = Outstanding + Spent + Released, dan
     * outstanding tidak negatif (perbelanjaan tidak melebihi komitmen).
     */
    public function assertConsistent(Project $project): void
    {
        $s = $this->summary($project);
        if ($s['outstanding']->isNegative()) {
            throw new ProjectException('Ketakkonsistenan kewangan: baki komitmen negatif.');
        }
        $recomputed = $s['outstanding']->plus($s['spent'])->plus($s['released']);
        if (! $recomputed->equals($s['approved'])) {
            throw new ProjectException('Ketakkonsistenan kewangan projek dikesan.');
        }
    }
}

<?php

namespace App\Services\Reports;

use App\Models\BudgetRequest;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Laporan governance kewangan Maker-Checker merentas Peruntukan, Pelarasan,
 * Perbelanjaan & Refund. Untuk kawalan dalaman / audit.
 */
class MakerCheckerReportService
{
    /** @return Collection<int, array> baris disatukan, disusun terkini dahulu */
    public function listing(array $filters): Collection
    {
        $rows = collect()
            ->merge($this->budgetRequests($filters))
            ->merge($this->expenses($filters))
            ->merge($this->refunds($filters));

        // Peta nama pengguna dalam satu pertanyaan (elak N+1).
        $userIds = $rows->flatMap(fn ($r) => [$r['maker_id'], $r['checker_id']])->filter()->unique();
        $names = User::whereIn('id', $userIds)->pluck('name', 'id');

        return $rows->map(function ($r) use ($names) {
            $r['maker'] = $r['maker_id'] ? ($names[$r['maker_id']] ?? '—') : '—';
            $r['checker'] = $r['checker_id'] ? ($names[$r['checker_id']] ?? '—') : '—';

            return $r;
        })->sortByDesc(fn ($r) => optional($r['submitted_at'])->timestamp ?? 0)->values();
    }

    private function budgetRequests(array $filters): Collection
    {
        return BudgetRequest::query()
            ->when($filters['financial_year_id'] ?? null, fn ($q, $v) => $q->where('financial_year_id', $v))
            ->when($filters['alp_id'] ?? null, fn ($q, $v) => $q->where('alp_id', $v))
            ->get()
            ->map(fn (BudgetRequest $r) => [
                'category' => 'Bajet',
                'reference' => $r->reference_number,
                'type' => $r->request_type->label(),
                'maker_id' => $r->created_by ?? $r->submitted_by,
                'checker_id' => $r->approved_by,
                'amount' => Money::of((string) $r->amount),
                'status' => $r->status->label(),
                'submitted_at' => $r->submitted_at,
                'approved_at' => $r->approved_at,
            ]);
    }

    private function expenses(array $filters): Collection
    {
        return ProjectExpense::query()
            ->with('project:id,alp_id,financial_year_id,project_number')
            ->whereHas('project', function ($q) use ($filters) {
                $q->when($filters['financial_year_id'] ?? null, fn ($qq, $v) => $qq->where('financial_year_id', $v))
                    ->when($filters['alp_id'] ?? null, fn ($qq, $v) => $qq->where('alp_id', $v));
            })
            ->get()
            ->map(fn (ProjectExpense $e) => [
                'category' => 'Perbelanjaan',
                'reference' => $e->reference_number,
                'type' => 'Perbelanjaan Projek',
                'maker_id' => $e->created_by ?? $e->submitted_by,
                'checker_id' => $e->verified_by,
                'amount' => Money::of((string) $e->amount),
                'status' => $e->status->label(),
                'submitted_at' => $e->submitted_at,
                'approved_at' => $e->verified_at,
            ]);
    }

    private function refunds(array $filters): Collection
    {
        return ProjectExpenseRefund::query()
            ->whereHas('project', function ($q) use ($filters) {
                $q->when($filters['financial_year_id'] ?? null, fn ($qq, $v) => $qq->where('financial_year_id', $v))
                    ->when($filters['alp_id'] ?? null, fn ($qq, $v) => $qq->where('alp_id', $v));
            })
            ->get()
            ->map(fn (ProjectExpenseRefund $r) => [
                'category' => 'Refund',
                'reference' => $r->reference_number ?? ('RF#'.$r->id),
                'type' => 'Refund Perbelanjaan',
                'maker_id' => $r->created_by ?? $r->submitted_by,
                'checker_id' => $r->verified_by,
                'amount' => Money::of((string) $r->amount),
                'status' => $r->status->label(),
                'submitted_at' => $r->submitted_at,
                'approved_at' => $r->verified_at,
            ]);
    }
}

<?php

namespace App\Models;

use App\Enums\ApplicationType;
use App\Enums\ProjectStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'project_number', 'application_id', 'alp_id', 'financial_year_id',
        'project_name', 'project_type', 'approved_amount',
        'start_date', 'end_date', 'actual_start_date', 'actual_completion_date',
        'status', 'progress_percent', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'project_type' => ApplicationType::class,
            'status' => ProjectStatus::class,
            'approved_amount' => 'decimal:2',
            'progress_percent' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_completion_date' => 'date',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function alp(): BelongsTo
    {
        return $this->belongsTo(Alp::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('sort_order')->orderBy('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class)->orderByDesc('id');
    }

    public function progressHistories(): HasMany
    {
        return $this->hasMany(ProjectProgressHistory::class)->orderBy('id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProjectStatusHistory::class)->orderBy('id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ProjectReport::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function budgetTransactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }

    public function approvedAmountMoney(): Money
    {
        return Money::of((string) $this->approved_amount);
    }

    public function isClosed(): bool
    {
        return $this->status === ProjectStatus::CLOSED;
    }
}

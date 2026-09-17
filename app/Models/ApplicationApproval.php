<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use App\Services\Application\ApprovalMatrixService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationApproval extends Model
{
    public $timestamps = false; // created_at sahaja (append-only)

    protected $fillable = [
        'application_id',
        'approval_level_id',
        'approver_id',
        'decision',
        'comments',
        'sequence',
        'revision_number',
        'decided_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'decision' => ApprovalDecision::class,
            'decided_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function approvalLevel(): BelongsTo
    {
        return $this->belongsTo(ApprovalLevel::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /** Aras kelulusan terakhir untuk permohonan ini (cth. Kelulusan PEPU). */
    public function isFinalLevelForApplication(?Application $application = null): bool
    {
        $application ??= $this->application;

        if (! $application || ! $this->approval_level_id) {
            return false;
        }

        $required = app(ApprovalMatrixService::class)
            ->requiredLevels($application->requestedAmountMoney(), $application->financial_year_id);

        return $required->last()?->id === $this->approval_level_id;
    }

    /** Label paparan — aras pengesyoran bukan "Diluluskan". */
    public function displayDecisionLabel(?Application $application = null): string
    {
        if ($this->decision !== ApprovalDecision::APPROVED) {
            return $this->decision->label();
        }

        return $this->isFinalLevelForApplication($application)
            ? 'Diluluskan'
            : 'Disyorkan';
    }

    public function displayDecisionBadgeClasses(?Application $application = null): string
    {
        if ($this->decision !== ApprovalDecision::APPROVED) {
            return $this->decision->badgeClasses();
        }

        return $this->isFinalLevelForApplication($application)
            ? 'bg-green-100 text-green-800'
            : 'bg-indigo-100 text-indigo-800';
    }
}

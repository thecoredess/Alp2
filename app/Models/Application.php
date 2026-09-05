<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ApplicationPaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'application_number',
        'financial_year_id',
        'alp_id',
        'recipient_id',
        'application_type',
        'project_title',
        'project_summary',
        'recipient_name',
        'recipient_ros_number',
        'recipient_bank_account',
        'recipient_address',
        'program_category',
        'objectives',
        'scope',
        'target_group',
        'location',
        'proposed_start_date',
        'proposed_end_date',
        'is_short_notice',
        'compliance_declared_at',
        'requested_amount',
        'status',
        'revision_number',
        'submitted_at',
        'payment_status',
        'payment_voucher_no',
        'payment_reference',
        'paid_at',
        'payment_remarks',
        'payment_updated_by',
        'payment_updated_at',
        'sent_to_jkew_at',
        'jkew_crosscheck_status',
        'jkew_crosscheck_remarks',
        'report_card_submitted_at',
        'report_card_reminder_sent_at',
        'report_card_remarks',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'application_type' => ApplicationType::class,
            'program_category' => \App\Enums\ProgramCategory::class,
            'status' => ApplicationStatus::class,
            'payment_status' => ApplicationPaymentStatus::class,
            'jkew_crosscheck_status' => \App\Enums\JkewCrosscheckStatus::class,
            'proposed_start_date' => 'date',
            'proposed_end_date' => 'date',
            'requested_amount' => 'decimal:2',
            'revision_number' => 'integer',
            'is_short_notice' => 'boolean',
            'submitted_at' => 'datetime',
            'compliance_declared_at' => 'datetime',
            'paid_at' => 'datetime',
            'payment_updated_at' => 'datetime',
            'sent_to_jkew_at' => 'datetime',
            'report_card_submitted_at' => 'datetime',
            'report_card_reminder_sent_at' => 'datetime',
        ];
    }

    // ── Perhubungan ─────────────────────────────────────────────

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function alp(): BelongsTo
    {
        return $this->belongsTo(Alp::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(ApplicationBudgetItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ApplicationReview::class)->orderBy('id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ApplicationApproval::class)->orderBy('id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ApplicationRevision::class)->orderBy('id');
    }

    /** Transaksi komitmen ledger (jika telah diluluskan). */
    public function commitmentTransaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BudgetTransaction::class)
            ->where('type', \App\Enums\BudgetTransactionType::COMMITMENT->value);
    }

    /** Projek yang dijana daripada permohonan diluluskan (satu-ke-satu). */
    public function project(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_updated_by');
    }

    // ── Bantuan ─────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === ApplicationStatus::DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === ApplicationStatus::SUBMITTED;
    }

    public function isEditableByOwner(): bool
    {
        return $this->status->isEditableByOwner();
    }

    /** Jumlah tepat item bajet (dikira dari ledger item, bukan input pengguna). */
    public function budgetItemsTotal(): Money
    {
        $total = Money::zero();
        foreach ($this->budgetItems as $item) {
            $total = $total->plus(Money::of((string) $item->total));
        }

        return $total;
    }

    /** Jumlah dipohon sebagai Money. */
    public function requestedAmountMoney(): Money
    {
        return Money::of((string) $this->requested_amount);
    }

    /**
     * Kira semula requested_amount dari item bajet (tepat) dan simpan.
     * Digunakan semasa DRAFT untuk paparan; snapshot muktamad dibuat semasa hantar.
     */
    public function recalculateRequestedAmount(): Money
    {
        $total = $this->budgetItemsTotal();
        $this->requested_amount = $total->value();
        $this->save();

        return $total;
    }
}

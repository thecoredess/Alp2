<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ApplicationPaymentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ReportCardStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'purpose',
        'recipient_name',
        'recipient_ros_number',
        'program_date',
        'program_category',
        'recipient_bank_account',
        'recipient_address',
        'requested_amount',
        'status',
        'revision_number',
        'submitted_at',
        'payment_status',
        'payment_voucher_no',
        'payment_supplier_no',
        'payment_voucher_date',
        'payment_reference',
        'paid_at',
        'payment_remarks',
        'payment_updated_by',
        'payment_updated_at',
        'sent_to_jkew_at',
        'jkew_crosscheck_status',
        'jkew_crosscheck_remarks',
        'report_card_submitted_at',
        'report_card_status',
        'report_card_reminder_sent_at',
        'report_card_remarks',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'application_type' => ApplicationType::class,
            'status' => ApplicationStatus::class,
            'payment_status' => ApplicationPaymentStatus::class,
            'jkew_crosscheck_status' => \App\Enums\JkewCrosscheckStatus::class,
            'requested_amount' => 'decimal:2',
            'revision_number' => 'integer',
            'submitted_at' => 'datetime',
            'program_date' => 'date',
            'program_category' => ProgramCategory::class,
            'paid_at' => 'datetime',
            'payment_voucher_date' => 'date',
            'payment_updated_at' => 'datetime',
            'sent_to_jkew_at' => 'datetime',
            'report_card_submitted_at' => 'datetime',
            'report_card_status' => ReportCardStatus::class,
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

    public function reportCardReviews(): HasMany
    {
        return $this->hasMany(ReportCardReview::class)->orderBy('id');
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

    /** Permohonan sumbangan rasmi URS (ALP/SUM/…) — bukan data legacy CSR/DEV. */
    public function scopeOfficialSumbangan(Builder $query): Builder
    {
        return $query
            ->where('application_type', ApplicationType::SUMBANGAN)
            ->where('application_number', 'like', 'ALP/SUM/%');
    }

    public function isOfficialSumbangan(): bool
    {
        return $this->application_type === ApplicationType::SUMBANGAN
            && str_starts_with((string) $this->application_number, 'ALP/SUM/');
    }

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

    /** Baucar sudah disedia (atau peringkat seterusnya). Jam laporan aktiviti bermula di sini. */
    public function hasVoucherPrepared(): bool
    {
        if ($this->payment_voucher_date || filled($this->payment_voucher_no)) {
            return true;
        }

        return in_array($this->payment_status, [
            ApplicationPaymentStatus::VOUCHER_PREPARED,
            ApplicationPaymentStatus::SENT_TO_JKEW,
            ApplicationPaymentStatus::PAID,
        ], true);
    }

    /** Jumlah dipohon sebagai Money. */
    public function requestedAmountMoney(): Money
    {
        return Money::of((string) $this->requested_amount);
    }

    /** Alias lama — jumlah kini dimasukkan terus pada borang (bukan pecahan item). */
    public function budgetItemsTotal(): Money
    {
        return $this->requestedAmountMoney();
    }

    public function recalculateRequestedAmount(): Money
    {
        return $this->requestedAmountMoney();
    }

    /** Alias paparan: tujuan sumbangan. */
    protected function projectTitle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['purpose'] ?? null,
            set: fn (?string $value) => ['purpose' => $value],
        );
    }
}

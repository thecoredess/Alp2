<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ApplicationPaymentStatus;
use App\Enums\DocumentType;
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

    /** Awalan data simulasi UAT — disimpan dalam DB, tidak dipapar kepada pengguna. */
    public const SIMULATION_PREFIX = '[SIM] ';

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
        'report_card_upcoming_reminder_sent_at',
        'report_card_overdue_reminder_sent_at',
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
            'report_card_upcoming_reminder_sent_at' => 'datetime',
            'report_card_overdue_reminder_sent_at' => 'datetime',
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

    /** Borang ulasan JKEW (semakan silang) dimuat naik. */
    public function hasJkewCrosscheckDocument(): bool
    {
        $this->loadMissing('documents');

        return $this->documents->contains(
            fn (ApplicationDocument $d) => $d->document_type === DocumentType::SEMAKAN_SILANG_JKEW,
        );
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

    /** Buang awalan simulasi UAT daripada teks paparan. */
    public static function stripSimulationPrefix(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return str_starts_with($value, self::SIMULATION_PREFIX)
            ? substr($value, strlen(self::SIMULATION_PREFIX))
            : $value;
    }

    /** Nama program / tujuan sumbangan untuk laporan (bukan label aliran kerja). */
    public function programLabelForReport(?int $limit = null): string
    {
        $label = $this->resolveProgramLabelForReport();

        return $limit !== null ? \Illuminate\Support\Str::limit($label, $limit) : $label;
    }

    /** Label kategori program untuk senarai permohonan. */
    public function programCategoryLabelForReport(?int $limit = null): string
    {
        $label = $this->program_category?->label() ?? '—';

        return $limit !== null ? \Illuminate\Support\Str::limit($label, $limit) : $label;
    }

    /** Nama persatuan penerima untuk laporan. */
    public function recipientLabelForReport(?int $limit = null): string
    {
        $raw = $this->getAttributes()['recipient_name'] ?? null;
        $label = self::stripSimulationPrefix($raw) ?? '—';

        return $limit !== null ? \Illuminate\Support\Str::limit($label, $limit) : $label;
    }

    public static function isWorkflowPurposePlaceholder(?string $purpose): bool
    {
        if ($purpose === null || $purpose === '') {
            return true;
        }

        static $placeholders = [
            'Permohonan ditolak',
            'Menunggu semakan Pegawai JP',
            'Menunggu Peraku (TP/Pengarah JP)',
            'Menunggu kelulusan PEPU',
            'Diluluskan — menunggu baucar',
            'Diluluskan — baucar disedia',
        ];

        if (in_array($purpose, $placeholders, true)) {
            return true;
        }

        return str_starts_with($purpose, 'Draf —');
    }

    protected function resolveProgramLabelForReport(): string
    {
        $rawPurpose = $this->getAttributes()['purpose'] ?? null;
        $purpose = self::stripSimulationPrefix($rawPurpose);

        if (filled($purpose) && ! self::isWorkflowPurposePlaceholder($purpose)) {
            return $purpose;
        }

        $this->loadMissing(['revisions']);

        $snapshotPurpose = $this->revisions
            ->sortByDesc('id')
            ->map(fn (ApplicationRevision $revision) => is_array($revision->snapshot)
                ? self::stripSimulationPrefix($revision->snapshot['purpose'] ?? null)
                : null)
            ->first(fn (?string $value) => filled($value) && ! self::isWorkflowPurposePlaceholder($value));

        if (filled($snapshotPurpose)) {
            return $snapshotPurpose;
        }

        return $this->program_category?->label() ?? '—';
    }

    protected function purpose(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => self::stripSimulationPrefix($value),
        );
    }

    /** Alias paparan: tujuan sumbangan. */
    protected function projectTitle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->purpose,
            set: fn (?string $value) => ['purpose' => $value],
        );
    }
}

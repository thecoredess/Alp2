<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'unit',
        'avatar_path',
        'avatar_icon',
        'must_change_password',
        'last_login_at',
        'alp_id',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ── Perhubungan ─────────────────────────────────────────────

    /** ALP yang dikaitkan (untuk role ALP / Urus Setia ALP). */
    public function alp(): BelongsTo
    {
        return $this->belongsTo(Alp::class);
    }

    /** Pentadbir yang mencipta akaun ini. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Bantuan ─────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /** URL gambar profil (disk public), atau null. */
    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return asset('storage/'.$this->avatar_path);
    }

    /** Huruf inisial untuk placeholder. */
    public function avatarInitial(): string
    {
        return strtoupper(mb_substr($this->name, 0, 1));
    }

    /**
     * Admin JP (dan Super Admin) boleh buat Keputusan Semakan penuh
     * (Disyorkan / Tidak Disyorkan / Kembalikan). Pegawai JP hanya perakuan.
     */
    public function canMakeFullJpReviewDecision(): bool
    {
        return $this->hasAnyRole([
            RoleName::SYSTEM_ADMIN->value,
            RoleName::SUPER_ADMIN->value,
        ]);
    }

    /** Admin JP boleh mencipta/hantar permohonan bagi pihak ALP. */
    public function canCreateApplicationOnBehalf(): bool
    {
        return $this->can('applications.create_on_behalf');
    }

    /** Admin JP boleh penepian lead time 2 bulan (BR-014) untuk notis pendek. */
    public function canWaiveProgramLeadTime(): bool
    {
        return $this->canCreateApplicationOnBehalf();
    }
}

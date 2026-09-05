<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Persatuan / penerima sumbangan (URS BR-008/009 — kunci: No. ROS). */
class Recipient extends Model
{
    protected $fillable = [
        'name',
        'ros_number',
        'bank_account',
        'address',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public static function normalizeRos(?string $ros): string
    {
        return mb_strtolower(trim((string) $ros));
    }
}

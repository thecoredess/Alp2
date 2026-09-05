<?php

namespace App\Models;

use App\Enums\ApplicationType;
use Illuminate\Database\Eloquent\Model;

class ApplicationWorkflowSetting extends Model
{
    protected $fillable = [
        'application_type',
        'requires_technical_review',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'application_type' => ApplicationType::class,
            'requires_technical_review' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /** Adakah jenis permohonan ini memerlukan semakan teknikal? */
    public static function requiresTechnicalReview(ApplicationType $type): bool
    {
        $setting = static::where('application_type', $type->value)->where('active', true)->first();

        return (bool) ($setting?->requires_technical_review ?? false);
    }
}

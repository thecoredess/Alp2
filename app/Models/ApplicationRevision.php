<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'revision_number',
        'previous_requested_amount',
        'snapshot',
        'returned_by',
        'return_stage',
        'reason',
        'returned_at',
        'resubmitted_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_requested_amount' => 'decimal:2',
            'snapshot' => 'array',
            'returned_at' => 'datetime',
            'resubmitted_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}

<?php

namespace App\Services\Reports;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Pelaporan jejak audit. Nilai lama/baru dipaparkan dengan selamat — kunci
 * sensitif ditapis supaya rahsia tidak terdedah.
 */
class AuditReportService
{
    /** Kunci yang tidak boleh dipaparkan dalam laporan audit. */
    private const SENSITIVE_KEYS = ['password', 'password_hash', 'remember_token', 'secret', 'token', 'api_key'];

    public function listing(array $filters, int $limit = 500): Collection
    {
        return AuditLog::query()
            ->with('user:id,name')
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', 'like', '%'.$v.'%'))
            ->when($filters['entity_type'] ?? null, fn ($q, $v) => $q->where('entity_type', 'like', '%'.$v.'%'))
            ->when($filters['entity_id'] ?? null, fn ($q, $v) => $q->where('entity_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (AuditLog $log) => [
                'timestamp' => $log->created_at,
                'user' => $log->user?->name ?? '—',
                'role' => $this->roleName($log->user),
                'action' => $log->action,
                'entity' => $this->entityLabel($log),
                'description' => $this->safeValues($log->new_values),
                'ip' => $log->ip_address,
            ]);
    }

    /** Senarai tindakan berbeza (untuk penapis dropdown). */
    public function distinctActions(): array
    {
        return AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action')->all();
    }

    private function roleName(?User $user): string
    {
        return $user ? ($user->getRoleNames()->first() ?? '—') : '—';
    }

    private function entityLabel(AuditLog $log): string
    {
        if (! $log->entity_type) {
            return '—';
        }
        $short = class_basename($log->entity_type);

        return $log->entity_id ? "{$short} #{$log->entity_id}" : $short;
    }

    /** Tapis kunci sensitif; pulangkan rentetan ringkas boleh baca. */
    private function safeValues(?array $values): string
    {
        if (empty($values)) {
            return '—';
        }
        $parts = [];
        foreach ($values as $k => $v) {
            if (in_array(strtolower((string) $k), self::SENSITIVE_KEYS, true)) {
                $v = '••••';
            }
            if (is_array($v)) {
                $v = '[…]';
            }
            $parts[] = "{$k}: {$v}";
        }

        return implode(' · ', $parts);
    }
}

@props([
    'limits',
    'value' => '',
])

@php
    $effectiveMax = (float) ($limits['effective_max'] ?? 0);
    $maxPerApp = (float) ($limits['max_per_application'] ?? 0);
    $available = (float) ($limits['available'] ?? 0);
    $periodRemaining = (float) ($limits['period_remaining'] ?? 0);
    $calendarYear = (int) ($limits['calendar_year'] ?? date('Y'));
    $policyOn = (bool) ($limits['policy_enabled'] ?? false);
    $forceMaxPerApp = (bool) ($limits['force_max_per_application'] ?? false);
    $hasAllocation = (bool) ($limits['has_allocation'] ?? false);
    $hintParts = [];
    if ($policyOn || $forceMaxPerApp) {
        $hintParts[] = 'Had setiap permohonan: RM'.number_format($maxPerApp, 2);
    }
    if ($policyOn) {
        $hintParts[] = 'Baki kuota tempoh: RM'.number_format($periodRemaining, 2);
    }
    if ($hasAllocation) {
        $hintParts[] = 'Baki peruntukan: RM'.number_format($available, 2).' · '.$calendarYear;
    }
    $hint = $hintParts !== [] ? implode(' · ', $hintParts) : null;
@endphp

<div
    {{ $attributes->merge(['class' => '']) }}
    x-data="{
        amount: @js(old('requested_amount', $value)),
        error: '',
        limits: {
            effectiveMax: {{ json_encode($effectiveMax) }},
            maxPerApp: {{ json_encode($maxPerApp) }},
            available: {{ json_encode($available) }},
            periodRemaining: {{ json_encode($periodRemaining) }},
            policyOn: @js($policyOn),
            forceMaxPerApp: @js($forceMaxPerApp),
            hasAllocation: @js($hasAllocation),
        },
        validate() {
            const raw = String(this.amount ?? '').trim();
            if (raw === '') {
                this.error = '';
                return;
            }
            const value = parseFloat(raw);
            if (Number.isNaN(value) || value <= 0) {
                this.error = 'Jumlah sumbangan mesti melebihi RM0.00.';
                return;
            }
            if ((this.limits.policyOn || this.limits.forceMaxPerApp) && value > this.limits.maxPerApp) {
                this.error = 'Jumlah melebihi had maksimum setiap permohonan (RM' + this.limits.maxPerApp.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ').';
                return;
            }
            if (this.limits.hasAllocation && value > this.limits.available) {
                this.error = 'Jumlah melebihi baki peruntukan tersedia (RM' + this.limits.available.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ').';
                return;
            }
            if (this.limits.policyOn && value > this.limits.periodRemaining) {
                this.error = 'Jumlah melebihi baki kuota tempoh semasa (RM' + this.limits.periodRemaining.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ').';
                return;
            }
            this.error = '';
        },
    }"
    x-init="validate()"
>
    <x-field label="f) Jumlah Sumbangan (RM)" name="requested_amount" :required="true" :hint="$hint">
        <input
            id="requested_amount"
            name="requested_amount"
            type="number"
            step="0.01"
            min="0.01"
            max="{{ max($effectiveMax, 0.01) }}"
            x-model="amount"
            @input="validate()"
            @blur="validate()"
            required
            class="inp"
            :class="error ? 'border-red-500 ring-1 ring-red-300' : ''"
        >
    </x-field>

    <div
        x-show="error"
        x-cloak
        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
        role="alert"
    >
        <strong>Ralat jumlah sumbangan:</strong>
        <span x-text="error"></span>
    </div>
</div>

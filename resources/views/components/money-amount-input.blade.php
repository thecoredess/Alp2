@props([
    'name' => 'amount',
    'value' => '',
    'required' => false,
    'id' => null,
])

@php
    $inputId = $id ?? $name;
    $raw = old($name, $value);
    $display = filled($raw)
        ? number_format((float) str_replace(',', '', (string) $raw), 2, '.', ',')
        : '';
@endphp

<input
    type="text"
    inputmode="decimal"
    id="{{ $inputId }}"
    name="{{ $name }}"
    value="{{ $display }}"
    @if ($required) required @endif
    {{ $attributes->class(['inp']) }}
    autocomplete="off"
    oninput="window.moneyAmountSanitize(this)"
    onblur="window.moneyAmountFormat(this)"
>

@once
    <script>
        window.moneyAmountParse = function (raw) {
            const cleaned = String(raw ?? '').replace(/,/g, '').trim();
            if (cleaned === '') {
                return null;
            }
            const amount = Number.parseFloat(cleaned);
            return Number.isFinite(amount) ? amount : null;
        };

        window.moneyAmountFormatValue = function (amount) {
            return amount.toLocaleString('en-MY', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        window.moneyAmountSanitize = function (input) {
            let value = String(input.value ?? '').replace(/[^\d.,]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts.shift() + '.' + parts.join('');
            }
            input.value = value;
        };

        window.moneyAmountFormat = function (input) {
            const amount = window.moneyAmountParse(input.value);
            if (amount === null) {
                return;
            }
            input.value = window.moneyAmountFormatValue(amount);
        };
    </script>
@endonce

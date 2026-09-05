@props(['value' => null, 'signed' => false])

@php
    use App\Support\Money;
    $money = $value instanceof Money ? $value : Money::of($value === null || $value === '' ? '0' : (string) $value);
    $sign = $money->isNegative() ? '-' : ($signed && $money->isPositive() ? '+' : '');
    $display = $sign . 'RM' . $money->abs()->format();
@endphp<span {{ $attributes }}>{{ $display }}</span>

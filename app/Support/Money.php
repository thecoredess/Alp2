<?php

namespace App\Support;

use InvalidArgumentException;
use Stringable;

/**
 * Objek nilai wang yang tepat (exact) berasaskan BCMath.
 *
 * - Diwakili secara dalaman sebagai string desimal berskala 2 (cth: "550000.00").
 * - TIADA aritmetik titik-terapung (float) digunakan pada bila-bila masa.
 * - Immutable: setiap operasi memulangkan objek Money baharu.
 * - Selaras dengan lajur MySQL DECIMAL(15,2).
 */
final class Money implements Stringable
{
    public const SCALE = 2;

    /** Had selari dengan DECIMAL(15,2): maksimum 13 digit sebelum titik. */
    private const MAX_INTEGER_DIGITS = 13;

    /** @var string Nilai kanonik berskala 2, cth "-123.45" */
    private readonly string $amount;

    private function __construct(string $canonical)
    {
        $this->amount = $canonical;
    }

    /**
     * Cipta Money daripada string atau integer.
     * Float TIDAK diterima (union int|string) untuk mengelakkan ketidaktepatan.
     */
    /** Tafsir input borang (cth. "1,234.56") sebelum disimpan. */
    public static function parseInput(int|string|null $value): self
    {
        if ($value === null || trim((string) $value) === '') {
            throw new InvalidArgumentException('Nilai wang tidak boleh kosong.');
        }

        $normalized = str_replace(',', '', trim((string) $value));

        return self::of($normalized);
    }

    public static function of(int|string $value): self
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            throw new InvalidArgumentException('Nilai wang tidak boleh kosong.');
        }

        // Tolak NaN, Infinity, notasi saintifik, dan aksara bukan angka.
        // Dibenarkan: tanda +/- pilihan, digit, satu titik perpuluhan dengan 1-2 desimal.
        if (! preg_match('/^[+-]?\d+(\.\d{1,2})?$/', $raw)) {
            throw new InvalidArgumentException("Format wang tidak sah: \"{$raw}\". Hanya nombor dengan maksimum 2 titik perpuluhan dibenarkan.");
        }

        // Normalkan ke skala 2 (bcadd tidak membundar; input telah disahkan ≤ 2 desimal).
        $canonical = bcadd($raw, '0', self::SCALE);

        // Elakkan "-0.00".
        if ($canonical === '-0.00') {
            $canonical = '0.00';
        }

        self::assertWithinRange($canonical);

        return new self($canonical);
    }

    public static function zero(): self
    {
        return new self('0.00');
    }

    // ── Aritmetik tepat ─────────────────────────────────────────

    public function plus(self $other): self
    {
        return self::of(bcadd($this->amount, $other->amount, self::SCALE));
    }

    public function minus(self $other): self
    {
        return self::of(bcsub($this->amount, $other->amount, self::SCALE));
    }

    public function negate(): self
    {
        return self::of(bcsub('0', $this->amount, self::SCALE));
    }

    /**
     * Darab dengan integer bukan-negatif (cth kuantiti item bajet).
     * Kekal tepat kerana pendarab ialah integer.
     */
    public function times(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Pendarab tidak boleh negatif.');
        }

        return self::of(bcmul($this->amount, (string) $factor, self::SCALE));
    }

    public function abs(): self
    {
        return $this->isNegative() ? $this->negate() : $this;
    }

    // ── Perbandingan (tiada perbandingan float) ─────────────────

    /** -1 jika < other, 0 jika sama, 1 jika > other. */
    public function compareTo(self $other): int
    {
        return bccomp($this->amount, $other->amount, self::SCALE);
    }

    public function equals(self $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    public function lessThan(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function greaterThan(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) < 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) > 0;
    }

    // ── Penukaran / paparan ─────────────────────────────────────

    /** Nilai kanonik untuk disimpan ke DB, cth "550000.00". */
    public function value(): string
    {
        return $this->amount;
    }

    public function __toString(): string
    {
        return $this->amount;
    }

    /**
     * Peratus $this berbanding $base sebagai float untuk PAPARAN sahaja.
     * Operan wang kekal tepat; hanya hasil nisbah (bukan wang) ditukar.
     */
    public function percentageOf(self $base, int $decimals = 1): float
    {
        if ($base->isZero()) {
            return 0.0;
        }

        $ratio = bcdiv($this->amount, $base->amount, 8);      // tepat kepada 8 tempat
        $percent = bcmul($ratio, '100', $decimals + 2);
        $rounded = bcadd($percent, '0', $decimals);           // pangkas ke $decimals

        return (float) $rounded; // nisbah paparan, bukan nilai wang
    }

    /** Format untuk paparan, cth "550,000.00" (paparan sahaja, bukan sumber kebenaran). */
    public function format(): string
    {
        $negative = $this->isNegative();
        $abs = ltrim($this->amount, '-');
        [$whole, $fraction] = explode('.', $abs);
        $grouped = number_format((int) $whole).'.'.$fraction;
        // number_format((int)$whole) hanya untuk pemisah ribuan bahagian integer.

        return ($negative ? '-' : '').$grouped;
    }

    private static function assertWithinRange(string $canonical): void
    {
        $digits = ltrim(explode('.', ltrim($canonical, '-'))[0], '0');

        if (strlen($digits) > self::MAX_INTEGER_DIGITS) {
            throw new InvalidArgumentException('Nilai wang melebihi had DECIMAL(15,2).');
        }
    }
}

<?php

namespace App\Services\Application;

use App\Models\ApprovalLevel;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Matriks kelulusan boleh dikonfig. Menentukan aras kelulusan yang diperlukan
 * bagi sesuatu jumlah, dan mengesahkan konfigurasi (tiada julat bertindih).
 *
 * Model: jumlah menentukan "band" (aras yang julatnya mengandungi jumlah).
 * Kelulusan berperingkat diperlukan dari aras 1 hingga aras band tersebut.
 */
class ApprovalMatrixService
{
    /**
     * Aras kelulusan aktif yang terpakai (khusus tahun kewangan atau global).
     *
     * @return Collection<int, ApprovalLevel>
     */
    public function applicableLevels(?int $financialYearId): Collection
    {
        return ApprovalLevel::query()
            ->where('active', true)
            ->where(function ($q) use ($financialYearId) {
                $q->whereNull('financial_year_id');
                if ($financialYearId) {
                    $q->orWhere('financial_year_id', $financialYearId);
                }
            })
            ->orderBy('sequence')
            ->get();
    }

    /**
     * Aras yang diperlukan (berurutan) untuk meluluskan sesuatu jumlah.
     * Mengembalikan aras 1..N di mana N ialah band yang mengandungi jumlah.
     *
     * @return Collection<int, ApprovalLevel>
     *
     * @throws ApplicationException jika tiada band terpakai untuk jumlah ini
     */
    public function requiredLevels(Money $amount, ?int $financialYearId): Collection
    {
        $levels = $this->applicableLevels($financialYearId);

        $band = $levels->first(fn (ApprovalLevel $l) => $l->contains($amount));

        if (! $band) {
            throw new ApplicationException('Tiada matriks kelulusan dikonfigurasi untuk jumlah ini.');
        }

        return $levels->filter(fn (ApprovalLevel $l) => $l->sequence <= $band->sequence)->values();
    }

    /**
     * Sahkan tiada julat aktif bertindih (mengabaikan satu id semasa menyunting).
     *
     * @throws ApplicationException jika bertindih
     */
    public function assertNoOverlap(?int $financialYearId, Money $min, ?Money $max, ?int $ignoreId = null): void
    {
        $levels = $this->applicableLevels($financialYearId)
            ->when($ignoreId, fn ($c) => $c->reject(fn ($l) => $l->id === $ignoreId));

        foreach ($levels as $level) {
            $lMin = $level->minMoney();
            $lMax = $level->maxMoney();

            // Bertindih jika (min <= lMax) DAN (max >= lMin), mengambil kira ∞ (null).
            $startsBeforeOtherEnds = $lMax === null || ! $min->greaterThan($lMax);
            $endsAfterOtherStarts = $max === null || ! $max->lessThan($lMin);

            if ($startsBeforeOtherEnds && $endsAfterOtherStarts) {
                throw new ApplicationException("Julat bertindih dengan aras sedia ada: {$level->name}.");
            }
        }
    }
}

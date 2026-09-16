<?php

namespace App\Support;

use Carbon\Carbon;

/** Tarikh Hijrah dalam format rasmi DBKL (contoh: 28 Rabiulawal 1448H). */
class HijriDate
{
    public static function malayLabel(?Carbon $date = null): string
    {
        $date ??= now();

        if (! class_exists(\IntlCalendar::class)) {
            return '—';
        }

        try {
            $cal = \IntlCalendar::createInstance(null, 'ms_MY@calendar=islamic');
            if (! $cal) {
                return '—';
            }
            $cal->setTime($date->getTimestamp() * 1000);
            $day = $cal->get(\IntlCalendar::FIELD_DAY_OF_MONTH);
            $month = $cal->get(\IntlCalendar::FIELD_MONTH) + 1;
            $months = [
                1 => 'Muharram', 2 => 'Safar', 3 => 'Rabiulawal', 4 => 'Rabiulakhir',
                5 => 'Jamadilawal', 6 => 'Jamadilakhir', 7 => 'Rejab', 8 => 'Syaaban',
                9 => 'Ramadan', 10 => 'Syawal', 11 => 'Zulkaedah', 12 => 'Zulhijjah',
            ];
            $year = $cal->get(\IntlCalendar::FIELD_YEAR);

            return sprintf('%d %s %dH', $day, $months[$month] ?? '', $year);
        } catch (\Throwable) {
            return '—';
        }
    }
}

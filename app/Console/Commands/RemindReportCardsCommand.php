<?php

namespace App\Console\Commands;

use App\Services\Application\ApplicationReportCardService;
use Illuminate\Console\Command;

/** NT-007 — peringatan muat naik report card. */
class RemindReportCardsCommand extends Command
{
    protected $signature = 'urs:remind-report-cards {--upcoming : Termasuk 7 hari sebelum tarikh akhir}';

    protected $description = 'Hantar peringatan NT-007 untuk laporan aktiviti / report card tertunggak';

    public function handle(ApplicationReportCardService $reportCards): int
    {
        $count = $reportCards->sendReminders(onlyOverdue: ! $this->option('upcoming'));
        $this->info("Peringatan dihantar: {$count}");

        return self::SUCCESS;
    }
}

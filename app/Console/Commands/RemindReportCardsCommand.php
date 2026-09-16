<?php

namespace App\Console\Commands;

use App\Services\Application\ApplicationReportCardService;
use Illuminate\Console\Command;

/** NT-007 — peringatan muat naik report card. */
class RemindReportCardsCommand extends Command
{
    protected $signature = 'urs:remind-report-cards';

    protected $description = 'NT-007 — peringatan laporan aktiviti 7 hari sebelum & selepas tarikh akhir';

    public function handle(ApplicationReportCardService $reportCards): int
    {
        $count = $reportCards->sendReminders();
        $this->info("Peringatan dihantar: {$count}");

        return self::SUCCESS;
    }
}

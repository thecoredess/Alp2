<?php

use App\Console\Commands\AlertPendingPaymentsCommand;
use App\Console\Commands\RemindReportCardsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(RemindReportCardsCommand::class)
    ->dailyAt('08:30')
    ->name('urs-remind-report-cards')
    ->withoutOverlapping();

// Tangkap permohonan yang diluluskan tetapi alertnya terlepas (contoh: baris gagal
// dihantar). Tanpa --paksa, permohonan yang sudah dialert dilangkau.
Schedule::command(AlertPendingPaymentsCommand::class)
    ->dailyAt('08:45')
    ->name('urs-alert-pembayaran')
    ->withoutOverlapping();

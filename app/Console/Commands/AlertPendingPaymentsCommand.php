<?php

namespace App\Console\Commands;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Services\Notification\ApplicationNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Alert Kewangan JP bagi permohonan yang telah diluluskan PEPU tetapi
 * belum selesai proses bayaran.
 *
 * Kegunaan: (a) isi notifikasi bagi permohonan yang diluluskan sebelum
 * alert automatik diperkenalkan, (b) peringatan berkala untuk baucar
 * yang masih tertunggak.
 */
class AlertPendingPaymentsCommand extends Command
{
    protected $signature = 'urs:alert-pembayaran
                            {--paksa : Hantar semula walaupun alert pernah dihantar}
                            {--kering : Papar sahaja, tanpa menghantar}';

    protected $description = 'Hantar alert kepada Kewangan JP untuk permohonan diluluskan yang menunggu proses bayaran';

    public function handle(ApplicationNotifier $notifier): int
    {
        $applications = Application::query()
            ->where('status', ApplicationStatus::APPROVED->value)
            ->whereIn('payment_status', ApplicationPaymentStatus::openValues())
            ->orderBy('id')
            ->get();

        if ($applications->isEmpty()) {
            $this->info('Tiada permohonan menunggu proses bayaran.');

            return self::SUCCESS;
        }

        $alreadyAlerted = $this->option('paksa') ? [] : $this->alreadyAlertedApplicationIds();
        $sent = 0;

        foreach ($applications as $application) {
            if (in_array($application->id, $alreadyAlerted, true)) {
                $this->line("  dilangkau  {$application->application_number} (alert pernah dihantar)");

                continue;
            }

            $this->line("  alert      {$application->application_number} · {$application->payment_status->label()} · RM".$application->requestedAmountMoney()->format());

            if (! $this->option('kering')) {
                $notifier->awaitingPayment($application);
            }

            $sent++;
        }

        $this->info($this->option('kering')
            ? "Kering: {$sent} permohonan akan dialert."
            : "Alert dihantar untuk {$sent} permohonan.");

        return self::SUCCESS;
    }

    /**
     * Notifikasi disimpan sebagai teks JSON, jadi tapis kasar dengan LIKE
     * kemudian sahkan dengan decode.
     *
     * @return list<int>
     */
    private function alreadyAlertedApplicationIds(): array
    {
        return DB::table('notifications')
            ->where('data', 'like', '%awaiting_payment%')
            ->pluck('data')
            ->map(function ($json) {
                $data = json_decode((string) $json, true);

                return ($data['event'] ?? null) === 'awaiting_payment'
                    ? ($data['application_id'] ?? null)
                    : null;
            })
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}

<?php

namespace App\Console\Commands;

use App\Models\ApplicationDocument;
use App\Support\PlaceholderPdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Data simulasi terdahulu mencipta rekod dokumen tanpa menulis fail ke disk,
 * menyebabkan pautan lampiran memulangkan 404. Perintah ini menjana semula
 * fail PDF simulasi yang hilang.
 */
class RepairSimulationDocumentsCommand extends Command
{
    protected $signature = 'urs:repair-simulation-documents {--dry-run : Papar sahaja tanpa menulis fail}';

    protected $description = 'Jana semula fail PDF simulasi yang hilang untuk lampiran permohonan';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $repaired = 0;
        $skipped = 0;

        $documents = ApplicationDocument::query()
            ->with('application:id,application_number')
            ->where('stored_path', 'like', 'simulation/%')
            ->get();

        foreach ($documents as $document) {
            if (Storage::disk('local')->exists($document->stored_path)) {
                continue;
            }

            if ($document->application === null) {
                $skipped++;

                continue;
            }

            $this->line('  '.($dryRun ? 'akan jana' : 'jana').': '.$document->stored_path);

            if (! $dryRun) {
                Storage::disk('local')->put($document->stored_path, PlaceholderPdf::make(
                    $document->document_type->label(),
                    [
                        'Dokumen simulasi UAT - bukan dokumen sebenar.',
                        'Permohonan: '.$document->application->application_number,
                    ],
                ));
            }

            $repaired++;
        }

        $this->info("Fail simulasi dijana: {$repaired}".($skipped > 0 ? " (dilangkau: {$skipped})" : ''));

        return self::SUCCESS;
    }
}

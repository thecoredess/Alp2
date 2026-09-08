<?php

namespace Database\Seeders;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\DocumentType;
use App\Enums\FinancialYearStatus;
use App\Enums\ProgramCategory;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Allocation;
use App\Models\BudgetTransaction;
use App\Models\DocumentRequirement;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Application\ApplicationNumberGenerator;
use App\Services\Application\ApplicationReviewService;
use App\Services\Application\ApplicationSubmissionService;
use App\Services\Application\ApprovalService;
use App\Services\Budget\BudgetService;
use App\Support\JpReviewChecklist;
use App\Support\Money;
use App\Support\PlaceholderPdf;
use App\Support\UrsContributionPolicy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Data simulasi UAT — permohonan sumbangan ALP dalam pelbagai status.
 * Jalankan: php artisan db:seed --class=SimulationSeeder
 */
class SimulationSeeder extends Seeder
{
    private const PREFIX = '[SIM] ';

    /** @var list<array{label: string, number: string, status: string, url: string, login?: string}> */
    private array $guide = [];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('SimulationSeeder dilangkau — production.');

            return;
        }

        $this->ensureFoundation();

        if (! User::where('email', 'alp01@dbkl.test')->exists()) {
            $this->call(DevSeeder::class);
        }

        $alpUser = User::where('email', 'alp01@dbkl.test')->first();
        $alp = $alpUser?->alp ?? Alp::where('ref_code', 'ALP01')->orWhere('ref_code', 'ALP-01')->first();
        $year = FinancialYear::query()->where('is_active', true)->first()
            ?? FinancialYear::query()->where('year', 2026)->first();

        if (! $alp || ! $year) {
            $this->command->error('ALP atau tahun kewangan tidak dijumpai. Jalankan DevSeeder / OfficialAlp2026Seeder dahulu.');

            return;
        }

        $this->purgeSimulation();
        $this->ensureBudget($alp, $year);

        $jp = User::where('email', 'urussetia@dbkl.test')->first();
        $peraku = User::where('email', 'pelulus@dbkl.test')->first();
        $pepu = User::where('email', 'pengurusan@dbkl.test')->first();

        $numbers = app(ApplicationNumberGenerator::class);
        $submit = app(ApplicationSubmissionService::class);
        $reviews = app(ApplicationReviewService::class);
        $approvals = app(ApprovalService::class);

        // 1) Draf — borang sahaja (belum lampiran)
        $draft = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Draf — belum muat naik lampiran',
            'requested_amount' => '1200.00',
            'program_category' => ProgramCategory::KOMUNITI,
            'status' => ApplicationStatus::DRAFT,
        ]);
        $this->record($draft, 'Draf (tiada lampiran)', 'alp01@dbkl.test');

        // 2) Draf — lengkap, sedia hantar
        $ready = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Draf — sedia hantar kepada JP',
            'requested_amount' => '1500.00',
            'program_category' => ProgramCategory::PENDIDIKAN,
            'status' => ApplicationStatus::DRAFT,
        ]);
        $this->attachApplicationDocuments($ready);
        $this->record($ready, 'Draf lengkap (sedia hantar)', 'alp01@dbkl.test');

        // 3) Draf — tarikh program perlu dikemaskini (dicipta 3 minggu lalu)
        $stale = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Draf — tarikh program perlu dikemaskini',
            'requested_amount' => '1800.00',
            'program_category' => ProgramCategory::SUKAN,
            'program_date' => now()->subWeeks(1)->addMonths(2)->toDateString(),
            'status' => ApplicationStatus::DRAFT,
        ]);
        $stale->forceFill([
            'created_at' => now()->subWeeks(3),
            'updated_at' => now()->subWeeks(3),
        ])->save();
        $this->attachApplicationDocuments($stale);
        $this->record($stale, 'Draf — amaran tarikh program (lapuk)', 'alp01@dbkl.test');

        // 4) Dihantar — menunggu semakan JP
        $submitted = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Menunggu semakan Pegawai JP',
            'requested_amount' => '300.00',
            'program_category' => ProgramCategory::KEMASYARAKATAN,
        ]);
        $this->attachApplicationDocuments($submitted);
        $submit->submit($submitted->fresh(), $alpUser);
        $this->record($submitted->fresh(), 'Dihantar → semakan JP', 'urussetia@dbkl.test');

        // 5) Menunggu Peraku (JP disyorkan)
        $pendingPeraku = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Menunggu Peraku (TP/Pengarah JP)',
            'requested_amount' => '300.00',
            'program_category' => ProgramCategory::KOMUNITI,
        ]);
        $this->attachApplicationDocuments($pendingPeraku);
        $submit->submit($pendingPeraku->fresh(), $alpUser);
        if ($jp) {
            $reviews->review(
                $pendingPeraku->fresh(),
                ReviewType::SECRETARIAT,
                $jp,
                ReviewDecision::RECOMMEND,
                'Disyorkan (simulasi)',
                $this->lengkapChecklist(),
            );
        }
        $this->record($pendingPeraku->fresh(), 'Menunggu Peraku', 'pelulus@dbkl.test');

        // 6) Menunggu PEPU (Peraku lulus)
        $pendingPepu = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Menunggu kelulusan PEPU',
            'requested_amount' => '350.00',
            'program_category' => ProgramCategory::PENDIDIKAN,
        ]);
        $this->attachApplicationDocuments($pendingPepu);
        $submit->submit($pendingPepu->fresh(), $alpUser);
        if ($jp && $peraku) {
            $reviews->review($pendingPepu->fresh(), ReviewType::SECRETARIAT, $jp, ReviewDecision::RECOMMEND, null, $this->lengkapChecklist());
            $approvals->approve($pendingPepu->fresh(), $peraku, 'Peraku lulus (simulasi)');
        }
        $this->record($pendingPepu->fresh(), 'Menunggu PEPU', 'pengurusan@dbkl.test');

        // 7) Diluluskan — menunggu baucar
        $approvedPendingVoucher = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Diluluskan — menunggu baucar',
            'requested_amount' => '350.00',
            'program_category' => ProgramCategory::KOMUNITI,
        ]);
        $this->attachApplicationDocuments($approvedPendingVoucher);
        $submit->submit($approvedPendingVoucher->fresh(), $alpUser);
        if ($jp && $peraku && $pepu) {
            $reviews->review($approvedPendingVoucher->fresh(), ReviewType::SECRETARIAT, $jp, ReviewDecision::RECOMMEND, null, $this->lengkapChecklist());
            $approvals->approve($approvedPendingVoucher->fresh(), $peraku, null);
            $approvals->approve($approvedPendingVoucher->fresh(), $pepu, 'PEPU lulus (simulasi)');
        }
        $this->record($approvedPendingVoucher->fresh(), 'Diluluskan (ALP: Dalam proses kelulusan)', 'alp01@dbkl.test');

        // 8) Diluluskan — baucar disedia
        $voucherReady = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Diluluskan — baucar disedia',
            'requested_amount' => '400.00',
            'program_category' => ProgramCategory::SUKAN,
        ]);
        $this->attachApplicationDocuments($voucherReady);
        $submit->submit($voucherReady->fresh(), $alpUser);
        if ($jp && $peraku && $pepu) {
            $reviews->review($voucherReady->fresh(), ReviewType::SECRETARIAT, $jp, ReviewDecision::RECOMMEND, null, $this->lengkapChecklist());
            $approvals->approve($voucherReady->fresh(), $peraku, null);
            $app = $approvals->approve($voucherReady->fresh(), $pepu, null);
            $app->update([
                'payment_status' => ApplicationPaymentStatus::VOUCHER_PREPARED,
                'payment_supplier_no' => 'SUP-SIM-001',
                'payment_voucher_no' => 'BV/SIM/2026/001',
                'payment_voucher_date' => now()->toDateString(),
                'payment_updated_at' => now(),
            ]);
        }
        $this->record($voucherReady->fresh(), 'Diluluskan + baucar (ALP timeline selesai)', 'alp01@dbkl.test');

        // 9) Ditolak — Peraku menolak selepas disyorkan JP
        $rejected = $this->createApplication($numbers, $alp, $year, [
            'purpose' => self::PREFIX.'Permohonan ditolak',
            'requested_amount' => '300.00',
            'program_category' => ProgramCategory::KEMASYARAKATAN,
        ]);
        $this->attachApplicationDocuments($rejected);
        $submit->submit($rejected->fresh(), $alpUser);
        if ($jp && $peraku) {
            $reviews->review($rejected->fresh(), ReviewType::SECRETARIAT, $jp, ReviewDecision::RECOMMEND, null, $this->lengkapChecklist());
            $approvals->reject($rejected->fresh(), $peraku, 'Ditolak (simulasi)');
        }
        $this->record($rejected->fresh(), 'Ditolak (Peraku)', 'alp01@dbkl.test');

        $this->printGuide($alp);
    }

    private function ensureFoundation(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(DocumentRequirementSeeder::class);
        $this->call(WorkflowSettingsSeeder::class);
        $this->call(ApprovalLevelSeeder::class);

        FinancialYear::firstOrCreate(
            ['year' => 2026],
            [
                'label' => 'Tahun Kewangan 2026',
                'status' => FinancialYearStatus::ACTIVE,
                'is_active' => true,
                'opened_at' => now(),
            ]
        );
    }

    private function ensureBudget(Alp $alp, FinancialYear $year): void
    {
        $budget = app(BudgetService::class);
        $allocation = Allocation::query()
            ->where('alp_id', $alp->id)
            ->where('financial_year_id', $year->id)
            ->first();

        if (! $allocation) {
            $actor = User::where('email', 'superadmin@dbkl.test')->first() ?? User::first();
            if ($actor) {
                auth()->login($actor);
            }
            $budget->allocate($alp, $year, '50000.00', 'SIM/2026/ALP01');

            return;
        }

        $available = $budget->summaryFor($alp->id, $year->id)->available();
        if ($available->lessThan(Money::of('12000'))) {
            $actor = User::where('email', 'superadmin@dbkl.test')->first() ?? User::first();
            if ($actor) {
                auth()->login($actor);
            }
            $budget->adjust($allocation, '20000.00', 'SIM/ADJ/2026', 'Pelarasan simulasi UAT');
        }
    }

    private function purgeSimulation(): void
    {
        $apps = Application::query()->where('purpose', 'like', self::PREFIX.'%')->get();

        foreach ($apps as $app) {
            BudgetTransaction::query()->where('application_id', $app->id)->delete();
            $app->documents()->delete();
            $app->reviews()->delete();
            $app->approvals()->delete();
            $app->statusHistories()->delete();
            $app->revisions()->delete();
            $app->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createApplication(
        ApplicationNumberGenerator $numbers,
        Alp $alp,
        FinancialYear $year,
        array $overrides,
    ): Application {
        $programDate = $overrides['program_date']
            ?? UrsContributionPolicy::minimumProgramDate()->toDateString();

        $payload = array_merge([
            'application_number' => $numbers->next(ApplicationType::SUMBANGAN, $year->year),
            'financial_year_id' => $year->id,
            'alp_id' => $alp->id,
            'application_type' => ApplicationType::SUMBANGAN,
            'purpose' => self::PREFIX.'Contoh',
            'recipient_name' => 'Persatuan Komuniti Simulasi KL',
            'recipient_ros_number' => 'ROS-SIM-'.fake()->unique()->numerify('######'),
            'recipient_bank_account' => '9876543210',
            'recipient_address' => 'No. 10, Jalan Raja Laut, 50350 Kuala Lumpur',
            'program_date' => $programDate,
            'program_category' => ProgramCategory::KOMUNITI,
            'requested_amount' => '1500.00',
            'status' => ApplicationStatus::DRAFT,
        ], $overrides);

        return Application::create($payload);
    }

    private function attachApplicationDocuments(Application $application): void
    {
        foreach (DocumentRequirement::requiredFor() as $type) {
            $storedPath = "simulation/{$application->id}/sim-{$type->value}.pdf";
            $content = PlaceholderPdf::make($type->label(), [
                'Dokumen simulasi UAT - bukan dokumen sebenar.',
                'Permohonan: '.$application->application_number,
            ]);

            // Tulis fail sebenar supaya pratonton lampiran berfungsi.
            Storage::disk('local')->put($storedPath, $content);

            ApplicationDocument::create([
                'application_id' => $application->id,
                'document_type' => $type,
                'original_filename' => "sim-{$type->value}.pdf",
                'stored_path' => $storedPath,
                'mime_type' => 'application/pdf',
                'file_size' => strlen($content),
                'sha256' => hash('sha256', $content),
            ]);
        }
    }

    /** @return array<string, string> */
    private function lengkapChecklist(): array
    {
        return collect(JpReviewChecklist::keys())
            ->mapWithKeys(fn (string $k) => [$k => JpReviewChecklist::LENGKAP])
            ->all();
    }

    private function record(Application $application, string $label, string $login): void
    {
        $base = rtrim(config('app.url', 'http://localhost/Alp2-main/public'), '/');

        $this->guide[] = [
            'label' => $label,
            'number' => $application->application_number,
            'status' => $application->status->label(),
            'url' => $base.'/permohonan/'.$application->id,
            'login' => $login,
        ];
    }

    private function printGuide(Alp $alp): void
    {
        $base = rtrim(config('app.url', 'http://localhost/Alp2-main/public'), '/');

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  SIMULASI UAT — Sistem ALP DBKL (data dummy)');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->line('  URL asas : '.$base);
        $this->command->line('  Kata laluan semua akaun : password');
        $this->command->newLine();
        $this->command->line('  ALP demo : alp01@dbkl.test ('.$alp->ref_code.' — '.$alp->name.')');
        $this->command->line('  JP       : urussetia@dbkl.test');
        $this->command->line('  Peraku   : pelulus@dbkl.test');
        $this->command->line('  PEPU     : pengurusan@dbkl.test');
        $this->command->newLine();
        $this->command->info('  Senario permohonan [SIM]:');

        foreach ($this->guide as $row) {
            $this->command->line(sprintf(
                '  • %-42s %s',
                $row['label'],
                $row['number'],
            ));
            $this->command->line('    Status: '.$row['status'].' | Log masuk: '.$row['login']);
            $this->command->line('    '.$row['url']);
        }

        $this->command->newLine();
        $this->command->info('  Aliran simulasi cadangan:');
        $this->command->line('  1. alp01 → Permohonan Baharu → isi borang → Simpan Draf');
        $this->command->line('  2. alp01 → sambung draf stale → lihat amaran tarikh program');
        $this->command->line('  3. urussetia → Semakan JP → disyorkan permohonan SUBMITTED');
        $this->command->line('  4. pelulus → lulus → pengurusan → lulus (2 aras)');
        $this->command->line('  5. alp01 → tab Status → timeline ALP (3 langkah)');
        $this->command->line('  6. kewangan → kemas kini baucar → alp01 lihat "Baucar disedia"');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}

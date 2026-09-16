<?php

namespace Database\Seeders;

use App\Enums\AlpStatus;
use App\Enums\FinancialYearStatus;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  DATA PEMBANGUNAN SAHAJA (DEVELOPMENT DATA)                  │
 * │  JANGAN jalankan seeder ini dalam persekitaran PRODUCTION.   │
 * │  Semua kata laluan lalai ialah: "password"                  │
 * └─────────────────────────────────────────────────────────────┘
 */
class DevSeeder extends Seeder
{
    private const DEV_PASSWORD = 'password';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('DevSeeder dilangkau — persekitaran production.');
            return;
        }

        // Matriks kelulusan contoh (DEVELOPMENT CONFIGURATION, bukan polisi DBKL).
        $this->call(ApprovalLevelSeeder::class);

        // 1. Tahun kewangan aktif.
        $year = FinancialYear::firstOrCreate(
            ['year' => 2026],
            [
                'label' => 'Tahun Kewangan 2026',
                'status' => FinancialYearStatus::ACTIVE,
                'is_active' => true,
                'opened_at' => now(),
            ]
        );

        // 2. Profil ALP (3 orang).
        $alps = collect([
            ['ALP-01', 'Dato\' Ahmad bin Ismail', 'Zon Utara'],
            ['ALP-02', 'Datin Siti binti Hassan', 'Zon Tengah'],
            ['ALP-03', 'Tuan Rajesh a/l Kumar', 'Zon Selatan'],
        ])->map(fn ($row) => Alp::firstOrCreate(
            ['ref_code' => $row[0]],
            [
                'name' => $row[1],
                'portfolio_zone' => $row[2],
                'appointment_start' => now()->subYear()->startOfYear(),
                'appointment_end' => now()->addYear()->endOfYear(),
                'status' => AlpStatus::ACTIVE,
            ]
        ));

        // 3. Akaun pengguna mengikut peranan.
        $this->makeUser('Super Admin', 'superadmin@dbkl.test', RoleName::SUPER_ADMIN);
        $this->makeUser('Admin JP', 'adminjp@dbkl.test', RoleName::SYSTEM_ADMIN);
        $this->makeUser('Pentadbir Sistem', 'sysadmin@dbkl.test', RoleName::SYSTEM_ADMIN);

        // ALP + akaun log masuk mereka.
        $this->makeUser($alps[0]->name, 'alp01@dbkl.test', RoleName::ALP, $alps[0]);
        $this->makeUser($alps[1]->name, 'alp02@dbkl.test', RoleName::ALP, $alps[1]);
        $this->makeUser($alps[2]->name, 'alp03@dbkl.test', RoleName::ALP, $alps[2]);

        $this->makeUser('Urus Setia ALP-01', 'urussetiaalp@dbkl.test', RoleName::URUSSETIA_ALP, $alps[0]);
        $this->makeUser('Pegawai Urus Setia DBKL', 'urussetia@dbkl.test', RoleName::PEGAWAI_URUSSETIA);
        $this->makeUser('Pegawai Kewangan', 'kewangan@dbkl.test', RoleName::PEGAWAI_KEWANGAN);
        $this->makeUser('Pegawai JKEW', 'jkew@dbkl.test', RoleName::PEGAWAI_JKEW);
        $this->makeUser('Pegawai Teknikal', 'teknikal@dbkl.test', RoleName::PEGAWAI_TEKNIKAL);
        $this->makeUser('TP / Pengarah JP', 'pelulus@dbkl.test', RoleName::PELULUS);
        $this->makeUser('Pengurusan DBKL', 'pengurusan@dbkl.test', RoleName::PENGURUSAN);

        // 4. Peruntukan contoh MELALUI aliran maker-checker (governance) — DEV DATA.
        //    Maker = Pegawai Kewangan; Checker = Pelulus (mesti berbeza).
        $maker = User::where('email', 'kewangan@dbkl.test')->first();
        $checker = User::where('email', 'pelulus@dbkl.test')->first();
        $requestService = app(\App\Services\Budget\BudgetRequestService::class);
        $approvalService = app(\App\Services\Budget\BudgetRequestApprovalService::class);
        $amounts = ['ALP-01' => '30000.00', 'ALP-02' => '30000.00', 'ALP-03' => '30000.00'];

        foreach ($alps as $alp) {
            $exists = \App\Models\Allocation::where('alp_id', $alp->id)
                ->where('financial_year_id', $year->id)->exists();
            if ($exists) {
                continue;
            }

            // Peruntukan awal: cadangan → hantar → luluskan.
            $req = $requestService->createDraft($maker, [
                'request_type' => \App\Enums\BudgetRequestType::INITIAL_ALLOCATION,
                'alp_id' => $alp->id,
                'financial_year_id' => $year->id,
                'amount' => $amounts[$alp->ref_code] ?? '30000.00',
                'reference_number' => "DBKL/BGT/{$year->year}/".str_pad((string) $alp->id, 4, '0', STR_PAD_LEFT),
                'reason' => 'Peruntukan tahunan URS (RM30,000)',
            ]);
            $requestService->submit($req, $maker);
            $approvalService->approve($req->fresh(), $checker);
        }

        // 5. Permohonan contoh (data pembangunan) untuk ALP-01.
        $this->makeSampleApplications($alps[0], $year);

        // 6. Permohonan DILULUSKAN contoh untuk ALP-02 (menunjukkan komitmen bajet).
        $this->makeApprovedSample($alps[1], $year);

        // 7. Projek pelbagai status untuk pelaporan (DEV DATA) — ALP-03 & ALP-01.
        $this->makeReportingSamples($alps, $year);

        $this->command->info('DevSeeder selesai. Log masuk contoh: superadmin@dbkl.test / password');
    }

    /**
     * Permohonan diluluskan + komitmen ledger (DEV DATA) untuk demonstrasi.
     */
    private function makeApprovedSample(Alp $alp, FinancialYear $year): void
    {
        if (\App\Models\Application::where('alp_id', $alp->id)->exists()) {
            return;
        }

        $numbers = app(\App\Services\Application\ApplicationNumberGenerator::class);
        $budget = app(\App\Services\Budget\BudgetService::class);

        \Illuminate\Support\Facades\DB::transaction(function () use ($alp, $year, $numbers, $budget) {
            $app = \App\Models\Application::create([
                'application_number' => $numbers->next(\App\Enums\ApplicationType::CSR, $year->year),
                'financial_year_id' => $year->id,
                'alp_id' => $alp->id,
                'application_type' => \App\Enums\ApplicationType::CSR,
                'purpose' => 'Program Pendidikan Komuniti (Diluluskan)',
                'recipient_name' => 'Persatuan Pendidikan Komuniti',
                'recipient_bank_account' => '1000000001',
                'requested_amount' => '40000.00',
                'status' => \App\Enums\ApplicationStatus::APPROVED,
                'submitted_at' => now()->subDays(3),
            ]);

            \App\Models\ApplicationStatusHistory::create([
                'application_id' => $app->id,
                'from_status' => \App\Enums\ApplicationStatus::PENDING_APPROVAL->value,
                'to_status' => \App\Enums\ApplicationStatus::APPROVED->value,
                'remarks' => 'Diluluskan (data pembangunan)',
                'created_at' => now(),
            ]);

            // Cipta projek + komitmen dikaitkan projek (Fasa 5).
            $project = app(\App\Services\Project\ProjectCreationService::class)->createFromApproved($app->fresh());
            $allocation = \App\Models\Allocation::where('alp_id', $alp->id)
                ->where('financial_year_id', $year->id)->first();
            if ($allocation) {
                $budget->recordCommitment($allocation, \App\Support\Money::of('40000.00'), $app, null, $project);
            }
        });

        // Mulakan projek & tambah satu perbelanjaan DISAHKAN (maker-checker) — DEV DATA.
        $project = \App\Models\Project::whereHas('application', fn ($q) => $q->where('alp_id', $alp->id))->first();
        if ($project) {
            $operator = User::where('email', 'urussetia@dbkl.test')->first();
            $expMaker = User::where('email', 'kewangan@dbkl.test')->first();
            $expChecker = User::where('email', 'pelulus@dbkl.test')->first();

            app(\App\Services\Project\ProjectService::class)->start($project, $operator);
            app(\App\Services\Project\ProjectService::class)->updateProgress($project->fresh(), $operator, 40, 'Kemajuan awal');

            $expenseSvc = app(\App\Services\Project\ProjectExpenseService::class);
            $expenseVerify = app(\App\Services\Project\ProjectExpenseVerificationService::class);
            $refundSvc = app(\App\Services\Project\ProjectRefundService::class);
            $refundVerify = app(\App\Services\Project\ProjectRefundVerificationService::class);

            // (a) Perbelanjaan DISAHKAN + bukti — RM15,000.
            $expense = $expenseSvc->createDraft($project->fresh(), $expMaker, [
                'expense_date' => now()->toDateString(),
                'reference_number' => 'INV/2026/0001',
                'payee' => 'Vendor Pendidikan Sdn Bhd',
                'description' => 'Bayaran bahan pembelajaran',
                'amount' => '15000.00',
            ]);
            $this->attachDoc($expense->project_id, 'expense_evidence', \App\Enums\ProjectDocumentType::INVOICE, $expense->id, null, $expMaker->id);
            $expenseSvc->submit($expense, $expMaker);
            $expenseVerify->verify($expense->fresh(), $expChecker);

            // (b) Refund SEBAHAGIAN DISAHKAN + bukti — RM3,000 (pemulihan lebihan bayaran).
            $refund = $refundSvc->createDraft($expense->fresh(), $expMaker, [
                'refund_date' => now()->toDateString(),
                'reference_number' => 'RF/2026/0001',
                'reason' => 'Pemulangan lebihan bayaran vendor',
                'amount' => '3000.00',
            ]);
            $this->attachDoc($refund->project_id, 'refund_evidence', \App\Enums\ProjectDocumentType::BANK_SLIP, null, $refund->id, $expMaker->id);
            $refundSvc->submit($refund, $expMaker);
            $refundVerify->verify($refund->fresh(), $expChecker);

            // (c) Perbelanjaan MENUNGGU PENGESAHAN + bukti — RM5,000 (giliran checker).
            $pending = $expenseSvc->createDraft($project->fresh(), $expMaker, [
                'expense_date' => now()->toDateString(),
                'reference_number' => 'INV/2026/0002',
                'payee' => 'Percetakan Ilmu Sdn Bhd',
                'description' => 'Bayaran percetakan modul',
                'amount' => '5000.00',
            ]);
            $this->attachDoc($pending->project_id, 'expense_evidence', \App\Enums\ProjectDocumentType::RECEIPT, $pending->id, null, $expMaker->id);
            $expenseSvc->submit($pending, $expMaker);

            // (d) Refund MENUNGGU PENGESAHAN + bukti — RM1,000 (giliran checker).
            $pendingRefund = $refundSvc->createDraft($expense->fresh(), $expMaker, [
                'refund_date' => now()->toDateString(),
                'reference_number' => 'RF/2026/0002',
                'reason' => 'Pemulangan tuntutan berganda',
                'amount' => '1000.00',
            ]);
            $this->attachDoc($pendingRefund->project_id, 'refund_evidence', \App\Enums\ProjectDocumentType::CREDIT_NOTE, null, $pendingRefund->id, $expMaker->id);
            $refundSvc->submit($pendingRefund, $expMaker);
        }
    }

    /**
     * Projek pelbagai status (lewat/selesai/ditutup) untuk pelaporan bermakna.
     * Menggunakan servis sebenar supaya ledger kekal terekonsiliasi.
     */
    private function makeReportingSamples($alps, FinancialYear $year): void
    {
        // ALP-03 belum ada projek → jadikan tapak untuk contoh pelaporan.
        if (\App\Models\Project::whereHas('application', fn ($q) => $q->where('alp_id', $alps[2]->id))->exists()) {
            return;
        }

        // (a) CSR LEWAT — belanja sebahagian.
        $this->buildProject($alps[2], $year, \App\Enums\ApplicationType::CSR, 'Program Kebajikan Warga Emas', '30000.00', 'delayed', '12000.00', 150);
        // (b) CSR SELESAI — laporan dihantar dengan penerima manfaat.
        $this->buildProject($alps[2], $year, \App\Enums\ApplicationType::CSR, 'Program Literasi Digital', '25000.00', 'completed', '20000.00', 320);
        // (c) Pembangunan DITUTUP — kitaran penuh + bukti penutupan.
        $this->buildProject($alps[0], $year, \App\Enums\ApplicationType::DEVELOPMENT, 'Naik Taraf Padang Permainan', '50000.00', 'closed', '46000.00', null);
    }

    private function buildProject(Alp $alp, FinancialYear $year, \App\Enums\ApplicationType $type, string $title, string $amount, string $targetStatus, string $expenseAmount, ?int $beneficiaries): void
    {
        $numbers = app(\App\Services\Application\ApplicationNumberGenerator::class);
        $budget = app(\App\Services\Budget\BudgetService::class);
        $operator = User::where('email', 'urussetia@dbkl.test')->first();
        $expMaker = User::where('email', 'kewangan@dbkl.test')->first();
        $expChecker = User::where('email', 'pelulus@dbkl.test')->first();

        $project = \Illuminate\Support\Facades\DB::transaction(function () use ($alp, $year, $type, $title, $amount, $numbers, $budget) {
            $app = \App\Models\Application::create([
                'application_number' => $numbers->next($type, $year->year),
                'financial_year_id' => $year->id, 'alp_id' => $alp->id, 'application_type' => $type,
                'purpose' => $title,
                'recipient_name' => 'Persatuan '.$title,
                'recipient_bank_account' => '10000000'.str_pad((string) $alp->id, 2, '0', STR_PAD_LEFT),
                'requested_amount' => $amount,
                'status' => \App\Enums\ApplicationStatus::APPROVED, 'submitted_at' => now()->subDays(20),
            ]);
            \App\Models\ApplicationStatusHistory::create(['application_id' => $app->id, 'from_status' => \App\Enums\ApplicationStatus::PENDING_APPROVAL->value, 'to_status' => \App\Enums\ApplicationStatus::APPROVED->value, 'remarks' => 'Diluluskan (dev)', 'created_at' => now()]);

            $project = app(\App\Services\Project\ProjectCreationService::class)->createFromApproved($app->fresh());
            $allocation = \App\Models\Allocation::where('alp_id', $alp->id)->where('financial_year_id', $year->id)->first();
            $budget->recordCommitment($allocation, \App\Support\Money::of($amount), $app, null, $project);

            return $project;
        });

        // Mula projek + perbelanjaan disahkan (dengan bukti).
        app(\App\Services\Project\ProjectService::class)->start($project, $operator);
        $expense = app(\App\Services\Project\ProjectExpenseService::class)->createDraft($project->fresh(), $expMaker, [
            'expense_date' => now()->subDays(10)->toDateString(), 'reference_number' => 'INV/'.$project->id.'/01',
            'payee' => 'Vendor Contoh', 'description' => 'Bayaran pelaksanaan', 'amount' => $expenseAmount,
        ]);
        $this->attachDoc($expense->project_id, 'expense_evidence', \App\Enums\ProjectDocumentType::INVOICE, $expense->id, null, $expMaker->id);
        app(\App\Services\Project\ProjectExpenseService::class)->submit($expense, $expMaker);
        app(\App\Services\Project\ProjectExpenseVerificationService::class)->verify($expense->fresh(), $expChecker);

        if ($targetStatus === 'delayed') {
            app(\App\Services\Project\ProjectService::class)->updateProgress($project->fresh(), $operator, 45, 'Kemajuan lewat', \App\Enums\ProjectStatus::DELAYED);

            return;
        }

        // completed / closed → 100% + selesai + laporan akhir.
        app(\App\Services\Project\ProjectService::class)->updateProgress($project->fresh(), $operator, 100, 'Siap');
        app(\App\Services\Project\ProjectService::class)->complete($project->fresh(), $operator);
        app(\App\Services\Project\ProjectReportService::class)->submit($project->fresh(), $operator, [
            'summary' => 'Projek disiapkan dengan jayanya.', 'outcome' => 'Impak positif kepada komuniti.',
            'beneficiary_count' => $beneficiaries,
        ]);

        if ($targetStatus === 'closed') {
            // Bukti penutupan wajib mengikut jenis projek.
            $this->attachDoc($project->id, 'closure_evidence', \App\Enums\ProjectDocumentType::FINAL_REPORT, null, null, $operator->id);
            $this->attachDoc($project->id, 'closure_evidence', \App\Enums\ProjectDocumentType::COMPLETION_PHOTO, null, null, $operator->id);
            if ($type === \App\Enums\ApplicationType::DEVELOPMENT) {
                $this->attachDoc($project->id, 'closure_evidence', \App\Enums\ProjectDocumentType::COMPLETION_CERTIFICATE, null, null, $operator->id);
            }
            app(\App\Services\Project\ProjectClosureService::class)->close($project->fresh(), $expChecker);
        }
    }

    /**
     * Cipta rekod dokumen bukti + fail placeholder pada storan peribadi (DEV DATA).
     */
    private function attachDoc(int $projectId, string $category, \App\Enums\ProjectDocumentType $type, ?int $expenseId, ?int $refundId, int $uploaderId): void
    {
        $storedPath = "projects/{$projectId}/{$category}/dev-".uniqid().'.pdf';
        $content = "%PDF-1.4\n% Dokumen placeholder DEV — {$type->value}\n";
        \Illuminate\Support\Facades\Storage::disk('local')->put($storedPath, $content);

        \App\Models\ProjectDocument::create([
            'project_id' => $projectId,
            'project_expense_id' => $expenseId,
            'project_expense_refund_id' => $refundId,
            'category' => $category,
            'document_type' => $type,
            'original_filename' => $type->value.'.pdf',
            'stored_path' => $storedPath,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($content),
            'sha256' => hash('sha256', $content),
            'uploaded_by' => $uploaderId,
        ]);
    }

    /**
     * Cipta permohonan contoh: satu DRAFT dan satu SUBMITTED (dibina terus untuk
     * demo dashboard — DEV DATA sahaja). Permohonan SUBMITTED menjadi Pending Request.
     */
    private function makeSampleApplications(Alp $alp, FinancialYear $year): void
    {
        if (\App\Models\Application::where('alp_id', $alp->id)->exists()) {
            return;
        }

        $numbers = app(\App\Services\Application\ApplicationNumberGenerator::class);

        // DRAFT — Pembangunan.
        \Illuminate\Support\Facades\DB::transaction(function () use ($alp, $year, $numbers) {
            $draft = \App\Models\Application::create([
                'application_number' => $numbers->next(\App\Enums\ApplicationType::DEVELOPMENT, $year->year),
                'financial_year_id' => $year->id,
                'alp_id' => $alp->id,
                'application_type' => \App\Enums\ApplicationType::DEVELOPMENT,
                'purpose' => 'Naik Taraf Dewan Komuniti (Draf)',
                'recipient_name' => 'Persatuan Penduduk PPR Seri Murni',
                'recipient_bank_account' => '1000000099',
                'requested_amount' => '60000.00',
                'status' => \App\Enums\ApplicationStatus::DRAFT,
            ]);
        });

        // SUBMITTED — CSR (menjadi Pending Request RM85,000).
        \Illuminate\Support\Facades\DB::transaction(function () use ($alp, $year, $numbers) {
            $app = \App\Models\Application::create([
                'application_number' => $numbers->next(\App\Enums\ApplicationType::CSR, $year->year),
                'financial_year_id' => $year->id,
                'alp_id' => $alp->id,
                'application_type' => \App\Enums\ApplicationType::CSR,
                'purpose' => 'Program Komuniti PPR Seri Murni',
                'recipient_name' => 'Persatuan Penduduk PPR Seri Murni',
                'recipient_bank_account' => '1000000098',
                'requested_amount' => '85000.00',
                'status' => \App\Enums\ApplicationStatus::SUBMITTED,
                'submitted_at' => now(),
            ]);
            \App\Models\ApplicationStatusHistory::create([
                'application_id' => $app->id,
                'from_status' => \App\Enums\ApplicationStatus::DRAFT->value,
                'to_status' => \App\Enums\ApplicationStatus::SUBMITTED->value,
                'remarks' => 'Permohonan dihantar (data pembangunan)',
                'created_at' => now(),
            ]);
        });
    }

    private function makeUser(string $name, string $email, RoleName $role, ?Alp $alp = null): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::DEV_PASSWORD),
                'status' => UserStatus::ACTIVE,
                'must_change_password' => false, // Dilonggarkan untuk kemudahan pembangunan.
                'alp_id' => $alp?->id,
                'unit' => $alp ? null : 'DBKL',
            ]
        );

        $user->syncRoles([$role->value]);

        return $user;
    }
}

<?php

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Enums\ApprovalDecision;
use App\Enums\BudgetTransactionType;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\BudgetTransaction;
use App\Services\Application\ApprovalMatrixService;
use Database\Seeders\ApprovalLevelSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * URS v1.2: Peraku (angkat ke PEPU) → PEPU (kelulusan akhir) untuk semua jumlah.
 * Pulihkan permohonan yang tersilap diluluskan selepas aras Peraku sahaja.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new ApprovalLevelSeeder)->run();

        $matrix = app(ApprovalMatrixService::class);

        Application::query()
            ->where('status', ApplicationStatus::APPROVED->value)
            ->with('approvals')
            ->each(function (Application $application) use ($matrix) {
                $required = $matrix->requiredLevels(
                    $application->requestedAmountMoney(),
                    $application->financial_year_id,
                )->count();

                $approved = $application->approvals
                    ->where('decision', ApprovalDecision::APPROVED)
                    ->where('revision_number', $application->revision_number)
                    ->count();

                if ($approved >= $required) {
                    return;
                }

                DB::transaction(function () use ($application) {
                    BudgetTransaction::query()
                        ->where('application_id', $application->id)
                        ->where('type', BudgetTransactionType::COMMITMENT->value)
                        ->delete();

                    ApplicationStatusHistory::create([
                        'application_id' => $application->id,
                        'from_status' => ApplicationStatus::APPROVED->value,
                        'to_status' => ApplicationStatus::PENDING_APPROVAL->value,
                        'changed_by' => null,
                        'remarks' => 'Pemulihan aliran: menunggu kelulusan PEPU selepas perakuan TP/Pengarah JP',
                        'created_at' => now(),
                    ]);

                    $application->update([
                        'status' => ApplicationStatus::PENDING_APPROVAL,
                        'payment_status' => ApplicationPaymentStatus::PENDING_PAYMENT,
                    ]);
                });
            });
    }

    public function down(): void
    {
        // Tiada rollback automatik.
    }
};

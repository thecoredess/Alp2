<?php

namespace Tests\Concerns;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Enums\RoleName;
use App\Enums\ProgramCategory;
use App\Models\Alp;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentRequirement;
use App\Models\FinancialYear;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Application\ApplicationReviewService;
use App\Services\Application\ApplicationSubmissionService;
use App\Services\Budget\BudgetService;
use App\Support\UrsContributionPolicy;
use Database\Seeders\ApprovalLevelSeeder;
use Database\Seeders\DocumentRequirementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\WorkflowSettingsSeeder;

trait BuildsWorkflow
{
    protected function seedWorkflow(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DocumentRequirementSeeder::class);
        $this->seed(WorkflowSettingsSeeder::class);
        $this->seed(ApprovalLevelSeeder::class);

        // Ujian aliran/matrix boleh guna jumlah > BR-005; ujian Urs* aktifkan semula secara eksplisit.
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, false);
    }

    /** Medan penerima URS (BR-008/009) — unik setiap draf supaya assert ROS lulus. */
    protected function ursRecipientAttrs(): array
    {
        return [
            'recipient_name' => 'Persatuan Ujian '.fake()->unique()->numerify('###'),
            'recipient_ros_number' => 'ROS-'.fake()->unique()->numerify('########'),
            'recipient_bank_account' => fake()->numerify('##########'),
            'recipient_address' => 'Kampung Baru, Kuala Lumpur',
            'location' => 'Kuala Lumpur',
            'program_category' => ProgramCategory::KOMUNITI,
            'proposed_start_date' => now()->addMonths(3)->toDateString(),
            'compliance_declared_at' => now(),
        ];
    }

    protected function makeYear(): FinancialYear
    {
        return FinancialYear::factory()->active()->create();
    }

    protected function userWithRole(string $role, ?Alp $alp = null): User
    {
        return User::factory()->create(['alp_id' => $alp?->id])->assignRole($role);
    }

    protected function allocate(Alp $alp, FinancialYear $year, string $amount): void
    {
        app(BudgetService::class)->allocate($alp, $year, $amount, 'REF');
    }

    protected function draftWithBudgetAndDocs(Alp $alp, FinancialYear $year, string $amount, ApplicationType $type = ApplicationType::CSR): Application
    {
        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'application_type' => $type,
            'status' => ApplicationStatus::DRAFT,
            ...$this->ursRecipientAttrs(),
        ]);
        $app->budgetItems()->create(['description' => 'Item', 'quantity' => 1, 'unit_cost' => $amount, 'total' => $amount, 'sort_order' => 1]);
        $app->recalculateRequestedAmount();

        foreach (DocumentRequirement::requiredFor($type) as $docType) {
            ApplicationDocument::factory()->type($docType)->create(['application_id' => $app->id]);
        }

        return $app->fresh();
    }

    protected function submitted(Alp $alp, FinancialYear $year, string $amount, ApplicationType $type = ApplicationType::CSR): Application
    {
        $app = $this->draftWithBudgetAndDocs($alp, $year, $amount, $type);

        return app(ApplicationSubmissionService::class)->submit($app, $this->userWithRole(RoleName::ALP->value, $alp));
    }

    /**
     * Cipta permohonan terus pada status SUBMITTED tanpa semakan penghantaran
     * (untuk mensimulasikan keadaan luar biasa/bersejarah yang menguji perlindungan Fasa 4).
     */
    protected function submittedDirect(Alp $alp, FinancialYear $year, string $amount, ApplicationType $type = ApplicationType::CSR): Application
    {
        $app = Application::factory()->submitted()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'application_type' => $type,
            ...$this->ursRecipientAttrs(),
        ]);
        $app->budgetItems()->create(['description' => 'Item', 'quantity' => 1, 'unit_cost' => $amount, 'total' => $amount, 'sort_order' => 1]);
        $app->recalculateRequestedAmount();

        foreach (\App\Models\DocumentRequirement::requiredFor($type) as $docType) {
            ApplicationDocument::factory()->type($docType)->create(['application_id' => $app->id]);
        }

        return $app->fresh();
    }

    /** Senarai semak JP semua lengkap (UR-M04-001) — untuk majukan ke perakuan dalam ujian. */
    protected function lengkapChecklist(): array
    {
        return collect(\App\Support\JpReviewChecklist::keys())
            ->mapWithKeys(fn (string $k) => [$k => \App\Support\JpReviewChecklist::LENGKAP])
            ->all();
    }

    /** Majukan permohonan yang telah dihantar hingga PENDING_APPROVAL (URS v1.2: JP sahaja). */
    protected function toPendingApproval(Application $app): Application
    {
        $reviews = app(ApplicationReviewService::class);

        $reviews->review(
            $app->fresh(),
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
            ReviewDecision::RECOMMEND,
            null,
            $this->lengkapChecklist(),
        );

        return $app->fresh();
    }
}

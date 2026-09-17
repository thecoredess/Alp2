<?php

namespace Tests\Concerns;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\DocumentType;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentRequirement;
use App\Models\FinancialYear;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Application\ApplicationReviewService;
use App\Services\Application\ApplicationSubmissionService;
use App\Services\Application\ApprovalService;
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

        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, false);
    }

    protected function ursRecipientAttrs(): array
    {
        return [
            'recipient_name' => 'Persatuan Ujian '.fake()->unique()->numerify('###'),
            'recipient_ros_number' => 'ROS-'.fake()->unique()->numerify('########'),
            'program_date' => now()->addMonths(3)->toDateString(),
            'program_category' => 'komuniti',
            'recipient_bank_account' => fake()->numerify('##########'),
            'recipient_address' => 'No. 1, Jalan Raja Laut, 50350 Kuala Lumpur',
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

    protected function draftWithBudgetAndDocs(Alp $alp, FinancialYear $year, string $amount, ApplicationType $type = ApplicationType::SUMBANGAN): Application
    {
        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'application_type' => $type,
            'status' => ApplicationStatus::DRAFT,
            'requested_amount' => $amount,
            'purpose' => 'Tujuan ujian sumbangan',
            ...$this->ursRecipientAttrs(),
        ]);

        foreach (DocumentRequirement::requiredFor() as $docType) {
            ApplicationDocument::factory()->type($docType)->create(['application_id' => $app->id]);
        }

        ApplicationDocument::factory()->type(DocumentType::SEMAKAN_SILANG_JKEW)->create(['application_id' => $app->id]);

        return $app->fresh();
    }

    protected function submitted(Alp $alp, FinancialYear $year, string $amount, ApplicationType $type = ApplicationType::SUMBANGAN): Application
    {
        $app = $this->draftWithBudgetAndDocs($alp, $year, $amount, $type);

        return app(ApplicationSubmissionService::class)->submit($app, $this->userWithRole(RoleName::ALP->value, $alp));
    }

    protected function submittedDirect(Alp $alp, FinancialYear $year, string $amount, ApplicationType $type = ApplicationType::SUMBANGAN): Application
    {
        $app = Application::factory()->submitted()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'application_type' => $type,
            'requested_amount' => $amount,
            'purpose' => 'Tujuan ujian sumbangan',
            ...$this->ursRecipientAttrs(),
        ]);

        foreach (DocumentRequirement::requiredFor() as $docType) {
            ApplicationDocument::factory()->type($docType)->create(['application_id' => $app->id]);
        }

        ApplicationDocument::factory()->type(DocumentType::SEMAKAN_SILANG_JKEW)->create(['application_id' => $app->id]);

        return $app->fresh();
    }

    protected function lengkapChecklist(): array
    {
        return collect(\App\Support\JpReviewChecklist::keys())
            ->mapWithKeys(fn (string $k) => [$k => \App\Support\JpReviewChecklist::LENGKAP])
            ->all();
    }

    protected function toPendingApproval(Application $app): Application
    {
        $reviews = app(ApplicationReviewService::class);

        // Admin JP → Pegawai JP → Pengarah.
        $reviews->review(
            $app->fresh(),
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::SYSTEM_ADMIN->value),
            ReviewDecision::RECOMMEND,
            'Disyorkan Admin JP',
            $this->lengkapChecklist(),
        );

        $reviews->review(
            $app->fresh(),
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
            ReviewDecision::RECOMMEND,
            'Syor kepada Pengarah JP',
            $this->lengkapChecklist(),
        );

        return $app->fresh();
    }

    /** Hanya semakan Admin JP (status → UNDER_SECRETARIAT_REVIEW). */
    protected function afterAdminJpReview(Application $app): Application
    {
        app(ApplicationReviewService::class)->review(
            $app->fresh(),
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::SYSTEM_ADMIN->value),
            ReviewDecision::RECOMMEND,
            'Disyorkan Admin JP',
            $this->lengkapChecklist(),
        );

        return $app->fresh();
    }

    /** Luluskan kedua-dua aras: Peraku → PEPU. */
    protected function fullyApprove(Application $app): Application
    {
        $approvals = app(ApprovalService::class);
        $approvals->approve($app->fresh(), $this->userWithRole(RoleName::PELULUS->value), null);
        $approvals->approve($app->fresh(), $this->userWithRole(RoleName::PENGURUSAN->value), null);

        return $app->fresh();
    }
}

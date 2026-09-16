<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Application;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use App\Enums\ApplicationStatus;
use App\Support\MailSettings;
use App\Support\NotificationTemplates;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class EmailSettingsTest extends TestCase
{
    use BuildsWorkflow, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        NotificationTemplates::seedDefaults();
    }

    public function test_super_admin_can_open_mail_settings(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $this->actingAs($super)
            ->get(route('settings.mail.edit'))
            ->assertOk()
            ->assertSee('Tetapan E-mel SMTP');
    }

    public function test_admin_jp_cannot_open_mail_settings(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('settings.mail.edit'))
            ->assertForbidden();
    }

    public function test_admin_jp_can_edit_notification_templates(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('settings.notification-templates.edit', ['peranan' => RoleName::ALP->value]))
            ->assertOk()
            ->assertSee('Templat Notifikasi E-mel');
    }

    public function test_notification_skips_mail_when_smtp_disabled(): void
    {
        SystemSetting::set(MailSettings::KEY_ENABLED, false);

        $user = User::factory()->create()->assignRole(RoleName::ALP->value);
        $app = Application::factory()->create();
        $notification = new ApplicationWorkflowNotification($app, 'approved', 'Test message');

        $this->assertSame(['database'], $notification->via($user));
    }

    public function test_notification_includes_mail_when_smtp_and_template_enabled(): void
    {
        SystemSetting::set(MailSettings::KEY_ENABLED, true);
        SystemSetting::set(NotificationTemplates::KEY_MAIL_GLOBALLY_ENABLED, true);

        $user = User::factory()->create()->assignRole(RoleName::ALP->value);
        $app = Application::factory()->create();
        $notification = new ApplicationWorkflowNotification($app, 'approved', 'Test message');

        $this->assertSame(['database', 'mail'], $notification->via($user));
    }

    public function test_custom_template_subject_is_used_in_mail(): void
    {
        SystemSetting::set(MailSettings::KEY_ENABLED, true);
        SystemSetting::set(
            NotificationTemplates::eventRoleSubjectKey('approved', RoleName::ALP->value),
            'Kelulusan ALP {application_number}',
        );

        $user = User::factory()->create()->assignRole(RoleName::ALP->value);
        $app = Application::factory()->create();
        $notification = new ApplicationWorkflowNotification($app, 'approved', 'Test message');
        $mail = $notification->toMail($user);

        $this->assertStringContainsString($app->application_number, (string) $mail->subject);
        $this->assertStringContainsString('Kelulusan ALP', (string) $mail->subject);
    }

    public function test_action_mail_links_to_review_form_for_pegawai_jp(): void
    {
        $this->seedWorkflow();
        SystemSetting::set(MailSettings::KEY_ENABLED, true);

        $alp = \App\Models\Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->afterAdminJpReview($this->submitted($alp, $year, '2500.00'));
        $app->update(['status' => ApplicationStatus::UNDER_SECRETARIAT_REVIEW]);

        $jp = User::factory()->create()->assignRole(RoleName::PEGAWAI_URUSSETIA->value);
        $notification = new ApplicationWorkflowNotification($app, 'awaiting_pegawai_jp', 'Menunggu pengesyoran.');
        $mail = $notification->toMail($jp);

        $this->assertStringContainsString('Tindakan diperlukan', (string) $mail->subject);
        $this->assertStringContainsString(route('reviews.show', [$app, 'secretariat'], false), (string) $mail->render());
    }

    public function test_verify_peer_can_be_disabled_in_mail_settings(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $payload = [
            'mail_enabled' => '1',
            'mailer' => 'smtp',
            'host' => 'smtp.internal.test',
            'port' => '587',
            'encryption' => 'tls',
            'from_address' => 'alp@dbkl.test',
            'from_name' => 'Sistem ALP',
        ];

        $this->actingAs($super)
            ->put(route('settings.mail.update'), $payload + ['verify_peer' => '1'])
            ->assertRedirect(route('settings.mail.edit'));

        MailSettings::applyToConfig();
        $this->assertTrue(config('mail.mailers.smtp.verify_peer'));

        $this->actingAs($super)
            ->put(route('settings.mail.update'), $payload)
            ->assertRedirect(route('settings.mail.edit'));

        MailSettings::applyToConfig();
        $this->assertFalse(config('mail.mailers.smtp.verify_peer'));
    }

    public function test_submission_still_notifies_when_mail_disabled(): void
    {
        Notification::fake();
        SystemSetting::set(MailSettings::KEY_ENABLED, false);

        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        $app = Application::factory()->submitted()->create();

        Notification::send(
            collect([$admin]),
            new ApplicationWorkflowNotification($app, 'submitted', 'Permohonan baharu.'),
        );

        Notification::assertSentTo($admin, ApplicationWorkflowNotification::class);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Models\SystemSetting;
use App\Services\Audit\AuditService;
use App\Support\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MailSettingController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()->hasRole(RoleName::SUPER_ADMIN->value), 403);

        return view('admin.settings.mail', [
            'settings' => MailSettings::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole(RoleName::SUPER_ADMIN->value), 403);

        $data = $request->validate([
            'mail_enabled' => ['nullable', 'boolean'],
            'mailer' => ['required', 'string', 'in:smtp,log,sendmail'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'verify_peer' => ['nullable', 'boolean'],
        ]);

        SystemSetting::set(MailSettings::KEY_ENABLED, $request->boolean('mail_enabled'));
        SystemSetting::set(MailSettings::KEY_MAILER, $data['mailer']);
        SystemSetting::set(MailSettings::KEY_HOST, $data['host']);
        SystemSetting::set(MailSettings::KEY_PORT, (string) $data['port']);
        SystemSetting::set(MailSettings::KEY_USERNAME, $data['username'] ?? '');
        SystemSetting::set(MailSettings::KEY_ENCRYPTION, $data['encryption'] === 'none' ? '' : ($data['encryption'] ?? ''));
        SystemSetting::set(MailSettings::KEY_FROM_ADDRESS, $data['from_address']);
        SystemSetting::set(MailSettings::KEY_FROM_NAME, $data['from_name']);
        SystemSetting::set(MailSettings::KEY_VERIFY_PEER, $request->boolean('verify_peer'));

        if (filled($data['password'] ?? null)) {
            MailSettings::storePassword($data['password']);
        }

        $this->audit->log('SETTINGS_MAIL_UPDATED', null, null, [
            'mailer' => $data['mailer'],
            'host' => $data['host'],
            'port' => $data['port'],
            'username' => $data['username'] ?? '',
            'encryption' => $data['encryption'] ?? '',
            'from_address' => $data['from_address'],
            'from_name' => $data['from_name'],
            'mail_enabled' => $request->boolean('mail_enabled'),
            'verify_peer' => $request->boolean('verify_peer'),
            'password_changed' => filled($data['password'] ?? null),
        ]);

        return redirect()
            ->route('settings.mail.edit')
            ->with('status', 'Tetapan e-mel SMTP dikemas kini.');
    }

    public function test(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole(RoleName::SUPER_ADMIN->value), 403);

        $data = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $recipient = $data['test_email'];

        // Ujian SMTP sentiasa guna tetapan DB walaupun toggle penghantaran dimatikan.
        MailSettings::applyToConfig(force: true);
        Mail::purge((string) config('mail.default'));

        try {
            Mail::raw(
                'Ini ujian e-mel dari Sistem ALP DBKL. Jika anda menerima mesej ini, konfigurasi SMTP berjaya.',
                fn ($message) => $message
                    ->to($recipient)
                    ->subject('[ALP] Ujian E-mel SMTP'),
            );
        } catch (\Throwable $e) {
            return redirect()
                ->route('settings.mail.edit')
                ->withInput()
                ->with('error', 'Gagal hantar e-mel ujian: '.$e->getMessage());
        }

        $this->audit->log('SETTINGS_MAIL_TEST_SENT', null, null, [
            'recipient' => $recipient,
        ]);

        return redirect()
            ->route('settings.mail.edit')
            ->with('status', 'E-mel ujian dihantar ke '.$recipient.'.');
    }
}

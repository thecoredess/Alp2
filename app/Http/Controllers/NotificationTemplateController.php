<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\Audit\AuditService;
use App\Support\NotificationTemplates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationTemplateController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $selectedRole = (string) $request->query('peranan', array_key_first(NotificationTemplates::roles()));

        if (! array_key_exists($selectedRole, NotificationTemplates::roles())) {
            $selectedRole = array_key_first(NotificationTemplates::roles());
        }

        return view('admin.settings.notification-templates', [
            'roles' => NotificationTemplates::roles(),
            'events' => NotificationTemplates::events(),
            'selectedRole' => $selectedRole,
            'roleEvents' => NotificationTemplates::eventsForRole($selectedRole),
            'templates' => NotificationTemplates::matrix()[$selectedRole] ?? [],
            'mailGloballyEnabled' => NotificationTemplates::mailGloballyEnabled(),
            'roleMailEnabled' => NotificationTemplates::roleMailEnabled($selectedRole),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $role = (string) $request->input('role');
        abort_unless(array_key_exists($role, NotificationTemplates::roles()), 422);

        $events = NotificationTemplates::eventsForRole($role);

        $rules = [
            'role' => ['required', 'string'],
            'notification_mail_globally_enabled' => ['nullable', 'boolean'],
            'role_mail_enabled' => ['nullable', 'boolean'],
        ];

        foreach ($events as $event) {
            $rules['templates.'.$event.'.subject'] = ['required', 'string', 'max:255'];
            $rules['templates.'.$event.'.body'] = ['required', 'string', 'max:5000'];
            $rules['templates.'.$event.'.mail_enabled'] = ['nullable', 'boolean'];
        }

        $data = $request->validate($rules);

        SystemSetting::set(
            NotificationTemplates::KEY_MAIL_GLOBALLY_ENABLED,
            $request->boolean('notification_mail_globally_enabled'),
        );
        SystemSetting::set(
            NotificationTemplates::roleMailKey($role),
            $request->boolean('role_mail_enabled'),
        );

        foreach ($events as $event) {
            $row = $data['templates'][$event] ?? [];
            SystemSetting::set(
                NotificationTemplates::eventRoleSubjectKey($event, $role),
                $row['subject'] ?? NotificationTemplates::defaultSubject($event),
            );
            SystemSetting::set(
                NotificationTemplates::eventRoleBodyKey($event, $role),
                $row['body'] ?? NotificationTemplates::defaultBody($event),
            );
            SystemSetting::set(
                NotificationTemplates::eventRoleMailKey($event, $role),
                (bool) ($row['mail_enabled'] ?? true),
            );
        }

        $this->audit->log('SETTINGS_NOTIFICATION_TEMPLATES_UPDATED', null, null, [
            'role' => $role,
            'events_updated' => count($events),
            'mail_globally_enabled' => $request->boolean('notification_mail_globally_enabled'),
            'role_mail_enabled' => $request->boolean('role_mail_enabled'),
        ]);

        return redirect()
            ->route('settings.notification-templates.edit', ['peranan' => $role])
            ->with('status', 'Templat notifikasi e-mel dikemas kini.');
    }
}

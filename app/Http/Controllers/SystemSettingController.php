<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Support\UrsContributionPolicy;
use App\Support\UrsDocumentTemplates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        return view('admin.settings.edit', [
            'enabled' => UrsContributionPolicy::enabled(),
            'maxAnnual' => UrsContributionPolicy::maxAnnualAllocation()->value(),
            'maxPerApp' => UrsContributionPolicy::maxPerApplication()->value(),
            'periodQuota' => UrsContributionPolicy::maxPeriodQuota()->value(),
            'overdueDays' => UrsContributionPolicy::overdueDays(),
            'templates' => UrsDocumentTemplates::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'urs_policy_enabled' => ['nullable', 'boolean'],
            'urs_max_annual_allocation' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'urs_max_per_application' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'urs_period_quota' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'urs_overdue_days' => ['required', 'integer', 'min:1', 'max:365'],
            'template_letter_header' => ['required', 'string', 'max:255'],
            'template_letter_body' => ['required', 'string', 'max:5000'],
            'template_letter_footer' => ['required', 'string', 'max:2000'],
            'template_borang_header' => ['required', 'string', 'max:255'],
            'template_borang_footer' => ['required', 'string', 'max:2000'],
        ]);

        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, $request->boolean('urs_policy_enabled'));
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_ANNUAL, number_format((float) $data['urs_max_annual_allocation'], 2, '.', ''));
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_PER_APPLICATION, number_format((float) $data['urs_max_per_application'], 2, '.', ''));
        SystemSetting::set(UrsContributionPolicy::KEY_PERIOD_QUOTA, number_format((float) $data['urs_period_quota'], 2, '.', ''));
        SystemSetting::set(UrsContributionPolicy::KEY_OVERDUE_DAYS, (string) (int) $data['urs_overdue_days']);

        SystemSetting::set(UrsDocumentTemplates::KEY_LETTER_HEADER, $data['template_letter_header']);
        SystemSetting::set(UrsDocumentTemplates::KEY_LETTER_BODY, $data['template_letter_body']);
        SystemSetting::set(UrsDocumentTemplates::KEY_LETTER_FOOTER, $data['template_letter_footer']);
        SystemSetting::set(UrsDocumentTemplates::KEY_BORANG_HEADER, $data['template_borang_header']);
        SystemSetting::set(UrsDocumentTemplates::KEY_BORANG_FOOTER, $data['template_borang_footer']);

        return redirect()->route('settings.edit')->with('status', 'Tetapan polisi URS & templat dokumen dikemas kini.');
    }
}

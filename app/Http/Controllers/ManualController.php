<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Support\OfficialManual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** M11 — Manual pengguna / kit tatacara (UR-M11). */
class ManualController extends Controller
{
    public function show(Request $request): View
    {
        $role = RoleName::tryFrom($request->user()->roles->first()?->name ?? '');

        [$audience, $audienceLabel] = match ($role) {
            RoleName::ALP, RoleName::URUSSETIA_ALP => ['alp', 'ALP / Persatuan'],
            RoleName::PEGAWAI_URUSSETIA => ['jp', RoleName::PEGAWAI_URUSSETIA->label()],
            RoleName::PELULUS => ['jp', RoleName::PELULUS->label()],
            RoleName::PENGURUSAN => ['jp', RoleName::PENGURUSAN->label()],
            RoleName::PEGAWAI_KEWANGAN => ['jp', RoleName::PEGAWAI_KEWANGAN->label()],
            RoleName::PEGAWAI_JKEW => ['jp', RoleName::PEGAWAI_JKEW->label()],
            RoleName::SYSTEM_ADMIN, RoleName::SUPER_ADMIN => ['jp', RoleName::SYSTEM_ADMIN->label()],
            default => ['umum', 'Umum'],
        };

        return view('manual.show', [
            'audience' => $audience,
            'audienceLabel' => $audienceLabel,
            'hasOfficialPdf' => OfficialManual::exists(),
            'canManageManual' => $request->user()->can('settings.manage'),
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('dashboard.view'), 403);
        abort_unless(OfficialManual::exists(), 404, 'Manual PDF rasmi belum dimuat naik.');

        return Storage::disk('local')->download(
            OfficialManual::path(),
            'Manual-Sistem-ALP-DBKL.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function upload(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $request->validate([
            'manual_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'manual_pdf.required' => 'Sila pilih fail PDF manual.',
            'manual_pdf.mimes' => 'Manual rasmi mestilah fail PDF.',
        ]);

        OfficialManual::storeUploaded($request->file('manual_pdf'));

        return back()->with('status', 'Manual PDF rasmi dimuat naik (M11).');
    }

    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);
        OfficialManual::clear();

        return back()->with('status', 'Manual PDF rasmi dibuang.');
    }
}

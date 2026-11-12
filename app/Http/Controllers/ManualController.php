<?php

namespace App\Http\Controllers;

use App\Support\OfficialManual;
use App\Support\RoleUserManual;
use App\Support\RoleUserManualScreenshots;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** M11 — Manual pengguna / kit tatacara (UR-M11). */
class ManualController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $roleManual = RoleUserManual::forUser($user);

        return view('manual.show', [
            'audience' => RoleUserManual::audienceKey($user),
            'audienceLabel' => RoleUserManual::audienceLabel($user),
            'roleManual' => $roleManual,
            'hasRoleManualDocx' => $roleManual !== null && $roleManual->exists(),
            'manualFiguresByFile' => RoleUserManualScreenshots::visibleFiguresKeyedFor($roleManual),
            'hasOfficialPdf' => OfficialManual::exists(),
            'canManageManual' => $user->can('settings.manage'),
        ]);
    }

    /** Tangkapan skrin manual pengguna (mengikut peranan). */
    public function screenshot(Request $request, string $name): BinaryFileResponse
    {
        abort_unless($request->user()->can('dashboard.view'), 403);

        $manual = RoleUserManual::forUser($request->user());
        abort_if($manual === null, 404);
        abort_unless(RoleUserManualScreenshots::isAllowedForManual($name, $manual), 404);

        $path = RoleUserManualScreenshots::absolutePath($name);
        abort_if($path === null, 404);

        return response()->file($path, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=3600']);
    }

    /** Muat turun Manual Pengguna Word mengikut peranan. */
    public function downloadDocx(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('dashboard.view'), 403);

        $manual = RoleUserManual::forUser($request->user());
        abort_if($manual === null || ! $manual->exists(), 404, 'Manual Word untuk peranan anda tidak dijumpai.');

        return response()->download(
            $manual->absolutePath(),
            $manual->downloadName,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        );
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

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\ApplicationWizardController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ApprovalMatrixController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AssociationGuideController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AlpController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| Laluan Tetamu (belum log masuk)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:20,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Laluan Berdaftar — Sistem Aktif URS v1.2
| Modul projek / belanja / refund / cadangan bajet / dashboard korporat
| diasingkan ke _reference_legacy (tiada route aktif).
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'password.set'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('tukar-kata-laluan', [ChangePasswordController::class, 'edit'])->name('password.change');
    Route::put('tukar-kata-laluan', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profil/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    Route::get('tetapan', fn () => redirect()->route('profile.edit'))->name('settings.index');
    Route::get('ketetapan', fn () => redirect()->route('settings.index'));

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pratonton reka bentuk dashboard analisa sumbangan (data contoh — untuk semakan sahaja).
    Route::get('dashboard/templat', [DashboardController::class, 'templates'])->name('dashboard.templates');

    // Laporan URS (M08) — tanpa laporan projek / maker-checker / CSR projek
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('laporan/kewangan/peruntukan', [ReportController::class, 'allocation'])->name('reports.allocation');
        Route::get('laporan/kewangan/ledger', [ReportController::class, 'ledger'])->name('reports.ledger');
        Route::get('laporan/permohonan', [ReportController::class, 'applications'])->name('reports.applications');
        Route::get('laporan/program', [ReportController::class, 'programs'])->name('reports.programs');
        Route::get('laporan/audit', [ReportController::class, 'auditTrail'])->name('reports.audit');
    });

    Route::get('bajet-saya', [AllocationController::class, 'myBudget'])->name('budget.mine');
    Route::get('peruntukan', [AllocationController::class, 'index'])->name('allocations.index');
    Route::get('peruntukan/cipta', [AllocationController::class, 'create'])->name('allocations.create');
    Route::post('peruntukan', [AllocationController::class, 'store'])->name('allocations.store');
    Route::get('peruntukan/{allocation}', [AllocationController::class, 'show'])->name('allocations.show');
    Route::get('peruntukan/{allocation}/laras', [AllocationController::class, 'adjustForm'])->name('allocations.adjust');
    Route::post('peruntukan/{allocation}/laras', [AllocationController::class, 'adjustStore'])->name('allocations.adjust.store');

    // Permohonan (M03)
    Route::get('permohonan', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('permohonan/semua', [ApplicationController::class, 'all'])->name('applications.all');
    Route::get('permohonan/baharu', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('permohonan', [ApplicationController::class, 'store'])->name('applications.store');

    Route::get('permohonan/{application}/borang', [ApplicationWizardController::class, 'maklumat'])->name('applications.wizard.maklumat');
    Route::put('permohonan/{application}/borang', [ApplicationWizardController::class, 'updateMaklumat'])->name('applications.wizard.maklumat.update');
    Route::get('permohonan/{application}/lampiran', [ApplicationWizardController::class, 'dokumen'])->name('applications.wizard.dokumen');
    Route::get('permohonan/{application}/semakan', [ApplicationWizardController::class, 'semakan'])->name('applications.wizard.semakan');
    Route::post('permohonan/{application}/hantar', [ApplicationWizardController::class, 'hantar'])->name('applications.submit');

    Route::post('permohonan/{application}/dokumen', [ApplicationDocumentController::class, 'store'])->name('applications.documents.store');
    Route::get('permohonan/{application}/dokumen/{document}/muat-turun', [ApplicationDocumentController::class, 'download'])->name('applications.documents.download');
    Route::get('permohonan/{application}/dokumen/{document}/lihat', [ApplicationDocumentController::class, 'view'])->name('applications.documents.view');
    Route::delete('permohonan/{application}/dokumen/{document}', [ApplicationDocumentController::class, 'destroy'])->name('applications.documents.destroy');

    // Semakan Pegawai JP (M04) — kewangan/teknikal pra-kelulusan dinyahaktif
    Route::get('semakan/urus-setia', [ReviewController::class, 'secretariat'])->name('reviews.secretariat');
    Route::get('permohonan/{application}/semak/{type}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::post('permohonan/{application}/semak/{type}', [ReviewController::class, 'store'])->name('reviews.store');

    // Kelulusan Peraku / PEPU (M05)
    Route::get('kelulusan', [ApprovalController::class, 'queue'])->name('approvals.queue');
    Route::get('permohonan/{application}/kelulusan', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('permohonan/{application}/kelulusan/lulus', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('permohonan/{application}/kelulusan/tolak', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('permohonan/{application}/kelulusan/kembali', [ApprovalController::class, 'returnForRevision'])->name('approvals.return');

    Route::get('pentadbiran/matriks-kelulusan', [ApprovalMatrixController::class, 'index'])->name('approval-matrix.index');
    Route::get('pentadbiran/matriks-kelulusan/cipta', [ApprovalMatrixController::class, 'create'])->name('approval-matrix.create');
    Route::post('pentadbiran/matriks-kelulusan', [ApprovalMatrixController::class, 'store'])->name('approval-matrix.store');
    Route::get('pentadbiran/matriks-kelulusan/{approvalLevel}/sunting', [ApprovalMatrixController::class, 'edit'])->name('approval-matrix.edit');
    Route::put('pentadbiran/matriks-kelulusan/{approvalLevel}', [ApprovalMatrixController::class, 'update'])->name('approval-matrix.update');
    Route::post('pentadbiran/matriks-kelulusan/{approvalLevel}/toggle', [ApprovalMatrixController::class, 'toggle'])->name('approval-matrix.toggle');

    Route::get('permohonan/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::get('permohonan/{application}/surat-kelulusan', [ApplicationController::class, 'letter'])->name('applications.letter');
    Route::get('permohonan/{application}/surat-kelulusan/pdf', [ApplicationController::class, 'letterPdf'])->name('applications.letter.pdf');
    Route::get('permohonan/{application}/borang-penyaluran', [ApplicationController::class, 'borang'])->name('applications.borang');
    Route::post('permohonan/{application}/report-card', [ReportCardController::class, 'store'])->name('applications.report-card.store');

    Route::get('laporan-aktiviti', [ReportCardController::class, 'index'])->name('report-cards.index');
    Route::get('laporan-aktiviti/semak', [ReportCardController::class, 'reviewQueue'])->name('report-cards.review.index');
    Route::get('permohonan/{application}/laporan-aktiviti/semak', [ReportCardController::class, 'reviewShow'])->name('report-cards.review.show');
    Route::post('permohonan/{application}/laporan-aktiviti/semak', [ReportCardController::class, 'reviewStore'])->name('report-cards.review.store');
    Route::get('manual', [ManualController::class, 'show'])->name('manual.show');
    Route::get('manual/pdf', [ManualController::class, 'download'])->name('manual.download');
    Route::get('panduan-dokumen-persatuan/pdf', [AssociationGuideController::class, 'download'])->name('association-guide.download');
    Route::post('manual/pdf', [ManualController::class, 'upload'])->name('manual.upload');
    Route::delete('manual/pdf', [ManualController::class, 'destroy'])->name('manual.destroy');

    Route::get('penerima', [RecipientController::class, 'index'])->name('recipients.index');
    Route::get('penerima/{recipient}', [RecipientController::class, 'show'])->name('recipients.show');

    Route::get('notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifikasi/{id}/baca', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Pembayaran / Baucar (M06)
    Route::get('pembayaran', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('pembayaran/eksport', [PaymentController::class, 'export'])->name('payments.export');
    Route::put('pembayaran/{application}', [PaymentController::class, 'update'])->name('payments.update');

    // Pentadbiran
    Route::get('tahun-kewangan', [FinancialYearController::class, 'index'])->name('financial-years.index');
    Route::get('tahun-kewangan/cipta', [FinancialYearController::class, 'create'])->name('financial-years.create');
    Route::post('tahun-kewangan', [FinancialYearController::class, 'store'])->name('financial-years.store');
    Route::get('tahun-kewangan/{financialYear}/sunting', [FinancialYearController::class, 'edit'])->name('financial-years.edit');
    Route::put('tahun-kewangan/{financialYear}', [FinancialYearController::class, 'update'])->name('financial-years.update');
    Route::post('tahun-kewangan/{financialYear}/buka', [FinancialYearController::class, 'open'])->name('financial-years.open');
    Route::post('tahun-kewangan/{financialYear}/aktif', [FinancialYearController::class, 'activate'])->name('financial-years.activate');
    Route::post('tahun-kewangan/{financialYear}/tutup', [FinancialYearController::class, 'close'])->name('financial-years.close');

    Route::resource('ahli-lembaga', AlpController::class)
        ->parameters(['ahli-lembaga' => 'alp'])
        ->names('alps')
        ->except(['destroy']);
    Route::post('ahli-lembaga/{alp}/nyahaktif', [AlpController::class, 'deactivate'])->name('alps.deactivate');
    Route::post('ahli-lembaga/{alp}/aktif', [AlpController::class, 'activate'])->name('alps.activate');

    Route::resource('pengguna', UserController::class)
        ->parameters(['pengguna' => 'user'])
        ->names('users')
        ->except(['destroy', 'show']);
    Route::post('pengguna/{user}/nyahaktif', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('pengguna/{user}/aktif', [UserController::class, 'activate'])->name('users.activate');
    Route::post('pengguna/{user}/reset-kata-laluan', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::get('tetapan/polisi-urs', [SystemSettingController::class, 'edit'])->name('settings.edit');
    Route::put('tetapan/polisi-urs', [SystemSettingController::class, 'update'])->name('settings.update');
});

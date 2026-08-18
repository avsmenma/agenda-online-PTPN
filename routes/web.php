<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeamVerifikasiController;
use App\Http\Controllers\DashboardPembayaranController;
use App\Http\Controllers\DashboardAkutansiController;
use App\Http\Controllers\DashboardPerpajakanController;
use App\Http\Controllers\DokumenRekapanController;
use App\Http\Controllers\DocumentHandlerController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\WelcomeMessageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\BulkOperationController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');

    // 2FA Verification Routes (accessible without auth, but requires 2fa_user_id in session)
    Route::get('/2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'showVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->middleware('throttle:5,1')->name('2fa.verify.store');
    Route::post('/2fa/verify-recovery', [\App\Http\Controllers\TwoFactorController::class, 'verifyRecoveryCode'])->middleware('throttle:5,1')->name('2fa.verify.recovery');

    // Jalan keluar untuk akun yang kehilangan authenticator SEKALIGUS recovery code.
    // Tanpa ini akun terkunci permanen: tombol pengajuan di /profile/account butuh
    // login, sedangkan programmer menolak mereset tanpa request berstatus pending.
    // Identitas pengaju dari session('2fa_user_id') — hanya terisi setelah password benar.
    Route::post('/2fa/reset-request', [\App\Http\Controllers\TwoFactorResetRequestController::class, 'storeFromVerify'])
        ->middleware('throttle:3,60')
        ->name('2fa.reset-request');
});

// GET logout - accessible without CSRF token (fixes 419 PAGE EXPIRED)
Route::get('/logout', function (Request $request) {
    if (Auth::check()) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
    return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
})->name('logout.get');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [LoginController::class, 'dashboard'])->name('dashboard');

    // 2FA Management Routes (requires authentication)
    Route::prefix('2fa')->name('2fa.')->group(function () {
        Route::get('/setup', [\App\Http\Controllers\TwoFactorController::class, 'showSetup'])->name('setup');
        Route::post('/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('enable');
        Route::get('/recovery-codes', [\App\Http\Controllers\TwoFactorController::class, 'showRecoveryCodes'])->name('recovery-codes');
        Route::post('/regenerate-recovery-codes', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate-recovery-codes');
        Route::post('/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
    });

    // Profile/Account Management Routes (requires authentication)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/account', [\App\Http\Controllers\ProfileController::class, 'showAccount'])->name('account');
        Route::post('/update-account', [\App\Http\Controllers\ProfileController::class, 'updateAccount'])->name('update-account');
        Route::post('/update-photo', [\App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('update-photo');
        Route::delete('/photo', [\App\Http\Controllers\ProfileController::class, 'deletePhoto'])->name('delete-photo');
        Route::post('/update-username', [\App\Http\Controllers\ProfileController::class, 'updateUsername'])->name('update-username');
        Route::post('/update-email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('update-email');
        Route::post('/update-password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('update-password');
        Route::post('/2fa-reset-requests', [\App\Http\Controllers\TwoFactorResetRequestController::class, 'store'])
            ->name('2fa-reset-requests.store');
    });

    // Document Preview API Routes (for all authenticated users)
    Route::prefix('api')->name('api.')->group(function () {
        // Document Preview
        Route::get('/documents/{id}/preview', [\App\Http\Controllers\Api\DocumentPreviewController::class, 'getPreviewData'])
            ->name('documents.preview');
        Route::post('/documents/{id}/quick-approve', [\App\Http\Controllers\Api\DocumentPreviewController::class, 'quickApprove'])
            ->name('documents.quick-approve');
        Route::post('/documents/{id}/quick-reject', [\App\Http\Controllers\Api\DocumentPreviewController::class, 'quickReject'])
            ->name('documents.quick-reject');

        // Advanced Search & Filters
        Route::post('/search/documents', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'search'])
            ->name('search.documents');
        Route::get('/search/filter-options', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'getFilterOptions'])
            ->name('search.filter-options');

        // Filter Presets
        Route::get('/search/presets', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'loadPresets'])
            ->name('search.presets.index');
        Route::post('/search/presets', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'savePreset'])
            ->name('search.presets.store');
        Route::post('/search/presets/{id}/use', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'usePreset'])
            ->name('search.presets.use');
        Route::delete('/search/presets/{id}', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'deletePreset'])
            ->name('search.presets.destroy');
    });
});

// Broadcasting Authentication Route
// Uses Laravel's built-in Pusher authentication (HMAC-SHA256)
// Custom broadcast auth route removed — was using insecure MD5 hash instead of Pusher protocol
Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/', function () {
    return redirect('/login');
});


Route::get('/api/welcome-message', [WelcomeMessageController::class, 'getMessage'])->middleware('auth');

// Route API check-updates (4 role) DIHAPUS 2026-07-09: pemanggil satu-satunya
// blok notifikasi popup lama di layout yang rusak (SyntaxError) & tak pernah jalan.

// Backward compatibility routes removed — old check-updates routes were redundant (Phase 2 cleanup)


// Dashboard operator lama (DashboardController@index → view operator.dashboard
// "Dokumen Terbaru") DIHAPUS 2026-07-05: halaman yatim, operator kini mendarat
// di /documents. URL /dashboard kini ditangani redirect LoginController::dashboard
// (→ getDashboardRoute per role).

// Dashboard Team Verifikasi
Route::get('dashboard/verifikasi', [TeamVerifikasiController::class, 'dashboard'])
    ->middleware('auth', 'role:team_verifikasi,verifikasi')
    ->name('dashboard.verifikasi');

Route::get('dashboard/pembayaran', [DashboardPembayaranController::class, 'dashboard'])
    ->middleware('auth', 'role:pembayaran')
    ->name('dashboard.pembayaran');

// Dashboard Team Akutansi & Perpajakan
Route::get('dashboard/akutansi', [DashboardAkutansiController::class, 'dashboard'])
    ->middleware('auth', 'role:akutansi')
  ->name('dashboard.akutansi');

Route::get('dashboard/perpajakan', [DashboardPerpajakanController::class, 'dashboard'])
    ->middleware('auth', 'role:perpajakan')
  ->name('dashboard.perpajakan');

// Backward compatibility routes removed — old dashboard URLs (Phase 2 cleanup)

// Professional API routes for rejected documents
Route::get('/api/documents/rejected/check', [DashboardController::class, 'checkRejectedDocuments'])
    ->middleware('auth', 'role:operator')
    ->name('api.documents.rejected.check');
Route::get('/api/documents/rejected/{dokumen}', [DashboardController::class, 'showRejectedDocument'])
    ->middleware('auth', 'role:operator,bagian')
    ->name('api.documents.rejected.show');
Route::get('/api/documents/verifikasi/rejected/check', [TeamVerifikasiController::class, 'checkRejectedDocuments'])
    ->middleware('auth', 'role:team_verifikasi')
    ->name('api.documents.verifikasi.rejected.check');
Route::get('/api/documents/verifikasi/rejected/{dokumen}', [TeamVerifikasiController::class, 'showRejectedDocument'])
    ->middleware('auth', 'role:team_verifikasi')
    ->name('api.documents.verifikasi.rejected.show');

// Backward compatibility routes removed — old rejected document URLs (Phase 2 cleanup)

// Owner Dashboard routes (God View)
// New Home page (main dashboard)
Route::get('owner/home', [OwnerDashboardController::class, 'home'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.home');

// Dokumen page (previously dashboard)
Route::get('owner/dokumen', [OwnerDashboardController::class, 'index'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dokumen');

// Route owner/dokumen/filter DIHAPUS 2026-07-11: nol pemanggil frontend.

// Redirect old dashboard URL to home
Route::get('owner/dashboard', fn() => redirect()->route('owner.home'))
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dashboard');

// Route owner/api/real-time-updates DIHAPUS 2026-07-11: nol pemanggil frontend.

// Recent documents API for dashboard polling
Route::get('owner/api/recent-documents', [OwnerDashboardController::class, 'getRecentDocuments'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.api.recent-documents');

// Trend chart API for period tab switching
Route::get('owner/api/trend-chart', [OwnerDashboardController::class, 'getTrendChart'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.api.trend-chart');

// (dihapus 2026-07-05, dead-code) route tracking-dokumen + owner/rekapan-keterlambatan
// (redirect duplikat) — lihat rekapan-keterlambatan.* tanpa prefix.

Route::get('rekapan-keterlambatan/{roleCode}', [OwnerDashboardController::class, 'rekapanKeterlambatanByRole'])
    ->middleware('auth')
    ->where('roleCode', 'operator|team_verifikasi|perpajakan|akutansi|pembayaran')
    ->name('rekapan-keterlambatan.role');

Route::get('rekapan-keterlambatan', function (Request $request) {
    $role = strtolower(str_replace(' ', '_', trim((string) (auth()->user()?->role ?? ''))));
    if (in_array($role, ['admin', 'owner'], true)) {
        return app(OwnerDashboardController::class)->rekapanKeterlambatan($request);
    }

    $roleCode = match ($role) {
        'verifikasi', 'tim_verifikasi', 'team_verifikasi' => 'team_verifikasi',
        'tim_perpajakan', 'team_perpajakan', 'perpajakan' => 'perpajakan',
        'akuntansi', 'tim_akuntansi', 'team_akuntansi', 'tim_akutansi', 'team_akutansi', 'akutansi' => 'akutansi',
        'tim_pembayaran', 'team_pembayaran', 'pembayaran' => 'pembayaran',
        default => 'team_verifikasi',
    };

    return redirect()->route('rekapan-keterlambatan.role', $roleCode);
})
    ->middleware('auth')
    ->name('rekapan-keterlambatan.index');

Route::get('rekapan-keterlambatan-export/{roleCode}', [OwnerDashboardController::class, 'exportRekapanKeterlambatan'])
    ->middleware('auth')
    ->where('roleCode', 'team_verifikasi|perpajakan|akutansi|pembayaran')
    ->name('rekapan-keterlambatan.export');

// (dihapus 2026-07-05, dead-code) owner/rekapan-keterlambatan/{role} + export —
// duplikat dari rekapan-keterlambatan.role & .export (tanpa prefix), tak dirujuk.

// Analytics Dashboard - merged with Rekapan Keterlambatan (redirect for backward compat)
Route::get('owner/analytics', fn() => redirect()->route('rekapan-keterlambatan.index'))
    ->middleware('auth', 'role:admin,owner')
    ->name('analytics.index');
// Route owner/analytics/data + AnalyticsController DIHAPUS 2026-07-11: nol pemanggil
// (halaman analytics hidup dilayani reports/analytics via DokumenRekapanController).

// (dihapus 2026-07-05, dead-code) admin/monitoring — duplikat /owner/dokumen
// (OwnerDashboardController@index), tak ditaut di mana pun.

// Urgency Alert Routes (Admin/Owner only for send/reset)
Route::post('owner/dokumen/{id}/urgency', [OwnerDashboardController::class, 'sendUrgency'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dokumen.urgency.send');

Route::delete('owner/dokumen/{id}/urgency', [OwnerDashboardController::class, 'resetUrgency'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dokumen.urgency.reset');

Route::delete('owner/urgency/reset-all', [OwnerDashboardController::class, 'resetAllUrgencies'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.urgency.reset-all');

// Notifikasi WhatsApp prioritas dari owner/kabag ke role verifikasi/perpajakan/akutansi
Route::post('owner/dokumen/{id}/priority-whatsapp', [OwnerDashboardController::class, 'sendPriorityWhatsApp'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dokumen.priority-whatsapp');

// Route owner/dokumen/{id}/history DIHAPUS 2026-07-11: nol pemanggil frontend.

// Active urgencies polling API – all authenticated roles can call this
Route::get('/api/documents/urgency/active', [OwnerDashboardController::class, 'getActiveUrgencies'])
    ->middleware('auth')
    ->name('api.documents.urgency.active');

// Professional Document Routes - Operator (Owner)
Route::middleware(['auth', 'role:operator'])->prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DokumenController::class, 'index'])->name('index');
    // Endpoint JSON progressive-load untuk Tabulator — STATIS, harus sebelum route {dokumen}.
    Route::get('/data', [DokumenController::class, 'datatable'])->name('data');
    // Task 4 fitur export bersama: tombol Export toolbar Tabulator (CFG.exportUrl) — Excel/PDF
    // dependency-free lewat DocumentExporter/ExportsDocuments (pola Task 3 pembayaran).
    // Statis, aman di atas route {dokumen} lain di grup ini.
    Route::get('/export', [DokumenController::class, 'exportDocuments'])->name('export');

    // CSV Import Routes - MUST be before {dokumen} routes to avoid conflict
    Route::get('/import', [\App\Http\Controllers\OperatorCsvImportController::class, 'index'])->name('import.index');
    Route::post('/import/upload', [\App\Http\Controllers\OperatorCsvImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/preview', [\App\Http\Controllers\OperatorCsvImportController::class, 'preview'])->name('import.preview');
    Route::post('/import', [\App\Http\Controllers\OperatorCsvImportController::class, 'import'])->name('import.execute');

    // API: Get next nomor agenda (auto-generate)
    Route::get('/next-nomor-agenda', [DokumenController::class, 'nextNomorAgenda'])->name('next-nomor-agenda');

    // Bulk send route (static route, before parameterized routes)
    Route::post('/bulk-send-to-verifikasi', [DokumenController::class, 'bulkSendToTeamVerifikasi'])->name('bulk-send-to-verifikasi');

    // Inline create — tambah baris dokumen langsung di tabel daftar dokumen
    Route::post('/inline-create', [DokumenController::class, 'inlineCreate'])->name('inline-create');

    // Routes with {dokumen} parameter - MUST be after static routes
    // Halaman edit dihapus 2026-07-09 — semua pengeditan via inline edit (documents.inline-update).
    Route::get('/{dokumen}/progress', [DokumenController::class, 'getDocumentProgressForOperator'])->name('progress');
    Route::delete('/{dokumen}', [DokumenController::class, 'destroy'])->name('destroy');
    Route::post('/{dokumen}/approve', [DokumenController::class, 'approveDocument'])->name('approve');
});

// Inline edit — accessible by all roles that can handle documents
Route::middleware(['auth', 'role:operator,team_verifikasi,verifikasi,perpajakan,akutansi,pembayaran'])
    ->prefix('documents')->name('documents.')
    ->group(function () {
        Route::patch('/{dokumen}/inline-update', [DokumenController::class, 'inlineUpdate'])->name('inline-update');
    });

// Pengurus Dokumen dropdown - available for all operational roles, guarded in controller.
Route::middleware(['auth'])
    ->prefix('documents')->name('documents.')
    ->group(function () {
        Route::patch('/{dokumen}/handler', [DocumentHandlerController::class, 'update'])->name('handler.update');
    });


// Professional Reports Routes
Route::middleware(['auth', 'role:operator'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [DokumenRekapanController::class, 'index'])->name('index');
});

// Rekapan Analitik — tersedia untuk semua role utama (owner & alur dokumen)
Route::get('reports/analytics', [DokumenRekapanController::class, 'analytics'])
    ->middleware('auth', 'role:operator,owner,team_verifikasi,perpajakan,akutansi,pembayaran')
    ->name('reports.analytics');

// Backward compatibility routes removed — old dokumen/rekapan URLs (Phase 2 cleanup)

// Autocomplete Routes — WAJIB login (dipakai form dokumen & inline-edit lintas role)
Route::middleware('auth')->group(function () {
    Route::get('/api/autocomplete/payment-recipients', [AutocompleteController::class, 'getPaymentRecipients'])->name('autocomplete.payment-recipients');
    Route::get('/api/autocomplete/document-senders', [AutocompleteController::class, 'getDocumentSenders'])->name('autocomplete.document-senders');
    Route::get('/api/autocomplete/document-descriptions', [AutocompleteController::class, 'getDocumentDescriptions'])->name('autocomplete.document-descriptions');
    Route::get('/api/autocomplete/po-numbers', [AutocompleteController::class, 'getPONumbers'])->name('autocomplete.po-numbers');
    Route::get('/api/autocomplete/pr-numbers', [AutocompleteController::class, 'getPRNumbers'])->name('autocomplete.pr-numbers');
});

// Professional Document Routes - Verifikasi (Team Verifikasi)
Route::middleware(['auth', 'role:team_verifikasi,verifikasi'])->prefix('documents/verifikasi')->name('documents.verifikasi.')->group(function () {
    Route::get('/', [TeamVerifikasiController::class, 'dokumens'])->name('index');
    Route::get('/data', [TeamVerifikasiController::class, 'datatable'])->name('data');
    // Task 4 fitur export bersama: tombol Export toolbar Tabulator (CFG.exportUrl) — Excel/PDF
    // dependency-free lewat DocumentExporter/ExportsDocuments (pola Task 3 pembayaran).
    Route::get('/export', [TeamVerifikasiController::class, 'exportDocuments'])->name('export');
});

// Professional Reports Routes - Verifikasi
// Halaman laporan/rekapan Team Verifikasi dihapus.

// Professional Returns Routes - Verifikasi
Route::middleware(['auth', 'role:team_verifikasi,verifikasi'])->prefix('returns/verifikasi')->name('returns.verifikasi.')->group(function () {
    // Halaman '/returns/verifikasi' (index) + '/stats' DIHAPUS 2026-07-05: halaman
    // "dokumen ditolak downstream" tanpa menu, tak dipakai lagi (dikonfirmasi pemilik).
    // Halaman pengembalian yang hidup ada di '/bagian' (menu "Pengembalian Ke Bagian").
    Route::get('/bagian', [TeamVerifikasiController::class, 'pengembalianKeBidang'])->name('bagian');
    Route::post('/{dokumen}/to-bidang', [TeamVerifikasiController::class, 'returnToBidang'])->name('to-bidang');
});


// Backward compatibility routes removed — old Team Verifikasi URLs (Phase 2 cleanup)

// Professional Approval Routes - Verifikasi (Team Verifikasi) DIHAPUS 2026-07-25 (Rollout 3,
// Task 6): acceptDocument/rejectDocument + route .accept/.reject yatim — Inbox memakai
// InboxController::approve/reject, bukan method ini. Grep final lintas resources/public/js/
// app/tests/config nol caller sebelum dihapus.

// Document Activity Tracking Routes
Route::middleware(['auth', 'web'])->prefix('api/documents')->name('api.documents.')->group(function () {
    Route::post('/{dokumen}/activity', [\App\Http\Controllers\InboxController::class, 'trackActivity'])
        ->name('activity.track');
    Route::get('/{dokumen}/activities', [\App\Http\Controllers\InboxController::class, 'getActivities'])
        ->name('activity.get');
    Route::post('/{dokumen}/activity/stop', [\App\Http\Controllers\InboxController::class, 'stopActivity'])
        ->name('activity.stop');
});

// Universal Approval DIHAPUS TOTAL 2026-07-09: getDetail & checkNotifications
// tak punya pemanggil frontend (pemanggil terakhir = blok notifikasi rusak di layout);
// approve/reject resmi via /inbox/*.

// Inbox Routes - Untuk Team Verifikasi, Perpajakan, Akutansi, Pembayaran (+ alias Verifikasi).
// operator & admin DIBUANG 2026-07-05: bagian tak lagi kirim dokumen → operator tak butuh
// inbox (PL-2); admin = KABAG god-view, bukan pekerja inbox (PL-1).
Route::middleware(['auth', 'role:team_verifikasi,verifikasi,perpajakan,akutansi,pembayaran'])->group(function () {
    Route::get('/inbox', [\App\Http\Controllers\InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/check-new', [\App\Http\Controllers\InboxController::class, 'checkNewDocuments'])->name('inbox.checkNew');
    Route::get('/inbox/history', [\App\Http\Controllers\InboxController::class, 'history'])->name('inbox.history');

    // Bulk approve route (static, before parameterized routes)
    Route::post('/inbox/bulk-approve', [\App\Http\Controllers\InboxController::class, 'bulkApprove'])->name('inbox.bulk-approve');

    Route::get('/inbox/{dokumen}', [\App\Http\Controllers\InboxController::class, 'show'])->name('inbox.show');
    Route::post('/inbox/{dokumen}/approve', [\App\Http\Controllers\InboxController::class, 'approve'])->name('inbox.approve');
    Route::post('/inbox/{dokumen}/reject', [\App\Http\Controllers\InboxController::class, 'reject'])->name('inbox.reject');
});

// Professional Document Routes - Pembayaran
Route::middleware(['auth', 'role:pembayaran'])->prefix('documents/pembayaran')->name('documents.pembayaran.')->group(function () {
    // URL diubah ke /daftar agar tidak terbentur cache redirect 301 lama (/documents/pembayaran → /dashboard/pembayaran).
    Route::get('/daftar', [DashboardPembayaranController::class, 'index'])->name('index');
    Route::get('/data', [DashboardPembayaranController::class, 'datatableTabulator'])->name('data');
    // Task 3 fitur export bersama: tombol Export toolbar Tabulator (CFG.exportUrl) — Excel/PDF
    // dependency-free lewat DocumentExporter/ExportsDocuments, ganti exportToExcel() PhpSpreadsheet
    // yang FATAL. Statis, aman di atas /{dokumen}/detail (beda jumlah segmen, tak pernah bentrok).
    Route::get('/export', [DashboardPembayaranController::class, 'exportDocuments'])->name('export');
    Route::get('/{dokumen}/detail', [DashboardPembayaranController::class, 'getDocumentDetail'])->name('detail');
});

// Professional Reports Routes - Pembayaran
Route::middleware(['auth', 'role:pembayaran'])->prefix('reports/pembayaran')->name('reports.pembayaran.')->group(function () {
    // Redirect to dashboard - content is now on home page
    Route::get('/', fn() => redirect()->route('dashboard.pembayaran'))->name('index');
    Route::get('/delays', [DashboardPembayaranController::class, 'rekapanKeterlambatan'])->name('delays');
});

// Backward compatibility routes removed — old Pembayaran URLs (Phase 2 cleanup)

// Grup route dashboard-pembayaran DIHAPUS 2026-07-05 (dead-code): duplikat mati
// dari halaman import CSV pembayaran (view pembayaranNEW/importCsv) yang sudah
// digantikan /csv-import (CsvImportController). Method index yang hidup tetap
// dilayani route documents.pembayaran.index. (checkUpdates ikut dihapus 2026-07-09
// bersama sistem notifikasi popup lama.)

// CSV Import Routes - Pembayaran
Route::middleware(['auth', 'role:pembayaran'])->prefix('csv-import')->name('csv.import.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CsvImportController::class, 'index'])->name('index');
    Route::post('/upload', [\App\Http\Controllers\CsvImportController::class, 'upload'])->name('upload');
    Route::post('/preview', [\App\Http\Controllers\CsvImportController::class, 'preview'])->name('preview');
    Route::post('/import', [\App\Http\Controllers\CsvImportController::class, 'import'])->name('execute');
});


// Professional Document Routes - Akutansi
Route::middleware(['auth', 'role:akutansi'])->prefix('documents/akutansi')->name('documents.akutansi.')->group(function () {
    Route::get('/', [DashboardAkutansiController::class, 'dokumens'])->name('index');
    // create/store/destroy DIHAPUS 2026-07-11: tak pernah di-link dari UI (create
    // error 500 karena view-nya tak ada; store/destroy hanya stub kosong).
    Route::get('/data', [DashboardAkutansiController::class, 'datatable'])->name('data');
    // Task 4 fitur export bersama: tombol Export toolbar Tabulator (CFG.exportUrl) — Excel/PDF
    // dependency-free lewat DocumentExporter/ExportsDocuments (pola Task 3 pembayaran).
    Route::get('/export', [DashboardAkutansiController::class, 'exportDocuments'])->name('export');
});

// Professional Reports Routes - Akutansi
// Halaman laporan/rekapan Akutansi dihapus.

// Professional Returns Routes - Akutansi
// Halaman Pengembalian Akutansi (returns.akutansi.index) DIHAPUS 2026-07-24 (dead-code):
// tak pernah di-link dari UI hidup; pergerakan dokumen kini lewat dropdown Pengurus Dokumen.
// Method aksi mati (getDocumentDetail/pengembalian) ikut dihapus dari
// DashboardAkutansiController.php — twin dari pembersihan perpajakan yang sama.


// Backward compatibility routes removed — old Akutansi URLs (Phase 2 cleanup)

// Professional Document Routes - Perpajakan
Route::middleware(['auth', 'role:perpajakan'])->prefix('documents/perpajakan')->name('documents.perpajakan.')->group(function () {
    Route::get('/', [DashboardPerpajakanController::class, 'dokumens'])->name('index');
    Route::get('/data', [DashboardPerpajakanController::class, 'datatable'])->name('data');
    // Task 4 fitur export bersama: tombol Export toolbar Tabulator (CFG.exportUrl) — Excel/PDF
    // dependency-free lewat DocumentExporter/ExportsDocuments (pola Task 3 pembayaran).
    Route::get('/export', [DashboardPerpajakanController::class, 'exportDocuments'])->name('export');
});

// Professional Reports Routes - Perpajakan
// Halaman laporan/rekapan & export Perpajakan dihapus.

// Professional Returns Routes - Perpajakan
// Halaman Pengembalian Perpajakan (returns.perpajakan.index) DIHAPUS 2026-07-24 (dead-code):
// tak pernah di-link dari UI hidup; pergerakan dokumen kini lewat dropdown Pengurus Dokumen.
// Method aksi mati (getDocumentDetail/sendToAkutansi/sendToNext/pengembalian) ikut dihapus
// dari DashboardPerpajakanController.php.

// Backward compatibility routes removed — old Perpajakan URLs (Phase 2 cleanup)


// =============================================================================
// BAGIAN DOCUMENT ROUTES - For department-specific users (AKN, DPM, KPL, etc.)
// =============================================================================
Route::middleware(['auth', 'bagian'])
    ->group(function () {
        // Document — VIEW ONLY (kemampuan tulis Bagian dicabut: Bagian hanya memantau dokumennya)
        Route::prefix('bagian/documents')->name('bagian.documents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BagianDokumenController::class, 'index'])->name('index');
            Route::get('/{dokumen}/detail', [\App\Http\Controllers\BagianDokumenController::class, 'getDocumentDetail'])->name('detail');
        });

        // Return detail API - reads return_reason directly from dokumens table
        Route::get('/api/bagian/documents/{dokumen}/return-detail', [\App\Http\Controllers\BagianDokumenController::class, 'getReturnDetail'])
            ->name('api.bagian.documents.return-detail');

        // Tandai notifikasi pengembalian sebagai sudah dibaca
        Route::post('/bagian/notifikasi/tandai-dibaca', [\App\Http\Controllers\BagianDokumenController::class, 'tandaiNotifikasiDibaca'])
            ->name('bagian.notifikasi.tandai-dibaca');

        // SEMENTARA (2026-08-07) — tombol uji kiriman WhatsApp untuk sesi uji coba
        // pengguna. throttle:5,1 bukan formalitas: tiap kiriman memotong kuota Fonnte
        // berbayar. Hapus bersama UjiWhatsAppBagianController (lihat docblock-nya).
        Route::post('/bagian/uji-whatsapp', [\App\Http\Controllers\UjiWhatsAppBagianController::class, 'kirim'])
            ->name('bagian.uji-whatsapp')
            ->middleware('throttle:5,1');
    });

// =============================================================================
// BULK OPERATIONS - Team Verifikasi
// =============================================================================
Route::middleware(['auth', 'role:team_verifikasi'])
    ->prefix('team-verifikasi/bulk')
    ->name('team-verifikasi.bulk.')
    ->group(function () {
        Route::post('/approve', [BulkOperationController::class, 'bulkApprove'])->name('approve');
        Route::post('/reject', [BulkOperationController::class, 'bulkReject'])->name('reject');
        Route::post('/forward', [BulkOperationController::class, 'bulkForward'])->name('forward');
    });

// =============================================================================
// BULK OPERATIONS - Common route for all allowed roles
// =============================================================================
Route::middleware(['auth', 'role:team_verifikasi,verifikasi,perpajakan,akutansi'])
    ->prefix('bulk-operations')
    ->name('bulk-operations.')
    ->group(function () {
        Route::post('/forward', [BulkOperationController::class, 'bulkForward'])->name('forward');
    });

// =============================================================================
// PROGRAMMER ROUTES - Special operations for developer/programmer role
// =============================================================================
Route::middleware(['auth', 'role:programmer'])
    ->prefix('programmer')
    ->name('programmer.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\ProgrammerController::class, 'dashboard'])
            ->name('dashboard');

        // Bulk Direct to Payment
        Route::get('/bulk-to-payment', [\App\Http\Controllers\ProgrammerController::class, 'showDirectToPaymentForm'])
            ->name('bulk-to-payment.form');
        Route::post('/bulk-to-payment/preview', [\App\Http\Controllers\ProgrammerController::class, 'previewDocuments'])
            ->name('bulk-to-payment.preview');
        Route::post('/bulk-to-payment', [\App\Http\Controllers\ProgrammerController::class, 'bulkDirectToPayment'])
            ->name('bulk-to-payment.execute');
            
        // Bulk Send to Role (Verifikasi, Perpajakan, Akutansi)
        Route::get('/bulk-send-to-role', [\App\Http\Controllers\ProgrammerController::class, 'showBulkSendToRoleForm'])
            ->name('bulk-send-to-role.form');
        Route::post('/bulk-send-to-role/preview', [\App\Http\Controllers\ProgrammerController::class, 'previewBulkSendToRole'])
            ->name('bulk-send-to-role.preview');
        Route::post('/bulk-send-to-role', [\App\Http\Controllers\ProgrammerController::class, 'bulkSendToRole'])
            ->name('bulk-send-to-role.execute');

        // Bulk Set Date Payment
        Route::get('/bulk-set-date-payment', [\App\Http\Controllers\ProgrammerController::class, 'showBulkSetDatePaymentForm'])
            ->name('bulk-set-date-payment.form');
        Route::post('/bulk-set-date-payment/preview', [\App\Http\Controllers\ProgrammerController::class, 'previewBulkSetDatePayment'])
            ->name('bulk-set-date-payment.preview');
        Route::post('/bulk-set-date-payment', [\App\Http\Controllers\ProgrammerController::class, 'executeBulkSetDatePayment'])
            ->name('bulk-set-date-payment.execute');

        // Document Tools - View document IDs and edit role timestamps
        Route::get('/document-tools', [\App\Http\Controllers\ProgrammerController::class, 'documentTools'])
            ->name('document-tools');
        Route::get('/document-tools/search', [\App\Http\Controllers\ProgrammerController::class, 'searchDocuments'])
            ->name('document-tools.search');
        Route::post('/document-tools/get-role-data', [\App\Http\Controllers\ProgrammerController::class, 'getRoleData'])
            ->name('document-tools.get-role-data');
        Route::post('/document-tools/update-timestamps', [\App\Http\Controllers\ProgrammerController::class, 'updateTimestamps'])
            ->name('document-tools.update-timestamps');

        // User Management - View and edit all users
        Route::get('/user-management', [\App\Http\Controllers\ProgrammerController::class, 'userManagement'])
            ->name('user-management');
        Route::get('/user-management/{id}', [\App\Http\Controllers\ProgrammerController::class, 'getUserData'])
            ->name('user-management.get');
        Route::post('/user-management/store', [\App\Http\Controllers\ProgrammerController::class, 'storeUser'])
            ->name('user-management.store');
        Route::post('/user-management/update', [\App\Http\Controllers\ProgrammerController::class, 'updateUser'])
            ->name('user-management.update');
        Route::delete('/user-management/{id}', [\App\Http\Controllers\ProgrammerController::class, 'destroyUser'])
            ->name('user-management.destroy');
        Route::post('/user-management/{id}/reset-2fa', [\App\Http\Controllers\ProgrammerController::class, 'resetUserTwoFactor'])
            ->name('user-management.reset-2fa');

        // Database Tools - Cleanup database
        Route::get('/database-tools', [\App\Http\Controllers\ProgrammerController::class, 'databaseTools'])
            ->name('database-tools');
        Route::get('/database-tools/preview', [\App\Http\Controllers\ProgrammerController::class, 'previewCleanup'])
            ->name('database-tools.preview');
        Route::post('/database-tools/cleanup', [\App\Http\Controllers\ProgrammerController::class, 'performCleanup'])
            ->name('database-tools.cleanup');
        Route::get('/database-tools/export/{database}', [\App\Http\Controllers\ProgrammerController::class, 'exportDatabase'])
            ->name('database-tools.export')
            ->where('database', 'agenda|cashbank');

        // Activity Logs - Riwayat Aktivitas Dokumen
        Route::get('/activity-logs', [\App\Http\Controllers\ProgrammerController::class, 'activityLogs'])
            ->name('activity-logs');

        // Programmer Audit Trail - Log aktivitas sensitif programmer
        Route::get('/programmer-audit-trail', [\App\Http\Controllers\ProgrammerLogController::class, 'index'])
            ->name('programmer-audit-trail');

        Route::get('/2fa-reset-requests', [\App\Http\Controllers\TwoFactorResetController::class, 'index'])
            ->name('2fa-reset-requests.index');
        Route::post('/2fa-reset-requests/{id}/approve', [\App\Http\Controllers\TwoFactorResetController::class, 'approve'])
            ->name('2fa-reset-requests.approve');
        Route::post('/2fa-reset-requests/{id}/reject', [\App\Http\Controllers\TwoFactorResetController::class, 'reject'])
            ->name('2fa-reset-requests.reject');

        Route::get('/notification-logs', [\App\Http\Controllers\WhatsAppNotificationLogController::class, 'index'])
            ->name('notification-logs');
    });

// =============================================================================
// OWNER: Lihat log aktivitas programmer (read-only)
// =============================================================================
Route::middleware(['auth', 'role:owner,admin'])
    ->group(function () {
        Route::get('/owner/programmer-logs', [\App\Http\Controllers\ProgrammerLogController::class, 'ownerIndex'])
            ->name('owner.programmer-logs');
    });

// =============================================================================
// OWNER / ADMIN: Laporan Cash Bank (read-only dari database cash_bank_new)
// =============================================================================
Route::middleware(['auth', 'role:owner,admin'])
    ->prefix('owner/cashbank')
    ->name('owner.cashbank.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\CashBankPimpinanController::class, 'index'])
            ->name('index');
        Route::get('/chart-data', [\App\Http\Controllers\CashBankPimpinanController::class, 'chartData'])
            ->name('chart');
    });

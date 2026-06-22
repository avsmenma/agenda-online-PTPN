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
use App\Http\Controllers\PengembalianDokumenController;
use App\Http\Controllers\DokumenRekapanController;
use App\Http\Controllers\DocumentHandlerController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\WelcomeMessageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\OwnerVirtualAssistantController;
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
    Route::post('/2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('2fa.verify.store');
    Route::post('/2fa/verify-recovery', [\App\Http\Controllers\TwoFactorController::class, 'verifyRecoveryCode'])->name('2fa.verify.recovery');
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

// SECURITY: Test routes removed or protected
// Only allow test routes in development environment
if (app()->environment('local', 'development')) {
    Route::get('/test-welcome/{module}', function ($module) {
        return view('test-welcome', ['module' => $module, 'title' => 'Testing Welcome Messages']);
    })->middleware('auth');

    Route::get('/simple-test', function () {
        $service = app('App\Services\WelcomeMessageService');
        $message = $service->getWelcomeMessage('operator');
        return "Welcome Message: " . $message;
    })->middleware('auth');
} else {
    // In production, return 404 for test routes
    Route::get('/test-welcome/{module}', function () {
        abort(404);
    });
    Route::get('/simple-test', function () {
        abort(404);
    });
}

Route::get('/api/welcome-message', [WelcomeMessageController::class, 'getMessage'])->middleware('auth');

// Professional API routes for document updates
Route::get('/api/documents/verifikasi/check-updates', [TeamVerifikasiController::class, 'checkVerifikasiUpdates'])
    ->middleware('auth')
    ->name('api.documents.verifikasi.check-updates');

Route::get('/api/documents/perpajakan/check-updates', [DashboardPerpajakanController::class, 'checkUpdates'])
    ->middleware('auth')
    ->name('api.documents.perpajakan.check-updates');
Route::get('/api/documents/akutansi/check-updates', [DashboardAkutansiController::class, 'checkUpdates'])
    ->middleware('auth')
    ->name('api.documents.akutansi.check-updates');
Route::get('/api/documents/pembayaran/check-updates', [DashboardPembayaranController::class, 'checkUpdates'])
    ->middleware('auth')
    ->name('api.documents.pembayaran.check-updates');

// Backward compatibility routes removed — old check-updates routes were redundant (Phase 2 cleanup)


// Dashboard routes with role protection - Professional URLs
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware('auth', 'role:admin,operator')
    ->name('dashboard.main');

// Dashboard Team Verifikasi
Route::get('dashboard/verifikasi', [TeamVerifikasiController::class, 'dashboard'])
    ->middleware('auth', 'role:admin,team_verifikasi,verifikasi')
    ->name('dashboard.verifikasi');

Route::get('dashboard/pembayaran', [DashboardPembayaranController::class, 'dashboard'])
    ->middleware('auth', 'role:admin,pembayaran')
    ->name('dashboard.pembayaran');

Route::get('dashboard/pembayaran/data', [DashboardPembayaranController::class, 'datatable'])
    ->middleware('auth', 'role:admin,pembayaran')
    ->name('dashboard.pembayaran.data');

Route::get('dashboard/pembayaran/asisten-virtual', [OwnerVirtualAssistantController::class, 'pembayaranIndex'])
    ->middleware('auth', 'role:admin,pembayaran')
    ->name('pembayaran.asisten-virtual');

Route::post('dashboard/pembayaran/asisten-virtual/chat', [OwnerVirtualAssistantController::class, 'chat'])
    ->middleware('auth', 'role:admin,pembayaran')
    ->name('pembayaran.asisten-virtual.chat');

Route::post('dashboard/pembayaran/asisten-virtual/{interaction}/feedback', [OwnerVirtualAssistantController::class, 'feedback'])
    ->middleware('auth', 'role:admin,pembayaran')
    ->name('pembayaran.asisten-virtual.feedback');






// Dashboard Team Akutansi & Perpajakan
Route::get('dashboard/akutansi', [DashboardAkutansiController::class, 'dashboard'])
    ->middleware('auth', 'role:admin,akutansi')
  ->name('dashboard.akutansi');

Route::get('dashboard/perpajakan', [DashboardPerpajakanController::class, 'dashboard'])
    ->middleware('auth', 'role:admin,perpajakan')
  ->name('dashboard.perpajakan');

// Backward compatibility routes removed — old dashboard URLs (Phase 2 cleanup)

// Professional API routes for rejected documents
Route::get('/api/documents/rejected/check', [DashboardController::class, 'checkRejectedDocuments'])
    ->middleware('auth', 'role:admin,operator')
    ->name('api.documents.rejected.check');
Route::get('/api/documents/rejected/{dokumen}', [DashboardController::class, 'showRejectedDocument'])
    ->middleware('auth', 'role:admin,operator,bagian')
    ->name('api.documents.rejected.show');
Route::get('/api/documents/verifikasi/rejected/check', [TeamVerifikasiController::class, 'checkRejectedDocuments'])
    ->middleware('auth', 'role:admin,team_verifikasi')
    ->name('api.documents.verifikasi.rejected.check');
Route::get('/api/documents/verifikasi/rejected/{dokumen}', [TeamVerifikasiController::class, 'showRejectedDocument'])
    ->middleware('auth', 'role:admin,team_verifikasi')
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

// AJAX filter endpoint for live filtering (no page refresh)
Route::get('owner/dokumen/filter', [OwnerDashboardController::class, 'filterDocuments'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dokumen.filter');

Route::get('owner/asisten-virtual', [OwnerVirtualAssistantController::class, 'index'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.asisten-virtual');

Route::post('owner/asisten-virtual/chat', [OwnerVirtualAssistantController::class, 'chat'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.asisten-virtual.chat');

Route::post('owner/asisten-virtual/{interaction}/feedback', [OwnerVirtualAssistantController::class, 'feedback'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.asisten-virtual.feedback');

// Redirect old dashboard URL to home
Route::get('owner/dashboard', fn() => redirect()->route('owner.home'))
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dashboard');

Route::get('owner/api/real-time-updates', [OwnerDashboardController::class, 'getRealTimeUpdates'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.api.real-time-updates');

// Recent documents API for dashboard polling
Route::get('owner/api/recent-documents', [OwnerDashboardController::class, 'getRecentDocuments'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.api.recent-documents');

// Trend chart API for period tab switching
Route::get('owner/api/trend-chart', [OwnerDashboardController::class, 'getTrendChart'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.api.trend-chart');

// Tracking Dokumen routes - Available for all roles
Route::get('tracking-dokumen', [OwnerDashboardController::class, 'trackingDokumen'])
    ->middleware('auth')
    ->name('tracking.dokumen');

Route::get('owner/rekapan-keterlambatan', fn () => redirect()->route('rekapan-keterlambatan.index'))
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.rekapan-keterlambatan');

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

// Rekapan keterlambatan per role
Route::get('owner/rekapan-keterlambatan/{roleCode}', [OwnerDashboardController::class, 'rekapanKeterlambatanByRole'])
    ->middleware('auth')
    ->where('roleCode', 'operator|team_verifikasi|perpajakan|akutansi|pembayaran')
    ->name('owner.rekapan-keterlambatan.role');

// Export rekapan keterlambatan per role to Excel
Route::get('owner/rekapan-keterlambatan-export/{roleCode}', [OwnerDashboardController::class, 'exportRekapanKeterlambatan'])
    ->middleware('auth', 'role:admin,owner')
    ->where('roleCode', 'team_verifikasi|perpajakan|akutansi|pembayaran')
    ->name('owner.rekapan-keterlambatan.export');

// Analytics Dashboard - merged with Rekapan Keterlambatan (redirect for backward compat)
Route::get('owner/analytics', fn() => redirect()->route('owner.rekapan-keterlambatan'))
    ->middleware('auth', 'role:admin,owner')
    ->name('analytics.index');
Route::get('owner/analytics/data', [\App\Http\Controllers\AnalyticsController::class, 'getAnalyticsData'])
    ->middleware('auth', 'role:admin,owner')
    ->name('analytics.data');

// Admin shortcut to Owner Dashboard
Route::get('admin/monitoring', [OwnerDashboardController::class, 'index'])
    ->middleware('auth', 'role:admin')
    ->name('admin.monitoring');

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

// Document History / Timeline API
Route::get('owner/dokumen/{id}/history', [OwnerDashboardController::class, 'getHistory'])
    ->middleware('auth', 'role:admin,owner')
    ->name('owner.dokumen.history');

// Active urgencies polling API – all authenticated roles can call this
Route::get('/api/documents/urgency/active', [OwnerDashboardController::class, 'getActiveUrgencies'])
    ->middleware('auth')
    ->name('api.documents.urgency.active');

// Professional Document Routes - Operator (Owner)
Route::middleware(['auth', 'role:admin,operator'])->prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DokumenController::class, 'index'])->name('index');
    Route::get('/create', [DokumenController::class, 'create'])->name('create');
    Route::post('/', [DokumenController::class, 'store'])->name('store');

    // CSV Import Routes - MUST be before {dokumen} routes to avoid conflict
    Route::get('/import', [\App\Http\Controllers\OperatorCsvImportController::class, 'index'])->name('import.index');
    Route::post('/import/upload', [\App\Http\Controllers\OperatorCsvImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/preview', [\App\Http\Controllers\OperatorCsvImportController::class, 'preview'])->name('import.preview');
    Route::post('/import', [\App\Http\Controllers\OperatorCsvImportController::class, 'import'])->name('import.execute');

    // API: Get next nomor agenda (auto-generate)
    Route::get('/next-nomor-agenda', [DokumenController::class, 'nextNomorAgenda'])->name('next-nomor-agenda');

    // Bulk send route (static route, before parameterized routes)
    Route::post('/bulk-send-to-verifikasi', [DokumenController::class, 'bulkSendToTeamVerifikasi'])->name('bulk-send-to-verifikasi');
    // AJAX table rows loader (for "Semua" per-page, no full reload)
    Route::get('/ajax-rows', [DokumenController::class, 'ajaxRows'])->name('ajax-rows');
    Route::get('/all-data', [DokumenController::class, 'getAllData'])->name('all-data');

    // Inline create — tambah baris dokumen langsung di tabel daftar dokumen
    Route::post('/inline-create', [DokumenController::class, 'inlineCreate'])->name('inline-create');

    // Routes with {dokumen} parameter - MUST be after static routes
    Route::get('/{dokumen}/edit', [DokumenController::class, 'edit'])->name('edit');
    Route::get('/{dokumen}/detail', [DokumenController::class, 'getDocumentDetail'])->name('detail');
    Route::get('/{dokumen}/progress', [DokumenController::class, 'getDocumentProgressForOperator'])->name('progress');
    Route::put('/{dokumen}', [DokumenController::class, 'update'])->name('update');
    Route::delete('/{dokumen}', [DokumenController::class, 'destroy'])->name('destroy');
    Route::post('/{dokumen}/send-to-verifikasi', [DokumenController::class, 'sendToTeamVerifikasi'])->name('send-to-verifikasi');
    Route::post('/{dokumen}/approve', [DokumenController::class, 'approveDocument'])->name('approve');
});

// Inline edit — accessible by all roles that can handle documents
Route::middleware(['auth', 'role:admin,operator,team_verifikasi,verifikasi,perpajakan,akutansi,pembayaran,bagian'])
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
Route::middleware(['auth', 'role:admin,operator'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [DokumenRekapanController::class, 'index'])->name('index');
});

// Rekapan Analitik — tersedia untuk semua role utama (owner & alur dokumen)
Route::get('reports/analytics', [DokumenRekapanController::class, 'analytics'])
    ->middleware('auth', 'role:admin,operator,owner,team_verifikasi,perpajakan,akutansi,pembayaran')
    ->name('reports.analytics');

// Backward compatibility routes removed — old dokumen/rekapan URLs (Phase 2 cleanup)

// Autocomplete Routes
Route::get('/api/autocomplete/payment-recipients', [AutocompleteController::class, 'getPaymentRecipients'])->name('autocomplete.payment-recipients');
Route::get('/api/autocomplete/document-senders', [AutocompleteController::class, 'getDocumentSenders'])->name('autocomplete.document-senders');
Route::get('/api/autocomplete/document-descriptions', [AutocompleteController::class, 'getDocumentDescriptions'])->name('autocomplete.document-descriptions');
Route::get('/api/autocomplete/po-numbers', [AutocompleteController::class, 'getPONumbers'])->name('autocomplete.po-numbers');
Route::get('/api/autocomplete/pr-numbers', [AutocompleteController::class, 'getPRNumbers'])->name('autocomplete.pr-numbers');
Route::get('/pengembalian-dokumens', [PengembalianDokumenController::class, 'index']);

// Professional Document Routes - Verifikasi (Team Verifikasi)
Route::middleware(['auth', 'role:admin,team_verifikasi,verifikasi'])->prefix('documents/verifikasi')->name('documents.verifikasi.')->group(function () {
    Route::get('/', [TeamVerifikasiController::class, 'dokumens'])->name('index');
    Route::get('/{dokumen}/detail', [TeamVerifikasiController::class, 'getDocumentDetail'])->name('detail');
    Route::get('/{dokumen}/edit', [TeamVerifikasiController::class, 'editDokumen'])->name('edit');
    Route::put('/{dokumen}', [TeamVerifikasiController::class, 'updateDokumen'])->name('update');
    Route::post('/{dokumen}/return-to-department', [TeamVerifikasiController::class, 'returnToDepartment'])->name('return-to-department');
    Route::post('/{dokumen}/send-to-next', [TeamVerifikasiController::class, 'sendToNextHandler'])->name('send-to-next');
    Route::post('/{dokumen}/set-deadline', [TeamVerifikasiController::class, 'setDeadline'])->name('set-deadline');
    Route::post('/{dokumen}/return-to-owner', [TeamVerifikasiController::class, 'returnToOperator'])->name('return-to-owner');
    Route::post('/{dokumen}/change-status', [TeamVerifikasiController::class, 'changeDocumentStatus'])->name('change-status');
    Route::post('/{dokumen}/paraf', [TeamVerifikasiController::class, 'parafDokumen'])->name('paraf');
});

// Professional Reports Routes - Verifikasi
// Halaman laporan/rekapan Team Verifikasi dihapus.

// Professional Returns Routes - Verifikasi
Route::middleware(['auth', 'role:admin,team_verifikasi,verifikasi'])->prefix('returns/verifikasi')->name('returns.verifikasi.')->group(function () {
    Route::get('/', [TeamVerifikasiController::class, 'pengembalian'])->name('index');
    Route::get('/stats', [TeamVerifikasiController::class, 'getPengembalianKeBagianStats'])->name('stats');
    Route::get('/bagian', [TeamVerifikasiController::class, 'pengembalianKeBidang'])->name('bagian');
    Route::post('/{dokumen}/to-bidang', [TeamVerifikasiController::class, 'returnToBidang'])->name('to-bidang');
    Route::post('/{dokumen}/restore-from-bidang', [TeamVerifikasiController::class, 'restoreFromBidang'])->name('restore-from-bidang');
});


// Backward compatibility routes removed — old Team Verifikasi URLs (Phase 2 cleanup)

// Professional Approval Routes - Verifikasi (Team Verifikasi)
Route::middleware(['auth', 'role:admin,team_verifikasi,verifikasi'])->prefix('documents/verifikasi')->name('documents.verifikasi.')->group(function () {
    Route::post('/{dokumen}/accept', [TeamVerifikasiController::class, 'acceptDocument'])
        ->name('accept');
    Route::post('/{dokumen}/reject', [TeamVerifikasiController::class, 'rejectDocument'])
        ->name('reject');
    Route::get('/pending-approval', [TeamVerifikasiController::class, 'pendingApproval'])
        ->name('pending-approval');
});

// Document Activity Tracking Routes
Route::middleware(['auth', 'web'])->prefix('api/documents')->name('api.documents.')->group(function () {
    Route::post('/{dokumen}/activity', [\App\Http\Controllers\InboxController::class, 'trackActivity'])
        ->name('activity.track');
    Route::get('/{dokumen}/activities', [\App\Http\Controllers\InboxController::class, 'getActivities'])
        ->name('activity.get');
    Route::post('/{dokumen}/activity/stop', [\App\Http\Controllers\InboxController::class, 'stopActivity'])
        ->name('activity.stop');
});

// Universal Approval Routes - Untuk semua user kecuali Operator - dengan auth
Route::middleware(['auth'])->group(function () {
    Route::post('/universal-approval/{dokumen}/approve', [\App\Http\Controllers\InboxController::class, 'approve'])
        ->name('universal.approval.approve');

    Route::post('/universal-approval/{dokumen}/reject', [\App\Http\Controllers\InboxController::class, 'reject'])
        ->name('universal.approval.reject');

    Route::get('/universal-approval/{dokumen}/detail', [\App\Http\Controllers\UniversalApprovalController::class, 'getDetail'])
        ->name('universal.approval.detail');

    Route::get('/universal-approval/notifications', [\App\Http\Controllers\UniversalApprovalController::class, 'checkNotifications'])
        ->name('universal.approval.notifications');
});

// Inbox Routes - Untuk Operator, Team Verifikasi, Perpajakan, Akutansi, Pembayaran, Verifikasi
Route::middleware(['auth', 'role:operator,team_verifikasi,verifikasi,perpajakan,akutansi,pembayaran,admin'])->group(function () {
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
Route::middleware(['auth', 'role:admin,pembayaran'])->prefix('documents/pembayaran')->name('documents.pembayaran.')->group(function () {
    // URL diubah ke /daftar agar tidak terbentur cache redirect 301 lama (/documents/pembayaran → /dashboard/pembayaran).
    Route::get('/daftar', [DashboardPembayaranController::class, 'index'])->name('index');
    Route::get('/{dokumen}/detail', [DashboardPembayaranController::class, 'getDocumentDetail'])->name('detail');
    Route::get('/{dokumen}/payment-data', [DashboardPembayaranController::class, 'getPaymentData'])->name('payment-data');
    Route::post('/{dokumen}/set-deadline', [DashboardPembayaranController::class, 'setDeadline'])->name('set-deadline');
    Route::post('/{dokumen}/update-status', [DashboardPembayaranController::class, 'updateStatus'])->name('update-status');
    Route::post('/{dokumen}/upload-proof', [DashboardPembayaranController::class, 'uploadBukti'])->name('upload-proof');
});

// Professional Reports Routes - Pembayaran
Route::middleware(['auth', 'role:admin,pembayaran'])->prefix('reports/pembayaran')->name('reports.pembayaran.')->group(function () {
    // Redirect to dashboard - content is now on home page
    Route::get('/', fn() => redirect()->route('dashboard.pembayaran'))->name('index');
    Route::match(['get', 'post'], '/export', [DashboardPembayaranController::class, 'exportRekapan'])->name('export');
    Route::get('/delays', [DashboardPembayaranController::class, 'rekapanKeterlambatan'])->name('delays');
});

// Professional Returns Routes - Pembayaran
Route::get('/returns/pembayaran', [DashboardPembayaranController::class, 'pengembalian'])
    ->middleware(['auth', 'role:admin,pembayaran'])
    ->name('returns.pembayaran.index');


// Backward compatibility routes removed — old Pembayaran URLs (Phase 2 cleanup)

// Dashboard Pembayaran Routes
Route::middleware('auth')->prefix('dashboard-pembayaran')->name('dashboard-pembayaran.')->group(function () {
    Route::get('/', [DashboardPembayaranController::class, 'index'])->name('index');
    Route::get('/import', [DashboardPembayaranController::class, 'showImportForm'])->name('import');
    Route::post('/import-csv', [DashboardPembayaranController::class, 'importCsv'])->name('import-csv');
    Route::get('/download-csv-template', [DashboardPembayaranController::class, 'downloadCsvTemplate'])->name('download-csv-template');
    Route::post('/check-updates', [DashboardPembayaranController::class, 'checkUpdates']);
});

// CSV Import Routes - Pembayaran
Route::middleware(['auth', 'role:admin,pembayaran'])->prefix('csv-import')->name('csv.import.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CsvImportController::class, 'index'])->name('index');
    Route::post('/upload', [\App\Http\Controllers\CsvImportController::class, 'upload'])->name('upload');
    Route::post('/preview', [\App\Http\Controllers\CsvImportController::class, 'preview'])->name('preview');
    Route::post('/import', [\App\Http\Controllers\CsvImportController::class, 'import'])->name('execute');
});


// Professional Document Routes - Akutansi
Route::middleware(['auth', 'role:admin,akutansi'])->prefix('documents/akutansi')->name('documents.akutansi.')->group(function () {
    Route::get('/', [DashboardAkutansiController::class, 'dokumens'])->name('index');
    Route::get('/create', [DashboardAkutansiController::class, 'createDokumen'])->name('create');
    Route::post('/', [DashboardAkutansiController::class, 'storeDokumen'])->name('store');
    Route::get('/{dokumen}/edit', [DashboardAkutansiController::class, 'editDokumen'])->name('edit');
    Route::get('/{dokumen}/detail', [DashboardAkutansiController::class, 'getDocumentDetail'])->name('detail');
    Route::put('/{dokumen}', [DashboardAkutansiController::class, 'updateDokumen'])->name('update');
    Route::delete('/{dokumen}', [DashboardAkutansiController::class, 'destroyDokumen'])->name('destroy');
    Route::post('/{dokumen}/set-deadline', [DashboardAkutansiController::class, 'setDeadline'])->name('set-deadline');
    Route::post('/{dokumen}/send-to-pembayaran', [DashboardAkutansiController::class, 'sendToPembayaran'])->name('send-to-pembayaran');
    Route::post('/{dokumen}/return', [DashboardAkutansiController::class, 'returnDocument'])->name('return');
});

// Professional Reports Routes - Akutansi
// Halaman laporan/rekapan Akutansi dihapus.

// Professional Returns Routes - Akutansi
Route::get('/returns/akutansi', [DashboardAkutansiController::class, 'pengembalian'])
    ->middleware(['auth', 'role:admin,akutansi'])
    ->name('returns.akutansi.index');


// Backward compatibility routes removed — old Akutansi URLs (Phase 2 cleanup)

// Professional Document Routes - Perpajakan
Route::middleware(['auth', 'role:admin,perpajakan'])->prefix('documents/perpajakan')->name('documents.perpajakan.')->group(function () {
    Route::get('/', [DashboardPerpajakanController::class, 'dokumens'])->name('index');
    Route::get('/{dokumen}/detail', [DashboardPerpajakanController::class, 'getDocumentDetail'])->name('detail');
    Route::get('/{dokumen}/edit', [DashboardPerpajakanController::class, 'editDokumen'])->name('edit');
    Route::put('/{dokumen}', [DashboardPerpajakanController::class, 'updateDokumen'])->name('update');
    Route::post('/{dokumen}/set-deadline', [DashboardPerpajakanController::class, 'setDeadline'])->name('set-deadline');
    Route::post('/{dokumen}/send-to-next', [DashboardPerpajakanController::class, 'sendToNext'])->name('send-to-next');
    Route::post('/{dokumen}/send-to-akutansi', [DashboardPerpajakanController::class, 'sendToAkutansi'])->name('send-to-akutansi');
    Route::post('/{dokumen}/return', [DashboardPerpajakanController::class, 'returnDocument'])->name('return');
});

// Professional Reports Routes - Perpajakan
// Halaman laporan/rekapan & export Perpajakan dihapus.

// Professional Returns Routes - Perpajakan
Route::get('/returns/perpajakan', [DashboardPerpajakanController::class, 'pengembalian'])
    ->middleware(['auth', 'role:admin,perpajakan'])
    ->name('returns.perpajakan.index');

// Backward compatibility routes removed — old Perpajakan URLs (Phase 2 cleanup)

// SECURITY: Test routes removed or protected - Only available in development
if (app()->environment('local', 'development')) {
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/test-broadcast', function () {
            $dokumen = \App\Models\Dokumen::where('current_handler', 'team_verifikasi')
                ->orWhere('status', 'sent_to_team_verifikasi')
                ->latest()
                ->first();

            if (!$dokumen) {
                return response()->json([
                    'error' => 'No document found for testing'
                ], 404);
            }

            try {
                broadcast(new \App\Events\DocumentSent($dokumen, 'test', 'team_verifikasi'));
                \Log::info('Test broadcast sent', ['document_id' => $dokumen->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Test broadcast sent!',
                    'document_id' => $dokumen->id,
                    'channel' => 'documents.Team Verifikasi'
                ]);
            } catch (\Exception $e) {
                \Log::error('Test broadcast failed: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Broadcast failed: ' . $e->getMessage()
                ], 500);
            }
        });

        Route::get('/test-returned-broadcast', function () {
            $dokumen = \App\Models\Dokumen::where('created_by', 'operator')
                ->where('status', 'returned_to_operator')
                ->latest()
                ->first();

            if (!$dokumen) {
                return response()->json([
                    'error' => 'No returned document found for testing'
                ], 404);
            }

            try {
                broadcast(new \App\Events\DocumentReturned($dokumen, $dokumen->alasan_pengembalian ?: 'Test alasan pengembalian', 'team_verifikasi'));
                \Log::info('Test returned broadcast sent', ['document_id' => $dokumen->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Test returned broadcast sent!',
                    'document_id' => $dokumen->id,
                    'channel' => 'documents.Operator'
                ]);
            } catch (\Exception $e) {
                \Log::error('Test returned broadcast failed: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Broadcast failed: ' . $e->getMessage()
                ], 500);
            }
        });

        Route::get('/test-broadcast-auth', function () {
            $user = auth()->user();
            return response()->json([
                'authenticated' => auth()->check(),
                'user_id' => $user?->id,
                'user_role' => $user?->role,
                'user_name' => $user?->name,
                'session_id' => session()->getId(),
                'csrf_token' => csrf_token()
            ]);
        });

        Route::get('/test-trigger-notification', function () {
            $dokumen = \App\Models\Dokumen::where('created_by', 'operator')
                ->where('status', 'returned_to_operator')
                ->first();

            if (!$dokumen) {
                return response()->json([
                    'error' => 'No returned document found'
                ]);
            }

            $dokumen->update([
                'returned_to_operator_at' => \Illuminate\Support\Carbon::now()->subMinutes(1),
                'alasan_pengembalian' => 'Test notification trigger at ' . \Illuminate\Support\Carbon::now()->format('H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification triggered!',
                'document_id' => $dokumen->id,
                'returned_at' => $dokumen->returned_to_operator_at,
            ]);
        });
    });
} else {
    // SECURITY: In production, return 404 for all test routes
    Route::get('/test-broadcast', function () {
        abort(404);
    });
    Route::get('/test-returned-broadcast', function () {
        abort(404);
    });
    Route::get('/test-broadcast-auth', function () {
        abort(404);
    });
    Route::get('/test-trigger-notification', function () {
        abort(404);
    });
}

// SECURITY: Role switching routes removed - Critical security vulnerability
// These routes allowed unauthorized role switching via URL manipulation
// If needed for development, protect with admin-only access
if (app()->environment('local', 'development')) {
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/switch-role/{role}', function ($role) {
            \Log::warning('SECURITY: Role switching attempted', [
                'user_id' => auth()->id(),
                'requested_role' => $role,
                'ip' => request()->ip(),
            ]);
            abort(403, 'Role switching disabled for security');
        })->name('switch.role');

        Route::get('/dev-dashboard/{role?}', function ($role = 'operator') {
            abort(403, 'Development dashboard disabled for security');
        })->name('dev.dashboard');

        Route::get('/dev-all', function () {
            abort(403, 'Development routes disabled for security');
        })->name('dev.all');
    });
} else {
    // In production, return 404
    Route::get('/switch-role/{role}', function () {
        abort(404);
    });
    Route::get('/dev-dashboard/{role?}', function () {
        abort(404);
    });
    Route::get('/dev-all', function () {
        abort(404);
    });
}

// =============================================================================
// BAGIAN DOCUMENT ROUTES - For department-specific users (AKN, DPM, KPL, etc.)
// =============================================================================
Route::middleware(['auth', 'bagian'])
    ->group(function () {
        // Dashboard
        Route::get('bagian/dashboard', [\App\Http\Controllers\BagianDokumenController::class, 'dashboard'])
            ->name('bagian.dashboard');

        // Document CRUD
        Route::prefix('bagian/documents')->name('bagian.documents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BagianDokumenController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BagianDokumenController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BagianDokumenController::class, 'store'])->name('store');
            Route::get('/{dokumen}/edit', [\App\Http\Controllers\BagianDokumenController::class, 'edit'])->name('edit');
            Route::get('/{dokumen}/detail', [\App\Http\Controllers\BagianDokumenController::class, 'getDocumentDetail'])->name('detail');
            Route::put('/{dokumen}', [\App\Http\Controllers\BagianDokumenController::class, 'update'])->name('update');
            Route::delete('/{dokumen}', [\App\Http\Controllers\BagianDokumenController::class, 'destroy'])->name('destroy');
            Route::post('/{dokumen}/send-to-Operator', [\App\Http\Controllers\BagianDokumenController::class, 'sendToOperator'])->name('send-to-Operator');
        });

        // Tracking
        Route::get('bagian/tracking', [\App\Http\Controllers\BagianDokumenController::class, 'tracking'])
            ->name('bagian.tracking');

        // Return detail API - reads return_reason directly from dokumens table
        Route::get('/api/bagian/documents/{dokumen}/return-detail', [\App\Http\Controllers\BagianDokumenController::class, 'getReturnDetail'])
            ->name('api.bagian.documents.return-detail');
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

        // Evaluasi Asisten Virtual - feedback loop dan test case AI
        Route::get('/assistant-evaluation', [\App\Http\Controllers\ProgrammerAssistantEvaluationController::class, 'index'])
            ->name('assistant-evaluation');
        Route::get('/assistant-evaluation/export', [\App\Http\Controllers\ProgrammerAssistantEvaluationController::class, 'export'])
            ->name('assistant-evaluation.export');
        Route::post('/assistant-evaluation/{interaction}/fixed', [\App\Http\Controllers\ProgrammerAssistantEvaluationController::class, 'markFixed'])
            ->name('assistant-evaluation.fixed');
        Route::post('/assistant-evaluation/test-cases', [\App\Http\Controllers\ProgrammerAssistantEvaluationController::class, 'storeTestCase'])
            ->name('assistant-evaluation.test-cases.store');

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

        Route::get('/owner/notification-logs', [\App\Http\Controllers\WhatsAppNotificationLogController::class, 'index'])
            ->name('owner.notification-logs');
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

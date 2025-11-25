<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\diagramController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardBController;
use App\Http\Controllers\DashboardPembayaranController;
use App\Http\Controllers\DashboardAkutansiController;
use App\Http\Controllers\DashboardPerpajakanController;
use App\Http\Controllers\PengembalianDokumenController;
use App\Http\Controllers\DokumenRekapanController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\WelcomeMessageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OwnerDashboardController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

Route::middleware('autologin')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [LoginController::class, 'dashboard'])->name('dashboard');
});

Route::get('/', function () {
    return redirect('/login');
});

// Test routes for welcome messages
Route::get('/test-welcome/{module}', function($module) {
    return view('test-welcome', ['module' => $module, 'title' => 'Testing Welcome Messages']);
});

Route::get('/simple-test', function() {
    $service = app('App\Services\WelcomeMessageService');
    $message = $service->getWelcomeMessage('IbuA');
    return "Welcome Message: " . $message;
});

Route::get('/api/welcome-message', [WelcomeMessageController::class, 'getMessage']);

// Broadcasting Authentication Route
Broadcast::routes(['middleware' => ['web']]);

// API endpoint untuk cek update dokumen baru
Route::get('/dokumensB/check-updates', function() {
    try {
        $lastChecked = request()->input('last_checked', 0);

        // Cek dokumen baru untuk IbuB
        $newDocuments = \App\Models\Dokumen::where('current_handler', 'ibuB')
            ->where('sent_to_ibub_at', '>', date('Y-m-d H:i:s', $lastChecked))
            ->latest('sent_to_ibub_at')
            ->take(10)
            ->get();

        $totalDocuments = \App\Models\Dokumen::where('current_handler', 'ibuB')->count();

        return response()->json([
            'has_updates' => $newDocuments->count() > 0,
            'new_count' => $newDocuments->count(),
            'total_documents' => $totalDocuments,
            'new_documents' => $newDocuments->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'nomor_agenda' => $doc->nomor_agenda,
                    'nomor_spp' => $doc->nomor_spp,
                    'uraian_spp' => $doc->uraian_spp,
                    'nilai_rupiah' => $doc->nilai_rupiah,
                    'status' => $doc->status,
                    'sent_at' => $doc->sent_to_ibub_at?->format('d/m/Y H:i'),
                ];
            }),
            'last_checked' => time()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => 'Failed to check updates: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/perpajakan/check-updates', [DashboardPerpajakanController::class, 'checkUpdates']);
Route::get('/akutansi/check-updates', [DashboardAkutansiController::class, 'checkUpdates']);
Route::get('/pembayaran/check-updates', [DashboardPembayaranController::class, 'checkUpdates']);


// Dashboard routes with role protection
Route::get('dashboard',[DashboardController::class, 'index'])
    ->middleware('autologin', 'role:admin,ibua')
    ->name('dashboard.main');

Route::get('dashboardB',[DashboardBController::class, 'index'])
    ->middleware('autologin', 'role:admin,ibub')
    ->name('dashboard.ibub');

Route::get('dashboardPembayaran',[DashboardPembayaranController::class, 'index'])
    ->middleware('autologin', 'role:admin,Pembayaran')
    ->name('dashboard.pembayaran');

Route::get('dashboardAkutansi',[DashboardAkutansiController::class, 'index'])
    ->middleware('autologin', 'role:admin,akutansi')
    ->name('dashboard.akutansi');

Route::get('dashboardPerpajakan',[DashboardPerpajakanController::class, 'index'])
    ->middleware('autologin', 'role:admin,perpajakan')
    ->name('dashboard.perpajakan');

// Dashboard for Verifikasi role (future implementation)
Route::get('dashboardVerifikasi', function() {
    return view('verifikasi.dashboard');
})->middleware('role:admin,verifikasi')
    ->name('dashboard.verifikasi');

// Owner Dashboard routes (God View)
Route::get('owner/dashboard', [OwnerDashboardController::class, 'index'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.dashboard');

Route::get('owner/api/real-time-updates', [OwnerDashboardController::class, 'getRealTimeUpdates'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.api.real-time-updates');

Route::get('owner/api/document-timeline/{id}', [OwnerDashboardController::class, 'getDocumentTimeline'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.api.document-timeline');

Route::get('owner/workflow/{id}', [OwnerDashboardController::class, 'showWorkflow'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.workflow');

Route::get('owner/rekapan', [OwnerDashboardController::class, 'rekapan'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.rekapan');

Route::get('owner/rekapan/{dokumen}/detail', [OwnerDashboardController::class, 'getDocumentDetail'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.rekapan.detail');

Route::get('owner/rekapan/by-handler/{handler}', [OwnerDashboardController::class, 'rekapanByHandler'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.rekapan.byHandler');

Route::get('owner/rekapan/detail/{type}', [OwnerDashboardController::class, 'rekapanDetail'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.rekapan.detailStats');

Route::get('owner/rekapan-keterlambatan', [OwnerDashboardController::class, 'rekapanKeterlambatan'])
    ->middleware('autologin', 'role:admin,owner')
    ->name('owner.rekapan-keterlambatan');

// Admin shortcut to Owner Dashboard
Route::get('admin/monitoring', [OwnerDashboardController::class, 'index'])
    ->middleware('autologin', 'role:admin')
    ->name('admin.monitoring');

// IbuA Routes (Dokumen)
Route::get('/dokumens', [DokumenController::class, 'index'])->name('dokumens.index');
Route::get('/dokumens/create', [DokumenController::class, 'create'])->name('dokumens.create');
Route::post('/dokumens', [DokumenController::class, 'store'])->name('dokumens.store');
Route::get('/dokumens/{dokumen}/edit', [DokumenController::class, 'edit'])->name('dokumens.edit');
Route::get('/dokumens/{dokumen}/detail-ibua', [DokumenController::class, 'getDocumentDetailForIbuA'])->name('dokumens.detail-ibua');
Route::get('/dokumens/{dokumen}/progress-ibua', [DokumenController::class, 'getDocumentProgressForIbuA'])->name('dokumens.progress-ibua');
Route::put('/dokumens/{dokumen}', [DokumenController::class, 'update'])->name('dokumens.update');
Route::delete('/dokumens/{dokumen}', [DokumenController::class, 'destroy'])->name('dokumens.destroy');
Route::post('/dokumens/{dokumen}/send-to-ibub', [DokumenController::class, 'sendToIbuB'])->name('dokumens.sendToIbuB');
Route::get('/rekapan', [DokumenRekapanController::class, 'index'])->name('rekapan.index');

// Autocomplete Routes
Route::get('/api/autocomplete/payment-recipients', [AutocompleteController::class, 'getPaymentRecipients'])->name('autocomplete.payment-recipients');
Route::get('/api/autocomplete/document-senders', [AutocompleteController::class, 'getDocumentSenders'])->name('autocomplete.document-senders');
Route::get('/api/autocomplete/document-descriptions', [AutocompleteController::class, 'getDocumentDescriptions'])->name('autocomplete.document-descriptions');
Route::get('/api/autocomplete/po-numbers', [AutocompleteController::class, 'getPONumbers'])->name('autocomplete.po-numbers');
Route::get('/api/autocomplete/pr-numbers', [AutocompleteController::class, 'getPRNumbers'])->name('autocomplete.pr-numbers');
Route::get('/pengembalian-dokumens', [PengembalianDokumenController::class, 'index']);
Route::get('diagram', [diagramController::class, 'index']);

// IbuB Routes
Route::get('/dokumensB', [DashboardBController::class, 'dokumens'])->name('dokumensB.index');
Route::get('/rekapan-ibuB', [DashboardBController::class, 'rekapan'])->name('dokumensB.rekapan');
Route::get('/dokumensB/{dokumen}/edit', [DashboardBController::class, 'editDokumen'])->name('dokumensB.edit');
Route::get('/dokumens/{dokumen}/detail', [DashboardBController::class, 'getDocumentDetail'])->name('dokumens.detail');
Route::put('/dokumensB/{dokumen}', [DashboardBController::class, 'updateDokumen'])->name('dokumensB.update');
Route::post('/dokumensB/{dokumen}/return-to-department', [DashboardBController::class, 'returnToDepartment'])->name('dokumensB.returnToDepartment');
Route::post('/dokumensB/{dokumen}/send-back-to-perpajakan', [DashboardBController::class, 'sendBackToPerpajakan'])->name('dokumensB.sendBackToPerpajakan');
Route::post('/dokumensB/{dokumen}/send-to-next', [DashboardBController::class, 'sendToNextHandler'])->name('dokumensB.sendToNext');
Route::post('/dokumensB/{dokumen}/send-to-target-department', [DashboardBController::class, 'sendToTargetDepartment'])->name('dokumensB.sendToTargetDepartment');
Route::post('/dokumensB/{dokumen}/set-deadline', [DashboardBController::class, 'setDeadline'])
    ->middleware(['autologin', 'role:ibub,admin'])
    ->name('dokumensB.setDeadline');
Route::get('/pengembalian-dokumensB', [DashboardBController::class, 'pengembalian'])->name('pengembalianB.index');
Route::get('/pengembalian-dokumens-ke-bagian/stats', [DashboardBController::class, 'getPengembalianKeBagianStats'])->name('pengembalianKeBagian.stats');
Route::get('/pengembalian-dokumens-ke-bidang', [DashboardBController::class, 'pengembalianKeBidang'])->name('pengembalianKeBidang.index');
Route::post('/dokumensB/{dokumen}/return-to-bidang', [DashboardBController::class, 'returnToBidang'])->name('dokumensB.returnToBidang');
Route::post('/dokumensB/{dokumen}/send-back-to-main-list', [DashboardBController::class, 'sendBackToMainList'])->name('dokumensB.sendBackToMainList');
Route::post('/dokumensB/{dokumen}/return-to-ibua', [DashboardBController::class, 'returnToIbuA'])->name('dokumensB.returnToIbuA');
Route::post('/dokumensB/{dokumen}/change-status', [DashboardBController::class, 'changeDocumentStatus'])->name('dokumensB.changeStatus');
Route::get('/diagramB', [DashboardBController::class, 'diagram'])->name('diagramB.index');

// IbuB - Document Approval Routes (NEW) - dengan autologin
Route::middleware(['autologin', 'role:ibub,admin'])->group(function () {
    Route::post('/ibub/dokumen/{dokumen}/accept', [DashboardBController::class, 'acceptDocument'])
        ->name('ibub.dokumen.accept');

    Route::post('/ibub/dokumen/{dokumen}/reject', [DashboardBController::class, 'rejectDocument'])
        ->name('ibub.dokumen.reject');

    Route::get('/ibub/pending-approval', [DashboardBController::class, 'pendingApproval'])
        ->name('ibub.pending.approval');
});

// Universal Approval Routes - Untuk semua user kecuali IbuA - dengan autologin
Route::middleware(['autologin'])->group(function () {
    Route::get('/daftar-masuk-dokumen', [\App\Http\Controllers\UniversalApprovalController::class, 'index'])
        ->name('universal.approval.index');

    Route::post('/universal-approval/{dokumen}/approve', [\App\Http\Controllers\UniversalApprovalController::class, 'approve'])
        ->name('universal.approval.approve');

    Route::post('/universal-approval/{dokumen}/reject', [\App\Http\Controllers\UniversalApprovalController::class, 'reject'])
        ->name('universal.approval.reject');

    Route::get('/universal-approval/{dokumen}/detail', [\App\Http\Controllers\UniversalApprovalController::class, 'getDetail'])
        ->name('universal.approval.detail');

    Route::get('/universal-approval/notifications', [\App\Http\Controllers\UniversalApprovalController::class, 'checkNotifications'])
        ->name('universal.approval.notifications');
});

// Pembayaran Routes
Route::get('/dokumensPembayaran', [DashboardPembayaranController::class, 'dokumens'])->name('dokumensPembayaran.index');
Route::get('/dokumensPembayaran/{dokumen}/detail', [DashboardPembayaranController::class, 'getDocumentDetail'])->name('dokumensPembayaran.detail');
Route::post('/dokumensPembayaran/{dokumen}/set-deadline', [DashboardPembayaranController::class, 'setDeadline'])->name('dokumensPembayaran.setDeadline');
Route::post('/dokumensPembayaran/{dokumen}/update-status', [DashboardPembayaranController::class, 'updateStatus'])->name('dokumensPembayaran.updateStatus');
Route::post('/dokumensPembayaran/{dokumen}/upload-bukti', [DashboardPembayaranController::class, 'uploadBukti'])->name('dokumensPembayaran.uploadBukti');
Route::get('/dokumensPembayaran/create', [DashboardPembayaranController::class, 'createDokumen'])->name('dokumensPembayaran.create');
Route::post('/dokumensPembayaran', [DashboardPembayaranController::class, 'storeDokumen'])->name('dokumensPembayaran.store');
Route::get('/dokumensPembayaran/{dokumen}/edit', [DashboardPembayaranController::class, 'editDokumen'])->name('dokumensPembayaran.edit');
Route::put('/dokumensPembayaran/{dokumen}', [DashboardPembayaranController::class, 'updateDokumen'])->name('dokumensPembayaran.update');
Route::delete('/dokumensPembayaran/{dokumen}', [DashboardPembayaranController::class, 'destroyDokumen'])->name('dokumensPembayaran.destroy');
Route::get('/pengembalian-dokumensPembayaran', [DashboardPembayaranController::class, 'pengembalian'])->name('pengembalianPembayaran.index');
Route::get('/rekapan-keterlambatan', [DashboardPembayaranController::class, 'rekapanKeterlambatan'])->name('rekapanKeterlambatan.index');
Route::get('/rekapan-pembayaran', [DashboardPembayaranController::class, 'rekapan'])->name('pembayaran.rekapan');
Route::get('/rekapan-pembayaran/export', [DashboardPembayaranController::class, 'exportRekapan'])->name('pembayaran.rekapan.export');
Route::get('/diagramPembayaran', [DashboardPembayaranController::class, 'diagram'])->name('diagramPembayaran.index');

// Akutansi Routes
Route::get('/dokumensAkutansi', [DashboardAkutansiController::class, 'dokumens'])->name('dokumensAkutansi.index');
Route::get('/dokumensAkutansi/create', [DashboardAkutansiController::class, 'createDokumen'])->name('dokumensAkutansi.create');
Route::post('/dokumensAkutansi', [DashboardAkutansiController::class, 'storeDokumen'])->name('dokumensAkutansi.store');
Route::get('/dokumensAkutansi/{dokumen}/edit', [DashboardAkutansiController::class, 'editDokumen'])->name('dokumensAkutansi.edit');
Route::get('/dokumensAkutansi/{dokumen}/detail', [DashboardAkutansiController::class, 'getDocumentDetail'])->name('dokumensAkutansi.detail');
Route::put('/dokumensAkutansi/{dokumen}', [DashboardAkutansiController::class, 'updateDokumen'])->name('dokumensAkutansi.update');
Route::delete('/dokumensAkutansi/{dokumen}', [DashboardAkutansiController::class, 'destroyDokumen'])->name('dokumensAkutansi.destroy');
Route::post('/dokumensAkutansi/{dokumen}/set-deadline', [DashboardAkutansiController::class, 'setDeadline'])
    ->middleware(['autologin', 'role:akutansi,admin'])
    ->name('dokumensAkutansi.setDeadline');
Route::post('/dokumensAkutansi/{dokumen}/send-to-pembayaran', [DashboardAkutansiController::class, 'sendToPembayaran'])->name('dokumensAkutansi.sendToPembayaran');
Route::get('/pengembalian-dokumensAkutansi', [DashboardAkutansiController::class, 'pengembalian'])->name('pengembalianAkutansi.index');
Route::get('/rekapan-akutansi', [DashboardAkutansiController::class, 'rekapan'])->name('akutansi.rekapan');
Route::get('/diagramAkutansi', [DashboardAkutansiController::class, 'diagram'])->name('diagramAkutansi.index');

// Perpajakan Routes
Route::get('/dokumensPerpajakan', [DashboardPerpajakanController::class, 'dokumens'])->name('dokumensPerpajakan.index');
Route::get('/dokumensPerpajakan/{dokumen}/detail', [DashboardPerpajakanController::class, 'getDocumentDetail'])->name('dokumensPerpajakan.detail');
Route::post('/dokumensPerpajakan/{dokumen}/set-deadline', [DashboardPerpajakanController::class, 'setDeadline'])->name('dokumensPerpajakan.setDeadline');
Route::get('/dokumensPerpajakan/{dokumen}/edit', [DashboardPerpajakanController::class, 'editDokumen'])->name('dokumensPerpajakan.edit');
Route::put('/dokumensPerpajakan/{dokumen}', [DashboardPerpajakanController::class, 'updateDokumen'])->name('dokumensPerpajakan.update');
Route::post('/dokumensPerpajakan/{dokumen}/send-to-akutansi', [DashboardPerpajakanController::class, 'sendToAkutansi'])->name('dokumensPerpajakan.sendToAkutansi');
Route::post('/dokumensPerpajakan/{dokumen}/return', [DashboardPerpajakanController::class, 'returnDocument'])->name('dokumensPerpajakan.return');
Route::get('/pengembalian-dokumensPerpajakan', [DashboardPerpajakanController::class, 'pengembalian'])->name('pengembalianPerpajakan.index');
Route::get('/diagramPerpajakan', [DashboardPerpajakanController::class, 'diagram'])->name('diagramPerpajakan.index');
Route::get('/rekapan-perpajakan', [DashboardPerpajakanController::class, 'rekapan'])->name('perpajakan.rekapan');

// Test route for broadcasting (remove in production)
Route::get('/test-broadcast', function() {
    $dokumen = \App\Models\Dokumen::where('current_handler', 'ibuB')
        ->orWhere('status', 'sent_to_ibub')
        ->latest()
        ->first();

    if (!$dokumen) {
        return response()->json([
            'error' => 'No document found for testing'
        ], 404);
    }

    try {
        broadcast(new \App\Events\DocumentSent($dokumen, 'test', 'ibuB'));
        \Log::info('Test broadcast sent', ['document_id' => $dokumen->id]);

        return response()->json([
            'success' => true,
            'message' => 'Test broadcast sent!',
            'document_id' => $dokumen->id,
            'channel' => 'documents.ibuB'
        ]);
    } catch (\Exception $e) {
        \Log::error('Test broadcast failed: ' . $e->getMessage());
        return response()->json([
            'error' => 'Broadcast failed: ' . $e->getMessage()
        ], 500);
    }
});

// Test route for returned documents broadcasting
Route::get('/test-returned-broadcast', function() {
    $dokumen = \App\Models\Dokumen::where('created_by', 'ibuA')
        ->where('status', 'returned_to_ibua')
        ->latest()
        ->first();

    if (!$dokumen) {
        return response()->json([
            'error' => 'No returned document found for testing'
        ], 404);
    }

    try {
        broadcast(new \App\Events\DocumentReturned($dokumen, $dokumen->alasan_pengembalian ?: 'Test alasan pengembalian', 'ibuB'));
        \Log::info('Test returned broadcast sent', ['document_id' => $dokumen->id]);

        return response()->json([
            'success' => true,
            'message' => 'Test returned broadcast sent!',
            'document_id' => $dokumen->id,
            'channel' => 'documents.ibuA'
        ]);
    } catch (\Exception $e) {
        \Log::error('Test returned broadcast failed: ' . $e->getMessage());
        return response()->json([
            'error' => 'Broadcast failed: ' . $e->getMessage()
        ], 500);
    }
});

// Simple test route to trigger notification without broadcast
Route::get('/test-trigger-notification', function() {
    // Update an existing document to trigger notification
    $dokumen = \App\Models\Dokumen::where('created_by', 'ibuA')
        ->where('status', 'returned_to_ibua')
        ->first();

    if (!$dokumen) {
        return response()->json([
            'error' => 'No returned document found'
        ]);
    }

    // Update the returned_at timestamp to make it look like a recent return
    $dokumen->update([
        'returned_to_ibua_at' => \Illuminate\Support\Carbon::now()->subMinutes(1),
        'alasan_pengembalian' => 'Test notification trigger at ' . \Illuminate\Support\Carbon::now()->format('H:i:s')
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Test notification triggered!',
        'document_id' => $dokumen->id,
        'returned_at' => $dokumen->returned_to_ibua_at,
    ]);
});

// Role Switching Routes - untuk development/testing
Route::middleware('autologin')->group(function () {
    // Quick role switching URLs
    Route::get('/switch-role/{role}', function($role) {
        // Logout user yang sedang login
        Auth::logout();

        // Validasi role
        $validRoles = ['IbuA', 'ibuB', 'Perpajakan', 'Akutansi', 'Pembayaran'];
        if (!in_array($role, $validRoles)) {
            $role = 'IbuA'; // Default ke IbuA jika role tidak valid
        }

        // Redirect dengan parameter role
        return redirect('/dashboard?role=' . $role);
    })->name('switch.role');

    // Development dashboard - auto-login berdasarkan parameter
    Route::get('/dev-dashboard/{role?}', function($role = 'IbuA') {
        $roleMap = [
            'IbuA' => '/dashboard',
            'ibuB' => '/dashboardB',
            'Perpajakan' => '/dashboardPerpajakan',
            'Akutansi' => '/dashboardAkutansi',
            'Pembayaran' => '/dashboardPembayaran'
        ];

        $url = $roleMap[$role] ?? '/dashboard';
        return redirect($url . '?role=' . $role);
    })->name('dev.dashboard');

    // Master development route - semua dashboard dengan role switching
    Route::get('/dev-all', function() {
        return view('dev.role-switcher');
    })->name('dev.all');
});
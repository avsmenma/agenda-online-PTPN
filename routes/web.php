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

// Custom broadcast authentication route - Define BEFORE broadcast routes
Route::post('/custom-broadcasting/auth', function (\Illuminate\Http\Request $request) {
    try {
        \Log::info('🔐 Custom broadcast auth attempt', [
            'channel_name' => $request->input('channel_name'),
            'socket_id' => $request->input('socket_id'),
            'user_authenticated' => auth()->check(),
            'session_id' => session()->getId(),
        ]);

        if (!auth()->check()) {
            \Log::error('❌ Broadcast auth failed: User not authenticated');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        // For development: always approve private channels for authenticated users
        if (str_starts_with($channelName, 'private-')) {
            $authData = $socketId . ':' . md5($socketId . ':' . config('broadcasting.connections.pusher.key', ''));

            \Log::info('✅ Custom broadcast auth successful', [
                'channel' => $channelName,
                'socket_id' => $socketId,
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role,
            ]);

            return response()->json(['auth' => $authData]);
        }

        \Log::warning('⚠️ Non-private channel request', ['channel' => $channelName]);
        return response()->json(['error' => 'Invalid channel type'], 400);

    } catch (\Exception $e) {
        \Log::error('💥 Custom broadcast auth error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Authentication failed'], 403);
    }
})->middleware(['web'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/', function () {
    return redirect('/login');
});

// Test routes for welcome messages
Route::get('/test-welcome/{module}', function ($module) {
    return view('test-welcome', ['module' => $module, 'title' => 'Testing Welcome Messages']);
});

Route::get('/simple-test', function () {
    $service = app('App\Services\WelcomeMessageService');
    $message = $service->getWelcomeMessage('IbuA');
    return "Welcome Message: " . $message;
});

Route::get('/api/welcome-message', [WelcomeMessageController::class, 'getMessage']);

// Broadcasting Authentication Route
Broadcast::routes(['middleware' => ['web']]);

// Professional API routes for document updates
Route::get('/api/documents/verifikasi/check-updates', function () {
    try {
        $lastChecked = request()->input('last_checked', 0);
        $lastCheckedDate = $lastChecked > 0 
            ? \Carbon\Carbon::createFromTimestamp($lastChecked)
            : \Carbon\Carbon::now()->subDays(1);

        // Cek dokumen yang berubah status setelah lastChecked
        // Beda antara dokumen baru dari IbuA vs dokumen yang sudah di-approve oleh Perpajakan/Akutansi/Pembayaran
        $newDocuments = \App\Models\Dokumen::where(function($query) use ($lastCheckedDate) {
                // Dokumen yang masih di ibuB dan updated setelah lastChecked (dokumen baru dari IbuA)
                $query->where(function($q) use ($lastCheckedDate) {
                    $q->where('current_handler', 'ibuB')
                      ->where('updated_at', '>', $lastCheckedDate)
                      ->whereIn('status', ['sent_to_ibub', 'sedang diproses', 'menunggu_di_approve']);
                })
                // Atau dokumen yang baru di-approve oleh perpajakan/akutansi/pembayaran setelah lastChecked
                ->orWhere(function($q) use ($lastCheckedDate) {
                    $q->whereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran'])
                      ->where('updated_at', '>', $lastCheckedDate);
                });
            })
            ->with(['roleData' => function($query) {
                $query->whereIn('role_code', ['ibub', 'perpajakan', 'akutansi', 'pembayaran']);
            }])
            ->with(['roleStatuses' => function($query) {
                $query->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran']);
            }])
            ->latest('updated_at')
            ->take(10)
            ->get();

        $totalDocuments = \App\Models\Dokumen::where(function($query) {
            $query->where('current_handler', 'ibuB')
                  ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
        })->count();

        return response()->json([
            'has_updates' => $newDocuments->count() > 0,
            'new_count' => $newDocuments->count(),
            'total_documents' => $totalDocuments,
            'new_documents' => $newDocuments->map(function ($doc) {
                $roleData = $doc->roleData->firstWhere('role_code', 'ibub');
                
                // Tentukan apakah ini dokumen baru dari IbuA atau dokumen yang sudah di-approve
                $isNewFromIbuA = $doc->current_handler === 'ibuB' && 
                                 in_array($doc->status, ['sent_to_ibub', 'sedang diproses', 'menunggu_di_approve']);
                
                // Cek apakah dokumen sudah di-approve oleh Perpajakan/Akutansi/Pembayaran
                $approvedBy = null;
                $approvedAt = null;
                if (!$isNewFromIbuA && in_array($doc->status, ['sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran'])) {
                    // Cek status dari role yang approve
                    if ($doc->status === 'sent_to_perpajakan') {
                        $perpajakanStatus = $doc->roleStatuses->firstWhere('role_code', 'perpajakan');
                        if ($perpajakanStatus && $perpajakanStatus->status === 'approved') {
                            $approvedBy = 'Perpajakan';
                            $approvedAt = $perpajakanStatus->status_changed_at?->format('d/m/Y H:i') ?? $doc->updated_at->format('d/m/Y H:i');
                        }
                    } elseif ($doc->status === 'sent_to_akutansi') {
                        $akutansiStatus = $doc->roleStatuses->firstWhere('role_code', 'akutansi');
                        if ($akutansiStatus && $akutansiStatus->status === 'approved') {
                            $approvedBy = 'Akutansi';
                            $approvedAt = $akutansiStatus->status_changed_at?->format('d/m/Y H:i') ?? $doc->updated_at->format('d/m/Y H:i');
                        }
                    } elseif ($doc->status === 'sent_to_pembayaran') {
                        $pembayaranStatus = $doc->roleStatuses->firstWhere('role_code', 'pembayaran');
                        if ($pembayaranStatus && $pembayaranStatus->status === 'approved') {
                            $approvedBy = 'Pembayaran';
                            $approvedAt = $pembayaranStatus->status_changed_at?->format('d/m/Y H:i') ?? $doc->updated_at->format('d/m/Y H:i');
                        }
                    }
                }
                
                return [
                    'id' => $doc->id,
                    'nomor_agenda' => $doc->nomor_agenda,
                    'nomor_spp' => $doc->nomor_spp,
                    'uraian_spp' => $doc->uraian_spp,
                    'nilai_rupiah' => $doc->nilai_rupiah,
                    'status' => $doc->status,
                    'sent_at' => $roleData?->received_at?->format('d/m/Y H:i') ?? $doc->updated_at->format('d/m/Y H:i'),
                    'is_new_from_ibua' => $isNewFromIbuA,
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                ];
            }),
            'last_checked' => time()
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in api/documents/verifikasi/check-updates: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error' => true,
            'message' => 'Failed to check updates: ' . $e->getMessage()
        ], 500);
    }
})->name('api.documents.verifikasi.check-updates');

Route::get('/api/documents/perpajakan/check-updates', [DashboardPerpajakanController::class, 'checkUpdates'])
    ->name('api.documents.perpajakan.check-updates');
Route::get('/api/documents/akutansi/check-updates', [DashboardAkutansiController::class, 'checkUpdates'])
    ->name('api.documents.akutansi.check-updates');
Route::get('/api/documents/pembayaran/check-updates', [DashboardPembayaranController::class, 'checkUpdates'])
    ->name('api.documents.pembayaran.check-updates');

// Backward compatibility for old check-updates routes
Route::get('/dokumensB/check-updates', function () {
    return redirect()->route('api.documents.verifikasi.check-updates', request()->query(), 301);
})->name('dokumensB.check-updates.old');
Route::get('/perpajakan/check-updates', function () {
    return redirect()->route('api.documents.perpajakan.check-updates', request()->query(), 301);
})->name('perpajakan.check-updates.old');
Route::get('/akutansi/check-updates', function () {
    return redirect()->route('api.documents.akutansi.check-updates', request()->query(), 301);
})->name('akutansi.check-updates.old');
Route::get('/pembayaran/check-updates', function () {
    return redirect()->route('api.documents.pembayaran.check-updates', request()->query(), 301);
})->name('pembayaran.check-updates.old');


// Dashboard routes with role protection - Professional URLs
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware('autologin', 'role:admin,ibua')
    ->name('dashboard.main');

// Professional dashboard routes
Route::get('dashboard/verifikasi', [DashboardBController::class, 'index'])
    ->middleware('autologin', 'role:admin,ibub')
    ->name('dashboard.verifikasi');

Route::get('dashboard/pembayaran', [DashboardPembayaranController::class, 'index'])
    ->middleware('autologin', 'role:admin,Pembayaran')
    ->name('dashboard.pembayaran');

Route::get('dashboard/akutansi', [DashboardAkutansiController::class, 'index'])
    ->middleware('autologin', 'role:admin,akutansi')
    ->name('dashboard.akutansi');

Route::get('dashboard/perpajakan', [DashboardPerpajakanController::class, 'index'])
    ->middleware('autologin', 'role:admin,perpajakan')
    ->name('dashboard.perpajakan');

// Dashboard for Verifikasi role (future implementation)
Route::get('dashboard/verifikasi-role', function () {
    return view('verifikasi.dashboard');
})->middleware('role:admin,verifikasi')
    ->name('dashboard.verifikasi-role');

// Backward compatibility - redirect old URLs to new professional URLs
Route::get('dashboardB', function () {
    return redirect()->route('dashboard.verifikasi', [], 301);
})->name('dashboard.ibub.old');

Route::get('dashboardPembayaran', function () {
    return redirect()->route('dashboard.pembayaran', [], 301);
})->name('dashboard.pembayaran.old');

Route::get('dashboardAkutansi', function () {
    return redirect()->route('dashboard.akutansi', [], 301);
})->name('dashboard.akutansi.old');

Route::get('dashboardPerpajakan', function () {
    return redirect()->route('dashboard.perpajakan', [], 301);
})->name('dashboard.perpajakan.old');

Route::get('dashboardVerifikasi', function () {
    return redirect()->route('dashboard.verifikasi-role', [], 301);
})->name('dashboard.verifikasi.old');

// Professional API routes for rejected documents
Route::get('/api/documents/rejected/check', [DashboardController::class, 'checkRejectedDocuments'])
    ->middleware('autologin', 'role:admin,ibua')
    ->name('api.documents.rejected.check');
Route::get('/api/documents/rejected/{dokumen}', [DashboardController::class, 'showRejectedDocument'])
    ->middleware('autologin', 'role:admin,ibua')
    ->name('api.documents.rejected.show');
Route::get('/api/documents/verifikasi/rejected/check', [DashboardBController::class, 'checkRejectedDocuments'])
    ->middleware('autologin', 'role:admin,ibub')
    ->name('api.documents.verifikasi.rejected.check');
Route::get('/api/documents/verifikasi/rejected/{dokumen}', [DashboardBController::class, 'showRejectedDocument'])
    ->middleware('autologin', 'role:admin,ibub')
    ->name('api.documents.verifikasi.rejected.show');

// Backward compatibility for old rejected document routes
Route::get('/ibua/check-rejected', function () {
    return redirect()->route('api.documents.rejected.check', [], 301);
})->name('ibua.checkRejected.old');
Route::get('/ibua/rejected/{dokumen}', function ($dokumen) {
    return redirect()->route('api.documents.rejected.show', ['dokumen' => $dokumen], 301);
})->name('ibua.rejected.show.old');
Route::get('/ibub/check-rejected', function () {
    return redirect()->route('api.documents.verifikasi.rejected.check', [], 301);
})->name('ibub.checkRejected.old');
Route::get('/ibub/rejected/{dokumen}', function ($dokumen) {
    return redirect()->route('api.documents.verifikasi.rejected.show', ['dokumen' => $dokumen], 301);
})->name('ibub.rejected.show.old');

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
    ->middleware('autologin', 'role:admin,owner,Pembayaran')
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

// Professional Document Routes - IbuA (Owner)
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DokumenController::class, 'index'])->name('index');
    Route::get('/create', [DokumenController::class, 'create'])->name('create');
    Route::post('/', [DokumenController::class, 'store'])->name('store');
    Route::get('/{dokumen}/edit', [DokumenController::class, 'edit'])->name('edit');
    Route::get('/{dokumen}/detail', [DokumenController::class, 'getDocumentDetail'])->name('detail');
    Route::get('/{dokumen}/progress', [DokumenController::class, 'getDocumentProgressForIbuA'])->name('progress');
    Route::put('/{dokumen}', [DokumenController::class, 'update'])->name('update');
    Route::delete('/{dokumen}', [DokumenController::class, 'destroy'])->name('destroy');
    Route::post('/{dokumen}/send-to-verifikasi', [DokumenController::class, 'sendToIbuB'])
        ->middleware(['autologin', 'role:ibuA,admin'])
        ->name('send-to-verifikasi');
    Route::post('/{dokumen}/approve', [DokumenController::class, 'approveDocument'])
        ->middleware(['autologin', 'role:ibuB,admin'])
        ->name('approve');
});

// Professional Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [DokumenRekapanController::class, 'index'])->name('index');
    Route::get('/analytics', [DokumenRekapanController::class, 'analytics'])->name('analytics');
});

// Backward compatibility for old document routes
Route::get('/dokumens', function () {
    return redirect()->route('documents.index', [], 301);
})->name('dokumens.index.old');
Route::get('/dokumens/create', function () {
    return redirect()->route('documents.create', [], 301);
})->name('dokumens.create.old');
Route::get('/dokumens/{dokumen}/edit', function ($dokumen) {
    return redirect()->route('documents.edit', ['dokumen' => $dokumen], 301);
})->name('dokumens.edit.old');
Route::get('/dokumens/{dokumen}/detail', function ($dokumen) {
    return redirect()->route('documents.detail', ['dokumen' => $dokumen], 301);
})->name('dokumens.detail.old');
Route::get('/rekapan', function () {
    return redirect()->route('reports.index', [], 301);
})->name('rekapan.index.old');
Route::get('/rekapan/analytics', function () {
    return redirect()->route('reports.analytics', [], 301);
})->name('rekapan.analytics.old');

// Autocomplete Routes
Route::get('/api/autocomplete/payment-recipients', [AutocompleteController::class, 'getPaymentRecipients'])->name('autocomplete.payment-recipients');
Route::get('/api/autocomplete/document-senders', [AutocompleteController::class, 'getDocumentSenders'])->name('autocomplete.document-senders');
Route::get('/api/autocomplete/document-descriptions', [AutocompleteController::class, 'getDocumentDescriptions'])->name('autocomplete.document-descriptions');
Route::get('/api/autocomplete/po-numbers', [AutocompleteController::class, 'getPONumbers'])->name('autocomplete.po-numbers');
Route::get('/api/autocomplete/pr-numbers', [AutocompleteController::class, 'getPRNumbers'])->name('autocomplete.pr-numbers');
Route::get('/pengembalian-dokumens', [PengembalianDokumenController::class, 'index']);
Route::get('diagram', [diagramController::class, 'index']);

// Professional Document Routes - Verifikasi (IbuB)
Route::prefix('documents/verifikasi')->name('documents.verifikasi.')->group(function () {
    Route::get('/', [DashboardBController::class, 'dokumens'])->name('index');
    Route::get('/{dokumen}/detail', [DashboardBController::class, 'getDocumentDetail'])->name('detail');
    Route::get('/{dokumen}/edit', [DashboardBController::class, 'editDokumen'])->name('edit');
    Route::put('/{dokumen}', [DashboardBController::class, 'updateDokumen'])->name('update');
    Route::post('/{dokumen}/return-to-department', [DashboardBController::class, 'returnToDepartment'])->name('return-to-department');
    Route::post('/{dokumen}/send-to-next', [DashboardBController::class, 'sendToNextHandler'])->name('send-to-next');
    Route::post('/{dokumen}/set-deadline', [DashboardBController::class, 'setDeadline'])
        ->middleware(['autologin', 'role:ibub,admin'])
        ->name('set-deadline');
    Route::post('/{dokumen}/return-to-owner', [DashboardBController::class, 'returnToIbuA'])->name('return-to-owner');
    Route::post('/{dokumen}/change-status', [DashboardBController::class, 'changeDocumentStatus'])->name('change-status');
});

// Professional Reports Routes - Verifikasi
Route::prefix('reports/verifikasi')->name('reports.verifikasi.')->group(function () {
    Route::get('/', [DashboardBController::class, 'rekapan'])->name('index');
    Route::get('/analytics', [DashboardBController::class, 'rekapanAnalytics'])
        ->middleware(['autologin', 'role:ibuB,admin'])
        ->name('analytics');
});

// Professional Returns Routes - Verifikasi
Route::prefix('returns/verifikasi')->name('returns.verifikasi.')->group(function () {
    Route::get('/', [DashboardBController::class, 'pengembalian'])->name('index');
    Route::get('/stats', [DashboardBController::class, 'getPengembalianKeBagianStats'])->name('stats');
    Route::get('/bidang', [DashboardBController::class, 'pengembalianKeBidang'])->name('bidang');
});

// Professional Diagram Routes - Verifikasi
Route::get('/reports/verifikasi/diagram', [DashboardBController::class, 'diagram'])->name('reports.verifikasi.diagram');

// Backward compatibility for old IbuB routes
Route::get('/dokumensB', function () {
    return redirect()->route('documents.verifikasi.index', [], 301);
})->name('dokumensB.index.old');
Route::get('/rekapan-ibuB', function () {
    return redirect()->route('reports.verifikasi.index', [], 301);
})->name('dokumensB.rekapan.old');
Route::get('/pengembalian-dokumensB', function () {
    return redirect()->route('returns.verifikasi.index', [], 301);
})->name('pengembalianB.index.old');
Route::get('/diagramB', function () {
    return redirect()->route('reports.verifikasi.diagram', [], 301);
})->name('diagramB.index.old');

// Professional Approval Routes - Verifikasi (IbuB)
Route::middleware(['autologin', 'role:ibub,admin'])->prefix('documents/verifikasi')->name('documents.verifikasi.')->group(function () {
    Route::post('/{dokumen}/accept', [DashboardBController::class, 'acceptDocument'])
        ->name('accept');
    Route::post('/{dokumen}/reject', [DashboardBController::class, 'rejectDocument'])
        ->name('reject');
    Route::get('/pending-approval', [DashboardBController::class, 'pendingApproval'])
        ->name('pending-approval');
});

// Backward compatibility for old IbuB approval routes
Route::post('/ibub/dokumen/{dokumen}/accept', function ($dokumen) {
    return redirect()->route('documents.verifikasi.accept', ['dokumen' => $dokumen], 301);
})->name('ibub.dokumen.accept.old');
Route::post('/ibub/dokumen/{dokumen}/reject', function ($dokumen) {
    return redirect()->route('documents.verifikasi.reject', ['dokumen' => $dokumen], 301);
})->name('ibub.dokumen.reject.old');
Route::get('/ibub/pending-approval', function () {
    return redirect()->route('documents.verifikasi.pending-approval', [], 301);
})->name('ibub.pending.approval.old');

// Universal Approval Routes - Untuk semua user kecuali IbuA - dengan autologin
Route::middleware(['autologin'])->group(function () {
    Route::post('/universal-approval/{dokumen}/approve', [\App\Http\Controllers\InboxController::class, 'approve'])
        ->name('universal.approval.approve');

    Route::post('/universal-approval/{dokumen}/reject', [\App\Http\Controllers\InboxController::class, 'reject'])
        ->name('universal.approval.reject');

    Route::get('/universal-approval/{dokumen}/detail', [\App\Http\Controllers\UniversalApprovalController::class, 'getDetail'])
        ->name('universal.approval.detail');

    Route::get('/universal-approval/notifications', [\App\Http\Controllers\UniversalApprovalController::class, 'checkNotifications'])
        ->name('universal.approval.notifications');
});

// Inbox Routes - Untuk IbuB, Perpajakan, Akutansi, Pembayaran
Route::middleware(['autologin', 'role:IbuB,Perpajakan,Akutansi,Pembayaran,admin'])->group(function () {
    Route::get('/inbox', [\App\Http\Controllers\InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/check-new', [\App\Http\Controllers\InboxController::class, 'checkNewDocuments'])->name('inbox.checkNew');
    Route::get('/inbox/{dokumen}', [\App\Http\Controllers\InboxController::class, 'show'])->name('inbox.show');
    Route::post('/inbox/{dokumen}/approve', [\App\Http\Controllers\InboxController::class, 'approve'])->name('inbox.approve');
    Route::post('/inbox/{dokumen}/reject', [\App\Http\Controllers\InboxController::class, 'reject'])->name('inbox.reject');
});

// Professional Document Routes - Pembayaran
Route::prefix('documents/pembayaran')->name('documents.pembayaran.')->group(function () {
    Route::get('/', [DashboardPembayaranController::class, 'dokumens'])->name('index');
    Route::get('/{dokumen}/detail', [DashboardPembayaranController::class, 'getDocumentDetail'])->name('detail');
    Route::get('/{dokumen}/payment-data', [DashboardPembayaranController::class, 'getPaymentData'])->name('payment-data');
    Route::post('/{dokumen}/set-deadline', [DashboardPembayaranController::class, 'setDeadline'])->name('set-deadline');
    Route::post('/{dokumen}/update-status', [DashboardPembayaranController::class, 'updateStatus'])->name('update-status');
    Route::post('/{dokumen}/upload-proof', [DashboardPembayaranController::class, 'uploadBukti'])->name('upload-proof');
    Route::get('/create', [DashboardPembayaranController::class, 'createDokumen'])->name('create');
    Route::post('/', [DashboardPembayaranController::class, 'storeDokumen'])->name('store');
    Route::get('/{dokumen}/edit', [DashboardPembayaranController::class, 'editDokumen'])->name('edit');
    Route::put('/{dokumen}', [DashboardPembayaranController::class, 'updateDokumen'])->name('update');
    Route::delete('/{dokumen}', [DashboardPembayaranController::class, 'destroyDokumen'])->name('destroy');
});

// Professional Reports Routes - Pembayaran
Route::prefix('reports/pembayaran')->name('reports.pembayaran.')->group(function () {
    Route::get('/', [DashboardPembayaranController::class, 'rekapan'])->name('index');
    Route::get('/export', [DashboardPembayaranController::class, 'exportRekapan'])->name('export');
    Route::get('/delays', [DashboardPembayaranController::class, 'rekapanKeterlambatan'])->name('delays');
    Route::get('/tu-tk', [DashboardPembayaranController::class, 'rekapanTuTk'])->name('tu-tk');
    Route::get('/tu-tk/export', [DashboardPembayaranController::class, 'exportRekapanTuTk'])->name('tu-tk.export');
    Route::get('/analytics', [DashboardPembayaranController::class, 'analytics'])->name('analytics');
});

// Professional Returns Routes - Pembayaran
Route::get('/returns/pembayaran', [DashboardPembayaranController::class, 'pengembalian'])->name('returns.pembayaran.index');

// Professional Diagram Routes - Pembayaran
Route::get('/reports/pembayaran/diagram', [DashboardPembayaranController::class, 'diagram'])->name('reports.pembayaran.diagram');

// Backward compatibility for old Pembayaran routes
Route::get('/dokumensPembayaran', function () {
    return redirect()->route('documents.pembayaran.index', [], 301);
})->name('dokumensPembayaran.index.old');
Route::get('/rekapan-pembayaran', function () {
    return redirect()->route('reports.pembayaran.index', [], 301);
})->name('pembayaran.rekapan.old');
Route::get('/rekapan-keterlambatan', function () {
    return redirect()->route('reports.pembayaran.delays', [], 301);
})->name('rekapanKeterlambatan.index.old');
Route::get('/diagramPembayaran', function () {
    return redirect()->route('reports.pembayaran.diagram', [], 301);
})->name('diagramPembayaran.index.old');
Route::get('/dokumensPembayaran/dokumens', [DashboardPembayaranController::class, 'dokumens'])->name('dokumensPembayaran.dokumens');
Route::get('/payment/analytics', [DashboardPembayaranController::class, 'analytics'])->name('pembayaran.analytics');
Route::get('/dokumensPembayaran/{dokumen}/detail', [DashboardPembayaranController::class, 'getDocumentDetail'])->name('dokumensPembayaran.detail');
Route::get('/dokumensPembayaran/{dokumen}/get-payment-data', [DashboardPembayaranController::class, 'getPaymentData'])->name('dokumensPembayaran.getPaymentData');
Route::post('/dokumensPembayaran/{dokumen}/set-deadline', [DashboardPembayaranController::class, 'setDeadline'])->name('dokumensPembayaran.setDeadline');
Route::post('/dokumensPembayaran/{dokumen}/update-status', [DashboardPembayaranController::class, 'updateStatus'])->name('dokumensPembayaran.updateStatus');
Route::post('/dokumensPembayaran/{dokumen}/upload-bukti', [DashboardPembayaranController::class, 'uploadBukti'])->name('dokumensPembayaran.uploadBukti');
Route::post('/dokumensPembayaran/{dokumen}/update-pembayaran', [DashboardPembayaranController::class, 'updatePembayaran'])->name('dokumensPembayaran.updatePembayaran');
Route::get('/dokumensPembayaran/create', [DashboardPembayaranController::class, 'createDokumen'])->name('dokumensPembayaran.create');
Route::post('/dokumensPembayaran', [DashboardPembayaranController::class, 'storeDokumen'])->name('dokumensPembayaran.store');
Route::get('/dokumensPembayaran/{dokumen}/edit', [DashboardPembayaranController::class, 'editDokumen'])->name('dokumensPembayaran.edit');
Route::put('/dokumensPembayaran/{dokumen}', [DashboardPembayaranController::class, 'updateDokumen'])->name('dokumensPembayaran.update');
Route::delete('/dokumensPembayaran/{dokumen}', [DashboardPembayaranController::class, 'destroyDokumen'])->name('dokumensPembayaran.destroy');
Route::get('/pengembalian-dokumensPembayaran', [DashboardPembayaranController::class, 'pengembalian'])->name('pengembalianPembayaran.index');
Route::get('/rekapan-keterlambatan', [DashboardPembayaranController::class, 'rekapanKeterlambatan'])->name('rekapanKeterlambatan.index');
Route::get('/rekapan-pembayaran', [DashboardPembayaranController::class, 'rekapan'])->name('pembayaran.rekapan');
Route::get('/rekapan-pembayaran/export', [DashboardPembayaranController::class, 'exportRekapan'])->name('pembayaran.rekapan.export');
Route::get('/rekapan-tu-tk', [DashboardPembayaranController::class, 'rekapanTuTk'])->name('pembayaran.rekapanTuTk');
Route::post('/rekapan-tu-tk/payment-installment', [DashboardPembayaranController::class, 'storePaymentInstallment'])->name('pembayaran.storePaymentInstallment');
Route::post('/rekapan-tu-tk/payment-installment-batch', [DashboardPembayaranController::class, 'storePaymentInstallmentBatch'])->name('pembayaran.storePaymentInstallmentBatch');
Route::get('/rekapan-tu-tk/payment-logs/{kontrol}', [DashboardPembayaranController::class, 'getPaymentLogs'])->name('pembayaran.getPaymentLogs');
Route::get('/rekapan-tu-tk/payment-logs-by-agenda/{agenda}', [DashboardPembayaranController::class, 'getPaymentLogsByAgenda'])->name('pembayaran.getPaymentLogsByAgenda');
Route::get('/rekapan-tu-tk/position-timeline/{kontrol}', [DashboardPembayaranController::class, 'getPositionTimeline'])->name('pembayaran.getPositionTimeline');
Route::post('/rekapan-tu-tk/update-position', [DashboardPembayaranController::class, 'updateDocumentPosition'])->name('pembayaran.updateDocumentPosition');
Route::get('/rekapan-tu-tk/export', [DashboardPembayaranController::class, 'exportRekapanTuTk'])->name('pembayaran.exportRekapanTuTk');

// Dashboard Pembayaran Routes
Route::middleware('auth')->prefix('dashboard-pembayaran')->name('dashboard-pembayaran.')->group(function () {
    Route::get('/', [DashboardPembayaranController::class, 'index'])->name('index');
    Route::get('/import', [DashboardPembayaranController::class, 'showImportForm'])->name('import');
    Route::post('/import-csv', [DashboardPembayaranController::class, 'importCsv'])->name('import-csv');
    Route::get('/download-csv-template', [DashboardPembayaranController::class, 'downloadCsvTemplate'])->name('download-csv-template');
    Route::post('/check-updates', [DashboardPembayaranController::class, 'checkUpdates']);
});
Route::get('/diagramPembayaran', [DashboardPembayaranController::class, 'diagram'])->name('diagramPembayaran.index');

// Professional Document Routes - Akutansi
Route::prefix('documents/akutansi')->name('documents.akutansi.')->group(function () {
    Route::get('/', [DashboardAkutansiController::class, 'dokumens'])->name('index');
    Route::get('/create', [DashboardAkutansiController::class, 'createDokumen'])->name('create');
    Route::post('/', [DashboardAkutansiController::class, 'storeDokumen'])->name('store');
    Route::get('/{dokumen}/edit', [DashboardAkutansiController::class, 'editDokumen'])->name('edit');
    Route::get('/{dokumen}/detail', [DashboardAkutansiController::class, 'getDocumentDetail'])->name('detail');
    Route::put('/{dokumen}', [DashboardAkutansiController::class, 'updateDokumen'])->name('update');
    Route::delete('/{dokumen}', [DashboardAkutansiController::class, 'destroyDokumen'])->name('destroy');
    Route::post('/{dokumen}/set-deadline', [DashboardAkutansiController::class, 'setDeadline'])
        ->middleware(['autologin', 'role:akutansi,admin'])
        ->name('set-deadline');
    Route::post('/{dokumen}/send-to-pembayaran', [DashboardAkutansiController::class, 'sendToPembayaran'])->name('send-to-pembayaran');
    Route::post('/{dokumen}/return', [DashboardAkutansiController::class, 'returnDocument'])->name('return');
});

// Professional Reports Routes - Akutansi
Route::prefix('reports/akutansi')->name('reports.akutansi.')->group(function () {
    Route::get('/', [DashboardAkutansiController::class, 'rekapan'])->name('index');
});

// Professional Returns Routes - Akutansi
Route::get('/returns/akutansi', [DashboardAkutansiController::class, 'pengembalian'])->name('returns.akutansi.index');

// Professional Diagram Routes - Akutansi
Route::get('/reports/akutansi/diagram', [DashboardAkutansiController::class, 'diagram'])->name('reports.akutansi.diagram');

// Backward compatibility for old Akutansi routes
Route::get('/dokumensAkutansi', function () {
    return redirect()->route('documents.akutansi.index', [], 301);
})->name('dokumensAkutansi.index.old');
Route::get('/rekapan-akutansi', function () {
    return redirect()->route('reports.akutansi.index', [], 301);
})->name('akutansi.rekapan.old');
Route::get('/pengembalian-dokumensAkutansi', function () {
    return redirect()->route('returns.akutansi.index', [], 301);
})->name('pengembalianAkutansi.index.old');
Route::get('/diagramAkutansi', function () {
    return redirect()->route('reports.akutansi.diagram', [], 301);
})->name('diagramAkutansi.index.old');

// Professional Document Routes - Perpajakan
Route::prefix('documents/perpajakan')->name('documents.perpajakan.')->group(function () {
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
Route::prefix('reports/perpajakan')->name('reports.perpajakan.')->group(function () {
    Route::get('/', [DashboardPerpajakanController::class, 'rekapan'])->name('index');
    Route::get('/export', [DashboardPerpajakanController::class, 'exportView'])->name('export');
    Route::get('/export/download', [DashboardPerpajakanController::class, 'exportData'])->name('export.download');
});

// Professional Returns Routes - Perpajakan
Route::get('/returns/perpajakan', [DashboardPerpajakanController::class, 'pengembalian'])->name('returns.perpajakan.index');

// Professional Diagram Routes - Perpajakan
Route::get('/reports/perpajakan/diagram', [DashboardPerpajakanController::class, 'diagram'])->name('reports.perpajakan.diagram');

// Backward compatibility for old Perpajakan routes
Route::get('/dokumensPerpajakan', function () {
    return redirect()->route('documents.perpajakan.index', [], 301);
})->name('dokumensPerpajakan.index.old');
Route::get('/rekapan-perpajakan', function () {
    return redirect()->route('reports.perpajakan.index', [], 301);
})->name('perpajakan.rekapan.old');
Route::get('/export-perpajakan', function () {
    return redirect()->route('reports.perpajakan.export', [], 301);
})->name('perpajakan.export.old');
Route::get('/pengembalian-dokumensPerpajakan', function () {
    return redirect()->route('returns.perpajakan.index', [], 301);
})->name('pengembalianPerpajakan.index.old');
Route::get('/diagramPerpajakan', function () {
    return redirect()->route('reports.perpajakan.diagram', [], 301);
})->name('diagramPerpajakan.index.old');

// Test route for broadcasting (remove in production)
Route::get('/test-broadcast', function () {
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
Route::get('/test-returned-broadcast', function () {
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

// Test route to check broadcast authentication
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
})->middleware('autologin');

// Simple test route to trigger notification without broadcast
Route::get('/test-trigger-notification', function () {
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
    Route::get('/switch-role/{role}', function ($role) {
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
    Route::get('/dev-dashboard/{role?}', function ($role = 'IbuA') {
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
    Route::get('/dev-all', function () {
        return view('dev.role-switcher');
    })->name('dev.all');
});
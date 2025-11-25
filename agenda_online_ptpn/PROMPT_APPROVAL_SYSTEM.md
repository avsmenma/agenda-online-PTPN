# PROMPT UNTUK AI CODING: SISTEM APPROVAL PENGIRIMAN DOKUMEN

## KONTEKS PROJECT
Project Laravel untuk manajemen dokumen PTPN dengan workflow:
- IbuA (pengirim) → IbuB (penerima) → Perpajakan/Akutansi/Pembayaran
- File path: `/home/claude/agenda_online_ptpn`
- Database: SQLite (`database/database.sqlite`)
- Model utama: `app/Models/Dokumen.php`
- Controller: `app/Http/Controllers/DokumenController.php` dan `DashboardBController.php`

## MASALAH SAAT INI
Ketika IbuA mengirim dokumen menggunakan method `sendToIbuB()`:
1. Status langsung berubah `sent_to_ibub`
2. `current_handler` langsung pindah ke `ibuB`
3. IbuB langsung bisa lihat dan proses dokumen tanpa approval
4. Tidak ada mekanisme "izin masuk" seperti ketuk pintu rumah

## REQUIREMENT BARU: SISTEM APPROVAL
Implementasikan sistem approval sebelum dokumen benar-benar masuk ke penerima:

### KONSEP "KETUK PINTU"
1. **Pengirim mengirim dokumen** → Dokumen dalam status "PENDING_APPROVAL" (menunggu di pintu)
2. **Penerima melihat notifikasi** → Ada dokumen yang menunggu approval
3. **Penerima bisa:**
   - **ACCEPT/TERIMA** → Dokumen masuk ke sistem penerima
   - **REJECT/TOLAK** → Dokumen kembali ke pengirim dengan alasan

### WORKFLOW YANG DIINGINKAN

#### FLOW 1: IbuA → IbuB
```
[IbuA] Kirim Dokumen
   ↓
[Status: pending_approval_ibub]
[current_handler: ibuA] (masih di pengirim)
   ↓
[IbuB] Lihat Notifikasi → Ada dokumen menunggu approval
   ↓
[IbuB] Pilihan:
   ├─ ACCEPT → [Status: sent_to_ibub, current_handler: ibuB]
   └─ REJECT → [Status: draft, current_handler: ibuA] + alasan penolakan
```

#### FLOW 2: IbuB → Perpajakan
```
[IbuB] Kirim ke Perpajakan
   ↓
[Status: pending_approval_perpajakan]
[current_handler: ibuB]
   ↓
[Perpajakan] Lihat Notifikasi
   ↓
[Perpajakan] Pilihan:
   ├─ ACCEPT → [Status: sent_to_perpajakan, current_handler: perpajakan]
   └─ REJECT → [Status: sedang diproses, current_handler: ibuB] + alasan
```

#### FLOW 3: IbuB → Akutansi
```
[IbuB] Kirim ke Akutansi
   ↓
[Status: pending_approval_akutansi]
[current_handler: ibuB]
   ↓
[Akutansi] Lihat Notifikasi
   ↓
[Akutansi] Pilihan:
   ├─ ACCEPT → [Status: sent_to_akutansi, current_handler: akutansi]
   └─ REJECT → [Status: sedang diproses, current_handler: ibuB] + alasan
```

## TASK IMPLEMENTASI

### TASK 1: UPDATE DATABASE SCHEMA
Tambahkan kolom baru di tabel `dokumens`:

```php
// Migration file: database/migrations/YYYY_MM_DD_HHMMSS_add_approval_system_to_dokumens.php

Schema::table('dokumens', function (Blueprint $table) {
    // Tracking siapa yang akan menerima dokumen (pending approval)
    $table->string('pending_approval_for')->nullable()->after('current_handler');
    
    // Timestamp ketika dokumen dikirim untuk approval
    $table->timestamp('pending_approval_at')->nullable()->after('pending_approval_for');
    
    // Timestamp ketika approval diterima/ditolak
    $table->timestamp('approval_responded_at')->nullable()->after('pending_approval_at');
    
    // User yang merespon approval (accept/reject)
    $table->string('approval_responded_by')->nullable()->after('approval_responded_at');
    
    // Alasan jika ditolak
    $table->text('approval_rejection_reason')->nullable()->after('approval_responded_by');
});
```

### TASK 2: UPDATE STATUS ENUM
Tambahkan status baru untuk pending approval:

```php
// Migration file: database/migrations/YYYY_MM_DD_HHMMSS_add_pending_approval_statuses.php

Schema::table('dokumens', function (Blueprint $table) {
    $table->enum('status', [
        'draft',
        'pending_approval_ibub',        // NEW: Menunggu IbuB terima
        'sent_to_ibub',
        'sedang diproses',
        'pending_approval_perpajakan',  // NEW: Menunggu Perpajakan terima
        'sent_to_perpajakan',
        'pending_approval_akutansi',    // NEW: Menunggu Akutansi terima
        'sent_to_akutansi',
        'approved_ibub',
        'rejected_ibub',
        'returned_to_ibua',
        'returned_to_department',
        'returned_to_bidang',
        'selesai'
    ])->change();
});
```

### TASK 3: UPDATE MODEL DOKUMEN
Tambahkan fillable dan casts baru:

```php
// File: app/Models/Dokumen.php

protected $fillable = [
    // ... existing fields ...
    'pending_approval_for',
    'pending_approval_at',
    'approval_responded_at',
    'approval_responded_by',
    'approval_rejection_reason',
];

protected $casts = [
    // ... existing casts ...
    'pending_approval_at' => 'datetime',
    'approval_responded_at' => 'datetime',
];

// Helper method untuk cek apakah dokumen sedang pending approval
public function isPendingApproval(): bool
{
    return in_array($this->status, [
        'pending_approval_ibub',
        'pending_approval_perpajakan',
        'pending_approval_akutansi'
    ]);
}

// Helper method untuk cek pending approval untuk role tertentu
public function isPendingApprovalFor(string $role): bool
{
    return $this->pending_approval_for === $role && $this->isPendingApproval();
}
```

### TASK 4: UPDATE METHOD sendToIbuB()
Ubah method di `DokumenController.php`:

```php
// File: app/Http/Controllers/DokumenController.php

public function sendToIbuB(Dokumen $dokumen)
{
    try {
        $currentHandler = $dokumen->current_handler ?? 'ibuA';
        $createdBy = $dokumen->created_by ?? 'ibuA';
        
        // Validasi status
        if (!in_array($dokumen->status, ['draft', 'returned_to_ibua', 'sedang diproses'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak dapat dikirim. Status dokumen harus draft, returned, atau sedang diproses.'
            ], 400);
        }

        // Validasi permission
        if ($createdBy !== 'ibuA' || $currentHandler !== 'ibuA') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengirim dokumen ini.'
            ], 403);
        }

        DB::beginTransaction();

        // UBAH: Set status ke pending approval, bukan langsung sent_to_ibub
        $dokumen->update([
            'status' => 'pending_approval_ibub',           // NEW
            'pending_approval_for' => 'ibuB',              // NEW
            'pending_approval_at' => now(),                // NEW
            'current_handler' => 'ibuA',                   // TETAP di pengirim
            'sent_to_ibub_at' => now(),                    // Timestamp pengiriman
        ]);

        $dokumen->refresh();
        DB::commit();

        // Broadcast event (opsional)
        try {
            broadcast(new \App\Events\DocumentSent($dokumen, 'ibuA', 'ibuB'));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dikirim ke IbuB. Menunggu IbuB untuk menerima dokumen.'
        ]);

    } catch (Exception $e) {
        DB::rollback();
        \Log::error('Error sending document: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengirim dokumen.'
        ], 500);
    }
}
```

### TASK 5: BUAT METHOD UNTUK APPROVAL (ACCEPT/REJECT)
Tambahkan method baru di `DashboardBController.php`:

```php
// File: app/Http/Controllers/DashboardBController.php

/**
 * Terima dokumen yang pending approval
 */
public function acceptDocument(Request $request, Dokumen $dokumen)
{
    try {
        // Validasi: harus pending approval untuk role ini
        if ($dokumen->status !== 'pending_approval_ibub') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak dalam status pending approval.'
            ], 400);
        }

        if ($dokumen->pending_approval_for !== 'ibuB') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini bukan untuk IbuB.'
            ], 403);
        }

        DB::beginTransaction();

        // Update dokumen: pindah ke status accepted
        $dokumen->update([
            'status' => 'sent_to_ibub',
            'current_handler' => 'ibuB',           // BARU PINDAH ke penerima
            'pending_approval_for' => null,
            'approval_responded_at' => now(),
            'approval_responded_by' => auth()->user()->username ?? 'ibuB',
            'approval_rejection_reason' => null,
        ]);

        $dokumen->refresh();
        DB::commit();

        // Broadcast event (opsional)
        try {
            broadcast(new \App\Events\DocumentAccepted($dokumen, 'ibuB'));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast acceptance: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diterima dan masuk ke sistem IbuB.'
        ]);

    } catch (Exception $e) {
        DB::rollback();
        \Log::error('Error accepting document: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat menerima dokumen.'
        ], 500);
    }
}

/**
 * Tolak dokumen yang pending approval
 */
public function rejectDocument(Request $request, Dokumen $dokumen)
{
    try {
        // Validasi input
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        // Validasi: harus pending approval untuk role ini
        if ($dokumen->status !== 'pending_approval_ibub') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak dalam status pending approval.'
            ], 400);
        }

        if ($dokumen->pending_approval_for !== 'ibuB') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini bukan untuk IbuB.'
            ], 403);
        }

        DB::beginTransaction();

        // Update dokumen: kembalikan ke pengirim
        $dokumen->update([
            'status' => 'draft',                   // Kembali ke draft
            'current_handler' => 'ibuA',           // Kembali ke pengirim
            'pending_approval_for' => null,
            'approval_responded_at' => now(),
            'approval_responded_by' => auth()->user()->username ?? 'ibuB',
            'approval_rejection_reason' => $request->rejection_reason,
        ]);

        $dokumen->refresh();
        DB::commit();

        // Broadcast event (opsional)
        try {
            broadcast(new \App\Events\DocumentRejected($dokumen, 'ibuB', $request->rejection_reason));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast rejection: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil ditolak dan dikembalikan ke IbuA.'
        ]);

    } catch (Exception $e) {
        DB::rollback();
        \Log::error('Error rejecting document: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat menolak dokumen.'
        ], 500);
    }
}
```

### TASK 6: UPDATE ROUTING
Tambahkan route baru di `routes/web.php`:

```php
// File: routes/web.php

// IbuB - Document Approval Routes
Route::middleware(['auth', 'checkRole:ibuB'])->group(function () {
    Route::post('/ibub/dokumen/{dokumen}/accept', [DashboardBController::class, 'acceptDocument'])
        ->name('ibub.dokumen.accept');
    
    Route::post('/ibub/dokumen/{dokumen}/reject', [DashboardBController::class, 'rejectDocument'])
        ->name('ibub.dokumen.reject');
});
```

### TASK 7: UPDATE VIEW UNTUK IbuB
Buat halaman untuk pending approval di IbuB:

```php
// File: resources/views/ibuB/dokumens/pendingApproval.blade.php

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Dokumen Menunggu Persetujuan</h2>

    @if($dokumensPending->count() > 0)
        <div class="alert alert-info">
            <i class="fas fa-bell"></i> Anda memiliki {{ $dokumensPending->count() }} dokumen yang menunggu persetujuan
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No. Agenda</th>
                            <th>No. SPP</th>
                            <th>Uraian</th>
                            <th>Nilai</th>
                            <th>Dikirim Oleh</th>
                            <th>Tanggal Kirim</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dokumensPending as $dok)
                        <tr id="dokumen-row-{{ $dok->id }}">
                            <td>{{ $dok->nomor_agenda }}</td>
                            <td>{{ $dok->nomor_spp }}</td>
                            <td>{{ Str::limit($dok->uraian_spp, 50) }}</td>
                            <td>{{ $dok->formatted_nilai_rupiah }}</td>
                            <td>{{ $dok->current_handler }}</td>
                            <td>{{ $dok->pending_approval_at->format('d M Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-success btn-accept" 
                                        data-id="{{ $dok->id }}"
                                        data-nomor="{{ $dok->nomor_agenda }}">
                                    <i class="fas fa-check"></i> Terima
                                </button>
                                <button class="btn btn-sm btn-danger btn-reject" 
                                        data-id="{{ $dok->id }}"
                                        data-nomor="{{ $dok->nomor_agenda }}">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                                <button class="btn btn-sm btn-info btn-detail" 
                                        data-id="{{ $dok->id }}">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-secondary">
            <i class="fas fa-inbox"></i> Tidak ada dokumen yang menunggu persetujuan
        </div>
    @endif
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Anda akan menolak dokumen: <strong id="reject-nomor-agenda"></strong></p>
                <div class="form-group">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejection-reason" rows="4" 
                              placeholder="Minimal 10 karakter..."></textarea>
                    <small class="text-danger" id="rejection-error" style="display:none;"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-reject">Tolak Dokumen</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let currentDokumenId = null;

    // Accept Document
    $('.btn-accept').click(function() {
        const dokumenId = $(this).data('id');
        const nomorAgenda = $(this).data('nomor');

        if (confirm(`Anda yakin ingin menerima dokumen ${nomorAgenda}?`)) {
            $.ajax({
                url: `/ibub/dokumen/${dokumenId}/accept`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        // Remove row from table
                        $(`#dokumen-row-${dokumenId}`).fadeOut(500, function() {
                            $(this).remove();
                            // Reload jika tidak ada lagi pending
                            if ($('tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        }
    });

    // Reject Document - Show Modal
    $('.btn-reject').click(function() {
        currentDokumenId = $(this).data('id');
        const nomorAgenda = $(this).data('nomor');
        
        $('#reject-nomor-agenda').text(nomorAgenda);
        $('#rejection-reason').val('');
        $('#rejection-error').hide();
        $('#rejectModal').modal('show');
    });

    // Confirm Reject
    $('#confirm-reject').click(function() {
        const reason = $('#rejection-reason').val().trim();
        
        if (reason.length < 10) {
            $('#rejection-error').text('Alasan penolakan minimal 10 karakter').show();
            return;
        }

        $.ajax({
            url: `/ibub/dokumen/${currentDokumenId}/reject`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                rejection_reason: reason
            },
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    alert(response.message);
                    // Remove row
                    $(`#dokumen-row-${currentDokumenId}`).fadeOut(500, function() {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                }
            },
            error: function(xhr) {
                $('#rejection-error').text(xhr.responseJSON?.message || 'Terjadi kesalahan').show();
            }
        });
    });
});
</script>
@endpush
@endsection
```

### TASK 8: UPDATE CONTROLLER UNTUK PENDING VIEW
Tambahkan method di `DashboardBController.php`:

```php
// File: app/Http/Controllers/DashboardBController.php

public function pendingApproval()
{
    // Get dokumen yang pending approval untuk IbuB
    $dokumensPending = Dokumen::where('status', 'pending_approval_ibub')
        ->where('pending_approval_for', 'ibuB')
        ->latest('pending_approval_at')
        ->get();

    $data = [
        'title' => 'Dokumen Menunggu Persetujuan',
        'module' => 'ibuB',
        'menuDokumen' => 'active',
        'menuPendingApproval' => 'active',
        'dokumensPending' => $dokumensPending,
    ];

    return view('ibuB.dokumens.pendingApproval', $data);
}
```

### TASK 9: TAMBAHKAN ROUTE UNTUK PENDING VIEW
```php
// File: routes/web.php

Route::middleware(['auth', 'checkRole:ibuB'])->group(function () {
    Route::get('/ibub/pending-approval', [DashboardBController::class, 'pendingApproval'])
        ->name('ibub.pending.approval');
});
```

### TASK 10: UPDATE QUERY DI dokumens() METHOD
Ubah query di `DashboardBController::dokumens()` untuk TIDAK menampilkan dokumen pending:

```php
// File: app/Http/Controllers/DashboardBController.php - method dokumens()

public function dokumens(Request $request){
    // EXCLUDE pending approval documents dari list
    $query = Dokumen::where(function($q) {
            $q->where('current_handler', 'ibuB')
              ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
        })
        ->where('status', '!=', 'returned_to_bidang')
        ->where('status', 'NOT LIKE', 'pending_approval%')  // NEW: exclude pending
        ->latest('sent_to_ibub_at')
        // ... rest of query
}
```

### TASK 11: TERAPKAN UNTUK PERPAJAKAN DAN AKUTANSI
Gunakan pola yang sama untuk flow ke Perpajakan dan Akutansi:

1. Ubah method `sendToPerpajakan()` dan `sendToAkutansi()` di `DashboardBController.php`
2. Set status ke `pending_approval_perpajakan` atau `pending_approval_akutansi`
3. Buat method `acceptDocument()` dan `rejectDocument()` di `DashboardPerpajakanController.php` dan `DashboardAkutansiController.php`
4. Buat view pending approval untuk masing-masing role

## OUTPUT YANG DIHARAPKAN

Setelah implementasi lengkap, sistem akan bekerja seperti ini:

### Dari sisi IbuA (Pengirim):
1. Klik "Kirim ke IbuB"
2. Status dokumen: "Menunggu persetujuan IbuB"
3. Dokumen masih terlihat di list IbuA dengan status pending
4. Jika ditolak: notifikasi + alasan penolakan

### Dari sisi IbuB (Penerima):
1. Melihat notifikasi badge: "3 dokumen menunggu persetujuan"
2. Masuk ke halaman "Pending Approval"
3. Bisa lihat detail dokumen
4. Pilihan:
   - **Terima**: Dokumen masuk ke sistem IbuB
   - **Tolak**: Dokumen kembali ke IbuA dengan alasan

## TESTING CHECKLIST
- [ ] IbuA bisa kirim dokumen
- [ ] Status berubah ke `pending_approval_ibub`
- [ ] `current_handler` tetap di `ibuA`
- [ ] IbuB melihat notifikasi pending
- [ ] IbuB bisa accept dokumen
- [ ] Setelah accept: status `sent_to_ibub`, handler `ibuB`
- [ ] IbuB bisa reject dengan alasan
- [ ] Setelah reject: status `draft`, handler `ibuA`
- [ ] IbuA melihat alasan penolakan
- [ ] Flow yang sama untuk Perpajakan dan Akutansi

## OPTIONAL ENHANCEMENTS
1. **Email notification** saat ada pending approval
2. **Auto-reject** jika tidak direspon dalam X hari
3. **History log** untuk tracking siapa approve/reject kapan
4. **Bulk approval** untuk approve beberapa dokumen sekaligus
5. **Real-time notification** menggunakan Laravel Echo + Pusher

## CATATAN PENTING
- Backup database sebelum migrate
- Test di environment development dulu
- Pastikan semua role (ibuA, ibuB, perpajakan, akutansi) sudah ada di user seeder
- Update dokumentasi API jika ada

---

**PROMPT SELESAI - SIAP UNTUK AI CODING**

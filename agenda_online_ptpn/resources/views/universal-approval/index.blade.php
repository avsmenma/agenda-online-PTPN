@extends('layouts.app')

@section('title', 'Daftar Masuk Dokumen - Agenda Online PTPN')

@section('content')
<style>
    .table-enhanced {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    .table-enhanced thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
        font-weight: 600;
        text-align: center;
        border-bottom: 2px solid #083E40;
        padding: 16px 12px;
        font-size: 13px;
    }
    
    .table-enhanced tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f0f0;
        background: white;
    }
    
    .table-enhanced tbody tr:hover {
        background: linear-gradient(135deg, rgba(136, 151, 23, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .table-enhanced tbody td {
        padding: 12px;
        vertical-align: middle;
        border-right: 1px solid #e0e0e0;
    }
    
    .table-enhanced tbody td:last-child {
        border-right: none;
    }
    
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        border-radius: 8px;
        padding: 12px 16px;
    }
    
    .btn-group .btn {
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
</style>

<div class="container-fluid" style="background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%); min-height: 100vh; padding: 20px; border-radius: 15px;">
    <!-- Header dengan Badge Counter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: #2e7d32; font-weight: 700;">
            <i class="fas fa-inbox" style="color: #2e7d32;"></i>
            Daftar Masuk Dokumen
            @if($waitingDocuments->count() > 0)
                <span class="badge badge-success ml-2" style="background: #28a745;" id="pending-count">{{ $waitingDocuments->count() }}</span>
            @endif
        </h2>
        <button class="btn btn-success btn-sm" onclick="location.reload()" style="background: #28a745; border: none;">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    @if($waitingDocuments->count() > 0)
        <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
            <i class="fas fa-bell" style="color: #155724;"></i>
            Anda memiliki <strong>{{ $waitingDocuments->count() }}</strong> dokumen yang menunggu persetujuan
        </div>

        <div class="card" style="background: white; border: 1px solid #dee2e6; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none; border-radius: 10px 10px 0 0;">
                <h5 class="mb-0" style="font-weight: 600;">
                    <i class="fas fa-clipboard-list"></i>
                    Dokumen Menunggu Approve Pengiriman
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-enhanced mb-0" id="documents-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">No. Agenda</th>
                                <th width="12%">No. SPP</th>
                                <th>Uraian</th>
                                <th width="12%">Nilai</th>
                                <th width="15%">Dikirim Oleh</th>
                                <th width="15%">Tanggal Kirim</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($waitingDocuments as $index => $dokumen)
                            <tr id="document-row-{{ $dokumen->id }}">
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td>
                                    <strong class="text-primary">{{ $dokumen->nomor_agenda }}</strong>
                                </td>
                                <td>{{ $dokumen->nomor_spp }}</td>
                                <td>
                                    {{ \Illuminate\Support\Str::limit($dokumen->uraian_spp, 60) }}
                                    @if(\Illuminate\Support\Str::length($dokumen->uraian_spp) > 60)
                                        <br><small class="text-muted">Klik "Detail" untuk melihat lengkap</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success">{{ $dokumen->formatted_nilai_rupiah }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $dokumen->getSenderDisplayName() }}</span>
                                </td>
                                <td>
                                    <small>{{ $dokumen->universal_approval_sent_at->format('d M Y') }}</small><br>
                                    <small class="text-muted">{{ $dokumen->universal_approval_sent_at->format('H:i') }}</small>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info btn-detail"
                                                data-id="{{ $dokumen->id }}"
                                                data-nomor="{{ $dokumen->nomor_agenda }}"
                                                title="Lihat Detail"
                                                style="background: #17a2b8; border: none; color: white; margin-right: 5px; padding: 6px 12px; font-weight: 500;"
                                                onclick="(function() {
                                                    console.log('Detail button clicked');
                                                    const id = {{ $dokumen->id }};
                                                    const nomor = '{{ $dokumen->nomor_agenda }}';

                                                    $('#detailModal .modal-title').html(\`<i class='fas fa-file-alt'></i> Detail Dokumen - \${nomor}\`);
                                                    $('#detail-content').html(\`
                                                        <div class='text-center py-3'>
                                                            <div class='spinner-border text-primary' role='status'>
                                                                <span class='sr-only'>Loading...</span>
                                                            </div>
                                                            <p class='mt-2'>Memuat detail dokumen...</p>
                                                        </div>
                                                    \`);
                                                    var modal = new bootstrap.Modal(document.getElementById('detailModal'));
                                                    modal.show();

                                                    $.ajax({
                                                        url: \`/universal-approval/\${id}/detail\`,
                                                        method: 'GET',
                                                        headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
                                                        success: function(response) {
                                                            if (response.success) {
                                                                const data = response.data;
                                                                $('#detail-content').html(\`
                                                                    <div class='row'>
                                                                        <div class='col-md-6'>
                                                                            <table class='table table-borderless'>
                                                                                <tr><td><strong>No. Agenda:</strong></td><td>\${data.nomor_agenda}</td></tr>
                                                                                <tr><td><strong>No. SPP:</strong></td><td>\${data.nomor_spp}</td></tr>
                                                                                <tr><td><strong>Nilai:</strong></td><td class='text-success'>\${data.nilai_rupiah}</td></tr>
                                                                                <tr><td><strong>Pengirim:</strong></td><td><span class='badge badge-info'>\${data.pengirim}</span></td></tr>
                                                                                <tr><td><strong>Dikirim Pada:</strong></td><td>\${data.dikirim_pada}</td></tr>
                                                                            </table>
                                                                        </div>
                                                                        <div class='col-md-6'>
                                                                            <table class='table table-borderless'>
                                                                                <tr><td><strong>Bagian:</strong></td><td>\${data.bagian || '-'}</td></tr>
                                                                                <tr><td><strong>Kategori:</strong></td><td>\${data.kategori || '-'}</td></tr>
                                                                                <tr><td><strong>Status:</strong></td><td><span class='badge badge-warning'>\${data.status}</span></td></tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                    <hr>
                                                                    <div class='row'>
                                                                        <div class='col-12'>
                                                                            <h6><strong>Uraian SPP:</strong></h6>
                                                                            <p>\${data.uraian_spp}</p>
                                                                        </div>
                                                                    </div>
                                                                \`);
                                                            }
                                                        }
                                                    });
                                                })(); return false;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success btn-approve"
                                                data-id="{{ $dokumen->id }}"
                                                data-nomor="{{ $dokumen->nomor_agenda }}"
                                                title="Approve Dokumen"
                                                style="background: #28a745; border: none; color: white; margin-right: 5px; padding: 6px 12px; font-weight: 500;"
                                                onclick="(function() {
                                                    console.log('Approve button clicked');
                                                    const id = {{ $dokumen->id }};
                                                    const nomor = '{{ $dokumen->nomor_agenda }}';

                                                    if (confirm(\`Apakah Anda yakin ingin menyetujui dokumen \${nomor}?\\n\\nDokumen akan masuk ke daftar dokumen Anda.\`)) {
                                                        const $row = $(\`#document-row-\${id}\`);
                                                        const $btnGroup = $row.find('.btn-group');
                                                        const originalHtml = $btnGroup.html();

                                                        $btnGroup.html(\`
                                                            <div class='spinner-border spinner-border-sm text-success' role='status'>
                                                                <span class='sr-only'>Loading...</span>
                                                            </div>
                                                        \`);

                                                        $.ajax({
                                                            url: \`/universal-approval/\${id}/approve\`,
                                                            method: 'POST',
                                                            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
                                                            data: { _token: $('meta[name=csrf-token]').attr('content') },
                                                            success: function(response) {
                                                                if (response.success) {
                                                                    $row.fadeOut(500, function() {
                                                                        $(this).remove();
                                                                        const count = $('#documents-table tbody tr').length;
                                                                        $('#pending-count').text(count);

                                                                        const $alert = $('.alert-success');
                                                                        if (count === 0) {
                                                                            $alert.fadeOut(300);
                                                                            setTimeout(() => location.reload(), 1000);
                                                                        } else {
                                                                            $alert.find('strong').text(count);
                                                                        }
                                                                    });
                                                                }
                                                            }
                                                        });
                                                    }
                                                })(); return false;">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-reject"
                                                data-id="{{ $dokumen->id }}"
                                                data-nomor="{{ $dokumen->nomor_agenda }}"
                                                title="Reject Dokumen"
                                                style="background: #dc3545; border: none; color: white; padding: 6px 12px; font-weight: 500;"
                                                onclick="(function() {
                                                    console.log('Reject button clicked');
                                                    const id = {{ $dokumen->id }};
                                                    const nomor = '{{ $dokumen->nomor_agenda }}';

                                                    $('#reject-nomor-agenda').text(nomor);
                                                    $('#rejection-reason').val('').removeClass('is-invalid');
                                                    $('#rejection-error').hide().text('');

                                                    // Store current document ID for the confirm button
                                                    window.currentRejectDocId = id;

                                                    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
                                                    modal.show();
                                                })(); return false;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-inbox fa-5x text-muted"></i>
            </div>
            <h3 class="text-muted">Tidak Ada Dokumen Menunggu</h3>
            <p class="text-muted">Semua dokumen telah diproses. Tidak ada dokumen yang menunggu persetujuan saat ini.</p>
            <a href="{{ route('dashboard.main') }}" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Kembali ke Dashboard
            </a>
        </div>
    @endif
</div>

<!-- Modal Approve Confirmation -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Konfirmasi Persetujuan Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle" style="font-size: 64px; color: #28a745;"></i>
                </div>
                <h5 class="fw-bold mb-3">Apakah Anda yakin ingin menyetujui dokumen ini?</h5>
                <div class="alert alert-info text-start">
                    <p class="mb-2"><strong>Nomor Agenda:</strong> <span id="approve-nomor-agenda">-</span></p>
                    <p class="mb-0"><strong>Tindakan:</strong> Dokumen akan masuk ke daftar dokumen Anda untuk diproses lebih lanjut.</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-success px-4" id="confirm-approve">
                    <i class="fas fa-check"></i> Ya, Setujui Dokumen
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Dokumen -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: #17a2b8; border: none;">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt"></i> Detail Dokumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="color: white;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detail-content">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat detail dokumen...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Tolak Dokumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Dokumen yang ditolak akan dikembalikan ke pengirim.
                </div>

                <p>Anda akan menolak dokumen: <strong id="reject-nomor-agenda"></strong></p>

                <div class="form-group">
                    <label for="rejection-reason">
                        Alasan Penolakan <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="rejection-reason" rows="4"
                              placeholder="Jelaskan mengapa dokumen ditolak (minimal 10 karakter)..."></textarea>
                    <small class="form-text text-muted">Minimal 10 karakter, maksimal 500 karakter</small>
                    <div class="invalid-feedback d-block" id="rejection-error" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirm-reject"
                        onclick="handleRejectSubmit(); return false;">
                    <i class="fas fa-check"></i> Ya, Tolak Dokumen
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Check if jQuery is loaded
console.log('Checking jQuery availability...');
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
} else {
    console.log('jQuery version:', $.fn.jquery);
}

// Check if document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Global functions should be available');
    if (typeof handleRejectSubmit === 'function') {
        console.log('✅ handleRejectSubmit is defined');
    } else {
        console.error('❌ handleRejectSubmit is NOT defined');
    }
});

// Global function for reject submission
function handleRejectSubmit() {
    console.log('Reject submit function called');

    const reason = document.getElementById('rejection-reason').value.trim();
    const docId = window.currentRejectDocId;

    console.log('Rejecting document:', docId, 'Reason:', reason);

    // Validation
    if (!docId) {
        alert('Error: Dokumen tidak ditemukan');
        return false;
    }

    if (reason.length < 10) {
        showRejectError('Alasan penolakan minimal 10 karakter');
        return false;
    }

    if (reason.length > 500) {
        showRejectError('Alasan penolakan maksimal 500 karakter');
        return false;
    }

    // Clear errors
    clearRejectError();

    // Show loading
    const btn = document.getElementById('confirm-reject');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menolak...';

    // Send AJAX request
    $.ajax({
        url: `/universal-approval/${docId}/reject`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            rejection_reason: reason
        },
        success: function(response) {
            console.log('Reject success:', response);
            handleRejectSuccess(response, docId, originalText);
        },
        error: function(xhr) {
            console.error('Reject error:', xhr);
            const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menolak dokumen';
            showRejectError(errorMsg);
            restoreRejectButton(originalText);
        }
    });
}

function showRejectError(message) {
    document.getElementById('rejection-error').textContent = message;
    document.getElementById('rejection-error').style.display = 'block';
    document.getElementById('rejection-reason').classList.add('is-invalid');
}

function clearRejectError() {
    document.getElementById('rejection-error').textContent = '';
    document.getElementById('rejection-error').style.display = 'none';
    document.getElementById('rejection-reason').classList.remove('is-invalid');
}

function restoreRejectButton(originalText) {
    const btn = document.getElementById('confirm-reject');
    btn.disabled = false;
    btn.innerHTML = originalText;
}

function handleRejectSuccess(response, docId, originalText) {
    if (response.success) {
        // Hide modal
        const modalElement = document.getElementById('rejectModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }

        // Animate and remove row
        $(`#document-row-${docId}`).fadeOut(500, function() {
            $(this).remove();
            updateDocumentCount();

            // Reload if empty
            if ($('#documents-table tbody tr').length === 0) {
                setTimeout(() => location.reload(), 1000);
            }
        });

        // Reset global variable
        window.currentRejectDocId = null;
    } else {
        const errorMsg = response.message || 'Gagal menolak dokumen';
        showRejectError(errorMsg);
        restoreRejectButton(originalText);
    }
}

function updateDocumentCount() {
    const count = $('#documents-table tbody tr').length;
    $('#pending-count').text(count);

    const alert = $('.alert-success');
    if (count === 0) {
        alert.fadeOut(300);
    } else {
        alert.find('strong').text(count);
    }
}

$(document).ready(function() {
    console.log('Universal Approval JavaScript initialized');
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat detail dokumen...</p>
                </div>
            `);
            var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            detailModal.show();

            // Load detail via AJAX
            console.log('Loading document detail for ID:', dokumenId);
            $.ajax({
                url: `/universal-approval/${dokumenId}/detail`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Detail response:', response);
                    if (response.success) {
                        const data = response.data;
                        $('#detail-content').html(`
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="35%"><strong>No. Agenda:</strong></td>
                                            <td>${data.nomor_agenda}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. SPP:</strong></td>
                                            <td>${data.nomor_spp}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nilai:</strong></td>
                                            <td class="text-success font-weight-bold">${data.nilai_rupiah}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pengirim:</strong></td>
                                            <td><span class="badge badge-info">${data.pengirim}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dikirim Pada:</strong></td>
                                            <td>${data.dikirim_pada}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="35%"><strong>Bagian:</strong></td>
                                            <td>${data.bagian || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kategori:</strong></td>
                                            <td>${data.kategori || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis Dokumen:</strong></td>
                                            <td>${data.jenis_dokumen || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Masuk:</strong></td>
                                            <td>${data.tanggal_masuk}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td><span class="badge badge-warning">${data.status}</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6><strong>Uraian SPP:</strong></h6>
                                    <p class="text-justify">${data.uraian_spp}</p>
                                </div>
                            </div>
                        `);
                    } else {
                        $('#detail-content').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                Gagal memuat detail dokumen: ${response.message}
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Detail load error:', xhr, status, error);
                    $('#detail-content').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Terjadi kesalahan saat memuat detail dokumen: ${error}
                        </div>
                    `);
                }
            });
        });
    }

    // Bind detail buttons immediately
    bindDetailButtons();

    // Also use event delegation as backup
    $(document).on('click', '.btn-detail', function(e) {
        console.log('Detail button clicked via event delegation');
        e.preventDefault();
        e.stopPropagation();

        const dokumenId = $(this).data('id');
        const nomorAgenda = $(this).data('nomor');
        console.log('Document ID:', dokumenId, 'Nomor Agenda:', nomorAgenda);

        // Re-bind and trigger
        bindDetailButtons();
        $(this).click();
    });

    // Global variable for approve
    window.currentApproveDocId = null;
    window.currentApproveNomorAgenda = null;

    // Approve Document - Use direct binding and event delegation
    function bindApproveButtons() {
        $('.btn-approve').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Approve button clicked via direct bind');

            const dokumenId = $(this).data('id');
            const nomorAgenda = $(this).data('nomor');
            console.log('Document ID:', dokumenId, 'Nomor Agenda:', nomorAgenda);

            // Set global variables
            window.currentApproveDocId = dokumenId;
            window.currentApproveNomorAgenda = nomorAgenda;

            // Show modal
            $('#approve-nomor-agenda').text(nomorAgenda);
            var approveModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('approveModal'));
            approveModal.show();
        });
    }

    // Bind approve buttons immediately
    bindApproveButtons();

    // Event delegation backup for approve
    $(document).on('click', '.btn-approve', function(e) {
        console.log('Approve button clicked via event delegation');
        e.preventDefault();
        e.stopPropagation();

        const dokumenId = $(this).data('id');
        const nomorAgenda = $(this).data('nomor');
        console.log('Document ID:', dokumenId, 'Nomor Agenda:', nomorAgenda);

        // Set global variables
        window.currentApproveDocId = dokumenId;
        window.currentApproveNomorAgenda = nomorAgenda;

        // Show modal
        $('#approve-nomor-agenda').text(nomorAgenda);
        var approveModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('approveModal'));
        approveModal.show();
    });

    // Handle Confirm Approve button
    $('#confirm-approve').off('click').on('click', function() {
        const dokumenId = window.currentApproveDocId;
        const nomorAgenda = window.currentApproveNomorAgenda;

        if (!dokumenId) {
            alert('Error: Dokumen tidak ditemukan');
            return;
        }

        console.log('Confirming approve for document:', dokumenId);

        // Hide modal
        var approveModal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
        approveModal.hide();

        // Call approve function
        approveDocument(dokumenId, nomorAgenda);

        // Reset global variables
        window.currentApproveDocId = null;
        window.currentApproveNomorAgenda = null;
    });

    function approveDocument(dokumenId, nomorAgenda) {
        console.log('Approving document:', dokumenId);
        
        // Show loading
        const $row = $(`#document-row-${dokumenId}`);
        const $btnGroup = $row.find('.btn-group');
        const originalHtml = $btnGroup.html();
        
        $btnGroup.html(`
            <div class="spinner-border spinner-border-sm text-success" role="status" style="width: 1rem; height: 1rem;">
                <span class="sr-only">Loading...</span>
            </div>
        `);

        console.log('Sending approve request for document:', dokumenId);
        $.ajax({
            url: `/universal-approval/${dokumenId}/approve`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Approve response:', response);
                if (response.success) {
                    // Remove row with animation
                    $row.fadeOut(500, function() {
                        $(this).remove();
                        updateDocumentCount();

                        // Show success message
                        showAlert(response.message || 'Dokumen berhasil disetujui', 'success');

                        // Check if table is empty
                        if ($('#documents-table tbody tr').length === 0) {
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    });
                } else {
                    showAlert(response.message || 'Gagal menyetujui dokumen', 'danger');
                    $btnGroup.html(originalHtml);
                }
            },
            error: function(xhr, status, error) {
                console.error('Approve error:', xhr, status, error);
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyetujui dokumen';
                showAlert(message, 'danger');

                // Restore buttons
                $btnGroup.html(originalHtml);
            }
        });
    }

    // Reject Document - Use direct binding and event delegation
    function bindRejectButtons() {
        $('.btn-reject').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Reject button clicked via direct bind');

            currentDokumenId = $(this).data('id');
            currentNomorAgenda = $(this).data('nomor');
            console.log('Document ID:', currentDokumenId, 'Nomor Agenda:', currentNomorAgenda);

            $('#reject-nomor-agenda').text(currentNomorAgenda);
            $('#rejection-reason').val('').removeClass('is-invalid');
            $('#rejection-error').hide().text('');

            // Use Bootstrap 5 modal
            var rejectModalElement = document.getElementById('rejectModal');
            var rejectModal = bootstrap.Modal.getOrCreateInstance(rejectModalElement);
            rejectModal.show();
        });
    }

    // Bind reject buttons immediately
    bindRejectButtons();

    // Event delegation backup for reject
    $(document).on('click', '.btn-reject', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Reject button clicked via event delegation');

        currentDokumenId = $(this).data('id');
        currentNomorAgenda = $(this).data('nomor');
        console.log('Document ID:', currentDokumenId, 'Nomor Agenda:', currentNomorAgenda);

        $('#reject-nomor-agenda').text(currentNomorAgenda);
        $('#rejection-reason').val('').removeClass('is-invalid');
        $('#rejection-error').hide().text('');

        // Use Bootstrap 5 modal
        var rejectModalElement = document.getElementById('rejectModal');
        var rejectModal = bootstrap.Modal.getOrCreateInstance(rejectModalElement);
        rejectModal.show();
    });

    // Confirm Reject - Use multiple binding approaches
    function bindConfirmRejectButton() {
        $('#confirm-reject').off('click').on('click', function() {
            console.log('Confirm reject button clicked via direct bind!');
            handleRejectConfirmation();
        });
    }

    // Bind immediately
    bindConfirmRejectButton();

    // Event delegation backup
    $(document).on('click', '#confirm-reject', function() {
        console.log('Confirm reject button clicked via event delegation!');
        handleRejectConfirmation();
    });

    function handleRejectConfirmation() {
        const reason = $('#rejection-reason').val().trim();
        const docId = window.currentRejectDocId;
        console.log('Current reject doc ID:', docId);
        console.log('Rejection reason:', reason);

        if (!docId) {
            alert('Error: Dokumen tidak ditemukan');
            return;
        }

        // Validation
        if (reason.length < 10) {
            $('#rejection-reason').addClass('is-invalid');
            $('#rejection-error').text('Alasan penolakan minimal 10 karakter').show();
            return;
        }

        if (reason.length > 500) {
            $('#rejection-reason').addClass('is-invalid');
            $('#rejection-error').text('Alasan penolakan maksimal 500 karakter').show();
            return;
        }

        $('#rejection-reason').removeClass('is-invalid');
        $('#rejection-error').hide();

        // Disable button and show loading
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html(`
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="sr-only">Loading...</span>
            </div> Menolak...
        `);

        console.log('Rejecting document:', docId, 'Reason:', reason);

        $.ajax({
            url: `/universal-approval/${docId}/reject`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                rejection_reason: reason
            },
            success: function(response) {
                if (response.success) {
                    var rejectModalElement = document.getElementById('rejectModal');
                    var rejectModal = bootstrap.Modal.getInstance(rejectModalElement);
                    rejectModal.hide();

                    // Remove row with animation
                    $(`#document-row-${docId}`).fadeOut(500, function() {
                        $(this).remove();
                        const count = $('#documents-table tbody tr').length;
                        $('#pending-count').text(count);

                        const $alert = $('.alert-success');
                        if (count === 0) {
                            $alert.fadeOut(300);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            $alert.find('strong').text(count);
                        }
                    });

                    // Reset global variable
                    window.currentRejectDocId = null;
                } else {
                    const message = response.message || 'Gagal menolak dokumen';
                    $('#rejection-error').text(message).show();
                    $('#rejection-reason').addClass('is-invalid');
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Reject error:', xhr, status, error);
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menolak dokumen';
                $('#rejection-error').text(message).show();
                $('#rejection-reason').addClass('is-invalid');

                // Restore button
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    function updateDocumentCount() {
        const count = $('#documents-table tbody tr').length;
        $('#pending-count').text(count);

        // Update alert notification
        const $alert = $('.alert-success');
        if (count === 0) {
            $alert.fadeOut(300);
        } else {
            $alert.find('strong').text(count);
            if ($alert.is(':hidden')) {
                $alert.fadeIn(300);
            }
        }
    }

    function loadNotificationCount() {
        $.get('/universal-approval/notifications')
            .done(function(response) {
                if (response.count !== undefined) {
                    // Update badge if on this page
                    $('#pending-count').text(response.count);

                    // Update notification badge in navigation (if exists)
                    $('.notification-badge').text(response.count);
                    if (response.count === 0) {
                        $('.notification-badge').hide();
                    } else {
                        $('.notification-badge').show();
                    }
                }
            });
    }

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

        // Insert after the header
        $('.container-fluid h2').after(alertHtml);

        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert-dismissible').alert('close');
        }, 5000);
    }

    // Clear modal state when hidden
    $('#rejectModal').on('hidden.bs.modal', function() {
        $('#rejection-reason').val('').removeClass('is-invalid');
        $('#rejection-error').hide().text('');
        window.currentRejectDocId = null;
        $('#confirm-reject').prop('disabled', false).html('<i class="fas fa-check"></i> Ya, Tolak Dokumen');
    });
});
</script>
@endpush
@endsection
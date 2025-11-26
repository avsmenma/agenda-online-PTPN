@extends('layouts.app')

@section('title', 'Detail Dokumen - Inbox')

@push('styles')
<style>
/* Modern Notification Toast Styles */
.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 350px;
    max-width: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 0;
    overflow: hidden;
    animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    transform: translateX(400px);
    opacity: 0;
}

.notification-toast.show {
    transform: translateX(0);
    opacity: 1;
}

.notification-toast.hide {
    animation: slideOutRight 0.3s ease-in forwards;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.notification-toast.success {
    border-left: 5px solid #48bb78;
}

.notification-toast.error {
    border-left: 5px solid #f56565;
}

.notification-toast.warning {
    border-left: 5px solid #f6ad55;
}

.notification-content {
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.notification-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.notification-toast.success .notification-icon {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
}

.notification-toast.error .notification-icon {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
}

.notification-toast.warning .notification-icon {
    background: linear-gradient(135deg, #f6ad55 0%, #fed7aa 100%);
    color: white;
}

.notification-body {
    flex: 1;
}

.notification-title {
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 4px;
    color: #1a202c;
}

.notification-message {
    font-size: 14px;
    color: #4a5568;
    line-height: 1.5;
}

.notification-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    color: #718096;
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
    transition: color 0.2s;
}

.notification-close:hover {
    color: #2d3748;
}

/* Modern Confirmation Modal */
.confirmation-modal .modal-content {
    border-radius: 16px;
    border: none;
    overflow: hidden;
}

.confirmation-modal .modal-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    padding: 24px;
}

.confirmation-modal .modal-header .modal-title {
    font-weight: 700;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.confirmation-modal .modal-body {
    padding: 32px 24px;
    text-align: center;
}

.confirmation-icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    animation: pulse 2s infinite;
}

.confirmation-icon-wrapper.approve {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
}

.confirmation-icon-wrapper.reject {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(72, 187, 120, 0);
    }
}

.confirmation-message {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 12px;
}

.confirmation-details {
    font-size: 14px;
    color: #718096;
    margin-bottom: 0;
}

.confirmation-modal .modal-footer {
    border: none;
    padding: 20px 24px;
    background: #f7fafc;
    display: flex;
    justify-content: center;
    gap: 12px;
}

.confirmation-modal .btn {
    padding: 12px 32px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
    border: none;
}

.confirmation-modal .btn-confirm-approve {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
}

.confirmation-modal .btn-confirm-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
}

.confirmation-modal .btn-confirm-reject {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
}

.confirmation-modal .btn-confirm-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 101, 101, 0.4);
}

.confirmation-modal .btn-cancel {
    background: #e2e8f0;
    color: #4a5568;
}

.confirmation-modal .btn-cancel:hover {
    background: #cbd5e0;
    transform: translateY(-2px);
}
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Dokumen - Inbox</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        @php
                            $dashboardUrl = match($userRole) {
                                'IbuB' => '/dashboardB',
                                'Perpajakan' => '/dashboardPerpajakan',
                                'Akutansi' => '/dashboardAkutansi',
                                default => '/dashboard'
                            };
                        @endphp
                        <li class="breadcrumb-item"><a href="{{ url($dashboardUrl) }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inbox.index') }}">Inbox</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- Document Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detail Dokumen</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="150">No. Agenda</th>
                                    <td>{{ $dokumen->nomor_agenda }}</td>
                                </tr>
                                <tr>
                                    <th>No. SPP</th>
                                    <td>{{ $dokumen->nomor_spp }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal SPP</th>
                                    <td>{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Uraian SPP</th>
                                    <td>{{ $dokumen->uraian_spp }}</td>
                                </tr>
                                <tr>
                                    <th>Nilai Rupiah</th>
                                    <td class="text-right">Rp {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Kategori</th>
                                    <td>{{ $dokumen->kategori ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Pengirim</th>
                                    <td>{{ $dokumen->getSenderDisplayName() }}</td>
                                </tr>
                                <tr>
                                    <th>Dikirim ke Inbox</th>
                                    <td>{{ $dokumen->inbox_approval_sent_at ? $dokumen->inbox_approval_sent_at->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body text-center">
                            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#approveConfirmModal">
                                <i class="fas fa-check"></i> Approve
                            </button>

                            <button type="button" class="btn btn-danger btn-lg ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Status Card -->
                    <div class="card bg-info">
                        <div class="card-header">
                            <h3 class="card-title">Status Dokumen</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Status Saat Ini:</strong></p>
                            <h4>Menunggu Persetujuan</h4>
                            <small>Dikirim: {{ $dokumen->inbox_approval_sent_at ? $dokumen->inbox_approval_sent_at->diffForHumans() : '-' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade confirmation-modal" id="approveConfirmModal" tabindex="-1" aria-labelledby="approveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveConfirmModalLabel">
                    <i class="fas fa-check-circle"></i>
                    Konfirmasi Persetujuan Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="confirmation-icon-wrapper approve">
                    <i class="fas fa-check"></i>
                </div>
                <p class="confirmation-message">Apakah Anda yakin ingin menyetujui dokumen ini?</p>
                <p class="confirmation-details">
                    Dokumen <strong>{{ $dokumen->nomor_agenda }}</strong> akan disetujui dan masuk ke daftar dokumen resmi untuk diproses lebih lanjut.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('inbox.approve', $dokumen) }}" id="approveForm" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-confirm-approve">
                        <i class="fas fa-check me-1"></i> Ya, Setujui
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('inbox.reject', $dokumen) }}" id="rejectForm">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="fas fa-times-circle"></i>
                        Reject Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Perhatian:</strong> Dokumen yang ditolak akan dikembalikan ke pengirim ({{ $dokumen->getSenderDisplayName() }}) dan tidak akan masuk ke sistem persetujuan.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Reject <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Masukkan alasan penolakan dokumen..." required></textarea>
                        <div class="form-text">Alasan penolakan akan dikirim ke pengirim dokumen.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notification Toast Container -->
<div id="notificationContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle flash messages from server
    @if(session('success'))
        showNotification('success', 'Berhasil!', '{{ session('success') }}');
    @endif

    @if(session('error'))
        showNotification('error', 'Error!', '{{ session('error') }}');
    @endif

    // Handle form submissions with loading state
    const approveForm = document.getElementById('approveForm');
    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
            }
        });
    }

    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const reason = document.getElementById('reason').value.trim();
            if (!reason) {
                e.preventDefault();
                showNotification('warning', 'Peringatan', 'Alasan penolakan harus diisi!');
                return false;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
            }
        });
    }
});

function showNotification(type, title, message) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `notification-toast ${type}`;
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>'
    };

    toast.innerHTML = `
        <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        <div class="notification-content">
            <div class="notification-icon">
                ${icons[type] || icons.success}
            </div>
            <div class="notification-body">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
        </div>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, 5000);
}
</script>
@endsection

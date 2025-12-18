@extends('layouts.app')

@section('title', 'Detail Dokumen - Inbox')

@push('styles')
<style>
/* Modern Approval Dashboard Styles */
.hero-banner {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    border-radius: 20px;
    padding: 32px 40px;
    margin-bottom: 30px;
    box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2);
    color: white;
}

.hero-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
    color: white;
}

.hero-subtitle {
    font-size: 16px;
    opacity: 0.9;
    color: rgba(255, 255, 255, 0.9);
}

.nominal-display {
    font-size: 42px;
    font-weight: 800;
    color: #fbbf24; /* Amber-400 - Kuning terang yang kontras dengan background gelap */
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), 0 0 20px rgba(251, 191, 36, 0.3);
    line-height: 1.2;
}

.status-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    font-weight: 600;
    font-size: 14px;
}

/* Tab Navigation */
.tab-nav {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 24px;
}

.tab-button {
    padding: 12px 24px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-weight: 600;
    font-size: 14px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    bottom: -2px;
}

.tab-button:hover {
    color: #083E40;
    background: rgba(8, 62, 64, 0.05);
}

.tab-button.active {
    color: #083E40;
    border-bottom-color: #083E40;
    background: rgba(8, 62, 64, 0.05);
}

/* Content Cards */
.content-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
    border: 1px solid rgba(8, 62, 64, 0.1);
    margin-bottom: 24px;
}

/* Executive Summary Grid */
.exec-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.summary-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.summary-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.1);
    border-color: #083E40;
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
}

.summary-content {
    flex: 1;
}

.summary-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.summary-value {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    word-break: break-word;
}

/* Timeline */
.timeline-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
    border: 1px solid rgba(8, 62, 64, 0.1);
    margin-bottom: 24px;
}

.timeline-item {
    display: flex;
    gap: 16px;
    position: relative;
    padding-bottom: 24px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 48px;
    width: 2px;
    height: calc(100% - 24px);
    background: #e2e8f0;
}

.timeline-dot {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    z-index: 1;
    position: relative;
}

.timeline-dot.completed {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: white;
}

.timeline-dot.current {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    box-shadow: 0 0 0 4px rgba(8, 62, 64, 0.1);
}

.timeline-content {
    flex: 1;
    padding-top: 4px;
}

.timeline-title {
    font-weight: 700;
    font-size: 14px;
    color: #1e293b;
    margin-bottom: 4px;
}

.timeline-time {
    font-size: 12px;
    color: #64748b;
}

/* Action Panel */
.action-panel {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.15);
    border: 2px solid rgba(8, 62, 64, 0.1);
    position: sticky;
    top: 20px;
}

.action-panel-title {
    font-size: 18px;
    font-weight: 700;
    color: #083E40;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
}

.action-note {
    margin-bottom: 24px;
}

.action-note label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #475569;
    margin-bottom: 8px;
}

.action-note textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    resize: vertical;
    transition: all 0.3s ease;
}

.action-note textarea:focus {
    outline: none;
    border-color: #083E40;
    box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-approve {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: white;
    border: none;
    padding: 16px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-reject {
    background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
    color: white;
    border: none;
    padding: 16px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

/* Table Styles */
.detail-table {
    width: 100%;
    border-collapse: collapse;
}

.detail-table th {
    background: #f8fafc;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
    width: 200px;
}

.detail-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
    font-size: 14px;
}

.detail-table tr:hover {
    background: #f8fafc;
}

/* Responsive */
@media (max-width: 1024px) {
    .exec-summary-grid {
        grid-template-columns: 1fr;
    }
}

/* Notification Toast Styles */
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
}

/* Modal Styles */
.confirmation-modal .modal-content {
    border-radius: 16px;
    border: none;
}

.confirmation-modal .modal-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    padding: 24px;
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
}

.confirmation-icon-wrapper.approve {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
}

.confirmation-icon-wrapper.reject {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
}

.confirmation-modal .btn-confirm-approve {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 10px;
}

.confirmation-modal .btn-confirm-reject {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 10px;
}
</style>
@endpush

@section('content')
<div class="content-wrapper" x-data="{ activeTab: 'summary' }">
    <!-- Hero Section -->
    <div class="hero-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="hero-title">{{ $dokumen->nomor_agenda }}</h1>
                <p class="hero-subtitle mb-0">{{ $dokumen->uraian_spp ? Str::limit($dokumen->uraian_spp, 80) : 'Tidak ada uraian' }}</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="nominal-display mb-3">
                    Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}
                </div>
                <div class="status-badge-pill">
                    <i class="fas fa-clock"></i>
                    Menunggu Persetujuan
                </div>
            </div>
        </div>
    </div>

    <!-- Main Layout: 2 Columns -->
    <div class="row g-4">
        <!-- Left Column: Main Content (span-8) -->
        <div class="col-lg-8">
            <!-- Tab Navigation -->
            <div class="content-card">
                <div class="tab-nav">
                    <button 
                        class="tab-button" 
                        :class="{ active: activeTab === 'summary' }"
                        @click="activeTab = 'summary'"
                    >
                        <i class="fas fa-chart-line me-2"></i>
                        Ringkasan Dokumen
                    </button>
                    <button 
                        class="tab-button"
                        :class="{ active: activeTab === 'full' }"
                        @click="activeTab = 'full'"
                    >
                        <i class="fas fa-table me-2"></i>
                        Data Lengkap
                    </button>
                    <button 
                        class="tab-button"
                        :class="{ active: activeTab === 'documents' }"
                        @click="activeTab = 'documents'"
                    >
                        <i class="fas fa-file-pdf me-2"></i>
                        Dokumen Fisik
                    </button>
                </div>

                <!-- Tab Content: Ringkasan Dokumen -->
                <div x-show="activeTab === 'summary'" x-transition>
                    <div class="exec-summary-grid">
                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Nomor SPP</div>
                                <div class="summary-value">
                                    {{ $dokumen->nomor_spp ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Tanggal SPP</div>
                                <div class="summary-value">
                                    {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Nilai Rupiah</div>
                                <div class="summary-value">
                                    <strong>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Dibayar Kepada (Vendor)</div>
                                <div class="summary-value">
                                    @if($dokumen->dibayarKepadas->count() > 0)
                                        {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
                                    @elseif($dokumen->dibayar_kepada)
                                        {{ $dokumen->dibayar_kepada }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Data Lengkap -->
                <div x-show="activeTab === 'full'" x-transition>
                    <table class="detail-table">
                        <tr>
                            <th>No. Agenda</th>
                            <td>{{ $dokumen->nomor_agenda }}</td>
                        </tr>
                        <tr>
                            <th>No. SPP</th>
                            <td>{{ $dokumen->nomor_spp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal SPP</th>
                            <td>{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Uraian SPP</th>
                            <td>{{ $dokumen->uraian_spp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nilai Rupiah</th>
                            <td><strong>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Kriteria CF</th>
                            <td>{{ $dokumen->kategori ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Sub Kriteria</th>
                            <td>{{ $dokumen->jenis_dokumen ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Item Sub Kriteria</th>
                            <td>{{ $dokumen->jenis_sub_pekerjaan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Bagian</th>
                            <td>{{ $dokumen->bagian ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Pengirim</th>
                            <td>{{ $dokumen->getSenderDisplayName() }}</td>
                        </tr>
                        <tr>
                            <th>Nama Pengirim</th>
                            <td>{{ $dokumen->nama_pengirim ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dikirim ke Inbox</th>
                            @php
                                $currentRoleCode = strtolower($userRole ?? 'ibub');
                                $roleStatus = $dokumen->getStatusForRole($currentRoleCode);
                                $roleData = $dokumen->getDataForRole($currentRoleCode);
                                $sentAt = $roleStatus?->status_changed_at ?? $roleData?->received_at ?? null;
                            @endphp
                            <td>{{ $sentAt ? $sentAt->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Bulan</th>
                            <td>{{ $dokumen->bulan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tahun</th>
                            <td>{{ $dokumen->tahun ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Tab Content: Dokumen Fisik -->
                <div x-show="activeTab === 'documents'" x-transition>
                    <div class="text-center py-5">
                        <i class="fas fa-file-pdf" style="font-size: 64px; color: #cbd5e1; margin-bottom: 16px;"></i>
                        <h5 style="color: #64748b; margin-bottom: 8px;">Dokumen Fisik</h5>
                        <p style="color: #94a3b8; font-size: 14px;">
                            Preview dokumen fisik akan ditampilkan di sini jika tersedia
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Context & Action (span-4) -->
        <div class="col-lg-4">
            <!-- Timeline Card -->
            <div class="timeline-card">
                <h5 class="mb-4" style="font-weight: 700; color: #083E40;">
                    <i class="fas fa-history me-2"></i>
                    Riwayat Dokumen
                </h5>
                
                <div class="timeline-item">
                    <div class="timeline-dot completed">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Ibu Tarapul</div>
                        <div class="timeline-time">
                            @php
                                $currentRoleCode = strtolower($userRole ?? 'ibub');
                                $roleStatus = $dokumen->getStatusForRole($currentRoleCode);
                                $roleData = $dokumen->getDataForRole($currentRoleCode);
                                $sentAt = $roleStatus?->status_changed_at ?? $roleData?->received_at ?? null;
                            @endphp
                            Dikirim {{ $sentAt ? $sentAt->diffForHumans() : '-' }}
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot current">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Inbox Anda</div>
                        <div class="timeline-time">Sekarang - Menunggu Persetujuan</div>
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="action-panel">
                <h5 class="action-panel-title">
                    <i class="fas fa-tasks me-2"></i>
                    Panel Persetujuan
                </h5>

                <div class="action-note">
                    <label for="approvalNote">
                        <i class="fas fa-sticky-note me-2"></i>
                        Catatan Persetujuan (Opsional)
                    </label>
                    <textarea 
                        id="approvalNote" 
                        name="approval_note" 
                        rows="3" 
                        placeholder="Tambahkan catatan untuk persetujuan dokumen ini..."
                    ></textarea>
                </div>

                <div class="action-buttons">
                    <button 
                        type="button" 
                        class="btn-approve"
                        data-bs-toggle="modal" 
                        data-bs-target="#approveConfirmModal"
                    >
                        <i class="fas fa-check"></i>
                        Setujui Dokumen
                    </button>
                </div>

                <div class="action-note mt-4">
                    <label for="rejectReason">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Alasan Penolakan <span class="text-danger">*</span>
                    </label>
                    <textarea 
                        id="rejectReason" 
                        name="reject_reason" 
                        rows="3" 
                        placeholder="Masukkan alasan penolakan dokumen..."
                    ></textarea>
                </div>

                <div class="action-buttons">
                    <button 
                        type="button" 
                        class="btn-reject"
                        data-bs-toggle="modal" 
                        data-bs-target="#rejectModal"
                    >
                        <i class="fas fa-times"></i>
                        Tolak Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('inbox.approve', $dokumen) }}" id="approveForm" style="display: inline;">
                    @csrf
                    <input type="hidden" name="note" id="approveNoteInput">
                    <button type="submit" class="btn btn-confirm-approve">
                        <i class="fas fa-check me-1"></i> Ya, Setujui
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade confirmation-modal" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle"></i>
                    Tolak Dokumen
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
                <p class="confirmation-message">Apakah Anda yakin ingin menolak dokumen ini?</p>
                <p class="confirmation-details">
                    Pastikan alasan penolakan sudah diisi dengan lengkap.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('inbox.reject', $dokumen) }}" id="rejectForm" style="display: inline;">
                    @csrf
                    <input type="hidden" name="reason" id="rejectReasonInput">
                    <button type="submit" class="btn btn-confirm-reject">
                        <i class="fas fa-times me-1"></i> Ya, Tolak
                    </button>
                </form>
            </div>
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
            // Copy note from textarea to hidden input
            const noteTextarea = document.getElementById('approvalNote');
            const noteInput = document.getElementById('approveNoteInput');
            if (noteTextarea && noteInput) {
                noteInput.value = noteTextarea.value;
            }

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
            const reasonTextarea = document.getElementById('rejectReason');
            const reasonInput = document.getElementById('rejectReasonInput');
            const reason = reasonTextarea ? reasonTextarea.value.trim() : '';

            if (!reason) {
                e.preventDefault();
                showNotification('warning', 'Peringatan', 'Alasan penolakan harus diisi!');
                return false;
            }

            // Copy reason from textarea to hidden input
            if (reasonTextarea && reasonInput) {
                reasonInput.value = reason;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
            }
        });
    }

    // Update reject modal to show reason from textarea
    const rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function() {
            const reasonTextarea = document.getElementById('rejectReason');
            if (reasonTextarea && !reasonTextarea.value.trim()) {
                setTimeout(() => {
                    reasonTextarea.focus();
                }, 300);
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

    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    if (type === 'success' || type === 'error') {
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }, 4000);
    }
}
</script>
@endsection

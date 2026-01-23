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
            color: #fbbf24;
            /* Amber-400 - Kuning terang yang kontras dengan background gelap */
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

        /* Activity Panel */
        .activity-panel {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(8, 62, 64, 0.15);
            border: 2px solid rgba(8, 62, 64, 0.1);
            margin-bottom: 24px;
        }

        .activity-panel-title {
            font-size: 18px;
            font-weight: 700;
            color: #083E40;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .activity-list {
            margin-bottom: 16px;
        }

        .activity-section {
            margin-bottom: 16px;
        }

        .activity-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .activity-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 3px solid #10b981;
        }

        .activity-item.self {
            background: #ecfdf5;
            border-left-color: #083E40;
        }

        .activity-item-name {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            flex: 1;
        }

        .activity-item-role {
            font-size: 11px;
            color: #64748b;
            background: #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .activity-item-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .activity-warning {
            padding: 12px 16px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            color: #92400e;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-top: 12px;
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

        .notification-body {
            flex: 1;
        }

        .notification-title {
            font-weight: 700;
            font-size: 16px;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .notification-message {
            font-size: 14px;
            color: #475569;
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
        }

        .notification-toast.warning .notification-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
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
                    <p class="hero-subtitle mb-0">
                        {{ $dokumen->uraian_spp ? Str::limit($dokumen->uraian_spp, 80) : 'Tidak ada uraian' }}</p>
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
                        <button class="tab-button" :class="{ active: activeTab === 'summary' }"
                            @click="activeTab = 'summary'">
                            <i class="fas fa-chart-line me-2"></i>
                            Ringkasan Dokumen
                        </button>
                        <button class="tab-button" :class="{ active: activeTab === 'full' }" @click="activeTab = 'full'">
                            <i class="fas fa-table me-2"></i>
                            Data Lengkap
                        </button>
                        <button class="tab-button" :class="{ active: activeTab === 'documents' }"
                            @click="activeTab = 'documents'">
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

                        <!-- Uraian SPP - Full Width -->
                        <div class="summary-item" style="grid-column: 1 / -1; margin-top: 20px;">
                            <div class="summary-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="summary-content" style="width: 100%;">
                                <div class="summary-label">Uraian SPP</div>
                                <div class="summary-value"
                                    style="font-size: 14px; font-weight: 500; line-height: 1.6; white-space: pre-wrap;">
                                    {{ $dokumen->uraian_spp ?? '-' }}
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
                                <td>
                                    @php
                                        $currentRoleCode = strtolower($userRole ?? 'team_verifikasi');
                                        $senderName = $dokumen->getInboxSenderDisplayName($currentRoleCode);
                                    @endphp
                                    {{ $senderName }}
                                </td>
                            </tr>
                            <tr>
                                <th>Nama Pengirim</th>
                                <td>{{ $dokumen->nama_pengirim ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Dikirim ke Inbox</th>
                                @php
                                    $currentRoleCode = strtolower($userRole ?? 'team_verifikasi');
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
                            <div class="timeline-title">
                                @php
                                    $currentRoleCode = strtolower($userRole ?? 'team_verifikasi');
                                    // Get sender name based on document status and current role
                                    $senderName = $dokumen->getInboxSenderDisplayName($currentRoleCode);
                                @endphp
                                {{ $senderName }}
                            </div>
                            <div class="timeline-time">
                                @php
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

                <!-- Activity Indicators Panel -->
                <div class="activity-panel" id="activity-panel" style="display: none;">
                    <h5 class="activity-panel-title">
                        <i class="fas fa-users me-2"></i>
                        Aktivitas Dokumen
                    </h5>

                    <div id="viewers-list" class="activity-list">
                        <div class="activity-section">
                            <div class="activity-label">
                                <i class="fas fa-eye me-2"></i>Sedang melihat:
                            </div>
                            <div id="viewers-items" class="activity-items">
                                <!-- Dynamic content -->
                            </div>
                        </div>
                    </div>

                    <div id="editors-warning" class="activity-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="editor-name"></span> sedang mengedit dokumen ini
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
                        <textarea id="approvalNote" name="approval_note" rows="3"
                            placeholder="Tambahkan catatan untuk persetujuan dokumen ini..."></textarea>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn-approve" data-bs-toggle="modal"
                            data-bs-target="#approveConfirmModal">
                            <i class="fas fa-check"></i>
                            Setujui Dokumen
                        </button>
                    </div>

                    <div class="action-note mt-4">
                        <label for="rejectReason">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea id="rejectReason" name="reject_reason" rows="3"
                            placeholder="Masukkan alasan penolakan dokumen..."
                            class="@error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i>
                            Tolak Dokumen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div class="modal fade confirmation-modal" id="approveConfirmModal" tabindex="-1"
        aria-labelledby="approveConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveConfirmModalLabel">
                        <i class="fas fa-check-circle"></i>
                        Konfirmasi Persetujuan Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="confirmation-icon-wrapper approve">
                        <i class="fas fa-check"></i>
                    </div>
                    <p class="confirmation-message">Apakah Anda yakin ingin menyetujui dokumen ini?</p>
                    <p class="confirmation-details">
                        Dokumen <strong>{{ $dokumen->nomor_agenda }}</strong> akan disetujui dan masuk ke daftar dokumen
                        resmi untuk diproses lebih lanjut.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="{{ route('inbox.approve', $dokumen) }}" id="approveForm"
                        style="display: inline;">
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
    <div class="modal fade confirmation-modal" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="fas fa-times-circle"></i>
                        Tolak Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Perhatian:</strong> Dokumen yang ditolak akan dikembalikan ke pengirim (@php
                                $currentRoleCode = strtolower($userRole ?? 'team_verifikasi');
                                $senderName = $dokumen->getInboxSenderDisplayName($currentRoleCode);
                            @endphp
                            {{ $senderName }}) dan tidak akan masuk ke sistem persetujuan.
                        </div>
                    </div>
                    <p class="confirmation-message">Apakah Anda yakin ingin menolak dokumen ini?</p>
                    <p class="confirmation-details">
                        Pastikan alasan penolakan sudah diisi dengan lengkap.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="{{ route('inbox.reject', $dokumen) }}" id="rejectForm"
                        style="display: inline;">
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
        document.addEventListener('DOMContentLoaded', function () {
            // Handle flash messages from server
            @if(session('success'))
                showNotification('success', 'Berhasil!', '{{ session('success') }}');
            @endif

            @if(session('error'))
                showNotification('error', 'Error!', '{{ session('error') }}');
            @endif

            // Handle validation errors
            @if($errors->has('reason'))
                @foreach($errors->get('reason') as $error)
                    showNotification('error', 'Validasi Gagal', '{{ $error }}');
                @endforeach
                // Re-open reject modal if it was closed
                const rejectModalEl = document.getElementById('rejectModal');
                if (rejectModalEl) {
                    const bsModal = new bootstrap.Modal(rejectModalEl);
                    bsModal.show();
                }
            @endif

        // Handle form submissions with loading state
        const approveForm = document.getElementById('approveForm');
            if (approveForm) {
                approveForm.addEventListener('submit', function (e) {
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
                rejectForm.addEventListener('submit', function (e) {
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
                rejectModal.addEventListener('show.bs.modal', function () {
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
    <!-- Document Activity Tracking Script -->
    <script>
        (function () {
            'use strict';

            const dokumenId = {{ $dokumen->id }};
            const currentUserId = {{ auth()->id() }};
            const currentUserName = '{{ auth()->user()->name }}';
            let activityInterval = null;
            let heartbeatInterval = null;
            let echoChannel = null;
            const activityUsers = {
                viewing: new Map(),
                editing: new Map()
            };

            console.log('🎯 Activity Tracking: Initializing...', {
                dokumenId,
                currentUserId,
                currentUserName
            });

            // Wait for Echo to be ready
            function waitForEcho(callback, maxAttempts = 20) {
                let attempts = 0;
                const checkEcho = setInterval(() => {
                    attempts++;
                    if (window.Echo) {
                        clearInterval(checkEcho);
                        console.log('✅ Echo is ready, initializing activity tracking');
                        callback();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkEcho);
                        console.error('❌ Echo not available after', maxAttempts, 'attempts');
                        // Fallback: use polling only
                        initActivityTrackingPollingOnly();
                    }
                }, 500);
            }

            // Initialize activity tracking
            function initActivityTracking() {
                console.log('🚀 Initializing activity tracking...');

                if (!window.Echo) {
                    console.warn('⚠️ Laravel Echo not available, using polling only');
                    initActivityTrackingPollingOnly();
                    return;
                }

                // Track initial viewing activity
                trackActivity('viewing');

                // Set up heartbeat (send activity every 30 seconds)
                heartbeatInterval = setInterval(() => {
                    trackActivity('viewing');
                }, 30000);

                // Listen to real-time activity changes
                try {
                    echoChannel = window.Echo.channel(`document.${dokumenId}`);
                    console.log('📡 Listening to channel: document.' + dokumenId);

                    echoChannel.listen('.document.activity.changed', (data) => {
                        console.log('📨 Activity change received:', data);
                        handleActivityChange(data);
                    });

                    echoChannel.error((error) => {
                        console.error('❌ Echo channel error:', error);
                    });

                    echoChannel.subscribed(() => {
                        console.log('✅ Subscribed to channel: document.' + dokumenId);
                    });
                } catch (error) {
                    console.error('❌ Error setting up Echo channel:', error);
                }

                // Load initial activities
                loadActivities();

                // Track activity every 5 seconds (backup polling)
                activityInterval = setInterval(() => {
                    loadActivities();
                }, 5000);

                // Track editing when user focuses on edit fields
                const editFields = document.querySelectorAll('input, textarea, select');
                editFields.forEach(field => {
                    field.addEventListener('focus', () => {
                        trackActivity('editing');
                    });
                    field.addEventListener('blur', () => {
                        // After 10 seconds of no editing, switch back to viewing
                        setTimeout(() => {
                            trackActivity('viewing');
                        }, 10000);
                    });
                });

                // Clean up on page unload
                window.addEventListener('beforeunload', () => {
                    stopActivity();
                });

                // Clean up on visibility change
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        stopActivity();
                    } else {
                        trackActivity('viewing');
                    }
                });
            }

            // Initialize with polling only (fallback)
            function initActivityTrackingPollingOnly() {
                console.log('🔄 Using polling-only mode for activity tracking');
                trackActivity('viewing');
                loadActivities();
                heartbeatInterval = setInterval(() => {
                    trackActivity('viewing');
                }, 30000);
                activityInterval = setInterval(() => {
                    loadActivities();
                }, 3000); // More frequent polling
            }

            // Track activity
            function trackActivity(activityType) {
                console.log('📤 Sending activity tracking request:', {
                    dokumen_id: dokumenId,
                    user_id: currentUserId,
                    activity_type: activityType
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch(`/api/documents/${dokumenId}/activity`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        activity_type: activityType
                    })
                })
                    .then(response => {
                        console.log('📥 Activity tracking response status:', response.status);
                        if (!response.ok) {
                            return response.text().then(text => {
                                console.error('❌ Activity tracking failed:', text);
                                throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('✅ Activity tracked successfully:', {
                                activity_type: activityType,
                                response: data
                            });
                        } else {
                            console.warn('⚠️ Activity tracking response (not success):', data);
                        }
                    })
                    .catch(err => {
                        console.error('❌ Error tracking activity:', err);
                    });
            }

            // Load current activities
            function loadActivities() {
                console.log('📥 Loading activities for dokumen:', dokumenId);

                fetch(`/api/documents/${dokumenId}/activities`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        console.log('📥 Activities response status:', response.status);
                        if (!response.ok) {
                            return response.text().then(text => {
                                console.error('❌ Failed to load activities:', text);
                                throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('📊 Activities loaded successfully:', {
                                viewing_count: data.activities.viewing?.length || 0,
                                editing_count: data.activities.editing?.length || 0,
                                viewing: data.activities.viewing,
                                editing: data.activities.editing
                            });
                            updateActivityDisplay(data.activities);
                        } else {
                            console.warn('⚠️ Activities response (not success):', data);
                        }
                    })
                    .catch(err => {
                        console.error('❌ Error loading activities:', err);
                    });
            }

            // Handle real-time activity change
            function handleActivityChange(data) {
                const { user_id, user_name, activity_type } = data;

                // Ignore own activities (we update via loadActivities)
                if (user_id === currentUserId) {
                    return;
                }

                if (activity_type === 'left') {
                    // Remove user from all activities
                    activityUsers.viewing.delete(user_id);
                    activityUsers.editing.delete(user_id);
                } else if (activity_type === 'viewing') {
                    activityUsers.viewing.set(user_id, {
                        name: user_name,
                        timestamp: data.timestamp
                    });
                    activityUsers.editing.delete(user_id);
                } else if (activity_type === 'editing') {
                    activityUsers.editing.set(user_id, {
                        name: user_name,
                        timestamp: data.timestamp
                    });
                }

                updateActivityDisplayFromMap();
            }

            // Update activity display from API data
            function updateActivityDisplay(activities) {
                // Clear existing
                activityUsers.viewing.clear();
                activityUsers.editing.clear();

                // Add viewing users
                activities.viewing?.forEach(user => {
                    if (user.user_id !== currentUserId) {
                        activityUsers.viewing.set(user.user_id, {
                            name: user.user_name,
                            role: user.user_role,
                            timestamp: user.last_activity_at
                        });
                    }
                });

                // Add editing users
                activities.editing?.forEach(user => {
                    if (user.user_id !== currentUserId) {
                        activityUsers.editing.set(user.user_id, {
                            name: user.user_name,
                            role: user.user_role,
                            timestamp: user.last_activity_at
                        });
                    }
                });

                updateActivityDisplayFromMap();
            }

            // Update UI from activity map
            function updateActivityDisplayFromMap() {
                const panel = document.getElementById('activity-panel');
                const viewersList = document.getElementById('viewers-items');
                const editorsWarning = document.getElementById('editors-warning');
                const editorNameSpan = document.getElementById('editor-name');

                if (!panel || !viewersList) {
                    console.error('❌ Activity panel elements not found');
                    return;
                }

                const totalViewers = activityUsers.viewing.size + 1; // +1 for current user
                const totalEditors = activityUsers.editing.size;

                console.log('📊 Updating activity display:', {
                    viewers: totalViewers,
                    editors: totalEditors,
                    viewingMap: Array.from(activityUsers.viewing.entries()),
                    editingMap: Array.from(activityUsers.editing.entries()),
                    currentUserId: currentUserId
                });

                // Show panel if there are other users OR if there are editors
                if (activityUsers.viewing.size > 0 || totalEditors > 0) {
                    panel.style.display = 'block';
                    console.log('✅ Showing activity panel - Found other users');
                } else {
                    panel.style.display = 'none';
                    console.log('ℹ️ Hiding activity panel - No other users detected');
                    console.log('💡 TIP: Activity tracking requires 2 DIFFERENT users logged in with DIFFERENT credentials');
                    console.log('💡 Current user ID:', currentUserId);
                    return;
                }

                // Update viewers list
                viewersList.innerHTML = '';

                // Add current user first
                const currentUserItem = createActivityItem(currentUserName, 'Anda', true);
                viewersList.appendChild(currentUserItem);
                console.log('➕ Added current user to viewers list');

                // Add other viewing users
                activityUsers.viewing.forEach((user, userId) => {
                    const item = createActivityItem(user.name, user.role || null, false);
                    viewersList.appendChild(item);
                    console.log('➕ Added viewer:', user.name);
                });

                // Update editors warning
                if (activityUsers.editing.size > 0) {
                    const firstEditor = Array.from(activityUsers.editing.values())[0];
                    editorNameSpan.textContent = firstEditor.name;
                    editorsWarning.style.display = 'block';
                    console.log('⚠️ Showing editor warning for:', firstEditor.name);
                } else {
                    editorsWarning.style.display = 'none';
                }
            }

            // Create activity item element
            function createActivityItem(name, role, isSelf) {
                const item = document.createElement('div');
                item.className = `activity-item ${isSelf ? 'self' : ''}`;

                item.innerHTML = `
                <div class="activity-item-status"></div>
                <div class="activity-item-name">${name}</div>
                ${role ? `<div class="activity-item-role">${role}</div>` : ''}
            `;

                return item;
            }

            // Stop activity tracking
            function stopActivity() {
                if (heartbeatInterval) {
                    clearInterval(heartbeatInterval);
                }
                if (activityInterval) {
                    clearInterval(activityInterval);
                }
                if (echoChannel) {
                    window.Echo.leave(`document.${dokumenId}`);
                }

                fetch(`/api/documents/${dokumenId}/activity/stop`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                }).catch(err => {
                    console.error('Error stopping activity:', err);
                });
            }

            // Initialize when DOM is ready and Echo is available
            function startActivityTracking() {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        waitForEcho(initActivityTracking);
                    });
                } else {
                    waitForEcho(initActivityTracking);
                }
            }

            // Start tracking
            startActivityTracking();
        })();
    </script>

@endsection



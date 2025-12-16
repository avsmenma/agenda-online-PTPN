@extends('layouts.app')

@section('title', 'Detail Dokumen Ditolak')

@push('styles')
<style>
/* Modern Rejected Document Detail Styles */
.rejected-detail-container {
    background: #f7fafc;
    min-height: calc(100vh - 80px);
    padding: 2rem 1.5rem;
}

.detail-header {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 25px rgba(245, 101, 101, 0.2);
    color: white;
}

.detail-header h1 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.detail-header .breadcrumb-custom {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 0.625rem 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.detail-header .breadcrumb-custom a {
    color: white;
    text-decoration: none;
    opacity: 0.9;
    transition: opacity 0.2s;
}

.detail-header .breadcrumb-custom a:hover {
    opacity: 1;
}

.detail-header .breadcrumb-custom .separator {
    opacity: 0.7;
    margin: 0 0.375rem;
}

.detail-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

.detail-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.detail-card-header {
    padding: 1.75rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f7fafc;
}

.detail-card-header h3 {
    font-size: 1.375rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.detail-card-body {
    padding: 2rem;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
}

.detail-table tr {
    border-bottom: 1px solid #e2e8f0;
}

.detail-table tr:last-child {
    border-bottom: none;
}

.detail-table th {
    width: 200px;
    padding: 1rem 0;
    font-weight: 600;
    color: #4a5568;
    text-align: left;
    vertical-align: top;
}

.detail-table td {
    padding: 1rem 0;
    color: #1a202c;
    font-weight: 500;
}

.status-card {
    background: linear-gradient(135deg, #4299e1 0%, #63b3ed 100%);
    color: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(66, 153, 225, 0.2);
}

.status-card h3 {
    font-size: 1.375rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.status-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
}

.status-badge .status-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.status-badge .status-value {
    font-size: 1.25rem;
    font-weight: 700;
}

.rejection-info {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-top: 1rem;
}

.rejection-info .rejection-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.rejection-info .rejection-value {
    font-size: 0.9375rem;
    font-weight: 600;
    line-height: 1.6;
}

.action-buttons {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
}

.btn-edit {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
    padding: 0.875rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9375rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
    color: white;
    text-decoration: none;
}

.btn-back {
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.875rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9375rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #cbd5e0;
    transform: translateY(-2px);
    color: #4a5568;
    text-decoration: none;
}

@media (max-width: 768px) {
    .detail-content {
        grid-template-columns: 1fr;
    }
    
    .detail-header {
        padding: 1.5rem;
    }
    
    .detail-card-body {
        padding: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-edit,
    .btn-back {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@section('content')
<div class="rejected-detail-container">
    <div class="container-fluid px-0">
        <!-- Header -->
        <div class="detail-header">
            <h1>
                <i class="fas fa-times-circle"></i>
                Detail Dokumen Ditolak
            </h1>
            <div class="breadcrumb-custom">
                <a href="{{ url('/dashboardB') }}">Home</a>
                <span class="separator">/</span>
                <a href="{{ route('documents.verifikasi.index') }}">Daftar Dokumen</a>
                <span class="separator">/</span>
                <span>Detail Ditolak</span>
            </div>
        </div>

        <div class="detail-content">
            <!-- Document Details -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3>
                        <i class="fas fa-file-invoice"></i>
                        Detail Dokumen
                    </h3>
                </div>
                <div class="detail-card-body">
                    <table class="detail-table">
                        <tr>
                            <th>No. Agenda</th>
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
                            <td class="text-right"><strong>Rp {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>{{ $dokumen->kategori ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Dokumen</th>
                            <td>{{ $dokumen->jenis_dokumen ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Masuk</th>
                            <td>{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-' }}</td>
                        </tr>
                    </table>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="{{ route('documents.verifikasi.edit', $dokumen) }}" class="btn-edit">
                            <i class="fas fa-edit"></i>
                            Edit Dokumen
                        </a>
                        <a href="{{ route('documents.verifikasi.index') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="status-card">
                <h3>
                    <i class="fas fa-info-circle"></i>
                    Status Dokumen
                </h3>
                
                <div class="status-badge">
                    <div class="status-label">Status Saat Ini</div>
                    <div class="status-value">Ditolak</div>
                </div>

                <div class="rejection-info">
                    <div class="rejection-label">Ditolak Oleh</div>
                    <div class="rejection-value">{{ $rejectedBy }}</div>
                </div>

                <div class="rejection-info">
                    <div class="rejection-label">Tanggal Ditolak</div>
                    <div class="rejection-value">
                        {{ $rejectedAt ? $rejectedAt->format('d/m/Y H:i') : '-' }}
                        @if($rejectedAt)
                            <br><small>({{ $rejectedAt->diffForHumans() }})</small>
                        @endif
                    </div>
                </div>

                @if($rejectionReason)
                <div class="rejection-info">
                    <div class="rejection-label">Alasan Penolakan</div>
                    <div class="rejection-value">{{ $rejectionReason }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


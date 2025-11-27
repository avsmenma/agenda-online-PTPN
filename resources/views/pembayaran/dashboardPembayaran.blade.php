@extends('layouts/app')

@section('content')
<style>
/* PEMBAYARAN DASHBOARD - Payment Processing Focus */
:root {
  --primary: #083E40;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-600: #4b5563;
  --gray-900: #111827;
}

body { background: var(--gray-50) !important; font-family: 'Inter', system-ui, sans-serif; }

.page-header {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  padding: 2rem 0;
  margin-bottom: 2rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.page-title {
  color: white;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 15px;
  margin: 0;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-left: 4px solid var(--card-color);
  transition: transform 0.2s;
}

.kpi-card:hover { transform: translateY(-4px); }

.kpi-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--gray-600);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.75rem;
}

.kpi-value {
  font-size: 32px;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.kpi-trend {
  font-size: 13px;
  color: var(--gray-600);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.action-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.action-btn {
  background: white;
  border: 2px solid var(--gray-200);
  border-radius: 12px;
  padding: 1.25rem;
  text-align: center;
  text-decoration: none;
  color: var(--gray-900);
  font-weight: 600;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.action-btn:hover {
  background: var(--primary);
  color: white;
  transform: translateY(-2px);
  border-color: var(--primary);
}

.action-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.action-btn:hover .action-icon {
  background: rgba(255,255,255,0.2);
}

.document-table {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--gray-100);
}

.table-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--gray-900);
}

.search-box {
  position: relative;
  margin-bottom: 1rem;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  border: 2px solid var(--gray-300);
  border-radius: 10px;
  font-size: 14px;
}

.search-input:focus {
  outline: none;
  border-color: var(--success);
}

.doc-item {
  padding: 1rem;
  border: 1px solid var(--gray-200);
  border-radius: 8px;
  margin-bottom: 0.75rem;
  transition: all 0.2s;
}

.doc-item:hover {
  background: var(--gray-50);
  border-color: var(--success);
}

.doc-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.75rem;
}

.doc-number {
  font-weight: 600;
  color: var(--gray-900);
  font-size: 15px;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.status-sudah { background: #d1fae5; color: #065f46; }
.status-siap { background: #dbeafe; color: #1e40af; }
.status-menunggu { background: #fef3c7; color: #92400e; }

.doc-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 0.75rem;
  font-size: 13px;
  color: var(--gray-600);
}

@media (max-width: 768px) {
  .kpi-grid, .action-grid { grid-template-columns: 1fr; }
}
</style>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="container">
    <h1 class="page-title">
      <i class="fas fa-money-check-alt me-3"></i>Dashboard Pembayaran
    </h1>
    <p class="page-subtitle">Kelola dan pantau proses pembayaran dokumen SPP</p>
  </div>
</div>

<div class="container">

  <!-- KPI CARDS -->
  <div class="kpi-grid">
    <div class="kpi-card" style="--card-color: #10b981;">
      <div class="kpi-label">Total Dokumen</div>
      <div class="kpi-value">{{ number_format($totalDokumen) }}</div>
      <div class="kpi-trend">
        <i class="fas fa-file-invoice"></i>
        <span>Di Pembayaran</span>
      </div>
    </div>

    <div class="kpi-card" style="--card-color: #3b82f6;">
      <div class="kpi-label">Siap Dibayar</div>
      <div class="kpi-value">{{ number_format($totalSiapBayar) }}</div>
      <div class="kpi-trend">
        <i class="fas fa-check-circle"></i>
        <span>Rp {{ number_format($nilaiSiapBayar / 1000000, 1) }}M</span>
      </div>
    </div>

    <div class="kpi-card" style="--card-color: #10b981;">
      <div class="kpi-label">Sudah Dibayar</div>
      <div class="kpi-value">{{ number_format($totalSelesai) }}</div>
      <div class="kpi-trend">
        <i class="fas fa-money-bill-wave"></i>
        <span>Rp {{ number_format($nilaiSudahBayar / 1000000, 1) }}M</span>
      </div>
    </div>

    <div class="kpi-card" style="--card-color: #f59e0b;">
      <div class="kpi-label">Menunggu Proses</div>
      <div class="kpi-value">{{ number_format($totalProses) }}</div>
      <div class="kpi-trend">
        <i class="fas fa-clock"></i>
        <span>Rp {{ number_format($nilaiMenunggu / 1000000, 1) }}M</span>
      </div>
    </div>

    @if($urgentDocs > 0)
    <div class="kpi-card" style="--card-color: #ef4444;">
      <div class="kpi-label">Urgent</div>
      <div class="kpi-value">{{ number_format($urgentDocs) }}</div>
      <div class="kpi-trend">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Perlu Perhatian</span>
      </div>
    </div>
    @endif

    <div class="kpi-card" style="--card-color: #8b5cf6;">
      <div class="kpi-label">Hari Ini</div>
      <div class="kpi-value">{{ number_format($todayDisbursed) }}</div>
      <div class="kpi-trend">
        <i class="fas fa-calendar-day"></i>
        <span>{{ $todayReady }} siap bayar</span>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="action-grid">
    <a href="{{ url('/dokumensPembayaran') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-list"></i></div>
      <span>Daftar Dokumen</span>
    </a>
    <a href="{{ url('/dokumensPembayaran?filter=siap_dibayar') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-check-double"></i></div>
      <span>Siap Dibayar</span>
    </a>
    <a href="{{ url('/rekapan-keterlambatan') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-chart-bar"></i></div>
      <span>Rekapan</span>
    </a>
    <a href="{{ url('/diagramPembayaran') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-chart-line"></i></div>
      <span>Diagram</span>
    </a>
  </div>

  <!-- DOCUMENTS LIST -->
  <div class="document-table">
    <div class="table-header">
      <h3 class="table-title">
        <i class="fas fa-file-invoice me-2"></i>Dokumen Terbaru
      </h3>
    </div>

    <div class="search-box">
      <form method="GET">
        <input type="text" name="search" class="search-input" 
               placeholder="Cari nomor agenda, SPP, atau penerima pembayaran..."
               value="{{ $search }}">
      </form>
    </div>

    @if($dokumenTerbaru->isEmpty())
      <div style="text-align: center; padding: 3rem; color: var(--gray-600);">
        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; margin-bottom: 1rem;"></i>
        <p>Tidak ada dokumen ditemukan</p>
      </div>
    @else
      @foreach($dokumenTerbaru as $dok)
      <div class="doc-item">
        <div class="doc-header">
          <div class="doc-number">
            <i class="fas fa-file-alt me-2"></i>
            {{ $dok->nomor_agenda }} - {{ $dok->nomor_spp }}
          </div>
          @if($dok->status_pembayaran)
            <span class="status-badge status-{{ $dok->status_pembayaran == 'sudah_dibayar' ? 'sudah' : ($dok->status_pembayaran == 'siap_dibayar' ? 'siap' : 'menunggu') }}">
              {{ ucwords(str_replace('_', ' ', $dok->status_pembayaran)) }}
            </span>
          @else
            <span class="status-badge status-menunggu">Menunggu</span>
          @endif
        </div>
        <div class="doc-details">
          <div>
            <strong>Nilai:</strong><br>
            Rp {{ number_format($dok->nilai_rupiah, 0, ',', '.') }}
          </div>
          <div>
            <strong>Dibayar Kepada:</strong><br>
            {{ $dok->dibayar_kepada ?? 'N/A' }}
          </div>
          <div>
            <strong>Tanggal:</strong><br>
            {{ $dok->tanggal_masuk ? $dok->tanggal_masuk->format('d M Y') : 'N/A' }}
          </div>
        </div>
      </div>
      @endforeach
    @endif
  </div>

</div>

@endsection
@extends('layouts/app')

@section('content')
@include('shared.modern-dashboard-style')

<style>
/* IbuB specific theme */
.page-header {
  --header-color-1: #3b82f6;
  --header-color-2: #2563eb;
}
.action-btn { --btn-hover-color: #3b82f6; }
</style>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="container">
    <h1 class="page-title">
      <i class="fas fa-route me-3"></i>Dashboard Ibu Yuni (Routing)
    </h1>
    <p class="page-subtitle">Kelola routing dan approval dokumen dari Ibu Tarapul ke departemen</p>
    <p class="page-timestamp">
      <i class="far fa-clock me-2"></i>
      {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY - HH:mm') }} WIB
    </p>
  </div>
</div>

<div class="container">

  <!-- KPI CARDS -->
  <div class="kpi-grid">
    <div class="kpi-card fade-in" style="--card-color: #3b82f6;">
      <div class="kpi-header">
        <div><div class="kpi-label">Total Dokumen</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(59, 130, 246, 0.1); --icon-color: #3b82f6;">
          <i class="fas fa-folder-open"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumen ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-inbox"></i> <span>Di handler IbuB</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #f59e0b;">
      <div class="kpi-header">
        <div><div class="kpi-label">Dalam Proses</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(245, 158, 11, 0.1); --icon-color: #f59e0b;">
          <i class="fas fa-spinner"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenProses ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-clock"></i> <span>Perlu direview</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #10b981;">
      <div class="kpi-header">
        <div><div class="kpi-label">Disetujui</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(16, 185, 129, 0.1); --icon-color: #10b981;">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenApproved ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-arrow-right"></i> <span>Diteruskan</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #ef4444;">
      <div class="kpi-header">
        <div><div class="kpi-label">Dikembalikan</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(239, 68, 68, 0.1); --icon-color: #ef4444;">
          <i class="fas fa-undo"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenRejected ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-exclamation-triangle"></i> <span>Perlu revisi</span></div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="action-grid">
    <a href="{{ url('/dokumensB') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-list"></i></div>
      <span>Daftar Dokumen</span>
    </a>
    <a href="{{ url('/dokumensB?status=new') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-file-import"></i></div>
      <span>Dokumen Baru</span>
    </a>
    <a href="{{ url('/pengembalianB') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-undo-alt"></i></div>
      <span>Pengembalian</span>
    </a>
    <a href="{{ url('/diagramB') }}" class="action-btn">
      <div class="action-icon"><i class="fas fa-chart-bar"></i></div>
      <span>Diagram</span>
    </a>
  </div>

  <!-- RECENT DOCUMENTS -->
  <div class="content-card">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-inbox"></i>
        Dokumen Terbaru
      </h3>
      <a href="{{ url('/dokumensB') }}" class="card-action">Lihat Semua →</a>
    </div>

    <div class="search-box">
      <form method="GET">
        <input type="text" name="search" class="search-input" 
               placeholder="Cari dokumen berdasarkan nomor agenda, SPP, atau pengirim..."
               value="{{ request('search') }}">
      </form>
    </div>

    @if(isset($dokumenTerbaru) && $dokumenTerbaru->count() > 0)
      <div class="doc-list">
        @foreach($dokumenTerbaru->take(10) as $dok)
        <div class="doc-item">
          <div class="doc-header">
            <div class="doc-number">
              <i class="fas fa-file-alt me-2"></i>
              {{ $dok->nomor_agenda ?? 'N/A' }} - {{ $dok->nomor_spp ?? 'N/A' }}
            </div>
            <span class="status-badge status-info">
              {{ ucwords(str_replace('_', ' ', $dok->status ?? 'pending')) }}
            </span>
          </div>
          <div class="doc-details">
            <div><strong>Pengirim:</strong> {{ $dok->nama_pengirim ?? 'N/A' }}</div>
            <div><strong>Tanggal:</strong> {{ $dok->tanggal_masuk ? $dok->tanggal_masuk->format('d M Y') : 'N/A' }}</div>
            <div><strong>Nilai:</strong> Rp {{ number_format($dok->nilai_rupiah ?? 0, 0, ',', '.') }}</div>
          </div>
        </div>
        @endforeach
      </div>
    @else
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
        <p>Tidak ada dokumen baru saat ini</p>
      </div>
    @endif
  </div>

</div>

@endsection

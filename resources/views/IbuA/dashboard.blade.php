@extends('layouts/app')

@section('content')
@include('shared.modern-dashboard-style')

<style>
.page-header { --header-color-1: #059669; --header-color-2: #047857; }
.action-btn { --btn-hover-color: #059669; }
</style>

<div class="page-header">
  <div class="container">
    <h1 class="page-title"><i class="fas fa-paper-plane me-3"></i>Dashboard Ibu Tarapul</h1>
    <p class="page-subtitle">Buat dan kirim dokumen SPP baru</p>
    <p class="page-timestamp"><i class="far fa-clock me-2"></i>{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY - HH:mm') }} WIB</p>
  </div>
</div>

<div class="container">

  <div class="kpi-grid">
    <div class="kpi-card fade-in" style="--card-color: #059669;">
      <div class="kpi-header">
        <div><div class="kpi-label">Total Dokumen</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(5, 150, 105, 0.1); --icon-color: #059669;"><i class="fas fa-file-alt"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumen ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-upload"></i> <span>Dokumen dibuat</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #f59e0b;">
      <div class="kpi-header">
        <div><div class="kpi-label">Dalam Proses</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(245, 158, 11, 0.1); --icon-color: #f59e0b;"><i class="fas fa-spinner"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenProses ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-clock"></i> <span>Sedang diproses</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #10b981;">
      <div class="kpi-header">
        <div><div class="kpi-label">Selesai</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(16, 185, 129, 0.1); --icon-color: #10b981;"><i class="fas fa-check-circle"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenSelesai ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-flag-checkered"></i> <span>Telah dibayar</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #ef4444;">
      <div class="kpi-header">
        <div><div class="kpi-label">Dikembalikan</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(239, 68, 68, 0.1); --icon-color: #ef4444;"><i class="fas fa-undo"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenDikembalikan ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-edit"></i> <span>Perlu perbaikan</span></div>
    </div>
  </div>

  <div class="action-grid">
    <a href="{{ url('/dokumen') }}" class="action-btn"><div class="action-icon"><i class="fas fa-list"></i></div><span>Daftar Dokumen</span></a>
    <a href="{{ url('/tambahDokumen') }}" class="action-btn" style="background: #059669; color: white; border-color: #059669;"><div class="action-icon" style="background: rgba(255,255,255,0.2);"><i class="fas fa-plus"></i></div><span>Buat Dokumen Baru</span></a>
    <a href="{{ url('/pengembalian') }}" class="action-btn"><div class="action-icon"><i class="fas fa-reply"></i></div><span>Pengembalian</span></a>
    <a href="{{ url('/diagram') }}" class="action-btn"><div class="action-icon"><i class="fas fa-chart-bar"></i></div><span>Diagram</span></a>
  </div>

  <div class="content-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-history"></i>Dokumen Terbaru Saya</h3>
      <a href="{{ url('/dokumen') }}" class="card-action">Lihat Semua →</a>
    </div>
    <div class="search-box">
      <form method="GET"><input type="text" name="search" class="search-input" placeholder="Cari dokumen berdasarkan nomor agenda atau SPP..." value="{{ request('search') }}"></form>
    </div>
    @if(isset($dokumenTerbaru) && $dokumenTerbaru->count() > 0)
      <div class="doc-list">
        @foreach($dokumenTerbaru->take(10) as $dok)
        <div class="doc-item">
          <div class="doc-header">
            <div class="doc-number"><i class="fas fa-file-alt me-2"></i>{{ $dok->nomor_agenda ?? 'N/A' }} - {{ $dok->nomor_spp ?? 'N/A' }}</div>
            <span class="status-badge {{ $dok->status == 'selesai' ? 'status-success' : ($dok->status == 'dikembalikan' ? 'status-danger' : 'status-warning') }}">
              {{ ucwords(str_replace('_', ' ', $dok->status ?? 'pending')) }}
            </span>
          </div>
          <div class="doc-details">
            <div><strong>Nilai:</strong> Rp {{ number_format($dok->nilai_rupiah ?? 0, 0, ',', '.') }}</div>
            <div><strong>Tanggal:</strong> {{ $dok->tanggal_masuk ? $dok->tanggal_masuk->format('d M Y') : 'N/A' }}</div>
            <div><strong>Handler:</strong> {{ $dok->current_handler ?? 'N/A' }}</div>
          </div>
        </div>
        @endforeach
      </div>
    @else
      <div class="empty-state"><div class="empty-icon"><i class="fas fa-inbox"></i></div><p>Belum ada dokumen. <a href="{{ url('/tambahDokumen') }}" style="color: #059669; font-weight: 600;">Buat dokumen baru →</a></p></div>
    @endif
  </div>

</div>
@endsection
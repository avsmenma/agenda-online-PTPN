@extends('layouts/app')

@section('content')
@include('shared.modern-dashboard-style')

<style>
.page-header { --header-color-1: #ec4899; --header-color-2: #db2777; }
.action-btn { --btn-hover-color: #ec4899; }
</style>

<div class="page-header">
  <div class="container">
    <h1 class="page-title"><i class="fas fa-calculator me-3"></i>Dashboard Akutansi</h1>
    <p class="page-subtitle">Verifikasi dan validasi dokumen keuangan</p>
    <p class="page-timestamp"><i class="far fa-clock me-2"></i>{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY - HH:mm') }} WIB</p>
  </div>
</div>

<div class="container">

  <div class="kpi-grid">
    <div class="kpi-card fade-in" style="--card-color: #ec4899;">
      <div class="kpi-header">
        <div><div class="kpi-label">Total Dokumen</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(236, 72, 153, 0.1); --icon-color: #ec4899;"><i class="fas fa-file-invoice"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumen ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-inbox"></i> <span>Di Akutansi</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #f59e0b;">
      <div class="kpi-header">
        <div><div class="kpi-label">Perlu Verifikasi</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(245, 158, 11, 0.1); --icon-color: #f59e0b;"><i class="fas fa-tasks"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenProses ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-clock"></i> <span>Menunggu review</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #10b981;">
      <div class="kpi-header">
        <div><div class="kpi-label">Terverifikasi</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(16, 185, 129, 0.1); --icon-color: #10b981;"><i class="fas fa-check-double"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenApproved ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-shield-alt"></i> <span>Valid & lengkap</span></div>
    </div>

    <div class="kpi-card fade-in" style="--card-color: #ef4444;">
      <div class="kpi-header">
        <div><div class="kpi-label">Dikembalikan</div></div>
        <div class="kpi-icon" style="--icon-bg: rgba(239, 68, 68, 0.1); --icon-color: #ef4444;"><i class="fas fa-exclamation-circle"></i></div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumenRejected ?? 0) }}</div>
      <div class="kpi-trend"><i class="fas fa-undo"></i> <span>Perlu koreksi</span></div>
    </div>
  </div>

  <div class="action-grid">
    <a href="{{ url('/dokumensAkutansi') }}" class="action-btn"><div class="action-icon"><i class="fas fa-list"></i></div><span>Daftar Dokumen</span></a>
    <a href="{{ url('/dokumensAkutansi?status=new') }}" class="action-btn"><div class="action-icon"><i class="fas fa-file-import"></i></div><span>Dokumen Baru</span></a>
    <a href="{{ url('/pengembalian-akutansi') }}" class="action-btn"><div class="action-icon"><i class="fas fa-reply"></i></div><span>Pengembalian</span></a>
    <a href="{{ url('/diagramAkutansi') }}" class="action-btn"><div class="action-icon"><i class="fas fa-chart-line"></i></div><span>Diagram</span></a>
  </div>

  <div class="content-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-clipboard-check"></i>Dokumen untuk Verifikasi</h3>
      <a href="{{ url('/dokumensAkutansi') }}" class="card-action">Lihat Semua →</a>
    </div>
    <div class="search-box">
      <form method="GET"><input type="text" name="search" class="search-input" placeholder="Cari nomor agenda, SPP, atau nilai dokumen..." value="{{ request('search') }}"></form>
    </div>
    @if(isset($dokumenTerbaru) && $dokumenTerbaru->count() > 0)
      <div class="doc-list">
        @foreach($dokumenTerbaru->take(10) as $dok)
        <div class="doc-item">
          <div class="doc-header">
            <div class="doc-number"><i class="fas fa-file-alt me-2"></i>{{ $dok->nomor_agenda ?? 'N/A' }} - {{ $dok->nomor_spp ?? 'N/A' }}</div>
            <span class="status-badge status-purple">{{ ucwords(str_replace('_', ' ', $dok->status ?? 'pending')) }}</span>
          </div>
          <div class="doc-details">
            <div><strong>Nilai:</strong> Rp {{ number_format($dok->nilai_rupiah ?? 0, 0, ',', '.') }}</div>
            <div><strong>Tanggal:</strong> {{ $dok->tanggal_masuk ? $dok->tanggal_masuk->format('d M Y') : 'N/A' }}</div>
            <div><strong>Status:</strong> {{ $dok->current_handler ?? 'N/A' }}</div>
          </div>
        </div>
        @endforeach
      </div>
    @else
      <div class="empty-state"><div class="empty-icon"><i class="fas fa-clipboard-check"></i></div><p>Tidak ada dokumen untuk diverifikasi</p></div>
    @endif
  </div>

</div>
@endsection
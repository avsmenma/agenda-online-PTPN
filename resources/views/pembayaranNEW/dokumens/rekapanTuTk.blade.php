@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  /* Dashboard Scorecards - Modern Design */
  .scorecard {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
    border: none;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    height: 160px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .scorecard::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    transition: all 0.4s ease;
  }

  .scorecard::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    transition: all 0.6s ease;
    opacity: 0;
  }

  .scorecard:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15), 0 4px 16px rgba(0, 0, 0, 0.08);
  }

  .scorecard:hover::after {
    opacity: 1;
    top: -60%;
    right: -60%;
  }

  .scorecard.merah::before {
    background: linear-gradient(180deg, #ff6b6b 0%, #ee5a6f 50%, #dc3545 100%);
  }

  .scorecard.merah:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(220, 53, 69, 0.5);
  }

  .scorecard.kuning::before {
    background: linear-gradient(180deg, #ffd93d 0%, #ffc107 50%, #f39c12 100%);
  }

  .scorecard.kuning:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(255, 193, 7, 0.5);
  }

  .scorecard.hijau::before {
    background: linear-gradient(180deg, #6bcf7f 0%, #28a745 50%, #1e7e34 100%);
  }

  .scorecard.hijau:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.5);
  }

  .scorecard.biru::before {
    background: linear-gradient(180deg, #4dabf7 0%, #007bff 50%, #0056b3 100%);
  }

  .scorecard.biru:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.5);
  }

  .scorecard-body {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .scorecard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
  }

  .scorecard-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    flex-shrink: 0;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }

  .scorecard:hover .scorecard-icon {
    transform: scale(1.15) rotate(5deg);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  }

  .scorecard.merah .scorecard-icon {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 50%, #dc3545 100%);
  }

  .scorecard.kuning .scorecard-icon {
    background: linear-gradient(135deg, #ffd93d 0%, #ffc107 50%, #f39c12 100%);
  }

  .scorecard.hijau .scorecard-icon {
    background: linear-gradient(135deg, #6bcf7f 0%, #28a745 50%, #1e7e34 100%);
  }

  .scorecard.biru .scorecard-icon {
    background: linear-gradient(135deg, #4dabf7 0%, #007bff 50%, #0056b3 100%);
  }

  .scorecard-title {
    font-size: 13px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
  }

  .scorecard-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .scorecard-value {
    font-size: 24px;
    font-weight: 700;
    color: #083E40;
    line-height: 1.2;
    margin: 0;
    word-break: break-word;
    letter-spacing: -0.5px;
    overflow-wrap: break-word;
  }

  /* Handle long numbers */
  .scorecard-value.long-number {
    font-size: 20px;
  }

  @media (max-width: 1200px) {
    .scorecard-value {
      font-size: 20px;
    }
    
    .scorecard-value.long-number {
      font-size: 18px;
    }
  }

  @media (max-width: 768px) {
    .scorecard {
      height: auto;
      min-height: 140px;
    }
    
    .scorecard-value {
      font-size: 20px;
    }
    
    .scorecard-value.long-number {
      font-size: 16px;
    }
    
    .scorecard-icon {
      width: 48px;
      height: 48px;
      font-size: 20px;
    }
  }

  .scorecard-label {
    font-size: 12px;
    color: #889717;
    font-weight: 500;
    margin: 0;
  }

  /* Filter Section */
  .filter-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.08);
  }

  .filter-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }

  .filter-group {
    flex: 1;
    min-width: 200px;
  }

  .filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #083E40;
    font-size: 14px;
  }

  .filter-group select,
  .filter-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
  }

  .filter-group select:focus,
  .filter-group input:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .btn-filter {
    background: #083E40;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-filter:hover {
    background: #889717;
    transform: translateY(-2px);
  }

  .btn-reset {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-reset:hover {
    background: #5a6268;
  }

  /* Table Styles */
  .table-container {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.08);
    overflow-x: auto;
  }

  .table {
    width: 100%;
    border-collapse: collapse;
  }

  .table thead {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    color: white;
  }

  .table thead th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
  }

  .table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
  }

  .table tbody tr:hover {
    background-color: #f8f9fa;
  }

  .table tbody td {
    padding: 12px 16px;
    font-size: 13px;
    vertical-align: middle;
  }

  /* Color coding untuk umur hutang */
  .row-hijau {
    background-color: #d4edda !important;
  }

  .row-kuning {
    background-color: #fff3cd !important;
  }

  .row-merah {
    background-color: #f8d7da !important;
  }

  .row-merah-gelap {
    background-color: #f5c6cb !important;
  }

  /* Progress Bar */
  .progress-container {
    width: 100%;
    height: 24px;
    background-color: #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
  }

  .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745 0%, #889717 100%);
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 11px;
    font-weight: 600;
  }

  /* Badge Status */
  .badge-status {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
  }

  .badge-lunas {
    background-color: #d4edda;
    color: #155724;
  }

  .badge-parsial {
    background-color: #fff3cd;
    color: #856404;
  }

  .badge-belum-lunas {
    background-color: #f8d7da;
    color: #721c24;
  }

  /* Widget Dokumen Terlama */
  .widget-terlama {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.08);
  }

  .widget-title {
    font-size: 18px;
    font-weight: 700;
    color: #083E40;
    margin-bottom: 16px;
  }

  .widget-item {
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .widget-item:last-child {
    border-bottom: none;
  }

  .widget-item-info {
    flex: 1;
  }

  .widget-item-label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 4px;
  }

  .widget-item-value {
    font-size: 15px;
    font-weight: 600;
    color: #083E40;
  }

  .widget-item-umur {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
  }

  .widget-item-umur.merah {
    background-color: #f8d7da;
    color: #721c24;
  }

  /* Pagination Styles */
  .pagination-wrapper {
    padding: 20px 25px;
    border-top: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 12px;
  }

  .pagination-info {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .pagination li {
    display: inline-block;
  }

  .pagination a,
  .pagination span {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    cursor: pointer;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    color: #083E40;
    transition: all 0.3s ease;
    min-width: 40px;
    height: 40px;
    text-decoration: none;
  }

  .pagination a:hover:not(.disabled) {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .pagination .disabled span,
  .pagination span.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #e0e0e0;
    color: #9e9e9e;
    border-color: #e0e0e0;
  }

  .pagination .active span {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  /* Icon Arrow Styling - Normalize Size */
  .pagination-wrapper .pagination a i,
  .pagination-wrapper .pagination span i {
    font-size: 14px !important;
    width: auto;
    height: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    margin: 0;
    padding: 0;
  }

  /* Override any Bootstrap default pagination styles */
  .pagination-wrapper .pagination .page-link,
  .pagination-wrapper .pagination .page-item .page-link {
    padding: 8px 12px !important;
    font-size: 13px !important;
    min-width: 40px !important;
    height: 40px !important;
    border: 2px solid rgba(8, 62, 64, 0.1) !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
  }

  .pagination-wrapper .pagination .page-link i,
  .pagination-wrapper .pagination .page-item .page-link i {
    font-size: 14px !important;
    line-height: 1 !important;
    vertical-align: middle;
  }

  .pagination-wrapper .pagination .page-link:focus,
  .pagination-wrapper .pagination .page-item .page-link:focus {
    box-shadow: none !important;
    outline: none !important;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .filter-row {
      flex-direction: column;
    }

    .filter-group {
      min-width: 100%;
    }

    .table-container {
      overflow-x: scroll;
    }

    .pagination-wrapper {
      flex-direction: column;
      align-items: stretch;
    }

    .pagination-info {
      text-align: center;
      margin-bottom: 10px;
    }

    .pagination {
      justify-content: center;
      width: 100%;
    }

    .pagination a,
    .pagination span {
      min-width: 36px;
      height: 36px;
      padding: 6px 10px;
      font-size: 12px;
    }
  }
</style>

<div class="container-fluid">
  <h2>Rekapan TU/TK</h2>

  <!-- Dashboard Scorecards -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="scorecard merah">
        <div class="scorecard-body">
          <div class="scorecard-header">
            <div class="scorecard-content" style="flex: 1;">
              <div class="scorecard-title">Total Outstanding</div>
              <div class="scorecard-value long-number">Rp {{ number_format($totalOutstanding ?? 0, 0, ',', '.') }}</div>
              <div class="scorecard-label">Belum dibayar</div>
            </div>
            <div class="scorecard-icon">
              <i class="fa-solid fa-money-bill-wave"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="scorecard kuning">
        <div class="scorecard-body">
          <div class="scorecard-header">
            <div class="scorecard-content" style="flex: 1;">
              <div class="scorecard-title">Dokumen Belum Lunas</div>
              <div class="scorecard-value">{{ number_format($totalDokumenBelumLunas ?? 0, 0, ',', '.') }}</div>
              <div class="scorecard-label">Dokumen</div>
            </div>
            <div class="scorecard-icon">
              <i class="fa-solid fa-file-invoice"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="scorecard hijau">
        <div class="scorecard-body">
          <div class="scorecard-header">
            <div class="scorecard-content" style="flex: 1;">
              <div class="scorecard-title">Total Terbayar Tahun Ini</div>
              <div class="scorecard-value long-number">Rp {{ number_format($totalTerbayarTahunIni ?? 0, 0, ',', '.') }}</div>
              <div class="scorecard-label">Tahun {{ date('Y') }}</div>
            </div>
            <div class="scorecard-icon">
              <i class="fa-solid fa-check-circle"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="scorecard biru">
        <div class="scorecard-body">
          <div class="scorecard-header">
            <div class="scorecard-content" style="flex: 1;">
              <div class="scorecard-title">Jatuh Tempo Minggu Ini</div>
              <div class="scorecard-value">{{ number_format($jatuhTempoMingguIni ?? 0, 0, ',', '.') }}</div>
              <div class="scorecard-label">Dokumen kritis</div>
            </div>
            <div class="scorecard-icon">
              <i class="fa-solid fa-clock"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <form method="GET" action="{{ route('pembayaran.rekapanTuTk') }}" id="filterForm">
      <div class="filter-row">
        <div class="filter-group">
          <label>Status Pembayaran</label>
          <select name="status_pembayaran" class="form-control">
            <option value="">Semua</option>
            <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas</option>
            <option value="belum_lunas" {{ request('status_pembayaran') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
            <option value="parsial" {{ request('status_pembayaran') == 'parsial' ? 'selected' : '' }}>Parsial</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Kategori</label>
          <select name="kategori" class="form-control">
            <option value="">Semua</option>
            @foreach($kategoris ?? [] as $kat)
              <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
            @endforeach
          </select>
        </div>
        <div class="filter-group">
          <label>Umur Hutang</label>
          <select name="umur_hutang" class="form-control">
            <option value="">Semua</option>
            <option value="kurang_30" {{ request('umur_hutang') == 'kurang_30' ? 'selected' : '' }}>&lt; 30 Hari</option>
            <option value="30_60" {{ request('umur_hutang') == '30_60' ? 'selected' : '' }}>30 - 60 Hari</option>
            <option value="lebih_60" {{ request('umur_hutang') == 'lebih_60' ? 'selected' : '' }}>&gt; 60 Hari</option>
            <option value="lebih_1_tahun" {{ request('umur_hutang') == 'lebih_1_tahun' ? 'selected' : '' }}>&gt; 1 Tahun</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Posisi Dokumen</label>
          <select name="posisi_dokumen" class="form-control">
            <option value="">Semua</option>
            @foreach($posisiDokumens ?? [] as $pos)
              <option value="{{ $pos }}" {{ request('posisi_dokumen') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="filter-row">
        <div class="filter-group" style="flex: 2;">
          <label>Search (Agenda, No. SPP, Vendor, No. Kontrak)</label>
          <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari...">
        </div>
        <div class="filter-group" style="display: flex; align-items: flex-end; gap: 10px;">
          <button type="submit" class="btn-filter">Filter</button>
          <a href="{{ route('pembayaran.rekapanTuTk') }}" class="btn-reset">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <!-- Widget 5 Dokumen Terlama -->
  @if(isset($dokumenTerlama) && $dokumenTerlama->count() > 0)
  <div class="widget-terlama">
    <div class="widget-title">5 Dokumen Terlama Belum Dibayar</div>
    @foreach($dokumenTerlama as $doc)
    <div class="widget-item">
      <div class="widget-item-info">
        <div class="widget-item-label">{{ $doc->AGENDA ?? '-' }} - {{ $doc->NO_SPP ?? '-' }}</div>
        <div class="widget-item-value">{{ Str::limit($doc->VENDOR ?? '-', 50) }}</div>
      </div>
      <div class="text-right">
        <div style="font-weight: 600; color: #083E40;">Rp {{ number_format($doc->BELUM_DIBAYAR ?? 0, 0, ',', '.') }}</div>
        <div class="widget-item-umur {{ $doc->warna_umur_hutang ?? 'merah' }}">
          {{ $doc->UMUR_HUTANG_HARI ?? 0 }} Hari
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  <!-- Table -->
  <div class="table-container mt-4">
    <table class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Agenda</th>
          <th>No. SPP</th>
          <th>Tgl SPP</th>
          <th>Vendor</th>
          <th>Kategori</th>
          <th>Nilai</th>
          <th>Status Pembayaran</th>
          <th>Progress</th>
          <th>Umur Hutang</th>
          <th>Posisi Dokumen</th>
        </tr>
      </thead>
      <tbody>
        @forelse($dokumens ?? [] as $index => $dokumen)
        @php
          $status = $dokumen->status_pembayaran ?? 'belum_lunas';
          $persentase = $dokumen->persentase_pembayaran ?? 0;
          $warnaUmur = $dokumen->warna_umur_hutang ?? 'hijau';
          $rowClass = 'row-' . $warnaUmur;
        @endphp
        <tr class="{{ $rowClass }}">
          <td>{{ ($dokumens->currentPage() - 1) * $dokumens->perPage() + $index + 1 }}</td>
          <td><strong>{{ $dokumen->AGENDA ?? '-' }}</strong></td>
          <td>{{ $dokumen->NO_SPP ?? '-' }}</td>
          <td>{{ $dokumen->TGL_SPP ?? '-' }}</td>
          <td>{{ Str::limit($dokumen->VENDOR ?? '-', 30) }}</td>
          <td>{{ $dokumen->KATEGORI ?? '-' }}</td>
          <td><strong>Rp {{ number_format($dokumen->NILAI ?? 0, 0, ',', '.') }}</strong></td>
          <td>
            @if($status == 'lunas')
              <span class="badge-status badge-lunas">Lunas</span>
            @elseif($status == 'parsial')
              <span class="badge-status badge-parsial">Parsial</span>
            @else
              <span class="badge-status badge-belum-lunas">Belum Lunas</span>
            @endif
          </td>
          <td>
            <div class="progress-container">
              <div class="progress-bar" style="width: {{ min($persentase, 100) }}%">
                {{ number_format($persentase, 1) }}%
              </div>
            </div>
            <div style="font-size: 11px; color: #6c757d; margin-top: 4px;">
              Dibayar: Rp {{ number_format($dokumen->JUMLAH_DIBAYAR ?? 0, 0, ',', '.') }} | 
              Sisa: Rp {{ number_format($dokumen->BELUM_DIBAYAR ?? 0, 0, ',', '.') }}
            </div>
          </td>
          <td>
            <strong>{{ $dokumen->UMUR_HUTANG_HARI ?? 0 }}</strong> Hari
          </td>
          <td>{{ $dokumen->POSISI_DOKUMEN ?? '-' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="11" style="text-align: center; padding: 40px; color: #6c757d;">
            <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
            <h5>Belum ada dokumen</h5>
            <p>Tidak ada dokumen untuk filter yang dipilih</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Pagination -->
    @if(isset($dokumens) && $dokumens->hasPages())
    <div class="pagination-wrapper">
      <div class="pagination-info">
        Menampilkan {{ $dokumens->firstItem() }} sampai {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} hasil
      </div>
      <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($dokumens->onFirstPage())
          <li class="disabled">
            <span class="disabled">
              <i class="fa-solid fa-chevron-left"></i>
            </span>
          </li>
        @else
          <li>
            <a href="{{ $dokumens->appends(request()->query())->previousPageUrl() }}" rel="prev">
              <i class="fa-solid fa-chevron-left"></i>
            </a>
          </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($dokumens->getUrlRange(max(1, $dokumens->currentPage() - 2), min($dokumens->lastPage(), $dokumens->currentPage() + 2)) as $page => $url)
          @if ($page == $dokumens->currentPage())
            <li class="active">
              <span>{{ $page }}</span>
            </li>
          @else
            <li>
              <a href="{{ $dokumens->appends(request()->query())->url($url) }}">{{ $page }}</a>
            </li>
          @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($dokumens->hasMorePages())
          <li>
            <a href="{{ $dokumens->appends(request()->query())->nextPageUrl() }}" rel="next">
              <i class="fa-solid fa-chevron-right"></i>
            </a>
          </li>
        @else
          <li class="disabled">
            <span class="disabled">
              <i class="fa-solid fa-chevron-right"></i>
            </span>
          </li>
        @endif
      </ul>
    </div>
    @endif
  </div>
</div>

@endsection

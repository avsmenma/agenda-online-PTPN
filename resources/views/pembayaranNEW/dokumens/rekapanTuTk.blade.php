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

  /* Table Styles - Match daftarPembayaran */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
    overflow: hidden;
  }

  .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(8, 62, 64, 0.3) transparent;
  }

  .table-responsive::-webkit-scrollbar {
    height: 12px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: rgba(8, 62, 64, 0.05);
    border-radius: 6px;
    margin: 0 20px;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.3), rgba(136, 151, 23, 0.4));
    border-radius: 6px;
    border: 2px solid rgba(255, 255, 255, 0.8);
  }

  .table {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1400px;
    width: 100%;
  }

  .table thead {
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .table thead th {
    background: #083E40;
    color: white;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    white-space: nowrap;
    text-align: left;
  }

  .table thead th:first-child {
    border-top-left-radius: 8px;
  }

  .table thead th:last-child {
    border-top-right-radius: 8px;
  }

  .table tbody tr {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    background: white;
  }

  .table tbody tr:hover {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.02) 0%, rgba(136, 151, 23, 0.02) 100%);
  }

  .table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(8, 62, 64, 0.08);
    vertical-align: middle;
    font-size: 13px;
  }

  .table tbody tr:last-child td {
    border-bottom: none;
  }

  /* Color coding untuk umur hutang */
  .row-hijau {
    background-color: #d4edda !important;
  }

  .row-hijau:hover {
    background: linear-gradient(135deg, rgba(212, 237, 218, 0.8) 0%, rgba(212, 237, 218, 0.6) 100%) !important;
  }

  .row-kuning {
    background-color: #fff3cd !important;
  }

  .row-kuning:hover {
    background: linear-gradient(135deg, rgba(255, 243, 205, 0.8) 0%, rgba(255, 243, 205, 0.6) 100%) !important;
  }

  .row-merah {
    background-color: #f8d7da !important;
  }

  .row-merah:hover {
    background: linear-gradient(135deg, rgba(248, 215, 218, 0.8) 0%, rgba(248, 215, 218, 0.6) 100%) !important;
  }

  .row-merah-gelap {
    background-color: #f5c6cb !important;
  }

  .row-merah-gelap:hover {
    background: linear-gradient(135deg, rgba(245, 198, 203, 0.8) 0%, rgba(245, 198, 203, 0.6) 100%) !important;
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
    margin-top: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 12px;
  }

  /* Pagination Styles - Same as daftarPembayaran */
  .pagination-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
  }

  .per-page-wrapper label {
    font-size: 13px;
    color: #083E40;
    font-weight: 500;
    margin: 0;
  }

  .per-page-wrapper select {
    padding: 6px 12px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    border-radius: 8px;
    background: white;
    color: #083E40;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    min-width: 60px;
    transition: all 0.3s ease;
  }

  .per-page-wrapper select:hover {
    border-color: #889717;
  }

  .per-page-wrapper select:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
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

  .pagination a:hover:not(.disabled),
  .btn-pagination:hover:not(:disabled) {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .btn-pagination {
    padding: 8px 12px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background-color: white;
    cursor: pointer;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    color: #083E40;
    transition: all 0.3s ease;
    min-width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    font-family: inherit;
  }

  .btn-pagination.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-pagination:disabled {
    background: #e0e0e0;
    color: #9e9e9e;
    border-color: rgba(8, 62, 64, 0.1);
    cursor: not-allowed;
    opacity: 0.6;
  }

  .btn-pagination-nav {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: rgba(8, 62, 64, 0.15);
  }

  .btn-pagination-nav:hover:not(:disabled) {
    background: linear-gradient(135deg, #0a4f52 0%, #889717 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-pagination-dots {
    background: transparent;
    border: none;
    color: #999;
    cursor: default;
    padding: 8px 4px;
  }

  /* Clean Pagination Styles - Match rekapanDokumen */
  .pagination-wrapper {
    padding: 20px 25px;
    border-top: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .pagination {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
  }

  .pagination button {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
    margin: 0;
  }

  .pagination button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: transparent;
  }

  .pagination button:disabled {
    background: #e0e0e0;
    color: #9e9e9e;
    border-color: rgba(8, 62, 64, 0.1);
    cursor: not-allowed;
    opacity: 0.6;
  }

  .pagination button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .pagination a {
    text-decoration: none;
    display: inline-flex;
  }

  .pagination button:disabled:hover {
    transform: none;
    box-shadow: none;
  }

  .btn-chevron {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .btn-chevron:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-chevron i {
    font-size: 14px;
    line-height: 1;
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
      padding: 15px !important;
    }

    .pagination-wrapper > div {
      flex-direction: column;
      align-items: stretch !important;
      gap: 12px !important;
    }

    .pagination {
      justify-content: center;
      flex-wrap: wrap;
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
      <!-- Data Source Selector -->
      <div class="filter-row" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef;">
        <div class="filter-group" style="flex: 1;">
          <label style="font-weight: 700; color: #083E40; font-size: 14px;">
            <i class="fa-solid fa-database me-2"></i>Pilih Sumber Data
          </label>
          <select name="data_source" class="form-control" id="dataSourceSelect" style="font-weight: 600; padding: 12px; border: 2px solid #083E40; border-radius: 8px;" onchange="this.form.submit()">
            <option value="input_ks" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_ks' ? 'selected' : '' }}>Input KS (tu_tk_2023)</option>
            <option value="input_pupuk" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_pupuk' ? 'selected' : '' }}>Input Pupuk (tu_tk_pupuk_2023)</option>
            <option value="input_tan" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_tan' ? 'selected' : '' }}>Input TAN (tu_tk_tan_2023)</option>
            <option value="input_vd" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_vd' ? 'selected' : '' }}>Input VD (tu_tk_vd_2023)</option>
          </select>
        </div>
      </div>
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
        @if((request('data_source', $dataSource ?? 'input_ks')) == 'input_ks')
        <div class="filter-group">
          <label>Kategori</label>
          <select name="kategori" class="form-control">
            <option value="">Semua</option>
            @foreach($kategoris ?? [] as $kat)
              <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
            @endforeach
          </select>
        </div>
        @endif
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
        <div class="filter-group" style="display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap;">
          <button type="submit" class="btn-filter">Filter</button>
          <a href="{{ route('pembayaran.rekapanTuTk') }}" class="btn-reset">Reset</a>
          <div style="display: flex; gap: 8px;">
            <a href="{{ route('pembayaran.exportRekapanTuTk', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn-export" style="padding: 8px 16px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i class="fa-solid fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('pembayaran.exportRekapanTuTk', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn-export" style="padding: 8px 16px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
          </div>
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
    <div class="table-responsive">
      <table class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Agenda</th>
          <th>No. SPP</th>
          <th>Tgl SPP</th>
          <th>Vendor</th>
          @if((request('data_source', $dataSource ?? 'input_ks')) == 'input_ks')
          <th>Kategori</th>
          @endif
          <th>Nilai</th>
          <th>Status Pembayaran</th>
          <th>Progress</th>
          <th>Umur Hutang</th>
          <th>Posisi Dokumen</th>
          <th>Aksi</th>
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
          @if((request('data_source', $dataSource ?? 'input_ks')) == 'input_ks')
          <td>{{ $dokumen->KATEGORI ?? '-' }}</td>
          @endif
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
              @php
                $currentDataSource = request('data_source', $dataSource ?? 'input_ks');
                $belumDibayarValue = $currentDataSource == 'input_ks' ? ($dokumen->BELUM_DIBAYAR ?? 0) : ($dokumen->BELUM_DIBAYAR_1 ?? 0);
              @endphp
              Dibayar: Rp {{ number_format($dokumen->JUMLAH_DIBAYAR ?? 0, 0, ',', '.') }} | 
              Sisa: Rp {{ number_format($belumDibayarValue, 0, ',', '.') }}
            </div>
          </td>
          <td>
            <strong>{{ $dokumen->UMUR_HUTANG_HARI ?? 0 }}</strong> Hari
          </td>
          <td>{{ $dokumen->POSISI_DOKUMEN ?? '-' }}</td>
          <td>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
              @php
                $currentDataSource = request('data_source', $dataSource ?? 'input_ks');
                $kontrolId = $currentDataSource == 'input_ks' ? ($dokumen->KONTROL ?? null) : ($dokumen->EXTRA_COL_0 ?? null);
                $belumDibayarValue = $currentDataSource == 'input_ks' ? ($dokumen->BELUM_DIBAYAR ?? 0) : ($dokumen->BELUM_DIBAYAR_1 ?? 0);
              @endphp
              <button class="btn-input-payment" onclick="openPaymentModal('{{ $kontrolId }}', {{ $dokumen->NILAI ?? 0 }}, {{ $dokumen->JUMLAH_DIBAYAR ?? 0 }}, {{ $belumDibayarValue }}, '{{ $currentDataSource }}')" style="padding: 6px 12px; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s ease;">
                <i class="fa-solid fa-money-bill-wave me-1"></i> Input Pembayaran
              </button>
              <button class="btn-timeline" onclick="openTimelineModal('{{ $kontrolId }}', '{{ $currentDataSource }}')" style="padding: 6px 12px; background: linear-gradient(135deg, #889717 0%, #a0b02a 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s ease;">
                <i class="fa-solid fa-history me-1"></i> Timeline
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="{{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_ks' ? '12' : '11' }}" style="text-align: center; padding: 40px; color: #6c757d;">
            <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
            <h5>Belum ada dokumen</h5>
            <p>Tidak ada dokumen untuk filter yang dipilih</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>

    <!-- Pagination -->
    @if($dokumens->hasPages())
    <div class="pagination-wrapper">
      <div class="text-muted" style="font-size: 13px; color: #083E40;">
        Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} dokumen
      </div>

      <!-- Per Page Selector -->
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
        <label for="perPageSelect" style="font-size: 13px; color: #083E40; font-weight: 500; margin: 0;">Tampilkan per halaman:</label>
        <select id="perPageSelect" onchange="changePerPage(this.value)" style="padding: 6px 12px; border: 2px solid rgba(8, 62, 64, 0.15); border-radius: 8px; background: white; color: #083E40; font-size: 13px; font-weight: 500; cursor: pointer;">
          <option value="10" {{ ($perPage ?? request('per_page', 25)) == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ ($perPage ?? request('per_page', 25)) == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ ($perPage ?? request('per_page', 25)) == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ ($perPage ?? request('per_page', 25)) == 100 ? 'selected' : '' }}>100</option>
        </select>
      </div>

      <div class="pagination">
        {{-- Previous Page Link --}}
        @if($dokumens->onFirstPage())
          <button class="btn-chevron" disabled>
            <i class="fa-solid fa-chevron-left"></i>
          </button>
        @else
          <a href="{{ $dokumens->previousPageUrl() }}">
            <button class="btn-chevron">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
          </a>
        @endif

        {{-- Pagination Elements --}}
        @if($dokumens->hasPages())
          {{-- First page --}}
          @if($dokumens->currentPage() > 3)
            <a href="{{ $dokumens->url(1) }}">
              <button>1</button>
            </a>
          @endif

          {{-- Dots --}}
          @if($dokumens->currentPage() > 4)
            <button disabled>...</button>
          @endif

          {{-- Range of pages --}}
          @for($i = max(1, $dokumens->currentPage() - 2); $i <= min($dokumens->lastPage(), $dokumens->currentPage() + 2); $i++)
            @if($dokumens->currentPage() == $i)
              <button class="active">{{ $i }}</button>
            @else
              <a href="{{ $dokumens->url($i) }}">
                <button>{{ $i }}</button>
              </a>
            @endif
          @endfor

          {{-- Dots --}}
          @if($dokumens->currentPage() < $dokumens->lastPage() - 3)
            <button disabled>...</button>
          @endif

          {{-- Last page --}}
          @if($dokumens->currentPage() < $dokumens->lastPage() - 2)
            <a href="{{ $dokumens->url($dokumens->lastPage()) }}">
              <button>{{ $dokumens->lastPage() }}</button>
            </a>
          @endif
        @endif

        {{-- Next Page Link --}}
        @if($dokumens->hasMorePages())
          <a href="{{ $dokumens->nextPageUrl() }}">
            <button class="btn-chevron">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </a>
        @else
          <button class="btn-chevron" disabled>
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>

<!-- Modal: Input Pembayaran Bertahap -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
        <h5 class="modal-title" id="paymentModalLabel">
          <i class="fa-solid fa-money-bill-wave me-2"></i>Input Pembayaran Bertahap
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Progress Bar -->
        <div class="payment-progress-container" style="margin-bottom: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-weight: 600; color: #083E40;">Progress Pembayaran</span>
            <span id="progressPercentage" style="font-weight: 600; color: #083E40;">0%</span>
          </div>
          <div class="progress" style="height: 24px; border-radius: 12px; background: #e9ecef; overflow: hidden;">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #083E40 0%, #889717 100%); transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px;">
              0%
            </div>
          </div>
          <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6c757d;">
            <span>Dibayar: <strong id="totalDibayar">Rp 0</strong></span>
            <span>Sisa: <strong id="sisaBayar">Rp 0</strong></span>
            <span>Total: <strong id="totalNilai">Rp 0</strong></span>
          </div>
        </div>

        <!-- Payment History -->
        <div id="paymentHistory" style="margin-bottom: 24px; max-height: 200px; overflow-y: auto;">
          <h6 style="font-weight: 600; color: #083E40; margin-bottom: 12px;">Riwayat Pembayaran</h6>
          <div id="paymentHistoryList" style="display: flex; flex-direction: column; gap: 8px;">
            <p class="text-muted text-center" style="padding: 20px;">Belum ada riwayat pembayaran</p>
          </div>
        </div>

        <!-- Form Input Pembayaran -->
        <form id="paymentForm">
          <input type="hidden" id="paymentKontrol" name="kontrol">
          <input type="hidden" id="paymentDataSource" name="data_source" value="{{ request('data_source', $dataSource ?? 'input_ks') }}">
          <input type="hidden" id="paymentSequence" name="payment_sequence" value="1">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="paymentTanggal" class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="paymentTanggal" name="tanggal_bayar" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="paymentJumlah" class="form-label">Jumlah Pembayaran (Rp) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="paymentJumlah" name="jumlah" step="0.01" min="0.01" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="paymentKeterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="paymentKeterangan" name="keterangan" rows="2" placeholder="Keterangan pembayaran (opsional)"></textarea>
          </div>

          <div class="alert alert-info" style="background: #e7f3ff; border-color: #b3d9ff; color: #004085;">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Info:</strong> Pembayaran akan disimpan sebagai pembayaran ke-<span id="currentSequence">1</span>. Maksimal 6 kali pembayaran bertahap.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="submitPayment()" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
          <i class="fa-solid fa-save me-2"></i>Simpan Pembayaran
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page'); // Reset to page 1 when changing per_page
    window.location.href = url.toString();
}

let currentKontrol = null;
let currentNilai = 0;
let currentDibayar = 0;
let currentBelumDibayar = 0;
let currentDataSource = 'input_ks';

function openPaymentModal(kontrol, nilai, dibayar, belumDibayar, dataSource = 'input_ks') {
    currentKontrol = kontrol;
    currentNilai = parseFloat(nilai) || 0;
    currentDibayar = parseFloat(dibayar) || 0;
    currentBelumDibayar = parseFloat(belumDibayar) || 0;
    currentDataSource = dataSource || 'input_ks';

    // Set form values
    document.getElementById('paymentKontrol').value = kontrol;
    document.getElementById('paymentDataSource').value = currentDataSource;
    document.getElementById('paymentTanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('paymentJumlah').value = '';
    document.getElementById('paymentKeterangan').value = '';

    // Calculate next sequence
    const nextSequence = Math.min(6, Math.floor(currentDibayar / (currentNilai / 6)) + 1);
    document.getElementById('paymentSequence').value = nextSequence;
    document.getElementById('currentSequence').textContent = nextSequence;

    // Update progress bar
    updateProgressBar();

    // Load payment history
    loadPaymentHistory(kontrol);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

function updateProgressBar() {
    const percentage = currentNilai > 0 ? (currentDibayar / currentNilai) * 100 : 0;
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressBar').textContent = percentage.toFixed(1) + '%';
    document.getElementById('progressPercentage').textContent = percentage.toFixed(1) + '%';
    document.getElementById('totalDibayar').textContent = 'Rp ' + formatNumber(currentDibayar);
    document.getElementById('sisaBayar').textContent = 'Rp ' + formatNumber(currentBelumDibayar);
    document.getElementById('totalNilai').textContent = 'Rp ' + formatNumber(currentNilai);
}

function loadPaymentHistory(kontrol) {
    const dataSource = currentDataSource || 'input_ks';
    fetch(`/rekapan-tu-tk/payment-logs/${kontrol}?data_source=${dataSource}`)
        .then(response => response.json())
        .then(data => {
            const historyList = document.getElementById('paymentHistoryList');
            if (data.length === 0) {
                historyList.innerHTML = '<p class="text-muted text-center" style="padding: 20px;">Belum ada riwayat pembayaran</p>';
            } else {
                historyList.innerHTML = data.map(log => `
                    <div style="padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #083E40;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Pembayaran ke-${log.payment_sequence}</strong>
                                <div style="font-size: 12px; color: #6c757d; margin-top: 4px;">
                                    ${new Date(log.tanggal_bayar).toLocaleDateString('id-ID')} - Rp ${formatNumber(log.jumlah)}
                                </div>
                                ${log.keterangan ? `<div style="font-size: 11px; color: #6c757d; margin-top: 4px;">${log.keterangan}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading payment history:', error);
        });
}

function submitPayment() {
    const form = document.getElementById('paymentForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    // Show loading
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';

    fetch('/rekapan-tu-tk/payment-installment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success message
            alert('Pembayaran berhasil disimpan!');
            
            // Close modal and reload page
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            modal.hide();
            window.location.reload();
        } else {
            alert('Gagal menyimpan pembayaran: ' + result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan pembayaran');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function openTimelineModal(kontrol, dataSource = 'input_ks') {
    // Show loading
    const timelineList = document.getElementById('timelineList');
    timelineList.innerHTML = '<div class="text-center p-4"><i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Memuat timeline...</p></div>';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('timelineModal'));
    modal.show();

    // Load timeline data
    fetch(`/rekapan-tu-tk/position-timeline/${kontrol}?data_source=${dataSource}`)
        .then(response => response.json())
        .then(data => {
            const timeline = data.timeline;
            const tuTk = data.tu_tk;

            // Update document info
            document.getElementById('timelineDocInfo').innerHTML = `
                <strong>Agenda:</strong> ${tuTk.AGENDA || '-'} | 
                <strong>No. SPP:</strong> ${tuTk.NO_SPP || '-'} | 
                <strong>Vendor:</strong> ${tuTk.VENDOR || '-'}
            `;

            // Render timeline
            if (timeline.length === 0) {
                timelineList.innerHTML = '<div class="text-center p-4 text-muted">Belum ada riwayat tracking</div>';
            } else {
                timelineList.innerHTML = timeline.map(item => `
                    <div class="timeline-item" style="position: relative; padding-left: 40px; padding-bottom: 24px; border-left: 2px solid ${item.color};">
                        <div class="timeline-icon" style="position: absolute; left: -10px; top: 0; width: 20px; height: 20px; background: ${item.color}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">
                            <i class="fa-solid ${item.icon}"></i>
                        </div>
                        <div class="timeline-content" style="background: #f8f9fa; padding: 16px; border-radius: 8px; border-left: 4px solid ${item.color};">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <h6 style="margin: 0; color: #083E40; font-weight: 600;">${item.title}</h6>
                                <span style="font-size: 12px; color: #6c757d;">${new Date(item.date).toLocaleString('id-ID')}</span>
                            </div>
                            <p style="margin: 0 0 8px 0; color: #495057; font-size: 14px;">${item.description}</p>
                            ${item.keterangan ? `<p style="margin: 0; color: #6c757d; font-size: 12px; font-style: italic;">${item.keterangan}</p>` : ''}
                            <div style="margin-top: 8px; font-size: 11px; color: #6c757d;">
                                <i class="fa-solid fa-user me-1"></i> ${item.changed_by || 'System'}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading timeline:', error);
            timelineList.innerHTML = '<div class="alert alert-danger">Gagal memuat timeline</div>';
        });
}
</script>

<!-- Modal: Timeline Tracking -->
<div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
        <h5 class="modal-title" id="timelineModalLabel">
          <i class="fa-solid fa-history me-2"></i>Timeline Tracking Dokumen
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="timelineDocInfo" style="padding: 12px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #083E40;">
          <!-- Document info will be loaded here -->
        </div>
        <div id="timelineList" style="max-height: 500px; overflow-y: auto;">
          <!-- Timeline items will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection

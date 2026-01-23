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

  /* Statistics Cards */
  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
  }

  .stat-card-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
  }

  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
  }

  .stat-icon.total { background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); }
  .stat-icon.belum { background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); }
  .stat-icon.siap { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
  .stat-icon.sudah { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }

  .stat-title {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
  }

  .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
  }

  .stat-nilai {
    font-size: 13px;
    font-weight: 600;
    color: #28a745;
  }

  /* Filter Section */
  .filter-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 25px;
    border-radius: 16px;
    margin-bottom: 25px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 13px;
  }

  .form-control, .form-select {
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-filter {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 13px;
    color: white;
  }

  .btn-filter:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
    color: white;
  }

  .btn-reset {
    background: white;
    border: 2px solid rgba(8, 62, 64, 0.2);
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 13px;
    color: #083E40;
  }

  .btn-reset:hover {
    background: #083E40;
    color: white;
  }

  /* Rekapan Table Toggle */
  .rekapan-toggle {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
    border: 2px solid #c8e6c9;
    border-radius: 12px;
    padding: 15px 20px;
    margin-top: 15px;
  }

  .rekapan-toggle-header {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
  }

  .rekapan-toggle-header input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
  }

  .rekapan-toggle-header label {
    font-weight: 600;
    color: #1b5e20;
    cursor: pointer;
    margin: 0;
  }

  /* Column Checkboxes */
  .column-checkboxes {
    display: none;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px dashed #c8e6c9;
  }

  .column-checkboxes.show {
    display: block;
  }

  .column-checkboxes-title {
    font-weight: 600;
    color: #2e7d32;
    margin-bottom: 12px;
    font-size: 13px;
  }

  .column-checkboxes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 10px;
  }

  .column-checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .column-checkbox-item:hover {
    border-color: #889717;
    background: #f8fff8;
  }

  .column-checkbox-item.selected {
    border-color: #28a745;
    background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
  }

  .column-checkbox-item input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
  }

  .column-checkbox-item label {
    font-size: 12px;
    color: #333;
    cursor: pointer;
    margin: 0;
    flex: 1;
  }

  .column-checkbox-item .order-badge {
    background: #28a745;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
    display: none;
  }

  .column-checkbox-item.selected .order-badge {
    display: inline-block;
  }

  /* Selected Columns Preview */
  .selected-columns-preview {
    margin-top: 12px;
    padding: 10px;
    background: #fff8e1;
    border-radius: 8px;
    font-size: 12px;
    color: #856404;
    display: none;
  }

  .selected-columns-preview.show {
    display: block;
  }

  .selected-columns-preview strong {
    color: #5d4e37;
  }

  /* Table Styles */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .table-header {
    padding: 20px 25px;
    border-bottom: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .table-header h5 {
    margin: 0;
    font-weight: 700;
    color: #083E40;
  }

  .table {
    margin: 0;
  }

  .table thead th {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 14px 12px;
    font-size: 11px;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
  }

  .table tbody tr:hover {
    background: rgba(136, 151, 23, 0.05);
  }

  .table tbody td {
    padding: 12px;
    font-size: 13px;
    color: #2c3e50;
    vertical-align: middle;
  }

  /* Vendor Group Header */
  .vendor-group-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    padding: 15px 20px;
    margin-top: 20px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .vendor-group-header:first-child {
    margin-top: 0;
  }

  .vendor-group-header h6 {
    margin: 0;
    font-weight: 700;
    font-size: 14px;
  }

  .vendor-group-stats {
    display: flex;
    gap: 15px;
    font-size: 12px;
  }

  .vendor-group-stats span {
    padding: 4px 10px;
    border-radius: 15px;
    background: rgba(255,255,255,0.2);
  }

  .vendor-table {
    border-radius: 0 0 10px 10px;
    overflow: hidden;
    margin-bottom: 20px;
    border: 1px solid rgba(8, 62, 64, 0.1);
    border-top: none;
  }

  /* Badge Styles */
  .badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .badge-belum { background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; }
  .badge-siap { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; }
  .badge-sudah { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }

  /* Pagination */
  .pagination-wrapper {
    padding: 20px 25px;
    border-top: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
  }

  .pagination button {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    cursor: pointer;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    color: #083E40;
    transition: all 0.3s ease;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pagination button:hover:not(:disabled) {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .pagination button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .pagination a {
    text-decoration: none;
    color: inherit;
  }

  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    border: none !important;
  }

  .btn-chevron:hover:not(:disabled) {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    transform: translateY(-2px);
  }

  .btn-chevron:disabled {
    background: #e0e0e0;
    color: #9e9e9e;
    cursor: not-allowed;
  }

  /* Grand Total */
  .grand-total-row {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%) !important;
    font-weight: 700;
  }

  .grand-total-row td {
    border-top: 2px solid #28a745 !important;
  }

  /* Export Buttons */
  .export-buttons {
    display: flex;
    gap: 10px;
  }

  .btn-export {
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
  }

  .btn-export-excel {
    background: linear-gradient(135deg, #217346 0%, #1e6b3f 100%);
    color: white;
  }

  .btn-export-excel:hover {
    background: linear-gradient(135deg, #1e6b3f 0%, #185a34 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 115, 70, 0.3);
  }

  .btn-export-pdf {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .btn-export-pdf:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
  }

  /* Print styles for PDF */
  @media print {
    .no-print {
      display: none !important;
    }

    .table-container {
      box-shadow: none !important;
      border: 1px solid #ddd !important;
    }

    .vendor-header-row td {
      background: #083E40 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .subtotal-row, .grand-total-row {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
  }
</style>

<h2>{{ $title }}</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Total Dokumen</div>
          <div class="stat-value">{{ $statistics['total_documents'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon total">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Belum Siap Bayar</div>
          <div class="stat-value">{{ $statistics['by_status']['belum_dibayar'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai_by_status']['belum_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon belum">
          <i class="fa-solid fa-clock"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Siap Dibayar</div>
          <div class="stat-value">{{ $statistics['by_status']['siap_dibayar'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai_by_status']['siap_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon siap">
          <i class="fa-solid fa-hourglass-half"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Sudah Dibayar</div>
          <div class="stat-value">{{ $statistics['by_status']['sudah_dibayar'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai_by_status']['sudah_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon sudah">
          <i class="fa-solid fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
  <form method="GET" action="{{ route('reports.pembayaran.index') }}" id="filterForm">
    <div class="row g-3 align-items-end">
      <div class="col-md-2">
        <label for="status_pembayaran" class="form-label">Status Pembayaran</label>
        <select name="status_pembayaran" id="status_pembayaran" class="form-select">
          <option value="">Semua Status</option>
          <option value="belum_siap_dibayar" {{ $selectedStatus == 'belum_siap_dibayar' ? 'selected' : '' }}>Belum Siap Dibayar</option>
          <option value="siap_dibayar" {{ $selectedStatus == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
          <option value="sudah_dibayar" {{ $selectedStatus == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
        </select>
      </div>
      <div class="col-md-2">
        <label for="year" class="form-label">Tahun</label>
        <select name="year" id="year" class="form-select">
          <option value="">Semua Tahun</option>
          @foreach($availableYears as $yr)
            <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
          @endforeach
          @if($availableYears->isEmpty())
            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
          @endif
        </select>
      </div>
      <div class="col-md-2">
        <label for="month" class="form-label">Bulan</label>
        <select name="month" id="month" class="form-select">
          <option value="">Semua Bulan</option>
          @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
            <option value="{{ $index + 1 }}" {{ $selectedMonth == ($index + 1) ? 'selected' : '' }}>{{ $bulan }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label for="search" class="form-label">Cari Dokumen</label>
        <input type="text" name="search" id="search" class="form-control"
               placeholder="Nomor agenda, SPP, penerima..."
               value="{{ $search }}">
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-filter me-2">
          <i class="fa-solid fa-filter me-1"></i> Filter
        </button>
        <a href="{{ route('reports.pembayaran.index') }}" class="btn btn-reset">
          <i class="fa-solid fa-refresh me-1"></i> Reset
        </a>
      </div>
    </div>

    <!-- Rekapan Table Toggle -->
    <div class="rekapan-toggle">
      <div class="rekapan-toggle-header">
        <input type="checkbox" id="enableRekapanTable" name="mode" value="rekapan_table" {{ $mode == 'rekapan_table' ? 'checked' : '' }}>
        <label for="enableRekapanTable">
          <i class="fa-solid fa-table-columns me-1"></i>
          Tampilkan Tabel Rekapan (Grouped by Vendor)
        </label>
      </div>

      <!-- Column Checkboxes -->
      <div class="column-checkboxes {{ $mode == 'rekapan_table' ? 'show' : '' }}" id="columnCheckboxes">
        <div class="column-checkboxes-title">
          <i class="fa-solid fa-check-square me-1"></i>
          Pilih kolom yang ingin ditampilkan (urutan sesuai pilihan):
        </div>
        <div class="column-checkboxes-grid" id="columnsGrid">
          @foreach($availableColumns as $key => $label)
            <div class="column-checkbox-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}" data-column="{{ $key }}">
              <input type="checkbox"
                     id="col_{{ $key }}"
                     name="columns[]"
                     value="{{ $key }}"
                     {{ in_array($key, $selectedColumns) ? 'checked' : '' }}>
              <label for="col_{{ $key }}">{{ $label }}</label>
              <span class="order-badge">{{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}</span>
            </div>
          @endforeach
        </div>

        <div class="selected-columns-preview {{ count($selectedColumns) > 0 ? 'show' : '' }}" id="selectedPreview">
          <strong><i class="fa-solid fa-sort-amount-down me-1"></i>Urutan Kolom:</strong>
          <span id="selectedColumnsList">
            @foreach($selectedColumns as $col)
              {{ $availableColumns[$col] ?? $col }}{{ !$loop->last ? ' → ' : '' }}
            @endforeach
          </span>
        </div>
      </div>
    </div>
  </form>
</div>

@if($mode == 'rekapan_table' && $rekapanByVendor && count($selectedColumns) > 0)
  <!-- Rekapan Table by Vendor - Single Table for Easy Export -->
  <div class="table-container">
    <div class="table-header">
      <div>
        <h5 style="margin-bottom: 5px;">
          <i class="fa-solid fa-table me-2"></i>
          Tabel Rekapan per Vendor
        </h5>
        <span class="text-muted">Total {{ $rekapanByVendor->count() }} vendor | {{ $statistics['total_documents'] }} dokumen</span>
      </div>
      <div class="export-buttons no-print">
        <button type="button" class="btn-export btn-export-excel" onclick="exportRekapanTableToExcel()">
          <i class="fa-solid fa-file-excel"></i> Export Excel
        </button>
        <button type="button" class="btn-export btn-export-pdf" onclick="exportRekapanTableToPDF()">
          <i class="fa-solid fa-file-pdf"></i> Export PDF
        </button>
      </div>
    </div>

    <div class="table-responsive">
      @php
        $grandTotalNilai = 0;
        $grandTotalBelum = 0;
        $grandTotalSiap = 0;
        $grandTotalSudah = 0;
        $globalNo = 0;

        // Find the index of first value column (calculated once)
        $valueColumns = ['nilai_rupiah', 'nilai_belum_siap_bayar', 'nilai_siap_bayar', 'nilai_sudah_dibayar'];
        $firstValueIndex = null;
        foreach($selectedColumns as $idx => $col) {
          if (in_array($col, $valueColumns)) {
            $firstValueIndex = $idx;
            break;
          }
        }
        // +1 for the "No" column
        $colspanCount = $firstValueIndex !== null ? $firstValueIndex + 1 : count($selectedColumns) + 1;
        $totalColumns = count($selectedColumns) + 1; // +1 for No column
      @endphp

      <table class="table table-hover mb-0" id="rekapanTable">
        <thead>
          <tr>
            <th style="width: 50px;">No</th>
            @foreach($selectedColumns as $col)
              <th>{{ $availableColumns[$col] ?? $col }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($rekapanByVendor as $vendorData)
            @php
              $grandTotalNilai += $vendorData['total_nilai'];
              $grandTotalBelum += $vendorData['total_belum_dibayar'];
              $grandTotalSiap += $vendorData['total_siap_dibayar'];
              $grandTotalSudah += $vendorData['total_sudah_dibayar'];
            @endphp

            <!-- Vendor Header Row -->
            <tr class="vendor-header-row">
              <td colspan="{{ $totalColumns }}" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; font-weight: 700; padding: 12px 15px;">
                <span class="vendor-name">{{ $vendorData['vendor'] }}</span>
                <span class="vendor-stats no-export" style="float: right; font-weight: 400; font-size: 12px;">
                  <i class="fa-solid fa-building me-2"></i>{{ $vendorData['count'] }} dok | Rp {{ number_format($vendorData['total_nilai'], 0, ',', '.') }}
                </span>
              </td>
            </tr>

            <!-- Document Rows -->
            @foreach($vendorData['documents'] as $index => $doc)
              @php $globalNo++; @endphp
              <tr>
                <td style="text-align: center;">{{ $globalNo }}</td>
                @foreach($selectedColumns as $col)
                  <td>
                    @switch($col)
                      @case('nomor_agenda')
                        <strong>{{ $doc->nomor_agenda }}</strong>
                        @break
                      @case('dibayar_kepada')
                        {{ $doc->dibayar_kepada ?? '-' }}
                        @break
                      @case('jenis_pembayaran')
                        {{ $doc->jenis_pembayaran ?? '-' }}
                        @break
                      @case('jenis_sub_pekerjaan')
                        {{ $doc->jenis_sub_pekerjaan ?? '-' }}
                        @break
                      @case('nomor_miro')
                        {{ $doc->nomor_miro ?? '-' }}
                        @break
                      @case('nomor_spp')
                        {{ $doc->nomor_spp ?? '-' }}
                        @break
                      @case('tanggal_spp')
                        {{ $doc->tanggal_spp ? $doc->tanggal_spp->format('d/m/Y') : '-' }}
                        @break
                      @case('tanggal_berita_acara')
                        {{ $doc->tanggal_berita_acara ? $doc->tanggal_berita_acara->format('d/m/Y') : '-' }}
                        @break
                      @case('umur_dokumen_tanggal_masuk')
                        @if($doc->tanggal_masuk)
                          @php
                            $tanggalMasuk = \Carbon\Carbon::parse($doc->tanggal_masuk)->startOfDay();
                            $hariIni = \Carbon\Carbon::now()->startOfDay();
                            // Selalu hitung dari tanggal yang lebih lama ke tanggal sekarang
                            if ($tanggalMasuk->lte($hariIni)) {
                              $umurDokumen = (int) $tanggalMasuk->diffInDays($hariIni);
                            } else {
                              // Jika tanggal masuk lebih baru dari hari ini, set ke 0
                              $umurDokumen = 0;
                            }
                          @endphp
                          <span class="badge-status badge-proses">{{ $umurDokumen }} hari</span>
                        @else
                          -
                        @endif
                        @break
                      @case('umur_dokumen_tanggal_spp')
                        @if($doc->tanggal_spp)
                          @php
                            $tanggalSpp = \Carbon\Carbon::parse($doc->tanggal_spp)->startOfDay();
                            $hariIni = \Carbon\Carbon::now()->startOfDay();
                            // Selalu hitung dari tanggal yang lebih lama ke tanggal sekarang
                            if ($tanggalSpp->lte($hariIni)) {
                              $umurDokumenSpp = (int) $tanggalSpp->diffInDays($hariIni);
                            } else {
                              // Jika tanggal SPP lebih baru dari hari ini, set ke 0
                              $umurDokumenSpp = 0;
                            }
                          @endphp
                          <span class="badge-status badge-proses">{{ $umurDokumenSpp }} hari</span>
                        @else
                          -
                        @endif
                        @break
                      @case('umur_dokumen_tanggal_ba')
                        @if($doc->tanggal_berita_acara)
                          @php
                            $tanggalBa = \Carbon\Carbon::parse($doc->tanggal_berita_acara)->startOfDay();
                            $hariIni = \Carbon\Carbon::now()->startOfDay();
                            // Selalu hitung dari tanggal yang lebih lama ke tanggal sekarang
                            if ($tanggalBa->lte($hariIni)) {
                              $umurDokumenBa = (int) $tanggalBa->diffInDays($hariIni);
                            } else {
                              // Jika tanggal BA lebih baru dari hari ini, set ke 0
                              $umurDokumenBa = 0;
                            }
                          @endphp
                          <span class="badge-status badge-proses">{{ $umurDokumenBa }} hari</span>
                        @else
                          -
                        @endif
                        @break
                      @case('no_berita_acara')
                        {{ $doc->no_berita_acara ?? '-' }}
                        @break
                      @case('tanggal_berakhir_ba')
                        {{ $doc->tanggal_berakhir_ba ?? '-' }}
                        @break
                      @case('no_spk')
                        {{ $doc->no_spk ?? '-' }}
                        @break
                      @case('tanggal_spk')
                        {{ $doc->tanggal_spk ? $doc->tanggal_spk->format('d/m/Y') : '-' }}
                        @break
                      @case('tanggal_berakhir_spk')
                        {{ $doc->tanggal_berakhir_spk ? $doc->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                        @break
                      @case('nilai_rupiah')
                        <strong>Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                        @break
                      @case('nilai_belum_siap_bayar')
                        @if($doc->computed_status == 'belum_siap_dibayar')
                          <span class="text-warning">Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                        @else
                          -
                        @endif
                        @break
                      @case('nilai_siap_bayar')
                        @if($doc->computed_status == 'siap_dibayar')
                          <span class="text-info">Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                        @else
                          -
                        @endif
                        @break
                      @case('nilai_sudah_dibayar')
                        @if($doc->computed_status == 'sudah_dibayar')
                          <span class="text-success">Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                        @else
                          -
                        @endif
                        @break
                      @default
                        {{ $doc->$col ?? '-' }}
                    @endswitch
                  </td>
                @endforeach
              </tr>
            @endforeach

            <!-- Subtotal Row -->
            <tr class="subtotal-row" style="background: #f8f9fa; font-weight: 600;">
              <td colspan="{{ $colspanCount }}" class="text-end" style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;">
                <strong>Subtotal {{ Str::limit($vendorData['vendor'], 30) }}:</strong>
              </td>
              @foreach($selectedColumns as $idx => $col)
                @if($firstValueIndex !== null && $idx >= $firstValueIndex)
                  @if($col == 'nilai_rupiah')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong>Rp {{ number_format($vendorData['total_nilai'], 0, ',', '.') }}</strong></td>
                  @elseif($col == 'nilai_belum_siap_bayar')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong class="text-warning">Rp {{ number_format($vendorData['total_belum_dibayar'], 0, ',', '.') }}</strong></td>
                  @elseif($col == 'nilai_siap_bayar')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong class="text-info">Rp {{ number_format($vendorData['total_siap_dibayar'], 0, ',', '.') }}</strong></td>
                  @elseif($col == 'nilai_sudah_dibayar')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong class="text-success">Rp {{ number_format($vendorData['total_sudah_dibayar'], 0, ',', '.') }}</strong></td>
                  @elseif(in_array($col, ['umur_dokumen_tanggal_masuk', 'umur_dokumen_tanggal_spp', 'umur_dokumen_tanggal_ba']))
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;">-</td>
                  @else
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"></td>
                  @endif
                @endif
              @endforeach
            </tr>

            <!-- Empty Row Separator -->
            <tr class="separator-row">
              <td colspan="{{ $totalColumns }}" style="height: 10px; background: #fff; border: none;"></td>
            </tr>
          @endforeach

          <!-- Grand Total Row -->
          <tr class="grand-total-row" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); font-weight: 700;">
            <td colspan="{{ $colspanCount }}" class="text-end" style="border-top: 3px solid #28a745; padding: 15px;">
              <strong style="font-size: 14px;"><i class="fa-solid fa-calculator me-2"></i>GRAND TOTAL:</strong>
            </td>
            @foreach($selectedColumns as $idx => $col)
              @if($firstValueIndex !== null && $idx >= $firstValueIndex)
                @if($col == 'nilai_rupiah')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong style="font-size: 14px;">Rp {{ number_format($grandTotalNilai, 0, ',', '.') }}</strong></td>
                @elseif($col == 'nilai_belum_siap_bayar')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong class="text-warning" style="font-size: 14px;">Rp {{ number_format($grandTotalBelum, 0, ',', '.') }}</strong></td>
                @elseif($col == 'nilai_siap_bayar')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong class="text-info" style="font-size: 14px;">Rp {{ number_format($grandTotalSiap, 0, ',', '.') }}</strong></td>
                @elseif($col == 'nilai_sudah_dibayar')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong class="text-success" style="font-size: 14px;">Rp {{ number_format($grandTotalSudah, 0, ',', '.') }}</strong></td>
                @elseif(in_array($col, ['umur_dokumen_tanggal_masuk', 'umur_dokumen_tanggal_spp', 'umur_dokumen_tanggal_ba']))
                  <td style="border-top: 3px solid #28a745; padding: 15px;">-</td>
                @else
                  <td style="border-top: 3px solid #28a745; padding: 15px;"></td>
                @endif
              @endif
            @endforeach
          </tr>
        </tbody>
      </table>
    </div>
  </div>

@else
  <!-- Normal Table -->
  <div class="table-container">
    <div class="table-header">
      <div>
        <h5>
          <i class="fa-solid fa-list me-2"></i>
          Daftar Dokumen
        </h5>
      </div>
      <div class="export-buttons no-print">
        <button type="button" class="btn-export btn-export-excel" onclick="exportNormalTableToExcel()">
          <i class="fa-solid fa-file-excel"></i> Export Excel
        </button>
        <button type="button" class="btn-export btn-export-pdf" onclick="exportNormalTableToPDF()">
          <i class="fa-solid fa-file-pdf"></i> Export PDF
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Nomor SPP</th>
            <th>Tgl Diterima</th>
            <th>Dibayar Kepada</th>
            <th>Nilai Rupiah</th>
            <th>Status</th>
            <th>Tgl Dibayar</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            <tr>
              <td style="text-align: center;">{{ $dokumens->firstItem() + $index }}</td>
              <td>
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br><small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              </td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ $dokumen->sent_to_pembayaran_at ? $dokumen->sent_to_pembayaran_at->format('d/m/Y') : '-' }}</td>
              <td>{{ Str::limit($dokumen->dibayar_kepada, 25) ?? '-' }}</td>
              <td><strong>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong></td>
              <td style="text-align: center;">
                @if($dokumen->computed_status == 'sudah_dibayar')
                  <span class="badge-status badge-sudah">Sudah Dibayar</span>
                @elseif($dokumen->computed_status == 'siap_dibayar')
                  <span class="badge-status badge-siap">Siap Dibayar</span>
                @else
                  <span class="badge-status badge-belum">Belum Siap Dibayar</span>
                @endif
              </td>
              <td>{{ $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Tidak ada data dokumen.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($dokumens->hasPages())
      <div class="pagination-wrapper">
        <div class="text-muted" style="font-size: 13px;">
          Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} dokumen
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
@endif

<!-- ExcelJS for Styled Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<!-- FileSaver for download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<!-- html2pdf for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
// Export to Excel Function with Styling (for rekapan table - now using server-side)
async function exportToExcel() {
  // This function is now replaced by exportRekapanTableToExcel
  exportRekapanTableToExcel();
}

// Legacy function - kept for backward compatibility
async function exportToPDF() {
  // This function is now replaced by exportRekapanTableToPDF
  exportRekapanTableToPDF();
}

// Old client-side export function (deprecated - now using server-side)
async function exportToExcelOld() {
  const table = document.getElementById('rekapanTable');
  if (!table) {
    alert('Tabel tidak ditemukan!');
    return;
  }

  // Show loading
  const btn = document.querySelector('.btn-export-excel');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  try {
    // Create workbook and worksheet
    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Sistem Pembayaran';
    workbook.created = new Date();

    const worksheet = workbook.addWorksheet('Rekapan Pembayaran', {
      pageSetup: { paperSize: 9, orientation: 'landscape' }
    });

    // Get table data
    const rows = table.querySelectorAll('tr');
    let excelRowIndex = 1;
    const colCount = table.querySelector('thead tr').children.length;

    // Set column widths
    for (let i = 1; i <= colCount; i++) {
      worksheet.getColumn(i).width = i === 1 ? 8 : 22;
    }

    // Process each row
    rows.forEach((row, rowIndex) => {
      // Skip separator rows
      if (row.classList.contains('separator-row')) return;

      const cells = row.querySelectorAll('th, td');
      const excelRow = worksheet.getRow(excelRowIndex);
      let colIndex = 1;

      cells.forEach((cell) => {
        const colspan = parseInt(cell.getAttribute('colspan')) || 1;

        // Get cell text - for vendor header, only get vendor name
        let cellText;
        if (row.classList.contains('vendor-header-row')) {
          const vendorName = cell.querySelector('.vendor-name');
          cellText = vendorName ? vendorName.innerText.trim() : cell.innerText.trim();
        } else {
          // Remove no-export elements from text
          const clone = cell.cloneNode(true);
          const noExport = clone.querySelectorAll('.no-export');
          noExport.forEach(el => el.remove());
          cellText = clone.innerText.trim();
        }

        // Set cell value
        const excelCell = excelRow.getCell(colIndex);
        excelCell.value = cellText;

        // Merge cells if colspan > 1
        if (colspan > 1) {
          worksheet.mergeCells(excelRowIndex, colIndex, excelRowIndex, colIndex + colspan - 1);
        }

        // Apply styles based on row type
        if (row.parentElement.tagName === 'THEAD') {
          // Header row - dark green background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FF083E40' }
            };
            c.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 11 };
            c.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            c.border = {
              top: { style: 'thin', color: { argb: 'FF000000' } },
              left: { style: 'thin', color: { argb: 'FF000000' } },
              bottom: { style: 'thin', color: { argb: 'FF000000' } },
              right: { style: 'thin', color: { argb: 'FF000000' } }
            };
          }
        } else if (row.classList.contains('vendor-header-row')) {
          // Vendor header row - teal background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FF0A4F52' }
            };
            c.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 11 };
            c.alignment = { horizontal: 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'thin', color: { argb: 'FF000000' } },
              left: { style: 'thin', color: { argb: 'FF000000' } },
              bottom: { style: 'thin', color: { argb: 'FF000000' } },
              right: { style: 'thin', color: { argb: 'FF000000' } }
            };
          }
        } else if (row.classList.contains('subtotal-row')) {
          // Subtotal row - light gray background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FFF0F0F0' }
            };
            c.font = { bold: true, size: 11 };
            c.alignment = { horizontal: i === colIndex ? 'right' : 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'medium', color: { argb: 'FFAAAAAA' } },
              left: { style: 'thin', color: { argb: 'FFCCCCCC' } },
              bottom: { style: 'medium', color: { argb: 'FFAAAAAA' } },
              right: { style: 'thin', color: { argb: 'FFCCCCCC' } }
            };
          }
        } else if (row.classList.contains('grand-total-row')) {
          // Grand total row - light green background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FFD4EDDA' }
            };
            c.font = { bold: true, size: 12, color: { argb: 'FF155724' } };
            c.alignment = { horizontal: i === colIndex ? 'right' : 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'medium', color: { argb: 'FF28A745' } },
              left: { style: 'thin', color: { argb: 'FF28A745' } },
              bottom: { style: 'medium', color: { argb: 'FF28A745' } },
              right: { style: 'thin', color: { argb: 'FF28A745' } }
            };
          }
        } else {
          // Data rows - white/alternating background
          const isEven = excelRowIndex % 2 === 0;
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: isEven ? 'FFF9F9F9' : 'FFFFFFFF' }
            };
            c.font = { size: 10 };
            c.alignment = { horizontal: i === 1 ? 'center' : 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'thin', color: { argb: 'FFDDDDDD' } },
              left: { style: 'thin', color: { argb: 'FFDDDDDD' } },
              bottom: { style: 'thin', color: { argb: 'FFDDDDDD' } },
              right: { style: 'thin', color: { argb: 'FFDDDDDD' } }
            };

            // Color for value columns based on content
            if (cellText.includes('Rp')) {
              c.alignment.horizontal = 'right';
              // Check if it's a warning/info/success value
              if (cell.querySelector('.text-warning') || cell.classList.contains('text-warning')) {
                c.font = { size: 10, color: { argb: 'FFCC8800' } }; // Orange/warning
              } else if (cell.querySelector('.text-info') || cell.classList.contains('text-info')) {
                c.font = { size: 10, color: { argb: 'FF17A2B8' } }; // Blue/info
              } else if (cell.querySelector('.text-success') || cell.classList.contains('text-success')) {
                c.font = { size: 10, color: { argb: 'FF28A745' } }; // Green/success
              }
            }
          }
        }

        colIndex += colspan;
      });

      excelRow.commit();
      excelRowIndex++;
    });

    // Set row heights
    worksheet.eachRow((row, rowNumber) => {
      row.height = 22;
    });

    // Freeze first row (header)
    worksheet.views = [{ state: 'frozen', ySplit: 1 }];

    // Generate filename with date
    const today = new Date();
    const dateStr = today.toISOString().slice(0, 10);
    const filename = `Rekapan_Pembayaran_${dateStr}.xlsx`;

    // Generate and download file
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    saveAs(blob, filename);

    btn.innerHTML = originalText;
    btn.disabled = false;

  } catch (error) {
    console.error('Excel Export Error:', error);
    btn.innerHTML = originalText;
    btn.disabled = false;
    alert('Gagal membuat Excel. Silakan coba lagi.');
  }
}

// Export to PDF Function
function exportToPDF() {
  const tableContainer = document.querySelector('.table-container');
  if (!tableContainer) {
    alert('Tabel tidak ditemukan!');
    return;
  }

  // Show loading
  const btn = document.querySelector('.btn-export-pdf');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  // Clone container for PDF
  const clone = tableContainer.cloneNode(true);

  // Remove export buttons from clone
  const exportBtns = clone.querySelector('.export-buttons');
  if (exportBtns) exportBtns.remove();

  // Remove separator rows for cleaner PDF
  const separatorRows = clone.querySelectorAll('.separator-row');
  separatorRows.forEach(row => row.remove());

  // PDF options
  const opt = {
    margin: [10, 10, 10, 10],
    filename: `Rekapan_Pembayaran_${new Date().toISOString().slice(0, 10)}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: {
      scale: 2,
      useCORS: true,
      logging: false
    },
    jsPDF: {
      unit: 'mm',
      format: 'a4',
      orientation: 'landscape'
    },
    pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
  };

  // Generate PDF
  html2pdf().set(opt).from(clone).save().then(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
  }).catch(err => {
    console.error('PDF Error:', err);
    btn.innerHTML = originalText;
    btn.disabled = false;
    alert('Gagal membuat PDF. Silakan coba lagi.');
  });
}

document.addEventListener('DOMContentLoaded', function() {
  const enableRekapan = document.getElementById('enableRekapanTable');
  const columnCheckboxes = document.getElementById('columnCheckboxes');
  const columnsGrid = document.getElementById('columnsGrid');
  const selectedPreview = document.getElementById('selectedPreview');
  const selectedColumnsList = document.getElementById('selectedColumnsList');
  const filterForm = document.getElementById('filterForm');

  // Track selection order
  let selectionOrder = [];

  // Initialize from existing selected columns
  @if(count($selectedColumns) > 0)
    selectionOrder = @json($selectedColumns);
  @endif

  // Toggle column checkboxes visibility
  enableRekapan.addEventListener('change', function() {
    if (this.checked) {
      columnCheckboxes.classList.add('show');
    } else {
      columnCheckboxes.classList.remove('show');
      // Clear all checkboxes when disabled
      document.querySelectorAll('.column-checkbox-item input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        cb.closest('.column-checkbox-item').classList.remove('selected');
      });
      selectionOrder = [];
      updatePreview();
      // Auto-submit when disabling rekapan table
      autoSubmitForm();
    }
  });

  // Handle column checkbox clicks
  columnsGrid.addEventListener('click', function(e) {
    const item = e.target.closest('.column-checkbox-item');
    if (!item) return;

    const checkbox = item.querySelector('input[type="checkbox"]');
    const columnKey = item.dataset.column;

    // Toggle checkbox if click wasn't directly on it
    if (e.target !== checkbox) {
      checkbox.checked = !checkbox.checked;
    }

    if (checkbox.checked) {
      // Add to selection order if not already there
      if (!selectionOrder.includes(columnKey)) {
        selectionOrder.push(columnKey);
      }
      item.classList.add('selected');
    } else {
      // Remove from selection order
      selectionOrder = selectionOrder.filter(key => key !== columnKey);
      item.classList.remove('selected');
    }

    updateOrderBadges();
    updatePreview();
    
    // Auto-submit form when checkbox is clicked
    autoSubmitForm();
  });
  
  // Function to auto-submit form
  function autoSubmitForm() {
    // Prepare form data
    prepareFormData();
    
    // Submit form automatically
    filterForm.submit();
  }
  
  // Function to prepare form data before submit
  function prepareFormData() {
    // Remove existing column inputs
    document.querySelectorAll('input[name="columns[]"]').forEach(input => {
      if (input.type === 'hidden') {
        input.remove();
      }
    });

    // Uncheck all checkboxes first (remove name attribute)
    document.querySelectorAll('.column-checkbox-item input[type="checkbox"]').forEach(cb => {
      cb.removeAttribute('name');
    });

    // Add hidden inputs in correct order
    selectionOrder.forEach(key => {
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'columns[]';
      hiddenInput.value = key;
      filterForm.appendChild(hiddenInput);
    });
    
    // Ensure rekapan table mode is enabled if columns are selected
    if (selectionOrder.length > 0) {
      enableRekapan.checked = true;
      columnCheckboxes.classList.add('show');
    }
  }

  function updateOrderBadges() {
    document.querySelectorAll('.column-checkbox-item').forEach(item => {
      const columnKey = item.dataset.column;
      const badge = item.querySelector('.order-badge');
      const index = selectionOrder.indexOf(columnKey);

      if (index !== -1) {
        badge.textContent = index + 1;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }
    });
  }

  function updatePreview() {
    if (selectionOrder.length > 0) {
      selectedPreview.classList.add('show');
      const labels = selectionOrder.map(key => {
        const item = document.querySelector(`.column-checkbox-item[data-column="${key}"] label`);
        return item ? item.textContent : key;
      });
      selectedColumnsList.textContent = labels.join(' → ');
    } else {
      selectedPreview.classList.remove('show');
      selectedColumnsList.textContent = '';
    }
  }

  // Before form submit, reorder hidden inputs to match selection order
  filterForm.addEventListener('submit', function(e) {
    prepareFormData();
  });

  // Initialize badges on page load
  updateOrderBadges();
});

// Export Normal Table to Excel
async function exportNormalTableToExcel() {
  const btn = event?.target?.closest('.btn-export-excel') || document.querySelector('.table-container:not([id*="rekapan"]) .btn-export-excel');
  if (!btn) return;
  
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  try {
    // Get all filter parameters
    const params = new URLSearchParams();
    params.append('status_pembayaran', '{{ $selectedStatus ?? "" }}');
    params.append('year', '{{ $selectedYear ?? "" }}');
    params.append('month', '{{ $selectedMonth ?? "" }}');
    params.append('search', '{{ $search ?? "" }}');
    params.append('export', 'excel');
    params.append('mode', 'normal');

    // Redirect to export route
    window.location.href = '{{ route("reports.pembayaran.export") }}?' + params.toString();
  } catch (error) {
    console.error('Export error:', error);
    alert('Terjadi kesalahan saat export: ' + error.message);
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}

// Export Normal Table to PDF
function exportNormalTableToPDF() {
  const btn = event?.target?.closest('.btn-export-pdf') || document.querySelector('.table-container:not([id*="rekapan"]) .btn-export-pdf');
  if (!btn) return;
  
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  try {
    // Get all filter parameters
    const params = new URLSearchParams();
    params.append('status_pembayaran', '{{ $selectedStatus ?? "" }}');
    params.append('year', '{{ $selectedYear ?? "" }}');
    params.append('month', '{{ $selectedMonth ?? "" }}');
    params.append('search', '{{ $search ?? "" }}');
    params.append('export', 'pdf');
    params.append('mode', 'normal');

    // Redirect to export route
    window.location.href = '{{ route("reports.pembayaran.export") }}?' + params.toString();
  } catch (error) {
    console.error('Export error:', error);
    alert('Terjadi kesalahan saat export: ' + error.message);
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}

// Export Rekapan Table to Excel
async function exportRekapanTableToExcel() {
  const btn = event?.target?.closest('.btn-export-excel') || document.querySelector('#rekapanTable').closest('.table-container').querySelector('.btn-export-excel');
  if (!btn) return;
  
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  try {
    // Get all filter parameters
    const params = new URLSearchParams();
    params.append('status_pembayaran', '{{ $selectedStatus ?? "" }}');
    params.append('year', '{{ $selectedYear ?? "" }}');
    params.append('month', '{{ $selectedMonth ?? "" }}');
    params.append('search', '{{ $search ?? "" }}');
    params.append('export', 'excel');
    params.append('mode', 'rekapan_table');
    
    // Add selected columns
    @if(!empty($selectedColumns))
      @foreach($selectedColumns as $col)
        params.append('columns[]', '{{ $col }}');
      @endforeach
    @endif

    // Redirect to export route
    window.location.href = '{{ route("reports.pembayaran.export") }}?' + params.toString();
  } catch (error) {
    console.error('Export error:', error);
    alert('Terjadi kesalahan saat export: ' + error.message);
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}

// Export Rekapan Table to PDF
function exportRekapanTableToPDF() {
  const btn = event?.target?.closest('.btn-export-pdf') || document.querySelector('#rekapanTable').closest('.table-container').querySelector('.btn-export-pdf');
  if (!btn) return;
  
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  try {
    // Get all filter parameters
    const params = new URLSearchParams();
    params.append('status_pembayaran', '{{ $selectedStatus ?? "" }}');
    params.append('year', '{{ $selectedYear ?? "" }}');
    params.append('month', '{{ $selectedMonth ?? "" }}');
    params.append('search', '{{ $search ?? "" }}');
    params.append('export', 'pdf');
    params.append('mode', 'rekapan_table');
    
    // Add selected columns
    @if(!empty($selectedColumns))
      @foreach($selectedColumns as $col)
        params.append('columns[]', '{{ $col }}');
      @endforeach
    @endif

    // Redirect to export route
    window.location.href = '{{ route("reports.pembayaran.export") }}?' + params.toString();
  } catch (error) {
    console.error('Export error:', error);
    alert('Terjadi kesalahan saat export: ' + error.message);
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}
</script>

@endsection



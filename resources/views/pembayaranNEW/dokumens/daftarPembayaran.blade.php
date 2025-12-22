@extends('layouts/app')
@section('content')

<style>
 h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 20px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .search-filter-container {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .search-input-wrapper {
    flex: 1;
    min-width: 250px;
  }

  .filter-wrapper {
    flex-shrink: 0;
  }

  .customize-button-wrapper {
    flex-shrink: 0;
  }

  .search-box .input-group {
    max-width: 100%;
  }

  .search-box .input-group-text {
    background: white;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-right: none;
    border-radius: 10px 0 0 10px;
    padding: 10px 14px;
  }

  .search-box .form-control {
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-left: none;
    border-radius: 0 10px 10px 0;
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  /* Filter Dropdown */
  .filter-dropdown {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
  }

  .filter-label {
    font-weight: 600;
    color: #083E40;
    font-size: 13px;
    margin: 0;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .dropdown-filter-modern {
    position: relative;
    min-width: 180px;
    flex-shrink: 0;
  }

  .dropdown-toggle-modern {
    width: 100%;
    padding: 10px 16px;
    padding-right: 40px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    font-size: 13px;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.05);
  }

  .dropdown-toggle-modern:hover {
    border-color: #889717;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.1);
    transform: translateY(-1px);
  }

  .dropdown-toggle-modern:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .dropdown-toggle-modern::after {
    content: '\f078';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    transition: all 0.3s ease;
    font-size: 12px;
    color: #083E40;
  }

  .dropdown-toggle-modern.show::after {
    transform: translateY(-50%) rotate(180deg);
  }

  .dropdown-menu-modern {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.15);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 8px;
    overflow: hidden;
  }

  .dropdown-menu-modern.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .dropdown-item-modern {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #083E40;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
    cursor: pointer;
  }

  .dropdown-item-modern:last-child {
    border-bottom: none;
  }

  .dropdown-item-modern:hover {
    background: rgba(136, 151, 23, 0.1);
    color: #083E40;
    padding-left: 16px;
  }
  
  .dropdown-item-modern.active:hover {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .dropdown-item-modern.active {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    font-weight: 600;
  }

  .dropdown-item-modern i {
    width: 16px;
    text-align: center;
    font-size: 12px;
  }

  /* Column Customization Button */
  .btn-customize-columns-inline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    font-size: 13px;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.05);
  }

  .btn-customize-columns-inline:hover {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(136, 151, 23, 0.04) 100%);
    color: #083E40;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.1);
  }

  .btn-customize-columns-inline:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-customize-columns-inline:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.1);
  }

  @media (max-width: 992px) {
    .search-filter-container {
      flex-direction: column;
      align-items: stretch;
    }

    .search-input-wrapper,
    .filter-wrapper,
    .customize-button-wrapper {
      width: 100%;
    }

    .filter-dropdown {
      width: 100%;
      flex-direction: row;
      justify-content: space-between;
    }

    .dropdown-filter-modern {
      flex: 1;
      min-width: 0;
    }

    .btn-customize-columns-inline {
      width: 100%;
      justify-content: center;
    }
  }

  @media (max-width: 768px) {
    .filter-dropdown {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }

    .filter-label {
      text-align: left;
    }

    .dropdown-filter-modern {
      min-width: 100%;
    }
  }

  /* Table Container */
  .table-dokumen {
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

  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1400px;
    width: 100%;
  }

  .table-enhanced thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    white-space: nowrap;
  }

  .table-enhanced tbody tr {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    background: white;
  }

  .table-enhanced tbody tr:hover {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.02) 0%, rgba(136, 151, 23, 0.02) 100%);
  }

  .table-enhanced tbody tr.clickable-row {
    cursor: pointer;
  }

  .table-enhanced tbody tr.clickable-row:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(136, 151, 23, 0.04) 100%);
    border-left-color: #889717;
  }
  
  .table-enhanced tbody tr.no-click-row {
    cursor: default !important;
  }
  
  .table-enhanced tbody tr.no-click-row:hover {
    background-color: transparent !important;
    border-left-color: transparent !important;
  }

  .table-enhanced tbody tr.non-clickable-row {
    opacity: 0.7;
    cursor: not-allowed;
  }

  .table-enhanced tbody tr.locked-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #eef3f3 100%);
    border-left-color: #ffc107;
    position: relative;
  }

  .table-enhanced tbody tr.locked-row::before {
    content: '🔒';
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    opacity: 0.6;
  }

  .table-enhanced tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(8, 62, 64, 0.08);
    vertical-align: middle;
  }

  .table-enhanced tbody tr:last-child td {
    border-bottom: none;
  }

  .table-enhanced tbody tr.selected {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(136, 151, 23, 0.04) 100%);
    border-left-color: #889717;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .btn-action {
    padding: 6px 12px;
    border: 1px solid rgba(8, 62, 64, 0.2);
    background: white;
    color: #083E40;
    font-size: 11px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }

  .btn-action:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, rgba(136, 151, 23, 0.05) 100%);
    border-color: #889717;
    color: #083E40;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.2);
  }

  .btn-action:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .btn-action i {
    font-size: 12px;
    width: 12px;
    text-align: center;
  }

  /* Status Buttons */
  .status-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .status-button {
    padding: 6px 12px;
    border: 1px solid rgba(8, 62, 64, 0.2);
    background: white;
    color: #083E40;
    font-size: 11px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }

  .status-button:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, rgba(136, 151, 23, 0.05) 100%);
    border-color: #889717;
    color: #083E40;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.2);
  }

  .status-button:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .status-button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
  }

  .status-button.active:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.4);
  }

  .status-button i {
    font-size: 12px;
    width: 12px;
    text-align: center;
  }

  .modal-content {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(8, 62, 64, 0.3);
    border: none;
    max-width: 500px;
    width: 90%;
    margin: 0 auto;
  }

  .modal-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    padding: 20px;
    border-radius: 16px 16px 0 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
  }

  .modal-body {
    padding: 30px;
  }

  .modal-footer {
    padding: 20px;
    background: #f8faf8;
    border-radius: 0 0 16px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #083E40;
  }

  .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    resize: vertical;
  }

  .form-group textarea:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-primary {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-secondary {
    background: white;
    color: #083E40;
    border: 2px solid rgba(8, 62, 64, 0.2);
  }

  .btn-secondary:hover {
    background: #f8faf8;
    border-color: #889717;
  }

  /* Status Badge */
  .status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
  }

  .status-badge.belum-diproses {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: white;
  }

  .status-badge.sedang-diproses {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
  }

  .status-badge.selesai {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .status-badge.siap-dibayar {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .status-badge.sudah-dibayar {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
    color: white;
  }

  @media (max-width: 768px) {
    .search-box {
      padding: 15px;
    }

    .table-dokumen {
      padding: 20px;
    }

    .table-enhanced {
      min-width: 1200px;
    }

    .action-buttons {
      flex-direction: column;
      align-items: stretch;
    }

    .modal-content {
      width: 95%;
    }
  }

  /* Pagination Styles */
  .btn-pagination {
    transition: all 0.3s ease;
  }

  .btn-pagination:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-pagination.active {
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
  }

  #perPageSelect:hover {
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  #perPageSelect:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  @media (max-width: 768px) {
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
  }
</style>

<h2>{{ $title }}</h2>

<!-- Search Box -->
<div class="search-box">
  <div class="search-filter-container">
    <div class="search-input-wrapper">
      <div class="input-group" id="searchInputGroup">
        <span class="input-group-text" style="cursor: pointer;" onclick="performSearch()">
          <i class="fa-solid fa-magnifying-glass text-muted"></i>
        </span>
        <input type="text" id="pembayaranSearchInput" class="form-control" placeholder="Cari dokumen pembayaran..." value="{{ $search ?? '' }}">
        <button class="input-group-text" type="button" id="clearSearchBtn" onclick="clearSearch()" style="cursor: pointer; border-left: none; background: white; border: 2px solid rgba(8, 62, 64, 0.1); border-left: none; display: {{ ($search ?? null) ? 'block' : 'none' }};">
          <i class="fa-solid fa-times text-muted"></i>
        </button>
      </div>
    </div>
    <div class="filter-wrapper">
      <div class="filter-dropdown">
        <label class="filter-label">
          <i class="fa-solid fa-filter me-2"></i>Filter Status:
        </label>
        <div class="dropdown-filter-modern">
          <button class="dropdown-toggle-modern" type="button" id="statusFilterDropdown">
            <i class="fa-solid fa-filter-circle-dot"></i>
            <span id="filterText">Semua Dokumen</span>
          </button>
          <div class="dropdown-menu-modern" id="statusFilterMenu">
            <a href="#" class="dropdown-item-modern {{ !$statusFilter ? 'active' : '' }}" data-filter="">
              <i class="fa-solid fa-list"></i>
              <span>Semua Dokumen</span>
            </a>
            <a href="#" class="dropdown-item-modern {{ $statusFilter === 'belum_siap_bayar' ? 'active' : '' }}" data-filter="belum_siap_bayar">
              <i class="fa-solid fa-clock"></i>
              <span>Belum Siap Bayar</span>
            </a>
            <a href="#" class="dropdown-item-modern {{ $statusFilter === 'siap_bayar' ? 'active' : '' }}" data-filter="siap_bayar">
              <i class="fa-solid fa-check-circle"></i>
              <span>Siap Bayar</span>
            </a>
            <a href="#" class="dropdown-item-modern {{ $statusFilter === 'sudah_dibayar' ? 'active' : '' }}" data-filter="sudah_dibayar">
              <i class="fa-solid fa-check-double"></i>
              <span>Sudah Dibayar</span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="customize-button-wrapper">
      <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
        <i class="fa-solid fa-table-columns me-2"></i>Kustomisasi Kolom Tabel
      </button>
    </div>
  </div>
</div>

<!-- Tabel Dokumen -->
<div class="table-dokumen">
  <div class="table-responsive">
    <table class="table table-enhanced mb-0">
      <thead>
        <tr>
          <th class="col-no sticky-column">No</th>
          @foreach($selectedColumns as $col)
            @if($col !== 'status')
              <th class="col-{{ $col }} sticky-column">{{ $availableColumns[$col] ?? $col }}</th>
            @endif
          @endforeach
          <th class="col-status sticky-column">Status</th>
          <th class="col-action sticky-column">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($dokumens as $index => $dokumen)
        @php
          // Tentukan status pembayaran untuk dokumen ini
          $paymentStatus = $dokumen->computed_status ?? 'belum_siap_bayar';
          // Normalize status jika menggunakan format lama
          if (is_string($paymentStatus)) {
            $statusUpper = strtoupper(trim($paymentStatus));
            if ($statusUpper === 'SIAP_DIBAYAR' || $statusUpper === 'SIAP DIBAYAR') {
              $paymentStatus = 'siap_bayar';
            } elseif ($statusUpper === 'SUDAH_DIBAYAR' || $statusUpper === 'SUDAH DIBAYAR') {
              $paymentStatus = 'sudah_dibayar';
            } elseif ($statusUpper === 'BELUM_SIAP_DIBAYAR' || $statusUpper === 'BELUM SIAP DIBAYAR') {
              $paymentStatus = 'belum_siap_bayar';
            }
          }
        @endphp
        <tr 
          {{-- Removed row onclick for siap_bayar status because openDocumentDetailModal is not defined
               and was causing JavaScript errors that prevented Edit button from working.
               For siap_bayar: users should only click Edit button, not the row itself --}}
          @if($paymentStatus === 'belum_siap_bayar')
            {{-- Belum siap bayar: row is not clickable, only eye icon for tracking --}}
            style="cursor: default;"
            class="no-click-row"
            title="Dokumen belum siap bayar. Klik icon mata untuk melihat tracking."
          @elseif($paymentStatus === 'siap_bayar')
            {{-- Siap bayar: row is not clickable, only Edit button is clickable --}}
            style="cursor: default;"
            class="no-click-row"
            title="Klik tombol Edit untuk input data pembayaran."
          @elseif($paymentStatus === 'sudah_dibayar')
            {{-- Sudah dibayar: row is not clickable anymore --}}
            style="cursor: default;"
            class="no-click-row"
            title="Dokumen sudah dibayar."
          @endif
          data-dokumen-id="{{ $dokumen->id }}"
        >
          <td class="col-no">{{ $dokumens->firstItem() + $index }}</td>
          @foreach($selectedColumns as $col)
            @if($col !== 'status')
            <td class="col-{{ $col }}">
              @if($col == 'nomor_agenda')
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br>
                <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              @elseif($col == 'tanggal_masuk')
                {{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}
              @elseif($col == 'nomor_spp')
                {{ $dokumen->nomor_spp }}
              @elseif($col == 'uraian_spp')
                {{ Str::limit($dokumen->uraian_spp ?? '-', 60) }}
              @elseif($col == 'nilai_rupiah')
                <strong>{{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</strong>
              @elseif($col == 'tanggal_spp')
                {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}
              @elseif($col == 'status_pembayaran')
                @switch($paymentStatus)
                  @case('siap_bayar')
                    <span class="status-badge siap-dibayar">Siap Bayar</span>
                    @break
                  @case('sudah_dibayar')
                    <span class="status-badge sudah-dibayar">Sudah Dibayar</span>
                    @break
                  @default
                    <span class="status-badge belum-diproses">Belum Siap Bayar</span>
                    @break
                @endswitch
              @else
                {{ $dokumen->$col ?? '-' }}
              @endif
            </td>
            @endif
          @endforeach
          <td class="col-status">
            @if(!in_array('status_pembayaran', $selectedColumns))
              @switch($paymentStatus)
                @case('siap_bayar')
                  <span class="status-badge siap-dibayar">Siap Bayar</span>
                  @break
                @case('sudah_dibayar')
                  <span class="status-badge sudah-dibayar">Sudah Dibayar</span>
                  @break
                @default
                  <span class="status-badge belum-diproses">Belum Siap Bayar</span>
                  @break
              @endswitch
            @endif
          </td>
          <td class="col-action" onclick="event.stopPropagation();">
            <div class="action-buttons">
              @if($paymentStatus === 'belum_siap_bayar')
                {{-- Kondisi A: Status = "Belum Siap Bayar" - Tampilkan icon mata untuk tracking --}}
                <a href="{{ route('owner.workflow', $dokumen->id) }}" 
                   target="_blank"
                   class="btn-action workflow-link"
                   title="Lihat Tracking Workflow Dokumen"
                   data-workflow-id="{{ $dokumen->id }}"
                   onclick="event.stopPropagation();">
                  <i class="fas fa-eye"></i>
                </a>
              @elseif($paymentStatus === 'siap_bayar')
                {{-- Kondisi B: Status = "Siap Bayar" - Tampilkan button edit --}}
              @php
                // Cek apakah kedua field sudah diisi
                $isComplete = !empty($dokumen->tanggal_dibayar) && !empty($dokumen->link_bukti_pembayaran);
              @endphp
              @if($isComplete)
                <button type="button" class="btn-action" disabled style="opacity: 0.6; cursor: not-allowed;">
                  <i class="fa-solid fa-check-circle"></i>
                  Selesai
                </button>
              @else
                  <button type="button" class="btn-action" onclick="event.stopPropagation(); event.preventDefault(); openEditPembayaranModalHandler({{ $dokumen->id }});">
                  <i class="fa-solid fa-edit"></i>
                  Edit
                  </button>
                @endif
              @elseif($paymentStatus === 'sudah_dibayar')
                {{-- Status = "Sudah Dibayar" - Tampilkan badge selesai --}}
                <button type="button" class="btn-action" disabled style="opacity: 0.6; cursor: not-allowed;">
                  <i class="fa-solid fa-check-circle"></i>
                  Selesai
                </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="{{ count($selectedColumns) + 3 }}" class="text-center text-muted py-5">
            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
            <h5>Belum ada dokumen pembayaran</h5>
            <p class="mb-0">Dokumen yang telah dikirim ke pembayaran akan muncul di sini.</p>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Pagination Controls -->
@if($dokumens->total() > 0)
<div class="pagination-wrapper" style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 16px; box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1); border: 1px solid rgba(8, 62, 64, 0.08);">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <!-- Info dan Per Page Selector -->
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
      <div class="text-muted" style="font-size: 13px; color: #083E40;">
        Menampilkan <strong>{{ $dokumens->firstItem() ?: 0 }}</strong> - <strong>{{ $dokumens->lastItem() ?: 0 }}</strong> dari total <strong>{{ $dokumens->total() }}</strong> dokumen
      </div>
      
      <!-- Per Page Selector -->
      <div style="display: flex; align-items: center; gap: 8px;">
        <label for="perPageSelect" style="font-size: 13px; color: #083E40; font-weight: 500; margin: 0;">Tampilkan per halaman:</label>
        <select id="perPageSelect" onchange="changePerPage(this.value)" style="padding: 6px 12px; border: 2px solid rgba(8, 62, 64, 0.15); border-radius: 8px; background: white; color: #083E40; font-size: 13px; font-weight: 500; cursor: pointer;">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
      </div>
    </div>

    <!-- Pagination Buttons -->
    @if($dokumens->hasPages())
    <div class="pagination" style="display: flex; gap: 8px; align-items: center;">
      {{-- Previous Page Link --}}
      @if($dokumens->onFirstPage())
        <button class="btn-pagination" disabled style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: #e0e0e0; color: #9e9e9e; border-radius: 10px; cursor: not-allowed;">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
      @else
        <a href="{{ $dokumens->appends(request()->query())->previousPageUrl() }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
        </a>
      @endif

      {{-- Pagination Elements --}}
      @php
        $currentPage = $dokumens->currentPage();
        $lastPage = $dokumens->lastPage();
        $startPage = max(1, $currentPage - 2);
        $endPage = min($lastPage, $currentPage + 2);
      @endphp

      {{-- First page --}}
      @if($startPage > 1)
        <a href="{{ $dokumens->appends(request()->query())->url(1) }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background-color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">1</button>
        </a>
        @if($startPage > 2)
          <button disabled style="padding: 10px 16px; border: none; background: transparent; color: #999; cursor: default;">...</button>
        @endif
      @endif

      {{-- Range of pages --}}
      @for($i = $startPage; $i <= $endPage; $i++)
        @if($currentPage == $i)
          <button class="btn-pagination active" style="padding: 10px 16px; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600;">{{ $i }}</button>
        @else
          <a href="{{ $dokumens->appends(request()->query())->url($i) }}">
            <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background-color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">{{ $i }}</button>
          </a>
        @endif
      @endfor

      {{-- Dots --}}
      @if($endPage < $lastPage)
        @if($endPage < $lastPage - 1)
          <button disabled style="padding: 10px 16px; border: none; background: transparent; color: #999; cursor: default;">...</button>
        @endif
        <a href="{{ $dokumens->appends(request()->query())->url($lastPage) }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background-color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">{{ $lastPage }}</button>
        </a>
      @endif

      {{-- Next Page Link --}}
      @if($dokumens->hasMorePages())
        <a href="{{ $dokumens->appends(request()->query())->nextPageUrl() }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </a>
      @else
        <button class="btn-pagination" disabled style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: #e0e0e0; color: #9e9e9e; border-radius: 10px; cursor: not-allowed;">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      @endif
    </div>
    @endif
  </div>
</div>
@endif

<!-- Modal: Edit Pembayaran -->
<div class="modal fade" id="editPembayaranModal" tabindex="-1" aria-labelledby="editPembayaranModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-radius: 12px 12px 0 0; border-bottom: none;">
        <h5 class="modal-title" id="editPembayaranModalLabel" style="color: white; font-weight: 700;">
          <i class="fa-solid fa-edit me-2"></i>Input Data Pembayaran
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 24px;">
        <form id="editPembayaranForm">
          <input type="hidden" id="editPembayaranDocId" name="dokumen_id" value="">
          
          <div class="form-group mb-4">
            <label for="tanggal_dibayar" class="form-label" style="font-weight: 600; color: #083E40; margin-bottom: 8px;">
              <i class="fa-solid fa-calendar-days me-2"></i>Tanggal Pembayaran
            </label>
            <input type="date" name="tanggal_dibayar" id="tanggal_dibayar" class="form-control" value="" style="border: 2px solid #e9ecef; border-radius: 8px; padding: 10px; font-size: 14px;">
            <small class="text-muted" style="font-size: 12px; margin-top: 4px; display: block;">
              <i class="fa-solid fa-info-circle me-1"></i>Pilih tanggal ketika pembayaran dilakukan
            </small>
          </div>
          
          <div class="form-group mb-4">
            <label for="link_bukti_pembayaran" class="form-label" style="font-weight: 600; color: #083E40; margin-bottom: 8px;">
              <i class="fa-brands fa-google-drive me-2"></i>Link Google Drive Bukti Pembayaran
            </label>
            <input type="url" name="link_bukti_pembayaran" id="link_bukti_pembayaran" class="form-control" placeholder="https://drive.google.com/file/d/..." value="" style="border: 2px solid #e9ecef; border-radius: 8px; padding: 10px; font-size: 14px;">
            <small class="text-muted" style="font-size: 12px; margin-top: 4px; display: block;">
              <i class="fa-solid fa-info-circle me-1"></i>Masukkan link Google Drive untuk bukti pembayaran (PDF/File)
            </small>
          </div>
          
          <div class="alert alert-info" style="background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 8px; padding: 12px; margin-bottom: 0;">
            <i class="fa-solid fa-info-circle me-2" style="color: #0d6efd;"></i>
            <strong style="color: #0d6efd;">Catatan:</strong> 
            <span style="color: #0d6efd;">Minimal salah satu field harus diisi. Status akan otomatis berubah menjadi "Sudah Dibayar" setelah salah satu field diisi.</span>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 20px;">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-primary" onclick="submitEditPembayaran()" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none; border-radius: 8px; padding: 8px 20px;">
          <i class="fa-solid fa-save me-2"></i>Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Column Customization -->
<div class="customization-modal" id="columnCustomizationModal">
  <div class="modal-content-custom">
    <div class="modal-header-custom">
      <h3>
        <i class="fa-solid fa-table-columns"></i>
        Kustomisasi Kolom Tabel
      </h3>
    </div>

    <div class="modal-body-custom">
      <div class="customization-grid">
        <!-- Selection Panel -->
        <div class="selection-panel">
          <div class="panel-title">
            <i class="fa-solid fa-check-square"></i>
            Pilih Kolom
          </div>
          <div class="panel-description">
            Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
          </div>
          <div class="column-selection-list" id="columnSelectionList">
            @foreach($availableColumns as $key => $label)
              <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}"
                   data-column="{{ $key }}"
                   draggable="{{ in_array($key, $selectedColumns) ? 'true' : 'false' }}"
                   onclick="toggleColumn(this)">
                <div class="drag-handle">
                  <i class="fa-solid fa-grip-vertical"></i>
                </div>
                <input type="checkbox"
                       class="column-item-checkbox"
                       value="{{ $key }}"
                       {{ in_array($key, $selectedColumns) ? 'checked' : '' }}
                       onclick="event.stopPropagation()">
                <label class="column-item-label">{{ $label }}</label>
                <span class="column-item-order">
                  {{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}
                </span>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Preview Panel -->
        <div class="preview-panel">
          <div class="panel-title">
            <i class="fa-solid fa-eye"></i>
            Preview Hasil
          </div>
          <div class="panel-description">
            Preview tabel akan menampilkan kolom yang Anda pilih sesuai urutan.
          </div>
          <div class="preview-container">
            <div id="tablePreview">
              @if(count($selectedColumns) > 0)
                <table class="preview-table">
                  <thead>
                    <tr>
                      <th>No</th>
                      @foreach($selectedColumns as $col)
                        <th>{{ $availableColumns[$col] ?? $col }}</th>
                      @endforeach
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @for($i = 1; $i <= 5; $i++)
                      <tr>
                        <td>{{ $i }}</td>
                        @foreach($selectedColumns as $col)
                          <td>
                            @if($col == 'nomor_agenda')
                              AGD/{{ 100 + $i }}/XII/2024
                            @elseif($col == 'nomor_spp')
                              SPP-00{{ 100 + $i }}/XII/2024
                            @elseif($col == 'tanggal_masuk')
                              {{ date('d-m-Y', strtotime("+$i days")) }} 08:{{ str_pad($i * 10, 2, '0', STR_PAD_LEFT) }}
                            @elseif($col == 'nilai_rupiah')
                              Rp. {{ number_format(1000000 * $i, 0, ',', '.') }}
                            @elseif($col == 'nomor_mirror')
                              MIR-{{ 1000 + $i }}
                            @elseif($col == 'uraian_spp')
                              Contoh uraian SPP ke {{ $i }}
                            @elseif($col == 'dibayar_kepada')
                              CV. Contoh Vendor {{ $i }}
                            @elseif($col == 'tanggal_spp')
                              {{ date('d/m/Y', strtotime("+$i days")) }}
                            @elseif($col == 'kategori')
                              Kategori {{ $i }}
                            @else
                              Contoh Data {{ $i }}
                            @endif
                          </td>
                        @endforeach
                        <td>Edit, Kirim</td>
                      </tr>
                    @endfor
                  </tbody>
                </table>
              @else
                <div class="empty-preview">
                  <i class="fa-solid fa-table"></i>
                  <p>Belum ada kolom yang dipilih</p>
                  <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer-custom">
      <div class="selected-count">
        <strong id="selectedColumnCount">{{ count($selectedColumns) }}</strong> kolom dipilih
        @if(count($selectedColumns) > 0)
          <br><small>Kolom: {{ implode(', ', array_map(function($col) use ($availableColumns) {
            return $availableColumns[$col] ?? $col;
          }, $selectedColumns)) }}</small>
        @endif
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-modal btn-cancel" onclick="closeColumnCustomizationModal()">
          <i class="fa-solid fa-times"></i>
          Batal
        </button>
        <button type="button" class="btn-modal btn-save" id="saveCustomizationBtn" onclick="saveColumnCustomization()">
          <i class="fa-solid fa-save"></i>
          Simpan Perubahan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Column Customization CSS -->
<style>
.customization-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.customization-modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content-custom {
  background: white;
  border-radius: 20px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
  max-width: 1400px;
  width: 95%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideIn 0.3s ease;
}

.modal-header-custom {
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  padding: 24px 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.modal-header-custom h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #212529;
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-body-custom {
  padding: 24px 32px;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
  min-height: 0;
}

.customization-grid {
  display: flex;
  flex-direction: column;
  gap: 24px;
  flex: 1;
  min-height: 0;
}

.selection-panel {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e9ecef;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

.preview-panel {
  background: #ffffff;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e9ecef;
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
}

.panel-title {
  font-size: 18px;
  font-weight: 600;
  color: #212529;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.panel-description {
  font-size: 13px;
  color: #6c757d;
  margin-bottom: 16px;
  line-height: 1.6;
}

.column-selection-list {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  max-height: 200px;
  overflow-y: auto;
  padding: 8px;
  background: white;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

@media (max-width: 900px) {
  .column-selection-list {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 600px) {
  .column-selection-list {
    grid-template-columns: repeat(2, 1fr);
  }
}

.column-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  background: #ffffff;
  border-radius: 8px;
  border: 2px solid #e9ecef;
  cursor: move;
  transition: all 0.2s ease;
  position: relative;
  user-select: none;
  min-height: 44px;
  gap: 8px;
}

.column-item:hover {
  border-color: #889717;
  box-shadow: 0 4px 12px rgba(136, 151, 23, 0.15);
  transform: translateY(-2px);
}

.column-item.selected {
  border-color: #28a745;
  background: #f0f9f4;
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
}

.column-item.dragging {
  opacity: 0.6;
  transform: scale(0.98);
}

.drag-handle {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  cursor: grab;
  flex-shrink: 0;
  font-size: 12px;
}

.column-item.selected .drag-handle {
  color: #28a745;
}

.column-item:not(.selected) .drag-handle {
  opacity: 0.3;
  cursor: default;
}

.column-item-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  flex-shrink: 0;
}

.column-item-label {
  font-size: 14px;
  color: #212529;
  font-weight: 500;
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.column-item-order {
  width: 24px;
  height: 24px;
  background: #28a745;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
  opacity: 0;
  transform: scale(0);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.column-item.selected .column-item-order {
  opacity: 1;
  transform: scale(1);
}

.preview-container {
  flex: 1;
  overflow-x: auto;
  overflow-y: auto;
  background: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
  min-height: 400px;
  width: 100%;
}

.preview-table {
  width: 100%;
  min-width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  font-size: 13px;
  table-layout: auto;
}

.preview-table th {
  background: #212529;
  color: white;
  padding: 14px 12px;
  text-align: center;
  font-weight: 600;
  font-size: 12px;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}

.preview-table td {
  padding: 12px;
  text-align: center;
  border-right: 1px solid #e9ecef;
  color: #495057;
  font-size: 13px;
}

.empty-preview {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.modal-footer-custom {
  padding: 20px 40px;
  border-top: 1px solid #e9ecef;
  background: #ffffff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
  position: sticky;
  bottom: 0;
  z-index: 100;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

.selected-count {
  font-size: 15px;
  color: #495057;
  font-weight: 500;
}

.selected-count strong {
  color: #28a745;
  font-size: 18px;
}

.modal-actions {
  display: flex;
  gap: 12px;
}

.btn-modal {
  padding: 12px 32px;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 48px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-cancel {
  background: #6c757d;
  color: white;
}

.btn-cancel:hover {
  background: #5a6268;
  transform: translateY(-1px);
}

.btn-save {
  background: #28a745;
  color: white;
}

.btn-save:hover {
  background: #218838;
  transform: translateY(-1px);
}

.btn-save:disabled {
  background: #adb5bd;
  cursor: not-allowed;
  transform: none;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { transform: translateY(-30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@media (max-width: 768px) {
  .customization-grid {
    flex-direction: column;
  }

  .column-selection-list {
    grid-template-columns: repeat(2, 1fr);
  }

  .modal-footer-custom {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  .modal-actions {
    flex-direction: column;
  }
}

</style>

<script>
// Global variables for column customization
let selectedColumnsOrder = [];
let availableColumnsData = {};

// Initialize available columns data from PHP
@php
    $columnsJson = json_encode($availableColumns);
    echo "availableColumnsData = {$columnsJson};";
@endphp

// Initialize selected columns from existing selection
@if(count($selectedColumns) > 0)
  selectedColumnsOrder = @json($selectedColumns);
@endif

// Global Functions
function openColumnCustomizationModal() {
  const modal = document.getElementById('columnCustomizationModal');
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
  initializeModalState();
}

function closeColumnCustomizationModal() {
  const modal = document.getElementById('columnCustomizationModal');
  modal.classList.remove('show');
  document.body.style.overflow = '';
}

function toggleColumn(columnElement) {
  const columnKey = columnElement.dataset.column;
  const checkbox = columnElement.querySelector('.column-item-checkbox');
  const isChecked = checkbox.checked;

  if (!isChecked) {
    if (!selectedColumnsOrder.includes(columnKey)) {
      selectedColumnsOrder.push(columnKey);
    }
    checkbox.checked = true;
    columnElement.classList.add('selected');
    columnElement.setAttribute('draggable', 'true');
  } else {
    selectedColumnsOrder = selectedColumnsOrder.filter(key => key !== columnKey);
    checkbox.checked = false;
    columnElement.classList.remove('selected');
    columnElement.setAttribute('draggable', 'false');
  }

  updateColumnOrderBadges();
  updatePreviewTable();
  updateSelectedCount();
  updateDraggableState();
}

function updateColumnOrderBadges() {
  document.querySelectorAll('.column-item').forEach(item => {
    const columnKey = item.dataset.column;
    const orderBadge = item.querySelector('.column-item-order');
    const index = selectedColumnsOrder.indexOf(columnKey);

    if (index !== -1) {
      orderBadge.textContent = index + 1;
    } else {
      orderBadge.textContent = '';
    }
  });
}

function updatePreviewTable() {
  const previewContainer = document.getElementById('tablePreview');

  if (selectedColumnsOrder.length === 0) {
    previewContainer.innerHTML = `
      <div class="empty-preview">
        <i class="fa-solid fa-table fa-2x mb-2"></i>
        <p>Belum ada kolom yang dipilih</p>
        <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
      </div>
    `;
    return;
  }

  let previewHTML = `
    <table class="preview-table">
      <thead>
        <tr>
          <th>No</th>
  `;

  selectedColumnsOrder.forEach(columnKey => {
    const columnLabel = availableColumnsData[columnKey] || columnKey;
    previewHTML += `<th>${columnLabel}</th>`;
  });

  previewHTML += `
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
  `;

  const sampleData = {
    'nomor_agenda': ['AGD/822/XII/2024', 'AGD/258/XII/2024', 'AGD/992/XII/2024', 'AGD/92/XII/2024', 'AGD/546/XII/2024'],
    'nomor_spp': ['SPP-001/XII/2024', 'SPP-002/XII/2024', 'SPP-003/XII/2024', 'SPP-004/XII/2024', 'SPP-005/XII/2024'],
    'tanggal_masuk': ['24/11/2024 08:49', '24/11/2024 08:37', '24/11/2024 08:18', '24/11/2024 08:13', '24/11/2024 08:09'],
    'nilai_rupiah': ['Rp. 241.650.650', 'Rp. 751.897.501', 'Rp. 232.782.087', 'Rp. 490.050.679', 'Rp. 397.340.004'],
    'nomor_mirror': ['MIR-1001', 'MIR-1002', 'MIR-1003', 'MIR-1004', 'MIR-1005'],
    'uraian_spp': ['Uraian SPP 1', 'Uraian SPP 2', 'Uraian SPP 3', 'Uraian SPP 4', 'Uraian SPP 5'],
    'dibayar_kepada': ['CV. Vendor A', 'CV. Vendor B', 'CV. Vendor C', 'CV. Vendor D', 'CV. Vendor E'],
    'tanggal_spp': ['01/11/2024', '05/11/2024', '10/11/2024', '08/11/2024', '12/11/2024'],
    'kategori': ['Operasional', 'Investasi', 'Operasional', 'Investasi', 'Operasional'],
  };

  for (let i = 0; i < 5; i++) {
    previewHTML += `<tr>`;
    previewHTML += `<td>${i + 1}</td>`;

    selectedColumnsOrder.forEach(columnKey => {
      // Skip 'status' column as it's always shown as a special column
      if (columnKey === 'status') {
        return;
      }
      
      const columnLabel = availableColumnsData[columnKey] || columnKey;
      let cellValue = sampleData[columnKey] ? sampleData[columnKey][i] : `Contoh ${columnLabel} ${i + 1}`;
      
      previewHTML += `<td>${cellValue}</td>`;
    });

    previewHTML += `<td>Edit, Kirim</td>`;
    previewHTML += `</tr>`;
  }

  previewHTML += `
      </tbody>
    </table>
  `;

  previewContainer.innerHTML = previewHTML;
}

function updateSelectedCount() {
  const countElement = document.getElementById('selectedColumnCount');
  countElement.textContent = selectedColumnsOrder.length;

  const saveButton = document.getElementById('saveCustomizationBtn');
  saveButton.disabled = selectedColumnsOrder.length === 0;
}

function saveColumnCustomization() {
  if (selectedColumnsOrder.length === 0) {
    alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
    return;
  }

  const filterForm = document.querySelector('form[action*="dokumensPembayaran"]');
  if (!filterForm) {
    // Create a new form if not found
    const newForm = document.createElement('form');
    newForm.method = 'GET';
    newForm.action = '{{ route("documents.pembayaran.index") }}';
    document.body.appendChild(newForm);
    
    selectedColumnsOrder.forEach(columnKey => {
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'columns[]';
      hiddenInput.value = columnKey;
      newForm.appendChild(hiddenInput);
    });

    closeColumnCustomizationModal();
    newForm.submit();
    return;
  }

  document.querySelectorAll('input[name="columns[]"]').forEach(input => {
    if (input.type === 'hidden') {
      input.remove();
    }
  });

  selectedColumnsOrder.forEach(columnKey => {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'columns[]';
    hiddenInput.value = columnKey;
    filterForm.appendChild(hiddenInput);
  });

  closeColumnCustomizationModal();
  filterForm.submit();
}

function initializeModalState() {
  document.querySelectorAll('.column-item').forEach(item => {
    const columnKey = item.dataset.column;
    const checkbox = item.querySelector('.column-item-checkbox');

    if (selectedColumnsOrder.includes(columnKey)) {
      checkbox.checked = true;
      item.classList.add('selected');
      item.setAttribute('draggable', 'true');
    } else {
      checkbox.checked = false;
      item.classList.remove('selected');
      item.setAttribute('draggable', 'false');
    }
  });

  initializeDragAndDrop();
  updateColumnOrderBadges();
  updatePreviewTable();
  updateSelectedCount();
}

function updateDraggableState() {
  document.querySelectorAll('.column-item').forEach(item => {
    const columnKey = item.dataset.column;
    if (selectedColumnsOrder.includes(columnKey)) {
      item.setAttribute('draggable', 'true');
    } else {
      item.setAttribute('draggable', 'false');
    }
  });
}

let draggedElement = null;

function initializeDragAndDrop() {
  const columnList = document.getElementById('columnSelectionList');
  if (!columnList) return;

  const newList = columnList.cloneNode(true);
  columnList.parentNode.replaceChild(newList, columnList);

  newList.querySelectorAll('.column-item.selected').forEach(item => {
    item.addEventListener('dragstart', handleDragStart);
    item.addEventListener('dragend', handleDragEnd);
    item.addEventListener('dragover', handleDragOver);
    item.addEventListener('drop', handleDrop);
  });
}

function handleDragStart(e) {
  draggedElement = this;
  this.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
  this.classList.remove('dragging');
  document.querySelectorAll('.column-item').forEach(el => {
    el.classList.remove('drag-over');
  });
  draggedElement = null;
}

function handleDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  
  if (this !== draggedElement && this.classList.contains('selected')) {
    const afterElement = getDragAfterElement(this.parentNode, e.clientY);
    
    if (afterElement == null) {
      this.parentNode.appendChild(draggedElement);
    } else {
      this.parentNode.insertBefore(draggedElement, afterElement);
    }
  }
  
  return false;
}

function handleDrop(e) {
  e.preventDefault();
  e.stopPropagation();
  
  this.classList.remove('drag-over');
  
  if (this !== draggedElement && this.classList.contains('selected')) {
    const columnList = document.getElementById('columnSelectionList');
    const selectedItems = Array.from(columnList.querySelectorAll('.column-item.selected'));
    const newOrder = selectedItems.map(item => item.dataset.column);
    
    selectedColumnsOrder = newOrder;
    
    updateColumnOrderBadges();
    updatePreviewTable();
    
    setTimeout(() => {
      initializeDragAndDrop();
    }, 50);
  }
  
  return false;
}

function getDragAfterElement(container, y) {
  const draggableElements = [...container.querySelectorAll('.column-item.selected:not(.dragging)')];
  
  return draggableElements.reduce((closest, child) => {
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height / 2;
    
    if (offset < 0 && offset > closest.offset) {
      return { offset: offset, element: child };
    } else {
      return closest;
    }
  }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  const modal = document.getElementById('columnCustomizationModal');
  if (modal && modal.classList.contains('show') && e.target === modal) {
    closeColumnCustomizationModal();
  }
});
</script>

<!-- JavaScript -->
<script>
// Define global functions FIRST before any DOMContentLoaded handlers
// This ensures functions are available immediately when page loads

// Handler function untuk tombol edit
function openEditPembayaranModalHandler(docId) {
    console.log('openEditPembayaranModalHandler called with docId:', docId);
    if (typeof window.openEditPembayaranModal === 'function') {
        window.openEditPembayaranModal(docId);
    } else {
        console.error('openEditPembayaranModal function not found');
        alert('Fungsi tidak tersedia. Silakan refresh halaman.');
    }
}

window.openEditPembayaranModal = function(docId) {
    console.log('openEditPembayaranModal called with docId:', docId);
    
    // Set dokumen ID in hidden field
    const docIdField = document.getElementById('editPembayaranDocId');
    const tanggalField = document.getElementById('tanggal_dibayar');
    const linkField = document.getElementById('link_bukti_pembayaran');
    const modalElement = document.getElementById('editPembayaranModal');
    
    if (!docIdField || !tanggalField || !linkField || !modalElement) {
        console.error('Modal elements not found:', {
            docIdField: !!docIdField,
            tanggalField: !!tanggalField,
            linkField: !!linkField,
            modalElement: !!modalElement
        });
        alert('Terjadi kesalahan. Silakan muat ulang halaman.');
        return;
    }
    
    docIdField.value = docId;
    
    // Ambil data terbaru dari server untuk memastikan nilai tidak hilang
    fetch(`/dokumensPembayaran/${docId}/get-payment-data`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Payment data received:', data);
        if (data.success) {
            tanggalField.value = data.tanggal_dibayar || '';
            linkField.value = data.link_bukti_pembayaran || '';
        }
        
        // Use getOrCreateInstance for better compatibility
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
            console.log('Modal opened successfully');
        } else {
            console.error('Bootstrap Modal not available');
            // Fallback: show modal manually
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'editPembayaranModalBackdrop';
            document.body.appendChild(backdrop);
        }
    })
    .catch(error => {
        console.error('Error fetching payment data:', error);
        // Jika error, tetap buka modal dengan nilai kosong
        tanggalField.value = '';
        linkField.value = '';
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        } else {
            // Fallback: show modal manually
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'editPembayaranModalBackdrop';
            document.body.appendChild(backdrop);
        }
    });
};

window.submitEditPembayaran = function() {
    const docId = document.getElementById('editPembayaranDocId').value;
    if (!docId) {
        alert('Dokumen tidak ditemukan. Silakan muat ulang halaman.');
        return;
    }
    
    const form = document.getElementById('editPembayaranForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#editPembayaranModal .btn-primary');

    const tanggalDibayar = formData.get('tanggal_dibayar');
    const linkBukti = formData.get('link_bukti_pembayaran');

    // Validasi: minimal salah satu harus diisi
    if (!tanggalDibayar && !linkBukti) {
        alert('Minimal salah satu field (tanggal pembayaran atau link bukti) harus diisi.');
        return;
    }

    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';

    // Kirim semua field yang ada di form (termasuk yang kosong, untuk mempertahankan nilai yang sudah ada)
    const requestData = {};
    // Selalu kirim tanggal_dibayar jika ada (termasuk string kosong, akan di-handle di backend)
    if (tanggalDibayar) {
        requestData.tanggal_dibayar = tanggalDibayar;
    }
    // Selalu kirim link_bukti_pembayaran jika ada (termasuk string kosong, akan di-handle di backend)
    if (linkBukti) {
        requestData.link_bukti_pembayaran = linkBukti;
    }

    fetch(`/dokumensPembayaran/${docId}/update-pembayaran`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modalElement = document.getElementById('editPembayaranModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
            if (data.is_complete) {
                alert('Data pembayaran berhasil disimpan! Kedua field sudah lengkap, dokumen selesai.');
            } else {
                alert('Data pembayaran berhasil disimpan! Status otomatis berubah menjadi "Sudah Dibayar".');
            }
            location.reload();
        } else {
            alert(data.message || 'Gagal menyimpan data pembayaran.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data pembayaran.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.getElementById('statusFilterDropdown');
    const dropdownMenu = document.getElementById('statusFilterMenu');
    const filterText = document.getElementById('filterText');
    const dropdownItems = document.querySelectorAll('.dropdown-item-modern');
    const currentFilter = '{{ $statusFilter }}';

    // Set initial active state
    if (currentFilter) {
        updateActiveFilter(currentFilter);
    }

    // Toggle dropdown
    dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = dropdownMenu.classList.contains('show');

        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    // Handle dropdown item clicks
    dropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            const filter = this.getAttribute('data-filter');

            // Auto-navigate to filter URL while preserving search parameter
            const url = new URL(window.location);
            url.searchParams.set('status_filter', filter);
            url.searchParams.delete('page'); // Reset to page 1 when changing filter
            
            // Preserve search parameter if exists
            const searchInput = document.getElementById('pembayaranSearchInput');
            if (searchInput && searchInput.value.trim()) {
                url.searchParams.set('search', searchInput.value.trim());
            } else {
                url.searchParams.delete('search');
            }
            
            // Preserve per_page parameter
            const perPageSelect = document.getElementById('perPageSelect');
            if (perPageSelect && perPageSelect.value) {
                url.searchParams.set('per_page', perPageSelect.value);
            }
            
            window.location.href = url.toString();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            closeDropdown();
        }
    });

    function openDropdown() {
        dropdownMenu.classList.add('show');
        dropdownToggle.classList.add('show');
        dropdownToggle.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown() {
        dropdownMenu.classList.remove('show');
        dropdownToggle.classList.remove('show');
        dropdownToggle.setAttribute('aria-expanded', 'false');
    }

    function updateActiveFilter(filter) {
        // Update dropdown text
        const texts = {
            '': 'Semua Dokumen',
            'belum_siap_bayar': 'Belum Siap Bayar',
            'siap_bayar': 'Siap Bayar',
            'sudah_dibayar': 'Sudah Dibayar'
        };

        filterText.textContent = texts[filter] || 'Semua Dokumen';

        // Update active states
        dropdownItems.forEach(item => {
            const itemFilter = item.getAttribute('data-filter');
            if (itemFilter === filter) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
});

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('pembayaranSearchInput');
    let searchTimeout;

    if (searchInput) {
        // Handle Enter key press
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        // Handle input with debounce (auto-search after user stops typing for 800ms)
        searchInput.addEventListener('input', function() {
            // Show/hide clear button based on input value
            const clearBtn = document.getElementById('clearSearchBtn');
            if (clearBtn) {
                if (this.value.trim()) {
                    clearBtn.style.display = 'block';
                } else {
                    clearBtn.style.display = 'none';
                }
            }
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch();
            }, 800);
        });

        // Handle search icon click
        const searchIcon = searchInput.closest('.input-group')?.querySelector('.input-group-text');
        if (searchIcon) {
            searchIcon.style.cursor = 'pointer';
            searchIcon.addEventListener('click', function() {
                performSearch();
            });
        }
    }

    function performSearch() {
        const searchValue = searchInput.value.trim();
        const currentFilter = '{{ $statusFilter ?? "" }}';
        const currentPerPage = '{{ $perPage ?? 10 }}';
        
        // Build URL with current filter and search
        const url = new URL(window.location.pathname, window.location.origin);
        
        // Add search parameter if not empty
        if (searchValue) {
            url.searchParams.set('search', searchValue);
        } else {
            url.searchParams.delete('search');
        }
        
        // Reset to page 1 when searching
        url.searchParams.delete('page');
        
        // Preserve status filter
        if (currentFilter) {
            url.searchParams.set('status_filter', currentFilter);
        } else {
            url.searchParams.delete('status_filter');
        }
        
        // Preserve per page setting
        if (currentPerPage) {
            url.searchParams.set('per_page', currentPerPage);
        }
        
        // Navigate to new URL
        window.location.href = url.toString();
    }

    // Make performSearch available globally
    window.performSearch = performSearch;
});

// Clear search function
function clearSearch() {
    const searchInput = document.getElementById('pembayaranSearchInput');
    if (searchInput) {
        searchInput.value = '';
        const currentFilter = '{{ $statusFilter ?? "" }}';
        
        // Build URL without search parameter
        const url = new URL(window.location.pathname, window.location.origin);
        url.searchParams.delete('search');
        url.searchParams.delete('page'); // Reset to page 1 when clearing search
        
        // Preserve status filter
        if (currentFilter) {
            url.searchParams.set('status_filter', currentFilter);
        } else {
            url.searchParams.delete('status_filter');
        }
        
        // Navigate to new URL
        window.location.href = url.toString();
    }
}

// Change per page function
function changePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to page 1 when changing per page
    window.location.href = url.toString();
}

// Note: openEditPembayaranModal and submitEditPembayaran are already defined at the top of this script
// They are defined before DOMContentLoaded to ensure they're available immediately

// Ensure workflow tracking links work correctly
document.addEventListener('DOMContentLoaded', function() {
    // Handle workflow tracking links - use mousedown instead of click to prevent any interference
    document.querySelectorAll('.workflow-link').forEach(function(link) {
        // Use mousedown to capture event earlier
        link.addEventListener('mousedown', function(e) {
            e.stopPropagation();
            e.preventDefault();
        });
        
        // Also handle click as backup
        link.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const url = this.getAttribute('href');
            console.log('Workflow link clicked, URL:', url);
            if (url) {
                window.open(url, '_blank');
            }
            return false;
        }, true); // Use capture phase to intercept early
    });
    
    // Prevent row click when clicking on action buttons (for all rows)
    document.querySelectorAll('.clickable-row .action-buttons a, .clickable-row .action-buttons button').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent row click when clicking action buttons
        }, true); // Use capture phase
    });
});

// Global function to open document detail modal
window.openDocumentDetailModal = function(dokumenId, event) {
    // Prevent default navigation
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    console.log('openDocumentDetailModal called with dokumenId:', dokumenId);
    
    // Show loading state
    const loadingEl = document.getElementById('view-loading');
    const errorEl = document.getElementById('view-error');
    const contentEl = document.getElementById('view-content');
    
    if (loadingEl) loadingEl.style.display = 'block';
    if (errorEl) errorEl.style.display = 'none';
    if (contentEl) contentEl.style.display = 'none';
    
    // Fetch document detail
    fetch(`/dokumensPembayaran/${dokumenId}/detail`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Gagal memuat data dokumen. Status: ' + response.status);
            });
        }
        return response.json();
    })
    .then(result => {
        if (result.success && result.data) {
            populateDocumentDetail(result.data);
            if (loadingEl) loadingEl.style.display = 'none';
            if (contentEl) contentEl.style.display = 'block';
            
            // Show Bootstrap modal
            const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
            modal.show();
        } else {
            throw new Error(result.message || 'Data tidak ditemukan');
        }
    })
    .catch(error => {
        console.error('Error loading document:', error);
        if (loadingEl) loadingEl.style.display = 'none';
        if (errorEl) {
            errorEl.style.display = 'block';
            const errorText = errorEl.querySelector('span');
            if (errorText) errorText.textContent = error.message || 'Terjadi kesalahan saat memuat data dokumen';
        }
        
        // Show modal even with error
        const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
        modal.show();
    });
    
    return false;
};

function populateDocumentDetail(data) {
    // Helper functions
    const formatDate = (dateStr) => {
        if (!dateStr || dateStr === '-') return '-';
        if (dateStr.includes('/')) return dateStr; // Already formatted
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };
    
    const formatDateTime = (dateStr) => {
        if (!dateStr || dateStr === '-') return '-';
        if (dateStr.includes('/') && dateStr.includes(':')) return dateStr; // Already formatted
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    };
    
    const formatNumber = (num) => {
        if (!num || num === '-') return '-';
        return new Intl.NumberFormat('id-ID').format(num);
    };
    
    // Identitas Dokumen
    document.getElementById('view-nomor-agenda').textContent = data.nomor_agenda || '-';
    document.getElementById('view-nomor-spp').textContent = data.nomor_spp || '-';
    document.getElementById('view-tanggal-spp').textContent = formatDate(data.tanggal_spp);
    document.getElementById('view-bulan').textContent = data.bulan || '-';
    document.getElementById('view-tahun').textContent = data.tahun || '-';
    document.getElementById('view-tanggal-masuk').textContent = formatDateTime(data.tanggal_masuk);
    document.getElementById('view-kategori').textContent = data.kategori || '-';
    document.getElementById('view-jenis-dokumen').textContent = data.jenis_dokumen || '-';
    document.getElementById('view-jenis-sub-pekerjaan').textContent = data.jenis_sub_pekerjaan || '-';
    
    // Detail Keuangan & Vendor
    document.getElementById('view-uraian-spp').textContent = data.uraian_spp || '-';
    document.getElementById('view-nilai-rupiah').textContent = data.nilai_rupiah_formatted || (data.nilai_rupiah ? 'Rp. ' + formatNumber(data.nilai_rupiah) : '-');
    
    // Ejaan nilai rupiah
    if (data.nilai_rupiah && data.nilai_rupiah > 0) {
        document.getElementById('view-ejaan-nilai-rupiah').textContent = terbilangRupiah(data.nilai_rupiah);
    } else {
        document.getElementById('view-ejaan-nilai-rupiah').textContent = '-';
    }
    
    document.getElementById('view-dibayar-kepada').textContent = data.dibayar_kepada || '-';
    document.getElementById('view-kebun').textContent = data.kebun || '-';
    
    // Referensi Pendukung
    document.getElementById('view-no-spk').textContent = data.no_spk || '-';
    document.getElementById('view-tanggal-spk').textContent = formatDate(data.tanggal_spk);
    document.getElementById('view-tanggal-berakhir-spk').textContent = formatDate(data.tanggal_berakhir_spk);
    document.getElementById('view-nomor-mirror').textContent = data.nomor_mirror || '-';
    document.getElementById('view-no-berita-acara').textContent = data.no_berita_acara || '-';
    document.getElementById('view-tanggal-berita-acara').textContent = formatDate(data.tanggal_berita_acara);
    
    // Nomor PO & PR
    document.getElementById('view-nomor-po').textContent = data.no_po || '-';
    document.getElementById('view-nomor-pr').textContent = data.no_pr || '-';
    
    // Informasi Perpajakan
    document.getElementById('view-npwp').textContent = data.npwp || '-';
    document.getElementById('view-status-perpajakan').textContent = data.status_perpajakan || '-';
    document.getElementById('view-no-faktur').textContent = data.no_faktur || '-';
    document.getElementById('view-tanggal-faktur').textContent = formatDate(data.tanggal_faktur);
    document.getElementById('view-jenis-pph').textContent = data.jenis_pph || '-';
    
    // Format DPP PPH - gunakan raw value jika ada, atau yang sudah diformat
    if (data.dpp_pph_raw && data.dpp_pph_raw > 0) {
        document.getElementById('view-dpp-pph').textContent = 'Rp. ' + formatNumber(data.dpp_pph_raw);
    } else if (data.dpp_pph && data.dpp_pph !== '-') {
        // Jika sudah diformat dari controller, langsung gunakan
        document.getElementById('view-dpp-pph').textContent = 'Rp. ' + data.dpp_pph;
    } else {
        document.getElementById('view-dpp-pph').textContent = '-';
    }
    
    // Format PPN Terhutang - gunakan raw value jika ada, atau yang sudah diformat
    if (data.ppn_terhutang_raw && data.ppn_terhutang_raw > 0) {
        document.getElementById('view-ppn-terhutang').textContent = 'Rp. ' + formatNumber(data.ppn_terhutang_raw);
    } else if (data.ppn_terhutang && data.ppn_terhutang !== '-') {
        // Jika sudah diformat dari controller, langsung gunakan
        document.getElementById('view-ppn-terhutang').textContent = 'Rp. ' + data.ppn_terhutang;
    } else {
        document.getElementById('view-ppn-terhutang').textContent = '-';
    }
    
    // Link Dokumen Pajak
    const linkDokumenPajak = data.link_dokumen_pajak || '-';
    if (linkDokumenPajak !== '-' && linkDokumenPajak) {
        const linkEl = document.getElementById('view-link-dokumen-pajak');
        linkEl.innerHTML = `<a href="${linkDokumenPajak}" target="_blank" style="color: #0d6efd; text-decoration: underline;">${linkDokumenPajak}</a>`;
    } else {
        document.getElementById('view-link-dokumen-pajak').textContent = '-';
    }
    
    // Set document ID for edit button
    document.getElementById('view-dokumen-id').value = data.id || '';
}

// Function to convert number to Indonesian terbilang
function terbilangRupiah(number) {
    number = parseFloat(number) || 0;
    
    if (number == 0) {
        return 'nol rupiah';
    }

    const angka = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
        'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
        'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
    ];

    let hasil = '';

    // Handle triliun
    if (number >= 1000000000000) {
        const triliun = Math.floor(number / 1000000000000);
        hasil += terbilangSatuan(triliun, angka) + ' triliun ';
        number = number % 1000000000000;
    }

    // Handle milyar
    if (number >= 1000000000) {
        const milyar = Math.floor(number / 1000000000);
        hasil += terbilangSatuan(milyar, angka) + ' milyar ';
        number = number % 1000000000;
    }

    // Handle juta
    if (number >= 1000000) {
        const juta = Math.floor(number / 1000000);
        hasil += terbilangSatuan(juta, angka) + ' juta ';
        number = number % 1000000;
    }

    // Handle ribu
    if (number >= 1000) {
        const ribu = Math.floor(number / 1000);
        if (ribu == 1) {
            hasil += 'seribu ';
        } else {
            hasil += terbilangSatuan(ribu, angka) + ' ribu ';
        }
        number = number % 1000;
    }

    // Handle ratusan, puluhan, dan satuan
    if (number > 0) {
        hasil += terbilangSatuan(number, angka);
    }

    return hasil.trim() + ' rupiah';
}

function terbilangSatuan(number, angka) {
    let hasil = '';
    number = parseInt(number);

    if (number == 0) {
        return '';
    }

    // Handle ratusan
    if (number >= 100) {
        const ratus = Math.floor(number / 100);
        if (ratus == 1) {
            hasil += 'seratus ';
        } else {
            hasil += angka[ratus] + ' ratus ';
        }
        number = number % 100;
    }

    // Handle puluhan dan satuan (0-99)
    if (number > 0) {
        if (number < 20) {
            hasil += angka[number] + ' ';
        } else {
            const puluhan = Math.floor(number / 10);
            const satuan = number % 10;
            
            if (puluhan == 1) {
                hasil += angka[10 + satuan] + ' ';
            } else {
                hasil += angka[puluhan] + ' puluh ';
                if (satuan > 0) {
                    hasil += angka[satuan] + ' ';
                }
            }
        }
    }

    return hasil.trim();
}

</script>

<!-- Modal View Document Detail -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="max-width: 98%; width: 98%; margin: 0.5rem auto;">
    <div class="modal-content" style="height: 95vh; display: flex; flex-direction: column;">
      <!-- Sticky Header -->
      <div class="modal-header" style="position: sticky; top: 0; z-index: 1050; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-bottom: none; flex-shrink: 0;">
        <h5 class="modal-title" id="viewDocumentModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
          <i class="fa-solid fa-file-lines me-2"></i>
          Detail Dokumen Lengkap
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Scrollable Body -->
      <div class="modal-body" style="overflow-y: auto; max-height: calc(95vh - 140px); padding: 30px; flex: 1;">
        <input type="hidden" id="view-dokumen-id">
        
        <!-- Loading State -->
        <div id="view-loading" style="display: none; text-align: center; padding: 40px;">
          <i class="fas fa-spinner fa-spin fa-3x text-emerald-600 mb-3"></i>
          <p class="text-muted">Memuat data dokumen...</p>
        </div>
        
        <!-- Error State -->
        <div id="view-error" style="display: none; background: #fee; border: 1px solid #fcc; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
          <div class="d-flex align-items-center gap-2 text-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span></span>
          </div>
        </div>
        
        <!-- Content -->
        <div id="view-content" style="display: none;">
          <!-- Section 1: Identitas Dokumen -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-id-card"></i>
                IDENTITAS DOKUMEN
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Nomor Agenda</label>
                  <div class="detail-value" id="view-nomor-agenda">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Nomor SPP</label>
                  <div class="detail-value" id="view-nomor-spp">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Tanggal SPP</label>
                  <div class="detail-value" id="view-tanggal-spp">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Bulan</label>
                  <div class="detail-value" id="view-bulan">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Tahun</label>
                  <div class="detail-value" id="view-tahun">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Tanggal Masuk</label>
                  <div class="detail-value" id="view-tanggal-masuk">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Kriteria CF</label>
                  <div class="detail-value" id="view-kategori">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Sub Kriteria</label>
                  <div class="detail-value" id="view-jenis-dokumen">-</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="detail-item">
                  <label class="detail-label">Item Sub Kriteria</label>
                  <div class="detail-value" id="view-jenis-sub-pekerjaan">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Detail Keuangan & Vendor -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-money-bill-wave"></i>
                DETAIL KEUANGAN & VENDOR
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-12">
                <div class="detail-item">
                  <label class="detail-label">Uraian SPP</label>
                  <div class="detail-value" id="view-uraian-spp" style="white-space: pre-wrap;">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Nilai Rupiah</label>
                  <div class="detail-value" id="view-nilai-rupiah" style="font-weight: 700; color: #083E40;">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Ejaan Nilai Rupiah</label>
                  <div class="detail-value" id="view-ejaan-nilai-rupiah" style="font-style: italic; color: #666;">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Dibayar Kepada (Vendor)</label>
                  <div class="detail-value" id="view-dibayar-kepada">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Kebun / Unit Kerja</label>
                  <div class="detail-value" id="view-kebun">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: Referensi Pendukung -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-contract"></i>
                REFERENSI PENDUKUNG
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-3">
                <div class="detail-item">
                  <label class="detail-label">No. SPK</label>
                  <div class="detail-value" id="view-no-spk">-</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="detail-item">
                  <label class="detail-label">Tanggal SPK</label>
                  <div class="detail-value" id="view-tanggal-spk">-</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="detail-item">
                  <label class="detail-label">Tanggal Berakhir SPK</label>
                  <div class="detail-value" id="view-tanggal-berakhir-spk">-</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="detail-item">
                  <label class="detail-label">No. Mirror</label>
                  <div class="detail-value" id="view-nomor-mirror">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">No. Berita Acara</label>
                  <div class="detail-value" id="view-no-berita-acara">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Tanggal Berita Acara</label>
                  <div class="detail-value" id="view-tanggal-berita-acara">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 4: Nomor PO & PR -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-hashtag"></i>
                NOMOR PO & PR
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Nomor PO</label>
                  <div class="detail-value" id="view-nomor-po">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Nomor PR</label>
                  <div class="detail-value" id="view-nomor-pr">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 5: Informasi Perpajakan -->
          <div class="form-section mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 12px; padding: 20px; border: 2px solid #ffc107;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #92400e; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                INFORMASI PERPAJAKAN
                <span style="background: #ffc107; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px;">KHUSUS PERPAJAKAN</span>
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">NPWP</label>
                  <div class="detail-value" id="view-npwp">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Status Perpajakan</label>
                  <div class="detail-value" id="view-status-perpajakan">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">No. Faktur</label>
                  <div class="detail-value" id="view-no-faktur">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Tanggal Faktur</label>
                  <div class="detail-value" id="view-tanggal-faktur">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Jenis PPH</label>
                  <div class="detail-value" id="view-jenis-pph">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">DPP PPH</label>
                  <div class="detail-value" id="view-dpp-pph" style="font-weight: 600; color: #92400e;">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">PPN Terhutang</label>
                  <div class="detail-value" id="view-ppn-terhutang" style="font-weight: 600; color: #92400e;">-</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label class="detail-label">Link Dokumen Pajak</label>
                  <div class="detail-value" id="view-link-dokumen-pajak">-</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Modal Footer -->
      <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #dee2e6; padding: 16px 24px; flex-shrink: 0;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Tutup
        </button>
        <button type="button" class="btn btn-primary" onclick="editDocumentFromModal()" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
          <i class="fa-solid fa-pencil me-2"></i>Edit Dokumen
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function editDocumentFromModal() {
  const dokumenId = document.getElementById('view-dokumen-id').value;
  if (dokumenId) {
    // Close modal first
    const modal = bootstrap.Modal.getInstance(document.getElementById('viewDocumentModal'));
    if (modal) {
      modal.hide();
    }
    // Open edit modal
    if (typeof window.openEditPembayaranModal === 'function') {
      window.openEditPembayaranModal(parseInt(dokumenId));
    } else {
      console.error('openEditPembayaranModal function not found');
    }
  }
}
</script>

<style>
.detail-item {
  margin-bottom: 0;
}

.detail-label {
  font-size: 12px;
  font-weight: 600;
  color: #6c757d;
  margin-bottom: 4px;
  display: block;
}

.detail-value {
  font-size: 14px;
  color: #212529;
  padding: 8px 12px;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  min-height: 38px;
  display: flex;
  align-items: center;
}
</style>

@endsection
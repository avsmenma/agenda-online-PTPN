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
          @if($paymentStatus !== 'belum_siap_bayar')
            onclick="openDocumentDetailModal({{ $dokumen->id }}, event); return false;"
            style="cursor: pointer;"
            class="clickable-row"
          @else
            style="cursor: default;"
            class="no-click-row"
            title="Dokumen belum siap bayar. Klik icon mata untuk melihat tracking."
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
                  <button type="button" class="btn-action" onclick="event.stopPropagation(); event.preventDefault(); if(typeof window.openEditPembayaranModal === 'function') { window.openEditPembayaranModal({{ $dokumen->id }}); } else { console.error('openEditPembayaranModal function not found'); alert('Fungsi tidak tersedia. Silakan refresh halaman.'); }">
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
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPembayaranModalLabel">
          <i class="fa-solid fa-edit me-2"></i>Edit Pembayaran
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editPembayaranForm">
          <input type="hidden" id="editPembayaranDocId" name="dokumen_id" value="">
          <div class="form-group mb-3">
            <label for="tanggal_dibayar" class="form-label">Tanggal Pembayaran</label>
            <input type="date" name="tanggal_dibayar" id="tanggal_dibayar" class="form-control" value="">
            <small class="text-muted">Isi tanggal ketika pembayaran dilakukan</small>
          </div>
          <div class="form-group mb-3">
            <label for="link_bukti_pembayaran" class="form-label">Link Bukti Pembayaran</label>
            <input type="url" name="link_bukti_pembayaran" id="link_bukti_pembayaran" class="form-control" placeholder="https://drive.google.com/..." value="">
            <small class="text-muted">Masukkan link PDF/Drive bukti pembayaran</small>
          </div>
          <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Catatan:</strong> Minimal salah satu field harus diisi. Status akan otomatis berubah menjadi "Sudah Dibayar" setelah salah satu field diisi.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="submitEditPembayaran()">Simpan</button>
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
    
    const modalElement = document.getElementById('documentDetailModal');
    if (!modalElement) {
        console.error('Modal element not found');
        alert('Modal tidak ditemukan. Silakan refresh halaman.');
        return false;
    }
    
    // Show modal immediately (before Alpine.js processes)
    modalElement.style.display = 'block';
    modalElement.style.position = 'fixed';
    modalElement.style.top = '0';
    modalElement.style.left = '0';
    modalElement.style.right = '0';
    modalElement.style.bottom = '0';
    modalElement.style.zIndex = '99999';
    modalElement.style.width = '100vw';
    modalElement.style.height = '100vh';
    modalElement.style.visibility = 'visible';
    modalElement.style.opacity = '1';
    
    // Remove x-cloak to ensure visibility
    modalElement.removeAttribute('x-cloak');
    modalElement.classList.remove('x-cloak');
    
    // Ensure modal content is visible immediately
    const modalContainer = modalElement.querySelector('.fixed.inset-0.flex');
    const modalContent = modalElement.querySelector('.bg-white.rounded-2xl');
    if (modalContainer) {
        modalContainer.style.display = 'flex';
        modalContainer.style.visibility = 'visible';
        modalContainer.style.opacity = '1';
        modalContainer.style.alignItems = 'center';
        modalContainer.style.justifyContent = 'center';
    }
    if (modalContent) {
        modalContent.style.display = 'block';
        modalContent.style.visibility = 'visible';
        modalContent.style.opacity = '1';
        modalContent.style.background = 'white';
        modalContent.style.margin = '0 auto';
    }
    
    // Wait for Alpine.js to be ready
    if (typeof Alpine !== 'undefined') {
        // Use custom event - Alpine will handle it via @open-document-modal.window
        setTimeout(() => {
            console.log('Dispatching open-document-modal event for dokumenId:', dokumenId);
            window.dispatchEvent(new CustomEvent('open-document-modal', { 
                detail: { dokumenId: dokumenId },
                bubbles: true,
                cancelable: true
            }));
        }, 50);
    } else {
        // Fallback: Direct modal manipulation if Alpine.js not loaded
        console.warn('Alpine.js not loaded, using fallback');
        if (typeof loadDocumentDetail === 'function') {
            loadDocumentDetail(dokumenId);
        }
    }
    
    // Prevent any navigation
    return false;
};

// Fallback function to load document detail (if Alpine.js not available)
window.loadDocumentDetail = function(dokumenId) {
    const modalElement = document.getElementById('documentDetailModal');
    if (!modalElement) {
        console.error('Modal element not found in loadDocumentDetail');
        return;
    }
    
    console.log('loadDocumentDetail called for dokumenId:', dokumenId);
    
    // Show loading state
    const loadingEl = modalElement.querySelector('[x-show="loading"]');
    const errorEl = modalElement.querySelector('[x-show="error && !loading"]');
    const modernView = modalElement.querySelector('[x-show="!loading && !error && viewMode === \'modern\'"]');
    const excelView = modalElement.querySelector('[x-show="!loading && !error && viewMode === \'excel\'"]');
    
    // Show loading, hide others
    if (loadingEl) {
        loadingEl.style.display = 'flex';
        loadingEl.removeAttribute('x-cloak');
    }
    if (errorEl) errorEl.style.display = 'none';
    if (modernView) modernView.style.display = 'none';
    if (excelView) excelView.style.display = 'none';
    
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
        console.log('Fallback response status:', response.status);
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Fallback response is not JSON. Content-Type:', contentType);
                console.error('Response body:', text);
                throw new Error('Server returned non-JSON response. Status: ' + response.status);
            });
        }
        
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Gagal memuat data dokumen. Status: ' + response.status);
            });
        }
        
        return response.json();
    })
    .then(result => {
        console.log('Fallback document detail response:', result);
        if (result.success && result.data) {
            // Hide loading, show content
            if (loadingEl) loadingEl.style.display = 'none';
            if (modernView) {
                modernView.style.display = 'block';
                modernView.removeAttribute('x-cloak');
            }
            
            // Try to update Alpine.js component if available
            if (window.documentDetailModalInstance) {
                window.documentDetailModalInstance.data = result.data;
                window.documentDetailModalInstance.loading = false;
                window.documentDetailModalInstance.error = null;
            }
        } else {
            throw new Error(result.message || 'Data tidak ditemukan');
        }
    })
    .catch(error => {
        console.error('Error loading document:', error);
        if (loadingEl) loadingEl.style.display = 'none';
        if (errorEl) {
            errorEl.style.display = 'block';
            errorEl.removeAttribute('x-cloak');
            const errorText = errorEl.querySelector('span');
            if (errorText) errorText.textContent = error.message || 'Terjadi kesalahan saat memuat data dokumen';
        }
    });
};
</script>

{{-- Smart Dual-View Modal Component --}}
<div id="documentDetailModal"
     x-data="documentDetailModal()" 
     x-show="show" 
     x-cloak
     @open-document-modal.window="openModal($event.detail.dokumenId)"
     @keydown.escape.window="if (typeof closeModal === 'function') { closeModal(); }"
     class="document-modal-overlay"
     style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999; width: 100vw; height: 100vh; overflow-y: auto;"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black bg-opacity-60 transition-opacity backdrop-blur-sm" 
         @click="closeModal()" 
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99998; pointer-events: auto; width: 100vw; height: 100vh; margin: 0; padding: 0;"></div>
    
    {{-- Modal Container - Centered --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 z-50 pointer-events-none" 
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999; display: flex !important; align-items: center !important; justify-content: center !important; pointer-events: none; width: 100vw; height: 100vh; margin: 0; padding: 16px;">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden transform transition-all pointer-events-auto"
             style="pointer-events: auto; background: white !important; display: block !important; visibility: visible !important; opacity: 1 !important; width: 100%; max-width: 72rem; max-height: 90vh; margin: 0 auto; position: relative;"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop>
            
            {{-- Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 flex items-center justify-between z-10">
                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-bold text-white">Detail Dokumen</h3>
                    <span x-show="loading" class="text-white text-sm">
                        <i class="fas fa-spinner fa-spin"></i> Memuat...
                    </span>
                </div>
                
                {{-- View Switcher --}}
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 rounded-lg p-1 flex gap-1">
                        <button @click="viewMode = 'modern'" 
                                :class="viewMode === 'modern' ? 'bg-white text-emerald-600' : 'text-white hover:bg-white/10'"
                                class="px-4 py-2 rounded-md text-sm font-semibold transition-all flex items-center gap-2">
                            <i class="fas fa-file-alt"></i> Modern
                        </button>
                        <button @click="viewMode = 'excel'" 
                                :class="viewMode === 'excel' ? 'bg-white text-emerald-600' : 'text-white hover:bg-white/10'"
                                class="px-4 py-2 rounded-md text-sm font-semibold transition-all flex items-center gap-2">
                            <i class="fas fa-table"></i> Excel
                        </button>
                    </div>
                    <button @click="closeModal()" class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            {{-- Modal Body --}}
            <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6" style="background: white; overflow-y: auto;">
                {{-- Loading State --}}
                <div x-show="loading" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="flex items-center justify-center py-20" 
                     style="min-height: 200px;">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-4xl text-emerald-600 mb-4"></i>
                        <p class="text-gray-600">Memuat data dokumen...</p>
                    </div>
                </div>
                
                {{-- Error State --}}
                <div x-show="error && !loading" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-red-800">
                        <i class="fas fa-exclamation-circle"></i>
                        <span x-text="error"></span>
                    </div>
                </div>
                
                {{-- Modern View --}}
                <div x-show="!loading && !error && viewMode === 'modern' && data !== null && data !== undefined" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="space-y-6">
                    {{-- Header Section: No SPP, Judul Pekerjaan, Nilai Rp --}}
                    <div class="bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 rounded-2xl p-8 border-2 border-emerald-200 shadow-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="bg-white rounded-xl p-5 shadow-md border border-emerald-100">
                                <label class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-2 block flex items-center gap-2">
                                    <i class="fas fa-file-invoice text-emerald-600"></i>
                                    No. SPP
                                </label>
                                <p class="text-xl font-bold text-gray-900 leading-tight" x-text="data && data.nomor_spp ? data.nomor_spp : '-'"></p>
                            </div>
                            <div class="md:col-span-2 bg-white rounded-xl p-5 shadow-md border border-emerald-100">
                                <label class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-2 block flex items-center gap-2">
                                    <i class="fas fa-briefcase text-emerald-600"></i>
                                    Judul Pekerjaan
                                </label>
                                <p class="text-xl font-semibold text-gray-900 leading-relaxed" x-text="data && data.uraian_spp ? data.uraian_spp : '-'"></p>
                            </div>
                            <div class="md:col-span-3 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl p-6 shadow-lg">
                                <label class="text-xs font-bold text-white uppercase tracking-wider mb-2 block flex items-center gap-2">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Nilai Rupiah
                                </label>
                                <p class="text-4xl font-bold text-white" x-text="data && data.nilai_rupiah_formatted ? data.nilai_rupiah_formatted : '-'"></p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Body: Grid 2 Columns --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Left Column: Data Tanggal & Vendor --}}
                        <div class="space-y-6">
                            {{-- Section: Data Tanggal --}}
                            <div class="bg-white rounded-2xl p-6 border-2 border-gray-200 shadow-lg">
                                <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3 pb-3 border-b-2 border-emerald-200">
                                    <div class="bg-emerald-100 p-3 rounded-lg">
                                        <i class="fas fa-calendar-alt text-emerald-600 text-xl"></i>
                                    </div>
                                    <span>Data Tanggal</span>
                                </h4>
                                <div class="space-y-4">
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-calendar-check text-emerald-500"></i>
                                                Tanggal Masuk
                                            </span>
                                            <span class="text-base font-bold text-gray-900" x-text="data && data.tanggal_masuk ? data.tanggal_masuk : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-file-invoice text-emerald-500"></i>
                                                Tanggal SPP
                                            </span>
                                            <span class="text-base font-bold text-gray-900" x-text="data && data.tanggal_spp ? data.tanggal_spp : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-file-alt text-emerald-500"></i>
                                                Tanggal Berita Acara
                                            </span>
                                            <span class="text-base font-bold text-gray-900" x-text="data && data.tanggal_berita_acara ? data.tanggal_berita_acara : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-file-contract text-emerald-500"></i>
                                                Tanggal SPK
                                            </span>
                                            <span class="text-base font-bold text-gray-900" x-text="data && data.tanggal_spk ? data.tanggal_spk : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-calendar-times text-emerald-500"></i>
                                                Tanggal Berakhir SPK
                                            </span>
                                            <span class="text-base font-bold text-gray-900" x-text="data && data.tanggal_berakhir_spk ? data.tanggal_berakhir_spk : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-emerald-50 rounded-lg p-4 border-2 border-emerald-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-emerald-700 flex items-center gap-2">
                                                <i class="fas fa-money-check-alt text-emerald-600"></i>
                                                Tanggal Dibayar
                                            </span>
                                            <span class="text-base font-bold text-emerald-700" x-text="data && data.tanggal_dibayar ? data.tanggal_dibayar : '-'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Section: Data Vendor --}}
                            <div class="bg-white rounded-2xl p-6 border-2 border-gray-200 shadow-lg">
                                <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3 pb-3 border-b-2 border-emerald-200">
                                    <div class="bg-emerald-100 p-3 rounded-lg">
                                        <i class="fas fa-building text-emerald-600 text-xl"></i>
                                    </div>
                                    <span>Data Vendor</span>
                                </h4>
                                <div class="space-y-4">
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-user-tie text-emerald-500"></i>
                                                Dibayar Kepada
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right max-w-xs" x-text="data && data.dibayar_kepada ? data.dibayar_kepada : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-seedling text-emerald-500"></i>
                                                Kebun
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.kebun ? data.kebun : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-sitemap text-emerald-500"></i>
                                                Bagian
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.bagian ? data.bagian : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-tags text-emerald-500"></i>
                                                Kategori
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.kategori ? data.kategori : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-file text-emerald-500"></i>
                                                Jenis Dokumen
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.jenis_dokumen ? data.jenis_dokumen : '-'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Right Column: Data Pajak & Akuntansi --}}
                        <div class="space-y-6">
                            {{-- Section: Data Pajak --}}
                            <div class="bg-white rounded-2xl p-6 border-2 border-gray-200 shadow-lg">
                                <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3 pb-3 border-b-2 border-emerald-200">
                                    <div class="bg-emerald-100 p-3 rounded-lg">
                                        <i class="fas fa-receipt text-emerald-600 text-xl"></i>
                                    </div>
                                    <span>Data Pajak</span>
                                </h4>
                                <div class="space-y-4">
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-id-card text-emerald-500"></i>
                                                NPWP
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right font-mono" x-text="data && data.npwp ? data.npwp : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-check-circle text-emerald-500"></i>
                                                Status Perpajakan
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.status_perpajakan ? data.status_perpajakan : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-file-invoice-dollar text-emerald-500"></i>
                                                No. Faktur
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.no_faktur ? data.no_faktur : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-calendar text-emerald-500"></i>
                                                Tanggal Faktur
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.tanggal_faktur ? data.tanggal_faktur : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-percent text-emerald-500"></i>
                                                Jenis PPH
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.jenis_pph ? data.jenis_pph : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-emerald-50 rounded-lg p-4 border-2 border-emerald-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-emerald-700 flex items-center gap-2">
                                                <i class="fas fa-coins text-emerald-600"></i>
                                                DPP PPH
                                            </span>
                                            <span class="text-base font-bold text-emerald-700" x-text="data && data.dpp_pph ? 'Rp ' + data.dpp_pph : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-emerald-50 rounded-lg p-4 border-2 border-emerald-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-emerald-700 flex items-center gap-2">
                                                <i class="fas fa-money-bill-wave text-emerald-600"></i>
                                                PPN Terhutang
                                            </span>
                                            <span class="text-base font-bold text-emerald-700" x-text="data && data.ppn_terhutang ? 'Rp ' + data.ppn_terhutang : '-'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Section: Data Akuntansi --}}
                            <div class="bg-white rounded-2xl p-6 border-2 border-gray-200 shadow-lg">
                                <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3 pb-3 border-b-2 border-emerald-200">
                                    <div class="bg-emerald-100 p-3 rounded-lg">
                                        <i class="fas fa-calculator text-emerald-600 text-xl"></i>
                                    </div>
                                    <span>Data Akuntansi</span>
                                </h4>
                                <div class="space-y-4">
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-shopping-cart text-emerald-500"></i>
                                                No. PO
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.no_po ? data.no_po : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-clipboard-list text-emerald-500"></i>
                                                No. PR
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.no_pr ? data.no_pr : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-file-signature text-emerald-500"></i>
                                                No. Berita Acara
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.no_berita_acara ? data.no_berita_acara : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-handshake text-emerald-500"></i>
                                                No. SPK
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.no_spk ? data.no_spk : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-700 flex items-center gap-2">
                                                <i class="fas fa-copy text-emerald-500"></i>
                                                No. Mirror
                                            </span>
                                            <span class="text-base font-bold text-gray-900 text-right" x-text="data && data.nomor_mirror ? data.nomor_mirror : '-'"></span>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-lg p-4 border-2 border-emerald-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-semibold text-emerald-700 flex items-center gap-2">
                                                <i class="fas fa-info-circle text-emerald-600"></i>
                                                Status Pembayaran
                                            </span>
                                            <span class="px-4 py-2 rounded-full text-sm font-bold shadow-md"
                                                  :class="data && data.payment_status ? {
                                                      'bg-green-500 text-white': data.payment_status === 'sudah_dibayar',
                                                      'bg-yellow-400 text-white': data.payment_status === 'siap_bayar',
                                                      'bg-gray-400 text-white': data.payment_status === 'belum_siap_bayar'
                                                  } : {}"
                                                  x-text="data && data.payment_status ? (data.payment_status === 'sudah_dibayar' ? 'Sudah Dibayar' : (data.payment_status === 'siap_bayar' ? 'Siap Bayar' : 'Belum Siap Bayar')) : '-'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Excel View --}}
                <div x-show="!loading && !error && viewMode === 'excel' && data !== null && data !== undefined" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="overflow-x-auto" 
                     style="background: white;">
                    <table class="w-full border-collapse border border-gray-400 text-sm font-mono">
                        <thead>
                            <tr class="bg-green-600">
                                <th class="border border-gray-400 px-2 py-1 text-left text-white font-bold">Field</th>
                                <th class="border border-gray-400 px-2 py-1 text-left text-white font-bold">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">No. SPP</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.nomor_spp ? data.nomor_spp : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Uraian SPP</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.uraian_spp ? data.uraian_spp : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Nilai Rupiah</td>
                                <td class="border border-gray-400 px-2 py-1 font-bold" x-text="data && data.nilai_rupiah_formatted ? data.nilai_rupiah_formatted : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Tanggal Masuk</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.tanggal_masuk ? data.tanggal_masuk : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Tanggal SPP</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.tanggal_spp ? data.tanggal_spp : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Dibayar Kepada</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.dibayar_kepada ? data.dibayar_kepada : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Kebun</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.kebun ? data.kebun : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Bagian</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.bagian ? data.bagian : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Kategori</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.kategori ? data.kategori : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Jenis Dokumen</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.jenis_dokumen ? data.jenis_dokumen : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">No. Berita Acara</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.no_berita_acara ? data.no_berita_acara : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Tanggal Berita Acara</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.tanggal_berita_acara ? data.tanggal_berita_acara : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">No. SPK</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.no_spk ? data.no_spk : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Tanggal SPK</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.tanggal_spk ? data.tanggal_spk : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">NPWP</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.npwp ? data.npwp : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Status Perpajakan</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.status_perpajakan ? data.status_perpajakan : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">No. Faktur</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.no_faktur ? data.no_faktur : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Tanggal Faktur</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.tanggal_faktur ? data.tanggal_faktur : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">DPP PPH</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.dpp_pph ? 'Rp ' + data.dpp_pph : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">PPN Terhutang</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.ppn_terhutang ? 'Rp ' + data.ppn_terhutang : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">No. PO</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.no_po ? data.no_po : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">No. PR</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.no_pr ? data.no_pr : '-'"></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Tanggal Dibayar</td>
                                <td class="border border-gray-400 px-2 py-1" x-text="data && data.tanggal_dibayar ? data.tanggal_dibayar : '-'"></td>
                            </tr>
                            <tr>
                                <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-100">Status Pembayaran</td>
                                <td class="border border-gray-400 px-2 py-1 font-bold" x-text="data && data.payment_status ? (data.payment_status === 'sudah_dibayar' ? 'Sudah Dibayar' : (data.payment_status === 'siap_bayar' ? 'Siap Bayar' : 'Belum Siap Bayar')) : '-'"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function documentDetailModal() {
    const component = {
        show: false,
        loading: false,
        error: null,
        data: null, // Initialize as null
        viewMode: 'modern', // 'modern' or 'excel'
        
        // Helper method to safely access data properties
        safeData(prop, defaultValue = '-') {
            return this.data && this.data[prop] !== null && this.data[prop] !== undefined ? this.data[prop] : defaultValue;
        },
        
        // Helper method to safely format currency
        safeCurrency(prop, defaultValue = '-') {
            if (!this.data || !this.data[prop]) return defaultValue;
            return 'Rp ' + this.data[prop];
        },
        
        openModal(dokumenId) {
            console.log('Opening modal for dokumen ID:', dokumenId);
            this.show = true;
            this.loading = true;
            this.error = null;
            this.data = null;
            
            // Prevent body scroll
            document.body.classList.add('modal-open');
            
            // Force show modal immediately (fallback if Alpine.js hasn't initialized)
            setTimeout(() => {
                const modalElement = document.getElementById('documentDetailModal');
                if (modalElement) {
                    modalElement.style.display = 'block';
                    modalElement.style.position = 'fixed';
                    modalElement.style.top = '0';
                    modalElement.style.left = '0';
                    modalElement.style.right = '0';
                    modalElement.style.bottom = '0';
                    modalElement.style.zIndex = '99999';
                    modalElement.style.width = '100vw';
                    modalElement.style.height = '100vh';
                    modalElement.style.margin = '0';
                    modalElement.style.padding = '0';
                    modalElement.style.visibility = 'visible';
                    modalElement.style.opacity = '1';
                    
                    // Ensure modal container is visible
                    const modalContainer = modalElement.querySelector('.fixed.inset-0.flex');
                    if (modalContainer) {
                        modalContainer.style.display = 'flex';
                        modalContainer.style.alignItems = 'center';
                        modalContainer.style.justifyContent = 'center';
                    }
                    
                    // Ensure modal content is visible
                    const modalContent = modalElement.querySelector('.bg-white.rounded-2xl');
                    if (modalContent) {
                        modalContent.style.display = 'block';
                        modalContent.style.visibility = 'visible';
                        modalContent.style.opacity = '1';
                        modalContent.style.margin = '0 auto';
                    }
                }
            }, 100);
            
            // Fetch document detail via AJAX
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
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Response is not JSON. Content-Type:', contentType);
                    return response.text().then(text => {
                        console.error('Response body:', text);
                        throw new Error('Server returned non-JSON response. Status: ' + response.status);
                    });
                }
                
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Gagal memuat data dokumen. Status: ' + response.status);
                    });
                }
                
                return response.json();
            })
            .then(result => {
                console.log('Document detail response:', result);
                if (result.success && result.data) {
                    // Set data and update loading state using proper Alpine.js reactivity
                    const self = this;
                    self.data = result.data;
                    self.loading = false;
                    self.error = null;
                    console.log('Data loaded successfully:', self.data);
                    console.log('Loading set to false, error set to null');
                    
                    // Use requestAnimationFrame to ensure DOM updates synchronously
                    requestAnimationFrame(() => {
                        console.log('After RAF - State:', {
                            loading: self.loading,
                            error: self.error,
                            hasData: !!self.data,
                            viewMode: self.viewMode
                        });
                    });
                } else {
                    throw new Error(result.message || 'Data tidak ditemukan');
                }
            })
            .catch(error => {
                console.error('Error loading document detail:', error);
                if (typeof Alpine !== 'undefined' && Alpine.nextTick) {
                    Alpine.nextTick(() => {
                        this.error = error.message || 'Terjadi kesalahan saat memuat data dokumen';
                        this.data = null;
                        this.loading = false;
                    });
                } else {
                    this.error = error.message || 'Terjadi kesalahan saat memuat data dokumen';
                    this.data = null;
                    this.loading = false;
                }
            });
        },
        
        closeModal() {
            console.log('Closing modal');
            this.show = false;
            this.data = null;
            this.error = null;
            
            // Restore body scroll
            document.body.classList.remove('modal-open');
            
            // Force hide modal
            const modalElement = document.getElementById('documentDetailModal');
            if (modalElement) {
                modalElement.style.display = 'none';
            }
        }
    };
    
    // Expose methods globally for direct access (after Alpine initializes)
    setTimeout(() => {
        window.documentDetailModalInstance = component;
    }, 100);
    
    return component;
}
</script>

<style>
[x-cloak] { 
    display: none !important; 
}

/* Ensure modal is always on top */
.fixed.inset-0.z-\[9999\] {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 9999 !important;
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
}

/* Ensure modal backdrop is visible and always on top */
.document-modal-overlay,
#documentDetailModal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 99999 !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow-y: auto !important;
    background: transparent !important;
}

/* Backdrop styling */
#documentDetailModal .fixed.inset-0 {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 99998 !important;
    background-color: rgba(0, 0, 0, 0.6) !important;
    backdrop-filter: blur(4px);
}

/* Modal container - centered */
#documentDetailModal > .fixed.inset-0.flex {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 99999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    pointer-events: none !important;
    padding: 16px !important;
    margin: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
}

/* Modal content - white box */
#documentDetailModal .bg-white.rounded-2xl {
    background-color: white !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    position: relative !important;
    margin: 0 auto !important;
    width: 100% !important;
    max-width: 72rem !important;
    max-height: 90vh !important;
    display: block !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    border-radius: 1rem !important;
}

/* Ensure backdrop covers entire screen */
#documentDetailModal > .fixed.inset-0:first-of-type {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 99998 !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Ensure modal content is styled correctly - let Alpine.js control visibility */
#documentDetailModal [x-cloak] {
    display: none !important;
}
</style>

@endsection
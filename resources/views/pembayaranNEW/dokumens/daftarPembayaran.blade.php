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
            <a href="#" class="dropdown-item-modern {{ $statusFilter === 'siap_dibayar' ? 'active' : '' }}" data-filter="siap_dibayar">
              <i class="fa-solid fa-check-circle"></i>
              <span>Siap Dibayar</span>
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
          // Handler yang dianggap "belum siap dibayar"
          $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];
        @endphp
        <tr class="{{ in_array($dokumen->current_handler, $belumSiapHandlers) ? 'locked-row' : '' }}" data-dokumen-id="{{ $dokumen->id }}">
          <td class="col-no">{{ $index + 1 }}</td>
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
                @php
                  // Use computed_status if available, otherwise fallback to status_pembayaran
                  $status = $dokumen->computed_status ?? $dokumen->status_pembayaran ?? 'belum_siap_dibayar';
                  // Handle uppercase formats
                  if (is_string($status)) {
                    $statusUpper = strtoupper(trim($status));
                    if ($statusUpper === 'SUDAH_DIBAYAR' || $statusUpper === 'SUDAH DIBAYAR') {
                      $status = 'sudah_dibayar';
                    } elseif ($statusUpper === 'SIAP_DIBAYAR' || $statusUpper === 'SIAP DIBAYAR') {
                      $status = 'siap_dibayar';
                    }
                  }
                @endphp
                @switch($status)
                  @case('siap_dibayar')
                    <span class="status-badge siap-dibayar">Siap Dibayar</span>
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
              @php
                // Use computed_status if available, otherwise fallback to status_pembayaran
                $status = $dokumen->computed_status ?? $dokumen->status_pembayaran ?? 'belum_siap_dibayar';
                // Handle uppercase formats
                if (is_string($status)) {
                  $statusUpper = strtoupper(trim($status));
                  if ($statusUpper === 'SUDAH_DIBAYAR' || $statusUpper === 'SUDAH DIBAYAR') {
                    $status = 'sudah_dibayar';
                  } elseif ($statusUpper === 'SIAP_DIBAYAR' || $statusUpper === 'SIAP DIBAYAR') {
                    $status = 'siap_dibayar';
                  }
                }
              @endphp
              @switch($status)
                @case('siap_dibayar')
                  <span class="status-badge siap-dibayar">Siap Dibayar</span>
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
          <td class="col-action">
            <div class="action-buttons">
              @php
                // Use computed_status if available, otherwise fallback to status_pembayaran
                $docStatus = $dokumen->computed_status ?? $dokumen->status_pembayaran ?? 'belum_siap_dibayar';
                // Handle uppercase formats
                if (is_string($docStatus)) {
                  $statusUpper = strtoupper(trim($docStatus));
                  if ($statusUpper === 'SUDAH_DIBAYAR' || $statusUpper === 'SUDAH DIBAYAR') {
                    $docStatus = 'sudah_dibayar';
                  } elseif ($statusUpper === 'SIAP_DIBAYAR' || $statusUpper === 'SIAP DIBAYAR') {
                    $docStatus = 'siap_dibayar';
                  }
                }
              @endphp
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
                <button type="button" class="btn-action" onclick="openEditPembayaranModal({{ $dokumen->id }})">
                  <i class="fa-solid fa-edit"></i>
                  Edit
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
    newForm.action = '{{ route("dokumensPembayaran.index") }}';
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
            
            // Preserve search parameter if exists
            const searchInput = document.getElementById('pembayaranSearchInput');
            if (searchInput && searchInput.value.trim()) {
                url.searchParams.set('search', searchInput.value.trim());
            } else {
                url.searchParams.delete('search');
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
            'siap_dibayar': 'Siap Dibayar',
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
        
        // Build URL with current filter and search
        const url = new URL(window.location.pathname, window.location.origin);
        
        // Add search parameter if not empty
        if (searchValue) {
            url.searchParams.set('search', searchValue);
        } else {
            url.searchParams.delete('search');
        }
        
        // Preserve status filter
        if (currentFilter) {
            url.searchParams.set('status_filter', currentFilter);
        } else {
            url.searchParams.delete('status_filter');
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

function openEditPembayaranModal(docId) {
    // Set dokumen ID in hidden field
    const docIdField = document.getElementById('editPembayaranDocId');
    const tanggalField = document.getElementById('tanggal_dibayar');
    const linkField = document.getElementById('link_bukti_pembayaran');
    const modalElement = document.getElementById('editPembayaranModal');
    
    if (!docIdField || !tanggalField || !linkField || !modalElement) {
        console.error('Modal elements not found');
        alert('Terjadi kesalahan. Silakan muat ulang halaman.');
        return;
    }
    
    docIdField.value = docId;
    
    // Ambil data terbaru dari server untuk memastikan nilai tidak hilang
    fetch(`/dokumensPembayaran/${docId}/get-payment-data`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            tanggalField.value = data.tanggal_dibayar || '';
            linkField.value = data.link_bukti_pembayaran || '';
        }
        
        // Use getOrCreateInstance for better compatibility
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
    })
    .catch(error => {
        console.error('Error fetching payment data:', error);
        // Jika error, tetap buka modal dengan nilai kosong
        tanggalField.value = '';
        linkField.value = '';
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
    });
}

function submitEditPembayaran() {
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
}
</script>

@endsection
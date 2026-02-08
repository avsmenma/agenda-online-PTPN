@extends('layouts/app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Dark mode override for h2 */
    .dark h2 {
      background: none !important;
      -webkit-background-clip: unset !important;
      -webkit-text-fill-color: #ffffff !important;
      background-clip: unset !important;
      color: #ffffff !important;
    }

    .dark h2 i {
      color: #ffffff !important;
    }


    .search-box {
      background: #ffffff;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
    }

    .search-filter-form {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .search-input-group {
      flex: 1;
      min-width: 250px;
    }

    .search-box .input-group-text {
      background: white;
      border: 1px solid #dee2e6;
      border-right: none;
      border-radius: 8px 0 0 8px;
      padding: 10px 14px;
    }

    .search-box .form-control {
      border: 1px solid #dee2e6;
      border-left: none;
      border-radius: 0 8px 8px 0;
      padding: 10px 14px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .search-box .form-control:focus {
      outline: none;
      border-color: #889717;
      box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
    }

    .btn-year-select,
    .btn-status-select {
      padding: 10px 16px;
      background: white;
      color: #495057;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      min-height: 44px;
    }

    .btn-year-select:hover,
    .btn-status-select:hover {
      border-color: #889717;
      background: #f8f9fa;
    }

    .btn-filter {
      padding: 10px 20px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
      min-height: 44px;
    }

    .btn-filter:hover {
      transform: translateY(-1px);
    }

    .btn-customize-columns-inline {
      padding: 10px 20px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      min-height: 44px;
      white-space: nowrap;
    }

    .btn-customize-columns-inline:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
      background: linear-gradient(135deg, #0a4f52 0%, #0c6065 100%);
      color: white;
    }

    .btn-customize-columns-inline:active {
      transform: translateY(0);
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
    }

    /* Column Customization Modal */
    .customization-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      overflow-y: auto;
      padding: 20px;
      box-sizing: border-box;
    }

    .customization-modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .customization-modal .modal-content-custom {
      background: white;
      border-radius: 20px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
      max-width: 600px;
      width: 100%;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .customization-modal .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 20px 24px;
      border-radius: 16px 16px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .customization-modal .modal-header-custom h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .customization-modal .modal-body-custom {
      padding: 24px;
      overflow-y: auto;
    }

    .column-selection-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 400px;
      overflow-y: auto;
      padding: 8px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .column-item {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      background: #ffffff;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      transition: all 0.2s ease;
      gap: 12px;
    }

    .column-item:hover {
      border-color: #889717;
      background: #f8f9ff;
    }

    .column-item.selected {
      border-color: #28a745;
      background: #f0f9f4;
    }

    .column-item-checkbox {
      width: 20px;
      height: 20px;
      cursor: pointer;
    }

    .column-item-label {
      font-size: 14px;
      color: #212529;
      font-weight: 500;
      flex: 1;
    }

    .customization-modal .modal-footer-custom {
      padding: 16px 24px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }

    .btn-modal {
      padding: 10px 24px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-cancel {
      background: #6c757d;
      color: white;
    }

    .btn-cancel:hover {
      background: #5a6268;
    }

    .btn-save {
      background: #28a745;
      color: white;
    }

    .btn-save:hover {
      background: #218838;
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .table-container {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .table-wrapper {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }

    /* Header tabel hijau solid seperti gambar */
    .data-table thead {
      background: #083E40;
    }

    .data-table th {
      padding: 16px 12px;
      color: white;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      letter-spacing: 0.5px;
      white-space: nowrap;
      border-right: 1px solid rgba(255, 255, 255, 0.15);
    }

    .data-table th:last-child {
      border-right: none;
    }

    .data-table td {
      padding: 14px 12px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(8, 62, 64, 0.05);
      border-right: 1px solid #e9ecef;
      font-size: 13px;
      text-align: center;
    }

    .data-table td:last-child {
      border-right: none;
    }

    .data-table tbody tr {
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .data-table tbody tr:hover {
      background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
    }

    .badge-draft {
      background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
      color: white;
    }

    .badge-terkirim {
      background: linear-gradient(135deg, #28a745 0%, #34c759 100%);
      color: white;
    }

    .badge-selesai {
      background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
      color: white;
    }

    /* Action Buttons - Horizontal Layout */
    .action-buttons {
      display: flex;
      gap: 6px;
      justify-content: center;
      align-items: center;
      flex-wrap: nowrap;
    }

    .btn-action {
      width: 36px;
      height: 36px;
      padding: 0;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-edit {
      background: #083E40;
      color: white;
    }

    .btn-edit:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-send {
      background: #083E40;
      color: white;
    }

    .btn-send:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-tracking {
      background: #083E40;
      color: white;
    }

    .btn-tracking:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-delete {
      background: #dc3545;
      color: white;
    }

    .btn-delete:hover {
      background: #c82333;
      color: white;
    }

    .btn-create {
      padding: 12px 24px;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.25);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-create:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(8, 62, 64, 0.35);
      color: white;
    }

    .pagination-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 20px;
      border-top: 1px solid #e9ecef;
      flex-wrap: wrap;
      gap: 16px;
    }

    .per-page-select {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .per-page-select label {
      font-size: 14px;
      color: #495057;
    }

    .per-page-select select {
      padding: 6px 12px;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      font-size: 14px;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-state i {
      font-size: 80px;
      color: #dee2e6;
      margin-bottom: 20px;
    }

    .empty-state h4 {
      color: #6c757d;
      margin-bottom: 10px;
    }

    .empty-state p {
      color: #adb5bd;
      margin-bottom: 20px;
    }

    /* Modal Popup */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      padding: 20px;
    }

    .modal-overlay.show {
      display: flex;
    }

    .modal-content-custom {
      background: white;
      border-radius: 20px;
      max-width: 90%;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
    }

    .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 20px 24px;
      border-radius: 16px 16px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header-custom h4 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .modal-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .modal-body-custom {
      padding: 24px;
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .detail-item {
      background: #f8f9fa;
      padding: 14px;
      border-radius: 10px;
      border-left: 4px solid #083E40;
    }

    .detail-item.full-width {
      grid-column: span 2;
    }

    .detail-label {
      font-size: 11px;
      font-weight: 700;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .detail-value {
      font-size: 14px;
      font-weight: 600;
      color: #212529;
    }

    .detail-value.highlight {
      color: #212529;
      font-size: 18px;
    }

    .modal-footer-custom {
      padding: 16px 24px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    @media (max-width: 768px) {
      .detail-grid {
        grid-template-columns: 1fr;
      }

      .detail-item.full-width {
        grid-column: span 1;
      }

      .action-buttons {
        flex-wrap: wrap;
      }
    }

    /* Document Age Badge */
    .document-age-badge {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 10px 14px;
      border-radius: 12px;
      min-width: 160px;
    }

    .document-age-badge.active {
      background: linear-gradient(135deg, #d4edda 0%, #c8e6c9 100%);
      border-left: 4px solid #28a745;
    }

    .document-age-badge.completed {
      background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
      border-left: 4px solid #6c757d;
    }

    .age-date {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      font-weight: 600;
    }

    .document-age-badge.active .age-date {
      color: #155724;
    }

    .document-age-badge.completed .age-date {
      color: #495057;
    }

    .age-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    .document-age-badge.active .age-dot {
      background: #28a745;
      box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    }

    .document-age-badge.completed .age-dot {
      background: #6c757d;
      animation: none;
    }

    .age-duration {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 500;
    }

    .document-age-badge.active .age-duration {
      color: #155724;
    }

    .document-age-badge.completed .age-duration {
      color: #6c757d;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.5;
      }
    }

    /* Payment Status Badge */
    .payment-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }

    .payment-status-badge.belum-dibayar {
      background: linear-gradient(135deg, #fff3cd 0%, #ffe0b2 100%);
      color: #856404;
      border: 1px solid #ffc107;
    }

    .payment-status-badge.siap-dibayar {
      background: linear-gradient(135deg, #cce5ff 0%, #b3d4fc 100%);
      color: #004085;
      border: 1px solid #007bff;
    }

    .payment-status-badge.sudah-dibayar {
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
      color: #155724;
      border: 1px solid #28a745;
    }

    /* Document Status Badge */
    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }

    /* Belum Dikirim - Grey with shimmer animation */
    .badge-status.badge-draft {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      position: relative;
      overflow: hidden;
    }

    .badge-status.badge-draft::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg,
          transparent,
          rgba(255, 255, 255, 0.3),
          transparent);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% {
        left: -100%;
      }

      100% {
        left: 100%;
      }
    }

    /* Terkirim - Premium dark green with auto shimmer animation */
    .badge-status.badge-success,
    .badge-status.badge-terkirim {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .badge-status.badge-success::before,
    .badge-status.badge-terkirim::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg,
          transparent,
          rgba(255, 255, 255, 0.4),
          transparent);
      animation: shimmer-terkirim 2.5s infinite;
    }

    .badge-status.badge-success:hover,
    .badge-status.badge-terkirim:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(8, 62, 64, 0.4);
      background: linear-gradient(135deg, #0a5f52 0%, #0c7066 100%);
    }

    @keyframes shimmer-terkirim {
      0% {
        left: -100%;
      }

      50% {
        left: 100%;
      }

      100% {
        left: 100%;
      }
    }
  </style>

  <div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2>
          <i class="fa-solid fa-file-lines me-2"></i>
          Daftar Dokumen Bagian {{ $bagianCode }}
        </h2>
        <p class="text-muted mb-0">{{ $bagianName }}</p>
      </div>
      <a href="{{ route('bagian.documents.create') }}" class="btn-create">
        <i class="fa-solid fa-plus"></i>
        Buat Dokumen
      </a>
    </div>


    <!-- Search & Filter -->
    <div class="search-box">
      <form action="{{ route('bagian.documents.index') }}" method="GET" class="search-filter-form">
        <div class="search-input-group">
          <div class="input-group">
            <span class="input-group-text">
              <i class="fa-solid fa-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control" placeholder="Cari nomor agenda, SPP, atau uraian..."
              value="{{ request('search') }}">
          </div>
        </div>

        <select name="tahun" class="btn-year-select">
          <option value="">Semua Tahun</option>
          @php
            $currentYear = date('Y');
            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
              $selected = request('tahun') == $y ? 'selected' : '';
              echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
            }
          @endphp
        </select>

        <select name="status" class="btn-status-select">
          <option value="">Semua Status</option>
          <option value="belum_dikirim" {{ request('status') == 'belum_dikirim' ? 'selected' : '' }}>Belum Dikirim</option>
          <option value="menunggu_approve" {{ request('status') == 'menunggu_approve' ? 'selected' : '' }}>Menunggu Approve
          </option>
          <option value="terkirim" {{ request('status') == 'terkirim' ? 'selected' : '' }}>Terkirim</option>
          <option value="belum_dibayar" {{ request('status') == 'belum_dibayar' ? 'selected' : '' }}>Belum Siap Dibayar
          </option>
          <option value="siap_dibayar" {{ request('status') == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
          <option value="sudah_dibayar" {{ request('status') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
        </select>

        <button type="submit" class="btn-filter">
          <i class="fa-solid fa-filter me-1"></i>Filter
        </button>
        <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
          <i class="fa-solid fa-table-columns me-2"></i>
          Kustomisasi Kolom Tabel
        </button>
      </form>
    </div>

    <!-- Document Table -->
    <div class="table-container">
      @if($dokumens->count() > 0)
        <div class="table-wrapper">
          <table class="data-table">
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
              @foreach($dokumens as $index => $doc)
                      @php
                        $statusLower = strtolower($doc->status ?? '');
                      @endphp
                      <tr onclick="showDocumentDetail({{ json_encode([
                  'id' => $doc->id,
                  'nomor_agenda' => $doc->nomor_agenda,
                  'nomor_spp' => $doc->nomor_spp,
                  'tanggal_spp' => $doc->tanggal_spp ? $doc->tanggal_spp->format('d/m/Y H:i') : '-',
                  'tanggal_masuk' => $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d/m/Y H:i') : '-',
                  'bulan' => $doc->bulan ?? '-',
                  'tahun' => $doc->tahun ?? '-',
                  'nilai_rupiah' => 'Rp. ' . number_format($doc->nilai_rupiah, 0, ',', '.'),
                  'ejaan_nilai_rupiah' => \App\Helpers\TerbilangHelper::terbilang($doc->nilai_rupiah),
                  'uraian_spp' => $doc->uraian_spp ?? '-',
                  'bagian' => $doc->bagian ?? '-',
                  'nama_pengirim' => $doc->nama_pengirim ?? '-',
                  'kebun' => $doc->kebun ?? '-',
                  'no_spk' => $doc->no_spk ?? '-',
                  'tanggal_spk' => $doc->tanggal_spk ? $doc->tanggal_spk->format('d/m/Y') : '-',
                  'tanggal_berakhir_spk' => $doc->tanggal_berakhir_spk ? $doc->tanggal_berakhir_spk->format('d/m/Y') : '-',
                  'no_berita_acara' => $doc->no_berita_acara ?? '-',
                  'tanggal_berita_acara' => $doc->tanggal_berita_acara ? $doc->tanggal_berita_acara->format('d/m/Y') : '-',
                  'no_po' => $doc->NO_PO ?? '-',
                  'no_miro' => $doc->nomor_miro_display ?? '-',
                  'kriteria_cf' => $doc->kategori ?? '-',
                  'sub_kriteria' => $doc->jenis_dokumen ?? '-',
                  'item_sub_kriteria' => $doc->jenis_sub_pekerjaan ?? '-',
                  'jenis_pembayaran' => $doc->jenis_pembayaran ?? '-',
                  'dibayar_kepada' => $doc->dibayarKepadas->pluck('nama_penerima')->join(', ') ?: '-',
                  'status' => ucwords(str_replace('_', ' ', $doc->status ?? 'Belum Dikirim'))
                ]) }})">
                        <td>{{ $dokumens->firstItem() + $index }}</td>
                        @foreach($selectedColumns as $col)
                          <td>
                            @if($col == 'nomor_agenda')
                              <strong style="color: #000000;">{{ $doc->nomor_agenda }}</strong>
                              <br>
                              <small class="text-muted">{{ $doc->bulan ?? '' }} {{ $doc->tahun ?? '' }}</small>
                            @elseif($col == 'nomor_spp')
                              {{ $doc->nomor_spp }}
                            @elseif($col == 'tanggal_masuk')
                              {{ $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d-m-Y H:i') : '-' }}
                            @elseif($col == 'nilai_rupiah')
                              <strong style="color: #000000;">Rp. {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                            @elseif($col == 'status')
                              @php
                                // Simplified status for Bagian view
                                $displayStatus = 'terkirim';
                                $statusClass = 'badge-terkirim';
                                $statusIcon = 'fa-check';
                                $statusText = 'Terkirim';

                                if ($statusLower == 'belum dikirim') {
                                  $displayStatus = 'belum_dikirim';
                                  $statusClass = 'badge-draft';
                                  $statusIcon = 'fa-file-lines';
                                  $statusText = 'Belum Dikirim';
                                } elseif ($statusLower == 'menunggu_approval_keuangan') {
                                  $displayStatus = 'menunggu_approve';
                                  $statusClass = 'badge-warning';
                                  $statusIcon = 'fa-clock';
                                  $statusText = 'Menunggu Approve';
                                }
                              @endphp
                              @if($displayStatus == 'belum_dikirim')
                                <span class="badge-status {{ $statusClass }}">
                                  <i class="fa-solid {{ $statusIcon }}"></i>
                                  <span>{{ $statusText }}</span>
                                </span>
                              @elseif($displayStatus == 'menunggu_approve')
                                <span class="badge-status {{ $statusClass }}"
                                  style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529;">
                                  <i class="fa-solid {{ $statusIcon }}"></i>
                                  <span>{{ $statusText }}</span>
                                </span>
                              @else
                                <span class="badge-status {{ $statusClass }}">
                                  <i class="fa-solid {{ $statusIcon }}"></i>
                                  <span>{{ $statusText }}</span>
                                </span>
                              @endif
                            @elseif($col == 'uraian_spp')
                              <span
                                style="display: block; white-space: normal; word-wrap: break-word; line-height: 1.5; max-width: 300px;">{{ $doc->uraian_spp ?? '-' }}</span>
                            @elseif($col == 'tanggal_spp')
                              {{ $doc->tanggal_spp ? $doc->tanggal_spp->format('d-m-Y') : '-' }}
                            @elseif($col == 'kebun')
                              {{ $doc->kebun ?? '-' }}
                            @elseif($col == 'nama_pengirim')
                              {{ $doc->nama_pengirim ?? '-' }}
                            @elseif($col == 'jenis_pembayaran')
                              {{ $doc->jenis_pembayaran ?? '-' }}
                            @elseif($col == 'umur_dokumen')
                              @php
                                // Determine if document is paid
                                $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);

                                // Calculate age
                                $startDate = $doc->created_at;
                                $endDate = $isPaid && $doc->tanggal_dibayar ? \Carbon\Carbon::parse($doc->tanggal_dibayar) : now();

                                if ($startDate) {
                                  $diff = $startDate->diff($endDate);
                                  $days = $diff->days;
                                  $hours = $diff->h;
                                  $minutes = $diff->i;

                                  $durationParts = [];
                                  if ($days > 0)
                                    $durationParts[] = $days . ' hari';
                                  if ($hours > 0)
                                    $durationParts[] = $hours . ' jam';
                                  if ($minutes > 0 || empty($durationParts))
                                    $durationParts[] = $minutes . ' menit';
                                  $durationText = implode(' ', $durationParts);
                                } else {
                                  $durationText = '-';
                                }
                              @endphp
                              <div class="document-age-badge {{ $isPaid ? 'completed' : 'active' }}">
                                <div class="age-date">
                                  <span class="age-dot"></span>
                                  {{ $startDate ? $startDate->format('d M Y, H:i') : '-' }}
                                </div>
                                <div class="age-duration">
                                  <i class="fa-solid fa-clock"></i>
                                  {{ $durationText }}
                                </div>
                              </div>
                            @elseif($col == 'status_pembayaran')
                              @php
                                // Determine payment status based on document position
                                // Check if already paid
                                $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);

                                // Check if in pembayaran role using current_handler
                                $currentHandlerLower = strtolower($doc->current_handler ?? '');
                                $isInPembayaran = str_contains($currentHandlerLower, 'pembayaran');

                                // Determine payment status change date
                                $statusChangeDate = null;

                                if ($isPaid) {
                                  $paymentStatusClass = 'sudah-dibayar';
                                  $paymentStatusText = 'Sudah Dibayar';
                                  $paymentStatusIcon = 'fa-check-circle';
                                  // Use tanggal_dibayar for paid status
                                  $statusChangeDate = $doc->tanggal_dibayar;
                                } elseif ($isInPembayaran) {
                                  $paymentStatusClass = 'siap-dibayar';
                                  $paymentStatusText = 'Siap Dibayar';
                                  $paymentStatusIcon = 'fa-money-bill-wave';
                                  // Get pembayaran role data for received_at date
                                  $pembayaranRoleData = $doc->getDataForRole('pembayaran');
                                  $statusChangeDate = $pembayaranRoleData?->received_at;
                                } else {
                                  $paymentStatusClass = 'belum-dibayar';
                                  $paymentStatusText = 'Belum Siap Dibayar';
                                  $paymentStatusIcon = 'fa-clock';
                                  // Use sent_at or created_at for initial status
                                  $statusChangeDate = $doc->sent_at ?? $doc->created_at;
                                }
                              @endphp
                              <div class="payment-status-container"
                                style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                <span class="payment-status-badge {{ $paymentStatusClass }}">
                                  <i class="fa-solid {{ $paymentStatusIcon }}"></i>
                                  {{ $paymentStatusText }}
                                </span>
                                @if($statusChangeDate)
                                  <small style="font-size: 10px; color: #6c757d; text-align: center;">
                                    {{ \Carbon\Carbon::parse($statusChangeDate)->format('d M Y, H:i') }}
                                  </small>
                                @endif
                              </div>
                            @else
                              -
                            @endif
                          </td>
                        @endforeach
                        <td onclick="event.stopPropagation()">
                          <div class="action-buttons">
                            @if($statusLower == 'belum dikirim')
                              <a href="{{ route('bagian.documents.edit', $doc) }}" class="btn-action btn-edit" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                              </a>
                              <form id="sendForm-{{ $doc->id }}" action="{{ route('bagian.documents.send-to-Operator', $doc) }}"
                                method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn-action btn-send" title="Kirim"
                                  onclick="showSendModal({{ $doc->id }})">
                                  <i class="fa-solid fa-paper-plane"></i>
                                </button>
                              </form>
                              <form id="deleteForm-{{ $doc->id }}" action="{{ route('bagian.documents.destroy', $doc) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-action btn-delete" title="Hapus"
                                  onclick="showDeleteModal({{ $doc->id }})">
                                  <i class="fa-solid fa-trash"></i>
                                </button>
                              </form>
                            @else
                              <a href="{{ route('bagian.tracking') }}" class="btn-action btn-tracking" title="Tracking">
                                <i class="fa-solid fa-route"></i>
                              </a>
                            @endif
                          </div>
                        </td>
                      </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
          <div class="per-page-select">
            <label>Baris per halaman:</label>
            <select onchange="changePerPage(this.value)">
              <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
              <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
              <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
              <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-muted">
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} hasil
            </span>
          </div>
          <div>
            {{ $dokumens->appends(request()->query())->links() }}
          </div>
        </div>
      @else
        <div class="empty-state">
          <i class="fa-solid fa-folder-open"></i>
          <h4>Belum ada dokumen</h4>
          <p>Buat dokumen pertama Anda sekarang</p>
          <a href="{{ route('bagian.documents.create') }}" class="btn-create">
            <i class="fa-solid fa-plus"></i>
            Buat Dokumen
          </a>
        </div>
      @endif
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-modal">
      <div class="confirm-icon delete-icon">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <h3 class="confirm-title">Hapus Dokumen?</h3>
      <p class="confirm-message">Apakah anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.</p>
      <div class="confirm-actions">
        <button type="button" class="btn-confirm-cancel" onclick="closeDeleteModal()">
          <i class="fa-solid fa-times"></i> Batal
        </button>
        <button type="button" class="btn-confirm-delete" id="confirmDeleteBtn">
          <i class="fa-solid fa-trash"></i> Ya, Hapus
        </button>
      </div>
    </div>
  </div>

  <!-- Send Confirmation Modal -->
  <div id="sendConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-modal send-modal">
      <div class="confirm-icon send-icon">
        <i class="fa-solid fa-paper-plane"></i>
      </div>
      <h3 class="confirm-title">Kirim Dokumen?</h3>
      <p class="confirm-message">Apakah anda yakin dokumen ini dikirim ke Bidang Keuangan dan Akutansi?</p>
      <div class="confirm-actions">
        <button type="button" class="btn-confirm-cancel" onclick="closeSendModal()">
          <i class="fa-solid fa-times"></i> Batal
        </button>
        <button type="button" class="btn-confirm-send" id="confirmSendBtn">
          <i class="fa-solid fa-paper-plane"></i> Ya, Kirim
        </button>
      </div>
    </div>
  </div>

  <!-- Send Success Modal -->
  <div id="sendSuccessModal" class="success-modal-overlay">
    <div class="success-modal">
      <div class="success-icon-container">
        <div class="success-circle">
          <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
          </svg>
        </div>
        <div class="confetti">
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
        </div>
      </div>
      <h2 class="success-title">Berhasil Terkirim!</h2>
      <div class="success-details">
        <div class="success-stat">
          <span class="stat-number">1</span>
          <span class="stat-label">Dokumen</span>
        </div>
        <div class="success-arrow">
          <i class="fa-solid fa-arrow-right"></i>
        </div>
        <div class="success-destination">
          <i class="fa-solid fa-inbox"></i>
          <span>Bidang Keuangan</span>
        </div>
      </div>
      <p class="success-message">
        <i class="fa-solid fa-info-circle"></i>
        Dokumen telah masuk ke <strong>inbox</strong> dan menunggu persetujuan
      </p>
      <button type="button" class="btn-success-close" onclick="closeSuccessAndReload()">
        <i class="fa-solid fa-check"></i> Mengerti
      </button>
    </div>
  </div>

  <!-- Column Customization Modal - Operator Style -->
  <div class="customization-modal" id="columnCustomizationModal">
    <div class="modal-content-custom" style="max-width: 90%; width: 90%;">
      <!-- Header -->
      <div class="modal-header-custom"
        style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; justify-content: space-between;">
        <h3 style="display: flex; align-items: center; gap: 12px; color: #212529; margin: 0;">
          <i class="fa-solid fa-table-columns"></i>
          Kustomisasi Kolom Tabel
        </h3>
        <button class="modal-close" onclick="closeColumnCustomizationModal()"
          style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6c757d;">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body-custom" style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Selection Panel -->
        <div class="selection-panel"
          style="background: #f8f9fa; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef;">
          <div class="panel-title"
            style="font-size: 18px; font-weight: 600; color: #212529; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-check-square"></i>
            Pilih Kolom
          </div>
          <div class="panel-description" style="font-size: 13px; color: #6c757d; margin-bottom: 16px; line-height: 1.6;">
            Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
          </div>
          <div class="column-selection-list" id="columnSelectionList"
            style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; max-height: 200px; overflow-y: auto; padding: 8px; background: white; border-radius: 8px; border: 1px solid #dee2e6;">
            @foreach($availableColumns as $key => $label)
              <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}" data-column="{{ $key }}"
                onclick="toggleColumn(this)">
                <input type="checkbox" class="column-item-checkbox" value="{{ $key }}" {{ in_array($key, $selectedColumns) ? 'checked' : '' }} onclick="event.stopPropagation()">
                <label class="column-item-label"
                  style="cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $label }}</label>
                <span class="column-item-order"
                  style="width: 24px; height: 24px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; {{ in_array($key, $selectedColumns) ? 'opacity: 1;' : 'opacity: 0; transform: scale(0);' }} transition: all 0.2s ease;">
                  {{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}
                </span>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Preview Panel -->
        <div class="preview-panel"
          style="background: #ffffff; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef;">
          <div class="panel-title"
            style="font-size: 18px; font-weight: 600; color: #212529; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-eye"></i>
            Preview Hasil
          </div>
          <div class="panel-description" style="font-size: 13px; color: #6c757d; margin-bottom: 16px; line-height: 1.6;">
            Preview tabel akan menampilkan <span style="color: #28a745; font-weight: 600;">kolom yang Anda pilih</span>
            sesuai urutan.
          </div>
          <div class="preview-container"
            style="overflow-x: auto; background: #f8f9fa; border-radius: 8px; padding: 16px; min-height: 200px;">
            <div id="tablePreview">
              @if(count($selectedColumns) > 0)
                <table class="preview-table"
                  style="width: 100%; min-width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); font-size: 13px;">
                  <thead>
                    <tr>
                      <th
                        style="background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px;">
                        No</th>
                      @foreach($selectedColumns as $col)
                        <th
                          style="background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px;">
                          {{ $availableColumns[$col] ?? $col }}
                        </th>
                      @endforeach
                      <th
                        style="background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px;">
                        Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @for($i = 1; $i <= 5; $i++)
                      <tr style="border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 12px; text-align: center; color: #495057;">{{ $i }}</td>
                        @foreach($selectedColumns as $col)
                          <td style="padding: 12px; text-align: center; color: #495057;">
                            @if($col == 'nomor_agenda')
                              0100{{ $i }}_2026
                            @elseif($col == 'nomor_spp')
                              {{ 200 + $i }}/M/SPP/8/04/2026
                            @elseif($col == 'tanggal_masuk')
                              {{ date('d-m-Y') }}
                            @elseif($col == 'nilai_rupiah')
                              Rp. {{ number_format(1000000 * $i, 0, ',', '.') }}
                            @elseif($col == 'status')
                              <span style="color: #28a745;">✓ Terkirim</span>
                            @else
                              Contoh Data {{ $i }}
                            @endif
                          </td>
                        @endforeach
                        <td style="padding: 12px; text-align: center; color: #495057;">Edit, Kirim</td>
                      </tr>
                    @endfor
                  </tbody>
                </table>
              @else
                <div class="empty-preview" style="text-align: center; padding: 60px 20px; color: #6c757d;">
                  <i class="fa-solid fa-table"
                    style="font-size: 48px; color: #adb5bd; margin-bottom: 16px; display: block;"></i>
                  <p style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">Belum ada kolom yang dipilih</p>
                  <small style="font-size: 14px; color: #868e96;">Silakan pilih minimal satu kolom untuk melihat
                    preview</small>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer-custom"
        style="padding: 20px 40px; border-top: 1px solid #e9ecef; background: #ffffff; display: flex; justify-content: space-between; align-items: center;">
        <div class="selected-count" style="font-size: 15px; color: #495057; font-weight: 500;">
          <strong id="selectedColumnCount" style="color: #28a745; font-size: 18px;">{{ count($selectedColumns) }}</strong>
          kolom dipilih
          @if(count($selectedColumns) > 0)
                  <br><small style="color: #6c757d;">Kolom: {{ implode(', ', array_map(function ($col) use ($availableColumns) {
              return $availableColumns[$col] ?? $col;
            }, $selectedColumns)) }}</small>
          @endif
        </div>
        <div class="modal-actions" style="display: flex; gap: 12px;">
          <button type="button" class="btn-modal btn-cancel" onclick="closeColumnCustomizationModal()">
            <i class="fa-solid fa-times"></i> Batal
          </button>
          <button type="button" class="btn-modal btn-save" id="saveCustomizationBtn" onclick="saveColumnCustomization()">
            <i class="fa-solid fa-save"></i> Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Document Detail Modal - Modern Redesign -->
  <div class="modal-overlay" id="documentDetailModal">
    <div class="modal-content-custom">
      <!-- Hero Header with Status -->
      <div class="modal-header-custom">
        <div class="header-content">
          <div class="header-icon">
            <i class="fa-solid fa-file-invoice"></i>
          </div>
          <div class="header-text">
            <h4>Detail Dokumen Lengkap</h4>
            <span class="doc-id" id="modal-header-agenda">-</span>
          </div>
        </div>
        <div class="header-actions">
          <span class="status-pill" id="modal-header-status">-</span>
          <button class="modal-close" onclick="closeModal()">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="modal-tabs">
        <button class="tab-btn active" onclick="switchTab('info')" data-tab="info">
          <i class="fa-solid fa-info-circle"></i>
          <span>Info Utama</span>
        </button>
        <button class="tab-btn" onclick="switchTab('keuangan')" data-tab="keuangan">
          <i class="fa-solid fa-wallet"></i>
          <span>Keuangan & Vendor</span>
        </button>
        <button class="tab-btn" onclick="switchTab('spk')" data-tab="spk">
          <i class="fa-solid fa-file-contract"></i>
          <span>SPK & Berita Acara</span>
        </button>
      </div>

      <div class="modal-body-custom">
        <!-- Tab: Info Utama -->
        <div class="tab-content active" id="tab-info">
          <!-- Quick Stats Cards -->
          <div class="stats-row">
            <div class="stat-card primary">
              <div class="stat-icon"><i class="fa-solid fa-hashtag"></i></div>
              <div class="stat-info">
                <span class="stat-label">Nomor Agenda</span>
                <span class="stat-value" id="modal-nomor-agenda">-</span>
              </div>
            </div>
            <div class="stat-card success">
              <div class="stat-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
              <div class="stat-info">
                <span class="stat-label">Nilai Rupiah</span>
                <span class="stat-value" id="modal-nilai-rupiah">-</span>
              </div>
            </div>
            <div class="stat-card info">
              <div class="stat-icon"><i class="fa-solid fa-calendar"></i></div>
              <div class="stat-info">
                <span class="stat-label">Periode</span>
                <span class="stat-value" id="modal-periode">-</span>
              </div>
            </div>
          </div>

          <!-- Detail Sections -->
          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-file-alt"></i>
              <h5>Informasi SPP</h5>
            </div>
            <div class="section-grid">
              <div class="info-card">
                <span class="info-label">Nomor SPP</span>
                <span class="info-value" id="modal-nomor-spp">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal SPP</span>
                <span class="info-value" id="modal-tanggal-spp">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal Masuk</span>
                <span class="info-value" id="modal-tanggal-masuk">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Status</span>
                <span class="info-value status-badge" id="modal-status">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-building"></i>
              <h5>Informasi Bagian</h5>
            </div>
            <div class="section-grid cols-3">
              <div class="info-card">
                <span class="info-label">Bagian</span>
                <span class="info-value" id="modal-bagian">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Nama Pengirim</span>
                <span class="info-value" id="modal-nama-pengirim">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Kebun/Unit Kerja</span>
                <span class="info-value" id="modal-kebun">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-align-left"></i>
              <h5>Uraian SPP</h5>
            </div>
            <div class="uraian-box" id="modal-uraian-spp">-</div>
          </div>
        </div>

        <!-- Tab: Keuangan & Vendor -->
        <div class="tab-content" id="tab-keuangan">
          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-coins"></i>
              <h5>Detail Nilai</h5>
            </div>
            <div class="money-display">
              <div class="money-amount" id="modal-nilai-rupiah-2">-</div>
              <div class="money-words" id="modal-ejaan-nilai-rupiah">-</div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-store"></i>
              <h5>Informasi Vendor</h5>
            </div>
            <div class="vendor-card">
              <div class="vendor-icon">
                <i class="fa-solid fa-building"></i>
              </div>
              <div class="vendor-info">
                <span class="vendor-label">Dibayarkan Kepada</span>
                <span class="vendor-name" id="modal-dibayar-kepada">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-tags"></i>
              <h5>Kategori & Klasifikasi</h5>
            </div>
            <div class="section-grid cols-2">
              <div class="info-card highlight">
                <span class="info-label">Kriteria CF</span>
                <span class="info-value tag" id="modal-kriteria-cf">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Sub Kriteria</span>
                <span class="info-value" id="modal-sub-kriteria">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Item Sub Kriteria</span>
                <span class="info-value" id="modal-item-sub-kriteria">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Jenis Pembayaran</span>
                <span class="info-value" id="modal-jenis-pembayaran">-</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab: SPK & Berita Acara -->
        <div class="tab-content" id="tab-spk">
          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-file-signature"></i>
              <h5>Data SPK (Surat Perintah Kerja)</h5>
            </div>
            <div class="section-grid cols-3">
              <div class="info-card">
                <span class="info-label">No SPK</span>
                <span class="info-value mono" id="modal-no-spk">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal SPK</span>
                <span class="info-value" id="modal-tanggal-spk">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal Berakhir SPK</span>
                <span class="info-value" id="modal-tanggal-berakhir-spk">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-clipboard-check"></i>
              <h5>Data Berita Acara</h5>
            </div>
            <div class="section-grid cols-2">
              <div class="info-card">
                <span class="info-label">No Berita Acara</span>
                <span class="info-value mono" id="modal-no-berita-acara">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal Berita Acara</span>
                <span class="info-value" id="modal-tanggal-berita-acara">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-receipt"></i>
              <h5>Data PO & MIRO</h5>
            </div>
            <div class="section-grid cols-2">
              <div class="info-card">
                <span class="info-label">No. PO</span>
                <span class="info-value mono" id="modal-no-po">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">No. Miro/SES</span>
                <span class="info-value mono" id="modal-no-miro">-</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer-custom">
        <button class="btn-footer secondary" onclick="closeModal()">
          <i class="fa-solid fa-times"></i>
          <span>Tutup</span>
        </button>
      </div>
    </div>
  </div>

  <style>
    /* Modern Modal Styles */
    .modal-content-custom {
      background: #ffffff;
      border-radius: 24px;
      max-width: 900px;
      width: 95%;
      max-height: 90vh;
      overflow: hidden;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
      display: flex;
      flex-direction: column;
    }

    .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 24px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 24px 24px 0 0;
    }

    .header-content {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .header-icon {
      width: 56px;
      height: 56px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .header-text h4 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
    }

    .doc-id {
      font-size: 14px;
      opacity: 0.85;
      font-weight: 500;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .status-pill {
      background: rgba(255, 255, 255, 0.2);
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .modal-close {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: scale(1.05);
    }

    /* Tabs */
    .modal-tabs {
      display: flex;
      background: #f8f9fa;
      padding: 12px 28px;
      gap: 8px;
      border-bottom: 1px solid #e9ecef;
    }

    .tab-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
      border: none;
      background: transparent;
      border-radius: 12px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      color: #6c757d;
      transition: all 0.3s ease;
    }

    .tab-btn:hover {
      background: rgba(8, 62, 64, 0.08);
      color: #083E40;
    }

    .tab-btn.active {
      background: #083E40;
      color: white;
      box-shadow: 0 4px 15px rgba(8, 62, 64, 0.3);
    }

    .tab-btn i {
      font-size: 16px;
    }

    /* Modal Body */
    .modal-body-custom {
      padding: 28px;
      overflow-y: auto;
      flex: 1;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Stats Row */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .stat-card {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 20px;
      border-radius: 16px;
      background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
      border: 1px solid #e9ecef;
    }

    .stat-card.primary {
      background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
      border-color: #bbdefb;
    }

    .stat-card.success {
      background: linear-gradient(135deg, #e8f5e9 0%, #f8f9fa 100%);
      border-color: #c8e6c9;
    }

    .stat-card.info {
      background: linear-gradient(135deg, #fff3e0 0%, #f8f9fa 100%);
      border-color: #ffe0b2;
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }

    .stat-card.primary .stat-icon {
      background: #1976d2;
      color: white;
    }

    .stat-card.success .stat-icon {
      background: #388e3c;
      color: white;
    }

    .stat-card.info .stat-icon {
      background: #f57c00;
      color: white;
    }

    .stat-info {
      display: flex;
      flex-direction: column;
    }

    .stat-label {
      font-size: 12px;
      color: #6c757d;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .stat-value {
      font-size: 16px;
      font-weight: 700;
      color: #212529;
    }

    /* Detail Sections */
    .detail-section {
      margin-bottom: 24px;
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e9ecef;
    }

    .section-header i {
      width: 32px;
      height: 32px;
      background: #083E40;
      color: white;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .section-header h5 {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: #212529;
    }

    .section-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .section-grid.cols-3 {
      grid-template-columns: repeat(3, 1fr);
    }

    .section-grid.cols-2 {
      grid-template-columns: repeat(2, 1fr);
    }

    .info-card {
      background: #f8f9fa;
      padding: 16px;
      border-radius: 12px;
      border-left: 4px solid #083E40;
    }

    .info-card.highlight {
      background: linear-gradient(135deg, #e8f5e9 0%, #f8f9fa 100%);
      border-left-color: #28a745;
    }

    .info-label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .info-value {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #212529;
    }

    .info-value.mono {
      font-family: 'Consolas', 'Monaco', monospace;
      background: #e9ecef;
      padding: 4px 8px;
      border-radius: 6px;
      display: inline-block;
    }

    .info-value.tag {
      background: #083E40;
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
    }

    .info-value.status-badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      background: linear-gradient(135deg, #28a745 0%, #34c759 100%);
      color: white;
    }

    /* Uraian Box */
    .uraian-box {
      background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 20px;
      font-size: 14px;
      line-height: 1.7;
      color: #495057;
      min-height: 80px;
    }

    /* Money Display */
    .money-display {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-radius: 16px;
      padding: 28px;
      text-align: center;
      color: white;
    }

    .money-amount {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .money-words {
      font-size: 14px;
      opacity: 0.9;
      font-style: italic;
    }

    /* Vendor Card */
    .vendor-card {
      display: flex;
      align-items: center;
      gap: 20px;
      background: #f8f9fa;
      border-radius: 16px;
      padding: 24px;
      border: 1px solid #e9ecef;
    }

    .vendor-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
    }

    .vendor-info {
      display: flex;
      flex-direction: column;
    }

    .vendor-label {
      font-size: 12px;
      color: #6c757d;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .vendor-name {
      font-size: 18px;
      font-weight: 700;
      color: #212529;
    }

    /* Footer */
    .modal-footer-custom {
      padding: 20px 28px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      background: #f8f9fa;
    }

    .btn-footer {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-footer.secondary {
      background: #6c757d;
      color: white;
    }

    .btn-footer.secondary:hover {
      background: #5a6268;
      transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .stats-row {
        grid-template-columns: 1fr;
      }

      .section-grid,
      .section-grid.cols-2,
      .section-grid.cols-3 {
        grid-template-columns: 1fr;
      }

      .modal-tabs {
        overflow-x: auto;
        padding: 12px 16px;
      }

      .tab-btn span {
        display: none;
      }

      .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .modal-header-custom {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
      }

      .stat-card {
        flex-direction: column;
        text-align: center;
      }
    }

    /* Confirmation Modal Styles */
    .confirm-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .confirm-modal-overlay.show {
      display: flex;
      opacity: 1;
    }

    .confirm-modal {
      background: white;
      border-radius: 16px;
      padding: 32px;
      text-align: center;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from {
        transform: scale(0.9) translateY(-20px);
        opacity: 0;
      }

      to {
        transform: scale(1) translateY(0);
        opacity: 1;
      }
    }

    .confirm-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 28px;
    }

    .delete-icon {
      background: linear-gradient(135deg, #fce4ec 0%, #ffcdd2 100%);
      color: #e53935;
    }

    .send-icon {
      background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
      color: #1976d2;
    }

    .confirm-title {
      font-size: 22px;
      font-weight: 700;
      color: #1f2937;
      margin: 0 0 12px 0;
    }

    .confirm-message {
      font-size: 14px;
      color: #6b7280;
      margin: 0 0 24px 0;
      line-height: 1.5;
    }

    .confirm-actions {
      display: flex;
      gap: 12px;
      justify-content: center;
    }

    .btn-confirm-cancel {
      padding: 12px 24px;
      border: 1px solid #e5e7eb;
      background: white;
      color: #374151;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-confirm-cancel:hover {
      background: #f3f4f6;
      border-color: #d1d5db;
    }

    .btn-confirm-delete {
      padding: 12px 24px;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
    }

    .btn-confirm-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .btn-confirm-send {
      padding: 12px 24px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(8, 62, 64, 0.3);
    }

    .btn-confirm-send:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(8, 62, 64, 0.4);
    }

    /* Success Modal Styles */
    .success-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .success-modal-overlay.show {
      display: flex;
      opacity: 1;
    }

    .success-modal {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border-radius: 20px;
      padding: 40px;
      text-align: center;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .success-icon-container {
      position: relative;
      margin-bottom: 24px;
    }

    .success-circle {
      width: 80px;
      height: 80px;
      margin: 0 auto;
    }

    .checkmark {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: block;
      stroke-width: 2;
      stroke: #10b981;
      stroke-miterlimit: 10;
      box-shadow: inset 0px 0px 0px #10b981;
      animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
    }

    .checkmark-circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 2;
      stroke-miterlimit: 10;
      stroke: #10b981;
      fill: none;
      animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark-check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
      100% {
        stroke-dashoffset: 0;
      }
    }

    @keyframes fill {
      100% {
        box-shadow: inset 0px 0px 0px 30px rgba(16, 185, 129, 0.1);
      }
    }

    @keyframes scale {

      0%,
      100% {
        transform: none;
      }

      50% {
        transform: scale3d(1.1, 1.1, 1);
      }
    }

    .confetti {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }

    .confetti-piece {
      position: absolute;
      width: 10px;
      height: 10px;
      border-radius: 2px;
      animation: confetti-fall 1s ease-out forwards;
      opacity: 0;
    }

    .confetti-piece:nth-child(1) {
      background: #f59e0b;
      animation-delay: 0.2s;
      --tx: -60px;
      --ty: -40px;
      --rot: 180deg;
    }

    .confetti-piece:nth-child(2) {
      background: #10b981;
      animation-delay: 0.3s;
      --tx: 70px;
      --ty: -50px;
      --rot: -200deg;
    }

    .confetti-piece:nth-child(3) {
      background: #3b82f6;
      animation-delay: 0.4s;
      --tx: -50px;
      --ty: 60px;
      --rot: 150deg;
    }

    .confetti-piece:nth-child(4) {
      background: #ec4899;
      animation-delay: 0.5s;
      --tx: 60px;
      --ty: 50px;
      --rot: -180deg;
    }

    .confetti-piece:nth-child(5) {
      background: #8b5cf6;
      animation-delay: 0.6s;
      --tx: -30px;
      --ty: -60px;
      --rot: 220deg;
    }

    .confetti-piece:nth-child(6) {
      background: #ef4444;
      animation-delay: 0.7s;
      --tx: 40px;
      --ty: 70px;
      --rot: -150deg;
    }

    @keyframes confetti-fall {
      0% {
        opacity: 1;
        transform: translate(0, 0) rotate(0deg) scale(1);
      }

      100% {
        opacity: 0;
        transform: translate(var(--tx, 50px), var(--ty, 80px)) rotate(var(--rot, 360deg)) scale(0.5);
      }
    }

    .success-title {
      color: #10b981;
      font-size: 28px;
      font-weight: 700;
      margin: 0 0 24px 0;
    }

    .success-details {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      margin-bottom: 24px;
      padding: 20px;
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
      border-radius: 12px;
      border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .success-stat {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .stat-number {
      font-size: 36px;
      font-weight: 800;
      color: #059669;
      line-height: 1;
    }

    .stat-label {
      font-size: 13px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 4px;
    }

    .success-arrow {
      color: #10b981;
      font-size: 20px;
      animation: arrowPulse 1s ease-in-out infinite;
    }

    @keyframes arrowPulse {

      0%,
      100% {
        transform: translateX(0);
        opacity: 1;
      }

      50% {
        transform: translateX(5px);
        opacity: 0.7;
      }
    }

    .success-destination {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }

    .success-destination i {
      font-size: 24px;
      color: #059669;
    }

    .success-destination span {
      font-size: 14px;
      font-weight: 600;
      color: #374151;
    }

    .success-message {
      color: #6b7280;
      font-size: 14px;
      margin: 0 0 28px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .success-message i {
      color: #3b82f6;
    }

    .btn-success-close {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border: none;
      padding: 14px 40px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }

    .btn-success-close:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
    }
  </style>

  <script>
    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      url.searchParams.delete('page');
      window.location.href = url.toString();
    }

    function showDocumentDetail(doc) {
      // Simplified status mapping for Bagian view
      function getSimplifiedStatus(status) {
        const statusLower = (status || '').toLowerCase();
        if (statusLower === 'belum dikirim') {
          return 'Belum Dikirim';
        } else if (statusLower === 'menunggu_approval_keuangan') {
          return 'Menunggu Approve';
        } else {
          return 'Terkirim';
        }
      }

      const simplifiedStatus = getSimplifiedStatus(doc.status);

      // Header fields
      document.getElementById('modal-header-agenda').textContent = doc.nomor_agenda || '-';
      document.getElementById('modal-header-status').textContent = simplifiedStatus;

      // Tab Info Utama
      document.getElementById('modal-nomor-agenda').textContent = doc.nomor_agenda || '-';
      document.getElementById('modal-status').textContent = simplifiedStatus;
      document.getElementById('modal-nomor-spp').textContent = doc.nomor_spp || '-';
      document.getElementById('modal-tanggal-spp').textContent = doc.tanggal_spp || '-';
      document.getElementById('modal-periode').textContent = (doc.bulan || '-') + ' ' + (doc.tahun || '');
      document.getElementById('modal-tanggal-masuk').textContent = doc.tanggal_masuk || '-';
      document.getElementById('modal-nilai-rupiah').textContent = doc.nilai_rupiah || '-';
      document.getElementById('modal-bagian').textContent = doc.bagian || '-';
      document.getElementById('modal-nama-pengirim').textContent = doc.nama_pengirim || '-';
      document.getElementById('modal-kebun').textContent = doc.kebun || '-';
      document.getElementById('modal-uraian-spp').textContent = doc.uraian_spp || '-';

      // Tab Keuangan & Vendor
      document.getElementById('modal-nilai-rupiah-2').textContent = doc.nilai_rupiah || '-';
      document.getElementById('modal-ejaan-nilai-rupiah').textContent = doc.ejaan_nilai_rupiah || '-';
      document.getElementById('modal-dibayar-kepada').textContent = doc.dibayar_kepada || '-';
      document.getElementById('modal-kriteria-cf').textContent = doc.kriteria_cf || '-';
      document.getElementById('modal-sub-kriteria').textContent = doc.sub_kriteria || '-';
      document.getElementById('modal-item-sub-kriteria').textContent = doc.item_sub_kriteria || '-';
      document.getElementById('modal-jenis-pembayaran').textContent = doc.jenis_pembayaran || '-';

      // Tab SPK & Berita Acara
      document.getElementById('modal-no-spk').textContent = doc.no_spk || '-';
      document.getElementById('modal-tanggal-spk').textContent = doc.tanggal_spk || '-';
      document.getElementById('modal-tanggal-berakhir-spk').textContent = doc.tanggal_berakhir_spk || '-';
      document.getElementById('modal-no-berita-acara').textContent = doc.no_berita_acara || '-';
      document.getElementById('modal-tanggal-berita-acara').textContent = doc.tanggal_berita_acara || '-';
      document.getElementById('modal-no-po').textContent = doc.no_po || '-';
      document.getElementById('modal-no-miro').textContent = doc.no_miro || '-';

      // Reset to first tab
      switchTab('info');

      document.getElementById('documentDetailModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function switchTab(tabName) {
      // Remove active from all tabs and contents
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

      // Add active to selected
      document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
      document.getElementById(`tab-${tabName}`).classList.add('active');
    }

    function closeModal() {
      document.getElementById('documentDetailModal').classList.remove('show');
      document.body.style.overflow = '';
    }

    // Close modal on overlay click
    document.getElementById('documentDetailModal').addEventListener('click', function (e) {
      if (e.target === this) {
        closeModal();
      }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
        closeSendModal();
      }
    });

    // ============ DELETE MODAL FUNCTIONS ============
    let deleteFormId = null;

    function showDeleteModal(docId) {
      deleteFormId = docId;
      document.getElementById('deleteConfirmModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
      document.getElementById('deleteConfirmModal').classList.remove('show');
      document.body.style.overflow = '';
      deleteFormId = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
      if (deleteFormId) {
        document.getElementById('deleteForm-' + deleteFormId).submit();
      }
    });

    // Close on overlay click
    document.getElementById('deleteConfirmModal').addEventListener('click', function (e) {
      if (e.target === this) closeDeleteModal();
    });

    // ============ SEND MODAL FUNCTIONS ============
    let sendFormId = null;

    function showSendModal(docId) {
      sendFormId = docId;
      document.getElementById('sendConfirmModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeSendModal() {
      document.getElementById('sendConfirmModal').classList.remove('show');
      document.body.style.overflow = '';
      sendFormId = null;
    }

    document.getElementById('confirmSendBtn').addEventListener('click', function () {
      if (sendFormId) {
        const form = document.getElementById('sendForm-' + sendFormId);
        const formData = new FormData(form);

        // Show loading state
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
        this.disabled = true;

        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => {
            closeSendModal();
            // Reset button
            this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Ya, Kirim';
            this.disabled = false;

            // Show success modal
            document.getElementById('sendSuccessModal').classList.add('show');
            document.body.style.overflow = 'hidden';
          })
          .catch(error => {
            console.error('Error:', error);
            closeSendModal();
            // Fallback to form submit on error
            form.submit();
          });
      }
    });

    // Close on overlay click
    document.getElementById('sendConfirmModal').addEventListener('click', function (e) {
      if (e.target === this) closeSendModal();
    });

    // ============ SUCCESS MODAL FUNCTIONS ============
    function closeSuccessAndReload() {
      document.getElementById('sendSuccessModal').classList.remove('show');
      document.body.style.overflow = '';
      window.location.reload();
    }

    // Close on overlay click
    document.getElementById('sendSuccessModal').addEventListener('click', function (e) {
      if (e.target === this) closeSuccessAndReload();
    });

    // ============ COLUMN CUSTOMIZATION MODAL FUNCTIONS ============
    let selectedColumnsOrder = [];

    function initializeColumnOrder() {
      selectedColumnsOrder = [];
      document.querySelectorAll('#columnCustomizationModal .column-item.selected').forEach((item) => {
        selectedColumnsOrder.push(item.dataset.column);
      });
      updateColumnOrderBadges();
      updateSelectedCount();
    }

    function openColumnCustomizationModal() {
      document.getElementById('columnCustomizationModal').classList.add('show');
      document.body.style.overflow = 'hidden';
      initializeColumnOrder();
    }

    function closeColumnCustomizationModal() {
      document.getElementById('columnCustomizationModal').classList.remove('show');
      document.body.style.overflow = '';
    }

    function toggleColumn(columnElement) {
      const columnKey = columnElement.dataset.column;
      const checkbox = columnElement.querySelector('.column-item-checkbox');
      const isChecked = checkbox.checked;

      if (!isChecked) {
        // Add to selection
        if (!selectedColumnsOrder.includes(columnKey)) {
          selectedColumnsOrder.push(columnKey);
        }
        checkbox.checked = true;
        columnElement.classList.add('selected');
      } else {
        // Remove from selection
        selectedColumnsOrder = selectedColumnsOrder.filter(key => key !== columnKey);
        checkbox.checked = false;
        columnElement.classList.remove('selected');
      }

      updateColumnOrderBadges();
      updateSelectedCount();
    }

    function updateColumnOrderBadges() {
      document.querySelectorAll('#columnCustomizationModal .column-item').forEach(item => {
        const columnKey = item.dataset.column;
        const orderBadge = item.querySelector('.column-item-order');
        if (orderBadge) {
          const orderIndex = selectedColumnsOrder.indexOf(columnKey);
          if (orderIndex !== -1) {
            orderBadge.textContent = orderIndex + 1;
            orderBadge.style.opacity = '1';
            orderBadge.style.transform = 'scale(1)';
          } else {
            orderBadge.textContent = '';
            orderBadge.style.opacity = '0';
            orderBadge.style.transform = 'scale(0)';
          }
        }
      });
    }

    function updateSelectedCount() {
      const countEl = document.getElementById('selectedColumnCount');
      if (countEl) {
        countEl.textContent = selectedColumnsOrder.length;
      }
    }

    function saveColumnCustomization() {
      if (selectedColumnsOrder.length === 0) {
        alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
        return;
      }

      // Get the filter form
      const filterForm = document.querySelector('.search-filter-form');

      // Remove any existing columns[] hidden inputs
      filterForm.querySelectorAll('input[name="columns[]"]').forEach(input => input.remove());

      // Add hidden inputs for selected columns
      selectedColumnsOrder.forEach(columnKey => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'columns[]';
        hiddenInput.value = columnKey;
        filterForm.appendChild(hiddenInput);
      });

      // Close modal and submit form
      closeColumnCustomizationModal();
      filterForm.submit();
    }

    // Close column customization modal on overlay click
    document.getElementById('columnCustomizationModal').addEventListener('click', function (e) {
      if (e.target === this) closeColumnCustomizationModal();
    });

    // Close modals on Escape key (add column customization)
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeColumnCustomizationModal();
      }
    });
  </script>

@endsection
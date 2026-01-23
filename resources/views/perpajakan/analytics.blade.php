@extends('layouts/app')
@section('content')

  @php
    use Illuminate\Support\Str;
  @endphp

  <style>
    /* Title Styles */
    h2 {
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 30px;
      font-weight: 700;
    }

    /* Filter Section */
    .filter-year-section {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 24px;
      border-radius: 16px;
      margin-bottom: 24px;
      box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(64, 145, 108, 0.05);
      border: 1px solid rgba(26, 77, 62, 0.08);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
    }

    .filter-wrapper {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .filter-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .filter-group label {
      font-size: 14px;
      font-weight: 600;
      color: #1a4d3e;
      margin: 0;
    }

    .filter-group select {
      padding: 10px 16px;
      border: 2px solid rgba(26, 77, 62, 0.15);
      border-radius: 10px;
      background: white;
      color: #1a4d3e;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 150px;
    }

    .filter-group select:hover {
      border-color: #40916c;
      box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.1);
    }

    .filter-group select:focus {
      outline: none;
      border-color: #40916c;
      box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.1);
    }

    /* Year Filter Button */
    .year-filter-btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      background: linear-gradient(135deg, #1a4d3e 0%, #40916c 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(26, 77, 62, 0.3);
    }

    .year-filter-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26, 77, 62, 0.4);
    }

    .year-filter-btn i {
      font-size: 16px;
    }

    /* Year Filter Modal Styles */
    .year-filter-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      backdrop-filter: blur(4px);
    }

    .year-filter-modal-overlay.active {
      display: flex;
    }

    .year-filter-modal {
      background: white;
      border-radius: 16px;
      width: 100%;
      max-width: 480px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: modalSlideIn 0.3s ease;
      overflow: hidden;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .year-filter-modal-header {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      padding: 20px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .year-filter-modal-header h5 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .year-filter-modal-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .year-filter-modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .year-filter-modal-body {
      padding: 24px;
    }

    .filter-type-section {
      margin-bottom: 24px;
    }

    .filter-type-section h6 {
      font-size: 14px;
      font-weight: 700;
      color: #1a4d3e;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .filter-type-options {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .filter-type-option {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .filter-type-option:hover {
      background: #e9f5f0;
      border-color: #1a4d3e;
    }

    .filter-type-option.selected {
      background: linear-gradient(135deg, #e9f5f0 0%, #d4ebe4 100%);
      border-color: #1a4d3e;
    }

    .filter-type-option input[type="radio"] {
      margin-right: 12px;
      accent-color: #1a4d3e;
      transform: scale(1.2);
    }

    .filter-type-option label {
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      flex: 1;
    }

    .filter-type-option label strong {
      display: block;
      font-size: 14px;
      color: #333;
      margin-bottom: 2px;
    }

    .filter-type-option label small,
    .filter-type-option small {
      color: #6c757d;
      font-size: 12px;
    }

    .year-selection-section h6 {
      font-size: 14px;
      font-weight: 700;
      color: #1a4d3e;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .year-buttons-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
    }

    .year-btn {
      padding: 14px 16px;
      border: 2px solid #e9ecef;
      background: #f8f9fa;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      color: #333;
    }

    .year-btn:hover {
      background: #e9f5f0;
      border-color: #1a4d3e;
    }

    .year-btn.selected {
      background: linear-gradient(135deg, #1a4d3e 0%, #0a3d2e 100%);
      border-color: #1a4d3e;
      color: white;
    }

    .year-btn.all-years {
      grid-column: span 4;
      background: linear-gradient(135deg, #083E40 0%, #0a5a5c 100%);
      color: white;
      border-color: #083E40;
    }

    .year-btn.all-years:hover {
      opacity: 0.9;
    }

    .year-btn.all-years.selected {
      background: linear-gradient(135deg, #1a4d3e 0%, #0a3d2e 100%);
      border-color: #1a4d3e;
    }

    .year-filter-modal-footer {
      padding: 16px 24px;
      background: #f8f9fa;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }

    .btn-reset-filter {
      padding: 10px 20px;
      background: #fff;
      border: 2px solid #dc3545;
      color: #dc3545;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-reset-filter:hover {
      background: #dc3545;
      color: white;
    }

    .btn-apply-filter {
      padding: 10px 24px;
      background: linear-gradient(135deg, #1a4d3e 0%, #0a3d2e 100%);
      border: none;
      color: white;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-apply-filter:hover {
      background: linear-gradient(135deg, #0a3d2e 0%, #083020 100%);
      transform: translateY(-1px);
    }

    /* Big Summary Card */
    .big-summary-card {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
      border-radius: 20px;
      padding: 40px;
      margin-bottom: 30px;
      box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
      color: white;
      position: relative;
      overflow: hidden;
    }

    .big-summary-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      animation: pulse 4s ease-in-out infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 0.5;
      }

      50% {
        transform: scale(1.1);
        opacity: 0.8;
      }
    }

    .big-summary-content {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
    }

    .summary-item {
      text-align: center;
    }

    .summary-label {
      font-size: 14px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      opacity: 0.9;
      margin-bottom: 12px;
    }

    .summary-value {
      font-size: 48px;
      font-weight: 800;
      line-height: 1.2;
      margin-bottom: 8px;
    }

    .summary-description {
      font-size: 13px;
      opacity: 0.8;
    }

    /* Monthly Grid Section */
    .monthly-grid-section {
      margin-bottom: 30px;
    }

    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: #1a4d3e;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 3px solid #40916c;
    }

    .month-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px;
      margin-bottom: 20px;
    }

    .month-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 4px 20px rgba(26, 77, 62, 0.08), 0 2px 8px rgba(64, 145, 108, 0.05);
      border: 2px solid rgba(26, 77, 62, 0.1);
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .month-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #1a4d3e 0%, #40916c 100%);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .month-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 32px rgba(26, 77, 62, 0.15), 0 4px 16px rgba(64, 145, 108, 0.1);
      border-color: #40916c;
    }

    .month-card:hover::before {
      transform: scaleX(1);
    }

    .month-card.active {
      background: linear-gradient(135deg, #40916c 0%, #1a4d3e 100%);
      color: white;
      border-color: #40916c;
      box-shadow: 0 8px 32px rgba(64, 145, 108, 0.3), 0 4px 16px rgba(26, 77, 62, 0.2);
    }

    .month-card.active::before {
      transform: scaleX(1);
      background: white;
    }

    .month-card.active .month-name,
    .month-card.active .month-count,
    .month-card.active .month-total {
      color: white;
    }

    .month-name {
      font-size: 16px;
      font-weight: 700;
      color: #1a4d3e;
      margin-bottom: 12px;
    }

    .month-count {
      font-size: 24px;
      font-weight: 800;
      color: #1a4d3e;
      margin-bottom: 8px;
    }

    .month-count-label {
      font-size: 11px;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .month-total {
      font-size: 14px;
      font-weight: 600;
      color: #40916c;
      margin-top: 8px;
    }

    .month-card.active .month-count-label,
    .month-card.active .month-total {
      color: rgba(255, 255, 255, 0.9);
    }

    .show-all-months-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    .show-all-months-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(26, 77, 62, 0.3);
    }

    /* Table Styles */
    .table-container {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 24px;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(64, 145, 108, 0.05);
      border: 1px solid rgba(26, 77, 62, 0.08);
      margin-top: 30px;
      overflow-x: auto;
      overflow-y: visible;
      width: 100%;
      max-width: 100%;
      position: relative;
      scrollbar-gutter: stable;
    }

    .table-container h6 {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 2px solid rgba(26, 77, 62, 0.1);
    }

    .table-container h6 span {
      background: linear-gradient(135deg, #1a4d3e 0%, #40916c 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
      font-size: 18px;
    }

    .table {
      margin-bottom: 0;
      width: 100%;
      min-width: 1200px;
      border-collapse: separate;
      border-spacing: 0;
      table-layout: auto;
    }

    .table thead {
      background: #1a4d3e !important;
    }

    .table thead th {
      background: #1a4d3e !important;
      color: white !important;
      font-weight: 600 !important;
      font-size: 14px !important;
      padding: 16px 12px !important;
      border: none !important;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }

    .table tbody tr:hover {
      background: linear-gradient(90deg, rgba(64, 145, 108, 0.05) 0%, transparent 100%);
      border-left: 3px solid #40916c;
      transform: scale(1.001);
    }

    .table tbody td {
      padding: 14px 12px;
      font-size: 13px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(26, 77, 62, 0.05);
    }

    /* Table Scroll Container - Horizontal Scrollbar Only (Always Visible) */
    .table-responsive {
      overflow-x: scroll !important;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
      scrollbar-color: #cbd5e1 #f1f5f9;
      position: relative;
      width: 100%;
      max-width: 100%;
      padding-bottom: 5px;
      margin-bottom: 5px;
    }

    /* Horizontal Scrollbar Styling - Webkit browsers */
    .table-responsive::-webkit-scrollbar {
      height: 16px !important;
      width: 0;
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    .table-responsive::-webkit-scrollbar-track:horizontal {
      background: #f1f5f9 !important;
      border-radius: 8px;
      margin: 0 10px;
      border: 1px solid #e2e8f0;
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    .table-responsive::-webkit-scrollbar-thumb:horizontal {
      background: #cbd5e1 !important;
      border-radius: 8px;
      border: 2px solid #f1f5f9;
      min-height: 16px;
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    .table-responsive::-webkit-scrollbar-thumb:horizontal:hover {
      background: #94a3b8 !important;
    }

    .table-responsive::-webkit-scrollbar-thumb:horizontal:active {
      background: #64748b !important;
    }

    .table-responsive:not(:hover)::-webkit-scrollbar {
      height: 16px !important;
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    .table-responsive:not(:hover)::-webkit-scrollbar-track:horizontal {
      background: #f1f5f9 !important;
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    .table-responsive:not(:hover)::-webkit-scrollbar-thumb:horizontal {
      background: #cbd5e1 !important;
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6c757d;
    }

    .empty-state i {
      font-size: 64px;
      color: #ddd;
      margin-bottom: 20px;
    }

    .empty-state h5 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 8px;
      color: #6c757d;
    }

    .empty-state p {
      font-size: 14px;
      color: #999;
    }

    .select-text {
      cursor: text;
      user-select: text;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .big-summary-content {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .summary-value {
        font-size: 36px;
      }

      .month-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
      }

      .filter-year-section {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-wrapper {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-group {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-group select {
        width: 100%;
      }
    }
  </style>

  <h2>Analitik Dokumen</h2>

  <!-- Filter Section -->
  <div class="filter-year-section">
    <div class="filter-wrapper">
      <div class="filter-group">
        <input type="hidden" id="yearSelect" value="{{ $selectedYear }}">
        <input type="hidden" id="yearFilterType" value="{{ $yearFilterType ?? 'tanggal_spp' }}">
        <button type="button" class="year-filter-btn" onclick="openYearFilterModal()">
          <i class="fa-solid fa-calendar-alt"></i>
          <span>Tahun: {{ $selectedYear }}</span>
          <span style="font-size: 11px; opacity: 0.8;">
            @if(($yearFilterType ?? 'tanggal_spp') == 'tanggal_spp')
              (Tgl SPP)
            @elseif(($yearFilterType ?? 'tanggal_spp') == 'tanggal_masuk')
              (Tgl Masuk)
            @else
              (No. SPP)
            @endif
          </span>
          <i class="fa-solid fa-chevron-down" style="font-size: 12px;"></i>
        </button>
      </div>
      <div class="filter-group">
        <label for="bagianSelect">Pilih Bagian:</label>
        <select id="bagianSelect" onchange="changeFilter()">
          <option value="">Semua Bagian ({{ $yearlySummary['total_dokumen'] ?? 0 }} dokumen)</option>
          @foreach($bagianList as $code => $name)
            <option value="{{ $code }}" {{ $selectedBagian == $code ? 'selected' : '' }}>
              {{ $name }} ({{ $bagianCounts[$code] ?? 0 }} dokumen)
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <div style="font-size: 14px; color: #6c757d; font-weight: 500;">
      <i class="fa-solid fa-calendar-alt me-2"></i>
      Data untuk tahun <strong>{{ $selectedYear }}</strong>
      @if($selectedBagian)
        - Bagian <strong>{{ $bagianList[$selectedBagian] ?? '' }}</strong>
      @endif
      <span style="margin-left: 8px; font-size: 12px; color: #40916c;">
        @if(($yearFilterType ?? 'tanggal_spp') == 'tanggal_spp')
          (berdasarkan Tanggal SPP)
        @elseif(($yearFilterType ?? 'tanggal_spp') == 'tanggal_masuk')
          (berdasarkan Tanggal Masuk)
        @else
          (berdasarkan Nomor SPP)
        @endif
      </span>
    </div>
  </div>

  <!-- Big Summary Card -->
  <div class="big-summary-card">
    <div class="big-summary-content">
      <div class="summary-item">
        <div class="summary-label">Total Nominal</div>
        <div class="summary-value">Rp {{ number_format($yearlySummary['total_nominal'] ?? 0, 0, ',', '.') }}</div>
        <div class="summary-description">Total nilai dokumen tahun {{ $selectedYear }}</div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Total Jumlah Dokumen</div>
        <div class="summary-value">{{ number_format($yearlySummary['total_dokumen'] ?? 0, 0, ',', '.') }}</div>
        <div class="summary-description">Total dokumen tahun {{ $selectedYear }}</div>
      </div>
    </div>
  </div>

  <!-- Monthly Grid Section -->
  <div class="monthly-grid-section">
    <div class="section-title">
      <i class="fa-solid fa-calendar-grid me-2"></i>
      Ringkasan Bulanan
    </div>
    <div class="month-grid" id="monthGrid">
      @for($month = 1; $month <= 12; $month++)
        @php
          $monthData = $monthlyStats[$month] ?? ['name' => '', 'count' => 0, 'total_nominal' => 0];
        @endphp
        <div class="month-card {{ $selectedMonth == $month ? 'active' : '' }}" onclick="filterByMonth({{ $month }})"
          data-month="{{ $month }}">
          <div class="month-name">{{ $monthData['name'] }}</div>
          <div class="month-count-label">Jumlah Dokumen</div>
          <div class="month-count">{{ number_format($monthData['count'], 0, ',', '.') }}</div>
          <div class="month-total">Rp {{ number_format($monthData['total_nominal'], 0, ',', '.') }}</div>
        </div>
      @endfor
    </div>
    @if($selectedMonth)
      <button class="show-all-months-btn" onclick="showAllMonths()">
        <i class="fa-solid fa-list"></i>
        Tampilkan Semua Bulan
      </button>
    @endif
  </div>

  <!-- Table Section -->
  <div class="table-container">
    <h6>
      <span>
        <i class="fa-solid fa-table me-2"></i>
        @if($selectedMonth)
          Daftar Dokumen - {{ $monthlyStats[$selectedMonth]['name'] ?? '' }} {{ $selectedYear }}
        @else
          Daftar Dokumen - Tahun {{ $selectedYear }}
        @endif
        @if($selectedBagian)
          - Bagian {{ $bagianList[$selectedBagian] ?? '' }}
        @endif
      </span>
      <span style="font-size: 13px; color: #6c757d; font-weight: 500;">
        Total: {{ $dokumens->total() }} dokumen
      </span>
    </h6>
    <div class="table-responsive scrollbar-visible">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Bulan</th>
            <th>Tahun</th>
            <th>Tanggal Masuk</th>
            <th>Nomor SPP</th>
            <th>Uraian SPP</th>
            <th>Nilai Rupiah</th>
            <th>Bagian</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="dokumenTableBody">
          @forelse($dokumens as $index => $dokumen)
            <tr onclick="openViewDocumentModal({{ $dokumen->id }})" style="cursor: pointer;">
              <td style="text-align: center;">{{ ($dokumens->currentPage() - 1) * $dokumens->perPage() + $index + 1 }}</td>
              <td class="select-text"><strong>{{ $dokumen->nomor_agenda ?? '-' }}</strong></td>
              <td>
                <span style="font-weight: 600; color: #1a4d3e;">
                  {{ $dokumen->bulan ?? '-' }}
                </span>
              </td>
              <td>
                <span style="font-weight: 600; color: #1a4d3e;">
                  {{ $dokumen->tahun ?? '-' }}
                </span>
              </td>
              <td class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y') : '-' }}</td>
              <td class="select-text">{{ $dokumen->nomor_spp ?? '-' }}</td>
              <td class="select-text">{{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}</td>
              <td class="select-text"><strong>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong></td>
              <td>
                @if($dokumen->bagian)
                  @php
                    $bagianColors = [
                      'AKN' => 'background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);', // Cyan
                      'DPM' => 'background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);', // Blue
                      'KPL' => 'background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);', // Purple
                      'PMO' => 'background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);', // Amber
                      'SDM' => 'background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);', // Pink
                      'SKH' => 'background: linear-gradient(135deg, #10b981 0%, #059669 100%);', // Emerald
                      'TAN' => 'background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);', // Teal
                      'TEP' => 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);', // Red
                    ];
                    $bagianStyle = $bagianColors[strtoupper($dokumen->bagian)] ?? 'background: linear-gradient(135deg, #64748b 0%, #475569 100%);';
                  @endphp
                  <span class="badge"
                    style="{{ $bagianStyle }} color: white; padding: 4px 12px; border-radius: 12px; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $dokumen->bagian }}
                  </span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @php
                  // Use status_perpajakan for perpajakan-specific status
                  $statusPerpajakan = $dokumen->status_perpajakan ?? '';

                  if ($statusPerpajakan == 'sedang_diproses' || !empty($dokumen->deadline_at)) {
                    // Has deadline or is being processed
                    $statusDisplay = 'Sedang Diproses';
                  } elseif ($statusPerpajakan == 'selesai') {
                    $statusDisplay = 'Selesai';
                  } elseif ($dokumen->status == 'sent_to_akutansi') {
                    $statusDisplay = 'Terkirim ke Akutansi';
                  } elseif ($dokumen->status == 'sent_to_perpajakan' && is_null($dokumen->deadline_at) && empty($statusPerpajakan)) {
                    // Only show Terkunci if no deadline AND no status_perpajakan set
                    $statusDisplay = 'Terkunci';
                  } else {
                    $statusDisplay = !empty($statusPerpajakan) ? ucfirst(str_replace('_', ' ', $statusPerpajakan)) : ucfirst(str_replace('_', ' ', $dokumen->status ?? ''));
                  }
                @endphp
                @if($statusDisplay == 'Terkunci')
                  <span class="badge"
                    style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                    🔒 Terkunci
                  </span>
                @elseif($statusDisplay == 'Sedang Diproses')
                  <span class="badge"
                    style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                    ⏳ Sedang Diproses
                  </span>
                @elseif($statusDisplay == 'Selesai')
                  <span class="badge"
                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                    ✓ Selesai
                  </span>
                @elseif($statusDisplay == 'Terkirim ke Akutansi')
                  <span class="badge"
                    style="background: linear-gradient(135deg, #1a4d3e 0%, #40916c 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                    📤 Terkirim ke Akutansi
                  </span>
                @else
                  <span class="badge"
                    style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                    {{ $statusDisplay }}
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <h5>Belum ada dokumen</h5>
                <p>Tidak ada dokumen untuk periode yang dipilih</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @include('partials.pagination-enhanced', ['paginator' => $dokumens])
  </div>

  <!-- Document Detail Modal -->
  @include('partials.document-detail-modal', ['detailRoute' => 'documents.perpajakan.detail', 'editRoute' => 'documents.perpajakan.edit'])

  <!-- Year Filter Modal -->
  <div class="year-filter-modal-overlay" id="yearFilterModalOverlay" onclick="closeYearFilterModal(event)">
    <div class="year-filter-modal" onclick="event.stopPropagation()">
      <div class="year-filter-modal-header">
        <h5>
          <i class="fa-solid fa-calendar-alt"></i>
          Filter Tahun
        </h5>
        <button type="button" class="year-filter-modal-close" onclick="closeYearFilterModal()">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
      <div class="year-filter-modal-body">
        <!-- Filter Type Selection -->
        <div class="filter-type-section">
          <h6><i class="fa-solid fa-filter me-2"></i>Filter Berdasarkan</h6>
          <div class="filter-type-options">
            <div class="filter-type-option {{ ($yearFilterType ?? 'tanggal_spp') == 'tanggal_spp' ? 'selected' : '' }}"
              onclick="selectFilterType('tanggal_spp', this)">
              <input type="radio" name="modal_filter_type" value="tanggal_spp" {{ ($yearFilterType ?? 'tanggal_spp') == 'tanggal_spp' ? 'checked' : '' }}>
              <label>
                <strong>Tanggal SPP</strong>
                <small class="d-block">Tahun dari kolom Tanggal SPP</small>
              </label>
            </div>
            <div class="filter-type-option {{ ($yearFilterType ?? 'tanggal_spp') == 'tanggal_masuk' ? 'selected' : '' }}"
              onclick="selectFilterType('tanggal_masuk', this)">
              <input type="radio" name="modal_filter_type" value="tanggal_masuk" {{ ($yearFilterType ?? 'tanggal_spp') == 'tanggal_masuk' ? 'checked' : '' }}>
              <label>
                <strong>Tanggal Masuk</strong>
                <small class="d-block">Tahun dari timestamp dokumen masuk</small>
              </label>
            </div>
            <div class="filter-type-option {{ ($yearFilterType ?? 'tanggal_spp') == 'nomor_spp' ? 'selected' : '' }}"
              onclick="selectFilterType('nomor_spp', this)">
              <input type="radio" name="modal_filter_type" value="nomor_spp" {{ ($yearFilterType ?? 'tanggal_spp') == 'nomor_spp' ? 'checked' : '' }}>
              <label>
                <strong>Tahun di Nomor SPP</strong>
                <small class="d-block">Ekstrak tahun dari format nomor SPP (contoh: 192/M/SPP/14/03/2024)</small>
              </label>
            </div>
          </div>
        </div>

        <!-- Year Selection -->
        <div class="year-selection-section">
          <h6><i class="fa-solid fa-calendar me-2"></i>Pilih Tahun</h6>
          <div class="year-buttons-grid">
            <button type="button" class="year-btn all-years {{ !$selectedYear ? 'selected' : '' }}"
              onclick="selectYear('{{ date('Y') }}', this)">
              Semua Tahun
            </button>
            @for($y = 2024; $y <= 2030; $y++)
              <button type="button" class="year-btn {{ $selectedYear == $y ? 'selected' : '' }}"
                onclick="selectYear('{{ $y }}', this)">{{ $y }}</button>
            @endfor
          </div>
        </div>
      </div>
      <div class="year-filter-modal-footer">
        <button type="button" class="btn-reset-filter" onclick="resetYearFilter()">
          <i class="fa-solid fa-rotate-left me-2"></i>Reset
        </button>
        <button type="button" class="btn-apply-filter" onclick="applyYearFilter()">
          <i class="fa-solid fa-check me-2"></i>Terapkan Filter
        </button>
      </div>
    </div>
  </div>

  <script>
    let selectedFilterType = '{{ $yearFilterType ?? "tanggal_spp" }}';
    let selectedYear = '{{ $selectedYear }}';

    function openYearFilterModal() {
      document.getElementById('yearFilterModalOverlay').classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeYearFilterModal(event) {
      if (event && event.target !== event.currentTarget) return;
      document.getElementById('yearFilterModalOverlay').classList.remove('active');
      document.body.style.overflow = '';
    }

    function selectFilterType(type, element) {
      selectedFilterType = type;

      // Update radio buttons
      document.querySelectorAll('.filter-type-option input[type="radio"]').forEach(radio => {
        radio.checked = radio.value === type;
      });

      // Update selected class
      document.querySelectorAll('.filter-type-option').forEach(el => el.classList.remove('selected'));
      if (element) {
        element.classList.add('selected');
      }
    }

    function selectYear(year, element) {
      selectedYear = year;
      document.querySelectorAll('.year-btn').forEach(el => el.classList.remove('selected'));
      if (element) {
        element.classList.add('selected');
      }
    }

    function resetYearFilter() {
      selectedFilterType = 'tanggal_spp';
      selectedYear = '{{ date("Y") }}';

      // Reset radio buttons
      document.querySelectorAll('.filter-type-option').forEach(el => el.classList.remove('selected'));
      document.querySelectorAll('.filter-type-option input[type="radio"]').forEach(radio => {
        radio.checked = radio.value === 'tanggal_spp';
      });
      const firstOption = document.querySelector('.filter-type-option');
      if (firstOption) firstOption.classList.add('selected');

      // Reset year buttons
      document.querySelectorAll('.year-btn').forEach(el => el.classList.remove('selected'));
      const allYearsBtn = document.querySelector('.year-btn.all-years');
      if (allYearsBtn) allYearsBtn.classList.add('selected');
    }

    function applyYearFilter() {
      const bagian = document.getElementById('bagianSelect')?.value || '';
      const url = new URL(window.location.href);
      url.searchParams.set('year', selectedYear);
      url.searchParams.set('year_filter_type', selectedFilterType);
      if (bagian) {
        url.searchParams.set('bagian', bagian);
      } else {
        url.searchParams.delete('bagian');
      }
      url.searchParams.delete('month');
      url.searchParams.delete('page');
      window.location.href = url.toString();
    }

    function changeFilter() {
      const year = document.getElementById('yearSelect').value;
      const yearFilterType = document.getElementById('yearFilterType').value;
      const bagian = document.getElementById('bagianSelect').value;

      const url = new URL(window.location.href);
      url.searchParams.set('year', year);
      url.searchParams.set('year_filter_type', yearFilterType);
      if (bagian) {
        url.searchParams.set('bagian', bagian);
      } else {
        url.searchParams.delete('bagian');
      }
      url.searchParams.delete('month');
      url.searchParams.delete('page');

      window.location.href = url.toString();
    }

    function filterByMonth(month) {
      const year = document.getElementById('yearSelect').value;
      const yearFilterType = document.getElementById('yearFilterType').value;
      const bagian = document.getElementById('bagianSelect').value;

      const url = new URL(window.location.href);
      url.searchParams.set('year', year);
      url.searchParams.set('year_filter_type', yearFilterType);
      url.searchParams.set('month', month);
      if (bagian) {
        url.searchParams.set('bagian', bagian);
      } else {
        url.searchParams.delete('bagian');
      }
      url.searchParams.delete('page');

      window.location.href = url.toString();
    }

    function showAllMonths() {
      const year = document.getElementById('yearSelect').value;
      const yearFilterType = document.getElementById('yearFilterType').value;
      const bagian = document.getElementById('bagianSelect').value;

      const url = new URL(window.location.href);
      url.searchParams.set('year', year);
      url.searchParams.set('year_filter_type', yearFilterType);
      url.searchParams.delete('month');
      if (bagian) {
        url.searchParams.set('bagian', bagian);
      } else {
        url.searchParams.delete('bagian');
      }
      url.searchParams.delete('page');

      window.location.href = url.toString();
    }

    // Close modal on overlay click
    document.getElementById('yearFilterModal').addEventListener('click', function (e) {
      if (e.target === this) closeYearFilterModal();
    });
  </script>

@endsection




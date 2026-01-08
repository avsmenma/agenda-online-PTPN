@extends('layouts.app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 30px;
      font-weight: 700;
      font-size: 28px;
    }

    /* Statistics Cards - Modern Grid Layout for 6 Cards */
    .stat-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
      border: 1px solid rgba(26, 77, 62, 0.08);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      height: 120px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(26, 77, 62, 0.05) 0%, transparent 70%);
      transition: all 0.5s ease;
    }

    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 40px rgba(26, 77, 62, 0.2), 0 4px 16px rgba(15, 61, 46, 0.1);
      border-color: rgba(26, 77, 62, 0.15);
    }

    .stat-card:hover::before {
      top: -60%;
      right: -60%;
    }

    .stat-card-body {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 15px;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
      flex-shrink: 0;
    }

    /* Icon Colors for Each Statistic */
    .stat-icon.total {
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    }

    .stat-icon.proses {
      background: linear-gradient(135deg, #2d6a4f 0%, #1b5e3f 100%);
    }

    .stat-icon.approved {
      background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%);
    }

    .stat-icon.rejected {
      background: linear-gradient(135deg, #52b788 0%, #40916c 100%);
    }

    .stat-icon.bidang {
      background: linear-gradient(135deg, #74c69d 0%, #52b788 100%);
    }

    .stat-icon.bagian {
      background: linear-gradient(135deg, #95d5b2 0%, #74c69d 100%);
    }

    /* Deadline Card Styles - Modern Design */
    .deadline-card-link {
      text-decoration: none;
      display: block;
    }

    .deadline-card {
      border-radius: 16px;
      padding: 20px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      min-height: 140px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      cursor: pointer;
      border-left: 5px solid;
    }

    .deadline-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .deadline-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }

    .deadline-indicator {
      display: flex;
      align-items: center;
    }

    .deadline-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    .deadline-dot.aman {
      background: #28a745;
      box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    }

    .deadline-dot.peringatan {
      background: #ffc107;
      box-shadow: 0 0 8px rgba(255, 193, 7, 0.5);
    }

    .deadline-dot.terlambat {
      background: #dc3545;
      box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
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

    .deadline-count {
      font-size: 18px;
      font-weight: 700;
    }

    .deadline-badge-wrapper {
      margin-bottom: 12px;
    }

    .deadline-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .badge-aman {
      background: #28a745;
      color: white;
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .badge-peringatan {
      background: #ffc107;
      color: #856404;
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }

    .badge-terlambat {
      background: #dc3545;
      color: white;
      box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .deadline-info {
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .deadline-info i {
      font-size: 14px;
    }

    /* Deadline Card Color Variations */
    .deadline-aman {
      background: linear-gradient(135deg, #d4edda 0%, #c8e6c9 100%);
      border-left-color: #28a745;
    }

    .deadline-aman .deadline-count,
    .deadline-aman .deadline-info {
      color: #155724;
    }

    .deadline-peringatan {
      background: linear-gradient(135deg, #fff3cd 0%, #ffe0b2 100%);
      border-left-color: #ffc107;
    }

    .deadline-peringatan .deadline-count,
    .deadline-peringatan .deadline-info {
      color: #856404;
    }

    .deadline-terlambat {
      background: linear-gradient(135deg, #f8d7da 0%, #ffcdd2 100%);
      border-left-color: #dc3545;
    }

    .deadline-terlambat .deadline-count,
    .deadline-terlambat .deadline-info {
      color: #721c24;
    }


    .stat-content {
      flex: 1;
      min-width: 0;
    }

    .stat-title {
      font-size: 11px;
      font-weight: 600;
      color: #6c757d;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      line-height: 1.2;
    }

    .stat-value {
      font-size: 26px;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 2px;
      line-height: 1;
    }

    .stat-description {
      font-size: 10px;
      color: #868e96;
      opacity: 0.8;
    }

    .stat-card:hover .stat-value {
      color: #1a4d3e;
    }

    /* Card Icon Animation */
    .stat-card:hover .stat-icon {
      transform: scale(1.1) rotate(5deg);
      transition: all 0.3s ease;
    }

    /* Search Box */
    .search-box {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 24px;
      border-radius: 16px;
      margin-bottom: 30px;
      box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
      border: 1px solid rgba(26, 77, 62, 0.08);
    }

    .search-box .input-group {
      max-width: 400px;
      margin: 0 auto;
    }

    .search-box .input-group-text {
      background: white;
      border: 2px solid rgba(26, 77, 62, 0.1);
      border-right: none;
      border-radius: 12px 0 0 12px;
      padding: 12px 16px;
    }

    .search-box .form-control {
      border: 2px solid rgba(26, 77, 62, 0.1);
      border-left: none;
      border-radius: 0 12px 12px 0;
      padding: 12px 16px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: white;
    }

    .search-box .form-control:focus {
      outline: none;
      border-color: #1a4d3e;
      box-shadow: 0 0 0 4px rgba(26, 77, 62, 0.1);
    }

    .search-box .form-control::placeholder {
      color: #adb5bd;
    }

    /* Table Container */
    .table-container {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
      border: 1px solid rgba(26, 77, 62, 0.08);
    }

    .table-container h6 {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      padding-bottom: 20px;
      border-bottom: 2px solid rgba(26, 77, 62, 0.1);
    }

    .table-container h6 span {
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
      font-size: 20px;
    }

    .table-container h6 a {
      color: #1a4d3e;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      padding: 8px 20px;
      border-radius: 20px;
      border: 2px solid #1a4d3e;
      background: transparent;
    }

    .table-container h6 a:hover {
      color: white;
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
      border-color: #0f3d2e;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(26, 77, 62, 0.3);
    }

    /* Table Styling */
    .table {
      margin-bottom: 0;
    }

    .table thead {
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    }

    .table thead th {
      color: white;
      font-weight: 600;
      font-size: 13px;
      letter-spacing: 0.5px;
      padding: 18px 16px;
      border: none;
      text-transform: uppercase;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }

    .table tbody tr:hover {
      background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
      border-left: 3px solid #1a4d3e;
      transform: scale(1.002);
    }

    .table tbody tr.highlight-row {
      background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
      border-left: 3px solid #1a4d3e;
    }

    .table tbody td {
      padding: 16px;
      font-size: 13px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(26, 77, 62, 0.05);
    }

    /* Badge Styling */
    .badge {
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.3px;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* Badge Status - Matching dokumensB standards */
    .badge-status {
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.3px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      border: none;
      text-align: center;
      min-width: 100px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.3s ease;
      white-space: nowrap;
    }

    .badge-status.badge-selesai {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .badge-status.badge-sent {
      background: linear-gradient(135deg, #0401ccff 0%, #020daaff 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(4, 1, 204, 0.3);
    }

    .badge-status.badge-proses {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
    }

    .badge-status.badge-dikembalikan {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    /* Legacy badge classes for compatibility */
    .badge-selesai {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .badge-proses {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
    }

    .badge-approved {
      background: linear-gradient(135deg, #52b788 0%, #40916c 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(82, 183, 136, 0.3);
    }

    .badge-rejected {
      background: linear-gradient(135deg, #74c69d 0%, #52b788 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(116, 198, 157, 0.3);
    }

    .badge-pending {
      background: linear-gradient(135deg, #95d5b2 0%, #74c69d 100%);
      color: #1a4d3e;
      box-shadow: 0 2px 8px rgba(149, 213, 178, 0.3);
    }

    .badge:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Green Table Header Theme */
    .table-header-green th {
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%) !important;
      color: white !important;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 12px;
      border: none !important;
      padding: 15px 12px;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 10;
      box-shadow: 0 2px 10px rgba(26, 77, 62, 0.3);
    }

    .table-header-green th:hover {
      background: linear-gradient(135deg, #0f3d2e 0%, #0a2e1f 100%) !important;
    }

    /* Action Button - Detail View */
    .btn-view {
      padding: 10px 16px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 12px;
      transition: all 0.3s ease;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      min-width: 90px;
      justify-content: center;
    }

    .btn-view:hover {
      background: linear-gradient(135deg, #20c997 0%, #1e9e7e 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
      color: white;
      text-decoration: none;
    }

    .btn-view:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 1400px) {
      .stat-card {
        height: 110px;
        padding: 16px;
      }

      .stat-value {
        font-size: 24px;
      }

      .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 18px;
      }
    }

    @media (max-width: 1200px) {
      .stat-card {
        height: 100px;
        padding: 14px;
      }

      .stat-value {
        font-size: 22px;
      }

      .stat-title {
        font-size: 10px;
      }

      .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
      }
    }

    @media (max-width: 768px) {
      .stat-card {
        height: 90px;
        padding: 12px;
      }

      .stat-value {
        font-size: 20px;
      }

      .stat-title {
        font-size: 9px;
      }

      .stat-description {
        font-size: 9px;
      }

      .stat-icon {
        width: 35px;
        height: 35px;
        font-size: 14px;
      }

      .table thead th {
        padding: 14px 10px;
        font-size: 11px;
      }

      .table tbody td {
        padding: 12px 10px;
        font-size: 12px;
      }

      .badge {
        padding: 6px 12px;
        font-size: 11px;
      }
    }

    @media (max-width: 576px) {
      .stat-card-body {
        flex-direction: column;
        text-align: center;
        gap: 10px;
      }

      .stat-value {
        font-size: 18px;
      }

      .btn-view {
        padding: 8px 12px;
        font-size: 11px;
      }
    }
  </style>

  <h2>{{ $title }}</h2>

  <!-- Statistics Cards - Row 1: Total, Proses, Terkirim -->
  <div class="row mb-3">
    <!-- Total Dokumen -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-icon total">
            <i class="fas fa-folder-open"></i>
          </div>
          <div class="stat-content">
            <div class="stat-title">Total Dokumen</div>
            <div class="stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Semua dokumen aktif</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Dokumen Proses -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-icon proses">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-content">
            <div class="stat-title">Dokumen Diproses</div>
            <div class="stat-value">{{ number_format($totalDokumenProses ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Sedang diproses</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Dokumen Terkirim -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
            <i class="fas fa-paper-plane"></i>
          </div>
          <div class="stat-content">
            <div class="stat-title">Total Terkirim</div>
            <div class="stat-value">{{ number_format($totalTerkirim ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Dikirim ke tahap selanjutnya</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards - Row 2: Deadline Cards (Clickable) -->
  <div class="row mb-4">
    <!-- Dokumen AMAN (< 1 Hari - GREEN) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensB?deadline_filter=aman') }}" class="deadline-card-link">
        <div class="deadline-card deadline-aman">
          <div class="deadline-card-header">
            <div class="deadline-indicator">
              <span class="deadline-dot aman"></span>
            </div>
            <div class="deadline-count">{{ number_format($dokumenLessThan24h ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper">
            <span class="deadline-badge badge-aman">
              <i class="fas fa-check-circle"></i> AMAN
            </span>
          </div>
          <div class="deadline-info">
            <i class="fas fa-clock"></i> Diterima < 24 jam yang lalu </div>
          </div>
      </a>
    </div>

    <!-- Dokumen PERINGATAN (1-3 Hari - YELLOW) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensB?deadline_filter=peringatan') }}" class="deadline-card-link">
        <div class="deadline-card deadline-peringatan">
          <div class="deadline-card-header">
            <div class="deadline-indicator">
              <span class="deadline-dot peringatan"></span>
            </div>
            <div class="deadline-count">{{ number_format($dokumen24to72h ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper">
            <span class="deadline-badge badge-peringatan">
              <i class="fas fa-exclamation-triangle"></i> PERINGATAN
            </span>
          </div>
          <div class="deadline-info">
            <i class="fas fa-clock"></i> Diterima 1-3 hari yang lalu
          </div>
        </div>
      </a>
    </div>

    <!-- Dokumen TERLAMBAT (> 3 Hari - RED) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensB?deadline_filter=terlambat') }}" class="deadline-card-link">
        <div class="deadline-card deadline-terlambat">
          <div class="deadline-card-header">
            <div class="deadline-indicator">
              <span class="deadline-dot terlambat"></span>
            </div>
            <div class="deadline-count">{{ number_format($dokumenMoreThan72h ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper">
            <span class="deadline-badge badge-terlambat">
              <i class="fas fa-exclamation-circle"></i> TERLAMBAT
            </span>
          </div>
          <div class="deadline-info">
            <i class="fas fa-clock"></i> Diterima > 3 hari yang lalu
          </div>
        </div>
      </a>
    </div>
  </div>


  <!-- Search Box -->
  <div class="search-box">
    <div class="input-group">
      <span class="input-group-text">
        <i class="fa-solid fa-magnifying-glass text-muted"></i>
      </span>
      <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput">
    </div>
  </div>

  <!-- Dokumen Terbaru Table -->
  <div class="table-container">
    <h6>
      <span>Dokumen Masuk Terbaru</span>
      <a href="{{ url('/dokumensB') }}">Lihat Semua</a>
    </h6>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr class="table-header-green">
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Tanggal Masuk</th>
            <th>Nomor SPP</th>
            <th>Nilai Rupiah</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumenTerbaru ?? [] as $index => $dokumen)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br>
                <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              </td>
              <td>{{ $dokumen->tanggal_masuk->format('d/m/Y H:i') }}</td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>
                <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
              </td>
              <td>
                @if($dokumen->status == 'selesai' || $dokumen->status == 'approved_ibub')
                  <span class="badge-status badge-selesai">✓
                    {{ $dokumen->status == 'approved_ibub' ? 'Approved' : 'Selesai' }}</span>
                @elseif($dokumen->status == 'rejected_ibub')
                  <span class="badge-status badge-dikembalikan">Rejected</span>
                @elseif($dokumen->status == 'sent_to_perpajakan')
                  <span class="badge-status badge-sent">📤 Terkirim ke Perpajakan</span>
                @elseif($dokumen->status == 'sent_to_akutansi')
                  <span class="badge-status badge-sent">📤 Terkirim ke Akutansi</span>
                @elseif($dokumen->status == 'sent_to_ibub')
                  <span class="badge-status badge-proses">⏳ Menunggu Review</span>
                @elseif($dokumen->status == 'sedang diproses')
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                @elseif($dokumen->status == 'returned_to_bidang')
                  <span class="badge-status badge-dikembalikan">Kembali ke Bidang</span>
                @elseif($dokumen->status == 'returned_to_department')
                  <span class="badge-status badge-dikembalikan">Dikembalikan</span>
                @else
                  <span class="badge-status badge-proses">⏳ {{ ucfirst($dokumen->status) }}</span>
                @endif
              </td>
              <td>
                <a href="{{ url('/dokumensB') }}" class="btn-view" title="Lihat Detail">
                  <i class="fa-solid fa-eye"></i>
                  Lihat Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Belum ada dokumen masuk</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Simple Search Script -->
  <script>
    document.getElementById('searchInput').addEventListener('input', function (e) {
      const searchTerm = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');

      rows.forEach(row => {
        if (row.querySelector('td[colspan]')) {
          // Skip empty state row
          return;
        }

        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
      });
    });
  </script>

@endsection
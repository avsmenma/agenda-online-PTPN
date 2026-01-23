@extends('layouts/app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 20px;
      font-weight: 700;
    }

    /* Modern Card Styles */
    .stat-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 20px;
      padding: 28px;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 2px 8px rgba(136, 151, 23, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
      transition: all 0.6s ease;
      opacity: 0;
    }

    .stat-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 12px 40px rgba(8, 62, 64, 0.15), 0 4px 16px rgba(136, 151, 23, 0.1);
      border-color: rgba(136, 151, 23, 0.3);
    }

    .stat-card:hover::before {
      opacity: 1;
      top: -60%;
      right: -60%;
    }

    .stat-card-body {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      position: relative;
      z-index: 1;
    }

    .stat-icon {
      width: 70px;
      height: 70px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: white;
      flex-shrink: 0;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .stat-card:hover .stat-icon {
      transform: scale(1.15) rotate(5deg);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .stat-icon.total {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    }

    .stat-icon.selesai {
      background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
    }

    .stat-icon.proses {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .stat-icon.dikembalikan {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .stat-content {
      flex: 1;
      min-width: 0;
    }

    .stat-title {
      font-size: 13px;
      font-weight: 600;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 8px;
      opacity: 0.9;
    }

    .stat-value {
      font-size: 36px;
      font-weight: 800;
      color: #000000 !important;
      line-height: 1.2;
      margin-bottom: 4px;
      background: none !important;
      -webkit-background-clip: unset !important;
      -webkit-text-fill-color: #000000 !important;
      background-clip: unset !important;
    }

    .stat-description {
      font-size: 12px;
      color: #6c757d;
      font-weight: 500;
      opacity: 0.7;
    }

    /* Legacy card styles for backward compatibility */
    .card {
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(8, 62, 64, 0.2), 0 2px 8px rgba(136, 151, 23, 0.1);
    }

    .card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      transition: all 0.5s ease;
    }

    .card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 12px 32px rgba(8, 62, 64, 0.3), 0 4px 12px rgba(136, 151, 23, 0.2);
    }

    .card:hover::before {
      top: -60%;
      right: -60%;
    }

    .card-body {
      position: relative;
      z-index: 1;
      padding: 24px !important;
    }

    .card i {
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .card:hover i {
      transform: scale(1.15) rotate(5deg);
    }

    .text-xs {
      font-size: 13px;
      letter-spacing: 0.8px;
      opacity: 0.95;
      font-weight: 600;
      text-transform: uppercase;
    }

    .h5 {
      font-size: 32px;
      font-weight: 800;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      line-height: 1.2;
    }

    .search-box {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 20px;
      border-radius: 16px;
      margin-bottom: 24px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.08);
    }

    .search-box .input-group {
      max-width: auto;
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

    .table-container {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 24px;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.08);
    }

    .table-container h6 {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 2px solid rgba(8, 62, 64, 0.1);
    }

    .table-container h6 span {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
    }

    .table-container h6 a {
      color: #889717;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      padding: 6px 16px;
      border-radius: 20px;
      border: 2px solid transparent;
    }

    .table-container h6 a:hover {
      color: white;
      background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
      border-color: #889717;
    }

    .table {
      margin-bottom: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table thead {
      background: #083E40 !important;
      display: table-header-group !important;
      visibility: visible !important;
      opacity: 1 !important;
    }

    .table thead th {
      background: #083E40 !important;
      color: white !important;
      font-weight: 600 !important;
      font-size: 14px !important;
      letter-spacing: 0.5px;
      padding: 16px 12px !important;
      border: none !important;
      vertical-align: middle !important;
      text-align: left !important;
      height: auto !important;
      line-height: normal !important;
      display: table-cell !important;
      visibility: visible !important;
    }

    .table thead tr {
      display: table-row !important;
      visibility: visible !important;
      height: auto !important;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }

    .table tbody tr:hover {
      background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
      border-left: 3px solid #889717;
      transform: scale(1.002);
    }

    .table tbody tr.highlight-row {
      background: linear-gradient(90deg, rgba(136, 151, 23, 0.15) 0%, transparent 100%);
      border-left: 3px solid #889717;
    }

    .table tbody td {
      padding: 14px 12px;
      font-size: 13px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(8, 62, 64, 0.05);
    }

    .badge {
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.3px;
    }

    .badge-success {
      background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(136, 151, 23, 0.3);
    }

    .btn-view {
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 12px;
      transition: all 0.3s ease;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
    }

    .btn-view:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 16px rgba(8, 62, 64, 0.3);
    }

    .btn-view:active {
      transform: translateY(-1px);
    }

    /* Badge Styles */
    .badge-status {
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.3px;
      display: inline-block;
    }

    .badge-selesai {
      background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .badge-proses {
      background: linear-gradient(135deg, #ffc107 0%, #ffcd39 100%);
      color: #333;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    /* Detail Row Styles */
    .detail-row {
      display: none;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .detail-row.show {
      display: table-row;
    }

    .detail-content {
      padding: 20px;
      border-top: 2px solid rgba(8, 62, 64, 0.1);
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 16px;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .detail-label {
      font-size: 11px;
      font-weight: 600;
      color: #083E40;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .detail-value {
      font-size: 13px;
      color: #333;
      font-weight: 500;
    }

    /* Action Button Styles */
    .action-buttons {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .btn-action {
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 12px;
      transition: all 0.3s ease;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .btn-action i {
      font-size: 14px;
    }

    .btn-edit {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    }

    .btn-upload {
      background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
      white-space: nowrap;
    }

    .btn-chevron {
      background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
      padding: 8px 12px;
    }

    .chevron-icon {
      transition: transform 0.3s ease;
    }

    .chevron-icon.rotate {
      transform: rotate(180deg);
    }

    .main-row {
      cursor: pointer;
    }

    /* Custom thead styling */
    .table-container .table-responsive table thead tr.table-dark {
      background: #083E40 !important;
    }

    .table-container .table-responsive table thead tr.table-dark th {
      background: #083E40 !important;
      color: white !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      padding: 16px 12px !important;
      border: none !important;
    }

    /* Override Bootstrap table-dark untuk memastikan warna hijau */
    .table-dark {
      background-color: #083E40 !important;
    }

    .table-dark th {
      background-color: #083E40 !important;
      color: white !important;
      border-color: rgba(255, 255, 255, 0.1) !important;
    }

    /* Section Title Styling */
    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: #083E40;
      margin-top: 30px;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 3px solid #889717;
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Detail item full width for long text */
    .detail-item-full {
      grid-column: 1 / -1;
    }

    /* Responsive adjustments for cards */
    @media (max-width: 1200px) {
      .stat-value {
        font-size: 30px;
      }

      .stat-icon {
        width: 60px;
        height: 60px;
        font-size: 28px;
      }
    }

    @media (max-width: 768px) {
      .stat-card {
        padding: 20px;
      }

      .stat-value {
        font-size: 28px;
      }

      .stat-icon {
        width: 55px;
        height: 55px;
        font-size: 24px;
      }

      .stat-title {
        font-size: 11px;
      }

      .stat-description {
        font-size: 11px;
      }
    }

    /* Animation for card entrance */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .stat-card {
      animation: fadeInUp 0.6s ease-out;
    }

    .stat-card:nth-child(1) {
      animation-delay: 0.1s;
    }

    .stat-card:nth-child(2) {
      animation-delay: 0.2s;
    }

    .stat-card:nth-child(3) {
      animation-delay: 0.3s;
    }

    .stat-card:nth-child(4) {
      animation-delay: 0.4s;
    }

    /* Clickable card styles */
    a .stat-card {
      text-decoration: none;
    }

    a .stat-card:hover {
      transform: translateY(-12px) scale(1.03);
    }

    a:hover {
      text-decoration: none;
      color: inherit;
    }
  </style>

  <h2 style="margin-bottom: 30px; font-weight: 700;">{{ $title }}</h2>

  <!-- Statistics Cards - Row 1: Document Status -->
  <div class="row mb-4">
    <!-- Total Dokumen -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-content">
            <div class="stat-title">Total Dokumen</div>
            <div class="stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Dokumen di pembayaran</div>
          </div>
          <div class="stat-icon total">
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Siap Bayar -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-content">
            <div class="stat-title">Siap Bayar</div>
            <div class="stat-value">{{ number_format($totalSiapBayar ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Dokumen siap dibayar</div>
          </div>
          <div class="stat-icon proses">
            <i class="fas fa-coins"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Sudah Dibayar -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-content">
            <div class="stat-title">Sudah Dibayar</div>
            <div class="stat-value">{{ number_format($totalSudahDibayar ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Dokumen selesai dibayar</div>
          </div>
          <div class="stat-icon selesai">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards - Row 2: Deadline Cards (Matching Verifikasi Style) -->
  <div class="row mb-4">
    <!-- Dokumen AMAN (< 1 Minggu - GREEN) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensPembayaran?deadline_filter=aman') }}" class="deadline-card-link"
        style="text-decoration: none;">
        <div class="deadline-card deadline-aman"
          style="border-radius: 16px; padding: 20px; min-height: 140px; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; border-left: 5px solid #28a745; background: linear-gradient(135deg, #d4edda 0%, #c8e6c9 100%); transition: all 0.3s ease;">
          <div class="deadline-card-header" style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div class="deadline-indicator">
              <span
                style="width: 12px; height: 12px; border-radius: 50%; background: #28a745; box-shadow: 0 0 8px rgba(40, 167, 69, 0.5); display: inline-block; animation: pulse 2s infinite;"></span>
            </div>
            <div class="deadline-count" style="font-size: 18px; font-weight: 700; color: #155724;">
              {{ number_format($totalAman ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper" style="margin-bottom: 12px;">
            <span
              style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; background: #28a745; color: white; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
              <i class="fas fa-check-circle"></i> AMAN
            </span>
          </div>
          <div class="deadline-info"
            style="font-size: 13px; display: flex; align-items: center; gap: 8px; color: #155724;">
            <i class="fas fa-clock"></i> Diterima < 1 minggu yang lalu </div>
          </div>
      </a>
    </div>

    <!-- Dokumen PERINGATAN (1-3 Minggu - YELLOW) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensPembayaran?deadline_filter=peringatan') }}" class="deadline-card-link"
        style="text-decoration: none;">
        <div class="deadline-card deadline-peringatan"
          style="border-radius: 16px; padding: 20px; min-height: 140px; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; border-left: 5px solid #ffc107; background: linear-gradient(135deg, #fff3cd 0%, #ffe0b2 100%); transition: all 0.3s ease;">
          <div class="deadline-card-header" style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div class="deadline-indicator">
              <span
                style="width: 12px; height: 12px; border-radius: 50%; background: #ffc107; box-shadow: 0 0 8px rgba(255, 193, 7, 0.5); display: inline-block; animation: pulse 2s infinite;"></span>
            </div>
            <div class="deadline-count" style="font-size: 18px; font-weight: 700; color: #856404;">
              {{ number_format($totalPeringatan ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper" style="margin-bottom: 12px;">
            <span
              style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; background: #ffc107; color: #856404; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);">
              <i class="fas fa-exclamation-triangle"></i> PERINGATAN
            </span>
          </div>
          <div class="deadline-info"
            style="font-size: 13px; display: flex; align-items: center; gap: 8px; color: #856404;">
            <i class="fas fa-clock"></i> Diterima 1-3 minggu yang lalu
          </div>
        </div>
      </a>
    </div>

    <!-- Dokumen TERLAMBAT (> 3 Minggu - RED) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensPembayaran?deadline_filter=terlambat') }}" class="deadline-card-link"
        style="text-decoration: none;">
        <div class="deadline-card deadline-terlambat"
          style="border-radius: 16px; padding: 20px; min-height: 140px; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; border-left: 5px solid #dc3545; background: linear-gradient(135deg, #f8d7da 0%, #ffcdd2 100%); transition: all 0.3s ease;">
          <div class="deadline-card-header" style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div class="deadline-indicator">
              <span
                style="width: 12px; height: 12px; border-radius: 50%; background: #dc3545; box-shadow: 0 0 8px rgba(220, 53, 69, 0.5); display: inline-block; animation: pulse 2s infinite;"></span>
            </div>
            <div class="deadline-count" style="font-size: 18px; font-weight: 700; color: #721c24;">
              {{ number_format($totalTerlambat ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper" style="margin-bottom: 12px;">
            <span
              style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; background: #dc3545; color: white; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);">
              <i class="fas fa-exclamation-circle"></i> TERLAMBAT
            </span>
          </div>
          <div class="deadline-info"
            style="font-size: 13px; display: flex; align-items: center; gap: 8px; color: #721c24;">
            <i class="fas fa-clock"></i> Diterima > 3 minggu yang lalu
          </div>
        </div>
      </a>
    </div>
  </div>

  <!-- Tabel Dokumen Terbaru -->
  <div class="table-container">
    <h6>
      <span
        style="background: linear-gradient(135deg, #083E40 0%, #889717 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-decoration: none; font-size: 24px; font-weight: 700;">Dokumen
        Masuk</span>
      <a href="{{ url('/dokumensPembayaran')}}" style="color: #1a4d3e; text-decoration: none; font-size: 14px;">View
        All</a>
    </h6>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr class="table table-dark">
            <th>No</th>
            <th>Tanggal Masuk</th>
            <th>Nomor SPP</th>
            <th>Tanggal SPP</th>
            <th>Nilai Rupiah</th>
            <th>Tanggal Dibayar</th>
            <th>Status</th>
            <th>Deadline</th>
            <th>Bukti</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumenTerbaru as $index => $dokumen)
            @php
              // Handler yang dianggap "belum siap dibayar"
              $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];

              // Cek apakah dokumen masih di handler yang belum siap
              $isBelumSiap = in_array($dokumen->current_handler, $belumSiapHandlers);

              // Tanggal masuk ke pembayaran (gunakan sent_to_pembayaran_at jika ada, jika tidak gunakan tanggal_masuk)
              $tanggalMasuk = $dokumen->sent_to_pembayaran_at ?? $dokumen->tanggal_masuk;
            @endphp
            <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
              <td style="text-align: center;">{{ $index + 1 }}</td>
              <td>{{ $tanggalMasuk ? $tanggalMasuk->format('d/m/Y H:i') : '-' }}</td>
              <td>{{ $dokumen->nomor_spp ?? '-' }}</td>
              <td>{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</td>
              <td>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
              <td>{{ $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y H:i') : '-' }}</td>
              <td>
                @if($dokumen->status_pembayaran == 'sudah_dibayar')
                  <span class="badge-status badge-selesai">Sudah Dibayar</span>
                @elseif($isBelumSiap)
                  <span class="badge-status badge-proses">Belum Siap</span>
                @elseif($dokumen->status_pembayaran == 'siap_dibayar')
                  <span class="badge-status badge-proses">Siap Dibayar</span>
                @else
                  <span class="badge-status badge-proses">Belum Dibayar</span>
                @endif
              </td>
              <td>
                @php
                  // Get received_at from roleData to calculate document age (count up)
                  $roleData = $dokumen->getDataForRole('pembayaran');
                  $receivedAt = $roleData?->received_at;

                  $isCompleted = $dokumen->status_pembayaran === 'sudah_dibayar';
                  $ageText = '-';
                  $ageLabel = '-';
                  $ageColor = 'gray';
                  $ageIcon = 'fa-clock';

                  if ($receivedAt) {
                    $receivedAt = \Carbon\Carbon::parse($receivedAt);
                    $processedAt = $roleData?->processed_at;

                    if ($isCompleted && $processedAt) {
                      $endTime = \Carbon\Carbon::parse($processedAt);
                      $diff = $receivedAt->diff($endTime);
                    } else {
                      $now = \Carbon\Carbon::now();
                      $diff = $receivedAt->diff($now);
                    }

                    // Format elapsed time as "X hari Y jam Z menit"
                    $elapsedParts = [];
                    if ($diff->days > 0)
                      $elapsedParts[] = $diff->days . ' hari';
                    if ($diff->h > 0)
                      $elapsedParts[] = $diff->h . ' jam';
                    if ($diff->i > 0 || empty($elapsedParts))
                      $elapsedParts[] = $diff->i . ' menit';
                    $ageText = implode(' ', $elapsedParts);

                    // Green: < 1 week (168h), Yellow: 1-3 weeks (168-504h), Red: >= 3 weeks (504h)
                    $totalHours = ($diff->days * 24) + $diff->h;

                    if ($isCompleted) {
                      $ageLabel = 'SELESAI';
                      $ageColor = 'green';
                      $ageIcon = 'fa-check-circle';
                    } elseif ($totalHours >= 504) {
                      $ageLabel = 'TERLAMBAT';
                      $ageColor = 'red';
                      $ageIcon = 'fa-times-circle';
                    } elseif ($totalHours >= 168) {
                      $ageLabel = 'PERINGATAN';
                      $ageColor = 'yellow';
                      $ageIcon = 'fa-exclamation-triangle';
                    } else {
                      $ageLabel = 'AMAN';
                      $ageColor = 'green';
                      $ageIcon = 'fa-check-circle';
                    }
                  }
                @endphp
                @if($receivedAt)
                  <div class="deadline-card deadline-{{ $ageColor }}"
                    style="padding: 8px; border-radius: 8px; min-width: 120px;">
                    <div style="font-size: 10px; color: #6b7280; margin-bottom: 4px;">
                      <i class="fa-solid fa-calendar"></i> {{ $receivedAt->format('d M Y, H:i') }}
                    </div>
                    <div
                      style="display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; 
                            background: {{ $ageColor === 'green' ? '#10b981' : ($ageColor === 'yellow' ? '#f59e0b' : ($ageColor === 'red' ? '#ef4444' : '#9ca3af')) }}; color: white;">
                      <i class="fa-solid {{ $ageIcon }}"></i> {{ $ageLabel }}
                    </div>
                    <div style="font-size: 9px; color: #6b7280; margin-top: 4px;">
                      <i class="fa-solid fa-hourglass-half"></i> {{ $ageText }}
                    </div>
                  </div>
                @else
                  <span style="color: #9ca3af; font-size: 11px;"><i class="fa-solid fa-clock"></i> Belum diterima</span>
                @endif
              </td>
              <td>
                @if($dokumen->bukti_pembayaran)
                  <a href="{{ asset('storage/' . $dokumen->bukti_pembayaran) }}" target="_blank" class="btn-action btn-edit"
                    onclick="event.stopPropagation()">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td onclick="event.stopPropagation()">
                <div class="action-buttons">
                  <button class="btn-action btn-chevron" onclick="toggleDetail({{ $dokumen->id }})">
                    <i class="fa-solid fa-chevron-down chevron-icon" id="chevron-{{ $dokumen->id }}"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr class="detail-row" id="detail-{{ $dokumen->id }}">
              <td colspan="9">
                <div class="detail-content">
                  <div class="detail-grid">
                    <div class="detail-item">
                      <span class="detail-label">Tanggal Masuk</span>
                      <span class="detail-value">{{ $tanggalMasuk ? $tanggalMasuk->format('d/m/Y H:i:s') : '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Bulan</span>
                      <span class="detail-value">{{ $dokumen->bulan ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Tahun</span>
                      <span class="detail-value">{{ $dokumen->tahun ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">No SPP</span>
                      <span class="detail-value">{{ $dokumen->nomor_spp ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Tanggal SPP</span>
                      <span
                        class="detail-value">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="detail-item detail-item-full">
                      <span class="detail-label">Uraian SPP</span>
                      <span class="detail-value">{{ $dokumen->uraian_spp ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Nilai Rp</span>
                      <span class="detail-value">Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Kriteria CF</span>
                      <span class="detail-value">{{ $dokumen->kategori ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Sub Kriteria</span>
                      <span class="detail-value">{{ $dokumen->jenis_dokumen ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Item Sub Kriteria</span>
                      <span class="detail-value">{{ $dokumen->jenis_sub_pekerjaan ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Dibayar Kepada</span>
                      <span class="detail-value">{{ $dokumen->dibayar_kepada ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">No Berita</span>
                      <span class="detail-value">{{ $dokumen->no_berita_acara ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Tanggal Berita Acara</span>
                      <span
                        class="detail-value">{{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">No PSK</span>
                      <span class="detail-value">{{ $dokumen->no_spk ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Tanggal PSK</span>
                      <span
                        class="detail-value">{{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Tanggal Akhir PSK</span>
                      <span
                        class="detail-value">{{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</span>
                    </div>
                    @if($dokumen->dokumenPos && $dokumen->dokumenPos->count() > 0)
                      <div class="detail-item">
                        <span class="detail-label">No PO</span>
                        <span
                          class="detail-value">{{ $dokumen->dokumenPos->pluck('nomor_po')->filter()->implode(', ') ?: '-' }}</span>
                      </div>
                    @endif
                    @if($dokumen->dokumenPrs && $dokumen->dokumenPrs->count() > 0)
                      <div class="detail-item">
                        <span class="detail-label">No PR</span>
                        <span
                          class="detail-value">{{ $dokumen->dokumenPrs->pluck('nomor_pr')->filter()->implode(', ') ?: '-' }}</span>
                      </div>
                    @endif
                    <div class="detail-item">
                      <span class="detail-label">Nomor Miro</span>
                      <span class="detail-value">{{ $dokumen->nomor_miro ?? '-' }}</span>
                    </div>
                  </div>

                  @if($dokumen->jenis_pph || $dokumen->dpp_pph || $dokumen->ppn_terhutang)
                    <!-- Section Perpajakan -->
                    <div class="section-title">Informasi Perpajakan</div>
                    <div class="detail-grid">
                      <div class="detail-item">
                        <span class="detail-label">Jenis PPh</span>
                        <span class="detail-value">{{ $dokumen->jenis_pph ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">DPP PPh</span>
                        <span
                          class="detail-value">{{ $dokumen->dpp_pph ? 'Rp ' . number_format($dokumen->dpp_pph, 0, ',', '.') : '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">PPh Terhutang</span>
                        <span
                          class="detail-value">{{ $dokumen->ppn_terhutang ? 'Rp ' . number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-' }}</span>
                      </div>
                    </div>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" style="text-align: center; padding: 40px;">
                <p style="color: #6c757d; font-size: 14px;">Tidak ada dokumen masuk</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function toggleDetail(dokumenId) {
      const detailRow = document.getElementById('detail-' + dokumenId);
      const chevron = document.getElementById('chevron-' + dokumenId);

      if (detailRow && chevron) {
        if (detailRow.classList.contains('show')) {
          detailRow.classList.remove('show');
          chevron.classList.remove('rotate');
        } else {
          detailRow.classList.add('show');
          chevron.classList.add('rotate');
        }
      }
    }

  </script>
@endsection



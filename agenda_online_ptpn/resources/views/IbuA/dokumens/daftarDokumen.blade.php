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
search-box .input-group {
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

  .search-box .form-control:focus + .input-group-text {
    border-color: #889717;
  }

  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow-x: auto;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
  }

  /* Enhanced table for better UX - Adopted from IbuB */
  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1000px;
  }

  .table-enhanced thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid #083E40;
    padding: 16px 12px;
    font-size: 13px;
  }

  .table-enhanced th.sticky-column {
    position: sticky;
    left: 0;
    z-index: 11;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  }

  .table-enhanced tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
  }

  .table-enhanced tbody tr:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);
    transform: translateY(-1px);
  }

  /* Column width optimization for IbuA */
  .table-enhanced td {
    padding: 12px;
    vertical-align: middle;
    border-right: 1px solid #e0e0e0;
    white-space: nowrap;
  }

  .table-enhanced .col-no { width: 80px; min-width: 80px; }
  .table-enhanced .col-agenda { width: 120px; min-width: 120px; }
  .table-enhanced .col-spp { width: 140px; min-width: 140px; }
  .table-enhanced .col-tanggal { width: 160px; min-width: 140px; }
  .table-enhanced .col-nilai { width: 120px; min-width: 120px; }
  .table-enhanced .col-mirror { width: 120px; min-width: 120px; }
  .table-enhanced .col-status { width: 120px; min-width: 100px; }
  .table-enhanced .col-keterangan { width: 150px; min-width: 130px; }
  .table-enhanced .col-action { width: 140px; min-width: 140px; }

  .table-enhanced .col-sticky {
    position: sticky;
    left: 0;
    background: white;
    z-index: 5;
  }

  /* Responsive design improvements */
  @media (max-width: 768px) {
    .table-dokumen {
      border-radius: 8px;
      box-shadow: 0 2px 15px rgba(8, 62, 64, 0.05);
    }

    .table-enhanced {
      min-width: 700px;
      font-size: 12px;
    }

    .table-enhanced th {
      padding: 12px 8px;
      font-size: 12px;
    }

    .table-enhanced td {
      padding: 10px 8px;
      font-size: 12px;
    }

    .table-enhanced .col-no { width: 60px; min-width: 60px; }
    .table-enhanced .col-agenda { width: 100px; min-width: 100px; }
    .table-enhanced .col-spp { width: 120px; min-width: 120px; }
    .table-enhanced .col-tanggal { width: 130px; min-width: 130px; }
    .table-enhanced .col-nilai { width: 100px; min-width: 100px; }
    .table-enhanced .col-mirror { width: 100px; min-width: 100px; }
    .table-enhanced .col-status { width: 90px; min-width: 90px; }
    .table-enhanced .col-keterangan { width: 110px; min-width: 110px; }
    .table-enhanced .col-action { width: 120px; min-width: 120px; }

    /* Improve readability on mobile - detail section */
    .detail-item {
      padding: 10px;
      gap: 4px;
    }

    .detail-label {
      font-size: 10px;
      color: #374151;
      letter-spacing: 0.5px;
      padding: 5px 8px;
      min-width: 100px;
    }

    .detail-value {
      font-size: 13px;
      color: #111827;
      line-height: 1.5;
    }
  }

  @media (max-width: 480px) {
    .table-enhanced {
      min-width: 600px;
    }

    .table-enhanced th {
      padding: 10px 6px;
      font-size: 11px;
    }

    .table-enhanced td {
      padding: 8px 6px;
      font-size: 11px;
    }

    .table-enhanced .col-no { width: 50px; min-width: 50px; }
    .table-enhanced .col-agenda { width: 90px; min-width: 90px; }
    .table-enhanced .col-spp { width: 100px; min-width: 100px; }
    .table-enhanced .col-tanggal { width: 120px; min-width: 120px; }
    .table-enhanced .col-nilai { width: 80px; min-width: 80px; }
    .table-enhanced .col-mirror { width: 80px; min-width: 80px; }
    .table-enhanced .col-status { width: 80px; min-width: 80px; }
    .table-enhanced .col-keterangan { width: 90px; min-width: 90px; }
    .table-enhanced .col-action { width: 100px; min-width: 100px; }
  }

  .table-dokumen thead {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    position: relative;
  }

  .table-dokumen thead::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #889717 50%, transparent 100%);
  }

  .table-dokumen thead th {
    padding: 16px 12px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    text-align: center;
    letter-spacing: 0.5px;
  }

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table-dokumen tbody tr.main-row:hover {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    border-left: 3px solid #889717;
    transform: scale(1.005);
  }

  .table-dokumen tbody tr.main-row.highlight {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.15) 0%, transparent 100%) !important;
    border-left: 3px solid #889717;
  }

  .table-dokumen tbody tr.main-row.selected {
    background: linear-gradient(90deg, rgba(8, 62, 64, 0.05) 0%, transparent 100%);
    border-left: 3px solid #083E40;
  }

  .table-dokumen tbody td {
    padding: 14px 12px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
  }

  /* Enhanced Detail Row Styles - Adopted from IbuB */
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
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    width: 100%;
    box-sizing: border-box;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px; /* Tambah gap untuk background spacing */
    min-width: 0;
    width: 100%;
    overflow: visible;
    background: #ffffff; /* Putih bersih untuk contrast dengan label */
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #f1f5f9; /* Border yang sangat tipis */
    transition: all 0.2s ease;
  }

  .detail-item:hover {
    border-color: #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .detail-label {
    display: inline-block; /* Inline block untuk background yang tepat */
    font-size: 11px;
    font-weight: 700; /* Extra bold */
    color: #374151; /* text-gray-700 - lebih gelap untuk kontras maksimal */
    text-transform: uppercase;
    letter-spacing: 0.7px;
    background: #f3f4f6; /* bg-gray-100 - background yang jelas terlihat */
    padding: 6px 10px; /* Padding yang visible */
    border-radius: 6px; /* Rounded corners yang lembut */
    border-left: 3px solid #6366f1; /* Aksen biru di kiri untuk visual distinction */
    margin-bottom: 2px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    max-width: 100%;
    width: fit-content; /* Hanya selebar teks */
    min-width: 120px; /* Minimum width untuk konsistensi */
  }

  .detail-value {
    font-size: 14px;
    color: #111827; /* text-gray-900 - hampir hitam */
    font-weight: 600; /* Semi-bold untuk menonjol sebagai data utama */
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
    white-space: normal;
    max-width: 100%;
    width: 100%;
    overflow: visible;
    line-height: 1.6;
    padding: 4px 0; /* Sedikit padding atas/bawah */
    position: relative;
  }

  /* Special styling for different field types */
  .detail-value.text-danger {
    color: #dc2626;
    font-weight: 600;
  }

  .detail-value .badge {
    font-size: 11px;
    font-weight: 600;
  }

  /* Chevron Icon Animation */
  .chevron-icon {
    transition: transform 0.3s ease;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  /* Enhanced Status System - Dynamic and Modern */
  .badge-status {
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
    border: 2px solid transparent;
    text-align: center;
    min-width: 100px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.3s ease;
  }

  /* State 1: Draft / Belum Dikirim */
  .badge-status.badge-draft {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    border-color: #495057;
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
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shimmer 2s infinite;
  }

  /* State 2: Sudah Dikirim ke IbuB */
  .badge-status.badge-terkirim {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
  }

  .badge-status.badge-terkirim::after {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    background: white;
    border-radius: 50%;
    margin-left: 6px;
    animation: pulse 1.5s infinite;
  }

  /* State 3: Dikembalikan */
  .badge-status.badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border-color: #dc3545;
    position: relative;
  }

  .badge-status.badge-dikembalikan::before {
    content: '⚠️';
    margin-right: 4px;
  }

  /* Enhanced hover effects */
  .badge-status:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
  }

  /* Animations */
  @keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
  }

  /* Responsive Status Badges */
  @media (max-width: 768px) {
    .badge-status {
      padding: 6px 12px;
      font-size: 11px;
      min-width: 80px;
      gap: 4px;
    }

    .badge-status.badge-terkirim::after {
      width: 4px;
      height: 4px;
      margin-left: 4px;
    }
  }

  @media (max-width: 480px) {
    .badge-status {
      padding: 5px 10px;
      font-size: 10px;
      min-width: 70px;
      border-radius: 15px;
    }

    .badge-status span {
      display: none; /* Hide text on very small screens, show only icons */
    }

    .badge-status::before {
      font-size: 14px;
    }
  }

  .badge-yellow {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: #333;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  .badge-green {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
  }

  .badge-yellow:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
  }

  .badge-green:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  }

  /* Enhanced Action Buttons - Touch-friendly and Modern */
  .action-buttons {
    display: flex;
    gap: 6px;
    justify-content: center;
    flex-wrap: wrap;
    align-items: center;
  }

  /* Touch-friendly button sizes */
  .btn-action {
    min-width: 44px;
    min-height: 44px;
    padding: 10px 12px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    text-decoration: none;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
  }

  .btn-action i {
    font-size: 12px;
    flex-shrink: 0;
  }

  .btn-action span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Enhanced hover and active states */
  .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }

  .btn-action:active {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  }

  /* Ripple effect for better touch feedback */
  .btn-action::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.3s ease, height 0.3s ease;
  }

  .btn-action:active::before {
    width: 100px;
    height: 100px;
  }

  /* Action button types with enhanced gradients */
  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
    color: white;
  }

  .btn-edit:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #0f6f74 100%);
  }

  .btn-send {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 50%, #a8bf23 100%);
    color: white;
  }

  .btn-send:hover {
    background: linear-gradient(135deg, #9ab01f 0%, #a8bf23 50%, #b8cf27 100%);
  }

  /* Responsive Action Button Styles */
  @media (max-width: 1200px) {
    .btn-action {
      padding: 8px 10px;
      font-size: 10px;
      gap: 3px;
    }

    .btn-action i {
      font-size: 11px;
    }

    .action-buttons {
      gap: 4px;
    }
  }

  @media (max-width: 768px) {
    .action-buttons {
      flex-direction: column;
      gap: 6px;
      align-items: stretch;
    }

    .btn-action {
      width: 100%;
      min-width: 48px;
      min-height: 48px;
      padding: 12px 8px;
      font-size: 11px;
      border-radius: 8px;
      justify-content: center;
      gap: 6px;
    }

    .btn-action i {
      font-size: 14px;
    }
  }

  @media (max-width: 480px) {
    .action-buttons {
      flex-direction: row;
      flex-wrap: nowrap;
      gap: 3px;
      overflow-x: auto;
      padding: 2px;
      -webkit-overflow-scrolling: touch;
    }

    .btn-action {
      flex-shrink: 0;
      min-width: 44px;
      min-height: 44px;
      padding: 8px 6px;
      font-size: 0;
      border-radius: 6px;
    }

    .btn-action i {
      font-size: 16px;
      margin: 0;
    }

    .btn-action span {
      display: none;
    }
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .action-buttons {
      gap: 6px;
    }

    .btn-action {
      padding: 6px 10px;
      font-size: 11px;
      min-width: 32px;
      height: 32px;
    }
  }

  .filter-section {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .filter-section select,
  .filter-section input {
    padding: 10px 14px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 13px;
    transition: all 0.3s ease;
    background: white;
    font-weight: 500;
  }

  .filter-section select:focus,
  .filter-section input:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-filter {
    padding: 10px 20px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 62, 64, 0.3);
    color: white;
  }

  .btn-filter:active {
    transform: translateY(-1px);
    box-shadow: 0 3px 12px rgba(8, 62, 64, 0.4);
  }

  .btn-tambah {
    padding: 10px 20px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
  }

  .btn-tambah:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(136, 151, 23, 0.3);
    color: white;
  }

  .btn-tambah:active {
    transform: translateY(-1px);
    box-shadow: 0 3px 12px rgba(136, 151, 23, 0.4);
  }

  /* Enhanced Form Controls */
  .form-select {
    padding: 10px 14px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 13px;
    transition: all 0.3s ease;
    background: white;
    font-weight: 500;
    min-height: 44px;
    min-width: 120px;
  }

  .form-select:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  /* Responsive Search & Filter */
  @media (max-width: 768px) {
    .search-box form {
      flex-direction: column;
      align-items: stretch;
      gap: 15px;
    }

    .input-group {
      min-width: auto !important;
      margin-right: 0 !important;
    }

    .filter-section {
      margin-right: 0 !important;
    }

    .btn-filter,
    .btn-tambah {
      width: 100%;
      justify-content: center;
      min-height: 48px;
    }

    .form-select {
      min-height: 48px;
      width: 100%;
    }
  }

  .btn-excel {
    padding: 10px 24px;
    background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    margin-left: 0;
  }

  .btn-excel:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
  }

  .chevron-icon {
    transition: transform 0.4s ease;
    color: #fff;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
  }

  .pagination button {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    cursor: pointer;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: #083E40;
  }

  .pagination button:hover {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .pagination button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
  }

  .btn-chevron:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
  }

  /* Enhanced Loading Spinner */
  .loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: #083E40;
    font-size: 14px;
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.02) 0%, rgba(136, 151, 23, 0.02) 100%);
    border-radius: 12px;
    margin: 20px 0;
  }

  .loading-spinner i {
    margin-right: 12px;
    font-size: 18px;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  /* Final optimization for consistent styling */
  .table-container {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
  }

  /* Micro-interactions for better UX */
  .main-row {
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
  }

  .main-row:hover {
    border-left: 3px solid #889717;
  }

  .main-row:active {
    transform: scale(0.99);
  }

  </style>

<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-radius: 10px;">
        <i class="fa-solid fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-radius: 10px;">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Enhanced Search & Filter Box -->
<div class="search-box">
  <form action="{{ route('dokumens.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-3">
    <div class="input-group" style="flex: 1; min-width: 300px;">
      <span class="input-group-text">
        <i class="fa-solid fa-magnifying-glass text-muted"></i>
      </span>
      <input type="text" class="form-control" name="search" placeholder="Cari nomor agenda atau SPP..." value="{{ request('search') }}">
    </div>
    <div class="filter-section">
      <select name="year" class="form-select">
        <option value="">Semua Tahun</option>
        <option value="2025" {{ request('year') == '2025' ? 'selected' : '' }}>2025</option>
        <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
        <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
      </select>
    </div>
    <button type="submit" class="btn-filter">
      <i class="fa-solid fa-filter me-2"></i>Filter
    </button>
    <a href="{{ route('dokumens.create') }}" class="btn-tambah">
      <i class="fa-solid fa-plus me-2"></i>Tambah Dokumen
    </a>
  </form>
</div>

<!-- Enhanced Tabel Dokumen -->
<div class="table-responsive table-container">
  <table class="table table-enhanced mb-0">
    <thead>
      <tr>
        <th class="col-no sticky-column">No</th>
        <th class="col-agenda">Nomor Agenda</th>
        <th class="col-spp">Nomor SPP</th>
        <th class="col-tanggal">Tanggal Masuk</th>
        <th class="col-nilai">Nilai Rupiah</th>
        <th class="col-mirror">Nomor Mirror</th>
        <th class="col-status">Status</th>
        <th class="col-keterangan">Keterangan</th>
        <th class="col-action sticky-column">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dokumens as $index => $dokumen)
      <tr class="main-row clickable-row" data-id="{{ $dokumen->id }}" data-dokumen-id="{{ $dokumen->id }}" onclick="loadDocumentDetail({{ $dokumen->id }})">
        <td class="col-no sticky-column">{{ $dokumens->firstItem() + $index }}</td>
        <td class="col-agenda">
          <strong>{{ $dokumen->nomor_agenda }}</strong>
          <br>
          <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
        </td>
        <td class="col-spp">{{ $dokumen->nomor_spp }}</td>
        <td class="col-tanggal">{{ $dokumen->tanggal_masuk->format('d-m-Y H:i') }}</td>
        <td class="col-nilai">
          <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
        </td>
        <td class="col-mirror">
          <span class="text-muted">-</span>
        </td>
        <td class="col-status">
          @if(in_array($dokumen->status, ['draft', 'returned_to_ibua']))
            <span class="badge-status badge-draft">
              <i class="fa-solid fa-file-lines me-1"></i>
              <span>Belum Dikirim</span>
            </span>
          @elseif($dokumen->status == 'pending_approval_ibub')
            <span class="badge-status" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
              <i class="fa-solid fa-clock me-1"></i>
              <span>Sedang Menunggu Persetujuan</span>
            </span>
          @elseif($dokumen->status == 'approved_data_sudah_terkirim')
            <span class="badge-status badge-terkirim">
              <i class="fa-solid fa-check me-1"></i>
              <span>Terkirim</span>
            </span>
          @elseif($dokumen->status == 'rejected_data_tidak_lengkap')
            <span class="badge-status badge-dikembalikan">
              <i class="fa-solid fa-times me-1"></i>
              <span>Dikembalikan</span>
            </span>
          @else
            <span class="badge-status badge-terkirim">
              <i class="fa-solid fa-check me-1"></i>
              <span>Terikirim</span>
            </span>
          @endif
        </td>
        <td class="col-keterangan">
          <span class="text-muted">-</span>
        </td>
        <td class="col-action sticky-column" onclick="event.stopPropagation()">
          <div class="action-buttons">
            <a href="{{ route('dokumens.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
              <i class="fa-solid fa-edit"></i>
              <span>Edit</span>
            </a>
            @php
              $canSend = in_array($dokumen->status, ['draft', 'returned_to_ibua', 'sedang diproses'])
                        && ($dokumen->current_handler ?? 'ibuA') == 'ibuA'
                        && ($dokumen->created_by ?? 'ibuA') == 'ibuA';
            @endphp
            <!-- @if($canSend)
            <button class="btn-action btn-send" onclick="sendToIbuB({{ $dokumen->id }})" title="Kirim ke IbuB">
              <i class="fa-solid fa-paper-plane"></i>
              <span>Kirim</span>
            </button>
            @endif -->
            @if($canSend)
<button class="btn-action btn-send" onclick="sendToIbuB({{ $dokumen->id }})" title="Kirim ke IbuB">
  <i class="fa-solid fa-paper-plane"></i>
  <span>Kirim</span>
</button>
@endif

          </div>
        </td>
      </tr>
      <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display: none;">
        <td colspan="9">
          <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
            <div class="loading-spinner">
              <i class="fa-solid fa-spinner fa-spin"></i>
              <span>Memuat detail dokumen...</span>
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" class="text-center py-4">
          <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
          <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
          <a href="{{ route('dokumens.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Tambah Dokumen
          </a>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Pagination -->
@if($dokumens->hasPages())
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

<div class="text-center mt-3">
    <small class="text-muted">
        Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }} dokumen
    </small>
</div>
@endif



<!-- Modal: Send Confirmation -->
<div class="modal fade" id="sendConfirmationModal" tabindex="-1" aria-labelledby="sendConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1a4d3e 0%, #0f8357 100%); color: white;">
        <h5 class="modal-title" id="sendConfirmationModalLabel">
          <i class="fa-solid fa-paper-plane me-2"></i>Konfirmasi Pengiriman
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-question-circle" style="font-size: 52px; color: #0f8357;"></i>
        </div>
        <h5 class="fw-bold mb-3">Apakah Anda yakin ingin mengirim dokumen ini ke IbuB?</h5>
        <p class="text-muted mb-0">
          Dokumen akan dikirim ke IbuB untuk proses verifikasi dan akan muncul di daftar dokumen IbuB.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success px-4" id="confirmSendToIbuBBtn">
          <i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Send Success -->
<div class="modal fade" id="sendSuccessModal" tabindex="-1" aria-labelledby="sendSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1a4d3e 0%, #0f8357 100%); color: white;">
        <h5 class="modal-title" id="sendSuccessModalLabel">
          <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #0f8357;"></i>
        </div>
        <h5 class="fw-bold mb-2">Dokumen telah dikirim ke IbuB!</h5>
        <p class="text-muted mb-0" id="sendSuccessMessage">
          Dokumen berhasil dikirim dan akan muncul di daftar IbuB untuk proses selanjutnya.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-check me-2"></i>Selesai
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Enhanced interactions and animations
function toggleDetail(rowId) {
  const detailRow = document.getElementById('detail-' + rowId);
  const chevron = document.getElementById('chevron-' + rowId);

  if (detailRow.style.display === 'none' || !detailRow.style.display) {
    // Show detail with animation
    detailRow.style.display = 'table-row';
    setTimeout(() => {
      detailRow.classList.add('show');
      chevron.classList.add('rotate');
    }, 10);
  } else {
    // Hide detail
    detailRow.classList.remove('show');
    chevron.classList.remove('rotate');
    setTimeout(() => {
      detailRow.style.display = 'none';
    }, 300);
  }
}

// Simple Send to IbuB Function
function sendToIbuB(docId) {
  // Store document ID for confirmation
  document.getElementById('confirmSendToIbuBBtn').setAttribute('data-doc-id', docId);
  
  // Show confirmation modal
  const confirmationModal = new bootstrap.Modal(document.getElementById('sendConfirmationModal'));
  confirmationModal.show();
}

// Confirm and send to IbuB
function confirmSendToIbuB() {
  const docId = document.getElementById('confirmSendToIbuBBtn').getAttribute('data-doc-id');
  if (!docId) {
    console.error('Document ID not found');
    return;
  }

  // Close confirmation modal
  const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('sendConfirmationModal'));
  confirmationModal.hide();

  const btn = document.querySelector(`button[onclick="sendToIbuB(${docId})"]`);
  if (!btn) return;

  // Show loading state
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';

  fetch(`/dokumens/${docId}/send-to-ibub`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      deadline_days: null,  // No deadline from IbuA
      deadline_note: null
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Show success modal
      const successModal = new bootstrap.Modal(document.getElementById('sendSuccessModal'));
      successModal.show();

      // Reload page when success modal is closed
      const successModalEl = document.getElementById('sendSuccessModal');
      successModalEl.addEventListener('hidden.bs.modal', function() {
        location.reload();
      }, { once: true });
    } else {
      showNotification('Gagal mengirim dokumen: ' + (data.message || 'Terjadi kesalahan'), 'error');
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.', 'error');
    btn.disabled = false;
    btn.innerHTML = originalHTML;
  });
}


let shouldReloadAfterSendSuccess = false;

function showSendSuccessModal(message) {
  const modalEl = document.getElementById('sendSuccessModal');
  if (!modalEl) {
    location.reload();
    return;
  }

  const textEl = document.getElementById('sendSuccessMessage');
  if (textEl) {
    textEl.textContent = message || 'Dokumen berhasil dikirim dan akan diproses oleh IbuB.';
  }

  shouldReloadAfterSendSuccess = true;
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

// Load Document Detail with Lazy Loading
function loadDocumentDetail(documentId) {
  const detailRow = document.getElementById(`detail-${documentId}`);
  const detailContent = document.getElementById(`detail-content-${documentId}`);
  const mainRow = document.querySelector(`tr[data-id="${documentId}"]`);

  // Toggle detail visibility
  if (detailRow.style.display === 'none' || !detailRow.style.display) {
    // Show loading
    detailRow.style.display = 'table-row';
    detailContent.innerHTML = `
      <div class="loading-spinner">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <span>Memuat detail dokumen...</span>
      </div>
    `;

    // Add highlight to main row
    mainRow.classList.add('selected');

    // Fetch detail data
    fetch(`/dokumens/${documentId}/detail-ibua`)
      .then(response => response.text())
      .then(html => {
        detailContent.innerHTML = html;
        detailRow.classList.add('show');
      })
      .catch(error => {
        console.error('Error loading document detail:', error);
        detailContent.innerHTML = `
          <div class="text-center p-4 text-danger">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            Gagal memuat detail dokumen. Silakan coba lagi.
          </div>
        `;
      });
  } else {
    // Hide detail
    detailRow.style.display = 'none';
    detailRow.classList.remove('show');
    mainRow.classList.remove('selected');
  }
}

// Enhanced notification system
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
      <span>${message}</span>
    </div>
  `;

  document.body.appendChild(notification);

  // Trigger animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  // Auto remove
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Add notification styles
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    transform: translateX(100%);
    transition: all 0.3s ease;
    max-width: 400px;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  }

  .notification.show {
    transform: translateX(0);
  }

  .notification-content {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    font-weight: 500;
  }

  .notification-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  }

  .notification-error {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  }

  .notification-info {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  }

  @media (max-width: 768px) {
    .notification {
      left: 20px;
      right: 20px;
      max-width: none;
      top: 10px;
    }
  }
`;
document.head.appendChild(notificationStyles);

// Enhanced page initialization
document.addEventListener('DOMContentLoaded', function() {
  const successModalEl = document.getElementById('sendSuccessModal');
  if (successModalEl) {
    successModalEl.addEventListener('hidden.bs.modal', function() {
      if (shouldReloadAfterSendSuccess) {
        shouldReloadAfterSendSuccess = false;
        location.reload();
      }
    });
  }

  // Initialize confirmation button click handler
  const confirmBtn = document.getElementById('confirmSendToIbuBBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', confirmSendToIbuB);
  }

  // Add smooth scroll behavior
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // Add loading states for all buttons
  document.querySelectorAll('.btn-action').forEach(button => {
    button.addEventListener('click', function() {
      if (!this.disabled) {
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
          this.style.transform = '';
        }, 100);
      }
    });
  });
});

</script>

@endsection

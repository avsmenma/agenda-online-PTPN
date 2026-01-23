@extends('layouts/app')
@section('content')

  <style>
    /* Color Variables - Always use Perpajakan Green Theme */
    :root {
      --primary-color: #1a4d3e;
      --primary-dark: #0f3d2e;
      --secondary-color: #40916c;
      --primary-rgba: rgba(26, 77, 62, 0.1);
      --primary-rgba-dark: rgba(26, 77, 62, 0.2);
      --primary-border: rgba(26, 77, 62, 0.08);
      --primary-border-hover: rgba(26, 77, 62, 0.15);
      --card-green: #28a745;
      --card-yellow: #ffc107;
      --card-red: #dc3545;
    }

    h2 {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 30px;
      font-weight: 700;
      font-size: 28px;
    }

    /* Timeframe Settings Panel */
    .timeframe-settings {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 30px;
      box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
      border: 1px solid var(--primary-border);
    }

    .timeframe-settings-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }


    /* Card Statistics - New Deadline Card Design */
    .card-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
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

    .deadline-card.active {
      border: 3px solid var(--primary-color);
      box-shadow: 0 12px 40px var(--primary-rgba-dark);
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

    /* Modern Filter Panel Styles */
    .modern-filter-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .filter-toggle-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem;
      background: linear-gradient(135deg, #f8faf8 0%, #f1f5f9 100%);
      border-bottom: 2px solid var(--primary-border);
    }

    .filter-toggle-btn {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1.25rem;
      background: white;
      border: 2px solid var(--primary-border);
      border-radius: 8px;
      font-weight: 600;
      color: var(--primary-color);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .filter-toggle-btn:hover {
      background: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px var(--primary-rgba-dark);
    }

    .filter-badge {
      background: var(--primary-color);
      color: white;
      padding: 0.25rem 0.5rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 700;
      min-width: 24px;
      text-align: center;
    }

    .filter-panel {
      padding: 2rem;
      background: white;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .filter-form {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .filter-row {
      display: flex;
      gap: 1rem;
    }

    .filter-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .filter-group.full-width {
      grid-column: 1 / -1;
    }

    .filter-label {
      font-weight: 600;
      color: var(--primary-color);
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .filter-select,
    .filter-input-search {
      padding: 0.75rem 1rem;
      border: 2px solid var(--primary-border);
      border-radius: 8px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background: white;
    }

    .filter-select:focus,
    .filter-input-search:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px var(--primary-rgba);
    }

    .filter-select:disabled {
      background-color: #f1f5f9;
      cursor: not-allowed;
      opacity: 0.6;
    }

    .filter-searchable {
      min-height: 44px;
    }

    .filter-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      padding-top: 1rem;
      border-top: 2px solid var(--primary-border);
    }

    .filter-btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .filter-btn-reset {
      background: #f8faf8;
      color: var(--primary-color);
    }

    .filter-btn-reset:hover {
      background: #e2e8f0;
      transform: translateY(-2px);
    }

    .filter-btn-apply {
      background: var(--primary-color);
      color: white;
    }

    .filter-btn-apply:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px var(--primary-rgba-dark);
    }

    .active-filters {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      padding-top: 1rem;
      border-top: 2px solid var(--primary-border);
    }

    .filter-badge-item {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background: var(--primary-color);
      color: white;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }

    .filter-badge-item .remove-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      transition: all 0.2s ease;
    }

    .filter-badge-item .remove-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    /* View Switcher */
    .view-switcher {
      display: inline-flex;
      background: white;
      border-radius: 8px;
      padding: 4px;
      gap: 4px;
      border: 2px solid var(--primary-border);
    }

    .view-switcher-btn {
      padding: 8px 16px;
      border: none;
      background: transparent;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 500;
      color: var(--primary-color);
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .view-switcher-btn:hover {
      background: var(--primary-rgba);
      color: var(--primary-color);
    }

    .view-switcher-btn.active {
      background: var(--primary-color);
      color: white;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* View Container */
    .view-container {
      display: none;
    }

    .view-container.active {
      display: block;
    }

    /* Card View */
    .card-view-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 1.5rem;
    }

    @media (max-width: 768px) {
      .card-view-container {
        grid-template-columns: 1fr;
      }

      .filter-grid {
        grid-template-columns: 1fr;
      }

      .filter-toggle-bar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
    }

    .document-card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--primary-border);
      transition: all 0.2s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .document-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px var(--primary-rgba);
      border-color: var(--secondary-color);
    }

    /* Color-coded document cards based on deadline status */
    .document-card-green {
      border-left: 5px solid var(--card-green);
      background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, white 100%);
    }

    .document-card-green:hover {
      border-color: var(--card-green);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }

    .document-card-yellow {
      border-left: 5px solid var(--card-yellow);
      background: linear-gradient(135deg, rgba(255, 193, 7, 0.08) 0%, white 100%);
    }

    .document-card-yellow:hover {
      border-color: var(--card-yellow);
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
    }

    .document-card-red {
      border-left: 5px solid var(--card-red);
      background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, white 100%);
    }

    .document-card-red:hover {
      border-color: var(--card-red);
      box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
    }


    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .card-title {
      font-size: 16px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 4px;
    }

    .card-subtitle {
      font-size: 13px;
      color: #6c757d;
    }

    .card-value {
      font-size: 20px;
      font-weight: 700;
      color: var(--secondary-color);
      margin-bottom: 1rem;
    }

    .card-info-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #6c757d;
      margin-bottom: 8px;
    }

    .card-info-row i {
      width: 16px;
      color: var(--primary-color);
    }

    /* Table Section */
    .table-container {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
      border: 1px solid var(--primary-border);
    }

    .table-responsive {
      overflow-x: auto;
      overflow-y: visible;
      -webkit-overflow-scrolling: touch;
    }

    .table thead {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
    }

    .table thead th {
      background: transparent !important;
      color: white !important;
      font-weight: 600;
      font-size: 13px;
      letter-spacing: 0.5px;
      padding: 18px 16px;
      border: none !important;
      text-transform: uppercase;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }

    .table tbody tr.clickable-row {
      cursor: pointer;
    }

    .table tbody tr.clickable-row:hover {
      background: linear-gradient(90deg, var(--primary-rgba) 0%, transparent 100%);
      border-left: 3px solid var(--secondary-color);
      transform: scale(1.002);
    }

    /* Color-coded table rows based on deadline status */
    .table tbody tr.table-row-green {
      border-left: 4px solid var(--card-green);
      background: linear-gradient(90deg, rgba(40, 167, 69, 0.08) 0%, transparent 50%);
    }

    .table tbody tr.table-row-yellow {
      border-left: 4px solid var(--card-yellow);
      background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, transparent 50%);
    }

    .table tbody tr.table-row-red {
      border-left: 4px solid var(--card-red);
      background: linear-gradient(90deg, rgba(220, 53, 69, 0.08) 0%, transparent 50%);
    }


    .age-badge {
      padding: 6px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }

    .age-badge.age-green {
      background: linear-gradient(135deg, var(--card-green) 0%, #20c997 100%);
      color: white;
    }

    .age-badge.age-yellow {
      background: linear-gradient(135deg, var(--card-yellow) 0%, #ffcd39 100%);
      color: #333;
    }

    .age-badge.age-red {
      background: linear-gradient(135deg, var(--card-red) 0%, #c82333 100%);
      color: white;
    }

    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 24px;
    }

    .pagination a,
    .pagination span {
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .pagination .page-link {
      color: var(--primary-color);
      background: white;
      border: 2px solid var(--primary-color);
    }

    .pagination .page-link:hover {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
    }

    .pagination .active .page-link {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
      border-color: var(--primary-color);
    }

    .form-control,
    .form-select {
      border: 2px solid rgba(8, 62, 64, 0.1);
      border-radius: 8px;
      padding: 10px 16px;
      transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem var(--primary-rgba);
    }

    .btn-filter {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 24px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-filter:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px var(--primary-rgba-dark);
    }
  </style>

  <div class="container-fluid">
    <h2><i class="fa-solid fa-exclamation-triangle"></i> Rekapan Keterlambatan - {{ $roleConfig[$roleCode]['name'] }}</h2>


    <!-- Card Statistics - New Deadline Design -->
    @if(in_array($roleCode, ['team_verifikasi', 'perpajakan', 'akutansi']))
      <div class="card-stats">
        @php
          $currentFilterAge = request('filter_age', '');
          $isCard1Active = $currentFilterAge === '1';
          $isCard2Active = $currentFilterAge === '2';
          $isCard3Active = $currentFilterAge === '3+';
        @endphp

        <!-- Card AMAN (< 1 Hari - Green) -->
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['filter_age', 'page']), ['filter_age' => $isCard1Active ? '' : '1']))) }}"
          class="deadline-card-link">
          <div class="deadline-card deadline-aman {{ $isCard1Active ? 'active' : '' }}">
            <div class="deadline-card-header">
              <div class="deadline-indicator">
                <span class="deadline-dot aman"></span>
              </div>
              <div class="deadline-count">{{ $cardStats['card1']['count'] ?? 0 }} Dokumen</div>
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

        <!-- Card PERINGATAN (1-3 Hari - Yellow) -->
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['filter_age', 'page']), ['filter_age' => $isCard2Active ? '' : '2']))) }}"
          class="deadline-card-link">
          <div class="deadline-card deadline-peringatan {{ $isCard2Active ? 'active' : '' }}">
            <div class="deadline-card-header">
              <div class="deadline-indicator">
                <span class="deadline-dot peringatan"></span>
              </div>
              <div class="deadline-count">{{ $cardStats['card2']['count'] ?? 0 }} Dokumen</div>
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

        <!-- Card TERLAMBAT (> 3 Hari - Red) -->
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['filter_age', 'page']), ['filter_age' => $isCard3Active ? '' : '3+']))) }}"
          class="deadline-card-link">
          <div class="deadline-card deadline-terlambat {{ $isCard3Active ? 'active' : '' }}">
            <div class="deadline-card-header">
              <div class="deadline-indicator">
                <span class="deadline-dot terlambat"></span>
              </div>
              <div class="deadline-count">{{ $cardStats['card3']['count'] ?? 0 }} Dokumen</div>
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
    @elseif($roleCode === 'pembayaran')
      <!-- Card Statistics for Pembayaran - Weekly Thresholds -->
      <div class="card-stats">
        @php
          $currentFilterAge = request('filter_age', '');
          $isCard1Active = $currentFilterAge === '1';
          $isCard2Active = $currentFilterAge === '2';
          $isCard3Active = $currentFilterAge === '3+';
        @endphp

        <!-- Card AMAN (< 1 Minggu - Green) -->
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['filter_age', 'page']), ['filter_age' => $isCard1Active ? '' : '1']))) }}"
          class="deadline-card-link">
          <div class="deadline-card deadline-aman {{ $isCard1Active ? 'active' : '' }}">
            <div class="deadline-card-header">
              <div class="deadline-indicator">
                <span class="deadline-dot aman"></span>
              </div>
              <div class="deadline-count">{{ $cardStats['card1']['count'] ?? 0 }} Dokumen</div>
            </div>
            <div class="deadline-badge-wrapper">
              <span class="deadline-badge badge-aman">
                <i class="fas fa-check-circle"></i> AMAN
              </span>
            </div>
            <div class="deadline-info">
              <i class="fas fa-clock"></i> Diterima < 1 minggu yang lalu </div>
            </div>
        </a>

        <!-- Card PERINGATAN (1-3 Minggu - Yellow) -->
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['filter_age', 'page']), ['filter_age' => $isCard2Active ? '' : '2']))) }}"
          class="deadline-card-link">
          <div class="deadline-card deadline-peringatan {{ $isCard2Active ? 'active' : '' }}">
            <div class="deadline-card-header">
              <div class="deadline-indicator">
                <span class="deadline-dot peringatan"></span>
              </div>
              <div class="deadline-count">{{ $cardStats['card2']['count'] ?? 0 }} Dokumen</div>
            </div>
            <div class="deadline-badge-wrapper">
              <span class="deadline-badge badge-peringatan">
                <i class="fas fa-exclamation-triangle"></i> PERINGATAN
              </span>
            </div>
            <div class="deadline-info">
              <i class="fas fa-clock"></i> Diterima 1-3 minggu yang lalu
            </div>
          </div>
        </a>

        <!-- Card TERLAMBAT (> 3 Minggu - Red) -->
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['filter_age', 'page']), ['filter_age' => $isCard3Active ? '' : '3+']))) }}"
          class="deadline-card-link">
          <div class="deadline-card deadline-terlambat {{ $isCard3Active ? 'active' : '' }}">
            <div class="deadline-card-header">
              <div class="deadline-indicator">
                <span class="deadline-dot terlambat"></span>
              </div>
              <div class="deadline-count">{{ $cardStats['card3']['count'] ?? 0 }} Dokumen</div>
            </div>
            <div class="deadline-badge-wrapper">
              <span class="deadline-badge badge-terlambat">
                <i class="fas fa-exclamation-circle"></i> TERLAMBAT
              </span>
            </div>
            <div class="deadline-info">
              <i class="fas fa-clock"></i> Diterima > 3 minggu yang lalu
            </div>
          </div>
        </a>
      </div>
    @endif


    <!-- Status Filter Tabs -->
    @if(in_array($roleCode, ['team_verifikasi', 'perpajakan', 'akutansi']))
      @php
        $currentStatusFilter = request('status_filter', 'all');
      @endphp
      <div class="status-filter-tabs" style="margin: 1.5rem 0; display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['status_filter', 'page']), ['status_filter' => 'all']))) }}"
          class="status-tab {{ $currentStatusFilter === 'all' ? 'active' : '' }}"
          style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.875rem; transition: all 0.2s ease;
                                  {{ $currentStatusFilter === 'all' ? 'background: var(--primary-color); color: white;' : 'background: #f0f0f0; color: #333;' }}">
          <i class="fas fa-list"></i> Semua
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['status_filter', 'page']), ['status_filter' => 'active']))) }}"
          class="status-tab {{ $currentStatusFilter === 'active' ? 'active' : '' }}"
          style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.875rem; transition: all 0.2s ease;
                                  {{ $currentStatusFilter === 'active' ? 'background: #0d6efd; color: white;' : 'background: #f0f0f0; color: #333;' }}">
          <i class="fas fa-spinner"></i> Aktif (Sedang Diproses)
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['status_filter', 'page']), ['status_filter' => 'completed']))) }}"
          class="status-tab {{ $currentStatusFilter === 'completed' ? 'active' : '' }}"
          style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.875rem; transition: all 0.2s ease;
                                  {{ $currentStatusFilter === 'completed' ? 'background: #198754; color: white;' : 'background: #f0f0f0; color: #333;' }}">
          <i class="fas fa-check-circle"></i> Selesai (Sudah Dikirim)
        </a>
      </div>
    @endif

    <!-- Modern Filter Panel -->
    <div class="modern-filter-container">
      <!-- Filter Toggle Button -->
      <div class="filter-toggle-bar">
        <button type="button" class="filter-toggle-btn" id="filterToggleBtn" onclick="toggleFilterPanel()">
          <i class="fas fa-filter"></i>
          <span>Filter Lanjutan</span>
          <span class="filter-badge" id="activeFilterCount">0</span>
          <i class="fas fa-chevron-down" id="filterToggleIcon"></i>
        </button>
        <div class="view-switcher">
          <button class="view-switcher-btn active" data-view="card" onclick="switchView('card')">
            <i class="fas fa-th"></i> Kartu
          </button>
          <button class="view-switcher-btn" data-view="table" onclick="switchView('table')">
            <i class="fas fa-table"></i> Tabel
          </button>
        </div>
      </div>


      <!-- Filter Panel (Collapsible) -->
      <div class="filter-panel" id="filterPanel" style="display: none;">
        <form method="GET" action="{{ route('owner.rekapan-keterlambatan.role', $roleCode) }}" id="filterForm"
          class="filter-form">
          <input type="hidden" name="filter_age" value="{{ request('filter_age') }}">

          <!-- Search Bar -->
          <div class="filter-row">
            <div class="filter-group full-width">
              <label class="filter-label">
                <i class="fas fa-search"></i> Cari Dokumen
              </label>
              <input type="text" name="search" class="filter-input-search" value="{{ request('search') }}"
                placeholder="Cari berdasarkan nomor agenda, SPP, uraian, dll...">
            </div>
          </div>

          <!-- Filter Grid -->
          <div class="filter-grid">
            <!-- Bagian -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-building"></i> Bagian
              </label>
              <select name="filter_bagian" class="filter-select" onchange="applyFilter()">
                <option value="">Semua Bagian</option>
                @foreach($filterData['bagian'] ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_bagian') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            <!-- Vendor/Dibayar Kepada -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-handshake"></i> Vendor/Dibayar Kepada
              </label>
              <select name="filter_vendor" class="filter-select filter-searchable" onchange="applyFilter()">
                <option value="">Semua Vendor</option>
                @foreach($filterData['vendor'] ?? [] as $key => $value)
                  <option value="{{ $value }}" {{ request('filter_vendor') == $value ? 'selected' : '' }}>{{ $value }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Kriteria CF -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-tags"></i> Kriteria CF
              </label>
              <select name="filter_kriteria_cf" id="filterKriteriaCf" class="filter-select filter-searchable"
                onchange="updateSubKriteriaFilter(); applyFilter();">
                <option value="">Semua Kriteria CF</option>
                @foreach($filterData['kriteria_cf'] ?? [] as $id => $nama)
                  <option value="{{ $id }}" {{ request('filter_kriteria_cf') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
              </select>
            </div>

            <!-- Sub Kriteria -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-tag"></i> Sub Kriteria
              </label>
              <select name="filter_sub_kriteria" id="filterSubKriteria" class="filter-select filter-searchable"
                onchange="updateItemSubKriteriaFilter(); applyFilter();" disabled>
                <option value="">Pilih Kriteria CF terlebih dahulu</option>
                @foreach($filterData['sub_kriteria'] ?? [] as $id => $nama)
                  <option value="{{ $id }}"
                    data-kriteria-cf="{{ \App\Models\SubKriteria::on('cash_bank')->where('id_sub_kriteria', $id)->value('id_kategori_kriteria') ?? '' }}"
                    {{ request('filter_sub_kriteria') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
              </select>
            </div>

            <!-- Item Sub Kriteria -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-list"></i> Item Sub Kriteria
              </label>
              <select name="filter_item_sub_kriteria" id="filterItemSubKriteria" class="filter-select filter-searchable"
                onchange="applyFilter()" disabled>
                <option value="">Pilih Sub Kriteria terlebih dahulu</option>
                @foreach($filterData['item_sub_kriteria'] ?? [] as $id => $nama)
                  <option value="{{ $id }}"
                    data-sub-kriteria="{{ \App\Models\ItemSubKriteria::on('cash_bank')->where('id_item_sub_kriteria', $id)->value('id_sub_kriteria') ?? '' }}"
                    {{ request('filter_item_sub_kriteria') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
              </select>
            </div>

            <!-- Kebun -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-seedling"></i> Kebun
              </label>
              <select name="filter_kebun" class="filter-select filter-searchable" onchange="applyFilter()">
                <option value="">Semua Kebun</option>
                @foreach($filterData['kebun'] ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_kebun') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            <!-- Tahun -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-calendar"></i> Tahun
              </label>
              <select name="year" class="filter-select" onchange="applyFilter()">
                <option value="">Semua Tahun</option>
                @foreach($availableYears as $year)
                  <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Filter Actions -->
          <div class="filter-actions">
            <button type="button" class="filter-btn filter-btn-reset" onclick="resetFilters()">
              <i class="fas fa-redo"></i> Reset Filter
            </button>
            <button type="button" class="filter-btn filter-btn-apply" onclick="applyFilter()">
              <i class="fas fa-check"></i> Terapkan Filter
            </button>
          </div>

          <!-- Active Filters Badges -->
          <div class="active-filters" id="activeFilters">
            <!-- Will be populated by JavaScript -->
          </div>
        </form>
      </div>
    </div>

    <!-- Card View -->
    <div id="cardView" class="view-container active">
      @if($dokumens->count() == 0)
        <div class="empty-state" style="text-align: center; padding: 60px 20px;">
          <i class="fa-solid fa-folder-open fa-4x text-muted mb-3"></i>
          <h5 class="text-muted">Tidak ada dokumen</h5>
          <p class="text-muted">Dokumen akan ditampilkan di sini ketika tersedia</p>
        </div>
      @else
        <div class="card-view-container">
          @foreach($dokumens as $index => $dokumen)
            @php
              $ageDays = $dokumen->age_days ?? 0;
              $ageHours = $dokumen->age_hours ?? 0;
              $ageFormatted = $dokumen->age_formatted ?? '-';

              // Determine card color based on age in hours (matching dashboard)
              $ageColor = 'green';
              if (in_array($roleCode, ['team_verifikasi', 'perpajakan', 'akutansi'])) {
                if ($ageHours >= 72) {
                  $ageColor = 'red';  // TERLAMBAT
                } elseif ($ageHours >= 24) {
                  $ageColor = 'yellow';  // PERINGATAN
                } else {
                  $ageColor = 'green';  // AMAN
                }
              }

              // Get received_at for display
              $receivedAt = '-';
              if (isset($dokumen->effective_received_at) && $dokumen->effective_received_at) {
                $receivedAt = \Carbon\Carbon::parse($dokumen->effective_received_at)->format('d M Y H:i');
              } elseif (isset($dokumen->delay_received_at) && $dokumen->delay_received_at) {
                $receivedAt = \Carbon\Carbon::parse($dokumen->delay_received_at)->format('d M Y H:i');
              } elseif ($roleCode === 'pembayaran' && isset($dokumen->sent_to_pembayaran_at) && $dokumen->sent_to_pembayaran_at) {
                $receivedAt = \Carbon\Carbon::parse($dokumen->sent_to_pembayaran_at)->format('d M Y H:i');
              }
            @endphp
            <div class="document-card document-card-{{ $ageColor }}"
              onclick="window.location.href='@if(in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'owner'])){{ route('owner.workflow', ['id' => $dokumen->id]) }}@elseif($roleCode === 'team_verifikasi'){{ route('documents.verifikasi.index', ['search' => $dokumen->nomor_agenda]) }}@elseif($roleCode === 'perpajakan'){{ route('documents.perpajakan.index', ['search' => $dokumen->nomor_agenda]) }}@elseif($roleCode === 'akutansi'){{ route('documents.akutansi.index', ['search' => $dokumen->nomor_agenda]) }}@elseif($roleCode === 'pembayaran'){{ route('documents.pembayaran.index', ['search' => $dokumen->nomor_agenda]) }}@else{{ route('owner.workflow', ['id' => $dokumen->id]) }}@endif'"
              title="{{ in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'owner']) ? 'Klik untuk melihat detail workflow dokumen' : 'Klik untuk melihat dokumen di daftar dokumen' }}">
              <div class="card-header">
                <div>
                  <div class="card-title">{{ $dokumen->nomor_agenda }}</div>
                  <div class="card-subtitle">SPP: {{ $dokumen->nomor_spp }}</div>
                </div>
                @if(isset($dokumen->is_completed))
                  <span class="badge {{ $dokumen->is_completed ? 'badge-completed' : 'badge-active' }}"
                    style="font-size: 0.7rem; padding: 4px 10px; border-radius: 12px;
                                                       {{ $dokumen->is_completed ? 'background: #198754; color: white;' : 'background: #0d6efd; color: white;' }}">
                    <i class="fas {{ $dokumen->is_completed ? 'fa-check-circle' : 'fa-spinner' }}"></i>
                    {{ $dokumen->is_completed ? 'Selesai' : 'Aktif' }}
                  </span>
                @endif
              </div>
              <div class="card-value">
                Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}
              </div>
              <div class="card-info-row">
                <i class="fas fa-calendar"></i>
                <span>Tanggal Masuk:</span>
                <span>{{ $receivedAt }}</span>
              </div>
              <div class="card-info-row">
                <i class="fas fa-clock"></i>
                <span>Waktu Proses:</span>
                <span class="age-badge age-{{ $ageColor }}">
                  {{ $ageFormatted }}
                  @if(isset($dokumen->is_completed) && $dokumen->is_completed)
                    <i class="fas fa-lock" title="Waktu permanen"></i>
                  @endif
                </span>
              </div>
              <div class="card-info-row">
                <i class="fas fa-info-circle"></i>
                <span>Status:</span>
                @if(isset($dokumen->is_completed) && $dokumen->is_completed)
                  <span class="badge"
                    style="background: #198754; color: white; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px;">
                    <i class="fas fa-paper-plane"></i> Sudah Dikirim
                  </span>
                @else
                  <span class="badge"
                    style="background: #0d6efd; color: white; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px;">
                    <i class="fas fa-spinner"></i> Sedang Diproses
                  </span>
                @endif
              </div>
            </div>

          @endforeach
        </div>
      @endif

      <!-- Pagination -->
      @if($dokumens->hasPages())
        <div class="pagination">
          @if($dokumens->onFirstPage())
            <span class="page-link disabled">«</span>
          @else
            <a href="{{ $dokumens->previousPageUrl() }}" class="page-link">«</a>
          @endif

          @for($i = 1; $i <= $dokumens->lastPage(); $i++)
            @if($i == $dokumens->currentPage())
              <span class="page-link active">{{ $i }}</span>
            @else
              <a href="{{ $dokumens->url($i) }}" class="page-link">{{ $i }}</a>
            @endif
          @endfor

          @if($dokumens->hasMorePages())
            <a href="{{ $dokumens->nextPageUrl() }}" class="page-link">»</a>
          @else
            <span class="page-link disabled">»</span>
          @endif
        </div>
      @endif
    </div>

    <!-- Table View -->
    <div id="tableView" class="view-container">
      @if($dokumens->count() == 0)
        <div class="empty-state" style="text-align: center; padding: 60px 20px;">
          <i class="fa-solid fa-folder-open fa-4x text-muted mb-3"></i>
          <h5 class="text-muted">Tidak ada dokumen</h5>
          <p class="text-muted">Dokumen akan ditampilkan di sini ketika tersedia</p>
        </div>
      @else
        <div class="table-container">
          <h6 style="font-size: 18px; font-weight: 700; color: var(--primary-color); margin-bottom: 24px;">
            <span>Daftar Dokumen (Total: {{ $totalDocuments }})</span>
          </h6>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nomor Agenda</th>
                  <th>Nomor SPP</th>
                  <th>Tanggal Masuk</th>
                  <th>Umur Dokumen</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($dokumens as $index => $dokumen)
                  @php
                    $ageDays = $dokumen->age_days ?? 0;
                    $ageHours = $dokumen->age_hours ?? 0;
                    $ageFormatted = $dokumen->age_formatted ?? '-';

                    // Determine row color based on age in hours (matching dashboard)
                    $ageColor = 'green';
                    if (in_array($roleCode, ['team_verifikasi', 'perpajakan', 'akutansi'])) {
                      if ($ageHours >= 72) {
                        $ageColor = 'red';  // TERLAMBAT
                      } elseif ($ageHours >= 24) {
                        $ageColor = 'yellow';  // PERINGATAN
                      } else {
                        $ageColor = 'green';  // AMAN
                      }
                    }

                    // Get received_at for display
                    $receivedAt = '-';
                    if (isset($dokumen->delay_received_at) && $dokumen->delay_received_at) {
                      $receivedAt = \Carbon\Carbon::parse($dokumen->delay_received_at)->format('d M Y H:i');
                    } elseif ($roleCode === 'pembayaran' && isset($dokumen->sent_to_pembayaran_at) && $dokumen->sent_to_pembayaran_at) {
                      $receivedAt = \Carbon\Carbon::parse($dokumen->sent_to_pembayaran_at)->format('d M Y H:i');
                    }
                  @endphp
                  <tr class="clickable-row table-row-{{ $ageColor }}"
                    onclick="window.location.href='@if(in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'owner'])){{ route('owner.workflow', ['id' => $dokumen->id]) }}@elseif($roleCode === 'team_verifikasi'){{ route('documents.verifikasi.index', ['search' => $dokumen->nomor_agenda]) }}@elseif($roleCode === 'perpajakan'){{ route('documents.perpajakan.index', ['search' => $dokumen->nomor_agenda]) }}@elseif($roleCode === 'akutansi'){{ route('documents.akutansi.index', ['search' => $dokumen->nomor_agenda]) }}@elseif($roleCode === 'pembayaran'){{ route('documents.pembayaran.index', ['search' => $dokumen->nomor_agenda]) }}@else{{ route('owner.workflow', ['id' => $dokumen->id]) }}@endif'"
                    title="{{ in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'owner']) ? 'Klik untuk melihat detail workflow dokumen' : 'Klik untuk melihat dokumen di daftar dokumen' }}">

                    <td>{{ $dokumens->firstItem() + $index }}</td>
                    <td>{{ $dokumen->nomor_agenda }}</td>
                    <td>{{ $dokumen->nomor_spp }}</td>
                    <td>{{ $receivedAt }}</td>
                    <td>
                      <span class="age-badge age-{{ $ageColor }}">
                        {{ $ageFormatted }}
                        @if(isset($dokumen->is_completed) && $dokumen->is_completed)
                          <i class="fas fa-lock" title="Waktu permanen"></i>
                        @endif
                      </span>
                    </td>
                    <td>
                      @if(isset($dokumen->is_completed) && $dokumen->is_completed)
                        <span class="badge"
                          style="background: #198754; color: white; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px;">
                          <i class="fas fa-paper-plane"></i> Selesai
                        </span>
                      @else
                        <span class="badge"
                          style="background: #0d6efd; color: white; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px;">
                          <i class="fas fa-spinner"></i> Aktif
                        </span>
                      @endif
                    </td>
                  </tr>

                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        @if($dokumens->hasPages())
          <div class="pagination">
            @if($dokumens->onFirstPage())
              <span class="page-link disabled">«</span>
            @else
              <a href="{{ $dokumens->previousPageUrl() }}" class="page-link">«</a>
            @endif

            @for($i = 1; $i <= $dokumens->lastPage(); $i++)
              @if($i == $dokumens->currentPage())
                <span class="page-link active">{{ $i }}</span>
              @else
                <a href="{{ $dokumens->url($i) }}" class="page-link">{{ $i }}</a>
              @endif
            @endfor

            @if($dokumens->hasMorePages())
              <a href="{{ $dokumens->nextPageUrl() }}" class="page-link">»</a>
            @else
              <span class="page-link disabled">»</span>
            @endif
          </div>
        @endif
      @endif
    </div>
  </div>

  <script>
    // Filter Panel Functionality
    let filterPanelExpanded = false;

    function toggleFilterPanel() {
      const panel = document.getElementById('filterPanel');
      const icon = document.getElementById('filterToggleIcon');
      filterPanelExpanded = !filterPanelExpanded;

      if (filterPanelExpanded) {
        panel.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
      } else {
        panel.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
      }

      updateActiveFilterCount();
    }

    function applyFilter() {
      const form = document.getElementById('filterForm');
      form.submit();
    }

    function resetFilters() {
      const currentUrl = new URL(window.location.href);
      const params = currentUrl.searchParams;
      const filterAge = params.get('filter_age');

      // Clear all filter_ parameters
      for (let key of Array.from(params.keys())) {
        if (key.startsWith('filter_') || key === 'search' || key === 'year') {
          params.delete(key);
        }
      }

      // Preserve filter_age if exists
      if (filterAge) {
        params.set('filter_age', filterAge);
      }

      window.location.href = currentUrl.toString();
    }

    function updateActiveFilterCount() {
      const form = document.getElementById('filterForm');
      const formData = new FormData(form);
      let count = 0;

      // Count active filters
      for (let [key, value] of formData.entries()) {
        if (key.startsWith('filter_') && value && value !== '') {
          count++;
        }
        if (key === 'year' && value && value !== '') {
          count++;
        }
        if (key === 'search' && value && value !== '') {
          count++;
        }
      }

      const badge = document.getElementById('activeFilterCount');
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';

      updateActiveFilterBadges();
    }

    function updateActiveFilterBadges() {
      const form = document.getElementById('filterForm');
      const formData = new FormData(form);
      const badgesContainer = document.getElementById('activeFilters');
      badgesContainer.innerHTML = '';

      const filterLabels = {
        'filter_bagian': 'Bagian',
        'filter_vendor': 'Vendor',
        'filter_kriteria_cf': 'Kriteria CF',
        'filter_sub_kriteria': 'Sub Kriteria',
        'filter_item_sub_kriteria': 'Item Sub Kriteria',
        'filter_kebun': 'Kebun',
        'year': 'Tahun',
        'search': 'Pencarian',
        'filter_age': 'Umur Dokumen'
      };

      const filterAgeLabels = {
        '1': '1 Hari',
        '2': '2 Hari',
        '3+': '3+ Hari'
      };

      for (let [key, value] of formData.entries()) {
        if ((key.startsWith('filter_') || key === 'year' || key === 'search') && value && value !== '') {
          const label = filterLabels[key] || key;
          let displayValue = getFilterDisplayValue(key, value);
          if (key === 'filter_age') {
            displayValue = filterAgeLabels[value] || value;
          }
          const badge = document.createElement('span');
          badge.className = 'filter-badge-item';
          badge.innerHTML = `
                    <span>${label}: ${displayValue}</span>
                    <button type="button" class="remove-btn" onclick="removeFilter('${key}')">
                      <i class="fas fa-times"></i>
                    </button>
                  `;
          badgesContainer.appendChild(badge);
        }
      }
    }

    function getFilterDisplayValue(key, value) {
      // Get display value from select options
      const select = document.querySelector(`[name="${key}"]`);
      if (select && select.options) {
        const option = Array.from(select.options).find(opt => opt.value === value);
        if (option) return option.text;
      }
      return value;
    }

    function removeFilter(key) {
      const input = document.querySelector(`[name="${key}"]`);
      if (input) {
        input.value = '';
        applyFilter();
      }
    }

    // Cascading dropdowns for Kriteria CF, Sub Kriteria, Item Sub Kriteria
    function updateSubKriteriaFilter() {
      const kriteriaCfId = document.getElementById('filterKriteriaCf').value;
      const subKriteriaSelect = document.getElementById('filterSubKriteria');
      const itemSubKriteriaSelect = document.getElementById('filterItemSubKriteria');

      // Enable/disable Sub Kriteria based on Kriteria CF selection
      if (kriteriaCfId && kriteriaCfId !== '') {
        subKriteriaSelect.disabled = false;
        subKriteriaSelect.style.opacity = '1';
        subKriteriaSelect.style.cursor = 'pointer';

        // Show/hide options based on selected kriteria CF
        Array.from(subKriteriaSelect.options).forEach(option => {
          if (option.value === '') {
            option.style.display = 'block';
            return;
          }
          const kriteriaCfIdForOption = option.getAttribute('data-kriteria-cf');
          if (kriteriaCfIdForOption === kriteriaCfId) {
            option.style.display = 'block';
          } else {
            option.style.display = 'none';
          }
        });
      } else {
        // Disable Sub Kriteria and reset value if Kriteria CF is not selected
        subKriteriaSelect.disabled = true;
        subKriteriaSelect.style.opacity = '0.6';
        subKriteriaSelect.style.cursor = 'not-allowed';
        subKriteriaSelect.value = '';

        // Also disable and reset Item Sub Kriteria
        itemSubKriteriaSelect.disabled = true;
        itemSubKriteriaSelect.style.opacity = '0.6';
        itemSubKriteriaSelect.style.cursor = 'not-allowed';
        itemSubKriteriaSelect.value = '';

        // Show all options when disabled
        Array.from(subKriteriaSelect.options).forEach(option => {
          option.style.display = 'block';
        });
      }

      // Update Item Sub Kriteria filter
      updateItemSubKriteriaFilter();
    }

    function updateItemSubKriteriaFilter() {
      const subKriteriaId = document.getElementById('filterSubKriteria').value;
      const itemSubKriteriaSelect = document.getElementById('filterItemSubKriteria');
      const subKriteriaSelect = document.getElementById('filterSubKriteria');

      // Enable/disable Item Sub Kriteria based on Sub Kriteria selection
      if (subKriteriaId && subKriteriaId !== '' && !subKriteriaSelect.disabled) {
        itemSubKriteriaSelect.disabled = false;
        itemSubKriteriaSelect.style.opacity = '1';
        itemSubKriteriaSelect.style.cursor = 'pointer';

        // Show/hide options based on selected sub kriteria
        Array.from(itemSubKriteriaSelect.options).forEach(option => {
          if (option.value === '') {
            option.style.display = 'block';
            return;
          }
          const subKriteriaIdForOption = option.getAttribute('data-sub-kriteria');
          if (subKriteriaIdForOption === subKriteriaId) {
            option.style.display = 'block';
          } else {
            option.style.display = 'none';
          }
        });
      } else {
        // Disable Item Sub Kriteria and reset value if Sub Kriteria is not selected
        itemSubKriteriaSelect.disabled = true;
        itemSubKriteriaSelect.style.opacity = '0.6';
        itemSubKriteriaSelect.style.cursor = 'not-allowed';
        itemSubKriteriaSelect.value = '';

        // Show all options when disabled
        Array.from(itemSubKriteriaSelect.options).forEach(option => {
          option.style.display = 'block';
        });
      }
    }

    // View Switcher
    function switchView(view) {
      // Update buttons
      document.querySelectorAll('.view-switcher-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      document.querySelector(`[data-view="${view}"]`).classList.add('active');

      // Update views
      document.getElementById('cardView').classList.toggle('active', view === 'card');
      document.getElementById('tableView').classList.toggle('active', view === 'table');

      // Save preference
      localStorage.setItem('rekapanKeterlambatanView', view);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
      // Load saved view preference
      const savedView = localStorage.getItem('rekapanKeterlambatanView') || 'card';
      switchView(savedView);

      // Initialize filter count and panel state
      updateActiveFilterCount();

      // Initialize cascading dropdowns
      updateSubKriteriaFilter();
      updateItemSubKriteriaFilter();

      // Auto-expand filter panel if filters are active
      const form = document.getElementById('filterForm');
      const formData = new FormData(form);
      let hasActiveFilters = false;
      for (let [key, value] of formData.entries()) {
        if ((key.startsWith('filter_') || key === 'year' || key === 'search') && value && value !== '') {
          hasActiveFilters = true;
          break;
        }
      }

      if (hasActiveFilters) {
        toggleFilterPanel();
      }
    });
  </script>

@endsection




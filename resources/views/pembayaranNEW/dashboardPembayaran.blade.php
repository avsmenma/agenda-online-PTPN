@extends('layouts.app')

@section('content')
  <style>
    /* ===== AGENDA GREEN THEME ===== */
    :root {
      --agenda-primary: #1a5d1a;
      --agenda-primary-light: #2e8b2e;
      --agenda-primary-dark: #0d3d0d;
      --agenda-accent: #4ade80;
      --agenda-accent-light: #86efac;
      --agenda-gradient-start: #166534;
      --agenda-gradient-end: #15803d;
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
      --shadow-glass: 0 8px 32px rgba(26, 93, 26, 0.15);
      --shadow-card: 0 4px 24px rgba(0, 0, 0, 0.08);
      --shadow-card-hover: 0 12px 40px rgba(0, 0, 0, 0.12);
    }

    /* ===== DASHBOARD CONTAINER ===== */
    .dashboard-container {
      padding: 1.5rem;
      background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f0fdf4 100%);
      min-height: 100vh;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .page-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--agenda-primary-dark);
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .page-title i {
      color: var(--agenda-primary);
    }

    .page-subtitle {
      color: #6b7280;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    /* ===== STATISTICS CARDS ===== */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    @media (max-width: 1200px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 576px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }

    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: var(--shadow-card);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      border: 1px solid transparent;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--card-accent, var(--agenda-primary)) 0%, var(--card-accent-light, var(--agenda-accent)) 100%);
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-card-hover);
      border-color: var(--agenda-accent-light);
    }

    .stat-card.active {
      border: 2px solid var(--agenda-primary);
      background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
    }

    .stat-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .stat-card-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--card-accent, var(--agenda-primary)) 0%, var(--card-accent-light, var(--agenda-accent)) 100%);
      color: white;
      font-size: 1.25rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-card-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-card-title {
      font-size: 0.875rem;
      color: #6b7280;
      font-weight: 500;
      margin-bottom: 0.5rem;
    }

    .stat-card-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--agenda-primary-dark);
      line-height: 1.2;
    }

    .stat-card-subtitle {
      font-size: 0.8rem;
      color: #9ca3af;
      margin-top: 0.5rem;
      font-weight: 500;
    }

    /* Color variants for stat cards */
    .stat-card.total {
      --card-accent: #1d4ed8;
      --card-accent-light: #60a5fa;
    }

    .stat-card.total .stat-card-icon {
      background: linear-gradient(135deg, #1d4ed8, #60a5fa);
    }

    .stat-card.total .stat-card-value {
      color: #1d4ed8;
    }

    .stat-card.belum {
      --card-accent: #f59e0b;
      --card-accent-light: #fcd34d;
    }

    .stat-card.belum .stat-card-icon {
      background: linear-gradient(135deg, #f59e0b, #fcd34d);
    }

    .stat-card.belum .stat-card-value {
      color: #d97706;
    }

    .stat-card.siap {
      --card-accent: #10b981;
      --card-accent-light: #34d399;
    }

    .stat-card.siap .stat-card-icon {
      background: linear-gradient(135deg, #10b981, #34d399);
    }

    .stat-card.siap .stat-card-value {
      color: #059669;
    }

    .stat-card.sudah {
      --card-accent: #8b5cf6;
      --card-accent-light: #a78bfa;
    }

    .stat-card.sudah .stat-card-icon {
      background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    }

    .stat-card.sudah .stat-card-value {
      color: #7c3aed;
    }

    /* ===== DEADLINE CARDS ===== */
    .deadline-section {
      margin-bottom: 2rem;
    }

    .deadline-section-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--agenda-primary-dark);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .deadline-section-title i {
      color: var(--agenda-primary);
    }

    .deadline-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
    }

    @media (max-width: 768px) {
      .deadline-grid {
        grid-template-columns: 1fr;
      }
    }

    .deadline-card {
      background: white;
      border-radius: 12px;
      padding: 1.25rem;
      box-shadow: var(--shadow-card);
      transition: all 0.3s ease;
      border-left: 4px solid;
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .deadline-card:hover {
      transform: translateX(4px);
      box-shadow: var(--shadow-card-hover);
    }

    .deadline-card.aman {
      border-left-color: #22c55e;
    }

    .deadline-card.aman .deadline-icon {
      background: linear-gradient(135deg, #22c55e, #4ade80);
    }

    .deadline-card.peringatan {
      border-left-color: #f59e0b;
    }

    .deadline-card.peringatan .deadline-icon {
      background: linear-gradient(135deg, #f59e0b, #fbbf24);
    }

    .deadline-card.terlambat {
      border-left-color: #ef4444;
    }

    .deadline-card.terlambat .deadline-icon {
      background: linear-gradient(135deg, #ef4444, #f87171);
    }

    .deadline-icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.125rem;
      flex-shrink: 0;
    }

    .deadline-info {
      flex: 1;
    }

    .deadline-label {
      font-size: 0.8rem;
      color: #6b7280;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .deadline-count {
      font-size: 1.5rem;
      font-weight: 700;
      color: #1f2937;
    }

    .deadline-desc {
      font-size: 0.75rem;
      color: #9ca3af;
    }

    /* ===== FILTER SECTION ===== */
    .filter-section {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: var(--shadow-card);
      margin-bottom: 2rem;
    }

    .filter-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .filter-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--agenda-primary-dark);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .filter-title i {
      color: var(--agenda-primary);
    }

    .filter-actions {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .filter-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }

    @media (max-width: 992px) {
      .filter-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 576px) {
      .filter-grid {
        grid-template-columns: 1fr;
      }
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .filter-label {
      font-size: 0.75rem;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .filter-input {
      padding: 0.625rem 1rem;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      font-size: 0.875rem;
      transition: all 0.2s ease;
      background: #f9fafb;
    }

    .filter-input:focus {
      outline: none;
      border-color: var(--agenda-primary);
      background: white;
      box-shadow: 0 0 0 3px rgba(26, 93, 26, 0.1);
    }

    .filter-input::placeholder {
      color: #9ca3af;
    }

    /* ===== BUTTONS ===== */
    .btn-primary-agenda {
      background: linear-gradient(135deg, var(--agenda-primary) 0%, var(--agenda-primary-light) 100%);
      color: white;
      border: none;
      padding: 0.625rem 1.25rem;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.875rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .btn-primary-agenda:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(26, 93, 26, 0.3);
      color: white;
    }

    .btn-secondary-agenda {
      background: white;
      color: var(--agenda-primary);
      border: 1.5px solid var(--agenda-primary);
      padding: 0.625rem 1.25rem;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.875rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .btn-secondary-agenda:hover {
      background: var(--agenda-primary);
      color: white;
    }

    .btn-sm {
      padding: 0.5rem 1rem;
      font-size: 0.8rem;
    }

    /* ===== TABLE SECTION ===== */
    .table-section {
      background: white;
      border-radius: 16px;
      box-shadow: var(--shadow-card);
      overflow: hidden;
    }

    .table-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    }

    .table-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--agenda-primary-dark);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .table-title i {
      color: var(--agenda-primary);
    }

    .table-count {
      font-size: 0.8rem;
      color: #6b7280;
      font-weight: normal;
    }

    .table-responsive-custom {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }

    .data-table thead th {
      background: linear-gradient(135deg, var(--agenda-primary) 0%, var(--agenda-primary-light) 100%);
      color: white;
      padding: 1rem 1.25rem;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      text-align: left;
      white-space: nowrap;
      border: none;
    }

    .data-table thead th:first-child {
      border-radius: 0;
    }

    .data-table thead th:last-child {
      border-radius: 0;
    }

    .data-table tbody tr {
      transition: all 0.2s ease;
    }

    .data-table tbody tr:hover {
      background: #f0fdf4;
    }

    .data-table tbody td {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid #f3f4f6;
      font-size: 0.875rem;
      color: #374151;
      vertical-align: middle;
    }

    .data-table tbody tr:last-child td {
      border-bottom: none;
    }

    /* Status badges */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.375rem 0.875rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-badge.siap_dibayar {
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
      color: #166534;
    }

    .status-badge.sudah_dibayar {
      background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
      color: #5b21b6;
    }

    .status-badge.belum_siap_dibayar {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #92400e;
    }

    /* Rupiah values */
    .rupiah-value {
      font-family: 'JetBrains Mono', 'Fira Code', monospace;
      font-weight: 600;
      color: var(--agenda-primary-dark);
    }

    .rupiah-value.large {
      font-size: 1.125rem;
    }

    /* ===== PAGINATION ===== */
    .pagination-wrapper {
      padding: 1.25rem 1.5rem;
      border-top: 1px solid #e5e7eb;
      display: flex;
      justify-content: between;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .pagination-info {
      font-size: 0.875rem;
      color: #6b7280;
    }

    .pagination {
      display: flex;
      gap: 0.25rem;
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .page-item .page-link {
      padding: 0.5rem 0.875rem;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      font-size: 0.875rem;
      color: #374151;
      background: white;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .page-item .page-link:hover {
      background: #f0fdf4;
      border-color: var(--agenda-primary);
      color: var(--agenda-primary);
    }

    .page-item.active .page-link {
      background: var(--agenda-primary);
      border-color: var(--agenda-primary);
      color: white;
    }

    .page-item.disabled .page-link {
      color: #9ca3af;
      background: #f9fafb;
      cursor: not-allowed;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
      padding: 4rem 2rem;
      text-align: center;
    }

    .empty-state-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
    }

    .empty-state-icon i {
      font-size: 2rem;
      color: var(--agenda-primary);
    }

    .empty-state-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
    }

    .empty-state-desc {
      font-size: 0.875rem;
      color: #6b7280;
    }

    /* ===== VENDOR GROUP TABLE ===== */
    .vendor-group {
      margin-bottom: 1rem;
    }

    .vendor-group-header {
      background: linear-gradient(135deg, var(--agenda-primary) 0%, var(--agenda-primary-light) 100%);
      color: white;
      padding: 1rem 1.25rem;
      border-radius: 10px 10px 0 0;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.2s ease;
    }

    .vendor-group-header:hover {
      filter: brightness(1.1);
    }

    .vendor-group-header h4 {
      margin: 0;
      font-size: 0.95rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .vendor-group-header .stats {
      display: flex;
      gap: 1.5rem;
      font-size: 0.8rem;
      font-weight: 500;
    }

    .vendor-group-body {
      border: 1px solid #e5e7eb;
      border-top: none;
      border-radius: 0 0 10px 10px;
      overflow: hidden;
    }

    /* ===== ANIMATIONS ===== */
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

    .animate-fade-in {
      animation: fadeInUp 0.4s ease-out forwards;
    }

    .animate-delay-1 {
      animation-delay: 0.1s;
    }

    .animate-delay-2 {
      animation-delay: 0.2s;
    }

    .animate-delay-3 {
      animation-delay: 0.3s;
    }

    .animate-delay-4 {
      animation-delay: 0.4s;
    }

    /* ===== MODE TOGGLE ===== */
    .mode-toggle {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.5rem 1rem;
      background: #f3f4f6;
      border-radius: 10px;
    }

    .mode-toggle-label {
      font-size: 0.875rem;
      font-weight: 500;
      color: #374151;
    }

    .toggle-switch {
      position: relative;
      width: 48px;
      height: 26px;
    }

    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .toggle-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #cbd5e1;
      transition: 0.3s;
      border-radius: 26px;
    }

    .toggle-slider:before {
      position: absolute;
      content: "";
      height: 20px;
      width: 20px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: 0.3s;
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    input:checked+.toggle-slider {
      background: linear-gradient(135deg, var(--agenda-primary) 0%, var(--agenda-primary-light) 100%);
    }

    input:checked+.toggle-slider:before {
      transform: translateX(22px);
    }

    /* ===== TOOLTIP ===== */
    [data-tooltip] {
      position: relative;
    }

    [data-tooltip]:hover::after {
      content: attr(data-tooltip);
      position: absolute;
      bottom: 100%;
      left: 50%;
      transform: translateX(-50%);
      padding: 0.5rem 0.75rem;
      background: #1f2937;
      color: white;
      font-size: 0.75rem;
      border-radius: 6px;
      white-space: nowrap;
      z-index: 100;
      margin-bottom: 0.5rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
      .dashboard-container {
        padding: 1rem;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .page-title {
        font-size: 1.5rem;
      }

      .filter-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .filter-actions {
        width: 100%;
      }

      .filter-actions .btn-primary-agenda,
      .filter-actions .btn-secondary-agenda {
        flex: 1;
        justify-content: center;
      }
    }

    /* Print styles */
    @media print {
      .dashboard-container {
        background: white !important;
        padding: 0 !important;
      }

      .filter-section,
      .page-header,
      .deadline-section {
        display: none !important;
      }

      .table-section {
        box-shadow: none !important;
      }
    }
  </style>

  <div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">
          <i class="fas fa-wallet"></i>
          Dashboard Pembayaran
        </h1>
        <p class="page-subtitle">Kelola dan pantau semua dokumen pembayaran</p>
      </div>
      <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('pembayaran.dokumens.export', ['format' => 'excel']) }}" class="btn-secondary-agenda btn-sm">
          <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('pembayaran.dokumens.export', ['format' => 'pdf']) }}" class="btn-secondary-agenda btn-sm">
          <i class="fas fa-file-pdf"></i> Export PDF
        </a>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <a href="?status_pembayaran=&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card total animate-fade-in animate-delay-1 {{ !$selectedStatus ? 'active' : '' }}">
        <div class="stat-card-header">
          <div class="stat-card-icon">
            <i class="fas fa-file-invoice"></i>
          </div>
        </div>
        <div class="stat-card-title">Total Dokumen</div>
        <div class="stat-card-value">{{ number_format($statistics['total_documents']) }}</div>
        <div class="stat-card-subtitle">
          Rp {{ number_format($statistics['total_nilai'], 0, ',', '.') }}
        </div>
      </a>

      <a href="?status_pembayaran=belum_siap_dibayar&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card belum animate-fade-in animate-delay-2 {{ $selectedStatus == 'belum_siap_dibayar' ? 'active' : '' }}">
        <div class="stat-card-header">
          <div class="stat-card-icon">
            <i class="fas fa-hourglass-half"></i>
          </div>
        </div>
        <div class="stat-card-title">Belum Siap Bayar</div>
        <div class="stat-card-value">{{ number_format($statistics['by_status']['belum_dibayar']) }}</div>
        <div class="stat-card-subtitle">
          Rp {{ number_format($statistics['total_nilai_by_status']['belum_dibayar'], 0, ',', '.') }}
        </div>
      </a>

      <a href="?status_pembayaran=siap_dibayar&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card siap animate-fade-in animate-delay-3 {{ $selectedStatus == 'siap_dibayar' ? 'active' : '' }}">
        <div class="stat-card-header">
          <div class="stat-card-icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
        <div class="stat-card-title">Siap Dibayar</div>
        <div class="stat-card-value">{{ number_format($statistics['by_status']['siap_dibayar']) }}</div>
        <div class="stat-card-subtitle">
          Rp {{ number_format($statistics['total_nilai_by_status']['siap_dibayar'], 0, ',', '.') }}
        </div>
      </a>

      <a href="?status_pembayaran=sudah_dibayar&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card sudah animate-fade-in animate-delay-4 {{ $selectedStatus == 'sudah_dibayar' ? 'active' : '' }}">
        <div class="stat-card-header">
          <div class="stat-card-icon">
            <i class="fas fa-money-check-alt"></i>
          </div>
        </div>
        <div class="stat-card-title">Sudah Dibayar</div>
        <div class="stat-card-value">{{ number_format($statistics['by_status']['sudah_dibayar']) }}</div>
        <div class="stat-card-subtitle">
          Rp {{ number_format($statistics['total_nilai_by_status']['sudah_dibayar'], 0, ',', '.') }}
        </div>
      </a>
    </div>

    <!-- Deadline Cards -->
    <div class="deadline-section">
      <h2 class="deadline-section-title">
        <i class="fas fa-clock"></i>
        Status Keterlambatan
      </h2>
      <div class="deadline-grid">
        <a href="{{ route('pembayaran.dokumens.index', ['status_keterlambatan' => 'aman']) }}" class="deadline-card aman">
          <div class="deadline-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="deadline-info">
            <div class="deadline-label">Aman</div>
            <div class="deadline-count">{{ number_format($totalAman) }}</div>
            <div class="deadline-desc">&lt; 1 Minggu</div>
          </div>
        </a>

        <a href="{{ route('pembayaran.dokumens.index', ['status_keterlambatan' => 'peringatan']) }}"
          class="deadline-card peringatan">
          <div class="deadline-icon">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div class="deadline-info">
            <div class="deadline-label">Peringatan</div>
            <div class="deadline-count">{{ number_format($totalPeringatan) }}</div>
            <div class="deadline-desc">1 - 3 Minggu</div>
          </div>
        </a>

        <a href="{{ route('pembayaran.dokumens.index', ['status_keterlambatan' => 'terlambat']) }}"
          class="deadline-card terlambat">
          <div class="deadline-icon">
            <i class="fas fa-times-circle"></i>
          </div>
          <div class="deadline-info">
            <div class="deadline-label">Terlambat</div>
            <div class="deadline-count">{{ number_format($totalTerlambat) }}</div>
            <div class="deadline-desc">&gt; 3 Minggu</div>
          </div>
        </a>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
      <div class="filter-header">
        <div class="filter-title">
          <i class="fas fa-filter"></i>
          Filter Dokumen
        </div>
        <div class="filter-actions">
          <div class="mode-toggle">
            <span class="mode-toggle-label">Group by Vendor</span>
            <label class="toggle-switch">
              <input type="checkbox" id="modeToggle" {{ $mode == 'rekapan_table' ? 'checked' : '' }}>
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>
      </div>

      <form method="GET" action="" id="filterForm">
        <input type="hidden" name="mode" id="modeInput" value="{{ $mode }}">

        <div class="filter-grid">
          <div class="filter-group">
            <label class="filter-label">Status</label>
            <select name="status_pembayaran" class="filter-input" onchange="this.form.submit()">
              <option value="">Semua Status</option>
              <option value="siap_dibayar" {{ $selectedStatus == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
              <option value="sudah_dibayar" {{ $selectedStatus == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Tahun</label>
            <select name="year" class="filter-input" onchange="this.form.submit()">
              <option value="">Semua Tahun</option>
              @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Bulan</label>
            <select name="month" class="filter-input" onchange="this.form.submit()">
              <option value="">Semua Bulan</option>
              @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
                  {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                </option>
              @endfor
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Pencarian</label>
            <input type="text" name="search" class="filter-input" placeholder="Cari no agenda, SPP, vendor..."
              value="{{ $search ?? '' }}">
          </div>
        </div>

        <div style="margin-top: 1rem; display: flex; gap: 0.75rem; justify-content: flex-end;">
          <button type="submit" class="btn-primary-agenda">
            <i class="fas fa-search"></i>
            Cari
          </button>
          <a href="{{ route('pembayaran.dashboard') }}" class="btn-secondary-agenda">
            <i class="fas fa-redo"></i>
            Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Table Section -->
    <div class="table-section">
      <div class="table-header">
        <div class="table-title">
          <i class="fas fa-list-alt"></i>
          Daftar Dokumen
          <span class="table-count">({{ $dokumens->total() }} dokumen)</span>
        </div>
      </div>

      @if($dokumens->count() > 0)
        @if($mode == 'rekapan_table' && $rekapanByVendor)
          <!-- Vendor Grouped View -->
          <div style="padding: 1rem;">
            @foreach($rekapanByVendor as $vendorData)
              <div class="vendor-group">
                <div class="vendor-group-header" onclick="toggleVendorGroup(this)">
                  <h4>
                    <i class="fas fa-building"></i>
                    {{ $vendorData['vendor'] }}
                    <span style="font-weight: 400; font-size: 0.8rem; opacity: 0.9;">({{ $vendorData['count'] }} dok)</span>
                  </h4>
                  <div class="stats">
                    <span><i class="fas fa-money-bill-wave"></i> Rp
                      {{ number_format($vendorData['total_nilai'], 0, ',', '.') }}</span>
                    <i class="fas fa-chevron-down vendor-chevron"></i>
                  </div>
                </div>
                <div class="vendor-group-body" style="display: none;">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th>No. Agenda</th>
                        <th>No. SPP</th>
                        <th>Uraian</th>
                        <th>Nilai (Rp)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($vendorData['documents'] as $doc)
                        <tr>
                          <td><strong>{{ $doc->nomor_agenda }}</strong></td>
                          <td>{{ $doc->nomor_spp ?? '-' }}</td>
                          <td>{{ Str::limit($doc->uraian_spp, 50) }}</td>
                          <td class="rupiah-value">{{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
                          <td>
                            <span class="status-badge {{ $doc->computed_status }}">
                              @if($doc->computed_status == 'siap_dibayar')
                                <i class="fas fa-check-circle"></i> Siap Dibayar
                              @elseif($doc->computed_status == 'sudah_dibayar')
                                <i class="fas fa-money-check-alt"></i> Sudah Dibayar
                              @else
                                <i class="fas fa-hourglass-half"></i> Belum Siap
                              @endif
                            </span>
                          </td>
                          <td>
                            <a href="{{ route('pembayaran.dokumens.show', $doc->id) }}" class="btn-primary-agenda btn-sm"
                              data-tooltip="Lihat Detail">
                              <i class="fas fa-eye"></i>
                            </a>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <!-- Normal Table View -->
          <div class="table-responsive-custom">
            <table class="data-table">
              <thead>
                <tr>
                  <th>No. Agenda</th>
                  <th>No. SPP</th>
                  <th>Dibayar Kepada</th>
                  <th>Uraian SPP</th>
                  <th>Nilai (Rp)</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($dokumens as $dok)
                  <tr>
                    <td><strong>{{ $dok->nomor_agenda }}</strong></td>
                    <td>{{ $dok->nomor_spp ?? '-' }}</td>
                    <td>{{ Str::limit($dok->dibayar_kepada ?? '-', 30) }}</td>
                    <td>{{ Str::limit($dok->uraian_spp ?? '-', 40) }}</td>
                    <td class="rupiah-value">{{ number_format($dok->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
                    <td>
                      <span class="status-badge {{ $dok->computed_status }}">
                        @if($dok->computed_status == 'siap_dibayar')
                          <i class="fas fa-check-circle"></i> Siap Dibayar
                        @elseif($dok->computed_status == 'sudah_dibayar')
                          <i class="fas fa-money-check-alt"></i> Sudah Dibayar
                        @else
                          <i class="fas fa-hourglass-half"></i> Belum Siap
                        @endif
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('pembayaran.dokumens.show', $dok->id) }}" class="btn-primary-agenda btn-sm"
                        data-tooltip="Lihat Detail">
                        <i class="fas fa-eye"></i>
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif

        <!-- Pagination -->
        @if($dokumens->hasPages())
          <div class="pagination-wrapper">
            <div class="pagination-info">
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} dokumen
            </div>
            <div>
              {{ $dokumens->links('pagination::bootstrap-4') }}
            </div>
          </div>
        @endif
      @else
        <!-- Empty State -->
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-inbox"></i>
          </div>
          <h3 class="empty-state-title">Tidak ada dokumen ditemukan</h3>
          <p class="empty-state-desc">Coba ubah filter pencarian atau reset filter untuk melihat semua dokumen.</p>
          <a href="{{ route('pembayaran.dashboard') }}" class="btn-primary-agenda" style="margin-top: 1rem;">
            <i class="fas fa-redo"></i>
            Reset Filter
          </a>
        </div>
      @endif
    </div>
  </div>

  <script>
    // Mode Toggle Handler
    document.getElementById('modeToggle')?.addEventListener('change', function () {
      const modeInput = document.getElementById('modeInput');
      modeInput.value = this.checked ? 'rekapan_table' : 'normal';
      document.getElementById('filterForm').submit();
    });

    // Vendor Group Toggle
    function toggleVendorGroup(header) {
      const body = header.nextElementSibling;
      const chevron = header.querySelector('.vendor-chevron');

      if (body.style.display === 'none') {
        body.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
      } else {
        body.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
      }
    }

    // Number formatting with animation
    document.querySelectorAll('.stat-card-value').forEach(el => {
      const value = parseInt(el.textContent.replace(/\D/g, '')) || 0;
      if (value > 0) {
        let current = 0;
        const increment = Math.ceil(value / 30);
        const interval = setInterval(() => {
          current += increment;
          if (current >= value) {
            current = value;
            clearInterval(interval);
          }
          el.textContent = current.toLocaleString('id-ID');
        }, 20);
      }
    });
  </script>
@endsection
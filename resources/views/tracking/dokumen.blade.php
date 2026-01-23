@extends('layouts.app')

@section('content')
  <style>
    /* Modern Command Center Dashboard Styles */
    :root {
      --primary-color: #083E40;
      --success-color: #889717;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --info-color: #0a4f52;
      --text-primary: #1a202c;
      --text-secondary: #4a5568;
      --text-muted: #718096;
      --border-color: #e2e8f0;
      --bg-light: #f8fafc;
    }

    body {
      background: var(--bg-light) !important;
      font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dark body {
      background: #0f172a !important;
      color: #f1f5f9;
    }

    /* Card View Styles - Modern Glassmorphism Design */
    .card-view-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }

    @media (max-width: 768px) {
      .card-view-container {
        grid-template-columns: 1fr;
      }
    }

    .smart-document-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 1.75rem;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      position: relative;
      overflow: hidden;
      user-select: text;
    }

    .smart-document-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #083E40, #0a5f52, #889717);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .smart-document-card:hover {
      box-shadow: 0 12px 40px rgba(8, 62, 64, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1);
      transform: translateY(-4px);
      border-color: rgba(8, 62, 64, 0.2);
    }

    .smart-document-card:hover::before {
      opacity: 1;
    }

    .smart-document-card.overdue {
      border-left: 4px solid #dc3545;
    }

    .smart-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .smart-card-title {
      font-size: 1.35rem;
      font-weight: 800;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.35rem;
      letter-spacing: -0.5px;
    }

    .smart-card-subtitle {
      font-size: 0.85rem;
      color: #64748b;
      font-weight: 500;
    }

    .smart-card-value {
      font-size: 1.75rem;
      font-weight: 800;
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1.25rem;
      letter-spacing: -0.5px;
    }

    .smart-card-info-row {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.85rem;
      font-size: 0.9rem;
      color: #475569;
      padding: 0.5rem 0.75rem;
      background: rgba(8, 62, 64, 0.03);
      border-radius: 10px;
      transition: background 0.2s ease;
    }

    .smart-card-info-row:hover {
      background: rgba(8, 62, 64, 0.06);
    }

    .smart-card-info-row i {
      color: #083E40;
      width: 18px;
      font-size: 0.95rem;
    }

    .user-avatar {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: 700;
      box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
    }

    /* Workflow Stepper - Modern Timeline Design */
    .workflow-stepper {
      margin-top: 1.75rem;
      padding: 1.5rem;
      background: linear-gradient(135deg, rgba(8, 62, 64, 0.03) 0%, rgba(136, 151, 23, 0.03) 100%);
      border-radius: 16px;
      border: 1px solid rgba(8, 62, 64, 0.08);
    }

    .stepper-label {
      font-size: 0.7rem;
      font-weight: 700;
      color: #083E40;
      margin-bottom: 1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .stepper-label::before {
      content: '⚡';
      font-size: 0.9rem;
    }

    .stepper-steps {
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      padding: 0 0.25rem;
    }

    .stepper-steps::before {
      content: '';
      position: absolute;
      top: 16px;
      left: 16px;
      right: 16px;
      height: 3px;
      background: linear-gradient(90deg, #e2e8f0 0%, #cbd5e1 100%);
      border-radius: 2px;
      z-index: 0;
    }

    .stepper-step {
      position: relative;
      z-index: 1;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: white;
      border: 3px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      font-weight: 700;
      color: #94a3b8;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .stepper-step.completed {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-color: #083E40;
      color: white;
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.35);
    }

    .stepper-step.active {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-color: #083E40;
      color: white;
      box-shadow: 0 0 0 4px rgba(8, 62, 64, 0.2), 0 4px 12px rgba(8, 62, 64, 0.35);
    }

    .stepper-step.waiting {
      background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
      border-color: #ffc107;
      color: #000;
      box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.3), 0 4px 12px rgba(255, 193, 7, 0.35);
      animation: pulse-waiting 2s ease-in-out infinite;
    }

    @keyframes pulse-waiting {

      0%,
      100% {
        box-shadow: 0 0 0 5px rgba(255, 193, 7, 0.25), 0 4px 12px rgba(255, 193, 7, 0.35);
      }

      50% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0.1), 0 4px 16px rgba(255, 193, 7, 0.4);
      }
    }

    .stepper-step.waiting .stepper-step-label {
      color: #856404;
      font-weight: 700;
    }

    .stepper-step-label {
      position: absolute;
      top: 38px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 0.65rem;
      color: #64748b;
      white-space: nowrap;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .stepper-step.completed .stepper-step-label,
    .stepper-step.active .stepper-step-label {
      color: #083E40;
      font-weight: 700;
    }

    /* Control Bar */
    .control-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding: 1rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .control-bar-left {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex: 1;
    }

    .search-input-modern {
      padding: 0.75rem 1rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.95rem;
      width: 300px;
      transition: all 0.2s ease;
    }

    .search-input-modern:focus {
      outline: none;
      border-color: #083E40;
      box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
    }

    .action-btn {
      padding: 0.75rem 1.5rem;
      background: #083E40;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .action-btn:hover {
      background: #065a5d;
      transform: translateY(-1px);
    }

    /* View Switcher */
    .view-switcher {
      display: inline-flex;
      background: #f1f5f9;
      border-radius: 8px;
      padding: 4px;
      gap: 4px;
    }

    .view-switcher-btn {
      padding: 8px 16px;
      border: none;
      background: transparent;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 500;
      color: #64748b;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .view-switcher-btn:hover {
      background: rgba(8, 62, 64, 0.05);
      color: #083E40;
    }

    .view-switcher-btn.active {
      background: white;
      color: #083E40;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #64748b;
    }

    .empty-state-icon {
      font-size: 4rem;
      color: #cbd5e1;
      margin-bottom: 1rem;
    }

    .empty-state-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #334155;
      margin-bottom: 0.5rem;
    }

    .empty-state-text {
      font-size: 1rem;
      color: #64748b;
    }

    /* Pagination */
    .pagination-wrapper {
      margin-top: 2rem;
      display: flex;
      justify-content: center;
    }

    .view-container {
      display: none;
    }

    .view-container.active {
      display: block;
    }

    /* Modern Table Styles */
    .modern-table-container {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
      border: 1px solid rgba(8, 62, 64, 0.1);
      overflow: hidden;
      margin-top: 1.5rem;
    }

    .modern-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }

    .modern-table thead {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
    }

    .modern-table thead th {
      padding: 1rem 1.25rem;
      text-align: left;
      font-weight: 700;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: white;
      border: none;
    }

    .modern-table thead th:first-child {
      border-radius: 0;
    }

    .modern-table thead th:last-child {
      border-radius: 0;
    }

    .modern-table tbody tr {
      transition: all 0.3s ease;
      border-bottom: 1px solid rgba(8, 62, 64, 0.06);
    }

    .modern-table tbody tr:hover {
      background: linear-gradient(90deg, rgba(8, 62, 64, 0.04) 0%, rgba(136, 151, 23, 0.04) 100%);
      transform: scale(1.002);
    }

    .modern-table tbody tr:last-child {
      border-bottom: none;
    }

    .modern-table tbody td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      color: #334155;
    }

    .table-doc-number {
      font-weight: 700;
      color: #083E40;
      font-size: 0.95rem;
    }

    .table-date {
      color: #64748b;
      font-size: 0.85rem;
    }

    .table-value {
      font-weight: 700;
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .table-position {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0.75rem;
      background: rgba(8, 62, 64, 0.08);
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      color: #083E40;
    }

    .table-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.4rem 0.9rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .table-status-badge.status-proses {
      background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
      color: #000;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .table-status-badge.status-selesai {
      background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .table-progress-container {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .table-progress-bar {
      flex: 1;
      height: 8px;
      background: #e2e8f0;
      border-radius: 4px;
      overflow: hidden;
      min-width: 60px;
    }

    .table-progress-fill {
      height: 100%;
      border-radius: 4px;
      background: linear-gradient(90deg, #083E40 0%, #889717 100%);
      transition: width 0.5s ease;
    }

    .table-progress-text {
      font-weight: 700;
      font-size: 0.8rem;
      color: #083E40;
      min-width: 35px;
      text-align: right;
    }

    .table-action-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.5rem 1rem;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .table-action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.35);
      color: white;
      text-decoration: none;
    }

    .table-action-btn i {
      font-size: 0.75rem;
    }

    /* Modern Filter Panel Styles */
    .modern-filter-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--border-color);
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .filter-toggle-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--border-color);
    }

    .filter-toggle-btn {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1.5rem;
      background: var(--bg-light);
      border: 2px solid var(--border-color);
      border-radius: 8px;
      font-weight: 600;
      color: var(--text-primary);
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .filter-toggle-btn:hover {
      background: white;
      border-color: var(--primary-color);
      color: var(--primary-color);
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
      display: none;
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
      color: var(--text-primary);
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .filter-label i {
      color: var(--primary-color);
      font-size: 0.9rem;
    }

    .filter-select,
    .filter-input-search {
      padding: 0.75rem 1rem;
      border: 2px solid var(--border-color);
      border-radius: 8px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background: white;
    }

    .filter-select:focus,
    .filter-input-search:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
    }

    .filter-select:disabled {
      background-color: #f1f5f9;
      cursor: not-allowed;
      opacity: 0.6;
      color: var(--text-muted);
    }

    .filter-select:disabled option {
      color: var(--text-muted);
    }

    .filter-searchable {
      min-height: 44px;
    }

    .filter-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      padding-top: 1rem;
      border-top: 2px solid var(--border-color);
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
      background: var(--bg-light);
      color: var(--text-secondary);
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
      background: #0a4f52;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .active-filters {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      padding-top: 1rem;
      border-top: 2px solid var(--border-color);
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

    @media (max-width: 768px) {
      .filter-grid {
        grid-template-columns: 1fr;
      }

      .filter-toggle-bar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }

      .filter-actions {
        flex-direction: column;
      }

      .filter-btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>

  <div class="container" style="margin-top: 2rem;">
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
        <form method="GET" action="{{ url('/tracking-dokumen') }}" id="filterForm" class="filter-form">
          <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

          <!-- Search Bar -->
          <div class="filter-row">
            <div class="filter-group full-width">
              <label class="filter-label">
                <i class="fas fa-search"></i> Cari Dokumen
              </label>
              <input type="text" name="search" class="filter-input-search" value="{{ $search ?? '' }}"
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

            <!-- Status Pembayaran -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-money-bill-wave"></i> Status Pembayaran
              </label>
              <select name="filter_status_pembayaran" class="filter-select" onchange="applyFilter()">
                <option value="">Semua Status Pembayaran</option>
                <option value="belum_dibayar" {{ request('filter_status_pembayaran') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="siap_dibayar" {{ request('filter_status_pembayaran') == 'siap_dibayar' ? 'selected' : '' }}>
                  Siap Dibayar</option>
                <option value="sudah_dibayar" {{ request('filter_status_pembayaran') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
              </select>
            </div>

            <!-- Status Dokumen -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-info-circle"></i> Status Dokumen
              </label>
              <select name="status" class="filter-select" onchange="applyFilter()">
                <option value="">Semua Status</option>
                <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Proses</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
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
      @if($documents->count() == 0)
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-folder-open"></i>
          </div>
          <div class="empty-state-title">Tidak ada dokumen</div>
          <div class="empty-state-text">
            @if(isset($search) && !empty($search))
              Tidak ada dokumen yang sesuai dengan pencarian "{{ $search }}"
            @else
              Dokumen akan ditampilkan di sini ketika tersedia
            @endif
          </div>
        </div>
      @else
        <div class="card-view-container">
          @foreach($documents as $dokumen)
            <div class="smart-document-card {{ $dokumen['is_overdue'] ?? false ? 'overdue' : '' }}"
              data-document-url="{{ url('/owner/workflow/' . $dokumen['id']) }}"
              onclick="handleCardClick(event, '{{ url('/owner/workflow/' . $dokumen['id']) }}')">

              <div class="smart-card-header">
                <div>
                  <div class="smart-card-title">
                    {{ $dokumen['nomor_agenda'] ?? 'N/A' }}
                  </div>
                  <div class="smart-card-subtitle">
                    SPP: {{ $dokumen['nomor_spp'] ?? 'N/A' }}
                  </div>
                </div>
              </div>

              <div class="smart-card-value">
                Rp {{ number_format($dokumen['nilai_rupiah'] ?? 0, 0, ',', '.') }}
              </div>

              <div class="smart-card-info-row">
                <i class="fas fa-user"></i>
                <span>Posisi:</span>
                <span class="user-avatar">
                  {{ substr($dokumen['current_handler_display'] ?? 'N/A', 0, 1) }}
                </span>
                <span>{{ $dokumen['current_handler_display'] ?? 'Belum ada penangan' }}</span>
              </div>

              @if(isset($dokumen['deadline_info']) && $dokumen['deadline_info'])
                <div class="smart-card-info-row" style="flex-direction: column; align-items: flex-start; gap: 0.5rem;">
                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-calendar-alt"></i>
                    <span style="font-size: 0.85rem; color: #64748b;">{{ $dokumen['deadline_info']['date'] ?? '' }}</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="badge bg-{{ $dokumen['deadline_info']['color'] ?? 'success' }}"
                      style="font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 12px;">
                      {{ $dokumen['deadline_info']['text'] ?? 'AMAN' }}
                    </span>
                    @if(!empty($dokumen['deadline_info']['elapsed']))
                      <span style="font-size: 0.8rem; color: #64748b;">
                        <i class="fas fa-hourglass-half"></i> {{ $dokumen['deadline_info']['elapsed'] }}
                      </span>
                    @endif
                  </div>
                </div>
              @endif

              <!-- Workflow Stepper -->
              <div class="workflow-stepper">
                <div class="stepper-label">Progres Alur Kerja</div>
                <div class="stepper-steps">
                  @php
                    $timeline = $dokumen['workflow_timeline'] ?? null;
                    $steps = $timeline['steps'] ?? [];
                  @endphp
                  @if($timeline && count($steps) > 0)
                    @foreach($steps as $step)
                      <div class="stepper-step {{ $step['status'] }}"
                        title="{{ $step['label'] }}{{ $step['in_inbox'] ? ' (Di Inbox)' : '' }}{{ $step['received_at'] ? ' - Diterima: ' . $step['received_at'] : '' }}">
                        @if($step['status'] === 'completed')
                          ✓
                        @elseif($step['in_inbox'])
                          📥
                        @elseif($step['status'] === 'active')
                          {{ $loop->iteration }}
                        @else
                          {{ $loop->iteration }}
                        @endif
                        <div class="stepper-step-label">
                          {{ $step['label'] }}
                        </div>
                      </div>
                    @endforeach
                  @else
                    {{-- Fallback to old logic --}}
                    @php
                      $progress = $dokumen['progress_percentage'] ?? 0;
                      $currentStep = min(5, max(1, ceil($progress / 20)));
                    @endphp
                    @for($i = 1; $i <= 5; $i++)
                      <div class="stepper-step {{ $i <= $currentStep ? ($i == $currentStep ? 'active' : 'completed') : '' }}">
                        {{ $i }}
                        <div class="stepper-step-label">
                          @if($i == 1) Bagian
                          @elseif($i == 2) Verif
                          @elseif($i == 3) Perpajakan
                          @elseif($i == 4) Akutansi
                          @else Pembayaran
                          @endif
                        </div>
                      </div>
                    @endfor
                  @endif
                </div>

                @if($timeline && $timeline['is_in_inbox'])
                  <div class="inbox-indicator"
                    style="margin-top: 0.75rem; padding: 0.5rem; background: #fff3cd; border-radius: 8px; font-size: 0.75rem; text-align: center; color: #856404;">
                    📥 Di Inbox {{ $timeline['current_handler_display'] }} - Menunggu diproses
                  </div>
                @endif
              </div>

            </div>
          @endforeach
        </div>
      @endif

      <!-- Pagination Footer for Card View -->
      @if($documents->count() > 0)
        @include('owner.partials.pagination-footer', ['paginator' => $documents])
      @endif
    </div>

    <!-- Table View -->
    <div id="tableView" class="view-container">
      @if($documents->count() == 0)
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-folder-open"></i>
          </div>
          <div class="empty-state-title">Tidak ada dokumen</div>
          <div class="empty-state-text">
            @if(isset($search) && !empty($search))
              Tidak ada dokumen yang sesuai dengan pencarian "{{ $search }}"
            @else
              Dokumen akan ditampilkan di sini ketika tersedia
            @endif
          </div>
        </div>
      @else
        <div class="modern-table-container">
          <table class="modern-table">
            <thead>
              <tr>
                <th>No. Dokumen</th>
                <th>Tgl Masuk</th>
                <th>Nilai (Rp)</th>
                <th>Posisi</th>
                <th>Status</th>
                <th>Progres</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($documents as $dokumen)
                <tr>
                  <td>
                    <span class="table-doc-number">{{ $dokumen['nomor_agenda'] ?? 'N/A' }}</span>
                  </td>
                  <td>
                    <span class="table-date">{{ $dokumen['tanggal_masuk'] ?? '-' }}</span>
                  </td>
                  <td>
                    <span class="table-value">Rp {{ number_format($dokumen['nilai_rupiah'] ?? 0, 0, ',', '.') }}</span>
                  </td>
                  <td>
                    <span class="table-position">
                      <i class="fas fa-user"></i>
                      {{ $dokumen['current_handler_display'] ?? 'Belum ada' }}
                    </span>
                  </td>
                  <td>
                    <span class="table-status-badge {{ $dokumen['progress_percentage'] >= 100 ? 'status-selesai' : 'status-proses' }}">
                      @if($dokumen['progress_percentage'] >= 100)
                        <i class="fas fa-check-circle"></i> Selesai
                      @else
                        <i class="fas fa-clock"></i> Proses
                      @endif
                    </span>
                  </td>
                  <td>
                    <div class="table-progress-container">
                      <div class="table-progress-bar">
                        <div class="table-progress-fill" style="width: {{ min(100, $dokumen['progress_percentage'] ?? 0) }}%"></div>
                      </div>
                      <span class="table-progress-text">{{ $dokumen['progress_percentage'] ?? 0 }}%</span>
                    </div>
                  </td>
                  <td>
                    <a href="{{ url('/owner/workflow/' . $dokumen['id']) }}" class="table-action-btn">
                      <i class="fas fa-eye"></i> Lihat
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      <!-- Pagination Footer for Table View -->
      @if($documents->count() > 0)
        @include('owner.partials.pagination-footer', ['paginator' => $documents])
      @endif
    </div>
  </div>

  <script>
    let filterPanelExpanded = false;

    function switchView(view) {
      // Hide all views
      document.querySelectorAll('.view-container').forEach(container => {
        container.classList.remove('active');
      });

      // Show selected view
      document.getElementById(view + 'View').classList.add('active');

      // Update button states
      document.querySelectorAll('.view-switcher-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.closest('.view-switcher-btn').classList.add('active');
    }

    function handleCardClick(event, url) {
      // Check if user is selecting text
      const selection = window.getSelection();
      const selectedText = selection.toString().trim();

      if (selectedText.length > 0) {
        event.preventDefault();
        event.stopPropagation();
        return false;
      }

      // Check if this is a double-click (usually for select word)
      if (event.detail === 2) {
        setTimeout(() => {
          const newSelection = window.getSelection();
          if (newSelection.toString().trim().length > 0) {
            return false;
          }
        }, 50);
        return false;
      }

      // Check if user is dragging (mouse drag selection)
      if (event.detail === 0 || event.which === 0) {
        return false;
      }

      // Navigate to document detail
      window.location.href = url;
      return true;
    }

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
      window.location.href = '{{ url("/tracking-dokumen") }}';
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
        if (key === 'status' && value && value !== '') {
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
        'filter_status_pembayaran': 'Status Pembayaran',
        'status': 'Status Dokumen'
      };

      for (let [key, value] of formData.entries()) {
        if ((key.startsWith('filter_') || key === 'status') && value && value !== '') {
          const label = filterLabels[key] || key;
          const badge = document.createElement('span');
          badge.className = 'filter-badge-item';
          badge.innerHTML = `
                  <span>${label}: ${getFilterDisplayValue(key, value)}</span>
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
        if (input.type === 'radio') {
          // Find and check the "Semua" option
          const semuaOption = document.querySelector(`[name="${key}"][value=""]`);
          if (semuaOption) semuaOption.checked = true;
        } else {
          input.value = '';
        }
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
      updateActiveFilterCount();

      // Initialize cascading dropdowns
      updateSubKriteriaFilter();
      updateItemSubKriteriaFilter();

      // Auto-expand filter panel if filters are active
      const form = document.getElementById('filterForm');
      const formData = new FormData(form);
      let hasActiveFilters = false;
      for (let [key, value] of formData.entries()) {
        if ((key.startsWith('filter_') || key === 'status') && value && value !== '') {
          hasActiveFilters = true;
          break;
        }
      }

      if (hasActiveFilters) {
        toggleFilterPanel();
      }

      // If Kriteria CF is already selected, enable Sub Kriteria
      const kriteriaCfSelect = document.getElementById('filterKriteriaCf');
      if (kriteriaCfSelect && kriteriaCfSelect.value) {
        updateSubKriteriaFilter();
      }
    });
  </script>
@endsection





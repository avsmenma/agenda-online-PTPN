@extends('layouts/app')
@section('content')

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <style>
    /* ═══════════════════════════════════════════════════════════
                 REKAPAN KETERLAMBATAN — MODERN REDESIGN
                 ═══════════════════════════════════════════════════════════ */

    :root {
      --rk-primary: #0f766e;
      --rk-primary-dark: #0d5d57;
      --rk-primary-deep: #134e4a;
      --rk-secondary: #14b8a6;
      --rk-accent: #2dd4bf;
      --rk-surface: #ffffff;
      --rk-surface-alt: #f0fdfa;
      --rk-surface-muted: #f8fafc;
      --rk-text: #0f172a;
      --rk-text-secondary: #64748b;
      --rk-text-muted: #94a3b8;
      --rk-border: #e2e8f0;
      --rk-border-light: #f1f5f9;
      --rk-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
      --rk-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
      --rk-shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
      --rk-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
      --rk-shadow-glow: 0 0 20px rgba(20, 184, 166, 0.15);
      --rk-radius: 12px;
      --rk-radius-lg: 16px;
      --rk-radius-xl: 20px;
      --rk-green: #10b981;
      --rk-green-bg: #ecfdf5;
      --rk-green-border: #a7f3d0;
      --rk-green-text: #065f46;
      --rk-yellow: #f59e0b;
      --rk-yellow-bg: #fffbeb;
      --rk-yellow-border: #fde68a;
      --rk-yellow-text: #92400e;
      --rk-red: #ef4444;
      --rk-red-bg: #fef2f2;
      --rk-red-border: #fecaca;
      --rk-red-text: #991b1b;
      --rk-transition: cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Page Title ── */
    .rk-page-title {
      font-family: 'Inter', sans-serif;
      font-size: clamp(22px, 3vw, 30px);
      font-weight: 800;
      background: linear-gradient(135deg, var(--rk-primary) 0%, var(--rk-secondary) 50%, var(--rk-accent) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 28px;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .rk-page-title i {
      background: linear-gradient(135deg, var(--rk-primary), var(--rk-secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-size: 0.85em;
    }

    h2 {
      display: none;
    }

    /* ── Global Font ── */
    .container-fluid {
      font-family: 'Inter', sans-serif;
    }

    /* ── Timeframe Filter ── */
    .timeframe-filter-container {
      background: var(--rk-surface);
      border-radius: var(--rk-radius);
      padding: 14px 20px;
      margin-bottom: 20px;
      box-shadow: var(--rk-shadow-sm);
      border: 1px solid var(--rk-border);
      display: flex;
      align-items: center;
      gap: 24px;
      flex-wrap: wrap;
    }

    .timeframe-filter-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .timeframe-filter-label {
      font-size: 13px;
      font-weight: 600;
      color: var(--rk-text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }

    .timeframe-filter-select {
      padding: 8px 14px;
      border: 1.5px solid var(--rk-border);
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      color: var(--rk-text);
      background: var(--rk-surface);
      cursor: pointer;
      transition: all 0.2s var(--rk-transition);
      min-width: 170px;
    }

    .timeframe-filter-select:focus {
      outline: none;
      border-color: var(--rk-secondary);
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
    }

    .timeframe-filter-select:hover {
      border-color: var(--rk-secondary);
    }

    .timeframe-settings {
      background: var(--rk-surface);
      border-radius: var(--rk-radius-lg);
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: var(--rk-shadow);
      border: 1px solid var(--rk-border);
    }

    .timeframe-settings-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--rk-primary);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* ── Status Cards ── */
    .card-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }

    @media(max-width:900px) {
      .card-stats {
        grid-template-columns: 1fr;
      }
    }

    .deadline-card-link {
      text-decoration: none;
      display: block;
    }

    .deadline-card {
      border-radius: var(--rk-radius-lg);
      padding: 24px;
      transition: all 0.35s var(--rk-transition);
      position: relative;
      overflow: hidden;
      min-height: 130px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      cursor: pointer;
      border: 1.5px solid transparent;
      border-top: 4px solid;
    }

    .deadline-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 120px;
      height: 120px;
      border-radius: 50%;
      opacity: 0.06;
      transform: translate(30%, -30%);
      transition: all 0.35s var(--rk-transition);
    }

    .deadline-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--rk-shadow-lg);
    }

    .deadline-card:hover::before {
      opacity: 0.12;
      transform: translate(20%, -20%) scale(1.2);
    }

    .deadline-card.active {
      border-color: var(--rk-primary) !important;
      box-shadow: var(--rk-shadow-lg), 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .deadline-card-header {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 14px;
    }

    .deadline-indicator {
      display: flex;
      align-items: center;
    }

    .deadline-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      animation: rkPulse 2s ease-in-out infinite;
    }

    .deadline-dot.aman {
      background: var(--rk-green);
      box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
    }

    .deadline-dot.peringatan {
      background: var(--rk-yellow);
      box-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
    }

    .deadline-dot.terlambat {
      background: var(--rk-red);
      box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
    }

    @keyframes rkPulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.5;
        transform: scale(0.85);
      }
    }

    .deadline-count {
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -0.5px;
      font-family: 'Inter', sans-serif;
    }

    .deadline-badge-wrapper {
      margin-bottom: 10px;
    }

    .deadline-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      font-family: 'Inter', sans-serif;
    }

    .badge-aman {
      background: var(--rk-green);
      color: white;
    }

    .badge-peringatan {
      background: var(--rk-yellow);
      color: var(--rk-yellow-text);
    }

    .badge-terlambat {
      background: var(--rk-red);
      color: white;
    }

    .deadline-info {
      font-size: 12.5px;
      display: flex;
      align-items: center;
      gap: 8px;
      opacity: 0.8;
    }

    .deadline-info i {
      font-size: 13px;
    }

    /* Card Color Variations */
    .deadline-aman {
      background: var(--rk-green-bg);
      border-top-color: var(--rk-green);
      border-color: var(--rk-green-border);
    }

    .deadline-aman::before {
      background: var(--rk-green);
    }

    .deadline-aman .deadline-count,
    .deadline-aman .deadline-info {
      color: var(--rk-green-text);
    }

    .deadline-aman:hover {
      box-shadow: 0 12px 30px rgba(16, 185, 129, 0.18);
    }

    .deadline-peringatan {
      background: var(--rk-yellow-bg);
      border-top-color: var(--rk-yellow);
      border-color: var(--rk-yellow-border);
    }

    .deadline-peringatan::before {
      background: var(--rk-yellow);
    }

    .deadline-peringatan .deadline-count,
    .deadline-peringatan .deadline-info {
      color: var(--rk-yellow-text);
    }

    .deadline-peringatan:hover {
      box-shadow: 0 12px 30px rgba(245, 158, 11, 0.18);
    }

    .deadline-terlambat {
      background: var(--rk-red-bg);
      border-top-color: var(--rk-red);
      border-color: var(--rk-red-border);
    }

    .deadline-terlambat::before {
      background: var(--rk-red);
    }

    .deadline-terlambat .deadline-count,
    .deadline-terlambat .deadline-info {
      color: var(--rk-red-text);
    }

    .deadline-terlambat:hover {
      box-shadow: 0 12px 30px rgba(239, 68, 68, 0.18);
    }

    /* ── Filter Panel ── */
    .modern-filter-container {
      background: var(--rk-surface);
      border-radius: var(--rk-radius-lg);
      box-shadow: var(--rk-shadow);
      margin-bottom: 1.5rem;
      overflow: hidden;
      border: 1px solid var(--rk-border);
    }

    .filter-toggle-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 20px;
      background: var(--rk-surface-muted);
      border-bottom: 1px solid var(--rk-border);
    }

    .filter-toggle-btn {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 8px 16px;
      background: var(--rk-surface);
      border: 1.5px solid var(--rk-border);
      border-radius: 8px;
      font-weight: 600;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      color: var(--rk-text-secondary);
      cursor: pointer;
      transition: all 0.25s var(--rk-transition);
    }

    .filter-toggle-btn:hover {
      background: var(--rk-primary);
      color: white;
      border-color: var(--rk-primary);
      transform: translateY(-1px);
      box-shadow: var(--rk-shadow);
    }

    .filter-badge {
      background: var(--rk-secondary);
      color: white;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 700;
      min-width: 20px;
      text-align: center;
    }

    .filter-panel {
      padding: 1.5rem;
      background: var(--rk-surface);
      animation: rkSlideDown 0.25s var(--rk-transition);
    }

    @keyframes rkSlideDown {
      from {
        opacity: 0;
        transform: translateY(-8px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .filter-form {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    .filter-row {
      display: flex;
      gap: 1rem;
    }

    .filter-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.25rem;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .filter-group.full-width {
      grid-column: 1 / -1;
    }

    .filter-label {
      font-weight: 600;
      color: var(--rk-text-secondary);
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .filter-select,
    .filter-input-search {
      padding: 10px 14px;
      border: 1.5px solid var(--rk-border);
      border-radius: 8px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      transition: all 0.2s var(--rk-transition);
      background: var(--rk-surface);
      color: var(--rk-text);
    }

    .filter-select:focus,
    .filter-input-search:focus {
      outline: none;
      border-color: var(--rk-secondary);
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }

    .filter-select:disabled {
      background: var(--rk-surface-muted);
      cursor: not-allowed;
      opacity: 0.5;
    }

    .filter-searchable {
      min-height: 42px;
    }

    .filter-actions {
      display: flex;
      gap: 0.75rem;
      justify-content: flex-end;
      padding-top: 1rem;
      border-top: 1px solid var(--rk-border);
    }

    .filter-btn {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: all 0.25s var(--rk-transition);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .filter-btn-reset {
      background: var(--rk-surface-muted);
      color: var(--rk-text-secondary);
    }

    .filter-btn-reset:hover {
      background: var(--rk-border);
      transform: translateY(-1px);
    }

    .filter-btn-apply {
      background: var(--rk-primary);
      color: white;
    }

    .filter-btn-apply:hover {
      background: var(--rk-primary-dark);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(15, 118, 110, 0.25);
    }

    .active-filters {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
      padding-top: 0.75rem;
      border-top: 1px solid var(--rk-border);
    }

    .filter-badge-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 12px;
      background: linear-gradient(135deg, var(--rk-primary), var(--rk-secondary));
      color: white;
      border-radius: 16px;
      font-size: 12px;
      font-weight: 500;
    }

    .filter-badge-item .remove-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      font-size: 10px;
      transition: all 0.2s;
    }

    .filter-badge-item .remove-btn:hover {
      background: rgba(255, 255, 255, 0.35);
    }

    /* ── View Switcher ── */
    .view-switcher {
      display: inline-flex;
      background: var(--rk-surface);
      border-radius: 8px;
      padding: 3px;
      gap: 3px;
      border: 1.5px solid var(--rk-border);
    }

    .view-switcher-btn {
      padding: 7px 14px;
      border: none;
      background: transparent;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      color: var(--rk-text-secondary);
      cursor: pointer;
      transition: all 0.2s var(--rk-transition);
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .view-switcher-btn:hover {
      background: var(--rk-surface-alt);
      color: var(--rk-primary);
    }

    .view-switcher-btn.active {
      background: linear-gradient(135deg, var(--rk-primary), var(--rk-secondary));
      color: white;
      box-shadow: 0 2px 6px rgba(15, 118, 110, 0.25);
    }

    .view-container {
      display: none;
    }

    .view-container.active {
      display: block;
    }

    /* ── Card View ── */
    .card-view-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 18px;
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
        gap: 0.75rem;
        align-items: stretch;
      }
    }

    .document-card {
      background: var(--rk-surface);
      border-radius: var(--rk-radius-lg);
      padding: 22px;
      box-shadow: var(--rk-shadow-sm);
      border: 1px solid var(--rk-border);
      border-top: 4px solid transparent;
      transition: all 0.3s var(--rk-transition);
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .document-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--rk-secondary), transparent);
      opacity: 0;
      transition: opacity 0.3s;
    }

    .document-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--rk-shadow-md);
    }

    .document-card:hover::after {
      opacity: 1;
    }

    .document-card-green {
      border-top-color: var(--rk-green);
      background: linear-gradient(180deg, var(--rk-green-bg) 0%, var(--rk-surface) 40%);
    }

    .document-card-green:hover {
      box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15);
    }

    .document-card-yellow {
      border-top-color: var(--rk-yellow);
      background: linear-gradient(180deg, var(--rk-yellow-bg) 0%, var(--rk-surface) 40%);
    }

    .document-card-yellow:hover {
      box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15);
    }

    .document-card-red {
      border-top-color: var(--rk-red);
      background: linear-gradient(180deg, var(--rk-red-bg) 0%, var(--rk-surface) 40%);
    }

    .document-card-red:hover {
      box-shadow: 0 8px 24px rgba(239, 68, 68, 0.15);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 12px;
    }

    .card-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--rk-text);
      font-family: 'Inter', sans-serif;
      margin-bottom: 3px;
    }

    .card-subtitle {
      font-size: 12.5px;
      color: var(--rk-text-muted);
      font-weight: 500;
    }

    .card-value {
      font-size: 20px;
      font-weight: 800;
      color: var(--rk-primary);
      margin-bottom: 14px;
      font-family: 'Inter', sans-serif;
      letter-spacing: -0.3px;
    }

    .card-info-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--rk-text-secondary);
      margin-bottom: 8px;
    }

    .card-info-row i {
      width: 16px;
      color: var(--rk-secondary);
      font-size: 13px;
    }

    /* ── Table Section ── */
    .table-container {
      background: var(--rk-surface);
      padding: 24px;
      border-radius: var(--rk-radius-lg);
      box-shadow: var(--rk-shadow);
      border: 1px solid var(--rk-border);
    }

    .table-responsive {
      overflow-x: auto;
      overflow-y: visible;
      -webkit-overflow-scrolling: touch;
    }

    .table thead {
      background: linear-gradient(135deg, var(--rk-primary-deep) 0%, var(--rk-primary) 100%) !important;
    }

    .table thead th {
      background: transparent !important;
      color: white !important;
      font-weight: 600;
      font-size: 11.5px;
      letter-spacing: 0.8px;
      padding: 14px 16px;
      border: none !important;
      text-transform: uppercase;
      font-family: 'Inter', sans-serif;
    }

    .table tbody tr {
      transition: all 0.25s var(--rk-transition);
      border-left: 3px solid transparent;
    }

    .table tbody tr.clickable-row {
      cursor: pointer;
    }

    .table tbody tr.clickable-row:hover {
      background: var(--rk-surface-alt);
      border-left-color: var(--rk-secondary);
    }

    .table tbody tr.table-row-green {
      border-left: 3px solid var(--rk-green);
      background: linear-gradient(90deg, rgba(16, 185, 129, 0.06) 0%, transparent 40%);
    }

    .table tbody tr.table-row-yellow {
      border-left: 3px solid var(--rk-yellow);
      background: linear-gradient(90deg, rgba(245, 158, 11, 0.06) 0%, transparent 40%);
    }

    .table tbody tr.table-row-red {
      border-left: 3px solid var(--rk-red);
      background: linear-gradient(90deg, rgba(239, 68, 68, 0.06) 0%, transparent 40%);
    }

    /* ── Age Badges ── */
    .age-badge {
      padding: 5px 12px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-family: 'Inter', sans-serif;
    }

    .age-badge.age-green {
      background: var(--rk-green-bg);
      color: var(--rk-green-text);
      border: 1px solid var(--rk-green-border);
    }

    .age-badge.age-yellow {
      background: var(--rk-yellow-bg);
      color: var(--rk-yellow-text);
      border: 1px solid var(--rk-yellow-border);
    }

    .age-badge.age-red {
      background: var(--rk-red-bg);
      color: var(--rk-red-text);
      border: 1px solid var(--rk-red-border);
    }

    /* ── Pagination ── */
    .modern-pagination {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--rk-border);
    }

    .pagination-info {
      font-size: 13px;
      color: var(--rk-text-muted);
      font-weight: 500;
      font-family: 'Inter', sans-serif;
    }

    .pagination-info strong {
      color: var(--rk-primary);
      font-weight: 700;
    }

    .pagination-controls {
      display: flex;
      align-items: center;
      gap: 5px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .pagination-controls a,
    .pagination-controls span {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 38px;
      height: 38px;
      padding: 0 10px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      transition: all 0.2s var(--rk-transition);
      border: 1.5px solid transparent;
      user-select: none;
    }

    .pg-btn {
      color: var(--rk-text-secondary);
      background: var(--rk-surface);
      border-color: var(--rk-border) !important;
    }

    .pg-btn:hover {
      background: var(--rk-surface-alt);
      border-color: var(--rk-secondary) !important;
      color: var(--rk-primary);
      transform: translateY(-1px);
    }

    .pg-btn.active {
      background: linear-gradient(135deg, var(--rk-primary), var(--rk-secondary));
      color: white;
      border-color: transparent !important;
      box-shadow: 0 3px 8px rgba(15, 118, 110, 0.25);
    }

    .pg-nav {
      color: var(--rk-text-secondary);
      background: var(--rk-surface);
      border-color: var(--rk-border) !important;
      font-size: 12px;
      gap: 4px;
    }

    .pg-nav:hover {
      background: var(--rk-primary);
      color: white;
      border-color: var(--rk-primary) !important;
      transform: translateY(-1px);
    }

    .pg-nav.disabled {
      color: var(--rk-text-muted);
      background: var(--rk-surface-muted);
      border-color: var(--rk-border-light) !important;
      cursor: not-allowed;
      pointer-events: none;
    }

    .pg-ellipsis {
      color: var(--rk-text-muted);
      background: transparent;
      border: none !important;
      cursor: default;
      font-weight: 700;
      letter-spacing: 2px;
    }

    @media (max-width: 576px) {

      .pagination-controls a,
      .pagination-controls span {
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        font-size: 12px;
      }
    }

    /* ── Form Controls ── */
    .form-control,
    .form-select {
      border: 1.5px solid var(--rk-border);
      border-radius: 8px;
      padding: 10px 14px;
      font-family: 'Inter', sans-serif;
      transition: all 0.2s var(--rk-transition);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--rk-secondary);
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }

    .btn-filter {
      background: linear-gradient(135deg, var(--rk-primary), var(--rk-secondary));
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 24px;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      transition: all 0.25s var(--rk-transition);
    }

    .btn-filter:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(15, 118, 110, 0.25);
    }

    /* ── Status Tabs ── */
    .rk-status-tabs {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      margin: 20px 0;
    }

    .rk-status-tab {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      transition: all 0.25s var(--rk-transition);
      border: 1.5px solid var(--rk-border);
      background: var(--rk-surface);
      color: var(--rk-text-secondary);
    }

    .rk-status-tab:hover {
      background: var(--rk-surface-alt);
      border-color: var(--rk-secondary);
      color: var(--rk-primary);
      transform: translateY(-1px);
    }

    .rk-status-tab.active-all {
      background: var(--rk-primary);
      color: white;
      border-color: var(--rk-primary);
    }

    .rk-status-tab.active-active {
      background: #0d6efd;
      color: white;
      border-color: #0d6efd;
    }

    .rk-status-tab.active-completed {
      background: #198754;
      color: white;
      border-color: #198754;
    }

    /* ── Entries / Export Bar ── */
    .rk-entries-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .rk-entries-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .rk-entries-label {
      font-size: 13px;
      color: var(--rk-text-muted);
      font-weight: 500;
    }

    .rk-entries-select {
      padding: 7px 12px;
      border: 1.5px solid var(--rk-border);
      border-radius: 8px;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      background: var(--rk-surface);
      transition: all 0.2s var(--rk-transition);
    }

    .rk-entries-select:focus {
      outline: none;
      border-color: var(--rk-secondary);
    }

    .rk-export-btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      background: linear-gradient(135deg, #217346 0%, #2e8b57 100%);
      color: white;
      border-radius: 8px;
      font-weight: 600;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      border: none;
      cursor: pointer;
      transition: all 0.25s var(--rk-transition);
      box-shadow: 0 2px 6px rgba(33, 115, 70, 0.2);
    }

    .rk-export-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(33, 115, 70, 0.3);
    }

    /* ── Quick Search ── */
    .rk-search-container {
      margin-bottom: 16px;
    }

    .rk-search-form {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }

    .rk-search-wrapper {
      flex: 1;
      min-width: 300px;
      position: relative;
    }

    .rk-search-wrapper i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--rk-text-muted);
      font-size: 14px;
    }

    .rk-search-input {
      width: 100%;
      padding: 11px 16px 11px 42px;
      border: 1.5px solid var(--rk-border);
      border-radius: var(--rk-radius);
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      transition: all 0.25s var(--rk-transition);
      background: var(--rk-surface);
      color: var(--rk-text);
    }

    .rk-search-input:focus {
      outline: none;
      border-color: var(--rk-secondary);
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }

    .rk-search-btn {
      padding: 11px 22px;
      background: linear-gradient(135deg, var(--rk-primary), var(--rk-secondary));
      color: white;
      border: none;
      border-radius: var(--rk-radius);
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.25s var(--rk-transition);
    }

    .rk-search-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(15, 118, 110, 0.25);
    }

    .rk-search-reset {
      padding: 11px 18px;
      background: var(--rk-surface-muted);
      color: var(--rk-text-secondary);
      border: none;
      border-radius: var(--rk-radius);
      font-weight: 600;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 6px;
      font-family: 'Inter', sans-serif;
      transition: all 0.2s var(--rk-transition);
    }

    .rk-search-reset:hover {
      background: var(--rk-border);
      color: var(--rk-text);
    }

    .rk-search-info {
      margin-top: 10px;
      padding: 8px 14px;
      background: var(--rk-surface-alt);
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--rk-text-secondary);
      border: 1px solid rgba(20, 184, 166, 0.15);
    }

    .rk-search-info i {
      color: var(--rk-secondary);
    }

    /* ── Status Badges ── */
    .rk-badge {
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 8px;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .rk-badge-completed {
      background: #198754;
      color: white;
    }

    .rk-badge-active {
      background: #0d6efd;
      color: white;
    }

    .rk-badge-sm {
      font-size: 11px;
      padding: 3px 8px;
    }

    /* ── Table Heading ── */
    .rk-table-heading {
      font-size: 17px;
      font-weight: 700;
      color: var(--rk-text);
      margin-bottom: 20px;
      font-family: 'Inter', sans-serif;
    }

    /* ── Empty State ── */
    .rk-empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .rk-empty-state i {
      font-size: 3rem;
      color: var(--rk-text-muted);
      margin-bottom: 16px;
    }

    .rk-empty-state h5 {
      color: var(--rk-text-secondary);
      font-family: 'Inter', sans-serif;
      font-weight: 600;
    }

    .rk-empty-state p {
      color: var(--rk-text-muted);
      font-size: 14px;
    }
  </style>

  <div class="container-fluid">
    <h2><i class="fa-solid fa-exclamation-triangle"></i> Rekapan Keterlambatan - {{ $roleConfig[$roleCode]['name'] }}</h2>
    <div class="rk-page-title">
      <i class="fa-solid fa-chart-line"></i> Rekapan Keterlambatan — {{ $roleConfig[$roleCode]['name'] }}
    </div>

    <!-- Horizontal Year/Month Filter -->
    <div class="timeframe-filter-container">
      <div class="timeframe-filter-group">
        <label class="timeframe-filter-label">Filter Tahun:</label>
        <select name="year" class="timeframe-filter-select" onchange="applyTimeframeFilter()">
          <option value="">Semua Tahun</option>
          @foreach($availableYears as $year)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>

      <div class="timeframe-filter-group">
        <label class="timeframe-filter-label">Pilih Bulan:</label>
        <select name="month" class="timeframe-filter-select" onchange="applyTimeframeFilter()">
          <option value="">Semua Bulan</option>
          @foreach($availableMonths as $monthNum => $monthName)
            <option value="{{ $monthNum }}" {{ request('month') == $monthNum ? 'selected' : '' }}>{{ $monthName }}</option>
          @endforeach
        </select>
      </div>
    </div>

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
      <div class="rk-status-tabs">
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['status_filter', 'page']), ['status_filter' => 'all']))) }}"
          class="rk-status-tab {{ $currentStatusFilter === 'all' ? 'active-all' : '' }}">
          <i class="fas fa-list"></i> Semua
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['status_filter', 'page']), ['status_filter' => 'active']))) }}"
          class="rk-status-tab {{ $currentStatusFilter === 'active' ? 'active-active' : '' }}">
          <i class="fas fa-spinner"></i> Aktif (Sedang Diproses)
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], array_merge(request()->except(['status_filter', 'page']), ['status_filter' => 'completed']))) }}"
          class="rk-status-tab {{ $currentStatusFilter === 'completed' ? 'active-completed' : '' }}">
          <i class="fas fa-check-circle"></i> Selesai (Sudah Dikirim)
        </a>
      </div>
    @endif

    <!-- Show Entries Bar -->
    <div class="rk-entries-bar">
      <div class="rk-entries-group">
        <span class="rk-entries-label">Show</span>
        <select id="entriesPerPage" onchange="changeEntriesPerPage(this.value)" class="rk-entries-select">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="rk-entries-label">entries</span>
      </div>

      <!-- Export Excel Button -->
      <button type="button" onclick="showExportModal()" class="rk-export-btn">
        <i class="fas fa-file-excel"></i>
        <span>Export Excel</span>
      </button>
    </div>

    <!-- Quick Search Box -->
    <div class="rk-search-container">
      <form method="GET" action="{{ route('owner.rekapan-keterlambatan.role', $roleCode) }}" id="quickSearchForm"
        class="rk-search-form">
        @if(request('year'))
          <input type="hidden" name="year" value="{{ request('year') }}">
        @endif
        @if(request('month'))
          <input type="hidden" name="month" value="{{ request('month') }}">
        @endif
        @if(request('filter_age'))
          <input type="hidden" name="filter_age" value="{{ request('filter_age') }}">
        @endif
        @if(request('status_filter'))
          <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
        @endif
        @if(request('filter_bagian'))
          <input type="hidden" name="filter_bagian" value="{{ request('filter_bagian') }}">
        @endif
        @if(request('filter_vendor'))
          <input type="hidden" name="filter_vendor" value="{{ request('filter_vendor') }}">
        @endif
        @if(request('filter_kebun'))
          <input type="hidden" name="filter_kebun" value="{{ request('filter_kebun') }}">
        @endif
        @if(request('filter_kriteria_cf'))
          <input type="hidden" name="filter_kriteria_cf" value="{{ request('filter_kriteria_cf') }}">
        @endif
        @if(request('per_page'))
          <input type="hidden" name="per_page" value="{{ request('per_page') }}">
        @endif

        <div class="rk-search-wrapper">
          <i class="fas fa-search"></i>
          <input type="text" name="search" id="quickSearchInput" value="{{ request('search') }}"
            placeholder="Cari nomor agenda, nilai rupiah, kebun, vendor, uraian..." class="rk-search-input"
            onkeypress="if(event.key === 'Enter') { document.getElementById('quickSearchForm').submit(); }">
        </div>

        <button type="submit" class="rk-search-btn">
          <i class="fas fa-search"></i>
          <span>Cari</span>
        </button>

        @if(request('search'))
          <a href="{{ route('owner.rekapan-keterlambatan.role', array_merge([$roleCode], request()->except(['search', 'page']))) }}"
            class="rk-search-reset">
            <i class="fas fa-times"></i>
            <span>Reset</span>
          </a>
        @endif
      </form>

      @if(request('search'))
        <div class="rk-search-info">
          <i class="fas fa-info-circle"></i>
          <span>Menampilkan hasil pencarian: <strong>"{{ request('search') }}"</strong></span>
        </div>
      @endif
    </div>

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
          <input type="hidden" name="status_filter" value="{{ request('status_filter', 'all') }}">

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

            <!-- Bulan -->
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-calendar-alt"></i> Bulan
              </label>
              <select name="month" class="filter-select" onchange="applyFilter()">
                <option value="">Semua Bulan</option>
                @foreach($availableMonths as $monthNum => $monthName)
                  <option value="{{ $monthNum }}" {{ request('month') == $monthNum ? 'selected' : '' }}>{{ $monthName }}
                  </option>
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
                // Daily thresholds: 24 hours peringatan, 72 hours terlambat
                if ($ageHours >= 72) {
                  $ageColor = 'red';  // TERLAMBAT
                } elseif ($ageHours >= 24) {
                  $ageColor = 'yellow';  // PERINGATAN
                } else {
                  $ageColor = 'green';  // AMAN
                }
              } elseif ($roleCode === 'pembayaran') {
                // Weekly thresholds: 1 week (168 hours) peringatan, 3 weeks (504 hours) terlambat
                if ($ageHours >= 504) {
                  $ageColor = 'red';  // TERLAMBAT (> 3 minggu)
                } elseif ($ageHours >= 168) {
                  $ageColor = 'yellow';  // PERINGATAN (1-3 minggu)
                } else {
                  $ageColor = 'green';  // AMAN (< 1 minggu)
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
                  <span class="rk-badge {{ $dokumen->is_completed ? 'rk-badge-completed' : 'rk-badge-active' }}">
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
                  <span class="rk-badge rk-badge-completed rk-badge-sm">
                    @if($roleCode === 'pembayaran')
                      <i class="fas fa-check-circle"></i> Sudah Dibayar
                    @else
                      <i class="fas fa-paper-plane"></i> Sudah Dikirim
                    @endif
                  </span>
                @else
                  <span class="rk-badge rk-badge-active rk-badge-sm">
                    <i class="fas fa-spinner"></i> Sedang Diproses
                  </span>
                @endif
              </div>
            </div>

          @endforeach
        </div>
      @endif

      <!-- Modern Pagination -->
      @if($dokumens->hasPages())
        <div class="modern-pagination">
          <div class="pagination-info">
            Halaman <strong>{{ $dokumens->currentPage() }}</strong> dari <strong>{{ $dokumens->lastPage() }}</strong>
            &nbsp;·&nbsp; {{ $dokumens->total() }} dokumen
          </div>
          <div class="pagination-controls">
            {{-- First + Prev --}}
            @if($dokumens->onFirstPage())
              <span class="pg-nav disabled"><i class="fas fa-angles-left"></i></span>
              <span class="pg-nav disabled"><i class="fas fa-angle-left"></i> Prev</span>
            @else
              <a href="{{ $dokumens->url(1) }}" class="pg-nav" title="Halaman pertama"><i class="fas fa-angles-left"></i></a>
              <a href="{{ $dokumens->previousPageUrl() }}" class="pg-nav"><i class="fas fa-angle-left"></i> Prev</a>
            @endif

            {{-- Page numbers with smart truncation --}}
            @php
              $current = $dokumens->currentPage();
              $last = $dokumens->lastPage();
              $window = 2;
              $start = max(1, $current - $window);
              $end = min($last, $current + $window);
            @endphp

            @if($start > 1)
              <a href="{{ $dokumens->url(1) }}" class="pg-btn">1</a>
              @if($start > 2)
                <span class="pg-ellipsis">···</span>
              @endif
            @endif

            @for($i = $start; $i <= $end; $i++)
              @if($i == $current)
                <span class="pg-btn active">{{ $i }}</span>
              @else
                <a href="{{ $dokumens->url($i) }}" class="pg-btn">{{ $i }}</a>
              @endif
            @endfor

            @if($end < $last)
              @if($end < $last - 1)
                <span class="pg-ellipsis">···</span>
              @endif
              <a href="{{ $dokumens->url($last) }}" class="pg-btn">{{ $last }}</a>
            @endif

            {{-- Next + Last --}}
            @if($dokumens->hasMorePages())
              <a href="{{ $dokumens->nextPageUrl() }}" class="pg-nav">Next <i class="fas fa-angle-right"></i></a>
              <a href="{{ $dokumens->url($last) }}" class="pg-nav" title="Halaman terakhir"><i
                  class="fas fa-angles-right"></i></a>
            @else
              <span class="pg-nav disabled">Next <i class="fas fa-angle-right"></i></span>
              <span class="pg-nav disabled"><i class="fas fa-angles-right"></i></span>
            @endif
          </div>
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
          <h6 class="rk-table-heading">
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
                      // Daily thresholds
                      if ($ageHours >= 72) {
                        $ageColor = 'red';  // TERLAMBAT
                      } elseif ($ageHours >= 24) {
                        $ageColor = 'yellow';  // PERINGATAN
                      } else {
                        $ageColor = 'green';  // AMAN
                      }
                    } elseif ($roleCode === 'pembayaran') {
                      // Weekly thresholds: 1 week (168 hours) peringatan, 3 weeks (504 hours) terlambat
                      if ($ageHours >= 504) {
                        $ageColor = 'red';  // TERLAMBAT (> 3 minggu)
                      } elseif ($ageHours >= 168) {
                        $ageColor = 'yellow';  // PERINGATAN (1-3 minggu)
                      } else {
                        $ageColor = 'green';  // AMAN (< 1 minggu)
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
                        <span class="rk-badge rk-badge-completed rk-badge-sm">
                          <i class="fas fa-paper-plane"></i> Selesai
                        </span>
                      @else
                        <span class="rk-badge rk-badge-active rk-badge-sm">
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

        <!-- Modern Pagination -->
        @if($dokumens->hasPages())
          <div class="modern-pagination">
            <div class="pagination-info">
              Halaman <strong>{{ $dokumens->currentPage() }}</strong> dari <strong>{{ $dokumens->lastPage() }}</strong>
              &nbsp;·&nbsp; {{ $dokumens->total() }} dokumen
            </div>
            <div class="pagination-controls">
              {{-- First + Prev --}}
              @if($dokumens->onFirstPage())
                <span class="pg-nav disabled"><i class="fas fa-angles-left"></i></span>
                <span class="pg-nav disabled"><i class="fas fa-angle-left"></i> Prev</span>
              @else
                <a href="{{ $dokumens->url(1) }}" class="pg-nav" title="Halaman pertama"><i class="fas fa-angles-left"></i></a>
                <a href="{{ $dokumens->previousPageUrl() }}" class="pg-nav"><i class="fas fa-angle-left"></i> Prev</a>
              @endif

              {{-- Page numbers with smart truncation --}}
              @php
                $current = $dokumens->currentPage();
                $last = $dokumens->lastPage();
                $window = 2;
                $start = max(1, $current - $window);
                $end = min($last, $current + $window);
              @endphp

              @if($start > 1)
                <a href="{{ $dokumens->url(1) }}" class="pg-btn">1</a>
                @if($start > 2)
                  <span class="pg-ellipsis">···</span>
                @endif
              @endif

              @for($i = $start; $i <= $end; $i++)
                @if($i == $current)
                  <span class="pg-btn active">{{ $i }}</span>
                @else
                  <a href="{{ $dokumens->url($i) }}" class="pg-btn">{{ $i }}</a>
                @endif
              @endfor

              @if($end < $last)
                @if($end < $last - 1)
                  <span class="pg-ellipsis">···</span>
                @endif
                <a href="{{ $dokumens->url($last) }}" class="pg-btn">{{ $last }}</a>
              @endif

              {{-- Next + Last --}}
              @if($dokumens->hasMorePages())
                <a href="{{ $dokumens->nextPageUrl() }}" class="pg-nav">Next <i class="fas fa-angle-right"></i></a>
                <a href="{{ $dokumens->url($last) }}" class="pg-nav" title="Halaman terakhir"><i
                    class="fas fa-angles-right"></i></a>
              @else
                <span class="pg-nav disabled">Next <i class="fas fa-angle-right"></i></span>
                <span class="pg-nav disabled"><i class="fas fa-angles-right"></i></span>
              @endif
            </div>
          </div>
        @endif
      @endif
    </div>
  </div>

  <!-- Export Confirmation Modal -->
  <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header"
          style="background: linear-gradient(135deg, #217346 0%, #2e8b57 100%); color: white; border-radius: 16px 16px 0 0; border-bottom: none;">
          <h5 class="modal-title" id="exportModalLabel" style="font-weight: 700;">
            <i class="fas fa-file-excel me-2"></i> Export Rekapan Keterlambatan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 24px;">
          <p style="color: #666; margin-bottom: 20px;">Pilih periode data yang ingin di-export:</p>

          <div class="mb-3">
            <label for="exportYear" class="form-label" style="font-weight: 600; color: var(--primary-color);">
              <i class="fas fa-calendar me-1"></i> Tahun
            </label>
            <select id="exportYear" class="form-select"
              style="border-radius: 8px; border: 2px solid #e5e7eb; padding: 10px 16px;">
              <option value="">Semua Tahun</option>
              @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="exportMonth" class="form-label" style="font-weight: 600; color: var(--primary-color);">
              <i class="fas fa-calendar-alt me-1"></i> Bulan
            </label>
            <select id="exportMonth" class="form-select"
              style="border-radius: 8px; border: 2px solid #e5e7eb; padding: 10px 16px;">
              <option value="">Semua Bulan</option>
              @foreach($availableMonths as $monthNum => $monthName)
                <option value="{{ $monthNum }}" {{ request('month') == $monthNum ? 'selected' : '' }}>{{ $monthName }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="exportStatus" class="form-label" style="font-weight: 600; color: var(--primary-color);">
              <i class="fas fa-filter me-1"></i> Status Dokumen
            </label>
            <select id="exportStatus" class="form-select"
              style="border-radius: 8px; border: 2px solid #e5e7eb; padding: 10px 16px;">
              <option value="">Semua Status</option>
              <option value="aman">Aman (< 24 jam)</option>
              <option value="peringatan">Peringatan (24-72 jam)</option>
              <option value="terlambat">Terlambat (≥ 72 jam)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            style="border-radius: 8px; padding: 10px 20px;">
            <i class="fas fa-times me-1"></i> Batal
          </button>
          <button type="button" class="btn" onclick="confirmExport()"
            style="background: linear-gradient(135deg, #217346 0%, #2e8b57 100%); color: white; border-radius: 8px; padding: 10px 20px; font-weight: 600;">
            <i class="fas fa-download me-1"></i> Download Excel
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Filter Pan  el Functionality
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
        if (key.startsWith('filter_') || key === 'search' || key === 'year' || key === 'month') {
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
        if ((key === 'year' || key === 'month') && value && value !== '') {
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

    // Timeframe Filter Function (for horizontal year/month filter)
    function applyTimeframeFilter() {
      const yearSelect = document.querySelector('.timeframe-filter-select[name="year"]');
      const monthSelect = document.querySelector('.timeframe-filter-select[name="month"]');

      const currentUrl = new URL(window.location.href);
      const params = currentUrl.searchParams;

      // Update year parameter
      if (yearSelect && yearSelect.value) {
        params.set('year', yearSelect.value);
      } else if (yearSelect) {
        params.delete('year');
      }

      // Update month parameter
      if (monthSelect && monthSelect.value) {
        params.set('month', monthSelect.value);
      } else if (monthSelect) {
        params.delete('month');
      }

      // Remove page parameter to go back to first page
      params.delete('page');

      window.location.href = currentUrl.toString();
    }

    // Change entries per page
    function changeEntriesPerPage(value) {
      const currentUrl = new URL(window.location.href);
      const params = currentUrl.searchParams;

      if (value && value !== '10') {
        params.set('per_page', value);
      } else {
        params.delete('per_page');
      }

      // Reset to page 1 when changing entries
      params.delete('page');

      window.location.href = currentUrl.toString();
    }

    // Apply quick search
    function applyQuickSearch() {
      const searchValue = document.getElementById('quickSearchInput').value.trim();
      const currentUrl = new URL(window.location.href);
      const params = currentUrl.searchParams;

      if (searchValue) {
        params.set('search', searchValue);
      } else {
        params.delete('search');
      }

      // Reset to page 1 when searching
      params.delete('page');

      window.location.href = currentUrl.toString();
    }

    // Export Modal Functions
    function showExportModal() {
      const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
      exportModal.show();
    }

    function confirmExport() {
      const year = document.getElementById('exportYear').value;
      const month = document.getElementById('exportMonth').value;
      const status = document.getElementById('exportStatus').value;

      let exportUrl = '{{ route('owner.rekapan-keterlambatan.export', $roleCode) }}';
      const params = new URLSearchParams();

      if (year) {
        params.set('year', year);
      }
      if (month) {
        params.set('month', month);
      }

      // Convert filter_age to status if present and status not manually selected
      // Read current filter_age from URL
      const currentUrl = new URL(window.location.href);
      const filterAge = currentUrl.searchParams.get('filter_age');

      if (status) {
        // User explicitly selected status in modal - use that
        params.set('status', status);
      } else if (filterAge) {
        // No manual status selection, but filter_age exists in URL
        // Convert filter_age to status for export compatibility
        const ageToStatus = {
          '1': 'aman',
          '2': 'peringatan',
          '3+': 'terlambat'
        };
        const convertedStatus = ageToStatus[filterAge];
        if (convertedStatus) {
          params.set('status', convertedStatus);
        }
      }

      if (params.toString()) {
        exportUrl += '?' + params.toString();
      }

      // Close Modal
      const modalEl = document.getElementById('exportModal');
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) {
        modal.hide();
      }

      // Redirect to download
      window.location.href = exportUrl;
    }
  </script>

@endsection
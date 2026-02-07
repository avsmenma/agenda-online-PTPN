@extends('layouts.app')

@section('content')
  <style>
    /* ===== IMPORT GOOGLE FONTS ===== */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    /* ===== PREMIUM SAAS DESIGN SYSTEM ===== */
    :root {
      /* Core Colors */
      --bg-primary: #fafbfc;
      --bg-secondary: #f8fafc;
      --bg-tertiary: #ffffff;

      /* Text Colors */
      --text-primary: #0f172a;
      --text-secondary: #475569;
      --text-tertiary: #94a3b8;
      --text-muted: #cbd5e1;

      /* Brand Colors - Soft Pastels */
      --brand-primary: #10b981;
      --brand-primary-soft: rgba(16, 185, 129, 0.1);
      --brand-primary-glow: rgba(16, 185, 129, 0.2);

      /* Status Colors - Refined */
      --status-emerald: #10b981;
      --status-emerald-soft: rgba(16, 185, 129, 0.12);
      --status-emerald-glow: 0 0 20px rgba(16, 185, 129, 0.3);

      --status-amber: #f59e0b;
      --status-amber-soft: rgba(245, 158, 11, 0.12);
      --status-amber-glow: 0 0 20px rgba(245, 158, 11, 0.3);

      --status-rose: #f43f5e;
      --status-rose-soft: rgba(244, 63, 94, 0.12);
      --status-rose-glow: 0 0 20px rgba(244, 63, 94, 0.3);

      --status-blue: #3b82f6;
      --status-blue-soft: rgba(59, 130, 246, 0.12);
      --status-blue-glow: 0 0 20px rgba(59, 130, 246, 0.3);

      --status-violet: #8b5cf6;
      --status-violet-soft: rgba(139, 92, 246, 0.12);

      /* Shadows & Effects */
      --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
      --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.08);
      --shadow-xl: 0 16px 48px rgba(0, 0, 0, 0.1);

      /* Glass Effect */
      --glass-bg: rgba(255, 255, 255, 0.7);
      --glass-border: rgba(255, 255, 255, 0.5);
      --glass-blur: blur(20px);

      /* Border */
      --border-light: #e2e8f0;
      --border-lighter: #f1f5f9;

      /* Radius */
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 20px;
      --radius-2xl: 24px;

      /* Transitions */
      --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
      --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
      --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ===== BASE STYLES ===== */
    .premium-dashboard {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--bg-primary);
      min-height: 100vh;
      padding: 2rem;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* ===== HEADER SECTION ===== */
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1.5rem;
    }

    .header-content {
      flex: 1;
    }

    .header-title {
      font-size: 1.875rem;
      font-weight: 700;
      color: var(--text-primary);
      letter-spacing: -0.025em;
      margin: 0 0 0.5rem 0;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .header-title-icon {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--brand-primary) 0%, #059669 100%);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.125rem;
    }

    .header-subtitle {
      font-size: 0.9375rem;
      color: var(--text-secondary);
      font-weight: 400;
      margin: 0;
    }

    .header-actions {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .btn-export {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: var(--radius-md);
      border: 1px solid var(--border-light);
      background: var(--bg-tertiary);
      color: var(--text-secondary);
      cursor: pointer;
      transition: var(--transition-base);
      text-decoration: none;
    }

    .btn-export:hover {
      background: var(--bg-secondary);
      color: var(--brand-primary);
      border-color: var(--brand-primary);
      transform: translateY(-1px);
    }

    .btn-export i {
      font-size: 1rem;
    }

    /* ===== BENTO GRID ===== */
    .bento-grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 1.25rem;
      margin-bottom: 2rem;
    }

    /* Main Stats Row */
    .stat-card {
      background: var(--bg-tertiary);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      border: 1px solid var(--border-lighter);
      box-shadow: var(--shadow-sm);
      transition: var(--transition-base);
      position: relative;
      overflow: hidden;
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
    }

    .stat-card:hover {
      box-shadow: var(--shadow-lg);
      transform: translateY(-2px);
      border-color: var(--border-light);
    }

    .stat-card.active {
      border-color: var(--brand-primary);
      box-shadow: 0 0 0 3px var(--brand-primary-glow);
    }

    .stat-card--total {
      grid-column: span 3;
    }

    .stat-card--pending {
      grid-column: span 3;
    }

    .stat-card--ready {
      grid-column: span 3;
    }

    .stat-card--paid {
      grid-column: span 3;
    }

    /* Total Card - Solid Green Gradient */
    .stat-card--total {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      border: none;
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
    }

    .stat-card--total:hover {
      box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4);
      transform: translateY(-3px);
    }

    .stat-card--total.active {
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3), 0 8px 30px rgba(16, 185, 129, 0.4);
    }

    .stat-card--total .stat-label,
    .stat-card--total .stat-value,
    .stat-card--total .stat-subvalue {
      color: white;
    }

    .stat-card--total .stat-label {
      opacity: 0.9;
    }

    .stat-card--total .stat-subvalue {
      opacity: 0.85;
    }

    .stat-card--total .stat-icon {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }

    .stat-card-content {
      display: flex;
      flex-direction: column;
    }

    .stat-icon {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      flex-shrink: 0;
    }

    .stat-icon--total {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }

    .stat-icon--pending {
      background: var(--status-amber-soft);
      color: var(--status-amber);
    }

    .stat-icon--ready {
      background: var(--status-blue-soft);
      color: var(--status-blue);
    }

    .stat-icon--paid {
      background: var(--status-emerald-soft);
      color: var(--status-emerald);
    }

    .stat-label {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 0.375rem;
    }

    .stat-value {
      font-size: 1.875rem;
      font-weight: 700;
      color: var(--text-primary);
      letter-spacing: -0.025em;
      line-height: 1.2;
      margin-bottom: 0.25rem;
    }

    .stat-subvalue {
      font-size: 0.8125rem;
      color: var(--status-emerald);
      font-weight: 600;
    }

    .stat-subvalue-link {
      font-size: 0.75rem;
      color: var(--text-tertiary);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.25rem;
      margin-top: 0.25rem;
    }

    .stat-subvalue-link:hover {
      color: var(--brand-primary);
    }

    /* Deadline Cards */
    .deadline-card {
      grid-column: span 4;
      background: var(--bg-tertiary);
      border-radius: var(--radius-lg);
      padding: 1.25rem 1.5rem;
      border: 1px solid var(--border-lighter);
      box-shadow: var(--shadow-sm);
      transition: var(--transition-base);
      display: flex;
      align-items: center;
      gap: 1rem;
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      position: relative;
      overflow: hidden;
    }

    .deadline-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      transition: var(--transition-base);
    }

    .deadline-card:hover {
      box-shadow: var(--shadow-lg);
      transform: translateY(-2px);
    }

    .deadline-card--aman::before {
      background: var(--status-emerald);
    }

    .deadline-card--peringatan::before {
      background: var(--status-amber);
    }

    .deadline-card--terlambat::before {
      background: var(--status-rose);
    }

    .deadline-card--aman:hover {
      box-shadow: var(--status-emerald-glow), var(--shadow-lg);
    }

    .deadline-card--peringatan:hover {
      box-shadow: var(--status-amber-glow), var(--shadow-lg);
    }

    .deadline-card--terlambat:hover {
      box-shadow: var(--status-rose-glow), var(--shadow-lg);
    }

    .deadline-icon {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      flex-shrink: 0;
    }

    .deadline-card--aman .deadline-icon {
      background: var(--status-emerald-soft);
      color: var(--status-emerald);
    }

    .deadline-card--peringatan .deadline-icon {
      background: var(--status-amber-soft);
      color: var(--status-amber);
    }

    .deadline-card--terlambat .deadline-icon {
      background: var(--status-rose-soft);
      color: var(--status-rose);
    }

    .deadline-content {
      flex: 1;
    }

    .deadline-label {
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--text-tertiary);
      margin-bottom: 0.25rem;
    }

    .deadline-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-primary);
    }

    .deadline-desc {
      font-size: 0.75rem;
      color: var(--text-tertiary);
      margin-top: 0.25rem;
    }

    /* ===== FILTER SECTION - FLOATING BAR ===== */
    .filter-section {
      background: var(--bg-tertiary);
      border-radius: var(--radius-xl);
      padding: 1.25rem 1.5rem;
      margin-bottom: 1.5rem;
      border: 1px solid var(--border-lighter);
      box-shadow: var(--shadow-sm);
    }

    .filter-row {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .filter-search {
      flex: 1;
      min-width: 280px;
      position: relative;
    }

    .filter-search-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-tertiary);
      font-size: 0.875rem;
      pointer-events: none;
    }

    .filter-search input {
      width: 100%;
      height: 44px;
      padding: 0 1rem 0 2.75rem;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-md);
      font-size: 0.875rem;
      font-family: inherit;
      background: var(--bg-secondary);
      color: var(--text-primary);
      transition: var(--transition-fast);
    }

    .filter-search input:focus {
      outline: none;
      border-color: var(--brand-primary);
      box-shadow: 0 0 0 3px var(--brand-primary-glow);
      background: var(--bg-tertiary);
    }

    .filter-search input::placeholder {
      color: var(--text-tertiary);
    }

    .filter-divider {
      width: 1px;
      height: 28px;
      background: var(--border-light);
    }

    .filter-select-group {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .filter-select {
      height: 44px;
      padding: 0 2.5rem 0 1rem;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-md);
      font-size: 0.8125rem;
      font-family: inherit;
      font-weight: 500;
      background: var(--bg-secondary) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E") no-repeat right 0.75rem center;
      color: var(--text-primary);
      cursor: pointer;
      transition: var(--transition-fast);
      appearance: none;
      min-width: 130px;
    }

    .filter-select:focus {
      outline: none;
      border-color: var(--brand-primary);
      box-shadow: 0 0 0 3px var(--brand-primary-glow);
      background-color: var(--bg-tertiary);
    }

    .filter-actions {
      display: flex;
      gap: 0.5rem;
      margin-left: auto;
    }

    .btn-filter {
      height: 44px;
      padding: 0 1.25rem;
      border-radius: var(--radius-md);
      font-size: 0.8125rem;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      transition: var(--transition-base);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      border: none;
    }

    .btn-filter--primary {
      background: var(--brand-primary);
      color: white;
    }

    .btn-filter--primary:hover {
      background: #059669;
      transform: translateY(-1px);
    }

    .btn-filter--secondary {
      background: transparent;
      color: var(--text-secondary);
      border: 1px solid var(--border-light);
    }

    .btn-filter--secondary:hover {
      background: var(--bg-secondary);
      color: var(--text-primary);
    }

    /* ===== TABLE SECTION ===== */
    .table-section {
      background: var(--bg-tertiary);
      border-radius: var(--radius-xl);
      border: 1px solid var(--border-lighter);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border-lighter);
    }

    .table-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .table-title i {
      color: var(--text-tertiary);
    }

    .table-count {
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--text-tertiary);
      background: var(--bg-secondary);
      padding: 0.25rem 0.75rem;
      border-radius: 999px;
      margin-left: 0.5rem;
    }

    .table-toggle {
      display: flex;
      background: var(--bg-secondary);
      border-radius: var(--radius-md);
      padding: 0.25rem;
    }

    .table-toggle-btn {
      padding: 0.5rem 1rem;
      font-size: 0.8125rem;
      font-weight: 500;
      font-family: inherit;
      color: var(--text-secondary);
      background: transparent;
      border: none;
      border-radius: var(--radius-sm);
      cursor: pointer;
      transition: var(--transition-fast);
    }

    .table-toggle-btn.active {
      background: var(--bg-tertiary);
      color: var(--text-primary);
      box-shadow: var(--shadow-sm);
    }

    .table-toggle-btn:hover:not(.active) {
      color: var(--text-primary);
    }

    /* Data Table */
    .data-table-wrapper {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th {
      padding: 0.875rem 1rem;
      text-align: left;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      background: var(--bg-secondary);
      border-bottom: 1px solid var(--border-lighter);
      white-space: nowrap;
    }

    .data-table th:first-child {
      padding-left: 1.5rem;
    }

    .data-table th:last-child {
      padding-right: 1.5rem;
    }

    .data-table td {
      padding: 1rem;
      font-size: 0.875rem;
      color: var(--text-secondary);
      border-bottom: 1px solid var(--border-lighter);
      vertical-align: middle;
    }

    .data-table td:first-child {
      padding-left: 1.5rem;
    }

    .data-table td:last-child {
      padding-right: 1.5rem;
    }

    .data-table tbody tr {
      transition: var(--transition-fast);
    }

    .data-table tbody tr:hover {
      background: var(--bg-secondary);
    }

    .data-table tbody tr:last-child td {
      border-bottom: none;
    }

    /* Cell Styles */
    .cell-primary {
      font-weight: 600;
      color: var(--text-primary);
    }

    .cell-mono {
      font-family: 'JetBrains Mono', 'Fira Code', monospace;
      font-size: 0.8125rem;
    }

    .cell-rupiah {
      font-weight: 600;
      color: var(--text-primary);
      font-variant-numeric: tabular-nums;
    }

    .cell-vendor {
      max-width: 180px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .cell-uraian {
      max-width: 220px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* Status Pills */
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.375rem 0.75rem;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 600;
      white-space: nowrap;
    }

    .status-pill--ready {
      background: var(--status-violet-soft);
      color: var(--status-violet);
    }

    .status-pill--paid {
      background: var(--status-emerald-soft);
      color: var(--status-emerald);
    }

    .status-pill--pending {
      background: var(--status-amber-soft);
      color: var(--status-amber);
    }

    .status-pill i {
      font-size: 0.625rem;
    }

    /* Action Button */
    .btn-action {
      width: 36px;
      height: 36px;
      border-radius: var(--radius-md);
      border: 1px solid var(--border-light);
      background: var(--bg-tertiary);
      color: var(--text-secondary);
      cursor: pointer;
      transition: var(--transition-base);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }

    .btn-action:hover {
      background: var(--brand-primary);
      border-color: var(--brand-primary);
      color: white;
      transform: scale(1.05);
    }

    .btn-action i {
      font-size: 0.875rem;
    }

    /* ===== VENDOR GROUP VIEW ===== */
    .vendor-groups {
      padding: 1rem 1.5rem;
    }

    .vendor-group {
      margin-bottom: 0.75rem;
      border: 1px solid var(--border-lighter);
      border-radius: var(--radius-lg);
      overflow: hidden;
      transition: var(--transition-base);
    }

    .vendor-group:hover {
      border-color: var(--border-light);
    }

    .vendor-group-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.25rem;
      background: var(--bg-secondary);
      cursor: pointer;
      transition: var(--transition-fast);
    }

    .vendor-group-header:hover {
      background: var(--bg-primary);
    }

    .vendor-group-header.expanded {
      background: var(--brand-primary-soft);
    }

    .vendor-group-info {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .vendor-icon {
      width: 36px;
      height: 36px;
      border-radius: var(--radius-md);
      background: var(--brand-primary-soft);
      color: var(--brand-primary);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .vendor-name {
      font-weight: 600;
      color: var(--text-primary);
      font-size: 0.9375rem;
    }

    .vendor-count {
      font-size: 0.8125rem;
      color: var(--text-tertiary);
      margin-left: 0.5rem;
    }

    .vendor-group-stats {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }

    .vendor-stat {
      text-align: right;
    }

    .vendor-stat-label {
      font-size: 0.6875rem;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .vendor-stat-value {
      font-size: 0.9375rem;
      font-weight: 700;
      color: var(--text-primary);
    }

    .vendor-chevron {
      color: var(--text-tertiary);
      transition: var(--transition-fast);
    }

    .vendor-group-header.expanded .vendor-chevron {
      transform: rotate(180deg);
      color: var(--brand-primary);
    }

    .vendor-group-body {
      display: none;
      border-top: 1px solid var(--border-lighter);
    }

    .vendor-group-body.show {
      display: block;
    }

    /* ===== PAGINATION ===== */
    .pagination-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem;
      border-top: 1px solid var(--border-lighter);
    }

    .pagination-info {
      font-size: 0.8125rem;
      color: var(--text-tertiary);
    }

    .pagination {
      display: flex;
      gap: 0.25rem;
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .pagination .page-item .page-link {
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 36px;
      height: 36px;
      padding: 0 0.75rem;
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--text-secondary);
      background: var(--bg-tertiary);
      border: 1px solid var(--border-light);
      border-radius: var(--radius-md);
      text-decoration: none;
      transition: var(--transition-fast);
    }

    .pagination .page-item .page-link:hover {
      background: var(--bg-secondary);
      color: var(--text-primary);
    }

    .pagination .page-item.active .page-link {
      background: var(--brand-primary);
      border-color: var(--brand-primary);
      color: white;
    }

    .pagination .page-item.disabled .page-link {
      opacity: 0.5;
      pointer-events: none;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
      padding: 4rem 2rem;
      text-align: center;
    }

    .empty-state-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 1.5rem;
      border-radius: 50%;
      background: var(--bg-secondary);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .empty-state-icon i {
      font-size: 2rem;
      color: var(--text-tertiary);
    }

    .empty-state-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0 0 0.5rem 0;
    }

    .empty-state-desc {
      font-size: 0.875rem;
      color: var(--text-tertiary);
      max-width: 360px;
      margin: 0 auto 1.5rem;
    }

    .btn-empty {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      background: var(--brand-primary);
      color: white;
      border-radius: var(--radius-md);
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition-base);
    }

    .btn-empty:hover {
      background: #059669;
      transform: translateY(-1px);
      color: white;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1280px) {

      .stat-card--total,
      .stat-card--pending,
      .stat-card--ready,
      .stat-card--paid {
        grid-column: span 6;
      }

      .deadline-card {
        grid-column: span 4;
      }
    }

    @media (max-width: 992px) {
      .deadline-card {
        grid-column: span 6;
      }
    }

    @media (max-width: 768px) {
      .premium-dashboard {
        padding: 1rem;
      }

      .bento-grid {
        gap: 0.75rem;
      }

      .stat-card--total,
      .stat-card--pending,
      .stat-card--ready,
      .stat-card--paid,
      .deadline-card {
        grid-column: span 12;
      }

      .filter-row {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-search {
        min-width: 100%;
      }

      .filter-divider {
        display: none;
      }

      .filter-select-group {
        flex-wrap: wrap;
      }

      .filter-select {
        flex: 1;
        min-width: 120px;
      }

      .filter-actions {
        margin-left: 0;
        justify-content: stretch;
      }

      .filter-actions .btn-filter {
        flex: 1;
        justify-content: center;
      }

      .header-actions {
        width: 100%;
        justify-content: flex-end;
      }
    }

    /* ===== ANIMATIONS ===== */
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

    @keyframes countUp {
      from {
        opacity: 0;
        transform: scale(0.8);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.6;
      }
    }

    .animate-fade-in {
      animation: fadeIn 0.5s ease-out forwards;
    }

    .animate-delay-1 {
      animation-delay: 0.1s;
      opacity: 0;
    }

    .animate-delay-2 {
      animation-delay: 0.15s;
      opacity: 0;
    }

    .animate-delay-3 {
      animation-delay: 0.2s;
      opacity: 0;
    }

    .animate-delay-4 {
      animation-delay: 0.25s;
      opacity: 0;
    }

    .animate-delay-5 {
      animation-delay: 0.3s;
      opacity: 0;
    }

    .animate-delay-6 {
      animation-delay: 0.35s;
      opacity: 0;
    }

    .animate-delay-7 {
      animation-delay: 0.4s;
      opacity: 0;
    }

    .count-animate {
      animation: countUp 0.6s ease-out forwards;
    }

    /* Lucide-style icons (thin stroke) */
    .premium-dashboard i.fas,
    .premium-dashboard i.far {
      font-weight: 400;
    }

    /* ============================================ */
    /* ADVANCED FILTER PANEL STYLES */
    /* ============================================ */
    .advanced-filter-toggle {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.625rem 1rem;
      background: transparent;
      border: 1px solid var(--border-light);
      color: var(--text-secondary);
      font-size: 0.8125rem;
      font-weight: 600;
      border-radius: var(--radius-md);
      cursor: pointer;
      transition: var(--transition-base);
      font-family: inherit;
    }

    .advanced-filter-toggle:hover {
      background: var(--brand-primary);
      color: white;
      border-color: var(--brand-primary);
      transform: translateY(-1px);
    }

    .advanced-filter-toggle.active {
      background: var(--brand-primary);
      color: white;
      border-color: var(--brand-primary);
    }

    .advanced-filter-toggle i {
      transition: transform 0.3s ease;
    }

    .advanced-filter-toggle.active i.fa-chevron-down {
      transform: rotate(180deg);
    }

    .active-filters-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 18px;
      height: 18px;
      background: #dc3545;
      color: white;
      font-size: 0.6875rem;
      font-weight: 700;
      border-radius: 50%;
      margin-left: 0.25rem;
    }

    .advanced-filter-toggle.active .active-filters-badge {
      background: white;
      color: var(--brand-primary);
    }

    .advanced-filter-panel {
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transition: all 0.4s ease;
      margin-top: 0;
    }

    .advanced-filter-panel.show {
      max-height: 500px;
      opacity: 1;
      margin-top: 1rem;
    }

    .advanced-filter-content {
      background: var(--bg-secondary);
      border: 1px solid var(--border-lighter);
      border-radius: var(--radius-lg);
      padding: 1.25rem;
    }

    .advanced-filter-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 0.375rem;
    }

    .filter-group label {
      font-weight: 600;
      color: var(--text-secondary);
      font-size: 0.75rem;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.375rem;
    }

    .filter-group label i {
      color: var(--text-tertiary);
    }

    .filter-group select {
      width: 100%;
      height: 40px;
      padding: 0 2rem 0 0.75rem;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-sm);
      font-size: 0.8125rem;
      font-family: inherit;
      color: var(--text-primary);
      background: var(--bg-tertiary) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E") no-repeat right 0.5rem center;
      cursor: pointer;
      transition: var(--transition-fast);
      appearance: none;
    }

    .filter-group select:hover {
      border-color: var(--brand-primary);
    }

    .filter-group select:focus {
      outline: none;
      border-color: var(--brand-primary);
      box-shadow: 0 0 0 3px var(--brand-primary-glow);
    }

    .advanced-filter-actions {
      display: flex;
      gap: 0.75rem;
      margin-top: 1rem;
      justify-content: flex-end;
      flex-wrap: wrap;
    }

    .btn-advanced-reset {
      padding: 0.625rem 1.25rem;
      background: var(--bg-tertiary);
      color: var(--status-rose);
      font-weight: 600;
      font-size: 0.8125rem;
      border: 1px solid rgba(244, 63, 94, 0.3);
      border-radius: var(--radius-md);
      cursor: pointer;
      transition: var(--transition-base);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-family: inherit;
    }

    .btn-advanced-reset:hover {
      background: var(--status-rose);
      color: white;
      border-color: var(--status-rose);
    }

    @media (max-width: 768px) {
      .advanced-filter-grid {
        grid-template-columns: 1fr 1fr;
      }

      .advanced-filter-actions {
        flex-direction: column;
      }

      .btn-advanced-reset {
        width: 100%;
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      .advanced-filter-grid {
        grid-template-columns: 1fr;
      }

      .btn-customize {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: transparent;
        border: 1px solid var(--border-light);
        color: var(--text-secondary);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-base);
      }

      .btn-customize:hover {
        background: var(--bg-surface);
        border-color: var(--primary);
        color: var(--primary);
      }

      .column-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
        padding: 1rem 0;
      }
    }
  </style>

  <div class="premium-dashboard">
    <!-- Header -->
    <header class="dashboard-header">
      <div class="header-content">
        <h1 class="header-title">
          <div class="header-title-icon">
            <i class="fas fa-wallet"></i>
          </div>
          Dashboard Pembayaran
        </h1>
        <p class="header-subtitle">Kelola dan pantau semua dokumen pembayaran</p>
      </div>
      <div class="header-actions">
        <button type="button" class="btn-export" onclick="exportDocument('excel')" title="Export Excel">
          <i class="fas fa-file-excel"></i>
        </button>
        <button type="button" class="btn-export" onclick="exportDocument('pdf')" title="Export PDF">
          <i class="fas fa-file-pdf"></i>
        </button>
      </div>
      <!-- Hidden export form -->
      <form id="exportForm" method="POST" action="{{ route('reports.pembayaran.export') }}" style="display: none;">
        @csrf
        <input type="hidden" name="format" id="exportFormat">
        <input type="hidden" name="status_pembayaran" value="{{ $selectedStatus ?? '' }}">
        <input type="hidden" name="year" value="{{ $selectedYear ?? '' }}">
        <input type="hidden" name="month" value="{{ $selectedMonth ?? '' }}">
        <input type="hidden" name="search" value="{{ $search ?? '' }}">
        <input type="hidden" name="mode" value="{{ $mode ?? 'normal' }}">
        <!-- Advanced filters for export -->
        <input type="hidden" name="filter_vendor" value="{{ request('filter_vendor') ?? '' }}">
        <input type="hidden" name="filter_kategori" value="{{ request('filter_kategori') ?? '' }}">
        <input type="hidden" name="filter_jenis_dokumen" value="{{ request('filter_jenis_dokumen') ?? '' }}">
        <input type="hidden" name="filter_jenis_sub_pekerjaan" value="{{ request('filter_jenis_sub_pekerjaan') ?? '' }}">
        <input type="hidden" name="filter_kebun" value="{{ request('filter_kebun') ?? '' }}">
        <input type="hidden" name="filter_jenis_pembayaran" value="{{ request('filter_jenis_pembayaran') ?? '' }}">
        <div id="exportColumnsContainer"></div>
      </form>
    </header>
    <!-- Bento Grid Stats -->
    <div class="bento-grid">
      <!-- Main Stats Row -->
      <a href="?status_pembayaran=&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card stat-card--total animate-fade-in animate-delay-1 {{ !$selectedStatus ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Total Dokumen</div>
          <div class="stat-value count-animate">{{ number_format($statistics['total_documents']) }}</div>
          <div class="stat-subvalue">Rp {{ number_format($statistics['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon stat-icon--total">
          <i class="fas fa-layer-group"></i>
        </div>
      </a>

      <a href="?status_pembayaran=belum_siap_dibayar&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card stat-card--pending animate-fade-in animate-delay-2 {{ $selectedStatus == 'belum_siap_dibayar' ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Belum Siap Bayar</div>
          <div class="stat-value count-animate">{{ number_format($statistics['by_status']['belum_dibayar']) }}</div>
          <div class="stat-subvalue">Rp
            {{ number_format($statistics['total_nilai_by_status']['belum_dibayar'], 0, ',', '.') }}
          </div>
          <div class="stat-subvalue-link">
            <i class="fas fa-arrow-right"></i> Klik untuk detail analitik
          </div>
        </div>
        <div class="stat-icon stat-icon--pending">
          <i class="fas fa-hourglass-half"></i>
        </div>
      </a>

      <a href="?status_pembayaran=siap_dibayar&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card stat-card--ready animate-fade-in animate-delay-3 {{ $selectedStatus == 'siap_dibayar' ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Siap Dibayar</div>
          <div class="stat-value count-animate">{{ number_format($statistics['by_status']['siap_dibayar']) }}</div>
          <div class="stat-subvalue">Rp
            {{ number_format($statistics['total_nilai_by_status']['siap_dibayar'], 0, ',', '.') }}
          </div>
          <div class="stat-subvalue-link">
            <i class="fas fa-arrow-right"></i> Klik untuk detail analitik
          </div>
        </div>
        <div class="stat-icon stat-icon--ready">
          <i class="fas fa-check-circle"></i>
        </div>
      </a>

      <a href="?status_pembayaran=sudah_dibayar&year={{ $selectedYear ?? '' }}&month={{ $selectedMonth ?? '' }}"
        class="stat-card stat-card--paid animate-fade-in animate-delay-4 {{ $selectedStatus == 'sudah_dibayar' ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Sudah Dibayar</div>
          <div class="stat-value count-animate">{{ number_format($statistics['by_status']['sudah_dibayar']) }}</div>
          <div class="stat-subvalue">Rp
            {{ number_format($statistics['total_nilai_by_status']['sudah_dibayar'], 0, ',', '.') }}
          </div>
          <div class="stat-subvalue-link">
            <i class="fas fa-arrow-right"></i> Klik untuk detail analitik
          </div>
        </div>
        <div class="stat-icon stat-icon--paid">
          <i class="fas fa-check-double"></i>
        </div>
      </a>

      <!-- Deadline Cards -->
      <a href="{{ route('documents.pembayaran.index', ['status_keterlambatan' => 'aman']) }}"
        class="deadline-card deadline-card--aman animate-fade-in animate-delay-5">
        <div class="deadline-icon">
          <i class="fas fa-shield-alt"></i>
        </div>
        <div class="deadline-content">
          <div class="deadline-label">Aman</div>
          <div class="deadline-value">{{ number_format($totalAman) }}</div>
          <div class="deadline-desc">&lt; 1 Minggu</div>
        </div>
      </a>

      <a href="{{ route('documents.pembayaran.index', ['status_keterlambatan' => 'peringatan']) }}"
        class="deadline-card deadline-card--peringatan animate-fade-in animate-delay-6">
        <div class="deadline-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="deadline-content">
          <div class="deadline-label">Peringatan</div>
          <div class="deadline-value">{{ number_format($totalPeringatan) }}</div>
          <div class="deadline-desc">1 - 3 Minggu</div>
        </div>
      </a>

      <a href="{{ route('documents.pembayaran.index', ['status_keterlambatan' => 'terlambat']) }}"
        class="deadline-card deadline-card--terlambat animate-fade-in animate-delay-7">
        <div class="deadline-icon">
          <i class="fas fa-times-circle"></i>
        </div>
        <div class="deadline-content">
          <div class="deadline-label">Terlambat</div>
          <div class="deadline-value">{{ number_format($totalTerlambat) }}</div>
          <div class="deadline-desc">&gt; 3 Minggu</div>
        </div>
      </a>
    </div>

    <!-- Filter Section -->
    <form action="{{ route('dashboard.pembayaran') }}" method="GET" class="filter-section">
      <div class="filter-row">
        <div class="filter-search">
          <i class="fas fa-search filter-search-icon"></i>
          <input type="text" name="search" placeholder="Cari no agenda, SPP, vendor..." value="{{ $search ?? '' }}">
        </div>

        <div class="filter-divider"></div>

        <div class="filter-select-group">
          <select name="status_pembayaran" class="filter-select">
            <option value="">Semua Status</option>
            <option value="belum_siap_dibayar" {{ ($selectedStatus ?? '') == 'belum_siap_dibayar' ? 'selected' : '' }}>Belum
              Siap</option>
            <option value="siap_dibayar" {{ ($selectedStatus ?? '') == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar
            </option>
            <option value="sudah_dibayar" {{ ($selectedStatus ?? '') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar
            </option>
          </select>

          <select name="year" class="filter-select">
            <option value="">Semua Tahun</option>
            @foreach($years ?? [] as $yr)
              <option value="{{ $yr }}" {{ ($selectedYear ?? '') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
          </select>

          <select name="month" class="filter-select">
            <option value="">Semua Bulan</option>
            @php
              $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            @endphp
            @foreach($months as $i => $m)
              <option value="{{ $i + 1 }}" {{ ($selectedMonth ?? '') == ($i + 1) ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn-filter btn-filter--primary">
            <i class="fas fa-search"></i>
            Cari
          </button>
          <a href="{{ route('dashboard.pembayaran') }}" class="btn-filter btn-filter--secondary">
            <i class="fas fa-redo"></i>
            Reset
          </a>

          @php
            $activeAdvancedFilterCount = 0;
            if (request('filter_vendor'))
              $activeAdvancedFilterCount++;
            if (request('filter_kategori'))
              $activeAdvancedFilterCount++;
            if (request('filter_jenis_dokumen'))
              $activeAdvancedFilterCount++;
            if (request('filter_jenis_sub_pekerjaan'))
              $activeAdvancedFilterCount++;
            if (request('filter_kebun'))
              $activeAdvancedFilterCount++;
          @endphp

          <button type="button" class="advanced-filter-toggle {{ $activeAdvancedFilterCount > 0 ? 'active' : '' }}"
            id="advancedFilterToggle">
            <i class="fas fa-sliders-h"></i>
            Filter Lanjutan
            <i class="fas fa-chevron-down"></i>
            @if($activeAdvancedFilterCount > 0)
              <span class="active-filters-badge">{{ $activeAdvancedFilterCount }}</span>
            @endif
          </button>
        </div>
      </div>

      <!-- Advanced Filter Panel -->
      <div class="advanced-filter-panel {{ $activeAdvancedFilterCount > 0 ? 'show' : '' }}" id="advancedFilterPanel">
        <div class="advanced-filter-content">
          <div class="advanced-filter-grid">
            <!-- Vendor Filter -->
            <div class="filter-group">
              <label for="filterVendor"><i class="fas fa-store"></i> Vendor</label>
              <select id="filterVendor" name="filter_vendor">
                <option value="">Semua Vendor</option>
                @foreach($availableDibayarKepada ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_vendor') == $key ? 'selected' : '' }}>
                    {{ Str::limit($value, 30) }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Kriteria Filter -->
            <div class="filter-group">
              <label for="filterKategori"><i class="fas fa-tags"></i> Kriteria CF</label>
              <select id="filterKategori" name="filter_kategori">
                <option value="">Semua Kriteria</option>
                @foreach($availableKategori ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_kategori') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            <!-- Sub Kriteria Filter -->
            <div class="filter-group">
              <label for="filterJenisDokumen"><i class="fas fa-tag"></i> Sub Kriteria</label>
              <select id="filterJenisDokumen" name="filter_jenis_dokumen">
                <option value="">Semua Sub Kriteria</option>
                @foreach($availableJenisDokumen ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_jenis_dokumen') == $key ? 'selected' : '' }}>{{ $value }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Item Sub Kriteria Filter -->
            <div class="filter-group">
              <label for="filterJenisSubPekerjaan"><i class="fas fa-th-list"></i> Item Sub Kriteria</label>
              <select id="filterJenisSubPekerjaan" name="filter_jenis_sub_pekerjaan">
                <option value="">Semua Item</option>
                @foreach($availableJenisSubPekerjaan ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_jenis_sub_pekerjaan') == $key ? 'selected' : '' }}>
                    {{ Str::limit($value, 30) }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Kebun Filter -->
            <div class="filter-group">
              <label for="filterKebun"><i class="fas fa-seedling"></i> Kebun</label>
              <select id="filterKebun" name="filter_kebun">
                <option value="">Semua Kebun</option>
                @foreach($availableKebuns ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_kebun') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            <!-- Jenis Pembayaran Filter -->
            <div class="filter-group">
              <label for="filterJenisPembayaran"><i class="fas fa-money-bill-wave"></i> Jenis Pembayaran</label>
              <select id="filterJenisPembayaran" name="filter_jenis_pembayaran">
                <option value="">Semua Jenis</option>
                @foreach($availableJenisPembayaran ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_jenis_pembayaran') == $key ? 'selected' : '' }}>{{ $value }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="advanced-filter-actions">
            <button type="button" class="btn-advanced-reset" onclick="resetAdvancedFilters()">
              <i class="fas fa-times"></i>
              Reset Filter Lanjutan
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Table Section -->
    <div class="table-section">
      <div class="table-header">
        <div class="table-title">
          <i class="fas fa-list-alt"></i>
          Daftar Dokumen
          <span class="table-count">{{ $dokumens->total() }}</span>
        </div>
        <div class="table-toggle">
          <button type="button" class="table-toggle-btn" onclick="openColumnModal()">
            <i class="fas fa-columns"></i> Atur Kolom
          </button>
          <button type="button" class="table-toggle-btn {{ $mode != 'rekapan_table' ? 'active' : '' }}"
            onclick="setViewMode('normal')">
            <i class="fas fa-th-list"></i> Normal
          </button>
          <button type="button" class="table-toggle-btn {{ $mode == 'rekapan_table' ? 'active' : '' }}"
            onclick="setViewMode('rekapan_table')">
            <i class="fas fa-object-group"></i> Group Vendor
          </button>
        </div>
      </div>

      @if($dokumens->count() > 0)
        @if($mode == 'rekapan_table' && $rekapanByVendor)
          <!-- Vendor Grouped View -->
          <div class="vendor-groups">
            @foreach($rekapanByVendor as $vendorData)
              <div class="vendor-group">
                <div class="vendor-group-header" onclick="toggleVendorGroup(this)">
                  <div class="vendor-group-info">
                    <div class="vendor-icon">
                      <i class="fas fa-building"></i>
                    </div>
                    <span class="vendor-name">{{ $vendorData['vendor'] }}</span>
                    <span class="vendor-count">{{ $vendorData['count'] }} dokumen</span>
                  </div>
                  <div class="vendor-group-stats">
                    <div class="vendor-stat">
                      <div class="vendor-stat-label">Total Nilai</div>
                      <div class="vendor-stat-value">Rp {{ number_format($vendorData['total_nilai'], 0, ',', '.') }}</div>
                    </div>
                    <i class="fas fa-chevron-down vendor-chevron"></i>
                  </div>
                </div>
                <div class="vendor-group-body">
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
                          <td class="cell-primary cell-mono">{{ $doc->nomor_agenda }}</td>
                          <td class="cell-mono">{{ $doc->nomor_spp ?? '-' }}</td>
                          <td class="cell-uraian">{{ Str::limit($doc->uraian_spp, 50) }}</td>
                          <td class="cell-rupiah">{{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
                          <td>
                            @if($doc->computed_status == 'siap_dibayar')
                              <span class="status-pill status-pill--ready">
                                <i class="fas fa-circle"></i> Siap Dibayar
                              </span>
                            @elseif($doc->computed_status == 'sudah_dibayar')
                              <span class="status-pill status-pill--paid">
                                <i class="fas fa-circle"></i> Sudah Dibayar
                              </span>
                            @else
                              <span class="status-pill status-pill--pending">
                                <i class="fas fa-circle"></i> Belum Siap
                              </span>
                            @endif
                          </td>
                          <td>
                            <a href="{{ route('documents.pembayaran.detail', $doc->id) }}" class="btn-action"
                              title="Lihat Detail">
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
          <div class="data-table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  @foreach($selectedColumns as $colKey)
                    <th>{{ $availableColumns[$colKey] ?? Str::headline($colKey) }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($dokumens as $dok)
                  <tr>
                    @foreach($selectedColumns as $colKey)
                      @if($colKey == 'nomor_agenda')
                        <td class="cell-primary cell-mono">{{ $dok->nomor_agenda }}</td>
                      @elseif($colKey == 'nomor_spp')
                        <td class="cell-mono">{{ $dok->nomor_spp ?? '-' }}</td>
                      @elseif($colKey == 'dibayar_kepada')
                        <td class="cell-vendor">{{ Str::limit($dok->dibayar_kepada ?? '-', 30) }}</td>
                      @elseif($colKey == 'uraian_spp')
                        <td class="cell-uraian">{{ Str::limit($dok->uraian_spp ?? '-', 40) }}</td>
                      @elseif($colKey == 'nilai_rupiah')
                        <td class="cell-rupiah text-end">{{ number_format($dok->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
                      @elseif($colKey == 'status_pembayaran')
                        <td>
                          @if($dok->computed_status == 'siap_dibayar')
                            <span class="status-pill status-pill--ready">
                              <i class="fas fa-circle"></i> Siap Dibayar
                            </span>
                          @elseif($dok->computed_status == 'sudah_dibayar')
                            <span class="status-pill status-pill--paid">
                              <i class="fas fa-circle"></i> Sudah Dibayar
                            </span>
                          @else
                            <span class="status-pill status-pill--pending">
                              <i class="fas fa-circle"></i> Belum Siap
                            </span>
                          @endif
                        </td>
                      @elseif($colKey == 'tgl_jatuhtempo')
                        <td>{{ $dok->tgl_jatuhtempo ? \Carbon\Carbon::parse($dok->tgl_jatuhtempo)->format('d/m/Y') : '-' }}</td>
                      @elseif(in_array($colKey, ['dokumen_po', 'dokumen_pr', 'dokumen_gr']))
                        <td class="cell-mono">{{ $dok->$colKey ?? '-' }}</td>
                      @else
                        <td>{{ $dok->$colKey ?? '-' }}</td>
                      @endif
                    @endforeach
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
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }}
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
          <a href="{{ route('dashboard.pembayaran') }}" class="btn-empty">
            <i class="fas fa-redo"></i>
            Reset Filter
          </a>
        </div>
      @endif
    </div>
  </div>

  <!-- Column Customization Modal -->
  <style>
    .customization-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 9999;
      padding: 2rem;
      overflow-y: auto;
    }

    .customization-modal.show {
      display: flex;
      align-items: flex-start;
      justify-content: center;
    }

    .modal-content-custom {
      background: #fff;
      border-radius: 16px;
      width: 100%;
      max-width: 95%;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      margin: 1rem;
    }

    .modal-header-custom {
      padding: 1.5rem 2rem;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .modal-header-custom h3 {
      margin: 0;
      font-size: 1.25rem;
      font-weight: 600;
      color: #1f2937;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .modal-header-custom h3 i {
      color: #083E40;
    }

    .modal-close-btn {
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #6b7280;
      cursor: pointer;
      padding: 0.5rem;
      line-height: 1;
      transition: color 0.2s;
    }

    .modal-close-btn:hover {
      color: #1f2937;
    }

    .modal-body-custom {
      padding: 1.5rem 2rem;
      overflow-y: auto;
      flex: 1;
    }

    .customization-grid {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .selection-panel,
    .preview-panel {
      background: #f9fafb;
      border-radius: 12px;
      padding: 1.25rem;
    }

    .panel-title {
      font-weight: 600;
      font-size: 1rem;
      color: #1f2937;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .panel-title i {
      color: #083E40;
    }

    .panel-description {
      font-size: 0.875rem;
      color: #6b7280;
      margin-bottom: 1rem;
    }

    .column-selection-list {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0.5rem;
      max-height: 300px;
      overflow-y: auto;
      padding: 0.5rem;
    }

    @media (max-width: 1000px) {
      .column-selection-list {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (max-width: 768px) {
      .column-selection-list {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 500px) {
      .column-selection-list {
        grid-template-columns: 1fr;
      }
    }

    .column-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1rem;
      background: #fff;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      user-select: none;
    }

    .column-item:hover {
      border-color: #083E40;
      background: rgba(8, 62, 64, 0.02);
    }

    .column-item.selected {
      border-color: #22c55e;
      background: rgba(34, 197, 94, 0.08);
    }

    .column-item .drag-handle {
      color: #9ca3af;
      cursor: grab;
    }

    .column-item.selected .drag-handle {
      color: #083E40;
    }

    .column-item-checkbox {
      width: 18px;
      height: 18px;
      accent-color: #22c55e;
      cursor: pointer;
    }

    .column-item-label {
      flex: 1;
      font-size: 0.875rem;
      color: #374151;
      cursor: pointer;
    }

    .column-item-order {
      display: none;
      width: 24px;
      height: 24px;
      background: #22c55e;
      color: #fff;
      border-radius: 50%;
      font-size: 0.75rem;
      font-weight: 600;
      align-items: center;
      justify-content: center;
    }

    .column-item.selected .column-item-order {
      display: flex;
    }

    .preview-container {
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #e5e7eb;
      max-height: 300px;
      overflow-y: auto;
    }

    .preview-table {
      width: 100%;
      font-size: 0.8rem;
      border-collapse: collapse;
    }

    .preview-table th {
      background: #083E40;
      color: #fff;
      padding: 0.75rem 0.5rem;
      text-align: left;
      font-weight: 600;
      position: sticky;
      top: 0;
    }

    .preview-table td {
      padding: 0.5rem;
      border-bottom: 1px solid #e5e7eb;
      color: #374151;
    }

    .preview-table tr:hover td {
      background: #f9fafb;
    }

    .empty-preview {
      padding: 3rem 1.5rem;
      text-align: center;
      color: #9ca3af;
    }

    .empty-preview i {
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }

    .modal-footer-custom {
      padding: 1rem 2rem;
      border-top: 1px solid #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .selected-count {
      font-size: 0.875rem;
      color: #374151;
    }

    .selected-count strong {
      color: #22c55e;
      font-weight: 700;
    }

    .modal-actions {
      display: flex;
      gap: 0.75rem;
    }

    .btn-modal {
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.875rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      transition: all 0.2s;
      border: none;
    }

    .btn-cancel {
      background: #ef4444;
      color: #fff;
    }

    .btn-cancel:hover {
      background: #dc2626;
    }

    .btn-save {
      background: #22c55e;
      color: #fff;
    }

    .btn-save:hover {
      background: #16a34a;
    }

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    .panel-actions {
      display: flex;
      gap: 0.5rem;
    }

    .btn-select-action {
      padding: 0.375rem 0.75rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 500;
      border: 1px solid #e5e7eb;
      background: #fff;
      color: #374151;
      cursor: pointer;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
    }

    .btn-select-action:hover {
      border-color: #083E40;
      color: #083E40;
    }

    .btn-select-action.btn-select-all:hover {
      background: rgba(34, 197, 94, 0.1);
      border-color: #22c55e;
      color: #22c55e;
    }

    .btn-select-action.btn-remove-all:hover {
      background: rgba(239, 68, 68, 0.1);
      border-color: #ef4444;
      color: #ef4444;
    }
  </style>

  <div class="customization-modal" id="columnCustomizationModal">
    <div class="modal-content-custom">
      <div class="modal-header-custom">
        <h3>
          <i class="fa-solid fa-table-columns"></i>
          Kustomisasi Kolom Tabel
        </h3>
        <button type="button" class="modal-close-btn" onclick="closeColumnModal()">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>

      <div class="modal-body-custom">
        <div class="customization-grid">
          <!-- Selection Panel -->
          <div class="selection-panel">
            <div class="panel-header">
              <div class="panel-title">
                <i class="fa-solid fa-check-square"></i>
                Pilih Kolom
              </div>
              <div class="panel-actions">
                <button type="button" class="btn-select-action btn-select-all" onclick="selectAllColumns()">
                  <i class="fa-solid fa-check-double"></i> Pilih Semua
                </button>
                <button type="button" class="btn-select-action btn-remove-all" onclick="removeAllColumns()">
                  <i class="fa-solid fa-times"></i> Hapus Semua
                </button>
              </div>
            </div>
            <div class="panel-description">
              Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
            </div>
            <div class="column-selection-list" id="columnSelectionList">
              @foreach($availableColumns as $key => $label)
                <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}" data-column="{{ $key }}"
                  onclick="toggleColumnSelection(this)">
                  <div class="drag-handle">
                    <i class="fa-solid fa-grip-vertical"></i>
                  </div>
                  <input type="checkbox" class="column-item-checkbox" value="{{ $key }}" {{ in_array($key, $selectedColumns) ? 'checked' : '' }} onclick="event.stopPropagation()">
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
                      @for($i = 1; $i <= 4; $i++)
                        <tr>
                          <td>{{ $i }}</td>
                          @foreach($selectedColumns as $col)
                            <td>
                              @if($col == 'nomor_agenda')
                                AGD/10{{ $i }}/XII/2024
                              @elseif($col == 'nomor_spp')
                                SPP-0010{{ $i }}/XII/2024
                              @elseif($col == 'nilai_rupiah')
                                Rp. {{ number_format(1000000 * $i, 0, ',', '.') }}
                              @elseif($col == 'dibayar_kepada')
                                CV. Contoh Vendor {{ $i }}
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
                  <br><small>Kolom: {{ implode(', ', array_map(function ($col) use ($availableColumns) {
              return $availableColumns[$col] ?? $col;
            }, $selectedColumns)) }}</small>
          @endif
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-modal btn-cancel" onclick="closeColumnModal()">
            <i class="fa-solid fa-times"></i>
            Batal
          </button>
          <button type="button" class="btn-modal btn-save" onclick="saveColumnCustomization()">
            <i class="fa-solid fa-save"></i>
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Column customization variables
    let selectedColumnsOrder = @json($selectedColumns);
    const availableColumnsData = @json($availableColumns);

    function openColumnModal() {
      document.getElementById('columnCustomizationModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeColumnModal() {
      document.getElementById('columnCustomizationModal').classList.remove('show');
      document.body.style.overflow = '';
    }

    function toggleColumnSelection(element) {
      const checkbox = element.querySelector('.column-item-checkbox');
      const columnKey = element.dataset.column;

      checkbox.checked = !checkbox.checked;

      if (checkbox.checked) {
        element.classList.add('selected');
        if (!selectedColumnsOrder.includes(columnKey)) {
          selectedColumnsOrder.push(columnKey);
        }
      } else {
        element.classList.remove('selected');
        selectedColumnsOrder = selectedColumnsOrder.filter(c => c !== columnKey);
      }

      updateOrderNumbers();
      updateSelectedCount();
      updatePreview();
    }

    function updateOrderNumbers() {
      document.querySelectorAll('.column-item').forEach(item => {
        const key = item.dataset.column;
        const orderSpan = item.querySelector('.column-item-order');
        const idx = selectedColumnsOrder.indexOf(key);
        if (idx !== -1) {
          orderSpan.textContent = idx + 1;
        } else {
          orderSpan.textContent = '';
        }
      });
    }

    function updateSelectedCount() {
      document.getElementById('selectedColumnCount').textContent = selectedColumnsOrder.length;
      // Update column list text
      const columnLabels = selectedColumnsOrder.map(col => availableColumnsData[col] || col);
      const countContainer = document.querySelector('.selected-count');
      if (countContainer) {
        countContainer.innerHTML = `<strong id="selectedColumnCount">${selectedColumnsOrder.length}</strong> kolom dipilih` +
          (selectedColumnsOrder.length > 0 ? `<br><small>Kolom: ${columnLabels.join(', ')}</small>` : '');
      }
    }

    function updatePreview() {
      const previewContainer = document.getElementById('tablePreview');
      if (!previewContainer) return;

      if (selectedColumnsOrder.length === 0) {
        previewContainer.innerHTML = `
                  <div class="empty-preview">
                    <i class="fa-solid fa-table"></i>
                    <p>Belum ada kolom yang dipilih</p>
                    <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
                  </div>`;
        return;
      }

      let tableHtml = '<table class="preview-table"><thead><tr><th>No</th>';
      selectedColumnsOrder.forEach(col => {
        tableHtml += `<th>${availableColumnsData[col] || col}</th>`;
      });
      tableHtml += '<th>Aksi</th></tr></thead><tbody>';

      for (let i = 1; i <= 4; i++) {
        tableHtml += `<tr><td>${i}</td>`;
        selectedColumnsOrder.forEach(col => {
          let cellValue = `Contoh Data ${i}`;
          if (col === 'nomor_agenda') cellValue = `AGD/10${i}/XII/2024`;
          else if (col === 'nomor_spp') cellValue = `SPP-0010${i}/XII/2024`;
          else if (col === 'nilai_rupiah') cellValue = `Rp. ${(1000000 * i).toLocaleString('id-ID')}`;
          else if (col === 'dibayar_kepada') cellValue = `CV. Contoh Vendor ${i}`;
          else if (col === 'status_pembayaran') cellValue = i % 2 === 0 ? 'Siap Bayar' : 'Belum Siap';
          tableHtml += `<td>${cellValue}</td>`;
        });
        tableHtml += '<td>Edit, Kirim</td></tr>';
      }

      tableHtml += '</tbody></table>';
      previewContainer.innerHTML = tableHtml;
    }

    function selectAllColumns() {
      selectedColumnsOrder = Object.keys(availableColumnsData);
      document.querySelectorAll('.column-item').forEach(item => {
        item.classList.add('selected');
        item.querySelector('.column-item-checkbox').checked = true;
      });
      updateOrderNumbers();
      updateSelectedCount();
      updatePreview();
    }

    function removeAllColumns() {
      selectedColumnsOrder = [];
      document.querySelectorAll('.column-item').forEach(item => {
        item.classList.remove('selected');
        item.querySelector('.column-item-checkbox').checked = false;
      });
      updateOrderNumbers();
      updateSelectedCount();
      updatePreview();
    }

    function saveColumnCustomization() {
      if (selectedColumnsOrder.length === 0) {
        alert('Pilih minimal satu kolom!');
        return;
      }

      const url = new URL(window.location.href);
      // Clear existing columns params
      url.searchParams.delete('columns[]');
      url.searchParams.delete('columns');

      // Add selected columns
      selectedColumnsOrder.forEach(col => {
        url.searchParams.append('columns[]', col);
      });

      window.location.href = url.toString();
    }

    // Close modal on escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeColumnModal();
      }
    });

    // Close modal on backdrop click
    document.getElementById('columnCustomizationModal')?.addEventListener('click', function (e) {
      if (e.target === this) {
        closeColumnModal();
      }
    });
  </script>

  <script>
    // View Mode Toggle
    function setViewMode(mode) {
      const url = new URL(window.location.href);
      url.searchParams.set('mode', mode);
      window.location.href = url.toString();
    }

    // Vendor Group Toggle
    function toggleVendorGroup(header) {
      const body = header.nextElementSibling;
      const isExpanded = header.classList.contains('expanded');

      if (isExpanded) {
        header.classList.remove('expanded');
        body.classList.remove('show');
      } else {
        header.classList.add('expanded');
        body.classList.add('show');
      }
    }

    // Number Counter Animation
    document.addEventListener('DOMContentLoaded', function () {
      const counters = document.querySelectorAll('.stat-value');

      counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
        if (isNaN(target) || target === 0) return;

        let current = 0;
        const increment = target / 30;
        const duration = 800;
        const stepTime = duration / 30;

        const updateCounter = () => {
          current += increment;
          if (current < target) {
            counter.textContent = Math.floor(current).toLocaleString('id-ID');
            setTimeout(updateCounter, stepTime);
          } else {
            counter.textContent = target.toLocaleString('id-ID');
          }
        };

        setTimeout(updateCounter, 300);
      });
    });

    // Add subtle hover feedback
    document.querySelectorAll('.stat-card, .deadline-card').forEach(card => {
      card.addEventListener('mouseenter', function () {
        this.style.transform = 'translateY(-4px)';
      });
      card.addEventListener('mouseleave', function () {
        this.style.transform = 'translateY(0)';
      });
    });

    // Advanced Filter Panel Toggle
    document.getElementById('advancedFilterToggle')?.addEventListener('click', function () {
      const panel = document.getElementById('advancedFilterPanel');
      const toggle = this;

      panel.classList.toggle('show');
      toggle.classList.toggle('active');
    });

    // Reset Advanced Filters
    function resetAdvancedFilters() {
      document.getElementById('filterVendor').value = '';
      document.getElementById('filterKategori').value = '';
      document.getElementById('filterJenisDokumen').value = '';
      document.getElementById('filterJenisSubPekerjaan').value = '';
      document.getElementById('filterKebun').value = '';
      document.getElementById('filterJenisPembayaran').value = '';
    }

    // Export Document Function
    function exportDocument(format) {
      const form = document.getElementById('exportForm');
      const formatInput = document.getElementById('exportFormat');
      const columnsContainer = document.getElementById('exportColumnsContainer');

      // Set format
      formatInput.value = format;

      // Clear previous columns
      columnsContainer.innerHTML = '';

      // Get selected columns from the page or use defaults
      // Check if there are checkboxes in column modal that are checked
      const columnCheckboxes = document.querySelectorAll('.column-item input[type="checkbox"]:checked');

      if (columnCheckboxes.length > 0) {
        // Use selected columns from modal
        columnCheckboxes.forEach(function (checkbox) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'columns[]';
          input.value = checkbox.value;
          columnsContainer.appendChild(input);
        });
      } else {
        // Use default columns based on what's visible in table
        const defaultColumns = ['nomor_agenda', 'dibayar_kepada', 'jenis_pembayaran', 'nomor_spp', 'uraian_spp', 'nilai_rupiah'];
        defaultColumns.forEach(function (col) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'columns[]';
          input.value = col;
          columnsContainer.appendChild(input);
        });
      }

      // Submit form
      form.submit();
    }
  </script>
@endsection
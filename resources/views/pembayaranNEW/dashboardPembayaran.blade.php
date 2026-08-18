@extends('layouts.app')

@section('content')
  {{-- Tabel pembayaran memakai tabel biasa + CSS sticky (bukan DataTables) --}}

  <style>
    /* ===== PREMIUM SAAS DESIGN SYSTEM ===== */
    :root {
      /* Core Colors */
      --bg-primary: #f5f7fb;
      --bg-secondary: #f8fafc;
      --bg-tertiary: #ffffff;

      /* Text Colors */
      --text-primary: #0f172a;
      --text-secondary: #475569;
      --text-tertiary: #94a3b8;
      --text-muted: #cbd5e1;

      /* Brand Colors - Soft Pastels */
      --brand-primary: #0f766e;
      --brand-primary-soft: rgba(15, 118, 110, 0.1);
      --brand-primary-glow: rgba(15, 118, 110, 0.18);

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
      --radius-lg: 14px;
      --radius-xl: 20px;
      --radius-2xl: 24px;

      /* Transitions */
      --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
      --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
      --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ===== BASE STYLES ===== */
    .premium-dashboard {
      font-family: 'Plus Jakarta Sans', 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--bg-primary);
      min-height: 100vh;
      padding: 1.75rem 2rem 2.25rem;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* ===== HEADER SECTION ===== */
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1.5rem;
    }

    .header-content {
      flex: 1;
    }

    .header-title {
      font-size: 1.45rem;
      font-weight: 700;
      color: var(--text-primary);
      letter-spacing: 0;
      margin: 0 0 0.5rem 0;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .header-title-icon {
      width: 38px;
      height: 38px;
      background: #0f766e;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.125rem;
    }

    .header-subtitle {
      font-size: 0.875rem;
      color: #94a3b8;
      font-weight: 400;
      margin: 0;
    }

    .header-actions {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .btn-export-excel {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: var(--radius-md);
      border: 1px solid #16a34a;
      background: #16a34a;
      color: white;
      cursor: pointer;
      transition: var(--transition-base);
      text-decoration: none;
      font-size: 0.8125rem;
      font-weight: 600;
      font-family: inherit;
    }

    .btn-export-excel:hover {
      background: #15803d;
      border-color: #15803d;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .btn-export-excel i {
      font-size: 1rem;
    }

    /* ===== BENTO GRID ===== */
    .bento-grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 1rem;
      margin-bottom: 2rem;
    }

    /* Main Stats Row */
    .stat-card {
      background: var(--bg-tertiary);
      border-radius: 14px;
      padding: 1.25rem 1.35rem;
      border: 1px solid #e8ecf4;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
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
      min-height: 116px;
    }

    .stat-card:hover {
      box-shadow: 0 16px 34px rgba(15, 23, 42, 0.10);
      transform: translateY(-2px);
      border-color: #dbe3ef;
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
      background: linear-gradient(135deg, #0f766e 0%, #079669 100%);
      border: none;
      box-shadow: 0 12px 28px rgba(15, 118, 110, 0.22);
    }

    .stat-card--total:hover {
      box-shadow: 0 16px 34px rgba(15, 118, 110, 0.28);
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
      width: 46px;
      height: 46px;
      border-radius: 12px;
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
      font-weight: 700;
      color: #9aa8c0;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 0.375rem;
    }

    .stat-value {
      font-size: 1.75rem;
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
      color: #94a3b8;
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
      border-radius: 14px;
      padding: 1.25rem 1.5rem;
      border: 1px solid #e8ecf4;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
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
      box-shadow: 0 16px 34px rgba(15, 23, 42, 0.10);
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
      width: 46px;
      height: 46px;
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
      font-weight: 600;
      color: #94a3b8;
      margin-bottom: 0.25rem;
    }

    .deadline-value {
      font-size: 1.625rem;
      font-weight: 700;
      color: var(--text-primary);
    }

    .deadline-desc {
      font-size: 0.75rem;
      color: #94a3b8;
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

    /* Tabulator (Rollout 4): #filterForm dipakai ulang sebagai .tabulator-toolbar
       agar public/js/document-tabulator.js membaca nama filter langsung dari DOM
       (CLAUDE.md §7 — toolbarFilterControls() generik lintas-role). Aturan global
       .tabulator-toolbar (public/css/tabulator-agenda.css) men-set display:flex;
       form ini punya anak blok (.filter-row) dan modal popup filter lanjutan yang
       harus tetap bertumpuk vertikal — netralkan di sini (spesifisitas ID menang
       tanpa !important). */
    #filterForm.tabulator-toolbar {
      display: block;
    }

    .filter-search {
      flex: 1;
      min-width: 280px;
      position: relative;
    }

    .filter-search input {
      width: 100%;
      height: 44px;
      padding: 0 1rem 0 2.75rem;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-md);
      font-size: 0.875rem;
      font-family: inherit;
      /* Ikon kaca pembesar tunggal via background — hindari duplikasi ikon FontAwesome */
      background-color: var(--bg-secondary);
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M21 21l-4.35-4.35'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: left 1rem center;
      background-size: 16px 16px;
      color: var(--text-primary);
      transition: var(--transition-fast);
    }

    /* Fix "ikon search dobel": partial global compact-document-ui memasang
       `.filter-section input { padding: .38rem .68rem !important }` yang memangkas
       padding-kiri, sehingga placeholder "Cari…" menindih ikon kaca pembesar
       background (tampak seolah 2 ikon). Selektor ini (specificity 0,2,1 + !important)
       mengembalikan ruang ikon; scoped pembayaran, tak menyentuh partial global. */
    .filter-section .filter-search input {
      padding-left: 2.75rem !important;
    }

    .filter-search input:focus {
      outline: none;
      border-color: var(--brand-primary);
      box-shadow: 0 0 0 3px var(--brand-primary-glow);
      background-color: var(--bg-tertiary);
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
      flex-wrap: wrap;
    }

    .filter-select,
    .filter-date {
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

    .filter-date {
      min-width: 150px;
      padding-right: 1rem;
      background: var(--bg-secondary);
      appearance: auto;
    }

    .filter-select:focus,
    .filter-date:focus {
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
      position: relative;
      background:
        linear-gradient(180deg, rgba(8, 62, 64, 0.035) 0%, rgba(255, 255, 255, 0) 120px),
        var(--bg-tertiary);
      border-radius: var(--radius-xl);
      border: 1px solid rgba(148, 163, 184, 0.22);
      box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
      overflow: hidden;
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      padding: 1.25rem 1.5rem 1rem;
      border-bottom: 1px solid rgba(226, 232, 240, 0.86);
      background: rgba(255, 255, 255, 0.72);
      backdrop-filter: blur(10px);
    }

    .table-heading {
      display: flex;
      align-items: center;
      gap: 1rem;
      min-width: 0;
    }

    .table-title-stack {
      display: flex;
      flex-direction: column;
      gap: 0.375rem;
    }

    .table-title {
      font-size: 1.0625rem;
      font-weight: 700;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .table-title i {
      color: var(--brand-primary);
    }

    .table-subtitle {
      font-size: 0.8125rem;
      color: var(--text-tertiary);
      line-height: 1.35;
    }

    .table-count {
      font-size: 0.8125rem;
      font-weight: 700;
      color: var(--brand-primary);
      background: var(--brand-primary-soft);
      padding: 0.25rem 0.75rem;
      border-radius: 999px;
      margin-left: 0.5rem;
    }

    .table-controls {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 0.875rem;
      flex-wrap: wrap;
    }

    .per-page-selector {
      display: inline-flex;
      align-items: center;
      gap: 0.625rem;
      padding: 0.375rem 0.5rem 0.375rem 0.75rem;
      background: var(--bg-secondary);
      border: 1px solid rgba(226, 232, 240, 0.95);
      border-radius: var(--radius-md);
    }

    .per-page-selector label {
      font-size: 0.8125rem;
      color: var(--text-secondary);
      white-space: nowrap;
    }

    .per-page-selector select {
      height: 34px;
      min-width: 74px;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-sm);
      padding: 0 2rem 0 0.75rem;
      background-color: var(--bg-tertiary);
      color: var(--text-primary);
      font: inherit;
      font-size: 0.8125rem;
      outline: none;
      cursor: pointer;
    }

    /* Data Table */
    .data-table-wrapper {
      position: relative;
      overflow: hidden;
      min-height: 360px;
      scrollbar-color: rgba(8, 62, 64, 0.36) rgba(241, 245, 249, 0.9);
      scrollbar-width: thin;
    }

    .dataTables_wrapper,
    .dt-container {
      width: 100%;
    }

    .dt-container .dt-info,
    .dt-container .dt-paging,
    .dt-container .dt-length,
    .dt-container .dt-search {
      display: none !important;
    }

    .dt-scroll-body,
    .dataTables_scrollBody {
      min-height: 420px;
      max-height: 62vh !important;
      scrollbar-color: rgba(8, 62, 64, 0.36) rgba(241, 245, 249, 0.9);
      scrollbar-width: thin;
    }

    .dt-processing {
      border: none !important;
      border-radius: var(--radius-lg) !important;
      box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12) !important;
      color: var(--brand-primary) !important;
      font-weight: 700;
    }

    .data-table-wrapper::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }

    .data-table-wrapper::-webkit-scrollbar-track {
      background: rgba(241, 245, 249, 0.9);
    }

    .data-table-wrapper::-webkit-scrollbar-thumb {
      background: rgba(8, 62, 64, 0.32);
      border-radius: 999px;
      border: 2px solid rgba(241, 245, 249, 0.9);
    }

    .data-table-wrapper::-webkit-scrollbar-thumb:hover {
      background: rgba(8, 62, 64, 0.52);
    }

    .data-table {
      width: max-content;
      min-width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      table-layout: fixed;
    }

    .data-table th {
      position: sticky;
      top: 0;
      z-index: 3;
      min-width: 132px;
      padding: 0.95rem 1rem;
      text-align: left;
      font-size: 0.75rem;
      font-weight: 750;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      background: #f8fafc;
      border-bottom: 1px solid rgba(226, 232, 240, 0.95);
      white-space: nowrap;
      box-shadow: 0 1px 0 rgba(226, 232, 240, 0.7);
    }

    .data-table th:first-child {
      padding-left: 1.5rem;
      left: 0;
      z-index: 4;
      min-width: 160px;
    }

    .data-table th:last-child {
      padding-right: 1.5rem;
    }

    .data-table td {
      min-width: 132px;
      padding: 1rem;
      font-size: 0.875rem;
      color: var(--text-secondary);
      border-bottom: 1px solid rgba(226, 232, 240, 0.78);
      vertical-align: middle;
      line-height: 1.45;
      background: rgba(255, 255, 255, 0.96);
    }

    .data-table td:first-child {
      padding-left: 1.5rem;
      position: sticky;
      left: 0;
      z-index: 2;
      min-width: 160px;
      background: #fff;
      box-shadow: 1px 0 0 rgba(226, 232, 240, 0.9);
    }

    .data-table td:last-child {
      padding-right: 1.5rem;
    }

    .data-table tbody tr {
      position: relative;
      transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .data-table tbody tr:nth-child(even) td {
      background: rgba(248, 250, 252, 0.72);
    }

    .data-table tbody tr:hover td {
      background: #f0fdfa;
      box-shadow: inset 0 1px 0 rgba(20, 184, 166, 0.08), inset 0 -1px 0 rgba(20, 184, 166, 0.08);
    }

    .data-table tbody tr:hover td:first-child {
      box-shadow: inset 4px 0 0 var(--brand-primary), 1px 0 0 rgba(20, 184, 166, 0.18);
    }

    .data-table tbody tr:last-child td {
      border-bottom: none;
    }

    /* Cell Styles */
    .cell-primary {
      font-weight: 800;
      color: var(--text-primary);
    }

    .cell-mono {
      font-family: 'JetBrains Mono', 'Fira Code', monospace;
      font-size: 0.8125rem;
    }

    .cell-rupiah {
      font-weight: 800;
      color: #0f766e;
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
    }

    .cell-vendor {
      min-width: 210px;
      max-width: 240px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      color: var(--text-primary);
      font-weight: 600;
    }

    .cell-uraian {
      min-width: 250px;
      max-width: 310px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      color: #475569;
    }

    /* Status Pills */
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.45rem 0.8rem;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 750;
      white-space: nowrap;
      box-shadow: inset 0 0 0 1px currentColor;
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

      .filter-select,
      .filter-date {
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

      .table-header {
        flex-direction: column;
        align-items: stretch;
      }

      .table-controls {
        justify-content: stretch;
      }

      .per-page-selector {
        width: 100%;
      }

      .data-table-wrapper {
        max-height: none;
        min-height: 320px;
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

  <style>
    #documentTableContainer.table-dokumen {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
      border: 1px solid #f1f5f9;
      overflow: hidden;
    }

    #documentTableContainer .dtable-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 1.35rem;
      border-bottom: 1px solid #f1f5f9;
      gap: 1rem;
      flex-wrap: wrap;
      background: #ffffff;
    }

    #documentTableContainer .dtable-toolbar-left,
    #documentTableContainer .dtable-toolbar-right {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      flex-wrap: wrap;
    }

    #documentTableContainer .dtable-toolbar-icon {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, #083E40 0%, #0a5254 100%);
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 0.875rem;
      flex-shrink: 0;
    }

    #documentTableContainer .dtable-toolbar-title {
      font-size: 1rem;
      font-weight: 700;
      color: #0f172a;
    }

    #documentTableContainer .dtable-toolbar-subtitle {
      font-size: 0.75rem;
      color: #94a3b8;
      font-weight: 500;
    }

    #documentTableContainer .btn-customize-columns-inline,
    #documentTableContainer .btn-refresh,
    #documentTableContainer .btn-fullscreen {
      min-height: 44px;
      border-radius: 8px;
      border: none;
      color: #ffffff;
      font-size: 14px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 16px;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.16);
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      white-space: nowrap;
      cursor: pointer;
    }

    #documentTableContainer .btn-customize-columns-inline {
      background: linear-gradient(135deg, #889717 0%, #9cab15 100%);
    }

    #documentTableContainer .btn-refresh {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    #documentTableContainer .btn-fullscreen {
      background: linear-gradient(135deg, #083E40 0%, #052f31 100%);
    }

    #documentTableContainer .btn-customize-columns-inline:hover,
    #documentTableContainer .btn-refresh:hover,
    #documentTableContainer .btn-fullscreen:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.24);
    }

    #documentTableContainer .data-table-wrapper {
      border-radius: 0;
      overflow: auto;
      background: #ffffff;
      min-height: 420px;
      scrollbar-color: rgba(8, 62, 64, 0.36) rgba(241, 245, 249, 0.9);
      scrollbar-width: thin;
    }

    #documentTableContainer .data-table {
      width: max-content;
      min-width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      table-layout: fixed;
    }

    #documentTableContainer .data-table th,
    #documentTableContainer .dt-scroll-head th {
      background: #0d3b6e !important;
      color: rgba(255,255,255,0.95) !important;
      box-shadow: 0 2px 0 #1a5276 !important;
      border-right: 1px solid rgba(255, 255, 255, 0.18) !important;
      border-left: 1px solid rgba(255, 255, 255, 0.08) !important;
      padding: 0.85rem 0.9rem !important;
      font-size: 0.775rem !important;
      font-weight: 700 !important;
      letter-spacing: 0.04em !important;
      white-space: nowrap;
    }

    #documentTableContainer .data-table th:first-child,
    #documentTableContainer .dt-scroll-head th:first-child {
      border-left: none !important;
    }

    #documentTableContainer .data-table th:last-child,
    #documentTableContainer .dt-scroll-head th:last-child {
      border-right: none !important;
    }

    #documentTableContainer .data-table td {
      border-top: 1px solid #f1f5f9 !important;
      border-bottom: none !important;
      border-right: 1px solid #d9e0e7 !important;
      padding: 0.85rem 0.9rem !important;
      color: #1f2937;
      font-size: 0.875rem;
      line-height: 1.45;
      white-space: normal;
      overflow-wrap: anywhere;
      word-break: break-word;
      background: #ffffff;
    }

    #documentTableContainer .data-table tbody tr:nth-child(even) td {
      background: #f8fafc;
    }

    #documentTableContainer .data-table tbody tr:hover td {
      background: #f3faf9 !important;
    }

    #documentTableContainer .data-table tbody tr:hover td.col-no {
      box-shadow: inset 3px 0 0 #083E40;
    }

    #documentTableContainer .data-table .col-no,
    #documentTableContainer .dt-scroll-head .col-no {
      width: 88px !important;
      min-width: 88px !important;
      max-width: 88px !important;
      text-align: center;
      font-weight: 700;
    }

    #documentTableContainer .data-table .col-nomor_agenda,
    #documentTableContainer .dt-scroll-head .col-nomor_agenda {
      min-width: 210px !important;
      width: 210px !important;
    }

    /* Kolom beku NO & NOMOR AGENDA (kolom + header) ditangani oleh fitur
       frozen native Tabulator (CFG.frozen, lihat 'frozen' => [...] di bawah) —
       bukan lagi partial sticky-cell lama. */

    #documentTableContainer .data-table tbody tr:nth-child(odd) td.col-no {
      background: #ffffff !important;
    }

    #documentTableContainer .data-table tbody tr:nth-child(even) td.col-no {
      background: #f8fafc !important;
    }

    #documentTableContainer .data-table tbody tr:hover td.col-no {
      background: #f3faf9 !important;
    }

    #documentTableContainer .data-table tbody tr:nth-child(odd) td.col-nomor_agenda {
      background: #ffffff !important;
    }

    #documentTableContainer .data-table tbody tr:nth-child(even) td.col-nomor_agenda {
      background: #f8fafc !important;
    }

    #documentTableContainer .data-table tbody tr:hover td.col-nomor_agenda {
      background: #f3faf9 !important;
    }

    #documentTableContainer .status-pill {
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 800;
      padding: 0.45rem 0.8rem;
      white-space: nowrap;
    }

    body.document-table-only-fullscreen #documentTableContainer,
    body.is-fullscreen #documentTableContainer {
      position: fixed !important;
      inset: 0 !important;
      z-index: 99990 !important;
      border-radius: 0 !important;
      height: 100vh !important;
      width: 100vw !important;
    }

    body.document-table-only-fullscreen #documentTableContainer .data-table-wrapper,
    body.is-fullscreen #documentTableContainer .data-table-wrapper {
      max-height: calc(100vh - 80px) !important;
      min-height: calc(100vh - 80px) !important;
    }

    body.document-table-only-fullscreen .premium-dashboard > *:not(#documentTableContainer),
    body.is-fullscreen .premium-dashboard > *:not(#documentTableContainer) {
      visibility: hidden;
    }

    /* Advanced Filter Modal Overlay — mengikuti pola modal kustomisasi kolom */
    .advanced-filter-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 9999;
      overflow-y: auto;
      padding: 20px;
      box-sizing: border-box;
    }
    .advanced-filter-modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
      animation: afFadeIn 0.2s ease;
    }
    @keyframes afFadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .afm-content {
      background: white;
      border-radius: 16px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
      width: 100%;
      max-width: 750px;
      max-height: 85vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      animation: afSlideIn 0.25s ease;
    }
    @keyframes afSlideIn {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .afm-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
      flex-shrink: 0;
    }
    .afm-header h5 {
      margin: 0;
      font-size: 1rem;
      font-weight: 700;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .afm-close {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      background: transparent;
      color: #64748b;
      font-size: 1.25rem;
      cursor: pointer;
      border-radius: 8px;
      transition: all 0.15s;
    }
    .afm-close:hover {
      background: #e2e8f0;
      color: #0f172a;
    }
    .afm-body {
      padding: 1.25rem 1.5rem;
      overflow-y: auto;
      flex: 1;
    }
    .afm-footer {
      display: flex;
      justify-content: flex-end;
      padding: 1rem 1.5rem;
      border-top: 1px solid #e2e8f0;
      background: #f8fafc;
      flex-shrink: 0;
    }

    @media (max-width: 768px) {
      #documentTableContainer .dtable-toolbar {
        align-items: flex-start;
      }

      #documentTableContainer .dtable-toolbar-left,
      #documentTableContainer .dtable-toolbar-right {
        width: 100%;
      }
    }
  </style>

  <style>
    /* Halaman dokumen pembayaran: fokus tabel + tools (selaras role lain).
       Kartu statistik & deadline adalah konten dashboard, kini disembunyikan di sini. */
    .premium-dashboard .bento-grid { display: none !important; }
  </style>
  <div class="premium-dashboard">
    <!-- Bento Grid Stats -->
    <div class="bento-grid">
      <!-- Main Stats Row -->
      <a href="?status_pembayaran=&date={{ $selectedDate ?? '' }}"
        class="stat-card stat-card--total animate-fade-in animate-delay-1 {{ !$selectedStatus ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Total Dokumen</div>
          <div class="stat-value count-animate">{{ number_format($statistics['total_documents']) }}</div>
          <div class="stat-subvalue">Rp {{ number_format($statistics['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon stat-icon--total">
          <i class="fa-solid fa-layer-group"></i>
        </div>
      </a>

      <a href="?status_pembayaran=belum_siap_dibayar&date={{ $selectedDate ?? '' }}"
        class="stat-card stat-card--pending animate-fade-in animate-delay-2 {{ $selectedStatus == 'belum_siap_dibayar' ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Belum Siap Bayar</div>
          <div class="stat-value count-animate">{{ number_format($statistics['by_status']['belum_dibayar']) }}</div>
          <div class="stat-subvalue">Rp
            {{ number_format($statistics['total_nilai_by_status']['belum_dibayar'], 0, ',', '.') }}
          </div>
          <div class="stat-subvalue-link">
            <i class="fa-solid fa-arrow-right"></i> Klik untuk detail analitik
          </div>
        </div>
        <div class="stat-icon stat-icon--pending">
          <i class="fa-solid fa-hourglass-half"></i>
        </div>
      </a>

      <a href="?status_pembayaran=siap_dibayar&date={{ $selectedDate ?? '' }}"
        class="stat-card stat-card--ready animate-fade-in animate-delay-3 {{ $selectedStatus == 'siap_dibayar' ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Siap Dibayar</div>
          <div class="stat-value count-animate">{{ number_format($statistics['by_status']['siap_dibayar']) }}</div>
          <div class="stat-subvalue">Rp
            {{ number_format($statistics['total_nilai_by_status']['siap_dibayar'], 0, ',', '.') }}
          </div>
          <div class="stat-subvalue-link">
            <i class="fa-solid fa-arrow-right"></i> Klik untuk detail analitik
          </div>
        </div>
        <div class="stat-icon stat-icon--ready">
          <i class="fa-solid fa-check-circle"></i>
        </div>
      </a>

      <a href="?status_pembayaran=sudah_dibayar&date={{ $selectedDate ?? '' }}"
        class="stat-card stat-card--paid animate-fade-in animate-delay-4 {{ $selectedStatus == 'sudah_dibayar' ? 'active' : '' }}">
        <div class="stat-card-content">
          <div class="stat-label">Sudah Dibayar</div>
          <div class="stat-value count-animate">{{ number_format($statistics['by_status']['sudah_dibayar']) }}</div>
          <div class="stat-subvalue">Rp
            {{ number_format($statistics['total_nilai_by_status']['sudah_dibayar'], 0, ',', '.') }}
          </div>
          <div class="stat-subvalue-link">
            <i class="fa-solid fa-arrow-right"></i> Klik untuk detail analitik
          </div>
        </div>
        <div class="stat-icon stat-icon--paid">
          <i class="fa-solid fa-check-double"></i>
        </div>
      </a>

      <!-- Deadline Cards -->
      <a href="{{ route('dashboard.pembayaran', ['status_keterlambatan' => 'aman']) }}"
        class="deadline-card deadline-card--aman animate-fade-in animate-delay-5">
        <div class="deadline-icon">
          <i class="fa-solid fa-shield-alt"></i>
        </div>
        <div class="deadline-content">
          <div class="deadline-label">Aman</div>
          <div class="deadline-value">{{ number_format($totalAman) }}</div>
          <div class="deadline-desc">&lt; 1 Minggu</div>
        </div>
      </a>

      <a href="{{ route('dashboard.pembayaran', ['status_keterlambatan' => 'peringatan']) }}"
        class="deadline-card deadline-card--peringatan animate-fade-in animate-delay-6">
        <div class="deadline-icon">
          <i class="fa-solid fa-exclamation-triangle"></i>
        </div>
        <div class="deadline-content">
          <div class="deadline-label">Peringatan</div>
          <div class="deadline-value">{{ number_format($totalPeringatan) }}</div>
          <div class="deadline-desc">1 - 3 Minggu</div>
        </div>
      </a>

      <a href="{{ route('dashboard.pembayaran', ['status_keterlambatan' => 'terlambat']) }}"
        class="deadline-card deadline-card--terlambat animate-fade-in animate-delay-7">
        <div class="deadline-icon">
          <i class="fa-solid fa-times-circle"></i>
        </div>
        <div class="deadline-content">
          <div class="deadline-label">Terlambat</div>
          <div class="deadline-value">{{ number_format($totalTerlambat) }}</div>
          <div class="deadline-desc">&gt; 3 Minggu</div>
        </div>
      </a>
    </div>

    <!-- Filter Section -->
    <form action="{{ route('documents.pembayaran.index') }}" method="GET" class="filter-section tabulator-toolbar" id="filterForm">
      <div class="filter-row">
        <div class="filter-search">
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

          <input type="date" name="date" class="filter-date" value="{{ $selectedDate ?? '' }}"
            data-placeholder="Pilih tanggal masuk (dd/mm/yyyy)"
            aria-label="Filter tanggal masuk" title="Filter tanggal masuk">
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn-filter btn-filter--primary">
            <i class="fa-solid fa-check"></i>
            Terapkan
          </button>
          {{-- Reset membersihkan filter saja; susunan kolom pilihan user sengaja
               dipertahankan (dulu ikut terhapus dari localStorage). --}}
          <a href="{{ route('documents.pembayaran.index') }}" class="btn-filter btn-filter--secondary">
            <i class="fa-solid fa-redo"></i>
            Reset
          </a>

          @php
            $activeAdvancedFilterCount = 0;
            if (request('month') || request('filter_bulan'))
              $activeAdvancedFilterCount++;
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
            if (request('filter_jenis_pembayaran'))
              $activeAdvancedFilterCount++;
            if (request('filter_bagian'))
              $activeAdvancedFilterCount++;
          @endphp

          <button type="button" class="advanced-filter-toggle"
            id="advancedFilterToggle" onclick="openAdvancedFilterModal()">
            <i class="fa-solid fa-sliders-h"></i>
            Filter Lanjutan
            @if($activeAdvancedFilterCount > 0)
              <span class="active-filters-badge">{{ $activeAdvancedFilterCount }}</span>
            @endif
          </button>

        </div>
      </div>

      <!-- Advanced Filter Modal -->
      <div class="advanced-filter-modal" id="advancedFilterModal">
        <div class="afm-content">
          <div class="afm-header">
            <h5><i class="fa-solid fa-sliders-h"></i> Filter Lanjutan</h5>
            <button type="button" class="afm-close" onclick="closeAdvancedFilterModal()" aria-label="Tutup">
              <i class="fa-solid fa-times"></i>
            </button>
          </div>
          <div class="afm-body">
            <div class="advanced-filter-grid">
              <!-- Bulan Filter -->
              <div class="filter-group">
                <label for="filterBulan"><i class="fa-solid fa-calendar-alt"></i> Bulan</label>
                <select id="filterBulan" name="month">
                  <option value="">Semua Bulan</option>
                  @php
                    $bulanOptions = [
                      1 => 'Januari',
                      2 => 'Februari',
                      3 => 'Maret',
                      4 => 'April',
                      5 => 'Mei',
                      6 => 'Juni',
                      7 => 'Juli',
                      8 => 'Agustus',
                      9 => 'September',
                      10 => 'Oktober',
                      11 => 'November',
                      12 => 'Desember'
                    ];
                    $selectedBulan = request('month', request('filter_bulan', $selectedMonth ?? ''));
                  @endphp
                  @foreach($bulanOptions as $num => $namaBulan)
                    <option value="{{ $num }}" {{ (string)$selectedBulan === (string)$num || strtolower((string)$selectedBulan) === strtolower($namaBulan) ? 'selected' : '' }}>
                      {{ $namaBulan }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Bagian Filter -->
              <div class="filter-group">
                <label for="filterBagian"><i class="fa-solid fa-building"></i> Bagian</label>
                <select id="filterBagian" name="filter_bagian">
                  <option value="">Semua Bagian</option>
                  @foreach($availableBagians ?? [] as $key => $value)
                    <option value="{{ $key }}" {{ request('filter_bagian') == $key ? 'selected' : '' }}>{{ $value }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Vendor Filter -->
              <div class="filter-group">
                <label for="filterVendor"><i class="fa-solid fa-store"></i> Vendor</label>
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
                <label for="filterKategori"><i class="fa-solid fa-tags"></i> Kriteria CF</label>
                <select id="filterKategori" name="filter_kategori">
                  <option value="">Semua Kriteria</option>
                  @foreach($availableKategori ?? [] as $key => $value)
                    <option value="{{ $key }}" {{ request('filter_kategori') == $key ? 'selected' : '' }}>{{ $value }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Sub Kriteria Filter -->
              <div class="filter-group">
                <label for="filterJenisDokumen"><i class="fa-solid fa-tag"></i> Sub Kriteria</label>
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
                <label for="filterJenisSubPekerjaan"><i class="fa-solid fa-th-list"></i> Item Sub Kriteria</label>
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
                <label for="filterKebun"><i class="fa-solid fa-seedling"></i> Kebun</label>
                <select id="filterKebun" name="filter_kebun">
                  <option value="">Semua Kebun</option>
                  @foreach($availableKebuns ?? [] as $key => $value)
                    <option value="{{ $key }}" {{ request('filter_kebun') == $key ? 'selected' : '' }}>{{ $value }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Jenis Pembayaran Filter -->
              <div class="filter-group">
                <label for="filterJenisPembayaran"><i class="fa-solid fa-money-bill-wave"></i> Jenis Pembayaran</label>
                <select id="filterJenisPembayaran" name="filter_jenis_pembayaran">
                  <option value="">Semua Jenis</option>
                  @foreach($availableJenisPembayaran ?? [] as $key => $value)
                    <option value="{{ $key }}" {{ request('filter_jenis_pembayaran') == $key ? 'selected' : '' }}>{{ $value }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="afm-footer">
            <button type="button" class="btn-advanced-reset" onclick="resetAdvancedFilters()">
              <i class="fa-solid fa-times"></i>
              Reset Filter Lanjutan
            </button>
          </div>
        </div>
      </div>
    </form>

    @php
      // Rollout 4 — konfigurasi engine Tabulator bersama (public/js/document-tabulator.js).
      // $renderColumns SUDAH terurut kiri→bebas→kanan (FrozenColumnLayout::renderOrder,
      // dihitung index() :193) — WAJIB dipakai apa adanya (bukan $selectedColumns) agar
      // frozen native Tabulator membekukan ke tepi yang benar (lihat CFG.frozen di bawah).
      $pembayaranTabulatorConfig = [
        'mountId' => 'pembayaranTabulatorTable',
        'dataUrl' => route('documents.pembayaran.data'),
        'inlineUpdateTpl' => str_replace('__ID__', '{id}', route('documents.inline-update', ['dokumen' => '__ID__'])),
        'handlerTpl' => str_replace('__ID__', '{id}', route('documents.handler.update', ['dokumen' => '__ID__'])),
        'csrf' => csrf_token(),
        // Task 3 fitur export bersama (ADITIF): mengisi ini memunculkan tombol Export
        // (Excel/PDF) di toolbar Tabulator — kirim filter aktif + columns[] terlihat +
        // format ke exportDocuments() (DocumentExporter, ganti exportToExcel() lama yang
        // FATAL karena PhpSpreadsheet tak terpasang).
        'exportUrl' => route('documents.pembayaran.export'),
        // Fix QA Rollout 4 — kolom katalog 'status_pembayaran' ("Status Pembayaran")
        // dirender via formatter 'paymentPill' (baca row.status_badge, bukan field
        // status_pembayaran mentah) agar tetap beku/kustomisasi via $renderColumns,
        // paritas legacy (di sana "Status Pembayaran" ADALAH pill-nya). Menggantikan
        // extraColumns Status terpisah di bawah (sebelumnya dobel dgn kolom katalog ini).
        'columns' => collect($renderColumns)->map(fn ($k) => [
          'key' => $k,
          'label' => $availableColumns[$k] ?? $k,
          ...($k === 'status_pembayaran' ? ['formatter' => 'paymentPill'] : []),
        ])->values(),
        'availableColumns' => $availableColumns,
        'selected' => array_values($selectedColumns),
        // Pill status kini menyatu ke kolom katalog 'status_pembayaran' di atas —
        // tanpa kolom tetap terpisah lagi (dulu dobel dgn "Status Pembayaran").
        'extraColumns' => [],
        // Kolom "Pengurus Dokumen" DIHIDUPKAN 2026-08-06 (keputusan user). Dulu dimatikan
        // dengan alasan "Pembayaran = ujung alur, tak ada tahap berikutnya untuk di-forward"
        // — itu menjawab soal AKSI, bukan soal INFORMASI. Pembayaran adalah satu-satunya
        // role yang menampilkan dokumen TANPA filter current_handler (termasuk hasil impor
        // CSV), jadi merekalah yang paling sering melihat dokumen yang belum sampai ke
        // mejanya dan paling butuh tahu posisinya.
        // Dropdown otomatis nonaktif untuk dokumen di tahap lain (can_change_handler =
        // viewerRole === current_handler), dan aktif untuk dokumen yang memang sedang di
        // Pembayaran — sama persis dengan 4 role lain.
        'showHandler' => true,
        // Freeze 2-tab (modal Kolom Beku di bawah) → frozen native Tabulator.
        'frozen' => ['left' => array_values($frozenLeft), 'right' => array_values($frozenRight)],
        'ie' => [
          'kategori' => $ieKategoriList ?? [],
          'sub' => $ieSubKriteriaList ?? [],
          'item' => $ieItemSubKriteriaList ?? [],
          'jenis' => $ieJenisPembayaranList ?? [],
          'bagian' => \App\Models\Bagian::active()->ordered()->get(['kode', 'nama']),
        ],
        // Daftar sama persis dgn BULAN_LIST kanonik (partials/_inlineEditEngine.blade.php:145)
        // — 'May'/'July' BUKAN typo baru di sini, nilai ini dipakai apa adanya di DB/validasi.
        'bulanList' => ['Januari', 'Februari', 'Maret', 'April', 'May', 'Juni', 'July', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
      ];
    @endphp

    <!-- Table Section -->
    <div class="table-section table-dokumen" id="documentTableContainer">
      <div class="dtable-toolbar">
        <div class="dtable-toolbar-left">
          <div class="dtable-toolbar-icon">
            <i class="fa-solid fa-list"></i>
          </div>
          <div>
            <div class="dtable-toolbar-title">
              Daftar Dokumen
              <span class="table-count" id="tableCount">{{ $dokumens->total() }}</span>
            </div>
            <div class="dtable-toolbar-subtitle">Nomor agenda, SPP, vendor, nilai, dan status pembayaran.</div>
          </div>
        </div>
        <div class="dtable-toolbar-right">
          <button type="button" class="btn-refresh" onclick="refreshPembayaranTable()">
            <i class="fa-solid fa-arrows-rotate"></i> Refresh
          </button>
          <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
            <i class="fa-solid fa-table-columns"></i>
            Kustomisasi Kolom Tabel
          </button>
        </div>
      </div>

      <!-- Normal Table View (Tabulator) -->
      <div id="pembayaranTabulatorTable" class="doc-tabulator"></div>
    </div>
  </div>

  {{-- Modal Kustomisasi Kolom — markup + CSS kini partial bersama, lihat berkasnya. --}}
  @include('partials._columnCustomizationModal')

  {{-- JS modal bersama dimuat TANPA syarat mode (bukan di dalam @if($mode != 'rekapan_table')
       di bawah, tempat document-tabulator.js dimuat): tombol "Kustomisasi Kolom Tabel" di
       toolbar tampil di kedua mode (Normal & Group Vendor), jadi openColumnCustomizationModal()
       wajib selalu terdefinisi — pola lama (JS modal inline) juga unconditional. --}}
  <script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>

  <script>
    function refreshPembayaranTable() {
      if (typeof window.refreshPembayaranDataTable === 'function') {
        window.refreshPembayaranDataTable();
        return;
      }

      window.location.reload();
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

    // Advanced Filter Modal Toggle
    function openAdvancedFilterModal() {
      document.getElementById('advancedFilterModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }
    function closeAdvancedFilterModal() {
      document.getElementById('advancedFilterModal').classList.remove('show');
      document.body.style.overflow = '';
    }
    // Close modal on backdrop click
    document.getElementById('advancedFilterModal')?.addEventListener('click', function (e) {
      if (e.target === this) {
        closeAdvancedFilterModal();
      }
    });
    // Reset Advanced Filters
    function resetAdvancedFilters() {
      const filterBulan = document.getElementById('filterBulan');
      if (filterBulan) filterBulan.value = '';
      document.getElementById('filterBagian').value = '';
      document.getElementById('filterVendor').value = '';
      document.getElementById('filterKategori').value = '';
      document.getElementById('filterJenisDokumen').value = '';
      document.getElementById('filterJenisSubPekerjaan').value = '';
      document.getElementById('filterKebun').value = '';
      document.getElementById('filterJenisPembayaran').value = '';
    }

  </script>

    {{-- Rollout 4: engine Tabulator bersama menggantikan renderer bespoke di atas.
         window.DOCUMENT_TABULATOR_CONFIG dibangun dari $pembayaranTabulatorConfig
         (dihitung dekat "Table Section" di atas — columns via FrozenColumnLayout::
         renderOrder, frozen dari $frozenLeft/$frozenRight, showHandler:true sejak
         2026-08-06. Pill
         status kini menyatu ke kolom katalog 'status_pembayaran' via
         columns[].formatter='paymentPill', bukan extraColumns terpisah — fix QA
         dobel kolom Status). --}}
    <script>window.DOCUMENT_TABULATOR_CONFIG = @json($pembayaranTabulatorConfig);</script>
    <script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::versioned('js/document-tabulator.js') }}"></script>
    <script>
      // Tombol Refresh toolbar (dtable-toolbar) memanggil window.refreshPembayaranDataTable
      // bila terdefinisi (lihat refreshPembayaranTable() di atas) — arahkan ke replaceData()
      // Tabulator (AJAX, tanpa reload halaman) alih-alih window.location.reload() bawaan.
      // window.documentTable diset SINKRON oleh document-tabulator.js saat skrip di atas
      // dieksekusi (elemen mount sudah ada di DOM sebelum tag <script> ini).
      window.refreshPembayaranDataTable = function () {
        if (window.documentTable) { window.documentTable.replaceData(); }
      };
    </script>

  {{-- ── Tombol keluar fullscreen (khusus halaman pembayaran) ──────────────
       Fullscreen global (compact-document-ui) menyembunyikan .dtable-toolbar —
       tempat tombol keluar berada — dan menampilkan .search-box sebagai bar atas.
       Halaman pembayaran TIDAK punya .search-box, sehingga tombol keluar ikut
       tersembunyi. Tombol mengambang ini disisipkan ke <body> (di luar .content,
       agar tak ikut disembunyikan aturan `.content > :not(...)`) dan hanya tampil
       saat mode fullscreen. Kliknya memicu mekanisme keluar milik tombol global. --}}
  <style>
    .pembayaran-fs-exit {
      display: none;
      position: fixed;
      top: 14px;
      right: 18px;
      z-index: 2147483600; /* di atas konten fullscreen (.content = 2147483000) */
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border: none;
      border-radius: 10px;
      background: #0f4c3a;
      color: #ffffff;
      font-weight: 700;
      font-size: 0.85rem;
      cursor: pointer;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.32);
    }
    .pembayaran-fs-exit:hover { background: #0d3f30; }
    body.document-table-only-fullscreen .pembayaran-fs-exit,
    body.is-fullscreen .pembayaran-fs-exit { display: inline-flex; }
  </style>
  <script>
    (function () {
      var exitBtn = document.createElement('button');
      exitBtn.type = 'button';
      exitBtn.id = 'pembayaranFsExit';
      exitBtn.className = 'pembayaran-fs-exit';
      // Teks tombol TIDAK boleh mengandung "keluar fullscreen": observer di
      // compact-document-ui memakai regex /keluar fullscreen/i pada textContent
      // setiap tombol untuk menyimpulkan "masih di mode fullscreen". Bila label ini
      // cocok, aksi keluar langsung dianulir (fullscreen aktif lagi seketika).
      // "Keluar Layar Penuh" aman dari regex itu dan tetap jelas.
      exitBtn.title = 'Keluar layar penuh (Esc)';
      exitBtn.innerHTML = '<i class="fa-solid fa-compress"></i> Keluar Layar Penuh';
      exitBtn.addEventListener('click', function () {
        // Picu mekanisme keluar milik tombol fullscreen global (app.blade.php).
        var globalFsBtn = document.querySelector('.btn-fullscreen-toggle');
        if (globalFsBtn) {
          globalFsBtn.click();
          return;
        }
        // Cadangan: kirim tombol Escape.
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
      });
      document.body.appendChild(exitBtn);
    })();
  </script>

@endsection

@push('styles')
  {{-- Rollout 4: aset Tabulator (vendor + tema bersama ala CASH_BANK). Dimuat di
       sini (bukan layouts/app.blade.php) agar tipografi/role lain tak ikut
       berubah (CLAUDE.md §6). --}}
  <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
  <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/tabulator-agenda.css') }}">
@endpush

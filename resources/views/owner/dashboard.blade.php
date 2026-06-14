@extends('layouts.app')

@section('content')
@php
  $activeStatus = request('status', 'all');
  if ($activeStatus === 'semua') $activeStatus = 'all';

  $tabs = [
    ['id' => 'all', 'label' => 'Semua', 'count' => $documentTabs['all'] ?? 0],
    ['id' => 'pending', 'label' => 'Menunggu', 'count' => $documentTabs['pending'] ?? 0],
    ['id' => 'urgent', 'label' => 'Urgent', 'count' => $documentTabs['urgent'] ?? 0, 'danger' => true],
    ['id' => 'returned', 'label' => 'Dikembalikan', 'count' => $documentTabs['returned'] ?? 0],
    ['id' => 'done', 'label' => 'Selesai', 'count' => $documentTabs['done'] ?? 0],
  ];

  $bagianColors = [
    'AKN' => '#7c3aed', 'DPM' => '#22c55e', 'KPL' => '#f59e0b',
    'PMO' => '#06b6d4', 'SDM' => '#8b5cf6', 'SKH' => '#ec4899',
    'TAN' => '#10b981', 'TEP' => '#6366f1', 'PTI' => '#3b82f6',
  ];

  $totalNilaiNum = (float)($totalNilai ?? 0);
  $totalNilaiShort = $totalNilaiNum >= 1_000_000_000
    ? 'Rp ' . number_format($totalNilaiNum / 1_000_000_000, 1, ',', '.') . ' M'
    : 'Rp ' . number_format($totalNilaiNum / 1_000_000, 1, ',', '.') . ' Jt';

  $queryWithoutStatus = request()->except(['status', 'page']);
  $ownerDocsBaseUrl = url('/owner/dokumen');
  $ownerStatUrls = [
    'total' => $ownerDocsBaseUrl . '?' . http_build_query(['status' => 'all']),
    'unpaid' => $ownerDocsBaseUrl . '?' . http_build_query(['status' => 'all', 'filter_status_pembayaran' => 'belum_dibayar']),
    'ready' => $ownerDocsBaseUrl . '?' . http_build_query(['status' => 'all', 'filter_status_pembayaran' => 'siap_dibayar']),
    'paid' => $ownerDocsBaseUrl . '?' . http_build_query(['status' => 'all', 'filter_status_pembayaran' => 'sudah_dibayar']),
    'returned' => $ownerDocsBaseUrl . '?' . http_build_query(['status' => 'returned']),
  ];
  $formatFilterMoney = fn ($value) => trim((string) $value) === ''
    ? ''
    : number_format((float) preg_replace('/[^0-9]/', '', (string) $value), 0, ',', '.');
@endphp

<style>
  :root {
    --od-bg: #f4f6fb;
    --od-card: #ffffff;
    --od-border: #e8ecf4;
    --od-border-soft: #f1f5f9;
    --od-text: #1a2340;
    --od-sub: #6b7a99;
    --od-muted: #a0aec0;
    --od-blue: #2563eb;
    --od-blue-soft: #eff4ff;
    --od-sky-soft: #e0f2fe;
    --od-green: #10b981;
    --od-green-soft: #ecfdf5;
    --od-orange: #f59e0b;
    --od-orange-soft: #fffbeb;
    --od-red: #ef4444;
    --od-red-soft: #fef2f2;
    --od-teal: #0f766e;
    --od-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05);
  }

  .owner-docs {
    min-height: 100vh;
    background: var(--od-bg);
    color: var(--od-text);
    font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
    font-size: 13px;
  }

  .owner-docs-topbar {
    background: var(--od-card);
    border-bottom: 1px solid var(--od-border);
    padding: 14px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    position: sticky;
    top: 0;
    z-index: 5;
  }

  .owner-docs-title {
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--od-text);
    margin: 0;
  }

  .owner-docs-subtitle {
    font-size: 12px;
    color: var(--od-muted);
    margin-top: 2px;
  }

  .owner-docs-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .owner-docs-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 13px;
    border-radius: 8px;
    border: 1px solid var(--od-border);
    background: #fff;
    color: var(--od-sub);
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
  }

  .owner-docs-pill i {
    font-size: 12px;
  }

  .owner-docs-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    flex: 0 0 auto;
  }

  .owner-docs-content {
    padding: 24px 28px 40px;
  }

  .owner-docs-stats {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 20px;
  }

  .owner-docs-stat {
    background: var(--od-card);
    border: 1px solid var(--od-border);
    border-radius: 14px;
    color: inherit;
    display: block;
    padding: 18px 18px 14px;
    box-shadow: var(--od-shadow);
    position: relative;
    min-height: 132px;
    overflow: hidden;
    text-decoration: none;
  }

  .owner-docs-stat-link {
    cursor: pointer;
    transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
  }

  .owner-docs-stat-link:hover,
  .owner-docs-stat-link:focus {
    border-color: #cbd5e1;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    color: inherit;
    outline: none;
    transform: translateY(-1px);
  }

  .owner-docs-stat-link:focus-visible {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.16), 0 8px 24px rgba(15, 23, 42, 0.12);
  }

  .owner-docs-stat-detail {
    color: #2563eb;
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    margin-top: 8px;
  }

  .owner-docs-stat.accent .owner-docs-stat-detail {
    color: rgba(255,255,255,.88);
  }

  .owner-docs-stat.accent {
    background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
    border-color: transparent;
  }

  .owner-docs-stat-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--od-muted);
    margin-bottom: 12px;
  }

  .owner-docs-stat.accent .owner-docs-stat-label {
    color: rgba(255,255,255,.72);
  }

  .owner-docs-stat-value {
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: 26px;
    font-weight: 700;
    line-height: 1;
    color: var(--od-text);
    margin-bottom: 8px;
  }

  .owner-docs-stat.accent .owner-docs-stat-value {
    color: #fff;
    font-size: 21px;
  }

  .owner-docs-stat-sub {
    font-size: 11px;
    color: var(--od-muted);
    line-height: 1.35;
  }

  .owner-docs-stat.accent .owner-docs-stat-sub {
    color: rgba(255,255,255,.68);
  }

  .owner-docs-stat-icon {
    position: absolute;
    right: 16px;
    top: 16px;
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }

  .owner-docs-section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
  }

  .owner-docs-section-title {
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: 15px;
    font-weight: 700;
    margin: 0;
    color: var(--od-text);
  }

  .owner-docs-live {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    font-size: 11px;
    color: var(--od-green);
    font-weight: 600;
  }

  .owner-docs-live .owner-docs-dot {
    background: var(--od-green);
    animation: odPulse 1.8s infinite;
  }

  @keyframes odPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: .45; transform: scale(.78); }
  }

  .owner-docs-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .owner-docs-select {
    min-height: 38px;
    border: 1px solid var(--od-border);
    border-radius: 8px;
    background: #fff;
    color: var(--od-sub);
    padding: 0 34px 0 12px;
    font-size: 12px;
    font-weight: 600;
    outline: none;
  }

  .owner-docs-tabs-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
  }

  .owner-docs-tabs {
    background: #fff;
    border: 1px solid var(--od-border);
    border-radius: 14px;
    padding: 6px;
    box-shadow: var(--od-shadow);
    display: inline-flex;
    gap: 4px;
    max-width: 100%;
    overflow-x: auto;
  }

  .owner-docs-age-shortcuts {
    display: inline-flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  .owner-docs-age-chip {
    display: inline-flex;
    align-items: center;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid var(--od-border);
    background: #fff;
    box-shadow: var(--od-shadow);
    color: var(--od-sub);
    font-weight: 800;
    font-size: 12px;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease;
  }

  .owner-docs-age-chip:hover {
    border-color: #93c5fd;
    color: #0369a1;
    background: #f8fafc;
    transform: translateY(-1px);
  }

  .owner-docs-age-chip.active {
    background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
    border-color: transparent;
    color: #fff;
  }

  .owner-docs-age-chip-count {
    margin-left: 6px;
    padding: 1px 7px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    font-size: 10.5px;
    font-weight: 800;
  }

  .owner-docs-age-chip.active .owner-docs-age-chip-count {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
  }

  .owner-docs-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 9px;
    color: var(--od-sub);
    text-decoration: none;
    font-weight: 700;
    font-size: 12.5px;
    white-space: nowrap;
  }

  .owner-docs-tab:hover {
    color: #0369a1;
    background: #f8fafc;
  }

  .owner-docs-tab.active {
    background: var(--od-sky-soft);
    color: #0369a1;
  }

  .owner-docs-tab.danger.active {
    background: var(--od-red-soft);
    color: #dc2626;
  }

  .owner-docs-tab-count {
    min-width: 24px;
    text-align: center;
    padding: 1px 8px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    font-size: 10.5px;
    font-weight: 800;
  }

  .owner-docs-tab.active .owner-docs-tab-count {
    background: #0369a1;
    color: #fff;
  }

  .owner-docs-tab.danger.active .owner-docs-tab-count {
    background: #dc2626;
  }

  .owner-docs-search {
    display: flex;
    align-items: center;
    gap: 11px;
    background: #fff;
    border: 1px solid var(--od-border);
    border-radius: 12px;
    padding: 11px 16px;
    box-shadow: var(--od-shadow);
    margin-bottom: 14px;
  }

  .owner-docs-search i {
    color: #94a3b8;
    font-size: 14px;
  }

  .owner-docs-search input {
    flex: 1;
    min-width: 160px;
    border: 0;
    outline: 0;
    color: var(--od-text);
    font-size: 13.5px;
    font-family: inherit;
  }

  .owner-docs-shortcut {
    font-family: 'Sora', monospace;
    color: var(--od-muted);
    font-size: 11px;
  }

  .owner-docs-filters {
    background: #fff;
    border: 1px solid var(--od-border);
    border-radius: 14px;
    box-shadow: var(--od-shadow);
    padding: 14px;
    margin-bottom: 18px;
  }

  .owner-docs-filter-grid {
    display: grid;
    grid-template-columns: repeat(8, minmax(130px, 1fr));
    gap: 10px;
    align-items: end;
  }

  .owner-docs-filter-group label {
    display: block;
    color: var(--od-muted);
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 6px;
  }

  .owner-docs-filter-group select,
  .owner-docs-filter-group input {
    width: 100%;
    border: 1px solid var(--od-border);
    border-radius: 8px;
    min-height: 38px;
    color: var(--od-text);
    background: #fff;
    padding: 0 11px;
    font-size: 12px;
    outline: none;
  }

  /* Searchable vendor combobox */
  .owner-vendor-combo {
    position: relative;
  }

  .owner-vendor-control {
    display: flex;
    align-items: center;
    border: 1px solid var(--od-border);
    border-radius: 8px;
    background: #fff;
    min-height: 38px;
    padding-right: 6px;
  }

  .owner-vendor-combo .owner-vendor-input {
    border: 0 !important;
    min-height: 36px !important;
    flex: 1 1 auto;
    border-radius: 8px !important;
    text-overflow: ellipsis;
  }

  .owner-vendor-clear {
    border: 0;
    background: transparent;
    color: var(--od-muted);
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    padding: 0 4px;
    display: none;
  }

  .owner-vendor-combo.has-value .owner-vendor-clear {
    display: inline-block;
  }

  .owner-vendor-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 60;
    max-height: 260px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid var(--od-border);
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.16);
    padding: 4px;
  }

  .owner-vendor-opt {
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 12px;
    color: var(--od-text);
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .owner-vendor-opt:hover,
  .owner-vendor-opt.is-active {
    background: var(--od-sky-soft);
    color: #0369a1;
  }

  .owner-vendor-empty {
    padding: 10px;
    font-size: 12px;
    color: var(--od-muted);
    text-align: center;
  }

  .owner-docs-filter-actions {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .owner-docs-btn {
    border: 1px solid var(--od-border);
    border-radius: 8px;
    min-height: 38px;
    padding: 0 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    background: #fff;
    color: var(--od-sub);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
  }

  .owner-docs-btn.primary {
    background: #0369a1;
    color: #fff;
    border-color: #0369a1;
  }

  .owner-docs-btn:hover {
    border-color: #0369a1;
    color: #0369a1;
  }

  .owner-docs-btn.primary:hover {
    color: #fff;
  }

  .owner-docs-table-card {
    background: #fff;
    border: 1px solid var(--od-border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--od-shadow);
  }

  .owner-docs-table-wrap {
    overflow-x: auto;
    cursor: grab;
  }

  .owner-docs-table-wrap.is-dragging {
    cursor: grabbing;
    user-select: none;
  }

  .owner-docs-table-wrap.is-dragging .owner-docs-row {
    cursor: grabbing;
  }

  .owner-docs-table {
    width: 100%;
    min-width: 1500px;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .owner-docs-col-doc { width: 30%; }
  .owner-docs-col-value { width: 11%; }
  .owner-docs-col-from { width: 8%; }
  .owner-docs-col-handler { width: 17%; }
  .owner-docs-col-status { width: 12%; }
  .owner-docs-col-duration { width: 11%; }
  .owner-docs-col-age { width: 11%; }

  .owner-docs-table thead {
    background: #f8fafc;
  }

  .owner-docs-table th {
    padding: 16px 12px;
    color: var(--od-muted);
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    text-align: center;
    border-bottom: 1px solid var(--od-border);
    white-space: normal;
    line-height: 1.25;
    vertical-align: middle;
  }

  .owner-docs-th-label {
    display: inline-block;
    max-width: 100%;
    overflow-wrap: normal;
  }

  .owner-docs-table th:nth-child(n+4),
  .owner-docs-table td:nth-child(n+4) {
    border-left: 1px solid var(--od-border-soft);
  }

  .owner-docs-table th:nth-child(4),
  .owner-docs-table td:nth-child(4) {
    padding-left: 18px;
    padding-right: 18px;
  }

  .owner-docs-table th:nth-child(6),
  .owner-docs-table td:nth-child(6),
  .owner-docs-table th:nth-child(7),
  .owner-docs-table td:nth-child(7) {
    padding-left: 18px;
    padding-right: 18px;
  }

  .owner-docs-table th:first-child,
  .owner-docs-table td:first-child {
    padding-left: 30px;
  }

  .owner-docs-table th:last-child,
  .owner-docs-table td:last-child {
    padding-right: 30px;
  }

  .owner-docs-table td {
    padding: 17px 14px;
    border-bottom: 1px solid var(--od-border-soft);
    vertical-align: middle;
    min-width: 0;
  }

  .owner-docs-table td:nth-child(2) {
    text-align: right;
  }

  .owner-docs-table td:nth-child(4),
  .owner-docs-table td:nth-child(5),
  .owner-docs-table td:nth-child(7) {
    text-align: center;
  }

  .owner-docs-row {
    cursor: pointer;
    transition: background .15s ease;
  }

  .owner-docs-row:hover {
    background: #f8fafc;
  }

  .owner-docs-docmeta {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 8px;
    margin-bottom: 7px;
    min-width: 0;
  }

  .owner-docs-docno {
    font-family: 'Sora', monospace;
    color: #475569;
    font-size: 11.5px;
    font-weight: 700;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .owner-docs-urgent {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 999px;
    background: var(--od-red-soft);
    color: #dc2626;
    font-size: 10px;
    font-weight: 800;
  }

  .owner-docs-docname {
    max-width: 520px;
    color: var(--od-text);
    font-weight: 800;
    font-size: 14px;
    line-height: 1.35;
    text-align: left;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    overflow-wrap: anywhere;
  }

  .owner-docs-payee {
    color: var(--od-muted);
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
    text-align: left;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    overflow-wrap: anywhere;
  }

  .owner-docs-money {
    font-family: 'Sora', monospace;
    color: #07132d;
    font-weight: 800;
    font-size: 12.5px;
    white-space: nowrap;
    text-align: right;
  }

  .owner-docs-bagian {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    color: var(--od-text);
    white-space: nowrap;
  }

  .owner-docs-bagian small {
    display: block;
    color: var(--od-muted);
    font-size: 10.5px;
    font-weight: 600;
    margin-top: 2px;
  }

  .owner-docs-handler {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
    width: 100%;
  }

  .owner-docs-handler > span:last-child {
    min-width: 0;
    max-width: 100%;
  }

  .owner-docs-avatar {
    width: 30px;
    height: 30px;
    border-radius: 9px;
    background: var(--od-blue-soft);
    color: var(--od-blue);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 900;
    flex: 0 0 auto;
  }

  .owner-docs-handler-name {
    display: block;
    color: var(--od-text);
    font-weight: 800;
    font-size: 12.5px;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .owner-docs-handler-sub {
    display: block;
    color: var(--od-muted);
    font-size: 10.5px;
    margin-top: 2px;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .owner-docs-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 800;
    white-space: nowrap;
    max-width: 100%;
  }

  .owner-docs-status.waiting {
    background: var(--od-orange-soft);
    color: #d97706;
  }

  .owner-docs-status.ready {
    background: var(--od-blue-soft);
    color: var(--od-blue);
  }

  .owner-docs-status.paid {
    background: var(--od-green-soft);
    color: #059669;
  }

  .owner-docs-duration {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'Sora', monospace;
    font-size: 11.5px;
    font-weight: 800;
    white-space: nowrap;
    justify-content: center;
    min-width: max-content;
  }

  .owner-docs-duration.safe {
    color: #0f766e;
  }

  .owner-docs-duration.warn {
    color: #d97706;
  }

  .owner-docs-duration.late {
    color: #dc2626;
  }

  .owner-docs-duration.muted {
    color: var(--od-muted);
  }

  .owner-docs-age {
    display: inline-block;
    color: #475569;
    font-family: 'Sora', monospace;
    font-size: 11.5px;
    font-weight: 800;
    white-space: nowrap;
    min-width: max-content;
  }

  .owner-docs-age.paid {
    color: #059669;
  }

  .owner-docs-empty {
    padding: 54px 24px;
    text-align: center;
    color: var(--od-muted);
  }

  .owner-docs-empty i {
    display: block;
    font-size: 30px;
    margin-bottom: 12px;
    color: #cbd5e1;
  }

  .owner-docs-empty strong {
    display: block;
    color: var(--od-text);
    font-size: 15px;
    margin-bottom: 4px;
  }

  .owner-docs-modal {
    position: fixed;
    inset: 0;
    z-index: 11000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 28px;
    background: rgba(15, 23, 42, .55);
    backdrop-filter: blur(5px);
  }

  .owner-docs-modal.open {
    display: flex;
  }

  .owner-docs-modal-panel {
    width: min(1180px, 96vw);
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 28px 80px rgba(15, 23, 42, .28);
  }

  .owner-docs-modal-head {
    position: relative;
    overflow: hidden;
    padding: 24px 92px 24px 34px;
    background: linear-gradient(135deg, #14513f 0%, #0f3d2e 100%);
    color: #fff;
    flex: 0 0 auto;
  }

  .owner-docs-modal-head::before,
  .owner-docs-modal-head::after {
    content: '';
    position: absolute;
    border: 1px solid rgba(255, 255, 255, .10);
    border-radius: 50%;
    width: 260px;
    height: 260px;
    right: 150px;
    top: -110px;
  }

  .owner-docs-modal-head::after {
    width: 360px;
    height: 360px;
    right: -70px;
    top: -160px;
  }

  .owner-docs-modal-no {
    position: relative;
    font-family: 'Sora', monospace;
    font-size: 12px;
    color: rgba(255, 255, 255, .72);
    margin-bottom: 8px;
  }

  .owner-docs-modal-title {
    position: relative;
    margin: 0;
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: clamp(20px, 2.1vw, 24px);
    font-weight: 800;
    line-height: 1.25;
    max-width: 760px;
    max-height: 3.75em;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    overflow-wrap: anywhere;
  }

  .owner-docs-modal-sub {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
    color: rgba(255, 255, 255, .78);
    font-size: 14px;
    font-weight: 600;
    min-width: 0;
  }

  .owner-docs-modal-sub span:last-child {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .owner-docs-modal-close {
    position: absolute;
    right: 32px;
    top: 28px;
    width: 44px;
    height: 44px;
    border: 0;
    border-radius: 10px;
    background: rgba(255, 255, 255, .12);
    color: #fff;
    font-size: 19px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
  }

  .owner-docs-modal-body {
    display: grid;
    grid-template-columns: 1.2fr .9fr;
    overflow: auto;
    min-height: 0;
  }

  .owner-docs-modal-timeline {
    padding: 30px 34px;
    border-right: 1px solid var(--od-border);
  }

  .owner-docs-modal-detail {
    padding: 30px 34px;
    background: #f8fafc;
  }

  .owner-docs-modal-section {
    margin: 0 0 22px;
    color: var(--od-muted);
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .08em;
  }

  .owner-docs-timeline-list {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0;
  }

  .owner-docs-timeline-item {
    position: relative;
    display: grid;
    grid-template-columns: 40px 1fr;
    gap: 16px;
    min-height: 76px;
  }

  .owner-docs-timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 36px;
    bottom: 0;
    width: 2px;
    background: #dbe3ef;
  }

  .owner-docs-timeline-item.done:not(:last-child)::before,
  .owner-docs-timeline-item.active:not(:last-child)::before {
    background: #10b981;
  }

  .owner-docs-timeline-dot {
    position: relative;
    z-index: 1;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8fafc;
    border: 1px solid #dbe3ef;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
  }

  .owner-docs-timeline-item.done .owner-docs-timeline-dot {
    background: #10b981;
    border-color: #10b981;
    color: #fff;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, .14);
  }

  .owner-docs-timeline-item.active .owner-docs-timeline-dot {
    background: #eff4ff;
    border-color: #2563eb;
    color: #2563eb;
  }

  .owner-docs-timeline-name {
    color: var(--od-text);
    font-size: 15px;
    font-weight: 900;
    margin-top: 2px;
  }

  .owner-docs-timeline-date {
    margin-top: 5px;
    color: #8ca0bd;
    font-size: 12.5px;
    font-weight: 600;
  }

  .owner-docs-detail-field {
    margin-bottom: 18px;
    min-width: 0;
  }

  .owner-docs-detail-field.full {
    padding: 16px;
    border: 1px solid #dfe7ef;
    border-radius: 12px;
    background: #fff;
  }

  .owner-docs-detail-label {
    margin-bottom: 6px;
    color: var(--od-muted);
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .08em;
  }

  .owner-docs-detail-value {
    color: var(--od-text);
    font-size: 14px;
    font-weight: 800;
    line-height: 1.45;
    overflow-wrap: anywhere;
  }

  .owner-docs-detail-value.mono {
    font-family: 'Sora', monospace;
  }

  .owner-docs-detail-value.long-text {
    color: #334155;
    font-weight: 650;
    white-space: pre-wrap;
  }

  @media (max-width: 1280px) {
    .owner-docs-stats {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .owner-docs-filter-grid {
      grid-template-columns: repeat(4, minmax(145px, 1fr));
    }
  }

  @media (max-width: 900px) {
    .owner-docs-topbar {
      align-items: flex-start;
      flex-direction: column;
    }

    .owner-docs-controls,
    .owner-docs-toolbar {
      justify-content: flex-start;
    }

    .owner-docs-stats,
    .owner-docs-filter-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .owner-docs-modal {
      padding: 16px;
      align-items: flex-start;
      overflow: auto;
    }

    .owner-docs-modal-panel {
      width: 100%;
      max-height: none;
    }

    .owner-docs-modal-body {
      grid-template-columns: 1fr;
    }

    .owner-docs-modal-timeline {
      border-right: 0;
      border-bottom: 1px solid var(--od-border);
    }
  }

  @media (max-width: 640px) {
    .owner-docs-content,
    .owner-docs-topbar {
      padding-left: 16px;
      padding-right: 16px;
    }

    .owner-docs-stats,
    .owner-docs-filter-grid {
      grid-template-columns: 1fr;
    }

    .owner-docs-search {
      align-items: stretch;
    }

    .owner-docs-shortcut {
      display: none;
    }

    .owner-docs-modal-head,
    .owner-docs-modal-timeline,
    .owner-docs-modal-detail {
      padding-left: 20px;
      padding-right: 20px;
    }

    .owner-docs-modal-close {
      right: 18px;
      top: 18px;
    }

    .owner-docs-modal-title {
      padding-right: 48px;
      font-size: 20px;
      -webkit-line-clamp: 2;
      max-height: 2.5em;
    }

    .owner-docs-table {
      min-width: 0;
      table-layout: auto;
    }

    .owner-docs-table thead {
      display: none;
    }

    .owner-docs-table colgroup {
      display: none;
    }

    .owner-docs-table,
    .owner-docs-table tbody,
    .owner-docs-table tr,
    .owner-docs-table td {
      display: block;
      width: 100%;
    }

    .owner-docs-table td {
      padding: 9px 18px;
      border-bottom: 0;
    }

    .owner-docs-table td:nth-child(n+4) {
      border-left: 0;
    }

    .owner-docs-table td[data-label]::before {
      content: attr(data-label);
      display: block;
      margin-bottom: 4px;
      color: var(--od-muted);
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .06em;
      text-transform: uppercase;
    }

    .owner-docs-table td:first-child {
      padding: 16px 18px 8px;
    }

    .owner-docs-table td:last-child {
      padding: 8px 18px 16px;
    }

    .owner-docs-row {
      display: block;
      border-bottom: 1px solid var(--od-border-soft);
    }

    .owner-docs-docname {
      max-width: none;
      -webkit-line-clamp: 3;
    }

    .owner-docs-money,
    .owner-docs-table td:nth-child(n+2) {
      text-align: left;
    }

    .owner-docs-status,
    .owner-docs-duration,
    .owner-docs-age {
      justify-content: flex-start;
    }
  }
</style>

<div class="owner-docs">
  <div class="owner-docs-topbar">
    <div>
      <h1 class="owner-docs-title">Overview Keuangan</h1>
      <div class="owner-docs-subtitle">Pantau dan kelola semua dokumen perusahaan</div>
    </div>
    <div class="owner-docs-controls">
      <div class="owner-docs-pill">
        <i class="far fa-calendar"></i>
        {{ now()->format('d M Y') }}
        <i class="fas fa-chevron-down" style="font-size:10px"></i>
      </div>
      <div class="owner-docs-pill">
        <span class="owner-docs-dot" style="background:var(--od-green)"></span>
        Semua Bagian
        <i class="fas fa-chevron-down" style="font-size:10px"></i>
      </div>
      <div class="owner-docs-pill">
        30 Hari Terakhir
        <i class="fas fa-chevron-down" style="font-size:10px"></i>
      </div>
    </div>
  </div>

  <div class="owner-docs-content">
    <div class="owner-docs-stats">
      <a class="owner-docs-stat owner-docs-stat-link" href="{{ $ownerStatUrls['total'] }}">
        <div class="owner-docs-stat-label">Total Dokumen</div>
        <div class="owner-docs-stat-icon" style="background:#f0f4ff;color:#2563eb">
          <i class="far fa-file-alt"></i>
        </div>
        <div class="owner-docs-stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub">Total seluruh dokumen</div>
        <span class="owner-docs-stat-detail">Lihat detail</span>
      </a>

      <a class="owner-docs-stat owner-docs-stat-link" href="{{ $ownerStatUrls['unpaid'] }}">
        <div class="owner-docs-stat-label">Belum Dibayar</div>
        <div class="owner-docs-stat-icon" style="background:#fffbeb;color:#f59e0b">
          <i class="far fa-clock"></i>
        </div>
        <div class="owner-docs-stat-value" style="color:#f59e0b">{{ number_format($dokumenProses ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub" style="font-weight:700;color:#f59e0b">Rp {{ number_format($nilaiBelumDibayar ?? 0, 0, ',', '.') }}</div>
        <span class="owner-docs-stat-detail">Lihat belum dibayar</span>
      </a>

      <a class="owner-docs-stat owner-docs-stat-link" href="{{ $ownerStatUrls['ready'] }}">
        <div class="owner-docs-stat-label">Siap Dibayar</div>
        <div class="owner-docs-stat-icon" style="background:#eff4ff;color:#2563eb">
          <i class="fas fa-check"></i>
        </div>
        <div class="owner-docs-stat-value" style="color:#2563eb">{{ number_format($dokumenSiapBayar ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub">Rp {{ number_format($nilaiSiapDibayar ?? 0, 0, ',', '.') }}</div>
        <span class="owner-docs-stat-detail">Lihat siap bayar</span>
      </a>

      <a class="owner-docs-stat owner-docs-stat-link" href="{{ $ownerStatUrls['paid'] }}">
        <div class="owner-docs-stat-label">Sudah Dibayar</div>
        <div class="owner-docs-stat-icon" style="background:#ecfdf5;color:#10b981">
          <i class="far fa-credit-card"></i>
        </div>
        <div class="owner-docs-stat-value" style="color:#10b981">{{ number_format($dokumenSelesai ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub" style="font-weight:700;color:#10b981">Rp {{ number_format($nilaiSudahDibayar ?? 0, 0, ',', '.') }}</div>
        <span class="owner-docs-stat-detail">Lihat sudah dibayar</span>
      </a>

      <a class="owner-docs-stat owner-docs-stat-link" href="{{ $ownerStatUrls['returned'] }}">
        <div class="owner-docs-stat-label">Dikembalikan</div>
        <div class="owner-docs-stat-icon" style="background:#fef2f2;color:#ef4444">
          <i class="fas fa-undo"></i>
        </div>
        <div class="owner-docs-stat-value" style="color:#ef4444">{{ number_format($dokumenDikembalikan ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub" style="font-weight:700;color:#ef4444">Rp {{ number_format($nilaiDikembalikan ?? 0, 0, ',', '.') }}</div>
        <span class="owner-docs-stat-detail">Lihat dikembalikan</span>
      </a>

      <a class="owner-docs-stat owner-docs-stat-link accent" href="{{ $ownerStatUrls['total'] }}">
        <div class="owner-docs-stat-label">Total Nilai</div>
        <div class="owner-docs-stat-value">{{ $totalNilaiShort }}</div>
        <div class="owner-docs-stat-sub">Rp {{ number_format($totalNilaiNum, 0, ',', '.') }}</div>
        <span class="owner-docs-stat-detail">Lihat semua dokumen</span>
      </a>
    </div>

    <div class="owner-docs-section-head">
      <div>
        <h2 class="owner-docs-section-title">Dokumen Terbaru</h2>
        <div class="owner-docs-live">
          <span class="owner-docs-dot"></span>
          Live update otomatis
        </div>
      </div>
      <div class="owner-docs-toolbar">
        <select class="owner-docs-select" onchange="changePerPage(this.value)" aria-label="Baris per halaman">
          @foreach([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" {{ (int)($documents->perPage() ?? 10) === $size ? 'selected' : '' }}>{{ $size }} baris</option>
          @endforeach
          <option value="all" {{ request('per_page') === 'all' ? 'selected' : '' }}>Semua baris</option>
        </select>
      </div>
    </div>

    <div class="owner-docs-tabs-row">
      <div class="owner-docs-tabs" aria-label="Filter status dokumen">
        @foreach($tabs as $tab)
          @php
            $tabUrl = url('/owner/dokumen') . '?' . http_build_query(array_merge($queryWithoutStatus, ['status' => $tab['id']]));
            $tabActive = $activeStatus === $tab['id'];
          @endphp
          <a class="owner-docs-tab {{ $tabActive ? 'active' : '' }} {{ ($tab['danger'] ?? false) ? 'danger' : '' }}" href="{{ $tabUrl }}">
            {{ $tab['label'] }}
            <span class="owner-docs-tab-count">{{ number_format($tab['count'], 0, ',', '.') }}</span>
          </a>
        @endforeach
      </div>

      <div class="owner-docs-age-shortcuts" aria-label="Pintasan filter umur dokumen">
        @php
          $ageShortcuts = [
            ['min' => 7,   'max' => 30,   'label' => '7&ndash;30 Hari'],
            ['min' => 30,  'max' => 60,   'label' => '30&ndash;60 Hari'],
            ['min' => 60,  'max' => 120,  'label' => '60&ndash;120 Hari'],
            ['min' => 120, 'max' => null, 'label' => '&gt; 120 Hari'],
          ];
        @endphp
        @foreach($ageShortcuts as $bucket)
          @php
            $ageParams = array_merge($queryWithoutStatus, ['status' => $activeStatus, 'filter_umur_min' => $bucket['min']]);
            if (!is_null($bucket['max'])) {
              $ageParams['filter_umur_max'] = $bucket['max'];
            } else {
              unset($ageParams['filter_umur_max']);
            }
            $ageUrl = url('/owner/dokumen') . '?' . http_build_query($ageParams);
            $ageActive = (string) request('filter_umur_min') === (string) $bucket['min']
              && (string) request('filter_umur_max', '') === (string) ($bucket['max'] ?? '');
            $ageCount = $umurShortcutCounts[$bucket['min']] ?? 0;
            $ageTitle = is_null($bucket['max'])
              ? "Dokumen berumur lebih dari {$bucket['min']} hari"
              : "Dokumen berumur {$bucket['min']}-{$bucket['max']} hari";
          @endphp
          <a class="owner-docs-age-chip {{ $ageActive ? 'active' : '' }}" href="{{ $ageUrl }}" title="{{ $ageTitle }}">
            {!! $bucket['label'] !!}<span class="owner-docs-age-chip-count">{{ number_format($ageCount, 0, ',', '.') }}</span>
          </a>
        @endforeach
      </div>
    </div>

    <form method="GET" action="{{ url('/owner/dokumen') }}" id="ownerDocsFilterForm">
      <input type="hidden" name="status" value="{{ $activeStatus }}">
      <input type="hidden" name="per_page" value="{{ request('per_page', $documents->perPage() ?? 10) }}">

      <div class="owner-docs-search">
        <i class="fas fa-search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor agenda, uraian SPP, penerima, atau bagian...">
        <span class="owner-docs-shortcut">Ctrl K</span>
      </div>

      <div class="owner-docs-filters">
        <div class="owner-docs-filter-grid">
          <div class="owner-docs-filter-group">
            <label>Dari</label>
            <select name="filter_bagian">
              <option value="">Semua Bagian</option>
              @foreach($filterData['bagian'] ?? [] as $key => $value)
                <option value="{{ $key }}" {{ request('filter_bagian') == $key ? 'selected' : '' }}>{{ $value }}</option>
              @endforeach
            </select>
          </div>

          <div class="owner-docs-filter-group">
            <label>Vendor</label>
            <div class="owner-vendor-combo" data-vendor-combo>
              <input type="hidden" name="filter_vendor" value="{{ request('filter_vendor', '') }}" data-vendor-value>
              <div class="owner-vendor-control">
                <input type="text" class="owner-vendor-input" placeholder="Semua Vendor" autocomplete="off"
                  value="{{ request('filter_vendor', '') }}" data-vendor-display aria-label="Cari vendor">
                <button type="button" class="owner-vendor-clear" data-vendor-clear aria-label="Reset vendor" title="Reset">&times;</button>
              </div>
              <div class="owner-vendor-menu" data-vendor-menu hidden></div>
            </div>
          </div>

          <div class="owner-docs-filter-group">
            <label>Status Pembayaran</label>
            <select name="filter_status_pembayaran">
              <option value="">Semua Status</option>
              <option value="belum_dibayar" {{ request('filter_status_pembayaran') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
              <option value="siap_dibayar" {{ request('filter_status_pembayaran') == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
              <option value="sudah_dibayar" {{ request('filter_status_pembayaran') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
            </select>
          </div>

          <div class="owner-docs-filter-group">
            <label>Tanggal Masuk</label>
            <input type="date" name="filter_tanggal_masuk" value="{{ request('filter_tanggal_masuk') }}">
          </div>

          <div class="owner-docs-filter-group">
            <label>Nilai Min</label>
            <input class="owner-docs-money-input" type="text" inputmode="numeric" name="filter_nilai_min" value="{{ $formatFilterMoney(request('filter_nilai_min')) }}" placeholder="0">
          </div>

          <div class="owner-docs-filter-group">
            <label>Nilai Max</label>
            <input class="owner-docs-money-input" type="text" inputmode="numeric" name="filter_nilai_max" value="{{ $formatFilterMoney(request('filter_nilai_max')) }}" placeholder="999.999.999">
          </div>

          <div class="owner-docs-filter-group">
            <label>Durasi Peran</label>
            <select name="filter_durasi_min">
              <option value="">Semua Durasi</option>
              <option value="1" {{ request('filter_durasi_min') == '1' ? 'selected' : '' }}>Lebih dari 1 hari</option>
              <option value="3" {{ request('filter_durasi_min') == '3' ? 'selected' : '' }}>Lebih dari 3 hari</option>
              <option value="7" {{ request('filter_durasi_min') == '7' ? 'selected' : '' }}>Lebih dari 7 hari</option>
            </select>
          </div>

          <div class="owner-docs-filter-group">
            <label>Umur Dokumen</label>
            <select name="filter_umur_min">
              <option value="">Semua Umur</option>
              <option value="1" {{ request('filter_umur_min') == '1' ? 'selected' : '' }}>Lebih dari 1 hari</option>
              <option value="3" {{ request('filter_umur_min') == '3' ? 'selected' : '' }}>Lebih dari 3 hari</option>
              <option value="7" {{ request('filter_umur_min') == '7' ? 'selected' : '' }}>Lebih dari 7 hari</option>
              <option value="30" {{ request('filter_umur_min') == '30' ? 'selected' : '' }}>Lebih dari 30 hari</option>
              <option value="60" {{ request('filter_umur_min') == '60' ? 'selected' : '' }}>Lebih dari 60 hari</option>
              <option value="90" {{ request('filter_umur_min') == '90' ? 'selected' : '' }}>Lebih dari 90 hari</option>
              <option value="120" {{ request('filter_umur_min') == '120' ? 'selected' : '' }}>Lebih dari 120 hari</option>
            </select>
          </div>
        </div>

        <div class="owner-docs-filter-actions" style="margin-top:12px">
          <button type="submit" class="owner-docs-btn primary">
            <i class="fas fa-filter"></i>
            Terapkan Filter
          </button>
          <a class="owner-docs-btn" href="{{ url('/owner/dokumen') }}">
            <i class="fas fa-rotate-left"></i>
            Reset
          </a>
          @if(($activeFilterCount ?? 0) > 0)
            <span style="color:var(--od-muted);font-size:12px;font-weight:700">{{ $activeFilterCount }} filter aktif</span>
          @endif
        </div>
      </div>
    </form>

    <div class="owner-docs-table-card">
      <div class="owner-docs-table-wrap">
        <table class="owner-docs-table">
          <colgroup>
            <col class="owner-docs-col-doc">
            <col class="owner-docs-col-value">
            <col class="owner-docs-col-from">
            <col class="owner-docs-col-age">
            <col class="owner-docs-col-status">
            <col class="owner-docs-col-handler">
            <col class="owner-docs-col-duration">
            <col class="owner-docs-col-action">
          </colgroup>
          <thead>
            <tr>
              <th><span class="owner-docs-th-label">Nomor / Uraian SPP</span></th>
              <th><span class="owner-docs-th-label">Nilai</span></th>
              <th><span class="owner-docs-th-label">Dari</span></th>
              <th><span class="owner-docs-th-label">Umur Dokumen</span></th>
              <th><span class="owner-docs-th-label">Status Pembayaran</span></th>
              <th><span class="owner-docs-th-label">Pengurus Dokumen</span></th>
              <th><span class="owner-docs-th-label">Durasi Peran</span></th>
              <th><span class="owner-docs-th-label">Aksi</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($documents as $dokumen)
              @php
                $bagian = $dokumen['from_label'] ?? ($dokumen['bagian'] ?? '-');
                $bagianColor = $bagianColors[$bagian] ?? '#64748b';
                $umur = $dokumen['umur_dokumen'] ?? null;
                $umurText = is_array($umur) ? ($umur['text'] ?? '-') : '-';
                $umurPaid = is_array($umur) ? ($umur['is_paid'] ?? false) : false;
                $durasi = $dokumen['durasi_peran'] ?? ['text' => '-', 'class' => 'muted', 'since' => null, 'seconds' => 0];
                $isUrgent = ($dokumen['urgency_active'] ?? false) || (($durasi['seconds'] ?? 0) >= 259200 && !($dokumen['is_paid'] ?? false));
                $nomor = $dokumen['nomor_agenda'] ?: ($dokumen['nomor_spp'] ?: '-');
              @endphp
              <tr class="owner-docs-row" onclick="openOwnerDocModal({{ (int) $dokumen['id'] }})">
                <td>
                  <div class="owner-docs-docmeta">
                    <span class="owner-docs-dot" style="background:{{ $bagianColor }}"></span>
                    @if($isUrgent)
                      <span class="owner-docs-urgent"><i class="fas fa-bolt"></i> URGENT</span>
                    @endif
                    <span class="owner-docs-docno" title="{{ $nomor }}">{{ $nomor }}</span>
                  </div>
                  <div class="owner-docs-docname" title="{{ $dokumen['uraian_spp'] ?: '-' }}">{{ $dokumen['uraian_spp'] ?: '-' }}</div>
                  <div class="owner-docs-payee" title="{{ ($dokumen['vendor'] ?? '') ?: '-' }}">Dibayar kepada: {{ ($dokumen['vendor'] ?? '') ?: '-' }}</div>
                  <div class="owner-docs-payee">Tanggal masuk: {{ $dokumen['tanggal_masuk'] ?? '-' }}</div>
                </td>
                <td class="owner-docs-money" data-label="Nilai">
                  Rp {{ number_format((float)($dokumen['nilai_rupiah'] ?? 0), 0, ',', '.') }}
                </td>
                <td data-label="Dari">
                  <span class="owner-docs-bagian">
                    <span class="owner-docs-dot" style="background:{{ $bagianColor }}"></span>
                    <span>
                      {{ $bagian }}
                    </span>
                  </span>
                </td>
                <td data-label="Umur Dokumen">
                  <span class="owner-docs-age {{ $umurPaid ? 'paid' : '' }}">{{ $umurText }}</span>
                </td>
                <td data-label="Status Pembayaran">
                  <span class="owner-docs-status {{ $dokumen['status_pembayaran_class'] ?? 'waiting' }}">
                    {{ $dokumen['status_pembayaran_label'] ?? 'Belum Dibayar' }}
                  </span>
                </td>
                <td data-label="Pengurus Dokumen">
                  <div class="owner-docs-handler">
                    <span class="owner-docs-avatar">{{ strtoupper(substr($dokumen['current_handler_display'] ?? '-', 0, 1)) }}</span>
                    <span>
                      <span class="owner-docs-handler-name">{{ $dokumen['current_handler_display'] ?? '-' }}</span>
                      <span class="owner-docs-handler-sub" title="{{ $durasi['since'] ? 'sejak ' . $durasi['since'] : 'posisi aktif' }}">{{ $durasi['since'] ? 'sejak ' . $durasi['since'] : 'posisi aktif' }}</span>
                    </span>
                  </div>
                </td>
                <td data-label="Durasi Peran">
                  <span class="owner-docs-duration {{ $durasi['class'] ?? 'muted' }}">
                    <i class="far fa-clock"></i>
                    {{ $durasi['text'] ?? '-' }}
                  </span>
                </td>
                <td data-label="Aksi" class="owner-docs-action-cell">
                  <button type="button" class="owner-docs-wa-btn"
                    title="Kirim notifikasi prioritas via WhatsApp"
                    onclick="event.stopPropagation(); openPriorityWaModal({{ (int) $dokumen['id'] }})">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8">
                  <div class="owner-docs-empty">
                    <i class="far fa-folder-open"></i>
                    <strong>Tidak ada dokumen</strong>
                    <span>Ubah filter atau kata kunci pencarian untuk melihat dokumen lain.</span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($documents->count() > 0)
        @include('owner.partials.pagination-footer', ['paginator' => $documents])
      @endif
    </div>
  </div>
</div>

<div class="owner-docs-modal" id="ownerDocsModal" role="dialog" aria-modal="true" aria-labelledby="ownerDocModalTitle" onclick="if (event.target === this) closeOwnerDocModal()">
  <div class="owner-docs-modal-panel">
    <div class="owner-docs-modal-head">
      <button type="button" class="owner-docs-modal-close" onclick="closeOwnerDocModal()" aria-label="Tutup detail dokumen">
        <i class="fas fa-times"></i>
      </button>
      <div class="owner-docs-modal-no" id="ownerDocModalNo">-</div>
      <h3 class="owner-docs-modal-title" id="ownerDocModalTitle">-</h3>
      <div class="owner-docs-modal-sub">
        <span class="owner-docs-dot" id="ownerDocModalDot"></span>
        <span id="ownerDocModalMeta">-</span>
      </div>
    </div>
    <div class="owner-docs-modal-body">
      <div class="owner-docs-modal-timeline">
        <h4 class="owner-docs-modal-section">Timeline Workflow</h4>
        <div class="owner-docs-timeline-list" id="ownerDocModalTimeline"></div>
      </div>
      <div class="owner-docs-modal-detail">
        <h4 class="owner-docs-modal-section">Detail Dokumen</h4>
        <div id="ownerDocModalDetails"></div>
      </div>
    </div>
  </div>
</div>

<style>
  .owner-docs-col-action { width: 132px; }
  .owner-docs-action-cell { text-align: center; }
  .owner-docs-wa-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 12px; border: none; border-radius: 8px; cursor: pointer;
    background: #25d366; color: #fff; font-size: 12.5px; font-weight: 700;
    transition: background .15s ease, transform .1s ease;
  }
  .owner-docs-wa-btn:hover { background: #1ebe5b; }
  .owner-docs-wa-btn:active { transform: scale(.97); }
  .owner-docs-wa-btn i { font-size: 15px; }

  .pwa-modal {
    position: fixed; inset: 0; z-index: 1200; display: none;
    align-items: center; justify-content: center; padding: 16px;
    background: rgba(15, 23, 42, .55); backdrop-filter: blur(4px);
  }
  .pwa-modal.open { display: flex; }
  .pwa-panel {
    width: 100%; max-width: 460px; background: #fff; border-radius: 16px;
    box-shadow: 0 24px 60px rgba(0,0,0,.28); overflow: hidden;
    animation: pwaIn .18s ease;
  }
  @keyframes pwaIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
  .pwa-head {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 20px; background: #0f4c3a; color: #fff;
  }
  .pwa-head i { font-size: 20px; }
  .pwa-head h3 { margin: 0; font-size: 15px; font-weight: 800; flex: 1; }
  .pwa-close { background: transparent; border: none; color: #fff; cursor: pointer; font-size: 18px; opacity: .85; }
  .pwa-close:hover { opacity: 1; }
  .pwa-body { padding: 18px 20px; }
  .pwa-docref {
    font-size: 12.5px; color: #475569; background: #f1f5f9;
    border-radius: 8px; padding: 8px 12px; margin-bottom: 16px; word-break: break-word;
  }
  .pwa-docref strong { color: #0f172a; }
  .pwa-label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 8px; }
  .pwa-roles { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }
  .pwa-role-opt {
    display: flex; align-items: center; gap: 10px; cursor: pointer;
    border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 12px;
    font-size: 13px; color: #1e293b; transition: border-color .12s, background .12s;
  }
  .pwa-role-opt:hover { border-color: #cbd5e1; }
  .pwa-role-opt input { width: 16px; height: 16px; accent-color: #0f4c3a; }
  .pwa-role-opt.checked { border-color: #0f4c3a; background: #f0fdf4; }
  .pwa-textarea {
    width: 100%; min-height: 110px; resize: vertical; box-sizing: border-box;
    border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 12px;
    font-size: 13px; font-family: inherit; color: #1e293b; margin-bottom: 6px;
  }
  .pwa-textarea:focus { outline: none; border-color: #0f4c3a; }
  .pwa-hint { font-size: 11.5px; color: #94a3b8; margin: 0 0 18px; }
  .pwa-actions { display: flex; justify-content: flex-end; gap: 10px; }
  .pwa-btn {
    padding: 9px 18px; border-radius: 9px; border: none; cursor: pointer;
    font-size: 13px; font-weight: 700;
  }
  .pwa-btn-cancel { background: #f1f5f9; color: #475569; }
  .pwa-btn-cancel:hover { background: #e2e8f0; }
  .pwa-btn-send { background: #25d366; color: #fff; display: inline-flex; align-items: center; gap: 7px; }
  .pwa-btn-send:hover { background: #1ebe5b; }
  .pwa-btn-send:disabled { opacity: .65; cursor: not-allowed; }
</style>

<div class="pwa-modal" id="priorityWaModal" role="dialog" aria-modal="true" aria-labelledby="pwaTitle"
  onclick="if (event.target === this) closePriorityWaModal()">
  <div class="pwa-panel">
    <div class="pwa-head">
      <i class="fab fa-whatsapp"></i>
      <h3 id="pwaTitle">Kirim Notifikasi Prioritas</h3>
      <button type="button" class="pwa-close" onclick="closePriorityWaModal()" aria-label="Tutup">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="pwa-body">
      <div class="pwa-docref" id="pwaDocRef">-</div>

      <span class="pwa-label">Kirim ke role:</span>
      <div class="pwa-roles" id="pwaRoles">
        <label class="pwa-role-opt">
          <input type="checkbox" value="team_verifikasi"> <span>Team Verifikasi</span>
        </label>
        <label class="pwa-role-opt">
          <input type="checkbox" value="perpajakan"> <span>Perpajakan</span>
        </label>
        <label class="pwa-role-opt">
          <input type="checkbox" value="akutansi"> <span>Akutansi</span>
        </label>
      </div>

      <span class="pwa-label">Pesan:</span>
      <textarea class="pwa-textarea" id="pwaMessage"
        placeholder="Tulis pesan untuk menuntut role agar segera memprioritaskan dokumen ini..."></textarea>
      <p class="pwa-hint">Detail dokumen (no. agenda, nilai, bagian) otomatis disertakan di pesan.</p>

      <div class="pwa-actions">
        <button type="button" class="pwa-btn pwa-btn-cancel" onclick="closePriorityWaModal()">Batal</button>
        <button type="button" class="pwa-btn pwa-btn-send" id="pwaSendBtn" onclick="submitPriorityWa()">
          <i class="fab fa-whatsapp"></i> <span>Kirim</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  const ownerDocs = new Map((@json($documents->items())).map((doc) => [Number(doc.id), doc]));
  const ownerBagianColors = @json($bagianColors);

  function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
  }

  function escapeOwnerHtml(value) {
    return String(value ?? '-')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatOwnerRupiah(value) {
    const amount = Number(value || 0);
    return 'Rp ' + amount.toLocaleString('id-ID');
  }

  function formatOwnerDots(value) {
    const digits = String(value || '').replace(/\D/g, '');
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function renderOwnerTimeline(steps) {
    const timeline = document.getElementById('ownerDocModalTimeline');
    if (!timeline) return;

    const safeSteps = Array.isArray(steps) && steps.length ? steps : [
      { label: 'Bagian', status: 'active', received_at: null },
      { label: 'Operator', status: 'pending', received_at: null },
      { label: 'Team Verifikasi', status: 'pending', received_at: null },
      { label: 'Perpajakan', status: 'pending', received_at: null },
      { label: 'Akutansi', status: 'pending', received_at: null },
      { label: 'Pembayaran', status: 'pending', received_at: null },
    ];

    timeline.innerHTML = safeSteps.map((step, index) => {
      const isDone = step.status === 'completed';
      const isActive = step.status === 'active' || step.status === 'waiting' || step.is_current;
      const date = step.processed_at || step.received_at || step.sent_at || '-';
      const dot = isDone ? '<i class="fas fa-check"></i>' : index + 1;
      const classes = ['owner-docs-timeline-item', isDone ? 'done' : '', isActive ? 'active' : '']
        .filter(Boolean)
        .join(' ');

      return `
        <div class="${classes}">
          <span class="owner-docs-timeline-dot">${dot}</span>
          <div>
            <div class="owner-docs-timeline-name">${escapeOwnerHtml(step.label || '-')}</div>
            <div class="owner-docs-timeline-date">${escapeOwnerHtml(date)}</div>
          </div>
        </div>
      `;
    }).join('');
  }

  function renderOwnerDetails(doc) {
    const detail = document.getElementById('ownerDocModalDetails');
    if (!detail) return;

    const fields = [
      ['Pengurus Dokumen Saat Ini', doc.current_handler_display || '-'],
      ['Nilai Rupiah', formatOwnerRupiah(doc.nilai_rupiah), true],
      ['Kategori', doc.kategori || '-'],
      ['Sub Kriteria', doc.jenis_dokumen || '-'],
      ['Item Sub Kriteria', doc.jenis_sub_pekerjaan || '-'],
      ['Jenis Pembayaran', doc.jenis_pembayaran || '-'],
    ];

    const fullDescription = `
      <div class="owner-docs-detail-field full">
        <div class="owner-docs-detail-label">Uraian Dokumen</div>
        <div class="owner-docs-detail-value long-text">${escapeOwnerHtml(doc.uraian_spp || '-')}</div>
      </div>
    `;

    detail.innerHTML = fullDescription + fields.map(([label, value, mono]) => `
      <div class="owner-docs-detail-field">
        <div class="owner-docs-detail-label">${escapeOwnerHtml(label)}</div>
        <div class="owner-docs-detail-value ${mono ? 'mono' : ''}">${escapeOwnerHtml(value)}</div>
      </div>
    `).join('');
  }

  function openOwnerDocModal(id) {
    const doc = ownerDocs.get(Number(id));
    const modal = document.getElementById('ownerDocsModal');
    if (!doc || !modal) return;

    const bagian = doc.from_label || doc.bagian || '-';
    const bagianColor = ownerBagianColors[bagian] || '#10b981';

    document.getElementById('ownerDocModalNo').textContent = doc.nomor_agenda || doc.nomor_spp || '-';
    const modalTitle = document.getElementById('ownerDocModalTitle');
    modalTitle.textContent = doc.uraian_spp || '-';
    modalTitle.title = doc.uraian_spp || '-';
    document.getElementById('ownerDocModalDot').style.background = bagianColor;
    const modalMeta = document.getElementById('ownerDocModalMeta');
    modalMeta.textContent = 'Bagian ' + bagian + ' - Dibayar kepada ' + (doc.vendor || '-');
    modalMeta.title = modalMeta.textContent;

    renderOwnerTimeline(doc.workflow_timeline?.steps || []);
    renderOwnerDetails(doc);

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeOwnerDocModal() {
    const modal = document.getElementById('ownerDocsModal');
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }

  // ===== Notifikasi WhatsApp Prioritas (owner -> role) =====
  let pwaCurrentDocId = null;
  const pwaUrlBase = "{{ url('owner/dokumen') }}";

  function openPriorityWaModal(id) {
    const doc = ownerDocs.get(Number(id));
    const modal = document.getElementById('priorityWaModal');
    if (!modal) return;

    pwaCurrentDocId = Number(id);

    const nomor = doc ? (doc.nomor_agenda || doc.nomor_spp || '-') : '-';
    const uraian = doc ? (doc.uraian_spp || '-') : '-';
    document.getElementById('pwaDocRef').innerHTML =
      '<strong>' + escapeOwnerHtml(nomor) + '</strong><br>' + escapeOwnerHtml(uraian);

    // reset form
    modal.querySelectorAll('#pwaRoles input[type="checkbox"]').forEach((cb) => {
      cb.checked = false;
      cb.closest('.pwa-role-opt').classList.remove('checked');
    });
    document.getElementById('pwaMessage').value = '';

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closePriorityWaModal() {
    const modal = document.getElementById('priorityWaModal');
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
    pwaCurrentDocId = null;
  }

  // visual toggle saat checkbox role dipilih
  document.querySelectorAll('#pwaRoles input[type="checkbox"]').forEach((cb) => {
    cb.addEventListener('change', () => {
      cb.closest('.pwa-role-opt').classList.toggle('checked', cb.checked);
    });
  });

  // Tampilkan toast (pakai toast global layout jika tersedia, fallback ke alert)
  function pwaToast(type, title, message) {
    if (typeof showGlobalToastNotification === 'function') {
      showGlobalToastNotification(type, title, message);
    } else {
      alert(message);
    }
  }

  function submitPriorityWa() {
    if (!pwaCurrentDocId) return;

    const roles = Array.from(
      document.querySelectorAll('#pwaRoles input[type="checkbox"]:checked')
    ).map((cb) => cb.value);

    const message = document.getElementById('pwaMessage').value.trim();

    if (roles.length === 0) {
      pwaToast('warning', 'Belum lengkap', 'Pilih minimal satu role tujuan.');
      return;
    }
    if (message === '') {
      pwaToast('warning', 'Belum lengkap', 'Pesan tidak boleh kosong.');
      return;
    }

    const btn = document.getElementById('pwaSendBtn');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Mengirim...</span>';

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(pwaUrlBase + '/' + pwaCurrentDocId + '/priority-whatsapp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ roles, message }),
    })
      .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
      })
      .then(({ ok, data }) => {
        if (ok && data.success) {
          closePriorityWaModal();
          pwaToast('success', 'Notifikasi terkirim', data.message || 'Notifikasi berhasil dikirim.');
        } else {
          pwaToast('error', 'Gagal mengirim', data.message || 'Gagal mengirim notifikasi. Silakan coba lagi.');
        }
      })
      .catch(() => {
        pwaToast('error', 'Gagal mengirim', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = original;
      });
  }

  document.querySelectorAll('.owner-docs-money-input').forEach((input) => {
    input.value = formatOwnerDots(input.value);
    input.addEventListener('input', () => {
      const cursorAtEnd = input.selectionStart === input.value.length;
      input.value = formatOwnerDots(input.value);
      if (cursorAtEnd) {
        input.setSelectionRange(input.value.length, input.value.length);
      }
    });
  });

  document.getElementById('ownerDocsFilterForm')?.addEventListener('submit', function() {
    this.querySelectorAll('.owner-docs-money-input').forEach((input) => {
      input.value = String(input.value || '').replace(/\D/g, '');
    });
  });

  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeOwnerDocModal();
    }

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      const input = document.querySelector('.owner-docs-search input');
      if (input) {
        event.preventDefault();
        input.focus();
        input.select();
      }
    }
  });

  // Hold-click-and-drag to pan the documents table horizontally/vertically.
  (function () {
    const wrap = document.querySelector('.owner-docs-table-wrap');
    if (!wrap) return;

    const DRAG_THRESHOLD = 5; // px before a press counts as a drag, not a click
    let isDown = false;
    let didDrag = false;
    let startX = 0, startY = 0, startScrollLeft = 0, startScrollTop = 0;

    wrap.addEventListener('mousedown', function (e) {
      if (e.button !== 0) return; // left button only
      // Don't hijack interactive controls inside the table.
      if (e.target.closest('a, button, input, select, textarea')) return;
      isDown = true;
      didDrag = false;
      startX = e.pageX;
      startY = e.pageY;
      startScrollLeft = wrap.scrollLeft;
      startScrollTop = wrap.scrollTop;
    });

    window.addEventListener('mousemove', function (e) {
      if (!isDown) return;
      const dx = e.pageX - startX;
      const dy = e.pageY - startY;
      if (!didDrag && (Math.abs(dx) > DRAG_THRESHOLD || Math.abs(dy) > DRAG_THRESHOLD)) {
        didDrag = true;
        wrap.classList.add('is-dragging');
      }
      if (didDrag) {
        e.preventDefault();
        wrap.scrollLeft = startScrollLeft - dx;
        wrap.scrollTop = startScrollTop - dy;
      }
    });

    window.addEventListener('mouseup', function () {
      isDown = false;
      wrap.classList.remove('is-dragging');
    });

    // Swallow the click that follows a drag so rows don't open the modal.
    wrap.addEventListener('click', function (e) {
      if (didDrag) {
        e.stopPropagation();
        e.preventDefault();
        didDrag = false;
      }
    }, true);
  })();

  // Searchable vendor filter (replaces the oversized native dropdown).
  (function () {
    const combo = document.querySelector('[data-vendor-combo]');
    if (!combo) return;

    const vendors = @json(array_values($filterData['vendor'] ?? []));
    const hidden = combo.querySelector('[data-vendor-value]');
    const display = combo.querySelector('[data-vendor-display]');
    const menu = combo.querySelector('[data-vendor-menu]');
    const clearBtn = combo.querySelector('[data-vendor-clear]');
    const MAX_RESULTS = 50;

    function syncHasValue() {
      combo.classList.toggle('has-value', (hidden.value || '').trim() !== '');
    }

    function renderMenu(query) {
      const q = (query || '').trim().toLowerCase();
      const matches = q
        ? vendors.filter((v) => String(v).toLowerCase().includes(q))
        : vendors;
      const shown = matches.slice(0, MAX_RESULTS);

      let html = '<div class="owner-vendor-opt" data-value="">Semua Vendor</div>';
      html += shown.map((v) => {
        const esc = String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        return `<div class="owner-vendor-opt" data-value="${esc}" title="${esc}">${esc}</div>`;
      }).join('');

      if (q && shown.length === 0) {
        html += '<div class="owner-vendor-empty">Vendor tidak ditemukan</div>';
      } else if (matches.length > shown.length) {
        html += `<div class="owner-vendor-empty">+${matches.length - shown.length} vendor lain, ketik untuk mempersempit</div>`;
      }
      menu.innerHTML = html;
    }

    function openMenu() {
      renderMenu(display.value === hidden.value ? '' : display.value);
      menu.hidden = false;
    }

    function closeMenu() {
      menu.hidden = true;
      // Always reflect the committed value in the text field.
      display.value = hidden.value;
    }

    function selectValue(value) {
      hidden.value = value;
      display.value = value;
      syncHasValue();
      menu.hidden = true;
    }

    display.addEventListener('focus', openMenu);
    display.addEventListener('input', function () {
      menu.hidden = false;
      renderMenu(display.value);
    });

    menu.addEventListener('mousedown', function (event) {
      const opt = event.target.closest('.owner-vendor-opt');
      if (!opt) return;
      event.preventDefault();
      selectValue(opt.getAttribute('data-value') || '');
    });

    clearBtn.addEventListener('click', function () {
      selectValue('');
      display.focus();
    });

    document.addEventListener('click', function (event) {
      if (!combo.contains(event.target)) closeMenu();
    });

    display.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') { closeMenu(); display.blur(); }
    });

    syncHasValue();
  })();
</script>
@endsection

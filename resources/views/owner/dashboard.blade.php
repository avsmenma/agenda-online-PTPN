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
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 20px;
  }

  .owner-docs-stat {
    background: var(--od-card);
    border: 1px solid var(--od-border);
    border-radius: 14px;
    padding: 18px 18px 14px;
    box-shadow: var(--od-shadow);
    position: relative;
    min-height: 132px;
    overflow: hidden;
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

  .owner-docs-tabs {
    background: #fff;
    border: 1px solid var(--od-border);
    border-radius: 14px;
    padding: 6px;
    box-shadow: var(--od-shadow);
    display: inline-flex;
    gap: 4px;
    margin-bottom: 14px;
    max-width: 100%;
    overflow-x: auto;
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
    grid-template-columns: repeat(7, minmax(135px, 1fr));
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
  }

  .owner-docs-table {
    width: 100%;
    min-width: 1160px;
    border-collapse: collapse;
  }

  .owner-docs-table thead {
    background: #f8fafc;
  }

  .owner-docs-table th {
    padding: 16px 14px;
    color: var(--od-muted);
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    text-align: left;
    border-bottom: 1px solid var(--od-border);
    white-space: nowrap;
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
  }

  .owner-docs-docno {
    font-family: 'Sora', monospace;
    color: #475569;
    font-size: 11.5px;
    font-weight: 700;
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
  }

  .owner-docs-payee {
    color: var(--od-muted);
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
    text-align: left;
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
    white-space: nowrap;
  }

  .owner-docs-handler-sub {
    display: block;
    color: var(--od-muted);
    font-size: 10.5px;
    margin-top: 2px;
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
    color: #475569;
    font-family: 'Sora', monospace;
    font-size: 11.5px;
    font-weight: 800;
    white-space: nowrap;
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
    padding: 26px 34px 28px;
    background: linear-gradient(135deg, #14513f 0%, #0f3d2e 100%);
    color: #fff;
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
    font-size: 24px;
    font-weight: 800;
    line-height: 1.25;
    max-width: 760px;
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
  }

  .owner-docs-detail-value.mono {
    font-family: 'Sora', monospace;
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
      <div class="owner-docs-stat">
        <div class="owner-docs-stat-label">Total Dokumen</div>
        <div class="owner-docs-stat-icon" style="background:#f0f4ff;color:#2563eb">
          <i class="far fa-file-alt"></i>
        </div>
        <div class="owner-docs-stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub">Total seluruh dokumen</div>
      </div>

      <div class="owner-docs-stat">
        <div class="owner-docs-stat-label">Belum Dibayar</div>
        <div class="owner-docs-stat-icon" style="background:#fffbeb;color:#f59e0b">
          <i class="far fa-clock"></i>
        </div>
        <div class="owner-docs-stat-value" style="color:#f59e0b">{{ number_format($dokumenProses ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub" style="font-weight:700;color:#f59e0b">Rp {{ number_format($nilaiBelumDibayar ?? 0, 0, ',', '.') }}</div>
      </div>

      <div class="owner-docs-stat">
        <div class="owner-docs-stat-label">Siap Dibayar</div>
        <div class="owner-docs-stat-icon" style="background:#eff4ff;color:#2563eb">
          <i class="fas fa-check"></i>
        </div>
        <div class="owner-docs-stat-value" style="color:#2563eb">{{ number_format($dokumenSiapBayar ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub">Rp {{ number_format($nilaiSiapDibayar ?? 0, 0, ',', '.') }}</div>
      </div>

      <div class="owner-docs-stat">
        <div class="owner-docs-stat-label">Sudah Dibayar</div>
        <div class="owner-docs-stat-icon" style="background:#ecfdf5;color:#10b981">
          <i class="far fa-credit-card"></i>
        </div>
        <div class="owner-docs-stat-value" style="color:#10b981">{{ number_format($dokumenSelesai ?? 0, 0, ',', '.') }}</div>
        <div class="owner-docs-stat-sub" style="font-weight:700;color:#10b981">Rp {{ number_format($nilaiSudahDibayar ?? 0, 0, ',', '.') }}</div>
      </div>

      <div class="owner-docs-stat accent">
        <div class="owner-docs-stat-label">Total Nilai</div>
        <div class="owner-docs-stat-value">{{ $totalNilaiShort }}</div>
        <div class="owner-docs-stat-sub">Rp {{ number_format($totalNilaiNum, 0, ',', '.') }}</div>
      </div>
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
            <label>Pengurus</label>
            <select name="filter_pengurus">
              <option value="">Semua Pengurus</option>
              @foreach($filterData['pengurus'] ?? [] as $key => $value)
                <option value="{{ $key }}" {{ request('filter_pengurus') == $key ? 'selected' : '' }}>{{ $value }}</option>
              @endforeach
            </select>
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
          <thead>
            <tr>
              <th style="width:34%;text-align:center">Nomor / Uraian SPP</th>
              <th style="text-align:center">Nilai</th>
              <th>Dari</th>
              <th>Pengurus Dokumen</th>
              <th>Status Pembayaran</th>
              <th>Durasi Peran</th>
              <th>Umur Dokumen</th>
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
                    <span class="owner-docs-docno">{{ $nomor }}</span>
                  </div>
                  <div class="owner-docs-docname">{{ $dokumen['uraian_spp'] ?: '-' }}</div>
                  <div class="owner-docs-payee">Dibayar kepada: {{ $dokumen['vendor'] ?? '-' }}</div>
                </td>
                <td class="owner-docs-money">
                  Rp {{ number_format((float)($dokumen['nilai_rupiah'] ?? 0), 0, ',', '.') }}
                </td>
                <td>
                  <span class="owner-docs-bagian">
                    <span class="owner-docs-dot" style="background:{{ $bagianColor }}"></span>
                    <span>
                      {{ $bagian }}
                    </span>
                  </span>
                </td>
                <td>
                  <div class="owner-docs-handler">
                    <span class="owner-docs-avatar">{{ strtoupper(substr($dokumen['current_handler_display'] ?? '-', 0, 1)) }}</span>
                    <span>
                      <span class="owner-docs-handler-name">{{ $dokumen['current_handler_display'] ?? '-' }}</span>
                      <span class="owner-docs-handler-sub">{{ $durasi['since'] ? 'sejak ' . $durasi['since'] : 'posisi aktif' }}</span>
                    </span>
                  </div>
                </td>
                <td>
                  <span class="owner-docs-status {{ $dokumen['status_pembayaran_class'] ?? 'waiting' }}">
                    {{ $dokumen['status_pembayaran_label'] ?? 'Belum Dibayar' }}
                  </span>
                </td>
                <td>
                  <span class="owner-docs-duration {{ $durasi['class'] ?? 'muted' }}">
                    <i class="far fa-clock"></i>
                    {{ $durasi['text'] ?? '-' }}
                  </span>
                </td>
                <td>
                  <span class="owner-docs-age {{ $umurPaid ? 'paid' : '' }}">{{ $umurText }}</span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7">
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

    detail.innerHTML = fields.map(([label, value, mono]) => `
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
    document.getElementById('ownerDocModalTitle').textContent = doc.uraian_spp || '-';
    document.getElementById('ownerDocModalDot').style.background = bagianColor;
    document.getElementById('ownerDocModalMeta').textContent = 'Bagian ' + bagian + ' - Dibayar kepada ' + (doc.vendor || '-');

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
</script>
@endsection

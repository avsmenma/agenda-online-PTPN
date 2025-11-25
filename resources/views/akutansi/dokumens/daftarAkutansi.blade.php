@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 20px;
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
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
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
  }

  .filter-section select:focus,
  .filter-section input:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-filter {
    padding: 10px 24px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 62, 64, 0.3);
  }

  .btn-customize-columns-inline {
    padding: 10px 20px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(136, 151, 23, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    white-space: nowrap;
  }

  .btn-customize-columns-inline:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.3);
    background: linear-gradient(135deg, #9ab01f 0%, #a8bf23 100%);
    color: white;
  }

  .btn-customize-columns-inline:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(136, 151, 23, 0.2);
  }

  /* Table Container - Enhanced Horizontal Scroll from perpajakan */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
    position: relative;
    overflow: hidden;
  }

  /* Horizontal Scroll Container - Enhanced from perpajakan */
  .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(26, 77, 62, 0.3) transparent;
  }

  .table-responsive::-webkit-scrollbar {
    height: 12px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: rgba(26, 77, 62, 0.05);
    border-radius: 6px;
    margin: 0 20px;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(26, 77, 62, 0.3), rgba(15, 61, 46, 0.4));
    border-radius: 6px;
    border: 2px solid rgba(255, 255, 255, 0.8);
  }

  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, rgba(26, 77, 62, 0.5), rgba(15, 61, 46, 0.6));
  }

  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1600px; /* Minimum width for horizontal scroll with all columns */
    width: 100%;
  }

  .table-enhanced thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid #1a4d3e;
    padding: 18px 16px;
    font-size: 13px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border: none;
    white-space: nowrap;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  /* Column Widths - Enhanced from perpajakan */
  .table-enhanced .col-no {
    width: 60px;
    min-width: 60px;
    text-align: center;
    font-weight: 600;
  }
  .table-enhanced .col-agenda {
    width: 150px;
    min-width: 150px;
    text-align: center;
  }
  .table-enhanced .col-tanggal {
    width: 140px;
    min-width: 140px;
    text-align: center;
  }
  .table-enhanced .col-spp {
    width: 160px;
    min-width: 160px;
    text-align: center;
  }
  .table-enhanced .col-nilai {
    width: 150px;
    min-width: 150px;
    text-align: center;
  }
  .table-enhanced .col-tanggal-spp {
    width: 140px;
    min-width: 140px;
    text-align: center;
  }
  .table-enhanced .col-uraian {
    width: 300px;
    min-width: 300px;
    max-width: 300px;
    text-align: left;
  }
  .table-enhanced .col-deadline {
    width: 180px;
    min-width: 180px;
    text-align: center;
  }
  .table-enhanced .col-status {
    width: 160px;
    min-width: 160px;
    text-align: center;
  }
  .table-enhanced .col-action {
    width: 180px;
    min-width: 180px;
    text-align: center;
  }

  .table-enhanced tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(26, 77, 62, 0.08);
    position: relative;
    border-left: 3px solid transparent;
  }

  /* Enhanced Locked Row Styling from perpajakan */
  .table-enhanced tbody tr.locked-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    opacity: 0.85;
    position: relative;
    border-left: 4px solid #ffc107 !important;
  }

  .table-enhanced tbody tr.locked-row::before {
    content: '🔒';
    position: absolute;
    top: 50%;
    left: -2px;
    transform: translateY(-50%);
    font-size: 16px;
    z-index: 2;
    opacity: 0.7;
  }

  .table-enhanced tbody tr.locked-row:hover {
    background: linear-gradient(135deg, #fff8e1 0%, #fff3c4 100%);
    border-left: 4px solid #ffc107 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15);
  }

  /* Regular row hover effect */
  .table-enhanced tbody tr:hover {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
    transform: scale(1.002);
  }

  /* Selected row styling */
  .table-enhanced tbody tr.selected {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
  }

  .table-enhanced tbody td {
    padding: 16px;
    vertical-align: middle;
    border-right: 1px solid rgba(26, 77, 62, 0.05);
    font-size: 13px;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
    text-align: center;
    font-weight: 400;
    color: #374151;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  /* Custom centering for specific column content */
  .table-enhanced .col-no,
  .table-enhanced .col-agenda,
  .table-enhanced .col-nilai,
  .table-enhanced .col-tanggal-spp,
  .table-enhanced .col-status,
  .table-enhanced .col-deadline,
  .table-enhanced .col-action {
    text-align: center;
  }

  .table-enhanced .col-tanggal {
    text-align: center;
    font-weight: 600;
  }

  .table-enhanced .col-spp {
    text-align: center;
    font-weight: 600;
  }

  .table-enhanced .col-uraian {
    text-align: left;
    font-weight: 600;
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.4;
    max-width: 300px;
    width: 300px;
    hyphens: auto;
    overflow-wrap: break-word;
  }

  /* Override uraian column header to center */
  .table-enhanced thead th.col-uraian {
    text-align: center;
  }

  /* Special styling for centered content */
  .table-enhanced td[colspan] {
    text-align: left;
  }

  /* Center agenda content properly */
  .table-enhanced td.col-agenda > strong,
  .table-enhanced td.col-agenda > small {
    display: block;
    text-align: center;
  }

  /* Center deadline content */
  .table-enhanced td.col-deadline > small,
  .table-enhanced td.col-deadline > span {
    display: block;
    text-align: center;
  }

  /* Deadline card design matching perpajakan style */
  .deadline-card {
    position: relative;
    background: white;
    border-radius: 12px;
    padding: 10px 12px;
    border: 2px solid transparent;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    margin: 0 auto;
    max-width: 150px;
  }

  .deadline-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
    transition: height 0.3s ease;
  }

  .deadline-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    border-color: var(--deadline-color);
  }

  .deadline-card:hover::before {
    height: 5px;
  }

  .deadline-time {
    font-size: 11px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }

  .deadline-time i {
    font-size: 10px;
    color: var(--deadline-color);
  }

  .deadline-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 5px 12px;
    border-radius: 20px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .deadline-indicator::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
  }

  .deadline-card:hover .deadline-indicator::before {
    left: 100%;
  }

  /* Safe State - Green Theme (>= 1 hari) */
  .deadline-card.deadline-safe {
    --deadline-color: #10b981;
    --deadline-color-light: #34d399;
    --deadline-bg: #ecfdf5;
    --deadline-text: #065f46;
  }

  .deadline-card.deadline-safe {
    background: var(--deadline-bg);
    border-color: rgba(16, 185, 129, 0.2);
  }

  .deadline-card.deadline-safe .deadline-time {
    color: var(--deadline-text);
  }

  .deadline-indicator.deadline-safe {
    background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
    color: white;
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.4);
  }

  .deadline-indicator.deadline-safe i::before {
    content: "\f058"; /* check-circle */
  }

  /* Warning State - Yellow Theme (< 1 hari) */
  .deadline-card.deadline-warning {
    --deadline-color: #f59e0b;
    --deadline-color-light: #fbbf24;
    --deadline-bg: #fffbeb;
    --deadline-text: #92400e;
  }

  .deadline-card.deadline-warning {
    background: var(--deadline-bg);
    border-color: rgba(245, 158, 11, 0.2);
  }

  .deadline-card.deadline-warning .deadline-time {
    color: var(--deadline-text);
  }

  .deadline-indicator.deadline-warning {
    background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
    color: white;
    box-shadow: 0 3px 10px rgba(245, 158, 11, 0.4);
  }

  .deadline-indicator.deadline-warning i::before {
    content: "\f071"; /* exclamation-triangle */
  }

  /* Overdue State - Red Theme (Terlambat) */
  .deadline-card.deadline-overdue {
    --deadline-color: #dc2626;
    --deadline-color-light: #ef4444;
    --deadline-bg: #fef2f2;
    --deadline-text: #991b1b;
  }

  .deadline-card.deadline-overdue {
    background: var(--deadline-bg);
    border-color: rgba(220, 38, 38, 0.3);
    animation: overdue-alert 3s infinite;
  }

  .deadline-card.deadline-overdue .deadline-time {
    color: var(--deadline-text);
    font-weight: 800;
  }

  .deadline-indicator.deadline-overdue {
    background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
    color: white;
    box-shadow: 0 4px 16px rgba(220, 38, 68, 0.5);
    font-weight: 800;
    animation: overdue-glow 1.5s infinite;
  }

  .deadline-indicator.deadline-overdue i::before {
    content: "\f071"; /* exclamation-triangle */
    animation: warning-shake 1s infinite;
  }

  /* Enhanced late information */
  .late-info {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    font-weight: 700;
    margin-top: 8px;
    padding: 6px 10px;
    border-radius: 20px;
    background: linear-gradient(135deg, rgba(220, 38, 68, 0.1) 0%, rgba(239, 68, 68, 0.15) 100%);
    border: 1px solid rgba(220, 38, 68, 0.3);
    color: #991b1b;
    animation: late-warning 2s infinite;
  }

  .late-info i {
    font-size: 11px;
    color: #dc2626;
  }

  .late-info .late-text {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .deadline-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg,
      var(--deadline-color) 0%,
      var(--deadline-color-light) 50%,
      var(--deadline-color) 100%);
    border-radius: 0 0 10px 10px;
    transform-origin: left;
    transition: transform 0.5s ease;
  }

  .deadline-note {
    font-size: 9px;
    color: #6b7280;
    font-style: italic;
    margin-top: 4px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
  }

  .no-deadline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #9ca3af;
    font-size: 11px;
    font-style: italic;
    padding: 8px 12px;
    border-radius: 20px;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    transition: all 0.3s ease;
  }

  .no-deadline:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
  }

  .no-deadline i {
    font-size: 11px;
    opacity: 0.7;
  }

  @keyframes overdue-alert {
    0%, 85%, 100% {
      border-color: rgba(220, 38, 38, 0.3);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    90%, 95% {
      border-color: rgba(220, 38, 38, 0.8);
      box-shadow: 0 0 16px rgba(220, 38, 38, 0.4);
    }
  }

  @keyframes overdue-glow {
    0%, 100% {
      box-shadow: 0 4px 16px rgba(220, 38, 68, 0.5);
      transform: translateY(0);
    }
    50% {
      box-shadow: 0 6px 24px rgba(220, 38, 68, 0.7);
      transform: translateY(-1px);
    }
  }

  @keyframes late-warning {
    0%, 100% {
      background: linear-gradient(135deg, rgba(220, 38, 68, 0.1) 0%, rgba(239, 68, 68, 0.15) 100%);
      transform: scale(1);
    }
    50% {
      background: linear-gradient(135deg, rgba(220, 38, 68, 0.15) 0%, rgba(239, 68, 68, 0.25) 100%);
      transform: scale(1.02);
    }
  }

  @keyframes warning-shake {
    0%, 100% { transform: translateX(0) rotate(0deg); }
    25% { transform: translateX(-1px) rotate(-1deg); }
    75% { transform: translateX(1px) rotate(1deg); }
  }

  /* Mobile responsive */
  @media (max-width: 768px) {
    .deadline-card {
      padding: 8px 10px;
      max-width: 130px;
    }

    .deadline-time {
      font-size: 10px;
    }

    .deadline-indicator {
      font-size: 9px;
      padding: 4px 10px;
    }

    .late-info {
      font-size: 9px;
      padding: 4px 8px;
      margin-top: 6px;
    }

    .deadline-note {
      font-size: 8px;
    }
  }

  @media (max-width: 576px) {
    .deadline-card {
      padding: 6px 8px;
      max-width: 120px;
    }

    .deadline-time {
      font-size: 9px;
    }

    .deadline-indicator {
      font-size: 8px;
      padding: 3px 8px;
    }

    .deadline-note {
      font-size: 7px;
    }

    .no-deadline {
      font-size: 9px;
      padding: 6px 10px;
    }

    .late-info {
      font-size: 8px;
      padding: 3px 6px;
    }
  }

  /* Enhanced Badge Styles matching perpajakan */
  .badge-status {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: none;
    text-align: center;
    min-width: 100px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.3s ease;
    white-space: nowrap;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  .badge-status.badge-locked {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
  }

  .badge-status.badge-proses {
    background: linear-gradient(135deg, #2d6a4f 0%, #1b5e3f 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(45, 106, 79, 0.3);
  }

  .badge-status.badge-selesai {
    background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(64, 145, 108, 0.3);
  }

  .badge-status.badge-belum {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
  }

  .badge-status.badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
  }

  .badge-status.badge-locked {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  .badge-status:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  /* Enhanced Action Buttons matching perpajakan */
  .action-buttons {
    display: flex;
    flex-direction: column;
    gap: 6px;
    justify-content: center;
    align-items: center;
    width: 100%;
  }

  .action-row {
    display: flex;
    gap: 6px;
    justify-content: center;
    align-items: center;
    width: 100%;
  }

  .btn-action {
    min-width: 44px;
    min-height: 44px;
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
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
    flex: 1;
    max-width: 140px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  .btn-action span {
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }

  .btn-edit {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
  }

  .btn-edit:hover {
    background: linear-gradient(135deg, #0f3d2e 0%, #0a2e1f 100%);
    color: white;
  }

  .btn-detail {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
  }

  .btn-detail:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
    color: white;
  }

  .btn-send {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    position: relative;
    overflow: hidden;
  }

  .btn-send::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
  }

  .btn-send:hover::before {
    left: 100%;
  }

  .btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.4);
    border-color: rgba(136, 151, 23, 0.6);
  }

  .btn-send i {
    transition: transform 0.3s ease;
  }

  .btn-send:hover i {
    transform: translateX(2px);
  }

  .btn-send:disabled {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    cursor: not-allowed;
    opacity: 0.6;
  }

  .btn-send:disabled:hover {
    transform: none;
    box-shadow: none;
  }

  .btn-action.locked {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
    cursor: not-allowed;
    opacity: 0.7;
  }

  .btn-action.locked:hover {
    transform: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .btn-set-deadline {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%) !important;
    color: white;
    border: 2px solid rgba(255, 193, 7, 0.3);
    position: relative;
    overflow: hidden;
  }

  .btn-set-deadline::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    animation: shimmer 2s infinite;
  }

  .btn-set-deadline:hover {
    background: linear-gradient(135deg, #ff8c00 0%, #e67300 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
    border-color: rgba(255, 193, 7, 0.6);
  }

  .btn-set-deadline i {
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
  }

  @keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
  }

  @keyframes fadeInOut {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 1; }
  }

  /* Enhanced Table Organization */
  .table-container-header {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 12px 12px 0 0;
    margin: -30px -30px 20px -30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .table-container-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .table-container-stats {
    display: flex;
    gap: 20px;
    align-items: center;
  }

  .stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
  }

  .stat-value {
    font-size: 16px;
    font-weight: 700;
  }

  .stat-label {
    font-size: 10px;
    opacity: 0.8;
    text-transform: uppercase;
  }

  /* Enhanced Row Separation */
  .table-enhanced tbody tr:not(:last-child)::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 16px;
    right: 16px;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(26, 77, 62, 0.1) 20%, rgba(26, 77, 62, 0.1) 80%, transparent 100%);
  }

  /* Responsive Design - Mobile Optimization */
  @media (max-width: 768px) {
    .table-dokumen {
      padding: 15px;
      border-radius: 12px;
    }

    .table-enhanced thead th {
      padding: 14px 8px;
      font-size: 11px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .table-enhanced td {
      padding: 12px 8px;
      font-size: 12px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .badge-status {
      padding: 6px 12px;
      font-size: 11px;
      min-width: 80px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .action-buttons {
      gap: 4px;
    }

    .btn-action {
      min-width: 40px;
      min-height: 40px;
      padding: 6px 10px;
      font-size: 10px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .btn-action span {
      font-size: 9px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .search-box {
      padding: 15px;
      margin-bottom: 15px;
    }

    /* Enhanced mobile horizontal scroll */
    .table-responsive {
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none; /* Hide scrollbar on mobile */
    }

    .table-responsive::-webkit-scrollbar {
      display: none;
    }

    /* Add scroll hint for mobile */
    .table-responsive::after {
      content: '→ Swipe to see more →';
      position: absolute;
      bottom: 10px;
      right: 10px;
      background: rgba(26, 77, 62, 0.8);
      color: white;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 10px;
      z-index: 5;
      animation: fadeInOut 3s infinite;
    }
  }

  @media (max-width: 576px) {
    .table-enhanced {
      min-width: 1600px; /* Still allow horizontal scroll on very small screens */
    }

    .table-enhanced .col-no { min-width: 60px; font-weight: 600; }
    .table-enhanced .col-agenda { min-width: 130px; }
    .table-enhanced .col-tanggal { min-width: 130px; font-weight: 600; }
    .table-enhanced .col-spp { min-width: 140px; font-weight: 600; }
    .table-enhanced .col-nilai { min-width: 140px; }
    .table-enhanced .col-tanggal-spp { min-width: 130px; }
    .table-enhanced .col-uraian { min-width: 280px; font-weight: 600; }
    .table-enhanced .col-deadline { min-width: 160px; }
    .table-enhanced .col-status { min-width: 140px; }
    .table-enhanced .col-action { min-width: 160px; }
  }

  /* Deadline styling */
  .deadline-soon {
    color: #dc3545;
    font-weight: 600;
  }

  .deadline-normal {
    color: #2c3e50;
  }

  /* Detail Row Styles - Enhanced from perpajakan */
  .detail-row {
    display: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  }

  .detail-row.show {
    display: table-row;
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

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table-dokumen tbody tr.main-row:hover {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
    transform: scale(1.002);
  }

  .table-dokumen tbody tr.main-row.selected {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
  }

  .detail-content {
    padding: 20px;
    border-top: 2px solid rgba(26, 77, 62, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
  }

  /* Detail Grid */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    width: 100%;
    box-sizing: border-box;
  }

  @media (min-width: 1400px) {
    .detail-grid {
      grid-template-columns: repeat(5, 1fr);
    }
  }

  @media (min-width: 1200px) and (max-width: 1399px) {
    .detail-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (max-width: 1199px) {
    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px;
    background: white;
    border-radius: 8px;
    border: 1px solid rgba(8, 62, 64, 0.08);
    transition: all 0.2s ease;
  }

  .detail-item:hover {
    border-color: #889717;
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.1);
    transform: translateY(-1px);
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
    word-break: break-word;
  }

  /* Separator for Perpajakan Data */
  .detail-section-separator {
    margin: 32px 0 24px 0;
    padding: 0;
  }

  .separator-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
    border-radius: 12px;
    border-left: 4px solid #ffc107;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.15);
  }

  .separator-content i {
    font-size: 20px;
    color: #ffc107;
  }

  .separator-content span:first-of-type {
    font-weight: 600;
    color: #856404;
    font-size: 14px;
  }

  .tax-badge {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    white-space: nowrap;
    margin-left: auto;
  }

  /* Tax Section Styling */
  .tax-section {
    position: relative;
  }

  .tax-section .detail-item {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid rgba(255, 193, 7, 0.15);
    position: relative;
  }

  .tax-section .detail-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    opacity: 0.3;
    border-radius: 3px 0 0 3px;
  }

  .tax-section .detail-item:hover {
    border-color: rgba(255, 193, 7, 0.4);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.1);
    transform: translateY(-2px);
  }

  .tax-section .detail-item:hover::before {
    opacity: 1;
  }

  .empty-field {
    color: #999;
    font-style: italic;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .tax-link {
    color: #0066cc;
    text-decoration: none;
    word-break: break-all;
  }

  .tax-link:hover {
    text-decoration: underline;
  }

  /* Badge styles for detail view */
  .badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
  }

  .badge.badge-selesai {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .badge.badge-proses {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .table-dokumen {
      padding: 15px;
    }

    .table-enhanced thead th {
      padding: 12px 8px;
      font-size: 11px;
    }

    .table-enhanced tbody td {
      padding: 10px 8px;
      font-size: 12px;
    }

    .btn-action {
      padding: 4px 6px;
      font-size: 10px;
    }

    .detail-content {
      padding: 16px;
    }

    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 10px;
    }

    .detail-item {
      padding: 10px;
    }

    .detail-label {
      font-size: 10px;
    }

    .detail-value {
      font-size: 12px;
    }

    .tax-badge {
      font-size: 10px;
      padding: 4px 12px;
    }
  }

  /* Modal Customization Styles */
  .customization-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    overflow-y: auto;
    padding: 20px;
    box-sizing: border-box;
  }

  .customization-modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .modal-content-custom {
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
    max-width: 900px;
    width: 90%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideIn 0.3s ease;
  }

  @keyframes slideIn {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  .modal-header-custom {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 24px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
  }

  .modal-header-custom h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #212529;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .modal-body-custom {
    padding: 24px 32px;
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .customization-grid {
    display: flex;
    flex-direction: column;
    gap: 24px;
    flex: 1;
    min-height: 0;
  }

  .selection-panel {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
  }

  .panel-title {
    font-size: 18px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .panel-description {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 16px;
    line-height: 1.6;
  }

  .column-selection-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    max-height: 200px;
    overflow-y: auto;
    padding: 8px;
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
  }

  @media (max-width: 900px) {
    .column-selection-list {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 600px) {
    .column-selection-list {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .column-item {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    background: #ffffff;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    cursor: move;
    transition: all 0.2s ease;
    position: relative;
    user-select: none;
    min-height: 44px;
    gap: 8px;
  }

  .column-item.selected {
    border-color: #28a745;
    background: #f0f9f4;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
  }

  .column-item.dragging {
    opacity: 0.6;
    transform: scale(0.98);
  }

  .drag-handle {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    cursor: grab;
    flex-shrink: 0;
    font-size: 12px;
  }

  .column-item.selected .drag-handle {
    color: #28a745;
  }

  .column-item:not(.selected) .drag-handle {
    opacity: 0.3;
    cursor: default;
  }

  .column-item-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    flex-shrink: 0;
  }

  .column-item-label {
    font-size: 14px;
    color: #212529;
    font-weight: 500;
    flex: 1;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .column-item-order {
    width: 24px;
    height: 24px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    opacity: 0;
    transform: scale(0);
    transition: all 0.2s ease;
    flex-shrink: 0;
  }

  .column-item.selected .column-item-order {
    opacity: 1;
    transform: scale(1);
  }

  .preview-panel {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
  }

  .preview-container {
    flex: 1;
    overflow-x: auto;
    overflow-y: auto;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    min-height: 400px;
    width: 100%;
  }

  .preview-table {
    width: 100%;
    min-width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    font-size: 13px;
    table-layout: auto;
  }

  .preview-table th {
    background: #212529;
    color: white;
    padding: 14px 12px;
    text-align: center;
    font-weight: 600;
    font-size: 12px;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    white-space: nowrap;
  }

  .preview-table td {
    padding: 12px;
    text-align: center;
    border-right: 1px solid #e9ecef;
    color: #495057;
    font-size: 13px;
  }

  .empty-preview {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
  }

  .modal-footer-custom {
    padding: 20px 40px;
    border-top: 1px solid #e9ecef;
    background: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-shrink: 0;
    position: sticky;
    bottom: 0;
    z-index: 100;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
  }

  .selected-count {
    font-size: 15px;
    color: #495057;
    font-weight: 500;
  }

  .selected-count strong {
    color: #28a745;
    font-size: 18px;
  }

  .modal-actions {
    display: flex;
    gap: 12px;
  }

  .btn-modal {
    padding: 12px 32px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 48px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-cancel {
    background: #6c757d;
    color: white;
  }

  .btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-1px);
  }

  .btn-save {
    background: #28a745;
    color: white;
  }

  .btn-save:hover {
    background: #218838;
    transform: translateY(-1px);
  }

  .btn-save:disabled {
    background: #adb5bd;
    cursor: not-allowed;
    transform: none;
  }
</style>

<h2>{{ $title }}</h2>

<!-- Enhanced Search & Filter Box -->
<div class="search-box">
  <form action="{{ route('dokumensAkutansi.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-3">
    <div class="input-group" style="flex: 1; min-width: 300px;">
      <span class="input-group-text">
        <i class="fa-solid fa-magnifying-glass text-muted"></i>
      </span>
      <input type="text" id="akutansiSearchInput" class="form-control" name="search" placeholder="Cari nomor agenda, SPP, nilai rupiah, atau field lainnya..." value="{{ request('search') }}">
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
    <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
      <i class="fa-solid fa-table-columns me-2"></i>
      Kustomisasi Kolom Tabel
    </button>
  </form>
</div>

@if(isset($suggestions) && !empty($suggestions) && request('search'))
<!-- Search Suggestions Alert -->
<div class="alert alert-info alert-dismissible fade show suggestion-alert" role="alert" style="margin-bottom: 20px; border-left: 4px solid #0dcaf0; background-color: #e7f3ff;">
  <div class="d-flex align-items-start">
    <i class="fa-solid fa-lightbulb me-2 mt-1" style="color: #0dcaf0; font-size: 18px;"></i>
    <div style="flex: 1;">
      <strong style="color: #0a58ca;">Apakah yang Anda maksud?</strong>
      <p class="mb-2 mt-2" style="color: #055160;">
        Tidak ada hasil ditemukan untuk "<strong>{{ request('search') }}</strong>". Mungkin maksud Anda:
      </p>
      <div class="suggestion-buttons d-flex flex-wrap gap-2">
        @foreach($suggestions as $suggestion)
          <button type="button" class="btn btn-sm btn-outline-primary suggestion-btn" 
                  data-suggestion="{{ $suggestion }}" 
                  style="border-color: #0dcaf0; color: #0dcaf0;">
            <i class="fa-solid fa-magnifying-glass me-1"></i>{{ $suggestion }}
          </button>
        @endforeach
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
</div>
@endif

<!-- Tabel Dokumen dengan Horizontal Scroll -->
<div class="table-dokumen">
  <div class="table-container-header">
    <h3 class="table-container-title">
      <i class="fa-solid fa-file-lines"></i>
      Daftar Dokumen Team Akutansi
    </h3>
    <div class="table-container-stats">
      <div class="stat-item">
        <span class="stat-value">{{ count($dokumens) }}</span>
        <span class="stat-label">Total</span>
      </div>
      <div class="stat-item">
        <span class="stat-value">{{ $dokumens->where('status', 'selesai')->count() }}</span>
        <span class="stat-label">Selesai</span>
      </div>
      <div class="stat-item">
        <span class="stat-value">{{ $dokumens->where('is_locked', true)->count() }}</span>
        <span class="stat-label">Terkunci</span>
      </div>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-enhanced mb-0">
      <thead>
        <tr>
          <th class="col-no">No</th>
          @foreach($selectedColumns as $col)
            <th class="col-{{ $col }}">{{ $availableColumns[$col] ?? $col }}</th>
          @endforeach
          <th class="col-deadline">Deadline</th>
          <th class="col-status">Status</th>
          <th class="col-action">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($dokumens as $index => $dokumen)
        <tr class="main-row {{ $dokumen->lock_status_class }}" onclick="toggleDetail({{ $dokumen->id }})" title="{{ $dokumen->lock_status_message }}">
            <td style="text-align: center;">{{ $index + 1 }}</td>
            @foreach($selectedColumns as $col)
              <td class="col-{{ $col }}">
                @if($col == 'nomor_agenda')
                  <strong>{{ $dokumen->nomor_agenda }}</strong>
                  <br>
                  <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
                @elseif($col == 'nomor_spp')
                  {{ $dokumen->nomor_spp }}
                @elseif($col == 'tanggal_masuk')
                  {{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}
                @elseif($col == 'nilai_rupiah')
                  <strong>{{ $dokumen->formatted_nilai_rupiah ?? 'Rp. ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                @elseif($col == 'nomor_mirror')
                  {{ $dokumen->nomor_mirror ?? '-' }}
                @elseif($col == 'keterangan')
                  {{ $dokumen->keterangan ?? '-' }}
                @elseif($col == 'tanggal_spp')
                  {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}
                @elseif($col == 'uraian_spp')
                  {{ Str::limit($dokumen->uraian_spp ?? '-', 60) }}
                @elseif($col == 'kategori')
                  {{ $dokumen->kategori ?? '-' }}
                @elseif($col == 'jenis_dokumen')
                  {{ $dokumen->jenis_dokumen ?? '-' }}
                @elseif($col == 'jenis_pembayaran')
                  {{ $dokumen->jenis_pembayaran ?? '-' }}
                @elseif($col == 'nama_pengirim')
                  {{ $dokumen->nama_pengirim ?? '-' }}
                @elseif($col == 'dibayar_kepada')
                  @if($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                    {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
                  @else
                    {{ $dokumen->dibayar_kepada ?? '-' }}
                  @endif
                @elseif($col == 'no_berita_acara')
                  {{ $dokumen->no_berita_acara ?? '-' }}
                @elseif($col == 'tanggal_berita_acara')
                  {{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-' }}
                @elseif($col == 'no_spk')
                  {{ $dokumen->no_spk ?? '-' }}
                @elseif($col == 'tanggal_spk')
                  {{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-' }}
                @elseif($col == 'tanggal_berakhir_spk')
                  {{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                @else
                  -
                @endif
              </td>
            @endforeach
            <td>
              @if($dokumen->deadline_at)
                <div class="deadline-card" data-deadline="{{ $dokumen->deadline_at->format('Y-m-d H:i:s') }}">
                  <div class="deadline-time">
                    <i class="fa-solid fa-clock"></i>
                    <span>{{ $dokumen->deadline_at->format('d M Y, H:i') }}</span>
                  </div>
                  <div class="deadline-indicator">
                    <i class="fa-solid"></i>
                    <span class="status-text">AMAN</span>
                  </div>
                  @if($dokumen->deadline_note)
                    <div class="deadline-note">{{ Str::limit($dokumen->deadline_note, 50) }}</div>
                  @endif
                </div>
              @else
                <div class="no-deadline">
                  <i class="fa-solid fa-clock"></i>
                  <span>Belum ada deadline</span>
                </div>
              @endif
            </td>
            <td style="text-align: center;">
              @if($dokumen->status == 'selesai')
                <span class="badge-status badge-selesai">✓ Selesai</span>
              @elseif($dokumen->status == 'sedang diproses' && $dokumen->current_handler == 'akutansi')
                <span class="badge-status badge-proses">⏳ Diproses</span>
              @elseif($dokumen->is_locked)
                <span class="badge-status badge-locked">🔒 Terkunci</span>
              @elseif($dokumen->status == 'sent_to_akutansi')
                <span class="badge-status badge-belum">⏳ Belum Diproses</span>
              @elseif(in_array($dokumen->status, ['returned_to_ibua', 'returned_to_department', 'dikembalikan']))
                <span class="badge-status badge-dikembalikan">← Dikembalikan</span>
              @else
                <span class="badge-status badge-proses">{{ $dokumen->status }}</span>
              @endif
            </td>
            <td onclick="event.stopPropagation()">
              <div class="action-buttons">
                @php
                  $isSentToPembayaran = $dokumen->status == 'sent_to_pembayaran';
                @endphp
                @if($dokumen->is_locked)
                  <!-- Locked state - buttons disabled -->
                  @unless($isSentToPembayaran)
                    <div class="action-row">
                      <button class="btn-action btn-edit locked" disabled title="Edit terkunci - tentukan deadline terlebih dahulu">
                        <i class="fa-solid fa-lock"></i>
                        <span>Terkunci</span>
                      </button>
                    </div>
                  @endunless
                  @if($dokumen->can_set_deadline)
                    <div class="action-row">
                      <button type="button" class="btn-action btn-set-deadline" onclick="openSetDeadlineModal({{ $dokumen->id }})" title="Tetapkan Deadline">
                        <i class="fa-solid fa-clock"></i>
                        <span>Set Deadline</span>
                      </button>
                    </div>
                  @endif
                  <div class="action-row">
                    @php
                      $hasNomorMiro = !empty($dokumen->nomor_miro);
                      $canSend = !$isSentToPembayaran && 
                                 $dokumen->status == 'sedang diproses' && 
                                 $dokumen->current_handler == 'akutansi' &&
                                 $hasNomorMiro;
                    @endphp
                    <button
                      type="button"
                      class="btn-action btn-send {{ !$canSend ? 'locked' : '' }}"
                      title="{{ $isSentToPembayaran ? 'Dokumen sudah dikirim ke Team Pembayaran' : (!$hasNomorMiro ? 'Nomor MIRO harus diisi terlebih dahulu' : 'Kirim ke Team Pembayaran') }}"
                      @if($isSentToPembayaran || !$canSend) disabled @endif
                      @unless($isSentToPembayaran || !$canSend)
                        onclick="sendToPembayaran({{ $dokumen->id }})"
                      @endunless
                    >
                      <i class="fa-solid fa-paper-plane"></i>
                      <span>Kirim</span>
                    </button>
                  </div>
                @else
                  <!-- Unlocked state - buttons enabled -->
                  <div class="action-row">
                    @unless($isSentToPembayaran)
                      @if($dokumen->can_edit)
                        <a href="{{ route('dokumensAkutansi.edit', $dokumen->id) }}" title="Edit Dokumen" style="text-decoration: none;">
                          <button class="btn-action btn-edit">
                            <i class="fa-solid fa-pen"></i>
                            <span>Edit</span>
                          </button>
                        </a>
                      @endif
                    @endunless
                    @php
                      $hasNomorMiro = !empty($dokumen->nomor_miro);
                      $canSend = !$isSentToPembayaran && 
                                 $dokumen->status == 'sedang diproses' && 
                                 $dokumen->current_handler == 'akutansi' &&
                                 $hasNomorMiro;
                    @endphp
                    <button
                      type="button"
                      class="btn-action btn-send {{ !$canSend ? 'locked' : '' }}"
                      title="{{ $isSentToPembayaran ? 'Dokumen sudah dikirim ke Team Pembayaran' : (!$hasNomorMiro ? 'Nomor MIRO harus diisi terlebih dahulu' : 'Kirim ke Team Pembayaran') }}"
                      @if($isSentToPembayaran || !$canSend) disabled @endif
                      @unless($isSentToPembayaran || !$canSend)
                        onclick="sendToPembayaran({{ $dokumen->id }})"
                      @endunless
                    >
                      <i class="fa-solid fa-paper-plane"></i>
                      <span>Kirim</span>
                    </button>
                  </div>
                @endif
              </div>
            </td>
          </tr>
          <tr class="detail-row" id="detail-{{ $dokumen->id }}">
            <td colspan="{{ count($selectedColumns) + 4 }}">
              <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                <div class="text-center p-4">
                  <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ count($selectedColumns) + 4 }}" class="text-center py-5">
              <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
              <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if(isset($dokumens) && $dokumens->hasPages())
<div class="pagination" style="margin-top: 24px; display: flex; justify-content: center; gap: 8px;">
    {{-- Previous Page Link --}}
    @if($dokumens->onFirstPage())
        <button class="btn-chevron" disabled style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: #e0e0e0; color: #9e9e9e; border-radius: 10px; cursor: not-allowed;">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
    @else
        <a href="{{ $dokumens->appends(request()->query())->previousPageUrl() }}">
            <button class="btn-chevron" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; border-radius: 10px; cursor: pointer;">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </a>
    @endif

    {{-- Pagination Elements --}}
    @if($dokumens->hasPages())
        {{-- First page --}}
        @if($dokumens->currentPage() > 3)
            <a href="{{ $dokumens->appends(request()->query())->url(1) }}">
                <button style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background-color: white; border-radius: 10px; cursor: pointer;">1</button>
            </a>
        @endif

        {{-- Dots --}}
        @if($dokumens->currentPage() > 4)
            <button disabled style="padding: 10px 16px; border: none; background: transparent; color: #999;">...</button>
        @endif

        {{-- Range of pages --}}
        @for($i = max(1, $dokumens->currentPage() - 2); $i <= min($dokumens->lastPage(), $dokumens->currentPage() + 2); $i++)
            @if($dokumens->currentPage() == $i)
                <button class="active" style="padding: 10px 16px; background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%); color: white; border: none; border-radius: 10px; cursor: pointer;">{{ $i }}</button>
            @else
                <a href="{{ $dokumens->appends(request()->query())->url($i) }}">
                    <button style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background-color: white; border-radius: 10px; cursor: pointer;">{{ $i }}</button>
                </a>
            @endif
        @endfor

        {{-- Dots --}}
        @if($dokumens->currentPage() < $dokumens->lastPage() - 3)
            <button disabled style="padding: 10px 16px; border: none; background: transparent; color: #999;">...</button>
        @endif

        {{-- Last page --}}
        @if($dokumens->currentPage() < $dokumens->lastPage() - 2)
            <a href="{{ $dokumens->appends(request()->query())->url($dokumens->lastPage()) }}">
                <button style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background-color: white; border-radius: 10px; cursor: pointer;">{{ $dokumens->lastPage() }}</button>
            </a>
        @endif
    @endif

    {{-- Next Page Link --}}
    @if($dokumens->hasMorePages())
        <a href="{{ $dokumens->appends(request()->query())->nextPageUrl() }}">
            <button class="btn-chevron" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; border-radius: 10px; cursor: pointer;">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </a>
    @else
        <button class="btn-chevron" disabled style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: #e0e0e0; color: #9e9e9e; border-radius: 10px; cursor: not-allowed;">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    @endif
</div>
@endif

<!-- Modal Set Deadline -->
<div class="modal fade" id="setDeadlineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-clock me-2"></i>Tetapkan Timeline Team Akutansi
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="deadlineDocId">

        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.12) 0%, rgba(255, 140, 0, 0.12) 100%); border-left: 4px solid #ffc107;">
          <i class="fa-solid fa-info-circle me-2"></i>
          Dokumen akan tetap terkunci sampai timeline ditetapkan. Setelah dibuka, dokumen dapat diedit atau dikirim.
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Periode Deadline*</label>
          <select class="form-select" id="deadlineDays" required>
            <option value="">Pilih periode deadline</option>
            <option value="1">1 hari</option>
            <option value="2">2 hari</option>
            <option value="3">3 hari</option>
            <option value="5">5 hari</option>
            <option value="7">7 hari</option>
            <option value="14">14 hari</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Catatan (opsional)</label>
          <textarea class="form-control" id="deadlineNote" rows="3" maxlength="500" placeholder="Contoh: Menunggu kelengkapan dokumen pendukung"></textarea>
          <small class="text-muted"><span id="deadlineCharCount">0</span>/500 karakter</small>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-warning" onclick="confirmSetDeadline()">
          <i class="fa-solid fa-check me-2"></i>Tetapkan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Deadline Success -->
<div class="modal fade" id="deadlineSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-circle-check me-2"></i>Deadline Berhasil Ditetapkan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #28a745;"></i>
        </div>
        <h5 class="fw-bold mb-2">Deadline berhasil ditetapkan!</h5>
        <p class="text-muted mb-0" id="deadlineSuccessMessage">
          Dokumen sekarang terbuka untuk diproses.
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
// Toggle detail row
function toggleDetail(docId) {
  const detailRow = document.getElementById('detail-' + docId);
  const mainRow = event.currentTarget;

  // Close all other detail rows first
  const allDetailRows = document.querySelectorAll('.detail-row.show');
  const allMainRows = document.querySelectorAll('.main-row.selected');

  allDetailRows.forEach(row => {
    if (row.id !== 'detail-' + docId) {
      row.classList.remove('show');
    }
  });

  allMainRows.forEach(row => {
    if (row !== mainRow) {
      row.classList.remove('selected');
    }
  });

  // Toggle current detail row
  const isShowing = detailRow.classList.contains('show');

  if (isShowing) {
    detailRow.classList.remove('show');
    mainRow.classList.remove('selected');
  } else {
    loadDocumentDetail(docId);
    detailRow.classList.add('show');
    mainRow.classList.add('selected');

    setTimeout(() => {
      detailRow.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest'
      });
    }, 100);
  }
}

// Load document detail via AJAX
function loadDocumentDetail(docId) {
  const detailContent = document.getElementById('detail-content-' + docId);

  detailContent.innerHTML = `
    <div class="text-center p-4">
      <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
    </div>
  `;

  fetch(`/dokumensAkutansi/${docId}/detail`)
    .then(response => response.text())
    .then(html => {
      detailContent.innerHTML = html;
    })
    .catch(error => {
      console.error('Error:', error);
      detailContent.innerHTML = `
        <div class="text-center p-4 text-danger">
          <i class="fa-solid fa-exclamation-triangle me-2"></i> Gagal memuat detail dokumen.
        </div>
      `;
    });
}

function editDocument(id) {
  // Implement edit functionality
  window.location.href = `/dokumensAkutansi/${id}/edit`;
}

function sendToPembayaran(id) {
  if (confirm('Apakah Anda yakin ingin mengirim dokumen ini ke Pembayaran?')) {
    const sendBtn = event.currentTarget;
    const originalHTML = sendBtn.innerHTML;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    fetch(`/dokumensAkutansi/${id}/send-to-pembayaran`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        // Remove the row from table or refresh
        location.reload();
      } else {
        alert(data.message || 'Gagal mengirim dokumen ke Pembayaran.');
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalHTML;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.');
      sendBtn.disabled = false;
      sendBtn.innerHTML = originalHTML;
    });
  }
}

// Search functionality
// Client-side search removed - using server-side search instead
// Search will be performed when form is submitted
/*
document.getElementById('akutansiSearchInput').addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const allRows = document.querySelectorAll('.table-enhanced tbody tr');

  allRows.forEach(row => {
    // Skip detail rows in search
    if (row.classList.contains('detail-row')) {
      return;
    }

    const text = row.textContent.toLowerCase();
    if (text.includes(searchTerm)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
      // Also hide corresponding detail row
      const rowId = row.getAttribute('onclick')?.match(/toggleDetail\((\d+)\)/)?.[1];
      if (rowId) {
        const detailRow = document.getElementById('detail-' + rowId);
        if (detailRow) {
          detailRow.style.display = 'none';
        }
      }
    }
  });
});
*/

document.getElementById('deadlineNote').addEventListener('input', function(e) {
  document.getElementById('deadlineCharCount').textContent = e.target.value.length;
});

function openSetDeadlineModal(docId) {
  document.getElementById('deadlineDocId').value = docId;
  document.getElementById('deadlineDays').value = '';
  document.getElementById('deadlineNote').value = '';
  document.getElementById('deadlineCharCount').textContent = '0';
  const modal = new bootstrap.Modal(document.getElementById('setDeadlineModal'));
  modal.show();
}

function confirmSetDeadline() {
  const docId = document.getElementById('deadlineDocId').value;
  const deadlineDays = document.getElementById('deadlineDays').value;
  const deadlineNote = document.getElementById('deadlineNote').value;

  if (!deadlineDays) {
    showWarningModal('Pilih periode deadline terlebih dahulu!');
    return;
  }

  if (deadlineDays < 1 || deadlineDays > 14) {
    showWarningModal('Periode deadline harus antara 1-14 hari!');
    return;
  }

  // Check CSRF token availability
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!csrfToken) {
    console.error('CSRF token not found!');
    showErrorModal('CSRF token tidak ditemukan. Silakan refresh halaman.');
    return;
  }

  const submitBtn = document.querySelector('#setDeadlineModal .btn-warning');
  const originalHTML = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

  // Type casting untuk memastikan integer
  const deadlineDaysInt = parseInt(deadlineDays, 10);

  console.log('Sending request to: ', `/dokumensAkutansi/${docId}/set-deadline`);
  console.log('Request payload: ', {
    deadline_days: deadlineDaysInt,
    deadline_note: deadlineNote
  });

  fetch(`/dokumensAkutansi/${docId}/set-deadline`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      deadline_days: deadlineDaysInt,
      deadline_note: deadlineNote
    })
  })
  .then(async response => {
    console.log('Response status:', response.status);
    
    // Try to parse response as JSON first
    let responseData;
    try {
      responseData = await response.json();
    } catch (e) {
      // If response is not JSON, create error object
      responseData = {
        success: false,
        message: `Server error: ${response.status} ${response.statusText}`
      };
    }
    
    if (!response.ok) {
      // Extract error message from response
      const errorMessage = responseData.message || responseData.error || `HTTP error! status: ${response.status}`;
      
      // Log debug info if available
      if (responseData.debug_info) {
        console.error('Debug info:', responseData.debug_info);
      }
      
      throw new Error(errorMessage);
    }
    
    return responseData;
  })
  .then(data => {
    console.log('Response data:', data);
    if (data.success) {
      const deadlineModal = bootstrap.Modal.getInstance(document.getElementById('setDeadlineModal'));
      deadlineModal.hide();

      // Show success modal
      const successModalEl = document.getElementById('deadlineSuccessModal');
      const successModal = new bootstrap.Modal(successModalEl);
      const successMessageEl = document.getElementById('deadlineSuccessMessage');
      
      if (data.deadline) {
        successMessageEl.textContent = 
          `Deadline: ${data.deadline}. Dokumen sekarang terbuka untuk diproses.`;
      } else {
        successMessageEl.textContent = data.message || 'Deadline berhasil ditetapkan.';
      }
      
      // Reload page when modal is closed
      successModalEl.addEventListener('hidden.bs.modal', function() {
        location.reload();
      }, { once: true });
      
      successModal.show();
    } else {
      alert('Gagal menetapkan deadline: ' + (data.message || 'Terjadi kesalahan yang tidak diketahui'));
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    console.error('Error details:', error.message);
    alert('Terjadi kesalahan saat menetapkan deadline: ' + error.message);
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
  });
}
// Helper Functions for Modal Alerts
function showWarningModal(message) {
  alert(message); // Simple fallback - could be enhanced with proper modal
}

function showErrorModal(message) {
  alert(message); // Simple fallback - could be enhanced with proper modal
}

function showSuccessModal(message) {
  alert(message); // Simple fallback - could be enhanced with proper modal
}

// Enhanced deadline system with color coding and late information for akutansi
function initializeDeadlines() {
  const deadlineElements = document.querySelectorAll('.deadline-card');

  deadlineElements.forEach(card => {
    updateDeadlineCard(card);
  });

  // Update every 30 seconds for better responsiveness
  setInterval(() => {
    deadlineElements.forEach(card => {
      updateDeadlineCard(card);
    });
  }, 30000); // Update every 30 seconds
}

function updateDeadlineCard(card) {
  const deadlineStr = card.dataset.deadline;
  if (!deadlineStr) return;

  const deadline = new Date(deadlineStr);
  const now = new Date();
  const diffMs = deadline - now;

  // Remove existing status classes
  card.classList.remove('deadline-safe', 'deadline-warning', 'deadline-danger', 'deadline-overdue');

  // Find status indicator
  const statusIndicator = card.querySelector('.deadline-indicator');
  const statusText = card.querySelector('.status-text');
  const statusIcon = statusIndicator.querySelector('i');

  // Remove existing late info and time hints
  const existingLateInfo = card.querySelector('.late-info');
  const existingTimeHint = card.querySelector('div[style*="margin-top: 2px"]');
  const existingProgress = card.querySelector('.deadline-progress');

  if (existingLateInfo) existingLateInfo.remove();
  if (existingTimeHint) existingTimeHint.remove();
  if (existingProgress) existingProgress.remove();

  if (diffMs < 0) {
    // Overdue state
    card.classList.add('deadline-overdue');

    // Calculate how late
    const diffHours = Math.abs(Math.floor(diffMs / (1000 * 60 * 60)));
    const diffDays = Math.abs(Math.floor(diffMs / (1000 * 60 * 60 * 24)));

    // Update status text
    statusText.textContent = 'TERLAMBAT';
    statusIcon.className = 'fa-solid fa-exclamation-triangle';
    statusIndicator.className = 'deadline-indicator deadline-overdue';

    // Create late info with enhanced styling
    let lateText;
    if (diffDays >= 1) {
      lateText = `${diffDays} ${diffDays === 1 ? 'hari' : 'hari'} telat`;
    } else if (diffHours >= 1) {
      lateText = `${diffHours} ${diffHours === 1 ? 'jam' : 'jam'} telat`;
    } else {
      lateText = 'Baru saja terlambat';
    }

    const lateInfo = document.createElement('div');
    lateInfo.className = 'late-info';
    lateInfo.innerHTML = `
      <i class="fa-solid fa-exclamation-triangle"></i>
      <span class="late-text">${lateText}</span>
    `;

    card.appendChild(lateInfo);

    // Add progress bar at bottom
    const progressBar = document.createElement('div');
    progressBar.className = 'deadline-progress';
    card.appendChild(progressBar);

  } else {
    // Time remaining
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffMinutes = Math.floor(diffMs / (1000 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    // Simplified 3-status logic: >= 1 hari = hijau, < 1 hari = kuning, terlambat = merah
    if (diffDays >= 1) {
      // Safe (>= 1 day) - Green
      card.classList.add('deadline-safe');
      statusText.textContent = 'AMAN';
      statusIcon.className = 'fa-solid fa-check-circle';
      statusIndicator.className = 'deadline-indicator deadline-safe';

      // Add time remaining hint
      const timeHint = document.createElement('div');
      timeHint.style.cssText = 'font-size: 8px; color: #065f46; margin-top: 2px; font-weight: 600;';
      timeHint.textContent = `${diffDays} ${diffDays === 1 ? 'hari' : 'hari'} lagi`;
      card.appendChild(timeHint);

    } else if (diffHours >= 1 || diffMinutes >= 1) {
      // Warning (< 1 day) - Yellow
      card.classList.add('deadline-warning');
      statusText.textContent = 'DEKAT';
      statusIcon.className = 'fa-solid fa-exclamation-triangle';
      statusIndicator.className = 'deadline-indicator deadline-warning';

      const timeHint = document.createElement('div');
      timeHint.style.cssText = 'font-size: 8px; color: #92400e; margin-top: 2px; font-weight: 700;';
      if (diffHours >= 1) {
        timeHint.textContent = `${diffHours} ${diffHours === 1 ? 'jam' : 'jam'} lagi`;
      } else {
        timeHint.textContent = `${diffMinutes} menit lagi`;
        timeHint.style.animation = 'warning-shake 1s infinite';
      }
      card.appendChild(timeHint);

    }

    // Add progress bar
    const progressBar = document.createElement('div');
    progressBar.className = 'deadline-progress';
    card.appendChild(progressBar);
  }
}

// Initialize deadlines system
document.addEventListener('DOMContentLoaded', function() {
  initializeDeadlines();
});

</script>

<script>
// Handle suggestion button clicks
document.addEventListener('DOMContentLoaded', function() {
    const suggestionButtons = document.querySelectorAll('.suggestion-btn');
    
    suggestionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const suggestion = this.getAttribute('data-suggestion');
            const searchInput = document.querySelector('input[name="search"]');
            const form = searchInput.closest('form');
            
            // Set the suggestion value to search input
            searchInput.value = suggestion;
            
            // Submit the form
            form.submit();
        });
    });
});
</script>

<!-- Modal: Column Customization -->
<div class="customization-modal" id="columnCustomizationModal">
  <div class="modal-content-custom">
    <div class="modal-header-custom">
      <h3>
        <i class="fa-solid fa-table-columns"></i>
        Kustomisasi Kolom Tabel
      </h3>
    </div>

    <div class="modal-body-custom">
      <div class="customization-grid">
        <!-- Selection Panel -->
        <div class="selection-panel">
          <div class="panel-title">
            <i class="fa-solid fa-check-square"></i>
            Pilih Kolom
          </div>
          <div class="panel-description">
            Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
          </div>
          <div class="column-selection-list" id="columnSelectionList">
            @foreach($availableColumns as $key => $label)
              <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}"
                   data-column="{{ $key }}"
                   draggable="{{ in_array($key, $selectedColumns) ? 'true' : 'false' }}"
                   onclick="toggleColumn(this)">
                <div class="drag-handle">
                  <i class="fa-solid fa-grip-vertical"></i>
                </div>
                <input type="checkbox"
                       class="column-item-checkbox"
                       value="{{ $key }}"
                       {{ in_array($key, $selectedColumns) ? 'checked' : '' }}
                       onclick="event.stopPropagation()">
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
                    @for($i = 1; $i <= 5; $i++)
                      <tr>
                        <td>{{ $i }}</td>
                        @foreach($selectedColumns as $col)
                          <td>
                            @if($col == 'nomor_agenda')
                              AGD/{{ 100 + $i }}/XII/2024
                            @elseif($col == 'nomor_spp')
                              {{ 200 + $i }}/M/SPP/8/04/2024
                            @elseif($col == 'tanggal_masuk')
                              {{ date('d-m-Y', strtotime("+$i days")) }} 08:{{ str_pad($i * 10, 2, '0', STR_PAD_LEFT) }}
                            @elseif($col == 'nilai_rupiah')
                              Rp. {{ number_format(1000000 * $i, 0, ',', '.') }}
                            @elseif($col == 'nomor_mirror')
                              MIR-{{ 1000 + $i }}
                            @elseif($col == 'status')
                              <span style="color: #28a745;">✓ Terkirim</span>
                            @elseif($col == 'keterangan')
                              Dokumen lengkap
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
          <br><small>Kolom: {{ implode(', ', array_map(function($col) use ($availableColumns) {
            return $availableColumns[$col] ?? $col;
          }, $selectedColumns)) }}</small>
        @endif
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-modal btn-cancel" onclick="closeColumnCustomizationModal()">
          <i class="fa-solid fa-times"></i>
          Batal
        </button>
        <button type="button" class="btn-modal btn-save" id="saveCustomizationBtn" onclick="saveColumnCustomization()">
          <i class="fa-solid fa-save"></i>
          Simpan Perubahan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Global variables for column customization
let selectedColumnsOrder = [];
let availableColumnsData = {};

// Initialize available columns data from PHP
@php
    $columnsJson = json_encode($availableColumns);
    echo "availableColumnsData = {$columnsJson};";
@endphp

// Initialize selected columns from existing selection
@if(count($selectedColumns) > 0)
  selectedColumnsOrder = @json($selectedColumns);
@endif

// Global Functions
function openColumnCustomizationModal() {
  const modal = document.getElementById('columnCustomizationModal');
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
  initializeModalState();
}

function closeColumnCustomizationModal() {
  const modal = document.getElementById('columnCustomizationModal');
  modal.classList.remove('show');
  document.body.style.overflow = '';
}

function toggleColumn(columnElement) {
  const columnKey = columnElement.dataset.column;
  const checkbox = columnElement.querySelector('.column-item-checkbox');
  const isChecked = checkbox.checked;

  if (!isChecked) {
    if (!selectedColumnsOrder.includes(columnKey)) {
      selectedColumnsOrder.push(columnKey);
    }
    checkbox.checked = true;
    columnElement.classList.add('selected');
    columnElement.setAttribute('draggable', 'true');
  } else {
    selectedColumnsOrder = selectedColumnsOrder.filter(key => key !== columnKey);
    checkbox.checked = false;
    columnElement.classList.remove('selected');
    columnElement.setAttribute('draggable', 'false');
  }

  updateColumnOrderBadges();
  updatePreviewTable();
  updateSelectedCount();
  updateDraggableState();
}

function updateColumnOrderBadges() {
  document.querySelectorAll('.column-item').forEach(item => {
    const columnKey = item.dataset.column;
    const orderBadge = item.querySelector('.column-item-order');
    const index = selectedColumnsOrder.indexOf(columnKey);

    if (index !== -1) {
      orderBadge.textContent = index + 1;
    } else {
      orderBadge.textContent = '';
    }
  });
}

function updatePreviewTable() {
  const previewContainer = document.getElementById('tablePreview');

  if (selectedColumnsOrder.length === 0) {
    previewContainer.innerHTML = `
      <div class="empty-preview">
        <i class="fa-solid fa-table fa-2x mb-2"></i>
        <p>Belum ada kolom yang dipilih</p>
        <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
      </div>
    `;
    return;
  }

  let previewHTML = `
    <table class="preview-table">
      <thead>
        <tr>
          <th>No</th>
  `;

  selectedColumnsOrder.forEach(columnKey => {
    const columnLabel = availableColumnsData[columnKey] || columnKey;
    previewHTML += `<th>${columnLabel}</th>`;
  });

  previewHTML += `
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
  `;

  const sampleData = {
    'nomor_agenda': ['AGD/822/XII/2024', 'AGD/258/XII/2024', 'AGD/992/XII/2024', 'AGD/92/XII/2024', 'AGD/546/XII/2024'],
    'nomor_spp': ['627/M/SPP/8/04/2024', '32/M/SPP/3/09/2024', '205/M/SPP/5/05/2024', '331/M/SPP/19/12/2024', '580/M/SPP/28/08/2024'],
    'tanggal_masuk': ['24/11/2024 08:49', '24/11/2024 08:37', '24/11/2024 08:18', '24/11/2024 08:13', '24/11/2024 08:09'],
    'nilai_rupiah': ['Rp. 241.650.650', 'Rp. 751.897.501', 'Rp. 232.782.087', 'Rp. 490.050.679', 'Rp. 397.340.004'],
    'nomor_mirror': ['MIR-1001', 'MIR-1002', 'MIR-1003', 'MIR-1004', 'MIR-1005'],
    'status': ['✓ Terkirim', '✓ Terkirim', '✓ Terkirim', '✓ Terkirim', '✓ Terkirim'],
    'keterangan': ['Dokumen lengkap', 'Dokumen lengkap', 'Dokumen lengkap', 'Dokumen lengkap', 'Dokumen lengkap'],
  };

  for (let i = 0; i < 5; i++) {
    previewHTML += `<tr>`;
    previewHTML += `<td>${i + 1}</td>`;

    selectedColumnsOrder.forEach(columnKey => {
      const columnLabel = availableColumnsData[columnKey] || columnKey;
      let cellValue = sampleData[columnKey] ? sampleData[columnKey][i] : `Contoh ${columnLabel} ${i + 1}`;
      
      if (columnKey === 'status') {
        cellValue = `<span style="color: #28a745;">${cellValue}</span>`;
      }
      
      previewHTML += `<td>${cellValue}</td>`;
    });

    previewHTML += `<td>Edit, Kirim</td>`;
    previewHTML += `</tr>`;
  }

  previewHTML += `
      </tbody>
    </table>
  `;

  previewContainer.innerHTML = previewHTML;
}

function updateSelectedCount() {
  const countElement = document.getElementById('selectedColumnCount');
  countElement.textContent = selectedColumnsOrder.length;

  const saveButton = document.getElementById('saveCustomizationBtn');
  saveButton.disabled = selectedColumnsOrder.length === 0;
}

function saveColumnCustomization() {
  if (selectedColumnsOrder.length === 0) {
    alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
    return;
  }

  const filterForm = document.querySelector('form[action*="dokumensAkutansi"]');
  if (!filterForm) {
    alert('Form tidak ditemukan.');
    return;
  }

  document.querySelectorAll('input[name="columns[]"]').forEach(input => {
    if (input.type === 'hidden') {
      input.remove();
    }
  });

  selectedColumnsOrder.forEach(columnKey => {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'columns[]';
    hiddenInput.value = columnKey;
    filterForm.appendChild(hiddenInput);
  });

  closeColumnCustomizationModal();
  filterForm.submit();
}

function initializeModalState() {
  document.querySelectorAll('.column-item').forEach(item => {
    const columnKey = item.dataset.column;
    const checkbox = item.querySelector('.column-item-checkbox');

    if (selectedColumnsOrder.includes(columnKey)) {
      checkbox.checked = true;
      item.classList.add('selected');
      item.setAttribute('draggable', 'true');
    } else {
      checkbox.checked = false;
      item.classList.remove('selected');
      item.setAttribute('draggable', 'false');
    }
  });

  initializeDragAndDrop();
  updateColumnOrderBadges();
  updatePreviewTable();
  updateSelectedCount();
}

function updateDraggableState() {
  document.querySelectorAll('.column-item').forEach(item => {
    const columnKey = item.dataset.column;
    if (selectedColumnsOrder.includes(columnKey)) {
      item.setAttribute('draggable', 'true');
    } else {
      item.setAttribute('draggable', 'false');
    }
  });
}

let draggedElement = null;

function initializeDragAndDrop() {
  const columnList = document.getElementById('columnSelectionList');
  if (!columnList) return;

  const newList = columnList.cloneNode(true);
  columnList.parentNode.replaceChild(newList, columnList);

  newList.querySelectorAll('.column-item.selected').forEach(item => {
    item.addEventListener('dragstart', handleDragStart);
    item.addEventListener('dragend', handleDragEnd);
    item.addEventListener('dragover', handleDragOver);
    item.addEventListener('drop', handleDrop);
  });
}

function handleDragStart(e) {
  draggedElement = this;
  this.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
  this.classList.remove('dragging');
  document.querySelectorAll('.column-item').forEach(el => {
    el.classList.remove('drag-over');
  });
  draggedElement = null;
}

function handleDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  
  if (this !== draggedElement && this.classList.contains('selected')) {
    const afterElement = getDragAfterElement(this.parentNode, e.clientY);
    
    if (afterElement == null) {
      this.parentNode.appendChild(draggedElement);
    } else {
      this.parentNode.insertBefore(draggedElement, afterElement);
    }
  }
  
  return false;
}

function handleDrop(e) {
  e.preventDefault();
  e.stopPropagation();
  
  this.classList.remove('drag-over');
  
  if (this !== draggedElement && this.classList.contains('selected')) {
    const columnList = document.getElementById('columnSelectionList');
    const selectedItems = Array.from(columnList.querySelectorAll('.column-item.selected'));
    const newOrder = selectedItems.map(item => item.dataset.column);
    
    selectedColumnsOrder = newOrder;
    
    updateColumnOrderBadges();
    updatePreviewTable();
    
    setTimeout(() => {
      initializeDragAndDrop();
    }, 50);
  }
  
  return false;
}

function getDragAfterElement(container, y) {
  const draggableElements = [...container.querySelectorAll('.column-item.selected:not(.dragging)')];
  
  return draggableElements.reduce((closest, child) => {
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height / 2;
    
    if (offset < 0 && offset > closest.offset) {
      return { offset: offset, element: child };
    } else {
      return closest;
    }
  }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  const modal = document.getElementById('columnCustomizationModal');
  if (modal && modal.classList.contains('show') && e.target === modal) {
    closeColumnCustomizationModal();
  }
});
</script>

@endsection
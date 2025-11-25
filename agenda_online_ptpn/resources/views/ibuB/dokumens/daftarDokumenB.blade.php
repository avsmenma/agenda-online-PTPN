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

  /* Ensure table container is scrollable */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow-x: auto !important;
    overflow-y: visible;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
  }

  .table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }

  /* Enhanced table for better UX */
  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1200px;
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

  /* Enhanced Locked State UX */
  .table-enhanced tbody tr.locked-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    opacity: 0.85;
    position: relative;
    border-left: 4px solid #ffc107 !important;
    box-shadow: inset 0 0 0 1px rgba(255, 193, 7, 0.2);
    transition: all 0.3s ease;
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
    animation: lock-pulse 2s infinite;
  }

  .table-enhanced tbody tr.locked-row::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
      45deg,
      transparent,
      transparent 10px,
      rgba(108, 117, 125, 0.05) 10px,
      rgba(108, 117, 125, 0.05) 20px
    );
    z-index: 1;
    pointer-events: none;
  }

  .table-enhanced tbody tr.locked-row:hover {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    transform: translateX(2px);
    box-shadow: inset 0 0 0 2px rgba(255, 193, 7, 0.4), 0 2px 8px rgba(255, 193, 7, 0.2);
  }

  /* Lock animation */
  @keyframes lock-pulse {
    0%, 100% {
      opacity: 0.7;
      transform: translateY(-50%) scale(1);
    }
    50% {
      opacity: 0.9;
      transform: translateY(-50%) scale(1.1);
    }
  }

  /* Enhanced locked state indicators */
  .table-enhanced tbody tr.locked-row .col-status .badge-locked {
    animation: badge-glow 2s infinite;
    position: relative;
  }

  .table-enhanced tbody tr.locked-row .col-status .badge-locked::before {
    content: '⚠️';
    position: absolute;
    top: -8px;
    right: -8px;
    font-size: 10px;
    animation: warning-bounce 1.5s infinite;
  }

  /* Enhanced locked button states */
  .table-enhanced tbody tr.locked-row .btn-action.locked {
    position: relative;
    overflow: hidden;
  }

  .table-enhanced tbody tr.locked-row .btn-action.locked::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: button-shimmer 3s infinite;
  }

  .table-enhanced tbody tr.locked-row .btn-action.locked:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 12px rgba(108, 117, 125, 0.3);
  }

  /* Lock hover tooltip enhancement */
  .table-enhanced tbody tr.locked-row:hover .btn-action.locked {
    cursor: not-allowed;
  }

  /* Enhanced deadline column for locked state */
  .table-enhanced tbody tr.locked-row .deadline-empty {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 140, 0, 0.1) 100%);
    border: 1px dashed rgba(255, 193, 7, 0.3);
    color: #856404;
    font-weight: 600;
    animation: deadline-attention 2s infinite;
  }

  /* Responsive Locked State Enhancements */
  @media (max-width: 768px) {
    .table-enhanced tbody tr.locked-row::before {
      font-size: 14px;
      left: -5px;
    }

    .table-enhanced tbody tr.locked-row .col-status .badge-locked::before {
      top: -6px;
      right: -6px;
      font-size: 8px;
    }

    .table-enhanced tbody tr.locked-row:hover {
      transform: translateX(1px);
    }
  }

  @media (max-width: 480px) {
    .table-enhanced tbody tr.locked-row::before {
      font-size: 12px;
    }

    .table-enhanced tbody tr.locked-row .col-status .badge-locked::before {
      display: none; /* Hide warning icon on very small screens */
    }

    /* Reduce animation intensity on mobile for better performance */
    .table-enhanced tbody tr.locked-row .btn-action.locked::before {
      animation-duration: 4s; /* Slower shimmer on mobile */
    }
  }

  /* Animations for enhanced UX */
  @keyframes badge-glow {
    0%, 100% {
      box-shadow: 0 0 5px rgba(255, 193, 7, 0.5);
    }
    50% {
      box-shadow: 0 0 20px rgba(255, 193, 7, 0.8);
    }
  }

  @keyframes warning-bounce {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-3px);
    }
  }

  @keyframes button-shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
  }

  @keyframes deadline-attention {
    0%, 100% {
      opacity: 0.8;
    }
    50% {
      opacity: 1;
    }
  }

  /* Reduced motion support for accessibility */
  @media (prefers-reduced-motion: reduce) {
    .table-enhanced tbody tr.locked-row::before,
    .table-enhanced tbody tr.locked-row .col-status .badge-locked,
    .table-enhanced tbody tr.locked-row .btn-action.locked::before,
    .table-enhanced tbody tr.locked-row .deadline-empty {
      animation: none;
    }
  }

  .table-enhanced td {
    padding: 12px;
    vertical-align: middle;
    border-right: 1px solid #e0e0e0;
    white-space: nowrap;
  }

  /* Column width optimization */
  .table-enhanced .col-no { width: 80px; min-width: 80px; }
  .table-enhanced .col-surat { width: 120px; min-width: 120px; }
  .table-enhanced .col-spp { width: 140px; min-width: 140px; }
  .table-enhanced .col-uraian { width: 250px; min-width: 200px; }
  .table-enhanced .col-nilai { width: 120px; min-width: 120px; }
  .table-enhanced .col-deadline { width: 160px; min-width: 140px; }
  .table-enhanced .col-status { width: 120px; min-width: 100px; }
  .table-enhanced .col-action { width: 120px; min-width: 120px; }
  .table-enhanced .col-paraf { width: 120px; min-width: 120px; }

  .table-enhanced .col-sticky {
    position: sticky;
    left: 0;
    background: white;
    z-index: 5;
  }

  /* Mobile optimization */
  @media (max-width: 768px) {
    .table-dokumen {
      border-radius: 8px;
      box-shadow: 0 2px 15px rgba(8, 62, 64, 0.05);
    }

    .table-enhanced {
      min-width: 600px;
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
    .table-enhanced .col-surat { width: 80px; min-width: 80px; }
    .table-enhanced .col-spp { width: 100px; min-width: 100px; }
    .table-enhanced .col-uraian { width: 150px; min-width: 150px; }
    .table-enhanced .col-nilai { width: 80px; min-width: 80px; }
    .table-enhanced .col-deadline { width: 100px; min-width: 100px; }
    .table-enhanced .col-status { width: 80px; min-width: 80px; }
    .table-enhanced .col-action { width: 80px; min-width: 80px; }
    .table-enhanced .col-paraf { width: 80px; min-width: 80px; }
  }

  @media (max-width: 480px) {
    .table-enhanced {
      min-width: 480px;
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
    .table-enhanced .col-surat { width: 70px; min-width: 70px; }
    .table-enhanced .col-spp { width: 80px; min-width: 80px; }
    .table-enhanced .col-uraian { width: 120px; min-width: 120px; }
    .table-enhanced .col-nilai { width: 60px; min-width: 60px; }
    .table-enhanced .col-deadline { width: 90px; min-width: 90px; }
    .table-enhanced .col-status { width: 70px; min-width: 70px; }
    .table-enhanced .col-action { width: 70px; min-width: 70px; }
    .table-enhanced .col-paraf { width: 70px; min-width: 70px; }
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
    position: relative;
  }

  .table-dokumen tbody tr.main-row::after {
    content: 'Klik untuk detail';
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    color: #889717;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    background: white;
    padding: 2px 6px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .table-dokumen tbody tr.main-row:hover {
    background: linear-gradient(90deg, rgba(183, 204, 26, 0.05) 0%, transparent 100%);
    border-left: 3px solid #889717;
    transform: scale(1.005);
  }

  .table-dokumen tbody tr.main-row:hover::after {
    opacity: 1;
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

  /* Detail Row Styles */
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

  .detail-content {
    padding: 20px;
    border-top: 2px solid rgba(8, 62, 64, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
  }

  /* Detail Grid - Horizontal Layout */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    margin-top: 0;
    width: 100%;
    box-sizing: border-box;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px; /* Gap untuk background spacing */
    padding: 12px;
    background: #ffffff; /* Putih bersih untuk contrast dengan label */
    border-radius: 8px;
    border: 1px solid #f1f5f9; /* Border yang sangat tipis */
    transition: all 0.2s ease;
    min-width: 0;
    width: 100%;
    overflow: visible;
    box-sizing: border-box;
  }

  .detail-item:hover {
    border-color: #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
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

  /* Badge in detail */
  .detail-value .badge {
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .badge-selesai {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .badge-proses {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .badge-sent {
    background: linear-gradient(135deg, #0401ccff 0%, #020daaff 100%);
    color: white;
  }

  /* Responsive Detail Grid */
  @media (max-width: 1200px) {
    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 12px;
    }
  }

  @media (max-width: 768px) {
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
  }

  @media (max-width: 480px) {
    .detail-grid {
      grid-template-columns: 1fr;
      gap: 8px;
    }

    .detail-item {
      padding: 8px;
    }
  }

  /* Simplified Status System - 3 States */
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

  /* State 1: 🔒 Terkunci (Locked - Waiting for Deadline) */
  .badge-status.badge-locked {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    border-color: #495057;
    position: relative;
    overflow: hidden;
  }

  .badge-status.badge-locked::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shimmer 2s infinite;
  }

  /* State 2: ⏳ Diproses (In Progress) */
  .badge-status.badge-proses {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
  }

  .badge-status.badge-proses::after {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    background: white;
    border-radius: 50%;
    margin-left: 6px;
    animation: pulse 1.5s infinite;
  }

  /* State 3: ✅ Selesai (Completed) */
  .badge-status.badge-selesai {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-color: #28a745;
  }

  /* Special state for returned/rejected documents */
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

  /* Status Dropdown Styles */
  .status-dropdown {
    position: relative;
    display: inline-block;
    z-index: 999;
  }

  /* Ensure status button is clickable */
  .status-button-simple {
    pointer-events: auto !important;
    cursor: pointer !important;
    position: relative;
    z-index: 1000;
  }

  .status-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px !important;
    border: none !important;
    border-radius: 25px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    position: relative;
    min-width: 140px;
    justify-content: space-between;
    pointer-events: auto !important;
    user-select: none;
    z-index: 1000;
  }

  .status-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
  }

  .status-button .dropdown-arrow {
    transition: transform 0.3s ease;
    font-size: 10px;
  }

  .status-dropdown.active .dropdown-arrow {
    transform: rotate(180deg);
  }

  .status-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
    z-index: 9999;
    min-width: 160px;
    margin-top: 4px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    pointer-events: none;
  }

  .status-dropdown.active .status-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
  }

  /* Support for status-menu-visible class used in inline styles */
  .status-menu-visible {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: 0 !important;
    background: white !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(0, 0, 0, 0.2) !important;
    z-index: 99999 !important;
    min-width: 160px !important;
    margin-top: 4px !important;
  }

  .status-dropdown.active .status-menu-visible {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    pointer-events: auto !important;
  }

  .status-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 13px;
    color: #2c3e50;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 0;
  }

  .status-option:first-child {
    border-radius: 12px 12px 0 0;
  }

  .status-option:last-child {
    border-radius: 0 0 12px 12px;
  }

  .status-option:hover {
    background: linear-gradient(135deg, rgba(26, 77, 62, 0.1) 0%, rgba(15, 61, 46, 0.05) 100%);
    color: #1a4d3e;
  }

  .status-option.approve {
    border-bottom: 1px solid #f0f0f0;
  }

  .status-option.approve:hover {
    background: linear-gradient(135deg, rgba(82, 183, 136, 0.1) 0%, rgba(64, 145, 108, 0.05) 100%);
  }

  .status-option.reject:hover {
    background: linear-gradient(135deg, rgba(116, 198, 157, 0.1) 0%, rgba(82, 183, 136, 0.05) 100%);
  }

  .status-option i {
    width: 16px;
    text-align: center;
    font-size: 14px;
  }

  .status-option.approve i {
    color: #40916c;
  }

  .status-option.reject i {
    color: #74c69d;
  }

  /* Loading state */
  .status-button.loading {
    pointer-events: none;
    opacity: 0.7;
  }

  .status-button.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid transparent;
    border-top: 2px solid #1a4d3e;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  /* Special state for sent documents */
  .badge-status.badge-sent {
    background: linear-gradient(135deg, #0401ccff 0%, #020daaff 100%);
    color: white;
    border-color: #0401ccff;
    position: relative;
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

  /* Enhanced hover effects */
  .badge-status:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
  }

  /* Responsive Status Badges */
  @media (max-width: 768px) {
    .badge-status {
      padding: 6px 12px;
      font-size: 11px;
      min-width: 80px;
      gap: 4px;
    }

    .badge-status.badge-proses::after {
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

  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
  }

  .btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }
  .btn-kirim {
    background: linear-gradient(135deg, #0401ccff 0%, #020daaff 100%);
    color: white;
  }


  .btn-view {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .btn-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }

  .btn-action:active {
    transform: translateY(-1px);
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
  }

  .btn-set-deadline:hover {
    background: linear-gradient(135deg, #ff8c00 0%, #e67300 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
  }

  /* Enhanced Responsive Action Buttons */
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
  .btn-action:hover:not(.locked) {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }

  .btn-action:active:not(.locked) {
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

  .btn-return {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 50%, #bd2130 100%);
    color: white;
  }

  .btn-return:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 50%, #a71e2a 100%);
  }


  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 50%, #495057 100%);
    color: white;
    min-width: 36px;
    min-height: 36px;
  }

  .btn-chevron:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 50%, #343a40 100%);
  }

  .btn-chevron.active {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    transform: rotate(180deg);
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

    .btn-chevron {
      min-width: 48px;
      min-height: 36px;
      width: auto;
      max-width: 80px;
    }

    /* Button tooltips on mobile */
    .btn-action[title]:hover::after {
      content: attr(title);
      position: absolute;
      bottom: 100%;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0, 0, 0, 0.9);
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 10px;
      white-space: nowrap;
      z-index: 1000;
      margin-bottom: 4px;
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

    /* Special cases for important buttons */
    .btn-set-deadline {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
      min-width: 48px;
    }

    .btn-set-deadline::before {
      content: '⏰';
      font-size: 14px;
      font-style: normal;
    }

    .btn-set-deadline i {
      display: none;
    }
  }

  /* High contrast mode support */
  @media (prefers-contrast: high) {
    .btn-action {
      border: 2px solid currentColor;
      background: white;
      color: black;
    }

    .btn-edit {
      border-color: #083E40;
      color: #083E40;
    }

    .btn-return {
      border-color: #dc3545;
      color: #dc3545;
    }

    .btn-chevron {
      border-color: #6c757d;
      color: #6c757d;
    }
  }

  /* Dark mode support */
  @media (prefers-color-scheme: dark) {
    .btn-action {
      background: linear-gradient(135deg, #495057 0%, #343a40 100%);
      color: white;
    }

    .btn-action:hover:not(.locked) {
      background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
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

  .btn-tambah {
    padding: 10px 24px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.2);
  }

  .btn-tambah:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(136, 151, 23, 0.3);
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
  .btn-paraf {
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
  .btn-paraf-selesai {
    padding: 10px 24px;
    background: linear-gradient(135deg, #5eff00ff 0%, #07ff13ff 100%);
    color: hitam;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .span-terlambat {
    font-weight: 600;
    color: #ff002bff;
    font-size: 12px;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }
  .span-tepatWaktu {
    font-weight: 600;
    color: #08c757ff;
    font-size: 12px;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }
  .span-proses {
    font-weight: 600;
    color : #ffca2c;
    font-size: 12px;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }

  /* Chevron Icon Animation */
  .chevron-icon {
    transition: transform 0.3s ease;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
    padding: 8px 12px;
  }
</style>

<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>



<!-- Search & Filter Box -->
<div class="search-box">
  <div class="input-group " style="flex: 1; max-width: auto; margin-right: 40%;">
    <span class="input-group-text">
      <i class="fa-solid fa-magnifying-glass text-muted"></i>
    </span>
    <input type="text" class="form-control" placeholder="Search...">
  </div>
  <div class="filter-section" style="margin-right: 20px;">
    <select>
      <option>Year</option>
      <option>2025</option>
      <option>2024</option>
      <option>2023</option>
    </select>
  </div>
  <a href="{{ url('/pengembalian-dokumens') }}"><button class="btn-tambah" style="margin-right: 20px;">Dokumen Dikembalikan</button></a>
  <a href="#"><button class="btn-excel mr-2">Ekspor ke PDF</button></a>
</div>

<!-- Tabel Dokumen -->
<div class="table-responsive table-container">
  <table class="table table-enhanced mb-0">
    <thead>
      <tr>
        <th class="col-no sticky-column">No</th>
        <th class="col-surat">Nomor Surat</th>
        <th class="col-spp">Tanggal Masuk</th>
        <th class="col-spp">Nomor SPP</th>
        <th class="col-spp">Tanggal SPP</th>
        <th class="col-uraian">Uraian Ketidaklengkapan</th>
        <th class="col-deadline">Deadline</th>
        <th class="col-status sticky-column">Status</th>
        <th class="col-action sticky-column">Aksi</th>
        <th class="col-paraf sticky-column">Status Paraf</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dokumens ?? [] as $dokumen)
      @php
        // Fix: Document should only be locked if it has NO deadline AND is in initial sent_to_ibub status
        // Documents returned from departments/bidangs should not be locked even if they have no deadline initially
        $isLocked = is_null($dokumen->deadline_at) && in_array($dokumen->status, ['sent_to_ibub']) && is_null($dokumen->returned_to_department_at) && is_null($dokumen->returned_to_bidang_at);
      @endphp
      <tr class="main-row {{ $isLocked ? 'locked-row' : '' }}" onclick="toggleDetail({{ $dokumen->id }})" title="Klik untuk melihat detail lengkap dokumen">
        <td class="col-no" style="text-align: center;">{{ $loop->iteration }}</td>
        <td class="col-surat">{{ $dokumen->nomor_agenda }}</td>
        <td class="col-spp">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</td>
        <td class="col-spp">{{ $dokumen->nomor_spp }}</td>
        <td class="col-spp">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y H:i') : '-' }}</td>
        <td class="col-uraian">{{ $dokumen->keterangan ?? '-' }}</td>
        <td class="col-deadline">
          @if($dokumen->deadline_at)
            <div class="deadline-info" data-deadline="{{ $dokumen->deadline_at->format('Y-m-d H:i:s') }}" data-doc-id="{{ $dokumen->id }}">
              <!-- Progress Bar -->
              <div class="deadline-progress">
                <div class="deadline-progress-bar" id="progress-{{ $dokumen->id }}" style="width: 0%;"></div>
              </div>

              <!-- Countdown Display -->
              <div class="deadline-countdown">
                <div class="deadline-countdown-icon">
                  <i class="fa-solid fa-clock"></i>
                  <span class="countdown-text" id="countdown-{{ $dokumen->id }}">Loading...</span>
                </div>
                <div class="deadline-countdown-text" id="countdown-short-{{ $dokumen->id }}">--</div>
              </div>

              <!-- Date Display -->
              <div class="deadline-date">
                <i class="fa-solid fa-calendar-alt"></i>
                <small>{{ $dokumen->deadline_at->format('d M Y, H:i') }}</small>
              </div>

              <!-- Note Display -->
              @if($dokumen->deadline_note)
              <div class="deadline-note">
                <i class="fa-solid fa-note-sticky"></i>
                <small>{{ Str::limit($dokumen->deadline_note, 50) }}</small>
              </div>
              @endif
            </div>
          @else
            <div class="deadline-empty">
              <i class="fa-solid fa-clock"></i>
              <span>Belum ditetapkan</span>
            </div>
          @endif
        </td>
        <td class="col-status" style="text-align: center;" onclick="event.stopPropagation()">
          @if($dokumen->status == 'selesai' || $dokumen->status == 'approved_ibub')
            <span class="badge-status badge-selesai">✓ {{ $dokumen->status == 'approved_ibub' ? 'Approved' : 'Selesai' }}</span>
          @elseif($dokumen->status == 'rejected_ibub')
            <span class="badge-status badge-dikembalikan">Rejected</span>
          @elseif($dokumen->status == 'sent_to_perpajakan')
            <span class="badge-status badge-sent">📤 Terkirim ke Perpajakan</span>
          @elseif($dokumen->status == 'sent_to_akutansi')
            <span class="badge-status badge-sent">📤 Terkirim ke Akutansi</span>
          @elseif(in_array($dokumen->status, ['sent_to_ibub']) && !$isLocked)
            <!-- Simple Status Change Buttons -->
            <div class="status-actions" id="status-dropdown-{{ $dokumen->id }}" style="display: flex; gap: 8px; justify-content: center; align-items: center;">
              <!-- Status Badge -->
              <span class="badge-status badge-proses" style="margin: 0;">
                ⏳ Diproses
              </span>

              <!-- Action Buttons -->
              <button
                onclick="quickApprove({{ $dokumen->id }})"
                style="
                  background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%) !important;
                  color: white !important;
                  border: none !important;
                  padding: 6px 12px !important;
                  border-radius: 20px !important;
                  font-size: 11px !important;
                  font-weight: 600 !important;
                  cursor: pointer !important;
                  display: inline-flex !important;
                  align-items: center !important;
                  gap: 4px !important;
                  transition: all 0.3s ease !important;
                  box-shadow: 0 2px 8px rgba(64, 145, 108, 0.3) !important;
                "
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(64, 145, 108, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(64, 145, 108, 0.3)'"
                title="Approve dokumen ini"
              >
                <i class="fas fa-check" style="font-size: 10px;"></i>
                Approve
              </button>

              <button
                onclick="quickReject({{ $dokumen->id }})"
                style="
                  background: linear-gradient(135deg, #74c69d 0%, #52b788 100%) !important;
                  color: white !important;
                  border: none !important;
                  padding: 6px 12px !important;
                  border-radius: 20px !important;
                  font-size: 11px !important;
                  font-weight: 600 !important;
                  cursor: pointer !important;
                  display: inline-flex !important;
                  align-items: center !important;
                  gap: 4px !important;
                  transition: all 0.3s ease !important;
                  box-shadow: 0 2px 8px rgba(116, 198, 157, 0.3) !important;
                "
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(116, 198, 157, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(116, 198, 157, 0.3)'"
                title="Reject dokumen ini"
              >
                <i class="fas fa-times" style="font-size: 10px;"></i>
                Reject
              </button>
            </div>
          @elseif($dokumen->status == 'sent_to_ibub' && $isLocked)
            <span class="badge-status badge-locked">🔒 Terkunci</span>
          @elseif($dokumen->status == 'returned_to_ibua')
            <span class="badge-status badge-dikembalikan">Dikembalikan</span>
          @else
            <span class="badge-status badge-proses">⏳ {{ ucfirst($dokumen->status) }}</span>
          @endif
        </td>
        <td class="col-action" onclick="event.stopPropagation()">
          <div class="action-buttons">
            @if(in_array($dokumen->status, ['sent_to_perpajakan', 'sent_to_akutansi']))
              <!-- Document already sent - show sent status -->
              <button class="btn-action btn-edit locked" disabled title="Dokumen sudah terkirim, tidak dapat diedit">
                <i class="fa-solid fa-check-circle"></i>
                <span>Terkirim</span>
              </button>
            @elseif($isLocked)
              <!-- Locked state - buttons disabled -->
              <button class="btn-action btn-edit locked" disabled title="Edit terkunci - tentukan deadline terlebih dahulu">
                <i class="fa-solid fa-lock"></i>
              </button>
              <button type="button" class="btn-action btn-return locked" disabled title="Kembalikan terkunci - tentukan deadline terlebih dahulu" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <i class="fa-solid fa-lock"></i>
              </button>
              <button type="button" class="btn-action locked" disabled title="Kirim terkunci - tentukan deadline terlebih dahulu" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <i class="fa-solid fa-lock"></i>
              </button>
              <button type="button" class="btn-action btn-set-deadline" onclick="openSetDeadlineModal({{ $dokumen->id }})" title="Tetapkan Deadline" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                <i class="fa-solid fa-clock"></i>
              </button>
            @else
              <!-- Unlocked state - buttons enabled -->
              <a href="{{ route('dokumensB.edit', $dokumen->id) }}" title="Edit Dokumen">
                <button class="btn-action btn-edit">
                  <i class="fa-solid fa-pen"></i>
                  <span>Edit</span>
                </button>
              </a>
              @if(in_array($dokumen->status, ['sent_to_ibub', 'approved_ibub', 'sedang diproses']))
              <button type="button" class="btn-action btn-return" onclick="openReturnToBidangModal({{ $dokumen->id }})" title="Kembalikan ke Bidang">
                <i class="fa-solid fa-undo"></i>
                <span>Return</span>
              </button>
              @endif
              @if(in_array($dokumen->status, ['sent_to_ibub', 'approved_ibub', 'sedang diproses']))
              <button type="button" class="btn-action btn-kirim" onclick="openSendToNextModal({{ $dokumen->id }})" title="Kirim ke Perpajakan/Akutansi">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim</span>
              </button>
              @endif
            @endif
          </div>
        </td>
        <td class="col-paraf" onclick="event.stopPropagation()">
          <div class="action-buttons">
            @if(in_array($dokumen->status, ['approved_ibub', 'selesai']))
              <button class="btn-paraf-selesai" disabled>Selesai</button>
            @else
              <button class="btn-paraf" onclick="confirmParaf({{ $dokumen->id }})">Paraf</button>
            @endif
          </div>
        </td>
      </tr>
      <tr class="detail-row" id="detail-{{ $dokumen->id }}">
        <td colspan="10">
          <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
            <div class="text-center p-4">
              <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="10" class="text-center" style="padding: 40px;">
          <i class="fa-solid fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
          <p style="color: #999; font-size: 14px;">Belum ada dokumen</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div class="pagination">
  <button>«</button>
  <button class="active">1</button>
  <button>2</button>
  <button>3</button>
  <button>4</button>
  <button>5</button>
  <button>»</button>
</div>

<!-- Modal Alasan Pengembalian -->

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
    // Hide detail
    detailRow.classList.remove('show');
    mainRow.classList.remove('selected');
  } else {
    // Show detail
    loadDocumentDetail(docId);
    detailRow.classList.add('show');
    mainRow.classList.add('selected');

    // Smooth scroll to detail
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

  // Show loading state
  detailContent.innerHTML = `
    <div class="text-center p-4">
      <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
    </div>
  `;

  fetch(`/dokumens/${docId}/detail`)
    .then(response => response.text())
    .then(html => {
      detailContent.innerHTML = html;

      // Initialize countdown for this detail if needed
      if (typeof initializeCountdowns === 'function') {
        setTimeout(() => initializeCountdowns(), 100);
      }
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

function confirmParaf(dokumenId) {
  if (confirm("Yakin mau menandai bahwa dokumen ini telah diparaf (selesai)?")) {
    // Implementation for paraf action
    console.log("Paraf confirmed for document:", dokumenId);
  }
}

// Global variables for approve/reject
window.currentApproveDocId = null;
window.currentRejectDocId = null;

// Simple approve function - Updated to use Modal
function quickApprove(docId) {
  console.log('Quick approve called for docId:', docId);

  // Find the document row to get nomor agenda
  const row = document.querySelector(`[onclick*="quickApprove(${docId})"]`)?.closest('tr');
  const nomorAgenda = row?.querySelector('.col-surat')?.textContent?.trim() || 'N/A';

  // Set global variable
  window.currentApproveDocId = docId;

  // Update modal content
  document.getElementById('approve-confirm-nomor-agenda').textContent = nomorAgenda;

  // Show modal
  const approveModal = new bootstrap.Modal(document.getElementById('approveConfirmModal'));
  approveModal.show();
}

// Simple reject function - Updated to use Modal
function quickReject(docId) {
  console.log('Quick reject called for docId:', docId);

  // Find the document row to get nomor agenda
  const row = document.querySelector(`[onclick*="quickReject(${docId})"]`)?.closest('tr');
  const nomorAgenda = row?.querySelector('.col-surat')?.textContent?.trim() || 'N/A';

  // Set global variable
  window.currentRejectDocId = docId;

  // Update modal content
  document.getElementById('reject-confirm-nomor-agenda').textContent = nomorAgenda;
  document.getElementById('reject-reason-input').value = '';
  document.getElementById('reject-reason-error').style.display = 'none';

  // Show modal
  const rejectModal = new bootstrap.Modal(document.getElementById('rejectConfirmModal'));
  rejectModal.show();
}

// Handle confirm approve button
document.getElementById('confirm-approve-btn')?.addEventListener('click', function() {
  const docId = window.currentApproveDocId;
  if (!docId) {
    alert('Error: Document ID tidak ditemukan');
    return;
  }

  // Hide modal
  const approveModal = bootstrap.Modal.getInstance(document.getElementById('approveConfirmModal'));
  approveModal?.hide();

  // Call approve function
  approveDocumentUniversal(docId);

  // Reset global variable
  window.currentApproveDocId = null;
});

// Handle confirm reject button
document.getElementById('confirm-reject-btn')?.addEventListener('click', function() {
  const docId = window.currentRejectDocId;
  const reason = document.getElementById('reject-reason-input').value.trim();
  const errorDiv = document.getElementById('reject-reason-error');

  if (!docId) {
    alert('Error: Document ID tidak ditemukan');
    return;
  }

  // Validate reason
  if (!reason || reason.length < 10) {
    errorDiv.textContent = 'Alasan penolakan harus diisi minimal 10 karakter!';
    errorDiv.style.display = 'block';
    document.getElementById('reject-reason-input').classList.add('is-invalid');
    return;
  }

  // Hide modal
  const rejectModal = bootstrap.Modal.getInstance(document.getElementById('rejectConfirmModal'));
  rejectModal?.hide();

  // Call reject function
  rejectDocumentUniversal(docId, reason);

  // Reset global variable
  window.currentRejectDocId = null;
});

// Approve document using Universal Approval System
function approveDocumentUniversal(docId) {
  const statusContainer = findStatusContainer(docId);
  if (!statusContainer) {
    console.error('Status container not found for docId:', docId);
    return;
  }

  const originalHTML = statusContainer.innerHTML;
  statusContainer.innerHTML = `
    <div style="display: flex; align-items: center; gap: 8px; justify-content: center; color: #28a745; font-weight: 600; font-size: 12px;">
      <i class="fa-solid fa-spinner fa-spin"></i>
      <span>Menyetujui...</span>
    </div>
  `;

  fetch(`/universal-approval/${docId}/approve`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message || 'Dokumen berhasil disetujui', 'success');

      statusContainer.innerHTML = `
        <span class="badge-status badge-selesai">
          <i class="fas fa-check-circle"></i> Approved
        </span>
      `;

      // Reload after 1.5 seconds
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      showNotification(data.message || 'Gagal menyetujui dokumen', 'error');
      statusContainer.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Terjadi kesalahan saat menyetujui dokumen.', 'error');
    statusContainer.innerHTML = originalHTML;
  });
}

// Reject document using Universal Approval System
function rejectDocumentUniversal(docId, reason) {
  const statusContainer = findStatusContainer(docId);
  if (!statusContainer) {
    console.error('Status container not found for docId:', docId);
    return;
  }

  const originalHTML = statusContainer.innerHTML;
  statusContainer.innerHTML = `
    <div style="display: flex; align-items: center; gap: 8px; justify-content: center; color: #dc3545; font-weight: 600; font-size: 12px;">
      <i class="fa-solid fa-spinner fa-spin"></i>
      <span>Menolak...</span>
    </div>
  `;

  fetch(`/universal-approval/${docId}/reject`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      rejection_reason: reason
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message || 'Dokumen berhasil ditolak', 'success');

      statusContainer.innerHTML = `
        <span class="badge-status badge-dikembalikan">
          <i class="fas fa-times-circle"></i> Rejected
        </span>
      `;

      // Reload after 1.5 seconds
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      showNotification(data.message || 'Gagal menolak dokumen', 'error');
      statusContainer.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Terjadi kesalahan saat menolak dokumen.', 'error');
    statusContainer.innerHTML = originalHTML;
  });
}

// Helper function to find status container
function findStatusContainer(docId) {
  let statusContainer = document.querySelector(`#status-dropdown-${docId}`);
  if (!statusContainer) {
    statusContainer = document.querySelector(`[onclick*="quickApprove(${docId})"]`)?.closest('td');
  }
  if (!statusContainer) {
    statusContainer = document.querySelector(`[onclick*="quickReject(${docId})"]`)?.closest('td');
  }
  return statusContainer;
}

// Simplified status change function
function changeDocumentStatus(docId, action, event) {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }

  console.log('Change status called for docId:', docId, 'action:', action);

  // Try multiple selectors to find the status container
  let statusContainer = document.querySelector(`#status-dropdown-${docId}`);
  if (!statusContainer) {
    statusContainer = document.querySelector(`[onclick*="quickApprove(${docId})"]`).closest('td');
  }
  if (!statusContainer) {
    statusContainer = document.querySelector(`[onclick*="quickReject(${docId})"]`).closest('td');
  }
  if (!statusContainer) {
    statusContainer = document.querySelector(`tr:has([onclick*="${docId}"]) .col-status`);
  }

  if (!statusContainer) {
    console.error('Status container not found for docId:', docId);
    console.log('Available containers with docId:', document.querySelectorAll(`[onclick*="${docId}"]`));
    return;
  }

  console.log('Found status container:', statusContainer);

  // Show loading state
  const originalHTML = statusContainer.innerHTML;
  statusContainer.innerHTML = `
    <div style="
      display: flex;
      align-items: center;
      gap: 8px;
      justify-content: center;
      color: #1a4d3e;
      font-weight: 600;
      font-size: 12px;
    ">
      <i class="fa-solid fa-spinner fa-spin"></i>
      <span>Processing...</span>
    </div>
  `;

  // Send AJAX request
  fetch(`/dokumensB/${docId}/change-status`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      status: action
    })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      // Show success notification
      showNotification(data.message, 'success');
      console.log('Status changed successfully:', data);

      // Update display
      const statusText = action === 'approved' ? 'Approved' : 'Rejected';
      const badgeClass = action === 'approved' ? 'badge-selesai' : 'badge-dikembalikan';

      statusContainer.innerHTML = `
        <span class="badge-status ${badgeClass}">
          ${action === 'approved' ? '✓' : '✗'} ${statusText}
        </span>
      `;

      // Update action buttons for this row
      updateActionButtons(docId, action);

      // Refresh page after delay to update dashboard stats
      setTimeout(() => {
        window.location.reload();
      }, 2000);

    } else {
      // Show error notification
      showNotification(data.message, 'error');
      console.error('Status change failed:', data);
      statusContainer.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Terjadi kesalahan saat mengubah status dokumen.', 'error');
    statusContainer.innerHTML = originalHTML;
  });
}

function updateActionButtons(docId, action) {
  const row = document.querySelector(`tr:has(#status-dropdown-${docId})`);
  if (!row) return;

  const actionCell = row.querySelector('.col-action');
  if (!actionCell) return;

  // Disable action buttons based on status
  const actionButtons = actionCell.querySelectorAll('.btn-action');
  actionButtons.forEach(btn => {
    if (action === 'approved') {
      // Keep some buttons for approved documents
      if (!btn.classList.contains('btn-edit') && !btn.classList.contains('btn-paraf')) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
      }
    } else if (action === 'rejected') {
      // Disable most action buttons for rejected documents
      btn.disabled = true;
      btn.style.opacity = '0.5';
      btn.style.cursor = 'not-allowed';
    }
  });
}


// Optimized countdown timer with performance improvements
let countdownUpdateInterval = null;
let countdownTimers = new Map();

function initializeCountdowns() {
  // Clear existing timers to prevent memory leaks
  if (countdownUpdateInterval) {
    clearInterval(countdownUpdateInterval);
  }
  countdownTimers.forEach(timer => clearInterval(timer));
  countdownTimers.clear();

  const deadlineElements = document.querySelectorAll('.deadline-info');
  if (deadlineElements.length === 0) return;

  // Collect all countdown data with progress bar support
  const countdowns = [];
  deadlineElements.forEach(deadlineInfo => {
    const deadlineStr = deadlineInfo.dataset.deadline;
    const docId = deadlineInfo.dataset.docId;
    const countdownEl = document.getElementById(`countdown-${docId}`);
    const progressEl = document.getElementById(`progress-${docId}`);
    const shortCountdownEl = document.getElementById(`countdown-short-${docId}`);

    if (!countdownEl || !deadlineStr) return;

    const deadline = new Date(deadlineStr.replace(' ', 'T'));
    const countdownText = countdownEl.querySelector('.countdown-text');

    if (!countdownText) return;

    // Calculate total time for progress bar (from document creation to deadline)
    const createdAt = new Date(deadlineInfo.dataset.created_at || deadlineStr.replace(' ', 'T'));
    const totalTime = deadline - createdAt;
    const elapsed = Date.now() - createdAt;

    countdowns.push({
      deadline,
      countdownEl,
      progressEl,
      shortCountdownEl,
      countdownText,
      deadlineInfo,
      docId,
      totalTime,
      createdAt
    });
  });

  if (countdowns.length === 0) return;

  // Enhanced update function with progress bars and status classes
  function updateAllCountdowns() {
    const now = new Date();
    let hasUrgentDeadlines = false;

    countdowns.forEach(countdown => {
      const distance = countdown.deadline - now;
      const countdownText = countdown.countdownText;
      const countdownEl = countdown.countdownEl;
      const progressEl = countdown.progressEl;
      const shortCountdownEl = countdown.shortCountdownEl;
      const deadlineInfo = countdown.deadlineInfo;

      // Remove all status classes first
      deadlineInfo.classList.remove('status-safe', 'status-warning', 'status-danger', 'status-overdue');

      if (distance < 0) {
        // Overdue deadline
        const daysOverdue = Math.abs(Math.floor(distance / (1000 * 60 * 60 * 24)));
        countdownText.innerHTML = `<i class="fa-solid fa-exclamation-triangle me-1"></i>${daysOverdue} hari terlambat`;
        if (shortCountdownEl) {
          shortCountdownEl.textContent = `Terlambat ${daysOverdue}h`;
        }
        if (progressEl) {
          progressEl.style.width = '100%';
        }
        deadlineInfo.classList.add('status-overdue');
        return;
      }

      // Calculate progress percentage
      let progressPercentage = 0;
      if (countdown.totalTime > 0) {
        const elapsed = now - countdown.createdAt;
        progressPercentage = Math.min(100, Math.max(0, (elapsed / countdown.totalTime) * 100));
      }

      // Calculate time units
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

      // Update display and status based on remaining time
      if (days >= 2) {
        // Safe zone (2+ days)
        countdownText.innerHTML = `<i class="fa-solid fa-clock me-1"></i>${days} hari ${hours} jam`;
        if (shortCountdownEl) {
          shortCountdownEl.textContent = `${days}h ${hours}j`;
        }
        deadlineInfo.classList.add('status-safe');
      } else if (days >= 1) {
        // Warning zone (1-2 days)
        countdownText.innerHTML = `<i class="fa-solid fa-clock me-1"></i>${days} hari ${hours} jam`;
        if (shortCountdownEl) {
          shortCountdownEl.textContent = `${days}h ${hours}j`;
        }
        deadlineInfo.classList.add('status-warning');
      } else if (hours >= 6) {
        // Warning zone (6-24 hours)
        countdownText.innerHTML = `<i class="fa-solid fa-exclamation-triangle me-1"></i>${hours} jam ${minutes} menit`;
        if (shortCountdownEl) {
          shortCountdownEl.textContent = `${hours}j ${minutes}m`;
        }
        deadlineInfo.classList.add('status-warning');
        hasUrgentDeadlines = true;
      } else {
        // Danger zone (< 6 hours)
        countdownText.innerHTML = `<i class="fa-solid fa-exclamation-circle me-1"></i>${hours} jam ${minutes} menit`;
        if (shortCountdownEl) {
          shortCountdownEl.textContent = `${hours}j ${minutes}m`;
        }
        deadlineInfo.classList.add('status-danger');
        hasUrgentDeadlines = true;
      }

      // Update progress bar
      if (progressEl) {
        progressEl.style.width = `${progressPercentage}%`;
      }
    });

    // Adjust update frequency based on urgency for performance optimization
    const newInterval = hasUrgentDeadlines ? 30000 : 120000; // 30s for urgent, 2min for normal
    if (countdownUpdateInterval && countdownUpdateInterval._interval !== newInterval) {
      clearInterval(countdownUpdateInterval);
      countdownUpdateInterval = setInterval(updateAllCountdowns, newInterval);
      countdownUpdateInterval._interval = newInterval;
    }
  }

  // Initial update
  updateAllCountdowns();

  // Set interval with initial 1-minute update
  countdownUpdateInterval = setInterval(updateAllCountdowns, 60000);
  countdownUpdateInterval._interval = 60000;
}

// Use efficient page load detection
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeCountdowns);
} else {
  // DOM already loaded
  initializeCountdowns();
}

// Optimized re-initialization - debounced and limited scope
let reinitializeTimeout = null;
function scheduleReinitialize() {
  if (reinitializeTimeout) {
    clearTimeout(reinitializeTimeout);
  }
  reinitializeTimeout = setTimeout(initializeCountdowns, 500);
}

// Cleanup on page unload to prevent memory leaks
window.addEventListener('beforeunload', function() {
  if (countdownUpdateInterval) {
    clearInterval(countdownUpdateInterval);
  }
  countdownTimers.forEach(timer => clearInterval(timer));
});
</script>

<style>
/* Optimized Deadline Display System */
.deadline-info {
  position: relative;
  padding: 10px;
  border-radius: 12px;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  border: 1px solid rgba(8, 62, 64, 0.1);
  transition: all 0.3s ease;
  overflow: hidden;
}

.deadline-info::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, var(--deadline-color, #28a745) 0%, var(--deadline-color-dark, #1e7e34) 100%);
  transition: all 0.3s ease;
}

.deadline-info:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(8, 62, 64, 0.15);
}

/* Progress Bar Container */
.deadline-progress {
  width: 100%;
  height: 6px;
  background: rgba(8, 62, 64, 0.1);
  border-radius: 3px;
  margin-bottom: 8px;
  overflow: hidden;
  position: relative;
}

.deadline-progress-bar {
  height: 100%;
  background: linear-gradient(90deg, var(--deadline-color, #28a745) 0%, var(--deadline-color-light, #34ce57) 100%);
  border-radius: 3px;
  transition: all 0.5s ease;
  position: relative;
  overflow: hidden;
}

.deadline-progress-bar::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  animation: progress-shimmer 2s infinite;
}

@keyframes progress-shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* Deadline Status States */
.deadline-info.status-safe {
  --deadline-color: #28a745;
  --deadline-color-dark: #1e7e34;
  --deadline-color-light: #34ce57;
}

.deadline-info.status-warning {
  --deadline-color: #ffc107;
  --deadline-color-dark: #e0a800;
  --deadline-color-light: #ffcd39;
}

.deadline-info.status-danger {
  --deadline-color: #dc3545;
  --deadline-color-dark: #c82333;
  --deadline-color-light: #e4606d;
}

.deadline-info.status-overdue {
  --deadline-color: #6f42c1;
  --deadline-color-dark: #59359a;
  --deadline-color-light: #7950b2;
  animation: overdue-pulse 2s infinite;
}

@keyframes overdue-pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(111, 66, 193, 0.4);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(111, 66, 193, 0);
  }
}

/* Optimized Countdown Display */
.deadline-countdown {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 6px;
  font-weight: 600;
  font-size: 13px;
  color: #083E40;
}

.deadline-countdown-icon {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
}

.deadline-countdown-text {
  font-weight: 700;
  flex: 1;
  text-align: right;
}

/* Deadline Date and Note */
.deadline-date {
  font-size: 11px;
  color: #666;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.deadline-date small {
  font-weight: 500;
}

.deadline-note {
  font-size: 10px;
  color: #888;
  margin-top: 6px;
  padding-top: 6px;
  border-top: 1px solid rgba(8, 62, 64, 0.1);
  font-style: italic;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Responsive Deadline Display */
@media (max-width: 768px) {
  .deadline-info {
    padding: 8px;
  }

  .deadline-progress {
    height: 5px;
    margin-bottom: 6px;
  }

  .deadline-countdown {
    font-size: 12px;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }

  .deadline-countdown-text {
    text-align: left;
  }

  .deadline-date {
    font-size: 10px;
  }

  .deadline-note {
    font-size: 9px;
  }
}

@media (max-width: 480px) {
  .deadline-info {
    padding: 6px;
  }

  .deadline-progress {
    height: 4px;
    margin-bottom: 4px;
  }

  .deadline-countdown {
    font-size: 11px;
  }

  .deadline-date {
    display: none; /* Hide date on very small screens */
  }

  .deadline-note {
    margin-top: 4px;
    padding-top: 4px;
  }
}

/* No deadline state */
.deadline-empty {
  color: #999;
  font-size: 12px;
  font-style: italic;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px;
}
</style>

<!-- Modal for Setting Deadline -->
<div class="modal fade" id="setDeadlineModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-clock me-2"></i>Tetapkan Deadline Verifikasi
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="deadlineDocId">

        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 140, 0, 0.1) 100%); border-left: 4px solid #ffc107;">
          <i class="fa-solid fa-info-circle me-2"></i>
          <strong>Penting:</strong> Setelah deadline ditetapkan, dokumen akan terbuka untuk diproses lebih lanjut.
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">
            <i class="fa-solid fa-calendar-days me-2"></i>Periode Deadline*
          </label>
          <select class="form-select" id="deadlineDays" required>
            <option value="">Pilih periode deadline</option>
            <option value="1">1 hari</option>
            <option value="2">2 hari</option>
            <option value="3">3 hari (maksimal)</option>
          </select>
          <div class="form-text">Maksimal deadline adalah 3 hari untuk efisiensi proses</div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">
            <i class="fa-solid fa-sticky-note me-2"></i>Catatan Deadline <span class="text-muted">(opsional)</span>
          </label>
          <textarea class="form-control" id="deadlineNote" rows="3"
                    placeholder="Contoh: Perlu verifikasi dokumen pendukung tambahan..."
                    maxlength="500"></textarea>
          <div class="form-text">
            <span id="charCount">0</span>/500 karakter
          </div>
        </div>

        <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.05) 0%, rgba(255, 140, 0, 0.05) 100%);">
          <i class="fa-solid fa-exclamation-triangle me-2"></i>
          <small>
            <strong>Catatan:</strong> Deadline yang telah ditetapkan tidak dapat diubah kembali. Pastikan periode yang dipilih sudah sesuai.
          </small>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-warning" onclick="confirmSetDeadline()">
          <i class="fa-solid fa-check me-2"></i>Tetapkan Deadline
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Sending to Next Handler -->
<div class="modal fade" id="sendToNextModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
        <h5 class="modal-title">Kirim Dokumen ke Bidang Berikutnya</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="nextDocId">

        <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, rgba(8, 62, 64, 0.1) 0%, rgba(136, 151, 23, 0.1) 100%); border-left: 4px solid #083E40;">
          <i class="fa-solid fa-info-circle me-2"></i>
          <strong>Catatan:</strong> Deadline akan ditetapkan oleh departemen tujuan (Perpajakan atau Akutansi) setelah dokumen diterima.
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">
            <i class="fa-solid fa-location-arrow me-2"></i>Pilih Tujuan Pengiriman:
          </label>
          <div class="form-check mb-3" style="border: 2px solid rgba(8, 62, 64, 0.1); border-radius: 8px; padding: 12px; transition: all 0.3s ease;">
            <input class="form-check-input" type="radio" name="next_handler" id="perpajakan" value="perpajakan" required>
            <label class="form-check-label w-100" for="perpajakan" style="cursor: pointer;">
              <div class="d-flex align-items-start">
                <i class="fa-solid fa-receipt me-3 mt-1" style="color: #083E40; font-size: 20px;"></i>
                <div>
                  <strong style="color: #083E40;">Perpajakan</strong>
                  <small class="text-muted d-block">Untuk dokumen yang perlu diproses perpajakan terlebih dahulu. Dokumen akan terkunci hingga perpajakan menetapkan deadline.</small>
                </div>
              </div>
            </label>
          </div>
          <div class="form-check" style="border: 2px solid rgba(8, 62, 64, 0.1); border-radius: 8px; padding: 12px; transition: all 0.3s ease;">
            <input class="form-check-input" type="radio" name="next_handler" id="akutansi" value="akutansi">
            <label class="form-check-label w-100" for="akutansi" style="cursor: pointer;">
              <div class="d-flex align-items-start">
                <i class="fa-solid fa-calculator me-3 mt-1" style="color: #083E40; font-size: 20px;"></i>
                <div>
                  <strong style="color: #083E40;">Akutansi Langsung</strong>
                  <small class="text-muted d-block">Untuk dokumen yang bisa langsung ke akutansi. Dokumen akan terkunci hingga akutansi menetapkan deadline.</small>
                </div>
              </div>
            </label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="confirmSendToNext()">
          <i class="fa-solid fa-paper-plane me-2"></i>Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Send Destination Warning -->
<div class="modal fade" id="sendDestinationWarningModal" tabindex="-1" aria-labelledby="sendDestinationWarningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title" id="sendDestinationWarningModalLabel">
          <i class="fa-solid fa-exclamation-triangle me-2"></i>Perhatian
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-exclamation-circle" style="font-size: 52px; color: #ffc107;"></i>
        </div>
        <h5 class="fw-bold mb-3">Pilih Tujuan Pengiriman Terlebih Dahulu!</h5>
        <p class="text-muted mb-0">
          Silakan pilih tujuan pengiriman dokumen terlebih dahulu:
          <br>• <strong>Perpajakan</strong> - untuk dokumen yang perlu diproses perpajakan terlebih dahulu
          <br>• <strong>Akutansi Langsung</strong> - untuk dokumen yang bisa langsung ke akutansi
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-check me-2"></i>Mengerti
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Send Success -->
<div class="modal fade" id="sendSuccessModal" tabindex="-1" aria-labelledby="sendSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #11823b 0%, #1cb666 100%); color: white;">
        <h5 class="modal-title" id="sendSuccessModalLabel">
          <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #1cb666;"></i>
        </div>
        <h5 class="fw-bold mb-2">Dokumen berhasil dikirim!</h5>
        <p class="text-muted mb-0" id="sendSuccessMessage">
          Dokumen telah dikirim dan akan muncul di halaman tujuan.
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

<!-- Modal for Deadline Success -->
<div class="modal fade" id="deadlineSuccessModal" tabindex="-1" aria-labelledby="deadlineSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title" id="deadlineSuccessModalLabel">
          <i class="fa-solid fa-circle-check me-2"></i>Deadline Berhasil Ditentukan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #ffc107;"></i>
        </div>
        <h5 class="fw-bold mb-2">Deadline berhasil ditetapkan!</h5>
        <p class="text-muted mb-0" id="deadlineSuccessMessage">
          Dokumen sekarang terbuka untuk diproses lebih lanjut.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal" id="deadlineSuccessBtn">
          <i class="fa-solid fa-check me-2"></i>Selesai
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Deadline Warning -->
<div class="modal fade" id="deadlineWarningModal" tabindex="-1" aria-labelledby="deadlineWarningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title" id="deadlineWarningModalLabel">
          <i class="fa-solid fa-exclamation-triangle me-2"></i>Perhatian
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-exclamation-circle" style="font-size: 52px; color: #ffc107;"></i>
        </div>
        <h5 class="fw-bold mb-3">Pilih Periode Deadline Terlebih Dahulu!</h5>
        <p class="text-muted mb-0">
          Silakan pilih periode deadline (1 hari, 2 hari, atau 3 hari) sebelum menetapkan deadline.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-check me-2"></i>Mengerti
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Approve Confirmation -->
<div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-labelledby="approveConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title" id="approveConfirmModalLabel">
          <i class="fa-solid fa-check-circle me-2"></i>Konfirmasi Persetujuan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 64px; color: #28a745;"></i>
        </div>
        <h5 class="fw-bold mb-3">Apakah Anda yakin ingin menyetujui dokumen ini?</h5>
        <div class="alert alert-info text-start">
          <p class="mb-2"><strong>Nomor Agenda:</strong> <span id="approve-confirm-nomor-agenda">-</span></p>
          <p class="mb-0"><strong>Tindakan:</strong> Dokumen akan masuk ke daftar dokumen Anda untuk diproses lebih lanjut.</p>
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success px-4" id="confirm-approve-btn">
          <i class="fa-solid fa-check me-2"></i>Ya, Setujui Dokumen
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Reject Confirmation -->
<div class="modal fade" id="rejectConfirmModal" tabindex="-1" aria-labelledby="rejectConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
        <h5 class="modal-title" id="rejectConfirmModalLabel">
          <i class="fa-solid fa-times-circle me-2"></i>Tolak Dokumen
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <i class="fa-solid fa-exclamation-triangle" style="font-size: 64px; color: #dc3545;"></i>
        </div>
        <h5 class="fw-bold mb-3 text-center">Alasan Penolakan Dokumen</h5>
        <div class="alert alert-warning">
          <p class="mb-2"><strong>Nomor Agenda:</strong> <span id="reject-confirm-nomor-agenda">-</span></p>
          <p class="mb-0 text-danger"><strong>Perhatian:</strong> Dokumen akan dikembalikan ke pengirim dengan alasan yang Anda berikan.</p>
        </div>
        <div class="form-group mt-3">
          <label for="reject-reason-input" class="fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
          <textarea class="form-control" id="reject-reason-input" rows="4"
                    placeholder="Tuliskan alasan penolakan dokumen ini..."
                    style="border-radius: 10px; border: 2px solid #e0e0e0;"></textarea>
          <small class="text-muted">Minimal 10 karakter</small>
          <div class="invalid-feedback" id="reject-reason-error" style="display: none;"></div>
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger px-4" id="confirm-reject-btn">
          <i class="fa-solid fa-ban me-2"></i>Tolak Dokumen
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function openSetDeadlineModal(docId) {
  document.getElementById('deadlineDocId').value = docId;
  document.getElementById('deadlineDays').value = '';
  document.getElementById('deadlineNote').value = '';
  document.getElementById('charCount').textContent = '0';
  const modal = new bootstrap.Modal(document.getElementById('setDeadlineModal'));
  modal.show();
}

function confirmSetDeadline() {
  const docId = document.getElementById('deadlineDocId').value;
  const deadlineDays = document.getElementById('deadlineDays').value;
  const deadlineNote = document.getElementById('deadlineNote').value;

  if (!deadlineDays) {
    // Show warning modal instead of alert
    const warningModal = new bootstrap.Modal(document.getElementById('deadlineWarningModal'));
    warningModal.show();
    return;
  }

  // Show loading state
  const submitBtn = document.querySelector('[onclick="confirmSetDeadline()"]');
  const originalHTML = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

  // Check CSRF token availability
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!csrfToken) {
    console.error('CSRF token not found!');
    alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
    return;
  }

  // Type casting untuk memastikan integer
  const deadlineDaysInt = parseInt(deadlineDays);

  console.log('Sending request to: ', `/dokumensB/${docId}/set-deadline`);
  console.log('Request payload: ', {
    deadline_days: deadlineDaysInt,
    deadline_note: deadlineNote
  });
  console.log('Deadline days type: ' + typeof deadlineDaysInt + ' value: ' + deadlineDaysInt);

  fetch(`/dokumensB/${docId}/set-deadline`, {
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
  .then(response => {
    console.log('Response status:', response.status);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
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
      }
      
      // Reload page when modal is closed
      successModalEl.addEventListener('hidden.bs.modal', function() {
        location.reload();
      }, { once: true });
      
      successModal.show();
    } else {
      alert('Gagal menetapkan deadline: ' + data.message);
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

// Character counter for deadline note
document.addEventListener('DOMContentLoaded', function() {
  const deadlineNote = document.getElementById('deadlineNote');
  const charCount = document.getElementById('charCount');

  if (deadlineNote && charCount) {
    deadlineNote.addEventListener('input', function() {
      charCount.textContent = this.value.length;
    });
  }
});

function openSendToNextModal(docId) {
  document.getElementById('nextDocId').value = docId;
  const modal = new bootstrap.Modal(document.getElementById('sendToNextModal'));
  modal.show();
}

function confirmSendToNext() {
  const docId = document.getElementById('nextDocId').value;
  const nextHandler = document.querySelector('input[name="next_handler"]:checked')?.value;

  if (!nextHandler) {
    // Ensure sendToNextModal stays open
    const sendModal = bootstrap.Modal.getInstance(document.getElementById('sendToNextModal'));
    if (!sendModal || !sendModal._isShown) {
      // If send modal is not open, open it first
      const sendModalNew = new bootstrap.Modal(document.getElementById('sendToNextModal'));
      sendModalNew.show();
    }
    
    // Show warning modal instead of alert
    const warningModal = new bootstrap.Modal(document.getElementById('sendDestinationWarningModal'));
    warningModal.show();
    
    // Focus back to first radio button when warning modal is closed
    const warningModalEl = document.getElementById('sendDestinationWarningModal');
    warningModalEl.addEventListener('hidden.bs.modal', function() {
      const firstRadio = document.getElementById('perpajakan');
      if (firstRadio) {
        setTimeout(() => {
          firstRadio.focus();
        }, 100);
      }
    }, { once: true });
    
    return;
  }

  const submitBtn = event.target;
  const originalHTML = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

  fetch(`/dokumensB/${docId}/send-to-next`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      next_handler: nextHandler
    })
  })
  .then(response => {
    console.log('Response status:', response.status);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    console.log('Response data:', data);
    if (data.success) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('sendToNextModal'));
      modal.hide();

      showSendSuccessModal(data.message);
    } else {
      alert('Gagal mengirim: ' + data.message);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat mengirim dokumen: ' + error.message);
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
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
    textEl.textContent = message || 'Dokumen telah dikirim dan akan muncul di halaman tujuan.';
  }

  shouldReloadAfterSendSuccess = true;
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

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
});
</script>

<!-- Return to Bidang Modal -->
<div class="modal fade" id="returnToBidangModal" tabindex="-1" aria-labelledby="returnToBidangModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" style="max-height: 90vh; overflow: hidden;">
      <div class="modal-header" style="background: linear-gradient(135deg, #6f42c1 0%, #a855f7 100%); color: white; flex-shrink: 0;">
        <h5 class="modal-title" id="returnToBidangModalLabel">
          <i class="fa-solid fa-sitemap me-2"></i>Kembalikan Dokumen ke Bidang
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 140px);">
        <div class="row g-4">
          <!-- Left Column: Document Info -->
          <div class="col-lg-5">
            <div class="sticky-top" style="top: 1rem;">
              <h6 class="text-muted mb-3">
                <i class="fa-solid fa-file-lines me-2"></i>Informasi Dokumen:
              </h6>
              <div id="return-bidang-doc-info" class="border rounded p-3 bg-light" style="min-height: 200px;">
                <!-- Document info akan dimuat di sini -->
              </div>
            </div>
          </div>

          <!-- Right Column: Form -->
          <div class="col-lg-7">
            <div class="sticky-top" style="top: 1rem;">
              <h6 class="text-muted mb-3">
                <i class="fa-solid fa-edit me-2"></i>Form Pengembalian:
              </h6>
              <form id="return-bidang-form" class="needs-validation" novalidate>
                <input type="hidden" id="return-bidang-doc-id" name="doc_id">

                <!-- Target Bidang -->
                <div class="mb-4">
                  <label for="target_bidang" class="form-label fw-bold">
                    <i class="fa-solid fa-sitemap me-2 text-primary"></i>Bidang Tujuan
                    <span class="text-danger">*</span>
                  </label>
                  <select class="form-select form-select-lg" id="target_bidang" name="target_bidang" required>
                    <option value="">-- Pilih Bidang Tujuan --</option>
                    <option value="DPM">DPM - Divisi Produksi dan Manufaktur</option>
                    <option value="SKH">SKH - Sub Kontrak Hutan</option>
                    <option value="SDM">SDM - Sumber Daya Manusia</option>
                    <option value="TEP">TEP - Teknik dan Perencanaan</option>
                    <option value="KPL">KPL - Keuangan dan Pelaporan</option>
                    <option value="AKN">AKN - Akuntansi</option>
                    <option value="TAN">TAN - Tanaman dan Perkebunan</option>
                    <option value="PMO">PMO - Project Management Office</option>
                  </select>
                  <div class="invalid-feedback">
                    Silakan pilih bidang tujuan pengembalian.
                  </div>
                </div>

                <!-- Return Reason -->
                <div class="mb-4">
                  <label for="bidang_return_reason" class="form-label fw-bold">
                    <i class="fa-solid fa-comment me-2 text-primary"></i>Alasan Pengembalian
                    <span class="text-danger">*</span>
                  </label>
                  <textarea class="form-control" id="bidang_return_reason" name="bidang_return_reason"
                            rows="3" placeholder="Jelaskan alasan pengembalian dokumen ke bidang ini..."
                            style="resize: vertical; min-height: 80px;" required></textarea>
                  <div class="d-flex justify-content-between mt-2">
                    <div class="form-text">Minimal 5 karakter</div>
                    <div class="form-text">
                      <span id="bidang-char-count">0</span>/1000 karakter
                    </div>
                  </div>
                  <div class="invalid-feedback">
                    Alasan pengembalian minimal 5 karakter dan maksimal 1000 karakter.
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-4">
                  <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-2"></i>Batal
                  </button>
                  <button type="button" class="btn btn-primary flex-fill" id="submit-return-bidang" onclick="returnToBidang()">
                    <i class="fa-solid fa-sitemap me-2"></i>Kembalikan ke Bidang
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Sticky positioning fix for modal */
.modal .sticky-top {
  z-index: 1;
}
</style>

<script>
// Open Return to Bidang Modal
function openReturnToBidangModal(docId) {
  // Fetch document details via AJAX (returns HTML)
  fetch(`/dokumens/${docId}/detail`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'text/html'
    }
  })
    .then(response => response.text())
    .then(html => {
      // Set document info in modal
      document.getElementById('return-bidang-doc-info').innerHTML = html;
      document.getElementById('return-bidang-doc-id').value = docId;

      // Reset form
      document.getElementById('target_bidang').value = '';
      document.getElementById('bidang_return_reason').value = '';
      document.getElementById('bidang-char-count').textContent = '0';

      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('returnToBidangModal'));
      modal.show();
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Gagal memuat detail dokumen.');
    });
}

// Return to Bidang function
function returnToBidang() {
  const docId = document.getElementById('return-bidang-doc-id').value;
  const targetBidang = document.getElementById('target_bidang').value;
  const reason = document.getElementById('bidang_return_reason').value;

  if (!targetBidang) {
    alert('Pilih bidang tujuan terlebih dahulu.');
    return;
  }

  if (!reason || reason.trim().length < 5) {
    alert('Alasan pengembalian minimal 5 karakter.');
    return;
  }

  const submitBtn = document.getElementById('submit-return-bidang');

  // Show loading state
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';

  fetch(`/dokumensB/${docId}/return-to-bidang`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      target_bidang: targetBidang,
      bidang_return_reason: reason
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('returnToBidangModal'));
      modal.hide();

      // Show success notification
      showNotification(`Dokumen berhasil dikembalikan ke bidang ${data.target_bidang}`, 'success');

      // Reload page after 2 seconds
      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      alert(data.message || 'Gagal mengembalikan dokumen ke bidang.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat mengembalikan dokumen ke bidang.');
  })
  .finally(() => {
    // Restore button state
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fa-solid fa-sitemap me-2"></i>Kembalikan ke Bidang';
  });
}

// Character counter for bidang return reason textarea
document.addEventListener('DOMContentLoaded', function() {
  const bidangReasonTextarea = document.getElementById('bidang_return_reason');
  const bidangCharCount = document.getElementById('bidang-char-count');

  if (bidangReasonTextarea && bidangCharCount) {
    bidangReasonTextarea.addEventListener('input', function() {
      const length = this.value.length;
      bidangCharCount.textContent = length;

      // Update color based on length
      bidangCharCount.classList.remove('warning', 'danger');
      if (length > 900) {
        bidangCharCount.classList.add('danger');
      } else if (length > 800) {
        bidangCharCount.classList.add('warning');
      }
    });
  }
});
</script>

<!-- Notification Styles -->
<style>
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

/* Send to Next Modal - Radio Button Styles */
#sendToNextModal .form-check {
  transition: all 0.3s ease;
}

#sendToNextModal .form-check:hover {
  border-color: #083E40 !important;
  background: linear-gradient(135deg, rgba(8, 62, 64, 0.05) 0%, rgba(136, 151, 23, 0.05) 100%);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(8, 62, 64, 0.15);
}

#sendToNextModal .form-check-input:checked ~ .form-check-label {
  color: #083E40;
}

#sendToNextModal .form-check:has(.form-check-input:checked) {
  border-color: #083E40 !important;
  background: linear-gradient(135deg, rgba(8, 62, 64, 0.1) 0%, rgba(136, 151, 23, 0.1) 100%);
  box-shadow: 0 4px 16px rgba(8, 62, 64, 0.2);
}

#sendToNextModal .form-check-input {
  width: 20px;
  height: 20px;
  margin-top: 2px;
  cursor: pointer;
}

#sendToNextModal .form-check-input:checked {
  background-color: #083E40;
  border-color: #083E40;
}
</style>

@endsection

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
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  /* Table Container - Enhanced Horizontal Scroll from dokumensB */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
    position: relative;
    overflow: hidden;
  }

  /* Horizontal Scroll Container */
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
    min-width: 1200px; /* Minimum width for horizontal scroll */
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
  }

  .table-enhanced tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
    border-left: 3px solid transparent;
  }

  .table-enhanced tbody tr:hover {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
    transform: scale(1.002);
  }

  .table-enhanced tbody tr.highlight-row {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
  }

  /* Enhanced Locked Row Styling from dokumensB */
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

  .table-enhanced td {
    padding: 16px;
    vertical-align: middle;
    border-right: 1px solid rgba(26, 77, 62, 0.05);
    white-space: nowrap;
    font-size: 13px;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
    text-align: center;
  }

  /* Custom centering for specific column content */
  .table-enhanced .col-no,
  .table-enhanced .col-agenda,
  .table-enhanced .col-spp,
  .table-enhanced .col-nilai,
  .table-enhanced .col-status,
  .table-enhanced .col-deadline,
  .table-enhanced .col-action {
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

  /* Column Widths for Horizontal Scroll */
  .table-enhanced .col-no {
    width: 80px;
    min-width: 80px;
    text-align: center;
    font-weight: 600;
  }
  .table-enhanced .col-agenda {
    width: 150px;
    min-width: 150px;
    text-align: center;
  }
  .table-enhanced .col-spp {
    width: 160px;
    min-width: 160px;
    text-align: center;
  }
  .table-enhanced .col-nilai {
    width: 140px;
    min-width: 140px;
    text-align: center;
  }
  .table-enhanced .col-status {
    width: 160px;
    min-width: 160px;
    text-align: center;
  }
  .table-enhanced .col-deadline {
    width: 180px;
    min-width: 180px;
    text-align: center;
  }
  .table-enhanced .col-action {
    width: 160px;
    min-width: 160px;
    text-align: center;
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

  /* Detail Grid - 5 Column Layout from dokumensB */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    width: 100%;
    box-sizing: border-box;
  }

  /* Responsive Detail Grid - 5 columns on desktop */
  @media (min-width: 1400px) {
    .detail-grid {
      grid-template-columns: repeat(5, 1fr);
      gap: 16px;
    }
  }

  @media (min-width: 1200px) and (max-width: 1399px) {
    .detail-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
    }
  }

  @media (max-width: 1199px) {
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

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px;
    background: white;
    border-radius: 10px;
    border: 1px solid rgba(8, 62, 64, 0.08);
    transition: all 0.2s ease;
    min-width: 0;
    width: 100%;
    overflow: hidden;
    box-sizing: border-box;
    position: relative;
  }

  .detail-item:hover {
    border-color: #889717;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.15);
    transform: translateY(-2px);
  }

  /* Enhanced visual hierarchy for 5-column layout */
  .detail-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    opacity: 0;
    transition: opacity 0.2s ease;
    border-radius: 3px 0 0 3px;
  }

  .detail-item:hover::before {
    opacity: 1;
  }

  /* Optimized for 5-column content */
  .detail-item:nth-child(5n+1) {
    border-left-color: rgba(136, 151, 23, 0.2);
  }

  .detail-item:nth-child(5n+2) {
    border-left-color: rgba(8, 62, 64, 0.2);
  }

  .detail-item:nth-child(5n+3) {
    border-left-color: rgba(136, 151, 23, 0.2);
  }

  .detail-item:nth-child(5n+4) {
    border-left-color: rgba(8, 62, 64, 0.2);
  }

  .detail-item:nth-child(5n) {
    border-left-color: rgba(136, 151, 23, 0.2);
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
    overflow: hidden;
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

  /* Special styling for different content types */
  .detail-value[href] {
    color: #083E40;
    text-decoration: none;
    border-bottom: 1px dotted #083E40;
    transition: all 0.2s ease;
  }

  .detail-value[href]:hover {
    color: #889717;
    border-bottom-style: solid;
  }

  /* Ensure proper spacing in 5-column layout */
  .detail-grid .detail-item {
    margin: 0;
  }

  /* Fix overflow issues in narrow columns */
  @media (min-width: 1400px) {
    .detail-item {
      min-height: 80px;
    }
  }

  /* Detail Section Separator - Visual divider between document and tax sections */
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
    border: 2px solid #ffc107;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
  }

  .separator-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
  }

  .separator-content i {
    font-size: 18px;
    color: #ffc107;
    background: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  .separator-content > span:nth-child(2) {
    font-size: 16px;
    font-weight: 700;
    color: #856404;
    flex: 1;
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
  }

  /* Tax Section Specific Styling */
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

  /* Empty field styling for tax information */
  .empty-field {
    color: #999;
    font-style: italic;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .empty-field::before {
    content: '⚠';
    font-size: 10px;
    opacity: 0.7;
  }

  /* Tax document link styling */
  .tax-document-link {
    color: #083E40;
    text-decoration: none;
    border-bottom: 1px dotted #083E40;
    transition: all 0.2s ease;
    word-break: break-all;
    display: inline-block;
    max-width: 100%;
  }

  .tax-document-link:hover {
    color: #889717;
    border-bottom-style: solid;
    text-decoration: none;
  }

  .tax-document-link i {
    font-size: 10px;
    opacity: 0.7;
    margin-left: 4px;
  }

  /* Responsive separator */
  @media (max-width: 768px) {
    .separator-content {
      flex-direction: column;
      text-align: center;
      gap: 8px;
      padding: 12px 16px;
    }

    .separator-content i {
      font-size: 16px;
      width: 36px;
      height: 36px;
    }

    .separator-content > span:nth-child(2) {
      font-size: 14px;
      order: -1;
    }

    .tax-badge {
      font-size: 10px;
      padding: 4px 12px;
    }
  }

  /* Enhanced Badge Styles matching dokumensB */
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

  .badge-status:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  /* Enhanced Action Buttons matching dokumensB */
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
  }

  .btn-action span {
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
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

  /* Return Button Styling */
  .btn-return {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: 2px solid rgba(220, 53, 69, 0.3);
    position: relative;
    overflow: hidden;
  }

  .btn-return::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    animation: shimmer 2s infinite;
  }

  .btn-return:hover {
    background: linear-gradient(135deg, #c82333 0%, #a02522 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
    border-color: rgba(220, 53, 69, 0.6);
  }

  .btn-return i {
    animation: pulse 2s infinite;
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

  .btn-filter {
    padding: 10px 24px;
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(26, 77, 62, 0.2);
  }

  .btn-filter:hover {
    background: linear-gradient(135deg, #0f3d2e 0%, #0a2e1f 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 77, 62, 0.3);
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
  .table-enhanced tbody tr {
    border-bottom: 1px solid rgba(26, 77, 62, 0.08);
    position: relative;
  }

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
    }

    .table-enhanced td {
      padding: 12px 8px;
      font-size: 12px;
    }

    .badge-status {
      padding: 6px 12px;
      font-size: 11px;
      min-width: 80px;
    }

    .action-buttons {
      gap: 4px;
    }

    .btn-action {
      min-width: 40px;
      min-height: 40px;
      padding: 6px 10px;
      font-size: 10px;
    }

    .btn-action span {
      font-size: 9px;
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
      min-width: 800px; /* Still allow horizontal scroll on very small screens */
    }

    .table-enhanced .col-agenda { min-width: 130px; }
    .table-enhanced .col-spp { min-width: 140px; }
    .table-enhanced .col-nilai { min-width: 120px; }
    .table-enhanced .col-status { min-width: 140px; }
    .table-enhanced .col-deadline { min-width: 160px; }
    .table-enhanced .col-action { min-width: 140px; }
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
</style>

<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

<!-- Search & Filter Box -->
<div class="search-box">
  <div class="input-group" style="flex: 1; max-width: 400px;">
    <span class="input-group-text">
      <i class="fa-solid fa-magnifying-glass text-muted"></i>
    </span>
    <input type="text" class="form-control" placeholder="Cari nomor agenda atau SPP...">
  </div>
</div>

<!-- Tabel Dokumen dengan Horizontal Scroll -->
<div class="table-dokumen">
  <div class="table-container-header">
    <h3 class="table-container-title">
      <i class="fa-solid fa-file-lines"></i>
      Daftar Dokumen Perpajakan
    </h3>
    <div class="table-container-stats">
      <div class="stat-item">
        <span class="stat-value">{{ count($dokumens) }}</span>
        <span class="stat-label">Total</span>
      </div>
      <div class="stat-item">
        <span class="stat-value">{{ $dokumens->where('status_perpajakan', 'selesai')->count() }}</span>
        <span class="stat-label">Selesai</span>
      </div>
      <div class="stat-item">
        <span class="stat-value">{{ $dokumens->whereNull('deadline_perpajakan_at')->count() }}</span>
        <span class="stat-label">Terkunci</span>
      </div>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-enhanced mb-0">
    <thead>
      <tr>
        <th class="col-no">No</th>
        <th class="col-agenda">Nomor Agenda</th>
        <th class="col-spp">Nomor SPP</th>
        <th class="col-nilai">Nilai Rupiah</th>
        <th class="col-status">Status</th>
        <th class="col-deadline">Deadline</th>
        <th class="col-action">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dokumens as $index => $dokumen)
        @php
          $isLocked = is_null($dokumen->deadline_perpajakan_at);
          $isSentToAkutansi = $dokumen->status == 'sent_to_akutansi';
          $canSendToAkutansi = $dokumen->status_perpajakan == 'selesai'
            && $dokumen->status != 'sent_to_akutansi'
            && $dokumen->current_handler == 'perpajakan';
          $perpajakanRequiredFields = [
            'npwp' => 'NPWP',
            'no_faktur' => 'Nomor Faktur',
            'tanggal_faktur' => 'Tanggal Faktur',
            'tanggal_selesai_verifikasi_pajak' => 'Tanggal Selesai Verifikasi Pajak',
            'jenis_pph' => 'Jenis PPh',
            'dpp_pph' => 'Nilai DPP PPh',
            'ppn_terhutang' => 'PPN Terhutang',
            'link_dokumen_pajak' => 'Link Dokumen Pajak',
          ];
          $missingPerpajakanFields = [];
          foreach ($perpajakanRequiredFields as $fieldKey => $fieldLabel) {
            if (empty($dokumen->{$fieldKey})) {
              $missingPerpajakanFields[] = $fieldLabel;
            }
          }
          
          // Determine send button tooltip message
          $sendButtonTooltip = 'Kirim ke Akutansi';
          if ($isSentToAkutansi) {
            $sendButtonTooltip = 'Dokumen sudah dikirim ke Akutansi';
          } elseif (!$canSendToAkutansi) {
            if ($dokumen->status_perpajakan != 'selesai') {
              $sendButtonTooltip = 'Status perpajakan harus "Selesai" terlebih dahulu sebelum dapat dikirim ke Akutansi';
            } elseif ($dokumen->current_handler != 'perpajakan') {
              $sendButtonTooltip = 'Dokumen tidak sedang ditangani oleh perpajakan';
            } else {
              $sendButtonTooltip = 'Status perpajakan harus "Selesai" terlebih dahulu sebelum dapat dikirim ke Akutansi';
            }
          }
        @endphp
        <tr class="main-row {{ $isLocked ? 'locked-row' : '' }}" data-dokumen-id="{{ $dokumen->id }}" onclick="toggleDetail({{ $dokumen->id }})" title="Klik untuk melihat detail lengkap dokumen">
          <td style="text-align: center;">{{ $index + 1 }}</td>
          <td>
            <strong>{{ $dokumen->nomor_agenda }}</strong>
            <br>
            <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
          </td>
          <td>{{ $dokumen->nomor_spp }}</td>
          <td><strong>{{ $dokumen->formatted_nilai_rupiah }}</strong></td>
          <td style="text-align: center;">
            @if($dokumen->status == 'sent_to_akutansi')
              <span class="badge-status badge-sent">Sudah terkirim ke Akutansi</span>
            @elseif($dokumen->status_perpajakan == 'selesai')
              <span class="badge-status badge-selesai">✓ Selesai</span>
            @elseif($dokumen->status_perpajakan == 'sedang_diproses')
              <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
            @else
              @if($isLocked)
                <span class="badge-status badge-locked">🔒 Terkunci</span>
              @else
                <span class="badge-status badge-proses">⏳ Belum Diproses</span>
              @endif
            @endif
          </td>
          <td>
            @if($dokumen->deadline_perpajakan_at)
              <small><strong>{{ $dokumen->deadline_perpajakan_at->format('d M Y, H:i') }}</strong></small>
              @if($dokumen->deadline_perpajakan_note)
                <br><small class="text-muted">{{ Str::limit($dokumen->deadline_perpajakan_note, 30) }}</small>
              @endif
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          <td onclick="event.stopPropagation()">
            <div class="action-buttons">
              @if($isLocked)
                <!-- Locked state - buttons disabled -->
                @unless($isSentToAkutansi)
                  <div class="action-row">
                    <button class="btn-action btn-edit locked" disabled title="Edit terkunci - tentukan deadline terlebih dahulu">
                      <i class="fa-solid fa-lock"></i>
                      <span>Terkunci</span>
                    </button>
                  </div>
                  <div class="action-row">
                    <button type="button" class="btn-action btn-set-deadline" onclick="openSetDeadlineModal({{ $dokumen->id }})" title="Tetapkan Deadline">
                      <i class="fa-solid fa-clock"></i>
                      <span>Set Deadline</span>
                    </button>
                  </div>
                  <div class="action-row">
                    <button type="button" class="btn-action btn-return" onclick="openReturnModal({{ $dokumen->id }})" title="Kembalikan ke IbuB">
                      <i class="fa-solid fa-undo"></i>
                      <span>Return</span>
                    </button>
                  </div>
                @endunless
                <div class="action-row">
                  <button
                    type="button"
                    class="btn-action btn-send"
                    onclick="handleSendToAkutansi({{ $dokumen->id }})"
                    data-doc-id="{{ $dokumen->id }}"
                    data-missing-fields="{{ e(implode('||', $missingPerpajakanFields)) }}"
                    title="{{ $sendButtonTooltip }}"
                    @if(!$canSendToAkutansi) disabled @endif
                  >
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Kirim</span>
                  </button>
                </div>
              @else
                <!-- Unlocked state - buttons enabled -->
                @unless($isSentToAkutansi)
                  <div class="action-row">
                    <a href="{{ route('dokumensPerpajakan.edit', $dokumen->id) }}" title="Edit Dokumen" style="text-decoration: none;">
                      <button class="btn-action btn-edit">
                        <i class="fa-solid fa-pen"></i>
                        <span>Edit</span>
                      </button>
                    </a>
                  </div>
                  <div class="action-row">
                    <button type="button" class="btn-action btn-return" onclick="openReturnModal({{ $dokumen->id }})" title="Kembalikan ke IbuB">
                      <i class="fa-solid fa-undo"></i>
                      <span>Return</span>
                    </button>
                  </div>
                @endunless
                <div class="action-row">
                  <button
                    type="button"
                    class="btn-action btn-send"
                    onclick="handleSendToAkutansi({{ $dokumen->id }})"
                    data-doc-id="{{ $dokumen->id }}"
                    data-missing-fields="{{ e(implode('||', $missingPerpajakanFields)) }}"
                    title="{{ $sendButtonTooltip }}"
                    @if(!$canSendToAkutansi) disabled @endif
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
          <td colspan="7">
            <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
              <div class="text-center p-4">
                <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
              </div>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center" style="padding: 40px;">
            <i class="fa-solid fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
            <p style="color: #999; font-size: 14px;">Belum ada dokumen</p>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<!-- Modal for Setting Deadline -->
<div class="modal fade" id="setDeadlineModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-clock me-2"></i>Tetapkan Deadline Perpajakan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="deadlineDocId">

        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 140, 0, 0.1) 100%); border-left: 4px solid #ffc107;">
          <i class="fa-solid fa-info-circle me-2"></i>
          <strong>Penting:</strong> Setelah deadline ditetapkan, dokumen akan terbuka untuk diproses.
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">
            <i class="fa-solid fa-calendar-days me-2"></i>Periode Deadline*
          </label>
          <select class="form-select" id="deadlineDays" required>
            <option value="">Pilih periode deadline</option>
            <option value="1">1 hari</option>
            <option value="2">2 hari</option>
            <option value="3">3 hari</option>
            <option value="7">1 minggu</option>
            <option value="14">2 minggu</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">
            <i class="fa-solid fa-sticky-note me-2"></i>Catatan Deadline <span class="text-muted">(opsional)</span>
          </label>
          <textarea class="form-control" id="deadlineNote" rows="3"
                    placeholder="Contoh: Perlu verifikasi dokumen pajak..."
                    maxlength="500"></textarea>
          <div class="form-text">
            <span id="charCount">0</span>/500 karakter
          </div>
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

<!-- Modal for Return to IbuB -->
<div class="modal fade" id="returnModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-undo me-2"></i>Kembalikan Dokumen ke IbuB
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="returnDocId">

        <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(200, 35, 51, 0.1) 100%); border-left: 4px solid #dc3545;">
          <i class="fa-solid fa-exclamation-triangle me-2"></i>
          <strong>Perhatian:</strong> Dokumen akan dikembalikan ke IbuB dan akan muncul di halaman pengembalian dokumen. Pastikan Anda telah mengisi alasan pengembalian dengan jelas.
        </div>

        <div class="form-group mb-3">
          <label for="returnReason" class="form-label">
            <strong>Alasan Pengembalian <span class="text-danger">*</span></strong>
          </label>
          <textarea class="form-control" id="returnReason" rows="4" placeholder="Jelaskan kenapa dokumen ini dikembalikan ke IbuB..." maxlength="500" required></textarea>
          <div class="form-text">
            <small class="text-muted">Mohon isi alasan pengembalian secara detail dan jelas.</small><br>
            <span id="returnCharCount">0</span>/500 karakter
          </div>
        </div>

        <div class="alert alert-info">
          <i class="fa-solid fa-info-circle me-2"></i>
          <strong>Informasi:</strong> Dokumen yang dikembalikan akan:
          <ul class="mb-0 mt-2">
            <li>Muncul di halaman "Pengembalian Dokumen IbuB"</li>
            <li>Muncul di halaman "Pengembalian Dokumen Perpajakan"</li>
            <li>Hilang dari daftar dokumen aktif perpajakan</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger" onclick="confirmReturn()">
          <i class="fa-solid fa-undo me-2"></i>Kembalikan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Return Confirmation -->
<div class="modal fade" id="returnConfirmationModal" tabindex="-1" aria-labelledby="returnConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
        <h5 class="modal-title" id="returnConfirmationModalLabel">
          <i class="fa-solid fa-question-circle me-2"></i>Konfirmasi Pengembalian
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <i class="fa-solid fa-exclamation-triangle" style="font-size: 52px; color: #dc3545;"></i>
        </div>
        <h5 class="fw-bold mb-3 text-center">Apakah Anda yakin ingin mengembalikan dokumen ini ke IbuB?</h5>
        <div class="alert alert-light border" style="background-color: #f8f9fa;">
          <div class="d-flex align-items-start">
            <i class="fa-solid fa-info-circle me-2 mt-1" style="color: #dc3545;"></i>
            <div>
              <strong>Alasan Pengembalian:</strong>
              <p class="mb-0 mt-2" id="returnConfirmationReason" style="color: #495057; font-size: 14px;"></p>
            </div>
          </div>
        </div>
        <p class="text-muted mb-0 text-center small">
          Dokumen akan dikembalikan ke IbuB dan akan muncul di halaman pengembalian dokumen.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger px-4" id="confirmReturnBtn">
          <i class="fa-solid fa-undo me-2"></i>Ya, Kembalikan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Return Success -->
<div class="modal fade" id="returnSuccessModal" tabindex="-1" aria-labelledby="returnSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title" id="returnSuccessModalLabel">
          <i class="fa-solid fa-circle-check me-2"></i>Pengembalian Berhasil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #28a745;"></i>
        </div>
        <h5 class="fw-bold mb-3">Dokumen berhasil dikembalikan ke IbuB!</h5>
        <p class="text-muted mb-0">
          Dokumen akan muncul di:
          <br>• Halaman "Pengembalian Dokumen IbuB"
          <br>• Halaman "Pengembalian Dokumen Perpajakan"
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

<!-- Modal for Return Validation Warning -->
<div class="modal fade" id="returnValidationWarningModal" tabindex="-1" aria-labelledby="returnValidationWarningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title" id="returnValidationWarningModalLabel">
          <i class="fa-solid fa-exclamation-triangle me-2"></i>Perhatian
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-exclamation-circle" style="font-size: 52px; color: #ffc107;"></i>
        </div>
        <h5 class="fw-bold mb-3" id="returnValidationWarningTitle">Validasi Gagal</h5>
        <p class="text-muted mb-0" id="returnValidationWarningMessage">
          Terjadi kesalahan pada input data.
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

<!-- Modal for Send Confirmation -->
<div class="modal fade" id="sendConfirmationModal" tabindex="-1" aria-labelledby="sendConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%); color: white;">
        <h5 class="modal-title" id="sendConfirmationModalLabel">
          <i class="fa-solid fa-paper-plane me-2"></i>Konfirmasi Pengiriman ke Akutansi
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="sendConfirmationDocId">
        <div class="alert alert-info border-0" id="sendConfirmationInfo">
          <i class="fa-solid fa-circle-info me-2"></i>
          Pastikan seluruh data perpajakan sudah lengkap sebelum mengirim dokumen ke Akutansi.
        </div>
        <div class="alert alert-warning border-0 d-none" id="missingFieldsWrapper">
          <div class="d-flex align-items-start">
            <i class="fa-solid fa-triangle-exclamation me-2 mt-1"></i>
            <div>
              <strong>Beberapa form khusus perpajakan belum diisi:</strong>
              <ul class="mt-2 mb-0" id="missingFieldsList"></ul>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success" id="confirmSendBtn" onclick="confirmSendToAkutansi()">
          <i class="fa-solid fa-paper-plane me-2"></i>Kirim Sekarang
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Send Success -->
<div class="modal fade" id="sendSuccessModal" tabindex="-1" aria-labelledby="sendSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #0d6c44 0%, #16a085 100%); color: white;">
        <h5 class="modal-title" id="sendSuccessModalLabel">
          <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 48px; color: #16a085;"></i>
        </div>
        <h5 class="fw-bold mb-3">Dokumen berhasil dikirim ke Akutansi!</h5>
        <p class="text-muted mb-0">
          Data perpajakan telah disertakan dan dokumen sekarang akan muncul di halaman Akutansi.
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
          <i class="fa-solid fa-check-circle" style="font-size: 48px; color: #ffc107;"></i>
        </div>
        <h5 class="fw-bold mb-3">Deadline berhasil ditetapkan!</h5>
        <p class="text-muted mb-0" id="deadlineSuccessMessage">
          Dokumen sekarang terbuka untuk diproses.
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
          Silakan pilih periode deadline (1 hari, 2 hari, 3 hari, 1 minggu, atau 2 minggu) sebelum menetapkan deadline.
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

  fetch(`/dokumensPerpajakan/${docId}/detail`)
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
    
    // Focus back to deadline days select when modal is closed
    const warningModalEl = document.getElementById('deadlineWarningModal');
    warningModalEl.addEventListener('hidden.bs.modal', function() {
      const deadlineDaysSelect = document.getElementById('deadlineDays');
      if (deadlineDaysSelect) {
        setTimeout(() => {
          deadlineDaysSelect.focus();
        }, 100);
      }
    }, { once: true });
    
    return;
  }

  const submitBtn = document.querySelector('[onclick="confirmSetDeadline()"]');
  const originalHTML = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!csrfToken) {
    alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
    return;
  }

  fetch(`/dokumensPerpajakan/${docId}/set-deadline`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      deadline_days: parseInt(deadlineDays),
      deadline_note: deadlineNote
    })
  })
  .then(response => response.json())
  .then(data => {
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

// Open Return Modal
function openReturnModal(docId) {
  document.getElementById('returnDocId').value = docId;
  document.getElementById('returnReason').value = '';
  document.getElementById('returnCharCount').textContent = '0';
  const modal = new bootstrap.Modal(document.getElementById('returnModal'));
  modal.show();
}

// Character counter for return reason
document.addEventListener('DOMContentLoaded', function() {
  const returnReason = document.getElementById('returnReason');
  const returnCharCount = document.getElementById('returnCharCount');

  if (returnReason && returnCharCount) {
    returnReason.addEventListener('input', function() {
      const currentLength = this.value.length;
      returnCharCount.textContent = currentLength;

      if (currentLength > 500) {
        returnCharCount.classList.add('text-danger');
      } else {
        returnCharCount.classList.remove('text-danger');
      }
    });
  }
});

// Show validation warning modal
function showReturnValidationWarning(title, message) {
  document.getElementById('returnValidationWarningTitle').textContent = title;
  document.getElementById('returnValidationWarningMessage').textContent = message;
  const warningModalEl = document.getElementById('returnValidationWarningModal');
  const warningModal = new bootstrap.Modal(warningModalEl);
  
  // Ensure return modal stays open
  const returnModal = bootstrap.Modal.getInstance(document.getElementById('returnModal'));
  if (!returnModal || !returnModal._isShown) {
    // If return modal is not open, open it first
    const returnModalNew = new bootstrap.Modal(document.getElementById('returnModal'));
    returnModalNew.show();
  }
  
  // When warning modal is closed, focus back to return reason textarea
  warningModalEl.addEventListener('hidden.bs.modal', function() {
    const returnReasonTextarea = document.getElementById('returnReason');
    if (returnReasonTextarea) {
      setTimeout(() => {
        returnReasonTextarea.focus();
        returnReasonTextarea.select();
      }, 100);
    }
  }, { once: true });
  
  warningModal.show();
}

// Confirm Return
function confirmReturn() {
  const docId = document.getElementById('returnDocId').value;
  const returnReason = document.getElementById('returnReason').value.trim();

  // Validation
  if (!returnReason) {
    showReturnValidationWarning(
      'Alasan Pengembalian Harus Diisi!',
      'Silakan isi alasan pengembalian terlebih dahulu sebelum mengembalikan dokumen. Alasan pengembalian wajib diisi untuk keperluan dokumentasi dan tracking.'
    );
    return;
  }

  if (returnReason.length < 10) {
    showReturnValidationWarning(
      'Alasan Pengembalian Terlalu Singkat!',
      'Alasan pengembalian minimal 10 karakter. Mohon jelaskan dengan lebih detail agar IbuB dapat memahami alasan pengembalian dokumen ini.'
    );
    return;
  }

  if (returnReason.length > 500) {
    showReturnValidationWarning(
      'Alasan Pengembalian Terlalu Panjang!',
      'Alasan pengembalian maksimal 500 karakter. Silakan ringkas alasan pengembalian Anda.'
    );
    return;
  }

  // Show confirmation modal with return reason
  const confirmationModal = new bootstrap.Modal(document.getElementById('returnConfirmationModal'));
  const reasonDisplay = document.getElementById('returnConfirmationReason');
  const displayReason = returnReason.length > 200 ? returnReason.substring(0, 200) + '...' : returnReason;
  reasonDisplay.textContent = displayReason;
  
  // Store data for final confirmation
  document.getElementById('confirmReturnBtn').setAttribute('data-doc-id', docId);
  document.getElementById('confirmReturnBtn').setAttribute('data-return-reason', returnReason);
  
  // Close return modal and show confirmation modal
  const returnModal = bootstrap.Modal.getInstance(document.getElementById('returnModal'));
  returnModal.hide();
  
  confirmationModal.show();
}

// Final confirmation and execute return
function executeReturn() {
  const docId = document.getElementById('confirmReturnBtn').getAttribute('data-doc-id');
  const returnReason = document.getElementById('confirmReturnBtn').getAttribute('data-return-reason');
  
  if (!docId || !returnReason) {
    console.error('Missing document ID or return reason');
    return;
  }

  // Close confirmation modal
  const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('returnConfirmationModal'));
  confirmationModal.hide();

  // Show loading state
  const submitBtn = document.querySelector('#returnModal .btn-danger');
  const originalText = submitBtn ? submitBtn.innerHTML : 'Kembalikan';

  // AJAX call to return document
  fetch(`/dokumensPerpajakan/${docId}/return`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      return_reason: returnReason
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Show success modal
      const successModal = new bootstrap.Modal(document.getElementById('returnSuccessModal'));
      successModal.show();
      
      // Reload page when success modal is closed
      const successModalEl = document.getElementById('returnSuccessModal');
      successModalEl.addEventListener('hidden.bs.modal', function() {
        location.reload();
      }, { once: true });
    } else {
      alert('❌ Gagal mengembalikan dokumen: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('❌ Terjadi kesalahan saat mengembalikan dokumen. Silakan coba lagi.');
  });
}

// Initialize confirmation button click handler
document.addEventListener('DOMContentLoaded', function() {
  const confirmReturnBtn = document.getElementById('confirmReturnBtn');
  if (confirmReturnBtn) {
    confirmReturnBtn.addEventListener('click', executeReturn);
  }
});

let currentSendButton = null;
let currentSendButtonOriginalHTML = '';
let shouldReloadAfterSuccess = false;

function handleSendToAkutansi(docId) {
  const sendBtn = document.querySelector(`button[data-doc-id="${docId}"]`);
  if (!sendBtn) {
    console.warn('Send button not found for document ID:', docId);
    return;
  }

  currentSendButton = sendBtn;
  currentSendButtonOriginalHTML = sendBtn.innerHTML;

  const missingFieldsAttr = sendBtn.getAttribute('data-missing-fields') || '';
  const missingFields = missingFieldsAttr
    .split('||')
    .map(field => field.trim())
    .filter(field => field.length > 0);

  const missingWrapper = document.getElementById('missingFieldsWrapper');
  const missingList = document.getElementById('missingFieldsList');
  const infoAlert = document.getElementById('sendConfirmationInfo');
  const confirmBtn = document.getElementById('confirmSendBtn');

  if (missingFields.length > 0) {
    missingWrapper.classList.remove('d-none');
    missingList.innerHTML = missingFields.map(field => `<li>${field}</li>`).join('');
    infoAlert.classList.add('d-none');
  } else {
    missingWrapper.classList.add('d-none');
    missingList.innerHTML = '';
    infoAlert.classList.remove('d-none');
  }

  document.getElementById('sendConfirmationDocId').value = docId;
  confirmBtn.disabled = false;
  confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Kirim Sekarang';

  const modal = new bootstrap.Modal(document.getElementById('sendConfirmationModal'));
  modal.show();
}

function confirmSendToAkutansi() {
  const docId = document.getElementById('sendConfirmationDocId').value;
  if (!docId) {
    alert('Dokumen tidak ditemukan. Silakan muat ulang halaman.');
    return;
  }
  performSendToAkutansi(docId);
}

function performSendToAkutansi(docId) {
  const confirmBtn = document.getElementById('confirmSendBtn');
  confirmBtn.disabled = true;
  confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

  if (currentSendButton) {
    currentSendButton.disabled = true;
    currentSendButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';
  }

  fetch(`/dokumensPerpajakan/${docId}/send-to-akutansi`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const modalElement = document.getElementById('sendConfirmationModal');
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance.hide();
      showSendSuccessModal();
    } else {
      alert('❌ Gagal mengirim dokumen: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('❌ Terjadi kesalahan saat mengirim dokumen ke Akutansi. Silakan coba lagi.');
  })
  .finally(() => {
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Kirim Sekarang';

    if (currentSendButton) {
      currentSendButton.disabled = false;
      currentSendButton.innerHTML = currentSendButtonOriginalHTML || '<i class="fa-solid fa-paper-plane"></i>';
    }
  });
}

function showSendSuccessModal() {
  const successModalEl = document.getElementById('sendSuccessModal');
  if (!successModalEl) {
    location.reload();
    return;
  }
  shouldReloadAfterSuccess = true;
  const successModal = new bootstrap.Modal(successModalEl);
  successModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
  const successModalEl = document.getElementById('sendSuccessModal');
  if (successModalEl) {
    successModalEl.addEventListener('hidden.bs.modal', function() {
      if (shouldReloadAfterSuccess) {
        shouldReloadAfterSuccess = false;
        location.reload();
      }
    });
  }

  // Initialize Bootstrap tooltips for disabled send buttons
  const disabledSendButtons = document.querySelectorAll('.btn-send:disabled');
  disabledSendButtons.forEach(button => {
    if (button.getAttribute('title')) {
      new bootstrap.Tooltip(button, {
        placement: 'top',
        trigger: 'hover focus'
      });
    }
  });
});
</script>

@endsection

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
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
  }

  .search-filter-form {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .search-input-group {
    flex: 1;
    min-width: 250px;
  }

  .search-box .input-group-text {
    background: white;
    border: 1px solid #dee2e6;
    border-right: none;
    border-radius: 8px 0 0 8px;
    padding: 10px 14px;
  }

  .search-box .form-control {
    border: 1px solid #dee2e6;
    border-left: none;
    border-radius: 0 8px 8px 0;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.3s ease;
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .search-box .form-control:focus + .input-group-text {
    border-color: #889717;
  }

  .year-dropdown-wrapper {
    position: relative;
  }

  .btn-year-select {
    padding: 10px 16px;
    background: white;
    color: #495057;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    white-space: nowrap;
    width: 100%;
    justify-content: space-between;
  }

  .btn-year-select:hover {
    border-color: #889717;
    background: #f8f9fa;
  }

  .btn-year-select.active {
    border-color: #889717;
    background: #f8f9fa;
  }

  .year-dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 150px;
    z-index: 1000;
    overflow: hidden;
    display: none;
  }

  .year-dropdown-menu.show,
  .year-dropdown-menu[style*="block"] {
    display: block;
  }

  .year-dropdown-item {
    display: block;
    padding: 12px 16px;
    color: #495057;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 14px;
    border-bottom: 1px solid #f1f3f5;
  }

  .year-dropdown-item:last-child {
    border-bottom: none;
  }

  .year-dropdown-item:hover {
    background: #f8f9fa;
    color: #889717;
  }

  .year-dropdown-item.active {
    background: #e8f5e9;
    color: #889717;
    font-weight: 600;
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

  /* Column Customization Button - Senior Friendly */
  .column-customization-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(8, 62, 64, 0.1);
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .btn-customize-columns {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 16px 24px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.25);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 56px;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    position: relative;
    overflow: hidden;
  }

  .btn-customize-columns:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(8, 62, 64, 0.35);
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #9ab01f 100%);
  }

  .btn-customize-columns:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.3);
  }

  .btn-customize-columns::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
  }

  .btn-customize-columns:hover::before {
    left: 100%;
  }

  .current-columns-info {
    background: rgba(8, 62, 64, 0.05);
    padding: 12px 16px;
    border-radius: 8px;
    border-left: 4px solid #083E40;
  }

  /* Modal Customization Styles - Senior Friendly */
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

  /* Vertical Layout: Configuration on Top, Preview on Bottom */
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

  .column-selection-list::-webkit-scrollbar {
    width: 8px;
  }

  .column-selection-list::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 4px;
  }

  .column-selection-list::-webkit-scrollbar-thumb {
    background: #ced4da;
    border-radius: 4px;
  }

  .column-selection-list::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
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

  .column-item:hover {
    border-color: #0066cc;
    background: #f8f9ff;
    box-shadow: 0 2px 8px rgba(0, 102, 204, 0.1);
  }

  .column-item.selected {
    border-color: #28a745;
    background: #f0f9f4;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
  }

  .column-item.dragging {
    opacity: 0.6;
    transform: scale(0.98);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1000;
  }

  .column-item.drag-over {
    border-color: #0066cc;
    border-style: dashed;
    background: #e7f3ff;
    transform: translateX(8px);
  }

  .column-item:not(.selected) .drag-handle {
    opacity: 0.3;
    cursor: not-allowed;
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

  .drag-handle:active {
    cursor: grabbing;
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

  .preview-table thead {
    position: sticky;
    top: 0;
    z-index: 10;
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

  .preview-table th:last-child {
    border-right: none;
  }

  .preview-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background 0.2s ease;
  }

  .preview-table tbody tr:hover {
    background: #f8f9fa;
  }

  .preview-table tbody tr:last-child {
    border-bottom: none;
  }

  .preview-table td {
    padding: 12px;
    text-align: center;
    border-right: 1px solid #e9ecef;
    color: #495057;
    font-size: 13px;
  }

  .preview-table td:last-child {
    border-right: none;
  }

  .empty-preview {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
  }

  .empty-preview i {
    font-size: 48px;
    color: #adb5bd;
    margin-bottom: 16px;
  }

  .empty-preview p {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 8px;
  }

  .empty-preview small {
    font-size: 14px;
    color: #868e96;
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
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
  }

  .btn-save {
    background: #28a745;
    color: white;
  }

  .btn-save:hover {
    background: #218838;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
  }

  .btn-save:disabled {
    background: #adb5bd;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .customization-modal {
      padding: 10px;
    }

    .modal-content-custom {
      max-height: 95vh;
    }

    .modal-header-custom,
    .modal-body-custom,
    .modal-footer-custom {
      padding: 20px;
    }

    .modal-header-custom h3 {
      font-size: 20px;
    }

    .column-item {
      padding: 10px 12px;
    }

    .column-item-label {
      font-size: 14px;
    }

    .preview-table {
      font-size: 12px;
    }

    .preview-table th,
    .preview-table td {
      padding: 6px 4px;
    }

    .modal-footer-custom {
      flex-direction: column;
      align-items: stretch;
    }

    .selected-count {
      text-align: center;
      margin-bottom: 12px;
    }

    .modal-actions {
      justify-content: stretch;
    }

    .btn-modal {
      flex: 1;
      justify-content: center;
    }
  }

  /* Table Container - Enhanced Horizontal Scroll from dokumensPerpajakan */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
    overflow: visible; /* Changed from hidden to visible to allow scrollbar */
    width: 100%;
    max-width: 100%;
  }

  /* Horizontal Scroll Container - Enhanced */
  .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: auto; /* Changed from thin to auto for better visibility */
    scrollbar-color: rgba(8, 62, 64, 0.5) rgba(8, 62, 64, 0.1);
    position: relative;
    width: 100%;
    max-width: 100%;
  }

  /* Webkit scrollbar styling for Chrome, Safari, Edge */
  .table-responsive::-webkit-scrollbar {
    height: 16px; /* Increased from 12px for better visibility */
  }

  .table-responsive::-webkit-scrollbar-track {
    background: rgba(8, 62, 64, 0.08);
    border-radius: 8px;
    margin: 0 10px;
    border: 1px solid rgba(8, 62, 64, 0.1);
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.6), rgba(136, 151, 23, 0.7));
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.8), rgba(136, 151, 23, 0.9));
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
  }

  .table-responsive::-webkit-scrollbar-thumb:active {
    background: linear-gradient(135deg, #083E40, #889717);
  }

  /* Firefox scrollbar styling */
  .table-responsive {
    scrollbar-width: auto;
    scrollbar-color: rgba(8, 62, 64, 0.6) rgba(8, 62, 64, 0.1);
  }

  /* Enhanced table for better UX - Adopted from IbuB */
  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1200px; /* Minimum width for horizontal scroll */
    width: 100%;
    table-layout: auto; /* Allow table to expand beyond container */
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

  /* Column width optimization for IbuA */
  .table-enhanced td {
    padding: 12px;
    vertical-align: middle;
    border-right: 1px solid #e0e0e0;
    white-space: nowrap;
  }

  .table-enhanced .col-no { width: 80px; min-width: 80px; }
  .table-enhanced .col-agenda { width: 120px; min-width: 120px; }
  .table-enhanced .col-spp { width: 140px; min-width: 140px; }
  .table-enhanced .col-tanggal { width: 160px; min-width: 140px; }
  .table-enhanced .col-nilai { width: 120px; min-width: 120px; }
  .table-enhanced .col-mirror { width: 120px; min-width: 120px; }
  .table-enhanced .col-status { width: 120px; min-width: 100px; }
  .table-enhanced .col-keterangan { width: 150px; min-width: 130px; }
  .table-enhanced .col-action { width: 140px; min-width: 140px; }

  .table-enhanced .col-sticky {
    position: sticky;
    left: 0;
    background: white;
    z-index: 5;
  }

  /* Responsive design improvements */
  @media (max-width: 768px) {
    .table-dokumen {
      border-radius: 8px;
      box-shadow: 0 2px 15px rgba(8, 62, 64, 0.05);
    }

    .table-enhanced {
      min-width: 700px;
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
    .table-enhanced .col-agenda { width: 100px; min-width: 100px; }
    .table-enhanced .col-spp { width: 120px; min-width: 120px; }
    .table-enhanced .col-tanggal { width: 130px; min-width: 130px; }
    .table-enhanced .col-nilai { width: 100px; min-width: 100px; }
    .table-enhanced .col-mirror { width: 100px; min-width: 100px; }
    .table-enhanced .col-status { width: 90px; min-width: 90px; }
    .table-enhanced .col-keterangan { width: 110px; min-width: 110px; }
    .table-enhanced .col-action { width: 120px; min-width: 120px; }

    /* Improve readability on mobile - detail section */
    .detail-item {
      padding: 10px;
      gap: 4px;
    }

    .detail-label {
      font-size: 10px;
      color: #374151;
      letter-spacing: 0.5px;
      padding: 5px 8px;
      min-width: 100px;
    }

    .detail-value {
      font-size: 13px;
      color: #111827;
      line-height: 1.5;
    }
  }

  @media (max-width: 480px) {
    .table-enhanced {
      min-width: 600px;
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
    .table-enhanced .col-agenda { width: 90px; min-width: 90px; }
    .table-enhanced .col-spp { width: 100px; min-width: 100px; }
    .table-enhanced .col-tanggal { width: 120px; min-width: 120px; }
    .table-enhanced .col-nilai { width: 80px; min-width: 80px; }
    .table-enhanced .col-mirror { width: 80px; min-width: 80px; }
    .table-enhanced .col-status { width: 80px; min-width: 80px; }
    .table-enhanced .col-keterangan { width: 90px; min-width: 90px; }
    .table-enhanced .col-action { width: 100px; min-width: 100px; }
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
  }

  .table-dokumen tbody tr.main-row:hover {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    border-left: 3px solid #889717;
    transform: scale(1.005);
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

  /* Enhanced Detail Row Styles - Adopted from IbuB */
  .detail-row {
    display: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  }

  .detail-row.show {
    display: table-row;
  }

  .detail-content {
    padding: 20px;
    border-top: 2px solid rgba(8, 62, 64, 0.1);
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    width: 100%;
    box-sizing: border-box;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px; /* Tambah gap untuk background spacing */
    min-width: 0;
    width: 100%;
    overflow: visible;
    background: #ffffff; /* Putih bersih untuk contrast dengan label */
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #f1f5f9; /* Border yang sangat tipis */
    transition: all 0.2s ease;
  }

  .detail-item:hover {
    border-color: #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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

  /* Chevron Icon Animation */
  .chevron-icon {
    transition: transform 0.3s ease;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  /* Enhanced Status System - Dynamic and Modern */
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

  /* State 1: Draft / Belum Dikirim */
  .badge-status.badge-draft {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    border-color: #495057;
    position: relative;
    overflow: hidden;
  }

  .badge-status.badge-draft::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shimmer 2s infinite;
  }

  /* State 2: Sudah Dikirim ke IbuB */
  .badge-status.badge-terkirim {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
  }

  .badge-status.badge-terkirim::after {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    background: white;
    border-radius: 50%;
    margin-left: 6px;
    animation: pulse 1.5s infinite;
  }

  /* State 3: Dikembalikan */
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

  /* Enhanced hover effects */
  .badge-status:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
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

  /* Responsive Status Badges */
  @media (max-width: 768px) {
    .badge-status {
      padding: 6px 12px;
      font-size: 11px;
      min-width: 80px;
      gap: 4px;
    }

    .badge-status.badge-terkirim::after {
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

  .badge-yellow {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: #333;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  .badge-green {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
  }

  .badge-yellow:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
  }

  .badge-green:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  }

  /* Enhanced Action Buttons - Touch-friendly and Modern */
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
  .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }

  .btn-action:active {
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

  .btn-send {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 50%, #a8bf23 100%);
    color: white;
  }

  .btn-send:hover {
    background: linear-gradient(135deg, #9ab01f 0%, #a8bf23 50%, #b8cf27 100%);
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
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .action-buttons {
      gap: 6px;
    }

    .btn-action {
      padding: 6px 10px;
      font-size: 11px;
      min-width: 32px;
      height: 32px;
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
    padding: 10px 20px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    white-space: nowrap;
  }

  .btn-filter:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    color: white;
  }

  .btn-filter:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
  }

  .btn-tambah {
    padding: 10px 20px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
  }

  .btn-tambah:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(136, 151, 23, 0.3);
    color: white;
  }

  .btn-tambah:active {
    transform: translateY(-1px);
    box-shadow: 0 3px 12px rgba(136, 151, 23, 0.4);
  }

  /* Enhanced Form Controls */
  .form-select {
    padding: 10px 14px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 13px;
    transition: all 0.3s ease;
    background: white;
    font-weight: 500;
    min-height: 44px;
    min-width: 120px;
  }

  .form-select:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  /* Responsive Search & Filter */
  @media (max-width: 768px) {
    .search-box form {
      flex-direction: column;
      align-items: stretch;
      gap: 15px;
    }

    .input-group {
      min-width: auto !important;
      margin-right: 0 !important;
    }

    .filter-section {
      margin-right: 0 !important;
    }

    .btn-filter,
    .btn-tambah {
      width: 100%;
      justify-content: center;
      min-height: 48px;
    }

    .form-select {
      min-height: 48px;
      width: 100%;
    }
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

  /* Enhanced Loading Spinner */
  .loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: #083E40;
    font-size: 14px;
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.02) 0%, rgba(136, 151, 23, 0.02) 100%);
    border-radius: 12px;
    margin: 20px 0;
  }

  .loading-spinner i {
    margin-right: 12px;
    font-size: 18px;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  /* Final optimization for consistent styling */
  .table-container {
    border-radius: 16px;
    overflow-x: auto; /* Enable horizontal scroll */
    overflow-y: visible; /* Allow vertical content */
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    width: 100%;
    max-width: 100%;
    position: relative;
    /* Force scrollbar to always be visible when content overflows */
    scrollbar-gutter: stable;
  }

  /* Ensure scrollbar is always visible when needed */
  .table-container:has(.table-enhanced) {
    overflow-x: scroll; /* Force scrollbar to appear */
  }

  /* Micro-interactions for better UX */
  .main-row {
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
  }

  .main-row:hover {
    border-left: 3px solid #889717;
  }

  .main-row:active {
    transform: scale(0.99);
  }

  /* Additional scrollbar enhancements for better visibility */
  .table-dokumen .table-responsive {
    padding-bottom: 5px; /* Add space for scrollbar */
    margin-bottom: 5px; /* Add margin for scrollbar visibility */
  }

  /* Ensure scrollbar is always visible on all browsers */
  @supports (scrollbar-width: auto) {
    .table-responsive {
      scrollbar-width: auto;
      scrollbar-color: rgba(8, 62, 64, 0.6) rgba(8, 62, 64, 0.1);
    }
  }

  /* Ensure horizontal scrollbar appears when content overflows */
  .table-container .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
  }

  </style>

<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

<!-- Alert Messages -->
<!-- Success notification is handled by layout/app.blade.php to avoid duplication -->
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-radius: 10px;">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Enhanced Search & Filter Box -->
<div class="search-box">
  <form action="{{ route('dokumens.index') }}" method="GET" id="filterForm" class="search-filter-form">
    <div class="input-group search-input-group">
      <span class="input-group-text">
        <i class="fa-solid fa-magnifying-glass text-muted"></i>
      </span>
      <input type="text" class="form-control" name="search" placeholder="Cari nomor agenda, SPP, nilai rupia" value="{{ request('search') }}">
    </div>
    <div class="year-dropdown-wrapper" style="position: relative;">
      <button type="button" class="btn-year-select" id="yearSelectBtn">
        <span id="yearSelectText">{{ request('year') ? request('year') : 'Semua Tahun' }}</span>
        <i class="fa-solid fa-chevron-down ms-2"></i>
      </button>
      <div class="year-dropdown-menu" id="yearDropdownMenu" style="display: none;">
        <a href="#" class="year-dropdown-item {{ !request('year') ? 'active' : '' }}" data-year="">
          Semua Tahun
        </a>
        <a href="#" class="year-dropdown-item {{ request('year') == '2025' ? 'active' : '' }}" data-year="2025">
          2025
        </a>
        <a href="#" class="year-dropdown-item {{ request('year') == '2024' ? 'active' : '' }}" data-year="2024">
          2024
        </a>
        <a href="#" class="year-dropdown-item {{ request('year') == '2023' ? 'active' : '' }}" data-year="2023">
          2023
        </a>
      </div>
      <input type="hidden" name="year" id="yearSelect" value="{{ request('year') }}">
    </div>
    <button type="submit" class="btn-filter" id="filterBtn" onclick="handleFilterSubmit(event)">
      <i class="fa-solid fa-caret-down me-2"></i>Filter
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

<!-- Enhanced Tabel Dokumen -->
<div class="table-dokumen">
  <div class="table-responsive table-container">
    <table class="table table-enhanced mb-0">
    <thead>
      <tr>
        <th class="col-no sticky-column">No</th>
        @foreach($selectedColumns as $col)
          <th class="col-{{ $col }}">{{ $availableColumns[$col] ?? $col }}</th>
        @endforeach
        <th class="col-action sticky-column">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dokumens as $index => $dokumen)
      <tr class="main-row clickable-row" data-id="{{ $dokumen->id }}" onclick="loadDocumentDetail({{ $dokumen->id }})">
        <td class="col-no sticky-column">{{ $dokumens->firstItem() + $index }}</td>
        @foreach($selectedColumns as $col)
          <td class="col-{{ $col }}">
            @if($col == 'nomor_agenda')
              <strong>{{ $dokumen->nomor_agenda }}</strong>
              <br>
              <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
            @elseif($col == 'nomor_spp')
              {{ $dokumen->nomor_spp }}
            @elseif($col == 'tanggal_masuk')
              {{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d-m-Y H:i') : '-' }}
            @elseif($col == 'nilai_rupiah')
              <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
            @elseif($col == 'nomor_mirror')
              {{ $dokumen->nomor_mirror ?? '-' }}
            @elseif($col == 'status')
              @if(in_array($dokumen->status, ['draft', 'returned_to_ibua']))
                <span class="badge-status badge-draft">
                  <i class="fa-solid fa-file-lines me-1"></i>
                  <span>Belum Dikirim</span>
                </span>
              @elseif($dokumen->status == 'sent_to_ibub')
                <span class="badge-status badge-terkirim">
                  <i class="fa-solid fa-check me-1"></i>
                  <span>Terkirim ke Ibu Yuni</span>
                </span>
              @elseif($dokumen->status == 'approved_data_sudah_terkirim')
                <span class="badge-status badge-terkirim">
                  <i class="fa-solid fa-check me-1"></i>
                  <span>Terkirim</span>
                </span>
              @elseif($dokumen->status == 'rejected_data_tidak_lengkap')
                <span class="badge-status badge-dikembalikan">
                  <i class="fa-solid fa-times me-1"></i>
                  <span>Dikembalikan</span>
                </span>
              @else
                <span class="badge-status badge-terkirim">
                  <i class="fa-solid fa-check me-1"></i>
                  <span>Terikirim</span>
                </span>
              @endif
            @elseif($col == 'keterangan')
              {{ $dokumen->keterangan ?? '-' }}
            @elseif($col == 'tanggal_spp')
              {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d-m-Y') : '-' }}
            @elseif($col == 'uraian_spp')
              {{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}
            @elseif($col == 'kategori')
              {{ $dokumen->kategori ?? '-' }}
            @elseif($col == 'kebun')
              {{ $dokumen->kebun ?? '-' }}
            @elseif($col == 'jenis_dokumen')
              {{ $dokumen->jenis_dokumen ?? '-' }}
            @elseif($col == 'jenis_pembayaran')
              {{ $dokumen->jenis_pembayaran ?? '-' }}
            @elseif($col == 'nama_pengirim')
              {{ $dokumen->nama_pengirim ?? '-' }}
            @elseif($col == 'dibayar_kepada')
              @if($dokumen->dibayarKepadas->count() > 0)
                {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
              @else
                {{ $dokumen->dibayar_kepada ?? '-' }}
              @endif
            @elseif($col == 'no_berita_acara')
              {{ $dokumen->no_berita_acara ?? '-' }}
            @elseif($col == 'tanggal_berita_acara')
              {{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d-m-Y') : '-' }}
            @elseif($col == 'no_spk')
              {{ $dokumen->no_spk ?? '-' }}
            @elseif($col == 'tanggal_spk')
              {{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d-m-Y') : '-' }}
            @elseif($col == 'tanggal_berakhir_spk')
              {{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d-m-Y') : '-' }}
            @else
              -
            @endif
          </td>
        @endforeach
        <td class="col-action sticky-column" onclick="event.stopPropagation()">
          <div class="action-buttons">
            @php
              $isSent = ($dokumen->status ?? '') == 'sent_to_ibub'
                       || (($dokumen->current_handler ?? 'ibuA') == 'ibuB' && ($dokumen->status ?? '') != 'returned_to_ibua');
              $canSend = in_array($dokumen->status, ['draft', 'returned_to_ibua', 'sedang diproses'])
                        && ($dokumen->current_handler ?? 'ibuA') == 'ibuA'
                        && ($dokumen->created_by ?? 'ibuA') == 'ibuA';
            @endphp
            @unless($isSent)
              <a href="{{ route('dokumens.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
                <i class="fa-solid fa-edit"></i>
                <span>Edit</span>
              </a>
            @endunless
            @if($canSend)
            <button class="btn-action btn-send" onclick="sendToIbuB({{ $dokumen->id }})" title="Kirim ke Ibu Yuni">
              <i class="fa-solid fa-paper-plane"></i>
              <span>Kirim</span>
            </button>
            @elseif($isSent)
            <button class="btn-action btn-send" disabled title="Dokumen sudah dikirim ke Ibu Yuni">
              <i class="fa-solid fa-paper-plane"></i>
              <span>Kirim</span>
            </button>
            @endif
          </div>
        </td>
      </tr>
      <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display: none;">
        <td colspan="{{ count($selectedColumns) + 2 }}">
          <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
            <div class="loading-spinner">
              <i class="fa-solid fa-spinner fa-spin"></i>
              <span>Memuat detail dokumen...</span>
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="{{ count($selectedColumns) + 2 }}" class="text-center py-4">
          <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
          <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
          <a href="{{ route('dokumens.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Tambah Dokumen
          </a>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<!-- Pagination -->
@if($dokumens->hasPages())
<div class="pagination">
    {{-- Previous Page Link --}}
    @if($dokumens->onFirstPage())
        <button class="btn-chevron" disabled>
            <i class="fa-solid fa-chevron-left"></i>
        </button>
    @else
        <a href="{{ $dokumens->appends(request()->query())->previousPageUrl() }}">
            <button class="btn-chevron">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </a>
    @endif

    {{-- Pagination Elements --}}
    @if($dokumens->hasPages())
        {{-- First page --}}
        @if($dokumens->currentPage() > 3)
            <a href="{{ $dokumens->appends(request()->query())->url(1) }}">
                <button>1</button>
            </a>
        @endif

        {{-- Dots --}}
        @if($dokumens->currentPage() > 4)
            <button disabled>...</button>
        @endif

        {{-- Range of pages --}}
        @for($i = max(1, $dokumens->currentPage() - 2); $i <= min($dokumens->lastPage(), $dokumens->currentPage() + 2); $i++)
            @if($dokumens->currentPage() == $i)
                <button class="active">{{ $i }}</button>
            @else
                <a href="{{ $dokumens->appends(request()->query())->url($i) }}">
                    <button>{{ $i }}</button>
                </a>
            @endif
        @endfor

        {{-- Dots --}}
        @if($dokumens->currentPage() < $dokumens->lastPage() - 3)
            <button disabled>...</button>
        @endif

        {{-- Last page --}}
        @if($dokumens->currentPage() < $dokumens->lastPage() - 2)
            <a href="{{ $dokumens->appends(request()->query())->url($dokumens->lastPage()) }}">
                <button>{{ $dokumens->lastPage() }}</button>
            </a>
        @endif
    @endif

    {{-- Next Page Link --}}
    @if($dokumens->hasMorePages())
        <a href="{{ $dokumens->appends(request()->query())->nextPageUrl() }}">
            <button class="btn-chevron">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </a>
    @else
        <button class="btn-chevron" disabled>
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    @endif
</div>

<div class="text-center mt-3">
    <small class="text-muted">
        Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }} dokumen
    </small>
</div>
@endif



<!-- Modal: Column Customization - Senior Friendly -->
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

<!-- Modal: Send Confirmation -->
<div class="modal fade" id="sendConfirmationModal" tabindex="-1" aria-labelledby="sendConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1a4d3e 0%, #0f8357 100%); color: white;">
        <h5 class="modal-title" id="sendConfirmationModalLabel">
          <i class="fa-solid fa-paper-plane me-2"></i>Konfirmasi Pengiriman
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-question-circle" style="font-size: 52px; color: #0f8357;"></i>
        </div>
        <h5 class="fw-bold mb-3">Apakah Anda yakin ingin mengirim dokumen ini ke Ibu Yuni?</h5>
        <p class="text-muted mb-0">
          Dokumen akan langsung dikirim ke Ibu Yuni dan muncul di daftar dokumen Ibu Yuni untuk diproses.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success px-4" id="confirmSendToIbuBBtn">
          <i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Send Success -->
<div class="modal fade" id="sendSuccessModal" tabindex="-1" aria-labelledby="sendSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1a4d3e 0%, #0f8357 100%); color: white;">
        <h5 class="modal-title" id="sendSuccessModalLabel">
          <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #0f8357;"></i>
        </div>
        <h5 class="fw-bold mb-2">Dokumen telah dikirim ke Ibu Yuni!</h5>
        <p class="text-muted mb-0" id="sendSuccessMessage">
          Dokumen berhasil dikirim dan langsung muncul di daftar dokumen Ibu Yuni.
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
// Enhanced interactions and animations
function toggleDetail(rowId) {
  const detailRow = document.getElementById('detail-' + rowId);
  const chevron = document.getElementById('chevron-' + rowId);

  if (detailRow.style.display === 'none' || !detailRow.style.display) {
    // Show detail with animation
    detailRow.style.display = 'table-row';
    setTimeout(() => {
      detailRow.classList.add('show');
      chevron.classList.add('rotate');
    }, 10);
  } else {
    // Hide detail
    detailRow.classList.remove('show');
    chevron.classList.remove('rotate');
    setTimeout(() => {
      detailRow.style.display = 'none';
    }, 300);
  }
}

// Simple Send to IbuB Function
function sendToIbuB(docId) {
  // Store document ID for confirmation
  document.getElementById('confirmSendToIbuBBtn').setAttribute('data-doc-id', docId);
  
  // Show confirmation modal
  const confirmationModal = new bootstrap.Modal(document.getElementById('sendConfirmationModal'));
  confirmationModal.show();
}

// Confirm and send to IbuB
function confirmSendToIbuB() {
  const docId = document.getElementById('confirmSendToIbuBBtn').getAttribute('data-doc-id');
  if (!docId) {
    console.error('Document ID not found');
    return;
  }

  // Close confirmation modal
  const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('sendConfirmationModal'));
  confirmationModal.hide();

  const btn = document.querySelector(`button[onclick="sendToIbuB(${docId})"]`);
  if (!btn) return;

  // Show loading state
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';

  fetch(`/dokumens/${docId}/send-to-ibub`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      deadline_days: null,  // No deadline from IbuA
      deadline_note: null
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Show success modal
      const successModal = new bootstrap.Modal(document.getElementById('sendSuccessModal'));
      successModal.show();

      // Reload page when success modal is closed
      const successModalEl = document.getElementById('sendSuccessModal');
      successModalEl.addEventListener('hidden.bs.modal', function() {
        location.reload();
      }, { once: true });
    } else {
      showNotification('Gagal mengirim dokumen: ' + (data.message || 'Terjadi kesalahan'), 'error');
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.', 'error');
    btn.disabled = false;
    btn.innerHTML = originalHTML;
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
    textEl.textContent = message || 'Dokumen berhasil dikirim dan akan diproses oleh Ibu Yuni.';
  }

  shouldReloadAfterSendSuccess = true;
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

// Load Document Detail with Lazy Loading
function loadDocumentDetail(documentId) {
  const detailRow = document.getElementById(`detail-${documentId}`);
  const detailContent = document.getElementById(`detail-content-${documentId}`);
  const mainRow = document.querySelector(`tr[data-id="${documentId}"]`);

  // Toggle detail visibility
  if (detailRow.style.display === 'none' || !detailRow.style.display) {
    // Show loading
    detailRow.style.display = 'table-row';
    detailContent.innerHTML = `
      <div class="loading-spinner">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <span>Memuat detail dokumen...</span>
      </div>
    `;

    // Add highlight to main row
    mainRow.classList.add('selected');

    // Fetch detail data
    fetch(`/dokumens/${documentId}/detail-ibua`)
      .then(response => response.text())
      .then(html => {
        detailContent.innerHTML = html;
        detailRow.classList.add('show');
      })
      .catch(error => {
        console.error('Error loading document detail:', error);
        detailContent.innerHTML = `
          <div class="text-center p-4 text-danger">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            Gagal memuat detail dokumen. Silakan coba lagi.
          </div>
        `;
      });
  } else {
    // Hide detail
    detailRow.style.display = 'none';
    detailRow.classList.remove('show');
    mainRow.classList.remove('selected');
  }
}

// Enhanced notification system
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
      <span>${message}</span>
    </div>
  `;

  document.body.appendChild(notification);

  // Trigger animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  // Auto remove
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Add notification styles
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
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

  .notification-error {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
`;
document.head.appendChild(notificationStyles);


// Enhanced page initialization
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

  // Initialize confirmation button click handler
  const confirmBtn = document.getElementById('confirmSendToIbuBBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', confirmSendToIbuB);
  }

  // Add smooth scroll behavior
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // Add loading states for all buttons
  document.querySelectorAll('.btn-action').forEach(button => {
    button.addEventListener('click', function() {
      if (!this.disabled) {
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
          this.style.transform = '';
        }, 100);
      }
    });
  });
});

</script>

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

// Global Functions - accessible from anywhere
function openColumnCustomizationModal() {
  const modal = document.getElementById('columnCustomizationModal');
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';

  // Initialize the modal state
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
    // Add to selection
    if (!selectedColumnsOrder.includes(columnKey)) {
      selectedColumnsOrder.push(columnKey);
    }
    checkbox.checked = true;
    columnElement.classList.add('selected');
    columnElement.setAttribute('draggable', 'true');
  } else {
    // Remove from selection
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

  // Add column headers
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

  // Add sample data rows (5 rows with realistic data)
  const sampleData = {
    'nomor_agenda': ['AGD/822/XII/2024', 'AGD/258/XII/2024', 'AGD/992/XII/2024', 'AGD/92/XII/2024', 'AGD/546/XII/2024'],
    'nomor_spp': ['627/M/SPP/8/04/2024', '32/M/SPP/3/09/2024', '205/M/SPP/5/05/2024', '331/M/SPP/19/12/2024', '580/M/SPP/28/08/2024'],
    'tanggal_masuk': ['24/11/2024 08:49', '24/11/2024 08:37', '24/11/2024 08:18', '24/11/2024 08:13', '24/11/2024 08:09'],
    'nilai_rupiah': ['Rp. 241.650.650', 'Rp. 751.897.501', 'Rp. 232.782.087', 'Rp. 490.050.679', 'Rp. 397.340.004'],
    'nomor_mirror': ['MIR-1001', 'MIR-1002', 'MIR-1003', 'MIR-1004', 'MIR-1005'],
    'status': ['✓ Terkirim', '✓ Terkirim', '✓ Terkirim', '✓ Terkirim', '✓ Terkirim'],
    'keterangan': ['Dokumen lengkap', 'Dokumen lengkap', 'Dokumen lengkap', 'Dokumen lengkap', 'Dokumen lengkap'],
    'tanggal_spp': ['15/11/2024', '10/11/2024', '20/11/2024', '18/11/2024', '22/11/2024'],
    'uraian_spp': ['Pembayaran kontraktor', 'Pembayaran vendor', 'Pembayaran supplier', 'Pembayaran jasa', 'Pembayaran material'],
    'kategori': ['Operasional', 'Investasi', 'Operasional', 'Investasi', 'Operasional'],
    'kebun': ['Kebun A', 'Kebun B', 'Kebun C', 'Kebun A', 'Kebun B'],
    'jenis_dokumen': ['SPP', 'SPP', 'SPP', 'SPP', 'SPP'],
    'jenis_pembayaran': ['Tunai', 'Transfer', 'Tunai', 'Transfer', 'Tunai'],
    'nama_pengirim': ['Ibu Tarapul', 'Ibu Tarapul', 'Ibu Tarapul', 'Ibu Tarapul', 'Ibu Tarapul'],
    'dibayar_kepada': ['PT ABC', 'PT XYZ', 'CV DEF', 'PT GHI', 'PT JKL'],
    'no_berita_acara': ['BA-001/2024', 'BA-002/2024', 'BA-003/2024', 'BA-004/2024', 'BA-005/2024'],
    'tanggal_berita_acara': ['10/11/2024', '08/11/2024', '15/11/2024', '12/11/2024', '18/11/2024'],
    'no_spk': ['SPK-001/2024', 'SPK-002/2024', 'SPK-003/2024', 'SPK-004/2024', 'SPK-005/2024'],
    'tanggal_spk': ['01/11/2024', '05/11/2024', '10/11/2024', '08/11/2024', '12/11/2024'],
    'tanggal_berakhir_spk': ['30/11/2024', '30/11/2024', '30/11/2024', '30/11/2024', '30/11/2024']
  };

  for (let i = 0; i < 5; i++) {
    previewHTML += `<tr>`;
    previewHTML += `<td>${i + 1}</td>`;

    selectedColumnsOrder.forEach(columnKey => {
      const columnLabel = availableColumnsData[columnKey] || columnKey;
      let cellValue = sampleData[columnKey] ? sampleData[columnKey][i] : `Contoh ${columnLabel} ${i + 1}`;
      
      // Format special cases
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

  // Get the filter form
  const filterForm = document.getElementById('filterForm');

  // Remove existing column inputs
  document.querySelectorAll('input[name="columns[]"]').forEach(input => {
    if (input.type === 'hidden') {
      input.remove();
    }
  });

  // Add hidden inputs for selected columns in order
  selectedColumnsOrder.forEach(columnKey => {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'columns[]';
    hiddenInput.value = columnKey;
    filterForm.appendChild(hiddenInput);
  });

  // Add enable customization flag
  const enableInput = document.createElement('input');
  enableInput.type = 'hidden';
  enableInput.name = 'enable_customization';
  enableInput.value = '1';
  filterForm.appendChild(enableInput);

  // Close modal
  closeColumnCustomizationModal();

  // Submit form to apply changes
  filterForm.submit();
}

function initializeModalState() {
  // Update column items based on current selection
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

  // Initialize drag and drop
  initializeDragAndDrop();

  // Update all displays
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
let draggedIndex = -1;

function initializeDragAndDrop() {
  const columnList = document.getElementById('columnSelectionList');
  if (!columnList) return;

  // Remove all existing event listeners by cloning
  const newList = columnList.cloneNode(true);
  columnList.parentNode.replaceChild(newList, columnList);

  // Add drag and drop to selected items only
  newList.querySelectorAll('.column-item.selected').forEach(item => {
    item.addEventListener('dragstart', handleDragStart);
    item.addEventListener('dragend', handleDragEnd);
    item.addEventListener('dragover', handleDragOver);
    item.addEventListener('dragenter', handleDragEnter);
    item.addEventListener('dragleave', handleDragLeave);
    item.addEventListener('drop', handleDrop);
  });
}

function handleDragStart(e) {
  draggedElement = this;
  draggedIndex = selectedColumnsOrder.indexOf(this.dataset.column);
  this.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/plain', this.dataset.column);
}

function handleDragEnd(e) {
  this.classList.remove('dragging');
  document.querySelectorAll('.column-item').forEach(el => {
    el.classList.remove('drag-over');
  });
  draggedElement = null;
  draggedIndex = -1;
}

function handleDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  
  if (this !== draggedElement && this.classList.contains('selected')) {
    const afterElement = getDragAfterElement(this.parentNode, e.clientY);
    const selectedItems = Array.from(this.parentNode.querySelectorAll('.column-item.selected'));
    
    if (afterElement == null) {
      this.parentNode.appendChild(draggedElement);
    } else {
      this.parentNode.insertBefore(draggedElement, afterElement);
    }
  }
  
  return false;
}

function handleDragEnter(e) {
  e.preventDefault();
  if (this !== draggedElement && this.classList.contains('selected')) {
    this.classList.add('drag-over');
  }
}

function handleDragLeave(e) {
  this.classList.remove('drag-over');
}

function handleDrop(e) {
  e.preventDefault();
  e.stopPropagation();
  
  this.classList.remove('drag-over');
  
  if (this !== draggedElement && this.classList.contains('selected')) {
    const targetKey = this.dataset.column;
    const draggedKey = draggedElement.dataset.column;
    
    // Get all selected items in current DOM order
    const columnList = document.getElementById('columnSelectionList');
    const selectedItems = Array.from(columnList.querySelectorAll('.column-item.selected'));
    
    // Find new order based on DOM position
    const newOrder = selectedItems.map(item => item.dataset.column);
    
    // Update selectedColumnsOrder
    selectedColumnsOrder = newOrder;
    
    // Update UI
    updateColumnOrderBadges();
    updatePreviewTable();
    
    // Re-initialize drag and drop for new order
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

    // Handle year dropdown - Fixed version
    const yearSelectBtn = document.getElementById('yearSelectBtn');
    const yearDropdownMenu = document.getElementById('yearDropdownMenu');
    const yearSelect = document.getElementById('yearSelect');
    const yearSelectText = document.getElementById('yearSelectText');
    const yearDropdownItems = document.querySelectorAll('.year-dropdown-item');

    if (yearSelectBtn && yearDropdownMenu && yearSelect) {
        // Toggle dropdown menu
        yearSelectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Year dropdown button clicked');

            // Toggle dropdown visibility
            if (yearDropdownMenu.style.display === 'none' || yearDropdownMenu.style.display === '') {
                yearDropdownMenu.style.display = 'block';
                yearSelectBtn.classList.add('active');
            } else {
                yearDropdownMenu.style.display = 'none';
                yearSelectBtn.classList.remove('active');
            }
        });

        // Handle year selection
        yearDropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Year item clicked:', this.getAttribute('data-year'));

                const selectedYear = this.getAttribute('data-year');

                // Update hidden input
                yearSelect.value = selectedYear;

                // Update button text
                yearSelectText.textContent = selectedYear || 'Semua Tahun';

                // Update active state
                yearDropdownItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                // Close dropdown
                yearDropdownMenu.style.display = 'none';
                yearSelectBtn.classList.remove('active');

                // Submit form to apply filter (preserve search and other params)
                const form = document.getElementById('filterForm');
                const searchInput = form.querySelector('input[name="search"]');
                const searchValue = searchInput ? searchInput.value.trim() : '';

                // Preserve search value in URL
                const url = new URL(form.action);
                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }

                if (selectedYear) {
                    url.searchParams.set('year', selectedYear);
                } else {
                    url.searchParams.delete('year');
                }

                // Preserve column customization params
                const columnInputs = form.querySelectorAll('input[name="columns[]"]');
                columnInputs.forEach(input => {
                    url.searchParams.append('columns[]', input.value);
                });

                // Redirect to new URL
                window.location.href = url.toString();
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!yearSelectBtn.contains(e.target) && !yearDropdownMenu.contains(e.target)) {
                yearDropdownMenu.style.display = 'none';
                yearSelectBtn.classList.remove('active');
            }
        });

        // Prevent dropdown from closing when clicking inside
        yearDropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Handle filter form submit to preserve all parameters
    function handleFilterSubmit(event) {
        event.preventDefault();
        const form = document.getElementById('filterForm');
        const searchInput = form.querySelector('input[name="search"]');
        const yearInput = form.querySelector('input[name="year"]');
        
        // Build URL with all parameters
        const url = new URL(form.action);
        
        // Add search parameter
        if (searchInput && searchInput.value.trim()) {
            url.searchParams.set('search', searchInput.value.trim());
        } else {
            url.searchParams.delete('search');
        }
        
        // Add year parameter
        if (yearInput && yearInput.value) {
            url.searchParams.set('year', yearInput.value);
        } else {
            url.searchParams.delete('year');
        }
        
        // Preserve column customization params
        const columnInputs = form.querySelectorAll('input[name="columns[]"]');
        columnInputs.forEach(input => {
            url.searchParams.append('columns[]', input.value);
        });
        
        // Redirect to new URL
        window.location.href = url.toString();
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
      const modal = document.getElementById('columnCustomizationModal');
      if (modal && modal.classList.contains('show') &&
          e.target === modal) {
        closeColumnCustomizationModal();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const modal = document.getElementById('columnCustomizationModal');
        if (modal && modal.classList.contains('show')) {
          closeColumnCustomizationModal();
        }
      }
    });

    // Re-initialize drag and drop when modal opens
    const modal = document.getElementById('columnCustomizationModal');
    if (modal) {
      const observer = new MutationObserver(function(mutations) {
        if (modal.classList.contains('show')) {
          setTimeout(() => {
            initializeDragAndDrop();
          }, 100);
        }
      });
      observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }
});
</script>

@endsection

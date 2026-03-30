@extends('layouts/app')
@section('content')

  <style>
    /* CRITICAL FIX: Override all possible border styles for Nilai Rupiah column */
    td.col-nilai, th.col-nilai,
    .table-enhanced td.col-nilai, .table-enhanced th.col-nilai,
    .table-dokumen td.col-nilai, .table-dokumen th.col-nilai,
    .table tbody td.col-nilai, .table thead th.col-nilai,
    [class*="col-nilai"], [class*="col-nilai"] td, [class*="col-nilai"] th {
      border-right: none !important;
      border-left: none !important;
      border-image: none !important;
      box-shadow: none !important;
      outline: none !important;
    }

    /* Additional override for any element containing "Nilai Rupiah" */
    td:has(strong:contains("Rp.")), th:contains("Nilai") {
      border-right: none !important;
    }

    /* Force override inline styles */
    td.col-nilai[style], th.col-nilai[style] {
      border-right: none !important;
      border-left: none !important;
    }

    /* =============================================
       INLINE EDITING - Spreadsheet Style
       ============================================= */
    .ie-cell {
      position: relative;
      cursor: text;
      transition: background 0.15s ease;
    }
    .ie-cell:hover {
      background: rgba(136, 151, 23, 0.08) !important;
    }
    .ie-cell:hover::after {
      content: '\f044';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      position: absolute;
      top: 4px;
      right: 5px;
      font-size: 10px;
      color: #889717;
      opacity: 0.7;
      pointer-events: none;
    }
    /* Active editing state */
    .ie-cell.ie-editing {
      padding: 2px !important;
      background: #fffdf0 !important;
      outline: 2px solid #889717 !important;
      box-shadow: 0 0 0 4px rgba(136,151,23,0.15) !important;
      z-index: 10;
    }
    /* Saving state */
    .ie-cell.ie-saving {
      background: rgba(136,151,23,0.07) !important;
      outline: 2px solid #adb5bd !important;
    }
    /* Success flash */
    .ie-cell.ie-saved {
      animation: ie-flash-ok 0.6s ease forwards;
    }
    @keyframes ie-flash-ok {
      0%   { background: rgba(40,167,69,0.25); }
      100% { background: transparent; }
    }
    /* Error flash */
    .ie-cell.ie-error {
      animation: ie-flash-err 0.6s ease forwards;
      outline: 2px solid #dc3545 !important;
    }
    @keyframes ie-flash-err {
      0%   { background: rgba(220,53,69,0.25); }
      100% { background: transparent; }
    }
    /* Input/textarea/select inside cell */
    .ie-input {
      width: 100%;
      min-width: 80px;
      border: none;
      outline: none;
      background: transparent;
      font-size: 13px;
      font-family: inherit;
      color: #212529;
      padding: 4px 6px;
      box-sizing: border-box;
      resize: none;
    }
    .ie-input:focus { outline: none; }
    .ie-input.ie-textarea {
      min-height: 60px;
      resize: vertical;
    }
    /* Saving spinner overlay */
    .ie-spinner {
      display: inline-block;
      width: 12px;
      height: 12px;
      border: 2px solid #adb5bd;
      border-top-color: #889717;
      border-radius: 50%;
      animation: ie-spin 0.6s linear infinite;
      margin-left: 5px;
      vertical-align: middle;
    }
    @keyframes ie-spin { to { transform: rotate(360deg); } }
    /* Edit mode badge on editable rows */
    tr[data-editable="true"] td.col-no::after {
      content: '\f044';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      font-size: 9px;
      color: #889717;
      margin-left: 4px;
      opacity: 0.5;
    }

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

    .search-box .form-control:focus+.input-group-text {
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
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
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
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .modal-content-custom {
      background: white;
      border-radius: 20px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
      max-width: 90%;
      width: 90%;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        transform: translateY(-30px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
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

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .panel-actions {
      display: flex;
      gap: 8px;
    }

    .btn-select-action {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 500;
      border: 1px solid #e5e7eb;
      background: #fff;
      color: #374151;
      cursor: pointer;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
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

    /* Fix for Nilai Rupiah column in preview table */
    .preview-table td.col-nilai,
    .preview-table th.col-nilai {
      border-right: none !important;
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
      overflow: visible;
      /* Changed from hidden to visible to allow scrollbar */
      width: 100%;
      max-width: 100%;
    }

    /* Horizontal Scroll Container - Enhanced */
    .table-responsive {
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: auto;
      /* Changed from thin to auto for better visibility */
      scrollbar-color: rgba(8, 62, 64, 0.5) rgba(8, 62, 64, 0.1);
      position: relative;
      width: 100%;
      max-width: 100%;
    }

    /* Webkit scrollbar styling for Chrome, Safari, Edge */
    .table-responsive::-webkit-scrollbar {
      height: 16px;
      /* Increased from 12px for better visibility */
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

    /* Enhanced table for better UX - Adopted from Team Verifikasi */
    .table-enhanced {
      border-collapse: separate;
      border-spacing: 0;
      min-width: 1200px;
      /* Minimum width for horizontal scroll */
      width: 100%;
      table-layout: auto;
      /* Allow table to expand beyond container */
    }

    .table-enhanced thead th {
      position: sticky;
      top: 0;
      z-index: 10;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      font-weight: 600;
      text-align: center;
      vertical-align: middle;
      white-space: nowrap;
      border-bottom: 2px solid #083E40;
      border-right: none;
      padding: 16px 12px;
      font-size: 13px;
    }


    .table-enhanced tbody tr {
      transition: all 0.2s ease;
      border-bottom: 1px solid #f0f0f0;
    }

    .table-enhanced tbody tr:hover {
      background: linear-gradient(135deg, rgba(136, 151, 23, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);
      transform: translateY(-1px);
    }

    /* Column width optimization for Operator */
    .table-enhanced td {
      padding: 12px;
      text-align: center;
      vertical-align: middle;
      white-space: nowrap;
      border-right: none;
    }

    .table-enhanced .col-no {
      width: 80px;
      min-width: 80px;
    }

    .table-enhanced .col-agenda {
      width: 120px;
      min-width: 120px;
    }

    .table-enhanced .col-spp {
      width: 140px;
      min-width: 140px;
    }

    .table-enhanced .col-tanggal {
      width: 160px;
      min-width: 140px;
    }

    .table-enhanced .col-nilai {
      width: 120px;
      min-width: 120px;
      border-right: none !important;
      border-left: none !important;
    }

    .table-enhanced .col-mirror {
      width: 120px;
      min-width: 120px;
    }

    .table-enhanced .col-status {
      width: 250px;
      min-width: 250px;
      max-width: 280px;
      text-align: center;
      overflow: visible;
    }

    .table-enhanced th.col-status {
      text-align: center;
    }

    .table-enhanced td.col-status {
      text-align: center;
    }

    .table-enhanced .col-status .badge-status {
      margin: 0 auto;
      display: inline-flex;
      white-space: normal;
    }

    .table-enhanced .col-keterangan {
      width: 150px;
      min-width: 130px;
    }

    .table-enhanced .col-action {
      width: 140px;
      min-width: 140px;
    }

    .table-enhanced .col-uraian {
      width: 700px;
      min-width: 500px;
      max-width: 1000px;
      word-wrap: break-word;
      white-space: normal !important;
      overflow-wrap: break-word;
      line-height: 1.6;
      vertical-align: top;
      padding: 12px;
    }

    .table-enhanced .col-uraian span {
      display: block;
      word-wrap: break-word;
      white-space: normal;
      overflow-wrap: break-word;
      line-height: 1.6;
      width: 100%;
    }

    .table-enhanced .col-dibayar_kepada,
    .table-enhanced td.col-dibayar_kepada {
      width: 250px;
      min-width: 200px;
      max-width: 300px;
      word-wrap: break-word;
      white-space: normal !important;
      overflow-wrap: break-word;
      line-height: 1.6;
      vertical-align: middle;
      text-align: center;
      padding: 12px;
    }

    /* Ensure checkbox and No columns are NOT sticky */
    .table-enhanced .col-checkbox,
    .table-enhanced .col-no {
      position: static !important;
      left: auto !important;
      z-index: auto !important;
      box-shadow: none !important;
    }

    /* Fix for Nilai Rupiah column vertical line issue */
    .table-enhanced td.col-nilai,
    .table-dokumen td.col-nilai {
      border-right: none !important;
      border-left: none !important;
      border-image: none !important;
      box-shadow: none !important;
    }

    /* Override any global table border styles for Nilai Rupiah column */
    .table tbody td.col-nilai,
    .table-enhanced tbody td.col-nilai {
      border-right: none !important;
      border-left: none !important;
    }

    /* Fix for Nilai Rupiah column header vertical line issue */
    .table-enhanced th.col-nilai,
    .table-dokumen th.col-nilai {
      border-right: none !important;
      border-left: none !important;
      border-image: none !important;
    }

    /* Dark mode support for Nilai Rupiah column */
    .dark .table-enhanced td.col-nilai,
    .dark .table-dokumen td.col-nilai,
    .dark .table tbody td.col-nilai {
      border-right: none !important;
      border-left: none !important;
      border-color: transparent !important;
    }

    .dark .table-enhanced th.col-nilai,
    .dark .table-dokumen th.col-nilai {
      border-right: none !important;
      border-left: none !important;
      border-color: transparent !important;
    }

    /* Force override for any inline styles or Bootstrap defaults */
    [class*="col-nilai"] {
      border-right: none !important;
      border-left: none !important;
    }

    /* Specific override for table cells that might have inline styles */
    td.col-nilai[style],
    th.col-nilai[style] {
      border-right: none !important;
      border-left: none !important;
    }

    /* FINAL OVERRIDE - This should be the last CSS rule loaded */
    .table-dokumen td.col-nilai,
    .table-dokumen th.col-nilai,
    .table-enhanced td.col-nilai,
    .table-enhanced th.col-nilai,
    .preview-table td.col-nilai,
    .preview-table th.col-nilai,
    table td.col-nilai,
    table th.col-nilai,
    td.col-nilai,
    th.col-nilai {
      border-right: none !important;
      border-left: none !important;
      border-image: none !important;
      box-shadow: none !important;
      outline: none !important;
      background-clip: padding-box !important;
      visibility: visible !important;
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

      .table-enhanced .col-no {
        width: 60px;
        min-width: 60px;
      }

      .table-enhanced .col-agenda {
        width: 100px;
        min-width: 100px;
      }

      .table-enhanced .col-spp {
        width: 120px;
        min-width: 120px;
      }

      .table-enhanced .col-tanggal {
        width: 130px;
        min-width: 130px;
      }

      .table-enhanced .col-nilai {
        width: 100px;
        min-width: 100px;
        border-right: none !important;
        border-left: none !important;
      }

      .table-enhanced .col-mirror {
        width: 100px;
        min-width: 100px;
      }

      .table-enhanced .col-status {
        width: 90px;
        min-width: 90px;
      }

      .table-enhanced .col-keterangan {
        width: 110px;
        min-width: 110px;
      }

      .table-enhanced .col-action {
        width: 120px;
        min-width: 120px;
      }

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

      .table-enhanced .col-no {
        width: 50px;
        min-width: 50px;
      }

      .table-enhanced .col-agenda {
        width: 90px;
        min-width: 90px;
      }

      .table-enhanced .col-spp {
        width: 100px;
        min-width: 100px;
      }

      .table-enhanced .col-tanggal {
        width: 120px;
        min-width: 120px;
      }

      .table-enhanced .col-nilai {
        width: 80px;
        min-width: 80px;
        border-right: none !important;
        border-left: none !important;
      }

      .table-enhanced .col-mirror {
        width: 80px;
        min-width: 80px;
      }

      .table-enhanced .col-status {
        width: 80px;
        min-width: 80px;
      }

      .table-enhanced .col-keterangan {
        width: 90px;
        min-width: 90px;
      }

      .table-enhanced .col-action {
        width: 100px;
        min-width: 100px;
      }
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
      border-right: none;
    }

    /* Enhanced Detail Row Styles - Adopted from Team Verifikasi */
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
      gap: 6px;
      /* Tambah gap untuk background spacing */
      min-width: 0;
      width: 100%;
      overflow: visible;
      background: #ffffff;
      /* Putih bersih untuk contrast dengan label */
      border-radius: 8px;
      padding: 12px;
      border: 1px solid #f1f5f9;
      /* Border yang sangat tipis */
      transition: all 0.2s ease;
    }

    .detail-item:hover {
      border-color: #e2e8f0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .detail-label {
      display: inline-block;
      /* Inline block untuk background yang tepat */
      font-size: 11px;
      font-weight: 700;
      /* Extra bold */
      color: #374151;
      /* text-gray-700 - lebih gelap untuk kontras maksimal */
      text-transform: uppercase;
      letter-spacing: 0.7px;
      background: #f3f4f6;
      /* bg-gray-100 - background yang jelas terlihat */
      padding: 6px 10px;
      /* Padding yang visible */
      border-radius: 6px;
      /* Rounded corners yang lembut */
      border-left: 3px solid #6366f1;
      /* Aksen biru di kiri untuk visual distinction */
      margin-bottom: 2px;
      word-wrap: break-word;
      overflow-wrap: break-word;
      white-space: normal;
      max-width: 100%;
      width: fit-content;
      /* Hanya selebar teks */
      min-width: 120px;
      /* Minimum width untuk konsistensi */
    }

    .detail-value {
      font-size: 14px;
      color: #111827;
      /* text-gray-900 - hampir hitam */
      font-weight: 600;
      /* Semi-bold untuk menonjol sebagai data utama */
      word-wrap: break-word;
      overflow-wrap: break-word;
      word-break: break-word;
      hyphens: auto;
      white-space: normal;
      max-width: 100%;
      width: 100%;
      overflow: visible;
      line-height: 1.6;
      padding: 4px 0;
      /* Sedikit padding atas/bawah */
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
      max-width: 250px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.3s ease;
      white-space: normal;
      word-wrap: break-word;
      line-height: 1.3;
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
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      animation: shimmer 2s infinite;
    }

    /* State 2: Sudah Dikirim ke Team Verifikasi */
    .badge-status.badge-terkirim {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border-color: #083E40;
    }

    /* State 2a: Document Approved - Special styling */
    .badge-status.badge-approved {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border-color: #083E40;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.4);
      position: relative;
      overflow: hidden;
      border-width: 2.5px;
      /* Slightly thicker border */
    }

    .badge-status.badge-approved::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      animation: shimmer-approved 2.5s infinite;
    }

    .badge-status.badge-approved::after {
      content: '';
      position: absolute;
      top: 2px;
      right: 2px;
      width: 8px;
      height: 8px;
      background: #4ade80;
      border-radius: 50%;
      box-shadow: 0 0 8px rgba(74, 222, 128, 0.6);
      animation: pulse-approved 2s infinite;
    }

    @keyframes pulse-approved {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.7;
        transform: scale(1.3);
      }
    }

    @keyframes shimmer-approved {
      0% {
        left: -100%;
      }

      100% {
        left: 100%;
      }
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
      content: '';
      display: none;
    }

    .badge-status.badge-dikembalikan a {
      color: white;
      text-decoration: underline;
      font-weight: 600;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .badge-status.badge-dikembalikan a:hover {
      color: #ffeb3b;
      text-decoration: underline;
      text-shadow: 0 0 5px rgba(255, 235, 59, 0.5);
    }

    /* Enhanced hover effects */
    .badge-status:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    /* Animations */
    @keyframes shimmer {
      0% {
        left: -100%;
      }

      100% {
        left: 100%;
      }
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.5;
        transform: scale(1.2);
      }
    }

    /* Responsive Status Badges */
    @media (max-width: 768px) {
      .badge-status {
        padding: 6px 12px;
        font-size: 11px;
        min-width: 80px;
        max-width: 200px;
        gap: 4px;
        white-space: normal;
        line-height: 1.2;
      }

      .badge-status.badge-terkirim::after {
        width: 4px;
        height: 4px;
        margin-left: 4px;
      }

      .badge-status.badge-approved::before {
        animation-duration: 3s;
        /* Slower animation on mobile */
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
        display: none;
        /* Hide text on very small screens, show only icons */
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

    /* Action button types with dark teal-green color (matching badge Terkirim) */
    .btn-edit {
      background: #083E40;
      color: white;
    }

    .btn-edit:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-send {
      background: #083E40;
      color: white;
    }

    .btn-send:hover {
      background: #0a4f52;
      color: white;
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
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* Final optimization for consistent styling */
    .table-container {
      border-radius: 16px;
      overflow-x: auto;
      /* Enable horizontal scroll */
      overflow-y: visible;
      /* Allow vertical content */
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
      width: 100%;
      max-width: 100%;
      position: relative;
      /* Force scrollbar to always be visible when content overflows */
      scrollbar-gutter: stable;
    }

    /* Ensure scrollbar is always visible when needed */
    .table-container:has(.table-enhanced) {
      overflow-x: scroll;
      /* Force scrollbar to appear */
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
      padding-bottom: 5px;
      /* Add space for scrollbar */
      margin-bottom: 5px;
      /* Add margin for scrollbar visibility */
    }

    /* Ensure scrollbar is always visible on all browsers */
    /* Ensure scrollbar is always visible on all browsers */
    .table-responsive {
      scrollbar-width: auto;
      scrollbar-color: rgba(8, 62, 64, 0.6) rgba(8, 62, 64, 0.1);
    }

    /* Ensure horizontal scrollbar appears when content overflows */
    .table-container .table-responsive {
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
    }

    /* ===== Multi-Select Bulk Send Styles ===== */

    /* Checkbox Column */
    .col-checkbox {
      width: 45px !important;
      min-width: 45px !important;
      max-width: 45px !important;
      text-align: center;
      padding: 8px !important;
      position: sticky;
      left: 0;
      background: inherit;
      z-index: 2;
    }

    .col-checkbox::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 1px;
      background: rgba(8, 62, 64, 0.1);
    }

    /* Custom Checkbox Styling */
    .bulk-checkbox {
      width: 18px;
      height: 18px;
      border: 2px solid #dee2e6;
      border-radius: 4px;
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      background: white;
      transition: all 0.2s ease;
      position: relative;
    }

    .bulk-checkbox:hover {
      border-color: #889717;
      box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
    }

    .bulk-checkbox:checked {
      background: linear-gradient(135deg, #889717 0%, #6b7814 100%);
      border-color: #889717;
    }

    .bulk-checkbox:checked::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-size: 12px;
      font-weight: bold;
    }

    .bulk-checkbox:disabled {
      background: #f8f9fa;
      border-color: #dee2e6;
      cursor: not-allowed;
      opacity: 0.5;
    }

    /* Select All Checkbox */
    #selectAllCheckbox {
      width: 18px;
      height: 18px;
    }

    /* Selected Row Highlight */
    .main-row.selected-for-bulk {
      background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(136, 151, 23, 0.04) 100%) !important;
      border-left: 3px solid #889717 !important;
    }

    .main-row.selected-for-bulk:hover {
      background: linear-gradient(135deg, rgba(136, 151, 23, 0.12) 0%, rgba(136, 151, 23, 0.08) 100%) !important;
    }

    /* Floating Action Bar */
    .bulk-action-bar {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%) translateY(100px);
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      padding: 16px 24px;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.4), 0 4px 16px rgba(0, 0, 0, 0.2);
      z-index: 1050;
      display: flex;
      align-items: center;
      gap: 20px;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .bulk-action-bar.visible {
      transform: translateX(-50%) translateY(0);
      opacity: 1;
      visibility: visible;
    }

    .bulk-action-bar .selected-count {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 15px;
      font-weight: 500;
    }

    .bulk-action-bar .selected-count .count-badge {
      background: rgba(255, 255, 255, 0.2);
      padding: 4px 12px;
      border-radius: 20px;
      font-weight: 700;
    }

    .bulk-action-bar .action-buttons {
      display: flex;
      gap: 12px;
    }

    .bulk-action-bar .btn-cancel-selection {
      background: rgba(255, 255, 255, 0.15);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.3);
      padding: 10px 18px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .bulk-action-bar .btn-cancel-selection:hover {
      background: rgba(255, 255, 255, 0.25);
    }

    .bulk-action-bar .btn-bulk-send {
      background: linear-gradient(135deg, #889717 0%, #6b7814 100%);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(136, 151, 23, 0.3);
    }

    .bulk-action-bar .btn-bulk-send:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(136, 151, 23, 0.4);
    }

    .bulk-action-bar .btn-bulk-send:disabled {
      background: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* Bulk Send Confirmation Modal */
    .bulk-send-modal .modal-content {
      border-radius: 20px;
      border: none;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .bulk-send-modal .modal-header {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      padding: 20px 24px;
      border: none;
    }

    .bulk-send-modal .modal-header .modal-title {
      font-weight: 700;
      font-size: 18px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .bulk-send-modal .modal-header .btn-close {
      filter: brightness(0) invert(1);
      opacity: 0.8;
    }

    .bulk-send-modal .modal-body {
      padding: 24px;
      max-height: 400px;
      overflow-y: auto;
    }

    .bulk-send-modal .document-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .bulk-send-modal .document-list-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      background: #f8f9fa;
      border-radius: 10px;
      margin-bottom: 8px;
      transition: all 0.2s ease;
    }

    .bulk-send-modal .document-list-item:hover {
      background: #e9ecef;
    }

    .bulk-send-modal .document-list-item .doc-icon {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, #889717 0%, #6b7814 100%);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 14px;
    }

    .bulk-send-modal .document-list-item .doc-info {
      flex: 1;
    }

    .bulk-send-modal .document-list-item .doc-agenda {
      font-weight: 600;
      color: #083E40;
      font-size: 14px;
    }

    .bulk-send-modal .document-list-item .doc-spp {
      font-size: 12px;
      color: #6c757d;
    }

    .bulk-send-modal .document-list-item .doc-value {
      font-weight: 600;
      color: #889717;
      font-size: 14px;
    }

    .bulk-send-modal .modal-footer {
      padding: 16px 24px;
      border-top: 1px solid #e9ecef;
      background: #f8f9fa;
    }

    .bulk-send-modal .summary-info {
      background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, rgba(136, 151, 23, 0.05) 100%);
      border: 1px solid rgba(136, 151, 23, 0.2);
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 16px;
    }

    .bulk-send-modal .summary-info .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
    }

    .bulk-send-modal .summary-info .summary-row:last-child {
      margin-bottom: 0;
      padding-top: 8px;
      border-top: 1px solid rgba(136, 151, 23, 0.2);
      font-weight: 700;
    }

    .bulk-send-modal .summary-info .summary-label {
      color: #495057;
    }

    .bulk-send-modal .summary-info .summary-value {
      font-weight: 600;
      color: #083E40;
    }

    /* Loading Overlay for Bulk Send */
    .bulk-send-loading {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(8, 62, 64, 0.9);
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 20px;
    }

    .bulk-send-loading.visible {
      display: flex;
    }

    .bulk-send-loading .loading-spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(255, 255, 255, 0.2);
      border-top-color: #889717;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    .bulk-send-loading .loading-text {
      color: white;
      font-size: 18px;
      font-weight: 600;
    }

    .bulk-send-loading .loading-progress {
      color: rgba(255, 255, 255, 0.7);
      font-size: 14px;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Responsive for Bulk Action Bar */
    @media (max-width: 576px) {
      .bulk-action-bar {
        left: 16px;
        right: 16px;
        transform: translateX(0) translateY(100px);
        padding: 12px 16px;
        flex-direction: column;
        gap: 12px;
      }

      .bulk-action-bar.visible {
        transform: translateX(0) translateY(0);
      }

      .bulk-action-bar .action-buttons {
        width: 100%;
      }

      .bulk-action-bar .btn-cancel-selection,
      .bulk-action-bar .btn-bulk-send {
        flex: 1;
        justify-content: center;
      }
    }

    /* Refresh Button */
    .btn-refresh {
      padding: 10px 20px;
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(23, 162, 184, 0.3);
      min-height: 44px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-refresh:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
      background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
    }
    .btn-refresh:active { transform: translateY(0); }
    .btn-refresh.loading { opacity: 0.8; cursor: wait; }
    .btn-refresh.loading i { animation: refreshSpin 1s linear infinite; }
    @keyframes refreshSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .refresh-toast {
      position: fixed; top: 20px; right: 20px; padding: 12px 20px;
      border-radius: 10px; color: white; font-size: 14px; font-weight: 500;
      z-index: 99999; display: flex; align-items: center; gap: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.5s forwards;
    }
    .refresh-toast.success { background: linear-gradient(135deg, #28a745, #218838); }
    .refresh-toast.error { background: linear-gradient(135deg, #dc3545, #c82333); }
    @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-10px); } }
  </style>

  <h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

  <!-- Alert Messages -->
  <!-- Success notification is handled by layout/app.blade.php to avoid duplication -->
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert"
      style="margin-bottom: 20px; border-radius: 10px;">
      <i class="fa-solid fa-exclamation-triangle me-2"></i>
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Enhanced Search & Filter Box -->
  <div class="search-box">
    <form action="{{ route('documents.index') }}" method="GET" id="filterForm" class="search-filter-form">
      <div class="input-group search-input-group">
        <span class="input-group-text">
          <i class="fa-solid fa-magnifying-glass text-muted"></i>
        </span>
        <input type="text" class="form-control" id="searchInput" name="search" placeholder="Cari nomor agenda, SPP, nilai rupia"
          value="{{ request('search') }}">
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
      <div class="status-dropdown-wrapper" style="position: relative;">
        <button type="button" class="btn-year-select" id="statusSelectBtn">
          <span id="statusSelectText">
            @php
              $statusFilter = request('status_filter');
              $statusLabels = [
                '' => 'Semua Status',
                'belum_dikirim' => 'Belum Dikirim',
                'menunggu_approval' => 'Menunggu Approve Team Verifikasi',
                'terkirim' => 'Terkirim'
              ];
            @endphp
            {{ $statusLabels[$statusFilter] ?? 'Semua Status' }}
          </span>
          <i class="fa-solid fa-chevron-down ms-2"></i>
        </button>
        <div class="year-dropdown-menu" id="statusDropdownMenu" style="display: none;">
          <a href="#" class="year-dropdown-item {{ !request('status_filter') ? 'active' : '' }}" data-status="">
            Semua Status
          </a>
          <a href="#" class="year-dropdown-item {{ request('status_filter') == 'belum_dikirim' ? 'active' : '' }}"
            data-status="belum_dikirim">
            Belum Dikirim
          </a>
          <a href="#" class="year-dropdown-item {{ request('status_filter') == 'menunggu_approval' ? 'active' : '' }}"
            data-status="menunggu_approval">
            Menunggu Approve Team Verifikasi
          </a>
          <a href="#" class="year-dropdown-item {{ request('status_filter') == 'terkirim' ? 'active' : '' }}"
            data-status="terkirim">
            Terkirim
          </a>
        </div>
        <input type="hidden" name="status_filter" id="statusSelect" value="{{ request('status_filter') }}">
      </div>
      <button type="submit" class="btn-filter" id="filterBtn" onclick="handleFilterSubmit(event)">
        <i class="fa-solid fa-magnifying-glass me-2"></i>Cari
      </button>
      <button type="button" class="btn-refresh" id="btnRefreshTable" onclick="refreshDocumentTable()">
        <i class="fa-solid fa-arrows-rotate"></i> Refresh
      </button>
      <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
        <i class="fa-solid fa-table-columns me-2"></i>
        Kustomisasi Kolom Tabel
      </button>
    </form>
  </div>

  @if(isset($suggestions) && !empty($suggestions) && request('search'))
    <!-- Search Suggestions Alert -->
    <div class="alert alert-info alert-dismissible fade show suggestion-alert" role="alert"
      style="margin-bottom: 20px; border-left: 4px solid #0dcaf0; background-color: #e7f3ff;">
      <div class="d-flex align-items-start">
        <i class="fa-solid fa-lightbulb me-2 mt-1" style="color: #0dcaf0; font-size: 18px;"></i>
        <div style="flex: 1;">
          <strong style="color: #0a58ca;">Apakah yang Anda maksud?</strong>
          <p class="mb-2 mt-2" style="color: #055160;">
            Tidak ada hasil ditemukan untuk "<strong>{{ request('search') }}</strong>". Mungkin maksud Anda:
          </p>
          <div class="suggestion-buttons d-flex flex-wrap gap-2">
            @foreach($suggestions as $suggestion)
              <button type="button" class="btn btn-sm btn-outline-primary suggestion-btn" data-suggestion="{{ $suggestion }}"
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

  <!-- Per-page dropdown at the top -->
  @include('partials.pagination-perpage-top', ['paginator' => $dokumens])

  <!-- AJAX All-rows progress bar -->
  <div id="ajaxLoadBar" style="display:none; margin-bottom:10px;">
    <div style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:#fffdf0; border:1px solid #c8d000; border-radius:10px;">
      <div style="flex:1; height:8px; background:#e9ecef; border-radius:4px; overflow:hidden;">
        <div id="ajaxLoadBarFill" style="height:100%; width:0%; background:linear-gradient(90deg,#083E40,#889717); border-radius:4px; transition:width .3s ease;"></div>
      </div>
      <span id="ajaxLoadBarText" style="font-size:12px; color:#495057; white-space:nowrap; min-width:130px; text-align:right;">Memuat data...</span>
      <button id="ajaxLoadCancel" onclick="cancelAjaxLoad()" style="background:none;border:none;color:#dc3545;cursor:pointer;font-size:12px;padding:0 4px;" title="Batalkan">&#10005;</button>
    </div>
  </div>

  <!-- Enhanced Tabel Dokumen -->
  <div class="table-dokumen" id="documentTableContainer">
    <div class="table-responsive table-container">
      <table class="table table-enhanced mb-0">
        <thead>
          <tr>
            <th class="col-checkbox">
              <input type="checkbox" id="selectAllCheckbox" class="bulk-checkbox" title="Pilih Semua">
            </th>
            <th class="col-no">No</th>
            @php
              $filteredColumns = array_filter($selectedColumns, function ($col) use ($availableColumns) {
                return $col !== 'nomor_mirror' && $col !== 'keterangan' && isset($availableColumns[$col]);
              });
            @endphp
            @foreach($filteredColumns as $col)
              @if($col === 'nomor_agenda')
                <th class="col-{{ $col }}" onclick="toggleSort('{{ $col }}')" style="cursor:pointer; user-select:none; white-space:nowrap;">
                  {{ $availableColumns[$col] }}
                  <span style="display:inline-flex; flex-direction:column; line-height:0.5; margin-left:4px; font-size:10px; vertical-align:middle;">
                    <i class="fas fa-caret-up" style="opacity:{{ (isset($sortColumn) && $sortColumn==$col && isset($sortOrder) && $sortOrder=='asc') ? '1' : '0.3' }}"></i>
                    <i class="fas fa-caret-down" style="opacity:{{ (isset($sortColumn) && $sortColumn==$col && isset($sortOrder) && $sortOrder=='desc') ? '1' : '0.3' }}"></i>
                  </span>
                </th>
              @else
                <th class="col-{{ $col }}">{{ $availableColumns[$col] }}</th>
              @endif
            @endforeach
            <th class="col-action">Aksi</th>
          </tr>
        </thead>
        <tbody id="dokumenTableBody">
          @forelse($dokumens as $index => $dokumen)
            @php
              // === Compute canSend early for checkbox ===
              // Check if document has been sent to Team Verifikasi
              $isSentToTeamVerifikasiForCheck = ($dokumen->status ?? '') == 'sent_to_team_verifikasi'
                || (($dokumen->current_handler ?? 'operator') == 'team_verifikasi' && ($dokumen->status ?? '') != 'returned_to_operator');

              // Check if approved by Team Verifikasi (use eager-loaded collection, no extra query)
              $teamVerifikasiStatusForCheck = $dokumen->roleStatuses
                ->where('role_code', 'team_verifikasi')
                ->where('status', 'approved')
                ->first();
              $isApprovedByTeamVerifikasiForCheck = $teamVerifikasiStatusForCheck !== null;

              // Check if rejected
              $teamVerifikasiRejectedForCheck = $dokumen->roleStatuses
                ->where('role_code', 'team_verifikasi')
                ->where('status', 'rejected')
                ->first();
              $isRejectedForCheck = $teamVerifikasiRejectedForCheck !== null;

              // Check if returned
              if (!$isRejectedForCheck && strtolower($dokumen->status ?? '') === 'returned_to_operator') {
                $hasAnyRejectionForCheck = $dokumen->roleStatuses->where('status', 'rejected')->isNotEmpty();
                if ($hasAnyRejectionForCheck) {
                  $isRejectedForCheck = true;
                }
              }

              // Check if sent to other roles
              $isSentToOtherRolesForCheck = in_array($dokumen->status ?? '', [
                'sent_to_perpajakan',
                'sent_to_akutansi',
                'sent_to_pembayaran',
                'pending_approval_perpajakan',
                'pending_approval_akutansi',
                'pending_approval_pembayaran'
              ]);

              // Final isSent check
              $isSentForCheck = ($isSentToTeamVerifikasiForCheck || ($isApprovedByTeamVerifikasiForCheck && $isSentToOtherRolesForCheck)) && !$isRejectedForCheck;

              // Compute canSend
              $createdByOperatorForCheck = in_array(strtolower($dokumen->created_by ?? ''), ['operator']);
              $currentHandlerOperatorForCheck = in_array(strtolower($dokumen->current_handler ?? ''), ['operator']);
              $isReturnedForCheck = strtolower($dokumen->status ?? '') === 'returned_to_operator';
              // Check if document is from Bagian (menunggu_approval_keuangan)
              $isFromBagianForCheck = strtolower($dokumen->status ?? '') === 'menunggu_approval_keuangan' && $currentHandlerOperatorForCheck;

              $canSendForBulk = false;
              // PRIORITY 0: Documents from Bagian waiting for approval can be sent
              if ($isFromBagianForCheck && !$isSentForCheck) {
                $canSendForBulk = true;
              }
              // PRIORITY 1: Rejected documents with Operator
              elseif ($isRejectedForCheck && $currentHandlerOperatorForCheck && !$isSentForCheck) {
                $canSendForBulk = true;
              }
              // PRIORITY 2: Returned documents
              elseif ($isReturnedForCheck && $currentHandlerOperatorForCheck && !$isSentForCheck) {
                $canSendForBulk = true;
              }
              // PRIORITY 3: Normal documents (draft, sedang diproses)
              elseif (in_array(strtolower($dokumen->status ?? ''), ['draft', 'sedang diproses']) && $currentHandlerOperatorForCheck && !$isSentForCheck) {
                $canSendForBulk = true;
              }
            @endphp
            @php
              $canInlineEdit = $currentHandlerOperatorForCheck && in_array(strtolower($dokumen->status ?? ''), ['draft','returned_to_operator','belum_dikirim','belum dikirim','menunggu_approval_keuangan']) || ($isRejectedForCheck && $currentHandlerOperatorForCheck);
            @endphp
            <tr class="main-row clickable-row" data-id="{{ $dokumen->id }}"
              data-nomor-agenda="{{ $dokumen->nomor_agenda }}"
              data-nomor-spp="{{ $dokumen->nomor_spp }}"
              data-nilai-rupiah="{{ $dokumen->formatted_nilai_rupiah }}"
              data-can-send="{{ $canSendForBulk ? 'true' : 'false' }}"
              data-editable="{{ $canInlineEdit ? 'true' : 'false' }}"
              data-dokumen-id="{{ $dokumen->id }}"
              ondblclick="handleRowClick(event, {{ $dokumen->id }})" title="Double klik untuk melihat detail">
              <td class="col-checkbox" onclick="event.stopPropagation()">
                @if($canSendForBulk)
                  <input type="checkbox" 
                    class="bulk-checkbox doc-checkbox" 
                    data-id="{{ $dokumen->id }}"
                    data-nomor-agenda="{{ $dokumen->nomor_agenda }}"
                    data-nomor-spp="{{ $dokumen->nomor_spp }}"
                    data-nilai-rupiah="{{ $dokumen->formatted_nilai_rupiah }}"
                    title="Pilih dokumen ini untuk bulk send">
                @else
                  <input type="checkbox" class="bulk-checkbox" disabled title="Dokumen ini tidak dapat dikirim">
                @endif
              </td>
              <td class="col-no">{{ $dokumens->firstItem() + $index }}</td>
              @php
                $filteredColumns = array_filter($selectedColumns, function ($col) use ($availableColumns) {
                  return $col !== 'nomor_mirror' && $col !== 'keterangan' && isset($availableColumns[$col]);
                });
              @endphp
              @foreach($filteredColumns as $col)
                @php
                  $nonEditableCols = ['tanggal_masuk','status','nomor_mirror','keterangan'];
                  $isCellEditable = $canInlineEdit && !in_array($col, $nonEditableCols);
                @endphp
                @php
                  $dateCols = ['tanggal_spp','tanggal_berita_acara','tanggal_spk','tanggal_berakhir_spk','tanggal_faktur','tanggal_paraf','tanggal_miro'];
                  if ($isCellEditable) {
                    if ($col === 'nilai_rupiah') {
                      $ieRaw = $dokumen->nilai_rupiah ?? '';
                    } elseif (in_array($col, $dateCols)) {
                      $ieRaw = $dokumen->$col ? $dokumen->$col->format('Y-m-d') : '';
                    } elseif ($col === 'dibayar_kepada') {
                      $ieRaw = $dokumen->dibayarKepadas->pluck('nama_penerima')->implode("\n");
                    } else {
                      $ieRaw = $dokumen->$col ?? '';
                    }
                  }
                @endphp
                <td class="col-{{ $col }}{{ $isCellEditable ? ' ie-cell' : '' }}"
                  @if($isCellEditable)
                    data-field="{{ $col }}"
                    data-raw="{{ $ieRaw }}"
                  @endif
                  >
                  @if($col == 'nomor_agenda')
                    <strong>{{ $dokumen->nomor_agenda }}</strong>
                    <br>
                    <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
                  @elseif($col == 'nomor_spp')
                    <span class="select-text">{{ $dokumen->nomor_spp }}</span>
                  @elseif($col == 'tanggal_masuk')
                    <span
                      class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d-m-Y H:i') : '-' }}</span>
                  @elseif($col == 'nilai_rupiah')
                    <strong class="select-text">{{ $dokumen->formatted_nilai_rupiah }}</strong>
                  @elseif($col == 'status')
                    @php
                      // === CHECK PRIORITAS: Apakah dokumen ditolak oleh Team Verifikasi dari inbox? ===
                      // Ini harus dicek PERTAMA karena rejection bisa terjadi setelah display_status diset
                      $teamVerifikasiRejectedStatus = $dokumen->roleStatuses
                        ->where('role_code', 'team_verifikasi')
                        ->where('status', 'rejected')
                        ->sortByDesc('status_changed_at')
                        ->first();
                      $isRejectedByTeamVerifikasi = $teamVerifikasiRejectedStatus !== null;
                      $rejectionReasonVerifikasi = $isRejectedByTeamVerifikasi ? ($teamVerifikasiRejectedStatus->notes ?? null) : null;

                      // === PERBAIKAN: Gunakan display_status dari dokumen_role_data untuk stabilitas ===
                      // Operator ('operator') memiliki display_status tersendiri yang tidak terpengaruh downstream
                      $OperatorDisplayStatus = $dokumen->getDisplayStatusForRole('operator');

                      // OVERRIDE: Jika ditolak oleh team verifikasi, selalu tampilkan status ditolak
                      // (override display_status yang mungkin masih menunjuk ke 'menunggu_approval_verifikasi')
                      if ($isRejectedByTeamVerifikasi) {
                        $OperatorDisplayStatus = 'ditolak_verifikasi';
                      }
                      // Fallback logic jika display_status belum diset
                      elseif (!$OperatorDisplayStatus) {
                        $handlerLower = strtolower($dokumen->current_handler ?? '');
                        $isWithOperator = in_array($handlerLower, ['operator', 'Operator', 'operator']);
                        $statusLower = strtolower($dokumen->status ?? 'draft');

                        // PRIORITY 0: Documents from Bagian waiting at Operator (draft for Operator)
                        if ($statusLower === 'menunggu_approval_keuangan' && $isWithOperator) {
                          $OperatorDisplayStatus = 'draft';
                        } else {
                          // Check team verifikasi statuses
                          $isPendingInTeamVerifikasi = $dokumen->roleStatuses
                            ->where('role_code', 'team_verifikasi')
                            ->where('status', 'pending')
                            ->isNotEmpty();

                          $teamVerifikasiHasApproved = $dokumen->roleStatuses
                            ->where('role_code', 'team_verifikasi')
                            ->where('status', 'approved')
                            ->isNotEmpty();

                          $hasPerpajakanStatus = $dokumen->roleStatuses
                            ->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran'])
                            ->isNotEmpty();

                          // === FIX: Pending di inbox Team Verifikasi = Menunggu Approval (regardless of current_handler) ===
                          // When document is sent to Team Verifikasi inbox for approval,
                          // current_handler might still be 'operator', so we can't check !$isWithOperator
                          if ($isPendingInTeamVerifikasi) {
                            $OperatorDisplayStatus = 'menunggu_approval_verifikasi';
                          } elseif ($statusLower === 'waiting_reviewer_approval' || str_contains($statusLower, 'pending_approval_team_verifikasi')) {
                            $OperatorDisplayStatus = 'menunggu_approval_verifikasi';
                          } elseif ($teamVerifikasiHasApproved || $hasPerpajakanStatus) {
                            $OperatorDisplayStatus = 'terkirim';
                          } elseif ($isWithOperator && in_array($statusLower, ['draft', 'returned_to_operator'])) {
                            $OperatorDisplayStatus = 'draft';
                          } else {
                            if (in_array($handlerLower, ['team_verifikasi', 'verifikasi', 'perpajakan', 'akutansi', 'pembayaran'])) {
                              $OperatorDisplayStatus = 'terkirim';
                            } else {
                              $OperatorDisplayStatus = 'draft';
                            }
                          }
                        }
                      }

                      // Map display status to badge
                      $statusLabel = match ($OperatorDisplayStatus) {
                        'draft' => 'Belum Dikirim',
                        'menunggu_approval_verifikasi' => 'Menunggu Approve Team Verifikasi',
                        'ditolak_verifikasi' => 'Dokumen Ditolak oleh Team Verifikasi',
                        'terkirim', 'terkirim_verifikasi', 'terkirim_perpajakan', 'terkirim_akutansi', 'terkirim_pembayaran' => 'Terkirim',
                        'selesai', 'dibayar' => 'Selesai',
                        default => 'Terkirim'
                      };
                    @endphp
                    @if($OperatorDisplayStatus === 'draft')
                      <span class="badge-status badge-draft">
                        <i class="fa-solid fa-file-lines me-1"></i>
                        <span>Belum Dikirim</span>
                      </span>
                    @elseif($OperatorDisplayStatus === 'ditolak_verifikasi')
                      <span class="badge-status badge-dikembalikan"
                        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; cursor: pointer;"
                        onclick="event.stopPropagation(); showRejectionModal({{ $dokumen->id }})">
                        <i class="fa-solid fa-times-circle me-1"></i>
                        <span>Dokumen Ditolak,
                          <span style="text-decoration: underline; font-weight: 700;">Alasan</span>
                        </span>
                      </span>
                    @elseif($OperatorDisplayStatus === 'menunggu_approval_verifikasi')
                      <span class="badge-status"
                        style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                        <i class="fa-solid fa-clock me-1"></i>
                        <span>Menunggu Approve Team Verifikasi</span>
                      </span>
                    @else
                      <span class="badge-status badge-terkirim">
                        <i class="fa-solid fa-check me-1"></i>
                        <span>{{ $statusLabel }}</span>
                      </span>
                    @endif
                  @elseif($col == 'tanggal_spp')
                    {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d-m-Y') : '-' }}
                  @elseif($col == 'uraian_spp')
                    <span title="{{ $dokumen->uraian_spp ?? '-' }}"
                      style="display: block; word-wrap: break-word; white-space: normal; overflow-wrap: break-word; line-height: 1.5; width: 100%;">
                      {{ $dokumen->uraian_spp ?? '-' }}
                    </span>
                  @elseif($col == 'kategori')
                    {{ $dokumen->kategori ?? '-' }}
                  @elseif($col == 'kebun')
                    {{ $dokumen->kebun ?? '-' }}
                  @elseif($col == 'bulan')
                    {{ $dokumen->bulan ?? '-' }}
                  @elseif($col == 'tahun')
                    {{ $dokumen->tahun ?? '-' }}
                  @elseif($col == 'nomor_miro')
                    {{ $dokumen->nomor_miro_display ?? $dokumen->nomor_miro ?? '-' }}
                  @elseif($col == 'jenis_dokumen')
                    {{ $dokumen->jenis_dokumen ?? '-' }}
                  @elseif($col == 'jenis_sub_pekerjaan')
                    {{ $dokumen->jenis_sub_pekerjaan ?? '-' }}
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
                  @elseif($col == 'bagian')
                    {{ $dokumen->bagian ?? '-' }}
                  @elseif($col == 'tanggal_paraf')
                    {{ $dokumen->tanggal_paraf ? $dokumen->tanggal_paraf->format('d/m/Y H:i') : '-' }}
                  @elseif($col == 'pemaraf')
                    @if($dokumen->pemaraf)
                      <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);color:white;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap;">
                        <i class="fa-solid fa-check-circle"></i>
                        {{ $dokumen->pemaraf }}
                      </span>
                    @else
                      -
                    @endif
                  @elseif($col == 'tanggal_selesai_diproses')
                    {{ $dokumen->tanggal_selesai_diproses ? $dokumen->tanggal_selesai_diproses->format('d/m/Y H:i') : '-' }}
                  @elseif($col == 'kepala_sub_bagian')
                    {{ $dokumen->kepala_sub_bagian ?? '-' }}
                  @elseif($col == 'status_dokumen_custom')
                    @if($dokumen->status_dokumen_csv)
                      <span class="badge-status {{ $dokumen->status_dokumen_csv == 'Selesai Dibayar' ? 'badge-selesai' : ($dokumen->status_dokumen_csv == 'Dikembalikan' ? 'badge-dikembalikan' : 'badge-proses') }}" style="font-size: 10px; padding: 4px 8px;">
                        {{ $dokumen->status_dokumen_csv }}
                      </span>
                    @else
                      -
                    @endif
                  @elseif($col == 'tanggal_dibayar')
                    {{ $dokumen->tanggal_dibayar ? \Carbon\Carbon::parse($dokumen->tanggal_dibayar)->format('d/m/Y') : '-' }}
                  @elseif($col == 'no_faktur')
                    {{ $dokumen->no_faktur ?? '-' }}
                  @elseif($col == 'tanggal_faktur')
                    {{ $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('d/m/Y') : '-' }}
                  @elseif($col == 'tanggal_selesai_verifikasi_pajak')
                    {{ $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('d/m/Y') : '-' }}
                  @elseif($col == 'jenis_pph')
                    {{ $dokumen->jenis_pph ?? '-' }}
                  @elseif($col == 'dpp_pph')
                    {{ $dokumen->dpp_pph ? number_format($dokumen->dpp_pph, 0, ',', '.') : '-' }}
                  @elseif($col == 'ppn_terhutang')
                    {{ $dokumen->ppn_terhutang ? number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-' }}
                  @else
                    -
                  @endif
                </td>
              @endforeach
              <td class="col-action" onclick="event.stopPropagation()">
                <div class="action-buttons">
                  @php
                    // Check if document has been sent to Team Verifikasi
                    $isSentToTeamVerifikasi = ($dokumen->status ?? '') == 'sent_to_team_verifikasi'
                      || (($dokumen->current_handler ?? 'operator') == 'team_verifikasi' && ($dokumen->status ?? '') != 'returned_to_operator');

                    // Check if document has been approved by Team Verifikasi (use eager-loaded collection)
                    $teamVerifikasiStatus = $dokumen->roleStatuses
                      ->whereIn('role_code', ['verifikasi', 'team_verifikasi'])
                      ->first();
                    $isApprovedByTeamVerifikasi = $teamVerifikasiStatus && $teamVerifikasiStatus->status === 'approved';

                    // Check if document is rejected
                    $isRejected = false;

                    // Method 1: Check from eager-loaded collection
                    if ($teamVerifikasiStatus && strtolower($teamVerifikasiStatus->status ?? '') === 'rejected') {
                      $isRejected = true;
                    }

                    // Method 2: Fallback - check from dokumen_statuses directly (case-insensitive)
                    if (!$isRejected) {
                      $rejectedStatus = $dokumen->roleStatuses
                        ->where('status', 'rejected')
                        ->where('role_code', 'team_verifikasi')
                        ->first();
                      $isRejected = $rejectedStatus !== null;
                    }

                    // Method 3: Check if status is returned_to_operator AND has rejection in roleStatuses
                    // This catches documents that were rejected but status might not be set correctly
                    if (!$isRejected && strtolower($dokumen->status ?? '') === 'returned_to_operator') {
                      // Check if there's any rejection status in roleStatuses relationship
                      $hasAnyRejection = $dokumen->roleStatuses
                        ->where('status', 'rejected')
                        ->isNotEmpty();
                      if ($hasAnyRejection) {
                        $isRejected = true;
                      }
                    }

                    // Check if document has been sent to Perpajakan/Akutansi/Pembayaran
                    $isSentToOtherRoles = in_array($dokumen->status ?? '', [
                      'sent_to_perpajakan',
                      'sent_to_akutansi',
                      'sent_to_pembayaran',
                      'pending_approval_perpajakan',
                      'pending_approval_akutansi',
                      'pending_approval_pembayaran'
                    ]);

                    // Document is considered "sent" if sent to Team Verifikasi OR approved by Team Verifikasi and sent to other roles
                    // BUT: rejected documents are NOT considered "sent" - they can be sent again
                    $isSent = ($isSentToTeamVerifikasi || ($isApprovedByTeamVerifikasi && $isSentToOtherRoles)) && !$isRejected;

                    // Can send only if document is draft/returned and still with Operator
                    // Include rejected documents (returned_to_operator) so they can be sent again
                    // IMPORTANT: Rejected documents should always be able to be sent again

                    // Check if document is created by Operator (case-insensitive)
                    $createdByOperator = in_array(strtolower($dokumen->created_by ?? ''), ['operator', 'Operator', 'operator']);

                    // Check if document is currently with Operator (case-insensitive)
                    $currentHandlerOperator = in_array(strtolower($dokumen->current_handler ?? ''), ['operator', 'Operator', 'operator']);

                    // Check if document is returned (case-insensitive)
                    $isReturned = strtolower($dokumen->status ?? '') === 'returned_to_operator';

                    // Check if document is from Bagian
                    // Documents from Bagian: current_handler = operator BUT created_by != operator
                    // They can have status 'menunggu_approval_keuangan' OR 'sent_to_team_verifikasi' (if re-sent)
                    $isFromBagian = $currentHandlerOperator && !$createdByOperator;

                    // Override $isSent for Bagian documents that are still with Operator
                    // These documents should be editable even if status is sent_to_team_verifikasi
                    if ($isFromBagian) {
                      $isSent = false;
                    }

                    // Initialize canSend
                    $canSend = false;

                    // PRIORITY 0: Documents from Bagian waiting for approval can be sent
                    if ($isFromBagian && !$isSent) {
                      $canSend = true;
                    }
                    // PRIORITY 1: Rejected documents can ALWAYS be sent again if they're with Operator
                    elseif ($isRejected && $currentHandlerOperator && !$isSent) {
                      $canSend = true;
                    }
                    // PRIORITY 2: Returned documents (returned_to_operator) can be sent
                    elseif ($isReturned && $currentHandlerOperator && !$isSent) {
                      $canSend = true;
                    }
                    // PRIORITY 3: Normal documents (draft, sedang diproses)
                    elseif (
                      in_array(strtolower($dokumen->status ?? ''), ['draft', 'sedang diproses'])
                      && $currentHandlerOperator
                      && !$isSent
                    ) {
                      $canSend = true;
                    }

                    // Can edit only if document is not sent and can be edited
                    $canEdit = false;

                    // PRIORITY 0: Documents from Bagian can be edited by Operator
                    if ($isFromBagian && !$isSent) {
                      $canEdit = true;
                    }
                    // PRIORITY 1: Rejected documents can ALWAYS be edited if they're with Operator
                    elseif ($isRejected && $currentHandlerOperator && !$isSent) {
                      $canEdit = true;
                    }
                    // PRIORITY 2: Returned documents (returned_to_operator) can be edited
                    elseif ($isReturned && $currentHandlerOperator && !$isSent) {
                      $canEdit = true;
                    }
                    // PRIORITY 3: Draft documents can be edited
                    elseif (
                      strtolower($dokumen->status ?? '') === 'draft'
                      && $currentHandlerOperator
                      && !$isSent
                    ) {
                      $canEdit = true;
                    }
                  @endphp
                  @if($canEdit)
                    <a href="{{ route('documents.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
                      <i class="fa-solid fa-edit"></i>
                      <span>Edit</span>
                    </a>
                  @endif
                  @if($canSend)
                    <form action="{{ route('documents.send-to-verifikasi', $dokumen->id) }}" method="POST"
                      style="display: inline;">
                      @csrf
                      <button type="submit" class="btn-action btn-send" title="Kirim ke Team Verifikasi">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim</span>
                      </button>
                    </form>
                  @elseif($isSent)
                    <button class="btn-action btn-send" disabled title="Dokumen sudah dikirim">
                      <i class="fa-solid fa-paper-plane"></i>
                      <span>Kirim</span>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
            <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display: none;">
              @php
                $filteredColumns = array_filter($selectedColumns, function ($col) use ($availableColumns) {
                  return $col !== 'nomor_mirror' && $col !== 'keterangan' && isset($availableColumns[$col]);
                });
              @endphp
              <td colspan="{{ count($filteredColumns) + 2 }}">
                <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                  <div class="loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Memuat detail dokumen...</span>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            @php
              $filteredColumns = array_filter($selectedColumns, function ($col) use ($availableColumns) {
                return $col !== 'nomor_mirror' && isset($availableColumns[$col]);
              });
            @endphp
            <tr>
              <td colspan="{{ count($filteredColumns) + 2 }}" class="text-center py-4">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
                <a href="{{ route('documents.create') }}" class="btn btn-primary">
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
  @include('partials.pagination-enhanced', ['paginator' => $dokumens])

  {{-- Modal untuk menampilkan alasan reject dari inbox --}}
  @if(isset($dokumens))
    @foreach($dokumens as $dokumen)
      @php
        $teamVerifikasiStatus = $dokumen->roleStatuses->whereIn('role_code', ['verifikasi','team_verifikasi'])->first();
        $isRejected = $teamVerifikasiStatus && $teamVerifikasiStatus->status === 'rejected';
        $rejectReason = $teamVerifikasiStatus?->notes ?? null;
      @endphp
      @if($isRejected && $rejectReason)
        <div class="modal fade" id="rejectReasonModal{{ $dokumen->id }}" tabindex="-1"
          aria-labelledby="rejectReasonModalLabel{{ $dokumen->id }}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectReasonModalLabel{{ $dokumen->id }}">
                  <i class="fas fa-times-circle me-2"></i>Alasan Penolakan Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label fw-bold">Nomor Agenda:</label>
                  <p class="mb-0">{{ $dokumen->nomor_agenda }}</p>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Nomor SPP:</label>
                  <p class="mb-0">{{ $dokumen->nomor_spp }}</p>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Ditolak oleh:</label>
                  <p class="mb-0">
                    @php
                      $rejectedBy = null;
                      // Cari dari activity log
                      $rejectLog = $dokumen->activityLogs()
                        ->where('action', 'inbox_rejected')
                        ->latest('action_at')
                        ->first();

                      if ($rejectLog) {
                        $rejectedBy = $rejectLog->performed_by ?? $rejectLog->details['rejected_by'] ?? null;
                      }

                      // Fallback ke role dari dokumen_statuses
                      if (!$rejectedBy && $teamVerifikasiStatus) {
                        $rejectedBy = $teamVerifikasiStatus->changed_by ?? 'Ibu Yuni';
                      }

                      // Format nama yang lebih ramah
                      if ($rejectedBy) {
                        $nameMap = [
                          'team_verifikasi' => 'Ibu Yuni',
                          'team_verifikasi' => 'Ibu Yuni',
                          'Perpajakan' => 'Team Perpajakan',
                          'perpajakan' => 'Team Perpajakan',
                          'Akutansi' => 'Team Akutansi',
                          'akutansi' => 'Team Akutansi',
                        ];
                        $rejectedBy = $nameMap[$rejectedBy] ?? $rejectedBy;
                      }
                    @endphp
                    {{ $rejectedBy ?? '-' }}
                  </p>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Tanggal Penolakan:</label>
                  <p class="mb-0">
                    @if($dokumen->inbox_approval_responded_at)
                      {{ $dokumen->inbox_approval_responded_at->format('d/m/Y H:i') }}
                    @else
                      -
                    @endif
                  </p>
                </div>
                <div class="mb-0">
                  <label class="form-label fw-bold">Alasan Penolakan:</label>
                  <div class="alert alert-warning mb-0">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $rejectReason ?? 'Tidak ada alasan yang dicatat' }}</p>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
              </div>
            </div>
          </div>
        </div>
      @endif
    @endforeach
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
                  draggable="{{ in_array($key, $selectedColumns) ? 'true' : 'false' }}" onclick="toggleColumn(this)">
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
                              @elseif($col == 'status')
                                <span style="color: #28a745;">✓ Terkirim</span>
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
          <h5 class="fw-bold mb-2">Dokumen telah dikirim ke Team Verifikasi!</h5>
          <p class="text-muted mb-0" id="sendSuccessMessage">
            Dokumen berhasil dikirim dan langsung muncul di daftar dokumen Team Verifikasi.
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

  <!-- Modal: Rejection Detail (Modern & Simple) -->
  <div class="modal fade" id="rejectionDetailModal" tabindex="-1" aria-labelledby="rejectionDetailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div class="modal-header"
          style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; padding: 1.5rem 2rem;">
          <h5 class="modal-title" id="rejectionDetailModalLabel" style="font-size: 1.25rem; font-weight: 600;">
            <i class="fa-solid fa-times-circle me-2"></i>Detail Penolakan Dokumen
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
            style="opacity: 0.9;"></button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
          <div id="rejectionModalLoading" class="text-center py-4">
            <div class="spinner-border text-danger" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat detail penolakan...</p>
          </div>
          <div id="rejectionModalContent" style="display: none;">
            <!-- Document Info Card -->
            <div class="card mb-4" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 12px;">
              <div class="card-body" style="padding: 1.5rem;">
                <h6 class="card-title mb-3"
                  style="color: #083E40; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="fa-solid fa-file-lines me-2" style="color: #889717;"></i>Informasi Dokumen
                </h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="info-item">
                      <span class="info-label">Nomor Agenda</span>
                      <span class="info-value" id="rejectionNomorAgenda">-</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="info-item">
                      <span class="info-label">Nomor SPP</span>
                      <span class="info-value" id="rejectionNomorSpp">-</span>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="info-item">
                      <span class="info-label">Uraian SPP</span>
                      <span class="info-value" id="rejectionUraianSpp">-</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="info-item">
                      <span class="info-label">Nilai Rupiah</span>
                      <span class="info-value text-success fw-bold" id="rejectionNilaiRupiah">-</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="info-item">
                      <span class="info-label">Tanggal Ditolak</span>
                      <span class="info-value" id="rejectionTanggal">-</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Rejection Info Card -->
            <div class="card"
              style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 12px; background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%); border-left: 4px solid #dc3545;">
              <div class="card-body" style="padding: 1.5rem;">
                <h6 class="card-title mb-3"
                  style="color: #dc3545; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="fa-solid fa-user-xmark me-2"></i>Informasi Penolakan
                </h6>
                <div class="mb-3">
                  <span class="info-label">Ditolak Oleh</span>
                  <div class="mt-1">
                    <span class="badge"
                      style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 0.5rem 1rem; font-size: 0.875rem; border-radius: 8px;">
                      <i class="fa-solid fa-user-shield me-2"></i>
                      <span id="rejectionBy">-</span>
                    </span>
                  </div>
                </div>
                <div>
                  <span class="info-label mb-2 d-block">Alasan Penolakan</span>
                  <div class="rejection-reason-box" id="rejectionReason"
                    style="background: white; padding: 1.25rem; border-radius: 10px; border: 1px solid rgba(220, 53, 69, 0.2); min-height: 80px; line-height: 1.6; color: #333;">
                    -
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div id="rejectionModalError" style="display: none;" class="text-center py-4">
            <i class="fa-solid fa-exclamation-triangle" style="font-size: 48px; color: #dc3545; margin-bottom: 1rem;"></i>
            <p class="text-danger mb-0" id="rejectionErrorMessage">Gagal memuat detail penolakan</p>
          </div>
        </div>
        <div class="modal-footer border-0 justify-content-center" style="padding: 1.5rem 2rem; background: #f8f9fa;">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
            style="border-radius: 8px; font-weight: 500;">
            <i class="fa-solid fa-times me-2"></i>Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .info-item {
      margin-bottom: 0.75rem;
    }

    .info-label {
      display: block;
      font-size: 0.75rem;
      font-weight: 600;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.25rem;
    }

    .info-value {
      display: block;
      font-size: 0.95rem;
      font-weight: 500;
      color: #083E40;
    }

    .rejection-reason-box {
      white-space: pre-wrap;
      word-wrap: break-word;
    }
  </style>

  <script>
  /* Sort toggle function */
  function toggleSort(column) {
    const url = new URL(window.location.href);
    const params = url.searchParams;
    const currentSort = params.get('sort') || 'nomor_agenda';
    const currentOrder = params.get('order') || 'desc';
    let newOrder = 'asc';
    if (currentSort === column) {
      newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
    }
    params.set('sort', column);
    params.set('order', newOrder);
    params.delete('page');
    window.location.href = url.toString();
  }
  </script>

  <script>   /* Enhanced interactions and animations */   function toggleDetail(rowId) 
       {     const detailRow = document.getElementById('detail-' + rowId);     const chevron = document.getElementById('chevron-' + rowId     );
           if (detailRow.style.display === 'none' || !detailRow.style.display) {       /* Show detail with animation */       detailRow.style.display = 'table-row';       setTimeout(() => {         detailRow.classList.add('show');         chevron.classList.add('rotate');       }, 10);     } else {       /* Hide detail */       detailRow.classList.remove('show');       chevron.classList.remove('rotate');       setTimeout(() => {         detailRow.style.display = 'none';       }, 300);     }   }
         /* Simple Send to Team Verifikasi Function */   function sendToTeamVerifikasi(docId) {     /* Store document ID for confirmation */     document.getElementById('confirmsendToTeamVerifikasiBtn').setAttribute('data-doc-id', docId);
           /* Show confirmation modal */     const confirmationModal = new bootstrap.Modal(document.getElementById('sendConfirmationModal'));     confirmationModal.show();   }
         /* Confirm and send to Team Verifikasi */   function confirmsendToTeamVerifikasi() {     const docId = document.getElementById('confirmsendToTeamVerifikasiBtn').getAttribute('data-doc-id');     if (!docId) {       console.error('Document ID not found');       return;     }
           /* Close confirmation modal */     const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('sendConfirmationModal'));     confirmationModal.hide();
           const btn = document.querySelector(`button[onclick="sendToTeamVerifikasi(${docId})"]`);     if (!btn) return;
           /* Show loading state */     const originalHTML = btn.innerHTML;     btn.disabled = true;     btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
           fetch(`/documents/${docId}/send-to-verifikasi`, {       method: 'POST',       headers: {         'Content-Type': 'application/json',         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')       },       body: JSON.stringify({         deadline_days: null,  /* No deadline from Operator */         deadline_note: null       })     })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                /* Show success modal */
                const successModal = new bootstrap.Modal(document.getElementById('sendSuccessModal'));
                successModal.show();

                /* Reload page when success modal is closed */
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
            });   }

         let shouldReloadAfterSendSuccess = false;
         function showSendSuccessModal(message) {     const modalEl = document.getElementById('sendSuccessModal');     if (!modalEl) {       location.reload();       return;     }
           const textEl = document.getElementById('sendSuccessMessage');     if (textEl) {       textEl.textContent = message || 'Dokumen berhasil dikirim dan akan diproses oleh Team Verifikasi.';     }
           shouldReloadAfterSendSuccess = true;     const modal = new bootstrap.Modal(modalEl);     modal.show();   }
         /* Wrapper function untuk handle row double-click */   function handleRowClick(event, documentId) {
           /* Cek apakah yang diklik adalah link/tombol/input/select/textarea */     const target = event.target;     const tagName = target.tagName.toLowerCase();     const isInteractiveElement         = 
              tagName === 'a' || 
              tagName === 'button' || 
              tagName === 'input' || 
              tagName === 'select' || 
              tagName === 'textarea' ||
              target.closest('a') !== null ||
              target.closest('button') !== null ||
              target.closest('.btn') !== null ||
              target.closest('.btn-action') !== null ||
              target.closest('[role="button"]') !== null;

            if (isInteractiveElement) {
              /* User klik elemen interaktif, biarkan default behavior */
              return true;
            }

            /* Clear text selection yang otomatis terjadi saat double-click */
            if (window.getSelection) {
              window.getSelection().removeAllRanges();
            }

            /* Buka modal popup untuk menampilkan detail dokumen */
            openViewDocumentModal(documentId);
            return true;
          }

          /* Load Document Detail with Lazy Loading */
          function loadDocumentDetail(documentId) {
            const detailRow = document.getElementById(`detail-${documentId}`);
            const detailContent = document.getElementById(`detail-content-${documentId}`);
            const mainRow = document.querySelector(`tr[data-id="${documentId}"]`);

            /* Toggle detail visibility */
            if (detailRow.style.display === 'none' || !detailRow.style.display) {
              /* Show loading */
              detailRow.style.display = 'table-row';
              detailContent.innerHTML = `
                <div class="loading-spinner">
                  <i class="fa-solid fa-spinner fa-spin"></i>
                  <span>Memuat detail dokumen...</span>
                </div>
              `;

              /* Add highlight to main row */
              mainRow.classList.add('selected');

              /* Fetch detail data */
              fetch(`/documents/${documentId}/detail-Operator`)
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
              /* Hide detail */
              detailRow.style.display = 'none';
              detailRow.classList.remove('show');
              mainRow.classList.remove('selected');
            }
          }

          /* Enhanced notification system */
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

            /* Trigger animation */
            setTimeout(() => {
              notification.classList.add('show');
            }, 10);

            /* Auto-hide untuk notifikasi success/error biasa setelah 4 detik */
            /* Notifikasi dokumen masuk/reject tetap permanen */
            if (type === 'success' || type === 'error') {
              setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                  if (notification.parentElement) {
                    notification.parentElement.removeChild(notification);
                  }
                }, 300);
              }, 4000); // 4 detik untuk notifikasi success/error biasa
            }
            // Jika type info atau dokumen masuk/reject, tetap permanen
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

          // Function to show rejection modal (MUST be defined before DOMContentLoaded to be globally accessible)
          window.showRejectionModal = function(dokumenId) {
            console.log('showRejectionModal called with dokumenId:', dokumenId);

            const modalEl = document.getElementById('rejectionDetailModal');
            if (!modalEl) {
              console.error('Modal element not found');
              alert('Modal tidak ditemukan. Silakan refresh halaman.');
              return;
            }

            const modal = new bootstrap.Modal(modalEl);
            const loadingEl = document.getElementById('rejectionModalLoading');
            const contentEl = document.getElementById('rejectionModalContent');
            const errorEl = document.getElementById('rejectionModalError');

            if (!loadingEl || !contentEl || !errorEl) {
              console.error('Modal elements not found');
              alert('Elemen modal tidak ditemukan. Silakan refresh halaman.');
              return;
            }

            // Reset modal state
            loadingEl.style.display = 'block';
            contentEl.style.display = 'none';
            errorEl.style.display = 'none';

            // Show modal
            modal.show();

            // Fetch rejection details
            fetch(`/api/documents/rejected/${dokumenId}`, {
              method: 'GET',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
              }
            })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
                // Populate modal with data
                document.getElementById('rejectionNomorAgenda').textContent = data.dokumen.nomor_agenda || '-';
                document.getElementById('rejectionNomorSpp').textContent = data.dokumen.nomor_spp || '-';
                document.getElementById('rejectionUraianSpp').textContent = data.dokumen.uraian_spp || '-';
                document.getElementById('rejectionNilaiRupiah').textContent = data.dokumen.nilai_rupiah || '-';
                document.getElementById('rejectionTanggal').textContent = data.rejected_at || '-';
                document.getElementById('rejectionBy').textContent = data.rejected_by || 'Unknown';
                document.getElementById('rejectionReason').textContent = data.rejection_reason || 'Tidak ada alasan yang diberikan';

                // Show content
                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';
              } else {
                throw new Error(data.message || 'Gagal memuat data');
              }
            })
            .catch(error => {
              console.error('Error loading rejection details:', error);
              loadingEl.style.display = 'none';
              errorEl.style.display = 'block';
              document.getElementById('rejectionErrorMessage').textContent = 
                'Gagal memuat detail penolakan: ' + (error.message || 'Terjadi kesalahan');
            });
          };

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
            const confirmBtn = document.getElementById('confirmsendToTeamVerifikasiBtn');
            if (confirmBtn) {
              confirmBtn.addEventListener('click', confirmsendToTeamVerifikasi);
            }

            // Add smooth scroll behavior - only for hash links (exclude modal links)
            document.querySelectorAll('a[href^="#"]:not(#view-edit-btn):not([id^="view-"])').forEach(anchor => {
              // Skip if anchor is inside a modal
              if (anchor.closest('.modal')) {
                return;
              }
              anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                // Only process hash links (starting with #) and ensure it's not empty
                if (href && href.startsWith('#') && href.length > 1) {
                  e.preventDefault();
                  try {
                    const target = document.querySelector(href);
                    if (target) {
                      target.scrollIntoView({ behavior: 'smooth' });
                    }
                  } catch (error) {
                    // Invalid selector, ignore
                    console.warn('Invalid hash selector:', href);
                  }
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

          function selectAllColumns() {
            const allKeys = Object.keys(@json($availableColumns));
            selectedColumnsOrder = allKeys;
            document.querySelectorAll('.column-item').forEach(item => {
              item.classList.add('selected');
              item.setAttribute('draggable', 'true');
              item.querySelector('.column-item-checkbox').checked = true;
            });
            updateColumnOrderBadges();
            updatePreviewTable();
            updateSelectedCount();
            updateDraggableState();
          }

          function removeAllColumns() {
            selectedColumnsOrder = [];
            document.querySelectorAll('.column-item').forEach(item => {
              item.classList.remove('selected');
              item.setAttribute('draggable', 'false');
              item.querySelector('.column-item-checkbox').checked = false;
            });
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
              'nama_pengirim': ['Operator', 'Operator', 'Operator', 'Operator', 'Operator'],
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

              // Auto-search removed - user clicks "Cari" button to search

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

                          // Preserve status_filter
                          const statusInput = form.querySelector('input[name="status_filter"]');
                          if (statusInput && statusInput.value) {
                              url.searchParams.set('status_filter', statusInput.value);
                          } else {
                              url.searchParams.delete('status_filter');
                          }

                          // Preserve per_page
                          const perPage = new URLSearchParams(window.location.search).get('per_page');
                          if (perPage) {
                              url.searchParams.set('per_page', perPage);
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

              // Status Dropdown Handler
              const statusSelectBtn = document.getElementById('statusSelectBtn');
              const statusDropdownMenu = document.getElementById('statusDropdownMenu');
              const statusSelect = document.getElementById('statusSelect');
              const statusSelectText = document.getElementById('statusSelectText');
              const statusDropdownItems = document.querySelectorAll('#statusDropdownMenu .year-dropdown-item');

              if (statusSelectBtn && statusDropdownMenu && statusSelect) {
                  // Toggle dropdown menu
                  statusSelectBtn.addEventListener('click', function(e) {
                      e.preventDefault();
                      e.stopPropagation();
                      console.log('Status dropdown button clicked');

                      // Close year dropdown if open
                      if (yearDropdownMenu) {
                          yearDropdownMenu.style.display = 'none';
                          if (yearSelectBtn) yearSelectBtn.classList.remove('active');
                      }

                      // Toggle status dropdown visibility
                      if (statusDropdownMenu.style.display === 'none' || statusDropdownMenu.style.display === '') {
                          statusDropdownMenu.style.display = 'block';
                          statusSelectBtn.classList.add('active');
                      } else {
                          statusDropdownMenu.style.display = 'none';
                          statusSelectBtn.classList.remove('active');
                      }
                  });

                  // Handle status selection
                  statusDropdownItems.forEach(item => {
                      item.addEventListener('click', function(e) {
                          e.preventDefault();
                          e.stopPropagation();
                          console.log('Status item clicked:', this.getAttribute('data-status'));

                          const selectedStatus = this.getAttribute('data-status');

                          // Update hidden input
                          statusSelect.value = selectedStatus;

                          // Update button text
                          const statusLabels = {
                              '': 'Semua Status',
                              'belum_dikirim': 'Belum Dikirim',
                              'menunggu_approval': 'Menunggu Approve Team Verifikasi',
                              'terkirim': 'Terkirim'
                          };
                          statusSelectText.textContent = statusLabels[selectedStatus] || 'Semua Status';

                          // Update active state
                          statusDropdownItems.forEach(i => i.classList.remove('active'));
                          this.classList.add('active');

                          // Close dropdown
                          statusDropdownMenu.style.display = 'none';
                          statusSelectBtn.classList.remove('active');

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

                          // Preserve year filter
                          const yearValue = yearSelect ? yearSelect.value : '';
                          if (yearValue) {
                              url.searchParams.set('year', yearValue);
                          } else {
                              url.searchParams.delete('year');
                          }

                          // Set status filter
                          if (selectedStatus) {
                              url.searchParams.set('status_filter', selectedStatus);
                          } else {
                              url.searchParams.delete('status_filter');
                          }

                          // Preserve column customization params
                          const columnInputs = form.querySelectorAll('input[name="columns[]"]');
                          columnInputs.forEach(input => {
                              url.searchParams.append('columns[]', input.value);
                          });

                          // Preserve per_page
                          const perPage = new URLSearchParams(window.location.search).get('per_page');
                          if (perPage) {
                              url.searchParams.set('per_page', perPage);
                          }

                          // Redirect to new URL
                          window.location.href = url.toString();
                      });
                  });

                  // Close dropdown when clicking outside
                  document.addEventListener('click', function(e) {
                      if (!statusSelectBtn.contains(e.target) && !statusDropdownMenu.contains(e.target)) {
                          statusDropdownMenu.style.display = 'none';
                          statusSelectBtn.classList.remove('active');
                      }
                  });

                  // Prevent dropdown from closing when clicking inside
                  statusDropdownMenu.addEventListener('click', function(e) {
                      e.stopPropagation();
                  });
              }

              // Handle filter form submit to preserve all parameters
              function handleFilterSubmit(event) {
                  event.preventDefault();
                  const form = document.getElementById('filterForm');
                  const searchInput = form.querySelector('input[name="search"]');
                  const yearInput = form.querySelector('input[name="year"]');
                  const statusInput = form.querySelector('input[name="status_filter"]');

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

                  // Add status_filter parameter
                  if (statusInput && statusInput.value) {
                      url.searchParams.set('status_filter', statusInput.value);
                  } else {
                      url.searchParams.delete('status_filter');
                  }

                  // Preserve per_page parameter
                  const perPage = new URLSearchParams(window.location.search).get('per_page');
                  if (perPage) {
                      url.searchParams.set('per_page', perPage);
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

                // Ctrl+F → fokus ke kolom pencarian
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                  const searchInput = document.getElementById('searchInput');
                  if (!searchInput) return;

                  // Cek apakah tidak ada modal yang terbuka
                  const anyModalOpen = document.querySelector('.modal.show');
                  if (anyModalOpen) return;

                  e.preventDefault(); // Cegah browser search bawaan

                  // Scroll ke atas dengan smooth
                  searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });

                  // Fokus & select semua teks agar langsung bisa mengetik
                  setTimeout(() => {
                    searchInput.focus();
                    searchInput.select();

                    // Efek highlight singkat
                    searchInput.style.transition = 'box-shadow 0.2s ease, border-color 0.2s ease';
                    searchInput.style.boxShadow = '0 0 0 3px rgba(136, 151, 23, 0.4)';
                    searchInput.style.borderColor = '#889717';
                    setTimeout(() => {
                      searchInput.style.boxShadow = '';
                      searchInput.style.borderColor = '';
                    }, 1500);
                  }, 100);
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

          // Open View Document Modal
          function openViewDocumentModal(docId) {
            // Set document ID
            document.getElementById('view-dokumen-id').value = docId;

            // Set edit button URL
            document.getElementById('view-edit-btn').href = `/documents/${docId}/edit`;

            // Load document data via AJAX
            fetch(`/documents/${docId}/detail`, {
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
              .then(response => {
                if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                console.log('Document data received:', data);
                if (data.success && data.dokumen) {
                  const dok = data.dokumen;

                  // Identitas Dokumen
                  document.getElementById('view-nomor-agenda').textContent = dok.nomor_agenda || '-';
                  document.getElementById('view-nomor-spp').textContent = dok.nomor_spp || '-';
                  document.getElementById('view-tanggal-spp').textContent = dok.tanggal_spp ? formatDate(dok.tanggal_spp) : '-';
                  // Helper function to convert month number to Indonesian month name
                  const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                  const formatBulan = (bulan) => {
                    if (!bulan) return '-';
                    const num = parseInt(bulan);
                    return (num >= 1 && num <= 12) ? monthNames[num] : bulan;
                  };
                  document.getElementById('view-bulan').textContent = formatBulan(dok.bulan);
                  document.getElementById('view-tahun').textContent = dok.tahun || '-';
                  document.getElementById('view-tanggal-masuk').textContent = dok.tanggal_masuk ? formatDateTime(dok.tanggal_masuk) : '-';
                  document.getElementById('view-jenis-dokumen').textContent = dok.jenis_dokumen || '-';
                  document.getElementById('view-jenis-sub-pekerjaan').textContent = dok.jenis_sub_pekerjaan || '-';
                  document.getElementById('view-kategori').textContent = dok.kategori || '-';
                  document.getElementById('view-jenis-pembayaran').textContent = dok.jenis_pembayaran || '-';

                  // Detail Keuangan & Vendor
                  document.getElementById('view-uraian-spp').textContent = dok.uraian_spp || '-';
                  document.getElementById('view-nilai-rupiah').textContent = dok.nilai_rupiah ? 'Rp. ' + formatNumber(dok.nilai_rupiah) : '-';
                  // Ejaan nilai rupiah
                  if (dok.nilai_rupiah && dok.nilai_rupiah > 0) {
                    document.getElementById('view-ejaan-nilai-rupiah').textContent = terbilangRupiah(dok.nilai_rupiah);
                  } else {
                    document.getElementById('view-ejaan-nilai-rupiah').textContent = '-';
                  }
                  document.getElementById('view-dibayar-kepada').textContent = dok.dibayar_kepada || '-';
                  document.getElementById('view-kebun').textContent = dok.kebun || '-';
                  document.getElementById('view-bagian').textContent = dok.bagian || '-';
                  document.getElementById('view-nama-pengirim').textContent = dok.nama_pengirim || '-';

                  // Referensi Pendukung
                  document.getElementById('view-no-spk').textContent = dok.no_spk || '-';
                  document.getElementById('view-tanggal-spk').textContent = dok.tanggal_spk ? formatDate(dok.tanggal_spk) : '-';
                  document.getElementById('view-tanggal-berakhir-spk').textContent = dok.tanggal_berakhir_spk ? formatDate(dok.tanggal_berakhir_spk) : '-';
                  document.getElementById('view-nomor-miro').textContent = dok.NO_MIRO_SES || '-';
                  document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
                  document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDate(dok.tanggal_berita_acara) : '-';

                  // Informasi Akutansi section removed
                  // document.getElementById('view-nomor-miro-akutansi').textContent = dok.nomor_miro || '-';
                  // document.getElementById('view-tanggal-miro').textContent = dok.tanggal_miro ? formatDate(dok.tanggal_miro) : '-';

                  // Nomor PO & PR
                  const poList = dok.NO_PO || (dok.dokumen_pos && dok.dokumen_pos.length > 0 
                    ? dok.dokumen_pos.map(po => po.nomor_po).join(', ')
                    : '-');
                  const prList = dok.dokumen_prs && dok.dokumen_prs.length > 0
                    ? dok.dokumen_prs.map(pr => pr.nomor_pr).join(', ')
                    : '-';
                  document.getElementById('view-nomor-po').textContent = poList;
                  document.getElementById('view-nomor-pr').textContent = prList;

                  // Show modal after data is loaded
                  const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                  modal.show();

                  // Ensure edit button works correctly - prevent any interference
                  const editBtn = document.getElementById('view-edit-btn');
                  if (editBtn) {
                    // Remove any existing event listeners by cloning and replacing
                    const newEditBtn = editBtn.cloneNode(true);
                    editBtn.parentNode.replaceChild(newEditBtn, editBtn);

                    // Add click handler to ensure navigation works
                    newEditBtn.addEventListener('click', function(e) {
                      const href = this.getAttribute('href');
                      if (href && href !== '#' && !href.startsWith('#')) {
                        // Valid URL, allow navigation
                        window.location.href = href;
                      }
                    });
                  }
                } else {
                  console.error('Invalid response format:', data);
                  alert('Gagal memuat data dokumen: ' + (data.message || 'Format respons tidak valid'));
                }
              })
              .catch(error => {
                console.error('Error loading document:', error);
                alert('Gagal memuat data dokumen: ' + error.message);
              });
          }

          // Helper functions for formatting
          function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
          }

          function formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
          }

          function formatNumber(num) {
            if (!num) return '-';
            return new Intl.NumberFormat('id-ID').format(num);
          }

          // Function to convert number to Indonesian terbilang
          function terbilangRupiah(number) {
            number = parseFloat(number) || 0;

            if (number == 0) {
              return 'nol rupiah';
            }

            const angka = [
              '', 'satu', 'dua', 'tiga', 'empat', 'lima',
              'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
              'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
              'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
            ];

            let hasil = '';

            // Handle triliun
            if (number >= 1000000000000) {
              const triliun = Math.floor(number / 1000000000000);
              hasil += terbilangSatuan(triliun, angka) + ' triliun ';
              number = number % 1000000000000;
            }

            // Handle milyar
            if (number >= 1000000000) {
              const milyar = Math.floor(number / 1000000000);
              hasil += terbilangSatuan(milyar, angka) + ' milyar ';
              number = number % 1000000000;
            }

            // Handle juta
            if (number >= 1000000) {
              const juta = Math.floor(number / 1000000);
              hasil += terbilangSatuan(juta, angka) + ' juta ';
              number = number % 1000000;
            }

            // Handle ribu
            if (number >= 1000) {
              const ribu = Math.floor(number / 1000);
              if (ribu == 1) {
                hasil += 'seribu ';
              } else {
                hasil += terbilangSatuan(ribu, angka) + ' ribu ';
              }
              number = number % 1000;
            }

            // Handle ratusan, puluhan, dan satuan
            if (number > 0) {
              hasil += terbilangSatuan(number, angka);
            }

            return hasil.trim() + ' rupiah';
          }

          function terbilangSatuan(number, angka) {
            let hasil = '';
            number = parseInt(number);

            if (number == 0) {
              return '';
            }

            // Handle ratusan
            if (number >= 100) {
              const ratus = Math.floor(number / 100);
              if (ratus == 1) {
                hasil += 'seratus ';
              } else {
                hasil += angka[ratus] + ' ratus ';
              }
              number = number % 100;
            }

            // Handle puluhan dan satuan (0-99)
            if (number > 0) {
              if (number < 20) {
                hasil += angka[number] + ' ';
              } else {
                const puluhan = Math.floor(number / 10);
                const satuan = number % 10;

                if (puluhan == 1) {
                  hasil += angka[10 + satuan] + ' ';
                } else {
                  hasil += angka[puluhan] + ' puluh ';
                  if (satuan > 0) {
                    hasil += angka[satuan] + ' ';
                  }
                }
              }
            }

            return hasil.trim();
          }
          </script>

          <!-- Modal View Document Detail -->
          <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width: 90%; width: 90%;">
              <div class="modal-content" style="height: 90vh; display: flex; flex-direction: column;">
                <!-- Sticky Header -->
                <div class="modal-header" style="position: sticky; top: 0; z-index: 1050; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-bottom: none; flex-shrink: 0;">
                  <h5 class="modal-title" id="viewDocumentModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
                    <i class="fa-solid fa-file-lines me-2"></i>
                    Detail Dokumen Lengkap
                  </h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Scrollable Body -->
                <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 140px); padding: 24px; flex: 1;">
                  <input type="hidden" id="view-dokumen-id">

                  <!-- Section 1: Identitas Dokumen -->
                  <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                      <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-id-card"></i>
                        IDENTITAS DOKUMEN
                      </h6>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Nomor Agenda</label>
                          <div class="detail-value" id="view-nomor-agenda">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Nomor SPP</label>
                          <div class="detail-value" id="view-nomor-spp">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Tanggal SPP</label>
                          <div class="detail-value" id="view-tanggal-spp">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Bulan</label>
                          <div class="detail-value" id="view-bulan">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Tahun</label>
                          <div class="detail-value" id="view-tahun">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Tanggal Masuk</label>
                          <div class="detail-value" id="view-tanggal-masuk">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Kriteria CF</label>
                          <div class="detail-value" id="view-kategori">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Sub Kriteria</label>
                          <div class="detail-value" id="view-jenis-dokumen">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Item Sub Kriteria</label>
                          <div class="detail-value" id="view-jenis-sub-pekerjaan">-</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="detail-item">
                          <label class="detail-label">Jenis Pembayaran</label>
                          <div class="detail-value" id="view-jenis-pembayaran">-</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Section 2: Detail Keuangan & Vendor -->
                  <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                      <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        DETAIL KEUANGAN & VENDOR
                      </h6>
                    </div>
                    <div class="row g-3">
                      <div class="col-12">
                        <div class="detail-item">
                          <label class="detail-label">Uraian SPP</label>
                          <div class="detail-value" id="view-uraian-spp" style="white-space: pre-wrap;">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Nilai Rupiah</label>
                          <div class="detail-value" id="view-nilai-rupiah" style="font-weight: 700; color: #083E40;">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Ejaan Nilai Rupiah</label>
                          <div class="detail-value" id="view-ejaan-nilai-rupiah" style="font-style: italic; color: #666;">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Dibayar Kepada (Vendor)</label>
                          <div class="detail-value" id="view-dibayar-kepada">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Kebun / Unit Kerja</label>
                          <div class="detail-value" id="view-kebun">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Bagian</label>
                          <div class="detail-value" id="view-bagian">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Nama Pengirim</label>
                          <div class="detail-value" id="view-nama-pengirim">-</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Section 3: Referensi Pendukung -->
                  <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                      <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-file-contract"></i>
                        REFERENSI PENDUKUNG
                      </h6>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-3">
                        <div class="detail-item">
                          <label class="detail-label">No. SPK</label>
                          <div class="detail-value" id="view-no-spk">-</div>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="detail-item">
                          <label class="detail-label">Tanggal SPK</label>
                          <div class="detail-value" id="view-tanggal-spk">-</div>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="detail-item">
                          <label class="detail-label">Tanggal Berakhir SPK</label>
                          <div class="detail-value" id="view-tanggal-berakhir-spk">-</div>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="detail-item">
                          <label class="detail-label">Nomor Miro/SES</label>
                          <div class="detail-value" id="view-nomor-miro">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">No. Berita Acara</label>
                          <div class="detail-value" id="view-no-berita-acara">-</div>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="detail-item">
                          <label class="detail-label">Tanggal Berita Acara</label>
                          <div class="detail-value" id="view-tanggal-berita-acara">-</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Section 4: Nomor PO & PR -->
                  <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                      <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-hashtag"></i>
                        NOMOR PO & PR
                      </h6>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Nomor PO</label>
                          <div class="detail-value" id="view-nomor-po">-</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="detail-item">
                          <label class="detail-label">Nomor PR</label>
                          <div class="detail-value" id="view-nomor-pr">-</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Section 5: Informasi Akutansi (REMOVED) -->
                </div>

                <!-- Sticky Footer -->
                <div class="modal-footer" style="position: sticky; bottom: 0; z-index: 1050; background: white; border-top: 1px solid #dee2e6; padding: 16px 24px; flex-shrink: 0;">
                  <button type="button" class="btn btn-danger" onclick="confirmDeleteDocument()">
                    <i class="fa-solid fa-trash me-2"></i>Hapus
                  </button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-2"></i>Tutup
                  </button>
                  <a href="#" id="view-edit-btn" class="btn btn-primary" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
                    <i class="fa-solid fa-pen me-2"></i>Edit Dokumen
                  </a>
                </div>

              </div>
            </div>
          </div>

          <style>
          /* Detail Item Styles for View Modal */
          .detail-item {
            margin-bottom: 8px;
          }

          .detail-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
          }

          .detail-value {
            font-size: 14px;
            color: #1f2937;
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            min-height: 38px;
            display: flex;
            align-items: center;
          }
          </style>

          <!-- Delete Confirmation Modal -->
          <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; padding: 20px 24px;">
                  <h5 class="modal-title" id="deleteConfirmModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Konfirmasi Hapus Dokumen
                  </h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 24px;">
                  <div style="text-align: center; margin-bottom: 20px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                      <i class="fa-solid fa-trash-can" style="font-size: 32px; color: #dc3545;"></i>
                    </div>
                    <h5 style="color: #1f2937; font-weight: 600; margin-bottom: 8px;">Apakah Anda yakin ingin menghapus dokumen ini?</h5>
                    <p style="color: #6b7280; font-size: 14px; margin-bottom: 0;">Dokumen yang dihapus tidak dapat dikembalikan.</p>
                  </div>

                  <!-- Document Info -->
                  <div style="background: #f8f9fa; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: grid; gap: 12px;">
                      <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Nomor Agenda</span>
                        <span id="delete-nomor-agenda" style="color: #1f2937; font-weight: 600; font-size: 14px;">-</span>
                      </div>
                      <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Nomor SPP</span>
                        <span id="delete-nomor-spp" style="color: #1f2937; font-weight: 600; font-size: 14px;">-</span>
                      </div>
                      <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Nilai</span>
                        <span id="delete-nilai" style="color: #28a745; font-weight: 700; font-size: 14px;">-</span>
                      </div>
                    </div>
                  </div>

                  <!-- Warning Alert -->
                  <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border: 1px solid #ffc107; border-radius: 10px; padding: 12px 16px; display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fa-solid fa-exclamation-circle" style="color: #856404; font-size: 18px; margin-top: 2px;"></i>
                    <div>
                      <strong style="color: #856404; font-size: 13px; display: block; margin-bottom: 4px;">Perhatian!</strong>
                      <span style="color: #856404; font-size: 12px;">Semua data dokumen termasuk nomor PO, PR, dan data terkait lainnya akan dihapus secara permanen.</span>
                    </div>
                  </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px; background: #f8f9fa;">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 24px; border-radius: 8px; font-weight: 600;">
                    <i class="fa-solid fa-times me-2"></i>Batal
                  </button>
                  <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="executeDeleteDocument()" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none;">
                    <i class="fa-solid fa-trash me-2"></i>Ya, Hapus Dokumen
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Success Toast Notification -->
          <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
            <div id="deleteSuccessToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px;">
              <div class="d-flex">
                <div class="toast-body" style="padding: 16px 20px; font-size: 14px;">
                  <i class="fa-solid fa-check-circle me-2"></i>
                  <strong>Berhasil!</strong> Dokumen telah dihapus.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>
          </div>

          <!-- Hidden Delete Form -->
          <form id="deleteDocumentForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
          </form>

          <script>
          // Variable to store document id for deletion
          let documentIdToDelete = null;

          // Show delete confirmation modal
          function confirmDeleteDocument() {
            const dokumenId = document.getElementById('view-dokumen-id').value;
            const nomorAgenda = document.getElementById('view-nomor-agenda').textContent;
            const nomorSpp = document.getElementById('view-nomor-spp').textContent;
            const nilaiRupiah = document.getElementById('view-nilai-rupiah') ? document.getElementById('view-nilai-rupiah').textContent : '-';

            if (!dokumenId) {
              alert('ID Dokumen tidak ditemukan');
              return;
            }

            // Store document id
            documentIdToDelete = dokumenId;

            // Populate modal with document info
            document.getElementById('delete-nomor-agenda').textContent = nomorAgenda || '-';
            document.getElementById('delete-nomor-spp').textContent = nomorSpp || '-';
            document.getElementById('delete-nilai').textContent = nilaiRupiah || '-';

            // Reset the confirm button state
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.innerHTML = '<i class="fa-solid fa-trash me-2"></i>Ya, Hapus Dokumen';
            confirmBtn.disabled = false;

            // Show the delete confirmation modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
          }

          // Execute the actual delete
          function executeDeleteDocument() {
            if (!documentIdToDelete) {
              alert('ID Dokumen tidak ditemukan');
              return;
            }

            // Show loading state
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menghapus...';
            confirmBtn.disabled = true;

            // Set form action and submit
            const form = document.getElementById('deleteDocumentForm');
            form.action = `/documents/${documentIdToDelete}`;
            form.submit();
          }

          // Show success toast if redirected with success message
          document.addEventListener('DOMContentLoaded', function() {
            @if(session('success') && str_contains(session('success'), 'hapus'))
              // Close any open modals first
              const openModals = document.querySelectorAll('.modal.show');
              openModals.forEach(modal => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
              });

              // Show success toast
              setTimeout(() => {
                const toast = new bootstrap.Toast(document.getElementById('deleteSuccessToast'), {
                  autohide: true,
                  delay: 5000
                });
                toast.show();
              }, 500);
            @endif
          });
          </script>

          <!-- ===== MULTI-SELECT BULK SEND COMPONENTS ===== -->

          <!-- Floating Action Bar -->
          <div class="bulk-action-bar" id="bulkActionBar">
            <div class="selected-count">
              <i class="fa-solid fa-check-circle"></i>
              <span><span class="count-badge" id="selectedCount">0</span> dokumen dipilih</span>
            </div>
            <div class="action-buttons">
              <button type="button" class="btn-cancel-selection" onclick="clearAllSelections()">
                <i class="fa-solid fa-times"></i>
                Batal
              </button>
              <button type="button" class="btn-bulk-send" id="btnBulkSend" onclick="showBulkSendConfirmation()">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim ke Team Verifikasi</span>
              </button>
            </div>
          </div>

          <!-- Bulk Send Confirmation Modal -->
          <div class="modal fade bulk-send-modal" id="bulkSendConfirmModal" tabindex="-1" aria-labelledby="bulkSendConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="bulkSendConfirmModalLabel">
                    <i class="fa-solid fa-paper-plane"></i>
                    Konfirmasi Kirim Dokumen
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="summary-info">
                    <div class="summary-row">
                      <span class="summary-label">Jumlah Dokumen:</span>
                      <span class="summary-value" id="summaryDocCount">0 dokumen</span>
                    </div>
                    <div class="summary-row">
                      <span class="summary-label">Total Nilai:</span>
                      <span class="summary-value" id="summaryTotalValue">Rp 0</span>
                    </div>
                  </div>

                  <p style="color: #495057; margin-bottom: 12px;">
                    <i class="fa-solid fa-info-circle text-muted me-2"></i>
                    Dokumen berikut akan dikirim ke <strong>Team Verifikasi</strong> untuk diproses:
                  </p>

                  <ul class="document-list" id="bulkSendDocumentList">
                    <!-- Document list items will be populated by JavaScript -->
                  </ul>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 20px; border-radius: 10px; font-weight: 600;">
                    <i class="fa-solid fa-times me-2"></i>Batal
                  </button>
                  <button type="button" class="btn btn-success" id="confirmBulkSendBtn" onclick="executeBulkSend()" style="padding: 10px 20px; border-radius: 10px; font-weight: 600; background: linear-gradient(135deg, #889717 0%, #6b7814 100%); border: none;">
                    <i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim Semua
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading Overlay -->
          <div class="bulk-send-loading" id="bulkSendLoading">
            <div class="loading-spinner"></div>
            <div class="loading-text">Mengirim dokumen...</div>
            <div class="loading-progress" id="loadingProgress">Memproses...</div>
          </div>

          <!-- Bulk Send JavaScript -->
          <script>
            // Store selected documents
            let selectedDocuments = new Map();

            // Initialize bulk select functionality
            document.addEventListener('DOMContentLoaded', function() {
              initBulkSelect();
            });

            function initBulkSelect() {
              // Get all document checkboxes
              const docCheckboxes = document.querySelectorAll('.doc-checkbox');
              const selectAllCheckbox = document.getElementById('selectAllCheckbox');

              // Handle individual checkbox changes
              docCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                  const docId = this.dataset.id;
                  const row = this.closest('tr');

                  if (this.checked) {
                    selectedDocuments.set(docId, {
                      id: docId,
                      nomor_agenda: this.dataset.nomorAgenda,
                      nomor_spp: this.dataset.nomorSpp,
                      nilai_rupiah: this.dataset.nilaiRupiah
                    });
                    row.classList.add('selected-for-bulk');
                  } else {
                    selectedDocuments.delete(docId);
                    row.classList.remove('selected-for-bulk');
                  }

                  updateBulkActionBar();
                  updateSelectAllCheckbox();
                });
              });

              // Handle Select All checkbox
              if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                  const isChecked = this.checked;

                  docCheckboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                      checkbox.checked = isChecked;
                      const row = checkbox.closest('tr');
                      const docId = checkbox.dataset.id;

                      if (isChecked) {
                        selectedDocuments.set(docId, {
                          id: docId,
                          nomor_agenda: checkbox.dataset.nomorAgenda,
                          nomor_spp: checkbox.dataset.nomorSpp,
                          nilai_rupiah: checkbox.dataset.nilaiRupiah
                        });
                        row.classList.add('selected-for-bulk');
                      } else {
                        selectedDocuments.delete(docId);
                        row.classList.remove('selected-for-bulk');
                      }
                    }
                  });

                  updateBulkActionBar();
                });
              }
            }

            function updateSelectAllCheckbox() {
              const selectAllCheckbox = document.getElementById('selectAllCheckbox');
              const allCheckboxes = document.querySelectorAll('.doc-checkbox:not(:disabled)');
              const checkedCheckboxes = document.querySelectorAll('.doc-checkbox:not(:disabled):checked');

              if (!selectAllCheckbox) return;

              if (allCheckboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
              } else if (checkedCheckboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
              } else if (checkedCheckboxes.length === allCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
              } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
              }
            }

            function updateBulkActionBar() {
              const actionBar = document.getElementById('bulkActionBar');
              const countElement = document.getElementById('selectedCount');
              const count = selectedDocuments.size;

              countElement.textContent = count;

              if (count > 0) {
                actionBar.classList.add('visible');
              } else {
                actionBar.classList.remove('visible');
              }
            }

            function clearAllSelections() {
              selectedDocuments.clear();

              document.querySelectorAll('.doc-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                const row = checkbox.closest('tr');
                if (row) {
                  row.classList.remove('selected-for-bulk');
                }
              });

              const selectAllCheckbox = document.getElementById('selectAllCheckbox');
              if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
              }

              updateBulkActionBar();
            }

            function showBulkSendConfirmation() {
              if (selectedDocuments.size === 0) {
                alert('Pilih minimal satu dokumen untuk dikirim.');
                return;
              }

              // Populate modal with selected documents
              const documentList = document.getElementById('bulkSendDocumentList');
              documentList.innerHTML = '';

              let totalValue = 0;
              let counter = 1;

              selectedDocuments.forEach((doc, id) => {
                // Parse nilai rupiah for total calculation
                const nilaiStr = doc.nilai_rupiah.replace(/[^\d]/g, '');
                const nilaiNum = parseInt(nilaiStr) || 0;
                totalValue += nilaiNum;

                const listItem = document.createElement('li');
                listItem.className = 'document-list-item';
                listItem.innerHTML = `
                  <div class="doc-icon">
                    <i class="fa-solid fa-file-lines"></i>
                  </div>
                  <div class="doc-info">
                    <div class="doc-agenda">${doc.nomor_agenda || '-'}</div>
                    <div class="doc-spp">SPP: ${doc.nomor_spp || '-'}</div>
                  </div>
                  <div class="doc-value">${doc.nilai_rupiah || '-'}</div>
                `;
                documentList.appendChild(listItem);
                counter++;
              });

              // Update summary
              document.getElementById('summaryDocCount').textContent = `${selectedDocuments.size} dokumen`;
              document.getElementById('summaryTotalValue').textContent = formatRupiah(totalValue);

              // Show modal
              const modal = new bootstrap.Modal(document.getElementById('bulkSendConfirmModal'));
              modal.show();
            }

            function formatRupiah(number) {
              return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function executeBulkSend() {
              if (selectedDocuments.size === 0) {
                alert('Tidak ada dokumen yang dipilih.');
                return;
              }

              // Disable button and show loading
              const confirmBtn = document.getElementById('confirmBulkSendBtn');
              confirmBtn.disabled = true;
              confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

              // Close modal and show loading overlay
              const modal = bootstrap.Modal.getInstance(document.getElementById('bulkSendConfirmModal'));
              modal.hide();

              // Show loading overlay
              const loadingOverlay = document.getElementById('bulkSendLoading');
              loadingOverlay.classList.add('visible');
              document.getElementById('loadingProgress').textContent = `Mengirim ${selectedDocuments.size} dokumen...`;

              // Prepare data
              const documentIds = Array.from(selectedDocuments.keys()).map(id => parseInt(id));

              // Send request
              fetch('{{ route("documents.bulk-send-to-verifikasi") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  document_ids: documentIds
                })
              })
              .then(response => response.json())
              .then(data => {
                // Hide loading overlay
                loadingOverlay.classList.remove('visible');

                if (data.success) {
                  // Show success message
                  showBulkSendResult(true, data);

                  // Clear selections
                  clearAllSelections();

                  // Reload page after delay to show updated data
                  setTimeout(() => {
                    window.location.reload();
                  }, 2000);
                } else {
                  // Show error message
                  showBulkSendResult(false, data);

                  // Reset button
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim Semua';
                }
              })
              .catch(error => {
                console.error('Bulk send error:', error);
                loadingOverlay.classList.remove('visible');

                alert('Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.');

                // Reset button
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim Semua';
              });
            }

            function showBulkSendResult(success, data) {
              // Create toast notification
              const toastContainer = document.createElement('div');
              toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
              toastContainer.style.zIndex = '11000';

              let message = '';
              let bgClass = '';

              if (success) {
                bgClass = 'bg-success';
                message = `<i class="fa-solid fa-check-circle me-2"></i>
                  <strong>Berhasil!</strong> ${data.success_count || selectedDocuments.size} dokumen telah dikirim ke Team Verifikasi.`;

                if (data.failed_count && data.failed_count > 0) {
                  message += `<br><small>${data.failed_count} dokumen gagal dikirim.</small>`;
                }
              } else {
                bgClass = 'bg-danger';
                message = `<i class="fa-solid fa-exclamation-circle me-2"></i>
                  <strong>Gagal!</strong> ${data.message || 'Terjadi kesalahan saat mengirim dokumen.'}`;
              }

              toastContainer.innerHTML = `
                <div class="toast align-items-center text-white ${bgClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px;">
                  <div class="d-flex">
                    <div class="toast-body" style="padding: 16px 20px; font-size: 14px;">
                      ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                  </div>
                </div>
              `;

              document.body.appendChild(toastContainer);

              // Auto-remove after 5 seconds
              setTimeout(() => {
                toastContainer.remove();
              }, 5000);
            }
          </script>

          <script>
            // AJAX Refresh Document Table
            function refreshDocumentTable() {
              const btn = document.getElementById('btnRefreshTable');
              const container = document.getElementById('documentTableContainer');
              if (!btn || !container) return;
              btn.classList.add('loading');
              btn.disabled = true;
              fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
              })
              .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
              })
              .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.getElementById('documentTableContainer');
                if (newTable) {
                  container.innerHTML = newTable.innerHTML;
                  showRefreshToast('success', 'Data berhasil diperbarui!');
                } else {
                  showRefreshToast('error', 'Gagal memperbarui data.');
                }
              })
              .catch(error => {
                console.error('Refresh error:', error);
                showRefreshToast('error', 'Gagal memperbarui data. Coba lagi.');
              })
              .finally(() => {
                btn.classList.remove('loading');
                btn.disabled = false;
              });
            }
            function showRefreshToast(type, message) {
              document.querySelectorAll('.refresh-toast').forEach(t => t.remove());
              const toast = document.createElement('div');
              toast.className = `refresh-toast ${type}`;
              toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
              document.body.appendChild(toast);
              setTimeout(() => { if (toast.parentNode) toast.remove(); }, 3000);
            }

            // Fix for Nilai Rupiah column vertical line - Force remove borders
            function fixNilaiRupiahColumnBorders() {
              // Target all possible selectors for Nilai Rupiah column
              const selectors = [
                'td.col-nilai',
                'th.col-nilai',
                '.table-enhanced td.col-nilai',
                '.table-dokumen td.col-nilai',
                '.table tbody td.col-nilai',
                '[class*="col-nilai"]'
              ];

              selectors.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                  element.style.borderRight = 'none';
                  element.style.borderLeft = 'none';
                  element.style.borderImage = 'none';
                  element.style.boxShadow = 'none';
                });
              });
            }

            // Apply fix when DOM is loaded
            document.addEventListener('DOMContentLoaded', fixNilaiRupiahColumnBorders);
            
            // Apply fix after any AJAX calls or table updates
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
              return originalFetch.apply(this, args).then(response => {
                setTimeout(fixNilaiRupiahColumnBorders, 100);
                return response;
              });
            };

            // Apply fix periodically to catch any dynamic changes
            setInterval(fixNilaiRupiahColumnBorders, 1000);

            // Additional CSS injection for maximum override
            const style = document.createElement('style');
            style.innerHTML = `
              /* MAXIMUM PRIORITY OVERRIDE FOR NILAI RUPIAH COLUMN */
              .table-dokumen thead th.col-nilai,
              .table-dokumen tbody td.col-nilai,
              .table-enhanced thead th.col-nilai,
              .table-enhanced tbody td.col-nilai,
              .preview-table thead th.col-nilai,
              .preview-table tbody td.col-nilai,
              table thead th.col-nilai,
              table tbody td.col-nilai,
              td.col-nilai, th.col-nilai {
                border-right: none !important;
                border-left: none !important;
                border-image: none !important;
                box-shadow: none !important;
                outline: none !important;
                background-clip: padding-box !important;
              }

              /* Override any inline styles */
              [style].col-nilai,
              td.col-nilai[style*="border"],
              th.col-nilai[style*="border"] {
                border-right: none !important;
                border-left: none !important;
              }

              /* ---- Inline edit: Textarea besar untuk Uraian SPP ---- */
              .ie-cell {
                position: relative;
              }

              /* Textarea uraian muncul sebagai overlay floating */
              .ie-textarea-large {
                position: absolute;
                z-index: 1000;
                top: 0;
                left: 0;
                min-width: 420px;
                max-width: 600px;
                width: max-content;
                min-height: 140px;
                resize: both;
                background: #fff;
                border: 2px solid #083E40;
                border-radius: 8px;
                box-shadow: 0 8px 24px rgba(8, 62, 64, 0.25);
                padding: 10px 12px;
                font-size: 13.5px;
                line-height: 1.6;
                color: #1a1a2e;
                font-family: inherit;
              }

              .ie-textarea-large:focus {
                outline: none;
                border-color: #889717;
                box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.2), 0 8px 24px rgba(8, 62, 64, 0.2);
              }

              /* Saat cell sedang diedit dengan textarea besar, beri overflow visible */
              .ie-cell.ie-editing:has(.ie-textarea-large) {
                overflow: visible;
              }
            `;
            document.head.appendChild(style);
          </script>

          <script>
            // =========================================================
            // INLINE EDITING ENGINE - Spreadsheet Style
            // =========================================================
            (function () {
              'use strict';

              // ---- Dropdown data passed from Laravel ----
              const IE_KATEGORI   = @json($ieKategoriList ?? []);
              const IE_SUB        = @json($ieSubKriteriaList ?? []);
              const IE_ITEM       = @json($ieItemSubKriteriaList ?? []);
              const IE_JENIS_BAYAR= @json($ieJenisPembayaranList ?? []);

              const BULAN_LIST = ['Januari','Februari','Maret','April','May','Juni','July','Agustus','September','Oktober','November','Desember'];

              // ---- Field type map ----
              const FIELD_TYPE = {
                nomor_agenda        : 'text',
                nomor_spp           : 'text',
                uraian_spp          : 'textarea',
                nilai_rupiah        : 'number',
                tanggal_spp         : 'date',
                tanggal_berita_acara: 'date',
                tanggal_spk         : 'date',
                tanggal_berakhir_spk: 'date',
                tanggal_faktur      : 'date',
                tanggal_paraf       : 'date',
                kebun               : 'text',
                bagian              : 'text',
                nama_pengirim       : 'text',
                no_berita_acara     : 'text',
                no_spk              : 'text',
                nomor_miro          : 'text',
                no_faktur           : 'text',
                pemaraf             : 'text',
                dibayar_kepada      : 'textarea',
                kategori            : 'select_kategori',
                jenis_dokumen       : 'select_sub',
                jenis_sub_pekerjaan : 'select_item',
                jenis_pembayaran    : 'select_jenis',
                bulan               : 'select_bulan',
                tahun               : 'text',
              };

              let activeCell = null;
              let activeInput = null;

              // ---- Build select options ----
              function buildSelect(field, currentVal) {
                const sel = document.createElement('select');
                sel.className = 'ie-input';

                let options = [];
                if (field === 'select_kategori') {
                  options = IE_KATEGORI.map(k => ({ value: k.nama_kriteria, label: k.nama_kriteria }));
                } else if (field === 'select_sub') {
                  options = IE_SUB.map(k => ({ value: k.nama_sub_kriteria, label: k.nama_sub_kriteria }));
                } else if (field === 'select_item') {
                  options = IE_ITEM.map(k => ({ value: k.nama_item_sub_kriteria, label: k.nama_item_sub_kriteria }));
                } else if (field === 'select_jenis') {
                  options = IE_JENIS_BAYAR.map(k => ({ value: k.nama_jenis_pembayaran, label: k.nama_jenis_pembayaran }));
                } else if (field === 'select_bulan') {
                  options = BULAN_LIST.map(b => ({ value: b, label: b }));
                }

                const emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.textContent = '-- Pilih --';
                sel.appendChild(emptyOpt);

                options.forEach(opt => {
                  const o = document.createElement('option');
                  o.value = opt.value;
                  o.textContent = opt.label;
                  if (opt.value === currentVal) o.selected = true;
                  sel.appendChild(o);
                });
                return sel;
              }

              // ---- Create input for a given field type ----
              function createInput(fieldType, rawValue, fieldName) {
                let el;
                if (fieldType === 'textarea') {
                  el = document.createElement('textarea');
                  el.className = 'ie-input ie-textarea';
                  el.value = rawValue ?? '';
                  // Uraian SPP butuh ruang lebih besar
                  el.rows = (fieldName === 'uraian_spp') ? 6 : 3;
                  if (fieldName === 'uraian_spp') {
                    el.classList.add('ie-textarea-large');
                  }
                } else if (fieldType.startsWith('select_')) {
                  el = buildSelect(fieldType, rawValue ?? '');
                } else if (fieldType === 'date') {
                  el = document.createElement('input');
                  el.type = 'date';
                  el.className = 'ie-input';
                  el.value = rawValue ?? '';
                } else if (fieldType === 'number') {
                  el = document.createElement('input');
                  el.type = 'text';
                  el.className = 'ie-input';
                  // Parse as float then round to integer before stripping non-digits.
                  // This prevents the decimal:2 cast ("4832149.00") from becoming
                  // "483214900" when dots are stripped — the two trailing zeros must go first.
                  const num = rawValue
                    ? String(Math.round(parseFloat(String(rawValue).replace(/[^0-9.]/g, '')) || 0))
                    : '';
                  el.value = num;
                  el.placeholder = '0';
                } else {
                  el = document.createElement('input');
                  el.type = 'text';
                  el.className = 'ie-input';
                  el.value = rawValue ?? '';
                }
                return el;
              }

              // ---- Activate a cell ----
              function activateCell(cell) {
                if (activeCell && activeCell !== cell) {
                  commitCell(activeCell);
                }
                if (activeCell === cell) return;

                const field = cell.dataset.field;
                if (!field) return;

                const fieldType = FIELD_TYPE[field] || 'text';
                const rawValue = cell.dataset.raw ?? '';

                // Save original HTML for cancel
                cell.dataset.originalHtml = cell.innerHTML;
                cell.dataset.originalRaw  = rawValue;

                activeCell  = cell;
                activeInput = createInput(fieldType, rawValue, field);

                cell.classList.add('ie-editing');
                cell.innerHTML = '';
                cell.appendChild(activeInput);

                // Focus after short delay for select
                setTimeout(() => {
                  activeInput.focus();
                  if (activeInput.tagName === 'INPUT' && activeInput.type === 'text') {
                    activeInput.select();
                  }
                }, 20);

                // ---- Event listeners ----
                activeInput.addEventListener('keydown', onKeyDown);

                if (activeInput.tagName === 'SELECT') {
                  activeInput.addEventListener('change', () => commitCell(cell));
                } else {
                  activeInput.addEventListener('blur', (e) => {
                    // Small delay to allow Tab navigation to set activeCell
                    setTimeout(() => {
                      if (activeCell === cell) commitCell(cell);
                    }, 80);
                  });
                }
              }

              // ---- Commit (save) a cell ----
              function commitCell(cell) {
                if (!cell || !activeInput) return;
                const field    = cell.dataset.field;
                const newValue = activeInput.value;
                const oldRaw   = cell.dataset.originalRaw ?? '';

                // No change — just cancel.
                // For number fields, normalize oldRaw by rounding to integer
                // so that "4832149.00" (decimal:2 cast) matches "4832149" from the input.
                let compareOld = oldRaw;
                if (FIELD_TYPE[field] === 'number') {
                  compareOld = oldRaw
                    ? String(Math.round(parseFloat(String(oldRaw).replace(/[^0-9.]/g, '')) || 0))
                    : '';
                }
                if (newValue === compareOld) {
                  cancelCell(cell);
                  return;
                }

                const dokumenId = cell.closest('tr').dataset.dokumenId;
                if (!dokumenId) { cancelCell(cell); return; }

                // Show saving state
                cell.classList.remove('ie-editing');
                cell.classList.add('ie-saving');
                cell.innerHTML = (cell.dataset.originalHtml || newValue) + '<span class="ie-spinner"></span>';

                activeCell  = null;
                activeInput = null;

                fetch(`/documents/${dokumenId}/inline-update`, {
                  method: 'PATCH',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                  },
                  body: JSON.stringify({ field, value: newValue }),
                })
                .then(r => r.json())
                .then(data => {
                  cell.classList.remove('ie-saving');
                  if (data.success) {
                    const display = data.display_value ?? newValue ?? '-';
                    cell.dataset.raw = data.raw_value ?? newValue;
                    // Rebuild display HTML
                    cell.innerHTML = buildDisplayHtml(field, display, cell.dataset.raw);
                    cell.classList.add('ie-saved');
                    setTimeout(() => cell.classList.remove('ie-saved'), 700);
                    // Update row-level data attributes for nomor_agenda
                    if (field === 'nomor_agenda') {
                      cell.closest('tr').dataset.nomorAgenda = newValue;
                    }
                  } else {
                    cell.innerHTML = cell.dataset.originalHtml;
                    cell.classList.add('ie-error');
                    setTimeout(() => cell.classList.remove('ie-error'), 700);
                    showIeToast('error', data.message || 'Gagal menyimpan.');
                  }
                })
                .catch(err => {
                  cell.classList.remove('ie-saving');
                  cell.innerHTML = cell.dataset.originalHtml;
                  cell.classList.add('ie-error');
                  setTimeout(() => cell.classList.remove('ie-error'), 700);
                  showIeToast('error', 'Koneksi gagal. Coba lagi.');
                });
              }

              // ---- Cancel a cell ----
              function cancelCell(cell) {
                if (!cell) return;
                cell.classList.remove('ie-editing', 'ie-saving');
                cell.innerHTML = cell.dataset.originalHtml || '';
                activeCell  = null;
                activeInput = null;
              }

              // ---- Build display HTML after save ----
              function buildDisplayHtml(field, displayValue, rawValue) {
                if (field === 'nilai_rupiah') {
                  return `<strong class="select-text">${displayValue}</strong>`;
                } else if (field === 'nomor_spp' || field === 'dibayar_kepada') {
                  return `<span class="select-text">${displayValue}</span>`;
                } else if (field === 'uraian_spp') {
                  return `<span>${displayValue}</span>`;
                }
                return displayValue || '-';
              }

              // ---- Keyboard handler ----
              function onKeyDown(e) {
                if (!activeCell) return;
                if (e.key === 'Escape') {
                  e.preventDefault();
                  cancelCell(activeCell);
                } else if (e.key === 'Enter' && activeInput && activeInput.tagName !== 'TEXTAREA') {
                  e.preventDefault();
                  commitCell(activeCell);
                } else if (e.key === 'Tab') {
                  e.preventDefault();
                  const direction = e.shiftKey ? -1 : 1;
                  const currentCell = activeCell;
                  commitCell(activeCell);
                  // Navigate to next/prev ie-cell in same row or next row
                  setTimeout(() => navigateCell(currentCell, direction), 100);
                }
              }

              // ---- Navigate to adjacent cell ----
              function navigateCell(fromCell, direction) {
                const row     = fromCell.closest('tr');
                const allCells = Array.from(document.querySelectorAll('tr[data-editable="true"] .ie-cell'));
                const idx     = allCells.indexOf(fromCell);
                if (idx === -1) return;
                const next = allCells[idx + direction];
                if (next) activateCell(next);
              }

              // ---- Toast notification ----
              function showIeToast(type, message) {
                const existing = document.getElementById('ie-toast');
                if (existing) existing.remove();
                const t = document.createElement('div');
                t.id = 'ie-toast';
                t.style.cssText = `
                  position:fixed; bottom:24px; left:50%; transform:translateX(-50%);
                  background:${type === 'error' ? '#dc3545' : '#28a745'};
                  color:#fff; padding:10px 20px; border-radius:8px;
                  font-size:13px; z-index:99999; box-shadow:0 4px 12px rgba(0,0,0,.2);
                  animation: fadeInUp .25s ease;
                `;
                t.textContent = message;
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 3500);
              }

              // ---- Attach click listeners ----
              document.addEventListener('click', function(e) {
                const cell = e.target.closest('.ie-cell');
                if (cell) {
                  e.stopPropagation();
                  activateCell(cell);
                } else if (activeCell) {
                  commitCell(activeCell);
                }
              });

              // ---- Block dblclick on ie-cell so row detail modal doesn't open ----
              document.addEventListener('dblclick', function(e) {
                const cell = e.target.closest('.ie-cell');
                if (cell) {
                  e.stopPropagation();
                  e.preventDefault();
                }
              }, true);

            })();
          </script>

          {{-- ============================================================
               AJAX "Semua" loader — no page reload, progressive loading
               ============================================================ --}}
          <script>
          (function() {
            'use strict';

            // Base URL for ajax-rows endpoint
            const AJAX_ROWS_URL = "{{ route('documents.ajax-rows') }}";
            const CSRF_TOKEN    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            let _ajaxController = null; // AbortController for cancellation

            /* ---- Override per-page selectors ---- */
            window.changePerPageTop = function(value) {
              if (value === 'all') { startAjaxLoad(); }
              else {
                const params = new URLSearchParams(window.location.search);
                params.set('per_page', value); params.set('page', '1');
                window.location.href = window.location.pathname + '?' + params.toString();
              }
            };
            window.changePerPageEnhanced = function(value) {
              if (value === 'all') { startAjaxLoad(); }
              else {
                const params = new URLSearchParams(window.location.search);
                params.set('per_page', value); params.set('page', '1');
                window.location.href = window.location.pathname + '?' + params.toString();
              }
            };

            /* ---- Cancel button ---- */
            window.cancelAjaxLoad = function() {
              if (_ajaxController) { _ajaxController.abort(); }
              hideBar();
              // Restore select to previous value
              document.querySelectorAll('.perpage-top-select, .pagination-enhanced-select')
                .forEach(s => { s.value = '{{ $dokumens->perPage() >= $dokumens->total() ? "all" : $dokumens->perPage() }}'; });
            };

            /* ---- Main loader ---- */
            async function startAjaxLoad() {
              const tbody    = document.getElementById('dokumenTableBody');
              const bar      = document.getElementById('ajaxLoadBar');
              const fill     = document.getElementById('ajaxLoadBarFill');
              const txt      = document.getElementById('ajaxLoadBarText');
              if (!tbody || !bar) return;

              // Save scroll position
              const scrollY = window.scrollY;

              // Show bar, clear table
              bar.style.display = 'block';
              fill.style.width  = '0%';
              txt.textContent   = 'Memuat data...';

              // Empty tbody but keep header spacer
              tbody.innerHTML = '<tr><td colspan="50" style="text-align:center;padding:20px;color:#6c757d;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat semua dokumen...</td></tr>';

              // Hide existing pagination while loading
              const paginationWrapper = document.querySelector('.pagination-enhanced-wrapper');
              if (paginationWrapper) paginationWrapper.style.display = 'none';

              // Build base params from current URL (preserve filters)
              const baseParams = new URLSearchParams(window.location.search);
              baseParams.delete('per_page');
              baseParams.delete('page');
              baseParams.set('chunk_size', '200');

              _ajaxController = new AbortController();
              const signal = _ajaxController.signal;

              let total   = 0;
              let loaded  = 0;
              let allHtml = '';

              try {
                // First request to get total
                const firstParams = new URLSearchParams(baseParams);
                firstParams.set('chunk_page', '1');
                const firstRes  = await fetch(AJAX_ROWS_URL + '?' + firstParams.toString(), {
                  signal,
                  headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!firstRes.ok) throw new Error('Server error: ' + firstRes.status);
                const firstData = await firstRes.json();

                total    = firstData.total;
                loaded   = firstData.to ?? 0;
                allHtml  = firstData.html;
                const lastPage = firstData.last_page;

                // Update progress
                updateBar(fill, txt, loaded, total);

                // Load remaining chunks sequentially
                for (let p = 2; p <= lastPage; p++) {
                  if (signal.aborted) return;
                  const params = new URLSearchParams(baseParams);
                  params.set('chunk_page', String(p));
                  const res  = await fetch(AJAX_ROWS_URL + '?' + params.toString(), {
                    signal,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
                  });
                  if (!res.ok) throw new Error('Server error: ' + res.status);
                  const data = await res.json();
                  allHtml += data.html;
                  loaded   = data.to ?? loaded;
                  updateBar(fill, txt, loaded, total);
                }

                // Inject all rows at once
                tbody.innerHTML = allHtml;

                // Show "viewing all" summary, hide pagination
                fill.style.width = '100%';
                txt.textContent  = 'Menampilkan semua ' + total.toLocaleString('id-ID') + ' dokumen';
                setTimeout(hideBar, 1500);

                // Update summary texts in per-page bars
                document.querySelectorAll('.pagination-enhanced-summary, .perpage-top-bar span').forEach(el => {
                  el.textContent = 'Menampilkan 1 - ' + total.toLocaleString('id-ID') + ' dari ' + total.toLocaleString('id-ID') + ' hasil';
                });

                // Mark selects as "all"
                document.querySelectorAll('.perpage-top-select, .pagination-enhanced-select').forEach(s => { s.value = 'all'; });

                // Update browser URL without reload
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('per_page', 'all');
                newUrl.searchParams.delete('page');
                history.replaceState(null, '', newUrl.toString());

                // Restore scroll
                window.scrollTo({ top: scrollY, behavior: 'instant' });

              } catch(err) {
                if (err.name === 'AbortError') return;
                console.error('AJAX load failed:', err);
                tbody.innerHTML = '<tr><td colspan="50" class="text-center py-4 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Gagal memuat data. <a href="?per_page=all">Coba reload</a></td></tr>';
                fill.style.width = '0%';
                txt.textContent  = 'Gagal memuat';
                setTimeout(hideBar, 3000);
                // Restore pagination
                if (paginationWrapper) paginationWrapper.style.display = '';
              }
            }

            function updateBar(fill, txt, loaded, total) {
              const pct = total > 0 ? Math.min(100, Math.round(loaded / total * 100)) : 0;
              fill.style.width = pct + '%';
              txt.textContent  = 'Memuat ' + loaded.toLocaleString('id-ID') + ' / ' + total.toLocaleString('id-ID') + ' dokumen (' + pct + '%)';
            }

            function hideBar() {
              const bar = document.getElementById('ajaxLoadBar');
              if (bar) bar.style.display = 'none';
            }

            // Auto-trigger if current URL already has per_page=all
            document.addEventListener('DOMContentLoaded', function() {
              const currentPerPage = new URLSearchParams(window.location.search).get('per_page');
              @if($dokumens->perPage() >= $dokumens->total() && $dokumens->total() > 0)
              // Page was loaded server-side with all rows already present — just mark selects
              document.querySelectorAll('.perpage-top-select, .pagination-enhanced-select').forEach(s => { s.value = 'all'; });
              @endif
            });
          })();
          </script>

@endsection









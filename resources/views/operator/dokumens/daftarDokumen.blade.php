@extends('layouts/app')
@push('styles')
  <style>
    #documentTableContainer.table-dokumen {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #f1f5f9;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
      overflow: visible;
    }

    #dokumen-tabulator {
      width: 100%;
      min-height: 520px;
      background: #ffffff;
    }

    #dokumen-tabulator .tabulator {
      border: 0;
      border-radius: 0;
      background: #ffffff;
      font-family: inherit;
      font-size: 13px;
    }

    #dokumen-tabulator .tabulator-header,
    #dokumen-tabulator .tabulator-header .tabulator-col {
      background: #143f6b;
      color: #fff;
      border-color: #245d8f;
      font-weight: 700;
    }

    #dokumen-tabulator .tabulator-header .tabulator-col:hover {
      background: #184b7d;
    }

    /* Frozen header columns with solid background */
    #dokumen-tabulator .tabulator-header .tabulator-frozen {
      background: #143f6b !important;
      z-index: 15;
    }

    #dokumen-tabulator .tabulator-header .tabulator-frozen:hover {
      background: #184b7d !important;
    }

    #dokumen-tabulator .tabulator-row {
      border-bottom: 1px solid #f1f5f9;
      min-height: 54px;
    }

    #dokumen-tabulator .tabulator-row .tabulator-cell {
      border-right: 1px solid #eef2f7;
      padding: 9px 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      white-space: normal;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    #dokumen-tabulator .tabulator-row.tabulator-row-even {
      background: #f8fafc;
    }

    #dokumen-tabulator .tabulator-row:hover {
      background: #f3faf9 !important;
      box-shadow: inset 3px 0 0 #083E40;
    }

    /* Frozen columns with solid background */
    #dokumen-tabulator .tabulator-frozen {
      background: #ffffff !important;
      z-index: 10;
    }

    #dokumen-tabulator .tabulator-row.tabulator-row-even .tabulator-frozen {
      background: #f8fafc !important;
    }

    #dokumen-tabulator .tabulator-row:hover .tabulator-frozen {
      background: #f3faf9 !important;
    }

    .tabulator-cell-text {
      width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .tabulator-cell-wrap {
      width: 100%;
      white-space: normal;
      line-height: 1.45;
    }

    .tabulator-agenda {
      display: flex;
      width: 100%;
      flex-direction: column;
      align-items: center;
      gap: 2px;
      line-height: 1.25;
    }

    .tabulator-agenda strong {
      color: #111827;
      font-weight: 800;
    }

    .tabulator-agenda small {
      color: #6b7280;
      font-size: 11px;
    }

    .tabulator-status-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      min-width: 128px;
      max-width: 100%;
      padding: 8px 13px;
      border-radius: 999px;
      color: #fff;
      font-size: 12px;
      font-weight: 800;
      line-height: 1.15;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);
    }

    .tabulator-status-badge.is-sent {
      background: linear-gradient(135deg, #064f52 0%, #07676b 100%);
    }

    .tabulator-status-badge.is-draft {
      background: linear-gradient(135deg, #5f6872 0%, #4b5563 100%);
    }

    .tabulator-status-badge.is-pending {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .tabulator-status-badge.is-returned {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      cursor: pointer;
    }

    .tabulator-status-badge.is-done {
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }

    .tabulator-status-dot {
      width: 7px;
      height: 7px;
      border-radius: 999px;
      background: rgba(255,255,255,.7);
      flex: 0 0 auto;
    }

    #dokumen-tabulator .tabulator-inline-editable-cell {
      cursor: text;
    }

    #dokumen-tabulator .tabulator-inline-editable-cell:hover {
      background: rgba(8, 62, 64, 0.06);
      box-shadow: inset 0 0 0 1px rgba(8, 62, 64, 0.18);
    }

    #dokumen-tabulator .tabulator-editing {
      background: #ffffff !important;
      box-shadow: inset 0 0 0 2px #083E40 !important;
    }

    #dokumen-tabulator .tabulator-editing input,
    #dokumen-tabulator .tabulator-editing textarea,
    #dokumen-tabulator .tabulator-editing select {
      width: 100%;
      min-height: 34px;
      border: 1px solid #889717;
      border-radius: 6px;
      padding: 7px 9px;
      color: #0f172a;
      font: inherit;
      outline: none;
      background: #fff;
    }

    .tabulator-dropdown-editor {
      width: 100%;
      min-height: 34px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 7px 32px 7px 9px;
      color: #0f172a;
      font: inherit;
      text-align: left;
      outline: none;
      background: #fff;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .tabulator-dropdown-editor:focus {
      border-color: #083E40;
      box-shadow: 0 0 0 2px rgba(8, 62, 64, 0.12);
    }

    .tabulator-dropdown-editor::after {
      content: '\f107';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      pointer-events: none;
    }

    .tabulator-dropdown-panel {
      position: fixed;
      background: #ffffff;
      border: 1px solid #64748b;
      border-radius: 4px;
      box-shadow: 0 12px 26px rgba(15, 23, 42, 0.18);
      padding: 0;
      z-index: 100000;
      overflow-y: auto;
      overscroll-behavior: contain;
    }

    .tabulator-dropdown-option {
      padding: 10px 12px;
      cursor: pointer;
      border-radius: 0;
      color: #0f172a;
      font-size: 13px;
      font-weight: 500;
      white-space: normal;
      line-height: 1.35;
    }

    .tabulator-dropdown-option:hover,
    .tabulator-dropdown-option.is-active {
      background: #2563d4;
      color: #ffffff;
      font-weight: 600;
    }

    .tabulator-dropdown-option.is-empty {
      color: #64748b;
      font-style: italic;
    }

    .tabulator-dropdown-option.is-empty.is-active,
    .tabulator-dropdown-option.is-empty:hover {
      color: #ffffff;
    }

    /* Autocomplete dropdown styling */
    .tabulator-edit-list {
      max-height: 300px !important;
      overflow-y: auto !important;
      background: #ffffff !important;
      border: 2px solid #889717 !important;
      border-radius: 8px !important;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
      padding: 4px !important;
      z-index: 10000 !important;
      margin-top: 4px !important;
    }

    .tabulator-edit-list-item {
      padding: 10px 12px !important;
      cursor: pointer !important;
      border-radius: 6px !important;
      color: #0f172a !important;
      font-size: 13px !important;
      font-weight: 500 !important;
      transition: all 0.15s ease !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }

    .tabulator-edit-list-item:hover {
      background: #f0f9ff !important;
      color: #0369a1 !important;
    }

    .tabulator-edit-list-item.active,
    .tabulator-edit-list-item.focus {
      background: #889717 !important;
      color: #ffffff !important;
      font-weight: 600 !important;
    }

    .tabulator-edit-list-group {
      padding: 8px 12px !important;
      font-weight: 700 !important;
      color: #64748b !important;
      font-size: 11px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
    }

    .tabulator-edit-list-group-item {
      padding-left: 24px !important;
    }

    .tabulator-edit-list::-webkit-scrollbar {
      width: 8px;
    }

    .tabulator-edit-list::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 4px;
    }

    .tabulator-edit-list::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }

    .tabulator-edit-list::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    #dokumen-tabulator .tabulator-cell.tabulator-acn-active {
      outline: 3px solid #083E40 !important;
      outline-offset: -2px !important;
      background-color: rgba(8, 62, 64, 0.08) !important;
      position: relative;
      z-index: 4;
    }

    #dokumen-tabulator .tabulator-cell.tabulator-acn-active::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0;
      height: 0;
      border-style: solid;
      border-width: 8px 8px 0 0;
      border-color: #083E40 transparent transparent transparent;
      pointer-events: none;
    }

    #dokumen-tabulator .tabulator-row.tabulator-acn-active-row .tabulator-cell {
      background-color: rgba(136, 151, 23, 0.04);
    }

    #dokumen-tabulator .tabulator-col.tabulator-acn-active-col {
      background: linear-gradient(135deg, #0a5f52 0%, #083E40 100%) !important;
      box-shadow: inset 0 -3px 0 #889717;
    }

    #dokumen-tabulator .tabulator-cell.tabulator-acn-pulse {
      animation: tabulator-acn-pulse 0.4s ease-out;
    }

    @keyframes tabulator-acn-pulse {
      0% { box-shadow: 0 0 0 0 rgba(8, 62, 64, 0.5); }
      70% { box-shadow: 0 0 0 6px rgba(8, 62, 64, 0); }
      100% { box-shadow: 0 0 0 0 rgba(8, 62, 64, 0); }
    }

    .tabulator-acn-indicator {
      display: none !important;
      align-items: center;
      gap: 12px;
      padding: 6px 16px;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      border-radius: 0 0 8px 8px;
      user-select: none;
    }

    .tabulator-acn-indicator.visible {
      display: none !important;
    }

    .tabulator-acn-cell-ref {
      background: rgba(255,255,255,0.15);
      border-radius: 4px;
      padding: 2px 10px;
      font-family: 'Courier New', monospace;
      letter-spacing: 0.5px;
      font-size: 13px;
    }

    .tabulator-acn-col-name {
      opacity: 0.85;
      font-size: 11px;
    }

    .tabulator-acn-hint {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-left: auto;
      opacity: 0.75;
      font-size: 11px;
    }

    .tabulator-acn-key {
      background: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 3px;
      padding: 1px 6px;
      font-family: monospace;
      font-size: 11px;
    }

    .tabulator-handler-select,
    .document-handler-select {
      width: 100%;
      min-width: 150px;
      max-width: 220px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 7px 9px;
      background: #fff;
      color: #0f172a;
      font-size: 12px;
      font-weight: 600;
      line-height: 1.2;
    }

    .tabulator-handler-select:disabled,
    .document-handler-select:disabled {
      background: #f1f5f9;
      color: #64748b;
      cursor: not-allowed;
    }

    .tabulator-handler-select.is-saving,
    .document-handler-select.is-saving {
      opacity: .7;
    }

    .tabulator-actions {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      width: 100%;
    }

    .tabulator-loading-panel {
      display: none;
      align-items: center;
      justify-content: center;
      gap: 10px;
      min-height: 180px;
      color: #64748b;
      font-weight: 700;
    }

    .tabulator-loading-panel.is-visible {
      display: flex;
    }

    body.document-table-only-fullscreen #dokumen-tabulator,
    body.is-fullscreen #dokumen-tabulator {
      min-height: calc(100vh - 28px);
    }
  </style>
@endpush
@section('content')

  <script>
    window.__operatorSharedInlineEdit = true;
  </script>

  <style>
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
    .data-table {
      border-collapse: separate;
      border-spacing: 0;
      min-width: 100%;
      width: max-content;
      table-layout: auto;
    }

    .data-table thead th {
      position: sticky;
      top: 0;
      z-index: 10;
      background: #0d3b6e;
      color: white;
      font-weight: 600;
      text-align: center;
      vertical-align: middle;
      white-space: normal;
      line-height: 1.25;
      overflow-wrap: normal;
      word-break: keep-all;
      background-clip: padding-box;
      border: 1px solid #1a5276;
      box-shadow: 0 2px 0 #1a5276;
      padding: 9px 8px;
      font-size: 11.5px;
      min-width: 132px;
      max-width: 320px;
    }


    .data-table tbody tr {
      transition: all 0.2s ease;
      border-bottom: 1px solid #f0f0f0;
    }

    .data-table tbody tr:hover {
      background: linear-gradient(135deg, rgba(136, 151, 23, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);
      transform: translateY(-1px);
    }

    /* Column width optimization for Operator */
    .data-table td {
      padding: 12px;
      text-align: center;
      vertical-align: middle;
      white-space: nowrap;
      border-right: none;
    }

    .data-table .col-no {
      width: 80px;
      min-width: 80px;
    }

    .data-table .col-nomor_agenda {
      width: 120px;
      min-width: 120px;
    }

    .data-table .col-nomor_spp,
    .data-table .col-no_spk,
    .data-table .col-no_berita_acara,
    .data-table .col-nomor_po,
    .data-table .col-nomor_miro,
    .data-table .col-no_faktur {
      width: 170px;
      min-width: 170px;
      max-width: 240px;
    }

    .data-table [class*="col-tanggal"] {
      width: 150px;
      min-width: 150px;
      max-width: 180px;
    }

    .data-table .col-nilai_rupiah,
    .data-table .col-dpp_pph,
    .data-table .col-ppn_terhutang {
      width: 120px;
      min-width: 120px;
    }

    .data-table .col-kategori,
    .data-table .col-jenis_dokumen,
    .data-table .col-jenis_sub_pekerjaan,
    .data-table .col-jenis_pembayaran,
    .data-table .col-nama_pengirim,
    .data-table .col-kepala_sub_bagian,
    .data-table .col-handler {
      width: 190px;
      min-width: 190px;
      max-width: 280px;
    }

    .data-table .col-status,
    .data-table .col-status_dokumen_custom {
      width: 250px;
      min-width: 250px;
      max-width: 280px;
      text-align: center;
      overflow: visible;
    }

    .data-table th.col-status {
      text-align: center;
    }

    .data-table td.col-status {
      text-align: center;
    }

    .data-table .col-status .badge-status {
      margin: 0 auto;
      display: inline-flex;
      white-space: normal;
    }

    .data-table .col-keterangan {
      width: 150px;
      min-width: 130px;
    }

    .data-table .col-action {
      width: 140px;
      min-width: 140px;
    }

    .data-table .col-uraian {
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

    .data-table .col-uraian span {
      display: block;
      word-wrap: break-word;
      white-space: normal;
      overflow-wrap: break-word;
      line-height: 1.6;
      width: 100%;
    }

    .data-table .col-dibayar_kepada,
    .data-table td.col-dibayar_kepada {
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
    .data-table .col-checkbox,
    .data-table .col-no {
      position: static !important;
      left: auto !important;
      z-index: auto !important;
      box-shadow: none !important;
    }

    /* Responsive design improvements */
    @media (max-width: 768px) {
      .table-dokumen {
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(8, 62, 64, 0.05);
      }

      .data-table {
        min-width: 700px;
        font-size: 12px;
      }

      .data-table th {
        padding: 12px 8px;
        font-size: 12px;
      }

      .data-table td {
        padding: 10px 8px;
        font-size: 12px;
      }

      .data-table .col-no {
        width: 60px;
        min-width: 60px;
      }

      .data-table .col-nomor_agenda {
        width: 100px;
        min-width: 100px;
      }

      .data-table .col-nomor_spp {
        width: 120px;
        min-width: 120px;
      }

      .data-table [class*="col-tanggal"] {
        width: 130px;
        min-width: 130px;
      }

      .data-table .col-nilai_rupiah {
        width: 100px;
        min-width: 100px;
      }

      .data-table .col-status {
        width: 90px;
        min-width: 90px;
      }

      .data-table .col-keterangan {
        width: 110px;
        min-width: 110px;
      }

      .data-table .col-action {
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
      .data-table {
        min-width: 600px;
      }

      .data-table th {
        padding: 10px 6px;
        font-size: 11px;
      }

      .data-table td {
        padding: 8px 6px;
        font-size: 11px;
      }

      .data-table .col-no {
        width: 50px;
        min-width: 50px;
      }

      .data-table .col-nomor_agenda {
        width: 90px;
        min-width: 90px;
      }

      .data-table .col-nomor_spp {
        width: 100px;
        min-width: 100px;
      }

      .data-table [class*="col-tanggal"] {
        width: 120px;
        min-width: 120px;
      }

      .data-table .col-nilai_rupiah {
        width: 80px;
        min-width: 80px;
      }

      .data-table .col-status {
        width: 80px;
        min-width: 80px;
      }

      .data-table .col-keterangan {
        width: 90px;
        min-width: 90px;
      }

      .data-table .col-action {
        width: 100px;
        min-width: 100px;
      }
    }

    .table-dokumen thead {
      background: #0d3b6e;
      color: white;
      position: relative;
    }

    .table-dokumen thead::after {
      content: none;
      display: none;
    }

    .table-dokumen thead th {
      padding: 9px 8px;
      font-weight: 600;
      font-size: 11.5px;
      border: 1px solid #1a5276;
      background: #0d3b6e;
      background-clip: padding-box;
      box-shadow: 0 2px 0 #1a5276;
      text-align: center;
      letter-spacing: 0;
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
    .table-container:has(.data-table) {
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
      content: 'Γ£ô';
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
        <input type="text" class="form-control" id="searchInput" name="search"
          placeholder="Cari nomor agenda, SPP, nilai rupiah, atau field lainnya..."
          value="{{ request('search') }}">
      </div>
      <div class="year-dropdown-wrapper" style="position: relative;">
        <button type="button" class="btn-year-select" id="yearSelectBtn">
        <span id="yearSelectText">{{ request('year') ? request('year') : 'Filter Tahun' }}</span>
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
        <i class="fa-solid fa-filter me-2"></i>Filter
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

  @php
    $operatorTableColumns = array_values(array_filter($selectedColumns, function ($col) use ($availableColumns) {
      return $col !== 'nomor_mirror' && isset($availableColumns[$col]);
    }));
  @endphp

  <div class="table-dokumen" id="documentTableContainer">
    <div class="table-responsive">
      <table class="data-table document-table operator-document-table">
        <thead>
          <tr>
            <th class="col-checkbox">
              <input type="checkbox" id="selectAllCheckbox" class="bulk-checkbox" title="Pilih Semua">
            </th>
            <th class="col-no">No</th>
            @foreach($operatorTableColumns as $col)
              @php
                $isSortableColumn = in_array($col, [
                  'nomor_agenda',
                  'bulan',
                  'tahun',
                  'kategori',
                  'jenis_dokumen',
                  'jenis_sub_pekerjaan',
                  'jenis_pembayaran',
                  'nomor_spp',
                  'tanggal_masuk',
                  'nilai_rupiah',
                  'tanggal_spp',
                  'status',
                ], true);
              @endphp
              <th class="col-{{ $col }}"
                @if($isSortableColumn) onclick="toggleSort('{{ $col }}')" style="cursor: pointer; user-select: none;" @endif>
                <span>{{ $availableColumns[$col] ?? ucfirst(str_replace('_', ' ', $col)) }}</span>
                @if($isSortableColumn)
                  <span class="sort-indicator">
                    @if($sortColumn === $col)
                      <i class="fa-solid fa-sort-{{ $sortOrder === 'asc' ? 'up' : 'down' }}"></i>
                    @else
                      <i class="fa-solid fa-sort"></i>
                    @endif
                  </span>
                @endif
              </th>
            @endforeach
            <th class="col-handler">Pengurus Dokumen</th>
          </tr>
        </thead>
        <tbody id="dokumenTableBody">
          @include('operator.dokumens._tableRowsAjax', [
            'dokumens' => $dokumens,
            'selectedColumns' => $operatorTableColumns,
            'availableColumns' => $availableColumns,
          ])
        </tbody>
      </table>
    </div>
  </div>
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
                                <span style="color: #28a745;">Γ£ô Terkirim</span>
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
              'status': ['Γ£ô Terkirim', 'Γ£ô Terkirim', 'Γ£ô Terkirim', 'Γ£ô Terkirim', 'Γ£ô Terkirim'],
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
                          yearSelectText.textContent = selectedYear || 'Filter Tahun';

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

                // Ctrl+F ΓåÆ fokus ke kolom pencarian
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
                  document.dispatchEvent(new CustomEvent('document-table-refreshed'));
                  window.dispatchEvent(new CustomEvent('document-table-refreshed'));
                  if (typeof window.syncDocumentStickyOffsets === 'function') {
                    window.syncDocumentStickyOffsets();
                  }
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

            // Additional CSS injection for inline-edit popup styles.
            const style = document.createElement('style');
            style.innerHTML = `
              /* =====================================================
                 CSS OVERLAY POPUP (uraian_spp → modal popup)
                 ===================================================== */
              .ie-overlay-backdrop {
                position: fixed; inset: 0; z-index: 9998;
                background: rgba(0,0,0,0.15);
                animation: ie-backdrop-in .12s ease;
              }
              @keyframes ie-backdrop-in { from { opacity: 0; } to { opacity: 1; } }
              .ie-overlay-popup {
                position: fixed; z-index: 9999;
                width: 480px; max-width: calc(100vw - 32px);
                background: #fff;
                border: 2px solid #083E40;
                border-radius: 10px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.10);
                padding: 12px 14px 10px;
                animation: ie-popup-in .15s cubic-bezier(.2,.8,.3,1);
              }
              @keyframes ie-popup-in {
                from { opacity: 0; transform: translateY(-6px) scale(.98); }
                to   { opacity: 1; transform: translateY(0)    scale(1); }
              }
              .ie-overlay-header {
                display: flex; align-items: center; justify-content: space-between;
                margin-bottom: 8px;
              }
              .ie-overlay-label {
                font-size: 11px; font-weight: 600; color: #083E40;
                text-transform: uppercase; letter-spacing: .5px;
              }
              .ie-overlay-close {
                background: none; border: none; cursor: pointer; padding: 2px 6px;
                font-size: 16px; color: #6b7280; line-height: 1; border-radius: 4px;
              }
              .ie-overlay-close:hover { background: #f3f4f6; color: #111; }
              .ie-overlay-textarea {
                width: 100%; box-sizing: border-box;
                min-height: 160px; max-height: 340px;
                border: 1px solid #d1d5db; border-radius: 6px;
                padding: 8px 10px; font-size: 13px; font-family: inherit;
                background: #f8fff8; color: #111;
                resize: vertical; line-height: 1.6; outline: none;
                transition: border-color .15s;
              }
              .ie-overlay-textarea:focus { border-color: #083E40; box-shadow: 0 0 0 3px rgba(8,62,64,.12); }
              .ie-overlay-footer {
                display: flex; align-items: center; justify-content: space-between;
                margin-top: 8px; gap: 8px;
              }
              .ie-overlay-charcount { font-size: 11px; color: #9ca3af; }
              .ie-overlay-actions { display: flex; gap: 6px; }
              .ie-overlay-btn {
                padding: 5px 16px; border-radius: 6px; border: none; cursor: pointer;
                font-size: 12px; font-weight: 600; transition: all .15s;
              }
              .ie-overlay-btn-save { background: #083E40; color: #fff; }
              .ie-overlay-btn-save:hover { background: #0a5254; }
              .ie-overlay-btn-cancel { background: #f3f4f6; color: #374151; }
              .ie-overlay-btn-cancel:hover { background: #e5e7eb; }
              /* Saat overlay terbuka, cell cukup highlight tanpa overflow */
              .ie-cell.ie-editing:has(.ie-overlay-textarea) { overflow: visible; }

              /* =====================================================
                 SCROLL PERFORMANCE ΓÇö mirip virtual scrolling
                 =====================================================
                 content-visibility: auto ΓåÆ browser SKIP paint/layout
                 untuk baris yang tidak terlihat di layar.
                 Ini setara dengan virtual scrolling tapi cukup CSS.
                 Efek: 2000 baris ΓåÆ render seperti ~20-30 baris aktif.
              */
              #dokumenTableBody tr.main-row {
                content-visibility: auto;
                contain-intrinsic-size: auto 64px; /* estimasi tinggi 1 baris */
              }

              /* GPU composite layer untuk scroll container */
              .table-container {
                will-change: scroll-position;
              }

              /* Isolasi layout per-baris agar reflow tidak menyebar */
              #dokumenTableBody tr {
                contain: layout style;
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
              if (window.__operatorSharedInlineEdit) return;

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
                dibayar_kepada      : 'text',
                kategori            : 'select_kategori',
                jenis_dokumen       : 'select_sub',
                jenis_sub_pekerjaan : 'select_item',
                jenis_pembayaran    : 'select_jenis',
                bulan               : 'select_bulan',
                tahun               : 'text',
              };

              let activeCell = null;
              let activeInput = null;
              let activeOverlay = null; // backdrop + popup untuk uraian_spp

              // ---- Long-text fields → modal overlay (bukan floating textarea) ----
              const OVERLAY_FIELDS = ['uraian_spp'];
              const OVERLAY_LABELS = { uraian_spp: 'Edit Uraian SPP' };

              // ---- Overlay functions (modal popup untuk field panjang) ----
              function openOverlay(cell, field, rawValue) {
                closeOverlay(false);
                const backdrop = document.createElement('div');
                backdrop.className = 'ie-overlay-backdrop';
                const popup = document.createElement('div');
                popup.className = 'ie-overlay-popup';
                const rect = cell.getBoundingClientRect();
                let top = rect.bottom + 6;
                const popupW = Math.min(480, window.innerWidth - 32);
                let left = rect.left;
                if (left + popupW > window.innerWidth - 16) left = window.innerWidth - popupW - 16;
                if (left < 8) left = 8;
                if (top + 300 > window.innerHeight) top = Math.max(8, rect.top - 310);
                popup.style.top  = top  + 'px';
                popup.style.left = left + 'px';
                popup.style.width = popupW + 'px';
                // Header
                const header = document.createElement('div');
                header.className = 'ie-overlay-header';
                const label = document.createElement('span');
                label.className = 'ie-overlay-label';
                label.textContent = OVERLAY_LABELS[field] || 'Edit';
                const closeBtn = document.createElement('button');
                closeBtn.className = 'ie-overlay-close'; closeBtn.type = 'button';
                closeBtn.textContent = '✕'; closeBtn.title = 'Tutup (Esc)';
                header.appendChild(label); header.appendChild(closeBtn);
                // Textarea
                const ta = document.createElement('textarea');
                ta.className = 'ie-overlay-textarea';
                ta.value = rawValue ?? ''; ta.rows = 7;
                ta.placeholder = 'Ketik uraian di sini…';
                // Footer
                const footer = document.createElement('div');
                footer.className = 'ie-overlay-footer';
                const charCount = document.createElement('span');
                charCount.className = 'ie-overlay-charcount';
                charCount.textContent = ta.value.length + ' karakter';
                ta.addEventListener('input', () => { charCount.textContent = ta.value.length + ' karakter'; });
                const actions = document.createElement('div');
                actions.className = 'ie-overlay-actions';
                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'ie-overlay-btn ie-overlay-btn-cancel';
                cancelBtn.type = 'button'; cancelBtn.textContent = 'Batal';
                const saveBtn = document.createElement('button');
                saveBtn.className = 'ie-overlay-btn ie-overlay-btn-save';
                saveBtn.type = 'button'; saveBtn.textContent = '💾 Simpan';
                actions.appendChild(cancelBtn); actions.appendChild(saveBtn);
                footer.appendChild(charCount); footer.appendChild(actions);
                popup.appendChild(header); popup.appendChild(ta); popup.appendChild(footer);
                document.body.appendChild(backdrop); document.body.appendChild(popup);
                activeOverlay = { backdrop, popup, ta, cell, field };
                activeInput = ta;
                cell.classList.add('ie-editing');
                cell.dataset.originalRaw  = rawValue ?? '';
                cell.dataset.originalHtml = cell.innerHTML;
                setTimeout(() => { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }, 20);
                const doSave = () => { const v = ta.value; closeOverlay(false); commitOverlayValue(cell, field, v, rawValue ?? ''); };
                const doCancel = () => closeOverlay(true);
                saveBtn.addEventListener('click', doSave);
                cancelBtn.addEventListener('click', doCancel);
                closeBtn.addEventListener('click', doCancel);
                backdrop.addEventListener('click', doSave);
                ta.addEventListener('keydown', e => {
                  if (e.key === 'Escape') { e.preventDefault(); doCancel(); }
                  if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); doSave(); }
                });
              }

              function closeOverlay(restoreHtml) {
                if (!activeOverlay) return;
                const { backdrop, popup, cell } = activeOverlay;
                cell.classList.remove('ie-editing');
                if (restoreHtml) cell.innerHTML = cell.dataset.originalHtml || '';
                backdrop.remove(); popup.remove();
                activeOverlay = null; activeCell = null; activeInput = null;
              }

              function commitOverlayValue(cell, field, newValue, oldRaw) {
                if (newValue === oldRaw) return;
                const dokumenId = cell.closest('tr').dataset.dokumenId;
                if (!dokumenId) return;
                cell.classList.add('ie-saving');
                cell.innerHTML = (cell.dataset.originalHtml || newValue) + '<span class="ie-spinner"></span>';
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
                    cell.innerHTML = buildDisplayHtml(field, display, cell.dataset.raw);
                    cell.classList.add('ie-saved');
                    setTimeout(() => cell.classList.remove('ie-saved'), 700);
                  } else {
                    cell.innerHTML = cell.dataset.originalHtml;
                    cell.classList.add('ie-error');
                    setTimeout(() => cell.classList.remove('ie-error'), 700);
                    showIeToast('error', data.message || 'Gagal menyimpan.');
                  }
                })
                .catch(() => {
                  cell.classList.remove('ie-saving');
                  cell.innerHTML = cell.dataset.originalHtml;
                  cell.classList.add('ie-error');
                  setTimeout(() => cell.classList.remove('ie-error'), 700);
                  showIeToast('error', 'Koneksi gagal. Coba lagi.');
                });
              }

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
                  // uraian_spp ditangani oleh overlay — fallback ke textarea biasa untuk field lain
                  el = document.createElement('textarea');
                  el.className = 'ie-input ie-textarea';
                  el.value = rawValue ?? '';
                  el.rows = 3;
                  // NOTE: ie-textarea-large dihapus — uraian_spp kini pakai openOverlay()
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
                  // "483214900" when dots are stripped ΓÇö the two trailing zeros must go first.
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
                  // Hubungkan datalist autocomplete untuk dibayar_kepada
                  if (fieldName === 'dibayar_kepada') {
                    el.setAttribute('list', 'ie-dibayar-kepada-list');
                    el.placeholder = 'Nama penerima (pisah koma)';
                  }
                }
                return el;
              }

              // ---- Activate a cell ----
              function activateCell(cell) {
                if (activeOverlay) return; // overlay sudah terbuka
                if (activeCell && activeCell !== cell) {
                  commitCell(activeCell);
                }
                if (activeCell === cell) return;

                const field = cell.dataset.field;
                if (!field) return;

                // Long-text fields → modal overlay popup
                if (OVERLAY_FIELDS.includes(field)) {
                  activeCell = cell;
                  openOverlay(cell, field, cell.dataset.raw ?? '');
                  return;
                }

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

                // No change ΓÇö just cancel.
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
                  // Gunakan style yang sama persis dengan render Blade asli agar lebar kolom tidak berubah
                  const safeText = String(displayValue ?? '-').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                  return `<span title="${safeText}" style="display: block; word-wrap: break-word; white-space: normal; overflow-wrap: break-word; line-height: 1.5; width: 100%;">${displayValue || '-'}</span>`;
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

          @if(false)
          {{-- Datalist autocomplete untuk field "Dibayar Kepada" --}}
          <datalist id="ie-dibayar-kepada-list"></datalist>
          <script>
            (function() {
              const list = document.getElementById('ie-dibayar-kepada-list');
              if (!list) return;

              // 1) Pre-populate dari teks yang sudah ada di kolom tabel (DOM scraping)
              const seen = new Set();
              document.querySelectorAll('td.col-dibayar_kepada').forEach(function(td) {
                const txt = td.textContent.trim();
                if (txt && txt !== '-') {
                  // Bisa berisi beberapa penerima dipisah koma
                  txt.split(',').forEach(function(name) {
                    const n = name.trim();
                    if (n && !seen.has(n)) {
                      seen.add(n);
                      const opt = document.createElement('option');
                      opt.value = n;
                      list.appendChild(opt);
                    }
                  });
                }
              });

              // 2) Live fetch saat user mengetik di input ie-dibayar-kepada
              let fetchTimer = null;
              document.addEventListener('input', function(e) {
                if (!e.target || e.target.getAttribute('list') !== 'ie-dibayar-kepada-list') return;
                clearTimeout(fetchTimer);
                const q = e.target.value.trim();
                if (q.length < 2) return;
                fetchTimer = setTimeout(function() {
                  fetch('/api/autocomplete/payment-recipients?q=' + encodeURIComponent(q), {
                    headers: {
                      'Accept': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                  })
                  .then(function(r) { return r.ok ? r.json() : []; })
                  .then(function(data) {
                    const items = Array.isArray(data) ? data : [];
                    items.forEach(function(name) {
                      if (typeof name === 'string' && !seen.has(name)) {
                        seen.add(name);
                        const opt = document.createElement('option');
                        opt.value = name;
                        list.appendChild(opt);
                      }
                    });
                  })
                  .catch(function() {});
                }, 250);
              });
            })();
          </script>
          @endif


          @if(false)
          @php
            $tabulatorInitialColumns = array_values(array_filter($selectedColumns, function ($col) use ($availableColumns) {
              return $col !== 'nomor_mirror' && isset($availableColumns[$col]);
            }));
          @endphp
          <script>
            let tabelDokumen = null;
            let allDataCache = [];
            let tabulatorHandlerOptions = { base: [], bagian: [] };
            let tabulatorLoadPromise = null;

            const tabulatorDataUrl = @json(route('documents.all-data'));
            const tabulatorCsrfToken = @json(csrf_token());
            const initialAvailableColumns = @json($availableColumns);
            const initialSelectedColumns = @json($tabulatorInitialColumns);
            const tabulatorKategoriOptions = @json($ieKategoriList ?? []);
            const tabulatorSubKriteriaOptions = @json($ieSubKriteriaList ?? []);
            const tabulatorItemSubKriteriaOptions = @json($ieItemSubKriteriaList ?? []);
            const tabulatorJenisPembayaranOptions = @json($ieJenisPembayaranList ?? []);
            const tabulatorBulanOptions = ['Januari','Februari','Maret','April','May','Juni','July','Agustus','September','Oktober','November','Desember'];
            const tabulatorDateFields = new Set([
              'tanggal_spp',
              'tanggal_berita_acara',
              'tanggal_spk',
              'tanggal_berakhir_spk',
              'tanggal_faktur',
              'tanggal_paraf',
              'tanggal_miro',
              'tanggal_selesai_verifikasi_pajak',
            ]);
            const tabulatorNumericFields = new Set(['nilai_rupiah', 'dpp_pph', 'ppn_terhutang']);
            const tabulatorInlineFieldTypes = {
              nomor_agenda: 'text',
              nomor_spp: 'text',
              uraian_spp: 'textarea',
              nilai_rupiah: 'number',
              tanggal_spp: 'date',
              tanggal_berita_acara: 'date',
              tanggal_spk: 'date',
              tanggal_berakhir_spk: 'date',
              tanggal_faktur: 'date',
              tanggal_paraf: 'date',
              tanggal_miro: 'date',
              tanggal_selesai_verifikasi_pajak: 'date',
              kebun: 'text',
              bagian: 'text',
              nama_pengirim: 'text',
              no_berita_acara: 'text',
              no_spk: 'text',
              nomor_miro: 'text',
              no_faktur: 'text',
              pemaraf: 'text',
              dibayar_kepada: 'text',
              kategori: 'select_kategori',
              jenis_dokumen: 'select_sub',
              jenis_sub_pekerjaan: 'select_item',
              jenis_pembayaran: 'select_jenis',
              bulan: 'select_bulan',
              tahun: 'text',
              jenis_pph: 'text',
              dpp_pph: 'number',
              ppn_terhutang: 'number',
            };
            const tabulatorNonEditableFields = new Set([
              'tanggal_masuk',
              'status',
              'status_dokumen_custom',
              'tanggal_selesai_diproses',
              'tanggal_kembali_ke_bagian',
              'tanggal_hasil_koreksi_bagian',
              'tanggal_dibayar',
              'nomor_po',
              'keterangan',
              'npwp',
              'link_dokumen_pajak',
            ]);
            const tabulatorScriptSources = [
              'https://cdn.jsdelivr.net/npm/tabulator-tables@6.3.0/dist/js/tabulator.min.js',
              'https://unpkg.com/tabulator-tables@6.3.0/dist/js/tabulator.min.js',
            ];

            function normalizeTabulatorGlobal() {
              if (!window.Tabulator && window.TabulatorFull) {
                window.Tabulator = window.TabulatorFull;
              }
              return window.Tabulator;
            }

            function loadTabulatorScript(src) {
              return new Promise((resolve, reject) => {
                const existing = document.querySelector(`script[data-tabulator-src="${src}"]`);
                if (existing) {
                  existing.addEventListener('load', () => resolve(), { once: true });
                  existing.addEventListener('error', () => reject(new Error(`Gagal memuat ${src}`)), { once: true });
                  return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.defer = true;
                script.dataset.tabulatorSrc = src;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error(`Gagal memuat ${src}`));
                document.head.appendChild(script);
              });
            }

            async function ensureTabulatorLoaded() {
              if (normalizeTabulatorGlobal()) return window.Tabulator;
              if (tabulatorLoadPromise) return tabulatorLoadPromise;

              tabulatorLoadPromise = (async () => {
                for (const src of tabulatorScriptSources) {
                  try {
                    await loadTabulatorScript(src);
                    if (normalizeTabulatorGlobal()) return window.Tabulator;
                  } catch (error) {
                    console.warn(error.message);
                  }
                }
                throw new Error('Tabulator.js belum termuat. Periksa akses browser ke CDN jsDelivr/unpkg.');
              })();

              return tabulatorLoadPromise;
            }

            function escapeHtml(value) {
              return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
            }

            function tabulatorHeight() {
              return Math.max(520, window.innerHeight - 255);
            }

            function plainCellFormatter(cell) {
              const value = cell.getValue() ?? '-';
              return `<span class="tabulator-cell-text" title="${escapeHtml(value)}">${escapeHtml(value)}</span>`;
            }

            function wrappedCellFormatter(cell) {
              const value = cell.getValue() ?? '-';
              return `<span class="tabulator-cell-wrap" title="${escapeHtml(value)}">${escapeHtml(value)}</span>`;
            }

            function agendaFormatter(cell) {
              const data = cell.getRow().getData();
              return `
                <span class="tabulator-agenda">
                  <strong>${escapeHtml(data.nomor_agenda || '-')}</strong>
                  <small>${escapeHtml([data.bulan, data.tahun].filter(Boolean).join(' ') || '')}</small>
                </span>
              `;
            }

            function moneyFormatter(cell) {
              const data = cell.getRow().getData();
              return `<strong class="tabulator-cell-text">${escapeHtml(data.nilai_rupiah_fmt || '-')}</strong>`;
            }

            function compactNumberFormatter(cell) {
              const field = cell.getField();
              const data = cell.getRow().getData();
              const value = data[`${field}_fmt`] ?? cell.getValue();
              if (value === null || value === undefined || value === '') return '-';
              if (typeof value === 'number') return escapeHtml(value.toLocaleString('id-ID'));
              return escapeHtml(value);
            }

            function formatDateForDisplay(value) {
              if (!value || value === '-') return '-';
              const raw = String(value).trim();
              if (/^\d{4}-\d{2}-\d{2}/.test(raw)) {
                const [year, month, day] = raw.slice(0, 10).split('-');
                return `${day}-${month}-${year}`;
              }
              return raw;
            }

            function dateCellFormatter(cell) {
              return `<span class="tabulator-cell-text">${escapeHtml(formatDateForDisplay(cell.getValue()))}</span>`;
            }

            function statusFormatter(cell) {
              const data = cell.getRow().getData();
              const badge = data.status_badge || 'sent';
              const icon = badge === 'sent' ? 'fa-check'
                : badge === 'draft' ? 'fa-file-lines'
                : badge === 'pending' ? 'fa-clock'
                : badge === 'returned' ? 'fa-rotate-left'
                : 'fa-circle-check';
              return `
                <span class="tabulator-status-badge is-${escapeHtml(badge)}" data-status-action="${badge === 'returned' ? 'rejection' : ''}">
                  <i class="fa-solid ${icon}"></i>
                  <span>${escapeHtml(data.status || '-')}</span>
                  <span class="tabulator-status-dot"></span>
                </span>
              `;
            }

            function linkFormatter(cell) {
              const value = cell.getValue();
              if (!value) return '-';
              return `<a href="${escapeHtml(value)}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()"><i class="fa-solid fa-link fa-sm"></i> Link Pajak</a>`;
            }

            function handlerFormatter(cell) {
              const data = cell.getRow().getData();
              const selected = data.handler_selected || 'operator';
              const disabled = data.handler_disabled ? 'disabled' : '';
              const title = escapeHtml(data.handler_title || '');
              const baseOptions = (tabulatorHandlerOptions.base || []).map(option =>
                `<option value="${escapeHtml(option.value)}" ${selected === option.value ? 'selected' : ''}>${escapeHtml(option.label)}</option>`
              ).join('');
              const bagianOptions = data.handler_show_bagian
                ? `<optgroup label="Bagian">${(tabulatorHandlerOptions.bagian || []).map(option =>
                    `<option value="${escapeHtml(option.value)}" ${selected === option.value ? 'selected' : ''}>${escapeHtml(option.label)}</option>`
                  ).join('')}</optgroup>`
                : '';

              return `
                <select class="document-handler-select tabulator-handler-select"
                  data-document-id="${escapeHtml(data.id)}"
                  data-original-value="${escapeHtml(selected)}"
                  title="${title}"
                  onclick="event.stopPropagation()"
                  ${disabled}>
                  ${baseOptions}${bagianOptions}
                </select>
              `;
            }

            function tabulatorColumnWidth(field) {
              const widths = {
                nomor_agenda: 170,
                nomor_spp: 220,
                tanggal_masuk: 170,
                tanggal_spp: 145,
                nilai_rupiah: 170,
                status: 230,
                kategori: 260,
                jenis_dokumen: 220,
                jenis_sub_pekerjaan: 230,
                jenis_pembayaran: 210,
                dibayar_kepada: 240,
                uraian_spp: 340,
                nama_pengirim: 220,
                no_spk: 180,
                no_berita_acara: 230,
                link_dokumen_pajak: 160,
              };
              return widths[field] || 150;
            }

            function selectValuesFrom(items, nameKey) {
              const values = {};
              (items || []).forEach(item => {
                const value = item?.[nameKey];
                if (value) values[value] = value;
              });
              return values;
            }

            function tabulatorEditorValues(field) {
              if (field === 'kategori') return selectValuesFrom(tabulatorKategoriOptions, 'nama_kriteria');
              if (field === 'jenis_dokumen') return selectValuesFrom(tabulatorSubKriteriaOptions, 'nama_sub_kriteria');
              if (field === 'jenis_sub_pekerjaan') return selectValuesFrom(tabulatorItemSubKriteriaOptions, 'nama_item_sub_kriteria');
              if (field === 'jenis_pembayaran') return selectValuesFrom(tabulatorJenisPembayaranOptions, 'nama_jenis_pembayaran');
              if (field === 'bulan') {
                return tabulatorBulanOptions.reduce((values, bulan) => {
                  values[bulan] = bulan;
                  return values;
                }, {});
              }
              return {};
            }

            function normalizeDropdownEditorOptions(values) {
              const options = [{ value: null, label: '(Kosong)', empty: true }];
              const seen = new Set(['__null__']);

              function addOption(value, label) {
                const normalizedValue = value === undefined ? '' : value;
                const key = normalizedValue === null ? '__null__' : String(normalizedValue);
                if (seen.has(key)) return;
                seen.add(key);
                options.push({
                  value: normalizedValue,
                  label: String(label ?? normalizedValue ?? ''),
                  empty: normalizedValue === null || normalizedValue === '',
                });
              }

              if (Array.isArray(values)) {
                values.forEach(item => {
                  if (item && typeof item === 'object') {
                    addOption(item.value ?? item.label ?? '', item.label ?? item.value ?? '');
                  } else {
                    addOption(item, item);
                  }
                });
              } else {
                Object.entries(values || {}).forEach(([value, label]) => addOption(value, label));
              }

              return options;
            }

            function dropdownValuesMatch(a, b) {
              return String(a ?? '') === String(b ?? '');
            }

            function createDropdownEditor(field) {
              return function(cell, onRendered, success, cancel) {
                const options = normalizeDropdownEditorOptions(tabulatorEditorValues(field));
                const initialValue = cell.getValue();
                let activeIndex = Math.max(0, options.findIndex(option => dropdownValuesMatch(option.value, initialValue)));
                let closed = false;
                let typeahead = '';
                let typeaheadTimer = null;

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'tabulator-dropdown-editor';
                button.setAttribute('aria-haspopup', 'listbox');
                button.setAttribute('aria-expanded', 'true');

                const panel = document.createElement('div');
                panel.className = 'tabulator-dropdown-panel';
                panel.setAttribute('role', 'listbox');

                function activeOption() {
                  return options[activeIndex] || options[0] || { value: null, label: '' };
                }

                function updateButtonLabel() {
                  button.textContent = activeOption().label || '(Kosong)';
                }

                function positionPanel() {
                  const cellElement = cell.getElement();
                  if (!cellElement || !document.body.contains(cellElement) || !panel.parentElement) return;

                  const rect = cellElement.getBoundingClientRect();
                  const margin = 8;
                  const width = Math.max(rect.width, 220);
                  const maxHeight = Math.min(300, Math.max(160, window.innerHeight - margin * 2));
                  const below = window.innerHeight - rect.bottom - margin;
                  const above = rect.top - margin;
                  const openAbove = below < 180 && above > below;
                  const height = Math.min(maxHeight, Math.max(160, openAbove ? above : below));
                  const left = Math.max(margin, Math.min(rect.left, window.innerWidth - width - margin));
                  const top = openAbove
                    ? Math.max(margin, rect.top - height - 4)
                    : Math.min(rect.bottom + 4, window.innerHeight - height - margin);

                  panel.style.left = `${left}px`;
                  panel.style.top = `${top}px`;
                  panel.style.width = `${width}px`;
                  panel.style.maxHeight = `${height}px`;
                }

                function renderOptions() {
                  panel.innerHTML = '';
                  options.forEach((option, index) => {
                    const item = document.createElement('div');
                    item.className = [
                      'tabulator-dropdown-option',
                      index === activeIndex ? 'is-active' : '',
                      option.empty ? 'is-empty' : '',
                    ].filter(Boolean).join(' ');
                    item.setAttribute('role', 'option');
                    item.setAttribute('aria-selected', index === activeIndex ? 'true' : 'false');
                    item.textContent = option.label || '(Kosong)';
                    item.addEventListener('mouseenter', () => {
                      activeIndex = index;
                      renderOptions();
                    });
                    item.addEventListener('mousedown', event => event.preventDefault());
                    item.addEventListener('click', event => {
                      event.preventDefault();
                      event.stopPropagation();
                      activeIndex = index;
                      finish(success, activeOption().value);
                    });
                    panel.appendChild(item);
                  });

                  const activeItem = panel.children[activeIndex];
                  if (activeItem) activeItem.scrollIntoView({ block: 'nearest' });
                  updateButtonLabel();
                }

                function moveActive(delta) {
                  if (!options.length) return;
                  activeIndex = (activeIndex + delta + options.length) % options.length;
                  renderOptions();
                }

                function searchOption(key) {
                  clearTimeout(typeaheadTimer);
                  typeahead += key.toLowerCase();
                  typeaheadTimer = setTimeout(() => { typeahead = ''; }, 700);

                  const foundIndex = options.findIndex(option =>
                    String(option.label || '').toLowerCase().includes(typeahead)
                  );
                  if (foundIndex >= 0) {
                    activeIndex = foundIndex;
                    renderOptions();
                  }
                }

                function cleanup() {
                  clearTimeout(typeaheadTimer);
                  window.removeEventListener('resize', positionPanel);
                  window.removeEventListener('scroll', positionPanel, true);
                  document.removeEventListener('mousedown', handleOutsideMouseDown, true);
                  panel.remove();
                }

                function finish(action, value) {
                  if (closed) return;
                  closed = true;
                  cleanup();
                  action(value);
                }

                function handleOutsideMouseDown(event) {
                  if (button.contains(event.target) || panel.contains(event.target)) return;
                  finish(cancel);
                }

                button.addEventListener('keydown', event => {
                  if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    event.stopPropagation();
                    moveActive(1);
                  } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    event.stopPropagation();
                    moveActive(-1);
                  } else if (event.key === 'Home') {
                    event.preventDefault();
                    activeIndex = 0;
                    renderOptions();
                  } else if (event.key === 'End') {
                    event.preventDefault();
                    activeIndex = Math.max(0, options.length - 1);
                    renderOptions();
                  } else if (event.key === 'Enter') {
                    event.preventDefault();
                    event.stopPropagation();
                    finish(success, activeOption().value);
                  } else if (event.key === 'Escape') {
                    event.preventDefault();
                    event.stopPropagation();
                    finish(cancel);
                  } else if (event.key === 'Tab') {
                    event.preventDefault();
                    const shift = event.shiftKey;
                    finish(success, activeOption().value);
                    setTimeout(() => moveTabulatorActiveCell(0, shift ? -1 : 1), 0);
                  } else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                    event.preventDefault();
                    searchOption(event.key);
                  }
                });

                button.addEventListener('click', event => {
                  event.preventDefault();
                  button.focus();
                });

                onRendered(function() {
                  document.body.appendChild(panel);
                  renderOptions();
                  positionPanel();
                  button.focus({ preventScroll: true });
                  window.addEventListener('resize', positionPanel);
                  window.addEventListener('scroll', positionPanel, true);
                  document.addEventListener('mousedown', handleOutsideMouseDown, true);
                });

                return button;
              };
            }

            function createTabulatorInputEditor(field, type) {
              return function(cell, onRendered, success, cancel) {
                const input = document.createElement(type === 'textarea' ? 'textarea' : 'input');
                const currentValue = normalizeInlineEditValue(field, cell.getValue());

                input.className = 'tabulator-inline-input-editor';
                input.value = currentValue ?? '';

                if (type === 'date') {
                  input.type = 'date';
                } else if (type === 'number') {
                  input.type = 'text';
                  input.inputMode = 'numeric';
                } else if (type === 'textarea') {
                  input.rows = 4;
                  input.maxLength = 2000;
                } else {
                  input.type = 'text';
                  input.maxLength = 255;
                }

                function finish(action, value) {
                  action(value);
                }

                input.addEventListener('keydown', event => {
                  if (event.key === 'Escape') {
                    event.preventDefault();
                    event.stopPropagation();
                    finish(cancel);
                    return;
                  }

                  if (event.key === 'Enter' && type !== 'textarea') {
                    event.preventDefault();
                    event.stopPropagation();
                    finish(success, input.value);
                    return;
                  }

                  if (event.key === 'Enter' && type === 'textarea' && (event.ctrlKey || event.metaKey)) {
                    event.preventDefault();
                    event.stopPropagation();
                    finish(success, input.value);
                    return;
                  }

                  if (event.key === 'Tab') {
                    event.preventDefault();
                    event.stopPropagation();
                    const shift = event.shiftKey;
                    finish(success, input.value);
                    setTimeout(() => moveTabulatorActiveCell(0, shift ? -1 : 1), 0);
                  }
                });

                onRendered(function() {
                  input.focus({ preventScroll: true });
                  if (type !== 'date' && typeof input.select === 'function') input.select();
                });

                return input;
              };
            }

            function isInlineEditableField(field) {
              return Object.prototype.hasOwnProperty.call(tabulatorInlineFieldTypes, field)
                && !tabulatorNonEditableFields.has(field);
            }

            function canEditTabulatorCell(cell) {
              const data = cell.getRow().getData();
              return Boolean(data.is_inline_editable) && isInlineEditableField(cell.getField());
            }

            function editorForTabulatorField(field) {
              const type = tabulatorInlineFieldTypes[field] || 'text';
              if (type === 'textarea') return createTabulatorInputEditor(field, 'textarea');
              if (type === 'number') return createTabulatorInputEditor(field, 'number');
              if (type === 'date') return createTabulatorInputEditor(field, 'date');
              if (type.startsWith('select_')) return createDropdownEditor(field);
              return createTabulatorInputEditor(field, 'text');
            }

            function editorParamsForTabulatorField(field) {
              const type = tabulatorInlineFieldTypes[field] || 'text';
              if (type === 'number') {
                return {
                  min: 0,
                  step: 1000,
                  elementAttributes: { inputmode: 'numeric' },
                };
              }
              if (type === 'date') {
                return { elementAttributes: { type: 'date' } };
              }
              if (type === 'textarea') {
                return { elementAttributes: { rows: 4, maxlength: '2000' } };
              }
              if (type.startsWith('select_')) {
                return {};
              }
              return { elementAttributes: { maxlength: '255' } };
            }

            function restoreOldCellValue(cell, oldValue) {
              if (typeof cell.restoreOldValue === 'function') {
                cell.restoreOldValue();
              } else {
                cell.setValue(oldValue, true);
              }
            }

            function flashTabulatorCell(cell, color, duration = 900) {
              const element = cell.getElement();
              if (!element) return;
              element.style.backgroundColor = color;
              setTimeout(() => {
                element.style.backgroundColor = '';
              }, duration);
            }

            function showTabulatorToast(type, message) {
              const existing = document.getElementById('tabulator-inline-toast');
              if (existing) existing.remove();

              const toast = document.createElement('div');
              toast.id = 'tabulator-inline-toast';
              toast.style.cssText = `
                position:fixed; bottom:24px; left:50%; transform:translateX(-50%);
                background:${type === 'error' ? '#dc3545' : '#16a34a'};
                color:#fff; padding:10px 20px; border-radius:8px;
                font-size:13px; font-weight:700; z-index:99999;
                box-shadow:0 4px 12px rgba(0,0,0,.2);
              `;
              toast.textContent = message;
              document.body.appendChild(toast);
              setTimeout(() => toast.remove(), 3500);
            }

            function normalizeInlineEditValue(field, value) {
              if (value === '-' || value === undefined || value === null) return '';
              if (tabulatorNumericFields.has(field)) {
                const raw = String(value).trim();
                const numeric = /^\d+(\.\d+)?$/.test(raw)
                  ? String(Math.round(Number(raw)))
                  : raw.replace(/[^0-9]/g, '');
                return numeric === '' ? '' : numeric;
              }
              return value;
            }

            async function saveTabulatorInlineEdit(cell) {
              const row = cell.getRow();
              const rowData = row.getData();
              const field = cell.getField();
              const oldValue = cell.getOldValue();
              const value = normalizeInlineEditValue(field, cell.getValue());

              if (!isInlineEditableField(field) || !rowData.id) return;
              if (String(value ?? '') === String(normalizeInlineEditValue(field, oldValue) ?? '')) return;

              flashTabulatorCell(cell, '#fff3cd', 100000);

              try {
                const response = await fetch(`/documents/${encodeURIComponent(rowData.id)}/inline-update`, {
                  method: 'PATCH',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': tabulatorCsrfToken,
                    'Accept': 'application/json',
                  },
                  body: JSON.stringify({ field, value }),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.success) {
                  restoreOldCellValue(cell, oldValue);
                  flashTabulatorCell(cell, '#fee2e2', 1500);
                  showTabulatorToast('error', data.message || 'Gagal menyimpan.');
                  return;
                }

                const update = {};
                const rawValue = data.raw_value ?? value ?? '';

                update[field] = rawValue;
                if (field === 'nilai_rupiah') {
                  update.nilai_rupiah = rawValue ? Number(rawValue) : 0;
                  update.nilai_rupiah_fmt = data.display_value || 'Rp. 0';
                } else if (field === 'dpp_pph' || field === 'ppn_terhutang') {
                  update[field] = rawValue ? Number(rawValue) : null;
                  update[`${field}_fmt`] = data.display_value || '-';
                } else if (field === 'tanggal_spp' && data.raw_value) {
                  const date = new Date(`${data.raw_value}T00:00:00`);
                  if (!Number.isNaN(date.getTime())) {
                    const monthNames = ['Januari','Februari','Maret','April','May','Juni','July','Agustus','September','Oktober','November','Desember'];
                    update.bulan = monthNames[date.getMonth()];
                    update.tahun = String(date.getFullYear());
                  }
                }

                row.update(update);
                flashTabulatorCell(cell, '#d1fae5', 1000);
              } catch (error) {
                console.error('Error inline edit:', error);
                restoreOldCellValue(cell, oldValue);
                flashTabulatorCell(cell, '#fee2e2', 1500);
                showTabulatorToast('error', 'Koneksi gagal. Coba lagi.');
              }
            }

            function buildTabulatorDataColumn(field, label) {
              const column = {
                title: label || field,
                field,
                minWidth: 110,
                width: tabulatorColumnWidth(field),
                headerSort: true,
                formatter: plainCellFormatter,
              };

              if (field === 'nomor_agenda') {
                column.formatter = agendaFormatter;
                column.hozAlign = 'center';
                column.frozen = true;
              } else if (field === 'nilai_rupiah') {
                column.sorter = 'number';
                column.hozAlign = 'right';
                column.formatter = moneyFormatter;
              } else if (field === 'dpp_pph' || field === 'ppn_terhutang') {
                column.sorter = 'number';
                column.hozAlign = 'right';
                column.formatter = compactNumberFormatter;
              } else if (tabulatorDateFields.has(field)) {
                column.formatter = dateCellFormatter;
                column.hozAlign = 'center';
              } else if (field === 'status') {
                column.formatter = statusFormatter;
                column.hozAlign = 'center';
                column.width = 260;
                column.cellClick = function(e, cell) {
                  const data = cell.getRow().getData();
                  if (data.status_badge === 'returned' && typeof window.showRejectionModal === 'function') {
                    e.stopPropagation();
                    window.showRejectionModal(data.id);
                  }
                };
              } else if (field === 'uraian_spp' || field === 'dibayar_kepada') {
                column.formatter = wrappedCellFormatter;
                column.vertAlign = 'middle';
              } else if (field === 'link_dokumen_pajak') {
                column.formatter = linkFormatter;
                column.hozAlign = 'center';
              }

              if (isInlineEditableField(field)) {
                column.editor = editorForTabulatorField(field);
                column.editorParams = editorParamsForTabulatorField(field);
                column.editable = canEditTabulatorCell;
                column.cellEdited = saveTabulatorInlineEdit;
                column.cssClass = [column.cssClass, 'tabulator-inline-editable-cell'].filter(Boolean).join(' ');
                column.tooltip = function(e, cell) {
                  cell = cell || e;
                  if (!cell || typeof cell.getRow !== 'function') return '';
                  return cell.getRow().getData().is_inline_editable
                    ? 'Klik untuk edit langsung'
                    : '';
                };
              }

              return column;
            }

            function buildTabulatorColumns(selectedColumns, availableColumns) {
              const columns = [
                {
                  title: '',
                  formatter: 'rowSelection',
                  titleFormatter: 'rowSelection',
                  hozAlign: 'center',
                  headerSort: false,
                  width: 58,
                  frozen: true,
                  cellClick: function(e, cell) {
                    e.stopPropagation();
                    const row = cell.getRow();
                    if (row.getData().can_send) row.toggleSelect();
                  },
                },
                {
                  title: 'No',
                  field: 'no',
                  width: 72,
                  hozAlign: 'center',
                  headerSort: false,
                  frozen: true,
                },
              ];

              (selectedColumns && selectedColumns.length ? selectedColumns : initialSelectedColumns).forEach(field => {
                if (field === 'nomor_mirror') return;
                if (!availableColumns[field]) return;
                columns.push(buildTabulatorDataColumn(field, availableColumns[field]));
              });

              columns.push(
                {
                  title: 'Pengurus Dokumen',
                  field: 'handler_selected',
                  width: 220,
                  headerSort: false,
                  formatter: handlerFormatter,
                  hozAlign: 'center',
                  frozen: true,
                }
              );

              return columns;
            }

            const tabulatorActiveCellState = {
              cell: null,
              rowIndex: 0,
              colIndex: 2,
              rowId: null,
              field: null,
              indicator: null,
              keyboardBound: false,
              visibilityBound: false,
              visibilityTimer: null,
              boundTable: null,
              boundHolder: null,
            };

            function getTabulatorRows() {
              if (!tabelDokumen || typeof tabelDokumen.getRows !== 'function') return [];
              try {
                const activeRows = tabelDokumen.getRows('active');
                if (Array.isArray(activeRows)) return activeRows;
              } catch (error) {
                // Older Tabulator builds may not support the "active" argument.
              }
              return tabelDokumen.getRows() || [];
            }

            function getTabulatorCells(row) {
              if (!row || typeof row.getCells !== 'function') return [];
              return row.getCells().filter(cell => {
                const column = cell.getColumn();
                const definition = column?.getDefinition?.() || {};
                return definition.visible !== false;
              });
            }

            function tabulatorColumnTitle(cell) {
              const column = cell?.getColumn?.();
              const definition = column?.getDefinition?.() || {};
              const rawTitle = definition.title || definition.field || '';
              return String(rawTitle).replace(/<[^>]*>/g, '').trim();
            }

            function tabulatorColumnIndexToLetter(index) {
              let label = '';
              let n = Math.max(0, index);
              do {
                label = String.fromCharCode(65 + (n % 26)) + label;
                n = Math.floor(n / 26) - 1;
              } while (n >= 0);
              return label;
            }

            function ensureTabulatorActiveCellIndicator() {
              if (tabulatorActiveCellState.indicator) return tabulatorActiveCellState.indicator;

              let indicator = document.getElementById('tabulatorAcnIndicator');
              if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'tabulatorAcnIndicator';
                indicator.className = 'tabulator-acn-indicator';
                indicator.innerHTML = `
                  <i class="fa-solid fa-table-cells" style="font-size:13px; opacity:0.8;"></i>
                  <span class="tabulator-acn-cell-ref" id="tabulatorAcnCellRef">-</span>
                  <span class="tabulator-acn-col-name" id="tabulatorAcnColName"></span>
                  <span class="tabulator-acn-hint">
                    <span class="tabulator-acn-key">Arrow</span> navigasi &nbsp;
                    <span class="tabulator-acn-key">Enter</span> edit
                  </span>`;
              }

              const container = document.getElementById('documentTableContainer');
              if (container && indicator.parentElement !== container) {
                container.appendChild(indicator);
              }

              tabulatorActiveCellState.indicator = indicator;
              return indicator;
            }

            function clearTabulatorActiveCellHighlight() {
              const tableElement = document.getElementById('dokumen-tabulator');
              if (tableElement) {
                tableElement.querySelectorAll('.tabulator-acn-active').forEach(el => el.classList.remove('tabulator-acn-active'));
                tableElement.querySelectorAll('.tabulator-acn-pulse').forEach(el => el.classList.remove('tabulator-acn-pulse'));
                tableElement.querySelectorAll('.tabulator-acn-active-row').forEach(el => el.classList.remove('tabulator-acn-active-row'));
                tableElement.querySelectorAll('.tabulator-acn-active-col').forEach(el => el.classList.remove('tabulator-acn-active-col'));
              }
              tabulatorActiveCellState.cell = null;
            }

            function updateTabulatorActiveCellIndicator(rowIndex, colIndex, cell) {
              const indicator = ensureTabulatorActiveCellIndicator();
              const ref = document.getElementById('tabulatorAcnCellRef');
              const title = document.getElementById('tabulatorAcnColName');

              if (ref) ref.textContent = `${tabulatorColumnIndexToLetter(colIndex)}${rowIndex + 1}`;
              if (title) title.textContent = tabulatorColumnTitle(cell);
              indicator.classList.add('visible');
            }

            function visibleFrozenBoundary(holderRect, side) {
              const tableElement = document.getElementById('dokumen-tabulator');
              if (!tableElement) return null;

              const midpoint = holderRect.left + (holderRect.width / 2);
              const frozenElements = Array.from(tableElement.querySelectorAll('.tabulator-frozen'));
              const boundaries = frozenElements
                .map(el => el.getBoundingClientRect())
                .filter(rect => rect.width > 0 && rect.height > 0)
                .filter(rect => rect.bottom > holderRect.top && rect.top < holderRect.bottom);

              if (side === 'left') {
                const leftFrozen = boundaries.filter(rect =>
                  rect.left < midpoint &&
                  rect.right > holderRect.left &&
                  rect.left < holderRect.right
                );
                if (!leftFrozen.length) return null;
                return Math.max(...leftFrozen.map(rect => rect.right));
              }

              const rightFrozen = boundaries.filter(rect =>
                rect.left >= midpoint &&
                rect.left < holderRect.right &&
                rect.right > holderRect.left
              );
              if (!rightFrozen.length) return null;
              return Math.min(...rightFrozen.map(rect => rect.left));
            }

            function ensureTabulatorCellFullyVisible(cell) {
              const cellElement = cell?.getElement?.();
              const tableElement = document.getElementById('dokumen-tabulator');
              const holder = tableElement?.querySelector?.('.tabulator-tableholder');
              if (!cellElement || !holder) return;

              const columnDefinition = cell.getColumn?.()?.getDefinition?.() || {};
              const isFrozenCell = columnDefinition.frozen || cellElement.classList.contains('tabulator-frozen');
              const margin = 8;

              try {
                cellElement.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'auto' });
              } catch (error) {
                cellElement.scrollIntoView(false);
              }

              if (isFrozenCell) return;

              function adjustHorizontalVisibility(attempt = 0) {
                const holderRect = holder.getBoundingClientRect();
                const cellRect = cellElement.getBoundingClientRect();
                const leftBoundary = Math.max(holderRect.left, visibleFrozenBoundary(holderRect, 'left') ?? holderRect.left);
                const rightBoundary = Math.min(holderRect.right, visibleFrozenBoundary(holderRect, 'right') ?? holderRect.right);
                let nextScrollLeft = holder.scrollLeft;

                if (cellRect.right > rightBoundary - margin) {
                  nextScrollLeft += cellRect.right - (rightBoundary - margin);
                } else if (cellRect.left < leftBoundary + margin) {
                  nextScrollLeft -= (leftBoundary + margin) - cellRect.left;
                }

                if (Math.abs(nextScrollLeft - holder.scrollLeft) > 1) {
                  holder.scrollLeft = Math.max(0, nextScrollLeft);
                  if (attempt < 2) {
                    requestAnimationFrame(() => adjustHorizontalVisibility(attempt + 1));
                  }
                }
              }

              requestAnimationFrame(() => adjustHorizontalVisibility());
            }

            function hasValidTabulatorActiveCell() {
              const element = tabulatorActiveCellState.cell?.getElement?.();
              return Boolean(tabulatorActiveCellState.cell && element && document.body.contains(element));
            }

            function ensureCurrentTabulatorActiveCellVisible(options = {}) {
              if (!tabelDokumen) return false;

              if (!hasValidTabulatorActiveCell()) {
                const restored = restoreTabulatorActiveCell({ noPulse: true });
                if (!restored && options.selectFirst !== false) {
                  return autoSelectFirstTabulatorCell({ noPulse: true });
                }
                return restored;
              }

              ensureTabulatorCellFullyVisible(tabulatorActiveCellState.cell);
              return true;
            }

            function scheduleTabulatorActiveCellVisibility(delay = 0, options = {}) {
              clearTimeout(tabulatorActiveCellState.visibilityTimer);
              tabulatorActiveCellState.visibilityTimer = setTimeout(() => {
                requestAnimationFrame(() => {
                  ensureCurrentTabulatorActiveCellVisible(options);
                });
              }, delay);
            }

            function setTabulatorActiveCell(rowIndex, colIndex, options = {}) {
              const rows = getTabulatorRows();
              if (rows.length === 0) {
                clearTabulatorActiveCellHighlight();
                return false;
              }

              const nextRowIndex = Math.max(0, Math.min(rowIndex, rows.length - 1));
              const row = rows[nextRowIndex];
              const cells = getTabulatorCells(row);
              if (cells.length === 0) return false;

              const nextColIndex = Math.max(0, Math.min(colIndex, cells.length - 1));
              const cell = cells[nextColIndex];
              const rowElement = row.getElement?.();
              const cellElement = cell.getElement?.();

              if ((!cellElement || !document.body.contains(cellElement)) && !options.afterScroll) {
                if (typeof row.scrollTo === 'function') {
                  row.scrollTo('center', false).then(() => {
                    setTabulatorActiveCell(nextRowIndex, nextColIndex, Object.assign({}, options, { afterScroll: true }));
                  });
                }
                return false;
              }

              clearTabulatorActiveCellHighlight();

              tabulatorActiveCellState.cell = cell;
              tabulatorActiveCellState.rowIndex = nextRowIndex;
              tabulatorActiveCellState.colIndex = nextColIndex;
              tabulatorActiveCellState.rowId = row.getData?.()?.id ?? null;
              tabulatorActiveCellState.field = cell.getField?.() || null;

              if (rowElement) rowElement.classList.add('tabulator-acn-active-row');
              if (cellElement) {
                cellElement.classList.add('tabulator-acn-active');
                if (!options.noPulse) {
                  cellElement.classList.add('tabulator-acn-pulse');
                  cellElement.addEventListener('animationend', () => {
                    cellElement.classList.remove('tabulator-acn-pulse');
                  }, { once: true });
                }
              }

              const columnElement = cell.getColumn?.()?.getElement?.();
              if (columnElement) columnElement.classList.add('tabulator-acn-active-col');

              updateTabulatorActiveCellIndicator(nextRowIndex, nextColIndex, cell);

              if (cellElement && !options.preventScroll) {
                ensureTabulatorCellFullyVisible(cell);
              }

              return true;
            }

            function firstTabulatorDataColumnIndex(cells) {
              const preferredIndex = cells.findIndex(cell => cell.getField?.() === 'nomor_agenda');
              if (preferredIndex >= 0) return preferredIndex;
              const noIndex = cells.findIndex(cell => cell.getField?.() === 'no');
              return noIndex >= 0 ? noIndex : 0;
            }

            function autoSelectFirstTabulatorCell(options = {}) {
              const rows = getTabulatorRows();
              if (rows.length === 0) return false;
              const cells = getTabulatorCells(rows[0]);
              return setTabulatorActiveCell(0, firstTabulatorDataColumnIndex(cells), options);
            }

            function restoreTabulatorActiveCell(options = {}) {
              const rows = getTabulatorRows();
              if (rows.length === 0) {
                clearTabulatorActiveCellHighlight();
                return false;
              }

              let rowIndex = tabulatorActiveCellState.rowIndex;
              if (tabulatorActiveCellState.rowId !== null) {
                const foundIndex = rows.findIndex(row => String(row.getData?.()?.id) === String(tabulatorActiveCellState.rowId));
                if (foundIndex >= 0) rowIndex = foundIndex;
              }

              rowIndex = Math.max(0, Math.min(rowIndex, rows.length - 1));
              const cells = getTabulatorCells(rows[rowIndex]);
              let colIndex = tabulatorActiveCellState.colIndex;
              if (tabulatorActiveCellState.field) {
                const foundColIndex = cells.findIndex(cell => cell.getField?.() === tabulatorActiveCellState.field);
                if (foundColIndex >= 0) colIndex = foundColIndex;
              }

              return setTabulatorActiveCell(rowIndex, colIndex, options);
            }

            function moveTabulatorActiveCell(deltaRow, deltaCol) {
              if (!tabulatorActiveCellState.cell) {
                autoSelectFirstTabulatorCell();
                return;
              }

              const rows = getTabulatorRows();
              if (rows.length === 0) return;

              const nextRowIndex = Math.max(0, Math.min(tabulatorActiveCellState.rowIndex + deltaRow, rows.length - 1));
              const nextCells = getTabulatorCells(rows[nextRowIndex]);
              const nextColIndex = Math.max(0, Math.min(tabulatorActiveCellState.colIndex + deltaCol, nextCells.length - 1));
              setTabulatorActiveCell(nextRowIndex, nextColIndex);
            }

            function shouldIgnoreTabulatorActiveCellKey(event) {
              const activeTag = document.activeElement?.tagName?.toLowerCase();
              if (['input', 'textarea', 'select'].includes(activeTag)) return true;
              if (document.activeElement?.isContentEditable) return true;
              if (document.querySelector('.modal.show, .modal-overlay.show, .swal2-container.swal2-shown, #dokumen-tabulator .tabulator-editing')) return true;

              const target = event.target instanceof Element ? event.target : null;
              if (!tabulatorActiveCellState.cell && target && !target.closest('#dokumen-tabulator')) {
                return true;
              }

              return false;
            }

            function bindTabulatorActiveCellKeyboard() {
              if (tabulatorActiveCellState.keyboardBound) return;

              document.addEventListener('keydown', function(event) {
                const navigationKeys = ['ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp', 'Tab', 'Enter', 'Escape'];
                if (!navigationKeys.includes(event.key)) return;
                if (shouldIgnoreTabulatorActiveCellKey(event)) return;

                const activeElement = tabulatorActiveCellState.cell?.getElement?.();
                if (!tabulatorActiveCellState.cell || !activeElement || !document.body.contains(activeElement)) {
                  restoreTabulatorActiveCell({ noPulse: true });
                }

                switch (event.key) {
                  case 'ArrowRight':
                    event.preventDefault();
                    moveTabulatorActiveCell(0, 1);
                    break;
                  case 'ArrowLeft':
                    event.preventDefault();
                    moveTabulatorActiveCell(0, -1);
                    break;
                  case 'ArrowDown':
                    event.preventDefault();
                    moveTabulatorActiveCell(1, 0);
                    break;
                  case 'ArrowUp':
                    event.preventDefault();
                    moveTabulatorActiveCell(-1, 0);
                    break;
                  case 'Tab':
                    event.preventDefault();
                    moveTabulatorActiveCell(0, event.shiftKey ? -1 : 1);
                    break;
                  case 'Enter':
                    if (tabulatorActiveCellState.cell && canEditTabulatorCell(tabulatorActiveCellState.cell)) {
                      event.preventDefault();
                      tabulatorActiveCellState.cell.edit();
                    }
                    break;
                  case 'Escape':
                    event.preventDefault();
                    restoreTabulatorActiveCell({ noPulse: true });
                    break;
                  default:
                    break;
                }
              });

              tabulatorActiveCellState.keyboardBound = true;
            }

            function bindTabulatorActiveCellVisibilityHandlers() {
              if (!tabulatorActiveCellState.visibilityBound) {
                const scheduleAfterViewportChange = () => scheduleTabulatorActiveCellVisibility(80);
                window.addEventListener('resize', scheduleAfterViewportChange);
                window.addEventListener('focus', scheduleAfterViewportChange);
                window.addEventListener('pageshow', scheduleAfterViewportChange);
                document.addEventListener('fullscreenchange', scheduleAfterViewportChange);
                document.addEventListener('visibilitychange', function() {
                  if (!document.hidden) scheduleTabulatorActiveCellVisibility(80);
                });
                tabulatorActiveCellState.visibilityBound = true;
              }

              const holder = document.querySelector('#dokumen-tabulator .tabulator-tableholder');
              if (holder && tabulatorActiveCellState.boundHolder !== holder) {
                if (tabulatorActiveCellState.boundHolder) {
                  tabulatorActiveCellState.boundHolder.removeEventListener('scroll', tabulatorActiveCellState.onHolderScroll);
                }

                tabulatorActiveCellState.onHolderScroll = () => scheduleTabulatorActiveCellVisibility(120, { selectFirst: false });
                holder.addEventListener('scroll', tabulatorActiveCellState.onHolderScroll, { passive: true });
                tabulatorActiveCellState.boundHolder = holder;
              }
            }

            function bindTabulatorActiveCell() {
              if (!tabelDokumen) return;
              ensureTabulatorActiveCellIndicator();
              bindTabulatorActiveCellKeyboard();
              bindTabulatorActiveCellVisibilityHandlers();

              if (tabulatorActiveCellState.boundTable === tabelDokumen) return;
              tabelDokumen.on('cellClick', function(event, cell) {
                const target = event.target instanceof Element ? event.target : null;
                if (target && target.closest('a, button, input, select, textarea, .btn, .btn-action')) return;
                const row = cell.getRow();
                const rows = getTabulatorRows();
                const cells = getTabulatorCells(row);
                const rowIndex = rows.indexOf(row);
                const colIndex = cells.indexOf(cell);
                if (rowIndex >= 0 && colIndex >= 0) {
                  setTabulatorActiveCell(rowIndex, colIndex);
                }
              });

              tabelDokumen.on('renderComplete', function() {
                bindTabulatorActiveCellVisibilityHandlers();
                if (tabulatorActiveCellState.rowId !== null || tabulatorActiveCellState.cell) {
                  restoreTabulatorActiveCell({ noPulse: true });
                }
                scheduleTabulatorActiveCellVisibility(40);
              });

              tabelDokumen.on('cellEdited', function(cell) {
                if (cell) {
                  const rows = getTabulatorRows();
                  const row = cell.getRow();
                  const cells = getTabulatorCells(row);
                  const rowIndex = rows.indexOf(row);
                  const colIndex = cells.indexOf(cell);
                  if (rowIndex >= 0 && colIndex >= 0) {
                    setTabulatorActiveCell(rowIndex, colIndex, { noPulse: true });
                    return;
                  }
                }
                scheduleTabulatorActiveCellVisibility(40);
              });

              tabulatorActiveCellState.boundTable = tabelDokumen;
            }

            function addSelectedDocument(rowData) {
              if (typeof selectedDocuments === 'undefined') return;
              selectedDocuments.set(String(rowData.id), {
                id: rowData.id,
                nomor_agenda: rowData.nomor_agenda,
                nomor_spp: rowData.nomor_spp,
                nilai_rupiah: rowData.nilai_rupiah_fmt || rowData.nilai_rupiah || '-',
              });
              if (typeof updateBulkActionBar === 'function') updateBulkActionBar();
            }

            function removeSelectedDocument(rowData) {
              if (typeof selectedDocuments === 'undefined') return;
              selectedDocuments.delete(String(rowData.id));
              if (typeof updateBulkActionBar === 'function') updateBulkActionBar();
            }

            function getTabulatorRequestUrl() {
              const params = new URLSearchParams(window.location.search);
              params.delete('page');
              params.delete('per_page');
              const queryString = params.toString();
              return queryString ? `${tabulatorDataUrl}?${queryString}` : tabulatorDataUrl;
            }

            function setTabulatorLoading(isLoading) {
              const loadingPanel = document.getElementById('tabulatorLoadingPanel');
              const tableEl = document.getElementById('dokumen-tabulator');
              if (loadingPanel) {
                if (isLoading) {
                  loadingPanel.innerHTML = '<span class="spinner-border spinner-border-sm text-success" role="status" aria-hidden="true"></span><span>Memuat data dokumen...</span>';
                }
                loadingPanel.classList.toggle('is-visible', isLoading);
              }
              if (tableEl) tableEl.style.display = isLoading ? 'none' : 'block';
              const refreshButton = document.getElementById('btnRefreshTable');
              if (refreshButton) {
                refreshButton.disabled = isLoading;
                refreshButton.classList.toggle('loading', isLoading);
              }
            }

            async function loadTabulatorData(showToast = false) {
              setTabulatorLoading(true);
              let loadFailed = false;
              try {
                await ensureTabulatorLoaded();

                const response = await fetch(getTabulatorRequestUrl(), {
                  headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                  },
                });

                if (!response.ok) {
                  throw new Error('Gagal memuat data dokumen.');
                }

                const payload = await response.json();
                const rows = Array.isArray(payload) ? payload : (payload.data || []);
                const columns = Array.isArray(payload) ? initialSelectedColumns : (payload.columns || initialSelectedColumns);
                const availableColumns = Array.isArray(payload) ? initialAvailableColumns : (payload.availableColumns || initialAvailableColumns);
                tabulatorHandlerOptions = Array.isArray(payload) ? tabulatorHandlerOptions : (payload.handlerOptions || tabulatorHandlerOptions);
                allDataCache = rows;

                if (tabelDokumen) {
                  tabelDokumen.setColumns(buildTabulatorColumns(columns, availableColumns));
                  await tabelDokumen.replaceData(rows);
                  bindTabulatorActiveCell();
                  scheduleTabulatorActiveCellVisibility(60);
                } else {
                  tabelDokumen = new Tabulator('#dokumen-tabulator', {
                    data: rows,
                    height: tabulatorHeight(),
                    virtualDom: true,
                    virtualDomBuffer: 400,
                    layout: 'fitDataStretch',
                    reactiveData: false,
                    placeholder: 'Tidak ada data dokumen',
                    selectableRows: true,
                    selectableRowsCheck: function(row) {
                      return row.getData().can_send === true;
                    },
                    initialSort: [
                      { column: 'nomor_agenda', dir: 'desc' },
                    ],
                    columns: buildTabulatorColumns(columns, availableColumns),
                    rowDblClick: function(e, row) {
                      const target = e.target;
                      if (target.closest('a, button, input, select, textarea, .btn, .btn-action')) return;
                      if (typeof window.openViewDocumentModal === 'function') {
                        window.openViewDocumentModal(row.getData().id);
                      }
                    },
                    rowSelected: function(row) {
                      addSelectedDocument(row.getData());
                    },
                    rowDeselected: function(row) {
                      removeSelectedDocument(row.getData());
                    },
                  });
                  bindTabulatorActiveCell();
                  scheduleTabulatorActiveCellVisibility(60);
                }

                if (typeof selectedDocuments !== 'undefined') {
                  selectedDocuments.clear();
                  if (typeof updateBulkActionBar === 'function') updateBulkActionBar();
                }

                if (showToast && typeof showRefreshToast === 'function') {
                  showRefreshToast('success', 'Data berhasil diperbarui!');
                }
              } catch (error) {
                loadFailed = true;
                console.error(error);
                const loadingPanel = document.getElementById('tabulatorLoadingPanel');
                if (loadingPanel) {
                  loadingPanel.classList.add('is-visible');
                  loadingPanel.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger"></i><span>Gagal memuat data dokumen. Klik Refresh untuk mencoba lagi.</span>';
                }
                const tableEl = document.getElementById('dokumen-tabulator');
                if (tableEl) tableEl.style.display = 'none';
                if (showToast && typeof showRefreshToast === 'function') {
                  showRefreshToast('error', 'Gagal memperbarui data. Coba lagi.');
                }
              } finally {
                if (!loadFailed) {
                  setTabulatorLoading(false);
                  requestAnimationFrame(() => {
                    if (!restoreTabulatorActiveCell({ noPulse: true })) {
                      autoSelectFirstTabulatorCell({ noPulse: true });
                    }
                    scheduleTabulatorActiveCellVisibility(80);
                  });
                }
              }
            }

            window.refreshDocumentTable = function() {
              return loadTabulatorData(true);
            };

            window.handleFilterSubmit = function(event) {
              if (event) event.preventDefault();
              const form = document.getElementById('filterForm');
              if (!form) return false;

              const params = new URLSearchParams();
              const searchInput = form.querySelector('input[name="search"]');
              const yearInput = form.querySelector('input[name="year"]');
              const statusInput = form.querySelector('input[name="status_filter"]');

              if (searchInput && searchInput.value.trim()) params.set('search', searchInput.value.trim());
              if (yearInput && yearInput.value) params.set('year', yearInput.value);
              if (statusInput && statusInput.value) params.set('status_filter', statusInput.value);
              form.querySelectorAll('input[name="columns[]"]').forEach(input => {
                if (input.value) params.append('columns[]', input.value);
              });

              const nextUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
              history.pushState({}, '', nextUrl);
              loadTabulatorData(false);
              return false;
            };

            document.addEventListener('submit', function(event) {
              if (event.target && event.target.id === 'filterForm') {
                window.handleFilterSubmit(event);
              }
            }, true);

            document.addEventListener('change', async function(event) {
              const select = event.target.closest('.tabulator-handler-select');
              if (!select) return;
              event.stopPropagation();

              const previousValue = select.dataset.originalValue;
              const nextValue = select.value;
              if (!nextValue || nextValue === previousValue) {
                select.value = previousValue;
                return;
              }

              select.disabled = true;
              select.classList.add('is-saving');

              try {
                const response = await fetch(`/documents/${select.dataset.documentId}/handler`, {
                  method: 'PATCH',
                  headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': tabulatorCsrfToken,
                  },
                  body: JSON.stringify({ target_handler: nextValue }),
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok || result.success !== true) {
                  throw new Error(result.message || 'Gagal mengubah pengurus dokumen.');
                }

                select.dataset.originalValue = nextValue;
                if (window.Swal) {
                  await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: result.message || 'Pengurus dokumen berhasil diubah.',
                    timer: 1200,
                    showConfirmButton: false,
                  });
                }
                await loadTabulatorData(false);
              } catch (error) {
                select.value = previousValue;
                alert(error.message || 'Gagal mengubah pengurus dokumen.');
                select.disabled = false;
                select.classList.remove('is-saving');
              }
            }, true);

            document.addEventListener('DOMContentLoaded', function() {
              const originalClearAllSelections = window.clearAllSelections;
              window.clearAllSelections = function() {
                if (typeof selectedDocuments !== 'undefined') selectedDocuments.clear();
                if (tabelDokumen) tabelDokumen.deselectRow();
                if (typeof originalClearAllSelections === 'function') {
                  originalClearAllSelections();
                } else if (typeof updateBulkActionBar === 'function') {
                  updateBulkActionBar();
                }
              };

              loadTabulatorData(false);
            });

            window.addEventListener('resize', function() {
              if (tabelDokumen) tabelDokumen.setHeight(tabulatorHeight());
              scheduleTabulatorActiveCellVisibility(120);
            });
          </script>
          @endif

@include('partials.virtual-document-table', ['paginator' => $dokumens, 'chunkSize' => 100, 'enabled' => true])
@include('partials._inlineEditEngine')
@include('partials._activeCellNav', ['tableSelector' => '.data-table'])
@include('partials._documentTableStickyCells')
<style>
  #documentTableContainer #dokumenTableBody tr,
  #documentTableContainer #dokumenTableBody tr.main-row {
    contain: none !important;
    content-visibility: visible !important;
  }

  #documentTableContainer .operator-document-table .sort-indicator {
    display: inline-flex;
    margin-left: 6px;
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.75rem;
  }

  #documentTableContainer .operator-document-table .col-handler .document-handler-select {
    width: 100%;
    max-width: none;
  }
</style>
@endsection

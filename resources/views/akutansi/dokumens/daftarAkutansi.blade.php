@extends('layouts/app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Bulk Select Styles */
    .bulk-checkbox {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #083E40;
    }

    .col-checkbox {
      width: 50px;
      text-align: center;
      vertical-align: middle;
    }

    /* Floating Action Bar */
    .floating-action-bar {
      position: fixed;
      bottom: -100px;
      left: 50%;
      transform: translateX(-50%);
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      padding: 16px 32px;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.4);
      display: flex;
      align-items: center;
      gap: 20px;
      z-index: 1000;
      transition: bottom 0.3s ease;
    }

    .floating-action-bar.show {
      bottom: 30px;
    }

    .floating-action-bar .selected-info {
      color: white;
      font-weight: 600;
      font-size: 14px;
    }

    .floating-action-bar .selected-count {
      background: rgba(255, 255, 255, 0.2);
      padding: 4px 12px;
      border-radius: 20px;
      margin-left: 8px;
    }

    .floating-action-bar select {
      padding: 10px 16px;
      border-radius: 8px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      background: rgba(255, 255, 255, 0.1);
      color: white;
      font-size: 14px;
      cursor: pointer;
      min-width: 180px;
    }

    .floating-action-bar select option {
      background: #083E40;
      color: white;
    }

    .floating-action-bar .btn-bulk-action {
      padding: 10px 24px;
      border-radius: 8px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .floating-action-bar .btn-send {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
    }

    .floating-action-bar .btn-send:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .floating-action-bar .btn-cancel {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }

    .floating-action-bar .btn-cancel:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    /* Bulk Send Confirmation Modal */
    .bulk-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 2000;
    }

    .bulk-modal-overlay.show {
      display: flex;
    }

    .bulk-modal {
      background: white;
      border-radius: 16px;
      padding: 32px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .bulk-modal h3 {
      margin: 0 0 16px 0;
      color: #083E40;
      font-size: 20px;
    }

    .bulk-modal .summary {
      background: #f8faf8;
      padding: 16px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .bulk-modal .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
    }

    .bulk-modal .summary-item:last-child {
      margin-bottom: 0;
    }

    .bulk-modal .summary-label {
      color: #6b7280;
    }

    .bulk-modal .summary-value {
      font-weight: 600;
      color: #083E40;
    }

    .bulk-modal .actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }

    .bulk-modal .btn {
      padding: 12px 24px;
      border-radius: 8px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .bulk-modal .btn-confirm {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
    }

    .bulk-modal .btn-close {
      background: #6b7280;
      color: white;
    }

    /* Loading Overlay */
    .bulk-loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 3000;
      flex-direction: column;
      gap: 20px;
    }

    .bulk-loading-overlay.show {
      display: flex;
    }

    .bulk-loading-overlay .spinner {
      width: 50px;
      height: 50px;
      border: 4px solid rgba(255, 255, 255, 0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    .bulk-loading-overlay .loading-text {
      color: white;
      font-size: 16px;
      font-weight: 600;
    }

    .bulk-loading-overlay .progress-text {
      color: rgba(255, 255, 255, 0.8);
      font-size: 14px;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
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
      padding: 10px 35px 10px 14px;
      border: 2px solid rgba(8, 62, 64, 0.1);
      border-radius: 10px;
      font-size: 13px;
      transition: all 0.3s ease;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 12px;
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
      min-width: 1600px;
      /* Minimum width for horizontal scroll with all columns */
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
      vertical-align: middle;
      border-bottom: 2px solid #1a4d3e;
      padding: 18px 16px;
      font-size: 13px;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      border: none;
      white-space: nowrap;
    }

    /* Column Widths - Enhanced from perpajakan */
    .table-enhanced .col-number {
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
      width: 700px;
      min-width: 500px;
      max-width: 1000px;
      text-align: left;
      word-wrap: break-word;
      white-space: normal;
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

    .table-enhanced .col-deadline {
      width: 180px;
      min-width: 180px;
      text-align: center;
    }

    .table-enhanced .col-status {
      width: 320px;
      min-width: 320px;
      max-width: 350px;
      text-align: center;
      overflow: visible;
    }

    .table-enhanced .col-action {
      width: 180px;
      min-width: 180px;
      text-align: center;
      overflow: visible;
      position: relative;
      box-sizing: border-box;
    }

    .table-enhanced tbody tr {
      transition: all 0.3s ease;
      border-bottom: 1px solid rgba(26, 77, 62, 0.08);
      position: relative;
      border-left: 3px solid transparent;
    }

    .table-enhanced tbody td {
      font-weight: 700;
    }

    /* Enhanced Locked Row Styling from perpajakan */
    .table-enhanced tbody tr.locked-row {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      opacity: 0.85;
      position: relative;
      border-left: 4px solid #ffc107 !important;
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
      font-weight: 500;
      color: #374151;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* Custom centering for specific column content */
    .table-enhanced .col-number,
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
      width: 700px;
      min-width: 500px;
      max-width: 1000px;
      text-align: left;
      font-weight: 600;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
      overflow-wrap: break-word;
      line-height: 1.6;
      vertical-align: top;
      padding: 12px;
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
    .table-enhanced td.col-agenda>strong,
    .table-enhanced td.col-agenda>small {
      display: block;
      text-align: center;
    }

    /* Center deadline content */
    .table-enhanced td.col-deadline>small,
    .table-enhanced td.col-deadline>span {
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
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
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
      content: "\f058";
      /* check-circle */
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
      content: "\f071";
      /* exclamation-triangle */
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
      content: "\f071";
      /* exclamation-triangle */
      animation: warning-shake 1s infinite;
    }

    /* New System: Age-based deadline colors (count up from received_at) */
    /* Green - Aman (<1 hari) */
    .deadline-card.deadline-green {
      --deadline-color: #10b981;
      --deadline-color-light: #34d399;
      --deadline-bg: #ecfdf5;
      --deadline-text: #065f46;
    }

    .deadline-card.deadline-green {
      background: var(--deadline-bg) !important;
      border-color: rgba(16, 185, 129, 0.2) !important;
    }

    .deadline-card.deadline-green .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-green {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(16, 185, 129, 0.4);
    }

    /* Yellow - Perlu Perhatian (>=1 hari <3 hari) */
    .deadline-card.deadline-yellow {
      --deadline-color: #f59e0b;
      --deadline-color-light: #fbbf24;
      --deadline-bg: #fffbeb;
      --deadline-text: #92400e;
    }

    .deadline-card.deadline-yellow {
      background: var(--deadline-bg) !important;
      border-color: rgba(245, 158, 11, 0.2) !important;
    }

    .deadline-card.deadline-yellow .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-yellow {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(245, 158, 11, 0.4);
    }

    /* Red - Terlambat (>=3 hari) */
    .deadline-card.deadline-red {
      --deadline-color: #ef4444;
      --deadline-color-light: #f87171;
      --deadline-bg: #fef2f2;
      --deadline-text: #991b1b;
    }

    .deadline-card.deadline-red {
      background: var(--deadline-bg) !important;
      border-color: rgba(239, 68, 68, 0.2) !important;
    }

    .deadline-card.deadline-red .deadline-time {
      color: var(--deadline-text) !important;
      font-weight: 800;
    }

    .deadline-indicator.deadline-red {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(239, 68, 68, 0.4);
      animation: danger-pulse 2s infinite;
    }

    /* Completed State - Dark Green Theme */
    .deadline-card.deadline-completed {
      --deadline-color: #15803d;
      --deadline-color-light: #16a34a;
      --deadline-bg: #f0fdf4;
      --deadline-text: #14532d;
      opacity: 0.9;
    }

    .deadline-card.deadline-completed {
      background: var(--deadline-bg) !important;
      border-color: rgba(21, 128, 61, 0.2) !important;
    }

    .deadline-card.deadline-completed .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-completed {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(21, 128, 61, 0.4);
    }

    .deadline-indicator.deadline-completed i::before {
      content: "\f058";
      /* check-double */
    }

    /* Sent State - Grey Theme */
    .deadline-card.deadline-sent {
      --deadline-color: #6b7280;
      --deadline-color-light: #9ca3af;
      --deadline-bg: #f3f4f6;
      --deadline-text: #374151;
      opacity: 0.8;
    }

    .deadline-card.deadline-sent {
      background: var(--deadline-bg) !important;
      border-color: rgba(107, 114, 128, 0.2) !important;
    }

    .deadline-card.deadline-sent .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-sent {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(107, 114, 128, 0.4);
    }

    .deadline-indicator.deadline-sent i::before {
      content: "\f1d8";
      /* paper-plane */
    }

    /* OVERRIDE: Force gray color for deadline-sent indicators regardless of other color classes */
    .deadline-card.deadline-sent .deadline-indicator,
    .deadline-card.deadline-sent .deadline-indicator.deadline-green,
    .deadline-card.deadline-sent .deadline-indicator.deadline-yellow,
    .deadline-card.deadline-sent .deadline-indicator.deadline-red {
      background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%) !important;
      color: white !important;
      box-shadow: 0 2px 6px rgba(107, 114, 128, 0.3) !important;
    }

    /* OVERRIDE: Force gray theme for entire deadline card when sent, regardless of color classes */
    .deadline-card.deadline-sent.deadline-green,
    .deadline-card.deadline-sent.deadline-yellow,
    .deadline-card.deadline-sent.deadline-red {
      --deadline-color: #6b7280 !important;
      --deadline-color-light: #9ca3af !important;
      --deadline-bg: #f9fafb !important;
      --deadline-text: #4b5563 !important;
      background: #f9fafb !important;
      border-color: rgba(107, 114, 128, 0.2) !important;
      opacity: 0.8;
    }

    .deadline-card.deadline-sent.deadline-green .deadline-time,
    .deadline-card.deadline-sent.deadline-yellow .deadline-time,
    .deadline-card.deadline-sent.deadline-red .deadline-time {
      color: #4b5563 !important;
    }

    /* Paused State - Grey Theme for returned documents */
    .deadline-card.deadline-paused {
      --deadline-color: #9ca3af;
      --deadline-color-light: #d1d5db;
      --deadline-bg: #f3f4f6;
      --deadline-text: #6b7280;
      opacity: 0.75;
    }

    .deadline-card.deadline-paused {
      background: var(--deadline-bg) !important;
      border-color: rgba(156, 163, 175, 0.3) !important;
      border-style: dashed !important;
    }

    .deadline-card.deadline-paused .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-card.deadline-paused .deadline-indicator,
    .deadline-card.deadline-paused .deadline-indicator.deadline-green,
    .deadline-card.deadline-paused .deadline-indicator.deadline-yellow,
    .deadline-card.deadline-paused .deadline-indicator.deadline-red {
      background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%) !important;
      color: white !important;
      box-shadow: 0 2px 6px rgba(156, 163, 175, 0.3) !important;
    }

    .deadline-card.deadline-paused .deadline-indicator i::before {
      content: "\f04c";
      /* pause icon */
    }

    .deadline-card.deadline-paused.deadline-green,
    .deadline-card.deadline-paused.deadline-yellow,
    .deadline-card.deadline-paused.deadline-red {
      --deadline-color: #9ca3af !important;
      --deadline-color-light: #d1d5db !important;
      --deadline-bg: #f3f4f6 !important;
      --deadline-text: #6b7280 !important;
      background: #f3f4f6 !important;
      border-color: rgba(156, 163, 175, 0.3) !important;
      border-style: dashed !important;
      opacity: 0.75;
    }

    .deadline-card.deadline-paused.deadline-green .deadline-time,
    .deadline-card.deadline-paused.deadline-yellow .deadline-time,
    .deadline-card.deadline-paused.deadline-red .deadline-time {
      color: #6b7280 !important;
    }

    .deadline-card.deadline-paused .deadline-paused-label {
      font-size: 8px;
      color: #9ca3af;
      margin-top: 4px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
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

      0%,
      85%,
      100% {
        border-color: rgba(220, 38, 38, 0.3);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      }

      90%,
      95% {
        border-color: rgba(220, 38, 38, 0.8);
        box-shadow: 0 0 16px rgba(220, 38, 38, 0.4);
      }
    }

    @keyframes overdue-glow {

      0%,
      100% {
        box-shadow: 0 4px 16px rgba(220, 38, 68, 0.5);
        transform: translateY(0);
      }

      50% {
        box-shadow: 0 6px 24px rgba(220, 38, 68, 0.7);
        transform: translateY(-1px);
      }
    }

    @keyframes late-warning {

      0%,
      100% {
        background: linear-gradient(135deg, rgba(220, 38, 68, 0.1) 0%, rgba(239, 68, 68, 0.15) 100%);
        transform: scale(1);
      }

      50% {
        background: linear-gradient(135deg, rgba(220, 38, 68, 0.15) 0%, rgba(239, 68, 68, 0.25) 100%);
        transform: scale(1.02);
      }
    }

    @keyframes warning-shake {

      0%,
      100% {
        transform: translateX(0) rotate(0deg);
      }

      25% {
        transform: translateX(-1px) rotate(-1deg);
      }

      75% {
        transform: translateX(1px) rotate(1deg);
      }
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
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      white-space: nowrap;
    }

    /* State 1: 🔒 Terkunci (Locked - Waiting for Deadline) */
    .badge-status.badge-locked {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      border-color: #495057;
      position: relative;
    }

    /* State 2: ⏳ Diproses (In Progress) */
    .badge-status.badge-proses {
      background: linear-gradient(135deg, #2E6F68 0%, #2A605A 100%);
      color: white;
      border-color: #2A605A;
      box-shadow: 0 3px 12px rgba(46, 111, 104, 0.25);
    }

    .badge-status.badge-proses::after {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      background: #4CAF50;
      border-radius: 50%;
      margin-left: 6px;
      animation: pulse 1.5s infinite;
    }

    /* State 3: ✅ Selesai (Completed) */
    .badge-status.badge-selesai {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border-color: #083E40;
    }

    .badge-status.badge-belum {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      border-color: #495057;
    }

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

    /* Special state for sent documents */
    .badge-status.badge-sent {
      background: #083E40;
      color: white;
      border-color: #083E40;
      position: relative;
    }

    .badge-status.badge-warning {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
      color: white;
      border-color: #ffc107;
      position: relative;
    }

    .badge-status:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    /* Enhanced Action Buttons matching perpajakan */
    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 6px;
      justify-content: center;
      align-items: center;
      width: 100%;
      position: relative;
      z-index: 1;
      box-sizing: border-box;
      max-width: 100%;
    }

    /* Hybrid Layout: Full-width button on top, row buttons below */
    .action-buttons-hybrid {
      display: flex;
      flex-direction: column;
      gap: 6px;
      width: 100%;
    }

    .action-buttons-hybrid .btn-full-width {
      width: 100%;
      min-width: 100%;
    }

    .action-buttons-hybrid .action-row {
      display: flex;
      gap: 6px;
      justify-content: center;
      align-items: center;
      width: 100%;
    }

    .action-buttons-hybrid .action-row .btn-action {
      flex: 1;
      min-width: 0;
    }

    .btn-kembalikan {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }

    .btn-kembalikan:hover {
      background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
      color: white;
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
      width: auto !important;
      flex: 0 0 auto;
      white-space: nowrap;
      cursor: pointer;
      font-size: 11px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      position: relative;
      overflow: visible;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      text-decoration: none;
      user-select: none;
      max-width: 140px;
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .btn-action span {
      font-size: 10px;
      font-weight: 600;
      white-space: nowrap;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      display: inline-block;
    }

    .btn-action.btn-full-width span {
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      display: inline-block;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .btn-edit {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
    }

    .btn-edit:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
      color: white;
    }

    .btn-kirim {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
      color: white;
    }

    .btn-kirim:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #0f6f74 100%);
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
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
      /* Dark teal gradient */
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
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #0f6f74 100%);
      /* Darker teal on hover */
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.4);
      /* Dark teal shadow */
      border-color: rgba(8, 62, 64, 0.6);
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

    .btn-sent {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      cursor: default;
      opacity: 0.85;
    }

    .btn-sent:hover {
      transform: none;
      box-shadow: none;
    }

    /* Destination Card Hover Effect */
    #destination-pembayaran:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2) !important;
      border-color: #0a4f52 !important;
    }

    #destination-pembayaran input[type="radio"]:checked+label {
      color: #083E40;
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

    .btn-terkirim {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
      color: white;
      cursor: default;
    }

    .btn-terkirim:hover {
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

    @keyframes shimmer {
      0% {
        left: -100%;
      }

      100% {
        left: 100%;
      }
    }

    @keyframes fadeInOut {

      0%,
      100% {
        opacity: 0.3;
      }

      50% {
        opacity: 1;
      }
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
        scrollbar-width: none;
        /* Hide scrollbar on mobile */
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
        min-width: 1600px;
        /* Still allow horizontal scroll on very small screens */
      }

      .table-enhanced .col-number {
        min-width: 60px;
        font-weight: 600;
      }

      .table-enhanced .col-agenda {
        min-width: 130px;
      }

      .table-enhanced .col-tanggal {
        min-width: 130px;
        font-weight: 600;
      }

      .table-enhanced .col-spp {
        min-width: 140px;
        font-weight: 600;
      }

      .table-enhanced .col-nilai {
        min-width: 140px;
      }

      .table-enhanced .col-tanggal-spp {
        min-width: 130px;
      }

      .table-enhanced .col-uraian {
        width: 500px;
        min-width: 400px;
        max-width: 700px;
        word-wrap: break-word;
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.6;
        vertical-align: top;
        padding: 12px;
        font-weight: 600;
      }

      .table-enhanced .col-deadline {
        min-width: 160px;
      }

      .table-enhanced .col-status {
        min-width: 140px;
      }

      .table-enhanced .col-action {
        min-width: 160px;
      }
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

    /* Year Filter Button */
    .btn-year-filter {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-year-filter:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    /* Year Filter Modal Styles */
    .year-filter-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      backdrop-filter: blur(4px);
    }

    .year-filter-modal-overlay.active {
      display: flex;
    }

    .year-filter-modal {
      background: white;
      border-radius: 16px;
      width: 100%;
      max-width: 480px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: yearModalSlideIn 0.3s ease;
      overflow: hidden;
    }

    @keyframes yearModalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .year-filter-modal-header {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      padding: 20px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .year-filter-modal-header h5 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .year-filter-modal-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .year-filter-modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .year-filter-modal-body {
      padding: 24px;
    }

    .filter-type-section {
      margin-bottom: 24px;
    }

    .filter-type-section h6 {
      font-size: 14px;
      font-weight: 700;
      color: #083E40;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .filter-type-options {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .filter-type-option {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .filter-type-option:hover {
      background: #e9f5f0;
      border-color: #083E40;
    }

    .filter-type-option.selected {
      background: linear-gradient(135deg, #e9f5f0 0%, #d4ebe4 100%);
      border-color: #083E40;
    }

    .filter-type-option input[type="radio"] {
      margin-right: 12px;
      accent-color: #083E40;
      transform: scale(1.2);
    }

    .filter-type-option label {
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      flex: 1;
    }

    .filter-type-option small {
      color: #6c757d;
      font-size: 12px;
    }

    .year-selection-section h6 {
      font-size: 14px;
      font-weight: 700;
      color: #083E40;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .year-buttons-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
    }

    .year-btn {
      padding: 14px 16px;
      border: 2px solid #e9ecef;
      background: #f8f9fa;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      color: #333;
    }

    .year-btn:hover {
      background: #e9f5f0;
      border-color: #083E40;
    }

    .year-btn.selected {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      border-color: #083E40;
      color: white;
    }

    .year-btn.all-years {
      grid-column: span 4;
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      border-color: #6c757d;
    }

    .year-btn.all-years.selected {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      border-color: #083E40;
    }

    .year-filter-modal-footer {
      padding: 16px 24px;
      background: #f8f9fa;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }

    .btn-reset-filter {
      padding: 10px 20px;
      background: #fff;
      border: 2px solid #dc3545;
      color: #dc3545;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-reset-filter:hover {
      background: #dc3545;
      color: white;
    }

    .btn-apply-filter {
      padding: 10px 24px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      border: none;
      color: white;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-apply-filter:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
      transform: translateY(-1px);
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
      display: inline-flex;
      align-items: center;
      min-height: 44px;
      white-space: nowrap;
    }

    .btn-filter:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    }

    .btn-filter:active {
      transform: translateY(0);
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
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

  <h2>{{ $title }}</h2>

  {{-- ===== BENTO GRID INFORMASI AKUTANSI ===== --}}
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
    .bento-info-grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      grid-template-rows: auto auto;
      gap: 16px;
      margin-bottom: 24px;
      font-family: 'Inter', sans-serif;
    }
    /* Stat cards — span 3 cols each */
    .bento-stat-card { grid-column: span 3; }
    /* Deadline cards — span 4 cols each */
    .bento-deadline-card { grid-column: span 4; }

    /* === BASE CARD === */
    .bento-card {
      background: #fff;
      border-radius: 16px;
      padding: 20px 22px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 16px rgba(8,62,64,0.07);
      border: 1px solid #f0f4f4;
      position: relative;
      overflow: hidden;
      transition: box-shadow 0.25s, transform 0.25s;
      cursor: default;
    }
    .bento-card:hover {
      box-shadow: 0 4px 20px rgba(8,62,64,0.14), 0 1px 4px rgba(0,0,0,0.06);
      transform: translateY(-2px);
    }

    /* First stat card — dark accent */
    .bento-stat-card:first-child .bento-card {
      background: linear-gradient(135deg, #083E40 0%, #0d5254 100%);
      border-color: transparent;
      color: #fff;
    }
    .bento-stat-card:first-child .bento-card .bento-label { color: rgba(255,255,255,0.7); }
    .bento-stat-card:first-child .bento-card .bento-number { color: #fff; }
    .bento-stat-card:first-child .bento-card .bento-sub { color: rgba(255,255,255,0.6); }
    .bento-stat-card:first-child .bento-card .bento-icon-circle {
      background: rgba(255,255,255,0.15);
      color: #fff;
    }
    .bento-stat-card:first-child .bento-card::before {
      content: '';
      position: absolute;
      top: -40px; right: -40px;
      width: 120px; height: 120px;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
    }

    /* Other stat cards */
    .bento-label {
      font-size: 0.7rem; font-weight: 700; letter-spacing: 0.07em;
      text-transform: uppercase; color: #94a3b8; margin-bottom: 6px;
    }
    .bento-number {
      font-size: 2rem; font-weight: 800; color: #0f172a;
      line-height: 1; margin-bottom: 4px; letter-spacing: -0.03em;
    }
    .bento-sub {
      font-size: 0.76rem; color: #64748b; font-weight: 500;
    }
    .bento-icon-circle {
      width: 40px; height: 40px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; margin-bottom: 12px;
    }
    .bento-icon-circle.purple { background: #f3f0ff; color: #7c3aed; }
    .bento-icon-circle.yellow { background: #fffbeb; color: #d97706; }
    .bento-icon-circle.blue   { background: #eff6ff; color: #2563eb; }

    /* === DEADLINE CARDS === */
    .bento-deadline-card .bento-card {
      border-left: 4px solid transparent;
      cursor: pointer;
      text-decoration: none;
    }
    .bento-deadline-card .bento-card:hover { color: inherit; }
    .bento-deadline-card.dl-green .bento-card { border-left-color: #10b981; }
    .bento-deadline-card.dl-yellow .bento-card { border-left-color: #f59e0b; }
    .bento-deadline-card.dl-red .bento-card { border-left-color: #ef4444; }

    .dl-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
    .dl-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; }
    .dl-green .dl-icon { background: #ecfdf5; color: #10b981; }
    .dl-yellow .dl-icon { background: #fffbeb; color: #f59e0b; }
    .dl-red .dl-icon { background: #fef2f2; color: #ef4444; }
    .dl-title { font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; }
    .dl-number { font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1; letter-spacing: -0.03em; }
    .dl-sub { font-size: 0.73rem; color: #94a3b8; font-weight: 500; margin-top: 4px; }
    .dl-badge { display: inline-block; margin-top: 8px; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; padding: 3px 10px; border-radius: 20px; }
    .dl-green .dl-badge { background: #ecfdf5; color: #059669; }
    .dl-yellow .dl-badge { background: #fffbeb; color: #b45309; }
    .dl-red .dl-badge { background: #fef2f2; color: #dc2626; }

    /* Active filter state */
    .bento-deadline-card .bento-card.active-filter {
      box-shadow: 0 0 0 3px currentColor, 0 4px 20px rgba(8,62,64,0.15);
    }
    .dl-green .bento-card.active-filter { box-shadow: 0 0 0 3px #10b981, 0 4px 20px rgba(16,185,129,0.2); }
    .dl-yellow .bento-card.active-filter { box-shadow: 0 0 0 3px #f59e0b, 0 4px 20px rgba(245,158,11,0.2); }
    .dl-red .bento-card.active-filter { box-shadow: 0 0 0 3px #ef4444, 0 4px 20px rgba(239,68,68,0.2); }

    @media (max-width: 1200px) {
      .bento-stat-card { grid-column: span 6; }
      .bento-deadline-card { grid-column: span 4; }
    }
    @media (max-width: 768px) {
      .bento-stat-card, .bento-deadline-card { grid-column: span 12; }
    }
  </style>

  <div class="bento-info-grid">
    {{-- Card 1: Total Dokumen Agenda (dark) --}}
    <div class="bento-stat-card">
      <div class="bento-card">
        <div class="bento-icon-circle" style="background:rgba(255,255,255,0.15);color:#fff;">
          <i class="fa-solid fa-book-open"></i>
        </div>
        <div class="bento-label">Total Dokumen Agenda</div>
        <div class="bento-number" data-counter="{{ $totalDokumenAgenda ?? 0 }}">0</div>
        <div class="bento-sub">Rp <span data-counter-rupiah="{{ $totalNilaiRupiah ?? 0 }}">0</span></div>
      </div>
    </div>

    {{-- Card 2: Total Dokumen Akutansi --}}
    <div class="bento-stat-card">
      <div class="bento-card">
        <div class="bento-icon-circle purple">
          <i class="fa-solid fa-calculator"></i>
        </div>
        <div class="bento-label">Total Dokumen Akutansi</div>
        <div class="bento-number" data-counter="{{ $totalDokumenAkutansi ?? 0 }}">0</div>
        <div class="bento-sub">Dokumen di akutansi</div>
      </div>
    </div>

    {{-- Card 3: Dokumen Diproses --}}
    <div class="bento-stat-card">
      <div class="bento-card">
        <div class="bento-icon-circle yellow">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div class="bento-label">Dokumen Diproses</div>
        <div class="bento-number" data-counter="{{ $totalDokumenDiproses ?? 0 }}">0</div>
        <div class="bento-sub">Sedang diproses</div>
      </div>
    </div>

    {{-- Card 4: Total Terkirim --}}
    <div class="bento-stat-card">
      <div class="bento-card">
        <div class="bento-icon-circle blue">
          <i class="fa-solid fa-paper-plane"></i>
        </div>
        <div class="bento-label">Total Terkirim</div>
        <div class="bento-number" data-counter="{{ $totalTerkirim ?? 0 }}">0</div>
        <div class="bento-sub">Dikirim ke tahap selanjutnya</div>
      </div>
    </div>

    {{-- Deadline Card: Aman --}}
    @php $currentFilter = request('keterlambatan'); @endphp
    <div class="bento-deadline-card dl-green">
      <a href="{{ route('documents.akutansi.index', array_merge(request()->except(['keterlambatan','page']), $currentFilter === 'aman' ? [] : ['keterlambatan' => 'aman'])) }}"
         class="bento-card text-decoration-none {{ $currentFilter === 'aman' ? 'active-filter' : '' }}">
        <div class="dl-header">
          <div class="dl-icon"><i class="fa-solid fa-shield-halved"></i></div>
          <div class="dl-title">Aman</div>
        </div>
        <div class="dl-number" data-counter="{{ $dokumenLessThan24h ?? 0 }}">0</div>
        <div class="dl-sub">Diterima &lt; 24 jam</div>
        <div class="dl-badge">{{ $currentFilter === 'aman' ? '✓ Aktif' : 'Klik untuk filter' }}</div>
      </a>
    </div>

    {{-- Deadline Card: Peringatan --}}
    <div class="bento-deadline-card dl-yellow">
      <a href="{{ route('documents.akutansi.index', array_merge(request()->except(['keterlambatan','page']), $currentFilter === 'peringatan' ? [] : ['keterlambatan' => 'peringatan'])) }}"
         class="bento-card text-decoration-none {{ $currentFilter === 'peringatan' ? 'active-filter' : '' }}">
        <div class="dl-header">
          <div class="dl-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div class="dl-title">Peringatan</div>
        </div>
        <div class="dl-number" data-counter="{{ $dokumen24to72h ?? 0 }}">0</div>
        <div class="dl-sub">Diterima 1–3 hari lalu</div>
        <div class="dl-badge">{{ $currentFilter === 'peringatan' ? '✓ Aktif' : 'Klik untuk filter' }}</div>
      </a>
    </div>

    {{-- Deadline Card: Terlambat --}}
    <div class="bento-deadline-card dl-red">
      <a href="{{ route('documents.akutansi.index', array_merge(request()->except(['keterlambatan','page']), $currentFilter === 'terlambat' ? [] : ['keterlambatan' => 'terlambat'])) }}"
         class="bento-card text-decoration-none {{ $currentFilter === 'terlambat' ? 'active-filter' : '' }}">
        <div class="dl-header">
          <div class="dl-icon"><i class="fa-solid fa-circle-xmark"></i></div>
          <div class="dl-title">Terlambat</div>
        </div>
        <div class="dl-number" data-counter="{{ $dokumenMoreThan72h ?? 0 }}">0</div>
        <div class="dl-sub">Diterima &gt; 3 hari lalu</div>
        <div class="dl-badge">{{ $currentFilter === 'terlambat' ? '✓ Aktif' : 'Klik untuk filter' }}</div>
      </a>
    </div>
  </div>

  <script>
  (function() {
    function easeOutExpo(t) { return t === 1 ? 1 : 1 - Math.pow(2, -10 * t); }
    function animateCounter(el, target, duration) {
      var start = null;
      function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        el.textContent = Math.floor(easeOutExpo(progress) * target).toLocaleString('id-ID');
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString('id-ID');
      }
      requestAnimationFrame(step);
    }
    function animateRupiah(el, target, duration) {
      var start = null;
      function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var val = Math.floor(easeOutExpo(progress) * target);
        el.textContent = val.toLocaleString('id-ID');
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString('id-ID');
      }
      requestAnimationFrame(step);
    }
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('[data-counter]').forEach(function(el) {
        animateCounter(el, parseInt(el.dataset.counter) || 0, 1400);
      });
      document.querySelectorAll('[data-counter-rupiah]').forEach(function(el) {
        animateRupiah(el, parseFloat(el.dataset.counterRupiah) || 0, 1800);
      });
    });
  })();
  </script>

  {{-- ===== REDESIGNED TABLE SECTION AKUTANSI ===== --}}
  <style>
    /* Override nested-table look — unified single card */
    #documentTableContainer.table-dokumen {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
      border: 1px solid #f1f5f9;
      overflow: hidden;
      padding: 0 !important;
    }
    .dtable-toolbar {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1rem 1.35rem; border-bottom: 1px solid #f1f5f9;
      gap: 1rem; flex-wrap: wrap;
    }
    .dtable-toolbar-left { display: flex; align-items: center; gap: 0.65rem; }
    .dtable-toolbar-icon {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, #083E40 0%, #0a5254 100%);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      color: white; font-size: 0.875rem; flex-shrink: 0;
    }
    .dtable-toolbar-title { font-size: 1rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em; }
    .dtable-toolbar-subtitle { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
    .dtable-toolbar-right { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .dtable-perpage-label { font-size: 0.8125rem; color: #64748b; white-space: nowrap; }
    .dtable-perpage-select {
      padding: 0.35rem 0.6rem; border-radius: 8px; border: 1px solid #e2e8f0;
      font-size: 0.8125rem; color: #334155; background: #f8fafc;
      cursor: pointer; outline: none; transition: border-color 0.2s;
    }
    .dtable-perpage-select:focus { border-color: #083E40; }
    /* Hide old pagination-perpage-top */
    #documentTableContainer .pagination-perpage-top-wrapper { display: none !important; }
    /* thead styling */
    div#documentTableContainer thead tr { background: linear-gradient(135deg, #083E40 0%, #0d5254 100%) !important; }
    div#documentTableContainer table thead th,
    div#documentTableContainer table thead tr th {
      background: linear-gradient(135deg, #083E40 0%, #0d5254 100%) !important;
      color: rgba(255,255,255,0.95) !important;
      font-size: 0.775rem !important; font-weight: 600 !important;
      letter-spacing: 0.04em !important; text-transform: uppercase !important;
      padding: 0.85rem 0.9rem !important; border: none !important; white-space: nowrap !important;
    }
    /* Zebra rows */
    #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(even) { background: #f8fafc; }
    #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(odd) { background: #ffffff; }
    #documentTableContainer .table-enhanced tbody tr.main-row:hover {
      background: linear-gradient(90deg, rgba(8,62,64,0.04) 0%, transparent 100%) !important;
      border-left: 3px solid #083E40;
    }
    #documentTableContainer .table-enhanced td { border-top: 1px solid #f1f5f9 !important; border-bottom: none !important; }
    #documentTableContainer .pagination-wrapper,
    #documentTableContainer nav[aria-label],
    #documentTableContainer .paginate-wrapper { padding: 0.75rem 1rem; border-top: 1px solid #f1f5f9; }
  </style>

  <!-- Tabel Dokumen — Unified Card Design -->
  <div class="table-dokumen" id="documentTableContainer">
    <!-- Unified toolbar: title + per-page selector -->
    <div class="dtable-toolbar">
      <div class="dtable-toolbar-left">
        <div class="dtable-toolbar-icon"><i class="fa-solid fa-file-lines"></i></div>
        <div>
          <div class="dtable-toolbar-title">Daftar Dokumen Team Akutansi</div>
          <div class="dtable-toolbar-subtitle">
            Menampilkan {{ $dokumens->firstItem() ?? 0 }}–{{ $dokumens->lastItem() ?? 0 }} dari {{ number_format($dokumens->total()) }} hasil
          </div>
        </div>
      </div>
      <div class="dtable-toolbar-right">
        <span class="dtable-perpage-label">Baris per halaman:</span>
        @php $currentPerPage = request('per_page', session('akutansi_per_page', 10)); @endphp
        <form method="GET" action="{{ route('documents.akutansi.index') }}" style="display:inline;">
          @foreach(request()->except('per_page', 'page') as $key => $val)
            @if(is_array($val))
              @foreach($val as $v)<input type="hidden" name="{{ $key }}[]" value="{{ $v }}">@endforeach
            @else
              <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
          @endforeach
          <select name="per_page" class="dtable-perpage-select" onchange="this.form.submit()">
            @foreach([10, 25, 50, 100] as $pp)
              <option value="{{ $pp }}" {{ (int)$currentPerPage === $pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
          </select>
        </form>
      </div>
    </div>

    <div class="table-responsive" style="overflow-x:auto;">

      <table class="table table-enhanced mb-0">

        <thead>
          <tr>
            <th class="col-checkbox" style="width: 50px;">
              <input type="checkbox" id="selectAllCheckbox" class="bulk-checkbox" title="Pilih Semua">
            </th>
            <th class="col-number">No</th>
            @foreach($selectedColumns as $col)
              @if($col !== 'status')
                @if($col === 'nomor_agenda')
                  <th class="col-{{ $col }}" onclick="toggleSort('{{ $col }}')"
                    style="cursor:pointer; user-select:none; white-space:nowrap;">
                    {{ $availableColumns[$col] ?? $col }}
                    <span
                      style="display:inline-flex; flex-direction:column; line-height:0.5; margin-left:4px; font-size:10px; vertical-align:middle;">
                      <i class="fas fa-caret-up"
                        style="opacity:{{ (isset($sortColumn) && $sortColumn == $col && isset($sortOrder) && $sortOrder == 'asc') ? '1' : '0.3' }}"></i>
                      <i class="fas fa-caret-down"
                        style="opacity:{{ (isset($sortColumn) && $sortColumn == $col && isset($sortOrder) && $sortOrder == 'desc') ? '1' : '0.3' }}"></i>
                    </span>
                  </th>
                @else
                  <th class="col-{{ $col }}">{{ $availableColumns[$col] ?? $col }}</th>
                @endif
              @endif
            @endforeach
            <th class="col-deadline">Deadline</th>
            <th class="col-status">Status</th>
            <th class="col-action">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            @php
              // Determine if document can be bulk selected (not sent to pembayaran yet)
              $isSentToPembayaranBulk = in_array($dokumen->status, [
                'sent_to_pembayaran',
                'pending_approval_pembayaran',
                'menunggu_di_approve',
                'completed',
                'selesai',
              ]) || $dokumen->status_pembayaran === 'sudah_dibayar';
              $canBulkSelect = !$isSentToPembayaranBulk && $dokumen->current_handler === 'akutansi';
            @endphp
            <tr class="main-row clickable-row {{ $dokumen->lock_status_class }}"
              onclick="handleRowClick(event, {{ $dokumen->id }})" title="{{ $dokumen->lock_status_message }}">
              <td class="col-checkbox" onclick="event.stopPropagation();">
                @if($canBulkSelect)
                  <input type="checkbox" class="doc-checkbox bulk-checkbox" data-id="{{ $dokumen->id }}"
                    data-agenda="{{ $dokumen->nomor_agenda }}">
                @endif
              </td>
              <td class="col-number">
                @php
                  // Jangan tampilkan icon kunci untuk dokumen yang sudah dikirim ke pembayaran
                  $isSentToPembayaran = in_array($dokumen->status, [
                    'sent_to_pembayaran',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve',
                    'completed',
                    'selesai',
                  ]) || $dokumen->status_pembayaran === 'sudah_dibayar';
                  $shouldShowLock = $dokumen->is_locked && !$isSentToPembayaran;
                @endphp
                @if($shouldShowLock)
                  <i class="fa-solid fa-lock text-warning me-1" style="font-size: 0.8em;"
                    title="Terkunci: {{ $dokumen->lock_status_message }}"></i>
                @endif
                {{ $dokumens->firstItem() + $index }}
              </td>
              @foreach($selectedColumns as $col)
                @if($col !== 'status')
                  <td class="col-{{ $col }}">
                    @if($col == 'nomor_agenda')
                      <strong>{{ $dokumen->nomor_agenda }}</strong>
                      <br>
                      <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
                    @elseif($col == 'nomor_spp')
                      <span class="select-text">{{ $dokumen->nomor_spp }}</span>
                    @elseif($col == 'tanggal_masuk')
                      <span
                        class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</span>
                    @elseif($col == 'nilai_rupiah')
                      <strong
                        class="select-text">{{ $dokumen->formatted_nilai_rupiah ?? 'Rp. ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                    @elseif($col == 'nomor_miro')
                      {{ $dokumen->nomor_miro ?? '-' }}
                    @elseif($col == 'tanggal_spp')
                      {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}
                    @elseif($col == 'uraian_spp')
                      <span title="{{ $dokumen->uraian_spp ?? '-' }}"
                        style="display: block; word-wrap: break-word; white-space: normal; overflow-wrap: break-word; line-height: 1.6; width: 100%;">
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
                    @elseif($col == 'jenis_dokumen')
                      {{ $dokumen->jenis_dokumen ?? '-' }}
                    @elseif($col == 'jenis_sub_pekerjaan')
                      {{ $dokumen->jenis_sub_pekerjaan ?? '-' }}
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
                    @elseif($col == 'npwp')
                      {{ $dokumen->npwp ?? '-' }}
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
                    @elseif($col == 'link_dokumen_pajak')
                      @if($dokumen->link_dokumen_pajak)
                        <a href="{{ $dokumen->link_dokumen_pajak }}" target="_blank" rel="noopener noreferrer"
                          title="{{ $dokumen->link_dokumen_pajak }}" style="color: #0d6efd; text-decoration: none;">
                          <i class="fa-solid fa-link me-1"></i>Lihat Dokumen
                        </a>
                      @else
                        -
                      @endif
                    @elseif($col == 'bagian')
                      {{ $dokumen->bagian ?? '-' }}
                    @elseif($col == 'tanggal_paraf')
                      {{ $dokumen->tanggal_paraf ? $dokumen->tanggal_paraf->format('d/m/Y H:i') : '-' }}
                    @elseif($col == 'pemaraf')
                      @if($dokumen->pemaraf)
                        <span
                          style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);color:white;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap;">
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
                        <span
                          class="badge-status {{ $dokumen->status_dokumen_csv == 'Selesai Dibayar' ? 'badge-selesai' : ($dokumen->status_dokumen_csv == 'Dikembalikan' ? 'badge-dikembalikan' : 'badge-proses') }}"
                          style="font-size: 10px; padding: 4px 8px;">
                          {{ $dokumen->status_dokumen_csv }}
                        </span>
                      @else
                        -
                      @endif
                    @elseif($col == 'tanggal_dibayar')
                      {{ $dokumen->tanggal_dibayar ? \Carbon\Carbon::parse($dokumen->tanggal_dibayar)->format('d/m/Y') : '-' }}
                    @else
                      -
                    @endif
                  </td>
                @endif
              @endforeach
              <td class="col-deadline">
                @php
                  // Get received_at from roleData to calculate document age (count up)
                  $roleData = $dokumen->getDataForRole('akutansi');
                  $receivedAt = $roleData?->received_at;

                  // Get pembayaran role data for bypass detection
                  $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');

                  // Check if document BYPASSED Akutansi (Bulk Direct to Payment)
                  // These documents went: Operator -> Team Verifikasi -> Pembayaran
                  // They never entered Akutansi workflow
                  $isBypassedToPaymentDeadline = (
                    $dokumen->current_handler === 'pembayaran' ||
                    $dokumen->status === 'completed' ||
                    $dokumen->status_pembayaran === 'sudah_dibayar' ||
                    ($pembayaranRoleData && $pembayaranRoleData->received_at)
                  ) && !$roleData?->received_at; // Akutansi never received this document

                  // Check if document is already sent to other roles
                  $isSent = in_array($dokumen->status, [
                    'sent_to_pembayaran',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve', // Status setelah dikirim ke pembayaran via sendToInbox
                  ]);

                  // Check if document is completed
                  $isCompleted = in_array($dokumen->status, [
                    'selesai',
                    'completed',
                    'approved_data_sudah_terkirim',
                  ]) || ($dokumen->status_pembayaran === 'sudah_dibayar');

                  // Check if document is returned to verifikasi (deadline paused)
                  $isReturned = $dokumen->status === 'returned_to_verifikasi';

                  // Calculate document age from received_at (count up)
                  $ageText = '-';
                  $ageLabel = '-';
                  $ageColor = 'gray';
                  $ageIcon = 'fa-clock';
                  $ageDays = 0;
                  $timeFrozen = false;

                  if ($receivedAt) {
                    // For sent/completed documents, calculate time from received_at to processed_at (frozen time)
                    // For active documents, calculate time from received_at to now (live time)
                    $processedAt = $roleData?->processed_at;

                    if (($isSent || $isCompleted || $isReturned) && $processedAt) {
                      // Document is sent/completed/returned - freeze the time at processed_at
                      $endTime = \Carbon\Carbon::parse($processedAt);
                      $timeFrozen = true;
                    } else if ($isReturned) {
                      // Returned but no processed_at - freeze at now
                      $endTime = \Carbon\Carbon::now();
                      $timeFrozen = true;
                    } else {
                      // Document is still active - use current time
                      $endTime = \Carbon\Carbon::now();
                    }

                    $diff = $receivedAt->diff($endTime);
                    $ageDays = $diff->days;

                    // Format elapsed time as "X hari Y jam Z menit"
                    $elapsedParts = [];
                    if ($diff->days > 0) {
                      $elapsedParts[] = $diff->days . ' hari';
                    }
                    if ($diff->h > 0) {
                      $elapsedParts[] = $diff->h . ' jam';
                    }
                    if ($diff->i > 0 || empty($elapsedParts)) {
                      $elapsedParts[] = $diff->i . ' menit';
                    }
                    $ageText = implode(' ', $elapsedParts);

                    // Add frozen indicator if time is frozen
                    if ($timeFrozen) {
                      $ageText .= ' ⏸️';
                    }

                    // Determine label and color based on elapsed time (in hours)
                    // Green: < 24 hours, Yellow: 24-72 hours, Red: > 72 hours
                    $totalHours = ($diff->days * 24) + $diff->h;

                    // Calculate label based on actual processing time
                    if ($totalHours >= 72) {
                      $ageLabel = 'TERLAMBAT';
                      $ageIcon = 'fa-times-circle';
                    } elseif ($totalHours >= 24) {
                      $ageLabel = 'PERINGATAN';
                      $ageIcon = 'fa-exclamation-triangle';
                    } else {
                      $ageLabel = 'AMAN';
                      $ageIcon = 'fa-check-circle';
                    }

                    // For sent/completed/returned documents, use grey color
                    // For active documents, use color based on time
                    if ($isSent || $isCompleted || $isReturned) {
                      $ageColor = 'gray';
                    } elseif ($totalHours >= 72) {
                      $ageColor = 'red';
                    } elseif ($totalHours >= 24) {
                      $ageColor = 'yellow';
                    } else {
                      $ageColor = 'green';
                    }
                  }

                  // Determine deadline type: 'active' (masih diproses), 'sent' (sudah terkirim), 'completed' (selesai), 'paused' (dikembalikan)
                  $deadlineType = 'active';
                  if ($isReturned) {
                    $deadlineType = 'paused';
                  } elseif ($isCompleted) {
                    $deadlineType = 'completed';
                  } elseif ($isSent) {
                    $deadlineType = 'sent';
                  }
                @endphp
                @if($receivedAt)
                  <div class="deadline-card deadline-{{ $deadlineType }} deadline-{{ $ageColor }}"
                    data-received-at="{{ $receivedAt->format('Y-m-d H:i:s') }}" data-age-days="{{ $ageDays }}"
                    data-sent="{{ $isSent ? 'true' : 'false' }}" data-completed="{{ $isCompleted ? 'true' : 'false' }}">
                    <div class="deadline-time">
                      <i class="fa-solid fa-calendar"></i>
                      <span>{{ $receivedAt->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="deadline-indicator deadline-{{ $ageColor }}">
                      <i class="fa-solid {{ $ageIcon }}"></i>
                      <span class="status-text">{{ $ageLabel }}</span>
                    </div>
                    <div class="deadline-age" style="font-size: 10px; color: #6b7280; margin-top: 4px;">
                      <i class="fa-solid fa-hourglass-half"></i>
                      <span>{{ $ageText }}</span>
                    </div>
                    @if($isReturned)
                      <div class="deadline-paused-label">
                        <i class="fa-solid fa-pause-circle"></i> Berhenti Sementara
                      </div>
                    @elseif($isSent)
                      <div class="deadline-label" style="font-size: 8px; color: #6b7280; margin-top: 4px; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Terkirim
                      </div>
                    @elseif($isCompleted)
                      <div class="deadline-label" style="font-size: 8px; color: #6b7280; margin-top: 4px; font-weight: 600;">
                        <i class="fa-solid fa-check-circle"></i> Selesai
                      </div>
                    @endif
                  </div>
                @elseif($isBypassedToPaymentDeadline)
                  {{-- Document bypassed Akutansi - show proper deadline card like Team Verifikasi --}}
                  @php
                    $bypassVerifikasiData = $dokumen->getDataForRole('team_verifikasi');

                    // Priority: pembayaran received_at > verifikasi processed_at > document tanggal_masuk
                    $bypassTimestamp = $pembayaranRoleData?->received_at
                      ?? $bypassVerifikasiData?->processed_at
                      ?? $dokumen->tanggal_masuk;

                    $bypassAgeText = '0 menit';
                    $bypassAgeLabel = 'AMAN';
                    $bypassAgeColor = 'gray';
                    $bypassAgeIcon = 'fa-check-circle';
                    $bypassAgeDays = 0;

                    if ($bypassTimestamp) {
                      $bypassStartTime = $bypassTimestamp instanceof \Carbon\Carbon
                        ? $bypassTimestamp
                        : \Carbon\Carbon::parse($bypassTimestamp);

                      // ALWAYS freeze time at processed_at (like Team Verifikasi)
                      $bypassProcessedAt = $bypassVerifikasiData?->processed_at ?? $pembayaranRoleData?->received_at;
                      if ($bypassProcessedAt) {
                        $bypassEndTime = $bypassProcessedAt instanceof \Carbon\Carbon
                          ? $bypassProcessedAt
                          : \Carbon\Carbon::parse($bypassProcessedAt);
                      } else {
                        $bypassEndTime = $bypassStartTime;
                      }

                      $bypassDiff = $bypassStartTime->diff($bypassEndTime);
                      $bypassAgeDays = $bypassDiff->days;

                      $bypassElapsedParts = [];
                      if ($bypassDiff->days > 0)
                        $bypassElapsedParts[] = $bypassDiff->days . ' hari';
                      if ($bypassDiff->h > 0)
                        $bypassElapsedParts[] = $bypassDiff->h . ' jam';
                      if ($bypassDiff->i > 0 || empty($bypassElapsedParts))
                        $bypassElapsedParts[] = $bypassDiff->i . ' menit';
                      $bypassAgeText = implode(' ', $bypassElapsedParts) . ' ⏸️';

                      $bypassTotalHours = ($bypassDiff->days * 24) + $bypassDiff->h;
                      if ($bypassTotalHours >= 72) {
                        $bypassAgeLabel = 'TERLAMBAT';
                        $bypassAgeIcon = 'fa-times-circle';
                      } elseif ($bypassTotalHours >= 24) {
                        $bypassAgeLabel = 'PERINGATAN';
                        $bypassAgeIcon = 'fa-exclamation-triangle';
                      } else {
                        $bypassAgeLabel = 'AMAN';
                        $bypassAgeIcon = 'fa-check-circle';
                      }
                    }
                  @endphp
                  @if($bypassTimestamp)
                    @php
                      $bypassDisplayTime = $bypassTimestamp instanceof \Carbon\Carbon
                        ? $bypassTimestamp
                        : \Carbon\Carbon::parse($bypassTimestamp);
                    @endphp
                    <div class="deadline-card deadline-sent deadline-{{ $bypassAgeColor }}"
                      data-received-at="{{ $bypassDisplayTime->format('Y-m-d H:i:s') }}" data-age-days="{{ $bypassAgeDays }}"
                      data-sent="true" data-completed="false">
                      <div class="deadline-time">
                        <i class="fa-solid fa-calendar"></i>
                        <span>{{ $bypassDisplayTime->format('d M Y, H:i') }}</span>
                      </div>
                      <div class="deadline-indicator deadline-{{ $bypassAgeColor }}">
                        <i class="fa-solid {{ $bypassAgeIcon }}"></i>
                        <span class="status-text">{{ $bypassAgeLabel }}</span>
                      </div>
                      <div class="deadline-age" style="font-size: 10px; color: #6b7280; margin-top: 4px;">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>{{ $bypassAgeText }}</span>
                      </div>
                      <div class="deadline-label" style="font-size: 8px; color: #6b7280; margin-top: 4px; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Terkirim
                      </div>
                    </div>
                  @else
                    <div class="deadline-card deadline-sent deadline-gray">
                      <div class="deadline-label" style="font-size: 10px; color: #6b7280; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Terkirim ke Pembayaran
                      </div>
                    </div>
                  @endif
                @else
                  <div class="no-deadline">
                    <i class="fa-solid fa-clock"></i>
                    <span>Belum diterima</span>
                  </div>
                @endif
              </td>
              <td class="col-status" style="text-align: center;" onclick="event.stopPropagation()">
                @php
                  // Check if document is rejected by akutansi or pembayaran
                  $isRejected = $dokumen->roleStatuses()
                    ->whereIn('role_code', ['akutansi', 'pembayaran'])
                    ->where('status', 'rejected')
                    ->exists();

                  // FIX: Akutansi needs to see ACTUAL workflow state (like Perpajakan)
                  // - If document is in Pembayaran inbox (pending) → "Menunggu Approval dari Pembayaran"
                  // - If document was approved by Pembayaran → "Terkirim ke Pembayaran"
                  $akutansiRoleData = $dokumen->getDataForRole('akutansi');
                  $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');

                  // Check if Pembayaran has APPROVED the document (not just pending)
                  $pembayaranHasApproved = $dokumen->roleStatuses()
                    ->where('role_code', 'pembayaran')
                    ->where('status', 'approved')
                    ->exists();

                  // Check if document is PENDING in Pembayaran inbox
                  $pembayaranIsPending = $dokumen->roleStatuses()
                    ->where('role_code', 'pembayaran')
                    ->where('status', 'pending')
                    ->exists();

                  // Check if document BYPASSED Akutansi (Bulk Direct to Payment)
                  // These documents went: Operator -> Team Verifikasi -> Pembayaran
                  // They never entered Akutansi workflow
                  $isBypassedToPayment = (
                    $dokumen->current_handler === 'pembayaran' ||
                    $dokumen->status === 'completed' ||
                    $dokumen->status_pembayaran === 'sudah_dibayar' ||
                    ($pembayaranRoleData && $pembayaranRoleData->received_at)
                  ) && !$akutansiRoleData?->received_at; // Akutansi never received this document

                  // Document is truly sent from akutansi if pembayaran has APPROVED (not just pending)
                  $sentFromAkutansi = (
                    $isBypassedToPayment ||
                    $pembayaranHasApproved ||
                    ($pembayaranRoleData && $pembayaranRoleData->received_at && !$pembayaranIsPending)
                  ) && !$isRejected;
                @endphp
                @if($isRejected)
                  {{-- Dokumen ditolak oleh akutansi --}}
                  <span class="badge-status badge-dikembalikan" style="position: relative;">
                    <i class="fa-solid fa-times-circle me-1"></i>
                    <span>Dokumen ditolak,
                      <a href="{{ route('returns.akutansi.index') }}?search={{ $dokumen->nomor_agenda }}"
                        class="text-white text-decoration-underline fw-bold" onclick="event.stopPropagation();"
                        style="color: #fff !important; text-decoration: underline !important; font-weight: 600 !important;">
                        cek disini
                      </a>
                    </span>
                  </span>
                @elseif($pembayaranIsPending)
                  {{-- FIX: Document is in Pembayaran inbox waiting approval --}}
                  <span class="badge-status badge-warning">
                    <i class="fa-solid fa-clock me-1"></i>
                    Menunggu Approval dari Pembayaran
                  </span>
                @elseif($sentFromAkutansi)
                  {{-- Document has been APPROVED by Pembayaran (not just pending) --}}
                  <span class="badge-status badge-sent">📤 Terkirim ke Pembayaran</span>
                @elseif($dokumen->status == 'sent_to_pembayaran' && !$pembayaranIsPending)
                  <span class="badge-status badge-sent">📤 Terkirim ke Pembayaran</span>
                @elseif($dokumen->is_locked)
                  <span class="badge-status badge-locked">🔒 Terkunci</span>
                @elseif($dokumen->status == 'selesai')
                  <span class="badge-status badge-selesai">✓ Selesai</span>
                @elseif($dokumen->status == 'returned_to_verifikasi')
                  <span class="badge-status badge-dikembalikan">← Dikembalikan ke Verifikasi</span>
                @elseif($dokumen->current_handler == 'akutansi' && !in_array($dokumen->status, ['sent_to_pembayaran', 'selesai', 'completed', 'menunggu_di_approve', 'pending_approval_pembayaran']))
                  {{-- Dokumen yang sedang ditangani akutansi dan bukan status khusus --}}
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                @elseif($dokumen->status == 'sent_to_akutansi' && $dokumen->current_handler != 'akutansi')
                  {{-- Dokumen yang baru dikirim ke akutansi dan belum diproses --}}
                  <span class="badge-status badge-belum">⏳ Belum Diproses</span>
                @elseif(in_array($dokumen->status, ['returned_to_operator', 'returned_to_department', 'dikembalikan']))
                  <span class="badge-status badge-dikembalikan">← Dikembalikan</span>
                @elseif($dokumen->status == 'completed')
                  <span class="badge-status badge-selesai">✓ Selesai - Sudah Dibayar</span>
                @else
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                @endif
              </td>
              <td class="col-action" onclick="event.stopPropagation()">
                @if(!$dokumen->is_at_my_role)
                  {{-- Cross-role visibility: document not yet at Akutansi --}}
                  @php
                    $handlerLabel = match ($dokumen->current_handler) {
                      'operator' => 'Operator',
                      'bidang' => 'Bidang',
                      'team_verifikasi' => 'Verifikasi',
                      'perpajakan' => 'Perpajakan',
                      'akutansi' => 'Akutansi',
                      'pembayaran' => 'Pembayaran',
                      default => ucfirst(str_replace('_', ' ', $dokumen->current_handler ?? 'Unknown')),
                    };
                  @endphp
                  <div class="action-buttons-hybrid" style="display: flex; align-items: center; justify-content: center;">
                    <span class="badge-status badge-proses" style="font-size: 11px; padding: 6px 12px; white-space: nowrap;">
                      <i class="fa-solid fa-hourglass-half me-1"></i>
                      Di {{ $handlerLabel }}
                    </span>
                  </div>
                @else
                  <div class="action-buttons-hybrid">
                    @php
                      $isSentToPembayaran = in_array($dokumen->status, [
                        'sent_to_pembayaran',
                        'pending_approval_pembayaran',
                        'menunggu_di_approve', // Status setelah dikirim ke pembayaran via sendToInbox
                        'completed',
                        'selesai',
                      ]) || $dokumen->status_pembayaran === 'sudah_dibayar' || $isBypassedToPayment;
                    @endphp
                    <!-- Unlocked state - buttons enabled -->
                    @if($dokumen->status == 'returned_to_verifikasi')
                      {{-- Document returned to verifikasi - disable all actions --}}
                      <button class="btn-action btn-edit locked btn-full-width" disabled
                        title="Dokumen dikembalikan ke Verifikasi">
                        <i class="fa-solid fa-undo"></i>
                        <span>Dikembalikan</span>
                      </button>
                    @elseif(!$isSentToPembayaran)
                      <!-- Tombol Kirim Data - selalu muncul untuk dokumen yang tidak terkunci dan belum terkirim -->
                      <button type="button" class="btn-action btn-send btn-full-width"
                        onclick="sendToPembayaran({{ $dokumen->id }})" title="Kirim ke Team Pembayaran">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Data</span>
                      </button>
                      <div class="action-row">
                        @if($dokumen->can_edit)
                          <a href="{{ route('documents.akutansi.edit', $dokumen->id) }}" title="Edit Dokumen"
                            style="flex: 1; text-decoration: none;">
                            <button class="btn-action btn-edit" style="width: 100%;">
                              <i class="fa-solid fa-pen"></i>
                              <span>Edit</span>
                            </button>
                          </a>
                        @endif
                        @if($dokumen->can_edit)
                          <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                            onclick="openReturnModal({{ $dokumen->id }})" title="Kembalikan Dokumen ke Team Verifikasi">
                            <i class="fa-solid fa-undo"></i>
                            <span>Balik</span>
                          </button>
                        @endif
                      </div>
                    @else
                      <!-- Dokumen sudah terkirim - tampilkan status Terkirim konsisten -->
                      <button type="button" class="btn-action btn-sent btn-full-width" disabled>
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Terkirim</span>
                      </button>
                    @endif
                  </div>
                @endif
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

  @if(isset($dokumens) && $dokumens->total() > 0)
    @include('partials.pagination-enhanced', ['paginator' => $dokumens])
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

          <div class="alert alert-info border-0"
            style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.12) 0%, rgba(255, 140, 0, 0.12) 100%); border-left: 4px solid #ffc107;">
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
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Catatan (opsional)</label>
            <textarea class="form-control" id="deadlineNote" rows="3" maxlength="500"
              placeholder="Contoh: Menunggu kelengkapan dokumen pendukung"></textarea>
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

  <!-- Modal for Return to Team Verifikasi -->
  <div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
          <h5 class="modal-title">
            <i class="fa-solid fa-undo me-2"></i>Kembalikan Dokumen ke Team Verifikasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="returnDocId">

          <div class="alert alert-warning border-0"
            style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(200, 35, 51, 0.1) 100%); border-left: 4px solid #dc3545;">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            <strong>Perhatian:</strong> Dokumen akan dikembalikan ke Team Verifikasi dan akan muncul di halaman
            pengembalian dokumen. Pastikan Anda telah mengisi alasan pengembalian dengan jelas.
          </div>

          <div class="form-group mb-3">
            <label for="returnReason" class="form-label">
              <strong>Alasan Pengembalian <span class="text-danger">*</span></strong>
            </label>
            <textarea class="form-control" id="returnReason" rows="4"
              placeholder="Jelaskan kenapa dokumen ini dikembalikan ke Team Verifikasi..." maxlength="500"
              required></textarea>
            <div class="form-text">
              <small class="text-muted">Mohon isi alasan pengembalian secara detail dan jelas.</small><br>
              <span id="returnCharCount">0</span>/500 karakter
            </div>
          </div>

          <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Dokumen yang dikembalikan akan:
            <ul class="mb-0 mt-2">
              <li>Muncul di halaman "Pengembalian Dokumen Team Verifikasi"</li>
              <li>Muncul di halaman "Pengembalian Dokumen Team Akutansi"</li>
              <li>Tetap terlihat di daftar dokumen akutansi dengan status "Dikembalikan ke Verifikasi"</li>
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
  <div class="modal fade" id="returnConfirmationModal" tabindex="-1" aria-labelledby="returnConfirmationModalLabel"
    aria-hidden="true">
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
          <h5 class="fw-bold mb-3 text-center">Apakah Anda yakin ingin mengembalikan dokumen ini ke Team Verifikasi?</h5>
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
            Dokumen akan dikembalikan ke Team Verifikasi dan akan muncul di halaman pengembalian dokumen.
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
  <div class="modal fade" id="returnSuccessModal" tabindex="-1" aria-labelledby="returnSuccessModalLabel"
    aria-hidden="true">
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
          <h5 class="fw-bold mb-3">Dokumen berhasil dikembalikan ke Team Verifikasi!</h5>
          <p class="text-muted mb-0">
            Dokumen akan muncul di:
            <br>• Halaman "Pengembalian Dokumen Team Verifikasi"
            <br>• Halaman "Pengembalian Dokumen Team Akutansi"
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
  <div class="modal fade" id="returnValidationWarningModal" tabindex="-1"
    aria-labelledby="returnValidationWarningModalLabel" aria-hidden="true">
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

  <!-- Modal for Send to Pembayaran -->
  <div class="modal fade" id="sendToPembayaranModal" tabindex="-1" aria-labelledby="sendToPembayaranModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
          <h5 class="modal-title" id="sendToPembayaranModalLabel">
            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Dokumen ke Bidang Berikutnya
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="sendToPembayaranDocId">

          <!-- Information Box -->
          <div class="alert alert-info border-0 mb-4"
            style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%); border-left: 4px solid #0d6efd;">
            <div class="d-flex align-items-start">
              <i class="fa-solid fa-circle-info me-2 mt-1" style="color: #0d6efd; font-size: 18px;"></i>
              <div>
                <strong>Catatan:</strong> Deadline akan ditetapkan oleh departemen tujuan (Team Pembayaran) setelah
                dokumen diterima.
              </div>
            </div>
          </div>

          <!-- Destination Selection -->
          <div class="mb-4">
            <label class="form-label fw-bold mb-3">
              <i class="fa-solid fa-paper-plane me-2"></i>Pilih Tujuan Pengiriman:
            </label>

            <!-- Team Pembayaran Option -->
            <div class="card border-2 mb-3"
              style="border-color: #083E40 !important; cursor: pointer; transition: all 0.3s ease;"
              onclick="selectDestination('pembayaran')" id="destination-pembayaran">
              <div class="card-body p-3">
                <div class="form-check d-flex align-items-start">
                  <input class="form-check-input mt-2 me-3" type="radio" name="destination" id="radioPembayaran"
                    value="pembayaran" checked>
                  <div class="flex-grow-1">
                    <label class="form-check-label fw-bold" for="radioPembayaran" style="cursor: pointer;">
                      <i class="fa-solid fa-credit-card me-2" style="color: #083E40;"></i>Team Pembayaran
                    </label>
                    <p class="text-muted mb-0 mt-2 small">
                      Untuk dokumen yang siap untuk diproses pembayaran. Status akan berubah menjadi "Siap Bayar" di
                      halaman pembayaran.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-success" onclick="confirmSendToPembayaran()"
            style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
            <i class="fa-solid fa-paper-plane me-2"></i>Kirim
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Send Success -->
  <div class="modal fade" id="sendToPembayaranSuccessModal" tabindex="-1"
    aria-labelledby="sendToPembayaranSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
          <h5 class="modal-title" id="sendToPembayaranSuccessModalLabel">
            <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #28a745;"></i>
          </div>
          <h5 class="fw-bold mb-3">Dokumen berhasil dikirim ke Team Pembayaran!</h5>
          <p class="text-muted mb-0">
            Dokumen sekarang akan muncul di inbox Team Pembayaran dan menunggu persetujuan.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal" onclick="location.reload()">
            <i class="fa-solid fa-check me-2"></i>Selesai
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Error -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
          <h5 class="modal-title" id="errorModalLabel">
            <i class="fa-solid fa-exclamation-circle me-2"></i>Terjadi Kesalahan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 52px; color: #dc3545;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="errorModalTitle">Error</h5>
          <p class="text-muted mb-0 text-center" id="errorModalMessage">
            Terjadi kesalahan yang tidak diketahui.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Warning -->
  <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
          <h5 class="modal-title" id="warningModalLabel">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>Peringatan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 52px; color: #ffc107;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="warningModalTitle">Peringatan</h5>
          <p class="text-muted mb-0 text-center" id="warningModalMessage">
            Peringatan.
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

  <!-- Modal for Info -->
  <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white;">
          <h5 class="modal-title" id="infoModalLabel">
            <i class="fa-solid fa-circle-info me-2"></i>Informasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-circle-info" style="font-size: 52px; color: #0d6efd;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="infoModalTitle">Informasi</h5>
          <p class="text-muted mb-0 text-center" id="infoModalMessage">
            Informasi.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-check me-2"></i>Mengerti
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Confirmation -->
  <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
          <h5 class="modal-title" id="confirmationModalLabel">
            <i class="fa-solid fa-question-circle me-2"></i>Konfirmasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-question-circle" style="font-size: 52px; color: #083E40;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="confirmationModalTitle">Konfirmasi</h5>
          <p class="text-muted mb-0 text-center" id="confirmationModalMessage">
            Apakah Anda yakin?
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center gap-2">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" id="confirmationModalCancel">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-success px-4" id="confirmationModalConfirm"
            style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
            <i class="fa-solid fa-check me-2"></i>Ya
          </button>
        </div>
      </div>
    </div>
  </div>

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

            <script>
            // Wrapper function untuk handle row click dengan text selection check
            function handleRowClick(event, docId) {
              // Cek apakah user sedang menyeleksi teks
              const selection = window.getSelection();
              const selectedText = selection.toString().trim();

              if (selectedText.length > 0) {
                // User sedang menyeleksi teks, jangan toggle detail
                event.preventDefault();
                event.stopPropagation();
                return false;
              }

              // Cek apakah yang diklik adalah link/tombol/input/select/textarea
              const target = event.target;
              const tagName = target.tagName.toLowerCase();
              const isInteractiveElement = 
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
                // User klik elemen interaktif, biarkan default behavior
                return true;
              }

              // Buka modal view detail dokumen
              openViewDocumentModal(docId);
              return true;
            }

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

              fetch(`/documents/akutansi/${docId}/detail`)
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
              window.location.href = `/documents/akutansi/${id}/edit`;
            }

            function sendToPembayaran(id) {
              // Set document ID in modal
              document.getElementById('sendToPembayaranDocId').value = id;

              // Reset radio button selection
              document.getElementById('radioPembayaran').checked = true;

              // Reset and highlight pembayaran card
              const pembayaranCard = document.getElementById('destination-pembayaran');
              if (pembayaranCard) {
                pembayaranCard.style.borderColor = '#083E40';
                pembayaranCard.style.boxShadow = '0 2px 8px rgba(8, 62, 64, 0.2)';
                pembayaranCard.style.backgroundColor = 'rgba(8, 62, 64, 0.05)';
              }

              // Open modal
              const modal = new bootstrap.Modal(document.getElementById('sendToPembayaranModal'));
              modal.show();

              // Reset card styling when modal is closed
              const modalEl = document.getElementById('sendToPembayaranModal');
              modalEl.addEventListener('hidden.bs.modal', function() {
                if (pembayaranCard) {
                  pembayaranCard.style.backgroundColor = '';
                }
              }, { once: true });
            }

            function selectDestination(destination) {
              // Update radio button
              if (destination === 'pembayaran') {
                document.getElementById('radioPembayaran').checked = true;

                // Update card styling to show selected state
                const pembayaranCard = document.getElementById('destination-pembayaran');
                pembayaranCard.style.borderColor = '#083E40';
                pembayaranCard.style.boxShadow = '0 2px 8px rgba(8, 62, 64, 0.2)';
                pembayaranCard.style.backgroundColor = 'rgba(8, 62, 64, 0.05)';
              }
            }

            function confirmSendToPembayaran() {
              const docId = document.getElementById('sendToPembayaranDocId').value;
              const selectedDestination = document.querySelector('input[name="destination"]:checked');

              if (!selectedDestination) {
                showWarningModal('Peringatan', 'Pilih tujuan pengiriman terlebih dahulu!');
                return;
              }

              if (selectedDestination.value !== 'pembayaran') {
                showWarningModal('Peringatan', 'Hanya dapat mengirim ke Team Pembayaran dari halaman Akutansi.');
                return;
              }

              // Disable button and show loading
              const confirmBtn = document.querySelector('[onclick="confirmSendToPembayaran()"]');
              const originalHTML = confirmBtn.innerHTML;
              confirmBtn.disabled = true;
              confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

              // Close modal
              const modal = bootstrap.Modal.getInstance(document.getElementById('sendToPembayaranModal'));
              modal.hide();

              // Send request
              fetch(`/documents/akutansi/${docId}/send-to-pembayaran`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              })
              .then(response => {
                // Check if response is ok
                if (!response.ok) {
                  return response.json().then(data => {
                    throw new Error(data.message || 'Terjadi kesalahan saat mengirim dokumen.');
                  });
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  // Show success modal
                  const successModal = new bootstrap.Modal(document.getElementById('sendToPembayaranSuccessModal'));
                  successModal.show();
                } else {
                  showErrorModal('Gagal Mengirim Dokumen', data.message || 'Gagal mengirim dokumen ke Pembayaran.');
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = originalHTML;
                }
              })
              .catch(error => {
                console.error('Error:', error);
                showErrorModal('Terjadi Kesalahan', error.message || 'Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalHTML;
              });
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

            function openReturnToPerpajakanModal(docId) {
              showConfirmationModal(
                'Konfirmasi Pengembalian',
                'Apakah Anda yakin ingin mengembalikan dokumen ini ke Team Perpajakan?',
                function() {
                  // TODO: Implement return to perpajakan functionality
                  showInfoModal('Informasi', 'Fungsi return ke Perpajakan akan segera diimplementasikan.');
                }
              );
            }

            function confirmSetDeadline() {
              const docId = document.getElementById('deadlineDocId').value;
              const deadlineDays = document.getElementById('deadlineDays').value;
              const deadlineNote = document.getElementById('deadlineNote').value;

              if (!deadlineDays) {
                showWarningModal('Pilih periode deadline terlebih dahulu!');
                return;
              }

              if (deadlineDays < 1 || deadlineDays > 3) {
                showWarningModal('Periode deadline harus antara 1-3 hari!');
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

              console.log('Sending request to: ', `/documents/akutansi/${docId}/set-deadline`);
              console.log('Request payload: ', {
                deadline_days: deadlineDaysInt,
                deadline_note: deadlineNote
              });

              fetch(`/documents/akutansi/${docId}/set-deadline`, {
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
                  showErrorModal('Gagal Menetapkan Deadline', data.message || 'Terjadi kesalahan yang tidak diketahui');
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = originalHTML;
                }
              })
              .catch(error => {
                console.error('Error:', error);
                console.error('Error details:', error.message);
                showErrorModal('Terjadi Kesalahan', 'Terjadi kesalahan saat menetapkan deadline: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
              });
            }
            // Helper Functions for Modal Alerts
            function showErrorModal(title, message) {
              const modal = document.getElementById('errorModal');
              const titleEl = document.getElementById('errorModalTitle');
              const messageEl = document.getElementById('errorModalMessage');

              titleEl.textContent = title || 'Terjadi Kesalahan';
              messageEl.textContent = message || 'Terjadi kesalahan yang tidak diketahui.';

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
            }

            function showWarningModal(title, message) {
              const modal = document.getElementById('warningModal');
              const titleEl = document.getElementById('warningModalTitle');
              const messageEl = document.getElementById('warningModalMessage');

              titleEl.textContent = title || 'Peringatan';
              messageEl.textContent = message || 'Peringatan.';

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
            }

            function showInfoModal(title, message) {
              const modal = document.getElementById('infoModal');
              const titleEl = document.getElementById('infoModalTitle');
              const messageEl = document.getElementById('infoModalMessage');

              titleEl.textContent = title || 'Informasi';
              messageEl.textContent = message || 'Informasi.';

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
            }

            function showConfirmationModal(title, message, onConfirm, onCancel) {
              const modal = document.getElementById('confirmationModal');
              const titleEl = document.getElementById('confirmationModalTitle');
              const messageEl = document.getElementById('confirmationModalMessage');
              const confirmBtn = document.getElementById('confirmationModalConfirm');
              const cancelBtn = document.getElementById('confirmationModalCancel');

              titleEl.textContent = title || 'Konfirmasi';
              messageEl.textContent = message || 'Apakah Anda yakin?';

              // Remove existing event listeners by cloning
              const newConfirmBtn = confirmBtn.cloneNode(true);
              confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

              const newCancelBtn = cancelBtn.cloneNode(true);
              cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

              // Add new event listeners
              newConfirmBtn.addEventListener('click', function() {
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                bootstrapModal.hide();
                if (onConfirm && typeof onConfirm === 'function') {
                  onConfirm();
                }
              });

              newCancelBtn.addEventListener('click', function() {
                if (onCancel && typeof onCancel === 'function') {
                  onCancel();
                }
              });

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
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

              // Get sent and completed status from data attributes
              const isSent = card.dataset.sent === 'true';
              const isCompleted = card.dataset.completed === 'true';

              // Remove existing status classes
              card.classList.remove('deadline-safe', 'deadline-warning', 'deadline-danger', 'deadline-overdue', 'deadline-sent', 'deadline-completed');

              // Find status indicator
              const statusIndicator = card.querySelector('.deadline-indicator');
              const statusText = card.querySelector('.status-text');
              if (!statusText) {
                console.error('Status text not found in card:', card);
                return;
              }
              const statusIcon = statusIndicator.querySelector('i');
              if (!statusIcon) {
                console.error('Status icon not found in card:', card);
                return;
              }

              // Remove existing late info and time hints
              const existingLateInfo = card.querySelector('.late-info');
              const existingTimeHint = card.querySelector('div[style*="margin-top: 2px"]');
              const existingProgress = card.querySelector('.deadline-progress');

              if (existingLateInfo) existingLateInfo.remove();
              if (existingTimeHint) existingTimeHint.remove();
              if (existingProgress) existingProgress.remove();

              // Handle completed documents - show as completed (green, no countdown)
              if (isCompleted) {
                card.classList.add('deadline-completed');
                statusText.textContent = 'SELESAI';
                statusIcon.className = 'fa-solid fa-check-circle';
                statusIndicator.className = 'deadline-indicator deadline-completed';
                return; // Don't show countdown for completed documents
              }

              // Handle sent documents - show as sent (gray, no countdown, no overdue)
              if (isSent) {
                card.classList.add('deadline-sent');
                statusText.textContent = 'TERKIRIM';
                statusIcon.className = 'fa-solid fa-paper-plane';
                statusIndicator.className = 'deadline-indicator deadline-sent';
                return; // Don't show countdown or overdue for sent documents
              }

              // Handle active documents (still being processed) - show countdown
              if (diffMs < 0) {
                // Overdue state - only for active documents
                card.classList.add('deadline-overdue');

                // Calculate how late
                const diffHours = Math.abs(Math.floor(diffMs / (1000 * 60 * 60)));
                const diffDays = Math.abs(Math.floor(diffMs / (1000 * 60 * 60 * 24)));

                // Update status text
                statusText.textContent = 'TERLAMBAT';
                statusIcon.className = 'fa-solid fa-exclamation-triangle';
                statusIndicator.className = 'deadline-indicator deadline-overdue';

                // Show late info for active documents
                let lateText;
                if (diffDays >= 1) {
                  lateText = `${diffDays} HARI TELAT`;
                } else if (diffHours >= 1) {
                  lateText = `${diffHours} JAM TELAT`;
                } else {
                  lateText = 'BARU SAJA TELAT';
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
                // Time remaining - only for active documents
                const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                const diffMinutes = Math.floor(diffMs / (1000 * 60));
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                // Get original deadline_days from data attribute
                const deadlineDays = parseInt(card.dataset.deadlineDays) || null;

                // Calculate remaining hours for more accurate display
                const totalHoursRemaining = Math.floor(diffMs / (1000 * 60 * 60));

                // Determine display text based on deadline_days and remaining time
                let displayText = '';
                let shouldShowDays = false;

                if (deadlineDays && deadlineDays > 0) {
                  // If original deadline was set for X days, show "X hari lagi" 
                  // as long as we're still within that period
                  if (totalHoursRemaining >= 12) {
                    if (deadlineDays === 1) {
                      // For 1 day deadline, show "1 hari lagi" if >= 12 hours remaining
                      displayText = '1 hari lagi';
                      shouldShowDays = true;
                    } else {
                      // For 2+ days deadline, show actual days remaining
                      const fullDaysRemaining = Math.floor(totalHoursRemaining / 24);
                      const daysToShow = Math.min(Math.max(1, fullDaysRemaining + (totalHoursRemaining % 24 >= 12 ? 1 : 0)), deadlineDays);
                      displayText = `${daysToShow} ${daysToShow === 1 ? 'hari' : 'hari'} lagi`;
                      shouldShowDays = daysToShow >= 1;
                    }
                  } else {
                    // Less than 12 hours remaining, show hours
                    displayText = `${diffHours} ${diffHours === 1 ? 'jam' : 'jam'} lagi`;
                    shouldShowDays = false;
                  }
                } else {
                  // No deadline_days info, use standard calculation
                  if (diffDays >= 1) {
                    displayText = `${diffDays} ${diffDays === 1 ? 'hari' : 'hari'} lagi`;
                    shouldShowDays = true;
                  } else if (diffHours >= 1) {
                    displayText = `${diffHours} ${diffHours === 1 ? 'jam' : 'jam'} lagi`;
                    shouldShowDays = false;
                  } else {
                    displayText = `${diffMinutes} menit lagi`;
                    shouldShowDays = false;
                  }
                }

                // Simplified 3-status logic: >= 1 hari = hijau, < 1 hari = kuning
                if (shouldShowDays || diffDays >= 1) {
                  // Safe (>= 1 day or still within original deadline period) - Green
                  card.classList.add('deadline-safe');
                  statusText.textContent = 'AMAN';
                  statusIcon.className = 'fa-solid fa-check-circle';
                  statusIndicator.className = 'deadline-indicator deadline-safe';

                  // Add time remaining hint
                  const timeHint = document.createElement('div');
                  timeHint.style.cssText = 'font-size: 8px; color: #065f46; margin-top: 2px; font-weight: 600;';
                  timeHint.textContent = displayText;
                  card.appendChild(timeHint);

                } else if (diffHours >= 1 || diffMinutes >= 1) {
                  // Warning (< 1 day or less than 12 hours remaining) - Yellow
                  card.classList.add('deadline-warning');
                  statusText.textContent = 'DEKAT';
                  statusIcon.className = 'fa-solid fa-exclamation-triangle';
                  statusIndicator.className = 'deadline-indicator deadline-warning';

                  // Add time remaining hint
                  const timeHint = document.createElement('div');
                  timeHint.style.cssText = 'font-size: 8px; color: #92400e; margin-top: 2px; font-weight: 700;';
                  timeHint.textContent = displayText;
                  if (diffMinutes < 60) {
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

              // Character counter for return reason
              const returnReasonTextarea = document.getElementById('returnReason');
              const returnCharCount = document.getElementById('returnCharCount');
              if (returnReasonTextarea && returnCharCount) {
                returnReasonTextarea.addEventListener('input', function() {
                  const length = this.value.length;
                  returnCharCount.textContent = length;
                  if (length > 500) {
                    returnCharCount.style.color = '#dc3545';
                  } else {
                    returnCharCount.style.color = '#6c757d';
                  }
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

            // Confirm Return
            function confirmReturn() {
              const docId = document.getElementById('returnDocId').value;
              const returnReason = document.getElementById('returnReason').value.trim();

              if (!returnReason) {
                const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                document.getElementById('returnValidationWarningTitle').textContent = 'Alasan Pengembalian Wajib Diisi';
                document.getElementById('returnValidationWarningMessage').textContent = 'Mohon isi alasan pengembalian sebelum melanjutkan.';
                warningModal.show();
                return;
              }

              if (returnReason.length < 10) {
                const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                document.getElementById('returnValidationWarningTitle').textContent = 'Alasan Pengembalian Terlalu Pendek';
                document.getElementById('returnValidationWarningMessage').textContent = 'Alasan pengembalian minimal 10 karakter.';
                warningModal.show();
                return;
              }

              // Show confirmation modal
              document.getElementById('returnConfirmationReason').textContent = returnReason;
              const returnModal = bootstrap.Modal.getInstance(document.getElementById('returnModal'));
              returnModal.hide();

              const confirmationModal = new bootstrap.Modal(document.getElementById('returnConfirmationModal'));
              confirmationModal.show();

              // Handle confirm button click
              const confirmBtn = document.getElementById('confirmReturnBtn');
              confirmBtn.onclick = function() {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';

                // Send return request
                fetch(`/documents/akutansi/${docId}/return`, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                    return_reason: returnReason
                  })
                })
                .then(response => response.json())
                .then(data => {
                  const confirmationModalInstance = bootstrap.Modal.getInstance(document.getElementById('returnConfirmationModal'));
                  confirmationModalInstance.hide();

                  if (data.success) {
                    const successModal = new bootstrap.Modal(document.getElementById('returnSuccessModal'));
                    successModal.show();

                    successModal._element.addEventListener('hidden.bs.modal', function() {
                      location.reload();
                    });
                  } else {
                    const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                    document.getElementById('returnValidationWarningTitle').textContent = 'Gagal Mengembalikan Dokumen';
                    document.getElementById('returnValidationWarningMessage').textContent = data.message || 'Terjadi kesalahan saat mengembalikan dokumen.';
                    warningModal.show();
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fa-solid fa-undo me-2"></i>Ya, Kembalikan';
                  }
                })
                .catch(error => {
                  console.error('Error:', error);
                  const confirmationModalInstance = bootstrap.Modal.getInstance(document.getElementById('returnConfirmationModal'));
                  confirmationModalInstance.hide();

                  const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                  document.getElementById('returnValidationWarningTitle').textContent = 'Terjadi Kesalahan';
                  document.getElementById('returnValidationWarningMessage').textContent = 'Terjadi kesalahan saat mengembalikan dokumen. Silakan coba lagi.';
                  warningModal.show();
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = '<i class="fa-solid fa-undo me-2"></i>Ya, Kembalikan';
                });
              };
            }

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
                                        @elseif($col == 'nomor_miro')
                                          MIR-{{ 1000 + $i }}
                                        @elseif($col == 'npwp')
                                          12.345.678.9-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}.456
                                        @elseif($col == 'no_faktur')
                                          123.456-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}.789
                                        @elseif($col == 'tanggal_faktur')
                                          {{ date('d/m/Y', strtotime("+$i days")) }}
                                        @elseif($col == 'tanggal_selesai_verifikasi_pajak')
                                          {{ date('d/m/Y', strtotime("+$i days")) }}
                                        @elseif($col == 'jenis_pph')
                                          PPh {{ 21 + $i }}
                                        @elseif($col == 'dpp_pph')
                                          {{ number_format(2000 * $i, 0, ',', '.') }}
                                        @elseif($col == 'ppn_terhutang')
                                          {{ number_format(3000 * $i, 0, ',', '.') }}
                                        @elseif($col == 'link_dokumen_pajak')
                                          <a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>
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
                'nomor_miro': ['MIR-1001', 'MIR-1002', 'MIR-1003', 'MIR-1004', 'MIR-1005'],
                'kategori': ['Operasional', 'Investasi', 'Operasional', 'Investasi', 'Operasional'],
                'kebun': ['Kebun A', 'Kebun B', 'Kebun C', 'Kebun A', 'Kebun B'],
                'npwp': ['12.345.678.9-001.456', '12.345.678.9-002.456', '12.345.678.9-003.456', '12.345.678.9-004.456', '12.345.678.9-005.456'],
                'no_faktur': ['123.456-001.789', '123.456-002.789', '123.456-003.789', '123.456-004.789', '123.456-005.789'],
                'tanggal_faktur': ['01/11/2024', '05/11/2024', '10/11/2024', '08/11/2024', '12/11/2024'],
                'tanggal_selesai_verifikasi_pajak': ['15/11/2024', '18/11/2024', '20/11/2024', '22/11/2024', '25/11/2024'],
                'jenis_pph': ['PPh 22', 'PPh 23', 'PPh 22', 'PPh 23', 'PPh 22'],
                'dpp_pph': ['2.000', '4.000', '6.000', '8.000', '10.000'],
                'ppn_terhutang': ['3.000', '6.000', '9.000', '12.000', '15.000'],
                'link_dokumen_pajak': ['<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>'],
              };

              for (let i = 0; i < 5; i++) {
                previewHTML += `<tr>`;
                previewHTML += `<td>${i + 1}</td>`;

                selectedColumnsOrder.forEach(columnKey => {
                  // Skip 'status' column as it's always shown as a special column
                  if (columnKey === 'status') {
                    return;
                  }

                  const columnLabel = availableColumnsData[columnKey] || columnKey;
                  let cellValue = sampleData[columnKey] ? sampleData[columnKey][i] : `Contoh ${columnLabel} ${i + 1}`;

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
                showWarningModal('Peringatan', 'Silakan pilih minimal satu kolom untuk ditampilkan.');
                return;
              }

              // Try multiple selectors to find the form
              let filterForm = document.getElementById('filterForm');
              if (!filterForm) {
                filterForm = document.querySelector('form[action*="akutansi"]');
              }
              if (!filterForm) {
                // Fallback: use first form on page
                filterForm = document.querySelector('form');
              }

              if (!filterForm) {
                showErrorModal('Error', 'Form tidak ditemukan.');
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

              // Add enable customization flag
              const enableInput = document.createElement('input');
              enableInput.type = 'hidden';
              enableInput.name = 'enable_customization';
              enableInput.value = '1';
              filterForm.appendChild(enableInput);

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

            // Open View Document Modal
            function openViewDocumentModal(docId) {
              // Set document ID
              document.getElementById('view-dokumen-id').value = docId;

              // Set edit button URL
              document.getElementById('view-edit-btn').href = `/documents/akutansi/${docId}/edit`;

              // CRITICAL: Reset all fields to loading state BEFORE fetch to prevent caching issues
              const fieldsToReset = [
                'view-nomor-agenda', 'view-nomor-spp', 'view-tanggal-spp', 'view-bulan', 'view-tahun',
                'view-tanggal-masuk', 'view-jenis-dokumen', 'view-jenis-sub-pekerjaan', 'view-kategori',
                'view-jenis-pembayaran', 'view-uraian-spp', 'view-nilai-rupiah', 'view-ejaan-nilai-rupiah',
                'view-dibayar-kepada', 'view-kebun', 'view-no-spk', 'view-tanggal-spk', 'view-tanggal-berakhir-spk',
                'view-nomor-miro', 'view-no-berita-acara', 'view-tanggal-berita-acara', 'view-nomor-po', 'view-nomor-pr',
                'view-tanggal-miro',
                // Perpajakan (Simplified - 6 Essential Fields)
                'view-no-faktur', 'view-tanggal-faktur', 'view-tanggal-selesai-verifikasi-pajak',
                'view-jenis-pph', 'view-dpp-pph', 'view-ppn-terhutang'
              ];

              fieldsToReset.forEach(fieldId => {
                const el = document.getElementById(fieldId);
                if (el) {
                  el.textContent = 'Memuat...';
                }
              });

              // Show modal immediately with loading state
              const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
              modal.show();

              // Load document data via AJAX
              fetch(`/documents/akutansi/${docId}/detail`, {
                headers: {
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
                .then(response => response.json())
                .then(data => {
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

                    // Referensi Pendukung
                    document.getElementById('view-no-spk').textContent = dok.no_spk || '-';
                    document.getElementById('view-tanggal-spk').textContent = dok.tanggal_spk ? formatDate(dok.tanggal_spk) : '-';
                    document.getElementById('view-tanggal-berakhir-spk').textContent = dok.tanggal_berakhir_spk ? formatDate(dok.tanggal_berakhir_spk) : '-';
                    // Nomor MIRO (consolidated via accessor in controller)
                    document.getElementById('view-nomor-miro').textContent = dok.nomor_miro || '-';
                    document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
                    document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDate(dok.tanggal_berita_acara) : '-';

                    // Nomor PO & PR
                    const poNumbers = dok.dokumen_pos ? dok.dokumen_pos.map(po => po.nomor_po).join(', ') : '-';
                    const prNumbers = dok.dokumen_prs ? dok.dokumen_prs.map(pr => pr.nomor_pr).join(', ') : '-';
                    document.getElementById('view-nomor-po').textContent = poNumbers || '-';
                    document.getElementById('view-nomor-pr').textContent = prNumbers || '-';

                    // Informasi Perpajakan (Simplified - 6 Essential Fields)
                    if (dok.no_faktur || dok.tanggal_faktur || dok.jenis_pph || dok.dpp_pph || dok.ppn_terhutang || dok.tanggal_selesai_verifikasi_pajak) {
                      // Row 1: No Faktur & Tanggal Faktur
                      const noFakturEl = document.getElementById('view-no-faktur');
                      if (noFakturEl) noFakturEl.textContent = dok.no_faktur || '-';

                      const tanggalFakturEl = document.getElementById('view-tanggal-faktur');
                      if (tanggalFakturEl) tanggalFakturEl.textContent = dok.tanggal_faktur ? formatDate(dok.tanggal_faktur) : '-';

                      // Row 2: Tgl Selesai Verifikasi & Jenis PPh
                      const tglVerifEl = document.getElementById('view-tanggal-selesai-verifikasi-pajak');
                      if (tglVerifEl) tglVerifEl.textContent = dok.tanggal_selesai_verifikasi_pajak ? formatDate(dok.tanggal_selesai_verifikasi_pajak) : '-';

                      const jenisPphEl = document.getElementById('view-jenis-pph');
                      if (jenisPphEl) jenisPphEl.textContent = dok.jenis_pph || '-';

                      // Row 3: DPP PPh & PPh Terhutang
                      const dppPphEl = document.getElementById('view-dpp-pph');
                      if (dppPphEl) dppPphEl.textContent = dok.dpp_pph ? formatNumber(dok.dpp_pph) : '-';

                      const ppnTerhutangEl = document.getElementById('view-ppn-terhutang');
                      if (ppnTerhutangEl) ppnTerhutangEl.textContent = dok.ppn_terhutang ? formatNumber(dok.ppn_terhutang) : '-';

                      // Show perpajakan section
                      document.getElementById('perpajakan-section').style.display = 'block';
                    } else {
                      // Hide perpajakan section if no data
                      document.getElementById('perpajakan-section').style.display = 'none';
                    }

                    // Informasi Akutansi
                    // Note: Nomor MIRO already set above, removed duplicate
                    document.getElementById('view-tanggal-miro').textContent = dok.tanggal_miro ? new Date(dok.tanggal_miro).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'}) : '-';
                  }
                })
                .catch(error => {
                  console.error('Error loading document:', error);
                  // Show error state in fields
                  fieldsToReset.forEach(fieldId => {
                    const el = document.getElementById(fieldId);
                    if (el) {
                      el.textContent = 'Gagal memuat data';
                    }
                  });
                });

              // Note: modal.show() is now called earlier with loading state

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

            function formatStatusPerpajakan(status) {
              if (!status) return '-';
              switch(status) {
                case 'sedang_diproses': return 'Sedang Diproses';
                case 'selesai': return 'Selesai';
                default: return status;
              }
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

                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">No. Berita Acara</label>
                            <div class="detail-value" id="view-no-berita-acara">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal Berita Acara</label>
                            <div class="detail-value" id="view-tanggal-berita-acara">-</div>
                          </div>
                        </div>

                        <!-- Miro Details -->
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Nomor MIRO</label>
                            <div class="detail-value" id="view-nomor-miro" style="font-weight: 700; color: #083E40;">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal MIRO</label>
                            <div class="detail-value" id="view-tanggal-miro" style="font-weight: 700; color: #083E40;">-</div>
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

                    <!-- Section 5: Informasi Perpajakan (Simplified - 6 Essential Fields) -->
                    <div class="form-section mb-4" id="perpajakan-section" style="display: none; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 12px; padding: 20px; border: 2px solid #ffc107;">
                      <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #92400e; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                          <i class="fa-solid fa-file-invoice-dollar"></i>
                          INFORMASI PERPAJAKAN
                          <span style="background: #ffc107; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px;">KHUSUS PERPAJAKAN</span>
                        </h6>
                      </div>

                      <!-- Row 1: No Faktur & Tanggal Faktur -->
                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">No Faktur</label>
                            <div class="detail-value" id="view-no-faktur">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal Faktur</label>
                            <div class="detail-value" id="view-tanggal-faktur">-</div>
                          </div>
                        </div>
                      </div>

                      <!-- Row 2: Tgl Selesai Verifikasi & Jenis PPh -->
                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tgl. Selesai Verifikasi Pajak</label>
                            <div class="detail-value" id="view-tanggal-selesai-verifikasi-pajak">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Jenis PPh</label>
                            <div class="detail-value" id="view-jenis-pph">-</div>
                          </div>
                        </div>
                      </div>

                      <!-- Row 3: DPP PPh & PPh Terhutang -->
                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">DPP PPh</label>
                            <div class="detail-value" id="view-dpp-pph">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">PPh Terhutang</label>
                            <div class="detail-value" id="view-ppn-terhutang">-</div>
                          </div>
                        </div>
                      </div>
                    </div>


                  </div>

                  <!-- Modal Footer -->
                  <div class="modal-footer" style="position: sticky; bottom: 0; z-index: 1050; background: white; border-top: 2px solid #e0e0e0; padding: 16px 24px; flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 24px;">
                      <i class="fa-solid fa-times me-2"></i>Tutup
                    </button>
                    <a href="#" id="view-edit-btn" class="btn" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; padding: 10px 24px;">
                      <i class="fa-solid fa-pen me-2"></i>Edit Dokumen
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <style>
              /* Detail Item Styles for View Modal - Matched with Perpajakan */
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
                word-wrap: break-word;
              }
            </style>

            <!-- Year Filter Modal -->
            <div class="year-filter-modal-overlay" id="yearFilterModalOverlay" onclick="closeYearFilterModal(event)">
              <div class="year-filter-modal" onclick="event.stopPropagation()">
                <div class="year-filter-modal-header">
                  <h5>
                    <i class="fa-solid fa-calendar-alt"></i>
                    Filter Tahun
                  </h5>
                  <button type="button" class="year-filter-modal-close" onclick="closeYearFilterModal()">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </div>
                <div class="year-filter-modal-body">
                  <!-- Filter Type Selection -->
                  <div class="filter-type-section">
                    <h6><i class="fa-solid fa-filter me-2"></i>Filter Berdasarkan</h6>
                    <div class="filter-type-options">
                      <div class="filter-type-option {{ request('year_filter_type', 'tanggal_spp') == 'tanggal_spp' ? 'selected' : '' }}" 
                           onclick="selectFilterType('tanggal_spp', this)">
                        <input type="radio" name="modal_filter_type" value="tanggal_spp" 
                               {{ request('year_filter_type', 'tanggal_spp') == 'tanggal_spp' ? 'checked' : '' }}>
                        <label>
                          <strong>Tanggal SPP</strong>
                          <small class="d-block">Tahun dari kolom Tanggal SPP</small>
                        </label>
                      </div>
                      <div class="filter-type-option {{ request('year_filter_type') == 'tanggal_masuk' ? 'selected' : '' }}" 
                           onclick="selectFilterType('tanggal_masuk', this)">
                        <input type="radio" name="modal_filter_type" value="tanggal_masuk" 
                               {{ request('year_filter_type') == 'tanggal_masuk' ? 'checked' : '' }}>
                        <label>
                          <strong>Tanggal Masuk</strong>
                          <small class="d-block">Tahun dari timestamp dokumen masuk</small>
                        </label>
                      </div>
                      <div class="filter-type-option {{ request('year_filter_type') == 'nomor_spp' ? 'selected' : '' }}" 
                           onclick="selectFilterType('nomor_spp', this)">
                        <input type="radio" name="modal_filter_type" value="nomor_spp" 
                               {{ request('year_filter_type') == 'nomor_spp' ? 'checked' : '' }}>
                        <label>
                          <strong>Tahun di Nomor SPP</strong>
                          <small class="d-block">Ekstrak tahun dari format nomor SPP (contoh: 192/M/SPP/14/03/2024)</small>
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Year Selection -->
                  <div class="year-selection-section">
                    <h6><i class="fa-solid fa-calendar me-2"></i>Pilih Tahun</h6>
                    <div class="year-buttons-grid">
                      <button type="button" class="year-btn all-years {{ !request('year') ? 'selected' : '' }}" 
                              onclick="selectYear('', this)">
                        Semua Tahun
                      </button>
                      <button type="button" class="year-btn {{ request('year') == '2024' ? 'selected' : '' }}" 
                              onclick="selectYear('2024', this)">2024</button>
                      <button type="button" class="year-btn {{ request('year') == '2025' ? 'selected' : '' }}" 
                              onclick="selectYear('2025', this)">2025</button>
                      <button type="button" class="year-btn {{ request('year') == '2026' ? 'selected' : '' }}" 
                              onclick="selectYear('2026', this)">2026</button>
                      <button type="button" class="year-btn {{ request('year') == '2027' ? 'selected' : '' }}" 
                              onclick="selectYear('2027', this)">2027</button>
                      <button type="button" class="year-btn {{ request('year') == '2028' ? 'selected' : '' }}" 
                              onclick="selectYear('2028', this)">2028</button>
                      <button type="button" class="year-btn {{ request('year') == '2029' ? 'selected' : '' }}" 
                              onclick="selectYear('2029', this)">2029</button>
                      <button type="button" class="year-btn {{ request('year') == '2030' ? 'selected' : '' }}" 
                              onclick="selectYear('2030', this)">2030</button>
                    </div>
                  </div>
                </div>
                <div class="year-filter-modal-footer">
                  <button type="button" class="btn-reset-filter" onclick="resetYearFilter()">
                    <i class="fa-solid fa-rotate-left me-2"></i>Reset
                  </button>
                  <button type="button" class="btn-apply-filter" onclick="applyYearFilter()">
                    <i class="fa-solid fa-check me-2"></i>Terapkan Filter
                  </button>
                </div>
              </div>
            </div>

            <script>
            // Year Filter Modal Functions
            let selectedYear = '{{ request('year') }}';
            let selectedFilterType = '{{ request('year_filter_type', 'tanggal_spp') }}';

            function openYearFilterModal() {
              document.getElementById('yearFilterModalOverlay').classList.add('active');
              document.body.style.overflow = 'hidden';
            }

            function closeYearFilterModal(event) {
              if (event && event.target !== event.currentTarget) return;
              document.getElementById('yearFilterModalOverlay').classList.remove('active');
              document.body.style.overflow = '';
            }

            function selectFilterType(type, element) {
              selectedFilterType = type;

              // Update visual state
              document.querySelectorAll('.filter-type-option').forEach(opt => {
                opt.classList.remove('selected');
                opt.querySelector('input').checked = false;
              });
              element.classList.add('selected');
              element.querySelector('input').checked = true;
            }

            function selectYear(year, element) {
              selectedYear = year;

              // Update visual state
              document.querySelectorAll('.year-btn').forEach(btn => {
                btn.classList.remove('selected');
              });
              element.classList.add('selected');
            }

            function resetYearFilter() {
              selectedYear = '';
              selectedFilterType = 'tanggal_spp';

              // Reset visual state
              document.querySelectorAll('.year-btn').forEach(btn => {
                btn.classList.remove('selected');
                if (btn.classList.contains('all-years')) {
                  btn.classList.add('selected');
                }
              });

              document.querySelectorAll('.filter-type-option').forEach((opt, index) => {
                opt.classList.remove('selected');
                opt.querySelector('input').checked = false;
                if (index === 0) {
                  opt.classList.add('selected');
                  opt.querySelector('input').checked = true;
                }
              });

              // Apply immediately
              applyYearFilter();
            }

            function applyYearFilter() {
              // Update hidden inputs
              document.getElementById('yearSelect').value = selectedYear;
              document.getElementById('yearFilterType').value = selectedFilterType;

              // Update button text
              const filterTypeLabels = {
                'tanggal_spp': 'Tgl SPP',
                'tanggal_masuk': 'Tgl Masuk',
                'nomor_spp': 'No SPP'
              };

              const btnText = document.getElementById('yearFilterBtnText');
              if (selectedYear) {
                btnText.textContent = selectedYear + ' (' + filterTypeLabels[selectedFilterType] + ')';
              } else {
                btnText.textContent = 'Filter Tahun';
              }

              // Close modal
              closeYearFilterModal();

              // Submit form
              document.getElementById('filterForm').submit();
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
              if (e.key === 'Escape') {
                const overlay = document.getElementById('yearFilterModalOverlay');
                if (overlay && overlay.classList.contains('active')) {
                  closeYearFilterModal();
                }
              }
            });

            // ===== LIVE SEARCH FUNCTIONALITY =====
            // Debounce function
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Live search handler
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                const liveSearchHandler = debounce(function() {
                    const form = searchInput.closest('form');
                    if (!form) return;

                    const searchValue = searchInput.value.trim();
                    const url = new URL(form.action);

                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }

                    const yearInput = form.querySelector('input[name="year"]');
                    if (yearInput && yearInput.value) {
                        url.searchParams.set('year', yearInput.value);
                    }

                    const yearFilterType = form.querySelector('input[name="year_filter_type"]');
                    if (yearFilterType && yearFilterType.value) {
                        url.searchParams.set('year_filter_type', yearFilterType.value);
                    }

                    const statusInput = form.querySelector('input[name="status_filter"]');
                    if (statusInput && statusInput.value) {
                        url.searchParams.set('status_filter', statusInput.value);
                    }

                    const perPage = new URLSearchParams(window.location.search).get('per_page');
                    if (perPage) {
                        url.searchParams.set('per_page', perPage);
                    }

                    const columnInputs = form.querySelectorAll('input[name="columns[]"]');
                    columnInputs.forEach(input => {
                        url.searchParams.append('columns[]', input.value);
                    });

                    window.location.href = url.toString();
                }, 500);

                searchInput.addEventListener('input', liveSearchHandler);
            }
            </script>

    <!-- Floating Action Bar (Akutansi only sends to Pembayaran) -->
    <div id="floatingActionBar" class="floating-action-bar">
      <div class="selected-info">
        <i class="fas fa-check-circle"></i>
        Dipilih: <span id="selectedCount" class="selected-count">0</span>
      </div>

      <div style="color: white; font-size: 14px; padding: 10px 16px; background: rgba(255,255,255,0.1); border-radius: 8px;">
        💰 Kirim ke Pembayaran
      </div>

      <button type="button" class="btn-bulk-action btn-send" onclick="openBulkConfirmModal()">
        <i class="fas fa-paper-plane"></i> Kirim
      </button>

      <button type="button" class="btn-bulk-action btn-cancel" onclick="cancelBulkSelection()">
        <i class="fas fa-times"></i> Batal
      </button>
    </div>

    <!-- Bulk Send Confirmation Modal -->
    <div id="bulkModalOverlay" class="bulk-modal-overlay">
      <div class="bulk-modal">
        <h3><i class="fas fa-paper-plane"></i> Konfirmasi Kirim Dokumen</h3>
        <div class="summary">
          <div class="summary-item">
            <span class="summary-label">Jumlah Dokumen:</span>
            <span id="summaryDocCount" class="summary-value">0 dokumen</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Tujuan:</span>
            <span id="summaryDestination" class="summary-value">Team Pembayaran</span>
          </div>
        </div>
        <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">
          Dokumen akan dikirim ke <strong>inbox</strong> Team Pembayaran dan menunggu persetujuan sebelum masuk ke daftar dokumen mereka.
        </p>
        <div class="actions">
          <button type="button" class="btn btn-close" onclick="closeBulkConfirmModal()">Batal</button>
          <button type="button" class="btn btn-confirm" onclick="executeBulkSend()">
            <i class="fas fa-paper-plane"></i> Kirim Sekarang
          </button>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div id="bulkLoadingOverlay" class="bulk-loading-overlay">
      <div class="spinner"></div>
      <div id="loadingText" class="loading-text">Memproses...</div>
      <div id="progressText" class="progress-text">Mohon tunggu...</div>
    </div>

    <!-- Bulk Send Success Modal -->
    <div id="bulkSuccessModal" class="bulk-success-overlay">
      <div class="bulk-success-modal">
        <div class="success-icon-container">
          <div class="success-circle">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
              <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
              <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
          </div>
          <div class="confetti">
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
          </div>
        </div>
        <h2 class="success-title">Berhasil Terkirim!</h2>
        <div class="success-details">
          <div class="success-stat">
            <span class="stat-number" id="successDocCount">0</span>
            <span class="stat-label">Dokumen</span>
          </div>
          <div class="success-arrow">
            <i class="fas fa-arrow-right"></i>
          </div>
          <div class="success-destination">
            <i class="fas fa-inbox"></i>
            <span id="successDestination">Team Pembayaran</span>
          </div>
        </div>
        <p class="success-message">
          <i class="fas fa-info-circle"></i>
          Dokumen telah masuk ke <strong>inbox</strong> dan menunggu persetujuan
        </p>
        <button type="button" class="btn-success-close" onclick="closeSuccessAndReload()">
          <i class="fas fa-check"></i> Mengerti
        </button>
      </div>
    </div>

    <style>
    /* Success Modal Styles */
    .bulk-success-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .bulk-success-overlay.show {
      display: flex;
      opacity: 1;
    }

    .bulk-success-modal {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border-radius: 20px;
      padding: 40px;
      text-align: center;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1);
      animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes modalSlideIn {
      from {
        transform: scale(0.8) translateY(20px);
        opacity: 0;
      }
      to {
        transform: scale(1) translateY(0);
        opacity: 1;
      }
    }

    .success-icon-container {
      position: relative;
      margin-bottom: 24px;
    }

    .success-circle {
      width: 80px;
      height: 80px;
      margin: 0 auto;
    }

    .checkmark {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: block;
      stroke-width: 2;
      stroke: #10b981;
      stroke-miterlimit: 10;
      box-shadow: inset 0px 0px 0px #10b981;
      animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
    }

    .checkmark-circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 2;
      stroke-miterlimit: 10;
      stroke: #10b981;
      fill: none;
      animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark-check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
      100% { stroke-dashoffset: 0; }
    }

    @keyframes fill {
      100% { box-shadow: inset 0px 0px 0px 30px rgba(16, 185, 129, 0.1); }
    }

    @keyframes scale {
      0%, 100% { transform: none; }
      50% { transform: scale3d(1.1, 1.1, 1); }
    }

    /* Confetti Animation */
    .confetti {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }

    .confetti-piece {
      position: absolute;
      width: 10px;
      height: 10px;
      border-radius: 2px;
      animation: confetti-fall 1s ease-out forwards;
      opacity: 0;
    }

    .confetti-piece:nth-child(1) { background: #f59e0b; animation-delay: 0.2s; }
    .confetti-piece:nth-child(2) { background: #10b981; animation-delay: 0.3s; }
    .confetti-piece:nth-child(3) { background: #3b82f6; animation-delay: 0.4s; }
    .confetti-piece:nth-child(4) { background: #ec4899; animation-delay: 0.5s; }
    .confetti-piece:nth-child(5) { background: #8b5cf6; animation-delay: 0.6s; }
    .confetti-piece:nth-child(6) { background: #ef4444; animation-delay: 0.7s; }

    @keyframes confetti-fall {
      0% {
        opacity: 1;
        transform: translate(0, 0) rotate(0deg) scale(1);
      }
      100% {
        opacity: 0;
        transform: translate(var(--tx, 50px), var(--ty, 80px)) rotate(var(--rot, 360deg)) scale(0.5);
      }
    }

    .confetti-piece:nth-child(1) { --tx: -60px; --ty: -40px; --rot: 180deg; }
    .confetti-piece:nth-child(2) { --tx: 70px; --ty: -50px; --rot: -200deg; }
    .confetti-piece:nth-child(3) { --tx: -50px; --ty: 60px; --rot: 150deg; }
    .confetti-piece:nth-child(4) { --tx: 60px; --ty: 50px; --rot: -180deg; }
    .confetti-piece:nth-child(5) { --tx: -30px; --ty: -60px; --rot: 220deg; }
    .confetti-piece:nth-child(6) { --tx: 40px; --ty: 70px; --rot: -150deg; }

    .success-title {
      color: #10b981;
      font-size: 28px;
      font-weight: 700;
      margin: 0 0 24px 0;
      letter-spacing: -0.5px;
    }

    .success-details {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      margin-bottom: 24px;
      padding: 20px;
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
      border-radius: 12px;
      border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .success-stat {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .stat-number {
      font-size: 36px;
      font-weight: 800;
      color: #059669;
      line-height: 1;
    }

    .stat-label {
      font-size: 13px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 4px;
    }

    .success-arrow {
      color: #10b981;
      font-size: 20px;
      animation: arrowPulse 1s ease-in-out infinite;
    }

    @keyframes arrowPulse {
      0%, 100% { transform: translateX(0); opacity: 1; }
      50% { transform: translateX(5px); opacity: 0.7; }
    }

    .success-destination {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }

    .success-destination i {
      font-size: 24px;
      color: #059669;
    }

    .success-destination span {
      font-size: 14px;
      font-weight: 600;
      color: #374151;
    }

    .success-message {
      color: #6b7280;
      font-size: 14px;
      margin: 0 0 28px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .success-message i {
      color: #3b82f6;
    }

    .btn-success-close {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border: none;
      padding: 14px 40px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }

    .btn-success-close:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
    }

    .btn-success-close:active {
      transform: translateY(0);
    }
    </style>

    <!-- Bulk Send JavaScript (MUST be after HTML elements) -->
    <script>
        // ===== BULK SEND FUNCTIONALITY =====
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const docCheckboxes = document.querySelectorAll('.doc-checkbox');
        const floatingActionBar = document.getElementById('floatingActionBar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkModalOverlay = document.getElementById('bulkModalOverlay');
        const bulkLoadingOverlay = document.getElementById('bulkLoadingOverlay');

        let selectedDocuments = [];

        // Update floating action bar visibility
        function updateFloatingBar() {
            selectedDocuments = [];
            docCheckboxes.forEach(cb => {
                if (cb.checked) {
                    selectedDocuments.push({
                        id: cb.dataset.id,
                        agenda: cb.dataset.agenda
                    });
                }
            });

            if (selectedDocuments.length > 0 && floatingActionBar) {
                floatingActionBar.classList.add('show');
                if (selectedCountSpan) selectedCountSpan.textContent = selectedDocuments.length;
            } else if (floatingActionBar) {
                floatingActionBar.classList.remove('show');
            }

            // Update select all checkbox state
            const allChecked = docCheckboxes.length > 0 && 
                Array.from(docCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(docCheckboxes).some(cb => cb.checked);

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        }

        // Select all checkbox handler
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                docCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateFloatingBar();
            });
        }

        // Individual checkbox handlers
        docCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateFloatingBar);
        });

        // Cancel bulk selection
        function cancelBulkSelection() {
            docCheckboxes.forEach(cb => cb.checked = false);
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            updateFloatingBar();
        }

        // Open confirmation modal
        function openBulkConfirmModal() {
            const summaryDocCount = document.getElementById('summaryDocCount');
            const summaryDestination = document.getElementById('summaryDestination');
            if (summaryDocCount) summaryDocCount.textContent = selectedDocuments.length + ' dokumen';
            if (summaryDestination) summaryDestination.textContent = 'Team Pembayaran';

            if (bulkModalOverlay) bulkModalOverlay.classList.add('show');
        }

        // Close confirmation modal
        function closeBulkConfirmModal() {
            if (bulkModalOverlay) bulkModalOverlay.classList.remove('show');
        }

        // Execute bulk send
        async function executeBulkSend() {
            closeBulkConfirmModal();

            const documentIds = selectedDocuments.map(doc => parseInt(doc.id));

            // Show loading overlay
            if (bulkLoadingOverlay) {
                bulkLoadingOverlay.classList.add('show');
                const loadingText = document.getElementById('loadingText');
                const progressText = document.getElementById('progressText');
                if (loadingText) loadingText.textContent = `Mengirim ${documentIds.length} dokumen ke Team Pembayaran...`;
                if (progressText) progressText.textContent = 'Mohon tunggu...';
            }

            try {
                const response = await fetch('/bulk-operations/forward', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        document_ids: documentIds,
                        target_role: 'pembayaran'
                    })
                });

                const result = await response.json();

                if (bulkLoadingOverlay) bulkLoadingOverlay.classList.remove('show');

                if (result.success) {
                    showSuccessModal(result.processed, 'Team Pembayaran');
                } else {
                    alert(`❌ Gagal!\n\n${result.message}`);
                }
            } catch (error) {
                if (bulkLoadingOverlay) bulkLoadingOverlay.classList.remove('show');
                console.error('Bulk send error:', error);
                alert('Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.');
            }
        }

        // Show success modal
        function showSuccessModal(count, destination) {
            const successModal = document.getElementById('bulkSuccessModal');
            const successDocCount = document.getElementById('successDocCount');
            const successDestination = document.getElementById('successDestination');

            if (successDocCount) successDocCount.textContent = count;
            if (successDestination) successDestination.textContent = destination;

            if (successModal) {
                successModal.classList.add('show');
            }
        }

        // Close success modal and reload page
        function closeSuccessAndReload() {
            const successModal = document.getElementById('bulkSuccessModal');
            if (successModal) {
                successModal.classList.remove('show');
            }
            window.location.reload();
        }

        // Close modal on overlay click
        if (bulkModalOverlay) {
            bulkModalOverlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeBulkConfirmModal();
                }
            });
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && bulkModalOverlay && bulkModalOverlay.classList.contains('show')) {
                closeBulkConfirmModal();
            }
        });
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
    </script>

@endsection





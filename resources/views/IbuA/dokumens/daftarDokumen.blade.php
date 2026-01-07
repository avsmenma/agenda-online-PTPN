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

    /* Enhanced table for better UX - Adopted from IbuB */
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
    }

    .table-enhanced .col-mirror {
      width: 120px;
      min-width: 120px;
    }

    .table-enhanced .col-status {
      width: 120px;
      min-width: 100px;
      text-align: center;
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
      white-space: nowrap;
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

    /* State 2: Sudah Dikirim ke IbuB */
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
        <input type="text" class="form-control" name="search" placeholder="Cari nomor agenda, SPP, nilai rupia"
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

  <!-- Enhanced Tabel Dokumen -->
  <div class="table-dokumen">
    <div class="table-responsive table-container">
      <table class="table table-enhanced mb-0">
        <thead>
          <tr>
            <th class="col-no sticky-column">No</th>
            @php
              $filteredColumns = array_filter($selectedColumns, function ($col) use ($availableColumns) {
                return $col !== 'nomor_mirror' && $col !== 'keterangan' && isset($availableColumns[$col]);
              });
            @endphp
            @foreach($filteredColumns as $col)
              <th class="col-{{ $col }}">{{ $availableColumns[$col] }}</th>
            @endforeach
            <th class="col-action sticky-column">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            <tr class="main-row clickable-row" data-id="{{ $dokumen->id }}"
              onclick="handleRowClick(event, {{ $dokumen->id }})">
              <td class="col-no sticky-column">{{ $dokumens->firstItem() + $index }}</td>
              @php
                $filteredColumns = array_filter($selectedColumns, function ($col) use ($availableColumns) {
                  return $col !== 'nomor_mirror' && $col !== 'keterangan' && isset($availableColumns[$col]);
                });
              @endphp
              @foreach($filteredColumns as $col)
                <td class="col-{{ $col }}">
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
                      // Logic Sederhana Khusus Ibu Tarapul: Hanya 2 Status (Belum Dikirim & Terkirim)
                      // Jika dokumen ada di tangan IbuA (Draft, Returned, Rejected) -> Belum Dikirim
                      // Jika dokumen ada di tangan role lain (IbuB, Perpajakan, Akutansi, dll) -> Terkirim
                      $isWithIbuA = in_array(strtolower($dokumen->current_handler ?? ''), ['ibua', 'ibu a']);
                    @endphp
                    @if((strtolower($dokumen->current_handler ?? '') == 'ibua' || strtolower($dokumen->current_handler ?? '') == 'ibu a') && !in_array($dokumen->status, ['pending_approval_ibub', 'pending_approval_ibu_b']))
                      <span class="badge-status badge-draft">
                        <i class="fa-solid fa-file-lines me-1"></i>
                        <span>Belum Dikirim</span>
                      </span>
                    @elseif(in_array($dokumen->status, ['pending_approval_ibub', 'pending_approval_ibu_b']))
                      <span class="badge-status"
                        style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                        <i class="fa-solid fa-clock me-1"></i>
                        <span>Menunggu Approve Team Verifikasi</span>
                      </span>
                    @else
                      <span class="badge-status badge-terkirim">
                        <i class="fa-solid fa-check me-1"></i>
                        <span>Terkirim</span>
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
                  @else
                    -
                  @endif
                </td>
              @endforeach
              <td class="col-action sticky-column" onclick="event.stopPropagation()">
                <div class="action-buttons">
                  @php
                    // Check if document has been sent to IbuB
                    $isSentToIbuB = ($dokumen->status ?? '') == 'sent_to_ibub'
                      || (($dokumen->current_handler ?? 'ibuA') == 'ibuB' && ($dokumen->status ?? '') != 'returned_to_ibua');

                    // Check if document has been approved by Team Verifikasi and sent to other roles
                    $ibuBStatus = $dokumen->getStatusForRole('ibub');
                    $isApprovedByIbuB = $ibuBStatus && $ibuBStatus->status === 'approved';

                    // Check if document is rejected - check from roleStatuses
                    // More comprehensive check to ensure we catch all rejected documents
                    $isRejected = false;

                    // Method 1: Check from getStatusForRole
                    if ($ibuBStatus && strtolower($ibuBStatus->status ?? '') === 'rejected') {
                      $isRejected = true;
                    }

                    // Method 2: Fallback - check from dokumen_statuses directly (case-insensitive)
                    if (!$isRejected) {
                      $rejectedStatus = $dokumen->roleStatuses()
                        ->where('status', 'rejected')
                        ->whereIn('role_code', ['ibub', 'ibuB', 'ibub', 'IbuB'])
                        ->first();
                      $isRejected = $rejectedStatus !== null;
                    }

                    // Method 3: Check if status is returned_to_ibua AND has rejection in roleStatuses
                    // This catches documents that were rejected but status might not be set correctly
                    if (!$isRejected && strtolower($dokumen->status ?? '') === 'returned_to_ibua') {
                      // Check if there's any rejection status in roleStatuses relationship
                      $hasAnyRejection = $dokumen->roleStatuses()
                        ->where('status', 'rejected')
                        ->exists();
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

                    // Document is considered "sent" if sent to IbuB OR approved by IbuB and sent to other roles
                    // BUT: rejected documents are NOT considered "sent" - they can be sent again
                    $isSent = ($isSentToIbuB || ($isApprovedByIbuB && $isSentToOtherRoles)) && !$isRejected;

                    // Can send only if document is draft/returned and still with IbuA
                    // Include rejected documents (returned_to_ibua) so they can be sent again
                    // IMPORTANT: Rejected documents should always be able to be sent again

                    // Check if document is created by IbuA (case-insensitive)
                    $createdByIbuA = in_array(strtolower($dokumen->created_by ?? ''), ['ibua', 'ibu a']);

                    // Check if document is currently with IbuA (case-insensitive)
                    $currentHandlerIbuA = in_array(strtolower($dokumen->current_handler ?? ''), ['ibua', 'ibu a']);

                    // Check if document is returned (case-insensitive)
                    $isReturned = strtolower($dokumen->status ?? '') === 'returned_to_ibua';

                    // Initialize canSend
                    $canSend = false;

                    // PRIORITY 1: Rejected documents can ALWAYS be sent again if they're with IbuA
                    // This is the most important case - rejected documents must be able to be resent
                    if ($isRejected && $currentHandlerIbuA && $createdByIbuA) {
                      $canSend = true;
                    }
                    // PRIORITY 2: Returned documents (returned_to_ibua) can be sent
                    // This includes rejected documents that have status returned_to_ibua
                    elseif ($isReturned && $currentHandlerIbuA && $createdByIbuA && !$isSent) {
                      $canSend = true;
                    }
                    // PRIORITY 3: Normal documents (draft, sedang diproses)
                    elseif (
                      in_array(strtolower($dokumen->status ?? ''), ['draft', 'sedang diproses'])
                      && $currentHandlerIbuA
                      && $createdByIbuA
                      && !$isSent
                    ) {
                      $canSend = true;
                    }

                    // DEBUG: Log untuk membantu troubleshooting (hapus setelah fix)
                    // \Log::info('Document send permission check', [
                    //   'doc_id' => $dokumen->id,
                    //   'isRejected' => $isRejected,
                    //   'isReturned' => $isReturned,
                    //   'current_handler' => $dokumen->current_handler,
                    //   'created_by' => $dokumen->created_by,
                    //   'status' => $dokumen->status,
                    //   'currentHandlerIbuA' => $currentHandlerIbuA,
                    //   'createdByIbuA' => $createdByIbuA,
                    //   'isSent' => $isSent,
                    //   'canSend' => $canSend,
                    // ]);

                    // Can edit only if document is not sent and can be edited
                    // IMPORTANT: Rejected documents should always be able to be edited
                    $canEdit = false;

                    // PRIORITY 1: Rejected documents can ALWAYS be edited if they're with IbuA
                    if ($isRejected && $currentHandlerIbuA && $createdByIbuA) {
                      $canEdit = true;
                    }
                    // PRIORITY 2: Returned documents (returned_to_ibua) can be edited
                    elseif ($isReturned && $currentHandlerIbuA && $createdByIbuA && !$isSent) {
                      $canEdit = true;
                    }
                    // PRIORITY 3: Draft documents can be edited
                    elseif (
                      strtolower($dokumen->status ?? '') === 'draft'
                      && $currentHandlerIbuA
                      && $createdByIbuA
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
                    <button class="btn-action btn-send" onclick="sendToIbuB({{ $dokumen->id }})"
                      title="Kirim ke Team Verifikasi">
                      <i class="fa-solid fa-paper-plane"></i>
                      <span>Kirim</span>
                    </button>
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
        $ibuBStatus = $dokumen->getStatusForRole('ibub');
        $isRejected = $ibuBStatus && $ibuBStatus->status === 'rejected';
        $rejectReason = $ibuBStatus?->notes ?? null;
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
                      if (!$rejectedBy && $ibuBStatus) {
                        $rejectedBy = $ibuBStatus->changed_by ?? 'Ibu Yuni';
                      }

                      // Format nama yang lebih ramah
                      if ($rejectedBy) {
                        $nameMap = [
                          'IbuB' => 'Ibu Yuni',
                          'ibuB' => 'Ibu Yuni',
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
            <div class="panel-title">
              <i class="fa-solid fa-check-square"></i>
              Pilih Kolom
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

  <!-- Modal: Send Confirmation -->
  <div class="modal fade" id="sendConfirmationModal" tabindex="-1" aria-labelledby="sendConfirmationModalLabel"
    aria-hidden="true">
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
          <h5 class="fw-bold mb-3">Apakah Anda yakin ingin mengirim dokumen ini ke Team Verifikasi?</h5>
          <p class="text-muted mb-0">
            Dokumen akan langsung dikirim ke Team Verifikasi dan muncul di daftar dokumen Team Verifikasi untuk diproses.
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
    // Enhanced interactions and anima  t  ions
    function toggleDetail    (rowI     d) {
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

      fetch(`/documents/${docId}/send-to-verifikasi`, {
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
        textEl.textContent = message || 'Dokumen berhasil dikirim dan akan diproses oleh Team Verifikasi.';
      }

      shouldReloadAfterSendSuccess = true;
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }

    // Wrapper function untuk handle row click dengan text selection check
    function handleRowClick(event, documentId) {
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

      // Buka modal popup untuk menampilkan detail dokumen
      openViewDocumentModal(documentId);
      return true;
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
        fetch(`/documents/${documentId}/detail-ibua`)
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

      // Auto-hide untuk notifikasi success/error biasa setelah 4 detik
      // Notifikasi dokumen masuk/reject tetap permanen
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
      const confirmBtn = document.getElementById('confirmSendToIbuBBtn');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', confirmSendToIbuB);
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
            document.getElementById('view-bulan').textContent = dok.bulan || '-';
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
            document.getElementById('view-nomor-miro').textContent = dok.nomor_miro || '-';
            document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
            document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDate(dok.tanggal_berita_acara) : '-';

            // Nomor PO & PR
            const poList = dok.dokumen_pos && dok.dokumen_pos.length > 0 
              ? dok.dokumen_pos.map(po => po.nomor_po).join(', ')
              : '-';
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
                    <label class="detail-label">Nomor Miro</label>
                    <div class="detail-value" id="view-nomor-miro">-</div>
                  </div>
                </div>
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
          </div>

          <!-- Sticky Footer -->
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
@endsection


@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  /* Statistics Cards */
  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
  }

  .stat-card-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
  }

  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
  }

  .stat-icon.total { background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); }
  .stat-icon.belum { background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); }
  .stat-icon.siap { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
  .stat-icon.sudah { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }

  .stat-title {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
  }

  .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
  }

  .stat-nilai {
    font-size: 13px;
    font-weight: 600;
    color: #28a745;
  }

  /* Filter Section - Modern Redesign */
  .filter-section {
    background: #ffffff;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
  }

  .main-filter-row {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
  }

  .filter-inputs {
    display: flex;
    gap: 16px;
    flex: 1;
    flex-wrap: wrap;
    min-width: 0;
  }

  .filter-item {
    flex: 1;
    min-width: 160px;
  }

  .filter-item.filter-search {
    flex: 1.5;
    min-width: 200px;
  }

  .filter-label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    font-size: 13px;
  }

  .form-select-modern, .form-control-modern {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: #ffffff;
    color: #111827;
    transition: all 0.2s ease;
  }

  .form-select-modern:focus, .form-control-modern:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .search-input-wrapper {
    position: relative;
  }

  .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
  }

  .form-control-modern {
    padding-left: 38px;
  }

  .filter-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
  }

  .btn-filter-primary {
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
  }

  .btn-filter-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  .btn-filter-reset {
    background: #ffffff;
    color: #6b7280;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
  }

  .btn-filter-reset:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #374151;
  }

  /* Rekapan Configuration Section */
  .rekapan-config-section {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
  }

  .rekapan-toggle-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
  }

  .rekapan-toggle-header {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
  }

  .rekapan-toggle-header input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #3b82f6;
  }

  .rekapan-toggle-header label {
    font-weight: 600;
    color: #111827;
    cursor: pointer;
    margin: 0;
    font-size: 14px;
  }

  .btn-column-modal {
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 18px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
  }

  .btn-column-modal:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  /* Collapsible Filter Rekapan Panel */
  .filter-rekapan-panel {
    display: none;
    margin-top: 16px;
  }

  .filter-rekapan-panel.show {
    display: block;
  }

  .panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .panel-header:hover {
    background: #f3f4f6;
  }

  .panel-title {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
    display: flex;
    align-items: center;
  }

  .panel-icon {
    color: #6b7280;
    transition: transform 0.2s ease;
  }

  .panel-header.active .panel-icon {
    transform: rotate(180deg);
  }

  .panel-content {
    display: none;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 8px 8px;
  }

  .panel-header.active + .panel-content,
  .panel-content.show {
    display: block;
  }

  .filter-rekapan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
  }

  .filter-rekapan-item {
    display: flex;
    flex-direction: column;
  }

  /* Column Modal */
  .column-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
  }

  .column-modal.show {
    display: flex;
  }

  .modal-content-modern {
    background: #ffffff;
    border-radius: 16px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
  }

  .modal-header-modern {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
    border-radius: 16px 16px 0 0;
  }

  .modal-header-modern h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
  }

  .modal-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #6b7280;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  .modal-close:hover {
    background: #e5e7eb;
    color: #111827;
  }

  .modal-body-modern {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
  }

  .modal-actions-top {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
  }

  .btn-select-all, .btn-deselect-all {
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #ffffff;
    color: #374151;
    font-weight: 500;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
  }

  .btn-select-all:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
  }

  .btn-deselect-all:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
  }

  .column-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    max-height: 400px;
    overflow-y: auto;
    padding: 8px;
  }

  .column-checkbox-item-modern {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .column-checkbox-item-modern:hover {
    border-color: #3b82f6;
    background: #f0f9ff;
  }

  .column-checkbox-item-modern.selected {
    border-color: #3b82f6;
    background: #eff6ff;
  }

  .column-checkbox-item-modern input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #3b82f6;
  }

  .column-checkbox-item-modern label {
    font-size: 14px;
    color: #111827;
    cursor: pointer;
    margin: 0;
    flex: 1;
    font-weight: 500;
  }

  .order-badge-modern {
    background: #3b82f6;
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 12px;
    min-width: 24px;
    text-align: center;
    display: none;
  }

  .column-checkbox-item-modern.selected .order-badge-modern {
    display: inline-block;
  }

  .selected-preview-modern {
    margin-top: 20px;
    padding: 16px;
    background: #f0f9ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    display: none;
  }

  .selected-preview-modern.show {
    display: block;
  }

  .preview-header {
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 12px;
    font-size: 14px;
    display: flex;
    align-items: center;
  }

  .preview-content {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .preview-badge {
    background: #3b82f6;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
  }

  .modal-footer-modern {
    padding: 16px 24px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f9fafb;
    border-radius: 0 0 16px 16px;
  }

  .btn-modal-cancel {
    padding: 10px 20px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
    color: #6b7280;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
  }

  .btn-modal-cancel:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
  }

  .btn-modal-save {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    background: #3b82f6;
    color: white;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
  }

  .btn-modal-save:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes slideUp {
    from {
      transform: translateY(20px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  /* Table Styles */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .table-header {
    padding: 20px 25px;
    border-bottom: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .table-header h5 {
    margin: 0;
    font-weight: 700;
    color: #400808ff;
  }

  .table {
    margin: 0;
  }

  .table thead th {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 14px 12px;
    font-size: 11px;
    text-transform: uppercase;
    white-space: nowrap;
    text-align: center;
  }
  
  .table tbody td {
    text-align: center;
    vertical-align: middle;
  }

  .table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
  }

  .table tbody tr:hover {
    background: rgba(136, 151, 23, 0.05);
  }

  .table tbody td {
    padding: 12px;
    font-size: 13px;
    color: #2c3e50;
    vertical-align: middle;
  }

  /* Vendor Group Header */
  .vendor-group-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    padding: 15px 20px;
    margin-top: 20px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .vendor-group-header:first-child {
    margin-top: 0;
  }

  .vendor-group-header h6 {
    margin: 0;
    font-weight: 700;
    font-size: 14px;
  }

  .vendor-group-stats {
    display: flex;
    gap: 15px;
    font-size: 12px;
  }

  .vendor-group-stats span {
    padding: 4px 10px;
    border-radius: 15px;
    background: rgba(255,255,255,0.2);
  }

  .vendor-table {
    border-radius: 0 0 10px 10px;
    overflow: hidden;
    margin-bottom: 20px;
    border: 1px solid rgba(8, 62, 64, 0.1);
    border-top: none;
  }

  /* Badge Styles */
  .badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .badge-belum { background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; }
  .badge-siap { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; }
  .badge-sudah { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }

  /* Pagination */
  .pagination-wrapper {
    padding: 20px 25px;
    border-top: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
  }

  .pagination button {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    cursor: pointer;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    color: #083E40;
    transition: all 0.3s ease;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pagination button:hover:not(:disabled) {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .pagination button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .pagination a {
    text-decoration: none;
    color: inherit;
  }

  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    border: none !important;
  }

  .btn-chevron:hover:not(:disabled) {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    transform: translateY(-2px);
  }

  .btn-chevron:disabled {
    background: #e0e0e0;
    color: #9e9e9e;
    cursor: not-allowed;
  }

  /* Grand Total */
  .grand-total-row {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%) !important;
    font-weight: 700;
  }

  .grand-total-row td {
    border-top: 2px solid #28a745 !important;
  }

  /* Export Buttons */
  .export-buttons {
    display: flex;
    gap: 10px;
  }

  .btn-export {
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
  }

  .btn-export-excel {
    background: linear-gradient(135deg, #217346 0%, #1e6b3f 100%);
    color: white;
  }

  .btn-export-excel:hover {
    background: linear-gradient(135deg, #1e6b3f 0%, #185a34 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 115, 70, 0.3);
  }

  .btn-export-pdf {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .btn-export-pdf:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
  }

  /* Responsive Design */
  @media (max-width: 1200px) {
    .main-filter-row {
      flex-direction: column;
    }

    .filter-inputs {
      width: 100%;
    }

    .filter-actions {
      width: 100%;
      justify-content: flex-end;
    }

    .filter-rekapan-grid {
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
  }

  @media (max-width: 768px) {
    .filter-item {
      min-width: 100%;
    }

    .filter-item.filter-search {
      min-width: 100%;
    }

    .filter-actions {
      width: 100%;
      flex-direction: column;
    }

    .btn-filter-primary,
    .btn-filter-reset {
      width: 100%;
    }

    .modal-content-modern {
      width: 95%;
      max-height: 95vh;
    }

    .column-selection-grid {
      grid-template-columns: 1fr;
    }
  }

  /* Print styles for PDF */
  @media print {
    .no-print {
      display: none !important;
    }

    .table-container {
      box-shadow: none !important;
      border: 1px solid #ddd !important;
    }

    .vendor-header-row td {
      background: #083E40 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .subtotal-row, .grand-total-row {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
  }
</style>

<h2>{{ $title }}</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Total Dokumen</div>
          <div class="stat-value">{{ $statistics['total_documents'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon total">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Belum Siap Bayar</div>
          <div class="stat-value">{{ $statistics['by_status']['belum_dibayar'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai_by_status']['belum_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon belum">
          <i class="fa-solid fa-clock"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Siap Dibayar</div>
          <div class="stat-value">{{ $statistics['by_status']['siap_dibayar'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai_by_status']['siap_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon siap">
          <i class="fa-solid fa-hourglass-half"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Sudah Dibayar</div>
          <div class="stat-value">{{ $statistics['by_status']['sudah_dibayar'] }}</div>
          <div class="stat-nilai">Rp {{ number_format($statistics['total_nilai_by_status']['sudah_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon sudah">
          <i class="fa-solid fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
  <form method="GET" action="{{ route('pembayaran.rekapan') }}" id="filterForm">
    <!-- Main Filter Row -->
    <div class="main-filter-row">
      <div class="filter-inputs">
        <div class="filter-item">
          <label for="status_pembayaran" class="filter-label">Status Pembayaran</label>
          <select name="status_pembayaran" id="status_pembayaran" class="form-select-modern">
            <option value="">Semua Status</option>
            <option value="belum_siap_dibayar" {{ $selectedStatus == 'belum_siap_dibayar' ? 'selected' : '' }}>Belum Siap Dibayar</option>
            <option value="siap_dibayar" {{ $selectedStatus == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
            <option value="sudah_dibayar" {{ $selectedStatus == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
          </select>
        </div>
        <div class="filter-item">
          <label for="year" class="filter-label">Tahun</label>
          <select name="year" id="year" class="form-select-modern">
            <option value="">Semua Tahun</option>
            @foreach($availableYears as $yr)
              <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
            @if($availableYears->isEmpty())
              <option value="{{ date('Y') }}">{{ date('Y') }}</option>
            @endif
          </select>
        </div>
        <div class="filter-item">
          <label for="month" class="filter-label">Bulan</label>
          <select name="month" id="month" class="form-select-modern">
            <option value="">Semua Bulan</option>
            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
              <option value="{{ $index + 1 }}" {{ $selectedMonth == ($index + 1) ? 'selected' : '' }}>{{ $bulan }}</option>
            @endforeach
          </select>
        </div>
        <div class="filter-item filter-search">
          <label for="search" class="filter-label">Cari Dokumen</label>
          <div class="search-input-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="search" id="search" class="form-control-modern"
                   placeholder="Nomor agenda, SPP, penerima..."
                   value="{{ $search }}">
          </div>
        </div>
      </div>
      <div class="filter-actions">
        <button type="submit" class="btn-filter-primary">
          <i class="fa-solid fa-filter me-2"></i>Filter
        </button>
        <a href="{{ route('pembayaran.rekapan') }}" class="btn-filter-reset">
          <i class="fa-solid fa-rotate-left me-2"></i>Reset
        </a>
      </div>
    </div>

    <!-- Rekapan Configuration Section -->
    <div class="rekapan-config-section">
      <!-- Rekapan Table Toggle -->
      <div class="rekapan-toggle-wrapper">
        <div class="rekapan-toggle-header">
          <input type="checkbox" id="enableRekapanTable" {{ $mode == 'rekapan_table' ? 'checked' : '' }}>
          <label for="enableRekapanTable">
            <i class="fa-solid fa-table-columns me-2"></i>
            Tampilkan Tabel Rekapan (Grouped by Vendor)
          </label>
        </div>
        <button type="button" class="btn-column-modal" onclick="openColumnModal()" id="openColumnModalBtn" style="display: {{ $mode == 'rekapan_table' ? 'inline-flex' : 'none' }};">
          <i class="fa-solid fa-table-columns me-2"></i>Atur Kolom Tabel
        </button>
      </div>

      <!-- Collapsible Filter Rekapan Panel -->
      <div class="filter-rekapan-panel {{ $mode == 'rekapan_table' ? 'show' : '' }}" id="filterRekapanPanel">
        <div class="panel-header" onclick="toggleFilterPanel()">
          <div class="panel-title">
            <i class="fa-solid fa-sliders me-2"></i>
            <span>Pilihan Filter Rekapan Detail</span>
          </div>
          <i class="fa-solid fa-chevron-down panel-icon" id="filterPanelIcon"></i>
        </div>
        <div class="panel-content" id="filterPanelContent">
          <div class="filter-rekapan-grid">
            <div class="filter-rekapan-item">
              <label for="filter_dibayar_kepada_column" class="filter-label">Dibayar Kepada (Vendor)</label>
              <select name="filter_dibayar_kepada_column" id="filter_dibayar_kepada_column" class="form-select-modern">
                <option value="">-- Pilih Vendor (Opsional) --</option>
                @if(isset($availableDibayarKepada) && $availableDibayarKepada->count() > 0)
                    @foreach($availableDibayarKepada as $id => $nama)
                        <option value="{{ $id }}" {{ request('filter_dibayar_kepada_column') == $id ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                @else
                    <option value="">Tidak ada data vendor</option>
                @endif
              </select>
            </div>
            
            <div class="filter-rekapan-item">
              <label for="filter_kategori_column" class="filter-label">Kategori</label>
              <select name="filter_kategori_column" id="filter_kategori_column" class="form-select-modern">
                <option value="">-- Pilih Kategori (Opsional) --</option>
                @if(isset($availableKategori) && $availableKategori->count() > 0)
                    @foreach($availableKategori as $id => $nama)
                        <option value="{{ $id }}" {{ request('filter_kategori_column') == $id ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                @else
                    <option value="">Tidak ada data kategori</option>
                @endif
              </select>
            </div>
            
            <div class="filter-rekapan-item">
              <label for="filter_jenis_dokumen_column" class="filter-label">Jenis Dokumen</label>
              <select name="filter_jenis_dokumen_column" id="filter_jenis_dokumen_column" class="form-select-modern">
                <option value="">-- Pilih Jenis Dokumen (Opsional) --</option>
                @if(isset($availableJenisDokumen) && request('filter_kategori_column'))
                    @php
                        $selectedKategoriId = request('filter_kategori_column');
                        $filteredJenisDokumen = \App\Models\JenisDokumen::where('id_kategori', $selectedKategoriId)->get();
                    @endphp
                    @if($filteredJenisDokumen->count() > 0)
                        @foreach($filteredJenisDokumen as $jenisDokumen)
                            <option value="{{ $jenisDokumen->id_jenis_dokumen }}" {{ request('filter_jenis_dokumen_column') == $jenisDokumen->id_jenis_dokumen ? 'selected' : '' }}>
                                {{ $jenisDokumen->nama_jenis_dokumen }}
                            </option>
                        @endforeach
                    @else
                        <option value="">Tidak ada jenis dokumen untuk kategori ini</option>
                    @endif
                @elseif(isset($availableJenisDokumen) && $availableJenisDokumen->count() > 0)
                    @foreach($availableJenisDokumen as $id => $nama)
                        <option value="{{ $id }}" {{ request('filter_jenis_dokumen_column') == $id ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                @else
                    <option value="">Tidak ada data jenis dokumen</option>
                @endif
              </select>
            </div>
            
            <div class="filter-rekapan-item">
              <label for="filter_jenis_sub_pekerjaan_column" class="filter-label">Jenis Sub Pekerjaan</label>
              <select name="filter_jenis_sub_pekerjaan_column" id="filter_jenis_sub_pekerjaan_column" class="form-select-modern">
                <option value="">-- Pilih Jenis Sub Pekerjaan (Opsional) --</option>
                @if(isset($availableJenisSubPekerjaan) && $availableJenisSubPekerjaan->count() > 0)
                    @foreach($availableJenisSubPekerjaan as $id => $nama)
                        <option value="{{ $id }}" {{ request('filter_jenis_sub_pekerjaan_column') == $id ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                @else
                    <option value="">Tidak ada data jenis sub pekerjaan</option>
                @endif
              </select>
            </div>
            
            <div class="filter-rekapan-item">
              <label for="filter_jenis_pembayaran_column" class="filter-label">Jenis Pembayaran</label>
              <select name="filter_jenis_pembayaran_column" id="filter_jenis_pembayaran_column" class="form-select-modern">
                <option value="">-- Pilih Jenis Pembayaran (Opsional) --</option>
                @if(isset($availableJenisPembayaran) && $availableJenisPembayaran->count() > 0)
                    @foreach($availableJenisPembayaran as $id => $nama)
                        <option value="{{ $id }}" {{ request('filter_jenis_pembayaran_column') == $id ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                @else
                    <option value="">Tidak ada data jenis pembayaran</option>
                @endif
              </select>
            </div>
            
            <div class="filter-rekapan-item">
              <label for="filter_jenis_kebuns_column" class="filter-label">Kebun</label>
              <select name="filter_jenis_kebuns_column" id="filter_jenis_kebuns_column" class="form-select-modern">
                <option value="">-- Pilih Kebun (Opsional) --</option>
                @if(isset($availableKebuns) && $availableKebuns->count() > 0)
                    @foreach($availableKebuns as $id => $nama)
                        <option value="{{ $id }}" {{ request('filter_jenis_kebuns_column') == $id ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                @else
                    <option value="">Tidak ada data kebun</option>
                @endif
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<!-- Modal: Column Selection -->
<div class="column-modal" id="columnModal">
  <div class="modal-content-modern">
    <div class="modal-header-modern">
      <h3>
        <i class="fa-solid fa-table-columns me-2"></i>
        Atur Kolom Tabel
      </h3>
      <button type="button" class="modal-close" onclick="closeColumnModal()">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
    <div class="modal-body-modern">
      <div class="modal-actions-top">
        <button type="button" class="btn-select-all" onclick="selectAllColumns()">
          <i class="fa-solid fa-check-double me-2"></i>Pilih Semua
        </button>
        <button type="button" class="btn-deselect-all" onclick="deselectAllColumns()">
          <i class="fa-solid fa-square me-2"></i>Hapus Semua
        </button>
      </div>
      <div class="column-selection-grid" id="columnsGrid">
        @foreach($availableColumns as $key => $label)
          <div class="column-checkbox-item-modern {{ in_array($key, $selectedColumns) ? 'selected' : '' }}" data-column="{{ $key }}">
            <input type="checkbox"
                   id="col_{{ $key }}"
                   value="{{ $key }}"
                   {{ in_array($key, $selectedColumns) ? 'checked' : '' }}>
            <label for="col_{{ $key }}">{{ $label }}</label>
            <span class="order-badge-modern">{{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}</span>
          </div>
        @endforeach
      </div>
      <div class="selected-preview-modern {{ count($selectedColumns) > 0 ? 'show' : '' }}" id="selectedPreview">
        <div class="preview-header">
          <i class="fa-solid fa-list-ol me-2"></i>
          <strong>Urutan Kolom Terpilih:</strong>
        </div>
        <div class="preview-content" id="selectedColumnsList">
          @foreach($selectedColumns as $col)
            <span class="preview-badge">{{ $availableColumns[$col] ?? $col }}</span>
          @endforeach
        </div>
      </div>
    </div>
    <div class="modal-footer-modern">
      <button type="button" class="btn-modal-cancel" onclick="closeColumnModal()">
        <i class="fa-solid fa-times me-2"></i>Batal
      </button>
      <button type="button" class="btn-modal-save" onclick="saveColumnSelection()">
        <i class="fa-solid fa-save me-2"></i>Simpan
      </button>
    </div>
  </div>
</div>

<!-- Rekapan Table Content Area -->
<div id="rekapanContentArea" style="display: {{ $mode == 'rekapan_table' ? 'block' : 'none' }};">
@if($mode == 'rekapan_table')
  @if($rekapanByVendor && count($selectedColumns) > 0)
  <div class="table-container" id="rekapanTableContainer">
    <div class="table-header">
      <div>
        <h5 style="margin-bottom: 5px;">
          <i class="fa-solid fa-table me-2"></i>
          Tabel Rekapan per Vendor
        </h5>
        <span class="text-muted">Total {{ $rekapanByVendor->count() }} vendor | {{ $statistics['total_documents'] }} dokumen</span>
      </div>
      <div class="export-buttons no-print">
        <button type="button" class="btn-export btn-export-excel" onclick="exportToExcel()">
          <i class="fa-solid fa-file-excel"></i> Export Excel
        </button>
        <button type="button" class="btn-export btn-export-pdf" onclick="exportToPDFWithDelay()">
          <i class="fa-solid fa-file-pdf"></i> Export PDF
        </button>
      </div>
    </div>

    <div class="table-responsive">
      @php
        $grandTotalNilai = 0;
        $grandTotalBelum = 0;
        $grandTotalSiap = 0;
        $grandTotalSudah = 0;
        $globalNo = 0;

        // Find the index of first value column (calculated once)
        $valueColumns = ['nilai_rupiah', 'nilai_belum_siap_bayar', 'nilai_siap_bayar', 'nilai_sudah_dibayar'];
        $firstValueIndex = null;
        foreach($selectedColumns as $idx => $col) {
          if (in_array($col, $valueColumns)) {
            $firstValueIndex = $idx;
            break;
          }
        }
        // +1 for the "No" column
        $colspanCount = $firstValueIndex !== null ? $firstValueIndex + 1 : count($selectedColumns) + 1;
        $totalColumns = count($selectedColumns) + 1; // +1 for No column
      @endphp

      <table class="table table-hover mb-0" id="rekapanTable">
        <thead>
          <tr>
            <th style="width: 50px; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; font-weight: 600; border: none; text-align: center;">No</th>
            @foreach($selectedColumns as $col)
              <th style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; font-weight: 600; border: none; text-align: center;">{{ $availableColumns[$col] ?? $col }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($rekapanByVendor as $vendorData)
            @php
              $grandTotalNilai += $vendorData['total_nilai'];
              $grandTotalBelum += $vendorData['total_belum_dibayar'];
              $grandTotalSiap += $vendorData['total_siap_dibayar'];
              $grandTotalSudah += $vendorData['total_sudah_dibayar'];
            @endphp

            <!-- Vendor Header Row - Skip if vendor is "Tidak Diketahui" -->
            @if($vendorData['vendor'] !== 'Tidak Diketahui')
            <tr class="vendor-header-row">
              <td colspan="{{ $totalColumns }}" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; font-weight: 700; padding: 12px 15px;">
                <span class="vendor-name">{{ $vendorData['vendor'] }}</span>
                <span class="vendor-stats no-export" style="float: right; font-weight: 400; font-size: 12px;">
                  <i class="fa-solid fa-building me-2"></i>{{ $vendorData['count'] }} dok | Rp {{ number_format($vendorData['total_nilai'], 0, ',', '.') }}
                </span>
              </td>
            </tr>
            @endif

            <!-- Document Rows -->
            @foreach($vendorData['documents'] as $index => $doc)
              @php $globalNo++; @endphp
              <tr>
                <td style="text-align: center; vertical-align: middle;">{{ $globalNo }}</td>
                @foreach($selectedColumns as $col)
                  @if($col === 'umur_dokumen_tanggal_ba')
                    {{-- Handle umur_dokumen_tanggal_ba secara khusus --}}
                    @if(isset($doc->computed_status) && $doc->computed_status === 'sudah_dibayar')
                        {{-- Jika sudah dibayar, tampilkan 0 --}}
                        <td style="background: #28a745; color: white; font-weight: 700; text-align: center; padding: 12px;">
                            0 HARI
                        </td>
                    @elseif($doc->tanggal_berita_acara)
                        @php
                            $tanggalBa = \Carbon\Carbon::parse($doc->tanggal_berita_acara)->startOfDay();
                            $hariIni = \Carbon\Carbon::now()->startOfDay();
                            $umurDokumenBa = $tanggalBa->lte($hariIni) ? $tanggalBa->diffInDays($hariIni) : 0;
                        @endphp
                        
                        @if ($umurDokumenBa <= 90)
                            <td style="background: #28a745; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenBa }} HARI
                            </td>
                        @elseif ($umurDokumenBa >= 91 && $umurDokumenBa <= 180)
                            <td style="background: #e5ff00ff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenBa }} HARI
                            </td>
                        @elseif ($umurDokumenBa >= 181 && $umurDokumenBa <= 270)
                            <td style="background: #10ffafff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenBa }} HARI
                            </td>
                        @elseif ($umurDokumenBa >= 271 && $umurDokumenBa <= 365)
                            <td style="background: #0099ffff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenBa }} HARI
                            </td>
                        @else 
                            <td style="background: #ff0000ff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenBa }} HARI
                            </td>
                        @endif
                    @else
                        <td style="text-align: center; padding: 12px;">-</td>
                    @endif
                  @elseif($col === 'umur_dokumen_tanggal_spp')
                    {{-- Handle umur_dokumen_tanggal_spp secara khusus --}}
                    @if(isset($doc->computed_status) && $doc->computed_status === 'sudah_dibayar')
                        {{-- Jika sudah dibayar, tampilkan 0 --}}
                        <td style="background: #28a745; color: white; font-weight: 700; text-align: center; padding: 12px;">
                            0 HARI
                        </td>
                    @elseif($doc->tanggal_spp)
                        @php
                            $tanggalSpp = \Carbon\Carbon::parse($doc->tanggal_spp)->startOfDay();
                            $hariIni = \Carbon\Carbon::now()->startOfDay();
                            $umurDokumenSpp = $tanggalSpp->lte($hariIni) ? $tanggalSpp->diffInDays($hariIni) : 0;
                        @endphp
                        
                        @if ($umurDokumenSpp <= 90)
                            <td style="background: #28a745; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenSpp }} HARI
                            </td>
                        @elseif ($umurDokumenSpp >= 91 && $umurDokumenSpp <= 180)
                            <td style="background: #e5ff00ff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenSpp }} HARI
                            </td>
                        @elseif ($umurDokumenSpp >= 181 && $umurDokumenSpp <= 270)
                            <td style="background: #10ffafff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenSpp }} HARI
                            </td>
                        @elseif ($umurDokumenSpp >= 271 && $umurDokumenSpp <= 365)
                            <td style="background: #0099ffff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenSpp }} HARI
                            </td>
                        @else 
                            <td style="background: #ff0000ff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenSpp }} HARI
                            </td>
                        @endif
                    @else
                        <td style="text-align: center; padding: 12px;">-</td>
                    @endif
                  @elseif($col === 'umur_dokumen_tanggal_masuk')
                    {{-- Handle umur_dokumen_tanggal_masuk secara khusus --}}
                    @if(isset($doc->computed_status) && $doc->computed_status === 'sudah_dibayar')
                        {{-- Jika sudah dibayar, tampilkan 0 --}}
                        <td style="background: #28a745; color: white; font-weight: 700; text-align: center; padding: 12px;">
                            0 HARI
                        </td>
                    @elseif($doc->tanggal_masuk)
                        @php
                            $tanggalMasuk = \Carbon\Carbon::parse($doc->tanggal_masuk)->startOfDay();
                            $hariIni = \Carbon\Carbon::now()->startOfDay();
                            $umurDokumenMasuk = $tanggalMasuk->lte($hariIni) ? $tanggalMasuk->diffInDays($hariIni) : 0;
                        @endphp
                        
                        @if ($umurDokumenMasuk <= 90)
                            <td style="background: #28a745; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenMasuk }} HARI
                            </td>
                        @elseif ($umurDokumenMasuk >= 91 && $umurDokumenMasuk <= 180)
                            <td style="background: #e5ff00ff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenMasuk }} HARI
                            </td>
                        @elseif ($umurDokumenMasuk >= 181 && $umurDokumenMasuk <= 270)
                            <td style="background: #10ffafff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenMasuk }} HARI
                            </td>
                        @elseif ($umurDokumenMasuk >= 271 && $umurDokumenMasuk <= 365)
                            <td style="background: #0099ffff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenMasuk }} HARI
                            </td>
                        @else 
                            <td style="background: #ff0000ff; color: white; font-weight: 700; text-align: center; padding: 12px;">
                                {{ $umurDokumenMasuk }} HARI
                            </td>
                        @endif
                    @else
                        <td style="text-align: center; padding: 12px;">-</td>
                    @endif
                  @else
                    {{-- Handle kolom lainnya dengan switch seperti biasa --}}
                    <td style="text-align: center; vertical-align: middle;">
                      @switch($col)
                        @case('nomor_agenda')
                          <strong>{{ $doc->nomor_agenda }}</strong>
                          @break
                        @case('dibayar_kepada')
                          {{ $doc->dibayar_kepada ?? '-' }}
                          @break
                        @case('jenis_pembayaran')
                          {{ $doc->jenis_pembayaran ?? '-' }}
                          @break
                        @case('jenis_sub_pekerjaan')
                          {{ $doc->jenis_sub_pekerjaan ?? '-' }}
                          @break
                        @case('kategori')
                            {{ $doc->kategori ?? '-' }}
                            @break
                        @case('jenis_dokumen')
                            {{ $doc->jenis_dokumen ?? '-' }}
                            @break
                        @case('kebun')
                            {{ $doc->kebun ?? $doc->nama_kebuns ?? '-' }}
                            @break
                        @case('uraian_spp')
                            {{ $doc->uraian_spp ?? '-' }}
                            @break
                        @case('nomor_mirror')
                          {{ $doc->nomor_mirror ?? '-' }}
                          @break
                        @case('nomor_spp')
                          {{ $doc->nomor_spp ?? '-' }}
                          @break
                        @case('tanggal_spp')
                          {{ $doc->tanggal_spp ? $doc->tanggal_spp->format('d/m/Y') : '-' }}
                          @break
                        @case('tanggal_berita_acara')
                          {{ $doc->tanggal_berita_acara ? $doc->tanggal_berita_acara->format('d/m/Y') : '-' }}
                          @break
                        @case('no_berita_acara')
                          {{ $doc->no_berita_acara ?? '-' }}
                          @break
                        @case('tanggal_berakhir_ba')
                          {{ $doc->tanggal_berakhir_ba ?? '-' }}
                          @break
                        @case('no_spk')
                          {{ $doc->no_spk ?? '-' }}
                          @break
                        @case('tanggal_spk')
                          {{ $doc->tanggal_spk ? $doc->tanggal_spk->format('d/m/Y') : '-' }}
                          @break
                        @case('tanggal_berakhir_spk')
                          {{ $doc->tanggal_berakhir_spk ? $doc->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                          @break
                        @case('nilai_rupiah')
                          <strong>Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                          @break
                        @case('nilai_belum_siap_bayar')
                          @if($doc->computed_status == 'belum_siap_dibayar')
                            <span class="text-warning">Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                          @else
                            -
                          @endif
                          @break
                        @case('nilai_siap_bayar')
                          @if($doc->computed_status == 'siap_dibayar')
                            <span class="text-info">Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                          @else
                            -
                          @endif
                          @break
                        @case('nilai_sudah_dibayar')
                          @if($doc->computed_status == 'sudah_dibayar')
                            <span class="text-success">Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                          @else
                            -
                          @endif
                          @break
                        @default
                          {{ $doc->$col ?? '-' }}
                      @endswitch
                    </td>
                  @endif
                @endforeach
              </tr>
          @endforeach

            <!-- Subtotal Row -->
            <tr class="subtotal-row" style="background: #f8f9fa; font-weight: 600;">
              <td colspan="{{ $colspanCount }}" class="text-end" style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;">
                <strong>Subtotal {{ Str::limit($vendorData['vendor'], 30) }}:</strong>
              </td>
              @foreach($selectedColumns as $idx => $col)
                @if($firstValueIndex !== null && $idx >= $firstValueIndex)
                  @if($col == 'nilai_rupiah')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong>Rp {{ number_format($vendorData['total_nilai'], 0, ',', '.') }}</strong></td>
                  @elseif($col == 'nilai_belum_siap_bayar')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong class="text-warning">Rp {{ number_format($vendorData['total_belum_dibayar'], 0, ',', '.') }}</strong></td>
                  @elseif($col == 'nilai_siap_bayar')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong class="text-info">Rp {{ number_format($vendorData['total_siap_dibayar'], 0, ',', '.') }}</strong></td>
                  @elseif($col == 'nilai_sudah_dibayar')
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"><strong class="text-success">Rp {{ number_format($vendorData['total_sudah_dibayar'], 0, ',', '.') }}</strong></td>
                  @elseif(in_array($col, ['umur_dokumen_tanggal_masuk', 'umur_dokumen_tanggal_spp', 'umur_dokumen_tanggal_ba']))
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;">-</td>
                  @else
                    <td style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;"></td>
                  @endif
                @endif
              @endforeach
            </tr>

            <!-- Empty Row Separator -->
            <tr class="separator-row">
              <td colspan="{{ $totalColumns }}" style="height: 10px; background: #fff; border: none;"></td>
            </tr>
          @endforeach

          <!-- Grand Total Row -->
          <tr class="grand-total-row" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); font-weight: 700;">
            <td colspan="{{ $colspanCount }}" class="text-end" style="border-top: 3px solid #28a745; padding: 15px;">
              <strong style="font-size: 14px;"><i class="fa-solid fa-calculator me-2"></i>GRAND TOTAL:</strong>
            </td>
            @foreach($selectedColumns as $idx => $col)
              @if($firstValueIndex !== null && $idx >= $firstValueIndex)
                @if($col == 'nilai_rupiah')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong style="font-size: 14px;">Rp {{ number_format($grandTotalNilai, 0, ',', '.') }}</strong></td>
                @elseif($col == 'nilai_belum_siap_bayar')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong class="text-warning" style="font-size: 14px;">Rp {{ number_format($grandTotalBelum, 0, ',', '.') }}</strong></td>
                @elseif($col == 'nilai_siap_bayar')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong class="text-info" style="font-size: 14px;">Rp {{ number_format($grandTotalSiap, 0, ',', '.') }}</strong></td>
                @elseif($col == 'nilai_sudah_dibayar')
                  <td style="border-top: 3px solid #28a745; padding: 15px;"><strong class="text-success" style="font-size: 14px;">Rp {{ number_format($grandTotalSudah, 0, ',', '.') }}</strong></td>
                @elseif(in_array($col, ['umur_dokumen_tanggal_masuk', 'umur_dokumen_tanggal_spp', 'umur_dokumen_tanggal_ba']))
                  <td style="border-top: 3px solid #28a745; padding: 15px;">-</td>
                @else
                  <td style="border-top: 3px solid #28a745; padding: 15px;"></td>
                @endif
              @endif
            @endforeach
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  @else
    <!-- Empty State: No Columns Selected -->
    <div class="table-container">
      <div class="table-header">
        <h5>
          <i class="fa-solid fa-info-circle me-2"></i>
          Pilih Kolom untuk Menampilkan Tabel Rekapan
        </h5>
      </div>
      <div style="padding: 40px; text-align: center; color: #6b7280;">
        <i class="fa-solid fa-table-columns fa-3x mb-3" style="color: #d1d5db;"></i>
        <p style="font-size: 16px; margin-bottom: 10px;">
          <strong>Belum ada kolom yang dipilih</strong>
        </p>
        <p style="font-size: 14px; margin-bottom: 20px;">
          Klik tombol <strong>"Atur Kolom Tabel"</strong> di atas untuk memilih kolom yang ingin ditampilkan dalam tabel rekapan.
        </p>
        <button type="button" class="btn-column-modal" onclick="openColumnModal()">
          <i class="fa-solid fa-table-columns me-2"></i>Atur Kolom Tabel
        </button>
      </div>
    </div>
  @endif
@endif
</div>

@if($mode != 'rekapan_table')
  <!-- Normal Table -->
  <div class="table-container">
    <div class="table-header">
      <h5>
        <i class="fa-solid fa-list me-2"></i>
        Daftar Dokumen
      </h5>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Nomor SPP</th>
            <th>Tgl Diterima</th>
            <th>Dibayar Kepada</th>
            <th>Nilai Rupiah</th>
            <th>Status</th>
            <th>Tgl Dibayar</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            <tr>
              <td style="text-align: center;">{{ $dokumens->firstItem() + $index }}</td>
              <td>
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br><small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              </td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ $dokumen->sent_to_pembayaran_at ? $dokumen->sent_to_pembayaran_at->format('d/m/Y') : '-' }}</td>
              <td>{{ Str::limit($dokumen->dibayar_kepada, 25) ?? '-' }}</td>
              <td><strong>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong></td>
              <td style="text-align: center;">
                @if($dokumen->computed_status == 'sudah_dibayar')
                  <span class="badge-status badge-sudah">Sudah Dibayar</span>
                @elseif($dokumen->computed_status == 'siap_dibayar')
                  <span class="badge-status badge-siap">Siap Dibayar</span>
                @else
                  <span class="badge-status badge-belum">Belum Siap Dibayar</span>
                @endif
              </td>
              <td>{{ $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Tidak ada data dokumen.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($dokumens->hasPages())
      <div class="pagination-wrapper">
        <div class="text-muted" style="font-size: 13px;">
          Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} dokumen
        </div>
        <div class="pagination">
          {{-- Previous Page Link --}}
          @if($dokumens->onFirstPage())
            <button class="btn-chevron" disabled>
              <i class="fa-solid fa-chevron-left"></i>
            </button>
          @else
            <a href="{{ $dokumens->previousPageUrl() }}">
              <button class="btn-chevron">
                <i class="fa-solid fa-chevron-left"></i>
              </button>
            </a>
          @endif

          {{-- Pagination Elements --}}
          @if($dokumens->hasPages())
            {{-- First page --}}
            @if($dokumens->currentPage() > 3)
              <a href="{{ $dokumens->url(1) }}">
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
                <a href="{{ $dokumens->url($i) }}">
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
              <a href="{{ $dokumens->url($dokumens->lastPage()) }}">
                <button>{{ $dokumens->lastPage() }}</button>
              </a>
            @endif
          @endif

          {{-- Next Page Link --}}
          @if($dokumens->hasMorePages())
            <a href="{{ $dokumens->nextPageUrl() }}">
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
      </div>
    @endif
  </div>
@endif

<!-- ExcelJS for Styled Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<!-- FileSaver for download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<!-- html2pdf for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
// Column Modal Functions - Global scope
window.openColumnModal = function() {
  const modal = document.getElementById('columnModal');
  if (modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    if (typeof initializeModalState === 'function') {
      initializeModalState();
    }
  }
};

window.closeColumnModal = function() {
  const modal = document.getElementById('columnModal');
  if (modal) {
    modal.classList.remove('show');
    document.body.style.overflow = '';
  }
};

window.selectAllColumns = function() {
  document.querySelectorAll('.column-checkbox-item-modern input[type="checkbox"]').forEach(cb => {
    const columnKey = cb.value;
    const item = cb.closest('.column-checkbox-item-modern');
    if (!cb.checked) {
      cb.checked = true;
      item.classList.add('selected');
      if (!selectionOrder.includes(columnKey)) {
        selectionOrder.push(columnKey);
      }
    }
  });
  if (typeof updateOrderBadges === 'function') updateOrderBadges();
  if (typeof updatePreview === 'function') updatePreview();
};

window.deselectAllColumns = function() {
  document.querySelectorAll('.column-checkbox-item-modern input[type="checkbox"]').forEach(cb => {
    const columnKey = cb.value;
    const item = cb.closest('.column-checkbox-item-modern');
    cb.checked = false;
    item.classList.remove('selected');
    selectionOrder = selectionOrder.filter(key => key !== columnKey);
  });
  if (typeof updateOrderBadges === 'function') updateOrderBadges();
  if (typeof updatePreview === 'function') updatePreview();
};

window.saveColumnSelection = function() {
  // Get current checked checkboxes from modal
  const checkedCheckboxes = document.querySelectorAll('.column-checkbox-item-modern input[type="checkbox"]:checked');
  
  // Get checked column keys
  const checkedKeys = Array.from(checkedCheckboxes).map(checkbox => {
    const item = checkbox.closest('.column-checkbox-item-modern');
    return item ? item.dataset.column : null;
  }).filter(key => key !== null);
  
  // Preserve order from selectionOrder, only include checked columns
  // This maintains the user's selected order
  const orderedSelection = selectionOrder.filter(key => checkedKeys.includes(key));
  
  // Add any newly checked columns that weren't in selectionOrder (shouldn't happen, but just in case)
  checkedKeys.forEach(key => {
    if (!orderedSelection.includes(key)) {
      orderedSelection.push(key);
    }
  });
  
  // Update selectionOrder with the ordered selection
  selectionOrder = orderedSelection;
  
  console.log('Selected columns (preserving order):', selectionOrder);
  
  if (selectionOrder.length === 0) {
    alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
    return;
  }
  
  if (!filterForm) {
    alert('Form tidak ditemukan!');
    return;
  }
  
  // Remove existing column inputs
  document.querySelectorAll('input[name="columns[]"]').forEach(input => {
    if (input.type === 'hidden') {
      input.remove();
    }
  });
  
  // Add hidden inputs for each selected column in order
  selectionOrder.forEach(key => {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'columns[]';
    hiddenInput.value = key;
    filterForm.appendChild(hiddenInput);
  });
  
  // Ensure mode is set to rekapan_table
  // Remove existing mode input if any
  const existingModeInput = filterForm.querySelector('input[name="mode"]');
  if (existingModeInput) {
    existingModeInput.remove();
  }
  
  // Add mode input
  const modeInput = document.createElement('input');
  modeInput.type = 'hidden';
  modeInput.name = 'mode';
  modeInput.value = 'rekapan_table';
  filterForm.appendChild(modeInput);
  
  // Debug: Log all form inputs
  const formData = new FormData(filterForm);
  console.log('Form data before submit:');
  for (let [key, value] of formData.entries()) {
    console.log(key, ':', value);
  }
  
  // Verify columns are in form
  const columnInputs = filterForm.querySelectorAll('input[name="columns[]"]');
  console.log('Column inputs in form:', columnInputs.length);
  columnInputs.forEach((input, index) => {
    console.log(`Column ${index + 1}:`, input.value);
  });
  
  if (columnInputs.length === 0) {
    alert('Error: Tidak ada kolom yang terkirim ke form. Silakan coba lagi.');
    console.error('No column inputs found in form!');
    return;
  }
  
  closeColumnModal();
  
  // Submit form to load rekapan table
  console.log('Submitting form...');
  filterForm.submit();
};

window.initializeModalState = function() {
  console.log('Initializing modal state with selectionOrder:', selectionOrder);
  
  document.querySelectorAll('.column-checkbox-item-modern').forEach(item => {
    const columnKey = item.dataset.column;
    const checkbox = item.querySelector('input[type="checkbox"]');
    if (selectionOrder.includes(columnKey)) {
      checkbox.checked = true;
      item.classList.add('selected');
    } else {
      checkbox.checked = false;
      item.classList.remove('selected');
    }
  });
  if (typeof updateOrderBadges === 'function') updateOrderBadges();
  if (typeof updatePreview === 'function') updatePreview();
  
  // Setup event listeners for checkboxes in modal
  setupModalCheckboxListeners();
};

// Setup event listeners for checkboxes in modal
function setupModalCheckboxListeners() {
  const columnsGrid = document.getElementById('columnsGrid');
  if (!columnsGrid) {
    console.log('columnsGrid not found');
    return;
  }
  
  // Remove existing listeners to avoid duplicates
  const newColumnsGrid = columnsGrid.cloneNode(true);
  columnsGrid.parentNode.replaceChild(newColumnsGrid, columnsGrid);
  
  // Add click event listener
  newColumnsGrid.addEventListener('click', function(e) {
    const item = e.target.closest('.column-checkbox-item-modern');
    if (!item) return;

    const checkbox = item.querySelector('input[type="checkbox"]');
    const columnKey = item.dataset.column;

    // Toggle checkbox if click wasn't directly on it
    if (e.target !== checkbox && e.target.tagName !== 'LABEL') {
      checkbox.checked = !checkbox.checked;
    }

    if (checkbox.checked) {
      // Add to selection order if not already there
      if (!selectionOrder.includes(columnKey)) {
        selectionOrder.push(columnKey);
        console.log('Added column to selectionOrder:', columnKey, 'Current order:', selectionOrder);
      }
      item.classList.add('selected');
    } else {
      // Remove from selection order
      selectionOrder = selectionOrder.filter(key => key !== columnKey);
      console.log('Removed column from selectionOrder:', columnKey, 'Current order:', selectionOrder);
      item.classList.remove('selected');
    }

    if (typeof updateOrderBadges === 'function') updateOrderBadges();
    if (typeof updatePreview === 'function') updatePreview();
  });
  
  // Also listen to direct checkbox changes
  newColumnsGrid.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    // Use event delegation instead of direct listener to avoid issues
    checkbox.addEventListener('change', function(e) {
      e.stopPropagation(); // Prevent event bubbling
      const item = this.closest('.column-checkbox-item-modern');
      if (!item) return;
      
      const columnKey = item.dataset.column;
      console.log('Checkbox change event:', columnKey, 'checked:', this.checked);
      
      if (this.checked) {
        if (!selectionOrder.includes(columnKey)) {
          selectionOrder.push(columnKey);
          console.log('Checkbox changed - Added column:', columnKey, 'Current order:', selectionOrder);
        }
        item.classList.add('selected');
      } else {
        selectionOrder = selectionOrder.filter(key => key !== columnKey);
        console.log('Checkbox changed - Removed column:', columnKey, 'Current order:', selectionOrder);
        item.classList.remove('selected');
      }
      
      if (typeof updateOrderBadges === 'function') updateOrderBadges();
      if (typeof updatePreview === 'function') updatePreview();
    });
  });
  
  console.log('Modal checkbox listeners setup complete. Total checkboxes:', newColumnsGrid.querySelectorAll('input[type="checkbox"]').length);
}

// Collapsible Panel Function
window.toggleFilterPanel = function() {
  const panelHeader = event.currentTarget;
  const panelContent = document.getElementById('filterPanelContent');
  const panelIcon = document.getElementById('filterPanelIcon');
  
  if (panelHeader && panelContent && panelIcon) {
    panelHeader.classList.toggle('active');
    panelContent.classList.toggle('show');
  }
};

// Global variables - must be declared before DOMContentLoaded
let selectionOrder = [];
let filterForm;

// Export to Excel Function with Styling
async function exportToExcel() {
  const table = document.getElementById('rekapanTable');
  if (!table) {
    alert('Tabel tidak ditemukan!');
    return;
  }

  // Show loading
  const btn = document.querySelector('.btn-export-excel');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  try {
    // Create workbook and worksheet
    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Sistem Pembayaran';
    workbook.created = new Date();

    const worksheet = workbook.addWorksheet('Rekapan Pembayaran', {
      pageSetup: { paperSize: 9, orientation: 'landscape' }
    });

    // Get table data
    const rows = table.querySelectorAll('tr');
    let excelRowIndex = 1;
    const colCount = table.querySelector('thead tr').children.length;

    // Set column widths
    for (let i = 1; i <= colCount; i++) {
      worksheet.getColumn(i).width = i === 1 ? 8 : 22;
    }

    // Process each row
    rows.forEach((row, rowIndex) => {
      // Skip separator rows
      if (row.classList.contains('separator-row')) return;

      const cells = row.querySelectorAll('th, td');
      const excelRow = worksheet.getRow(excelRowIndex);
      let colIndex = 1;

      cells.forEach((cell) => {
        const colspan = parseInt(cell.getAttribute('colspan')) || 1;

        // Get cell text - for vendor header, only get vendor name
        let cellText;
        if (row.classList.contains('vendor-header-row')) {
          const vendorName = cell.querySelector('.vendor-name');
          cellText = vendorName ? vendorName.innerText.trim() : cell.innerText.trim();
        } else {
          // Remove no-export elements from text
          const clone = cell.cloneNode(true);
          const noExport = clone.querySelectorAll('.no-export');
          noExport.forEach(el => el.remove());
          cellText = clone.innerText.trim();
        }

        // Set cell value
        const excelCell = excelRow.getCell(colIndex);
        excelCell.value = cellText;

        // Merge cells if colspan > 1
        if (colspan > 1) {
          worksheet.mergeCells(excelRowIndex, colIndex, excelRowIndex, colIndex + colspan - 1);
        }

        // Apply styles based on row type
        if (row.parentElement.tagName === 'THEAD') {
          // Header row - dark green background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FF083E40' }
            };
            c.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 11 };
            c.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            c.border = {
              top: { style: 'thin', color: { argb: 'FF000000' } },
              left: { style: 'thin', color: { argb: 'FF000000' } },
              bottom: { style: 'thin', color: { argb: 'FF000000' } },
              right: { style: 'thin', color: { argb: 'FF000000' } }
            };
          }
        } else if (row.classList.contains('vendor-header-row')) {
          // Vendor header row - teal background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FF0A4F52' }
            };
            c.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 11 };
            c.alignment = { horizontal: 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'thin', color: { argb: 'FF000000' } },
              left: { style: 'thin', color: { argb: 'FF000000' } },
              bottom: { style: 'thin', color: { argb: 'FF000000' } },
              right: { style: 'thin', color: { argb: 'FF000000' } }
            };
          }
        } else if (row.classList.contains('subtotal-row')) {
          // Subtotal row - light gray background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FFF0F0F0' }
            };
            c.font = { bold: true, size: 11 };
            c.alignment = { horizontal: i === colIndex ? 'right' : 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'medium', color: { argb: 'FFAAAAAA' } },
              left: { style: 'thin', color: { argb: 'FFCCCCCC' } },
              bottom: { style: 'medium', color: { argb: 'FFAAAAAA' } },
              right: { style: 'thin', color: { argb: 'FFCCCCCC' } }
            };
          }
        } else if (row.classList.contains('grand-total-row')) {
          // Grand total row - light green background
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: 'FFD4EDDA' }
            };
            c.font = { bold: true, size: 12, color: { argb: 'FF155724' } };
            c.alignment = { horizontal: i === colIndex ? 'right' : 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'medium', color: { argb: 'FF28A745' } },
              left: { style: 'thin', color: { argb: 'FF28A745' } },
              bottom: { style: 'medium', color: { argb: 'FF28A745' } },
              right: { style: 'thin', color: { argb: 'FF28A745' } }
            };
          }
        } else {
          // Data rows - white/alternating background
          const isEven = excelRowIndex % 2 === 0;
          for (let i = colIndex; i < colIndex + colspan; i++) {
            const c = excelRow.getCell(i);
            c.fill = {
              type: 'pattern',
              pattern: 'solid',
              fgColor: { argb: isEven ? 'FFF9F9F9' : 'FFFFFFFF' }
            };
            c.font = { size: 10 };
            c.alignment = { horizontal: i === 1 ? 'center' : 'left', vertical: 'middle' };
            c.border = {
              top: { style: 'thin', color: { argb: 'FFDDDDDD' } },
              left: { style: 'thin', color: { argb: 'FFDDDDDD' } },
              bottom: { style: 'thin', color: { argb: 'FFDDDDDD' } },
              right: { style: 'thin', color: { argb: 'FFDDDDDD' } }
            };

            // Color for value columns based on content
            if (cellText.includes('Rp')) {
              c.alignment.horizontal = 'right';
              // Check if it's a warning/info/success value
              if (cell.querySelector('.text-warning') || cell.classList.contains('text-warning')) {
                c.font = { size: 10, color: { argb: 'FFCC8800' } }; // Orange/warning
              } else if (cell.querySelector('.text-info') || cell.classList.contains('text-info')) {
                c.font = { size: 10, color: { argb: 'FF17A2B8' } }; // Blue/info
              } else if (cell.querySelector('.text-success') || cell.classList.contains('text-success')) {
                c.font = { size: 10, color: { argb: 'FF28A745' } }; // Green/success
              }
            }
          }
        }

        colIndex += colspan;
      });

      excelRow.commit();
      excelRowIndex++;
    });

    // Set row heights
    worksheet.eachRow((row, rowNumber) => {
      row.height = 22;
    });

    // Freeze first row (header)
    worksheet.views = [{ state: 'frozen', ySplit: 1 }];

    // Generate filename with date
    const today = new Date();
    const dateStr = today.toISOString().slice(0, 10);
    const filename = `Rekapan_Pembayaran_${dateStr}.xlsx`;

    // Generate and download file
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    saveAs(blob, filename);

    btn.innerHTML = originalText;
    btn.disabled = false;

  } catch (error) {
    console.error('Excel Export Error:', error);
    btn.innerHTML = originalText;
    btn.disabled = false;
    alert('Gagal membuat Excel. Silakan coba lagi.');
  }
}

// Export to PDF Function - Server-side approach
function exportToPDF() {
  const btn = document.querySelector('.btn-export-pdf');
  if (!btn) {
    alert('Tombol export tidak ditemukan!');
    return;
  }
  
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  try {
    // Get all filter parameters
    const params = new URLSearchParams();
    params.append('status_pembayaran', '{{ $selectedStatus ?? "" }}');
    params.append('year', '{{ $selectedYear ?? "" }}');
    params.append('month', '{{ $selectedMonth ?? "" }}');
    params.append('search', '{{ $search ?? "" }}');
    params.append('export', 'pdf');
    
    // Determine mode based on whether rekapan table is enabled
    const enableRekapan = document.getElementById('enableRekapanTable');
    const mode = enableRekapan && enableRekapan.checked ? 'rekapan_table' : 'normal';
    params.append('mode', mode);
    
    // Add selected columns if in rekapan_table mode
    @if(!empty($selectedColumns))
      @foreach($selectedColumns as $col)
        params.append('columns[]', '{{ $col }}');
      @endforeach
    @endif

    // Add rekapan detail filters if in rekapan_table mode
    if (mode === 'rekapan_table') {
      const filterDibayarKepada = document.getElementById('filter_dibayar_kepada_column')?.value;
      if (filterDibayarKepada) {
        params.append('filter_dibayar_kepada_column', filterDibayarKepada);
      }

      const filterKategori = document.getElementById('filter_kategori_column')?.value;
      if (filterKategori) {
        params.append('filter_kategori_column', filterKategori);
      }

      const filterJenisDokumen = document.getElementById('filter_jenis_dokumen_column')?.value;
      if (filterJenisDokumen) {
        params.append('filter_jenis_dokumen_column', filterJenisDokumen);
      }

      const filterJenisSubPekerjaan = document.getElementById('filter_jenis_sub_pekerjaan_column')?.value;
      if (filterJenisSubPekerjaan) {
        params.append('filter_jenis_sub_pekerjaan_column', filterJenisSubPekerjaan);
      }

      const filterJenisPembayaran = document.getElementById('filter_jenis_pembayaran_column')?.value;
      if (filterJenisPembayaran) {
        params.append('filter_jenis_pembayaran_column', filterJenisPembayaran);
      }

      const filterKebun = document.getElementById('filter_jenis_kebuns_column')?.value;
      if (filterKebun) {
        params.append('filter_jenis_kebuns_column', filterKebun);
      }
    }

    // Open export route in new tab
    const exportUrl = '{{ route("pembayaran.rekapan.export") }}?' + params.toString();
    window.open(exportUrl, '_blank');
    
    // Reset button after a short delay
    setTimeout(() => {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }, 500);
  } catch (error) {
    console.error('Export error:', error);
    alert('Terjadi kesalahan saat export: ' + error.message);
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}

// Alternative export function with delay for better loading
function exportToPDFWithDelay() {
  setTimeout(() => {
    exportToPDF();
  }, 300);
}

document.addEventListener('DOMContentLoaded', function() {
  const enableRekapan = document.getElementById('enableRekapanTable');
  const filterRekapanPanel = document.getElementById('filterRekapanPanel');
  const openColumnModalBtn = document.getElementById('openColumnModalBtn');
  const columnsGrid = document.getElementById('columnsGrid');
  const selectedPreview = document.getElementById('selectedPreview');
  const selectedColumnsList = document.getElementById('selectedColumnsList');
  filterForm = document.getElementById('filterForm');

  // Initialize from existing selected columns
  @if(count($selectedColumns) > 0)
    selectionOrder = @json($selectedColumns);
  @endif

  // Toggle rekapan table mode - show/hide content without reload
  enableRekapan.addEventListener('change', function() {
    const rekapanContentArea = document.getElementById('rekapanContentArea');
    const filterRekapanPanel = document.getElementById('filterRekapanPanel');
    const openColumnModalBtn = document.getElementById('openColumnModalBtn');
    
    if (this.checked) {
      // Show rekapan configuration area
      if (filterRekapanPanel) {
        filterRekapanPanel.classList.add('show');
      }
      if (openColumnModalBtn) {
        openColumnModalBtn.style.display = 'inline-flex';
      }
      if (rekapanContentArea) {
        rekapanContentArea.style.display = 'block';
      }
    } else {
      // Hide rekapan configuration area
      if (filterRekapanPanel) {
        filterRekapanPanel.classList.remove('show');
      }
      if (openColumnModalBtn) {
        openColumnModalBtn.style.display = 'none';
      }
      if (rekapanContentArea) {
        rekapanContentArea.style.display = 'none';
      }
      // Clear selection
      selectionOrder = [];
      if (typeof updateOrderBadges === 'function') updateOrderBadges();
      if (typeof updatePreview === 'function') updatePreview();
    }
  });
  
  // Auto-submit form when rekapan filter changes (only if rekapan table is enabled)
  const rekapanFilterSelects = [
    'filter_dibayar_kepada_column',
    'filter_kategori_column',
    'filter_jenis_dokumen_column',
    'filter_jenis_sub_pekerjaan_column',
    'filter_jenis_pembayaran_column',
    'filter_jenis_kebuns_column'
  ];
  
  rekapanFilterSelects.forEach(filterId => {
    const filterSelect = document.getElementById(filterId);
    if (filterSelect) {
      filterSelect.addEventListener('change', function() {
        // Only auto-submit if rekapan table is enabled
        if (enableRekapan && enableRekapan.checked) {
          // Get current selected columns from URL or from selectionOrder
          const urlParams = new URLSearchParams(window.location.search);
          const currentColumns = urlParams.getAll('columns[]');
          
          // Use current columns from URL if available, otherwise use selectionOrder
          const columnsToSubmit = currentColumns.length > 0 ? currentColumns : (selectionOrder.length > 0 ? selectionOrder : []);
          
          // Only submit if we have columns selected
          if (columnsToSubmit.length > 0) {
            // Remove existing column inputs
            document.querySelectorAll('input[name="columns[]"]').forEach(input => {
              if (input.type === 'hidden') {
                input.remove();
              }
            });
            
            // Add hidden inputs for each selected column in order
            columnsToSubmit.forEach(key => {
              const hiddenInput = document.createElement('input');
              hiddenInput.type = 'hidden';
              hiddenInput.name = 'columns[]';
              hiddenInput.value = key;
              filterForm.appendChild(hiddenInput);
            });
            
            // Ensure mode is set to rekapan_table
            const existingModeInput = filterForm.querySelector('input[name="mode"]');
            if (existingModeInput) {
              existingModeInput.remove();
            }
            
            const modeInput = document.createElement('input');
            modeInput.type = 'hidden';
            modeInput.name = 'mode';
            modeInput.value = 'rekapan_table';
            filterForm.appendChild(modeInput);
            
            // Ensure checkbox is checked
            if (!enableRekapan.checked) {
              enableRekapan.checked = true;
            }
            
            // Submit form
            console.log('Auto-submitting form with columns:', columnsToSubmit);
            filterForm.submit();
          } else {
            console.log('No columns selected, skipping auto-submit');
          }
        }
      });
    }
  });

  
  // Function to auto-submit form
  function autoSubmitForm() {
    // Prepare form data
    prepareFormData();
    
    // Submit form automatically
    filterForm.submit();
  }
  
  // Function to prepare form data before submit
  function prepareFormData() {
    // Remove existing column inputs
    document.querySelectorAll('input[name="columns[]"]').forEach(input => {
      if (input.type === 'hidden') {
        input.remove();
      }
    });

    // Uncheck all checkboxes first (remove name attribute)
    document.querySelectorAll('.column-checkbox-item input[type="checkbox"]').forEach(cb => {
      cb.removeAttribute('name');
    });

    // Add hidden inputs in correct order
    selectionOrder.forEach(key => {
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'columns[]';
      hiddenInput.value = key;
      filterForm.appendChild(hiddenInput);
    });
    
    // Ensure rekapan table mode is enabled if columns are selected
    if (selectionOrder.length > 0) {
      enableRekapan.checked = true;
      columnCheckboxes.classList.add('show');
    }
  }

  function updateOrderBadges() {
    document.querySelectorAll('.column-checkbox-item-modern').forEach(item => {
      const columnKey = item.dataset.column;
      const badge = item.querySelector('.order-badge-modern');
      const index = selectionOrder.indexOf(columnKey);

      if (index !== -1) {
        badge.textContent = index + 1;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }
    });
  }

  function updatePreview() {
    if (selectionOrder.length > 0) {
      selectedPreview.classList.add('show');
      const labels = selectionOrder.map(key => {
        const item = document.querySelector(`.column-checkbox-item-modern[data-column="${key}"] label`);
        return item ? item.textContent : key;
      });
      selectedColumnsList.textContent = labels.join(' → ');
    } else {
      selectedPreview.classList.remove('show');
      selectedColumnsList.textContent = '';
    }
  }

  // Before form submit, reorder hidden inputs to match selection order
  filterForm.addEventListener('submit', function(e) {
    prepareFormData();
  });

  // Initialize badges on page load
  updateOrderBadges();
});

// Export Normal Table to PDF
// function exportToPDF() {
//   const tableContainer = document.querySelector('.table-container');
//   if (!tableContainer) {
//     alert('Tabel tidak ditemukan!');
//     return;
//   }

//   // Show loading
//   const btn = document.querySelector('.btn-export-pdf');
//   const originalText = btn.innerHTML;
//   btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
//   btn.disabled = true;

//   // Clone container for PDF
//   const clone = tableContainer.cloneNode(true);

//   // Remove export buttons from clone
//   const exportBtns = clone.querySelector('.export-buttons');
//   if (exportBtns) exportBtns.remove();

//   // Remove separator rows for cleaner PDF
//   const separatorRows = clone.querySelectorAll('.separator-row');
//   separatorRows.forEach(row => row.remove());

//   // PDF options
//   const opt = {
//     margin: [10, 10, 10, 10],
//     filename: `Rekapan_Pembayaran_${new Date().toISOString().slice(0, 10)}.pdf`,
//     image: { type: 'jpeg', quality: 0.98 },
//     html2canvas: {
//       scale: 2,
//       useCORS: true,
//       logging: false
//     },
//     jsPDF: {
//       unit: 'mm',
//       format: 'a4',
//       orientation: 'landscape'
//     },
//     pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
//   };

//   // Generate PDF
//   html2pdf().set(opt).from(clone).save().then(() => {
//     btn.innerHTML = originalText;
//     btn.disabled = false;
//   }).catch(err => {
//     console.error('PDF Error:', err);
//     btn.innerHTML = originalText;
//     btn.disabled = false;
//     alert('Gagal membuat PDF. Silakan coba lagi.');
//   });
// }

document.addEventListener('DOMContentLoaded', function() {
  const enableRekapan = document.getElementById('enableRekapanTable');
  const columnCheckboxes = document.getElementById('columnCheckboxes');
  const columnsGrid = document.getElementById('columnsGrid');
  const selectedPreview = document.getElementById('selectedPreview');
  const selectedColumnsList = document.getElementById('selectedColumnsList');
  const filterForm = document.getElementById('filterForm');
  const kategoriSelect = document.getElementById('filter_kategori_column');
  const jenisDokumenSelect = document.getElementById('filter_jenis_dokumen_column');

  // Track selection order
  let selectionOrder = [];

  // Initialize from existing selected columns
  @if(count($selectedColumns) > 0)
    selectionOrder = @json($selectedColumns);
  @endif

  // Toggle column checkboxes visibility
  enableRekapan.addEventListener('change', function() {
    if (this.checked) {
      columnCheckboxes.classList.add('show');
    } else {
      columnCheckboxes.classList.remove('show');
      // Clear all checkboxes when disabled
      document.querySelectorAll('.column-checkbox-item-modern input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        cb.closest('.column-checkbox-item-modern').classList.remove('selected');
      });
      selectionOrder = [];
      updatePreview();
      // Auto-submit when disabling rekapan table
      autoSubmitForm();
    }
  });

  // Mapping filter dropdown ke kolom checkbox
    const filterToColumnMapping = {
      'filter_dibayar_kepada_column': 'dibayar_kepada',
      'filter_kategori_column': 'kategori',
      'filter_jenis_dokumen_column': 'jenis_dokumen',
      'filter_jenis_pembayaran_column': 'jenis_pembayaran',
      'filter_jenis_sub_pekerjaan_column': 'jenis_sub_pekerjaan',
      'filter_jenis_kebuns_column': 'kebun',
    };

    // Function untuk auto-check checkbox berdasarkan filter yang dipilih
    // function autoCheckColumnFromFilter(filterId, columnKey) {
    //   const filterSelect = document.getElementById(filterId);
    //   if (!filterSelect) return;

    //   filterSelect.addEventListener('change', function() {
    //     const selectedValue = this.value;
    //     const checkboxItem = document.querySelector(`.column-checkbox-item-modern[data-column="${columnKey}"]`);
        
    //     if (checkboxItem) {
    //       const checkbox = checkboxItem.querySelector('input[type="checkbox"]');
          
    //       if (selectedValue && selectedValue !== '') {
    //         // Jika filter dipilih, centang checkbox
    //         if (!checkbox.checked) {
    //           checkbox.checked = true;
              
    //           // Add to selection order if not already there
    //           if (!selectionOrder.includes(columnKey)) {
    //             selectionOrder.push(columnKey);
    //           }
    //           checkboxItem.classList.add('selected');
              
    //           updateOrderBadges();
    //           updatePreview();
    //         }
    //       }
    //     }
    //   });
    // }
    // Function untuk auto-check checkbox berdasarkan filter yang dipilih
    // Function untuk auto-check checkbox berdasarkan filter yang dipilih
    function autoCheckColumnFromFilter(filterId, columnKey) {
      const filterSelect = document.getElementById(filterId);
      if (!filterSelect) return;

      filterSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        const checkboxItem = document.querySelector(`.column-checkbox-item-modern[data-column="${columnKey}"]`);
        
        if (checkboxItem) {
          const checkbox = checkboxItem.querySelector('input[type="checkbox"]');
          
          if (selectedValue && selectedValue !== '') {
            // Jika filter dipilih, centang checkbox
            if (!checkbox.checked) {
              checkbox.checked = true;
              
              // Add to selection order if not already there
              if (!selectionOrder.includes(columnKey)) {
                selectionOrder.push(columnKey);
              }
              checkboxItem.classList.add('selected');
              
              updateOrderBadges();
              updatePreview();
            }
            
            // Auto-submit form setelah checkbox tercentang
            prepareFormData();
            filterForm.submit();
          } else {
            // Jika filter direset, uncheck checkbox
            if (checkbox.checked) {
              checkbox.checked = false;
              selectionOrder = selectionOrder.filter(key => key !== columnKey);
              checkboxItem.classList.remove('selected');
              updateOrderBadges();
              updatePreview();
              
              // Auto-submit form setelah checkbox di-uncheck
              prepareFormData();
              filterForm.submit();
            }
          }
        }
      });
    }

    // Apply auto-check untuk semua filter
    Object.keys(filterToColumnMapping).forEach(filterId => {
      const columnKey = filterToColumnMapping[filterId];
      autoCheckColumnFromFilter(filterId, columnKey);
    });

    // Auto-check pada saat halaman dimuat (jika filter sudah dipilih)
    Object.keys(filterToColumnMapping).forEach(filterId => {
      const filterSelect = document.getElementById(filterId);
      const columnKey = filterToColumnMapping[filterId];
      
      if (filterSelect && filterSelect.value && filterSelect.value !== '') {
        const checkboxItem = document.querySelector(`.column-checkbox-item-modern[data-column="${columnKey}"]`);
        if (checkboxItem) {
          const checkbox = checkboxItem.querySelector('input[type="checkbox"]');
          if (!checkbox.checked) {
            checkbox.checked = true;
            if (!selectionOrder.includes(columnKey)) {
              selectionOrder.push(columnKey);
            }
            checkboxItem.classList.add('selected');
          }
        }
      }
    });

    // Update badges dan preview setelah auto-check
    updateOrderBadges();
    updatePreview();

  // Handle kategori change - Hanya update jenis dokumen, TIDAK submit form
    // Handle kategori change - Reset jenis dokumen dan auto-submit
    if (kategoriSelect && jenisDokumenSelect) {
      kategoriSelect.addEventListener('change', function() {
        const kategoriId = this.value;
        
        // Reset jenis dokumen saat kategori berubah
        if (jenisDokumenSelect) {
          jenisDokumenSelect.value = '';
          jenisDokumenSelect.innerHTML = '<option value="">-- Pilih Jenis Dokumen (Opsional) --</option>';
        }
        
    // Auto-check checkbox kategori
    const kategoriCheckboxItem = document.querySelector('.column-checkbox-item-modern[data-column="kategori"]');
    if (kategoriCheckboxItem && kategoriId && kategoriId !== '') {
      const kategoriCheckbox = kategoriCheckboxItem.querySelector('input[type="checkbox"]');
      if (!kategoriCheckbox.checked) {
        kategoriCheckbox.checked = true;
        if (!selectionOrder.includes('kategori')) {
          selectionOrder.push('kategori');
        }
        kategoriCheckboxItem.classList.add('selected');
        updateOrderBadges();
        updatePreview();
      }
    }
        
        // Update jenis dokumen via AJAX (jika ingin dinamis tanpa refresh)
        if (kategoriId) {
          fetch(`/api/jenis-dokumen-by-kategori?kategori_id=${kategoriId}`)
            .then(response => response.json())
            .then(data => {
              if (data.length > 0) {
                data.forEach(jenis => {
                  const option = document.createElement('option');
                  option.value = jenis.id_jenis_dokumen;
                  option.textContent = jenis.nama_jenis_dokumen;
                  
                  // Cek apakah ini adalah nilai yang sudah dipilih sebelumnya
                  const selectedValue = new URLSearchParams(window.location.search).get('filter_jenis_dokumen_column');
                  if (selectedValue && selectedValue == jenis.id_jenis_dokumen) {
                    option.selected = true;
                  }
                  
                  jenisDokumenSelect.appendChild(option);
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
            });
        }
        
        // Auto-submit form setelah update
        prepareFormData();
        filterForm.submit();
      });
    }
      
  // Function to auto-submit form
  function autoSubmitForm() {
    // Prepare form data
    prepareFormData();
    
    // Submit form automatically
    filterForm.submit();
  }
  
  // Function to prepare form data before submit
  function prepareFormData() {
    // Remove existing column inputs
    document.querySelectorAll('input[name="columns[]"]').forEach(input => {
      if (input.type === 'hidden') {
        input.remove();
      }
    });

    // Uncheck all checkboxes first (remove name attribute)
    document.querySelectorAll('.column-checkbox-item input[type="checkbox"]').forEach(cb => {
      cb.removeAttribute('name');
    });

    // Add hidden inputs in correct order
    selectionOrder.forEach(key => {
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'columns[]';
      hiddenInput.value = key;
      filterForm.appendChild(hiddenInput);
    });
    
    // Ensure rekapan table mode is enabled if columns are selected
    if (selectionOrder.length > 0) {
      enableRekapan.checked = true;
      columnCheckboxes.classList.add('show');
    }
  }

  function updateOrderBadges() {
    document.querySelectorAll('.column-checkbox-item-modern').forEach(item => {
      const columnKey = item.dataset.column;
      const badge = item.querySelector('.order-badge-modern');
      const index = selectionOrder.indexOf(columnKey);

      if (index !== -1) {
        badge.textContent = index + 1;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }
    });
  }

  function updatePreview() {
    if (selectionOrder.length > 0) {
      selectedPreview.classList.add('show');
      const labels = selectionOrder.map(key => {
        const item = document.querySelector(`.column-checkbox-item-modern[data-column="${key}"] label`);
        return item ? item.textContent : key;
      });
      selectedColumnsList.textContent = labels.join(' → ');
    } else {
      selectedPreview.classList.remove('show');
      selectedColumnsList.textContent = '';
    }
  }

  // Before form submit, reorder hidden inputs to match selection order
  filterForm.addEventListener('submit', function(e) {
    prepareFormData();
  });

  // Initialize badges on page load
  updateOrderBadges();
});
</script>

@endsection


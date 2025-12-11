@extends('layouts.app')

@section('content')
<!-- Tailwind CSS CDN for responsive utilities -->
<script src="https://cdn.tailwindcss.com"></script>

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
    animation: fadeIn 0.5s ease;
  }

  /* Statistics Cards - Enhanced with smooth animations */
  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    min-height: auto;
    height: auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
    animation: slideUp 0.4s ease backwards;
  }

  .stat-card:nth-child(1) { animation-delay: 0.1s; }
  .stat-card:nth-child(2) { animation-delay: 0.2s; }
  .stat-card:nth-child(3) { animation-delay: 0.3s; }
  .stat-card:nth-child(4) { animation-delay: 0.4s; }

  .stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
    border-color: rgba(136, 151, 23, 0.3);
  }

  .stat-card-body {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    min-width: 0;
  }

  .stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
    min-width: 48px;
    transition: transform 0.3s ease;
  }

  .stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
  }

  .stat-icon.total { background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); }
  .stat-icon.terkunci { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
  .stat-icon.proses { background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); }
  .stat-icon.selesai { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }

  .stat-content {
    flex: 1;
    min-width: 0;
    overflow: hidden;
  }

  .stat-title {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.2;
    margin-bottom: 4px;
  }

  /* Responsive font sizes */
  @media (min-width: 640px) {
    .stat-value {
      font-size: 26px;
    }
  }

  @media (min-width: 1024px) {
    .stat-value {
      font-size: 28px;
    }
  }

  @media (min-width: 1536px) {
    .stat-value {
      font-size: 32px;
    }
  }

  @media (max-width: 768px) {
    .stat-card {
      padding: 16px 20px;
    }
    
    .stat-icon {
      width: 48px;
      height: 48px;
      font-size: 20px;
      min-width: 40px;
    }
  }

  /* Filter Section - Modern Redesign */
  .filter-section {
    background: #ffffff;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    animation: slideUp 0.5s ease backwards;
    animation-delay: 0.5s;
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
    width: 100%;
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
    height: 42px;
    box-sizing: border-box;
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
    align-items: flex-end;
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
    justify-content: center;
    height: 42px;
    box-sizing: border-box;
    white-space: nowrap;
  }

  .btn-filter-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  .btn-filter-reset {
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 42px;
    width: 42px;
    box-sizing: border-box;
  }

  .btn-filter-reset:hover {
    background: #4b5563;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
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

  /* Table Container - Enhanced */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1);
    border: 1px solid rgba(8, 62, 64, 0.08);
    animation: slideUp 0.6s ease backwards;
    animation-delay: 0.6s;
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
    transition: background 0.3s ease;
  }

  .table tbody tr {
    transition: all 0.2s ease;
  }

  .table tbody tr:hover {
    background-color: rgba(8, 62, 64, 0.03);
    transform: scale(1.001);
  }

        /* Table cell text clamping for long text */
        .table tbody td {
            vertical-align: middle;
            max-width: 250px;
            padding: 10px 12px;
        }

        .table tbody td .cell-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 230px;
            line-height: 1.4;
        }

        .table tbody td .cell-text-long {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 280px;
            line-height: 1.4;
        }

        /* Wider columns for specific fields */
        .table tbody td[data-column="alamat_pembeli"],
        .table tbody td[data-column="uraian_spp"],
        .table tbody td[data-column="keterangan"],
        .table tbody td[data-column="keterangan_pajak"] {
            max-width: 300px;
        }

        /* Narrower columns for numbers/dates */
        .table tbody td[data-column="nilai_rupiah"],
        .table tbody td[data-column="dpp_pph"],
        .table tbody td[data-column="ppn_terhutang"],
        .table tbody td[data-column="tanggal_spp"],
        .table tbody td[data-column="deadline_at"],
        .table tbody td[data-column="no"] {
            max-width: 150px;
            white-space: nowrap;
        }

        /* Customize Button */
        .btn-customize-columns {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: 2px solid rgba(8, 62, 64, 0.15);
            background: white;
            color: #083E40;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-customize-columns:hover {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            color: white;
            border-color: #083E40;
        }

        /* Column Customization Modal */
        .customization-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .customization-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content-custom {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            max-width: 1200px; /* Widened as requested */
            width: 95%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }

        .modal-header-custom {
            background: white;
            color: #333;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .modal-header-custom i {
            color: #333;
            font-size: 20px;
        }

        .modal-header-custom h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #333;
        }

        .modal-body-custom {
            padding: 25px;
            overflow-y: auto;
            flex: 1;
            background: #fcfcfc;
        }

        .modal-footer-custom {
            padding: 20px 25px;
            background: white;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-shortcut {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        
        .btn-shortcut-select {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-color: #28a745;
        }
        
        .btn-shortcut-select:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea080 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        
        .btn-shortcut-deselect {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-color: #dc3545;
        }
        
        .btn-shortcut-deselect:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }
        
        .btn-shortcut i {
            font-size: 11px;
        }

        .column-selection-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            margin-bottom: 25px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .section-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }

        .column-selection-list {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            max-height: 280px; /* Approx 4 rows height (assuming ~60px per item + gap) */
            overflow-y: auto;
            padding-right: 5px; /* Space for scrollbar */
        }

        /* Custom scrollbar for column list */
        .column-selection-list::-webkit-scrollbar {
            width: 6px;
        }
        .column-selection-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .column-selection-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }
        .column-selection-list::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }

        .column-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .column-item:hover {
            border-color: #28a745;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.1);
            transform: translateY(-1px);
        }

        .column-item.selected {
            border-color: #28a745;
            background: #f0fff4;
        }

        /* Drag handle */
        .column-drag-handle {
            color: #adb5bd;
            cursor: move;
            font-size: 12px;
        }

        .column-item-checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #28a745;
        }

        .column-item-label {
            flex: 1;
            font-weight: 500;
            user-select: none;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .column-item-order {
            font-size: 10px;
            font-weight: 700;
            background: #e9ecef;
            color: #6c757d;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .column-item.selected .column-item-order {
            background: #28a745;
            color: white;
        }

        /* Preview Table */
        .preview-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }

        .preview-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #333;
            font-weight: 700;
        }

        .preview-table-container {
            border: 1px solid #edf2f7;
            border-radius: 8px;
            overflow: hidden;
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .preview-table th {
            background: #2c3e50;
            color: white;
            padding: 10px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            white-space: nowrap;
        }

        .preview-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview-table tr:last-child td {
            border-bottom: none;
        }

  /* Animations */
  @keyframes fadeIn {
    from { 
      opacity: 0; 
    }
    to { 
      opacity: 1; 
    }
  }

  @keyframes slideUp {
    from {
      transform: translateY(30px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .filter-inputs {
      flex-direction: column;
    }
    
    .filter-item,
    .filter-item.filter-search {
      width: 100%;
      min-width: 100%;
      flex: 1 1 100%;
    }
    
    .filter-actions {
      width: 100%;
      justify-content: stretch;
    }
    
    .btn-filter-primary,
    .btn-filter-reset {
      flex: 1;
    }
  }

        @media (max-width: 1200px) {
             /* Maintain 4 columns even on slightly smaller screens if possible, or fallback gracefully */
             /* User explicitly asked for 4, so let's try to keep it unless very small */
        }

        @media (max-width: 992px) {
            .column-selection-list {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .column-selection-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <div class="container-fluid" style="background-color: #f7f9f7; min-height: 100vh; padding: 30px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa-solid fa-file-export me-3"></i>Export Data Perpajakan</h2>
            <button type="button" class="btn-customize-columns" onclick="openColumnModal()">
                <i class="fa-solid fa-table-columns"></i> Kustomisasi Kolom Tabel
            </button>
        </div>

        <!-- Statistics Cards - Responsive Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-4 gap-4 mb-4">
            <!-- Card 1: Total Dokumen -->
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-content" style="flex: 1; min-width: 0;">
                        <div class="stat-title">Total Dokumen</div>
                        <div class="stat-value">{{ number_format($statistics['total_documents'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon total flex-shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
            
            <!-- Card 2: Terkunci -->
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-content" style="flex: 1; min-width: 0;">
                        <div class="stat-title">Terkunci</div>
                        <div class="stat-value">{{ number_format($statistics['terkunci'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon terkunci flex-shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>
            </div>
            
            <!-- Card 3: Sedang Diproses -->
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-content" style="flex: 1; min-width: 0;">
                        <div class="stat-title">Sedang Diproses</div>
                        <div class="stat-value">{{ number_format($statistics['sedang_diproses'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon proses flex-shrink-0">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
            
            <!-- Card 4: Selesai -->
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-content" style="flex: 1; min-width: 0;">
                        <div class="stat-title">Selesai</div>
                        <div class="stat-value">{{ number_format($statistics['selesai'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon selesai flex-shrink-0">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Export Section -->
        <div class="filter-section">
            <form action="{{ route('perpajakan.export') }}" method="GET" id="filterForm">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="columns" id="hiddenColumns" value="">
                <div class="main-filter-row">
                    <div class="filter-inputs">
                        <div class="filter-item">
                            <label class="filter-label">Tahun</label>
                            <select name="year" class="form-select-modern">
                                <option value="">Semua Tahun</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Bulan</label>
                            <select name="month" class="form-select-modern">
                                <option value="">Semua Bulan</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-item filter-search">
                            <label class="filter-label">Pencarian</label>
                            <div class="search-input-wrapper">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" name="search" class="form-control-modern" placeholder="No Agenda/SPP/Vendor..." value="{{ $search }}">
                            </div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter-primary">
                            <i class="fa-solid fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('perpajakan.export') }}" class="btn-filter-reset" title="Reset Filter">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </a>
                    </div>
                </div>

                <!-- Export Actions -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <div>
                        <span class="text-muted"><i class="fa-solid fa-info-circle me-1"></i> Kolom yang dipilih: <strong
                                id="selectedColumnsDisplay">Semua</strong></span>
                    </div>
                    <div class="export-buttons">
                        <button type="submit" formaction="{{ route('perpajakan.export.download') }}" name="export"
                            value="excel" class="btn-export btn-export-excel">
                            <i class="fa-solid fa-file-excel"></i>
                            <span>Export Excel</span>
                        </button>
                        <button type="button" onclick="exportToPDF()" class="btn-export btn-export-pdf">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Export PDF</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="mainTable">
                    <thead>
                        <tr id="tableHeaderRow">
                            <th data-column="no">No</th>
                            @foreach($availableColumns as $key => $label)
                                <th data-column="{{ $key }}">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($dokumens as $index => $doc)
                            <tr>
                                <td data-column="no">{{ $dokumens->firstItem() + $index }}</td>
                                @foreach($availableColumns as $key => $label)
                                    @php
                                        // Define text-heavy columns that need clamping
                                        $longTextColumns = ['uraian_spp', 'alamat_pembeli', 'keterangan', 'keterangan_pajak', 'dibayar_kepada', 'komoditi_perpajakan'];
                                        $isLongText = in_array($key, $longTextColumns);
                                        
                                        // Define date columns
                                        $dateColumns = ['tanggal_spp', 'tanggal_berita_acara', 'tanggal_spk', 'tanggal_berakhir_spk',
                                            'tanggal_faktur', 'tanggal_selesai_verifikasi_pajak', 'tanggal_invoice',
                                            'tanggal_pengajuan_pajak', 'created_at', 'sent_to_perpajakan_at',
                                            'processed_perpajakan_at', 'deadline_at', 'deadline_perpajakan_at'];
                                        
                                        // Define currency columns
                                        $currencyColumns = ['nilai_rupiah', 'dpp_pph', 'ppn_terhutang', 'dpp_invoice', 'ppn_invoice',
                                            'dpp_ppn_invoice', 'dpp_faktur', 'ppn_faktur', 'selisih_pajak',
                                            'penggantian_pajak', 'dpp_penggantian', 'ppn_penggantian', 'selisih_ppn'];
                                        
                                        // Get the raw value for tooltip
                                        $rawValue = $doc->$key ?? '-';
                                        if (in_array($key, $dateColumns) && $rawValue && $rawValue !== '-') {
                                            $rawValue = $rawValue->format('d/m/Y');
                                        } elseif (in_array($key, $currencyColumns) && $rawValue && $rawValue !== '-') {
                                            $rawValue = 'Rp ' . number_format($rawValue, 0, ',', '.');
                                        }
                                    @endphp
                                    <td data-column="{{ $key }}" title="{{ is_string($rawValue) ? $rawValue : '' }}">
                                        @if($key == 'nilai_rupiah')
                                            <span class="cell-text">Rp {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</span>
                                        @elseif($key == 'status')
                                            @php
                                                $statusMap = [
                                                    'selesai' => 'Selesai',
                                                    'terkunci' => 'Terkunci',
                                                    'sedang diproses' => 'Sedang Diproses',
                                                    'sent_to_perpajakan' => 'Terkirim ke Team Perpajakan',
                                                    'sent_to_akutansi' => 'Terkirim ke Team Akutansi',
                                                    'returned_to_department' => 'Dikembalikan ke Department',
                                                    'returned_from_akutansi' => 'Dikembalikan dari Akutansi',
                                                    'returned_from_perpajakan' => 'Dikembalikan dari Perpajakan',
                                                    'proses_perpajakan' => 'Diproses Team Perpajakan',
                                                    'proses_akutansi' => 'Diproses Team Akutansi',
                                                    'sent_to_ibub' => 'Terkirim ke Ibu Yuni',
                                                    'proses_ibub' => 'Diproses Ibu Yuni',
                                                    'pending_approval_ibub' => 'Menunggu Persetujuan Ibu Yuni',
                                                    'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                                    'draft' => 'Draft',
                                                ];
                                                $statusDisplay = $statusMap[$doc->status] ?? ucwords(str_replace('_', ' ', $doc->status));
                                                
                                                // Determine badge class
                                                $badgeClass = 'bg-secondary';
                                                if ($doc->status == 'selesai') {
                                                    $badgeClass = 'bg-success';
                                                } elseif ($doc->status == 'terkunci') {
                                                    $badgeClass = 'bg-danger';
                                                } elseif ($doc->status == 'sedang diproses') {
                                                    $badgeClass = 'bg-warning';
                                                } elseif (in_array($doc->status, ['sent_to_perpajakan', 'sent_to_akutansi'])) {
                                                    $badgeClass = 'bg-info';
                                                } elseif (in_array($doc->status, ['returned_to_department', 'returned_from_akutansi', 'returned_from_perpajakan'])) {
                                                    $badgeClass = 'bg-warning';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $statusDisplay }}</span>
                                        @elseif(in_array($key, $dateColumns))
                                            {{ $doc->$key ? $doc->$key->format('d/m/Y') : '-' }}
                                        @elseif(in_array($key, $currencyColumns))
                                            <span class="cell-text">{{ $doc->$key ? 'Rp ' . number_format($doc->$key, 0, ',', '.') : '-' }}</span>
                                        @elseif($isLongText)
                                            <span class="cell-text-long">{{ $doc->$key ?? '-' }}</span>
                                        @else
                                            <span class="cell-text">{{ $doc->$key ?? '-' }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($availableColumns) + 1 }}" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-folder-open fa-3x mb-3"></i>
                                        <p>Tidak ada data ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Pagination Controls -->
@if($dokumens->total() > 0)
<div class="pagination-wrapper" style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 16px; box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1); border: 1px solid rgba(8, 62, 64, 0.08);">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <!-- Info dan Per Page Selector -->
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
      <div class="text-muted" style="font-size: 13px; color: #083E40;">
        Menampilkan <strong>{{ $dokumens->firstItem() ?: 0 }}</strong> - <strong>{{ $dokumens->lastItem() ?: 0 }}</strong> dari total <strong>{{ $dokumens->total() }}</strong> dokumen
      </div>
      
      <!-- Per Page Selector -->
      <div style="display: flex; align-items: center; gap: 8px;">
        <label for="perPageSelect" style="font-size: 13px; color: #083E40; font-weight: 500; margin: 0;">Tampilkan per halaman:</label>
        <select id="perPageSelect" onchange="changePerPage(this.value)" style="padding: 6px 12px; border: 2px solid rgba(8, 62, 64, 0.15); border-radius: 8px; background: white; color: #083E40; font-size: 13px; font-weight: 500; cursor: pointer;">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
      </div>
    </div>

    <!-- Pagination Buttons -->
    @if($dokumens->hasPages())
    <div class="pagination" style="display: flex; gap: 8px; align-items: center;">
      {{-- Previous Page Link --}}
      @if($dokumens->onFirstPage())
        <button class="btn-pagination" disabled style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: #e0e0e0; color: #9e9e9e; border-radius: 10px; cursor: not-allowed;">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
      @else
        <a href="{{ $dokumens->appends(request()->query())->previousPageUrl() }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
        </a>
      @endif

      {{-- Pagination Elements --}}
      @php
        $currentPage = $dokumens->currentPage();
        $lastPage = $dokumens->lastPage();
        $startPage = max(1, $currentPage - 2);
        $endPage = min($lastPage, $currentPage + 2);
      @endphp

      {{-- First page --}}
      @if($startPage > 1)
        <a href="{{ $dokumens->appends(request()->query())->url(1) }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background-color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">1</button>
        </a>
        @if($startPage > 2)
          <button disabled style="padding: 10px 16px; border: none; background: transparent; color: #999; cursor: default;">...</button>
        @endif
      @endif

      {{-- Range of pages --}}
      @for($i = $startPage; $i <= $endPage; $i++)
        @if($currentPage == $i)
          <button class="btn-pagination active" style="padding: 10px 16px; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600;">{{ $i }}</button>
        @else
          <a href="{{ $dokumens->appends(request()->query())->url($i) }}">
            <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background-color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">{{ $i }}</button>
          </a>
        @endif
      @endfor

      {{-- Dots --}}
      @if($endPage < $lastPage)
        @if($endPage < $lastPage - 1)
          <button disabled style="padding: 10px 16px; border: none; background: transparent; color: #999; cursor: default;">...</button>
        @endif
        <a href="{{ $dokumens->appends(request()->query())->url($lastPage) }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background-color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">{{ $lastPage }}</button>
        </a>
      @endif

      {{-- Next Page Link --}}
      @if($dokumens->hasMorePages())
        <a href="{{ $dokumens->appends(request()->query())->nextPageUrl() }}">
          <button class="btn-pagination" style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </a>
      @else
        <button class="btn-pagination" disabled style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.1); background: #e0e0e0; color: #9e9e9e; border-radius: 10px; cursor: not-allowed;">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      @endif
    </div>
    @endif
  </div>
</div>
@endif

    <!-- Column Customization Modal -->
    <div class="customization-modal" id="columnModal">
        <div class="modal-content-custom">
            <!-- Header -->
            <div class="modal-header-custom">
                <i class="fa-solid fa-table-columns"></i>
                <h3>Kustomisasi Kolom Tabel</h3>
            </div>

            <!-- Body -->
            <div class="modal-body-custom">
                <!-- Column Selection Section -->
                <div class="column-selection-section">
                    <div class="section-header">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="checkbox" id="selectAllCheckbox" class="column-item-checkbox" onchange="toggleSelectAll(this)"> 
                            <h5 style="margin: 0; flex: 1;">Pilih Kolom</h5>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="selectAllColumns()" class="btn-shortcut btn-shortcut-select" title="Pilih Semua Kolom">
                                    <i class="fa-solid fa-check-double"></i>
                                    <span>Pilih Semua</span>
                                </button>
                                <button type="button" onclick="deselectAllColumns()" class="btn-shortcut btn-shortcut-deselect" title="Hapus Semua Pilihan">
                                    <i class="fa-solid fa-xmark"></i>
                                    <span>Hapus Semua</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted mb-3 small">Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.</p>

                    <div class="column-selection-list" id="columnSelectionList">
                        <!-- Generated by JS -->
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="preview-section">
                    <div class="preview-header">
                        <i class="fa-solid fa-eye text-muted"></i>
                        <span>Preview Hasil</span>
                    </div>
                    <p class="text-muted mb-3 small">Preview tabel akan menampilkan kolom yang Anda pilih sesuai urutan.</p>
                    
                    <div class="preview-table-container">
                        <div class="table-responsive">
                            <table class="preview-table" id="previewTable">
                                <thead>
                                    <tr id="previewHeader"></tr>
                                </thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer-custom">
                <div>
                    <strong id="selectedColumnCount" class="text-success" style="font-size: 16px;">0</strong> <span class="text-muted">kolom dipilih</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" onclick="closeColumnModal()">Batal</button>
                    <button type="button" class="btn btn-success" onclick="applyColumnCustomization()">
                        <i class="fa-solid fa-check me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Available columns from server
        const availableColumns = @json($availableColumns);
        @php
        // Build sample data dynamically from availableColumns
        $sampleDataArray = $dokumens->take(3)->map(function ($doc) use ($availableColumns) {
            $row = [];
            $dateFields = ['tanggal_spp', 'tanggal_berita_acara', 'tanggal_spk', 'tanggal_berakhir_spk',
                'tanggal_faktur', 'tanggal_selesai_verifikasi_pajak', 'tanggal_invoice',
                'tanggal_pengajuan_pajak', 'created_at', 'sent_to_perpajakan_at',
                'processed_perpajakan_at', 'deadline_at', 'deadline_perpajakan_at'];
            $currencyFields = ['nilai_rupiah', 'dpp_pph', 'ppn_terhutang', 'dpp_invoice', 'ppn_invoice',
                'dpp_ppn_invoice', 'dpp_faktur', 'ppn_faktur', 'selisih_pajak',
                'penggantian_pajak', 'dpp_penggantian', 'ppn_penggantian', 'selisih_ppn'];
            
            foreach(array_keys($availableColumns) as $col) {
                $value = $doc->$col ?? null;
                if (in_array($col, $dateFields) && $value) {
                    $row[$col] = $value->format('d/m/Y');
                } elseif (in_array($col, $currencyFields) && $value) {
                    $row[$col] = 'Rp ' . number_format($value, 0, ',', '.');
                } else {
                    $row[$col] = $value ?? '-';
                }
            }
            return $row;
        })->values()->toArray();
        @endphp
        const sampleData = @json($sampleDataArray);

        // Selected columns in order
        let selectedColumnsOrder = Object.keys(availableColumns);

        // Load from localStorage
        const savedColumns = localStorage.getItem('perpajakanExportColumns');
        if (savedColumns) {
            selectedColumnsOrder = JSON.parse(savedColumns);
            // Filter out nomor_mirror and nomor_miro if present
            selectedColumnsOrder = selectedColumnsOrder.filter(col => 
                col !== 'nomor_mirror' && col !== 'nomor_miro'
            );
            // Update hidden input with saved columns
            document.getElementById('hiddenColumns').value = selectedColumnsOrder.join(',');
        }

        function openColumnModal() {
            document.getElementById('columnModal').classList.add('show');
            generateColumnSelection();
            updatePreview();
        }

        function closeColumnModal() {
            document.getElementById('columnModal').classList.remove('show');
        }

        function generateColumnSelection() {
            const list = document.getElementById('columnSelectionList');
            list.innerHTML = '';

            Object.keys(availableColumns).forEach((key) => {
                const isSelected = selectedColumnsOrder.includes(key);
                const order = selectedColumnsOrder.indexOf(key) + 1;

                const item = document.createElement('div');
                item.className = `column-item ${isSelected ? 'selected' : ''}`;
                item.setAttribute('data-column', key);
                item.onclick = (e) => {
                    // Prevent triggering when clicking checkbox directly (handled by checkbox change)
                    if (e.target.type !== 'checkbox') {
                        toggleColumn(key);
                    }
                };

                item.innerHTML = `
                <i class="fa-solid fa-ellipsis-vertical column-drag-handle"></i>
                <input type="checkbox" class="column-item-checkbox" ${isSelected ? 'checked' : ''} onchange="toggleColumn('${key}')">
                <span class="column-item-label" title="${availableColumns[key]}">${availableColumns[key]}</span>
                <span class="column-item-order">${isSelected ? order : ''}</span>
            `;

                list.appendChild(item);
            });

            updateSelectedCount();
            updateSelectAllCheckbox();
        }

        function updateSelectAllCheckbox() {
             const allSelected = Object.keys(availableColumns).length === selectedColumnsOrder.length;
             document.getElementById('selectAllCheckbox').checked = allSelected;
        }

        function toggleSelectAll(checkbox) {
            if (checkbox.checked) {
                selectAllColumns();
            } else {
                deselectAllColumns();
            }
        }

        function toggleColumn(key) {
            const index = selectedColumnsOrder.indexOf(key);
            if (index > -1) {
                selectedColumnsOrder.splice(index, 1);
            } else {
                selectedColumnsOrder.push(key);
            }
            generateColumnSelection();
            updatePreview();
        }

        function selectAllColumns() {
            selectedColumnsOrder = Object.keys(availableColumns);
            // Update select all checkbox
            document.getElementById('selectAllCheckbox').checked = true;
            generateColumnSelection();
            updatePreview();
            updateSelectedCount();
        }

        function deselectAllColumns() {
            selectedColumnsOrder = [];
            // Update select all checkbox
            document.getElementById('selectAllCheckbox').checked = false;
            generateColumnSelection();
            updatePreview();
            updateSelectedCount();
        }

        function updateSelectedCount() {
            document.getElementById('selectedColumnCount').textContent = selectedColumnsOrder.length;
        }

        function updatePreview() {
            const header = document.getElementById('previewHeader');
            const body = document.getElementById('previewBody');

            header.innerHTML = '<th>No</th>' + selectedColumnsOrder.map(key => `<th>${availableColumns[key]}</th>`).join('');

            body.innerHTML = sampleData.map((row, idx) => {
                return `<tr><td>${idx + 1}</td>${selectedColumnsOrder.map(key => `<td>${row[key] || '-'}</td>`).join('')}</tr>`;
            }).join('');

            if (sampleData.length === 0 || selectedColumnsOrder.length === 0) {
                body.innerHTML = '<tr><td colspan="100" class="text-center text-muted py-3">Tidak ada preview tersedia</td></tr>';
            }
        }

        function applyColumnCustomization() {
            if (selectedColumnsOrder.length === 0) {
                alert('Silakan pilih minimal 1 kolom untuk ditampilkan.');
                return;
            }

            // Save to localStorage
            localStorage.setItem('perpajakanExportColumns', JSON.stringify(selectedColumnsOrder));

            // Update hidden input for form submission
            document.getElementById('hiddenColumns').value = selectedColumnsOrder.join(',');

            // Update display text
            if (selectedColumnsOrder.length === Object.keys(availableColumns).length) {
                document.getElementById('selectedColumnsDisplay').textContent = 'Semua';
            } else {
                document.getElementById('selectedColumnsDisplay').textContent = selectedColumnsOrder.length + ' kolom';
            }

            // Apply to table
            applyColumnsToTable();

            // Close modal
            closeColumnModal();
        }

        function applyColumnsToTable() {
            // Hide all columns first
            document.querySelectorAll('#mainTable th, #mainTable td').forEach(cell => {
                const col = cell.getAttribute('data-column');
                if (col && col !== 'no') {
                    cell.style.display = selectedColumnsOrder.includes(col) ? '' : 'none';
                }
            });
        }

        // Initialize on page load
        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to page 1 when changing per page
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function () {
            applyColumnsToTable();

            // Update display text
            if (selectedColumnsOrder.length === Object.keys(availableColumns).length) {
                document.getElementById('selectedColumnsDisplay').textContent = 'Semua';
            } else {
                document.getElementById('selectedColumnsDisplay').textContent = selectedColumnsOrder.length + ' kolom';
            }

            // Set hidden input
            document.getElementById('hiddenColumns').value = selectedColumnsOrder.join(',');
        });

        // Function to export PDF in new window
        function exportToPDF() {
            // Get form element
            const form = document.getElementById('filterForm');
            if (!form) {
                alert('Form tidak ditemukan!');
                return;
            }

            // Get all form data
            const formData = new FormData(form);
            
            // Add export type
            formData.append('export', 'pdf');
            
            // Ensure columns are set
            const hiddenColumns = document.getElementById('hiddenColumns').value;
            if (hiddenColumns) {
                formData.set('columns', hiddenColumns);
            }

            // Build URL with all parameters
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            // Open in new window
            const url = '{{ route("perpajakan.export.download") }}?' + params.toString();
            window.open(url, '_blank');
        }
    </script>
@endsection
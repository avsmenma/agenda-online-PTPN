@extends('layouts/app')
@section('content')

  <style>
    .form-title {
      font-size: 24px;
      font-weight: 700;
      background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .stat-label {
      font-size: 13px;
      color: #666;
      font-weight: 500;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: #333;
    }

    .stat-dept {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      margin-top: 8px;
      color: white;
    }

    .stat-dept.DPM {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-dept.SKH {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-dept.SDM {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-dept.TEP {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .stat-dept.KPL {
      background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .stat-dept.AKN {
      background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
    }

    .stat-dept.TAN {
      background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }

    .stat-dept.PMO {
      background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%);
    }

    .stat-dept.PTI {
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    }

    .table-dokumen {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.08);
    }

    .table-dokumen thead {
      background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
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
      background: linear-gradient(90deg, transparent 0%, #f8f9fa 50%, transparent 100%);
    }

    .table-dokumen thead th {
      padding: 16px 12px;
      font-weight: 600;
      font-size: 13px;
      border: none;
      text-align: center;
      letter-spacing: 0.5px;
    }

    .table-dokumen tbody tr {
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }

    .table-dokumen tbody tr.main-row {
      cursor: pointer;
    }

    .table-dokumen tbody tr:hover {
      background: linear-gradient(135deg, rgba(253, 126, 20, 0.05) 0%, rgba(229, 90, 0, 0.02) 100%);
      border-left: 3px solid #fd7e14;
    }

    .table-dokumen tbody td {
      padding: 12px;
      vertical-align: middle;
      border-bottom: 1px solid #f0f0f0;
    }

    .dept-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      color: white;
      display: inline-block;
      text-transform: capitalize;
    }

    .bidang-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      color: white;
      display: inline-block;
      text-transform: uppercase;
      background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
    }

    .bidang-badge.DPM {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .bidang-badge.SKH {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .bidang-badge.SDM {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .bidang-badge.TEP {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .bidang-badge.KPL {
      background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .bidang-badge.AKN {
      background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
    }

    .bidang-badge.TAN {
      background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }

    .bidang-badge.PMO {
      background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%);
    }

    .bidang-badge.PTI {
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    }

    .action-buttons {
      display: flex;
      gap: 6px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-action {
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 11px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      min-width: 44px;
      min-height: 36px;
    }

    .btn-send {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }

    .btn-send:hover {
      background: linear-gradient(135deg, #20c997 0%, #1ea085 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
      color: white;
    }

    .btn-edit {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
    }

    .btn-edit:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
      color: white;
    }


    .detail-content {
      padding: 20px;
      border-top: 2px solid rgba(8, 62, 64, 0.1);
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    /* Detail Grid - Horizontal Layout */
    .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 16px;
      margin-top: 0;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
      padding: 12px;
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

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #999;
    }

    .empty-state i {
      font-size: 48px;
      margin-bottom: 16px;
      opacity: 0.5;
    }

    .department-info {
      font-size: 11px;
      color: #666;
      margin-top: 4px;
    }

    @media (max-width: 768px) {
      .stats-container {
        grid-template-columns: 1fr;
      }

      .action-buttons {
        flex-direction: column;
        gap: 8px;
      }

      .btn-action {
        width: 100%;
        justify-content: center;
      }
    }

    .returns-modern {
      --rm-bg: #f5f6fa;
      --rm-card: #ffffff;
      --rm-border: #e8ecf4;
      --rm-primary: #1a2340;
      --rm-muted: #8492a6;
      --rm-accent: #2563eb;
      --rm-teal: #0f766e;
      --rm-teal-soft: #ecfdf5;
      --rm-warning: #d97706;
      --rm-warning-soft: #fffbeb;
      --rm-radius: 14px;
      --rm-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
      color: var(--rm-primary);
      font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
      background: var(--rm-bg);
      padding: 24px 28px 36px;
      min-height: calc(100vh - 96px);
    }

    .returns-modern .returns-page-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 20px;
    }

    .returns-modern .form-title {
      margin: 0;
      background: none;
      -webkit-text-fill-color: currentColor;
      color: var(--rm-primary);
      font-size: 24px;
      letter-spacing: 0;
    }

    .returns-modern .page-kicker {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      color: var(--rm-teal);
      background: var(--rm-teal-soft);
      border: 1px solid #bbf7d0;
      border-radius: 999px;
      padding: 5px 10px;
      font-size: 11px;
      font-weight: 700;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .returns-modern .page-subtitle {
      color: var(--rm-muted);
      font-size: 12.5px;
      margin-top: 5px;
    }

    .returns-modern .page-total-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--rm-card);
      border: 1px solid var(--rm-border);
      box-shadow: var(--rm-shadow);
      color: var(--rm-primary);
      border-radius: 999px;
      padding: 9px 13px;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
    }

    .returns-modern .stats-container {
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 14px;
      margin-bottom: 18px;
    }

    .returns-modern .stat-card {
      background: var(--rm-card);
      border: 1px solid var(--rm-border);
      border-radius: var(--rm-radius);
      padding: 17px;
      box-shadow: var(--rm-shadow);
      overflow: hidden;
      position: relative;
    }

    .returns-modern .stat-card::before {
      content: '';
      position: absolute;
      inset: 0 auto 0 0;
      width: 4px;
      background: transparent;
    }

    .returns-modern .stat-card.stat-total {
      background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
      border-color: transparent;
      color: #fff;
    }

    .returns-modern .stat-card.stat-total::before {
      background: rgba(255, 255, 255, .55);
    }

    .returns-modern .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, .09);
    }

    .returns-modern .stat-label {
      color: var(--rm-muted);
      font-size: 10.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 9px;
    }

    .returns-modern .stat-card.stat-total .stat-label,
    .returns-modern .stat-card.stat-total .stat-value,
    .returns-modern .stat-card.stat-total .stat-sub {
      color: #fff;
    }

    .returns-modern .stat-value {
      font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
      font-size: 26px;
      line-height: 1;
      color: var(--rm-primary);
    }

    .returns-modern .stat-sub {
      margin-top: 6px;
      color: var(--rm-muted);
      font-size: 11px;
    }

    .returns-modern .stat-dept {
      margin-top: 10px;
      border-radius: 999px;
      padding: 4px 9px;
      font-size: 10.5px;
      box-shadow: none;
    }

    .returns-modern .search-box {
      background: var(--rm-card);
      border: 1px solid var(--rm-border);
      border-radius: var(--rm-radius);
      box-shadow: var(--rm-shadow);
      padding: 12px 14px;
      margin-bottom: 16px !important;
    }

    .returns-modern .search-box form {
      gap: 10px;
      flex-wrap: wrap;
    }

    .returns-modern .input-group,
    .returns-modern .form-select {
      height: 42px;
    }

    .returns-modern .input-group .input-group-text,
    .returns-modern .input-group .form-control,
    .returns-modern .form-select {
      border-color: var(--rm-border);
      background: #fff;
      font-size: 12.5px;
    }

    .returns-modern .input-group .input-group-text {
      border-radius: 10px 0 0 10px;
      color: var(--rm-muted);
    }

    .returns-modern .input-group .form-control {
      border-radius: 0 10px 10px 0;
    }

    .returns-modern .form-select {
      border-radius: 10px;
      color: var(--rm-primary);
    }

    .returns-modern .btn-filter-modern {
      height: 42px;
      border: none;
      border-radius: 10px;
      background: var(--rm-accent);
      color: #fff;
      padding: 0 18px;
      font-size: 12.5px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 6px 14px rgba(37, 99, 235, .18);
    }

    .returns-modern .btn-filter-modern:hover {
      background: #1d4ed8;
      color: #fff;
    }

    .returns-modern .table-dokumen {
      background: var(--rm-card);
      border: 1px solid var(--rm-border);
      border-radius: var(--rm-radius);
      box-shadow: var(--rm-shadow);
      overflow: hidden;
    }

    .returns-modern .returns-table-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding: 14px 16px;
      border-bottom: 1px solid var(--rm-border);
      background: #fff;
    }

    .returns-modern .returns-table-title {
      font-size: 13px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .returns-modern .returns-table-title i {
      color: var(--rm-teal);
    }

    .returns-modern .returns-table-meta {
      color: var(--rm-muted);
      font-size: 12px;
      white-space: nowrap;
    }

    .returns-modern .table-dokumen .table {
      margin-bottom: 0;
      min-width: 1120px;
      border-collapse: collapse;
    }

    .returns-modern .table-dokumen thead {
      background: #fafbfd;
      color: var(--rm-muted);
    }

    .returns-modern .table-dokumen thead::after {
      display: none;
    }

    .returns-modern .table-dokumen thead th {
      background: #fafbfd;
      color: var(--rm-muted);
      border-bottom: 1px solid var(--rm-border);
      padding: 12px 14px;
      font-size: 10.5px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .05em;
      text-align: left;
      white-space: nowrap;
    }

    .returns-modern .table-dokumen thead th.text-center,
    .returns-modern .table-dokumen tbody td.text-center {
      text-align: center;
    }

    .returns-modern .table-dokumen tbody tr {
      border-left: 0;
      border-bottom: 1px solid var(--rm-border);
      cursor: pointer;
    }

    .returns-modern .table-dokumen tbody tr:hover {
      background: #fafbfd;
      border-left: 0;
    }

    .returns-modern .table-dokumen tbody td {
      padding: 14px;
      color: var(--rm-primary);
      font-size: 12.5px;
      border-bottom: none;
      vertical-align: middle;
    }

    .returns-modern .doc-agenda {
      font-weight: 800;
      color: var(--rm-primary);
    }

    .returns-modern .doc-sub {
      color: var(--rm-muted);
      font-size: 11px;
      margin-top: 3px;
    }

    .returns-modern .doc-amount {
      font-weight: 800;
      color: var(--rm-primary);
      white-space: nowrap;
    }

    .returns-modern .reason-text {
      max-width: 220px;
      color: var(--rm-primary);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .returns-modern .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      white-space: nowrap;
    }

    .returns-modern .status-pill.waiting {
      background: var(--rm-warning-soft);
      color: var(--rm-warning);
      border: 1px solid #fde68a;
    }

    .returns-modern .status-pill.done {
      background: var(--rm-teal-soft);
      color: var(--rm-teal);
      border: 1px solid #bbf7d0;
    }

    .returns-modern .status-pill.neutral {
      background: #f1f5f9;
      color: #64748b;
      border: 1px solid #e2e8f0;
    }

    .returns-modern .detail-content {
      background: #fbfcfe;
      border-top: 1px solid var(--rm-border);
    }

    .returns-modern .returns-pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding: 14px 16px;
      border-top: 1px solid var(--rm-border);
      background: #fff;
    }

    .returns-modern .empty-state {
      background: var(--rm-card);
      border: 1px solid var(--rm-border);
      border-radius: var(--rm-radius);
      box-shadow: var(--rm-shadow);
      color: var(--rm-muted);
      padding: 64px 24px;
    }

    .returns-modern .empty-state i {
      color: var(--rm-teal);
      opacity: .35;
    }

    @media (max-width: 1280px) {
      .returns-modern .stats-container {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    @media (max-width: 768px) {
      .returns-modern {
        padding: 18px 14px 28px;
      }

      .returns-modern .returns-page-head,
      .returns-modern .returns-pagination,
      .returns-modern .returns-table-header {
        flex-direction: column;
        align-items: stretch;
      }

      .returns-modern .stats-container {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .returns-modern .input-group,
      .returns-modern .form-select,
      .returns-modern .btn-filter-modern {
        width: 100% !important;
        max-width: none !important;
      }
    }
  </style>

  <div class="returns-modern">
    <div class="returns-page-head">
      <div>
        <div class="page-kicker">
          <i class="fa-solid fa-rotate-left"></i>
          Pengembalian Bagian
        </div>
        <h2 class="form-title">{{ $title }}</h2>
        <div class="page-subtitle">Pantau dokumen yang sedang menunggu respon dari bidang tujuan.</div>
      </div>
      <div class="page-total-pill">
        <i class="fa-solid fa-file-lines"></i>
        {{ $totalReturned }} dokumen
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
      <div class="stat-card stat-total">
        <div class="stat-label">
          <i class="fa-solid fa-building"></i>
          Total Dokumen
        </div>
        <div class="stat-value">{{ $totalReturned }}</div>
        <div class="stat-sub">Menunggu respon bidang</div>
      </div>

      @foreach($bidangStats as $stat)
        <div class="stat-card">
          <div class="stat-label">
            <i class="fa-solid fa-sitemap"></i>
            {{ $stat['kode_bidang'] }}
          </div>
          <div class="stat-value">{{ $stat['count'] }}</div>
          <div class="stat-sub">{{ $stat['nama_bidang'] ?? 'Bidang tujuan' }}</div>
          <div class="stat-dept {{ $stat['kode_bidang'] }}">{{ $stat['kode_bidang'] }}</div>
        </div>
      @endforeach
    </div>

    <!-- Search and Filter -->
    <div class="search-box d-flex align-items-center mb-4">
      <form action="{{ route('returns.verifikasi.bagian') }}" method="GET" class="d-flex align-items-center w-100">
        <div class="input-group me-3" style="max-width: 300px;">
          <span class="input-group-text">
            <i class="fa-solid fa-search"></i>
          </span>
          <input type="text" class="form-control" name="search" placeholder="Cari nomor agenda, nomor SPP, atau uraian..."
            value="{{ request('search') }}">
        </div>

        <select name="bidang" class="form-select me-3" style="width: 220px;">
          <option value="">Semua Bidang</option>
          <option value="AKN" {{ $selectedBidang == 'AKN' ? 'selected' : '' }}>AKN</option>
          <option value="DPM" {{ $selectedBidang == 'DPM' ? 'selected' : '' }}>DPM</option>
          <option value="KPL" {{ $selectedBidang == 'KPL' ? 'selected' : '' }}>KPL</option>
          <option value="PMO" {{ $selectedBidang == 'PMO' ? 'selected' : '' }}>PMO</option>
          <option value="PTI" {{ $selectedBidang == 'PTI' ? 'selected' : '' }}>PTI</option>
          <option value="SDM" {{ $selectedBidang == 'SDM' ? 'selected' : '' }}>SDM</option>
          <option value="SKH" {{ $selectedBidang == 'SKH' ? 'selected' : '' }}>SKH</option>
          <option value="TAN" {{ $selectedBidang == 'TAN' ? 'selected' : '' }}>TAN</option>
          <option value="TEP" {{ $selectedBidang == 'TEP' ? 'selected' : '' }}>TEP</option>
        </select>

        <button type="submit" class="btn-filter-modern">
          <i class="fa-solid fa-filter me-2"></i>Filter
        </button>
      </form>
    </div>

    <!-- Documents Table -->
    <div class="table-dokumen">
      @if($dokumens->count() > 0)
          <div class="returns-table-header">
            <div>
              <div class="returns-table-title">
                <i class="fa-solid fa-table-list"></i>
                Daftar Dokumen
              </div>
              <div class="page-subtitle">Klik baris untuk melihat detail dokumen.</div>
            </div>
            <div class="returns-table-meta">
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} dokumen
            </div>
          </div>

          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th class="text-center" style="width: 64px;">No</th>
                  <th>Nomor Agenda</th>
                  <th>Nomor SPP</th>
                  <th>Uraian</th>
                  <th>Nilai</th>
                  <th>Bidang Tujuan</th>
                  <th>Tanggal Pengembalian</th>
                  <th>Alasan</th>
                  <th>Status Pengembalian</th>
                </tr>
              </thead>
              <tbody>
                @foreach($dokumens as $index => $dokumen)
                  <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
                    <td class="text-center">{{ $dokumens->firstItem() + $index }}</td>
                    <td>
                      <div class="doc-agenda">{{ $dokumen->nomor_agenda }}</div>
                      <div class="doc-sub">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</div>
                    </td>
                    <td>{{ $dokumen->nomor_spp }}</td>
                    <td>{{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}</td>
                    <td>
                      <span class="doc-amount">{{ $dokumen->formatted_nilai_rupiah }}</span>
                    </td>
                    <td>
                      <span class="bidang-badge {{ $dokumen->return_source }}">
                        {{ $dokumen->return_source }}
                      </span>
                    </td>
                    <td>
                      @php $returnedAtDate = $dokumen->returned_at; @endphp
                      <small>{{ $returnedAtDate ? $returnedAtDate->format('d-m-Y H:i') : '-' }}</small>
                    </td>
                    <td>
                      <div class="reason-text" title="{{ $dokumen->return_reason ?? '-' }}">
                        {{ Str::limit($dokumen->return_reason ?? '-', 30) }}
                      </div>
                    </td>
                    <td>
                      @if($dokumen->status == 'returned_to_bidang')
                        <span class="status-pill waiting">
                          <i class="fa-solid fa-clock me-1"></i>Menunggu Respon Bagian
                        </span>
                      @elseif(in_array($dokumen->status, ['sent_to_team_verifikasi', 'sedang_diproses', 'sedang diproses', 'menunggu_approval_keuangan']))
                        <span class="status-pill done">
                          <i class="fa-solid fa-check me-1"></i>Sudah Dikirim Kembali
                        </span>
                      @else
                        <span class="status-pill neutral">
                          {{ $dokumen->status }}
                        </span>
                      @endif
                    </td>
                  </tr>
                  <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display: none;">
                    <td colspan="9">
                      <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                        <div class="text-center p-4">
                          <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="returns-pagination">
            <div class="returns-table-meta">
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }}
              dokumen
            </div>
            {{ $dokumens->links() }}
          </div>
        @else
        <div class="empty-state">
          <i class="fa-solid fa-building"></i>
          <h5>Belum ada dokumen</h5>
          <p class="mt-2">Tidak ada dokumen yang dikembalikan ke bagian saat ini.</p>
          <a href="{{ route('documents.verifikasi.index') }}" class="btn btn-primary mt-3">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Daftar Dokumen
          </a>
        </div>
      @endif
    </div>
  </div>


  <script>
    // Toggle detail row
    function toggleDetail(docId) {
      const detailRow = document.getElementById('detail-' + docId);
      const chevron = document.getElementById('chevron-' + docId);

      if (detailRow.style.display === 'none' || !detailRow.style.display) {
        // Show detail
        loadDocumentDetail(docId);
        detailRow.style.display = 'table-row';
        if (chevron) chevron.classList.add('rotate');
      } else {
        // Hide detail
        detailRow.style.display = 'none';
        if (chevron) chevron.classList.remove('rotate');
      }
    }

    // Load document detail
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

    // Available documents data (passed from PHP to JavaScript)
    const documentsData = @json($dokumens->keyBy('id'));

    // Send back to main list
    // Notification function
    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `notification notification-${type}`;
      notification.innerHTML = `
                      <div class="notification-content">
                        <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
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

    // Auto-refresh notification badge
    function updateNotificationBadge() {
      fetch('/pengembalian-dokumens-ke-bagian/stats')
        .then(response => response.json())
        .then(data => {
          const badge = document.getElementById('pengembalian-ke-bagian-badge');
          if (badge && data.total > 0) {
            badge.textContent = data.total;
            badge.style.display = 'inline-flex';
          } else if (badge) {
            badge.style.display = 'none';
          }
        })
        .catch(error => console.log('Error updating badge:', error));
    }

    // Update badge on page load
    document.addEventListener('DOMContentLoaded', function () {
      updateNotificationBadge();

      // Update badge every 30 seconds
      setInterval(updateNotificationBadge, 30000);
    });
  </script>

@endsection

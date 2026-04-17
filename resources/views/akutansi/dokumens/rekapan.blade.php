@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
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
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
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
    box-shadow: 0 12px 40px rgba(26, 77, 62, 0.2), 0 4px 16px rgba(15, 61, 46, 0.1);
    border-color: rgba(26, 77, 62, 0.15);
  }

  .stat-card-body {
    position: relative;
    z-index: 1;
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
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
  }

  .stat-icon.total { background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%); }
  .stat-icon.bagian { background: linear-gradient(135deg, #2d6a4f 0%, #1b5e3f 100%); }
  .stat-icon.status { background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%); }

  .stat-content {
    flex: 1;
    min-width: 0;
  }

  .stat-title {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
    line-height: 1;
  }

  .stat-description {
    font-size: 11px;
    color: #868e96;
    opacity: 0.8;
  }

  /* Filter Section */
  .filter-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
  }

  .filter-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #1a4d3e;
  }

  .filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
  }

  .form-group {
    margin-bottom: 0;
  }

  .form-group label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 8px;
    color: #083E40;
    letter-spacing: 0.3px;
  }

  .form-group input,
  .form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background-color: #ffffff;
  }

  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
    background-color: #fffef8;
  }

  /* Bagian Statistics - Style konsisten dengan rekapan-Team Verifikasi dan rekapan-perpajakan */
  .bagian-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 20px;
  }

  .bagian-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    border: 1px solid rgba(26, 77, 62, 0.08);
    transition: all 0.3s ease;
  }

  .bagian-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(26, 77, 62, 0.15);
    border-color: rgba(26, 77, 62, 0.15);
  }

  .bagian-name {
    font-weight: 700;
    color: #1a4d3e;
    margin-bottom: 8px;
    font-size: 16px;
  }

  .bagian-count {
    font-size: 24px;
    font-weight: 700;
    color: #40916c;
  }

  /* Bagian Statistics - Layout like pengembalian ke bidang (old style, keep for compatibility) */
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
    text-align: center;
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

  .stat-dept.DPM { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .stat-dept.SKH { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .stat-dept.SDM { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
  .stat-dept.TEP { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
  .stat-dept.KPL { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
  .stat-dept.AKN { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
  .stat-dept.TAN { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
  .stat-dept.PMO { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }

  /* Detail Grid for each bagian */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
    margin-top: 12px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 8px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }

  .detail-item:hover {
    background: white;
    border-color: #889717;
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.1);
    transform: translateY(-1px);
  }

  .detail-label {
    font-size: 10px;
    font-weight: 600;
    color: #083E40;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
  }

  .detail-value {
    font-size: 14px;
    color: #333;
    font-weight: 600;
  }

  /* Table Section */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
  }

  .table-responsive {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
  }

  /* Always visible scrollbar */
  .table-responsive::-webkit-scrollbar {
    height: 12px;
    -webkit-appearance: none;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    border-radius: 6px;
    border: 2px solid #ffffff;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
  }

  /* Firefox scrollbar - always visible */
  .table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #1a4d3e #f1f1f1;
  }

  .table-container h6 {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(26, 77, 62, 0.1);
  }

  .table-container h6 span {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 20px;
  }

  .table {
    margin-bottom: 0;
  }

  .table thead {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%) !important;
  }

  .table thead th {
    background: transparent !important;
    color: white !important;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.5px;
    padding: 18px 16px;
    border: none !important;
    text-transform: uppercase;
  }

  .table tbody tr {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table tbody tr:hover {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
    transform: scale(1.002);
  }

  .table tbody td {
    padding: 16px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
  }

  .badge {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .badge-draft { background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: #333; }
  .badge-sent { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
  .badge-processing { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; }
  .badge-completed { background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%); color: white; }
  .badge-returned { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
  .badge-unknown { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }

  /* Pagination */
  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
  }

  .pagination .page-link {
    border: 2px solid rgba(26, 77, 62, 0.1);
    background-color: white;
    color: #1a4d3e;
    border-radius: 10px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin: 0 2px;
    min-width: 40px;
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .pagination .page-link:hover {
    border-color: #40916c;
    background: linear-gradient(135deg, rgba(64, 145, 108, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    border-color: transparent;
    color: white;
  }

  .pagination .page-link.active {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    border-color: transparent;
    color: white;
    cursor: default;
  }

  .btn-filter {
    padding: 12px 24px;
    border: 2px solid #1a4d3e;
    background: transparent;
    color: #1a4d3e;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
  }

  .btn-filter:hover {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 77, 62, 0.3);
  }

  /* Responsive Design untuk bagian stats - konsisten dengan halaman lain */
  @media (max-width: 768px) {
    .bagian-stats {
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 12px;
    }

    .bagian-name {
      font-size: 14px;
    }

    .bagian-count {
      font-size: 20px;
    }
  }

  /* Responsive */
  @media (max-width: 768px) {
    .filter-row {
      grid-template-columns: 1fr;
    }

    .stats-container {
      grid-template-columns: 1fr;
    }

    .detail-grid {
      grid-template-columns: repeat(3, 1fr);
    }

    .detail-item {
      padding: 6px;
    }

    .detail-label {
      font-size: 9px;
    }

    .detail-value {
      font-size: 12px;
    }

    .stat-value {
      font-size: 24px;
    }

    .stat-dept {
      font-size: 11px;
      padding: 3px 6px;
    }
  }

  @media (max-width: 480px) {
    .detail-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .stat-value {
      font-size: 20px;
    }
  }
</style>

<h2>{{ $title }}</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-xl-4 col-lg-6 mb-4">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-icon total">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="stat-content">
          <div class="stat-title">Total Dokumen</div>
          <div class="stat-value">{{ number_format($statistics['total_documents'], 0, ',', '.') }}</div>
          <div class="stat-description">Semua dokumen</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-lg-6 mb-4">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-icon bagian">
          <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
          <div class="stat-title">Total Bagian</div>
          <div class="stat-value">{{ count(array_filter($statistics['by_bagian'], fn($b) => $b['total'] > 0)) }}</div>
          <div class="stat-description">Bagian aktif</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-lg-6 mb-4">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-icon status">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
          <div class="stat-title">Dokumen Aktif</div>
          <div class="stat-value">{{ number_format($statistics['by_status']['sent_to_team_verifikasi'] + $statistics['by_status']['sedang diproses'], 0, ',', '.') }}</div>
          <div class="stat-description">Sedang diproses</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Statistik per Bagian (Sesuai style rekapan-Team Verifikasi & rekapan-perpajakan) -->
@if(!$selectedBagian)
<div class="row mb-4">
  <div class="col-12">
    <h5 class="mb-3">Statistik per Bagian</h5>
    <div class="bagian-stats">
      @foreach($statistics['by_bagian'] as $code => $bagian)
        @if($bagian['total'] > 0)
          <div class="bagian-card">
            <div class="bagian-name">{{ $bagian['name'] }}</div>
            <div class="bagian-count">{{ $bagian['total'] }}</div>
          </div>
        @endif
      @endforeach
    </div>
  </div>
</div>
@else
<div class="row mb-4">
  <div class="col-12">
    <h5 class="mb-3">Statistik Bagian {{ $bagianList[$selectedBagian] ?? '' }}</h5>
    <div class="row">
      <div class="col-md-3 col-6 mb-3">
        <div class="bagian-card">
          <div class="bagian-name">Draft</div>
          <div class="bagian-count">{{ $statistics['by_status']['draft'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="bagian-card">
          <div class="bagian-name">Terkirim</div>
          <div class="bagian-count">{{ $statistics['by_status']['sent_to_team_verifikasi'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="bagian-card">
          <div class="bagian-name">Diproses</div>
          <div class="bagian-count">{{ $statistics['by_status']['sedang diproses'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="bagian-card">
          <div class="bagian-name">Selesai</div>
          <div class="bagian-count">{{ $statistics['by_status']['selesai'] }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Filter Section -->
<div class="filter-section">
  <h6 class="filter-title">Filter Dokumen</h6>
  <form method="GET" action="{{ route('reports.akutansi.index') }}">
    <div class="filter-row">
      <div class="form-group">
        <label for="bagian">Filter Bagian</label>
        <select name="bagian" id="bagian">
          <option value="">Semua Bagian</option>
          @foreach($bagianList as $code => $name)
            <option value="{{ $code }}" {{ $selectedBagian == $code ? 'selected' : '' }}>
              {{ $name }} ({{ $statistics['by_bagian'][$code]['total'] }} dokumen)
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="search">Search</label>
        <input type="text" name="search" id="search" placeholder="Cari nomor agenda, SPP, uraian..." value="{{ request('search') }}">
      </div>
      <div class="form-group">
        <label for="year">Tahun</label>
        <select name="year" id="year">
          <option value="">Semua Tahun</option>
          @for($year = date('Y'); $year >= 2020; $year--)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endfor
        </select>
      </div>
    </div>
    <button type="submit" class="btn-filter">
      <i class="fas fa-filter"></i> Terapkan Filter
    </button>
  </form>
</div>

<!-- Table Section -->
<div class="table-container">
  <h6>
    <span>Daftar Dokumen {{ $selectedBagian ? "- Bagian " . $bagianList[$selectedBagian] : '' }}</span>
  </h6>

  @if($selectedBagian)
    <div class="mb-3">
      <a href="{{ route('reports.akutansi.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke semua bagian
      </a>
    </div>
  @endif

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Nomor Agenda</th>
          <th>Bagian</th>
          <th>Pengirim</th>
          <th>Nomor SPP</th>
          <th>Tanggal Masuk</th>
          <th>Nilai Rupiah</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($dokumens as $index => $dokumen)
          <tr>
            <td>{{ ($dokumens->currentPage() - 1) * $dokumens->perPage() + $index + 1 }}</td>
            <td>
              <strong>{{ $dokumen->nomor_agenda }}</strong>
              <br>
              <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
            </td>
            <td>
              @if($dokumen->bagian)
                <span class="badge" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white;">
                  {{ $dokumen->bagian }}
                </span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>{{ $dokumen->nama_pengirim ?? '-' }}</td>
            <td>{{ $dokumen->nomor_spp }}</td>
            <td>{{ $dokumen->tanggal_masuk->format('d/m/Y H:i') }}</td>
            <td>
              <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
            </td>
            <td>
              @switch($dokumen->status)
                @case('draft')
                  <span class="badge badge-draft">
                    <i class="fas fa-clock"></i> Draft
                  </span>
                  @break
                @case('sent_to_team_verifikasi')
                  <span class="badge badge-sent">
                    <i class="fas fa-paper-plane"></i> Terkirim ke Team Verifikasi
                  </span>
                  @break
                @case('sent_to_perpajakan')
                  <span class="badge badge-sent">
                    <i class="fas fa-paper-plane"></i> Terkirim ke Team Perpajakan
                  </span>
                  @break
                @case('sent_to_akutansi')
                  <span class="badge badge-sent">
                    <i class="fas fa-paper-plane"></i> Terkirim ke Team Akutansi
                  </span>
                  @break
                @case('sent_to_pembayaran')
                  <span class="badge badge-sent">
                    <i class="fas fa-paper-plane"></i> Terkirim ke Team Pembayaran
                  </span>
                  @break
                @case('sedang diproses')
                  <span class="badge badge-processing">
                    <i class="fas fa-spinner"></i> Sedang Diproses
                  </span>
                  @break
                @case('selesai')
                  <span class="badge badge-completed">
                    <i class="fas fa-check"></i> Selesai
                  </span>
                  @break
                @case('returned_to_operator')
                  <span class="badge badge-returned">
                    <i class="fas fa-undo"></i> Dikembalikan ke Ibu Tarapul
                  </span>
                  @break
                @case('returned_to_department')
                  <span class="badge badge-returned">
                    <i class="fas fa-undo"></i> Dikembalikan ke Bagian
                  </span>
                  @break
                @case('returned_to_bidang')
                  <span class="badge badge-returned">
                    <i class="fas fa-undo"></i> Dikembalikan ke Bidang
                  </span>
                  @break
                @default
                  <span class="badge badge-unknown">
                    <i class="fas fa-question"></i> {{ ucfirst(str_replace('_', ' ', $dokumen->status)) }}
                  </span>
              @endswitch
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-4">
              <i class="fa-solid fa-inbox fa-2x text-muted mb-2"></i>
              <p class="text-muted mb-0">Tidak ada dokumen yang ditemukan.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  @include('partials.pagination-enhanced', ['paginator' => $dokumens])
</div>

@endsection





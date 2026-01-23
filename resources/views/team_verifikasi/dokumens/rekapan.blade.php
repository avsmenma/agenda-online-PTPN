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

  .form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
  }

  .form-control, .form-select {
    border: 2px solid rgba(26, 77, 62, 0.1);
    border-radius: 12px;
    padding: 12px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #40916c;
    box-shadow: 0 0 0 4px rgba(64, 145, 108, 0.1);
    outline: none;
  }

  .btn-primary {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    border: none;
    border-radius: 12px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(26, 77, 62, 0.2);
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #0f3d2e 0%, #0a2a1f 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 77, 62, 0.3);
  }

  /* Table Styles */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow-x: auto;
    overflow-y: visible;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
    -webkit-overflow-scrolling: touch;
  }

  /* Always visible scrollbar */
  .table-container::-webkit-scrollbar {
    height: 12px;
    -webkit-appearance: none;
  }

  .table-container::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    border-radius: 6px;
    border: 2px solid #ffffff;
  }

  .table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
  }

  /* Firefox scrollbar - always visible */
  .table-container {
    scrollbar-width: thin;
    scrollbar-color: #1a4d3e #f1f1f1;
  }

  .table {
    margin: 0;
  }

  .table thead th {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 16px 12px;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
  }

  .table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
  }

  .table tbody tr:hover {
    background: linear-gradient(135deg, rgba(64, 145, 108, 0.05) 0%, rgba(26, 77, 62, 0.02) 100%);
    transform: scale(1.005);
  }

  .table tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    font-size: 13px;
    font-weight: 500;
    color: #2c3e50;
  }

  /* Badge Styles */
  .badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .badge-draft {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
  }

  .badge-sent {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
  }

  .badge-processing {
    background: linear-gradient(135deg, #2d6a4f 0%, #1b5e3f 100%);
    color: white;
  }

  .badge-completed {
    background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%);
    color: white;
  }

  .badge-returned {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }
  .badge-unknown {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
  }

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

  /* Bagian Statistics */
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

  /* Responsive Design */
  @media (max-width: 768px) {
    .stat-card {
      height: 120px;
      padding: 20px;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      font-size: 20px;
    }

    .stat-value {
      font-size: 28px;
    }

    .filter-section {
      padding: 20px;
    }

    .bagian-stats {
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 12px;
    }
  }
</style>

<h2>Rekapan Dokumen</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-md-4 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Total Dokumen</div>
          <div class="stat-value">{{ $statistics['total_documents'] }}</div>
          <div class="stat-description">Semua dokumen yang dOperatort oleh Ibu Tarapul</div>
        </div>
        <div class="stat-icon total">
          <i class="fa-solid fa-file-lines"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <a href="{{ route('reports.verifikasi.analytics') }}" style="text-decoration: none; display: block;">
      <div class="stat-card" style="cursor: pointer;">
        <div class="stat-card-body">
          <div class="stat-content">
            <div class="stat-title">Total Dokumen Bagian</div>
            <div class="stat-value">{{ $statistics['total_documents'] }}</div>
            <div class="stat-description">Klik untuk melihat analitik</div>
          </div>
          <div class="stat-icon bagian">
            <i class="fa-solid fa-chart-line"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4 mb-3">
    <div class="stat-card">
      <div class="stat-card-body">
        <div class="stat-content">
          <div class="stat-title">Selesai</div>
          <div class="stat-value">{{ $statistics['by_status']['selesai'] }}</div>
          <div class="stat-description">Dokumen yang telah selesai diproses</div>
        </div>
        <div class="stat-icon bagian">
          <i class="fa-solid fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Filter Section (Dipindahkan ke posisi 3) -->
<div class="filter-section">
  <form method="GET" action="{{ route('reports.verifikasi.index') }}">
    <div class="row g-3">
      <div class="col-md-4">
        <label for="bagian" class="form-label">Filter Bagian</label>
        <select name="bagian" id="bagian" class="form-select">
          <option value="">Semua Bagian ({{ $statistics['total_documents'] }} dokumen)</option>
          @foreach($bagianList as $code => $name)
            <option value="{{ $code }}" {{ $selectedBagian == $code ? 'selected' : '' }}>
              {{ $name }} ({{ $statistics['by_bagian'][$code]['total'] ?? 0 }} dokumen)
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label for="year" class="form-label">Filter Tahun</label>
        <select name="year" id="year" class="form-select">
          <option value="">Semua Tahun</option>
          @for($year = date('Y'); $year >= 2020; $year--)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
              {{ $year }}
            </option>
          @endfor
        </select>
      </div>
      <div class="col-md-4">
        <label for="search" class="form-label">Cari Dokumen</label>
        <input type="text" name="search" id="search" class="form-control"
               placeholder="Nomor agenda, SPP, atau uraian..."
               value="{{ request('search') }}">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-filter me-2"></i>Filter Data
        </button>
        <a href="{{ route('reports.verifikasi.index') }}" class="btn btn-secondary ms-2">
          <i class="fa-solid fa-refresh me-2"></i>Reset
        </a>
      </div>
    </div>
  </form>
</div>

<!-- Bagian Statistics -->
@if($selectedBagian)
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

<!-- Documents Table -->
<div class="table-container">
  <h6>
    <span>Daftar Dokumen {{ $selectedBagian ? "- Bagian " . $bagianList[$selectedBagian] : '' }}</span>
  </h6>
  <table class="table table-hover">
    <thead>
      <tr>
        <th>No</th>
        <th>Nomor Agenda</th>
        <th>Nomor SPP</th>
        <th>Tanggal Masuk</th>
        <th>Uraian SPP</th>
        <th>Nilai Rupiah</th>
        <th>Bagian</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dokumens as $index => $dokumen)
        <tr>
          <td>{{ $dokumens->firstItem() + $index }}</td>
          <td>{{ $dokumen->nomor_agenda }}</td>
          <td>{{ $dokumen->nomor_spp }}</td>
          <td>{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y') : '-' }}</td>
          <td>{{ Str::limit($dokumen->uraian_spp, 50) }}</td>
          <td>{{ $dokumen->formatted_nilai_rupiah }}</td>
          <td>{{ $dokumen->bagian ?? '-' }}</td>
          <td>
            @switch($dokumen->status)
              @case('draft')
                <span class="badge badge-draft">Draft</span>
                @break
              @case('sent_to_team_verifikasi')
                <span class="badge badge-sent">Terkirim ke Team Verifikasi</span>
                @break
              @case('sent_to_perpajakan')
                <span class="badge badge-sent">Terkirim ke Team Perpajakan</span>
                @break
              @case('sent_to_akutansi')
                <span class="badge badge-sent">Terkirim ke Team Akutansi</span>
                @break
              @case('sent_to_pembayaran')
                <span class="badge badge-sent">Terkirim ke Team Pembayaran</span>
                @break
              @case('sedang diproses')
                <span class="badge badge-processing">Sedang Diproses</span>
                @break
              @case('selesai')
                <span class="badge badge-completed">Selesai</span>
                @break
              @case('returned_to_Operator')
                <span class="badge badge-returned">Dikembalikan ke Ibu Tarapul</span>
                @break
              @case('returned_to_department')
                <span class="badge badge-returned">Dikembalikan ke Bagian</span>
                @break
              @case('returned_to_bidang')
                <span class="badge badge-returned">Dikembalikan ke Bidang</span>
                @break
              @default
                <span class="badge badge-unknown">{{ ucfirst(str_replace('_', ' ', $dokumen->status)) }}</span>
            @endswitch
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-4">
            <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Pagination -->
@include('partials.pagination-enhanced', ['paginator' => $dokumens])

@endsection




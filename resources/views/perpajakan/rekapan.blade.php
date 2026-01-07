@extends('layouts/app')
@section('content')
<!-- Tailwind CSS CDN for responsive utilities -->
<script src="https://cdn.tailwindcss.com"></script>

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

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
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
    border: 2px solid rgba(23, 162, 184, 0.1);
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

  /* Responsive Design */
  @media (max-width: 768px) {
    .filter-section {
      padding: 20px;
    }
  }
</style>

<h2>Rekapan Dokumen</h2>

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

<!-- Filter Section (Dipindahkan ke posisi 3) -->
<div class="filter-section">
  <form method="GET" action="{{ route('reports.perpajakan.index') }}">
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
        <a href="{{ route('reports.perpajakan.index') }}" class="btn btn-secondary ms-2">
          <i class="fa-solid fa-refresh me-2"></i>Reset
        </a>
      </div>
    </div>
  </form>
</div>


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
              @case('sent_to_ibub')
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
              @case('returned_to_ibua')
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
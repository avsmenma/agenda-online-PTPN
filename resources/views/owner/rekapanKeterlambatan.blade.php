@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(220, 53, 69, 0.1), 0 2px 8px rgba(200, 35, 51, 0.05);
    border: 1px solid rgba(220, 53, 69, 0.08);
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
    box-shadow: 0 12px 40px rgba(220, 53, 69, 0.2), 0 4px 16px rgba(200, 35, 51, 0.1);
    border-color: rgba(220, 53, 69, 0.15);
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
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  }

  .stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #dc3545;
    margin-bottom: 4px;
    line-height: 1;
  }

  .stat-title {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .filter-section {
    background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(220, 53, 69, 0.1), 0 2px 8px rgba(200, 35, 51, 0.05);
    border: 1px solid rgba(220, 53, 69, 0.08);
  }

  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(220, 53, 69, 0.1), 0 2px 8px rgba(200, 35, 51, 0.05);
    border: 1px solid rgba(220, 53, 69, 0.08);
  }

  .table-responsive {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
  }

  .table-responsive::-webkit-scrollbar {
    height: 12px;
    -webkit-appearance: none;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border-radius: 6px;
    border: 2px solid #ffffff;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
  }

  .table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #dc3545 #f1f1f1;
  }

  .table thead {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
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

  .table tbody tr.clickable-row {
    cursor: pointer;
  }

  .table tbody tr.clickable-row:hover {
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.05) 0%, transparent 100%);
    border-left: 3px solid #dc3545;
    transform: scale(1.002);
  }

  .table tbody tr:hover {
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.05) 0%, transparent 100%);
    border-left: 3px solid #dc3545;
    transform: scale(1.002);
  }

  .badge {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .badge-terlambat {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white !important;
  }

  .badge-draft {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: #333 !important;
  }

  .badge-sent {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white !important;
  }

  .badge-processing {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white !important;
  }

  .badge-completed {
    background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
    color: white !important;
  }

  .badge-returned {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white !important;
  }

  .badge-unknown {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white !important;
  }

  .handler-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #f8f9fa;
    color: #495057;
    border: 1px solid #dee2e6;
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
  }

  .pagination a, .pagination span {
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .pagination .page-link {
    color: #dc3545;
    background: white;
    border: 2px solid #dc3545;
  }

  .pagination .page-link:hover {
    background: #dc3545;
    color: white;
  }

  .pagination .active .page-link {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
  }

  .handler-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .handler-stat-card {
    background: white;
    padding: 16px;
    border-radius: 12px;
    border-left: 4px solid #dc3545;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .handler-stat-name {
    font-size: 12px;
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 8px;
    text-transform: uppercase;
  }

  .handler-stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #dc3545;
  }
</style>

<div class="container-fluid">
  <h2><i class="fa-solid fa-exclamation-triangle"></i> Rekapan Keterlambatan</h2>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-4 mb-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="stat-title">Total Terlambat</div>
            <div class="stat-value">{{ $totalTerlambat }}</div>
          </div>
          <div class="stat-icon">
            <i class="fa-solid fa-clock"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Handler Statistics -->
  <div class="handler-stats">
    @foreach($terlambatByHandler as $handlerCode => $count)
      @if($count > 0)
        <div class="handler-stat-card">
          <div class="handler-stat-name">{{ $handlerList[$handlerCode] ?? $handlerCode }}</div>
          <div class="handler-stat-value">{{ $count }}</div>
        </div>
      @endif
    @endforeach
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <form method="GET" action="{{ url('/owner/rekapan-keterlambatan') }}" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Cari Dokumen</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nomor agenda, SPP, dll">
      </div>
      <div class="col-md-3">
        <label class="form-label">Tahun</label>
        <select name="year" class="form-select">
          <option value="">Semua Tahun</option>
          @foreach($availableYears as $year)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Handler</label>
        <select name="handler" class="form-select">
          <option value="">Semua Handler</option>
          @foreach($handlerList as $code => $name)
            <option value="{{ $code }}" {{ $selectedHandler == $code ? 'selected' : '' }}>{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">&nbsp;</label>
        <button type="submit" class="btn btn-danger w-100">
          <i class="fa-solid fa-search"></i> Filter
        </button>
      </div>
    </form>
  </div>

  <!-- Table Section -->
  <div class="table-container">
    <h6><span>Daftar Dokumen Terlambat</span></h6>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Nomor SPP</th>
            <th>Uraian SPP</th>
            <th>Nilai Rupiah</th>
            <th>Handler</th>
            <th>Deadline</th>
            <th>Keterlambatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            @php
              $now = \Carbon\Carbon::now();
              $deadline = \Carbon\Carbon::parse($dokumen->deadline_at);
              
              // Calculate keterlambatan in a more readable format
              // Since deadline has passed, we calculate the difference
              $diff = $deadline->diff($now);
              $terlambatHari = $diff->days;
              $terlambatJam = $diff->h;
              $terlambatMenit = $diff->i;
              
              // Format keterlambatan: hari + jam + menit
              $keterlambatanParts = [];
              
              if ($terlambatHari > 0) {
                $keterlambatanParts[] = $terlambatHari . ' hari';
              }
              if ($terlambatJam > 0) {
                $keterlambatanParts[] = $terlambatJam . ' jam';
              }
              if ($terlambatMenit > 0) {
                $keterlambatanParts[] = $terlambatMenit . ' menit';
              }
              
              // Join parts with space, or show "0 menit" if empty
              $keterlambatanText = !empty($keterlambatanParts) 
                ? implode(' ', $keterlambatanParts) 
                : '0 menit';
            @endphp
            <tr class="clickable-row" onclick="window.location.href='{{ route('owner.workflow', ['id' => $dokumen->id]) }}'" title="Klik untuk melihat detail workflow dokumen">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td>{{ $dokumen->nomor_agenda }}</td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ Str::limit($dokumen->uraian_spp, 50) }}</td>
              <td>Rp. {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</td>
              <td>
                <span class="handler-badge">
                  {{ $handlerList[$dokumen->current_handler] ?? $dokumen->current_handler }}
                </span>
              </td>
              <td>{{ $dokumen->deadline_at ? $dokumen->deadline_at->format('d M Y H:i') : '-' }}</td>
              <td>
                <span class="badge-terlambat">
                  <i class="fa-solid fa-exclamation-triangle"></i>
                  {{ $keterlambatanText }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <i class="fa-solid fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">Tidak ada dokumen terlambat</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($dokumens->hasPages())
      <div class="pagination">
        @if($dokumens->onFirstPage())
          <span class="page-link disabled">«</span>
        @else
          <a href="{{ $dokumens->previousPageUrl() }}" class="page-link">«</a>
        @endif

        @for($i = 1; $i <= $dokumens->lastPage(); $i++)
          @if($i == $dokumens->currentPage())
            <span class="page-link active">{{ $i }}</span>
          @else
            <a href="{{ $dokumens->url($i) }}" class="page-link">{{ $i }}</a>
          @endif
        @endfor

        @if($dokumens->hasMorePages())
          <a href="{{ $dokumens->nextPageUrl() }}" class="page-link">»</a>
        @else
          <span class="page-link disabled">»</span>
        @endif
      </div>
    @endif
  </div>
</div>

@endsection


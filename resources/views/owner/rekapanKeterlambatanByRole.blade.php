@extends('layouts/app')
@section('content')

<style>
  /* Color Variables - Always use Perpajakan Green Theme */
  :root {
    --primary-color: #1a4d3e;
    --primary-dark: #0f3d2e;
    --secondary-color: #40916c;
    --primary-rgba: rgba(26, 77, 62, 0.1);
    --primary-rgba-dark: rgba(26, 77, 62, 0.2);
    --primary-border: rgba(26, 77, 62, 0.08);
    --primary-border-hover: rgba(26, 77, 62, 0.15);
    --card-green: #28a745;
    --card-yellow: #ffc107;
    --card-red: #dc3545;
  }

  h2 {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  /* Timeframe Settings Panel */
  .timeframe-settings {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid var(--primary-border);
  }

  .timeframe-settings-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }


  /* Card Statistics */
  .card-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .card-stat {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid var(--primary-border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .card-stat::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
  }

  .card-stat.card-green::before {
    background: linear-gradient(135deg, var(--card-green) 0%, #20c997 100%);
  }

  .card-stat.card-yellow::before {
    background: linear-gradient(135deg, var(--card-yellow) 0%, #ffcd39 100%);
  }

  .card-stat.card-red::before {
    background: linear-gradient(135deg, var(--card-red) 0%, #c82333 100%);
  }

  .card-stat:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px var(--primary-rgba-dark), 0 4px 16px rgba(64, 145, 108, 0.1);
  }

  .card-stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
  }

  .card-stat-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--primary-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .card-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
  }

  .card-stat-icon.icon-green {
    background: linear-gradient(135deg, var(--card-green) 0%, #20c997 100%);
  }

  .card-stat-icon.icon-yellow {
    background: linear-gradient(135deg, var(--card-yellow) 0%, #ffcd39 100%);
  }

  .card-stat-icon.icon-red {
    background: linear-gradient(135deg, var(--card-red) 0%, #c82333 100%);
  }

  .card-stat-value {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 8px;
  }

  .card-stat-value.value-green {
    color: var(--card-green);
  }

  .card-stat-value.value-yellow {
    color: var(--card-yellow);
  }

  .card-stat-value.value-red {
    color: var(--card-red);
  }

  .card-stat-label {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Table Section */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid var(--primary-border);
  }

  .table-responsive {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
  }

  .table thead {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
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
    background: linear-gradient(90deg, var(--primary-rgba) 0%, transparent 100%);
    border-left: 3px solid var(--secondary-color);
    transform: scale(1.002);
  }

  .age-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
  }

  .age-badge.age-green {
    background: linear-gradient(135deg, var(--card-green) 0%, #20c997 100%);
    color: white;
  }

  .age-badge.age-yellow {
    background: linear-gradient(135deg, var(--card-yellow) 0%, #ffcd39 100%);
    color: #333;
  }

  .age-badge.age-red {
    background: linear-gradient(135deg, var(--card-red) 0%, #c82333 100%);
    color: white;
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
    color: var(--primary-color);
    background: white;
    border: 2px solid var(--primary-color);
  }

  .pagination .page-link:hover {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
  }

  .pagination .active .page-link {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border-color: var(--primary-color);
  }

  .form-control, .form-select {
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 8px;
    padding: 10px 16px;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem var(--primary-rgba);
  }

  .btn-filter {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 24px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--primary-rgba-dark);
  }
</style>

<div class="container-fluid">
  <h2><i class="fa-solid fa-exclamation-triangle"></i> Rekapan Keterlambatan - {{ $roleConfig[$roleCode]['name'] }}</h2>


  <!-- Card Statistics -->
  @if(in_array($roleCode, ['ibuB', 'perpajakan', 'akutansi']))
  <div class="card-stats">
    <div class="card-stat card-green">
      <div class="card-stat-header">
        <div class="card-stat-title">Dokumen {{ $cardStats['card1']['label'] }}</div>
        <div class="card-stat-icon icon-green">
          <i class="fa-solid fa-check-circle"></i>
        </div>
      </div>
      <div class="card-stat-value value-green">{{ $cardStats['card1']['count'] }}</div>
      <div class="card-stat-label">Dokumen</div>
    </div>
    <div class="card-stat card-yellow">
      <div class="card-stat-header">
        <div class="card-stat-title">Dokumen {{ $cardStats['card2']['label'] }}</div>
        <div class="card-stat-icon icon-yellow">
          <i class="fa-solid fa-clock"></i>
        </div>
      </div>
      <div class="card-stat-value value-yellow">{{ $cardStats['card2']['count'] }}</div>
      <div class="card-stat-label">Dokumen</div>
    </div>
    <div class="card-stat card-red">
      <div class="card-stat-header">
        <div class="card-stat-title">Dokumen {{ $cardStats['card3']['label'] }}</div>
        <div class="card-stat-icon icon-red">
          <i class="fa-solid fa-exclamation-triangle"></i>
        </div>
      </div>
      <div class="card-stat-value value-red">{{ $cardStats['card3']['count'] }}</div>
      <div class="card-stat-label">Dokumen</div>
    </div>
  </div>
  @endif

  <!-- Filter Section -->
  <div class="timeframe-settings" style="margin-bottom: 30px;">
    <div class="timeframe-settings-title"><i class="fa-solid fa-filter"></i> Filter Data</div>
    <form method="GET" action="{{ route('owner.rekapan-keterlambatan.role', $roleCode) }}" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Cari Dokumen</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nomor agenda, SPP, dll">
      </div>
      <div class="col-md-4">
        <label class="form-label">Tahun</label>
        <select name="year" class="form-select">
          <option value="">Semua Tahun</option>
          @foreach($availableYears as $year)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">&nbsp;</label>
        <button type="submit" class="btn btn-filter w-100">
          <i class="fa-solid fa-search"></i> Filter
        </button>
      </div>
    </form>
  </div>

  <!-- Table Section -->
  <div class="table-container">
    <h6 style="font-size: 18px; font-weight: 700; color: var(--primary-color); margin-bottom: 24px;">
      <span>Daftar Dokumen (Total: {{ $totalDocuments }})</span>
    </h6>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Nomor SPP</th>
            <th>Tanggal Masuk</th>
            <th>Umur Dokumen</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            @php
              $ageDays = $dokumen->age_days ?? 0;
              $ageFormatted = $dokumen->age_formatted ?? '-';
              
              // Determine card color based on age (fixed: 1, 2, 3+ days)
              $ageColor = 'green';
              if (in_array($roleCode, ['ibuB', 'perpajakan', 'akutansi'])) {
                if ($ageDays > 2) {
                  $ageColor = 'red';
                } elseif ($ageDays > 1) {
                  $ageColor = 'yellow';
                } else {
                  $ageColor = 'green';
                }
              }
              
              // Get received_at for display
              $receivedAt = '-';
              if (isset($dokumen->delay_received_at) && $dokumen->delay_received_at) {
                $receivedAt = \Carbon\Carbon::parse($dokumen->delay_received_at)->format('d M Y H:i');
              } elseif ($roleCode === 'pembayaran' && isset($dokumen->sent_to_pembayaran_at) && $dokumen->sent_to_pembayaran_at) {
                $receivedAt = \Carbon\Carbon::parse($dokumen->sent_to_pembayaran_at)->format('d M Y H:i');
              }
            @endphp
            <tr class="clickable-row" onclick="window.location.href='{{ route('owner.workflow', ['id' => $dokumen->id]) }}'" title="Klik untuk melihat detail workflow dokumen">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td>{{ $dokumen->nomor_agenda }}</td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ $receivedAt }}</td>
              <td>
                <span class="age-badge age-{{ $ageColor }}">
                  {{ $ageFormatted }}
                </span>
              </td>
              <td>
                <span class="badge badge-processing">
                  Belum Diproses
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5">
                <i class="fa-solid fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">Tidak ada dokumen</p>
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


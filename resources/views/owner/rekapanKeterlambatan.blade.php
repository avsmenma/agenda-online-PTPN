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

  /* Summary Cards per Team */
  .team-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .team-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid var(--primary-border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .team-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  }

  .team-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px var(--primary-rgba-dark), 0 4px 16px rgba(64, 145, 108, 0.1);
    border-color: var(--primary-border-hover);
  }

  .team-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .team-card-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--primary-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .team-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  }

  .team-card-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }

  .team-stat-item {
    text-align: center;
  }

  .team-stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 4px;
    line-height: 1;
  }

  .team-stat-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Tab Navigation */
  .tab-navigation {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 8px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid var(--primary-border);
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .tab-button {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    color: var(--primary-color);
    background: transparent;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .tab-button:hover {
    background: var(--primary-rgba);
    color: var(--primary-color);
  }

  .tab-button.active {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border-color: var(--primary-color);
  }

  /* Chart Section */
  .chart-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid var(--primary-border);
  }

  .chart-header {
    margin-bottom: 24px;
  }

  .chart-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 8px;
  }

  .chart-subtitle {
    font-size: 13px;
    color: #6c757d;
  }

  .chart-container {
    position: relative;
    height: 400px;
  }

  /* Filter Section */
  .filter-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid var(--primary-border);
  }

  .filter-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 20px;
  }

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

  .table-responsive::-webkit-scrollbar {
    height: 12px;
    -webkit-appearance: none;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    border-radius: 6px;
    border: 2px solid #ffffff;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
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

  .table tbody tr:hover {
    background: linear-gradient(90deg, var(--primary-rgba) 0%, transparent 100%);
    border-left: 3px solid var(--secondary-color);
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

  .team-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border: none;
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
  <h2><i class="fa-solid fa-exclamation-triangle"></i> Rekapan Keterlambatan Dokumen</h2>

  <!-- Summary Cards per Team -->
  <div class="team-cards">
    @foreach($teamStats as $teamCode => $stats)
      <div class="team-card">
        <div class="team-card-header">
          <div class="team-card-title">{{ $stats['name'] }}</div>
          <div class="team-card-icon">
            <i class="fa-solid fa-users"></i>
          </div>
        </div>
        <div class="team-card-stats">
          <div class="team-stat-item">
            <div class="team-stat-value">{{ $stats['total'] }}</div>
            <div class="team-stat-label">Total Terlambat</div>
          </div>
          <div class="team-stat-item">
            <div class="team-stat-value">{{ $stats['avgDelay'] }}</div>
            <div class="team-stat-label">Rata-rata Hari</div>
          </div>
          <div class="team-stat-item">
            <div class="team-stat-value">{{ $stats['percentage'] }}%</div>
            <div class="team-stat-label">% Terlambat</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <!-- Tab Navigation -->
  <div class="tab-navigation">
    <a href="{{ url('/owner/rekapan-keterlambatan') }}" class="tab-button {{ !$selectedTeam ? 'active' : '' }}">
      <i class="fa-solid fa-list"></i> Semua
    </a>
    @foreach($teams as $teamCode => $teamInfo)
      <a href="{{ url('/owner/rekapan-keterlambatan?team=' . $teamCode) }}" class="tab-button {{ $selectedTeam == $teamCode ? 'active' : '' }}">
        {{ $teamInfo['name'] }}
      </a>
    @endforeach
  </div>

  <!-- Chart and Submenu Section -->
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
    <!-- Chart Section -->
    <div class="chart-section">
      <div class="chart-header">
        <div class="chart-title">Tren Keterlambatan Bulanan per Team</div>
        <div class="chart-subtitle">Statistik keterlambatan dokumen dalam 12 bulan terakhir</div>
      </div>
      <div class="chart-container">
        <canvas id="delayChart"></canvas>
      </div>
    </div>

    <!-- Submenu Section -->
    <div class="submenu-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); padding: 30px; border-radius: 16px; box-shadow: 0 8px 32px var(--primary-rgba), 0 2px 8px rgba(64, 145, 108, 0.05); border: 1px solid var(--primary-border);">
      <div class="submenu-header" style="margin-bottom: 24px;">
        <div style="font-size: 18px; font-weight: 700; color: var(--primary-color); margin-bottom: 8px;">
          <i class="fa-solid fa-list"></i> Submenu Role
        </div>
        <div style="font-size: 13px; color: #6c757d;">
          Klik untuk melihat detail keterlambatan per role
        </div>
      </div>
      <div class="submenu-list" style="display: flex; flex-direction: column; gap: 12px;">
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'ibuA') }}" class="submenu-item" style="padding: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 12px; border: 2px solid var(--primary-border); text-decoration: none; color: var(--primary-color); transition: all 0.3s ease; display: flex; align-items: center; gap: 12px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
            <i class="fa-solid fa-user"></i>
          </div>
          <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 14px;">Ibu Tara</div>
            <div style="font-size: 12px; color: #6c757d;">Lihat detail keterlambatan</div>
          </div>
          <i class="fa-solid fa-chevron-right" style="color: #6c757d;"></i>
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'ibuB') }}" class="submenu-item" style="padding: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 12px; border: 2px solid var(--primary-border); text-decoration: none; color: var(--primary-color); transition: all 0.3s ease; display: flex; align-items: center; gap: 12px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
            <i class="fa-solid fa-users"></i>
          </div>
          <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 14px;">Team Verifikasi</div>
            <div style="font-size: 12px; color: #6c757d;">Lihat detail keterlambatan</div>
          </div>
          <i class="fa-solid fa-chevron-right" style="color: #6c757d;"></i>
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'perpajakan') }}" class="submenu-item" style="padding: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 12px; border: 2px solid var(--primary-border); text-decoration: none; color: var(--primary-color); transition: all 0.3s ease; display: flex; align-items: center; gap: 12px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
            <i class="fa-solid fa-file-invoice-dollar"></i>
          </div>
          <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 14px;">Team Perpajakan</div>
            <div style="font-size: 12px; color: #6c757d;">Lihat detail keterlambatan</div>
          </div>
          <i class="fa-solid fa-chevron-right" style="color: #6c757d;"></i>
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'akutansi') }}" class="submenu-item" style="padding: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 12px; border: 2px solid var(--primary-border); text-decoration: none; color: var(--primary-color); transition: all 0.3s ease; display: flex; align-items: center; gap: 12px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
            <i class="fa-solid fa-calculator"></i>
          </div>
          <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 14px;">Team Akutansi</div>
            <div style="font-size: 12px; color: #6c757d;">Lihat detail keterlambatan</div>
          </div>
          <i class="fa-solid fa-chevron-right" style="color: #6c757d;"></i>
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'pembayaran') }}" class="submenu-item" style="padding: 16px; background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 12px; border: 2px solid var(--primary-border); text-decoration: none; color: var(--primary-color); transition: all 0.3s ease; display: flex; align-items: center; gap: 12px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
            <i class="fa-solid fa-money-bill-wave"></i>
          </div>
          <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 14px;">Pembayaran</div>
            <div style="font-size: 12px; color: #6c757d;">Lihat detail keterlambatan</div>
          </div>
          <i class="fa-solid fa-chevron-right" style="color: #6c757d;"></i>
        </a>
      </div>
    </div>
  </div>

  <style>
    .submenu-item:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px var(--primary-rgba-dark), 0 2px 8px rgba(64, 145, 108, 0.1);
      border-color: var(--secondary-color) !important;
    }

    @media (max-width: 992px) {
      div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
      }
    }
  </style>

  <!-- Filter Section -->
  <div class="filter-section">
    <div class="filter-title"><i class="fa-solid fa-filter"></i> Filter Data</div>
    <form method="GET" action="{{ url('/owner/rekapan-keterlambatan') }}" class="row g-3">
      @if($selectedTeam)
        <input type="hidden" name="team" value="{{ $selectedTeam }}">
      @endif
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
        <label class="form-label">Team</label>
        <select name="team" class="form-select">
          <option value="">Semua Team</option>
          @foreach($teams as $teamCode => $teamInfo)
            <option value="{{ $teamCode }}" {{ $selectedTeam == $teamCode ? 'selected' : '' }}>{{ $teamInfo['name'] }}</option>
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
      <span>Daftar Dokumen Terlambat</span>
    </h6>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Nomor SPP</th>
            <th>Handler</th>
            <th>Deadline</th>
            <th>Keterlambatan</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            @php
              $now = \Carbon\Carbon::now();
              $deadlineDate = $dokumen->delay_deadline_at ?? null;
              if (!$deadlineDate) {
                  // Fallback: try to get from dokumen's deadline_at if exists
                  $deadlineDate = isset($dokumen->deadline_at) ? $dokumen->deadline_at : null;
              }
              $deadline = $deadlineDate ? \Carbon\Carbon::parse($deadlineDate) : $now;
              $delayRoleCode = $dokumen->delay_role_code ?? 'unknown';
              $teamName = isset($teams[$delayRoleCode]) ? $teams[$delayRoleCode]['name'] : $delayRoleCode;
              
              // Calculate keterlambatan
              $diff = $deadline->diff($now);
              $terlambatHari = $diff->days;
              $terlambatJam = $diff->h;
              $terlambatMenit = $diff->i;
              
              // Format keterlambatan
              $keterlambatanParts = [];
              
              if ($terlambatHari > 0) {
                $keterlambatanParts[] = $terlambatHari . ' hari';
              }
              if ($terlambatJam > 0) {
                $keterlambatanParts[] = $terlambatJam . ' jam';
              }
              if ($terlambatMenit > 0 || empty($keterlambatanParts)) {
                $keterlambatanParts[] = $terlambatMenit . ' menit';
              }
              
              $keterlambatanText = implode(' ', $keterlambatanParts);
            @endphp
            <tr class="clickable-row" onclick="window.location.href='{{ route('owner.workflow', ['id' => $dokumen->id]) }}'" title="Klik untuk melihat detail workflow dokumen">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td>{{ $dokumen->nomor_agenda }}</td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>
                <span class="team-badge">
                  {{ $teamName }}
                </span>
              </td>
              <td>{{ $deadline->format('d M Y H:i') }}</td>
              <td>
                <span class="badge-terlambat">
                  <i class="fa-solid fa-exclamation-triangle"></i>
                  {{ $keterlambatanText }}
                </span>
              </td>
              <td>
                <span class="badge badge-processing">
                  Terlambat
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Data from controller
  const monthlyStats = @json($monthlyStats);
  const months = @json($months);
  const teams = @json($teams);
  // Chart colors - Always use Perpajakan Green Theme
  const teamColors = {
    'ibuA': 'rgba(8, 62, 64, 1)',
    'ibuB': 'rgba(136, 151, 23, 1)',
    'perpajakan': 'rgba(26, 77, 62, 1)',
    'akutansi': 'rgba(23, 162, 184, 1)',
  };

  const teamColorsTransparent = {
    'ibuA': 'rgba(8, 62, 64, 0.1)',
    'ibuB': 'rgba(136, 151, 23, 0.1)',
    'perpajakan': 'rgba(26, 77, 62, 0.1)',
    'akutansi': 'rgba(23, 162, 184, 0.1)',
  };

  // Get CSS variable values for chart
  const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
  const secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim();

  // Prepare datasets
  const datasets = [];
  Object.keys(teams).forEach(teamCode => {
    if (monthlyStats[teamCode]) {
      datasets.push({
        label: teams[teamCode].name,
        data: monthlyStats[teamCode],
        borderColor: teamColors[teamCode],
        backgroundColor: teamColorsTransparent[teamCode],
        borderWidth: 3,
        tension: 0.4,
        fill: true,
        pointBackgroundColor: teamColors[teamCode],
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7
      });
    }
  });

  // Create chart
  const ctx = document.getElementById('delayChart').getContext('2d');
  const delayChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months,
      datasets: datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            font: {
              size: 12,
              weight: '600'
            },
            padding: 15,
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: 'rgba(26, 77, 62, 0.9)',
          padding: 12,
          cornerRadius: 8,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          },
          multiKeyBackground: 'rgba(255, 255, 255, 0.1)'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(26, 77, 62, 0.05)',
            drawBorder: false
          },
          ticks: {
            color: primaryColor || '#1a4d3e',
            font: {
              size: 12,
              weight: '500'
            },
            stepSize: 1
          }
        },
        x: {
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            color: primaryColor || '#1a4d3e',
            font: {
              size: 11,
              weight: '500'
            }
          }
        }
      },
      interaction: {
        mode: 'index',
        intersect: false
      }
    }
  });
</script>

@endsection

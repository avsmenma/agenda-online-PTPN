@extends('layouts/app')
@section('content')

@php
  use Illuminate\Support\Str;
@endphp

<style>
  /* Title Styles */
  h2 {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
  }

  /* Filter Section */
  .filter-year-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
  }

  .filter-wrapper {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: #1a4d3e;
    margin: 0;
  }

  .filter-group select {
    padding: 10px 16px;
    border: 2px solid rgba(26, 77, 62, 0.15);
    border-radius: 10px;
    background: white;
    color: #1a4d3e;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 150px;
  }

  .filter-group select:hover {
    border-color: #40916c;
    box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.1);
  }

  .filter-group select:focus {
    outline: none;
    border-color: #40916c;
    box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.1);
  }

  /* Big Summary Card */
  .big-summary-card {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
    color: white;
    position: relative;
    overflow: hidden;
  }

  .big-summary-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 4s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
  }

  .big-summary-content {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
  }

  .summary-item {
    text-align: center;
  }

  .summary-label {
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
    margin-bottom: 12px;
  }

  .summary-value {
    font-size: 48px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 8px;
  }

  .summary-description {
    font-size: 13px;
    opacity: 0.8;
  }

  /* Monthly Grid Section */
  .monthly-grid-section {
    margin-bottom: 30px;
  }

  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a4d3e;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #40916c;
  }

  .month-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
  }

  .month-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(26, 77, 62, 0.08), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 2px solid rgba(26, 77, 62, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }

  .month-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1a4d3e 0%, #40916c 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
  }

  .month-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.15), 0 4px 16px rgba(64, 145, 108, 0.1);
    border-color: #40916c;
  }

  .month-card:hover::before {
    transform: scaleX(1);
  }

  .month-card.active {
    background: linear-gradient(135deg, #40916c 0%, #1a4d3e 100%);
    color: white;
    border-color: #40916c;
    box-shadow: 0 8px 32px rgba(64, 145, 108, 0.3), 0 4px 16px rgba(26, 77, 62, 0.2);
  }

  .month-card.active::before {
    transform: scaleX(1);
    background: white;
  }

  .month-card.active .month-name,
  .month-card.active .month-count,
  .month-card.active .month-total {
    color: white;
  }

  .month-name {
    font-size: 16px;
    font-weight: 700;
    color: #1a4d3e;
    margin-bottom: 12px;
  }

  .month-count {
    font-size: 24px;
    font-weight: 800;
    color: #1a4d3e;
    margin-bottom: 8px;
  }

  .month-count-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
  }

  .month-total {
    font-size: 14px;
    font-weight: 600;
    color: #40916c;
    margin-top: 8px;
  }

  .month-card.active .month-count-label,
  .month-card.active .month-total {
    color: rgba(255, 255, 255, 0.9);
  }

  .show-all-months-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 10px;
  }

  .show-all-months-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(26, 77, 62, 0.3);
  }

  /* Table Styles */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(64, 145, 108, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
    margin-top: 30px;
    overflow-x: auto;
    overflow-y: visible;
    width: 100%;
    max-width: 100%;
    position: relative;
    scrollbar-gutter: stable;
  }

  .table-container h6 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid rgba(26, 77, 62, 0.1);
  }

  .table-container h6 span {
    background: linear-gradient(135deg, #1a4d3e 0%, #40916c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 18px;
  }

  .table {
    margin-bottom: 0;
    width: 100%;
    min-width: 1200px;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
  }

  .table thead {
    background: #1a4d3e !important;
  }

  .table thead th {
    background: #1a4d3e !important;
    color: white !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    padding: 16px 12px !important;
    border: none !important;
  }

  .table tbody tr {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table tbody tr:hover {
    background: linear-gradient(90deg, rgba(64, 145, 108, 0.05) 0%, transparent 100%);
    border-left: 3px solid #40916c;
    transform: scale(1.001);
  }

  .table tbody td {
    padding: 14px 12px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
  }

  /* Table Scroll Container - Horizontal Scrollbar Only (Always Visible) */
  .table-responsive {
    overflow-x: scroll !important;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
    position: relative;
    width: 100%;
    max-width: 100%;
    padding-bottom: 5px;
    margin-bottom: 5px;
  }

  /* Horizontal Scrollbar Styling - Webkit browsers */
  .table-responsive::-webkit-scrollbar {
    height: 16px !important;
    width: 0;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .table-responsive::-webkit-scrollbar-track:horizontal {
    background: #f1f5f9 !important;
    border-radius: 8px;
    margin: 0 10px;
    border: 1px solid #e2e8f0;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .table-responsive::-webkit-scrollbar-thumb:horizontal {
    background: #cbd5e1 !important;
    border-radius: 8px;
    border: 2px solid #f1f5f9;
    min-height: 16px;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .table-responsive::-webkit-scrollbar-thumb:horizontal:hover {
    background: #94a3b8 !important;
  }

  .table-responsive::-webkit-scrollbar-thumb:horizontal:active {
    background: #64748b !important;
  }

  .table-responsive:not(:hover)::-webkit-scrollbar {
    height: 16px !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .table-responsive:not(:hover)::-webkit-scrollbar-track:horizontal {
    background: #f1f5f9 !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .table-responsive:not(:hover)::-webkit-scrollbar-thumb:horizontal {
    background: #cbd5e1 !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
  }

  .empty-state i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
  }

  .empty-state h5 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #6c757d;
  }

  .empty-state p {
    font-size: 14px;
    color: #999;
  }

  .select-text {
    cursor: text;
    user-select: text;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .big-summary-content {
      grid-template-columns: 1fr;
      gap: 20px;
    }

    .summary-value {
      font-size: 36px;
    }

    .month-grid {
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 12px;
    }

    .filter-year-section {
      flex-direction: column;
      align-items: stretch;
    }

    .filter-wrapper {
      flex-direction: column;
      align-items: stretch;
    }

    .filter-group {
      flex-direction: column;
      align-items: stretch;
    }

    .filter-group select {
      width: 100%;
    }
  }
</style>

<h2>Analitik Dokumen</h2>

<!-- Filter Section -->
<div class="filter-year-section">
  <div class="filter-wrapper">
    <div class="filter-group">
      <label for="yearSelect">Pilih Tahun:</label>
      <select id="yearSelect" onchange="changeFilter()">
        @foreach($availableYears as $year)
          <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">
      <label for="bagianSelect">Pilih Bagian:</label>
      <select id="bagianSelect" onchange="changeFilter()">
        <option value="">Semua Bagian ({{ $yearlySummary['total_dokumen'] ?? 0 }} dokumen)</option>
        @foreach($bagianList as $code => $name)
          <option value="{{ $code }}" {{ $selectedBagian == $code ? 'selected' : '' }}>
            {{ $name }} ({{ $bagianCounts[$code] ?? 0 }} dokumen)
          </option>
        @endforeach
      </select>
    </div>
  </div>
  <div style="font-size: 14px; color: #6c757d; font-weight: 500;">
    <i class="fa-solid fa-calendar-alt me-2"></i>
    Data untuk tahun <strong>{{ $selectedYear }}</strong>
    @if($selectedBagian)
      - Bagian <strong>{{ $bagianList[$selectedBagian] ?? '' }}</strong>
    @endif
  </div>
</div>

<!-- Big Summary Card -->
<div class="big-summary-card">
  <div class="big-summary-content">
    <div class="summary-item">
      <div class="summary-label">Total Nominal</div>
      <div class="summary-value">Rp {{ number_format($yearlySummary['total_nominal'] ?? 0, 0, ',', '.') }}</div>
      <div class="summary-description">Total nilai dokumen tahun {{ $selectedYear }}</div>
    </div>
    <div class="summary-item">
      <div class="summary-label">Total Jumlah Dokumen</div>
      <div class="summary-value">{{ number_format($yearlySummary['total_dokumen'] ?? 0, 0, ',', '.') }}</div>
      <div class="summary-description">Total dokumen tahun {{ $selectedYear }}</div>
    </div>
  </div>
</div>

<!-- Monthly Grid Section -->
<div class="monthly-grid-section">
  <div class="section-title">
    <i class="fa-solid fa-calendar-grid me-2"></i>
    Ringkasan Bulanan
  </div>
  <div class="month-grid" id="monthGrid">
    @for($month = 1; $month <= 12; $month++)
      @php
        $monthData = $monthlyStats[$month] ?? ['name' => '', 'count' => 0, 'total_nominal' => 0];
      @endphp
      <div class="month-card {{ $selectedMonth == $month ? 'active' : '' }}" 
           onclick="filterByMonth({{ $month }})"
           data-month="{{ $month }}">
        <div class="month-name">{{ $monthData['name'] }}</div>
        <div class="month-count-label">Jumlah Dokumen</div>
        <div class="month-count">{{ number_format($monthData['count'], 0, ',', '.') }}</div>
        <div class="month-total">Rp {{ number_format($monthData['total_nominal'], 0, ',', '.') }}</div>
      </div>
    @endfor
  </div>
  @if($selectedMonth)
    <button class="show-all-months-btn" onclick="showAllMonths()">
      <i class="fa-solid fa-list"></i>
      Tampilkan Semua Bulan
    </button>
  @endif
</div>

<!-- Table Section -->
<div class="table-container">
  <h6>
    <span>
      <i class="fa-solid fa-table me-2"></i>
      @if($selectedMonth)
        Daftar Dokumen - {{ $monthlyStats[$selectedMonth]['name'] ?? '' }} {{ $selectedYear }}
      @else
        Daftar Dokumen - Tahun {{ $selectedYear }}
      @endif
      @if($selectedBagian)
        - Bagian {{ $bagianList[$selectedBagian] ?? '' }}
      @endif
    </span>
    <span style="font-size: 13px; color: #6c757d; font-weight: 500;">
      Total: {{ $dokumens->total() }} dokumen
    </span>
  </h6>
  <div class="table-responsive scrollbar-visible">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Nomor Agenda</th>
          <th>Bulan</th>
          <th>Tahun</th>
          <th>Tanggal Masuk</th>
          <th>Nomor SPP</th>
          <th>Uraian SPP</th>
          <th>Nilai Rupiah</th>
          <th>Bagian</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="dokumenTableBody">
        @forelse($dokumens as $index => $dokumen)
          <tr>
            <td style="text-align: center;">{{ ($dokumens->currentPage() - 1) * $dokumens->perPage() + $index + 1 }}</td>
            <td class="select-text"><strong>{{ $dokumen->nomor_agenda ?? '-' }}</strong></td>
            <td>
              <span style="font-weight: 600; color: #1a4d3e;">
                {{ $dokumen->bulan ?? '-' }}
              </span>
            </td>
            <td>
              <span style="font-weight: 600; color: #1a4d3e;">
                {{ $dokumen->tahun ?? '-' }}
              </span>
            </td>
            <td class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y') : '-' }}</td>
            <td class="select-text">{{ $dokumen->nomor_spp ?? '-' }}</td>
            <td class="select-text">{{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}</td>
            <td class="select-text"><strong>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong></td>
            <td>
              @if($dokumen->bagian)
                <span class="badge" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                  {{ $dokumen->bagian }}
                </span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              @php
                $statusLabel = $dokumen->status_perpajakan ?? ($dokumen->status ?? '');
                if ($statusLabel == 'sedang_diproses') {
                  $statusDisplay = 'Sedang Diproses';
                } elseif ($statusLabel == 'selesai') {
                  $statusDisplay = 'Selesai';
                } elseif ($dokumen->status == 'sent_to_akutansi') {
                  $statusDisplay = 'Terkirim ke Akutansi';
                } elseif ($dokumen->status == 'sent_to_perpajakan' && is_null($dokumen->deadline_at)) {
                  $statusDisplay = 'Terkunci';
                } else {
                  $statusDisplay = ucfirst(str_replace('_', ' ', $statusLabel));
                }
              @endphp
              @if($statusDisplay == 'Terkunci')
                <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                  🔒 Terkunci
                </span>
              @elseif($statusDisplay == 'Sedang Diproses')
                <span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                  ⏳ Sedang Diproses
                </span>
              @elseif($statusDisplay == 'Selesai')
                <span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                  ✓ Selesai
                </span>
              @elseif($statusDisplay == 'Terkirim ke Akutansi')
                <span class="badge" style="background: linear-gradient(135deg, #1a4d3e 0%, #40916c 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                  📤 Terkirim ke Akutansi
                </span>
              @else
                <span class="badge" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; padding: 4px 12px; border-radius: 12px;">
                  {{ $statusDisplay }}
                </span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="empty-state">
              <i class="fa-solid fa-inbox"></i>
              <h5>Belum ada dokumen</h5>
              <p>Tidak ada dokumen untuk periode yang dipilih</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <!-- Pagination -->
  @include('partials.pagination-enhanced', ['paginator' => $dokumens])
</div>

<script>
function changeFilter() {
  const year = document.getElementById('yearSelect').value;
  const bagian = document.getElementById('bagianSelect').value;
  
  const url = new URL(window.location.href);
  url.searchParams.set('year', year);
  if (bagian) {
    url.searchParams.set('bagian', bagian);
  } else {
    url.searchParams.delete('bagian');
  }
  url.searchParams.delete('month');
  url.searchParams.delete('page');
  
  window.location.href = url.toString();
}

function filterByMonth(month) {
  const year = document.getElementById('yearSelect').value;
  const bagian = document.getElementById('bagianSelect').value;
  
  const url = new URL(window.location.href);
  url.searchParams.set('year', year);
  url.searchParams.set('month', month);
  if (bagian) {
    url.searchParams.set('bagian', bagian);
  } else {
    url.searchParams.delete('bagian');
  }
  url.searchParams.delete('page');
  
  window.location.href = url.toString();
}

function showAllMonths() {
  const year = document.getElementById('yearSelect').value;
  const bagian = document.getElementById('bagianSelect').value;
  
  const url = new URL(window.location.href);
  url.searchParams.set('year', year);
  url.searchParams.delete('month');
  if (bagian) {
    url.searchParams.set('bagian', bagian);
  } else {
    url.searchParams.delete('bagian');
  }
  url.searchParams.delete('page');
  
  window.location.href = url.toString();
}
</script>

@endsection


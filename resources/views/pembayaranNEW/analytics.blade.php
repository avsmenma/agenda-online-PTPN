@extends('layouts/app')
@section('content')

<style>
  /* Title Styles */
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
  }

  /* Filter Year Section */
  .btn-back {
    padding: 12px 24px;
    border: 2px solid rgba(8, 62, 64, 0.2);
    background-color: white;
    color: #083E40;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
  }

  .btn-back:hover {
    background-color: #083E40;
    color: white;
    border-color: #083E40;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .filter-year-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
  }

  .year-select-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .year-select-wrapper label {
    font-size: 14px;
    font-weight: 600;
    color: #083E40;
    margin: 0;
  }

  .year-select-wrapper select {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    border-radius: 10px;
    background: white;
    color: #083E40;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .year-select-wrapper select:hover {
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .year-select-wrapper select:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
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

  /* Monthly Grid */
  .monthly-grid-section {
    margin-bottom: 30px;
  }

  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #083E40;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #889717;
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
    box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 2px solid rgba(8, 62, 64, 0.1);
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
    background: linear-gradient(90deg, #083E40 0%, #889717 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
  }

  .month-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.15), 0 4px 16px rgba(136, 151, 23, 0.1);
    border-color: #889717;
  }

  .month-card:hover::before {
    transform: scaleX(1);
  }

  .month-card.active {
    background: linear-gradient(135deg, #889717 0%, #083E40 100%);
    color: white;
    border-color: #889717;
    box-shadow: 0 8px 32px rgba(136, 151, 23, 0.3), 0 4px 16px rgba(8, 62, 64, 0.2);
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
    color: #083E40;
    margin-bottom: 12px;
  }

  .month-count {
    font-size: 24px;
    font-weight: 800;
    color: #083E40;
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
    color: #889717;
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
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
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
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.3);
  }

  /* Table Styles */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    margin-top: 30px;
  }

  .table-container h6 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid rgba(8, 62, 64, 0.1);
  }

  .table-container h6 span {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 18px;
  }

  .table {
    margin-bottom: 0;
    width: 100%;
    border-collapse: collapse;
  }

  .table thead {
    background: #083E40 !important;
  }

  .table thead th {
    background: #083E40 !important;
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

  .table tbody tr.clickable-row {
    cursor: pointer;
  }

  .table tbody tr.clickable-row:hover {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    border-left: 3px solid #889717;
    transform: scale(1.001);
  }

  .table tbody td {
    padding: 14px 12px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
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
  }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
  <h2 style="margin: 0;">{{ $title }}</h2>
  <a href="{{ route('reports.pembayaran.index') }}" class="btn-back">
    <i class="fa-solid fa-arrow-left"></i>
    Kembali ke Rekapan
  </a>
</div>

<!-- Filter Year Section -->
<div class="filter-year-section">
  <div class="year-select-wrapper">
    <label for="yearSelect">Pilih Tahun:</label>
    <select id="yearSelect" onchange="changeYear(this.value)">
      @foreach($availableYears as $year)
        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
      @endforeach
    </select>
  </div>
  <div style="font-size: 14px; color: #6c757d; font-weight: 500;">
    <i class="fa-solid fa-calendar-alt me-2"></i>
    Data untuk tahun <strong>{{ $selectedYear }}</strong>
  </div>
</div>

<!-- Big Summary Card -->
<div class="big-summary-card">
  <div class="big-summary-content">
    <div class="summary-item">
      <div class="summary-label">Total Nominal</div>
      <div class="summary-value">Rp {{ number_format($yearlySummary['total_nominal'] ?? 0, 0, ',', '.') }}</div>
      <div class="summary-description">Total nilai pembayaran tahun {{ $selectedYear }}</div>
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
    </span>
    <span style="font-size: 13px; color: #6c757d; font-weight: 500;">
      Total: {{ $dokumens->count() }} dokumen
    </span>
  </h6>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Nomor Agenda</th>
          <th>Bulan</th>
          <th>Tahun</th>
          <th>Tanggal Dibayar</th>
          <th>Nomor SPP</th>
          <th>Uraian SPP</th>
          <th>Nilai Rupiah</th>
          <th>Dibayar Kepada</th>
        </tr>
      </thead>
      <tbody id="dokumenTableBody">
        @forelse($dokumens as $index => $dokumen)
          <tr class="clickable-row" 
              onclick="handleItemClick(event, '{{ route('documents.pembayaran.detail', $dokumen->id) }}')"
              title="Klik untuk melihat detail dokumen">
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td><strong>{{ $dokumen->nomor_agenda ?? '-' }}</strong></td>
            <td>
              <span style="font-weight: 600; color: #083E40;">
                {{ $dokumen->bulan ?? '-' }}
              </span>
            </td>
            <td>
              <span style="font-weight: 600; color: #083E40;">
                {{ $dokumen->tahun ?? '-' }}
              </span>
            </td>
            <td class="select-text">{{ $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : '-' }}</td>
            <td class="select-text">{{ $dokumen->nomor_spp ?? '-' }}</td>
            <td class="select-text">{{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}</td>
            <td><strong class="select-text">Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong></td>
            <td>
              @if($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
              @else
                {{ $dokumen->dibayar_kepada ?? '-' }}
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="empty-state">
              <i class="fa-solid fa-inbox"></i>
              <h5>Belum ada dokumen</h5>
              <p>Tidak ada dokumen untuk periode yang dipilih</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
// Store current year and month from server
const currentYear = {{ $selectedYear }};
const currentMonth = {{ $selectedMonth ?? 'null' }};

// Change year - reload page with new year
function changeYear(year) {
    const url = new URL(window.location.href);
    url.searchParams.set('year', year);
    url.searchParams.delete('month'); // Reset month when changing year
    window.location.href = url.toString();
}

// Filter by month - reload with smooth scroll to table
function filterByMonth(month) {
    const url = new URL(window.location.href);
    url.searchParams.set('month', month);
    
    // Smooth scroll to top then reload
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Small delay for smooth UX, then reload
    setTimeout(() => {
        window.location.href = url.toString();
    }, 300);
}

// Show all months - reset filter
function showAllMonths() {
    const url = new URL(window.location.href);
    url.searchParams.delete('month');
    
    // Smooth scroll to top then reload
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Small delay for smooth UX, then reload
    setTimeout(() => {
        window.location.href = url.toString();
    }, 300);
}

// Initialize: Set active month card if month is selected
document.addEventListener('DOMContentLoaded', function() {
    if (currentMonth) {
        const monthCard = document.querySelector(`[data-month="${currentMonth}"]`);
        if (monthCard) {
            monthCard.classList.add('active');
        }
    }
});
</script>

@endsection





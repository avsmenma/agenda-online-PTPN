@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 8px 32px rgba(0, 123, 255, 0.1), 0 2px 8px rgba(0, 86, 179, 0.05);
    border: 1px solid rgba(0, 123, 255, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .stat-card.clickable {
    cursor: pointer;
  }

  .stat-card.clickable:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 123, 255, 0.2), 0 4px 16px rgba(0, 86, 179, 0.1);
    border-color: rgba(0, 123, 255, 0.15);
  }

  .stat-card.clickable.active {
    border: 2px solid #007bff;
    box-shadow: 0 12px 40px rgba(0, 123, 255, 0.3), 0 4px 16px rgba(0, 86, 179, 0.15);
    background: linear-gradient(135deg, #f0f7ff 0%, #e6f2ff 100%);
  }

  .stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    margin-left: 20px;
  }

  .stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
    line-height: 1.2;
    margin-top: 8px;
  }

  .stat-title {
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.4;
  }

  .back-button {
    margin-bottom: 20px;
  }

  .back-button a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .back-button a:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
  }

  .stat-icon.total {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  }

  .stat-icon.selesai {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  }

  .stat-icon.proses {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
  }

  .stat-icon.terlambat {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  }

  .filter-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(0, 123, 255, 0.1), 0 2px 8px rgba(0, 86, 179, 0.05);
    border: 1px solid rgba(0, 123, 255, 0.08);
  }

  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 123, 255, 0.1), 0 2px 8px rgba(0, 86, 179, 0.05);
    border: 1px solid rgba(0, 123, 255, 0.08);
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
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 6px;
    border: 2px solid #ffffff;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
  }

  .table thead {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
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
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.05) 0%, transparent 100%);
    border-left: 3px solid #007bff;
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

  .badge-completed {
    background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
    color: white !important;
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
    color: #007bff;
    background: white;
    border: 2px solid #007bff;
  }

  .pagination .page-link:hover {
    background: #007bff;
    color: white;
  }

  .pagination .active .page-link {
    background: #007bff;
    color: white;
    border-color: #007bff;
  }

  .main-row {
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .main-row:hover {
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.08) 0%, transparent 100%) !important;
  }

  .main-row.selected {
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.15) 0%, transparent 100%) !important;
    border-left: 3px solid #007bff;
  }

  .detail-row {
    background: #f8f9fa;
  }

  .detail-row td {
    padding: 0 !important;
    border: none !important;
  }

  .detail-content {
    padding: 24px;
    background: white;
    border-radius: 8px;
    margin: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
    width: calc(100% - 32px);
    max-width: 100%;
    box-sizing: border-box;
  }

  @media (max-width: 768px) {
    .detail-content {
      padding: 16px;
      margin: 8px;
      width: calc(100% - 16px);
    }

    .detail-content .detail-grid {
      grid-template-columns: 1fr;
      gap: 12px;
    }
  }

  .detail-content * {
    box-sizing: border-box;
  }

  .detail-content .detail-grid {
    width: 100%;
    max-width: 100%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
  }

  .detail-content .detail-item {
    min-width: 0;
    word-wrap: break-word;
    overflow-wrap: break-word;
    display: flex;
    flex-direction: column;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #007bff;
  }

  .detail-content .detail-label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
  }

  .detail-content .detail-value {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
    line-height: 1.5;
  }

  .detail-content .detail-section-separator {
    margin: 32px 0 20px 0;
    padding: 16px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 8px;
    text-align: center;
  }

  .detail-content .separator-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: white;
    font-weight: 600;
    font-size: 16px;
    flex-wrap: wrap;
  }

  .detail-content .separator-content i {
    font-size: 20px;
  }

  .detail-content .tax-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .detail-content .tax-section {
    margin-top: 16px;
  }

  .detail-content .tax-field {
    border-left-color: #28a745;
  }

  .detail-content .empty-field {
    color: #6c757d;
    font-style: italic;
  }

  .detail-content .tax-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
  }

  .detail-content .tax-link:hover {
    text-decoration: underline;
  }

  .loading-spinner {
    color: #007bff;
  }

  .loading-spinner i {
    font-size: 24px;
    margin-right: 12px;
  }

  /* Detail Grid Styles */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #007bff;
  }

  .detail-label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
  }

  .detail-value {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
    word-wrap: break-word;
  }

  .detail-section-separator {
    margin: 32px 0 20px 0;
    padding: 16px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 8px;
    text-align: center;
  }

  .separator-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: white;
    font-weight: 600;
    font-size: 16px;
  }

  .separator-content i {
    font-size: 20px;
  }

  .tax-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .tax-section {
    margin-top: 16px;
  }

  .tax-field {
    border-left-color: #28a745;
  }

  .empty-field {
    color: #6c757d;
    font-style: italic;
  }

  .tax-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
  }

  .tax-link:hover {
    text-decoration: underline;
  }
</style>

<div class="container-fluid">
  <div class="back-button">
    <a href="{{ route('owner.rekapan') }}">
      <i class="fa-solid fa-arrow-left"></i>
      Kembali ke Rekapan Dokumen
    </a>
  </div>

  <h2>
    <i class="fa-solid fa-chart-line"></i> 
    Detail {{ $typeNames[$type] }}
  </h2>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
      <div class="stat-card clickable {{ $statFilter == '' || $statFilter == 'total' ? 'active' : '' }}" 
           data-filter="total" 
           onclick="filterByStat('total')"
           title="Klik untuk menampilkan semua dokumen">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Total Dokumen</div>
            <div class="stat-value">{{ $totalDokumen }}</div>
          </div>
          <div class="stat-icon total">
            <i class="fa-solid fa-file-lines"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
      <div class="stat-card clickable {{ $statFilter == 'selesai' ? 'active' : '' }}" 
           data-filter="selesai" 
           onclick="filterByStat('selesai')"
           title="Klik untuk menampilkan dokumen selesai">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Selesai</div>
            <div class="stat-value">{{ $totalSelesai }}</div>
          </div>
          <div class="stat-icon selesai">
            <i class="fa-solid fa-check-circle"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
      <div class="stat-card clickable {{ $statFilter == 'proses' ? 'active' : '' }}" 
           data-filter="proses" 
           onclick="filterByStat('proses')"
           title="Klik untuk menampilkan dokumen proses">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Proses</div>
            <div class="stat-value">{{ $totalProses }}</div>
          </div>
          <div class="stat-icon proses">
            <i class="fa-solid fa-spinner"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
      <div class="stat-card clickable {{ $statFilter == 'terlambat' ? 'active' : '' }}" 
           data-filter="terlambat" 
           onclick="filterByStat('terlambat')"
           title="Klik untuk menampilkan dokumen terlambat">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Terlambat</div>
            <div class="stat-value">{{ $totalTerlambat }}</div>
          </div>
          <div class="stat-icon terlambat">
            <i class="fa-solid fa-exclamation-triangle"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Info Section -->
  <div class="alert alert-info" style="border-radius: 12px; border-left: 4px solid #007bff; margin-bottom: 30px;">
    <div class="d-flex align-items-start">
      <i class="fa-solid fa-info-circle me-3" style="font-size: 20px; margin-top: 2px;"></i>
      <div>
        <strong>Informasi Statistik:</strong>
        <ul class="mb-0 mt-2" style="padding-left: 20px;">
          <li><strong>Total Dokumen:</strong> Semua dokumen yang sesuai dengan kategori {{ $typeNames[$type] }}</li>
          <li><strong>Dokumen Selesai:</strong> Dokumen yang sudah selesai diproses (status: selesai, approved_data_sudah_terkirim, atau sudah_dibayar)</li>
          <li><strong>Dokumen Proses:</strong> Dokumen yang sedang dalam proses (status: sedang diproses, sent_to_*, proses_*)</li>
          <li><strong>Dokumen Terlambat:</strong> Dokumen yang memiliki deadline dan sudah melewati deadline, namun belum selesai</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <form method="GET" action="{{ route('owner.rekapan.detailStats', ['type' => $type]) }}" class="row g-3">
      @if($statFilter)
        <input type="hidden" name="stat_filter" value="{{ $statFilter }}">
      @endif
      <div class="col-md-3">
        <label class="form-label">Cari Dokumen</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nomor agenda, SPP, dll">
      </div>
      <div class="col-md-2">
        <label class="form-label">Tahun</label>
        <select name="year" class="form-select">
          <option value="">Semua Tahun</option>
          @foreach($availableYears as $year)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Bagian</label>
        <select name="bagian" class="form-select">
          <option value="">Semua Bagian</option>
          @foreach($bagianList as $code => $name)
            <option value="{{ $code }}" {{ $selectedBagian == $code ? 'selected' : '' }}>{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="completion_status" class="form-select">
          <option value="">Semua Status</option>
          <option value="selesai" {{ $completionFilter == 'selesai' ? 'selected' : '' }}>Sudah Selesai</option>
          <option value="belum_selesai" {{ $completionFilter == 'belum_selesai' ? 'selected' : '' }}>Belum Selesai</option>
        </select>
      </div>
      <div class="col-md-1">
        <label class="form-label">&nbsp;</label>
        <button type="submit" class="btn btn-primary w-100">
          <i class="fa-solid fa-search"></i> Filter
        </button>
      </div>
      <div class="col-md-2">
        <label class="form-label">&nbsp;</label>
        <a href="{{ route('owner.rekapan.detailStats', ['type' => $type]) }}" class="btn btn-secondary w-100">
          <i class="fa-solid fa-refresh"></i> Reset
        </a>
      </div>
    </form>
  </div>

  <!-- Table Section -->
  <div class="table-container">
    <h6><span>Daftar Dokumen</span></h6>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>Nomor Agenda</th>
            <th>Nomor SPP</th>
            <th>Uraian SPP</th>
            <th>Bagian</th>
            <th>Nilai Rupiah</th>
            <th>Dokumen Selesai</th>
            <th>Tanggal Masuk</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dokumens as $index => $dokumen)
            <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})" style="cursor: pointer;" title="Klik untuk melihat detail lengkap dokumen" data-id="{{ $dokumen->id }}">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td>{{ $dokumen->nomor_agenda }}</td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ Str::limit($dokumen->uraian_spp, 50) }}</td>
              <td>
                <span class="badge" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 6px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                  {{ $dokumen->bagian ?? '-' }}
                </span>
              </td>
              <td>Rp. {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</td>
              <td>
                @php
                  $isCompleted = in_array($dokumen->status, ['selesai', 'approved_data_sudah_terkirim']) || $dokumen->current_handler == 'pembayaran';
                @endphp
                @if($isCompleted)
                  <span class="badge badge-completed">
                    <i class="fa-solid fa-check-circle"></i> Selesai
                  </span>
                @else
                  <span style="color: #6c757d;">-</span>
                @endif
              </td>
              <td>{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d M Y') : '-' }}</td>
            </tr>
            <tr id="detail-{{ $dokumen->id }}" class="detail-row" style="display: none;">
              <td colspan="8">
                <div id="detail-content-{{ $dokumen->id }}" class="detail-content">
                  <div class="loading-spinner text-center p-4">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Memuat detail dokumen...</span>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada dokumen ditemukan</p>
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

<script>
function filterByStat(filterType) {
  // Get current URL parameters
  const urlParams = new URLSearchParams(window.location.search);
  
  // Update or remove stat_filter parameter
  if (filterType === 'total' || filterType === '') {
    urlParams.delete('stat_filter');
  } else {
    urlParams.set('stat_filter', filterType);
  }
  
  // Reset to page 1 when filtering
  urlParams.delete('page');
  
  // Build new URL
  const baseUrl = window.location.pathname;
  const newUrl = baseUrl + (urlParams.toString() ? '?' + urlParams.toString() : '');
  
  // Navigate to new URL
  window.location.href = newUrl;
}

function toggleDetail(docId) {
  const detailRow = document.getElementById('detail-' + docId);
  const mainRow = document.querySelector(`tr[data-id="${docId}"]`);

  // Toggle visibility
  if (detailRow.style.display === 'none' || !detailRow.style.display) {
    // Show detail
    detailRow.style.display = 'table-row';
    mainRow.classList.add('selected');

    // Load detail content via AJAX
    loadDocumentDetail(docId);
  } else {
    // Hide detail
    detailRow.style.display = 'none';
    mainRow.classList.remove('selected');
  }
}

function loadDocumentDetail(docId) {
  const detailContent = document.getElementById(`detail-content-${docId}`);
  
  // Show loading
  detailContent.innerHTML = `
    <div class="loading-spinner text-center p-4">
      <i class="fa-solid fa-spinner fa-spin"></i>
      <span>Memuat detail dokumen...</span>
    </div>
  `;

  // Fetch detail data
  fetch(`/owner/rekapan/${docId}/detail`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(html => {
      detailContent.innerHTML = html;
    })
    .catch(error => {
      console.error('Error loading document detail:', error);
      detailContent.innerHTML = `
        <div class="text-center p-4 text-danger">
          <i class="fa-solid fa-exclamation-triangle me-2"></i>
          Gagal memuat detail dokumen. Silakan coba lagi.
        </div>
      `;
    });
}

// Prevent row click when clicking on action buttons
document.addEventListener('click', function(e) {
  if (e.target.closest('.btn-action') || e.target.closest('button')) {
    e.stopPropagation();
  }
});
</script>

@endsection


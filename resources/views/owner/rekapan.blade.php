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

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 123, 255, 0.2), 0 4px 16px rgba(0, 86, 179, 0.1);
    border-color: rgba(0, 123, 255, 0.15);
  }

  .stat-card[style*="cursor: pointer"]:hover {
    cursor: pointer;
    border-color: rgba(0, 123, 255, 0.3);
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

  .table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #007bff #f1f1f1;
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

  /* Detail Row Styles */
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

  .detail-content {
    padding: 24px;
    background: white;
    border-radius: 8px;
    margin: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

  .badge-selesai {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
  }

  .badge-proses {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
  }
</style>

<div class="container-fluid">
  <h2>
    <i class="fa-solid fa-chart-pie"></i> 
    Rekapan Dokumen
    @if(isset($handler) && isset($handlerNames))
      - {{ $handlerNames[$handler] }}
    @endif
  </h2>

  <!-- Statistics Cards -->
  <!-- Row 1: 3 Cards -->
  <div class="row mb-4">
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('owner.rekapan.detailStats', ['type' => 'total']) }}'">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Total Dokumen</div>
            <div class="stat-value">{{ $statistics['total_documents'] }}</div>
          </div>
          <div class="stat-icon">
            <i class="fa-solid fa-file-lines"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('owner.rekapan.detailStats', ['type' => 'selesai']) }}'">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Selesai</div>
            <div class="stat-value">{{ $statistics['completed_documents'] }}</div>
          </div>
          <div class="stat-icon">
            <i class="fa-solid fa-check-circle"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('owner.rekapan.detailStats', ['type' => 'ibuA']) }}'">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Ibu Tarapul</div>
            <div class="stat-value">{{ $statistics['ibu_tarapul'] }}</div>
          </div>
          <div class="stat-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <i class="fa-solid fa-user"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 2: 3 Cards -->
  <div class="row mb-4">
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('owner.rekapan.detailStats', ['type' => 'ibuB']) }}'">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Ibu Yuni</div>
            <div class="stat-value">{{ $statistics['ibu_yuni'] }}</div>
          </div>
          <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
            <i class="fa-solid fa-user-check"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('owner.rekapan.detailStats', ['type' => 'perpajakan']) }}'">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Team Perpajakan</div>
            <div class="stat-value">{{ $statistics['perpajakan'] }}</div>
          </div>
          <div class="stat-icon" style="background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);">
            <i class="fa-solid fa-file-invoice-dollar"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('owner.rekapan.detailStats', ['type' => 'akutansi']) }}'">
        <div class="d-flex align-items-center justify-content-between">
          <div style="flex: 1;">
            <div class="stat-title">Dokumen Team Akutansi</div>
            <div class="stat-value">{{ $statistics['akutansi'] }}</div>
          </div>
          <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
            <i class="fa-solid fa-calculator"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <form method="GET" action="{{ url('/owner/rekapan') }}" class="row g-3">
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
            <option value="{{ $code }}" {{ $selectedBagian == $code ? 'selected' : '' }}>
              {{ $name }} ({{ $bagianCounts[$code] ?? 0 }} dokumen)
            </option>
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
              <td colspan="7" class="text-center py-5">
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


@extends('layouts/app')
@section('content')

<style>
  .form-title {
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0;
  }

  /* Statistics Cards - Modern Design with Left Border & Icon */
  .stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #083E40;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 120px;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.15);
    border-left-width: 5px;
  }

  .stat-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .stat-label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .stat-label i {
    font-size: 16px;
    color: #083E40;
  }

  .stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #083E40;
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.5px;
  }

  .stat-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.2);
  }

  .stat-icon {
    font-size: 28px;
    color: white;
  }

  /* Search Box - Enhanced */
  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 20px 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .search-box .input-group-text {
    background: white;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-right: none;
    border-radius: 10px 0 0 10px;
    padding: 10px 14px;
    color: #083E40;
  }

  .search-box .form-control {
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-left: none;
    border-radius: 0 10px 10px 0;
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .search-box .btn-primary {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .search-box .btn-primary:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  /* Table Styling - Green Theme */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow-x: auto;
    overflow-y: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .table-dokumen table {
    min-width: 1400px;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .table-dokumen thead {
    background: #083E40;
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .table-dokumen thead::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.2);
  }

  .table-dokumen thead th {
    padding: 18px 16px;
    font-weight: 700;
    font-size: 14px;
    border: none;
    text-align: center;
    letter-spacing: 0.8px;
    color: white;
    text-transform: uppercase;
    white-space: nowrap;
    position: relative;
    border-right: 1px solid rgba(255, 255, 255, 0.2);
    background: #083E40;
  }

  .table-dokumen thead th:last-child {
    border-right: none;
  }

  /* Column width settings */
  .table-dokumen thead th:nth-child(1) { width: 60px; min-width: 60px; }
  .table-dokumen thead th:nth-child(2) { width: 150px; min-width: 150px; }
  .table-dokumen thead th:nth-child(3) { width: 180px; min-width: 180px; }
  .table-dokumen thead th:nth-child(4) { width: 250px; min-width: 200px; }
  .table-dokumen thead th:nth-child(5) { width: 140px; min-width: 140px; }
  .table-dokumen thead th:nth-child(6) { width: 160px; min-width: 160px; }
  .table-dokumen thead th:nth-child(7) { width: 180px; min-width: 180px; }
  .table-dokumen thead th:nth-child(8) { width: 400px; min-width: 300px; }
  .table-dokumen thead th:nth-child(9) { width: 200px; min-width: 180px; }

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 4px solid #083E40;
    border-bottom: 1px solid rgba(8, 62, 64, 0.1);
    background: #ffffff;
  }

  .table-dokumen tbody tr.main-row:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(8, 62, 64, 0.04) 100%);
    border-left-color: #889717;
    transform: translateX(2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.15);
  }

  .table-dokumen tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
    border-right: 1px solid rgba(8, 62, 64, 0.05);
    text-align: center;
    font-size: 13px;
    line-height: 1.5;
    word-wrap: break-word;
    overflow-wrap: break-word;
  }

  .table-dokumen tbody td:last-child {
    border-right: none;
  }

  /* Alasan Column - Bubble Chat Style */
  .alasan-column {
    max-width: 400px;
    min-width: 300px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.6;
    text-align: left !important;
    padding: 16px 20px !important;
  }

  .alasan-bubble {
    background: linear-gradient(135deg, #f0f9f9 0%, #e0f2f2 100%);
    color: #083E40;
    padding: 12px 16px;
    border-radius: 12px;
    border-left: 4px solid #083E40;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.1);
  }

  .alasan-bubble::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 20px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-right: 8px solid #083E40;
  }

  .alasan-icon {
    font-size: 18px;
    color: #083E40;
    flex-shrink: 0;
    margin-top: 2px;
  }

  .alasan-text {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.6;
    word-break: break-word;
  }

  .nilai-column {
    font-weight: 700;
    color: #083E40;
    font-size: 14px;
  }

  .tanggal-column small {
    background: linear-gradient(135deg, #e8f4fd 0%, #f0f8ff 100%);
    padding: 6px 10px;
    border-radius: 6px;
    color: #0066cc;
    font-size: 11px;
    font-weight: 500;
  }

  .uraian-column {
    text-align: left !important;
    max-width: 300px;
    min-width: 200px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.5;
  }

  .nomor-column {
    font-weight: 600;
    color: #2c3e50;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-action {
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 100px;
    min-height: 40px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
  }

  .btn-edit:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.4);
    color: white;
  }

  .btn-send {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .btn-send:hover {
    background: linear-gradient(135deg, #20c997 0%, #1ea085 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    color: white;
  }

  /* Badge Status */
  .badge-status {
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
  }

  .badge-status:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
  }

  .badge-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: 2px solid transparent;
  }

  .badge-returned {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
    border: 2px solid transparent;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  /* Detail Row */
  .detail-row {
    display: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-left: 4px solid #889717;
  }

  .detail-row.show {
    display: table-row;
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .detail-content {
    padding: 24px;
    border-top: 2px solid rgba(8, 62, 64, 0.1);
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px;
    background: white;
    border-radius: 8px;
    border: 1px solid rgba(8, 62, 64, 0.08);
    transition: all 0.2s ease;
  }

  .detail-item:hover {
    border-color: #889717;
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.1);
    transform: translateY(-1px);
  }

  .detail-label {
    font-size: 11px;
    font-weight: 600;
    color: #083E40;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .detail-value {
    font-size: 13px;
    color: #333;
    font-weight: 500;
    word-break: break-word;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
  }

  .empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
    color: #083E40;
  }

  .empty-state h5 {
    color: #083E40;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .empty-state p {
    color: #6c757d;
    margin-bottom: 20px;
  }

  .empty-state .btn-primary {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .empty-state .btn-primary:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  /* Pagination */
  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
    padding: 20px;
  }

  .pagination .page-link {
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    color: #083E40;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .pagination .page-link:hover {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .stats-container {
      grid-template-columns: 1fr;
    }

    .action-buttons {
      flex-direction: column;
      gap: 8px;
    }

    .btn-action {
      width: 100%;
      justify-content: center;
    }

    .table-dokumen {
      border-radius: 12px;
    }
  }

  @media (max-width: 480px) {
    .detail-grid {
      grid-template-columns: 1fr;
      gap: 8px;
    }

    .detail-item {
      padding: 8px;
    }

    .stat-card {
      flex-direction: column;
      text-align: center;
      gap: 16px;
    }

    .stat-icon-wrapper {
      margin: 0 auto;
    }
  }
</style>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="form-title">Daftar Pengembalian Dokumen Team Perpajakan ke Team Verifikasi</h2>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-container">
    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-label">
          <i class="fa-solid fa-file-invoice-dollar"></i>
          Total Dokumen Dikembalikan
        </div>
        <div class="stat-value">{{ $totalReturned ?? 0 }}</div>
      </div>
      <div class="stat-icon-wrapper">
        <i class="fa-solid fa-file-invoice-dollar stat-icon"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-label">
          <i class="fa-solid fa-clock"></i>
          Menunggu Verifikasi
        </div>
        <div class="stat-value">{{ $totalPending ?? 0 }}</div>
      </div>
      <div class="stat-icon-wrapper">
        <i class="fa-solid fa-clock stat-icon"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-label">
          <i class="fa-solid fa-check-circle"></i>
          Selesai Diverifikasi
        </div>
        <div class="stat-value">{{ $totalCompleted ?? 0 }}</div>
      </div>
      <div class="stat-icon-wrapper">
        <i class="fa-solid fa-check-circle stat-icon"></i>
      </div>
    </div>
  </div>

  <!-- Search Box -->
  <div class="search-box">
    <form action="{{ route('returns.perpajakan.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-3">
      <div class="input-group" style="flex: 1; min-width: 300px;">
        <span class="input-group-text">
          <i class="fa-solid fa-magnifying-glass"></i>
        </span>
        <input type="text" class="form-control" name="search" placeholder="Cari nomor agenda, nomor SPP, atau uraian..." value="{{ request('search') }}">
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-filter me-2"></i>Filter
      </button>
    </form>
  </div>

  <!-- Documents Table -->
  <div class="table-responsive">
    <div class="table-dokumen">
      @if(isset($dokumens) && $dokumens->count() > 0)
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nomor Agenda</th>
              <th>Nomor SPP</th>
              <th>Uraian</th>
              <th>Nilai</th>
              <th>Status Dokumen</th>
              <th>Tanggal Dikembalikan</th>
              <th>Alasan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dokumens as $index => $dokumen)
            <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td class="nomor-column">
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br>
                <small class="text-muted" style="font-size: 11px;">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              </td>
              <td class="nomor-column">{{ $dokumen->nomor_spp }}</td>
              <td class="uraian-column">{{ $dokumen->uraian_spp ?? '-' }}</td>
              <td class="nilai-column">{{ $dokumen->formatted_nilai_rupiah }}</td>
              <td>
                @if($dokumen->returned_from_perpajakan_fixed_at || ($dokumen->current_handler == 'perpajakan' && !$dokumen->pengembalian_awaiting_fix && $dokumen->returned_from_perpajakan_at))
                  <span class="badge-status badge-success">
                    <i class="fa-solid fa-check-circle"></i>
                    Sudah diperbaiki
                  </span>
                @else
                  <span class="badge-status badge-returned">
                    <i class="fa-solid fa-clock"></i>
                    Menunggu perbaikan
                  </span>
                @endif
              </td>
              <td class="tanggal-column">
                <small>{{ $dokumen->returned_from_perpajakan_at ? $dokumen->returned_from_perpajakan_at->format('d/m/Y H:i') : '-' }}</small>
              </td>
              <td class="alasan-column">
                <div class="alasan-bubble">
                  <i class="fa-solid fa-comment-dots alasan-icon"></i>
                  <span class="alasan-text">{{ $dokumen->alasan_pengembalian ?? '-' }}</span>
                </div>
              </td>
              <td onclick="event.stopPropagation()">
                <div class="action-buttons">
                  <a href="{{ route('documents.perpajakan.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
                    <i class="fa-solid fa-pen"></i>
                    <span>Edit</span>
                  </a>
                  <button type="button" class="btn-action btn-send" onclick="sendBackToVerification({{ $dokumen->id }})" title="Kirim ke Verifikasi">
                    <i class="fa-solid fa-undo"></i>
                    <span>Kembali</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display: none;">
              <td colspan="9">
                <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                  <div class="text-center p-4">
                    <i class="fa-solid fa-spinner fa-spin me-2" style="color: #083E40;"></i> 
                    <span style="color: #083E40; font-weight: 600;">Loading detail...</span>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center" style="padding: 20px;">
          <div class="text-muted" style="font-size: 13px; color: #6c757d;">
            Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }} dokumen
          </div>
          {{ $dokumens->links() }}
        </div>
      @else
        <div class="empty-state">
          <i class="fa-solid fa-file-invoice-dollar"></i>
          <h5>Belum ada dokumen</h5>
          <p>Tidak ada dokumen yang dikembalikan ke team verifikasi saat ini.</p>
          <a href="{{ route('documents.perpajakan.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Daftar Dokumen Team Perpajakan
          </a>
        </div>
      @endif
    </div>
  </div>
</div>

<script>
function toggleDetail(docId) {
  const detailRow = document.getElementById('detail-' + docId);

  // Toggle visibility
  if (detailRow.style.display === 'none' || detailRow.style.display === '') {
    detailRow.style.display = 'table-row';
    detailRow.classList.add('show');

    // Load detail content via AJAX
    loadDocumentDetail(docId);
  } else {
    detailRow.style.display = 'none';
    detailRow.classList.remove('show');
  }
}

function loadDocumentDetail(docId) {
  fetch(`/documents/perpajakan/${docId}/detail`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      const detailContent = document.getElementById(`detail-content-${docId}`);
      if (detailContent && data.success && data.html) {
        detailContent.innerHTML = data.html;
      } else if (detailContent && data.html) {
        // Fallback: jika response langsung HTML
        detailContent.innerHTML = data.html;
      } else {
        throw new Error('Invalid response format');
      }
    })
    .catch(error => {
      console.error('Error loading document detail:', error);
      const detailContent = document.getElementById(`detail-content-${docId}`);
      if (detailContent) {
        detailContent.innerHTML = '<div class="text-center p-4 text-danger"><i class="fa-solid fa-exclamation-triangle me-2"></i>Gagal memuat detail dokumen</div>';
      }
    });
}

function sendBackToVerification(docId) {
  if (confirm("Apakah Anda yakin ingin mengirim dokumen ini kembali ke proses verifikasi?")) {
    // AJAX call to send back to verification
    fetch(`/documents/perpajakan/${docId}/send-to-next`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        next_handler: 'ibuB'
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Dokumen berhasil dikirim kembali ke verifikasi!');
        location.reload();
      } else {
        alert('Gagal mengirim dokumen: ' + (data.message || 'Terjadi kesalahan'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan saat mengirim dokumen.');
    });
  }
}
</script>

@endsection

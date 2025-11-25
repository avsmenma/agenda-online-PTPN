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
  }

  .stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.08);
    border: 1px solid rgba(8, 62, 64, 0.05);
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(8, 62, 64, 0.12);
  }

  .stat-label {
    font-size: 13px;
    color: #666;
    font-weight: 500;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #333;
  }

  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 20px;
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
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    position: relative;
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
    background: linear-gradient(90deg, transparent 0%, rgba(136, 151, 23, 0.3) 50%, transparent 100%);
  }

  .table-dokumen thead th {
    padding: 18px 16px;
    font-weight: 700;
    font-size: 14px;
    border: none;
    text-align: center;
    letter-spacing: 0.8px;
    color: #000000;
    text-transform: uppercase;
    white-space: nowrap;
    position: relative;
    text-shadow: none;
    border-right: 1px solid rgba(0, 0, 0, 0.1);
  }

  .table-dokumen thead th:last-child {
    border-right: none;
  }

  /* Column width settings for better layout */
  .table-dokumen thead th:nth-child(1) { width: 60px; min-width: 60px; } /* No */
  .table-dokumen thead th:nth-child(2) { width: 150px; min-width: 150px; } /* Nomor Agenda */
  .table-dokumen thead th:nth-child(3) { width: 180px; min-width: 180px; } /* Nomor SPP */
  .table-dokumen thead th:nth-child(4) { width: 250px; min-width: 200px; } /* Uraian */
  .table-dokumen thead th:nth-child(5) { width: 140px; min-width: 140px; } /* Nilai */
  .table-dokumen thead th:nth-child(6) { width: 160px; min-width: 160px; } /* Status Perpajakan */
  .table-dokumen thead th:nth-child(7) { width: 180px; min-width: 180px; } /* Tanggal Dikembalikan */
  .table-dokumen thead th:nth-child(8) { width: 400px; min-width: 300px; } /* Alasan */
  .table-dokumen thead th:nth-child(9) { width: 200px; min-width: 180px; } /* Aksi */

  .table-dokumen thead th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 2px;
    background: linear-gradient(90deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 50%, rgba(0,0,0,0.3) 100%);
    border-radius: 1px;
  }

  .table-dokumen tbody tr {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    border-bottom: 1px solid rgba(136, 151, 23, 0.05);
  }

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
  }

  .table-dokumen tbody tr:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(8, 62, 64, 0.04) 100%);
    border-left: 3px solid #889717;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.1);
  }

  .table-dokumen tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(136, 151, 23, 0.03);
    border-right: 1px solid rgba(136, 151, 23, 0.05);
    text-align: center;
    font-size: 13px;
    line-height: 1.5;
    word-wrap: break-word;
    overflow-wrap: break-word;
  }

  .table-dokumen tbody td:last-child {
    border-right: none;
  }

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
    display: block;
    hyphens: auto;
    font-size: 12px;
    color: #555;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px;
    border-left: 3px solid #889717;
    width: 100%;
    box-sizing: border-box;
    margin: 0;
  }

  .nilai-column {
    font-weight: 600;
    color: #2c3e50;
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

  .action-buttons {
    display: flex;
    gap: 6px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-width: 44px;
    min-height: 36px;
  }

  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .btn-edit:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
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
    }
    to {
      opacity: 1;
    }
  }

  .detail-content {
    padding: 20px;
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

  .badge-status {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .badge-returned {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  .chevron-icon {
    transition: transform 0.3s ease;
    color: #fff;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
  }

  .empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
  }

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
  }

  @media (max-width: 480px) {
    .detail-grid {
      grid-template-columns: 1fr;
      gap: 8px;
    }

    .detail-item {
      padding: 8px;
    }
  }
</style>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="form-title">Daftar Pengembalian Dokumen Team Perpajakan ke team verifikasi</h2>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-container">
    <div class="stat-card">
      <div class="stat-label">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        Total Dokumen Dikembalikan
      </div>
      <div class="stat-value">{{ $totalReturned ?? 0 }}</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">
        <i class="fa-solid fa-clock"></i>
        Menunggu Verifikasi
      </div>
      <div class="stat-value">{{ $totalPending ?? 0 }}</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">
        <i class="fa-solid fa-check-circle"></i>
        Selesai Diverifikasi
      </div>
      <div class="stat-value">{{ $totalCompleted ?? 0 }}</div>
    </div>
  </div>

  <!-- Search Box -->
  <div class="search-box">
    <form action="{{ route('pengembalianPerpajakan.index') }}" method="GET" class="d-flex align-items-center w-100">
      <div class="input-group me-3" style="max-width: 300px;">
        <span class="input-group-text">
          <i class="fa-solid fa-search"></i>
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
              <th style="width: 50px;">No</th>
              <th>Nomor Agenda</th>
              <th>Nomor SPP</th>
              <th>Uraian</th>
              <th>Nilai</th>
              <th>Status Dokumen</th>
              <th>Tanggal Dikembalikan</th>
              <th>Alasan</th>
              <th style="width: 200px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dokumens as $index => $dokumen)
            <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td class="nomor-column">
                {{ $dokumen->nomor_agenda }}
                <br>
                <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
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
                {{ $dokumen->alasan_pengembalian ?? '-' }}
              </td>
              <td onclick="event.stopPropagation()">
                <div class="action-buttons">
                  <a href="{{ route('dokumensPerpajakan.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
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
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
          <div class="text-muted">
            Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }} dokumen
          </div>
          {{ $dokumens->links() }}
        </div>
      @else
        <div class="empty-state">
          <i class="fa-solid fa-file-invoice-dollar"></i>
          <h5>Belum ada dokumen</h5>
          <p class="mt-2">Tidak ada dokumen yang dikembalikan ke team verifikasi saat ini.</p>
          <a href="{{ route('dokumensPerpajakan.index') }}" class="btn btn-primary mt-3">
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
  fetch(`/dokumensPerpajakan/${docId}/detail`)
    .then(response => response.text())
    .then(html => {
      const detailContent = document.getElementById(`detail-content-${docId}`);
      if (detailContent) {
        detailContent.innerHTML = html;
      }
    })
    .catch(error => {
      console.error('Error loading document detail:', error);
      const detailContent = document.getElementById(`detail-content-${docId}`);
      if (detailContent) {
        detailContent.innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat detail dokumen</div>';
      }
    });
}

function sendBackToVerification(docId) {
  if (confirm("Apakah Anda yakin ingin mengirim dokumen ini kembali ke proses verifikasi?")) {
    // AJAX call to send back to verification
    fetch(`/pengembalian-dokumensPerpajakan/${docId}/kirim-kembali`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
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
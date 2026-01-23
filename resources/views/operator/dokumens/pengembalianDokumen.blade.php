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

  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .table-dokumen thead {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    position: relative;
  }

  .table-dokumen thead::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #889717 50%, transparent 100%);
  }

  .table-dokumen thead th {
    padding: 16px 12px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    text-align: center;
    letter-spacing: 0.5px;
  }

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table-dokumen tbody tr.main-row:hover {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    border-left: 3px solid #889717;
    transform: scale(1.005);
  }

  .table-dokumen tbody tr.main-row.highlight {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.15) 0%, transparent 100%) !important;
    border-left: 3px solid #889717;
  }

  .table-dokumen tbody tr.main-row.selected {
    background: linear-gradient(90deg, rgba(8, 62, 64, 0.05) 0%, transparent 100%);
    border-left: 3px solid #083E40;
  }

  .table-dokumen tbody td {
    padding: 14px 12px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
  }

  .row{
    margin-right:10px;
    justify-content : space-between;
    gap: 24px;
  }

  .row > [class*='col-'] {
    padding-left: 12px;
    padding-right: 12px;
  }

   .detail-row {
    display: none;
    background: linear-gradient(135deg, #f8faf8 0%, #ffffff 100%);
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
    padding: 24px;
    border-radius: 8px;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    padding: 12px;
    background: white;
    border-radius: 8px;
    border-left: 3px solid #889717;
    transition: all 0.3s ease;
  }

  .detail-item:hover {
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.1);
    transform: translateY(-2px);
  }

  .detail-label {
    font-weight: 600;
    color: #083E40;
    font-size: 12px;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }

  .detail-value {
    color: #333;
    font-size: 13px;
    font-weight: 500;
  }

  .badge-status {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .badge-Dikembalikan {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
  }

  .btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .btn-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .btn-view {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .btn-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }

  .btn-action:active {
    transform: translateY(-1px);
  }

  .filter-section {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .filter-section select,
  .filter-section input {
    padding: 10px 14px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  .filter-section select:focus,
  .filter-section input:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-filter {
    padding: 10px 24px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 62, 64, 0.3);
  }

  .btn-tambah {
    padding: 10px 24px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.2);
  }

  .btn-tambah:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(136, 151, 23, 0.3);
  }

  .chevron-icon {
    transition: transform 0.4s ease;
    color: #fff;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
  }

  .pagination button {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    cursor: pointer;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: #083E40;
  }

  .pagination button:hover {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .pagination button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
  }

  .btn-chevron:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
  }

  .card-info {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    border-radius: 16px;
    color: white;
    padding: 32px 24px;
    box-shadow: 0 8px 24px rgba(8, 62, 64, 0.2), 0 2px 8px rgba(136, 151, 23, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 160px;
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .card-info::before {
    /* content: ''; */
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    transition: all 0.5s ease;
  }

  .card-info:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(8, 62, 64, 0.3), 0 4px 12px rgba(136, 151, 23, 0.2);
  }

  .card-info:hover::before {
    top: -60%;
    right: -60%;
  }

  .card-info .icon-box {
    background: rgba(255,255,255,0.25);
    padding: 16px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
  }

  .card-info:hover .icon-box {
    background: rgba(255,255,255,0.35);
    transform: scale(1.1) rotate(5deg);
  }

  .card-info i {
    color: white;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
  }

  .card-title-small {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    opacity: 0.95;
  }

  .card-number {
    font-size: 28px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  /* Checkbox styling */
  input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #889717;
  }

</style>
<div class="card mb-4 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 16px; box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05); border: 1px solid rgba(8, 62, 64, 0.08);">
    <h2 class="form-title">{{ $title }}</h2>
</div>
<div class="row justify-content-center">

    <!-- Card 1 -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card-info">
            <div class="flex-grow-1">
                <div class="card-title-small">T. Dokumen yang Dibaca</div>
                <div class="card-number">{{ number_format($totalDibaca ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="icon-box ms-3">
                <i class="fas fa-refresh fa-2x"></i>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card-info">
            <div class="flex-grow-1">
                <div class="card-title-small">T. Dokumen Dikembalikan</div>
                <div class="card-number">{{ number_format($totalDikembalikan ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="icon-box ms-3">
                <i class="fas fa-book-open fa-2x"></i>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card-info">
            <div class="flex-grow-1">
                <div class="card-title-small">Dokumen Dikirim</div>
                <div class="card-number">{{ number_format($totalDikirim ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="icon-box ms-3">
                <i class="fas fa-paper-plane fa-2x"></i>
            </div>
        </div>
    </div>

</div>


<!-- Tabel Dokumen -->
<div class="table-dokumen">
  <table class="table mb-0">
    <thead>
      <tr>
        <th style="width: 50px;">No</th>
        <th>Nomor Surat</th>
        <th>Tanggal Masuk</th>
        <th>Nomor SPP</th>
        <th>Tanggal SPP</th>
        <th>Uraian Ketidaklengkapan</th>
        <th>Status</th>
        <th style="width: 150px;">Aksi</th>
        <th style="width: 150px;">Status Dibaca</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dokumens ?? [] as $dokumen)
      <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
        <td style="text-align: center;">{{ $loop->iteration }}</td>
        <td>{{ $dokumen->nomor_agenda }}</td>
        <td>{{ $dokumen->tanggal_masuk->format('d/m/Y H:i') }}</td>
        <td>{{ $dokumen->nomor_spp }}</td>
        <td>{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y H:i') : '-' }}</td>
        <td>
          @if($dokumen->alasan_pengembalian)
            <div class="alasan-pengembalian">
              <span class="alasan-text">{{ Str::limit($dokumen->alasan_pengembalian, 100) }}</span>
              @if(strlen($dokumen->alasan_pengembalian) > 100)
                <button class="btn-link btn-sm" onclick="toggleAlasan({{ $dokumen->id }})">
                  <i class="fa-solid fa-chevron-down"></i> Lihat selengkapnya
                </button>
                <div id="alasan-full-{{ $dokumen->id }}" style="display: none; margin-top: 8px;">
                  <small class="text-muted">{{ $dokumen->alasan_pengembalian }}</small>
                </div>
              @endif
            </div>
          @else
            <span class="text-muted">Tidak ada alasan</span>
          @endif
        </td>
        <td>
          <span class="badge badge-status badge-dikembalikan">Dikembalikan</span>
        </td>
        <td>
          <div class="action-buttons">
            <a href="{{ route('documents.edit', $dokumen->id) }}" class="btn-action btn-view" title="Edit">
              <i class="fa-solid fa-edit"></i>
            </a>
            <button type="button" class="btn-action btn-chevron" onclick="toggleDetail({{ $dokumen->id }})" title="Lihat Detail">
              <i class="fa-solid fa-chevron-down chevron-icon" id="chevron-{{ $dokumen->id }}"></i>
            </button>
          </div>
        </td>
        <td>
          <span class="text-muted">-</span>
        </td>
      </tr>
      <tr class="detail-row" id="detail-{{ $dokumen->id }}">
        <td colspan="9">
          <div class="detail-content">
            <h6 style="color: #083E40; margin-bottom: 20px; font-weight: 700; border-bottom: 2px solid #889717; padding-bottom: 10px;">Detail Lengkap Dokumen</h6>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Nomor Agenda</span>
                <span class="detail-value">{{ $dokumen->nomor_agenda }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Nomor SPP</span>
                <span class="detail-value">{{ $dokumen->nomor_spp }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Tanggal Masuk</span>
                <span class="detail-value">{{ $dokumen->tanggal_masuk->format('d-m-Y H:i') }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Nilai Rupiah</span>
                <span class="detail-value"><strong>{{ $dokumen->formatted_nilai_rupiah }}</strong></span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">{{ $dokumen->status }}</span>
              </div>
              @if($dokumen->alasan_pengembalian)
              <div class="detail-item">
                <span class="detail-label">Alasan Pengembalian</span>
                <span class="detail-value text-danger">{{ $dokumen->alasan_pengembalian }}</span>
              </div>
              @endif
              <div class="detail-item">
                <span class="detail-label">Keterangan</span>
                <span class="detail-value">{{ $dokumen->keterangan ?? '-' }}</span>
              </div>
              @if($dokumen->returned_to_Operator_at)
              <div class="detail-item">
                <span class="detail-label">Tanggal Dikembalikan</span>
                <span class="detail-value">{{ $dokumen->returned_to_Operator_at->format('d-m-Y H:i') }}</span>
              </div>
              @endif
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" class="text-center" style="padding: 40px;">
          <i class="fa-solid fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
          <p style="color: #999; font-size: 14px;">Belum ada dokumen dikembalikan</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Pagination -->
{{-- Pagination akan diisi dari database --}}
<div class="pagination">
  {{-- Pagination akan di-generate oleh backend --}}
</div>

<script>
function toggleDetail(rowId) {
  const detailRow = document.getElementById('detail-' + rowId);
  const chevron = document.getElementById('chevron-' + rowId);

  detailRow.classList.toggle('show');
  chevron.classList.toggle('rotate');
}

function toggleAlasan(docId) {
  const fullAlasan = document.getElementById('alasan-full-' + docId);
  const button = event.target.closest('.btn-link');
  const icon = button.querySelector('i');

  if (fullAlasan.style.display === 'none') {
    fullAlasan.style.display = 'block';
    button.innerHTML = '<i class="fa-solid fa-chevron-up"></i> Tutup';
  } else {
    fullAlasan.style.display = 'none';
    button.innerHTML = '<i class="fa-solid fa-chevron-down"></i> Lihat selengkapnya';
  }
}
</script>

<style>
.alasan-pengembalian {
  max-width: 300px;
}

.alasan-text {
  display: block;
  line-height: 1.4;
}

.btn-link {
  background: none;
  border: none;
  color: #007bff;
  text-decoration: none;
  cursor: pointer;
  padding: 2px 4px;
  font-size: 12px;
}

.btn-link:hover {
  text-decoration: underline;
  color: #0056b3;
}

.detail-value.text-danger {
  background-color: #f8d7da;
  border-left: 3px solid #dc3545;
  padding: 8px 12px;
  border-radius: 4px;
  font-weight: 500;
  line-height: 1.5;
}
</style>

@endsection




@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 20px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .search-box .input-group {
    max-width: auto;
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

  /* Table Container - Enhanced Horizontal Scroll */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
    overflow: hidden;
  }

  /* Horizontal Scroll Container */
  .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(8, 62, 64, 0.3) transparent;
  }

  .table-responsive::-webkit-scrollbar {
    height: 12px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: rgba(8, 62, 64, 0.05);
    border-radius: 6px;
    margin: 0 20px;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.3), rgba(136, 151, 23, 0.4));
    border-radius: 6px;
    border: 2px solid rgba(255, 255, 255, 0.8);
  }

  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.5), rgba(136, 151, 23, 0.6));
  }

  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1400px; /* Minimum width for horizontal scroll */
    width: 100%;
  }

  .table-enhanced thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    white-space: nowrap;
  }

  /* Column Widths */
  .table-enhanced .col-no { width: 50px; text-align: center; }
  .table-enhanced .col-agenda { width: 140px; min-width: 140px; }
  .table-enhanced .col-tanggal { width: 120px; min-width: 120px; }
  .table-enhanced .col-spp { width: 140px; min-width: 140px; }
  .table-enhanced .col-nilai { width: 150px; min-width: 150px; }
  .table-enhanced .col-tanggal-spp { width: 120px; min-width: 120px; }
  .table-enhanced .col-uraian { width: 300px; min-width: 300px; }
  .table-enhanced .col-deadline { width: 140px; min-width: 140px; }
  .table-enhanced .col-status { width: 140px; min-width: 140px; text-align: center; }
  .table-enhanced .col-action { width: 80px; text-align: center; }

  .table-enhanced tbody tr {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    background: white;
  }

  .table-enhanced tbody tr.locked-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #eef3f3 100%);
    border-left-color: #ffc107;
    position: relative;
  }

  .table-enhanced tbody tr.locked-row::before {
    content: '🔒';
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    opacity: 0.6;
  }

  .table-enhanced tbody tr:hover {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    border-left: 3px solid #889717;
    transform: scale(1.005);
  }

  .table-enhanced tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    font-size: 13px;
    font-weight: 500;
    color: #2c3e50;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
  }

  /* Status Badge */
  .badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-block;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .badge-selesai {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.3);
  }

  .badge-proses {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  .badge-belum {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
  }

  .badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
  }

  /* Action Buttons */
  .btn-action {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 11px;
    transition: all 0.3s ease;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    margin: 0 2px;
  }

  .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-edit {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  }

  .btn-edit:hover {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  }

  .btn-detail {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  }

  .btn-detail:hover {
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
  }

  .btn-locked {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    cursor: not-allowed;
    opacity: 0.8;
  }

  /* Deadline styling */
  .deadline-soon {
    color: #dc3545;
    font-weight: 600;
  }

  .deadline-normal {
    color: #2c3e50;
  }

  /* Detail Row Styles */
  .detail-row {
    display: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
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

  .main-row {
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .main-row.selected {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    border-left: 3px solid #889717;
  }

  .detail-content {
    padding: 20px;
    border-top: 2px solid rgba(8, 62, 64, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
  }

  /* Detail Grid */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    width: 100%;
    box-sizing: border-box;
  }

  @media (min-width: 1400px) {
    .detail-grid {
      grid-template-columns: repeat(5, 1fr);
    }
  }

  @media (min-width: 1200px) and (max-width: 1399px) {
    .detail-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (max-width: 1199px) {
    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px;
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

  /* Separator for Perpajakan Data */
  .detail-section-separator {
    margin: 32px 0 24px 0;
    padding: 0;
  }

  .separator-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
    border-radius: 12px;
    border-left: 4px solid #ffc107;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.15);
  }

  .separator-content i {
    font-size: 20px;
    color: #ffc107;
  }

  .separator-content span:first-of-type {
    font-weight: 600;
    color: #856404;
    font-size: 14px;
  }

  .tax-badge {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    white-space: nowrap;
    margin-left: auto;
  }

  /* Tax Section Styling */
  .tax-section {
    position: relative;
  }

  .tax-section .detail-item {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid rgba(255, 193, 7, 0.15);
    position: relative;
  }

  .tax-section .detail-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    opacity: 0.3;
    border-radius: 3px 0 0 3px;
  }

  .tax-section .detail-item:hover {
    border-color: rgba(255, 193, 7, 0.4);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.1);
    transform: translateY(-2px);
  }

  .tax-section .detail-item:hover::before {
    opacity: 1;
  }

  .empty-field {
    color: #999;
    font-style: italic;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .tax-link {
    color: #0066cc;
    text-decoration: none;
    word-break: break-all;
  }

  .tax-link:hover {
    text-decoration: underline;
  }

  /* Badge styles for detail view */
  .badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
  }

  .badge.badge-selesai {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .badge.badge-proses {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .table-dokumen {
      padding: 15px;
    }

    .table-enhanced thead th {
      padding: 12px 8px;
      font-size: 11px;
    }

    .table-enhanced tbody td {
      padding: 10px 8px;
      font-size: 12px;
    }

    .btn-action {
      padding: 4px 6px;
      font-size: 10px;
    }

    .detail-content {
      padding: 16px;
    }

    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 10px;
    }

    .detail-item {
      padding: 10px;
    }

    .detail-label {
      font-size: 10px;
    }

    .detail-value {
      font-size: 12px;
    }

    .tax-badge {
      font-size: 10px;
      padding: 4px 12px;
    }
  }
</style>

<h2>{{ $title }}</h2>

<!-- Search Box -->
<div class="search-box">
  <div class="input-group">
    <span class="input-group-text">
      <i class="fa-solid fa-magnifying-glass text-muted"></i>
    </span>
    <input type="text" id="akutansiSearchInput" class="form-control" placeholder="Cari dokumen akutansi...">
  </div>
</div>

<!-- Tabel Dokumen -->
<div class="table-dokumen">
  <div class="table-responsive">
    <table class="table table-enhanced mb-0">
      <thead>
        <tr>
          <th class="col-no">No</th>
          <th class="col-agenda">Nomor Agenda</th>
          <th class="col-tanggal">Tanggal Masuk</th>
          <th class="col-spp">Nomor SPP</th>
          <th class="col-nilai">Nilai Rupiah</th>
          <th class="col-tanggal-spp">Tanggal SPP</th>
          <th class="col-uraian">Uraian</th>
          <th class="col-deadline">Deadline</th>
          <th class="col-status">Status</th>
          <th class="col-action">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($dokumens as $index => $dokumen)
        @php
          $isLocked = $dokumen->current_handler === 'akutansi'
            && $dokumen->status === 'sent_to_akutansi'
            && is_null($dokumen->deadline_at);
        @endphp
        <tr class="main-row {{ $isLocked ? 'locked-row' : '' }}" data-dokumen-id="{{ $dokumen->id }}" onclick="toggleDetail({{ $dokumen->id }})" title="Klik untuk melihat detail lengkap dokumen">
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>
              <strong>{{ $dokumen->nomor_agenda }}</strong>
              <br>
              <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
            </td>
            <td>{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y') : '-' }}</td>
            <td>{{ $dokumen->nomor_spp }}</td>
            <td><strong>{{ $dokumen->formatted_nilai_rupiah }}</strong></td>
            <td>{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</td>
            <td>{{ Str::limit($dokumen->uraian_spp, 60) ?? '-' }}</td>
            <td>
              @if($dokumen->deadline_at)
                @php
                  $deadlineDate = $dokumen->deadline_at;
                  $isOverdue = $deadlineDate->isPast();
                  $daysLeft = $deadlineDate->diffInDays(\Carbon\Carbon::now());
                @endphp
                <small class="{{ $isOverdue ? 'deadline-soon' : 'deadline-normal' }}">
                  <strong>{{ $deadlineDate->format('d M Y') }}</strong>
                  @if($isOverdue)
                    <br><span class="text-danger">Terlambat {{ $daysLeft }} hari</span>
                  @elseif($daysLeft <= 3)
                    <br><span class="text-warning">{{ $daysLeft }} hari lagi</span>
                  @endif
                </small>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td style="text-align: center;">
              @if($dokumen->status == 'selesai')
                <span class="badge-status badge-selesai">✓ Selesai</span>
              @elseif($dokumen->status == 'sedang diproses' && $dokumen->current_handler == 'akutansi')
                <span class="badge-status badge-proses">⏳ Diproses</span>
              @elseif($isLocked)
                <span class="badge-status badge-belum">🔒 Terkunci</span>
              @elseif($dokumen->status == 'sent_to_akutansi')
                <span class="badge-status badge-belum">⏳ Belum Diproses</span>
              @elseif(in_array($dokumen->status, ['returned_to_ibua', 'returned_to_department', 'dikembalikan']))
                <span class="badge-status badge-dikembalikan">← Dikembalikan</span>
              @else
                <span class="badge-status badge-proses">{{ $dokumen->status }}</span>
              @endif
            </td>
            <td style="text-align: center;" onclick="event.stopPropagation()">
              <div class="d-flex justify-content-center flex-wrap gap-1">
                @if($dokumen->status == 'sent_to_pembayaran')
                  <span class="badge-status badge-selesai" style="font-size: 10px;">✓ Terkirim ke Pembayaran</span>
                @elseif($isLocked)
                  <button class="btn-action btn-locked" disabled title="Dokumen terkunci. Tetapkan deadline untuk membuka.">
                    <i class="fa-solid fa-lock"></i>
                  </button>
                  <button class="btn-action btn-action" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);" onclick="openSetDeadlineModal({{ $dokumen->id }})" title="Tetapkan Deadline">
                    <i class="fa-solid fa-clock"></i>
                  </button>
                @else
                  <button class="btn-action btn-edit" onclick="editDocument({{ $dokumen->id }})" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn-action" style="background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);" onclick="sendToPembayaran({{ $dokumen->id }})" title="Kirim ke Pembayaran">
                    <i class="fa-solid fa-paper-plane"></i>
                  </button>
                @endif
              </div>
            </td>
          </tr>
          <tr class="detail-row" id="detail-{{ $dokumen->id }}">
            <td colspan="10">
              <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                <div class="text-center p-4">
                  <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center py-5">
              <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
              <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Set Deadline -->
<div class="modal fade" id="setDeadlineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-clock me-2"></i>Tetapkan Timeline Akutansi
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="deadlineDocId">

        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.12) 0%, rgba(255, 140, 0, 0.12) 100%); border-left: 4px solid #ffc107;">
          <i class="fa-solid fa-info-circle me-2"></i>
          Dokumen akan tetap terkunci sampai timeline ditetapkan. Setelah dibuka, dokumen dapat diedit atau dikirim.
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Periode Deadline*</label>
          <select class="form-select" id="deadlineDays" required>
            <option value="">Pilih periode deadline</option>
            <option value="1">1 hari</option>
            <option value="2">2 hari</option>
            <option value="3">3 hari</option>
            <option value="5">5 hari</option>
            <option value="7">7 hari</option>
            <option value="14">14 hari</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Catatan (opsional)</label>
          <textarea class="form-control" id="deadlineNote" rows="3" maxlength="500" placeholder="Contoh: Menunggu kelengkapan dokumen pendukung"></textarea>
          <small class="text-muted"><span id="deadlineCharCount">0</span>/500 karakter</small>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-warning" onclick="confirmSetDeadline()">
          <i class="fa-solid fa-check me-2"></i>Tetapkan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle detail row
function toggleDetail(docId) {
  const detailRow = document.getElementById('detail-' + docId);
  const mainRow = event.currentTarget;

  // Close all other detail rows first
  const allDetailRows = document.querySelectorAll('.detail-row.show');
  const allMainRows = document.querySelectorAll('.main-row.selected');

  allDetailRows.forEach(row => {
    if (row.id !== 'detail-' + docId) {
      row.classList.remove('show');
    }
  });

  allMainRows.forEach(row => {
    if (row !== mainRow) {
      row.classList.remove('selected');
    }
  });

  // Toggle current detail row
  const isShowing = detailRow.classList.contains('show');

  if (isShowing) {
    detailRow.classList.remove('show');
    mainRow.classList.remove('selected');
  } else {
    loadDocumentDetail(docId);
    detailRow.classList.add('show');
    mainRow.classList.add('selected');

    setTimeout(() => {
      detailRow.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest'
      });
    }, 100);
  }
}

// Load document detail via AJAX
function loadDocumentDetail(docId) {
  const detailContent = document.getElementById('detail-content-' + docId);

  detailContent.innerHTML = `
    <div class="text-center p-4">
      <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
    </div>
  `;

  fetch(`/dokumensAkutansi/${docId}/detail`)
    .then(response => response.text())
    .then(html => {
      detailContent.innerHTML = html;
    })
    .catch(error => {
      console.error('Error:', error);
      detailContent.innerHTML = `
        <div class="text-center p-4 text-danger">
          <i class="fa-solid fa-exclamation-triangle me-2"></i> Gagal memuat detail dokumen.
        </div>
      `;
    });
}

function editDocument(id) {
  // Implement edit functionality
  window.location.href = `/dokumensAkutansi/${id}/edit`;
}

function sendToPembayaran(id) {
  if (!confirm('Apakah Anda yakin ingin mengirim dokumen ini ke Pembayaran?')) {
    return;
  }

  // Show loading state
  const btn = event.target.closest('button');
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  fetch(`/dokumensAkutansi/${id}/send-to-pembayaran`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Dokumen berhasil dikirim ke Pembayaran!');
      location.reload();
    } else {
      alert(data.message || 'Gagal mengirim dokumen.');
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat mengirim dokumen.');
    btn.disabled = false;
    btn.innerHTML = originalHTML;
  });
}

// Search functionality
document.getElementById('akutansiSearchInput').addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const allRows = document.querySelectorAll('.table-enhanced tbody tr');

  allRows.forEach(row => {
    // Skip detail rows in search
    if (row.classList.contains('detail-row')) {
      return;
    }

    const text = row.textContent.toLowerCase();
    if (text.includes(searchTerm)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
      // Also hide corresponding detail row
      const rowId = row.getAttribute('onclick')?.match(/toggleDetail\((\d+)\)/)?.[1];
      if (rowId) {
        const detailRow = document.getElementById('detail-' + rowId);
        if (detailRow) {
          detailRow.style.display = 'none';
        }
      }
    }
  });
});

document.getElementById('deadlineNote').addEventListener('input', function(e) {
  document.getElementById('deadlineCharCount').textContent = e.target.value.length;
});

function openSetDeadlineModal(docId) {
  document.getElementById('deadlineDocId').value = docId;
  document.getElementById('deadlineDays').value = '';
  document.getElementById('deadlineNote').value = '';
  document.getElementById('deadlineCharCount').textContent = '0';
  const modal = new bootstrap.Modal(document.getElementById('setDeadlineModal'));
  modal.show();
}

function confirmSetDeadline() {
  const docId = document.getElementById('deadlineDocId').value;
  const deadlineDays = document.getElementById('deadlineDays').value;
  const deadlineNote = document.getElementById('deadlineNote').value;

  if (!deadlineDays) {
    alert('Pilih periode deadline terlebih dahulu!');
    return;
  }

  const submitBtn = document.querySelector('#setDeadlineModal .btn-warning');
  const originalHTML = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

  fetch(`/dokumensAkutansi/${docId}/set-deadline`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      deadline_days: parseInt(deadlineDays, 10),
      deadline_note: deadlineNote
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('setDeadlineModal'));
      modal.hide();
      alert('Deadline berhasil ditetapkan! Dokumen kini terbuka untuk diproses.');
      location.reload();
    } else {
      alert(data.message || 'Gagal menetapkan deadline.');
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat menetapkan deadline.');
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
  });
}
</script>

@endsection
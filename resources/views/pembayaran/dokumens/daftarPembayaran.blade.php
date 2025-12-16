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

  /* Filter Buttons */
  .filter-buttons {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .filter-label {
    font-weight: 600;
    color: #083E40;
    font-size: 13px;
    margin: 0;
    white-space: nowrap;
  }

  .btn-filter {
    padding: 8px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    font-size: 12px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
  }

  .btn-filter:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, rgba(136, 151, 23, 0.05) 100%);
    border-color: #889717;
    color: #083E40;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.2);
  }

  .btn-filter.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
  }

  .btn-filter.active:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.4);
  }

  @media (max-width: 768px) {
    .filter-buttons {
      flex-direction: column;
      align-items: stretch;
    }

    .filter-label {
      text-align: center;
    }

    .btn-group {
      display: flex;
      flex-direction: column;
      width: 100%;
    }

    .btn-filter {
      width: 100%;
      justify-content: center;
    }
  }

  /* Table Container */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
    overflow: hidden;
  }

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

  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1400px;
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

  .badge-siap {
    background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
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

  .tax-section .detail-item:hover::before {
    opacity: 1;
  }

  .empty-field {
    color: #999;
    font-style: italic;
    font-size: 12px;
  }

  .tax-link {
    color: #0066cc;
    text-decoration: none;
    word-break: break-all;
  }

  .tax-link:hover {
    text-decoration: underline;
  }

  /* Badge styles */
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
</style>

<h2>{{ $title }}</h2>

<!-- Search Box -->
<div class="search-box">
  <div class="row g-3">
    <div class="col-md-6">
      <div class="input-group">
        <span class="input-group-text">
          <i class="fa-solid fa-magnifying-glass text-muted"></i>
        </span>
        <input type="text" id="pembayaranSearchInput" class="form-control" placeholder="Cari dokumen pembayaran...">
      </div>
    </div>
    <div class="col-md-6">
      <div class="filter-buttons">
        <label class="filter-label">
          <i class="fa-solid fa-filter me-2"></i>Filter Status:
        </label>
        <div class="btn-group" role="group">
          <a href="{{ route('documents.pembayaran.index') }}" 
             class="btn btn-filter {{ !$statusFilter ? 'active' : '' }}">
            <i class="fa-solid fa-list me-1"></i>Semua
          </a>
          <a href="{{ route('documents.pembayaran.index', ['status_filter' => 'belum_siap_dibayar']) }}" 
             class="btn btn-filter {{ $statusFilter === 'belum_siap_dibayar' ? 'active' : '' }}">
            <i class="fa-solid fa-clock me-1"></i>Belum Siap
          </a>
          <a href="{{ route('documents.pembayaran.index', ['status_filter' => 'siap_dibayar']) }}" 
             class="btn btn-filter {{ $statusFilter === 'siap_dibayar' ? 'active' : '' }}">
            <i class="fa-solid fa-check-circle me-1"></i>Sudah Siap
          </a>
          <a href="{{ route('documents.pembayaran.index', ['status_filter' => 'sudah_dibayar']) }}" 
             class="btn btn-filter {{ $statusFilter === 'sudah_dibayar' ? 'active' : '' }}">
            <i class="fa-solid fa-check-double me-1"></i>Sudah Dibayar
          </a>
        </div>
      </div>
    </div>
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
          <th class="col-status">Status</th>
          <th class="col-action">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($dokumens as $index => $dokumen)
        @php
          // Handler yang dianggap "belum siap dibayar"
          $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];
          
          // Cek apakah dokumen masih di handler yang belum siap
          $isBelumSiap = in_array($dokumen->current_handler, $belumSiapHandlers);
          
          // Cek apakah dokumen sudah terkirim ke pembayaran (bisa diedit)
          $isSentToPembayaran = $dokumen->status === 'sent_to_pembayaran' || $dokumen->current_handler === 'pembayaran';
          
          // Use DokumenHelper to check lock status
          $isLocked = \App\Helpers\DokumenHelper::isDocumentLocked($dokumen);
          $canSetDeadline = \App\Helpers\DokumenHelper::canSetDeadline($dokumen)['can_set'];
          $canEdit = \App\Helpers\DokumenHelper::canEditDocument($dokumen, 'pembayaran');
        @endphp
        <tr class="main-row {{ $isLocked ? 'locked-row' : '' }}" onclick="toggleDetail({{ $dokumen->id }})" title="Klik untuk melihat detail lengkap dokumen">
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
            <td style="text-align: center;">
              @if($dokumen->status_pembayaran == 'sudah_dibayar')
                <span class="badge-status badge-selesai">✓ Sudah Dibayar</span>
              @elseif($isBelumSiap)
                <span class="badge-status badge-proses">⏳ Belum Siap</span>
              @elseif($dokumen->status_pembayaran == 'siap_dibayar')
                <span class="badge-status badge-siap">✓ Siap Dibayar</span>
              @elseif($isLocked)
                <span class="badge-status badge-belum">🔒 Terkunci</span>
              @else
                <span class="badge-status badge-proses">⏳ Belum Dibayar</span>
              @endif
            </td>
            <td style="text-align: center;" onclick="event.stopPropagation()">
              <div class="d-flex justify-content-center flex-wrap gap-1">
                @if($isBelumSiap)
                  {{-- Dokumen belum siap - hanya bisa dilihat, tidak bisa diedit --}}
                  <span class="badge-status badge-proses" style="font-size: 10px; padding: 4px 8px;">
                    <i class="fa-solid fa-eye me-1"></i>Hanya Lihat
                  </span>
                @elseif($isLocked)
                  {{-- Dokumen terkunci - perlu set deadline dulu --}}
                  <button class="btn-action btn-locked" disabled title="Dokumen terkunci. Tetapkan deadline untuk membuka.">
                    <i class="fa-solid fa-lock"></i>
                  </button>
                  @if($canSetDeadline)
                    <button class="btn-action btn-action" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);" onclick="openSetDeadlineModal({{ $dokumen->id }})" title="Tetapkan Deadline">
                      <i class="fa-solid fa-clock"></i>
                    </button>
                  @endif
                @elseif($canEdit && !$isLocked)
                  {{-- Dokumen sudah terkirim ke pembayaran dan tidak terkunci - bisa diedit --}}
                  <button class="btn-action btn-edit" onclick="editDocument({{ $dokumen->id }})" title="Ubah Status">
                    <i class="fas fa-edit"></i>
                  </button>
                  @if($dokumen->status_pembayaran != 'sudah_dibayar')
                    <button class="btn-action" style="background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);" onclick="uploadBukti({{ $dokumen->id }})" title="Upload Bukti Pembayaran">
                      <i class="fa-solid fa-upload"></i>
                    </button>
                  @endif
                @else
                  {{-- Fallback: tidak bisa diedit --}}
                  <span class="badge-status badge-proses" style="font-size: 10px; padding: 4px 8px;">
                    <i class="fa-solid fa-eye me-1"></i>Hanya Lihat
                  </span>
                @endif
              </div>
            </td>
          </tr>
          <tr class="detail-row" id="detail-{{ $dokumen->id }}">
            <td colspan="9">
              <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                <div class="text-center p-4">
                  <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-5">
              <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
              <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Edit Status -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
        <h5 class="modal-title">
          <i class="fas fa-edit me-2"></i>Ubah Status Pembayaran
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editStatusDocId">
        
        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(23, 162, 184, 0.12) 0%, rgba(19, 132, 150, 0.12) 100%); border-left: 4px solid #17a2b8;">
          <i class="fa-solid fa-info-circle me-2"></i>
          Pilih status pembayaran untuk dokumen ini.
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Status Pembayaran*</label>
          <select class="form-select" id="statusPembayaran" required>
            <option value="">Pilih status</option>
            <option value="siap_dibayar">Siap Dibayar</option>
            <option value="sudah_dibayar">Sudah Dibayar</option>
          </select>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-primary" onclick="confirmUpdateStatus()">
          <i class="fa-solid fa-check me-2"></i>Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Upload Bukti Pembayaran -->
<div class="modal fade" id="uploadBuktiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #34ce57 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-upload me-2"></i>Upload Link Bukti Pembayaran
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="uploadBuktiDocId">
        
        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.12) 0%, rgba(52, 206, 87, 0.12) 100%); border-left: 4px solid #28a745;">
          <i class="fa-solid fa-info-circle me-2"></i>
          Masukkan link bukti pembayaran (contoh: link Google Drive, Dropbox, atau URL lainnya).
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Link Bukti Pembayaran*</label>
          <input type="url" class="form-control" id="linkBuktiPembayaran" placeholder="https://drive.google.com/..." required>
          <small class="text-muted">Pastikan link dapat diakses dan tidak memerlukan izin khusus.</small>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success" onclick="confirmUploadBukti()">
          <i class="fa-solid fa-check me-2"></i>Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Set Deadline -->
<div class="modal fade" id="setDeadlineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-clock me-2"></i>Tetapkan Timeline Pembayaran
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="deadlineDocId">

        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.12) 0%, rgba(255, 140, 0, 0.12) 100%); border-left: 4px solid #ffc107;">
          <i class="fa-solid fa-info-circle me-2"></i>
          Dokumen akan tetap terkunci sampai timeline ditetapkan. Setelah dibuka, dokumen dapat diproses untuk pembayaran.
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
          <textarea class="form-control" id="deadlineNote" rows="3" maxlength="500" placeholder="Contoh: Menunggu dana tersedia"></textarea>
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

  fetch(`/documents/pembayaran/${docId}/detail`)
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
  document.getElementById('editStatusDocId').value = id;
  // Reset form
  document.getElementById('statusPembayaran').value = '';
  const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
  modal.show();
}

function uploadBukti(id) {
  document.getElementById('uploadBuktiDocId').value = id;
  document.getElementById('linkBuktiPembayaran').value = '';
  const modal = new bootstrap.Modal(document.getElementById('uploadBuktiModal'));
  modal.show();
}

function confirmUpdateStatus() {
  const docId = document.getElementById('editStatusDocId').value;
  const statusPembayaran = document.getElementById('statusPembayaran').value;

  if (!statusPembayaran) {
    alert('Pilih status pembayaran terlebih dahulu!');
    return;
  }

  const submitBtn = document.querySelector('#editStatusModal .btn-primary');
  const originalHTML = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';

  fetch(`/documents/pembayaran/${docId}/update-status`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      status_pembayaran: statusPembayaran
    })
  })
  .then(async response => {
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      return response.json();
    } else {
      const text = await response.text();
      throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}`);
    }
  })
  .then(data => {
    if (data.success) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('editStatusModal'));
      modal.hide();
      alert('Status pembayaran berhasil diperbarui!');
      location.reload();
    } else {
      alert(data.message || 'Gagal memperbarui status pembayaran.');
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat memperbarui status pembayaran: ' + (error.message || 'Unknown error'));
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
  });
}

function confirmUploadBukti() {
  const docId = document.getElementById('uploadBuktiDocId').value;
  const linkBukti = document.getElementById('linkBuktiPembayaran').value.trim();

  if (!linkBukti) {
    alert('Masukkan link bukti pembayaran terlebih dahulu!');
    return;
  }

  // Basic URL validation
  try {
    new URL(linkBukti);
  } catch (e) {
    alert('Format link tidak valid. Pastikan link dimulai dengan http:// atau https://');
    return;
  }

  const submitBtn = document.querySelector('#uploadBuktiModal .btn-success');
  const originalHTML = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';

  fetch(`/dokumensPembayaran/${docId}/upload-bukti`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      link_bukti_pembayaran: linkBukti
    })
  })
  .then(async response => {
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      return response.json();
    } else {
      const text = await response.text();
      throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}`);
    }
  })
  .then(data => {
    if (data.success) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('uploadBuktiModal'));
      modal.hide();
      alert('Link bukti pembayaran berhasil disimpan!');
      location.reload();
    } else {
      alert(data.message || 'Gagal menyimpan link bukti pembayaran.');
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat menyimpan link bukti pembayaran: ' + (error.message || 'Unknown error'));
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
  });
}

// Search functionality
document.getElementById('pembayaranSearchInput').addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const allRows = document.querySelectorAll('.table-enhanced tbody tr');

  allRows.forEach(row => {
    if (row.classList.contains('detail-row')) {
      return;
    }

    const text = row.textContent.toLowerCase();
    if (text.includes(searchTerm)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
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

  fetch(`/dokumensPembayaran/${docId}/set-deadline`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      deadline_days: parseInt(deadlineDays, 10),
      deadline_note: deadlineNote
    })
  })
  .then(async response => {
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      return response.json();
    } else {
      const text = await response.text();
      throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}`);
    }
  })
  .then(data => {
    if (data.success) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('setDeadlineModal'));
      modal.hide();
      alert('Deadline berhasil ditetapkan! Dokumen kini siap untuk diproses.');
      location.reload();
    } else {
      alert(data.message || 'Gagal menetapkan deadline.');
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat menetapkan deadline: ' + (error.message || 'Unknown error'));
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
  });
}
</script>

@endsection

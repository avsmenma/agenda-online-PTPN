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

  #viewDocumentModal.modal {
    padding-right: 0 !important;
    padding-left: 0 !important;
  }

  #viewDocumentModal .modal-dialog.modal-xl {
    max-width: 90% !important;
    width: 90% !important;
    margin: 1rem auto !important;
    padding: 0 !important;
  }

  #viewDocumentModal.show .modal-dialog {
    transform: none !important;
  }

  #viewDocumentModal .modal-content {
    border-radius: 0 !important;
  }

  @media (max-width: 992px) {
    #viewDocumentModal .modal-dialog.modal-xl {
      max-width: 95% !important;
      width: 95% !important;
      margin: 0.5rem auto !important;
    }
  }

  @media (max-width: 768px) {
    #viewDocumentModal .modal-dialog.modal-xl {
      max-width: 98% !important;
      width: 98% !important;
      margin: 0.25rem auto !important;
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
          
          // Gunakan computed_status untuk menentukan apakah dokumen "belum siap bayar"
          $computedStatus = $dokumen->computed_status ?? 'belum_siap_bayar';
          $isBelumSiapBayar = ($computedStatus === 'belum_siap_bayar');
          
          // Dokumen bisa diklik jika statusnya 'siap_bayar' atau 'sudah_dibayar'
          $canClick = in_array($computedStatus, ['siap_bayar', 'sudah_dibayar']);
          
          // Cek apakah dokumen sudah terkirim ke pembayaran (bisa diedit)
          $isSentToPembayaran = $dokumen->status === 'sent_to_pembayaran' || $dokumen->current_handler === 'pembayaran';
          
          // Use DokumenHelper to check lock status
          $isLocked = \App\Helpers\DokumenHelper::isDocumentLocked($dokumen);
          $canSetDeadline = \App\Helpers\DokumenHelper::canSetDeadline($dokumen)['can_set'];
          $canEdit = \App\Helpers\DokumenHelper::canEditDocument($dokumen, 'pembayaran');
        @endphp
        <tr class="main-row {{ $isLocked ? 'locked-row' : '' }}" 
            @if($canClick)
              onclick="if(typeof window.openDocumentDetailModal === 'function') { window.openDocumentDetailModal({{ $dokumen->id }}, event); }" 
              style="cursor: pointer;"
              title="Klik untuk melihat detail lengkap dokumen"
            @else
              style="cursor: default;"
              title="Dokumen belum siap bayar. Klik icon mata untuk melihat tracking."
            @endif>
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
                @if($isBelumSiapBayar)
                  {{-- Dokumen belum siap bayar - hanya bisa lihat tracking, tidak bisa lihat detail --}}
                  <a href="{{ route('owner.workflow', $dokumen->id) }}" 
                     target="_blank"
                     class="btn-action workflow-link"
                     title="Lihat Tracking Workflow Dokumen"
                     data-workflow-id="{{ $dokumen->id }}"
                     style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-eye"></i>
                    <span style="font-size: 12px;">Tracking</span>
                  </a>
                @elseif($isBelumSiap)
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

<!-- Modal View Document Detail -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="max-width: 90%; width: 90%;">
    <div class="modal-content" style="height: 95vh; display: flex; flex-direction: column;">
      <!-- Sticky Header -->
      <div class="modal-header" style="position: sticky; top: 0; z-index: 1050; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-bottom: none; flex-shrink: 0;">
        <h5 class="modal-title" id="viewDocumentModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
          <i class="fa-solid fa-file-lines me-2"></i>
          Detail Dokumen Lengkap
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Scrollable Body -->
      <div class="modal-body" style="overflow-y: auto; max-height: calc(95vh - 140px); padding: 30px; flex: 1;">
        <input type="hidden" id="view-dokumen-id">
        
        <!-- Loading State -->
        <div id="view-loading" style="display: none; text-align: center; padding: 40px;">
          <i class="fas fa-spinner fa-spin fa-3x mb-3" style="color: #083E40;"></i>
          <p class="text-muted">Memuat data dokumen...</p>
        </div>
        
        <!-- Error State -->
        <div id="view-error" style="display: none; background: #fee; border: 1px solid #fcc; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
          <div class="d-flex align-items-center gap-2 text-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span></span>
          </div>
        </div>
        
        <!-- Content -->
        <div id="view-content" style="display: none;">
          <!-- Section 1: Identitas Dokumen -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                <i class="fa-solid fa-id-card me-2"></i>IDENTITAS DOKUMEN
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <div><strong>Nomor Agenda:</strong> <span id="view-nomor-agenda">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Nomor SPP:</strong> <span id="view-nomor-spp">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Tanggal SPP:</strong> <span id="view-tanggal-spp">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Bulan:</strong> <span id="view-bulan">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Tahun:</strong> <span id="view-tahun">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Tanggal Masuk:</strong> <span id="view-tanggal-masuk">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Kriteria CF:</strong> <span id="view-kategori">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Sub Kriteria:</strong> <span id="view-jenis-dokumen">-</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Item Sub Kriteria:</strong> <span id="view-jenis-sub-pekerjaan">-</span></div>
              </div>
            </div>
          </div>

          <!-- Section 2: Detail Keuangan & Vendor -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                <i class="fa-solid fa-money-bill-wave me-2"></i>DETAIL KEUANGAN & VENDOR
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-12">
                <div><strong>Uraian SPP:</strong> <span id="view-uraian-spp" style="white-space: pre-wrap;">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Nilai Rupiah:</strong> <span id="view-nilai-rupiah" style="font-weight: 700; color: #083E40;">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Ejaan Nilai Rupiah:</strong> <span id="view-ejaan-nilai-rupiah" style="font-style: italic; color: #666;">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Dibayar Kepada:</strong> <span id="view-dibayar-kepada">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Kebun:</strong> <span id="view-kebun">-</span></div>
              </div>
            </div>
          </div>

          <!-- Section 3: Referensi Pendukung -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                <i class="fa-solid fa-file-contract me-2"></i>REFERENSI PENDUKUNG
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-3">
                <div><strong>No. SPK:</strong> <span id="view-no-spk">-</span></div>
              </div>
              <div class="col-md-3">
                <div><strong>Tanggal SPK:</strong> <span id="view-tanggal-spk">-</span></div>
              </div>
              <div class="col-md-3">
                <div><strong>Tanggal Berakhir SPK:</strong> <span id="view-tanggal-berakhir-spk">-</span></div>
              </div>
              <div class="col-md-3">
                <div><strong>No. Mirror:</strong> <span id="view-nomor-mirror">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>No. Berita Acara:</strong> <span id="view-no-berita-acara">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Tanggal Berita Acara:</strong> <span id="view-tanggal-berita-acara">-</span></div>
              </div>
            </div>
          </div>

          <!-- Section 4: Nomor PO & PR -->
          <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                <i class="fa-solid fa-hashtag me-2"></i>NOMOR PO & PR
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <div><strong>Nomor PO:</strong> <span id="view-nomor-po">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Nomor PR:</strong> <span id="view-nomor-pr">-</span></div>
              </div>
            </div>
          </div>

          <!-- Section 5: Informasi Perpajakan -->
          <div class="form-section mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 12px; padding: 20px; border: 2px solid #ffc107;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #92400e; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                <i class="fa-solid fa-file-invoice-dollar me-2"></i>INFORMASI PERPAJAKAN
                <span style="background: #ffc107; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px;">KHUSUS PERPAJAKAN</span>
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <div><strong>NPWP:</strong> <span id="view-npwp">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Status Perpajakan:</strong> <span id="view-status-perpajakan">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>No. Faktur:</strong> <span id="view-no-faktur">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Tanggal Faktur:</strong> <span id="view-tanggal-faktur">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Jenis PPH:</strong> <span id="view-jenis-pph">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>DPP PPH:</strong> <span id="view-dpp-pph">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>PPN Terhutang:</strong> <span id="view-ppn-terhutang">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Link Dokumen Pajak:</strong> <span id="view-link-dokumen-pajak">-</span></div>
              </div>
            </div>
          </div>

          <!-- Section 6: Data Pembayaran -->
          <div class="form-section mb-4" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 12px; padding: 20px; border: 2px solid #28a745;">
            <div class="section-header mb-3">
              <h6 class="section-title" style="color: #155724; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0;">
                <i class="fa-solid fa-money-bill-wave me-2"></i>DATA PEMBAYARAN
                <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px;">DITAMBAHKAN OLEH TEAM PEMBAYARAN</span>
              </h6>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <div><strong>Status Pembayaran:</strong> <span id="view-status-pembayaran">-</span></div>
              </div>
              <div class="col-md-6">
                <div><strong>Tanggal Dibayar:</strong> <span id="view-tanggal-dibayar">-</span></div>
              </div>
              <div class="col-12">
                <div><strong>Link Bukti Pembayaran:</strong> <span id="view-link-bukti-pembayaran">-</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Modal Footer -->
      <div class="modal-footer" style="flex-shrink: 0; border-top: 1px solid #e9ecef;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Global function to open document detail modal
window.openDocumentDetailModal = function(dokumenId, event) {
  // Prevent default navigation
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  
  // Show loading state
  const loadingEl = document.getElementById('view-loading');
  const errorEl = document.getElementById('view-error');
  const contentEl = document.getElementById('view-content');
  
  if (loadingEl) loadingEl.style.display = 'block';
  if (errorEl) errorEl.style.display = 'none';
  if (contentEl) contentEl.style.display = 'none';
  
  // Fetch document detail
  fetch(`/documents/pembayaran/${dokumenId}/detail`, {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    },
    credentials: 'same-origin'
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(err => {
        throw new Error(err.message || 'Gagal memuat data dokumen. Status: ' + response.status);
      });
    }
    return response.json();
  })
  .then(result => {
    if (result.success && result.data) {
      populateDocumentDetail(result.data);
      if (loadingEl) loadingEl.style.display = 'none';
      if (contentEl) contentEl.style.display = 'block';
      
      // Show Bootstrap modal
      const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
      modal.show();
    } else {
      throw new Error(result.message || 'Data tidak ditemukan');
    }
  })
  .catch(error => {
    console.error('Error loading document:', error);
    if (loadingEl) loadingEl.style.display = 'none';
    if (errorEl) {
      errorEl.style.display = 'block';
      const errorText = errorEl.querySelector('span');
      if (errorText) errorText.textContent = error.message || 'Terjadi kesalahan saat memuat data dokumen';
    }
    
    // Show modal even with error
    const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
    modal.show();
  });
  
  return false;
};

// Function to convert number to Indonesian terbilang
function terbilangRupiah(number) {
  number = parseFloat(number) || 0;
  
  if (number == 0) {
    return 'nol rupiah';
  }

  const angka = [
    '', 'satu', 'dua', 'tiga', 'empat', 'lima',
    'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
    'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
    'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
  ];

  let hasil = '';

  // Handle triliun
  if (number >= 1000000000000) {
    const triliun = Math.floor(number / 1000000000000);
    hasil += terbilangSatuan(triliun, angka) + ' triliun ';
    number = number % 1000000000000;
  }

  // Handle milyar
  if (number >= 1000000000) {
    const milyar = Math.floor(number / 1000000000);
    hasil += terbilangSatuan(milyar, angka) + ' milyar ';
    number = number % 1000000000;
  }

  // Handle juta
  if (number >= 1000000) {
    const juta = Math.floor(number / 1000000);
    hasil += terbilangSatuan(juta, angka) + ' juta ';
    number = number % 1000000;
  }

  // Handle ribu
  if (number >= 1000) {
    const ribu = Math.floor(number / 1000);
    if (ribu == 1) {
      hasil += 'seribu ';
    } else {
      hasil += terbilangSatuan(ribu, angka) + ' ribu ';
    }
    number = number % 1000;
  }

  // Handle ratusan, puluhan, dan satuan
  if (number > 0) {
    hasil += terbilangSatuan(number, angka);
  }

  return hasil.trim() + ' rupiah';
}

function terbilangSatuan(number, angka) {
  let hasil = '';
  number = parseInt(number);

  if (number == 0) {
    return '';
  }

  // Handle ratusan
  if (number >= 100) {
    const ratus = Math.floor(number / 100);
    if (ratus == 1) {
      hasil += 'seratus ';
    } else {
      hasil += angka[ratus] + ' ratus ';
    }
    number = number % 100;
  }

  // Handle puluhan dan satuan (0-99)
  if (number > 0) {
    if (number < 20) {
      hasil += angka[number] + ' ';
    } else {
      const puluhan = Math.floor(number / 10);
      const satuan = number % 10;
      
      if (puluhan == 1) {
        hasil += angka[10 + satuan] + ' ';
      } else {
        hasil += angka[puluhan] + ' puluh ';
        if (satuan > 0) {
          hasil += angka[satuan] + ' ';
        }
      }
    }
  }

  return hasil.trim();
}

function populateDocumentDetail(data) {
  // Helper functions
  const formatDate = (dateStr) => {
    if (!dateStr || dateStr === '-') return '-';
    if (dateStr.includes('/')) return dateStr; // Already formatted
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
  };
  
  const formatDateTime = (dateStr) => {
    if (!dateStr || dateStr === '-') return '-';
    if (dateStr.includes('/') && dateStr.includes(':')) return dateStr; // Already formatted
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { 
      day: '2-digit', 
      month: '2-digit', 
      year: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  };
  
  const formatNumber = (num) => {
    if (!num || num === '-') return '-';
    return new Intl.NumberFormat('id-ID').format(num);
  };
  
  // Identitas Dokumen
  document.getElementById('view-nomor-agenda').textContent = data.nomor_agenda || '-';
  document.getElementById('view-nomor-spp').textContent = data.nomor_spp || '-';
  document.getElementById('view-tanggal-spp').textContent = formatDate(data.tanggal_spp_date || data.tanggal_spp);
  document.getElementById('view-bulan').textContent = data.bulan || '-';
  document.getElementById('view-tahun').textContent = data.tahun || '-';
  document.getElementById('view-tanggal-masuk').textContent = formatDateTime(data.tanggal_masuk);
  document.getElementById('view-kategori').textContent = data.kategori || '-';
  document.getElementById('view-jenis-dokumen').textContent = data.jenis_dokumen || '-';
  document.getElementById('view-jenis-sub-pekerjaan').textContent = data.jenis_sub_pekerjaan || '-';
  
  // Detail Keuangan & Vendor
  document.getElementById('view-uraian-spp').textContent = data.uraian_spp || '-';
  document.getElementById('view-nilai-rupiah').textContent = data.nilai_rupiah_formatted || (data.nilai_rupiah ? 'Rp ' + formatNumber(data.nilai_rupiah) : '-');
  
  // Ejaan nilai rupiah (terbilang)
  if (data.nilai_rupiah && data.nilai_rupiah > 0 && typeof terbilangRupiah === 'function') {
    document.getElementById('view-ejaan-nilai-rupiah').textContent = terbilangRupiah(data.nilai_rupiah);
  } else {
    document.getElementById('view-ejaan-nilai-rupiah').textContent = '-';
  }
  
  document.getElementById('view-dibayar-kepada').textContent = data.dibayar_kepada || '-';
  document.getElementById('view-kebun').textContent = data.kebun || '-';
  
  // Referensi Pendukung
  document.getElementById('view-no-spk').textContent = data.no_spk || '-';
  document.getElementById('view-tanggal-spk').textContent = formatDate(data.tanggal_spk_date || data.tanggal_spk);
  document.getElementById('view-tanggal-berakhir-spk').textContent = formatDate(data.tanggal_berakhir_spk_date || data.tanggal_berakhir_spk);
  document.getElementById('view-nomor-mirror').textContent = data.nomor_mirror || '-';
  document.getElementById('view-no-berita-acara').textContent = data.no_berita_acara || '-';
  document.getElementById('view-tanggal-berita-acara').textContent = formatDate(data.tanggal_berita_acara_date || data.tanggal_berita_acara);
  
  // Nomor PO & PR
  document.getElementById('view-nomor-po').textContent = data.no_po || '-';
  document.getElementById('view-nomor-pr').textContent = data.no_pr || '-';
  
  // Informasi Perpajakan
  document.getElementById('view-npwp').textContent = data.npwp || '-';
  document.getElementById('view-status-perpajakan').textContent = data.status_perpajakan || '-';
  document.getElementById('view-no-faktur').textContent = data.no_faktur || '-';
  document.getElementById('view-tanggal-faktur').textContent = formatDate(data.tanggal_faktur_date || data.tanggal_faktur);
  document.getElementById('view-jenis-pph').textContent = data.jenis_pph || '-';
  
  // Format DPP PPH
  if (data.dpp_pph_raw && data.dpp_pph_raw > 0) {
    document.getElementById('view-dpp-pph').textContent = 'Rp ' + formatNumber(data.dpp_pph_raw);
  } else if (data.dpp_pph && data.dpp_pph !== '-') {
    document.getElementById('view-dpp-pph').textContent = 'Rp ' + data.dpp_pph;
  } else {
    document.getElementById('view-dpp-pph').textContent = '-';
  }
  
  // Format PPN Terhutang
  if (data.ppn_terhutang_raw && data.ppn_terhutang_raw > 0) {
    document.getElementById('view-ppn-terhutang').textContent = 'Rp ' + formatNumber(data.ppn_terhutang_raw);
  } else if (data.ppn_terhutang && data.ppn_terhutang !== '-') {
    document.getElementById('view-ppn-terhutang').textContent = 'Rp ' + data.ppn_terhutang;
  } else {
    document.getElementById('view-ppn-terhutang').textContent = '-';
  }
  
  // Link Dokumen Pajak
  const linkDokumenPajak = data.link_dokumen_pajak || '-';
  if (linkDokumenPajak !== '-' && linkDokumenPajak) {
    const linkEl = document.getElementById('view-link-dokumen-pajak');
    linkEl.innerHTML = `<a href="${linkDokumenPajak}" target="_blank" style="color: #0d6efd; text-decoration: underline;">${linkDokumenPajak}</a>`;
  } else {
    document.getElementById('view-link-dokumen-pajak').textContent = '-';
  }
  
  // Data Pembayaran
  const statusPembayaran = data.payment_status || data.status_pembayaran || '-';
  document.getElementById('view-status-pembayaran').textContent = statusPembayaran === 'sudah_dibayar' ? 'Sudah Dibayar' : (statusPembayaran === 'siap_bayar' ? 'Siap Dibayar' : statusPembayaran);
  document.getElementById('view-tanggal-dibayar').textContent = formatDate(data.tanggal_dibayar_date || data.tanggal_dibayar);
  
  // Link Bukti Pembayaran
  const linkBuktiPembayaran = data.link_bukti_pembayaran || '-';
  if (linkBuktiPembayaran !== '-' && linkBuktiPembayaran) {
    const linkEl = document.getElementById('view-link-bukti-pembayaran');
    linkEl.innerHTML = `<a href="${linkBuktiPembayaran}" target="_blank" style="color: #0d6efd; text-decoration: underline;">${linkBuktiPembayaran}</a>`;
  } else {
    document.getElementById('view-link-bukti-pembayaran').textContent = '-';
  }
  
  // Set document ID for edit button (if needed)
  document.getElementById('view-dokumen-id').value = data.id || '';
}

// Toggle detail row (kept for backward compatibility if needed)
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

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
      background: #ffffff;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
    }

    .search-filter-form {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .search-input-group {
      flex: 1;
      min-width: 250px;
    }

    .search-box .input-group-text {
      background: white;
      border: 1px solid #dee2e6;
      border-right: none;
      border-radius: 8px 0 0 8px;
      padding: 10px 14px;
    }

    .search-box .form-control {
      border: 1px solid #dee2e6;
      border-left: none;
      border-radius: 0 8px 8px 0;
      padding: 10px 14px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .search-box .form-control:focus {
      outline: none;
      border-color: #889717;
      box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
    }

    .btn-year-select,
    .btn-status-select {
      padding: 10px 16px;
      background: white;
      color: #495057;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      min-height: 44px;
    }

    .btn-year-select:hover,
    .btn-status-select:hover {
      border-color: #889717;
      background: #f8f9fa;
    }

    .btn-filter {
      padding: 10px 20px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
      min-height: 44px;
    }

    .btn-filter:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .table-container {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .table-wrapper {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }

    /* Header tabel hijau solid seperti gambar */
    .data-table thead {
      background: #083E40;
    }

    .data-table th {
      padding: 16px 12px;
      color: white;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      letter-spacing: 0.5px;
      white-space: nowrap;
      border-right: 1px solid rgba(255, 255, 255, 0.15);
    }

    .data-table th:last-child {
      border-right: none;
    }

    .data-table td {
      padding: 14px 12px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(8, 62, 64, 0.05);
      border-right: 1px solid #e9ecef;
      font-size: 13px;
      text-align: center;
    }

    .data-table td:last-child {
      border-right: none;
    }

    .data-table tbody tr {
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .data-table tbody tr:hover {
      background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
    }

    .badge-draft {
      background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
      color: white;
    }

    .badge-terkirim {
      background: linear-gradient(135deg, #28a745 0%, #34c759 100%);
      color: white;
    }

    .badge-selesai {
      background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
      color: white;
    }

    /* Action Buttons - Horizontal Layout */
    .action-buttons {
      display: flex;
      gap: 6px;
      justify-content: center;
      align-items: center;
      flex-wrap: nowrap;
    }

    .btn-action {
      width: 36px;
      height: 36px;
      padding: 0;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-edit {
      background: #083E40;
      color: white;
    }

    .btn-edit:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-send {
      background: #083E40;
      color: white;
    }

    .btn-send:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-tracking {
      background: #083E40;
      color: white;
    }

    .btn-tracking:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-delete {
      background: #dc3545;
      color: white;
    }

    .btn-delete:hover {
      background: #c82333;
      color: white;
    }

    .btn-create {
      padding: 12px 24px;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.25);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-create:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(8, 62, 64, 0.35);
      color: white;
    }

    .pagination-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 20px;
      border-top: 1px solid #e9ecef;
      flex-wrap: wrap;
      gap: 16px;
    }

    .per-page-select {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .per-page-select label {
      font-size: 14px;
      color: #495057;
    }

    .per-page-select select {
      padding: 6px 12px;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      font-size: 14px;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-state i {
      font-size: 80px;
      color: #dee2e6;
      margin-bottom: 20px;
    }

    .empty-state h4 {
      color: #6c757d;
      margin-bottom: 10px;
    }

    .empty-state p {
      color: #adb5bd;
      margin-bottom: 20px;
    }

    /* Modal Popup */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      padding: 20px;
    }

    .modal-overlay.show {
      display: flex;
    }

    .modal-content-custom {
      background: white;
      border-radius: 20px;
      max-width: 90%;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
    }

    .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 20px 24px;
      border-radius: 16px 16px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header-custom h4 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .modal-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .modal-body-custom {
      padding: 24px;
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .detail-item {
      background: #f8f9fa;
      padding: 14px;
      border-radius: 10px;
      border-left: 4px solid #083E40;
    }

    .detail-item.full-width {
      grid-column: span 2;
    }

    .detail-label {
      font-size: 11px;
      font-weight: 700;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .detail-value {
      font-size: 14px;
      font-weight: 600;
      color: #212529;
    }

    .detail-value.highlight {
      color: #212529;
      font-size: 18px;
    }

    .modal-footer-custom {
      padding: 16px 24px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    @media (max-width: 768px) {
      .detail-grid {
        grid-template-columns: 1fr;
      }

      .detail-item.full-width {
        grid-column: span 1;
      }

      .action-buttons {
        flex-wrap: wrap;
      }
    }
  </style>

  <div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2>
          <i class="fa-solid fa-file-lines me-2"></i>
          Daftar Dokumen Bagian {{ $bagianCode }}
        </h2>
        <p class="text-muted mb-0">{{ $bagianName }}</p>
      </div>
      <a href="{{ route('bagian.documents.create') }}" class="btn-create">
        <i class="fa-solid fa-plus"></i>
        Buat Dokumen
      </a>
    </div>


    <!-- Search & Filter -->
    <div class="search-box">
      <form action="{{ route('bagian.documents.index') }}" method="GET" class="search-filter-form">
        <div class="search-input-group">
          <div class="input-group">
            <span class="input-group-text">
              <i class="fa-solid fa-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control" placeholder="Cari nomor agenda, SPP, atau uraian..."
              value="{{ request('search') }}">
          </div>
        </div>

        <select name="tahun" class="btn-year-select">
          <option value="">Semua Tahun</option>
          @php
            $currentYear = date('Y');
            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
              $selected = request('tahun') == $y ? 'selected' : '';
              echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
            }
          @endphp
        </select>

        <select name="status" class="btn-status-select">
          <option value="">Semua Status</option>
          <option value="belum dikirim" {{ request('status') == 'belum dikirim' ? 'selected' : '' }}>Belum Dikirim
          </option>
          <option value="sent_to_team_verifikasi" {{ request('status') == 'sent_to_team_verifikasi' ? 'selected' : '' }}>
            Menunggu Verifikasi
          </option>
          <option value="sudah dibayar" {{ request('status') == 'sudah dibayar' ? 'selected' : '' }}>Sudah Dibayar
          </option>
        </select>

        <button type="submit" class="btn-filter">
          <i class="fa-solid fa-filter me-1"></i>Filter
        </button>
      </form>
    </div>

    <!-- Document Table -->
    <div class="table-container">
      @if($dokumens->count() > 0)
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nomor Agenda</th>
                <th>Nomor SPP</th>
                <th>Tanggal Masuk</th>
                <th>Nilai Rupiah</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($dokumens as $index => $doc)
                      @php
                        $statusLower = strtolower($doc->status ?? '');
                      @endphp
                      <tr onclick="showDocumentDetail({{ json_encode([
                  'id' => $doc->id,
                  'nomor_agenda' => $doc->nomor_agenda,
                  'nomor_spp' => $doc->nomor_spp,
                  'tanggal_spp' => $doc->tanggal_spp ? $doc->tanggal_spp->format('d/m/Y H:i') : '-',
                  'tanggal_masuk' => $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d/m/Y H:i') : '-',
                  'bulan' => $doc->bulan ?? '-',
                  'tahun' => $doc->tahun ?? '-',
                  'nilai_rupiah' => 'Rp. ' . number_format($doc->nilai_rupiah, 0, ',', '.'),
                  'ejaan_nilai_rupiah' => \App\Helpers\TerbilangHelper::terbilang($doc->nilai_rupiah),
                  'uraian_spp' => $doc->uraian_spp ?? '-',
                  'bagian' => $doc->bagian ?? '-',
                  'nama_pengirim' => $doc->nama_pengirim ?? '-',
                  'kebun' => $doc->kebun ?? '-',
                  'no_spk' => $doc->no_spk ?? '-',
                  'tanggal_spk' => $doc->tanggal_spk ? $doc->tanggal_spk->format('d/m/Y') : '-',
                  'tanggal_berakhir_spk' => $doc->tanggal_berakhir_spk ? $doc->tanggal_berakhir_spk->format('d/m/Y') : '-',
                  'no_berita_acara' => $doc->no_berita_acara ?? '-',
                  'tanggal_berita_acara' => $doc->tanggal_berita_acara ? $doc->tanggal_berita_acara->format('d/m/Y') : '-',
                  'no_po' => $doc->NO_PO ?? '-',
                  'no_miro' => $doc->nomor_miro_display ?? '-',
                  'kriteria_cf' => $doc->kategori ?? '-',
                  'sub_kriteria' => $doc->jenis_dokumen ?? '-',
                  'item_sub_kriteria' => $doc->jenis_sub_pekerjaan ?? '-',
                  'jenis_pembayaran' => $doc->jenis_pembayaran ?? '-',
                  'dibayar_kepada' => $doc->dibayarKepadas->pluck('nama_penerima')->join(', ') ?: '-',
                  'status' => ucwords(str_replace('_', ' ', $doc->status ?? 'Belum Dikirim'))
                ]) }})">
                        <td>{{ $dokumens->firstItem() + $index }}</td>
                        <td>
                          <strong style="color: #083E40;">{{ $doc->nomor_agenda }}</strong>
                          <br>
                          <small class="text-muted">{{ $doc->bulan ?? '' }} {{ $doc->tahun ?? '' }}</small>
                        </td>
                        <td>{{ $doc->nomor_spp }}</td>
                        <td>{{ $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d-m-Y H:i') : '-' }}</td>
                        <td>
                          <strong style="color: #28a745;">Rp. {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                        </td>
                        @php
                          // Simplified status for Bagian view
                          // Only 3 statuses: Belum Dikirim, Menunggu Approve, Terkirim
                          $displayStatus = 'terkirim'; // Default to Terkirim
                          $statusClass = 'badge-terkirim';
                          $statusIcon = 'fa-check';
                          $statusText = 'Terkirim';

                          if ($statusLower == 'belum dikirim') {
                            $displayStatus = 'belum_dikirim';
                            $statusClass = 'badge-draft';
                            $statusIcon = 'fa-file-lines';
                            $statusText = 'Belum Dikirim';
                          } elseif ($statusLower == 'menunggu_approval_keuangan') {
                            $displayStatus = 'menunggu_approve';
                            $statusClass = 'badge-warning';
                            $statusIcon = 'fa-clock';
                            $statusText = 'Menunggu Approve';
                          }
                          // All other statuses = Terkirim (document has been processed by Operator)
                        @endphp

                        @if($displayStatus == 'belum_dikirim')
                          <span class="badge-status {{ $statusClass }}">
                            <i class="fa-solid {{ $statusIcon }}"></i>
                            <span>{{ $statusText }}</span>
                          </span>
                        @elseif($displayStatus == 'menunggu_approve')
                          <span class="badge-status {{ $statusClass }}"
                            style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529;">
                            <i class="fa-solid {{ $statusIcon }}"></i>
                            <span>{{ $statusText }}</span>
                          </span>
                        @else
                          <span class="badge-status {{ $statusClass }}">
                            <i class="fa-solid {{ $statusIcon }}"></i>
                            <span>{{ $statusText }}</span>
                          </span>
                        @endif
                        </td>
                        <td onclick="event.stopPropagation()">
                          <div class="action-buttons">
                            @if($statusLower == 'belum dikirim')
                              <a href="{{ route('bagian.documents.edit', $doc) }}" class="btn-action btn-edit" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                              </a>
                              <form id="sendForm-{{ $doc->id }}" action="{{ route('bagian.documents.send-to-Operator', $doc) }}"
                                method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn-action btn-send" title="Kirim"
                                  onclick="showSendModal({{ $doc->id }})">
                                  <i class="fa-solid fa-paper-plane"></i>
                                </button>
                              </form>
                              <form id="deleteForm-{{ $doc->id }}" action="{{ route('bagian.documents.destroy', $doc) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-action btn-delete" title="Hapus"
                                  onclick="showDeleteModal({{ $doc->id }})">
                                  <i class="fa-solid fa-trash"></i>
                                </button>
                              </form>
                            @else
                              <a href="{{ route('owner.workflow', $doc->id) }}" class="btn-action btn-tracking" title="Tracking">
                                <i class="fa-solid fa-route"></i>
                              </a>
                            @endif
                          </div>
                        </td>
                      </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
          <div class="per-page-select">
            <label>Baris per halaman:</label>
            <select onchange="changePerPage(this.value)">
              <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
              <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
              <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
              <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-muted">
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} hasil
            </span>
          </div>
          <div>
            {{ $dokumens->appends(request()->query())->links() }}
          </div>
        </div>
      @else
        <div class="empty-state">
          <i class="fa-solid fa-folder-open"></i>
          <h4>Belum ada dokumen</h4>
          <p>Buat dokumen pertama Anda sekarang</p>
          <a href="{{ route('bagian.documents.create') }}" class="btn-create">
            <i class="fa-solid fa-plus"></i>
            Buat Dokumen
          </a>
        </div>
      @endif
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-modal">
      <div class="confirm-icon delete-icon">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <h3 class="confirm-title">Hapus Dokumen?</h3>
      <p class="confirm-message">Apakah anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.</p>
      <div class="confirm-actions">
        <button type="button" class="btn-confirm-cancel" onclick="closeDeleteModal()">
          <i class="fa-solid fa-times"></i> Batal
        </button>
        <button type="button" class="btn-confirm-delete" id="confirmDeleteBtn">
          <i class="fa-solid fa-trash"></i> Ya, Hapus
        </button>
      </div>
    </div>
  </div>

  <!-- Send Confirmation Modal -->
  <div id="sendConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-modal send-modal">
      <div class="confirm-icon send-icon">
        <i class="fa-solid fa-paper-plane"></i>
      </div>
      <h3 class="confirm-title">Kirim Dokumen?</h3>
      <p class="confirm-message">Apakah anda yakin dokumen ini dikirim ke Bidang Keuangan dan Akutansi?</p>
      <div class="confirm-actions">
        <button type="button" class="btn-confirm-cancel" onclick="closeSendModal()">
          <i class="fa-solid fa-times"></i> Batal
        </button>
        <button type="button" class="btn-confirm-send" id="confirmSendBtn">
          <i class="fa-solid fa-paper-plane"></i> Ya, Kirim
        </button>
      </div>
    </div>
  </div>

  <!-- Send Success Modal -->
  <div id="sendSuccessModal" class="success-modal-overlay">
    <div class="success-modal">
      <div class="success-icon-container">
        <div class="success-circle">
          <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
          </svg>
        </div>
        <div class="confetti">
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
        </div>
      </div>
      <h2 class="success-title">Berhasil Terkirim!</h2>
      <div class="success-details">
        <div class="success-stat">
          <span class="stat-number">1</span>
          <span class="stat-label">Dokumen</span>
        </div>
        <div class="success-arrow">
          <i class="fa-solid fa-arrow-right"></i>
        </div>
        <div class="success-destination">
          <i class="fa-solid fa-inbox"></i>
          <span>Bidang Keuangan</span>
        </div>
      </div>
      <p class="success-message">
        <i class="fa-solid fa-info-circle"></i>
        Dokumen telah masuk ke <strong>inbox</strong> dan menunggu persetujuan
      </p>
      <button type="button" class="btn-success-close" onclick="closeSuccessAndReload()">
        <i class="fa-solid fa-check"></i> Mengerti
      </button>
    </div>
  </div>

  <!-- Document Detail Modal - Modern Redesign -->
  <div class="modal-overlay" id="documentDetailModal">
    <div class="modal-content-custom">
      <!-- Hero Header with Status -->
      <div class="modal-header-custom">
        <div class="header-content">
          <div class="header-icon">
            <i class="fa-solid fa-file-invoice"></i>
          </div>
          <div class="header-text">
            <h4>Detail Dokumen Lengkap</h4>
            <span class="doc-id" id="modal-header-agenda">-</span>
          </div>
        </div>
        <div class="header-actions">
          <span class="status-pill" id="modal-header-status">-</span>
          <button class="modal-close" onclick="closeModal()">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="modal-tabs">
        <button class="tab-btn active" onclick="switchTab('info')" data-tab="info">
          <i class="fa-solid fa-info-circle"></i>
          <span>Info Utama</span>
        </button>
        <button class="tab-btn" onclick="switchTab('keuangan')" data-tab="keuangan">
          <i class="fa-solid fa-wallet"></i>
          <span>Keuangan & Vendor</span>
        </button>
        <button class="tab-btn" onclick="switchTab('spk')" data-tab="spk">
          <i class="fa-solid fa-file-contract"></i>
          <span>SPK & Berita Acara</span>
        </button>
      </div>

      <div class="modal-body-custom">
        <!-- Tab: Info Utama -->
        <div class="tab-content active" id="tab-info">
          <!-- Quick Stats Cards -->
          <div class="stats-row">
            <div class="stat-card primary">
              <div class="stat-icon"><i class="fa-solid fa-hashtag"></i></div>
              <div class="stat-info">
                <span class="stat-label">Nomor Agenda</span>
                <span class="stat-value" id="modal-nomor-agenda">-</span>
              </div>
            </div>
            <div class="stat-card success">
              <div class="stat-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
              <div class="stat-info">
                <span class="stat-label">Nilai Rupiah</span>
                <span class="stat-value" id="modal-nilai-rupiah">-</span>
              </div>
            </div>
            <div class="stat-card info">
              <div class="stat-icon"><i class="fa-solid fa-calendar"></i></div>
              <div class="stat-info">
                <span class="stat-label">Periode</span>
                <span class="stat-value" id="modal-periode">-</span>
              </div>
            </div>
          </div>

          <!-- Detail Sections -->
          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-file-alt"></i>
              <h5>Informasi SPP</h5>
            </div>
            <div class="section-grid">
              <div class="info-card">
                <span class="info-label">Nomor SPP</span>
                <span class="info-value" id="modal-nomor-spp">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal SPP</span>
                <span class="info-value" id="modal-tanggal-spp">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal Masuk</span>
                <span class="info-value" id="modal-tanggal-masuk">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Status</span>
                <span class="info-value status-badge" id="modal-status">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-building"></i>
              <h5>Informasi Bagian</h5>
            </div>
            <div class="section-grid cols-3">
              <div class="info-card">
                <span class="info-label">Bagian</span>
                <span class="info-value" id="modal-bagian">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Nama Pengirim</span>
                <span class="info-value" id="modal-nama-pengirim">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Kebun/Unit Kerja</span>
                <span class="info-value" id="modal-kebun">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-align-left"></i>
              <h5>Uraian SPP</h5>
            </div>
            <div class="uraian-box" id="modal-uraian-spp">-</div>
          </div>
        </div>

        <!-- Tab: Keuangan & Vendor -->
        <div class="tab-content" id="tab-keuangan">
          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-coins"></i>
              <h5>Detail Nilai</h5>
            </div>
            <div class="money-display">
              <div class="money-amount" id="modal-nilai-rupiah-2">-</div>
              <div class="money-words" id="modal-ejaan-nilai-rupiah">-</div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-store"></i>
              <h5>Informasi Vendor</h5>
            </div>
            <div class="vendor-card">
              <div class="vendor-icon">
                <i class="fa-solid fa-building"></i>
              </div>
              <div class="vendor-info">
                <span class="vendor-label">Dibayarkan Kepada</span>
                <span class="vendor-name" id="modal-dibayar-kepada">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-tags"></i>
              <h5>Kategori & Klasifikasi</h5>
            </div>
            <div class="section-grid cols-2">
              <div class="info-card highlight">
                <span class="info-label">Kriteria CF</span>
                <span class="info-value tag" id="modal-kriteria-cf">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Sub Kriteria</span>
                <span class="info-value" id="modal-sub-kriteria">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Item Sub Kriteria</span>
                <span class="info-value" id="modal-item-sub-kriteria">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Jenis Pembayaran</span>
                <span class="info-value" id="modal-jenis-pembayaran">-</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab: SPK & Berita Acara -->
        <div class="tab-content" id="tab-spk">
          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-file-signature"></i>
              <h5>Data SPK (Surat Perintah Kerja)</h5>
            </div>
            <div class="section-grid cols-3">
              <div class="info-card">
                <span class="info-label">No SPK</span>
                <span class="info-value mono" id="modal-no-spk">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal SPK</span>
                <span class="info-value" id="modal-tanggal-spk">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal Berakhir SPK</span>
                <span class="info-value" id="modal-tanggal-berakhir-spk">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-clipboard-check"></i>
              <h5>Data Berita Acara</h5>
            </div>
            <div class="section-grid cols-2">
              <div class="info-card">
                <span class="info-label">No Berita Acara</span>
                <span class="info-value mono" id="modal-no-berita-acara">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tanggal Berita Acara</span>
                <span class="info-value" id="modal-tanggal-berita-acara">-</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="section-header">
              <i class="fa-solid fa-receipt"></i>
              <h5>Data PO & MIRO</h5>
            </div>
            <div class="section-grid cols-2">
              <div class="info-card">
                <span class="info-label">No. PO</span>
                <span class="info-value mono" id="modal-no-po">-</span>
              </div>
              <div class="info-card">
                <span class="info-label">No. Miro/SES</span>
                <span class="info-value mono" id="modal-no-miro">-</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer-custom">
        <button class="btn-footer secondary" onclick="closeModal()">
          <i class="fa-solid fa-times"></i>
          <span>Tutup</span>
        </button>
      </div>
    </div>
  </div>

  <style>
    /* Modern Modal Styles */
    .modal-content-custom {
      background: #ffffff;
      border-radius: 24px;
      max-width: 900px;
      width: 95%;
      max-height: 90vh;
      overflow: hidden;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
      display: flex;
      flex-direction: column;
    }

    .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 24px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 24px 24px 0 0;
    }

    .header-content {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .header-icon {
      width: 56px;
      height: 56px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .header-text h4 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
    }

    .doc-id {
      font-size: 14px;
      opacity: 0.85;
      font-weight: 500;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .status-pill {
      background: rgba(255, 255, 255, 0.2);
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .modal-close {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: scale(1.05);
    }

    /* Tabs */
    .modal-tabs {
      display: flex;
      background: #f8f9fa;
      padding: 12px 28px;
      gap: 8px;
      border-bottom: 1px solid #e9ecef;
    }

    .tab-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
      border: none;
      background: transparent;
      border-radius: 12px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      color: #6c757d;
      transition: all 0.3s ease;
    }

    .tab-btn:hover {
      background: rgba(8, 62, 64, 0.08);
      color: #083E40;
    }

    .tab-btn.active {
      background: #083E40;
      color: white;
      box-shadow: 0 4px 15px rgba(8, 62, 64, 0.3);
    }

    .tab-btn i {
      font-size: 16px;
    }

    /* Modal Body */
    .modal-body-custom {
      padding: 28px;
      overflow-y: auto;
      flex: 1;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Stats Row */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .stat-card {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 20px;
      border-radius: 16px;
      background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
      border: 1px solid #e9ecef;
    }

    .stat-card.primary {
      background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
      border-color: #bbdefb;
    }

    .stat-card.success {
      background: linear-gradient(135deg, #e8f5e9 0%, #f8f9fa 100%);
      border-color: #c8e6c9;
    }

    .stat-card.info {
      background: linear-gradient(135deg, #fff3e0 0%, #f8f9fa 100%);
      border-color: #ffe0b2;
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }

    .stat-card.primary .stat-icon {
      background: #1976d2;
      color: white;
    }

    .stat-card.success .stat-icon {
      background: #388e3c;
      color: white;
    }

    .stat-card.info .stat-icon {
      background: #f57c00;
      color: white;
    }

    .stat-info {
      display: flex;
      flex-direction: column;
    }

    .stat-label {
      font-size: 12px;
      color: #6c757d;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .stat-value {
      font-size: 16px;
      font-weight: 700;
      color: #212529;
    }

    /* Detail Sections */
    .detail-section {
      margin-bottom: 24px;
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e9ecef;
    }

    .section-header i {
      width: 32px;
      height: 32px;
      background: #083E40;
      color: white;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .section-header h5 {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: #212529;
    }

    .section-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .section-grid.cols-3 {
      grid-template-columns: repeat(3, 1fr);
    }

    .section-grid.cols-2 {
      grid-template-columns: repeat(2, 1fr);
    }

    .info-card {
      background: #f8f9fa;
      padding: 16px;
      border-radius: 12px;
      border-left: 4px solid #083E40;
    }

    .info-card.highlight {
      background: linear-gradient(135deg, #e8f5e9 0%, #f8f9fa 100%);
      border-left-color: #28a745;
    }

    .info-label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .info-value {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #212529;
    }

    .info-value.mono {
      font-family: 'Consolas', 'Monaco', monospace;
      background: #e9ecef;
      padding: 4px 8px;
      border-radius: 6px;
      display: inline-block;
    }

    .info-value.tag {
      background: #083E40;
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
    }

    .info-value.status-badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      background: linear-gradient(135deg, #28a745 0%, #34c759 100%);
      color: white;
    }

    /* Uraian Box */
    .uraian-box {
      background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 20px;
      font-size: 14px;
      line-height: 1.7;
      color: #495057;
      min-height: 80px;
    }

    /* Money Display */
    .money-display {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-radius: 16px;
      padding: 28px;
      text-align: center;
      color: white;
    }

    .money-amount {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .money-words {
      font-size: 14px;
      opacity: 0.9;
      font-style: italic;
    }

    /* Vendor Card */
    .vendor-card {
      display: flex;
      align-items: center;
      gap: 20px;
      background: #f8f9fa;
      border-radius: 16px;
      padding: 24px;
      border: 1px solid #e9ecef;
    }

    .vendor-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
    }

    .vendor-info {
      display: flex;
      flex-direction: column;
    }

    .vendor-label {
      font-size: 12px;
      color: #6c757d;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .vendor-name {
      font-size: 18px;
      font-weight: 700;
      color: #212529;
    }

    /* Footer */
    .modal-footer-custom {
      padding: 20px 28px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      background: #f8f9fa;
    }

    .btn-footer {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-footer.secondary {
      background: #6c757d;
      color: white;
    }

    .btn-footer.secondary:hover {
      background: #5a6268;
      transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .stats-row {
        grid-template-columns: 1fr;
      }

      .section-grid,
      .section-grid.cols-2,
      .section-grid.cols-3 {
        grid-template-columns: 1fr;
      }

      .modal-tabs {
        overflow-x: auto;
        padding: 12px 16px;
      }

      .tab-btn span {
        display: none;
      }

      .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .modal-header-custom {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
      }

      .stat-card {
        flex-direction: column;
        text-align: center;
      }
    }

    /* Confirmation Modal Styles */
    .confirm-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .confirm-modal-overlay.show {
      display: flex;
      opacity: 1;
    }

    .confirm-modal {
      background: white;
      border-radius: 16px;
      padding: 32px;
      text-align: center;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from {
        transform: scale(0.9) translateY(-20px);
        opacity: 0;
      }

      to {
        transform: scale(1) translateY(0);
        opacity: 1;
      }
    }

    .confirm-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 28px;
    }

    .delete-icon {
      background: linear-gradient(135deg, #fce4ec 0%, #ffcdd2 100%);
      color: #e53935;
    }

    .send-icon {
      background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
      color: #1976d2;
    }

    .confirm-title {
      font-size: 22px;
      font-weight: 700;
      color: #1f2937;
      margin: 0 0 12px 0;
    }

    .confirm-message {
      font-size: 14px;
      color: #6b7280;
      margin: 0 0 24px 0;
      line-height: 1.5;
    }

    .confirm-actions {
      display: flex;
      gap: 12px;
      justify-content: center;
    }

    .btn-confirm-cancel {
      padding: 12px 24px;
      border: 1px solid #e5e7eb;
      background: white;
      color: #374151;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-confirm-cancel:hover {
      background: #f3f4f6;
      border-color: #d1d5db;
    }

    .btn-confirm-delete {
      padding: 12px 24px;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
    }

    .btn-confirm-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .btn-confirm-send {
      padding: 12px 24px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(8, 62, 64, 0.3);
    }

    .btn-confirm-send:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(8, 62, 64, 0.4);
    }

    /* Success Modal Styles */
    .success-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .success-modal-overlay.show {
      display: flex;
      opacity: 1;
    }

    .success-modal {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border-radius: 20px;
      padding: 40px;
      text-align: center;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .success-icon-container {
      position: relative;
      margin-bottom: 24px;
    }

    .success-circle {
      width: 80px;
      height: 80px;
      margin: 0 auto;
    }

    .checkmark {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: block;
      stroke-width: 2;
      stroke: #10b981;
      stroke-miterlimit: 10;
      box-shadow: inset 0px 0px 0px #10b981;
      animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
    }

    .checkmark-circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 2;
      stroke-miterlimit: 10;
      stroke: #10b981;
      fill: none;
      animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark-check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
      100% {
        stroke-dashoffset: 0;
      }
    }

    @keyframes fill {
      100% {
        box-shadow: inset 0px 0px 0px 30px rgba(16, 185, 129, 0.1);
      }
    }

    @keyframes scale {

      0%,
      100% {
        transform: none;
      }

      50% {
        transform: scale3d(1.1, 1.1, 1);
      }
    }

    .confetti {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }

    .confetti-piece {
      position: absolute;
      width: 10px;
      height: 10px;
      border-radius: 2px;
      animation: confetti-fall 1s ease-out forwards;
      opacity: 0;
    }

    .confetti-piece:nth-child(1) {
      background: #f59e0b;
      animation-delay: 0.2s;
      --tx: -60px;
      --ty: -40px;
      --rot: 180deg;
    }

    .confetti-piece:nth-child(2) {
      background: #10b981;
      animation-delay: 0.3s;
      --tx: 70px;
      --ty: -50px;
      --rot: -200deg;
    }

    .confetti-piece:nth-child(3) {
      background: #3b82f6;
      animation-delay: 0.4s;
      --tx: -50px;
      --ty: 60px;
      --rot: 150deg;
    }

    .confetti-piece:nth-child(4) {
      background: #ec4899;
      animation-delay: 0.5s;
      --tx: 60px;
      --ty: 50px;
      --rot: -180deg;
    }

    .confetti-piece:nth-child(5) {
      background: #8b5cf6;
      animation-delay: 0.6s;
      --tx: -30px;
      --ty: -60px;
      --rot: 220deg;
    }

    .confetti-piece:nth-child(6) {
      background: #ef4444;
      animation-delay: 0.7s;
      --tx: 40px;
      --ty: 70px;
      --rot: -150deg;
    }

    @keyframes confetti-fall {
      0% {
        opacity: 1;
        transform: translate(0, 0) rotate(0deg) scale(1);
      }

      100% {
        opacity: 0;
        transform: translate(var(--tx, 50px), var(--ty, 80px)) rotate(var(--rot, 360deg)) scale(0.5);
      }
    }

    .success-title {
      color: #10b981;
      font-size: 28px;
      font-weight: 700;
      margin: 0 0 24px 0;
    }

    .success-details {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      margin-bottom: 24px;
      padding: 20px;
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
      border-radius: 12px;
      border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .success-stat {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .stat-number {
      font-size: 36px;
      font-weight: 800;
      color: #059669;
      line-height: 1;
    }

    .stat-label {
      font-size: 13px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 4px;
    }

    .success-arrow {
      color: #10b981;
      font-size: 20px;
      animation: arrowPulse 1s ease-in-out infinite;
    }

    @keyframes arrowPulse {

      0%,
      100% {
        transform: translateX(0);
        opacity: 1;
      }

      50% {
        transform: translateX(5px);
        opacity: 0.7;
      }
    }

    .success-destination {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }

    .success-destination i {
      font-size: 24px;
      color: #059669;
    }

    .success-destination span {
      font-size: 14px;
      font-weight: 600;
      color: #374151;
    }

    .success-message {
      color: #6b7280;
      font-size: 14px;
      margin: 0 0 28px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .success-message i {
      color: #3b82f6;
    }

    .btn-success-close {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border: none;
      padding: 14px 40px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }

    .btn-success-close:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
    }
  </style>

  <script>
    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      url.searchParams.delete('page');
      window.location.href = url.toString();
    }

    function showDocumentDetail(doc) {
      // Simplified status mapping for Bagian view
      function getSimplifiedStatus(status) {
        const statusLower = (status || '').toLowerCase();
        if (statusLower === 'belum dikirim') {
          return 'Belum Dikirim';
        } else if (statusLower === 'menunggu_approval_keuangan') {
          return 'Menunggu Approve';
        } else {
          return 'Terkirim';
        }
      }

      const simplifiedStatus = getSimplifiedStatus(doc.status);

      // Header fields
      document.getElementById('modal-header-agenda').textContent = doc.nomor_agenda || '-';
      document.getElementById('modal-header-status').textContent = simplifiedStatus;

      // Tab Info Utama
      document.getElementById('modal-nomor-agenda').textContent = doc.nomor_agenda || '-';
      document.getElementById('modal-status').textContent = simplifiedStatus;
      document.getElementById('modal-nomor-spp').textContent = doc.nomor_spp || '-';
      document.getElementById('modal-tanggal-spp').textContent = doc.tanggal_spp || '-';
      document.getElementById('modal-periode').textContent = (doc.bulan || '-') + ' ' + (doc.tahun || '');
      document.getElementById('modal-tanggal-masuk').textContent = doc.tanggal_masuk || '-';
      document.getElementById('modal-nilai-rupiah').textContent = doc.nilai_rupiah || '-';
      document.getElementById('modal-bagian').textContent = doc.bagian || '-';
      document.getElementById('modal-nama-pengirim').textContent = doc.nama_pengirim || '-';
      document.getElementById('modal-kebun').textContent = doc.kebun || '-';
      document.getElementById('modal-uraian-spp').textContent = doc.uraian_spp || '-';

      // Tab Keuangan & Vendor
      document.getElementById('modal-nilai-rupiah-2').textContent = doc.nilai_rupiah || '-';
      document.getElementById('modal-ejaan-nilai-rupiah').textContent = doc.ejaan_nilai_rupiah || '-';
      document.getElementById('modal-dibayar-kepada').textContent = doc.dibayar_kepada || '-';
      document.getElementById('modal-kriteria-cf').textContent = doc.kriteria_cf || '-';
      document.getElementById('modal-sub-kriteria').textContent = doc.sub_kriteria || '-';
      document.getElementById('modal-item-sub-kriteria').textContent = doc.item_sub_kriteria || '-';
      document.getElementById('modal-jenis-pembayaran').textContent = doc.jenis_pembayaran || '-';

      // Tab SPK & Berita Acara
      document.getElementById('modal-no-spk').textContent = doc.no_spk || '-';
      document.getElementById('modal-tanggal-spk').textContent = doc.tanggal_spk || '-';
      document.getElementById('modal-tanggal-berakhir-spk').textContent = doc.tanggal_berakhir_spk || '-';
      document.getElementById('modal-no-berita-acara').textContent = doc.no_berita_acara || '-';
      document.getElementById('modal-tanggal-berita-acara').textContent = doc.tanggal_berita_acara || '-';
      document.getElementById('modal-no-po').textContent = doc.no_po || '-';
      document.getElementById('modal-no-miro').textContent = doc.no_miro || '-';

      // Reset to first tab
      switchTab('info');

      document.getElementById('documentDetailModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function switchTab(tabName) {
      // Remove active from all tabs and contents
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

      // Add active to selected
      document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
      document.getElementById(`tab-${tabName}`).classList.add('active');
    }

    function closeModal() {
      document.getElementById('documentDetailModal').classList.remove('show');
      document.body.style.overflow = '';
    }

    // Close modal on overlay click
    document.getElementById('documentDetailModal').addEventListener('click', function (e) {
      if (e.target === this) {
        closeModal();
      }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
        closeSendModal();
      }
    });

    // ============ DELETE MODAL FUNCTIONS ============
    let deleteFormId = null;

    function showDeleteModal(docId) {
      deleteFormId = docId;
      document.getElementById('deleteConfirmModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
      document.getElementById('deleteConfirmModal').classList.remove('show');
      document.body.style.overflow = '';
      deleteFormId = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
      if (deleteFormId) {
        document.getElementById('deleteForm-' + deleteFormId).submit();
      }
    });

    // Close on overlay click
    document.getElementById('deleteConfirmModal').addEventListener('click', function (e) {
      if (e.target === this) closeDeleteModal();
    });

    // ============ SEND MODAL FUNCTIONS ============
    let sendFormId = null;

    function showSendModal(docId) {
      sendFormId = docId;
      document.getElementById('sendConfirmModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeSendModal() {
      document.getElementById('sendConfirmModal').classList.remove('show');
      document.body.style.overflow = '';
      sendFormId = null;
    }

    document.getElementById('confirmSendBtn').addEventListener('click', function () {
      if (sendFormId) {
        const form = document.getElementById('sendForm-' + sendFormId);
        const formData = new FormData(form);

        // Show loading state
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
        this.disabled = true;

        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => {
            closeSendModal();
            // Reset button
            this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Ya, Kirim';
            this.disabled = false;

            // Show success modal
            document.getElementById('sendSuccessModal').classList.add('show');
            document.body.style.overflow = 'hidden';
          })
          .catch(error => {
            console.error('Error:', error);
            closeSendModal();
            // Fallback to form submit on error
            form.submit();
          });
      }
    });

    // Close on overlay click
    document.getElementById('sendConfirmModal').addEventListener('click', function (e) {
      if (e.target === this) closeSendModal();
    });

    // ============ SUCCESS MODAL FUNCTIONS ============
    function closeSuccessAndReload() {
      document.getElementById('sendSuccessModal').classList.remove('show');
      document.body.style.overflow = '';
      window.location.reload();
    }

    // Close on overlay click
    document.getElementById('sendSuccessModal').addEventListener('click', function (e) {
      if (e.target === this) closeSuccessAndReload();
    });
  </script>

@endsection
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
      border-radius: 16px;
      max-width: 700px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
      color: #28a745;
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

    <!-- Single Alert Message (fix duplicate notification) -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @elseif(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

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
          <option value="sent_to_ibub" {{ request('status') == 'sent_to_ibub' ? 'selected' : '' }}>Menunggu Verifikasi
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
                    'uraian_spp' => $doc->uraian_spp ?? '-',
                    'bagian' => $doc->bagian ?? '-',
                    'nama_pengirim' => $doc->nama_pengirim ?? '-',
                    'kebun' => $doc->kebun ?? '-',
                    'no_spk' => $doc->no_spk ?? '-',
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
                  <td>
                    @if($statusLower == 'belum dikirim')
                      <span class="badge-status badge-draft">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Belum Dikirim</span>
                      </span>
                    @elseif(in_array($statusLower, ['sent_to_ibub', 'pending_approval_ibub']))
                      <span class="badge-status badge-terkirim">
                        <i class="fa-solid fa-check"></i>
                        <span>Terkirim</span>
                      </span>
                    @elseif($statusLower == 'sudah dibayar')
                      <span class="badge-status badge-selesai">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Selesai</span>
                      </span>
                    @else
                      <span class="badge-status badge-terkirim">
                        <i class="fa-solid fa-spinner"></i>
                        <span>{{ ucwords(str_replace('_', ' ', $doc->status)) }}</span>
                      </span>
                    @endif
                  </td>
                  <td onclick="event.stopPropagation()">
                    <div class="action-buttons">
                      @if($statusLower == 'belum dikirim')
                        <a href="{{ route('bagian.documents.edit', $doc) }}" class="btn-action btn-edit" title="Edit">
                          <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('bagian.documents.send-to-verifikasi', $doc) }}" method="POST"
                          class="d-inline" onsubmit="return confirm('Kirim dokumen ini ke Team Verifikasi?')">
                          @csrf
                          <button type="submit" class="btn-action btn-send" title="Kirim">
                            <i class="fa-solid fa-paper-plane"></i>
                          </button>
                        </form>
                        <form action="{{ route('bagian.documents.destroy', $doc) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus dokumen ini?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn-action btn-delete" title="Hapus">
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

  <!-- Document Detail Modal -->
  <div class="modal-overlay" id="documentDetailModal">
    <div class="modal-content-custom">
      <div class="modal-header-custom">
        <h4><i class="fa-solid fa-file-alt me-2"></i>Detail Dokumen</h4>
        <button class="modal-close" onclick="closeModal()">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
      <div class="modal-body-custom">
        <div class="detail-grid">
          <div class="detail-item">
            <div class="detail-label">Nomor Agenda</div>
            <div class="detail-value" id="modal-nomor-agenda">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Status</div>
            <div class="detail-value" id="modal-status">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Nomor SPP</div>
            <div class="detail-value" id="modal-nomor-spp">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Tanggal SPP</div>
            <div class="detail-value" id="modal-tanggal-spp">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Periode</div>
            <div class="detail-value" id="modal-periode">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Tanggal Masuk</div>
            <div class="detail-value" id="modal-tanggal-masuk">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Nilai Rupiah</div>
            <div class="detail-value highlight" id="modal-nilai-rupiah">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Bagian</div>
            <div class="detail-value" id="modal-bagian">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Nama Pengirim</div>
            <div class="detail-value" id="modal-nama-pengirim">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Kebun</div>
            <div class="detail-value" id="modal-kebun">-</div>
          </div>
          <div class="detail-item full-width">
            <div class="detail-label">Uraian SPP</div>
            <div class="detail-value" id="modal-uraian-spp">-</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">No SPK</div>
            <div class="detail-value" id="modal-no-spk">-</div>
          </div>
        </div>
      </div>
      <div class="modal-footer-custom">
        <button class="btn btn-secondary" onclick="closeModal()">
          <i class="fa-solid fa-times me-1"></i>Tutup
        </button>
      </div>
    </div>
  </div>

  <script>
    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      url.searchParams.delete('page');
      window.location.href = url.toString();
    }

    function showDocumentDetail(doc) {
      document.getElementById('modal-nomor-agenda').textContent = doc.nomor_agenda || '-';
      document.getElementById('modal-status').textContent = doc.status || '-';
      document.getElementById('modal-nomor-spp').textContent = doc.nomor_spp || '-';
      document.getElementById('modal-tanggal-spp').textContent = doc.tanggal_spp || '-';
      document.getElementById('modal-periode').textContent = (doc.bulan || '-') + ' ' + (doc.tahun || '');
      document.getElementById('modal-tanggal-masuk').textContent = doc.tanggal_masuk || '-';
      document.getElementById('modal-nilai-rupiah').textContent = doc.nilai_rupiah || '-';
      document.getElementById('modal-bagian').textContent = doc.bagian || '-';
      document.getElementById('modal-nama-pengirim').textContent = doc.nama_pengirim || '-';
      document.getElementById('modal-kebun').textContent = doc.kebun || '-';
      document.getElementById('modal-uraian-spp').textContent = doc.uraian_spp || '-';
      document.getElementById('modal-no-spk').textContent = doc.no_spk || '-';

      document.getElementById('documentDetailModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      document.getElementById('documentDetailModal').classList.remove('show');
      document.body.style.overflow = '';
    }

    // Close modal on overlay click
    document.getElementById('documentDetailModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });
  </script>

@endsection
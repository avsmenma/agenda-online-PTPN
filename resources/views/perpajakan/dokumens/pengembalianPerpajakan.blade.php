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
      min-width: 1600px;
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }

    /* Action buttons styling */
    .btn-action {
      padding: 8px 14px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
      margin: 3px 2px;
    }

    .btn-action-edit {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
    }

    .btn-action-edit:hover {
      background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
      color: white;
    }

    .btn-action-send {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }

    .btn-action-send:hover {
      background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
      color: white;
    }

    .action-column {
      white-space: nowrap;
      text-align: center !important;
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
    .table-dokumen thead th:nth-child(1) {
      width: 60px;
      min-width: 60px;
    }

    .table-dokumen thead th:nth-child(2) {
      width: 150px;
      min-width: 150px;
    }

    .table-dokumen thead th:nth-child(3) {
      width: 180px;
      min-width: 180px;
    }

    .table-dokumen thead th:nth-child(4) {
      width: 220px;
      min-width: 180px;
    }

    .table-dokumen thead th:nth-child(5) {
      width: 130px;
      min-width: 130px;
    }

    .table-dokumen thead th:nth-child(6) {
      width: 140px;
      min-width: 140px;
    }

    .table-dokumen thead th:nth-child(7) {
      width: 160px;
      min-width: 160px;
    }

    .table-dokumen thead th:nth-child(8) {
      width: 300px;
      min-width: 250px;
    }

    .table-dokumen thead th:nth-child(9) {
      width: 180px;
      min-width: 180px;
    }

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

    .table-dokumen tbody tr.main-row.selected {
      background: linear-gradient(135deg, rgba(136, 151, 23, 0.12) 0%, rgba(8, 62, 64, 0.06) 100%);
      border-left-color: #889717;
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
      display: none !important;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border-left: 4px solid #889717;
    }

    .detail-row.show {
      display: table-row !important;
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
            Menunggu Perbaikan
          </div>
          <div class="stat-value">{{ $totalMenungguPerbaikan ?? 0 }}</div>
        </div>
        <div class="stat-icon-wrapper">
          <i class="fa-solid fa-clock stat-icon"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-label">
            <i class="fa-solid fa-check-circle"></i>
            Sudah Diperbaiki
          </div>
          <div class="stat-value">{{ $totalSudahDiperbaiki ?? 0 }}</div>
        </div>
        <div class="stat-icon-wrapper">
          <i class="fa-solid fa-check-circle stat-icon"></i>
        </div>
      </div>
    </div>

    <!-- Search Box -->
    <div class="search-box">
      <form action="{{ route('returns.perpajakan.index') }}" method="GET"
        class="d-flex align-items-center flex-wrap gap-3">
        <div class="input-group" style="flex: 1; min-width: 300px;">
          <span class="input-group-text">
            <i class="fa-solid fa-magnifying-glass"></i>
          </span>
          <input type="text" class="form-control" name="search" placeholder="Cari nomor agenda, nomor SPP, atau uraian..."
            value="{{ request('search') }}">
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
                <tr class="main-row" data-id="{{ $dokumen->id }}"
                  onclick="event.stopPropagation(); toggleDetail({{ $dokumen->id }});">
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
                    @if($dokumen->returned_from_perpajakan_fixed_at || ($dokumen->current_handler == 'perpajakan' && !$dokumen->pengembalian_awaiting_fix))
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
                    <small>
                      @if($dokumen->department_returned_at)
                        {{ $dokumen->department_returned_at->format('d/m/Y H:i') }}
                      @else
                        -
                      @endif
                    </small>
                  </td>
                  <td class="alasan-column">
                    @php
                      // Get rejection reason - first try alasan_pengembalian, then check roleStatuses
                      $rejectionReason = $dokumen->alasan_pengembalian;
                      if (empty($rejectionReason)) {
                        // Check roleStatuses for rejection reason from akutansi
                        $akutansiStatus = $dokumen->roleStatuses->where('role_code', 'akutansi')->where('status', 'rejected')->first();
                        if ($akutansiStatus && !empty($akutansiStatus->notes)) {
                          $rejectionReason = $akutansiStatus->notes;
                        }
                      }
                      if (empty($rejectionReason)) {
                        $rejectionReason = $dokumen->department_return_reason;
                      }
                    @endphp
                    <div class="alasan-bubble">
                      <i class="fa-solid fa-comment-dots alasan-icon"></i>
                      <span class="alasan-text">{{ $rejectionReason ?? '-' }}</span>
                    </div>
                  </td>
                  <td class="action-column" onclick="event.stopPropagation();">
                    <a href="{{ route('documents.perpajakan.edit', $dokumen->id) }}?redirect_to={{ urlencode(route('returns.perpajakan.index')) }}"
                      class="btn-action btn-action-edit">
                      <i class="fa-solid fa-edit"></i>
                      Perbaiki Data
                    </a>
                    <button type="button" class="btn-action btn-action-send" onclick="sendToAkutansi({{ $dokumen->id }})">
                      <i class="fa-solid fa-paper-plane"></i>
                      Kirim
                    </button>
                  </td>
                </tr>
                <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display: none !important;">
                  <td colspan="9" style="padding: 0;">
                    <div class="detail-content" id="detail-content-{{ $dokumen->id }}" style="padding: 24px;">
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
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }}
              dokumen
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

  <!-- Document Detail Modal -->
  <div id="documentDetailModal" class="document-modal" style="display: none;">
    <div class="document-modal-overlay" onclick="closeDocumentModal()"></div>
    <div class="document-modal-content">
      <div class="document-modal-header">
        <h3><i class="fa-solid fa-file-invoice-dollar me-2"></i>Detail Dokumen Lengkap</h3>
        <button type="button" class="modal-close-btn" onclick="closeDocumentModal()">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
      <div class="document-modal-body" id="documentModalBody">
        <div class="text-center p-4">
          <i class="fa-solid fa-spinner fa-spin me-2" style="color: #083E40; font-size: 24px;"></i>
          <p style="color: #083E40; font-weight: 600; margin-top: 12px;">Memuat data dokumen...</p>
        </div>
      </div>
      <div class="document-modal-footer">
        <button type="button" class="btn-modal-close" onclick="closeDocumentModal()">
          <i class="fa-solid fa-times me-2"></i>Tutup
        </button>
      </div>
    </div>
  </div>

  <style>
    /* Document Detail Modal Styles */
    .document-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .document-modal-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
    }

    .document-modal-content {
      position: relative;
      background: white;
      border-radius: 16px;
      width: 90%;
      max-width: 900px;
      max-height: 85vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 20px 60px rgba(8, 62, 64, 0.3);
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .document-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 24px;
      border-bottom: 2px solid rgba(8, 62, 64, 0.1);
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      border-radius: 16px 16px 0 0;
    }

    .document-modal-header h3 {
      margin: 0;
      color: white;
      font-size: 18px;
      font-weight: 600;
    }

    .modal-close-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      color: white;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .modal-close-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.1);
    }

    .document-modal-body {
      padding: 24px;
      overflow-y: auto;
      flex: 1;
    }

    .modal-detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 16px;
    }

    .modal-detail-item {
      padding: 14px 16px;
      background: linear-gradient(135deg, #f8faf8 0%, #ffffff 100%);
      border-radius: 10px;
      border: 1px solid rgba(8, 62, 64, 0.08);
      transition: all 0.2s ease;
    }

    .modal-detail-item:hover {
      border-color: #889717;
      box-shadow: 0 2px 8px rgba(136, 151, 23, 0.1);
    }

    .modal-detail-label {
      font-size: 11px;
      font-weight: 600;
      color: #083E40;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .modal-detail-value {
      font-size: 14px;
      color: #333;
      font-weight: 500;
      word-break: break-word;
    }

    .modal-section-title {
      font-size: 15px;
      font-weight: 700;
      color: #083E40;
      margin: 20px 0 12px;
      padding-bottom: 8px;
      border-bottom: 2px solid rgba(136, 151, 23, 0.3);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .modal-section-title:first-child {
      margin-top: 0;
    }

    .modal-section-title i {
      color: #889717;
    }

    .document-modal-footer {
      padding: 16px 24px;
      border-top: 2px solid rgba(8, 62, 64, 0.1);
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }

    .btn-modal-close {
      padding: 10px 24px;
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-modal-close:hover {
      background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    @media (max-width: 768px) {
      .document-modal-content {
        width: 95%;
        max-height: 90vh;
      }

      .modal-detail-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <script>
    // Prevent event bubbling issues and ensure proper event handling
    document.addEventListener('DOMContentLoaded', function () {
      // Close all detail rows when clicking outside
      document.addEventListener('click', function (e) {
        if (!e.target.closest('.table-dokumen tbody tr')) {
          const openRows = document.querySelectorAll('.detail-row.show');
          openRows.forEach(row => {
            row.style.display = 'none';
            row.classList.remove('show');
            const docId = row.id.replace('detail-', '');
            const mainRow = document.querySelector(`tr.main-row[data-id="${docId}"]`);
            if (mainRow) {
              mainRow.classList.remove('selected');
            }
          });
        }
      });

      // Handle row clicks - open modal for document details
      const mainRows = document.querySelectorAll('.table-dokumen tbody tr.main-row');
      mainRows.forEach(row => {
        row.addEventListener('click', function (e) {
          // Don't open modal if clicking on action buttons
          if (e.target.closest('.action-column')) {
            return;
          }
          e.stopPropagation();
          e.preventDefault();
          const docId = this.getAttribute('data-id');
          if (docId) {
            openDocumentModal(parseInt(docId));
          }
        });
      });
    });

    function toggleDetail(docId) {
      const detailRow = document.getElementById('detail-' + docId);
      const mainRow = document.querySelector(`tr.main-row[data-id="${docId}"]`);

      if (!detailRow) {
        console.error('Detail row not found for document:', docId);
        return;
      }

      // Close all other detail rows first
      const allDetailRows = document.querySelectorAll('.detail-row.show');
      allDetailRows.forEach(row => {
        if (row.id !== 'detail-' + docId) {
          row.style.display = 'none';
          row.classList.remove('show');
          const otherDocId = row.id.replace('detail-', '');
          const otherMainRow = document.querySelector(`tr.main-row[data-id="${otherDocId}"]`);
          if (otherMainRow) {
            otherMainRow.classList.remove('selected');
          }
        }
      });

      // Toggle visibility
      const isHidden = detailRow.style.display === 'none' || !detailRow.classList.contains('show');

      if (isHidden) {
        // Show detail row
        detailRow.style.display = 'table-row';
        detailRow.classList.add('show');

        // Add selected class to main row
        if (mainRow) {
          mainRow.classList.add('selected');
        }

        // Scroll to detail row
        detailRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Load detail content via AJAX
        loadDocumentDetail(docId);
      } else {
        // Hide detail row
        detailRow.style.display = 'none';
        detailRow.classList.remove('show');

        // Remove selected class from main row
        if (mainRow) {
          mainRow.classList.remove('selected');
        }
      }
    }

    function loadDocumentDetail(docId) {
      const detailContent = document.getElementById(`detail-content-${docId}`);

      // Show loading
      detailContent.innerHTML = `
            <div class="text-center p-4">
              <i class="fa-solid fa-spinner fa-spin me-2" style="color: #083E40;"></i> 
              <span style="color: #083E40; font-weight: 600;">Loading detail...</span>
            </div>
          `;

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
          if (detailContent && data.success && data.dokumen) {
            // Generate HTML from JSON data
            const html = generateDetailHtml(data.dokumen);
            detailContent.innerHTML = html;
          } else {
            throw new Error('Invalid response format');
          }
        })
        .catch(error => {
          console.error('Error loading document detail:', error);
          if (detailContent) {
            detailContent.innerHTML = '<div class="text-center p-4 text-danger"><i class="fa-solid fa-exclamation-triangle me-2"></i>Gagal memuat detail dokumen</div>';
          }
        });
    }

    function generateDetailHtml(dokumen) {
      let html = '<div class="detail-grid">';

      // Document Information Section
      const detailItems = {
        'Tanggal Masuk': dokumen.tanggal_masuk || '-',
        'Bulan': dokumen.bulan || '-',
        'Tahun': dokumen.tahun || '-',
        'No SPP': dokumen.nomor_spp || '-',
        'Tanggal SPP': dokumen.tanggal_spp || '-',
        'Uraian SPP': dokumen.uraian_spp || '-',
        'Nilai Rp': formatRupiah(dokumen.nilai_rupiah) || '-',
        'Kriteria CF': dokumen.kategori || '-',
        'Jenis Dokumen': dokumen.jenis_dokumen || '-',
        'Jenis Sub Pekerjaan': dokumen.jenis_sub_pekerjaan || '-',
        'Jenis Pembayaran': dokumen.jenis_pembayaran || '-',
        'Dibayar Kepada': dokumen.dibayar_kepada || '-',
        'Kebun': dokumen.kebun || '-',
        'No SPK': dokumen.no_spk || '-',
        'Tanggal SPK': dokumen.tanggal_spk || '-',
        'Tanggal Berakhir SPK': dokumen.tanggal_berakhir_spk || '-',
        'No Berita Acara': dokumen.no_berita_acara || '-',
        'Tanggal Berita Acara': dokumen.tanggal_berita_acara || '-',
      };

      // Add PO/PR numbers if available
      if (dokumen.dokumen_pos && dokumen.dokumen_pos.length > 0) {
        const poNumbers = dokumen.dokumen_pos.map(po => po.nomor_po).join(', ');
        detailItems['Nomor PO'] = poNumbers;
      }

      if (dokumen.dokumen_prs && dokumen.dokumen_prs.length > 0) {
        const prNumbers = dokumen.dokumen_prs.map(pr => pr.nomor_pr).join(', ');
        detailItems['Nomor PR'] = prNumbers;
      }

      // Perpajakan fields
      if (dokumen.komoditi_perpajakan) detailItems['Komoditi Perpajakan'] = dokumen.komoditi_perpajakan;
      if (dokumen.status_perpajakan) detailItems['Status Perpajakan'] = dokumen.status_perpajakan;
      if (dokumen.npwp) detailItems['NPWP'] = dokumen.npwp;
      if (dokumen.alamat_pembeli) detailItems['Alamat Pembeli'] = dokumen.alamat_pembeli;
      if (dokumen.no_kontrak) detailItems['No Kontrak'] = dokumen.no_kontrak;
      if (dokumen.no_invoice) detailItems['No Invoice'] = dokumen.no_invoice;
      if (dokumen.tanggal_invoice) detailItems['Tanggal Invoice'] = dokumen.tanggal_invoice;
      if (dokumen.dpp_invoice) detailItems['DPP Invoice'] = formatRupiah(dokumen.dpp_invoice);
      if (dokumen.ppn_invoice) detailItems['PPN Invoice'] = formatRupiah(dokumen.ppn_invoice);
      if (dokumen.dpp_ppn_invoice) detailItems['DPP + PPN Invoice'] = formatRupiah(dokumen.dpp_ppn_invoice);
      if (dokumen.tanggal_pengajuan_pajak) detailItems['Tanggal Pengajuan Pajak'] = dokumen.tanggal_pengajuan_pajak;
      if (dokumen.no_faktur) detailItems['No Faktur'] = dokumen.no_faktur;
      if (dokumen.tanggal_faktur) detailItems['Tanggal Faktur'] = dokumen.tanggal_faktur;
      if (dokumen.dpp_faktur) detailItems['DPP Faktur'] = formatRupiah(dokumen.dpp_faktur);
      if (dokumen.ppn_faktur) detailItems['PPN Faktur'] = formatRupiah(dokumen.ppn_faktur);
      if (dokumen.selisih_pajak) detailItems['Selisih Pajak'] = formatRupiah(dokumen.selisih_pajak);
      if (dokumen.keterangan_pajak) detailItems['Keterangan Pajak'] = dokumen.keterangan_pajak;
      if (dokumen.penggantian_pajak) detailItems['Penggantian Pajak'] = dokumen.penggantian_pajak;
      if (dokumen.dpp_penggantian) detailItems['DPP Penggantian'] = formatRupiah(dokumen.dpp_penggantian);
      if (dokumen.ppn_penggantian) detailItems['PPN Penggantian'] = formatRupiah(dokumen.ppn_penggantian);
      if (dokumen.selisih_ppn) detailItems['Selisih PPN'] = formatRupiah(dokumen.selisih_ppn);
      if (dokumen.tanggal_selesai_verifikasi_pajak) detailItems['Tanggal Selesai Verifikasi Pajak'] = dokumen.tanggal_selesai_verifikasi_pajak;
      if (dokumen.jenis_pph) detailItems['Jenis PPH'] = dokumen.jenis_pph;
      if (dokumen.dpp_pph) detailItems['DPP PPH'] = formatRupiah(dokumen.dpp_pph);
      if (dokumen.ppn_terhutang) detailItems['PPN Terhutang'] = formatRupiah(dokumen.ppn_terhutang);
      if (dokumen.link_dokumen_pajak) detailItems['Link Dokumen Pajak'] = dokumen.link_dokumen_pajak;

      // Generate detail items HTML
      for (const [label, value] of Object.entries(detailItems)) {
        html += `
              <div class="detail-item">
                <div class="detail-label">${label}</div>
                <div class="detail-value">${value}</div>
              </div>
            `;
      }

      html += '</div>';
      return html;
    }

    function formatRupiah(angka) {
      if (!angka) return '-';
      return 'Rp. ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function sendToAkutansi(docId) {
      if (!confirm('Apakah Anda yakin ingin mengirim dokumen ini ke Akutansi setelah diperbaiki?')) {
        return;
      }

      const button = event.target.closest('.btn-action-send');
      const originalContent = button.innerHTML;
      button.disabled = true;
      button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';

      fetch(`/documents/perpajakan/${docId}/send-to-akutansi`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          next_handler: 'akutansi'
        })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Dokumen berhasil dikirim ke Akutansi!');
            window.location.reload();
          } else {
            alert('Gagal mengirim dokumen: ' + (data.message || 'Terjadi kesalahan'));
            button.disabled = false;
            button.innerHTML = originalContent;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan saat mengirim dokumen');
          button.disabled = false;
          button.innerHTML = originalContent;
        });
    }

    // Modal Functions
    function openDocumentModal(docId) {
      const modal = document.getElementById('documentDetailModal');
      const modalBody = document.getElementById('documentModalBody');
      
      // Show modal with loading state
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden'; // Prevent background scroll
      
      // Show loading state
      modalBody.innerHTML = `
        <div class="text-center p-4">
          <i class="fa-solid fa-spinner fa-spin me-2" style="color: #083E40; font-size: 24px;"></i>
          <p style="color: #083E40; font-weight: 600; margin-top: 12px;">Memuat data dokumen...</p>
        </div>
      `;

      // Fetch document details
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
          if (data.success && data.dokumen) {
            modalBody.innerHTML = generateModalHtml(data.dokumen);
          } else {
            throw new Error('Invalid response format');
          }
        })
        .catch(error => {
          console.error('Error loading document detail:', error);
          modalBody.innerHTML = '<div class="text-center p-4 text-danger"><i class="fa-solid fa-exclamation-triangle me-2"></i>Gagal memuat detail dokumen</div>';
        });
    }

    function closeDocumentModal() {
      const modal = document.getElementById('documentDetailModal');
      modal.style.display = 'none';
      document.body.style.overflow = ''; // Restore scroll
    }

    // Close modal on Escape key press
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeDocumentModal();
      }
    });

    function generateModalHtml(dokumen) {
      let html = '';

      // Section: Informasi Dasar
      html += '<div class="modal-section-title"><i class="fa-solid fa-file-lines"></i>Informasi Dasar</div>';
      html += '<div class="modal-detail-grid">';
      
      const basicInfo = {
        'Nomor Agenda': dokumen.nomor_agenda || '-',
        'Nomor SPP': dokumen.nomor_spp || '-',
        'Bulan': dokumen.bulan || '-',
        'Tahun': dokumen.tahun || '-',
        'Tanggal Masuk': dokumen.tanggal_masuk || '-',
        'Tanggal SPP': dokumen.tanggal_spp || '-',
      };

      for (const [label, value] of Object.entries(basicInfo)) {
        html += `<div class="modal-detail-item"><div class="modal-detail-label">${label}</div><div class="modal-detail-value">${value}</div></div>`;
      }
      html += '</div>';

      // Section: Informasi SPP
      html += '<div class="modal-section-title"><i class="fa-solid fa-file-invoice-dollar"></i>Informasi SPP</div>';
      html += '<div class="modal-detail-grid">';
      
      const sppInfo = {
        'Uraian SPP': dokumen.uraian_spp || '-',
        'Nilai Rupiah': formatRupiah(dokumen.nilai_rupiah) || '-',
        'Kriteria CF': dokumen.kategori || '-',
        'Jenis Dokumen': dokumen.jenis_dokumen || '-',
        'Jenis Sub Pekerjaan': dokumen.jenis_sub_pekerjaan || '-',
        'Jenis Pembayaran': dokumen.jenis_pembayaran || '-',
        'Dibayar Kepada': dokumen.dibayar_kepada || '-',
        'Kebun': dokumen.kebun || '-',
      };

      for (const [label, value] of Object.entries(sppInfo)) {
        html += `<div class="modal-detail-item"><div class="modal-detail-label">${label}</div><div class="modal-detail-value">${value}</div></div>`;
      }
      html += '</div>';

      // Section: Kontrak & SPK
      html += '<div class="modal-section-title"><i class="fa-solid fa-file-contract"></i>Kontrak & SPK</div>';
      html += '<div class="modal-detail-grid">';
      
      const kontrakInfo = {
        'No SPK': dokumen.no_spk || '-',
        'Tanggal SPK': dokumen.tanggal_spk || '-',
        'Tanggal Berakhir SPK': dokumen.tanggal_berakhir_spk || '-',
        'No Berita Acara': dokumen.no_berita_acara || '-',
        'Tanggal Berita Acara': dokumen.tanggal_berita_acara || '-',
      };

      for (const [label, value] of Object.entries(kontrakInfo)) {
        html += `<div class="modal-detail-item"><div class="modal-detail-label">${label}</div><div class="modal-detail-value">${value}</div></div>`;
      }
      html += '</div>';

      // Section: Perpajakan
      html += '<div class="modal-section-title" style="color: #ffc107;"><i class="fa-solid fa-receipt" style="color: #ffc107;"></i>Informasi Perpajakan</div>';
      html += '<div class="modal-detail-grid">';
      
      const taxInfo = {};
      if (dokumen.komoditi_perpajakan) taxInfo['Komoditi'] = dokumen.komoditi_perpajakan;
      if (dokumen.status_perpajakan) taxInfo['Status Pajak'] = dokumen.status_perpajakan;
      if (dokumen.npwp) taxInfo['NPWP'] = dokumen.npwp;
      if (dokumen.alamat_pembeli) taxInfo['Alamat Pembeli'] = dokumen.alamat_pembeli;
      if (dokumen.no_kontrak) taxInfo['No Kontrak'] = dokumen.no_kontrak;
      if (dokumen.no_invoice) taxInfo['No Invoice'] = dokumen.no_invoice;
      if (dokumen.tanggal_invoice) taxInfo['Tanggal Invoice'] = dokumen.tanggal_invoice;
      if (dokumen.dpp_invoice) taxInfo['DPP Invoice'] = formatRupiah(dokumen.dpp_invoice);
      if (dokumen.ppn_invoice) taxInfo['PPN Invoice'] = formatRupiah(dokumen.ppn_invoice);
      if (dokumen.no_faktur) taxInfo['No Faktur'] = dokumen.no_faktur;
      if (dokumen.tanggal_faktur) taxInfo['Tanggal Faktur'] = dokumen.tanggal_faktur;
      if (dokumen.dpp_faktur) taxInfo['DPP Faktur'] = formatRupiah(dokumen.dpp_faktur);
      if (dokumen.ppn_faktur) taxInfo['PPN Faktur'] = formatRupiah(dokumen.ppn_faktur);
      if (dokumen.jenis_pph) taxInfo['Jenis PPH'] = dokumen.jenis_pph;
      if (dokumen.dpp_pph) taxInfo['DPP PPH'] = formatRupiah(dokumen.dpp_pph);
      if (dokumen.ppn_terhutang) taxInfo['PPN Terhutang'] = formatRupiah(dokumen.ppn_terhutang);

      if (Object.keys(taxInfo).length === 0) {
        html += '<div class="modal-detail-item"><div class="modal-detail-value" style="color: #6c757d;">Belum ada data perpajakan</div></div>';
      } else {
        for (const [label, value] of Object.entries(taxInfo)) {
          html += `<div class="modal-detail-item"><div class="modal-detail-label">${label}</div><div class="modal-detail-value">${value}</div></div>`;
        }
      }
      html += '</div>';

      return html;
    }
  </script>

@endsection
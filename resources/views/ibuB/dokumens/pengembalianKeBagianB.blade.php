@extends('layouts/app')
@section('content')

<style>
  .form-title {
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
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
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
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

  .stat-dept {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
    color: white;
  }

  .stat-dept.perpajakan { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .stat-dept.akutansi { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .stat-dept.pembayaran { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow-x: auto;
    overflow-y: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .table-dokumen table {
    min-width: 1200px;
    width: 100%;
  }

  .table-dokumen thead {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
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
    background: linear-gradient(90deg, transparent 0%, #f8f9fa 50%, transparent 100%);
  }

  .table-dokumen thead th {
    padding: 18px 16px;
    font-weight: 700;
    font-size: 14px;
    border: none;
    text-align: center;
    letter-spacing: 0.5px;
    color: #000000;
    text-transform: uppercase;
    white-space: nowrap;
    position: relative;
  }

  /* Column width settings for consistent layout */
  .table-dokumen thead th:nth-child(1) { width: 60px; min-width: 60px; } /* No */
  .table-dokumen thead th:nth-child(2) { width: 150px; min-width: 150px; } /* Nomor Agenda */
  .table-dokumen thead th:nth-child(3) { width: 150px; min-width: 150px; } /* Nomor SPP */
  .table-dokumen thead th:nth-child(4) { width: 250px; min-width: 200px; } /* Uraian */
  .table-dokumen thead th:nth-child(5) { width: 140px; min-width: 140px; } /* Nilai */
  .table-dokumen thead th:nth-child(6) { width: 180px; min-width: 180px; } /* Tanggal */
  .table-dokumen thead th:nth-child(7) { width: 150px; min-width: 130px; } /* Dari */
  .table-dokumen thead th:nth-child(8) { width: 350px; min-width: 300px; } /* Alasan */
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
    border-bottom: 1px solid rgba(240, 240, 240, 0.5);
  }

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
  }

  .table-dokumen tbody tr:hover {
    background: linear-gradient(135deg, rgba(253, 126, 20, 0.08) 0%, rgba(229, 90, 0, 0.04) 100%);
    border-left: 3px solid #fd7e14;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(253, 126, 20, 0.1);
  }

  .table-dokumen tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(240, 240, 240, 0.3);
    text-align: center;
    font-size: 13px;
    line-height: 1.5;
  }

  .alasan-column {
    max-width: 350px;
    min-width: 300px;
    word-wrap: break-word;
    white-space: normal;
    line-height: 1.5;
    text-align: left !important;
    padding: 16px 20px !important;
  }

  .alasan-column small {
    display: inline-block;
    word-break: break-word;
    overflow-wrap: break-word;
    font-size: 12px;
    color: #555;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 8px 12px;
    border-radius: 8px;
    border-left: 3px solid #fd7e14;
    width: 100%;
    box-sizing: border-box;
    hyphens: auto;
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

  .dari-column {
    text-align: center !important;
    vertical-align: middle;
  }

  .dari-column .dept-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: white;
    white-space: nowrap;
  }

  .uraian-column {
    text-align: left !important;
    max-width: 250px;
    min-width: 200px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    line-height: 1.5;
  }

  .nomor-column {
    font-weight: 600;
    color: #2c3e50;
  }

  .dept-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: white;
    display: inline-block;
    text-transform: capitalize;
  }

  .dept-badge.perpajakan { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .dept-badge.akutansi { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .dept-badge.pembayaran { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

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


  .detail-content {
    padding: 20px;
    border-top: 2px solid rgba(8, 62, 64, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  }

  /* Detail Grid - Horizontal Layout */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    margin-top: 0;
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

  /* Badge in detail */
  .detail-value .badge {
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .badge-selesai {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .badge-proses {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  /* Responsive Detail Grid */
  @media (max-width: 1200px) {
    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 12px;
    }
  }

  @media (max-width: 768px) {
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

  .department-info {
    font-size: 11px;
    color: #666;
    margin-top: 4px;
  }

  /* Custom scrollbar styling */
  .table-dokumen::-webkit-scrollbar {
    height: 8px;
  }

  .table-dokumen::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }

  .table-dokumen::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
    cursor: pointer;
  }

  .table-dokumen::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
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
</style>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="form-title">{{ $title }}</h2>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-container">
    <div class="stat-card">
      <div class="stat-label">
        <i class="fa-solid fa-building"></i>
        Total Dokumen
      </div>
      <div class="stat-value">{{ $totalReturnedToDept }}</div>
    </div>

    @foreach($totalByDept as $dept => $count)
    <div class="stat-card">
      <div class="stat-label">
        <i class="fa-solid fa-sitemap"></i>
        {{ ucfirst($dept) }}
      </div>
      <div class="stat-value">{{ $count }}</div>
      <div class="stat-dept {{ $dept }}">{{ ucfirst($dept) }}</div>
    </div>
    @endforeach
  </div>

  <!-- Search and Filter -->
  <div class="search-box d-flex align-items-center mb-4">
    <form action="{{ route('pengembalianB.index') }}" method="GET" class="d-flex align-items-center w-100">
      <div class="input-group me-3" style="max-width: 300px;">
        <span class="input-group-text">
          <i class="fa-solid fa-search"></i>
        </span>
        <input type="text" class="form-control" name="search" placeholder="Cari dokumen..." value="{{ request('search') }}">
      </div>

      <select name="department" class="form-select me-3" style="width: 150px;">
        <option value="">Semua Bagian</option>
        @foreach($departments as $dept)
        <option value="{{ $dept }}" {{ $selectedDepartment == $dept ? 'selected' : '' }}>
          {{ ucfirst($dept) }}
        </option>
        @endforeach
      </select>

      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-filter me-2"></i>Filter
      </button>
    </form>
  </div>

  <!-- Documents Table -->
  <div class="table-responsive">
    <div class="table-dokumen">
      @if($dokumens->count() > 0)
        <table class="table">
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <th>Nomor Agenda</th>
              <th>Nomor SPP</th>
              <th>Uraian</th>
              <th>Nilai Rupiah</th>
              <th>Tanggal Terima Dokumen</th>
              <th>Dari</th>
              <th>Alasan</th>
              <th style="width: 200px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dokumens as $index => $dokumen)
            <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td class="nomor-column">{{ $dokumen->nomor_agenda }}</td>
              <td class="nomor-column">{{ $dokumen->nomor_spp }}</td>
              <td class="uraian-column">{{ \Illuminate\Support\Str::limit($dokumen->uraian_spp ?? '-', 50) }}</td>
              <td class="nilai-column">{{ $dokumen->formatted_nilai_rupiah }}</td>
              <td class="tanggal-column">
                @if($dokumen->inbox_approval_status == 'rejected' && $dokumen->inbox_approval_responded_at)
                  <small>{{ $dokumen->inbox_approval_responded_at->format('d/m/Y H:i') }}</small>
                @elseif($dokumen->returned_from_perpajakan_at)
                  <small>{{ $dokumen->returned_from_perpajakan_at->format('d/m/Y H:i') }}</small>
                @elseif($dokumen->department_returned_at)
                  <small>{{ $dokumen->department_returned_at->format('d/m/Y H:i') }}</small>
                @else
                  <small>-</small>
                @endif
              </td>
              <td class="dari-column">
                @if($dokumen->inbox_approval_status == 'rejected')
                  <span class="dept-badge" style="background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);">
                    <i class="fa-solid fa-inbox me-1"></i>Ditolak dari Inbox
                  </span>
                @elseif($dokumen->returned_from_perpajakan_at)
                  <span class="dept-badge perpajakan">
                    <i class="fa-solid fa-building me-1"></i>Team Perpajakan
                  </span>
                @elseif($dokumen->target_department == 'akutansi')
                  <span class="dept-badge akutansi">
                    <i class="fa-solid fa-building me-1"></i>Team Akutansi
                  </span>
                @elseif($dokumen->target_department == 'pembayaran')
                  <span class="dept-badge pembayaran">
                    <i class="fa-solid fa-building me-1"></i>Pembayaran
                  </span>
                @elseif($dokumen->target_department)
                  <span class="dept-badge" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                    <i class="fa-solid fa-building me-1"></i>{{ ucfirst($dokumen->target_department) }}
                  </span>
                @else
                  <small class="text-muted">-</small>
                @endif
              </td>
              <td class="alasan-column">
                @if($dokumen->inbox_approval_status == 'rejected' && $dokumen->inbox_approval_reason)
                  <small class="text-danger" title="{{ $dokumen->inbox_approval_reason }}">
                    <i class="fa-solid fa-times-circle me-1"></i>
                    {{ \Illuminate\Support\Str::limit($dokumen->inbox_approval_reason, 50) }}
                  </small>
                @else
                  <small>{{ $dokumen->alasan_pengembalian ?? '-' }}</small>
                @endif
              </td>
              <td onclick="event.stopPropagation()">
                <div class="action-buttons">
                  @if($dokumen->inbox_approval_status == 'rejected')
                    {{-- Dokumen yang di-reject dari inbox tidak bisa diedit atau dikirim --}}
                    <span class="text-muted" style="font-size: 12px;">
                      <i class="fa-solid fa-info-circle me-1"></i>
                      Dokumen ditolak dari inbox
                    </span>
                  @else
                    <a href="{{ route('dokumensB.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
                      <i class="fa-solid fa-pen"></i>
                      <span>Edit</span>
                    </a>
                    @if($dokumen->returned_from_perpajakan_at)
                      <button type="button" class="btn-action btn-send" onclick="sendBackToPerpajakan({{ $dokumen->id }})" title="Kirim ke Team Perpajakan">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim</span>
                      </button>
                    @else
                      <button type="button" class="btn-action btn-send" onclick="sendToNextHandler({{ $dokumen->id }})" title="Kirim Dokumen">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim</span>
                      </button>
                    @endif
                  @endif
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
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
          Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }} dokumen
        </div>
        {{ $dokumens->links() }}
      </div>
      @else
      <div class="empty-state">
        <i class="fa-solid fa-building"></i>
        <h5>Belum ada dokumen</h5>
        <p class="mt-2">Tidak ada dokumen yang dikembalikan ke bagian saat ini.</p>
        <a href="{{ route('dokumensB.index') }}" class="btn btn-primary mt-3">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Daftar Dokumen
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- Modal for Send to Target Department -->
<div class="modal fade" id="sendToTargetDepartmentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-paper-plane me-2"></i>Kirim Dokumen ke Bagian
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="sendToDepartmentForm">
          <input type="hidden" id="send-dept-doc-id" name="doc_id">

          <div class="mb-3">
            <label class="form-label">
              <i class="fa-solid fa-sitemap me-1"></i>Bagian Tujuan
            </label>
            <input type="text" class="form-control" id="send-dept-target" readonly>
            <div class="form-text">Dokumen akan dikirim ke bagian yang telah ditentukan.</div>
          </div>

          <div class="mb-3">
            <label for="deadline_days" class="form-label">
              <i class="fa-solid fa-clock me-1"></i>Deadline (Opsional)
            </label>
            <select class="form-select" id="deadline_days" name="deadline_days">
              <option value="">Tidak ada deadline</option>
              <option value="1">1 hari</option>
              <option value="3">3 hari</option>
              <option value="7">7 hari</option>
              <option value="14">14 hari</option>
              <option value="30">30 hari</option>
            </select>
            <div class="form-text">Set deadline untuk dokumen di bagian tujuan.</div>
          </div>

          <div class="mb-3">
            <label for="deadline_note" class="form-label">
              <i class="fa-solid fa-sticky-note me-1"></i>Catatan Deadline
            </label>
            <textarea class="form-control" id="deadline_note" name="deadline_note" rows="3"
                      placeholder="Tambahkan catatan untuk deadline..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-1"></i>Batal
        </button>
        <button type="button" class="btn btn-success" id="submit-send-dept">
          <i class="fa-solid fa-paper-plane me-1"></i>Kirim Dokumen
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Send Back to Perpajakan Confirmation -->
<div class="modal fade" id="sendBackToPerpajakanConfirmationModal" tabindex="-1" aria-labelledby="sendBackToPerpajakanConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title" id="sendBackToPerpajakanConfirmationModalLabel">
          <i class="fa-solid fa-question-circle me-2"></i>Konfirmasi Pengiriman ke Team Perpajakan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-question-circle" style="font-size: 52px; color: #28a745;"></i>
        </div>
        <h5 class="fw-bold mb-3">Apakah Anda yakin dokumen ini sudah diperbaiki dan ingin dikirim ke Team Perpajakan?</h5>
        <p class="text-muted mb-0">
          Dokumen akan dikirim ke Team Perpajakan dan akan muncul di daftar dokumen Team Perpajakan untuk proses verifikasi selanjutnya.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success px-4" id="confirmSendBackToPerpajakanBtn">
          <i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Send Back to Perpajakan Success -->
<div class="modal fade" id="sendBackToPerpajakanSuccessModal" tabindex="-1" aria-labelledby="sendBackToPerpajakanSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title" id="sendBackToPerpajakanSuccessModalLabel">
          <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #28a745;"></i>
        </div>
        <h5 class="fw-bold mb-3">Dokumen berhasil dikirim ke Team Perpajakan!</h5>
        <p class="text-muted mb-0">
          Dokumen akan muncul di daftar dokumen Team Perpajakan untuk proses verifikasi selanjutnya.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-check me-2"></i>Selesai
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle detail row
function toggleDetail(docId) {
  const detailRow = document.getElementById('detail-' + docId);
  const chevron = document.getElementById('chevron-' + docId);

  if (detailRow.style.display === 'none' || !detailRow.style.display) {
    // Show detail
    loadDocumentDetail(docId);
    detailRow.style.display = 'table-row';
    if (chevron) chevron.classList.add('rotate');
  } else {
    // Hide detail
    detailRow.style.display = 'none';
    if (chevron) chevron.classList.remove('rotate');
  }
}

// Send document to next handler
function sendToNextHandler(docId) {
  if (confirm('Apakah Anda yakin ingin mengirim dokumen ini ke proses selanjutnya?')) {
    window.location.href = `/dokumensB/${docId}/send-to-next`;
  }
}

// Send document back to perpajakan after repair
function sendBackToPerpajakan(docId) {
  // Store document ID for confirmation
  document.getElementById('confirmSendBackToPerpajakanBtn').setAttribute('data-doc-id', docId);
  
  // Show confirmation modal
  const confirmationModal = new bootstrap.Modal(document.getElementById('sendBackToPerpajakanConfirmationModal'));
  confirmationModal.show();
}

// Confirm and send back to perpajakan
function confirmSendBackToPerpajakan() {
  const docId = document.getElementById('confirmSendBackToPerpajakanBtn').getAttribute('data-doc-id');
  if (!docId) {
    console.error('Document ID not found');
    return;
  }

  // Close confirmation modal
  const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('sendBackToPerpajakanConfirmationModal'));
  confirmationModal.hide();

  // Show loading state
  const btn = document.querySelector(`button[onclick="sendBackToPerpajakan(${docId})"]`);
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Mengirim...</span>';
  
  // AJAX call to send back to perpajakan
  fetch(`/dokumensB/${docId}/send-back-to-perpajakan`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Show success modal
      const successModal = new bootstrap.Modal(document.getElementById('sendBackToPerpajakanSuccessModal'));
      successModal.show();

      // Reload page when success modal is closed
      const successModalEl = document.getElementById('sendBackToPerpajakanSuccessModal');
      successModalEl.addEventListener('hidden.bs.modal', function() {
        location.reload();
      }, { once: true });
    } else {
      alert('❌ Gagal mengirim dokumen: ' + (data.message || 'Terjadi kesalahan'));
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('❌ Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.');
    btn.disabled = false;
    btn.innerHTML = originalHTML;
  });
}

// Load document detail
function loadDocumentDetail(docId) {
  const detailContent = document.getElementById('detail-content-' + docId);

  // Show loading state
  detailContent.innerHTML = `
    <div class="text-center p-4">
      <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
    </div>
  `;

  fetch(`/dokumens/${docId}/detail`)
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

// Available documents data (passed from PHP to JavaScript)
const documentsData = @json($dokumens->keyBy('id'));

// Send to target department
function sendToTargetDepartment(docId) {
  const dokumen = documentsData[docId];

  if (dokumen) {
    document.getElementById('send-dept-doc-id').value = docId;
    document.getElementById('send-dept-target').value = dokumen.target_department;
  }

  const modal = new bootstrap.Modal(document.getElementById('sendToTargetDepartmentModal'));
  modal.show();
}

// Submit send to department
document.getElementById('submit-send-dept').addEventListener('click', function() {
  const docId = document.getElementById('send-dept-doc-id').value;
  const deadlineDays = document.getElementById('deadline_days').value;
  const deadlineNote = document.getElementById('deadline_note').value;

  const submitBtn = this;
  const originalText = this.innerHTML;

  // Show loading state
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

  const formData = {
    deadline_days: deadlineDays || null,
    deadline_note: deadlineNote || null
  };

  fetch(`/dokumensB/${docId}/send-to-target-department`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('sendToTargetDepartmentModal'));
      modal.hide();

      // Show success message
      showNotification(data.message, 'success');

      // Reload page after 2 seconds
      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      alert(data.message || 'Gagal mengirim dokumen.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat mengirim dokumen.');
  })
  .finally(() => {
    // Restore button
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
  });
});

// Notification function
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
      <span>${message}</span>
    </div>
  `;

  document.body.appendChild(notification);

  // Trigger animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  // Auto remove
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Auto-refresh notification badge
function updateNotificationBadge() {
  fetch('/pengembalian-dokumens-ke-bagian/stats')
    .then(response => response.json())
    .then(data => {
      const badge = document.getElementById('pengembalian-ke-bagian-badge');
      if (badge && data.total > 0) {
        badge.textContent = data.total;
        badge.style.display = 'inline-flex';
      } else if (badge) {
        badge.style.display = 'none';
      }
    })
    .catch(error => console.log('Error updating badge:', error));
}

// Update badge on page load
document.addEventListener('DOMContentLoaded', function() {
  updateNotificationBadge();

  // Update badge every 30 seconds
  setInterval(updateNotificationBadge, 30000);

  // Initialize confirmation button click handler
  const confirmSendBackBtn = document.getElementById('confirmSendBackToPerpajakanBtn');
  if (confirmSendBackBtn) {
    confirmSendBackBtn.addEventListener('click', confirmSendBackToPerpajakan);
  }
});
</script>

@endsection
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

  /* Department Badge Styles */
  .dept-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
    white-space: nowrap;
  }

  .dept-badge.pembayaran {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
  }

  .dept-badge.akutansi {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  }

  .dept-badge.rejected {
    border: 2px solid rgba(255, 255, 255, 0.3);
  }

  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
  }

  .btn-action {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .btn-action.btn-fix {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
  }

  .btn-action.btn-fix:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
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
    <h2 class="form-title">Daftar Pengembalian Dokumen Team Akutansi ke Team Verifikasi</h2>
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
    <form action="{{ route('returns.akutansi.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-3">
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
              <th style="width: 50px;">No</th>
              <th>Nomor Agenda</th>
              <th>Nomor SPP</th>
              <th>Uraian</th>
              <th>Nilai Rupiah</th>
              <th>TGL DOKUMEN MASUK</th>
              <th>Dari</th>
              <th>Alasan</th>
              <th style="width: 200px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dokumens as $index => $dokumen)
            <tr class="main-row" onclick="openViewDocumentModal({{ $dokumen->id }})" style="cursor: pointer;">
              <td>{{ $dokumens->firstItem() + $index }}</td>
              <td class="nomor-column">{{ $dokumen->nomor_agenda }}</td>
              <td class="nomor-column">{{ $dokumen->nomor_spp }}</td>
              <td class="uraian-column">{{ \Illuminate\Support\Str::limit($dokumen->uraian_spp ?? '-', 50) }}</td>
              <td class="nilai-column">{{ $dokumen->formatted_nilai_rupiah }}</td>
              <td class="tanggal-column">
                @php
                  // Cari rejected status dari pembayaran atau akutansi
                  $rejectedStatus = $dokumen->roleStatuses()
                    ->whereIn('role_code', ['pembayaran', 'akutansi'])
                    ->where('status', 'rejected')
                    ->latest('status_changed_at')
                    ->first();
                  
                  $tanggalTerima = null;
                  if ($rejectedStatus && $rejectedStatus->status_changed_at) {
                    $tanggalTerima = $rejectedStatus->status_changed_at;
                  } elseif ($dokumen->department_returned_at) {
                    $tanggalTerima = $dokumen->department_returned_at;
                  }
                @endphp
                @if($tanggalTerima)
                  <small>{{ \Carbon\Carbon::parse($tanggalTerima)->format('d/m/Y H:i') }}</small>
                @else
                  <small>-</small>
                @endif
              </td>
              <td class="dari-column">
                @php
                  // Cari rejected status dari pembayaran atau akutansi
                  $rejectedStatus = $dokumen->roleStatuses()
                    ->whereIn('role_code', ['pembayaran', 'akutansi'])
                    ->where('status', 'rejected')
                    ->latest('status_changed_at')
                    ->first();
                  
                  $dariRole = null;
                  if ($rejectedStatus) {
                    $dariRole = $rejectedStatus->role_code;
                  } elseif ($dokumen->target_department) {
                    $dariRole = $dokumen->target_department;
                  }
                @endphp
                @if($dariRole == 'pembayaran')
                  <span class="dept-badge pembayaran rejected">
                    <i class="fa-solid fa-times-circle me-1"></i>Team Pembayaran
                  </span>
                @elseif($dariRole == 'akutansi')
                  <span class="dept-badge akutansi rejected">
                    <i class="fa-solid fa-times-circle me-1"></i>Team Akutansi
                  </span>
                @elseif($dariRole)
                  <span class="dept-badge" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                    <i class="fa-solid fa-building me-1"></i>{{ ucfirst($dariRole) }}
                  </span>
                @else
                  <small class="text-muted">-</small>
                @endif
              </td>
              <td class="alasan-column">
                @php
                  // Cari rejected status dari pembayaran atau akutansi untuk mendapatkan alasan
                  $rejectedStatus = $dokumen->roleStatuses()
                    ->whereIn('role_code', ['pembayaran', 'akutansi'])
                    ->where('status', 'rejected')
                    ->latest('status_changed_at')
                    ->first();
                  
                  $alasan = '';
                  if ($rejectedStatus && $rejectedStatus->notes) {
                    // Ambil alasan dari dokumen_statuses table (notes)
                    $alasan = $rejectedStatus->notes;
                  } elseif ($dokumen->department_return_reason) {
                    $alasan = $dokumen->department_return_reason;
                  } elseif ($dokumen->alasan_pengembalian) {
                    // Fallback ke alasan_pengembalian
                    $alasan = $dokumen->alasan_pengembalian;
                  } else {
                    $alasan = '-';
                  }
                @endphp
                <div class="alasan-bubble">
                  <i class="fa-solid fa-exclamation-circle alasan-icon"></i>
                  <div class="alasan-text">{{ \Illuminate\Support\Str::limit($alasan, 100) }}</div>
                </div>
              </td>
              <td onclick="event.stopPropagation()">
                <div class="action-buttons">
                  <a href="{{ route('documents.akutansi.edit', $dokumen->id) }}" class="btn-action btn-fix" title="Perbaiki Data" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-wrench"></i>
                    <span>Perbaiki Data</span>
                  </a>
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
          <a href="{{ route('documents.akutansi.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Daftar Dokumen Team Akutansi
          </a>
        </div>
      @endif
    </div>
  </div>
</div>

<script>
// Format date helper
function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Format datetime helper
function formatDateTime(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Format number helper
function formatNumber(num) {
  if (!num) return '0';
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

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
  
  if (number >= 100) {
    const ratus = Math.floor(number / 100);
    if (ratus == 1) {
      hasil += 'seratus ';
    } else {
      hasil += angka[ratus] + ' ratus ';
    }
    number = number % 100;
  }
  
  if (number >= 20) {
    const puluh = Math.floor(number / 10);
    hasil += angka[puluh] + ' puluh ';
    number = number % 10;
  }
  
  if (number > 0) {
    hasil += angka[number] + ' ';
  }
  
  return hasil.trim();
}

// Open View Document Modal
function openViewDocumentModal(docId) {
  // Set document ID
  document.getElementById('view-dokumen-id').value = docId;
  
  // Set edit button URL
  document.getElementById('view-edit-btn').href = `/documents/akutansi/${docId}/edit`;
  
  // Load document data via AJAX
  fetch(`/documents/akutansi/${docId}/detail`, {
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
      console.log('Document data received:', data);
      if (data.success && data.dokumen) {
        const dok = data.dokumen;

        // Identitas Dokumen
        document.getElementById('view-nomor-agenda').textContent = dok.nomor_agenda || '-';
        document.getElementById('view-nomor-spp').textContent = dok.nomor_spp || '-';
        document.getElementById('view-tanggal-spp').textContent = dok.tanggal_spp ? formatDate(dok.tanggal_spp) : '-';
        document.getElementById('view-bulan').textContent = dok.bulan || '-';
        document.getElementById('view-tahun').textContent = dok.tahun || '-';
        document.getElementById('view-tanggal-masuk').textContent = dok.tanggal_masuk ? formatDateTime(dok.tanggal_masuk) : '-';
        document.getElementById('view-jenis-dokumen').textContent = dok.jenis_dokumen || '-';
        document.getElementById('view-jenis-sub-pekerjaan').textContent = dok.jenis_sub_pekerjaan || '-';
        document.getElementById('view-kategori').textContent = dok.kategori || '-';
        document.getElementById('view-jenis-pembayaran').textContent = dok.jenis_pembayaran || '-';

        // Detail Keuangan & Vendor
        document.getElementById('view-uraian-spp').textContent = dok.uraian_spp || '-';
        document.getElementById('view-nilai-rupiah').textContent = dok.nilai_rupiah ? 'Rp. ' + formatNumber(dok.nilai_rupiah) : '-';
        // Ejaan nilai rupiah
        if (dok.nilai_rupiah && dok.nilai_rupiah > 0) {
          document.getElementById('view-ejaan-nilai-rupiah').textContent = terbilangRupiah(dok.nilai_rupiah);
        } else {
          document.getElementById('view-ejaan-nilai-rupiah').textContent = '-';
        }
        document.getElementById('view-dibayar-kepada').textContent = dok.dibayar_kepada || '-';
        document.getElementById('view-kebun').textContent = dok.kebun || '-';

        // Referensi Pendukung
        document.getElementById('view-no-spk').textContent = dok.no_spk || '-';
        document.getElementById('view-tanggal-spk').textContent = dok.tanggal_spk ? formatDate(dok.tanggal_spk) : '-';
        document.getElementById('view-tanggal-berakhir-spk').textContent = dok.tanggal_berakhir_spk ? formatDate(dok.tanggal_berakhir_spk) : '-';
        document.getElementById('view-nomor-miro').textContent = dok.nomor_miro || '-';
        document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
        document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDate(dok.tanggal_berita_acara) : '-';

        // Nomor PO & PR
        const poList = dok.dokumen_pos && dok.dokumen_pos.length > 0 
          ? dok.dokumen_pos.map(po => po.nomor_po).join(', ')
          : '-';
        const prList = dok.dokumen_prs && dok.dokumen_prs.length > 0
          ? dok.dokumen_prs.map(pr => pr.nomor_pr).join(', ')
          : '-';
        document.getElementById('view-nomor-po').textContent = poList;
        document.getElementById('view-nomor-pr').textContent = prList;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
        modal.show();
      } else {
        throw new Error('Invalid response format');
      }
    })
    .catch(error => {
      console.error('Error loading document detail:', error);
      alert('Gagal memuat detail dokumen: ' + error.message);
    });
}
</script>

<!-- Modal Detail Dokumen Lengkap -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border: none;">
        <h5 class="modal-title" id="viewDocumentModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
          <i class="fa-solid fa-file-invoice me-2"></i>
          Detail Dokumen Lengkap
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 24px; background: #f8f9fa;">
        <input type="hidden" id="view-dokumen-id" value="">
        
        <div class="row g-3">
          <!-- Identitas Dokumen -->
          <div class="col-12">
            <div class="card" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <div class="card-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; font-weight: 600;">
                <i class="fa-solid fa-file-lines me-2"></i>Identitas Dokumen
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-3"><strong>Nomor Agenda:</strong> <span id="view-nomor-agenda">-</span></div>
                  <div class="col-md-3"><strong>Nomor SPP:</strong> <span id="view-nomor-spp">-</span></div>
                  <div class="col-md-3"><strong>Tanggal SPP:</strong> <span id="view-tanggal-spp">-</span></div>
                  <div class="col-md-3"><strong>Bulan:</strong> <span id="view-bulan">-</span></div>
                  <div class="col-md-3"><strong>Tahun:</strong> <span id="view-tahun">-</span></div>
                  <div class="col-md-3"><strong>Tanggal Masuk:</strong> <span id="view-tanggal-masuk">-</span></div>
                  <div class="col-md-3"><strong>Jenis Dokumen:</strong> <span id="view-jenis-dokumen">-</span></div>
                  <div class="col-md-3"><strong>Jenis Sub Pekerjaan:</strong> <span id="view-jenis-sub-pekerjaan">-</span></div>
                  <div class="col-md-3"><strong>Kriteria CF:</strong> <span id="view-kategori">-</span></div>
                  <div class="col-md-3"><strong>Jenis Pembayaran:</strong> <span id="view-jenis-pembayaran">-</span></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Detail Keuangan & Vendor -->
          <div class="col-12">
            <div class="card" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <div class="card-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; font-weight: 600;">
                <i class="fa-solid fa-money-bill-wave me-2"></i>Detail Keuangan & Vendor
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-12"><strong>Uraian SPP:</strong> <span id="view-uraian-spp">-</span></div>
                  <div class="col-md-6"><strong>Nilai Rupiah:</strong> <span id="view-nilai-rupiah">-</span></div>
                  <div class="col-md-6"><strong>Ejaan Nilai Rupiah:</strong> <span id="view-ejaan-nilai-rupiah">-</span></div>
                  <div class="col-md-6"><strong>Dibayar Kepada:</strong> <span id="view-dibayar-kepada">-</span></div>
                  <div class="col-md-6"><strong>Kebun:</strong> <span id="view-kebun">-</span></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Referensi Pendukung -->
          <div class="col-12">
            <div class="card" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <div class="card-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; font-weight: 600;">
                <i class="fa-solid fa-file-contract me-2"></i>Referensi Pendukung
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-4"><strong>Nomor PO:</strong> <span id="view-nomor-po">-</span></div>
                  <div class="col-md-4"><strong>Nomor PR:</strong> <span id="view-nomor-pr">-</span></div>
                  <div class="col-md-4"><strong>Nomor MIRO:</strong> <span id="view-nomor-miro">-</span></div>
                  <div class="col-md-4"><strong>No SPK:</strong> <span id="view-no-spk">-</span></div>
                  <div class="col-md-4"><strong>Tanggal SPK:</strong> <span id="view-tanggal-spk">-</span></div>
                  <div class="col-md-4"><strong>Tanggal Berakhir SPK:</strong> <span id="view-tanggal-berakhir-spk">-</span></div>
                  <div class="col-md-6"><strong>No Berita Acara:</strong> <span id="view-no-berita-acara">-</span></div>
                  <div class="col-md-6"><strong>Tanggal Berita Acara:</strong> <span id="view-tanggal-berita-acara">-</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border: none; background: #f8f9fa; padding: 16px 24px;">
        <a id="view-edit-btn" href="#" class="btn btn-warning" style="font-weight: 600;">
          <i class="fa-solid fa-wrench me-2"></i>Perbaiki Data
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-weight: 600;">
          <i class="fa-solid fa-times me-2"></i>Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<script>

@endsection



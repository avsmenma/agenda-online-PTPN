@extends('layouts/app')
@section('content')

<style>
  /* Alert Banner */
  .alert-banner {
    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
    border-left: 5px solid #ffc107;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .alert-banner-icon {
    font-size: 32px;
    color: #ff9800;
    flex-shrink: 0;
  }

  .alert-banner-content {
    flex: 1;
  }

  .alert-banner-title {
    font-size: 16px;
    font-weight: 700;
    color: #856404;
    margin-bottom: 4px;
  }

  .alert-banner-text {
    font-size: 14px;
    color: #856404;
    margin: 0;
  }

  /* Statistics Cards - Modern Design with Left Border */
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
    border-left: 4px solid #e0e0e0;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 120px;
  }

  .stat-card.has-items {
    border-left-color: #083E40;
    background: #ffffff;
  }

  .stat-card.has-items.perpajakan {
    border-left-color: #8b5cf6;
  }

  .stat-card.has-items.akutansi {
    border-left-color: #083E40;
  }

  .stat-card.has-items.pembayaran {
    border-left-color: #4facfe;
  }

  .stat-card.safe {
    border-left-color: #28a745;
    background: #ffffff;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
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
  }

  .stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #083E40;
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.5px;
  }

  .stat-subtext {
    font-size: 12px;
    color: #889717;
    font-weight: 500;
    margin: 0;
    word-wrap: break-word;
  }

  .stat-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .stat-card.has-items .stat-icon-wrapper {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  }

  .stat-card.has-items.perpajakan .stat-icon-wrapper {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  }

  .stat-card.has-items.akutansi .stat-icon-wrapper {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  }

  .stat-card.has-items.pembayaran .stat-icon-wrapper {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  .stat-card.safe .stat-icon-wrapper {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }

  .stat-icon {
    font-size: 28px;
    color: white;
  }

  /* Table Styling - Warning Theme */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow-x: auto;
    overflow-y: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(8, 62, 64, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.1);
  }

  .table-dokumen table {
    min-width: 1200px;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .table-dokumen thead {
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .table-dokumen thead th {
    background: #083E40;
    color: white;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    white-space: nowrap;
    text-align: left;
    position: relative;
  }

  .table-dokumen thead th:first-child {
    border-top-left-radius: 8px;
  }

  .table-dokumen thead th:last-child {
    border-top-right-radius: 8px;
  }

  /* Table Row - Green Border Left */
  .table-dokumen tbody tr.main-row {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 4px solid #083E40;
    border-bottom: 1px solid rgba(8, 62, 64, 0.1);
    background: #ffffff;
  }

  .table-dokumen tbody tr.main-row:hover {
    background: #f0f9f9;
    border-left-color: #0a4f52;
    transform: translateX(2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.15);
  }

  .table-dokumen tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
    text-align: center;
    font-size: 13px;
    line-height: 1.5;
  }

  /* Alasan Column - Bubble Chat */
  .alasan-column {
    max-width: 350px;
    min-width: 300px;
    word-wrap: break-word;
    white-space: normal;
    line-height: 1.5;
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

  /* Dari Column - Badge Pill */
  .dari-column {
    text-align: center !important;
    vertical-align: middle;
  }

  .dept-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    color: white;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .dept-badge.perpajakan { 
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    border: 2px solid rgba(139, 92, 246, 0.3);
  }
  .dept-badge.akutansi { 
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    border: 2px solid rgba(8, 62, 64, 0.3);
  }
  .dept-badge.pembayaran { 
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    border: 2px solid rgba(79, 172, 254, 0.3);
  }
  .dept-badge.rejected {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    border: 2px solid rgba(8, 62, 64, 0.3);
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-action {
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 120px;
    min-height: 40px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .btn-fix {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
  }

  .btn-fix:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.4);
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

  /* Form Styling */
  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
  }

  .form-control, .form-select {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #083E40;
    box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
    outline: none;
  }

  /* Modal Styling */
  .modal-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    padding: 20px 24px;
  }

  .modal-title {
    font-weight: 700;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .modal-body {
    padding: 24px;
  }

  .modal-footer {
    border-top: 1px solid #e0e0e0;
    padding: 16px 24px;
  }

  .btn-modal-primary {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 700;
    transition: all 0.3s ease;
  }

  .btn-modal-primary:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    color: white;
  }

  .btn-modal-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
  }

  .btn-modal-secondary:hover {
    background: #5a6268;
    color: white;
  }

  /* Other Styles */
  .nomor-column {
    font-weight: 600;
    color: #2c3e50;
  }

  .nilai-column {
    font-weight: 700;
    color: #dc3545;
    font-size: 14px;
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

  .tanggal-column small {
    background: linear-gradient(135deg, #e8f4fd 0%, #f0f8ff 100%);
    padding: 6px 10px;
    border-radius: 6px;
    color: #0066cc;
    font-size: 11px;
    font-weight: 500;
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

  /* Form Section Styles */
  .form-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
  }

  .section-header {
    border-bottom: 2px solid #083E40;
    padding-bottom: 12px;
  }

  .section-title {
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
    display: flex;
    align-items: center;
  }

  .section-title i {
    color: #083E40;
    font-size: 14px;
  }

  .form-label-custom {
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: block;
  }

  .form-control-custom {
    width: 100%;
    padding: 10px 14px;
    font-size: 14px;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.3s ease;
    color: #111827;
  }

  .form-control-custom:focus {
    outline: none;
    border-color: #083E40;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
  }

  .form-control-custom::placeholder {
    color: #9ca3af;
  }

  .form-text-custom {
    font-size: 11px;
    color: #6b7280;
    margin-top: 4px;
    display: block;
  }

  .form-group {
    margin-bottom: 0;
  }

  .btn-add-field {
    padding: 6px 12px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
  }

  .btn-add-field:hover {
    background: #e5e7eb;
    border-color: #9ca3af;
    color: #111827;
  }

  .po-item, .pr-item {
    display: flex;
    align-items: center;
      gap: 8px;
    }

  .po-item input, .pr-item input {
    flex: 1;
  }

  .btn-remove-field {
    padding: 8px 10px;
    background: #f0f9f9;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    color: #083E40;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s ease;
  }

  .btn-remove-field:hover {
    background: #e0f2f2;
    border-color: #083E40;
  }
</style>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="form-title">{{ $title }}</h2>
  </div>

  <!-- Alert Banner -->
  @if($dokumens->count() > 0)
  <div class="alert-banner">
    <i class="fa-solid fa-exclamation-triangle alert-banner-icon"></i>
    <div class="alert-banner-content">
      <div class="alert-banner-title">Perhatian: Terdapat {{ $dokumens->total() }} dokumen yang dikembalikan dan perlu revisi segera.</div>
      <p class="alert-banner-text">Silakan perbaiki data dokumen yang ditolak dan kirim ulang ke bagian terkait.</p>
      </div>
  </div>
  @endif

  <!-- Statistics Cards - Modern Design -->
  <div class="stats-container">
    <div class="stat-card {{ $totalReturnedToDept > 0 ? 'has-items' : 'safe' }}">
      <div class="stat-content">
        <div class="stat-label">Total Dokumen</div>
      <div class="stat-value">{{ $totalReturnedToDept }}</div>
        <div class="stat-subtext">
          @if($totalReturnedToDept > 0)
            Perlu Perhatian
          @else
            Tidak Ada Masalah
          @endif
        </div>
      </div>
      <div class="stat-icon-wrapper">
        <i class="fa-solid fa-file-circle-exclamation stat-icon"></i>
      </div>
    </div>

    @foreach($totalByDept as $dept => $count)
    <div class="stat-card {{ $count > 0 ? 'has-items ' . $dept : 'safe' }}">
      <div class="stat-content">
        <div class="stat-label">{{ strtoupper($dept) }}</div>
      <div class="stat-value">{{ $count }}</div>
        <div class="stat-subtext">
          @if($count > 0)
            Dokumen perlu revisi
          @else
            Tidak ada masalah
          @endif
        </div>
      </div>
      <div class="stat-icon-wrapper">
        <i class="fa-solid fa-sitemap stat-icon"></i>
      </div>
    </div>
    @endforeach
  </div>

  <!-- Search and Filter -->
  <div class="search-box d-flex align-items-center mb-4">
    <form action="{{ route('returns.verifikasi.index') }}" method="GET" class="d-flex align-items-center w-100">
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
                  // Cari rejected status dari perpajakan atau akutansi
                  $rejectedStatus = $dokumen->roleStatuses()
                    ->whereIn('role_code', ['perpajakan', 'akutansi'])
                    ->where('status', 'rejected')
                    ->latest('status_changed_at')
                    ->first();
                  
                  $tanggalTerima = null;
                  if ($rejectedStatus && $rejectedStatus->status_changed_at) {
                    $tanggalTerima = $rejectedStatus->status_changed_at;
                  } elseif ($dokumen->inbox_approval_status == 'rejected' && $dokumen->inbox_approval_responded_at) {
                    $tanggalTerima = $dokumen->inbox_approval_responded_at;
                  } elseif ($dokumen->returned_from_perpajakan_at) {
                    $tanggalTerima = $dokumen->returned_from_perpajakan_at;
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
                  // Cari rejected status dari perpajakan atau akutansi
                  $rejectedStatus = $dokumen->roleStatuses()
                    ->whereIn('role_code', ['perpajakan', 'akutansi'])
                    ->where('status', 'rejected')
                    ->latest('status_changed_at')
                    ->first();
                  
                  $dariRole = null;
                  if ($rejectedStatus) {
                    $dariRole = $rejectedStatus->role_code;
                  } elseif ($dokumen->inbox_approval_status == 'rejected') {
                    $rejectedFrom = $dokumen->inbox_approval_for ?? 'Unknown';
                    $dariRole = strtolower($rejectedFrom);
                  } elseif ($dokumen->returned_from_perpajakan_at) {
                    $dariRole = 'perpajakan';
                  } elseif ($dokumen->target_department) {
                    $dariRole = $dokumen->target_department;
                  }
                @endphp
                @if($dariRole == 'perpajakan')
                  <span class="dept-badge perpajakan rejected">
                    <i class="fa-solid fa-times-circle me-1"></i>Team Perpajakan
                  </span>
                @elseif($dariRole == 'akutansi')
                  <span class="dept-badge akutansi rejected">
                    <i class="fa-solid fa-times-circle me-1"></i>Team Akutansi
                  </span>
                @elseif($dariRole == 'pembayaran')
                  <span class="dept-badge pembayaran">
                    <i class="fa-solid fa-building me-1"></i>Team Pembayaran
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
                  // Cari rejected status dari perpajakan atau akutansi untuk mendapatkan alasan
                  $rejectedStatus = $dokumen->roleStatuses()
                    ->whereIn('role_code', ['perpajakan', 'akutansi'])
                    ->where('status', 'rejected')
                    ->latest('status_changed_at')
                    ->first();
                  
                  $alasan = '';
                  if ($rejectedStatus && $rejectedStatus->notes) {
                    // Ambil alasan dari dokumen_statuses table (notes)
                    $alasan = $rejectedStatus->notes;
                  } elseif ($dokumen->inbox_approval_status == 'rejected' && $dokumen->inbox_approval_reason) {
                    // Fallback ke legacy field
                    $alasan = $dokumen->inbox_approval_reason;
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
                  <a href="{{ route('documents.verifikasi.edit', $dokumen->id) }}" class="btn-action btn-fix" title="Perbaiki Data" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-wrench"></i>
                    <span>Perbaiki Data</span>
                  </a>
                    @if($dokumen->returned_from_perpajakan_at)
                      <button type="button" class="btn-action btn-send" onclick="sendBackToPerpajakan({{ $dokumen->id }})" title="Kirim ke Team Perpajakan">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim</span>
                      </button>
                  @elseif($dokumen->inbox_approval_status != 'rejected')
                      <button type="button" class="btn-action btn-send" onclick="sendToNextHandler({{ $dokumen->id }})" title="Kirim Dokumen">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim</span>
                      </button>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <!-- Pagination -->
        @if($dokumens->total() > 0)
          @include('partials.pagination-enhanced', ['paginator' => $dokumens])
        @endif
      @else
      <div class="empty-state">
        <i class="fa-solid fa-building"></i>
        <h5>Belum ada dokumen</h5>
        <p class="mt-2">Tidak ada dokumen yang dikembalikan ke bagian saat ini.</p>
        <a href="{{ route('documents.verifikasi.index') }}" class="btn btn-primary mt-3">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Daftar Dokumen
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- Modal Edit Dokumen - Full Data Editor -->

<!-- Modal for Send Back to Perpajakan Confirmation -->
<!-- Modal Konfirmasi Pengiriman ke Team Perpajakan -->
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

<!-- Modal Konfirmasi Pengiriman ke Team Selanjutnya -->
<div class="modal fade" id="sendToNextHandlerConfirmationModal" tabindex="-1" aria-labelledby="sendToNextHandlerConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
        <h5 class="modal-title" id="sendToNextHandlerConfirmationModalLabel">
          <i class="fa-solid fa-question-circle me-2"></i>Konfirmasi Pengiriman Dokumen
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa-solid fa-question-circle" style="font-size: 52px; color: #083E40;"></i>
        </div>
        <h5 class="fw-bold mb-3">Apakah Anda yakin dokumen ini sudah diperbaiki dan ingin dikirim ke team selanjutnya?</h5>
        <p class="text-muted mb-0" id="sendToNextHandlerMessage">
          Dokumen akan dikirim ke team yang sesuai dan akan muncul di inbox mereka untuk proses verifikasi selanjutnya.
        </p>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success px-4" id="confirmSendToNextHandlerBtn">
          <i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Documents data
const documentsData = @json($dokumens->keyBy('id'));

// Add PO Field
function addPOField() {
  const container = document.getElementById('po-container');
  const newField = document.createElement('div');
  newField.className = 'po-item mb-2';
  newField.innerHTML = `
    <input type="text" class="form-control-custom" name="nomor_po[]" placeholder="Masukkan nomor PO" style="flex: 1;">
    <button type="button" class="btn-remove-field" onclick="removeField(this)">
      <i class="fa-solid fa-times"></i>
        </button>
  `;
  container.appendChild(newField);
}

// Add PR Field
function addPRField() {
  const container = document.getElementById('pr-container');
  const newField = document.createElement('div');
  newField.className = 'pr-item mb-2';
  newField.innerHTML = `
    <input type="text" class="form-control-custom" name="nomor_pr[]" placeholder="Masukkan nomor PR" style="flex: 1;">
    <button type="button" class="btn-remove-field" onclick="removeField(this)">
      <i class="fa-solid fa-times"></i>
    </button>
  `;
  container.appendChild(newField);
}

// Remove Field
function removeField(btn) {
  btn.closest('.po-item, .pr-item').remove();
}

// Format date untuk datetime-local
function formatDateTimeLocal(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Format date untuk date input - handle various date formats
function formatDate(dateString) {
  if (!dateString) return '';
  
  // Handle null or undefined
  if (dateString === null || dateString === undefined || dateString === 'null' || dateString === 'undefined') return '';
  
  // If already in YYYY-MM-DD format, return as is
  if (typeof dateString === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    return dateString;
  }
  
  // Handle Laravel date format (YYYY-MM-DD HH:MM:SS or YYYY-MM-DD)
  if (typeof dateString === 'string') {
    // Extract date part if it's a datetime string
    const dateMatch = dateString.match(/^(\d{4}-\d{2}-\d{2})/);
    if (dateMatch) {
      return dateMatch[1];
    }
  }
  
  // Try to parse the date
  const date = new Date(dateString);
  
  // Check if date is valid
  if (isNaN(date.getTime())) {
    console.warn('Invalid date:', dateString);
    return '';
  }
  
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}


// Show Notification Function
function showNotification(type, message) {
  // Remove existing notification if any
  const existingNotification = document.getElementById('custom-notification');
  if (existingNotification) {
    existingNotification.remove();
  }

  // Create notification element
  const notification = document.createElement('div');
  notification.id = 'custom-notification';
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 300px;
    max-width: 500px;
    animation: slideInRight 0.3s ease-out;
    font-weight: 500;
    font-size: 14px;
  `;

  if (type === 'success') {
    notification.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    notification.style.color = 'white';
    notification.innerHTML = `
      <i class="fa-solid fa-check-circle" style="font-size: 20px;"></i>
      <span>${message}</span>
    `;
  } else {
    notification.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
    notification.style.color = 'white';
    notification.innerHTML = `
      <i class="fa-solid fa-exclamation-circle" style="font-size: 20px;"></i>
      <span>${message}</span>
    `;
  }

  // Add animation CSS if not exists
  if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
      @keyframes slideInRight {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      @keyframes slideOutRight {
        from {
          transform: translateX(0);
          opacity: 1;
        }
        to {
          transform: translateX(100%);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  }

  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.style.animation = 'slideOutRight 0.3s ease-out';
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 5000);
}

// Format Rupiah Input - Auto format with dots
function formatRupiahInput(input) {
  // Remove all non-numeric characters
  let value = input.value.replace(/[^\d]/g, '');
  
  // Format with thousand separators (dots)
  if (value) {
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    input.value = value;
  } else {
    input.value = '';
  }
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

// Apply format rupiah to edit-nilai-rupiah input and update ejaan
document.addEventListener('DOMContentLoaded', function() {
  const nilaiRupiahInput = document.getElementById('edit-nilai-rupiah');
  const ejaanInput = document.getElementById('edit-ejaan-nilai-rupiah');
  
  if (nilaiRupiahInput) {
    // Format on input
    nilaiRupiahInput.addEventListener('input', function() {
      formatRupiahInput(this);
      
      // Update ejaan nilai rupiah
      if (ejaanInput) {
        const numericValue = this.value.replace(/[^\d]/g, '');
        if (numericValue && parseInt(numericValue) > 0) {
          ejaanInput.value = terbilangRupiah(parseInt(numericValue));
        } else {
          ejaanInput.value = '';
        }
      }
    });
    
    // Format on paste
    nilaiRupiahInput.addEventListener('paste', function(e) {
      setTimeout(() => {
        formatRupiahInput(this);
        
        // Update ejaan nilai rupiah
        if (ejaanInput) {
          const numericValue = this.value.replace(/[^\d]/g, '');
          if (numericValue && parseInt(numericValue) > 0) {
            ejaanInput.value = terbilangRupiah(parseInt(numericValue));
          } else {
            ejaanInput.value = '';
          }
        }
      }, 10);
    });
  }
});

// Open View Document Modal
function openViewDocumentModal(docId) {
  // Set document ID
  document.getElementById('view-dokumen-id').value = docId;
  
  // Set edit button URL
  document.getElementById('view-edit-btn').href = `/documents/verifikasi/${docId}/edit`;
  
  // Load document data via AJAX
  fetch(`/documents/verifikasi/${docId}/detail`, {
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

        // Show modal after data is loaded
        const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
        modal.show();
      } else {
        console.error('Invalid response format:', data);
        alert('Gagal memuat data dokumen: ' + (data.message || 'Format respons tidak valid'));
      }
    })
    .catch(error => {
      console.error('Error loading document:', error);
      alert('Gagal memuat data dokumen: ' + error.message);
    });
}

// Helper functions for formatting
function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  });
}

function formatNumber(num) {
  if (!num) return '0';
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Terbilang function - use the existing function defined below
// The terbilangRupiah function is already defined in this file, so we can use it directly

// Send document to next handler
function sendToNextHandler(docId) {
  // Get document data to determine target department
  const docData = documentsData[docId];
  if (!docData) {
    alert('Data dokumen tidak ditemukan');
    return;
  }

  // Determine target department/handler
  let targetHandler = null;
  let handlerName = '';
  
  if (docData.target_department) {
    targetHandler = docData.target_department;
    const handlerNameMap = {
      'perpajakan': 'Team Perpajakan',
      'akutansi': 'Team Akutansi',
      'pembayaran': 'Team Pembayaran'
    };
    handlerName = handlerNameMap[targetHandler] || targetHandler;
  } else {
    // Default to perpajakan if no target_department
    targetHandler = 'perpajakan';
    handlerName = 'Team Perpajakan';
  }

  // Set document ID and handler info in modal
  document.getElementById('confirmSendToNextHandlerBtn').setAttribute('data-doc-id', docId);
  document.getElementById('confirmSendToNextHandlerBtn').setAttribute('data-next-handler', targetHandler);
  
  // Update modal message
  const messageEl = document.getElementById('sendToNextHandlerMessage');
  if (messageEl) {
    messageEl.textContent = `Dokumen akan dikirim ke ${handlerName} dan akan muncul di inbox mereka untuk proses verifikasi selanjutnya.`;
  }

  // Show confirmation modal
  const confirmationModal = new bootstrap.Modal(document.getElementById('sendToNextHandlerConfirmationModal'));
  confirmationModal.show();
}

// Send document back to perpajakan after repair
function sendBackToPerpajakan(docId) {
  document.getElementById('confirmSendBackToPerpajakanBtn').setAttribute('data-doc-id', docId);
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

  const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('sendBackToPerpajakanConfirmationModal'));
  confirmationModal.hide();

  const btn = document.querySelector(`button[onclick="sendBackToPerpajakan(${docId})"]`);
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Mengirim...</span>';
  
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
      window.location.reload();
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

// Confirm and send to next handler
function confirmSendToNextHandler() {
  const btn = document.getElementById('confirmSendToNextHandlerBtn');
  const docId = btn.getAttribute('data-doc-id');
  const nextHandler = btn.getAttribute('data-next-handler');
  
  if (!docId || !nextHandler) {
    console.error('Document ID or next handler not found');
    alert('Data tidak lengkap. Silakan coba lagi.');
    return;
  }

  const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('sendToNextHandlerConfirmationModal'));
  confirmationModal.hide();

  // Disable button and show loading
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Mengirim...</span>';

  // Send AJAX request
  fetch(`/documents/verifikasi/${docId}/send-to-next`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      next_handler: nextHandler
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Show success notification
      showNotification('success', data.message || 'Dokumen berhasil dikirim!');
      // Reload page after short delay
      setTimeout(() => {
        window.location.reload();
      }, 1500);
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

// Initialize confirmation buttons
document.addEventListener('DOMContentLoaded', function() {
  const confirmSendBackBtn = document.getElementById('confirmSendBackToPerpajakanBtn');
  if (confirmSendBackBtn) {
    confirmSendBackBtn.addEventListener('click', confirmSendBackToPerpajakan);
  }

  const confirmSendToNextBtn = document.getElementById('confirmSendToNextHandlerBtn');
  if (confirmSendToNextBtn) {
    confirmSendToNextBtn.addEventListener('click', confirmSendToNextHandler);
  }
});
</script>

<!-- Modal View Document Detail -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="max-width: 90%; width: 90%;">
    <div class="modal-content" style="height: 90vh; display: flex; flex-direction: column;">
      <!-- Sticky Header -->
      <div class="modal-header" style="position: sticky; top: 0; z-index: 1050; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-bottom: none; flex-shrink: 0;">
        <h5 class="modal-title" id="viewDocumentModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
          <i class="fa-solid fa-file-lines me-2"></i>
          Detail Dokumen Lengkap
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Scrollable Body -->
      <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 140px); padding: 24px; flex: 1;">
        <input type="hidden" id="view-dokumen-id">

        <!-- Section 1: Identitas Dokumen -->
        <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
          <div class="section-header mb-3">
            <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-id-card"></i>
              IDENTITAS DOKUMEN
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Nomor Agenda</label>
                <div class="detail-value" id="view-nomor-agenda">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Nomor SPP</label>
                <div class="detail-value" id="view-nomor-spp">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Tanggal SPP</label>
                <div class="detail-value" id="view-tanggal-spp">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Bulan</label>
                <div class="detail-value" id="view-bulan">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Tahun</label>
                <div class="detail-value" id="view-tahun">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Tanggal Masuk</label>
                <div class="detail-value" id="view-tanggal-masuk">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Kriteria CF</label>
                <div class="detail-value" id="view-kategori">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Sub Kriteria</label>
                <div class="detail-value" id="view-jenis-dokumen">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Item Sub Kriteria</label>
                <div class="detail-value" id="view-jenis-sub-pekerjaan">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="detail-item">
                <label class="detail-label">Jenis Pembayaran</label>
                <div class="detail-value" id="view-jenis-pembayaran">-</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 2: Detail Keuangan & Vendor -->
        <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
          <div class="section-header mb-3">
            <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-money-bill-wave"></i>
              DETAIL KEUANGAN & VENDOR
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <div class="detail-item">
                <label class="detail-label">Uraian SPP</label>
                <div class="detail-value" id="view-uraian-spp" style="white-space: pre-wrap;">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">Nilai Rupiah</label>
                <div class="detail-value" id="view-nilai-rupiah" style="font-weight: 700; color: #083E40;">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">Ejaan Nilai Rupiah</label>
                <div class="detail-value" id="view-ejaan-nilai-rupiah" style="font-style: italic; color: #666;">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">Dibayar Kepada (Vendor)</label>
                <div class="detail-value" id="view-dibayar-kepada">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">Kebun / Unit Kerja</label>
                <div class="detail-value" id="view-kebun">-</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 3: Referensi Pendukung -->
        <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
          <div class="section-header mb-3">
            <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-file-contract"></i>
              REFERENSI PENDUKUNG
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-md-3">
              <div class="detail-item">
                <label class="detail-label">No. SPK</label>
                <div class="detail-value" id="view-no-spk">-</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="detail-item">
                <label class="detail-label">Tanggal SPK</label>
                <div class="detail-value" id="view-tanggal-spk">-</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="detail-item">
                <label class="detail-label">Tanggal Berakhir SPK</label>
                <div class="detail-value" id="view-tanggal-berakhir-spk">-</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="detail-item">
                <label class="detail-label">Nomor Miro</label>
                <div class="detail-value" id="view-nomor-miro">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">No. Berita Acara</label>
                <div class="detail-value" id="view-no-berita-acara">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">Tanggal Berita Acara</label>
                <div class="detail-value" id="view-tanggal-berita-acara">-</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 4: Nomor PO & PR -->
        <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
          <div class="section-header mb-3">
            <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-hashtag"></i>
              NOMOR PO & PR
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">Nomor PO</label>
                <div class="detail-value" id="view-nomor-po">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-item">
                <label class="detail-label">Nomor PR</label>
                <div class="detail-value" id="view-nomor-pr">-</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sticky Footer -->
      <div class="modal-footer" style="position: sticky; bottom: 0; z-index: 1050; background: white; border-top: 2px solid #e0e0e0; padding: 16px 24px; flex-shrink: 0;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 24px;">
          <i class="fa-solid fa-times me-2"></i>Tutup
        </button>
        <a href="#" id="view-edit-btn" class="btn" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; padding: 10px 24px;">
          <i class="fa-solid fa-pen me-2"></i>Edit Dokumen
        </a>
      </div>
    </div>
  </div>
</div>

<style>
/* Detail Item Styles for View Modal */
.detail-item {
  margin-bottom: 8px;
}

.detail-label {
  display: block;
  font-size: 10px;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 14px;
  color: #1f2937;
  padding: 8px 12px;
  background: white;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  min-height: 38px;
  display: flex;
  align-items: center;
}
</style>

@endsection




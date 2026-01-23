@extends('layouts/app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 30px;
      font-weight: 700;
      font-size: 28px;
    }

    /* Statistics Cards - Modern Grid Layout for 5 Cards */
    .stat-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.08);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      height: 120px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(8, 62, 64, 0.05) 0%, transparent 70%);
      transition: all 0.5s ease;
    }

    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
      border-color: rgba(8, 62, 64, 0.15);
    }

    .stat-card:hover::before {
      top: -60%;
      right: -60%;
    }

    .stat-card-body {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 15px;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
      flex-shrink: 0;
    }

    /* Icon Colors for Each Statistic - Accounting Theme */
    .stat-icon.total {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    }

    .stat-icon.selesai {
      background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    }

    .stat-icon.proses {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    }

    .stat-icon.belum {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    }

    .stat-icon.dikembalikan {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .stat-icon.dikirim {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    /* Deadline Card Styles - Modern Design */
    .deadline-card-link {
      text-decoration: none;
      display: block;
    }

    .deadline-card {
      border-radius: 16px;
      padding: 20px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      min-height: 140px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      cursor: pointer;
      border-left: 5px solid;
    }

    .deadline-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .deadline-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }

    .deadline-indicator {
      display: flex;
      align-items: center;
    }

    .deadline-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    .deadline-dot.aman {
      background: #28a745;
      box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    }

    .deadline-dot.peringatan {
      background: #ffc107;
      box-shadow: 0 0 8px rgba(255, 193, 7, 0.5);
    }

    .deadline-dot.terlambat {
      background: #dc3545;
      box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .deadline-count {
      font-size: 18px;
      font-weight: 700;
    }

    .deadline-badge-wrapper {
      margin-bottom: 12px;
    }

    .deadline-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .badge-aman {
      background: #28a745;
      color: white;
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .badge-peringatan {
      background: #ffc107;
      color: #856404;
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }

    .badge-terlambat {
      background: #dc3545;
      color: white;
      box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .deadline-info {
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .deadline-info i {
      font-size: 14px;
    }

    .deadline-aman {
      background: linear-gradient(135deg, #d4edda 0%, #c8e6c9 100%);
      border-left-color: #28a745;
    }

    .deadline-aman .deadline-count,
    .deadline-aman .deadline-info {
      color: #155724;
    }

    .deadline-peringatan {
      background: linear-gradient(135deg, #fff3cd 0%, #ffe0b2 100%);
      border-left-color: #ffc107;
    }

    .deadline-peringatan .deadline-count,
    .deadline-peringatan .deadline-info {
      color: #856404;
    }

    .deadline-terlambat {
      background: linear-gradient(135deg, #f8d7da 0%, #ffcdd2 100%);
      border-left-color: #dc3545;
    }

    .deadline-terlambat .deadline-count,
    .deadline-terlambat .deadline-info {
      color: #721c24;
    }

    .stat-content {
      flex: 1;
      min-width: 0;
    }

    .stat-title {
      font-size: 11px;
      font-weight: 600;
      color: #6c757d;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      line-height: 1.2;
    }

    .stat-value {
      font-size: 26px;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 2px;
      line-height: 1;
    }

    .stat-description {
      font-size: 10px;
      color: #868e96;
      opacity: 0.8;
    }

    .stat-card:hover .stat-value {
      color: #083E40;
    }

    /* Card Icon Animation */
    .stat-card:hover .stat-icon {
      transform: scale(1.1) rotate(5deg);
      transition: all 0.3s ease;
    }

    .search-box {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 24px;
      border-radius: 16px;
      margin-bottom: 30px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.08);
    }

    .search-box .input-group {
      max-width: 400px;
      margin: 0 auto;
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

    .table-container {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      padding: 24px;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.08);
    }

    .table-container h6 {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 2px solid rgba(8, 62, 64, 0.1);
    }

    .table-container h6 span {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
      font-size: 24px;
    }

    .table-container h6 a {
      color: #083E40;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      padding: 8px 20px;
      border-radius: 20px;
      border: 2px solid #083E40;
      background: transparent;
    }

    .table-container h6 a:hover {
      color: white;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      border-color: #0a4f52;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .table {
      margin-bottom: 0;
    }

    .table thead {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    }

    .table thead th {
      color: white;
      font-weight: 600;
      font-size: 13px;
      letter-spacing: 0.5px;
      padding: 18px 16px;
      border: none;
      text-transform: uppercase;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }

    .table tbody tr:hover {
      background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
      border-left: 3px solid #889717;
      transform: scale(1.002);
    }

    .table tbody tr.highlight-row {
      background: linear-gradient(90deg, rgba(136, 151, 23, 0.15) 0%, transparent 100%);
      border-left: 3px solid #889717;
    }

    .table tbody td {
      padding: 16px;
      font-size: 13px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(8, 62, 64, 0.05);
    }

    .badge {
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.3px;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      display: inline-flex;
      align-items: center;
      gap: 6px;
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

    .badge:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Action Buttons - Modern Design */
    .btn-action {
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 12px;
      transition: all 0.3s ease;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
    }

    .btn-action:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 16px rgba(8, 62, 64, 0.3);
    }

    .btn-action:active {
      transform: translateY(-1px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .stat-card {
        height: 110px;
        padding: 16px;
      }

      .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 18px;
      }

      .stat-value {
        font-size: 24px;
      }

      .table thead th {
        padding: 14px 12px;
        font-size: 12px;
      }

      .table tbody td {
        padding: 12px 10px;
        font-size: 12px;
      }
    }
  </style>

  <h2>{{ $title }}</h2>

  <!-- Statistics Cards - Row 1: Total, Proses, Terkirim -->
  <div class="row mb-3">
    <!-- Total Dokumen -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-icon total">
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
          <div class="stat-content">
            <div class="stat-title">Total Dokumen</div>
            <div class="stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Semua dokumen akutansi</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Dokumen Diproses -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-icon proses">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-content">
            <div class="stat-title">Dokumen Diproses</div>
            <div class="stat-value">{{ number_format($totalDokumenProses ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Sedang dalam verifikasi</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Dokumen Terkirim -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <div class="stat-card">
        <div class="stat-card-body">
          <div class="stat-icon dikirim">
            <i class="fas fa-paper-plane"></i>
          </div>
          <div class="stat-content">
            <div class="stat-title">Total Terkirim</div>
            <div class="stat-value">{{ number_format($totalTerkirim ?? 0, 0, ',', '.') }}</div>
            <div class="stat-description">Dikirim ke tahap selanjutnya</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards - Row 2: Deadline Cards (Clickable) -->
  <div class="row mb-4">
    <!-- Dokumen AMAN (< 1 Hari - GREEN) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensAkutansi?deadline_filter=aman') }}" class="deadline-card-link">
        <div class="deadline-card deadline-aman">
          <div class="deadline-card-header">
            <div class="deadline-indicator">
              <span class="deadline-dot aman"></span>
            </div>
            <div class="deadline-count">{{ number_format($dokumenLessThan24h ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper">
            <span class="deadline-badge badge-aman">
              <i class="fas fa-check-circle"></i> AMAN
            </span>
          </div>
          <div class="deadline-info">
            <i class="fas fa-clock"></i> Diterima < 24 jam yang lalu </div>
          </div>
      </a>
    </div>

    <!-- Dokumen PERINGATAN (1-3 Hari - YELLOW) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensAkutansi?deadline_filter=peringatan') }}" class="deadline-card-link">
        <div class="deadline-card deadline-peringatan">
          <div class="deadline-card-header">
            <div class="deadline-indicator">
              <span class="deadline-dot peringatan"></span>
            </div>
            <div class="deadline-count">{{ number_format($dokumen24to72h ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper">
            <span class="deadline-badge badge-peringatan">
              <i class="fas fa-exclamation-triangle"></i> PERINGATAN
            </span>
          </div>
          <div class="deadline-info">
            <i class="fas fa-clock"></i> Diterima 1-3 hari yang lalu
          </div>
        </div>
      </a>
    </div>

    <!-- Dokumen TERLAMBAT (> 3 Hari - RED) -->
    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
      <a href="{{ url('/dokumensAkutansi?deadline_filter=terlambat') }}" class="deadline-card-link">
        <div class="deadline-card deadline-terlambat">
          <div class="deadline-card-header">
            <div class="deadline-indicator">
              <span class="deadline-dot terlambat"></span>
            </div>
            <div class="deadline-count">{{ number_format($dokumenMoreThan72h ?? 0, 0, ',', '.') }} Dokumen</div>
          </div>
          <div class="deadline-badge-wrapper">
            <span class="deadline-badge badge-terlambat">
              <i class="fas fa-exclamation-circle"></i> TERLAMBAT
            </span>
          </div>
          <div class="deadline-info">
            <i class="fas fa-clock"></i> Diterima > 3 hari yang lalu
          </div>
        </div>
      </a>
    </div>
  </div>


  <!-- Search Box -->
  <div class="search-box">
    <div class="input-group">
      <span class="input-group-text">
        <i class="fa-solid fa-magnifying-glass text-muted"></i>
      </span>
      <input type="text" class="form-control" placeholder="Cari dokumen akutansi...">
    </div>
  </div>

  <!-- Tabel Dokumen Terbaru -->
  <div class="table-container">
    <h6>
      <span>Dokumen Terbaru</span>
      <a href="{{ route('documents.akutansi.index') }}">View All</a>
    </h6>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr class="table table-dark">
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
          @forelse($dokumenTerbaru as $index => $dokumen)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br>
                <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              </td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</td>
              <td>
                <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
              </td>
              <td>
                @if($dokumen->status == 'selesai')
                  <span class="badge badge-selesai">
                    <i class="fas fa-check-circle"></i>
                    Selesai
                  </span>
                @elseif($dokumen->status == 'sedang diproses' && $dokumen->current_handler == 'akutansi')
                  <span class="badge badge-proses">
                    <i class="fas fa-clock"></i>
                    Diproses
                  </span>
                @elseif($dokumen->status == 'sent_to_akutansi')
                  <span class="badge badge-belum">
                    <i class="fas fa-hourglass-half"></i>
                    Belum Diproses
                  </span>
                @elseif(in_array($dokumen->status, ['returned_to_operator', 'returned_to_department', 'dikembalikan']))
                  <span class="badge badge-dikembalikan">
                    <i class="fas fa-arrow-left"></i>
                    Dikembalikan
                  </span>
                @else
                  <span class="badge badge-secondary">{{ $dokumen->status }}</span>
                @endif
              </td>
              <td>
                <button class="btn btn-action btn-sm" onclick="viewDocument({{ $dokumen->id }})">
                  <i class="fas fa-eye"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function viewDocument(id) {
      // Implement document view functionality
      console.log('View document:', id);
      // You can redirect to detail page or open modal
      window.location.href = `/dokumensAkutansi/${id}/detail`;
    }
  </script>

@endsection





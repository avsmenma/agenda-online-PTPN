@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  /* Statistics Cards - Modern Grid Layout for 6 Cards */
  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
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
    background: radial-gradient(circle, rgba(26, 77, 62, 0.05) 0%, transparent 70%);
    transition: all 0.5s ease;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(26, 77, 62, 0.2), 0 4px 16px rgba(15, 61, 46, 0.1);
    border-color: rgba(26, 77, 62, 0.15);
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

  /* Icon Colors for Each Statistic - Tax Theme */
  .stat-icon.total {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
  }
  .stat-icon.selesai {
    background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%);
  }
  .stat-icon.diproses {
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
    color: #1a4d3e;
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
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
  }

  .search-box .input-group {
    max-width: 400px;
    margin: 0 auto;
  }

  .search-box .input-group-text {
    background: white;
    border: 2px solid rgba(26, 77, 62, 0.1);
    border-right: none;
    border-radius: 12px 0 0 12px;
    padding: 12px 16px;
  }

  .search-box .form-control {
    border: 2px solid rgba(26, 77, 62, 0.1);
    border-left: none;
    border-radius: 0 12px 12px 0;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #1a4d3e;
    box-shadow: 0 0 0 4px rgba(26, 77, 62, 0.1);
  }

  .search-box .form-control::placeholder {
    color: #adb5bd;
  }

  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
  }

  .table-container h6 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(26, 77, 62, 0.1);
  }

  .table-container h6 span {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 20px;
  }

  .table-container h6 a {
    color: #1a4d3e;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 8px 20px;
    border-radius: 20px;
    border: 2px solid #1a4d3e;
    background: transparent;
  }

  .table-container h6 a:hover {
    color: white;
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    border-color: #0f3d2e;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 77, 62, 0.3);
  }

  .table {
    margin-bottom: 0;
  }

  .table thead {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
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
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
    transform: scale(1.002);
  }

  .table tbody tr.highlight-row {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
  }

  .table tbody td {
    padding: 16px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
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
    background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(64, 145, 108, 0.3);
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

  .badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  /* Action Buttons - Modern Design */
  .btn-action {
    padding: 10px 14px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    gap: 6px;
  }

  .btn-view {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
  }

  .btn-view:hover {
    background: linear-gradient(135deg, #0f3d2e 0%, #0a2e1f 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 77, 62, 0.3);
    color: white;
  }

  .btn-action:hover:not(.btn-sent) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  /* Responsive adjustments */
  @media (max-width: 1200px) {
    .stat-card {
      height: 110px;
    }

    .stat-value {
      font-size: 24px;
    }

    .stat-icon {
      width: 45px;
      height: 45px;
      font-size: 18px;
    }
  }

  @media (max-width: 768px) {
    .stat-card {
      height: 100px;
      padding: 16px;
    }

    .stat-value {
      font-size: 20px;
    }

    .stat-icon {
      width: 40px;
      height: 40px;
      font-size: 16px;
    }

    .stat-title {
      font-size: 10px;
    }

    .stat-description {
      font-size: 9px;
    }

    .table thead th {
      padding: 14px 10px;
      font-size: 11px;
    }

    .table tbody td {
      padding: 12px 10px;
      font-size: 12px;
    }

    .badge {
      padding: 6px 12px;
      font-size: 11px;
    }
  }

  @media (max-width: 576px) {
    .stat-card-body {
      flex-direction: column;
      text-align: center;
      gap: 10px;
    }

    .stat-value {
      font-size: 18px;
    }

    .btn-action {
      padding: 8px 12px;
      font-size: 11px;
      min-width: 36px;
      height: 36px;
    }
  }
</style>

<h2>{{ $title }}</h2>

<!-- Statistics Cards - 6 Cards Grid Layout -->
<div class="row mb-4">
    <!-- Total Dokumen -->
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon total">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Dokumen</div>
                    <div class="stat-value">{{ number_format($totalDokumen, 0, ',', '.') }}</div>
                    <div class="stat-description">Semua dokumen perpajakan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Dokumen Selesai -->
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon selesai">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Selesai</div>
                    <div class="stat-value">{{ number_format($totalSelesai, 0, ',', '.') }}</div>
                    <div class="stat-description">Verifikasi pajak selesai</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Dokumen Diproses -->
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon diproses">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Diproses</div>
                    <div class="stat-value">{{ number_format($totalDiproses, 0, ',', '.') }}</div>
                    <div class="stat-description">Sedang dalam verifikasi</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Dokumen Belum Diproses -->
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon belum">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Belum Diproses</div>
                    <div class="stat-value">{{ number_format($totalBelumDiproses, 0, ',', '.') }}</div>
                    <div class="stat-description">Menunggu diproses</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Dokumen Dikembalikan -->
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon dikembalikan">
                    <i class="fas fa-arrow-left"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Dikembalikan</div>
                    <div class="stat-value">{{ number_format($totalDikembalikan, 0, ',', '.') }}</div>
                    <div class="stat-description">Dokumen dikembalikan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Dokumen Dikirim -->
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon dikirim">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Selesai & Dikirim</div>
                    <div class="stat-value">{{ number_format($totalDikirim, 0, ',', '.') }}</div>
                    <div class="stat-description">Dokumen selesai dikirim ke tahap berikutnya</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Box -->
<div class="search-box">
  <div class="input-group">
    <span class="input-group-text">
      <i class="fa-solid fa-magnifying-glass text-muted"></i>
    </span>
    <input type="text" class="form-control" placeholder="Cari dokumen perpajakan...">
  </div>
</div>

<!-- Tabel Dokumen Terbaru -->
<div class="table-container">
  <h6>
    <span>Dokumen Terbaru</span>
    <a href="{{ route('documents.perpajakan.index') }}">View All</a>
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
            @if($dokumen->status_perpajakan == 'selesai')
              <span class="badge badge-selesai">
                <i class="fas fa-check-circle"></i>
                Selesai
              </span>
            @elseif($dokumen->status_perpajakan == 'sedang_diproses')
              <span class="badge badge-proses">
                <i class="fas fa-clock"></i>
                Diproses
              </span>
            @else
              <span class="badge badge-belum">
                <i class="fas fa-hourglass-half"></i>
                Belum Diproses
              </span>
            @endif
          </td>
          <td>
            <a href="{{ route('documents.perpajakan.index') }}" class="btn-action btn-view" title="Lihat Detail">
              <i class="fa-solid fa-eye"></i>
              Lihat
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center py-4">
            <i class="fa-solid fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted mb-0">Belum ada dokumen yang tersedia.</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
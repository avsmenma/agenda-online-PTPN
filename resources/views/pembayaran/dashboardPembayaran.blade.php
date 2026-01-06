@extends('layouts/app');
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    font-weight: 700;
  }

  .card {
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(8, 62, 64, 0.2), 0 2px 8px rgba(136, 151, 23, 0.1);
  }

  .card::before {
    /* content: ''; */
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    transition: all 0.5s ease;
  }

  .card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(8, 62, 64, 0.3), 0 4px 12px rgba(136, 151, 23, 0.2);
  }

  .card:hover::before {
    top: -60%;
    right: -60%;
  }

  .card-body {
    position: relative;
    z-index: 1;
  }

  .card i {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    transition: all 0.3s ease;
  }

  .card:hover i {
    transform: scale(1.1) rotate(5deg);
  }

  .text-xs {
    font-size: 14px;
    letter-spacing: 0.5px;
    opacity: 0.95;
  }

  .h5 {
    font-size: 28px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 24px;
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
  }

  .table-container h6 a {
    color: #889717;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 6px 16px;
    border-radius: 20px;
    border: 2px solid transparent;
  }

  .table-container h6 a:hover {
    color: white;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    border-color: #889717;
  }

  .table {
    margin-bottom: 0;
    width: 100%;
    border-collapse: collapse;
  }

  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .table thead {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%) !important;
    display: table-header-group !important;
    visibility: visible !important;
    opacity: 1 !important;
  }

  .table thead th {
    color: white !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    letter-spacing: 0.5px;
    padding: 16px 12px !important;
    border: none !important;
    vertical-align: middle !important;
    text-align: left !important;
    height: auto !important;
    line-height: normal !important;
    display: table-cell !important;
    visibility: visible !important;
  }

  .table thead tr {
    display: table-row !important;
    visibility: visible !important;
    height: auto !important;
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
    padding: 14px 12px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
  }

  .badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
  }

  .badge-success {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.3);
  }

  .btn-view {
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

  .btn-view:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.3);
  }

  .btn-view:active {
    transform: translateY(-1px);
  }

  /* Badge Styles */
  .badge-status {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    display: inline-block;
  }

  .badge-selesai {
    background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
  }

  .badge-proses {
    background: linear-gradient(135deg, #ffc107 0%, #ffcd39 100%);
    color: #333;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  /* Detail Row Styles */
  .detail-row {
    display: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  }

  .detail-row.show {
    display: table-row;
  }

  .detail-content {
    padding: 20px;
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
  }

  /* Action Button Styles */
  .action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
  }

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
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-action i {
    font-size: 14px;
  }

  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  }

  .btn-upload {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    white-space: nowrap;
  }

  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
    padding: 8px 12px;
  }

  .chevron-icon {
    transition: transform 0.3s ease;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  .main-row {
    cursor: pointer;
  }

  /* Custom thead styling with gradient */
  .table-container .table-responsive table thead tr.table-dark {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%) !important;
  }

  .table-container .table-responsive table thead tr.table-dark th {
    color: white !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    padding: 16px 12px !important;
    border: none !important;
  }

  /* Section Title Styling */
  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #083E40;
    margin-top: 30px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #889717;
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  /* Detail item full width for long text */
  .detail-item-full {
    grid-column: 1 / -1;
  }
</style>

<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4 ">
                            <div class="card border-left-primary shadow h-100 py-2" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-white mb-1">
                                                T.Dokumen</div>
                                            <div class="h5 mb-0 font-weight-bold text-white">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-tasks fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2" style="background: linear-gradient(135deg, #889717 0%, #9ab01f 50%, #083E40 100%);">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-white mb-1">
                                                T.Dokumen Selesai</div>
                                            <div class="h5 mb-0 font-weight-bold text-white">{{ number_format($totalSelesai ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-book-open fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-white  mb-1">
                                                T.Dokumen Proses</div>
                                            <div class="h5 mb-0 font-weight-bold text-white">{{ number_format($totalProses ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-refresh fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    <div class="col-xl-3 col-md-6 mb-4 ">
                            <div class="card border-left-primary shadow h-100 py-2" style="background: linear-gradient(135deg, #889717 0%, #9ab01f 50%, #083E40 100%);">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-white  mb-1">
                                                T.Dokumen Dikembalikan</div>
                                            <div class="h5 mb-0 font-weight-bold text-white">{{ number_format($totalDikembalikan ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-left fa-2x text-white"></i>
                                        </div>
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
        <input type="text" class="form-control" placeholder="Search...">
      </div>
    </div>

       <!-- Tabel Dokumen Terbaru -->
    <div class="table-container">
      <h6>
        <span style="color: #1a4d3e; text-decoration: none; font-size: 24px;">Dokumen Masuk</span>
        <a href="{{ url('/dokumensPembayaran')}}" style="color: #1a4d3e; text-decoration: none; font-size: 14px;">View All</a>
      </h6>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr class="table table-dark">
              <th>No</th>
              <th>Tanggal Masuk</th>
              <th>Nomor SPP</th>
              <th>Tanggal SPP</th>
              <th>Nilai Rupiah</th>
              <th>Tanggal Dibayar</th>
              <th>Status</th>
              <th>Bukti</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dokumenTerbaru as $index => $dokumen)
              @php
                // Handler yang dianggap "belum siap dibayar"
                $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];
                
                // Cek apakah dokumen masih di handler yang belum siap
                $isBelumSiap = in_array($dokumen->current_handler, $belumSiapHandlers);
                
                // Tanggal masuk ke pembayaran (gunakan sent_to_pembayaran_at jika ada, jika tidak gunakan tanggal_masuk)
                $tanggalMasuk = $dokumen->sent_to_pembayaran_at ?? $dokumen->tanggal_masuk;
              @endphp
              <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $tanggalMasuk ? $tanggalMasuk->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $dokumen->nomor_spp ?? '-' }}</td>
                <td>{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</td>
                <td>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
                <td>{{ $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y H:i') : '-' }}</td>
                <td>
                  @if($dokumen->status_pembayaran == 'sudah_dibayar')
                    <span class="badge-status badge-selesai">Sudah Dibayar</span>
                  @elseif($isBelumSiap)
                    <span class="badge-status badge-proses">Belum Siap</span>
                  @elseif($dokumen->status_pembayaran == 'siap_dibayar')
                    <span class="badge-status badge-proses">Siap Dibayar</span>
                  @else
                    <span class="badge-status badge-proses">Belum Dibayar</span>
                  @endif
                </td>
                <td>
                  @if($dokumen->bukti_pembayaran)
                    <a href="{{ asset('storage/' . $dokumen->bukti_pembayaran) }}" target="_blank" class="btn-action btn-edit" onclick="event.stopPropagation()">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td onclick="event.stopPropagation()">
                  <div class="action-buttons">
                    <button class="btn-action btn-chevron" onclick="toggleDetail({{ $dokumen->id }})">
                      <i class="fa-solid fa-chevron-down chevron-icon" id="chevron-{{ $dokumen->id }}"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="detail-row" id="detail-{{ $dokumen->id }}">
                <td colspan="9">
                  <div class="detail-content">
                    <div class="detail-grid">
                      <div class="detail-item">
                        <span class="detail-label">Tanggal Masuk</span>
                        <span class="detail-value">{{ $tanggalMasuk ? $tanggalMasuk->format('d/m/Y H:i:s') : '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Bulan</span>
                        <span class="detail-value">{{ $dokumen->bulan ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Tahun</span>
                        <span class="detail-value">{{ $dokumen->tahun ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">No SPP</span>
                        <span class="detail-value">{{ $dokumen->nomor_spp ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Tanggal SPP</span>
                        <span class="detail-value">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</span>
                      </div>
                      <div class="detail-item detail-item-full">
                        <span class="detail-label">Uraian SPP</span>
                        <span class="detail-value">{{ $dokumen->uraian_spp ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Nilai Rp</span>
                        <span class="detail-value">Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Kriteria CF</span>
                        <span class="detail-value">{{ $dokumen->kategori ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Sub Kriteria</span>
                        <span class="detail-value">{{ $dokumen->jenis_dokumen ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Item Sub Kriteria</span>
                        <span class="detail-value">{{ $dokumen->jenis_sub_pekerjaan ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Kebun</span>
                        <span class="detail-value">{{ $dokumen->kebun ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Dibayar Kepada</span>
                        <span class="detail-value">{{ $dokumen->dibayar_kepada ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">No Berita</span>
                        <span class="detail-value">{{ $dokumen->no_berita_acara ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Tanggal Berita Acara</span>
                        <span class="detail-value">{{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">No PSK</span>
                        <span class="detail-value">{{ $dokumen->no_spk ?? '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Tanggal PSK</span>
                        <span class="detail-value">{{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Tanggal Akhir PSK</span>
                        <span class="detail-value">{{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</span>
                      </div>
                      @if($dokumen->dokumenPos && $dokumen->dokumenPos->count() > 0)
                        <div class="detail-item">
                          <span class="detail-label">No PO</span>
                          <span class="detail-value">{{ $dokumen->dokumenPos->pluck('nomor_po')->filter()->implode(', ') ?: '-' }}</span>
                        </div>
                      @endif
                      @if($dokumen->dokumenPrs && $dokumen->dokumenPrs->count() > 0)
                        <div class="detail-item">
                          <span class="detail-label">No PR</span>
                          <span class="detail-value">{{ $dokumen->dokumenPrs->pluck('nomor_pr')->filter()->implode(', ') ?: '-' }}</span>
                        </div>
                      @endif
                      <div class="detail-item">
                        <span class="detail-label">Nomor Miro</span>
                        <span class="detail-value">{{ $dokumen->nomor_miro ?? '-' }}</span>
                      </div>
                    </div>

                    @if($dokumen->jenis_pph || $dokumen->dpp_pph || $dokumen->ppn_terhutang)
                      <!-- Section Perpajakan -->
                      <div class="section-title">Informasi Team Perpajakan</div>
                      <div class="detail-grid">
                        <div class="detail-item">
                          <span class="detail-label">Jenis PPh</span>
                          <span class="detail-value">{{ $dokumen->jenis_pph ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                          <span class="detail-label">DPP PPh</span>
                          <span class="detail-value">{{ $dokumen->dpp_pph ? 'Rp ' . number_format($dokumen->dpp_pph, 0, ',', '.') : '-' }}</span>
                        </div>
                        <div class="detail-item">
                          <span class="detail-label">PPh Terhutang</span>
                          <span class="detail-value">{{ $dokumen->ppn_terhutang ? 'Rp ' . number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-' }}</span>
                        </div>
                      </div>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" style="text-align: center; padding: 40px;">
                  <p style="color: #6c757d; font-size: 14px;">Tidak ada dokumen masuk</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

<script>
  function toggleDetail(dokumenId) {
    const detailRow = document.getElementById('detail-' + dokumenId);
    const chevron = document.getElementById('chevron-' + dokumenId);

    if (detailRow && chevron) {
      if (detailRow.classList.contains('show')) {
        detailRow.classList.remove('show');
        chevron.classList.remove('rotate');
      } else {
        detailRow.classList.add('show');
        chevron.classList.add('rotate');
      }
    }
  }

  // Search functionality
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-box input');

    if (searchInput) {
      searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('.main-row');

        tableRows.forEach(function(row) {
          const text = row.textContent.toLowerCase();
          const onclickAttr = row.getAttribute('onclick');
          if (onclickAttr) {
            const match = onclickAttr.match(/toggleDetail\((\d+)\)/);
            if (match) {
              const dokumenId = match[1];
              if (text.includes(searchTerm)) {
                row.style.display = '';
                // Also show the detail row if it was visible
                const detailRow = document.getElementById('detail-' + dokumenId);
                if (detailRow && detailRow.classList.contains('show')) {
                  detailRow.style.display = '';
                }
              } else {
                row.style.display = 'none';
                // Hide the detail row too
                const detailRow = document.getElementById('detail-' + dokumenId);
                if (detailRow) {
                  detailRow.style.display = 'none';
                }
              }
            }
          }
        });
      });
    }
  });
</script>
@endsection
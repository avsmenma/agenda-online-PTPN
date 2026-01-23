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
  }

  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow-x: auto;
    overflow-y: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    max-width: 100%;
    margin: 0 auto;
  }

  .table-dokumen table {
    width: 100%;
    max-width: 100%;
    table-layout: auto;
  }

  .table-dokumen thead {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    position: relative;
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
    background: linear-gradient(90deg, transparent 0%, #889717 50%, transparent 100%);
  }

  .table-dokumen thead th {
    padding: 14px 12px;
    font-weight: 700;
    font-size: 13px;
    border: none;
    text-align: center;
    letter-spacing: 0.5px;
    white-space: nowrap;
    color: #ffffff;
    text-transform: uppercase;
    position: relative;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
  }

  .table-dokumen thead th:last-child {
    border-right: none;
  }

  /* Column width settings - More compact and proportional */
  .table-dokumen thead th:nth-child(1) { width: 50px; min-width: 50px; max-width: 60px; } /* No */
  .table-dokumen thead th:nth-child(2) { width: 120px; min-width: 120px; max-width: 150px; } /* Nomor Agenda */
  .table-dokumen thead th:nth-child(3) { width: 160px; min-width: 160px; max-width: 200px; } /* Nomor SPP */
  .table-dokumen thead th:nth-child(4) { width: 200px; min-width: 180px; max-width: 280px; } /* Uraian */
  .table-dokumen thead th:nth-child(5) { width: 130px; min-width: 120px; max-width: 150px; } /* Nilai */
  .table-dokumen thead th:nth-child(6) { width: 160px; min-width: 150px; max-width: 180px; } /* Tanggal Terima Dokumen */
  .table-dokumen thead th:nth-child(7) { width: 300px; min-width: 250px; max-width: 400px; } /* Alasan */

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table-dokumen tbody tr {
    height: auto;
    min-height: 50px;
  }

  .table-dokumen tbody tr.main-row:hover {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    border-left: 3px solid #889717;
    transform: scale(1.005);
  }

  .table-dokumen tbody tr.main-row.highlight {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.15) 0%, transparent 100%) !important;
    border-left: 3px solid #889717;
  }

  .table-dokumen tbody tr.main-row.selected {
    background: linear-gradient(90deg, rgba(8, 62, 64, 0.05) 0%, transparent 100%);
    border-left: 3px solid #083E40;
  }

  .table-dokumen tbody td {
    padding: 12px 10px;
    font-size: 12px;
    vertical-align: top;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
    border-right: 1px solid rgba(8, 62, 64, 0.05);
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
  }

  .table-dokumen tbody td:last-child {
    border-right: none;
  }

  .alasan-column {
    max-width: 400px;
    min-width: 250px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.5;
    padding: 10px;
    vertical-align: top;
  }

  .alasan-column > div {
    display: block;
    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    max-width: 100%;
    white-space: normal;
  }

  /* Remove max-width constraint that was causing issues */
  .table-dokumen tbody td {
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
  }

  /* Responsive adjustments */
  @media (max-width: 1400px) {
    .table-dokumen table {
      font-size: 12px;
    }

    .table-dokumen thead th {
      padding: 12px 10px;
      font-size: 12px;
    }

    .table-dokumen tbody td {
      padding: 10px 8px;
      font-size: 11px;
    }
  }

  @media (max-width: 1200px) {
    .table-dokumen {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table-dokumen table {
      min-width: 1000px;
    }

    /* Reduce column widths on smaller screens */
    .table-dokumen thead th:nth-child(4) { width: 180px; min-width: 150px; max-width: 220px; } /* Uraian */
    .table-dokumen thead th:nth-child(7) { width: 280px; min-width: 220px; max-width: 350px; } /* Alasan */
  }

  @media (max-width: 992px) {
    .table-dokumen table {
      min-width: 900px;
    }
  }

  /* Uraian column styling */
  .uraian-column {
    max-width: 280px;
    min-width: 180px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.5;
    text-align: left !important;
    vertical-align: top;
  }

  .row{
    margin-right:10px;
    justify-content : space-between;
    gap: 24px;
  }

  .row > [class*='col-'] {
    padding-left: 12px;
    padding-right: 12px;
  }

   .detail-row {
    display: none;
    background: linear-gradient(135deg, #f8faf8 0%, #ffffff 100%);
    border-left: 4px solid #889717;
  }

.detail-row.show {
    display: table-row;
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .detail-content {
    padding: 24px;
    border-radius: 8px;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    padding: 12px;
    background: white;
    border-radius: 8px;
    border-left: 3px solid #889717;
    transition: all 0.3s ease;
  }

  .detail-item:hover {
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.1);
    transform: translateY(-2px);
  }

  .detail-label {
    font-weight: 600;
    color: #083E40;
    font-size: 12px;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }

  .detail-value {
    color: #333;
    font-size: 13px;
    font-weight: 500;
  }

  .badge-status {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .badge-Dikembalikan {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
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
  }

  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }
  .btn-kirim {
    background: linear-gradient(135deg, #0401ccff 0%, #020daaff 100%);
    color: white;
  }

  .btn-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .btn-view {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .btn-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }

  .btn-action:active {
    transform: translateY(-1px);
  }

  .filter-section {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .filter-section select,
  .filter-section input {
    padding: 10px 14px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  .filter-section select:focus,
  .filter-section input:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-filter {
    padding: 10px 24px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }
  .btn-paraf {
    padding: 10px 24px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 62, 64, 0.3);
  }

  .chevron-icon {
    transition: transform 0.4s ease;
    color: #fff;
  }

  .chevron-icon.rotate {
    transform: rotate(180deg);
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
  }

  .pagination button {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    cursor: pointer;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: #083E40;
  }

  .pagination button:hover {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .pagination button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-chevron {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
  }

  .btn-chevron:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
  }

  .card-info {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    border-radius: 16px;
    color: white;
    padding: 32px 24px;
    box-shadow: 0 8px 24px rgba(8, 62, 64, 0.2), 0 2px 8px rgba(136, 151, 23, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 160px;
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .card-info::before {
    /* content: ''; */
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    transition: all 0.5s ease;
  }

  .card-info:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(8, 62, 64, 0.3), 0 4px 12px rgba(136, 151, 23, 0.2);
  }

  .card-info:hover::before {
    top: -60%;
    right: -60%;
  }

  .card-info .icon-box {
    background: rgba(255,255,255,0.25);
    padding: 16px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
  }

  .card-info:hover .icon-box {
    background: rgba(255,255,255,0.35);
    transform: scale(1.1) rotate(5deg);
  }

  .card-info i {
    color: white;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
  }

  .card-title-small {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    opacity: 0.95;
  }

  .card-number {
    font-size: 28px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  /* Checkbox styling */
  input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #889717;
  }

</style>
<!-- <div class="card mb-4 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 10px; box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05); border: 1px solid rgba(8, 62, 64, 0.08);">
    <h2 class="form-title">{{ $title }}</h2>
</div> -->
<h2 style="margin-bottom: 50px; font-weight: 700;">{{ $title }}</h2>

<div class="row justify-content-center  pb-0 mb-3" style="width :100%;">

    <!-- Card 1 -->
    <div class=" col-xl-3 col-md-6 mb-4 "style="width :30%;" >
        <div class="card-info w-100">
            <div class="flex-grow-1">
                <div class="card-title-small">Total Dokumen Aktif</div>
                <div class="card-number">{{ number_format($totalDibaca ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="icon-box ms-3">
                <i class="fas fa-file-lines fa-2x"></i>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="col-xl-3 col-md-6 mb-4 px-xl-2 " style="width :30%;" >
        <div class="card-info">
            <div class="flex-grow-1">
                <div class="card-title-small">Dikembalikan ke Ibu Tarapul</div>
                <div class="card-number">{{ number_format($totalDikembalikan ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="icon-box ms-3">
                <i class="fas fa-undo fa-2x"></i>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="col-xl-3 col-md-6 mb-4"style="width :30%;">
        <div class="card-info">
            <div class="flex-grow-1">
                <div class="card-title-small">Dokumen Diselesaikan</div>
                <div class="card-number">{{ number_format($totalDikirim ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="icon-box ms-3">
                <i class="fas fa-check-circle fa-2x"></i>
            </div>
        </div>
    </div>

</div>
<!-- Tabel Dokumen -->
  <div class="table-dokumen">
    <table class="table mb-0">
      <thead>
        <tr>
          <th style="width: 50px;">No</th>
          <th>Nomor Agenda</th>
          <th>Nomor SPP</th>
          <th>Uraian</th>
          <th>Nilai</th>
          <th>Tanggal Terima Dokumen</th>
          <th>Alasan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($dokumens ?? [] as $dokumen)
        <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
          <td style="text-align: center;">{{ isset($dokumens) && $dokumens->hasPages() ? (($dokumens->currentPage() - 1) * $dokumens->perPage() + $loop->iteration) : $loop->iteration }}</td>
          <td>{{ $dokumen->nomor_agenda }}</td>
          <td>{{ $dokumen->nomor_spp }}</td>
          <td class="uraian-column">{{ $dokumen->uraian_spp ?? '-' }}</td>
          <td>
            <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
          </td>
          <td>
            @if($dokumen->department_returned_at)
              <small>{{ $dokumen->department_returned_at->format('d/m/Y H:i') }}</small>
            @else
              <small>-</small>
            @endif
          </td>
          <td class="alasan-column">
            <div style="
              background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
              padding: 10px 14px;
              border-radius: 8px;
              border-left: 3px solid #889717;
              word-wrap: break-word;
              overflow-wrap: break-word;
              word-break: break-word;
              white-space: normal;
              line-height: 1.6;
              font-size: 12px;
              color: #555;
              min-height: 40px;
              display: block;
            ">
              {{ $dokumen->department_return_reason ?? '-' }}
            </div>
          </td>
        </tr>
        <tr class="detail-row" id="detail-{{ $dokumen->id }}">
          <td colspan="7">
            <div class="detail-content">
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">Tanggal Masuk</span>
                  <span class="detail-value">{{ $dokumen->tanggal_masuk->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Bulan</span>
                  <span class="detail-value">{{ $dokumen->bulan }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Tahun</span>
                  <span class="detail-value">{{ $dokumen->tahun }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">No SPP</span>
                  <span class="detail-value">{{ $dokumen->nomor_spp }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Tanggal SPP</span>
                  <span class="detail-value">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Uraian SPP</span>
                  <span class="detail-value">{{ $dokumen->uraian_spp }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Nilai Rp</span>
                  <span class="detail-value">{{ $dokumen->formatted_nilai_rupiah }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Kategori</span>
                  <span class="detail-value">{{ $dokumen->kategori }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Jenis Dokumen</span>
                  <span class="detail-value">{{ $dokumen->jenis_dokumen }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">SubBagian Pekerjaan</span>
                  <span class="detail-value">{{ $dokumen->jenis_sub_pekerjaan ?? '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Dibayar Kepada</span>
                  <span class="detail-value">{{ $dokumen->dibayar_kepada ?? '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">No Berita Acara</span>
                  <span class="detail-value">{{ $dokumen->no_berita_acara ?? '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Tanggal Berita Acara</span>
                  <span class="detail-value">{{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">No SPK</span>
                  <span class="detail-value">{{ $dokumen->no_spk ?? '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Tanggal SPK</span>
                  <span class="detail-value">{{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Tanggal Akhir SPK</span>
                  <span class="detail-value">{{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">No PO</span>
                  <span class="detail-value">
                    @if($dokumen->dokumenPos->count() > 0)
                      {{ $dokumen->dokumenPos->pluck('nomor_po')->join(', ') }}
                    @else
                      -
                    @endif
                  </span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">No PR</span>
                  <span class="detail-value">
                    @if($dokumen->dokumenPrs->count() > 0)
                      {{ $dokumen->dokumenPrs->pluck('nomor_pr')->join(', ') }}
                    @else
                      -
                    @endif
                  </span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Nomor Miro</span>
                  <span class="detail-value">{{ $dokumen->nomor_miro ?? '-' }}</span>
                </div>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center" style="padding: 40px;">
            <i class="fa-solid fa-undo" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
            <p style="color: #999; font-size: 14px;">Belum ada dokumen yang dikembalikan ke Ibu Tarapul</p>
            <p style="color: #bbb; font-size: 12px;">Dokumen yang dikembalikan ke Ibu Tarapul akan tampil di sini</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>


<!-- Pagination -->
@if(isset($dokumens) && $dokumens->hasPages())
  <div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
      Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }} dokumen
    </div>
    {{ $dokumens->links() }}
  </div>
@endif

<script>
function toggleDetail(rowId) {
  const detailRow = document.getElementById('detail-' + rowId);
  const chevron = document.getElementById('chevron-' + rowId);

  detailRow.classList.toggle('show');
  chevron.classList.toggle('rotate');
}
</script>

@endsection




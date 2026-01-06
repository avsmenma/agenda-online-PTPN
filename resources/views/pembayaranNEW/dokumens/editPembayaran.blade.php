@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .form-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 5px 20px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    margin-bottom: 30px;
  }

  .form-title {
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .form-title span {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  /* Accordion Section Styles */
  .accordion-section {
    background: white;
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.08);
    transition: all 0.3s ease;
  }

  .accordion-section:hover {
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.12);
  }

  .accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    cursor: pointer;
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    border-left: 4px solid #889717;
    user-select: none;
    transition: all 0.3s ease;
  }

  .accordion-header:hover {
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
  }

  .accordion-header.active {
    background: linear-gradient(90deg, rgba(8, 62, 64, 0.08) 0%, transparent 100%);
    border-left-color: #083E40;
  }

  .accordion-title {
    font-size: 16px;
    font-weight: 600;
    color: #083E40;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .accordion-title i {
    font-size: 18px;
    color: #889717;
  }

  .accordion-icon {
    font-size: 20px;
    color: #083E40;
    transition: transform 0.3s ease;
  }

  .accordion-header.active .accordion-icon {
    transform: rotate(180deg);
  }

  .accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
  }

  .accordion-content.active {
    max-height: 2000px;
  }

  .accordion-body {
    padding: 24px;
    border-top: 1px solid rgba(8, 62, 64, 0.1);
  }

  .section-badge {
    display: inline-block;
    padding: 4px 12px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 8px;
    color: #083E40;
    letter-spacing: 0.3px;
  }

  .form-group input,
  .form-group textarea,
  .form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background-color: #ffffff;
  }

  .form-group input:focus,
  .form-group textarea:focus,
  .form-group select:focus {
    outline: none;
    border-color: #28a745;
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
    background-color: #f8fff8;
  }

  .form-group input:hover,
  .form-group textarea:hover,
  .form-group select:hover {
    border-color: rgba(8, 62, 64, 0.25);
  }

  .form-group textarea {
    min-height: 100px;
    resize: vertical;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }

  .form-row-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }

  .form-row-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 40px;
    padding-top: 24px;
    border-top: 2px solid rgba(8, 62, 64, 0.1);
  }

  .btn-reset {
    padding: 12px 32px;
    border: 2px solid rgba(8, 62, 64, 0.2);
    background-color: white;
    color: #083E40;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
    text-decoration: none;
  }

  .btn-reset:hover {
    background-color: #083E40;
    color: white;
    border-color: #083E40;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-submit {
    padding: 12px 32px;
    border: none;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(40, 167, 69, 0.3);
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
  }

  .btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
    transition: left 0.5s ease;
  }

  .btn-submit:hover::before {
    left: 100%;
  }

  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(40, 167, 69, 0.4);
  }

  .btn-submit:active {
    transform: translateY(0);
  }

  .optional-label {
    color: #889717;
    font-weight: 500;
    font-size: 12px;
    opacity: 0.8;
  }

  .info-alert {
    background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%);
    border-left: 4px solid #2196F3;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
    display: flex;
    align-items: start;
    gap: 12px;
  }

  .info-alert i {
    color: #2196F3;
    font-size: 20px;
    margin-top: 2px;
  }

  .info-alert-content {
    flex: 1;
  }

  .info-alert-title {
    font-weight: 600;
    color: #1976D2;
    margin-bottom: 4px;
    font-size: 14px;
  }

  .info-alert-text {
    color: #424242;
    font-size: 13px;
    line-height: 1.5;
    margin: 0;
  }

  /* Read-only info item styling */
  .info-item {
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #083E40;
  }

  .info-item.highlight {
    background: linear-gradient(135deg, #fff8e1 0%, #fffde7 100%);
    border-left-color: #889717;
  }

  .info-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
  }

  .info-value {
    font-size: 14px;
    font-weight: 500;
    color: #083E40;
    word-break: break-word;
  }

  .info-value.currency {
    font-weight: 700;
    color: #28a745;
    font-size: 16px;
  }

  /* Perpajakan Section Special Styling */
  .perpajakan-section {
    border: 2px solid #ffc107;
  }

  .perpajakan-section .accordion-header {
    background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, transparent 100%);
    border-left-color: #ffc107;
  }

  .perpajakan-section .accordion-header:hover {
    background: linear-gradient(90deg, rgba(255, 193, 7, 0.15) 0%, transparent 100%);
  }

  .perpajakan-section .accordion-title i {
    color: #ffc107;
  }

  .perpajakan-badge {
    display: inline-block;
    padding: 4px 12px;
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: white;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 8px;
  }

  /* Akutansi Section Special Styling */
  .akutansi-section {
    border: 2px solid #007bff;
  }

  .akutansi-section .accordion-header {
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.1) 0%, transparent 100%);
    border-left-color: #007bff;
  }

  .akutansi-section .accordion-header:hover {
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.15) 0%, transparent 100%);
  }

  .akutansi-section .accordion-title i {
    color: #007bff;
  }

  .akutansi-badge {
    display: inline-block;
    padding: 4px 12px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 8px;
  }

  /* Pembayaran Section Special Styling */
  .pembayaran-section {
    border: 2px solid #28a745;
  }

  .pembayaran-section .accordion-header {
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.1) 0%, transparent 100%);
    border-left-color: #28a745;
  }

  .pembayaran-section .accordion-header:hover {
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.15) 0%, transparent 100%);
  }

  .pembayaran-section .accordion-title i {
    color: #28a745;
  }

  .pembayaran-badge {
    display: inline-block;
    padding: 4px 12px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 8px;
  }

  /* Status Badge */
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }

  .status-badge.belum {
    background: #fff3cd;
    color: #856404;
  }

  .status-badge.siap {
    background: #cce5ff;
    color: #004085;
  }

  .status-badge.sudah {
    background: #d4edda;
    color: #155724;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .form-row,
    .form-row-3,
    .form-row-4 {
      grid-template-columns: 1fr;
    }

    .accordion-header {
      padding: 14px 16px;
    }

    .accordion-body {
      padding: 16px;
    }

    .form-actions {
      flex-direction: column;
    }

    .btn-reset,
    .btn-submit {
      width: 100%;
      text-align: center;
    }
  }
</style>

<div class="card mb-4 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 16px; box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05); border: 1px solid rgba(8, 62, 64, 0.08);">
    <h2 class="form-title">Edit <span>Dokumen - Team Pembayaran</span></h2>
</div>

<!-- Info Alert -->
<div class="info-alert">
  <i class="fa-solid fa-circle-info"></i>
  <div class="info-alert-content">
    <div class="info-alert-title">Informasi Edit Dokumen</div>
    <p class="info-alert-text">
      Sebagai Team Pembayaran, Anda dapat mengisi data pembayaran. Data dari role lain (Perpajakan, Akutansi) ditampilkan sebagai referensi dan tidak dapat diedit.
    </p>
  </div>
</div>

<div class="form-container">
  <form action="{{ route('documents.pembayaran.update', $dokumen->id) }}" method="POST" id="editForm">
    @csrf
    @method('PUT')

    <!-- Section 1: Informasi Dasar Dokumen (Read-Only) -->
    <div class="accordion-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-file-lines"></i>
          <span>Informasi Dasar Dokumen</span>
          <span class="section-badge" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);">Readonly</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row">
            <div class="info-item">
              <div class="info-label">Nomor Agenda</div>
              <div class="info-value">{{ $dokumen->nomor_agenda ?? '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Nomor SPP</div>
              <div class="info-value">{{ $dokumen->nomor_spp ?? '-' }}</div>
            </div>
          </div>

          <div class="form-row" style="margin-top: 15px;">
            <div class="info-item">
              <div class="info-label">Tanggal Masuk</div>
              <div class="info-value">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Tanggal SPP</div>
              <div class="info-value">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</div>
            </div>
          </div>

          <div class="form-row" style="margin-top: 15px;">
            <div class="info-item highlight">
              <div class="info-label">Nilai Rupiah</div>
              <div class="info-value currency">Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="info-item highlight">
              <div class="info-label">Dibayar Kepada</div>
              @php
                $dibayarKepadaValue = '';
                if ($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0) {
                    $dibayarKepadaValue = $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ');
                } else {
                    $dibayarKepadaValue = $dokumen->dibayar_kepada ?? '-';
                }
              @endphp
              <div class="info-value">{{ $dibayarKepadaValue }}</div>
            </div>
          </div>

          <div class="form-row-3" style="margin-top: 15px;">
            <div class="info-item">
              <div class="info-label">Kategori</div>
              <div class="info-value">{{ $dokumen->kategori ?? '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Jenis Dokumen</div>
              <div class="info-value">{{ $dokumen->jenis_dokumen ?? '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Jenis Sub Pekerjaan</div>
              <div class="info-value">{{ $dokumen->jenis_sub_pekerjaan ?? '-' }}</div>
            </div>
          </div>

          <div style="margin-top: 15px;">
            <div class="info-item">
              <div class="info-label">Uraian SPP</div>
              <div class="info-value">{{ $dokumen->uraian_spp ?? '-' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Informasi Perpajakan (Read-Only, jika ada) -->
    @if(isset($hasPerpajakanData) && $hasPerpajakanData)
    <div class="accordion-section perpajakan-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-file-invoice-dollar"></i>
          <span>Informasi Perpajakan</span>
          <span class="perpajakan-badge">DATA DARI PERPAJAKAN</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row-3">
            <div class="info-item">
              <div class="info-label">Status Perpajakan</div>
              <div class="info-value">{{ $dokumen->status_perpajakan ?? '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">NPWP</div>
              <div class="info-value">{{ $dokumen->npwp ?? '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">No Faktur</div>
              <div class="info-value">{{ $dokumen->no_faktur ?? '-' }}</div>
            </div>
          </div>

          <div class="form-row-3" style="margin-top: 15px;">
            <div class="info-item">
              <div class="info-label">Tanggal Faktur</div>
              <div class="info-value">{{ $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Tgl. Selesai Verifikasi Pajak</div>
              <div class="info-value">{{ $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">No Invoice</div>
              <div class="info-value">{{ $dokumen->no_invoice ?? '-' }}</div>
            </div>
          </div>

          <div class="form-row-3" style="margin-top: 15px;">
            <div class="info-item">
              <div class="info-label">Jenis PPh</div>
              <div class="info-value">{{ $dokumen->jenis_pph ?? '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">DPP PPh</div>
              <div class="info-value">{{ $dokumen->dpp_pph ? 'Rp ' . number_format($dokumen->dpp_pph, 0, ',', '.') : '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">PPN Terhutang</div>
              <div class="info-value">{{ $dokumen->ppn_terhutang ? 'Rp ' . number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- Section 3: Informasi Akutansi (Read-Only, jika ada) -->
    @if(isset($hasAkutansiData) && $hasAkutansiData)
    <div class="accordion-section akutansi-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-calculator"></i>
          <span>Informasi Akutansi</span>
          <span class="akutansi-badge">DATA DARI AKUTANSI</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row">
            <div class="info-item">
              <div class="info-label">Nomor MIRO</div>
              <div class="info-value">{{ $dokumen->nomor_miro ?? '-' }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Tanggal MIRO</div>
              <div class="info-value">{{ $dokumen->tanggal_miro ? $dokumen->tanggal_miro->format('d/m/Y') : '-' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- Section 4: Input Pembayaran (Editable) -->
    <div class="accordion-section pembayaran-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-money-check-dollar"></i>
          <span>Input Pembayaran</span>
          <span class="pembayaran-badge">KHUSUS PEMBAYARAN</span>
          @if($dokumen->status_pembayaran == 'belum_dibayar')
            <span class="status-badge belum" style="margin-left: 15px;">
              <i class="fa-solid fa-clock"></i> Belum Dibayar
            </span>
          @elseif($dokumen->status_pembayaran == 'siap_dibayar')
            <span class="status-badge siap" style="margin-left: 15px;">
              <i class="fa-solid fa-hourglass-half"></i> Siap Dibayar
            </span>
          @else
            <span class="status-badge sudah" style="margin-left: 15px;">
              <i class="fa-solid fa-check-circle"></i> Sudah Dibayar
            </span>
          @endif
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <!-- Petunjuk Pengisian -->
          <div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border: 1px solid #c8e6c9; border-radius: 10px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px;">
            <i class="fa-solid fa-info-circle" style="color: #28a745; font-size: 20px; margin-top: 2px;"></i>
            <div>
              <strong style="color: #1b5e20; display: block; margin-bottom: 4px;">Petunjuk Pengisian</strong>
              <span style="color: #2e7d32; font-size: 13px;">Isi minimal salah satu dari Tanggal Pembayaran atau Link Bukti Pembayaran untuk mengubah status dokumen menjadi "Sudah Dibayar".</span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>
                <i class="fa-solid fa-calendar-check" style="color: #28a745;"></i>
                Tanggal Pembayaran <span class="optional-label">(Opsional)</span>
              </label>
              <input type="date"
                     name="tanggal_dibayar"
                     value="{{ old('tanggal_dibayar', $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('Y-m-d') : '') }}"
                     placeholder="mm/dd/yyyy">
              <small style="display: block; margin-top: 5px; color: #6c757d; font-size: 11px;">
                <i class="fa-solid fa-info-circle"></i> Pilih tanggal ketika pembayaran dilakukan
              </small>
              @error('tanggal_dibayar')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>
                <i class="fa-solid fa-receipt" style="color: #28a745;"></i>
                Jenis Pembayaran <span class="optional-label">(Opsional)</span>
              </label>
              <select name="jenis_pembayaran">
                <option value="">Pilih Jenis Pembayaran</option>
                @if(isset($jenisPembayaranList) && $jenisPembayaranList->count() > 0)
                  @foreach($jenisPembayaranList as $jenisPembayaran)
                    <option value="{{ $jenisPembayaran->form_value ?? $jenisPembayaran->nama_jenis_pembayaran }}" 
                            {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == ($jenisPembayaran->form_value ?? $jenisPembayaran->nama_jenis_pembayaran) ? 'selected' : '' }}>
                      {{ $jenisPembayaran->display_name ?? $jenisPembayaran->nama_jenis_pembayaran }}
                    </option>
                  @endforeach
                @endif
              </select>
              @error('jenis_pembayaran')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-group">
            <label>
              <i class="fa-brands fa-google-drive" style="color: #4285F4;"></i>
              Link Google Drive Bukti Pembayaran <span class="optional-label">(Opsional)</span>
            </label>
            <input type="url"
                   name="link_bukti_pembayaran"
                   value="{{ old('link_bukti_pembayaran', $dokumen->link_bukti_pembayaran) }}"
                   placeholder="https://drive.google.com/file/d/...">
            <small style="display: block; margin-top: 5px; color: #6c757d; font-size: 11px;">
              <i class="fa-solid fa-info-circle"></i> Masukkan link Google Drive untuk bukti pembayaran (PDF/File)
            </small>
            @if($dokumen->link_bukti_pembayaran)
            <div style="margin-top: 10px; padding: 10px 15px; background: #e8f5e9; border-radius: 8px;">
              <i class="fa-brands fa-google-drive" style="color: #28a745;"></i>
              <a href="{{ $dokumen->link_bukti_pembayaran }}" target="_blank" style="margin-left: 8px; color: #28a745; font-size: 13px; text-decoration: underline;">
                <i class="fa-solid fa-external-link-alt"></i> Lihat Bukti Pembayaran
              </a>
            </div>
            @endif
            @error('link_bukti_pembayaran')
              <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label>
              <i class="fa-solid fa-comment" style="color: #28a745;"></i>
              Catatan Pembayaran <span class="optional-label">(Opsional)</span>
            </label>
            <textarea name="catatan_pembayaran" placeholder="Masukkan catatan tambahan jika diperlukan...">{{ old('catatan_pembayaran', $dokumen->catatan_pembayaran) }}</textarea>
            @error('catatan_pembayaran')
              <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <a href="{{ route('documents.pembayaran.index') }}" class="btn-reset">
        <i class="fa-solid fa-arrow-left me-2"></i>Batal
      </a>
      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-save me-2"></i>Simpan Pembayaran
      </button>
    </div>
  </form>
</div>

<script>
// Accordion Toggle
function toggleAccordion(header) {
  const content = header.nextElementSibling;
  const icon = header.querySelector('.accordion-icon');

  header.classList.toggle('active');
  content.classList.toggle('active');
}
</script>

@endsection
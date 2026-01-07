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
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
    background-color: #fffef8;
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

  /* Dynamic Field Styles */
  .dynamic-field {
    position: relative;
    padding-right: 80px;
  }

  .add-field-btn {
    position: absolute;
    right: 40px;
    top: 32px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #889717;
    background: linear-gradient(135deg, #ffffff 0%, #f9faf5 100%);
    color: #083E40;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.2);
  }

  .add-field-btn:hover {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 4px 16px rgba(136, 151, 23, 0.3);
  }

  .remove-field-btn {
    position: absolute;
    right: 0;
    top: 32px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #dc3545;
    background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
    color: #dc3545;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
  }

  .remove-field-btn:hover {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 16px rgba(220, 53, 69, 0.3);
  }

  .dynamic-field:first-of-type .remove-field-btn,
  .dynamic-field[data-field-type="po"]:first-of-type .remove-field-btn,
  .dynamic-field[data-field-type="pr"]:first-of-type .remove-field-btn {
    display: none !important;
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
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.3);
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
    box-shadow: 0 6px 24px rgba(8, 62, 64, 0.4), 0 2px 8px rgba(136, 151, 23, 0.3);
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

  /* Responsive Design */
  @media (max-width: 768px) {
    .form-row,
    .form-row-3 {
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
    <h2 class="form-title">Edit <span>Dokumen - Team Verifikasi</span></h2>
</div>

<!-- Info Alert -->
<div class="info-alert">
  <i class="fa-solid fa-circle-info"></i>
  <div class="info-alert-content">
    <div class="info-alert-title">Informasi Edit Dokumen</div>
    <p class="info-alert-text">
      Sebagai Team Verifikasi, Anda dapat mengedit semua data dokumen. Perubahan yang Anda lakukan akan tersimpan dan dapat dilihat oleh semua pihak terkait.
    </p>
  </div>
</div>

<div class="form-container">
  <form action="{{ route('documents.verifikasi.update', $dokumen->id) }}" method="POST" id="editForm">
    @csrf
    @method('PUT')

    <!-- Section 1: Informasi Dasar -->
    <div class="accordion-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-file-lines"></i>
          <span>Informasi Dasar Dokumen</span>
          <span class="section-badge">Wajib</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row">
            <div class="form-group">
              <label>Nomor Agenda</label>
              <input type="text" name="nomor_agenda" placeholder="Masukkan nomor agenda" value="{{ old('nomor_agenda', $dokumen->nomor_agenda) }}">
              @error('nomor_agenda')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Bulan</label>
              <select name="bulan">
                <option value="">Pilih Bulan</option>
                @foreach(['Januari', 'Februari', 'Maret', 'April', 'May', 'Juni', 'July', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                  <option value="{{ $bulan }}" {{ old('bulan', $dokumen->bulan) == $bulan ? 'selected' : '' }}>{{ $bulan }}</option>
                @endforeach
              </select>
              @error('bulan')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Tahun</label>
              <input type="number" name="tahun" placeholder="2025" value="{{ old('tahun', $dokumen->tahun) }}" min="2020" max="2030">
              @error('tahun')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Tanggal Masuk</label>
              <input type="datetime-local" name="tanggal_masuk" value="{{ old('tanggal_masuk', $dokumen->tanggal_masuk?->format('Y-m-d\TH:i') ?? '') }}">
              @error('tanggal_masuk')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label>Dibayar Kepada</label>
              @php
                // Get dibayar_kepada from relationship if available, otherwise use direct field
                $dibayarKepadaValue = old('dibayar_kepada');
                if (!$dibayarKepadaValue) {
                  if ($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0) {
                    $dibayarKepadaValue = $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ');
                  } else {
                    $dibayarKepadaValue = $dokumen->dibayar_kepada ?? '';
                  }
                }
              @endphp
              <input type="text" name="dibayar_kepada" value="{{ $dibayarKepadaValue }}" placeholder="Nama penerima">
              @error('dibayar_kepada')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>No Berita Acara</label>
              <input type="text" name="no_berita_acara" placeholder="5TEP/BAST/49/SP.30/XI/2024" value="{{ old('no_berita_acara', $dokumen->no_berita_acara) }}">
              @error('no_berita_acara')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Tanggal Berita Acara</label>
              <input type="date" name="tanggal_berita_acara" value="{{ old('tanggal_berita_acara', $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('Y-m-d') : '') }}">
              @error('tanggal_berita_acara')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label>No SPK</label>
              <input type="text" name="no_spk" placeholder="5TEP/SP/Sawit/30/IX/2024" value="{{ old('no_spk', $dokumen->no_spk) }}">
              @error('no_spk')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Tanggal SPK</label>
              <input type="date" name="tanggal_spk" value="{{ old('tanggal_spk', $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('Y-m-d') : '') }}">
              @error('tanggal_spk')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Tanggal Berakhir SPK</label>
              <input type="date" name="tanggal_berakhir_spk" value="{{ old('tanggal_berakhir_spk', $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('Y-m-d') : '') }}">
              @error('tanggal_berakhir_spk')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Informasi SPP -->
    <div class="accordion-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-file-invoice-dollar"></i>
          <span>Informasi SPP</span>
          <span class="section-badge">Wajib</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row">
            <div class="form-group">
              <label>Nomor SPP</label>
              <input type="text" name="nomor_spp" placeholder="123/M/SPP/13/XII/2025" value="{{ old('nomor_spp', $dokumen->nomor_spp) }}">
              @error('nomor_spp')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Tanggal SPP</label>
              <input type="datetime-local" name="tanggal_spp" value="{{ old('tanggal_spp', $dokumen->tanggal_spp?->format('Y-m-d\TH:i') ?? '') }}">
              @error('tanggal_spp')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-group">
            <label>Uraian SPP</label>
            <textarea name="uraian_spp" placeholder="Permintaan permohonan pembayaran...">{{ old('uraian_spp', $dokumen->uraian_spp) }}</textarea>
            @error('uraian_spp')
              <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label>Nilai Rupiah</label>
            <input type="text" name="nilai_rupiah" id="edit-nilai-rupiah" placeholder="Masukkan nilai rupiah (contoh: 120000000)" value="{{ old('nilai_rupiah', $dokumen->nilai_rupiah ? number_format($dokumen->nilai_rupiah, 0, '', '.') : '') }}">
            @error('nilai_rupiah')
              <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label>Ejaan Nilai Rupiah</label>
            <input type="text" name="ejaan_nilai_rupiah" id="edit-ejaan-nilai-rupiah" placeholder="Ejaan akan terisi otomatis" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: Kriteria CF, Sub Kriteria, Item Sub Kriteria -->
    <div class="accordion-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-tags"></i>
          <span>Kriteria CF, Sub Kriteria, Item Sub Kriteria</span>
          <span class="section-badge">Wajib</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          @if(isset($isDropdownAvailable) && $isDropdownAvailable && $kategoriKriteria->count() > 0)
          <!-- Mode Dropdown (jika database cash_bank tersedia) -->
          <div class="form-row" id="dropdown-mode">
            <div class="form-group">
              <label>Kriteria CF</label>
              <select id="kriteria_cf" name="kriteria_cf">
                <option value="">Pilih Kriteria CF</option>
                @foreach($kategoriKriteria as $kategori)
                  <option value="{{ $kategori->id_kategori_kriteria }}" {{ old('kriteria_cf', $selectedKriteriaCfId ?? '') == $kategori->id_kategori_kriteria ? 'selected' : '' }}>
                    {{ $kategori->nama_kriteria }}
                  </option>
                @endforeach
              </select>
              @error('kriteria_cf')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Sub Kriteria</label>
              <select id="sub_kriteria" name="sub_kriteria">
                <option value="">Pilih Kriteria CF terlebih dahulu</option>
              </select>
              @error('sub_kriteria')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Item Sub Kriteria</label>
              <select id="item_sub_kriteria" name="item_sub_kriteria">
                <option value="">Pilih Sub Kriteria terlebih dahulu</option>
              </select>
              @error('item_sub_kriteria')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Jenis Pembayaran</label>
              @if(isset($isJenisPembayaranAvailable) && $isJenisPembayaranAvailable && $jenisPembayaranList->count() > 0)
                <select name="jenis_pembayaran" id="jenis_pembayaran">
                  <option value="">Pilih Jenis Pembayaran</option>
                  @foreach($jenisPembayaranList as $jenisPembayaran)
                    <option value="{{ $jenisPembayaran->form_value }}" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == $jenisPembayaran->form_value ? 'selected' : '' }}>
                      {{ $jenisPembayaran->display_name }}
                    </option>
                  @endforeach
                </select>
              @else
                <input type="text" name="jenis_pembayaran" id="jenis_pembayaran" value="{{ old('jenis_pembayaran', $dokumen->jenis_pembayaran ?? '') }}" placeholder="Masukkan Jenis Pembayaran">
                <small style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
                  <i class="fas fa-info-circle"></i> Database jenis pembayaran tidak tersedia. Silakan isi manual sesuai kebutuhan.
                </small>
              @endif
              @error('jenis_pembayaran')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>
          @else
          <!-- Mode Input Manual (jika database cash_bank tidak tersedia) -->
          <div class="form-row" id="manual-mode">
            <div class="form-group">
              <label>Kategori</label>
              <input type="text" name="kategori" id="kategori" value="{{ old('kategori', $dokumen->kategori ?? '') }}" placeholder="Masukkan Kategori">
              <small style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
                <i class="fas fa-info-circle"></i> Database cash_bank tidak tersedia. Silakan isi manual sesuai kebutuhan.
              </small>
              @error('kategori')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Jenis Dokumen</label>
              <input type="text" name="jenis_dokumen" id="jenis_dokumen" value="{{ old('jenis_dokumen', $dokumen->jenis_dokumen ?? '') }}" placeholder="Masukkan Jenis Dokumen">
              @error('jenis_dokumen')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Jenis Sub Pekerjaan</label>
              <input type="text" name="jenis_sub_pekerjaan" id="jenis_sub_pekerjaan" value="{{ old('jenis_sub_pekerjaan', $dokumen->jenis_sub_pekerjaan ?? '') }}" placeholder="Masukkan Jenis Sub Pekerjaan">
              @error('jenis_sub_pekerjaan')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Jenis Pembayaran</label>
              <input type="text" name="jenis_pembayaran" id="jenis_pembayaran" value="{{ old('jenis_pembayaran', $dokumen->jenis_pembayaran ?? '') }}" placeholder="Masukkan Jenis Pembayaran">
              <small style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
                <i class="fas fa-info-circle"></i> Database jenis pembayaran tidak tersedia. Silakan isi manual sesuai kebutuhan.
              </small>
              @error('jenis_pembayaran')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <!-- Hidden fields untuk dropdown (nullable) -->
            <input type="hidden" name="kriteria_cf" value="">
            <input type="hidden" name="sub_kriteria" value="">
            <input type="hidden" name="item_sub_kriteria" value="">
          </div>
          @endif
          <div class="form-row">
            <div class="form-group">
              <label>Kebun</label>
              <select name="kebun">
                <option value="">Pilih Kebun</option>
                @php
                  $kebunOptions = [
                    'KEBUN-UNIT', 'REGION OFFICE', 'UNIT GRUP KALBAR', 'GUNUNG MELIAU',
                    'PKS GUNME', 'SUNGAI DEKAN', 'RIMBA BELIAN', 'PKS RIMBA BELIA',
                    'GUNUNG MAS', 'SINTANG', 'NGABANG', 'PKS NGABANG',
                    'PARINDU', 'PKS PARINDU', 'KEMBAYAN', 'PKS KEMBAYAN',
                    'PPPBB', 'UNIT GRUP KALSEL/TENG', 'DANAU SALAK', 'TAMBARANGAN',
                    'BATULICIN', 'PELAIHARI', 'PKS PELAIHARI', 'KUMAI',
                    'PKS PAMUKAN', 'PAMUKAN', 'PRYBB', 'RAREN BATUAH',
                    'UNIT GRUP KALTIM', 'TABARA', 'TAJATI', 'PANDAWA',
                    'LONGKALI', 'PKS SAMUNTAI', 'PKS LONG PINANG', 'KP JAKARTA',
                    'KP BALIKPAPAN'
                  ];
                  $currentKebun = old('kebun', $dokumen->kebun);
                  $currentKebunClean = preg_replace('/^\d+\s+/', '', $currentKebun);
                @endphp
                @foreach($kebunOptions as $kebun)
                  <option value="{{ $kebun }}" {{ ($currentKebun == $kebun || $currentKebunClean == $kebun) ? 'selected' : '' }}>{{ $kebun }}</option>
                @endforeach
              </select>
              @error('kebun')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 4: Nomor PO & PR -->
    <div class="accordion-section">
      <div class="accordion-header" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-hashtag"></i>
          <span>Nomor PO & PR</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content">
        <div class="accordion-body">
          <!-- Nomor PO -->
          <div id="po-container">
            @if($dokumen->dokumenPos->count() > 0)
              @foreach($dokumen->dokumenPos as $index => $po)
              <div class="form-group dynamic-field" data-field-type="po">
                <label>Nomor PO</label>
                <input type="text" placeholder="Masukkan nomor PO" name="nomor_po[]" value="{{ old('nomor_po.' . $index, $po->nomor_po) }}">
                <button type="button" class="add-field-btn">+</button>
                <button type="button" class="remove-field-btn" style="{{ $loop->first ? 'display: none;' : 'display: flex;' }}">−</button>
              </div>
              @endforeach
            @else
            <div class="form-group dynamic-field" data-field-type="po">
              <label>Nomor PO</label>
              <input type="text" placeholder="Masukkan nomor PO" name="nomor_po[]" value="{{ old('nomor_po.0') }}">
              <button type="button" class="add-field-btn">+</button>
              <button type="button" class="remove-field-btn" style="display: none;">−</button>
            </div>
            @endif
          </div>

          <!-- Nomor PR -->
          <div id="pr-container">
            @if($dokumen->dokumenPrs->count() > 0)
              @foreach($dokumen->dokumenPrs as $index => $pr)
              <div class="form-group dynamic-field" data-field-type="pr">
                <label>Nomor PR</label>
                <input type="text" placeholder="Masukkan nomor PR" name="nomor_pr[]" value="{{ old('nomor_pr.' . $index, $pr->nomor_pr) }}">
                <button type="button" class="add-field-btn">+</button>
                <button type="button" class="remove-field-btn" style="{{ $loop->first ? 'display: none;' : 'display: flex;' }}">−</button>
              </div>
              @endforeach
            @else
            <div class="form-group dynamic-field" data-field-type="pr">
              <label>Nomor PR</label>
              <input type="text" placeholder="Masukkan nomor PR" name="nomor_pr[]" value="{{ old('nomor_pr.0') }}">
              <button type="button" class="add-field-btn">+</button>
              <button type="button" class="remove-field-btn" style="display: none;">−</button>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <a href="{{ route('documents.verifikasi.index') }}" class="btn-reset" style="text-decoration: none; display: inline-block;">Batal</a>
      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-save me-2"></i>Update Dokumen
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

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
  // Data untuk cascading dropdown (hanya jika dropdown mode tersedia)
  @if(isset($isDropdownAvailable) && $isDropdownAvailable && $kategoriKriteria->count() > 0)
  const subKriteriaData = @json($subKriteria);
  const itemSubKriteriaData = @json($itemSubKriteria);

  // Function to update sub kriteria dropdown
  function updateSubKriteria(kategoriKriteriaId, selectedValue = null) {
    const subKriteriaSelect = document.getElementById('sub_kriteria');
    const itemSubKriteriaSelect = document.getElementById('item_sub_kriteria');

    if (!subKriteriaSelect || !itemSubKriteriaSelect) {
      return;
    }

    // Clear existing options
    subKriteriaSelect.innerHTML = '<option value="">Pilih Sub Kriteria</option>';
    itemSubKriteriaSelect.innerHTML = '<option value="">Pilih Sub Kriteria terlebih dahulu</option>';

    if (!kategoriKriteriaId) {
      return;
    }

    // Filter sub kriteria berdasarkan id_kategori_kriteria
    const filteredSubKriteria = subKriteriaData.filter(sub => 
      sub.id_kategori_kriteria == kategoriKriteriaId
    );

    // Populate sub kriteria options
    filteredSubKriteria.forEach(sub => {
      const option = document.createElement('option');
      option.value = sub.id_sub_kriteria;
      option.textContent = sub.nama_sub_kriteria;
      if (selectedValue && selectedValue == sub.id_sub_kriteria) {
        option.selected = true;
      }
      subKriteriaSelect.appendChild(option);
    });
  }

  // Function to update item sub kriteria dropdown
  function updateItemSubKriteria(subKriteriaId, selectedValue = null) {
    const itemSubKriteriaSelect = document.getElementById('item_sub_kriteria');

    if (!itemSubKriteriaSelect) {
      return;
    }

    // Clear existing options
    itemSubKriteriaSelect.innerHTML = '<option value="">Pilih Item Sub Kriteria</option>';

    if (!subKriteriaId) {
      return;
    }

    // Filter item sub kriteria berdasarkan id_sub_kriteria
    const filteredItemSubKriteria = itemSubKriteriaData.filter(item => 
      item.id_sub_kriteria == subKriteriaId
    );

    // Populate item sub kriteria options
    filteredItemSubKriteria.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id_item_sub_kriteria;
      option.textContent = item.nama_item_sub_kriteria;
      if (selectedValue && selectedValue == item.id_item_sub_kriteria) {
        option.selected = true;
      }
      itemSubKriteriaSelect.appendChild(option);
    });
  }
  @endif

  // Initialize dropdowns if values already selected (hanya jika dropdown mode tersedia)
  @if(isset($isDropdownAvailable) && $isDropdownAvailable && $kategoriKriteria->count() > 0)
  const kriteriaCfSelect = document.getElementById('kriteria_cf');
  const subKriteriaSelect = document.getElementById('sub_kriteria');
  const itemSubKriteriaSelect = document.getElementById('item_sub_kriteria');
  
  if (kriteriaCfSelect && subKriteriaSelect && itemSubKriteriaSelect) {
    const oldKriteriaCf = '{{ old("kriteria_cf", $selectedKriteriaCfId ?? "") }}';
    const oldSubKriteria = '{{ old("sub_kriteria", $selectedSubKriteriaId ?? "") }}';
    const oldItemSubKriteria = '{{ old("item_sub_kriteria", $selectedItemSubKriteriaId ?? "") }}';

    if (oldKriteriaCf && oldKriteriaCf !== '') {
      updateSubKriteria(oldKriteriaCf, oldSubKriteria);
      if (oldSubKriteria && oldSubKriteria !== '') {
        updateItemSubKriteria(oldSubKriteria, oldItemSubKriteria);
      }
    }

    // Event listener untuk dropdown kriteria CF
    kriteriaCfSelect.addEventListener('change', function() {
      updateSubKriteria(this.value);
      // Reset item sub kriteria
      itemSubKriteriaSelect.innerHTML = '<option value="">Pilih Sub Kriteria terlebih dahulu</option>';
    });

    // Event listener untuk dropdown sub kriteria
    subKriteriaSelect.addEventListener('change', function() {
      updateItemSubKriteria(this.value);
    });
  }
  @endif

  // Event delegation untuk tombol tambah dan kurang
  document.addEventListener('click', function(e) {
    // Handle tombol tambah (+)
    if (e.target.classList.contains('add-field-btn')) {
      e.preventDefault();
      const fieldGroup = e.target.closest('.dynamic-field');
      const newField = fieldGroup.cloneNode(true);

      // Reset nilai input
      newField.querySelector('input').value = '';

      // Show remove button on new field
      const newRemoveBtn = newField.querySelector('.remove-field-btn');
      if (newRemoveBtn) {
        newRemoveBtn.style.display = 'flex';
      }

      // Hide remove button on first field
      const fieldType = fieldGroup.getAttribute('data-field-type');
      const allFields = document.querySelectorAll(`[data-field-type="${fieldType}"]`);
      if (allFields.length >= 1) {
        const firstField = allFields[0];
        const firstRemoveBtn = firstField.querySelector('.remove-field-btn');
        if (firstRemoveBtn) {
          firstRemoveBtn.style.display = 'none';
        }
      }

      // Insert after current field
      fieldGroup.parentNode.insertBefore(newField, fieldGroup.nextSibling);
    }

    // Handle tombol kurang (-)
    if (e.target.classList.contains('remove-field-btn')) {
      e.preventDefault();
      const fieldGroup = e.target.closest('.dynamic-field');
      const fieldType = fieldGroup.getAttribute('data-field-type');
      const allFields = document.querySelectorAll(`[data-field-type="${fieldType}"]`);

      // Only remove if there's more than one field
      if (allFields.length > 1) {
        fieldGroup.remove();

        // Hide remove button on first field if only one remains
        const remainingFields = document.querySelectorAll(`[data-field-type="${fieldType}"]`);
        if (remainingFields.length === 1) {
          const firstRemoveBtn = remainingFields[0].querySelector('.remove-field-btn');
          if (firstRemoveBtn) {
            firstRemoveBtn.style.display = 'none';
          }
        }
      }
    }
  });

  // Function to convert number to Indonesian terbilang
  function terbilangRupiah(number) {
    number = parseFloat(number.toString().replace(/\./g, '')) || 0;
    
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

    if (number == 0) {
      return '';
    }

    // Handle ratusan
    if (number >= 100) {
      const ratus = Math.floor(number / 100);
      if (ratus == 1) {
        hasil += 'seratus ';
      } else {
        hasil += angka[ratus] + ' ratus ';
      }
      number = number % 100;
    }

    // Handle puluhan dan satuan (0-99)
    if (number > 0) {
      if (number < 20) {
        hasil += angka[number] + ' ';
      } else {
        const puluhan = Math.floor(number / 10);
        const satuan = number % 10;
        
        if (puluhan == 1) {
          hasil += angka[10 + satuan] + ' ';
        } else {
          hasil += angka[puluhan] + ' puluh ';
          if (satuan > 0) {
            hasil += angka[satuan] + ' ';
          }
        }
      }
    }

    return hasil.trim();
  }

  // Format nilai rupiah input with dots and auto-generate ejaan
  const nilaiRupiahInput = document.getElementById('edit-nilai-rupiah');
  const ejaanRupiahInput = document.getElementById('edit-ejaan-nilai-rupiah');

  if (nilaiRupiahInput) {
    // Format on input
    nilaiRupiahInput.addEventListener('input', function() {
      // Format with dots
      let value = this.value.replace(/[^\d]/g, '');
      if (value) {
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.value = value;
      } else {
        this.value = '';
      }

      // Update ejaan
      if (ejaanRupiahInput) {
        const numericValue = value.replace(/\./g, '');
        if (numericValue && numericValue > 0) {
          ejaanRupiahInput.value = terbilangRupiah(numericValue);
        } else {
          ejaanRupiahInput.value = '';
        }
      }
    });

    // Format on paste
    nilaiRupiahInput.addEventListener('paste', function(e) {
      setTimeout(() => {
        let value = this.value.replace(/[^\d]/g, '');
        if (value) {
          value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
          this.value = value;
        } else {
          this.value = '';
        }

        // Update ejaan
        if (ejaanRupiahInput) {
          const numericValue = value.replace(/\./g, '');
          if (numericValue && numericValue > 0) {
            ejaanRupiahInput.value = terbilangRupiah(numericValue);
          } else {
            ejaanRupiahInput.value = '';
          }
        }
      }, 10);
    });

    // Format initial value if exists (untuk memastikan ejaan terisi saat halaman dimuat)
    if (nilaiRupiahInput.value) {
      // Parse nilai yang sudah terformat dengan dots
      const numericValue = nilaiRupiahInput.value.replace(/\./g, '');
      if (numericValue && numericValue > 0 && ejaanRupiahInput) {
        ejaanRupiahInput.value = terbilangRupiah(numericValue);
      }
      // Trigger input event untuk format dots
      nilaiRupiahInput.dispatchEvent(new Event('input'));
    }
  }

  // Remove format dots from nilai_rupiah before form submit
  document.querySelector('form').addEventListener('submit', function(e) {
    const nilaiRupiahInput = document.getElementById('edit-nilai-rupiah');
    if (nilaiRupiahInput && nilaiRupiahInput.value) {
      // Remove all dots before submitting
      nilaiRupiahInput.value = nilaiRupiahInput.value.replace(/\./g, '');
    }
    
    // Remove ejaan_nilai_rupiah from form submission (it's readonly/display only)
    const ejaanInput = document.getElementById('edit-ejaan-nilai-rupiah');
    if (ejaanInput) {
      ejaanInput.disabled = true; // Disable so it won't be submitted
    }
  });

}); // End DOMContentLoaded
</script>

@endsection

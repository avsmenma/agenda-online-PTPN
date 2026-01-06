@extends('layouts/app')
@section('content')

<style>
  .form-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 5px 20px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
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

  .section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
    margin-top: 30px;
    padding-bottom: 12px;
    padding-left: 12px;
    border-left: 4px solid #889717;
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    color: #083E40;
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

  .form-row-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }

  .dynamic-field {
    position: relative;
    padding-right: 80px;
    margin-bottom: 15px;
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
  .dynamic-field[data-field-type="pr"]:first-of-type .remove-field-btn,
  .dynamic-field[data-field-type="dibayar_kepada"]:first-of-type .remove-field-btn {
    display: none !important;
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 40px;
  }

  .btn-back {
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
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .btn-back:hover {
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

  .info-box {
    background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
    border: 1px solid rgba(136, 151, 23, 0.2);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #083E40;
  }

  .info-box i {
    color: #889717;
    margin-right: 8px;
  }

  .dokumen-info {
    background: linear-gradient(135deg, #fff9c4 0%, #ffed9a 100%);
    border: 1px solid rgba(255, 193, 7, 0.2);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #856404;
  }

  .dokumen-info strong {
    color: #664d03;
  }
</style>

<div class="card mb-4 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 16px; box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05); border: 1px solid rgba(8, 62, 64, 0.08);">
    <h2 class="form-title">Edit <span>Dokumen</span></h2>
</div>

<!-- Dokumen Information -->
<div class="dokumen-info">
  <strong>Informasi Dokumen:</strong><br>
  Nomor Agenda: {{ $dokumen->nomor_agenda }} |
  Status: {{ $dokumen->status }} |
  Dibuat: {{ $dokumen->tanggal_masuk->format('d/m/Y H:i') }}
</div>

<div class="form-container">
  <form action="{{ route('documents.update', $dokumen->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Input Dokumen Baru -->
    <div class="section-title">Edit Dokumen</div>

    <!-- Info Box -->
    <div class="info-box">
      <i class="fas fa-info-circle"></i>
      <strong>Informasi:</strong> Bulan dan tahun otomatis diambil dari tanggal SPP. Tanggal masuk tidak dapat diubah.
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Nomor Agenda</label>
        <input type="text" name="nomor_agenda" placeholder="Masukkan nomor agenda" value="{{ old('nomor_agenda', $dokumen->nomor_agenda) }}">
        @error('nomor_agenda')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Bagian</label>
        <select name="bagian">
          <option value="">Pilih Bagian</option>
          <option value="DPM" {{ old('bagian', $dokumen->bagian) == 'DPM' ? 'selected' : '' }}>DPM</option>
          <option value="SKH" {{ old('bagian', $dokumen->bagian) == 'SKH' ? 'selected' : '' }}>SKH</option>
          <option value="SDM" {{ old('bagian', $dokumen->bagian) == 'SDM' ? 'selected' : '' }}>SDM</option>
          <option value="TEP" {{ old('bagian', $dokumen->bagian) == 'TEP' ? 'selected' : '' }}>TEP</option>
          <option value="KPL" {{ old('bagian', $dokumen->bagian) == 'KPL' ? 'selected' : '' }}>KPL</option>
          <option value="AKN" {{ old('bagian', $dokumen->bagian) == 'AKN' ? 'selected' : '' }}>AKN</option>
          <option value="TAN" {{ old('bagian', $dokumen->bagian) == 'TAN' ? 'selected' : '' }}>TAN</option>
          <option value="PMO" {{ old('bagian', $dokumen->bagian) == 'PMO' ? 'selected' : '' }}>PMO</option>
        </select>
        @error('bagian')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Nama Pengirim Dokumen</label>
        <input type="text" name="nama_pengirim" placeholder="Masukkan nama pengirim dokumen" value="{{ old('nama_pengirim', $dokumen->nama_pengirim) }}" data-autocomplete="document-senders">
        @error('nama_pengirim')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Nomor SPP</label>
        <input type="text" name="nomor_spp" placeholder="123/M/SPP/13/XII/2025" value="{{ old('nomor_spp', $dokumen->nomor_spp) }}">
        @error('nomor_spp')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tanggal SPP</label>
        <input type="datetime-local" name="tanggal_spp" value="{{ old('tanggal_spp', $dokumen->tanggal_spp?->format('Y-m-d\TH:i') ?? '') }}">
        @error('tanggal_spp')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Uraian SPP</label>
        <textarea name="uraian_spp" placeholder="Permintaan permohonan pembayaran THR Pegawai/Pekerja Harian Lepas (PHL) Bulan Maret sampai dengan Desember 2024" data-autocomplete="document-descriptions">{{ old('uraian_spp', $dokumen->uraian_spp) }}</textarea>
        @error('uraian_spp')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Nilai Rupiah -->
    <div class="form-row">
      <div class="form-group">
        <label>Nilai Rupiah</label>
        <input type="text" name="nilai_rupiah" id="nilai_rupiah" placeholder="123456" value="{{ old('nilai_rupiah', $dokumen->nilai_rupiah ? number_format((float) $dokumen->nilai_rupiah, 0, ',', '.') : '') }}">
        @error('nilai_rupiah')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Ejaan Nilai Rupiah (Otomatis) -->
    <div class="form-group">
      <label>Ejaan Nilai Rupiah</label>
      <input type="text" name="ejaan_nilai_rupiah" id="ejaan_nilai_rupiah" placeholder="Ejaan akan terisi otomatis" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
    </div>

    <!-- Kriteria CF, Sub Kriteria, Item Sub Kriteria -->
    @if(isset($isDropdownAvailable) && $isDropdownAvailable && $kategoriKriteria->count() > 0)
    <!-- Mode Dropdown (jika database cash_bank tersedia) -->
    <div class="form-row-3" id="dropdown-mode">
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
    <div class="form-row-3" id="manual-mode">
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

    <!-- Kebun -->
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
            // Handle old values with numbers for backward compatibility
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

    <!-- Nomor PR dan PO -->
    <div class="section-title">Nomor PR & PO</div>

    <!-- Nomor PR (Opsional) -->
    <div class="form-group">
      <label>Nomor PR <span class="optional-label">(Opsional)</span></label>
      @foreach($dokumen->dokumenPrs as $index => $dokumenPR)
        <div class="dynamic-field" data-field-type="pr">
          <input type="text" placeholder="Masukkan nomor PR" name="nomor_pr[]" value="{{ $dokumenPR->nomor_pr }}" data-autocomplete="pr-numbers">
          @if($index == 0)
            <button type="button" class="add-field-btn">+</button>
          @else
            <button type="button" class="add-field-btn">+</button>
            <button type="button" class="remove-field-btn">−</button>
          @endif
        </div>
      @endforeach
      @if($dokumen->dokumenPrs->count() == 0)
        <div class="dynamic-field" data-field-type="pr">
          <input type="text" placeholder="Masukkan nomor PR" name="nomor_pr[]" data-autocomplete="pr-numbers">
          <button type="button" class="add-field-btn">+</button>
          <button type="button" class="remove-field-btn" style="display: none;">−</button>
        </div>
      @endif
    </div>

    <!-- Nomor PO (Opsional) -->
    <div class="form-group">
      <label>Nomor PO <span class="optional-label">(Opsional)</span></label>
      @foreach($dokumen->dokumenPos as $index => $dokumenPO)
        <div class="dynamic-field" data-field-type="po">
          <input type="text" placeholder="Masukkan nomor PO" name="nomor_po[]" value="{{ $dokumenPO->nomor_po }}" data-autocomplete="po-numbers">
          @if($index == 0)
            <button type="button" class="add-field-btn">+</button>
          @else
            <button type="button" class="add-field-btn">+</button>
            <button type="button" class="remove-field-btn">−</button>
          @endif
        </div>
      @endforeach
      @if($dokumen->dokumenPos->count() == 0)
        <div class="dynamic-field" data-field-type="po">
          <input type="text" placeholder="Masukkan nomor PO" name="nomor_po[]" data-autocomplete="po-numbers">
          <button type="button" class="add-field-btn">+</button>
          <button type="button" class="remove-field-btn" style="display: none;">−</button>
        </div>
      @endif
    </div>

    <!-- Dibayar Kepada (Dynamic seperti PO) -->
    <div class="section-title">Penerima Pembayaran</div>

    <div class="form-group">
      <label>Dibayar Kepada <span class="optional-label">(Bisa lebih dari 1)</span></label>
      @foreach($dokumen->dibayarKepadas as $index => $dibayarKepada)
        <div class="dynamic-field" data-field-type="dibayar_kepada">
          <input type="text" placeholder="Masukkan nama penerima" name="dibayar_kepada[]" value="{{ $dibayarKepada->nama_penerima }}" data-autocomplete="payment-recipients">
          @if($index == 0)
            <button type="button" class="add-field-btn">+</button>
          @else
            <button type="button" class="add-field-btn">+</button>
            <button type="button" class="remove-field-btn">−</button>
          @endif
        </div>
      @endforeach
      @if($dokumen->dibayarKepadas->count() == 0)
        <div class="dynamic-field" data-field-type="dibayar_kepada">
          <input type="text" placeholder="Masukkan nama penerima" name="dibayar_kepada[]" data-autocomplete="payment-recipients">
          <button type="button" class="add-field-btn">+</button>
          <button type="button" class="remove-field-btn" style="display: none;">−</button>
        </div>
      @endif
    </div>

    <!-- Berita Acara -->
    <div class="section-title">Dokumen Pendukung</div>

    <div class="form-row-3">
      <div class="form-group">
        <label>No Berita Acara</label>
        <input type="text" name="no_berita_acara" placeholder="5TEP/BAST/49/SP.30/XI/2024" value="{{ old('no_berita_acara', $dokumen->no_berita_acara) }}">
        @error('no_berita_acara')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Tanggal Berita Acara</label>
         <input type="date" name="tanggal_berita_acara" value="{{ old('tanggal_berita_acara', $dokumen->tanggal_berita_acara?->format('Y-m-d')) }}">
         @error('tanggal_berita_acara')
             <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
         @enderror
      </div>
      <div class="form-group">
        <label>No SPK <span class="optional-label">(Opsional)</span></label>
        <input type="text" name="no_spk" placeholder="5TEP/SP/Sawit/30/IX/2024" value="{{ old('no_spk', $dokumen->no_spk) }}">
        @error('no_spk')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="form-row-2">
      <div class="form-group">
        <label>Tanggal SPK <span class="optional-label">(Opsional)</span></label>
        <input type="date" name="tanggal_spk" value="{{ old('tanggal_spk', $dokumen->tanggal_spk?->format('Y-m-d')) }}">
        @error('tanggal_spk')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Tanggal Berakhir SPK <span class="optional-label">(Opsional)</span></label>
        <input type="date" name="tanggal_berakhir_spk" value="{{ old('tanggal_berakhir_spk', $dokumen->tanggal_berakhir_spk?->format('Y-m-d')) }}">
        @error('tanggal_berakhir_spk')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <a href="{{ route('documents.index') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali
      </a>
      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-save"></i> Update Dokumen
      </button>
    </div>
  </form>
</div>

<script>
  // Wait for DOM to be ready
  document.addEventListener('DOMContentLoaded', function() {
    // Function to convert number to Indonesian terbilang
    function terbilangRupiah(number) {
      number = parseFloat(number.toString().replace(/\./g, '')) || 0;
      
      if (number == 0) {
        return 'nol rupiah';
      }
      
      const satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
      const belasan = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
      
      function terbilangSatuan(n) {
        if (n < 10) return satuan[n];
        if (n < 20) return belasan[n - 10];
        if (n < 100) {
          const puluhan = Math.floor(n / 10);
          const sisa = n % 10;
          if (puluhan === 1) return belasan[sisa];
          return satuan[puluhan] + ' puluh' + (sisa > 0 ? ' ' + satuan[sisa] : '');
        }
        if (n < 1000) {
          const ratusan = Math.floor(n / 100);
          const sisa = n % 100;
          if (ratusan === 1) return 'seratus' + (sisa > 0 ? ' ' + terbilangSatuan(sisa) : '');
          return satuan[ratusan] + ' ratus' + (sisa > 0 ? ' ' + terbilangSatuan(sisa) : '');
        }
        if (n < 1000000) {
          const ribuan = Math.floor(n / 1000);
          const sisa = n % 1000;
          if (ribuan === 1) return 'seribu' + (sisa > 0 ? ' ' + terbilangSatuan(sisa) : '');
          return terbilangSatuan(ribuan) + ' ribu' + (sisa > 0 ? ' ' + terbilangSatuan(sisa) : '');
        }
        if (n < 1000000000) {
          const jutaan = Math.floor(n / 1000000);
          const sisa = n % 1000000;
          return terbilangSatuan(jutaan) + ' juta' + (sisa > 0 ? ' ' + terbilangSatuan(sisa) : '');
        }
        if (n < 1000000000000) {
          const milyaran = Math.floor(n / 1000000000);
          const sisa = n % 1000000000;
          return terbilangSatuan(milyaran) + ' milyar' + (sisa > 0 ? ' ' + terbilangSatuan(sisa) : '');
        }
        return 'jumlah terlalu besar';
      }
      
      return terbilangSatuan(Math.floor(number)) + ' rupiah';
    }

    // Format nilai rupiah input with dots and update ejaan
    const nilaiRupiahInput = document.getElementById('nilai_rupiah');
    const ejaanRupiahInput = document.getElementById('ejaan_nilai_rupiah');

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

      // Format initial value if exists
      if (nilaiRupiahInput.value) {
        nilaiRupiahInput.dispatchEvent(new Event('input'));
      }
    }

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

    // Handle form submission - remove dots from nilai_rupiah and disable ejaan_nilai_rupiah
    document.querySelector('form').addEventListener('submit', function(e) {
      const nilaiRupiahInput = document.getElementById('nilai_rupiah');
      if (nilaiRupiahInput && nilaiRupiahInput.value) {
        // Remove all dots before submitting
        nilaiRupiahInput.value = nilaiRupiahInput.value.replace(/\./g, '');
      }
      
      // Remove ejaan_nilai_rupiah from form submission (it's readonly/display only)
      const ejaanInput = document.getElementById('ejaan_nilai_rupiah');
      if (ejaanInput) {
        ejaanInput.disabled = true; // Disable so it won't be submitted
      }
    });

    // Event delegation untuk tombol tambah dan kurang
    document.addEventListener('click', function(e) {
      // Handle tombol tambah (+)
      if (e.target.classList.contains('add-field-btn')) {
        e.preventDefault();
        const fieldGroup = e.target.closest('.dynamic-field');
        const newField = fieldGroup.cloneNode(true);

        // Reset nilai input
        newField.querySelector('input').value = '';

        // Show remove button on new field (always show for new fields)
        const newRemoveBtn = newField.querySelector('.remove-field-btn');
        if (newRemoveBtn) {
          newRemoveBtn.style.display = 'flex';
        }

        // Hide remove button on first field if it exists
        const fieldType = fieldGroup.getAttribute('data-field-type');
        const allFields = document.querySelectorAll(`[data-field-type="${fieldType}"]`);
        if (allFields.length >= 1) {
          const firstField = allFields[0];
          const firstRemoveBtn = firstField.querySelector('.remove-field-btn');
          if (firstRemoveBtn) {
            firstRemoveBtn.style.display = 'none';
          }
        }

        // Insert setelah field saat ini
        fieldGroup.parentNode.insertBefore(newField, fieldGroup.nextSibling);

        // Initialize autocomplete for the new input field
        const newInput = newField.querySelector('input[data-autocomplete]');
        if (newInput) {
          new Autocomplete(newInput);
        }
      }

      // Handle tombol kurang (-)
      if (e.target.classList.contains('remove-field-btn')) {
        e.preventDefault();
        const fieldGroup = e.target.closest('.dynamic-field');
        const fieldType = fieldGroup.getAttribute('data-field-type');
        const allFields = document.querySelectorAll(`[data-field-type="${fieldType}"]`);

        // Only remove if there's more than one field of this type
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

  }); // End DOMContentLoaded

  // ============================================
  // CLEAN AUTOCOMPLETE SYSTEM v2.0
  // ============================================

  // API endpoints mapping
  const API_ENDPOINTS = {
    'payment-recipients': '{{ route("autocomplete.payment-recipients") }}',
    'document-senders': '{{ route("autocomplete.document-senders") }}',
    'document-descriptions': '{{ route("autocomplete.document-descriptions") }}',
    'po-numbers': '{{ route("autocomplete.po-numbers") }}',
    'pr-numbers': '{{ route("autocomplete.pr-numbers") }}'
  };

  // Utility debounce function
  const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => fn.apply(null, args), delay);
    };
  };

  // Main autocomplete class
  class Autocomplete {
    constructor(inputElement) {
      this.input = inputElement;
      this.type = inputElement.dataset.autocomplete;
      this.endpoint = API_ENDPOINTS[this.type];

      if (!this.endpoint) return;

      // State management
      this.state = {
        isVisible: false,
        suggestions: [],
        selectedIndex: -1,
        isLoading: false,
        abortController: null,
        justSelected: false // Flag to prevent dropdown reopening after selection
      };

      // DOM elements
      this.dropdown = null;
      this.init();
    }

    init() {
      this.createDropdown();
      this.attachEventListeners();
    }

    createDropdown() {
      this.dropdown = document.createElement('div');
      this.dropdown.className = 'autocomplete-dropdown';
      this.dropdown.setAttribute('role', 'listbox');
      this.input.parentNode.style.position = 'relative';
      this.input.parentNode.appendChild(this.dropdown);
    }

    attachEventListeners() {
      // Input events
      this.input.addEventListener('input', debounce(this.handleInput.bind(this), 300));
      this.input.addEventListener('focus', this.handleFocus.bind(this));
      this.input.addEventListener('keydown', this.handleKeydown.bind(this));

      // Click outside to close
      document.addEventListener('click', this.handleClickOutside.bind(this));

      // Prevent form submission on enter when dropdown is visible
      this.input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && this.state.isVisible) {
          e.preventDefault();
        }
      });
    }

    handleInput(e) {
      // Prevent dropdown from opening immediately after selection
      if (this.state.justSelected) {
        this.state.justSelected = false;
        return;
      }

      const query = e.target.value.trim();

      if (query.length < 2) {
        this.hide();
        return;
      }

      this.fetchSuggestions(query);
    }

    handleFocus() {
      if (this.input.value.trim().length >= 2 && this.state.suggestions.length > 0) {
        this.show();
      }
    }

    handleKeydown(e) {
      if (!this.state.isVisible) return;

      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault();
          this.selectNext();
          break;
        case 'ArrowUp':
          e.preventDefault();
          this.selectPrevious();
          break;
        case 'Enter':
          e.preventDefault();
          this.selectCurrent();
          break;
        case 'Escape':
          e.preventDefault();
          this.hide();
          break;
      }
    }

    handleClickOutside(e) {
      // Close dropdown when clicking outside
      if (!this.input.contains(e.target) && !this.dropdown.contains(e.target)) {
        this.hide();
      }
    }

    async fetchSuggestions(query) {
      // Cancel previous request
      if (this.state.abortController) {
        this.state.abortController.abort();
      }

      this.state.abortController = new AbortController();
      this.state.isLoading = true;

      try {
        const url = `${this.endpoint}?q=${encodeURIComponent(query)}&limit=10`;
        const response = await fetch(url, {
          signal: this.state.abortController.signal,
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) throw new Error('Network response was not ok');

        let suggestions = await response.json();

        // Filter duplicates for payment recipients
        if (this.type === 'payment-recipients') {
          suggestions = this.filterDuplicates(suggestions);
        }

        this.state.suggestions = suggestions;

        if (suggestions.length > 0) {
          this.render(suggestions, query);
          this.show();
        } else {
          this.hide();
        }

      } catch (error) {
        if (error.name !== 'AbortError') {
          console.error('Autocomplete error:', error);
        }
        this.hide();
      } finally {
        this.state.isLoading = false;
        this.state.abortController = null;
      }
    }

    filterDuplicates(suggestions) {
      const form = this.input.closest('form');
      const allInputs = form.querySelectorAll('input[name="dibayar_kepada[]"]');
      const existingValues = Array.from(allInputs)
        .filter(inp => inp !== this.input)
        .map(inp => inp.value.trim().toLowerCase())
        .filter(val => val.length > 0);

      return suggestions.filter(suggestion =>
        !existingValues.includes(suggestion.toLowerCase())
      );
    }

    render(suggestions, query) {
      this.dropdown.innerHTML = '';
      this.state.selectedIndex = -1;

      suggestions.forEach((suggestion, index) => {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.setAttribute('role', 'option');
        item.textContent = suggestion;

        // Highlight matching text
        if (query) {
          const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
          item.innerHTML = suggestion.replace(regex, '<mark>$1</mark>');
        }

        item.addEventListener('click', () => this.selectItem(suggestion));
        item.addEventListener('mouseenter', () => this.highlightItem(index));

        this.dropdown.appendChild(item);
      });
    }

    highlightText(text, query) {
      if (!query) return text;
      const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
      return text.replace(regex, '<mark>$1</mark>');
    }

    highlightItem(index) {
      this.state.selectedIndex = index;
      this.updateVisualSelection();
    }

    selectNext() {
      const maxIndex = this.state.suggestions.length - 1;
      this.state.selectedIndex = Math.min(this.state.selectedIndex + 1, maxIndex);
      this.updateVisualSelection();
      this.scrollToSelected();
    }

    selectPrevious() {
      this.state.selectedIndex = Math.max(this.state.selectedIndex - 1, -1);
      this.updateVisualSelection();
      this.scrollToSelected();
    }

    selectCurrent() {
      if (this.state.selectedIndex >= 0) {
        const suggestion = this.state.suggestions[this.state.selectedIndex];
        this.selectItem(suggestion);
      }
    }

    selectItem(suggestion) {
      // Set flag to prevent dropdown reopening
      this.state.justSelected = true;
      
      // Set value
      this.input.value = suggestion;
      
      // FORCE HIDE dropdown immediately
      this.hide();
      
      // Clear suggestions to prevent reopening
      this.state.suggestions = [];
      
      // Trigger input event for validation (but flag will prevent reopening)
      this.input.dispatchEvent(new Event('input', { bubbles: true }));
      
      // Keep focus on input
      this.input.focus();
      
      // Reset flag after a short delay to allow normal typing again
      setTimeout(() => {
        this.state.justSelected = false;
      }, 100);
    }

    updateVisualSelection() {
      const items = this.dropdown.querySelectorAll('.autocomplete-item');
      items.forEach((item, index) => {
        item.classList.toggle('selected', index === this.state.selectedIndex);
      });
    }

    scrollToSelected() {
      const selectedItem = this.dropdown.querySelector('.autocomplete-item.selected');
      if (selectedItem) {
        selectedItem.scrollIntoView({ block: 'nearest' });
      }
    }

    show() {
      if (this.state.suggestions.length === 0) return;

      this.state.isVisible = true;
      this.dropdown.style.display = 'block';
      this.positionDropdown();
    }

    hide() {
      this.state.isVisible = false;
      this.state.selectedIndex = -1;
      this.dropdown.style.display = 'none';
    }

    positionDropdown() {
      const inputRect = this.input.getBoundingClientRect();
      this.dropdown.style.top = `${this.input.offsetHeight}px`;
      this.dropdown.style.left = '0';
      this.dropdown.style.width = `${this.input.offsetWidth}px`;
    }

    destroy() {
      if (this.dropdown) {
        this.dropdown.remove();
      }
      document.removeEventListener('click', this.handleClickOutside);
    }
  }

  // Initialize all autocomplete instances
  const initAutocomplete = () => {
    const inputs = document.querySelectorAll('input[data-autocomplete]');
    inputs.forEach(input => new Autocomplete(input));
  };

  // Auto-initialize on DOM ready
  document.addEventListener('DOMContentLoaded', initAutocomplete);
</script>

<style>
  /* Standard Dropdown List - Simple & Clean */
  .autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #ccc;
    border-top: none;
    z-index: 1000;
    margin: 0;
    padding: 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 14px;
    color: #333;
    line-height: 1.4;
    border-bottom: 1px solid #eee;
    background: #ffffff;
    margin: 0;
    list-style: none;
  }

  .autocomplete-item:last-child {
    border-bottom: none;
  }

  .autocomplete-item:hover,
  .autocomplete-item.selected {
    background-color: #f0f0f0;
    color: #000;
  }

  .autocomplete-item mark {
    background-color: transparent;
    color: inherit;
    font-weight: bold;
    padding: 0;
  }

  /* Ensure proper positioning context */
  .form-group:has(input[data-autocomplete]) {
    position: relative;
  }

  .dynamic-field:has(input[data-autocomplete]) {
    position: relative;
  }

  /* Simple scrollbar */
  .autocomplete-dropdown::-webkit-scrollbar {
    width: 8px;
  }

  .autocomplete-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  .autocomplete-dropdown::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
  }

  .autocomplete-dropdown::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
</style>

@endsection
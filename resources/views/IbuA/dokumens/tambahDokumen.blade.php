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

  .btn-reset {
    padding: 12px 24px;
    border: none;
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(108, 117, 125, 0.3);
    letter-spacing: 0.5px;
    margin-right: 10px;
  }

  .btn-reset:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(108, 117, 125, 0.4);
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
  }

  .btn-auto-fill {
    padding: 12px 24px;
    border: none;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(136, 151, 23, 0.3);
    letter-spacing: 0.5px;
    margin-right: 10px;
  }

  .btn-auto-fill:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(136, 151, 23, 0.4);
    background: linear-gradient(135deg, #9ab01f 0%, #a9b825 100%);
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(8, 62, 64, 0.1);
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
</style>

<div class="card mb-4 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%); border-radius: 16px; box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05); border: 1px solid rgba(8, 62, 64, 0.08);">
    <h2 class="form-title">Tambah <span>Dokumen</span></h2>
</div>

<div class="form-container">
  <form action="{{ route('dokumens.store') }}" method="POST">
    @csrf

    <!-- Input Dokumen Baru -->
    <div class="section-title">Input Dokumen Baru</div>

    <!-- Info Box -->
    <div class="info-box">
      <i class="fas fa-info-circle"></i>
      <strong>Informasi:</strong> Tanggal masuk akan diisi otomatis saat dokumen disimpan. Bulan dan tahun diambil dari tanggal SPP.
    </div>

    <!-- 1. Nomor Agenda -->
    <div class="form-group">
      <label>Nomor Agenda</label>
      <input type="text" name="nomor_agenda" placeholder="Masukkan nomor agenda" required value="{{ old('nomor_agenda') }}">
      @error('nomor_agenda')
          <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
      @enderror
    </div>

    <!-- 2. Nomor SPP -->
    <div class="form-group">
      <label>Nomor SPP</label>
      <input type="text" name="nomor_spp" placeholder="123/M/SPP/13/XII/2025" required value="{{ old('nomor_spp') }}">
      @error('nomor_spp')
          <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
      @enderror
    </div>

    <!-- 3. Tanggal SPP -->
    <div class="form-group">
      <label>Tanggal SPP</label>
      <input type="datetime-local" name="tanggal_spp" required value="{{ old('tanggal_spp') }}">
      @error('tanggal_spp')
          <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
      @enderror
    </div>

    <!-- 4. Uraian SPP -->
    <div class="form-group">
      <label>Uraian SPP</label>
      <textarea name="uraian_spp" placeholder="Permintaan permohonan pembayaran THR Pegawai/Pekerja Harian Lepas (PHL) Bulan Maret sampai dengan Desember 2024" required data-autocomplete="document-descriptions">{{ old('uraian_spp') }}</textarea>
      @error('uraian_spp')
          <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
      @enderror
    </div>

    <!-- 5. Vendor/Dibayar Kepada -->
    <div class="section-title">Vendor/Dibayar Kepada</div>
    <div class="form-group dynamic-field" data-field-type="dibayar_kepada">
      <label>Vendor/Dibayar Kepada <span class="optional-label">(Bisa lebih dari 1)</span></label>
      <input type="text" placeholder="Masukkan nama vendor/penerima" name="dibayar_kepada[]" data-autocomplete="payment-recipients" value="{{ old('dibayar_kepada.0') }}">
      <button type="button" class="add-field-btn">+</button>
      <button type="button" class="remove-field-btn" style="display: none;">−</button>
    </div>

    <!-- 6. Nilai Rupiah -->
    <div class="form-group">
      <label>Nilai Rupiah</label>
      <input type="text" name="nilai_rupiah" id="nilai_rupiah" placeholder="Masukkan nilai rupiah (contoh: 120000000)" required value="{{ old('nilai_rupiah') }}">
      @error('nilai_rupiah')
          <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
      @enderror
    </div>

    <!-- 7. Ejaan Nilai Rupiah (Otomatis) -->
    <div class="form-group">
      <label>Ejaan Nilai Rupiah</label>
      <input type="text" name="ejaan_nilai_rupiah" id="ejaan_nilai_rupiah" placeholder="Ejaan akan terisi otomatis" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
    </div>

    <!-- Kategori & Jenis Dokumen -->
    <div class="form-row">
      <div class="form-group">
        <label>Kategori</label>
        <select id="kategori" name="kategori" required>
          <option value="">Pilih Kategori</option>
          <option value="Investasi on farm" {{ old('kategori') == 'Investasi on farm' ? 'selected' : '' }}>Investasi on farm</option>
          <option value="Investasi off farm" {{ old('kategori') == 'Investasi off farm' ? 'selected' : '' }}>Investasi off farm</option>
          <option value="Exploitasi" {{ old('kategori') == 'Exploitasi' ? 'selected' : '' }}>Exploitasi</option>
        </select>
        @error('kategori')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Jenis Dokumen</label>
        <select id="jenis_dokumen" name="jenis_dokumen" required>
          <option value="">Pilih Kategori terlebih dahulu</option>
        </select>
        @error('jenis_dokumen')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Jenis SubPekerjaan & Jenis Pembayaran -->
    <div class="form-row">
      <div class="form-group">
        <label>Jenis SubPekerjaan</label>
        <select name="jenis_sub_pekerjaan">
          <option value="">Pilih Opsi</option>
          <option value="Surat Masuk/Keluar Reguler" {{ old('jenis_sub_pekerjaan') == 'Surat Masuk/Keluar Reguler' ? 'selected' : '' }}>Surat Masuk/Keluar Reguler</option>
          <option value="Surat Undangan" {{ old('jenis_sub_pekerjaan') == 'Surat Undangan' ? 'selected' : '' }}>Surat Undangan</option>
          <option value="Memo Internal" {{ old('jenis_sub_pekerjaan') == 'Memo Internal' ? 'selected' : '' }}>Memo Internal</option>
        </select>
        @error('jenis_sub_pekerjaan')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Jenis Pembayaran</label>
        <select name="jenis_pembayaran">
          <option value="">Pilih Opsi</option>
          <option value="Karyawan" {{ old('jenis_pembayaran') == 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
          <option value="Mitra" {{ old('jenis_pembayaran') == 'Mitra' ? 'selected' : '' }}>Mitra</option>
          <option value="MPN" {{ old('jenis_pembayaran') == 'MPN' ? 'selected' : '' }}>MPN</option>
          <option value="TBS" {{ old('jenis_pembayaran') == 'TBS' ? 'selected' : '' }}>TBS</option>
          <option value="Dropping" {{ old('jenis_pembayaran') == 'Dropping' ? 'selected' : '' }}>Dropping</option>
          <option value="Lainnya" {{ old('jenis_pembayaran') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
        </select>
        @error('jenis_pembayaran')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

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
            $oldKebun = old('kebun');
            $oldKebunClean = $oldKebun ? preg_replace('/^\d+\s+/', '', $oldKebun) : '';
          @endphp
          @foreach($kebunOptions as $kebun)
            <option value="{{ $kebun }}" {{ ($oldKebun == $kebun || $oldKebunClean == $kebun) ? 'selected' : '' }}>{{ $kebun }}</option>
          @endforeach
        </select>
        @error('kebun')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- 8. ALL SPK -->
    <div class="section-title">SPK (Surat Perintah Kerja)</div>
    <div class="form-row">
      <div class="form-group">
        <label>No SPK <span class="optional-label">(Opsional)</span></label>
        <input type="text" name="no_spk" placeholder="5TEP/SP/Sawit/30/IX/2024" value="{{ old('no_spk') }}">
        @error('no_spk')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Tanggal SPK <span class="optional-label">(Opsional)</span></label>
        <input type="date" name="tanggal_spk" value="{{ old('tanggal_spk') }}">
        @error('tanggal_spk')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>
    <div class="form-group">
      <label>Tanggal Berakhir SPK <span class="optional-label">(Opsional)</span></label>
      <input type="date" name="tanggal_berakhir_spk" value="{{ old('tanggal_berakhir_spk') }}">
      @error('tanggal_berakhir_spk')
          <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
      @enderror
    </div>

    <!-- 9. ALL BA (Berita Acara) -->
    <div class="section-title">BA (Berita Acara)</div>
    <div class="form-row">
      <div class="form-group">
        <label>No Berita Acara</label>
        <input type="text" name="no_berita_acara" placeholder="5TEP/BAST/49/SP.30/XI/2024" value="{{ old('no_berita_acara') }}">
        @error('no_berita_acara')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Tanggal Berita Acara</label>
        <input type="date" name="tanggal_berita_acara" value="{{ old('tanggal_berita_acara') }}">
        @error('tanggal_berita_acara')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- 10. ALL PO/PR -->
    <div class="section-title">PO/PR (Purchase Order / Purchase Request)</div>
    <div class="form-group dynamic-field" data-field-type="po">
      <label>Nomor PO <span class="optional-label">(Opsional)</span></label>
      <input type="text" placeholder="Masukkan nomor PO" name="nomor_po[]" data-autocomplete="po-numbers" value="{{ old('nomor_po.0') }}">
      <button type="button" class="add-field-btn">+</button>
      <button type="button" class="remove-field-btn" style="display: none;">−</button>
    </div>
    <div class="form-group dynamic-field" data-field-type="pr">
      <label>Nomor PR <span class="optional-label">(Opsional)</span></label>
      <input type="text" placeholder="Masukkan nomor PR" name="nomor_pr[]" data-autocomplete="pr-numbers" value="{{ old('nomor_pr.0') }}">
      <button type="button" class="add-field-btn">+</button>
      <button type="button" class="remove-field-btn" style="display: none;">−</button>
    </div>

    <!-- Field tambahan yang diperlukan (hidden atau di bagian bawah) -->
    <div class="section-title">Informasi Tambahan</div>
    <div class="form-row">
      <div class="form-group">
        <label>Bagian</label>
        <select name="bagian" required>
          <option value="">Pilih Bagian</option>
          <option value="DPM" {{ old('bagian') == 'DPM' ? 'selected' : '' }}>DPM</option>
          <option value="SKH" {{ old('bagian') == 'SKH' ? 'selected' : '' }}>SKH</option>
          <option value="SDM" {{ old('bagian') == 'SDM' ? 'selected' : '' }}>SDM</option>
          <option value="TEP" {{ old('bagian') == 'TEP' ? 'selected' : '' }}>TEP</option>
          <option value="KPL" {{ old('bagian') == 'KPL' ? 'selected' : '' }}>KPL</option>
          <option value="AKN" {{ old('bagian') == 'AKN' ? 'selected' : '' }}>AKN</option>
          <option value="TAN" {{ old('bagian') == 'TAN' ? 'selected' : '' }}>TAN</option>
          <option value="PMO" {{ old('bagian') == 'PMO' ? 'selected' : '' }}>PMO</option>
        </select>
        @error('bagian')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Nama Pengirim Dokumen</label>
        <input type="text" name="nama_pengirim" placeholder="Masukkan nama pengirim dokumen" value="{{ old('nama_pengirim') }}" data-autocomplete="document-senders">
        @error('nama_pengirim')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Kategori & Jenis Dokumen -->
    <div class="form-row">
      <div class="form-group">
        <label>Kategori</label>
        <select id="kategori" name="kategori" required>
          <option value="">Pilih Kategori</option>
          <option value="Investasi on farm" {{ old('kategori') == 'Investasi on farm' ? 'selected' : '' }}>Investasi on farm</option>
          <option value="Investasi off farm" {{ old('kategori') == 'Investasi off farm' ? 'selected' : '' }}>Investasi off farm</option>
          <option value="Exploitasi" {{ old('kategori') == 'Exploitasi' ? 'selected' : '' }}>Exploitasi</option>
        </select>
        @error('kategori')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Jenis Dokumen</label>
        <select id="jenis_dokumen" name="jenis_dokumen" required>
          <option value="">Pilih Kategori terlebih dahulu</option>
        </select>
        @error('jenis_dokumen')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Jenis SubPekerjaan & Jenis Pembayaran -->
    <div class="form-row">
      <div class="form-group">
        <label>Jenis SubPekerjaan</label>
        <select name="jenis_sub_pekerjaan">
          <option value="">Pilih Opsi</option>
          <option value="Surat Masuk/Keluar Reguler" {{ old('jenis_sub_pekerjaan') == 'Surat Masuk/Keluar Reguler' ? 'selected' : '' }}>Surat Masuk/Keluar Reguler</option>
          <option value="Surat Undangan" {{ old('jenis_sub_pekerjaan') == 'Surat Undangan' ? 'selected' : '' }}>Surat Undangan</option>
          <option value="Memo Internal" {{ old('jenis_sub_pekerjaan') == 'Memo Internal' ? 'selected' : '' }}>Memo Internal</option>
        </select>
        @error('jenis_sub_pekerjaan')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label>Jenis Pembayaran</label>
        <select name="jenis_pembayaran">
          <option value="">Pilih Opsi</option>
          <option value="Karyawan" {{ old('jenis_pembayaran') == 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
          <option value="Mitra" {{ old('jenis_pembayaran') == 'Mitra' ? 'selected' : '' }}>Mitra</option>
          <option value="MPN" {{ old('jenis_pembayaran') == 'MPN' ? 'selected' : '' }}>MPN</option>
          <option value="TBS" {{ old('jenis_pembayaran') == 'TBS' ? 'selected' : '' }}>TBS</option>
          <option value="Dropping" {{ old('jenis_pembayaran') == 'Dropping' ? 'selected' : '' }}>Dropping</option>
          <option value="Lainnya" {{ old('jenis_pembayaran') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
        </select>
        @error('jenis_pembayaran')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

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
            $oldKebun = old('kebun');
            $oldKebunClean = $oldKebun ? preg_replace('/^\d+\s+/', '', $oldKebun) : '';
          @endphp
          @foreach($kebunOptions as $kebun)
            <option value="{{ $kebun }}" {{ ($oldKebun == $kebun || $oldKebunClean == $kebun) ? 'selected' : '' }}>{{ $kebun }}</option>
          @endforeach
        </select>
        @error('kebun')
            <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <button type="button" class="btn-auto-fill" onclick="autoFillForm()">
        <i class="fa-solid fa-magic me-2"></i>Auto Isi
      </button>
      <button type="reset" class="btn-reset">
        <i class="fa-solid fa-undo me-2"></i>Reset
      </button>
      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-save me-2"></i>Simpan dokumen
      </button>
    </div>
  </form>
</div>

<script>
  // Remove format dots from nilai_rupiah before form submit
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
</script>

<script>
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

  // Wait for DOM to be ready
  document.addEventListener('DOMContentLoaded', function() {
    // Format nilai rupiah input with dots
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
    // Data jenis dokumen berdasarkan kategori
    const jenisDokumenData = {
      'Exploitasi': [
        'Pemeliharaan Tanaman Menghasilkan',
        'Pemupukan',
        'Aplikasi Pemupukan',
        'Panen & Pengumpulan',
        'Pengangkutan',
        'Pengolahan',
        'Pembelian Bahan Bakar Minyak (BBM)',
        'Biaya Pengiriman ke Pelabuhan',
        'Biaya Sewa Gudang',
        'Biaya Instalasi Pemompaan',
        'Biaya Pelabuhan',
        'Biaya Jasa KPBN',
        'Biaya Pemasaran Lainnya',
        'Biaya Pengangkutan, Perjalan & Penginapan',
        'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi',
        'Biaya Pemeliharaan Perlengkapan Kantor',
        'Biaya Pajak dan Retribusi',
        'Biaya Premi Asuransi',
        'Biaya Keamanan',
        'Biaya Mutu (ISO 9000)',
        'Biaya Pengendalian Lingkungan (ISO 14000)',
        'Biaya Sistem Manajemen Kesehatan & Keselamatan Kerja',
        'Biaya Penelitian dan Percobaan',
        'Biaya Sumbangan dan Iuran',
        'Biaya CSR',
        'Biaya Pendidikan dan Pengembangan SDM',
        'Biaya Konsultan',
        'Biaya Audit',
        'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)',
        'Biaya Distrik',
        'Biaya Institusi Terkait',
        'Biaya Kantor Perwakilan',
        'Biaya Komisaris',
        'Biaya Media',
        'Biaya Rapat',
        'Biaya Telekomunikasi dan Ekspedisi',
        'Lainnya',
        'PPh Badan',
        'PBB',
        'PPH Masa',
        'PPN',
        'BPHTB',
        'PPh Pasal 22, Pasal 23, Pasal 4 ayat (2) & PPh Pasal 15'
      ],
      'Investasi on farm': [
        'Pekerjaan TU,TK,TB.',
        'Pemel TBM Pupuk',
        'Pemel TBM diluar Pupuk',
        'Pembangunan bibitan'
      ],
      'Investasi off farm': [
        'Pekerjaan Pembangunan Rumah',
        'Pekerjaan Pembangunan Perusahaan',
        'Pekerjaan Pembangunan Mesin dan Instalasi',
        'Pekerjaan Pembangunan Jalan,jembatan dan Saluran Air',
        'Pekerjaan Alat Angkutan',
        'Pekerjaan Inventaris kecil',
        'Pekerjaan Investasi Off Farm Lainnya'
      ]
    };

    // Function to update jenis dokumen dropdown
    function updateJenisDokumen(kategori, selectedValue = null) {
      const jenisDokumenSelect = document.getElementById('jenis_dokumen');

      // Clear existing options
      jenisDokumenSelect.innerHTML = '<option value="">Pilih Jenis Dokumen</option>';

      // Populate options based on selected kategori
      if (kategori && jenisDokumenData[kategori]) {
        jenisDokumenData[kategori].forEach(function(jenis) {
          const option = document.createElement('option');
          option.value = jenis;
          option.textContent = jenis;
          if (selectedValue && jenis === selectedValue) {
            option.selected = true;
          }
          jenisDokumenSelect.appendChild(option);
        });
      } else {
        jenisDokumenSelect.innerHTML = '<option value="">Pilih Kategori terlebih dahulu</option>';
      }
    }

    // Initialize jenis dokumen if kategori already selected (from old input)
    const kategoriSelect = document.getElementById('kategori');
    const oldKategori = '{{ old("kategori") }}';
    const oldJenisDokumen = '{{ old("jenis_dokumen") }}';

    if (oldKategori && oldKategori !== '') {
      updateJenisDokumen(oldKategori, oldJenisDokumen);
    }

    // Event listener untuk dropdown kategori
    kategoriSelect.addEventListener('change', function() {
      updateJenisDokumen(this.value);
    });

    // Handle form reset
    document.querySelector('form').addEventListener('reset', function() {
      setTimeout(function() {
        updateJenisDokumen('');
      }, 0);
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

  // Auto Fill Form Function
  function autoFillForm() {
    // Generate random data
    const randomData = generateRandomDocumentData();

    // Fill form fields
    fillFormField('nomor_agenda', randomData.nomor_agenda);
    fillFormField('bagian', randomData.bagian, 'select');
    fillFormField('nama_pengirim', randomData.nama_pengirim);
    fillFormField('nomor_spp', randomData.nomor_spp);
    fillFormField('tanggal_spp', randomData.tanggal_spp, 'datetime-local');
    fillFormField('uraian_spp', randomData.uraian_spp, 'textarea');
    fillFormField('nilai_rupiah', randomData.nilai_rupiah);
    fillFormField('kategori', randomData.kategori, 'select');
    fillFormField('jenis_dokumen', randomData.jenis_dokumen, 'select');
    fillFormField('jenis_sub_pekerjaan', randomData.jenis_sub_pekerjaan, 'select');
    fillFormField('jenis_pembayaran', randomData.jenis_pembayaran, 'select');
    fillFormField('kebun', randomData.kebun, 'select');
    fillFormField('no_berita_acara', randomData.no_berita_acara);
    fillFormField('tanggal_berita_acara', randomData.tanggal_berita_acara, 'date');
    fillFormField('no_spk', randomData.no_spk);
    fillFormField('tanggal_spk', randomData.tanggal_spk, 'date');
    fillFormField('tanggal_berakhir_spk', randomData.tanggal_berakhir_spk, 'date');

    // Fill dynamic fields
    fillDynamicField('nomor_pr[]', randomData.nomor_pr);
    fillDynamicField('nomor_po[]', randomData.nomor_po);
    fillDynamicField('dibayar_kepada[]', randomData.dibayar_kepada);

    // Show success message
    showNotification('Form berhasil diisi dengan data dummy!');
  }

  function generateRandomDocumentData() {
    const bagians = ['DPM', 'SKH', 'SDM', 'TEP', 'KPL', 'AKN', 'TAN', 'PMO'];
    const categories = ['Operasional', 'Investasi On Farm', 'Pemeliharaan'];
    const kategoris = ['Administrasi & Umum', 'Exploitasi', 'Pembangunan'];
    const jenisPembayaran = ['Mitra', 'Internal', 'Kontrak'];
    const kebunOptions = [
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
    const namaPengirim = ['Ahmad Wijaya', 'Siti Nurhaliza', 'Budi Santoso', 'Diana Putri', 'Eko Prasetyo'];

    const currentDate = new Date();
    const randomDate = new Date(currentDate.getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000);

    return {
      nomor_agenda: `AGD/${Math.floor(Math.random() * 1000)}/XII/2024`,
      bagian: bagians[Math.floor(Math.random() * bagians.length)],
      nama_pengirim: namaPengirim[Math.floor(Math.random() * namaPengirim.length)],
      nomor_spp: `${Math.floor(Math.random() * 999)}/M/SPP/${Math.floor(Math.random() * 31)}/${String(Math.floor(Math.random() * 12) + 1).padStart(2, '0')}/2024`,
      tanggal_spp: formatDateTime(randomDate),
      uraian_spp: generateRandomUraian(),
      nilai_rupiah: Math.floor(Math.random() * 900000000) + 100000000, // 100M - 1B
      kategori: categories[Math.floor(Math.random() * categories.length)],
      jenis_dokumen: kategoris[Math.floor(Math.random() * kategoris.length)],
      jenis_sub_pekerjaan: 'Pembayaran Tagihan',
      jenis_pembayaran: jenisPembayaran[Math.floor(Math.random() * jenisPembayaran.length)],
      kebun: kebunOptions[Math.floor(Math.random() * kebunOptions.length)],
      no_berita_acara: `BAST/${Math.floor(Math.random() * 100)}/${Math.floor(Math.random() * 31)}/XI/2024`,
      tanggal_berita_acara: formatDate(randomDate),
      no_spk: `SPK/${Math.floor(Math.random() * 200)}/TEP/${Math.floor(Math.random() * 12)}/IX/2024`,
      tanggal_spk: formatDate(randomDate),
      tanggal_berakhir_spk: formatDate(new Date(randomDate.getTime() + 30 * 24 * 60 * 60 * 1000)),
      nomor_pr: `PR${Math.floor(Math.random() * 9999).toString().padStart(4, '0')}/XII/2024`,
      nomor_po: `PO${Math.floor(Math.random() * 9999).toString().padStart(4, '0')}/XII/2024`,
      dibayar_kepada: 'PT. Mitra Sejati'
    };
  }

  function generateRandomUraian() {
    const uraians = [
      'Permintaan pembayaran untuk pengadaan alat pertanian periode Desember 2024',
      'Pembayaran jasa maintenance mesin produksi bulan November 2024',
      'Pengadaan spare part untuk traktor dan alat pertanian modern',
      'Pembayaran honorarium konsultan pertanian periode Q4 2024',
      'Pembelian pupuk organik untuk lahan perkebunan seluas 500 hektar',
      'Biaya transportasi dan distribusi hasil panen ke gudang',
      'Pembayaran sewa alat berat untuk kegiatan perawatan kebun',
      'Pengadaan sistem irigasi modern untuk perkebunan sawit',
      'Biaya training dan pengembangan SDM pertanian',
      'Pembayaran asuransi tanaman dan properti pertanian'
    ];

    return uraians[Math.floor(Math.random() * uraians.length)];
  }

  function fillFormField(name, value, type = 'text') {
    const field = document.querySelector(`[name="${name}"]`);
    if (field) {
      field.value = value;
      // Trigger change event for any listeners
      field.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }

  function fillDynamicField(name, value) {
    const fields = document.querySelectorAll(`[name="${name}"]`);
    if (fields.length > 0) {
      fields[0].value = value;
      fields[0].dispatchEvent(new Event('change', { bubbles: true }));
    }
  }

  function formatDateTime(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(Math.floor(Math.random() * 12) + 8).padStart(2, '0'); // 8AM-8PM
    const minutes = String(Math.floor(Math.random() * 60)).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 16px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 16px rgba(40, 167, 69, 0.3);
      z-index: 10000;
      font-weight: 600;
      max-width: 300px;
      animation: slideIn 0.3s ease;
    `;
    notification.innerHTML = `
      <i class="fa-solid fa-check-circle me-2"></i>${message}
    `;

    // Add animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      @keyframes slideOut {
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

    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
        if (style.parentNode) {
          style.parentNode.removeChild(style);
        }
      }, 300);
    }, 3000);
  }
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
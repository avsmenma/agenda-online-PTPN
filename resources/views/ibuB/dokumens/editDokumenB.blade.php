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
    <h2 class="form-title">Edit <span>Dokumen - Ibu Yuni</span></h2>
</div>

<!-- Info Alert -->
<div class="info-alert">
  <i class="fa-solid fa-circle-info"></i>
  <div class="info-alert-content">
    <div class="info-alert-title">Informasi Edit Dokumen</div>
    <p class="info-alert-text">
      Sebagai Ibu Yuni, Anda dapat mengedit semua data dokumen. Perubahan yang Anda lakukan akan tersimpan dan dapat dilihat oleh semua pihak terkait.
    </p>
  </div>
</div>

<div class="form-container">
  <form action="{{ route('dokumensB.update', $dokumen->id) }}" method="POST" id="editForm">
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
              <label>Nomor Agenda <span style="color: red;">*</span></label>
              <input type="text" name="nomor_agenda" placeholder="Masukkan nomor agenda" required value="{{ old('nomor_agenda', $dokumen->nomor_agenda) }}">
              @error('nomor_agenda')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Bulan <span style="color: red;">*</span></label>
              <select name="bulan" required>
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
              <label>Tahun <span style="color: red;">*</span></label>
              <input type="number" name="tahun" placeholder="2025" value="{{ old('tahun', $dokumen->tahun) }}" required min="2020" max="2030">
              @error('tahun')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Tanggal Masuk <span style="color: red;">*</span></label>
              <input type="datetime-local" name="tanggal_masuk" required value="{{ old('tanggal_masuk', $dokumen->tanggal_masuk->format('Y-m-d\TH:i')) }}">
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
              <label>Nomor SPP <span style="color: red;">*</span></label>
              <input type="text" name="nomor_spp" placeholder="123/M/SPP/13/XII/2025" required value="{{ old('nomor_spp', $dokumen->nomor_spp) }}">
              @error('nomor_spp')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Tanggal SPP <span style="color: red;">*</span></label>
              <input type="datetime-local" name="tanggal_spp" required value="{{ old('tanggal_spp', $dokumen->tanggal_spp->format('Y-m-d\TH:i')) }}">
              @error('tanggal_spp')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-group">
            <label>Uraian SPP <span style="color: red;">*</span></label>
            <textarea name="uraian_spp" placeholder="Permintaan permohonan pembayaran..." required>{{ old('uraian_spp', $dokumen->uraian_spp) }}</textarea>
            @error('uraian_spp')
              <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label>Nilai Rupiah <span style="color: red;">*</span></label>
            <input type="text" name="nilai_rupiah" placeholder="123456" required value="{{ old('nilai_rupiah', number_format($dokumen->nilai_rupiah, 0, '', '')) }}">
            @error('nilai_rupiah')
              <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: Kategori & Jenis Dokumen -->
    <div class="accordion-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-tags"></i>
          <span>Kategori & Jenis Dokumen</span>
          <span class="section-badge">Wajib</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row">
            <div class="form-group">
              <label>Kategori <span style="color: red;">*</span></label>
              <select id="kategori" name="kategori" required>
                <option value="">Pilih Kategori</option>
                <option value="Investasi on farm" {{ old('kategori', $dokumen->kategori) == 'Investasi on farm' ? 'selected' : '' }}>Investasi on farm</option>
                <option value="Investasi off farm" {{ old('kategori', $dokumen->kategori) == 'Investasi off farm' ? 'selected' : '' }}>Investasi off farm</option>
                <option value="Exploitasi" {{ old('kategori', $dokumen->kategori) == 'Exploitasi' ? 'selected' : '' }}>Exploitasi</option>
              </select>
              @error('kategori')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Jenis Dokumen <span style="color: red;">*</span></label>
              <select id="jenis_dokumen" name="jenis_dokumen" required>
                <option value="">Pilih Kategori terlebih dahulu</option>
              </select>
              @error('jenis_dokumen')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Jenis SubPekerjaan <span class="optional-label">(Opsional)</span></label>
              <select name="jenis_sub_pekerjaan">
                <option value="">Pilih Opsi</option>
                <option value="Surat Masuk/Keluar Reguler" {{ old('jenis_sub_pekerjaan', $dokumen->jenis_sub_pekerjaan) == 'Surat Masuk/Keluar Reguler' ? 'selected' : '' }}>Surat Masuk/Keluar Reguler</option>
                <option value="Surat Undangan" {{ old('jenis_sub_pekerjaan', $dokumen->jenis_sub_pekerjaan) == 'Surat Undangan' ? 'selected' : '' }}>Surat Undangan</option>
                <option value="Memo Internal" {{ old('jenis_sub_pekerjaan', $dokumen->jenis_sub_pekerjaan) == 'Memo Internal' ? 'selected' : '' }}>Memo Internal</option>
              </select>
              @error('jenis_sub_pekerjaan')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Jenis Pembayaran <span class="optional-label">(Opsional)</span></label>
              <select name="jenis_pembayaran">
                <option value="">Pilih Opsi</option>
                @foreach(['Karyawan', 'Mitra', 'MPN', 'TBS', 'Dropping', 'Lainnya'] as $jenis)
                  <option value="{{ $jenis }}" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                @endforeach
              </select>
              @error('jenis_pembayaran')
                <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Kebun <span class="optional-label">(Opsional)</span></label>
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
          <span class="section-badge" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);">Opsional</span>
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
                <label>Nomor PO <span class="optional-label">(Opsional)</span></label>
                <input type="text" placeholder="Masukkan nomor PO" name="nomor_po[]" value="{{ old('nomor_po.' . $index, $po->nomor_po) }}">
                <button type="button" class="add-field-btn">+</button>
                <button type="button" class="remove-field-btn" style="{{ $loop->first ? 'display: none;' : 'display: flex;' }}">−</button>
              </div>
              @endforeach
            @else
            <div class="form-group dynamic-field" data-field-type="po">
              <label>Nomor PO <span class="optional-label">(Opsional)</span></label>
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
                <label>Nomor PR <span class="optional-label">(Opsional)</span></label>
                <input type="text" placeholder="Masukkan nomor PR" name="nomor_pr[]" value="{{ old('nomor_pr.' . $index, $pr->nomor_pr) }}">
                <button type="button" class="add-field-btn">+</button>
                <button type="button" class="remove-field-btn" style="{{ $loop->first ? 'display: none;' : 'display: flex;' }}">−</button>
              </div>
              @endforeach
            @else
            <div class="form-group dynamic-field" data-field-type="pr">
              <label>Nomor PR <span class="optional-label">(Opsional)</span></label>
              <input type="text" placeholder="Masukkan nomor PR" name="nomor_pr[]" value="{{ old('nomor_pr.0') }}">
              <button type="button" class="add-field-btn">+</button>
              <button type="button" class="remove-field-btn" style="display: none;">−</button>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Section 5: Keterangan -->
    <div class="accordion-section">
      <div class="accordion-header" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-comment"></i>
          <span>Keterangan</span>
          <span class="section-badge" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);">Opsional</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content">
        <div class="accordion-body">
          <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" placeholder="Masukkan keterangan (opsional)">{{ old('keterangan', $dokumen->keterangan) }}</textarea>
            @error('keterangan')
              <div class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <a href="{{ route('dokumensB.index') }}" class="btn-reset" style="text-decoration: none; display: inline-block;">Batal</a>
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

  // Initialize jenis dokumen if kategori already selected
  const kategoriSelect = document.getElementById('kategori');
  const currentKategori = '{{ old("kategori", $dokumen->kategori) }}';
  const currentJenisDokumen = '{{ old("jenis_dokumen", $dokumen->jenis_dokumen) }}';

  if (currentKategori && currentKategori !== '') {
    updateJenisDokumen(currentKategori, currentJenisDokumen);
  }

  // Event listener untuk dropdown kategori
  kategoriSelect.addEventListener('change', function() {
    updateJenisDokumen(this.value);
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

}); // End DOMContentLoaded
</script>

@endsection

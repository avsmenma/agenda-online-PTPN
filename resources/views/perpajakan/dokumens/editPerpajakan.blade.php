@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    margin-bottom: 24px;
  }

  .form-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 5px 20px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    margin-bottom: 30px;
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
    max-height: 3000px;
  }

  .accordion-body {
    padding: 24px;
    border-top: 1px solid rgba(8, 62, 64, 0.1);
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

  .dynamic-field {
    position: relative;
    padding-right: 40px;
  }

  .btn-remove {
    position: absolute;
    right: 0;
    top: 32px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-remove:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
  }

  .btn-add {
    padding: 10px 20px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.2);
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(136, 151, 23, 0.3);
  }

  .btn-submit {
    padding: 14px 32px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 62, 64, 0.3);
  }

  .btn-cancel {
    padding: 14px 32px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-2px);
    color: white;
  }

  .alert {
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-left: 4px solid #28a745;
    color: #155724;
  }

  .alert-error {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border-left: 4px solid #dc3545;
    color: #721c24;
  }

  @media (max-width: 768px) {
    .form-row, .form-row-3 {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="container-fluid">
  <h2>{{ $title }}</h2>

  @if(session('success'))
    <div class="alert alert-success">
      <i class="fa-solid fa-check-circle"></i>
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-error">
      <i class="fa-solid fa-exclamation-circle"></i>
      {{ session('error') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-error">
      <i class="fa-solid fa-exclamation-circle"></i>
      <div>
        <strong>Terdapat kesalahan:</strong>
        <ul style="margin: 8px 0 0 20px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  <form action="{{ route('dokumensPerpajakan.update', $dokumen->id) }}" method="POST" class="form-container">
    @csrf
    @method('PUT')

    <!-- Section 1: Informasi Dasar -->
    <div class="accordion-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-file-alt"></i>
          <span>Informasi Dasar Dokumen</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row-3">
            <div class="form-group">
              <label for="nomor_agenda">Nomor Agenda <span class="text-danger">*</span></label>
              <input type="text" id="nomor_agenda" name="nomor_agenda" value="{{ old('nomor_agenda', $dokumen->nomor_agenda) }}" required>
            </div>
            <div class="form-group">
              <label for="bulan">Bulan <span class="text-danger">*</span></label>
              <select id="bulan" name="bulan" required>
                <option value="">Pilih Bulan</option>
                @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulanOption)
                  <option value="{{ $bulanOption }}" {{ old('bulan', $dokumen->bulan) == $bulanOption ? 'selected' : '' }}>{{ $bulanOption }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="tahun">Tahun <span class="text-danger">*</span></label>
              <input type="number" id="tahun" name="tahun" value="{{ old('tahun', $dokumen->tahun) }}" min="2020" max="2030" required>
            </div>
          </div>

          <div class="form-group">
            <label for="tanggal_masuk">Tanggal Masuk <span class="text-danger">*</span></label>
            <input type="datetime-local" id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('Y-m-d\TH:i') : '') }}" required>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Informasi SPP -->
    <div class="accordion-section">
      <div class="accordion-header active" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-file-invoice"></i>
          <span>Informasi SPP</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div class="form-row">
            <div class="form-group">
              <label for="nomor_spp">Nomor SPP <span class="text-danger">*</span></label>
              <input type="text" id="nomor_spp" name="nomor_spp" value="{{ old('nomor_spp', $dokumen->nomor_spp) }}" required>
            </div>
            <div class="form-group">
              <label for="tanggal_spp">Tanggal SPP <span class="text-danger">*</span></label>
              <input type="date" id="tanggal_spp" name="tanggal_spp" value="{{ old('tanggal_spp', $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('Y-m-d') : '') }}" required>
            </div>
          </div>

          <div class="form-group">
            <label for="uraian_spp">Uraian SPP</label>
            <textarea id="uraian_spp" name="uraian_spp">{{ old('uraian_spp', $dokumen->uraian_spp) }}</textarea>
          </div>

          <div class="form-group">
            <label for="nilai_rupiah">Nilai Rupiah <span class="text-danger">*</span></label>
            <input type="text" id="nilai_rupiah" name="nilai_rupiah" value="{{ old('nilai_rupiah', number_format($dokumen->nilai_rupiah, 0, ',', '.')) }}" required>
            <small class="text-muted">Format: 1.000.000</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: Detail Dokumen -->
    <div class="accordion-section">
      <div class="accordion-header" onclick="toggleAccordion(this)">
        <div class="accordion-title">
          <i class="fa-solid fa-list-alt"></i>
          <span>Detail Dokumen</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content">
        <div class="accordion-body">
          <div class="form-row-3">
            <div class="form-group">
              <label for="kategori">Kategori</label>
              <select id="kategori" name="kategori">
                <option value="">Pilih Kategori</option>
                <option value="KONTRAK" {{ old('kategori', $dokumen->kategori) == 'KONTRAK' ? 'selected' : '' }}>KONTRAK</option>
                <option value="LANGGANAN" {{ old('kategori', $dokumen->kategori) == 'LANGGANAN' ? 'selected' : '' }}>LANGGANAN</option>
                <option value="BIAYA LAINNYA" {{ old('kategori', $dokumen->kategori) == 'BIAYA LAINNYA' ? 'selected' : '' }}>BIAYA LAINNYA</option>
              </select>
            </div>
            <div class="form-group">
              <label for="jenis_dokumen">Jenis Dokumen</label>
              <input type="text" id="jenis_dokumen" name="jenis_dokumen" value="{{ old('jenis_dokumen', $dokumen->jenis_dokumen) }}">
            </div>
            <div class="form-group">
              <label for="jenis_sub_pekerjaan">Jenis Sub Pekerjaan</label>
              <input type="text" id="jenis_sub_pekerjaan" name="jenis_sub_pekerjaan" value="{{ old('jenis_sub_pekerjaan', $dokumen->jenis_sub_pekerjaan) }}">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="jenis_pembayaran">Jenis Pembayaran</label>
              <input type="text" id="jenis_pembayaran" name="jenis_pembayaran" value="{{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) }}">
            </div>
            <div class="form-group">
              <label for="dibayar_kepada">Dibayar Kepada</label>
              <input type="text" id="dibayar_kepada" name="dibayar_kepada" value="{{ old('dibayar_kepada', $dokumen->dibayar_kepada) }}">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="kebun">Kebun</label>
              <select id="kebun" name="kebun">
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
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="no_berita_acara">No Berita Acara</label>
              <input type="text" id="no_berita_acara" name="no_berita_acara" value="{{ old('no_berita_acara', $dokumen->no_berita_acara) }}">
            </div>
            <div class="form-group">
              <label for="tanggal_berita_acara">Tanggal Berita Acara</label>
              <input type="date" id="tanggal_berita_acara" name="tanggal_berita_acara" value="{{ old('tanggal_berita_acara', $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('Y-m-d') : '') }}">
            </div>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label for="no_spk">No SPK</label>
              <input type="text" id="no_spk" name="no_spk" value="{{ old('no_spk', $dokumen->no_spk) }}">
            </div>
            <div class="form-group">
              <label for="tanggal_spk">Tanggal SPK</label>
              <input type="date" id="tanggal_spk" name="tanggal_spk" value="{{ old('tanggal_spk', $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
              <label for="tanggal_berakhir_spk">Tanggal Berakhir SPK</label>
              <input type="date" id="tanggal_berakhir_spk" name="tanggal_berakhir_spk" value="{{ old('tanggal_berakhir_spk', $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('Y-m-d') : '') }}">
            </div>
          </div>

          <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan">{{ old('keterangan', $dokumen->keterangan) }}</textarea>
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
          <div class="form-row">
            <div>
              <label>Nomor PO</label>
              <div id="po-container">
                @if($dokumen->dokumenPos->count() > 0)
                  @foreach($dokumen->dokumenPos as $index => $po)
                    <div class="form-group dynamic-field">
                      <input type="text" name="nomor_po[]" value="{{ old('nomor_po.'.$index, $po->nomor_po) }}" placeholder="Nomor PO">
                      @if($index > 0)
                        <button type="button" class="btn-remove" onclick="removeField(this)">
                          <i class="fa-solid fa-times"></i>
                        </button>
                      @endif
                    </div>
                  @endforeach
                @else
                  <div class="form-group dynamic-field">
                    <input type="text" name="nomor_po[]" placeholder="Nomor PO">
                  </div>
                @endif
              </div>
              <button type="button" class="btn-add" onclick="addPOField()">
                <i class="fa-solid fa-plus"></i> Tambah Nomor PO
              </button>
            </div>

            <div>
              <label>Nomor PR</label>
              <div id="pr-container">
                @if($dokumen->dokumenPrs->count() > 0)
                  @foreach($dokumen->dokumenPrs as $index => $pr)
                    <div class="form-group dynamic-field">
                      <input type="text" name="nomor_pr[]" value="{{ old('nomor_pr.'.$index, $pr->nomor_pr) }}" placeholder="Nomor PR">
                      @if($index > 0)
                        <button type="button" class="btn-remove" onclick="removeField(this)">
                          <i class="fa-solid fa-times"></i>
                        </button>
                      @endif
                    </div>
                  @endforeach
                @else
                  <div class="form-group dynamic-field">
                    <input type="text" name="nomor_pr[]" placeholder="Nomor PR">
                  </div>
                @endif
              </div>
              <button type="button" class="btn-add" onclick="addPRField()">
                <i class="fa-solid fa-plus"></i> Tambah Nomor PR
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 5: INFORMASI PERPAJAKAN (NEW) -->
    <div class="accordion-section" style="border: 2px solid #ffc107;">
      <div class="accordion-header active" onclick="toggleAccordion(this)" style="background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, transparent 100%); border-left-color: #ffc107;">
        <div class="accordion-title">
          <i class="fa-solid fa-file-invoice-dollar" style="color: #ffc107;"></i>
          <span>Informasi Team Perpajakan</span>
          <span style="background: #ffc107; color: white; padding: 4px 12px; border-radius: 20px; font-size: 10px; margin-left: 8px;">KHUSUS PERPAJAKAN</span>
        </div>
        <i class="fa-solid fa-chevron-down accordion-icon"></i>
      </div>
      <div class="accordion-content active">
        <div class="accordion-body">
          <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 16px; margin-bottom: 20px; border-radius: 8px;">
            <strong><i class="fa-solid fa-info-circle me-1"></i>Panduan Pengisian:</strong>
            <ul style="margin: 8px 0 0 20px; font-size: 13px;">
              <li>Semua field di bawah ini <strong>opsional</strong> (tidak wajib diisi)</li>
              <li>Isi sesuai dengan dokumen pajak yang tersedia</li>
              <li>Untuk <strong>DPP PPh</strong> dan <strong>PPN Terhutang</strong>: masukkan angka saja (contoh: 1000000), sistem akan memformat otomatis</li>
              <li>Jika field tidak diisi, nilai yang sudah ada sebelumnya akan tetap dipertahankan</li>
            </ul>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="npwp">
                <i class="fa-solid fa-id-card me-1"></i>NPWP
              </label>
              <input type="text" id="npwp" name="npwp" value="{{ old('npwp', $dokumen->npwp) }}" placeholder="00.000.000.0-000.000">
            </div>
            <div class="form-group">
              <label for="status_perpajakan">
                <i class="fa-solid fa-info-circle me-1"></i>Status Team Perpajakan
              </label>
              <select id="status_perpajakan" name="status_perpajakan">
                <option value="">Pilih Status</option>
                <option value="sedang_diproses" {{ old('status_perpajakan', $dokumen->status_perpajakan) == 'sedang_diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                <option value="selesai" {{ old('status_perpajakan', $dokumen->status_perpajakan) == 'selesai' ? 'selected' : '' }}>Selesai</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="no_faktur">
                <i class="fa-solid fa-receipt me-1"></i>No Faktur
              </label>
              <input type="text" id="no_faktur" name="no_faktur" value="{{ old('no_faktur', $dokumen->no_faktur) }}" placeholder="000.000-00.00000000">
            </div>
            <div class="form-group">
              <label for="tanggal_faktur">
                <i class="fa-solid fa-calendar me-1"></i>Tanggal Faktur
              </label>
              <input type="date" id="tanggal_faktur" name="tanggal_faktur" value="{{ old('tanggal_faktur', $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('Y-m-d') : '') }}">
            </div>
          </div>

          <div class="form-group">
            <label for="tanggal_selesai_verifikasi_pajak">
              <i class="fa-solid fa-check-circle me-1"></i>Tanggal Selesai Verifikasi Pajak
            </label>
            <input type="date" id="tanggal_selesai_verifikasi_pajak" name="tanggal_selesai_verifikasi_pajak" value="{{ old('tanggal_selesai_verifikasi_pajak', $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('Y-m-d') : '') }}">
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label for="jenis_pph">
                <i class="fa-solid fa-percent me-1"></i>Jenis PPh
              </label>
              <input type="text" id="jenis_pph" name="jenis_pph" value="{{ old('jenis_pph', $dokumen->jenis_pph) }}" placeholder="PPh 21, PPh 22, PPh 23, dll">
            </div>
            <div class="form-group">
              <label for="dpp_pph">
                <i class="fa-solid fa-money-bill-wave me-1"></i>DPP PPh
              </label>
              <input type="text" id="dpp_pph" name="dpp_pph" value="{{ old('dpp_pph', $dokumen->dpp_pph ? number_format($dokumen->dpp_pph, 0, ',', '.') : '') }}" placeholder="0">
              <small class="text-muted">Dasar Pengenaan Pajak</small>
            </div>
            <div class="form-group">
              <label for="ppn_terhutang">
                <i class="fa-solid fa-calculator me-1"></i>PPN Terhutang
              </label>
              <input type="text" id="ppn_terhutang" name="ppn_terhutang" value="{{ old('ppn_terhutang', $dokumen->ppn_terhutang ? number_format($dokumen->ppn_terhutang, 0, ',', '.') : '') }}" placeholder="0">
            </div>
          </div>

          <div class="form-group">
            <label for="link_dokumen_pajak">
              <i class="fa-solid fa-link me-1"></i>Link Dokumen Pajak
            </label>
            <input type="text" id="link_dokumen_pajak" name="link_dokumen_pajak" value="{{ old('link_dokumen_pajak', $dokumen->link_dokumen_pajak) }}" placeholder="https://drive.google.com/...">
            <small class="text-muted">Link Google Drive, Dropbox, atau penyimpanan cloud lainnya</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Buttons -->
    <div style="padding: 24px 0; display: flex; gap: 12px; justify-content: flex-end;">
      <a href="{{ route('dokumensPerpajakan.index') }}" class="btn-cancel">
        <i class="fa-solid fa-times"></i>
        Batal
      </a>
      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-save"></i>
        Simpan Perubahan
      </button>
    </div>
  </form>
</div>

<script>
// Accordion toggle
function toggleAccordion(header) {
  const content = header.nextElementSibling;
  const isActive = header.classList.contains('active');

  if (isActive) {
    header.classList.remove('active');
    content.classList.remove('active');
  } else {
    header.classList.add('active');
    content.classList.add('active');
  }
}

// Add PO field
function addPOField() {
  const container = document.getElementById('po-container');
  const newField = document.createElement('div');
  newField.className = 'form-group dynamic-field';
  newField.innerHTML = `
    <input type="text" name="nomor_po[]" placeholder="Nomor PO">
    <button type="button" class="btn-remove" onclick="removeField(this)">
      <i class="fa-solid fa-times"></i>
    </button>
  `;
  container.appendChild(newField);
}

// Add PR field
function addPRField() {
  const container = document.getElementById('pr-container');
  const newField = document.createElement('div');
  newField.className = 'form-group dynamic-field';
  newField.innerHTML = `
    <input type="text" name="nomor_pr[]" placeholder="Nomor PR">
    <button type="button" class="btn-remove" onclick="removeField(this)">
      <i class="fa-solid fa-times"></i>
    </button>
  `;
  container.appendChild(newField);
}

// Remove field
function removeField(button) {
  button.parentElement.remove();
}

// Format rupiah input
const rupiahInputs = ['nilai_rupiah', 'dpp_pph', 'ppn_terhutang'];
rupiahInputs.forEach(id => {
  const input = document.getElementById(id);
  if (input) {
    input.addEventListener('input', function(e) {
      let value = e.target.value.replace(/[^\d]/g, '');
      e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
  }
});
</script>

@endsection

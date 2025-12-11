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

  .dynamic-field {
    position: relative;
    padding-right: 40px;
  }

  .add-field-btn {
    position: absolute;
    right: 0;
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
    /* content: ''; */
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

  .perpajakan-section {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.05) 0%, rgba(255, 193, 7, 0.02) 100%);
    border: 2px solid rgba(255, 193, 7, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
  }

  .perpajakan-section .section-title {
    border-left-color: #ffc107;
    background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, transparent 100%);
  }

  .read-only-field {
    background-color: #f5f5f5 !important;
    cursor: not-allowed !important;
    opacity: 0.8;
  }

  .read-only-field:hover {
    border-color: rgba(8, 62, 64, 0.1) !important;
  }
</style>


<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

<!-- Success/Error Messages -->
@if(session('success'))
  <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: linear-gradient(135deg, #d1f2eb 0%, #b8e6d3 100%); border: 1px solid #10b981; color: #065f46;">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 1px solid #ef4444; color: #991b1b;">
    <i class="fa-solid fa-exclamation-triangle me-2"></i>{{ session('error') }}
  </div>
@endif

@if($errors->any())
  <div class="alert alert-warning" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #f59e0b; color: #92400e;">
    <h6><i class="fa-solid fa-exclamation-circle me-2"></i>Terdapat kesalahan pada input:</h6>
    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="form-container">

  <form method="POST" action="{{ route('dokumensAkutansi.update', $dokumen->id) }}">
    @csrf
    @method('PUT')
    <!-- Input Dokumen Baru -->
    <div class="section-title">Edit Dokumen Team Akutansi</div>

    <div class="form-row">
      <div class="form-group">
        <label>Nomor Agenda</label>
        <input type="text" name="nomor_agenda" placeholder="Masukkan nomor agenda"
               value="{{ old('nomor_agenda', $dokumen->nomor_agenda) }}" required>
      </div>
      <div class="form-group">
        <label>Bulan</label>
        <select name="bulan">
          <option value="">Pilih Bulan</option>
          <option value="Januari" {{ old('bulan', $dokumen->bulan) == 'Januari' ? 'selected' : '' }}>Januari</option>
          <option value="Februari" {{ old('bulan', $dokumen->bulan) == 'Februari' ? 'selected' : '' }}>Februari</option>
          <option value="Maret" {{ old('bulan', $dokumen->bulan) == 'Maret' ? 'selected' : '' }}>Maret</option>
          <option value="April" {{ old('bulan', $dokumen->bulan) == 'April' ? 'selected' : '' }}>April</option>
          <option value="Mei" {{ old('bulan', $dokumen->bulan) == 'Mei' ? 'selected' : '' }}>Mei</option>
          <option value="Juni" {{ old('bulan', $dokumen->bulan) == 'Juni' ? 'selected' : '' }}>Juni</option>
          <option value="Juli" {{ old('bulan', $dokumen->bulan) == 'Juli' ? 'selected' : '' }}>Juli</option>
          <option value="Agustus" {{ old('bulan', $dokumen->bulan) == 'Agustus' ? 'selected' : '' }}>Agustus</option>
          <option value="September" {{ old('bulan', $dokumen->bulan) == 'September' ? 'selected' : '' }}>September</option>
          <option value="Oktober" {{ old('bulan', $dokumen->bulan) == 'Oktober' ? 'selected' : '' }}>Oktober</option>
          <option value="November" {{ old('bulan', $dokumen->bulan) == 'November' ? 'selected' : '' }}>November</option>
          <option value="Desember" {{ old('bulan', $dokumen->bulan) == 'Desember' ? 'selected' : '' }}>Desember</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tahun</label>
        <input type="text" name="tahun" placeholder="2025" 
               value="{{ old('tahun', $dokumen->tahun) }}" required>
      </div>
      <div class="form-group">
        <label>Tanggal Masuk</label>
        <input type="datetime-local" name="tanggal_masuk" 
               value="{{ old('tanggal_masuk', $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('Y-m-d\TH:i') : '') }}">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Nomor SPP</label>
        <input type="text" name="nomor_spp" placeholder="123/M/SPP/13/XII/2025" 
               value="{{ old('nomor_spp', $dokumen->nomor_spp) }}" required>
      </div>
      <div class="form-group">
        <label>Tanggal SPP</label>
        <input type="date" name="tanggal_spp" 
               value="{{ old('tanggal_spp', $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('Y-m-d') : '') }}">
      </div>
    </div>

    <div class="form-group">
      <label>Uraian SPP</label>
      <textarea name="uraian_spp" placeholder="Permintaan permohonan pembayaran..." rows="3">{{ old('uraian_spp', $dokumen->uraian_spp) }}</textarea>
    </div>

    <!-- Nilai Rupiah -->
    <div class="form-row">
      <div class="form-group">
        <label>Nilai Rupiah</label>
        <input type="number" name="nilai_rupiah" placeholder="0"
               value="{{ old('nilai_rupiah', $dokumen->nilai_rupiah) }}" required>
      </div>
      <div class="form-group">
        <!-- Empty space to maintain grid layout -->
      </div>
    </div>

    <!-- Kategori & Jenis -->
    <div class="form-row-3">
      <div class="form-group">
        <label>Kategori</label>
        <select>
          <option>Pilih Opsi</option>
          <option selected>Investasi on farm</option>
          <option>Investasi off farm</option>
          <option>Exploitasi</option>
        </select>
      </div>
      <div class="form-group">
        <label>Jenis SubPekerjaan</label>
        <select>
          <option>Pilih Opsi</option>
          <option selected>Surat Masuk/Keluar Reguler</option>
          <option>Surat Undangan</option>
          <option>Memo Internal</option>
        </select>
      </div>
      <div class="form-group">
        <label>Jenis Pembayaran</label>
        <select name="jenis_pembayaran">
          <option value="">Pilih Opsi</option>
          <option value="Karyawan" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
          <option value="Mitra" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == 'Mitra' ? 'selected' : '' }}>Mitra</option>
          <option value="MPN" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == 'MPN' ? 'selected' : '' }}>MPN</option>
          <option value="TBS" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == 'TBS' ? 'selected' : '' }}>TBS</option>
          <option value="Dropping" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == 'Dropping' ? 'selected' : '' }}>Dropping</option>
          <option value="Lainnya" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
        </select>
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
            $currentKebun = old('kebun', $dokumen->kebun);
            $currentKebunClean = preg_replace('/^\d+\s+/', '', $currentKebun);
          @endphp
          @foreach($kebunOptions as $kebun)
            <option value="{{ $kebun }}" {{ ($currentKebun == $kebun || $currentKebunClean == $kebun) ? 'selected' : '' }}>{{ $kebun }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <!-- Berita Acara -->
    <div class="form-row-3">
      <div class="form-group">
        <label>Dibayar Kepada</label>
        <input type="text" value="PT. Contoh Mitra">
      </div>
      <div class="form-group">
        <label>No Berita Acara</label>
        <input type="text" placeholder="5TEP/BAST/49/SP.30/XI/2024" value="5TEP/BAST/49/SP.30/I/2025">
      </div>
      <div class="form-group">
        <label>Tanggal Berita Acara</label>
        <input type="date" value="2025-01-15">
      </div>
    </div>

    <!-- No SPK -->
    <div class="form-row-3">
      <div class="form-group">
        <label>No SPK</label>
        <input type="text" placeholder="5TEP/SP/Sawit/30/IX/2024" value="5TEP/SP/Sawit/30/I/2025">
      </div>
      <div class="form-group">
        <label>Tanggal SPK</label>
        <input type="date" value="2025-01-10">
      </div>
      <div class="form-group">
        <label>Tanggal Berakhir SPK</label>
        <input type="date" value="2025-12-31">
      </div>
    </div>

    <!-- Nomor PO (Opsional) -->
    <div class="form-group dynamic-field">
      <label>Nomor PO <span class="optional-label">(Opsional)</span></label>
      <input type="text" placeholder="Masukkan nomor PO" value="PO-2025-001">
      <button type="button" class="add-field-btn">+</button>
    </div>

    <!-- Nomor PR (Opsional) -->
    <div class="form-group dynamic-field">
      <label>Nomor PR <span class="optional-label">(Opsional)</span></label>
      <input type="text" placeholder="Masukkan nomor PR" value="PR-2025-001">
      <button type="button" class="add-field-btn">+</button>
    </div>

    <!-- SECTION INFORMASI PERPAJAKAN (Jika dokumen pernah ke perpajakan) -->
    @if($hasPerpajakanData)
    <div class="perpajakan-section">
      <div class="section-title" style="background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, transparent 100%); border-left-color: #ffc107;">
        <i class="fa-solid fa-file-invoice-dollar me-2" style="color: #ffc107;"></i>
        Informasi Team Perpajakan
        <span style="background: #ffc107; color: white; padding: 4px 12px; border-radius: 20px; font-size: 10px; margin-left: 8px;">DATA DARI PERPAJAKAN</span>
      </div>

    <div class="form-row">
      <div class="form-group">
        <label>Status Perpajakan</label>
        <select name="status_perpajakan" disabled class="read-only-field">
          <option value="">Pilih Status</option>
          <option value="sedang_diproses" {{ old('status_perpajakan', $dokumen->status_perpajakan) == 'sedang_diproses' ? 'selected' : '' }}>Sedang Diproses</option>
          <option value="selesai" {{ old('status_perpajakan', $dokumen->status_perpajakan) == 'selesai' ? 'selected' : '' }}>Selesai Verifikasi</option>
        </select>
        <small class="text-muted" style="font-size: 11px;">
          <i class="fa-solid fa-info-circle me-1"></i>
          Data ini berasal dari Team Perpajakan (read-only)
        </small>
      </div>
      <div class="form-group">
        <label>NPWP</label>
        <input type="text" value="{{ old('npwp', $dokumen->npwp ?? '') }}" disabled class="read-only-field">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>No Faktur</label>
        <input type="text" value="{{ old('no_faktur', $dokumen->no_faktur ?? '') }}" disabled class="read-only-field">
      </div>
      <div class="form-group">
        <label>Tanggal Faktur</label>
        <input type="date" value="{{ old('tanggal_faktur', $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('Y-m-d') : '') }}" disabled class="read-only-field">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tgl. Selesai Verifikasi Pajak</label>
        <input type="date" value="{{ old('tanggal_selesai_verifikasi_pajak', $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('Y-m-d') : '') }}" disabled class="read-only-field">
      </div>
      <div class="form-group">
        <label>No Invoice</label>
        <input type="text" value="{{ old('no_invoice', $dokumen->no_invoice ?? '') }}" disabled class="read-only-field">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tanggal Invoice</label>
        <input type="date" value="{{ old('tanggal_invoice', $dokumen->tanggal_invoice ? $dokumen->tanggal_invoice->format('Y-m-d') : '') }}" disabled class="read-only-field">
      </div>
      <div class="form-group">
        <label>No Kontrak</label>
        <input type="text" value="{{ old('no_kontrak', $dokumen->no_kontrak ?? '') }}" disabled class="read-only-field">
      </div>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label>Jenis PPh</label>
        <select name="jenis_pph" disabled class="read-only-field">
          <option value="">Pilih Jenis PPh</option>
          <option value="PPh 21" {{ old('jenis_pph', $dokumen->jenis_pph) == 'PPh 21' ? 'selected' : '' }}>PPh 21</option>
          <option value="PPh 22" {{ old('jenis_pph', $dokumen->jenis_pph) == 'PPh 22' ? 'selected' : '' }}>PPh 22</option>
          <option value="PPh 23" {{ old('jenis_pph', $dokumen->jenis_pph) == 'PPh 23' ? 'selected' : '' }}>PPh 23</option>
          <option value="PPh 25" {{ old('jenis_pph', $dokumen->jenis_pph) == 'PPh 25' ? 'selected' : '' }}>PPh 25</option>
          <option value="PPh 26" {{ old('jenis_pph', $dokumen->jenis_pph) == 'PPh 26' ? 'selected' : '' }}>PPh 26</option>
          <option value="PPh 29" {{ old('jenis_pph', $dokumen->jenis_pph) == 'PPh 29' ? 'selected' : '' }}>PPh 29</option>
          <option value="PPh Final" {{ old('jenis_pph', $dokumen->jenis_pph) == 'PPh Final' ? 'selected' : '' }}>PPh Final</option>
        </select>
      </div>
      <div class="form-group">
        <label>DPP PPh</label>
        <input type="text" value="{{ old('dpp_pph', $dokumen->dpp_pph ? 'Rp. ' . number_format($dokumen->dpp_pph, 0, ',', '.') : '') }}" disabled class="read-only-field">
      </div>
      <div class="form-group">
        <label>PPh Terhutang</label>
        <input type="text" value="{{ old('ppn_terhutang', $dokumen->ppn_terhutang ? 'Rp. ' . number_format($dokumen->ppn_terhutang, 0, ',', '.') : '') }}" disabled class="read-only-field">
      </div>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label>DPP Invoice</label>
        <input type="text" value="{{ old('dpp_invoice', $dokumen->dpp_invoice ? 'Rp. ' . number_format($dokumen->dpp_invoice, 0, ',', '.') : '') }}" disabled class="read-only-field">
      </div>
      <div class="form-group">
        <label>PPN Invoice</label>
        <input type="text" value="{{ old('ppn_invoice', $dokumen->ppn_invoice ? 'Rp. ' . number_format($dokumen->ppn_invoice, 0, ',', '.') : '') }}" disabled class="read-only-field">
      </div>
      <div class="form-group">
        <label>DPP + PPN Invoice</label>
        <input type="text" value="{{ old('dpp_ppn_invoice', $dokumen->dpp_ppn_invoice ? 'Rp. ' . number_format($dokumen->dpp_ppn_invoice, 0, ',', '.') : '') }}" disabled class="read-only-field">
      </div>
    </div>

    @if($dokumen->link_dokumen_pajak)
    <div class="form-group">
      <label>Link Dokumen Pajak</label>
      <div style="padding: 12px 16px; border: 2px solid rgba(8, 62, 64, 0.1); border-radius: 10px; background-color: #f5f5f5;">
        <a href="{{ $dokumen->link_dokumen_pajak }}" target="_blank" style="color: #889717; text-decoration: none;">
          <i class="fa-solid fa-external-link me-2"></i>{{ $dokumen->link_dokumen_pajak }}
        </a>
      </div>
    </div>
    @endif

    @if($dokumen->alamat_pembeli)
    <div class="form-group">
      <label>Alamat Pembeli</label>
      <textarea disabled class="read-only-field">{{ old('alamat_pembeli', $dokumen->alamat_pembeli) }}</textarea>
    </div>
    @endif
    </div>
    @endif

    <!-- SECTION KHUSUS AKUTANSI -->
    <div class="section-title">Informasi Team Akutansi</div>

    <!-- MIRO Section - Khusus Team Akutansi -->
    <div class="form-row">
      <div class="form-group">
        <label style="color: #083E40; font-weight: 700;">
          <i class="fa-solid fa-file-invoice-dollar me-2" style="color: #889717;"></i>
          Nomor MIRO
        </label>
        <input type="text"
               name="nomor_miro"
               id="nomor_miro"
               placeholder=""
               value="{{ old('nomor_miro', $dokumen->nomor_miro ?? '') }}"
               style="border: 2px solid #889717; background: linear-gradient(135deg, #f8faf8 0%, #ffffff 100%);">
        <small class="text-muted" style="font-size: 11px;">
          <i class="fa-solid fa-info-circle me-1"></i>
          Nomor MIRO wajib diisi untuk proses pembayaran
        </small>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <button type="reset" class="btn-reset">Reset</button>
      <button type="submit" class="btn-submit">Update Dokumen</button>
    </div>
  </form>
</div>

<script>
  // Script untuk menambah field dinamis
  document.querySelectorAll('.add-field-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const fieldGroup = this.closest('.form-group');
      const newField = fieldGroup.cloneNode(true);

      // Reset nilai input
      newField.querySelector('input').value = '';

      // Re-attach event listener ke tombol baru
      newField.querySelector('.add-field-btn').addEventListener('click', function(e) {
        e.preventDefault();
        // Recursive call untuk field baru
        arguments.callee.call(this, e);
      });

      // Insert setelah field saat ini
      fieldGroup.parentNode.insertBefore(newField, fieldGroup.nextSibling);
    });
  });
</script>

@endsection

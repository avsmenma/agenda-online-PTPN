@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 24px;
    font-weight: 700;
  }

  .form-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .form-group {
    margin-bottom: 24px;
  }

  .form-label {
    font-weight: 600;
    color: #083E40;
    margin-bottom: 8px;
    font-size: 14px;
    letter-spacing: 0.3px;
  }

  .form-control, .form-select {
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
  }

  .form-control:focus, .form-select:focus {
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
    outline: none;
  }

  .btn-primary {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 62, 64, 0.4);
  }

  .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
  }

  .btn-secondary:hover {
    background: linear-gradient(135deg, #868e96 0%, #6c757d 100%);
    transform: translateY(-2px);
  }

  .form-section {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 2px solid rgba(8, 62, 64, 0.1);
  }

  .form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
  }

  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #083E40;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid #889717;
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .required {
    color: #dc3545;
  }

  .form-text {
    color: #6c757d;
    font-size: 12px;
    margin-top: 4px;
  }
</style>

<h2>{{ $title }}</h2>

<div class="form-container">
  <form action="{{ route('dokumensB.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Section Informasi Umum -->
    <div class="form-section">
      <h3 class="section-title">Informasi Umum Dokumen</h3>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="nomor_surat" class="form-label">Nomor Surat <span class="required">*</span></label>
            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
            <div class="form-text">Format: XXX/XXX/XXX/YYYY</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="tanggal_masuk" class="form-label">Tanggal Masuk <span class="required">*</span></label>
            <input type="datetime-local" class="form-control" id="tanggal_masuk" name="tanggal_masuk" required>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="asal_instansi" class="form-label">Asal Instansi <span class="required">*</span></label>
        <input type="text" class="form-control" id="asal_instansi" name="asal_instansi" required>
      </div>

      <div class="form-group">
        <label for="perihal" class="form-label">Perihal <span class="required">*</span></label>
        <textarea class="form-control" id="perihal" name="perihal" rows="3" required></textarea>
      </div>
    </div>

    <!-- Section Kategori dan Prioritas -->
    <div class="form-section">
      <h3 class="section-title">Kategori dan Prioritas</h3>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="kategori" class="form-label">Kategori <span class="required">*</span></label>
            <select class="form-select" id="kategori" name="kategori" required>
              <option value="">Pilih Kategori</option>
              <option value="internal">Internal</option>
              <option value="eksternal">Eksternal</option>
              <option value="rahasia">Rahasia</option>
              <option value="biasa">Biasa</option>
              <option value="penting">Penting</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="prioritas" class="form-label">Prioritas <span class="required">*</span></label>
            <select class="form-select" id="prioritas" name="prioritas" required>
              <option value="">Pilih Prioritas</option>
              <option value="tinggi">Tinggi</option>
              <option value="sedang">Sedang</option>
              <option value="rendah">Rendah</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Informasi Kontak -->
    <div class="form-section">
      <h3 class="section-title">Informasi Kontak</h3>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="nama_pengirim" class="form-label">Nama Pengirim</label>
            <input type="text" class="form-control" id="nama_pengirim" name="nama_pengirim">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
            <input type="tel" class="form-control" id="nomor_telepon" name="nomor_telepon">
            <div class="form-text">Format: 08xx-xxxx-xxxx</div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="email_pengirim" class="form-label">Email Pengirim</label>
        <input type="email" class="form-control" id="email_pengirim" name="email_pengirim">
        <div class="form-text">Format: email@domain.com</div>
      </div>
    </div>

    <!-- Section File Upload -->
    <div class="form-section">
      <h3 class="section-title">Upload Dokumen</h3>

      <div class="form-group">
        <label for="file_dokumen" class="form-label">File Dokumen <span class="required">*</span></label>
        <input type="file" class="form-control" id="file_dokumen" name="file_dokumen" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
        <div class="form-text">Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG (Max. 10MB)</div>
      </div>

      <div class="form-group">
        <label for="keterangan" class="form-label">Keterangan Tambahan</label>
        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Tambahkan keterangan atau catatan penting terkait dokumen..."></textarea>
      </div>
    </div>

    <!-- Section Tindakan -->
    <div class="form-section">
      <h3 class="section-title">Tindakan Selanjutnya</h3>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="tujuan_disposisi" class="form-label">Tujuan Disposisi</label>
            <select class="form-select" id="tujuan_disposisi" name="tujuan_disposisi">
              <option value="">Pilih Tujuan</option>
              <option value="divisi_keuangan">Divisi Keuangan</option>
              <option value="divisi_sdm">Divisi SDM</option>
              <option value="divisi_operasional">Divisi Operasional</option>
              <option value="direktur">Direktur</option>
              <option value="manager">Manager</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="deadline" class="form-label">Deadline Tindakan</label>
            <input type="date" class="form-control" id="deadline" name="deadline">
            <div class="form-text">Biarkan kosong jika tidak ada deadline</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="d-flex justify-content-end gap-3">
      <a href="{{ route('dokumensB.index') }}" class="btn btn-secondary">
        <i class="fas fa-times me-2"></i>Batal
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>Simpan Dokumen
      </button>
    </div>
  </form>
</div>

<script>
  // Auto-generate nomor surat based on date and category
  document.getElementById('kategori').addEventListener('change', function() {
    const kategori = this.value;
    const nomorSurat = document.getElementById('nomor_surat');
    const tanggal = new Date();
    const tahun = tanggal.getFullYear();
    const bulan = String(tanggal.getMonth() + 1).padStart(2, '0');
    const hari = String(tanggal.getDate()).padStart(2, '0');

    let prefix = '';
    switch(kategori) {
      case 'internal': prefix = 'INT'; break;
      case 'eksternal': prefix = 'EXT'; break;
      case 'rahasia': prefix = 'RH'; break;
      case 'biasa': prefix = 'BS'; break;
      case 'penting': prefix = 'PT'; break;
      default: prefix = 'DOC';
    }

    const autoNomor = `${prefix}/${bulan}/${tahun}`;
    nomorSurat.value = autoNomor;
  });

  // Phone number formatting
  document.getElementById('nomor_telepon').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 12) value = value.slice(0, 12);

    if (value.length >= 4) {
      value = value.slice(0, 4) + '-' + value.slice(4);
    }
    if (value.length >= 8) {
      value = value.slice(0, 8) + '-' + value.slice(8);
    }

    e.target.value = value;
  });

  // File validation
  document.getElementById('file_dokumen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/jpeg', 'image/jpg', 'image/png'];

    if (file) {
      if (file.size > maxSize) {
        alert('Ukuran file terlalu besar! Maksimal 10MB');
        e.target.value = '';
        return;
      }

      if (!allowedTypes.includes(file.type)) {
        alert('Format file tidak didukung! Silakan pilih file dengan format yang sesuai.');
        e.target.value = '';
        return;
      }
    }
  });
</script>

@endsection
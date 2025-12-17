@extends('layouts/app')
@section('content')

<style>
  .form-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 25px 30px;
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

  .section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
    margin-top: 20px;
    padding-bottom: 12px;
    padding-left: 12px;
    border-left: 4px solid #889717;
    background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    color: #083E40;
  }

  .section-title.payment-section {
    border-left-color: #28a745;
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.08) 0%, transparent 100%);
  }

  .section-title i {
    margin-right: 8px;
  }

  .info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }

  .info-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
  }

  .info-item {
    padding: 12px 15px;
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
    min-height: 80px;
    resize: vertical;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }

  .optional-label {
    color: #889717;
    font-weight: 500;
    font-size: 12px;
    opacity: 0.8;
  }

  .required-note {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
    border: 1px solid #c8e6c9;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }

  .required-note i {
    color: #28a745;
    font-size: 20px;
    margin-top: 2px;
  }

  .required-note-text {
    flex: 1;
  }

  .required-note-text strong {
    color: #1b5e20;
    display: block;
    margin-bottom: 4px;
  }

  .required-note-text span {
    color: #2e7d32;
    font-size: 13px;
  }

  /* File Upload Styling */
  .file-upload-wrapper {
    position: relative;
    border: 2px dashed rgba(40, 167, 69, 0.3);
    border-radius: 10px;
    padding: 30px 20px;
    text-align: center;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #ffffff 0%, #f8fff8 100%);
  }

  .file-upload-wrapper:hover {
    border-color: #28a745;
    background: linear-gradient(135deg, #f8fff8 0%, #e8f5e9 100%);
  }

  .file-upload-wrapper.dragover {
    border-color: #28a745;
    background: #e8f5e9;
  }

  .file-upload-wrapper input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
  }

  .file-upload-content {
    pointer-events: none;
  }

  .file-upload-content i {
    font-size: 40px;
    color: #28a745;
    margin-bottom: 10px;
  }

  .file-upload-content p {
    margin: 0;
    color: #083E40;
    font-weight: 500;
  }

  .file-upload-content small {
    color: #6c757d;
    font-size: 12px;
  }

  .file-selected {
    display: none;
    margin-top: 15px;
    padding: 10px 15px;
    background: #d4edda;
    border-radius: 8px;
    color: #155724;
    font-size: 13px;
  }

  .file-selected.show {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .file-selected i {
    color: #28a745;
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(8, 62, 64, 0.1);
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
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(40, 167, 69, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(40, 167, 69, 0.4);
  }

  .btn-submit:active {
    transform: translateY(0);
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

  /* Alert styling */
  .alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(8, 62, 64, 0.15), transparent);
    margin: 25px 0;
  }
</style>

<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

@if(session('error'))
<div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    <div>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
</div>
@endif

<div class="form-container">
    <!-- Document Information Section (Read-Only) -->
    <div class="section-title">
        <i class="fa-solid fa-file-invoice"></i>
        Informasi Dokumen
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Nomor Agenda</div>
            <div class="info-value">{{ $dokumen->nomor_agenda ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Nomor SPP</div>
            <div class="info-value">{{ $dokumen->nomor_spp ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal Masuk</div>
            <div class="info-value">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal SPP</div>
            <div class="info-value">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</div>
        </div>
    </div>

    <div class="info-grid" style="margin-top: 15px;">
        <div class="info-item highlight">
            <div class="info-label">Nilai Rupiah</div>
            <div class="info-value currency">Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="info-item highlight">
            <div class="info-label">Dibayar Kepada</div>
            <div class="info-value">{{ $dokumen->dibayar_kepada ?? '-' }}</div>
        </div>
    </div>

    <div class="info-grid-3" style="margin-top: 15px;">
        <div class="info-item">
            <div class="info-label">Kategori</div>
            <div class="info-value">{{ $dokumen->kategori ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Jenis Dokumen</div>
            <div class="info-value">{{ $dokumen->jenis_dokumen ?? '-' }}</div>
        </div>
    </div>

    <div style="margin-top: 15px;">
        <div class="info-item">
            <div class="info-label">Uraian SPP</div>
            <div class="info-value">{{ $dokumen->uraian_spp ?? '-' }}</div>
        </div>
    </div>

    <!-- Tax Information if available -->
    @if($dokumen->npwp || $dokumen->no_faktur || $dokumen->jenis_pph)
    <div class="divider"></div>
    <div class="section-title">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        Informasi Perpajakan
    </div>

    <div class="info-grid-3">
        <div class="info-item">
            <div class="info-label">NPWP</div>
            <div class="info-value">{{ $dokumen->npwp ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">No Faktur</div>
            <div class="info-value">{{ $dokumen->no_faktur ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Jenis PPh</div>
            <div class="info-value">{{ $dokumen->jenis_pph ?? '-' }}</div>
        </div>
    </div>

    <div class="info-grid" style="margin-top: 15px;">
        <div class="info-item">
            <div class="info-label">DPP PPh</div>
            <div class="info-value">{{ $dokumen->dpp_pph ? 'Rp ' . number_format($dokumen->dpp_pph, 0, ',', '.') : '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">PPN Terhutang</div>
            <div class="info-value">{{ $dokumen->ppn_terhutang ? 'Rp ' . number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-' }}</div>
        </div>
    </div>
    @endif

    <div class="divider"></div>

    <!-- Payment Form Section -->
    <div class="section-title payment-section">
        <i class="fa-solid fa-money-check-dollar"></i>
        Input Pembayaran
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

    <div class="required-note">
        <i class="fa-solid fa-info-circle"></i>
        <div class="required-note-text">
            <strong>Petunjuk Pengisian</strong>
            <span>Isi minimal salah satu dari Tanggal Bayar atau Upload Bukti Pembayaran untuk mengubah status dokumen menjadi "Sudah Dibayar".</span>
        </div>
    </div>

    <form action="{{ route('documents.pembayaran.update', $dokumen->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label>
                    <i class="fa-solid fa-calendar-check" style="color: #28a745;"></i>
                    Tanggal Bayar <span class="optional-label">(Opsional)</span>
                </label>
                <input type="date"
                       name="tanggal_dibayar"
                       value="{{ old('tanggal_dibayar', $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('Y-m-d') : '') }}"
                       placeholder="Pilih tanggal pembayaran">
            </div>
            <div class="form-group">
                <label>
                    <i class="fa-solid fa-comment" style="color: #28a745;"></i>
                    Catatan Pembayaran <span class="optional-label">(Opsional)</span>
                </label>
                <input type="text"
                       name="catatan_pembayaran"
                       value="{{ old('catatan_pembayaran', $dokumen->catatan_pembayaran) }}"
                       placeholder="Masukkan catatan jika ada">
            </div>
        </div>

        <div class="form-group">
            <label>
                <i class="fa-solid fa-file-arrow-up" style="color: #28a745;"></i>
                Upload Bukti Pembayaran <span class="optional-label">(Opsional)</span>
            </label>
            <div class="file-upload-wrapper" id="dropZone">
                <input type="file" name="bukti_pembayaran" id="fileInput" accept=".pdf,.jpg,.jpeg,.png">
                <div class="file-upload-content">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Drag & drop file atau klik untuk memilih</p>
                    <small>Format: PDF, JPG, JPEG, PNG (Maks. 5MB)</small>
                </div>
            </div>
            <div class="file-selected" id="fileSelected">
                <i class="fa-solid fa-file-check"></i>
                <span id="fileName"></span>
            </div>

            @if($dokumen->bukti_pembayaran)
            <div style="margin-top: 10px; padding: 10px 15px; background: #e8f5e9; border-radius: 8px;">
                <i class="fa-solid fa-file-pdf" style="color: #28a745;"></i>
                <span style="margin-left: 8px; font-size: 13px;">File saat ini: {{ basename($dokumen->bukti_pembayaran) }}</span>
                <a href="{{ asset('storage/' . $dokumen->bukti_pembayaran) }}" target="_blank" style="margin-left: 10px; color: #28a745; font-size: 13px;">
                    <i class="fa-solid fa-external-link-alt"></i> Lihat
                </a>
            </div>
            @endif
        </div>

        <div class="form-actions">
            <a href="{{ route('documents.pembayaran.index') }}" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-save"></i>
                Simpan Pembayaran
            </button>
        </div>
    </form>
</div>

<script>
    // File upload handling
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileSelected = document.getElementById('fileSelected');
    const fileName = document.getElementById('fileName');

    // Drag and drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            updateFileDisplay(files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            updateFileDisplay(e.target.files[0]);
        }
    });

    function updateFileDisplay(file) {
        fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        fileSelected.classList.add('show');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
</script>

@endsection

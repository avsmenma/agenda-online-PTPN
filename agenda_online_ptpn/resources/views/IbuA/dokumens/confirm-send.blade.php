@extends('layouts/app')
@section('content')

<style>
  .confirm-card {
    max-width: 600px;
    margin: 50px auto;
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .confirm-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    font-size: 36px;
    color: white;
  }

  .confirm-title {
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    color: #083E40;
    margin-bottom: 20px;
  }

  .confirm-message {
    text-align: center;
    color: #6c757d;
    margin-bottom: 30px;
    font-size: 14px;
  }

  .document-details {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid #e0e0e0;
  }

  .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
  }

  .detail-row:last-child {
    border-bottom: none;
  }

  .detail-label {
    font-weight: 600;
    color: #495057;
  }

  .detail-value {
    color: #6c757d;
  }

  .action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
  }

  .btn-confirm {
    padding: 12px 30px;
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.2);
  }

  .btn-confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(136, 151, 23, 0.3);
  }

  .btn-cancel {
    padding: 12px 30px;
    background: white;
    color: #6c757d;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-cancel:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
  }
</style>

<div class="confirm-card">
  <div class="confirm-icon">
    <i class="fa-solid fa-paper-plane"></i>
  </div>

  <h2 class="confirm-title">Konfirmasi Pengiriman Dokumen</h2>

  <p class="confirm-message">
    Apakah Anda yakin ingin mengirim dokumen ini ke IbuB untuk proses verifikasi?
  </p>

  <div class="document-details">
    <div class="detail-row">
      <span class="detail-label">Nomor Agenda:</span>
      <span class="detail-value">{{ $dokumen->nomor_agenda }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Nomor SPP:</span>
      <span class="detail-value">{{ $dokumen->nomor_spp }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Nilai Rupiah:</span>
      <span class="detail-value">{{ $dokumen->formatted_nilai_rupiah }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Tanggal Masuk:</span>
      <span class="detail-value">{{ $dokumen->tanggal_masuk->format('d/m/Y H:i') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Status Saat Ini:</span>
      <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $dokumen->status)) }}</span>
    </div>
  </div>

  <form action="{{ route('dokumens.sendToIbuB', $dokumen->id) }}" method="POST">
    @csrf
    <div class="action-buttons">
      <button type="submit" class="btn-confirm">
        <i class="fa-solid fa-check me-2"></i>Ya, Kirim Sekarang
      </button>
      <a href="{{ route('dokumens.index') }}" class="btn-cancel">
        <i class="fa-solid fa-times me-2"></i>Batal
      </a>
    </div>
  </form>
</div>

@endsection

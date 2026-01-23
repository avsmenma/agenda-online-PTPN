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
    margin-bottom: 25px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #083E40;
    font-size: 14px;
  }

  .form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    background: white;
    color: #083E40;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-primary {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(8, 62, 64, 0.3);
  }

  .btn-secondary {
    background: #6c757d;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: 12px;
    text-decoration: none;
    display: inline-block;
  }

  .btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  @media (max-width: 768px) {
    .form-row {
      grid-template-columns: 1fr;
    }

    .form-container {
      padding: 20px;
    }
  }
</style>

<div class="form-container">
  <h2 class="form-title">{{ $title }}</h2>

  <form action="{{ route('documents.pembayaran.store') }}" method="POST">
    @csrf

    <div class="form-row">
      <div class="form-group">
        <label for="nomor_agenda" class="form-label">Nomor Agenda</label>
        <input type="text" id="nomor_agenda" name="nomor_agenda" class="form-control"
               value="{{ old('nomor_agenda') }}" required>
      </div>

      <div class="form-group">
        <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
        <input type="date" id="tanggal_masuk" name="tanggal_masuk" class="form-control"
               value="{{ old('tanggal_masuk') ?? date('Y-m-d') }}" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="nomor_spp" class="form-label">Nomor SPP</label>
        <input type="text" id="nomor_spp" name="nomor_spp" class="form-control"
               value="{{ old('nomor_spp') }}" required>
      </div>

      <div class="form-group">
        <label for="nilai_rupiah" class="form-label">Nilai Rupiah</label>
        <input type="text" id="nilai_rupiah" name="nilai_rupiah" class="form-control"
               value="{{ old('nilai_rupiah') }}" required>
      </div>
    </div>

    <div class="form-group">
      <label for="uraian_spp" class="form-label">Uraian SPP</label>
      <textarea id="uraian_spp" name="uraian_spp" class="form-control" rows="4"
                required>{{ old('uraian_spp') }}</textarea>
    </div>

    <div class="form-group">
      <label for="keterangan" class="form-label">Keterangan</label>
      <textarea id="keterangan" name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-primary">
        <i class="fas fa-save"></i> Simpan Pembayaran
      </button>
      <a href="{{ route('documents.pembayaran.index') }}" class="btn-secondary">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

@endsection


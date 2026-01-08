@extends('layouts.app')

@section('title', 'Edit Dokumen - Bagian ' . $bagianCode)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #083E40; font-weight: 700;">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Edit Dokumen
                </h2>
                <p class="text-muted mb-0">{{ $dokumen->nomor_agenda }} - {{ $dokumen->nomor_spp }}</p>
            </div>
            <a href="{{ route('bagian.documents.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <!-- Form Card -->
        <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div class="card-body p-4">
                <form action="{{ route('bagian.documents.update', $dokumen) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Section: Informasi Dasar -->
                    <div class="mb-4">
                        <h5
                            style="color: #083E40; font-weight: 600; border-bottom: 2px solid #083E40; padding-bottom: 10px;">
                            <i class="fa-solid fa-info-circle me-2"></i>Informasi Dasar
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Bagian (Auto-filled, read-only) -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bagian</label>
                            <input type="text" class="form-control" value="{{ $bagianCode }} - {{ $bagianName }}" readonly
                                style="background: #e9f7ef; border-color: #28a745; font-weight: 600;">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nomor Agenda <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_agenda" class="form-control"
                                value="{{ old('nomor_agenda', $dokumen->nomor_agenda) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nomor SPP <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_spp" class="form-control"
                                value="{{ old('nomor_spp', $dokumen->nomor_spp) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal SPP <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_spp" class="form-control"
                                value="{{ old('tanggal_spp', $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('Y-m-d') : '') }}"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nilai Rupiah <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" name="nilai_rupiah" class="form-control"
                                    value="{{ old('nilai_rupiah', number_format($dokumen->nilai_rupiah, 0, ',', '.')) }}"
                                    required id="nilai_rupiah">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nama Pengirim</label>
                            <input type="text" name="nama_pengirim" class="form-control"
                                value="{{ old('nama_pengirim', $dokumen->nama_pengirim) }}">
                        </div>
                    </div>

                    <!-- Section: Uraian -->
                    <div class="mb-4">
                        <h5
                            style="color: #083E40; font-weight: 600; border-bottom: 2px solid #083E40; padding-bottom: 10px;">
                            <i class="fa-solid fa-align-left me-2"></i>Uraian Dokumen
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Uraian SPP <span class="text-danger">*</span></label>
                            <textarea name="uraian_spp" class="form-control" rows="4"
                                required>{{ old('uraian_spp', $dokumen->uraian_spp) }}</textarea>
                        </div>
                    </div>

                    <!-- Section: Jenis Pembayaran -->
                    @if($isJenisPembayaranAvailable)
                        <div class="mb-4">
                            <h5
                                style="color: #083E40; font-weight: 600; border-bottom: 2px solid #083E40; padding-bottom: 10px;">
                                <i class="fa-solid fa-credit-card me-2"></i>Jenis Pembayaran
                            </h5>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jenis Pembayaran</label>
                                <select name="jenis_pembayaran" class="form-select">
                                    <option value="">-- Pilih Jenis Pembayaran --</option>
                                    @foreach($jenisPembayaranList as $jp)
                                        <option value="{{ $jp->nama_jenis_pembayaran }}" {{ old('jenis_pembayaran', $dokumen->jenis_pembayaran) == $jp->nama_jenis_pembayaran ? 'selected' : '' }}>
                                            {{ $jp->nama_jenis_pembayaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Section: Penerima -->
                    <div class="mb-4">
                        <h5
                            style="color: #083E40; font-weight: 600; border-bottom: 2px solid #083E40; padding-bottom: 10px;">
                            <i class="fa-solid fa-user me-2"></i>Dibayar Kepada
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div id="dibayar-kepada-container">
                                @if($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                                    @foreach($dokumen->dibayarKepadas as $index => $dk)
                                        <div class="input-group mb-2">
                                            <input type="text" name="dibayar_kepada[]" class="form-control"
                                                value="{{ $dk->nama_penerima }}" placeholder="Nama penerima">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success" onclick="addDibayarKepada()">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
                                                    <i class="fa-solid fa-minus"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2">
                                        <input type="text" name="dibayar_kepada[]" class="form-control"
                                            placeholder="Nama penerima pembayaran">
                                        <button type="button" class="btn btn-success" onclick="addDibayarKepada()">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Section: Referensi -->
                    <div class="mb-4">
                        <h5
                            style="color: #083E40; font-weight: 600; border-bottom: 2px solid #083E40; padding-bottom: 10px;">
                            <i class="fa-solid fa-link me-2"></i>Referensi
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kebun</label>
                            <input type="text" name="kebun" class="form-control"
                                value="{{ old('kebun', $dokumen->kebun) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">No. SPK</label>
                            <input type="text" name="no_spk" class="form-control"
                                value="{{ old('no_spk', $dokumen->no_spk) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal SPK</label>
                            <input type="date" name="tanggal_spk" class="form-control"
                                value="{{ old('tanggal_spk', $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <!-- Section: Nomor PO & PR -->
                    <div class="mb-4">
                        <h5
                            style="color: #083E40; font-weight: 600; border-bottom: 2px solid #083E40; padding-bottom: 10px;">
                            <i class="fa-solid fa-hashtag me-2"></i>Nomor PO & PR
                        </h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor PO</label>
                            <div id="po-container">
                                @if($dokumen->dokumenPos && $dokumen->dokumenPos->count() > 0)
                                    @foreach($dokumen->dokumenPos as $index => $po)
                                        <div class="input-group mb-2">
                                            <input type="text" name="nomor_po[]" class="form-control" value="{{ $po->nomor_po }}">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success" onclick="addPO()">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
                                                    <i class="fa-solid fa-minus"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2">
                                        <input type="text" name="nomor_po[]" class="form-control" placeholder="Nomor PO">
                                        <button type="button" class="btn btn-success" onclick="addPO()">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor PR</label>
                            <div id="pr-container">
                                @if($dokumen->dokumenPrs && $dokumen->dokumenPrs->count() > 0)
                                    @foreach($dokumen->dokumenPrs as $index => $pr)
                                        <div class="input-group mb-2">
                                            <input type="text" name="nomor_pr[]" class="form-control" value="{{ $pr->nomor_pr }}">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success" onclick="addPR()">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
                                                    <i class="fa-solid fa-minus"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2">
                                        <input type="text" name="nomor_pr[]" class="form-control" placeholder="Nomor PR">
                                        <button type="button" class="btn btn-success" onclick="addPR()">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-4" style="border-top: 1px solid #e9ecef;">
                        <a href="{{ route('bagian.documents.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-times me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4"
                            style="background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%); border: none;">
                            <i class="fa-solid fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('nilai_rupiah').addEventListener('input', function (e) {
            let value = this.value.replace(/\D/g, '');
            this.value = new Intl.NumberFormat('id-ID').format(value);
        });

        function addDibayarKepada() {
            const container = document.getElementById('dibayar-kepada-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
        <input type="text" name="dibayar_kepada[]" class="form-control" placeholder="Nama penerima">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
          <i class="fa-solid fa-minus"></i>
        </button>
      `;
            container.appendChild(div);
        }

        function addPO() {
            const container = document.getElementById('po-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
        <input type="text" name="nomor_po[]" class="form-control" placeholder="Nomor PO">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
          <i class="fa-solid fa-minus"></i>
        </button>
      `;
            container.appendChild(div);
        }

        function addPR() {
            const container = document.getElementById('pr-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
        <input type="text" name="nomor_pr[]" class="form-control" placeholder="Nomor PR">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
          <i class="fa-solid fa-minus"></i>
        </button>
      `;
            container.appendChild(div);
        }
    </script>
@endsection
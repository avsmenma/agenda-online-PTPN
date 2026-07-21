@extends('layouts.app')

@section('content')
{{--
  View Tabulator (pilot) untuk Daftar Dokumen Operator.
  Toolbar filter (search/year/status_filter) tersambung ke Tabulator via AJAX (Tugas 7c)
  dan diinisialisasi dari request() agar bertahan lepas reload kustomisasi kolom.
  Tabel di-mount oleh public/js/operator-tabulator.js membaca window.OPERATOR_TABULATOR_CONFIG.
--}}
@php
    // Peta kolom terpilih menjadi {key,label} untuk definisi kolom Tabulator.
    $selectedColumns = $selectedColumns ?? [];
    $availableColumns = $availableColumns ?? [];

    // Template URL berparameter dibangun via placeholder __ID__ lalu ditukar {id}
    // agar klien (JS) cukup mengganti {id} dengan id baris saat memanggil endpoint.
    $configArray = [
        'dataUrl'          => route('documents.data'),
        'inlineCreateUrl'  => route('documents.inline-create'),
        'inlineUpdateTpl'  => str_replace('__ID__', '{id}', route('documents.inline-update', ['dokumen' => '__ID__'])),
        'handlerTpl'       => str_replace('__ID__', '{id}', route('documents.handler.update', ['dokumen' => '__ID__'])),
        'detailTpl'        => str_replace('__ID__', '{id}', route('documents.detail', ['dokumen' => '__ID__'])),
        'destroyTpl'       => str_replace('__ID__', '{id}', route('documents.destroy', ['dokumen' => '__ID__'])),
        'csrf'             => csrf_token(),
        'columns'          => collect($selectedColumns)->map(fn ($k) => ['key' => $k, 'label' => $availableColumns[$k] ?? $k])->values(),
        'availableColumns' => $availableColumns,
        'selected'         => array_values($selectedColumns),
        'ie'               => [
            'kategori' => $ieKategoriList ?? [],
            'sub'      => $ieSubKriteriaList ?? [],
            'item'     => $ieItemSubKriteriaList ?? [],
            'jenis'    => $ieJenisPembayaranList ?? [],
            'bagian'   => \App\Models\Bagian::active()->ordered()->get(['kode', 'nama']),
        ],
        'bulanList'        => ['Januari', 'Februari', 'Maret', 'April', 'May', 'Juni', 'July', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    ];
@endphp

<div class="tabulator-page">
    {{-- Toolbar filter (Tugas 7c: menggerakkan Tabulator via AJAX tanpa reload). --}}
    {{-- Opsi tahun & status disalin dari daftarDokumen.blade.php:2134-2180. --}}
    <div class="tabulator-toolbar">
        <input type="text" name="search" class="form-control tabulator-toolbar-search"
               placeholder="Cari dokumen..." autocomplete="off" value="{{ request('search') }}">

        <select name="year" class="form-select" style="max-width: 140px;">
            <option value="">Semua Tahun</option>
            @for ($y = (int) date('Y'); $y >= (int) date('Y') - 5; $y--)
              <option value="{{ $y }}" {{ (string) request('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>

        <select name="status_filter" class="form-select" style="max-width: 260px;">
            <option value="">Semua Status</option>
            <option value="belum_dikirim" {{ request('status_filter') == 'belum_dikirim' ? 'selected' : '' }}>Belum Dikirim</option>
            <option value="menunggu_approval" {{ request('status_filter') == 'menunggu_approval' ? 'selected' : '' }}>Menunggu Approve Team Verifikasi</option>
            <option value="terkirim" {{ request('status_filter') == 'terkirim' ? 'selected' : '' }}>Terkirim</option>
        </select>

        {{-- CLAUDE.md §8: dobel-klik kini memulai edit, jadi modal Detail Dokumen
             pindah ke tombol ini — membuka detail untuk baris yang sel-nya aktif. --}}
        <button type="button" class="btn btn-outline-secondary" id="btnDetailBarisAktif"
                title="Buka detail dokumen pada baris yang sedang aktif">
            <i class="fa-solid fa-eye me-1"></i> Detail
        </button>

        <button type="button" class="btn btn-outline-secondary" onclick="openColumnCustomizationModal()">
            <i class="fa-solid fa-table-columns me-1"></i> Kustomisasi Kolom
        </button>

        <button type="button" class="btn btn-primary" id="btnTambahBarisTabulator" disabled>
            <i class="fa-solid fa-plus me-1"></i> Tambah Baris
        </button>

        {{-- Petunjuk pintasan: fitur §8 seluruhnya berbasis keyboard, tanpa petunjuk
             ini pengguna tidak punya cara menemukannya. --}}
        <small class="text-muted ms-auto"
               title="Panah: pindah sel &#10;Dobel-klik atau Enter: mulai edit (Esc batal)&#10;Drag / Shift+Klik / Shift+Panah: pilih blok&#10;Ctrl+C: salin &#10;Ctrl+V: tempel&#10;Delete: kosongkan&#10;Ctrl+Z: batalkan &#10;Ctrl+Y: ulangi">
            <i class="fa-regular fa-keyboard me-1"></i>
            Panah pindah sel &middot; Enter edit &middot; Shift+Panah blok &middot; Ctrl+C/V &middot; Delete &middot; Ctrl+Z/Y
        </small>
    </div>

    {{-- id="documentTableContainer" WAJIB: partial global document-workbench-ui bergantung padanya --}}
    <div class="table-section table-dokumen" id="documentTableContainer">
        <div id="operatorTabulatorTable"></div>
    </div>
</div>

{{--
  ============================================================================
  Tugas 7a/7d/7e — Modal & form disalin dari daftarDokumen.blade.php agar view
  lama tetap utuh (duplikasi sementara; view lama dihapus di Tugas 8).
  ============================================================================
--}}

{{-- Form GET tersembunyi untuk kustomisasi kolom (Tugas 7d). saveColumnCustomization()
     menambah columns[] + enable_customization lalu submit → reload view Tabulator
     (TANPA ?classic) dengan set kolom baru dari session. --}}
<form action="{{ route('documents.index') }}" method="GET" id="filterForm" class="d-none"></form>

{{-- Form DELETE tersembunyi (Tugas 7a) — hapus tetap full-page submit + redirect. --}}
<form id="deleteDocumentForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- ==== Modal Detail Dokumen (disalin daftarDokumen.blade.php:4168-4397) ==== --}}
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 90%; width: 90%;">
        <div class="modal-content" style="height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header" style="position: sticky; top: 0; z-index: 1050; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-bottom: none; flex-shrink: 0;">
                <h5 class="modal-title" id="viewDocumentModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
                    <i class="fa-solid fa-file-lines me-2"></i>
                    Detail Dokumen Lengkap
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 140px); padding: 24px; flex: 1;">
                <input type="hidden" id="view-dokumen-id">

                <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-id-card"></i>
                            IDENTITAS DOKUMEN
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Nomor Agenda</label><div class="detail-value" id="view-nomor-agenda">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Nomor SPP</label><div class="detail-value" id="view-nomor-spp">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Tanggal SPP</label><div class="detail-value" id="view-tanggal-spp">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Bulan</label><div class="detail-value" id="view-bulan">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Tahun</label><div class="detail-value" id="view-tahun">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Tanggal Masuk</label><div class="detail-value" id="view-tanggal-masuk">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Kriteria CF</label><div class="detail-value" id="view-kategori">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Sub Kriteria</label><div class="detail-value" id="view-jenis-dokumen">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Item Sub Kriteria</label><div class="detail-value" id="view-jenis-sub-pekerjaan">-</div></div></div>
                        <div class="col-md-4"><div class="detail-item"><label class="detail-label">Jenis Pembayaran</label><div class="detail-value" id="view-jenis-pembayaran">-</div></div></div>
                    </div>
                </div>

                <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            DETAIL KEUANGAN & VENDOR
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-12"><div class="detail-item"><label class="detail-label">Uraian SPP</label><div class="detail-value" id="view-uraian-spp" style="white-space: pre-wrap;">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Nilai Rupiah</label><div class="detail-value" id="view-nilai-rupiah" style="font-weight: 700; color: #083E40;">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Ejaan Nilai Rupiah</label><div class="detail-value" id="view-ejaan-nilai-rupiah" style="font-style: italic; color: #666;">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Dibayar Kepada (Vendor)</label><div class="detail-value" id="view-dibayar-kepada">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Kebun / Unit Kerja</label><div class="detail-value" id="view-kebun">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Bagian</label><div class="detail-value" id="view-bagian">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Nama Pengirim</label><div class="detail-value" id="view-nama-pengirim">-</div></div></div>
                    </div>
                </div>

                <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-file-contract"></i>
                            REFERENSI PENDUKUNG
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><div class="detail-item"><label class="detail-label">No. SPK</label><div class="detail-value" id="view-no-spk">-</div></div></div>
                        <div class="col-md-3"><div class="detail-item"><label class="detail-label">Tanggal SPK</label><div class="detail-value" id="view-tanggal-spk">-</div></div></div>
                        <div class="col-md-3"><div class="detail-item"><label class="detail-label">Tanggal Berakhir SPK</label><div class="detail-value" id="view-tanggal-berakhir-spk">-</div></div></div>
                        <div class="col-md-3"><div class="detail-item"><label class="detail-label">Nomor Miro/SES</label><div class="detail-value" id="view-nomor-miro">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">No. Berita Acara</label><div class="detail-value" id="view-no-berita-acara">-</div></div></div>
                        <div class="col-md-3"><div class="detail-item"><label class="detail-label">Tanggal Berita Acara</label><div class="detail-value" id="view-tanggal-berita-acara">-</div></div></div>
                    </div>
                </div>

                <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-hashtag"></i>
                            NOMOR PO & PR
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Nomor PO</label><div class="detail-value" id="view-nomor-po">-</div></div></div>
                        <div class="col-md-6"><div class="detail-item"><label class="detail-label">Nomor PR</label><div class="detail-value" id="view-nomor-pr">-</div></div></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="position: sticky; bottom: 0; z-index: 1050; background: white; border-top: 1px solid #dee2e6; padding: 16px 24px; flex-shrink: 0;">
                <button type="button" class="btn btn-danger" onclick="confirmDeleteDocument()">
                    <i class="fa-solid fa-trash me-2"></i>Hapus
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==== Modal Konfirmasi Hapus (disalin daftarDokumen.blade.php:4429-4490) ==== --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; padding: 20px 24px;">
                <h5 class="modal-title" id="deleteConfirmModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Konfirmasi Hapus Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fa-solid fa-trash-can" style="font-size: 32px; color: #dc3545;"></i>
                    </div>
                    <h5 style="color: #1f2937; font-weight: 600; margin-bottom: 8px;">Apakah Anda yakin ingin menghapus dokumen ini?</h5>
                    <p style="color: #6b7280; font-size: 14px; margin-bottom: 0;">Dokumen yang dihapus tidak dapat dikembalikan.</p>
                </div>
                <div style="background: #f8f9fa; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: grid; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Nomor Agenda</span>
                            <span id="delete-nomor-agenda" style="color: #1f2937; font-weight: 600; font-size: 14px;">-</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Nomor SPP</span>
                            <span id="delete-nomor-spp" style="color: #1f2937; font-weight: 600; font-size: 14px;">-</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Nilai</span>
                            <span id="delete-nilai" style="color: #28a745; font-weight: 700; font-size: 14px;">-</span>
                        </div>
                    </div>
                </div>
                <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border: 1px solid #ffc107; border-radius: 10px; padding: 12px 16px; display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fa-solid fa-exclamation-circle" style="color: #856404; font-size: 18px; margin-top: 2px;"></i>
                    <div>
                        <strong style="color: #856404; font-size: 13px; display: block; margin-bottom: 4px;">Perhatian!</strong>
                        <span style="color: #856404; font-size: 12px;">Semua data dokumen termasuk nomor PO, PR, dan data terkait lainnya akan dihapus secara permanen.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px; background: #f8f9fa;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 24px; border-radius: 8px; font-weight: 600;">
                    <i class="fa-solid fa-times me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="executeDeleteDocument()" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none;">
                    <i class="fa-solid fa-trash me-2"></i>Ya, Hapus Dokumen
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==== Toast sukses hapus (disalin daftarDokumen.blade.php:4493-4503) ==== --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
    <div id="deleteSuccessToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px;">
        <div class="d-flex">
            <div class="toast-body" style="padding: 16px 20px; font-size: 14px;">
                <i class="fa-solid fa-check-circle me-2"></i>
                <strong>Berhasil!</strong> Dokumen telah dihapus.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- ==== Modal Alasan Penolakan global tunggal (Tugas 7e) ==== --}}
<div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; padding: 1.5rem 2rem;">
                <h5 class="modal-title" id="rejectReasonModalLabel" style="font-size: 1.25rem; font-weight: 600;">
                    <i class="fa-solid fa-times-circle me-2"></i>Detail Penolakan Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;"></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div class="mb-3">
                    <label class="form-label fw-bold">Ditolak / Dikembalikan Oleh:</label>
                    <p class="mb-0" id="reject-reason-by">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal:</label>
                    <p class="mb-0" id="reject-reason-at">-</p>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Alasan Penolakan:</label>
                    <div class="alert alert-warning mb-0">
                        <p class="mb-0" id="reject-reason-text" style="white-space: pre-wrap;">Tidak ada alasan yang dicatat</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ==== Modal Kustomisasi Kolom (disalin daftarDokumen.blade.php:2596-2727) ==== --}}
<div class="customization-modal" id="columnCustomizationModal">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3>
                <i class="fa-solid fa-table-columns"></i>
                Kustomisasi Kolom Tabel
            </h3>
        </div>

        <div class="modal-body-custom">
            <div class="customization-grid">
                <div class="selection-panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <i class="fa-solid fa-check-square"></i>
                            Pilih Kolom
                        </div>
                        <div class="panel-actions">
                            <button type="button" class="btn-select-action btn-select-all" onclick="selectAllColumns()">
                                <i class="fa-solid fa-check-double"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn-select-action btn-remove-all" onclick="removeAllColumns()">
                                <i class="fa-solid fa-times"></i> Hapus Semua
                            </button>
                        </div>
                    </div>
                    <div class="panel-description">
                        Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
                    </div>
                    <div class="column-selection-list" id="columnSelectionList">
                        @foreach($availableColumns as $key => $label)
                            <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}" data-column="{{ $key }}"
                                draggable="{{ in_array($key, $selectedColumns) ? 'true' : 'false' }}" onclick="toggleColumn(this)">
                                <div class="drag-handle">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </div>
                                <input type="checkbox" class="column-item-checkbox" value="{{ $key }}" {{ in_array($key, $selectedColumns) ? 'checked' : '' }} onclick="event.stopPropagation()">
                                <label class="column-item-label">{{ $label }}</label>
                                <span class="column-item-order">
                                    {{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="preview-panel">
                    <div class="panel-title">
                        <i class="fa-solid fa-eye"></i>
                        Preview Hasil
                    </div>
                    <div class="panel-description">
                        Preview tabel akan menampilkan kolom yang Anda pilih sesuai urutan.
                    </div>
                    <div class="preview-container">
                        <div id="tablePreview">
                            @if(count($selectedColumns) > 0)
                                <table class="preview-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            @foreach($selectedColumns as $col)
                                                <th>{{ $availableColumns[$col] ?? $col }}</th>
                                            @endforeach
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($i = 1; $i <= 5; $i++)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                @foreach($selectedColumns as $col)
                                                    <td>Contoh Data {{ $i }}</td>
                                                @endforeach
                                                <td>Edit, Kirim</td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-preview">
                                    <i class="fa-solid fa-table"></i>
                                    <p>Belum ada kolom yang dipilih</p>
                                    <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer-custom">
            <div class="selected-count">
                <strong id="selectedColumnCount">{{ count($selectedColumns) }}</strong> kolom dipilih
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeColumnCustomizationModal()">
                    <i class="fa-solid fa-times"></i>
                    Batal
                </button>
                <button type="button" class="btn-modal btn-save" id="saveCustomizationBtn" onclick="saveColumnCustomization()">
                    <i class="fa-solid fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tabulator-agenda.css') }}">
    <style>
    /* Toolbar filter Tabulator */
    .tabulator-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; }
    .tabulator-toolbar-search { max-width: 320px; }

    /* Detail Item Styles (disalin daftarDokumen.blade.php:4400-4425) */
    .detail-item { margin-bottom: 8px; }
    .detail-label { display: block; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .detail-value { font-size: 14px; color: #1f2937; padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid #e5e7eb; min-height: 38px; display: flex; align-items: center; }

    /* Modal Customization Styles (disalin daftarDokumen.blade.php:358-910) */
    .customization-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; overflow-y: auto; padding: 20px; box-sizing: border-box; }
    .customization-modal.show { display: flex; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-content-custom { background: white; border-radius: 20px; box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25); max-width: 90%; width: 90%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; animation: slideIn 0.3s ease; }
    @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-header-custom { background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 24px 40px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
    .modal-header-custom h3 { margin: 0; font-size: 24px; font-weight: 600; color: #212529; display: flex; align-items: center; gap: 12px; }
    .modal-body-custom { padding: 24px 32px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; }
    .customization-grid { display: flex; flex-direction: column; gap: 24px; flex: 1; min-height: 0; }
    .selection-panel { background: #f8f9fa; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef; display: flex; flex-direction: column; flex-shrink: 0; }
    .panel-title { font-size: 18px; font-weight: 600; color: #212529; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
    .panel-description { font-size: 13px; color: #6c757d; margin-bottom: 16px; line-height: 1.6; }
    .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .panel-actions { display: flex; gap: 8px; }
    .btn-select-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-select-action:hover { border-color: #083E40; color: #083E40; }
    .btn-select-action.btn-select-all:hover { background: rgba(34, 197, 94, 0.1); border-color: #22c55e; color: #22c55e; }
    .btn-select-action.btn-remove-all:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #ef4444; }
    .column-selection-list { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; max-height: 200px; overflow-y: auto; padding: 8px; background: white; border-radius: 8px; border: 1px solid #dee2e6; }
    @media (max-width: 900px) { .column-selection-list { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 600px) { .column-selection-list { grid-template-columns: repeat(2, 1fr); } }
    .column-item { display: flex; align-items: center; padding: 10px 12px; background: #ffffff; border-radius: 8px; border: 2px solid #e9ecef; cursor: move; transition: all 0.2s ease; position: relative; user-select: none; min-height: 44px; gap: 8px; }
    .column-item:hover { border-color: #0066cc; background: #f8f9ff; box-shadow: 0 2px 8px rgba(0, 102, 204, 0.1); }
    .column-item.selected { border-color: #28a745; background: #f0f9f4; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15); }
    .column-item.dragging { opacity: 0.6; transform: scale(0.98); box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); z-index: 1000; }
    .column-item.drag-over { border-color: #0066cc; border-style: dashed; background: #e7f3ff; transform: translateX(8px); }
    .drag-handle { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; color: #6c757d; cursor: grab; flex-shrink: 0; font-size: 12px; }
    .drag-handle:active { cursor: grabbing; }
    .column-item.selected .drag-handle { color: #28a745; }
    .column-item:not(.selected) .drag-handle { opacity: 0.3; cursor: default; }
    .column-item-checkbox { width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; }
    .column-item-label { font-size: 14px; color: #212529; font-weight: 500; flex: 1; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .column-item-order { width: 24px; height: 24px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; opacity: 0; transform: scale(0); transition: all 0.2s ease; flex-shrink: 0; }
    .column-item.selected .column-item-order { opacity: 1; transform: scale(1); }
    .preview-panel { background: #ffffff; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef; display: flex; flex-direction: column; flex: 1; min-height: 0; }
    .preview-container { flex: 1; overflow-x: auto; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 16px; min-height: 400px; width: 100%; }
    .preview-table { width: 100%; min-width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); font-size: 13px; table-layout: auto; }
    .preview-table thead { position: sticky; top: 0; z-index: 10; }
    .preview-table th { background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px; border-right: 1px solid rgba(255, 255, 255, 0.1); white-space: nowrap; }
    .preview-table th:last-child { border-right: none; }
    .preview-table tbody tr { border-bottom: 1px solid #e9ecef; transition: background 0.2s ease; }
    .preview-table tbody tr:hover { background: #f8f9fa; }
    .preview-table tbody tr:last-child { border-bottom: none; }
    .preview-table td { padding: 12px; text-align: center; border-right: 1px solid #e9ecef; color: #495057; font-size: 13px; }
    .preview-table td:last-child { border-right: none; }
    .empty-preview { text-align: center; padding: 60px 20px; color: #6c757d; }
    .empty-preview i { font-size: 48px; color: #adb5bd; margin-bottom: 16px; }
    .empty-preview p { font-size: 16px; font-weight: 500; margin-bottom: 8px; }
    .empty-preview small { font-size: 14px; color: #868e96; }
    .modal-footer-custom { padding: 20px 40px; border-top: 1px solid #e9ecef; background: #ffffff; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-shrink: 0; position: sticky; bottom: 0; z-index: 100; box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05); }
    .selected-count { font-size: 15px; color: #495057; font-weight: 500; }
    .selected-count strong { color: #28a745; font-size: 18px; }
    .modal-actions { display: flex; gap: 12px; }
    .btn-modal { padding: 12px 32px; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; min-height: 48px; display: inline-flex; align-items: center; gap: 8px; }
    .btn-cancel { background: #6c757d; color: white; }
    .btn-cancel:hover { background: #5a6268; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3); }
    .btn-save { background: #28a745; color: white; }
    .btn-save:hover { background: #218838; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); }
    .btn-save:disabled { background: #adb5bd; cursor: not-allowed; transform: none; box-shadow: none; }
    @media (max-width: 768px) {
        .customization-modal { padding: 10px; }
        .modal-content-custom { max-height: 95vh; }
        .modal-header-custom, .modal-body-custom, .modal-footer-custom { padding: 20px; }
        .modal-header-custom h3 { font-size: 20px; }
        .modal-footer-custom { flex-direction: column; align-items: stretch; }
        .selected-count { text-align: center; margin-bottom: 12px; }
        .modal-actions { justify-content: stretch; }
        .btn-modal { flex: 1; justify-content: center; }
    }
    </style>
@endpush

@push('scripts')
    <script>window.OPERATOR_TABULATOR_CONFIG = @json($configArray);</script>
    <script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>
    <script src="{{ asset('js/operator-tabulator.js') }}"></script>
@endpush

@push('scripts')
{{-- Modal Detail + Hapus + Kustomisasi Kolom — JS disalin dari daftarDokumen.blade.php
     (Tugas 7a/7d). Fungsi global dipanggil oleh operator-tabulator.js (dblclick) &
     tombol toolbar. bootstrap.* tersedia (bundle dimuat layout sebelum @stack). --}}
<script>
    // ==== Detail Dokumen (daftarDokumen.blade.php:3957-4048) ====
    function openViewDocumentModal(docId) {
        document.getElementById('view-dokumen-id').value = docId;

        fetch(window.OPERATOR_TABULATOR_CONFIG.detailTpl.replace('{id}', docId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
                return response.json();
            })
            .then(data => {
                if (data.success && data.dokumen) {
                    const dok = data.dokumen;

                    document.getElementById('view-nomor-agenda').textContent = dok.nomor_agenda || '-';
                    document.getElementById('view-nomor-spp').textContent = dok.nomor_spp || '-';
                    document.getElementById('view-tanggal-spp').textContent = dok.tanggal_spp ? formatDate(dok.tanggal_spp) : '-';
                    const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    const formatBulan = (bulan) => {
                        if (!bulan) return '-';
                        const num = parseInt(bulan);
                        return (num >= 1 && num <= 12) ? monthNames[num] : bulan;
                    };
                    document.getElementById('view-bulan').textContent = formatBulan(dok.bulan);
                    document.getElementById('view-tahun').textContent = dok.tahun || '-';
                    document.getElementById('view-tanggal-masuk').textContent = dok.tanggal_masuk ? formatDateTime(dok.tanggal_masuk) : '-';
                    document.getElementById('view-jenis-dokumen').textContent = dok.jenis_dokumen || '-';
                    document.getElementById('view-jenis-sub-pekerjaan').textContent = dok.jenis_sub_pekerjaan || '-';
                    document.getElementById('view-kategori').textContent = dok.kategori || '-';
                    document.getElementById('view-jenis-pembayaran').textContent = dok.jenis_pembayaran || '-';

                    document.getElementById('view-uraian-spp').textContent = dok.uraian_spp || '-';
                    document.getElementById('view-nilai-rupiah').textContent = dok.nilai_rupiah ? 'Rp. ' + formatNumber(dok.nilai_rupiah) : '-';
                    if (dok.nilai_rupiah && dok.nilai_rupiah > 0) {
                        document.getElementById('view-ejaan-nilai-rupiah').textContent = terbilangRupiah(dok.nilai_rupiah);
                    } else {
                        document.getElementById('view-ejaan-nilai-rupiah').textContent = '-';
                    }
                    document.getElementById('view-dibayar-kepada').textContent = dok.dibayar_kepada || '-';
                    document.getElementById('view-kebun').textContent = dok.kebun || '-';
                    document.getElementById('view-bagian').textContent = dok.bagian || '-';
                    document.getElementById('view-nama-pengirim').textContent = dok.nama_pengirim || '-';

                    document.getElementById('view-no-spk').textContent = dok.no_spk || '-';
                    document.getElementById('view-tanggal-spk').textContent = dok.tanggal_spk ? formatDate(dok.tanggal_spk) : '-';
                    document.getElementById('view-tanggal-berakhir-spk').textContent = dok.tanggal_berakhir_spk ? formatDate(dok.tanggal_berakhir_spk) : '-';
                    document.getElementById('view-nomor-miro').textContent = dok.nomor_miro || '-';
                    document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
                    document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDate(dok.tanggal_berita_acara) : '-';

                    const poList = dok.NO_PO || (dok.dokumen_pos && dok.dokumen_pos.length > 0
                        ? dok.dokumen_pos.map(po => po.nomor_po).join(', ')
                        : '-');
                    const prList = dok.dokumen_prs && dok.dokumen_prs.length > 0
                        ? dok.dokumen_prs.map(pr => pr.nomor_pr).join(', ')
                        : '-';
                    document.getElementById('view-nomor-po').textContent = poList;
                    document.getElementById('view-nomor-pr').textContent = prList;

                    const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                    modal.show();
                } else {
                    alert('Gagal memuat data dokumen: ' + (data.message || 'Format respons tidak valid'));
                }
            })
            .catch(error => {
                alert('Gagal memuat data dokumen: ' + error.message);
            });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    function formatNumber(num) {
        if (!num) return '-';
        return new Intl.NumberFormat('id-ID').format(num);
    }
    function terbilangRupiah(number) {
        number = parseFloat(number) || 0;
        if (number == 0) { return 'nol rupiah'; }
        const angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
            'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
        let hasil = '';
        if (number >= 1000000000000) { const triliun = Math.floor(number / 1000000000000); hasil += terbilangSatuan(triliun, angka) + ' triliun '; number = number % 1000000000000; }
        if (number >= 1000000000) { const milyar = Math.floor(number / 1000000000); hasil += terbilangSatuan(milyar, angka) + ' milyar '; number = number % 1000000000; }
        if (number >= 1000000) { const juta = Math.floor(number / 1000000); hasil += terbilangSatuan(juta, angka) + ' juta '; number = number % 1000000; }
        if (number >= 1000) { const ribu = Math.floor(number / 1000); if (ribu == 1) { hasil += 'seribu '; } else { hasil += terbilangSatuan(ribu, angka) + ' ribu '; } number = number % 1000; }
        if (number > 0) { hasil += terbilangSatuan(number, angka); }
        return hasil.trim() + ' rupiah';
    }
    function terbilangSatuan(number, angka) {
        let hasil = '';
        number = parseInt(number);
        if (number == 0) { return ''; }
        if (number >= 100) { const ratus = Math.floor(number / 100); if (ratus == 1) { hasil += 'seratus '; } else { hasil += angka[ratus] + ' ratus '; } number = number % 100; }
        if (number > 0) {
            if (number < 20) { hasil += angka[number] + ' '; }
            else {
                const puluhan = Math.floor(number / 10);
                const satuan = number % 10;
                if (puluhan == 1) { hasil += angka[10 + satuan] + ' '; }
                else { hasil += angka[puluhan] + ' puluh '; if (satuan > 0) { hasil += angka[satuan] + ' '; } }
            }
        }
        return hasil.trim();
    }

    // ==== Hapus Dokumen (daftarDokumen.blade.php:4512-4582) — tetap full-page submit + redirect ====
    let documentIdToDelete = null;

    function confirmDeleteDocument() {
        const dokumenId = document.getElementById('view-dokumen-id').value;
        const nomorAgenda = document.getElementById('view-nomor-agenda').textContent;
        const nomorSpp = document.getElementById('view-nomor-spp').textContent;
        const nilaiRupiah = document.getElementById('view-nilai-rupiah') ? document.getElementById('view-nilai-rupiah').textContent : '-';

        if (!dokumenId) { alert('ID Dokumen tidak ditemukan'); return; }

        documentIdToDelete = dokumenId;
        document.getElementById('delete-nomor-agenda').textContent = nomorAgenda || '-';
        document.getElementById('delete-nomor-spp').textContent = nomorSpp || '-';
        document.getElementById('delete-nilai').textContent = nilaiRupiah || '-';

        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.innerHTML = '<i class="fa-solid fa-trash me-2"></i>Ya, Hapus Dokumen';
        confirmBtn.disabled = false;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteModal.show();
    }

    function executeDeleteDocument() {
        if (!documentIdToDelete) { alert('ID Dokumen tidak ditemukan'); return; }
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menghapus...';
        confirmBtn.disabled = true;
        const form = document.getElementById('deleteDocumentForm');
        form.action = window.OPERATOR_TABULATOR_CONFIG.destroyTpl.replace('{id}', documentIdToDelete);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success') && str_contains(session('success'), 'hapus'))
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            });
            setTimeout(() => {
                const toast = new bootstrap.Toast(document.getElementById('deleteSuccessToast'), { autohide: true, delay: 5000 });
                toast.show();
            }, 500);
        @endif
    });

    // ==== Kustomisasi Kolom (daftarDokumen.blade.php:3236-3619) ====
    let selectedColumnsOrder = [];
    let availableColumnsData = @json($availableColumns);
    @if(count($selectedColumns) > 0)
        selectedColumnsOrder = @json(array_values($selectedColumns));
    @endif

    function openColumnCustomizationModal() {
        const modal = document.getElementById('columnCustomizationModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        initializeModalState();
    }
    function closeColumnCustomizationModal() {
        const modal = document.getElementById('columnCustomizationModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    function toggleColumn(columnElement) {
        const columnKey = columnElement.dataset.column;
        const checkbox = columnElement.querySelector('.column-item-checkbox');
        const isChecked = checkbox.checked;
        if (!isChecked) {
            if (!selectedColumnsOrder.includes(columnKey)) { selectedColumnsOrder.push(columnKey); }
            checkbox.checked = true;
            columnElement.classList.add('selected');
            columnElement.setAttribute('draggable', 'true');
        } else {
            selectedColumnsOrder = selectedColumnsOrder.filter(key => key !== columnKey);
            checkbox.checked = false;
            columnElement.classList.remove('selected');
            columnElement.setAttribute('draggable', 'false');
        }
        updateColumnOrderBadges();
        updatePreviewTable();
        updateSelectedCount();
        updateDraggableState();
    }
    function selectAllColumns() {
        const allKeys = Object.keys(availableColumnsData);
        selectedColumnsOrder = allKeys;
        document.querySelectorAll('.column-item').forEach(item => {
            item.classList.add('selected');
            item.setAttribute('draggable', 'true');
            item.querySelector('.column-item-checkbox').checked = true;
        });
        updateColumnOrderBadges();
        updatePreviewTable();
        updateSelectedCount();
        updateDraggableState();
    }
    function removeAllColumns() {
        selectedColumnsOrder = [];
        document.querySelectorAll('.column-item').forEach(item => {
            item.classList.remove('selected');
            item.setAttribute('draggable', 'false');
            item.querySelector('.column-item-checkbox').checked = false;
        });
        updateColumnOrderBadges();
        updatePreviewTable();
        updateSelectedCount();
        updateDraggableState();
    }
    function updateColumnOrderBadges() {
        document.querySelectorAll('.column-item').forEach(item => {
            const columnKey = item.dataset.column;
            const orderBadge = item.querySelector('.column-item-order');
            const index = selectedColumnsOrder.indexOf(columnKey);
            orderBadge.textContent = index !== -1 ? (index + 1) : '';
        });
    }
    function updatePreviewTable() {
        const previewContainer = document.getElementById('tablePreview');
        if (selectedColumnsOrder.length === 0) {
            previewContainer.innerHTML = '<div class="empty-preview"><i class="fa-solid fa-table fa-2x mb-2"></i><p>Belum ada kolom yang dipilih</p><small>Silakan pilih minimal satu kolom untuk melihat preview</small></div>';
            return;
        }
        let previewHTML = '<table class="preview-table"><thead><tr><th>No</th>';
        selectedColumnsOrder.forEach(columnKey => {
            const columnLabel = availableColumnsData[columnKey] || columnKey;
            previewHTML += `<th>${columnLabel}</th>`;
        });
        previewHTML += '<th>Aksi</th></tr></thead><tbody>';
        for (let i = 0; i < 5; i++) {
            previewHTML += '<tr><td>' + (i + 1) + '</td>';
            selectedColumnsOrder.forEach(columnKey => {
                const columnLabel = availableColumnsData[columnKey] || columnKey;
                previewHTML += `<td>Contoh ${columnLabel} ${i + 1}</td>`;
            });
            previewHTML += '<td>Edit, Kirim</td></tr>';
        }
        previewHTML += '</tbody></table>';
        previewContainer.innerHTML = previewHTML;
    }
    function updateSelectedCount() {
        const countElement = document.getElementById('selectedColumnCount');
        countElement.textContent = selectedColumnsOrder.length;
        const saveButton = document.getElementById('saveCustomizationBtn');
        saveButton.disabled = selectedColumnsOrder.length === 0;
    }
    function saveColumnCustomization() {
        if (selectedColumnsOrder.length === 0) {
            alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
            return;
        }
        const filterForm = document.getElementById('filterForm');
        // Bersihkan input kolom lama.
        filterForm.querySelectorAll('input[name="columns[]"], input[name="enable_customization"]').forEach(input => input.remove());
        selectedColumnsOrder.forEach(columnKey => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'columns[]';
            hiddenInput.value = columnKey;
            filterForm.appendChild(hiddenInput);
        });
        const enableInput = document.createElement('input');
        enableInput.type = 'hidden';
        enableInput.name = 'enable_customization';
        enableInput.value = '1';
        filterForm.appendChild(enableInput);
        appendActiveFilterInputs(filterForm); // Fix: bawa filter toolbar aktif agar tak hilang saat reload GET.
        closeColumnCustomizationModal();
        filterForm.submit(); // GET → documents.index (tanpa ?classic) → reload view Tabulator dgn kolom baru.
    }

    // Salin nilai kontrol toolbar (search/year/status_filter) TERKINI ke #filterForm
    // sebagai hidden input SEBELUM submit, agar reload GET documents.index membawa
    // filter yang sedang aktif alih-alih kembali ke tabel tak terfilter.
    function appendActiveFilterInputs(filterForm) {
        filterForm.querySelectorAll('input[name="search"], input[name="year"], input[name="status_filter"]').forEach(input => input.remove());
        const searchEl = document.querySelector('.tabulator-toolbar input[name="search"]');
        const yearEl = document.querySelector('.tabulator-toolbar select[name="year"]');
        const statusEl = document.querySelector('.tabulator-toolbar select[name="status_filter"]');
        [['search', searchEl], ['year', yearEl], ['status_filter', statusEl]].forEach(([name, el]) => {
            if (el && el.value) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = name;
                hiddenInput.value = el.value;
                filterForm.appendChild(hiddenInput);
            }
        });
    }
    function initializeModalState() {
        document.querySelectorAll('.column-item').forEach(item => {
            const columnKey = item.dataset.column;
            const checkbox = item.querySelector('.column-item-checkbox');
            if (selectedColumnsOrder.includes(columnKey)) {
                checkbox.checked = true;
                item.classList.add('selected');
                item.setAttribute('draggable', 'true');
            } else {
                checkbox.checked = false;
                item.classList.remove('selected');
                item.setAttribute('draggable', 'false');
            }
        });
        initializeDragAndDrop();
        updateColumnOrderBadges();
        updatePreviewTable();
        updateSelectedCount();
    }
    function updateDraggableState() {
        document.querySelectorAll('.column-item').forEach(item => {
            const columnKey = item.dataset.column;
            item.setAttribute('draggable', selectedColumnsOrder.includes(columnKey) ? 'true' : 'false');
        });
    }

    let draggedElement = null;
    let draggedIndex = -1;
    function initializeDragAndDrop() {
        const columnList = document.getElementById('columnSelectionList');
        if (!columnList) return;
        const newList = columnList.cloneNode(true);
        columnList.parentNode.replaceChild(newList, columnList);
        newList.querySelectorAll('.column-item.selected').forEach(item => {
            item.addEventListener('dragstart', handleDragStart);
            item.addEventListener('dragend', handleDragEnd);
            item.addEventListener('dragover', handleDragOver);
            item.addEventListener('dragenter', handleDragEnter);
            item.addEventListener('dragleave', handleDragLeave);
            item.addEventListener('drop', handleDrop);
        });
    }
    function handleDragStart(e) {
        draggedElement = this;
        draggedIndex = selectedColumnsOrder.indexOf(this.dataset.column);
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.dataset.column);
    }
    function handleDragEnd(e) {
        this.classList.remove('dragging');
        document.querySelectorAll('.column-item').forEach(el => { el.classList.remove('drag-over'); });
        draggedElement = null;
        draggedIndex = -1;
    }
    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (this !== draggedElement && this.classList.contains('selected')) {
            const afterElement = getDragAfterElement(this.parentNode, e.clientY);
            if (afterElement == null) { this.parentNode.appendChild(draggedElement); }
            else { this.parentNode.insertBefore(draggedElement, afterElement); }
        }
        return false;
    }
    function handleDragEnter(e) {
        e.preventDefault();
        if (this !== draggedElement && this.classList.contains('selected')) { this.classList.add('drag-over'); }
    }
    function handleDragLeave(e) { this.classList.remove('drag-over'); }
    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('drag-over');
        if (this !== draggedElement && this.classList.contains('selected')) {
            const columnList = document.getElementById('columnSelectionList');
            const selectedItems = Array.from(columnList.querySelectorAll('.column-item.selected'));
            const newOrder = selectedItems.map(item => item.dataset.column);
            selectedColumnsOrder = newOrder;
            updateColumnOrderBadges();
            updatePreviewTable();
            setTimeout(() => { initializeDragAndDrop(); }, 50);
        }
        return false;
    }
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.column-item.selected:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) { return { offset: offset, element: child }; }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // Tutup modal kustomisasi: klik luar + Escape + re-init drag saat modal dibuka.
    document.addEventListener('click', function (e) {
        const modal = document.getElementById('columnCustomizationModal');
        if (modal && modal.classList.contains('show') && e.target === modal) {
            closeColumnCustomizationModal();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('columnCustomizationModal');
            if (modal && modal.classList.contains('show')) { closeColumnCustomizationModal(); }
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('columnCustomizationModal');
        if (modal) {
            const observer = new MutationObserver(function () {
                if (modal.classList.contains('show')) {
                    setTimeout(() => { initializeDragAndDrop(); }, 100);
                }
            });
            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        }
    });
</script>
@endpush

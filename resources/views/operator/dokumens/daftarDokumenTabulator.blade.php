@extends('layouts.app')

@section('content')
{{--
  View Tabulator (pilot) untuk Daftar Dokumen Operator.
  Toolbar filter (search/year/status_filter) tersambung ke Tabulator via AJAX (Tugas 7c)
  dan diinisialisasi dari request() agar bertahan lepas reload kustomisasi kolom.
  Tabel di-mount oleh public/js/document-tabulator.js membaca window.DOCUMENT_TABULATOR_CONFIG.
  Modal Kustomisasi Kolom kini lewat partial bersama partials._columnCustomizationModal +
  public/js/column-customization.js (utang de-dup §7 LUNAS 2026-07-28). JS operator-only
  (hapus baris aktif) TETAP di sini — lihat @push('scripts') kedua di bawah.
--}}
@php
    // Peta kolom terpilih menjadi {key,label} untuk definisi kolom Tabulator.
    $selectedColumns = $selectedColumns ?? [];
    $availableColumns = $availableColumns ?? [];

    // Template URL berparameter dibangun via placeholder __ID__ lalu ditukar {id}
    // agar klien (JS) cukup mengganti {id} dengan id baris saat memanggil endpoint.
    $configArray = [
        'mountId'          => 'operatorTabulatorTable',
        'dataUrl'          => route('documents.data'),
        'inlineCreateUrl'  => route('documents.inline-create'),
        'inlineUpdateTpl'  => str_replace('__ID__', '{id}', route('documents.inline-update', ['dokumen' => '__ID__'])),
        'handlerTpl'       => str_replace('__ID__', '{id}', route('documents.handler.update', ['dokumen' => '__ID__'])),
        'destroyTpl'       => str_replace('__ID__', '{id}', route('documents.destroy', ['dokumen' => '__ID__'])),
        'csrf'             => csrf_token(),
        // Task 4 fitur export bersama (ADITIF): mengisi ini memunculkan tombol Export
        // (Excel/PDF) di toolbar Tabulator (pola Task 3 pembayaran).
        'exportUrl'        => route('documents.export'),
        'columns'          => collect($renderColumns)->map(fn ($k) => ['key' => $k, 'label' => $availableColumns[$k] ?? $k])->values(),
        'frozen'           => $frozenColumns ?? ['left' => [], 'right' => []],
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

        {{-- Fitur Detail Dokumen dihapus atas permintaan user (2026-07-22) — modal
             detail beserta tombol pembukanya dicabut seluruhnya. Tombol Hapus
             dulunya berada DI DALAM modal itu (footer-nya); sekarang dipindah ke
             toolbar agar kemampuan menghapus dokumen tetap ada, bekerja pada baris
             yang sel-nya sedang aktif — pola yang sama dengan tombol Detail lama. --}}
        <button type="button" class="btn btn-outline-danger" id="btnHapusBarisAktif"
                title="Hapus dokumen pada baris yang sedang aktif">
            <i class="fa-solid fa-trash me-1"></i> Hapus
        </button>

        <button type="button" class="btn btn-outline-secondary" onclick="openColumnCustomizationModal()">
            <i class="fa-solid fa-table-columns me-1"></i> Kustomisasi Kolom
        </button>

        <button type="button" class="btn btn-primary" id="btnTambahBarisTabulator" disabled>
            <i class="fa-solid fa-plus me-1"></i> Tambah Baris
        </button>

    </div>

    {{-- id="documentTableContainer" WAJIB: partial global document-workbench-ui bergantung padanya --}}
    <div class="table-section table-dokumen" id="documentTableContainer">
        <div id="operatorTabulatorTable" class="doc-tabulator"></div>
    </div>
</div>

{{--
  ============================================================================
  Tugas 7a/7d/7e — Modal & form disalin dari daftarDokumen.blade.php agar view
  lama tetap utuh (duplikasi sementara; view lama dihapus di Tugas 8).
  ============================================================================
--}}

{{-- Form GET tersembunyi untuk kustomisasi kolom (Tugas 7d) — kini tak lagi disubmit.
     saveColumnCustomization() membangun URL (columns[]/frozen_config/frozen_left[]/
     frozen_right[]) lalu mengarahkan browser ke situ → reload view Tabulator (TANPA
     ?classic) dengan set kolom & kolom beku baru. --}}
<form action="{{ route('documents.index') }}" method="GET" id="filterForm" class="d-none"></form>

{{-- Form DELETE tersembunyi (Tugas 7a) — hapus tetap full-page submit + redirect. --}}
<form id="deleteDocumentForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

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

{{-- ==== Floating Label Notifikasi Sukses Hapus (Pojok Kanan Atas, 3 Detik) ==== --}}
<div id="deleteSuccessFloatingToast" class="floating-toast-container" style="display: none;">
    <div class="floating-toast-card">
        <div class="floating-toast-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="floating-toast-text">
            Dokumen Berhasil Dihapus
        </div>
        <button type="button" class="floating-toast-close" onclick="hideDeleteToast()" aria-label="Tutup">
            <i class="fa-solid fa-xmark"></i>
        </button>
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

@include('partials._columnCustomizationModal')
@endsection

@push('styles')
    {{-- Font tabel ala CASH_BANK. Sengaja dimuat di sini, BUKAN di layouts/app.blade.php,
         agar tipografi role lain tidak ikut berubah (CLAUDE.md §6). --}}
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/tabulator-agenda.css') }}">
    <style>
    /* Toolbar filter Tabulator */
    .tabulator-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; }
    .tabulator-toolbar-search { max-width: 320px; }

    /* Floating Label Notifikasi Sukses Hapus (Pojok Kanan Atas, 3 Detik) */
    .floating-toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999999 !important;
        pointer-events: none;
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
        transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .floating-toast-container.show {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    .floating-toast-card {
        pointer-events: auto;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #064e3b;
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25), 0 4px 12px rgba(6, 78, 59, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
    }
    .floating-toast-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        color: #34d399;
        font-size: 15px;
        flex-shrink: 0;
    }
    .floating-toast-text {
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 0.2px;
        white-space: nowrap;
        color: #ffffff;
    }
    .floating-toast-close {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        margin-left: 4px;
        border-radius: 50%;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .floating-toast-close:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.15);
    }
    </style>
@endpush

@push('scripts')
    <script>window.DOCUMENT_TABULATOR_CONFIG = @json($configArray);</script>
    <script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::versioned('js/document-tabulator.js') }}"></script>
@endpush

@push('scripts')
{{-- Hapus Dokumen — JS operator-only, disalin dari daftarDokumen.blade.php (Tugas 7a).
     Modal Detail beserta fungsi JS yang dulu memuatnya dihapus atas permintaan user
     (2026-07-22) — lihat komentar tombol Hapus toolbar di atas. Fungsi global di bawah
     dipanggil oleh document-tabulator.js (tombol toolbar Hapus). bootstrap.* tersedia
     (bundle dimuat layout sebelum @stack). Kustomisasi Kolom PINDAH ke partial +
     JS bersama — lihat @push('scripts') berikutnya. --}}
<script>
    // ==== Hapus Dokumen (daftarDokumen.blade.php:4512-4582) — tetap full-page submit + redirect ====
    let documentIdToDelete = null;

    // Menerima nilai tampilan sebagai PARAMETER (bukan membaca dari DOM modal Detail
    // yang sudah dihapus) — pemanggilnya adalah tombol toolbar #btnHapusBarisAktif di
    // document-tabulator.js, yang mengambil nilai ini dari data baris Tabulator aktif.
    function confirmDeleteDocument(dokumenId, nomorAgenda, nomorSpp, nilaiRupiah) {
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
        form.action = window.DOCUMENT_TABULATOR_CONFIG.destroyTpl.replace('{id}', documentIdToDelete);
        form.submit();
    }

    let deleteToastTimer = null;

    function showDeleteToast() {
        const toastEl = document.getElementById('deleteSuccessFloatingToast');
        if (!toastEl) return;
        
        clearTimeout(deleteToastTimer);
        toastEl.style.display = 'block';
        void toastEl.offsetWidth; // trigger reflow
        toastEl.classList.add('show');

        deleteToastTimer = setTimeout(() => {
            hideDeleteToast();
        }, 3000);
    }

    function hideDeleteToast() {
        const toastEl = document.getElementById('deleteSuccessFloatingToast');
        if (!toastEl) return;
        
        toastEl.classList.remove('show');
        clearTimeout(deleteToastTimer);
        deleteToastTimer = setTimeout(() => {
            toastEl.style.display = 'none';
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success') && str_contains(strtolower(session('success')), 'hapus'))
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            });
            setTimeout(() => {
                showDeleteToast();
            }, 300);
        @endif
    });
</script>
@endpush

@push('scripts')
    {{-- Modal Kustomisasi Kolom — partial partials._columnCustomizationModal + JS bersama.
         window.COLUMN_CUSTOMIZATION_CONFIG diset partial (di atas), jadi dimuat sebelum file ini. --}}
    <script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>
@endpush

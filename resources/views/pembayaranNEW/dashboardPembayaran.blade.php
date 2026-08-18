@extends('layouts.app')

@section('content')
{{--
  View Tabulator (Rollout 4) untuk Daftar Dokumen Pembayaran.
  Tampilan tools dirapikan selaras dengan halaman Verifikasi (1 baris toolbar bersih,
  kontrol berukuran standar), dengan mempertahankan seluruh filter & fitur pembayaran:
  - Search (no agenda, SPP, vendor)
  - Status Pembayaran (Semua Status, Belum Siap, Siap Dibayar, Sudah Dibayar)
  - Filter Tanggal Masuk
  - Filter Lanjutan (Bulan, Bagian, Vendor, Kriteria CF, Sub Kriteria, Item, Kebun, Jenis Pembayaran)
  - Reset Filter
  - Kustomisasi Kolom
  - Export (Excel/PDF)
--}}
@php
    $selectedColumns = $selectedColumns ?? [];
    $availableColumns = $availableColumns ?? [];

    $pembayaranTabulatorConfig = [
        'mountId'          => 'pembayaranTabulatorTable',
        'dataUrl'          => route('documents.pembayaran.data'),
        'inlineUpdateTpl'  => str_replace('__ID__', '{id}', route('documents.inline-update', ['dokumen' => '__ID__'])),
        'handlerTpl'       => str_replace('__ID__', '{id}', route('documents.handler.update', ['dokumen' => '__ID__'])),
        'csrf'             => csrf_token(),
        'exportUrl'        => route('documents.pembayaran.export'),
        'columns'          => collect($renderColumns)->map(fn ($k) => [
            'key' => $k,
            'label' => $availableColumns[$k] ?? $k,
            ...($k === 'status_pembayaran' ? ['formatter' => 'paymentPill'] : []),
        ])->values(),
        'availableColumns' => $availableColumns,
        'selected'         => array_values($selectedColumns),
        'extraColumns'     => [],
        'showHandler'      => true,
        'frozen'           => ['left' => array_values($frozenLeft ?? []), 'right' => array_values($frozenRight ?? [])],
        'ie'               => [
            'kategori' => $ieKategoriList ?? [],
            'sub'      => $ieSubKriteriaList ?? [],
            'item'     => $ieItemSubKriteriaList ?? [],
            'jenis'    => $ieJenisPembayaranList ?? [],
            'bagian'   => \App\Models\Bagian::active()->ordered()->get(['kode', 'nama']),
        ],
        'bulanList'        => ['Januari', 'Februari', 'Maret', 'April', 'May', 'Juni', 'July', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    ];

    $activeAdvancedFilterCount = 0;
    if (request('month') || request('filter_bulan')) $activeAdvancedFilterCount++;
    if (request('filter_vendor')) $activeAdvancedFilterCount++;
    if (request('filter_kategori')) $activeAdvancedFilterCount++;
    if (request('filter_jenis_dokumen')) $activeAdvancedFilterCount++;
    if (request('filter_jenis_sub_pekerjaan')) $activeAdvancedFilterCount++;
    if (request('filter_kebun')) $activeAdvancedFilterCount++;
    if (request('filter_jenis_pembayaran')) $activeAdvancedFilterCount++;
    if (request('filter_bagian')) $activeAdvancedFilterCount++;
@endphp

<div class="tabulator-page">
    <div class="tabulator-toolbar">
        {{-- 1. Search Input --}}
        <input type="text" name="search" class="form-control tabulator-toolbar-search"
               placeholder="Cari no agenda, SPP, vendor..." autocomplete="off"
               value="{{ request('search', $search ?? '') }}" style="max-width: 260px;">

        {{-- 2. Status Pembayaran --}}
        <select name="status_pembayaran" class="form-select" style="max-width: 140px;">
            <option value="">Semua Status</option>
            <option value="belum_siap_dibayar" {{ ($selectedStatus ?? request('status_pembayaran')) == 'belum_siap_dibayar' ? 'selected' : '' }}>Belum Siap</option>
            <option value="siap_dibayar" {{ ($selectedStatus ?? request('status_pembayaran')) == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
            <option value="sudah_dibayar" {{ ($selectedStatus ?? request('status_pembayaran')) == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
        </select>

        {{-- 3. Filter Tanggal Masuk --}}
        <input type="date" name="date" class="form-control" style="max-width: 155px;"
               value="{{ $selectedDate ?? request('date', '') }}"
               title="Filter tanggal masuk" placeholder="Pilih tanggal">

        {{-- 4. Tombol Filter Lanjutan --}}
        <button type="button" class="btn btn-outline-secondary" onclick="openAdvancedFilterModal()" id="advancedFilterToggle">
            <i class="fa-solid fa-sliders me-1"></i> Filter Lanjutan
            <span class="badge bg-danger rounded-pill ms-1" id="advancedFilterBadge" style="{{ $activeAdvancedFilterCount > 0 ? '' : 'display: none;' }}">{{ $activeAdvancedFilterCount }}</span>
        </button>

        {{-- 5. Tombol Kustomisasi Kolom --}}
        <button type="button" class="btn btn-outline-secondary" onclick="openColumnCustomizationModal()">
            <i class="fa-solid fa-table-columns me-1"></i> Kustomisasi Kolom
        </button>

        {{-- 6. Tombol Reset Filter --}}
        <button type="button" class="btn btn-outline-secondary" onclick="resetAllFilters()" title="Reset Semua Filter">
            <i class="fa-solid fa-redo me-1"></i> Reset
        </button>

        {{-- Modal Filter Lanjutan (di dalam toolbar agar otomatis terdeteksi oleh engine AJAX Tabulator) --}}
        <div class="afm-backdrop" id="advancedFilterModal" onclick="handleBackdropClick(event)">
            <div class="afm-dialog" role="dialog" aria-labelledby="afmTitle" aria-modal="true">
                <div class="afm-header">
                    <h5 id="afmTitle" class="m-0 font-weight-bold"><i class="fa-solid fa-sliders text-primary me-2"></i>Filter Lanjutan</h5>
                    <button type="button" class="btn-close" onclick="closeAdvancedFilterModal()" aria-label="Tutup"></button>
                </div>
                <div class="afm-body">
                    <div class="afm-grid">
                        <!-- Bulan Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-calendar-alt me-1"></i> Bulan</label>
                            <select id="filterBulan" name="month" class="form-select form-select-sm">
                                <option value="">Semua Bulan</option>
                                @php
                                    $bulanOptions = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                    $selectedBulan = request('month', request('filter_bulan', $selectedMonth ?? ''));
                                @endphp
                                @foreach($bulanOptions as $num => $namaBulan)
                                    <option value="{{ $num }}" {{ (string)$selectedBulan === (string)$num || strtolower((string)$selectedBulan) === strtolower($namaBulan) ? 'selected' : '' }}>
                                        {{ $namaBulan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bagian Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-building me-1"></i> Bagian</label>
                            <select id="filterBagian" name="filter_bagian" class="form-select form-select-sm">
                                <option value="">Semua Bagian</option>
                                @foreach($availableBagians ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ request('filter_bagian') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Vendor Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-store me-1"></i> Vendor</label>
                            <select id="filterVendor" name="filter_vendor" class="form-select form-select-sm">
                                <option value="">Semua Vendor</option>
                                @foreach($availableDibayarKepada ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ request('filter_vendor') == $key ? 'selected' : '' }}>{{ Str::limit($value, 30) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Kriteria CF Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-tags me-1"></i> Kriteria CF</label>
                            <select id="filterKategori" name="filter_kategori" class="form-select form-select-sm">
                                <option value="">Semua Kriteria</option>
                                @foreach($availableKategori ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ request('filter_kategori') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sub Kriteria Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-tag me-1"></i> Sub Kriteria</label>
                            <select id="filterJenisDokumen" name="filter_jenis_dokumen" class="form-select form-select-sm">
                                <option value="">Semua Sub Kriteria</option>
                                @foreach($availableJenisDokumen ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ request('filter_jenis_dokumen') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Item Sub Kriteria Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-th-list me-1"></i> Item Sub Kriteria</label>
                            <select id="filterJenisSubPekerjaan" name="filter_jenis_sub_pekerjaan" class="form-select form-select-sm">
                                <option value="">Semua Item</option>
                                @foreach($availableJenisSubPekerjaan ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ request('filter_jenis_sub_pekerjaan') == $key ? 'selected' : '' }}>{{ Str::limit($value, 30) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Kebun Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-seedling me-1"></i> Kebun</label>
                            <select id="filterKebun" name="filter_kebun" class="form-select form-select-sm">
                                <option value="">Semua Kebun</option>
                                @foreach($availableKebuns ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ request('filter_kebun') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jenis Pembayaran Filter -->
                        <div class="afm-field">
                            <label class="form-label small fw-bold text-secondary"><i class="fa-solid fa-money-bill-wave me-1"></i> Jenis Pembayaran</label>
                            <select id="filterJenisPembayaran" name="filter_jenis_pembayaran" class="form-select form-select-sm">
                                <option value="">Semua Jenis</option>
                                @foreach($availableJenisPembayaran ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ request('filter_jenis_pembayaran') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="afm-footer">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetAdvancedFilters()">
                        <i class="fa-solid fa-times me-1"></i> Reset Filter Lanjutan
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="closeAdvancedFilterModal()">
                        <i class="fa-solid fa-check me-1"></i> Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- id="documentTableContainer" WAJIB: partial global document-workbench-ui bergantung padanya --}}
    <div class="table-section table-dokumen" id="documentTableContainer">
        <div id="pembayaranTabulatorTable" class="doc-tabulator"></div>
    </div>
</div>

{{-- Form GET tersembunyi (peninggalan mekanisme lama, tidak lagi disubmit) --}}
<form action="{{ route('documents.pembayaran.index') }}" method="GET" id="filterForm" class="d-none"></form>

@include('partials._columnCustomizationModal')
@endsection

@push('styles')
    {{-- Font tabel ala CASH_BANK. Sengaja dimuat di sini, BUKAN di layouts/app.blade.php,
         agar tipografi role lain tidak ikut berubah (CLAUDE.md §6). --}}
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/tabulator-agenda.css') }}">
    <style>
    /* Toolbar filter Tabulator — sejajar 1 baris rapi seperti verifikasi */
    .tabulator-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 16px;
    }
    .tabulator-toolbar-search {
        max-width: 260px;
    }

    /* Status Pills Pembayaran (Belum Siap: Oranye, Siap Dibayar: Ungu, Sudah Dibayar: Hijau) */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: inset 0 0 0 1px currentColor;
    }
    .status-pill i {
        font-size: 0.55rem;
    }
    .status-pill--pending {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #f59e0b !important;
    }
    .status-pill--ready {
        background: rgba(139, 92, 246, 0.12) !important;
        color: #8b5cf6 !important;
    }
    .status-pill--paid {
        background: rgba(16, 185, 129, 0.12) !important;
        color: #10b981 !important;
    }

    /* Modal Filter Lanjutan */
    .afm-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .afm-backdrop.show {
        display: flex !important;
    }
    .afm-dialog {
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 680px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        animation: afmFadeIn 0.2s ease-out;
    }
    @keyframes afmFadeIn {
        from { opacity: 0; transform: translateY(-10px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .afm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .afm-body {
        padding: 20px;
        overflow-y: auto;
    }
    .afm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }
    .afm-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    </style>
@endpush

@push('scripts')
    <script>
      function setViewMode() {}
      function refreshPembayaranTable() {
        if (window.DOCUMENT_TABULATOR_TABLE) {
          window.DOCUMENT_TABULATOR_TABLE.replaceData();
        }
      }
      function changePerPage() {}
      function toggleVendorGroup() {}

      function openAdvancedFilterModal() {
        var modal = document.getElementById('advancedFilterModal');
        if (modal) modal.classList.add('show');
      }
      function closeAdvancedFilterModal() {
        var modal = document.getElementById('advancedFilterModal');
        if (modal) modal.classList.remove('show');
        updateAdvancedFilterBadge();
      }
      function handleBackdropClick(e) {
        if (e.target && e.target.id === 'advancedFilterModal') {
          closeAdvancedFilterModal();
        }
      }
      function resetAdvancedFilters() {
        var modal = document.getElementById('advancedFilterModal');
        if (!modal) return;
        modal.querySelectorAll('select, input').forEach(function(el) {
          el.value = '';
        });
        updateAdvancedFilterBadge();
        if (window.DOCUMENT_TABULATOR_TABLE) {
          window.DOCUMENT_TABULATOR_TABLE.replaceData();
        }
      }
      function resetAllFilters() {
        var toolbar = document.querySelector('.tabulator-toolbar');
        if (toolbar) {
          toolbar.querySelectorAll('input[name], select[name]').forEach(function(el) {
            el.value = '';
          });
        }
        updateAdvancedFilterBadge();
        if (window.DOCUMENT_TABULATOR_TABLE) {
          window.DOCUMENT_TABULATOR_TABLE.replaceData();
        }
      }
      function updateAdvancedFilterBadge() {
        var modal = document.getElementById('advancedFilterModal');
        if (!modal) return;
        var count = 0;
        modal.querySelectorAll('select[name], input[name]').forEach(function(el) {
          if (el.value && el.value.trim() !== '') count++;
        });
        var badge = document.getElementById('advancedFilterBadge');
        if (badge) {
          badge.textContent = count;
          badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('advancedFilterModal');
        if (modal) {
          modal.querySelectorAll('select, input').forEach(function(el) {
            el.addEventListener('change', updateAdvancedFilterBadge);
          });
        }
      });
    </script>
    <script>window.DOCUMENT_TABULATOR_CONFIG = @json($pembayaranTabulatorConfig);</script>
    <script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::versioned('js/document-tabulator.js') }}"></script>
@endpush

@push('scripts')
    {{-- Modal Kustomisasi Kolom — partial partials._columnCustomizationModal + JS bersama.
         window.COLUMN_CUSTOMIZATION_CONFIG diset partial (di atas), jadi dimuat sebelum file ini. --}}
    <script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>
@endpush

@extends('layouts.app')

@section('content')
{{--
  View Tabulator (Rollout 2) untuk Daftar Dokumen Perpajakan. Meniru view akutansi
  (Rollout 1) tetapi: endpoint documents.perpajakan.data, kolom tetap Deadline + Status
  (via extraColumns), TANPA Tambah/Hapus (perpajakan tak punya create/destroy). Tabel
  di-mount public/js/document-tabulator.js membaca window.DOCUMENT_TABULATOR_CONFIG.
  Modal Kustomisasi Kolom kini lewat partial bersama partials._columnCustomizationModal +
  public/js/column-customization.js (utang de-dup §7 LUNAS 2026-07-28).
--}}
@php
    $selectedColumns = $selectedColumns ?? [];
    $availableColumns = $availableColumns ?? [];

    $configArray = [
        'mountId'          => 'perpajakanTabulatorTable',
        'dataUrl'          => route('documents.perpajakan.data'),
        'inlineUpdateTpl'  => str_replace('__ID__', '{id}', route('documents.inline-update', ['dokumen' => '__ID__'])),
        'handlerTpl'       => str_replace('__ID__', '{id}', route('documents.handler.update', ['dokumen' => '__ID__'])),
        'csrf'             => csrf_token(),
        // Task 4 fitur export bersama (ADITIF): mengisi ini memunculkan tombol Export
        // (Excel/PDF) di toolbar Tabulator (pola Task 3 pembayaran).
        'exportUrl'        => route('documents.perpajakan.export'),
        'columns'          => collect($renderColumns)->map(fn ($k) => ['key' => $k, 'label' => $availableColumns[$k] ?? $k])->values(),
        'frozen'           => $frozenColumns ?? ['left' => [], 'right' => []],
        'availableColumns' => $availableColumns,
        'selected'         => array_values($selectedColumns),
        // Kolom tetap khas perpajakan: Deadline + Status (dirender formatter server-object).
        'extraColumns'     => [
            ['field' => 'deadline',     'title' => 'Deadline', 'formatter' => 'deadline', 'width' => 110],
            ['field' => 'status_badge', 'title' => 'Status',   'formatter' => 'akutansiStatus'],
        ],
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
    <div class="tabulator-toolbar">
        <input type="text" name="search" class="form-control tabulator-toolbar-search"
               placeholder="Cari dokumen..." autocomplete="off" value="{{ request('search') }}">

        <select name="status" class="form-select" style="max-width: 240px;">
            <option value="">Semua Status</option>
            <option value="sedang_proses" {{ request('status') == 'sedang_proses' ? 'selected' : '' }}>Sedang Proses</option>
            <option value="terkirim_akutansi" {{ request('status') == 'terkirim_akutansi' ? 'selected' : '' }}>Terkirim ke Akutansi</option>
            <option value="terkirim_pembayaran" {{ request('status') == 'terkirim_pembayaran' ? 'selected' : '' }}>Terkirim ke Pembayaran</option>
            <option value="menunggu_approve" {{ request('status') == 'menunggu_approve' ? 'selected' : '' }}>Menunggu Approve</option>
            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Dokumen Ditolak</option>
        </select>

        <select name="filter_dari" class="form-select" style="max-width: 200px;">
            <option value="">Semua Bagian</option>
            @foreach(($filterDariOptions ?? []) as $bagianVal => $bagianLabel)
                <option value="{{ $bagianVal }}" {{ request('filter_dari') == $bagianVal ? 'selected' : '' }}>{{ $bagianLabel }}</option>
            @endforeach
        </select>

        <button type="button" class="btn btn-outline-secondary" onclick="openColumnCustomizationModal()">
            <i class="fa-solid fa-table-columns me-1"></i> Kustomisasi Kolom
        </button>
    </div>

    {{-- id="documentTableContainer" WAJIB: partial global document-workbench-ui bergantung padanya --}}
    <div class="table-section table-dokumen" id="documentTableContainer">
        <div id="perpajakanTabulatorTable" class="doc-tabulator"></div>
    </div>
</div>

{{-- Form GET tersembunyi (peninggalan mekanisme lama, tidak lagi disubmit) — kustomisasi
     kolom kini disimpan via pembangunan URL oleh saveColumnCustomization() lalu diarahkan
     browser ke situ, bukan lewat form ini. --}}
<form action="{{ route('documents.perpajakan.index') }}" method="GET" id="filterForm" class="d-none"></form>

@include('partials._columnCustomizationModal')
@endsection

@push('styles')
    {{-- Font tabel ala CASH_BANK. Sengaja dimuat di sini, BUKAN di layouts/app.blade.php,
         agar tipografi role lain tidak ikut berubah (CLAUDE.md §6). --}}
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/tabulator-agenda.css') }}">
    {{-- Badge Deadline & Status (pil ringkas + popover rincian) — satu berkas
         bersama untuk akutansi/perpajakan/verifikasi. Menggantikan
         perpajakan-deadline-badge.css yang dihapus 2026-08-06. --}}
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/document-badges.css') }}">
    <style>
    /* Toolbar filter Tabulator */
    .tabulator-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; }
    .tabulator-toolbar-search { max-width: 320px; }
    </style>
@endpush

@push('scripts')
    <script>window.DOCUMENT_TABULATOR_CONFIG = @json($configArray);</script>
    <script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::versioned('js/document-tabulator.js') }}"></script>
@endpush

@push('scripts')
    {{-- Modal Kustomisasi Kolom — partial partials._columnCustomizationModal + JS bersama.
         window.COLUMN_CUSTOMIZATION_CONFIG diset partial (di atas), jadi dimuat sebelum file ini. --}}
    <script src="{{ \App\Support\Asset::versioned('js/column-customization.js') }}"></script>
@endpush

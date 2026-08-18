@extends('layouts.app')

@section('content')
{{--
  View Tabulator (Rollout 4) untuk Daftar Dokumen Pembayaran.
  Meniru kerapian view verifikasi / akutansi / perpajakan:
  endpoint documents.pembayaran.data, showHandler: true, modal Kustomisasi Kolom
  lewat partial bersama partials._columnCustomizationModal + public/js/column-customization.js,
  dan export terpadu via public/js/document-tabulator.js.
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
@endphp

<div class="tabulator-page">
    <div class="tabulator-toolbar">
        <input type="text" name="search" class="form-control tabulator-toolbar-search"
               placeholder="Cari dokumen..." autocomplete="off" value="{{ request('search') }}">

        <select name="status_pembayaran" class="form-select" style="max-width: 140px;">
            <option value="">Semua Status</option>
            <option value="belum_siap_dibayar" {{ ($selectedStatus ?? request('status_pembayaran')) == 'belum_siap_dibayar' ? 'selected' : '' }}>Belum Siap</option>
            <option value="siap_dibayar" {{ ($selectedStatus ?? request('status_pembayaran')) == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
            <option value="sudah_dibayar" {{ ($selectedStatus ?? request('status_pembayaran')) == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
        </select>

        <select name="filter_bagian" class="form-select" style="max-width: 140px;">
            <option value="">Semua Bagian</option>
            @foreach(($availableBagians ?? []) as $bagianVal => $bagianLabel)
                <option value="{{ $bagianVal }}" {{ request('filter_bagian') == $bagianVal ? 'selected' : '' }}>{{ $bagianLabel }}</option>
            @endforeach
        </select>

        <button type="button" class="btn btn-outline-secondary" onclick="openColumnCustomizationModal()">
            <i class="fa-solid fa-table-columns me-1"></i> Kustomisasi Kolom
        </button>
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
    /* Toolbar filter Tabulator */
    .tabulator-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; }
    .tabulator-toolbar-search { max-width: 320px; }
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

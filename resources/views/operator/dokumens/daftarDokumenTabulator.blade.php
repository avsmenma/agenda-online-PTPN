@extends('layouts.app')

@section('content')
{{--
  View Tabulator (pilot) untuk Daftar Dokumen Operator.
  Skeleton Tugas 4: toolbar filter masih INERT (disambung ke Tabulator di Tugas 7),
  tabel di-mount oleh public/js/operator-tabulator.js membaca window.OPERATOR_TABULATOR_CONFIG.
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
        ],
    ];
@endphp

<div class="tabulator-page">
    {{-- Toolbar filter (INERT pada skeleton — fungsi disambung di Tugas 7) --}}
    <div class="tabulator-toolbar">
        <input type="text" name="search" class="form-control tabulator-toolbar-search"
               placeholder="Cari dokumen..." autocomplete="off" disabled>

        <select name="year" class="form-select" style="max-width: 140px;" disabled>
            <option value="">Semua Tahun</option>
            @for ($y = (int) date('Y'); $y >= (int) date('Y') - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>

        <select name="status_filter" class="form-select" style="max-width: 200px;" disabled>
            <option value="">Semua Status</option>
            <option value="belum_dikirim">Belum Dikirim</option>
            <option value="menunggu_approval">Menunggu Approval</option>
            <option value="terkirim">Terkirim</option>
        </select>

        <button type="button" class="btn btn-primary" id="btnTambahBarisTabulator" disabled>
            <i class="fa-solid fa-plus me-1"></i> Tambah Baris
        </button>
    </div>

    {{-- id="documentTableContainer" WAJIB: partial global document-workbench-ui bergantung padanya --}}
    <div class="table-section table-dokumen" id="documentTableContainer">
        <div id="operatorTabulatorTable"></div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/tabulator.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tabulator-agenda.css') }}">
@endpush

@push('scripts')
    <script>window.OPERATOR_TABULATOR_CONFIG = @json($configArray);</script>
    <script src="{{ asset('vendor/tabulator/tabulator.min.js') }}"></script>
    <script src="{{ asset('js/operator-tabulator.js') }}"></script>
@endpush

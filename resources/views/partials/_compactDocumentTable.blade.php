{{--
  _compactDocumentTable.blade.php
  Tampilan tabel data lebih ringkas agar lebih banyak baris terlihat dalam
  1 layar pada zoom 100%. Dipakai oleh role operator, verifikasi, perpajakan,
  dan akutansi. JANGAN dipakai di pembayaran/bagian (struktur kolom berbeda).

  Usage: @include('partials._compactDocumentTable')
--}}
<style>
  /* ===== Compact data table ===== */
  #documentTableContainer .data-table td,
  #documentTableContainer .data-table tbody td {
    padding: 4px 8px !important;
    line-height: 1.3 !important;
    font-size: 12px !important;
  }

  #documentTableContainer .data-table thead th {
    padding: 7px 8px !important;
    font-size: 11px !important;
  }

  /* Kolom teks panjang: rapatkan padding & jarak baris */
  #documentTableContainer .data-table .col-uraian,
  #documentTableContainer .data-table td.col-uraian,
  #documentTableContainer .data-table .col-dibayar_kepada,
  #documentTableContainer .data-table td.col-dibayar_kepada {
    padding: 4px 8px !important;
    line-height: 1.32 !important;
  }

  #documentTableContainer .data-table .col-uraian span {
    line-height: 1.32 !important;
  }

  /* Dropdown pengurus lebih ringkas */
  #documentTableContainer .data-table .document-handler-select {
    padding: 4px 8px !important;
    min-width: 148px !important;
    max-width: 190px !important;
    font-size: 11px !important;
  }

  /* Kartu & indikator deadline lebih kecil */
  #documentTableContainer .data-table .deadline-card {
    padding: 5px 8px !important;
    max-width: 130px !important;
  }

  #documentTableContainer .data-table .deadline-time {
    margin-bottom: 2px !important;
    font-size: 10px !important;
  }

  #documentTableContainer .data-table .late-info {
    margin-top: 4px !important;
    padding: 3px 8px !important;
  }

  /* Badge status lebih ringkas */
  #documentTableContainer .data-table .badge-status,
  #documentTableContainer .data-table .badge {
    padding: 3px 8px !important;
    font-size: 10px !important;
  }
</style>

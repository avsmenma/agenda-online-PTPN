<style>
  body:has(#documentTableContainer) .content h2,
  body:has(#documentTableContainer) .main-content h2,
  body:has(#documentTableContainer) main h2 {
    font-size: clamp(1.45rem, 1.55vw, 1.85rem) !important;
    line-height: 1.12 !important;
    margin-bottom: 0.7rem !important;
    letter-spacing: 0 !important;
  }


  .search-box {
    padding: 0.62rem 0.8rem !important;
    margin-bottom: 0.7rem !important;
    border-radius: 12px !important;
    box-shadow: 0 5px 18px rgba(15, 23, 42, 0.06) !important;
  }

  .search-box form {
    gap: 0.5rem !important;
  }

  .search-box .input-group,
  .search-box .filter-section {
    min-height: 36px !important;
  }

  .search-box .input-group-text,
  .search-box .form-control,
  .filter-section select,
  .filter-section input,
  .form-select,
  .btn-year-filter,
  .btn-filter,
  .btn-refresh,
  .btn-customize-columns-inline,
  .btn-fullscreen,
  .btn-fullscreen-toggle {
    min-height: 36px !important;
    height: 36px !important;
    padding: 0.38rem 0.68rem !important;
    font-size: 0.78rem !important;
    line-height: 1.2 !important;
    border-radius: 8px !important;
  }

  .btn-filter,
  .btn-refresh,
  .btn-customize-columns-inline,
  .btn-fullscreen,
  .btn-fullscreen-toggle,
  .btn-year-filter {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.35rem !important;
    white-space: nowrap !important;
  }

  .btn-filter i,
  .btn-refresh i,
  .btn-customize-columns-inline i,
  .btn-fullscreen i,
  .btn-fullscreen-toggle i,
  .btn-year-filter i {
    font-size: 0.76rem !important;
  }

  #documentTableContainer.table-dokumen {
    margin-top: 0.75rem !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  /* Toolbar tabel dirapatkan, BUKAN disembunyikan. `display: none` di sini
     pernah menghilangkan seluruh kontrolnya (Refresh, Kustomisasi Kolom Tabel,
     toggle Normal/Group Vendor, Fullscreen) di halaman pembayaran. Penyembunyian
     yang disengaja hanya berlaku saat mode fullscreen — lihat aturan
     `body.document-table-only-fullscreen` di bawah. */
  #documentTableContainer .dtable-toolbar {
    padding: 0.45rem 0.7rem 0.6rem !important;
    margin-bottom: 0.55rem !important;
    gap: 0.65rem !important;
  }

  #documentTableContainer .dtable-toolbar-left {
    gap: 0.5rem !important;
  }

  #documentTableContainer .dtable-toolbar-icon {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    border-radius: 6px !important;
    background: #0d3b6e !important;
    color: #ffffff !important;
    font-size: 0.82rem !important;
  }

  #documentTableContainer .dtable-toolbar-title {
    font-size: 1rem !important;
    line-height: 1.15 !important;
    margin-bottom: 0.1rem !important;
    color: #0d3b6e !important;
    font-weight: 700 !important;
  }

  #documentTableContainer .dtable-toolbar-subtitle {
    font-size: 0.72rem !important;
    line-height: 1.2 !important;
    color: #64748b !important;
  }

  #documentTableContainer .virtual-table-badge {
    display: none !important;
  }

  #documentTableContainer .table-container,
  #documentTableContainer .table-responsive {
    margin-top: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    max-height: 60vh !important;
    overflow: auto !important;
  }

  #documentTableContainer table,
  #documentTableContainer .data-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: 1px solid #dee2e6 !important;
    margin-bottom: 0 !important;
    font-size: 12px !important;
    width: max-content !important;
    min-width: 100% !important;
    table-layout: auto !important;
  }

  #documentTableContainer .table-container,
  #documentTableContainer .table-responsive,
  #documentTableContainer .virtual-table-viewport {
    isolation: isolate !important;
    position: relative !important;
  }

  #documentTableContainer table thead,
  #documentTableContainer .data-table thead {
    display: table-header-group !important;
    position: static !important;
    top: auto !important;
    z-index: auto !important;
    background: #0d3b6e !important;
  }

  #documentTableContainer table thead tr,
  #documentTableContainer .data-table thead tr {
    display: table-row !important;
    position: static !important;
    top: auto !important;
    z-index: auto !important;
    background: #0d3b6e !important;
  }

  #documentTableContainer table thead::after,
  #documentTableContainer .data-table thead::after {
    content: none !important;
    display: none !important;
  }

  #documentTableContainer table thead th,
  #documentTableContainer table thead tr th {
    position: sticky !important;
    top: 0 !important;
    z-index: 520 !important;
    padding: 9px 8px !important;
    min-width: 132px !important;
    max-width: 320px !important;
    background: #0d3b6e !important;
    background-clip: padding-box !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    text-align: center !important;
    white-space: normal !important;
    overflow-wrap: normal !important;
    word-break: keep-all !important;
    hyphens: none !important;
    line-height: 1.25 !important;
    vertical-align: middle !important;
    border: 1px solid #1a5276 !important;
    box-shadow: 0 2px 0 #1a5276 !important;
  }

  #documentTableContainer table thead th.col-checkbox,
  #documentTableContainer table tbody td.col-checkbox {
    width: 56px !important;
    min-width: 56px !important;
    max-width: 56px !important;
  }

  #documentTableContainer table thead th.col-no,
  #documentTableContainer table tbody td.col-no {
    width: 76px !important;
    min-width: 76px !important;
    max-width: 76px !important;
  }

  #documentTableContainer table thead th.col-bulan,
  #documentTableContainer table tbody td.col-bulan,
  #documentTableContainer table thead th.col-tahun,
  #documentTableContainer table tbody td.col-tahun {
    width: 76px !important;
    min-width: 76px !important;
    max-width: 90px !important;
  }

  #documentTableContainer table thead th[class*="tanggal"],
  #documentTableContainer table tbody td[class*="tanggal"] {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 180px !important;
  }

  #documentTableContainer table thead th.col-nomor_agenda,
  #documentTableContainer table tbody td.col-nomor_agenda,
  #documentTableContainer table thead th.col-nomor_spp,
  #documentTableContainer table tbody td.col-nomor_spp,
  #documentTableContainer table thead th.col-no_spk,
  #documentTableContainer table tbody td.col-no_spk,
  #documentTableContainer table thead th.col-no_berita_acara,
  #documentTableContainer table tbody td.col-no_berita_acara,
  #documentTableContainer table thead th.col-nomor_po,
  #documentTableContainer table tbody td.col-nomor_po,
  #documentTableContainer table thead th.col-nomor_miro,
  #documentTableContainer table tbody td.col-nomor_miro,
  #documentTableContainer table thead th.col-no_faktur,
  #documentTableContainer table tbody td.col-no_faktur {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 240px !important;
  }

  #documentTableContainer table thead th.col-kategori,
  #documentTableContainer table tbody td.col-kategori,
  #documentTableContainer table thead th.col-jenis_dokumen,
  #documentTableContainer table tbody td.col-jenis_dokumen,
  #documentTableContainer table thead th.col-jenis_sub_pekerjaan,
  #documentTableContainer table tbody td.col-jenis_sub_pekerjaan,
  #documentTableContainer table thead th.col-jenis_pembayaran,
  #documentTableContainer table tbody td.col-jenis_pembayaran,
  #documentTableContainer table thead th.col-dibayar_kepada,
  #documentTableContainer table tbody td.col-dibayar_kepada,
  #documentTableContainer table thead th.col-nama_pengirim,
  #documentTableContainer table tbody td.col-nama_pengirim,
  #documentTableContainer table thead th.col-kepala_sub_bagian,
  #documentTableContainer table tbody td.col-kepala_sub_bagian,
  #documentTableContainer table thead th.col-handler,
  #documentTableContainer table tbody td.col-handler {
    width: 190px !important;
    min-width: 190px !important;
    max-width: 280px !important;
  }

  #documentTableContainer table thead th.col-status,
  #documentTableContainer table tbody td.col-status,
  #documentTableContainer table thead th.col-status_dokumen_custom,
  #documentTableContainer table tbody td.col-status_dokumen_custom {
    width: 220px !important;
    min-width: 220px !important;
    max-width: 280px !important;
  }

  #documentTableContainer table thead th.col-nilai_rupiah,
  #documentTableContainer table tbody td.col-nilai_rupiah,
  #documentTableContainer table thead th.col-dpp_pph,
  #documentTableContainer table tbody td.col-dpp_pph,
  #documentTableContainer table thead th.col-ppn_terhutang,
  #documentTableContainer table tbody td.col-ppn_terhutang {
    width: 160px !important;
    min-width: 160px !important;
    max-width: 190px !important;
  }

  #documentTableContainer table thead th.col-link_dokumen_pajak,
  #documentTableContainer table tbody td.col-link_dokumen_pajak {
    width: 220px !important;
    min-width: 220px !important;
    max-width: 320px !important;
  }

  #documentTableContainer table thead th.col-action,
  #documentTableContainer table tbody td.col-action {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 170px !important;
  }

  #documentTableContainer table thead th:last-child,
  #documentTableContainer table thead tr th:last-child {
    border-right: 0 !important;
  }

  #documentTableContainer table tbody td,
  #documentTableContainer .data-table tbody td {
    padding: 8px 10px !important;
    height: 44px !important;
    min-height: 44px !important;
    color: #111827 !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    line-height: 1.3 !important;
    vertical-align: middle !important;
    background: #ffffff !important;
    border: 1px solid #dee2e6 !important;
    box-shadow: none !important;
  }

  #documentTableContainer table tbody,
  #documentTableContainer .data-table tbody,
  #documentTableContainer table tbody tr,
  #documentTableContainer .data-table tbody tr,
  #documentTableContainer table tbody td,
  #documentTableContainer .data-table tbody td {
    position: static !important;
    z-index: 0 !important;
  }

  #documentTableContainer table tbody td.acn-active,
  #documentTableContainer .data-table tbody td.acn-active {
    position: relative !important;
    z-index: 1 !important;
  }

  #documentTableContainer table tbody td strong,
  #documentTableContainer .data-table tbody td strong,
  #documentTableContainer table tbody td b,
  #documentTableContainer .data-table tbody td b {
    font-weight: 500 !important;
  }

  body:has(#documentTableContainer) .acn-indicator,
  body:has(#documentTableContainer) #acnIndicator {
    display: none !important;
  }

  #documentTableContainer table tbody td:last-child,
  #documentTableContainer .data-table tbody td:last-child {
    border-right: 1px solid #dee2e6 !important;
    box-shadow: none !important;
  }

  #documentTableContainer table tbody tr:nth-child(even) td,
  #documentTableContainer .data-table tbody tr:nth-child(even) td {
    background-color: #f8fbff !important;
  }

  #documentTableContainer table tbody tr:hover td,
  #documentTableContainer .data-table tbody tr:hover td {
    background-color: #f0f5fb !important;
    border-color: #dee2e6 !important;
  }

  #documentTableContainer .virtual-scroll-spacer,
  #documentTableContainer table tbody tr.virtual-scroll-spacer,
  #documentTableContainer .data-table tbody tr.virtual-scroll-spacer {
    height: var(--virtual-spacer-height, 0px) !important;
    min-height: 0 !important;
    max-height: var(--virtual-spacer-height, 0px) !important;
    background: transparent !important;
  }

  #documentTableContainer .virtual-scroll-spacer td,
  #documentTableContainer table tbody tr.virtual-scroll-spacer td,
  #documentTableContainer .data-table tbody tr.virtual-scroll-spacer td {
    height: var(--virtual-spacer-height, 0px) !important;
    min-height: 0 !important;
    max-height: var(--virtual-spacer-height, 0px) !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  #documentTableContainer table tbody tr,
  #documentTableContainer .data-table tbody tr {
    transform: none !important;
  }

  #documentTableContainer .btn,
  #documentTableContainer button,
  #documentTableContainer .btn-refresh,
  #documentTableContainer .btn-filter,
  #documentTableContainer .btn-customize-columns-inline,
  #documentTableContainer .btn-fullscreen,
  #documentTableContainer .btn-fullscreen-toggle {
    border-radius: 4px !important;
    font-size: 12.5px !important;
    min-height: 32px !important;
    height: 32px !important;
    padding: 4px 10px !important;
  }

  #documentTableContainer select,
  #documentTableContainer input[type="search"],
  #documentTableContainer input[type="text"],
  #documentTableContainer .form-select,
  #documentTableContainer .form-control {
    border-radius: 4px !important;
    border-color: #ced4da !important;
    font-size: 12.5px !important;
    min-height: 32px !important;
    height: 32px !important;
  }

  #documentTableContainer .data-table th.col-uraian,
  #documentTableContainer .data-table td.col-uraian,
  #documentTableContainer .data-table th.col-uraian_spp,
  #documentTableContainer .data-table td.col-uraian_spp,
  #documentTableContainer .data-table th[class*="uraian"],
  #documentTableContainer .data-table td[class*="uraian"],
  #documentTableContainer table th[class*="uraian"],
  #documentTableContainer table td[class*="uraian"] {
    width: 620px !important;
    min-width: 620px !important;
    max-width: 760px !important;
    white-space: normal !important;
    overflow-wrap: break-word !important;
    word-break: normal !important;
    line-height: 1.35 !important;
    vertical-align: middle !important;
    padding: 0.6rem 0.75rem !important;
  }

  #documentTableContainer .data-table th.col-uraian,
  #documentTableContainer .data-table th.col-uraian_spp,
  #documentTableContainer .data-table th[class*="uraian"] {
    text-align: center !important;
  }

  #documentTableContainer .data-table td.col-uraian,
  #documentTableContainer .data-table td.col-uraian_spp,
  #documentTableContainer .data-table td[class*="uraian"],
  #documentTableContainer table td[class*="uraian"] {
    text-align: left !important;
  }

  #documentTableContainer .data-table td.col-uraian > *,
  #documentTableContainer .data-table td.col-uraian_spp > *,
  #documentTableContainer .data-table td[class*="uraian"] > *,
  #documentTableContainer table td[class*="uraian"] > * {
    width: 100% !important;
    max-width: none !important;
    white-space: normal !important;
    overflow-wrap: break-word !important;
    word-break: normal !important;
    line-height: 1.35 !important;
  }

  body.document-table-only-fullscreen {
    overflow: hidden !important;
    background: #ffffff !important;
  }

  /*
   * Native fullscreen (element.requestFullscreen) paints a default BLACK
   * ::backdrop behind the fullscreen element. Any element promoted to its own
   * stacking context inside the table (e.g. the active navigation cell with
   * position: relative + z-index and a semi-transparent background) composites
   * against that black backdrop instead of the table's white background,
   * turning the active cell black in fullscreen. Force the backdrop to white
   * so transparent/semi-transparent content composites correctly.
   */
  #documentTableContainer:fullscreen::backdrop,
  #documentTableContainer:-webkit-full-screen::backdrop {
    background: #ffffff !important;
  }

  body.document-table-only-fullscreen .sidebar,
  body.document-table-only-fullscreen .secondary-sidebar,
  body.document-table-only-fullscreen .navbar,
  body.document-table-only-fullscreen .topbar,
  body.document-table-only-fullscreen header,
  body.document-table-only-fullscreen footer,
  body.document-table-only-fullscreen .vsummary-strip,
  body.document-table-only-fullscreen .alert,
  body.document-table-only-fullscreen .acn-indicator,
  body.document-table-only-fullscreen #acnIndicator,
  body.document-table-only-fullscreen .notification-container,
  body.document-table-only-fullscreen #notification-container,
  body.document-table-only-fullscreen #globalNotificationContainer {
    display: none !important;
  }

  body.document-table-only-fullscreen .content {
    width: 100vw !important;
    max-width: none !important;
    min-height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
    inset: 0 !important;
    background: #ffffff !important;
  }

  body.document-table-only-fullscreen .content > :not(#documentTableContainer):not(.search-box):not(style):not(script) {
    display: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer.table-dokumen,
  body.document-table-only-fullscreen #documentTableContainer {
    position: fixed !important;
    inset: 0 !important;
    z-index: 2147483000 !important;
    width: 100vw !important;
    height: 100vh !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    overflow: hidden !important;
    background: #ffffff !important;
    display: flex !important;
    flex-direction: column !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .dtable-toolbar {
    display: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .dtable-toolbar-right {
    display: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .table-container,
  body.document-table-only-fullscreen #documentTableContainer .table-responsive,
  body.document-table-only-fullscreen #documentTableContainer .virtual-table-viewport {
    flex: 1 1 auto !important;
    height: 100vh !important;
    max-height: none !important;
    margin-top: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    overflow: auto !important;
    border-top: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
  }

  /* === Tampilkan toolbar (search/filter/tambah) di mode fullscreen === */
  /* Susun sebagai kolom: toolbar di atas, tabel mengisi sisa tinggi & scroll. */
  body.document-table-only-fullscreen .content {
    position: fixed !important;
    inset: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    height: 100vh !important;
    z-index: 2147483000 !important;
    overflow: hidden !important;
  }
  body.document-table-only-fullscreen .search-box {
    display: block !important;
    position: relative !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
    padding: 12px 16px !important;
    background: #ffffff !important;
    border-bottom: 1px solid #e5e7eb !important;
    z-index: 1 !important;
  }
  body.document-table-only-fullscreen #documentTableContainer.table-dokumen,
  body.document-table-only-fullscreen #documentTableContainer {
    position: relative !important;
    inset: auto !important;
    flex: 1 1 auto !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    z-index: auto !important;
  }
  body.document-table-only-fullscreen #documentTableContainer .table-container,
  body.document-table-only-fullscreen #documentTableContainer .table-responsive,
  body.document-table-only-fullscreen #documentTableContainer .virtual-table-viewport {
    height: auto !important;
    min-height: 0 !important;
    flex: 1 1 auto !important;
  }

  body.document-table-only-fullscreen #documentTableContainer table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: 1px solid #dee2e6 !important;
    margin-bottom: 0 !important;
    width: max-content !important;
    min-width: 100% !important;
    table-layout: auto !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .virtual-scroll-status,
  body.document-table-only-fullscreen #documentTableContainer .virtual-table-badge,
  body.document-table-only-fullscreen #documentTableContainer .pagination-wrapper,
  body.document-table-only-fullscreen #documentTableContainer .pagination-enhanced-wrapper,
  body.document-table-only-fullscreen #documentTableContainer .perpage-top-bar,
  body.document-table-only-fullscreen #documentTableContainer .pagination-perpage-top-wrapper {
    display: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer thead th,
  body.document-table-only-fullscreen #documentTableContainer table thead th,
  body.document-table-only-fullscreen #documentTableContainer table thead tr th {
    position: sticky !important;
    top: 0 !important;
    z-index: 520 !important;
    background: #0d3b6e !important;
    background-clip: padding-box !important;
    border-left: 0 !important;
    border: 1px solid #1a5276 !important;
    box-shadow: 0 2px 0 #1a5276 !important;
    transform: translateZ(0);
  }

  body.document-table-only-fullscreen #documentTableContainer table thead th:last-child,
  body.document-table-only-fullscreen #documentTableContainer table thead tr th:last-child {
    border-right: 0 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer thead,
  body.document-table-only-fullscreen #documentTableContainer table thead {
    display: table-header-group !important;
    position: static !important;
    top: auto !important;
    z-index: auto !important;
    background: #0d3b6e !important;
  }

  body.document-table-only-fullscreen #documentTableContainer thead tr,
  body.document-table-only-fullscreen #documentTableContainer table thead tr {
    display: table-row !important;
    position: static !important;
    top: auto !important;
    z-index: auto !important;
    background: #0d3b6e !important;
  }

  body.document-table-only-fullscreen #documentTableContainer thead::after,
  body.document-table-only-fullscreen #documentTableContainer .col-checkbox::after {
    content: none !important;
    display: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody tr,
  body.document-table-only-fullscreen #documentTableContainer tbody td {
    position: static !important;
    z-index: 0 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody td.acn-active {
    position: relative !important;
    z-index: 1 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody tr:hover,
  body.document-table-only-fullscreen #documentTableContainer tbody tr.main-row:hover,
  body.document-table-only-fullscreen #documentTableContainer tbody tr.main-row:active {
    transform: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer th,
  body.document-table-only-fullscreen #documentTableContainer td,
  body.document-table-only-fullscreen #documentTableContainer .data-table th,
  body.document-table-only-fullscreen #documentTableContainer .data-table td {
    border-left: 0 !important;
    border-image: none !important;
    outline-color: transparent !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody td {
    padding: 8px 10px !important;
    height: 44px !important;
    min-height: 44px !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    line-height: 1.3 !important;
    border: 1px solid #dee2e6 !important;
    box-shadow: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody td:last-child {
    border-right: 1px solid #dee2e6 !important;
    box-shadow: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody tr:nth-child(even) td {
    background-color: #f8fbff !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody tr:hover td {
    background-color: #f0f5fb !important;
    border-color: #dee2e6 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .virtual-scroll-spacer,
  body.document-table-only-fullscreen #documentTableContainer table tbody tr.virtual-scroll-spacer,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody tr.virtual-scroll-spacer {
    height: var(--virtual-spacer-height, 0px) !important;
    min-height: 0 !important;
    max-height: var(--virtual-spacer-height, 0px) !important;
    background: transparent !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .virtual-scroll-spacer td,
  body.document-table-only-fullscreen #documentTableContainer table tbody tr.virtual-scroll-spacer td,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody tr.virtual-scroll-spacer td {
    height: var(--virtual-spacer-height, 0px) !important;
    min-height: 0 !important;
    max-height: var(--virtual-spacer-height, 0px) !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .data-table th.col-uraian,
  body.document-table-only-fullscreen #documentTableContainer .data-table td.col-uraian,
  body.document-table-only-fullscreen #documentTableContainer .data-table th.col-uraian_spp,
  body.document-table-only-fullscreen #documentTableContainer .data-table td.col-uraian_spp,
  body.document-table-only-fullscreen #documentTableContainer .data-table th[class*="uraian"],
  body.document-table-only-fullscreen #documentTableContainer .data-table td[class*="uraian"],
  body.document-table-only-fullscreen #documentTableContainer table th[class*="uraian"],
  body.document-table-only-fullscreen #documentTableContainer table td[class*="uraian"] {
    width: 680px !important;
    min-width: 680px !important;
    max-width: 820px !important;
  }

  body.is-fullscreen #documentTableContainer.table-dokumen,
  body.is-fullscreen #documentTableContainer {
    overflow: hidden !important;
  }

  body.is-fullscreen #documentTableContainer .table-container,
  body.is-fullscreen #documentTableContainer .table-responsive,
  body.is-fullscreen #documentTableContainer .virtual-table-viewport {
    height: 100vh !important;
    max-height: none !important;
    overflow: auto !important;
    margin-top: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border-top: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
  }

  body.is-fullscreen #documentTableContainer table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: 1px solid #dee2e6 !important;
    font-size: 12px !important;
  }

  body.is-fullscreen #documentTableContainer thead,
  body.is-fullscreen #documentTableContainer table thead {
    display: table-header-group !important;
    position: static !important;
    top: auto !important;
    z-index: auto !important;
    background: #0d3b6e !important;
  }

  body.is-fullscreen #documentTableContainer thead tr,
  body.is-fullscreen #documentTableContainer table thead tr {
    display: table-row !important;
    position: static !important;
    top: auto !important;
    z-index: auto !important;
    background: #0d3b6e !important;
  }

  body.is-fullscreen #documentTableContainer thead th,
  body.is-fullscreen #documentTableContainer table thead th,
  body.is-fullscreen #documentTableContainer table thead tr th {
    position: sticky !important;
    top: 0 !important;
    z-index: 520 !important;
    background: #0d3b6e !important;
    color: #ffffff !important;
    background-clip: padding-box !important;
    border-left: 0 !important;
    border: 1px solid #1a5276 !important;
    box-shadow: 0 2px 0 #1a5276 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    padding: 9px 8px !important;
    text-align: center !important;
    white-space: normal !important;
    overflow-wrap: normal !important;
    word-break: keep-all !important;
    hyphens: none !important;
    line-height: 1.25 !important;
  }

  body.is-fullscreen #documentTableContainer thead::after,
  body.is-fullscreen #documentTableContainer .col-checkbox::after {
    content: none !important;
    display: none !important;
  }

  body.is-fullscreen #documentTableContainer tbody tr:hover,
  body.is-fullscreen #documentTableContainer tbody tr.main-row:hover,
  body.is-fullscreen #documentTableContainer tbody tr.main-row:active {
    transform: none !important;
  }

  body.is-fullscreen #documentTableContainer tbody,
  body.is-fullscreen #documentTableContainer tbody tr,
  body.is-fullscreen #documentTableContainer tbody td {
    position: static !important;
    z-index: 0 !important;
  }

  body.is-fullscreen #documentTableContainer tbody td.acn-active {
    position: relative !important;
    z-index: 1 !important;
  }

  body.is-fullscreen #documentTableContainer th,
  body.is-fullscreen #documentTableContainer td,
  body.is-fullscreen #documentTableContainer .data-table th,
  body.is-fullscreen #documentTableContainer .data-table td {
    border-left: 0 !important;
    border-image: none !important;
  }

  body.is-fullscreen #documentTableContainer table thead th:last-child,
  body.is-fullscreen #documentTableContainer table thead tr th:last-child {
    border-right: 0 !important;
  }

  body.is-fullscreen #documentTableContainer tbody td {
    padding: 8px 10px !important;
    height: 44px !important;
    min-height: 44px !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    line-height: 1.3 !important;
    border: 1px solid #dee2e6 !important;
    box-shadow: none !important;
  }

  body.is-fullscreen #documentTableContainer tbody td:last-child {
    border-right: 1px solid #dee2e6 !important;
    box-shadow: none !important;
  }

  body.is-fullscreen #documentTableContainer tbody tr:nth-child(even) td {
    background-color: #f8fbff !important;
  }

  body.is-fullscreen #documentTableContainer tbody tr:hover td {
    background-color: #f0f5fb !important;
    border-color: #dee2e6 !important;
  }

  body.is-fullscreen #documentTableContainer .virtual-scroll-spacer,
  body.is-fullscreen #documentTableContainer table tbody tr.virtual-scroll-spacer,
  body.is-fullscreen #documentTableContainer .data-table tbody tr.virtual-scroll-spacer {
    height: var(--virtual-spacer-height, 0px) !important;
    min-height: 0 !important;
    max-height: var(--virtual-spacer-height, 0px) !important;
    background: transparent !important;
  }

  body.is-fullscreen #documentTableContainer .virtual-scroll-spacer td,
  body.is-fullscreen #documentTableContainer table tbody tr.virtual-scroll-spacer td,
  body.is-fullscreen #documentTableContainer .data-table tbody tr.virtual-scroll-spacer td {
    height: var(--virtual-spacer-height, 0px) !important;
    min-height: 0 !important;
    max-height: var(--virtual-spacer-height, 0px) !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  @media (max-width: 1400px) {

    .search-box {
      padding: 0.55rem 0.7rem !important;
    }

    .search-box .input-group-text,
    .search-box .form-control,
    .filter-section select,
    .filter-section input,
    .form-select,
    .btn-year-filter,
    .btn-filter,
    .btn-refresh,
    .btn-customize-columns-inline,
    .btn-fullscreen,
    .btn-fullscreen-toggle {
      min-height: 34px !important;
      height: 34px !important;
      padding: 0.34rem 0.58rem !important;
      font-size: 0.74rem !important;
    }

    #documentTableContainer.table-dokumen {
      padding: 0 !important;
    }

    #documentTableContainer .dtable-toolbar {
      padding: 0.35rem 0.55rem 0.5rem !important;
    }

    #documentTableContainer .dtable-toolbar-title {
      font-size: 0.9rem !important;
    }

    #documentTableContainer .dtable-toolbar-subtitle {
      font-size: 0.64rem !important;
    }

    #documentTableContainer .data-table th.col-uraian,
    #documentTableContainer .data-table td.col-uraian,
    #documentTableContainer .data-table th.col-uraian_spp,
    #documentTableContainer .data-table td.col-uraian_spp,
    #documentTableContainer .data-table th[class*="uraian"],
    #documentTableContainer .data-table td[class*="uraian"],
    #documentTableContainer table th[class*="uraian"],
    #documentTableContainer table td[class*="uraian"] {
      width: 560px !important;
      min-width: 560px !important;
      max-width: 700px !important;
      line-height: 1.32 !important;
    }
  }

  @supports selector(:fullscreen) {
    html:fullscreen body:has(#documentTableContainer) .sidebar,
    html:fullscreen body:has(#documentTableContainer) .secondary-sidebar,
    html:fullscreen body:has(#documentTableContainer) .navbar,
    html:fullscreen body:has(#documentTableContainer) .topbar,
    html:fullscreen body:has(#documentTableContainer) header,
    html:fullscreen body:has(#documentTableContainer) footer,
    html:fullscreen body:has(#documentTableContainer) .vsummary-strip,
    html:fullscreen body:has(#documentTableContainer) .search-box {
      display: none !important;
    }
  }
</style>

<script>
  (function () {
    const tableContainer = document.getElementById('documentTableContainer');
    if (!tableContainer) return;

    const fullscreenClassNames = [
      'fullscreen',
      'fullscreen-mode',
      'is-fullscreen',
      'table-fullscreen',
      'document-fullscreen',
      'active-fullscreen'
    ];

    const hasClassState = () => {
      return fullscreenClassNames.some((className) =>
        document.documentElement.classList.contains(className) ||
        document.body.classList.contains(className) ||
        tableContainer.classList.contains(className)
      );
    };

    const hasExitFullscreenControl = () => {
      const isAlreadyTableOnly = document.body.classList.contains('document-table-only-fullscreen');

      return Array.from(document.querySelectorAll('button, a')).some((element) => {
        const text = (element.textContent || '').replace(/\s+/g, ' ').trim();
        if (!/keluar fullscreen/i.test(text)) return false;
        if (isAlreadyTableOnly) return true;

        const style = window.getComputedStyle(element);
        return style.display !== 'none' && style.visibility !== 'hidden' && element.offsetParent !== null;
      });
    };

    const syncTableOnlyFullscreen = () => {
      const isFullscreen =
        Boolean(document.fullscreenElement) ||
        hasClassState() ||
        hasExitFullscreenControl();

      document.body.classList.toggle('document-table-only-fullscreen', isFullscreen);
    };

    document.addEventListener('fullscreenchange', syncTableOnlyFullscreen);
    window.addEventListener('resize', syncTableOnlyFullscreen);

    new MutationObserver(syncTableOnlyFullscreen).observe(document.body, {
      attributes: true,
      childList: true,
      subtree: true,
      attributeFilter: ['class', 'style']
    });

    syncTableOnlyFullscreen();
  })();
</script>

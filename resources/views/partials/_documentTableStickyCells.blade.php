@php
    // Halaman yang memakai kolom beku pilihan user menyalakan $dynamicFrozen.
    // Default false => perilaku lama (kolom beku di-hardcode) tidak berubah.
    $dynamicFrozen = $dynamicFrozen ?? false;
@endphp

<style>
  #documentTableContainer .table-responsive {
    position: relative;
  }

  #documentTableContainer .data-table {
    table-layout: fixed;
    width: max-content;
    min-width: 100%;
  }

  #documentTableContainer .data-table th,
  #documentTableContainer .data-table td {
    box-sizing: border-box;
    max-width: 100%;
    overflow: hidden;
    text-overflow: clip;
  }

  #documentTableContainer .data-table thead th,
  body.is-fullscreen #documentTableContainer .data-table thead th,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead th {
    border-right: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-left: 1px solid rgba(255, 255, 255, 0.08) !important;
  }

  #documentTableContainer .data-table thead th:first-child,
  body.is-fullscreen #documentTableContainer .data-table thead th:first-child,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead th:first-child {
    border-left: none !important;
  }

  #documentTableContainer .data-table thead th:last-child,
  body.is-fullscreen #documentTableContainer .data-table thead th:last-child,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead th:last-child {
    border-right: none !important;
  }

  #documentTableContainer .data-table tbody td {
    white-space: normal !important;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.45;
    border-right: 1px solid #d9e0e7 !important;
  }

  #documentTableContainer .data-table thead th,
  body.is-fullscreen #documentTableContainer .data-table thead th,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead th {
    background: #0d3b6e !important;
    box-shadow: 0 2px 0 #1a5276 !important;
    color: rgba(255, 255, 255, 0.95) !important;
  }

  #documentTableContainer .data-table .col-checkbox {
    width: 64px;
    min-width: 64px;
  }

  #documentTableContainer .data-table .col-no,
  #documentTableContainer .data-table .col-number {
    width: 88px;
    min-width: 88px;
  }

  #documentTableContainer .data-table .col-nomor_agenda {
    width: 210px;
    min-width: 210px;
  }

  #documentTableContainer .data-table .col-bulan {
    width: 110px;
    min-width: 110px;
  }

  #documentTableContainer .data-table .col-tahun {
    width: 100px;
    min-width: 100px;
  }

  #documentTableContainer .data-table .col-kategori,
  #documentTableContainer .data-table .col-jenis_dokumen,
  #documentTableContainer .data-table .col-jenis_sub_pekerjaan {
    width: 300px;
    min-width: 300px;
  }

  #documentTableContainer .data-table .col-jenis_pembayaran {
    width: 190px;
    min-width: 190px;
  }

  #documentTableContainer .data-table .col-nomor_spp {
    width: 230px;
    min-width: 230px;
  }

  #documentTableContainer .data-table .col-tanggal_masuk,
  #documentTableContainer .data-table .col-tanggal_spp,
  #documentTableContainer .data-table .col-tanggal_berita_acara,
  #documentTableContainer .data-table .col-tanggal_spk,
  #documentTableContainer .data-table .col-tanggal_berakhir_spk,
  #documentTableContainer .data-table .col-tanggal_faktur,
  #documentTableContainer .data-table .col-tanggal_paraf,
  #documentTableContainer .data-table .col-tanggal_miro,
  #documentTableContainer .data-table .col-tanggal_selesai_verifikasi_pajak {
    width: 180px;
    min-width: 180px;
  }

  #documentTableContainer .data-table .col-deadline {
    width: 210px;
    min-width: 210px;
  }

  #documentTableContainer .data-table .col-status {
    width: 300px;
    min-width: 300px;
  }

  #documentTableContainer .data-table .col-handler {
    width: 240px;
    min-width: 240px;
  }

  #documentTableContainer .data-table .col-checkbox,
  #documentTableContainer .data-table .col-no,
  #documentTableContainer .data-table .col-number {
    position: sticky !important;
    background-clip: padding-box;
  }

  @unless($dynamicFrozen)
  #documentTableContainer .data-table .col-nomor_agenda,
  #documentTableContainer .data-table .col-handler {
    position: sticky !important;
    background-clip: padding-box;
  }
  @endunless

  #documentTableContainer .data-table thead .col-checkbox,
  #documentTableContainer .data-table thead .col-no,
  #documentTableContainer .data-table thead .col-number,
  body.is-fullscreen #documentTableContainer .data-table thead .col-checkbox,
  body.is-fullscreen #documentTableContainer .data-table thead .col-no,
  body.is-fullscreen #documentTableContainer .data-table thead .col-number,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead .col-checkbox,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead .col-no,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead .col-number {
    background: #0d3b6e !important;
    box-shadow: 0 2px 0 #1a5276 !important;
    z-index: 560 !important;
  }

  @unless($dynamicFrozen)
  /* Mode lama: nomor_agenda & handler memang selalu beku, jadi header keduanya ikut
     ditinggikan. Pada mode dinamis aturan ini WAJIB absen — halaman induk sudah
     memberi z-index 560 hanya pada kolom yang benar-benar dibekukan user. Bila
     dibiarkan, header kolom yang TIDAK dibekukan tetap membawa z-index 560 (semua
     <th> sudah position: sticky dari halaman induk) sehingga nilainya seri dengan
     header kolom beku; pemenangnya jatuh ke urutan DOM dan header di tengah
     melintas DI ATAS kolom beku kiri saat tabel digulir horizontal. */
  #documentTableContainer .data-table thead .col-nomor_agenda,
  #documentTableContainer .data-table thead .col-handler,
  body.is-fullscreen #documentTableContainer .data-table thead .col-nomor_agenda,
  body.is-fullscreen #documentTableContainer .data-table thead .col-handler,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead .col-nomor_agenda,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead .col-handler {
    background: #0d3b6e !important;
    box-shadow: 0 2px 0 #1a5276 !important;
    z-index: 560 !important;
  }
  @endunless

  #documentTableContainer .data-table tbody .col-checkbox,
  #documentTableContainer .data-table tbody .col-no,
  #documentTableContainer .data-table tbody .col-number,
  body.is-fullscreen #documentTableContainer .data-table tbody .col-checkbox,
  body.is-fullscreen #documentTableContainer .data-table tbody .col-no,
  body.is-fullscreen #documentTableContainer .data-table tbody .col-number,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody .col-checkbox,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody .col-no,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody .col-number {
    background: #ffffff !important;
    z-index: 30 !important;
  }

  @unless($dynamicFrozen)
  /* Sepasang dengan blok thead di atas: latar & z-index sel badan untuk dua kolom
     beku mode lama. Pada mode dinamis nilainya datang dari halaman induk, hanya
     untuk kolom yang memang dibekukan. */
  #documentTableContainer .data-table tbody .col-nomor_agenda,
  #documentTableContainer .data-table tbody .col-handler,
  body.is-fullscreen #documentTableContainer .data-table tbody .col-nomor_agenda,
  body.is-fullscreen #documentTableContainer .data-table tbody .col-handler,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody .col-nomor_agenda,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody .col-handler {
    background: #ffffff !important;
    z-index: 30 !important;
  }
  @endunless

  #documentTableContainer .data-table tbody tr.main-row:nth-child(even) > .col-checkbox,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(even) > .col-no,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(even) > .col-number,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(even) > .col-nomor_agenda,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(even) > .col-handler {
    background: #f8fafc !important;
  }

  #documentTableContainer .data-table tbody tr.main-row:nth-child(odd) > .col-checkbox,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(odd) > .col-no,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(odd) > .col-number,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(odd) > .col-nomor_agenda,
  #documentTableContainer .data-table tbody tr.main-row:nth-child(odd) > .col-handler {
    background: #ffffff !important;
  }

  #documentTableContainer .data-table tbody tr.main-row:hover > .col-checkbox,
  #documentTableContainer .data-table tbody tr.main-row:hover > .col-no,
  #documentTableContainer .data-table tbody tr.main-row:hover > .col-number,
  #documentTableContainer .data-table tbody tr.main-row:hover > .col-nomor_agenda,
  #documentTableContainer .data-table tbody tr.main-row:hover > .col-handler {
    background: #f3faf9 !important;
  }

  #documentTableContainer .data-table .col-no,
  #documentTableContainer .data-table .col-number {
    left: 0 !important;
  }

  @unless($dynamicFrozen)
  /* Mode lama: posisi tepi kedua kolom beku ditetapkan lewat CSS.
     Pada mode dinamis aturan ini harus absen — nilai left/right dihitung JS
     dan ditulis sebagai inline style, yang akan kalah oleh !important di sini. */
  #documentTableContainer .data-table .col-nomor_agenda {
    left: var(--document-sticky-agenda-left, 88px) !important;
  }

  #documentTableContainer .data-table .col-handler {
    right: 0 !important;
  }
  @endunless

  #documentTableContainer .data-table thead .col-handler,
  body.is-fullscreen #documentTableContainer .data-table thead .col-handler,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead .col-handler {
    z-index: 570 !important;
  }

  #documentTableContainer .data-table thead th.acn-active-col,
  body.is-fullscreen #documentTableContainer .data-table thead th.acn-active-col,
  body.document-table-only-fullscreen #documentTableContainer .data-table thead th.acn-active-col {
    background: #0d3b6e !important;
    box-shadow: 0 2px 0 #1a5276 !important;
  }

  #documentTableContainer .data-table tbody td.acn-active,
  body.is-fullscreen #documentTableContainer .data-table tbody td.acn-active,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody td.acn-active {
    position: relative !important;
    z-index: 55 !important;
    outline: 3px solid #083E40 !important;
    outline-offset: -3px !important;
    background-color: #e6f0ef !important;
    box-shadow:
      inset 0 0 0 1px rgba(245, 158, 11, 0.8),
      0 0 0 1px rgba(255, 255, 255, 0.75) !important;
  }

  body.is-fullscreen #documentTableContainer .data-table tbody td.acn-active,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody td.acn-active {
    outline-color: #052f31 !important;
    box-shadow:
      inset 0 0 0 1px #f59e0b,
      0 0 0 2px rgba(255, 255, 255, 0.9) !important;
  }

  #documentTableContainer .data-table tbody td.acn-active.col-checkbox,
  #documentTableContainer .data-table tbody td.acn-active.col-no,
  #documentTableContainer .data-table tbody td.acn-active.col-number,
  #documentTableContainer .data-table tbody td.acn-active.col-nomor_agenda,
  #documentTableContainer .data-table tbody td.acn-active.col-handler,
  body.is-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-checkbox,
  body.is-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-no,
  body.is-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-number,
  body.is-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-nomor_agenda,
  body.is-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-handler,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-checkbox,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-no,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-number,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-nomor_agenda,
  body.document-table-only-fullscreen #documentTableContainer .data-table tbody td.acn-active.col-handler {
    position: sticky !important;
    background: #e6f0ef !important;
    z-index: 80 !important;
  }
</style>

<script>
  (function () {
    const containerId = 'documentTableContainer';

    // Daftar kolom beku mode lama (hardcoded). Mode dinamis memakai getStickySelector().
    const staticStickySelector = '.col-no, .col-number, .col-nomor_agenda, .col-handler';

    // Kunci kolom hanya boleh berisi karakter aman. Kunci cacat diabaikan supaya
    // selector rusak tidak melempar exception dan mematikan penanganan klik tabel.
    function isSafeColumnKey(key) {
      return typeof key === 'string' && /^[A-Za-z0-9_-]+$/.test(key);
    }

    // Selector kolom beku dihitung SAAT DIPAKAI, bukan sekali di awal, karena
    // window.DOCUMENT_STICKY_CONFIG baru terbaca saat runtime.
    function getStickySelector() {
      const config = window.DOCUMENT_STICKY_CONFIG;
      if (!config) return staticStickySelector;

      const frozenKeys = getConfigKeys(config.left).concat(getConfigKeys(config.right));

      // Kolom nomor urut baris tetap beku di mode dinamis, jadi selalu ikut.
      return ['.col-no', '.col-number']
        .concat(frozenKeys.map(key => `.col-${key}`))
        .join(', ');
    }

    function getConfigKeys(value) {
      return (Array.isArray(value) ? value : []).filter(isSafeColumnKey);
    }

    function getContainer() {
      return document.getElementById(containerId);
    }

    function getTable(container) {
      return container ? container.querySelector('.data-table') : null;
    }

    function getScrollBox(container) {
      return container ? container.querySelector('.table-responsive') : null;
    }

    function findColumn(table, selector) {
      const selectors = selector.split(',').map(item => item.trim()).filter(Boolean);
      for (const item of selectors) {
        const column = table.querySelector(`thead ${item}`) || table.querySelector(`tbody ${item}`);
        if (column) return column;
      }
      return null;
    }

    function measureWidth(table, selector) {
      const column = findColumn(table, selector);
      return column ? column.getBoundingClientRect().width : 0;
    }

    // Mengurutkan kunci kolom mengikuti urutan <th> di thead.
    // Urutan penyimpanan config = urutan klik user, yang belum tentu sama dengan
    // urutan DOM; hanya urutan DOM yang menentukan offset beku yang benar.
    function orderKeysByDom(table, keys) {
      const headers = Array.from(table.querySelectorAll('thead th'));
      const positions = new Map();

      headers.forEach(function (header, index) {
        header.classList.forEach(function (name) {
          if (name.indexOf('col-') === 0 && !positions.has(name)) positions.set(name, index);
        });
      });

      return getConfigKeys(keys)
        .filter(key => positions.has(`col-${key}`))
        .sort((a, b) => positions.get(`col-${a}`) - positions.get(`col-${b}`));
    }

    // Menempelkan offset beku pada satu sisi, menumpuk lebar kolom sebelumnya.
    // Kolom berlebar 0 (belum terukur / tersembunyi) dilewati, karena menghitungnya
    // sebagai 0 membuat kolom beku sesudahnya bertumpuk.
    function applyStickyOffsets(table, keys, edge, startOffset) {
      let offset = startOffset;

      keys.forEach(function (key) {
        const cells = table.querySelectorAll(`thead .col-${key}, tbody .col-${key}`);
        if (!cells.length) return;
        const width = cells[0].getBoundingClientRect().width;
        if (width <= 0) return;
        cells.forEach(cell => { cell.style[edge] = `${Math.round(offset)}px`; });
        offset += width;
      });

      return offset;
    }

    // Jalur dinamis: kolom beku ditentukan user, bukan di-hardcode.
    // KONTRAK config = { left: ['key', ...], right: ['key', ...] }. Kedua array
    // diperlakukan sebagai HIMPUNAN keanggotaan saja — urutannya tidak dipakai.
    // Urutan visual (kolom mana menempel paling luar) diturunkan dari urutan DOM:
    // kiri = <th> paling awal menempel paling kiri, kanan = <th> paling akhir
    // menempel paling kanan.
    function syncDynamicStickyOffsets(config) {
      const container = getContainer();
      const table = getTable(container);
      if (!container || !table) return;

      // Kolom nomor urut baris selalu beku paling kiri, jadi jadi titik awal offset.
      const numberWidth = measureWidth(table, '.col-no, .col-number');

      const leftKeys = orderKeysByDom(table, config.left);
      const leftOffset = applyStickyOffsets(table, leftKeys, 'left', numberWidth);

      // Ditelusuri mundur dari kolom terkanan agar offset menumpuk ke arah dalam.
      const rightKeys = orderKeysByDom(table, config.right).reverse();
      const rightOffset = applyStickyOffsets(table, rightKeys, 'right', 0);

      container.style.setProperty('--document-sticky-left-width', `${Math.round(leftOffset)}px`);
      container.style.setProperty('--document-sticky-right-width', `${Math.round(rightOffset)}px`);
    }

    function syncDocumentStickyOffsets() {
      const dynamicConfig = window.DOCUMENT_STICKY_CONFIG;
      if (dynamicConfig) {
        syncDynamicStickyOffsets(dynamicConfig);
        return;
      }

      const container = getContainer();
      const table = getTable(container);
      if (!container || !table) return;

      const numberWidth = measureWidth(table, '.col-no, .col-number');
      const agendaWidth = measureWidth(table, '.col-nomor_agenda');
      const handlerWidth = measureWidth(table, '.col-handler');

      // Kolom No kini paling kiri (left:0); kolom Nomor Agenda menempel tepat setelahnya.
      if (numberWidth > 0) {
        container.style.setProperty('--document-sticky-agenda-left', `${Math.round(numberWidth)}px`);
        container.style.setProperty('--document-sticky-left-width', `${Math.round(numberWidth + agendaWidth)}px`);
      }

      if (handlerWidth > 0) {
        container.style.setProperty('--document-sticky-right-width', `${Math.round(handlerWidth)}px`);
      }
    }

    function scrollCellFullyIntoView(cell) {
      const container = getContainer();
      const scrollBox = getScrollBox(container);
      if (!container || !scrollBox || !cell || !cell.closest(`#${containerId} tbody`)) return;
      if (cell.matches(getStickySelector())) return;

      syncDocumentStickyOffsets();

      const scrollRect = scrollBox.getBoundingClientRect();
      const cellRect = cell.getBoundingClientRect();
      const leftGuard = parseFloat(getComputedStyle(container).getPropertyValue('--document-sticky-left-width')) || 0;
      const rightGuard = parseFloat(getComputedStyle(container).getPropertyValue('--document-sticky-right-width')) || 0;
      const visibleLeft = scrollRect.left + leftGuard + 8;
      const visibleRight = scrollRect.right - rightGuard - 8;

      if (cellRect.left < visibleLeft) {
        scrollBox.scrollLeft -= Math.ceil(visibleLeft - cellRect.left);
      } else if (cellRect.right > visibleRight) {
        scrollBox.scrollLeft += Math.ceil(cellRect.right - visibleRight);
      }
    }

    function scrollActiveCellFullyIntoView() {
      const container = getContainer();
      const activeCell = container ? container.querySelector('tbody td.acn-active') : null;
      scrollCellFullyIntoView(activeCell);
    }

    function scheduleScrollForCell(cell) {
      requestAnimationFrame(() => {
        requestAnimationFrame(() => scrollCellFullyIntoView(cell));
      });
    }

    document.addEventListener('click', function (event) {
      const cell = event.target.closest(`#${containerId} .data-table tbody td`);
      if (cell) scheduleScrollForCell(cell);
    });

    document.addEventListener('keydown', function (event) {
      if (['ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp', 'Tab', 'Enter'].includes(event.key)) {
        requestAnimationFrame(() => requestAnimationFrame(scrollActiveCellFullyIntoView));
      }
    });

    window.syncDocumentStickyOffsets = syncDocumentStickyOffsets;
    window.scrollDocumentActiveCellIntoView = scrollActiveCellFullyIntoView;
    window.addEventListener('resize', () => requestAnimationFrame(syncDocumentStickyOffsets));
    window.addEventListener('document-table-refreshed', () => requestAnimationFrame(syncDocumentStickyOffsets));
    document.addEventListener('fullscreenchange', () => setTimeout(syncDocumentStickyOffsets, 100));
    document.addEventListener('DOMContentLoaded', () => {
      syncDocumentStickyOffsets();
      setTimeout(syncDocumentStickyOffsets, 250);
    });
    requestAnimationFrame(syncDocumentStickyOffsets);
  })();
</script>

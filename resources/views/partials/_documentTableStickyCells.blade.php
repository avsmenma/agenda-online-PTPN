<style>
  #documentTableContainer .table-responsive {
    position: relative;
  }

  #documentTableContainer .table-enhanced {
    table-layout: fixed;
    width: max-content;
    min-width: 100%;
  }

  #documentTableContainer .table-enhanced th,
  #documentTableContainer .table-enhanced td {
    box-sizing: border-box;
    max-width: 100%;
    overflow: hidden;
    text-overflow: clip;
  }

  #documentTableContainer .table-enhanced thead th,
  body.is-fullscreen #documentTableContainer .table-enhanced thead th,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead th {
    border-right: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-left: 1px solid rgba(255, 255, 255, 0.08) !important;
  }

  #documentTableContainer .table-enhanced thead th:first-child,
  body.is-fullscreen #documentTableContainer .table-enhanced thead th:first-child,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead th:first-child {
    border-left: none !important;
  }

  #documentTableContainer .table-enhanced thead th:last-child,
  body.is-fullscreen #documentTableContainer .table-enhanced thead th:last-child,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead th:last-child {
    border-right: none !important;
  }

  #documentTableContainer .table-enhanced tbody td {
    white-space: normal !important;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.45;
    border-right: 1px solid #d9e0e7 !important;
  }

  #documentTableContainer .table-enhanced thead th,
  body.is-fullscreen #documentTableContainer .table-enhanced thead th,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead th {
    background: #0d3b6e !important;
    box-shadow: 0 2px 0 #1a5276 !important;
    color: rgba(255, 255, 255, 0.95) !important;
  }

  #documentTableContainer .table-enhanced .col-checkbox {
    width: 64px;
    min-width: 64px;
  }

  #documentTableContainer .table-enhanced .col-no,
  #documentTableContainer .table-enhanced .col-number {
    width: 88px;
    min-width: 88px;
  }

  #documentTableContainer .table-enhanced .col-nomor_agenda {
    width: 210px;
    min-width: 210px;
  }

  #documentTableContainer .table-enhanced .col-bulan {
    width: 110px;
    min-width: 110px;
  }

  #documentTableContainer .table-enhanced .col-tahun {
    width: 100px;
    min-width: 100px;
  }

  #documentTableContainer .table-enhanced .col-kategori,
  #documentTableContainer .table-enhanced .col-jenis_dokumen,
  #documentTableContainer .table-enhanced .col-jenis_sub_pekerjaan {
    width: 300px;
    min-width: 300px;
  }

  #documentTableContainer .table-enhanced .col-jenis_pembayaran {
    width: 190px;
    min-width: 190px;
  }

  #documentTableContainer .table-enhanced .col-nomor_spp {
    width: 230px;
    min-width: 230px;
  }

  #documentTableContainer .table-enhanced .col-tanggal_masuk,
  #documentTableContainer .table-enhanced .col-tanggal_spp,
  #documentTableContainer .table-enhanced .col-tanggal_berita_acara,
  #documentTableContainer .table-enhanced .col-tanggal_spk,
  #documentTableContainer .table-enhanced .col-tanggal_berakhir_spk,
  #documentTableContainer .table-enhanced .col-tanggal_faktur,
  #documentTableContainer .table-enhanced .col-tanggal_paraf,
  #documentTableContainer .table-enhanced .col-tanggal_miro,
  #documentTableContainer .table-enhanced .col-tanggal_selesai_verifikasi_pajak {
    width: 180px;
    min-width: 180px;
  }

  #documentTableContainer .table-enhanced .col-deadline {
    width: 210px;
    min-width: 210px;
  }

  #documentTableContainer .table-enhanced .col-status {
    width: 300px;
    min-width: 300px;
  }

  #documentTableContainer .table-enhanced .col-handler {
    width: 240px;
    min-width: 240px;
  }

  #documentTableContainer .table-enhanced .col-checkbox,
  #documentTableContainer .table-enhanced .col-no,
  #documentTableContainer .table-enhanced .col-number,
  #documentTableContainer .table-enhanced .col-nomor_agenda,
  #documentTableContainer .table-enhanced .col-handler {
    position: sticky !important;
    background-clip: padding-box;
  }

  #documentTableContainer .table-enhanced thead .col-checkbox,
  #documentTableContainer .table-enhanced thead .col-no,
  #documentTableContainer .table-enhanced thead .col-number,
  #documentTableContainer .table-enhanced thead .col-nomor_agenda,
  #documentTableContainer .table-enhanced thead .col-handler,
  body.is-fullscreen #documentTableContainer .table-enhanced thead .col-checkbox,
  body.is-fullscreen #documentTableContainer .table-enhanced thead .col-no,
  body.is-fullscreen #documentTableContainer .table-enhanced thead .col-number,
  body.is-fullscreen #documentTableContainer .table-enhanced thead .col-nomor_agenda,
  body.is-fullscreen #documentTableContainer .table-enhanced thead .col-handler,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead .col-checkbox,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead .col-no,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead .col-number,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead .col-nomor_agenda,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead .col-handler {
    background: #0d3b6e !important;
    box-shadow: 0 2px 0 #1a5276 !important;
    z-index: 560 !important;
  }

  #documentTableContainer .table-enhanced tbody .col-checkbox,
  #documentTableContainer .table-enhanced tbody .col-no,
  #documentTableContainer .table-enhanced tbody .col-number,
  #documentTableContainer .table-enhanced tbody .col-nomor_agenda,
  #documentTableContainer .table-enhanced tbody .col-handler,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody .col-checkbox,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody .col-no,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody .col-number,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody .col-nomor_agenda,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody .col-handler,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody .col-checkbox,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody .col-no,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody .col-number,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody .col-nomor_agenda,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody .col-handler {
    background: #ffffff !important;
    z-index: 30 !important;
  }

  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(even) > .col-checkbox,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(even) > .col-no,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(even) > .col-number,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(even) > .col-nomor_agenda,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(even) > .col-handler {
    background: #f8fafc !important;
  }

  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(odd) > .col-checkbox,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(odd) > .col-no,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(odd) > .col-number,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(odd) > .col-nomor_agenda,
  #documentTableContainer .table-enhanced tbody tr.main-row:nth-child(odd) > .col-handler {
    background: #ffffff !important;
  }

  #documentTableContainer .table-enhanced tbody tr.main-row:hover > .col-checkbox,
  #documentTableContainer .table-enhanced tbody tr.main-row:hover > .col-no,
  #documentTableContainer .table-enhanced tbody tr.main-row:hover > .col-number,
  #documentTableContainer .table-enhanced tbody tr.main-row:hover > .col-nomor_agenda,
  #documentTableContainer .table-enhanced tbody tr.main-row:hover > .col-handler {
    background: #f3faf9 !important;
  }

  #documentTableContainer .table-enhanced .col-checkbox {
    left: 0 !important;
  }

  #documentTableContainer .table-enhanced .col-no,
  #documentTableContainer .table-enhanced .col-number {
    left: var(--document-sticky-no-left, 56px) !important;
  }

  #documentTableContainer .table-enhanced .col-nomor_agenda {
    left: var(--document-sticky-agenda-left, 132px) !important;
  }

  #documentTableContainer .table-enhanced .col-handler {
    right: 0 !important;
  }

  #documentTableContainer .table-enhanced thead .col-handler,
  body.is-fullscreen #documentTableContainer .table-enhanced thead .col-handler,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead .col-handler {
    z-index: 570 !important;
  }

  #documentTableContainer .table-enhanced thead th.acn-active-col,
  body.is-fullscreen #documentTableContainer .table-enhanced thead th.acn-active-col,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced thead th.acn-active-col {
    background: #0d3b6e !important;
    box-shadow: 0 2px 0 #1a5276 !important;
  }

  #documentTableContainer .table-enhanced tbody td.acn-active,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active {
    position: relative !important;
    z-index: 55 !important;
    outline: 3px solid #083E40 !important;
    outline-offset: -3px !important;
    background-color: #e6f0ef !important;
    box-shadow:
      inset 0 0 0 1px rgba(245, 158, 11, 0.8),
      0 0 0 1px rgba(255, 255, 255, 0.75) !important;
  }

  body.is-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active {
    outline-color: #052f31 !important;
    box-shadow:
      inset 0 0 0 1px #f59e0b,
      0 0 0 2px rgba(255, 255, 255, 0.9) !important;
  }

  #documentTableContainer .table-enhanced tbody td.acn-active.col-checkbox,
  #documentTableContainer .table-enhanced tbody td.acn-active.col-no,
  #documentTableContainer .table-enhanced tbody td.acn-active.col-number,
  #documentTableContainer .table-enhanced tbody td.acn-active.col-nomor_agenda,
  #documentTableContainer .table-enhanced tbody td.acn-active.col-handler,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-checkbox,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-no,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-number,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-nomor_agenda,
  body.is-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-handler,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-checkbox,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-no,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-number,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-nomor_agenda,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced tbody td.acn-active.col-handler {
    position: sticky !important;
    background: #e6f0ef !important;
    z-index: 80 !important;
  }
</style>

<script>
  (function () {
    const containerId = 'documentTableContainer';
    const leftStickySelector = '.col-checkbox, .col-no, .col-number, .col-nomor_agenda';
    const stickySelector = `${leftStickySelector}, .col-handler`;

    function getContainer() {
      return document.getElementById(containerId);
    }

    function getTable(container) {
      return container ? container.querySelector('.table-enhanced') : null;
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

    function syncDocumentStickyOffsets() {
      const container = getContainer();
      const table = getTable(container);
      if (!container || !table) return;

      const checkboxWidth = measureWidth(table, '.col-checkbox');
      const numberWidth = measureWidth(table, '.col-no, .col-number');
      const agendaWidth = measureWidth(table, '.col-nomor_agenda');
      const handlerWidth = measureWidth(table, '.col-handler');

      if (checkboxWidth > 0) {
        container.style.setProperty('--document-sticky-no-left', `${Math.round(checkboxWidth)}px`);
        container.style.setProperty('--document-sticky-agenda-left', `${Math.round(checkboxWidth + numberWidth)}px`);
        container.style.setProperty('--document-sticky-left-width', `${Math.round(checkboxWidth + numberWidth + agendaWidth)}px`);
      }

      if (handlerWidth > 0) {
        container.style.setProperty('--document-sticky-right-width', `${Math.round(handlerWidth)}px`);
      }
    }

    function scrollCellFullyIntoView(cell) {
      const container = getContainer();
      const scrollBox = getScrollBox(container);
      if (!container || !scrollBox || !cell || !cell.closest(`#${containerId} tbody`)) return;
      if (cell.matches(stickySelector)) return;

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
      const cell = event.target.closest(`#${containerId} .table-enhanced tbody td`);
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

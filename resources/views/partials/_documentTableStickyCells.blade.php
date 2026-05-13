<style>
  #documentTableContainer .table-responsive {
    position: relative;
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

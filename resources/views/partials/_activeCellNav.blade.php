{{--
  _activeCellNav.blade.php
  Spreadsheet-style active cell navigation untuk tabel daftar dokumen.

  Usage: @include('partials._activeCellNav', ['tableSelector' => '.data-table'])
--}}
@php
  $tableSelector = $tableSelector ?? '.data-table';
@endphp

<style>
  .acn-indicator {
    display: none !important;
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
    color: white;
    padding: 6px 16px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 0 0 8px 8px;
    z-index: 20;
    display: none !important;
    align-items: center;
    gap: 12px;
    user-select: none;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s ease;
  }

  .acn-indicator.visible {
    opacity: 0;
    pointer-events: none;
  }

  .acn-cell-ref {
    background: rgba(255,255,255,0.15);
    border-radius: 4px;
    padding: 2px 10px;
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
    font-size: 13px;
  }

  .acn-hint {
    opacity: 0.75;
    font-size: 11px;
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .acn-key {
    background: rgba(255,255,255,0.2);
    border-radius: 3px;
    padding: 1px 6px;
    font-family: monospace;
    font-size: 11px;
    border: 1px solid rgba(255,255,255,0.3);
  }

  td.acn-active {
    outline: 3px solid #083E40 !important;
    outline-offset: -2px !important;
    background-color: rgba(8, 62, 64, 0.08) !important;
    position: relative;
    z-index: 2;
    transition: background-color 0.15s ease;
  }

  tr.acn-active-row > td {
    background-color: rgba(136, 151, 23, 0.04);
  }

  th.acn-active-col {
    background: linear-gradient(135deg, #0a5f52 0%, #083E40 100%) !important;
    box-shadow: inset 0 -3px 0 #889717;
  }

  td.acn-active::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 8px 0 0;
    border-color: #083E40 transparent transparent transparent;
    pointer-events: none;
  }

  @keyframes acn-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(8, 62, 64, 0.5); }
    70%  { box-shadow: 0 0 0 6px rgba(8, 62, 64, 0); }
    100% { box-shadow: 0 0 0 0 rgba(8, 62, 64, 0); }
  }

  td.acn-pulse {
    animation: acn-pulse 0.4s ease-out;
  }

  .table-wrapper,
  .table-responsive {
    scroll-behavior: smooth;
  }
</style>

<script>
(function () {
  'use strict';

  const TABLE_SELECTOR = '{{ $tableSelector }}';
  const INDICATOR_ID = 'acnIndicator';
  const REBIND_EVENT = 'document-table-refreshed';

  let tableEl = null;
  let tbodyEl = null;
  let theadEl = null;
  let wrapperEl = null;
  let indicatorEl = null;
  let observer = null;
  let rebindTimer = null;
  let keyboardBound = false;

  let activeCell = null;
  let activeRow = 0;
  let activeCol = 0;
  let activeDocId = null;
  // Highlight sel hanya muncul setelah user benar-benar berinteraksi
  // (klik sel atau navigasi keyboard) — bukan otomatis saat halaman dimuat.
  let userEngaged = false;
  // Cache baris agar getComputedStyle tidak dihitung ulang tiap navigasi/keystroke (anti-jank pada data banyak).
  // Cache di-reset hanya saat tabel berubah (mutation/rebind).
  let rowsCache = null;
  function invalidateRowsCache() { rowsCache = null; }

  function findTable() {
    return document.querySelector(TABLE_SELECTOR);
  }

  function getDataRows() {
    if (rowsCache) return rowsCache;
    if (!tbodyEl) return [];
    rowsCache = Array.from(tbodyEl.querySelectorAll('tr:not(.detail-row)'))
      .filter(row => {
        if (row.classList.contains('virtual-scroll-spacer')) return false;
        if (row.dataset.acnIgnore === 'true') return false;
        if (row.hidden || row.getAttribute('aria-hidden') === 'true') return false;

        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return false;

        const rowStyle = window.getComputedStyle(row);
        if (rowStyle.display === 'none' || rowStyle.visibility === 'hidden') return false;

        return true;
      });
    return rowsCache;
  }

  function getCells(row) {
    return Array.from(row.querySelectorAll('td'));
  }

  function getDocId(row) {
    return row?.dataset?.dokumenId || row?.dataset?.id || null;
  }

  function createIndicator() {
    indicatorEl = document.getElementById(INDICATOR_ID);
    if (!indicatorEl) {
      indicatorEl = document.createElement('div');
      indicatorEl.className = 'acn-indicator';
      indicatorEl.id = INDICATOR_ID;
      indicatorEl.innerHTML = `
        <i class="fa-solid fa-table-cells" style="font-size:13px; opacity:0.8;"></i>
        <span class="acn-cell-ref" id="acnCellRef">-</span>
        <span id="acnColName" style="opacity:0.85; font-size:11px;"></span>
        <span class="acn-hint">
          <span class="acn-key">Arrow</span> navigasi &nbsp;
          <span class="acn-key">Enter/F2</span> edit
        </span>`;
    }

    if (wrapperEl && indicatorEl.previousElementSibling !== wrapperEl) {
      wrapperEl.insertAdjacentElement('afterend', indicatorEl);
    }
    indicatorEl.style.display = 'flex';
  }

  function clearHighlightOnly() {
    if (activeCell) {
      activeCell.classList.remove('acn-active', 'acn-pulse');
    }

    if (tbodyEl) {
      tbodyEl.querySelectorAll('tr.acn-active-row').forEach(tr => tr.classList.remove('acn-active-row'));
    }

    if (theadEl) {
      theadEl.querySelectorAll('th.acn-active-col').forEach(th => th.classList.remove('acn-active-col'));
    }

    activeCell = null;
  }

  function setActiveCell(rowIdx, colIdx, options = {}) {
    const rows = getDataRows();
    if (rows.length === 0) return false;

    const clampedRow = Math.max(0, Math.min(rowIdx, rows.length - 1));
    const row = rows[clampedRow];
    const cells = getCells(row);
    if (cells.length === 0) return false;

    const clampedCol = Math.max(0, Math.min(colIdx, cells.length - 1));
    const cell = cells[clampedCol];

    clearHighlightOnly();

    activeCell = cell;
    activeRow = clampedRow;
    activeCol = clampedCol;
    activeDocId = getDocId(row);

    row.classList.add('acn-active-row');
    cell.classList.add('acn-active');
    if (!options.noPulse) {
      cell.classList.add('acn-pulse');
      cell.addEventListener('animationend', () => cell.classList.remove('acn-pulse'), { once: true });
    }

    if (theadEl) {
      const headerRow = theadEl.querySelector('tr');
      const headers = headerRow ? Array.from(headerRow.querySelectorAll('th')) : [];
      headers.forEach(th => th.classList.remove('acn-active-col'));
      if (headers[clampedCol]) headers[clampedCol].classList.add('acn-active-col');
    }

    updateIndicator(clampedRow, clampedCol);

    if (!options.preventScroll) {
      scrollCellIntoView(cell);
    }

    return true;
  }

  function autoSelectFirstCell(options = {}) {
    const rows = getDataRows();
    if (rows.length === 0) return false;

    const cells = getCells(rows[0]);
    if (cells.length === 0) return false;

    const startCol = cells.length > 1 ? 1 : 0;
    return setActiveCell(0, startCol, options);
  }

  function restoreActiveCell(options = {}) {
    const rows = getDataRows();
    if (rows.length === 0) {
      clearHighlightOnly();
      return false;
    }

    if (activeDocId) {
      const rowByDoc = rows.find(row => getDocId(row) === activeDocId);
      if (rowByDoc) {
        return setActiveCell(rows.indexOf(rowByDoc), activeCol, options);
      }
    }

    if (activeCell) {
      return setActiveCell(activeRow, activeCol, options);
    }

    // Jangan auto-pilih sel pertama sebelum user berinteraksi — agar tidak
    // muncul highlight hijau yang mengganggu saat halaman baru dimuat.
    if (userEngaged) {
      return autoSelectFirstCell(options);
    }

    clearHighlightOnly();
    return false;
  }

  function updateIndicator(rowIdx, colIdx) {
    if (!indicatorEl) return;

    const ref = document.getElementById('acnCellRef');
    const colName = document.getElementById('acnColName');

    if (ref) ref.textContent = `${colIndexToLetter(colIdx)}${rowIdx + 1}`;

    if (colName && theadEl) {
      const headerRow = theadEl.querySelector('tr');
      const headers = headerRow ? Array.from(headerRow.querySelectorAll('th')) : [];
      colName.textContent = headers[colIdx]?.innerText?.trim() || '';
    }

    indicatorEl.classList.add('visible');
  }

  function attachCellListeners() {
    if (!tbodyEl || tbodyEl.dataset.acnBound === 'true') return;

    tbodyEl.addEventListener('click', function (event) {
      const td = event.target.closest('td');
      if (!td) return;

      const row = td.closest('tr');
      if (!row || row.classList.contains('detail-row')) return;

      const isInteractive = Boolean(
        event.target.closest('a, button, .btn, select, input, textarea, [contenteditable="true"]')
      );

      if (isInteractive) return;

      const rows = getDataRows();
      const cells = getCells(row);
      const rowIdx = rows.indexOf(row);
      const colIdx = cells.indexOf(td);

      if (rowIdx >= 0 && colIdx >= 0) {
        userEngaged = true;
        setActiveCell(rowIdx, colIdx);
      }
    });

    tbodyEl.dataset.acnBound = 'true';
  }

  function bindKeyboard() {
    if (keyboardBound) return;

    document.addEventListener('keydown', function (event) {
      const activeTag = document.activeElement?.tagName?.toLowerCase();
      if (['input', 'textarea', 'select'].includes(activeTag)) return;
      if (document.querySelector('.ie-cell.ie-editing, .modal-overlay.show, .modal.show, .swal2-container.swal2-shown')) return;
      if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') return;

      const navigationKeys = ['ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp', 'Tab', 'Enter', 'F2', 'Escape'];
      if (!navigationKeys.includes(event.key)) return;

      userEngaged = true;

      if (!activeCell || !document.body.contains(activeCell)) {
        rebind({ restore: true, noPulse: true, preventScroll: true });
      }

      if (!activeCell && event.key !== 'Enter') {
        autoSelectFirstCell();
      }

      switch (event.key) {
        case 'ArrowRight':
          event.preventDefault();
          moveCell(0, 1);
          break;
        case 'ArrowLeft':
          event.preventDefault();
          moveCell(0, -1);
          break;
        case 'ArrowDown':
          event.preventDefault();
          moveCell(1, 0);
          break;
        case 'ArrowUp':
          event.preventDefault();
          moveCell(-1, 0);
          break;
        case 'Tab':
          event.preventDefault();
          moveCell(0, event.shiftKey ? -1 : 1);
          break;
        case 'Enter':
        case 'F2':
          if (activeCell && activeCell.classList.contains('ie-cell')) {
            event.preventDefault();
            if (typeof window.ieActivateCell === 'function') {
              window.ieActivateCell(activeCell);
            } else {
              activeCell.click();
            }
          }
          break;
        case 'Escape':
          event.preventDefault();
          restoreActiveCell({ noPulse: true, preventScroll: true });
          break;
        default:
          break;
      }
    });

    keyboardBound = true;
  }

  function moveCell(deltaRow, deltaCol) {
    const rows = getDataRows();
    if (rows.length === 0) return;

    let nextRow = activeRow + deltaRow;
    nextRow = Math.max(0, Math.min(nextRow, rows.length - 1));

    if (nextRow === activeRow && deltaRow !== 0 && activeCell && document.body.contains(activeCell)) {
      scrollCellIntoView(activeCell);
      return;
    }

    const maxCol = getCells(rows[nextRow]).length - 1;
    let nextCol = activeCol + deltaCol;
    nextCol = Math.max(0, Math.min(nextCol, maxCol));

    if (nextCol === activeCol && deltaCol !== 0 && nextRow === activeRow && activeCell && document.body.contains(activeCell)) {
      scrollCellIntoView(activeCell);
      return;
    }

    setActiveCell(nextRow, nextCol);
  }

  function scrollCellIntoView(cell) {
    if (!cell) return;

    const wrapper = cell.closest('.table-wrapper, .table-responsive, .table-dokumen') || wrapperEl;
    if (!wrapper) return;

    // Use setTimeout to ensure DOM has updated before calculating positions
    setTimeout(function() {
      const cellRect = cell.getBoundingClientRect();
      const wrapperRect = wrapper.getBoundingClientRect();
      const table = cell.closest('table');
      const header = table?.querySelector('thead');
      const headerRect = header?.getBoundingClientRect();

      // Calculate sticky column widths (left and right)
      let leftStickyWidth = 0;
      let rightStickyWidth = 0;

      if (table) {
        // Get all sticky columns on the left
        const leftStickyCols = table.querySelectorAll('.col-checkbox, .col-no, .col-nomor_agenda');
        leftStickyCols.forEach(function(col) {
          const colRect = col.getBoundingClientRect();
          leftStickyWidth = Math.max(leftStickyWidth, colRect.right - wrapperRect.left);
        });

        // Get sticky column on the right
        const rightStickyCol = table.querySelector('.col-handler');
        if (rightStickyCol) {
          const colRect = rightStickyCol.getBoundingClientRect();
          rightStickyWidth = wrapperRect.right - colRect.left;
        }
      }

      // Add padding for better visibility
      const padding = 20;

      // Calculate visible area (excluding sticky columns)
      const visibleLeft = wrapperRect.left + leftStickyWidth + padding;
      const visibleRight = wrapperRect.right - rightStickyWidth - padding;

      // Check if cell is hidden or partially visible
      const cellLeft = cellRect.left;
      const cellRight = cellRect.right;

      let scrollAmount = 0;

      if (cellRight > visibleRight) {
        // Cell is cut off on the right
        scrollAmount = cellRight - visibleRight;
      } else if (cellLeft < visibleLeft) {
        // Cell is cut off on the left
        scrollAmount = cellLeft - visibleLeft;
      }

      if (scrollAmount !== 0) {
        wrapper.scrollBy({
          left: scrollAmount,
          behavior: 'smooth'
        });
      }

      // Vertical visibility must account for sticky table headers. Native
      // scrollIntoView can leave the active cell hidden underneath the header.
      const headerOverlap = headerRect
        ? Math.max(0, Math.min(headerRect.bottom, wrapperRect.bottom) - wrapperRect.top)
        : 0;
      const verticalPadding = 12;
      const visibleTop = wrapperRect.top + headerOverlap + verticalPadding;
      const visibleBottom = wrapperRect.bottom - verticalPadding;
      let verticalAmount = 0;

      if (cellRect.top < visibleTop) {
        verticalAmount = cellRect.top - visibleTop;
      } else if (cellRect.bottom > visibleBottom) {
        verticalAmount = cellRect.bottom - visibleBottom;
      }

      if (verticalAmount !== 0) {
        const canScrollWrapperY = wrapper.scrollHeight > wrapper.clientHeight + 1;
        if (canScrollWrapperY) {
          wrapper.scrollBy({
            top: verticalAmount,
            behavior: 'smooth'
          });
        } else {
          window.scrollBy({
            top: verticalAmount,
            behavior: 'smooth'
          });
        }
      }
    }, 10);
  }

  function colIndexToLetter(index) {
    let label = '';
    let n = index;
    do {
      label = String.fromCharCode(65 + (n % 26)) + label;
      n = Math.floor(n / 26) - 1;
    } while (n >= 0);
    return label;
  }

  function setupObserver() {
    const container = document.getElementById('documentTableContainer');
    if (!container || observer) return;

    observer = new MutationObserver(function (mutations) {
      const shouldIgnore = mutations.every(function (mutation) {
        const target = mutation.target instanceof Element ? mutation.target : mutation.target.parentElement;
        return Boolean(target && target.closest('.ie-editing, .ie-saving, .ie-overlay-popup, .ie-overlay-backdrop'));
      });

      if (shouldIgnore) return;
      scheduleRebind();
    });
    observer.observe(container, { childList: true, subtree: true });
  }

  function scheduleRebind() {
    invalidateRowsCache(); // tabel berubah -> cache baris harus dihitung ulang
    if (document.querySelector('.ie-editing, .ie-saving, .ie-overlay-popup, .ie-overlay-backdrop')) return;

    clearTimeout(rebindTimer);
    rebindTimer = setTimeout(function () {
      if (document.querySelector('.ie-editing, .ie-saving, .ie-overlay-popup, .ie-overlay-backdrop')) return;
      rebind({ restore: true, noPulse: true, preventScroll: true });
    }, 50);
  }

  function rebind(options = {}) {
    tableEl = findTable();
    if (!tableEl) return false;

    tbodyEl = tableEl.querySelector('tbody');
    theadEl = tableEl.querySelector('thead');
    wrapperEl = tableEl.closest('.table-wrapper, .table-responsive') || tableEl.parentElement;
    invalidateRowsCache(); // tbody mungkin berganti -> cache baris tidak valid lagi

    createIndicator();
    attachCellListeners();
    bindKeyboard();

    if (options.restore) {
      return restoreActiveCell(options);
    }

    return activeCell && document.body.contains(activeCell)
      ? restoreActiveCell({ noPulse: true, preventScroll: true })
      : autoSelectFirstCell({ noPulse: true, preventScroll: true });
  }

  function init() {
    if (!rebind({ restore: true, noPulse: true, preventScroll: true })) return;
    setupObserver();
  }

  function handleExternalRebind() {
    rebind({ restore: true, noPulse: true, preventScroll: true });
  }

  document.addEventListener(REBIND_EVENT, handleExternalRebind);
  window.addEventListener(REBIND_EVENT, handleExternalRebind);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }

  window.acnSetActive = setActiveCell;
  window.acnRefresh = function () {
    return rebind({ restore: true, noPulse: true, preventScroll: true });
  };
  window.acnClear = function () {
    restoreActiveCell({ noPulse: true, preventScroll: true });
  };
})();
</script>

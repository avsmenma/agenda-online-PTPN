{{--
  _activeCellNav.blade.php
  Spreadsheet-style active cell navigation untuk tabel daftar dokumen.

  Usage: @include('partials._activeCellNav', ['tableSelector' => '.data-table'])

  Parameter:
    $tableSelector (string) – CSS selector tabel target. Default: '.data-table'
--}}
@php
  $tableSelector = $tableSelector ?? '.data-table';
@endphp

<style>
  /* ===== Active Cell Navigation ===== */

  /* Cell position indicator (misal: Baris 3, Kolom 4) */
  .acn-indicator {
    display: none;
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
    display: flex;
    align-items: center;
    gap: 12px;
    user-select: none;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s ease;
  }

  .acn-indicator.visible {
    opacity: 1;
    pointer-events: auto;
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

  /* Active Cell highlight */
  td.acn-active {
    outline: 3px solid #083E40 !important;
    outline-offset: -2px !important;
    background-color: rgba(8, 62, 64, 0.08) !important;
    position: relative;
    z-index: 2;
    transition: background-color 0.15s ease;
  }

  /* Active row soft tint */
  tr.acn-active-row > td {
    background-color: rgba(136, 151, 23, 0.04);
  }

  /* Active col header tint (optional visual) */
  th.acn-active-col {
    background: linear-gradient(135deg, #0a5f52 0%, #083E40 100%) !important;
    box-shadow: inset 0 -3px 0 #889717;
  }

  /* Corner indicator on active cell (top-left triangle) */
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

  /* Pulse animation saat pertama aktif */
  @keyframes acn-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(8, 62, 64, 0.5); }
    70%  { box-shadow: 0 0 0 6px rgba(8, 62, 64, 0); }
    100% { box-shadow: 0 0 0 0 rgba(8, 62, 64, 0); }
  }

  td.acn-pulse {
    animation: acn-pulse 0.4s ease-out;
  }

  /* ===== Scrollbar wrapper untuk navigasi keyboard ===== */
  .table-wrapper, .table-responsive {
    scroll-behavior: smooth;
  }
</style>

<script>
(function () {
  'use strict';

  const TABLE_SELECTOR = '{{ $tableSelector }}';

  /** State */
  let activeCell   = null; // current <td>
  let activeRow    = 0;    // 0-based row index in tbody
  let activeCol    = 0;    // 0-based col index in tr

  /** Cache DOM references after DOM ready */
  let tableEl      = null;
  let tbodyEl      = null;
  let theadEl      = null;
  let wrapperEl    = null;
  let indicatorEl  = null;

  function init() {
    tableEl = document.querySelector(TABLE_SELECTOR);
    if (!tableEl) return; // tabel tidak ada di halaman ini

    tbodyEl   = tableEl.querySelector('tbody');
    theadEl   = tableEl.querySelector('thead');
    wrapperEl = tableEl.closest('.table-wrapper, .table-responsive') || tableEl.parentElement;

    createIndicator();
    attachCellListeners();
    attachKeyboardListener();

    // Auto-pilih cell pertama saat halaman dimuat (seperti spreadsheet)
    autoSelectFirstCell();

    console.log('✅ Active Cell Navigation initialized on', TABLE_SELECTOR);
  }

  /**
   * Otomatis pilih cell pertama dari baris pertama data.
   * Kolom yang dipilih = kolom ke-1 (index 1) agar skip kolom "No" / checkbox.
   * Jika tabel hanya punya 1 kolom, fallback ke kolom ke-0.
   */
  function autoSelectFirstCell() {
    // Tunggu sebentar agar layout/render selesai sepenuhnya
    setTimeout(function () {
      if (!tbodyEl) return;
      const rows = Array.from(tbodyEl.querySelectorAll('tr:not(.detail-row)'));
      if (rows.length === 0) return; // tabel kosong, tidak ada yang dipilih

      const firstRow = rows[0];
      const cols = Array.from(firstRow.querySelectorAll('td'));
      if (cols.length === 0) return;

      // Pilih kolom ke-1 (Nomor Agenda) jika ada, fallback ke kolom ke-0
      const startCol = cols.length > 1 ? 1 : 0;

      setActiveCell(0, startCol);
    }, 150); // 150ms cukup untuk render selesai
  }

  /** Buat elemen indikator posisi di bawah tabel */
  function createIndicator() {
    indicatorEl = document.createElement('div');
    indicatorEl.className = 'acn-indicator';
    indicatorEl.id = 'acnIndicator';
    indicatorEl.innerHTML = `
      <i class="fa-solid fa-table-cells" style="font-size:13px; opacity:0.8;"></i>
      <span class="acn-cell-ref" id="acnCellRef">-</span>
      <span id="acnColName" style="opacity:0.85; font-size:11px;"></span>
      <span class="acn-hint">
        <span class="acn-key">↑↓←→</span> navigasi &nbsp;
        <span class="acn-key">Esc</span> lepas
      </span>`;

    // Sisipkan tepat setelah wrapper tabel
    wrapperEl.insertAdjacentElement('afterend', indicatorEl);
    // Pastikan tampil
    indicatorEl.style.display = 'flex';
  }

  /** Pasang event click pada setiap <td> di tbody */
  function attachCellListeners() {
    if (!tbodyEl) return;

    // Event delegation - lebih efisien
    tbodyEl.addEventListener('click', function (e) {
      const td = e.target.closest('td');
      if (!td) return;

      const tr = td.closest('tr');
      if (!tr || tr.classList.contains('detail-row')) return;

      // Jangan aktifkan jika klik pada tombol/link interaktif
      const isInteractive =
        e.target.closest('a') ||
        e.target.closest('button') ||
        e.target.closest('.btn') ||
        e.target.closest('select') ||
        e.target.closest('input');

      if (isInteractive) return;

      const rows = Array.from(tbodyEl.querySelectorAll('tr:not(.detail-row)'));
      const cols = Array.from(tr.querySelectorAll('td'));

      let rIdx = rows.indexOf(tr);
      let cIdx = cols.indexOf(td);

      if (rIdx < 0 || cIdx < 0) return;

      setActiveCell(rIdx, cIdx);
    });
  }

  /** Set active cell berdasarkan index baris & kolom */
  function setActiveCell(rowIdx, colIdx) {
    const rows = Array.from(tbodyEl.querySelectorAll('tr:not(.detail-row)'));
    if (rowIdx < 0 || rowIdx >= rows.length) return;

    const tr = rows[rowIdx];
    const cols = Array.from(tr.querySelectorAll('td'));
    if (colIdx < 0 || colIdx >= cols.length) return;

    const td = cols[colIdx];

    // Hapus highlight lama
    clearHighlight();

    // Set baru
    activeCell = td;
    activeRow  = rowIdx;
    activeCol  = colIdx;

    // Highlight row
    tr.classList.add('acn-active-row');

    // Highlight cell
    td.classList.add('acn-active', 'acn-pulse');
    td.addEventListener('animationend', () => td.classList.remove('acn-pulse'), { once: true });

    // Highlight header kolom
    if (theadEl) {
      const thRow = theadEl.querySelector('tr');
      if (thRow) {
        const ths = Array.from(thRow.querySelectorAll('th'));
        ths.forEach(th => th.classList.remove('acn-active-col'));
        if (ths[colIdx]) ths[colIdx].classList.add('acn-active-col');
      }
    }

    // Update indikator
    updateIndicator(rowIdx, colIdx);

    // Scroll ke cell agar terlihat
    scrollCellIntoView(td);
  }

  /** Update teks pada indikator bawah */
  function updateIndicator(rowIdx, colIdx) {
    if (!indicatorEl) return;

    // Label kolom: A, B, C, ... Z, AA, ...
    const colLabel = colIndexToLetter(colIdx);
    const rowLabel = rowIdx + 1;

    const ref     = document.getElementById('acnCellRef');
    const colName = document.getElementById('acnColName');

    if (ref) ref.textContent = `${colLabel}${rowLabel}`;

    // Ambil nama kolom dari header
    if (colName && theadEl) {
      const thRow = theadEl.querySelector('tr');
      if (thRow) {
        const ths = Array.from(thRow.querySelectorAll('th'));
        colName.textContent = ths[colIdx]?.innerText?.trim() || '';
      }
    }

    indicatorEl.classList.add('visible');
  }

  /** Hapus semua highlight */
  function clearHighlight() {
    // Cell lama
    if (activeCell) {
      activeCell.classList.remove('acn-active', 'acn-pulse');
    }

    // Row lama
    tbodyEl.querySelectorAll('tr.acn-active-row').forEach(tr => tr.classList.remove('acn-active-row'));

    // Header lama
    if (theadEl) {
      theadEl.querySelectorAll('th.acn-active-col').forEach(th => th.classList.remove('acn-active-col'));
    }

    activeCell = null;
  }

  /** Keyboard navigation */
  function attachKeyboardListener() {
    document.addEventListener('keydown', function (e) {
      // Jangan aktifkan jika sedang mengetik di input/textarea/select
      const tag = document.activeElement?.tagName?.toLowerCase();
      if (['input', 'textarea', 'select'].includes(tag)) return;
      // Jangan aktifkan jika ada modal terbuka
      if (document.querySelector('.modal-overlay.show, .modal.show')) return;
      // Jangan aktifkan jika belum ada cell aktif dan bukan Enter
      if (!activeCell && e.key !== 'Enter') return;

      switch (e.key) {
        case 'ArrowRight':
          e.preventDefault();
          moveCell(0, +1);
          break;
        case 'ArrowLeft':
          e.preventDefault();
          moveCell(0, -1);
          break;
        case 'ArrowDown':
          e.preventDefault();
          moveCell(+1, 0);
          break;
        case 'ArrowUp':
          e.preventDefault();
          moveCell(-1, 0);
          break;
        case 'Escape':
          clearHighlight();
          if (indicatorEl) indicatorEl.classList.remove('visible');
          activeCell = null;
          activeRow  = 0;
          activeCol  = 0;
          break;
        case 'Tab':
          // Tab = gerak kanan, Shift+Tab = kiri (mirip Excel)
          e.preventDefault();
          moveCell(0, e.shiftKey ? -1 : +1);
          break;
        default:
          break;
      }
    });
  }

  /** Pindah cell secara relatif (deltaRow, deltaCol) */
  function moveCell(deltaRow, deltaCol) {
    const rows = Array.from(tbodyEl.querySelectorAll('tr:not(.detail-row)'));
    let newRow = activeRow + deltaRow;
    let newCol = activeCol + deltaCol;

    // Clamp row
    if (newRow < 0) newRow = 0;
    if (newRow >= rows.length) newRow = rows.length - 1;

    // Clamp col
    const maxCols = rows[newRow]?.querySelectorAll('td').length || 1;
    if (newCol < 0) newCol = 0;
    if (newCol >= maxCols) newCol = maxCols - 1;

    setActiveCell(newRow, newCol);
  }

  /** Scroll cell ke viewport */
  function scrollCellIntoView(td) {
    if (!td) return;
    td.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
  }

  /** Konversi index kolom ke label huruf (0→A, 1→B, 25→Z, 26→AA) */
  function colIndexToLetter(idx) {
    let label = '';
    let n = idx;
    do {
      label = String.fromCharCode(65 + (n % 26)) + label;
      n = Math.floor(n / 26) - 1;
    } while (n >= 0);
    return label;
  }

  // Init saat DOM siap
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose untuk keperluan lain
  window.acnSetActive = setActiveCell;
  window.acnClear     = function () {
    clearHighlight();
    if (indicatorEl) indicatorEl.classList.remove('visible');
  };

})();
</script>

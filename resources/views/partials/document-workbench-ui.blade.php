@once
  <style>
    .document-workbench-bar {
      align-items: center;
      display: flex;
      flex-wrap: wrap;
      gap: 0.55rem;
      justify-content: space-between;
      margin-bottom: 0.75rem;
    }

    .document-workbench-actions {
      align-items: center;
      display: inline-flex;
      flex-wrap: wrap;
      gap: 0.45rem;
    }

    .document-active-filter-badge {
      color: #64748b;
      font-size: 0.74rem;
      font-weight: 700;
      letter-spacing: 0;
      white-space: nowrap;
    }

    .document-active-filter-badge {
      align-items: center;
      background: #ecfeff;
      border: 1px solid #bae6fd;
      border-radius: 999px;
      color: #0369a1;
      display: none;
      gap: 0.35rem;
      min-height: 32px;
      padding: 0 0.7rem;
    }

    .document-active-filter-badge.is-visible {
      display: inline-flex;
    }

    body.document-density-compact #documentTableContainer .data-table th,
    body.document-density-compact #documentTableContainer .data-table td,
    body.document-density-compact #documentTableContainer table th,
    body.document-density-compact #documentTableContainer table td {
      font-size: 12.5px !important;
      line-height: 1.32 !important;
      padding: 7px 9px !important;
    }

    body.document-density-compact #documentTableContainer .data-table tbody tr,
    body.document-density-compact #documentTableContainer table tbody tr {
      min-height: 42px !important;
    }

    body.document-density-compact #documentTableContainer .data-table td {
      height: 42px !important;
    }

    body.document-density-compact #documentTableContainer td {
      vertical-align: middle !important;
    }

    body.document-density-compact #documentTableContainer td:nth-child(4),
    body.document-density-compact #documentTableContainer td:nth-child(5),
    body.document-density-compact #documentTableContainer td:nth-child(6),
    body.document-density-compact #documentTableContainer td:nth-child(7),
    body.document-density-compact #documentTableContainer td:nth-child(8),
    body.document-density-compact #documentTableContainer td:nth-child(9) {
      max-width: 260px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    body.document-density-compact #documentTableContainer td[data-field="uraian_spp"],
    body.document-density-compact #documentTableContainer td.uraian-spp,
    body.document-density-compact #documentTableContainer .uraian-spp-cell {
      display: -webkit-box;
      max-height: 42px;
      overflow: hidden;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
      white-space: normal !important;
    }

    body.document-density-compact #documentTableContainer td[data-field="nilai_rupiah"],
    body.document-density-compact #documentTableContainer td.col-nilai,
    body.document-density-compact #documentTableContainer td:nth-child(10) {
      font-variant-numeric: tabular-nums;
      text-align: right;
    }

    body.document-density-compact .owner-docs-table th,
    body.document-density-compact .owner-docs-table td {
      font-size: 12px;
      padding-bottom: 11px;
      padding-top: 11px;
    }

    body.document-density-compact .owner-docs-docname {
      display: -webkit-box;
      max-height: 38px;
      overflow: hidden;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
    }

    body.document-density-compact .owner-docs-payee {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .document-qv-active-row > td,
    .owner-docs-row.document-qv-active-row > td {
      background: #f0fdfa !important;
      border-left-color: transparent !important;
    }

    .document-qv-active-row > td:first-child,
    .owner-docs-row.document-qv-active-row > td:first-child {
      box-shadow: inset 3px 0 0 #0f766e;
    }

    .document-qv-panel {
      background: #ffffff;
      border: 1px solid #dbe5ee;
      border-radius: 16px;
      bottom: 18px;
      box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
      display: flex;
      flex-direction: column;
      max-width: calc(100vw - 32px);
      position: fixed;
      right: 18px;
      top: 88px;
      transform: translateX(calc(100% + 28px));
      transition: transform 180ms ease;
      width: 430px;
      z-index: 1065;
    }

    .document-qv-panel.is-open {
      transform: translateX(0);
    }

    .document-qv-head {
      align-items: flex-start;
      border-bottom: 1px solid #e8eef4;
      display: flex;
      gap: 0.75rem;
      justify-content: space-between;
      padding: 1rem 1rem 0.9rem;
    }

    .document-qv-kicker {
      color: #64748b;
      font-size: 0.72rem;
      font-weight: 800;
      letter-spacing: 0.04em;
      margin-bottom: 0.28rem;
      text-transform: uppercase;
    }

    .document-qv-title {
      color: #0f172a;
      font-size: 1.05rem;
      font-weight: 800;
      line-height: 1.25;
      margin: 0;
      word-break: break-word;
    }

    .document-qv-close {
      align-items: center;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      color: #475569;
      display: inline-flex;
      height: 34px;
      justify-content: center;
      width: 34px;
    }

    .document-qv-body {
      overflow: auto;
      padding: 1rem;
      scroll-behavior: smooth;
    }

    .document-qv-section {
      border-bottom: 1px solid #eef2f7;
      margin-bottom: 0.9rem;
      padding-bottom: 0.9rem;
    }

    .document-qv-section:last-child {
      border-bottom: 0;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .document-qv-section-title {
      color: #083E40;
      font-size: 0.76rem;
      font-weight: 800;
      letter-spacing: 0.03em;
      margin-bottom: 0.58rem;
      text-transform: uppercase;
    }

    .document-qv-grid {
      display: grid;
      gap: 0.55rem;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .document-qv-field {
      background: #f8fafc;
      border: 1px solid #eef2f7;
      border-radius: 10px;
      padding: 0.62rem 0.72rem;
      min-width: 0;
    }

    .document-qv-field.is-wide {
      grid-column: 1 / -1;
    }

    .document-qv-label {
      color: #64748b;
      display: block;
      font-size: 0.68rem;
      font-weight: 800;
      letter-spacing: 0.02em;
      margin-bottom: 0.28rem;
      text-transform: uppercase;
    }

    .document-qv-value {
      color: #0f172a;
      font-size: 0.9rem;
      font-weight: 650;
      line-height: 1.45;
      overflow-wrap: anywhere;
      white-space: pre-wrap;
    }

    .document-qv-money {
      color: #083E40;
      font-variant-numeric: tabular-nums;
      font-weight: 850;
    }

    .document-qv-footer {
      border-top: 1px solid #e8eef4;
      display: flex;
      gap: 0.55rem;
      justify-content: flex-end;
      padding: 0.85rem 1rem;
    }

    .document-qv-action {
      align-items: center;
      background: #083E40;
      border: 0;
      border-radius: 10px;
      color: #ffffff;
      display: inline-flex;
      font-size: 0.82rem;
      font-weight: 800;
      gap: 0.42rem;
      min-height: 38px;
      padding: 0 0.9rem;
      text-decoration: none;
    }

    .document-qv-action:hover {
      color: #ffffff;
    }

    @media (max-width: 780px) {
      .document-workbench-bar {
        align-items: stretch;
        flex-direction: column;
      }

      .document-workbench-actions {
        width: 100%;
      }

      .document-active-filter-badge {
        width: 100%;
      }

      .document-qv-panel {
        border-radius: 16px 16px 0 0;
        bottom: 0;
        left: 0;
        max-height: 76vh;
        max-width: none;
        right: 0;
        top: auto;
        transform: translateY(calc(100% + 24px));
        width: 100%;
      }

      .document-qv-panel.is-open {
        transform: translateY(0);
      }

      .document-qv-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <script>
    (function () {
      'use strict';

      let selectedRow = null;
      let quickPanel = null;
      let detailButton = null;

      const fieldAliases = {
        nomor: ['nomor agenda', 'nomor / uraian spp', 'nomor spp', 'no agenda'],
        uraian: ['uraian spp', 'uraian', 'nomor / uraian spp'],
        nilai: ['nilai', 'nilai rupiah', 'jumlah'],
        bagian: ['dari', 'bagian', 'unit', 'kebun'],
        statusDokumen: ['status dokumen', 'status'],
        statusBayar: ['status pembayaran', 'pembayaran'],
        pengurus: ['pengurus dokumen', 'pengurus', 'handler'],
        tanggalMasuk: ['tanggal masuk', 'tgl masuk'],
        durasi: ['durasi peran', 'durasi'],
        umur: ['umur dokumen', 'umur'],
        nomorSpp: ['nomor spp', 'spp']
      };

      function ready(callback) {
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
          callback();
        }
      }

      function isTypingTarget(target) {
        return !!target.closest('input, textarea, select, [contenteditable="true"], .ie-input, .ie-overlay-popup');
      }

      function normalize(text) {
        return (text || '').replace(/\s+/g, ' ').trim().toLowerCase();
      }

      function clean(text) {
        return (text || '').replace(/\s+/g, ' ').trim();
      }

      function closestElement(target, selector) {
        const element = target && target.nodeType === 1 ? target : target?.parentElement;
        return element ? element.closest(selector) : null;
      }

      function getSearchInput() {
        return document.querySelector('.role-filter-toolbar input[name="search"], .owner-docs-search input[name="search"], input[name="search"]');
      }

      function countActiveFilters(form) {
        if (!form) return 0;
        const ignored = new Set(['_token', 'page', 'per_page', 'columns[]', 'sort', 'order']);
        const defaults = new Set(['', 'all', 'semua']);
        let count = 0;
        Array.from(form.elements || []).forEach((field) => {
          if (!field.name || ignored.has(field.name) || field.disabled) return;
          if ((field.type === 'hidden' && field.name !== 'keterlambatan') || field.type === 'button' || field.type === 'submit') return;
          if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) return;
          const value = clean(field.value);
          if (!defaults.has(value.toLowerCase())) count += 1;
        });
        return count;
      }

      function ensureWorkbenchBar() {
        if (document.getElementById('documentWorkbenchBar')) return;

        const hasDocumentSurface = document.getElementById('documentTableContainer')
          || document.querySelector('.owner-docs-table-card')
          || document.querySelector('.owner-docs-table');
        if (!hasDocumentSurface) return;

        const bar = document.createElement('div');
        bar.id = 'documentWorkbenchBar';
        bar.className = 'document-workbench-bar';
        bar.innerHTML = [
          '<div class="document-workbench-actions">',
            '<span class="document-active-filter-badge" id="documentActiveFilterBadge"><i class="fa-solid fa-filter"></i><span>0 filter aktif</span></span>',
          '</div>'
        ].join('');

        const target = document.querySelector('.role-filter-toolbar')
          || document.querySelector('.owner-docs-table-card')
          || document.getElementById('documentTableContainer');

        if (target && target.parentNode) {
          target.parentNode.insertBefore(bar, target);
        }

        updateFilterBadge();
      }

      function initCompactDensity() {
        document.body.classList.add('document-density-compact');
        document.body.classList.remove('document-density-normal');
        localStorage.removeItem('agenda.documentDensity');
      }

      function updateFilterBadge() {
        const badge = document.getElementById('documentActiveFilterBadge');
        if (!badge) return;
        const form = document.getElementById('filterForm') || document.getElementById('ownerDocsFilterForm');
        const total = countActiveFilters(form);
        badge.classList.toggle('is-visible', total > 0);
        const label = badge.querySelector('span');
        if (label) label.textContent = total + ' filter aktif';
      }

      function ensureQuickPanel() {
        if (quickPanel) return quickPanel;
        quickPanel = document.createElement('aside');
        quickPanel.id = 'documentQuickViewPanel';
        quickPanel.className = 'document-qv-panel';
        quickPanel.setAttribute('aria-hidden', 'true');
        quickPanel.innerHTML = [
          '<div class="document-qv-head">',
            '<div>',
              '<div class="document-qv-kicker">Detail Cepat</div>',
              '<h2 class="document-qv-title" id="documentQvTitle">Pilih dokumen</h2>',
            '</div>',
            '<button type="button" class="document-qv-close" aria-label="Tutup detail cepat"><i class="fa-solid fa-xmark"></i></button>',
          '</div>',
          '<div class="document-qv-body" id="documentQvBody"></div>',
          '<div class="document-qv-footer">',
            '<button type="button" class="document-qv-action" id="documentQvDetailButton"><i class="fa-solid fa-up-right-from-square"></i> Detail lengkap</button>',
          '</div>'
        ].join('');
        document.body.appendChild(quickPanel);
        quickPanel.querySelector('.document-qv-close').addEventListener('click', closeQuickView);
        detailButton = quickPanel.querySelector('#documentQvDetailButton');
        detailButton.addEventListener('click', function () {
          const ownerId = detailButton.dataset.ownerId;
          const href = detailButton.dataset.href;
          if (ownerId && typeof window.openOwnerDocModal === 'function') {
            window.openOwnerDocModal(Number(ownerId));
            return;
          }
          if (href) window.location.href = href;
        });
        return quickPanel;
      }

      function closeQuickView() {
        if (!quickPanel) return;
        quickPanel.classList.remove('is-open');
        quickPanel.setAttribute('aria-hidden', 'true');
      }

      function isQuickViewOpen() {
        return !!quickPanel && quickPanel.classList.contains('is-open');
      }

      function scrollQuickViewByKey(key) {
        if (!isQuickViewOpen()) return false;
        const body = quickPanel.querySelector('#documentQvBody');
        if (!body) return false;

        const line = 74;
        const page = Math.max(180, Math.floor(body.clientHeight * 0.82));
        const maxTop = Math.max(0, body.scrollHeight - body.clientHeight);
        let nextTop = body.scrollTop;

        if (key === 'ArrowDown') nextTop += line;
        else if (key === 'ArrowUp') nextTop -= line;
        else if (key === 'PageDown') nextTop += page;
        else if (key === 'PageUp') nextTop -= page;
        else if (key === 'Home') nextTop = 0;
        else if (key === 'End') nextTop = maxTop;
        else return false;

        body.scrollTo({
          top: Math.max(0, Math.min(maxTop, nextTop)),
          behavior: 'smooth'
        });
        return true;
      }

      function headersFor(row) {
        const table = row.closest('table');
        if (!table) return [];
        return Array.from(table.querySelectorAll('thead th')).map((th) => clean(th.textContent));
      }

      function textForCell(cell) {
        if (!cell) return '';
        const editable = cell.querySelector('.ie-display');
        if (editable) return clean(editable.textContent);
        const select = cell.querySelector('select');
        if (select) return clean(select.options[select.selectedIndex]?.textContent || select.value);
        return clean(cell.textContent);
      }

      function findByAlias(fields, key) {
        const aliases = fieldAliases[key] || [];
        const direct = fields.find((field) => aliases.includes(normalize(field.label)));
        if (direct) return direct.value;
        const loose = fields.find((field) => aliases.some((alias) => normalize(field.label).includes(alias)));
        return loose ? loose.value : '';
      }

      function extractOwnerRow(row) {
        const idMatch = (row.getAttribute('onclick') || '').match(/openOwnerDocModal\((\d+)\)/);
        const handlerName = clean(row.querySelector('.owner-docs-handler-name')?.textContent);
        const handlerSub = clean(row.querySelector('.owner-docs-handler-sub')?.textContent);
        const handlerValue = [handlerName, handlerSub].filter(Boolean).join(' ');
        return {
          ownerId: idMatch ? idMatch[1] : '',
          title: clean(row.querySelector('.owner-docs-docno')?.textContent) || 'Dokumen',
          fields: [
            { label: 'Nomor Agenda', value: clean(row.querySelector('.owner-docs-docno')?.textContent), important: true },
            { label: 'Uraian SPP', value: clean(row.querySelector('.owner-docs-docname')?.textContent), wide: true },
            { label: 'Nilai', value: textForCell(row.querySelector('[data-label="Nilai"]')), money: true },
            { label: 'Dari', value: textForCell(row.querySelector('[data-label="Dari"]')) },
            { label: 'Dibayar Kepada', value: clean(Array.from(row.querySelectorAll('.owner-docs-payee')).find((el) => el.textContent.includes('Dibayar kepada'))?.textContent.replace('Dibayar kepada:', '')), wide: true },
            { label: 'Umur Dokumen', value: textForCell(row.querySelector('[data-label="Umur Dokumen"]')) },
            { label: 'Status Pembayaran', value: textForCell(row.querySelector('[data-label="Status Pembayaran"]')) },
            { label: 'Pengurus Dokumen', value: handlerValue },
            { label: 'Durasi Peran', value: textForCell(row.querySelector('[data-label="Durasi Peran"]')) },
            { label: 'Tanggal Masuk', value: clean(Array.from(row.querySelectorAll('.owner-docs-payee')).find((el) => el.textContent.includes('Tanggal masuk'))?.textContent.replace('Tanggal masuk:', '')) }
          ].filter((field) => field.value)
        };
      }

      function extractGenericRow(row) {
        const headers = headersFor(row);
        const fields = Array.from(row.children).map((cell, index) => ({
          label: headers[index] || cell.dataset.label || cell.dataset.field || ('Kolom ' + (index + 1)),
          value: textForCell(cell)
        })).filter((field) => field.value && field.value !== '-');

        const title = findByAlias(fields, 'nomor') || row.dataset.nomorAgenda || row.dataset.dokumenId || 'Dokumen';
        const detailLink = row.querySelector('a[href*="detail"], a[href*="edit"], a[href*="show"], a[href*="dokumen"]')?.getAttribute('href') || '';
        return { title, fields, detailLink };
      }

      function fieldMarkup(field) {
        const wide = field.wide || /uraian|nomor \/ uraian/i.test(field.label || '');
        const money = field.money || /nilai|rupiah|rp/i.test(field.label || '');
        return [
          '<div class="document-qv-field ', wide ? 'is-wide' : '', '">',
            '<span class="document-qv-label">', escapeHtml(field.label || '-'), '</span>',
            '<div class="document-qv-value ', money ? 'document-qv-money' : '', '">', escapeHtml(field.value || '-'), '</div>',
          '</div>'
        ].join('');
      }

      function renderQuickView(data) {
        const panel = ensureQuickPanel();
        panel.querySelector('#documentQvTitle').textContent = data.title || 'Dokumen';
        const body = panel.querySelector('#documentQvBody');

        const wanted = [
          'Nomor Agenda', 'Nomor SPP', 'Uraian SPP', 'Nilai', 'Nilai Rupiah',
          'Dari', 'Bagian', 'Dibayar Kepada', 'Status Dokumen', 'Status Pembayaran',
          'Pengurus Dokumen', 'Tanggal Masuk', 'Durasi Peran', 'Umur Dokumen'
        ];
        const used = new Set();
        const priority = [];
        wanted.forEach((label) => {
          const found = data.fields.find((field, index) => {
            if (used.has(index)) return false;
            return normalize(field.label).includes(normalize(label)) || normalize(label).includes(normalize(field.label));
          });
          if (found) {
            used.add(data.fields.indexOf(found));
            priority.push(found);
          }
        });
        const rest = data.fields.filter((_, index) => !used.has(index)).slice(0, 10);

        body.innerHTML = [
          '<div class="document-qv-section">',
            '<div class="document-qv-section-title">Informasi Utama</div>',
            '<div class="document-qv-grid">', priority.map(fieldMarkup).join(''), '</div>',
          '</div>',
          rest.length ? '<div class="document-qv-section"><div class="document-qv-section-title">Data Lainnya</div><div class="document-qv-grid">' + rest.map(fieldMarkup).join('') + '</div></div>' : ''
        ].join('');

        detailButton.dataset.ownerId = data.ownerId || '';
        detailButton.dataset.href = data.detailLink || '';
        detailButton.style.display = (data.ownerId || data.detailLink) ? 'inline-flex' : 'none';

        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
      }

      function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
      }

      function openQuickView(row) {
        if (!row) return;
        selectRow(row);
        const data = row.classList.contains('owner-docs-row') ? extractOwnerRow(row) : extractGenericRow(row);
        renderQuickView(data);
      }

      function selectRow(row) {
        if (!row) return;
        if (selectedRow && selectedRow !== row) selectedRow.classList.remove('document-qv-active-row');
        selectedRow = row;
        selectedRow.classList.add('document-qv-active-row');
      }

      function shouldIgnoreRowClick(target) {
        return !!closestElement(target, 'a, button, input, select, textarea, label, .dropdown-menu, .modal, .document-handler-select');
      }

      function bindEvents() {
        document.addEventListener('keydown', function (event) {
          if (isTypingTarget(event.target)) return;
          if (scrollQuickViewByKey(event.key)) {
            event.preventDefault();
            event.stopImmediatePropagation();
          }
        }, true);

        document.addEventListener('click', function (event) {
          const row = closestElement(event.target, '#documentTableContainer tbody tr, .owner-docs-row');
          if (!row || shouldIgnoreRowClick(event.target)) return;
          if (row.classList.contains('owner-docs-row')) {
            event.preventDefault();
            event.stopImmediatePropagation();
          }
          selectRow(row);
        }, true);

        // Double-click a row to open the quick detail panel.
        document.addEventListener('dblclick', function (event) {
          const row = closestElement(event.target, '#documentTableContainer tbody tr, .owner-docs-row');
          if (!row || shouldIgnoreRowClick(event.target)) return;
          event.preventDefault();
          event.stopImmediatePropagation();
          window.getSelection?.()?.removeAllRanges();
          openQuickView(row);
        }, true);

        document.addEventListener('keydown', function (event) {
          if ((event.ctrlKey || event.metaKey) && (event.key.toLowerCase() === 'k' || event.key.toLowerCase() === 'f') && !isTypingTarget(event.target)) {
            const input = getSearchInput();
            if (input) {
              event.preventDefault();
              input.focus();
              input.select();
            }
            return;
          }

          if (event.key === 'Escape' && !isTypingTarget(event.target)) {
            closeQuickView();
            return;
          }
        });

        document.addEventListener('change', function (event) {
          if (event.target.closest('#filterForm, #ownerDocsFilterForm')) updateFilterBadge();
        });

        document.querySelectorAll('.role-filter-toolbar input[name="search"], .owner-docs-search input[name="search"]').forEach((input) => {
          let timer = null;
          input.addEventListener('input', function () {
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
              const form = input.closest('form');
              if (!form || input.dataset.lastSubmittedValue === input.value) return;
              input.dataset.lastSubmittedValue = input.value;
              if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
              } else {
                form.submit();
              }
            }, 650);
          });
        });
      }

      ready(function () {
        ensureWorkbenchBar();
        ensureQuickPanel();
        initCompactDensity();
        bindEvents();
        updateFilterBadge();
      });

      window.openDocumentQuickViewFromRow = openQuickView;
      window.closeDocumentQuickView = closeQuickView;

      // Jalur data-driven (aditif) — untuk baris berbasis <div> (mis. Tabulator
      // operator) yang TIDAK bisa di-scrape sebagai <td>. Memakai renderer internal
      // yang SAMA (renderQuickView) dengan jalur DOM lama, hanya sumber datanya objek
      // alih-alih DOM. Bentuk `data`: { title, fields:[{label,value,wide?,money?}],
      // ownerId?, detailLink? } — identik keluaran extractGenericRow/extractOwnerRow.
      // Jalur DOM lama (openQuickView→selectRow+renderQuickView) tidak diubah.
      window.openDocumentQuickViewFromData = function (data) {
        if (!data || typeof data !== 'object') return;
        renderQuickView(data);
      };
    })();
  </script>
@endonce

@php
  $virtualTotal = method_exists($paginator, 'total') ? $paginator->total() : 0;
  $virtualPerPage = (int) ($chunkSize ?? 100);
  $virtualLastPage = max(1, (int) ceil(max(1, $virtualTotal) / max(1, $virtualPerPage)));
  $virtualContainer = $containerSelector ?? '#documentTableContainer';
  $virtualEnabled = $enabled ?? request('per_page') === 'all';
@endphp

@if($virtualEnabled && $virtualTotal > $virtualPerPage)
  <style>
    {{ $virtualContainer }} .table-responsive.virtual-scroll-active {
      max-height: min(72vh, 760px);
      overflow-y: auto;
      position: relative;
      border-top: 1px solid rgba(8, 62, 64, 0.08);
    }

    {{ $virtualContainer }} .table-responsive.virtual-scroll-active thead th {
      position: sticky;
      top: 0;
      z-index: 5;
    }

    .virtual-scroll-status {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 8px;
      background: rgba(8, 62, 64, 0.08);
      color: #083E40;
      font-size: 13px;
      font-weight: 600;
      margin-left: 8px;
    }

    .virtual-scroll-spacer td {
      padding: 0 !important;
      border: 0 !important;
      height: var(--virtual-spacer-height, 0px);
      background: transparent !important;
    }

    .pagination-enhanced-right,
    .pagination-wrapper .pagination {
      display: none !important;
    }

    .perpage-top-bar,
    .pagination-enhanced-wrapper,
    .pagination-wrapper,
    {{ $virtualContainer }} .dtable-toolbar-right {
      display: none !important;
    }
  </style>

  <script>
    (function() {
      const config = {
        containerSelector: @json($virtualContainer),
        total: {{ $virtualTotal }},
        perPage: {{ $virtualPerPage }},
        lastPage: {{ $virtualLastPage }},
        initialPage: {{ max(1, (int) request('page', 1)) }},
        rowHeight: 64
      };

      const cache = new Map();
      let activePage = config.initialPage;
      let loadingPage = null;
      let raf = null;

      function getColspan(table) {
        const headerRow = table.querySelector('thead tr');
        return headerRow ? Math.max(1, headerRow.children.length) : 20;
      }

      function normalizeRows(tbody) {
        return Array.from(tbody.children)
          .filter(row => !row.classList.contains('virtual-scroll-spacer'))
          .map(row => row.outerHTML)
          .join('');
      }

      function estimateRowHeight(tbody) {
        const rows = Array.from(tbody.querySelectorAll('tr.main-row, tr.clickable-row, tr:not(.detail-row)'))
          .filter(row => row.offsetHeight > 0)
          .slice(0, 20);
        if (!rows.length) return config.rowHeight;
        const average = rows.reduce((sum, row) => sum + row.offsetHeight, 0) / rows.length;
        return Math.max(48, Math.min(120, Math.round(average)));
      }

      function addStatus(container) {
        const summary = container.querySelector('.pagination-enhanced-summary, .perpage-top-bar span, .dtable-toolbar-subtitle, .pagination-wrapper .text-muted');
        if (!summary || summary.dataset.virtualStatusAdded === 'true') return;
        summary.dataset.virtualStatusAdded = 'true';
        const badge = document.createElement('span');
        badge.className = 'virtual-scroll-status';
        badge.innerHTML = '<i class="fa-solid fa-bolt"></i> Semua dokumen aktif, render bertahap';
        summary.insertAdjacentElement('afterend', badge);
      }

      function syncSelectors() {
        document.querySelectorAll('.perpage-top-select, .pagination-enhanced-select, .dtable-perpage-select, #perPageSelect')
          .forEach(select => { select.value = 'all'; });
      }

      function updateSummary(container, page) {
        const from = ((page - 1) * config.perPage) + 1;
        const to = Math.min(page * config.perPage, config.total);
        const text = 'Semua ' + config.total.toLocaleString('id-ID') + ' dokumen tersedia. Baris ' +
          from.toLocaleString('id-ID') + ' - ' + to.toLocaleString('id-ID') + ' sedang dirender virtual.';
        container.querySelectorAll('.pagination-enhanced-summary, .perpage-top-bar span, .dtable-toolbar-subtitle, .pagination-wrapper .text-muted')
          .forEach(el => { el.textContent = text; });
      }

      function renderPage(tbody, table, page, html) {
        const colspan = getColspan(table);
        const topRows = (page - 1) * config.perPage;
        const bottomRows = Math.max(0, config.total - (page * config.perPage));
        const topHeight = topRows * config.rowHeight;
        const bottomHeight = bottomRows * config.rowHeight;

        tbody.innerHTML =
          '<tr class="virtual-scroll-spacer" style="--virtual-spacer-height:' + topHeight + 'px"><td colspan="' + colspan + '"></td></tr>' +
          html +
          '<tr class="virtual-scroll-spacer" style="--virtual-spacer-height:' + bottomHeight + 'px"><td colspan="' + colspan + '"></td></tr>';
      }

      async function fetchPage(page) {
        if (cache.has(page)) return cache.get(page);
        if (loadingPage === page) return null;

        loadingPage = page;
        try {
          const url = new URL(window.location.href);
          url.searchParams.set('per_page', String(config.perPage));
          url.searchParams.set('page', String(page));
          url.searchParams.set('virtual_chunk', '1');

          const response = await fetch(url.toString(), {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'text/html'
            }
          });

          if (!response.ok) throw new Error('Gagal memuat halaman virtual ' + page);
          const html = await response.text();
          const doc = new DOMParser().parseFromString(html, 'text/html');
          const nextContainer = doc.querySelector(config.containerSelector);
          const nextBody = nextContainer ? nextContainer.querySelector('tbody') : doc.querySelector(config.containerSelector + ' tbody');
          const rows = nextBody ? normalizeRows(nextBody) : '';

          cache.set(page, rows);
          return rows;
        } finally {
          loadingPage = null;
        }
      }

      function wantedPage(scrollBox) {
        const firstVisibleRow = Math.max(0, Math.floor(scrollBox.scrollTop / config.rowHeight));
        return Math.min(config.lastPage, Math.max(1, Math.floor(firstVisibleRow / config.perPage) + 1));
      }

      function init() {
        const container = document.querySelector(config.containerSelector);
        if (!container) return;
        const scrollBox = container.querySelector('.table-responsive');
        const table = container.querySelector('table');
        const tbody = table ? table.querySelector('tbody') : null;
        if (!scrollBox || !table || !tbody) return;

        config.rowHeight = estimateRowHeight(tbody);
        cache.set(config.initialPage, normalizeRows(tbody));

        scrollBox.classList.add('virtual-scroll-active');
        addStatus(container);
        syncSelectors();
        updateSummary(container, config.initialPage);
        renderPage(tbody, table, config.initialPage, cache.get(config.initialPage));
        scrollBox.scrollTop = (config.initialPage - 1) * config.perPage * config.rowHeight;

        scrollBox.addEventListener('scroll', function() {
          if (raf) cancelAnimationFrame(raf);
          raf = requestAnimationFrame(async function() {
            const page = wantedPage(scrollBox);
            if (page === activePage) return;
            activePage = page;

            if (cache.has(page)) {
              renderPage(tbody, table, page, cache.get(page));
              updateSummary(container, page);
              return;
            }

            const previousHtml = tbody.innerHTML;
            try {
              const rows = await fetchPage(page);
              if (rows !== null) {
                renderPage(tbody, table, page, rows);
                updateSummary(container, page);
              }
            } catch (error) {
              console.error(error);
              tbody.innerHTML = previousHtml;
            }
          });
        }, { passive: true });
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    })();
  </script>
@endif

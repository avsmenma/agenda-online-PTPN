@php
  $virtualTotal = method_exists($paginator, 'total') ? $paginator->total() : 0;
  $configuredChunkSize = (int) ($chunkSize ?? 100);
  $virtualPerPage = max(1, $configuredChunkSize);
  $virtualLastPage = max(1, (int) ceil(max(1, $virtualTotal) / max(1, $virtualPerPage)));
  $virtualContainer = $containerSelector ?? '#documentTableContainer';
  $virtualEnabled = ($enabled ?? request('per_page') === 'all') && $virtualTotal > $virtualPerPage;
  $hidePaginationUi = $hidePaginationUi ?? true;
@endphp

@if($virtualEnabled)
  <style>
    {{ $virtualContainer }} .table-responsive.virtual-scroll-active {
      /* Isi tinggi layar: tabel mengisi ruang kosong sampai dekat bawah viewport,
         bukan dibatasi tinggi tetap (mencegah area kosong di monitor tinggi). */
      max-height: calc(100vh - 200px);
      min-height: 320px;
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

    .virtual-scroll-loader td {
      padding: 14px 12px !important;
      text-align: center !important;
      font-size: 13px;
      font-weight: 600;
      color: #083E40;
      background: rgba(8, 62, 64, 0.04) !important;
    }

    .virtual-scroll-loader.has-error td {
      color: #b02a37;
      background: rgba(220, 53, 69, 0.06) !important;
      cursor: pointer;
    }

    @if($hidePaginationUi)
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
    @endif
  </style>

  <script>
    (function() {
      const config = {
        containerSelector: @json($virtualContainer),
        total: {{ $virtualTotal }},
        perPage: {{ $virtualPerPage }},
        lastPage: {{ $virtualLastPage }},
        initialPage: {{ max(1, (int) request('page', 1)) }},
        // Mulai memuat chunk berikutnya sebelum user benar-benar mentok di bawah.
        prefetchPx: 600
      };

      let nextPage = config.initialPage + 1;
      let loading = false;

      function getParts() {
        const container = document.querySelector(config.containerSelector);
        if (!container) return null;
        const scrollBox = container.querySelector('.table-responsive');
        const table = container.querySelector('table');
        const tbody = table ? table.querySelector('tbody') : null;
        if (!scrollBox || !table || !tbody) return null;
        return { container, scrollBox, table, tbody };
      }

      function getColspan(table) {
        const headerRow = table.querySelector('thead tr');
        return headerRow ? Math.max(1, headerRow.children.length) : 20;
      }

      function addStatus(container) {
        const summary = container.querySelector('.pagination-enhanced-summary, .perpage-top-bar span, .dtable-toolbar-subtitle, .pagination-wrapper .text-muted');
        if (!summary || summary.dataset.virtualStatusAdded === 'true') return;
        summary.dataset.virtualStatusAdded = 'true';
        const badge = document.createElement('span');
        badge.className = 'virtual-scroll-status';
        badge.innerHTML = '<i class="fa-solid fa-bolt"></i> Dokumen dimuat bertahap saat scroll';
        summary.insertAdjacentElement('afterend', badge);
      }

      function syncSelectors() {
        document.querySelectorAll('.perpage-top-select, .pagination-enhanced-select, .dtable-perpage-select, #perPageSelect')
          .forEach(select => { select.value = 'all'; });
      }

      function updateSummary(container) {
        const loaded = Math.min(config.total, (nextPage - 1) * config.perPage);
        const text = loaded >= config.total
          ? 'Semua ' + config.total.toLocaleString('id-ID') + ' dokumen telah dimuat.'
          : loaded.toLocaleString('id-ID') + ' dari ' + config.total.toLocaleString('id-ID') +
            ' dokumen dimuat. Scroll ke bawah untuk memuat berikutnya.';
        container.querySelectorAll('.pagination-enhanced-summary, .perpage-top-bar span, .dtable-toolbar-subtitle, .pagination-wrapper .text-muted')
          .forEach(el => { el.textContent = text; });
      }

      function removeLoader(tbody) {
        tbody.querySelectorAll('tr.virtual-scroll-loader').forEach(row => row.remove());
      }

      function showLoader(tbody, table, message, isError) {
        removeLoader(tbody);
        const row = document.createElement('tr');
        row.className = 'virtual-scroll-loader' + (isError ? ' has-error' : '');
        row.innerHTML = '<td colspan="' + getColspan(table) + '">' + message + '</td>';
        if (isError) {
          row.addEventListener('click', function() {
            const parts = getParts();
            if (parts) loadNextChunk(parts.scrollBox, true);
          });
        }
        tbody.appendChild(row);
      }

      async function fetchChunk(page) {
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

        if (!response.ok) throw new Error('Gagal memuat dokumen halaman ' + page);
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextBody = doc.querySelector(config.containerSelector + ' tbody') || doc.querySelector('tbody');
        return nextBody ? nextBody.innerHTML : '';
      }

      function nearBottom(scrollBox) {
        return scrollBox.scrollTop + scrollBox.clientHeight >= scrollBox.scrollHeight - config.prefetchPx;
      }

      async function loadNextChunk(scrollBox, force) {
        if (loading || nextPage > config.lastPage) return;
        if (!force && !nearBottom(scrollBox)) return;

        loading = true;
        const parts = getParts();
        if (!parts) { loading = false; return; }

        showLoader(parts.tbody, parts.table, '<i class="fa-solid fa-spinner fa-spin"></i> Memuat dokumen berikutnya...', false);
        try {
          const rowsHtml = await fetchChunk(nextPage);

          // Ambil ulang referensi DOM: isi kontainer bisa saja diganti (Refresh)
          // selama fetch berjalan — jangan menulis ke elemen yang sudah lepas.
          const fresh = getParts();
          if (!fresh || fresh.scrollBox.dataset.virtualScrollBound !== 'true') return;

          removeLoader(fresh.tbody);
          fresh.tbody.insertAdjacentHTML('beforeend', rowsHtml);
          nextPage++;
          updateSummary(fresh.container);

          // Beri tahu partial lain (sticky cells, active-cell nav) ada baris baru.
          document.dispatchEvent(new CustomEvent('virtual-rows-appended'));
          window.dispatchEvent(new CustomEvent('virtual-rows-appended'));
          if (typeof window.syncDocumentStickyOffsets === 'function') {
            window.syncDocumentStickyOffsets();
          }

          // Bila viewport masih dekat dasar (mis. layar sangat tinggi),
          // langsung muat chunk berikutnya tanpa menunggu scroll baru.
          requestAnimationFrame(function() {
            loadNextChunk(fresh.scrollBox, false);
          });
        } catch (error) {
          console.error(error);
          const fresh = getParts();
          if (fresh) {
            showLoader(fresh.tbody, fresh.table,
              '<i class="fa-solid fa-triangle-exclamation"></i> Gagal memuat dokumen berikutnya. Klik di sini untuk mencoba lagi.', true);
          }
        } finally {
          loading = false;
        }
      }

      function init() {
        const parts = getParts();
        if (!parts) return;
        const { container, scrollBox } = parts;
        if (scrollBox.dataset.virtualScrollBound === 'true') return;
        scrollBox.dataset.virtualScrollBound = 'true';

        scrollBox.classList.add('virtual-scroll-active');
        addStatus(container);
        syncSelectors();
        updateSummary(container);

        scrollBox.addEventListener('scroll', function() {
          loadNextChunk(scrollBox, false);
        }, { passive: true });

        // Layar sangat tinggi / chunk kecil: pastikan viewport terisi.
        loadNextChunk(scrollBox, false);
      }

      function reinitAfterRefresh() {
        const parts = getParts();
        if (parts && parts.scrollBox.dataset.virtualScrollBound !== 'true') {
          // Isi kontainer diganti dari server (kembali ke halaman awal) — reset state.
          nextPage = config.initialPage + 1;
          init();
        }
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
      window.addEventListener('document-table-refreshed', reinitAfterRefresh);
    })();
  </script>
@endif

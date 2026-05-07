<style>
  body:has(#documentTableContainer) .content h2,
  body:has(#documentTableContainer) .main-content h2,
  body:has(#documentTableContainer) main h2 {
    font-size: clamp(1.45rem, 1.55vw, 1.85rem) !important;
    line-height: 1.12 !important;
    margin-bottom: 0.7rem !important;
    letter-spacing: 0 !important;
  }

  .vstat-grid {
    gap: 0.65rem !important;
    margin-bottom: 0.65rem !important;
  }

  .vstat-card {
    min-height: 0 !important;
    padding: 0.78rem 1rem !important;
    border-radius: 12px !important;
    box-shadow: 0 5px 18px rgba(15, 23, 42, 0.07) !important;
  }

  .vstat-label {
    font-size: 0.58rem !important;
    line-height: 1.25 !important;
    letter-spacing: 0.035em !important;
    margin-bottom: 0.12rem !important;
  }

  .vstat-value {
    font-size: clamp(1.28rem, 1.1vw, 1.55rem) !important;
    line-height: 1 !important;
    letter-spacing: 0 !important;
    margin-bottom: 0.08rem !important;
  }

  .vstat-sub {
    font-size: 0.68rem !important;
    line-height: 1.25 !important;
  }

  .vstat-icon {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    font-size: 0.78rem !important;
    border-radius: 50% !important;
  }

  .vdeadline-card {
    min-height: 0 !important;
    padding: 0.58rem 0.9rem !important;
    gap: 0.6rem !important;
    border-radius: 12px !important;
    box-shadow: 0 5px 18px rgba(15, 23, 42, 0.06) !important;
  }

  .vdeadline-icon {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    border-radius: 8px !important;
    font-size: 0.76rem !important;
  }

  .vdeadline-label {
    font-size: 0.66rem !important;
    line-height: 1.2 !important;
    margin-bottom: 0.05rem !important;
  }

  .vdeadline-value {
    font-size: 1.05rem !important;
    line-height: 1 !important;
  }

  .vdeadline-desc {
    font-size: 0.62rem !important;
    line-height: 1.2 !important;
    margin-top: 0.04rem !important;
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
    padding: 0.9rem 1rem !important;
    border-radius: 14px !important;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06) !important;
  }

  #documentTableContainer .dtable-toolbar {
    padding: 0.45rem 0.7rem 0.6rem !important;
    margin-bottom: 0.55rem !important;
    gap: 0.65rem !important;
  }

  #documentTableContainer .dtable-toolbar-left {
    gap: 0.5rem !important;
  }

  #documentTableContainer .dtable-toolbar-icon {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    border-radius: 8px !important;
    font-size: 0.78rem !important;
  }

  #documentTableContainer .dtable-toolbar-title {
    font-size: 0.98rem !important;
    line-height: 1.15 !important;
    margin-bottom: 0.1rem !important;
  }

  #documentTableContainer .dtable-toolbar-subtitle {
    font-size: 0.68rem !important;
    line-height: 1.2 !important;
  }

  #documentTableContainer .virtual-table-badge {
    margin-top: 0.28rem !important;
    padding: 0.32rem 0.55rem !important;
    font-size: 0.72rem !important;
    border-radius: 7px !important;
  }

  #documentTableContainer .table-container,
  #documentTableContainer .table-responsive {
    margin-top: 0.55rem !important;
  }

  #documentTableContainer table,
  #documentTableContainer .table-enhanced {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: 1px solid #cbd5e1 !important;
  }

  #documentTableContainer table thead th,
  #documentTableContainer table thead tr th {
    padding: 0.65rem 0.65rem !important;
    font-size: 0.72rem !important;
    border-right: 1px solid rgba(255, 255, 255, 0.14) !important;
    border-bottom: 2px solid #0f766e !important;
    box-shadow: inset 0 -1px 0 rgba(15, 23, 42, 0.2) !important;
  }

  #documentTableContainer table thead th:last-child,
  #documentTableContainer table thead tr th:last-child {
    border-right: 0 !important;
  }

  #documentTableContainer table tbody td,
  #documentTableContainer .table-enhanced tbody td {
    border-right: 1px solid #d7dee8 !important;
    border-bottom: 1px solid #d7dee8 !important;
    box-shadow: inset 0 -1px 0 rgba(15, 23, 42, 0.035) !important;
  }

  #documentTableContainer table tbody td:last-child,
  #documentTableContainer .table-enhanced tbody td:last-child {
    border-right: 0 !important;
  }

  #documentTableContainer table tbody tr:nth-child(even) td,
  #documentTableContainer .table-enhanced tbody tr:nth-child(even) td {
    background-color: #f8fafc !important;
  }

  #documentTableContainer table tbody tr:hover td,
  #documentTableContainer .table-enhanced tbody tr:hover td {
    background-color: #eef7ff !important;
    border-bottom-color: #b7c8da !important;
  }

  #documentTableContainer .table-enhanced th.col-uraian,
  #documentTableContainer .table-enhanced td.col-uraian,
  #documentTableContainer .table-enhanced th.col-uraian_spp,
  #documentTableContainer .table-enhanced td.col-uraian_spp,
  #documentTableContainer .table-enhanced th[class*="uraian"],
  #documentTableContainer .table-enhanced td[class*="uraian"],
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

  #documentTableContainer .table-enhanced th.col-uraian,
  #documentTableContainer .table-enhanced th.col-uraian_spp,
  #documentTableContainer .table-enhanced th[class*="uraian"] {
    text-align: center !important;
  }

  #documentTableContainer .table-enhanced td.col-uraian,
  #documentTableContainer .table-enhanced td.col-uraian_spp,
  #documentTableContainer .table-enhanced td[class*="uraian"],
  #documentTableContainer table td[class*="uraian"] {
    text-align: left !important;
  }

  #documentTableContainer .table-enhanced td.col-uraian > *,
  #documentTableContainer .table-enhanced td.col-uraian_spp > *,
  #documentTableContainer .table-enhanced td[class*="uraian"] > *,
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

  body.document-table-only-fullscreen .sidebar,
  body.document-table-only-fullscreen .secondary-sidebar,
  body.document-table-only-fullscreen .navbar,
  body.document-table-only-fullscreen .topbar,
  body.document-table-only-fullscreen header,
  body.document-table-only-fullscreen footer,
  body.document-table-only-fullscreen .vstat-grid,
  body.document-table-only-fullscreen .search-box,
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

  body.document-table-only-fullscreen .content > :not(#documentTableContainer):not(style):not(script) {
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
    overflow: auto !important;
    border-top: 0 !important;
    border-radius: 0 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer table {
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    margin-bottom: 0 !important;
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
    z-index: 80 !important;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%) !important;
    background-clip: padding-box !important;
    border-left: 0 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.16) !important;
    border-bottom: 2px solid #0f766e !important;
    box-shadow: none !important;
    transform: translateZ(0);
  }

  body.document-table-only-fullscreen #documentTableContainer table thead th:last-child,
  body.document-table-only-fullscreen #documentTableContainer table thead tr th:last-child {
    border-right: 0 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer thead,
  body.document-table-only-fullscreen #documentTableContainer table thead {
    position: sticky !important;
    top: 0 !important;
    z-index: 79 !important;
    background: #083E40 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer thead::after,
  body.document-table-only-fullscreen #documentTableContainer .col-checkbox::after {
    content: none !important;
    display: none !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody tr,
  body.document-table-only-fullscreen #documentTableContainer tbody td {
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
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced th,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced td {
    border-left: 0 !important;
    border-image: none !important;
    outline-color: transparent !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody td {
    border-right: 1px solid #d7dee8 !important;
    border-bottom: 1px solid #d7dee8 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody td:last-child {
    border-right: 0 !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody tr:nth-child(even) td {
    background-color: #f8fafc !important;
  }

  body.document-table-only-fullscreen #documentTableContainer tbody tr:hover td {
    background-color: #eef7ff !important;
    border-bottom-color: #b7c8da !important;
  }

  body.document-table-only-fullscreen #documentTableContainer .table-enhanced th.col-uraian,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced td.col-uraian,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced th.col-uraian_spp,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced td.col-uraian_spp,
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced th[class*="uraian"],
  body.document-table-only-fullscreen #documentTableContainer .table-enhanced td[class*="uraian"],
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
    height: calc(100vh - 20px) !important;
    max-height: none !important;
    overflow: auto !important;
    margin-top: 0 !important;
    border-top: 0 !important;
    border-radius: 0 !important;
  }

  body.is-fullscreen #documentTableContainer table {
    border-collapse: collapse !important;
    border-spacing: 0 !important;
  }

  body.is-fullscreen #documentTableContainer thead th,
  body.is-fullscreen #documentTableContainer table thead th,
  body.is-fullscreen #documentTableContainer table thead tr th {
    position: sticky !important;
    top: 0 !important;
    z-index: 80 !important;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%) !important;
    background-clip: padding-box !important;
    border-left: 0 !important;
    border-right: 0 !important;
    box-shadow: none !important;
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

  body.is-fullscreen #documentTableContainer th,
  body.is-fullscreen #documentTableContainer td,
  body.is-fullscreen #documentTableContainer .table-enhanced th,
  body.is-fullscreen #documentTableContainer .table-enhanced td {
    border-left: 0 !important;
    border-right: 0 !important;
    border-image: none !important;
  }

  @media (max-width: 1400px) {
    .vstat-grid {
      gap: 0.55rem !important;
    }

    .vstat-card {
      padding: 0.68rem 0.85rem !important;
    }

    .vstat-value {
      font-size: 1.22rem !important;
    }

    .vstat-icon {
      width: 30px !important;
      height: 30px !important;
      min-width: 30px !important;
      font-size: 0.7rem !important;
    }

    .vdeadline-card {
      padding: 0.52rem 0.75rem !important;
      gap: 0.5rem !important;
    }

    .vdeadline-icon {
      width: 28px !important;
      height: 28px !important;
      min-width: 28px !important;
      font-size: 0.68rem !important;
    }

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
      padding: 0.75rem 0.85rem !important;
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

    #documentTableContainer .table-enhanced th.col-uraian,
    #documentTableContainer .table-enhanced td.col-uraian,
    #documentTableContainer .table-enhanced th.col-uraian_spp,
    #documentTableContainer .table-enhanced td.col-uraian_spp,
    #documentTableContainer .table-enhanced th[class*="uraian"],
    #documentTableContainer .table-enhanced td[class*="uraian"],
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
    html:fullscreen body:has(#documentTableContainer) .vstat-grid,
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

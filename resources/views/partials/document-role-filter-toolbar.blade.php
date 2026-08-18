@php
  $filterRouteName = $filterRouteName ?? 'documents.verifikasi.index';
  $filterStatusOptions = $filterStatusOptions ?? ['' => 'Semua Status'];
  $filterDariOptions = $filterDariOptions ?? [];
  $formatFilterMoney = static function ($value): string {
      $digits = preg_replace('/[^0-9]/', '', (string) $value);
      return $digits === '' ? '' : number_format((int) $digits, 0, ',', '.');
  };
  $resetQuery = [];
  if (request('per_page')) {
      $resetQuery['per_page'] = request('per_page');
  }
  if (request('columns')) {
      $resetQuery['columns'] = request('columns');
  }

  // Deteksi filter lanjutan aktif → panel dibuka & badge dimunculkan saat load.
  $advancedKeys = ['filter_dari', 'filter_tanggal_masuk', 'filter_nilai_min', 'filter_nilai_max'];
  $advancedCount = 0;
  foreach ($advancedKeys as $k) {
      $v = request($k);
      if ($v !== null && $v !== '') {
          $advancedCount++;
      }
  }
  $advancedActive = $advancedCount > 0;
@endphp

@once
  <style>
    /* ===== Compact toolbar (redesign) — hanya menata tools di atas tabel ===== */
    .role-filter-toolbar {
      --rft-line: #e6ecf3;
      --rft-ink: #1d2b3a;
      --rft-active: #e9f1fb;
      --rft-active-ink: #2b6fb8;
      background: #fff;
      border: 1px solid var(--rft-line);
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
      margin-bottom: 1rem;
      overflow: hidden;
    }

    .role-filter-toolbar .rft-main {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 11px 13px;
    }

    .role-filter-toolbar .rft-search {
      position: relative;
      flex: 1;
      min-width: 160px;
      max-width: 520px;
      display: flex;
      align-items: center;
    }
    .role-filter-toolbar .rft-search > i {
      position: absolute;
      left: 14px;
      color: #9aa7b6;
      font-size: 15px;
      pointer-events: none;
    }
    .role-filter-toolbar .rft-search input {
      width: 100%;
      height: 42px;
      border: 1px solid var(--rft-line);
      border-radius: 11px;
      background: #f8fafc;
      padding: 0 14px 0 40px;
      font-family: inherit;
      font-size: 14px;
      color: var(--rft-ink);
      transition: .15s;
    }
    .role-filter-toolbar .rft-search input::placeholder { color: #9aa7b6; }
    .role-filter-toolbar .rft-search input:focus {
      outline: none;
      border-color: #9cc4e6;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(43, 140, 196, .12);
    }

    .role-filter-toolbar .rft-select { position: relative; flex-shrink: 0; }
    .role-filter-toolbar .rft-select select {
      appearance: none;
      -webkit-appearance: none;
      height: 42px;
      width: 100%;
      border: 1px solid var(--rft-line);
      border-radius: 11px;
      background: #f8fafc;
      padding: 0 38px 0 14px;
      font-family: inherit;
      font-size: 14px;
      color: var(--rft-ink);
      cursor: pointer;
      font-weight: 500;
    }
    .role-filter-toolbar .rft-select select:focus { outline: none; border-color: #9cc4e6; background: #fff; }
    .role-filter-toolbar .rft-select::after {
      content: "";
      position: absolute;
      right: 14px;
      top: 50%;
      width: 8px;
      height: 8px;
      border-right: 2px solid #8595a6;
      border-bottom: 2px solid #8595a6;
      transform: translateY(-65%) rotate(45deg);
      pointer-events: none;
    }

    .role-filter-toolbar .rft-filter-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      height: 42px;
      padding: 0 16px;
      border-radius: 11px;
      border: 1px solid var(--rft-line);
      background: #f8fafc;
      color: #3a4a5b;
      font-family: inherit;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      flex-shrink: 0;
      transition: .15s;
    }
    .role-filter-toolbar .rft-filter-btn:hover { background: #eef3f8; border-color: #d5dee8; }
    .role-filter-toolbar .rft-filter-btn.open { background: var(--rft-active); border-color: #bcd6f0; color: var(--rft-active-ink); }
    .role-filter-toolbar .rft-filter-btn .rft-chev {
      width: 9px;
      height: 9px;
      border-right: 2px solid currentColor;
      border-bottom: 2px solid currentColor;
      transform: rotate(45deg);
      transition: .2s;
      margin-left: 2px;
    }
    .role-filter-toolbar .rft-filter-btn.open .rft-chev { transform: rotate(-135deg); }
    .role-filter-toolbar .rft-count {
      min-width: 19px;
      height: 19px;
      padding: 0 5px;
      border-radius: 10px;
      background: #2b8cc4;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      display: none;
      place-items: center;
      line-height: 1;
    }
    .role-filter-toolbar .rft-count.show { display: grid; }

    .role-filter-toolbar .rft-spacer { flex: 1; }

    .role-filter-toolbar .rft-actions { display: flex; align-items: center; gap: 7px; flex-shrink: 0; }
    .role-filter-toolbar .rft-iconbtn {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      border: none;
      cursor: pointer;
      display: grid;
      place-items: center;
      color: #fff;
      transition: .15s;
      position: relative;
      font-size: 17px;
    }
    .role-filter-toolbar .rft-iconbtn.rft-refresh { background: linear-gradient(135deg, #2b8cc4, #2178a8); }
    .role-filter-toolbar .rft-iconbtn.rft-cols { background: linear-gradient(135deg, #3aa050, #2f8c44); }
    .role-filter-toolbar .rft-iconbtn.rft-full { background: linear-gradient(135deg, #155b63, #0f4750); }
    .role-filter-toolbar .rft-iconbtn:hover { filter: brightness(1.07); transform: translateY(-1px); }
    .role-filter-toolbar .rft-iconbtn:active { transform: translateY(0); }
    .role-filter-toolbar .rft-iconbtn::after {
      content: attr(data-tip);
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      white-space: nowrap;
      background: #1d2b3a;
      color: #fff;
      font-size: 11.5px;
      font-weight: 500;
      padding: 5px 9px;
      border-radius: 7px;
      opacity: 0;
      pointer-events: none;
      transition: .15s;
      transform: translateY(-3px);
      z-index: 20;
    }
    .role-filter-toolbar .rft-iconbtn:hover::after { opacity: 1; transform: translateY(0); }

    /* collapsible advanced filter panel */
    .role-filter-toolbar .rft-panel {
      max-height: 0;
      overflow: hidden;
      transition: max-height .28s ease;
      border-top: 1px solid transparent;
    }
    .role-filter-toolbar .rft-panel.open { max-height: 360px; border-top: 1px solid var(--rft-line); }
    .role-filter-toolbar .rft-grid {
      display: grid;
      grid-template-columns: 1.2fr 1.4fr .9fr .9fr auto auto;
      gap: 14px;
      align-items: end;
      padding: 16px 13px;
    }
    .role-filter-toolbar .rft-field { min-width: 0; }
    .role-filter-toolbar .rft-field > label {
      display: block;
      font-size: 10.5px;
      letter-spacing: .08em;
      color: #9aa7b6;
      font-weight: 600;
      margin-bottom: 6px;
      text-transform: uppercase;
    }
    .role-filter-toolbar .rft-field input,
    .role-filter-toolbar .rft-field .rft-select select {
      width: 100%;
      height: 42px;
      border: 1px solid var(--rft-line);
      border-radius: 11px;
      background: #f8fafc;
      padding: 0 14px;
      font-family: inherit;
      font-size: 14px;
      color: var(--rft-ink);
    }
    .role-filter-toolbar .rft-field .rft-select { display: block; }
    .role-filter-toolbar .rft-field .rft-select select { padding-right: 38px; }
    .role-filter-toolbar .rft-field input:focus { outline: none; border-color: #9cc4e6; background: #fff; }

    .role-filter-toolbar .rft-btn {
      height: 42px;
      padding: 0 22px;
      border-radius: 11px;
      border: none;
      cursor: pointer;
      font-family: inherit;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: .15s;
      white-space: nowrap;
      text-decoration: none;
    }
    .role-filter-toolbar .rft-btn-apply { background: linear-gradient(135deg, #155b63, #0f4750); color: #fff; }
    .role-filter-toolbar .rft-btn-apply:hover { filter: brightness(1.08); color: #fff; }
    .role-filter-toolbar .rft-btn-reset { background: #fff; border: 1px solid var(--rft-line); color: #5a6a7b; }
    .role-filter-toolbar .rft-btn-reset:hover { background: #f4f7fb; color: #34445a; }

    @media (max-width: 1180px) {
      .role-filter-toolbar .rft-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .role-filter-toolbar .rft-grid .rft-btn { width: 100%; }
    }
    @media (max-width: 820px) {
      .role-filter-toolbar .rft-main { flex-wrap: wrap; }
      .role-filter-toolbar .rft-search { flex: 1 1 100%; max-width: none; order: -1; }
      .role-filter-toolbar .rft-spacer { display: none; }
      .role-filter-toolbar .rft-grid { grid-template-columns: 1fr; }
    }

    /* Floating exit control shown while the table fills the website viewport. */
    .document-table-fullscreen-exit {
      position: fixed;
      top: 16px;
      right: 18px;
      z-index: 2147483600;
      align-items: center;
      gap: 8px;
      padding: 9px 16px;
      border: 0;
      border-radius: 10px;
      background: #083E40;
      color: #ffffff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 6px 18px rgba(8, 62, 64, 0.35);
    }

    .document-table-fullscreen-exit:hover {
      background: #0a5f52;
    }
  </style>

  <script>
    // Fullscreen that fills only the website viewport (not the OS/native
    // fullscreen). Reuses the shared `document-table-only-fullscreen` layout.
    (function () {
      const FS_CLASS = 'document-table-only-fullscreen';

      function ensureExitButton() {
        let btn = document.getElementById('documentTableFullscreenExit');
        if (!btn) {
          btn = document.createElement('button');
          btn.id = 'documentTableFullscreenExit';
          btn.type = 'button';
          btn.className = 'document-table-fullscreen-exit';
          btn.innerHTML = '<i class="fa-solid fa-compress"></i> Keluar Fullscreen';
          btn.style.display = 'none';
          btn.addEventListener('click', function () {
            window.setDocumentTableFullscreen(false);
          });
          document.body.appendChild(btn);
        }
        return btn;
      }

      window.setDocumentTableFullscreen = function (enabled) {
        const btn = ensureExitButton();
        if (enabled) {
          document.body.classList.add(FS_CLASS);
          btn.style.display = 'inline-flex';
        } else {
          btn.style.display = 'none';
          document.body.classList.remove(FS_CLASS);
        }
      };

      // Toggle panel filter lanjutan + sinkron badge jumlah filter aktif.
      function initRoleFilterToggle() {
        const toggle = document.getElementById('rftFilterToggle');
        const panel = document.getElementById('rftFilterPanel');
        const countEl = document.getElementById('rftFilterCount');
        if (!toggle || !panel || toggle.dataset.bound) return;
        toggle.dataset.bound = '1';

        toggle.addEventListener('click', function () {
          const open = panel.classList.toggle('open');
          toggle.classList.toggle('open', open);
        });

        function updateCount() {
          let n = 0;
          panel.querySelectorAll('select[name="filter_dari"], input[name="filter_tanggal_masuk"], input[name="filter_nilai_min"], input[name="filter_nilai_max"]')
            .forEach(function (el) { if (el.value && String(el.value).trim() !== '') n++; });
          if (countEl) {
            countEl.textContent = n;
            countEl.classList.toggle('show', n > 0);
          }
        }
        panel.addEventListener('input', updateCount);
        panel.addEventListener('change', updateCount);
        updateCount();
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRoleFilterToggle);
      } else {
        initRoleFilterToggle();
      }
    })();
  </script>
@endonce

<div class="role-filter-toolbar">
  <form action="{{ route($filterRouteName) }}" method="GET" class="role-filter-form" id="filterForm">
    <div class="rft-main">
      <div class="rft-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search"
          aria-label="Cari dokumen"
          autocomplete="off"
          data-document-search
          placeholder="Cari nomor agenda, SPP, nilai rupiah, atau field lainnya..."
          value="{{ request('search') }}">
      </div>

      <div class="rft-select">
        <select name="status" aria-label="Filter status">
          @foreach($filterStatusOptions as $value => $label)
            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <button type="button" class="rft-filter-btn {{ $advancedActive ? 'open' : '' }}" id="rftFilterToggle"
        aria-controls="rftFilterPanel" aria-label="Filter lanjutan">
        <i class="fa-solid fa-sliders"></i>
        Filter
        <span class="rft-count {{ $advancedActive ? 'show' : '' }}" id="rftFilterCount">{{ $advancedCount }}</span>
        <span class="rft-chev"></span>
      </button>

      <div class="rft-spacer"></div>

      <div class="rft-actions">
        <button type="button" class="rft-iconbtn rft-cols" data-tip="Kustomisasi Kolom Tabel" onclick="openColumnCustomizationModal()">
          <i class="fa-solid fa-table-columns"></i>
        </button>
      </div>
    </div>

    <div class="rft-panel {{ $advancedActive ? 'open' : '' }}" id="rftFilterPanel">
      <div class="rft-grid">
        <div class="rft-field">
          <label>Dari</label>
          <div class="rft-select">
            <select name="filter_dari">
              <option value="">Semua Bagian</option>
              @foreach($filterDariOptions as $value => $label)
                <option value="{{ $value }}" {{ request('filter_dari') == $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="rft-field">
          <label>Tanggal Masuk</label>
          <input type="date" name="filter_tanggal_masuk" value="{{ request('filter_tanggal_masuk') }}">
        </div>

        <div class="rft-field">
          <label>Nilai Min</label>
          <input type="text" inputmode="numeric" name="filter_nilai_min"
            value="{{ $formatFilterMoney(request('filter_nilai_min')) }}" placeholder="0">
        </div>

        <div class="rft-field">
          <label>Nilai Max</label>
          <input type="text" inputmode="numeric" name="filter_nilai_max"
            value="{{ $formatFilterMoney(request('filter_nilai_max')) }}" placeholder="999.999.999">
        </div>

        <button type="submit" class="rft-btn rft-btn-apply">
          <i class="fa-solid fa-filter"></i>
          Terapkan
        </button>
        <a class="rft-btn rft-btn-reset" href="{{ route($filterRouteName, $resetQuery) }}">
          <i class="fa-solid fa-rotate-left"></i>
          Reset
        </a>
      </div>
    </div>

    @if(request('keterlambatan'))
      <input type="hidden" name="keterlambatan" value="{{ request('keterlambatan') }}">
    @endif
    @if(request('per_page'))
      <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    @endif
    @if(request('columns'))
      @foreach((array) request('columns') as $column)
        <input type="hidden" name="columns[]" value="{{ $column }}">
      @endforeach
    @endif
    @if(request('sort'))
      <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if(request('order'))
      <input type="hidden" name="order" value="{{ request('order') }}">
    @endif
  </form>
</div>

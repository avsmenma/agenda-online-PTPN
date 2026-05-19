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
@endphp

@once
  <style>
    .role-filter-toolbar {
      border: 1px solid #e8eef4;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
      margin-bottom: 1rem;
      padding: 0.85rem 1rem;
    }

    .role-filter-form {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      width: 100%;
    }

    .role-filter-main,
    .role-filter-advanced {
      display: grid;
      gap: 0.65rem;
      width: 100%;
    }

    .role-filter-main {
      align-items: center;
      grid-template-columns: minmax(280px, 1fr) minmax(180px, 220px) auto auto auto;
    }

    .role-filter-advanced {
      align-items: end;
      grid-template-columns: minmax(150px, 1fr) minmax(155px, 1fr) minmax(140px, 1fr) minmax(140px, 1fr) auto auto;
    }

    .role-filter-field {
      min-width: 0;
    }

    .role-filter-field label {
      color: #94a3b8;
      display: block;
      font-size: 0.66rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      line-height: 1.1;
      margin-bottom: 0.35rem;
      text-transform: uppercase;
    }

    .role-filter-toolbar .input-group,
    .role-filter-toolbar .form-select,
    .role-filter-toolbar .form-control {
      min-height: 46px;
    }

    .role-filter-toolbar .input-group-text,
    .role-filter-toolbar .form-select,
    .role-filter-toolbar .form-control {
      background: #ffffff;
      border-color: #dbe5ee;
      color: #0f172a;
      font-size: 0.88rem;
    }

    .role-filter-toolbar .form-select,
    .role-filter-toolbar .form-control {
      border-radius: 10px;
      box-shadow: none;
      height: 46px;
    }

    .role-filter-toolbar .input-group-text {
      border-radius: 10px 0 0 10px;
      padding-left: 1rem;
      padding-right: 0.9rem;
    }

    .role-filter-toolbar .input-group .form-control {
      border-radius: 0 10px 10px 0;
    }

    .role-filter-actions {
      display: flex;
      gap: 0.55rem;
      white-space: nowrap;
    }

    .role-filter-btn {
      align-items: center;
      border: 1px solid transparent;
      border-radius: 10px;
      display: inline-flex;
      font-size: 0.88rem;
      font-weight: 700;
      gap: 0.45rem;
      height: 46px;
      justify-content: center;
      padding: 0 1rem;
      text-decoration: none;
      transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .role-filter-btn:hover {
      transform: translateY(-1px);
    }

    .role-filter-btn-primary {
      background: #083E40;
      color: #ffffff;
      box-shadow: 0 6px 14px rgba(8, 62, 64, 0.18);
    }

    .role-filter-btn-primary:hover {
      color: #ffffff;
      box-shadow: 0 9px 18px rgba(8, 62, 64, 0.24);
    }

    .role-filter-btn-soft {
      background: #ffffff;
      border-color: #dbe5ee;
      color: #5f6f84;
    }

    .role-filter-btn-soft:hover {
      border-color: #c8d5e3;
      color: #34445a;
    }

    .role-filter-action-sync {
      background: #13a3b8;
      color: #ffffff;
      box-shadow: 0 6px 14px rgba(19, 163, 184, 0.18);
    }

    .role-filter-action-sync:hover {
      color: #ffffff;
      box-shadow: 0 9px 18px rgba(19, 163, 184, 0.24);
    }

    .role-filter-btn-columns {
      background: #8aa30b;
      color: #ffffff;
      box-shadow: 0 6px 14px rgba(138, 163, 11, 0.2);
    }

    .role-filter-btn-columns:hover {
      color: #ffffff;
      box-shadow: 0 9px 18px rgba(138, 163, 11, 0.26);
    }

    .role-filter-action-expand {
      background: #053f42;
      color: #ffffff;
      box-shadow: 0 6px 14px rgba(5, 63, 66, 0.2);
    }

    .role-filter-action-expand:hover {
      color: #ffffff;
      box-shadow: 0 9px 18px rgba(5, 63, 66, 0.26);
    }

    @media (max-width: 1400px) {
      .role-filter-main {
        grid-template-columns: minmax(260px, 1fr) minmax(170px, 210px) auto auto;
      }

      .role-filter-action-expand {
        grid-column: span 1;
      }

      .role-filter-advanced {
        grid-template-columns: repeat(4, minmax(140px, 1fr)) auto auto;
      }
    }

    @media (max-width: 1180px) {
      .role-filter-main,
      .role-filter-advanced {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .role-filter-main .role-filter-search {
        grid-column: 1 / -1;
      }

      .role-filter-actions {
        flex-wrap: wrap;
      }
    }

    @media (max-width: 680px) {
      .role-filter-toolbar {
        padding: 0.75rem;
      }

      .role-filter-main,
      .role-filter-advanced {
        grid-template-columns: 1fr;
      }

      .role-filter-actions,
      .role-filter-btn {
        width: 100%;
      }
    }
  </style>

  <script>
    function toggleDocumentTableFullscreen() {
      const table = document.getElementById('documentTableContainer');
      if (!table) return;

      if (document.fullscreenElement) {
        document.exitFullscreen();
        return;
      }

      if (table.requestFullscreen) {
        table.requestFullscreen();
      }
    }
  </script>
@endonce

<div class="role-filter-toolbar">
  <form action="{{ route($filterRouteName) }}" method="GET" class="role-filter-form" id="filterForm">
    <div class="role-filter-main">
      <div class="role-filter-search">
        <div class="input-group">
          <span class="input-group-text">
            <i class="fa-solid fa-magnifying-glass text-muted"></i>
          </span>
          <input type="text" class="form-control" name="search"
            aria-label="Cari dokumen"
            autocomplete="off"
            data-document-search
            placeholder="Cari nomor agenda, SPP, nilai rupiah, atau field lainnya..."
            value="{{ request('search') }}">
        </div>
      </div>

      <div class="role-filter-field">
        <select name="status" class="form-select" aria-label="Filter status">
          @foreach($filterStatusOptions as $value => $label)
            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <button type="button" class="role-filter-btn role-filter-action-sync" id="btnRefreshTable" onclick="refreshDocumentTable()">
        <i class="fa-solid fa-arrows-rotate"></i>
        Refresh
      </button>

      <button type="button" class="role-filter-btn role-filter-btn-columns" onclick="openColumnCustomizationModal()">
        <i class="fa-solid fa-table-columns"></i>
        Kustomisasi Kolom Tabel
      </button>

      <button type="button" class="role-filter-btn role-filter-action-expand btn-fullscreen-toggle" onclick="toggleDocumentTableFullscreen()">
        <i class="fa-solid fa-expand"></i>
        Fullscreen
      </button>
    </div>

    <div class="role-filter-advanced">
      <div class="role-filter-field">
        <label>Dari</label>
        <select name="filter_dari" class="form-select">
          <option value="">Semua Bagian</option>
          @foreach($filterDariOptions as $value => $label)
            <option value="{{ $value }}" {{ request('filter_dari') == $value ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="role-filter-field">
        <label>Tanggal Masuk</label>
        <input type="date" class="form-control" name="filter_tanggal_masuk" value="{{ request('filter_tanggal_masuk') }}">
      </div>

      <div class="role-filter-field">
        <label>Nilai Min</label>
        <input type="text" inputmode="numeric" class="form-control" name="filter_nilai_min"
          value="{{ $formatFilterMoney(request('filter_nilai_min')) }}" placeholder="0">
      </div>

      <div class="role-filter-field">
        <label>Nilai Max</label>
        <input type="text" inputmode="numeric" class="form-control" name="filter_nilai_max"
          value="{{ $formatFilterMoney(request('filter_nilai_max')) }}" placeholder="999.999.999">
      </div>

      <div class="role-filter-actions">
        <button type="submit" class="role-filter-btn role-filter-btn-primary">
          <i class="fa-solid fa-filter"></i>
          Terapkan
        </button>
        <a class="role-filter-btn role-filter-btn-soft" href="{{ route($filterRouteName, $resetQuery) }}">
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

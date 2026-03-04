@extends('layouts/app')

@section('content')
  {{-- External CSS for clean, maintainable styling --}}
  <link rel="stylesheet" href="{{ asset('css/owner-dokumen.css') }}">

  <div class="owner-dashboard">

    {{-- ===== Header Section ===== --}}
    <div class="dashboard-header">
      <div class="header-left">
        <div class="header-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="header-content">
          <h1>Dashboard Kabag Keuangan</h1>
          <p>Pantau dan kelola semua dokumen perusahaan dengan mudah</p>
        </div>
      </div>
      <div class="header-right">
        {{-- Dark Mode Toggle --}}
        <button id="owner-theme-toggle" class="header-action-btn" aria-label="Toggle dark mode"
          onclick="toggleOwnerTheme()">
          <i class="fas fa-moon theme-icon-moon"></i>
          <i class="fas fa-sun theme-icon-sun"></i>
        </button>
        {{-- Notification Bell --}}
        <button class="header-action-btn" aria-label="Notifications">
          <i class="fas fa-bell"></i>
        </button>
        {{-- Profile Dropdown --}}
        <div class="header-profile-dropdown">
          <button class="header-action-btn header-profile-btn" onclick="toggleOwnerProfileMenu()">
            <i class="fas fa-user"></i>
          </button>
          <div class="header-profile-menu" id="ownerProfileMenu">
            <a href="{{ route('profile.account') }}" class="header-profile-item">
              <i class="fas fa-user-circle"></i>
              <span>Akun</span>
            </a>
            <a href="{{ route('2fa.setup') }}" class="header-profile-item">
              <i class="fas fa-shield-alt"></i>
              <span>Keamanan 2FA</span>
            </a>
            <div class="header-profile-divider"></div>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="header-profile-item header-logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== Statistics Cards ===== --}}
    <div class="stats-grid">
      <div class="stat-card total clickable" data-filter="all" onclick="filterByCard('all')"
        title="Klik untuk melihat semua dokumen">
        <div class="stat-content">
          <div class="stat-label">Total Dokumen</div>
          <div class="stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon total">
          <i class="fas fa-file-alt"></i>
        </div>
      </div>

      <div class="stat-card proses clickable" data-filter="belum_siap" onclick="filterByCard('belum_siap')"
        title="Klik untuk filter dokumen belum siap bayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Belum Siap Bayar</div>
          <div class="stat-value">{{ number_format($dokumenBelumSiapBayar ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon proses">
          <i class="fas fa-clock"></i>
        </div>
      </div>

      <div class="stat-card siap clickable" data-filter="siap_dibayar" onclick="filterByCard('siap_dibayar')"
        title="Klik untuk filter dokumen siap bayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Siap Bayar</div>
          <div class="stat-value">{{ number_format($dokumenSiapBayar ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon siap">
          <i class="fas fa-clipboard-check"></i>
        </div>
      </div>

      <div class="stat-card selesai clickable" data-filter="sudah_dibayar" onclick="filterByCard('sudah_dibayar')"
        title="Klik untuk filter dokumen sudah dibayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Sudah Dibayar</div>
          <div class="stat-value">{{ number_format($dokumenSelesai ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon selesai">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>

      <div class="stat-card nilai">
        <div class="stat-content">
          <div class="stat-label">Total Nilai (Rp)</div>
          <div class="stat-value small">Rp{{ number_format($totalNilai ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon nilai">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>
    </div>

    {{-- ===== Document Age Filter Section ===== --}}
    <div class="age-filter-section">
      <div class="age-filter-header">
        <h3><i class="fas fa-hourglass-half"></i> Filter Umur Dokumen</h3>
        <button class="age-settings-btn" onclick="openAgeSettingsModal()">
          <i class="fas fa-cog"></i>
          <span>Pengaturan</span>
        </button>
      </div>

      {{-- Age Info Cards --}}
      <div class="age-info-cards">
        <div class="age-info-card" id="ageCard1" onclick="filterByAge(1)">
          <div class="age-info-card-icon">
            <i class="fas fa-calendar-day"></i>
          </div>
          <div class="age-info-card-content">
            <div class="age-info-card-label">Umur &gt; <span id="ageLabel1">3</span> Hari</div>
            <div class="age-info-card-value" id="ageCount1">-</div>
            <div class="age-info-card-count">dokumen</div>
          </div>
        </div>

        <div class="age-info-card warning" id="ageCard2" onclick="filterByAge(2)">
          <div class="age-info-card-icon">
            <i class="fas fa-calendar-week"></i>
          </div>
          <div class="age-info-card-content">
            <div class="age-info-card-label">Umur &gt; <span id="ageLabel2">7</span> Hari</div>
            <div class="age-info-card-value" id="ageCount2">-</div>
            <div class="age-info-card-count">dokumen</div>
          </div>
        </div>

        <div class="age-info-card danger" id="ageCard3" onclick="filterByAge(3)">
          <div class="age-info-card-icon">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="age-info-card-content">
            <div class="age-info-card-label">Umur &gt; <span id="ageLabel3">30</span> Hari</div>
            <div class="age-info-card-value" id="ageCount3">-</div>
            <div class="age-info-card-count">dokumen</div>
          </div>
        </div>
      </div>

      {{-- Age Filter Chips --}}
      <div class="age-filter-chips">
        <button class="age-chip" id="ageChip1" onclick="filterByAge(1)">
          <i class="fas fa-filter"></i>
          <span>&gt; <span class="chip-days-1">3</span> hari</span>
        </button>
        <button class="age-chip warning" id="ageChip2" onclick="filterByAge(2)">
          <i class="fas fa-filter"></i>
          <span>&gt; <span class="chip-days-2">7</span> hari</span>
        </button>
        <button class="age-chip danger" id="ageChip3" onclick="filterByAge(3)">
          <i class="fas fa-filter"></i>
          <span>&gt; <span class="chip-days-3">30</span> hari</span>
        </button>
        <button class="age-chip age-chip-clear" id="ageChipClear" onclick="clearAgeFilter()" style="display: none;">
          <i class="fas fa-times"></i>
          <span>Hapus Filter</span>
        </button>
      </div>
    </div>

    {{-- Age Settings Modal --}}
    <div class="age-modal-overlay" id="ageSettingsModal" onclick="closeAgeSettingsModal(event)">
      <div class="age-modal" onclick="event.stopPropagation()">
        <div class="age-modal-header">
          <i class="fas fa-sliders-h"></i>
          <h4>Pengaturan Filter Umur Dokumen</h4>
        </div>
        <div class="age-modal-body">
          <div class="age-input-group">
            <label>Filter 1 - Level Rendah (Kuning Muda)</label>
            <div class="age-input-suffix">
              <input type="number" id="ageSetting1" min="1" max="365" value="3">
              <span>hari</span>
            </div>
          </div>
          <div class="age-input-group">
            <label>Filter 2 - Level Sedang (Oranye)</label>
            <div class="age-input-suffix">
              <input type="number" id="ageSetting2" min="1" max="365" value="7">
              <span>hari</span>
            </div>
          </div>
          <div class="age-input-group">
            <label>Filter 3 - Level Tinggi (Merah)</label>
            <div class="age-input-suffix">
              <input type="number" id="ageSetting3" min="1" max="365" value="30">
              <span>hari</span>
            </div>
          </div>
        </div>
        <div class="age-modal-footer">
          <button class="age-modal-btn reset" onclick="resetAgeSettings()">
            <i class="fas fa-redo"></i> Reset Default
          </button>
          <div>
            <button class="age-modal-btn cancel" onclick="closeAgeSettingsModal()">Batal</button>
            <button class="age-modal-btn save" onclick="saveAgeSettings()">Simpan</button>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== Filter Bar ===== --}}
    <div class="filter-bar">
      {{-- Search Input --}}
      <div class="search-wrapper" style="flex: 1 !important; min-width: 180px !important;">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}"
          placeholder="Cari nomor agenda, SPP, uraian..." onkeypress="if(event.key==='Enter') applyFilter()">
      </div>

      {{-- Per Page Selector --}}
      <div id="perPageWrapper"
        style="display:flex !important; align-items:center !important; gap:8px !important; flex-shrink:0 !important;">
        <label
          style="font-weight:500 !important; color:#64748b !important; font-size:0.8rem !important; white-space:nowrap !important; margin:0 !important;">Baris:</label>
        <select id="perPageSelect" onchange="changePerPage(this.value)"
          style="padding:0.5rem 2rem 0.5rem 0.75rem !important; border:1px solid #e2e8f0 !important; border-radius:6px !important; background:#fff !important; font-size:0.8rem !important; color:#334155 !important; cursor:pointer !important; appearance:none !important; background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%2712%27 viewBox=%270 0 12 12%27%3E%3Cpath fill=%27%2364748b%27 d=%27M6 8L1 3h10z%27/%3E%3C/svg%3E') !important; background-repeat:no-repeat !important; background-position:right 0.5rem center !important; min-width:65px !important;">
          <option value="10" {{ ($documents->perPage() ?? 10) == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ ($documents->perPage() ?? 10) == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ ($documents->perPage() ?? 10) == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ ($documents->perPage() ?? 10) == 100 ? 'selected' : '' }}>100</option>
          <option value="all" {{ ($documents->perPage() ?? 10) >= ($documents->total() ?? 0) ? 'selected' : '' }}>Semua
          </option>
        </select>
      </div>

      {{-- Quick Filter Chips --}}
      <div class="filter-chips">
        <button class="chip {{ request('status') == 'semua' ? 'active' : '' }}" onclick="setStatus('semua')">
          📄 Semua Dokumen
        </button>
        <button
          class="chip {{ (request('status') == '' || request('status') == 'belum_siap') && request('status') != 'semua' ? 'active' : '' }}"
          onclick="setStatus('belum_siap')">
          🔄 Belum Siap Dibayar
        </button>
        <button class="chip {{ request('status') == 'siap_dibayar' ? 'active' : '' }}"
          onclick="setStatus('siap_dibayar')">
          📋 Siap Dibayar
        </button>
        <button class="chip {{ request('status') == 'sudah_dibayar' ? 'active' : '' }}"
          onclick="setStatus('sudah_dibayar')">
          ✅ Sudah Dibayar
        </button>
      </div>

      {{-- Advanced Filter Toggle --}}
      <button class="advanced-toggle" onclick="toggleFilterPanel()">
        <i class="fas fa-sliders-h"></i>
        <span>Filter</span>
        <span class="badge" id="filterCount"
          data-count="{{ $activeFilterCount ?? 0 }}">{{ $activeFilterCount ?? 0 }}</span>
      </button>

      {{-- View Switcher --}}
      <div class="view-switcher">
        <button class="view-btn active" data-view="card" onclick="switchView('card')">
          <i class="fas fa-th-large"></i>
          <span class="d-none d-sm-inline">Kartu</span>
        </button>
        <button class="view-btn" data-view="table" onclick="switchView('table')">
          <i class="fas fa-table"></i>
          <span class="d-none d-sm-inline">Tabel</span>
        </button>
      </div>
    </div>

    {{-- ===== Advanced Filter Panel (Collapsed by Default) ===== --}}
    <div class="filter-panel" id="filterPanel">
      <form method="GET" action="{{ url('/owner/dokumen') }}" id="filterForm">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
        <input type="hidden" name="status" id="statusInput" value="{{ request('status', '') }}">

        <div class="filter-panel-content">
          <div class="filter-grid">
            {{-- Bagian --}}
            <div class="filter-group">
              <label><i class="fas fa-building"></i> Bagian</label>
              <select name="filter_bagian" onchange="updateFilterCount(); applyFilter()">
                <option value="">Semua Bagian</option>
                @foreach($filterData['bagian'] ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_bagian') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            {{-- Vendor --}}
            <div class="filter-group">
              <label><i class="fas fa-handshake"></i> Vendor</label>
              <select name="filter_vendor" onchange="updateFilterCount(); applyFilter()">
                <option value="">Semua Vendor</option>
                @foreach($filterData['vendor'] ?? [] as $key => $value)
                  <option value="{{ $value }}" {{ request('filter_vendor') == $value ? 'selected' : '' }}>{{ $value }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Kriteria CF --}}
            <div class="filter-group">
              <label><i class="fas fa-tags"></i> Kriteria CF</label>
              <select name="filter_kriteria_cf" id="filterKriteriaCf"
                onchange="updateSubKriteriaFilter(); updateFilterCount(); applyFilter();">
                <option value="">Semua Kriteria</option>
                @foreach($filterData['kriteria_cf'] ?? [] as $id => $nama)
                  <option value="{{ $id }}" {{ request('filter_kriteria_cf') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
              </select>
            </div>

            {{-- Sub Kriteria --}}
            <div class="filter-group">
              <label><i class="fas fa-tag"></i> Sub Kriteria</label>
              <select name="filter_sub_kriteria" id="filterSubKriteria"
                onchange="updateItemSubKriteriaFilter(); updateFilterCount(); applyFilter();" disabled>
                <option value="">Pilih Kriteria CF dahulu</option>
                @foreach($filterData['sub_kriteria'] ?? [] as $id => $nama)
                  <option value="{{ $id }}"
                    data-kriteria-cf="{{ \App\Models\SubKriteria::on('cash_bank')->where('id_sub_kriteria', $id)->value('id_kategori_kriteria') ?? '' }}"
                    {{ request('filter_sub_kriteria') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
              </select>
            </div>

            {{-- Item Sub Kriteria --}}
            <div class="filter-group">
              <label><i class="fas fa-list"></i> Item Sub Kriteria</label>
              <select name="filter_item_sub_kriteria" id="filterItemSubKriteria"
                onchange="updateFilterCount(); applyFilter();" disabled>
                <option value="">Pilih Sub Kriteria dahulu</option>
                @foreach($filterData['item_sub_kriteria'] ?? [] as $id => $nama)
                  <option value="{{ $id }}"
                    data-sub-kriteria="{{ \App\Models\ItemSubKriteria::on('cash_bank')->where('id_item_sub_kriteria', $id)->value('id_sub_kriteria') ?? '' }}"
                    {{ request('filter_item_sub_kriteria') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
              </select>
            </div>

            {{-- Kebun --}}
            <div class="filter-group">
              <label><i class="fas fa-seedling"></i> Kebun</label>
              <select name="filter_kebun" onchange="updateFilterCount(); applyFilter()">
                <option value="">Semua Kebun</option>
                @foreach($filterData['kebun'] ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_kebun') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            {{-- Status Pembayaran --}}
            <div class="filter-group">
              <label><i class="fas fa-money-bill-wave"></i> Status Pembayaran</label>
              <select name="filter_status_pembayaran" onchange="updateFilterCount(); applyFilter()">
                <option value="">Semua Status</option>
                <option value="belum_dibayar" {{ request('filter_status_pembayaran') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="siap_dibayar" {{ request('filter_status_pembayaran') == 'siap_dibayar' ? 'selected' : '' }}>
                  Siap Dibayar</option>
                <option value="sudah_dibayar" {{ request('filter_status_pembayaran') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
              </select>
            </div>
          </div>

          <div class="filter-actions">
            <button type="button" class="btn-filter btn-reset" onclick="resetFilters()">
              <i class="fas fa-redo"></i> Reset
            </button>
          </div>

          {{-- Active Filter Tags --}}
          <div class="active-filters" id="activeFilters"></div>
        </div>
      </form>
    </div>

    {{-- ===== Bagian Summary Banner (shown when bagian filter is active) ===== --}}
    <style>
      .bagian-summary-banner {
        background: #fff;
        border-radius: 16px;
        border: none;
        margin-bottom: 1.5rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 6px 16px rgba(8, 62, 64, .06)
      }

      .bagian-summary-banner.show {
        animation: bagianSlide .35s cubic-bezier(.4, 0, .2, 1)
      }

      @keyframes bagianSlide {
        from {
          opacity: 0;
          transform: translateY(-12px)
        }

        to {
          opacity: 1;
          transform: translateY(0)
        }
      }

      .bagian-summary-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 14px 20px !important;
        background: linear-gradient(135deg, #083E40 0%, #0a5e61 100%) !important
      }

      .bagian-summary-title {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        font-size: .875rem !important;
        color: #fff !important;
        font-weight: 500 !important
      }

      .bagian-summary-title i {
        color: rgba(255, 255, 255, .8) !important;
        font-size: .9rem !important
      }

      .bagian-summary-title strong {
        color: #fff !important;
        font-weight: 700 !important;
        letter-spacing: .3px !important
      }

      .bagian-summary-close {
        width: 30px !important;
        height: 30px !important;
        border-radius: 8px !important;
        border: none !important;
        background: rgba(255, 255, 255, .15) !important;
        color: rgba(255, 255, 255, .8) !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: .75rem !important;
        transition: all .2s ease !important
      }

      .bagian-summary-close:hover {
        background: rgba(255, 255, 255, .25) !important;
        color: #fff !important;
        transform: scale(1.05) !important
      }

      .bagian-summary-stats {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 0 !important;
        padding: 0 !important
      }

      .bagian-stat-item {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
        padding: 18px 20px !important;
        position: relative !important;
        transition: all .25s ease !important;
        border: none !important
      }

      .bagian-stat-item:not(:last-child)::after {
        content: '' !important;
        position: absolute !important;
        right: 0 !important;
        top: 22% !important;
        height: 56% !important;
        width: 1px !important;
        background: #e5e7eb !important
      }

      .bagian-stat-item:hover {
        background: #f8fafb !important
      }

      .bagian-stat-icon {
        width: 44px !important;
        height: 44px !important;
        min-width: 44px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.05rem !important;
        color: #fff !important;
        flex-shrink: 0 !important;
        box-shadow: 0 3px 8px rgba(0, 0, 0, .12) !important
      }

      .bagian-stat-item.total .bagian-stat-icon {
        background: linear-gradient(140deg, #083E40, #0d6b6e) !important
      }

      .bagian-stat-item.belum .bagian-stat-icon {
        background: linear-gradient(140deg, #e67e22, #f39c12) !important
      }

      .bagian-stat-item.siap .bagian-stat-icon {
        background: linear-gradient(140deg, #2563eb, #3b82f6) !important
      }

      .bagian-stat-item.sudah .bagian-stat-icon {
        background: linear-gradient(140deg, #16a34a, #22c55e) !important
      }

      .bagian-stat-info {
        min-width: 0 !important;
        display: flex !important;
        flex-direction: column !important
      }

      .bagian-stat-value {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        color: #1e293b !important;
        line-height: 1.15 !important;
        letter-spacing: -.3px !important
      }

      .bagian-stat-label {
        font-size: .68rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: .6px !important;
        color: #94a3b8 !important;
        margin-top: 3px !important;
        white-space: nowrap !important
      }

      html.dark .bagian-summary-banner {
        background: #1e293b !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .2), 0 6px 20px rgba(0, 0, 0, .15) !important
      }

      html.dark .bagian-summary-header {
        background: linear-gradient(135deg, #0f4547, #0d6b6e) !important
      }

      html.dark .bagian-stat-item:not(:last-child)::after {
        background: #334155 !important
      }

      html.dark .bagian-stat-item:hover {
        background: rgba(255, 255, 255, .04) !important
      }

      html.dark .bagian-stat-value {
        color: #f1f5f9 !important
      }

      html.dark .bagian-stat-label {
        color: #64748b !important
      }

      @media(max-width:900px) {
        .bagian-summary-stats {
          grid-template-columns: repeat(2, 1fr) !important
        }

        .bagian-stat-item:nth-child(2)::after {
          display: none !important
        }

        .bagian-stat-item:nth-child(1),
        .bagian-stat-item:nth-child(2) {
          border-bottom: 1px solid #e5e7eb !important
        }
      }

      @media(max-width:480px) {
        .bagian-summary-stats {
          grid-template-columns: 1fr !important
        }

        .bagian-stat-item::after {
          display: none !important
        }

        .bagian-stat-item:not(:last-child) {
          border-bottom: 1px solid #e5e7eb !important
        }
      }
    </style>
    <div class="bagian-summary-banner" id="bagianSummaryBanner" style="display: none;">
      <div class="bagian-summary-header">
        <div class="bagian-summary-title">
          <i class="fas fa-building"></i>
          <span>Ringkasan Bagian: <strong id="bagianSummaryName">-</strong></span>
        </div>
        <button class="bagian-summary-close" onclick="hideBagianSummary()" title="Tutup">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="bagian-summary-stats">
        <div class="bagian-stat-item total">
          <div class="bagian-stat-icon"><i class="fas fa-file-alt"></i></div>
          <div class="bagian-stat-info">
            <div class="bagian-stat-value" id="bagianStatTotal">0</div>
            <div class="bagian-stat-label">Total Dokumen</div>
          </div>
        </div>
        <div class="bagian-stat-item belum">
          <div class="bagian-stat-icon"><i class="fas fa-clock"></i></div>
          <div class="bagian-stat-info">
            <div class="bagian-stat-value" id="bagianStatBelum">0</div>
            <div class="bagian-stat-label">Belum Siap Bayar</div>
          </div>
        </div>
        <div class="bagian-stat-item siap">
          <div class="bagian-stat-icon"><i class="fas fa-clipboard-check"></i></div>
          <div class="bagian-stat-info">
            <div class="bagian-stat-value" id="bagianStatSiap">0</div>
            <div class="bagian-stat-label">Siap Dibayar</div>
          </div>
        </div>
        <div class="bagian-stat-item sudah">
          <div class="bagian-stat-icon"><i class="fas fa-check-circle"></i></div>
          <div class="bagian-stat-info">
            <div class="bagian-stat-value" id="bagianStatSudah">0</div>
            <div class="bagian-stat-label">Sudah Dibayar</div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== Card View ===== --}}
    <div id="cardView" class="view-container active">
      @if($documents->count() == 0)
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-folder-open"></i>
          </div>
          <div class="empty-state-title">Tidak ada dokumen</div>
          <div class="empty-state-text">
            @if(isset($search) && !empty($search))
              Tidak ada dokumen yang sesuai dengan pencarian "{{ $search }}"
            @else
              Dokumen akan ditampilkan di sini ketika tersedia
            @endif
          </div>
        </div>
      @else
        <div class="cards-grid">
          @foreach($documents as $dokumen)
            <div class="doc-card {{ $dokumen['is_overdue'] ? 'overdue' : '' }} {{ $dokumen['is_paid'] ? 'completed' : '' }}"
              onclick="navigateToWorkflow('{{ $dokumen['id'] }}')">

          @if($dokumen['is_overdue'])
                <span class="overdue-badge">TERLAMBAT</span>
              @endif

              @if($dokumen['is_paid'])
                <span class="paid-stamp">SUDAH DIBAYAR</span>
              @endif

              {{-- Bell Urgency Button --}}
              <button
                class="urgency-bell-btn {{ ($dokumen['urgency_active'] ?? false) ? 'active' : '' }}"
                onclick="event.stopPropagation(); openUrgencyModal({{ $dokumen['id'] }}, '{{ addslashes($dokumen['nomor_agenda']) }}', '{{ addslashes($dokumen['current_handler_display'] ?? '-') }}')"
                title="{{ ($dokumen['urgency_active'] ?? false) ? 'Notifikasi sudah dikirim – menunggu penyelesaian' : 'Kirim pengingat darurat ke penanggung jawab dokumen ini' }}"
                {{ ($dokumen['urgency_active'] ?? false) ? 'disabled' : '' }}
                data-doc-id="{{ $dokumen['id'] }}"
                id="bell-btn-{{ $dokumen['id'] }}">
                <i class="fas fa-bell"></i>
              </button>

              <div class="doc-card-header">
                <div>
                  <div class="doc-card-title">{{ $dokumen['nomor_agenda'] }}</div>
                  <div class="doc-card-subtitle">SPP: {{ $dokumen['nomor_spp'] }}</div>
                </div>
                @if($dokumen['is_paid'])
                  <span class="paid-indicator">
                    <i class="fas fa-check-circle"></i> Dibayar
                  </span>
                @endif
              </div>

              <div class="doc-card-value">
                Rp {{ number_format($dokumen['nilai_rupiah'], 0, ',', '.') }}
              </div>

              <div class="doc-card-meta">
                <div class="doc-card-meta-item">
                  <i class="fas fa-user"></i>
                  <span>Posisi:</span>
                  <span class="handler-avatar">{{ substr($dokumen['current_handler_display'] ?? 'N', 0, 1) }}</span>
                  <span>{{ $dokumen['current_handler_display'] ?? '-' }}</span>
                </div>
                @if($dokumen['deadline_info'])
                  <div class="doc-card-meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Batas Waktu:</span>
                    <span style="font-weight: 500; color: {{ $dokumen['is_overdue'] ? 'var(--danger-color)' : 'inherit' }}">
                      {{ $dokumen['deadline_info']['text'] }}
                    </span>
                  </div>
                @endif
                @if($dokumen['umur_dokumen'])
                  <div class="doc-card-meta-item">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Umur Dokumen:</span>
                    <span
                      style="font-weight: 500; color: {{ $dokumen['umur_dokumen']['is_paid'] ? 'var(--success-color)' : 'inherit' }}">
                      {{ $dokumen['umur_dokumen']['text'] }}
                      @if($dokumen['umur_dokumen']['is_paid'])
                        <i class="fas fa-check-circle" style="margin-left: 4px; color: var(--success-color);"></i>
                      @endif
                    </span>
                  </div>
                @endif
              </div>

              {{-- Workflow Stepper --}}
              <div class="workflow-stepper">
                <div class="stepper-label">Progres Alur</div>
                <div class="stepper-track">
                  @php
                    $progress = $dokumen['progress_percentage'] ?? 0;
                    $currentStep = min(5, max(1, ceil($progress / 20)));
                    $isPaid = $dokumen['is_paid'] ?? false;
                    $stepLabels = ['Operator', 'Verifikasi', 'Perpajakan', 'Akutansi', 'Pembayaran'];
                  @endphp
                  @for($i = 1; $i <= 5; $i++)
                    @if($isPaid)
                      <div class="stepper-step completed" data-tooltip="{{ $stepLabels[$i - 1] }}">
                        <i class="fas fa-check"></i>
                      </div>
                    @else
                      <div class="stepper-step {{ $i < $currentStep ? 'completed' : ($i == $currentStep ? 'active' : '') }}"
                        data-tooltip="{{ $stepLabels[$i - 1] }}">
                        @if($i < $currentStep)
                          <i class="fas fa-check"></i>
                        @else
                          {{ $i }}
                        @endif
                      </div>
                    @endif
                  @endfor
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Pagination --}}
        @include('owner.partials.pagination-footer', ['paginator' => $documents])
      @endif
    </div>

    {{-- ===== Table View ===== --}}
    <div id="tableView" class="view-container">
      @if($documents->count() == 0)
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-folder-open"></i>
          </div>
          <div class="empty-state-title">Tidak ada dokumen</div>
          <div class="empty-state-text">
            @if(isset($search) && !empty($search))
              Tidak ada dokumen yang sesuai dengan pencarian "{{ $search }}"
            @else
              Dokumen akan ditampilkan di sini ketika tersedia
            @endif
          </div>
        </div>
      @else
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>No. Dokumen</th>
                <th>Tgl Masuk</th>
                <th>Nilai (Rp)</th>
                <th>Kebun</th>
                <th>Vendor/Dibayarkan Kepada</th>
                <th>Posisi</th>
                <th>Status</th>
                <th>Progres</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($documents as $dokumen)
                <tr class="{{ $dokumen['is_overdue'] ? 'overdue' : '' }}"
                  onclick="navigateToWorkflow('{{ $dokumen['id'] }}')">
                  <td>
                    <div class="doc-number">{{ $dokumen['nomor_agenda'] }}</div>
                    <div class="doc-spp">{{ $dokumen['nomor_spp'] }}</div>
                  </td>
                  <td>{{ $dokumen['tanggal_masuk'] ?? '-' }}</td>
                  <td>
                    <span class="doc-value">Rp {{ number_format($dokumen['nilai_rupiah'], 0, ',', '.') }}</span>
                  </td>
                  <td>{{ $dokumen['kebun'] ?? '-' }}</td>
                  <td>
                    <span class="vendor-cell" title="{{ $dokumen['vendor'] ?? '-' }}"
                      style="max-width: 180px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $dokumen['vendor'] ?? '-' }}</span>
                  </td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 6px;">
                      <span class="handler-avatar">{{ substr($dokumen['current_handler_display'] ?? 'N', 0, 1) }}</span>
                      <span>{{ $dokumen['current_handler_display'] ?? '-' }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="status-badge {{ $dokumen['progress_percentage'] >= 100 ? 'selesai' : 'proses' }}">
                      {{ $dokumen['progress_percentage'] >= 100 ? 'Selesai' : 'Proses' }}
                    </span>
                  </td>
                  <td>
                    <div class="progress-bar-mini">
                      <div class="fill"
                        style="width: {{ $dokumen['progress_percentage'] }}%; background: {{ $dokumen['progress_color'] }};">
                      </div>
                    </div>
                    <div class="progress-text">{{ $dokumen['progress_percentage'] }}%</div>
                  </td>
                  <td>
                    <div style="display:flex;gap:6px;align-items:center;">
                      <button class="btn-view" onclick="event.stopPropagation(); navigateToWorkflow('{{ $dokumen['id'] }}')">
                        Lihat
                      </button>
                      <button class="btn-riwayat" title="Riwayat Perjalanan Dokumen"
                        onclick="event.stopPropagation(); openTimelineModal({{ $dokumen['id'] }}, '{{ addslashes($dokumen['nomor_agenda']) }}')">
                        <i class="fas fa-history"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @include('owner.partials.pagination-footer', ['paginator' => $documents])
      @endif
    </div>

  </div>

  {{-- ===== Urgency Bell CSS ===== --}}
  <style>
    /* Bell button on doc-cards */
    .urgency-bell-btn {
      position: absolute; top: 8px; right: 8px;
      width: 30px; height: 30px; border-radius: 50%;
      background: transparent; border: 1px solid #cbd5e1;
      color: #94a3b8; cursor: pointer; font-size: 13px;
      display: flex; align-items: center; justify-content: center;
      transition: all 0.2s ease; z-index: 10; padding: 0;
    }
    .urgency-bell-btn:hover:not(:disabled) {
      background: #fff7ed; border-color: #f59e0b; color: #f59e0b; transform: scale(1.1);
    }
    .urgency-bell-btn.active {
      background: #fef3c7; border-color: #f59e0b; color: #d97706;
      animation: bell-ring 0.65s ease-in-out;
    }
    .urgency-bell-btn:disabled { opacity: 0.8; cursor: not-allowed; }
    @keyframes bell-ring {
      0%, 100% { transform: rotate(0deg); }
      20% { transform: rotate(-15deg); }
      40% { transform: rotate(15deg); }
      60% { transform: rotate(-10deg); }
      80% { transform: rotate(10deg); }
    }
    /* Urgency Modal */
    .urgency-modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,0.45);
      z-index: 9999; display: flex; align-items: center; justify-content: center;
      animation: fadeIn 0.2s ease;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .urgency-modal {
      background: #fff; border-radius: 16px; padding: 28px 32px;
      max-width: 420px; width: 90%;
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
      text-align: center; animation: slideUp 0.25s ease;
    }
    @keyframes slideUp {
      from { transform: translateY(20px); opacity: 0; }
      to   { transform: translateY(0);    opacity: 1; }
    }
    html.dark .urgency-modal { background: #1e293b; color: #f1f5f9; }
    .urgency-modal-icon { font-size: 38px; margin-bottom: 10px; animation: bell-ring 0.8s ease; }
    .urgency-modal h4 { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    html.dark .urgency-modal h4 { color: #f1f5f9; }
    .urgency-modal p { font-size: 0.88rem; color: #64748b; margin-bottom: 20px; line-height: 1.55; }
    html.dark .urgency-modal p { color: #94a3b8; }
    .urgency-modal strong { color: #0f172a; }
    html.dark .urgency-modal strong { color: #e2e8f0; }
    .urgency-modal-actions { display: flex; gap: 10px; justify-content: center; }
    .urgency-modal-actions .btn-cancel {
      padding: 9px 20px; border-radius: 8px; border: 1px solid #e2e8f0;
      background: transparent; color: #64748b; font-size: 0.85rem;
      font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .urgency-modal-actions .btn-cancel:hover { background: #f8fafc; }
    .urgency-modal-actions .btn-confirm {
      padding: 9px 20px; border-radius: 8px; border: none;
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: #fff; font-size: 0.85rem; font-weight: 700;
      cursor: pointer; transition: all 0.2s;
    }
    .urgency-modal-actions .btn-confirm:hover { background: linear-gradient(135deg, #d97706, #b45309); transform: translateY(-1px); }
    .urgency-modal-actions .btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    /* Toast */
    .urgency-toast-container {
      position: fixed; bottom: 24px; right: 24px; z-index: 10000;
      display: flex; flex-direction: column; gap: 8px; pointer-events: none;
    }
    .urgency-toast {
      background: #fff; border-radius: 10px; padding: 12px 18px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      display: flex; align-items: center; gap: 10px;
      font-size: 0.85rem; font-weight: 600; color: #1e293b;
      animation: toastIn 0.3s ease; pointer-events: auto;
      min-width: 260px; max-width: 380px; border-left: 4px solid #10b981;
    }
    .urgency-toast.error { border-left-color: #ef4444; }
    @keyframes toastIn  { from { transform: translateX(40px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes toastOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(40px); opacity: 0; } }
  </style>

  {{-- ===== Urgency Confirmation Modal ===== --}}
  <div id="urgencyModalOverlay" style="display:none" class="urgency-modal-overlay" onclick="closeUrgencyModalOverlay(event)">
    <div class="urgency-modal" onclick="event.stopPropagation()">
      <div class="urgency-modal-icon">🔔</div>
      <h4>Kirim Pengingat Urgency</h4>
      <p id="urgencyModalBody">Mengirim pengingat...</p>
      <div class="urgency-modal-actions">
        <button class="btn-cancel" onclick="document.getElementById('urgencyModalOverlay').style.display='none'">Batalkan</button>
        <button class="btn-confirm" id="urgencyConfirmBtn" onclick="confirmSendUrgency()">
          <i class="fas fa-paper-plane"></i> Ya, Kirim Pengingat
        </button>
      </div>
    </div>
  </div>

  {{-- ===== Toast Container ===== --}}
  <div class="urgency-toast-container" id="urgencyToastContainer"></div>

  {{-- ===== Urgency JavaScript ===== --}}
  <script>
    let _urgencyDocId = null;

    function openUrgencyModal(docId, nomorAgenda, handler) {
      _urgencyDocId = docId;
      document.getElementById('urgencyModalBody').innerHTML =
        'Kirim pengingat urgency ke <strong>' + handler + '</strong> untuk dokumen <strong>' + nomorAgenda + '</strong>?<br><br>' +
        '<span style="color:#f59e0b;font-size:0.8rem;">⚡ Penanggung jawab akan melihat notifikasi darurat pada daftar dokumen mereka.</span>';
      const btn = document.getElementById('urgencyConfirmBtn');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Ya, Kirim Pengingat';
      document.getElementById('urgencyModalOverlay').style.display = 'flex';
    }

    function closeUrgencyModalOverlay(event) {
      if (event && event.target !== document.getElementById('urgencyModalOverlay')) return;
      document.getElementById('urgencyModalOverlay').style.display = 'none';
      _urgencyDocId = null;
    }

    function confirmSendUrgency() {
      if (!_urgencyDocId) return;
      const btn = document.getElementById('urgencyConfirmBtn');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      fetch('/owner/dokumen/' + _urgencyDocId + '/urgency', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({})
      })
      .then(r => r.json())
      .then(data => {
        document.getElementById('urgencyModalOverlay').style.display = 'none';
        if (data.success) {
          const bellBtn = document.getElementById('bell-btn-' + _urgencyDocId);
          if (bellBtn) {
            bellBtn.classList.add('active');
            bellBtn.disabled = true;
            bellBtn.title = 'Notifikasi sudah dikirim – menunggu penyelesaian';
          }
          showUrgencyToast('✅ Pengingat berhasil dikirim!', 'success');
        } else {
          showUrgencyToast('❌ ' + (data.message || 'Gagal mengirim pengingat.'), 'error');
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-paper-plane"></i> Ya, Kirim Pengingat';
        }
        _urgencyDocId = null;
      })
      .catch(() => {
        document.getElementById('urgencyModalOverlay').style.display = 'none';
        showUrgencyToast('❌ Terjadi kesalahan. Silakan coba lagi.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Ya, Kirim Pengingat';
        _urgencyDocId = null;
      });
    }

    function showUrgencyToast(message, type = 'success') {
      const container = document.getElementById('urgencyToastContainer');
      const toast = document.createElement('div');
      toast.className = 'urgency-toast' + (type === 'error' ? ' error' : '');
      toast.innerHTML = message;
      container.appendChild(toast);
      setTimeout(() => {
        toast.style.animation = 'toastOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
      }, 3500);
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && document.getElementById('urgencyModalOverlay').style.display !== 'none') {
        document.getElementById('urgencyModalOverlay').style.display = 'none';
        _urgencyDocId = null;
      }
    });
  </script>

  <script>
    // ===== Navigati  on ==       ===
    function navigateToWorkflow(id) {
      window.location.href = '{{ url("/owner/workflow") }}/' + id;
    }



    // ===== View Switcher =====
    function switchView(view) {
      document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelector(`[data-view="${view}"]`).classList.add('active');

      document.getElementById('cardView').classList.toggle('active', view === 'card');
      document.getElementById('tableView').classList.toggle('active', view === 'table');

      localStorage.setItem('ownerDashboardView', view);
    }

    // ===== Filter Functions =====
    function toggleFilterPanel() {
      const panel = document.getElementById('filterPanel');
      panel.classList.toggle('open');
    }

    function setStatus(status) {
      document.getElementById('statusInput').value = status;
      document.querySelectorAll('.chip').forEach(chip => chip.classList.remove('active'));
      event.target.classList.add('active');

      // Sync the filter_status_pembayaran dropdown in the advanced filter panel
      const statusPembayaranSelect = document.querySelector('[name="filter_status_pembayaran"]');
      if (statusPembayaranSelect) {
        const statusMap = {
          'semua': '',
          'belum_siap': 'belum_dibayar',
          'siap_dibayar': 'siap_dibayar',
          'sudah_dibayar': 'sudah_dibayar'
        };
        statusPembayaranSelect.value = statusMap[status] || '';
      }

      // Sync stat card highlight
      const cardFilterMap = {
        'semua': 'all',
        'belum_siap': 'belum_siap',
        'siap_dibayar': 'siap_dibayar',
        'sudah_dibayar': 'sudah_dibayar'
      };
      highlightActiveStatCard(cardFilterMap[status] || null);

      applyFilter();
    }

    // ===== AJAX Live Filter =====
    let _ajaxAbortController = null;

    function applyFilter(extraParams) {
      const form = document.getElementById('filterForm');
      const searchInput = document.getElementById('searchInput');

      // Build query params from form + search + current page/per_page
      const params = new URLSearchParams();

      // Add form fields
      const formData = new FormData(form);
      for (let [key, value] of formData.entries()) {
        if (value && value !== '' && key !== '_token') {
          params.set(key, value);
        }
      }

      // Add search
      if (searchInput && searchInput.value.trim()) {
        params.set('search', searchInput.value.trim());
      }

      // Merge extraParams (e.g. page, per_page from pagination)
      if (extraParams) {
        for (let [key, value] of Object.entries(extraParams)) {
          if (value === null || value === '') {
            params.delete(key);
          } else {
            params.set(key, value);
          }
        }
      }

      // Default: reset to page 1 unless page was explicitly passed
      if (!extraParams || !extraParams.page) {
        params.delete('page');
      }

      // Update URL bar without navigation
      const baseUrl = '{{ url("/owner/dokumen") }}';
      const qs = params.toString();
      history.replaceState(null, '', qs ? `${baseUrl}?${qs}` : baseUrl);

      // Show loading
      showLoadingOverlay();

      // Abort previous request if still in flight
      if (_ajaxAbortController) _ajaxAbortController.abort();
      _ajaxAbortController = new AbortController();

      const filterUrl = '{{ url("/owner/dokumen") }}' + (qs ? '?' + qs : '');

      fetch(filterUrl, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        signal: _ajaxAbortController.signal
      })
        .then(res => {
          const contentType = res.headers.get('content-type') || '';
          if (!res.ok) {
            // If redirected to login or error page
            return res.text().then(text => {
              console.error('Filter HTTP error:', res.status, text.substring(0, 200));
              throw new Error('HTTP error ' + res.status);
            });
          }
          if (!contentType.includes('application/json')) {
            return res.text().then(text => {
              console.error('Non-JSON response:', contentType, text.substring(0, 200));
              throw new Error('Expected JSON but got: ' + contentType);
            });
          }
          return res.json();
        })
        .then(data => {
          if (data && data.success) {
            renderCardView(data.documents);
            renderTableView(data.documents);
            renderPagination(data.pagination);

            // Show/hide bagian summary banner
            if (data.bagian_stats) {
              showBagianSummary(data.bagian_stats);
            } else {
              hideBagianSummary();
            }
          }
          hideLoadingOverlay();
        })
        .catch(err => {
          if (err.name !== 'AbortError') {
            console.error('Filter fetch error:', err);
            hideLoadingOverlay();
          }
        });

      // Update filter count & tags
      updateFilterCount();
    }

    function resetFilters() {
      // Clear all form inputs
      const form = document.getElementById('filterForm');
      form.querySelectorAll('select').forEach(s => s.value = '');
      form.querySelectorAll('input[type="hidden"]').forEach(i => { if (i.name !== '_token') i.value = ''; });
      document.getElementById('searchInput').value = '';
      document.getElementById('statusInput').value = 'belum_siap';

      // Reset chips
      document.querySelectorAll('.chip').forEach(chip => chip.classList.remove('active'));
      const defaultChip = document.querySelector('.chip:nth-child(2)');
      if (defaultChip) defaultChip.classList.add('active');

      // Reset stat cards
      highlightActiveStatCard(null);

      // Hide bagian summary banner
      hideBagianSummary();

      applyFilter();
    }

    function updateFilterCount() {
      const form = document.getElementById('filterForm');
      const formData = new FormData(form);
      let count = 0;

      for (let [key, value] of formData.entries()) {
        if (key.startsWith('filter_') && value && value !== '') {
          count++;
        }
      }

      const badge = document.getElementById('filterCount');
      badge.textContent = count;
      badge.setAttribute('data-count', count);

      updateActiveFilterTags();
    }

    function updateActiveFilterTags() {
      const container = document.getElementById('activeFilters');
      const form = document.getElementById('filterForm');
      const formData = new FormData(form);
      container.innerHTML = '';

      const labels = {
        'filter_bagian': 'Bagian',
        'filter_vendor': 'Vendor',
        'filter_kriteria_cf': 'Kriteria CF',
        'filter_sub_kriteria': 'Sub Kriteria',
        'filter_item_sub_kriteria': 'Item Sub',
        'filter_kebun': 'Kebun',
        'filter_status_pembayaran': 'Status Bayar'
      };

      for (let [key, value] of formData.entries()) {
        if (key.startsWith('filter_') && value && value !== '') {
          const select = form.querySelector(`[name="${key}"]`);
          const displayValue = select ? (Array.from(select.options).find(o => o.value === value)?.text || value) : value;

          const tag = document.createElement('span');
          tag.className = 'filter-tag';
          tag.innerHTML = `
                                            <span>${labels[key] || key}: ${displayValue}</span>
                                            <button type="button" class="remove" onclick="removeFilter('${key}')">
                                              <i class="fas fa-times"></i>
                                            </button>
                                          `;
          container.appendChild(tag);
        }
      }
    }

    function removeFilter(key) {
      const input = document.querySelector(`[name="${key}"]`);
      if (input) {
        input.value = '';
        applyFilter();
      }
    }

    // ===== Cascading Dropdowns =====
    function updateSubKriteriaFilter() {
      const kriteriaCfId = document.getElementById('filterKriteriaCf').value;
      const subKriteriaSelect = document.getElementById('filterSubKriteria');
      const itemSubKriteriaSelect = document.getElementById('filterItemSubKriteria');

      if (kriteriaCfId && kriteriaCfId !== '') {
        subKriteriaSelect.disabled = false;
        Array.from(subKriteriaSelect.options).forEach(option => {
          if (option.value === '') {
            option.style.display = 'block';
            return;
          }
          const kriteriaCfIdForOption = option.getAttribute('data-kriteria-cf');
          option.style.display = kriteriaCfIdForOption === kriteriaCfId ? 'block' : 'none';
        });
      } else {
        subKriteriaSelect.disabled = true;
        subKriteriaSelect.value = '';
        itemSubKriteriaSelect.disabled = true;
        itemSubKriteriaSelect.value = '';
        Array.from(subKriteriaSelect.options).forEach(option => option.style.display = 'block');
      }
      updateItemSubKriteriaFilter();
    }

    function updateItemSubKriteriaFilter() {
      const subKriteriaId = document.getElementById('filterSubKriteria').value;
      const itemSubKriteriaSelect = document.getElementById('filterItemSubKriteria');
      const subKriteriaSelect = document.getElementById('filterSubKriteria');

      if (subKriteriaId && subKriteriaId !== '' && !subKriteriaSelect.disabled) {
        itemSubKriteriaSelect.disabled = false;
        Array.from(itemSubKriteriaSelect.options).forEach(option => {
          if (option.value === '') {
            option.style.display = 'block';
            return;
          }
          const subKriteriaIdForOption = option.getAttribute('data-sub-kriteria');
          option.style.display = subKriteriaIdForOption === subKriteriaId ? 'block' : 'none';
        });
      } else {
        itemSubKriteriaSelect.disabled = true;
        itemSubKriteriaSelect.value = '';
        Array.from(itemSubKriteriaSelect.options).forEach(option => option.style.display = 'block');
      }
    }

    // ===== Age Filter Settings =====
    const DEFAULT_AGE_THRESHOLDS = { age1: 3, age2: 7, age3: 30 };
    let currentAgeFilter = null;

    // Get age thresholds from localStorage
    function getAgeThresholds() {
      const saved = localStorage.getItem('documentAgeThresholds');
      if (saved) {
        try {
          return JSON.parse(saved);
        } catch (e) {
          return { ...DEFAULT_AGE_THRESHOLDS };
        }
      }
      return { ...DEFAULT_AGE_THRESHOLDS };
    }

    // Save age thresholds to localStorage
    function saveAgeThresholds(thresholds) {
      localStorage.setItem('documentAgeThresholds', JSON.stringify(thresholds));
    }

    // Update UI labels with current thresholds
    function updateAgeLabels() {
      const thresholds = getAgeThresholds();
      // Update card labels
      document.getElementById('ageLabel1').textContent = thresholds.age1;
      document.getElementById('ageLabel2').textContent = thresholds.age2;
      document.getElementById('ageLabel3').textContent = thresholds.age3;
      // Update chip labels
      document.querySelectorAll('.chip-days-1').forEach(el => el.textContent = thresholds.age1);
      document.querySelectorAll('.chip-days-2').forEach(el => el.textContent = thresholds.age2);
      document.querySelectorAll('.chip-days-3').forEach(el => el.textContent = thresholds.age3);
    }

    // ===== Bagian Summary Banner =====
    function showBagianSummary(stats) {
      const banner = document.getElementById('bagianSummaryBanner');
      document.getElementById('bagianSummaryName').textContent = stats.bagian_name;
      document.getElementById('bagianStatTotal').textContent = formatNumber(stats.total);
      document.getElementById('bagianStatBelum').textContent = formatNumber(stats.belum_siap_bayar);
      document.getElementById('bagianStatSiap').textContent = formatNumber(stats.siap_dibayar);
      document.getElementById('bagianStatSudah').textContent = formatNumber(stats.sudah_dibayar);
      banner.style.display = 'block';
      banner.classList.add('show');
    }

    function hideBagianSummary() {
      const banner = document.getElementById('bagianSummaryBanner');
      banner.classList.remove('show');
      banner.style.display = 'none';
    }

    function formatNumber(num) {
      return new Intl.NumberFormat('id-ID').format(num || 0);
    }

    // Count documents by age - uses data passed from PHP
    function countDocumentsByAge() {
      const thresholds = getAgeThresholds();
      const allDokumenAge = @json($allDokumenUmur ?? []);

      let count1 = 0, count2 = 0, count3 = 0;

      allDokumenAge.forEach(days => {
        if (days > thresholds.age1) count1++;
        if (days > thresholds.age2) count2++;
        if (days > thresholds.age3) count3++;
      });

      document.getElementById('ageCount1').textContent = count1;
      document.getElementById('ageCount2').textContent = count2;
      document.getElementById('ageCount3').textContent = count3;
    }

    // Open age settings modal
    function openAgeSettingsModal() {
      const thresholds = getAgeThresholds();
      document.getElementById('ageSetting1').value = thresholds.age1;
      document.getElementById('ageSetting2').value = thresholds.age2;
      document.getElementById('ageSetting3').value = thresholds.age3;
      document.getElementById('ageSettingsModal').classList.add('show');
    }

    // Close age settings modal
    function closeAgeSettingsModal(event) {
      if (!event || event.target === event.currentTarget) {
        document.getElementById('ageSettingsModal').classList.remove('show');
      }
    }

    // Save age settings
    function saveAgeSettings() {
      const age1 = parseInt(document.getElementById('ageSetting1').value) || 3;
      const age2 = parseInt(document.getElementById('ageSetting2').value) || 7;
      const age3 = parseInt(document.getElementById('ageSetting3').value) || 30;

      saveAgeThresholds({ age1, age2, age3 });
      updateAgeLabels();
      countDocumentsByAge();
      closeAgeSettingsModal();

      // Show feedback
      const saveBtn = document.querySelector('.age-modal-btn.save');
      const originalText = saveBtn.innerHTML;
      saveBtn.innerHTML = '<i class="fas fa-check"></i> Tersimpan!';
      saveBtn.style.background = '#22c55e';
      setTimeout(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.style.background = '';
      }, 1500);
    }

    // Reset age settings to default
    function resetAgeSettings() {
      document.getElementById('ageSetting1').value = DEFAULT_AGE_THRESHOLDS.age1;
      document.getElementById('ageSetting2').value = DEFAULT_AGE_THRESHOLDS.age2;
      document.getElementById('ageSetting3').value = DEFAULT_AGE_THRESHOLDS.age3;
    }

    // Filter by age
    function filterByAge(level) {
      const thresholds = getAgeThresholds();
      let minDays;

      switch (level) {
        case 1: minDays = thresholds.age1; break;
        case 2: minDays = thresholds.age2; break;
        case 3: minDays = thresholds.age3; break;
      }

      // Toggle filter
      if (currentAgeFilter === level) {
        clearAgeFilter();
        return;
      }

      currentAgeFilter = level;

      // Update UI
      document.querySelectorAll('.age-info-card').forEach(card => card.classList.remove('active'));
      document.querySelectorAll('.age-chip').forEach(chip => chip.classList.remove('active'));
      document.getElementById('ageCard' + level).classList.add('active');
      document.getElementById('ageChip' + level).classList.add('active');
      document.getElementById('ageChipClear').style.display = 'flex';

      // Apply filter to visible cards
      filterDocumentsByDays(minDays);
    }

    // Clear age filter
    function clearAgeFilter() {
      currentAgeFilter = null;
      document.querySelectorAll('.age-info-card').forEach(card => card.classList.remove('active'));
      document.querySelectorAll('.age-chip').forEach(chip => chip.classList.remove('active'));
      document.getElementById('ageChipClear').style.display = 'none';

      // Show all cards
      document.querySelectorAll('.doc-card').forEach(card => {
        card.style.display = '';
      });
      document.querySelectorAll('.data-table tbody tr').forEach(row => {
        row.style.display = '';
      });
    }

    // Filter documents by minimum days
    function filterDocumentsByDays(minDays) {
      // Filter card view
      document.querySelectorAll('.doc-card').forEach(card => {
        const ageText = card.querySelector('.doc-card-meta-item .fa-hourglass-half')?.parentElement?.textContent || '';
        const match = ageText.match(/(\d+)\s*hari/);
        const days = match ? parseInt(match[1]) : 0;

        card.style.display = days > minDays ? '' : 'none';
      });

      // Filter table view
      document.querySelectorAll('.data-table tbody tr').forEach(row => {
        const ageCell = row.querySelector('td:nth-child(6)')?.textContent || '';
        const match = ageCell.match(/(\d+)\s*hari/);
        const days = match ? parseInt(match[1]) : 0;

        row.style.display = days > minDays ? '' : 'none';
      });
    }

    // ===== Owner Header Theme Toggle =====
    function toggleOwnerTheme() {
      const html = document.documentElement;
      const isDark = html.classList.toggle('dark');
      localStorage.setItem('darkMode', isDark ? 'true' : 'false');
    }

    // ===== Owner Header Profile Menu =====
    function toggleOwnerProfileMenu() {
      const menu = document.getElementById('ownerProfileMenu');
      menu.classList.toggle('show');
    }

    // Close profile menu when clicking outside
    document.addEventListener('click', function (e) {
      const dropdown = document.querySelector('.header-profile-dropdown');
      const menu = document.getElementById('ownerProfileMenu');
      if (dropdown && menu && !dropdown.contains(e.target)) {
        menu.classList.remove('show');
      }
    });

    // Escape key to close modal
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.getElementById('ageSettingsModal').classList.contains('show')) {
        closeAgeSettingsModal();
      }
    });

    // ===== Initialize =====
    document.addEventListener('DOMContentLoaded', function () {
      // Restore dark mode from localStorage FIRST
      const isDarkMode = localStorage.getItem('darkMode') === 'true';
      if (isDarkMode) {
        document.documentElement.classList.add('dark');
      }

      // Load saved view preference (default: card)
      const savedView = localStorage.getItem('ownerDashboardView') || 'card';
      switchView(savedView);

      // Initialize cascading dropdowns
      updateSubKriteriaFilter();
      updateFilterCount();

      // Auto-open filter panel if filters are active
      const filterCount = parseInt(document.getElementById('filterCount').getAttribute('data-count') || 0);
      if (filterCount > 0) {
        document.getElementById('filterPanel').classList.add('open');
      }

      // Enable sub kriteria if kriteria CF is selected
      const kriteriaCfSelect = document.getElementById('filterKriteriaCf');
      if (kriteriaCfSelect && kriteriaCfSelect.value) {
        updateSubKriteriaFilter();
      }

      // Initialize age filter
      updateAgeLabels();
      countDocumentsByAge();

      // Highlight active stat card based on current URL status
      const urlParams = new URLSearchParams(window.location.search);
      const currentStatus = urlParams.get('status') || '';
      const statusToCardMap = {
        'semua': 'all',
        'belum_siap': 'belum_siap',
        'siap_dibayar': 'siap_dibayar',
        'sudah_dibayar': 'sudah_dibayar'
      };
      if (currentStatus && statusToCardMap[currentStatus]) {
        highlightActiveStatCard(statusToCardMap[currentStatus]);
      }

      // Create top progress bar element
      const progressBar = document.createElement('div');
      progressBar.id = 'ajaxProgressBar';
      progressBar.innerHTML = '<div class="progress-fill"></div>';
      document.body.appendChild(progressBar);

      // Create loading overlay element
      const overlay = document.createElement('div');
      overlay.id = 'ajaxLoadingOverlay';
      overlay.innerHTML = `
                    <div class="ajax-spinner">
                      <div class="spinner-ring"></div>
                      <div class="spinner-text">Memuat data...</div>
                      <div class="spinner-subtext">Mohon tunggu sebentar</div>
                    </div>
                  `;
      document.querySelector('.main-content').appendChild(overlay);
    });

    // ===== Highlight Active Stat Card =====
    function highlightActiveStatCard(filterType) {
      // Remove active class from all clickable stat cards
      document.querySelectorAll('.stat-card.clickable').forEach(card => {
        card.classList.remove('card-active');
      });
      // Add active class to the matching card
      if (filterType) {
        const activeCard = document.querySelector(`.stat-card.clickable[data-filter="${filterType}"]`);
        if (activeCard) {
          activeCard.classList.add('card-active');
        }
      }
    }

    // ===== Filter By Card Function (AJAX) =====
    function filterByCard(type) {
      const statusInput = document.getElementById('statusInput');
      const statusPembayaranSelect = document.querySelector('[name="filter_status_pembayaran"]');
      const extra = {};

      if (type === 'all') {
        statusInput.value = 'semua';
        extra['status'] = 'semua';
        if (statusPembayaranSelect) statusPembayaranSelect.value = '';
      } else if (type === 'belum_siap') {
        statusInput.value = 'belum_siap';
        extra['status'] = 'belum_siap';
        extra['filter_status_pembayaran'] = 'belum_dibayar';
        if (statusPembayaranSelect) statusPembayaranSelect.value = 'belum_dibayar';
      } else if (type === 'siap_dibayar') {
        statusInput.value = 'siap_dibayar';
        extra['status'] = 'siap_dibayar';
        extra['filter_status_pembayaran'] = 'siap_dibayar';
        if (statusPembayaranSelect) statusPembayaranSelect.value = 'siap_dibayar';
      } else if (type === 'sudah_dibayar') {
        statusInput.value = 'sudah_dibayar';
        extra['status'] = 'sudah_dibayar';
        extra['filter_status_pembayaran'] = 'sudah_dibayar';
        if (statusPembayaranSelect) statusPembayaranSelect.value = 'sudah_dibayar';
      }

      // Update chips UI
      document.querySelectorAll('.chip').forEach(chip => chip.classList.remove('active'));
      const chipMap = { 'all': 0, 'belum_siap': 1, 'siap_dibayar': 2, 'sudah_dibayar': 3 };
      const chipIdx = chipMap[type];
      const chips = document.querySelectorAll('.filter-chips .chip');
      if (chipIdx !== undefined && chips[chipIdx]) chips[chipIdx].classList.add('active');

      highlightActiveStatCard(type);
      applyFilter(extra);
    }

    // ===== AJAX Render Functions =====
    function formatRupiah(value) {
      return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function buildStepperHtml(progressPct, isPaid) {
      const currentStep = Math.min(5, Math.max(1, Math.ceil((progressPct || 0) / 20)));
      const labels = ['Operator', 'Verifikasi', 'Perpajakan', 'Akutansi', 'Pembayaran'];
      let html = '<div class="workflow-stepper"><div class="stepper-label">Progres Alur</div><div class="stepper-track">';
      for (let i = 1; i <= 5; i++) {
        if (isPaid) {
          html += `<div class="stepper-step completed" data-tooltip="${labels[i - 1]}"><i class="fas fa-check"></i></div>`;
        } else if (i < currentStep) {
          html += `<div class="stepper-step completed" data-tooltip="${labels[i - 1]}"><i class="fas fa-check"></i></div>`;
        } else if (i === currentStep) {
          html += `<div class="stepper-step active" data-tooltip="${labels[i - 1]}">${i}</div>`;
        } else {
          html += `<div class="stepper-step" data-tooltip="${labels[i - 1]}">${i}</div>`;
        }
      }
      html += '</div></div>';
      return html;
    }

    function renderCardView(documents) {
      const container = document.getElementById('cardView');
      if (!documents || documents.length === 0) {
        container.innerHTML = `
                          <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                            <div class="empty-state-title">Tidak ada dokumen</div>
                            <div class="empty-state-text">Dokumen akan ditampilkan di sini ketika tersedia</div>
                          </div>`;
        return;
      }

      let html = '<div class="cards-grid">';
      documents.forEach(doc => {
        const isOverdue = doc.is_overdue;
        const isPaid = doc.is_paid;
        const cardClass = `doc-card ${isOverdue ? 'overdue' : ''} ${isPaid ? 'completed' : ''}`;

        html += `<div class="${cardClass}" onclick="navigateToWorkflow('${doc.id}')">`;

        if (isOverdue) html += '<span class="overdue-badge">TERLAMBAT</span>';
        if (isPaid) html += '<span class="paid-stamp">SUDAH DIBAYAR</span>';

        // Bell urgency button
        const urgencyActive = doc.urgency_active;
        const bellTitle = urgencyActive
          ? 'Notifikasi sudah dikirim – menunggu penyelesaian'
          : 'Kirim pengingat darurat ke penanggung jawab dokumen ini';
        const bellClass = 'urgency-bell-btn' + (urgencyActive ? ' active' : '');
        const bellDisabled = urgencyActive ? 'disabled' : '';
        const handlerDisplay = (doc.current_handler_display || '-').replace(/'/g, "\\'");
        const nomorAgendaEsc = (doc.nomor_agenda || '-').replace(/'/g, "\\'");
        html += `<button
          class="${bellClass}"
          onclick="event.stopPropagation(); openUrgencyModal('${doc.id}', '${nomorAgendaEsc}', '${handlerDisplay}')"
          title="${bellTitle}"
          ${bellDisabled}
          data-doc-id="${doc.id}"
          id="bell-btn-${doc.id}">
          <i class="fas fa-bell"></i>
        </button>`;

        html += '<div class="doc-card-header"><div>';
        html += `<div class="doc-card-title">${doc.nomor_agenda || '-'}</div>`;
        html += `<div class="doc-card-subtitle">SPP: ${doc.nomor_spp || '-'}</div>`;
        html += '</div>';
        if (isPaid) html += '<span class="paid-indicator"><i class="fas fa-check-circle"></i> Dibayar</span>';
        html += '</div>';

        html += `<div class="doc-card-value">${formatRupiah(doc.nilai_rupiah)}</div>`;

        // Meta
        const handler = doc.current_handler_display || '-';
        html += '<div class="doc-card-meta">';
        html += `<div class="doc-card-meta-item"><i class="fas fa-user"></i><span>Posisi:</span><span class="handler-avatar">${handler.charAt(0)}</span><span>${handler}</span></div>`;

        if (doc.deadline_info) {
          const dlColor = isOverdue ? 'var(--danger-color)' : 'inherit';
          html += `<div class="doc-card-meta-item"><i class="fas fa-clock"></i><span>Batas Waktu:</span><span style="font-weight:500;color:${dlColor}">${doc.deadline_info.text || '-'}</span></div>`;
        }
        if (doc.umur_dokumen) {
          const umurColor = doc.umur_dokumen.is_paid ? 'var(--success-color)' : 'inherit';
          let umurExtra = doc.umur_dokumen.is_paid ? ' <i class="fas fa-check-circle" style="margin-left:4px;color:var(--success-color);"></i>' : '';
          html += `<div class="doc-card-meta-item"><i class="fas fa-hourglass-half"></i><span>Umur Dokumen:</span><span style="font-weight:500;color:${umurColor}">${doc.umur_dokumen.text || '-'}${umurExtra}</span></div>`;
        }
        html += '</div>';

        // Stepper
        html += buildStepperHtml(doc.progress_percentage, isPaid);
        html += '</div>';
      });
      html += '</div>';

      // Pagination placeholder will be rendered separately
      container.innerHTML = html + '<div id="cardPagination"></div>';
    }

    function renderTableView(documents) {
      const container = document.getElementById('tableView');
      if (!documents || documents.length === 0) {
        container.innerHTML = `
                          <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                            <div class="empty-state-title">Tidak ada dokumen</div>
                            <div class="empty-state-text">Dokumen akan ditampilkan di sini ketika tersedia</div>
                          </div>`;
        return;
      }

      let html = '<div class="table-container"><table class="data-table"><thead><tr>';
      html += '<th>No. Dokumen</th><th>Tgl Masuk</th><th>Nilai (Rp)</th><th>Posisi</th><th>Status</th><th>Progres</th><th>Aksi</th>';
      html += '</tr></thead><tbody>';

      documents.forEach(doc => {
        const rowClass = doc.is_overdue ? 'overdue' : '';
        const handler = doc.current_handler_display || '-';
        const pct = doc.progress_percentage || 0;
        const statusLabel = pct >= 100 ? 'Selesai' : 'Proses';
        const statusClass = pct >= 100 ? 'selesai' : 'proses';

        html += `<tr class="${rowClass}" onclick="navigateToWorkflow('${doc.id}')">`;
        html += `<td><div class="doc-number">${doc.nomor_agenda || '-'}</div><div class="doc-spp">${doc.nomor_spp || '-'}</div></td>`;
        html += `<td>${doc.tanggal_masuk || '-'}</td>`;
        html += `<td><span class="doc-value">${formatRupiah(doc.nilai_rupiah)}</span></td>`;
        html += `<td><div style="display:flex;align-items:center;gap:6px;"><span class="handler-avatar">${handler.charAt(0)}</span><span>${handler}</span></div></td>`;
        html += `<td><span class="status-badge ${statusClass}">${statusLabel}</span></td>`;
        html += `<td><div class="progress-bar-mini"><div class="fill" style="width:${pct}%;background:${doc.progress_color || '#083E40'}"></div></div><div class="progress-text">${pct}%</div></td>`;
        html += `<td><button class="btn-view" onclick="event.stopPropagation();navigateToWorkflow('${doc.id}')">Lihat</button></td>`;
        html += '</tr>';
      });

      html += '</tbody></table></div>';
      container.innerHTML = html + '<div id="tablePagination"></div>';
    }

    function renderPagination(pg) {
      if (!pg || pg.total === 0) {
        ['cardPagination', 'tablePagination'].forEach(id => {
          const el = document.getElementById(id);
          if (el) el.innerHTML = '';
        });
        return;
      }

      const totalFormatted = Number(pg.total).toLocaleString('id-ID');
      const prevDisabled = pg.current_page <= 1 ? 'disabled' : '';
      const nextDisabled = pg.current_page >= pg.last_page ? 'disabled' : '';

      const html = `
                        <div class="pagination-footer">
                          <div class="pagination-footer-left">
                            <label class="pagination-label">Baris per halaman:</label>
                            <select class="pagination-select" onchange="changePerPage(this.value)">
                              <option value="10" ${pg.per_page == 10 ? 'selected' : ''}>10</option>
                              <option value="25" ${pg.per_page == 25 ? 'selected' : ''}>25</option>
                              <option value="50" ${pg.per_page == 50 ? 'selected' : ''}>50</option>
                              <option value="100" ${pg.per_page == 100 ? 'selected' : ''}>100</option>
                              <option value="all" ${pg.per_page >= pg.total ? 'selected' : ''}>Semua</option>
                            </select>
                            <span class="pagination-summary">Menampilkan ${pg.from || 0} - ${pg.to || 0} dari ${totalFormatted} hasil</span>
                          </div>
                          <div class="pagination-footer-right">
                            <button class="pagination-btn" onclick="goToPage(${pg.current_page - 1})" ${prevDisabled} title="Halaman sebelumnya">
                              <i class="fas fa-chevron-left"></i>
                            </button>
                            <input type="number" class="pagination-page-input" value="${pg.current_page}" min="1" max="${pg.last_page}"
                              onchange="goToPage(this.value)" onkeypress="if(event.key==='Enter')goToPage(this.value)">
                            <span class="pagination-total-pages">dari ${pg.last_page} halaman</span>
                            <button class="pagination-btn" onclick="goToPage(${pg.current_page + 1})" ${nextDisabled} title="Halaman berikutnya">
                              <i class="fas fa-chevron-right"></i>
                            </button>
                          </div>
                        </div>`;

      ['cardPagination', 'tablePagination'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = html;
      });
    }

    // ===== AJAX-aware pagination =====
    function changePerPage(value) {
      applyFilter({ per_page: value, page: '1' });
    }

    function goToPage(page) {
      let pageNum = parseInt(page);
      if (isNaN(pageNum) || pageNum < 1) pageNum = 1;
      applyFilter({ page: String(pageNum) });
    }

    // ===== Loading overlay with progress bar =====
    let _progressInterval = null;
    let _progressValue = 0;

    function showLoadingOverlay() {
      // Show top progress bar
      const bar = document.getElementById('ajaxProgressBar');
      if (bar) {
        bar.classList.add('show');
        const fill = bar.querySelector('.progress-fill');
        _progressValue = 0;
        if (fill) fill.style.width = '0%';

        // Simulate progress: fast start, then slow
        clearInterval(_progressInterval);
        _progressInterval = setInterval(() => {
          if (_progressValue < 30) {
            _progressValue += 3;
          } else if (_progressValue < 60) {
            _progressValue += 1.5;
          } else if (_progressValue < 85) {
            _progressValue += 0.5;
          } else if (_progressValue < 95) {
            _progressValue += 0.1;
          }
          if (fill) fill.style.width = _progressValue + '%';
        }, 100);
      }

      // Show overlay
      const el = document.getElementById('ajaxLoadingOverlay');
      if (el) el.classList.add('show');
    }

    function hideLoadingOverlay() {
      clearInterval(_progressInterval);

      // Complete the progress bar
      const bar = document.getElementById('ajaxProgressBar');
      if (bar) {
        const fill = bar.querySelector('.progress-fill');
        if (fill) {
          fill.style.width = '100%';
          fill.style.transition = 'width 0.3s ease';
        }
        setTimeout(() => {
          bar.classList.remove('show');
          if (fill) {
            fill.style.width = '0%';
            fill.style.transition = '';
          }
        }, 400);
      }

      // Hide overlay
      const el = document.getElementById('ajaxLoadingOverlay');
      if (el) el.classList.remove('show');
    }
  </script>

  <style>
    /* ===== Top Progress Bar ===== */
    #ajaxProgressBar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      z-index: 99999;
      background: rgba(0, 0, 0, 0.08);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }

    #ajaxProgressBar.show {
      opacity: 1;
    }

    #ajaxProgressBar .progress-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, #083E40, #0ea5e9, #10b981);
      background-size: 200% 100%;
      animation: progressShimmer 1.5s ease infinite;
      border-radius: 0 2px 2px 0;
      transition: width 0.15s ease;
      box-shadow: 0 0 10px rgba(14, 165, 233, 0.5), 0 0 5px rgba(16, 185, 129, 0.3);
    }

    @keyframes progressShimmer {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }

    /* ===== Loading Overlay ===== */
    #ajaxLoadingOverlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(248, 250, 252, 0.75);
      backdrop-filter: blur(2px);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    #ajaxLoadingOverlay.show {
      display: flex;
    }

    .ajax-spinner {
      background: white;
      padding: 2rem 2.5rem;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.75rem;
      animation: spinnerPop 0.25s ease;
    }

    @keyframes spinnerPop {
      from {
        transform: scale(0.9);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .spinner-ring {
      width: 44px;
      height: 44px;
      border: 4px solid #e2e8f0;
      border-top-color: #083E40;
      border-right-color: #0ea5e9;
      border-radius: 50%;
      animation: spinnerRotate 0.8s linear infinite;
    }

    @keyframes spinnerRotate {
      to {
        transform: rotate(360deg);
      }
    }

    .spinner-text {
      font-size: 1rem;
      font-weight: 600;
      color: #1e293b;
    }

    .spinner-subtext {
      font-size: 0.8rem;
      color: #94a3b8;
    }

    /* ===== Dark Mode ===== */
    html.dark #ajaxProgressBar {
      background: rgba(255, 255, 255, 0.05);
    }

    html.dark #ajaxLoadingOverlay {
      background: rgba(15, 23, 42, 0.75);
    }

    html.dark .ajax-spinner {
      background: #1e293b;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    html.dark .spinner-ring {
      border-color: #334155;
      border-top-color: #0ea5e9;
      border-right-color: #10b981;
    }

    html.dark .spinner-text {
      color: #f1f5f9;
    }

    html.dark .spinner-subtext {
      color: #64748b;
    }
  </style>
{{-- ===== Timeline / Riwayat Modal ===== --}}
<style>
  /* ── Riwayat button in table ── */
  .btn-riwayat {
    width: 32px; height: 32px; border-radius: 8px;
    background: #f1f5f9; border: 1px solid #e2e8f0;
    color: #64748b; cursor: pointer; font-size: 13px;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.18s ease; padding: 0; flex-shrink: 0;
  }
  .btn-riwayat:hover { background: #e0f2fe; border-color: #38bdf8; color: #0284c7; transform: scale(1.08); }

  /* ── Modal backdrop + box ── */
  #timelineModal {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 10999;
    align-items: center; justify-content: center;
    animation: tlFade .2s ease;
  }
  #timelineModal.open { display: flex; }
  @keyframes tlFade { from { opacity: 0; } to { opacity: 1; } }

  .tl-box {
    background: #fff; border-radius: 18px; width: 560px; max-width: 95vw;
    max-height: 85vh; overflow: hidden;
    display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    animation: tlSlide .25s ease;
  }
  @keyframes tlSlide { from { transform: translateY(24px); opacity:0; } to { transform: translateY(0); opacity:1; } }

  html.dark .tl-box { background: #1e293b; color: #f1f5f9; }

  /* ── Header ── */
  .tl-header {
    padding: 18px 22px 14px;
    border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
  }
  html.dark .tl-header { border-color: #334155; }
  .tl-header h3 { margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; }
  html.dark .tl-header h3 { color: #f1f5f9; }
  .tl-header p { margin: 2px 0 0; font-size: 12px; color: #64748b; }
  .tl-close-btn {
    width: 32px; height: 32px; border-radius: 8px;
    background: #f1f5f9; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #64748b; font-size: 15px; transition: all .18s;
  }
  .tl-close-btn:hover { background: #fee2e2; color: #ef4444; }
  html.dark .tl-close-btn { background: #334155; color: #94a3b8; }

  /* ── Body scroll ── */
  .tl-body { padding: 20px 24px; overflow-y: auto; flex: 1; }

  /* ── Empty state ── */
  .tl-empty {
    text-align: center; padding: 40px 20px; color: #94a3b8;
  }
  .tl-empty i { font-size: 36px; margin-bottom: 10px; display: block; }

  /* ── Timeline nodes ── */
  .tl-list { list-style: none; margin: 0; padding: 0; position: relative; }
  .tl-list::before {
    content: ''; position: absolute;
    left: 19px; top: 28px; bottom: 20px; width: 2px;
    background: #e2e8f0;
  }
  html.dark .tl-list::before { background: #334155; }

  .tl-item {
    display: flex; gap: 14px; margin-bottom: 22px;
    position: relative;
  }

  /* dot */
  .tl-dot {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 14px; position: relative; z-index: 1;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
  }

  /* pulse for last node */
  .tl-dot.pulse::after {
    content: ''; position: absolute;
    width: 100%; height: 100%; border-radius: 50%;
    animation: dotPulse 1.5s ease-in-out infinite;
  }
  @keyframes dotPulse {
    0%   { box-shadow: 0 0 0 0 rgba(59,130,246,.5); }
    70%  { box-shadow: 0 0 0 10px rgba(59,130,246,0); }
    100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
  }

  /* card */
  .tl-card {
    flex: 1; background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 10px 14px;
    transition: border-color .15s;
  }
  html.dark .tl-card { background: #0f172a; border-color: #334155; }
  .tl-card:hover { border-color: #94a3b8; }

  .tl-card.slowest { border-color: #f87171 !important; background: #fff5f5 !important; }
  html.dark .tl-card.slowest { background: #2d1515 !important; border-color: #b91c1c !important; }

  .tl-action { font-weight: 700; font-size: 13.5px; color: #1e293b; }
  html.dark .tl-action { color: #f1f5f9; }
  .tl-meta { font-size: 12px; color: #64748b; margin-top: 3px; }
  .tl-duration {
    display: inline-block; margin-top: 5px;
    font-size: 11px; font-weight: 600; padding: 2px 8px;
    border-radius: 9999px; background: #e0f2fe; color: #0284c7;
  }
  .tl-duration.slow { background: #fee2e2; color: #b91c1c; }
  .tl-urgency-badge {
    display: inline-flex; align-items: center; gap: 4px;
    margin-top: 4px; font-size: 11px; font-weight: 600;
    color: #9a3412; background: #fff7ed; border: 1px solid #fed7aa;
    border-radius: 9999px; padding: 2px 8px;
  }

  /* ── Summary footer ── */
  .tl-summary {
    margin-top: 18px; padding: 14px 16px;
    background: #f1f5f9; border-radius: 10px;
    display: flex; gap: 24px; flex-wrap: wrap;
  }
  html.dark .tl-summary { background: #0f172a; }
  .tl-stat { display: flex; flex-direction: column; }
  .tl-stat-val { font-size: 18px; font-weight: 800; color: #0f172a; }
  html.dark .tl-stat-val { color: #f1f5f9; }
  .tl-stat-lbl { font-size: 11px; color: #94a3b8; margin-top: 2px; }

  /* ── Loading spinner ── */
  .tl-loading {
    text-align: center; padding: 48px 20px; color: #94a3b8;
  }
  .tl-spinner {
    width: 36px; height: 36px; border: 3px solid #e2e8f0;
    border-top-color: #0284c7; border-radius: 50%;
    animation: spin .7s linear infinite;
    margin: 0 auto 12px;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>

{{-- Modal shell --}}
<div id="timelineModal" onclick="if(event.target===this)closeTimelineModal()" aria-modal="true" role="dialog">
  <div class="tl-box">
    <div class="tl-header">
      <div>
        <h3><i class="fas fa-history" style="margin-right:8px;color:#0284c7;"></i>Riwayat Perjalanan Dokumen</h3>
        <p id="tlModalSubtitle">Memuat data...</p>
      </div>
      <button class="tl-close-btn" onclick="closeTimelineModal()" title="Tutup"><i class="fas fa-times"></i></button>
    </div>
    <div class="tl-body" id="tlModalBody">
      <div class="tl-loading">
        <div class="tl-spinner"></div>
        <div>Memuat riwayat...</div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const modal = document.getElementById('timelineModal');
  const body  = document.getElementById('tlModalBody');
  const sub   = document.getElementById('tlModalSubtitle');

  window.openTimelineModal = function(docId, nomorAgenda) {
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    sub.textContent = 'Agenda: ' + nomorAgenda;
    body.innerHTML = '<div class="tl-loading"><div class="tl-spinner"></div><div>Memuat riwayat...</div></div>';

    fetch('/owner/dokumen/' + docId + '/history', {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success) { body.innerHTML = '<div class="tl-empty"><i class="fas fa-exclamation-circle"></i>Gagal memuat data.</div>'; return; }
      renderTimeline(data);
    })
    .catch(function() {
      body.innerHTML = '<div class="tl-empty"><i class="fas fa-wifi"></i>Koneksi gagal. Coba lagi.</div>';
    });
  };

  window.closeTimelineModal = function() {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  };

  // Close on Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeTimelineModal();
  });

  function renderTimeline(data) {
    const nodes   = data.nodes   || [];
    const doc     = data.document || {};
    const summary = data.summary  || {};

    sub.textContent = 'Agenda: ' + (doc.nomor_agenda || '-') + '  •  ' + (doc.dibayar_kepada || '');

    if (!nodes.length) {
      body.innerHTML = '<div class="tl-empty"><i class="fas fa-clock"></i><div>Belum ada riwayat untuk dokumen ini.<br><small style="color:#94a3b8">Riwayat akan tercatat mulai dari sekarang.</small></div></div>';
      return;
    }

    // Build list
    let html = '<ul class="tl-list">';
    nodes.forEach(function(node, idx) {
      const isLast    = node.is_last;
      const isSlowest = node.is_slowest;
      const dotStyle  = 'background:' + node.color + ';';
      const dotClass  = isLast ? 'tl-dot pulse' : 'tl-dot';
      const cardClass = 'tl-card' + (isSlowest ? ' slowest' : '');

      // Check urgency marker in metadata
      const meta = node.metadata || {};
      const urgencyHtml = meta.urgency ? '<span class="tl-urgency-badge"><i class="fas fa-bolt"></i> Urgency dikirim</span>' : '';

      // Duration badge
      let durationHtml = '';
      if (node.duration_label) {
        const cls = isSlowest ? 'tl-duration slow' : 'tl-duration';
        durationHtml = '<span class="' + cls + '">' + (isSlowest ? '⚠ Terlama: ' : '⏱ Durasi: ') + node.duration_label + '</span>';
      }

      html += '<li class="tl-item">' +
        '<div class="' + dotClass + '" style="' + dotStyle + '"><i class="fas ' + node.icon + '"></i></div>' +
        '<div class="' + cardClass + '">' +
          '<div class="tl-action">' + escHtml(node.action_label) + '</div>' +
          '<div class="tl-meta"><i class="fas fa-user" style="margin-right:4px"></i>' + escHtml(node.actor_label) + ' &nbsp;·&nbsp; <i class="fas fa-calendar" style="margin-right:4px"></i>' + escHtml(node.action_at) + '</div>' +
          urgencyHtml +
          durationHtml +
        '</div>' +
      '</li>';
    });
    html += '</ul>';

    // Summary
    html += '<div class="tl-summary">' +
      '<div class="tl-stat"><div class="tl-stat-val">' + summary.total_events + '</div><div class="tl-stat-lbl">Events Tercatat</div></div>' +
      '<div class="tl-stat"><div class="tl-stat-val">' + (summary.total_duration || '-') + '</div><div class="tl-stat-lbl">Total Durasi</div></div>' +
    '</div>';

    body.innerHTML = html;
  }

  function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>

@endsection
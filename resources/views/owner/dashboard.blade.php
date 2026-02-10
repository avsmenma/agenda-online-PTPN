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
      <div class="stat-card total clickable" onclick="filterByCard('all')" title="Klik untuk melihat semua dokumen">
        <div class="stat-content">
          <div class="stat-label">Total Dokumen</div>
          <div class="stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon total">
          <i class="fas fa-file-alt"></i>
        </div>
      </div>

      <div class="stat-card proses clickable" onclick="filterByCard('belum_siap')"
        title="Klik untuk filter dokumen belum siap bayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Belum Siap Bayar</div>
          <div class="stat-value">{{ number_format($dokumenBelumSiapBayar ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon proses">
          <i class="fas fa-clock"></i>
        </div>
      </div>

      <div class="stat-card siap clickable" onclick="filterByCard('siap_dibayar')"
        title="Klik untuk filter dokumen siap bayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Siap Bayar</div>
          <div class="stat-value">{{ number_format($dokumenSiapBayar ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon siap">
          <i class="fas fa-clipboard-check"></i>
        </div>
      </div>

      <div class="stat-card selesai clickable" onclick="filterByCard('sudah_dibayar')"
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
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}"
          placeholder="Cari nomor agenda, SPP, uraian..." onkeypress="if(event.key==='Enter') applyFilter()">
      </div>

      {{-- Quick Filter Chips --}}
      <div class="filter-chips">
        <button class="chip {{ request('status') == '' || request('status') == 'belum_siap' ? 'active' : '' }}"
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
              <select name="filter_bagian" onchange="updateFilterCount()">
                <option value="">Semua Bagian</option>
                @foreach($filterData['bagian'] ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_bagian') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            {{-- Vendor --}}
            <div class="filter-group">
              <label><i class="fas fa-handshake"></i> Vendor</label>
              <select name="filter_vendor" onchange="updateFilterCount()">
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
                onchange="updateSubKriteriaFilter(); updateFilterCount();">
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
                onchange="updateItemSubKriteriaFilter(); updateFilterCount();" disabled>
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
              <select name="filter_item_sub_kriteria" id="filterItemSubKriteria" onchange="updateFilterCount();" disabled>
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
              <select name="filter_kebun" onchange="updateFilterCount()">
                <option value="">Semua Kebun</option>
                @foreach($filterData['kebun'] ?? [] as $key => $value)
                  <option value="{{ $key }}" {{ request('filter_kebun') == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>

            {{-- Status Pembayaran --}}
            <div class="filter-group">
              <label><i class="fas fa-money-bill-wave"></i> Status Pembayaran</label>
              <select name="filter_status_pembayaran" onchange="updateFilterCount()">
                <option value="">Semua Status</option>
                <option value="belum_dibayar" {{ request('filter_status_pembayaran') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="siap_dibayar" {{ request('filter_status_pembayaran') == 'siap_dibayar' ? 'selected' : '' }}>
                  Siap Dibayar</option>
                <option value="sudah_dibayar" {{ request('filter_status_pembayaran') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
              </select>
            </div>
          </div>

          {{-- Filter Actions --}}
          <div class="filter-actions">
            <button type="button" class="btn-filter btn-reset" onclick="resetFilters()">
              <i class="fas fa-redo"></i> Reset
            </button>
            <button type="button" class="btn-filter btn-apply" onclick="applyFilter()">
              <i class="fas fa-check"></i> Terapkan
            </button>
          </div>

          {{-- Active Filter Tags --}}
          <div class="active-filters" id="activeFilters"></div>
        </div>
      </form>
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
                    <button class="btn-view" onclick="event.stopPropagation(); navigateToWorkflow('{{ $dokumen['id'] }}')">
                      Lihat
                    </button>
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

  <script>
    // ===== Navigation ==       ===
    function navigateToWorkflow(id) {
      window.location.href = '{{ url("/owner/workflow") }}/' + id;
    }

    // ===== Filter by Card Click =====
    function filterByCard(status) {
      if (status === 'all') {
        // Clear all status filters and reload
        window.location.href = '{{ url("/owner/dokumen") }}';
      } else {
        document.getElementById('statusInput').value = status;
        document.querySelectorAll('.chip').forEach(chip => chip.classList.remove('active'));
        // Find and activate the correct chip
        const chips = document.querySelectorAll('.chip');
        chips.forEach(chip => {
          if (chip.textContent.includes('Belum Siap') && status === 'belum_siap') chip.classList.add('active');
          if (chip.textContent.includes('Siap Dibayar') && !chip.textContent.includes('Belum') && status === 'siap_dibayar') chip.classList.add('active');
          if (chip.textContent.includes('Sudah Dibayar') && status === 'sudah_dibayar') chip.classList.add('active');
        });
        applyFilter();
      }
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
      applyFilter();
    }

    function applyFilter() {
      const form = document.getElementById('filterForm');
      const searchInput = document.getElementById('searchInput');

      // Add search value to form
      let searchHidden = form.querySelector('input[name="search"]');
      if (!searchHidden) {
        searchHidden = document.createElement('input');
        searchHidden.type = 'hidden';
        searchHidden.name = 'search';
        form.appendChild(searchHidden);
      }
      searchHidden.value = searchInput.value;

      form.submit();
    }

    function resetFilters() {
      window.location.href = '{{ url("/owner/dokumen") }}';
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
      updateSubKriteriaFilter(); \n        updateFilterCount();

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
    });

    // ===== Filter By Card Function =====
    function filterByCard(type) {
      const baseUrl = '{{ url("/owner/dokumen") }}';
      const params = new URLSearchParams(window.location.search);

      // Update or remove status_pembayaran parameter based on filter type
      if (type === 'all') {
        // Show all documents - remove status filter
        params.delete('filter_status_pembayaran');
      } else if (type === 'belum_siap') {
        params.set('filter_status_pembayaran', 'belum_dibayar');
      } else if (type === 'siap_dibayar') {
        params.set('filter_status_pembayaran', 'siap_dibayar');
      } else if (type === 'sudah_dibayar') {
        params.set('filter_status_pembayaran', 'sudah_dibayar');
      }

      // Navigate to URL with updated parameters
      const queryString = params.toString();
      window.location.href = queryString ? `${baseUrl}?${queryString}` : baseUrl;
    }
  </script>
@endsection
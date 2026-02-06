@extends('layouts/app')

@section('content')
  {{-- External CSS for clean, maintainable styling --}}
  <link rel="stylesheet" href="{{ asset('css/owner-dokumen.css') }}">

  <div class="owner-dashboard">

    {{-- ===== Header Section ===== --}}
    <div class="dashboard-header">
      <div class="header-icon">
        <i class="fas fa-chart-line"></i>
      </div>
      <div class="header-content">
        <h1>Dashboard Kabag Keuangan</h1>
        <p>Pantau dan kelola semua dokumen perusahaan dengan mudah</p>
      </div>
    </div>

    {{-- ===== Statistics Cards ===== --}}
    <div class="stats-grid">
      <div class="stat-card clickable" onclick="filterByCard('all')" title="Klik untuk melihat semua dokumen">
        <div class="stat-content">
          <div class="stat-label">Total Dokumen</div>
          <div class="stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon total">
          <i class="fas fa-file-alt"></i>
        </div>
      </div>

      <div class="stat-card clickable" onclick="filterByCard('belum_siap')"
        title="Klik untuk filter dokumen belum siap bayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Belum Siap Bayar</div>
          <div class="stat-value">{{ number_format($dokumenBelumSiapBayar ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon proses">
          <i class="fas fa-clock"></i>
        </div>
      </div>

      <div class="stat-card clickable" onclick="filterByCard('siap_dibayar')"
        title="Klik untuk filter dokumen siap bayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Siap Bayar</div>
          <div class="stat-value">{{ number_format($dokumenSiapBayar ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon siap">
          <i class="fas fa-clipboard-check"></i>
        </div>
      </div>

      <div class="stat-card clickable" onclick="filterByCard('sudah_dibayar')"
        title="Klik untuk filter dokumen sudah dibayar">
        <div class="stat-content">
          <div class="stat-label">Dokumen Sudah Dibayar</div>
          <div class="stat-value">{{ number_format($dokumenSelesai ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon selesai">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-label">Total Nilai (Rp)</div>
          <div class="stat-value small">Rp{{ number_format($totalNilai ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-icon nilai">
          <i class="fas fa-money-bill-wave"></i>
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
    // ===== Navigation =====
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

    // ===== Initialize =====
    document.addEventListener('DOMContentLoaded', function () {
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
    });
  </script>
@endsection
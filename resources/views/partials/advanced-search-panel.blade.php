{{-- Advanced Search Panel for Team Verifikasi --}}
<div class="advanced-search-container" style="margin-bottom: 20px;">
    <!-- Search Bar with Toggle -->
    <div class="search-header" style="display: flex; gap: 10px; margin-bottom: 15px;">
        <div class="input-group" style="flex: 1;">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" class="form-control" id="advancedSearchInput"
                placeholder="Cari nomor SPP, agenda, vendor, uraian..." style="border-radius: 0 8px 8px 0;">
        </div>
        <button class="btn btn-outline-primary" id="toggleFilters" style="border-radius: 8px;">
            <i class="fa-solid fa-filter"></i> Filters
        </button>
        <button class="btn btn-primary" id="searchButton" style="border-radius: 8px;">
            <i class="fa-solid fa-search"></i> Search
        </button>
    </div>

    <!-- Advanced Filters Panel (Collapsible) -->
    <div id="advancedFiltersPanel"
        style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
        <div class="row g-3">
            <!-- Tahun Filter -->
            <div class="col-md-3">
                <label class="form-label fw-bold">📅 Tahun</label>
                <select class="form-select" id="filterTahun" multiple size="3">
                    <!-- Dynamically loaded -->
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label class="form-label fw-bold">📊 Status</label>
                <div id="statusFilters" style="max-height: 120px; overflow-y: auto;">
                    <!-- Dynamically loaded checkboxes -->
                </div>
            </div>

            <!-- Bagian Filter -->
            <div class="col-md-3">
                <label class="form-label fw-bold">🏢 Bagian</label>
                <select class="form-select" id="filterBagian" multiple size="3">
                    <!-- Dynamically loaded -->
                </select>
            </div>

            <!-- Deadline Quick Filters -->
            <div class="col-md-3">
                <label class="form-label fw-bold">⏰ Deadline</label>
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-outline-success btn-sm deadline-filter" data-deadline="aman">
                        🟢 AMAN (&lt;24h)
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm deadline-filter"
                        data-deadline="peringatan">
                        🟡 PERINGATAN (1-3d)
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm deadline-filter"
                        data-deadline="terlambat">
                        🔴 TERLAMBAT (&gt;3d)
                    </button>
                </div>
            </div>

            <!-- Nilai Range -->
            <div class="col-md-6">
                <label class="form-label fw-bold">💰 Nilai Rupiah</label>
                <div class="input-group">
                    <span class="input-group-text">Min</span>
                    <input type="number" class="form-control" id="nilaiMin" placeholder="0">
                    <span class="input-group-text">Max</span>
                    <input type="number" class="form-control" id="nilaiMax" placeholder="999999999">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button class="btn btn-success flex-fill" id="applyFilters">
                    <i class="fa-solid fa-check"></i> Apply Filters
                </button>
                <button class="btn btn-secondary flex-fill" id="resetFilters">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
                <button class="btn btn-info" id="savePresetBtn" title="Save as Preset">
                    <i class="fa-solid fa-save"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Saved Presets -->
    <div id="savedPresets" style="display: none; margin-bottom: 15px;">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="presetsList">
            <small class="text-muted">💾 Saved Filters:</small>
            <!-- Dynamically loaded -->
        </div>
    </div>

    <!-- Active Filters Display -->
    <div id="activeFilters" class="d-flex gap-2 flex-wrap" style="min-height: 30px;">
        <!-- Pills showing active filters -->
    </div>
</div>

{{-- JavaScript for Advanced Search --}}
<script>
    class AdvancedSearch {
        constructor() {
            this.filters = {};
            this.presets = [];
            this.activeDeadline = null;
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            this.init();
        }

        async init() {
            await this.loadFilterOptions();
            await this.loadPresets();
            this.setupEventListeners();
        }

        setupEventListeners() {
            // Toggle filters panel
            document.getElementById('toggleFilters').addEventListener('click', () => {
                const panel = document.getElementById('advancedFiltersPanel');
                panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
            });

            // Search button
            document.getElementById('searchButton').addEventListener('click', () => this.performSearch());

            // Enter key on search input
            document.getElementById('advancedSearchInput').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.performSearch();
            });

            // Apply filters
            document.getElementById('applyFilters').addEventListener('click', () => this.performSearch());

            // Reset filters
            document.getElementById('resetFilters').addEventListener('click', () => this.resetFilters());

            // Deadline filter buttons
            document.querySelectorAll('.deadline-filter').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.deadline-filter').forEach(b => b.classList.remove('active'));
                    const deadline = e.currentTarget.dataset.deadline;
                    if (this.activeDeadline === deadline) {
                        this.activeDeadline = null;
                    } else {
                        this.activeDeadline = deadline;
                        e.currentTarget.classList.add('active');
                    }
                });
            });

            // Save preset
            document.getElementById('savePresetBtn').addEventListener('click', () => this.savePreset());
        }

        async loadFilterOptions() {
            try {
                const response = await fetch('/api/search/filter-options', {
                    headers: { 'X-CSRF-TOKEN': this.csrfToken }
                });
                const data = await response.json();

                if (data.success) {
                    this.populateFilterOptions(data.options);
                }
            } catch (error) {
                console.error('Error loading filter options:', error);
            }
        }

        populateFilterOptions(options) {
            // Populate Tahun
            const tahunSelect = document.getElementById('filterTahun');
            tahunSelect.innerHTML = options.tahun.map(year =>
                `<option value="${year}">${year}</option>`
            ).join('');

            // Populate Status as checkboxes
            const statusDiv = document.getElementById('statusFilters');
            statusDiv.innerHTML = Object.entries(options.status).map(([key, label]) =>
                `<div class="form-check">
                <input class="form-check-input" type="checkbox" value="${key}" id="status_${key}">
                <label class="form-check-label" for="status_${key}">${label}</label>
            </div>`
            ).join('');

            // Populate Bagian
            const bagianSelect = document.getElementById('filterBagian');
            bagianSelect.innerHTML = options.bagian.map(b =>
                `<option value="${b.id}">${b.name}</option>`
            ).join('');
        }

        async loadPresets() {
            try {
                const response = await fetch('/api/search/presets', {
                    headers: { 'X-CSRF-TOKEN': this.csrfToken }
                });
                const data = await response.json();

                if (data.success) {
                    this.presets = data.presets;
                    this.displayPresets();
                }
            } catch (error) {
                console.error('Error loading presets:', error);
            }
        }

        displayPresets() {
            const container = document.getElementById('savedPresets');
            const list = document.getElementById('presetsList');

            if (this.presets.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            list.innerHTML = '<small class="text-muted">💾 Saved:</small>' + this.presets.map(preset =>
                `<button class="btn btn-sm btn-outline-secondary" onclick="advancedSearch.applyPreset(${preset.id})">
                ${preset.name} <span class="badge bg-info">${preset.usage_count}</span>
            </button>`
            ).join('');
        }

        collectFilters() {
            const tahunOptions = document.getElementById('filterTahun').selectedOptions;
            const tahunSelected = Array.from(tahunOptions).map(opt => opt.value);

            const statusChecked = document.querySelectorAll('#statusFilters input[type="checkbox"]:checked');
            const statusSelected = Array.from(statusChecked).map(cb => cb.value);

            const bagianOptions = document.getElementById('filterBagian').selectedOptions;
            const bagianSelected = Array.from(bagianOptions).map(opt => opt.value);

            return {
                search: document.getElementById('advancedSearchInput').value,
                tahun: tahunSelected.length ? tahunSelected : undefined,
                status: statusSelected.length ? statusSelected : undefined,
                bagian: bagianSelected.length ? bagianSelected : undefined,
                nilai_min: document.getElementById('nilaiMin').value || undefined,
                nilai_max: document.getElementById('nilaiMax').value || undefined,
                deadline: this.activeDeadline || undefined
            };
        }

        async performSearch() {
            const filters = this.collectFilters();

            try {
                const response = await fetch('/api/search/documents', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify(filters)
                });

                const data = await response.json();

                if (data.success) {
                    this.displayResults(data.data);
                    this.displayActiveFilt(data.filters_applied);
                }
            } catch (error) {
                console.error('Search error:', error);
                alert('Error performing search');
            }
        }

        displayResults(results) {
            // Update the table with search results
            const tbody = document.querySelector('tbody');

            if (results.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No documents found</p>
            </td></tr>`;
                return;
            }

            // Replace table content with results
            tbody.innerHTML = results.map((doc, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <strong>${doc.nomor_agenda || '-'}</strong><br>
                    <small class="text-muted">${doc.bulan || ''} ${doc.tahun || ''}</small>
                </td>
                <td>${doc.tanggal_masuk || '-'}</td>
                <td>${doc.nomor_spp || '-'}</td>
                <td><strong>${doc.formatted_nilai_rupiah || '-'}</strong></td>
                <td><span class="badge bg-primary">${doc.status || '-'}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary me-2" 
                            onclick="openDocumentPreview(${doc.id})">
                        <i class="fa-solid fa-eye"></i> Preview
                    </button>
                </td>
            </tr>
        `).join('');
        }

        displayActiveFilters(filters) {
            const container = document.getElementById('activeFilters');
            const pills = [];

            if (filters.search) pills.push(`<span class="badge bg-info">🔍 "${filters.search}"</span>`);
            if (filters.tahun) pills.push(`<span class="badge bg-secondary">📅 ${filters.tahun.join(', ')}</span>`);
            if (filters.status) pills.push(`<span class="badge bg-primary">📊 ${filters.status.length} status</span>`);
            if (filters.bagian) pills.push(`<span class="badge bg-success">🏢 ${filters.bagian.length} bagian</span>`);
            if (filters.nilai_min || filters.nilai_max) {
                pills.push(`<span class="badge bg-warning">💰 ${filters.nilai_min || '0'} - ${filters.nilai_max || '∞'}</span>`);
            }
            if (filters.deadline) pills.push(`<span class="badge bg-danger">⏰ ${filters.deadline}</span>`);

            if (pills.length > 0) {
                pills.push(`<button class="btn btn-sm btn-link" onclick="advancedSearch.resetFilters()">Clear All</button>`);
            }

            container.innerHTML = pills.join('');
        }

        resetFilters() {
            document.getElementById('advancedSearchInput').value = '';
            document.getElementById('filterTahun').selectedIndex = -1;
            document.getElementById('filterBagian').selectedIndex = -1;
            document.querySelectorAll('#statusFilters input').forEach(cb => cb.checked = false);
            document.getElementById('nilaiMin').value = '';
            document.getElementById('nilaiMax').value = '';
            document.querySelectorAll('.deadline-filter').forEach(btn => btn.classList.remove('active'));
            this.activeDeadline = null;
            document.getElementById('activeFilters').innerHTML = '';

            // Reload original data
            location.reload();
        }

        async savePreset() {
            const name = prompt('Enter preset name:');
            if (!name) return;

            const filters = this.collectFilters();

            try {
                const response = await fetch('/api/search/presets', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({ name, filters })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Filter preset saved!');
                    await this.loadPresets();
                }
            } catch (error) {
                console.error('Error saving preset:', error);
                alert('Error saving preset');
            }
        }

        async applyPreset(presetId) {
            try {
                const response = await fetch(`/api/search/presets/${presetId}/use`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken }
                });

                const data = await response.json();
                if (data.success) {
                    // Apply the filters from preset
                    this.populateFiltersFromPreset(data.filters);
                    this.performSearch();
                }
            } catch (error) {
                console.error('Error applying preset:', error);
            }
        }

        populateFiltersFromPreset(filters) {
            if (filters.search) document.getElementById('advancedSearchInput').value = filters.search;
            // Add more filter population logic as needed
        }
    }

    // Initialize on page load
    let advancedSearch;
    document.addEventListener('DOMContentLoaded', function () {
        advancedSearch = new AdvancedSearch();
    });
</script>

<style>
    .advanced-search-container {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .deadline-filter.active {
        background-color: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }

    #activeFilters .badge {
        font-size: 0.9em;
        padding: 8px 12px;
    }
</style>
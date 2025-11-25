@extends('layouts/app')

@section('content')
<style>
/* Owner Dashboard - Senior Friendly Design */
:root {
  --primary-color: #0056b3;
  --success-color: #1e7e34;
  --warning-color: #e0a800;
  --danger-color: #bd2130;
  --info-color: #117a8b;
  --secondary-color: #6c757d;
  --light-bg: #f8f9fa;
  --border-color: #ced4da;
  --text-primary: #212529;
  --text-secondary: #495057;
  --text-muted: #6c757d;
  --senior-primary: #004085;
  --senior-bg-light: #e7f3ff;
  --senior-font-large: 18px;
  --senior-font-medium: 16px;
  --senior-font-small: 14px;
}

body {
  background: #ffffff !important;
  color: var(--text-primary);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: var(--senior-font-medium);
  line-height: 1.6;
}

/* Header Styles - Senior Friendly */
.dashboard-header {
  background: linear-gradient(135deg, var(--senior-primary) 0%, #003366 100%);
  color: white;
  padding: 2rem 0;
  margin-bottom: 2rem;
  border-radius: 0;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.header-title {
  font-size: 28px;
  font-weight: 600;
  margin: 0;
  letter-spacing: 0.5px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Main Layout */
.dashboard-container {
  display: flex;
  gap: 1.5rem;
  min-height: calc(100vh - 120px);
}

.document-list {
  flex: 1;
  min-width: 0;
}

/* Search Section Styles - Senior Friendly */
.search-section {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  border: 2px solid var(--border-color);
}

.search-form .form-control {
  font-size: var(--senior-font-medium);
  padding: 12px 16px;
  border: 2px solid var(--border-color);
  border-radius: 8px;
  height: auto;
}

.search-form .form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.25);
  outline: none;
}

.search-form .form-label {
  font-size: var(--senior-font-medium);
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.search-form .btn {
  font-size: var(--senior-font-medium);
  padding: 12px 24px;
  font-weight: 600;
  min-height: 48px;
  border-radius: 8px;
}

@media (max-width: 1200px) {
  .dashboard-container {
    flex-direction: column;
  }
}

/* Document Cards - Senior Friendly */
.document-card {
  background: white;
  border: 2px solid var(--border-color);
  border-radius: 12px;
  padding: 1.75rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
}

.document-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  border-color: var(--primary-color);
}

.document-card.overdue {
  border-left: 6px solid var(--danger-color);
  background: #fff5f5;
}

.document-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
}

.document-number {
  font-size: var(--senior-font-large);
  font-weight: 600;
  color: var(--primary-color);
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.document-body {
  margin-bottom: 1.25rem;
}

.document-value {
  font-size: 20px;
  font-weight: 600;
  color: var(--success-color);
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.document-info {
  font-size: var(--senior-font-medium);
  color: var(--text-secondary);
  margin-bottom: 0.75rem;
  line-height: 1.5;
}

.document-status {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: var(--senior-font-medium);
  font-weight: 500;
  margin-bottom: 0.75rem;
}

/* Progress Bar - Senior Friendly */
.progress-container {
  margin-bottom: 1.25rem;
}

.progress-bar {
  height: 16px;
  background: #e9ecef;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
  border: 1px solid var(--border-color);
}

.progress-fill {
  height: 100%;
  border-radius: 8px;
  transition: width 0.8s ease;
  position: relative;
}

.progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 100%);
}

.progress-percentage {
  font-size: var(--senior-font-medium);
  font-weight: 600;
  color: var(--text-primary);
  margin-top: 8px;
  text-align: center;
}


/* Info Panel */
.info-panel {
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.info-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.stat-card {
  background: var(--light-bg);
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-color);
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  font-weight: 500;
}

/* Activity Section */
.activity-section {
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.activity-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 1rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 200px;
  overflow-y: auto;
}

.activity-item {
  padding: 0.75rem;
  background: var(--light-bg);
  border-radius: 8px;
  font-size: 0.875rem;
  border-left: 3px solid var(--info-color);
}

.activity-item.success {
  border-left-color: var(--success-color);
}

.activity-item.warning {
  border-left-color: var(--warning-color);
}

.activity-item.danger {
  border-left-color: var(--danger-color);
}

/* Animations */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideUp {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-20px);
  }
}

.timeline-event {
  animation: slideDown 0.3s ease-out;
}

.document-timeline.collapsing .timeline-event {
  animation: slideUp 0.3s ease-out;
}

/* Responsive - Senior Friendly */
@media (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
  }

  .info-panel {
    width: 100%;
    position: static;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .header-title {
    font-size: 24px;
  }

  .document-card {
    padding: 1.25rem;
    margin-bottom: 1.25rem;
  }

  .document-number {
    font-size: var(--senior-font-medium);
  }

  .document-value {
    font-size: 18px;
  }

  .search-section {
    padding: 1.5rem;
  }

  .search-form .form-control {
    font-size: var(--senior-font-medium);
    padding: 12px;
  }

  .search-form .btn {
    padding: 12px 20px;
    min-height: 44px;
  }
}

/* Loading State */
.loading-skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.skeleton-card {
  height: 150px;
  border-radius: 12px;
  margin-bottom: 1rem;
}

/* Scrollbar Styling */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Utility Classes - Senior Friendly */
.text-primary { color: var(--text-primary); }
.text-secondary { color: var(--text-secondary); }
.text-muted { color: var(--text-muted); }
.text-success { color: var(--success-color); }
.text-warning { color: var(--warning-color); }
.text-danger { color: var(--danger-color); }
.text-info { color: var(--info-color); }

.bg-light { background-color: var(--light-bg); }
.bg-success { background-color: var(--success-color); }
.bg-warning { background-color: var(--warning-color); }
.bg-danger { background-color: var(--danger-color); }
.bg-info { background-color: var(--info-color); }

.rounded { border-radius: 8px; }
.rounded-pill { border-radius: 50px; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
.shadow { box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

/* Additional Senior Friendly Improvements */
.senior-hint {
  background: var(--senior-bg-light);
  border-left: 4px solid var(--primary-color);
  padding: 12px 16px;
  margin: 16px 0;
  border-radius: 0 8px 8px 0;
  font-size: var(--senior-font-medium);
}

.senior-stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.senior-stat-card {
  flex: 1;
  min-width: 200px;
  background: white;
  border: 2px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transition: transform 0.2s ease;
}

.senior-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.senior-stat-number {
  font-size: 32px;
  font-weight: 700;
  color: var(--primary-color);
  margin-bottom: 8px;
}

.senior-stat-label {
  font-size: var(--senior-font-medium);
  color: var(--text-secondary);
  font-weight: 500;
}

/* Focus indicators for accessibility */
.document-card:focus,
.btn:focus,
.form-control:focus {
  outline: 3px solid var(--primary-color);
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  :root {
    --text-secondary: #000000;
    --border-color: #000000;
  }

  .document-card {
    border-width: 3px;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .document-card,
  .document-card:hover,
  .senior-stat-card,
  .senior-stat-card:hover {
    transform: none;
    transition: none;
  }

  .progress-fill {
    transition: none;
  }
}
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
  <div class="container">
    <h1 class="header-title">
      <i class="fas fa-chart-line me-3"></i>Dashboard Owner
    </h1>
    <p class="mb-0" style="font-size: var(--senior-font-medium); opacity: 0.9;">Pantau dan kelola semua dokumen perusahaan dengan mudah</p>
  </div>
</div>

<div class="container">

  <!-- Senior Friendly Help Hint -->
  <div class="senior-hint">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Panduan Cepat:</strong> Gunakan kotak pencarian di bawah untuk menemukan dokumen. Klik pada kartu dokumen untuk melihat detail dan status prosesnya.
  </div>

  <!-- Quick Stats -->
  @if(!$documents->isEmpty())
  <div class="senior-stats">
    <div class="senior-stat-card">
      <div class="senior-stat-number">{{ $documents->count() }}</div>
      <div class="senior-stat-label">Total Dokumen</div>
    </div>
    <div class="senior-stat-card">
      <div class="senior-stat-number">{{ $documents->where('is_overdue', true)->count() }}</div>
      <div class="senior-stat-label">Dokumen Terlambat</div>
    </div>
    <div class="senior-stat-card">
      <div class="senior-stat-number">
        {{ number_format($documents->sum('nilai_rupiah'), 0, ',', '.') }}
      </div>
      <div class="senior-stat-label">Total Nilai (Rp)</div>
    </div>
  </div>
  @endif

  <!-- Search Section -->
  <div class="search-section">
    <form method="GET" action="{{ url('/owner/dashboard') }}" class="search-form">
      <div class="d-flex gap-3 align-items-end">
        <div class="flex-grow-1">
          <label class="form-label">
            <i class="fas fa-search me-2"></i>Pencarian Dokumen
          </label>
          <input type="text"
                 name="search"
                 class="form-control"
                 value="{{ $search ?? '' }}"
                 placeholder="Masukkan nomor agenda, SPP, atau kata kunci...">
        </div>
        <div>
          <button type="submit" class="btn btn-primary" style="background: var(--primary-color); border: none;">
            <i class="fas fa-search me-2"></i>Cari Dokumen
          </button>
        </div>
        @if(isset($search) && !empty($search))
        <div>
          <a href="{{ url('/owner/dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Lihat Semua
          </a>
        </div>
        @endif
      </div>
    </form>
  </div>

  <div class="dashboard-container">
    <!-- Document List (100%) -->
    <div class="document-list" style="flex: 1;">
      <div id="documentList">
        @if($documents->isEmpty())
          <div class="text-center py-5">
            <div class="mb-4">
              <i class="fas fa-folder-open" style="font-size: 48px; color: var(--text-muted);"></i>
            </div>
            @if(isset($search) && !empty($search))
              <h4 style="color: var(--text-secondary); font-size: var(--senior-font-large);">Tidak ada dokumen ditemukan</h4>
              <p style="color: var(--text-secondary); font-size: var(--senior-font-medium);">Tidak ada dokumen yang sesuai dengan pencarian "<strong>{{ $search }}</strong>"</p>
              <a href="{{ url('/owner/dashboard') }}" class="btn btn-primary mt-3" style="background: var(--primary-color); border: none; font-size: var(--senior-font-medium);">
                <i class="fas fa-arrow-left me-2"></i>Lihat Semua Dokumen
              </a>
            @else
              <h4 style="color: var(--text-secondary); font-size: var(--senior-font-large);">Belum ada dokumen</h4>
              <p style="color: var(--text-secondary); font-size: var(--senior-font-medium);">Dokumen akan ditampilkan di sini ketika tersedia</p>
            @endif
          </div>
        @else
          @foreach($documents as $index => $dokumen)
            <div class="document-card {{ $dokumen['is_overdue'] ? 'overdue' : '' }}"
                 data-document-id="{{ $dokumen['id'] }}"
                 onclick="window.location.href='{{ url('/owner/workflow/' . $dokumen['id']) }}'">
              <div class="document-header">
                <div>
                  <div class="document-number">
                    <i class="fas fa-file-alt me-2"></i>Dokumen #{{ $index + 1 }} - {{ $dokumen['nomor_agenda'] }}
                  </div>
                  <div class="document-info">
                    <strong>Nomor SPP:</strong> {{ $dokumen['nomor_spp'] }}
                  </div>
                </div>
              </div>

              <div class="document-body">
                <div class="document-value">
                  <i class="fas fa-money-bill-wave me-2"></i>
                  Rp {{ number_format($dokumen['nilai_rupiah'], 0, ',', '.') }}
                </div>
                <div class="document-info">
                  <i class="fas fa-map-marker-alt me-2"></i>
                  <strong>Posisi saat ini:</strong> {{ $dokumen['current_handler_display'] ?? ($dokumen['current_handler'] ?? 'Belum ada penangan') }}
                </div>
                @if($dokumen['deadline_info'])
                <div class="document-info">
                  <i class="fas fa-clock me-2"></i>
                  <strong>Deadline:</strong>
                  <span class="text-{{ $dokumen['deadline_info']['class'] }}" style="font-weight: 600;">
                    {{ $dokumen['deadline_info']['text'] }}
                  </span>
                </div>
                @endif
              </div>

              <div class="progress-container">
                <div class="progress-bar">
                  <div class="progress-fill"
                       style="width: {{ $dokumen['progress_percentage'] }}%; background: {{ $dokumen['progress_color'] }};">
                  </div>
                </div>
                <div class="progress-percentage">
                  <strong>Progres: {{ $dokumen['progress_percentage'] }}%</strong>
                </div>
              </div>

              <div class="text-center mt-3">
                <small style="color: var(--text-muted); font-size: var(--senior-font-small);">
                  <i class="fas fa-hand-pointer me-1"></i>Klik untuk melihat detail
                </small>
              </div>

            </div>
          @endforeach
        @endif
      </div>
    </div>

  </div>
</div>


@endsection
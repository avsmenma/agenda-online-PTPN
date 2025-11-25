@extends('layouts/app')

@section('content')
<style>
/* Owner Dashboard - Modern Clean Style */
:root {
  --primary-color: #007bff;
  --success-color: #28a745;
  --warning-color: #ffc107;
  --danger-color: #dc3545;
  --info-color: #17a2b8;
  --secondary-color: #6f42c1;
  --light-bg: #f8f9fa;
  --border-color: #dee2e6;
  --text-primary: #343a40;
  --text-secondary: #6c757d;
  --text-muted: #adb5bd;
}

body {
  background: #ffffff !important;
  color: var(--text-primary);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Header Styles */
.dashboard-header {
  background: linear-gradient(135deg, #00695c 0%, #004d40 100%);
  color: white;
  padding: 1.5rem 0;
  margin-bottom: 2rem;
  border-radius: 0;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.header-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  letter-spacing: -0.5px;
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

/* Search Section Styles */
.search-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.search-form .form-control:focus {
  border-color: #00695c;
  box-shadow: 0 0 0 0.2rem rgba(0, 105, 92, 0.25);
}

@media (max-width: 1200px) {
  .dashboard-container {
    flex-direction: column;
  }
}

/* Document Cards */
.document-card {
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
}

.document-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  border-color: var(--primary-color);
}

.document-card.overdue {
  border-left: 4px solid var(--danger-color);
}

.document-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.document-number {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--primary-color);
  margin-bottom: 0.5rem;
}


.document-body {
  margin-bottom: 1rem;
}

.document-value {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--success-color);
  margin-bottom: 0.5rem;
}

.document-info {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
}

.document-status {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

/* Progress Bar */
.progress-container {
  margin-bottom: 1rem;
}

.progress-bar {
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  position: relative;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
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
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-secondary);
  margin-top: 0.25rem;
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

/* Responsive */
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
    font-size: 1.5rem;
  }

  .document-card {
    padding: 1rem;
  }

  .document-number {
    font-size: 1rem;
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

/* Utility Classes */
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
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
  <div class="container">
    <h1 class="header-title">
      🎯 PUSAT KOMANDO OWNER
    </h1>
    <p class="text-white-50 mb-0">Pemantauan & Analitik Dokumen Real-Time</p>
  </div>
</div>

<div class="container">
  <!-- Search Section -->
  <div class="search-section" style="background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
    <form method="GET" action="{{ url('/owner/dashboard') }}" class="search-form">
      <div class="d-flex gap-2 align-items-end">
        <div class="flex-grow-1">
          <label class="form-label" style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">
            <i class="fas fa-search me-2"></i>Cari Dokumen
          </label>
          <input type="text" 
                 name="search" 
                 class="form-control" 
                 value="{{ $search ?? '' }}" 
                 placeholder="Cari berdasarkan nomor agenda, SPP, uraian, nilai, bagian, dll..."
                 style="border-radius: 8px; border: 1px solid var(--border-color); padding: 0.75rem;">
        </div>
        <div>
          <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #00695c 0%, #004d40 100%); border: none; border-radius: 8px; padding: 0.75rem 1.5rem; font-weight: 600;">
            <i class="fas fa-search me-2"></i>Cari
          </button>
        </div>
        @if(isset($search) && !empty($search))
        <div>
          <a href="{{ url('/owner/dashboard') }}" class="btn btn-secondary" style="border-radius: 8px; padding: 0.75rem 1.5rem; font-weight: 600;">
            <i class="fas fa-times me-2"></i>Reset
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
              <i class="fas fa-inbox fa-3x text-muted"></i>
            </div>
            @if(isset($search) && !empty($search))
              <h4 class="text-muted">Tidak ada dokumen ditemukan</h4>
              <p class="text-muted">Tidak ada dokumen yang sesuai dengan pencarian "{{ $search }}"</p>
              <a href="{{ url('/owner/dashboard') }}" class="btn btn-primary mt-3" style="background: linear-gradient(135deg, #00695c 0%, #004d40 100%); border: none;">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Semua Dokumen
              </a>
            @else
              <h4 class="text-muted">Belum ada dokumen</h4>
              <p class="text-muted">Dokumen akan muncul di sini ketika dibuat</p>
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
                    📄 #{{ $index + 1 }} - {{ $dokumen['nomor_agenda'] }}
                  </div>
                  <div class="document-info">
                    {{ $dokumen['nomor_spp'] }}
                  </div>
                </div>
              </div>

              <div class="document-body">
                <div class="document-value">
                  Rp. {{ number_format($dokumen['nilai_rupiah'], 0, ',', '.') }}
                </div>
                <div class="document-info">
                  📍 <strong>Posisi:</strong> {{ $dokumen['current_handler_display'] ?? ($dokumen['current_handler'] ?? 'Tidak ada') }}
                </div>
                @if($dokumen['deadline_info'])
                <div class="document-info">
                  ⏰ <strong>Deadline:</strong>
                  <span class="text-{{ $dokumen['deadline_info']['class'] }}">
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
                  {{ $dokumen['progress_percentage'] }}%
                </div>
              </div>

            </div>
          @endforeach
        @endif
      </div>
    </div>

  </div>
</div>


@endsection
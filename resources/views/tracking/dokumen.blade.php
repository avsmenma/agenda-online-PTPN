@extends('layouts.app')

@section('content')
<style>
/* Modern Command Center Dashboard Styles */
:root {
  --primary-color: #083E40;
  --success-color: #889717;
  --warning-color: #ffc107;
  --danger-color: #dc3545;
  --info-color: #0a4f52;
  --text-primary: #1a202c;
  --text-secondary: #4a5568;
  --text-muted: #718096;
  --border-color: #e2e8f0;
  --bg-light: #f8fafc;
}

body {
  background: var(--bg-light) !important;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.dark body {
  background: #0f172a !important;
  color: #f1f5f9;
}

/* Card View Styles */
.card-view-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

@media (max-width: 768px) {
  .card-view-container {
    grid-template-columns: 1fr;
  }
}

.smart-document-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  user-select: text;
}

.smart-document-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.smart-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.smart-card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #083E40;
  margin-bottom: 0.25rem;
}

.smart-card-subtitle {
  font-size: 0.875rem;
  color: #64748b;
}

.smart-card-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #083E40;
  margin-bottom: 1rem;
}

.smart-card-info-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
  color: #475569;
}

.smart-card-info-row i {
  color: #083E40;
  width: 16px;
}

.user-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #083E40;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Workflow Stepper */
.workflow-stepper {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.stepper-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stepper-steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.stepper-steps::before {
  content: '';
  position: absolute;
  top: 12px;
  left: 0;
  right: 0;
  height: 2px;
  background: #e2e8f0;
  z-index: 0;
}

.stepper-step {
  position: relative;
  z-index: 1;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: white;
  border: 2px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  transition: all 0.3s ease;
}

.stepper-step.completed {
  background: #083E40;
  border-color: #083E40;
  color: white;
}

.stepper-step.active {
  background: #083E40;
  border-color: #083E40;
  color: white;
  box-shadow: 0 0 0 4px rgba(8, 62, 64, 0.1);
}

.stepper-step-label {
  position: absolute;
  top: 28px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 0.625rem;
  color: #64748b;
  white-space: nowrap;
  font-weight: 500;
}

.stepper-step.completed .stepper-step-label,
.stepper-step.active .stepper-step-label {
  color: #083E40;
  font-weight: 600;
}

/* Control Bar */
.control-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.control-bar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.search-input-modern {
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  width: 300px;
  transition: all 0.2s ease;
}

.search-input-modern:focus {
  outline: none;
  border-color: #083E40;
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

.action-btn {
  padding: 0.75rem 1.5rem;
  background: #083E40;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.action-btn:hover {
  background: #065a5d;
  transform: translateY(-1px);
}

/* View Switcher */
.view-switcher {
  display: inline-flex;
  background: #f1f5f9;
  border-radius: 8px;
  padding: 4px;
  gap: 4px;
}

.view-switcher-btn {
  padding: 8px 16px;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-switcher-btn:hover {
  background: rgba(8, 62, 64, 0.05);
  color: #083E40;
}

.view-switcher-btn.active {
  background: white;
  color: #083E40;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #64748b;
}

.empty-state-icon {
  font-size: 4rem;
  color: #cbd5e1;
  margin-bottom: 1rem;
}

.empty-state-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.5rem;
}

.empty-state-text {
  font-size: 1rem;
  color: #64748b;
}

/* Pagination */
.pagination-wrapper {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
}

.view-container {
  display: none;
}

.view-container.active {
  display: block;
}
</style>

<div class="container" style="margin-top: 2rem;">
  <!-- Control Bar -->
  <div class="control-bar">
    <div class="control-bar-left">
      <form method="GET" action="{{ url('/tracking-dokumen') }}" class="d-flex align-items-center gap-2">
        <input type="text" name="search" class="search-input-modern" value="{{ $search ?? '' }}" placeholder="Cari dokumen...">
        <button type="submit" class="action-btn">
          <i class="fas fa-search me-1"></i> Cari
        </button>
        @if(isset($search) && !empty($search))
          <a href="{{ url('/tracking-dokumen') }}" class="action-btn" style="background: #64748b;">
            <i class="fas fa-times me-1"></i> Reset
          </a>
        @endif
      </form>
    </div>
    <div class="view-switcher">
      <button class="view-switcher-btn active" onclick="switchView('card')">
        <i class="fas fa-th-large me-1"></i> Kartu
      </button>
      <button class="view-switcher-btn" onclick="switchView('table')">
        <i class="fas fa-table me-1"></i> Tabel
      </button>
    </div>
  </div>

  <!-- Card View -->
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
      <div class="card-view-container">
        @foreach($documents as $dokumen)
          <div class="smart-document-card {{ $dokumen['is_overdue'] ?? false ? 'overdue' : '' }}"
               data-document-url="{{ url('/owner/workflow/' . $dokumen['id']) }}"
               onclick="handleCardClick(event, '{{ url('/owner/workflow/' . $dokumen['id']) }}')">
            
            <div class="smart-card-header">
              <div>
                <div class="smart-card-title">
                  {{ $dokumen['nomor_agenda'] ?? 'N/A' }}
                </div>
                <div class="smart-card-subtitle">
                  SPP: {{ $dokumen['nomor_spp'] ?? 'N/A' }}
                </div>
              </div>
            </div>

            <div class="smart-card-value">
              Rp {{ number_format($dokumen['nilai_rupiah'] ?? 0, 0, ',', '.') }}
            </div>

            <div class="smart-card-info-row">
              <i class="fas fa-user"></i>
              <span>Posisi:</span>
              <span class="user-avatar">
                {{ substr($dokumen['current_handler_display'] ?? 'N/A', 0, 1) }}
              </span>
              <span>{{ $dokumen['current_handler_display'] ?? 'Belum ada penangan' }}</span>
            </div>

            @if(isset($dokumen['deadline_info']) && $dokumen['deadline_info'])
            <div class="smart-card-info-row">
              <i class="fas fa-clock"></i>
              <span>Batas Waktu:</span>
              <span class="text-{{ $dokumen['deadline_info']['class'] ?? 'secondary' }}" style="font-weight: 600;">
                {{ $dokumen['deadline_info']['text'] ?? 'N/A' }}
              </span>
            </div>
            @endif

            <!-- Workflow Stepper -->
            <div class="workflow-stepper">
              <div class="stepper-label">Progres Alur Kerja</div>
              <div class="stepper-steps">
                @php
                  $progress = $dokumen['progress_percentage'] ?? 0;
                  $currentStep = min(5, max(1, ceil($progress / 20)));
                @endphp
                @for($i = 1; $i <= 5; $i++)
                  <div class="stepper-step {{ $i <= $currentStep ? ($i == $currentStep ? 'active' : 'completed') : '' }}">
                    {{ $i }}
                    <div class="stepper-step-label">
                      @if($i == 1) ibutara
                      @elseif($i == 2) teamverifikasi
                      @elseif($i == 3) team perpajakan
                      @elseif($i == 4) team akutansi
                      @else pembayaran
                      @endif
                    </div>
                  </div>
                @endfor
              </div>
            </div>

          </div>
        @endforeach
      </div>
    @endif

    <!-- Pagination Footer for Card View -->
    @if($documents->count() > 0)
      @include('owner.partials.pagination-footer', ['paginator' => $documents])
    @endif
  </div>

  <!-- Table View -->
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
      <div class="table-responsive" style="margin-top: 1rem;">
        <table class="table table-striped">
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
              <tr>
                <td>{{ $dokumen['nomor_agenda'] ?? 'N/A' }}</td>
                <td>{{ $dokumen['tanggal_masuk'] ?? '-' }}</td>
                <td>Rp {{ number_format($dokumen['nilai_rupiah'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ $dokumen['current_handler_display'] ?? 'Belum ada penangan' }}</td>
                <td>
                  <span class="badge {{ $dokumen['progress_percentage'] >= 100 ? 'bg-success' : 'bg-warning' }}">
                    {{ $dokumen['progress_percentage'] >= 100 ? 'Selesai' : 'Proses' }}
                  </span>
                </td>
                <td>{{ $dokumen['progress_percentage'] ?? 0 }}%</td>
                <td>
                  <a href="{{ url('/owner/workflow/' . $dokumen['id']) }}" class="btn btn-sm btn-primary">
                    Lihat
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    <!-- Pagination Footer for Table View -->
    @if($documents->count() > 0)
      @include('owner.partials.pagination-footer', ['paginator' => $documents])
    @endif
  </div>
</div>

<script>
function switchView(view) {
  // Hide all views
  document.querySelectorAll('.view-container').forEach(container => {
    container.classList.remove('active');
  });
  
  // Show selected view
  document.getElementById(view + 'View').classList.add('active');
  
  // Update button states
  document.querySelectorAll('.view-switcher-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  event.target.closest('.view-switcher-btn').classList.add('active');
}

function handleCardClick(event, url) {
  // Check if user is selecting text
  const selection = window.getSelection();
  const selectedText = selection.toString().trim();
  
  if (selectedText.length > 0) {
    return false;
  }
  
  // Navigate to document detail
  window.location.href = url;
  return true;
}
</script>
@endsection


@extends('layouts/app')

@section('content')
<style>
:root {
  --primary-color: #007bff;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --info-color: #3b82f6;
  --secondary-color: #9ca3af;
  --light-bg: #f9fafb;
  --border-color: #e5e7eb;
  --text-primary: #111827;
  --text-secondary: #6b7280;
}

body {
  background: #f3f4f6;
  min-height: 100vh;
  padding: 1.5rem 0;
}

.workflow-container {
  max-width: 100%;
  margin: 0 auto;
  padding: 1rem 2rem;
}

.workflow-header {
  background: white;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.workflow-title-section h1 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.workflow-subtitle {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 0.25rem 0 0 0;
}

.workflow-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow-x: auto;
}

.workflow-stages {
  display: flex;
  align-items: flex-start;
  gap: 0;
  min-width: fit-content;
  padding: 1rem 0;
}

.workflow-stage {
  display: flex;
  align-items: center;
  gap: 0;
  position: relative;
  min-width: 200px;
}

.stage-card {
  background: white;
  border-radius: 10px;
  padding: 1.25rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: all 0.2s ease;
  border: 2px solid var(--border-color);
  position: relative;
  width: 100%;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.stage-card.completed {
  border-color: var(--success-color);
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
}

.stage-card.processing {
  border-color: var(--primary-color);
  background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
  animation: subtle-pulse 2s ease-in-out infinite;
}

.stage-card.pending {
  border-color: var(--border-color);
  background: #fafafa;
  opacity: 0.7;
}

.stage-card.returned {
  border-color: var(--danger-color);
  background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
  position: relative;
}

.stage-card.returned::after {
  content: '↩️';
  position: absolute;
  top: -8px;
  right: -8px;
  background: var(--danger-color);
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  z-index: 10;
}

.stage-card.has-cycle {
  position: relative;
}

.cycle-badge {
  position: absolute;
  top: -8px;
  left: -8px;
  background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 700;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.cycle-info {
  margin-top: 0.75rem;
  padding: 0.5rem;
  background: #fef3c7;
  border-radius: 6px;
  border-left: 3px solid var(--warning-color);
  font-size: 0.7rem;
  line-height: 1.4;
}

.cycle-info-item {
  margin-bottom: 0.25rem;
  color: var(--text-primary);
}

.cycle-info-item strong {
  color: var(--warning-color);
}

@keyframes subtle-pulse {
  0%, 100% {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  50% {
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
  }
}

.stage-icon-wrapper {
  width: 48px;
  height: 48px;
  margin: 0 auto 0.75rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  background: var(--stage-color, #9ca3af);
  transition: all 0.2s ease;
}

.stage-card.completed .stage-icon-wrapper {
  background: var(--success-color);
}

.stage-card.processing .stage-icon-wrapper {
  background: var(--primary-color);
  animation: spin 3s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.stage-card.pending .stage-icon-wrapper {
  background: var(--secondary-color);
}

.stage-card.returned .stage-icon-wrapper {
  background: var(--danger-color);
}

.return-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  background: var(--danger-color);
  color: white;
  border-radius: 4px;
  font-size: 0.65rem;
  font-weight: 600;
  margin-top: 0.5rem;
}

.return-info {
  margin-top: 0.75rem;
  padding: 0.5rem;
  background: #fee2e2;
  border-radius: 6px;
  border-left: 3px solid var(--danger-color);
  font-size: 0.7rem;
  line-height: 1.4;
}

.return-info-item {
  margin-bottom: 0.25rem;
  color: var(--text-primary);
}

.return-info-item strong {
  color: var(--danger-color);
}

.stage-label {
  font-size: 0.625rem;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.stage-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.stage-description {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-bottom: 0.75rem;
  min-height: 32px;
  line-height: 1.4;
}

.stage-timestamp {
  font-size: 0.7rem;
  color: var(--text-secondary);
  padding: 0.375rem 0.5rem;
  background: var(--light-bg);
  border-radius: 6px;
  margin-top: auto;
}

.stage-duration {
  font-size: 0.7rem;
  color: var(--success-color);
  font-weight: 600;
  margin-top: 0.375rem;
}

/* Connection Lines - Horizontal */
.workflow-connector {
  width: 60px;
  height: 3px;
  background: var(--border-color);
  position: relative;
  margin: 0 0.5rem;
  flex-shrink: 0;
  align-self: center;
}

.workflow-connector.completed {
  background: var(--success-color);
}

.workflow-connector.processing {
  background: linear-gradient(90deg, var(--success-color) 0%, var(--primary-color) 100%);
  animation: flow-horizontal 2s ease-in-out infinite;
}

@keyframes flow-horizontal {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
}

.workflow-connector.pending {
  background: var(--border-color);
  opacity: 0.4;
}

.workflow-connector.returned {
  background: var(--danger-color);
  opacity: 0.6;
}

.workflow-connector.has-return {
  position: relative;
}

.workflow-connector.has-return::before {
  content: '↩';
  position: absolute;
  top: -12px;
  right: -8px;
  background: var(--danger-color);
  color: white;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  z-index: 5;
}

.workflow-connector::after {
  content: '';
  position: absolute;
  right: -6px;
  top: 50%;
  transform: translateY(-50%);
  width: 0;
  height: 0;
  border-left: 6px solid;
  border-left-color: inherit;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
}

/* Document Info Panel - Compact with Accordion */
.document-info-panel {
  background: var(--light-bg);
  border-radius: 10px;
  padding: 1.25rem;
  margin-top: 1.5rem;
}

.document-info-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Accordion Styles */
.accordion-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.accordion-item {
  background: white;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  overflow: hidden;
  transition: all 0.3s ease;
}

.accordion-item:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.accordion-header {
  padding: 0.875rem 1rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  transition: background 0.2s ease;
  user-select: none;
}

.accordion-header:hover {
  background: var(--light-bg);
}

.accordion-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-primary);
}

.accordion-title i {
  color: var(--primary-color);
  font-size: 0.875rem;
}

.accordion-icon {
  transition: transform 0.3s ease;
  color: var(--text-secondary);
  font-size: 0.75rem;
}

.accordion-item.active .accordion-icon {
  transform: rotate(180deg);
}

.accordion-content {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  background: var(--light-bg);
}

.accordion-item.active .accordion-content {
  max-height: 2000px;
  padding: 1rem;
}

.document-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.75rem;
}

.document-info-item {
  background: white;
  padding: 0.75rem;
  border-radius: 8px;
  border-left: 3px solid var(--primary-color);
  transition: all 0.2s ease;
}

.document-info-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.document-info-item.tax-field {
  border-left-color: var(--success-color);
}

.document-info-label {
  font-size: 0.7rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-bottom: 0.25rem;
  font-weight: 600;
}

.document-info-value {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
  word-break: break-word;
}

.empty-field {
  color: var(--text-secondary);
  font-style: italic;
  font-size: 0.8rem;
}

.tax-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.tax-link:hover {
  text-decoration: underline;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-selesai {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
}

.badge-proses {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: white;
}

.badge-success {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 500;
}

.badge-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 500;
}

/* Back Button - Compact */
.back-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: white;
  color: var(--text-primary);
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.875rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  transition: all 0.2s ease;
  border: 1px solid var(--border-color);
}

.back-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.15);
  color: var(--primary-color);
  border-color: var(--primary-color);
}

/* Scrollbar Styling */
.workflow-content::-webkit-scrollbar {
  height: 8px;
}

.workflow-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.workflow-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.workflow-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive */
@media (max-width: 768px) {
  .workflow-container {
    padding: 1rem;
  }

  .workflow-content {
    padding: 1rem;
  }

  .workflow-stage {
    min-width: 160px;
  }

  .stage-card {
    min-height: 160px;
    padding: 1rem;
  }

  .workflow-connector {
    width: 40px;
  }
}
</style>

<div class="workflow-container">
  <div class="workflow-header">
    <div class="workflow-title-section">
      <h1>
        <i class="fas fa-project-diagram"></i>
        Workflow Tracking
      </h1>
      <p class="workflow-subtitle">{{ $dokumen->nomor_agenda }}</p>
    </div>
    <a href="{{ url('/owner/dashboard') }}" class="back-button">
      <i class="fas fa-arrow-left"></i>
      Kembali
    </a>
  </div>

  <div class="workflow-content">
    <div class="workflow-stages">
      @foreach($workflowStages as $index => $stage)
        <div class="workflow-stage">
          <div class="stage-card {{ $stage['status'] }} {{ isset($stage['hasCycle']) && $stage['hasCycle'] ? 'has-cycle' : '' }}" style="--stage-color: {{ $stage['color'] }}" data-stage-id="{{ $stage['id'] }}">
            @if(isset($stage['hasCycle']) && $stage['hasCycle'] && isset($stage['cycleInfo']['attemptCount']) && $stage['cycleInfo']['attemptCount'] > 1)
              <div class="cycle-badge">
                <i class="fas fa-redo"></i> Attempt {{ $stage['cycleInfo']['attemptCount'] }}
              </div>
            @endif
            <div>
              <div class="stage-icon-wrapper" style="background: {{ $stage['color'] }}">
                <i class="fas {{ $stage['icon'] }}"></i>
              </div>
              <div class="stage-label">{{ $stage['label'] }}</div>
              <div class="stage-name">{{ $stage['name'] }}</div>
              <div class="stage-description">{{ $stage['description'] }}</div>
            </div>
            <div>
              @if($stage['timestamp'])
                <div class="stage-timestamp">
                  <i class="far fa-clock"></i> {{ $stage['timestamp']->format('d M Y, H:i') }}
                </div>
              @endif
              @if(isset($stage['duration']))
                <div class="stage-duration">
                  <i class="fas fa-hourglass-half"></i> {{ $stage['duration'] }}
                </div>
              @endif
              
              @php
                $stageLogs = isset($activityLogsByStage) && $activityLogsByStage->has($stage['id']) 
                    ? $activityLogsByStage[$stage['id']] 
                    : collect();
              @endphp
              <div class="activity-logs" style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e5e7eb;">
                <div style="font-size: 0.65rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="fas fa-list"></i> Logs Aktivitas:
                </div>
                @if($stageLogs->count() > 0)
                  <a href="javascript:void(0)" 
                     onclick="openActivityLogsModal('{{ $stage['id'] }}', '{{ $stage['name'] }}')" 
                     class="view-logs-link"
                     style="display: inline-block; font-size: 0.75rem; color: {{ $stage['color'] }}; text-decoration: none; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 4px; transition: all 0.2s;"
                     onmouseover="this.style.backgroundColor='{{ $stage['color'] }}20'; this.style.textDecoration='underline';"
                     onmouseout="this.style.backgroundColor='transparent'; this.style.textDecoration='none';">
                    <i class="fas fa-eye"></i> Klik untuk melihat aktivitas ({{ $stageLogs->count() }})
                  </a>
                  
                  <!-- Hidden data for modal -->
                  <div id="logs-data-{{ $stage['id'] }}" style="display: none;" data-stage-color="{{ $stage['color'] }}">
                    @foreach($stageLogs as $log)
                      <div class="log-item" 
                           data-action="{{ $log->action }}" 
                           data-description="{{ htmlspecialchars($log->action_description, ENT_QUOTES, 'UTF-8') }}" 
                           data-timestamp="{{ $log->action_at->format('d-m-Y H:i:s') }}" 
                           data-timestamp-date="{{ $log->action_at->format('d-m-Y') }}" 
                           data-timestamp-time="{{ $log->action_at->format('H:i:s') }}"></div>
                    @endforeach
                  </div>
                @else
                  <div style="font-size: 0.7rem; color: #9ca3af; font-style: italic;">
                    Belum ada aktivitas yang tercatat
                  </div>
                @endif
              </div>
              @if($stage['hasReturn'] && $stage['returnInfo'])
                <div class="return-badge">
                  <i class="fas fa-undo"></i> Dikembalikan
                </div>
                <div class="return-info">
                  <div class="return-info-item">
                    <strong>Dikembalikan ke:</strong> {{ $stage['returnInfo']['returned_to'] }}
                  </div>
                  <div class="return-info-item">
                    <strong>Pada:</strong> {{ $stage['returnInfo']['timestamp']->format('d M Y, H:i') }}
                  </div>
                  <div class="return-info-item">
                    <strong>Alasan:</strong> {{ Str::limit($stage['returnInfo']['reason'], 50) }}
                  </div>
                </div>
              @endif
              @if(isset($stage['hasCycle']) && $stage['hasCycle'] && isset($stage['cycleInfo']['isResend']) && $stage['cycleInfo']['isResend'])
                <div class="cycle-info">
                  <div class="cycle-info-item">
                    <strong>🔄 Dikirim Kembali:</strong> {{ $stage['cycleInfo']['resendTimestamp']->format('d M Y, H:i') }}
                  </div>
                  @if($stage['cycleInfo']['returnTimestamp'])
                    <div class="cycle-info-item">
                      <strong>↩️ Dikembalikan:</strong> {{ $stage['cycleInfo']['returnTimestamp']->format('d M Y, H:i') }}
                    </div>
                  @endif
                  <div class="cycle-info-item">
                    <strong>📊 Total Attempt:</strong> {{ $stage['cycleInfo']['attemptCount'] }}x
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>

        @if($index < count($workflowStages) - 1)
          @php
            $nextStage = $workflowStages[$index + 1];
            $connectorStatus = $stage['status'];
            // If current stage is returned, show returned connector
            if ($stage['status'] === 'returned') {
              $connectorStatus = 'returned';
            }
          @endphp
          <div class="workflow-connector {{ $connectorStatus }} {{ $stage['hasReturn'] ? 'has-return' : '' }}">
          </div>
        @endif
      @endforeach
    </div>

    <!-- Document Info Panel with Accordion -->
    <div class="document-info-panel">
      <div class="document-info-title">
        <i class="fas fa-info-circle"></i>
        Informasi Dokumen Lengkap
      </div>
      
      <!-- Accordion Container -->
      <div class="accordion-container">
        <!-- Data Awal Section -->
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion('data-awal')">
            <div class="accordion-title">
              <i class="fas fa-file-alt"></i>
              <span>Data Awal</span>
            </div>
            <i class="fas fa-chevron-down accordion-icon" id="icon-data-awal"></i>
          </div>
          <div class="accordion-content" id="content-data-awal">
            <div class="document-info-grid">
              <div class="document-info-item">
                <div class="document-info-label">Nomor Agenda</div>
                <div class="document-info-value">{{ $dokumen->nomor_agenda }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Bulan</div>
                <div class="document-info-value">{{ $dokumen->bulan ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Tahun</div>
                <div class="document-info-value">{{ $dokumen->tahun ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Tanggal Masuk</div>
                <div class="document-info-value">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d M Y, H:i') : '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Nomor SPP</div>
                <div class="document-info-value">{{ $dokumen->nomor_spp ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Tanggal SPP</div>
                <div class="document-info-value">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d M Y') : '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Uraian SPP</div>
                <div class="document-info-value">{{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Nilai Rupiah</div>
                <div class="document-info-value">Rp. {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Kategori</div>
                <div class="document-info-value">{{ $dokumen->kategori ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Jenis Dokumen</div>
                <div class="document-info-value">{{ $dokumen->jenis_dokumen ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Sub Bagian Pekerjaan</div>
                <div class="document-info-value">{{ $dokumen->jenis_sub_pekerjaan ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Jenis Pembayaran</div>
                <div class="document-info-value">{{ $dokumen->jenis_pembayaran ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Kebun</div>
                <div class="document-info-value">{{ $dokumen->kebun ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Bagian</div>
                <div class="document-info-value">{{ $dokumen->bagian ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Nama Pengirim</div>
                <div class="document-info-value">{{ $dokumen->nama_pengirim ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Dibayar Kepada</div>
                <div class="document-info-value">
                  @if($dokumen->dibayarKepadas->count() > 0)
                    {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
                  @else
                    {{ $dokumen->dibayar_kepada ?? '-' }}
                  @endif
                </div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">No Berita Acara</div>
                <div class="document-info-value">{{ $dokumen->no_berita_acara ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Tanggal Berita Acara</div>
                <div class="document-info-value">{{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d M Y') : '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">No SPK</div>
                <div class="document-info-value">{{ $dokumen->no_spk ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Tanggal SPK</div>
                <div class="document-info-value">{{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d M Y') : '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Tanggal Berakhir SPK</div>
                <div class="document-info-value">{{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d M Y') : '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">No PO</div>
                <div class="document-info-value">
                  @if($dokumen->dokumenPos->count() > 0)
                    {{ $dokumen->dokumenPos->pluck('nomor_po')->join(', ') }}
                  @else
                    -
                  @endif
                </div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">No PR</div>
                <div class="document-info-value">
                  @if($dokumen->dokumenPrs->count() > 0)
                    {{ $dokumen->dokumenPrs->pluck('nomor_pr')->join(', ') }}
                  @else
                    -
                  @endif
                </div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">No Mirror</div>
                <div class="document-info-value">{{ $dokumen->nomor_mirror ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Status</div>
                <div class="document-info-value">{{ $dokumen->status }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Handler</div>
                <div class="document-info-value">{{ $dokumen->current_handler ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Dibuat</div>
                <div class="document-info-value">{{ $dokumen->created_at->format('d M Y, H:i') }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Data Perpajakan Section -->
        @php
          $hasPerpajakanData = !empty($dokumen->npwp) || !empty($dokumen->no_faktur) || 
                               !empty($dokumen->tanggal_faktur) || !empty($dokumen->jenis_pph) ||
                               !empty($dokumen->dpp_pph) || !empty($dokumen->ppn_terhutang) ||
                               !empty($dokumen->link_dokumen_pajak) || !empty($dokumen->status_perpajakan);
        @endphp
        @if($hasPerpajakanData || $dokumen->status == 'sent_to_akutansi' || $dokumen->status == 'sent_to_pembayaran' || $dokumen->current_handler == 'akutansi' || $dokumen->current_handler == 'pembayaran')
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion('data-perpajakan')">
            <div class="accordion-title">
              <i class="fas fa-file-invoice-dollar"></i>
              <span>Data Perpajakan</span>
            </div>
            <i class="fas fa-chevron-down accordion-icon" id="icon-data-perpajakan"></i>
          </div>
          <div class="accordion-content" id="content-data-perpajakan">
            <div class="document-info-grid">
              <div class="document-info-item tax-field">
                <div class="document-info-label">NPWP</div>
                <div class="document-info-value">{{ $dokumen->npwp ?? '-' }}</div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">Status Perpajakan</div>
                <div class="document-info-value">
                  @if($dokumen->status_perpajakan == 'selesai')
                    <span class="badge badge-selesai">Selesai</span>
                  @elseif($dokumen->status_perpajakan == 'sedang_diproses')
                    <span class="badge badge-proses">Sedang Diproses</span>
                  @else
                    <span class="empty-field">-</span>
                  @endif
                </div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">No Faktur</div>
                <div class="document-info-value">{{ $dokumen->no_faktur ?? '-' }}</div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">Tanggal Faktur</div>
                <div class="document-info-value">{{ $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('d M Y') : '-' }}</div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">Tanggal Selesai Verifikasi Pajak</div>
                <div class="document-info-value">{{ $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('d M Y') : '-' }}</div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">Jenis PPh</div>
                <div class="document-info-value">{{ $dokumen->jenis_pph ?? '-' }}</div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">DPP PPh</div>
                <div class="document-info-value">{{ $dokumen->dpp_pph ? 'Rp. ' . number_format($dokumen->dpp_pph, 0, ',', '.') : '-' }}</div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">PPN Terhutang</div>
                <div class="document-info-value">{{ $dokumen->ppn_terhutang ? 'Rp. ' . number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-' }}</div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">Link Dokumen Pajak</div>
                <div class="document-info-value">
                  @if($dokumen->link_dokumen_pajak)
                    @if(filter_var($dokumen->link_dokumen_pajak, FILTER_VALIDATE_URL))
                      <a href="{{ $dokumen->link_dokumen_pajak }}" target="_blank" class="tax-link">
                        {{ Str::limit($dokumen->link_dokumen_pajak, 40) }} <i class="fas fa-external-link-alt"></i>
                      </a>
                    @else
                      {{ $dokumen->link_dokumen_pajak }}
                    @endif
                  @else
                    <span class="empty-field">-</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif

        <!-- Data Akutansi Section -->
        @php
          $hasAkutansiData = !empty($dokumen->nomor_miro);
        @endphp
        @if($hasAkutansiData || $dokumen->status == 'sent_to_pembayaran' || $dokumen->current_handler == 'pembayaran')
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion('data-akutansi')">
            <div class="accordion-title">
              <i class="fas fa-calculator"></i>
              <span>Data Akutansi</span>
            </div>
            <i class="fas fa-chevron-down accordion-icon" id="icon-data-akutansi"></i>
          </div>
          <div class="accordion-content" id="content-data-akutansi">
            <div class="document-info-grid">
              <div class="document-info-item tax-field">
                <div class="document-info-label">Nomor MIRO</div>
                <div class="document-info-value">{{ $dokumen->nomor_miro ?? '-' }}</div>
              </div>
            </div>
          </div>
        </div>
        @endif

        <!-- Data Pembayaran Section -->
        @php
          $statusPembayaran = $dokumen->status_pembayaran ?? null;
          $linkBuktiPembayaran = $dokumen->link_bukti_pembayaran ?? null;
          $hasPembayaranData = !empty($statusPembayaran) || !empty($linkBuktiPembayaran);
          $isCompleted = in_array($dokumen->status, ['selesai', 'approved_data_sudah_terkirim', 'completed']) || $statusPembayaran === 'sudah_dibayar';
        @endphp
        @if($hasPembayaranData || $dokumen->current_handler == 'pembayaran' || $dokumen->status == 'sent_to_pembayaran' || $isCompleted)
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion('data-pembayaran')">
            <div class="accordion-title">
              <i class="fas fa-money-bill-wave"></i>
              <span>Data Pembayaran</span>
            </div>
            <i class="fas fa-chevron-down accordion-icon" id="icon-data-pembayaran"></i>
          </div>
          <div class="accordion-content" id="content-data-pembayaran">
            <div class="document-info-grid">
              <div class="document-info-item tax-field">
                <div class="document-info-label">Status Pembayaran</div>
                <div class="document-info-value">
                  @if($statusPembayaran)
                    @php
                      $statusLabel = match($statusPembayaran) {
                        'siap_dibayar' => 'Siap Dibayar',
                        'sudah_dibayar' => 'Sudah Dibayar',
                        default => ucfirst(str_replace('_', ' ', $statusPembayaran))
                      };
                    @endphp
                    <span class="badge badge-{{ $statusPembayaran == 'sudah_dibayar' ? 'success' : 'info' }}">{{ $statusLabel }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </div>
              </div>
              <div class="document-info-item tax-field">
                <div class="document-info-label">Link Bukti Pembayaran</div>
                <div class="document-info-value">
                  @if($linkBuktiPembayaran)
                    <a href="{{ $linkBuktiPembayaran }}" target="_blank" class="text-primary" style="text-decoration: underline;">
                      {{ Str::limit($linkBuktiPembayaran, 50) }}
                      <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
// Smooth hover effects
document.querySelectorAll('.stage-card').forEach(card => {
  card.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-2px)';
    this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
  });
  
  card.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
    this.style.boxShadow = '';
  });
});

// Accordion Toggle Function
function toggleAccordion(id) {
  const item = document.querySelector(`#content-${id}`).closest('.accordion-item');
  const isActive = item.classList.contains('active');
  
  // Close all accordions
  document.querySelectorAll('.accordion-item').forEach(acc => {
    acc.classList.remove('active');
  });
  
  // Open clicked accordion if it wasn't active
  if (!isActive) {
    item.classList.add('active');
  }
}

// Auto-expand first accordion on load
document.addEventListener('DOMContentLoaded', function() {
  const firstAccordion = document.querySelector('.accordion-item');
  if (firstAccordion) {
    firstAccordion.classList.add('active');
  }
});

// Activity Logs Modal Functions
function openActivityLogsModal(stageId, stageName) {
  const modal = document.getElementById('activityLogsModal');
  const modalTitle = document.getElementById('activityLogsModalTitle');
  const modalContent = document.getElementById('activityLogsModalContent');
  const logsData = document.getElementById('logs-data-' + stageId);
  
  if (!logsData) return;
  
  // Set modal title
  modalTitle.textContent = 'Logs Aktivitas - ' + stageName;
  
  // Clear previous content
  modalContent.innerHTML = '';
  
  // Get stage color from logs data element
  let stageColor = logsData.getAttribute('data-stage-color') || '#3b82f6';
  
  // Build logs HTML
  const logItems = logsData.querySelectorAll('.log-item');
  if (logItems.length === 0) {
    modalContent.innerHTML = '<div style="text-align: center; padding: 2rem; color: #9ca3af; font-style: italic;">Belum ada aktivitas yang tercatat</div>';
  } else {
    logItems.forEach((item, index) => {
      const action = item.getAttribute('data-action');
      const description = item.getAttribute('data-description');
      const timestampDate = item.getAttribute('data-timestamp-date');
      const timestampTime = item.getAttribute('data-timestamp-time');
      const timestamp = item.getAttribute('data-timestamp');
      
      const logDiv = document.createElement('div');
      logDiv.className = 'modal-log-item';
      logDiv.style.cssText = 'margin-bottom: 0.75rem; padding: 0.75rem; background: #f9fafb; border-left: 3px solid ' + stageColor + '; border-radius: 4px;';
      
      let logText = '';
      if (action === 'received' || action === 'deadline_set') {
        logText = '• ' + description + ' ' + timestampDate + ' jam ' + timestampTime;
      } else {
        logText = '• ' + description + ' pada tanggal ' + timestamp;
      }
      
      logDiv.innerHTML = '<div style="font-size: 0.875rem; color: #374151; line-height: 1.6;">' + logText + '</div>';
      modalContent.appendChild(logDiv);
    });
  }
  
  // Show modal
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeActivityLogsModal() {
  const modal = document.getElementById('activityLogsModal');
  modal.style.display = 'none';
  document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('activityLogsModal');
  if (event.target === modal) {
    closeActivityLogsModal();
  }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeActivityLogsModal();
  }
});
</script>

<!-- Activity Logs Modal -->
<div id="activityLogsModal" class="activity-logs-modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; animation: fadeIn 0.2s ease-in-out;">
  <div class="activity-logs-modal-content" style="background-color: white; margin: auto; padding: 0; border-radius: 12px; width: 90%; max-width: 700px; max-height: 80vh; box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column; animation: slideUp 0.3s ease-out;">
    <div class="activity-logs-modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px 12px 0 0;">
      <h3 id="activityLogsModalTitle" style="margin: 0; font-size: 1.125rem; font-weight: 600; color: white; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-list"></i> <span>Logs Aktivitas</span>
      </h3>
      <button onclick="closeActivityLogsModal()" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; font-weight: bold; cursor: pointer; padding: 0.25rem 0.75rem; border-radius: 6px; transition: all 0.2s; line-height: 1;" onmouseover="this.style.background='rgba(255,255,255,0.3)';" onmouseout="this.style.background='rgba(255,255,255,0.2)';">
        <span>&times;</span>
      </button>
    </div>
    <div id="activityLogsModalContent" class="activity-logs-modal-body" style="padding: 1.5rem; overflow-y: auto; flex: 1; max-height: calc(80vh - 80px);">
      <!-- Logs will be inserted here -->
    </div>
  </div>
</div>

<style>
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    transform: translateY(20px);
    opacity: 0;
  }
  to { 
    transform: translateY(0);
    opacity: 1;
  }
}

.activity-logs-modal {
  backdrop-filter: blur(4px);
}

.activity-logs-modal-content {
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.activity-logs-modal-body::-webkit-scrollbar {
  width: 8px;
}

.activity-logs-modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.activity-logs-modal-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.activity-logs-modal-body::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.modal-log-item {
  transition: all 0.2s;
}

.modal-log-item:hover {
  background: #f3f4f6 !important;
  transform: translateX(4px);
}

.view-logs-link {
  font-weight: 500;
}

.view-logs-link i {
  margin-right: 0.25rem;
}
</style>

@endsection


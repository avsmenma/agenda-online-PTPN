@extends('layouts.app')

@section('content')
<style>
  /* Modern Professional Workflow Design */
  * {
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }

  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    border-radius: 10px;
  }

  /* Professional Timeline Container */
  .workflow-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
  }

  /* Header Section */
  .workflow-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 24px;
    padding: 32px;
    margin-bottom: 40px;
    box-shadow: 0 4px 24px rgba(8, 62, 64, 0.08);
    border: 1px solid rgba(8, 62, 64, 0.1);
  }

  .workflow-header-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 20px;
  }

  .workflow-title-section h1 {
    font-size: 32px;
    font-weight: 800;
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
  }

  .workflow-title-section .document-info {
    font-size: 16px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
  }

  .workflow-header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
  }

  .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: white;
    border: 2px solid rgba(8, 62, 64, 0.2);
    border-radius: 12px;
    color: #083E40;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .btn-back:hover {
    background: #083E40;
    color: white;
    border-color: #083E40;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(8, 62, 64, 0.2);
  }

  /* Progress Bar */
  .progress-bar-container {
    background: #f1f5f9;
    height: 8px;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
  }

  .progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    background-size: 200% 100%;
    border-radius: 10px;
    transition: width 1s ease-out;
    position: relative;
    overflow: hidden;
  }

  .progress-bar-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shimmer 2s infinite;
  }

  @keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
  }

  .progress-percentage {
    text-align: right;
    margin-top: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
  }

  /* Professional Vertical Timeline */
  .workflow-timeline {
    position: relative;
    padding: 40px 0;
  }

  .timeline-line {
    position: absolute;
    left: 40px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #e2e8f0 0%, #cbd5e1 100%);
    border-radius: 2px;
  }

  .timeline-line-progress {
    position: absolute;
    left: 40px;
    top: 0;
    width: 4px;
    background: linear-gradient(180deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    border-radius: 2px;
    transition: height 1s ease-out;
    box-shadow: 0 0 10px rgba(8, 62, 64, 0.3);
  }

  /* Timeline Stage Item */
  .timeline-stage {
    position: relative;
    display: flex;
    gap: 32px;
    margin-bottom: 48px;
    padding-left: 0;
    animation: fadeInUp 0.6s ease-out;
    animation-fill-mode: both;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .timeline-stage:nth-child(1) { animation-delay: 0.1s; }
  .timeline-stage:nth-child(2) { animation-delay: 0.2s; }
  .timeline-stage:nth-child(3) { animation-delay: 0.3s; }
  .timeline-stage:nth-child(4) { animation-delay: 0.4s; }
  .timeline-stage:nth-child(5) { animation-delay: 0.5s; }

  /* Stage Icon/Node */
  .timeline-node {
    position: relative;
    z-index: 10;
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    color: white;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .timeline-node.completed {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
  }

  .timeline-node.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    box-shadow: 0 0 0 8px rgba(8, 62, 64, 0.1), 0 12px 32px rgba(8, 62, 64, 0.3);
    animation: pulse-ring 2s ease-out infinite;
  }

  @keyframes pulse-ring {
    0% {
      box-shadow: 0 0 0 0 rgba(8, 62, 64, 0.4), 0 12px 32px rgba(8, 62, 64, 0.3);
    }
    50% {
      box-shadow: 0 0 0 12px rgba(8, 62, 64, 0), 0 12px 32px rgba(8, 62, 64, 0.3);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(8, 62, 64, 0), 0 12px 32px rgba(8, 62, 64, 0.3);
    }
  }

  .timeline-node.pending {
    background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
    color: #64748b;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .timeline-node.returned {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
  }

  /* Stage Content Card */
  .timeline-content {
    flex: 1;
    background: white;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
    border: 2px solid #e2e8f0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .timeline-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #083E40 0%, #889717 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .timeline-stage.active .timeline-content {
    border-color: #083E40;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.15);
    transform: translateX(8px);
  }

  .timeline-stage.active .timeline-content::before {
    opacity: 1;
  }

  .timeline-stage.completed .timeline-content {
    border-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
  }

  .timeline-stage.returned .timeline-content {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
  }

  /* Stage Header */
  .stage-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
  }

  .stage-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #94a3b8;
    margin-bottom: 4px;
  }

  .stage-name {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
  }

  .stage-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .stage-status-badge.active {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .stage-status-badge.completed {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
  }

  .stage-status-badge.returned {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
  }

  .stage-status-badge.pending {
    background: #e2e8f0;
    color: #64748b;
  }

  /* Stage Description */
  .stage-description {
    font-size: 15px;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 16px;
  }

  /* Stage Timestamp */
  .stage-timestamp {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
  }

  /* Return/Cycle Info */
  .stage-return-info {
    margin-top: 16px;
    padding: 16px;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 12px;
    border: 1px solid #fbbf24;
  }

  .stage-return-info p {
    font-size: 13px;
    color: #92400e;
    margin: 4px 0;
  }

  /* Information Grid */
  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-top: 48px;
  }

  .info-card {
    background: white;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
    border: 1px solid rgba(8, 62, 64, 0.1);
  }

  .info-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f5f9;
  }

  .info-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
  }

  .info-card-title {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
  }

  /* Hero Financial Card */
  .hero-financial-card {
    grid-column: 1 / -1;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    border-radius: 24px;
    padding: 40px;
    color: white;
    position: relative;
    overflow: hidden;
  }

  .hero-financial-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
  }

  @keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  .hero-financial-content {
    position: relative;
    z-index: 10;
  }

  .hero-financial-label {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
  }

  .hero-financial-value {
    font-size: 48px;
    font-weight: 800;
    margin-bottom: 32px;
    letter-spacing: -1px;
  }

  .hero-financial-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
  }

  .hero-detail-item {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 16px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .hero-detail-label {
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
  }

  .hero-detail-value {
    font-size: 18px;
    font-weight: 700;
  }

  /* Activity Logs */
  .activity-log-container {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 8px;
  }

  .activity-log-item {
    position: relative;
    padding-left: 32px;
    padding-bottom: 20px;
    border-left: 2px solid #e2e8f0;
  }

  .activity-log-item:last-child {
    border-left: none;
  }

  .activity-log-item::before {
    content: '';
    position: absolute;
    left: -6px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #083E40;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e2e8f0;
  }

  .activity-log-text {
    font-size: 14px;
    color: #0f172a;
    font-weight: 500;
    margin-bottom: 4px;
  }

  .activity-log-time {
    font-size: 12px;
    color: #64748b;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .workflow-container {
      padding: 20px 16px;
    }

    .timeline-stage {
      flex-direction: column;
      gap: 20px;
    }

    .timeline-line,
    .timeline-line-progress {
      left: 20px;
    }

    .timeline-node {
      width: 60px;
      height: 60px;
      font-size: 24px;
    }

    .timeline-content {
      padding: 20px;
    }

    .stage-name {
      font-size: 20px;
    }

    .hero-financial-value {
      font-size: 36px;
    }
  }
</style>

<div class="workflow-container">
  {{-- Header --}}
  <div class="workflow-header">
    <div class="workflow-header-top">
      <div class="workflow-title-section">
        <h1>Workflow Tracking</h1>
        <div class="document-info">
          <i class="fas fa-file-invoice text-emerald-500"></i>
          <span>Memantau perjalanan dokumen <strong>{{ $dokumen->nomor_spp ?? $dokumen->nomor_agenda }}</strong></span>
        </div>
      </div>
      <div class="workflow-header-actions">
        <a href="{{ $dashboardUrl ?? '/owner/dashboard' }}" class="btn-back">
          <i class="fas fa-arrow-left"></i>
          Kembali
        </a>
      </div>
    </div>

    {{-- Progress Bar --}}
    @php
      $currentProgress = $dokumen->progress_percentage ?? 0;
      $progress = is_numeric($currentProgress) ? $currentProgress : 0;
    @endphp
    <div>
      <div class="progress-bar-container">
        <div class="progress-bar-fill" style="width: {{ $progress }}%"></div>
      </div>
      <div class="progress-percentage">{{ number_format($progress, 1) }}% Selesai</div>
    </div>
  </div>

  {{-- Professional Vertical Timeline --}}
  <div class="workflow-timeline">
    {{-- Background Timeline Line --}}
    <div class="timeline-line"></div>
    
    {{-- Progress Timeline Line --}}
    @php
      $completedStages = 0;
      $totalStages = count($workflowStages);
      foreach ($workflowStages as $stage) {
        $status = $stage['status'] ?? 'pending';
        if ($status === 'completed' || $status === 'selesai') {
          $completedStages++;
        }
      }
      $timelineProgress = $totalStages > 0 ? ($completedStages / $totalStages) * 100 : 0;
      $timelineHeight = $totalStages > 0 ? ($completedStages * (100 / $totalStages)) : 0;
    @endphp
    <div class="timeline-line-progress" style="height: {{ $timelineHeight }}%"></div>

    {{-- Timeline Stages --}}
    @foreach($workflowStages as $index => $stage)
      @php
        $status = $stage['status'] ?? 'pending';
        $isActive = $status === 'processing' || $status === 'active';
        $isCompleted = $status === 'completed' || $status === 'selesai';
        $isReturned = $status === 'returned';
        $isPending = !$isActive && !$isCompleted && !$isReturned;

        $stageClass = 'timeline-stage';
        if ($isActive) {
          $stageClass .= ' active';
        } elseif ($isCompleted) {
          $stageClass .= ' completed';
        } elseif ($isReturned) {
          $stageClass .= ' returned';
        } else {
          $stageClass .= ' pending';
        }

        $nodeClass = 'timeline-node';
        if ($isActive) {
          $nodeClass .= ' active';
        } elseif ($isCompleted) {
          $nodeClass .= ' completed';
        } elseif ($isReturned) {
          $nodeClass .= ' returned';
        } else {
          $nodeClass .= ' pending';
        }

        $badgeClass = 'stage-status-badge';
        if ($isActive) {
          $badgeClass .= ' active';
        } elseif ($isCompleted) {
          $badgeClass .= ' completed';
        } elseif ($isReturned) {
          $badgeClass .= ' returned';
        } else {
          $badgeClass .= ' pending';
        }

        $badgeText = 'Menunggu';
        if ($isActive) {
          $badgeText = 'Sedang Diproses';
        } elseif ($isCompleted) {
          $badgeText = 'Selesai';
        } elseif ($isReturned) {
          $badgeText = 'Dikembalikan';
        }

        $iconClass = 'fas fa-circle';
        if ($isCompleted) {
          $iconClass = 'fas fa-check';
        } elseif ($isReturned) {
          $iconClass = 'fas fa-undo-alt';
        } else {
          $rawIcon = $stage['icon'] ?? 'fa-circle';
          $iconClass = strpos($rawIcon, 'fa-') === 0 ? 'fas ' . $rawIcon : 'fas fa-' . $rawIcon;
        }
      @endphp

      <div class="{{ $stageClass }}">
        {{-- Timeline Node --}}
        <div class="{{ $nodeClass }}">
          <i class="{{ $iconClass }}"></i>
        </div>

        {{-- Stage Content --}}
        <div class="timeline-content">
          <div class="stage-header">
            <div>
              <div class="stage-label">{{ $stage['label'] ?? 'STAGE' }}</div>
              <div class="stage-name">{{ $stage['name'] ?? 'Unknown' }}</div>
            </div>
            <span class="{{ $badgeClass }}">
              @if($isActive)
                <span class="w-2 h-2 rounded-full bg-white mr-1.5 inline-block animate-pulse"></span>
              @endif
              {{ $badgeText }}
            </span>
          </div>

          <div class="stage-description">
            {{ $stage['description'] ?? 'Menunggu proses' }}
          </div>

          @if(!empty($stage['timestamp']))
            <div class="stage-timestamp">
              <i class="far fa-clock"></i>
              <span>{{ \Carbon\Carbon::parse($stage['timestamp'])->format('d M Y, H:i') }}</span>
            </div>
          @endif

          @if(($stage['hasCycle'] ?? false) || ($stage['hasReturn'] ?? false))
            <div class="stage-return-info">
              @if($stage['hasCycle'] ?? false)
                <p>
                  <i class="fas fa-history mr-2"></i>
                  <strong>Resubmission #{{ $stage['cycleInfo']['cycleCount'] ?? 1 }}</strong>
                </p>
              @endif
              @if($stage['hasReturn'] ?? false)
                <p class="mt-2">
                  <i class="fas fa-exclamation-triangle mr-2"></i>
                  {{ $stage['returnInfo']['alasan'] ?? 'Tidak ada alasan' }}
                </p>
              @endif
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  {{-- Information Grid --}}
  <div class="info-grid">
    {{-- Hero Financial Card --}}
    <div class="hero-financial-card">
      <div class="hero-financial-content">
        <div class="hero-financial-label">Nilai Nominal</div>
        <div class="hero-financial-value">
          Rp {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}
        </div>
        <div class="hero-financial-details">
          <div class="hero-detail-item">
            <div class="hero-detail-label">Nomor SPP</div>
            <div class="hero-detail-value">{{ $dokumen->nomor_spp ?? '-' }}</div>
          </div>
          <div class="hero-detail-item">
            <div class="hero-detail-label">Dibayar Kepada</div>
            <div class="hero-detail-value">
              {{ $dokumen->dibayarKepadas->first()?->nama_penerima ?? $dokumen->dibayar_kepada ?? '-' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Document Information Card --}}
    <div class="info-card">
      <div class="info-card-header">
        <div class="info-card-icon" style="background: linear-gradient(135deg, #083E40 0%, #889717 100%);">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="info-card-title">Informasi Dokumen</div>
      </div>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div>
          <div class="stage-label">Uraian SPP</div>
          <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->uraian_spp ?? '-' }}</div>
        </div>
        <div>
          <div class="stage-label">Nomor Agenda</div>
          <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->nomor_agenda ?? '-' }}</div>
        </div>
        <div>
          <div class="stage-label">Jenis Dokumen</div>
          <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->jenis_dokumen ?? '-' }}</div>
        </div>
        <div>
          <div class="stage-label">Kategori</div>
          <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->kategori ?? '-' }}</div>
        </div>
        <div>
          <div class="stage-label">Bagian Pengirim</div>
          <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->bagian ?? '-' }}</div>
        </div>
      </div>
    </div>

    {{-- Tax Data Card --}}
    <div class="info-card">
      <div class="info-card-header">
        <div class="info-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
          <i class="fas fa-calculator"></i>
        </div>
        <div class="info-card-title">Data Perpajakan</div>
      </div>
      <div style="space-y: 16px;">
        @if($dokumen->npwp || $dokumen->no_faktur)
          <div style="padding: 16px; background: #f8fafc; border-radius: 12px; margin-bottom: 12px;">
            <div class="stage-label">NPWP</div>
            <div style="font-family: monospace; font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->npwp ?? '-' }}</div>
          </div>
          <div style="padding: 16px; background: #f8fafc; border-radius: 12px; margin-bottom: 12px;">
            <div class="stage-label">No. Faktur</div>
            <div style="font-family: monospace; font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->no_faktur ?? '-' }}</div>
          </div>
          @if($dokumen->jenis_pph)
            <div style="padding: 16px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 12px; border: 1px solid #86efac;">
              <div class="stage-label" style="color: #059669;">Jenis PPh</div>
              <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->jenis_pph }}</div>
            </div>
          @endif
        @else
          <div style="text-align: center; padding: 40px; color: #94a3b8;">
            <i class="fas fa-search-dollar" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
            <p style="font-size: 14px;">Belum ada data perpajakan</p>
          </div>
        @endif
      </div>
    </div>

    {{-- Activity Logs Card --}}
    <div class="info-card">
      <div class="info-card-header">
        <div class="info-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
          <i class="fas fa-history"></i>
        </div>
        <div class="info-card-title">Activity Logs</div>
      </div>
      <div class="activity-log-container custom-scrollbar">
        @forelse($dokumen->activityLogs->sortByDesc('created_at')->take(15) as $log)
          <div class="activity-log-item">
            <div class="activity-log-text">{{ $log->description ?? 'Activity' }}</div>
            <div class="activity-log-time">
              <i class="far fa-clock mr-1"></i>
              {{ $log->created_at->diffForHumans() }}
            </div>
          </div>
        @empty
          <div style="text-align: center; padding: 40px; color: #94a3b8;">
            <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
            <p style="font-size: 14px;">Tidak ada activity log</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<script>
  // Smooth scroll to active stage on load
  document.addEventListener('DOMContentLoaded', function() {
    const activeStage = document.querySelector('.timeline-stage.active');
    if (activeStage) {
      setTimeout(() => {
        activeStage.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 500);
    }
  });
</script>

@endsection

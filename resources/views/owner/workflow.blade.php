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

  .timeline-stage .timeline-content:has(.stage-overdue-info) {
    border-color: #ef4444;
    border-width: 2px;
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
  .stage-overdue-info {
    margin-top: 16px;
    padding: 16px;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-radius: 12px;
    border: 2px solid #ef4444;
    animation: pulse-overdue 2s ease-in-out infinite;
  }

  @keyframes pulse-overdue {
    0%, 100% {
      box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
      box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
    }
  }

  .stage-overdue-info p {
    font-size: 14px;
    color: #991b1b;
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
  }

  .stage-overdue-info .fas {
    color: #dc2626;
  }

  .overdue-deadline {
    font-size: 12px;
    color: #7f1d1d;
    font-weight: 400;
    margin-left: 8px;
  }

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
    padding: 12px 12px 12px 32px;
    border-left: 2px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 8px;
    margin-bottom: 8px;
  }

  .activity-log-item:hover {
    background: #f8fafc;
    border-left-color: #3b82f6;
    transform: translateX(4px);
  }

  .activity-log-item:last-child {
    border-left: 2px solid #e2e8f0;
  }

  .activity-log-item::before {
    content: '';
    position: absolute;
    left: -6px;
    top: 16px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #083E40;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e2e8f0;
    transition: all 0.2s ease;
  }

  .activity-log-item:hover::before {
    background: #3b82f6;
    transform: scale(1.2);
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

  /* Clickable Card */
  .info-card.clickable {
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    user-select: none;
  }

  .info-card.clickable:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(8, 62, 64, 0.15);
    border-color: rgba(8, 62, 64, 0.3);
  }

  .info-card.clickable:active {
    transform: translateY(-2px);
  }

  .info-card.clickable * {
    pointer-events: none;
  }

  .info-card.clickable {
    pointer-events: auto;
  }

  /* Modern Modal Popup */
  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .modal-overlay.show {
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
  }

  .modal-container {
    background: white;
    border-radius: 24px;
    max-width: 900px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transform: scale(0.9) translateY(20px);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
  }

  .modal-overlay.show .modal-container {
    transform: scale(1) translateY(0);
  }

  .modal-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    padding: 24px 32px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
  }

  .modal-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
  }

  .modal-header-content {
    position: relative;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .modal-header-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
  }

  .modal-header-text h2 {
    font-size: 24px;
    font-weight: 800;
    margin: 0;
    margin-bottom: 4px;
  }

  .modal-header-text p {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
  }

  .modal-close {
    position: relative;
    z-index: 10;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
  }

  .modal-body {
    padding: 32px;
    overflow-y: auto;
    flex: 1;
    background: #f8faf9;
  }

  .modal-section {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.1);
  }

  .modal-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #083E40;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .modal-section-title i {
    font-size: 18px;
  }

  .modal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }

  .modal-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .modal-field-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
  }

  .modal-field-value {
    font-size: 15px;
    font-weight: 600;
    color: #0f172a;
    word-break: break-word;
  }

  .modal-field-value.monospace {
    font-family: 'Courier New', monospace;
    font-size: 14px;
  }

  .modal-field-value.highlight {
    color: #083E40;
    font-weight: 700;
  }

  .modal-field-value.empty {
    color: #cbd5e1;
    font-style: italic;
  }

  /* Tax Specific Styles */
  .tax-summary-card {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 2px solid #86efac;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
  }

  .tax-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(8, 62, 64, 0.1);
  }

  .tax-summary-row:last-child {
    border-bottom: none;
  }

  .tax-summary-label {
    font-size: 14px;
    font-weight: 600;
    color: #059669;
  }

  .tax-summary-value {
    font-size: 16px;
    font-weight: 700;
    color: #083E40;
    font-family: 'Courier New', monospace;
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

    .modal-container {
      width: 95%;
      max-height: 95vh;
    }

    .modal-header {
      padding: 20px 24px;
    }

    .modal-body {
      padding: 24px 20px;
    }

    .modal-grid {
      grid-template-columns: 1fr;
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

          @if(($stage['isOverdue'] ?? false) && ($status === 'processing' || $status === 'active'))
            <div class="stage-overdue-info">
              <p>
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>⚠️ Terlambat: {{ $stage['deadlineInfo']['days_overdue'] ?? 0 }} hari</strong>
                @if($stage['deadlineInfo']['deadline_at'] ?? null)
                  <span class="overdue-deadline">(Deadline: {{ \Carbon\Carbon::parse($stage['deadlineInfo']['deadline_at'])->format('d M Y, H:i') }})</span>
                @endif
              </p>
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
          Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}
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
    <div class="info-card clickable" id="document-info-card" data-modal-type="document">
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
    <div class="info-card clickable" id="tax-data-card" data-modal-type="tax">
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
        @forelse($dokumen->activityLogs->sortByDesc('action_at')->take(15) as $log)
          @php
            // Map performed_by to display name
            $performedByMap = [
              'ibuA' => 'Ibu Tara',
              'IbuA' => 'Ibu Tara',
              'ibuB' => 'Team Verifikasi',
              'IbuB' => 'Team Verifikasi',
              'perpajakan' => 'Team Perpajakan',
              'Perpajakan' => 'Team Perpajakan',
              'akutansi' => 'Team Akutansi',
              'Akutansi' => 'Team Akutansi',
              'pembayaran' => 'Pembayaran',
              'Pembayaran' => 'Pembayaran',
            ];
            $performedByDisplay = $performedByMap[$log->performed_by ?? ''] ?? ($log->performed_by ?? 'System');
            
            // Format action_at
            $actionAt = $log->action_at ?? $log->created_at;
            $actionAtFormatted = $actionAt->format('d M Y, H:i');
            $actionAtRelative = $actionAt->diffForHumans();
          @endphp
          <div class="activity-log-item" 
               onclick="showActivityDetail({{ $log->id }})"
               data-activity-id="{{ $log->id }}"
               data-action-description="{{ htmlspecialchars($log->action_description ?? 'Activity', ENT_QUOTES, 'UTF-8') }}"
               data-performed-by="{{ htmlspecialchars($performedByDisplay, ENT_QUOTES, 'UTF-8') }}"
               data-action-at="{{ htmlspecialchars($actionAtFormatted, ENT_QUOTES, 'UTF-8') }}"
               data-action-at-relative="{{ htmlspecialchars($actionAtRelative, ENT_QUOTES, 'UTF-8') }}"
               data-stage="{{ htmlspecialchars($log->stage ?? '-', ENT_QUOTES, 'UTF-8') }}"
               data-action="{{ htmlspecialchars($log->action ?? '-', ENT_QUOTES, 'UTF-8') }}"
               data-details="{{ htmlspecialchars(json_encode($log->details ?? []), ENT_QUOTES, 'UTF-8') }}">
            <div class="activity-log-text">{{ $log->action_description ?? 'Activity' }}</div>
            <div class="activity-log-time">
              <i class="far fa-clock mr-1"></i>
              {{ $actionAtRelative }}
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

{{-- Modal Popup --}}
<div class="modal-overlay" id="detail-modal">
  <div class="modal-container">
    <div class="modal-header">
      <div class="modal-header-content">
        <div class="modal-header-icon" id="modal-icon">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="modal-header-text">
          <h2 id="modal-title">Detail Dokumen</h2>
          <p id="modal-subtitle">Informasi lengkap dokumen</p>
        </div>
      </div>
      <button class="modal-close" id="modal-close">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body custom-scrollbar" id="modal-body">
      {{-- Content will be populated by JavaScript --}}
    </div>
  </div>
</div>

<script>
  // Document data for modal
  @php
    $documentDataArray = [
      'nomor_agenda' => $dokumen->nomor_agenda ?? null,
      'nomor_spp' => $dokumen->nomor_spp ?? null,
      'tanggal_spp' => $dokumen->tanggal_spp ? \Carbon\Carbon::parse($dokumen->tanggal_spp)->format('d M Y') : null,
      'uraian_spp' => $dokumen->uraian_spp ?? null,
      'nilai_rupiah' => $dokumen->nilai_rupiah ?? null,
      'kategori' => $dokumen->kategori ?? null,
      'jenis_dokumen' => $dokumen->jenis_dokumen ?? null,
      'jenis_sub_pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? null,
      'jenis_pembayaran' => $dokumen->jenis_pembayaran ?? null,
      'kebun' => $dokumen->kebun ?? null,
      'bagian' => $dokumen->bagian ?? null,
      'nama_pengirim' => $dokumen->nama_pengirim ?? null,
      'dibayar_kepada' => $dokumen->dibayarKepadas->first()?->nama_penerima ?? $dokumen->dibayar_kepada ?? null,
      'no_berita_acara' => $dokumen->no_berita_acara ?? null,
      'tanggal_berita_acara' => $dokumen->tanggal_berita_acara ? \Carbon\Carbon::parse($dokumen->tanggal_berita_acara)->format('d M Y') : null,
      'no_spk' => $dokumen->no_spk ?? null,
      'tanggal_spk' => $dokumen->tanggal_spk ? \Carbon\Carbon::parse($dokumen->tanggal_spk)->format('d M Y') : null,
      'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk ? \Carbon\Carbon::parse($dokumen->tanggal_berakhir_spk)->format('d M Y') : null,
      'nomor_miro' => $dokumen->nomor_miro ?? null,
      'status' => $dokumen->status ?? null,
      'status_display' => function($status) {
        $statusMap = [
          'draft' => 'Draft',
          'sedang diproses' => 'Sedang Diproses',
          'menunggu_verifikasi' => 'Menunggu Verifikasi',
          'pending_approval_ibub' => 'Menunggu Persetujuan Ibu Yuni',
          'sent_to_ibub' => 'Terkirim ke Ibu Yuni',
          'proses_ibub' => 'Diproses Ibu Yuni',
          'sent_to_perpajakan' => 'Terkirim ke Team Perpajakan',
          'proses_perpajakan' => 'Diproses Team Perpajakan',
          'sent_to_akutansi' => 'Terkirim ke Team Akutansi',
          'proses_akutansi' => 'Diproses Team Akutansi',
          'menunggu_approved_pengiriman' => 'Menunggu Persetujuan Pengiriman',
          'proses_pembayaran' => 'Diproses Team Pembayaran',
          'sent_to_pembayaran' => 'Terkirim ke Team Pembayaran',
          'approved_data_sudah_terkirim' => 'Data Sudah Terkirim',
          'rejected_data_tidak_lengkap' => 'Ditolak - Data Tidak Lengkap',
          'selesai' => 'Selesai',
          'returned_to_ibua' => 'Dikembalikan ke Ibu Tarapul',
          'returned_to_department' => 'Dikembalikan ke Department',
          'returned_to_bidang' => 'Dikembalikan ke Bidang',
        ];
        return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
      }($dokumen->status ?? null),
      'keterangan' => $dokumen->keterangan ?? null,
      'tanggal_masuk' => $dokumen->tanggal_masuk ? \Carbon\Carbon::parse($dokumen->tanggal_masuk)->format('d M Y, H:i') : null,
    ];

    $taxDataArray = [
      'npwp' => $dokumen->npwp ?? null,
      'status_perpajakan' => $dokumen->status_perpajakan ?? null,
      'no_faktur' => $dokumen->no_faktur ?? null,
      'tanggal_faktur' => $dokumen->tanggal_faktur ? \Carbon\Carbon::parse($dokumen->tanggal_faktur)->format('d M Y') : null,
      'tanggal_selesai_verifikasi_pajak' => $dokumen->tanggal_selesai_verifikasi_pajak ? \Carbon\Carbon::parse($dokumen->tanggal_selesai_verifikasi_pajak)->format('d M Y') : null,
      'jenis_pph' => $dokumen->jenis_pph ?? null,
      'dpp_pph' => $dokumen->dpp_pph ?? null,
      'ppn_terhutang' => $dokumen->ppn_terhutang ?? null,
      'link_dokumen_pajak' => $dokumen->link_dokumen_pajak ?? null,
      'komoditi_perpajakan' => $dokumen->komoditi_perpajakan ?? null,
      'alamat_pembeli' => $dokumen->alamat_pembeli ?? null,
      'no_kontrak' => $dokumen->no_kontrak ?? null,
      'no_invoice' => $dokumen->no_invoice ?? null,
      'tanggal_invoice' => $dokumen->tanggal_invoice ? \Carbon\Carbon::parse($dokumen->tanggal_invoice)->format('d M Y') : null,
      'dpp_invoice' => $dokumen->dpp_invoice ?? null,
      'ppn_invoice' => $dokumen->ppn_invoice ?? null,
      'dpp_ppn_invoice' => $dokumen->dpp_ppn_invoice ?? null,
      'tanggal_pengajuan_pajak' => $dokumen->tanggal_pengajuan_pajak ? \Carbon\Carbon::parse($dokumen->tanggal_pengajuan_pajak)->format('d M Y') : null,
      'dpp_faktur' => $dokumen->dpp_faktur ?? null,
      'ppn_faktur' => $dokumen->ppn_faktur ?? null,
      'selisih_pajak' => $dokumen->selisih_pajak ?? null,
      'keterangan_pajak' => $dokumen->keterangan_pajak ?? null,
      'penggantian_pajak' => $dokumen->penggantian_pajak ?? null,
      'dpp_penggantian' => $dokumen->dpp_penggantian ?? null,
      'ppn_penggantian' => $dokumen->ppn_penggantian ?? null,
      'selisih_ppn' => $dokumen->selisih_ppn ?? null,
    ];
  @endphp
  const documentData = @json($documentDataArray);
  const taxData = @json($taxDataArray);

  function formatCurrency(value) {
    if (!value) return '-';
    return 'Rp ' + parseFloat(value).toLocaleString('id-ID');
  }

  function formatField(label, value, options = {}) {
    if (value === null || value === undefined || value === '') return null;
    
    const { monospace = false, highlight = false, currency = false } = options;
    let displayValue = value;
    
    if (currency && value) {
      displayValue = formatCurrency(value);
    }
    
    return {
      label,
      value: displayValue,
      monospace,
      highlight,
      empty: false
    };
  }

  function renderDocumentModal() {
    const modalBody = document.getElementById('modal-body');
    if (!modalBody) {
      console.error('Modal body not found');
      return;
    }
    modalBody.innerHTML = `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-info-circle"></i>
          Informasi Umum
        </div>
        <div class="modal-grid">
          ${formatField('Nomor Agenda', documentData.nomor_agenda) ? `
            <div class="modal-field">
              <div class="modal-field-label">Nomor Agenda</div>
              <div class="modal-field-value highlight">${documentData.nomor_agenda}</div>
            </div>
          ` : ''}
          ${formatField('Nomor SPP', documentData.nomor_spp) ? `
            <div class="modal-field">
              <div class="modal-field-label">Nomor SPP</div>
              <div class="modal-field-value monospace highlight">${documentData.nomor_spp}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal SPP', documentData.tanggal_spp) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal SPP</div>
              <div class="modal-field-value">${documentData.tanggal_spp}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal Masuk', documentData.tanggal_masuk) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal Masuk</div>
              <div class="modal-field-value">${documentData.tanggal_masuk}</div>
            </div>
          ` : ''}
          ${formatField('Status', documentData.status_display || documentData.status) ? `
            <div class="modal-field">
              <div class="modal-field-label">Status</div>
              <div class="modal-field-value highlight">${documentData.status_display || documentData.status || '-'}</div>
            </div>
          ` : ''}
        </div>
      </div>

      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-file-invoice-dollar"></i>
          Detail Dokumen
        </div>
        <div class="modal-grid">
          ${formatField('Uraian SPP', documentData.uraian_spp) ? `
            <div class="modal-field" style="grid-column: 1 / -1;">
              <div class="modal-field-label">Uraian SPP</div>
              <div class="modal-field-value">${documentData.uraian_spp}</div>
            </div>
          ` : ''}
          ${formatField('Nilai Rupiah', documentData.nilai_rupiah, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">Nilai Rupiah</div>
              <div class="modal-field-value highlight">${formatCurrency(documentData.nilai_rupiah)}</div>
            </div>
          ` : ''}
          ${formatField('Kategori', documentData.kategori) ? `
            <div class="modal-field">
              <div class="modal-field-label">Kategori</div>
              <div class="modal-field-value">${documentData.kategori}</div>
            </div>
          ` : ''}
          ${formatField('Jenis Dokumen', documentData.jenis_dokumen) ? `
            <div class="modal-field">
              <div class="modal-field-label">Jenis Dokumen</div>
              <div class="modal-field-value">${documentData.jenis_dokumen}</div>
            </div>
          ` : ''}
          ${formatField('Jenis Sub Pekerjaan', documentData.jenis_sub_pekerjaan) ? `
            <div class="modal-field">
              <div class="modal-field-label">Jenis Sub Pekerjaan</div>
              <div class="modal-field-value">${documentData.jenis_sub_pekerjaan}</div>
            </div>
          ` : ''}
          ${formatField('Jenis Pembayaran', documentData.jenis_pembayaran) ? `
            <div class="modal-field">
              <div class="modal-field-label">Jenis Pembayaran</div>
              <div class="modal-field-value">${documentData.jenis_pembayaran}</div>
            </div>
          ` : ''}
        </div>
      </div>

      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-building"></i>
          Informasi Pengirim & Penerima
        </div>
        <div class="modal-grid">
          ${formatField('Kebun', documentData.kebun) ? `
            <div class="modal-field">
              <div class="modal-field-label">Kebun</div>
              <div class="modal-field-value">${documentData.kebun}</div>
            </div>
          ` : ''}
          ${formatField('Bagian', documentData.bagian) ? `
            <div class="modal-field">
              <div class="modal-field-label">Bagian Pengirim</div>
              <div class="modal-field-value">${documentData.bagian}</div>
            </div>
          ` : ''}
          ${formatField('Nama Pengirim', documentData.nama_pengirim) ? `
            <div class="modal-field">
              <div class="modal-field-label">Nama Pengirim</div>
              <div class="modal-field-value">${documentData.nama_pengirim}</div>
            </div>
          ` : ''}
          ${formatField('Dibayar Kepada', documentData.dibayar_kepada) ? `
            <div class="modal-field">
              <div class="modal-field-label">Dibayar Kepada</div>
              <div class="modal-field-value highlight">${documentData.dibayar_kepada}</div>
            </div>
          ` : ''}
        </div>
      </div>

      ${(documentData.no_berita_acara || documentData.no_spk || documentData.nomor_miro) ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-file-contract"></i>
          Dokumen Pendukung
        </div>
        <div class="modal-grid">
          ${formatField('No. Berita Acara', documentData.no_berita_acara) ? `
            <div class="modal-field">
              <div class="modal-field-label">No. Berita Acara</div>
              <div class="modal-field-value monospace">${documentData.no_berita_acara}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal Berita Acara', documentData.tanggal_berita_acara) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal Berita Acara</div>
              <div class="modal-field-value">${documentData.tanggal_berita_acara}</div>
            </div>
          ` : ''}
          ${formatField('No. SPK', documentData.no_spk) ? `
            <div class="modal-field">
              <div class="modal-field-label">No. SPK</div>
              <div class="modal-field-value monospace">${documentData.no_spk}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal SPK', documentData.tanggal_spk) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal SPK</div>
              <div class="modal-field-value">${documentData.tanggal_spk}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal Berakhir SPK', documentData.tanggal_berakhir_spk) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal Berakhir SPK</div>
              <div class="modal-field-value">${documentData.tanggal_berakhir_spk}</div>
            </div>
          ` : ''}
          ${formatField('Nomor MIRO', documentData.nomor_miro) ? `
            <div class="modal-field">
              <div class="modal-field-label">Nomor MIRO</div>
              <div class="modal-field-value monospace highlight">${documentData.nomor_miro}</div>
            </div>
          ` : ''}
        </div>
      </div>
      ` : ''}

      ${documentData.keterangan ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-sticky-note"></i>
          Keterangan
        </div>
        <div class="modal-field">
          <div class="modal-field-value">${documentData.keterangan}</div>
        </div>
      </div>
      ` : ''}
    `;
  }

  function renderTaxModal() {
    const modalBody = document.getElementById('modal-body');
    
    // Check if there's any tax data
    const hasTaxData = Object.values(taxData).some(v => v !== null && v !== '');
    
    if (!hasTaxData) {
      modalBody.innerHTML = `
        <div style="text-align: center; padding: 60px 20px;">
          <i class="fas fa-search-dollar" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
          <h3 style="color: #64748b; margin-bottom: 8px;">Belum Ada Data Perpajakan</h3>
          <p style="color: #94a3b8;">Data perpajakan untuk dokumen ini belum diisi.</p>
        </div>
      `;
      return;
    }

    modalBody.innerHTML = `
      ${taxData.npwp || taxData.no_faktur || taxData.jenis_pph ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-id-card"></i>
          Identitas Perpajakan
        </div>
        <div class="modal-grid">
          ${formatField('NPWP', taxData.npwp) ? `
            <div class="modal-field">
              <div class="modal-field-label">NPWP</div>
              <div class="modal-field-value monospace highlight">${taxData.npwp}</div>
            </div>
          ` : ''}
          ${formatField('No. Faktur', taxData.no_faktur) ? `
            <div class="modal-field">
              <div class="modal-field-label">No. Faktur</div>
              <div class="modal-field-value monospace highlight">${taxData.no_faktur}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal Faktur', taxData.tanggal_faktur) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal Faktur</div>
              <div class="modal-field-value">${taxData.tanggal_faktur}</div>
            </div>
          ` : ''}
          ${formatField('Jenis PPh', taxData.jenis_pph) ? `
            <div class="modal-field">
              <div class="modal-field-label">Jenis PPh</div>
              <div class="modal-field-value highlight">${taxData.jenis_pph}</div>
            </div>
          ` : ''}
          ${formatField('Status Perpajakan', taxData.status_perpajakan) ? `
            <div class="modal-field">
              <div class="modal-field-label">Status Perpajakan</div>
              <div class="modal-field-value highlight">${taxData.status_perpajakan}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal Selesai Verifikasi', taxData.tanggal_selesai_verifikasi_pajak) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal Selesai Verifikasi</div>
              <div class="modal-field-value">${taxData.tanggal_selesai_verifikasi_pajak}</div>
            </div>
          ` : ''}
        </div>
      </div>
      ` : ''}

      ${taxData.komoditi_perpajakan || taxData.alamat_pembeli || taxData.no_kontrak ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-building"></i>
          Informasi Pembeli
        </div>
        <div class="modal-grid">
          ${formatField('Komoditi', taxData.komoditi_perpajakan) ? `
            <div class="modal-field">
              <div class="modal-field-label">Komoditi</div>
              <div class="modal-field-value">${taxData.komoditi_perpajakan}</div>
            </div>
          ` : ''}
          ${formatField('Alamat Pembeli', taxData.alamat_pembeli) ? `
            <div class="modal-field" style="grid-column: 1 / -1;">
              <div class="modal-field-label">Alamat Pembeli</div>
              <div class="modal-field-value">${taxData.alamat_pembeli}</div>
            </div>
          ` : ''}
          ${formatField('No. Kontrak', taxData.no_kontrak) ? `
            <div class="modal-field">
              <div class="modal-field-label">No. Kontrak</div>
              <div class="modal-field-value monospace">${taxData.no_kontrak}</div>
            </div>
          ` : ''}
        </div>
      </div>
      ` : ''}

      ${taxData.dpp_pph || taxData.ppn_terhutang ? `
      <div class="tax-summary-card">
        <div class="tax-summary-row">
          <div class="tax-summary-label">DPP PPh</div>
          <div class="tax-summary-value">${formatCurrency(taxData.dpp_pph)}</div>
        </div>
        <div class="tax-summary-row">
          <div class="tax-summary-label">PPN Terhutang</div>
          <div class="tax-summary-value">${formatCurrency(taxData.ppn_terhutang)}</div>
        </div>
      </div>
      ` : ''}

      ${taxData.no_invoice || taxData.dpp_invoice || taxData.ppn_invoice ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-file-invoice"></i>
          Data Invoice
        </div>
        <div class="modal-grid">
          ${formatField('No. Invoice', taxData.no_invoice) ? `
            <div class="modal-field">
              <div class="modal-field-label">No. Invoice</div>
              <div class="modal-field-value monospace">${taxData.no_invoice}</div>
            </div>
          ` : ''}
          ${formatField('Tanggal Invoice', taxData.tanggal_invoice) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal Invoice</div>
              <div class="modal-field-value">${taxData.tanggal_invoice}</div>
            </div>
          ` : ''}
          ${formatField('DPP Invoice', taxData.dpp_invoice, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">DPP Invoice</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_invoice)}</div>
            </div>
          ` : ''}
          ${formatField('PPN Invoice', taxData.ppn_invoice, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">PPN Invoice</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.ppn_invoice)}</div>
            </div>
          ` : ''}
          ${formatField('DPP + PPN Invoice', taxData.dpp_ppn_invoice, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">DPP + PPN Invoice</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_ppn_invoice)}</div>
            </div>
          ` : ''}
        </div>
      </div>
      ` : ''}

      ${taxData.dpp_faktur || taxData.ppn_faktur || taxData.selisih_pajak ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-receipt"></i>
          Data Faktur
        </div>
        <div class="modal-grid">
          ${formatField('DPP Faktur', taxData.dpp_faktur, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">DPP Faktur</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_faktur)}</div>
            </div>
          ` : ''}
          ${formatField('PPN Faktur', taxData.ppn_faktur, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">PPN Faktur</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.ppn_faktur)}</div>
            </div>
          ` : ''}
          ${formatField('Selisih Pajak', taxData.selisih_pajak, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">Selisih Pajak</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.selisih_pajak)}</div>
            </div>
          ` : ''}
        </div>
      </div>
      ` : ''}

      ${taxData.penggantian_pajak || taxData.dpp_penggantian || taxData.ppn_penggantian || taxData.selisih_ppn ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-exchange-alt"></i>
          Penggantian Pajak
        </div>
        <div class="modal-grid">
          ${formatField('Penggantian Pajak', taxData.penggantian_pajak) ? `
            <div class="modal-field">
              <div class="modal-field-label">Penggantian Pajak</div>
              <div class="modal-field-value">${taxData.penggantian_pajak}</div>
            </div>
          ` : ''}
          ${formatField('DPP Penggantian', taxData.dpp_penggantian, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">DPP Penggantian</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_penggantian)}</div>
            </div>
          ` : ''}
          ${formatField('PPN Penggantian', taxData.ppn_penggantian, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">PPN Penggantian</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.ppn_penggantian)}</div>
            </div>
          ` : ''}
          ${formatField('Selisih PPN', taxData.selisih_ppn, { currency: true }) ? `
            <div class="modal-field">
              <div class="modal-field-label">Selisih PPN</div>
              <div class="modal-field-value highlight">${formatCurrency(taxData.selisih_ppn)}</div>
            </div>
          ` : ''}
        </div>
      </div>
      ` : ''}

      ${taxData.tanggal_pengajuan_pajak ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-calendar-check"></i>
          Timeline Perpajakan
        </div>
        <div class="modal-grid">
          ${formatField('Tanggal Pengajuan Pajak', taxData.tanggal_pengajuan_pajak) ? `
            <div class="modal-field">
              <div class="modal-field-label">Tanggal Pengajuan Pajak</div>
              <div class="modal-field-value">${taxData.tanggal_pengajuan_pajak}</div>
            </div>
          ` : ''}
        </div>
      </div>
      ` : ''}

      ${taxData.keterangan_pajak ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-sticky-note"></i>
          Keterangan Pajak
        </div>
        <div class="modal-field">
          <div class="modal-field-value">${taxData.keterangan_pajak}</div>
        </div>
      </div>
      ` : ''}

      ${taxData.link_dokumen_pajak ? `
      <div class="modal-section">
        <div class="modal-section-title">
          <i class="fas fa-link"></i>
          Link Dokumen Pajak
        </div>
        <div class="modal-field">
          <a href="${taxData.link_dokumen_pajak}" target="_blank" class="modal-field-value" style="color: #083E40; text-decoration: underline;">
            <i class="fas fa-external-link-alt mr-2"></i>
            ${taxData.link_dokumen_pajak}
          </a>
        </div>
      </div>
      ` : ''}
    `;
  }

  // Modal functionality
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('detail-modal');
    const modalClose = document.getElementById('modal-close');
    const documentCard = document.getElementById('document-info-card');
    const taxCard = document.getElementById('tax-data-card');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');
    const modalIcon = document.getElementById('modal-icon');

    // Check if elements exist
    if (!modal || !modalClose || !documentCard || !taxCard || !modalTitle || !modalSubtitle || !modalIcon) {
      console.error('Modal elements not found');
      return;
    }

    function openModal(type) {
      console.log('Opening modal:', type);
      try {
        if (type === 'document') {
          modalTitle.textContent = 'Detail Informasi Dokumen';
          modalSubtitle.textContent = 'Informasi lengkap dokumen';
          modalIcon.innerHTML = '<i class="fas fa-file-alt"></i>';
          modalIcon.style.background = 'linear-gradient(135deg, #083E40 0%, #889717 100%)';
          renderDocumentModal();
        } else if (type === 'tax') {
          modalTitle.textContent = 'Detail Data Perpajakan';
          modalSubtitle.textContent = 'Informasi lengkap perpajakan';
          modalIcon.innerHTML = '<i class="fas fa-calculator"></i>';
          modalIcon.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
          renderTaxModal();
        } else if (type === 'activity') {
          // Content already set by showActivityDetail
          // Just show the modal
        }
        
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        console.log('Modal opened successfully');
      } catch (error) {
        console.error('Error opening modal:', error);
      }
    }

    function closeModal() {
      modal.classList.remove('show');
      document.body.style.overflow = '';
    }

    // Add click event listeners
    if (documentCard) {
      documentCard.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Document card clicked');
        openModal('document');
      });
      console.log('Document card event listener attached');
    } else {
      console.error('Document card not found');
    }

    if (taxCard) {
      taxCard.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Tax card clicked');
        openModal('tax');
      });
      console.log('Tax card event listener attached');
    } else {
      console.error('Tax card not found');
    }

    if (modalClose) {
      modalClose.addEventListener('click', closeModal);
    }
    
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal.classList.contains('show')) {
        closeModal();
      }
    });

    // Smooth scroll to active stage on load
    const activeStage = document.querySelector('.timeline-stage.active');
    if (activeStage) {
      setTimeout(() => {
        activeStage.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 500);
    }

    // Helper function to escape HTML (define before use in showActivityDetail)
    window.escapeHtml = function(text) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return String(text).replace(/[&<>"']/g, m => map[m]);
    };

    // Function to show activity detail
    window.showActivityDetail = function(activityId) {
      const activityItem = document.querySelector(`[data-activity-id="${activityId}"]`);
      if (!activityItem) return;

      const actionDescription = activityItem.getAttribute('data-action-description') || 'Activity';
      const performedBy = activityItem.getAttribute('data-performed-by') || 'System';
      const actionAt = activityItem.getAttribute('data-action-at') || '-';
      const actionAtRelative = activityItem.getAttribute('data-action-at-relative') || '-';
      const stage = activityItem.getAttribute('data-stage') || '-';
      const action = activityItem.getAttribute('data-action') || '-';
      const detailsJson = activityItem.getAttribute('data-details') || '{}';
      
      let details = {};
      try {
        details = JSON.parse(detailsJson);
      } catch (e) {
        details = {};
      }

      // Map stage to display name
      const stageMap = {
        'sender': 'Ibu Tara',
        'reviewer': 'Team Verifikasi',
        'tax': 'Team Perpajakan',
        'accounting': 'Team Akutansi',
        'payment': 'Pembayaran',
      };
      const stageDisplay = stageMap[stage] || stage;

      // Build modal content
      modalTitle.textContent = 'Detail Activity';
      modalSubtitle.textContent = 'Informasi lengkap aktivitas';
      modalIcon.innerHTML = '<i class="fas fa-history"></i>';
      modalIcon.style.background = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';

      let modalContent = `
        <div style="display: grid; gap: 24px;">
          <div style="background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Aktivitas</div>
            <div style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 20px;">${window.escapeHtml(actionDescription)}</div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
              <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Dilakukan Oleh</div>
              <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-user" style="color: #3b82f6;"></i>
                ${window.escapeHtml(performedBy)}
              </div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
              <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Stage</div>
              <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-layer-group" style="color: #10b981;"></i>
                ${window.escapeHtml(stageDisplay)}
              </div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
              <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Waktu</div>
              <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="far fa-clock" style="color: #f59e0b;"></i>
                ${window.escapeHtml(actionAt)}
              </div>
              <div style="font-size: 13px; color: #64748b; margin-top: 4px;">${window.escapeHtml(actionAtRelative)}</div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
              <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Action Type</div>
              <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-code" style="color: #8b5cf6;"></i>
                ${window.escapeHtml(action)}
              </div>
            </div>
          </div>
      `;

      // Add details if available
      if (details && Object.keys(details).length > 0) {
        modalContent += `
          <div style="background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
              <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
              Detail Tambahan
            </div>
            <div style="display: grid; gap: 12px;">
        `;
        
        for (const [key, value] of Object.entries(details)) {
          const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
          let displayValue = value;
          if (typeof value === 'object') {
            displayValue = JSON.stringify(value, null, 2);
          }
          modalContent += `
            <div style="padding: 12px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #3b82f6;">
              <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">${window.escapeHtml(label)}</div>
              <div style="font-size: 14px; font-weight: 500; color: #0f172a;">${window.escapeHtml(String(displayValue))}</div>
            </div>
          `;
        }
        
        modalContent += `
            </div>
          </div>
        `;
      }

      modalContent += `</div>`;
      
      document.getElementById('modal-body').innerHTML = modalContent;
      openModal('activity');
    };
  });
</script>

@endsection

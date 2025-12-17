@extends('layouts/app')

@section('content')
<style>
:root {
  --primary-color: #0f4c5c;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --info-color: #1e7e8d;
  --secondary-color: #9ca3af;
  --light-bg: #f9fafb;
  --border-color: #e5e7eb;
  --text-primary: #111827;
  --text-secondary: #6b7280;
  --teal-600: #0d9488;
  --teal-700: #0f766e;
  --emerald-500: #10b981;
  --emerald-600: #059669;
}

body {
  background: #f3f4f6;
  min-height: 100vh;
  padding: 1.5rem 0;
  transition: background-color 0.3s ease;
}

.dark body {
  background: #0f172a; /* slate-900 */
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
  transition: background-color 0.3s ease;
}

.dark .workflow-header {
  background: #1e293b; /* slate-800 */
}

.workflow-title-section h1 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: color 0.3s ease;
}

.dark .workflow-title-section h1 {
  color: #f1f5f9; /* slate-100 */
}

.workflow-subtitle {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 0.25rem 0 0 0;
  transition: color 0.3s ease;
}

.dark .workflow-subtitle {
  color: #94a3b8; /* slate-400 */
}

.workflow-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow-x: auto;
  transition: background-color 0.3s ease;
}

.dark .workflow-content {
  background: #1e293b; /* slate-800 */
}

/* Premium Card Style Workflow */
.workflow-cards-container {
  position: relative;
  padding: 2rem 0;
  margin-bottom: 2rem;
  overflow-x: auto;
}

.workflow-cards-wrapper {
  display: flex;
  align-items: flex-start;
  gap: 2rem;
  position: relative;
  min-width: fit-content;
  padding: 0 1rem;
}

/* Base Premium Workflow Card */
.workflow-card {
  position: relative;
  width: 240px;
  min-width: 240px;
  background: white;
  border-radius: 0.75rem; /* rounded-xl */
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s ease, border-color 0.3s ease;
  cursor: pointer;
  flex-shrink: 0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* shadow-md */
  border: 1px solid transparent;
}

.dark .workflow-card {
  background: #1e293b; /* slate-800 */
}

/* Completed Card Style - Solid & Clear */
.workflow-card.completed {
  border: 1px solid #e5e7eb;
  opacity: 1;
}

.workflow-card.completed .card-header {
  background: var(--emerald-500); /* bg-emerald-500 solid */
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.workflow-card.completed .card-header-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
}

.workflow-card.completed .card-body {
  padding: 1.25rem;
  background: white;
  transition: background-color 0.3s ease;
}

.dark .workflow-card.completed .card-body {
  background: #1e293b; /* slate-800 */
}

/* Active Card Style - The Hero with Glow Effect */
.workflow-card.active {
  transform: scale(1.05); /* scale-105 */
  border: 2px solid var(--emerald-500); /* border-2 border-emerald-500 */
  background: white;
  box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.4); /* shadow-[0_10px_30px_-10px_rgba(16,185,129,0.4)] */
  z-index: 10;
  position: relative;
}

.workflow-card.active .card-header {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(13, 148, 136, 0.05) 100%);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.workflow-card.active .card-header-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--emerald-500) 0%, var(--emerald-600) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 28px;
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}

.workflow-card.active .card-body {
  padding: 1.25rem;
  background: white;
  transition: background-color 0.3s ease;
}

.dark .workflow-card.active .card-body {
  background: #1e293b; /* slate-800 */
}

/* Current Position Badge - "Sedang Proses" dengan animate-pulse */
.current-position-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: linear-gradient(135deg, var(--emerald-500) 0%, var(--emerald-600) 100%);
  color: white;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Pending Card Style - Dimmed */
.workflow-card.pending {
  background: #f8fafc; /* bg-slate-50 */
  border: 1px dashed #cbd5e1; /* border dashed tipis */
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  opacity: 0.85;
}

.workflow-card.pending .card-header {
  background: #f1f5f9;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.workflow-card.pending .card-header-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 20px;
}

.workflow-card.pending .card-body {
  padding: 1.25rem;
  background: #f8fafc;
}

.workflow-card.pending .card-label,
.workflow-card.pending .card-name,
.workflow-card.pending .card-description,
.workflow-card.pending .card-timestamp {
  color: #94a3b8; /* text-slate-400 */
}

/* Returned Card Style */
.workflow-card.returned {
  border: 2px solid var(--danger-color);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

.workflow-card.returned .card-header {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.workflow-card.returned .card-header-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--danger-color);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
}

.workflow-card.returned .card-header-icon::after {
  content: '↩';
  position: absolute;
  top: -6px;
  right: -6px;
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
}

.workflow-card.returned .card-body {
  padding: 1.25rem;
  background: white;
  border-bottom: 4px solid var(--danger-color);
}

/* Card Content */
.card-label {
  font-size: 10px;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.8px;
  margin-bottom: 0.75rem;
  text-align: center;
  transition: color 0.3s ease;
}

.workflow-card.completed .card-label,
.workflow-card.active .card-label {
  color: var(--text-primary);
}

.dark .workflow-card.completed .card-label,
.dark .workflow-card.active .card-label {
  color: #f1f5f9; /* slate-100 */
}

.dark .workflow-card.pending .card-label {
  color: #94a3b8; /* slate-400 */
}

.card-name {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  text-align: center;
  transition: color 0.3s ease;
}

.dark .workflow-card.completed .card-name,
.dark .workflow-card.active .card-name {
  color: #f1f5f9; /* slate-100 */
}

.dark .workflow-card.pending .card-name {
  color: #94a3b8; /* slate-400 */
}

.card-description {
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 1rem;
  text-align: center;
  line-height: 1.5;
  min-height: 36px;
  transition: color 0.3s ease;
}

.dark .workflow-card.completed .card-description,
.dark .workflow-card.active .card-description {
  color: #cbd5e1; /* slate-300 */
}

/* User Avatar */
.card-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--teal-600) 0%, var(--emerald-500) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 20px;
  margin: 0 auto 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border: 3px solid white;
}

.workflow-card.active .card-avatar {
  width: 72px;
  height: 72px;
  font-size: 24px;
  border: 4px solid var(--emerald-500);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
}

.workflow-card.pending .card-avatar {
  width: 56px;
  height: 56px;
  font-size: 18px;
  background: #e2e8f0;
  color: #94a3b8;
  border: 2px solid #cbd5e1;
  box-shadow: none;
}

/* Card Timestamp */
.card-timestamp {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 11px;
  color: var(--text-secondary);
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
  transition: color 0.3s ease, border-color 0.3s ease;
}

.dark .card-timestamp {
  border-top-color: #334155; /* slate-700 */
}

.workflow-card.completed .card-timestamp,
.workflow-card.active .card-timestamp {
  color: var(--emerald-600);
  font-weight: 600;
}

.dark .workflow-card.completed .card-timestamp,
.dark .workflow-card.active .card-timestamp {
  color: #34d399; /* emerald-400 */
}

.card-timestamp i {
  font-size: 10px;
}

/* Connector Between Cards - Gradient dari Hijau ke Abu-abu */
.workflow-connector {
  position: relative;
  width: 2rem;
  height: 6px;
  align-self: center;
  flex-shrink: 0;
  margin: 0 0.5rem;
}

.workflow-connector-line {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  transform: translateY(-50%);
}

/* Connector untuk Completed ke Completed - Hijau Solid Tebal */
.workflow-connector.completed .workflow-connector-line {
  background: var(--emerald-500); /* Hijau Solid Tebal */
  height: 6px;
  box-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
}

/* Connector untuk Completed ke Active - Gradien Hijau ke Hijau Lebih Terang */
.workflow-connector.completed-to-active .workflow-connector-line {
  background: linear-gradient(90deg, var(--emerald-500) 0%, var(--emerald-600) 100%);
  height: 6px;
  box-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
}

/* Connector untuk Active ke Pending - Gradien dari Hijau ke Abu-abu */
.workflow-connector.active-to-pending .workflow-connector-line {
  background: linear-gradient(90deg, var(--emerald-500) 0%, #94a3b8 100%);
  height: 6px;
}

/* Connector untuk Completed ke Pending - Gradien dari Hijau ke Abu-abu */
.workflow-connector.completed-to-pending .workflow-connector-line {
  background: linear-gradient(90deg, var(--emerald-500) 0%, #94a3b8 100%);
  height: 6px;
}

/* Connector untuk Pending ke Pending - Abu-abu */
.workflow-connector.pending .workflow-connector-line {
  background: #e5e7eb;
  height: 4px;
  opacity: 0.5;
}

/* Cycle Badge */
.workflow-cycle-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background: linear-gradient(135deg, var(--warning-color) 0%, var(--danger-color) 100%);
  color: white;
  padding: 4px 8px;
  border-radius: 10px;
  font-size: 9px;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  z-index: 10;
}

/* Hover Effect */
.workflow-card:hover {
  transform: translateY(-4px);
}

.workflow-card.active:hover {
  transform: scale(1.05) translateY(-4px);
}

/* Document Info Panel - Keep existing styles */
.document-info-panel {
  background: var(--light-bg);
  border-radius: 10px;
  padding: 1.25rem;
  margin-top: 1.5rem;
  transition: background-color 0.3s ease;
}

.dark .document-info-panel {
  background: #0f172a; /* slate-900 */
}

.document-info-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: color 0.3s ease;
}

.dark .document-info-title {
  color: #f1f5f9; /* slate-100 */
}

/* Accordion Styles - Keep existing */
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
  transition: all 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
}

.dark .accordion-item {
  background: #1e293b; /* slate-800 */
  border-color: #334155; /* slate-700 */
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
  transition: background 0.2s ease, background-color 0.3s ease;
  user-select: none;
}

.accordion-header:hover {
  background: var(--light-bg);
}

.dark .accordion-header {
  background: #1e293b; /* slate-800 */
}

.dark .accordion-header:hover {
  background: #0f172a; /* slate-900 */
}

.accordion-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-primary);
  transition: color 0.3s ease;
}

.dark .accordion-title {
  color: #f1f5f9; /* slate-100 */
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
  transition: max-height 0.3s ease, background-color 0.3s ease;
  background: var(--light-bg);
}

.dark .accordion-content {
  background: #0f172a; /* slate-900 */
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
  transition: all 0.2s ease, background-color 0.3s ease;
}

.dark .document-info-item {
  background: #1e293b; /* slate-800 */
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
  transition: color 0.3s ease;
}

.dark .document-info-label {
  color: #94a3b8; /* slate-400 */
}

.document-info-value {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
  word-break: break-word;
  transition: color 0.3s ease;
}

.dark .document-info-value {
  color: #f1f5f9; /* slate-100 */
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

/* Responsive */
@media (max-width: 1024px) {
  .workflow-cards-wrapper {
    gap: 1.5rem;
  }
  
  .workflow-card {
    width: 220px;
    min-width: 220px;
  }
  
  .workflow-card.active {
    transform: scale(1.03);
  }
}

@media (max-width: 768px) {
  .workflow-container {
    padding: 1rem;
  }

  .workflow-content {
    padding: 1rem;
  }
  
  .workflow-cards-container {
    padding: 1.5rem 0;
  }
  
  .workflow-cards-wrapper {
    gap: 1rem;
    padding: 0 0.5rem;
  }
  
  .workflow-card {
    width: 200px;
    min-width: 200px;
  }
  
  .workflow-card.active {
    transform: scale(1.02);
  }
  
  .workflow-connector {
    width: 1.5rem;
  }
  
  .card-avatar {
    width: 56px;
    height: 56px;
    font-size: 18px;
  }
  
  .workflow-card.active .card-avatar {
    width: 64px;
    height: 64px;
    font-size: 20px;
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
    <!-- Premium Card Style Workflow -->
    <div class="workflow-cards-container">
      <div class="workflow-cards-wrapper">
        @foreach($workflowStages as $index => $stage)
          @php
            $stageLogs = isset($activityLogsByStage) && $activityLogsByStage->has($stage['id']) 
                ? $activityLogsByStage[$stage['id']] 
                : collect();
            
            $cardStatus = 'pending';
            if($stage['status'] === 'completed') {
              $cardStatus = 'completed';
            } elseif($stage['status'] === 'processing' || $stage['status'] === 'active') {
              $cardStatus = 'active';
            } elseif($stage['status'] === 'returned') {
              $cardStatus = 'returned';
            }
            
            // Get first letter of name for avatar
            $avatarInitial = substr($stage['name'], 0, 1);
          @endphp
          
          <!-- Workflow Card -->
          <div class="workflow-card {{ $cardStatus }}">
            @if($cardStatus === 'active')
              <div class="current-position-badge">Sedang Proses</div>
            @endif
            
            @if(isset($stage['hasCycle']) && $stage['hasCycle'] && isset($stage['cycleInfo']['attemptCount']) && $stage['cycleInfo']['attemptCount'] > 1)
              <div class="workflow-cycle-badge">
                <i class="fas fa-redo"></i> {{ $stage['cycleInfo']['attemptCount'] }}x
              </div>
            @endif
            
            <!-- Card Header -->
            <div class="card-header">
              <div class="card-header-icon">
                @if($cardStatus === 'completed')
                  <i class="fas fa-check"></i>
                @else
                  <i class="fas {{ $stage['icon'] }}"></i>
                @endif
              </div>
            </div>
            
            <!-- Card Body -->
            <div class="card-body">
              <div class="card-label">{{ $stage['label'] }}</div>
              
              <!-- User Avatar -->
              <div class="card-avatar">
                {{ $avatarInitial }}
              </div>
              
              <div class="card-name">{{ $stage['name'] }}</div>
              <div class="card-description">{{ $stage['description'] }}</div>
              
              <!-- Timestamp -->
              @if($stage['timestamp'])
                <div class="card-timestamp">
                  <i class="far fa-clock"></i>
                  <span>{{ $stage['timestamp']->format('d M Y') }}</span>
                  <span>•</span>
                  <span>{{ $stage['timestamp']->format('H:i') }}</span>
                </div>
              @else
                <div class="card-timestamp" style="color: #94a3b8;">
                  <i class="far fa-clock"></i>
                  <span>Menunggu</span>
                </div>
              @endif
            </div>
          </div>
          
          <!-- Connector -->
          @if($index < count($workflowStages) - 1)
            @php
              $nextStage = $workflowStages[$index + 1];
              $nextStatus = 'pending';
              if($nextStage['status'] === 'completed') {
                $nextStatus = 'completed';
              } elseif($nextStage['status'] === 'processing' || $nextStage['status'] === 'active') {
                $nextStatus = 'active';
              }
              
              // Determine connector class based on current and next status
              $connectorClass = '';
              if($cardStatus === 'completed' && $nextStatus === 'completed') {
                $connectorClass = 'completed'; // Hijau solid tebal
              } elseif($cardStatus === 'completed' && $nextStatus === 'active') {
                $connectorClass = 'completed-to-active'; // Gradien hijau ke hijau
              } elseif($cardStatus === 'active' && $nextStatus === 'pending') {
                $connectorClass = 'active-to-pending'; // Gradien hijau ke abu-abu
              } elseif($cardStatus === 'completed' && $nextStatus === 'pending') {
                $connectorClass = 'completed-to-pending'; // Gradien hijau ke abu-abu
              } elseif($cardStatus === 'pending' && $nextStatus === 'pending') {
                $connectorClass = 'pending'; // Abu-abu
              } else {
                $connectorClass = ''; // Default abu-abu
              }
            @endphp
            <div class="workflow-connector {{ $connectorClass }}">
              <div class="workflow-connector-line"></div>
            </div>
          @endif
        @endforeach
      </div>
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
                <div class="document-info-label">Kriteria CF</div>
                <div class="document-info-value">{{ $dokumen->kategori ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Sub Kriteria</div>
                <div class="document-info-value">{{ $dokumen->jenis_dokumen ?? '-' }}</div>
              </div>
              <div class="document-info-item">
                <div class="document-info-label">Item Sub Kriteria</div>
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
</script>

@endsection

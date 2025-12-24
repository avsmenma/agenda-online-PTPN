@extends('layouts.app')

@section('content')
<style>
  /* Modern Scrollbar */
  .custom-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    border-radius: 10px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #9ba820 100%);
  }

  /* Smooth Animations */
  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes pulse-glow {
    0%, 100% {
      box-shadow: 0 0 20px rgba(8, 62, 64, 0.3);
    }
    50% {
      box-shadow: 0 0 30px rgba(136, 151, 23, 0.5);
    }
  }

  @keyframes shimmer {
    0% {
      background-position: -1000px 0;
    }
    100% {
      background-position: 1000px 0;
    }
  }

  .animate-slide-in {
    animation: slideInUp 0.6s ease-out;
  }

  /* Glassmorphism Effect */
  .glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1);
  }

  /* Workflow Stage Cards */
  .workflow-stage-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .workflow-stage-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
  }

  .workflow-stage-card:hover::before {
    left: 100%;
  }

  .workflow-stage-card.active {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(8, 62, 64, 0.25);
  }

  .workflow-stage-card.completed {
    border-color: #10b981;
  }

  .workflow-stage-card.pending {
    opacity: 0.6;
  }

  .workflow-stage-card.returned {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  }

  /* Stage Icon */
  .stage-icon {
    transition: all 0.3s ease;
  }

  .workflow-stage-card.active .stage-icon {
    animation: pulse-glow 2s ease-in-out infinite;
  }

  /* Connector Line */
  .workflow-connector {
    position: relative;
    height: 3px;
    transition: all 0.4s ease;
  }

  .workflow-connector.active {
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #10b981 100%);
    background-size: 200% 100%;
    animation: shimmer 2s linear infinite;
  }

  .workflow-connector.completed {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }

  /* Status Badge */
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    animation: slideInUp 0.4s ease-out;
  }

  .status-badge.active {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
  }

  .status-badge.returned {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
  }

  /* Hero Card */
  .hero-card {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    position: relative;
    overflow: hidden;
  }

  .hero-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: pulse 4s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
      opacity: 0.5;
    }
    50% {
      transform: scale(1.1);
      opacity: 0.8;
    }
  }

  /* Info Card */
  .info-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
    border: 1px solid rgba(8, 62, 64, 0.1);
    transition: all 0.3s ease;
  }

  .info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(8, 62, 64, 0.15);
  }

  /* Activity Log */
  .activity-log-item {
    position: relative;
    padding-left: 24px;
    padding-bottom: 16px;
    border-left: 2px solid #e5e7eb;
  }

  .activity-log-item::before {
    content: '';
    position: absolute;
    left: -6px;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #083E40;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #e5e7eb;
  }

  .activity-log-item:last-child {
    border-left: none;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .workflow-stage-card {
      min-width: 280px;
    }
  }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/30 to-slate-50 pb-20">
  {{-- Sticky Header with Progress --}}
  <div class="sticky top-0 z-50 glass-card border-b border-slate-200/50">
    @php
      $currentProgress = $dokumen->progress_percentage ?? 0;
      $progress = is_numeric($currentProgress) ? $currentProgress : 0;
    @endphp
    
    {{-- Progress Bar --}}
    <div class="w-full h-2 bg-slate-100/50">
      <div class="h-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-500 transition-all duration-1000 ease-out relative overflow-hidden" 
           style="width: {{ $progress }}%">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer"></div>
      </div>
    </div>

    {{-- Header Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="animate-slide-in">
          <div class="flex items-center gap-3 mb-2">
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg">
              AGENDA #{{ $dokumen->nomor_agenda }}
            </div>
            <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-slate-900 via-emerald-700 to-slate-900 bg-clip-text text-transparent">
              Workflow Tracking
            </h1>
          </div>
          <p class="text-sm text-slate-600 flex items-center gap-2">
            <i class="fas fa-file-invoice text-emerald-500"></i>
            Memantau perjalanan dokumen <span class="font-semibold text-slate-800">{{ $dokumen->nomor_spp }}</span>
          </p>
        </div>
        
        <div class="flex items-center gap-4 animate-slide-in" style="animation-delay: 0.1s">
          @if($dokumen->deadline_at)
            <div class="hidden md:block text-right px-4 py-2 bg-white/80 rounded-xl border border-slate-200">
              <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Deadline</p>
              <p class="text-sm font-bold text-slate-800">
                {{ \Carbon\Carbon::parse($dokumen->deadline_at)->format('d M Y') }}
              </p>
            </div>
          @endif
          <a href="{{ $dashboardUrl ?? '/owner/dashboard' }}"
             class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-600 transition-all duration-300 shadow-sm hover:shadow-md">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    {{-- Workflow Stages --}}
    <section class="animate-slide-in" style="animation-delay: 0.2s">
      <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
          <i class="fas fa-route text-emerald-500"></i>
          Progres Alur Kerja
        </h2>
        <p class="text-sm text-slate-500 mt-1">Lacak perjalanan dokumen melalui setiap tahap</p>
      </div>

      {{-- Horizontal Scroll Container --}}
      <div class="overflow-x-auto pb-6 -mx-4 px-4 custom-scrollbar">
        <div class="flex items-start gap-6 min-w-max">
          @foreach($workflowStages as $index => $stage)
            @php
              $status = $stage['status'] ?? 'pending';
              $isLast = $loop->last;
              $isActive = $status === 'processing' || $status === 'active';
              $isCompleted = $status === 'completed' || $status === 'selesai';
              $isReturned = $status === 'returned';

              // Card Classes
              $cardClasses = "workflow-stage-card relative w-80 flex-shrink-0 rounded-2xl p-6 border-2 transition-all duration-300";
              
              if ($isActive) {
                $cardClasses .= " active border-emerald-400 bg-gradient-to-br from-emerald-50 to-white shadow-xl";
              } elseif ($isCompleted) {
                $cardClasses .= " completed border-emerald-300 bg-gradient-to-br from-emerald-50/50 to-white shadow-lg";
              } elseif ($isReturned) {
                $cardClasses .= " returned border-amber-400 bg-gradient-to-br from-amber-50 to-white shadow-lg";
              } else {
                $cardClasses .= " pending border-slate-200 bg-white/60";
              }

              // Icon Classes
              $iconClasses = "stage-icon w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold transition-all duration-300";
              
              if ($isActive) {
                $iconClasses .= " bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg";
              } elseif ($isCompleted) {
                $iconClasses .= " bg-gradient-to-br from-emerald-400 to-emerald-500 text-white shadow-md";
              } elseif ($isReturned) {
                $iconClasses .= " bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-md";
              } else {
                $iconClasses .= " bg-slate-200 text-slate-400";
              }

              // Title Classes
              $titleClasses = "text-lg font-bold mb-1";
              if ($isActive) {
                $titleClasses .= " text-slate-900";
              } elseif ($isCompleted) {
                $titleClasses .= " text-emerald-700";
              } elseif ($isReturned) {
                $titleClasses .= " text-amber-700";
              } else {
                $titleClasses .= " text-slate-500";
              }
            @endphp

            {{-- Stage Card --}}
            <div class="{{ $cardClasses }}">
              {{-- Status Badge --}}
              @if($isActive)
                <div class="absolute -top-3 right-4 z-10">
                  <span class="status-badge active">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    Sedang Diproses
                  </span>
                </div>
              @elseif($isReturned)
                <div class="absolute -top-3 right-4 z-10">
                  <span class="status-badge returned">
                    <i class="fas fa-undo-alt text-xs"></i>
                    Dikembalikan
                  </span>
                </div>
              @endif

              {{-- Card Content --}}
              <div class="space-y-4">
                {{-- Header --}}
                <div class="flex items-start gap-4">
                  <div class="{{ $iconClasses }}">
                    @if($isCompleted)
                      <i class="fas fa-check"></i>
                    @elseif($isReturned)
                      <i class="fas fa-undo-alt"></i>
                    @else
                      <i class="fas {{ $stage['icon'] ?? 'fa-circle' }}"></i>
                    @endif
                  </div>
                  <div class="flex-1 pt-1">
                    <p class="text-xs font-bold tracking-wider text-slate-400 uppercase mb-1">
                      {{ $stage['label'] ?? 'STAGE' }}
                    </p>
                    <h3 class="{{ $titleClasses }}">
                      {{ $stage['name'] ?? 'Unknown' }}
                    </h3>
                  </div>
                </div>

                {{-- Description --}}
                <p class="text-sm {{ $isActive || $isCompleted ? 'text-slate-600' : 'text-slate-400' }} leading-relaxed">
                  {{ $stage['description'] ?? 'Menunggu proses' }}
                </p>

                {{-- Timestamp --}}
                @if(!empty($stage['timestamp']))
                  <div class="flex items-center gap-2 text-xs text-slate-500 pt-3 border-t border-slate-100">
                    <i class="far fa-clock"></i>
                    <span>{{ \Carbon\Carbon::parse($stage['timestamp'])->format('d M Y, H:i') }}</span>
                  </div>
                @endif

                {{-- Return/Cycle Info --}}
                @if(($stage['hasCycle'] ?? false) || ($stage['hasReturn'] ?? false))
                  <div class="mt-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                    @if($stage['hasCycle'] ?? false)
                      <p class="text-xs font-semibold text-amber-700 mb-1">
                        <i class="fas fa-history mr-1"></i>
                        Resubmission #{{ $stage['cycleInfo']['cycleCount'] ?? 1 }}
                      </p>
                    @endif
                    @if($stage['hasReturn'] ?? false)
                      <p class="text-xs text-amber-600 mt-1">
                        {{ $stage['returnInfo']['alasan'] ?? 'Tidak ada alasan' }}
                      </p>
                    @endif
                  </div>
                @endif
              </div>
            </div>

            {{-- Connector --}}
            @if(!$isLast)
              @php
                $nextStage = $workflowStages[$index + 1];
                $nextStatus = $nextStage['status'] ?? 'pending';
                
                $connectorClass = "workflow-connector w-12 h-1 rounded-full mx-3 mt-12";
                
                if ($isCompleted && ($nextStatus === 'completed' || $nextStatus === 'selesai')) {
                  $connectorClass .= " completed";
                } elseif ($isCompleted && ($nextStatus === 'processing' || $nextStatus === 'active')) {
                  $connectorClass .= " active";
                } elseif ($isActive) {
                  $connectorClass .= " active";
                } else {
                  $connectorClass .= " bg-slate-200";
                }
              @endphp
              <div class="{{ $connectorClass }}"></div>
            @endif
          @endforeach
        </div>
      </div>
    </section>

    {{-- Information Grid --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 animate-slide-in" style="animation-delay: 0.3s">
      
      {{-- Main Info (Left Column) --}}
      <div class="lg:col-span-8 space-y-6">
        
        {{-- Hero Financial Card --}}
        <div class="hero-card rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden">
          <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
          
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-6">
              <div>
                <p class="text-emerald-100 font-semibold tracking-wide uppercase text-sm mb-2">Nilai Nominal</p>
                <h2 class="text-4xl sm:text-5xl font-bold tracking-tight">
                  <span class="text-emerald-300">Rp</span>
                  {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}
                </h2>
              </div>
              <div class="hidden md:block">
                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fas fa-money-bill-wave text-4xl text-white"></i>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-white/20">
              <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                <p class="text-emerald-100 text-xs uppercase font-semibold mb-2">Nomor SPP</p>
                <p class="font-mono text-lg font-bold">{{ $dokumen->nomor_spp ?? '-' }}</p>
              </div>
              <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                <p class="text-emerald-100 text-xs uppercase font-semibold mb-2">Dibayar Kepada</p>
                <p class="font-semibold text-lg truncate">
                  {{ $dokumen->dibayarKepadas->first()?->nama_penerima ?? $dokumen->dibayar_kepada ?? '-' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        {{-- Document Information Card --}}
        <div class="info-card">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
              <i class="fas fa-file-alt text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Informasi Dokumen</h3>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="group">
              <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2 group-hover:text-emerald-600 transition-colors">
                Uraian SPP
              </p>
              <p class="text-slate-800 font-semibold leading-relaxed">{{ $dokumen->uraian_spp ?? '-' }}</p>
            </div>

            <div class="group">
              <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2 group-hover:text-emerald-600 transition-colors">
                Nomor Agenda
              </p>
              <p class="text-slate-800 font-semibold">{{ $dokumen->nomor_agenda ?? '-' }}</p>
            </div>

            <div class="group">
              <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2 group-hover:text-emerald-600 transition-colors">
                Jenis Dokumen
              </p>
              <p class="text-slate-800 font-semibold">{{ $dokumen->jenis_dokumen ?? '-' }}</p>
            </div>

            <div class="group">
              <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2 group-hover:text-emerald-600 transition-colors">
                Kategori
              </p>
              <p class="text-slate-800 font-semibold">{{ $dokumen->kategori ?? '-' }}</p>
            </div>

            <div class="group">
              <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2 group-hover:text-emerald-600 transition-colors">
                Bagian Pengirim
              </p>
              <p class="text-slate-800 font-semibold">{{ $dokumen->bagian ?? '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Sidebar (Right Column) --}}
      <div class="lg:col-span-4 space-y-6">
        
        {{-- Tax Data Card --}}
        <div class="info-card">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
              <i class="fas fa-calculator text-white"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Data Perpajakan</h3>
          </div>

          <div class="space-y-4">
            @if($dokumen->npwp || $dokumen->no_faktur)
              <div class="p-4 bg-gradient-to-br from-slate-50 to-white rounded-xl border border-slate-100">
                <p class="text-xs text-slate-500 uppercase font-bold mb-2">NPWP</p>
                <p class="font-mono text-slate-900 font-semibold">{{ $dokumen->npwp ?? '-' }}</p>
              </div>
              <div class="p-4 bg-gradient-to-br from-slate-50 to-white rounded-xl border border-slate-100">
                <p class="text-xs text-slate-500 uppercase font-bold mb-2">No. Faktur</p>
                <p class="font-mono text-slate-900 font-semibold">{{ $dokumen->no_faktur ?? '-' }}</p>
              </div>
              @if($dokumen->jenis_pph)
                <div class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl border border-emerald-200">
                  <p class="text-xs text-emerald-700 uppercase font-bold mb-2">Jenis PPh</p>
                  <p class="text-slate-900 font-semibold">{{ $dokumen->jenis_pph }}</p>
                </div>
              @endif
            @else
              <div class="text-center py-8 text-slate-400">
                <i class="fas fa-search-dollar text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">Belum ada data perpajakan</p>
              </div>
            @endif
          </div>
        </div>

        {{-- Activity Logs Card --}}
        <div class="info-card">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center">
              <i class="fas fa-history text-white"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Activity Logs</h3>
          </div>
          
          <div class="max-h-80 overflow-y-auto pr-2 space-y-4 custom-scrollbar">
            @forelse($dokumen->activityLogs->sortByDesc('created_at')->take(10) as $log)
              <div class="activity-log-item">
                <p class="text-sm text-slate-800 font-medium mb-1">{{ $log->description ?? 'Activity' }}</p>
                <p class="text-xs text-slate-400 flex items-center gap-2">
                  <i class="far fa-clock"></i>
                  {{ $log->created_at->diffForHumans() }}
                </p>
              </div>
            @empty
              <div class="text-center py-8 text-slate-400">
                <i class="fas fa-inbox text-3xl mb-2 opacity-30"></i>
                <p class="text-sm">Tidak ada activity log</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<script>
  // Smooth scroll untuk workflow stages pada mobile
  document.addEventListener('DOMContentLoaded', function() {
    const workflowContainer = document.querySelector('.overflow-x-auto');
    if (workflowContainer) {
      // Auto-scroll ke active stage pada load
      const activeCard = workflowContainer.querySelector('.workflow-stage-card.active');
      if (activeCard) {
        setTimeout(() => {
          activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }, 300);
      }
    }
  });
</script>

@endsection

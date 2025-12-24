@extends('layouts.app')

@section('content')
<style>
    /* Custom Scrollbar for inner areas */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div class="min-h-screen bg-slate-50 font-sans text-slate-800 pb-20">
    {{-- Top Progress & Header --}}
    <div class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        {{-- Global Progress Bar --}}
        @php
            $currentProgress = $dokumen->progress_percentage ?? 0;
            // Ensure numeric
            $progress = is_numeric($currentProgress) ? $currentProgress : 0;
        @endphp
        <div class="w-full h-1.5 bg-slate-100">
            <div class="h-full bg-emerald-500 transition-all duration-1000 ease-out" style="width: {{ $progress }}%"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-3">
                        <span class="bg-slate-900 text-white text-sm px-2 py-1 rounded">AGENDA #{{ $dokumen->nomor_agenda }}</span>
                        <span>Workflow Tracking</span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Memantau perjalanan dokumen <span class="font-medium text-slate-700">{{ $dokumen->nomor_spp }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                   <div class="text-right mr-4 hidden md:block">
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Estimasi Selesai</p>
                        <p class="text-sm font-medium text-slate-700">{{ $dokumen->deadline_at ? \Carbon\Carbon::parse($dokumen->deadline_at)->format('d M Y') : '-' }}</p>
                   </div>
                   <a href="{{ $dashboardUrl ?? '/owner/dashboard' }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 hover:text-emerald-600 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

        {{-- 1. Interactive Floating Path (Stepper) --}}
        <section class="relative">
            {{-- Horizontal Scroll Container for Mobile --}}
            <div class="overflow-x-auto pb-8 -mx-4 px-4 scrollbar-hide">
                <div class="flex items-center min-w-max space-x-4 p-2">
                    
                    @foreach($workflowStages as $index => $stage)
                        @php
                            $status = $stage['status'] ?? 'pending';
                            $isLast = $loop->last;
                            $isActive = $status === 'processing' || $status === 'active';
                            $isCompleted = $status === 'completed' || $status === 'selesai';
                            $isReturned = $status === 'returned';
                            
                            // Visual Classes Logic
                            $cardBaseClass = "relative w-72 flex-shrink-0 rounded-2xl p-5 border transition-all duration-300 ease-in-out group select-none";
                            
                            if ($isActive) {
                                // Active: Glassmorphism + Animated Border Effect (via shadow/border)
                                $cardVisualClass = "bg-white/90 backdrop-blur-xl border-emerald-500/50 ring-4 ring-emerald-500/10 shadow-[0_20px_40px_-15px_rgba(16,185,129,0.3)] translate-y-[-4px]";
                                $iconBgClass = "bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/40 animate-pulse";
                                $textTitleClass = "text-slate-900 font-bold";
                            } elseif ($isCompleted) {
                                // Completed: Clean Emerald
                                $cardVisualClass = "bg-emerald-50/50 border-emerald-200 hover:border-emerald-300 hover:shadow-lg shadow-sm hover:-translate-y-1";
                                $iconBgClass = "bg-emerald-100 text-emerald-600";
                                $textTitleClass = "text-slate-900 font-semibold";
                            } elseif ($isReturned) {
                                // Returned: Amber/Red Attention
                                $cardVisualClass = "bg-amber-50/50 border-amber-200 ring-2 ring-amber-100 hover:shadow-md";
                                $iconBgClass = "bg-amber-100 text-amber-600";
                                $textTitleClass = "text-amber-900 font-semibold";
                            } else {
                                // Pending: Muted
                                $cardVisualClass = "bg-slate-50 border-slate-200 opacity-60 hover:opacity-100 hover:bg-white transition-opacity";
                                $iconBgClass = "bg-slate-200 text-slate-400";
                                $textTitleClass = "text-slate-500 font-medium";
                            }
                        @endphp

                        {{-- Card --}}
                        <div class="{{ $cardBaseClass }} {{ $cardVisualClass }}">
                            
                            {{-- Active Badge --}}
                            @if($isActive)
                            <div class="absolute -top-3 right-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold leading-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white mr-1.5 animate-ping"></span>
                                    SEDANG DIPROSES
                                </span>
                            </div>
                            @endif

                            {{-- Return Badge --}}
                            @if($isReturned)
                            <div class="absolute -top-3 right-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold leading-4 bg-amber-500 text-white shadow-md">
                                    <i class="fas fa-undo-alt mr-1"></i> DIKEMBALIKAN
                                </span>
                            </div>
                            @endif

                            {{-- Card Header: Icon & Title --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl flex items-center justify-center {{ $iconBgClass }} transition-colors duration-300">
                                        @if($isCompleted)
                                            <i class="fas fa-check text-xl"></i>
                                        @else
                                            <i class="fas {{ $stage['icon'] ?? 'fa-circle' }} text-xl"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold tracking-wider text-slate-400 uppercase mb-0.5">{{ $stage['label'] }}</p>
                                        <h3 class="{{ $textTitleClass }} text-lg">{{ $stage['name'] }}</h3>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Content --}}
                            <div class="space-y-3">
                                <p class="text-sm {{ $isActive || $isCompleted ? 'text-slate-600' : 'text-slate-400' }}">
                                    {{ $stage['description'] }}
                                </p>
                                
                                @if(!empty($stage['timestamp']))
                                <div class="flex items-center gap-2 text-xs text-slate-400 border-t border-slate-100 pt-3 mt-3">
                                    <i class="far fa-clock"></i>
                                    <span>{{ \Carbon\Carbon::parse($stage['timestamp'])->format('d M Y, H:i') }}</span>
                                </div>
                                @endif
                                
                                {{-- Cycle/Return Info --}}
                                @if(($stage['hasCycle'] ?? false) || ($stage['hasReturn'] ?? false))
                                    <div class="mt-2 text-xs text-amber-600 bg-amber-50 rounded-lg p-2 border border-amber-100">
                                        @if($stage['hasCycle'] ?? false)
                                            <p><i class="fas fa-history mr-1"></i> Resubmission #{{ $stage['cycleInfo']['cycleCount'] ?? 1 }}</p>
                                        @endif
                                        @if($stage['hasReturn'] ?? false)
                                             <p class="mt-1 font-medium">{{ $stage['returnInfo']['alasan'] ?? 'No reason provided' }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Connector --}}
                        @if(!$isLast)
                            @php
                                // Connector Logic
                                // If current is completed, and next is active or completed -> Solid Color
                                // If current is completed, next is pending -> Gradient fade
                                // If current is active -> Animated Dashed
                                
                                $nextStage = $workflowStages[$index + 1];
                                $nextStatus = $nextStage['status'] ?? 'pending';
                                
                                $connectorClass = "h-1 w-12 rounded-full mx-2"; // Default length
                                
                                if ($isCompleted && ($nextStatus === 'completed' || $nextStatus === 'selesai')) {
                                    // Solid Green
                                    $connectorVisual = "bg-emerald-400";
                                } elseif ($isCompleted && ($nextStatus === 'processing' || $nextStatus === 'active')) {
                                    // Gradient Green to Pulse
                                    $connectorVisual = "bg-gradient-to-r from-emerald-400 to-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]";
                                } elseif ($isActive) {
                                    // Animated Dashed/Gradient
                                    $connectorVisual = "bg-gradient-to-r from-emerald-500 to-slate-200 animate-pulse";
                                } else {
                                    // Gray
                                    $connectorVisual = "bg-slate-200";
                                }
                            @endphp
                            <div class="{{ $connectorClass }}">
                                <div class="h-full w-full rounded-full {{ $connectorVisual }}"></div>
                            </div>
                        @endif

                    @endforeach
                    
                </div>
            </div>
        </section>


        {{-- 2. Bento Grid Layout for Information --}}
        <section>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                {{-- Main Info Card (Large) --}}
                <div class="lg:col-span-8 space-y-6">
                    
                    {{-- Financial Stats (Hero) --}}
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden group">
                        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-colors duration-500"></div>
                        
                        <div class="relative z-10">
                            <p class="text-slate-400 font-medium tracking-wide uppercase text-sm mb-2">Nilai Nominal</p>
                            <h2 class="text-4xl sm:text-5xl font-bold tracking-tight mb-6">
                                <span class="text-emerald-400">Rp</span> 
                                {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}
                            </h2>
                            
                            <div class="grid grid-cols-2 gap-8 border-t border-white/10 pt-6">
                                <div>
                                    <p class="text-slate-400 text-sm mb-1">Nomor SPP</p>
                                    <p class="font-mono text-lg font-medium tracking-wide">{{ $dokumen->nomor_spp }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 text-sm mb-1">Dibayar Kepada</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                        <p class="font-medium truncate">{{ is_object($dokumen->dibayarKepadas) ? $dokumen->dibayarKepadas->nama_penerima : ($dokumen->dibayar_kepada ?? '-') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Data Awal Grid --}}
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 flex items-center gap-2">
                            <i class="fas fa-file-alt text-emerald-500"></i> Informasi Dokumen
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                            <div class="group col-span-full">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1 group-hover:text-emerald-500 transition-colors">Uraian SPP</p>
                                <p class="text-slate-700 font-medium text-base">{{ $dokumen->uraian_spp ?? '-' }}</p>
                            </div>
                            
                            <div class="group">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1 group-hover:text-emerald-500 transition-colors">Nomor Agenda</p>
                                <p class="text-slate-700 font-medium text-base">{{ $dokumen->nomor_agenda ?? '-' }}</p>
                            </div>

                            <div class="group">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1 group-hover:text-emerald-500 transition-colors">Jenis Dokumen</p>
                                <p class="text-slate-700 font-medium text-base">{{ $dokumen->jenis_dokumen ?? '-' }}</p>
                            </div>

                            <div class="group">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1 group-hover:text-emerald-500 transition-colors">Kategori</p>
                                <p class="text-slate-700 font-medium text-base">{{ $dokumen->kategori ?? '-' }}</p>
                            </div>

                            <div class="group">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1 group-hover:text-emerald-500 transition-colors">Bagian Pengirim</p>
                                <p class="text-slate-700 font-medium text-base">{{ $dokumen->bagian ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Side Stats (Right Column) --}}
                <div class="lg:col-span-4 space-y-6">
                    
                    {{-- Tax Data --}}
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 h-full relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full blur-2xl -mr-10 -mt-10"></div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 relative z-10 flex items-center gap-2">
                            <i class="fas fa-calculator text-emerald-500"></i> Data Perpajakan
                        </h3>
                        
                        <div class="space-y-4 relative z-10">
                            @if($dokumen->npwp || $dokumen->no_faktur)
                                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">NPWP</p>
                                    <p class="font-mono text-slate-900 font-medium">{{ $dokumen->npwp ?? '-' }}</p>
                                </div>
                                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">No. Faktur</p>
                                    <p class="font-mono text-slate-900 font-medium">{{ $dokumen->no_faktur ?? '-' }}</p>
                                </div>
                                @if($dokumen->jenis_pph)
                                <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                                    <p class="text-xs text-emerald-600 uppercase font-bold mb-1">Jenis PPh</p>
                                    <p class="text-slate-900 font-medium">{{ $dokumen->jenis_pph }}</p>
                                </div>
                                @endif
                            @else
                                <div class="text-center py-8 text-slate-400">
                                    <i class="fas fa-search-dollar text-3xl mb-2 opacity-50"></i>
                                    <p class="text-sm">Belum ada data perpajakan</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                
                {{-- Full Width Sections --}}
                <div class="lg:col-span-12 grid grid-cols-1 md:grid-cols-2 gap-6">
                     {{-- Additional Accounting Data or Status Logs --}}
                     <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Activity Logs</h3>
                        <div class="max-h-60 overflow-y-auto pr-2 space-y-4 custom-scrollbar">
                           @foreach($dokumen->activityLogs->sortByDesc('created_at') as $log)
                           <div class="flex gap-3">
                               <div class="mt-1">
                                    <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                               </div>
                               <div>
                                   <p class="text-sm text-slate-800 font-medium">{{ $log->description }}</p>
                                   <p class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                               </div>
                           </div>
                           @endforeach
                        </div>
                     </div>
                </div>

            </div>
        </section>

    </div>
</div>
@endsection

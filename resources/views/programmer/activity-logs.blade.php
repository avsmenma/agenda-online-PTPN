@extends('layouts.programmer')

@section('title', 'Riwayat Aktivitas Dokumen')
@section('page_title', 'Riwayat Aktivitas')
@section('page_breadcrumb', 'Programmer → Riwayat Aktivitas')

@section('content')
<div class="container-fluid">
    <div class="prog-page-header">
        <div class="prog-page-header-left">
            <a href="{{ route('programmer.dashboard') }}" class="btn-back-prog mb-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <h2><i class="fas fa-history me-2 text-primary"></i>Riwayat Aktivitas Dokumen</h2>
            <p>Seluruh log aktivitas dokumen dari semua role</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('programmer.activity-logs') }}" id="filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><i class="fas fa-search me-1"></i>Cari Dokumen</label>
                        <input type="text" name="search" class="form-control" placeholder="Nomor agenda / SPP..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold"><i class="fas fa-building me-1"></i>Bagian</label>
                        <select name="bagian" class="form-select">
                            <option value="">Semua Bagian</option>
                            @foreach($bagianList as $b)
                                <option value="{{ $b }}" {{ request('bagian') == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold"><i class="fas fa-user me-1"></i>Dilakukan Oleh</label>
                        <select name="performed_by" class="form-select">
                            <option value="">Semua</option>
                            @foreach($performerList as $p)
                                <option value="{{ $p }}" {{ request('performed_by') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold"><i class="fas fa-calendar me-1"></i>Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary w-100" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="{{ route('programmer.activity-logs') }}" class="btn btn-outline-secondary" title="Reset">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="fas fa-list me-1 text-primary"></i>
                Total: {{ number_format($logs->total()) }} log
            </span>
            <span class="text-muted small">
                Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 130px;">Nomor Agenda</th>
                            <th style="width: 80px;">Bagian</th>
                            <th>Deskripsi Aktivitas</th>
                            <th style="width: 120px;">Dilakukan Oleh</th>
                            <th style="width: 80px;">Stage</th>
                            <th style="width: 150px;">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                            <tr>
                                <td class="text-muted">{{ $logs->firstItem() + $index }}</td>
                                <td>
                                    @if($log->dokumen)
                                        <span class="fw-semibold">{{ $log->dokumen->nomor_agenda ?? '-' }}</span>
                                    @else
                                        <span class="text-muted">ID: {{ $log->dokumen_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->dokumen && $log->dokumen->bagian)
                                        <span class="badge bg-secondary">{{ $log->dokumen->bagian }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 400px;" title="{{ $log->action_description }}">
                                        {{ $log->action_description ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $log->performed_by ?? 'System' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-outline-primary border text-dark" style="font-size: 11px;">
                                        {{ $log->stage ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        {{ $log->action_at ? $log->action_at->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        {{ $log->action_at ? $log->action_at->format('H:i:s') : '' }}
                                        @if($log->action_at)
                                            · {{ $log->action_at->locale('id')->diffForHumans() }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block" style="opacity: 0.3;"></i>
                                    <p class="text-muted mb-0">Tidak ada riwayat aktivitas ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-center">
                {{ $logs->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

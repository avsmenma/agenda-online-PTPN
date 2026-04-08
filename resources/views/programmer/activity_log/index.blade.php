@extends('layouts.app')

@section('title', 'Audit Trail - Aktivitas Programmer')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- === HEADER === --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1 fw-bold" style="color:#1e293b;">
                <i class="fas fa-shield-alt me-2 text-danger"></i>Audit Trail Programmer
            </h2>
            <p class="text-muted mb-0 small">
                Catatan semua tindakan sensitif yang dilakukan oleh akun programmer
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fs-6">
                <i class="fas fa-lock me-1"></i>Log bersifat permanen &amp; tidak dapat dihapus
            </span>
        </div>
    </div>

    {{-- === FILTER CARD === --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-header border-0 bg-white py-3" style="border-radius:14px 14px 0 0;">
            <h6 class="mb-0 fw-semibold text-secondary">
                <i class="fas fa-filter me-2"></i>Filter Log
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}" class="row g-3 align-items-end">
                {{-- Filter Action --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Jenis Aksi</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">-- Semua Aksi --</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>
                                {{ ucwords(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Programmer --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Programmer</label>
                    <select name="programmer_id" class="form-select form-select-sm">
                        <option value="">-- Semua Programmer --</option>
                        @foreach ($programmers as $prog)
                            <option value="{{ $prog->id }}" @selected(request('programmer_id') == $prog->id)>
                                {{ $prog->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tanggal Dari --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>

                {{-- Filter Tanggal Sampai --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>

                {{-- Buttons --}}
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- === STATS ROW === --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 h-100" style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:14px;">
                <div class="card-body text-white d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                        <i class="fas fa-list-alt fa-lg"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $logs->total() }}</div>
                        <div class="small opacity-75">Total Log</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 h-100" style="background:linear-gradient(135deg,#f093fb,#f5576c);border-radius:14px;">
                <div class="card-body text-white d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                        <i class="fas fa-trash-alt fa-lg"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">
                            {{ $logs->getCollection()->filter(fn($l) => str_contains($l->action, 'delete') || str_contains($l->action, 'cleanup'))->count() }}
                        </div>
                        <div class="small opacity-75">Aksi Hapus (halaman ini)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 h-100" style="background:linear-gradient(135deg,#4facfe,#00f2fe);border-radius:14px;">
                <div class="card-body text-white d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                        <i class="fas fa-users-cog fa-lg"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">
                            {{ $logs->getCollection()->filter(fn($l) => str_contains($l->action, 'user'))->count() }}
                        </div>
                        <div class="small opacity-75">Aksi User (halaman ini)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 h-100" style="background:linear-gradient(135deg,#43e97b,#38f9d7);border-radius:14px;">
                <div class="card-body text-white d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                        <i class="fas fa-fast-forward fa-lg"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">
                            {{ $logs->getCollection()->filter(fn($l) => str_contains($l->action, 'bulk'))->count() }}
                        </div>
                        <div class="small opacity-75">Operasi Bulk (halaman ini)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === TABLE CARD === --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header border-0 bg-white py-3 d-flex align-items-center justify-content-between" style="border-radius:14px 14px 0 0;">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-history me-2 text-primary"></i>
                Riwayat Aktivitas
                @if(request()->hasAny(['action','programmer_id','date_from','date_to']))
                    <span class="badge bg-primary ms-2">Filter Aktif</span>
                @endif
            </h6>
            <span class="text-muted small">
                {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} entri
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                <thead style="background-color:#f8fafc;">
                    <tr>
                        <th class="ps-4 py-3 text-muted fw-semibold" style="width:60px;">#</th>
                        <th class="py-3 text-muted fw-semibold" style="width:160px;">Waktu</th>
                        <th class="py-3 text-muted fw-semibold">Programmer</th>
                        <th class="py-3 text-muted fw-semibold">Aksi</th>
                        <th class="py-3 text-muted fw-semibold">Target / Deskripsi</th>
                        <th class="py-3 text-muted fw-semibold pe-4" style="width:140px;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            // Tentukan badge style berdasarkan action
                            $badgeStyle = match(true) {
                                str_contains($log->action, 'delete') || str_contains($log->action, 'cleanup')
                                    => 'background:#fee2e2;color:#b91c1c;',
                                str_contains($log->action, '2fa') || str_contains($log->action, 'password')
                                    => 'background:#fef9c3;color:#92400e;',
                                str_contains($log->action, 'bulk')
                                    => 'background:#ede9fe;color:#6d28d9;',
                                str_contains($log->action, 'create')
                                    => 'background:#dcfce7;color:#15803d;',
                                default
                                    => 'background:#dbeafe;color:#1d4ed8;',
                            };

                            $actionLabel = match($log->action) {
                                'user_create'       => 'Buat User',
                                'user_update'       => 'Ubah User',
                                'user_delete'       => 'Hapus User',
                                'reset_2fa'         => 'Reset 2FA',
                                'disable_2fa'       => 'Nonaktifkan 2FA',
                                'reset_password'    => 'Reset Password',
                                'bulk_to_payment'   => 'Bulk → Pembayaran',
                                'bulk_set_date'     => 'Bulk Set Tanggal',
                                'bulk_delete'       => 'Bulk Hapus',
                                'bulk_import'       => 'Bulk Import CSV',
                                'database_cleanup'  => 'Bersihkan Database',
                                'update_timestamps' => 'Ubah Timestamp',
                                default             => ucwords(str_replace('_', ' ', $log->action)),
                            };

                            $rowBg = (str_contains($log->action, 'delete') || str_contains($log->action, 'cleanup'))
                                ? 'background-color:#fff5f5;'
                                : '';
                        @endphp
                        <tr style="{{ $rowBg }}">
                            {{-- Nomor urut --}}
                            <td class="ps-4 text-muted">
                                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                            </td>

                            {{-- Waktu --}}
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $log->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    {{ $log->created_at->format('H:i:s') }}
                                </div>
                            </td>

                            {{-- Nama Programmer --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:32px;height:32px;background:linear-gradient(135deg,#667eea,#764ba2);font-size:0.75rem;flex-shrink:0;">
                                        {{ strtoupper(substr($log->programmer->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size:0.875rem;">
                                            {{ $log->programmer->name ?? '(Dihapus)' }}
                                        </div>
                                        <div class="text-muted" style="font-size:0.78rem;">
                                            @{{ $log->programmer->username ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Badge Aksi --}}
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold"
                                      style="{{ $badgeStyle }} font-size:0.78rem;">
                                    {{ $actionLabel }}
                                </span>
                            </td>

                            {{-- Target / Deskripsi --}}
                            <td style="max-width:350px;">
                                @if ($log->target_type && $log->target_id)
                                    <span class="badge bg-light text-secondary border me-1"
                                          style="font-size:0.75rem;">
                                        {{ $log->target_type }} #{{ $log->target_id }}
                                    </span>
                                @endif
                                @if ($log->target_description)
                                    <span class="text-dark" style="font-size:0.85rem;word-break:break-word;">
                                        {{ $log->target_description }}
                                    </span>
                                @elseif (!$log->target_type)
                                    <span class="text-muted fst-italic">—</span>
                                @endif
                            </td>

                            {{-- IP Address --}}
                            <td class="pe-4">
                                <code class="text-secondary" style="font-size:0.8rem;">
                                    {{ $log->ip_address }}
                                </code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                    <i class="fas fa-inbox fa-3x opacity-25"></i>
                                    <p class="mb-0">Belum ada log aktivitas yang tercatat.</p>
                                    @if(request()->hasAny(['action','programmer_id','date_from','date_to']))
                                        <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary mt-1">
                                            <i class="fas fa-times me-1"></i>Hapus Filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($logs->hasPages())
            <div class="card-footer border-0 bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-radius:0 0 14px 14px;">
                <span class="text-muted small">
                    Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}
                </span>
                <div>
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection

@push('styles')
<style>
    .table tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: background-color 0.15s ease;
    }
    .table thead th {
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
    }
    .card-header {
        border-bottom: 1px solid #f1f5f9 !important;
    }
</style>
@endpush

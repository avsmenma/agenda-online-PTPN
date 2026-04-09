@extends('layouts.app')

@section('title', 'Log Notifikasi')

@section('content')
  <div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h2 class="mb-1 fw-bold" style="color:#1e293b;">
          <i class="fas fa-bell me-2 text-primary"></i>Log Notifikasi
        </h2>
        <div class="text-muted small">Riwayat pengiriman notifikasi WhatsApp dan email fallback</div>
      </div>
      <div class="text-muted small">
        {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} entri
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3" style="border-radius:14px;">
      <div class="card-body">
        <form method="GET" action="{{ request()->url() }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small text-muted">Cari</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="phone / role / reason">
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Channel</label>
            <select name="channel" class="form-select form-select-sm">
              <option value="">Semua</option>
              <option value="whatsapp" @selected(request('channel') === 'whatsapp')>whatsapp</option>
              <option value="email" @selected(request('channel') === 'email')>email</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Status</label>
            <select name="status" class="form-select form-select-sm">
              <option value="">Semua</option>
              <option value="pending" @selected(request('status') === 'pending')>pending</option>
              <option value="success" @selected(request('status') === 'success')>success</option>
              <option value="failed" @selected(request('status') === 'failed')>failed</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Role</label>
            <input type="text" name="role_code" value="{{ request('role_code') }}" class="form-control form-control-sm" placeholder="perpajakan">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary btn-sm flex-fill" type="submit"><i class="fas fa-search me-1"></i>Filter</button>
            <a class="btn btn-outline-secondary btn-sm" href="{{ request()->url() }}"><i class="fas fa-times"></i></a>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:14px;">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
          <thead style="background-color:#f8fafc;">
            <tr>
              <th class="ps-4 py-3 text-muted fw-semibold" style="width:70px;">#</th>
              <th class="py-3 text-muted fw-semibold" style="width:170px;">Waktu</th>
              <th class="py-3 text-muted fw-semibold" style="width:120px;">Channel</th>
              <th class="py-3 text-muted fw-semibold" style="width:110px;">Status</th>
              <th class="py-3 text-muted fw-semibold" style="width:140px;">Role</th>
              <th class="py-3 text-muted fw-semibold">Dokumen</th>
              <th class="py-3 text-muted fw-semibold" style="width:160px;">User</th>
              <th class="py-3 text-muted fw-semibold" style="width:140px;">Phone</th>
              <th class="py-3 text-muted fw-semibold">Reason</th>
              <th class="py-3 text-muted fw-semibold pe-4" style="width:110px;">Type</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($logs as $log)
              @php
                $badgeLabel = $log->status === 'failed' ? 'failed' : ($log->channel === 'email' ? 'email' : 'whatsapp');
                $badgeClass = $log->status === 'failed'
                  ? 'bg-danger'
                  : ($log->channel === 'email' ? 'bg-warning text-dark' : 'bg-success');

                $statusClass = match($log->status) {
                  'success' => 'bg-success',
                  'failed' => 'bg-danger',
                  default => 'bg-secondary',
                };
              @endphp
              <tr>
                <td class="ps-4">{{ $log->id }}</td>
                <td>
                  <div class="fw-semibold">{{ optional($log->sent_at ?? $log->created_at)->format('d M Y') }}</div>
                  <div class="text-muted small">{{ optional($log->sent_at ?? $log->created_at)->format('H:i:s') }}</div>
                </td>
                <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                <td><span class="badge {{ $statusClass }}">{{ $log->status }}</span></td>
                <td><span class="text-muted">{{ $log->role_code }}</span></td>
                <td>
                  <div class="fw-semibold">{{ $log->dokumen->nomor_agenda ?? $log->dokumen_id }}</div>
                  <div class="text-muted small">{{ $log->dokumen->uraian_spp ?? '-' }}</div>
                </td>
                <td>{{ $log->user->name ?? '-' }}</td>
                <td><span class="text-muted">{{ $log->phone_number }}</span></td>
                <td style="max-width:320px;">
                  <div class="text-muted" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $log->fallback_reason }}">{{ $log->fallback_reason ?? '-' }}</div>
                </td>
                <td class="pe-4"><span class="badge bg-light text-dark border">{{ $log->message_type }}</span></td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center py-5">
                  <div class="text-muted">Belum ada log notifikasi.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-body pt-3">
        {{ $logs->links() }}
      </div>
    </div>
  </div>
@endsection


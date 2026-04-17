@extends('layouts/app')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">

<style>
/* ── CSS Variables ── */
*,*::before,*::after{box-sizing:border-box}
:root{
  --bg:#f5f6fa;
  --card:#fff;
  --border:#e9ecf3;
  --primary:#1a2340;
  --muted:#8492a6;
  --accent:#2563eb;
  --green:#16a34a;
  --green-bg:#f0fdf4;
  --yellow:#d97706;
  --yellow-bg:#fffbeb;
  --red:#dc2626;
  --red-bg:#fef2f2;
  --teal:#0d9488;
  --teal-bg:#f0fdfa;
  --r:12px;
  --sh:0 1px 3px rgba(0,0,0,.06),0 4px 12px rgba(0,0,0,.05);
}

.at-wrap{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:var(--bg);
  padding:24px 28px;
  min-height:100vh;
  font-size:13.5px;
  color:var(--primary);
}

/* ── PAGE HEADER ── */
.at-header{
  display:flex;align-items:flex-start;justify-content:space-between;
  margin-bottom:22px;flex-wrap:wrap;gap:12px;
}
.at-header-left{}
.at-page-title{
  font-family:'Sora',sans-serif;font-size:20px;font-weight:700;
  color:var(--primary);margin:0 0 4px;
}
.at-page-sub{font-size:12.5px;color:var(--muted);margin:0}
.at-permanent-badge{
  display:inline-flex;align-items:center;gap:6px;
  padding:7px 14px;border-radius:8px;
  background:var(--red-bg);border:1px solid #fecaca;
  color:var(--red);font-size:11.5px;font-weight:600;white-space:nowrap;
}
.at-permanent-badge svg{width:13px;height:13px;flex-shrink:0}

/* ── STATS CARDS ── */
.at-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
@media(max-width:1024px){.at-stats-row{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.at-stats-row{grid-template-columns:repeat(2,1fr)}}

.at-stat-card{
  border-radius:var(--r);padding:18px 20px;
  display:flex;align-items:center;gap:16px;
  box-shadow:var(--sh);animation:fadeUp .4s ease both;
}
.at-stat-card:nth-child(1){background:linear-gradient(135deg,#667eea,#764ba2);animation-delay:.05s}
.at-stat-card:nth-child(2){background:linear-gradient(135deg,#f093fb,#f5576c);animation-delay:.1s}
.at-stat-card:nth-child(3){background:linear-gradient(135deg,#4facfe,#00f2fe);animation-delay:.15s}
.at-stat-card:nth-child(4){background:linear-gradient(135deg,#43e97b,#38f9d7);animation-delay:.2s}

.at-stat-icon{
  width:44px;height:44px;border-radius:10px;
  background:rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.at-stat-icon svg{width:20px;height:20px;color:#fff}
.at-stat-val{font-family:'Sora',sans-serif;font-size:22px;font-weight:700;color:#fff;line-height:1}
.at-stat-lbl{font-size:11px;color:rgba(255,255,255,.8);margin-top:3px}

/* ── FILTER BAR ── */
.at-filter-card{
  background:#fff;border:1px solid var(--border);border-radius:var(--r);
  padding:16px 20px;margin-bottom:18px;box-shadow:var(--sh);animation:fadeUp .4s ease both;animation-delay:.25s;
}
.at-filter-title{
  font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;
  color:var(--muted);margin-bottom:12px;display:flex;align-items:center;gap:6px;
}
.at-filter-title svg{width:13px;height:13px}
.at-filter-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.at-filter-group{display:flex;flex-direction:column;gap:4px;flex:1;min-width:140px}
.at-filter-group label{font-size:11px;font-weight:600;color:var(--muted)}
.at-filter-group select,
.at-filter-group input{
  border:1px solid var(--border);border-radius:7px;padding:7px 10px;
  font-size:12px;font-family:inherit;color:var(--primary);background:#fff;
  outline:none;cursor:pointer;transition:.15s;
}
.at-filter-group select:focus,
.at-filter-group input:focus{border-color:var(--accent)}
.at-filter-btns{display:flex;gap:6px;align-items:flex-end;padding-bottom:1px}
.at-btn-filter{
  display:flex;align-items:center;gap:5px;padding:7px 16px;border-radius:8px;
  font-size:12px;font-weight:600;cursor:pointer;border:none;transition:.15s;
}
.at-btn-filter-primary{background:var(--accent);color:#fff}
.at-btn-filter-primary:hover{background:#1d4ed8}
.at-btn-filter-reset{background:var(--bg);color:var(--muted);border:1px solid var(--border)}
.at-btn-filter-reset:hover{border-color:var(--accent);color:var(--accent)}
.at-btn-filter svg{width:13px;height:13px}

/* ── TABLE WRAPPER ── */
.at-table-card{
  background:#fff;border:1px solid var(--border);border-radius:var(--r);
  box-shadow:var(--sh);overflow:hidden;animation:fadeUp .4s ease both;animation-delay:.3s;
}
.at-table-header{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 18px;border-bottom:1px solid var(--border);
}
.at-table-title{
  display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--primary);
}
.at-table-title svg{width:15px;height:15px;color:var(--accent)}
.at-filter-active-badge{
  font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;
  background:#eff4ff;color:var(--accent);
}
.at-table-count{font-size:12px;color:var(--muted)}

/* ── TABLE ── */
table.at-table{width:100%;border-collapse:collapse}
table.at-table thead th{
  font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;
  color:var(--muted);padding:11px 14px;text-align:left;background:#fafbfd;
  border-bottom:1px solid var(--border);white-space:nowrap;
}
table.at-table tbody tr{border-bottom:1px solid var(--border);transition:background .1s}
table.at-table tbody tr:last-child{border-bottom:none}
table.at-table tbody tr:hover{background:#fafbfd}
table.at-table tbody tr.row-danger{background:#fff8f8}
table.at-table tbody tr.row-danger:hover{background:#fff0f0}
table.at-table tbody td{padding:11px 14px;vertical-align:middle}

/* Programmer avatar cell */
.prog-avatar{
  width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,#667eea,#764ba2);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:12px;font-weight:700;flex-shrink:0;
}
.prog-name{font-size:12px;font-weight:600;color:var(--primary)}
.prog-username{font-size:11px;color:var(--muted)}

/* Action badge */
.action-badge{
  display:inline-flex;align-items:center;padding:4px 10px;border-radius:20px;
  font-size:11px;font-weight:700;white-space:nowrap;
}
.action-delete{background:var(--red-bg);color:var(--red)}
.action-2fa{background:var(--yellow-bg);color:var(--yellow)}
.action-bulk{background:#ede9fe;color:#6d28d9}
.action-create{background:var(--green-bg);color:var(--green)}
.action-default{background:#dbeafe;color:#1d4ed8}

/* Target description */
.target-type{
  font-size:10.5px;font-weight:600;padding:2px 7px;border-radius:5px;
  background:#f1f5f9;color:var(--muted);border:1px solid var(--border);
  margin-right:5px;
}
.target-desc{font-size:12px;color:var(--primary);word-break:break-word}

/* Time cell */
.time-date{font-size:12px;font-weight:600;color:var(--primary)}
.time-hour{font-size:11px;color:var(--muted)}

/* IP cell */
.ip-code{font-family:monospace;font-size:11.5px;color:var(--muted)}

/* ── PAGINATION ── */
.at-pagination{
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 18px;border-top:1px solid var(--border);background:#fafbfd;
}
.at-pag-info{font-size:12px;color:var(--muted)}
.pagination-links .pagination{margin:0;gap:4px;display:flex}
.pagination-links .page-item .page-link{
  width:30px;height:30px;border-radius:6px;border:1px solid var(--border);
  background:#fff;display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:500;color:var(--muted);cursor:pointer;
  transition:.15s;text-decoration:none;
}
.pagination-links .page-item.active .page-link,
.pagination-links .page-item .page-link:hover{
  background:var(--accent);color:#fff;border-color:var(--accent);
}
.pagination-links .page-item.disabled .page-link{opacity:.4;cursor:default}

/* ── EMPTY STATE ── */
.at-empty{text-align:center;padding:56px 24px;color:var(--muted)}
.at-empty svg{width:48px;height:48px;margin:0 auto 12px;display:block;opacity:.3}
.at-empty p{font-size:13px;margin:0}

@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
</style>

<div class="at-wrap">

  {{-- PAGE HEADER --}}
  <div class="at-header">
    <div class="at-header-left">
      <h1 class="at-page-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#2563eb"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Audit Trail
      </h1>
      <p class="at-page-sub">Catatan semua tindakan sensitif yang dilakukan oleh akun programmer</p>
    </div>
    <div class="at-permanent-badge">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      Log bersifat permanen &amp; tidak dapat dihapus
    </div>
  </div>

  {{-- STATS ROW --}}
  <div class="at-stats-row">
    <div class="at-stat-card">
      <div class="at-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <div>
        <div class="at-stat-val">{{ $logs->total() }}</div>
        <div class="at-stat-lbl">Total Log</div>
      </div>
    </div>
    <div class="at-stat-card">
      <div class="at-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
      </div>
      <div>
        <div class="at-stat-val">{{ $logs->getCollection()->filter(fn($l) => str_contains($l->action, 'delete') || str_contains($l->action, 'cleanup'))->count() }}</div>
        <div class="at-stat-lbl">Aksi Hapus (halaman ini)</div>
      </div>
    </div>
    <div class="at-stat-card">
      <div class="at-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
      </div>
      <div>
        <div class="at-stat-val">{{ $logs->getCollection()->filter(fn($l) => str_contains($l->action, 'user'))->count() }}</div>
        <div class="at-stat-lbl">Aksi User (halaman ini)</div>
      </div>
    </div>
    <div class="at-stat-card">
      <div class="at-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      </div>
      <div>
        <div class="at-stat-val">{{ $logs->getCollection()->filter(fn($l) => str_contains($l->action, 'bulk'))->count() }}</div>
        <div class="at-stat-lbl">Operasi Bulk (halaman ini)</div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="at-filter-card">
    <div class="at-filter-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      Filter Log
    </div>
    <form method="GET" action="{{ request()->url() }}">
      <div class="at-filter-row">
        <div class="at-filter-group">
          <label>Jenis Aksi</label>
          <select name="action">
            <option value="">-- Semua Aksi --</option>
            @foreach ($actions as $action)
              <option value="{{ $action }}" @selected(request('action') === $action)>
                {{ ucwords(str_replace('_', ' ', $action)) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="at-filter-group">
          <label>Programmer</label>
          <select name="programmer_id">
            <option value="">-- Semua Programmer --</option>
            @foreach ($programmers as $prog)
              <option value="{{ $prog->id }}" @selected(request('programmer_id') == $prog->id)>
                {{ $prog->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="at-filter-group" style="max-width:160px">
          <label>Dari Tanggal</label>
          <input type="date" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div class="at-filter-group" style="max-width:160px">
          <label>Sampai Tanggal</label>
          <input type="date" name="date_to" value="{{ request('date_to') }}">
        </div>
        <div class="at-filter-btns">
          <button type="submit" class="at-btn-filter at-btn-filter-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Filter
          </button>
          <a href="{{ request()->url() }}" class="at-btn-filter at-btn-filter-reset" style="text-decoration:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </a>
        </div>
      </div>
    </form>
  </div>

  {{-- TABLE CARD --}}
  <div class="at-table-card">
    <div class="at-table-header">
      <div class="at-table-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Riwayat Aktivitas
        @if(request()->hasAny(['action','programmer_id','date_from','date_to']))
          <span class="at-filter-active-badge">Filter Aktif</span>
        @endif
      </div>
      <div class="at-table-count">
        {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} entri
      </div>
    </div>

    <div style="overflow-x:auto">
      <table class="at-table">
        <thead>
          <tr>
            <th style="width:50px;padding-left:20px">#</th>
            <th style="width:130px">Waktu</th>
            <th>Programmer</th>
            <th>Aksi</th>
            <th>Target / Deskripsi</th>
            <th style="width:130px">IP Address</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($logs as $log)
            @php
              $isDanger = str_contains($log->action, 'delete') || str_contains($log->action, 'cleanup');
              $badgeClass = match(true) {
                $isDanger                                           => 'action-delete',
                str_contains($log->action, '2fa') || str_contains($log->action, 'password') => 'action-2fa',
                str_contains($log->action, 'bulk')                 => 'action-bulk',
                str_contains($log->action, 'create')               => 'action-create',
                default                                            => 'action-default',
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
            @endphp
            <tr class="{{ $isDanger ? 'row-danger' : '' }}">
              {{-- Nomor --}}
              <td style="padding-left:20px;color:var(--muted)">
                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
              </td>
              {{-- Waktu --}}
              <td>
                <div class="time-date">{{ $log->created_at->format('d/m/Y') }}</div>
                <div class="time-hour">{{ $log->created_at->format('H:i:s') }}</div>
              </td>
              {{-- Programmer --}}
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div class="prog-avatar">
                    {{ strtoupper(substr($log->programmer->name ?? '?', 0, 1)) }}
                  </div>
                  <div>
                    <div class="prog-name">{{ $log->programmer->name ?? '(Dihapus)' }}</div>
                    <div class="prog-username">@{{ $log->programmer->username ?? '-' }}</div>
                  </div>
                </div>
              </td>
              {{-- Aksi --}}
              <td>
                <span class="action-badge {{ $badgeClass }}">{{ $actionLabel }}</span>
              </td>
              {{-- Target --}}
              <td style="max-width:360px">
                @if ($log->target_type && $log->target_id)
                  <span class="target-type">{{ $log->target_type }} #{{ $log->target_id }}</span>
                @endif
                @if ($log->target_description)
                  <span class="target-desc">{{ $log->target_description }}</span>
                @elseif (!$log->target_type)
                  <span style="color:var(--muted)">—</span>
                @endif
              </td>
              {{-- IP --}}
              <td>
                <span class="ip-code">{{ $log->ip_address }}</span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="at-empty">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                  </svg>
                  <p>Belum ada log aktivitas yang tercatat.</p>
                  @if(request()->hasAny(['action','programmer_id','date_from','date_to']))
                    <a href="{{ request()->url() }}" class="at-btn-filter at-btn-filter-reset" style="text-decoration:none;display:inline-flex;margin-top:10px">
                      Hapus Filter
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
      <div class="at-pagination">
        <div class="at-pag-info">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</div>
        <div class="pagination-links">
          {{ $logs->links('pagination::bootstrap-5') }}
        </div>
      </div>
    @endif
  </div>

</div>

@endsection

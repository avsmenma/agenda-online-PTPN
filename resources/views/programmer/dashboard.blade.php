@extends('layouts.programmer')

@section('title', 'Programmer Dashboard')
@section('page_title', 'Dashboard')
@section('page_breadcrumb', 'Programmer → Dashboard')

@section('content')
<style>
  .stat-card {
    border-radius: 12px !important;
    border: 1px solid #e2e8f0 !important;
    transition: transform .2s, box-shadow .2s;
    overflow: hidden;
  }
  .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.08) !important; }

  .stat-val { font-size: 28px; font-weight: 700; margin: 0; }
  .stat-lbl { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; opacity: .65; margin-bottom: 6px; }

  .tool-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
    overflow: hidden;
  }
  .tool-list-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: #1e293b;
    transition: background .15s;
  }
  .tool-list-item:last-child { border-bottom: none; }
  .tool-list-item:hover { background: #f8fafc; }
  .tool-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
  }
  .tool-list-item h6 { margin: 0 0 2px; font-size: 14px; font-weight: 600; }
  .tool-list-item small { color: #64748b; font-size: 12px; }
  .tool-list-item .chevron { margin-left: auto; color: #cbd5e1; }

  .info-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
  .info-row:last-child { border-bottom: none; }
  .info-row label { width: 130px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; flex-shrink: 0; }
</style>

{{-- Welcome bar --}}
<div class="prog-welcome-bar">
  <i class="fas fa-code"></i>
  <div>
    <strong>Selamat datang, {{ Auth::user()->name ?? 'Programmer' }}!</strong><br>
    <small>Akses penuh ke semua operasi sistem — gunakan dengan bijak.</small>
  </div>
  <div class="ms-auto text-end">
    <div style="font-size:12px; opacity:.8;">{{ now()->isoFormat('dddd, D MMMM Y') }}</div>
    <div style="font-size:18px; font-weight:700;">{{ now()->format('H:i') }}</div>
  </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @php
    $statItems = [
      ['label' => 'Total Dokumen', 'val' => $stats['total_dokumens'],       'color' => '#6366f1', 'bg' => '#e0e7ff', 'icon' => 'fa-file-alt'],
      ['label' => 'Operator',      'val' => $stats['dokumens_operator'],     'color' => '#0ea5e9', 'bg' => '#e0f2fe', 'icon' => 'fa-user'],
      ['label' => 'Verifikasi',    'val' => $stats['dokumens_verifikasi'],   'color' => '#f59e0b', 'bg' => '#fef3c7', 'icon' => 'fa-check-double'],
      ['label' => 'Perpajakan',    'val' => $stats['dokumens_perpajakan'],   'color' => '#8b5cf6', 'bg' => '#ede9fe', 'icon' => 'fa-receipt'],
      ['label' => 'Akutansi',      'val' => $stats['dokumens_akutansi'],     'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-calculator'],
      ['label' => 'Pembayaran',    'val' => $stats['dokumens_pembayaran'],   'color' => '#ef4444', 'bg' => '#fee2e2', 'icon' => 'fa-money-bill'],
    ];
  @endphp
  @foreach($statItems as $s)
  <div class="col-xl-2 col-md-4 col-6">
    <div class="stat-card card p-3">
      <div class="d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;background:{{ $s['bg'] }};border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:18px;"></i>
        </div>
        <div>
          <div class="stat-lbl">{{ $s['label'] }}</div>
          <div class="stat-val" style="color:{{ $s['color'] }}">{{ number_format($s['val']) }}</div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Tools + Info --}}
<div class="row g-4">
  <div class="col-lg-7">
    <div class="tool-card">
      <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <h6 style="margin:0;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">
          <i class="fas fa-bolt me-2 text-warning"></i>Special Operations
        </h6>
      </div>

      @php
      $tools = [
        ['route'=>'programmer.bulk-to-payment.form',       'icon'=>'fa-fast-forward',  'color'=>'#f59e0b','bg'=>'#fef3c7','title'=>'Bulk Direct to Payment',   'desc'=>'Kirim dokumen langsung ke Pembayaran (skip workflow)'],
        ['route'=>'programmer.bulk-set-date-payment.form', 'icon'=>'fa-calendar-check','color'=>'#0ea5e9','bg'=>'#e0f2fe','title'=>'Bulk Set Date Payment',     'desc'=>'Set tanggal pembayaran massal untuk dokumen siap bayar'],
        ['route'=>'programmer.document-tools',             'icon'=>'fa-tools',          'color'=>'#6366f1','bg'=>'#e0e7ff','title'=>'Document Tools',            'desc'=>'Lihat ID dokumen dan edit timestamp role'],
        ['route'=>'programmer.user-management',            'icon'=>'fa-users-cog',      'color'=>'#10b981','bg'=>'#d1fae5','title'=>'User Management',           'desc'=>'Kelola user, username, password, dan role'],
        ['route'=>'programmer.activity-logs',              'icon'=>'fa-history',        'color'=>'#8b5cf6','bg'=>'#ede9fe','title'=>'Riwayat Aktivitas',         'desc'=>'Lihat seluruh log aktivitas dokumen per role'],
        ['route'=>'programmer.database-tools',             'icon'=>'fa-database',       'color'=>'#ef4444','bg'=>'#fee2e2','title'=>'Database Tools',            'desc'=>'Backup/export database SQL dan cleanup data dokumen'],
        ['route'=>'programmer.programmer-audit-trail',     'icon'=>'fa-shield-alt',     'color'=>'#7c3aed','bg'=>'#ede9fe','title'=>'Audit Trail Programmer',    'desc'=>'Lihat log aktivitas sensitif yang dilakukan programmer'],
        ['route'=>'programmer.2fa-reset-requests.index',   'icon'=>'fa-unlock-alt',     'color'=>'#ef4444','bg'=>'#fee2e2','title'=>'2FA Reset Requests',        'desc'=>'Kelola pengajuan reset 2FA dari user'],
      ];
      @endphp

      @foreach($tools as $tool)
      <a href="{{ route($tool['route']) }}" class="tool-list-item">
        <div class="tool-icon" style="background:{{ $tool['bg'] }};">
          <i class="fas {{ $tool['icon'] }}" style="color:{{ $tool['color'] }};"></i>
        </div>
        <div>
          <h6>{{ $tool['title'] }}</h6>
          <small>{{ $tool['desc'] }}</small>
        </div>
        <i class="fas fa-chevron-right chevron"></i>
      </a>
      @endforeach
    </div>
  </div>

  <div class="col-lg-5">
    <div class="tool-card h-100">
      <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <h6 style="margin:0;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">
          <i class="fas fa-info-circle me-2 text-info"></i>Informasi Session
        </h6>
      </div>
      <div style="padding:16px 18px;">
        <div class="info-row">
          <label>Login sebagai</label>
          <span style="font-weight:600;">{{ Auth::user()->name ?? 'Programmer' }}</span>
        </div>
        <div class="info-row">
          <label>Username</label>
          <code style="font-size:13px;">{{ Auth::user()->username ?? '-' }}</code>
        </div>
        <div class="info-row">
          <label>Role</label>
          <span class="badge" style="background:#e0e7ff;color:#6366f1;font-weight:700;font-size:12px;">{{ Auth::user()->role ?? 'programmer' }}</span>
        </div>
        <div class="info-row">
          <label>Waktu Server</label>
          <span>{{ now()->format('d M Y, H:i:s') }}</span>
        </div>
        <div class="info-row">
          <label>IP Address</label>
          <code style="font-size:13px;">{{ request()->ip() }}</code>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@php
  $summaryRouteName = $summaryRouteName ?? 'documents.verifikasi.index';
  $summaryRoleLabel = $summaryRoleLabel ?? 'Verifikasi';
  $summaryRoleSub = $summaryRoleSub ?? 'di verifikasi';
  $summaryRoleIcon = $summaryRoleIcon ?? 'fas fa-folder-open';
  $summarySentParams = $summarySentParams ?? ['status' => 'terkirim'];
  $summaryFilter = request('keterlambatan');
  $summaryDeadlineBaseQuery = request()->except('keterlambatan', 'page');
@endphp

@once
  <style>
    .vsummary-strip {
      display: grid;
      grid-template-columns: 1.25fr repeat(5, minmax(0, 1fr));
      gap: 0.65rem;
      margin-bottom: 0.75rem;
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .vsummary-item {
      min-height: 66px;
      background: #ffffff;
      border: 1px solid #e8eef4;
      border-radius: 12px;
      box-shadow: 0 5px 18px rgba(15, 23, 42, 0.06);
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 0.68rem;
      padding: 0.72rem 0.82rem;
      position: relative;
      overflow: hidden;
      text-decoration: none;
      transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    }

    .vsummary-item:hover {
      color: #0f172a;
      border-color: #cbd5e1;
      box-shadow: 0 9px 24px rgba(15, 23, 42, 0.1);
      transform: translateY(-1px);
    }

    .vsummary-item--total {
      background: linear-gradient(135deg, #083E40 0%, #0d5b59 100%);
      border-color: transparent;
      color: #ffffff;
      box-shadow: 0 8px 24px rgba(8, 62, 64, 0.2);
    }

    .vsummary-item--total:hover,
    .vsummary-item--total .vsummary-label,
    .vsummary-item--total .vsummary-value,
    .vsummary-item--total .vsummary-sub {
      color: #ffffff;
    }

    .vsummary-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      align-items: center;
      background: rgba(8, 62, 64, 0.09);
      color: #083E40;
      display: flex;
      flex: 0 0 auto;
      font-size: 0.9rem;
      justify-content: center;
    }

    .vsummary-item--total .vsummary-icon {
      background: rgba(255, 255, 255, 0.18);
      color: #ffffff;
    }

    .vsummary-item--safe { border-left: 4px solid #10b981; }
    .vsummary-item--warn { border-left: 4px solid #f59e0b; }
    .vsummary-item--late { border-left: 4px solid #f43f5e; }
    .vsummary-item--safe .vsummary-icon { background: rgba(16, 185, 129, 0.12); color: #059669; }
    .vsummary-item--warn .vsummary-icon { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .vsummary-item--late .vsummary-icon { background: rgba(244, 63, 94, 0.12); color: #e11d48; }

    .vsummary-item--active {
      background: rgba(8, 62, 64, 0.06);
      border-color: #0d5b59;
      box-shadow: 0 0 0 2px rgba(8, 62, 64, 0.12), 0 9px 24px rgba(15, 23, 42, 0.08);
    }

    .vsummary-item--safe.vsummary-item--active { background: rgba(16, 185, 129, 0.08); border-color: #10b981; }
    .vsummary-item--warn.vsummary-item--active { background: rgba(245, 158, 11, 0.09); border-color: #f59e0b; }
    .vsummary-item--late.vsummary-item--active { background: rgba(244, 63, 94, 0.08); border-color: #f43f5e; }

    .vsummary-meta {
      min-width: 0;
    }

    .vsummary-label {
      color: #94a3b8;
      font-size: 0.64rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      line-height: 1.2;
      margin-bottom: 0.12rem;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .vsummary-value {
      color: #0f172a;
      font-size: clamp(1.16rem, 1.03vw, 1.36rem);
      font-weight: 700;
      letter-spacing: 0;
      line-height: 1;
    }

    .vsummary-sub {
      color: #10b981;
      font-size: 0.64rem;
      font-weight: 600;
      line-height: 1.2;
      margin-top: 0.18rem;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .vsummary-item--warn .vsummary-sub { color: #d97706; }
    .vsummary-item--late .vsummary-sub { color: #e11d48; }

    @media (max-width: 1280px) {
      .vsummary-strip { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 768px) {
      .vsummary-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 520px) {
      .vsummary-strip { grid-template-columns: 1fr; }
      .vsummary-item { min-height: 62px; }
    }
  </style>
@endonce

<div class="vsummary-strip" aria-label="Ringkasan dokumen">
  <a href="{{ route($summaryRouteName) }}" class="vsummary-item vsummary-item--total">
    <div class="vsummary-icon"><i class="fas fa-layer-group"></i></div>
    <div class="vsummary-meta">
      <div class="vsummary-label">Total Dokumen</div>
      <div class="vsummary-value" data-counter="{{ $summaryTotalCount ?? 0 }}" data-duration="1400">0</div>
      <div class="vsummary-sub" data-counter-rupiah="{{ $summaryTotalValue ?? 0 }}" data-duration="1600">Rp 0</div>
    </div>
  </a>

  <a href="{{ route($summaryRouteName) }}" class="vsummary-item">
    <div class="vsummary-icon"><i class="{{ $summaryRoleIcon }}"></i></div>
    <div class="vsummary-meta">
      <div class="vsummary-label">{{ $summaryRoleLabel }}</div>
      <div class="vsummary-value" data-counter="{{ $summaryRoleCount ?? 0 }}" data-duration="1200">0</div>
      <div class="vsummary-sub">{{ $summaryRoleSub }}</div>
    </div>
  </a>

  <a href="{{ route($summaryRouteName, $summarySentParams) }}" class="vsummary-item">
    <div class="vsummary-icon"><i class="fas fa-paper-plane"></i></div>
    <div class="vsummary-meta">
      <div class="vsummary-label">Terkirim</div>
      <div class="vsummary-value" data-counter="{{ $summarySentCount ?? 0 }}" data-duration="1200">0</div>
      <div class="vsummary-sub">tahap lanjut</div>
    </div>
  </a>

  <a href="{{ route($summaryRouteName, array_merge($summaryDeadlineBaseQuery, $summaryFilter === 'aman' ? [] : ['keterlambatan' => 'aman'])) }}"
    class="vsummary-item vsummary-item--safe {{ $summaryFilter === 'aman' ? 'vsummary-item--active' : '' }}">
    <div class="vsummary-icon"><i class="fas fa-shield-alt"></i></div>
    <div class="vsummary-meta">
      <div class="vsummary-label">Aman</div>
      <div class="vsummary-value" data-counter="{{ $summarySafeCount ?? 0 }}" data-duration="1000">0</div>
      <div class="vsummary-sub">&lt; 24 jam</div>
    </div>
  </a>

  <a href="{{ route($summaryRouteName, array_merge($summaryDeadlineBaseQuery, $summaryFilter === 'peringatan' ? [] : ['keterlambatan' => 'peringatan'])) }}"
    class="vsummary-item vsummary-item--warn {{ $summaryFilter === 'peringatan' ? 'vsummary-item--active' : '' }}">
    <div class="vsummary-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="vsummary-meta">
      <div class="vsummary-label">Peringatan</div>
      <div class="vsummary-value" data-counter="{{ $summaryWarnCount ?? 0 }}" data-duration="1000">0</div>
      <div class="vsummary-sub">1-3 hari</div>
    </div>
  </a>

  <a href="{{ route($summaryRouteName, array_merge($summaryDeadlineBaseQuery, $summaryFilter === 'terlambat' ? [] : ['keterlambatan' => 'terlambat'])) }}"
    class="vsummary-item vsummary-item--late {{ $summaryFilter === 'terlambat' ? 'vsummary-item--active' : '' }}">
    <div class="vsummary-icon"><i class="fas fa-times-circle"></i></div>
    <div class="vsummary-meta">
      <div class="vsummary-label">Terlambat</div>
      <div class="vsummary-value" data-counter="{{ $summaryLateCount ?? 0 }}" data-duration="1000">0</div>
      <div class="vsummary-sub">&gt; 3 hari</div>
    </div>
  </a>
</div>

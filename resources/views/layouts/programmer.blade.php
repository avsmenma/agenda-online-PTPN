<!DOCTYPE html>
<html lang="id" id="html-root">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Programmer Panel') — PTPN Agenda</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    (function() {
      try {
        if (localStorage.getItem('sidebar_collapsed') === '1') {
          document.documentElement.classList.add('sidebar-collapsed');
        }
      } catch (error) {
        // Ignore storage failures; sidebar will use expanded mode.
      }
    })();
  </script>

  <style>
    /* ═══════════════════════════════════════════
       BASE
    ═══════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; }

    :root {
      --sidebar-w: 260px;
      --topbar-h: 60px;
      --accent: #6366f1;
      --accent-dark: #4f46e5;
      --accent-soft: #e0e7ff;
      --bg-main: #f1f5f9;
      --bg-card: #ffffff;
      --text-primary: #1e293b;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --sidebar-bg: #0f172a;
      --sidebar-hover: rgba(255,255,255,.07);
      --sidebar-active: rgba(99,102,241,.25);
      --sidebar-text: #94a3b8;
      --sidebar-text-active: #ffffff;
    }

    html, body { margin: 0; padding: 0; height: 100%; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-main);
      color: var(--text-primary);
    }

    /* ═══════════════════════════════════════════
       SIDEBAR
    ═══════════════════════════════════════════ */
    .prog-sidebar {
      position: fixed;
      inset: 0 auto 0 0;
      width: var(--sidebar-w);
      background: var(--sidebar-bg);
      display: flex;
      flex-direction: column;
      z-index: 100;
      overflow: hidden;
      transition: width .22s ease;
    }

    /* Logo / Brand */
    .prog-brand {
      height: var(--topbar-h);
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0 54px 0 20px;
      border-bottom: 1px solid rgba(255,255,255,.06);
      flex-shrink: 0;
      position: relative;
    }
    .prog-brand-icon {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, var(--accent) 0%, #818cf8 100%);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      color: #fff;
      flex-shrink: 0;
    }
    .prog-brand-text {
      display: flex;
      flex-direction: column;
    }
    .prog-brand-title {
      font-size: 14px;
      font-weight: 700;
      color: #f1f5f9;
      line-height: 1.2;
    }
    .prog-brand-sub {
      font-size: 11px;
      color: #475569;
      line-height: 1.2;
    }
    .prog-sidebar-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 32px;
      height: 32px;
      border: 1px solid rgba(148,163,184,.22);
      border-radius: 10px;
      background: rgba(255,255,255,.06);
      color: #cbd5e1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background .16s ease, border-color .16s ease, color .16s ease;
    }
    .prog-sidebar-toggle:hover {
      background: rgba(99,102,241,.22);
      border-color: rgba(129,140,248,.45);
      color: #fff;
    }
    .prog-sidebar-toggle:focus-visible {
      outline: 2px solid #818cf8;
      outline-offset: 2px;
    }
    .prog-sidebar-toggle i { font-size: 13px; }
    .prog-sidebar-toggle .prog-toggle-expand { display: none; }

    /* Nav items */
    .prog-nav {
      flex: 1;
      overflow-y: auto;
      padding: 12px 12px 0;
    }
    .prog-nav::-webkit-scrollbar { width: 4px; }
    .prog-nav::-webkit-scrollbar-track { background: transparent; }
    .prog-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

    .prog-nav-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #334155;
      padding: 14px 8px 6px;
    }

    .prog-nav a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      border-radius: 8px;
      color: var(--sidebar-text);
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 500;
      transition: background .15s, color .15s;
      margin-bottom: 2px;
    }
    .prog-nav a .nav-icon {
      width: 20px;
      text-align: center;
      font-size: 14px;
      color: #475569;
      flex-shrink: 0;
      transition: color .15s;
    }
    .prog-nav a:hover {
      background: var(--sidebar-hover);
      color: #f1f5f9;
    }
    .prog-nav a:hover .nav-icon { color: #f1f5f9; }
    .prog-nav a.active {
      background: var(--sidebar-active);
      color: var(--sidebar-text-active);
      font-weight: 600;
    }
    .prog-nav a.active .nav-icon { color: var(--accent); }

    /* danger item */
    .prog-nav a.danger .nav-icon { color: #f87171; }
    .prog-nav a.danger:hover { background: rgba(239,68,68,.12); color: #fca5a5; }
    .prog-nav a.danger:hover .nav-icon { color: #fca5a5; }

    /* Sidebar footer */
    .prog-sidebar-footer {
      padding: 12px;
      border-top: 1px solid rgba(255,255,255,.06);
    }
    .prog-user-tile {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 8px;
      background: rgba(255,255,255,.05);
      color: inherit;
      text-decoration: none;
      transition: background .15s;
    }
    .prog-user-tile:hover {
      background: rgba(99,102,241,.18);
      color: inherit;
    }
    .prog-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent) 0%, #818cf8 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
      overflow: hidden;
    }
    .prog-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .prog-user-name {
      font-size: 13px;
      font-weight: 600;
      color: #e2e8f0;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .prog-user-role {
      font-size: 11px;
      color: #475569;
    }

    .prog-logout {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      border-radius: 8px;
      color: #94a3b8;
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 500;
      margin-top: 4px;
      transition: background .15s, color .15s;
    }
    .prog-logout:hover {
      background: rgba(239,68,68,.12);
      color: #fca5a5;
    }
    .prog-logout .nav-icon { color: #475569; transition: color .15s; }
    .prog-logout:hover .nav-icon { color: #fca5a5; }

    /* ═══════════════════════════════════════════
       TOPBAR
    ═══════════════════════════════════════════ */
    .prog-topbar {
      position: fixed;
      top: 0;
      left: var(--sidebar-w);
      right: 0;
      height: var(--topbar-h);
      background: #fff;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      z-index: 50;
      transition: left .22s ease;
    }
    .prog-topbar-left { display: flex; align-items: center; gap: 12px; }
    .prog-topbar-right { display: flex; align-items: center; gap: 6px; }

    .prog-page-title {
      font-size: 15px;
      font-weight: 600;
      color: var(--text-primary);
    }
    .prog-page-breadcrumb {
      font-size: 12px;
      color: var(--text-muted);
    }
    .prog-topbar-badge {
      background: var(--accent-soft);
      color: var(--accent);
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
      letter-spacing: .3px;
    }

    /* ═══════════════════════════════════════════
       MAIN CONTENT
    ═══════════════════════════════════════════ */
    .prog-content {
      margin-left: var(--sidebar-w);
      padding-top: var(--topbar-h);
      min-height: 100vh;
      transition: margin-left .22s ease;
    }
    .prog-content-inner {
      padding: 28px 28px;
    }

    /* ═══════════════════════════════════════════
       BACK BUTTON — global style
    ═══════════════════════════════════════════ */
    .btn-back-prog {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 7px 16px;
      border-radius: 8px;
      background: #f1f5f9;
      color: var(--text-muted);
      font-size: 13px;
      font-weight: 600;
      border: 1px solid var(--border);
      text-decoration: none;
      transition: background .15s, color .15s, border-color .15s;
    }
    .btn-back-prog:hover {
      background: var(--accent-soft);
      color: var(--accent);
      border-color: var(--accent);
    }

    /* ═══════════════════════════════════════════
       PAGE HEADER SECTION
    ═══════════════════════════════════════════ */
    .prog-page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .prog-page-header-left h2 {
      font-size: 22px;
      font-weight: 700;
      margin: 0 0 4px;
      color: var(--text-primary);
    }
    .prog-page-header-left p {
      font-size: 13px;
      color: var(--text-muted);
      margin: 0;
    }

    /* ═══════════════════════════════════════════
       CARD OVERRIDES
    ═══════════════════════════════════════════ */
    .card {
      border-radius: 12px !important;
      border: 1px solid var(--border) !important;
      box-shadow: 0 1px 4px rgba(0,0,0,.05) !important;
    }

    /* Welcome banner */
    .prog-welcome-bar {
      background: linear-gradient(135deg, var(--accent) 0%, #818cf8 100%);
      border-radius: 12px;
      padding: 16px 20px;
      color: white;
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 24px;
    }
    .prog-welcome-bar i { font-size: 22px; opacity: .8; }
    .prog-welcome-bar strong { font-size: 15px; }
    .prog-welcome-bar small { opacity: .8; font-size: 12px; }

    .sidebar-collapsed .prog-sidebar {
      width: 84px;
    }
    .sidebar-collapsed .prog-brand {
      padding: 0 8px 0 10px;
      gap: 0;
    }
    .sidebar-collapsed .prog-brand-icon {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      font-size: 14px;
    }
    .sidebar-collapsed .prog-brand-text,
    .sidebar-collapsed .prog-nav-label,
    .sidebar-collapsed .prog-user-name,
    .sidebar-collapsed .prog-user-role {
      display: none;
    }
    .sidebar-collapsed .prog-sidebar-toggle {
      right: 7px;
      width: 30px;
      height: 30px;
    }
    .sidebar-collapsed .prog-sidebar-toggle .prog-toggle-collapse { display: none; }
    .sidebar-collapsed .prog-sidebar-toggle .prog-toggle-expand { display: inline-block; }
    .sidebar-collapsed .prog-nav {
      padding: 12px 8px 0;
    }
    .sidebar-collapsed .prog-nav a {
      justify-content: center;
      gap: 0;
      padding: 12px 10px;
      font-size: 0;
    }
    .sidebar-collapsed .prog-nav a .nav-icon {
      width: 20px;
      font-size: 15px;
    }
    .sidebar-collapsed .prog-sidebar-footer {
      padding: 10px 8px;
    }
    .sidebar-collapsed .prog-user-tile {
      justify-content: center;
      width: 44px;
      height: 44px;
      padding: 0;
      margin: 0 auto 8px;
    }
    .sidebar-collapsed .prog-logout {
      justify-content: center;
      width: 44px;
      height: 40px;
      padding: 0;
      margin: 4px auto 0;
      font-size: 0;
    }
    .sidebar-collapsed .prog-topbar {
      left: 84px;
    }
    .sidebar-collapsed .prog-content {
      margin-left: 84px;
    }
  </style>

  @yield('styles')
</head>
<body>

{{-- ═══════════════════════ SIDEBAR ═══════════════════════ --}}
<aside class="prog-sidebar">

  {{-- Brand --}}
  <div class="prog-brand">
    <div class="prog-brand-icon"><i class="fas fa-code"></i></div>
    <div class="prog-brand-text">
      <span class="prog-brand-title">Programmer Panel</span>
      <span class="prog-brand-sub">PTPN Agenda Online</span>
    </div>
    <button type="button" class="prog-sidebar-toggle" data-sidebar-toggle aria-label="Kecilkan sidebar" title="Kecilkan sidebar">
      <i class="fas fa-angles-left prog-toggle-collapse" aria-hidden="true"></i>
      <i class="fas fa-angles-right prog-toggle-expand" aria-hidden="true"></i>
    </button>
  </div>

  {{-- Nav --}}
  <nav class="prog-nav">

    <div class="prog-nav-label">Overview</div>
    <a href="{{ route('programmer.dashboard') }}"
       class="{{ request()->routeIs('programmer.dashboard') ? 'active' : '' }}" title="Dashboard">
      <i class="fas fa-tachometer-alt nav-icon"></i> Dashboard
    </a>

    <div class="prog-nav-label">Dokumen</div>
    <a href="{{ route('programmer.bulk-to-payment.form') }}"
       class="{{ request()->routeIs('programmer.bulk-to-payment*') ? 'active' : '' }}" title="Bulk to Payment">
      <i class="fas fa-fast-forward nav-icon"></i> Bulk to Payment
    </a>
    <a href="{{ route('programmer.bulk-send-to-role.form') }}"
       class="{{ request()->routeIs('programmer.bulk-send-to-role*') ? 'active' : '' }}" title="Bulk Send to Role">
      <i class="fas fa-share-square nav-icon"></i> Bulk Send to Role
    </a>
    <a href="{{ route('programmer.bulk-set-date-payment.form') }}"
       class="{{ request()->routeIs('programmer.bulk-set-date-payment*') ? 'active' : '' }}" title="Bulk Set Date Payment">
      <i class="fas fa-calendar-check nav-icon"></i> Bulk Set Date Payment
    </a>
    <a href="{{ route('programmer.document-tools') }}"
       class="{{ request()->routeIs('programmer.document-tools*') ? 'active' : '' }}" title="Document Tools">
      <i class="fas fa-tools nav-icon"></i> Document Tools
    </a>
    <a href="{{ route('programmer.activity-logs') }}"
       class="{{ request()->routeIs('programmer.activity-logs') ? 'active' : '' }}" title="Riwayat Aktivitas">
      <i class="fas fa-history nav-icon"></i> Riwayat Aktivitas
    </a>
    <a href="{{ route('programmer.notification-logs') }}"
       class="{{ request()->routeIs('programmer.notification-logs') ? 'active' : '' }}" title="Log Notifikasi">
      <i class="fas fa-bell nav-icon"></i> Log Notifikasi
    </a>

    <div class="prog-nav-label">Users & Keamanan</div>
    <a href="{{ route('programmer.user-management') }}"
       class="{{ request()->routeIs('programmer.user-management*') ? 'active' : '' }}" title="User Management">
      <i class="fas fa-users-cog nav-icon"></i> User Management
    </a>
    <a href="{{ route('programmer.2fa-reset-requests.index') }}"
       class="{{ request()->routeIs('programmer.2fa-reset-requests*') ? 'active' : '' }}" title="2FA Reset Requests">
      <i class="fas fa-unlock-alt nav-icon"></i> 2FA Reset Requests
    </a>
    <a href="{{ route('profile.account') }}"
       class="{{ request()->routeIs('profile.account') ? 'active' : '' }}" title="Profil Saya">
      <i class="fas fa-user-circle nav-icon"></i> Profil Saya
    </a>

    <div class="prog-nav-label">Sistem</div>
    <a href="{{ route('programmer.database-tools') }}"
       class="{{ request()->routeIs('programmer.database-tools*') ? 'active' : '' }}" title="Database Tools">
      <i class="fas fa-database nav-icon"></i> Database Tools
    </a>
    <a href="{{ route('programmer.programmer-audit-trail') }}"
       class="{{ request()->routeIs('programmer.programmer-audit-trail') ? 'active' : '' }}" title="Audit Trail">
      <i class="fas fa-shield-alt nav-icon"></i> Audit Trail
    </a>

  </nav>

  {{-- Footer --}}
  <div class="prog-sidebar-footer">
    @php
      $programmerUser = Auth::user();
      $programmerPhotoUrl = $programmerUser?->profile_photo_url;
    @endphp
    <a class="prog-user-tile" href="{{ route('profile.account') }}" title="Buka profil saya">
      <div class="prog-avatar">
        @if($programmerPhotoUrl)
          <img src="{{ $programmerPhotoUrl }}" alt="Foto profil {{ $programmerUser->name ?? 'Programmer' }}">
        @else
          {{ strtoupper(substr($programmerUser->name ?? 'P', 0, 1)) }}
        @endif
      </div>
      <div style="overflow:hidden;">
        <div class="prog-user-name">{{ $programmerUser->name ?? 'Programmer' }}</div>
        <div class="prog-user-role">{{ $programmerUser->role ?? 'programmer' }}</div>
      </div>
    </a>
    <a href="{{ route('logout.get') }}" class="prog-logout" title="Keluar">
      <i class="fas fa-sign-out-alt nav-icon"></i> Keluar
    </a>
  </div>

</aside>

{{-- ═══════════════════════ TOPBAR ═══════════════════════ --}}
<header class="prog-topbar">
  <div class="prog-topbar-left">
    <div>
      <div class="prog-page-title">@yield('page_title', 'Programmer Panel')</div>
      <div class="prog-page-breadcrumb">@yield('page_breadcrumb', 'PTPN Agenda Online')</div>
    </div>
  </div>
  <div class="prog-topbar-right">
    <span class="prog-topbar-badge"><i class="fas fa-code me-1"></i>PROGRAMMER</span>
  </div>
</header>

{{-- ═══════════════════════ CONTENT ═══════════════════════ --}}
<main class="prog-content">
  <div class="prog-content-inner">
    @yield('content')
  </div>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sidebarStorageKey = 'sidebar_collapsed';
    const sidebarToggleButtons = document.querySelectorAll('[data-sidebar-toggle]');

    function syncSidebarToggleState() {
      const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');

      sidebarToggleButtons.forEach(function(button) {
        button.setAttribute('aria-label', isCollapsed ? 'Tampilkan sidebar penuh' : 'Kecilkan sidebar');
        button.setAttribute('title', isCollapsed ? 'Tampilkan sidebar penuh' : 'Kecilkan sidebar');
        button.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
      });
    }

    sidebarToggleButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        const shouldCollapse = !document.documentElement.classList.contains('sidebar-collapsed');
        document.documentElement.classList.toggle('sidebar-collapsed', shouldCollapse);

        try {
          localStorage.setItem(sidebarStorageKey, shouldCollapse ? '1' : '0');
        } catch (error) {
          // Sidebar tetap berjalan meski browser menolak localStorage.
        }

        syncSidebarToggleState();
      });
    });

    syncSidebarToggleState();
  });
</script>
@yield('scripts')

</body>
</html>

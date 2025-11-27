<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'PTPN Agenda Online' }}</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fc;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      /* background: linear-gradient(180deg, #1a4d3e 0%, #0d2621 100%); */
      background: white;
      color: #01545A;
      font-weight: 600;
      padding-top: 20px;
      display: flex;
      flex-direction: column;
    }

    .sidebar a {
      color: #666666;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
      border-radius: 20px 0 0 20px;
      margin-left: 30px ;
      margin-top:20px;
      transition: all 0.3s;
    }
    
    .sidebar a .badge.right {
      margin-left: 10px;
      padding: 2px 10px;
      border-radius: 999px;
      background: #083E40;
      color: #ffffff;
      font-weight: 600;
      font-size: 12px;
      border: none;
    }

    .sidebar a:hover .badge.right,
    .sidebar a.active .badge.right {
      background: #ffffff;
      color: #083E40;
    }

    .sidebar a:hover, .sidebar a.active {
      background-color: #083E40;
      color: #FFFFFF;
    }

    /* .sidebar .dropdown-menu-custom {
      margin-left: 30px;
      margin-top: 20px;
    } */

    .sidebar .dropdown-toggle {
      color: #666666;
      text-decoration: none;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 20px;
      border-radius: 20px 0 0 20px;
      margin-left: 30px;
      margin-top: 20px;
      cursor: pointer;

      transition: all 0.3s;
    }

    .sidebar .dropdown-toggle:hover {
      background-color: #083E40;
      color: #FFFFFF;
    }

    .sidebar .dropdown-toggle.active {
      background-color: #083E40;
      color: #FFFFFF;
    }

    .sidebar .dropdown-content {
      display: none;
      margin-left: 20px;
      margin-top: 10px;
    }

    .sidebar .dropdown-content.show {
      display: block;
    }

    .sidebar .dropdown-content a {
      margin-left: 20px;
      margin-top: 5px;
      padding: 10px 20px;
      font-size: 14px;
      border-radius: 20px 0 0 20px;
    }

    .sidebar .dropdown-icon {
      transition: transform 0.3s;
    }

    .sidebar .dropdown-icon.rotate {
      transform: rotate(180deg);
    }

    .sidebar hr.sidebar-divider {
  margin: 0 1rem 1rem;
}

    .welcome-message {
      color: #01545A;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .welcome-message::before {
      content: "👋";
      font-size: 1.2em;
    }

    .content {
      margin-left: 250px;
      padding: 20px;
    }

    .topbar {
      background-color: white;
      /* border-radius: 8px; */
      padding: 25px 40px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 5px;
      margin-left: 16%;
      padding-left: 30px;
      /* width: 100%; */
    }

    .card-stat {
      border-radius: 12px;
      padding: 20px;
      color: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }

    .card-stat:hover {
      transform: translateY(-5px);
    }

    .card-stat h6 {
      font-size: 14px;
      margin-bottom: 10px;
      opacity: 0.9;
    }

    .card-stat h3 {
      font-size: 36px;
      font-weight: bold;
      margin: 0;
    }

    .card-dark-green {
      background-color: #1a4d3e;
    }

    .card-lime-green {
      background-color: #8fa924;
    }

    .card-teal {
      background-color: #0d5449;
    }

    .card-orange {
      background-color: #d97706;
    }

    .search-box {
      display: flex;
      background-color: white;
      border-radius: 8px;
      padding: 15px;
      margin:10px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .search-box .input-group-text {
      background-color: white;
      border: 1px solid #e0e0e0;
      border-right: none;
      border-radius: 6px 0 0 6px;
    }

    .search-box input {
      border: 1px solid #e0e0e0;
      border-left: none;
      border-radius: 0 6px 6px 0;
      padding: 10px 15px;
    }

    .search-box input:focus {
      outline: none;
      box-shadow: none;
      border-color: #e0e0e0;
    }

    .table-container {
      background-color: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .table-container h6 {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      font-weight: 600;
    }

    .table thead {
      background-color: #1a4d3e;
      color: white;
    }

    .table thead th {
      border: none;
      padding: 12px;
      font-weight: 500;
      font-size: 14px;
    }

    .table tbody tr {
      border-bottom: 1px solid #f0f0f0;
    }

    .table tbody tr:hover {
      background-color: #f8f9fa;
    }

    .table tbody td {
      padding: 12px;
      vertical-align: middle;
      font-size: 14px;
    }

    .badge-success {
      background-color: #10b981;
      padding: 5px 12px;
      border-radius: 6px;
    }

    .badge-warning {
      background-color: #f59e0b;
      padding: 5px 12px;
      border-radius: 6px;
      color: white;
    }

    .btn-view {
      background-color: #8fa924;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn-view:hover {
      background-color: #7a8d1f;
    }

    .highlight-row {
      background-color: #c4d82f !important;
    }

    footer {
      text-align: center;
      padding: 10px;
      color: #888;
      margin-top: 30px;
    }

    /* Notification System Styles */
    #notification-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      max-width: 400px;
    }

    #globalNotificationContainer {
      position: fixed;
      top: 20px;
      right: 420px;
      z-index: 9999;
      max-width: 400px;
    }

    .notification-toast {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      padding: 16px 20px;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.3);
      margin-bottom: 12px;
      animation: slideInRight 0.3s ease;
      cursor: pointer;
      transition: transform 0.2s ease;
      position: relative;
      overflow: hidden;
    }

    .notification-toast:hover {
      transform: translateX(-5px);
    }

    .notification-toast::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background: #889717;
    }

    .notification-toast .notification-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .notification-toast .notification-title {
      font-weight: 600;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .notification-toast .notification-close {
      background: none;
      border: none;
      color: white;
      font-size: 18px;
      cursor: pointer;
      opacity: 0.7;
      transition: opacity 0.2s;
      padding: 0;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .notification-toast .notification-close:hover {
      opacity: 1;
    }

    .notification-toast .notification-body {
      font-size: 13px;
      opacity: 0.95;
      line-height: 1.5;
    }

    .notification-toast .notification-footer {
      margin-top: 10px;
      display: flex;
      gap: 8px;
      justify-content: flex-end;
    }

    .notification-toast .btn-refresh {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      padding: 4px 12px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .notification-toast .btn-refresh:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    /* Notification styles for returned documents */
    .notification-returned {
      border-left: 4px solid #dc3545 !important;
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    }

    .notification-returned .notification-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification-returned .alasan-text {
      color: #ffcccc;
      font-style: italic;
      font-size: 13px;
      line-height: 1.4;
      display: block;
      margin-top: 4px;
      padding: 4px 8px;
      background: rgba(0, 0, 0, 0.2);
      border-radius: 4px;
      max-height: 60px;
      overflow-y: auto;
    }

    /* Notification styles for perpajakan documents */
    .notification-perpajakan {
      border-left: 4px solid #17a2b8 !important;
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    }

    .notification-perpajakan .notification-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification-header-perpajakan {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    }

    /* Notification styles for akutansi documents */
    .notification-akutansi {
      border-left: 4px solid #889717 !important;
      background: linear-gradient(135deg, #889717 0%, #9ab01f 100%) !important;
    }

    .notification-akutansi .notification-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification-header-akutansi {
      background: linear-gradient(135deg, #889717 0%, #9ab01f 100%) !important;
    }

    /* Notification styles for pembayaran documents */
    .notification-pembayaran {
      border-left: 4px solid #083E40 !important;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%) !important;
    }

    .notification-pembayaran .notification-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification-header-pembayaran {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%) !important;
    }

    /* Notification styles for new documents */
    .notification-new {
      border-left: 4px solid #28a745 !important;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }

    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @keyframes slideOutRight {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }

    .notification-toast.hiding {
      animation: slideOutRight 0.3s ease forwards;
    }

    /* Sidebar Badge Styles */
    .menu-notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #dc3545;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      font-size: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      animation: pulse 2s infinite;
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
    }

    .menu-item-wrapper {
      position: relative;
    }

    /* Universal Notification Badge */
    .notification-badge {
      background: #dc3545;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      font-size: 11px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      margin-left: 8px;
      animation: pulse 2s infinite;
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        opacity: 1;
      }
      50% {
        transform: scale(1.1);
        opacity: 0.9;
      }
    }

    .menu-highlight {
      animation: highlightPulse 1.5s ease-in-out;
    }

    .menu-highlight.returned {
      animation: highlightReturnedPulse 1.5s ease-in-out;
    }

    @keyframes highlightPulse {
      0%, 100% {
        background-color: transparent;
      }
      50% {
        background-color: rgba(8, 62, 64, 0.1);
      }
    }

    @keyframes highlightReturnedPulse {
      0%, 100% {
        background-color: transparent;
      }
      50% {
        background-color: rgba(220, 53, 69, 0.1);
      }
    }
  </style>

  <!-- Smart Autocomplete CSS -->
  <link href="{{ asset('css/smart-autocomplete.css') }}" rel="stylesheet">
  
  <!-- Stack for additional styles from views -->
  @stack('styles')
</head>
<body>
  <header>
       <div class="topbar mb-0 mt-0">
        <h5 class="mb-0 welcome-message">{{ $welcomeMessage ?? 'Selamat datang di Agenda Online PTPN' }}</h5>
        <div class="d-flex align-items-center">
          <i class="fa-solid fa-bell me-3" style="font-size: 20px; color: #666;"></i>
          <i class="fa-solid fa-user" style="font-size: 18px; color: #666;"></i>
      </div>
  </header>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center mb-4"><i class="fa-solid fa-calendar-days"></i> Agenda Online</h4>
    <hr>

        @php
            // Check if user is owner
            $isOwner = auth()->check() && (auth()->user()->role === 'owner' || auth()->user()->role === 'Owner' || auth()->user()->role === 'OWNER' || auth()->user()->role === 'Admin' || auth()->user()->role === 'admin');
            
            // Normalize module to lowercase untuk konsistensi
            $module = strtolower($module ?? 'ibua');
            
            $dashboardUrl = match($module) {
                'ibua', 'ibua' => '/dashboard',
                'ibub', 'ibub' => '/dashboardB',
                'pembayaran' => '/dashboardPembayaran',
                'akutansi' => '/dashboardAkutansi',
                'perpajakan' => '/dashboardPerpajakan',
                default => '/dashboard'
            };
            $dokumenUrl = match($module) {
                'ibua', 'ibua' => '/dokumens',
                'ibub', 'ibub' => '/dokumensB',
                'pembayaran' => '/dokumensPembayaran',
                'akutansi' => '/dokumensAkutansi',
                'perpajakan' => '/dokumensPerpajakan',
                default => '/dokumens'
            };
            $pengembalianUrl = match($module) {
                'ibub', 'ibub' => '/pengembalian-dokumensB',
                'pembayaran' => '/rekapan-keterlambatan',
                'akutansi' => '/pengembalian-dokumensAkutansi',
                'perpajakan' => '/pengembalian-dokumensPerpajakan',
                default => '/pengembalian-dokumens'
            };
            $tambahDokumenUrl = match($module) {
                'ibua', 'ibua' => '/dokumens/create',
                default => null
            };
            $editDokumenUrl = match($module) {
                'pembayaran' => '/dokumensPembayaran', // This will be handled by individual edit routes
                'akutansi' => '/dokumensAkutansi',
                'perpajakan' => '/dokumensPerpajakan',
                'ibub', 'ibub' => '/dokumensB',
                default => null
            };
            $diagramUrl = match($module) {
                'ibua', 'ibua' => '/diagram',
                'ibub', 'ibub' => '/diagramB',
                'pembayaran' => '/diagramPembayaran',
                'akutansi' => '/diagramAkutansi',
                'perpajakan' => '/diagramPerpajakan',
                default => '/diagram'
            };
        @endphp

        @if($isOwner)
            <!-- Owner Menu - Clean and Simple -->
            <div style="flex: 1; display: flex; flex-direction: column;">
                <a href="{{ url('/owner/dashboard') }}" class="{{ $menuDashboard ?? '' }}">
                    <i class="fa-solid fa-satellite-dish"></i> Dashboard Owner
                </a>
                <a href="{{ url('/owner/rekapan') }}" class="{{ $menuRekapan ?? '' }}">
                    <i class="fa-solid fa-chart-pie"></i> Rekapan Dokumen
                </a>
                <a href="{{ url('/owner/rekapan-keterlambatan') }}" class="{{ $menuRekapanKeterlambatan ?? '' }}">
                    <i class="fa-solid fa-exclamation-triangle"></i> Rekapan Keterlambatan
                </a>
            </div>
            <div style="margin-top: auto; padding-bottom: 20px;">
                <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-owner').submit();" style="display: block; margin-left: 30px; margin-top: 20px;">
                    <i class="fa-solid fa-sign-out-alt"></i> Keluar
                </a>
                <form id="logout-form-owner" action="{{ url('/logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        @else
            <!-- Regular Menu for other roles -->
            <a href="{{ url($dashboardUrl) }}" class="{{ $menuDashboard ?? '' }}"><i class="fa-solid fa-house"></i> Home</a>

            <!-- Owner Dashboard - Only for Admin users -->
            @if(auth()->check() && (auth()->user()->role === 'Admin' || auth()->user()->role === 'admin'))
                <a href="{{ url('/owner/dashboard') }}" class="nav-link">
                    <i class="fa-solid fa-satellite-dish"></i> Owner Dashboard
                </a>
            @endif

            <!-- Inbox Menu - Untuk IbuB, Perpajakan, Akutansi -->
    @php
        $currentUserRole = 'IbuA'; // Default
        if (auth()->check()) {
            $user = auth()->user();
            if (isset($user->name)) {
                $nameToRole = [
                    'Ibu A' => 'ibuA',
                    'IbuA' => 'ibuA',
                    'IbuB' => 'ibuB',
                    'Ibu B' => 'ibuB',
                    'Perpajakan' => 'perpajakan',
                    'Akutansi' => 'akutansi',
                    'Pembayaran' => 'pembayaran'
                ];
                $currentUserRole = $nameToRole[$user->name] ?? 'IbuA';
            } elseif (isset($user->role)) {
                $currentUserRole = $user->role;
            }
        }
        
        $inboxRoles = ['ibuB', 'IbuB', 'Perpajakan', 'perpajakan', 'Akutansi', 'akutansi'];
        $showInbox = in_array($currentUserRole, $inboxRoles);
        $inboxRoleForQuery = 'IbuB';
        if (in_array($currentUserRole, ['Perpajakan', 'perpajakan'])) {
            $inboxRoleForQuery = 'Perpajakan';
        } elseif (in_array($currentUserRole, ['Akutansi', 'akutansi'])) {
            $inboxRoleForQuery = 'Akutansi';
        }
    @endphp
    
    @if($showInbox)
        <a href="{{ url('/inbox') }}" class="{{ request()->routeIs('inbox.*') ? 'active' : '' }}">
            <i class="fa-solid fa-inbox"></i>
            Inbox
            @php
                try {
                    $inboxCount = \App\Models\Dokumen::where('inbox_approval_for', $inboxRoleForQuery)
                        ->where('inbox_approval_status', 'pending')
                        ->count();
                } catch (\Exception $e) {
                    $inboxCount = 0;
                }
            @endphp
            @if($inboxCount > 0)
                <span class="badge badge-danger right">{{ $inboxCount }}</span>
            @endif
        </a>
    @endif
    
    @unless($isOwner)
    <!-- Dropdown Menu Dokumen - Customized per Module -->
    <div class="dropdown-menu-custom">
      <div class="dropdown-toggle {{ $menuDokumen ?? '' }}" id="dokumenDropdown">
        <span><i class="fa-solid fa-file-lines {{ $menuDokumen ?? '' }}"></i> 
          @if($module === 'pembayaran')
            Pembayaran
          @elseif($module === 'akutansi')
            Akutansi
          @elseif($module === 'perpajakan')
            Perpajakan
          @elseif($module === 'ibub')
            Dokumen
          @else
            Dokumen
          @endif
        </span>
        <i class="fa-solid fa-chevron-down dropdown-icon"></i>
      </div>
      <div class="dropdown-content {{ $menuDokumen ? 'show' : '' }}" id="dokumenContent">
        @if($module === 'pembayaran')
          <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}"><i></i> Daftar Pembayaran</a>
          <a href="{{ route('pembayaran.rekapan') }}" class="{{ $menuRekapanDokumen ?? '' }}"><i></i> Rekapan Dokumen</a>
          <a href="{{ url($pengembalianUrl) }}" class="{{ $menuRekapKeterlambatan ?? '' }}"><i></i> Rekap Keterlambatan</a>
        @elseif($module === 'akutansi')
          <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
            <span class="menu-item-wrapper">
              <i></i> Daftar Akutansi
              <span class="menu-notification-badge" id="akutansi-notification-badge" style="display: none;">0</span>
            </span>
          </a>
          <a href="{{ url($pengembalianUrl) }}" class="{{ $menuDaftarDokumenDikembalikan ?? '' }}"><i></i> Daftar Pengembalian Akutansi</a>
          <a href="{{ route('akutansi.rekapan') }}" class="{{ $menuRekapan ?? '' }}"><i></i> Rekapan Akutansi</a>
        @elseif($module === 'perpajakan')
          <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
            <span class="menu-item-wrapper">
              <i></i> Daftar Perpajakan
              <span class="menu-notification-badge" id="perpajakan-notification-badge" style="display: none;">0</span>
            </span>
          </a>
          <a href="{{ url($pengembalianUrl) }}" class="{{ $menuDaftarDokumenDikembalikan ?? '' }}"><i></i> Daftar Pengembalian Perpajakan</a>
          <a href="{{ url('/rekapan-perpajakan') }}" class="{{ $menuRekapan ?? '' }}">
            <span class="menu-item-wrapper">
              <i></i> Rekapan
            </span>
          </a>
        @elseif($module === 'ibub')
          <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
            <span class="menu-item-wrapper">
              <i></i> Daftar Dokumen
              <span class="menu-notification-badge" id="notification-badge" style="display: none;">0</span>
            </span>
          </a>
          <a href="{{ url('/pengembalian-dokumens-ke-bidang') }}" class="{{ $menuPengembalianKeBidang ?? '' }}">
            <span class="menu-item-wrapper">
              <i></i> Pengembalian ke Bidang
              <span class="menu-notification-badge" id="pengembalian-ke-bidang-badge" style="display: none;">0</span>
            </span>
          </a>
          <a href="{{ url('/pengembalian-dokumensB') }}" class="{{ $menuDaftarDokumenDikembalikan ?? '' }}">
            <span class="menu-item-wrapper">
              <i></i> Pengembalian dari Bagian
              <span class="menu-notification-badge" id="pengembalian-ke-bagian-badge" style="display: none;">0</span>
            </span>
          </a>
          <a href="{{ url('/rekapan-ibuB') }}" class="{{ $menuRekapan ?? '' }}">
            <span class="menu-item-wrapper">
              <i></i> Rekapan
            </span>
          </a>
        @else
          <!-- IbuA -->
          <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}"><i></i> Daftar Dokumen</a>
          @if($tambahDokumenUrl)
          <a href="{{ url($tambahDokumenUrl) }}" class="{{ $menuTambahDokumen ?? '' }}"><i></i> Tambah Dokumen</a>
          @endif
          <a href="{{ url('/rekapan') }}" class="{{ $menuRekapan ?? '' }}"><i class="fa-solid fa-chart-pie"></i> Rekapan</a>
        @endif
      </div>
    </div>

    <a href="{{ url($diagramUrl) }}" class="{{ $menuDiagram ?? '' }}"><i class="fa-solid fa-chart-simple"></i> Diagram</a>
    @endunless

    @unless($isOwner)
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
    </form>
    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
    @endunless
        @endif
  </div>

  <!-- Content -->
  <div class="content">
    <!-- Notifikasi Success/Error -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);">
        <i class="fa-solid fa-circle-check me-2"></i>
        <strong>Berhasil!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <strong>Terjadi Kesalahan!</strong>
        <ul class="mb-0 mt-2" style="padding-left: 20px;">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @yield('content')
    </div>

    <!-- Notification Container -->
    <div id="notification-container"></div>
    <div id="globalNotificationContainer"></div>

    <footer>
      &copy; 2025 Agenda Online - All Rights Reserved
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Pusher & Laravel Echo -->
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

  <!-- Laravel Echo Setup for Real-time Notifications -->
  <script>
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: '5ce115effb7713734101',
      cluster: 'ap1',
      forceTLS: true,
      disableStats: true,
      enabledTransports: ['ws', 'wss', 'flashsocket']
    });

    console.log('Laravel Echo initialized for real-time notifications with Pusher');
    console.log('CSRF Token:', token);

    // Test connection
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ Pusher connected successfully');
    });

    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('❌ Pusher connection error:', err);
    });

    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.warn('⚠️ Pusher disconnected');
    });
  </script>

  <!-- Custom JS for Dropdown -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const dropdownToggle = document.getElementById('dokumenDropdown');
      const dropdownContent = document.getElementById('dokumenContent');
      const dropdownIcon = dropdownToggle.querySelector('.dropdown-icon');

      dropdownToggle.addEventListener('click', function() {
        // Toggle dropdown content
        dropdownContent.classList.toggle('show');

        // Rotate icon
        dropdownIcon.classList.toggle('rotate');

        // Toggle active state
        dropdownToggle.classList.toggle('active');
      });
    });
  </script>

  <!-- Auto-Refresh System for IbuB -->
  <script>
    (function() {
      'use strict';

      // Get user role from authenticated user
      let currentUserRole = 'IbuA'; // Default
      @php
          $tempUserRole = 'IbuA';
          if (auth()->check()) {
              $user = auth()->user();
              if (isset($user->name)) {
                  $nameToRole = [
                      'Ibu A' => 'ibuA',
                      'IbuA' => 'ibuA',
                      'IbuB' => 'ibuB',
                      'Ibu B' => 'ibuB',
                      'Perpajakan' => 'perpajakan',
                      'Akutansi' => 'akutansi',
                      'Pembayaran' => 'pembayaran'
                  ];
                  $tempUserRole = $nameToRole[$user->name] ?? 'IbuA';
              } elseif (isset($user->role)) {
                  $tempUserRole = $user->role;
              }
          }
      @endphp
      currentUserRole = '{{ $tempUserRole }}';

      const isIbuB = currentUserRole.toLowerCase() === 'ibub';
      const isIbuA = currentUserRole.toLowerCase() === 'ibua';
      const isPerpajakan = currentUserRole.toLowerCase() === 'perpajakan';
      const isAkutansi = currentUserRole.toLowerCase() === 'akutansi';
      const isPembayaran = currentUserRole.toLowerCase() === 'pembayaran';

      console.log('Auto-refresh system setup:', {
        userRole: currentUserRole,
        isIbuB: isIbuB,
        isIbuA: isIbuA,
        isPerpajakan: isPerpajakan,
        isAkutansi: isAkutansi,
        isPembayaran: isPembayaran,
        path: window.location.pathname
      });

      // Additional debugging for akutansi
      if (isAkutansi) {
        console.log('🟢 AKUTANSI MODULE DETECTED - Notifications should work');
      }

      // Enable for IbuB, Perpajakan, Akutansi, Pembayaran (any page) - Excluding IbuA only
      const shouldEnableAutoRefresh = isIbuB || isPerpajakan || isAkutansi || isPembayaran;

      console.log('Should enable auto-refresh:', shouldEnableAutoRefresh);

      if (!shouldEnableAutoRefresh) {
        console.log('Auto-refresh disabled: User is IbuA or role not recognized');
        return;
      }

      console.log('Auto-refresh enabled for:', currentUserRole);

      // Configuration
      const POLLING_INTERVAL = 10000; // 10 detik
      const NOTIFICATION_DURATION = 8000; // 8 detik
      let pollingTimer = null;
      let lastChecked = Date.now();
      let notificationCount = 0;
      let returnedNotificationCount = 0;
      let perpajakanNotificationCount = 0;
      let akutansiNotificationCount = 0;
      let pembayaranNotificationCount = 0;
      let knownDocumentIds = new Set();

      // Smart Detection System
      let userActiveState = {
        isInputting: false,
        hasModalOpen: false,
        lastActivity: Date.now()
      };

      function isUserActive() {
        const activeElement = document.activeElement;
        const isInputting = activeElement && (
          activeElement.tagName === 'INPUT' ||
          activeElement.tagName === 'TEXTAREA' ||
          activeElement.tagName === 'SELECT' ||
          activeElement.contentEditable === 'true'
        );

        const hasModalOpen = document.querySelector('.modal.show') !== null ||
                            document.querySelector('[role="dialog"]') !== null;

        const isTyping = (Date.now() - userActiveState.lastActivity) < 2000; // Reduced from 3s to 2s

        // For IbuA, we want to be less restrictive to show important notifications
        const isIbuA = currentUserRole.toLowerCase() === 'ibua';
        if (isIbuA) {
          // Only skip if user is actively typing in an input field
          return isInputting;
        }

        return isInputting || hasModalOpen || isTyping;
      }

      // Track user activity
      document.addEventListener('keydown', function() {
        userActiveState.lastActivity = Date.now();
      });

      document.addEventListener('focusin', function(e) {
        const tag = e.target.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') {
          userActiveState.isInputting = true;
        }
      });

      document.addEventListener('focusout', function() {
        userActiveState.isInputting = false;
      });

      // Initialize known documents from current page
      function initializeKnownDocuments() {
        // For returned documents, we want to start fresh to ensure we show notifications
        const isIbuA = currentUserRole.toLowerCase() === 'ibua';
        const isPerpajakan = currentUserRole.toLowerCase() === 'perpajakan';
        const isAkutansi = currentUserRole.toLowerCase() === 'akutansi';
        
        if (isIbuA || isPerpajakan || isAkutansi) {
          // Don't pre-populate known document IDs for IbuA, Perpajakan, and Akutansi 
          // to ensure notifications work for new documents
          knownDocumentIds.clear();
          console.log('Known document IDs cleared for', currentUserRole, 'notifications');
          return;
        }

        const tableRows = document.querySelectorAll('table tbody tr');
        tableRows.forEach(row => {
          const editLink = row.querySelector('a[href*="/edit"]');
          if (editLink) {
            const docId = editLink.getAttribute('href').match(/\/(\d+)\/edit/);
            if (docId) {
              knownDocumentIds.add(parseInt(docId[1]));
            }
          }
        });
        console.log('Known document IDs initialized:', Array.from(knownDocumentIds));
      }

      // Update notification badge
      function updateNotificationBadge(count, type = 'new') {
        let badgeId;
        if (type === 'returned') {
          badgeId = 'notification-badge-returned';
        } else if (type === 'perpajakan') {
          badgeId = 'perpajakan-notification-badge';
        } else if (type === 'akutansi') {
          badgeId = 'akutansi-notification-badge';
          console.log('🎯 AKUTANSI BADGE UPDATE - Badge ID:', badgeId, 'Count:', count);
        } else {
          badgeId = 'notification-badge';
        }

        const badge = document.getElementById(badgeId);
        console.log('🎯 BADGE ELEMENT FOUND:', badge, 'for type:', type, 'ID:', badgeId);

        if (badge) {
          if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';

            // Highlight appropriate menu
            let menuItemId;
            if (type === 'returned') {
              menuItemId = 'menu-daftar-dokumen-dikembalikan';
            } else if (type === 'perpajakan') {
              menuItemId = 'menu-daftar-dokumen'; // perpajakan uses same id
            } else {
              menuItemId = 'menu-daftar-dokumen';
            }

            const menuItem = document.getElementById(menuItemId);

            if (menuItem) {
              menuItem.classList.add('menu-highlight');
              if (type === 'returned') {
                menuItem.classList.add('returned');
              }
              setTimeout(() => {
                menuItem.classList.remove('menu-highlight');
                menuItem.classList.remove('returned');
              }, 1500);
            }
          } else {
            badge.style.display = 'none';
          }
        }
      }

      // Show toast notification
      function showNotification(newDocuments, type = 'new') {
        const container = document.getElementById('notification-container');
        if (!container) return;

        newDocuments.forEach((doc, index) => {
          setTimeout(() => {
            const notificationId = 'notification-' + Date.now() + '-' + index;
            const notification = document.createElement('div');
            notification.id = notificationId;
            let notificationClass;
    if (type === 'returned') {
      notificationClass = 'notification-returned';
    } else if (type === 'perpajakan') {
      notificationClass = 'notification-perpajakan';
    } else if (type === 'akutansi') {
      notificationClass = 'notification-akutansi';
    } else if (type === 'pembayaran') {
      notificationClass = 'notification-pembayaran';
    } else {
      notificationClass = 'notification-new';
    }
    notification.className = `notification-toast ${notificationClass}`;

            const formattedRupiah = new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0
            }).format(doc.nilai_rupiah || 0);

            // Different content for returned documents
            if (type === 'returned') {
              notification.innerHTML = `
                <div class="notification-header notification-header-returned">
                  <div class="notification-title">
                    <i class="fa-solid fa-file-circle-exclamation"></i>
                    Dokumen Dikembalikan
                  </div>
                  <button class="notification-close" onclick="removeNotification('${notificationId}')">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </div>
                <div class="notification-body">
                  <strong>No. Agenda:</strong> ${doc.nomor_agenda || '-'}<br>
                  <strong>No. SPP:</strong> ${doc.nomor_spp || '-'}<br>
                  <strong>Alasan:</strong> <span class="alasan-text">${doc.alasan_pengembalian || 'Tidak ada alasan'}</span><br>
                  <small style="opacity: 0.8;">Dikembalikan dari Ibu Yuni - ${doc.returned_at}</small>
                </div>
                <div class="notification-footer">
                  <button class="btn-refresh" onclick="refreshPage()">
                    <i class="fa-solid fa-refresh"></i> Refresh Halaman
                  </button>
                  <button class="btn-refresh" onclick="viewReturnedDocument(${doc.id})">
                    <i class="fa-solid fa-eye"></i> Lihat Detail
                  </button>
                </div>
              `;
            } else if (type === 'perpajakan') {
              // Perpajakan document notification
              notification.innerHTML = `
                <div class="notification-header notification-header-perpajakan">
                  <div class="notification-title">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Dokumen Baru untuk Team Perpajakan
                  </div>
                  <button class="notification-close" onclick="removeNotification('${notificationId}')">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </div>
                <div class="notification-body">
                  <strong>No. Agenda:</strong> ${doc.nomor_agenda || '-'}<br>
                  <strong>No. SPP:</strong> ${doc.nomor_spp || '-'}<br>
                  <strong>Nilai:</strong> ${formattedRupiah}<br>
                  <strong>Status Perpajakan:</strong> ${doc.status_perpajakan || 'Belum diproses'}<br>
                  <small style="opacity: 0.8;">Dokumen baru dari Ibu Yuni - ${doc.sent_at}</small>
                </div>
                <div class="notification-footer">
                  <button class="btn-refresh" onclick="refreshPage()">
                    <i class="fa-solid fa-refresh"></i> Refresh Halaman
                  </button>
                  <button class="btn-refresh" onclick="viewDocument(${doc.id})">
                    <i class="fa-solid fa-eye"></i> Lihat Detail
                  </button>
                </div>
              `;
            } else if (type === 'akutansi') {
              // Akutansi document notification
              notification.innerHTML = `
                <div class="notification-header notification-header-akutansi">
                  <div class="notification-title">
                    <i class="fa-solid fa-calculator"></i>
                    Dokumen Baru untuk Team Akutansi
                  </div>
                  <button class="notification-close" onclick="removeNotification('${notificationId}')">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </div>
                <div class="notification-body">
                  <strong>No. Agenda:</strong> ${doc.nomor_agenda || '-'}<br>
                  <strong>No. SPP:</strong> ${doc.nomor_spp || '-'}<br>
                  <strong>Nilai:</strong> ${formattedRupiah}<br>
                  <strong>Status:</strong> ${doc.status || 'Belum diproses'}<br>
                  <small style="opacity: 0.8;">Dokumen baru dari Perpajakan - ${doc.sent_at}</small>
                </div>
                <div class="notification-footer">
                  <button class="btn-refresh" onclick="refreshPage()">
                    <i class="fa-solid fa-refresh"></i> Refresh Halaman
                  </button>
                  <button class="btn-refresh" onclick="viewDocument(${doc.id})">
                    <i class="fa-solid fa-eye"></i> Lihat Detail
                  </button>
                </div>
              `;
            } else {
              // Original new document notification
              notification.innerHTML = `
                <div class="notification-header notification-header-new">
                  <div class="notification-title">
                    <i class="fa-solid fa-file-circle-check"></i>
                    Dokumen Baru Diterima
                  </div>
                  <button class="notification-close" onclick="removeNotification('${notificationId}')">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </div>
                <div class="notification-body">
                  <strong>No. Agenda:</strong> ${doc.nomor_agenda || '-'}<br>
                  <strong>No. SPP:</strong> ${doc.nomor_spp || '-'}<br>
                  <strong>Nilai:</strong> ${formattedRupiah}<br>
                  <small style="opacity: 0.8;">Dokumen baru dari IbuA - ${doc.sent_at}</small>
                </div>
                <div class="notification-footer">
                  <button class="btn-refresh" onclick="refreshPage()">
                    <i class="fa-solid fa-refresh"></i> Refresh Halaman
                  </button>
                  <button class="btn-refresh" onclick="viewDocument(${doc.id})">
                    <i class="fa-solid fa-eye"></i> Lihat Detail
                  </button>
                </div>
              `;
            }

            container.appendChild(notification);

            // Notifikasi permanen - hanya hilang ketika user klik tombol X
            // Auto-remove dihapus agar notifikasi tetap muncul sampai user menutupnya

            notificationCount++;
          }, index * 500); // Stagger notifications
        });

        updateNotificationBadge(notificationCount);
      }

      // Remove notification
      window.removeNotification = function(notificationId) {
        const notification = document.getElementById(notificationId);
        if (notification) {
          // Determine which type of notification this is
          const isReturnedNotification = notification.classList.contains('notification-returned');
          const isPerpajakanNotification = notification.classList.contains('notification-perpajakan');
          const isAkutansiNotification = notification.classList.contains('notification-akutansi');
          const isPembayaranNotification = notification.classList.contains('notification-pembayaran');

          notification.classList.add('hiding');
          setTimeout(() => {
            notification.remove();

            if (isReturnedNotification) {
              returnedNotificationCount = Math.max(0, returnedNotificationCount - 1);
              updateNotificationBadge(returnedNotificationCount, 'returned');
            } else if (isPerpajakanNotification) {
              perpajakanNotificationCount = Math.max(0, perpajakanNotificationCount - 1);
              updateNotificationBadge(perpajakanNotificationCount, 'perpajakan');
            } else if (isAkutansiNotification) {
              akutansiNotificationCount = Math.max(0, akutansiNotificationCount - 1);
              updateNotificationBadge(akutansiNotificationCount, 'akutansi');
            } else if (isPembayaranNotification) {
              pembayaranNotificationCount = Math.max(0, pembayaranNotificationCount - 1);
              updateNotificationBadge(pembayaranNotificationCount, 'pembayaran');
            } else {
              notificationCount = Math.max(0, notificationCount - 1);
              updateNotificationBadge(notificationCount, 'new');
            }
          }, 300);
        }
      };

      // Refresh page with smart check
      window.refreshPage = function() {
        if (isUserActive()) {
          alert('Anda sedang menginput data. Silakan selesaikan terlebih dahulu, kemudian refresh secara manual.');
          return;
        }
        window.location.reload();
      };

      // View document
      window.viewDocument = function(docId) {
        if (isAkutansi) {
          window.location.href = `/dokumensAkutansi#doc-${docId}`;
        } else if (isPerpajakan) {
          window.location.href = `/dokumensPerpajakan#doc-${docId}`;
        } else if (isIbuB) {
          window.location.href = `/dokumensB/${docId}/edit`;
        } else {
          window.location.href = `/dokumens/${docId}/edit`;
        }
      };

      // View returned document for IbuA
      window.viewReturnedDocument = function(docId) {
        // Redirect to pengembalian dokumen page with the specific document
        window.location.href = `/pengembalian-dokumens#doc-${docId}`;
      };

      // Refresh page
      window.refreshPage = function() {
        window.location.reload();
      };

      // Check for updates
      async function checkForUpdates() {
        try {
          // Choose endpoint based on current module
          let endpoint;
          if (isIbuB) {
            endpoint = `/dokumensB/check-updates?last_checked=${Math.floor(lastChecked / 1000)}`;
          } else if (isPerpajakan) {
            endpoint = `/perpajakan/check-updates?last_checked=${Math.floor(lastChecked / 1000)}`;
          } else if (isAkutansi) {
            endpoint = `/akutansi/check-updates?last_checked=${Math.floor(lastChecked / 1000)}`;
          } else if (isPembayaran) {
            endpoint = `/pembayaran/check-updates?last_checked=${Math.floor(lastChecked / 1000)}`;
          } else {
            endpoint = `/dokumens/check-returned-updates?last_checked=${Math.floor(lastChecked / 1000)}`;
          }

          console.log('Checking updates from:', endpoint);
          console.log('Current module check:', { isIbuB, isIbuA, isPerpajakan, isAkutansi, isPembayaran });

          if (isAkutansi) {
            console.log('🔍 CHECKING FOR AKUTANSI UPDATES from:', endpoint);
          }

          try {
          const response = await fetch(endpoint);

          if (!response.ok) {
            console.error('HTTP Error:', response.status, response.statusText);
            return;
          }

          const data = await response.json();
          console.log('API Response:', data);

          if (data.error) {
            console.error('Update check failed:', data.message);
            return;
          }

          // Process data based on module
          let documents;
          if (isIbuB) {
            documents = data.new_documents;
          } else if (isPerpajakan) {
            documents = data.new_documents;
          } else if (isAkutansi) {
            documents = data.new_documents;
          } else if (isPembayaran) {
            documents = data.new_documents;
          } else {
            documents = data.returned_documents;
          }

          console.log('Processed documents:', documents);

          if (data.has_updates && documents.length > 0) {
            const newDocuments = documents.filter(doc => !knownDocumentIds.has(doc.id));

            if (newDocuments.length > 0) {
              console.log('New documents found:', newDocuments);
              console.log('🚨 NOTIFICATION TRIGGERED - Type will be:', isAkutansi ? 'akutansi' : (isPerpajakan ? 'perpajakan' : 'other'));

              // Add to known documents
              newDocuments.forEach(doc => knownDocumentIds.add(doc.id));

              // Show notifications
              let notificationType;
              if (isIbuB) {
                notificationType = 'new';
              } else if (isPerpajakan) {
                notificationType = 'perpajakan';
              } else if (isAkutansi) {
                notificationType = 'akutansi';
                console.log('🟢 AKUTANSI NOTIFICATION TYPE SET');
              } else if (isPembayaran) {
                notificationType = 'pembayaran';
              } else {
                notificationType = 'returned';
              }
              showNotification(newDocuments, notificationType);

              // Update badge counter based on type
              if (isIbuB) {
                notificationCount += newDocuments.length;
                updateNotificationBadge(notificationCount, 'new');
              } else if (isPerpajakan) {
                perpajakanNotificationCount += newDocuments.length;
                updateNotificationBadge(perpajakanNotificationCount, 'perpajakan');
              } else if (isAkutansi) {
                akutansiNotificationCount += newDocuments.length;
                console.log('🔔 UPDATING AKUTANSI BADGE with count:', akutansiNotificationCount);
                updateNotificationBadge(akutansiNotificationCount, 'akutansi');
              } else if (isPembayaran) {
                pembayaranNotificationCount = (pembayaranNotificationCount || 0) + newDocuments.length;
                updateNotificationBadge(pembayaranNotificationCount, 'pembayaran');
              } else {
                returnedNotificationCount += newDocuments.length;
                updateNotificationBadge(returnedNotificationCount, 'returned');
              }
            }
          }

          lastChecked = data.last_checked * 1000;

          } catch (fetchError) {
            console.error('Fetch error:', fetchError);
          }

        } catch (error) {
          // Filter out browser extension errors
          if (error.message && error.message.includes('ethereum')) {
            // Ignore crypto wallet errors
            return;
          }
          console.error('Failed to check updates:', error);
        }
      }

      // Universal Approval System - Check for waiting documents
      async function checkUniversalNotifications() {
        // Only check for non-IbuA users
        if (currentUserRole.toLowerCase() === 'ibua') {
          return;
        }

        try {
          const response = await fetch('/universal-approval/notifications');

          if (!response.ok) {
            return;
          }

          const data = await response.json();

          if (data.count !== undefined) {
            const badge = document.getElementById('universal-notification-badge');
            if (badge) {
              if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'inline-flex';
              } else {
                badge.style.display = 'none';
              }
            }
          }
        } catch (error) {
          console.error('Failed to check universal notifications:', error);
        }
      }

      // Start polling
      function startPolling() {
        console.log('Starting auto-refresh system...');
        console.log('Polling interval:', POLLING_INTERVAL + 'ms');

        // Initialize known documents
        initializeKnownDocuments();

        // Check immediately
        checkForUpdates();

        // Set up periodic polling
        pollingTimer = setInterval(() => {
          const shouldSkip = isUserActive();
          const isIbuA = currentUserRole.toLowerCase() === 'ibua';

          // Check universal notifications for all non-IbuA users
          checkUniversalNotifications();

          // For IbuA and Perpajakan, be less aggressive about skipping - only skip if actively typing
          if ((isIbuA || isPerpajakan || isAkutansi) && shouldSkip) {
            const activeElement = document.activeElement;
            const isActuallyTyping = activeElement && (
              activeElement.tagName === 'INPUT' ||
              activeElement.tagName === 'TEXTAREA' ||
              activeElement.tagName === 'SELECT'
            );

            const moduleName = isPerpajakan ? 'Perpajakan' : (isAkutansi ? 'Akutansi' : 'IbuA');
            if (isActuallyTyping) {
              console.log(`${moduleName}: Skipping update check - user is typing`);
              return;
            }
          }

          if (shouldSkip && !isIbuA && !isPerpajakan && !isAkutansi) {
            console.log('Skipping update check - user is active');
          } else {
            checkForUpdates();
          }
        }, POLLING_INTERVAL);
      }

      // Start the system
      startPolling();

      const moduleNames = [];
      if (isIbuB) moduleNames.push('IbuB');
      if (isPerpajakan) moduleNames.push('Perpajakan');
      if (isAkutansi) moduleNames.push('Akutansi');
      if (isIbuA) moduleNames.push('IbuA');
      if (isPembayaran) moduleNames.push('Pembayaran');

      console.log('✅ Auto-refresh system initialized for: ' + moduleNames.join(', '));
      console.log('Listening for new documents every ' + (POLLING_INTERVAL / 1000) + ' seconds');

    })();
  </script>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Smart Autocomplete JavaScript -->
  <script src="{{ asset('js/smart-autocomplete.js') }}"></script>
<!-- Global Inbox Notification System -->
<style>
/* Global Toast Notification Styles */
#globalNotificationContainer {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    pointer-events: none;
}

.global-notification-toast {
    min-width: 350px;
    max-width: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 0;
    overflow: hidden;
    animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    transform: translateX(400px);
    opacity: 0;
    margin-bottom: 16px;
    pointer-events: auto;
}

.global-notification-toast.show {
    transform: translateX(0);
    opacity: 1;
}

.global-notification-toast.hide {
    animation: slideOutRight 0.3s ease-in forwards;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.global-notification-toast.info {
    border-left: 5px solid #4299e1;
}

.global-notification-toast.error {
    border-left: 5px solid #f56565;
}

.global-notification-content {
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.global-notification-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.global-notification-toast.info .global-notification-icon {
    background: linear-gradient(135deg, #4299e1 0%, #90cdf4 100%);
    color: white;
}

.global-notification-toast.error .global-notification-icon {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
}

.global-notification-body {
    flex: 1;
}

.global-notification-title {
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 4px;
    color: #1a202c;
}

.global-notification-message {
    font-size: 14px;
    color: #4a5568;
    line-height: 1.5;
    margin-bottom: 8px;
}

.global-notification-action-btn {
    display: inline-block;
    margin-top: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #4299e1 0%, #63b3ed 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(66, 153, 225, 0.25);
}

.global-notification-action-btn:hover {
    background: linear-gradient(135deg, #3182ce 0%, #4299e1 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(66, 153, 225, 0.35);
    color: white;
    text-decoration: none;
}

.global-notification-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    color: #718096;
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
    transition: color 0.2s;
    z-index: 10;
}

.global-notification-close:hover {
    color: #2d3748;
}
</style>

<!-- Global Notification Container -->
<div id="globalNotificationContainer"></div>

<script>
(function() {
    'use strict';

    // Check if user has inbox access (IbuB, Perpajakan, Akutansi) or is IbuA
    const userRole = '{{ auth()->user()->role ?? "" }}';
    const userRoleLower = userRole.toLowerCase();
    
    // Case-insensitive check for inbox roles
    const inboxRoles = ['ibub', 'perpajakan', 'akutansi'];
    const isIbuA = ['ibua', 'ibu a', 'ibu tarapul'].includes(userRoleLower);
    const hasInboxAccess = inboxRoles.includes(userRoleLower);

    // Debug logging
    console.log('Notification System Init:', {
        userRole: userRole,
        userRoleLower: userRoleLower,
        hasInboxAccess: hasInboxAccess,
        isIbuA: isIbuA
    });

    // Check if user is IbuB
    const isIbuB = ['ibub', 'ibu b', 'ibu yuni'].includes(userRoleLower);

    // Debug logging
    console.log('Notification System Init:', {
        userRole: userRole,
        userRoleLower: userRoleLower,
        hasInboxAccess: hasInboxAccess,
        isIbuA: isIbuA,
        isIbuB: isIbuB
    });

    // Initialize IbuA rejected documents notification if applicable
    if (isIbuA) {
        console.log('Initializing IbuA rejected notifications');
        initIbuARejectedNotifications();
        // IbuA does not have inbox access, so exit here
        return;
    }

    // Initialize IbuB rejected documents notification if applicable
    if (isIbuB) {
        console.log('Initializing IbuB rejected notifications');
        initIbuBRejectedNotifications();
    }

    // Only continue with inbox notifications if user has inbox access
    if (!hasInboxAccess) {
        console.log('User does not have inbox access, exiting inbox notification system');
        return; // Exit if user doesn't have access
    }

    // Inbox notification polling
    let inboxLastCheckTime = localStorage.getItem('inbox_last_check_time');
    if (!inboxLastCheckTime) {
        inboxLastCheckTime = new Date().toISOString();
        localStorage.setItem('inbox_last_check_time', inboxLastCheckTime);
    }

    // Track shown notifications to prevent duplicates
    const shownNotificationIds = new Set(JSON.parse(localStorage.getItem('inbox_shown_notifications') || '[]'));

    // Map user role to inbox role format (case-insensitive)
    const roleMap = {
        'ibub': 'IbuB',
        'ibu b': 'IbuB',
        'ibu yuni': 'IbuB',
        'perpajakan': 'Perpajakan',
        'akutansi': 'Akutansi'
    };
    const inboxRole = roleMap[userRoleLower] || (userRoleLower === 'perpajakan' ? 'Perpajakan' : 
                                                    userRoleLower === 'akutansi' ? 'Akutansi' : 
                                                    userRoleLower === 'ibub' ? 'IbuB' : userRole);
    
    console.log('Mapped inbox role:', inboxRole);

    // Real-time notification using Laravel Echo (Public Channel)
    if (window.Echo && hasInboxAccess) {
        console.log('🚀 Setting up real-time notifications for inbox role:', inboxRole);

        try {
            // Use public channel - no authentication required
            window.Echo.channel('inbox-updates')
                .listen('.document.sent.to.inbox', (e) => {
                    console.log('🎉 Real-time notification received:', e);

                    // Only show notification if it's for this user's role
                    if (e.recipientRole && (e.recipientRole.toLowerCase() === inboxRole.toLowerCase() ||
                        (e.recipientRole.toLowerCase() === 'ibub' && inboxRole.toLowerCase() === 'ibub'))) {

                        console.log('✅ Notification is for current user role:', inboxRole);

                        // Show immediate notification
                        showGlobalToastNotification(
                            'info',
                            'Dokumen Baru Masuk',
                            `${e.dokumen.nomor_agenda} - ${e.dokumen.nomor_spp}`,
                            `/inbox/${e.dokumen.id}`,
                            'Lihat Dokumen'
                        );

                        // Play notification sound if available
                        playNotificationSound();

                        // Update inbox count immediately
                        updateInboxCount();

                        // Don't wait for polling - refresh last check time
                        const now = new Date().toISOString();
                        localStorage.setItem('inbox_last_check_time', now);
                        inboxLastCheckTime = now;
                    } else {
                        console.log('🔕 Notification not for current user role. For:', e.recipientRole, 'Current:', inboxRole);
                    }
                })
                .subscribed(() => {
                    console.log('✅ Successfully subscribed to public channel: inbox-updates');
                })
                .error((error) => {
                    console.error('❌ Error subscribing to public channel:', error);
                });

            console.log('🔧 Real-time listener setup completed for public channel: inbox-updates');
        } catch (error) {
            console.error('💥 Failed to setup real-time notifications:', error);
        }
    }

    // Global toast notification function
    function showGlobalToastNotification(type, title, message, actionUrl, actionText) {
        const container = document.getElementById('globalNotificationContainer');
        if (!container) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = `global-notification-toast ${type}`;
        
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-times-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-bell"></i>'
        };

        toast.innerHTML = `
            <button class="global-notification-close" onclick="this.parentElement.remove()">&times;</button>
            <div class="global-notification-content">
                <div class="global-notification-icon">
                    ${icons[type] || icons.info}
                </div>
                <div class="global-notification-body">
                    <div class="global-notification-title">${title}</div>
                    <div class="global-notification-message">${message}</div>
                    ${actionUrl ? `<a href="${actionUrl}" class="global-notification-action-btn">${actionText || 'Lihat Dokumen'}</a>` : ''}
                </div>
            </div>
        `;

        container.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto-remove untuk notifikasi success/error biasa setelah 4 detik
        // Notifikasi dokumen masuk/reject (dengan actionUrl) tetap permanen
        if ((type === 'success' || type === 'error') && !actionUrl) {
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }, 4000); // 4 detik untuk notifikasi success/error biasa
        }
        // Jika punya actionUrl (dokumen masuk/reject) atau type info/warning, tetap permanen
    }

    // Play notification sound
    function playNotificationSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (e) {
            console.log('Sound notification not available');
        }
    }

    // Function to update inbox count immediately
    function updateInboxCount() {
        try {
            const inboxBadges = document.querySelectorAll('.badge-danger');
            fetch('/inbox/check-new?last_check_time=' + encodeURIComponent(new Date(Date.now() - 60000).toISOString()))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.pending_count !== undefined) {
                        inboxBadges.forEach(badge => {
                            if (data.pending_count > 0) {
                                badge.textContent = data.pending_count;
                                badge.style.display = 'inline-block';
                            } else {
                                badge.style.display = 'none';
                            }
                        });
                    }
                })
                .catch(error => console.error('Error updating inbox count:', error));
        } catch (error) {
            console.error('Error in updateInboxCount:', error);
        }
    }

    // Function to check for new inbox documents
    async function checkInboxNotifications() {
        try {
            // Ensure we have inbox access before checking
            // IbuA does not have inbox access, so skip the check
            if (!hasInboxAccess) {
                console.log('No inbox access, skipping notification check');
                return;
            }

            // Debug: Log current user role and inbox access
            console.log('Checking inbox notifications for role:', userRole, 'hasInboxAccess:', hasInboxAccess, 'inboxRole:', inboxRole);
            
            const response = await fetch(`/inbox/check-new?last_check_time=${encodeURIComponent(inboxLastCheckTime)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                console.warn('Inbox notification check failed:', response.status, response.statusText);
                if (response.status === 403) {
                    console.warn('Access denied - user role may not be recognized');
                }
                return;
            }

            const data = await response.json();

            if (data.success) {
                // Update last check time
                if (data.current_time) {
                    inboxLastCheckTime = data.current_time;
                    localStorage.setItem('inbox_last_check_time', inboxLastCheckTime);
                }

                // Debug: Log notification data
                console.log('Inbox notification data:', {
                    new_documents_count: data.new_documents_count,
                    pending_count: data.pending_count,
                    new_documents: data.new_documents
                });

                // If there are new documents
                if (data.new_documents_count > 0 && data.new_documents.length > 0) {
                    // Filter out already shown notifications
                    // Only filter if document was shown more than 1 minute ago (to allow re-notification if needed)
                    const now = Date.now();
                    const newDocsToShow = data.new_documents.filter(doc => {
                        const docKey = `doc_${doc.id}_shown`;
                        const shownTime = localStorage.getItem(docKey);
                        
                        // If shown less than 1 minute ago, skip
                        if (shownTime && (now - parseInt(shownTime)) < 60000) {
                            return false;
                        }
                        
                        // Mark as shown with current timestamp
                        localStorage.setItem(docKey, now.toString());
                        shownNotificationIds.add(doc.id);
                        return true;
                    });

                    // Save shown notification IDs to localStorage
                    localStorage.setItem('inbox_shown_notifications', JSON.stringify(Array.from(shownNotificationIds)));

                    // Show notification on ALL pages (including inbox page)
                    // The inbox page will also show its own notification, but global notification should still appear
                    console.log('New documents to show:', newDocsToShow.length, 'Total new documents:', data.new_documents_count);
                    
                    if (newDocsToShow.length > 0) {
                        // Show toast notification for each new document (on all pages)
                        // Add small delay between notifications if multiple
                        newDocsToShow.forEach((doc, index) => {
                            setTimeout(() => {
                                const message = `${doc.nomor_agenda} - ${doc.uraian_spp}`;
                                console.log('Showing notification for document:', doc.id, doc.nomor_agenda);
                                showGlobalToastNotification('info', 'Dokumen Baru Masuk!', message, doc.url, 'Lihat Dokumen');
                            }, index * 500); // Stagger notifications by 500ms
                        });

                        // Play sound only once
                        playNotificationSound();
                    } else {
                        console.log('All documents have already been shown recently');
                    }

                    // Update badge if on inbox page
                    if (window.updateNewDocumentsBadge) {
                        window.updateNewDocumentsBadge(data.new_documents_count, data.pending_count);
                    }
                }
            } else {
                console.warn('Inbox notification check returned unsuccessful:', data.message);
            }
        } catch (error) {
            console.error('Error checking inbox notifications:', error);
        }
    }

    // Check immediately on page load (with small delay to ensure DOM is ready)
    setTimeout(function() {
        checkInboxNotifications();
    }, 1000);

    // Poll every 3 seconds for better responsiveness (as backup to real-time)
    setInterval(checkInboxNotifications, 3000);

    // Also check when page becomes visible (user switches back to tab)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(checkInboxNotifications, 500);
        }
    });

    // Check when window gains focus
    window.addEventListener('focus', function() {
        setTimeout(checkInboxNotifications, 500);
    });

    // Also check when page is fully loaded
    if (document.readyState === 'complete') {
        setTimeout(checkInboxNotifications, 1000);
    } else {
        window.addEventListener('load', function() {
            setTimeout(checkInboxNotifications, 1000);
        });
    }

    // IbuA Rejected Documents Notification System
    function initIbuARejectedNotifications() {
        console.log('initIbuARejectedNotifications function called');
        // Rejected documents notification polling
        // Reset last check time jika lebih dari 24 jam yang lalu untuk memastikan semua dokumen terdeteksi
        let rejectedLastCheckTime = localStorage.getItem('ibua_rejected_last_check_time');
        if (!rejectedLastCheckTime) {
            rejectedLastCheckTime = new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(); // 24 jam yang lalu
            localStorage.setItem('ibua_rejected_last_check_time', rejectedLastCheckTime);
        } else {
            // Jika last check time lebih dari 24 jam yang lalu, reset ke 24 jam yang lalu
            const lastCheck = new Date(rejectedLastCheckTime);
            const twentyFourHoursAgo = new Date(Date.now() - 24 * 60 * 60 * 1000);
            if (lastCheck < twentyFourHoursAgo) {
                rejectedLastCheckTime = twentyFourHoursAgo.toISOString();
                localStorage.setItem('ibua_rejected_last_check_time', rejectedLastCheckTime);
                console.log('🔄 Reset rejected documents last check time to 24 hours ago');
            }
        }

        // Track shown rejected notifications to prevent duplicates
        const shownRejectedIds = new Set(JSON.parse(localStorage.getItem('ibua_shown_rejected_notifications') || '[]'));

        // Function to check for rejected documents
        async function checkRejectedDocuments() {
            try {
                console.log('🔍 Checking for rejected documents...', { lastCheckTime: rejectedLastCheckTime });
                
                const response = await fetch(`/ibua/check-rejected?last_check_time=${encodeURIComponent(rejectedLastCheckTime)}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    console.error('❌ Failed to check rejected documents:', response.status, response.statusText);
                    if (response.status === 403) {
                        console.error('Access denied - user may not have permission');
                    }
                    return;
                }

                const data = await response.json();
                console.log('📋 Rejected documents check result:', {
                    success: data.success,
                    rejected_count: data.rejected_documents_count,
                    total_rejected: data.total_rejected,
                    documents: data.rejected_documents
                });

                if (data.success) {
                    // JANGAN update lastCheckTime terlalu cepat
                    // Biarkan dokumen yang sama bisa ditampilkan lagi jika sudah lebih dari 30 menit
                    // Update lastCheckTime hanya untuk tracking, bukan untuk filtering
                    if (data.current_time) {
                        // Update last check time untuk tracking, tapi jangan gunakan untuk filtering dokumen
                        rejectedLastCheckTime = data.current_time;
                        localStorage.setItem('ibua_rejected_last_check_time', rejectedLastCheckTime);
                        console.log('✅ Updated last check time to:', rejectedLastCheckTime);
                    }

                    // If there are rejected documents (baik baru maupun yang sudah pernah ditampilkan)
                    if (data.rejected_documents_count > 0 && data.rejected_documents.length > 0) {
                        console.log('🔔 Found rejected documents:', data.rejected_documents.length);
                        
                        // Filter dokumen yang perlu ditampilkan
                        // Untuk memastikan notifikasi selalu muncul, tampilkan dokumen yang di-reject dalam 24 jam terakhir
                        // Tampilkan jika:
                        // 1. Belum pernah ditampilkan sebelumnya, ATAU
                        // 2. Sudah pernah ditampilkan tapi lebih dari 5 menit yang lalu (untuk memastikan user melihat notifikasi)
                        //    (Dikurangi dari 30 menit menjadi 5 menit agar notifikasi lebih sering muncul)
                        const now = Date.now();
                        const fiveMinutesInMs = 5 * 60 * 1000; // 5 menit dalam milliseconds
                        
                        const newRejectedToShow = data.rejected_documents.filter(doc => {
                            const docKey = `rejected_doc_${doc.id}_shown_time`;
                            const shownTime = localStorage.getItem(docKey);
                            
                            if (!shownTime) {
                                // Belum pernah ditampilkan - tampilkan
                                localStorage.setItem(docKey, now.toString());
                                shownRejectedIds.add(doc.id);
                                console.log('✅ New rejected document to show:', doc.id, doc.nomor_agenda);
                                return true;
                            }
                            
                            const shownTimeNum = parseInt(shownTime);
                            const timeSinceShown = now - shownTimeNum; // Selisih waktu dalam milliseconds
                            
                            // Jika sudah ditampilkan lebih dari 5 menit yang lalu, tampilkan lagi
                            // Ini memastikan bahwa jika user kembali ke halaman, notifikasi akan muncul lagi
                            // FIX: Bandingkan timeSinceShown dengan fiveMinutesInMs (durasi), bukan dengan timestamp
                            if (timeSinceShown > fiveMinutesInMs) {
                                localStorage.setItem(docKey, now.toString());
                                const minutesAgo = Math.round(timeSinceShown / 1000 / 60);
                                console.log('🔄 Re-showing rejected document (shown >5min ago):', doc.id, doc.nomor_agenda, 'shown', minutesAgo, 'minutes ago');
                                return true;
                            }
                            
                            // Jika sudah ditampilkan kurang dari 5 menit yang lalu, skip
                            const minutesAgo = Math.round(timeSinceShown / 1000 / 60);
                            console.log('⏭️ Skipping recently shown document:', doc.id, 'shown', minutesAgo, 'minutes ago');
                            return false;
                        });

                        // Save shown notification IDs to localStorage
                        localStorage.setItem('ibua_shown_rejected_notifications', JSON.stringify(Array.from(shownRejectedIds)));

                        // Show notification untuk semua dokumen yang perlu ditampilkan
                        if (newRejectedToShow.length > 0) {
                            console.log('🔔 Showing notifications for', newRejectedToShow.length, 'rejected documents');
                            
                            // Show toast notification for each rejected document
                            newRejectedToShow.forEach((doc, index) => {
                                setTimeout(() => {
                                    const message = `${doc.nomor_agenda} - ${doc.uraian_spp}\nDitolak oleh: ${doc.rejected_by}\nAlasan: ${doc.rejection_reason}`;
                                    console.log('📢 Showing notification for document:', doc.id, doc.nomor_agenda);
                                    
                                    // Use global notification function if available
                                    if (typeof showGlobalToastNotification === 'function') {
                                        showGlobalToastNotification('error', 'Dokumen Ditolak!', message, doc.url, 'Lihat Dokumen');
                                    } else {
                                        // Fallback: use alert or console
                                        console.warn('⚠️ showGlobalToastNotification not available, using alert');
                                        alert(`Dokumen Ditolak!\n\n${message}`);
                                    }
                                }, index * 500); // Stagger notifications
                            });

                            // Play sound only once
                            if (typeof playNotificationSound === 'function') {
                                playNotificationSound();
                            }
                        } else {
                            console.log('ℹ️ No rejected documents to show (all recently shown)');
                        }
                    } else if (data.total_rejected > 0) {
                        // Ada dokumen yang di-reject tapi tidak dalam 24 jam terakhir
                        console.log('ℹ️ Total rejected documents:', data.total_rejected, 'but none in last 24 hours');
                    } else {
                        console.log('✅ No rejected documents found');
                    }
                } else {
                    console.warn('⚠️ Rejected documents check returned unsuccessful:', data.message);
                }
            } catch (error) {
                console.error('❌ Error checking rejected documents:', error);
            }
        }

        // Store interval ID so we can clear it if needed
        let rejectedDocumentsInterval = null;

        // Check immediately on page load (with small delay to ensure DOM is ready)
        setTimeout(function() {
            checkRejectedDocuments();
        }, 500);

        // Poll every 3 seconds for faster notification (rejected documents are critical)
        // Store interval ID globally so it persists across page navigations
        rejectedDocumentsInterval = setInterval(checkRejectedDocuments, 3000);
        
        // Store interval in window object to ensure it persists
        window.ibuaRejectedDocumentsInterval = rejectedDocumentsInterval;

        // Also check when page becomes visible (user switches back to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('👁️ Page became visible, checking rejected documents immediately');
                // Check immediately when page becomes visible
                checkRejectedDocuments();
            }
        });

        // Check when window gains focus
        window.addEventListener('focus', function() {
            console.log('🎯 Window gained focus, checking rejected documents immediately');
            checkRejectedDocuments();
        });
        
        // Also check when page is fully loaded (faster check)
        if (document.readyState === 'complete') {
            setTimeout(checkRejectedDocuments, 1000);
        } else {
            window.addEventListener('load', function() {
                setTimeout(checkRejectedDocuments, 1000);
            });
        }
        
        // Additional check after 2 seconds to catch any missed notifications
        setTimeout(checkRejectedDocuments, 2000);
    }

    // IbuB Rejected Documents Notification System
    function initIbuBRejectedNotifications() {
        console.log('initIbuBRejectedNotifications function called - Initializing IbuB rejected documents notification system');
        
        // Rejected documents notification polling
        let rejectedLastCheckTime = localStorage.getItem('ibub_rejected_last_check_time');
        if (!rejectedLastCheckTime) {
            rejectedLastCheckTime = new Date().toISOString();
            localStorage.setItem('ibub_rejected_last_check_time', rejectedLastCheckTime);
        }

        // Track shown rejected notifications to prevent duplicates
        const shownRejectedIds = new Set(JSON.parse(localStorage.getItem('ibub_shown_rejected_notifications') || '[]'));

        // Function to check for rejected documents
        async function checkRejectedDocuments() {
            try {
                console.log('Checking rejected documents for IbuB, last check:', rejectedLastCheckTime);
                
                const response = await fetch(`/ibub/check-rejected?last_check_time=${encodeURIComponent(rejectedLastCheckTime)}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    console.warn('IbuB rejected documents check failed:', response.status, response.statusText);
                    return;
                }

                const data = await response.json();

                console.log('IbuB rejected documents data:', data);

                if (data.success) {
                    // Update last check time
                    if (data.current_time) {
                        rejectedLastCheckTime = data.current_time;
                        localStorage.setItem('ibub_rejected_last_check_time', rejectedLastCheckTime);
                    }

                    // If there are rejected documents
                    if (data.rejected_documents_count > 0 && data.rejected_documents.length > 0) {
                        // Filter out already shown notifications
                        const newRejectedToShow = data.rejected_documents.filter(doc => {
                            const docKey = `ibub_rejected_doc_${doc.id}_shown`;
                            const shownTime = localStorage.getItem(docKey);
                            const now = Date.now();
                            
                            // If shown less than 1 minute ago, skip
                            if (shownTime && (now - parseInt(shownTime)) < 60000) {
                                return false;
                            }
                            
                            // Mark as shown with current timestamp
                            localStorage.setItem(docKey, now.toString());
                            shownRejectedIds.add(doc.id);
                            return true;
                        });

                        // Save shown notification IDs to localStorage
                        localStorage.setItem('ibub_shown_rejected_notifications', JSON.stringify(Array.from(shownRejectedIds)));

                        // Only show notification if we have new rejected documents
                        if (newRejectedToShow.length > 0) {
                            console.log('Showing rejected document notifications for IbuB:', newRejectedToShow.length);
                            
                            // Show toast notification for each rejected document
                            newRejectedToShow.forEach((doc, index) => {
                                setTimeout(() => {
                                    const message = `${doc.nomor_agenda} - ${doc.uraian_spp}\nDitolak oleh: ${doc.rejected_by}\nAlasan: ${doc.rejection_reason}`;
                                    console.log('Showing notification for rejected document:', doc.id, doc.nomor_agenda);
                                    showGlobalToastNotification('error', 'Dokumen Ditolak!', message, doc.url, 'Lihat Dokumen');
                                }, index * 500); // Stagger notifications by 500ms
                            });

                            // Play sound only once
                            playNotificationSound();
                        } else {
                            console.log('All rejected documents have already been shown recently');
                        }
                    } else {
                        console.log('No new rejected documents for IbuB');
                    }
                } else {
                    console.warn('IbuB rejected documents check returned unsuccessful:', data.message);
                }
            } catch (error) {
                console.error('Error checking rejected documents for IbuB:', error);
            }
        }

        // Check immediately on page load (with delay to ensure DOM is ready)
        setTimeout(() => {
            console.log('IbuB: Running initial rejected documents check');
            checkRejectedDocuments();
        }, 1500);

        // Poll every 30 seconds
        const pollInterval = setInterval(checkRejectedDocuments, 30000);
        console.log('IbuB: Rejected documents polling started, interval:', pollInterval);

        // Also check when page becomes visible (user switches back to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('IbuB: Page visible, checking rejected documents');
                setTimeout(checkRejectedDocuments, 500);
            }
        });

        // Check when window gains focus
        window.addEventListener('focus', function() {
            console.log('IbuB: Window focused, checking rejected documents');
            setTimeout(checkRejectedDocuments, 500);
        });

        // Also check when page is fully loaded
        if (document.readyState === 'complete') {
            setTimeout(() => {
                console.log('IbuB: Page complete, checking rejected documents');
                checkRejectedDocuments();
            }, 2000);
        } else {
            window.addEventListener('load', function() {
                setTimeout(() => {
                    console.log('IbuB: Page loaded, checking rejected documents');
                    checkRejectedDocuments();
                }, 2000);
            });
        }
    }
})();
</script>

</body>
</html>

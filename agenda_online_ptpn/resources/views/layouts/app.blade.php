<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Agenda Online PTPN' }}</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    html, body {
      margin: 0;
      padding: 0;
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
      margin-top: 0 !important;
      margin-left: 16%;
      padding-left: 30px;
      padding-top: 25px;
      /* width: 100%; */
    }
    
    header {
      margin-top: 0 !important;
      padding-top: 0 !important;
      margin: 0 !important;
      position: relative;
      top: 0;
      border-top: none;
    }
    
    body > header {
      margin-top: 0 !important;
      padding-top: 0 !important;
      top: 0;
    }
    
    html {
      margin: 0;
      padding: 0;
    }
    
    html body {
      margin: 0;
      padding: 0;
    }
    
    html body > header {
      margin-top: 0 !important;
      padding-top: 0 !important;
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

  <!-- Deadline Notification Styles -->
  <style>
    .deadline-notification-dropdown {
      position: fixed;
      top: 60px;
      right: 20px;
      width: 380px;
      max-height: 500px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.2);
      z-index: 10000;
      overflow: hidden;
      border: 1px solid rgba(8, 62, 64, 0.1);
    }

    .deadline-notification-dropdown .notification-header {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .deadline-notification-dropdown .notification-header h6 {
      margin: 0;
      font-weight: 600;
      font-size: 14px;
    }

    .deadline-notification-dropdown .notification-list {
      max-height: 400px;
      overflow-y: auto;
    }

    .deadline-notification-item {
      padding: 12px 20px;
      border-bottom: 1px solid rgba(8, 62, 64, 0.05);
      cursor: pointer;
      transition: background 0.2s;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .deadline-notification-item:hover {
      background: rgba(8, 62, 64, 0.05);
    }

    .deadline-notification-item.unread {
      background: rgba(8, 62, 64, 0.02);
      border-left: 4px solid;
    }

    .deadline-notification-item.unread.status-warning {
      border-left-color: #ffc107;
    }

    .deadline-notification-item.unread.status-danger {
      border-left-color: #dc3545;
    }

    .deadline-notification-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: white;
      flex-shrink: 0;
    }

    .deadline-notification-icon.status-warning {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    }

    .deadline-notification-icon.status-danger {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .deadline-notification-content {
      flex: 1;
      min-width: 0;
    }

    .deadline-notification-title {
      font-weight: 600;
      font-size: 13px;
      color: #083E40;
      margin-bottom: 4px;
    }

    .deadline-notification-message {
      font-size: 12px;
      color: #666;
      margin-bottom: 4px;
    }

    .deadline-notification-time {
      font-size: 11px;
      color: #999;
    }

    .deadline-notification-empty {
      padding: 40px 20px;
      text-align: center;
      color: #999;
    }

    .deadline-notification-empty i {
      font-size: 48px;
      margin-bottom: 12px;
      opacity: 0.3;
    }

    /* Table row colors for deadline status */
    .table-row-deadline-warning {
      background-color: #fff3cd !important;
      border-left: 4px solid #ffc107;
    }

    .table-row-deadline-danger {
      background-color: #f8d7da !important;
      border-left: 4px solid #dc3545;
    }

    .table-row-deadline-warning:hover {
      background-color: #ffe69c !important;
    }

    .table-row-deadline-danger:hover {
      background-color: #f5c6cb !important;
    }
  </style>

  <!-- Smart Autocomplete CSS -->
  <link href="{{ asset('css/smart-autocomplete.css') }}" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0;">
  <header style="margin: 0; padding: 0; margin-top: 0 !important;">
       <div class="topbar mb-0 mt-0" style="margin-top: 0 !important; padding-top: 25px;">
        <h5 class="mb-0 welcome-message">{{ $welcomeMessage ?? 'Selamat datang di Agenda Online PTPN' }}</h5>
        <div class="d-flex align-items-center">
          <div class="position-relative me-3" id="deadlineNotificationBell" style="cursor: pointer;">
            <i class="fa-solid fa-bell" style="font-size: 20px; color: #666;"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="deadlineNotificationBadge" style="display: none; font-size: 10px; min-width: 18px; height: 18px; padding: 2px 5px;">
              0
            </span>
          </div>
          <i class="fa-solid fa-user" style="font-size: 18px; color: #666;"></i>
      </div>
      
      <!-- Deadline Notification Dropdown -->
      <div class="deadline-notification-dropdown" id="deadlineNotificationDropdown" style="display: none;">
        <div class="notification-header">
          <h6>Notifikasi Deadline</h6>
          <button class="btn btn-sm btn-link text-white p-0" onclick="markAllDeadlineNotificationsAsRead()" id="markAllReadBtn" style="display: none;">
            Tandai semua dibaca
          </button>
        </div>
        <div class="notification-list" id="deadlineNotificationList">
          <div class="text-center p-3 text-muted">
            <i class="fa-solid fa-spinner fa-spin"></i> Memuat notifikasi...
          </div>
        </div>
      </div>
  </header>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center mb-4"><i class="fa-solid fa-calendar-days"></i> Agenda Online</h4>
    <hr>

        @php
            // Normalize module to lowercase for consistency
            $module = strtolower($module ?? 'ibua');

            $dashboardUrl = match($module) {
                'ibua' => '/dashboard',
                'ibub' => '/dashboardB',
                'pembayaran' => '/dashboardPembayaran',
                'akutansi' => '/dashboardAkutansi',
                'perpajakan' => '/dashboardPerpajakan',
                default => '/dashboard'
            };
            $dokumenUrl = match($module) {
                'ibua' => '/dokumens',
                'ibub' => '/dokumensB',
                'pembayaran' => '/dokumensPembayaran',
                'akutansi' => '/dokumensAkutansi',
                'perpajakan' => '/dokumensPerpajakan',
                default => '/dokumens'
            };
            $pengembalianUrl = match($module) {
                'ibub' => '/pengembalian-dokumensB',
                'pembayaran' => '/rekapan-keterlambatan',
                'akutansi' => '/pengembalian-dokumensAkutansi',
                'perpajakan' => '/pengembalian-dokumensPerpajakan',
                default => '/pengembalian-dokumens'
            };
            $tambahDokumenUrl = match($module) {
                'ibua' => '/dokumens/create',
                default => null
            };
            $editDokumenUrl = match($module) {
                'pembayaran' => '/dokumensPembayaran', // This will be handled by individual edit routes
                'akutansi' => '/dokumensAkutansi',
                'perpajakan' => '/dokumensPerpajakan',
                'ibub' => '/dokumensB',
                default => null
            };
            $diagramUrl = match($module) {
                'ibua' => '/diagram',
                'ibub' => '/diagramB',
                'pembayaran' => '/diagramPembayaran',
                'akutansi' => '/diagramAkutansi',
                'perpajakan' => '/diagramPerpajakan',
                default => '/diagram'
            };
        @endphp

        <a href="{{ url($dashboardUrl) }}" class="{{ $menuDashboard ?? '' }}"><i class="fa-solid fa-house"></i> Home</a>

    <!-- Universal Daftar Masuk Dokumen - Untuk semua user kecuali IbuA -->
    @php
        $currentUserRole = 'ibua'; // Default (lowercase)
        if (auth()->check()) {
            $user = auth()->user();
            // Prioritize role field over name field
            if (isset($user->role)) {
                $currentUserRole = strtolower($user->role);
            } elseif (isset($user->name)) {
                $currentUserRole = strtolower($user->name);
            }
        }
    @endphp

    @if($currentUserRole !== 'ibua')
        <a href="{{ url('/daftar-masuk-dokumen') }}" class="{{ request()->routeIs('universal.approval.*') ? 'active' : '' }}">
            <i class="fa-solid fa-inbox"></i>
            Daftar Masuk Dokumen
            <span class="notification-badge" id="universal-notification-badge" style="display: none;">0</span>
        </a>
    @endif

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
          @else
            Dokumen
          @endif
        </span>
        <i class="fa-solid fa-chevron-down dropdown-icon"></i>
      </div>
      <div class="dropdown-content {{ isset($menuDokumen) && $menuDokumen ? 'show' : '' }}" id="dokumenContent">
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
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
    </form>
    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
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

    <footer>
      &copy; 2025 Agenda Online - All Rights Reserved
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Pusher & Laravel Echo -->
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

  <!-- Custom JS for Dropdown -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const dropdownToggle = document.getElementById('dokumenDropdown');
      const dropdownContent = document.getElementById('dokumenContent');
      const dropdownIcon = dropdownToggle ? dropdownToggle.querySelector('.dropdown-icon') : null;

      // Initialize dropdown state based on server-side class
      if (dropdownContent && dropdownContent.classList.contains('show')) {
        if (dropdownIcon) {
          dropdownIcon.classList.add('rotate');
        }
        if (dropdownToggle) {
          dropdownToggle.classList.add('active');
        }
      }

      if (dropdownToggle) {
        dropdownToggle.addEventListener('click', function() {
          // Toggle dropdown content
          if (dropdownContent) {
            dropdownContent.classList.toggle('show');
          }

          // Rotate icon
          if (dropdownIcon) {
            dropdownIcon.classList.toggle('rotate');
          }

          // Toggle active state
          dropdownToggle.classList.toggle('active');
        });
      }
    });
  </script>

  <!-- Auto-Refresh System for IbuB -->
  <script>
    (function() {
      'use strict';

      // Get user role from authenticated user
      let currentUserRole = 'ibua'; // Default (lowercase)
      @php
          $tempUserRole = 'ibua';
          if (auth()->check()) {
              $user = auth()->user();
              // Prioritize role field over name field
              if (isset($user->role)) {
                  $tempUserRole = strtolower($user->role);
              } elseif (isset($user->name)) {
                  $tempUserRole = strtolower($user->name);
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
                  <small style="opacity: 0.8;">Dikembalikan dari IbuB - ${doc.returned_at}</small>
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
                    Dokumen Baru untuk Perpajakan
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
                  <small style="opacity: 0.8;">Dokumen baru dari IbuB - ${doc.sent_at}</small>
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
                    Dokumen Baru untuk Akutansi
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

            // Auto remove
            setTimeout(() => {
              removeNotification(notificationId);
            }, NOTIFICATION_DURATION);

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
          } else {
            endpoint = `/dokumens/check-returned-updates?last_checked=${Math.floor(lastChecked / 1000)}`;
          }

          console.log('Checking updates from:', endpoint);
          console.log('Current module check:', { isIbuB, isIbuA, isPerpajakan, isAkutansi });

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

      // Deadline Notification System
      let deadlineNotificationInterval;
      let deadlineNotifications = [];
      
      function loadDeadlineNotifications() {
        const module = '{{ $module ?? "" }}';
        if (!module || !['akutansi', 'perpajakan', 'ibu_a', 'IbuA'].includes(module)) {
          return;
        }

        fetch(`/api/deadline-notifications?module=${module}`, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          deadlineNotifications = data.notifications || [];
          updateDeadlineNotificationBadge(data.count || 0);
          updateDeadlineNotificationList(deadlineNotifications);
          updateTableRowColors(deadlineNotifications);
        })
        .catch(error => {
          console.error('Error loading deadline notifications:', error);
        });
      }

      function updateDeadlineNotificationBadge(count) {
        const badge = document.getElementById('deadlineNotificationBadge');
        if (badge) {
          if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
            badge.classList.add('animate__animated', 'animate__pulse');
          } else {
            badge.style.display = 'none';
          }
        }
      }

      function updateDeadlineNotificationList(notifications) {
        const list = document.getElementById('deadlineNotificationList');
        if (!list) return;

        if (notifications.length === 0) {
          list.innerHTML = `
            <div class="deadline-notification-empty">
              <i class="fa-solid fa-bell-slash"></i>
              <p>Tidak ada notifikasi deadline</p>
            </div>
          `;
          return;
        }

        list.innerHTML = notifications.map(notif => `
          <div class="deadline-notification-item unread status-${notif.status}" onclick="markDeadlineNotificationAsRead(${notif.id}, ${notif.dokumen_id})">
            <div class="deadline-notification-icon status-${notif.status}">
              <i class="fa-solid fa-${notif.status === 'danger' ? 'exclamation-triangle' : 'clock'}"></i>
            </div>
            <div class="deadline-notification-content">
              <div class="deadline-notification-title">Dokumen ${notif.nomor_agenda}</div>
              <div class="deadline-notification-message">
                Terlambat ${notif.days_overdue} hari dari deadline
              </div>
              <div class="deadline-notification-time">
                Deadline: ${notif.deadline_at}
              </div>
            </div>
          </div>
        `).join('');
      }

      function updateTableRowColors(notifications) {
        // Remove all deadline row colors first
        document.querySelectorAll('.table-row-deadline-warning, .table-row-deadline-danger').forEach(row => {
          row.classList.remove('table-row-deadline-warning', 'table-row-deadline-danger');
        });
        
        // Apply colors based on notifications
        notifications.forEach(notif => {
          const rows = document.querySelectorAll(`tr[data-dokumen-id="${notif.dokumen_id}"]`);
          rows.forEach(row => {
            row.classList.remove('table-row-deadline-warning', 'table-row-deadline-danger');
            if (notif.status === 'warning') {
              row.classList.add('table-row-deadline-warning');
            } else if (notif.status === 'danger') {
              row.classList.add('table-row-deadline-danger');
            }
          });
        });
      }

      function markDeadlineNotificationAsRead(id, dokumenId) {
        fetch(`/api/deadline-notifications/${id}/read`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
          }
        })
        .then(() => {
          loadDeadlineNotifications();
          // Optionally redirect to document
          // window.location.href = `/dokumens/${dokumenId}`;
        })
        .catch(error => {
          console.error('Error marking notification as read:', error);
        });
      }

      function markAllDeadlineNotificationsAsRead() {
        const module = '{{ $module ?? "" }}';
        fetch('/api/deadline-notifications/read-all', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ module: module })
        })
        .then(() => {
          loadDeadlineNotifications();
        })
        .catch(error => {
          console.error('Error marking all notifications as read:', error);
        });
      }

      // Toggle notification dropdown
      document.addEventListener('DOMContentLoaded', function() {
        const bell = document.getElementById('deadlineNotificationBell');
        const dropdown = document.getElementById('deadlineNotificationDropdown');
        const markAllBtn = document.getElementById('markAllReadBtn');

        if (bell && dropdown) {
          bell.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            if (dropdown.style.display === 'block') {
              loadDeadlineNotifications();
            }
          });

          // Close dropdown when clicking outside
          document.addEventListener('click', function(e) {
            if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
              dropdown.style.display = 'none';
            }
          });

          // Load notifications on page load
          loadDeadlineNotifications();

          // Refresh notifications every 6 hours (21600000 ms) and also every 5 minutes for real-time updates
          deadlineNotificationInterval = setInterval(loadDeadlineNotifications, 300000); // 5 minutes
        }
      });
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
</body>
</html>

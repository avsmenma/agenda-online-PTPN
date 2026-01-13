<!DOCTYPE html>
<html lang="id" id="html-root">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'PTPN Agenda Online' }}</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <!-- Flatpickr Indonesian Locale -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #F8FAFC;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Dark Mode Styles */
    .dark body {
      background-color: #0f172a;
      /* slate-900 */
      color: #f1f5f9;
      /* slate-100 */
    }

    /* Sidebar - Expandable with Floating Drawer Effect */
    .sidebar {
      width: 72px;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: #FFFFFF;
      color: #01545A;
      font-weight: 600;
      padding-top: 20px;
      display: flex;
      flex-direction: column;
      transition: width 0.25s ease, background-color 0.3s ease, border-color 0.3s ease;
      overflow: hidden;
      z-index: 1000;
      border-right: 1px solid #E2E8F0;
      box-shadow: none;
    }

    /* Expanded state on hover - Simple extend without animations */
    .sidebar:hover {
      width: 240px;
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
      z-index: 1001;
    }

    .dark .sidebar {
      background: #1e293b;
      /* slate-800 */
      border-right-color: #334155;
      /* slate-700 */
      color: #cbd5e1;
      /* slate-300 */
    }

    /* Expanded state on hover - Simple extend without animations */
    .sidebar:hover {
      width: 240px;
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
      z-index: 1001;
    }

    .sidebar a {
      color: #666666;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 12px 16px;
      border-radius: 8px;
      margin-left: 8px;
      margin-right: 8px;
      margin-top: 8px;
      transition: none;
      white-space: nowrap;
      overflow: hidden;
      position: relative;
    }

    .dark .sidebar a {
      color: #cbd5e1;
      /* slate-300 */
    }

    /* Force hide text nodes when collapsed */
    .sidebar:not(:hover) a {
      font-size: 0;
      line-height: 0;
      color: transparent;
      padding: 12px 0;
      justify-content: center;
    }

    /* Keep icon visible when collapsed - consistent size */
    .sidebar:not(:hover) a i {
      font-size: 18px;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #666666;
      width: 20px;
      height: 20px;
      flex-shrink: 0;
    }

    .dark .sidebar:not(:hover) a i {
      color: #cbd5e1;
      /* slate-300 */
    }

    /* Expanded state - restore text visibility, keep icon size consistent */
    .sidebar:hover a {
      justify-content: flex-start;
      padding: 12px 16px;
      margin-left: 12px;
      margin-right: 12px;
      font-size: 14px;
      line-height: 1.5;
      color: #666666;
    }

    .sidebar:hover a i {
      font-size: 18px;
      margin-right: 12px;
      width: 20px;
      height: 20px;
      flex-shrink: 0;
    }

    /* Badge handling - simple show/hide */
    .sidebar:not(:hover) a .badge.right {
      display: none;
    }

    .sidebar:hover a .badge.right {
      display: inline-block;
      margin-left: 8px;
      padding: 2px 8px;
      border-radius: 12px;
      background: #F1F5F9;
      color: #475569;
      font-weight: 600;
      font-size: 11px;
      border: none;
    }

    .sidebar:hover a:hover .badge.right,
    .sidebar:hover a.active .badge.right {
      background: #0369A1;
      color: #ffffff;
    }

    /* Collapsed state hover */
    .sidebar:not(:hover) a:hover {
      background-color: #F1F5F9;
    }

    .sidebar:not(:hover) a:hover i {
      color: #01545A;
    }

    /* Active menu item - Collapsed state */
    .sidebar:not(:hover) a.active {
      background-color: #E0F2FE;
    }

    .sidebar:not(:hover) a.active i {
      color: #0369A1;
    }

    /* Expanded state hover */
    .sidebar:hover a:hover {
      background-color: #F1F5F9;
      color: #01545A;
    }

    /* Active menu item - Expanded state */
    .sidebar:hover a.active {
      background-color: #E0F2FE;
      color: #0369A1;
      font-weight: 600;
    }

    /* Active state for menu trigger (when secondary sidebar is open) */
    .sidebar-menu-trigger.active,
    .sidebar-menu-trigger[aria-expanded="true"] {
      background-color: #E0F2FE !important;
      color: #0369A1 !important;
      font-weight: 600;
    }

    .sidebar:not(:hover) .sidebar-menu-trigger.active i,
    .sidebar:not(:hover) .sidebar-menu-trigger[aria-expanded="true"] i {
      color: #0369A1;
    }

    .sidebar:hover .sidebar-menu-trigger.active,
    .sidebar:hover .sidebar-menu-trigger[aria-expanded="true"] {
      background-color: #E0F2FE;
      color: #0369A1;
    }

    /* Cursor pointer untuk menu trigger */
    .sidebar-menu-trigger {
      cursor: pointer;
      user-select: none;
    }

    .sidebar-menu-trigger:hover {
      background-color: #F1F5F9;
    }

    /* .sidebar .dropdown-menu-custom {
      margin-left: 30px;
      margin-top: 20px;
    } */

    .sidebar .dropdown-toggle {
      color: #666666;
      text-decoration: none;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 12px 16px;
      border-radius: 8px;
      margin-left: 8px;
      margin-right: 8px;
      margin-top: 8px;
      cursor: pointer;
      transition: none;
      white-space: nowrap;
      overflow: hidden;
      position: relative;
      font-size: 14px;
    }

    /* Collapsed state dropdown */
    .sidebar:not(:hover) .dropdown-toggle {
      font-size: 0;
      color: transparent;
      padding: 12px 0;
      justify-content: center;
    }

    .sidebar:not(:hover) .dropdown-toggle i {
      font-size: 18px;
      color: #666666;
    }

    /* Expanded state dropdown */
    .sidebar:hover .dropdown-toggle {
      justify-content: space-between;
      padding: 12px 16px;
      margin-left: 12px;
      margin-right: 12px;
      font-size: 14px;
      color: #666666;
    }

    /* Hide dropdown text when collapsed */
    .sidebar:not(:hover) .dropdown-toggle {
      font-size: 0;
    }

    .sidebar:not(:hover) .dropdown-toggle i {
      font-size: 18px;
      color: #666666;
    }

    /* Show dropdown text when expanded */
    .sidebar:hover .dropdown-toggle {
      font-size: 14px;
    }

    /* Hide dropdown chevron icon when collapsed */
    .sidebar:not(:hover) .dropdown-toggle .dropdown-icon {
      display: none;
    }

    /* Show dropdown chevron icon when expanded */
    .sidebar:hover .dropdown-toggle .dropdown-icon {
      display: inline-block;
      font-size: 12px;
    }

    /* Collapsed state dropdown hover */
    .sidebar:not(:hover) .dropdown-toggle:hover {
      background-color: #F1F5F9;
    }

    .sidebar:not(:hover) .dropdown-toggle:hover i {
      color: #01545A;
    }

    /* Collapsed state dropdown active */
    .sidebar:not(:hover) .dropdown-toggle.active {
      background-color: #E0F2FE;
    }

    .sidebar:not(:hover) .dropdown-toggle.active i {
      color: #0369A1;
    }

    /* Expanded state dropdown hover */
    .sidebar:hover .dropdown-toggle:hover {
      background-color: #F1F5F9;
      color: #01545A;
    }

    /* Expanded state dropdown active */
    .sidebar:hover .dropdown-toggle.active {
      background-color: #E0F2FE;
      color: #0369A1;
      font-weight: 600;
    }

    .sidebar .dropdown-content {
      display: none;
      margin-left: 20px;
      margin-top: 10px;
      opacity: 0;
      max-height: 0;
      overflow: hidden;
      transition: opacity 0.3s ease, max-height 0.3s ease;
    }

    .sidebar .dropdown-content.show {
      display: block;
      opacity: 1;
      max-height: 500px;
    }

    /* Hide dropdown content text when sidebar is collapsed */
    .sidebar:not(:hover) .dropdown-content.show {
      opacity: 0;
    }

    /* Show dropdown content when sidebar is expanded */
    .sidebar:hover .dropdown-content.show {
      opacity: 1;
    }

    .sidebar .dropdown-content a {
      margin-left: 20px;
      margin-top: 5px;
      padding: 10px 20px;
      font-size: 14px;
      border-radius: 20px 0 0 20px;
    }

    .sidebar .dropdown-icon {
      transition: transform 0.2s ease;
    }

    .sidebar .dropdown-icon.rotate {
      transform: rotate(180deg);
    }

    .sidebar hr.sidebar-divider {
      margin: 0 1rem 1rem;
    }

    /* Sidebar title - show only icon when collapsed */
    .sidebar h4 {
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 16px 0;
      padding: 0 16px;
      color: #01545A;
      font-size: 16px;
      font-weight: 600;
      transition: none;
      white-space: nowrap;
      overflow: hidden;
    }

    /* Icon in title - consistent size */
    .sidebar h4 i {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: #01545A;
      flex-shrink: 0;
      width: 20px;
      height: 20px;
    }

    /* Hide title text when collapsed */
    .sidebar:not(:hover) h4 {
      justify-content: center;
      font-size: 0;
      line-height: 0;
      padding: 0;
    }

    .sidebar:not(:hover) h4 i {
      font-size: 18px;
      line-height: 1;
    }

    /* Show full title when expanded */
    .sidebar:hover h4 {
      justify-content: flex-start;
      text-align: left;
      padding: 0 16px;
      font-size: 16px;
      line-height: 1.5;
    }

    .sidebar:hover h4 i {
      margin-right: 8px;
      font-size: 18px;
    }

    /* Hide hr completely when collapsed */
    .sidebar:not(:hover) hr {
      display: none;
    }

    /* Show hr when expanded */
    .sidebar:hover hr {
      display: block;
      opacity: 1;
      border-color: #E2E8F0;
      margin: 0 12px 16px;
    }

    /* Logout link styling */
    .sidebar .logout-link {
      margin-top: auto;
      margin-bottom: 20px;
    }

    .sidebar:not(:hover) .logout-link {
      margin-left: 0;
      margin-right: 0;
    }

    .sidebar:hover .logout-link {
      margin-left: 12px;
      margin-right: 12px;
    }

    /* Badge positioning */
    .sidebar a .badge.right {
      opacity: 0;
      width: 0;
      overflow: hidden;
      transition: opacity 0.2s ease, width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar:hover a .badge.right {
      opacity: 1;
      width: auto;
    }

    .welcome-message {
      color: #01545A;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: color 0.3s ease;
    }

    .dark .welcome-message {
      color: #cbd5e1;
      /* slate-300 */
    }

    .welcome-message::before {
      content: "👋";
      font-size: 1.2em;
    }

    /* Secondary Sidebar (Submenu Panel) - Mekari Style */
    .secondary-sidebar {
      position: fixed;
      left: 72px;
      top: 0;
      width: 240px;
      height: 100vh;
      background: #FFFFFF;
      border-right: 1px solid #E2E8F0;
      z-index: 5;
      /* Lower than topbar (z-index: 10) */
      display: none;
      flex-direction: column;
      transition: transform 0.3s ease, opacity 0.3s ease;
      box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
    }

    .secondary-sidebar.active {
      display: flex;
    }

    .dark .secondary-sidebar {
      background: #1e293b;
      /* slate-800 */
      border-right-color: #334155;
      /* slate-700 */
    }

    .secondary-sidebar-header {
      padding: 20px 16px;
      border-bottom: 1px solid #E2E8F0;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #64748B;
      background: #F8FAFC;
    }

    .dark .secondary-sidebar-header {
      background: #0f172a;
      /* slate-900 */
      border-bottom-color: #334155;
      /* slate-700 */
      color: #94a3b8;
      /* slate-400 */
    }

    .secondary-sidebar-content {
      flex: 1;
      padding: 12px 0;
      overflow-y: auto;
    }

    .secondary-sidebar a {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: #475569;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.2s ease;
      border-left: 3px solid transparent;
      position: relative;
      margin: 2px 8px;
      border-radius: 8px;
    }

    .secondary-sidebar a i {
      width: 18px;
      text-align: center;
      margin-right: 12px;
      font-size: 14px;
      flex-shrink: 0;
    }

    .secondary-sidebar a:hover {
      background: #F1F5F9;
      color: #0369A1;
    }

    .secondary-sidebar a.active {
      background: linear-gradient(135deg, #E0F2FE 0%, #DBEAFE 100%);
      color: #0369A1;
      border-left-color: #0369A1;
      border-left-width: 4px;
      font-weight: 600;
      box-shadow: 0 2px 4px rgba(3, 105, 161, 0.1);
    }

    .secondary-sidebar a.active i {
      color: #0369A1;
    }

    .dark .secondary-sidebar a {
      color: #cbd5e1;
      /* slate-300 */
    }

    .dark .secondary-sidebar a:hover {
      background: #0f172a;
      /* slate-900 */
      color: #60a5fa;
      /* blue-400 */
    }

    .dark .secondary-sidebar a.active {
      background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 100%);
      /* blue-900 gradient */
      color: #60a5fa;
      /* blue-400 */
      border-left-color: #60a5fa;
      border-left-width: 4px;
      font-weight: 600;
      box-shadow: 0 2px 4px rgba(96, 165, 250, 0.2);
    }

    .dark .secondary-sidebar a.active i {
      color: #60a5fa;
      /* blue-400 */
    }

    .content {
      margin-left: 72px;
      padding: 20px;
      position: relative;
      z-index: auto;
      background-color: #F8FAFC;
      min-height: 100vh;
      transition: margin-left 0.3s ease, background-color 0.3s ease;
    }

    .content.with-secondary-sidebar {
      margin-left: 312px;
      /* 72px (primary) + 240px (secondary) */
    }

    .dark .content {
      background-color: #0f172a;
      /* slate-900 */
    }

    /* Responsive: Hide secondary sidebar on mobile */
    @media (max-width: 768px) {
      .secondary-sidebar {
        transform: translateX(-100%);
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
      }

      .secondary-sidebar.active {
        transform: translateX(0);
      }

      .content.with-secondary-sidebar {
        margin-left: 72px;
        /* Only primary sidebar on mobile */
      }
    }

    /* Smooth transition for secondary sidebar */
    @media (min-width: 769px) {
      .secondary-sidebar {
        transform: translateX(0);
      }
    }

    /* Dark Mode Toggle Button */
    .theme-toggle-btn {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      border: 1px solid #E2E8F0;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-right: 12px;
      color: #666;
    }

    .theme-toggle-btn:hover {
      background: #f1f5f9;
      border-color: #cbd5e1;
      transform: scale(1.05);
    }

    .dark .theme-toggle-btn {
      background: #1e293b;
      border-color: #334155;
      color: #fbbf24;
      /* amber-400 */
    }

    .dark .theme-toggle-btn:hover {
      background: #334155;
      border-color: #475569;
    }

    .theme-toggle-icon {
      font-size: 18px;
      transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .theme-toggle-icon.sun {
      display: none;
    }

    .dark .theme-toggle-icon.moon {
      display: none;
    }

    .dark .theme-toggle-icon.sun {
      display: block;
      color: #fbbf24;
      /* amber-400 */
    }

    /* Dark mode icon colors in topbar */
    .dark .topbar i {
      color: #cbd5e1 !important;
      /* slate-300 */
    }

    /* Profile Dropdown Styles */
    .profile-dropdown-container {
      position: relative;
    }

    .profile-icon {
      padding: 8px;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .profile-icon:hover {
      background-color: #f1f5f9;
    }

    .dark .profile-icon:hover {
      background-color: #334155;
    }

    .profile-dropdown-menu {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      background: white;
      border: 1px solid #E2E8F0;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      min-width: 200px;
      z-index: 1050;
      overflow: hidden;
      padding: 4px 0;
    }

    .dark .profile-dropdown-menu {
      background: #1e293b;
      border-color: #334155;
    }

    .profile-dropdown-item {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      color: #374151;
      text-decoration: none;
      font-size: 14px;
      transition: background-color 0.2s ease, color 0.2s ease;
    }

    .dark .profile-dropdown-item {
      color: #cbd5e1;
    }

    .profile-dropdown-item:hover {
      background-color: #E8F5E9;
      color: #01545A;
      font-weight: 500;
    }

    .dark .profile-dropdown-item:hover {
      background-color: rgba(1, 84, 90, 0.2);
      color: #4ade80;
    }

    .profile-dropdown-item i {
      width: 18px;
      text-align: center;
    }

    .profile-dropdown-divider {
      height: 1px;
      background-color: #E2E8F0;
      margin: 4px 0;
    }

    .dark .profile-dropdown-divider {
      background-color: #334155;
    }

    .topbar {
      background-color: white;
      padding: 25px 40px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 5px;
      margin-left: 72px;
      padding-left: 30px;
      border-bottom: 1px solid #E2E8F0;
      transition: background-color 0.3s ease, border-color 0.3s ease, margin-left 0.3s ease;
      position: relative;
      z-index: 10;
    }

    .topbar.with-secondary-sidebar {
      margin-left: 312px;
      /* 72px (primary) + 240px (secondary) */
    }

    .dark .topbar {
      background-color: #1e293b;
      /* slate-800 */
      border-bottom-color: #334155;
      /* slate-700 */
    }

    .card-stat {
      border-radius: 12px;
      padding: 20px;
      color: white;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
      margin: 10px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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

    /* Notification styles for approved documents */
    .notification-approved {
      border-left: 4px solid #ffc107 !important;
      background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
    }

    .notification-approved .notification-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification-header-approved {
      background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
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

      0%,
      100% {
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

      0%,
      100% {
        background-color: transparent;
      }

      50% {
        background-color: rgba(8, 62, 64, 0.1);
      }
    }

    @keyframes highlightReturnedPulse {

      0%,
      100% {
        background-color: transparent;
      }

      50% {
        background-color: rgba(220, 53, 69, 0.1);
      }
    }

    /* Global UX Helper: Text Selection Styles */
    .select-text,
    .cursor-text {
      cursor: text;
      user-select: text;
      -webkit-user-select: text;
      -moz-user-select: text;
      -ms-user-select: text;
    }

    .select-text::selection,
    .cursor-text::selection {
      background-color: rgba(8, 62, 64, 0.2);
      color: inherit;
    }

    /* Prevent text selection on clickable containers */
    .clickable-row,
    .clickable-card,
    [onclick*="handleItemClick"],
    [onclick*="handleCardClick"] {
      user-select: none;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
    }

    /* Allow text selection on specific elements inside clickable containers */
    .clickable-row .select-text,
    .clickable-card .select-text,
    [onclick*="handleItemClick"] .select-text,
    [onclick*="handleCardClick"] .select-text {
      user-select: text;
      -webkit-user-select: text;
      -moz-user-select: text;
      -ms-user-select: text;
      cursor: text;
    }
  </style>

  <!-- Smart Autocomplete CSS -->
  <link href="{{ asset('css/smart-autocomplete.css') }}" rel="stylesheet">

  <!-- Stack for additional styles from views -->
  @stack('styles')

  <!-- Dark Mode Toggle Script - Run Immediately -->
  <script>
    (function () {
      // Check localStorage for saved theme preference
      const savedTheme = localStorage.getItem('theme');
      const htmlElement = document.documentElement;

      // Apply saved theme or default to light
      if (savedTheme === 'dark') {
        htmlElement.classList.add('dark');
      } else {
        htmlElement.classList.remove('dark');
        // Ensure light mode if no preference saved
        if (!savedTheme) {
          localStorage.setItem('theme', 'light');
        }
      }

      // Theme toggle function
      function toggleTheme() {
        htmlElement.classList.toggle('dark');
        const isDark = htmlElement.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
      }

      // Attach event listener to toggle button when DOM is ready
      function initThemeToggle() {
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
          themeToggle.addEventListener('click', toggleTheme);
        }
      }

      // Run immediately if DOM is already loaded
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
      } else {
        initThemeToggle();
      }
    })();
  </script>
</head>

<body>
  @php
    // Pre-calculate shouldShowSecondarySidebar for header
    // Check if user is owner
    $isOwner = auth()->check() && (auth()->user()->role === 'owner' || auth()->user()->role === 'Owner' || auth()->user()->role === 'OWNER' || auth()->user()->role === 'Admin' || auth()->user()->role === 'admin');

    $hasSubmenu = isset($menuDokumen) && !empty($menuDokumen);
    $isSubmenuPageForHeader = false;

    // Check if owner is on rekapan keterlambatan page
    $isOwnerRekapanKeterlambatan = $isOwner && (request()->is('*rekapan-keterlambatan*') ||
      request()->routeIs('owner.rekapan-keterlambatan*'));

    if ($isOwnerRekapanKeterlambatan) {
      $isSubmenuPageForHeader = true;
    } elseif (isset($module)) {
      if ($module === 'pembayaran') {
        $isSubmenuPageForHeader = request()->routeIs('dokumensPembayaran.*') ||
          request()->routeIs('pembayaran.*') ||
          request()->routeIs('rekapanKeterlambatan.*') ||
          request()->routeIs('csv.import.*') ||
          request()->is('*dokumensPembayaran*') ||
          request()->is('*rekapan-pembayaran*') ||
          request()->is('*rekapan-keterlambatan*') ||
          request()->is('*csv-import*') ||
          request()->is('*pengembalian-dokumensPembayaran*');
      } elseif ($module === 'akutansi') {
        $isSubmenuPageForHeader = request()->routeIs('dokumensAkutansi.*') ||
          request()->routeIs('akutansi.*') ||
          request()->is('*dokumensAkutansi*') ||
          request()->is('*rekapan-akutansi*');
      } elseif ($module === 'perpajakan') {
        $isSubmenuPageForHeader = request()->routeIs('dokumensPerpajakan.*') ||
          request()->routeIs('perpajakan.*') ||
          request()->is('*dokumensPerpajakan*') ||
          request()->is('*rekapan-perpajakan*');
      } elseif ($module === 'ibub') {
        $isSubmenuPageForHeader = request()->routeIs('dokumensB.*') ||
          request()->routeIs('ibub.*') ||
          request()->is('*dokumensB*') ||
          request()->is('*rekapan-ibuB*');
      } else {
        $isSubmenuPageForHeader = request()->is('*dokumens*') ||
          request()->is('*rekapan*') ||
          request()->is('*pengembalian*');
      }
    }
    $shouldShowSecondarySidebarForHeader = $hasSubmenu || $isSubmenuPageForHeader || $isOwnerRekapanKeterlambatan;
  @endphp
  <header>
    <div class="topbar mb-0 mt-0 {{ $shouldShowSecondarySidebarForHeader ? 'with-secondary-sidebar' : '' }}">
      @if(($module ?? '') !== 'owner')
        <h5 class="mb-0 welcome-message">{{ $welcomeMessage ?? 'Selamat datang di Agenda Online PTPN' }}</h5>
      @else
        {{-- Spacer to push icons to the right for owner pages --}}
        <div class="flex-grow-1"></div>
      @endif
      <div class="d-flex align-items-center ms-auto">
        <!-- Dark Mode Toggle Button -->
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode">
          <i class="fas fa-moon theme-toggle-icon moon"></i>
          <i class="fas fa-sun theme-toggle-icon sun"></i>
        </button>
        <i class="fa-solid fa-bell me-3" style="font-size: 20px; color: #666; cursor: pointer;"></i>

        <!-- Profile Dropdown -->
        <div class="profile-dropdown-container" style="position: relative;">
          <i class="fa-solid fa-user profile-icon" id="profileDropdownToggle"
            style="font-size: 18px; color: #666; cursor: pointer; position: relative;">
          </i>
          <div class="profile-dropdown-menu" id="profileDropdownMenu" style="display: none;">
            <a href="{{ route('profile.account') }}" class="profile-dropdown-item">
              <i class="fa-solid fa-user-circle me-2"></i>
              Akun
            </a>
            <a href="{{ route('2fa.setup') }}" class="profile-dropdown-item">
              <i class="fa-solid fa-shield-alt me-2"></i>
              Keamanan 2FA
            </a>
            <div class="profile-dropdown-divider"></div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
              @csrf
              <button type="submit" class="profile-dropdown-item"
                style="width: 100%; text-align: left; border: none; background: none; padding: 8px 16px; cursor: pointer;">
                <i class="fa-solid fa-sign-out-alt me-2"></i>
                Logout
              </button>
            </form>
          </div>
        </div>
      </div>
  </header>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center mb-4"><i class="fa-solid fa-calendar-days"></i> Agenda Online</h4>
    <hr>

    @php
      // Normalize module to lowercase untuk konsistensi
      // Note: $isOwner is already defined at the top of the body section
      $module = strtolower($module ?? 'ibua');

      $dashboardUrl = match ($module) {
        'ibua', 'ibua' => '/dashboard',
        'ibub', 'ibub' => '/dashboardB',
        'pembayaran' => '/dashboardPembayaran',
        'akutansi' => '/dashboardAkutansi',
        'perpajakan' => '/dashboardPerpajakan',
        default => '/dashboard'
      };
      $dokumenUrl = match ($module) {
        'ibua', 'ibua' => '/dokumens',
        'ibub', 'ibub' => '/dokumensB',
        'pembayaran' => '/dokumensPembayaran',
        'akutansi' => '/dokumensAkutansi',
        'perpajakan' => '/dokumensPerpajakan',
        default => '/dokumens'
      };
      $pengembalianUrl = match ($module) {
        'ibub', 'ibub' => '/pengembalian-dokumensB',
        'pembayaran' => '/rekapan-keterlambatan',
        'akutansi' => '/pengembalian-dokumensAkutansi',
        'perpajakan' => '/pengembalian-dokumensPerpajakan',
        default => '/pengembalian-dokumens'
      };
      $tambahDokumenUrl = match ($module) {
        'ibua', 'ibua' => '/dokumens/create',
        default => null
      };
      $editDokumenUrl = match ($module) {
        'pembayaran' => '/dokumensPembayaran', // This will be handled by individual edit routes
        'akutansi' => '/dokumensAkutansi',
        'perpajakan' => '/dokumensPerpajakan',
        'ibub', 'ibub' => '/dokumensB',
        default => null
      };
    @endphp

    @php
      // Check if user is a bagian user - defined here to be available throughout sidebar
      $isBagianUser = false;
      if (auth()->check()) {
        $userRoleLower = strtolower(auth()->user()->role ?? '');
        $isBagianUser = str_starts_with($userRoleLower, 'bagian_');
      }
    @endphp

    @if($isOwner)
      <!-- Owner Menu - Clean and Simple -->
      <div style="flex: 1; display: flex; flex-direction: column;">
        <a href="{{ url('/owner/home') }}" class="{{ $menuHome ?? '' }}">
          <i class="fa-solid fa-house"></i> Home
        </a>
        <a href="{{ url('/owner/dokumen') }}" class="{{ $menuDokumen ?? '' }}">
          <i class="fa-solid fa-file-lines"></i> Dokumen
        </a>
        @php
          $isRekapanKeterlambatanActive = request()->is('*rekapan-keterlambatan*') ||
            request()->routeIs('owner.rekapan-keterlambatan*');
        @endphp
        <a href="{{ url('/owner/rekapan-keterlambatan') }}"
          class="{{ $menuRekapanKeterlambatan ?? '' }} sidebar-menu-trigger {{ $isRekapanKeterlambatanActive ? 'active' : '' }}"
          data-submenu="rekapan-keterlambatan" aria-expanded="{{ $isRekapanKeterlambatanActive ? 'true' : 'false' }}">
          <i class="fa-solid fa-exclamation-triangle"></i> Rekapan Keterlambatan
        </a>
      </div>
      <div style="margin-top: auto; padding-bottom: 20px;">
        <a href="{{ url('/logout') }}"
          onclick="event.preventDefault(); document.getElementById('logout-form-owner').submit();" class="logout-link">
          <i class="fa-solid fa-sign-out-alt"></i> Keluar
        </a>
        <form id="logout-form-owner" action="{{ url('/logout') }}" method="POST" style="display: none;">
          @csrf
        </form>
      </div>
    @else
      <!-- Regular Menu for other roles -->
      <div style="flex: 1; display: flex; flex-direction: column;">
        @if($isBagianUser)
          {{-- Bagian-specific Home menu --}}
          @php
            $isBagianDashboardActive = request()->is('*bagian/dashboard*') || request()->routeIs('bagian.dashboard');
          @endphp
          <a href="{{ route('bagian.dashboard') }}" class="{{ $isBagianDashboardActive ? 'active' : '' }}"><i
              class="fa-solid fa-house"></i> Home</a>
        @else
          <a href="{{ url($dashboardUrl) }}" class="{{ $menuDashboard ?? '' }}"><i class="fa-solid fa-house"></i> Home</a>
        @endif

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
            // Prioritize role field first (most accurate)
            if (isset($user->role) && !empty($user->role)) {
              $currentUserRole = $user->role;
            } elseif (isset($user->name)) {
              // Fallback to name mapping if role is not set
              $nameToRole = [
                'Ibu A' => 'IbuA',
                'IbuA' => 'IbuA',
                'Ibu Tarapul' => 'IbuA',
                'IbuB' => 'IbuB',
                'Ibu B' => 'IbuB',
                'Ibu Yuni' => 'IbuB',
                'Team Verifikasi' => 'IbuB',
                'Perpajakan' => 'Perpajakan',
                'Team Perpajakan' => 'Perpajakan',
                'Akutansi' => 'Akutansi',
                'Team Akutansi' => 'Akutansi',
                'Pembayaran' => 'Pembayaran',
                'Team Pembayaran' => 'Pembayaran'
              ];
              $currentUserRole = $nameToRole[$user->name] ?? 'IbuA';
            }
          }

          // Normalize role to check (case-insensitive comparison)
          $currentUserRoleLower = strtolower($currentUserRole);
          $inboxRoles = ['ibub', 'verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
          $showInbox = in_array($currentUserRoleLower, $inboxRoles);

          // Map role to inbox query format
          $inboxRoleForQuery = 'IbuB';
          if (in_array($currentUserRoleLower, ['perpajakan'])) {
            $inboxRoleForQuery = 'Perpajakan';
          } elseif (in_array($currentUserRoleLower, ['akutansi'])) {
            $inboxRoleForQuery = 'Akutansi';
          } elseif (in_array($currentUserRoleLower, ['pembayaran'])) {
            $inboxRoleForQuery = 'Pembayaran';
          } elseif (in_array($currentUserRoleLower, ['verifikasi'])) {
            $inboxRoleForQuery = 'IbuB'; // Verifikasi uses IbuB inbox
          }
        @endphp

        @if($showInbox)
          <a href="{{ url('/inbox') }}"
            class="{{ request()->is('inbox') || request()->routeIs('inbox.*') ? 'active' : '' }}">
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
          @if($isBagianUser)
            {{-- Bagian-specific menu --}}
            @php
              $isBagianDocumentsActive = request()->is('*bagian/documents*') || request()->routeIs('bagian.documents.*');
            @endphp
            <a href="{{ route('bagian.documents.index') }}" class="{{ $isBagianDocumentsActive ? 'active' : '' }}">
              <i class="fa-solid fa-file-lines"></i> Dokumen
            </a>
          @else
            {{-- Regular Dokumen menu for other roles --}}
            @php
              // Determine route based on module
              $menuRoute = match ($module) {
                'pembayaran' => route('documents.pembayaran.index'),
                'akutansi' => url($dokumenUrl),
                'perpajakan' => url($dokumenUrl),
                'ibub' => url($dokumenUrl),
                default => url($dokumenUrl)
              };

              // Check if current route is within this module
              $isModuleActive = match ($module) {
                'pembayaran' => request()->routeIs('dokumensPembayaran.*') ||
                request()->routeIs('pembayaran.*') ||
                request()->routeIs('rekapanKeterlambatan.*') ||
                request()->routeIs('csv.import.*') ||
                request()->is('*dokumensPembayaran*') ||
                request()->is('*rekapan-pembayaran*') ||
                request()->is('*rekapan-keterlambatan*') ||
                request()->is('*csv-import*'),
                'akutansi' => request()->routeIs('dokumensAkutansi.*') ||
                request()->routeIs('akutansi.*'),
                'perpajakan' => request()->routeIs('dokumensPerpajakan.*') ||
                request()->routeIs('perpajakan.*'),
                'ibub' => request()->routeIs('dokumensB.*') ||
                request()->routeIs('ibub.*'),
                default => false
              };
            @endphp
            <a href="{{ $menuRoute }}"
              class="{{ ($menuDokumen ?? '') . ($isModuleActive ? ' active' : '') }} sidebar-menu-trigger"
              data-submenu="dokumen" id="btn-pembayaran" aria-expanded="{{ $isModuleActive ? 'true' : 'false' }}">
              <i class="fa-solid fa-file-lines"></i>
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
            </a>
          @endif

        @endunless

        <!-- Tracking Dokumen Menu - Untuk semua role -->
        @if($isBagianUser)
          {{-- Bagian-specific tracking menu --}}
          @php
            $isBagianTrackingActive = request()->is('*bagian/tracking*') || request()->routeIs('bagian.tracking');
          @endphp
          <a href="{{ route('bagian.tracking') }}" class="{{ $isBagianTrackingActive ? 'active' : '' }}">
            <i class="fa-solid fa-route"></i> Tracking Dokumen
          </a>
        @else
          @php
            $trackingUrl = match ($module) {
              'ibua', 'ibua' => '/tracking-dokumen',
              'ibub', 'ibub' => '/tracking-dokumen',
              'pembayaran' => '/tracking-dokumen',
              'akutansi' => '/tracking-dokumen',
              'perpajakan' => '/tracking-dokumen',
              default => '/tracking-dokumen'
            };
            $isTrackingActive = request()->is('*tracking-dokumen*');
          @endphp
          <a href="{{ url($trackingUrl) }}" class="{{ $isTrackingActive ? 'active' : '' }}">
            <i class="fa-solid fa-route"></i> Tracking Dokumen
          </a>
        @endif
      </div>

      <!-- Logout Button - Pindahkan ke paling bawah -->
      @unless($isOwner)
        <div style="margin-top: auto; padding-bottom: 20px;">
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
          <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="logout-link">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
          </a>
        </div>
      @endunless
    @endif
  </div>

  <!-- Secondary Sidebar (Submenu Panel) - Mekari Style -->
  @if($isOwner)
    @php
      // Check if owner is on rekapan keterlambatan page
      $isRekapanKeterlambatanPage = request()->is('*rekapan-keterlambatan*') ||
        request()->routeIs('owner.rekapan-keterlambatan*');
      $shouldShowSecondarySidebarOwner = $isRekapanKeterlambatanPage;
    @endphp
    <div class="secondary-sidebar {{ $shouldShowSecondarySidebarOwner ? 'active' : '' }}"
      id="sidebar-rekapan-keterlambatan" role="complementary" aria-label="Submenu Panel">
      <div class="secondary-sidebar-header">
        MENU REKAPAN KETERLAMBATAN
      </div>
      <div class="secondary-sidebar-content">
        @php
          $currentRole = strtolower(request()->route('roleCode') ?? '');
        @endphp
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'ibuA') }}"
          class="{{ $currentRole === 'ibua' ? 'active' : '' }}">
          <i class="fa-solid fa-user me-2"></i> Ibu Tara
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'ibuB') }}"
          class="{{ $currentRole === 'ibub' ? 'active' : '' }}">
          <i class="fa-solid fa-users me-2"></i> Team Verifikasi
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'perpajakan') }}"
          class="{{ $currentRole === 'perpajakan' ? 'active' : '' }}">
          <i class="fa-solid fa-file-invoice-dollar me-2"></i> Team Perpajakan
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'akutansi') }}"
          class="{{ $currentRole === 'akutansi' ? 'active' : '' }}">
          <i class="fa-solid fa-calculator me-2"></i> Team Akutansi
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'pembayaran') }}"
          class="{{ $currentRole === 'pembayaran' ? 'active' : '' }}">
          <i class="fa-solid fa-money-bill-wave me-2"></i> Pembayaran
        </a>
      </div>
    </div>
  @else
  @php
    // Check if user is on a submenu page or menu dokumen is active
    $hasSubmenu = isset($menuDokumen) && !empty($menuDokumen);

    // Enhanced detection for pembayaran module
    $isSubmenuPage = false;
    if ($module === 'pembayaran') {
      $isSubmenuPage = request()->routeIs('dokumensPembayaran.*') ||
        request()->routeIs('pembayaran.*') ||
        request()->routeIs('rekapanKeterlambatan.*') ||
        request()->routeIs('csv.import.*') ||
        request()->is('*dokumensPembayaran*') ||
        request()->is('*rekapan-pembayaran*') ||
        request()->is('*rekapan-keterlambatan*') ||
        request()->is('*csv-import*') ||
        request()->is('*pengembalian-dokumensPembayaran*');
    } elseif ($module === 'akutansi') {
      $isSubmenuPage = request()->routeIs('dokumensAkutansi.*') ||
        request()->routeIs('akutansi.*') ||
        request()->is('*dokumensAkutansi*') ||
        request()->is('*rekapan-akutansi*');
    } elseif ($module === 'perpajakan') {
      $isSubmenuPage = request()->routeIs('dokumensPerpajakan.*') ||
        request()->routeIs('perpajakan.*') ||
        request()->is('*dokumensPerpajakan*') ||
        request()->is('*rekapan-perpajakan*');
    } elseif ($module === 'ibub') {
      $isSubmenuPage = request()->routeIs('dokumensB.*') ||
        request()->routeIs('ibub.*') ||
        request()->is('*dokumensB*') ||
        request()->is('*rekapan-ibuB*');
    } elseif ($isBagianUser) {
      $isSubmenuPage = request()->is('*bagian/documents*') ||
        request()->is('*bagian/tracking*');
    } else {
      $isSubmenuPage = request()->is('*dokumens*') ||
        request()->is('*rekapan*') ||
        request()->is('*pengembalian*');
    }

    $shouldShowSecondarySidebar = $hasSubmenu || $isSubmenuPage;

    $submenuTitle = '';
    if ($module === 'pembayaran') {
      $submenuTitle = 'MENU PEMBAYARAN';
    } elseif ($module === 'akutansi') {
      $submenuTitle = 'MENU AKUTANSI';
    } elseif ($module === 'perpajakan') {
      $submenuTitle = 'MENU PERPAJAKAN';
    } elseif ($module === 'ibub') {
      $submenuTitle = 'MENU DOKUMEN';
    } elseif ($isBagianUser) {
      $submenuTitle = 'MENU DOKUMEN';
    } else {
      $submenuTitle = 'MENU DOKUMEN';
    }
  @endphp
  <div class="secondary-sidebar {{ $shouldShowSecondarySidebar ? 'active' : '' }}" id="sidebar-pembayaran"
    role="complementary" aria-label="Submenu Panel">
    <div class="secondary-sidebar-header">
      {{ $submenuTitle }}
    </div>
    <div class="secondary-sidebar-content">
      @if($module === 'pembayaran')
        @php
          // Determine active state for each submenu item (combine controller class + route detection)
          $isDaftarActive = ($menuDaftarDokumen ?? '') === 'Active' ||
            request()->routeIs('documents.pembayaran.*') ||
            request()->routeIs('documents.pembayaran.index') ||
            request()->is('*documents/pembayaran*');
          $isRekapanActive = ($menuRekapanDokumen ?? '') === 'Active' ||
            request()->routeIs('pembayaran.rekapan') ||
            request()->is('*rekapan-pembayaran*');
          $isKeterlambatanActive = ($menuRekapKeterlambatan ?? '') === 'Active' ||
            request()->routeIs('rekapanKeterlambatan.*') ||
            request()->is('*rekapan-keterlambatan*');
        @endphp
        <a href="{{ url($dokumenUrl) }}" class="{{ $isDaftarActive ? 'active' : '' }}">
          <i class="fa-solid fa-list me-2"></i> Daftar Pembayaran
        </a>
        <a href="{{ route('csv.import.index') }}"
          class="{{ request()->routeIs('csv.import.*') || request()->is('*csv-import*') ? 'active' : '' }}">
          <i class="fa-solid fa-file-import me-2"></i> Import Data
        </a>
        <a href="{{ route('reports.pembayaran.index') }}" class="{{ $isRekapanActive ? 'active' : '' }}">
          <i class="fa-solid fa-chart-bar me-2"></i> Rekapan Dokumen
        </a>
        <a href="{{ url($pengembalianUrl) }}" class="{{ $isKeterlambatanActive ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @elseif($module === 'akutansi')
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
          <i class="fa-solid fa-list me-2"></i> Daftar Akutansi
          <span class="menu-notification-badge" id="akutansi-notification-badge"
            style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ url($pengembalianUrl) }}" class="{{ $menuDaftarDokumenDikembalikan ?? '' }}">
          <i class="fa-solid fa-rotate-left me-2"></i> Daftar Pengembalian Akutansi
        </a>
        <a href="{{ route('reports.akutansi.index') }}" class="{{ $menuRekapan ?? '' }}">
          <i class="fa-solid fa-chart-bar me-2"></i> Rekapan Akutansi
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'akutansi') }}" class="{{ request()->is('*rekapan-keterlambatan/akutansi*') ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @elseif($module === 'perpajakan')
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
          <i class="fa-solid fa-list me-2"></i> Daftar Perpajakan
          <span class="menu-notification-badge" id="perpajakan-notification-badge"
            style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ url($pengembalianUrl) }}" class="{{ $menuDaftarDokumenDikembalikan ?? '' }}">
          <i class="fa-solid fa-rotate-left me-2"></i> Daftar Pengembalian Perpajakan
        </a>
        <a href="{{ route('reports.perpajakan.index') }}" class="{{ $menuRekapan ?? '' }}">
          <i class="fa-solid fa-chart-bar me-2"></i> Rekapan
        </a>
        <a href="{{ route('reports.perpajakan.export') }}"
          class="{{ request()->routeIs('reports.perpajakan.export*') ? 'active' : '' }}">
          <i class="fa-solid fa-file-export me-2"></i> Export Data
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'perpajakan') }}" class="{{ request()->is('*rekapan-keterlambatan/perpajakan*') ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @elseif($module === 'ibub')
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
          <i class="fa-solid fa-list me-2"></i> Daftar Dokumen
          <span class="menu-notification-badge" id="notification-badge" style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ route('returns.verifikasi.bidang') }}" class="{{ $menuPengembalianKeBidang ?? '' }}">
          <i class="fa-solid fa-arrow-left me-2"></i> Pengembalian ke Bidang
          <span class="menu-notification-badge" id="pengembalian-ke-bidang-badge"
            style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ route('returns.verifikasi.index') }}" class="{{ $menuDaftarDokumenDikembalikan ?? '' }}">
          <i class="fa-solid fa-arrow-right me-2"></i> Pengembalian dari Bagian
          <span class="menu-notification-badge" id="pengembalian-ke-bagian-badge"
            style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ route('reports.verifikasi.index') }}" class="{{ $menuRekapan ?? '' }}">
          <i class="fa-solid fa-chart-bar me-2"></i> Rekapan
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'ibuB') }}" class="{{ request()->is('*rekapan-keterlambatan/ibuB*') ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @elseif($isBagianUser)
        {{-- Bagian submenu (same pattern as IbuA) --}}
        @php
          $isDaftarActive = request()->routeIs('bagian.documents.index') || request()->is('*bagian/documents');
          $isTambahActive = request()->routeIs('bagian.documents.create') || request()->is('*bagian/documents/create*');
          $isEditActive = request()->routeIs('bagian.documents.edit') || request()->is('*bagian/documents/*/edit*');
          $isRekapanActive = request()->routeIs('bagian.rekapan') || request()->is('*bagian/rekapan*');
        @endphp
        <a href="{{ route('bagian.documents.index') }}" class="{{ $isDaftarActive ? 'active' : '' }}">
          <i class="fa-solid fa-list me-2"></i> Daftar Dokumen
        </a>
        <a href="{{ route('bagian.documents.create') }}" class="{{ $isTambahActive ? 'active' : '' }}">
          <i class="fa-solid fa-plus me-2"></i> Tambah Dokumen
        </a>
        <a href="{{ route('bagian.tracking') }}" class="{{ request()->routeIs('bagian.tracking') ? 'active' : '' }}">
          <i class="fa-solid fa-chart-pie me-2"></i> Rekapan
        </a>
      @else
        <!-- IbuA -->
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}">
          <i class="fa-solid fa-list me-2"></i> Daftar Dokumen
        </a>
        @if($tambahDokumenUrl)
          <a href="{{ url($tambahDokumenUrl) }}" class="{{ $menuTambahDokumen ?? '' }}">
            <i class="fa-solid fa-plus me-2"></i> Tambah Dokumen
          </a>
        @endif
        <a href="{{ url('/rekapan') }}" class="{{ $menuRekapan ?? '' }}">
          <i class="fa-solid fa-chart-pie me-2"></i> Rekapan
        </a>
        <a href="{{ route('owner.rekapan-keterlambatan.role', 'ibuA') }}" class="{{ request()->is('*rekapan-keterlambatan/ibuA*') ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @endif
    </div>
  </div>
  @endunless

  <!-- Content -->
  <div class="content {{ ($shouldShowSecondarySidebar ?? false) ? 'with-secondary-sidebar' : '' }}">
    <!-- Notifikasi Success/Error -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert"
        style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);">
        <i class="fa-solid fa-circle-check me-2"></i>
        <strong>Berhasil!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert"
        style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert"
        style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);">
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
  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Global UX Helper: Prevent Navigation During Text Selection -->
  <script>
  /        *         * * Global Handler untuk mencegah navigasi saat user sedang menyeleksi teks
   * Digunakan pada Card dan Table Row yangbisa diklik
   * 
   * @param {Event} event - Click event
   * @param {string} url - URL tujua    nnavigasi
   */
  window.handleItemClick = function(event, url) {
    // 1. Cek apakah user sedang menyeleksi teks
    const selection = window.getSelection();
    const selectedText = selection.toString().trim();
    
    if (selectedText.length > 0) {
      // User sedang menyeleksi teks, jangan navigasi
      event.preventDefault();
      event.stopPropagation();
      return false;
    }
    
    // 2. Cek apakah yang diklik adalah link/tombol/input/select/textarea
    const target = event.target;
    const tagName = target.tagName.toLowerCase();
    const isInteractiveElement = 
      tagName === 'a' || 
      tagName === 'button' || 
      tagName === 'input' || 
      tagName === 'select' || 
      tagName === 'textarea' ||
      target.closest('a') !== null ||
      target.closest('button') !== null ||
      target.closest('.btn') !== null ||
      target.closest('[role="button"]') !== null;
    
    if (isInteractiveElement) {
      // User klik elemen interaktif, biarkan default behavior
      return true;
    }
    
    // 3. Cek apakah ini adalah double-click (biasanya untuk select word)
    if (event.detail === 2) {
      // Double-click biasanya untuk select word, tunggu sebentar
      setTimeout(() => {
        const newSelection = window.getSelection();
        if (newSelection.toString().trim().length > 0) {
          // User berhasil select text, jangan navigasi
          return false;
        }
      }, 50);
      return false;
    }
    
    // 4. Cek apakah user sedang drag (mouse drag selection)
    if (event.detail === 0 || event.which === 0) {
      // Ini adalah programmatic click atau drag, jangan navigasi
      return false;
    }
    
    // 5. Jika aman, lakukan navigasi
    if (url) {
      window.location.href = url;
    }
    return true;
  };
  </script>

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
      const dropdownIcon = dropdownToggle ? dropdownToggle.querySelector('.dropdown-icon') : null;

      if (dropdownToggle && dropdownIcon) {
        dropdownToggle.addEventListener('click', function() {
          // Toggle dropdown content
          dropdownContent.classList.toggle('show');

          // Rotate icon
          dropdownIcon.classList.toggle('rotate');

          // Toggle active state
          dropdownToggle.classList.toggle('active');
        });
      }

      // Sidebar now uses floating drawer effect - no need to adjust margins
      // Content stays fixed at 72px margin, sidebar overlays on hover
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
      // Check if document is approved
      if (doc.approved_by) {
        notificationClass = 'notification-approved';
      } else {
        notificationClass = 'notification-new';
      }
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
                  <small style="opacity: 0.8;">Dikembalikan dari Team Verifikasi - ${doc.returned_at}</small>
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
                  <small style="opacity: 0.8;">Dokumen baru dari Team Verifikasi - ${doc.sent_at}</small>
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
            } else if (type === 'approved' || doc.approved_by) {
              // Dokumen yang sudah di-approve oleh Perpajakan/Akutansi/Pembayaran
              const approvedRoleName = doc.approved_by === 'Perpajakan' ? 'Team Perpajakan' : 
                                       doc.approved_by === 'Akutansi' ? 'Team Akutansi' : 
                                       doc.approved_by === 'Pembayaran' ? 'Team Pembayaran' : doc.approved_by;
              
              notification.innerHTML = `
                <div class="notification-header notification-header-approved">
                  <div class="notification-title">
                    <i class="fa-solid fa-check-circle"></i>
                    Dokumen Sudah Di-approve
                  </div>
                  <button class="notification-close" onclick="removeNotification('${notificationId}')">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </div>
                <div class="notification-body">
                  <strong>No. Agenda:</strong> ${doc.nomor_agenda || '-'}<br>
                  <strong>No. SPP:</strong> ${doc.nomor_spp || '-'}<br>
                  <strong>Nilai:</strong> ${formattedRupiah}<br>
                  <small style="opacity: 0.8;">Disetujui oleh ${approvedRoleName} - ${doc.approved_at || doc.sent_at}</small>
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

            // Only increment counter for new documents, not for approved documents
            if (type !== 'approved' && !doc.approved_by) {
              notificationCount++;
            }
          }, index * 500); // Stagger notifications
        });

        // Only update badge for new documents, not for approved documents
        if (type !== 'approved') {
          updateNotificationBadge(notificationCount);
        }
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
          const isApprovedNotification = notification.classList.contains('notification-approved');

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
            } else if (isApprovedNotification) {
              // Approved notifications don't affect badge counter
              // Do nothing
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

              // Separate new documents from approved documents for IbuB
              let documentsToNotify = newDocuments;
              let approvedDocuments = [];
              let newDocumentsOnly = [];
              
              if (isIbuB) {
                newDocumentsOnly = newDocuments.filter(doc => doc.is_new_from_ibua === true);
                approvedDocuments = newDocuments.filter(doc => doc.approved_by);
                
                // Only show notification for approved documents (not as "new document")
                if (approvedDocuments.length > 0) {
                  showNotification(approvedDocuments, 'approved');
                }
                
                // Show notification for new documents from IbuA
                if (newDocumentsOnly.length > 0) {
                  showNotification(newDocumentsOnly, 'new');
                }
                
                documentsToNotify = []; // Don't show default notification
              }

              // Add to known documents
              newDocuments.forEach(doc => knownDocumentIds.add(doc.id));

              // Show notifications for other roles
              if (!isIbuB && documentsToNotify.length > 0) {
                let notificationType;
                if (isPerpajakan) {
                  notificationType = 'perpajakan';
                } else if (isAkutansi) {
                  notificationType = 'akutansi';
                  console.log('🟢 AKUTANSI NOTIFICATION TYPE SET');
                } else if (isPembayaran) {
                  notificationType = 'pembayaran';
                } else {
                  notificationType = 'returned';
                }
                showNotification(documentsToNotify, notificationType);
              }

              // Update badge counter based on type (only for new documents, not approved)
              if (isIbuB) {
                // Only count new documents from IbuA, not approved documents
                notificationCount += newDocumentsOnly.length;
                updateNotificationBadge(notificationCount, 'new');
              } else if (isPerpajakan) {
                perpajakanNotificationCount += documentsToNotify.length;
                updateNotificationBadge(perpajakanNotificationCount, 'perpajakan');
              } else if (isAkutansi) {
                akutansiNotificationCount += documentsToNotify.length;
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
    const inboxRoles = ['ibub', 'verifikasi', 'perpajakan', 'akutansi'];
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
    const isIbuB = ['ibub', 'ibu b', 'ibu yuni', 'team verifikasi'].includes(userRoleLower);

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
        'team verifikasi': 'IbuB',
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

                        // FIX: Filter hanya dokumen yang benar-benar milik user
                        // Ini mencegah cross-interference dari reject dokumen user lain
                        const userRejectedDocs = data.rejected_documents.filter(doc => {
                            // Hanya dokumen yang created_by milik user yang sedang login
                            const createdBy = (doc.created_by || '').toString().toLowerCase();
                            return createdBy === 'ibua' || createdBy === 'ibu a' || createdBy === 'ibua' || createdBy === 'ibu tarapul';
                        });

                        console.log('👤 User rejected documents after filtering:', userRejectedDocs.length);

                        // Filter dokumen yang perlu ditampilkan
                        // Untuk memastikan notifikasi selalu muncul, tampilkan dokumen yang di-reject dalam 24 jam terakhir
                        // Tampilkan jika:
                        // 1. Belum pernah ditampilkan sebelumnya, ATAU
                        // 2. Sudah pernah ditampilkan tapi lebih dari 5 menit yang lalu (untuk memastikan user melihat notifikasi)
                        //    (Dikurangi dari 30 menit menjadi 5 menit agar notifikasi lebih sering muncul)
                        const now = Date.now();
                        const fiveMinutesInMs = 5 * 60 * 1000; // 5 menit dalam milliseconds

                        const newRejectedToShow = userRejectedDocs.filter(doc => {
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

<!-- Secondary Sidebar Toggle Script -->
<script>
// Define functions in global scope immediately
(function() {
  'use strict';
  
  /**
   * Toggle Secondary Sidebar (Mekari Style)
   * Menampilkan/menyembunyikan secondary sidebar tanpa reload halaman
   */
  window.toggleSecondarySidebar = function() {
    console.log('toggleSecondarySidebar called');
    const secondarySidebar = document.getElementById('sidebar-pembayaran');
    const content = document.querySelector('.content');
    const topbar = document.querySelector('.topbar');
    const menuTrigger = document.getElementById('btn-pembayaran');
    
    if (!secondarySidebar) {
      console.error('Secondary sidebar not found');
      return;
    }
    
    if (!content) {
      console.error('Content not found');
      return;
    }
    
    // Toggle active state
    const isActive = secondarySidebar.classList.contains('active');
    console.log('Secondary sidebar isActive:', isActive);
    
    if (isActive) {
      // Hide secondary sidebar
      secondarySidebar.classList.remove('active');
      content.classList.remove('with-secondary-sidebar');
      if (topbar) {
        topbar.classList.remove('with-secondary-sidebar');
      }
      
      // Update menu trigger state
      if (menuTrigger) {
        menuTrigger.classList.remove('active');
        menuTrigger.setAttribute('aria-expanded', 'false');
      }
      console.log('Secondary sidebar hidden');
    } else {
      // Show secondary sidebar
      secondarySidebar.classList.add('active');
      content.classList.add('with-secondary-sidebar');
      if (topbar) {
        topbar.classList.add('with-secondary-sidebar');
      }
      
      // Update menu trigger state
      if (menuTrigger) {
        menuTrigger.classList.add('active');
        menuTrigger.setAttribute('aria-expanded', 'true');
      }
      console.log('Secondary sidebar shown');
    }
  };
  
  /**
   * Show Secondary Sidebar (without toggle)
   */
  window.showSecondarySidebar = function() {
    const secondarySidebar = document.getElementById('sidebar-pembayaran');
    const content = document.querySelector('.content');
    const topbar = document.querySelector('.topbar');
    const menuTrigger = document.getElementById('btn-pembayaran');
    
    if (secondarySidebar && content) {
      secondarySidebar.classList.add('active');
      content.classList.add('with-secondary-sidebar');
      if (topbar) {
        topbar.classList.add('with-secondary-sidebar');
      }
      
      if (menuTrigger) {
        menuTrigger.classList.add('active');
        menuTrigger.setAttribute('aria-expanded', 'true');
      }
    }
  };
  
  /**
   * Hide Secondary Sidebar
   */
  window.hideSecondarySidebar = function() {
    const secondarySidebar = document.getElementById('sidebar-pembayaran');
    const content = document.querySelector('.content');
    const topbar = document.querySelector('.topbar');
    const menuTrigger = document.getElementById('btn-pembayaran');
    
    if (secondarySidebar && content) {
      secondarySidebar.classList.remove('active');
      content.classList.remove('with-secondary-sidebar');
      if (topbar) {
        topbar.classList.remove('with-secondary-sidebar');
      }
      
      if (menuTrigger) {
        menuTrigger.classList.remove('active');
        menuTrigger.setAttribute('aria-expanded', 'false');
      }
    }
  };
})();


// Auto-show secondary sidebar jika menu dokumen aktif atau user berada di halaman submenu
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOMContentLoaded - Setting up secondary sidebar');
  
  const secondarySidebar = document.getElementById('sidebar-pembayaran');
  const content = document.querySelector('.content');
  const menuTrigger = document.getElementById('btn-pembayaran');
  
  console.log('Elements found:', {
    secondarySidebar: !!secondarySidebar,
    content: !!content,
    menuTrigger: !!menuTrigger
  });
  
  // Check if user is on a submenu page (any page that should show secondary sidebar)
  const currentPathCheck = window.location.pathname;
  const currentPath = window.location.pathname; // Alias for compatibility
  let isSubmenuPage = currentPathCheck.includes('/dokumens') || 
                      currentPathCheck.includes('/rekapan') || 
                      currentPathCheck.includes('/pengembalian') ||
                      currentPathCheck.includes('/dokumensPembayaran') || 
                      currentPathCheck.includes('/rekapan-pembayaran') || 
                      currentPathCheck.includes('/rekapan-keterlambatan') ||
                      currentPathCheck.includes('/pengembalian-dokumensPembayaran') ||
                      currentPathCheck.includes('/csv-import') ||
                      currentPathCheck.includes('/dokumensAkutansi') ||
                      currentPathCheck.includes('/rekapan-akutansi') ||
                      currentPathCheck.includes('/dokumensPerpajakan') ||
                      currentPathCheck.includes('/rekapan-perpajakan') ||
                      currentPathCheck.includes('/dokumensB') ||
                      currentPathCheck.includes('/rekapan-ibuB') ||
                      currentPathCheck.includes('/documents/pembayaran') ||
                      currentPathCheck.includes('/documents/akutansi') ||
                      currentPathCheck.includes('/documents/perpajakan') ||
                      currentPathCheck.includes('/documents/verifikasi');
  
  console.log('State check:', {
    isSubmenuPage,
    currentPath: currentPathCheck
  });
  
  // Show secondary sidebar if user is on submenu page
  if (isSubmenuPage) {
    if (secondarySidebar) {
      secondarySidebar.classList.add('active');
    }
    if (content) {
      content.classList.add('with-secondary-sidebar');
    }
    if (menuTrigger) {
      menuTrigger.classList.add('active');
      menuTrigger.setAttribute('aria-expanded', 'true');
    }
    console.log('Secondary sidebar auto-shown');
  }
  
  // Ensure secondary sidebar is visible if it has active class on page load
  if (secondarySidebar && secondarySidebar.classList.contains('active')) {
    if (content) {
      content.classList.add('with-secondary-sidebar');
    }
    if (menuTrigger) {
      menuTrigger.classList.add('active');
      menuTrigger.setAttribute('aria-expanded', 'true');
    }
  }
  
  // Auto-open secondary sidebar based on current route
  // No need for click handler since menu is now a direct link
  
  const topbar = document.querySelector('.topbar');
  
  if (isSubmenuPage && secondarySidebar) {
    secondarySidebar.classList.add('active');
    if (content) {
      content.classList.add('with-secondary-sidebar');
    }
    if (topbar) {
      topbar.classList.add('with-secondary-sidebar');
    }
    if (menuTrigger) {
      menuTrigger.classList.add('active');
      menuTrigger.setAttribute('aria-expanded', 'true');
    }
    console.log('Secondary sidebar auto-opened for submenu page');
  }
  
  // Update menu trigger active state based on current route
  if (menuTrigger && isSubmenuPage) {
    menuTrigger.classList.add('active');
    menuTrigger.setAttribute('aria-expanded', 'true');
  }
  
  // Ensure topbar has correct class if secondary sidebar is already active on page load
  if (secondarySidebar && secondarySidebar.classList.contains('active')) {
    if (topbar) {
      topbar.classList.add('with-secondary-sidebar');
    }
  }
  
  console.log('Secondary sidebar setup complete');

  // Profile Dropdown Toggle
  const profileDropdownToggle = document.getElementById('profileDropdownToggle');
  const profileDropdownMenu = document.getElementById('profileDropdownMenu');
  
  if (profileDropdownToggle && profileDropdownMenu) {
    // Toggle dropdown on click
    profileDropdownToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      profileDropdownMenu.style.display = profileDropdownMenu.style.display === 'none' ? 'block' : 'none';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!profileDropdownToggle.contains(e.target) && !profileDropdownMenu.contains(e.target)) {
        profileDropdownMenu.style.display = 'none';
      }
    });

    // Close dropdown when clicking on a menu item
    const dropdownItems = profileDropdownMenu.querySelectorAll('.profile-dropdown-item');
    dropdownItems.forEach(item => {
      item.addEventListener('click', function() {
        profileDropdownMenu.style.display = 'none';
      });
    });
  }
});

// Global Function: Format Rupiah Input (Auto format with dots)
window.formatRupiahInput = function(input) {
  if (!input) return;
  
  // Remove all non-numeric characters
  let value = input.value.replace(/[^\d]/g, '');
  
  // Format with thousand separators (dots)
  if (value) {
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    input.value = value;
  } else {
    input.value = '';
  }
};

// Auto-apply format rupiah to all inputs with specific names/ids
document.addEventListener('DOMContentLoaded', function() {
  // List of common input names/ids for nilai rupiah
  const rupiahInputSelectors = [
    'input[name="nilai_rupiah"]',
    'input[id*="nilai_rupiah"]',
    'input[id*="nilai-rupiah"]',
    'input[name*="nilai_rupiah"]',
    'input[name*="nilai-rupiah"]',
    '#nilai_rupiah',
    '#nilai-rupiah',
    '#edit-nilai-rupiah',
    '#edit_nilai_rupiah'
  ];
  
  rupiahInputSelectors.forEach(selector => {
    const inputs = document.querySelectorAll(selector);
    inputs.forEach(input => {
      // Skip if already has event listener (check for data attribute)
      if (input.dataset.rupiahFormatted === 'true') return;
      
      // Mark as formatted
      input.dataset.rupiahFormatted = 'true';
      
      // Format on input
      input.addEventListener('input', function() {
        window.formatRupiahInput(this);
      });
      
      // Format on paste
      input.addEventListener('paste', function(e) {
        setTimeout(() => {
          window.formatRupiahInput(this);
        }, 10);
      });
      
      // Format initial value if exists
      if (input.value) {
        window.formatRupiahInput(input);
      }
    });
  });
  
  // Auto-remove format from nilai_rupiah inputs before form submit
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      // Find all nilai rupiah inputs in this form
      const rupiahInputs = form.querySelectorAll('input[name="nilai_rupiah"], input[id*="nilai_rupiah"], input[id*="nilai-rupiah"]');
      rupiahInputs.forEach(input => {
        // Remove dots before submit
        if (input.value) {
          input.value = input.value.replace(/[^\d]/g, '');
        }
      });
    });
  });

  // Initialize Flatpickr for all date and datetime inputs with DD/MM/YYYY format
  function initializeFlatpickrDatePickers() {
    // Wait for Flatpickr to be loaded
    if (typeof flatpickr === 'undefined') {
      console.warn('Flatpickr is not loaded yet, retrying...');
      setTimeout(initializeFlatpickrDatePickers, 100);
      return;
    }

    // Find all date and datetime-local inputs
    const dateInputs = document.querySelectorAll("input[type='date']");
    const datetimeInputs = document.querySelectorAll("input[type='datetime-local']");
    
    // Convert all date inputs to text with Flatpickr
    dateInputs.forEach(input => {
      // Store original type
      input.dataset.originalType = 'date';
      
      // Convert existing value from YYYY-MM-DD to DD/MM/YYYY if exists
      let currentValue = input.value;
      if (currentValue && /^\d{4}-\d{2}-\d{2}$/.test(currentValue)) {
        const parts = currentValue.split('-');
        currentValue = `${parts[2]}/${parts[1]}/${parts[0]}`;
      }
      
      // Change type to text and add placeholder
      input.type = 'text';
      input.placeholder = 'Pilih tanggal (dd/mm/yyyy)';
      if (currentValue) {
        input.value = currentValue;
      }
      
      // Initialize Flatpickr for date inputs
      flatpickr(input, {
        dateFormat: "d/m/Y",
        locale: "id",
        allowInput: true,
        placeholder: "dd/mm/yyyy",
        parseDate: function(datestr, format) {
          // Parse DD/MM/YYYY format
          const parts = datestr.split('/');
          if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]);
          }
          return null;
        }
      });
    });

    // Convert all datetime-local inputs to text with Flatpickr
    datetimeInputs.forEach(input => {
      // Store original type
      input.dataset.originalType = 'datetime-local';
      
      // Convert existing value from YYYY-MM-DDTHH:MM to DD/MM/YYYY HH:MM if exists
      let currentValue = input.value;
      if (currentValue && /^\d{4}-\d{2}-\d{2}T/.test(currentValue)) {
        const [datePart, timePart] = currentValue.split('T');
        const parts = datePart.split('-');
        const time = timePart ? timePart.substring(0, 5) : '00:00';
        currentValue = `${parts[2]}/${parts[1]}/${parts[0]} ${time}`;
      }
      
      // Change type to text and add placeholder
      input.type = 'text';
      input.placeholder = 'Pilih tanggal & waktu (dd/mm/yyyy hh:mm)';
      if (currentValue) {
        input.value = currentValue;
      }
      
      // Initialize Flatpickr for datetime inputs
      flatpickr(input, {
        dateFormat: "d/m/Y H:i",
        locale: "id",
        enableTime: true,
        time_24hr: false,
        allowInput: true,
        placeholder: "dd/mm/yyyy hh:mm",
        parseDate: function(datestr, format) {
          // Parse DD/MM/YYYY HH:MM format
          const parts = datestr.split(' ');
          if (parts.length === 2) {
            const dateParts = parts[0].split('/');
            const timeParts = parts[1].split(':');
            if (dateParts.length === 3 && timeParts.length === 2) {
              return new Date(dateParts[2], dateParts[1] - 1, dateParts[0], timeParts[0], timeParts[1]);
            }
          }
          return null;
        }
      });
    });

    // Handle form submission - convert back to YYYY-MM-DD format
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function(e) {
        // Convert date inputs from DD/MM/YYYY back to YYYY-MM-DD for form submission
        form.querySelectorAll("input[data-original-type='date'], input[name*='tanggal'][type='text']:not([name*='spp']):not([name*='datetime']):not([name*='masuk'])").forEach(input => {
          if (input.value && /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(input.value.trim())) {
            const parts = input.value.trim().split('/');
            if (parts.length === 3) {
              const day = parts[0].padStart(2, '0');
              const month = parts[1].padStart(2, '0');
              const year = parts[2];
              input.value = `${year}-${month}-${day}`;
            }
          }
        });

        // Convert datetime-local inputs from DD/MM/YYYY HH:MM back to YYYY-MM-DDTHH:MM for form submission
        form.querySelectorAll("input[data-original-type='datetime-local'], input[name*='tanggal_spp'][type='text'], input[name*='tanggal_masuk'][type='text']").forEach(input => {
          if (input.value && /^\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{2}/.test(input.value.trim())) {
            const [datePart, timePart] = input.value.trim().split(' ');
            const parts = datePart.split('/');
            if (parts.length === 3) {
              const day = parts[0].padStart(2, '0');
              const month = parts[1].padStart(2, '0');
              const year = parts[2];
              const time = timePart || '00:00';
              input.value = `${year}-${month}-${day}T${time}`;
            }
          }
        });
      });
    });
  }

  // Initialize when DOM is ready and Flatpickr is loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFlatpickrDatePickers);
  } else {
    // DOM is already ready, but wait a bit for Flatpickr to load
    setTimeout(initializeFlatpickrDatePickers, 100);
  }
});
</script>

</body>
</html>

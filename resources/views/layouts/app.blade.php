<!DOCTYPE html>
<html lang="id" id="html-root">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'PTPN Agenda Online' }}</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts for Owner Dashboard -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
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

  <!-- Mobile Responsive CSS -->
  <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

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
      width: 290px;
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
      width: 290px;
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

    .sidebar .agenda-tree {
      margin-top: 8px;
    }

    .sidebar .agenda-tree > .agenda-tree-toggle {
      margin-top: 0;
      cursor: pointer;
    }

    .sidebar .agenda-tree > .agenda-tree-toggle .right {
      margin-left: auto;
      font-size: 12px;
      transition: transform 0.2s ease;
    }

    .sidebar .agenda-tree.menu-open > .agenda-tree-toggle .right {
      transform: rotate(-90deg);
    }

    .sidebar .nav-treeview {
      display: none;
      margin-left: 10px;
      padding: 2px 0 4px;
    }

    .sidebar .agenda-tree.menu-open > .nav-treeview {
      display: block;
    }

    .sidebar .nav-treeview a {
      margin-left: 10px;
      margin-right: 10px;
      margin-top: 5px;
      padding: 9px 12px 9px 14px;
      font-size: 13px;
      border-radius: 8px;
      line-height: 1.35;
      font-weight: 500;
      width: auto;
      max-width: 252px;
    }

    .sidebar .nav-treeview a i {
      font-size: 13px;
      width: 16px;
      height: 16px;
      margin-right: 10px;
    }

    .sidebar:not(:hover) .agenda-tree .nav-treeview {
      display: none !important;
    }

    .sidebar:not(:hover) .agenda-tree > .agenda-tree-toggle .right {
      display: none;
    }

    #sidebar-pembayaran {
      display: none !important;
    }

    .content.with-secondary-sidebar,
    .topbar.with-secondary-sidebar {
      margin-left: 72px !important;
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

    /* Sidebar logo image */
    .sidebar h4 .sidebar-logo-img {
      width: 36px;
      height: 36px;
      object-fit: contain;
      flex-shrink: 0;
      background: transparent;
      border-radius: 0;
      display: inline-flex;
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

    .sidebar:not(:hover) h4 .sidebar-logo-img {
      width: 36px;
      height: 36px;
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

    .sidebar:hover h4 .sidebar-logo-img {
      margin-right: 10px;
      width: 36px;
      height: 36px;
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

    .dark footer {
      color: #94a3b8;
    }

    /* ========================================
       COMPREHENSIVE DARK MODE STYLES
       ======================================== */

    /* Cards */
    .dark .card {
      background-color: #1e293b;
      border-color: #334155;
      color: #e2e8f0;
    }

    .dark .card-header {
      background-color: #0f172a;
      border-bottom-color: #334155;
      color: #f1f5f9;
    }

    .dark .card-body {
      background-color: #1e293b;
      color: #e2e8f0;
    }

    .dark .card-footer {
      background-color: #0f172a;
      border-top-color: #334155;
    }

    /* Search Box & Filter Cards */
    .dark .search-box,
    .dark .filter-card {
      background-color: #1e293b;
      border-color: #334155;
    }

    /* Tables */
    .dark .table {
      color: #e2e8f0;
      --bs-table-bg: #1e293b;
      --bs-table-border-color: #334155;
    }

    .dark .table thead {
      background-color: #0f172a;
      color: #f1f5f9;
    }

    .dark .table thead th {
      background-color: #0f172a;
      border-color: #475569;
      color: #f1f5f9;
    }

    .dark .table tbody tr {
      background-color: #1e293b;
      border-color: #334155;
    }

    .dark .table tbody tr:hover {
      background-color: #334155;
    }

    .dark .table tbody td {
      border-color: #334155;
      color: #e2e8f0;
    }

    .dark .table-container {
      background-color: #1e293b;
      border-color: #334155;
    }

    .dark .table-responsive {
      background-color: #1e293b;
      border-radius: 12px;
    }

    /* Form Controls */
    .dark .form-control,
    .dark .form-select {
      background-color: #1e293b;
      border-color: #475569;
      color: #f1f5f9;
    }

    .dark .form-control:focus,
    .dark .form-select:focus {
      background-color: #334155;
      border-color: #60a5fa;
      color: #f1f5f9;
      box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
    }

    .dark .form-control::placeholder {
      color: #94a3b8;
    }

    .dark .form-label {
      color: #cbd5e1;
    }

    .dark .form-text {
      color: #94a3b8;
    }

    .dark .input-group-text {
      background-color: #334155;
      border-color: #475569;
      color: #cbd5e1;
    }

    /* Buttons */
    .dark .btn-outline-secondary {
      color: #cbd5e1;
      border-color: #475569;
    }

    .dark .btn-outline-secondary:hover {
      background-color: #334155;
      border-color: #64748b;
      color: #f1f5f9;
    }

    .dark .btn-outline-primary {
      color: #60a5fa;
      border-color: #3b82f6;
    }

    .dark .btn-outline-primary:hover {
      background-color: #3b82f6;
      color: #ffffff;
    }

    .dark .btn-outline-success {
      color: #4ade80;
      border-color: #22c55e;
    }

    .dark .btn-outline-warning {
      color: #fbbf24;
      border-color: #f59e0b;
    }

    .dark .btn-outline-danger {
      color: #f87171;
      border-color: #ef4444;
    }

    .dark .btn-outline-info {
      color: #38bdf8;
      border-color: #0ea5e9;
    }

    .dark .btn-light {
      background-color: #334155;
      border-color: #475569;
      color: #f1f5f9;
    }

    .dark .btn-light:hover {
      background-color: #475569;
      border-color: #64748b;
      color: #f1f5f9;
    }

    /* Dropdowns */
    .dark .dropdown-menu {
      background-color: #1e293b;
      border-color: #334155;
    }

    .dark .dropdown-item {
      color: #e2e8f0;
    }

    .dark .dropdown-item:hover,
    .dark .dropdown-item:focus {
      background-color: #334155;
      color: #f1f5f9;
    }

    .dark .dropdown-divider {
      border-color: #334155;
    }

    /* Modals */
    .dark .modal-content {
      background-color: #1e293b;
      border-color: #334155;
      color: #e2e8f0;
    }

    .dark .modal-header {
      border-bottom-color: #334155;
    }

    .dark .modal-header .modal-title {
      color: #f1f5f9;
    }

    .dark .modal-header .btn-close {
      filter: invert(1) grayscale(100%) brightness(200%);
    }

    .dark .modal-footer {
      border-top-color: #334155;
    }

    /* Badges - improved visibility */
    .dark .badge {
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .dark .badge-success,
    .dark .bg-success {
      background-color: #059669 !important;
      color: #ffffff !important;
    }

    .dark .badge-warning,
    .dark .bg-warning {
      background-color: #d97706 !important;
      color: #ffffff !important;
    }

    .dark .badge-danger,
    .dark .bg-danger {
      background-color: #dc2626 !important;
      color: #ffffff !important;
    }

    .dark .badge-info,
    .dark .bg-info {
      background-color: #0891b2 !important;
      color: #ffffff !important;
    }

    .dark .badge-primary,
    .dark .bg-primary {
      background-color: #2563eb !important;
      color: #ffffff !important;
    }

    .dark .badge-secondary,
    .dark .bg-secondary {
      background-color: #475569 !important;
      color: #f1f5f9 !important;
    }

    /* Text colors */
    .dark .text-muted {
      color: #94a3b8 !important;
    }

    .dark .text-dark {
      color: #e2e8f0 !important;
    }

    .dark h1,
    .dark h2,
    .dark h3,
    .dark h4,
    .dark h5,
    .dark h6 {
      color: #f1f5f9;
    }

    .dark p,
    .dark span:not(.badge) {
      color: #e2e8f0;
    }

    .dark strong,
    .dark b {
      color: #f8fafc;
    }

    .dark small {
      color: #94a3b8;
    }

    .dark a:not(.btn):not(.nav-link):not(.sidebar a) {
      color: #60a5fa;
    }

    .dark a:not(.btn):not(.nav-link):not(.sidebar a):hover {
      color: #93c5fd;
    }

    /* Borders */
    .dark .border {
      border-color: #334155 !important;
    }

    .dark .border-bottom {
      border-bottom-color: #334155 !important;
    }

    .dark .border-top {
      border-top-color: #334155 !important;
    }

    /* List groups */
    .dark .list-group-item {
      background-color: #1e293b;
      border-color: #334155;
      color: #e2e8f0;
    }

    .dark .list-group-item:hover {
      background-color: #334155;
    }

    /* Alerts */
    .dark .alert-info {
      background-color: rgba(14, 165, 233, 0.15);
      border-color: #0ea5e9;
      color: #7dd3fc;
    }

    .dark .alert-success {
      background-color: rgba(34, 197, 94, 0.15);
      border-color: #22c55e;
      color: #86efac;
    }

    .dark .alert-warning {
      background-color: rgba(245, 158, 11, 0.15);
      border-color: #f59e0b;
      color: #fcd34d;
    }

    .dark .alert-danger {
      background-color: rgba(239, 68, 68, 0.15);
      border-color: #ef4444;
      color: #fca5a5;
    }

    /* Pagination */
    .dark .pagination .page-link {
      background-color: #1e293b;
      border-color: #334155;
      color: #cbd5e1;
    }

    .dark .pagination .page-link:hover {
      background-color: #334155;
      border-color: #475569;
      color: #f1f5f9;
    }

    .dark .pagination .page-item.active .page-link {
      background-color: #2563eb;
      border-color: #2563eb;
      color: #fff;
    }

    .dark .pagination .page-item.disabled .page-link {
      background-color: #0f172a;
      border-color: #334155;
      color: #64748b;
    }

    /* Tooltips */
    .dark .tooltip-inner {
      background-color: #334155;
      color: #f1f5f9;
    }

    /* Progress Bars */
    .dark .progress {
      background-color: #334155;
    }

    /* Nav Tabs */
    .dark .nav-tabs {
      border-bottom-color: #334155;
    }

    .dark .nav-tabs .nav-link {
      color: #94a3b8;
    }

    .dark .nav-tabs .nav-link:hover {
      border-color: #475569;
      color: #e2e8f0;
    }

    .dark .nav-tabs .nav-link.active {
      background-color: #1e293b;
      border-color: #334155 #334155 #1e293b;
      color: #f1f5f9;
    }

    /* Shadow adjustments */
    .dark .shadow,
    .dark .shadow-sm,
    .dark .shadow-lg {
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
    }

    /* Custom status badges for dark mode */
    .dark .badge-status {
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .dark .table-position-badge {
      background: linear-gradient(135deg, #334155 0%, #475569 100%);
      border-color: #475569;
      color: #e2e8f0;
    }

    .dark .table-action-btn {
      background: linear-gradient(135deg, #334155 0%, #475569 100%);
      border: 1px solid #475569;
    }

    .dark .table-action-btn:hover {
      background: linear-gradient(135deg, #475569 0%, #64748b 100%);
    }

    /* Scrollbar for dark mode */
    .dark ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    .dark ::-webkit-scrollbar-track {
      background: #1e293b;
    }

    .dark ::-webkit-scrollbar-thumb {
      background: #475569;
      border-radius: 4px;
    }

    .dark ::-webkit-scrollbar-thumb:hover {
      background: #64748b;
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
    [onclick*="handleItemClick"] {
      user-select: none;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
    }

    /* Allow text selection on specific elements inside clickable containers */
    .clickable-row .select-text,
    .clickable-card .select-text,
    [onclick*="handleItemClick"] .select-text {
      user-select: text;
      -webkit-user-select: text;
      -moz-user-select: text;
      -ms-user-select: text;
      cursor: text;
    }

    /* ========================================
       DARK MODE FIX - HIGH CONTRAST ELEMENTS
       For Daftar Dokumen tables and badges
       ======================================== */

    /* Currency/Value Display - HIGH CONTRAST */
    .dark .table tbody td strong,
    .dark .data-table td strong,
    .dark .detail-value,
    .dark .detail-value.highlight,
    .dark .doc-value,
    .dark .stat-value,
    .dark .doc-card-value {
      color: #ffffff !important;
    }

    /* Money/Rupiah values - make them pop */
    .dark .formatted-rupiah,
    .dark .nilai-rupiah {
      color: #4ade80 !important;
      font-weight: 600;
    }

    /* Payment Status Badges - Dark Mode */
    .dark .payment-status-badge.belum-dibayar {
      background: linear-gradient(135deg, #92400e 0%, #78350f 100%);
      color: #fef3c7;
      border-color: #f59e0b;
    }

    .dark .payment-status-badge.siap-dibayar {
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
      color: #dbeafe;
      border-color: #60a5fa;
    }

    .dark .payment-status-badge.sudah-dibayar {
      background: linear-gradient(135deg, #166534 0%, #14532d 100%);
      color: #dcfce7;
      border-color: #4ade80;
    }

    .dark .payment-status-badge small {
      color: #cbd5e1 !important;
    }

    /* Document Age Badges - Dark Mode */
    .dark .document-age-badge.active {
      background: linear-gradient(135deg, #14532d 0%, #166534 100%);
      border-left-color: #4ade80;
    }

    .dark .document-age-badge.active .age-date,
    .dark .document-age-badge.active .age-duration {
      color: #bbf7d0 !important;
    }

    .dark .document-age-badge.active .age-dot {
      background: #4ade80;
      box-shadow: 0 0 8px rgba(74, 222, 128, 0.6);
    }

    .dark .document-age-badge.completed {
      background: linear-gradient(135deg, #334155 0%, #475569 100%);
      border-left-color: #94a3b8;
    }

    .dark .document-age-badge.completed .age-date,
    .dark .document-age-badge.completed .age-duration {
      color: #e2e8f0 !important;
    }

    .dark .document-age-badge.completed .age-dot {
      background: #94a3b8;
    }

    /* Table Data Cells - Ensure Readability */
    .dark .data-table td {
      color: #e2e8f0;
      border-color: #334155;
    }

    .dark .data-table td .text-muted,
    .dark .data-table small.text-muted,
    .dark .table td .text-muted,
    .dark .table td small.text-muted {
      color: #94a3b8 !important;
    }

    /* Detail Items in Modals */
    .dark .detail-item {
      background: #334155;
      border-left-color: #4ade80;
    }

    .dark .detail-label {
      color: #94a3b8 !important;
    }

    .dark .detail-value {
      color: #f8fafc !important;
    }

    /* Search Box & Filters */
    .dark .search-box {
      background: #1e293b;
      border-color: #334155;
    }

    .dark .search-box .input-group-text {
      background: #334155;
      border-color: #475569;
      color: #94a3b8;
    }

    .dark .search-box .form-control {
      background: #1e293b;
      border-color: #475569;
      color: #f1f5f9;
    }

    .dark .search-box .form-control::placeholder {
      color: #64748b;
    }

    .dark .btn-year-select,
    .dark .btn-status-select {
      background: #334155;
      color: #e2e8f0;
      border-color: #475569;
    }

    .dark .btn-year-select:hover,
    .dark .btn-status-select:hover {
      background: #475569;
      border-color: #64748b;
    }

    /* Table Container */
    .dark .table-container,
    .dark .table-wrapper {
      background: #1e293b;
      border-color: #334155;
    }

    /* Empty State */
    .dark .empty-state {
      background: #1e293b;
    }

    .dark .empty-state i {
      color: #475569;
    }

    .dark .empty-state h4 {
      color: #e2e8f0;
    }

    .dark .empty-state p {
      color: #94a3b8;
    }

    /* Modal Custom Styles */
    .dark .modal-content-custom {
      background: #1e293b;
    }

    .dark .modal-body-custom {
      background: #1e293b;
    }

    .dark .modal-footer-custom {
      background: #1e293b;
      border-top-color: #334155;
    }

    /* Column Customization Modal */
    .dark .customization-modal .modal-content-custom {
      background: #1e293b;
    }

    .dark .column-selection-list {
      background: #0f172a;
    }

    .dark .column-item {
      background: #1e293b;
      border-color: #475569;
    }

    .dark .column-item:hover {
      border-color: #60a5fa;
      background: #334155;
    }

    .dark .column-item.selected {
      border-color: #4ade80;
      background: rgba(74, 222, 128, 0.1);
    }

    .dark .column-item-label {
      color: #e2e8f0;
    }

    /* Per Page Select */
    .dark .per-page-select label {
      color: #cbd5e1;
    }

    .dark .per-page-select select {
      background: #334155;
      border-color: #475569;
      color: #f1f5f9;
    }

    /* Stat Cards Additional */
    .dark .stat-card {
      background: #1e293b;
      border-color: #334155;
    }

    .dark .stat-title,
    .dark .stat-label {
      color: #94a3b8;
    }

    .dark .stat-description {
      color: #64748b;
    }

    /* Form Containers */
    .dark .form-container {
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
      border-color: #475569;
    }

    .dark .section-title {
      color: #e2e8f0;
      background: linear-gradient(90deg, rgba(74, 222, 128, 0.1) 0%, transparent 100%);
      border-left-color: #4ade80;
    }

    .dark .info-box {
      background: linear-gradient(135deg, rgba(74, 222, 128, 0.1) 0%, rgba(74, 222, 128, 0.05) 100%);
      border-color: rgba(74, 222, 128, 0.3);
      color: #e2e8f0;
    }

    /* Status Badges Additional */
    .dark .status-badge.proses {
      background: #92400e !important;
      color: #fef3c7 !important;
    }

    .dark .status-badge.selesai {
      background: #166534 !important;
      color: #dcfce7 !important;
    }

    /* Badge Status Text Colors */
    .dark .badge-status {
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Gradient Text Fix for Dark Mode */
    .dark h2 {
      background: linear-gradient(135deg, #4ade80 0%, #34d399 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Pagination Container Dark Mode */
    .dark .pagination-container {
      background: #1e293b;
      border-top-color: #334155;
    }

    /* Action Buttons visibility */
    .dark .btn-action {
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    /* Detail Grid Dark Mode */
    .dark .detail-grid .detail-item {
      background: #334155;
      border-left-color: #4ade80;
    }

    /* Form group labels */
    .dark .form-group label {
      color: #e2e8f0;
    }

    /* Optional labels */
    .dark .optional-label {
      color: #4ade80;
    }

    /* ========================================
       ADDITIONAL DARK MODE FIXES
       For sidebar, buttons, and action icons
       ======================================== */

    /* Secondary Sidebar Menu Items - White Text */
    .dark .secondary-sidebar .nav-link,
    .dark .secondary-sidebar a,
    .dark .secondary-sidebar .menu-item,
    .dark .secondary-sidebar .sidebar-menu a,
    .dark .secondary-sidebar .submenu-item {
      color: #ffffff !important;
    }

    .dark .secondary-sidebar .nav-link:hover,
    .dark .secondary-sidebar a:hover {
      color: #4ade80 !important;
    }

    .dark .secondary-sidebar .nav-link i,
    .dark .secondary-sidebar a i,
    .dark .secondary-sidebar .menu-icon {
      color: #ffffff !important;
    }

    /* Buat Dokumen Button - White Text */
    .dark .btn-create,
    .dark .btn-add-document,
    .dark a.btn-create,
    .dark a[href*="tambahDokumen"],
    .dark .btn-primary-action {
      color: #ffffff !important;
    }

    .dark .btn-create i,
    .dark .btn-add-document i,
    .dark a.btn-create i {
      color: #ffffff !important;
    }

    /* Action Icons/Buttons - White Icons */
    .dark .action-buttons .btn-action i,
    .dark .btn-action i,
    .dark .btn-edit i,
    .dark .btn-send i,
    .dark .btn-tracking i,
    .dark .btn-delete i,
    .dark .btn-view i,
    .dark td .btn i,
    .dark .action-buttons a i,
    .dark .action-buttons button i {
      color: #ffffff !important;
    }

    /* Ensure action button backgrounds stay visible */
    .dark .btn-edit,
    .dark .btn-send,
    .dark .btn-tracking {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%) !important;
      color: #ffffff !important;
    }

    .dark .btn-delete {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
      color: #ffffff !important;
    }

    /* Secondary sidebar active state */
    .dark .secondary-sidebar .nav-link.active,
    .dark .secondary-sidebar a.active {
      background: rgba(74, 222, 128, 0.15) !important;
      color: #4ade80 !important;
    }

    .dark .secondary-sidebar .nav-link.active i,
    .dark .secondary-sidebar a.active i {
      color: #4ade80 !important;
    }

    /* Primary Sidebar Menu Items - White Text */
    .dark .sidebar .nav-link,
    .dark .sidebar a,
    .dark .sidebar .menu-item,
    .dark .sidebar .sidebar-menu a,
    .dark .sidebar span,
    .dark .sidebar-text,
    .dark .menu-text {
      color: #ffffff !important;
    }

    .dark .sidebar .nav-link i,
    .dark .sidebar a i,
    .dark .sidebar .menu-icon,
    .dark .sidebar-icon {
      color: #ffffff !important;
    }

    .dark .sidebar .nav-link:hover,
    .dark .sidebar a:hover {
      color: #4ade80 !important;
    }

    .dark .sidebar .nav-link:hover i,
    .dark .sidebar a:hover i {
      color: #4ade80 !important;
    }

    .dark .sidebar .nav-link.active,
    .dark .sidebar a.active {
      background: rgba(74, 222, 128, 0.15) !important;
      color: #4ade80 !important;
    }

    .dark .sidebar .nav-link.active i,
    .dark .sidebar a.active i {
      color: #4ade80 !important;
    }

    /* App Logo/Title in Sidebar */
    .dark .sidebar .logo-text,
    .dark .sidebar-brand,
    .dark .sidebar h6,
    .dark .sidebar .app-name {
      color: #ffffff !important;
    }

    /* Logout link */
    .dark .sidebar .logout-link,
    .dark .sidebar a[href*="logout"] {
      color: #ffffff !important;
    }

    .dark .sidebar a[href*="logout"]:hover {
      color: #f87171 !important;
    }

    /* ========================================
       COLUMN CUSTOMIZATION MODAL DARK MODE
       ======================================== */

    /* Modal Background */
    .dark .customization-modal,
    .dark .modal-overlay {
      background: rgba(0, 0, 0, 0.8) !important;
    }

    /* Modal Content Container */
    .dark .customization-modal .modal-content-custom,
    .dark .modal-content-custom {
      background: #1e293b !important;
      border: 1px solid #334155 !important;
    }

    /* Modal Header */
    .dark .customization-modal .modal-header-custom,
    .dark .modal-header-custom {
      background: #0f172a !important;
      border-bottom-color: #334155 !important;
    }

    .dark .customization-modal .modal-header-custom h3,
    .dark .modal-header-custom h3,
    .dark .modal-header-custom h4 {
      color: #f1f5f9 !important;
    }

    .dark .customization-modal .modal-header-custom .modal-close,
    .dark .modal-header-custom .modal-close {
      color: #94a3b8 !important;
    }

    .dark .customization-modal .modal-header-custom .modal-close:hover,
    .dark .modal-header-custom .modal-close:hover {
      color: #f87171 !important;
    }

    /* Modal Body */
    .dark .customization-modal .modal-body-custom,
    .dark .modal-body-custom {
      background: #1e293b !important;
    }

    /* Selection Panel */
    .dark .customization-modal .selection-panel {
      background: #0f172a !important;
      border-color: #334155 !important;
    }

    .dark .customization-modal .panel-title {
      color: #f1f5f9 !important;
    }

    .dark .customization-modal .panel-description {
      color: #94a3b8 !important;
    }

    /* Column Selection List */
    .dark .customization-modal .column-selection-list,
    .dark #columnSelectionList {
      background: #1e293b !important;
      border-color: #334155 !important;
    }

    /* Column Items */
    .dark .customization-modal .column-item,
    .dark .column-item {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #f1f5f9 !important;
    }

    .dark .customization-modal .column-item.selected,
    .dark .column-item.selected {
      background: rgba(74, 222, 128, 0.15) !important;
      border-color: #4ade80 !important;
    }

    .dark .customization-modal .column-item-label,
    .dark .column-item-label {
      color: #f1f5f9 !important;
    }

    .dark .customization-modal .column-item-checkbox {
      accent-color: #4ade80;
    }

    /* Preview Panel */
    .dark .customization-modal .preview-panel {
      background: #0f172a !important;
      border-color: #334155 !important;
    }

    .dark .customization-modal .preview-container {
      background: #1e293b !important;
    }

    /* Preview Table */
    .dark .customization-modal .preview-table,
    .dark .preview-table {
      background: #1e293b !important;
    }

    .dark .customization-modal .preview-table thead th,
    .dark .preview-table thead th {
      background: #334155 !important;
      color: #f1f5f9 !important;
    }

    .dark .customization-modal .preview-table tbody td,
    .dark .preview-table tbody td {
      background: #1e293b !important;
      color: #e2e8f0 !important;
      border-bottom-color: #334155 !important;
    }

    .dark .customization-modal .preview-table tbody tr,
    .dark .preview-table tbody tr {
      border-bottom-color: #334155 !important;
    }

    .dark .customization-modal .preview-table tbody tr:hover,
    .dark .preview-table tbody tr:hover {
      background: rgba(255, 255, 255, 0.05) !important;
    }

    /* Empty Preview */
    .dark .customization-modal .empty-preview {
      color: #94a3b8 !important;
    }

    /* Modal Footer */
    .dark .customization-modal .modal-footer-custom,
    .dark .modal-footer-custom {
      background: #0f172a !important;
      border-top-color: #334155 !important;
    }

    .dark .customization-modal .selected-count,
    .dark .selected-count {
      color: #e2e8f0 !important;
    }

    .dark .customization-modal .selected-count small,
    .dark .selected-count small {
      color: #94a3b8 !important;
    }

    /* Modal Action Buttons */
    .dark .btn-modal.btn-cancel {
      background: #334155 !important;
      color: #f1f5f9 !important;
      border-color: #475569 !important;
    }

    .dark .btn-modal.btn-cancel:hover {
      background: #475569 !important;
    }

    .dark .btn-modal.btn-save {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%) !important;
      color: #ffffff !important;
    }

    /* Document Detail Modal Specific */
    .dark .modal-tabs {
      background: #0f172a !important;
      border-bottom-color: #334155 !important;
    }

    .dark .tab-btn {
      color: #94a3b8 !important;
    }

    .dark .tab-btn.active {
      color: #4ade80 !important;
      border-bottom-color: #4ade80 !important;
    }

    .dark .tab-btn:hover {
      color: #f1f5f9 !important;
    }

    /* ========================================
       FORM PAGE DARK MODE (Tambah Dokumen)
       ======================================== */

    /* Form Container */
    .dark .form-container {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
      border-color: #334155 !important;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
    }

    /* Form Title */
    .dark .form-title {
      background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%) !important;
      -webkit-background-clip: text !important;
      -webkit-text-fill-color: transparent !important;
      background-clip: text !important;
    }

    /* Section Title */
    .dark .section-title {
      color: #f1f5f9 !important;
      background: linear-gradient(90deg, rgba(74, 222, 128, 0.1) 0%, transparent 100%) !important;
      border-left-color: #4ade80 !important;
    }

    /* Form Group Labels */
    .dark .form-group label {
      color: #e2e8f0 !important;
    }

    /* Form Inputs, Textareas, Selects */
    .dark .form-group input,
    .dark .form-group textarea,
    .dark .form-group select,
    .dark .form-control,
    .dark input.form-control,
    .dark textarea.form-control,
    .dark select.form-control {
      background-color: #334155 !important;
      border-color: #475569 !important;
      color: #f1f5f9 !important;
    }

    .dark .form-group input::placeholder,
    .dark .form-group textarea::placeholder,
    .dark .form-control::placeholder {
      color: #94a3b8 !important;
    }

    .dark .form-group input:focus,
    .dark .form-group textarea:focus,
    .dark .form-group select:focus,
    .dark .form-control:focus {
      background-color: #3b4d63 !important;
      border-color: #4ade80 !important;
      box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.15) !important;
    }

    .dark .form-group input:hover,
    .dark .form-group textarea:hover,
    .dark .form-group select:hover {
      border-color: #64748b !important;
    }

    /* Vendor/Recipient Field Container */
    .dark .vendor-field-container,
    .dark .dynamic-field {
      background: #1e293b !important;
    }

    /* Add/Remove Field Buttons */
    .dark .add-field-btn {
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
      border-color: #4ade80 !important;
      color: #4ade80 !important;
    }

    .dark .add-field-btn:hover {
      background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%) !important;
      color: #ffffff !important;
    }

    .dark .remove-field-btn {
      background: linear-gradient(135deg, #1e293b 0%, #3d2222 100%) !important;
      border-color: #f87171 !important;
      color: #f87171 !important;
    }

    .dark .remove-field-btn:hover {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
      color: #ffffff !important;
    }

    /* Info Boxes */
    .dark .info-box,
    .dark .alert-info {
      background: linear-gradient(135deg, rgba(74, 222, 128, 0.1) 0%, rgba(74, 222, 128, 0.05) 100%) !important;
      border-color: rgba(74, 222, 128, 0.3) !important;
      color: #e2e8f0 !important;
    }

    /* Submit Button */
    .dark .btn-submit,
    .dark .btn-primary {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%) !important;
      color: #ffffff !important;
      border: none !important;
    }

    .dark .btn-submit:hover,
    .dark .btn-primary:hover {
      background: linear-gradient(135deg, #0a5f52 0%, #083E40 100%) !important;
    }

    /* Cancel Button */
    .dark .btn-cancel,
    .dark .btn-secondary {
      background: #334155 !important;
      color: #f1f5f9 !important;
      border-color: #475569 !important;
    }

    /* Required Field Asterisk */
    .dark .form-group label .required,
    .dark .form-group label span.text-danger {
      color: #f87171 !important;
    }

    /* Optional Label */
    .dark .optional-label,
    .dark .text-muted {
      color: #94a3b8 !important;
    }

    /* Card Headers in Forms */
    .dark .card-header {
      background: #0f172a !important;
      border-bottom-color: #334155 !important;
      color: #f1f5f9 !important;
    }

    /* Card Body */
    .dark .card-body {
      background: #1e293b !important;
    }

    /* Card Footer */
    .dark .card-footer {
      background: #0f172a !important;
      border-top-color: #334155 !important;
    }

    /* Autocomplete Dropdown */
    .dark .autocomplete-dropdown,
    .dark .ui-autocomplete,
    .dark .dropdown-menu {
      background: #1e293b !important;
      border-color: #334155 !important;
    }

    .dark .autocomplete-dropdown li,
    .dark .ui-autocomplete li,
    .dark .dropdown-item {
      color: #e2e8f0 !important;
    }

    .dark .autocomplete-dropdown li:hover,
    .dark .ui-autocomplete li:hover,
    .dark .dropdown-item:hover {
      background: #334155 !important;
      color: #4ade80 !important;
    }

    /* Flatpickr Calendar Dark Mode */
    .dark .flatpickr-calendar {
      background: #1e293b !important;
      border-color: #334155 !important;
    }

    .dark .flatpickr-day {
      color: #e2e8f0 !important;
    }

    .dark .flatpickr-day:hover {
      background: #334155 !important;
    }

    .dark .flatpickr-day.selected {
      background: #4ade80 !important;
      color: #0f172a !important;
    }

    .dark .flatpickr-months {
      background: #0f172a !important;
    }

    .dark .flatpickr-month,
    .dark .flatpickr-current-month {
      color: #f1f5f9 !important;
    }

    .dark .flatpickr-weekday {
      color: #94a3b8 !important;
    }

    /* ========================================
       TRACKING PAGE DARK MODE
       ======================================== */

    /* Filter Card */
    .dark .filter-card {
      background: #1e293b !important;
      border-color: #334155 !important;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3) !important;
    }

    .dark .filter-card .form-label {
      color: #e2e8f0 !important;
    }

    .dark .filter-card .form-control {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #f1f5f9 !important;
    }

    .dark .filter-card .form-control::placeholder {
      color: #94a3b8 !important;
    }

    /* View Toggle Buttons */
    .dark .view-toggle .btn-active {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%) !important;
      color: #ffffff !important;
    }

    .dark .view-toggle .btn-inactive {
      background: #334155 !important;
      color: #94a3b8 !important;
      border-color: #475569 !important;
    }

    .dark .view-toggle .btn-inactive:hover {
      background: #475569 !important;
      color: #f1f5f9 !important;
    }

    /* Document Cards */
    .dark .doc-card {
      background: #1e293b !important;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
    }

    .dark .doc-card:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4) !important;
    }

    .dark .doc-card-header {
      background: #0f172a !important;
      border-bottom-color: #334155 !important;
    }

    /* All text in doc-card should be white/light */
    .dark .doc-card,
    .dark .doc-card * {
      color: #ffffff !important;
    }

    .dark .doc-agenda {
      color: #ffffff !important;
    }

    .dark .doc-spp {
      color: #cbd5e1 !important;
    }

    .dark .doc-nilai {
      color: #ffffff !important;
    }

    .dark .doc-card-body {
      background: #1e293b !important;
    }

    .dark .doc-position {
      color: #ffffff !important;
    }

    .dark .doc-position i {
      color: #4ade80 !important;
    }

    .dark .doc-position span,
    .dark .doc-position strong {
      color: #ffffff !important;
    }


    /* Paid Card Styling */
    .dark .doc-card.paid {
      border-color: #4ade80 !important;
      background: linear-gradient(135deg, rgba(74, 222, 128, 0.08), rgba(34, 197, 94, 0.08)) !important;
    }

    .dark .doc-card.paid .doc-card-header {
      background: rgba(74, 222, 128, 0.15) !important;
    }

    .dark .paid-stamp {
      background: #0f172a !important;
      border-color: #4ade80 !important;
    }

    .dark .paid-stamp::before {
      border-color: #4ade80 !important;
    }

    .dark .paid-stamp i,
    .dark .paid-stamp-text {
      color: #4ade80 !important;
    }

    /* Progress Section */
    .dark .progress-section {
      background: transparent !important;
    }

    .dark .progress-title {
      color: #e2e8f0 !important;
    }

    .dark .progress-title i {
      color: #4ade80 !important;
    }

    /* Step Circles */
    .dark .step-circle.pending {
      background: #475569 !important;
      color: #94a3b8 !important;
    }

    .dark .step-circle.completed {
      background: linear-gradient(135deg, #4ade80, #22c55e) !important;
      color: #0f172a !important;
    }

    .dark .step-circle.current {
      background: linear-gradient(135deg, #083E40, #0a5f52) !important;
      color: #ffffff !important;
      box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.3) !important;
    }

    /* Step Labels */
    .dark .step-label {
      color: #94a3b8 !important;
    }

    .dark .step-label.current {
      color: #4ade80 !important;
    }

    /* Progress Line */
    .dark .progress-line {
      background: #475569 !important;
    }

    .dark .progress-line-fill {
      background: linear-gradient(90deg, #4ade80, #22c55e) !important;
    }

    /* Tracking Table */
    .dark .tracking-table-card {
      background: #1e293b !important;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3) !important;
    }

    .dark .tracking-table tbody tr {
      border-bottom-color: #334155 !important;
    }

    .dark .tracking-table tbody tr:hover {
      background: rgba(74, 222, 128, 0.08) !important;
    }

    .dark .tracking-table tbody td {
      background: #1e293b !important;
      color: #e2e8f0 !important;
      border-right-color: #334155 !important;
    }

    .dark .doc-agenda-cell {
      color: #ffffff !important;
    }

    .dark .doc-agenda-cell a {
      color: #ffffff !important;
    }

    .dark .doc-spp-cell {
      color: #ffffff !important;
    }

    .dark .doc-nilai-cell {
      color: #ffffff !important;
    }

    /* Table Position Badge */
    .dark .table-position-badge {
      background: linear-gradient(135deg, #334155 0%, #475569 100%) !important;
      color: #ffffff !important;
      border-color: #64748b !important;
    }

    .dark .table-position-badge i {
      color: #ffffff !important;
    }

    /* Table Action Buttons */
    .dark .tracking-table .btn-outline-primary,
    .dark .tracking-table .btn-primary {
      color: #ffffff !important;
      border-color: #4ade80 !important;
    }

    .dark .tracking-table .btn-outline-primary i,
    .dark .tracking-table .btn-primary i,
    .dark .tracking-table td a i,
    .dark .tracking-table td button i {
      color: #ffffff !important;
    }


    /* Outline Buttons in Dark Mode */
    .dark .btn-outline-secondary {
      color: #e2e8f0 !important;
      border-color: #475569 !important;
    }

    .dark .btn-outline-secondary:hover {
      background: #334155 !important;
      color: #f1f5f9 !important;
    }

    .dark .btn-outline-warning {
      color: #fbbf24 !important;
      border-color: #fbbf24 !important;
    }

    .dark .btn-outline-warning:hover {
      background: #fbbf24 !important;
      color: #0f172a !important;
    }

    .dark .btn-outline-info {
      color: #38bdf8 !important;
      border-color: #38bdf8 !important;
    }

    .dark .btn-outline-info:hover {
      background: #38bdf8 !important;
      color: #0f172a !important;
    }

    .dark .btn-outline-success {
      color: #4ade80 !important;
      border-color: #4ade80 !important;
    }

    .dark .btn-outline-success:hover {
      background: #4ade80 !important;
      color: #0f172a !important;
    }

    /* Empty State Styling */
    .dark .tracking-container .text-muted {
      color: #94a3b8 !important;
    }

    .dark .tracking-container h5.text-muted {
      color: #e2e8f0 !important;
    }

    /* ========================================
       DASHBOARD PAGE DARK MODE
       ======================================== */

    /* Dashboard Header */
    .dark .container-fluid h2 {
      color: #ffffff !important;
    }

    .dark .container-fluid h2 i {
      color: #4ade80 !important;
    }

    .dark .container-fluid .text-muted {
      color: #94a3b8 !important;
    }

    /* Stats Cards */
    .dark .container-fluid .card {
      background: #1e293b !important;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
    }

    .dark .container-fluid .card-body {
      background: #1e293b !important;
    }

    .dark .container-fluid .card h3,
    .dark .container-fluid .card h5 {
      color: #ffffff !important;
    }

    .dark .container-fluid .card .text-muted {
      color: #94a3b8 !important;
    }

    /* Action Cards Icons - keep green for action cards */
    .dark .container-fluid .card .card-body.text-center i {
      color: #4ade80 !important;
    }

    /* Stats Cards Icons - keep white for icons with colored backgrounds */
    .dark .container-fluid .card .card-body .d-flex i.text-white {
      color: #ffffff !important;
      /* White color for icons with colored bg */
    }



    /* Card Header for Recent Documents */
    .dark .container-fluid .card-header {
      background: #0f172a !important;
      border-bottom-color: #334155 !important;
    }

    .dark .container-fluid .card-header h5 {
      color: #ffffff !important;
    }

    /* Table in Dashboard */
    .dark .container-fluid .table {
      background: #1e293b !important;
    }

    .dark .container-fluid .table thead {
      background: #0f172a !important;
    }

    .dark .container-fluid .table thead th {
      background: #0f172a !important;
      color: #94a3b8 !important;
      border-color: #334155 !important;
    }

    .dark .container-fluid .table tbody tr {
      background: #1e293b !important;
      border-color: #334155 !important;
    }

    .dark .container-fluid .table tbody tr:hover {
      background: rgba(74, 222, 128, 0.08) !important;
    }

    .dark .container-fluid .table tbody td {
      color: #ffffff !important;
      border-color: #334155 !important;
    }

    .dark .container-fluid .table tbody td strong {
      color: #ffffff !important;
    }

    /* Fix table hover border */
    .dark .table-hover>tbody>tr:hover>* {
      background: rgba(74, 222, 128, 0.08) !important;
    }

    /* ========================================
       MODAL POPUP DARK MODE
       ======================================== */

    /* Modal Content */
    .dark .modal-content {
      background: #1e293b !important;
      border-color: #334155 !important;
      color: #ffffff !important;
    }

    /* Modal Header */
    .dark .modal-header {
      background: #0f172a !important;
      border-bottom-color: #334155 !important;
    }

    .dark .modal-header .modal-title,
    .dark .modal-header h5 {
      color: #ffffff !important;
    }

    .dark .modal-header .btn-close {
      filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* Modal Body */
    .dark .modal-body {
      background: #1e293b !important;
      color: #ffffff !important;
    }

    .dark .modal-body * {
      color: #ffffff !important;
    }

    /* Modal Footer */
    .dark .modal-footer {
      background: #0f172a !important;
      border-top-color: #334155 !important;
    }

    /* Tabs in Modal */
    .dark .modal .nav-tabs {
      border-bottom-color: #334155 !important;
    }

    .dark .modal .nav-tabs .nav-link {
      color: #94a3b8 !important;
      border-color: transparent !important;
    }

    .dark .modal .nav-tabs .nav-link:hover {
      color: #ffffff !important;
      border-color: #475569 !important;
    }

    .dark .modal .nav-tabs .nav-link.active {
      background: #334155 !important;
      color: #4ade80 !important;
      border-color: #334155 !important;
    }

    /* Tab Content */
    .dark .modal .tab-content {
      background: #1e293b !important;
    }

    /* Cards inside Modal */
    .dark .modal .card {
      background: #334155 !important;
      border-color: #475569 !important;
    }

    .dark .modal .card-body {
      background: #334155 !important;
    }

    /* Form Controls in Modal */
    .dark .modal .form-control,
    .dark .modal .form-select {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #ffffff !important;
    }

    .dark .modal .form-control::placeholder {
      color: #94a3b8 !important;
    }

    .dark .modal .form-control:disabled,
    .dark .modal .form-control[readonly] {
      background: #1e293b !important;
      color: #94a3b8 !important;
    }

    /* Labels in Modal */
    .dark .modal label,
    .dark .modal .form-label {
      color: #e2e8f0 !important;
    }

    /* Info Boxes/Cards in Modal */
    .dark .modal .info-box,
    .dark .modal .detail-box,
    .dark .modal .bg-light {
      background: #334155 !important;
      border-color: #475569 !important;
    }

    /* Badge in Modal */
    .dark .modal .badge {
      color: #ffffff !important;
    }

    .dark .modal .badge.bg-warning {
      background: #f59e0b !important;
      color: #0f172a !important;
    }

    .dark .modal .badge.bg-success {
      background: #22c55e !important;
      color: #0f172a !important;
    }

    .dark .modal .badge.bg-danger {
      background: #ef4444 !important;
      color: #ffffff !important;
    }

    .dark .modal .badge.bg-info {
      background: #06b6d4 !important;
      color: #0f172a !important;
    }

    .dark .modal .badge.bg-primary {
      background: #3b82f6 !important;
      color: #ffffff !important;
    }

    /* Text colors in Modal */
    .dark .modal .text-muted {
      color: #94a3b8 !important;
    }

    .dark .modal .text-dark {
      color: #ffffff !important;
    }

    .dark .modal h6,
    .dark .modal h5,
    .dark .modal h4 {
      color: #ffffff !important;
    }

    /* Borders in Modal */
    .dark .modal .border,
    .dark .modal .border-bottom,
    .dark .modal .border-top {
      border-color: #475569 !important;
    }

    /* Icons in Modal */
    .dark .modal i {
      color: #4ade80 !important;
    }

    /* Rounded boxes in Modal */
    .dark .modal .rounded,
    .dark .modal .rounded-3 {
      background: #334155 !important;
    }

    /* Small text in Modal */
    .dark .modal small,
    .dark .modal .small {
      color: #94a3b8 !important;
    }

    /* Links in Modal */
    .dark .modal a {
      color: #4ade80 !important;
    }

    .dark .modal a:hover {
      color: #22c55e !important;
    }

    /* ========================================
       CUSTOM MODAL (Document Detail Popup) DARK MODE
       ======================================== */

    /* Modal Overlay */
    .dark .modal-overlay {
      background: rgba(0, 0, 0, 0.8) !important;
    }

    /* Custom Modal Content */
    .dark .modal-content-custom {
      background: #1e293b !important;
      border-color: #334155 !important;
      color: #ffffff !important;
    }

    /* Custom Modal Header */
    .dark .modal-header-custom {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
      border-bottom-color: #334155 !important;
    }

    .dark .modal-header-custom h4,
    .dark .modal-header-custom .header-text h4 {
      color: #ffffff !important;
    }

    .dark .modal-header-custom .header-icon {
      background: rgba(74, 222, 128, 0.2) !important;
      color: #4ade80 !important;
    }

    .dark .modal-header-custom .header-icon i {
      color: #4ade80 !important;
    }

    .dark .modal-header-custom .doc-id,
    .dark .modal-header-custom span {
      color: #94a3b8 !important;
    }

    .dark .modal-header-custom .modal-close {
      background: rgba(255, 255, 255, 0.1) !important;
      color: #ffffff !important;
      border-color: #475569 !important;
    }

    .dark .modal-header-custom .modal-close:hover {
      background: rgba(239, 68, 68, 0.2) !important;
      color: #ef4444 !important;
    }

    /* Status Pill */
    .dark .status-pill {
      background: #334155 !important;
      color: #ffffff !important;
    }

    /* Modal Tabs */
    .dark .modal-tabs {
      background: #0f172a !important;
      border-bottom-color: #334155 !important;
    }

    .dark .modal-tabs .tab-btn {
      background: transparent !important;
      color: #94a3b8 !important;
      border-color: transparent !important;
    }

    .dark .modal-tabs .tab-btn:hover {
      background: rgba(74, 222, 128, 0.1) !important;
      color: #ffffff !important;
    }

    .dark .modal-tabs .tab-btn.active {
      background: #4ade80 !important;
      color: #0f172a !important;
    }

    .dark .modal-tabs .tab-btn i {
      color: inherit !important;
    }

    /* Custom Modal Body */
    .dark .modal-body-custom {
      background: #1e293b !important;
      color: #ffffff !important;
    }

    /* Tab Content */
    .dark .tab-content {
      background: #1e293b !important;
      color: #ffffff !important;
    }

    /* Stats Row and Stat Cards */
    .dark .stats-row {
      background: transparent !important;
    }

    .dark .stat-card {
      background: #334155 !important;
      border-color: #475569 !important;
    }

    .dark .stat-card .stat-icon {
      background: rgba(74, 222, 128, 0.2) !important;
    }

    .dark .stat-card .stat-icon i {
      color: #4ade80 !important;
    }

    .dark .stat-card .stat-label {
      color: #94a3b8 !important;
    }

    .dark .stat-card .stat-value {
      color: #ffffff !important;
    }

    .dark .stat-card.primary {
      border-left-color: #3b82f6 !important;
    }

    .dark .stat-card.primary .stat-icon {
      background: rgba(59, 130, 246, 0.2) !important;
    }

    .dark .stat-card.primary .stat-icon i {
      color: #3b82f6 !important;
    }

    .dark .stat-card.success {
      border-left-color: #22c55e !important;
    }

    .dark .stat-card.success .stat-icon {
      background: rgba(34, 197, 94, 0.2) !important;
    }

    .dark .stat-card.success .stat-icon i {
      color: #22c55e !important;
    }

    .dark .stat-card.info {
      border-left-color: #f59e0b !important;
    }

    .dark .stat-card.info .stat-icon {
      background: rgba(245, 158, 11, 0.2) !important;
    }

    .dark .stat-card.info .stat-icon i {
      color: #f59e0b !important;
    }

    /* Detail Sections */
    .dark .detail-section {
      background: #0f172a !important;
      border-color: #334155 !important;
      border-radius: 8px;
      margin-bottom: 16px;
    }

    .dark .section-header {
      background: transparent !important;
      border-bottom-color: #334155 !important;
    }

    .dark .section-header h5 {
      color: #ffffff !important;
    }

    .dark .section-header i {
      color: #4ade80 !important;
    }

    /* Section Grid */
    .dark .section-grid {
      background: transparent !important;
    }

    /* Info Cards */
    .dark .info-card {
      background: #334155 !important;
      border-color: #475569 !important;
      border-radius: 8px;
    }

    .dark .info-card .info-label {
      color: #94a3b8 !important;
    }

    .dark .info-card .info-value {
      color: #ffffff !important;
    }

    /* Modal Footer */
    .dark .modal-footer-custom {
      background: #0f172a !important;
      border-top-color: #334155 !important;
    }

    /* Uraian Box */
    .dark .uraian-box,
    .dark .description-box {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #e2e8f0 !important;
    }

    /* All text in custom modal should be visible */
    .dark .modal-content-custom *,
    .dark .modal-body-custom * {
      color: #ffffff;
    }

    .dark .modal-content-custom .info-label,
    .dark .modal-body-custom .info-label,
    .dark .modal-content-custom .stat-label,
    .dark .modal-body-custom .stat-label {
      color: #94a3b8 !important;
    }

    /* Section Header Icons - Make them white */
    .dark .section-header i,
    .dark .detail-section .section-header i,
    .dark .modal-content-custom .section-header i,
    .dark .modal-body-custom .section-header i {
      color: #ffffff !important;
    }

    /* Vendor Info Card - Fix white background */
    .dark .vendor-card,
    .dark .vendor-info-card,
    .dark .vendor-box,
    .dark .info-vendor,
    .dark [class*="vendor"] {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #ffffff !important;
    }

    .dark .vendor-card *,
    .dark .vendor-info-card *,
    .dark .vendor-box *,
    .dark .info-vendor *,
    .dark [class*="vendor"] * {
      color: #ffffff !important;
    }

    .dark .vendor-card .vendor-icon,
    .dark .vendor-info-card .vendor-icon,
    .dark [class*="vendor"] .vendor-icon {
      background: rgba(74, 222, 128, 0.2) !important;
    }

    .dark .vendor-card .vendor-icon i,
    .dark .vendor-info-card .vendor-icon i,
    .dark [class*="vendor"] i {
      color: #4ade80 !important;
    }

    /* Any remaining white backgrounds in modal */
    .dark .modal-content-custom .bg-white,
    .dark .modal-body-custom .bg-white,
    .dark .modal-content-custom .bg-light,
    .dark .modal-body-custom .bg-light {
      background: #334155 !important;
    }

    /* Kategori & Klasifikasi section */
    .dark .kategori-card,
    .dark .klasifikasi-card,
    .dark [class*="kategori"],
    .dark [class*="klasifikasi"] {
      background: #334155 !important;
      border-color: #475569 !important;
    }

    /* Data SPK, Berita Acara, PO & MIRO sections */
    .dark .spk-card,
    .dark .berita-acara-card,
    .dark .po-miro-card,
    .dark [class*="spk-"],
    .dark [class*="acara"],
    .dark [class*="miro"] {
      background: #334155 !important;
      border-color: #475569 !important;
    }

    /* All input boxes in modal */
    .dark .modal-content-custom input,
    .dark .modal-body-custom input,
    .dark .modal-content-custom textarea,
    .dark .modal-body-custom textarea,
    .dark .modal-content-custom select,
    .dark .modal-body-custom select {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #ffffff !important;
    }

    /* Fix any cards that still have white background - catch all */
    .dark .modal-content-custom .card,
    .dark .modal-body-custom .card,
    .dark .modal-content-custom [style*="background: white"],
    .dark .modal-body-custom [style*="background: white"],
    .dark .modal-content-custom [style*="background:#fff"],
    .dark .modal-body-custom [style*="background:#fff"] {
      background: #334155 !important;
      border-color: #475569 !important;
    }

    /* Fix inline styles that might override */
    .dark .modal-content-custom .rounded,
    .dark .modal-body-custom .rounded,
    .dark .modal-content-custom .rounded-lg,
    .dark .modal-body-custom .rounded-lg,
    .dark .modal-content-custom .rounded-3,
    .dark .modal-body-custom .rounded-3 {
      background: #334155 !important;
    }

    /* Terbilang/Description text */
    .dark .terbilang,
    .dark .nilai-terbilang {
      color: #94a3b8 !important;
    }

    /* ========================================
       VENDOR CARD SPECIFIC DARK MODE
       ======================================== */

    /* Vendor Card - more specific */
    .dark .vendor-card {
      background: #334155 !important;
      border: 1px solid #475569 !important;
    }

    .dark .vendor-card .vendor-icon {
      background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%) !important;
    }

    .dark .vendor-card .vendor-icon i {
      color: #0f172a !important;
    }

    .dark .vendor-card .vendor-info {
      color: #ffffff !important;
    }

    .dark .vendor-card .vendor-label {
      color: #94a3b8 !important;
    }

    .dark .vendor-card .vendor-name {
      color: #ffffff !important;
    }

    /* Section Header Icons - ensure white bg with icon */
    .dark .section-header i {
      background: #4ade80 !important;
      color: #0f172a !important;
    }

    /* Info Card Highlight */
    .dark .info-card.highlight {
      background: #334155 !important;
      border-color: #475569 !important;
    }

    .dark .info-card.highlight .info-value.tag {
      background: rgba(74, 222, 128, 0.2) !important;
      color: #4ade80 !important;
    }

    /* Uraian Box Dark Mode */
    .dark .uraian-box {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #e2e8f0 !important;
    }

    /* Money Display - keep gradient but adjust for dark */
    .dark .money-display {
      background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
    }

    .dark .money-display .money-amount {
      color: #ffffff !important;
    }

    .dark .money-display .money-words {
      color: rgba(255, 255, 255, 0.85) !important;
    }

    /* Mono text in info-value */
    .dark .info-value.mono {
      color: #ffffff !important;
      font-family: monospace;
    }

    /* ========================================
       PAGE HEADER DARK MODE
       ======================================== */

    /* Page Header Title and Icon */
    .dark .container-fluid h2,
    .dark .container-fluid h1,
    .dark .container-fluid h3,
    .dark .content-header h2,
    .dark .content-header h1 {
      color: #ffffff !important;
    }

    .dark .container-fluid h2 i,
    .dark .container-fluid h1 i,
    .dark .container-fluid h3 i,
    .dark .content-header h2 i,
    .dark .content-header h1 i {
      color: #ffffff !important;
    }

    /* Subtitle/Description under header */
    .dark .container-fluid h2+p,
    .dark .container-fluid h2~small,
    .dark .container-fluid .text-muted,
    .dark .content-header .text-muted,
    .dark .content-header p {
      color: #94a3b8 !important;
    }

    /* Any page title with icon */
    .dark h2 i,
    .dark h1 i,
    .dark h3 i {
      color: #ffffff !important;
    }

    .dark h2,
    .dark h1,
    .dark h3 {
      color: #ffffff !important;
    }

    /* ========================================
       SWEETALERT2 DARK MODE
       ======================================== */

    /* SweetAlert2 popup container */
    .dark .swal2-popup {
      background: #1e293b !important;
      color: #ffffff !important;
    }

    /* SweetAlert2 title */
    .dark .swal2-title {
      color: #ffffff !important;
    }

    /* SweetAlert2 content/html content */
    .dark .swal2-html-container,
    .dark .swal2-content {
      color: #94a3b8 !important;
    }

    /* SweetAlert2 close button */
    .dark .swal2-close {
      color: #94a3b8 !important;
    }

    .dark .swal2-close:hover {
      color: #ffffff !important;
    }

    /* SweetAlert2 icon colors - keep the original colors but adjust background */
    .dark .swal2-icon.swal2-info {
      border-color: #4299e1 !important;
      color: #4299e1 !important;
    }

    .dark .swal2-icon.swal2-warning {
      border-color: #f6ad55 !important;
      color: #f6ad55 !important;
    }

    .dark .swal2-icon.swal2-error {
      border-color: #f56565 !important;
    }

    .dark .swal2-icon.swal2-success {
      border-color: #48bb78 !important;
    }

    .dark .swal2-icon.swal2-success .swal2-success-ring {
      border-color: rgba(72, 187, 120, 0.3) !important;
    }

    .dark .swal2-icon.swal2-success [class^='swal2-success-line'] {
      background-color: #48bb78 !important;
    }

    /* SweetAlert2 confirm button */
    .dark .swal2-confirm {
      background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%) !important;
      color: #ffffff !important;
    }

    .dark .swal2-confirm:hover {
      background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%) !important;
    }

    /* SweetAlert2 cancel button */
    .dark .swal2-cancel {
      background: #475569 !important;
      color: #ffffff !important;
    }

    .dark .swal2-cancel:hover {
      background: #334155 !important;
    }

    /* SweetAlert2 deny button */
    .dark .swal2-deny {
      background: #f56565 !important;
      color: #ffffff !important;
    }

    /* SweetAlert2 input fields */
    .dark .swal2-input,
    .dark .swal2-textarea,
    .dark .swal2-select {
      background: #0f172a !important;
      border-color: #475569 !important;
      color: #ffffff !important;
    }

    .dark .swal2-input:focus,
    .dark .swal2-textarea:focus,
    .dark .swal2-select:focus {
      border-color: #4299e1 !important;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.3) !important;
    }

    /* SweetAlert2 footer */
    .dark .swal2-footer {
      border-top-color: #334155 !important;
      color: #94a3b8 !important;
    }

    /* SweetAlert2 timer progress bar */
    .dark .swal2-timer-progress-bar {
      background: #4299e1 !important;
    }

    /* SweetAlert2 backdrop - slightly darker */
    .dark .swal2-backdrop-show {
      background: rgba(0, 0, 0, 0.7) !important;
    }

    /* ========================================
       OWNER SIDEBAR - Fixed 240px Modern Design
       ======================================== */
    body.owner-layout {
      --modern-sidebar-width: 240px;
      --modern-content-width: calc(100vw - var(--modern-sidebar-width));
      overflow-x: hidden;
    }

    .sidebar-owner {
      width: var(--modern-sidebar-width) !important;
      background: #ffffff;
      border-right: 1px solid #e8ecf4;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 1000;
      padding: 0 0 16px;
      overflow: hidden;
      font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
      transition: width 0.22s ease, background-color 0.3s ease, border-color 0.3s ease;
    }


    .dark .sidebar-owner {
      background: #1e293b;
      border-right-color: #334155;
    }

    /* Owner sidebar logo */
    .owner-sidebar-logo {
      display: flex; align-items: center; gap: 10px;
      padding: 20px 52px 16px 20px;
      border-bottom: 1px solid #e8ecf4;
      position: relative;
      min-height: 82px;
    }
    .dark .owner-sidebar-logo { border-bottom-color: #334155; }
    .owner-logo-icon {
      width: 44px; height: 44px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      padding: 4px;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }
    .owner-logo-icon img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .dark .owner-logo-icon { background: #f8fafc; border-color: #475569; }
    .owner-logo-text { font-family: 'Sora', 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 13px; color: #1a2340; line-height: 1.2; }
    .owner-logo-sub { font-size: 10px; color: #a0aec0; font-weight: 400; }
    .dark .owner-logo-text { color: #f1f5f9; }
    .dark .owner-logo-sub { color: #94a3b8; }
    .owner-sidebar-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 32px;
      height: 32px;
      border: 1px solid #dbe3ef;
      border-radius: 10px;
      background: #ffffff;
      color: #64748b;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
      transition: background-color .16s ease, color .16s ease, border-color .16s ease, transform .16s ease;
    }
    .owner-sidebar-toggle:hover {
      background: #eff4ff;
      border-color: #bfdbfe;
      color: #2563eb;
    }
    .owner-sidebar-toggle:focus-visible {
      outline: 2px solid #2563eb;
      outline-offset: 2px;
    }
    .owner-sidebar-toggle i { font-size: 13px; line-height: 1; }
    .owner-sidebar-toggle .owner-toggle-expand { display: none; }
    .dark .owner-sidebar-toggle {
      background: #1f2937;
      border-color: #334155;
      color: #cbd5e1;
    }

    /* Owner sidebar nav section */
    .owner-sidebar-section { padding: 16px 12px 4px; }
    .owner-sidebar-label {
      font-size: 10px; font-weight: 600; color: #a0aec0;
      letter-spacing: .08em; text-transform: uppercase;
      padding: 0 8px 8px;
    }
    .dark .owner-sidebar-label { color: #94a3b8; }

    .sidebar-owner .owner-nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 10px; border-radius: 9px;
      color: #6b7a99; font-weight: 500; font-size: 13px;
      cursor: pointer; transition: all .15s; margin-bottom: 2px;
      text-decoration: none; white-space: nowrap;
    }
    .sidebar-owner .owner-nav-item:hover { background: #f4f6fb; color: #1a2340; }
    .sidebar-owner .owner-nav-item.active { background: #eff4ff; color: #2563eb; font-weight: 600; }
    .sidebar-owner .owner-nav-item i { width: 16px; font-size: 14px; text-align: center; flex-shrink: 0; }
    .dark .sidebar-owner .owner-nav-item { color: #cbd5e1; }
    .dark .sidebar-owner .owner-nav-item:hover { background: #334155; color: #f1f5f9; }
    .dark .sidebar-owner .owner-nav-item.active { background: rgba(37, 99, 235, 0.15); color: #60a5fa; }

    .sidebar-owner .bagian-dot-nav {
      width: 8px; height: 8px; border-radius: 50%;
      display: inline-block; flex-shrink: 0;
    }

    /* Owner sidebar bottom user card */
    .owner-sidebar-bottom {
      margin-top: auto; padding: 12px;
      border-top: 1px solid #e8ecf4;
    }
    .dark .owner-sidebar-bottom { border-top-color: #334155; }
    .owner-user-actions {
      display: flex; align-items: center; gap: 8px;
    }
    .owner-user-card {
      display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;
      padding: 10px; border-radius: 10px;
      background: #f4f6fb; cursor: pointer; text-decoration: none;
      color: inherit; transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .owner-user-card:hover {
      background: #eff4ff; box-shadow: 0 8px 18px rgba(37, 99, 235, 0.08);
    }
    .owner-user-card:focus-visible,
    .owner-logout-btn:focus-visible {
      outline: 2px solid #2563eb; outline-offset: 2px;
    }
    .dark .owner-user-card { background: #334155; }
    .dark .owner-user-card:hover { background: rgba(37, 99, 235, 0.18); }
    .owner-avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: linear-gradient(135deg, #0f766e, #10b981);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700; color: white; flex-shrink: 0;
      overflow: hidden;
    }
    .owner-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .owner-user-info { flex: 1; min-width: 0; }
    .owner-user-name { font-weight: 600; font-size: 12.5px; color: #1a2340; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .owner-user-role-text { font-size: 11px; color: #a0aec0; }
    .dark .owner-user-name { color: #f1f5f9; }
    .dark .owner-user-role-text { color: #94a3b8; }
    .owner-profile-icon {
      width: 14px; height: 14px; color: #8da0bd; flex-shrink: 0;
    }
    .owner-logout-btn {
      width: 40px; height: 40px; border: 1px solid #e8ecf4; border-radius: 10px;
      display: inline-flex; align-items: center; justify-content: center;
      background: #ffffff; color: #94a3b8; cursor: pointer;
      transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
      flex-shrink: 0;
    }
    .owner-logout-btn:hover {
      background: #fef2f2; border-color: #fecaca; color: #ef4444;
      box-shadow: 0 8px 18px rgba(239, 68, 68, 0.08);
    }
    .owner-logout-btn svg { width: 15px; height: 15px; }
    .dark .owner-logout-btn {
      background: #1f2937; border-color: #334155; color: #94a3b8;
    }
    .dark .owner-logout-btn:hover {
      background: rgba(239, 68, 68, 0.12); border-color: rgba(239, 68, 68, 0.32); color: #f87171;
    }

    /* Owner content area — wider margin for fixed sidebar */
    body.owner-layout .content {
      margin-left: var(--modern-sidebar-width) !important;
      width: var(--modern-content-width);
      max-width: var(--modern-content-width);
      min-width: 0;
      box-sizing: border-box;
      overflow-x: clip;
      transition: margin-left 0.22s ease, width 0.22s ease, max-width 0.22s ease;
    }
    body.owner-layout .topbar {
      margin-left: var(--modern-sidebar-width) !important;
      width: var(--modern-content-width);
      max-width: var(--modern-content-width);
      box-sizing: border-box;
      transition: margin-left 0.22s ease, width 0.22s ease, max-width 0.22s ease;
    }

    .sidebar-collapsed body.owner-layout {
      --modern-sidebar-width: 84px;
    }

    .sidebar-collapsed body.owner-layout .sidebar-owner {
      width: var(--modern-sidebar-width) !important;
    }
    .sidebar-collapsed body.owner-layout .owner-sidebar-logo {
      padding: 16px 8px 14px 10px;
      gap: 0;
    }
    .sidebar-collapsed body.owner-layout .owner-logo-icon {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      padding: 3px;
    }
    .sidebar-collapsed body.owner-layout .owner-logo-text,
    .sidebar-collapsed body.owner-layout .owner-logo-sub,
    .sidebar-collapsed body.owner-layout .owner-sidebar-label,
    .sidebar-collapsed body.owner-layout .owner-user-info,
    .sidebar-collapsed body.owner-layout .owner-profile-icon {
      display: none;
    }
    .sidebar-collapsed body.owner-layout .owner-sidebar-toggle {
      right: 7px;
      width: 30px;
      height: 30px;
    }
    .sidebar-collapsed body.owner-layout .owner-sidebar-toggle .owner-toggle-collapse { display: none; }
    .sidebar-collapsed body.owner-layout .owner-sidebar-toggle .owner-toggle-expand { display: inline-block; }
    .sidebar-collapsed body.owner-layout .owner-sidebar-section {
      padding: 12px 8px 4px;
    }
    .sidebar-collapsed body.owner-layout .sidebar-owner .owner-nav-item {
      justify-content: center;
      padding: 12px 10px;
      font-size: 0;
      gap: 0;
    }
    .sidebar-collapsed body.owner-layout .sidebar-owner .owner-nav-item svg {
      width: 20px !important;
      height: 20px !important;
    }
    .sidebar-collapsed body.owner-layout .owner-sidebar-bottom {
      padding: 10px 8px;
    }
    .sidebar-collapsed body.owner-layout .owner-user-actions {
      flex-direction: column;
      gap: 8px;
    }
    .sidebar-collapsed body.owner-layout .owner-user-card {
      justify-content: center;
      flex: 0 0 auto;
      width: 44px;
      height: 44px;
      padding: 0;
    }
    .sidebar-collapsed body.owner-layout .owner-logout-btn {
      width: 40px;
      height: 40px;
    }
    .sidebar-collapsed body.owner-layout .content,
    .sidebar-collapsed body.owner-layout .topbar {
      margin-left: var(--modern-sidebar-width) !important;
    }

    @media (max-width: 768px) {
      body.owner-layout {
        --modern-sidebar-width: 72px;
      }

      body.owner-layout .sidebar-owner {
        width: var(--modern-sidebar-width) !important;
      }
      body.owner-layout .owner-sidebar-logo {
        justify-content: center; padding: 16px 0 12px;
      }
      body.owner-layout .owner-sidebar-toggle {
        display: none;
      }
      body.owner-layout .owner-logo-text,
      body.owner-layout .owner-logo-sub,
      body.owner-layout .owner-sidebar-label,
      body.owner-layout .owner-user-info,
      body.owner-layout .owner-profile-icon {
        display: none;
      }
      body.owner-layout .owner-sidebar-section {
        padding: 12px 8px 4px;
      }
      body.owner-layout .sidebar-owner .owner-nav-item {
        justify-content: center; padding: 12px 10px; font-size: 0;
      }
      body.owner-layout .sidebar-owner .owner-nav-item svg {
        width: 20px !important; height: 20px !important;
      }
      body.owner-layout .owner-sidebar-bottom {
        padding: 10px 8px;
      }
      body.owner-layout .owner-user-actions {
        flex-direction: column; gap: 8px;
      }
      body.owner-layout .owner-user-card {
        justify-content: center; flex: 0 0 auto; width: 44px; height: 44px; padding: 0;
      }
      body.owner-layout .owner-logout-btn {
        width: 40px; height: 40px;
      }
      body.owner-layout .content,
      body.owner-layout .topbar {
        margin-left: var(--modern-sidebar-width) !important;
      }
    }
  </style>














  <!-- Smart Autocomplete CSS -->
  <link href="{{ asset('css/smart-autocomplete.css') }}" rel="stylesheet">

  <!-- Stack for additional styles from views -->
  @stack('styles')

  <!-- Dark Mode Toggle Script - Run Immediately -->
  <script>
    (function () {
      try {
        if (localStorage.getItem('sidebar_collapsed') === '1') {
          document.documentElement.classList.add('sidebar-collapsed');
        }
      } catch (error) {
        // Ignore storage failures; sidebar will use expanded mode.
      }

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

      // Run immediately if DOM is alrea dy loaded
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
      } else {
        initThemeToggle();
      }
    })();
  </script>
</head>

@php
  $layoutUserRoleLower = auth()->check() ? strtolower(auth()->user()->role ?? '') : '';
  $layoutModuleLower = strtolower($module ?? '');
  $modernWorkflowRoles = ['team_verifikasi', 'verifikasi', 'perpajakan', 'akutansi', 'akuntansi'];
  $modernWorkflowModules = ['team_verifikasi', 'perpajakan', 'akutansi'];
  $usesOwnerShell = in_array($layoutUserRoleLower, ['owner', 'admin'], true);
  $usesOperatorShell = $layoutUserRoleLower === 'operator' || $layoutModuleLower === 'operator';
  $usesPaymentShell = $layoutUserRoleLower === 'pembayaran' || (($module ?? null) === 'pembayaran');
  $usesWorkflowShell = in_array($layoutUserRoleLower, $modernWorkflowRoles, true) || in_array($layoutModuleLower, $modernWorkflowModules, true);
  $usesModernSidebarShell = $usesOwnerShell || $usesOperatorShell || $usesPaymentShell || $usesWorkflowShell;
@endphp
<body class="{{ $usesModernSidebarShell ? 'owner-layout' : '' }} {{ $usesOperatorShell ? 'operator-layout' : '' }} {{ $usesPaymentShell ? 'payment-layout' : '' }} {{ $usesWorkflowShell ? 'workflow-layout' : '' }}">
  @php
    // Pre-calculate shouldShowSecondarySidebar for header
    // Check if user is owner
    $userRoleLower = $layoutUserRoleLower;
    $isOwner = $usesOwnerShell;
    $isOperatorShell = $usesOperatorShell;
    $isPaymentShell = $usesPaymentShell;
    $isWorkflowShell = $usesWorkflowShell;
    $isModernSidebarShell = $usesModernSidebarShell;

    $hasSubmenu = isset($menuDokumen) && !empty($menuDokumen);
    $isSubmenuPageForHeader = false;

    // Owner pages use the dedicated owner sidebar only. The legacy secondary
    // sidebar is reserved for non-owner workflow modules.
    if (!$isModernSidebarShell && isset($module)) {
      if ($module === 'akutansi') {
        $isSubmenuPageForHeader = request()->routeIs('dokumensAkutansi.*') ||
          request()->routeIs('akutansi.*') ||
          request()->is('*dokumensAkutansi*') ||
          request()->is('*rekapan-akutansi*');
      } elseif ($module === 'perpajakan') {
        $isSubmenuPageForHeader = request()->routeIs('dokumensPerpajakan.*') ||
          request()->routeIs('perpajakan.*') ||
          request()->is('*dokumensPerpajakan*') ||
          request()->is('*rekapan-perpajakan*');
      } elseif ($module === 'team_verifikasi') {
        $isSubmenuPageForHeader = request()->routeIs('documents.verifikasi.*') ||
          request()->routeIs('reports.verifikasi.*') ||
          request()->routeIs('returns.verifikasi.*') ||
          request()->is('*documents/verifikasi*');
      } else {
        $isSubmenuPageForHeader = request()->is('*dokumens*') ||
          request()->is('*rekapan*') ||
          request()->is('*pengembalian*');
      }
    }
    $shouldShowSecondarySidebarForHeader = !$isModernSidebarShell && ($hasSubmenu || $isSubmenuPageForHeader);

    // Define $isBagianUser early so it's available everywhere (not just inside sidebar block)
    $isBagianUser = false;
    if (auth()->check()) {
      $userRoleLower = strtolower(auth()->user()->role ?? '');
      $isBagianUser = str_starts_with($userRoleLower, 'bagian_');
    }

    // Spreadsheet mode dinonaktifkan — operator kembali ke tampilan tabel normal dengan sidebar
    $isOperatorSpreadsheet = false;
  @endphp

  {{-- ═══════════════════════════════════════════════════════════════════
       OPERATOR SPREADSHEET MODE — Full-width topbar, no sidebar
       ═══════════════════════════════════════════════════════════════════ --}}
  @if($isOperatorSpreadsheet)
    <style>
      /* Operator Spreadsheet Mode — override layout */
      .op-ss-topbar {
        background: linear-gradient(135deg, #0d6b5e 0%, #083e40 100%);
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 20px;
        height: 52px;
        box-shadow: 0 2px 8px rgba(0,0,0,.25);
        position: sticky; top: 0; z-index: 1050;
      }
      .op-ss-topbar .op-logo {
        display: flex; align-items: center; gap: 8px;
        font-size: 15px; font-weight: 600; letter-spacing: .3px;
        white-space: nowrap;
      }
      .op-ss-topbar .op-logo img {
        height: 28px; width: auto; filter: brightness(10);
      }
      .op-ss-topbar .op-nav {
        display: flex; align-items: center; gap: 4px;
        margin-left: 24px;
      }
      .op-ss-topbar .op-nav a,
      .op-ss-topbar .op-nav button {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 6px; border: none;
        font: 500 13px 'Inter', 'DM Sans', sans-serif;
        cursor: pointer; transition: .15s;
        text-decoration: none;
        background: rgba(255,255,255,.12); color: #fff;
      }
      .op-ss-topbar .op-nav a:hover,
      .op-ss-topbar .op-nav button:hover {
        background: rgba(255,255,255,.22);
      }
      .op-ss-topbar .op-nav a.active {
        background: rgba(255,255,255,.25);
        font-weight: 600;
      }
      .op-ss-topbar .op-nav .badge-count {
        background: #f5a623; color: #fff;
        font-size: 10px; font-weight: 700;
        padding: 1px 6px; border-radius: 10px;
        min-width: 18px; text-align: center;
      }
      .op-ss-topbar .op-right {
        margin-left: auto;
        display: flex; align-items: center; gap: 10px;
      }
      .op-ss-topbar .op-right .theme-toggle-btn {
        color: #ffffffcc !important;
      }
      .op-ss-topbar .op-right .profile-icon {
        color: #ffffffcc !important;
        font-size: 18px; cursor: pointer;
      }
      /* Hide sidebar & secondary sidebar for operator */
      body.op-spreadsheet-mode .sidebar,
      body.op-spreadsheet-mode .secondary-sidebar {
        display: none !important;
      }
      /* Full-width content for operator */
      body.op-spreadsheet-mode .content {
        margin-left: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
      }
      body.op-spreadsheet-mode footer {
        display: none !important;
      }
    </style>
    <script>document.body.classList.add('op-spreadsheet-mode');</script>

    <div class="op-ss-topbar">
      <div class="op-logo">
        <img src="{{ asset('images/logo_ptpn.png') }}" alt="Logo PTPN">
        <span>Agenda Online PTPN</span>
      </div>

      <div class="op-nav">
        {{-- Daftar Dokumen (always active on /documents) --}}
        <a href="{{ url('/documents') }}" class="{{ request()->is('documents*') && !request()->is('documents/import*') ? 'active' : '' }}">
          <i class="fa-solid fa-table-cells"></i> Spreadsheet
        </a>

        {{-- Inbox --}}
        @php
          try {
            $opInboxCount = \App\Models\Dokumen::where('inbox_approval_for', 'operator')
              ->where('inbox_approval_status', 'pending')
              ->count();
          } catch (\Exception $e) { $opInboxCount = 0; }
        @endphp
        <a href="{{ url('/inbox') }}" class="{{ request()->is('inbox*') ? 'active' : '' }}">
          <i class="fa-solid fa-inbox"></i> Inbox
          @if($opInboxCount > 0)
            <span class="badge-count">{{ $opInboxCount }}</span>
          @endif
        </a>

        {{-- Import CSV --}}
        <a href="{{ url('/documents/import') }}" class="{{ request()->is('documents/import*') ? 'active' : '' }}">
          <i class="fa-solid fa-file-import"></i> Import CSV
        </a>
      </div>

      <div class="op-right">
        <!-- Dark Mode Toggle -->
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode" style="background:none;border:none;">
          <i class="fas fa-moon theme-toggle-icon moon"></i>
          <i class="fas fa-sun theme-toggle-icon sun"></i>
        </button>

        <!-- Profile Dropdown -->
        <div class="profile-dropdown-container" style="position: relative;">
          <i class="fa-solid fa-user profile-icon" id="profileDropdownToggle"
             style="position: relative;"></i>
          <div class="profile-dropdown-menu" id="profileDropdownMenu" style="display: none;">
            <a href="{{ route('profile.account') }}" class="profile-dropdown-item">
              <i class="fa-solid fa-user-circle me-2"></i> Akun
            </a>
            <a href="{{ route('2fa.setup') }}" class="profile-dropdown-item">
              <i class="fa-solid fa-shield-alt me-2"></i> Keamanan 2FA
            </a>
            <div class="profile-dropdown-divider"></div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
              @csrf
              <button type="submit" class="profile-dropdown-item"
                style="width: 100%; text-align: left; border: none; background: none; padding: 8px 16px; cursor: pointer;">
                <i class="fa-solid fa-sign-out-alt me-2"></i> Logout
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

  {{-- ═══════ OTHER ROLES — standard header with topbar ═══════ --}}
  @elseif(!$isModernSidebarShell)
    <header>
      <div class="topbar mb-0 mt-0 {{ $shouldShowSecondarySidebarForHeader ? 'with-secondary-sidebar' : '' }}">
        <h5 class="mb-0 welcome-message">{{ $welcomeMessage ?? 'Selamat datang di Agenda Online PTPN' }}</h5>
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
  @endif

  <!-- Sidebar (hidden for operator spreadsheet mode via CSS) -->
  @if(!($isOperatorSpreadsheet ?? false))
  <div class="{{ $isModernSidebarShell ? 'sidebar-owner' : 'sidebar' }}">
    @if(!$isModernSidebarShell)
    <h4 class="text-center mb-4"><img src="{{ asset('images/logo_ptpn.png') }}" alt="Logo PTPN" class="sidebar-logo-img"> Agenda Online</h4>
    <hr>
    @endif

    @php
      // Normalize module to lowercase untuk konsistensi
      // Note: $isOwner is already defined at the top of the body section
      $module = strtolower($module ?? 'operator');

      // URL dashboard per role — menggunakan route yang benar sesuai web.php
      $dashboardUrl = match ($module) {
        'operator'        => '/dashboard',
        'team_verifikasi' => '/documents/verifikasi',
        'pembayaran'      => '/dashboard/pembayaran',
        'akutansi'        => '/dashboard/akutansi',
        'perpajakan'      => '/dashboard/perpajakan',
        default           => '/dashboard'
      };
      // URL halaman daftar dokumen per role — route baru (professional URLs)
      $dokumenUrl = match ($module) {
        'operator'        => '/documents',
        'team_verifikasi' => '/documents/verifikasi',
        'pembayaran'      => '/dashboard/pembayaran',
        'akutansi'        => '/documents/akutansi',
        'perpajakan'      => '/documents/perpajakan',
        default           => '/documents'
      };
      // URL halaman pengembalian/rekapan per role
      $pengembalianUrl = match ($module) {
        'team_verifikasi' => '/returns/verifikasi/bagian',
        'pembayaran'      => '/returns/pembayaran',
        'akutansi'        => '/returns/akutansi',
        'perpajakan'      => '/returns/perpajakan',
        default           => '/pengembalian-dokumens'
      };
      $tambahDokumenUrl = match ($module) {
        'operator' => '/documents/create',
        default    => null
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

    <script>window._userModule = @json($module);</script>

    @if($isModernSidebarShell)
      {{-- Modern role sidebar (Owner/Admin/Pembayaran) --}}

      {{-- Logo / Brand --}}
      <div class="owner-sidebar-logo">
        <div class="owner-logo-icon">
          <img src="{{ asset('images/logoPTPNNew.png') }}" alt="Logo PTPN">
        </div>
        <div>
          <div class="owner-logo-text">Agenda Online</div>
          <div class="owner-logo-sub">PTPN IV Regional V</div>
        </div>
        <button type="button" class="owner-sidebar-toggle" data-sidebar-toggle aria-label="Kecilkan sidebar" title="Kecilkan sidebar">
          <i class="fa-solid fa-angles-left owner-toggle-collapse" aria-hidden="true"></i>
          <i class="fa-solid fa-angles-right owner-toggle-expand" aria-hidden="true"></i>
        </button>
      </div>

      {{-- MENU Section --}}
      <div class="owner-sidebar-section" style="flex:0 0 auto;">
        <div class="owner-sidebar-label">Menu</div>
        @if($isOperatorShell)
        @php
          $isOperatorDocumentsActive = request()->routeIs('documents.index') ||
            request()->is('documents');
          $isOperatorCreateActive = request()->routeIs('documents.create') || request()->is('documents/create');
          $isOperatorImportActive = request()->routeIs('documents.import.*') || request()->is('documents/import*');
          $isOperatorReportActive = request()->routeIs('reports.analytics') || request()->is('reports/analytics');
        @endphp
        {{-- Inbox operator DIHAPUS 2026-07-05 (PL-2): bagian tak lagi kirim dokumen → operator tak butuh inbox --}}
        <a href="{{ route('documents.index') }}" class="owner-nav-item {{ $isOperatorDocumentsActive ? 'active' : '' }}" title="Daftar Dokumen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
          </svg>
          Daftar Dokumen
        </a>
        <a href="{{ route('documents.create') }}" class="owner-nav-item {{ $isOperatorCreateActive ? 'active' : '' }}" title="Tambah Dokumen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M12 5v14M5 12h14"/>
          </svg>
          Tambah Dokumen
        </a>
        <a href="{{ route('documents.import.index') }}" class="owner-nav-item {{ $isOperatorImportActive ? 'active' : '' }}" title="Import CSV">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M12 3v12"/>
            <path d="M8 11l4 4 4-4"/>
            <path d="M4 21h16"/>
          </svg>
          Import CSV
        </a>
        <a href="{{ route('reports.analytics') }}" class="owner-nav-item {{ $isOperatorReportActive ? 'active' : '' }}" title="Rekapan Dokumen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M3 3v18h18"/>
            <path d="M7 15l4-4 3 3 5-7"/>
          </svg>
          Rekapan Dokumen
        </a>
        @elseif($isPaymentShell)
        @php
          $isPaymentAssistantActive = request()->routeIs('pembayaran.asisten-virtual*');
          $isPaymentDashboardActive = !$isPaymentAssistantActive && (request()->routeIs('dashboard.pembayaran') || request()->routeIs('dashboard.pembayaran.data') || request()->is('*dashboard/pembayaran*'));
          $isPaymentDelayActive = request()->routeIs('reports.pembayaran.delays') || request()->is('*rekapan-keterlambatan*');
          $isPaymentReportActive = request()->routeIs('reports.pembayaran.*') || request()->is('*reports/pembayaran*');
        @endphp
        <a href="{{ route('dashboard.pembayaran') }}" class="owner-nav-item {{ $isPaymentDashboardActive ? 'active' : '' }}" title="Dashboard Pembayaran">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
          </svg>
          Dashboard
        </a>
        <a href="{{ route('documents.pembayaran.index') }}" class="owner-nav-item {{ request()->routeIs('documents.pembayaran.*') ? 'active' : '' }}" title="Daftar Pembayaran">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
          </svg>
          Daftar Pembayaran
        </a>
        <a href="{{ route('pembayaran.asisten-virtual') }}" class="owner-nav-item {{ $isPaymentAssistantActive ? 'active' : '' }}" title="Asisten Virtual">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <rect x="7" y="8" width="10" height="8" rx="3"/>
            <path d="M12 4v4"/>
            <path d="M8.5 12h.01M15.5 12h.01"/>
            <path d="M9 16v2h6v-2"/>
          </svg>
          Asisten Virtual
        </a>
        <a href="{{ route('reports.pembayaran.delays') }}" class="owner-nav-item {{ $isPaymentDelayActive ? 'active' : '' }}" title="Rekap Keterlambatan">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v6l4 2"/>
          </svg>
          Rekap Keterlambatan
        </a>
        <a href="{{ route('reports.analytics') }}" class="owner-nav-item {{ request()->routeIs('reports.analytics') ? 'active' : '' }}" title="Rekapan Dokumen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M3 3v18h18"/>
            <path d="M7 15l4-4 3 3 5-7"/>
          </svg>
          Rekapan Dokumen
        </a>
        @elseif($isWorkflowShell)
        @php
          $workflowRoleCode = match ($module ?? null) {
            'team_verifikasi', 'perpajakan', 'akutansi' => $module,
            default => match ($layoutUserRoleLower) {
              'verifikasi', 'team_verifikasi' => 'team_verifikasi',
              'perpajakan' => 'perpajakan',
              'akuntansi', 'akutansi' => 'akutansi',
              default => 'team_verifikasi',
            },
          };

          $workflowMenu = [
            'team_verifikasi' => [
              'dashboard_route' => route('dashboard.verifikasi'),
              'dashboard_active' => request()->routeIs('dashboard.verifikasi'),
              'document_label' => 'Daftar Dokumen',
              'document_route' => route('documents.verifikasi.index'),
              'document_active' => request()->routeIs('documents.verifikasi.*') || request()->is('*documents/verifikasi*'),
              'return_label' => 'Pengembalian Ke Bagian',
              'return_route' => route('returns.verifikasi.bagian'),
              'return_active' => request()->routeIs('returns.verifikasi.bagian'),
              'delay_route' => route('rekapan-keterlambatan.role', 'team_verifikasi'),
              'delay_active' => request()->is('*rekapan-keterlambatan/team_verifikasi*'),
              'badge_id' => 'notification-badge',
            ],
            'perpajakan' => [
              'dashboard_route' => route('dashboard.perpajakan'),
              'dashboard_active' => request()->routeIs('dashboard.perpajakan'),
              'document_label' => 'Daftar Perpajakan',
              'document_route' => route('documents.perpajakan.index'),
              'document_active' => request()->routeIs('documents.perpajakan.*') || request()->is('*documents/perpajakan*'),
              'return_label' => null,
              'return_route' => null,
              'return_active' => false,
              'delay_route' => route('rekapan-keterlambatan.role', 'perpajakan'),
              'delay_active' => request()->is('*rekapan-keterlambatan/perpajakan*'),
              'badge_id' => 'perpajakan-notification-badge',
            ],
            'akutansi' => [
              'dashboard_route' => route('dashboard.akutansi'),
              'dashboard_active' => request()->routeIs('dashboard.akutansi'),
              'document_label' => 'Daftar Akutansi',
              'document_route' => route('documents.akutansi.index'),
              'document_active' => request()->routeIs('documents.akutansi.*') || request()->is('*documents/akutansi*'),
              'return_label' => null,
              'return_route' => null,
              'return_active' => false,
              'delay_route' => route('rekapan-keterlambatan.role', 'akutansi'),
              'delay_active' => request()->is('*rekapan-keterlambatan/akutansi*'),
              'badge_id' => 'akutansi-notification-badge',
            ],
          ];

          $menu = $workflowMenu[$workflowRoleCode] ?? $workflowMenu['team_verifikasi'];
          $isWorkflowInboxActive = request()->is('inbox') || request()->routeIs('inbox.*');
        @endphp
        @if(!empty($menu['dashboard_route']))
        <a href="{{ $menu['dashboard_route'] }}" class="owner-nav-item {{ ($menu['dashboard_active'] ?? false) ? 'active' : '' }}" title="Dashboard">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
          </svg>
          Dashboard
        </a>
        @endif
        <a href="{{ url('/inbox') }}" class="owner-nav-item {{ $isWorkflowInboxActive ? 'active' : '' }}" title="Inbox">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
            <path d="M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z"/>
          </svg>
          Inbox
        </a>
        <a href="{{ $menu['document_route'] }}" class="owner-nav-item {{ $menu['document_active'] ? 'active' : '' }}" title="{{ $menu['document_label'] }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
          </svg>
          {{ $menu['document_label'] }}
          <span class="menu-notification-badge" id="{{ $menu['badge_id'] }}" style="display:none;margin-left:auto;">0</span>
        </a>
        @if($menu['return_route'])
        <a href="{{ $menu['return_route'] }}" class="owner-nav-item {{ $menu['return_active'] ? 'active' : '' }}" title="{{ $menu['return_label'] }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M9 14l-4-4 4-4"/>
            <path d="M5 10h11a4 4 0 010 8h-1"/>
          </svg>
          {{ $menu['return_label'] }}
          <span class="menu-notification-badge" id="pengembalian-ke-bidang-badge" style="display:none;margin-left:auto;">0</span>
        </a>
        @endif
        <a href="{{ $menu['delay_route'] }}" class="owner-nav-item {{ $menu['delay_active'] ? 'active' : '' }}" title="Rekap Keterlambatan">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v6l4 2"/>
          </svg>
          Rekap Keterlambatan
        </a>
        <a href="{{ route('reports.analytics') }}" class="owner-nav-item {{ request()->routeIs('reports.analytics') ? 'active' : '' }}" title="Rekapan Dokumen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M3 3v18h18"/>
            <path d="M7 15l4-4 3 3 5-7"/>
          </svg>
          Rekapan Dokumen
        </a>
        @else
        {{-- 1. Dashboard --}}
        <a href="{{ url('/owner/home') }}" class="owner-nav-item {{ $menuHome ?? '' }}" title="Dashboard">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
          </svg>
          Dashboard
        </a>
        {{-- 2. Daftar Dokumen --}}
        <a href="{{ url('/owner/dokumen') }}" class="owner-nav-item {{ $menuDokumen ?? '' }}" title="Daftar Dokumen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
          </svg>
          Daftar Dokumen
        </a>
        {{-- 3. Rekapan Dokumen --}}
        <a href="{{ route('reports.analytics') }}" class="owner-nav-item {{ request()->routeIs('reports.analytics') ? 'active' : '' }}" title="Rekapan Dokumen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M3 3v18h18"/>
            <path d="M7 15l4-4 3 3 5-7"/>
          </svg>
          Rekapan Dokumen
        </a>
        {{-- 4. Rekapan & Analisis Kerja --}}
        @php
          $isRekapanKeterlambatanActive = request()->is('*rekapan-keterlambatan*') ||
            request()->routeIs('rekapan-keterlambatan*') ||
            request()->routeIs('rekapan-keterlambatan.*') ||
            request()->is('*owner/analytics*') ||
            request()->routeIs('analytics.index');
        @endphp
        <a href="{{ route('rekapan-keterlambatan.index') }}"
          class="owner-nav-item {{ $menuRekapanKeterlambatan ?? '' }} {{ $isRekapanKeterlambatanActive ? 'active' : '' }}" title="Rekapan & Analisis Kerja">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
          Rekapan &amp; Analisis Kerja
        </a>
        {{-- 5. Laporan Cash Bank --}}
        @php
          $isCashBankActive = request()->is('*owner/cashbank*') || request()->routeIs('owner.cashbank.*');
        @endphp
        <a href="{{ route('owner.cashbank.index') }}"
           class="owner-nav-item {{ $isCashBankActive ? 'active' : '' }}" title="Laporan Cash Bank">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <path d="M8 21h8M12 17v4"/>
          </svg>
          Laporan Cash Bank
        </a>
        {{-- 6. Asisten Virtual --}}
        @php
          $isAsistenVirtualActive = request()->routeIs('owner.asisten-virtual');
        @endphp
        <a href="{{ route('owner.asisten-virtual') }}" class="owner-nav-item {{ ($menuAsistenVirtual ?? '') ?: ($isAsistenVirtualActive ? 'active' : '') }}" title="Asisten Virtual">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M12 8V4H8"/>
            <rect x="4" y="8" width="16" height="12" rx="4"/>
            <path d="M2 14h2M20 14h2"/>
            <circle cx="9" cy="14" r="1"/>
            <circle cx="15" cy="14" r="1"/>
            <path d="M10 18h4"/>
          </svg>
          Asisten Virtual
        </a>
        {{-- 7. Audit Trail --}}
        @php
          $isAuditTrailActive = request()->is('*owner/programmer-logs*') ||
            request()->routeIs('owner.programmer-logs') ||
            ($menuAuditTrail ?? '') === 'active';
        @endphp
        <a href="{{ url('/owner/programmer-logs') }}" class="owner-nav-item {{ $isAuditTrailActive ? 'active' : '' }}" title="Audit Trail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Audit Trail
        </a>
        @endif
      </div>


      {{-- User Card Bottom --}}
      <div class="owner-sidebar-bottom">
        @php
          $authUser = auth()->user();
          $authUserPhotoUrl = $authUser?->profile_photo_url;
          $initials = $authUser ? strtoupper(substr($authUser->name ?? 'U', 0, 1) . (strpos($authUser->name ?? '', ' ') !== false ? substr($authUser->name, strpos($authUser->name, ' ') + 1, 1) : '')) : 'U';
        @endphp
        <div class="owner-user-actions">
          <a class="owner-user-card" href="{{ route('profile.account') }}" title="Buka pengaturan profil">
            <div class="owner-avatar">
              @if($authUserPhotoUrl)
                <img src="{{ $authUserPhotoUrl }}" alt="Foto profil {{ $authUser->name ?? 'Pengguna' }}">
              @else
                {{ $initials }}
              @endif
            </div>
            <div class="owner-user-info">
              <div class="owner-user-name">{{ $authUser->name ?? 'Pengguna' }}</div>
              <div class="owner-user-role-text">{{ ucfirst(str_replace('_', ' ', $authUser->role ?? 'Owner')) }}</div>
            </div>
            <svg class="owner-profile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21a8 8 0 10-16 0"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </a>
          <form id="logout-form-owner" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
          <button type="button" class="owner-logout-btn" title="Keluar" aria-label="Keluar"
            onclick="event.preventDefault(); document.getElementById('logout-form-owner').submit();">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
          </button>
        </div>
      </div>

    @else
    <!-- Regular Menu for other roles -->
    <div style="flex: 1; display: flex; flex-direction: column;">
      {{-- Bagian TIDAK punya menu Home lagi (landing = Daftar Dokumen /bagian/documents). --}}
      @if(!$isBagianUser && !in_array($module, ['operator', 'team_verifikasi', 'perpajakan', 'akutansi']))
        <a href="{{ url($dashboardUrl) }}" class="{{ $menuDashboard ?? '' }}"><i class="fa-solid fa-house"></i> Home</a>
      @endif

      <!-- Owner Dashboard - Only for Admin users -->
      @if(auth()->check() && (auth()->user()->role === 'Admin' || auth()->user()->role === 'admin'))
        <a href="{{ url('/owner/dashboard') }}" class="nav-link">
          <i class="fa-solid fa-satellite-dish"></i> Owner Dashboard
        </a>
        
        <!-- Audit Trail Menu - Only for Admin users -->
        <a href="{{ url('/owner/programmer-logs') }}" class="nav-link">
          <i class="fa-solid fa-shield-halved"></i> Audit Trail
        </a>
      @endif

      <!-- Inbox Menu - Untuk Team Verifikasi, Perpajakan, Akutansi -->
      @php
        $currentUserRole = 'operator'; // Default
        if (auth()->check()) {
          $user = auth()->user();
          // Prioritize role field first (most accurate)
          if (isset($user->role) && !empty($user->role)) {
            $currentUserRole = $user->role;
          } elseif (isset($user->name)) {
            // Fallback to name mapping if role is not set
            $nameToRole = [
              'Operator' => 'operator',
              'operator' => 'operator',
              'Operator' => 'operator',
              'team_verifikasi' => 'team_verifikasi',
              'Ibu B' => 'team_verifikasi',
              'Ibu Yuni' => 'team_verifikasi',
              'Team Verifikasi' => 'team_verifikasi',
              'Perpajakan' => 'Perpajakan',
              'Team Perpajakan' => 'Perpajakan',
              'Akutansi' => 'Akutansi',
              'Team Akutansi' => 'Akutansi',
              'Pembayaran' => 'Pembayaran',
              'Team Pembayaran' => 'Pembayaran'
            ];
            $currentUserRole = $nameToRole[$user->name] ?? 'operator';
          }
        }

        // Normalize role to check (case-insensitive comparison)
        $currentUserRoleLower = strtolower($currentUserRole);
        // Include all possible variations of role names after lowercase
        $OperatorRoles = ['operator', 'Operator', 'Operator', 'operator'];
        $inboxRoles = array_merge($OperatorRoles, ['team_verifikasi', 'ibu b', 'verifikasi', 'team verifikasi', 'perpajakan', 'team perpajakan', 'akutansi', 'team akutansi', 'pembayaran', 'team pembayaran']);
        $showInbox = in_array($currentUserRoleLower, $inboxRoles);

        // Map role to inbox query format
        $inboxRoleForQuery = 'team_verifikasi';
        if (in_array($currentUserRoleLower, $OperatorRoles)) {
          $inboxRoleForQuery = 'operator';
        } elseif (in_array($currentUserRoleLower, ['perpajakan', 'team perpajakan'])) {
          $inboxRoleForQuery = 'Perpajakan';
        } elseif (in_array($currentUserRoleLower, ['akutansi', 'team akutansi'])) {
          $inboxRoleForQuery = 'Akutansi';
        } elseif (in_array($currentUserRoleLower, ['pembayaran', 'team pembayaran'])) {
          $inboxRoleForQuery = 'Pembayaran';
        } elseif (in_array($currentUserRoleLower, ['verifikasi', 'team verifikasi', 'team_verifikasi', 'ibu b'])) {
          $inboxRoleForQuery = 'team_verifikasi'; // Verifikasi uses Team Verifikasi inbox
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
            $isBagianTambahActive = request()->routeIs('bagian.documents.create') || request()->is('*bagian/documents/create*');
            $isBagianTreeOpen = $isBagianDocumentsActive || $isBagianTambahActive;
          @endphp
          <div class="agenda-tree {{ $isBagianTreeOpen ? 'menu-open menu-is-opening' : '' }}">
            <a href="#"
              class="agenda-tree-toggle {{ $isBagianTreeOpen ? 'active' : '' }}"
              aria-expanded="{{ $isBagianTreeOpen ? 'true' : 'false' }}">
              <i class="fa-solid fa-file-lines"></i>
              Dokumen
              <i class="right fa-solid fa-angle-left"></i>
            </a>
            <div class="nav-treeview">
              <a href="{{ route('bagian.documents.index') }}" class="{{ $isBagianDocumentsActive ? 'active' : '' }}">
                <i class="fa-solid fa-list"></i> Daftar Dokumen
              </a>
            </div>
          </div>
        @else
          {{-- Regular Dokumen menu for other roles --}}
          @php
            // Determine route based on module — gunakan route() helper sesuai web.php
            $menuRoute = match ($module) {
              'akutansi'        => route('documents.akutansi.index'),
              'perpajakan'      => route('documents.perpajakan.index'),
              'team_verifikasi' => route('documents.verifikasi.index'),
              default           => url($dokumenUrl)
            };

            // Check if current route is within this module
            $isModuleActive = match ($module) {
              'akutansi' => request()->routeIs('documents.akutansi.*') ||
              request()->routeIs('reports.akutansi.*') ||
              request()->routeIs('returns.akutansi.*') ||
              request()->is('*documents/akutansi*'),
              'perpajakan' => request()->routeIs('documents.perpajakan.*') ||
              request()->routeIs('reports.perpajakan.*') ||
              request()->routeIs('returns.perpajakan.*') ||
              request()->is('*documents/perpajakan*'),
              'team_verifikasi' => request()->routeIs('documents.verifikasi.*') ||
              request()->routeIs('reports.verifikasi.*') ||
              request()->routeIs('returns.verifikasi.*') ||
              request()->is('*documents/verifikasi*'),
              default => request()->routeIs('documents.*') ||
                request()->routeIs('reports.*') ||
                request()->is('documents*') ||
                request()->is('reports*')
            };

            $isDaftarDokumenActive = match ($module) {
              'akutansi' => request()->routeIs('documents.akutansi.*') || request()->is('*documents/akutansi*'),
              'perpajakan' => request()->routeIs('documents.perpajakan.*') || request()->is('*documents/perpajakan*'),
              'team_verifikasi' => request()->routeIs('documents.verifikasi.*') || request()->is('*documents/verifikasi*'),
              default => request()->routeIs('documents.index') || request()->routeIs('documents.create') || request()->is('documents') || request()->is('documents/create')
            };
            $isImportActive = request()->routeIs('documents.import.*') || request()->is('*documents/import*');
            $isReportsActive = match ($module) {
              'akutansi' => request()->routeIs('reports.akutansi.*') || request()->is('*rekapan-keterlambatan/akutansi*'),
              'perpajakan' => request()->routeIs('reports.perpajakan.*') || request()->is('*rekapan-keterlambatan/perpajakan*'),
              'team_verifikasi' => request()->routeIs('reports.verifikasi.*') || request()->is('*rekapan-keterlambatan*'),
              default => request()->routeIs('reports.*') || request()->is('*reports*')
            };
            $isReturnsActive = match ($module) {
              'akutansi' => request()->routeIs('returns.akutansi.*') || request()->is('*returns/akutansi*'),
              'perpajakan' => request()->routeIs('returns.perpajakan.*') || request()->is('*returns/perpajakan*'),
              'team_verifikasi' => request()->routeIs('returns.verifikasi.*') || request()->is('*returns/verifikasi*'),
              default => request()->is('*pengembalian*')
            };
          @endphp
          <div class="agenda-tree {{ $isModuleActive ? 'menu-open menu-is-opening' : '' }}">
            <a href="#"
              class="{{ ($menuDokumen ?? '') . ($isModuleActive ? ' active' : '') }} agenda-tree-toggle sidebar-menu-trigger"
              data-submenu="dokumen" id="btn-pembayaran" aria-expanded="{{ $isModuleActive ? 'true' : 'false' }}">
              <i class="fa-solid fa-file-lines"></i>
              @if($module === 'akutansi')
                Akutansi
              @elseif($module === 'perpajakan')
                Perpajakan
              @elseif($module === 'team_verifikasi')
                Dokumen
              @else
                Dokumen
              @endif
              <i class="right fa-solid fa-angle-left"></i>
            </a>
            <div class="nav-treeview">
              @if($module === 'akutansi')
                <a href="{{ route('documents.akutansi.index') }}" class="{{ $isDaftarDokumenActive ? 'active' : '' }}">
                  <i class="fa-solid fa-list"></i> Daftar Akutansi
                </a>
                <a href="{{ route('rekapan-keterlambatan.role', 'akutansi') }}"
                  class="{{ request()->is('*rekapan-keterlambatan/akutansi*') ? 'active' : '' }}">
                  <i class="fa-solid fa-clock-rotate-left"></i> Rekap Keterlambatan
                </a>
              @elseif($module === 'perpajakan')
                <a href="{{ route('documents.perpajakan.index') }}" class="{{ $isDaftarDokumenActive ? 'active' : '' }}">
                  <i class="fa-solid fa-list"></i> Daftar Perpajakan
                </a>
                <a href="{{ route('rekapan-keterlambatan.role', 'perpajakan') }}"
                  class="{{ request()->is('*rekapan-keterlambatan/perpajakan*') ? 'active' : '' }}">
                  <i class="fa-solid fa-clock-rotate-left"></i> Rekap Keterlambatan
                </a>
              @elseif($module === 'team_verifikasi')
                <a href="{{ route('documents.verifikasi.index') }}" class="{{ $isDaftarDokumenActive ? 'active' : '' }}">
                  <i class="fa-solid fa-list"></i> Daftar Dokumen
                </a>
                <a href="{{ route('returns.verifikasi.bagian') }}"
                  class="{{ request()->routeIs('returns.verifikasi.bagian') ? 'active' : '' }}">
                  <i class="fa-solid fa-arrow-left"></i> Pengembalian Ke Bagian
                </a>
                <a href="{{ route('rekapan-keterlambatan.role', 'team_verifikasi') }}"
                  class="{{ request()->is('*rekapan-keterlambatan/team_verifikasi*') ? 'active' : '' }}">
                  <i class="fa-solid fa-clock-rotate-left"></i> Rekap Keterlambatan
                </a>
              @else
                <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.index') ? 'active' : '' }}">
                  <i class="fa-solid fa-list"></i> Daftar Dokumen
                </a>
                @if($tambahDokumenUrl)
                  <a href="{{ route('documents.create') }}" class="{{ request()->routeIs('documents.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-plus"></i> Tambah Dokumen
                  </a>
                @endif
                <a href="{{ route('documents.import.index') }}" class="{{ $isImportActive ? 'active' : '' }}">
                  <i class="fa-solid fa-file-import"></i> Import CSV
                </a>
                <a href="{{ route('reports.analytics') }}" class="{{ $isReportsActive ? 'active' : '' }}">
                  <i class="fa-solid fa-chart-pie"></i> Rekapan Dokumen
                </a>
              @endif
            </div>
          </div>
        @endif

      @endunless

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
  @endif {{-- end !isOperatorSpreadsheet --}}

  <!-- Secondary Sidebar (Submenu Panel) - Mekari Style -->
  @if($isModernSidebarShell)
    {{-- Modern sidebar layouts intentionally use only the primary sidebar. --}}
  @else
  @php
    // Check if user is on a submenu page or menu dokumen is active
    $hasSubmenu = isset($menuDokumen) && !empty($menuDokumen);

    $isSubmenuPage = false;
    if ($module === 'akutansi') {
      $isSubmenuPage = request()->routeIs('documents.akutansi.*') ||
        request()->routeIs('reports.akutansi.*') ||
        request()->routeIs('returns.akutansi.*') ||
        request()->is('*documents/akutansi*');
    } elseif ($module === 'perpajakan') {
      $isSubmenuPage = request()->routeIs('documents.perpajakan.*') ||
        request()->routeIs('reports.perpajakan.*') ||
        request()->routeIs('returns.perpajakan.*') ||
        request()->is('*documents/perpajakan*');
    } elseif ($module === 'team_verifikasi') {
      $isSubmenuPage = request()->routeIs('documents.verifikasi.*') ||
        request()->routeIs('reports.verifikasi.*') ||
        request()->routeIs('returns.verifikasi.*') ||
        request()->is('*documents/verifikasi*');
    } elseif ($isBagianUser) {
      $isSubmenuPage = request()->is('*bagian/documents*');
    } else {
      $isSubmenuPage = request()->is('*dokumens*') ||
        request()->is('*rekapan*') ||
        request()->is('*reports*') ||
        request()->is('*pengembalian*');
    }

    $shouldShowSecondarySidebar = $hasSubmenu || $isSubmenuPage;

    $submenuTitle = '';
    if ($module === 'akutansi') {
      $submenuTitle = 'MENU AKUTANSI';
    } elseif ($module === 'perpajakan') {
      $submenuTitle = 'MENU PERPAJAKAN';
    } elseif ($module === 'team_verifikasi') {
      $submenuTitle = 'MENU DOKUMEN';
    } elseif ($isBagianUser) {
      $submenuTitle = 'MENU DOKUMEN';
    } else {
      $submenuTitle = 'MENU DOKUMEN';
    }
  @endphp
  @if(!($isOperatorSpreadsheet ?? false))
  <div class="secondary-sidebar {{ $shouldShowSecondarySidebar ? 'active' : '' }}" id="sidebar-pembayaran"
    role="complementary" aria-label="Submenu Panel">
    <div class="secondary-sidebar-header">
      {{ $submenuTitle }}
    </div>
    <div class="secondary-sidebar-content">
      @if($module === 'akutansi')
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
          <i class="fa-solid fa-list me-2"></i> Daftar Akutansi
          <span class="menu-notification-badge" id="akutansi-notification-badge"
            style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ route('rekapan-keterlambatan.role', 'akutansi') }}"
          class="{{ request()->is('*rekapan-keterlambatan/akutansi*') ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @elseif($module === 'perpajakan')
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
          <i class="fa-solid fa-list me-2"></i> Daftar Perpajakan
          <span class="menu-notification-badge" id="perpajakan-notification-badge"
            style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ route('rekapan-keterlambatan.role', 'perpajakan') }}"
          class="{{ request()->is('*rekapan-keterlambatan/perpajakan*') ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @elseif($module === 'team_verifikasi')
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}" id="menu-daftar-dokumen">
          <i class="fa-solid fa-list me-2"></i> Daftar Dokumen
          <span class="menu-notification-badge" id="notification-badge" style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ route('returns.verifikasi.bagian') }}" class="{{ $menuPengembalianKeBidang ?? '' }}">
          <i class="fa-solid fa-arrow-left me-2"></i> Pengembalian Ke Bagian
          <span class="menu-notification-badge" id="pengembalian-ke-bidang-badge"
            style="display: none; margin-left: auto;">0</span>
        </a>
        <a href="{{ route('rekapan-keterlambatan.role', 'team_verifikasi') }}"
          class="{{ request()->is('*rekapan-keterlambatan/team_verifikasi*') ? 'active' : '' }}">
          <i class="fa-solid fa-clock-rotate-left me-2"></i> Rekap Keterlambatan
        </a>
      @elseif($isBagianUser)
        {{-- Bagian submenu (same pattern as Operator) --}}
        @php
          $isDaftarActive = request()->routeIs('bagian.documents.index') || request()->is('*bagian/documents');
        @endphp
        <a href="{{ route('bagian.documents.index') }}" class="{{ $isDaftarActive ? 'active' : '' }}">
          <i class="fa-solid fa-list me-2"></i> Daftar Dokumen
        </a>
      @else
        <!-- Operator -->
        <a href="{{ url($dokumenUrl) }}" class="{{ $menuDaftarDokumen ?? '' }}">
          <i class="fa-solid fa-list me-2"></i> Daftar Dokumen
        </a>
        @if($tambahDokumenUrl)
          <a href="{{ url($tambahDokumenUrl) }}" class="{{ $menuTambahDokumen ?? '' }}">
            <i class="fa-solid fa-plus me-2"></i> Tambah Dokumen
          </a>
        @endif
        <a href="{{ url('/documents/import') }}" class="{{ request()->is('*documents/import*') ? 'active' : '' }}">
          <i class="fa-solid fa-file-import me-2"></i> Import CSV
        </a>
        <a href="{{ url('/reports/analytics') }}" class="{{ $menuRekapan ?? '' }}">
          <i class="fa-solid fa-chart-pie me-2"></i> Rekapan Dokumen
        </a>
      @endif
    </div>
  </div>
  @endif {{-- end !isOperatorSpreadsheet for secondary sidebar --}}
  @endunless

  <!-- Content -->
  <div class="content {{ ($isOperatorSpreadsheet ?? false) ? '' : (($shouldShowSecondarySidebar ?? false) ? 'with-secondary-sidebar' : '') }}">
    <!-- Notifikasi Success/Error -->
    @php
      $showGlobalFlash = !request()->routeIs('profile.account');
    @endphp

    @if($showGlobalFlash && session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert"
        style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);">
        <i class="fa-solid fa-circle-check me-2"></i>
        <strong>Berhasil!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($showGlobalFlash && session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert"
        style="margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if($showGlobalFlash && $errors->any())
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
    @include('partials.compact-document-ui')
    @include('partials.document-workbench-ui')
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
  /**
   * Global Handler untuk mencegah navigasi saat user sedang menyeleksi teks
   * Digunakan pada Card dan Table Row yang bisa diklik
   *
   * @param {Event} event - Click event
   * @param {string} url - URL tujuan navigasi
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
      document.querySelectorAll('.agenda-tree-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(event) {
          event.preventDefault();

          const tree = toggle.closest('.agenda-tree');
          if (!tree) return;

          const isOpen = tree.classList.contains('menu-open');
          tree.classList.toggle('menu-open', !isOpen);
          tree.classList.toggle('menu-is-opening', !isOpen);
          toggle.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
        });
      });

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

  <!-- Auto-Refresh System for Team Verifikasi -->
  <script>
    (function() {
      'use strict';

      // Get user role from authenticated user
      let currentUserRole = 'operator'; // Default
      @php
        $tempUserRole = 'operator';
        if (auth()->check()) {
          $user = auth()->user();
          if (isset($user->name)) {
            $nameToRole = [
              'Operator' => 'operator',
              'operator' => 'operator',
              'team_verifikasi' => 'team_verifikasi',
              'Ibu B' => 'team_verifikasi',
              'Perpajakan' => 'perpajakan',
              'Akutansi' => 'akutansi',
              'Pembayaran' => 'pembayaran'
            ];
            $tempUserRole = $nameToRole[$user->name] ?? 'operator';
          } elseif (isset($user->role)) {
            $tempUserRole = $user->role;
          }
        }
      @endphp
      currentUserRole = '{{ $tempUserRole }}';

      const isTeam Verifikasi = currentUserRole.toLowerCase() === 'team_verifikasi';
      const isOperator = currentUserRole.toLowerCase() === 'operator';
      const isPerpajakan = currentUserRole.toLowerCase() === 'perpajakan';
      const isAkutansi = currentUserRole.toLowerCase() === 'akutansi';
      const isPembayaran = currentUserRole.toLowerCase() === 'pembayaran';

      console.log('Auto-refresh system setup:', {
        userRole: currentUserRole,
        isTeam Verifikasi: isTeam Verifikasi,
        isOperator: isoperator,
        isPerpajakan: isPerpajakan,
        isAkutansi: isAkutansi,
        isPembayaran: isPembayaran,
        path: window.location.pathname
      });

      // Additional debugging for akutansi
      if (isAkutansi) {
        console.log('🟢 AKUTANSI MODULE DETECTED - Notifications should work');
      }

      // Enable for Team Verifikasi, Perpajakan, Akutansi, Pembayaran (any page) - Excluding Operator only
      const shouldEnableAutoRefresh = isTeam Verifikasi || isPerpajakan || isAkutansi || isPembayaran;

      console.log('Should enable auto-refresh:', shouldEnableAutoRefresh);

      if (!shouldEnableAutoRefresh) {
        console.log('Auto-refresh disabled: User is Operator or role not recognized');
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

        // For operator, we want to be less restrictive to show important notifications
        const isOperator = currentUserRole.toLowerCase() === 'operator';
        if (isOperator) {
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
        const isOperator = currentUserRole.toLowerCase() === 'operator';
        const isPerpajakan = currentUserRole.toLowerCase() === 'perpajakan';
        const isAkutansi = currentUserRole.toLowerCase() === 'akutansi';
        
        if (isOperator || isPerpajakan || isAkutansi) {
          // Don't pre-populate known document IDs for operator, Perpajakan, and Akutansi 
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
                  <strong>Alasan:</strong> <span class="alasan-text">${doc.return_reason || 'Tidak ada alasan'}</span><br>
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
                  <small style="opacity: 0.8;">Dokumen baru dari Operator - ${doc.sent_at}</small>
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

      // View document — arahkan ke daftar dokumen role (halaman edit sudah dihapus,
      // pengeditan via inline edit di daftar).
      window.viewDocument = function(docId) {
        if (isAkutansi) {
          window.location.href = `/documents/akutansi#doc-${docId}`;
        } else if (isPerpajakan) {
          window.location.href = `/documents/perpajakan#doc-${docId}`;
        } else {
          window.location.href = `/documents#doc-${docId}`;
        }
      };

      // View returned document for Operator
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
          if (isTeam Verifikasi) {
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
          console.log('Current module check:', { isTeam Verifikasi, isoperator, isPerpajakan, isAkutansi, isPembayaran });

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
          if (isTeam Verifikasi) {
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

              // Separate new documents from approved documents for Team Verifikasi
              let documentsToNotify = newDocuments;
              let approvedDocuments = [];
              let newDocumentsOnly = [];
              
              if (isTeam Verifikasi) {
                newDocumentsOnly = newDocuments.filter(doc => doc.is_new_from_Operator === true);
                approvedDocuments = newDocuments.filter(doc => doc.approved_by);
                
                // Only show notification for approved documents (not as "new document")
                if (approvedDocuments.length > 0) {
                  showNotification(approvedDocuments, 'approved');
                }
                
                // Show notification for new documents from Operator
                if (newDocumentsOnly.length > 0) {
                  showNotification(newDocumentsOnly, 'new');
                }
                
                documentsToNotify = []; // Don't show default notification
              }

              // Add to known documents
              newDocuments.forEach(doc => knownDocumentIds.add(doc.id));

              // Show notifications for other roles
              if (!isTeam Verifikasi && documentsToNotify.length > 0) {
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
              if (isTeam Verifikasi) {
                // Only count new documents from operator, not approved documents
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
        // Only check for non-Operator users
        if (currentUserRole.toLowerCase() === 'operator') {
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
          const isOperator = currentUserRole.toLowerCase() === 'operator';

          // Check universal notifications for all non-Operator users
          checkUniversalNotifications();

          // For Operator and Perpajakan, be less aggressive about skipping - only skip if actively typing
          if ((isOperator || isPerpajakan || isAkutansi) && shouldSkip) {
            const activeElement = document.activeElement;
            const isActuallyTyping = activeElement && (
              activeElement.tagName === 'INPUT' ||
              activeElement.tagName === 'TEXTAREA' ||
              activeElement.tagName === 'SELECT'
            );

            const moduleName = isPerpajakan ? 'Perpajakan' : (isAkutansi ? 'Akutansi' : 'operator');
            if (isActuallyTyping) {
              console.log(`${moduleName}: Skipping update check - user is typing`);
              return;
            }
          }

          if (shouldSkip && !isOperator && !isPerpajakan && !isAkutansi) {
            console.log('Skipping update check - user is active');
          } else {
            checkForUpdates();
          }
        }, POLLING_INTERVAL);
      }

      // Start the system
      startPolling();

      const moduleNames = [];
      if (isTeam Verifikasi) moduleNames.push('team_verifikasi');
      if (isPerpajakan) moduleNames.push('Perpajakan');
      if (isAkutansi) moduleNames.push('Akutansi');
      if (isOperator) moduleNames.push('operator');
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

    // Check if user has inbox access (Team Verifikasi, Perpajakan, Akutansi) or is Operator
    const userRole = '{{ auth()->user()->role ?? "" }}';
    const userRoleLower = userRole.toLowerCase();
    
    // Case-insensitive check for inbox roles
    const inboxRoles = ['team_verifikasi', 'verifikasi', 'perpajakan', 'akutansi'];
    const isOperator = ['operator', 'Operator', 'Operator'].includes(userRoleLower);
    const hasInboxAccess = inboxRoles.includes(userRoleLower);

    // Debug logging
    console.log('Notification System Init:', {
        userRole: userRole,
        userRoleLower: userRoleLower,
        hasInboxAccess: hasInboxAccess,
        isOperator: isOperator
    });

    // Check if user is Team Verifikasi
    const isTeamVerifikasi = ['team_verifikasi', 'ibu b', 'ibu yuni', 'team verifikasi'].includes(userRoleLower);

    // Debug logging
    console.log('Notification System Init:', {
        userRole: userRole,
        userRoleLower: userRoleLower,
        hasInboxAccess: hasInboxAccess,
        isOperator: isOperator,
        isTeamVerifikasi: isTeamVerifikasi
    });

    // Initialize Operator rejected documents notification if applicable
    if (isOperator) {
        console.log('Initializing Operator rejected notifications');
        initOperatorRejectedNotifications();
        // Operator does not have inbox access, so exit here
        return;
    }

    // Initialize Team Verifikasi rejected documents notification if applicable
    if (isTeamVerifikasi) {
        console.log('Initializing Team Verifikasi rejected notifications');
        initTeamVerifikasiRejectedNotifications();
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
        'team_verifikasi': 'team_verifikasi',
        'ibu b': 'team_verifikasi',
        'ibu yuni': 'team_verifikasi',
        'team verifikasi': 'team_verifikasi',
        'perpajakan': 'Perpajakan',
        'akutansi': 'Akutansi'
    };
    const inboxRole = roleMap[userRoleLower] || (userRoleLower === 'perpajakan' ? 'Perpajakan' : 
                                                    userRoleLower === 'akutansi' ? 'Akutansi' : 
                                                    userRoleLower === 'team_verifikasi' ? 'team_verifikasi' : userRole);
    
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
                        (e.recipientRole.toLowerCase() === 'team_verifikasi' && inboxRole.toLowerCase() === 'team_verifikasi'))) {

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
            // Operator does not have inbox access, so skip the check
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

    // Operator Rejected Documents Notification System
    function initOperatorRejectedNotifications() {
        console.log('initOperatorRejectedNotifications function called');
        // Rejected documents notification polling
        // Reset last check time jika lebih dari 24 jam yang lalu untuk memastikan semua dokumen terdeteksi
        let rejectedLastCheckTime = localStorage.getItem('Operator_rejected_last_check_time');
        if (!rejectedLastCheckTime) {
            rejectedLastCheckTime = new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(); // 24 jam yang lalu
            localStorage.setItem('Operator_rejected_last_check_time', rejectedLastCheckTime);
        } else {
            // Jika last check time lebih dari 24 jam yang lalu, reset ke 24 jam yang lalu
            const lastCheck = new Date(rejectedLastCheckTime);
            const twentyFourHoursAgo = new Date(Date.now() - 24 * 60 * 60 * 1000);
            if (lastCheck < twentyFourHoursAgo) {
                rejectedLastCheckTime = twentyFourHoursAgo.toISOString();
                localStorage.setItem('Operator_rejected_last_check_time', rejectedLastCheckTime);
                console.log('🔄 Reset rejected documents last check time to 24 hours ago');
            }
        }

        // Track shown rejected notifications to prevent duplicates
        const shownRejectedIds = new Set(JSON.parse(localStorage.getItem('Operator_shown_rejected_notifications') || '[]'));

        // Function to check for rejected documents
        async function checkRejectedDocuments() {
            try {
                console.log('🔍 Checking for rejected documents...', { lastCheckTime: rejectedLastCheckTime });
                
                const response = await fetch(`/api/documents/rejected/check?last_check_time=${encodeURIComponent(rejectedLastCheckTime)}`, {
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
                        localStorage.setItem('Operator_rejected_last_check_time', rejectedLastCheckTime);
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
                            return createdBy === 'operator' || createdBy === 'Operator' || createdBy === 'operator' || createdBy === 'Operator';
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
                        localStorage.setItem('Operator_shown_rejected_notifications', JSON.stringify(Array.from(shownRejectedIds)));

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
        window.OperatorRejectedDocumentsInterval = rejectedDocumentsInterval;

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

    // Team Verifikasi Rejected Documents Notification System
    function initTeamVerifikasiRejectedNotifications() {
        console.log('initTeamVerifikasiRejectedNotifications function called - Initializing Team Verifikasi rejected documents notification system');
        
        // Rejected documents notification polling
        let rejectedLastCheckTime = localStorage.getItem('team_verifikasi_rejected_last_check_time');
        if (!rejectedLastCheckTime) {
            rejectedLastCheckTime = new Date().toISOString();
            localStorage.setItem('team_verifikasi_rejected_last_check_time', rejectedLastCheckTime);
        }

        // Track shown rejected notifications to prevent duplicates
        const shownRejectedIds = new Set(JSON.parse(localStorage.getItem('team_verifikasi_shown_rejected_notifications') || '[]'));

        // Function to check for rejected documents
        async function checkRejectedDocuments() {
            try {
                console.log('Checking rejected documents for Team Verifikasi, last check:', rejectedLastCheckTime);
                
                const response = await fetch(`/api/documents/verifikasi/rejected/check?last_check_time=${encodeURIComponent(rejectedLastCheckTime)}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    console.warn('Team Verifikasi rejected documents check failed:', response.status, response.statusText);
                    return;
                }

                const data = await response.json();

                console.log('Team Verifikasi rejected documents data:', data);

                if (data.success) {
                    // Update last check time
                    if (data.current_time) {
                        rejectedLastCheckTime = data.current_time;
                        localStorage.setItem('team_verifikasi_rejected_last_check_time', rejectedLastCheckTime);
                    }

                    // If there are rejected documents
                    if (data.rejected_documents_count > 0 && data.rejected_documents.length > 0) {
                        // Filter out already shown notifications
                        const newRejectedToShow = data.rejected_documents.filter(doc => {
                            const docKey = `team_verifikasi_rejected_doc_${doc.id}_shown`;
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
                        localStorage.setItem('team_verifikasi_shown_rejected_notifications', JSON.stringify(Array.from(shownRejectedIds)));

                        // Only show notification if we have new rejected documents
                        if (newRejectedToShow.length > 0) {
                            console.log('Showing rejected document notifications for Team Verifikasi:', newRejectedToShow.length);
                            
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
                        console.log('No new rejected documents for Team Verifikasi');
                    }
                } else {
                    console.warn('Team Verifikasi rejected documents check returned unsuccessful:', data.message);
                }
            } catch (error) {
                console.error('Error checking rejected documents for Team Verifikasi:', error);
            }
        }

        // Check immediately on page load (with delay to ensure DOM is ready)
        setTimeout(() => {
            console.log('Team Verifikasi: Running initial rejected documents check');
            checkRejectedDocuments();
        }, 1500);

        // Poll every 30 seconds
        const pollInterval = setInterval(checkRejectedDocuments, 30000);
        console.log('Team Verifikasi: Rejected documents polling started, interval:', pollInterval);

        // Also check when page becomes visible (user switches back to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('Team Verifikasi: Page visible, checking rejected documents');
                setTimeout(checkRejectedDocuments, 500);
            }
        });

        // Check when window gains focus
        window.addEventListener('focus', function() {
            console.log('Team Verifikasi: Window focused, checking rejected documents');
            setTimeout(checkRejectedDocuments, 500);
        });

        // Also check when page is fully loaded
        if (document.readyState === 'complete') {
            setTimeout(() => {
                console.log('Team Verifikasi: Page complete, checking rejected documents');
                checkRejectedDocuments();
            }, 2000);
        } else {
            window.addEventListener('load', function() {
                setTimeout(() => {
                    console.log('Team Verifikasi: Page loaded, checking rejected documents');
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
                      currentPathCheck.includes('/rekapan-Team Verifikasi') ||
                      currentPathCheck.includes('/dashboard/pembayaran') ||
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
      // (bisa dikustom per-input lewat atribut data-placeholder)
      input.type = 'text';
      input.placeholder = input.dataset.placeholder || 'Pilih tanggal (dd/mm/yyyy)';
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

{{-- ===================================================
     URGENCY NOTIFICATION WIDGET (Global – all roles)
     Polls /api/documents/urgency/active every 60s.
     Only shown to non-admin/non-owner roles who have
     urgency-active documents assigned to them.
     =================================================== --}}
@php
    $currentUserRole = strtolower(auth()->user()->role ?? '');
    $isRecipientRole = in_array($currentUserRole, ['operator', 'team_verifikasi', 'verifikasi', 'perpajakan', 'akutansi', 'pembayaran']);
@endphp

@if($isRecipientRole)
<style>
  /* Urgency Banner (recipient role) */
  #urgencyGlobalBanner {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 99998;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    padding: 0;
    box-shadow: none;
    transform: translateY(-100%);
    visibility: hidden;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), visibility 0s 0.4s;
  }
  #urgencyGlobalBanner.visible {
    transform: translateY(0);
    visibility: visible;
    box-shadow: 0 4px 16px rgba(239,68,68,0.35);
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), visibility 0s 0s;
  }
  #urgencyGlobalBanner .urgency-banner-inner {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 20px; gap: 12px;
  }
  #urgencyGlobalBanner .urgency-banner-left {
    display: flex; align-items: center; gap: 10px; flex: 1;
  }
  #urgencyGlobalBanner .urgency-banner-icon {
    font-size: 20px; animation: urgencyPulse 1.5s ease-in-out infinite;
  }
  @keyframes urgencyPulse {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.2); }
  }
  #urgencyGlobalBanner .urgency-banner-text {
    font-size: 0.88rem; font-weight: 600; line-height: 1.4;
  }
  #urgencyGlobalBanner .urgency-banner-text strong { font-size: 1rem; }
  #urgencyGlobalBanner .urgency-banner-links {
    display: flex; gap: 8px; flex-shrink: 0;
  }
  #urgencyGlobalBanner .urgency-banner-link {
    background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4);
    color: #fff; border-radius: 6px; padding: 5px 12px;
    font-size: 0.8rem; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: all 0.2s;
  }
  #urgencyGlobalBanner .urgency-banner-link:hover { background: rgba(255,255,255,0.35); color: #fff; }
  #urgencyGlobalBanner .urgency-banner-dismiss {
    background: none; border: none; color: rgba(255,255,255,0.8);
    font-size: 18px; cursor: pointer; line-height: 1; padding: 0 4px;
    transition: color 0.2s;
  }
  #urgencyGlobalBanner .urgency-banner-dismiss:hover { color: #fff; }
  /* Push main content down when banner is visible */
  body.urgency-banner-visible { padding-top: 52px !important; }
  /* Urgency badge in DataTable rows */
  .urgency-row-badge {
    display: inline-block;
    background: #ef4444; color: #fff;
    border-radius: 10px; padding: 2px 8px;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.5px; margin-left: 4px;
    vertical-align: middle; animation: urgencyPulse 2s infinite;
  }
</style>

<div id="urgencyGlobalBanner">
  <div class="urgency-banner-inner">
    <div class="urgency-banner-left">
      <span class="urgency-banner-icon">⚡</span>
      <div class="urgency-banner-text">
        <span id="urgencyBannerMsg">Anda memiliki <strong id="urgencyBannerCount">...</strong> dokumen yang memerlukan penyelesaian segera!</span><br>
        <small id="urgencyBannerList" style="opacity:0.85;font-weight:400;"></small>
      </div>
    </div>
    <div class="urgency-banner-links">
      <a href="{{ url('/inbox') }}" class="urgency-banner-link">
        <i class="fas fa-inbox"></i> Buka Inbox
      </a>
    </div>
    <button class="urgency-banner-dismiss" onclick="dismissUrgencyBanner()" title="Tutup (muncul kembali jika ada urgency baru)">×</button>
  </div>
</div>

<script>
(function() {
  const POLL_INTERVAL_MS = 60000;   // 60 seconds
  const LS_KEY_DISMISSED_COUNT = 'urgency_dismissed_count';
  const LS_KEY_DISMISSED_IDS   = 'urgency_dismissed_ids';
  let urgencyPollTimer = null;

  function getDismissedIds() {
    try {
      const raw = localStorage.getItem(LS_KEY_DISMISSED_IDS);
      return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
  }

  function dismissUrgencyBanner() {
    // Store current urgency IDs so banner stays hidden until new ones arrive
    const countEl = document.getElementById('urgencyBannerCount');
    const currentCount = countEl ? countEl.textContent : '0';
    localStorage.setItem(LS_KEY_DISMISSED_COUNT, currentCount);
    // Also store the IDs of dismissed urgencies (set by last poll)
    if (window._lastUrgencyIds) {
      localStorage.setItem(LS_KEY_DISMISSED_IDS, JSON.stringify(window._lastUrgencyIds));
    }
    hideBanner();
  }
  window.dismissUrgencyBanner = dismissUrgencyBanner;

  function shouldShowBanner(currentIds) {
    const dismissedIds = getDismissedIds();
    if (dismissedIds.length === 0) return true;
    // Show banner if there are any NEW urgency IDs not in the dismissed set
    const dismissedSet = new Set(dismissedIds);
    for (let i = 0; i < currentIds.length; i++) {
      if (!dismissedSet.has(currentIds[i])) return true;
    }
    return false;
  }

  function showBanner(count, list) {
    const currentIds = list ? list.map(function(u) { return u.id; }) : [];
    window._lastUrgencyIds = currentIds;

    // Don't show if user dismissed these same urgencies
    if (!shouldShowBanner(currentIds)) return;

    const banner = document.getElementById('urgencyGlobalBanner');
    const countEl = document.getElementById('urgencyBannerCount');
    const listEl  = document.getElementById('urgencyBannerList');
    if (!banner) return;
    countEl.textContent = count;
    if (list && list.length > 0) {
      const preview = list.slice(0, 3).map(function(u) { return u.nomor_agenda; }).join(', ');
      listEl.textContent = 'Dokumen: ' + preview + (list.length > 3 ? ' +' + (list.length - 3) + ' lainnya' : '');
    } else {
      listEl.textContent = '';
    }
    banner.classList.add('visible');
    document.body.classList.add('urgency-banner-visible');
  }

  function hideBanner() {
    const banner = document.getElementById('urgencyGlobalBanner');
    if (banner) banner.classList.remove('visible');
    document.body.classList.remove('urgency-banner-visible');
  }

  function pollUrgencies() {
    fetch('/api/documents/urgency/active', {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.ok ? r.json() : null; })
    .then(function(data) {
      if (data && data.success && data.count > 0) {
        showBanner(data.count, data.urgencies);
      } else {
        // No urgencies at all — hide banner and clear dismissed state
        hideBanner();
        localStorage.removeItem(LS_KEY_DISMISSED_COUNT);
        localStorage.removeItem(LS_KEY_DISMISSED_IDS);
      }
    })
    .catch(function() {}); // fail silently
  }

  // Start polling after page is ready
  document.addEventListener('DOMContentLoaded', function() {
    // Initial poll after 2 seconds so page can finish loading first
    setTimeout(pollUrgencies, 2000);
    // Then poll every 60 seconds
    urgencyPollTimer = setInterval(pollUrgencies, POLL_INTERVAL_MS);
  });
})();
</script>
@endif

{{-- ===================================================
     DRAG-TO-SCROLL: Global horizontal + vertical drag scroll
     Works on DataTable scroll bodies + .table-responsive
     =================================================== --}}
<style>
  /* Grab cursor on all scroll containers */
  .dataTables_scrollBody,
  .table-responsive,
  .table-wrapper {
    cursor: grab;
    scroll-behavior: auto !important;
    -webkit-overflow-scrolling: touch;
    transform: translateZ(0);
    backface-visibility: hidden;
  }
  .dataTables_scrollBody.is-dragging,
  .table-responsive.is-dragging,
  .table-wrapper.is-dragging {
    cursor: grabbing !important;
    user-select: none !important;
    will-change: scroll-position;
  }
  /* Kill ALL transitions inside table during drag — prevents hover lag */
  .is-dragging * {
    transition: none !important;
    pointer-events: none !important;
  }
</style>

<script>
(function () {
  const DRAG_THRESHOLD = 5;   // px – below this = click, above = drag
  const SPEED_FACTOR   = 1.4; // scroll multiplier for responsiveness

  const SCROLL_SELECTORS = [
    '.dataTables_scrollBody',
    '.table-responsive',
    '.table-wrapper',
  ];

  function initDragScroll(el) {
    if (el._dragScrollInited) return;
    el._dragScrollInited = true;

    let isDragging = false;
    let didDrag    = false;
    let startX     = 0;
    let startScrollLeft = 0;

    // ── Mouse events (rAF-optimised) ───────────────────────────
    var rafId = 0;
    var lastMoveX = 0;

    el.addEventListener('mousedown', function (e) {
      if (e.button !== 0) return;
      if (e.target.closest('a, button, input, select, textarea, label')) return;
      isDragging = true;
      didDrag    = false;
      startX     = e.clientX;
      startScrollLeft = el.scrollLeft;
      el.style.willChange = 'scroll-position';
      e.stopPropagation();
    }, { passive: true });

    el.addEventListener('mousemove', function (e) {
      if (!isDragging) return;
      // Hold-and-drag KHUSUS scroll horizontal — gerakan vertikal diabaikan total.
      var deltaX = e.clientX - startX;
      if (!didDrag && Math.abs(deltaX) < DRAG_THRESHOLD) return;
      if (!didDrag) {
        didDrag = true;
        el.classList.add('is-dragging');
      }
      lastMoveX = deltaX;
      if (!rafId) {
        rafId = requestAnimationFrame(function () {
          el.scrollLeft = startScrollLeft - lastMoveX * SPEED_FACTOR;
          rafId = 0;
        });
      }
      e.preventDefault();
    });

    el.addEventListener('mouseup', function (e) {
      if (!isDragging) return;
      isDragging = false;
      el.classList.remove('is-dragging');
      el.style.willChange = 'auto';
      if (rafId) { cancelAnimationFrame(rafId); rafId = 0; }
      if (didDrag) {
        e.stopPropagation();
        var blocker = function (ev) {
          ev.stopPropagation();
          ev.preventDefault();
          el.removeEventListener('click', blocker, true);
        };
        el.addEventListener('click', blocker, true);
      }
      didDrag = false;
    });

    el.addEventListener('mouseleave', function () {
      if (isDragging) {
        isDragging = false;
        didDrag    = false;
        el.classList.remove('is-dragging');
        el.style.willChange = 'auto';
        if (rafId) { cancelAnimationFrame(rafId); rafId = 0; }
      }
    });

    // ── Touch events ──────────────────────────────────────────
    let touchStartX = 0;
    let touchScrollLeft = 0;

    el.addEventListener('touchstart', function (e) {
      touchStartX     = e.touches[0].clientX;
      touchScrollLeft = el.scrollLeft;
    }, { passive: true });

    el.addEventListener('touchmove', function (e) {
      // Hanya scroll horizontal; gerakan vertikal dibiarkan untuk scroll halaman native.
      const deltaX = e.touches[0].clientX - touchStartX;
      if (Math.abs(deltaX) < DRAG_THRESHOLD) return;
      el.scrollLeft = touchScrollLeft - deltaX * SPEED_FACTOR;
    }, { passive: true });
  }

  function activateOnAllContainers() {
    SCROLL_SELECTORS.forEach(function (selector) {
      document.querySelectorAll(selector).forEach(function (el) {
        // Activate if element has scrollable content in any direction
        const hasHScroll = el.scrollWidth  > el.clientWidth  + 5;
        const hasVScroll = el.scrollHeight > el.clientHeight + 5;
        if (hasHScroll || hasVScroll) {
          initDragScroll(el);
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    activateOnAllContainers();

    const mo = new MutationObserver(function () {
      activateOnAllContainers();
    });
    mo.observe(document.body, { childList: true, subtree: true });

    if (window.jQuery) {
      jQuery(document).on('draw.dt', function () {
        activateOnAllContainers();
      });
    }
  });
})();
</script>

{{-- ===================================================
     FULLSCREEN MODE: global fullscreen toggle
     Auto-injects button next to .btn-customize-columns-inline
     =================================================== --}}
<style>
  /* ── Fullscreen button styles ── */
  .btn-fullscreen-toggle {
    padding: 10px 20px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 44px;
    white-space: nowrap;
    text-decoration: none;
  }
  .btn-fullscreen-toggle:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    color: white;
  }
  .btn-fullscreen-toggle.active {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    box-shadow: 0 2px 6px rgba(100, 116, 139, 0.3);
  }

  /* ── Tambah Dokumen button (fullscreen only) ── */
  .btn-tambah-dokumen-fs {
    padding: 10px 20px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 44px;
    white-space: nowrap;
    text-decoration: none;
    margin-left: 8px;
  }
  .btn-tambah-dokumen-fs:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    color: white;
  }

  /* ── Container when fullscreen ── */
  body.is-fullscreen .fs-content-area {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 9990 !important;
    overflow: auto !important;
    background: #F8FAFC !important;
    padding: 10px 14px !important;
    box-sizing: border-box !important;
  }
  html.dark body.is-fullscreen .fs-content-area {
    background: #0f172a !important;
  }
  /* ── Sidebar/secondary nav — smooth hide on fullscreen ── */
  .sidebar,
  .secondary-sidebar {
    transition: opacity 0.2s ease, visibility 0.2s ease;
  }
  body.is-fullscreen .sidebar,
  body.is-fullscreen .secondary-sidebar {
    opacity: 0 !important;
    pointer-events: none !important;
    visibility: hidden !important;
  }
  .btn-fullscreen-toggle:focus-visible {
    outline: 2px solid #f59e0b;
  }

  /* ── Fullscreen transition overlay (smooth fade) ── */
  body.fullscreen-transitioning .sidebar,
  body.fullscreen-transitioning .secondary-sidebar {
    display: block !important; /* keep in DOM for transition to work */
  }
</style>

<script>
(function () {
  var FS_KEY            = 'agenda_fs_active'; // sessionStorage key
  let isFullscreen      = false;
  let fsArea            = null;
  let hiddenElements    = [];   // [{el, prev}] – page sections above filter
  let hiddenSidebars    = [];   // [{el, prev}] – sidebars
  let savedAreaStyles   = {};   // saved inline styles of the content area

  // ── Find content wrapper (.content / main) ─────────────────────
  function findContentArea(btn) {
    let el = btn;
    for (let i = 0; i < 15; i++) {
      el = el.parentElement;
      if (!el || el === document.body) break;
      if (
        el.classList.contains('content') ||
        el.classList.contains('main-content') ||
        el.tagName === 'MAIN'
      ) return el;
    }
    // Fallback: ancestor teratas dari tombol yang merupakan anak langsung <body>.
    // Menjamin area yang dikembalikan MEMUAT tombol — cegah layar putih (jika area
    // salah/tak memuat tombol, hideAbove bisa menyembunyikan seluruh isi halaman).
    el = btn;
    while (el && el.parentElement && el.parentElement !== document.body) {
      el = el.parentElement;
    }
    return el || document.querySelector('.content') || null;
  }

  // ── Direct child of contentArea that wraps the filter row ─────
  function findFilterAnchor(fsBtn, contentArea) {
    let el = fsBtn;
    while (el && el.parentElement !== contentArea) {
      el = el.parentElement;
    }
    return el;
  }

  // ── Hide siblings above the filter-row anchor ─────────────────
  function hideAbove(anchor, contentArea) {
    hiddenElements = [];
    let child = contentArea.firstElementChild;
    while (child && child !== anchor) {
      hiddenElements.push({ el: child, prev: child.style.display });
      child.style.display = 'none';
      child = child.nextElementSibling;
    }
  }

  // ── Hide sidebars via JS (covers all class-name variations) ───
  function hideSidebars() {
    hiddenSidebars = [];
    const selectors = [
      '.sidebar',
      '.secondary-sidebar',
      '.side-nav',
      '[class*="sidebar"]',
      '[class*="side-bar"]',
    ];
    const seen = new WeakSet();
    selectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        if (seen.has(el)) return;
        seen.add(el);
        // Skip if it's inside the content area (don't hide content children)
        if (fsArea && fsArea.contains(el)) return;
        hiddenSidebars.push({ el: el, prev: el.style.display });
        el.style.display = 'none';
      });
    });
  }

  // ── Save & override content area positioning ──────────────────
  function lockContentArea(area) {
    savedAreaStyles = {
      position:   area.style.position,
      top:        area.style.top,
      left:       area.style.left,
      width:      area.style.width,
      height:     area.style.height,
      zIndex:     area.style.zIndex,
      overflow:   area.style.overflow,
      background: area.style.background,
      padding:    area.style.padding,
      margin:     area.style.margin,
      marginLeft: area.style.marginLeft,
      boxSizing:  area.style.boxSizing,
    };
    const bg = document.documentElement.classList.contains('dark')
      ? '#0f172a' : '#F8FAFC';
    Object.assign(area.style, {
      position:   'fixed',
      top:        '0',
      left:       '0',
      width:      '100vw',
      height:     '100vh',
      zIndex:     '9990',
      overflow:   'auto',
      background: bg,
      padding:    '10px 14px',
      margin:     '0',
      marginLeft: '0',
      boxSizing:  'border-box',
    });
  }

  // ── Restore content area ──────────────────────────────────────
  function unlockContentArea(area) {
    Object.assign(area.style, savedAreaStyles);
    savedAreaStyles = {};
  }

  // ── Restore all hidden elements ───────────────────────────────
  function restoreAll() {
    hiddenElements.forEach(function (item) {
      item.el.style.display = item.prev || '';
    });
    hiddenSidebars.forEach(function (item) {
      item.el.style.display = item.prev || '';
    });
    hiddenElements = [];
    hiddenSidebars = [];
  }

  // ── Enter fullscreen ──────────────────────────────────────────
  function enterFullscreen(fsBtn) {
    isFullscreen = true;
    try { sessionStorage.setItem(FS_KEY, '1'); } catch(e) {}

    document.body.classList.add('fullscreen-transitioning');

    fsArea = findContentArea(fsBtn);
    if (fsArea) {
      const anchor = findFilterAnchor(fsBtn, fsArea);
      // Hanya sembunyikan elemen DI ATAS anchor bila anchor benar-benar anak
      // langsung dari fsArea — jika tidak, lewati agar isi tabel tak ikut hilang.
      if (anchor && anchor.parentElement === fsArea && anchor !== fsArea) hideAbove(anchor, fsArea);
      lockContentArea(fsArea);          // ← inline fixed positioning
    }

    hideSidebars();                     // ← hide all sidebars via JS

    document.body.classList.add('is-fullscreen');
    document.body.style.overflow = 'hidden';

    setTimeout(function () {
      document.body.classList.remove('fullscreen-transitioning');
    }, 250);

    fsBtn.classList.add('active');
    fsBtn.innerHTML = '<i class="fas fa-compress"></i> Keluar Fullscreen';
    fsBtn.title = 'Keluar dari mode fullscreen (Esc)';

    // ── Inject "Tambah Dokumen" button only for operator role ──
    var _mod = (window._userModule || '').toLowerCase();
    var _noTambah = ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
    if (_noTambah.indexOf(_mod) === -1 && !document.getElementById('btn-tambah-dokumen-fs')) {
      var tambahBtn = document.createElement('button');
      tambahBtn.type = 'button';
      tambahBtn.id = 'btn-tambah-dokumen-fs';
      tambahBtn.className = 'btn-tambah-dokumen-fs';
      tambahBtn.innerHTML = '✚ Tambah Dokumen';
      tambahBtn.title = 'Tambah dokumen baru';
      tambahBtn.addEventListener('click', function (e) {
        e.preventDefault();
        try {
          sessionStorage.setItem('return_to_fullscreen', '1');
          sessionStorage.setItem('return_url', window.location.pathname + window.location.search);
        } catch(err) {}
        window.location.href = '/documents/create';
      });
      fsBtn.parentNode.insertBefore(tambahBtn, fsBtn.nextSibling);
    }
  }

  // ── Exit fullscreen ───────────────────────────────────────────
  function exitFullscreen() {
    if (!isFullscreen) return;
    isFullscreen = false;
    try { sessionStorage.removeItem(FS_KEY); } catch(e) {}

    document.body.classList.add('fullscreen-transitioning');
    document.body.classList.remove('is-fullscreen');

    // Wait for CSS transition then clean up
    setTimeout(function () {
      document.body.classList.remove('fullscreen-transitioning');
      restoreAll();

      if (fsArea) {
        unlockContentArea(fsArea);
        fsArea = null;
      }

      document.body.style.overflow = '';
    }, 250);

    // ── Remove "Tambah Dokumen" button ──
    var tambahBtn = document.getElementById('btn-tambah-dokumen-fs');
    if (tambahBtn) tambahBtn.remove();

    document.querySelectorAll('.btn-fullscreen-toggle').forEach(function (b) {
      b.classList.remove('active');
      b.innerHTML = '<i class="fas fa-expand"></i> Fullscreen';
      b.title = 'Tampilan layar penuh (Ctrl+Shift+F)';
    });
  }

  function toggleFullscreen(btn) {
    isFullscreen ? exitFullscreen() : enterFullscreen(btn);
  }

  // ── Inject button ─────────────────────────────────────────────
  function injectFullscreenButton(customizeBtn) {
    if (customizeBtn._fullscreenInjected) return;
    customizeBtn._fullscreenInjected = true;

    const fsBtn = document.createElement('button');
    fsBtn.type      = 'button';
    fsBtn.className = 'btn-fullscreen-toggle';
    fsBtn.innerHTML = '<i class="fas fa-expand"></i> Fullscreen';
    fsBtn.title     = 'Tampilan layar penuh (Ctrl+Shift+F)';

    fsBtn.addEventListener('click', function (e) {
      e.preventDefault();
      toggleFullscreen(fsBtn);
    });

    customizeBtn.parentNode.insertBefore(fsBtn, customizeBtn.nextSibling);
  }

  function scanAndInject() {
    document.querySelectorAll('.btn-customize-columns-inline').forEach(injectFullscreenButton);
  }

  // ── Keyboard shortcuts ────────────────────────────────────────
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && isFullscreen) {
      exitFullscreen();
      return;
    }
    if (e.key === 'F' && e.ctrlKey && e.shiftKey) {
      e.preventDefault();
      const fsBtn = document.querySelector('.btn-fullscreen-toggle');
      if (fsBtn) toggleFullscreen(fsBtn);
    }

    // Ctrl+F → fokus ke kolom pencarian di semua role
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
      // Jangan override jika user sedang mengetik di input/textarea
      const tag = document.activeElement && document.activeElement.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

      // Cari modal terbuka — jika ada, biarkan browser/modal yg handle
      const anyModalOpen = document.querySelector('.modal.show');
      if (anyModalOpen) return;

      // Cari search input — urutan prioritas sesuai ID di berbagai role view
      const searchInput =
        document.getElementById('searchInput') ||
        document.getElementById('search') ||
        document.querySelector('input[name="search"]') ||
        document.querySelector('input[type="search"]') ||
        document.querySelector('.search-input[type="text"]');

      if (!searchInput) return; // tidak ada search input di halaman ini

      e.preventDefault(); // cegah browser search bawaan

      // Scroll smooth ke input
      searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });

      setTimeout(function () {
        searchInput.focus();
        searchInput.select();

        // Efek highlight singkat (warna utama tema)
        searchInput.style.transition = 'box-shadow 0.2s ease, border-color 0.2s ease';
        searchInput.style.boxShadow  = '0 0 0 3px rgba(8, 62, 64, 0.35)';
        searchInput.style.borderColor = '#083E40';
        setTimeout(function () {
          searchInput.style.boxShadow  = '';
          searchInput.style.borderColor = '';
        }, 1500);
      }, 80);
    }

    // Ctrl+Z → Back ke halaman sebelumnya (Simulasi tombol Back Browser) di semua role
    if ((e.ctrlKey || e.metaKey) && (e.key === 'z' || e.key === 'Z')) {
      // Periksa apakah user sedang mengetik sesuatu di input/textarea
      const tag = document.activeElement && document.activeElement.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA') {
        // Biarkan default browser behavior (Undo text)
        return;
      }
      e.preventDefault(); // Cegah fungsi undo bawaan browser di luar input
      window.history.back();
    }
  });


  // ── Restore fullscreen state after filter/reload ─────────────
  function restoreFullscreenIfNeeded() {
    // Check ?fullscreen=1 URL param (from redirect after document creation)
    try {
      var params = new URLSearchParams(window.location.search);
      if (params.get('fullscreen') === '1') {
        sessionStorage.setItem(FS_KEY, '1');
        params.delete('fullscreen');
        var newUrl = window.location.pathname +
                     (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);
      }
    } catch(e) {}

    let saved = false;
    try { saved = sessionStorage.getItem(FS_KEY) === '1'; } catch(e) {}
    if (!saved) return;

    // Wait for the fullscreen button to be injected before restoring
    const fsBtn = document.querySelector('.btn-fullscreen-toggle');
    if (fsBtn) {
      enterFullscreen(fsBtn);
    } else {
      // Button not yet injected — wait a tick and retry
      setTimeout(restoreFullscreenIfNeeded, 50);
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    scanAndInject();
    const mo = new MutationObserver(scanAndInject);
    mo.observe(document.body, { childList: true, subtree: true });

    // Restore fullscreen after page reload (filter/pagination/refresh)
    restoreFullscreenIfNeeded();
  });
})();
</script>

{{-- ===================================================
     GLOBAL PERFORMANCE CSS: micro-animations, smooth scroll,
     GPU hints, skeleton loading, button feedback
     =================================================== --}}
<style>
  /* ── Smooth page scroll (except table containers) ── */
  html { scroll-behavior: smooth; }

  /* ── Button micro-animations ── */
  button,
  .btn,
  [role="button"] {
    transition: transform 0.1s ease,
                opacity 0.1s ease,
                box-shadow 0.15s ease,
                background 0.2s ease,
                color 0.2s ease;
    -webkit-tap-highlight-color: transparent;
    position: relative;
    overflow: hidden;
  }

  button:active:not(:disabled),
  .btn:active:not(:disabled),
  [role="button"]:active:not(:disabled) {
    transform: scale(0.97);
    opacity: 0.9;
  }

  /* Subtle ripple flash on click */
  button::after,
  .btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.18);
    border-radius: inherit;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
  }

  button:active::after,
  .btn:active::after {
    opacity: 1;
    transition: none;
  }

  /* ── GPU hints for dynamic elements ── */
  .modal,
  .dropdown-menu,
  .tooltip,
  .popover,
  .notification-toast,
  .global-notification-toast {
    transform: translateZ(0);
    will-change: transform, opacity;
  }

  /* ── Smooth transitions for status/navigation ── */
  .badge,
  .status-indicator,
  .nav-item,
  .nav-link,
  .sidebar a,
  .sidebar .nav-link,
  .sidebar .menu-item {
    transition: background-color 0.15s ease,
                color 0.15s ease,
                opacity 0.15s ease;
  }

  /* ── DataTable row hover — GPU-friendly ── */
  .dataTables_wrapper tbody tr {
    transition: background-color 0.12s ease;
  }

  /* ── DataTable skeleton shimmer ── */
  @keyframes skeleton-shimmer {
    0%   { background-position: -600px 0; }
    100% { background-position: 600px 0; }
  }

  .dt-skeleton-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 5;
    background: rgba(255,255,255,0.85);
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 8px 12px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.15s ease;
  }
  .dt-skeleton-overlay.visible { opacity: 1; }

  html.dark .dt-skeleton-overlay {
    background: rgba(15,23,42,0.85);
  }

  .dt-skeleton-row {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 600px 100%;
    animation: skeleton-shimmer 1.5s infinite linear;
    border-radius: 6px;
    height: 38px;
    min-width: 100%;
  }
  html.dark .dt-skeleton-row {
    background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
    background-size: 600px 100%;
  }

  /* Fade-in after data loads */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .dt-data-loaded tbody tr {
    animation: fadeInUp 0.2s ease forwards;
  }
</style>

{{-- ===================================================
     DATATABLE SKELETON LOADING: show shimmer during AJAX
     =================================================== --}}
<script>
(function () {
  if (!window.jQuery) return;

  function createSkeleton(wrapper) {
    if (wrapper.querySelector('.dt-skeleton-overlay')) return;
    var body = wrapper.querySelector('.dataTables_scrollBody') ||
               wrapper.querySelector('tbody');
    if (!body) return;
    body.style.position = 'relative';

    var overlay = document.createElement('div');
    overlay.className = 'dt-skeleton-overlay';
    for (var i = 0; i < 6; i++) {
      var row = document.createElement('div');
      row.className = 'dt-skeleton-row';
      overlay.appendChild(row);
    }
    body.appendChild(overlay);
    // Trigger reflow then show
    overlay.offsetHeight;
    overlay.classList.add('visible');
  }

  function removeSkeleton(wrapper) {
    var overlay = wrapper.querySelector('.dt-skeleton-overlay');
    if (!overlay) return;
    overlay.classList.remove('visible');
    setTimeout(function () { overlay.remove(); }, 150);
    wrapper.classList.add('dt-data-loaded');
    setTimeout(function () { wrapper.classList.remove('dt-data-loaded'); }, 300);
  }

  jQuery(document).on('preXhr.dt', function (e, settings) {
    var wrapper = jQuery(settings.nTableWrapper)[0];
    if (wrapper) createSkeleton(wrapper);
  });

  jQuery(document).on('xhr.dt', function (e, settings) {
    var wrapper = jQuery(settings.nTableWrapper)[0];
    if (wrapper) {
      // Small delay so skeleton doesn't flash for very fast requests
      setTimeout(function () { removeSkeleton(wrapper); }, 100);
    }
  });
})();
</script>

<script>
(function () {
  function clearVirtualAssistantChatStorage() {
    try {
      Object.keys(localStorage)
        .filter(function (key) { return key.indexOf('virtual_assistant_chat_') === 0; })
        .forEach(function (key) { localStorage.removeItem(key); });
    } catch (error) {
      // Ignore storage failures; logout must never be blocked by UI cleanup.
    }
  }

  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || !form.action) return;
    if (String(form.action).indexOf('/logout') !== -1) {
      clearVirtualAssistantChatStorage();
    }
  }, true);

  document.addEventListener('click', function (event) {
    var link = event.target && event.target.closest ? event.target.closest('a[href]') : null;
    if (!link) return;
    if (String(link.href).indexOf('/logout') !== -1) {
      clearVirtualAssistantChatStorage();
    }
  }, true);
})();
</script>

@stack('scripts')

</body>
</html>

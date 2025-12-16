@extends('layouts/app')
@section('content')

<!-- Tailwind CSS CDN for responsive utilities -->
<script src="https://cdn.tailwindcss.com"></script>

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  /* Dashboard Scorecards - Modern Design */
  .scorecard {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 20px;
    padding: 20px 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
    border: none;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    min-height: auto;
    height: auto;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .scorecard::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    transition: all 0.4s ease;
  }

  .scorecard::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    transition: all 0.6s ease;
    opacity: 0;
  }

  .scorecard:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15), 0 4px 16px rgba(0, 0, 0, 0.08);
  }

  .scorecard:hover::after {
    opacity: 1;
    top: -60%;
    right: -60%;
  }

  .scorecard.merah::before {
    background: linear-gradient(180deg, #ff6b6b 0%, #ee5a6f 50%, #dc3545 100%);
  }

  .scorecard.merah:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(220, 53, 69, 0.5);
  }

  .scorecard.kuning::before {
    background: linear-gradient(180deg, #ffd93d 0%, #ffc107 50%, #f39c12 100%);
  }

  .scorecard.kuning:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(255, 193, 7, 0.5);
  }

  .scorecard.hijau::before {
    background: linear-gradient(180deg, #6bcf7f 0%, #28a745 50%, #1e7e34 100%);
  }

  .scorecard.hijau:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.5);
  }

  .scorecard.biru::before {
    background: linear-gradient(180deg, #4dabf7 0%, #007bff 50%, #0056b3 100%);
  }

  .scorecard.biru:hover::before {
    width: 8px;
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.5);
  }

  .scorecard-body {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .scorecard-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    min-width: 0;
  }

  .scorecard-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    flex-shrink: 0;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    min-width: 48px; /* Prevent icon from shrinking too much */
  }

  /* Responsive icon size */
  @media (max-width: 768px) {
    .scorecard-icon {
      width: 48px;
      height: 48px;
      font-size: 20px;
      min-width: 40px;
    }
  }

  .scorecard:hover .scorecard-icon {
    transform: scale(1.15) rotate(5deg);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  }

  .scorecard.merah .scorecard-icon {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 50%, #dc3545 100%);
  }

  .scorecard.kuning .scorecard-icon {
    background: linear-gradient(135deg, #ffd93d 0%, #ffc107 50%, #f39c12 100%);
  }

  .scorecard.hijau .scorecard-icon {
    background: linear-gradient(135deg, #6bcf7f 0%, #28a745 50%, #1e7e34 100%);
  }

  .scorecard.biru .scorecard-icon {
    background: linear-gradient(135deg, #4dabf7 0%, #007bff 50%, #0056b3 100%);
  }

  .scorecard-title {
    font-size: 13px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
  }

  .scorecard-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
    flex: 1;
    overflow: hidden;
  }

  .scorecard-value {
    font-size: 18px; /* Base: Mobile */
    font-weight: 700;
    color: #083E40;
    line-height: 1.2;
    margin: 0;
    letter-spacing: -0.5px;
  }

  /* Responsive font sizes */
  @media (min-width: 640px) {
    .scorecard-value {
      font-size: 20px; /* md: Tablet */
    }
  }

  @media (min-width: 1024px) {
    .scorecard-value {
      font-size: 22px; /* lg: Laptop */
    }
  }

  @media (min-width: 1536px) {
    .scorecard-value {
      font-size: 24px; /* 2xl: Large Monitor */
    }
  }

  /* Handle long numbers - Responsive */
  .scorecard-value.long-number {
    font-size: 16px; /* Base: Mobile */
  }

  @media (min-width: 640px) {
    .scorecard-value.long-number {
      font-size: 18px; /* md: Tablet */
    }
  }

  @media (min-width: 1024px) {
    .scorecard-value.long-number {
      font-size: 20px; /* lg: Laptop */
    }
  }

  @media (min-width: 1536px) {
    .scorecard-value.long-number {
      font-size: 22px; /* 2xl: Large Monitor */
    }
  }

  /* Prevent text wrapping for long numbers */
  .scorecard-value.whitespace-nowrap {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  @media (max-width: 768px) {
    .scorecard {
      padding: 16px 20px;
    }
    
    .scorecard-icon {
      width: 48px;
      height: 48px;
      font-size: 20px;
    }
  }

  .scorecard-label {
    font-size: 12px;
    color: #889717;
    font-weight: 500;
    margin: 0;
    word-wrap: break-word;
    overflow-wrap: break-word;
    line-height: 1.4;
  }

  /* Filter Section */
  .filter-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.08);
    position: relative;
    z-index: 1;
    overflow: visible;
  }

  .filter-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }

  .filter-group {
    flex: 1;
    min-width: 200px;
  }

  /* Responsive adjustments for action toolbar */
  @media (max-width: 768px) {
    .filter-row:last-child {
      flex-direction: column;
    }
  }

  .filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #083E40;
    font-size: 14px;
  }

  .filter-group select,
  .filter-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    height: 42px;
    box-sizing: border-box;
    background-color: white;
    position: relative;
    z-index: 1;
  }

  /* Custom dropdown arrow for select - remove default arrow */
  .filter-group select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23083E40' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
    padding-right: 40px;
    overflow: visible;
  }

  /* Ensure select text is visible and not overlapped */
  .filter-group select option {
    padding: 10px 12px;
    background-color: white;
    color: #083E40;
  }

  .filter-group select:focus,
  .filter-group input:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
    z-index: 2;
  }

  /* Fix for data source select specifically */
  #dataSourceSelect {
    padding-right: 40px !important;
    overflow: visible !important;
    text-overflow: ellipsis;
  }

  #dataSourceSelect option {
    padding: 10px 12px;
    white-space: normal;
  }

  .btn-filter {
    background: #083E40;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 42px;
    box-sizing: border-box;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .btn-filter:hover {
    background: #889717;
    transform: translateY(-2px);
  }

  .btn-reset {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 42px;
    box-sizing: border-box;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
  }

  .btn-reset:hover {
    background: #5a6268;
  }

  /* Table Styles - Match daftarPembayaran */
  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
    overflow: hidden;
  }

  .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(8, 62, 64, 0.3) transparent;
  }

  .table-responsive::-webkit-scrollbar {
    height: 12px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: rgba(8, 62, 64, 0.05);
    border-radius: 6px;
    margin: 0 20px;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.3), rgba(136, 151, 23, 0.4));
    border-radius: 6px;
    border: 2px solid rgba(255, 255, 255, 0.8);
  }

  .table {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1400px;
    width: 100%;
  }

  .table thead {
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .table thead th {
    background: #083E40;
    color: white;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    white-space: nowrap;
    text-align: left;
  }

  .table thead th:first-child {
    border-top-left-radius: 8px;
  }

  .table thead th:last-child {
    border-top-right-radius: 8px;
  }

  .table tbody tr {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    background: white;
  }

  .table tbody tr:hover {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.02) 0%, rgba(136, 151, 23, 0.02) 100%);
  }

  .table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(8, 62, 64, 0.08);
    vertical-align: middle;
    font-size: 13px;
  }

  .table tbody tr:last-child td {
    border-bottom: none;
  }

  /* Color coding untuk umur hutang */
  .row-hijau {
    background-color: #d4edda !important;
  }

  .row-hijau:hover {
    background: linear-gradient(135deg, rgba(212, 237, 218, 0.8) 0%, rgba(212, 237, 218, 0.6) 100%) !important;
  }

  .row-kuning {
    background-color: #fff3cd !important;
  }

  .row-kuning:hover {
    background: linear-gradient(135deg, rgba(255, 243, 205, 0.8) 0%, rgba(255, 243, 205, 0.6) 100%) !important;
  }

  .row-merah {
    background-color: #f8d7da !important;
  }

  .row-merah:hover {
    background: linear-gradient(135deg, rgba(248, 215, 218, 0.8) 0%, rgba(248, 215, 218, 0.6) 100%) !important;
  }

  .row-merah-gelap {
    background-color: #f5c6cb !important;
  }

  .row-merah-gelap:hover {
    background: linear-gradient(135deg, rgba(245, 198, 203, 0.8) 0%, rgba(245, 198, 203, 0.6) 100%) !important;
  }

  /* Progress Bar */
  .progress-container {
    width: 100%;
    height: 24px;
    background-color: #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
  }

  .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745 0%, #889717 100%);
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 11px;
    font-weight: 600;
  }

  /* Badge Status */
  .badge-status {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
  }

  .badge-lunas {
    background-color: #d4edda;
    color: #155724;
  }

  .badge-parsial {
    background-color: #fff3cd;
    color: #856404;
  }

  .badge-belum-lunas {
    background-color: #f8d7da;
    color: #721c24;
  }

  /* Widget Dokumen Terlama */
  .widget-terlama {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 4px 16px rgba(8, 62, 64, 0.08);
  }

  .widget-title {
    font-size: 18px;
    font-weight: 700;
    color: #083E40;
    margin-bottom: 16px;
  }

  .widget-item {
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .widget-item:last-child {
    border-bottom: none;
  }

  .widget-item-info {
    flex: 1;
  }

  .widget-item-label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 4px;
  }

  .widget-item-value {
    font-size: 15px;
    font-weight: 600;
    color: #083E40;
  }

  .widget-item-umur {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
  }

  .widget-item-umur.merah {
    background-color: #f8d7da;
    color: #721c24;
  }

  /* Pagination Styles */
  .pagination-wrapper {
    padding: 20px 25px;
    border-top: 1px solid rgba(8, 62, 64, 0.08);
    margin-top: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 12px;
  }

  /* Pagination Styles - Same as daftarPembayaran */
  .pagination-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
  }

  .per-page-wrapper label {
    font-size: 13px;
    color: #083E40;
    font-weight: 500;
    margin: 0;
  }

  .per-page-wrapper select {
    padding: 6px 12px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    border-radius: 8px;
    background: white;
    color: #083E40;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    min-width: 60px;
    transition: all 0.3s ease;
  }

  .per-page-wrapper select:hover {
    border-color: #889717;
  }

  .per-page-wrapper select:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .pagination li {
    display: inline-block;
  }

  .pagination a,
  .pagination span {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    background-color: white;
    cursor: pointer;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    color: #083E40;
    transition: all 0.3s ease;
    min-width: 40px;
    height: 40px;
    text-decoration: none;
  }

  .pagination a:hover:not(.disabled),
  .btn-pagination:hover:not(:disabled) {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
    transform: translateY(-2px);
  }

  .btn-pagination {
    padding: 8px 12px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background-color: white;
    cursor: pointer;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    color: #083E40;
    transition: all 0.3s ease;
    min-width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    font-family: inherit;
  }

  .btn-pagination.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-pagination:disabled {
    background: #e0e0e0;
    color: #9e9e9e;
    border-color: rgba(8, 62, 64, 0.1);
    cursor: not-allowed;
    opacity: 0.6;
  }

  .btn-pagination-nav {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: rgba(8, 62, 64, 0.15);
  }

  .btn-pagination-nav:hover:not(:disabled) {
    background: linear-gradient(135deg, #0a4f52 0%, #889717 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-pagination-dots {
    background: transparent;
    border: none;
    color: #999;
    cursor: default;
    padding: 8px 4px;
  }

  /* Clean Pagination Styles - Match rekapanDokumen */
  .pagination-wrapper {
    padding: 20px 25px;
    border-top: 1px solid rgba(8, 62, 64, 0.08);
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .pagination {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
  }

  .pagination button {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
    margin: 0;
  }

  .pagination button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: transparent;
  }

  .pagination button:disabled {
    background: #e0e0e0;
    color: #9e9e9e;
    border-color: rgba(8, 62, 64, 0.1);
    cursor: not-allowed;
    opacity: 0.6;
  }

  .pagination button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .pagination a {
    text-decoration: none;
    display: inline-flex;
  }

  .pagination button:disabled:hover {
    transform: none;
    box-shadow: none;
  }

  .btn-chevron {
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .btn-chevron:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-chevron i {
    font-size: 14px;
    line-height: 1;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .filter-row {
      flex-direction: column;
    }

    .filter-group {
      min-width: 100%;
    }

    .table-container {
      overflow-x: scroll;
    }

    .pagination-wrapper {
      padding: 15px !important;
    }

    .pagination-wrapper > div {
      flex-direction: column;
      align-items: stretch !important;
      gap: 12px !important;
    }

    .pagination {
      justify-content: center;
      flex-wrap: wrap;
    }

    .pagination a,
    .pagination span {
      min-width: 36px;
      height: 36px;
      padding: 6px 10px;
      font-size: 12px;
    }
  }

  /* Pagination Footer Styles - Match Dashboard Admin */
  .pagination-footer {
    background: white;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-top: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .dark .pagination-footer {
    background: #1e293b; /* slate-800 */
    border-color: #334155; /* slate-700 */
  }

  .pagination-footer-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .pagination-footer-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .pagination-label {
    font-size: 14px;
    color: #4a5568;
    white-space: nowrap;
    transition: color 0.3s ease;
  }

  .dark .pagination-label {
    color: #94a3b8; /* slate-400 */
  }

  .pagination-select {
    padding: 6px 32px 6px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    color: #1a202c;
    cursor: pointer;
    transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234a5568' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 12px;
  }

  .dark .pagination-select {
    background: #0f172a; /* slate-900 */
    border-color: #334155; /* slate-700 */
    color: #f1f5f9; /* slate-100 */
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%94a3b8' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  }

  .pagination-select:focus {
    outline: none;
    border-color: #083E40;
    box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
  }

  .pagination-summary {
    font-size: 14px;
    color: #4a5568;
    white-space: nowrap;
    transition: color 0.3s ease;
  }

  .dark .pagination-summary {
    color: #94a3b8; /* slate-400 */
  }

  .pagination-btn {
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: white;
    color: #4a5568;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .dark .pagination-btn {
    background: #0f172a; /* slate-900 */
    border-color: #334155; /* slate-700 */
    color: #94a3b8; /* slate-400 */
  }

  .pagination-btn:hover:not(:disabled) {
    background: #f7fafc;
    border-color: #083E40;
    color: #083E40;
  }

  .dark .pagination-btn:hover:not(:disabled) {
    background: #1e293b; /* slate-800 */
    border-color: #60a5fa; /* blue-400 */
    color: #60a5fa; /* blue-400 */
  }

  .pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f7fafc;
    border-color: #e2e8f0;
    color: #a0aec0;
  }

  .dark .pagination-btn:disabled {
    background: #1e293b; /* slate-800 */
    border-color: #334155; /* slate-700 */
    color: #475569; /* slate-600 */
  }

  .pagination-page-input {
    width: 60px;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    text-align: center;
    background: white;
    color: #1a202c;
    transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
  }

  .dark .pagination-page-input {
    background: #0f172a; /* slate-900 */
    border-color: #334155; /* slate-700 */
    color: #f1f5f9; /* slate-100 */
  }

  .pagination-page-input:focus {
    outline: none;
    border-color: #083E40;
    box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
  }

  .pagination-total-pages {
    font-size: 14px;
    color: #4a5568;
    white-space: nowrap;
    transition: color 0.3s ease;
  }

  .dark .pagination-total-pages {
    color: #94a3b8; /* slate-400 */
  }

  /* Responsive Pagination Footer */
  @media (max-width: 768px) {
    .pagination-footer {
      flex-direction: column;
      align-items: stretch;
    }

    .pagination-footer-left,
    .pagination-footer-right {
      width: 100%;
      justify-content: center;
    }
  }

  /* Excel-Like Modal Styles */
  .excel-modal-header {
    font-family: 'Courier New', monospace;
  }

  .excel-grid-table {
    border-collapse: collapse;
  }

  .excel-header-cell {
    background: #00FF00 !important;
    color: #000 !important;
    font-weight: 700 !important;
  }

  .excel-label-cell {
    background: #f0f0f0;
    user-select: none;
  }

  .excel-input-cell {
    padding: 0 !important;
  }

  .excel-input {
    transition: background-color 0.15s ease;
  }

  .excel-input:focus {
    outline: none;
    background: #fffacd !important; /* Light yellow like Excel */
    box-shadow: inset 0 0 0 2px #083E40;
  }

  .excel-row-clickable:hover {
    background-color: #f0f9ff !important;
  }

  /* Prevent text selection on table rows */
  .excel-row-clickable td:not(:last-child) {
    user-select: none;
  }

  /* Excel-like input styling */
  .excel-input::placeholder {
    color: #999;
    opacity: 0.6;
  }
</style>

<div class="container-fluid">
  <h2>Rekapan TU/TK</h2>

  <!-- Dashboard Scorecards - Responsive Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-4 gap-4 mb-4">
    <!-- Card 1: Total Outstanding -->
    <div class="scorecard merah">
      <div class="scorecard-body">
        <div class="scorecard-header">
          <div class="scorecard-content" style="flex: 1; min-width: 0;">
            <div class="scorecard-title">Total Outstanding</div>
            <div class="scorecard-value long-number whitespace-nowrap overflow-hidden text-ellipsis" title="Rp {{ number_format($totalOutstanding ?? 0, 0, ',', '.') }}">Rp {{ number_format($totalOutstanding ?? 0, 0, ',', '.') }}</div>
            <div class="scorecard-label">Belum dibayar</div>
          </div>
          <div class="scorecard-icon flex-shrink-0">
            <i class="fa-solid fa-money-bill-wave"></i>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Card 2: Dokumen Belum Lunas -->
    <div class="scorecard kuning">
      <div class="scorecard-body">
        <div class="scorecard-header">
          <div class="scorecard-content" style="flex: 1; min-width: 0;">
            <div class="scorecard-title">Dokumen Belum Lunas</div>
            <div class="scorecard-value">{{ number_format($totalDokumenBelumLunas ?? 0, 0, ',', '.') }}</div>
            <div class="scorecard-label">Dokumen</div>
          </div>
          <div class="scorecard-icon flex-shrink-0">
            <i class="fa-solid fa-file-invoice"></i>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Card 3: Total Terbayar Tahun Ini -->
    <div class="scorecard hijau">
      <div class="scorecard-body">
        <div class="scorecard-header">
          <div class="scorecard-content" style="flex: 1; min-width: 0;">
            <div class="scorecard-title">Total Terbayar Tahun Ini</div>
            <div class="scorecard-value long-number whitespace-nowrap overflow-hidden text-ellipsis" title="Rp {{ number_format($totalTerbayarTahunIni ?? 0, 0, ',', '.') }}">Rp {{ number_format($totalTerbayarTahunIni ?? 0, 0, ',', '.') }}</div>
            <div class="scorecard-label">Tahun {{ date('Y') }}</div>
          </div>
          <div class="scorecard-icon flex-shrink-0">
            <i class="fa-solid fa-check-circle"></i>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Card 4: Jatuh Tempo Minggu Ini -->
    <div class="scorecard biru">
      <div class="scorecard-body">
        <div class="scorecard-header">
          <div class="scorecard-content" style="flex: 1; min-width: 0;">
            <div class="scorecard-title">Jatuh Tempo Minggu Ini</div>
            <div class="scorecard-value">{{ number_format($jatuhTempoMingguIni ?? 0, 0, ',', '.') }}</div>
            <div class="scorecard-label">Dokumen kritis</div>
          </div>
          <div class="scorecard-icon flex-shrink-0">
            <i class="fa-solid fa-clock"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <form method="GET" action="{{ route('reports.pembayaran.tu-tk') }}" id="filterForm">
      <!-- Data Source Selector -->
      <div class="filter-row" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef;">
        <div class="filter-group" style="flex: 1; position: relative;">
          <label style="font-weight: 700; color: #083E40; font-size: 14px; display: block; margin-bottom: 8px;">
            <i class="fa-solid fa-database me-2"></i>Pilih Sumber Data
          </label>
          <select name="data_source" class="form-control" id="dataSourceSelect" style="font-weight: 600; padding: 12px 40px 12px 12px; border: 2px solid #083E40; border-radius: 8px; width: 100%; background-color: white; position: relative; z-index: 1; overflow: visible;" onchange="this.form.submit()">
            <option value="input_ks" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_ks' ? 'selected' : '' }}>Input KS (tu_tk_2023)</option>
            <option value="input_pupuk" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_pupuk' ? 'selected' : '' }}>Input Pupuk (tu_tk_pupuk_2023)</option>
            <option value="input_tan" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_tan' ? 'selected' : '' }}>Input TAN (tu_tk_tan_2023)</option>
            <option value="input_vd" {{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_vd' ? 'selected' : '' }}>Input VD (tu_tk_vd_2023)</option>
          </select>
        </div>
      </div>
      <div class="filter-row">
        <div class="filter-group">
          <label>Status Pembayaran</label>
          <select name="status_pembayaran" class="form-control">
            <option value="">Semua</option>
            <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas</option>
            <option value="belum_lunas" {{ request('status_pembayaran') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
            <option value="parsial" {{ request('status_pembayaran') == 'parsial' ? 'selected' : '' }}>Parsial</option>
          </select>
        </div>
        @if((request('data_source', $dataSource ?? 'input_ks')) == 'input_ks')
        <div class="filter-group">
          <label>Kategori</label>
          <select name="kategori" class="form-control">
            <option value="">Semua</option>
            @foreach($kategoris ?? [] as $kat)
              <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="filter-group">
          <label>Umur Hutang</label>
          <select name="umur_hutang" class="form-control">
            <option value="">Semua</option>
            <option value="kurang_30" {{ request('umur_hutang') == 'kurang_30' ? 'selected' : '' }}>&lt; 30 Hari</option>
            <option value="30_60" {{ request('umur_hutang') == '30_60' ? 'selected' : '' }}>30 - 60 Hari</option>
            <option value="lebih_60" {{ request('umur_hutang') == 'lebih_60' ? 'selected' : '' }}>&gt; 60 Hari</option>
            <option value="lebih_1_tahun" {{ request('umur_hutang') == 'lebih_1_tahun' ? 'selected' : '' }}>&gt; 1 Tahun</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Posisi Dokumen</label>
          <select name="posisi_dokumen" class="form-control">
            <option value="">Semua</option>
            @foreach($posisiDokumens ?? [] as $pos)
              <option value="{{ $pos }}" {{ request('posisi_dokumen') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="flex flex-wrap items-end justify-between gap-4 w-full">
        <!-- Search Input - Full width on mobile, flex-grow on desktop -->
        <div class="w-full md:flex-1 min-w-0">
          <label class="block mb-2" style="font-weight: 600; color: #083E40; font-size: 14px;">Search (Agenda, No. SPP, Vendor, No. Kontrak)</label>
          <input type="text" name="search" class="form-control w-full" value="{{ request('search') }}" placeholder="Cari...">
        </div>
        
        <!-- Action Buttons Group -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
          <!-- Primary Actions: Filter & Reset -->
          <div class="flex gap-2 flex-shrink-0">
            <button type="submit" class="btn-filter" style="white-space: nowrap;">Filter</button>
            <a href="{{ route('reports.pembayaran.tu-tk') }}" class="btn-reset" style="white-space: nowrap;">Reset</a>
          </div>
          
          <!-- Divider (hidden on mobile) -->
          <div class="h-6 w-px bg-gray-300 mx-1 hidden md:block"></div>
          
          <!-- Export Actions: Excel & PDF -->
          <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('pembayaran.exportRekapanTuTk', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn-export" style="padding: 10px 16px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; height: 42px; box-sizing: border-box;">
              <i class="fa-solid fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('pembayaran.exportRekapanTuTk', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn-export" style="padding: 10px 16px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; height: 42px; box-sizing: border-box;">
              <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Widget 5 Dokumen Terlama -->
  @if(isset($dokumenTerlama) && $dokumenTerlama->count() > 0)
  <div class="widget-terlama">
    <div class="widget-title">5 Dokumen Terlama Belum Dibayar</div>
    @foreach($dokumenTerlama as $doc)
    <div class="widget-item">
      <div class="widget-item-info">
        <div class="widget-item-label">{{ $doc->AGENDA ?? '-' }} - {{ $doc->NO_SPP ?? '-' }}</div>
        <div class="widget-item-value">{{ Str::limit($doc->VENDOR ?? '-', 50) }}</div>
      </div>
      <div class="text-right">
        <div style="font-weight: 600; color: #083E40;">Rp {{ number_format($doc->BELUM_DIBAYAR ?? 0, 0, ',', '.') }}</div>
        <div class="widget-item-umur {{ $doc->warna_umur_hutang ?? 'merah' }}">
          {{ $doc->UMUR_HUTANG_HARI ?? 0 }} Hari
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  <!-- Table -->
  <div class="table-container mt-4">
    <div class="table-responsive">
      <table class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Agenda</th>
          <th>No. SPP</th>
          <th>Tgl SPP</th>
          <th>Vendor</th>
          @if((request('data_source', $dataSource ?? 'input_ks')) == 'input_ks')
          <th>Kategori</th>
          @endif
          <th>Nilai</th>
          <th>Status Pembayaran</th>
          <th>Progress</th>
          <th>Umur Hutang</th>
          <th>Posisi Dokumen</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($dokumens ?? [] as $index => $dokumen)
        @php
          $status = $dokumen->status_pembayaran ?? 'belum_lunas';
          $persentase = $dokumen->persentase_pembayaran ?? 0;
          $warnaUmur = $dokumen->warna_umur_hutang ?? 'hijau';
          $rowClass = 'row-' . $warnaUmur;
        @endphp
        @php
          $currentDataSource = request('data_source', $dataSource ?? 'input_ks');
          
          // CRITICAL FIX: Use AGENDA as unique identifier instead of KONTROL
          // Because all documents have KONTROL = 1 in database (not unique)
          // AGENDA is unique for each document (e.g., 676_2025, 677_2025, etc.)
          $agenda = $dokumen->AGENDA ?? null;
          
          // Fallback: Still get kontrol for backward compatibility, but use AGENDA as primary identifier
          if ($currentDataSource == 'input_ks') {
              $kontrolId = $dokumen->KONTROL ?? null;
          } else {
              $kontrolId = $dokumen->EXTRA_COL_0 ?? null;
          }
          
          $belumDibayarValue = $currentDataSource == 'input_ks' ? ($dokumen->BELUM_DIBAYAR ?? 0) : ($dokumen->BELUM_DIBAYAR_1 ?? 0);
          $noSpp = $dokumen->NO_SPP ?? '-';
          $vendor = Str::limit($dokumen->VENDOR ?? '-', 50);
          $nilai = $dokumen->NILAI ?? 0;
        @endphp
        <tr class="{{ $rowClass }} excel-row-clickable" 
            onclick="openExcelPaymentModal('{{ $agenda }}', '{{ $agenda }}', '{{ $noSpp }}', '{{ $vendor }}', {{ $nilai }}, {{ $dokumen->JUMLAH_DIBAYAR ?? 0 }}, {{ $belumDibayarValue }}, '{{ $currentDataSource }}')"
            data-agenda="{{ $agenda }}"
            data-kontrol-id="{{ $kontrolId }}"
            style="cursor: pointer; transition: background-color 0.2s ease;"
            onmouseover="this.style.backgroundColor='#f0f9ff'" 
            onmouseout="this.style.backgroundColor=''">
          <td>{{ ($dokumens->currentPage() - 1) * $dokumens->perPage() + $index + 1 }}</td>
          <td><strong>{{ $agenda }}</strong></td>
          <td>{{ $noSpp }}</td>
          <td>{{ $dokumen->TGL_SPP ?? '-' }}</td>
          <td>{{ Str::limit($dokumen->VENDOR ?? '-', 30) }}</td>
          @if((request('data_source', $dataSource ?? 'input_ks')) == 'input_ks')
          <td>{{ $dokumen->KATEGORI ?? '-' }}</td>
          @endif
          <td><strong>Rp {{ number_format($nilai, 0, ',', '.') }}</strong></td>
          <td>
            @if($status == 'lunas')
              <span class="badge-status badge-lunas">Lunas</span>
            @elseif($status == 'parsial')
              <span class="badge-status badge-parsial">Parsial</span>
            @else
              <span class="badge-status badge-belum-lunas">Belum Lunas</span>
            @endif
          </td>
          <td>
            <div class="progress-container">
              <div class="progress-bar" style="width: {{ min($persentase, 100) }}%">
                {{ number_format($persentase, 1) }}%
              </div>
            </div>
            <div style="font-size: 11px; color: #6c757d; margin-top: 4px;">
              Dibayar: Rp {{ number_format($dokumen->JUMLAH_DIBAYAR ?? 0, 0, ',', '.') }} | 
              Sisa: Rp {{ number_format($belumDibayarValue, 0, ',', '.') }}
            </div>
          </td>
          <td>
            <strong>{{ $dokumen->UMUR_HUTANG_HARI ?? 0 }}</strong> Hari
          </td>
          <td>{{ $dokumen->POSISI_DOKUMEN ?? '-' }}</td>
          <td>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;" onclick="event.stopPropagation();">
              <button class="btn-input-payment" onclick="event.stopPropagation(); openPaymentModal('{{ $kontrolId }}', {{ $nilai }}, {{ $dokumen->JUMLAH_DIBAYAR ?? 0 }}, {{ $belumDibayarValue }}, '{{ $currentDataSource }}')" style="padding: 6px 12px; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s ease;">
                <i class="fa-solid fa-money-bill-wave me-1"></i> Input Pembayaran
              </button>
              <button class="btn-timeline" onclick="event.stopPropagation(); openTimelineModal('{{ $kontrolId }}', '{{ $currentDataSource }}')" style="padding: 6px 12px; background: linear-gradient(135deg, #889717 0%, #a0b02a 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s ease;">
                <i class="fa-solid fa-history me-1"></i> Timeline
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="{{ (request('data_source', $dataSource ?? 'input_ks')) == 'input_ks' ? '12' : '11' }}" style="text-align: center; padding: 40px; color: #6c757d;">
            <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
            <h5>Belum ada dokumen</h5>
            <p>Tidak ada dokumen untuk filter yang dipilih</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>

    <!-- Pagination Footer -->
    @if($dokumens->hasPages())
      @include('owner.partials.pagination-footer', ['paginator' => $dokumens])
    @endif
  </div>
</div>

<!-- Modal: Input Pembayaran Bertahap (Old Style - Keep for button trigger) -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
        <h5 class="modal-title" id="paymentModalLabel">
          <i class="fa-solid fa-money-bill-wave me-2"></i>Input Pembayaran Bertahap
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Progress Bar -->
        <div class="payment-progress-container" style="margin-bottom: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-weight: 600; color: #083E40;">Progress Pembayaran</span>
            <span id="progressPercentage" style="font-weight: 600; color: #083E40;">0%</span>
          </div>
          <div class="progress" style="height: 24px; border-radius: 12px; background: #e9ecef; overflow: hidden;">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #083E40 0%, #889717 100%); transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px;">
              0%
            </div>
          </div>
          <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6c757d;">
            <span>Dibayar: <strong id="totalDibayar">Rp 0</strong></span>
            <span>Sisa: <strong id="sisaBayar">Rp 0</strong></span>
            <span>Total: <strong id="totalNilai">Rp 0</strong></span>
          </div>
        </div>

        <!-- Payment History -->
        <div id="paymentHistory" style="margin-bottom: 24px; max-height: 200px; overflow-y: auto;">
          <h6 style="font-weight: 600; color: #083E40; margin-bottom: 12px;">Riwayat Pembayaran</h6>
          <div id="paymentHistoryList" style="display: flex; flex-direction: column; gap: 8px;">
            <p class="text-muted text-center" style="padding: 20px;">Belum ada riwayat pembayaran</p>
          </div>
        </div>

        <!-- Form Input Pembayaran -->
        <form id="paymentForm">
          <input type="hidden" id="paymentKontrol" name="kontrol">
          <input type="hidden" id="paymentDataSource" name="data_source" value="{{ request('data_source', $dataSource ?? 'input_ks') }}">
          <input type="hidden" id="paymentSequence" name="payment_sequence" value="1">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="paymentTanggal" class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="paymentTanggal" name="tanggal_bayar" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="paymentJumlah" class="form-label">Jumlah Pembayaran (Rp) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="paymentJumlah" name="jumlah" step="0.01" min="0.01" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="paymentKeterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="paymentKeterangan" name="keterangan" rows="2" placeholder="Keterangan pembayaran (opsional)"></textarea>
          </div>

          <div class="alert alert-info" style="background: #e7f3ff; border-color: #b3d9ff; color: #004085;">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Info:</strong> Pembayaran akan disimpan sebagai pembayaran ke-<span id="currentSequence">1</span>. Maksimal 6 kali pembayaran bertahap.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="submitPayment()" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
          <i class="fa-solid fa-save me-2"></i>Simpan Pembayaran
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Excel-Like Payment Input -->
<div class="modal fade" id="excelPaymentModal" tabindex="-1" aria-labelledby="excelPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" style="border-radius: 0; border: 2px solid #000;">
      <!-- Header: Document Info (Gray Background) -->
      <div class="excel-modal-header" style="background: #d3d3d3; padding: 12px 16px; border-bottom: 2px solid #000;">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex gap-4" style="font-family: 'Courier New', monospace; font-size: 13px; font-weight: 600; color: #000;">
            <div><strong>No. Agenda:</strong> <span id="excelAgenda">-</span></div>
            <div><strong>No. SPP:</strong> <span id="excelNoSpp">-</span></div>
            <div><strong>Vendor:</strong> <span id="excelVendor">-</span></div>
            <div><strong>Total Nilai:</strong> <span id="excelTotalNilai">Rp 0</span></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.7;"></button>
        </div>
      </div>

      <!-- Body: Excel Grid -->
      <div class="modal-body p-0">
        <div class="excel-grid-container" style="overflow-x: auto;">
          <table class="excel-grid-table" style="width: 100%; border-collapse: collapse; font-family: 'Courier New', monospace;">
            <!-- Header Row (Green Neon Background) -->
            <thead>
              <tr>
                <th class="excel-header-cell" style="background: #00FF00; color: #000; font-weight: 700; padding: 8px; border: 1px solid #000; text-align: center; font-size: 12px;">TAHAP</th>
                <th class="excel-header-cell" style="background: #00FF00; color: #000; font-weight: 700; padding: 8px; border: 1px solid #000; text-align: center; font-size: 12px;">NOMINAL (Rp)</th>
                <th class="excel-header-cell" style="background: #00FF00; color: #000; font-weight: 700; padding: 8px; border: 1px solid #000; text-align: center; font-size: 12px;">TANGGAL BAYAR</th>
                <th class="excel-header-cell" style="background: #00FF00; color: #000; font-weight: 700; padding: 8px; border: 1px solid #000; text-align: center; font-size: 12px;">KETERANGAN</th>
              </tr>
            </thead>
            <tbody>
              <!-- Row 1: Tahap 1 -->
              <tr>
                <td class="excel-label-cell" style="background: #f0f0f0; padding: 6px 12px; border: 1px solid #000; font-weight: 600; font-size: 12px; text-align: center; width: 100px;">Tahap 1</td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input nominal-input" 
                         id="nominal_1" 
                         name="payments[1][nominal]"
                         tabindex="1"
                         placeholder="0"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; text-align: right; background: #fff;"
                         oninput="formatCurrency(this)"
                         onfocus="this.select()">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="date" 
                         class="excel-input date-input" 
                         id="tanggal_1" 
                         name="payments[1][tanggal]"
                         tabindex="2"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input keterangan-input" 
                         id="keterangan_1" 
                         name="payments[1][keterangan]"
                         tabindex="3"
                         placeholder="Opsional"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
              </tr>
              <!-- Row 2: Tahap 2 -->
              <tr>
                <td class="excel-label-cell" style="background: #f0f0f0; padding: 6px 12px; border: 1px solid #000; font-weight: 600; font-size: 12px; text-align: center;">Tahap 2</td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input nominal-input" 
                         id="nominal_2" 
                         name="payments[2][nominal]"
                         tabindex="4"
                         placeholder="0"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; text-align: right; background: #fff;"
                         oninput="formatCurrency(this)"
                         onfocus="this.select()">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="date" 
                         class="excel-input date-input" 
                         id="tanggal_2" 
                         name="payments[2][tanggal]"
                         tabindex="5"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input keterangan-input" 
                         id="keterangan_2" 
                         name="payments[2][keterangan]"
                         tabindex="6"
                         placeholder="Opsional"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
              </tr>
              <!-- Row 3: Tahap 3 -->
              <tr>
                <td class="excel-label-cell" style="background: #f0f0f0; padding: 6px 12px; border: 1px solid #000; font-weight: 600; font-size: 12px; text-align: center;">Tahap 3</td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input nominal-input" 
                         id="nominal_3" 
                         name="payments[3][nominal]"
                         tabindex="7"
                         placeholder="0"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; text-align: right; background: #fff;"
                         oninput="formatCurrency(this)"
                         onfocus="this.select()">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="date" 
                         class="excel-input date-input" 
                         id="tanggal_3" 
                         name="payments[3][tanggal]"
                         tabindex="8"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input keterangan-input" 
                         id="keterangan_3" 
                         name="payments[3][keterangan]"
                         tabindex="9"
                         placeholder="Opsional"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
              </tr>
              <!-- Row 4: Tahap 4 -->
              <tr>
                <td class="excel-label-cell" style="background: #f0f0f0; padding: 6px 12px; border: 1px solid #000; font-weight: 600; font-size: 12px; text-align: center;">Tahap 4</td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input nominal-input" 
                         id="nominal_4" 
                         name="payments[4][nominal]"
                         tabindex="10"
                         placeholder="0"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; text-align: right; background: #fff;"
                         oninput="formatCurrency(this)"
                         onfocus="this.select()">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="date" 
                         class="excel-input date-input" 
                         id="tanggal_4" 
                         name="payments[4][tanggal]"
                         tabindex="11"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
                <td class="excel-input-cell" style="padding: 0; border: 1px solid #000;">
                  <input type="text" 
                         class="excel-input keterangan-input" 
                         id="keterangan_4" 
                         name="payments[4][keterangan]"
                         tabindex="12"
                         placeholder="Opsional"
                         style="width: 100%; border: none; padding: 6px 8px; font-size: 12px; font-family: 'Courier New', monospace; background: #fff;">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Footer: Save Button -->
      <div class="modal-footer" style="background: #f5f5f5; padding: 12px 16px; border-top: 2px solid #000; justify-content: flex-end;">
        <input type="hidden" id="excelPaymentKontrol" name="kontrol">
        <input type="hidden" id="excelPaymentDataSource" name="data_source">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 8px 16px; font-size: 13px; border-radius: 0;">Batal</button>
        <button type="button" class="btn btn-primary" onclick="submitExcelPayment()" style="background: #083E40; border: none; padding: 8px 24px; font-size: 13px; font-weight: 600; border-radius: 0;">
          <i class="fa-solid fa-save me-2"></i>Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page'); // Reset to page 1 when changing per_page
    window.location.href = url.toString();
}

let currentKontrol = null; // Backward compatibility - stores agenda
let currentAgenda = null; // New: Store agenda separately
let currentNilai = 0;
let currentDibayar = 0;
let currentBelumDibayar = 0;
let currentDataSource = 'input_ks';
let currentRowData = null; // Store row data for Excel modal

// Excel-Like Modal Function
// CRITICAL FIX: First parameter is now AGENDA (not kontrol) because all documents have KONTROL = 1
function openExcelPaymentModal(agenda, agendaDisplay, noSpp, vendor, nilai, dibayar, belumDibayar, dataSource = 'input_ks') {
    // CRITICAL: Validate agenda (now used as unique identifier)
    if (!agenda || agenda === 'null' || agenda === '' || agenda === '-') {
        console.error('Invalid agenda:', agenda);
        alert('Error: Nomor Agenda tidak valid. Silakan refresh halaman dan coba lagi.');
        return;
    }
    
    console.log('=== Opening Excel Payment Modal ===');
    console.log('Agenda (Identifier):', agenda);
    console.log('Agenda (Display):', agendaDisplay);
    console.log('No SPP:', noSpp);
    console.log('DataSource:', dataSource);
    
    // Use AGENDA as the unique identifier
    currentKontrol = agenda; // Store agenda as kontrol for backward compatibility
    currentAgenda = agenda; // New: Store agenda separately
    currentNilai = parseFloat(nilai) || 0;
    currentDibayar = parseFloat(dibayar) || 0;
    currentBelumDibayar = parseFloat(belumDibayar) || 0;
    currentDataSource = dataSource || 'input_ks';
    
    // Store row data
    currentRowData = {
        agenda: agenda, // Use AGENDA as primary identifier
        kontrol: agenda, // For backward compatibility
        agendaDisplay: agendaDisplay,
        noSpp: noSpp,
        vendor: vendor,
        nilai: currentNilai,
        dibayar: currentDibayar,
        belumDibayar: currentBelumDibayar,
        dataSource: currentDataSource
    };

    // Set header info
    document.getElementById('excelAgenda').textContent = agendaDisplay;
    document.getElementById('excelNoSpp').textContent = noSpp;
    document.getElementById('excelVendor').textContent = vendor;
    document.getElementById('excelTotalNilai').textContent = 'Rp ' + formatNumber(currentNilai);
    
    // Set hidden inputs
    // CRITICAL: Use AGENDA as identifier instead of KONTROL
    document.getElementById('excelPaymentKontrol').value = agenda; // Store agenda in kontrol field
    document.getElementById('excelPaymentDataSource').value = currentDataSource;
    
    // Clear all inputs FIRST (CRITICAL: Clear before loading to prevent stale data)
    for (let i = 1; i <= 4; i++) {
        const nominalInput = document.getElementById('nominal_' + i);
        const tanggalInput = document.getElementById('tanggal_' + i);
        const keteranganInput = document.getElementById('keterangan_' + i);
        
        if (nominalInput) nominalInput.value = '';
        if (tanggalInput) tanggalInput.value = '';
        if (keteranganInput) keteranganInput.value = '';
    }
    
    console.log('Opening modal for agenda:', agenda, 'dataSource:', dataSource);
    
    // Load existing payment data if any
    // CRITICAL: Pass AGENDA (not kontrol) as identifier
    // Use setTimeout to ensure inputs are cleared before loading
    setTimeout(() => {
        loadExcelPaymentHistory(agenda, dataSource); // Use agenda as identifier
    }, 100);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('excelPaymentModal'));
    modal.show();
    
    // Focus first input
    setTimeout(() => {
        document.getElementById('nominal_1').focus();
    }, 300);
}

function loadExcelPaymentHistory(agenda, dataSource) {
    // Use provided dataSource or fallback to currentDataSource
    const source = dataSource || currentDataSource || 'input_ks';
    
    console.log('Loading payment history for agenda:', agenda, 'dataSource:', source);
    
    // CRITICAL FIX: Use AGENDA as identifier instead of KONTROL
    fetch(`/rekapan-tu-tk/payment-logs-by-agenda/${encodeURIComponent(agenda)}?data_source=${source}`)
        .then(response => response.json())
        .then(data => {
            console.log('Loaded payment history for agenda', agenda, ':', data);
            
            // Populate inputs with existing payment data
            // Use payment_sequence from data, not array index
            data.forEach((log) => {
                const tahap = log.payment_sequence; // Use sequence from data (1, 2, 3, or 4)
                if (tahap >= 1 && tahap <= 4) {
                    const nominalInput = document.getElementById('nominal_' + tahap);
                    const tanggalInput = document.getElementById('tanggal_' + tahap);
                    const keteranganInput = document.getElementById('keterangan_' + tahap);
                    
                    if (nominalInput) {
                        if (log.jumlah && log.jumlah > 0) {
                            // Format nominal with thousand separators
                            nominalInput.value = formatNumber(log.jumlah);
                            console.log(`Populated tahap ${tahap} - Nominal:`, log.jumlah);
                        } else {
                            nominalInput.value = '';
                        }
                    }
                    
                    if (tanggalInput) {
                        if (log.tanggal_bayar) {
                            // Extract date part (YYYY-MM-DD) from datetime string
                            const dateStr = log.tanggal_bayar.split(' ')[0];
                            tanggalInput.value = dateStr;
                            console.log(`Populated tahap ${tahap} - Tanggal:`, dateStr);
                        } else {
                            tanggalInput.value = '';
                        }
                    }
                    
                    if (keteranganInput) {
                        if (log.keterangan) {
                            keteranganInput.value = log.keterangan;
                            console.log(`Populated tahap ${tahap} - Keterangan:`, log.keterangan);
                        } else {
                            keteranganInput.value = '';
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading payment history for agenda', agenda, ':', error);
        });
}

function formatCurrency(input) {
    // Remove non-numeric characters
    let value = input.value.replace(/[^\d]/g, '');
    
    // Format with thousand separators
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
        input.value = value;
    } else {
        input.value = '';
    }
}

// Enhanced keyboard navigation for Excel-like modal
document.addEventListener('DOMContentLoaded', function() {
    // Handle Enter key to move to next field
    const excelInputs = document.querySelectorAll('#excelPaymentModal .excel-input');
    excelInputs.forEach((input, index) => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextIndex = index + 1;
                if (nextIndex < excelInputs.length) {
                    excelInputs[nextIndex].focus();
                    excelInputs[nextIndex].select();
                } else {
                    // If last input, focus save button
                    document.querySelector('#excelPaymentModal .btn-primary').focus();
                }
            }
            
            // Handle Arrow keys for navigation
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const currentRow = parseInt(input.id.split('_')[1]);
                const currentType = input.id.split('_')[0];
                if (currentRow < 4) {
                    const nextInput = document.getElementById(currentType + '_' + (currentRow + 1));
                    if (nextInput) {
                        nextInput.focus();
                        nextInput.select();
                    }
                }
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                const currentRow = parseInt(input.id.split('_')[1]);
                const currentType = input.id.split('_')[0];
                if (currentRow > 1) {
                    const prevInput = document.getElementById(currentType + '_' + (currentRow - 1));
                    if (prevInput) {
                        prevInput.focus();
                        prevInput.select();
                    }
                }
            }
        });
    });
    
    // Auto-focus first input when modal is shown
    const excelModal = document.getElementById('excelPaymentModal');
    if (excelModal) {
        excelModal.addEventListener('shown.bs.modal', function() {
            const firstInput = document.getElementById('nominal_1');
            if (firstInput) {
                setTimeout(() => {
                    firstInput.focus();
                    firstInput.select();
                }, 100);
            }
        });
    }
});

function getNumericValue(input) {
    // Remove all non-numeric characters (including dots and commas from formatting)
    const cleaned = input.value.replace(/[^\d]/g, '');
    return parseInt(cleaned) || 0;
}

function submitExcelPayment() {
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';
    
    // Collect ALL payment data (including empty ones for batch update)
    const batchData = {
        kontrol: currentKontrol,
        data_source: currentDataSource
    };
    
    // Collect data for all 4 stages
    for (let i = 1; i <= 4; i++) {
        const nominalInput = document.getElementById('nominal_' + i);
        const tanggalInput = document.getElementById('tanggal_' + i);
        const keteranganInput = document.getElementById('keterangan_' + i);
        
        const nominal = getNumericValue(nominalInput);
        
        // CRITICAL: For date input (type="date"), check if value exists
        // Date input returns empty string "" if not filled, or "YYYY-MM-DD" if filled
        let tanggal = null; // Default to null
        if (tanggalInput && tanggalInput.value) {
            const dateValue = tanggalInput.value.trim();
            // Only set tanggal if it's a valid date string (YYYY-MM-DD format, at least 8 chars)
            if (dateValue && dateValue.length >= 8 && dateValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
                tanggal = dateValue;
            }
        }
        // If tanggalInput doesn't exist or value is empty/null, tanggal remains null
        
        const keterangan = keteranganInput && keteranganInput.value ? keteranganInput.value.trim() : null;
        if (keterangan === '' || keterangan === 'Opsional') {
            keterangan = null;
        }
        
        // Always include all stages (even if empty) for batch update
        // This ensures we can clear/reset stages that were previously filled
        batchData['tanggal_bayar_' + i] = tanggal; // null if empty, or valid date string "YYYY-MM-DD"
        batchData['jumlah' + i] = nominal;
        batchData['keterangan_' + i] = keterangan;
        
        // Debug log
        console.log(`Stage ${i}: tanggal=${tanggal}, jumlah=${nominal}, keterangan=${keterangan}`);
    }
    
    // Validate: At least one stage must have nominal > 0
    let hasValidPayment = false;
    for (let i = 1; i <= 4; i++) {
        if (batchData['jumlah' + i] > 0) {
            hasValidPayment = true;
            break;
        }
    }
    
    if (!hasValidPayment) {
        alert('Minimal isi 1 tahap pembayaran dengan nominal!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
    }
    
    // Submit batch update via AJAX
    // Use route() helper to generate URL from route name
    const batchUrl = '{{ route("pembayaran.storePaymentInstallmentBatch") }}';
    console.log('Submitting to:', batchUrl);
    console.log('Batch data:', batchData);
    
    fetch(batchUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(batchData)
    })
    .then(response => {
        // Check if response is OK
        if (!response.ok) {
            // If response is not OK, try to get error message
            return response.text().then(text => {
                console.error('HTTP Error:', response.status, text);
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`);
            });
        }
        // Try to parse as JSON
        return response.json();
    })
    .then(result => {
        if (result.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('excelPaymentModal'));
            modal.hide();
            
            // Reload page to update table
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Gagal menyimpan pembayaran: ' + (result.message || 'Unknown error'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan pembayaran: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function openPaymentModal(kontrol, nilai, dibayar, belumDibayar, dataSource = 'input_ks') {
    currentKontrol = kontrol;
    currentNilai = parseFloat(nilai) || 0;
    currentDibayar = parseFloat(dibayar) || 0;
    currentBelumDibayar = parseFloat(belumDibayar) || 0;
    currentDataSource = dataSource || 'input_ks';

    // Set form values
    document.getElementById('paymentKontrol').value = kontrol;
    document.getElementById('paymentDataSource').value = currentDataSource;
    document.getElementById('paymentTanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('paymentJumlah').value = '';
    document.getElementById('paymentKeterangan').value = '';

    // Calculate next sequence
    const nextSequence = Math.min(6, Math.floor(currentDibayar / (currentNilai / 6)) + 1);
    document.getElementById('paymentSequence').value = nextSequence;
    document.getElementById('currentSequence').textContent = nextSequence;

    // Update progress bar
    updateProgressBar();

    // Load payment history
    loadPaymentHistory(kontrol);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

function updateProgressBar() {
    const percentage = currentNilai > 0 ? (currentDibayar / currentNilai) * 100 : 0;
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressBar').textContent = percentage.toFixed(1) + '%';
    document.getElementById('progressPercentage').textContent = percentage.toFixed(1) + '%';
    document.getElementById('totalDibayar').textContent = 'Rp ' + formatNumber(currentDibayar);
    document.getElementById('sisaBayar').textContent = 'Rp ' + formatNumber(currentBelumDibayar);
    document.getElementById('totalNilai').textContent = 'Rp ' + formatNumber(currentNilai);
}

function loadPaymentHistory(kontrol) {
    const dataSource = currentDataSource || 'input_ks';
    fetch(`/rekapan-tu-tk/payment-logs/${kontrol}?data_source=${dataSource}`)
        .then(response => response.json())
        .then(data => {
            const historyList = document.getElementById('paymentHistoryList');
            if (data.length === 0) {
                historyList.innerHTML = '<p class="text-muted text-center" style="padding: 20px;">Belum ada riwayat pembayaran</p>';
            } else {
                historyList.innerHTML = data.map(log => `
                    <div style="padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #083E40;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Pembayaran ke-${log.payment_sequence}</strong>
                                <div style="font-size: 12px; color: #6c757d; margin-top: 4px;">
                                    ${new Date(log.tanggal_bayar).toLocaleDateString('id-ID')} - Rp ${formatNumber(log.jumlah)}
                                </div>
                                ${log.keterangan ? `<div style="font-size: 11px; color: #6c757d; margin-top: 4px;">${log.keterangan}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading payment history:', error);
        });
}

function submitPayment() {
    const form = document.getElementById('paymentForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    // Show loading
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';

    fetch('/rekapan-tu-tk/payment-installment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success message
            alert('Pembayaran berhasil disimpan!');
            
            // Close modal and reload page
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            modal.hide();
            window.location.reload();
        } else {
            alert('Gagal menyimpan pembayaran: ' + result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan pembayaran');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function openTimelineModal(kontrol, dataSource = 'input_ks') {
    // Show loading
    const timelineList = document.getElementById('timelineList');
    timelineList.innerHTML = '<div class="text-center p-4"><i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Memuat timeline...</p></div>';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('timelineModal'));
    modal.show();

    // Load timeline data
    fetch(`/rekapan-tu-tk/position-timeline/${kontrol}?data_source=${dataSource}`)
        .then(response => response.json())
        .then(data => {
            const timeline = data.timeline;
            const tuTk = data.tu_tk;

            // Update document info
            document.getElementById('timelineDocInfo').innerHTML = `
                <strong>Agenda:</strong> ${tuTk.AGENDA || '-'} | 
                <strong>No. SPP:</strong> ${tuTk.NO_SPP || '-'} | 
                <strong>Vendor:</strong> ${tuTk.VENDOR || '-'}
            `;

            // Render timeline
            if (timeline.length === 0) {
                timelineList.innerHTML = '<div class="text-center p-4 text-muted">Belum ada riwayat tracking</div>';
            } else {
                timelineList.innerHTML = timeline.map(item => `
                    <div class="timeline-item" style="position: relative; padding-left: 40px; padding-bottom: 24px; border-left: 2px solid ${item.color};">
                        <div class="timeline-icon" style="position: absolute; left: -10px; top: 0; width: 20px; height: 20px; background: ${item.color}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">
                            <i class="fa-solid ${item.icon}"></i>
                        </div>
                        <div class="timeline-content" style="background: #f8f9fa; padding: 16px; border-radius: 8px; border-left: 4px solid ${item.color};">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <h6 style="margin: 0; color: #083E40; font-weight: 600;">${item.title}</h6>
                                <span style="font-size: 12px; color: #6c757d;">${new Date(item.date).toLocaleString('id-ID')}</span>
                            </div>
                            <p style="margin: 0 0 8px 0; color: #495057; font-size: 14px;">${item.description}</p>
                            ${item.keterangan ? `<p style="margin: 0; color: #6c757d; font-size: 12px; font-style: italic;">${item.keterangan}</p>` : ''}
                            <div style="margin-top: 8px; font-size: 11px; color: #6c757d;">
                                <i class="fa-solid fa-user me-1"></i> ${item.changed_by || 'System'}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading timeline:', error);
            timelineList.innerHTML = '<div class="alert alert-danger">Gagal memuat timeline</div>';
        });
}
</script>

<!-- Modal: Timeline Tracking -->
<div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
        <h5 class="modal-title" id="timelineModalLabel">
          <i class="fa-solid fa-history me-2"></i>Timeline Tracking Dokumen
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="timelineDocInfo" style="padding: 12px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #083E40;">
          <!-- Document info will be loaded here -->
        </div>
        <div id="timelineList" style="max-height: 500px; overflow-y: auto;">
          <!-- Timeline items will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection

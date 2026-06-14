@extends('layouts/app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Table Container - Enhanced Horizontal Scroll from perpajakan */

    /* Horizontal Scroll Container - Enhanced from perpajakan */
    .table-responsive {
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
      scrollbar-color: rgba(26, 77, 62, 0.3) transparent;
    }

    .table-responsive::-webkit-scrollbar {
      height: 12px;
    }

    .table-responsive::-webkit-scrollbar-track {
      background: rgba(26, 77, 62, 0.05);
      border-radius: 6px;
      margin: 0 20px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, rgba(26, 77, 62, 0.3), rgba(15, 61, 46, 0.4));
      border-radius: 6px;
      border: 2px solid rgba(255, 255, 255, 0.8);
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, rgba(26, 77, 62, 0.5), rgba(15, 61, 46, 0.6));
    }

    .data-table {
      border-collapse: separate;
      border-spacing: 0;
      min-width: 1600px;
      /* Minimum width for horizontal scroll with all columns */
      width: 100%;
    }

    /* Column Widths - Enhanced from perpajakan */
    .data-table .col-number {
      width: 60px;
      min-width: 60px;
      text-align: center;
      font-weight: 600;
    }

    .data-table .col-agenda {
      width: 150px;
      min-width: 150px;
      text-align: center;
    }

    .data-table .col-tanggal {
      width: 140px;
      min-width: 140px;
      text-align: center;
    }

    .data-table .col-spp {
      width: 160px;
      min-width: 160px;
      text-align: center;
    }

    .data-table .col-nilai {
      width: 150px;
      min-width: 150px;
      text-align: center;
    }

    .data-table .col-tanggal-spp {
      width: 140px;
      min-width: 140px;
      text-align: center;
    }

    .data-table .col-uraian {
      width: 700px;
      min-width: 500px;
      max-width: 1000px;
      text-align: left;
      word-wrap: break-word;
      white-space: normal;
      overflow-wrap: break-word;
      line-height: 1.6;
      vertical-align: top;
      padding: 12px;
    }

    .data-table .col-uraian span {
      display: block;
      word-wrap: break-word;
      white-space: normal;
      overflow-wrap: break-word;
      line-height: 1.6;
      width: 100%;
    }

    .data-table .col-dibayar_kepada,
    .data-table td.col-dibayar_kepada {
      width: 250px;
      min-width: 200px;
      max-width: 300px;
      word-wrap: break-word;
      white-space: normal !important;
      overflow-wrap: break-word;
      line-height: 1.6;
      vertical-align: middle;
      text-align: center;
      padding: 12px;
    }

    .data-table .col-deadline {
      width: 180px;
      min-width: 180px;
      text-align: center;
    }

    .data-table .col-status {
      width: 320px;
      min-width: 320px;
      max-width: 350px;
      text-align: center;
      overflow: visible;
    }

    .data-table .col-action {
      width: 180px;
      min-width: 180px;
      text-align: center;
      overflow: visible;
      position: relative;
      box-sizing: border-box;
    }

    .data-table tbody tr {
      /* Jangan transition: all — agar baris tidak menganimasikan layout saat lebar konten/sidebar berubah (anti-lag) */
      transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease, border-color 0.3s ease;
      border-bottom: 1px solid rgba(26, 77, 62, 0.08);
      position: relative;
      border-left: 3px solid transparent;
    }

    .data-table tbody td {
      font-weight: 700;
    }

    /* Enhanced Locked Row Styling from perpajakan */
    .data-table tbody tr.locked-row {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      opacity: 0.85;
      position: relative;
      border-left: 4px solid #ffc107 !important;
    }

    .data-table tbody tr.locked-row:hover {
      background: linear-gradient(135deg, #fff8e1 0%, #fff3c4 100%);
      border-left: 4px solid #ffc107 !important;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15);
    }

    /* Regular row hover effect */
    .data-table tbody tr:hover {
      background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
      border-left: 3px solid #1a4d3e;
      transform: scale(1.002);
    }


    /* Selected row styling */
    .data-table tbody tr.selected {
      background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
      border-left: 3px solid #1a4d3e;
    }

    .data-table tbody td {
      padding: 16px;
      vertical-align: middle;
      border-right: 1px solid rgba(26, 77, 62, 0.05);
      font-size: 13px;
      border-bottom: 1px solid rgba(26, 77, 62, 0.05);
      text-align: center;
      font-weight: 500;
      color: #374151;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* Custom centering for specific column content */
    .data-table .col-number,
    .data-table .col-agenda,
    .data-table .col-nilai,
    .data-table .col-tanggal-spp,
    .data-table .col-status,
    .data-table .col-deadline,
    .data-table .col-action {
      text-align: center;
    }

    .data-table .col-tanggal {
      text-align: center;
      font-weight: 600;
    }

    .data-table .col-spp {
      text-align: center;
      font-weight: 600;
    }

    .data-table .col-uraian {
      width: 700px;
      min-width: 500px;
      max-width: 1000px;
      text-align: left;
      font-weight: 600;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
      overflow-wrap: break-word;
      line-height: 1.6;
      vertical-align: top;
      padding: 12px;
      hyphens: auto;
      overflow-wrap: break-word;
    }

    /* Override uraian column header to center */
    .data-table thead th.col-uraian {
      text-align: center;
    }

    /* Special styling for centered content */
    .data-table td[colspan] {
      text-align: left;
    }

    /* Center agenda content properly */
    .data-table td.col-agenda>strong,
    .data-table td.col-agenda>small {
      display: block;
      text-align: center;
    }

    /* Center deadline content */
    .data-table td.col-deadline>small,
    .data-table td.col-deadline>span {
      display: block;
      text-align: center;
    }

    /* Deadline card design matching perpajakan style */
    .deadline-card {
      position: relative;
      background: white;
      border-radius: 12px;
      padding: 10px 12px;
      border: 2px solid transparent;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
      margin: 0 auto;
      max-width: 150px;
    }

    .deadline-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      transition: height 0.3s ease;
    }

    .deadline-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      border-color: var(--deadline-color);
    }

    .deadline-card:hover::before {
      height: 5px;
    }

    .deadline-time {
      font-size: 11px;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .deadline-time i {
      font-size: 10px;
      color: var(--deadline-color);
    }

    .deadline-indicator {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 5px 12px;
      border-radius: 20px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .deadline-indicator::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.5s ease;
    }

    .deadline-card:hover .deadline-indicator::before {
      left: 100%;
    }

    /* Safe State - Green Theme (>= 1 hari) */
    .deadline-card.deadline-safe {
      --deadline-color: #10b981;
      --deadline-color-light: #34d399;
      --deadline-bg: #ecfdf5;
      --deadline-text: #065f46;
    }

    .deadline-card.deadline-safe {
      background: var(--deadline-bg);
      border-color: rgba(16, 185, 129, 0.2);
    }

    .deadline-card.deadline-safe .deadline-time {
      color: var(--deadline-text);
    }

    .deadline-indicator.deadline-safe {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(16, 185, 129, 0.4);
    }

    .deadline-indicator.deadline-safe i::before {
      content: "\f058";
      /* check-circle */
    }

    /* Warning State - Yellow Theme (< 1 hari) */
    .deadline-card.deadline-warning {
      --deadline-color: #f59e0b;
      --deadline-color-light: #fbbf24;
      --deadline-bg: #fffbeb;
      --deadline-text: #92400e;
    }

    .deadline-card.deadline-warning {
      background: var(--deadline-bg);
      border-color: rgba(245, 158, 11, 0.2);
    }

    .deadline-card.deadline-warning .deadline-time {
      color: var(--deadline-text);
    }

    .deadline-indicator.deadline-warning {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(245, 158, 11, 0.4);
    }

    .deadline-indicator.deadline-warning i::before {
      content: "\f071";
      /* exclamation-triangle */
    }

    /* Overdue State - Red Theme (Terlambat) */
    .deadline-card.deadline-overdue {
      --deadline-color: #dc2626;
      --deadline-color-light: #ef4444;
      --deadline-bg: #fef2f2;
      --deadline-text: #991b1b;
    }

    .deadline-card.deadline-overdue {
      background: var(--deadline-bg);
      border-color: rgba(220, 38, 38, 0.3);
      animation: overdue-alert 3s infinite;
    }

    .deadline-card.deadline-overdue .deadline-time {
      color: var(--deadline-text);
      font-weight: 800;
    }

    .deadline-indicator.deadline-overdue {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 4px 16px rgba(220, 38, 68, 0.5);
      font-weight: 800;
      animation: overdue-glow 1.5s infinite;
    }

    .deadline-indicator.deadline-overdue i::before {
      content: "\f071";
      /* exclamation-triangle */
      animation: warning-shake 1s infinite;
    }

    /* New System: Age-based deadline colors (count up from received_at) */
    /* Green - Aman (<1 hari) */
    .deadline-card.deadline-green {
      --deadline-color: #10b981;
      --deadline-color-light: #34d399;
      --deadline-bg: #ecfdf5;
      --deadline-text: #065f46;
    }

    .deadline-card.deadline-green {
      background: var(--deadline-bg) !important;
      border-color: rgba(16, 185, 129, 0.2) !important;
    }

    .deadline-card.deadline-green .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-green {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(16, 185, 129, 0.4);
    }

    /* Yellow - Perlu Perhatian (>=1 hari <3 hari) */
    .deadline-card.deadline-yellow {
      --deadline-color: #f59e0b;
      --deadline-color-light: #fbbf24;
      --deadline-bg: #fffbeb;
      --deadline-text: #92400e;
    }

    .deadline-card.deadline-yellow {
      background: var(--deadline-bg) !important;
      border-color: rgba(245, 158, 11, 0.2) !important;
    }

    .deadline-card.deadline-yellow .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-yellow {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(245, 158, 11, 0.4);
    }

    /* Red - Terlambat (>=3 hari) */
    .deadline-card.deadline-red {
      --deadline-color: #ef4444;
      --deadline-color-light: #f87171;
      --deadline-bg: #fef2f2;
      --deadline-text: #991b1b;
    }

    .deadline-card.deadline-red {
      background: var(--deadline-bg) !important;
      border-color: rgba(239, 68, 68, 0.2) !important;
    }

    .deadline-card.deadline-red .deadline-time {
      color: var(--deadline-text) !important;
      font-weight: 800;
    }

    .deadline-indicator.deadline-red {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(239, 68, 68, 0.4);
      animation: danger-pulse 2s infinite;
    }

    /* Completed State - Dark Green Theme */
    .deadline-card.deadline-completed {
      --deadline-color: #15803d;
      --deadline-color-light: #16a34a;
      --deadline-bg: #f0fdf4;
      --deadline-text: #14532d;
      opacity: 0.9;
    }

    .deadline-card.deadline-completed {
      background: var(--deadline-bg) !important;
      border-color: rgba(21, 128, 61, 0.2) !important;
    }

    .deadline-card.deadline-completed .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-completed {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(21, 128, 61, 0.4);
    }

    .deadline-indicator.deadline-completed i::before {
      content: "\f058";
      /* check-double */
    }

    /* Sent State - Grey Theme */
    .deadline-card.deadline-sent {
      --deadline-color: #6b7280;
      --deadline-color-light: #9ca3af;
      --deadline-bg: #f3f4f6;
      --deadline-text: #374151;
      opacity: 0.8;
    }

    .deadline-card.deadline-sent {
      background: var(--deadline-bg) !important;
      border-color: rgba(107, 114, 128, 0.2) !important;
    }

    .deadline-card.deadline-sent .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-indicator.deadline-sent {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(107, 114, 128, 0.4);
    }

    .deadline-indicator.deadline-sent i::before {
      content: "\f1d8";
      /* paper-plane */
    }

    /* OVERRIDE: Force gray color for deadline-sent indicators regardless of other color classes */
    .deadline-card.deadline-sent .deadline-indicator,
    .deadline-card.deadline-sent .deadline-indicator.deadline-green,
    .deadline-card.deadline-sent .deadline-indicator.deadline-yellow,
    .deadline-card.deadline-sent .deadline-indicator.deadline-red {
      background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%) !important;
      color: white !important;
      box-shadow: 0 2px 6px rgba(107, 114, 128, 0.3) !important;
    }

    /* OVERRIDE: Force gray theme for entire deadline card when sent, regardless of color classes */
    .deadline-card.deadline-sent.deadline-green,
    .deadline-card.deadline-sent.deadline-yellow,
    .deadline-card.deadline-sent.deadline-red {
      --deadline-color: #6b7280 !important;
      --deadline-color-light: #9ca3af !important;
      --deadline-bg: #f9fafb !important;
      --deadline-text: #4b5563 !important;
      background: #f9fafb !important;
      border-color: rgba(107, 114, 128, 0.2) !important;
      opacity: 0.8;
    }

    .deadline-card.deadline-sent.deadline-green .deadline-time,
    .deadline-card.deadline-sent.deadline-yellow .deadline-time,
    .deadline-card.deadline-sent.deadline-red .deadline-time {
      color: #4b5563 !important;
    }

    /* Paused State - Grey Theme for returned documents */
    .deadline-card.deadline-paused {
      --deadline-color: #9ca3af;
      --deadline-color-light: #d1d5db;
      --deadline-bg: #f3f4f6;
      --deadline-text: #6b7280;
      opacity: 0.75;
    }

    .deadline-card.deadline-paused {
      background: var(--deadline-bg) !important;
      border-color: rgba(156, 163, 175, 0.3) !important;
      border-style: dashed !important;
    }

    .deadline-card.deadline-paused .deadline-time {
      color: var(--deadline-text) !important;
    }

    .deadline-card.deadline-paused .deadline-indicator,
    .deadline-card.deadline-paused .deadline-indicator.deadline-green,
    .deadline-card.deadline-paused .deadline-indicator.deadline-yellow,
    .deadline-card.deadline-paused .deadline-indicator.deadline-red {
      background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%) !important;
      color: white !important;
      box-shadow: 0 2px 6px rgba(156, 163, 175, 0.3) !important;
    }

    .deadline-card.deadline-paused .deadline-indicator i::before {
      content: "\f04c";
      /* pause icon */
    }

    .deadline-card.deadline-paused.deadline-green,
    .deadline-card.deadline-paused.deadline-yellow,
    .deadline-card.deadline-paused.deadline-red {
      --deadline-color: #9ca3af !important;
      --deadline-color-light: #d1d5db !important;
      --deadline-bg: #f3f4f6 !important;
      --deadline-text: #6b7280 !important;
      background: #f3f4f6 !important;
      border-color: rgba(156, 163, 175, 0.3) !important;
      border-style: dashed !important;
      opacity: 0.75;
    }

    .deadline-card.deadline-paused.deadline-green .deadline-time,
    .deadline-card.deadline-paused.deadline-yellow .deadline-time,
    .deadline-card.deadline-paused.deadline-red .deadline-time {
      color: #6b7280 !important;
    }

    .deadline-card.deadline-paused .deadline-paused-label {
      font-size: 8px;
      color: #9ca3af;
      margin-top: 4px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Enhanced late information */
    .late-info {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 10px;
      font-weight: 700;
      margin-top: 8px;
      padding: 6px 10px;
      border-radius: 20px;
      background: linear-gradient(135deg, rgba(220, 38, 68, 0.1) 0%, rgba(239, 68, 68, 0.15) 100%);
      border: 1px solid rgba(220, 38, 68, 0.3);
      color: #991b1b;
      animation: late-warning 2s infinite;
    }

    .late-info i {
      font-size: 11px;
      color: #dc2626;
    }

    .late-info .late-text {
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .deadline-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg,
          var(--deadline-color) 0%,
          var(--deadline-color-light) 50%,
          var(--deadline-color) 100%);
      border-radius: 0 0 10px 10px;
      transform-origin: left;
      transition: transform 0.5s ease;
    }

    .deadline-note {
      font-size: 9px;
      color: #6b7280;
      font-style: italic;
      margin-top: 4px;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      text-align: center;
    }

    .no-deadline {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #9ca3af;
      font-size: 11px;
      font-style: italic;
      padding: 8px 12px;
      border-radius: 20px;
      background: #f9fafb;
      border: 1px dashed #d1d5db;
      transition: all 0.3s ease;
    }

    .no-deadline:hover {
      background: #f3f4f6;
      border-color: #9ca3af;
    }

    .no-deadline i {
      font-size: 11px;
      opacity: 0.7;
    }

    @keyframes overdue-alert {

      0%,
      85%,
      100% {
        border-color: rgba(220, 38, 38, 0.3);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      }

      90%,
      95% {
        border-color: rgba(220, 38, 38, 0.8);
        box-shadow: 0 0 16px rgba(220, 38, 38, 0.4);
      }
    }

    @keyframes overdue-glow {

      0%,
      100% {
        box-shadow: 0 4px 16px rgba(220, 38, 68, 0.5);
        transform: translateY(0);
      }

      50% {
        box-shadow: 0 6px 24px rgba(220, 38, 68, 0.7);
        transform: translateY(-1px);
      }
    }

    @keyframes late-warning {

      0%,
      100% {
        background: linear-gradient(135deg, rgba(220, 38, 68, 0.1) 0%, rgba(239, 68, 68, 0.15) 100%);
        transform: scale(1);
      }

      50% {
        background: linear-gradient(135deg, rgba(220, 38, 68, 0.15) 0%, rgba(239, 68, 68, 0.25) 100%);
        transform: scale(1.02);
      }
    }

    @keyframes warning-shake {

      0%,
      100% {
        transform: translateX(0) rotate(0deg);
      }

      25% {
        transform: translateX(-1px) rotate(-1deg);
      }

      75% {
        transform: translateX(1px) rotate(1deg);
      }
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
      .deadline-card {
        padding: 8px 10px;
        max-width: 130px;
      }

      .deadline-time {
        font-size: 10px;
      }

      .deadline-indicator {
        font-size: 9px;
        padding: 4px 10px;
      }

      .late-info {
        font-size: 9px;
        padding: 4px 8px;
        margin-top: 6px;
      }

      .deadline-note {
        font-size: 8px;
      }
    }

    @media (max-width: 576px) {
      .deadline-card {
        padding: 6px 8px;
        max-width: 120px;
      }

      .deadline-time {
        font-size: 9px;
      }

      .deadline-indicator {
        font-size: 8px;
        padding: 3px 8px;
      }

      .deadline-note {
        font-size: 7px;
      }

      .no-deadline {
        font-size: 9px;
        padding: 6px 10px;
      }

      .late-info {
        font-size: 8px;
        padding: 3px 6px;
      }
    }

    /* Enhanced Badge Styles matching perpajakan */
    .badge-status {
      padding: 8px 16px;
      border-radius: 25px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.5px;
      box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
      border: 2px solid transparent;
      text-align: center;
      min-width: 100px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.3s ease;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      white-space: nowrap;
    }

    /* State 1: 🔒 Terkunci (Locked - Waiting for Deadline) */
    .badge-status.badge-locked {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      border-color: #495057;
      position: relative;
    }

    /* State 2: ⏳ Diproses (In Progress) */
    .badge-status.badge-proses {
      background: linear-gradient(135deg, #2E6F68 0%, #2A605A 100%);
      color: white;
      border-color: #2A605A;
      box-shadow: 0 3px 12px rgba(46, 111, 104, 0.25);
    }

    .badge-status.badge-proses::after {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      background: #4CAF50;
      border-radius: 50%;
      margin-left: 6px;
      animation: pulse 1.5s infinite;
    }

    /* State 3: ✅ Selesai (Completed) */
    .badge-status.badge-selesai {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border-color: #083E40;
    }

    .badge-status.badge-belum {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      border-color: #495057;
    }

    .badge-status.badge-dikembalikan {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
      border-color: #dc3545;
      position: relative;
    }

    .badge-status.badge-dikembalikan::before {
      content: '⚠️';
      margin-right: 4px;
    }

    /* Special state for sent documents */
    .badge-status.badge-sent {
      background: #083E40;
      color: white;
      border-color: #083E40;
      position: relative;
    }

    .badge-status.badge-warning {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
      color: white;
      border-color: #ffc107;
      position: relative;
    }

    .badge-status:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    /* Enhanced Action Buttons matching perpajakan */
    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 6px;
      justify-content: center;
      align-items: center;
      width: 100%;
      position: relative;
      z-index: 1;
      box-sizing: border-box;
      max-width: 100%;
    }

    /* Hybrid Layout: Full-width button on top, row buttons below */
    .action-buttons-hybrid {
      display: flex;
      flex-direction: column;
      gap: 6px;
      width: 100%;
    }

    .action-buttons-hybrid .btn-full-width {
      width: 100%;
      min-width: 100%;
    }

    .action-buttons-hybrid .action-row {
      display: flex;
      gap: 6px;
      justify-content: center;
      align-items: center;
      width: 100%;
    }

    .action-buttons-hybrid .action-row .btn-action {
      flex: 1;
      min-width: 0;
    }

    .btn-kembalikan {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }

    .btn-kembalikan:hover {
      background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
      color: white;
    }

    .action-row {
      display: flex;
      gap: 6px;
      justify-content: center;
      align-items: center;
      width: 100%;
    }

    .btn-action {
      min-width: 44px;
      min-height: 44px;
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      width: auto !important;
      flex: 0 0 auto;
      white-space: nowrap;
      cursor: pointer;
      font-size: 11px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      position: relative;
      overflow: visible;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      text-decoration: none;
      user-select: none;
      max-width: 140px;
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .btn-action span {
      font-size: 10px;
      font-weight: 600;
      white-space: nowrap;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      display: inline-block;
    }

    .btn-action.btn-full-width span {
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      display: inline-block;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .btn-edit {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
    }

    .btn-edit:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
      color: white;
    }

    .btn-kirim {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
      color: white;
    }

    .btn-kirim:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #0f6f74 100%);
    }

    .btn-detail {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
    }

    .btn-detail:hover {
      background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
      color: white;
    }

    .btn-send {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
      /* Dark teal gradient */
      color: white;
      position: relative;
      overflow: hidden;
    }

    .btn-send::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn-send:hover::before {
      left: 100%;
    }

    .btn-send:hover {
      background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #0f6f74 100%);
      /* Darker teal on hover */
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.4);
      /* Dark teal shadow */
      border-color: rgba(8, 62, 64, 0.6);
    }

    .btn-send i {
      transition: transform 0.3s ease;
    }

    .btn-send:hover i {
      transform: translateX(2px);
    }

    .btn-send:disabled {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      cursor: not-allowed;
      opacity: 0.6;
    }

    .btn-send:disabled:hover {
      transform: none;
      box-shadow: none;
    }

    .btn-sent {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      cursor: default;
      opacity: 0.85;
    }

    .btn-sent:hover {
      transform: none;
      box-shadow: none;
    }

    /* Destination Card Hover Effect */
    #destination-pembayaran:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2) !important;
      border-color: #0a4f52 !important;
    }

    #destination-pembayaran input[type="radio"]:checked+label {
      color: #083E40;
    }

    .btn-action.locked {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
      cursor: not-allowed;
      opacity: 0.7;
    }

    .btn-action.locked:hover {
      transform: none;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-terkirim {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
      color: white;
      cursor: default;
    }

    .btn-terkirim:hover {
      transform: none;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-set-deadline {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%) !important;
      color: white;
      border: 2px solid rgba(255, 193, 7, 0.3);
      position: relative;
      overflow: hidden;
    }

    .btn-set-deadline::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      animation: shimmer 2s infinite;
    }

    .btn-set-deadline:hover {
      background: linear-gradient(135deg, #ff8c00 0%, #e67300 100%) !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
      border-color: rgba(255, 193, 7, 0.6);
    }

    .btn-set-deadline i {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.5;
        transform: scale(1.2);
      }
    }

    @keyframes shimmer {
      0% {
        left: -100%;
      }

      100% {
        left: 100%;
      }
    }

    @keyframes fadeInOut {

      0%,
      100% {
        opacity: 0.3;
      }

      50% {
        opacity: 1;
      }
    }

    /* Enhanced Table Organization */
    .table-container-header {
      background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
      color: white;
      padding: 12px 20px;
      border-radius: 12px 12px 0 0;
      margin: -30px -30px 20px -30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .table-container-title {
      font-size: 14px;
      font-weight: 600;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .table-container-stats {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .stat-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 2px;
    }

    .stat-value {
      font-size: 16px;
      font-weight: 700;
    }

    .stat-label {
      font-size: 10px;
      opacity: 0.8;
      text-transform: uppercase;
    }

    /* Enhanced Row Separation */
    .data-table tbody tr:not(:last-child)::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 16px;
      right: 16px;
      height: 1px;
      background: linear-gradient(90deg, transparent 0%, rgba(26, 77, 62, 0.1) 20%, rgba(26, 77, 62, 0.1) 80%, transparent 100%);
    }

    /* Responsive Design - Mobile Optimization */
    @media (max-width: 768px) {

      .data-table td {
        padding: 12px 8px;
        font-size: 12px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      }

      .badge-status {
        padding: 6px 12px;
        font-size: 11px;
        min-width: 80px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      }

      .action-buttons {
        gap: 4px;
      }

      .btn-action {
        min-width: 40px;
        min-height: 40px;
        padding: 6px 10px;
        font-size: 10px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      }

      .btn-action span {
        font-size: 9px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      }

      /* Enhanced mobile horizontal scroll */
      .table-responsive {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        /* Hide scrollbar on mobile */
      }

      .table-responsive::-webkit-scrollbar {
        display: none;
      }

      /* Add scroll hint for mobile */
      .table-responsive::after {
        content: '→ Swipe to see more →';
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(26, 77, 62, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 10px;
        z-index: 5;
        animation: fadeInOut 3s infinite;
      }
    }

    @media (max-width: 576px) {
      .data-table {
        min-width: 1600px;
        /* Still allow horizontal scroll on very small screens */
      }

      .data-table .col-number {
        min-width: 60px;
        font-weight: 600;
      }

      .data-table .col-agenda {
        min-width: 130px;
      }

      .data-table .col-tanggal {
        min-width: 130px;
        font-weight: 600;
      }

      .data-table .col-spp {
        min-width: 140px;
        font-weight: 600;
      }

      .data-table .col-nilai {
        min-width: 140px;
      }

      .data-table .col-tanggal-spp {
        min-width: 130px;
      }

      .data-table .col-uraian {
        width: 500px;
        min-width: 400px;
        max-width: 700px;
        word-wrap: break-word;
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.6;
        vertical-align: top;
        padding: 12px;
        font-weight: 600;
      }

      .data-table .col-deadline {
        min-width: 160px;
      }

      .data-table .col-status {
        min-width: 140px;
      }

      .data-table .col-action {
        min-width: 160px;
      }
    }

    /* Deadline styling */
    .deadline-soon {
      color: #dc3545;
      font-weight: 600;
    }

    .deadline-normal {
      color: #2c3e50;
    }

    /* Detail Row Styles - Enhanced from perpajakan */
    .detail-row {
      display: none;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .detail-row.show {
      display: table-row;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .detail-content {
      padding: 20px;
      border-top: 2px solid rgba(26, 77, 62, 0.1);
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      width: 100%;
      box-sizing: border-box;
      overflow-x: hidden;
    }

    /* Detail Grid */
    .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 16px;
      width: 100%;
      box-sizing: border-box;
    }

    @media (min-width: 1400px) {
      .detail-grid {
        grid-template-columns: repeat(5, 1fr);
      }
    }

    @media (min-width: 1200px) and (max-width: 1399px) {
      .detail-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }

    @media (max-width: 1199px) {
      .detail-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      }
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 14px;
      background: white;
      border-radius: 8px;
      border: 1px solid rgba(8, 62, 64, 0.08);
      transition: all 0.2s ease;
    }

    .detail-item:hover {
      border-color: #889717;
      box-shadow: 0 2px 8px rgba(136, 151, 23, 0.1);
      transform: translateY(-1px);
    }

    .detail-label {
      font-size: 11px;
      font-weight: 600;
      color: #083E40;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .detail-value {
      font-size: 13px;
      color: #333;
      font-weight: 500;
      word-break: break-word;
    }

    /* Separator for Perpajakan Data */
    .detail-section-separator {
      margin: 32px 0 24px 0;
      padding: 0;
    }

    .separator-content {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px 20px;
      background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
      border-radius: 12px;
      border-left: 4px solid #ffc107;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.15);
    }

    .separator-content i {
      font-size: 20px;
      color: #ffc107;
    }

    .separator-content span:first-of-type {
      font-weight: 600;
      color: #856404;
      font-size: 14px;
    }

    .tax-badge {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
      color: white;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
      white-space: nowrap;
      margin-left: auto;
    }

    /* Tax Section Styling */
    .tax-section {
      position: relative;
    }

    .tax-section .detail-item {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border: 1px solid rgba(255, 193, 7, 0.15);
      position: relative;
    }

    .tax-section .detail-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 3px;
      height: 100%;
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
      opacity: 0.3;
      border-radius: 3px 0 0 3px;
    }

    .tax-section .detail-item:hover {
      border-color: rgba(255, 193, 7, 0.4);
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.1);
      transform: translateY(-2px);
    }

    .tax-section .detail-item:hover::before {
      opacity: 1;
    }

    .empty-field {
      color: #999;
      font-style: italic;
      font-size: 12px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .tax-link {
      color: #0066cc;
      text-decoration: none;
      word-break: break-all;
    }

    .tax-link:hover {
      text-decoration: underline;
    }

    /* Badge styles for detail view */
    .badge {
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      display: inline-block;
    }

    .badge.badge-selesai {
      background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
      color: white;
    }

    .badge.badge-proses {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
      color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {

      .data-table tbody td {
        padding: 10px 8px;
        font-size: 12px;
      }

      .btn-action {
        padding: 4px 6px;
        font-size: 10px;
      }

      .detail-content {
        padding: 16px;
      }

      .detail-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
      }

      .detail-item {
        padding: 10px;
      }

      .detail-label {
        font-size: 10px;
      }

      .detail-value {
        font-size: 12px;
      }

      .tax-badge {
        font-size: 10px;
        padding: 4px 12px;
      }
    }

    /* Modal Customization Styles */
    .customization-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      overflow-y: auto;
      padding: 20px;
      box-sizing: border-box;
    }

    .customization-modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .modal-content-custom {
      background: white;
      border-radius: 20px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
      max-width: 90%;
      width: 90%;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        transform: translateY(-30px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .modal-header-custom {
      background: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
      padding: 24px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
    }

    .modal-header-custom h3 {
      margin: 0;
      font-size: 24px;
      font-weight: 600;
      color: #212529;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .modal-body-custom {
      padding: 24px 32px;
      flex: 1;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .customization-grid {
      display: flex;
      flex-direction: column;
      gap: 24px;
      flex: 1;
      min-height: 0;
    }

    .selection-panel {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 24px;
      border: 1px solid #e9ecef;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
    }

    .panel-title {
      font-size: 18px;
      font-weight: 600;
      color: #212529;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .panel-description {
      font-size: 13px;
      color: #6c757d;
      margin-bottom: 16px;
      line-height: 1.6;
    }

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .panel-actions {
      display: flex;
      gap: 8px;
    }

    .btn-select-action {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 500;
      border: 1px solid #e5e7eb;
      background: #fff;
      color: #374151;
      cursor: pointer;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-select-action:hover {
      border-color: #083E40;
      color: #083E40;
    }

    .btn-select-action.btn-select-all:hover {
      background: rgba(34, 197, 94, 0.1);
      border-color: #22c55e;
      color: #22c55e;
    }

    .btn-select-action.btn-remove-all:hover {
      background: rgba(239, 68, 68, 0.1);
      border-color: #ef4444;
      color: #ef4444;
    }

    .column-selection-list {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
      max-height: 200px;
      overflow-y: auto;
      padding: 8px;
      background: white;
      border-radius: 8px;
      border: 1px solid #dee2e6;
    }

    @media (max-width: 900px) {
      .column-selection-list {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (max-width: 600px) {
      .column-selection-list {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    .column-item {
      display: flex;
      align-items: center;
      padding: 10px 12px;
      background: #ffffff;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      cursor: move;
      transition: all 0.2s ease;
      position: relative;
      user-select: none;
      min-height: 44px;
      gap: 8px;
    }

    .column-item.selected {
      border-color: #28a745;
      background: #f0f9f4;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
    }

    .column-item.dragging {
      opacity: 0.6;
      transform: scale(0.98);
    }

    .drag-handle {
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6c757d;
      cursor: grab;
      flex-shrink: 0;
      font-size: 12px;
    }

    .column-item.selected .drag-handle {
      color: #28a745;
    }

    .column-item:not(.selected) .drag-handle {
      opacity: 0.3;
      cursor: default;
    }

    .column-item-checkbox {
      width: 18px;
      height: 18px;
      cursor: pointer;
      flex-shrink: 0;
    }

    .column-item-label {
      font-size: 14px;
      color: #212529;
      font-weight: 500;
      flex: 1;
      cursor: pointer;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .column-item-order {
      width: 24px;
      height: 24px;
      background: #28a745;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 600;
      opacity: 0;
      transform: scale(0);
      transition: all 0.2s ease;
      flex-shrink: 0;
    }

    .column-item.selected .column-item-order {
      opacity: 1;
      transform: scale(1);
    }

    .preview-panel {
      background: #ffffff;
      border-radius: 12px;
      padding: 24px;
      border: 1px solid #e9ecef;
      display: flex;
      flex-direction: column;
      flex: 1;
      min-height: 0;
    }

    .preview-container {
      flex: 1;
      overflow-x: auto;
      overflow-y: auto;
      background: #f8f9fa;
      border-radius: 8px;
      padding: 16px;
      min-height: 400px;
      width: 100%;
    }

    .preview-table {
      width: 100%;
      min-width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      font-size: 13px;
      table-layout: auto;
    }

    .preview-table th {
      background: #212529;
      color: white;
      padding: 14px 12px;
      text-align: center;
      font-weight: 600;
      font-size: 12px;
      border-right: 1px solid rgba(255, 255, 255, 0.1);
      white-space: nowrap;
    }

    .preview-table td {
      padding: 12px;
      text-align: center;
      border-right: 1px solid #e9ecef;
      color: #495057;
      font-size: 13px;
    }

    .empty-preview {
      text-align: center;
      padding: 60px 20px;
      color: #6c757d;
    }

    .modal-footer-custom {
      padding: 20px 40px;
      border-top: 1px solid #e9ecef;
      background: #ffffff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-shrink: 0;
      position: sticky;
      bottom: 0;
      z-index: 100;
      box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
    }

    .selected-count {
      font-size: 15px;
      color: #495057;
      font-weight: 500;
    }

    .selected-count strong {
      color: #28a745;
      font-size: 18px;
    }

    .modal-actions {
      display: flex;
      gap: 12px;
    }

    .btn-modal {
      padding: 12px 32px;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      min-height: 48px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-cancel {
      background: #6c757d;
      color: white;
    }

    .btn-cancel:hover {
      background: #5a6268;
      transform: translateY(-1px);
    }

    .btn-save {
      background: #28a745;
      color: white;
    }

    .btn-save:hover {
      background: #218838;
      transform: translateY(-1px);
    }

    .btn-save:disabled {
      background: #adb5bd;
      cursor: not-allowed;
      transform: none;
    }
    .refresh-toast {
      position: fixed; top: 20px; right: 20px; padding: 12px 20px;
      border-radius: 10px; color: white; font-size: 14px; font-weight: 500;
      z-index: 99999; display: flex; align-items: center; gap: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.5s forwards;
    }
    .refresh-toast.success { background: linear-gradient(135deg, #28a745, #218838); }
    .refresh-toast.error { background: linear-gradient(135deg, #dc3545, #c82333); }
    @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-10px); } }
  </style>

  <script>
    (function() {
      function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
      }
      function formatNumber(n) {
        return Math.floor(n).toLocaleString('id-ID').replace(/\./g, ',');
      }
      function formatRupiah(n) {
        return 'Rp ' + Math.floor(n).toLocaleString('id-ID');
      }
      function animateCounter(el, target, duration, isRupiah) {
        const start = performance.now();
        target = parseInt(target) || 0;
        function step(now) {
          const elapsed = now - start;
          const progress = Math.min(elapsed / duration, 1);
          const eased = easeOutExpo(progress);
          const current = eased * target;
          el.textContent = isRupiah ? formatRupiah(current) : formatNumber(current);
          if (progress < 1) {
            requestAnimationFrame(step);
          } else {
            el.textContent = isRupiah ? formatRupiah(target) : formatNumber(target);
          }
        }
        requestAnimationFrame(step);
      }
      function initCounters() {
        document.querySelectorAll('[data-counter]').forEach(function(el) {
          const target = el.getAttribute('data-counter');
          const duration = parseInt(el.getAttribute('data-duration')) || 1200;
          let animated = false;
          const obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
              if (entry.isIntersecting && !animated) {
                animated = true;
                animateCounter(el, target, duration, false);
                obs.disconnect();
              }
            });
          }, { threshold: 0.3 });
          obs.observe(el);
        });
        document.querySelectorAll('[data-counter-rupiah]').forEach(function(el) {
          const target = el.getAttribute('data-counter-rupiah');
          const duration = parseInt(el.getAttribute('data-duration')) || 1600;
          let animated = false;
          const obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
              if (entry.isIntersecting && !animated) {
                animated = true;
                animateCounter(el, target, duration, true);
                obs.disconnect();
              }
            });
          }, { threshold: 0.3 });
          obs.observe(el);
        });
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCounters);
      } else {
        initCounters();
      }
    })();
  </script>



  @include('partials.document-role-filter-toolbar', [
    'filterRouteName' => 'documents.akutansi.index',
    'filterDariOptions' => $filterDariOptions ?? [],
    'filterStatusOptions' => [
      '' => 'Semua Status',
      'sedang_proses' => 'Sedang Proses',
      'terkirim_pembayaran' => 'Terkirim ke Pembayaran',
      'menunggu_approve' => 'Menunggu Approve',
      'ditolak' => 'Dokumen Ditolak',
    ],
  ])
  <!-- Tabel Dokumen — Unified Card Design -->
  <div class="table-dokumen" id="documentTableContainer">
    <div class="table-responsive" style="overflow-x:auto;">
      @php
        $showActionColumn = false;
      @endphp

      <table class="data-table document-table">

        <thead>
          <tr>
            <th class="col-number">No</th>
            @foreach($selectedColumns as $col)
              @if($col !== 'status')
                @if($col === 'nomor_agenda')
                  <th class="col-{{ $col }}" onclick="toggleSort('{{ $col }}')"
                    style="cursor:pointer; user-select:none; white-space:nowrap;">
                    {{ $availableColumns[$col] ?? $col }}
                    <span
                      style="display:inline-flex; flex-direction:column; line-height:0.5; margin-left:4px; font-size:10px; vertical-align:middle;">
                      <i class="fas fa-caret-up"
                        style="opacity:{{ (isset($sortColumn) && $sortColumn == $col && isset($sortOrder) && $sortOrder == 'asc') ? '1' : '0.3' }}"></i>
                      <i class="fas fa-caret-down"
                        style="opacity:{{ (isset($sortColumn) && $sortColumn == $col && isset($sortOrder) && $sortOrder == 'desc') ? '1' : '0.3' }}"></i>
                    </span>
                  </th>
                @else
                  <th class="col-{{ $col }}">{{ $availableColumns[$col] ?? $col }}</th>
                @endif
              @endif
            @endforeach
            <th class="col-deadline">Deadline</th>
            <th class="col-status">Status</th>
            <th class="col-handler">Pengurus Dokumen</th>
            @if($showActionColumn)
              <th class="col-action">Aksi</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @include("akutansi.dokumens._rows")
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Set Deadline -->
  <div class="modal fade" id="setDeadlineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
          <h5 class="modal-title">
            <i class="fa-solid fa-clock me-2"></i>Tetapkan Timeline Team Akutansi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="deadlineDocId">

          <div class="alert alert-info border-0"
            style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.12) 0%, rgba(255, 140, 0, 0.12) 100%); border-left: 4px solid #ffc107;">
            <i class="fa-solid fa-info-circle me-2"></i>
            Dokumen akan tetap terkunci sampai timeline ditetapkan. Setelah dibuka, dokumen dapat diedit atau dikirim.
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Periode Deadline*</label>
            <select class="form-select" id="deadlineDays" required>
              <option value="">Pilih periode deadline</option>
              <option value="1">1 hari</option>
              <option value="2">2 hari</option>
              <option value="3">3 hari</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Catatan (opsional)</label>
            <textarea class="form-control" id="deadlineNote" rows="3" maxlength="500"
              placeholder="Contoh: Menunggu kelengkapan dokumen pendukung"></textarea>
            <small class="text-muted"><span id="deadlineCharCount">0</span>/500 karakter</small>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-warning" onclick="confirmSetDeadline()">
            <i class="fa-solid fa-check me-2"></i>Tetapkan
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Deadline Success -->
  <div class="modal fade" id="deadlineSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
          <h5 class="modal-title">
            <i class="fa-solid fa-circle-check me-2"></i>Deadline Berhasil Ditetapkan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #28a745;"></i>
          </div>
          <h5 class="fw-bold mb-2">Deadline berhasil ditetapkan!</h5>
          <p class="text-muted mb-0" id="deadlineSuccessMessage">
            Dokumen sekarang terbuka untuk diproses.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-check me-2"></i>Selesai
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Return to Team Verifikasi -->
  <div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white;">
          <h5 class="modal-title">
            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Kembali ke Team Verifikasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="returnDocId">

          <div class="alert alert-info border-0"
            style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(2, 132, 199, 0.1) 100%); border-left: 4px solid #0ea5e9;">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Dokumen akan dikirim kembali ke Team Verifikasi setelah proses akuntansi selesai.
            Isi catatan singkat agar Verifikasi tahu konteks dokumen.
          </div>

          <div class="form-group mb-3">
            <label for="returnReason" class="form-label">
              <strong>Catatan Penyerahan Kembali <span class="text-danger">*</span></strong>
            </label>
            <textarea class="form-control" id="returnReason" rows="4"
              placeholder="Contoh: Data akuntansi sudah dilengkapi, dokumen fisik diserahkan kembali ke Verifikasi." maxlength="500"
              required></textarea>
            <div class="form-text">
              <small class="text-muted">Mohon isi catatan singkat dan jelas.</small><br>
              <span id="returnCharCount">0</span>/500 karakter
            </div>
          </div>

          <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Dokumen yang dikirim kembali akan:
            <ul class="mb-0 mt-2">
              <li>Muncul di daftar dokumen Team Verifikasi</li>
              <li>Tetap terlihat di daftar dokumen akuntansi sebagai riwayat kerja</li>
              <li>Tampil dengan status "Kembali ke Team Verifikasi"</li>
            </ul>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-primary" onclick="confirmReturn()">
            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Kembali
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Return Confirmation -->
  <div class="modal fade" id="returnConfirmationModal" tabindex="-1" aria-labelledby="returnConfirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white;">
          <h5 class="modal-title" id="returnConfirmationModalLabel">
            <i class="fa-solid fa-question-circle me-2"></i>Konfirmasi Kirim Kembali
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-paper-plane" style="font-size: 52px; color: #0ea5e9;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center">Apakah Anda yakin ingin mengirim kembali dokumen ini ke Team Verifikasi?</h5>
          <div class="alert alert-light border" style="background-color: #f8f9fa;">
            <div class="d-flex align-items-start">
              <i class="fa-solid fa-info-circle me-2 mt-1" style="color: #0ea5e9;"></i>
              <div>
                <strong>Catatan Penyerahan Kembali:</strong>
                <p class="mb-0 mt-2" id="returnConfirmationReason" style="color: #495057; font-size: 14px;"></p>
              </div>
            </div>
          </div>
          <p class="text-muted mb-0 text-center small">
            Dokumen akan masuk kembali ke daftar Team Verifikasi untuk pengecekan lanjutan.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center gap-2">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-primary px-4" id="confirmReturnBtn">
            <i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim Kembali
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Return Success -->
  <div class="modal fade" id="returnSuccessModal" tabindex="-1" aria-labelledby="returnSuccessModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
          <h5 class="modal-title" id="returnSuccessModalLabel">
            <i class="fa-solid fa-circle-check me-2"></i>Berhasil Dikirim Kembali
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #28a745;"></i>
          </div>
          <h5 class="fw-bold mb-3">Dokumen berhasil dikirim kembali ke Team Verifikasi!</h5>
          <p class="text-muted mb-0">
            Dokumen akan muncul di:
            <br>• Daftar dokumen Team Verifikasi
            <br>• Daftar dokumen Team Akuntansi sebagai riwayat
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-check me-2"></i>Selesai
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Return Validation Warning -->
  <div class="modal fade" id="returnValidationWarningModal" tabindex="-1"
    aria-labelledby="returnValidationWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
          <h5 class="modal-title" id="returnValidationWarningModalLabel">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>Perhatian
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-exclamation-circle" style="font-size: 52px; color: #ffc107;"></i>
          </div>
          <h5 class="fw-bold mb-3" id="returnValidationWarningTitle">Validasi Gagal</h5>
          <p class="text-muted mb-0" id="returnValidationWarningMessage">
            Terjadi kesalahan pada input data.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-check me-2"></i>Mengerti
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Send to Pembayaran -->
  <div class="modal fade" id="sendToPembayaranModal" tabindex="-1" aria-labelledby="sendToPembayaranModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
          <h5 class="modal-title" id="sendToPembayaranModalLabel">
            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Dokumen ke Bidang Berikutnya
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="sendToPembayaranDocId">

          <!-- Information Box -->
          <div class="alert alert-info border-0 mb-4"
            style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%); border-left: 4px solid #0d6efd;">
            <div class="d-flex align-items-start">
              <i class="fa-solid fa-circle-info me-2 mt-1" style="color: #0d6efd; font-size: 18px;"></i>
              <div>
                <strong>Catatan:</strong> Deadline akan ditetapkan oleh departemen tujuan (Team Pembayaran) setelah
                dokumen diterima.
              </div>
            </div>
          </div>

          <!-- Destination Selection -->
          <div class="mb-4">
            <label class="form-label fw-bold mb-3">
              <i class="fa-solid fa-paper-plane me-2"></i>Pilih Tujuan Pengiriman:
            </label>

            <!-- Team Pembayaran Option -->
            <div class="card border-2 mb-3"
              style="border-color: #083E40 !important; cursor: pointer; transition: all 0.3s ease;"
              onclick="selectDestination('pembayaran')" id="destination-pembayaran">
              <div class="card-body p-3">
                <div class="form-check d-flex align-items-start">
                  <input class="form-check-input mt-2 me-3" type="radio" name="destination" id="radioPembayaran"
                    value="pembayaran" checked>
                  <div class="flex-grow-1">
                    <label class="form-check-label fw-bold" for="radioPembayaran" style="cursor: pointer;">
                      <i class="fa-solid fa-credit-card me-2" style="color: #083E40;"></i>Team Pembayaran
                    </label>
                    <p class="text-muted mb-0 mt-2 small">
                      Untuk dokumen yang siap untuk diproses pembayaran. Status akan berubah menjadi "Siap Bayar" di
                      halaman pembayaran.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-success" onclick="confirmSendToPembayaran()"
            style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
            <i class="fa-solid fa-paper-plane me-2"></i>Kirim
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Send Success -->
  <div class="modal fade" id="sendToPembayaranSuccessModal" tabindex="-1"
    aria-labelledby="sendToPembayaranSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
          <h5 class="modal-title" id="sendToPembayaranSuccessModalLabel">
            <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #28a745;"></i>
          </div>
          <h5 class="fw-bold mb-3">Dokumen berhasil dikirim ke Team Pembayaran!</h5>
          <p class="text-muted mb-0">
            Dokumen sekarang akan muncul di inbox Team Pembayaran dan menunggu persetujuan.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal" onclick="location.reload()">
            <i class="fa-solid fa-check me-2"></i>Selesai
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Error -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
          <h5 class="modal-title" id="errorModalLabel">
            <i class="fa-solid fa-exclamation-circle me-2"></i>Terjadi Kesalahan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 52px; color: #dc3545;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="errorModalTitle">Error</h5>
          <p class="text-muted mb-0 text-center" id="errorModalMessage">
            Terjadi kesalahan yang tidak diketahui.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Warning -->
  <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
          <h5 class="modal-title" id="warningModalLabel">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>Peringatan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 52px; color: #ffc107;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="warningModalTitle">Peringatan</h5>
          <p class="text-muted mb-0 text-center" id="warningModalMessage">
            Peringatan.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-check me-2"></i>Mengerti
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Info -->
  <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white;">
          <h5 class="modal-title" id="infoModalLabel">
            <i class="fa-solid fa-circle-info me-2"></i>Informasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-circle-info" style="font-size: 52px; color: #0d6efd;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="infoModalTitle">Informasi</h5>
          <p class="text-muted mb-0 text-center" id="infoModalMessage">
            Informasi.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
            <i class="fa-solid fa-check me-2"></i>Mengerti
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Confirmation -->
  <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
          <h5 class="modal-title" id="confirmationModalLabel">
            <i class="fa-solid fa-question-circle me-2"></i>Konfirmasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fa-solid fa-question-circle" style="font-size: 52px; color: #083E40;"></i>
          </div>
          <h5 class="fw-bold mb-3 text-center" id="confirmationModalTitle">Konfirmasi</h5>
          <p class="text-muted mb-0 text-center" id="confirmationModalMessage">
            Apakah Anda yakin?
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center gap-2">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" id="confirmationModalCancel">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-success px-4" id="confirmationModalConfirm"
            style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border: none;">
            <i class="fa-solid fa-check me-2"></i>Ya
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
          /* Sort toggle function */
            function toggleSort(column) {
              const url = new URL(window.location.href);
              const params = url.searchParams;
              const currentSort = params.get('sort') || 'nomor_agenda';
              const currentOrder = params.get('order') || 'desc';
              let newOrder = 'asc';
              if (currentSort === column) {
                newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
              }
              params.set('sort', column);
              params.set('order', newOrder);
              params.delete('page');
              window.location.href = url.toString();
            }
            </script>

            <script>
            // Wrapper function untuk handle row double-click
            function handleRowClick(event, docId) {
              // Cek apakah yang diklik adalah link/tombol/input/select/textarea
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
                target.closest('.btn-action') !== null ||
                target.closest('[role="button"]') !== null;

              if (isInteractiveElement) {
                // User klik elemen interaktif, biarkan default behavior
                return true;
              }

              // Clear text selection yang otomatis terjadi saat double-click
              if (window.getSelection) {
                window.getSelection().removeAllRanges();
              }

              // Buka modal view detail dokumen
              openViewDocumentModal(docId);
              return true;
            }

            // Toggle detail row
            function toggleDetail(docId) {
              const detailRow = document.getElementById('detail-' + docId);
              const mainRow = event.currentTarget;

              // Close all other detail rows first
              const allDetailRows = document.querySelectorAll('.detail-row.show');
              const allMainRows = document.querySelectorAll('.main-row.selected');

              allDetailRows.forEach(row => {
                if (row.id !== 'detail-' + docId) {
                  row.classList.remove('show');
                }
              });

              allMainRows.forEach(row => {
                if (row !== mainRow) {
                  row.classList.remove('selected');
                }
              });

              // Toggle current detail row
              const isShowing = detailRow.classList.contains('show');

              if (isShowing) {
                detailRow.classList.remove('show');
                mainRow.classList.remove('selected');
              } else {
                loadDocumentDetail(docId);
                detailRow.classList.add('show');
                mainRow.classList.add('selected');

                setTimeout(() => {
                  detailRow.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                  });
                }, 100);
              }
            }

            // Load document detail via AJAX
            function loadDocumentDetail(docId) {
              const detailContent = document.getElementById('detail-content-' + docId);

              detailContent.innerHTML = `
                <div class="text-center p-4">
                  <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                </div>
              `;

              fetch(`/documents/akutansi/${docId}/detail`)
                .then(response => response.text())
                .then(html => {
                  detailContent.innerHTML = html;
                })
                .catch(error => {
                  console.error('Error:', error);
                  detailContent.innerHTML = `
                    <div class="text-center p-4 text-danger">
                      <i class="fa-solid fa-exclamation-triangle me-2"></i> Gagal memuat detail dokumen.
                    </div>
                  `;
                });
            }

            function editDocument(id) {
              // Implement edit functionality
              window.location.href = `/documents/akutansi/${id}/edit`;
            }

            function sendToPembayaran(id) {
              // Set document ID in modal
              document.getElementById('sendToPembayaranDocId').value = id;

              // Reset radio button selection
              document.getElementById('radioPembayaran').checked = true;

              // Reset and highlight pembayaran card
              const pembayaranCard = document.getElementById('destination-pembayaran');
              if (pembayaranCard) {
                pembayaranCard.style.borderColor = '#083E40';
                pembayaranCard.style.boxShadow = '0 2px 8px rgba(8, 62, 64, 0.2)';
                pembayaranCard.style.backgroundColor = 'rgba(8, 62, 64, 0.05)';
              }

              // Open modal
              const modal = new bootstrap.Modal(document.getElementById('sendToPembayaranModal'));
              modal.show();

              // Reset card styling when modal is closed
              const modalEl = document.getElementById('sendToPembayaranModal');
              modalEl.addEventListener('hidden.bs.modal', function() {
                if (pembayaranCard) {
                  pembayaranCard.style.backgroundColor = '';
                }
              }, { once: true });
            }

            function selectDestination(destination) {
              // Update radio button
              if (destination === 'pembayaran') {
                document.getElementById('radioPembayaran').checked = true;

                // Update card styling to show selected state
                const pembayaranCard = document.getElementById('destination-pembayaran');
                pembayaranCard.style.borderColor = '#083E40';
                pembayaranCard.style.boxShadow = '0 2px 8px rgba(8, 62, 64, 0.2)';
                pembayaranCard.style.backgroundColor = 'rgba(8, 62, 64, 0.05)';
              }
            }

            function confirmSendToPembayaran() {
              const docId = document.getElementById('sendToPembayaranDocId').value;
              const selectedDestination = document.querySelector('input[name="destination"]:checked');

              if (!selectedDestination) {
                showWarningModal('Peringatan', 'Pilih tujuan pengiriman terlebih dahulu!');
                return;
              }

              if (selectedDestination.value !== 'pembayaran') {
                showWarningModal('Peringatan', 'Hanya dapat mengirim ke Team Pembayaran dari halaman Akutansi.');
                return;
              }

              // Disable button and show loading
              const confirmBtn = document.querySelector('[onclick="confirmSendToPembayaran()"]');
              const originalHTML = confirmBtn.innerHTML;
              confirmBtn.disabled = true;
              confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

              // Close modal
              const modal = bootstrap.Modal.getInstance(document.getElementById('sendToPembayaranModal'));
              modal.hide();

              // Send request
              fetch(`/documents/akutansi/${docId}/send-to-pembayaran`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              })
              .then(response => {
                // Check if response is ok
                if (!response.ok) {
                  return response.json().then(data => {
                    throw new Error(data.message || 'Terjadi kesalahan saat mengirim dokumen.');
                  });
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  // Show success modal
                  const successModal = new bootstrap.Modal(document.getElementById('sendToPembayaranSuccessModal'));
                  successModal.show();
                } else {
                  showErrorModal('Gagal Mengirim Dokumen', data.message || 'Gagal mengirim dokumen ke Pembayaran.');
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = originalHTML;
                }
              })
              .catch(error => {
                console.error('Error:', error);
                showErrorModal('Terjadi Kesalahan', error.message || 'Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalHTML;
              });
            }

            // Search functionality
            // Client-side search removed - using server-side search instead
            // Search will be performed when form is submitted
            /*
            document.getElementById('akutansiSearchInput').addEventListener('input', function(e) {
              const searchTerm = e.target.value.toLowerCase();
              const allRows = document.querySelectorAll('.data-table tbody tr');

              allRows.forEach(row => {
                // Skip detail rows in search
                if (row.classList.contains('detail-row')) {
                  return;
                }

                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                  row.style.display = '';
                } else {
                  row.style.display = 'none';
                  // Also hide corresponding detail row
                  const rowId = row.getAttribute('onclick')?.match(/toggleDetail\((\d+)\)/)?.[1];
                  if (rowId) {
                    const detailRow = document.getElementById('detail-' + rowId);
                    if (detailRow) {
                      detailRow.style.display = 'none';
                    }
                  }
                }
              });
            });
            */

            document.getElementById('deadlineNote').addEventListener('input', function(e) {
              document.getElementById('deadlineCharCount').textContent = e.target.value.length;
            });

            function openSetDeadlineModal(docId) {
              document.getElementById('deadlineDocId').value = docId;
              document.getElementById('deadlineDays').value = '';
              document.getElementById('deadlineNote').value = '';
              document.getElementById('deadlineCharCount').textContent = '0';
              const modal = new bootstrap.Modal(document.getElementById('setDeadlineModal'));
              modal.show();
            }

            function openReturnToPerpajakanModal(docId) {
              showConfirmationModal(
                'Konfirmasi Pengembalian',
                'Apakah Anda yakin ingin mengembalikan dokumen ini ke Team Perpajakan?',
                function() {
                  // TODO: Implement return to perpajakan functionality
                  showInfoModal('Informasi', 'Fungsi return ke Perpajakan akan segera diimplementasikan.');
                }
              );
            }

            function confirmSetDeadline() {
              const docId = document.getElementById('deadlineDocId').value;
              const deadlineDays = document.getElementById('deadlineDays').value;
              const deadlineNote = document.getElementById('deadlineNote').value;

              if (!deadlineDays) {
                showWarningModal('Pilih periode deadline terlebih dahulu!');
                return;
              }

              if (deadlineDays < 1 || deadlineDays > 3) {
                showWarningModal('Periode deadline harus antara 1-3 hari!');
                return;
              }

              // Check CSRF token availability
              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
              if (!csrfToken) {
                console.error('CSRF token not found!');
                showErrorModal('CSRF token tidak ditemukan. Silakan refresh halaman.');
                return;
              }

              const submitBtn = document.querySelector('#setDeadlineModal .btn-warning');
              const originalHTML = submitBtn.innerHTML;
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

              // Type casting untuk memastikan integer
              const deadlineDaysInt = parseInt(deadlineDays, 10);

              console.log('Sending request to: ', `/documents/akutansi/${docId}/set-deadline`);
              console.log('Request payload: ', {
                deadline_days: deadlineDaysInt,
                deadline_note: deadlineNote
              });

              fetch(`/documents/akutansi/${docId}/set-deadline`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  deadline_days: deadlineDaysInt,
                  deadline_note: deadlineNote
                })
              })
              .then(async response => {
                console.log('Response status:', response.status);

                // Try to parse response as JSON first
                let responseData;
                try {
                  responseData = await response.json();
                } catch (e) {
                  // If response is not JSON, create error object
                  responseData = {
                    success: false,
                    message: `Server error: ${response.status} ${response.statusText}`
                  };
                }

                if (!response.ok) {
                  // Extract error message from response
                  const errorMessage = responseData.message || responseData.error || `HTTP error! status: ${response.status}`;

                  // Log debug info if available
                  if (responseData.debug_info) {
                    console.error('Debug info:', responseData.debug_info);
                  }

                  throw new Error(errorMessage);
                }

                return responseData;
              })
              .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                  const deadlineModal = bootstrap.Modal.getInstance(document.getElementById('setDeadlineModal'));
                  deadlineModal.hide();

                  // Show success modal
                  const successModalEl = document.getElementById('deadlineSuccessModal');
                  const successModal = new bootstrap.Modal(successModalEl);
                  const successMessageEl = document.getElementById('deadlineSuccessMessage');

                  if (data.deadline) {
                    successMessageEl.textContent = 
                      `Deadline: ${data.deadline}. Dokumen sekarang terbuka untuk diproses.`;
                  } else {
                    successMessageEl.textContent = data.message || 'Deadline berhasil ditetapkan.';
                  }

                  // Reload page when modal is closed
                  successModalEl.addEventListener('hidden.bs.modal', function() {
                    location.reload();
                  }, { once: true });

                  successModal.show();
                } else {
                  showErrorModal('Gagal Menetapkan Deadline', data.message || 'Terjadi kesalahan yang tidak diketahui');
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = originalHTML;
                }
              })
              .catch(error => {
                console.error('Error:', error);
                console.error('Error details:', error.message);
                showErrorModal('Terjadi Kesalahan', 'Terjadi kesalahan saat menetapkan deadline: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
              });
            }
            // Helper Functions for Modal Alerts
            function showErrorModal(title, message) {
              const modal = document.getElementById('errorModal');
              const titleEl = document.getElementById('errorModalTitle');
              const messageEl = document.getElementById('errorModalMessage');

              titleEl.textContent = title || 'Terjadi Kesalahan';
              messageEl.textContent = message || 'Terjadi kesalahan yang tidak diketahui.';

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
            }

            function showWarningModal(title, message) {
              const modal = document.getElementById('warningModal');
              const titleEl = document.getElementById('warningModalTitle');
              const messageEl = document.getElementById('warningModalMessage');

              titleEl.textContent = title || 'Peringatan';
              messageEl.textContent = message || 'Peringatan.';

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
            }

            function showInfoModal(title, message) {
              const modal = document.getElementById('infoModal');
              const titleEl = document.getElementById('infoModalTitle');
              const messageEl = document.getElementById('infoModalMessage');

              titleEl.textContent = title || 'Informasi';
              messageEl.textContent = message || 'Informasi.';

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
            }

            function showConfirmationModal(title, message, onConfirm, onCancel) {
              const modal = document.getElementById('confirmationModal');
              const titleEl = document.getElementById('confirmationModalTitle');
              const messageEl = document.getElementById('confirmationModalMessage');
              const confirmBtn = document.getElementById('confirmationModalConfirm');
              const cancelBtn = document.getElementById('confirmationModalCancel');

              titleEl.textContent = title || 'Konfirmasi';
              messageEl.textContent = message || 'Apakah Anda yakin?';

              // Remove existing event listeners by cloning
              const newConfirmBtn = confirmBtn.cloneNode(true);
              confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

              const newCancelBtn = cancelBtn.cloneNode(true);
              cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

              // Add new event listeners
              newConfirmBtn.addEventListener('click', function() {
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                bootstrapModal.hide();
                if (onConfirm && typeof onConfirm === 'function') {
                  onConfirm();
                }
              });

              newCancelBtn.addEventListener('click', function() {
                if (onCancel && typeof onCancel === 'function') {
                  onCancel();
                }
              });

              const bootstrapModal = new bootstrap.Modal(modal);
              bootstrapModal.show();
            }

            // Enhanced deadline system with color coding and late information for akutansi
            function initializeDeadlines() {
              const deadlineElements = document.querySelectorAll('.deadline-card');

              deadlineElements.forEach(card => {
                updateDeadlineCard(card);
              });

              // Update every 30 seconds for better responsiveness
              setInterval(() => {
                deadlineElements.forEach(card => {
                  updateDeadlineCard(card);
                });
              }, 30000); // Update every 30 seconds
            }

            function updateDeadlineCard(card) {
              const deadlineStr = card.dataset.deadline;
              if (!deadlineStr) return;

              const deadline = new Date(deadlineStr);
              const now = new Date();
              const diffMs = deadline - now;

              // Get sent and completed status from data attributes
              const isSent = card.dataset.sent === 'true';
              const isCompleted = card.dataset.completed === 'true';

              // Remove existing status classes
              card.classList.remove('deadline-safe', 'deadline-warning', 'deadline-danger', 'deadline-overdue', 'deadline-sent', 'deadline-completed');

              // Find status indicator
              const statusIndicator = card.querySelector('.deadline-indicator');
              const statusText = card.querySelector('.status-text');
              if (!statusText) {
                console.error('Status text not found in card:', card);
                return;
              }
              const statusIcon = statusIndicator.querySelector('i');
              if (!statusIcon) {
                console.error('Status icon not found in card:', card);
                return;
              }

              // Remove existing late info and time hints
              const existingLateInfo = card.querySelector('.late-info');
              const existingTimeHint = card.querySelector('div[style*="margin-top: 2px"]');
              const existingProgress = card.querySelector('.deadline-progress');

              if (existingLateInfo) existingLateInfo.remove();
              if (existingTimeHint) existingTimeHint.remove();
              if (existingProgress) existingProgress.remove();

              // Handle completed documents - show as completed (green, no countdown)
              if (isCompleted) {
                card.classList.add('deadline-completed');
                statusText.textContent = 'SELESAI';
                statusIcon.className = 'fa-solid fa-check-circle';
                statusIndicator.className = 'deadline-indicator deadline-completed';
                return; // Don't show countdown for completed documents
              }

              // Handle sent documents - show as sent (gray, no countdown, no overdue)
              if (isSent) {
                card.classList.add('deadline-sent');
                statusText.textContent = 'TERKIRIM';
                statusIcon.className = 'fa-solid fa-paper-plane';
                statusIndicator.className = 'deadline-indicator deadline-sent';
                return; // Don't show countdown or overdue for sent documents
              }

              // Handle active documents (still being processed) - show countdown
              if (diffMs < 0) {
                // Overdue state - only for active documents
                card.classList.add('deadline-overdue');

                // Calculate how late
                const diffHours = Math.abs(Math.floor(diffMs / (1000 * 60 * 60)));
                const diffDays = Math.abs(Math.floor(diffMs / (1000 * 60 * 60 * 24)));

                // Update status text
                statusText.textContent = 'TERLAMBAT';
                statusIcon.className = 'fa-solid fa-exclamation-triangle';
                statusIndicator.className = 'deadline-indicator deadline-overdue';

                // Show late info for active documents
                let lateText;
                if (diffDays >= 1) {
                  lateText = `${diffDays} HARI TELAT`;
                } else if (diffHours >= 1) {
                  lateText = `${diffHours} JAM TELAT`;
                } else {
                  lateText = 'BARU SAJA TELAT';
                }

                const lateInfo = document.createElement('div');
                lateInfo.className = 'late-info';
                lateInfo.innerHTML = `
                  <i class="fa-solid fa-exclamation-triangle"></i>
                  <span class="late-text">${lateText}</span>
                `;

                card.appendChild(lateInfo);

                // Add progress bar at bottom
                const progressBar = document.createElement('div');
                progressBar.className = 'deadline-progress';
                card.appendChild(progressBar);

              } else {
                // Time remaining - only for active documents
                const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                const diffMinutes = Math.floor(diffMs / (1000 * 60));
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                // Get original deadline_days from data attribute
                const deadlineDays = parseInt(card.dataset.deadlineDays) || null;

                // Calculate remaining hours for more accurate display
                const totalHoursRemaining = Math.floor(diffMs / (1000 * 60 * 60));

                // Determine display text based on deadline_days and remaining time
                let displayText = '';
                let shouldShowDays = false;

                if (deadlineDays && deadlineDays > 0) {
                  // If original deadline was set for X days, show "X hari lagi" 
                  // as long as we're still within that period
                  if (totalHoursRemaining >= 12) {
                    if (deadlineDays === 1) {
                      // For 1 day deadline, show "1 hari lagi" if >= 12 hours remaining
                      displayText = '1 hari lagi';
                      shouldShowDays = true;
                    } else {
                      // For 2+ days deadline, show actual days remaining
                      const fullDaysRemaining = Math.floor(totalHoursRemaining / 24);
                      const daysToShow = Math.min(Math.max(1, fullDaysRemaining + (totalHoursRemaining % 24 >= 12 ? 1 : 0)), deadlineDays);
                      displayText = `${daysToShow} ${daysToShow === 1 ? 'hari' : 'hari'} lagi`;
                      shouldShowDays = daysToShow >= 1;
                    }
                  } else {
                    // Less than 12 hours remaining, show hours
                    displayText = `${diffHours} ${diffHours === 1 ? 'jam' : 'jam'} lagi`;
                    shouldShowDays = false;
                  }
                } else {
                  // No deadline_days info, use standard calculation
                  if (diffDays >= 1) {
                    displayText = `${diffDays} ${diffDays === 1 ? 'hari' : 'hari'} lagi`;
                    shouldShowDays = true;
                  } else if (diffHours >= 1) {
                    displayText = `${diffHours} ${diffHours === 1 ? 'jam' : 'jam'} lagi`;
                    shouldShowDays = false;
                  } else {
                    displayText = `${diffMinutes} menit lagi`;
                    shouldShowDays = false;
                  }
                }

                // Simplified 3-status logic: >= 1 hari = hijau, < 1 hari = kuning
                if (shouldShowDays || diffDays >= 1) {
                  // Safe (>= 1 day or still within original deadline period) - Green
                  card.classList.add('deadline-safe');
                  statusText.textContent = 'AMAN';
                  statusIcon.className = 'fa-solid fa-check-circle';
                  statusIndicator.className = 'deadline-indicator deadline-safe';

                  // Add time remaining hint
                  const timeHint = document.createElement('div');
                  timeHint.style.cssText = 'font-size: 8px; color: #065f46; margin-top: 2px; font-weight: 600;';
                  timeHint.textContent = displayText;
                  card.appendChild(timeHint);

                } else if (diffHours >= 1 || diffMinutes >= 1) {
                  // Warning (< 1 day or less than 12 hours remaining) - Yellow
                  card.classList.add('deadline-warning');
                  statusText.textContent = 'DEKAT';
                  statusIcon.className = 'fa-solid fa-exclamation-triangle';
                  statusIndicator.className = 'deadline-indicator deadline-warning';

                  // Add time remaining hint
                  const timeHint = document.createElement('div');
                  timeHint.style.cssText = 'font-size: 8px; color: #92400e; margin-top: 2px; font-weight: 700;';
                  timeHint.textContent = displayText;
                  if (diffMinutes < 60) {
                    timeHint.style.animation = 'warning-shake 1s infinite';
                  }
                  card.appendChild(timeHint);
                }

                // Add progress bar
                const progressBar = document.createElement('div');
                progressBar.className = 'deadline-progress';
                card.appendChild(progressBar);
              }
            }

            // Initialize deadlines system
            document.addEventListener('DOMContentLoaded', function() {
              initializeDeadlines();

              // Character counter for return reason
              const returnReasonTextarea = document.getElementById('returnReason');
              const returnCharCount = document.getElementById('returnCharCount');
              if (returnReasonTextarea && returnCharCount) {
                returnReasonTextarea.addEventListener('input', function() {
                  const length = this.value.length;
                  returnCharCount.textContent = length;
                  if (length > 500) {
                    returnCharCount.style.color = '#dc3545';
                  } else {
                    returnCharCount.style.color = '#6c757d';
                  }
                });
              }
            });

            // Open Return Modal
            function openReturnModal(docId) {
              document.getElementById('returnDocId').value = docId;
              document.getElementById('returnReason').value = '';
              document.getElementById('returnCharCount').textContent = '0';
              const modal = new bootstrap.Modal(document.getElementById('returnModal'));
              modal.show();
            }

            // Confirm Return
            function confirmReturn() {
              const docId = document.getElementById('returnDocId').value;
              const returnReason = document.getElementById('returnReason').value.trim();

              if (!returnReason) {
                const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                document.getElementById('returnValidationWarningTitle').textContent = 'Catatan Wajib Diisi';
                document.getElementById('returnValidationWarningMessage').textContent = 'Mohon isi catatan penyerahan kembali sebelum melanjutkan.';
                warningModal.show();
                return;
              }

              if (returnReason.length < 10) {
                const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                document.getElementById('returnValidationWarningTitle').textContent = 'Catatan Terlalu Pendek';
                document.getElementById('returnValidationWarningMessage').textContent = 'Catatan penyerahan kembali minimal 10 karakter.';
                warningModal.show();
                return;
              }

              // Show confirmation modal
              document.getElementById('returnConfirmationReason').textContent = returnReason;
              const returnModal = bootstrap.Modal.getInstance(document.getElementById('returnModal'));
              returnModal.hide();

              const confirmationModal = new bootstrap.Modal(document.getElementById('returnConfirmationModal'));
              confirmationModal.show();

              // Handle confirm button click
              const confirmBtn = document.getElementById('confirmReturnBtn');
              confirmBtn.onclick = function() {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';

                // Send return request
                fetch(`/documents/akutansi/${docId}/return`, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                    return_reason: returnReason
                  })
                })
                .then(response => response.json())
                .then(data => {
                  const confirmationModalInstance = bootstrap.Modal.getInstance(document.getElementById('returnConfirmationModal'));
                  confirmationModalInstance.hide();

                  if (data.success) {
                    const successModal = new bootstrap.Modal(document.getElementById('returnSuccessModal'));
                    successModal.show();

                    successModal._element.addEventListener('hidden.bs.modal', function() {
                      location.reload();
                    });
                  } else {
                    const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                    document.getElementById('returnValidationWarningTitle').textContent = 'Gagal Mengirim Kembali Dokumen';
                    document.getElementById('returnValidationWarningMessage').textContent = data.message || 'Terjadi kesalahan saat mengirim kembali dokumen.';
                    warningModal.show();
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim Kembali';
                  }
                })
                .catch(error => {
                  console.error('Error:', error);
                  const confirmationModalInstance = bootstrap.Modal.getInstance(document.getElementById('returnConfirmationModal'));
                  confirmationModalInstance.hide();

                  const warningModal = new bootstrap.Modal(document.getElementById('returnValidationWarningModal'));
                  document.getElementById('returnValidationWarningTitle').textContent = 'Terjadi Kesalahan';
                  document.getElementById('returnValidationWarningMessage').textContent = 'Terjadi kesalahan saat mengirim kembali dokumen. Silakan coba lagi.';
                  warningModal.show();
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Ya, Kirim Kembali';
                });
              };
            }

            </script>

            <script>
            // Handle suggestion button clicks
            document.addEventListener('DOMContentLoaded', function() {
                const suggestionButtons = document.querySelectorAll('.suggestion-btn');

                suggestionButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const suggestion = this.getAttribute('data-suggestion');
                        const searchInput = document.querySelector('input[name="search"]');
                        const form = searchInput.closest('form');

                        // Set the suggestion value to search input
                        searchInput.value = suggestion;

                        // Submit the form
                        form.submit();
                    });
                });
            });
            </script>

            <!-- Modal: Column Customization -->
            <div class="customization-modal" id="columnCustomizationModal">
              <div class="modal-content-custom">
                <div class="modal-header-custom">
                  <h3>
                    <i class="fa-solid fa-table-columns"></i>
                    Kustomisasi Kolom Tabel
                  </h3>
                </div>

                <div class="modal-body-custom">
                  <div class="customization-grid">
                    <!-- Selection Panel -->
                      <div class="selection-panel">
                        <div class="panel-header">
                          <div class="panel-title">
                            <i class="fa-solid fa-check-square"></i>
                            Pilih Kolom
                          </div>
                          <div class="panel-actions">
                            <button type="button" class="btn-select-action btn-select-all" onclick="selectAllColumns()">
                              <i class="fa-solid fa-check-double"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn-select-action btn-remove-all" onclick="removeAllColumns()">
                              <i class="fa-solid fa-times"></i> Hapus Semua
                            </button>
                          </div>
                        </div>
                        <div class="panel-description">
                          Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
                        </div>
                      <div class="column-selection-list" id="columnSelectionList">
                        @foreach($availableColumns as $key => $label)
                          <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}"
                               data-column="{{ $key }}"
                               draggable="{{ in_array($key, $selectedColumns) ? 'true' : 'false' }}"
                               onclick="toggleColumn(this)">
                            <div class="drag-handle">
                              <i class="fa-solid fa-grip-vertical"></i>
                            </div>
                            <input type="checkbox"
                                   class="column-item-checkbox"
                                   value="{{ $key }}"
                                   {{ in_array($key, $selectedColumns) ? 'checked' : '' }}
                                   onclick="event.stopPropagation()">
                            <label class="column-item-label">{{ $label }}</label>
                            <span class="column-item-order">
                              {{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}
                            </span>
                          </div>
                        @endforeach
                      </div>
                    </div>

                    <!-- Preview Panel -->
                    <div class="preview-panel">
                      <div class="panel-title">
                        <i class="fa-solid fa-eye"></i>
                        Preview Hasil
                      </div>
                      <div class="panel-description">
                        Preview tabel akan menampilkan kolom yang Anda pilih sesuai urutan.
                      </div>
                      <div class="preview-container">
                        <div id="tablePreview">
                          @if(count($selectedColumns) > 0)
                            <table class="preview-table">
                              <thead>
                                <tr>
                                  <th>No</th>
                                  @foreach($selectedColumns as $col)
                                    <th>{{ $availableColumns[$col] ?? $col }}</th>
                                  @endforeach
                                </tr>
                              </thead>
                              <tbody>
                                @for($i = 1; $i <= 5; $i++)
                                  <tr>
                                    <td>{{ $i }}</td>
                                    @foreach($selectedColumns as $col)
                                      <td>
                                        @if($col == 'nomor_agenda')
                                          AGD/{{ 100 + $i }}/XII/2024
                                        @elseif($col == 'nomor_spp')
                                          {{ 200 + $i }}/M/SPP/8/04/2024
                                        @elseif($col == 'tanggal_masuk')
                                          {{ date('d-m-Y', strtotime("+$i days")) }} 08:{{ str_pad($i * 10, 2, '0', STR_PAD_LEFT) }}
                                        @elseif($col == 'nilai_rupiah')
                                          Rp. {{ number_format(1000000 * $i, 0, ',', '.') }}
                                        @elseif($col == 'nomor_miro')
                                          MIR-{{ 1000 + $i }}
                                        @elseif($col == 'npwp')
                                          12.345.678.9-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}.456
                                        @elseif($col == 'no_faktur')
                                          123.456-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}.789
                                        @elseif($col == 'tanggal_faktur')
                                          {{ date('d/m/Y', strtotime("+$i days")) }}
                                        @elseif($col == 'tanggal_selesai_verifikasi_pajak')
                                          {{ date('d/m/Y', strtotime("+$i days")) }}
                                        @elseif($col == 'jenis_pph')
                                          PPh {{ 21 + $i }}
                                        @elseif($col == 'dpp_pph')
                                          {{ number_format(2000 * $i, 0, ',', '.') }}
                                        @elseif($col == 'ppn_terhutang')
                                          {{ number_format(3000 * $i, 0, ',', '.') }}
                                        @elseif($col == 'link_dokumen_pajak')
                                          <a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>
                                        @else
                                          Contoh Data {{ $i }}
                                        @endif
                                      </td>
                                    @endforeach
                                  </tr>
                                @endfor
                              </tbody>
                            </table>
                          @else
                            <div class="empty-preview">
                              <i class="fa-solid fa-table"></i>
                              <p>Belum ada kolom yang dipilih</p>
                              <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="modal-footer-custom">
                  <div class="selected-count">
                    <strong id="selectedColumnCount">{{ count($selectedColumns) }}</strong> kolom dipilih
                    @if(count($selectedColumns) > 0)
                                                                                                                      <br><small>Kolom: {{ implode(', ', array_map(function ($col) use ($availableColumns) {
                        return $availableColumns[$col] ?? $col;
                      }, $selectedColumns)) }}</small>
                    @endif
                  </div>
                  <div class="modal-actions">
                    <button type="button" class="btn-modal btn-cancel" onclick="closeColumnCustomizationModal()">
                      <i class="fa-solid fa-times"></i>
                      Batal
                    </button>
                    <button type="button" class="btn-modal btn-save" id="saveCustomizationBtn" onclick="saveColumnCustomization()">
                      <i class="fa-solid fa-save"></i>
                      Simpan Perubahan
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <script>
            // Global variables for column customization
            let selectedColumnsOrder = [];
            let availableColumnsData = {};

            // Initialize available columns data from PHP
            @php
              $columnsJson = json_encode($availableColumns);
              echo "availableColumnsData = {$columnsJson};";
            @endphp

            // Initialize selected columns from existing selection
            @if(count($selectedColumns) > 0)
              selectedColumnsOrder = @json($selectedColumns);
            @endif

            // Global Functions
            function openColumnCustomizationModal() {
              const modal = document.getElementById('columnCustomizationModal');
              modal.classList.add('show');
              document.body.style.overflow = 'hidden';
              initializeModalState();
            }

            function closeColumnCustomizationModal() {
              const modal = document.getElementById('columnCustomizationModal');
              modal.classList.remove('show');
              document.body.style.overflow = '';
            }

            function toggleColumn(columnElement) {
              const columnKey = columnElement.dataset.column;
              const checkbox = columnElement.querySelector('.column-item-checkbox');
              const isChecked = checkbox.checked;

              if (!isChecked) {
                if (!selectedColumnsOrder.includes(columnKey)) {
                  selectedColumnsOrder.push(columnKey);
                }
                checkbox.checked = true;
                columnElement.classList.add('selected');
                columnElement.setAttribute('draggable', 'true');
              } else {
                selectedColumnsOrder = selectedColumnsOrder.filter(key => key !== columnKey);
                checkbox.checked = false;
                columnElement.classList.remove('selected');
                columnElement.setAttribute('draggable', 'false');
              }

              updateColumnOrderBadges();
              updatePreviewTable();
              updateSelectedCount();
              updateDraggableState();
            }

            function selectAllColumns() {
              const allKeys = Object.keys(@json($availableColumns));
              selectedColumnsOrder = allKeys;
              document.querySelectorAll('.column-item').forEach(item => {
                item.classList.add('selected');
                item.setAttribute('draggable', 'true');
                item.querySelector('.column-item-checkbox').checked = true;
              });
              updateColumnOrderBadges();
              updatePreviewTable();
              updateSelectedCount();
              updateDraggableState();
            }

            function removeAllColumns() {
              selectedColumnsOrder = [];
              document.querySelectorAll('.column-item').forEach(item => {
                item.classList.remove('selected');
                item.setAttribute('draggable', 'false');
                item.querySelector('.column-item-checkbox').checked = false;
              });
              updateColumnOrderBadges();
              updatePreviewTable();
              updateSelectedCount();
              updateDraggableState();
            }

            function updateColumnOrderBadges() {
              document.querySelectorAll('.column-item').forEach(item => {
                const columnKey = item.dataset.column;
                const orderBadge = item.querySelector('.column-item-order');
                const index = selectedColumnsOrder.indexOf(columnKey);

                if (index !== -1) {
                  orderBadge.textContent = index + 1;
                } else {
                  orderBadge.textContent = '';
                }
              });
            }

            function updatePreviewTable() {
              const previewContainer = document.getElementById('tablePreview');

              if (selectedColumnsOrder.length === 0) {
                previewContainer.innerHTML = `
                  <div class="empty-preview">
                    <i class="fa-solid fa-table fa-2x mb-2"></i>
                    <p>Belum ada kolom yang dipilih</p>
                    <small>Silakan pilih minimal satu kolom untuk melihat preview</small>
                  </div>
                `;
                return;
              }

              let previewHTML = `
                <table class="preview-table">
                  <thead>
                    <tr>
                      <th>No</th>
              `;

              selectedColumnsOrder.forEach(columnKey => {
                const columnLabel = availableColumnsData[columnKey] || columnKey;
                previewHTML += `<th>${columnLabel}</th>`;
              });

              previewHTML += `
                    </tr>
                  </thead>
                  <tbody>
              `;

              const sampleData = {
                'nomor_agenda': ['AGD/822/XII/2024', 'AGD/258/XII/2024', 'AGD/992/XII/2024', 'AGD/92/XII/2024', 'AGD/546/XII/2024'],
                'nomor_spp': ['627/M/SPP/8/04/2024', '32/M/SPP/3/09/2024', '205/M/SPP/5/05/2024', '331/M/SPP/19/12/2024', '580/M/SPP/28/08/2024'],
                'tanggal_masuk': ['24/11/2024 08:49', '24/11/2024 08:37', '24/11/2024 08:18', '24/11/2024 08:13', '24/11/2024 08:09'],
                'nilai_rupiah': ['Rp. 241.650.650', 'Rp. 751.897.501', 'Rp. 232.782.087', 'Rp. 490.050.679', 'Rp. 397.340.004'],
                'nomor_miro': ['MIR-1001', 'MIR-1002', 'MIR-1003', 'MIR-1004', 'MIR-1005'],
                'kategori': ['Operasional', 'Investasi', 'Operasional', 'Investasi', 'Operasional'],
                'kebun': ['Kebun A', 'Kebun B', 'Kebun C', 'Kebun A', 'Kebun B'],
                'npwp': ['12.345.678.9-001.456', '12.345.678.9-002.456', '12.345.678.9-003.456', '12.345.678.9-004.456', '12.345.678.9-005.456'],
                'no_faktur': ['123.456-001.789', '123.456-002.789', '123.456-003.789', '123.456-004.789', '123.456-005.789'],
                'tanggal_faktur': ['01/11/2024', '05/11/2024', '10/11/2024', '08/11/2024', '12/11/2024'],
                'tanggal_selesai_verifikasi_pajak': ['15/11/2024', '18/11/2024', '20/11/2024', '22/11/2024', '25/11/2024'],
                'jenis_pph': ['PPh 22', 'PPh 23', 'PPh 22', 'PPh 23', 'PPh 22'],
                'dpp_pph': ['2.000', '4.000', '6.000', '8.000', '10.000'],
                'ppn_terhutang': ['3.000', '6.000', '9.000', '12.000', '15.000'],
                'link_dokumen_pajak': ['<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>', '<a href="#" style="color: #0d6efd; text-decoration: none;"><i class="fa-solid fa-link me-1"></i>Lihat Dokumen</a>'],
              };

              for (let i = 0; i < 5; i++) {
                previewHTML += `<tr>`;
                previewHTML += `<td>${i + 1}</td>`;

                selectedColumnsOrder.forEach(columnKey => {
                  // Skip 'status' column as it's always shown as a special column
                  if (columnKey === 'status') {
                    return;
                  }

                  const columnLabel = availableColumnsData[columnKey] || columnKey;
                  let cellValue = sampleData[columnKey] ? sampleData[columnKey][i] : `Contoh ${columnLabel} ${i + 1}`;

                  previewHTML += `<td>${cellValue}</td>`;
                });

                previewHTML += `</tr>`;
              }

              previewHTML += `
                  </tbody>
                </table>
              `;

              previewContainer.innerHTML = previewHTML;
            }

            function updateSelectedCount() {
              const countElement = document.getElementById('selectedColumnCount');
              countElement.textContent = selectedColumnsOrder.length;

              const saveButton = document.getElementById('saveCustomizationBtn');
              saveButton.disabled = selectedColumnsOrder.length === 0;
            }

            function saveColumnCustomization() {
              if (selectedColumnsOrder.length === 0) {
                showWarningModal('Peringatan', 'Silakan pilih minimal satu kolom untuk ditampilkan.');
                return;
              }

              // Try multiple selectors to find the form
              let filterForm = document.getElementById('filterForm');
              if (!filterForm) {
                filterForm = document.querySelector('form[action*="akutansi"]');
              }
              if (!filterForm) {
                // Fallback: use first form on page
                filterForm = document.querySelector('form');
              }

              if (!filterForm) {
                showErrorModal('Error', 'Form tidak ditemukan.');
                return;
              }

              document.querySelectorAll('input[name="columns[]"]').forEach(input => {
                if (input.type === 'hidden') {
                  input.remove();
                }
              });

              selectedColumnsOrder.forEach(columnKey => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'columns[]';
                hiddenInput.value = columnKey;
                filterForm.appendChild(hiddenInput);
              });

              // Add enable customization flag
              const enableInput = document.createElement('input');
              enableInput.type = 'hidden';
              enableInput.name = 'enable_customization';
              enableInput.value = '1';
              filterForm.appendChild(enableInput);

              closeColumnCustomizationModal();
              filterForm.submit();
            }

            function initializeModalState() {
              document.querySelectorAll('.column-item').forEach(item => {
                const columnKey = item.dataset.column;
                const checkbox = item.querySelector('.column-item-checkbox');

                if (selectedColumnsOrder.includes(columnKey)) {
                  checkbox.checked = true;
                  item.classList.add('selected');
                  item.setAttribute('draggable', 'true');
                } else {
                  checkbox.checked = false;
                  item.classList.remove('selected');
                  item.setAttribute('draggable', 'false');
                }
              });

              initializeDragAndDrop();
              updateColumnOrderBadges();
              updatePreviewTable();
              updateSelectedCount();
            }

            function updateDraggableState() {
              document.querySelectorAll('.column-item').forEach(item => {
                const columnKey = item.dataset.column;
                if (selectedColumnsOrder.includes(columnKey)) {
                  item.setAttribute('draggable', 'true');
                } else {
                  item.setAttribute('draggable', 'false');
                }
              });
            }

            let draggedElement = null;

            function initializeDragAndDrop() {
              const columnList = document.getElementById('columnSelectionList');
              if (!columnList) return;

              const newList = columnList.cloneNode(true);
              columnList.parentNode.replaceChild(newList, columnList);

              newList.querySelectorAll('.column-item.selected').forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragend', handleDragEnd);
                item.addEventListener('dragover', handleDragOver);
                item.addEventListener('drop', handleDrop);
              });
            }

            function handleDragStart(e) {
              draggedElement = this;
              this.classList.add('dragging');
              e.dataTransfer.effectAllowed = 'move';
            }

            function handleDragEnd(e) {
              this.classList.remove('dragging');
              document.querySelectorAll('.column-item').forEach(el => {
                el.classList.remove('drag-over');
              });
              draggedElement = null;
            }

            function handleDragOver(e) {
              e.preventDefault();
              e.dataTransfer.dropEffect = 'move';

              if (this !== draggedElement && this.classList.contains('selected')) {
                const afterElement = getDragAfterElement(this.parentNode, e.clientY);

                if (afterElement == null) {
                  this.parentNode.appendChild(draggedElement);
                } else {
                  this.parentNode.insertBefore(draggedElement, afterElement);
                }
              }

              return false;
            }

            function handleDrop(e) {
              e.preventDefault();
              e.stopPropagation();

              this.classList.remove('drag-over');

              if (this !== draggedElement && this.classList.contains('selected')) {
                const columnList = document.getElementById('columnSelectionList');
                const selectedItems = Array.from(columnList.querySelectorAll('.column-item.selected'));
                const newOrder = selectedItems.map(item => item.dataset.column);

                selectedColumnsOrder = newOrder;

                updateColumnOrderBadges();
                updatePreviewTable();

                setTimeout(() => {
                  initializeDragAndDrop();
                }, 50);
              }

              return false;
            }

            function getDragAfterElement(container, y) {
              const draggableElements = [...container.querySelectorAll('.column-item.selected:not(.dragging)')];

              return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;

                if (offset < 0 && offset > closest.offset) {
                  return { offset: offset, element: child };
                } else {
                  return closest;
                }
              }, { offset: Number.NEGATIVE_INFINITY }).element;
            }

            // Close modal when clicking outside
            document.addEventListener('click', function(e) {
              const modal = document.getElementById('columnCustomizationModal');
              if (modal && modal.classList.contains('show') && e.target === modal) {
                closeColumnCustomizationModal();
              }
            });

            // Open View Document Modal
            function openViewDocumentModal(docId) {
              // Set document ID
              document.getElementById('view-dokumen-id').value = docId;

              // Set edit button URL
              document.getElementById('view-edit-btn').href = `/documents/akutansi/${docId}/edit`;

              // CRITICAL: Reset all fields to loading state BEFORE fetch to prevent caching issues
              const fieldsToReset = [
                'view-nomor-agenda', 'view-nomor-spp', 'view-tanggal-spp', 'view-bulan', 'view-tahun',
                'view-tanggal-masuk', 'view-jenis-dokumen', 'view-jenis-sub-pekerjaan', 'view-kategori',
                'view-jenis-pembayaran', 'view-uraian-spp', 'view-nilai-rupiah', 'view-ejaan-nilai-rupiah',
                'view-dibayar-kepada', 'view-kebun', 'view-no-spk', 'view-tanggal-spk', 'view-tanggal-berakhir-spk',
                'view-nomor-miro', 'view-no-berita-acara', 'view-tanggal-berita-acara', 'view-nomor-po', 'view-nomor-pr',
                'view-tanggal-miro',
                // Perpajakan (Simplified - 6 Essential Fields)
                'view-no-faktur', 'view-tanggal-faktur', 'view-tanggal-selesai-verifikasi-pajak',
                'view-jenis-pph', 'view-dpp-pph', 'view-ppn-terhutang'
              ];

              fieldsToReset.forEach(fieldId => {
                const el = document.getElementById(fieldId);
                if (el) {
                  el.textContent = 'Memuat...';
                }
              });

              // Show modal immediately with loading state
              const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
              modal.show();

              // Load document data via AJAX
              fetch(`/documents/akutansi/${docId}/detail`, {
                headers: {
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
                .then(response => response.json())
                .then(data => {
                  if (data.success && data.dokumen) {
                    const dok = data.dokumen;

                    // Identitas Dokumen
                    document.getElementById('view-nomor-agenda').textContent = dok.nomor_agenda || '-';
                    document.getElementById('view-nomor-spp').textContent = dok.nomor_spp || '-';
                    document.getElementById('view-tanggal-spp').textContent = dok.tanggal_spp ? formatDate(dok.tanggal_spp) : '-';
                    // Helper function to convert month number to Indonesian month name
                    const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    const formatBulan = (bulan) => {
                      if (!bulan) return '-';
                      const num = parseInt(bulan);
                      return (num >= 1 && num <= 12) ? monthNames[num] : bulan;
                    };
                    document.getElementById('view-bulan').textContent = formatBulan(dok.bulan);
                    document.getElementById('view-tahun').textContent = dok.tahun || '-';
                    document.getElementById('view-tanggal-masuk').textContent = dok.tanggal_masuk ? formatDateTime(dok.tanggal_masuk) : '-';
                    document.getElementById('view-jenis-dokumen').textContent = dok.jenis_dokumen || '-';
                    document.getElementById('view-jenis-sub-pekerjaan').textContent = dok.jenis_sub_pekerjaan || '-';
                    document.getElementById('view-kategori').textContent = dok.kategori || '-';
                    document.getElementById('view-jenis-pembayaran').textContent = dok.jenis_pembayaran || '-';

                    // Detail Keuangan & Vendor
                    document.getElementById('view-uraian-spp').textContent = dok.uraian_spp || '-';
                    document.getElementById('view-nilai-rupiah').textContent = dok.nilai_rupiah ? 'Rp. ' + formatNumber(dok.nilai_rupiah) : '-';
                    // Ejaan nilai rupiah
                    if (dok.nilai_rupiah && dok.nilai_rupiah > 0) {
                      document.getElementById('view-ejaan-nilai-rupiah').textContent = terbilangRupiah(dok.nilai_rupiah);
                    } else {
                      document.getElementById('view-ejaan-nilai-rupiah').textContent = '-';
                    }
                    document.getElementById('view-dibayar-kepada').textContent = dok.dibayar_kepada || '-';
                    document.getElementById('view-kebun').textContent = dok.kebun || '-';

                    // Referensi Pendukung
                    document.getElementById('view-no-spk').textContent = dok.no_spk || '-';
                    document.getElementById('view-tanggal-spk').textContent = dok.tanggal_spk ? formatDate(dok.tanggal_spk) : '-';
                    document.getElementById('view-tanggal-berakhir-spk').textContent = dok.tanggal_berakhir_spk ? formatDate(dok.tanggal_berakhir_spk) : '-';
                    // Nomor MIRO (consolidated via accessor in controller)
                    document.getElementById('view-nomor-miro').textContent = dok.nomor_miro || '-';
                    document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
                    document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDate(dok.tanggal_berita_acara) : '-';

                    // Nomor PO & PR
                    const poNumbers = dok.dokumen_pos ? dok.dokumen_pos.map(po => po.nomor_po).join(', ') : '-';
                    const prNumbers = dok.dokumen_prs ? dok.dokumen_prs.map(pr => pr.nomor_pr).join(', ') : '-';
                    document.getElementById('view-nomor-po').textContent = poNumbers || '-';
                    document.getElementById('view-nomor-pr').textContent = prNumbers || '-';

                    // Informasi Perpajakan (Simplified - 6 Essential Fields)
                    if (dok.no_faktur || dok.tanggal_faktur || dok.jenis_pph || dok.dpp_pph || dok.ppn_terhutang || dok.tanggal_selesai_verifikasi_pajak) {
                      // Row 1: No Faktur & Tanggal Faktur
                      const noFakturEl = document.getElementById('view-no-faktur');
                      if (noFakturEl) noFakturEl.textContent = dok.no_faktur || '-';

                      const tanggalFakturEl = document.getElementById('view-tanggal-faktur');
                      if (tanggalFakturEl) tanggalFakturEl.textContent = dok.tanggal_faktur ? formatDate(dok.tanggal_faktur) : '-';

                      // Row 2: Tgl Selesai Verifikasi & Jenis PPh
                      const tglVerifEl = document.getElementById('view-tanggal-selesai-verifikasi-pajak');
                      if (tglVerifEl) tglVerifEl.textContent = dok.tanggal_selesai_verifikasi_pajak ? formatDate(dok.tanggal_selesai_verifikasi_pajak) : '-';

                      const jenisPphEl = document.getElementById('view-jenis-pph');
                      if (jenisPphEl) jenisPphEl.textContent = dok.jenis_pph || '-';

                      // Row 3: DPP PPh & PPh Terhutang
                      const dppPphEl = document.getElementById('view-dpp-pph');
                      if (dppPphEl) dppPphEl.textContent = dok.dpp_pph ? formatNumber(dok.dpp_pph) : '-';

                      const ppnTerhutangEl = document.getElementById('view-ppn-terhutang');
                      if (ppnTerhutangEl) ppnTerhutangEl.textContent = dok.ppn_terhutang ? formatNumber(dok.ppn_terhutang) : '-';

                      // Show perpajakan section
                      document.getElementById('perpajakan-section').style.display = 'block';
                    } else {
                      // Hide perpajakan section if no data
                      document.getElementById('perpajakan-section').style.display = 'none';
                    }

                    // Informasi Akutansi
                    // Note: Nomor MIRO already set above, removed duplicate
                    document.getElementById('view-tanggal-miro').textContent = dok.tanggal_miro ? new Date(dok.tanggal_miro).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'}) : '-';
                  }
                })
                .catch(error => {
                  console.error('Error loading document:', error);
                  // Show error state in fields
                  fieldsToReset.forEach(fieldId => {
                    const el = document.getElementById(fieldId);
                    if (el) {
                      el.textContent = 'Gagal memuat data';
                    }
                  });
                });

              // Note: modal.show() is now called earlier with loading state

              // Ensure edit button works correctly - prevent any interference
              const editBtn = document.getElementById('view-edit-btn');
              if (editBtn) {
                // Remove any existing event listeners by cloning and replacing
                const newEditBtn = editBtn.cloneNode(true);
                editBtn.parentNode.replaceChild(newEditBtn, editBtn);

                // Add click handler to ensure navigation works
                newEditBtn.addEventListener('click', function(e) {
                  const href = this.getAttribute('href');
                  if (href && href !== '#' && !href.startsWith('#')) {
                    // Valid URL, allow navigation
                    window.location.href = href;
                  }
                });
              }
            }

            // Helper functions for formatting
            function formatDate(dateStr) {
              if (!dateStr) return '-';
              const date = new Date(dateStr);
              return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }

            function formatDateTime(dateStr) {
              if (!dateStr) return '-';
              const date = new Date(dateStr);
              return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            }

            function formatNumber(num) {
              if (!num) return '-';
              return new Intl.NumberFormat('id-ID').format(num);
            }

            // Function to convert number to Indonesian terbilang
            function terbilangRupiah(number) {
              number = parseFloat(number) || 0;

              if (number == 0) {
                return 'nol rupiah';
              }

              const angka = [
                '', 'satu', 'dua', 'tiga', 'empat', 'lima',
                'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
                'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
                'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
              ];

              let hasil = '';

              // Handle triliun
              if (number >= 1000000000000) {
                const triliun = Math.floor(number / 1000000000000);
                hasil += terbilangSatuan(triliun, angka) + ' triliun ';
                number = number % 1000000000000;
              }

              // Handle milyar
              if (number >= 1000000000) {
                const milyar = Math.floor(number / 1000000000);
                hasil += terbilangSatuan(milyar, angka) + ' milyar ';
                number = number % 1000000000;
              }

              // Handle juta
              if (number >= 1000000) {
                const juta = Math.floor(number / 1000000);
                hasil += terbilangSatuan(juta, angka) + ' juta ';
                number = number % 1000000;
              }

              // Handle ribu
              if (number >= 1000) {
                const ribu = Math.floor(number / 1000);
                if (ribu == 1) {
                  hasil += 'seribu ';
                } else {
                  hasil += terbilangSatuan(ribu, angka) + ' ribu ';
                }
                number = number % 1000;
              }

              // Handle ratusan, puluhan, dan satuan
              if (number > 0) {
                hasil += terbilangSatuan(number, angka);
              }

              return hasil.trim() + ' rupiah';
            }

            function terbilangSatuan(number, angka) {
              let hasil = '';
              number = parseInt(number);

              if (number == 0) {
                return '';
              }

              // Handle ratusan
              if (number >= 100) {
                const ratus = Math.floor(number / 100);
                if (ratus == 1) {
                  hasil += 'seratus ';
                } else {
                  hasil += angka[ratus] + ' ratus ';
                }
                number = number % 100;
              }

              // Handle puluhan dan satuan (0-99)
              if (number > 0) {
                if (number < 20) {
                  hasil += angka[number] + ' ';
                } else {
                  const puluhan = Math.floor(number / 10);
                  const satuan = number % 10;

                  if (puluhan == 1) {
                    hasil += angka[10 + satuan] + ' ';
                  } else {
                    hasil += angka[puluhan] + ' puluh ';
                    if (satuan > 0) {
                      hasil += angka[satuan] + ' ';
                    }
                  }
                }
              }

              return hasil.trim();
            }

            function formatStatusPerpajakan(status) {
              if (!status) return '-';
              switch(status) {
                case 'sedang_diproses': return 'Sedang Diproses';
                case 'selesai': return 'Selesai';
                default: return status;
              }
            }
            </script>

            <!-- Modal View Document Detail -->
            <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl" style="max-width: 90%; width: 90%;">
                <div class="modal-content" style="height: 90vh; display: flex; flex-direction: column;">
                  <!-- Sticky Header -->
                  <div class="modal-header" style="position: sticky; top: 0; z-index: 1050; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-bottom: none; flex-shrink: 0;">
                    <h5 class="modal-title" id="viewDocumentModalLabel" style="color: white; font-weight: 700; font-size: 18px;">
                      <i class="fa-solid fa-file-lines me-2"></i>
                      Detail Dokumen Lengkap
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <!-- Scrollable Body -->
                  <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 140px); padding: 24px; flex: 1;">
                    <input type="hidden" id="view-dokumen-id">

                    <!-- Section 1: Identitas Dokumen -->
                    <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                      <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                          <i class="fa-solid fa-id-card"></i>
                          IDENTITAS DOKUMEN
                        </h6>
                      </div>
                      <div class="row g-3">
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Nomor Agenda</label>
                            <div class="detail-value" id="view-nomor-agenda">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Nomor SPP</label>
                            <div class="detail-value" id="view-nomor-spp">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal SPP</label>
                            <div class="detail-value" id="view-tanggal-spp">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Bulan</label>
                            <div class="detail-value" id="view-bulan">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Tahun</label>
                            <div class="detail-value" id="view-tahun">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal Masuk</label>
                            <div class="detail-value" id="view-tanggal-masuk">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Kriteria CF</label>
                            <div class="detail-value" id="view-kategori">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Sub Kriteria</label>
                            <div class="detail-value" id="view-jenis-dokumen">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Item Sub Kriteria</label>
                            <div class="detail-value" id="view-jenis-sub-pekerjaan">-</div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="detail-item">
                            <label class="detail-label">Jenis Pembayaran</label>
                            <div class="detail-value" id="view-jenis-pembayaran">-</div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Section 2: Detail Keuangan & Vendor -->
                    <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                      <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                          <i class="fa-solid fa-money-bill-wave"></i>
                          DETAIL KEUANGAN & VENDOR
                        </h6>
                      </div>
                      <div class="row g-3">
                        <div class="col-12">
                          <div class="detail-item">
                            <label class="detail-label">Uraian SPP</label>
                            <div class="detail-value" id="view-uraian-spp" style="white-space: pre-wrap;">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Nilai Rupiah</label>
                            <div class="detail-value" id="view-nilai-rupiah" style="font-weight: 700; color: #083E40;">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Ejaan Nilai Rupiah</label>
                            <div class="detail-value" id="view-ejaan-nilai-rupiah" style="font-style: italic; color: #666;">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Dibayar Kepada (Vendor)</label>
                            <div class="detail-value" id="view-dibayar-kepada">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Kebun / Unit Kerja</label>
                            <div class="detail-value" id="view-kebun">-</div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Section 3: Referensi Pendukung -->
                    <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                      <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                          <i class="fa-solid fa-file-contract"></i>
                          REFERENSI PENDUKUNG
                        </h6>
                      </div>
                      <div class="row g-3">
                        <div class="col-md-3">
                          <div class="detail-item">
                            <label class="detail-label">No. SPK</label>
                            <div class="detail-value" id="view-no-spk">-</div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal SPK</label>
                            <div class="detail-value" id="view-tanggal-spk">-</div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal Berakhir SPK</label>
                            <div class="detail-value" id="view-tanggal-berakhir-spk">-</div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">No. Berita Acara</label>
                            <div class="detail-value" id="view-no-berita-acara">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal Berita Acara</label>
                            <div class="detail-value" id="view-tanggal-berita-acara">-</div>
                          </div>
                        </div>

                        <!-- Miro Details -->
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Nomor MIRO</label>
                            <div class="detail-value" id="view-nomor-miro" style="font-weight: 700; color: #083E40;">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal MIRO</label>
                            <div class="detail-value" id="view-tanggal-miro" style="font-weight: 700; color: #083E40;">-</div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Section 4: Nomor PO & PR -->
                    <div class="form-section mb-4" style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                      <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                          <i class="fa-solid fa-hashtag"></i>
                          NOMOR PO & PR
                        </h6>
                      </div>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Nomor PO</label>
                            <div class="detail-value" id="view-nomor-po">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Nomor PR</label>
                            <div class="detail-value" id="view-nomor-pr">-</div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Section 5: Informasi Perpajakan (Simplified - 6 Essential Fields) -->
                    <div class="form-section mb-4" id="perpajakan-section" style="display: none; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 12px; padding: 20px; border: 2px solid #ffc107;">
                      <div class="section-header mb-3">
                        <h6 class="section-title" style="color: #92400e; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                          <i class="fa-solid fa-file-invoice-dollar"></i>
                          INFORMASI PERPAJAKAN
                          <span style="background: #ffc107; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px;">KHUSUS PERPAJAKAN</span>
                        </h6>
                      </div>

                      <!-- Row 1: No Faktur & Tanggal Faktur -->
                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">No Faktur</label>
                            <div class="detail-value" id="view-no-faktur">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tanggal Faktur</label>
                            <div class="detail-value" id="view-tanggal-faktur">-</div>
                          </div>
                        </div>
                      </div>

                      <!-- Row 2: Tgl Selesai Verifikasi & Jenis PPh -->
                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Tgl. Selesai Verifikasi Pajak</label>
                            <div class="detail-value" id="view-tanggal-selesai-verifikasi-pajak">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">Jenis PPh</label>
                            <div class="detail-value" id="view-jenis-pph">-</div>
                          </div>
                        </div>
                      </div>

                      <!-- Row 3: DPP PPh & PPh Terhutang -->
                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">DPP PPh</label>
                            <div class="detail-value" id="view-dpp-pph">-</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="detail-item">
                            <label class="detail-label">PPh Terhutang</label>
                            <div class="detail-value" id="view-ppn-terhutang">-</div>
                          </div>
                        </div>
                      </div>
                    </div>


                  </div>

                  <!-- Modal Footer -->
                  <div class="modal-footer" style="position: sticky; bottom: 0; z-index: 1050; background: white; border-top: 2px solid #e0e0e0; padding: 16px 24px; flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 24px;">
                      <i class="fa-solid fa-times me-2"></i>Tutup
                    </button>
                    <a href="#" id="view-edit-btn" class="btn" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; padding: 10px 24px;">
                      <i class="fa-solid fa-pen me-2"></i>Edit Dokumen
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <style>
              /* Detail Item Styles for View Modal - Matched with Perpajakan */
              .detail-item {
                margin-bottom: 8px;
              }

              .detail-label {
                display: block;
                font-size: 10px;
                font-weight: 700;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 4px;
              }

              .detail-value {
                font-size: 14px;
                color: #1f2937;
                padding: 8px 12px;
                background: white;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                min-height: 38px;
                display: flex;
                align-items: center;
                word-wrap: break-word;
              }
            </style>

            <script>
            // ===== LIVE SEARCH FUNCTIONALITY =====
            // Debounce function
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Live search handler
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                const liveSearchHandler = debounce(function() {
                    const form = searchInput.closest('form');
                    if (!form) return;

                    const url = new URL(form.action);
                    const formData = new FormData(form);
                    formData.forEach((value, key) => {
                        const normalizedValue = String(value).trim();
                        if (normalizedValue) {
                            url.searchParams.append(key, normalizedValue);
                        }
                    });

                    const perPage = new URLSearchParams(window.location.search).get('per_page');
                    if (perPage && !url.searchParams.has('per_page')) {
                        url.searchParams.set('per_page', perPage);
                    }

                    window.location.href = url.toString();
                }, 500);

                searchInput.addEventListener('input', liveSearchHandler);
            }
            </script>


    <script>
      // AJAX Refresh Document Table
      function captureRefreshPosition(container) {
        const scrollBox = container.querySelector('.table-responsive');
        return {
          windowX: window.scrollX,
          windowY: window.scrollY,
          tableLeft: scrollBox ? scrollBox.scrollLeft : 0,
          tableTop: scrollBox ? scrollBox.scrollTop : 0,
          activeElementId: document.activeElement && document.activeElement.id ? document.activeElement.id : null
        };
      }

      function restoreRefreshPosition(container, position) {
        const scrollBox = container.querySelector('.table-responsive');
        if (scrollBox) {
          scrollBox.scrollLeft = position.tableLeft;
          scrollBox.scrollTop = position.tableTop;
        }

        if (position.activeElementId) {
          const activeElement = document.getElementById(position.activeElementId);
          if (activeElement && typeof activeElement.focus === 'function') {
            activeElement.focus({ preventScroll: true });
          }
        }

        window.scrollTo(position.windowX, position.windowY);
      }

      function refreshDocumentTable(options = {}) {
        const isSilent = Boolean(options.silent);
        const btn = document.getElementById('btnRefreshTable');
        const container = document.getElementById('documentTableContainer');
        if (!btn || !container) return Promise.resolve(false);
        const position = captureRefreshPosition(container);
        btn.classList.add('loading');
        btn.disabled = true;
        return fetch(window.location.href, {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newTable = doc.getElementById('documentTableContainer');
          if (newTable) {
            container.innerHTML = newTable.innerHTML;
            requestAnimationFrame(() => {
              restoreRefreshPosition(container, position);
              window.dispatchEvent(new CustomEvent('document-table-refreshed'));
              if (typeof window.acnRefresh === 'function') window.acnRefresh();
              requestAnimationFrame(() => restoreRefreshPosition(container, position));
            });
            if (!isSilent) showRefreshToast('success', 'Data berhasil diperbarui!');
            return true;
          } else {
            if (!isSilent) showRefreshToast('error', 'Gagal memperbarui data.');
            return false;
          }
        })
        .catch(error => {
          console.error('Refresh error:', error);
          if (!isSilent) showRefreshToast('error', 'Gagal memperbarui data. Coba lagi.');
          return false;
        })
        .finally(() => {
          btn.classList.remove('loading');
          btn.disabled = false;
        });
      }
      function showRefreshToast(type, message) {
        document.querySelectorAll('.refresh-toast').forEach(t => t.remove());
        const toast = document.createElement('div');
        toast.className = `refresh-toast ${type}`;
        toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.parentNode) toast.remove(); }, 3000);
      }
    </script>

@include('partials.virtual-document-table', ['paginator' => $dokumens, 'chunkSize' => 100, 'enabled' => true])
@include('partials._inlineEditEngine')
@include('partials._activeCellNav', ['tableSelector' => '.data-table'])
@include('partials._documentTableStickyCells')
@include('partials.auto-refresh-documents')
@include('partials._compactDocumentTable')
@endsection

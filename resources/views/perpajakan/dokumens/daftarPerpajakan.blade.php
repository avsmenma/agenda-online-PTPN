@extends('layouts/app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    /* Filter section select dropdowns (Year and Status) */
    /* Table Container - Enhanced Horizontal Scroll from dokumensB */

    /* Horizontal Scroll Container */
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
      min-width: 1470px;
      /* Minimum width for horizontal scroll with uraian column + larger action column */
      width: 100%;
    }

    .data-table tbody tr {
      /* Jangan transition: all — agar baris tidak menganimasikan layout saat lebar konten/sidebar berubah (anti-lag) */
      transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease, border-color 0.3s ease;
      border-bottom: 1px solid rgba(26, 77, 62, 0.05);
      border-left: 3px solid transparent;
    }

    .data-table tbody tr:hover {
      background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
      border-left: 3px solid #1a4d3e;
      transform: scale(1.002);
    }


    .data-table tbody tr.highlight-row {
      background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
      border-left: 3px solid #1a4d3e;
    }

    /* Enhanced Locked Row Styling from dokumensB */
    .data-table tbody tr.locked-row {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      opacity: 0.85;
      position: relative;
      border-left: 4px solid #ffc107 !important;
    }


    .data-table td {
      padding: 14px 12px;
      vertical-align: middle;
      border-right: 1px solid rgba(26, 77, 62, 0.05);
      font-size: 13px;
      font-weight: 500;
      color: #000000;
      border-bottom: 1px solid rgba(26, 77, 62, 0.05);
      text-align: center;
    }

    /* Custom centering for specific column content */
    .data-table .col-no,
    .data-table .col-agenda,
    .data-table .col-spp,
    .data-table .col-nilai,
    .data-table .col-status,
    .data-table .col-action {
      text-align: center;
    }

    /* Uraian column - improved styling like akutansi */
    .data-table .col-uraian {
      width: 700px;
      min-width: 500px;
      max-width: 1000px;
      text-align: left;
      word-wrap: break-word;
      word-break: break-word;
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

    /* Modern deadline card design for perpajakan - same as Team Verifikasi */
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

    /* Safe State - Green Theme */
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

    /* Warning State - Orange Theme */
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

    /* Danger State - Red Theme */
    .deadline-card.deadline-danger {
      --deadline-color: #ef4444;
      --deadline-color-light: #f87171;
      --deadline-bg: #fef2f2;
      --deadline-text: #991b1b;
    }

    .deadline-card.deadline-danger {
      background: var(--deadline-bg);
      border-color: rgba(239, 68, 68, 0.2);
    }

    .deadline-card.deadline-danger .deadline-time {
      color: var(--deadline-text);
      font-weight: 800;
    }

    .deadline-indicator.deadline-danger {
      background: linear-gradient(135deg, var(--deadline-color) 0%, var(--deadline-color-light) 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(239, 68, 68, 0.4);
      animation: danger-pulse 2s infinite;
    }

    .deadline-indicator.deadline-danger i::before {
      content: "\f06a";
      /* exclamation-circle */
    }

    /* Overdue State - Dark Red with Alert Animation */
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
      content: "\f04c"; /* pause icon */
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

    /* Progress indicator */
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

    /* Note styling */
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

    /* No deadline state */
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

    /* Animations */
    @keyframes danger-pulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 1;
      }

      50% {
        transform: scale(1.05);
        opacity: 0.9;
      }
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

    /* Column Widths for Horizontal Scroll */
    .data-table .col-no {
      width: 60px;
      min-width: 60px;
      text-align: center;
      font-weight: 600;
    }

    .data-table .col-agenda {
      width: 140px;
      min-width: 140px;
      text-align: center;
    }

    .data-table .col-spp {
      width: 140px;
      min-width: 140px;
      text-align: center;
    }

    .data-table .col-deadline {
      width: 180px;
      min-width: 180px;
      text-align: center;
    }

    .data-table .col-nilai {
      width: 150px;
      min-width: 150px;
      text-align: center;
    }

    .data-table .col-uraian {
      width: 300px;
      min-width: 300px;
      text-align: left;
    }

    .data-table .col-status {
      width: 320px;
      min-width: 320px;
      max-width: 350px;
      text-align: center;
      overflow: visible;
      padding: 8px 4px;
      box-sizing: border-box;
      position: relative;
    }

    .data-table .col-status .badge-status {
      display: inline-block;
      box-sizing: border-box;
    }

    .data-table .col-action {
      width: 180px;
      min-width: 180px;
      max-width: 180px;
      text-align: center;
      overflow: visible;
      padding: 8px 4px;
      box-sizing: border-box;
      position: relative;
    }

    .data-table .col-action>* {
      max-width: 100%;
      box-sizing: border-box;
    }

    .data-table .col-action .action-buttons {
      max-width: 100% !important;
      width: 100% !important;
      display: flex;
      gap: 8px;
      justify-content: center;
      flex-wrap: wrap;
      align-items: center;
    }

    .data-table .col-action .btn-action {
      max-width: 100% !important;
      width: auto !important;
      box-sizing: border-box !important;
      flex: 0 0 auto;
      white-space: nowrap;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 14px;
      background: white;
      border-radius: 10px;
      border: 1px solid rgba(8, 62, 64, 0.08);
      transition: all 0.2s ease;
      min-width: 0;
      width: 100%;
      overflow: hidden;
      box-sizing: border-box;
      position: relative;
    }

    .detail-item:hover {
      border-color: #889717;
      box-shadow: 0 4px 12px rgba(136, 151, 23, 0.15);
      transform: translateY(-2px);
    }

    /* Enhanced visual hierarchy for 5-column layout */
    .detail-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 3px;
      height: 100%;
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      opacity: 0;
      transition: opacity 0.2s ease;
      border-radius: 3px 0 0 3px;
    }

    .detail-item:hover::before {
      opacity: 1;
    }

    /* Optimized for 5-column content */
    .detail-item:nth-child(5n+1) {
      border-left-color: rgba(136, 151, 23, 0.2);
    }

    .detail-item:nth-child(5n+2) {
      border-left-color: rgba(8, 62, 64, 0.2);
    }

    .detail-item:nth-child(5n+3) {
      border-left-color: rgba(136, 151, 23, 0.2);
    }

    .detail-item:nth-child(5n+4) {
      border-left-color: rgba(8, 62, 64, 0.2);
    }

    .detail-item:nth-child(5n) {
      border-left-color: rgba(136, 151, 23, 0.2);
    }

    .detail-label {
      display: inline-block;
      /* Inline block untuk background yang tepat */
      font-size: 11px;
      font-weight: 700;
      /* Extra bold */
      color: #374151;
      /* text-gray-700 - lebih gelap untuk kontras maksimal */
      text-transform: uppercase;
      letter-spacing: 0.7px;
      background: #f3f4f6;
      /* bg-gray-100 - background yang jelas terlihat */
      padding: 6px 10px;
      /* Padding yang visible */
      border-radius: 6px;
      /* Rounded corners yang lembut */
      border-left: 3px solid #6366f1;
      /* Aksen biru di kiri untuk visual distinction */
      margin-bottom: 2px;
      word-wrap: break-word;
      overflow-wrap: break-word;
      white-space: normal;
      max-width: 100%;
      width: fit-content;
      /* Hanya selebar teks */
      min-width: 120px;
      /* Minimum width untuk konsistensi */
    }

    .detail-value {
      font-size: 14px;
      color: #111827;
      /* text-gray-900 - hampir hitam */
      font-weight: 600;
      /* Semi-bold untuk menonjol sebagai data utama */
      word-wrap: break-word;
      overflow-wrap: break-word;
      word-break: break-word;
      hyphens: auto;
      white-space: normal;
      max-width: 100%;
      width: 100%;
      overflow: hidden;
      line-height: 1.6;
      padding: 4px 0;
      /* Sedikit padding atas/bawah */
      position: relative;
    }

    /* Special styling for different field types */
    .detail-value.text-danger {
      color: #dc2626;
      font-weight: 600;
    }

    .detail-value .badge {
      font-size: 11px;
      font-weight: 600;
    }

    /* Special styling for different content types */
    .detail-value[href] {
      color: #083E40;
      text-decoration: none;
      border-bottom: 1px dotted #083E40;
      transition: all 0.2s ease;
    }

    .detail-value[href]:hover {
      color: #889717;
      border-bottom-style: solid;
    }

    /* Ensure proper spacing in 5-column layout */
    .detail-grid .detail-item {
      margin: 0;
    }

    /* Fix overflow issues in narrow columns */
    @media (min-width: 1400px) {
      .detail-item {
        min-height: 80px;
      }
    }

    /* Detail Section Separator - Visual divider between document and tax sections */
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
      border: 2px solid #ffc107;
      border-radius: 12px;
      position: relative;
      overflow: hidden;
    }

    .separator-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    }

    .separator-content i {
      font-size: 18px;
      color: #ffc107;
      background: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .separator-content>span:nth-child(2) {
      font-size: 16px;
      font-weight: 700;
      color: #856404;
      flex: 1;
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
    }

    /* Tax Section Specific Styling */
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

    /* Empty field styling for tax information */
    .empty-field {
      color: #999;
      font-style: italic;
      font-size: 12px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .empty-field::before {
      content: '⚠';
      font-size: 10px;
      opacity: 0.7;
    }

    /* Tax document link styling */
    .tax-document-link {
      color: #083E40;
      text-decoration: none;
      border-bottom: 1px dotted #083E40;
      transition: all 0.2s ease;
      word-break: break-all;
      display: inline-block;
      max-width: 100%;
    }

    .tax-document-link:hover {
      color: #889717;
      border-bottom-style: solid;
      text-decoration: none;
    }

    .tax-document-link i {
      font-size: 10px;
      opacity: 0.7;
      margin-left: 4px;
    }

    /* Responsive separator */
    @media (max-width: 768px) {
      .separator-content {
        flex-direction: column;
        text-align: center;
        gap: 8px;
        padding: 12px 16px;
      }

      .separator-content i {
        font-size: 16px;
        width: 36px;
        height: 36px;
      }

      .separator-content>span:nth-child(2) {
        font-size: 14px;
        order: -1;
      }

      .tax-badge {
        font-size: 10px;
        padding: 4px 12px;
      }
    }

    /* Enhanced Badge Styles matching dokumensB */
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
      white-space: nowrap;
    }

    /* State 1: Terkirim (Locked - Already Sent via Bulk) */
    .badge-status.badge-locked {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border-color: #083E40;
      position: relative;
    }

    /* State 2: ⏳ Diproses (In Progress) - Match Verifikasi style */
    .badge-status.badge-proses {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border-color: #083E40;
    }

    .badge-status.badge-proses::after {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      background: white;
      border-radius: 50%;
      margin-left: 6px;
      animation: pulse 1.5s infinite;
    }

    /* State 3: ✅ Selesai (Completed) - Match Verifikasi style */
    .badge-status.badge-selesai {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border-color: #083E40;
    }

    /* Special state for sent documents - Match Verifikasi style */
    .badge-status.badge-sent {
      background: #083E40;
      color: white;
      border-color: #083E40;
      position: relative;
    }

    /* Special state for warning/pending approval */
    .badge-status.badge-warning {
      background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
      color: white;
      border-color: #ffc107;
    }

    /* Special state for returned/rejected documents */
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

    .badge-status:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    /* Add pulse animation for badge-proses */
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

    /* Enhanced Action Buttons matching dokumensB */
    .action-buttons {
      display: flex;
      gap: 8px;
      justify-content: center;
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

    .btn-action {
      min-width: 44px;
      min-height: 44px;
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 10px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      text-decoration: none;
      user-select: none;
      white-space: nowrap;
      box-sizing: border-box;
    }

    .btn-action span {
      font-size: 10px;
      font-weight: 600;
      white-space: nowrap;
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

    .btn-action.locked {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%) !important;
      color: white;
      cursor: not-allowed;
      opacity: 0.85;
    }

    .btn-action.locked:hover {
      transform: none;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-terkirim {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%) !important;
      color: white;
      cursor: default;
      opacity: 0.85;
    }

    .btn-terkirim:hover {
      transform: none;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-set-deadline {
      background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%) !important;
      color: white;
      border: 1px solid rgba(255, 193, 7, 0.3) !important;
      position: relative;
      overflow: hidden;
      max-width: 100% !important;
      width: 100% !important;
      box-sizing: border-box !important;
      padding: 8px 8px !important;
      font-size: 10px !important;
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

    .btn-send {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
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
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.4);
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
    .data-table tbody tr {
      border-bottom: 1px solid rgba(26, 77, 62, 0.08);
      position: relative;
    }

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
      }

      .badge-status {
        padding: 6px 12px;
        font-size: 11px;
        min-width: 80px;
      }

      .action-buttons {
        gap: 4px;
      }

      .btn-action {
        min-width: 40px;
        min-height: 40px;
        padding: 6px 10px;
        font-size: 10px;
      }

      .btn-action span {
        font-size: 9px;
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
        min-width: 1470px;
        /* Still allow horizontal scroll on very small screens */
      }

      .data-table .col-no {
        min-width: 60px;
      }

      .data-table .col-agenda {
        min-width: 130px;
      }

      .data-table .col-spp {
        min-width: 130px;
      }

      .data-table .col-nilai {
        min-width: 140px;
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
      }

      .data-table .col-status {
        min-width: 140px;
      }

      .data-table .col-action {
        min-width: 160px;
      }
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
      function easeOutExpo(t) { return t === 1 ? 1 : 1 - Math.pow(2, -10 * t); }
      function formatNumber(n) { return Math.floor(n).toLocaleString('id-ID').replace(/\./g, ','); }
      function formatRupiah(n) { return 'Rp ' + Math.floor(n).toLocaleString('id-ID'); }
      function animateCounter(el, target, duration, isRupiah) {
        var start = performance.now(); target = parseInt(target) || 0;
        function step(now) {
          var elapsed = now - start, progress = Math.min(elapsed / duration, 1);
          var current = easeOutExpo(progress) * target;
          el.textContent = isRupiah ? formatRupiah(current) : formatNumber(current);
          if (progress < 1) { requestAnimationFrame(step); }
          else { el.textContent = isRupiah ? formatRupiah(target) : formatNumber(target); }
        }
        requestAnimationFrame(step);
      }
      function initCounters() {
        document.querySelectorAll('[data-counter]').forEach(function(el) {
          var target = el.getAttribute('data-counter');
          var duration = parseInt(el.getAttribute('data-duration')) || 1200;
          var animated = false;
          var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) { if (e.isIntersecting && !animated) { animated = true; animateCounter(el, target, duration, false); obs.disconnect(); } });
          }, { threshold: 0.3 });
          obs.observe(el);
        });
        document.querySelectorAll('[data-counter-rupiah]').forEach(function(el) {
          var target = el.getAttribute('data-counter-rupiah');
          var duration = parseInt(el.getAttribute('data-duration')) || 1600;
          var animated = false;
          var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) { if (e.isIntersecting && !animated) { animated = true; animateCounter(el, target, duration, true); obs.disconnect(); } });
          }, { threshold: 0.3 });
          obs.observe(el);
        });
      }
      if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initCounters); } else { initCounters(); }
    })();
  </script>




  <script>
    (function() {
      function easeOutExpo(t) { return t === 1 ? 1 : 1 - Math.pow(2, -10 * t); }
      function formatNumber(n) { return Math.floor(n).toLocaleString('id-ID').replace(/\./g, ','); }
      function formatRupiah(n) { return 'Rp ' + Math.floor(n).toLocaleString('id-ID'); }
      function animateCounter(el, target, duration, isRupiah) {
        var start = performance.now(); target = parseInt(target) || 0;
        function step(now) {
          var elapsed = now - start, progress = Math.min(elapsed / duration, 1);
          var current = easeOutExpo(progress) * target;
          el.textContent = isRupiah ? formatRupiah(current) : formatNumber(current);
          if (progress < 1) { requestAnimationFrame(step); }
          else { el.textContent = isRupiah ? formatRupiah(target) : formatNumber(target); }
        }
        requestAnimationFrame(step);
      }
      function initCounters() {
        document.querySelectorAll('[data-counter]').forEach(function(el) {
          var target = el.getAttribute('data-counter');
          var duration = parseInt(el.getAttribute('data-duration')) || 1200;
          var animated = false;
          var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) { if (e.isIntersecting && !animated) { animated = true; animateCounter(el, target, duration, false); obs.disconnect(); } });
          }, { threshold: 0.3 });
          obs.observe(el);
        });
        document.querySelectorAll('[data-counter-rupiah]').forEach(function(el) {
          var target = el.getAttribute('data-counter-rupiah');
          var duration = parseInt(el.getAttribute('data-duration')) || 1600;
          var animated = false;
          var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) { if (e.isIntersecting && !animated) { animated = true; animateCounter(el, target, duration, true); obs.disconnect(); } });
          }, { threshold: 0.3 });
          obs.observe(el);
        });
      }
      if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initCounters); } else { initCounters(); }
    })();
  </script>

  @include('partials.document-role-filter-toolbar', [
    'filterRouteName' => 'documents.perpajakan.index',
    'filterDariOptions' => $filterDariOptions ?? [],
    'filterStatusOptions' => [
      '' => 'Semua Status',
      'sedang_proses' => 'Sedang Proses',
      'terkirim_akutansi' => 'Terkirim ke Akutansi',
      'terkirim_pembayaran' => 'Terkirim ke Pembayaran',
      'menunggu_approve' => 'Menunggu Approve',
      'ditolak' => 'Dokumen Ditolak',
    ],
  ])
  @if(isset($suggestions) && !empty($suggestions) && request('search'))
    <!-- Search Suggestions Alert -->
    <div class="alert alert-info alert-dismissible fade show suggestion-alert" role="alert"
      style="margin-bottom: 20px; border-left: 4px solid #0dcaf0; background-color: #e7f3ff;">
      <div class="d-flex align-items-start">
        <i class="fa-solid fa-lightbulb me-2 mt-1" style="color: #0dcaf0; font-size: 18px;"></i>
        <div style="flex: 1;">
          <strong style="color: #0a58ca;">Apakah yang Anda maksud?</strong>
          <p class="mb-2 mt-2" style="color: #055160;">
            Tidak ada hasil ditemukan untuk "<strong>{{ request('search') }}</strong>". Mungkin maksud Anda:
          </p>
          <div class="suggestion-buttons d-flex flex-wrap gap-2">
            @foreach($suggestions as $suggestion)
              <button type="button" class="btn btn-sm btn-outline-primary suggestion-btn" data-suggestion="{{ $suggestion }}"
                style="border-color: #0dcaf0; color: #0dcaf0;">
                <i class="fa-solid fa-magnifying-glass me-1"></i>{{ $suggestion }}
              </button>
            @endforeach
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  <div class="table-dokumen" id="documentTableContainer">
    <div class="table-responsive">
      @php
        $showActionColumn = false;
      @endphp
      <table class="data-table document-table">
        <thead>
          <tr>

            <th class="col-no">No</th>
            @foreach($selectedColumns as $col)
              @if($col !== 'status')
                @if($col === 'nomor_agenda')
                  <th class="col-{{ $col }}" onclick="toggleSort('{{ $col }}')" style="cursor:pointer; user-select:none; white-space:nowrap;">
                    {{ $availableColumns[$col] ?? $col }}
                    <span style="display:inline-flex; flex-direction:column; line-height:0.5; margin-left:4px; font-size:10px; vertical-align:middle;">
                      <i class="fas fa-caret-up" style="opacity:{{ (isset($sortColumn) && $sortColumn==$col && isset($sortOrder) && $sortOrder=='asc') ? '1' : '0.3' }}"></i>
                      <i class="fas fa-caret-down" style="opacity:{{ (isset($sortColumn) && $sortColumn==$col && isset($sortOrder) && $sortOrder=='desc') ? '1' : '0.3' }}"></i>
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
          @include("perpajakan.dokumens._rows")
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal for Setting Deadline -->
  <div class="modal fade" id="setDeadlineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
          <h5 class="modal-title">
            <i class="fa-solid fa-clock me-2"></i>Tetapkan Deadline Team Perpajakan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="deadlineDocId">

          <div class="alert alert-info border-0"
            style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 140, 0, 0.1) 100%); border-left: 4px solid #ffc107;">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Penting:</strong> Setelah deadline ditetapkan, dokumen akan terbuka untuk diproses.
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">
              <i class="fa-solid fa-calendar-days me-2"></i>Periode Deadline*
            </label>
            <select class="form-select" id="deadlineDays" required>
              <option value="">Pilih periode deadline</option>
              <option value="1">1 hari</option>
              <option value="2">2 hari</option>
              <option value="3">3 hari (maksimal)</option>
            </select>
            <div class="form-text">Maksimal deadline adalah 3 hari untuk efisiensi proses</div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">
              <i class="fa-solid fa-sticky-note me-2"></i>Catatan Deadline <span class="text-muted">(opsional)</span>
            </label>
            <textarea class="form-control" id="deadlineNote" rows="3"
              placeholder="Contoh: Perlu verifikasi dokumen pajak..." maxlength="500"></textarea>
            <div class="form-text">
              <span id="charCount">0</span>/500 karakter
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-warning" onclick="confirmSetDeadline()">
            <i class="fa-solid fa-check me-2"></i>Tetapkan Deadline
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
            <strong>Informasi:</strong> Dokumen akan dikirim kembali ke Team Verifikasi setelah proses perpajakan selesai.
            Isi catatan singkat agar Verifikasi tahu konteks dokumen.
          </div>

          <div class="form-group mb-3">
            <label for="returnReason" class="form-label">
              <strong>Catatan Penyerahan Kembali <span class="text-danger">*</span></strong>
            </label>
            <textarea class="form-control" id="returnReason" rows="4"
              placeholder="Contoh: Pajak sudah diproses, dokumen fisik diserahkan kembali ke Verifikasi." maxlength="500"
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
              <li>Tetap terlihat di daftar dokumen perpajakan sebagai riwayat kerja</li>
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
            <br>• Daftar dokumen Team Perpajakan sebagai riwayat
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

  <!-- Modal for Send Confirmation -->
  <div class="modal fade" id="sendConfirmationModal" tabindex="-1" aria-labelledby="sendConfirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%); color: white;">
          <h5 class="modal-title" id="sendConfirmationModalLabel">
            <i class="fa-solid fa-paper-plane me-2"></i>Konfirmasi Pengiriman Dokumen
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="sendConfirmationDocId">
          <div class="mb-4">
            <label class="form-label fw-bold mb-3">
              <i class="fa-solid fa-route me-2"></i>Pilih Tujuan Pengiriman
            </label>

            <!-- Visual Card Selection -->
            <div class="destination-cards-container">
              <div class="destination-card" data-value="akutansi" onclick="selectDestination('akutansi')">
                <div class="destination-icon">
                  <i class="fa-solid fa-calculator"></i>
                </div>
                <div class="destination-info">
                  <div class="destination-name">Team Akutansi</div>
                  <div class="destination-desc">Kirim untuk proses akutansi</div>
                </div>
                <div class="destination-check">
                  <i class="fa-solid fa-circle-check"></i>
                </div>
              </div>

              <div class="destination-card" data-value="pembayaran" onclick="selectDestination('pembayaran')">
                <div class="destination-icon pembayaran">
                  <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div class="destination-info">
                  <div class="destination-name">Team Pembayaran</div>
                  <div class="destination-desc">Kirim langsung ke pembayaran</div>
                </div>
                <div class="destination-check">
                  <i class="fa-solid fa-circle-check"></i>
                </div>
              </div>
            </div>

            <input type="hidden" id="nextHandlerSelect" value="">
          </div>

          <div class="alert alert-info border-0" id="sendConfirmationInfo">
            <i class="fa-solid fa-circle-info me-2"></i>
            Pastikan seluruh data Team Perpajakan sudah lengkap sebelum mengirim dokumen.
          </div>
          <div class="alert alert-warning border-0 d-none" id="missingFieldsWrapper">
            <div class="d-flex align-items-start">
              <i class="fa-solid fa-triangle-exclamation me-2 mt-1"></i>
              <div>
                <strong>Beberapa form khusus perpajakan belum diisi:</strong>
                <ul class="mt-2 mb-0" id="missingFieldsList"></ul>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-success" id="confirmSendBtn" onclick="confirmSendToNext()" disabled>
            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Sekarang
          </button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .destination-cards-container {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .destination-card {
      display: flex;
      align-items: center;
      padding: 16px 20px;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: #fff;
      position: relative;
    }

    .destination-card:hover {
      border-color: #1a4d3e;
      background: #f0fdf4;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(26, 77, 62, 0.15);
    }

    .destination-card.selected {
      border-color: #1a4d3e;
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      box-shadow: 0 4px 12px rgba(26, 77, 62, 0.2);
    }

    .destination-card.selected .destination-check {
      opacity: 1;
      transform: scale(1);
    }

    .destination-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 16px;
      flex-shrink: 0;
    }

    .destination-icon i {
      font-size: 22px;
      color: white;
    }

    .destination-icon.pembayaran {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .destination-info {
      flex: 1;
    }

    .destination-name {
      font-weight: 600;
      font-size: 16px;
      color: #1f2937;
      margin-bottom: 2px;
    }

    .destination-desc {
      font-size: 13px;
      color: #6b7280;
    }

    .destination-check {
      width: 28px;
      height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transform: scale(0.5);
      transition: all 0.3s ease;
    }

    .destination-check i {
      font-size: 24px;
      color: #1a4d3e;
    }
  </style>

  <!-- Modal for Send Success -->
  <div class="modal fade" id="sendSuccessModal" tabindex="-1" aria-labelledby="sendSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #0d6c44 0%, #16a085 100%); color: white;">
          <h5 class="modal-title" id="sendSuccessModalLabel">
            <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-check-circle" style="font-size: 48px; color: #16a085;"></i>
          </div>
          <h5 class="fw-bold mb-3" id="sendSuccessTitle">Dokumen berhasil dikirim!</h5>
          <p class="text-muted mb-0" id="sendSuccessMessage">
            Data Team Perpajakan telah disertakan dan dokumen sekarang akan muncul di halaman tujuan.
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

  <!-- Modal for Deadline Success -->
  <div class="modal fade" id="deadlineSuccessModal" tabindex="-1" aria-labelledby="deadlineSuccessModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
          <h5 class="modal-title" id="deadlineSuccessModalLabel">
            <i class="fa-solid fa-circle-check me-2"></i>Deadline Berhasil Ditentukan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-check-circle" style="font-size: 48px; color: #ffc107;"></i>
          </div>
          <h5 class="fw-bold mb-3">Deadline berhasil ditetapkan!</h5>
          <p class="text-muted mb-0" id="deadlineSuccessMessage">
            Dokumen sekarang terbuka untuk diproses.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal" id="deadlineSuccessBtn">
            <i class="fa-solid fa-check me-2"></i>Selesai
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Deadline Warning -->
  <div class="modal fade" id="deadlineWarningModal" tabindex="-1" aria-labelledby="deadlineWarningModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
          <h5 class="modal-title" id="deadlineWarningModalLabel">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>Perhatian
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-3">
            <i class="fa-solid fa-exclamation-circle" style="font-size: 52px; color: #ffc107;"></i>
          </div>
          <h5 class="fw-bold mb-3">Pilih Periode Deadline Terlebih Dahulu!</h5>
          <p class="text-muted mb-0">
            Silakan pilih periode deadline (1 hari, 2 hari, 3 hari, 1 minggu, atau 2 minggu) sebelum menetapkan deadline.
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

    // Open View Document Modal
    function openViewDocumentModal(docId) {
      // Set document ID
      document.getElementById('view-dokumen-id').value = docId;

      // Set edit button URL
      document.getElementById('view-edit-btn').href = `/documents/perpajakan/${docId}/edit`;

      // Load document data via AJAX
      fetch(`/documents/perpajakan/${docId}/detail`, {
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

            // Nomor PO & PR with fallback to NO_PO
            const poList = dok.dokumen_pos && dok.dokumen_pos.length > 0
              ? dok.dokumen_pos.map(po => po.nomor_po).filter(p => p).join(', ')
              : (dok.NO_PO || '-');
            const prList = dok.dokumen_prs && dok.dokumen_prs.length > 0
              ? dok.dokumen_prs.map(pr => pr.nomor_pr).filter(p => p).join(', ')
              : '-';
            document.getElementById('view-nomor-po').textContent = poList;
            document.getElementById('view-nomor-pr').textContent = prList;

            // Informasi Akutansi
            const miroEl = document.getElementById('view-nomor-miro-akutansi');
                if (miroEl) miroEl.textContent = dok.nomor_miro || '-';
                const tanggalMiroEl = document.getElementById('view-tanggal-miro');
                if (tanggalMiroEl) tanggalMiroEl.textContent = dok.tanggal_miro ? formatDate(dok.tanggal_miro) : '-';

                                    // Informasi Perpajakan (Simplified - 6 Essential Fields)
                                    // Row 1: No Faktur & Tanggal Faktur
                                    document.getElementById('view-no-faktur').textContent = dok.no_faktur || '-';
                                    document.getElementById('view-tanggal-faktur').textContent = dok.tanggal_faktur ? formatDate(dok.tanggal_faktur) : '-';
                                    // Row 2: Tgl Selesai Verifikasi & Jenis PPh
                                    document.getElementById('view-tanggal-selesai-verifikasi-pajak').textContent = dok.tanggal_selesai_verifikasi_pajak ? formatDate(dok.tanggal_selesai_verifikasi_pajak) : '-';
                                    document.getElementById('view-jenis-pph').textContent = dok.jenis_pph || '-';
                                    // Row 3: DPP PPh & PPh Terhutang
                                    document.getElementById('view-dpp-pph').textContent = dok.dpp_pph ? formatNumber(dok.dpp_pph) : '-';
                                    document.getElementById('view-ppn-terhutang').textContent = dok.ppn_terhutang ? formatNumber(dok.ppn_terhutang) : '-';
                                  }
                                })
                                .catch(error => {
                                  console.error('Error loading document:', error);
                                });

                              // Show modal
                              const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                              modal.show();

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

                            function openSetDeadlineModal(docId) {
                              document.getElementById('deadlineDocId').value = docId;
                              document.getElementById('deadlineDays').value = '';
                              document.getElementById('deadlineNote').value = '';
                              document.getElementById('charCount').textContent = '0';
                              const modal = new bootstrap.Modal(document.getElementById('setDeadlineModal'));
                              modal.show();
                            }

                            function confirmSetDeadline() {
                              const docId = document.getElementById('deadlineDocId').value;
                              const deadlineDays = document.getElementById('deadlineDays').value;
                              const deadlineNote = document.getElementById('deadlineNote').value;

                              if (!deadlineDays) {
                                // Show warning modal instead of alert
                                const warningModal = new bootstrap.Modal(document.getElementById('deadlineWarningModal'));
                                warningModal.show();

                                // Focus back to deadline days select when modal is closed
                                const warningModalEl = document.getElementById('deadlineWarningModal');
                                warningModalEl.addEventListener('hidden.bs.modal', function() {
                                  const deadlineDaysSelect = document.getElementById('deadlineDays');
                                  if (deadlineDaysSelect) {
                                    setTimeout(() => {
                                      deadlineDaysSelect.focus();
                                    }, 100);
                                  }
                                }, { once: true });

                                return;
                              }

                              const submitBtn = document.querySelector('[onclick="confirmSetDeadline()"]');
                              const originalHTML = submitBtn.innerHTML;
                              submitBtn.disabled = true;
                              submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

                              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                              if (!csrfToken) {
                                alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalHTML;
                                return;
                              }

                              fetch(`/documents/perpajakan/${docId}/set-deadline`, {
                                method: 'POST',
                                headers: {
                                  'Content-Type': 'application/json',
                                  'X-CSRF-TOKEN': csrfToken,
                                  'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                  deadline_days: parseInt(deadlineDays),
                                  deadline_note: deadlineNote
                                })
                              })
                              .then(response => response.json())
                              .then(data => {
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
                                  }

                                  // Reload page when modal is closed
                                  successModalEl.addEventListener('hidden.bs.modal', function() {
                                    location.reload();
                                  }, { once: true });

                                  successModal.show();
                                } else {
                                  alert('Gagal menetapkan deadline: ' + data.message);
                                  submitBtn.disabled = false;
                                  submitBtn.innerHTML = originalHTML;
                                }
                              })
                              .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat menetapkan deadline: ' + error.message);
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalHTML;
                              });
                            }

                            // Enhanced deadline system with color coding and late information for perpajakan
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

                              // Get sent and completed status from data attributes
                              const isSent = card.dataset.sent === 'true';
                              const isCompleted = card.dataset.completed === 'true';

                              const deadline = new Date(deadlineStr);
                              const now = new Date();
                              const diffMs = deadline - now;

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
                                // Overdue state
                                card.classList.add('deadline-overdue');

                                // Calculate how late
                                const diffHours = Math.abs(Math.floor(diffMs / (1000 * 60 * 60)));
                                const diffDays = Math.abs(Math.floor(diffMs / (1000 * 60 * 60 * 24)));

                                // Update status text
                                statusText.textContent = 'TERLAMBAT';
                                statusIcon.className = 'fa-solid fa-exclamation-triangle';
                                statusIndicator.className = 'deadline-indicator deadline-overdue';

                                // Only show late info if document is not sent
                                if (!isSent) {
                                  // Create late info with enhanced styling
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
                                }

                                // Add progress bar at bottom
                                const progressBar = document.createElement('div');
                                progressBar.className = 'deadline-progress';
                                card.appendChild(progressBar);

                              } else {
                                // Time remaining
                                const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                                const diffMinutes = Math.floor(diffMs / (1000 * 60));
                                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                                // Get original deadline_days from data attribute
                                const deadlineDays = parseInt(card.dataset.deadlineDays) || null;
                                const totalHoursRemaining = Math.floor(diffMs / (1000 * 60 * 60));

                                // Simplified 3-status logic: >= 1 hari = hijau, < 1 hari = kuning, terlambat = merah
                                if (diffDays >= 1) {
                                  // Safe (>= 1 day) - Green
                                  card.classList.add('deadline-safe');
                                  statusText.textContent = 'AMAN';
                                  statusIcon.className = 'fa-solid fa-check-circle';
                                  statusIndicator.className = 'deadline-indicator deadline-safe';

                                  // Only add time remaining hint if document is not sent
                                  if (!isSent) {
                                    const timeHint = document.createElement('div');
                                    timeHint.style.cssText = 'font-size: 8px; color: #065f46; margin-top: 2px; font-weight: 600;';

                                    // Use deadlineDays to display correctly for 1-day deadlines
                                    if (deadlineDays === 1 && totalHoursRemaining >= 12) {
                                      timeHint.textContent = '1 hari lagi';
                                    } else if (diffDays >= 1) {
                                      timeHint.textContent = `${diffDays} ${diffDays === 1 ? 'hari' : 'hari'} lagi`;
                                    } else {
                                      timeHint.textContent = `${totalHoursRemaining} jam lagi`;
                                    }
                                    card.appendChild(timeHint);
                                  }

                                } else if (diffHours >= 1 || diffMinutes >= 1) {
                                  // Warning (< 1 day) - Yellow
                                  card.classList.add('deadline-warning');
                                  statusText.textContent = 'DEKAT';
                                  statusIcon.className = 'fa-solid fa-exclamation-triangle';
                                  statusIndicator.className = 'deadline-indicator deadline-warning';

                                  // Only add time remaining hint if document is not sent
                                  if (!isSent) {
                                    const timeHint = document.createElement('div');
                                    timeHint.style.cssText = 'font-size: 8px; color: #92400e; margin-top: 2px; font-weight: 700;';

                                    // Use deadlineDays to display correctly for 1-day deadlines
                                    if (deadlineDays === 1 && totalHoursRemaining >= 12) {
                                      timeHint.textContent = '1 hari lagi';
                                    } else if (diffHours >= 1) {
                                      timeHint.textContent = `${diffHours} ${diffHours === 1 ? 'jam' : 'jam'} lagi`;
                                    } else {
                                      timeHint.textContent = `${diffMinutes} menit lagi`;
                                      timeHint.style.animation = 'warning-shake 1s infinite';
                                    }
                                    card.appendChild(timeHint);
                                  }

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
                            });

                            // Character counter for deadline note
                            document.addEventListener('DOMContentLoaded', function() {
                              const deadlineNote = document.getElementById('deadlineNote');
                              const charCount = document.getElementById('charCount');

                              if (deadlineNote && charCount) {
                                deadlineNote.addEventListener('input', function() {
                                  charCount.textContent = this.value.length;
                                });
                              }
                            });

                            // Return functionality has been removed as it's no longer needed

                            let currentSendButton = null;
                            let currentSendButtonOriginalHTML = '';
                            let shouldReloadAfterSuccess = false;

                            function handleSendToNext(docId) {
                              const sendBtn = document.querySelector(`button[data-doc-id="${docId}"]`);
                              if (!sendBtn) {
                                console.warn('Send button not found for document ID:', docId);
                                return;
                              }

                              currentSendButton = sendBtn;
                              currentSendButtonOriginalHTML = sendBtn.innerHTML;

                              const missingFieldsAttr = sendBtn.getAttribute('data-missing-fields') || '';
                              const missingFields = missingFieldsAttr
                                .split('||')
                                .map(field => field.trim())
                                .filter(field => field.length > 0);

                              const missingWrapper = document.getElementById('missingFieldsWrapper');
                              const missingList = document.getElementById('missingFieldsList');
                              const infoAlert = document.getElementById('sendConfirmationInfo');
                              const confirmBtn = document.getElementById('confirmSendBtn');
                              const nextHandlerSelect = document.getElementById('nextHandlerSelect');

                              // Reset form
                              nextHandlerSelect.value = '';

                              if (missingFields.length > 0) {
                                missingWrapper.classList.remove('d-none');
                                missingList.innerHTML = missingFields.map(field => `<li>${field}</li>`).join('');
                                infoAlert.classList.add('d-none');
                              } else {
                                missingWrapper.classList.add('d-none');
                                missingList.innerHTML = '';
                                infoAlert.classList.remove('d-none');
                              }

                              document.getElementById('sendConfirmationDocId').value = docId;
                              // Reset card selection and button state
                              confirmBtn.disabled = true; // Start disabled, enable when user selects a destination
                              confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Kirim Sekarang';

                              // Reset all card selections
                              document.querySelectorAll('.destination-card').forEach(card => {
                                card.classList.remove('selected');
                              });
                              document.getElementById('nextHandlerSelect').value = '';

                              const modal = new bootstrap.Modal(document.getElementById('sendConfirmationModal'));
                              modal.show();
                            }

                            // Function to handle visual card selection for destination
                            function selectDestination(value) {
                              // Remove selected class from all cards
                              document.querySelectorAll('.destination-card').forEach(card => {
                                card.classList.remove('selected');
                              });

                              // Add selected class to clicked card
                              const selectedCard = document.querySelector(`.destination-card[data-value="${value}"]`);
                              if (selectedCard) {
                                selectedCard.classList.add('selected');
                              }

                              // Set hidden input value
                              document.getElementById('nextHandlerSelect').value = value;

                              // Enable send button
                              document.getElementById('confirmSendBtn').disabled = false;
                            }

                            function confirmSendToNext() {
                              const docId = document.getElementById('sendConfirmationDocId').value;
                              const nextHandler = document.getElementById('nextHandlerSelect').value;

                              if (!docId) {
                                alert('Dokumen tidak ditemukan. Silakan muat ulang halaman.');
                                return;
                              }

                              if (!nextHandler) {
                                alert('Silakan pilih tujuan pengiriman terlebih dahulu.');
                                return;
                              }

                              performSendToNext(docId, nextHandler);
                            }

                            function performSendToNext(docId, nextHandler) {
                              const confirmBtn = document.getElementById('confirmSendBtn');
                              confirmBtn.disabled = true;
                              confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

                              if (currentSendButton) {
                                currentSendButton.disabled = true;
                                currentSendButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';
                              }

                              fetch(`/documents/perpajakan/${docId}/send-to-next`, {
                                method: 'POST',
                                headers: {
                                  'Content-Type': 'application/json',
                                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                  next_handler: nextHandler
                                })
                              })
                              .then(response => response.json())
                              .then(data => {
                                if (data.success) {
                                  const modalElement = document.getElementById('sendConfirmationModal');
                                  const modalInstance = bootstrap.Modal.getInstance(modalElement);
                                  modalInstance.hide();

                                  // Update success message based on handler
                                  const handlerName = nextHandler === 'akutansi' ? 'Team Akutansi' : 'Team Pembayaran';
                                  document.getElementById('sendSuccessTitle').textContent = `Dokumen berhasil dikirim ke ${handlerName}!`;
                                  document.getElementById('sendSuccessMessage').textContent = `Data Team Perpajakan telah disertakan dan dokumen sekarang akan muncul di halaman ${handlerName}.`;

                                  showSendSuccessModal();
                                } else {
                                  alert('❌ Gagal mengirim dokumen: ' + data.message);
                                }
                              })
                              .catch(error => {
                                console.error('Error:', error);
                                alert('❌ Terjadi kesalahan saat mengirim dokumen. Silakan coba lagi.');
                              })
                              .finally(() => {
                                confirmBtn.disabled = false;
                                confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Kirim Sekarang';

                                if (currentSendButton) {
                                  currentSendButton.disabled = false;
                                  currentSendButton.innerHTML = currentSendButtonOriginalHTML || '<i class="fa-solid fa-paper-plane"></i>';
                                }
                              });
                            }

                            function showSendSuccessModal() {
                              const successModalEl = document.getElementById('sendSuccessModal');
                              if (!successModalEl) {
                                location.reload();
                                return;
                              }
                              shouldReloadAfterSuccess = true;
                              const successModal = new bootstrap.Modal(successModalEl);
                              successModal.show();
                            }

                            document.addEventListener('DOMContentLoaded', function() {
                              const successModalEl = document.getElementById('sendSuccessModal');
                              if (successModalEl) {
                                successModalEl.addEventListener('hidden.bs.modal', function() {
                                  if (shouldReloadAfterSuccess) {
                                    shouldReloadAfterSuccess = false;
                                    location.reload();
                                  }
                                });
                              }

                              // Initialize Bootstrap tooltips for disabled send buttons
                              const disabledSendButtons = document.querySelectorAll('.btn-send:disabled');
                              disabledSendButtons.forEach(button => {
                                if (button.getAttribute('title')) {
                                  new bootstrap.Tooltip(button, {
                                    placement: 'top',
                                    trigger: 'hover focus'
                                  });
                                }
                              });

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
                                fetch(`/documents/perpajakan/${docId}/return`, {
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
                                                        @elseif($col == 'nomor_mirror')
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
                                'nomor_mirror': ['MIR-1001', 'MIR-1002', 'MIR-1003', 'MIR-1004', 'MIR-1005'],
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

                                  // Handle special formatting for link_dokumen_pajak
                                  if (columnKey === 'link_dokumen_pajak' && sampleData[columnKey]) {
                                    previewHTML += `<td>${cellValue}</td>`;
                                  } else {
                                    previewHTML += `<td>${cellValue}</td>`;
                                  }
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
                                alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
                                return;
                              }

                              // Try multiple selectors to find the form
                              let filterForm = document.getElementById('filterForm');
                              if (!filterForm) {
                                filterForm = document.querySelector('form[action*="perpajakan"]');
                              }
                              if (!filterForm) {
                                // Fallback: use first form on page
                                filterForm = document.querySelector('form');
                              }

                              if (!filterForm) {
                                alert('Form tidak ditemukan.');
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
                                        <div class="col-md-3">
                                          <div class="detail-item">
                                            <label class="detail-label">Nomor Miro</label>
                                            <div class="detail-value" id="view-nomor-miro">-</div>
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
                                    <div class="form-section mb-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 12px; padding: 20px; border: 2px solid #ffc107;">
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
                                      <div class="row g-3">
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

                                  <!-- Sticky Footer -->
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
                            /* Detail Item Styles for View Modal */
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

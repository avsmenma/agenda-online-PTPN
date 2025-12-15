@extends('layouts/app')
@section('content')
  @php
    use Illuminate\Support\Str;
  @endphp

  <!-- Critical JavaScript Functions - Load First -->
  <script>
        // Use IIFE to create a closure and ensure proper function definit  ion
      ( function () {
        'use strict';

        console.log('🚀 Initializing critical functions...');

        // Wrapper function untuk handle row click dengan text selection check
        function handleRowClick      (event, docId) {
          // Cek apakah user sedang menyeleksi teks
          const selection = window.getSelection();
          const selectedText = selection.toString().trim();

          // Jika ada text yang diseleksi, jangan toggle detail
          if (selectedText.length > 0) {
            console.log('Text selection detected, preventing detail toggle');
            event.preventDefault();
            event.stopPropagation();
            return false;
          }

          // Cek apakah ini adalah double-click (biasanya untuk select word)
          if (event.detail === 2) {
            // Double-click biasanya untuk select word, tunggu sebentar untuk cek selection
            setTimeout(() => {
              const newSelection = window.getSelection();
              if (newSelection.toString().trim().length > 0) {
                console.log('Double-click text selection detected, preventing detail toggle');
                return false;
              }
            }, 50);
            // Untuk double-click, kita biarkan default behavior (select word) dulu
            return false;
          }

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
            target.closest('.status-button') !== null ||
            target.closest('.status-dropdown') !== null ||
            target.closest('.status-actions') !== null ||
            target.closest('.action-buttons') !== null ||
            target.closest('[role="button"]') !== null;

          if (isInteractiveElement) {
            // User klik elemen interaktif, biarkan default behavior
            return true;
          }

          // Cek apakah user sedang melakukan drag (mouse drag selection)
          // Jika event.which === 0, ini biasanya adalah programmatic click atau drag
          if (event.detail === 0 || (event.which === 0 && event.button === 0)) {
            console.log('Drag detected, preventing detail toggle');
            return false;
          }

          // Jika aman, panggil openViewDocumentModal
          openViewDocumentModal(docId);
          return true;
        }

        // Define functions immediately to prevent "is not defined" errors
        function toggleDetailImpl(event, docId) {
          console.log('🎯 toggleDetail called with docId:', docId, 'event:', event);

          // Always prevent default behavior and stop propagation
          if (event) {
            event.preventDefault();
            event.stopPropagation();
          }

          // Check if click is on interactive elements that should NOT trigger detail view
          if (event && event.target) {
            const target = event.target;
            if (target && (
              target.closest('.btn-action') ||
              target.closest('.status-button') ||
              target.closest('.status-dropdown') ||
              target.closest('.status-actions') ||
              target.closest('.action-buttons') ||
              target.closest('button') ||
              target.closest('a') ||
              target.closest('select') ||
              target.closest('input') ||
              target.closest('textarea')
            )) {
              console.log('Click on interactive element, skipping detail toggle');
              return;
            }
          }

          // Debug: Log the document being clicked
          console.log('Opening detail for document ID:', docId);

          // Find the detail row
          const detailRow = document.getElementById('detail-' + docId);
          if (!detailRow) {
            console.error('Could not find detail row for document:', docId);
            return;
          }

          // Find the main row (parent of detail row)
          const mainRow = detailRow.previousElementSibling;
          if (!mainRow) {
            console.error('Could not find main row for document:', docId);
            return;
          }

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
            // Hide detail
            detailRow.classList.remove('show');
            mainRow.classList.remove('selected');
          } else {
            // Show detail
            loadDocumentDetail(docId);
            detailRow.classList.add('show');
            mainRow.classList.add('selected');

            // Smooth scroll to detail
            setTimeout(() => {
              detailRow.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
              });
            }, 100);
          }
        }

        function loadDocumentDetailImpl(docId) {
          console.log('loadDocumentDetail called for docId:', docId);
          const detailContent = document.getElementById('detail-content-' + docId);

          if (!detailContent) {
            console.error('Detail content container not found for document:', docId);
            return;
          }

          // Show loading state
          detailContent.innerHTML = `
                <div class="text-center p-4">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                </div>
            `;

          // Get CSRF token
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

          fetch(`/dokumens/${docId}/detail`, {
            method: 'GET',
            headers: {
              'Content-Type': 'text/html',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'text/html'
            }
          })
            .then(response => {
              console.log('Response status:', response.status);
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.text();
            })
            .then(html => {
              console.log('Detail loaded successfully for document:', docId);
              detailContent.innerHTML = html;

              // Initialize countdown for this detail if needed
              if (typeof initializeCountdowns === 'function') {
                setTimeout(() => initializeCountdowns(), 100);
              }

              // Initialize deadlines if function exists
              if (typeof initializeDeadlines === 'function') {
                setTimeout(() => initializeDeadlines(), 100);
              }
            })
            .catch(error => {
              console.error('Error loading document detail:', error);
              detailContent.innerHTML = `
                    <div class="text-center p-4 text-danger">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                        Gagal memuat detail dokumen.
                        <br><small class="text-muted">Error: ${error.message}</small>
                        <br><button class="btn btn-sm btn-primary mt-2" onclick="loadDocumentDetail(${docId})">
                            <i class="fa-solid fa-refresh me-1"></i> Coba Lagi
                        </button>
                    </div>
                `;
            });
        }

        function showNotificationImpl(message, type = 'info') {
          console.log('showNotification called:', message, type);
          // Remove existing notifications
          const existingNotifications = document.querySelectorAll('.notification');
          existingNotifications.forEach(notification => notification.remove());

          // Create notification element
          const notification = document.createElement('div');
          notification.className = `notification notification-${type}`;
          notification.innerHTML = `
                <div class="notification-content">
                    <i class="fa-solid fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;

          // Add to page
          document.body.appendChild(notification);

          // Show notification
          setTimeout(() => notification.classList.add('show'), 100);

          // Auto-hide untuk notifikasi success/error biasa setelah 4 detik
          // Notifikasi dokumen masuk/reject tetap permanen
          if (type === 'success' || type === 'error') {
            setTimeout(() => {
              notification.classList.remove('show');
              setTimeout(() => notification.remove(), 300);
            }, 4000); // 4 detik untuk notifikasi success/error biasa
          }
          // Jika type info atau dokumen masuk/reject, tetap permanen
        }

        // Assign functions to global window object immediately
        window.toggleDetail = toggleDetailImpl;
        window.handleRowClick = handleRowClick;
        window.loadDocumentDetail = loadDocumentDetailImpl;
        window.showNotification = showNotificationImpl;

        console.log('✅ Functions assigned to window object');
        console.log('window.toggleDetail type:', typeof window.toggleDetail);

      })();
    </script>

    <style>
      h2 {
        background: linear-gradient(135deg, #083E40 0%, #889717 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
      }

      .search-box {
        background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 20px;
        box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
        border: 1px solid rgba(8, 62, 64, 0.08);
      }

      search-box .input-group {
        max-width: auto;
      }

      .search-box .input-group-text {
        background: white;
        border: 2px solid rgba(8, 62, 64, 0.1);
        border-right: none;
        border-radius: 10px 0 0 10px;
        padding: 10px 14px;
      }

      .search-box .form-control {
        border: 2px solid rgba(8, 62, 64, 0.1);
        border-left: none;
        border-radius: 0 10px 10px 0;
        padding: 10px 14px;
        font-size: 13px;
        transition: all 0.3s ease;
      }

      .search-box .form-control:focus {
        outline: none;
        border-color: #889717;
        box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
      }

      .search-box .form-control:focus+.input-group-text {
        border-color: #889717;
      }

      .table-dokumen {
        background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
        border: 1px solid rgba(8, 62, 64, 0.08);
        position: relative;
        overflow: hidden;
      }

      /* Table Container Header with Statistics */
      .table-container-header {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
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

      /* Horizontal Scroll Container */
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

      .table-responsive::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, rgba(8, 62, 64, 0.5), rgba(136, 151, 23, 0.6));
      }

      /* Enhanced table for better UX */
      .table-enhanced {
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1200px;
      }

      .table-enhanced thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
        font-weight: 600;
        text-align: center;
        border-bottom: 2px solid #083E40;
        padding: 16px 12px;
        font-size: 13px;
      }

      .table-enhanced th.sticky-column {
        position: sticky;
        left: 0;
        z-index: 11;
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      }

      .table-enhanced tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f0f0;
      }

      .table-enhanced tbody tr:hover {
        background: linear-gradient(135deg, rgba(136, 151, 23, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);
        transform: translateY(-1px);
      }

      /* Enhanced Locked State UX - Simplified for better readability */
      .table-enhanced tbody tr.locked-row {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        opacity: 1;
        position: relative;
        border-left: 3px solid #ffc107 !important;
        transition: all 0.3s ease;
        pointer-events: auto;
        cursor: pointer;
      }

      .table-enhanced tbody tr.locked-row::before {
        display: none;
        /* Removed lock icon overlay */
      }

      .table-enhanced tbody tr.locked-row::after {
        display: none;
        /* Removed diagonal pattern overlay */
      }

      .table-enhanced tbody tr.locked-row:hover {
        background: linear-gradient(135deg, #fffef5 0%, #fff9e6 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.1);
        pointer-events: auto;
      }

      /* Lock animation - Removed for better readability */

      /* Enhanced locked state indicators - Simplified */
      .table-enhanced tbody tr.locked-row .col-status .badge-locked {
        position: relative;
        z-index: 1;
        /* Removed animation for better readability */
      }

      /* Ensure all content in locked row stays above overlay */
      .table-enhanced tbody tr.locked-row td {
        position: relative;
        z-index: 1;
      }

      .table-enhanced tbody tr.locked-row .btn-action {
        position: relative;
        z-index: 1;
      }

      .table-enhanced tbody tr.locked-row .badge-status {
        position: relative;
        z-index: 1;
      }

      .table-enhanced tbody tr.locked-row .col-status .badge-locked::before {
        display: none;
        /* Removed warning icon animation */
      }

      /* Enhanced locked button states - Simplified */
      .table-enhanced tbody tr.locked-row .btn-action.locked {
        position: relative;
        overflow: hidden;
        opacity: 0.6;
      }

      .table-enhanced tbody tr.locked-row .btn-action.locked::before {
        display: none;
        /* Removed shimmer animation */
      }

      .table-enhanced tbody tr.locked-row .btn-action.locked:hover {
        opacity: 0.7;
        cursor: not-allowed;
      }

      /* Lock hover tooltip enhancement */
      .table-enhanced tbody tr.locked-row:hover .btn-action.locked {
        cursor: not-allowed;
      }

      /* Ensure all document rows are clickable regardless of status */
      .table-enhanced tbody tr {
        cursor: pointer !important;
        pointer-events: auto !important;
      }

      /* Override any cursor styles for locked rows to still show pointer */
      .table-enhanced tbody tr.locked-row {
        cursor: pointer !important;
        pointer-events: auto !important;
      }

      /* Make sure the detail rows don't interfere with clicking */
      .detail-row {
        pointer-events: none;
      }

      .detail-row .detail-content {
        pointer-events: auto;
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

      /* Safe State - Green Theme */
      .deadline-card.deadline-safe {
        --deadline-color: #10b981;
        --deadline-color-light: #34d399;
        --deadline-bg: #ecfdf5;
        --deadline-text: #065f46;
      }

      .deadline-card.deadline-safe {
        background: var(--deadline-bg) !important;
        border-color: rgba(16, 185, 129, 0.2) !important;
      }

      .deadline-card.deadline-safe .deadline-time {
        color: var(--deadline-text) !important;
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
        background: var(--deadline-bg) !important;
        border-color: rgba(245, 158, 11, 0.2) !important;
      }

      .deadline-card.deadline-warning .deadline-time {
        color: var(--deadline-text) !important;
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
        background: var(--deadline-bg) !important;
        border-color: rgba(239, 68, 68, 0.2) !important;
      }

      .deadline-card.deadline-danger .deadline-time {
        color: var(--deadline-text) !important;
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
        background: var(--deadline-bg) !important;
        border-color: rgba(220, 38, 38, 0.3) !important;
        animation: overdue-alert 3s infinite;
      }

      .deadline-card.deadline-overdue .deadline-time {
        color: var(--deadline-text) !important;
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

      /* Mobile responsive adjustments */
      @media (max-width: 768px) {
        .table-enhanced .col-deadline {
          min-width: 140px;
        }

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

      /* Responsive Locked State Enhancements */
      @media (max-width: 768px) {
        .table-enhanced tbody tr.locked-row .col-status .badge-locked::before {
          top: -6px;
          right: -6px;
          font-size: 8px;
        }

        .table-enhanced tbody tr.locked-row:hover {
          transform: translateY(-1px);
        }
      }

      @media (max-width: 480px) {
        /* Removed locked-row::before styles */

        .table-enhanced tbody tr.locked-row .col-status .badge-locked::before {
          display: none;
          /* Hide warning icon on very small screens */
        }

        /* Reduce animation intensity on mobile for better performance */
        .table-enhanced tbody tr.locked-row .btn-action.locked::before {
          animation-duration: 4s;
          /* Slower shimmer on mobile */
        }
      }

      /* Animations for enhanced UX */
      /* Removed animations (badge-glow, warning-bounce) for better readability */

      /* Reduced motion support for accessibility */
      @media (prefers-reduced-motion: reduce) {

        .table-enhanced tbody tr.locked-row .col-status .badge-locked,
        .table-enhanced tbody tr.locked-row .btn-action.locked::before,
        .table-enhanced tbody tr.locked-row .deadline-empty {
          animation: none;
        }
      }

      .table-enhanced td {
        padding: 12px;
        vertical-align: middle;
        border-right: 1px solid #e0e0e0;
        white-space: nowrap;
      }

      /* Column width optimization */
      .table-enhanced .col-no {
        width: 80px;
        min-width: 80px;
      }

      .table-enhanced .col-surat {
        width: 120px;
        min-width: 120px;
      }

      .table-enhanced .col-spp {
        width: 140px;
        min-width: 140px;
      }

      .table-enhanced .col-uraian {
        width: 250px;
        min-width: 200px;
      }

      .table-enhanced .col-nilai {
        width: 120px;
        min-width: 120px;
      }

      .table-enhanced .col-deadline {
        width: 160px;
        min-width: 140px;
      }

      .table-enhanced .col-status {
        width: 120px;
        min-width: 100px;
      }

      .table-enhanced .col-action {
        width: 120px;
        min-width: 120px;
      }

      .table-enhanced .col-paraf {
        width: 120px;
        min-width: 120px;
      }

      .table-enhanced .col-sticky {
        position: sticky;
        left: 0;
        background: white;
        z-index: 5;
      }

      /* Mobile optimization */
      @media (max-width: 768px) {
        .table-dokumen {
          padding: 15px;
          border-radius: 12px;
        }

        .table-enhanced {
          min-width: 800px;
          font-size: 12px;
        }

        .table-enhanced th {
          padding: 14px 8px;
          font-size: 11px;
        }

        .table-enhanced td {
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

        .search-box {
          padding: 15px;
          margin-bottom: 15px;
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
          background: rgba(8, 62, 64, 0.8);
          color: white;
          padding: 5px 10px;
          border-radius: 15px;
          font-size: 10px;
          z-index: 5;
          animation: fadeInOut 3s infinite;
        }
      }

      @media (max-width: 576px) {
        .table-enhanced {
          min-width: 900px;
          /* Still allow horizontal scroll on very small screens */
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

      .table-enhanced .col-no {
        width: 60px;
        min-width: 60px;
      }

      .table-enhanced .col-surat {
        width: 80px;
        min-width: 80px;
      }

      .table-enhanced .col-spp {
        width: 100px;
        min-width: 100px;
      }

      .table-enhanced .col-uraian {
        width: 150px;
        min-width: 150px;
      }

      .table-enhanced .col-nilai {
        width: 80px;
        min-width: 80px;
      }

      .table-enhanced .col-deadline {
        width: 100px;
        min-width: 100px;
      }

      .table-enhanced .col-status {
        width: 80px;
        min-width: 80px;
      }

      .table-enhanced .col-action {
        width: 80px;
        min-width: 80px;
      }

      .table-enhanced .col-paraf {
        width: 80px;
        min-width: 80px;
      }
      }

      @media (max-width: 480px) {
        .table-enhanced {
          min-width: 480px;
        }

        .table-enhanced th {
          padding: 10px 6px;
          font-size: 11px;
        }

        .table-enhanced td {
          padding: 8px 6px;
          font-size: 11px;
        }

        .table-enhanced .col-no {
          width: 50px;
          min-width: 50px;
        }

        .table-enhanced .col-surat {
          width: 70px;
          min-width: 70px;
        }

        .table-enhanced .col-spp {
          width: 80px;
          min-width: 80px;
        }

        .table-enhanced .col-uraian {
          width: 120px;
          min-width: 120px;
        }

        .table-enhanced .col-nilai {
          width: 60px;
          min-width: 60px;
        }

        .table-enhanced .col-deadline {
          width: 90px;
          min-width: 90px;
        }

        .table-enhanced .col-status {
          width: 70px;
          min-width: 70px;
        }

        .table-enhanced .col-action {
          width: 70px;
          min-width: 70px;
        }

        .table-enhanced .col-paraf {
          width: 70px;
          min-width: 70px;
        }
      }

      .table-dokumen thead {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
        color: white;
        position: relative;
      }

      .table-dokumen thead::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent 0%, #889717 50%, transparent 100%);
      }

      .table-dokumen thead th {
        padding: 16px 12px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        text-align: center;
        letter-spacing: 0.5px;
      }

      .table-dokumen tbody tr.main-row {
        cursor: pointer;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        position: relative;
      }

      .table-dokumen tbody tr.main-row::after {
        content: 'Klik untuk detail';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        color: #889717;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        background: white;
        padding: 2px 6px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .table-dokumen tbody tr.main-row:hover {
        background: linear-gradient(90deg, rgba(183, 204, 26, 0.05) 0%, transparent 100%);
        border-left: 3px solid #889717;
        transform: scale(1.005);
      }

      .table-dokumen tbody tr.main-row:hover::after {
        opacity: 1;
      }

      .table-dokumen tbody tr.main-row.highlight {
        background: linear-gradient(90deg, rgba(136, 151, 23, 0.15) 0%, transparent 100%) !important;
        border-left: 3px solid #889717;
      }

      .table-dokumen tbody tr.main-row.selected {
        background: linear-gradient(90deg, rgba(8, 62, 64, 0.05) 0%, transparent 100%);
        border-left: 3px solid #083E40;
      }

      .table-dokumen tbody td {
        padding: 14px 12px;
        font-size: 13px;
        vertical-align: middle;
        border-bottom: 1px solid rgba(8, 62, 64, 0.05);
      }

      /* Detail Row Styles */
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
        border-top: 2px solid rgba(8, 62, 64, 0.1);
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        width: 100%;
        box-sizing: border-box;
        overflow-x: hidden;
      }

      /* Detail Grid - Horizontal Layout */
      .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 16px;
        margin-top: 0;
        width: 100%;
        box-sizing: border-box;
      }

      .detail-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        /* Gap untuk background spacing */
        padding: 12px;
        background: #ffffff;
        /* Putih bersih untuk contrast dengan label */
        border-radius: 8px;
        border: 1px solid #f1f5f9;
        /* Border yang sangat tipis */
        transition: all 0.2s ease;
        min-width: 0;
        width: 100%;
        overflow: visible;
        box-sizing: border-box;
      }

      .detail-item:hover {
        border-color: #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
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
        overflow: visible;
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

      /* Badge in detail */
      .detail-value .badge {
        font-size: 11px;
        padding: 4px 12px;
        border-radius: 20px;
      }

      .badge-selesai {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
      }

      .badge-proses {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
      }

      .badge-dikembalikan {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
      }

      .badge-sent {
        background: #083E40;
        color: white;
      }

      /* Responsive Detail Grid */
      @media (max-width: 1200px) {
        .detail-grid {
          grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
          gap: 12px;
        }
      }

      @media (max-width: 768px) {
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
      }

      @media (max-width: 480px) {
        .detail-grid {
          grid-template-columns: 1fr;
          gap: 8px;
        }

        .detail-item {
          padding: 8px;
        }
      }

      /* Simplified Status System - 3 States */
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
      }

      /* State 1: 🔒 Terkunci (Locked - Waiting for Deadline) - Simplified */
      .badge-status.badge-locked {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        border-color: #495057;
        position: relative;
        /* Removed overflow and animation for better readability */
      }

      .badge-status.badge-locked::before {
        display: none;
        /* Removed shimmer animation */
      }

      /* State 2: ⏳ Diproses (In Progress) */
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

      /* State 3: ✅ Selesai (Completed) */
      .badge-status.badge-selesai {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
        border-color: #083E40;
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

      /* Status Dropdown Styles */
      .status-dropdown {
        position: relative;
        display: inline-block;
        z-index: 999;
      }

      /* Ensure status button is clickable */
      .status-button-simple {
        pointer-events: auto !important;
        cursor: pointer !important;
        position: relative;
        z-index: 1000;
      }

      .status-button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px !important;
        border: none !important;
        border-radius: 25px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        position: relative;
        min-width: 140px;
        justify-content: space-between;
        pointer-events: auto !important;
        user-select: none;
        z-index: 1000;
      }

      .status-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
      }

      .status-button .dropdown-arrow {
        transition: transform 0.3s ease;
        font-size: 10px;
      }

      .status-dropdown.active .dropdown-arrow {
        transform: rotate(180deg);
      }

      .status-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.1);
        z-index: 5;
        min-width: 160px;
        margin-top: 4px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        pointer-events: none;
      }

      .status-dropdown.active .status-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
      }

      /* Support for status-menu-visible class used in inline styles */
      .status-menu-visible {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        background: white !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25) !important;
        border: 1px solid rgba(0, 0, 0, 0.2) !important;
        z-index: 5 !important;
        min-width: 160px !important;
        margin-top: 4px !important;
      }

      .status-dropdown.active .status-menu-visible {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
        pointer-events: auto !important;
      }

      .status-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        font-size: 13px;
        color: #2c3e50;
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 0;
      }

      .status-option:first-child {
        border-radius: 12px 12px 0 0;
      }

      .status-option:last-child {
        border-radius: 0 0 12px 12px;
      }

      .status-option:hover {
        background: linear-gradient(135deg, rgba(26, 77, 62, 0.1) 0%, rgba(15, 61, 46, 0.05) 100%);
        color: #1a4d3e;
      }

      .status-option.approve {
        border-bottom: 1px solid #f0f0f0;
      }

      .status-option.approve:hover {
        background: linear-gradient(135deg, rgba(82, 183, 136, 0.1) 0%, rgba(64, 145, 108, 0.05) 100%);
      }

      .status-option.reject:hover {
        background: linear-gradient(135deg, rgba(116, 198, 157, 0.1) 0%, rgba(82, 183, 136, 0.05) 100%);
      }

      .status-option i {
        width: 16px;
        text-align: center;
        font-size: 14px;
      }

      .status-option.approve i {
        color: #40916c;
      }

      .status-option.reject i {
        color: #74c69d;
      }

      /* Loading state */
      .status-button.loading {
        pointer-events: none;
        opacity: 0.7;
      }

      .status-button.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid transparent;
        border-top: 2px solid #1a4d3e;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
      }

      /* Special state for sent documents */
      .badge-status.badge-sent {
        background: #083E40;
        color: white;
        border-color: #083E40;
        position: relative;
      }

      /* Animations */
      @keyframes shimmer {
        0% {
          left: -100%;
        }

        100% {
          left: 100%;
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

      /* Enhanced hover effects */
      .badge-status:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
      }

      /* Responsive Status Badges */
      @media (max-width: 768px) {
        .badge-status {
          padding: 6px 12px;
          font-size: 11px;
          min-width: 80px;
          gap: 4px;
        }

        .badge-status.badge-proses::after {
          width: 4px;
          height: 4px;
          margin-left: 4px;
        }
      }

      @media (max-width: 480px) {
        .badge-status {
          padding: 5px 10px;
          font-size: 10px;
          min-width: 70px;
          border-radius: 15px;
        }

        .badge-status span {
          display: none;
          /* Hide text on very small screens, show only icons */
        }

        .badge-status::before {
          font-size: 14px;
        }
      }

      .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
      }

      .btn-action {
        padding: 8px 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s ease;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }

      .btn-edit {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
      }

      .btn-kirim {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
      }


      .btn-view {
        background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
        color: white;
      }

      .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
      }

      .btn-action:active {
        transform: translateY(-1px);
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

      .btn-action:disabled,
      .btn-action[disabled] {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
      }

      .btn-action:disabled:hover,
      .btn-action[disabled]:hover {
        transform: none !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
      }

      .btn-set-deadline {
        background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%) !important;
        color: white;
      }

      .btn-set-deadline:hover {
        background: linear-gradient(135deg, #ff8c00 0%, #e67300 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
      }

      /* Enhanced Responsive Action Buttons */
      .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 6px;
        justify-content: center;
        align-items: stretch;
        width: 100%;
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

      .btn-kirim {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
        color: white;
      }

      .btn-kirim:hover {
        background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #0f6f74 100%);
      }

      .btn-kembalikan {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
      }

      .btn-kembalikan:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        color: white;
      }

      /* Touch-friendly button sizes */
      .btn-action {
        min-width: 44px;
        min-height: 44px;
        padding: 10px 12px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 11px;
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
        -webkit-tap-highlight-color: transparent;
      }

      .btn-action i {
        font-size: 12px;
        flex-shrink: 0;
      }

      .btn-action span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      /* Enhanced hover and active states */
      .btn-action:hover:not(.locked) {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
      }

      .btn-action:active:not(.locked) {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      }

      /* Ripple effect for better touch feedback */
      .btn-action::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.3s ease, height 0.3s ease;
      }

      .btn-action:active::before {
        width: 100px;
        height: 100px;
      }

      /* Action button types with enhanced gradients */
      .btn-edit {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #0d5f63 100%);
        color: white;
      }

      .btn-edit:hover {
        background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 50%, #0f6f74 100%);
      }

      .btn-return {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 50%, #bd2130 100%);
        color: white;
      }

      .btn-return:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 50%, #a71e2a 100%);
      }


      .btn-chevron {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 50%, #495057 100%);
        color: white;
        min-width: 36px;
        min-height: 36px;
      }

      .btn-chevron:hover {
        background: linear-gradient(135deg, #5a6268 0%, #495057 50%, #343a40 100%);
      }

      .btn-chevron.active {
        background: linear-gradient(135deg, #083E40 0%, #889717 100%);
        transform: rotate(180deg);
      }

      /* Responsive Action Button Styles */
      @media (max-width: 1200px) {
        .btn-action {
          padding: 8px 10px;
          font-size: 10px;
          gap: 3px;
        }

        .btn-action i {
          font-size: 11px;
        }

        .action-buttons {
          gap: 4px;
        }
      }

      @media (max-width: 768px) {
        .action-buttons {
          flex-direction: column;
          gap: 6px;
          align-items: stretch;
        }

        .btn-action {
          width: 100%;
          min-width: 48px;
          min-height: 48px;
          padding: 12px 8px;
          font-size: 11px;
          border-radius: 8px;
          justify-content: center;
          gap: 6px;
        }

        .btn-action i {
          font-size: 14px;
        }

        .btn-chevron {
          min-width: 48px;
          min-height: 36px;
          width: auto;
          max-width: 80px;
        }

        /* Button tooltips on mobile */
        .btn-action[title]:hover::after {
          content: attr(title);
          position: absolute;
          bottom: 100%;
          left: 50%;
          transform: translateX(-50%);
          background: rgba(0, 0, 0, 0.9);
          color: white;
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 10px;
          white-space: nowrap;
          z-index: 1000;
          margin-bottom: 4px;
        }
      }

      @media (max-width: 480px) {
        .action-buttons {
          flex-direction: row;
          flex-wrap: nowrap;
          gap: 3px;
          overflow-x: auto;
          padding: 2px;
          -webkit-overflow-scrolling: touch;
        }

        .btn-action {
          flex-shrink: 0;
          min-width: 44px;
          min-height: 44px;
          padding: 8px 6px;
          font-size: 0;
          border-radius: 6px;
        }

        .btn-action i {
          font-size: 16px;
          margin: 0;
        }

        .btn-action span {
          display: none;
        }

        /* Special cases for important buttons */
        .btn-set-deadline {
          background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
          min-width: 48px;
        }

        .btn-set-deadline::before {
          content: '⏰';
          font-size: 14px;
          font-style: normal;
        }

        .btn-set-deadline i {
          display: none;
        }
      }

      /* High contrast mode support */
      @media (prefers-contrast: high) {
        .btn-action {
          border: 2px solid currentColor;
          background: white;
          color: black;
        }

        .btn-edit {
          border-color: #083E40;
          color: #083E40;
        }

        .btn-return {
          border-color: #dc3545;
          color: #dc3545;
        }

        .btn-chevron {
          border-color: #6c757d;
          color: #6c757d;
        }
      }

      /* Dark mode support */
      @media (prefers-color-scheme: dark) {
        .btn-action {
          background: linear-gradient(135deg, #495057 0%, #343a40 100%);
          color: white;
        }

        .btn-action:hover:not(.locked) {
          background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
        }
      }

      .filter-section {
        display: flex;
        gap: 10px;
        align-items: center;
      }

      .filter-section select,
      .filter-section input {
        padding: 10px 14px;
        border: 2px solid rgba(8, 62, 64, 0.1);
        border-radius: 10px;
        font-size: 13px;
        transition: all 0.3s ease;
        background: white;
        font-weight: 500;
      }

      .filter-section select:focus,
      .filter-section input:focus {
        outline: none;
        border-color: #889717;
        box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
      }

      .btn-filter {
        padding: 10px 24px;
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
      }

      .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(8, 62, 64, 0.3);
      }

      .btn-tambah {
        padding: 10px 24px;
        background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(136, 151, 23, 0.2);
      }

      .btn-tambah:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(136, 151, 23, 0.3);
      }

      .btn-excel {
        padding: 10px 24px;
        background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        margin-left: 0;
      }

      .btn-excel:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
      }

      .btn-customize-columns-inline {
        padding: 10px 20px;
        background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(136, 151, 23, 0.2);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        min-height: 44px;
        white-space: nowrap;
        margin-right: 20px;
      }

      .btn-customize-columns-inline:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(136, 151, 23, 0.3);
        background: linear-gradient(135deg, #9ab01f 0%, #a8bf23 100%);
        color: white;
      }

      .btn-customize-columns-inline:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(136, 151, 23, 0.2);
      }

      .chevron-icon {
        transition: transform 0.4s ease;
        color: #fff;
      }

      .chevron-icon.rotate {
        transform: rotate(180deg);
      }

      .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 24px;
      }

      .pagination button {
        padding: 10px 16px;
        border: 2px solid rgba(8, 62, 64, 0.1);
        background-color: white;
        cursor: pointer;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: #083E40;
      }

      .pagination button:hover {
        border-color: #889717;
        background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, transparent 100%);
        transform: translateY(-2px);
      }

      .pagination button.active {
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
      }

      .btn-chevron {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
      }

      .btn-chevron:hover {
        background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
      }

      .btn-paraf {
        padding: 10px 24px;
        background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
      }

      .btn-paraf-selesai {
        padding: 10px 24px;
        background: linear-gradient(135deg, #5eff00ff 0%, #07ff13ff 100%);
        color: hitam;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
      }

      .span-terlambat {
        font-weight: 600;
        color: #ff002bff;
        font-size: 12px;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
      }

      .span-tepatWaktu {
        font-weight: 600;
        color: #08c757ff;
        font-size: 12px;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
      }

      .span-proses {
        font-weight: 600;
        color: #ffca2c;
        font-size: 12px;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
      }

      /* Chevron Icon Animation */
      .chevron-icon {
        transition: transform 0.3s ease;
      }

      .chevron-icon.rotate {
        transform: rotate(180deg);
      }

      .btn-chevron {
        background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
        padding: 8px 12px;
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

      .preview-table thead {
        background: #212529;
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

      .preview-table th:last-child {
        border-right: none;
      }

      .preview-table tbody tr:hover {
        background-color: #f8f9fa;
      }

      .preview-table td {
        padding: 12px;
        text-align: center;
        border-right: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
        color: #495057;
        font-size: 13px;
      }

      .preview-table td:last-child {
        border-right: none;
      }

      .preview-table tbody tr:last-child td {
        border-bottom: none;
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
    </style>

    <h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>



    <!-- Enhanced Search & Filter Box -->
    <div class="search-box">
      <form action="{{ route('dokumensB.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-3">
        <div class="input-group" style="flex: 1; min-width: 300px;">
          <span class="input-group-text">
            <i class="fa-solid fa-magnifying-glass text-muted"></i>
          </span>
          <input type="text" class="form-control" name="search"
            placeholder="Cari nomor agenda, SPP, nilai rupiah, atau field lainnya..." value="{{ request('search') }}">
        </div>
        <div class="filter-section">
          <select name="year" id="year-filter" class="form-select" onchange="this.form.submit()">
            <option value="">Semua Tahun</option>
            <option value="2025" {{ request('year') == '2025' ? 'selected' : '' }}>2025</option>
            <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
            <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
          </select>
          <select name="status" id="status-filter" class="form-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="deadline" {{ request('status') == 'deadline' ? 'selected' : '' }}>Deadline</option>
            <option value="sedang_proses" {{ request('status') == 'sedang_proses' ? 'selected' : '' }}>Sedang Proses</option>
            <option value="terkirim_perpajakan" {{ request('status') == 'terkirim_perpajakan' ? 'selected' : '' }}>Terkirim ke
              Perpajakan</option>
            <option value="terkirim_akutansi" {{ request('status') == 'terkirim_akutansi' ? 'selected' : '' }}>Terkirim ke
              Akutansi</option>
            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Dokumen Ditolak</option>
          </select>
        </div>
        <button type="submit" class="btn-filter">
          <i class="fa-solid fa-filter me-2"></i>Filter
        </button>
        <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
          <i class="fa-solid fa-table-columns me-2"></i>
          Kustomisasi Kolom Tabel
        </button>
        <a href="#"><button type="button" class="btn-excel mr-2">Ekspor ke PDF</button></a>

        <!-- Preserve per_page and columns parameters -->
        @if(request('per_page'))
          <input type="hidden" name="per_page" value="{{ request('per_page') }}">
        @endif
        @if(request('columns'))
          @foreach(request('columns') as $column)
            <input type="hidden" name="columns[]" value="{{ $column }}">
          @endforeach
        @endif
      </form>
    </div>

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

    <!-- Tabel Dokumen -->
    <div class="table-dokumen">
      <div class="table-container-header">
        <h3 class="table-container-title">
          <i class="fa-solid fa-file-lines"></i>
          Daftar Dokumen B
        </h3>
        <div class="table-container-stats">
          <div class="stat-item">
            <span class="stat-value">{{ count($dokumens) }}</span>
            <span class="stat-label">Total</span>
          </div>
          <div class="stat-item">
            <span
              class="stat-value">{{ $dokumens->whereIn('status', ['selesai', 'approved_ibub', 'sent_to_perpajakan', 'sent_to_akutansi'])->count() }}</span>
            <span class="stat-label">Selesai</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{{ $dokumens->filter(function ($dokumen) {
    $roleData = $dokumen->getDataForRole('ibub');
    $hasDeadline = ($roleData && $roleData->deadline_at) || $dokumen->deadline_at;
    $isRejected = $dokumen->roleStatuses()
      ->where('role_code', 'ibub')
      ->where('status', 'rejected')
      ->exists();
    return !$hasDeadline
      && in_array($dokumen->status, ['sent_to_ibub', 'sedang diproses'])
      && is_null($dokumen->returned_to_department_at)
      && is_null($dokumen->returned_to_bidang_at)
      && !$isRejected;
  })->count() }}</span>
            <span class="stat-label">Terkunci</span>
          </div>
        </div>
      </div>
      <div class="table-responsive table-container">
        <table class="table table-enhanced mb-0">
          <thead>
            <tr>
              <th class="col-no sticky-column">No</th>
              @foreach($selectedColumns as $col)
                @if($col !== 'status')
                  <th class="col-{{ $col }}">{{ $availableColumns[$col] ?? $col }}</th>
                @endif
              @endforeach
              <th class="col-deadline sticky-column">Deadline</th>
              <th class="col-status sticky-column">Status</th>
              <th class="col-action sticky-column">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dokumens ?? [] as $dokumen)
                @php
                  // Get deadline from roleData relationship
                  $roleData = $dokumen->getDataForRole('ibub');
                  $hasDeadline = false;
                  
                  if ($roleData && $roleData->deadline_at) {
                    $hasDeadline = true;
                  } elseif ($dokumen->deadline_at) {
                    // Fallback: check alias value
                    $hasDeadline = !is_null($dokumen->deadline_at);
                  }
                  
                  // Check rejection status from roleStatuses
                  $isRejected = $dokumen->roleStatuses()
                    ->where('role_code', 'ibub')
                    ->where('status', 'rejected')
                    ->exists();
                  
                  // Check pending status from roleStatuses
                  $isPending = $dokumen->roleStatuses()
                    ->where('role_code', 'ibub')
                    ->where('status', 'pending')
                    ->exists();
                  
                  // Document is locked if:
                  // 1. It has NO deadline AND
                  // 2. Status is 'sent_to_ibub' OR 'sedang diproses' (newly approved from inbox) AND
                  // 3. It's not a returned document (from departments/bidangs)
                  // Documents returned from departments/bidangs should not be locked even if they have no deadline initially
                  $isLocked = !$hasDeadline
                    && in_array($dokumen->status, ['sent_to_ibub', 'sedang diproses'])
                    && is_null($dokumen->returned_to_department_at)
                    && is_null($dokumen->returned_to_bidang_at);

                  $isReturnedStatus = Str::startsWith($dokumen->status, 'returned_')
                    || in_array($dokumen->status, ['returned_to_department']);

                  if ($isReturnedStatus || $isRejected) {
                    $isLocked = false;
                  }
                @endphp
                <tr class="main-row clickable-row {{ $isLocked ? 'locked-row' : '' }}"
                  onclick="handleRowClick(event, {{ $dokumen->id }})"
                  title="Klik untuk melihat detail lengkap dokumen (bisa dibuka walau status sudah terkirim)"
                  style="cursor: pointer;">
                  <td class="col-no" style="text-align: center;">
                    {{ ($dokumens->currentPage() - 1) * $dokumens->perPage() + $loop->iteration }}</td>
                  @foreach($selectedColumns as $col)
                    @if($col !== 'status')
                      <td class="col-{{ $col }}">
                        @if($col == 'nomor_agenda')
                          <span class="select-text">{{ $dokumen->nomor_agenda }}</span>
                        @elseif($col == 'nomor_spp')
                          <span class="select-text">{{ $dokumen->nomor_spp }}</span>
                        @elseif($col == 'tanggal_masuk')
                          <span
                            class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</span>
                        @elseif($col == 'nilai_rupiah')
                          <strong
                            class="select-text">{{ $dokumen->formatted_nilai_rupiah ?? 'Rp. ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                        @elseif($col == 'nomor_mirror')
                          {{ $dokumen->nomor_mirror ?? '-' }}
                        @elseif($col == 'tanggal_spp')
                          {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y H:i') : '-' }}
                        @elseif($col == 'uraian_spp')
                          {{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}
                        @elseif($col == 'kategori')
                          {{ $dokumen->kategori ?? '-' }}
                        @elseif($col == 'kebun')
                          {{ $dokumen->kebun ?? '-' }}
                        @elseif($col == 'jenis_dokumen')
                          {{ $dokumen->jenis_dokumen ?? '-' }}
                        @elseif($col == 'jenis_pembayaran')
                          {{ $dokumen->jenis_pembayaran ?? '-' }}
                        @elseif($col == 'nama_pengirim')
                          {{ $dokumen->nama_pengirim ?? '-' }}
                        @elseif($col == 'dibayar_kepada')
                          @if($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                            {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
                          @else
                            {{ $dokumen->dibayar_kepada ?? '-' }}
                          @endif
                        @elseif($col == 'no_berita_acara')
                          {{ $dokumen->no_berita_acara ?? '-' }}
                        @elseif($col == 'tanggal_berita_acara')
                          {{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-' }}
                        @elseif($col == 'no_spk')
                          {{ $dokumen->no_spk ?? '-' }}
                        @elseif($col == 'tanggal_spk')
                          {{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-' }}
                        @elseif($col == 'tanggal_berakhir_spk')
                          {{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                        @else
                          -
                        @endif
                      </td>
                    @endif
                  @endforeach
                  <!-- Kolom Deadline -->
                  <td class="col-deadline">
                    @php
                      // Get deadline from roleData relationship or from alias (fallback)
                      $roleData = $dokumen->getDataForRole('ibub');
                      $deadlineAt = null;
                      $deadlineNote = null;
                      
                      if ($roleData && $roleData->deadline_at) {
                        // Use Carbon instance from relationship
                        $deadlineAt = $roleData->deadline_at;
                        $deadlineNote = $roleData->deadline_note;
                      } elseif ($dokumen->deadline_at) {
                        // Fallback: if deadline_at is set as alias (string), convert to Carbon
                        $deadlineAt = is_string($dokumen->deadline_at) 
                          ? \Carbon\Carbon::parse($dokumen->deadline_at) 
                          : $dokumen->deadline_at;
                        $deadlineNote = $dokumen->deadline_note;
                      }
                    @endphp
                    @if($deadlineAt)
                      <div class="deadline-card" data-deadline="{{ $deadlineAt->format('Y-m-d H:i:s') }}">
                        <div class="deadline-time">
                          <i class="fa-solid fa-clock"></i>
                          <span>{{ $deadlineAt->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="deadline-indicator">
                          <i class="fa-solid"></i>
                          <span class="status-text">AMAN</span>
                        </div>
                        @if($deadlineNote)
                          <div class="deadline-note">{{ Str::limit($deadlineNote, 50) }}</div>
                        @endif
                      </div>
                    @else
                      <div class="no-deadline">
                        <i class="fa-solid fa-clock"></i>
                        <span>Belum ada deadline</span>
                      </div>
                    @endif
                  </td>
                  <!-- Kolom Status: Menampilkan status badge -->
                  <td class="col-status" style="text-align: center;" onclick="event.stopPropagation()">
                    @if($isRejected)
                      {{-- Dokumen ditolak dari inbox --}}
                      <span class="badge-status badge-dikembalikan" style="position: relative;">
                        <i class="fa-solid fa-times-circle me-1"></i>
                        <span>Dokumen Ditolak,
                          <a href="{{ route('ibub.rejected.show', $dokumen) }}"
                            class="text-white text-decoration-underline fw-bold" onclick="event.stopPropagation();"
                            style="color: #fff !important; text-decoration: underline !important; font-weight: 600 !important;">
                            Alasan
                          </a>
                        </span>
                      </span>
                    @elseif($dokumen->status == 'selesai' || $dokumen->status == 'approved_ibub')
                      {{-- Dokumen yang benar-benar sudah selesai diproses --}}
                      <span class="badge-status badge-selesai">✓
                        {{ $dokumen->status == 'approved_ibub' ? 'Approved' : 'Selesai' }}</span>
                    @elseif($dokumen->status == 'rejected_ibub')
                      <span class="badge-status badge-dikembalikan">Rejected</span>
                    @elseif($dokumen->status == 'sent_to_perpajakan')
                      <span class="badge-status badge-sent">📤 Terkirim ke Team Perpajakan</span>
                    @elseif($dokumen->status == 'sent_to_akutansi')
                      <span class="badge-status badge-sent">📤 Terkirim ke Team Akutansi</span>
                    @elseif($dokumen->status == 'sent_to_pembayaran')
                      <span class="badge-status badge-sent">📤 Terkirim ke Team Pembayaran</span>
                    @elseif(in_array($dokumen->status, ['menunggu_di_approve', 'waiting_reviewer_approval', 'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_ibub']) || $isPending)
                      @php
                        $approvalText = 'Menunggu Approval';
                        if ($dokumen->status == 'pending_approval_perpajakan') {
                          $approvalText = 'Menunggu Approval Perpajakan';
                        } elseif ($dokumen->status == 'pending_approval_akutansi') {
                          $approvalText = 'Menunggu Approval Akutansi';
                        } elseif ($dokumen->status == 'pending_approval_ibub' || $dokumen->status == 'waiting_reviewer_approval') {
                          $approvalText = 'Menunggu Approval Reviewer';
                        } elseif ($isPending) {
                          // Check which role is pending
                          $pendingStatus = $dokumen->roleStatuses()->where('status', 'pending')->first();
                          if ($pendingStatus) {
                            $roleName = match($pendingStatus->role_code) {
                              'perpajakan' => 'Perpajakan',
                              'akutansi' => 'Akutansi',
                              'pembayaran' => 'Pembayaran',
                              'ibub' => 'Reviewer',
                              default => 'Approval'
                            };
                            $approvalText = "Menunggu Approval {$roleName}";
                          }
                        }
                      @endphp
                      <span class="badge-status"
                        style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                        <i class="fa-solid fa-clock me-1"></i>
                        <span>{{ $approvalText }}</span>
                      </span>
                    @elseif($dokumen->status == 'sedang diproses' && $isLocked)
                      {{-- Dokumen yang baru di-approve dari inbox tapi belum di-set deadline --}}
                      <span class="badge-status badge-locked">🔒 Terkunci</span>
                    @elseif($dokumen->status == 'sedang diproses')
                      {{-- Dokumen yang baru di-approve dari inbox dan sudah di-set deadline --}}
                      <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                    @elseif(in_array($dokumen->status, ['sent_to_ibub']) && !$isLocked)
                      {{-- Dokumen yang sedang diproses (status lama) --}}
                      <span class="badge-status badge-proses">⏳ Diproses</span>
                    @elseif($dokumen->status == 'sent_to_ibub' && $isLocked)
                      <span class="badge-status badge-locked">🔒 Terkunci</span>
                    @elseif($dokumen->status == 'returned_to_ibua')
                      <span class="badge-status badge-dikembalikan">Dikembalikan ke Ibu A</span>
                    @elseif($dokumen->status == 'returned_to_department')
                      <span class="badge-status badge-dikembalikan">
                        Dikembalikan dari {{ Str::title($dokumen->target_department ?? 'Bagian Terkait') }}
                      </span>
                    @elseif(Str::startsWith($dokumen->status, 'returned_from_'))
                      @php
                        $source = Str::after($dokumen->status, 'returned_from_');
                        $sourceLabel = match ($source) {
                          'akutansi' => 'Team Akutansi',
                          'perpajakan' => 'Team Perpajakan',
                          default => Str::title(str_replace('_', ' ', $source)),
                        };
                      @endphp
                      <span class="badge-status badge-dikembalikan">Dikembalikan dari {{ $sourceLabel }}</span>
                    @else
                      <span class="badge-status badge-proses">⏳ {{ ucfirst($dokumen->status) }}</span>
                    @endif
                  </td>
                  <td class="col-action" onclick="event.stopPropagation()">
                    <div class="action-buttons-hybrid">
                      @if($isLocked)
                        <!-- Locked state - tampilkan button Set Deadline -->
                        <button type="button" class="btn-action btn-set-deadline btn-full-width"
                          onclick="openSetDeadlineModal({{ $dokumen->id }})" title="Tetapkan Deadline"
                          style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                          <i class="fa-solid fa-clock"></i>
                          <span>Set Deadline</span>
                        </button>
                      @elseif(in_array($dokumen->status, ['sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran', 'menunggu_di_approve', 'waiting_reviewer_approval', 'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_ibub']) || $isPending)
                        <!-- Document already sent or waiting approval - show status -->
                        <button class="btn-action btn-edit locked btn-full-width" disabled
                          title="Dokumen sedang menunggu approval, tidak dapat diedit">
                          <i class="fa-solid fa-clock"></i>
                          <span>Menunggu Approval</span>
                        </button>
                      @elseif($isRejected)
                        <!-- Dokumen ditolak dari inbox - tampilkan Kirim (full width), Edit dan Kembalikan di bawah -->
                        <button type="button" class="btn-action btn-kirim btn-full-width"
                          onclick="openSendToNextModal({{ $dokumen->id }})" title="Kirim ke Team Perpajakan/Team Akutansi">
                          <i class="fa-solid fa-paper-plane"></i>
                          <span>Kirim Data</span>
                        </button>
                        <div class="action-row">
                          <a href="{{ route('dokumensB.edit', $dokumen->id) }}" title="Edit Dokumen"
                            style="flex: 1; text-decoration: none;">
                            <button class="btn-action btn-edit" style="width: 100%;">
                              <i class="fa-solid fa-pen"></i>
                              <span>Edit</span>
                            </button>
                          </a>
                          <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                            onclick="alert('Fitur kembalikan untuk IbuB akan segera tersedia')" title="Kembalikan Dokumen">
                            <i class="fa-solid fa-undo"></i>
                            <span>Balik</span>
                          </button>
                        </div>
                      @else
                        <!-- Unlocked state - buttons enabled -->
                        @if(in_array($dokumen->status, ['sent_to_ibub', 'approved_ibub', 'sedang diproses', 'returned_to_department', 'returned_from_akutansi']))
                          <button type="button" class="btn-action btn-kirim btn-full-width"
                            onclick="openSendToNextModal({{ $dokumen->id }})" title="Kirim ke Team Perpajakan/Team Akutansi">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Kirim Data</span>
                          </button>
                        @endif
                        <div class="action-row">
                          <a href="{{ route('dokumensB.edit', $dokumen->id) }}" title="Edit Dokumen"
                            style="flex: 1; text-decoration: none;">
                            <button class="btn-action btn-edit" style="width: 100%;">
                              <i class="fa-solid fa-pen"></i>
                              <span>Edit</span>
                            </button>
                          </a>
                          <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                            onclick="alert('Fitur kembalikan untuk IbuB akan segera tersedia')" title="Kembalikan Dokumen">
                            <i class="fa-solid fa-undo"></i>
                            <span>Balik</span>
                          </button>
                        </div>
                      @endif
                    </div>
                  </td>
                </tr>
                <tr class="detail-row" id="detail-{{ $dokumen->id }}">
                  <td colspan="9">
                  <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                    <div class="text-center p-4">
                      <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="{{ count($selectedColumns) + 3 }}" class="text-center" style="padding: 40px;">
                  <i class="fa-solid fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
                  <p style="color: #999; font-size: 14px;">Belum ada dokumen</p>
                </td>
              </tr>
            @endforelse
        </tbody>
      </table>
      </div>
    </div>

    <!-- Pagination -->
    @if(isset($dokumens) && $dokumens->hasPages())
      @include('partials.pagination-enhanced', ['paginator' => $dokumens])
    @endif

    <!-- Modal Alasan Pengembalian -->

    <script>
    // Core JavaScript functions - Load first to ensure availability
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM loaded, checking function availability...');

      // Wait for scripts to fully load
      setTimeout(() => {
        console.log('Checking function types:');
        console.log('window.toggleDetail type:', typeof window.toggleDetail);
        console.log('window.loadDocumentDetail type:', typeof window.loadDocumentDetail);

        if (typeof window.toggleDetail === 'function') {
          console.log('✓ toggleDetail is properly loaded');
        } else {
          console.error('✗ toggleDetail is not a function');
        }

        if (typeof window.loadDocumentDetail === 'function') {
          console.log('✓ loadDocumentDetail is properly loaded');
        } else {
          console.error('✗ loadDocumentDetail is not a function');
        }

        // Test with a click on first document row if available
        const firstRow = document.querySelector('tr.main-row');
        if (firstRow) {
          console.log('Found document rows, ready for clicking');
        }
      }, 1000);
    });

    // Confirm paraf function - Define as global
    window.confirmParaf = function(dokumenId) {
      if (confirm("Yakin mau menandai bahwa dokumen ini telah diparaf (selesai)?")) {
        // Implementation for paraf action
        console.log("Paraf confirmed for document:", dokumenId);
      }
    };

    console.log('Additional functions loaded');
    console.log('Final toggleDetail type:', typeof window.toggleDetail);
    console.log('Final loadDocumentDetail type:', typeof window.loadDocumentDetail);

    // Simple approve function
    function quickApprove(docId) {
      console.log('Quick approve called for docId:', docId);

      if (confirm('Apakah Anda yakin ingin menyetujui (approve) dokumen ini?')) {
        changeDocumentStatus(docId, 'approved');
      }
    }

    // Simple reject function
    function quickReject(docId) {
      console.log('Quick reject called for docId:', docId);

      if (confirm('Apakah Anda yakin ingin menolak (reject) dokumen ini?')) {
        changeDocumentStatus(docId, 'rejected');
      }
    }

    // Simplified status change function
    function changeDocumentStatus(docId, action, event) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }

      console.log('Change status called for docId:', docId, 'action:', action);

      // Try multiple selectors to find the status container
      let statusContainer = document.querySelector(`#status-dropdown-${docId}`);
      if (!statusContainer) {
        statusContainer = document.querySelector(`[onclick*="quickApprove(${docId})"]`).closest('td');
      }
      if (!statusContainer) {
        statusContainer = document.querySelector(`[onclick*="quickReject(${docId})"]`).closest('td');
      }
      if (!statusContainer) {
        statusContainer = document.querySelector(`tr:has([onclick*="${docId}"]) .col-status`);
      }

      if (!statusContainer) {
        console.error('Status container not found for docId:', docId);
        console.log('Available containers with docId:', document.querySelectorAll(`[onclick*="${docId}"]`));
        return;
      }

      console.log('Found status container:', statusContainer);

      // Show loading state
      const originalHTML = statusContainer.innerHTML;
      statusContainer.innerHTML = `
        <div style="
          display: flex;
          align-items: center;
          gap: 8px;
          justify-content: center;
          color: #1a4d3e;
          font-weight: 600;
          font-size: 12px;
        ">
          <i class="fa-solid fa-spinner fa-spin"></i>
          <span>Processing...</span>
        </div>
      `;

      // Send AJAX request
      fetch(`/dokumensB/${docId}/change-status`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          status: action,
          document_id: docId  // FIX: Kirim document ID untuk mencegah cross-interference
        })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Show success notification
          showNotification(data.message, 'success');
          console.log('Status changed successfully:', data);

          // Update display
          const statusText = action === 'approved' ? 'Approved' : 'Rejected';
          const badgeClass = action === 'approved' ? 'badge-selesai' : 'badge-dikembalikan';

          statusContainer.innerHTML = `
            <span class="badge-status ${badgeClass}">
              ${action === 'approved' ? '✓' : '✗'} ${statusText}
            </span>
          `;

          // Update action buttons for this row
          updateActionButtons(docId, action);

          // Refresh page after delay to update dashboard stats
          setTimeout(() => {
            window.location.reload();
          }, 2000);

        } else {
          // Show error notification
          showNotification(data.message, 'error');
          console.error('Status change failed:', data);
          statusContainer.innerHTML = originalHTML;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat mengubah status dokumen.', 'error');
        statusContainer.innerHTML = originalHTML;
      });
    }

    function updateActionButtons(docId, action) {
      const row = document.querySelector(`tr:has(#status-dropdown-${docId})`);
      if (!row) return;

      const actionCell = row.querySelector('.col-action');
      if (!actionCell) return;

      // Disable action buttons based on status
      const actionButtons = actionCell.querySelectorAll('.btn-action');
      actionButtons.forEach(btn => {
        if (action === 'approved') {
          // Keep some buttons for approved documents
          if (!btn.classList.contains('btn-edit') && !btn.classList.contains('btn-paraf')) {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
          }
        } else if (action === 'rejected') {
          // Disable most action buttons for rejected documents
          btn.disabled = true;
          btn.style.opacity = '0.5';
          btn.style.cursor = 'not-allowed';
        }
      });
    }

    // Optimized countdown timer with performance improvements
    let countdownUpdateInterval = null;
    let countdownTimers = new Map();

    function initializeCountdowns() {
      // Clear existing timers to prevent memory leaks
      if (countdownUpdateInterval) {
        clearInterval(countdownUpdateInterval);
      }
      countdownTimers.forEach(timer => clearInterval(timer));
      countdownTimers.clear();
    }

    // Enhanced deadline system with color coding and late information
    function initializeDeadlines() {
      console.log('Initializing deadlines...');
      const deadlineElements = document.querySelectorAll('.deadline-card');
      console.log('Found deadline cards:', deadlineElements.length);

      deadlineElements.forEach(card => {
        updateDeadlineCard(card);
      });

      // Update every 30 seconds for better responsiveness
      setInterval(() => {
        const cards = document.querySelectorAll('.deadline-card');
        cards.forEach(card => {
          updateDeadlineCard(card);
        });
      }, 30000); // Update every 30 seconds
    }

    function updateDeadlineCard(card) {
      const deadlineStr = card.dataset.deadline;
      if (!deadlineStr) {
        console.warn('Deadline card missing data-deadline attribute');
        return;
      }

      // Check if document is already sent
      const isSent = card.dataset.sent === 'true';

      const deadline = new Date(deadlineStr);
      const now = new Date();
      const diffMs = deadline - now;

      // Remove existing status classes
      card.classList.remove('deadline-safe', 'deadline-warning', 'deadline-danger', 'deadline-overdue');

      // Find status indicator
      const statusIndicator = card.querySelector('.deadline-indicator');
      if (!statusIndicator) {
        console.error('Deadline indicator not found in card:', card);
        return;
      }
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

        // Create late info with enhanced styling only if document is not sent
        if (!isSent) {
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

        // Simplified 3-status logic: >= 1 hari = hijau, < 1 hari = kuning, terlambat = merah
        if (diffDays >= 1) {
          // Safe (>= 1 day) - Green
          card.classList.add('deadline-safe');
          statusText.textContent = 'AMAN';
          statusIcon.className = 'fa-solid fa-check-circle';
          statusIndicator.className = 'deadline-indicator deadline-safe';

          // Add time remaining hint only if document is not sent
          if (!isSent) {
            const timeHint = document.createElement('div');
            timeHint.style.cssText = 'font-size: 8px; color: #065f46; margin-top: 2px; font-weight: 600;';
            timeHint.textContent = `${diffDays} ${diffDays === 1 ? 'hari' : 'hari'} lagi`;
            card.appendChild(timeHint);
          }

        } else if (diffHours >= 1 || diffMinutes >= 1) {
          // Warning (< 1 day) - Yellow
          card.classList.add('deadline-warning');
          statusText.textContent = 'DEKAT';
          statusIcon.className = 'fa-solid fa-exclamation-triangle';
          statusIndicator.className = 'deadline-indicator deadline-warning';

          // Add time remaining hint only if document is not sent
          if (!isSent) {
            const timeHint = document.createElement('div');
            timeHint.style.cssText = 'font-size: 8px; color: #92400e; margin-top: 2px; font-weight: 700;';
            if (diffHours >= 1) {
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

    // Initialize on page load - Make sure function is available globally
    window.initializeDeadlines = initializeDeadlines;

    // Initialize deadlines when DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded, initializing deadlines...');
        initializeDeadlines();
      });
    } else {
      console.log('DOM already loaded, initializing deadlines immediately...');
      initializeDeadlines();
    }
    </script>

    <style>
    /* Optimized Deadline Display System */
    .deadline-info {
      position: relative;
      padding: 10px;
      border-radius: 12px;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border: 1px solid rgba(8, 62, 64, 0.1);
      transition: all 0.3s ease;
      overflow: hidden;
    }

    .deadline-info::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, var(--deadline-color, #28a745) 0%, var(--deadline-color-dark, #1e7e34) 100%);
      transition: all 0.3s ease;
    }

    .deadline-info:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(8, 62, 64, 0.15);
    }

    /* Simplified deadline styling - progress bar removed */

    /* Deadline Status States */
    .deadline-info.status-safe {
      --deadline-color: #28a745;
      --deadline-color-dark: #1e7e34;
      --deadline-color-light: #34ce57;
    }

    .deadline-info.status-warning {
      --deadline-color: #ffc107;
      --deadline-color-dark: #e0a800;
      --deadline-color-light: #ffcd39;
    }

    .deadline-info.status-danger {
      --deadline-color: #dc3545;
      --deadline-color-dark: #c82333;
      --deadline-color-light: #e4606d;
    }

    .deadline-info.status-overdue {
      --deadline-color: #6f42c1;
      --deadline-color-dark: #59359a;
      --deadline-color-light: #7950b2;
      animation: overdue-pulse 2s infinite;
    }

    @keyframes overdue-pulse {
      0%, 100% {
        box-shadow: 0 0 0 0 rgba(111, 66, 193, 0.4);
      }
      50% {
        box-shadow: 0 0 0 8px rgba(111, 66, 193, 0);
      }
    }

    /* Optimized Countdown Display */
    .deadline-countdown {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 6px;
      font-weight: 600;
      font-size: 13px;
      color: #083E40;
    }

    .deadline-countdown-icon {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
    }

    .deadline-countdown-text {
      font-weight: 700;
      flex: 1;
      text-align: right;
    }

    /* Deadline Date and Note */
    .deadline-date {
      font-size: 11px;
      color: #666;
      margin-top: 4px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .deadline-date small {
      font-weight: 500;
    }

    .deadline-note {
      font-size: 10px;
      color: #888;
      margin-top: 6px;
      padding-top: 6px;
      border-top: 1px solid rgba(8, 62, 64, 0.1);
      font-style: italic;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* Responsive Deadline Display */
    @media (max-width: 768px) {
      .deadline-info {
        padding: 8px;
      }

      .deadline-progress {
        height: 5px;
        margin-bottom: 6px;
      }

      .deadline-countdown {
        font-size: 12px;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
      }

      .deadline-countdown-text {
        text-align: left;
      }

      .deadline-date {
        font-size: 10px;
      }

      .deadline-note {
        font-size: 9px;
      }
    }

    @media (max-width: 480px) {
      .deadline-info {
        padding: 6px;
      }

      .deadline-progress {
        height: 4px;
        margin-bottom: 4px;
      }

      .deadline-countdown {
        font-size: 11px;
      }

      .deadline-date {
        display: none; /* Hide date on very small screens */
      }

      .deadline-note {
        margin-top: 4px;
        padding-top: 4px;
      }
    }

    /* No deadline state */
    .deadline-empty {
      color: #999;
      font-size: 12px;
      font-style: italic;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 8px;
    }
    </style>

    <!-- Modal for Setting Deadline -->
    <div class="modal fade" id="setDeadlineModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
            <h5 class="modal-title">
              <i class="fa-solid fa-clock me-2"></i>Tetapkan Deadline Verifikasi
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="deadlineDocId">

            <div class="alert alert-info border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 140, 0, 0.1) 100%); border-left: 4px solid #ffc107;">
              <i class="fa-solid fa-info-circle me-2"></i>
              <strong>Penting:</strong> Setelah deadline ditetapkan, dokumen akan terbuka untuk diproses lebih lanjut.
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
                        placeholder="Contoh: Perlu verifikasi dokumen pendukung tambahan..."
                        maxlength="500"></textarea>
              <div class="form-text">
                <span id="charCount">0</span>/500 karakter
              </div>
            </div>

            <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.05) 0%, rgba(255, 140, 0, 0.05) 100%);">
              <i class="fa-solid fa-exclamation-triangle me-2"></i>
              <small>
                <strong>Catatan:</strong> Deadline yang telah ditetapkan tidak dapat diubah kembali. Pastikan periode yang dipilih sudah sesuai.
              </small>
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

    <!-- Modal for Sending to Next Handler -->
    <div class="modal fade" id="sendToNextModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white;">
            <h5 class="modal-title">Kirim Dokumen ke Bidang Berikutnya</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="nextDocId">

            <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, rgba(8, 62, 64, 0.1) 0%, rgba(136, 151, 23, 0.1) 100%); border-left: 4px solid #083E40;">
              <i class="fa-solid fa-info-circle me-2"></i>
              <strong>Catatan:</strong> Deadline akan ditetapkan oleh departemen tujuan (Team Perpajakan, Team Akutansi, atau Team Pembayaran) setelah dokumen diterima.
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">
                <i class="fa-solid fa-location-arrow me-2"></i>Pilih Tujuan Pengiriman:
              </label>
              <div class="form-check mb-3" style="border: 2px solid rgba(8, 62, 64, 0.1); border-radius: 8px; padding: 12px; transition: all 0.3s ease;">
                <input class="form-check-input" type="radio" name="next_handler" id="perpajakan" value="perpajakan" required>
                <label class="form-check-label w-100" for="perpajakan" style="cursor: pointer;">
                  <div class="d-flex align-items-start">
                    <i class="fa-solid fa-receipt me-3 mt-1" style="color: #083E40; font-size: 20px;"></i>
                    <div>
                      <strong style="color: #083E40;">Team Perpajakan</strong>
                      <small class="text-muted d-block">Untuk dokumen yang perlu diproses Team Perpajakan terlebih dahulu. Dokumen akan terkunci hingga Team Perpajakan menetapkan deadline.</small>
                    </div>
                  </div>
                </label>
              </div>
              <div class="form-check mb-3" style="border: 2px solid rgba(8, 62, 64, 0.1); border-radius: 8px; padding: 12px; transition: all 0.3s ease;">
                <input class="form-check-input" type="radio" name="next_handler" id="akutansi" value="akutansi">
                <label class="form-check-label w-100" for="akutansi" style="cursor: pointer;">
                  <div class="d-flex align-items-start">
                    <i class="fa-solid fa-calculator me-3 mt-1" style="color: #083E40; font-size: 20px;"></i>
                    <div>
                      <strong style="color: #083E40;">Team Akutansi</strong>
                      <small class="text-muted d-block">Untuk dokumen yang bisa langsung ke Team Akutansi. Dokumen akan terkunci hingga Team Akutansi menetapkan deadline.</small>
                    </div>
                  </div>
                </label>
              </div>
              <div class="form-check" style="border: 2px solid rgba(8, 62, 64, 0.1); border-radius: 8px; padding: 12px; transition: all 0.3s ease;">
                <input class="form-check-input" type="radio" name="next_handler" id="pembayaran" value="pembayaran">
                <label class="form-check-label w-100" for="pembayaran" style="cursor: pointer;">
                  <div class="d-flex align-items-start">
                    <i class="fa-solid fa-money-bill-wave me-3 mt-1" style="color: #083E40; font-size: 20px;"></i>
                    <div>
                      <strong style="color: #083E40;">Team Pembayaran</strong>
                      <small class="text-muted d-block">Untuk dokumen yang siap untuk diproses pembayaran. Status akan berubah menjadi "Siap Bayar" di halaman pembayaran.</small>
                    </div>
                  </div>
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" onclick="confirmSendToNext()">
              <i class="fa-solid fa-paper-plane me-2"></i>Kirim
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Send Destination Warning -->
    <div class="modal fade" id="sendDestinationWarningModal" tabindex="-1" aria-labelledby="sendDestinationWarningModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
            <h5 class="modal-title" id="sendDestinationWarningModalLabel">
              <i class="fa-solid fa-exclamation-triangle me-2"></i>Perhatian
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <div class="mb-3">
              <i class="fa-solid fa-exclamation-circle" style="font-size: 52px; color: #ffc107;"></i>
            </div>
            <h5 class="fw-bold mb-3">Pilih Tujuan Pengiriman Terlebih Dahulu!</h5>
            <p class="text-muted mb-0">
              Silakan pilih tujuan pengiriman dokumen terlebih dahulu:
              <br>• <strong>Team Perpajakan</strong> - untuk dokumen yang perlu diproses Team Perpajakan terlebih dahulu
              <br>• <strong>Team Akutansi</strong> - untuk dokumen yang bisa langsung ke Team Akutansi
              <br>• <strong>Team Pembayaran</strong> - untuk dokumen yang siap diproses pembayaran
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

    <!-- Modal for Send Success -->
    <div class="modal fade" id="sendSuccessModal" tabindex="-1" aria-labelledby="sendSuccessModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background: linear-gradient(135deg, #11823b 0%, #1cb666 100%); color: white;">
            <h5 class="modal-title" id="sendSuccessModalLabel">
              <i class="fa-solid fa-circle-check me-2"></i>Pengiriman Berhasil
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <div class="mb-3">
              <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #1cb666;"></i>
            </div>
            <h5 class="fw-bold mb-2">Dokumen berhasil dikirim!</h5>
            <p class="text-muted mb-0" id="sendSuccessMessage">
              Dokumen telah dikirim dan akan muncul di halaman tujuan.
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
    <div class="modal fade" id="deadlineSuccessModal" tabindex="-1" aria-labelledby="deadlineSuccessModalLabel" aria-hidden="true">
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
              <i class="fa-solid fa-check-circle" style="font-size: 52px; color: #ffc107;"></i>
            </div>
            <h5 class="fw-bold mb-2">Deadline berhasil ditetapkan!</h5>
            <p class="text-muted mb-0" id="deadlineSuccessMessage">
              Dokumen sekarang terbuka untuk diproses lebih lanjut.
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
    <div class="modal fade" id="deadlineWarningModal" tabindex="-1" aria-labelledby="deadlineWarningModalLabel" aria-hidden="true">
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
              Silakan pilih periode deadline (1 hari, 2 hari, atau 3 hari) sebelum menetapkan deadline.
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
        return;
      }

      // Show loading state
      const submitBtn = document.querySelector('[onclick="confirmSetDeadline()"]');
      const originalHTML = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

      // Get CSRF token from meta tag or hidden input
      let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Fallback to hidden input if meta tag not found
      if (!csrfToken) {
        const hiddenInput = document.querySelector('input[name="_token"]');
        if (hiddenInput) {
          csrfToken = hiddenInput.value;
        }
      }

      // Fallback to Laravel's global csrf_token() if available in window
      if (!csrfToken && typeof window.Laravel !== 'undefined' && window.Laravel.csrfToken) {
        csrfToken = window.Laravel.csrfToken;
      }

      if (!csrfToken) {
        console.error('CSRF token not found in any location!');
        alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
        return;
      }

      console.log('CSRF Token found:', csrfToken.substring(0, 20) + '...');

      // Type casting untuk memastikan integer
      const deadlineDaysInt = parseInt(deadlineDays);

      console.log('Sending request to: ', `/dokumensB/${docId}/set-deadline`);
      console.log('Request payload: ', {
        deadline_days: deadlineDaysInt,
        deadline_note: deadlineNote
      });
      console.log('Deadline days type: ' + typeof deadlineDaysInt + ' value: ' + deadlineDaysInt);

      fetch(`/dokumensB/${docId}/set-deadline`, {
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
          alert('Gagal menetapkan deadline: ' + (data.message || 'Terjadi kesalahan yang tidak diketahui'));
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalHTML;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        console.error('Error details:', error.message);
        alert('Terjadi kesalahan saat menetapkan deadline: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
      });
    }

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

    function openSendToNextModal(docId) {
      document.getElementById('nextDocId').value = docId;
      const modal = new bootstrap.Modal(document.getElementById('sendToNextModal'));
      modal.show();
    }

    function confirmSendToNext() {
      const docId = document.getElementById('nextDocId').value;
      const nextHandler = document.querySelector('input[name="next_handler"]:checked')?.value;

      if (!nextHandler) {
        // Ensure sendToNextModal stays open
        const sendModal = bootstrap.Modal.getInstance(document.getElementById('sendToNextModal'));
        if (!sendModal || !sendModal._isShown) {
          // If send modal is not open, open it first
          const sendModalNew = new bootstrap.Modal(document.getElementById('sendToNextModal'));
          sendModalNew.show();
        }

        // Show warning modal instead of alert
        const warningModal = new bootstrap.Modal(document.getElementById('sendDestinationWarningModal'));
        warningModal.show();

        // Focus back to first radio button when warning modal is closed
        const warningModalEl = document.getElementById('sendDestinationWarningModal');
        warningModalEl.addEventListener('hidden.bs.modal', function() {
          const firstRadio = document.getElementById('perpajakan');
          if (firstRadio) {
            setTimeout(() => {
              firstRadio.focus();
            }, 100);
          }
        }, { once: true });

        return;
      }

      const submitBtn = event.target;
      const originalHTML = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

      fetch(`/dokumensB/${docId}/send-to-next`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          next_handler: nextHandler
        })
      })
      .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('Response data:', data);
        if (data.success) {
          const modal = bootstrap.Modal.getInstance(document.getElementById('sendToNextModal'));
          modal.hide();

          showSendSuccessModal(data.message);
        } else {
          alert('Gagal mengirim: ' + data.message);
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalHTML;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim dokumen: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
      });
    }

    let shouldReloadAfterSendSuccess = false;

    function showSendSuccessModal(message) {
      const modalEl = document.getElementById('sendSuccessModal');
      if (!modalEl) {
        location.reload();
        return;
      }

      const textEl = document.getElementById('sendSuccessMessage');
      if (textEl) {
        textEl.textContent = message || 'Dokumen telah dikirim dan akan muncul di halaman tujuan.';
      }

      shouldReloadAfterSendSuccess = true;
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
      const successModalEl = document.getElementById('sendSuccessModal');
      if (successModalEl) {
        successModalEl.addEventListener('hidden.bs.modal', function() {
          if (shouldReloadAfterSendSuccess) {
            shouldReloadAfterSendSuccess = false;
            location.reload();
          }
        });
      }
    });
    </script>

    <!-- Return to Bidang Modal -->
    <div class="modal fade" id="returnToBidangModal" tabindex="-1" aria-labelledby="returnToBidangModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content" style="max-height: 90vh; overflow: hidden;">
          <div class="modal-header" style="background: linear-gradient(135deg, #6f42c1 0%, #a855f7 100%); color: white; flex-shrink: 0;">
            <h5 class="modal-title" id="returnToBidangModalLabel">
              <i class="fa-solid fa-sitemap me-2"></i>Kembalikan Dokumen ke Bidang
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 140px);">
            <div class="row g-4">
              <!-- Left Column: Document Info -->
              <div class="col-lg-5">
                <div class="sticky-top" style="top: 1rem;">
                  <h6 class="text-muted mb-3">
                    <i class="fa-solid fa-file-lines me-2"></i>Informasi Dokumen:
                  </h6>
                  <div id="return-bidang-doc-info" class="border rounded p-3 bg-light" style="min-height: 200px;">
                    <!-- Document info akan dimuat di sini -->
                  </div>
                </div>
              </div>

              <!-- Right Column: Form -->
              <div class="col-lg-7">
                <div class="sticky-top" style="top: 1rem;">
                  <h6 class="text-muted mb-3">
                    <i class="fa-solid fa-edit me-2"></i>Form Pengembalian:
                  </h6>
                  <form id="return-bidang-form" class="needs-validation" novalidate>
                    <input type="hidden" id="return-bidang-doc-id" name="doc_id">

                    <!-- Target Bidang -->
                    <div class="mb-4">
                      <label for="target_bidang" class="form-label fw-bold">
                        <i class="fa-solid fa-sitemap me-2 text-primary"></i>Bidang Tujuan
                        <span class="text-danger">*</span>
                      </label>
                      <select class="form-select form-select-lg" id="target_bidang" name="target_bidang" required>
                        <option value="">-- Pilih Bidang Tujuan --</option>
                        <option value="DPM">DPM - Divisi Produksi dan Manufaktur</option>
                        <option value="SKH">SKH - Sub Kontrak Hutan</option>
                        <option value="SDM">SDM - Sumber Daya Manusia</option>
                        <option value="TEP">TEP - Teknik dan Perencanaan</option>
                        <option value="KPL">KPL - Keuangan dan Pelaporan</option>
                        <option value="AKN">AKN - Akuntansi</option>
                        <option value="TAN">TAN - Tanaman dan Perkebunan</option>
                        <option value="PMO">PMO - Project Management Office</option>
                      </select>
                      <div class="invalid-feedback">
                        Silakan pilih bidang tujuan pengembalian.
                      </div>
                    </div>

                    <!-- Return Reason -->
                    <div class="mb-4">
                      <label for="bidang_return_reason" class="form-label fw-bold">
                        <i class="fa-solid fa-comment me-2 text-primary"></i>Alasan Pengembalian
                        <span class="text-danger">*</span>
                      </label>
                      <textarea class="form-control" id="bidang_return_reason" name="bidang_return_reason"
                                rows="3" placeholder="Jelaskan alasan pengembalian dokumen ke bidang ini..."
                                style="resize: vertical; min-height: 80px;" required></textarea>
                      <div class="d-flex justify-content-between mt-2">
                        <div class="form-text">Minimal 5 karakter</div>
                        <div class="form-text">
                          <span id="bidang-char-count">0</span>/1000 karakter
                        </div>
                      </div>
                      <div class="invalid-feedback">
                        Alasan pengembalian minimal 5 karakter dan maksimal 1000 karakter.
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-4">
                      <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times me-2"></i>Batal
                      </button>
                      <button type="button" class="btn btn-primary flex-fill" id="submit-return-bidang" onclick="returnToBidang()">
                        <i class="fa-solid fa-sitemap me-2"></i>Kembalikan ke Bidang
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
    /* Sticky positioning fix for modal */
    .modal .sticky-top {
      z-index: 1;
    }
    </style>

    <script>
    // Open Return to Bidang Modal
    function openReturnToBidangModal(docId) {
      // Fetch document details via AJAX (returns HTML)
      fetch(`/dokumens/${docId}/detail`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html'
        }
      })
        .then(response => response.text())
        .then(html => {
          // Set document info in modal
          document.getElementById('return-bidang-doc-info').innerHTML = html;
          document.getElementById('return-bidang-doc-id').value = docId;

          // Reset form
          document.getElementById('target_bidang').value = '';
          document.getElementById('bidang_return_reason').value = '';
          document.getElementById('bidang-char-count').textContent = '0';

          // Show modal
          const modal = new bootstrap.Modal(document.getElementById('returnToBidangModal'));
          modal.show();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Gagal memuat detail dokumen.');
        });
    }

    // Return to Bidang function
    function returnToBidang() {
      const docId = document.getElementById('return-bidang-doc-id').value;
      const targetBidang = document.getElementById('target_bidang').value;
      const reason = document.getElementById('bidang_return_reason').value;

      if (!targetBidang) {
        alert('Pilih bidang tujuan terlebih dahulu.');
        return;
      }

      if (!reason || reason.trim().length < 5) {
        alert('Alasan pengembalian minimal 5 karakter.');
        return;
      }

      const submitBtn = document.getElementById('submit-return-bidang');

      // Show loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';

      fetch(`/dokumensB/${docId}/return-to-bidang`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          target_bidang: targetBidang,
          bidang_return_reason: reason
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Close modal
          const modal = bootstrap.Modal.getInstance(document.getElementById('returnToBidangModal'));
          modal.hide();

          // Show success notification
          showNotification(`Dokumen berhasil dikembalikan ke bidang ${data.target_bidang}`, 'success');

          // Reload page after 2 seconds
          setTimeout(() => {
            location.reload();
          }, 2000);
        } else {
          alert(data.message || 'Gagal mengembalikan dokumen ke bidang.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengembalikan dokumen ke bidang.');
      })
      .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-sitemap me-2"></i>Kembalikan ke Bidang';
      });
    }

    // Character counter for bidang return reason textarea
    document.addEventListener('DOMContentLoaded', function() {
      const bidangReasonTextarea = document.getElementById('bidang_return_reason');
      const bidangCharCount = document.getElementById('bidang-char-count');

      if (bidangReasonTextarea && bidangCharCount) {
        bidangReasonTextarea.addEventListener('input', function() {
          const length = this.value.length;
          bidangCharCount.textContent = length;

          // Update color based on length
          bidangCharCount.classList.remove('warning', 'danger');
          if (length > 900) {
            bidangCharCount.classList.add('danger');
          } else if (length > 800) {
            bidangCharCount.classList.add('warning');
          }
        });
      }
    });
    </script>

    <!-- Notification Styles -->
    <style>
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      transform: translateX(100%);
      transition: all 0.3s ease;
      max-width: 400px;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    }

    .notification.show {
      transform: translateX(0);
    }

    .notification-content {
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: white;
      font-weight: 500;
    }

    .notification-success {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .notification-info {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    }

    @media (max-width: 768px) {
      .notification {
        left: 20px;
        right: 20px;
        max-width: none;
        top: 10px;
      }
    }

    /* Send to Next Modal - Radio Button Styles */
    #sendToNextModal .form-check {
      transition: all 0.3s ease;
    }

    #sendToNextModal .form-check:hover {
      border-color: #083E40 !important;
      background: linear-gradient(135deg, rgba(8, 62, 64, 0.05) 0%, rgba(136, 151, 23, 0.05) 100%);
      transform: translateX(4px);
      box-shadow: 0 2px 12px rgba(8, 62, 64, 0.15);
    }

    #sendToNextModal .form-check-input:checked ~ .form-check-label {
      color: #083E40;
    }

    #sendToNextModal .form-check:has(.form-check-input:checked) {
      border-color: #083E40 !important;
      background: linear-gradient(135deg, rgba(8, 62, 64, 0.1) 0%, rgba(136, 151, 23, 0.1) 100%);
      box-shadow: 0 4px 16px rgba(8, 62, 64, 0.2);
    }

    #sendToNextModal .form-check-input {
      width: 20px;
      height: 20px;
      margin-top: 2px;
      cursor: pointer;
    }

    #sendToNextModal .form-check-input:checked {
      background-color: #083E40;
      border-color: #083E40;
    }
    </style>

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
              <div class="panel-title">
                <i class="fa-solid fa-check-square"></i>
                Pilih Kolom
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
                          <th>Aksi</th>
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
                                @else
                                  Contoh Data {{ $i }}
                                @endif
                              </td>
                            @endforeach
                            <td>Edit, Kirim</td>
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
              <th>Aksi</th>
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

        previewHTML += `<td>Edit, Kirim</td>`;
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

      const filterForm = document.querySelector('form[action*="dokumensB"]');
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

    <script>
    // Open View Document Modal
    function openViewDocumentModal(docId) {
      // Set document ID
      document.getElementById('view-dokumen-id').value = docId;

      // Set edit button URL
      document.getElementById('view-edit-btn').href = `/dokumensB/${docId}/edit`;

      // Load document data via AJAX
      fetch(`/dokumensB/${docId}/detail`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Document data received:', data);
          if (data.success && data.dokumen) {
            const dok = data.dokumen;

            // Identitas Dokumen
            document.getElementById('view-nomor-agenda').textContent = dok.nomor_agenda || '-';
            document.getElementById('view-nomor-spp').textContent = dok.nomor_spp || '-';
            document.getElementById('view-tanggal-spp').textContent = dok.tanggal_spp ? formatDate(dok.tanggal_spp) : '-';
            document.getElementById('view-bulan').textContent = dok.bulan || '-';
            document.getElementById('view-tahun').textContent = dok.tahun || '-';
            document.getElementById('view-tanggal-masuk').textContent = dok.tanggal_masuk ? formatDateTime(dok.tanggal_masuk) : '-';
            document.getElementById('view-jenis-dokumen').textContent = dok.jenis_dokumen || '-';
            document.getElementById('view-jenis-sub-pekerjaan').textContent = dok.jenis_sub_pekerjaan || '-';
            document.getElementById('view-kategori').textContent = dok.kategori || '-';

            // Detail Keuangan & Vendor
            document.getElementById('view-uraian-spp').textContent = dok.uraian_spp || '-';
            document.getElementById('view-nilai-rupiah').textContent = dok.nilai_rupiah ? 'Rp. ' + formatNumber(dok.nilai_rupiah) : '-';
            document.getElementById('view-jenis-pembayaran').textContent = dok.jenis_pembayaran || '-';
            document.getElementById('view-dibayar-kepada').textContent = dok.dibayar_kepada || '-';
            document.getElementById('view-kebun').textContent = dok.kebun || '-';

            // Referensi Pendukung
            document.getElementById('view-no-spk').textContent = dok.no_spk || '-';
            document.getElementById('view-tanggal-spk').textContent = dok.tanggal_spk ? formatDate(dok.tanggal_spk) : '-';
            document.getElementById('view-tanggal-berakhir-spk').textContent = dok.tanggal_berakhir_spk ? formatDate(dok.tanggal_berakhir_spk) : '-';
            document.getElementById('view-nomor-mirror').textContent = dok.nomor_mirror || '-';
            document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
            document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDate(dok.tanggal_berita_acara) : '-';

            // Nomor PO & PR
            const poList = dok.dokumen_pos && dok.dokumen_pos.length > 0 
              ? dok.dokumen_pos.map(po => po.nomor_po).join(', ')
              : '-';
            const prList = dok.dokumen_prs && dok.dokumen_prs.length > 0
              ? dok.dokumen_prs.map(pr => pr.nomor_pr).join(', ')
              : '-';
            document.getElementById('view-nomor-po').textContent = poList;
            document.getElementById('view-nomor-pr').textContent = prList;

            // Show modal after data is loaded
            const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
            modal.show();
          } else {
            console.error('Invalid response format:', data);
            alert('Gagal memuat data dokumen: ' + (data.message || 'Format respons tidak valid'));
          }
        })
        .catch(error => {
          console.error('Error loading document:', error);
          alert('Gagal memuat data dokumen: ' + error.message);
        });
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
                    <label class="detail-label">Jenis Dokumen</label>
                    <div class="detail-value" id="view-jenis-dokumen">-</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="detail-item">
                    <label class="detail-label">Sub-Bagian Pekerjaan</label>
                    <div class="detail-value" id="view-jenis-sub-pekerjaan">-</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="detail-item">
                    <label class="detail-label">Kategori Investasi</label>
                    <div class="detail-value" id="view-kategori">-</div>
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
                    <label class="detail-label">Jenis Pembayaran</label>
                    <div class="detail-value" id="view-jenis-pembayaran">-</div>
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
                    <label class="detail-label">No. Mirror</label>
                    <div class="detail-value" id="view-nomor-mirror">-</div>
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

@endsection
